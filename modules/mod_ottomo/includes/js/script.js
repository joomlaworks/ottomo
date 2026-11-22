/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */
(function () {
	'use strict';

	function resetCaptcha() {
		// Captcha tokens are single-use: reset the widget after each attempt
		['grecaptcha', 'hcaptcha', 'turnstile'].forEach(function (name) {
			if (window[name] && typeof window[name].reset === 'function') {
				try {
					window[name].reset();
				} catch (e) { /* not rendered in this form */ }
			}
		});
	}

	function parse(text) {
		try {
			var data = JSON.parse(text);
			if (data && typeof data === 'object' && 'success' in data) {
				return data;
			}
		} catch (e) { /* plain text (template override of the endpoint, PHP notice...) */ }
		return { success: false, message: String(text || '').trim() };
	}

	function bind(root) {
		var form = root.querySelector('form');
		if (!form || form.getAttribute('data-otm-bound')) {
			return;
		}
		form.setAttribute('data-otm-bound', '1');
		var message = root.querySelector('.otm-module__message');
		var button = root.querySelector('.otm-module__submit');

		function show(text, success) {
			if (!message) {
				return;
			}
			message.textContent = text;
			message.classList.toggle('is-success', !!success);
			message.classList.toggle('is-error', !success);
		}

		function busy(state) {
			if (button) {
				button.disabled = state;
				button.setAttribute('aria-busy', state ? 'true' : 'false');
			}
		}

		form.addEventListener('submit', function (event) {
			event.preventDefault();
			// Several topics offered: at least one must be ticked
			var topics = form.querySelectorAll('input[type="checkbox"][name="topics[]"]');
			if (topics.length && !form.querySelector('input[type="checkbox"][name="topics[]"]:checked')) {
				topics[0].setCustomValidity(form.getAttribute('data-topics-error') || ' ');
				topics[0].reportValidity();
				topics[0].setCustomValidity('');
				return;
			}
			busy(true);
			fetch(form.getAttribute('action') || 'index.php', {
				method: 'POST',
				body: new FormData(form),
				credentials: 'same-origin',
				headers: { 'Accept': 'application/json' }
			})
				.then(function (response) {
					return response.text();
				})
				.then(function (text) {
					var result = parse(text);
					show(result.message || form.getAttribute('data-error') || '', result.success);
					if (result.success) {
						form.reset();
						root.classList.add('is-subscribed');
					} else {
						resetCaptcha();
					}
				})
				.catch(function () {
					show(form.getAttribute('data-error') || 'Error', false);
					resetCaptcha();
				})
				.then(function () {
					busy(false);
				});
		});
	}

	function boot() {
		Array.prototype.forEach.call(document.querySelectorAll('.otm-module'), bind);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
