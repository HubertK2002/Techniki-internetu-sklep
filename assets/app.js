import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
import { Dropdown } from 'bootstrap';

const initDropdowns = () => {
	document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach((el) => {
		Dropdown.getOrCreateInstance(el);
	});
};

document.addEventListener('DOMContentLoaded', initDropdowns);
document.addEventListener('turbo:load', initDropdowns);
document.addEventListener('turbo:render', initDropdowns);

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
