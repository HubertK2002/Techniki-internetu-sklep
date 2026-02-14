import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
	connect() {
		this.onSubmit = this.onSubmit.bind(this);
		document.addEventListener('submit', this.onSubmit);
	}

	disconnect() {
		document.removeEventListener('submit', this.onSubmit);
	}

	async onSubmit(e) {
		const form = e.target.closest('.js-wishlist-form');
		if (!form) {
			return;
		}

		e.preventDefault();

		const submitBtn = form.querySelector('button[type="submit"]');
		if (submitBtn) {
			submitBtn.disabled = true;
		}

		try {
			const formData = new FormData(form);
			const res = await fetch(form.action, {
				method: 'POST',
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'Accept': 'application/json',
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				credentials: 'same-origin',
				body: new URLSearchParams(formData).toString(),
			});

			let data = null;
			try {
				data = await res.json();
			} catch (_err) {
				data = null;
			}

			if (!res.ok || !data || !data.ok) {
				form.submit();
				return;
			}

			const action = form.dataset.wishlistAction;
			const productId = form.dataset.productId;

			if (form.dataset.wishlistRole === 'toggle') {
				const container = form.closest('.js-wishlist-toggle-container');
				if (container && data.toggleHtml) {
					container.innerHTML = data.toggleHtml;
				}
			}

			if (action === 'remove' && form.dataset.wishlistRole !== 'toggle') {
				const item = document.querySelector(`[data-wishlist-item="${productId}"]`);
				if (item) {
					item.remove();
				}

				const grid = document.getElementById('wishlist-grid');
				const empty = document.getElementById('wishlist-empty');
				if (grid && grid.querySelectorAll('[data-wishlist-item]').length === 0) {
					grid.remove();
					if (empty) {
						empty.classList.remove('d-none');
					}
				}
			}
		} catch (err) {
			console.error(err);
			form.submit();
		} finally {
			if (submitBtn) {
				submitBtn.disabled = false;
			}
		}
	}
}
