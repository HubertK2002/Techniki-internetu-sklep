import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

export default class extends Controller {
	static targets = ['qty', 'checkoutForm', 'modal', 'list', 'ignoreButton', 'saveButton'];

	connect() {
		this._pendingSubmit = false;
		this._busy = false;
		this._onClick = this._onClick.bind(this);
		this._onInput = this._onInput.bind(this);
		this._onChange = this._onChange.bind(this);

		this._modal = this.hasModalTarget
			? Modal.getOrCreateInstance(this.modalTarget)
			: null;

		this.element.addEventListener('click', this._onClick);
		this.element.addEventListener('input', this._onInput);
		this.element.addEventListener('change', this._onChange);

		this._refreshSelectedTotalAndInputs();
	}

	disconnect() {
		this.element.removeEventListener('click', this._onClick);
		this.element.removeEventListener('input', this._onInput);
		this.element.removeEventListener('change', this._onChange);
	}

	async saveRow(e) {
		e.preventDefault();
		if (this._busy) return;

		const form = e.currentTarget;
		const input = form.querySelector('[data-cart-qty-guard-target="qty"]');
		if (!input) return;

		const qty = parseInt(input.value ?? '1', 10);
		if (!Number.isFinite(qty) || qty < 1) return;

		const url = input.dataset.setUrl;
		if (!url) return;

		this._busy = true;

		try {
			const res = await fetch(url, {
				method: 'POST',
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'Accept': 'application/json',
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				credentials: 'same-origin',
				body: new URLSearchParams({ qty: String(qty) }).toString(),
			});

			if (!res.ok) throw new Error('saveRow failed');

			input.dataset.initialQty = String(qty);
		} catch (err) {
			console.error(err);
		} finally {
			this._busy = false;
		}
	}

	checkoutSubmit(e) {
		if (this._pendingSubmit) {
			this._pendingSubmit = false;
			return;
		}

		const dirty = this._getDirtyItems(true);
		if (dirty.length === 0) return;

		e.preventDefault();
		this._renderList(dirty);
		this._modal?.show();
	}

	ignoreAndContinue() {
		if (this._busy) return;

		this._modal?.hide();
		this._pendingSubmit = true;
		this.checkoutFormTarget.requestSubmit();
	}

