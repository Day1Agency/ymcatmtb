/* Native interactions matching YMC Event Registration (standalone).html. */
(function () {
	'use strict';
	if (!document.querySelector('.ymc-reference')) return;
	const root = document;
	const counters = root.querySelectorAll('[data-ymc-countdown]');
	const target = new Date(window.ymcKickoff || '2027-03-28T09:00:00-04:00').getTime();
	function tick() {
		let remaining = Math.max(0, target - Date.now());
		const values = [86400000, 3600000, 60000, 1000].map(unit => {
			const value = Math.floor(remaining / unit);
			remaining -= value * unit;
			return value;
		});
		counters.forEach(el => { const i = Number(el.dataset.ymcCountdown); (el.firstElementChild || el).textContent = i ? String(values[i]).padStart(2, '0') : String(values[i]); });
	}
	tick();
	setInterval(tick, 1000);
	root.querySelectorAll('[data-ymc-section="9"]').forEach((faq, group) => {
	const buttons = [...faq.querySelectorAll('[data-ymc-faq]')];
	buttons.forEach((button, i) => { const answer = button.parentElement.querySelector('p'); answer.id = 'ymc-answer-' + group + '-' + i; button.setAttribute('aria-controls', answer.id); });
	buttons.forEach(button => button.addEventListener('click', function () {
		const wasOpen = button.getAttribute('aria-expanded') === 'true';
		buttons.forEach(other => {
			const open = other === button && !wasOpen;
			other.setAttribute('aria-expanded', String(open));
			document.getElementById(other.getAttribute('aria-controls')).hidden = !open;
			other.parentElement.style.borderColor = open ? '#D4AF37' : 'rgba(255,255,255,0.12)';
			other.parentElement.style.background = open ? '#241046' : 'rgba(255,255,255,0.02)';
			const icon = other.lastElementChild;
			icon.style.background = open ? '#D4AF37' : 'rgba(212,175,55,0.14)';
			icon.style.color = open ? '#241046' : '#D4AF37';
			icon.querySelector('i').className = open ? 'fas fa-minus' : 'fas fa-plus';
		});
	}));
	});
	/* The header follows the reader: it slides away going down, returns going up. */
	const header = root.querySelector('[data-ymc-section="0"]');
	if (header) {
		header.classList.add('ymc-ref-sticky-header');
		let last = window.scrollY;
		let pending = false;
		function onScroll() {
			const y = Math.max(0, window.scrollY);
			if (y > last && y > header.offsetHeight * 1.5) header.classList.add('is-hidden');
			else if (y < last || y <= 0) header.classList.remove('is-hidden');
			last = y;
			pending = false;
		}
		window.addEventListener('scroll', function () {
			if (pending) return;
			pending = true;
			window.requestAnimationFrame(onScroll);
		}, { passive: true });
	}
})();
