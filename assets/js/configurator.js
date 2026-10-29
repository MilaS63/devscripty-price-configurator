(function () {
	'use strict';

	function fmt(n, cur) {
		return cur + Math.round(n).toLocaleString('en-US');
	}

	function init(root) {
		var cfg = {};
		try { cfg = JSON.parse(root.getAttribute('data-config') || '{}'); } catch (e) {}
		var cur = cfg.currency || '$';
		var speeds = cfg.speeds || [];
		var out = root.querySelector('.dsp-amount');
		var cta = root.querySelector('.dsp-cta');
		var range = root.querySelector('.dsp-range');
		var speedLabel = root.querySelector('.dsp-speed-current');

		function calc() {
			var base = 0, add = 0, speed = 0;
			var pIdx = '', sIdx = '', aIdx = [];

			var p = root.querySelector('input[data-group="platform"]:checked');
			if (p) { base = parseFloat(p.value) || 0; pIdx = p.getAttribute('data-index'); }

			root.querySelectorAll('input[data-group="addon"]:checked').forEach(function (c) {
				add += parseFloat(c.value) || 0;
				aIdx.push(c.getAttribute('data-index'));
			});

			if (range && speeds.length) {
				var i = parseInt(range.value, 10) || 0;
				sIdx = String(i);
				speed = (speeds[i] && parseFloat(speeds[i].price)) || 0;
				if (speedLabel && speeds[i]) { speedLabel.textContent = speeds[i].label; }
				var max = parseInt(range.max, 10) || 1;
				range.style.setProperty('--dsp-fill', (i / max * 100) + '%');
			}

			var total = Math.max(0, base + add + speed);
			var low = Math.round(total * (1 - (cfg.lowPct || 0) / 100));
			var high = Math.round(total * (1 + (cfg.highPct || 0) / 100));
			out.textContent = fmt(low, cur) + ' - ' + fmt(high, cur);

			// Quote page link: only short option numbers
			if (cta && cfg.quoteUrl) {
				var q = [];
				if (pIdx !== '') { q.push('dsp_p=' + pIdx); }
				if (aIdx.length) { q.push('dsp_a=' + aIdx.join('-')); }
				if (sIdx !== '') { q.push('dsp_s=' + sIdx); }
				var sep = cfg.quoteUrl.indexOf('?') === -1 ? '?' : '&';
				cta.setAttribute('href', cfg.quoteUrl + (q.length ? sep + q.join('&') : ''));
			}
		}

		root.addEventListener('change', calc);
		root.addEventListener('input', calc);
		calc();
	}

	function boot() {
		document.querySelectorAll('.dsp-cfg[data-config]').forEach(init);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
