import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
	static values = {
		storageKey: String,
	};

	connect() {
		this.key = this.storageKeyValue || "category_menu_open_v1";
		this.openSet = this.loadOpenSet();

		// restore state
		this.element.querySelectorAll(".category-collapse[id]").forEach((el) => {
			if (this.openSet.has(el.id)) {
				el.classList.add("show");
				const btn = this.element.querySelector(`[data-bs-target="#${CSS.escape(el.id)}"]`);
				if (btn) btn.setAttribute("aria-expanded", "true");
			}
		});

		// bind events
		this.onShown = (e) => this.handleShown(e);
		this.onHidden = (e) => this.handleHidden(e);

		this.element.addEventListener("shown.bs.collapse", this.onShown);
		this.element.addEventListener("hidden.bs.collapse", this.onHidden);
	}

	disconnect() {
		this.element.removeEventListener("shown.bs.collapse", this.onShown);
		this.element.removeEventListener("hidden.bs.collapse", this.onHidden);
	}

	handleShown(e) {
		const el = e.target;
		if (!el.classList.contains("category-collapse") || !el.id) return;
		this.openSet.add(el.id);
		this.saveOpenSet();
	}

	handleHidden(e) {
		const el = e.target;
		if (!el.classList.contains("category-collapse") || !el.id) return;
		this.openSet.delete(el.id);
		this.saveOpenSet();
	}

	loadOpenSet() {
		try {
			const raw = localStorage.getItem(this.key);
			const arr = raw ? JSON.parse(raw) : [];
			return new Set(Array.isArray(arr) ? arr : []);
		} catch {
			return new Set();
		}
	}

	saveOpenSet() {
		localStorage.setItem(this.key, JSON.stringify(Array.from(this.openSet)));
	}
}
