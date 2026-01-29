import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
	static values = {
		url: String,
		locked: Boolean,
	};
	static targets = ['payu', 'cod'];

	connect() {
		this._busy = false;
	}

	async choose(e) {
		e.preventDefault();
		if (this.lockedValue || this._busy) return;

		const btn = e.currentTarget;
		const method = btn.dataset.method;
		if (!method) return;

		this._busy = true;
		this._setDisabled(true);

		try {
			const res = await fetch(this.urlValue, {
				method: 'POST',
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'Accept': 'application/json',
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				credentials: 'same-origin',
				body: new URLSearchParams({ payment_method: method }).toString(),
			});

			const data = await res.json();
			if (!res.ok || !data.ok) {
				throw new Error('Payment update failed');
			}

			this._applySelected(method);
		} catch (err) {
			// możesz tu dorzucić toast/alert w UI
			console.error(err);
		} finally {
			this._setDisabled(false);
			this._busy = false;
		}
	}

	_applySelected(method) {
		const payu = this.hasPayuTarget ? this.payuTarget : null;
		const cod = this.hasCodTarget ? this.codTarget : null;

		if (payu) {
			payu.classList.toggle('btn-primary', method === 'payu');
			payu.classList.toggle('btn-outline-primary', method !== 'payu');
		}
		if (cod) {
			cod.classList.toggle('btn-primary', method === 'cod');
			cod.classList.toggle('btn-outline-primary', method !== 'cod');
		}
	}

	_setDisabled(disabled) {
		if (this.hasPayuTarget) this.payuTarget.disabled = disabled;
		if (this.hasCodTarget) this.codTarget.disabled = disabled;
	}
}
