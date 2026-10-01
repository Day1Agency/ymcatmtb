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
	/* Sponsorship buttons ask which tournament before leaving the site. */
	const CAMPAIGN = {
		teen: 'https://givebutter.com/ymc-teen-player-registration',
		adult: 'https://givebutter.com/ymc-adult-player-registration'
	};
	/* Suggested amounts, pre-selected on Givebutter. Empty means let them choose. */
	const SPONSOR_BUTTONS = {
		'become a sponsor': { title: 'Become a sponsor', teen: '', adult: '' },
		'support a player': { title: 'Support a player', teen: '100', adult: '180' },
		'support a team': { title: 'Support a team', teen: '', adult: '' }
	};
	const sponsorLinks = [...root.querySelectorAll('[data-ymc-section="8"] a')].filter(a => SPONSOR_BUTTONS[a.textContent.trim().toLowerCase()]);
	if (sponsorLinks.length) {
		const modal = document.createElement('div');
		modal.className = 'ymc-choice';
		modal.hidden = true;
		modal.innerHTML = '<div class="ymc-choice__backdrop" data-ymc-close></div>'
			+ '<div class="ymc-choice__panel" role="dialog" aria-modal="true" aria-labelledby="ymc-choice-title" tabindex="-1">'
			+ '<button type="button" class="ymc-choice__close" data-ymc-close aria-label="Close">&times;</button>'
			+ '<p class="ymc-choice__eyebrow">Choose a tournament</p>'
			+ '<h2 class="ymc-choice__title" id="ymc-choice-title">Become a sponsor</h2>'
			+ '<p class="ymc-choice__lede">Which side of the tournament would you like to support?</p>'
			+ '<div class="ymc-choice__options">'
			+ '<a class="ymc-choice__option" data-ymc-go="teen" target="_blank" rel="noopener"><span class="ymc-choice__name">Teen Tournament</span><span class="ymc-choice__note">Grades 8 to 12, registered individually</span></a>'
			+ '<a class="ymc-choice__option" data-ymc-go="adult" target="_blank" rel="noopener"><span class="ymc-choice__name">Adult Tournament</span><span class="ymc-choice__note">Adults playing in teams</span></a>'
			+ '</div></div>';
		document.body.appendChild(modal);
		const panel = modal.querySelector('.ymc-choice__panel');
		const heading = modal.querySelector('.ymc-choice__title');
		let opener = null;
		function destination(which, config) {
			return CAMPAIGN[which] + '/donate' + (config[which] ? '?amount=' + config[which] : '');
		}
		function open(config, link) {
			opener = link;
			heading.textContent = config.title;
			modal.querySelectorAll('[data-ymc-go]').forEach(a => a.href = destination(a.dataset.ymcGo, config));
			modal.hidden = false;
			document.body.classList.add('ymc-choice-open');
			panel.focus();
		}
		function close() {
			modal.hidden = true;
			document.body.classList.remove('ymc-choice-open');
			if (opener) opener.focus();
			opener = null;
		}
		modal.addEventListener('click', function (event) {
			if (event.target.closest('[data-ymc-close]')) close();
			else if (event.target.closest('[data-ymc-go]')) close();
		});
		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && !modal.hidden) close();
		});
		sponsorLinks.forEach(link => {
			const config = SPONSOR_BUTTONS[link.textContent.trim().toLowerCase()];
			link.setAttribute('aria-haspopup', 'dialog');
			link.addEventListener('click', function (event) {
				event.preventDefault();
				open(config, link);
			});
		});
	}
})();
