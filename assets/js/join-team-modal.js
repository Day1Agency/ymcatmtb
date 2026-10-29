/**
 * Shows a player what to pick in Givebutter before sending them there.
 *
 * Givebutter's checkout gives no guidance, so players have chosen Captain Only
 * tickets and captains have registered as "Go solo" - both needing manual
 * repair afterwards. Three lines of instruction beforehand prevent it.
 *
 * Progressive enhancement: without JavaScript the button is an ordinary link
 * straight to registration, which still works.
 */
(() => {
	const trigger = document.querySelector('a[href*="ymc-adult-player-registration"][class*="scp0"]');
	if (!trigger) return;

	/* Always the checkout entry point, never whatever the button happens to hold.
	   The campaign root drops people on the overview page and they have to find
	   their way in again; /register opens the form itself. */
	const registerUrl = 'https://givebutter.com/ymc-adult-player-registration/register';
	let dialog = null;
	let lastFocused = null;

	function build() {
		const wrap = document.createElement('div');
		wrap.className = 'ymc-jm';
		wrap.hidden = true;
		wrap.innerHTML = `
			<div class="ymc-jm-backdrop" data-close></div>
			<div class="ymc-jm-panel" role="dialog" aria-modal="true" aria-labelledby="ymc-jm-title">
				<button type="button" class="ymc-jm-close" data-close aria-label="Close">&times;</button>
				<p class="ymc-jm-eyebrow">Adult tournament &middot; $180 per player</p>
				<h2 id="ymc-jm-title">Joining your captain&rsquo;s team</h2>
				<p class="ymc-jm-intro">Have your captain&rsquo;s team name ready. Registration takes a few minutes and finishes on Givebutter.</p>
				<ol class="ymc-jm-steps">
					<li><strong>Sign in or create your Givebutter account.</strong></li>
					<li><strong>Choose &ldquo;Join a team&rdquo;</strong>, then select your captain&rsquo;s team from the list.</li>
					<li><strong>Choose &ldquo;Adult Tournament &mdash; Player&rdquo;</strong> and complete your registration.</li>
				</ol>
				<p class="ymc-jm-note">Your <strong>$180</strong> counts toward your team&rsquo;s <strong>$15,000</strong> goal. If your captain&rsquo;s team is not in the list yet, wait for them to confirm it has been set up.</p>
				<div class="ymc-jm-actions">
					<a class="ymc-jm-go" href="${registerUrl}" target="_blank" rel="noopener">Continue to registration</a>
					<a class="ymc-jm-alt" href="/need-a-team/">Don&rsquo;t have a team yet?</a>
				</div>
			</div>`;
		document.body.appendChild(wrap);
		wrap.addEventListener('click', (e) => { if (e.target.closest('[data-close]')) close(); });
		return wrap;
	}

	function open(event) {
		event.preventDefault();
		lastFocused = document.activeElement;
		if (!dialog) dialog = build();
		dialog.hidden = false;
		document.body.classList.add('ymc-jm-open');
		dialog.querySelector('.ymc-jm-go').focus();
		document.addEventListener('keydown', onKey);
	}

	function close() {
		if (!dialog) return;
		dialog.hidden = true;
		document.body.classList.remove('ymc-jm-open');
		document.removeEventListener('keydown', onKey);
		if (lastFocused) lastFocused.focus();
	}

	/* Tab is kept inside the panel: a dialog that lets focus wander behind it is
	   unusable with a keyboard or screen reader. */
	function onKey(e) {
		if (e.key === 'Escape') { close(); return; }
		if (e.key !== 'Tab') return;
		const items = dialog.querySelectorAll('a[href], button');
		if (!items.length) return;
		const first = items[0];
		const last = items[items.length - 1];
		if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
		else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
	}

	trigger.addEventListener('click', open);
})();
