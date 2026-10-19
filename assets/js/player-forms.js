/* Sections arrive as you reach them, matching the front page's reveal. */
(function () {
	const page = document.querySelector('.ymcf-page');
	if (!page) return;
	if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
	if (!('IntersectionObserver' in window)) return;

	/* Each group staggers on its own, so later sections do not inherit the
	   delay of everything above them and arrive long after you get there. */
	const GROUPS = [
		'.ymcf-hero > *',
		'.ymcf-section-heading',
		'.ymcf-cards > *',
		'.ymcf-guardian',
		'.ymcf-bundle',
		'.ymcf-contact, .ymcf-footnote'
	];

	const revealed = [];
	GROUPS.forEach(selector => {
		let i = 0;
		page.querySelectorAll(selector).forEach(el => {
			el.classList.add('ymcf-reveal');
			el.style.setProperty('--ymcf-reveal-delay', (i++ * 60) + 'ms');
			revealed.push(el);
		});
	});
	if (!revealed.length) return;

	let observed = false;
	const watcher = new IntersectionObserver(function (entries) {
		observed = true;
		entries.forEach(entry => {
			/* A fast scroll or anchor jump can skip an element entirely, so
			   anything now above the viewport is shown rather than left hidden. */
			if (!entry.isIntersecting && entry.boundingClientRect.bottom > 0) return;
			entry.target.classList.add('is-visible');
			watcher.unobserve(entry.target);
		});
	}, { rootMargin: '0px 0px -4% 0px', threshold: 0.01 });

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
