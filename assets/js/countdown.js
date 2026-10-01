/**
 * The kickoff countdown in the hero.
 *
 * Reads the date from the markup, so the page can be edited without touching
 * this file. If the date has passed, the cells read zero.
 */
( function () {
	'use strict';

	var box = document.querySelector( '.ymc-count[data-kickoff]' );

	if ( ! box ) {
		return;
	}

	var kickoff = new Date( box.getAttribute( 'data-kickoff' ) ).getTime();
	var cells = {
		days: box.querySelector( '[data-unit="days"]' ),
		hours: box.querySelector( '[data-unit="hours"]' ),
		min: box.querySelector( '[data-unit="min"]' ),
		sec: box.querySelector( '[data-unit="sec"]' )
	};

	function pad( value ) {
		return value < 10 ? '0' + value : String( value );
	}

	function tick() {
		var left = Math.max( 0, kickoff - Date.now() );
		var seconds = Math.floor( left / 1000 );

		cells.days.textContent = String( Math.floor( seconds / 86400 ) );
		cells.hours.textContent = pad( Math.floor( seconds / 3600 ) % 24 );
		cells.min.textContent = pad( Math.floor( seconds / 60 ) % 60 );
		cells.sec.textContent = pad( seconds % 60 );
	}

	tick();
	setInterval( tick, 1000 );
}() );
