import { Controller } from '@hotwired/stimulus';
import * as bootstrap from 'bootstrap';

export default class extends Controller {
	static targets = ['addressLine', 'postalCode', 'city', 'confirmForm', 'modal', 'continueButton'];

	connect() {
		this._initial = {
			addressLine: (this.addressLineTarget?.value ?? ''),
			postalCode: (this.postalCodeTarget?.value ?? ''),
			city: (this.cityTarget?.value ?? ''),
		};

		this._dirty = false;
		this._pendingSubmit = false;

		this._onInput = () => { this._dirty = this._computeDirty(); };

		['input', 'change'].forEach(evt => {
			this.addressLineTarget?.addEventListener(evt, this._onInput);
			this.postalCodeTarget?.addEventListener(evt, this._onInput);
			this.cityTarget?.addEventListener(evt, this._onInput);
		});

		this._onConfirmSubmitBound = (e) => this._onConfirmSubmit(e);
		this.confirmFormTarget?.addEventListener('submit', this._onConfirmSubmitBound);

		// modal instance
		this._modalInstance = this.hasModalTarget ? bootstrap.Modal.getOrCreateInstance(this.modalTarget) : null;

		// klik "Kontynuuj"
		if (this.hasContinueButtonTarget) {
			this.continueButtonTarget.addEventListener('click', () => {
				this._pendingSubmit = true;
				this._modalInstance?.hide();
				// wysyłamy formularz "na twardo"
				this.confirmFormTarget?.requestSubmit();
			});
		}
	}

	disconnect() {
		['input', 'change'].forEach(evt => {
			this.addressLineTarget?.removeEventListener(evt, this._onInput);
			this.postalCodeTarget?.removeEventListener(evt, this._onInput);
			this.cityTarget?.removeEventListener(evt, this._onInput);
		});
		this.confirmFormTarget?.removeEventListener('submit', this._onConfirmSubmitBound);
	}

	_computeDirty() {
		const cur = {
			addressLine: (this.addressLineTarget?.value ?? '').trim(),
			postalCode: (this.postalCodeTarget?.value ?? '').trim(),
			city: (this.cityTarget?.value ?? '').trim(),
		};

		return cur.addressLine !== (this._initial.addressLine ?? '').trim()
			|| cur.postalCode !== (this._initial.postalCode ?? '').trim()
			|| cur.city !== (this._initial.city ?? '').trim();
	}

	_onConfirmSubmit(e) {
		// jeśli kliknięcie przyszło z "Kontynuuj", przepuść
		if (this._pendingSubmit) {
			this._pendingSubmit = false;
			return;
		}

		const anyFilled =
			(this.addressLineTarget?.value ?? '').trim() !== '' ||
			(this.postalCodeTarget?.value ?? '').trim() !== '' ||
			(this.cityTarget?.value ?? '').trim() !== '';

		if (!anyFilled) return;

		if (this._dirty) {
			e.preventDefault();
			this._modalInstance?.show();
		}
	}
}
