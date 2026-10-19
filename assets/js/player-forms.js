/* Sections arrive as you reach them, matching the front page's reveal. */
(function () {
	const page = document.querySelector('.ymcf-page');
	if (!page) return;
	if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
	if (!('IntersectionObserver' in window)) return;

	const TARGETS = '.ymcf-hero > *, .ymcf-section-heading, .ymcf-cards > *, .ymcf-guardian, .ymcf-bundle, .ymcf-contact, .ymcf-footnote';
	const revealed = [];
	let i = 0;

	page.querySelectorAll(TARGETS).forEach(el => {
		el.classList.add('ymcf-reveal');
		el.style.setProperty('--ymcf-reveal-delay', (i++ * 90) + 'ms');
		revealed.push(el);
	});
	if (!revealed.length) return;

	let observed = false;
	const watcher = new IntersectionObserver(function (entries) {
		observed = true;
		entries.forEach(entry => {
			if (!entry.isIntersecting) return;
			entry.target.classList.add('is-visible');
			watcher.unobserve(entry.target);
		});
	}, { rootMargin: '0px 0px -10% 0px', threshold: 0.05 });

	/* Anything already scrolled past (an anchor landing) is shown at once. */
	revealed.forEach(el => {
		if (el.getBoundingClientRect().bottom < 0) {
			el.classList.add('is-visible');
			return;
		}
		watcher.observe(el);
	});

	/* If the observer never reports, show everything rather than hide it. */
	setTimeout(() => { if (!observed) revealed.forEach(el => el.classList.add('is-visible')); }, 1500);
})();
