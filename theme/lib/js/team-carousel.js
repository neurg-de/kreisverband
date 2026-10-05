/**
 * Compact team rows, department tabs and an optional idle slideshow.
 *
 * @package Neurg_Kreisverband
 */

(function () {
    'use strict';

    document.querySelectorAll( '[data-team-carousel]' ).forEach(
        function (root) {
			var tabs   = Array.from( root.querySelectorAll( '[role="tab"]' ) );
			var panels = Array.from( root.querySelectorAll( '.gk-team__panel' ) );
			if ( ! panels.length) {
				return;
			}
			var tablist       = root.querySelector( '[role="tablist"]' );
			var controls      = root.querySelector( '.gk-team__controls' );
			var navigation    = root.querySelector( '.gk-team__navigation' );
			var status        = root.querySelector( '.gk-team__status' );
			var play          = root.querySelector( '[data-team-play]' );
			var expand        = root.querySelector( '[data-team-expand]' );
			var motion        = window.matchMedia( '(prefers-reduced-motion: reduce)' );
			var active        = 0;
			var expanded      = false;
			var paused        = motion.matches;
			var keyboardFocus = false;
			var visible       = false;
			var resumeAt      = 0;
			var timer;
			var interval = 6000;

			function track() {
				return panels[active].querySelector( '.gk-team__track' ); }

			function updateStatus() {
				var row            = track();
				var cards          = Array.from( row.children );
				var bounds         = row.getBoundingClientRect();
				var shown          = cards.map(
                    function (card, index) {
                        var rect = card.getBoundingClientRect();
                        return rect.left >= bounds.left - 2 && rect.right <= bounds.right + 2 ? index + 1 : 0;
                    }
                ).filter( Boolean );
				status.textContent = shown.length ? shown[0] + '–' + shown[shown.length - 1] + ' von ' + cards.length : cards.length + ' Mitglieder';
			}

			function canRun() {
				return ! expanded && ! paused && ! keyboardFocus && visible && ! document.hidden;
			}

			function schedule() {
				clearTimeout( timer );
				if ( ! canRun()) {
					return;
				}
				timer = setTimeout(
                    function () {
                        if ( ! canRun()) {
                            return;
                        }
                        advance( 1 );
                        schedule();
                    },
                    Math.max( interval, resumeAt - Date.now() )
                );
			}

			function interact() {
				resumeAt = Date.now() + 12000;
				schedule();
			}

			function activate(index, atEnd) {
				active = (index + panels.length) % panels.length;
				panels.forEach(
                    function (panel, i) {
                        panel.hidden = i !== active;
                        tabs[i].classList.toggle( 'is-active', i === active );
                        tabs[i].setAttribute( 'aria-selected', String( i === active ) );
                        tabs[i].tabIndex = i === active ? 0 : -1;
                    }
                );
				var row = track();
				row.scrollTo( {left: atEnd ? row.scrollWidth : 0, behavior : 'instant'} );
				updateStatus();
			}

			function advance(direction) {
				var row = track();
				var max = row.scrollWidth - row.clientWidth;
				if (direction > 0 && row.scrollLeft >= max - 2) {
					activate( active + 1, false );
				} else if (direction < 0 && row.scrollLeft <= 2) {
					activate( active - 1, true );
				} else {
					var cards = row.children;
					var step  = cards.length > 1 ? cards[1].offsetLeft - cards[0].offsetLeft : row.clientWidth;
					var gap   = parseFloat( getComputedStyle( row ).columnGap ) || 0;
					var count = Math.max( 1, Math.floor( (row.clientWidth + gap + 1) / step ) );
					var first = Math.round( row.scrollLeft / step );
					row.scrollTo(
                        {
							left: Math.max( 0, Math.min( max, (first + direction * count) * step ) ),
							behavior: motion.matches ? 'instant' : 'smooth'
                        }
					);
				}
			}

			tabs.forEach(
                function (tab, index) {
                    tab.addEventListener(
                        'click',
                        function () {
                            activate( index, false ); interact(); }
                    );
                    tab.addEventListener(
                        'keydown',
                        function (event) {
                            var next = index;
                            if (event.key === 'ArrowRight') {
                                next++;
                            } else if (event.key === 'ArrowLeft') {
                                next--;
                            } else if (event.key === 'Home') {
                                next = 0;
                            } else if (event.key === 'End') {
                                next = tabs.length - 1;
                            } else {
                                return;
                            }
                            event.preventDefault();
                            activate( next, false );
                            tabs[active].focus();
                            interact();
                        }
                    );
                }
			);

			root.querySelector( '[data-team-prev]' ).addEventListener(
                'click',
                function () {
                    advance( -1 ); interact(); }
            );
			root.querySelector( '[data-team-next]' ).addEventListener(
                'click',
                function () {
                    advance( 1 ); interact(); }
            );
			function updatePlay() {
				play.textContent = paused ? 'Automatik starten' : 'Automatik pausieren';
				play.setAttribute( 'aria-pressed', String( paused ) );
			}
			play.addEventListener(
                'click',
                function () {
                    paused = ! paused;
                    // An explicit start also works while this button retains focus.
                    keyboardFocus = false;
                    resumeAt      = 0;
                    updatePlay();
                    schedule();
                }
            );
			motion.addEventListener(
                'change',
                function () {
                    if (motion.matches) {
                        paused = true;
                    }
                    updatePlay();
                    schedule();
                }
			);
			expand.addEventListener(
                'click',
                function () {
                    expanded = ! expanded;
                    root.classList.toggle( 'is-expanded', expanded );
                    expand.setAttribute( 'aria-expanded', String( expanded ) );
                    expand.textContent = expanded ? 'Kompakt anzeigen' : 'Alle anzeigen';
                    tablist.hidden     = expanded || tabs.length < 2;
                    navigation.hidden  = expanded;
                    panels.forEach(
                        function (panel, i) {
                            panel.hidden = ! expanded && i !== active;
                            panel.setAttribute( 'role', expanded ? 'region' : 'tabpanel' );
                            panel.setAttribute( 'aria-labelledby', expanded || tabs.length < 2 ? panel.id.replace( '-panel-', '-label-' ) : tabs[i].id );
                            panel.querySelector( '.gk-team__track' ).tabIndex = expanded ? -1 : 0;
                        }
                    );
                    if ( ! expanded) {
                        activate( active, false );
                        (tabs.length > 1 ? tabs[active] : track()).focus( {preventScroll : true} );
                        root.scrollIntoView( {block: 'start', behavior: motion.matches ? 'instant' : 'smooth'} );
                    }
                    interact();
                }
			);

			root.addEventListener(
                'pointermove',
                function (event) {
                    if (event.pointerType === 'mouse') {
                        interact();
                    }
                }
			);
			root.addEventListener(
                'keydown',
                function () {
                    keyboardFocus = true;
                    schedule();
                }
            );
			root.addEventListener(
                'focusin',
                function () {
                    keyboardFocus = document.activeElement.matches( ':focus-visible' );
                    schedule();
                }
            );
			root.addEventListener(
                'focusout',
                function () {
                    setTimeout(
                        function () {
                            if ( ! root.contains( document.activeElement )) {
                                keyboardFocus = false;
                            }
                            schedule();
                        },
                        0
                    );
                }
            );
			root.addEventListener(
                'pointerdown',
                function () {
                    keyboardFocus = false;
                    interact();
                },
                {passive: true}
            );
			root.addEventListener( 'wheel', interact, {passive: true} );
			root.addEventListener( 'touchmove', interact, {passive: true} );
			document.addEventListener( 'visibilitychange', schedule );
			panels.forEach(
                function (panel, i) {
                    panel.setAttribute( 'role', 'tabpanel' );
                    panel.setAttribute( 'aria-labelledby', tabs.length > 1 ? tabs[i].id : panel.id.replace( '-panel-', '-label-' ) );
                    panel.querySelector( '.gk-team__track' ).addEventListener( 'scroll', updateStatus, {passive: true} );
                }
			);
			new ResizeObserver( updateStatus ).observe( root );
			new IntersectionObserver(
                function (entries) {
                    visible = entries[0].isIntersecting;
                    schedule();
                },
                {threshold: 0.25}
			).observe( root );

			root.classList.add( 'is-enhanced' );
			tablist.hidden  = tabs.length < 2;
			controls.hidden = false;
			activate( 0, false );
			updatePlay();
		}
    );
})();
