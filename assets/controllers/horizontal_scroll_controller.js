import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
	static targets = ['track'];

	prev() {
		this._scroll(-1);
	}

	next() {
		this._scroll(1);
	}

	_scroll(direction) {
		if (!this.hasTrackTarget) return;

		const viewport = this.trackTarget.clientWidth;
		const amount = Math.max(240, Math.floor(viewport * 0.85));

		this.trackTarget.scrollBy({
			left: direction * amount,
			behavior: 'smooth',
		});
	}
}
