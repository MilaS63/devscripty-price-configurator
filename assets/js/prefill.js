(function () {
	'use strict';
	function run() {
		var box = document.querySelector('.dsp-quote[data-summary]');
		if (!box) { return; }
		var text = box.getAttribute('data-summary') || '';
		if (!text) { return; }

		var field = document.querySelector('textarea.dsp-prefill, .dsp-prefill textarea, input.dsp-prefill[type="text"], .dsp-prefill input[type="text"]');
		if (field && !field.value) {
			field.value = text;
			field.dispatchEvent(new Event('input', { bubbles: true }));
			field.dispatchEvent(new Event('change', { bubbles: true }));
		}
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}
})();
