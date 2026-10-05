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
	/* Sections arrive as you reach them, rather than sitting there flat. */
	const SKIP_REVEAL = ['0', '2', '11'];
	if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && 'IntersectionObserver' in window) {
		const revealed = [];
		function collect(el) {
			/* A row of cards reads better animating card by card. */
			const display = getComputedStyle(el).display;
			const children = [...el.children];
			if ((display === 'grid' || display === 'flex') && children.length > 1 && children.length < 7 && el.clientHeight > 120) return children;
			return [el];
		}
		root.querySelectorAll('[data-ymc-section]').forEach(section => {
			if (SKIP_REVEAL.indexOf(section.dataset.ymcSection) > -1) return;
			/* The padded element is the section's content; the rest are backdrops. */
			const container = [...section.children].find(child => parseFloat(getComputedStyle(child).paddingTop) > 20);
			if (!container) return;
			let i = 0;
			[...container.children].forEach(child => collect(child).forEach(el => {
				el.classList.add('ymc-reveal');
				el.style.setProperty('--ymc-reveal-delay', (i++ * 90) + 'ms');
				revealed.push(el);
			}));
		});
		let observed = false;
		const watcher = new IntersectionObserver(function (entries) {
			observed = true;
			entries.forEach(entry => {
				if (!entry.isIntersecting) return;
				entry.target.classList.add('is-visible');
				watcher.unobserve(entry.target);
			});
		}, { rootMargin: '0px 0px -10% 0px', threshold: 0.05 });
		revealed.forEach(el => watcher.observe(el));
		/* If the observer never reports, show everything rather than hide it. */
		setTimeout(() => { if (!observed) revealed.forEach(el => el.classList.add('is-visible')); }, 1500);
	}
	/* The sponsorship packages advance on their own, stop the moment someone
	   takes hold of them, and can be opened out into the full list. */
	const packages = root.querySelector('.ymc-ref-packages');
	if (packages && packages.children.length > 1) {
		const wrap = document.createElement('div');
		wrap.className = 'ymc-ref-carousel';
		packages.parentNode.insertBefore(wrap, packages);
		wrap.appendChild(packages);

		const nav = document.createElement('div');
		nav.className = 'ymc-ref-carousel__nav';
		nav.innerHTML = '<button type="button" class="ymc-ref-carousel__btn" data-ymc-carousel="prev" aria-label="Previous packages"><i class="fas fa-arrow-left"></i></button>'
			+ '<button type="button" class="ymc-ref-carousel__btn" data-ymc-carousel="next" aria-label="Next packages"><i class="fas fa-arrow-right"></i></button>'
			+ '<button type="button" class="ymc-ref-carousel__all" data-ymc-carousel="all" aria-expanded="false">View all (7)</button>';
		wrap.appendChild(nav);

		const prev = nav.querySelector('[data-ymc-carousel="prev"]');
		const next = nav.querySelector('[data-ymc-carousel="next"]');
		const all = nav.querySelector('[data-ymc-carousel="all"]');

		let paused = false;
		let resume = null;
		/* Taking hold stops the automatic advance for a while. */
		function hold() {
			paused = true;
			clearTimeout(resume);
			resume = setTimeout(function () { paused = false; }, 9000);
		}

		const step = function () { return packages.children[0].getBoundingClientRect().width + 16; };
		const atEnd = function () { return packages.scrollLeft >= packages.scrollWidth - packages.clientWidth - 4; };
		const expanded = function () { return wrap.classList.contains('is-expanded'); };

		function syncButtons() {
			prev.disabled = expanded() || packages.scrollLeft <= 4;
			next.disabled = expanded() || atEnd();
		}
		prev.addEventListener('click', function () { packages.scrollBy({ left: -step() }); hold(); });
		next.addEventListener('click', function () { packages.scrollBy({ left: step() }); hold(); });
		all.addEventListener('click', function () {
			const open = wrap.classList.toggle('is-expanded');
			all.setAttribute('aria-expanded', String(open));
			all.textContent = open ? 'Show less' : 'View all (' + packages.children.length + ')';
			if (!open) packages.scrollTo({ left: 0 });
			syncButtons();
			hold();
		});
		packages.addEventListener('scroll', syncButtons, { passive: true });
		all.textContent = 'View all (' + packages.children.length + ')';
		syncButtons();

		if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			let timer = null;
			function advance() {
				if (paused || expanded() || !packages.offsetParent) return;
				/* Back to the first package once the last one has been seen. */
				packages.scrollTo({ left: atEnd() ? 0 : packages.scrollLeft + step() });
			}
			function start() { if (!timer) timer = setInterval(advance, 4500); }
			function stop() { clearInterval(timer); timer = null; }
			packages.addEventListener('pointerenter', function () { paused = true; });
			packages.addEventListener('pointerleave', function () { paused = false; });
			packages.addEventListener('focusin', function () { paused = true; });
			packages.addEventListener('touchstart', hold, { passive: true });
			packages.addEventListener('wheel', hold, { passive: true });
			nav.addEventListener('pointerenter', function () { paused = true; });
			nav.addEventListener('pointerleave', function () { paused = false; });
			/* Only run it while the section is actually on screen. */
			new IntersectionObserver(function (entries) {
				entries.forEach(function (e) { return e.isIntersecting ? start() : stop(); });
			}, { threshold: 0.2 }).observe(packages);
		}
	}
})();
