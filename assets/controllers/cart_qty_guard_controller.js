import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

export default class extends Controller {
	static targets = ['qty', 'checkoutForm', 'modal', 'list', 'ignoreButton', 'saveButton'];

	connect() {
		this._pendingSubmit = false;
		this._busy = false;

		this._modal = this.hasModalTarget
			? Modal.getOrCreateInstance(this.modalTarget)
			: null;
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

		const dirty = this._getDirtyItems();
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

		const dirty = this._getDirtyItems();
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

	_getDirtyItems() {
		const dirty = [];

		this.qtyTargets.forEach((input) => {
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
}