	async saveAndContinue() {
		if (this._busy) return;

		const dirty = this._getDirtyItems(true);
		if (dirty.length === 0) {
			this._modal?.hide();
			this._pendingSubmit = true;
			this.checkoutFormTarget.requestSubmit();
			return;
		}

		this._busy = true;
		this._setModalButtonsDisabled(true);

		try {
			for (const it of dirty) {
				const res = await fetch(it.setUrl, {
					method: 'POST',
					headers: {
						'X-Requested-With': 'XMLHttpRequest',
						'Accept': 'application/json',
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					credentials: 'same-origin',
					body: new URLSearchParams({ qty: String(it.current) }).toString(),
				});

				if (!res.ok) throw new Error('saveAll failed: ' + it.name);
			}

			this.qtyTargets.forEach((input) => {
				const v = parseInt(input.value ?? '1', 10);
				if (Number.isFinite(v) && v >= 1) input.dataset.initialQty = String(v);
			});

			this._modal?.hide();
			this._pendingSubmit = true;
			this.checkoutFormTarget.requestSubmit();
		} catch (err) {
			console.error(err);
		} finally {
			this._setModalButtonsDisabled(false);
			this._busy = false;
		}
	}

	_getDirtyItems(onlySelected = false) {
		const dirty = [];

		this.qtyTargets.forEach((input) => {
			if (onlySelected) {
				const row = input.closest('[data-cart-item]');
				const checkbox = row ? row.querySelector('[data-cart-select]') : null;
				if (!checkbox || !checkbox.checked) return;
			}

			const initial = parseInt(input.dataset.initialQty ?? '0', 10);
			const current = parseInt(input.value ?? '0', 10);

			if (!Number.isFinite(initial) || !Number.isFinite(current) || current < 1) return;
			if (current === initial) return;

			dirty.push({
				name: input.dataset.productName ?? 'Produkt',
				initial,
				current,
				setUrl: input.dataset.setUrl ?? '',
			});
		});

		return dirty.filter(x => x.setUrl);
	}

	_renderList(dirty) {
		if (!this.hasListTarget) return;
		this.listTarget.innerHTML = '';
		dirty.forEach((it) => {
			const li = document.createElement('li');
			li.textContent = `${it.name}: ${it.initial} → ${it.current}`;
			this.listTarget.appendChild(li);
		});
	}

	_setModalButtonsDisabled(disabled) {
		if (this.hasIgnoreButtonTarget) this.ignoreButtonTarget.disabled = disabled;
		if (this.hasSaveButtonTarget) this.saveButtonTarget.disabled = disabled;
	}

	_onClick(e) {
		const minus = e.target.closest('[data-cart-qty-minus]');
		const plus = e.target.closest('[data-cart-qty-plus]');
		if (!minus && !plus) return;

		const form = (minus || plus).closest('form');
		const input = form ? form.querySelector('[data-cart-qty-input]') : null;
		if (!input) return;

		const current = parseInt(input.value || '1', 10);
		const safeCurrent = Number.isNaN(current) ? 1 : current;

		if (minus) input.value = String(Math.max(1, safeCurrent - 1));
		if (plus) input.value = String(Math.max(1, safeCurrent + 1));

		this._refreshCartLineAndTotal(form);
		form.requestSubmit();
	}

	_onInput(e) {
		const input = e.target.closest('[data-cart-qty-input]');
		if (!input) return;

		const value = parseInt(input.value || '1', 10);
		if (Number.isNaN(value) || value < 1) {
			input.value = '1';
		}

		this._refreshCartLineAndTotal(input.closest('form'));
	}

	_onChange(e) {
		const checkbox = e.target.closest('[data-cart-select]');
		if (checkbox) {
			this._refreshSelectedTotalAndInputs();
			return;
		}

		const input = e.target.closest('[data-cart-qty-input]');
		if (!input) return;
		const form = input.closest('form');
		if (!form) return;

		this._refreshCartLineAndTotal(form);
		form.requestSubmit();
	}

	_refreshCartLineAndTotal(form) {
		if (!form) return;
		const item = form.closest('[data-cart-item]');
		if (!item) return;

		const qtyInput = form.querySelector('[data-cart-qty-input]');
		const lineNode = item.querySelector('[data-cart-line-total]');
		if (!qtyInput || !lineNode) return;

		const unitPrice = parseFloat(item.dataset.unitPrice || '0');
		const qty = Math.max(1, parseInt(qtyInput.value || '1', 10) || 1);
		const line = unitPrice * qty;
		lineNode.textContent = this._formatPln(line);

		this._refreshSelectedTotalAndInputs();
	}

	_refreshSelectedTotalAndInputs() {
		let sum = 0;
		this.element.querySelectorAll('[data-cart-item]').forEach((row) => {
			const checkbox = row.querySelector('[data-cart-select]');
			if (!checkbox || !checkbox.checked) return;

			const rowPrice = parseFloat(row.dataset.unitPrice || '0');
			const rowQtyInput = row.querySelector('[data-cart-qty-input]');
			const rowQty = Math.max(1, parseInt((rowQtyInput && rowQtyInput.value) || '1', 10) || 1);
			sum += rowPrice * rowQty;
		});

		const grand = this.element.querySelector('#cart-grand-total');
		if (grand) grand.textContent = this._formatPln(sum);

		const container = this.element.querySelector('#selected-items-inputs');
		if (container) {
			container.innerHTML = '';
			this.element.querySelectorAll('[data-cart-select]:checked').forEach((checkbox) => {
				const id = checkbox.getAttribute('data-product-id');
				if (!id) return;
				const input = document.createElement('input');
				input.type = 'hidden';
				input.name = 'selected_product_ids[]';
				input.value = id;
				container.appendChild(input);
			});
		}
	}

	_formatPln(value) {
		return `${new Intl.NumberFormat('pl-PL', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value)} zł`;
	}
}
