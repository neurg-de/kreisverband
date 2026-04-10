/**
 * Neurg Kreisverband – Main Scripts
 */
(function($) {
    'use strict';

    // ── Mobile Navigation Toggle ────────────────────────────────────────────

    var $mobileNav = $('#nav-mobile');
    var $body = $('body');
    var $hamburger = $('.switch-menu');

    $hamburger.on('click', function(e) {
        e.preventDefault();
        var isOpen = $mobileNav.hasClass('is-open');
        $mobileNav.toggleClass('is-open');
        $body.toggleClass('nav-open');

        if (!isOpen) {
            // Opening: set aria-expanded and move focus to first link
            $hamburger.attr('aria-expanded', 'true');
            var $firstLink = $mobileNav.find('a').first();
            if ($firstLink.length) {
                $firstLink.focus();
            }
        } else {
            // Closing: set aria-expanded and return focus to hamburger
            $hamburger.attr('aria-expanded', 'false');
            $hamburger.focus();
        }
    });

    $('.mobile-overlay').on('click', function() {
        $mobileNav.removeClass('is-open');
        $body.removeClass('nav-open');
        $hamburger.attr('aria-expanded', 'false');
        $hamburger.focus();
    });


    // ── Priority+ Navigation (overflow → "Mehr" dropdown) ────────────────────

    (function() {
        var navs = document.querySelectorAll('#nav-desktop nav.nav-main, #nav-flyin nav.nav-main');
        if (!navs.length) return;

        navs.forEach(function(nav) {
            var ul = nav.querySelector(':scope > ul') || nav.querySelector(':scope > .nav-fallback > ul');
            if (!ul) return;

            // Create "Mehr" list item with dropdown
            var moreLi = document.createElement('li');
            moreLi.className = 'gk-nav-more';
            moreLi.innerHTML = '<button aria-expanded="false" aria-haspopup="true">Mehr</button><ul></ul>';
            var moreBtn = moreLi.querySelector('button');
            var moreUl = moreLi.querySelector('ul');

            ul.appendChild(moreLi);

            // Toggle dropdown
            moreBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                var open = moreUl.classList.toggle('is-open');
                moreBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });

            // Close on outside click
            document.addEventListener('click', function() {
                moreUl.classList.remove('is-open');
                moreBtn.setAttribute('aria-expanded', 'false');
            });

            // Close on Escape
            moreLi.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    moreUl.classList.remove('is-open');
                    moreBtn.setAttribute('aria-expanded', 'false');
                    moreBtn.focus();
                }
            });

            // Collect original items (excluding the "Mehr" item itself)
            var items = [];
            var children = ul.children;
            for (var i = 0; i < children.length; i++) {
                if (children[i] !== moreLi) {
                    items.push(children[i]);
                }
            }

            function reflow() {
                // Skip if nav is not visible (e.g. #nav-flyin before scroll)
                if (nav.offsetWidth === 0) return;

                // Reset: show all items, clear overflow list
                for (var i = 0; i < items.length; i++) {
                    items[i].classList.remove('gk-nav-hidden');
                }
                moreUl.innerHTML = '';
                moreLi.style.display = 'none';

                // Measure available width
                var navWidth = nav.getBoundingClientRect().width;
                // Sum widths of all sibling elements (logo, kv-back, OV nav, etc.)
                // that are NOT the main menu <ul> / .nav-fallback
                var usedByOthers = 0;
                var ulContainer = ul.parentElement.classList.contains('nav-fallback') ? ul.parentElement : ul;
                var siblings = nav.children;
                for (var j = 0; j < siblings.length; j++) {
                    var sib = siblings[j];
                    if (sib === ulContainer) continue; // skip the main menu list
                    if (sib.offsetWidth === 0) continue; // skip hidden elements (e.g. screen-reader headings)
                    var style = window.getComputedStyle(sib);
                    usedByOthers += sib.getBoundingClientRect().width
                        + parseFloat(style.marginLeft) + parseFloat(style.marginRight);
                }

                var available = navWidth - usedByOthers;
                var moreWidth = 90; // reserve space for "Mehr" button

                // Measure each item
                var totalWidth = 0;
                var breakIndex = -1;
                for (var i = 0; i < items.length; i++) {
                    totalWidth += items[i].getBoundingClientRect().width;
                    if (totalWidth > available - moreWidth) {
                        breakIndex = i;
                        break;
                    }
                }

                // If everything fits, no need for "Mehr"
                if (breakIndex === -1) {
                    // Double check: does everything really fit without "Mehr" reserve?
                    if (totalWidth <= available) return;
                    breakIndex = items.length - 1;
                }

                // Move overflowing items to dropdown
                moreLi.style.display = '';
                for (var i = breakIndex; i < items.length; i++) {
                    items[i].classList.add('gk-nav-hidden');
                    var clone = items[i].cloneNode(true);
                    clone.classList.remove('gk-nav-hidden');
                    // Flatten: only keep the top-level link, drop submenus
                    var submenus = clone.querySelectorAll('ul');
                    for (var j = 0; j < submenus.length; j++) submenus[j].remove();
                    moreUl.appendChild(clone);
                }
            }

            // Initial layout and resize handling
            reflow();
            var resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(reflow, 100);
            });

            // Expose reflow for deferred navs (fly-in)
            nav._gkReflow = reflow;
        });
    })();


    // ── Sticky Fly-in Navigation ────────────────────────────────────────────

    var $flyinNav = $('#nav-flyin');
    var flyinOffset = 300;

    $(window).on('scroll', function() {
        var scrollTop = $(this).scrollTop();

        // Show/hide fly-in nav
        if (scrollTop > flyinOffset) {
            var wasHidden = !$flyinNav.hasClass('is-visible');
            $flyinNav.addClass('is-visible');
            // Trigger Priority+ reflow on first show
            if (wasHidden) {
                var flyinNavEl = $flyinNav.find('nav.nav-main')[0];
                if (flyinNavEl && flyinNavEl._gkReflow) flyinNavEl._gkReflow();
            }
        } else {
            $flyinNav.removeClass('is-visible');
        }

        // Show/hide back-to-top
        if (scrollTop > 600) {
            $('.back-to-top').addClass('visible');
        } else {
            $('.back-to-top').removeClass('visible');
        }
    });


    // ── Back to Top ─────────────────────────────────────────────────────────

    $('.back-to-top').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({ scrollTop: 0 }, 400);
    });


    // ── Smooth scroll for anchor links ──────────────────────────────────────

    $('a[href^="#"]').not('.gk-lightbox-close, .suche a').on('click', function(e) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top - 60
            }, 400);
        }
    });


    // ── Desktop Search Toggle ──────────────────────────────────────────────

    var $searchPanel = $('.search-desktop');
    $('li.suche > a, #nav-flyin li.suche > a').on('click', function(e) {
        e.preventDefault();
        $searchPanel.toggleClass('is-open');
        if ($searchPanel.hasClass('is-open')) {
            $searchPanel.find('.seachphrase').focus();
        }
    });

    // Close search when the X link inside is clicked
    $searchPanel.find('a[href="#header"]').on('click', function(e) {
        e.preventDefault();
        $searchPanel.removeClass('is-open');
    });


    // ── OnePageNav for Story pages ──────────────────────────────────────────

    var $inhaltvz = $('.inhaltvz a');
    if ($inhaltvz.length) {
        $inhaltvz.on('click', function(e) {
            var href = $(this).attr('href');
            if (href && href.charAt(0) === '#') {
                var $target = $(href);
                if ($target.length) {
                    e.preventDefault();
                    $('html, body').animate({ scrollTop: $target.offset().top - 80 }, 600);
                }
            }
        });
    }


    // ── Lightbox for images ─────────────────────────────────────────────────

    // Auto-detect linked images in content and add lightbox behavior
    var $lightboxLinks = $('.entry-content a[href$=".jpg"], .entry-content a[href$=".jpeg"], .entry-content a[href$=".png"], .entry-content a[href$=".gif"], .entry-content a[href$=".webp"]');

    if ($lightboxLinks.length) {
        // Create lightbox overlay
        var $overlay = $('<div class="gk-lightbox-overlay" style="display:none">' +
            '<button class="gk-lightbox-close" aria-label="Schliessen">&times;</button>' +
            '<img class="gk-lightbox-img" src="" alt="" />' +
            '<p class="gk-lightbox-caption"></p>' +
            '</div>').appendTo('body');

        var $lbImg = $overlay.find('.gk-lightbox-img');
        var $lbCaption = $overlay.find('.gk-lightbox-caption');

        $lightboxLinks.on('click', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            var title = $(this).find('img').attr('alt') || $(this).attr('title') || '';
            $lbImg.attr('src', href);
            $lbCaption.text(title);
            $overlay.fadeIn(200);
            $body.css('overflow', 'hidden');
        });

        $overlay.on('click', function(e) {
            if (e.target === this || $(e.target).hasClass('gk-lightbox-close')) {
                $overlay.fadeOut(200);
                $body.css('overflow', '');
            }
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $overlay.is(':visible')) {
                $overlay.fadeOut(200);
                $body.css('overflow', '');
            }
        });
    }


    // ── Kreiskarte: Label Hover Sync ───────────────────────────────────────
    // Polygon and label are in different SVG groups, so CSS :hover siblings
    // don't work. Sync via JS: highlight label when polygon is hovered.

    $('#kreiskarte-municipalities a[data-ov-slug]')
        .on('mouseenter focusin', function() {
            var slug = $(this).data('ov-slug');
            $('#kreiskarte-labels text[data-slug="' + slug + '"]').addClass('kk-label--hover');
        })
        .on('mouseleave focusout', function() {
            var slug = $(this).data('ov-slug');
            $('#kreiskarte-labels text[data-slug="' + slug + '"]').removeClass('kk-label--hover');
        });


    // ── Responsive Tabs ─────────────────────────────────────────────────────

    if (typeof RESPONSIVEUI !== 'undefined' && RESPONSIVEUI.responsiveTabs) {
        RESPONSIVEUI.responsiveTabs();
    }


    // ── Sticky "Suchst du deinen Ortsverband?" bar ─────────────────────────
    // Shows when the user is above the Kreiskarte section, hides once they
    // scroll into or past it.

    (function() {
        var stickyBar = document.getElementById('gk-sticky-ov');
        var ovsSection = document.getElementById('ovs');
        if (!stickyBar || !ovsSection) return;

        // Small delay so the hero is fully painted before we start observing
        var hasScrolledPastHero = false;

        // Observer: fires when #ovs enters/exits the viewport.
        // rootMargin '0px 0px 100px 0px' expands the bottom edge so the
        // observer fires ~100px before the section scrolls into view,
        // preventing the sticky bar from overlapping the toolbar.
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (!entry.isIntersecting && hasScrolledPastHero) {
                    // Section is not in the (expanded) viewport —
                    // only show if section is below us, not above
                    var rect = entry.boundingClientRect;
                    if (rect.top > 0) {
                        stickyBar.hidden = false;
                        stickyBar.offsetHeight;
                        stickyBar.classList.add('is-visible');
                    } else {
                        stickyBar.classList.remove('is-visible');
                    }
                } else {
                    stickyBar.classList.remove('is-visible');
                }
            });
        }, { threshold: 0, rootMargin: '0px 0px 100px 0px' });

        observer.observe(ovsSection);

        // Don't show the bar until the user has scrolled past the hero
        $(window).on('scroll.stickyov', function() {
            if ($(this).scrollTop() > 200) {
                hasScrolledPastHero = true;
                // Re-check by triggering observer
                observer.unobserve(ovsSection);
                observer.observe(ovsSection);
            }
        });

        // When bar link is clicked, hide bar immediately
        $(stickyBar).find('a').on('click', function() {
            stickyBar.classList.remove('is-visible');
        });
    })();


    // ── Kreiskarte: Search, Toggle, Bottom Sheet ───────────────────────────

    var $kr = $('.kreiskarte-responsive');
    if ($kr.length) {
        var $search   = $kr.find('.gk-ov-search');
        var $chips    = $kr.find('.gk-ov-chip');
        var $noResult = $kr.find('.gk-ov-no-results');
        var $toggle   = $kr.find('.gk-ov-toggle');
        var $listView = $kr.find('.gk-ov-list-view');
        var $mapView  = $kr.find('.gk-ov-map-view');
        var $sheet    = $kr.find('.gk-ov-sheet');
        var showingMap = false;

        // Search filter
        $search.on('input', function() {
            var q = this.value.toLowerCase().trim();
            var visible = 0;
            $chips.each(function() {
                var name = $(this).attr('data-ov-name').toLowerCase();
                var match = !q || name.indexOf(q) !== -1;
                $(this).toggle(match);
                if (match) visible++;
            });
            $noResult.prop('hidden', visible > 0);

            // If user is searching, make sure list view is visible
            if (q && showingMap) {
                toggleView(false);
            }
        });

        // Map / List toggle
        function toggleView(toMap) {
            showingMap = toMap;
            $toggle.attr('aria-pressed', toMap ? 'true' : 'false');
            $toggle.find('.gk-ov-toggle__label').text(
                toMap ? $toggle.data('label-list') : $toggle.data('label-map')
            );
            $kr.toggleClass('gk-ov--map-active', toMap);
        }

        $toggle.on('click', function() {
            toggleView(!showingMap);
        });

        // Bottom sheet for mobile map taps
        if ($sheet.length) {
            var isMobile = function() { return window.innerWidth < 768; };
            var $sheetTitle = $sheet.find('.gk-ov-sheet__title');
            var $sheetCta   = $sheet.find('.gk-ov-sheet__cta');

            // Intercept SVG link clicks on mobile
            $kr.on('click', '.gk-ov-map-svg a[data-ov-name]', function(e) {
                if (!isMobile()) return; // desktop: normal navigation

                e.preventDefault();
                var name = $(this).data('ov-name');
                var url  = $(this).data('ov-url');

                $sheetTitle.text(name);
                $sheetCta.attr('href', url);
                $sheet.prop('hidden', false);
                // Trigger reflow then add active class for animation
                $sheet[0].offsetHeight;
                $sheet.addClass('gk-ov-sheet--open');
                $body.css('overflow', 'hidden');
            });

            function closeSheet() {
                $sheet.removeClass('gk-ov-sheet--open');
                $body.css('overflow', '');
                setTimeout(function() { $sheet.prop('hidden', true); }, 300);
            }

            $sheet.find('.gk-ov-sheet__close, .gk-ov-sheet__backdrop').on('click', closeSheet);
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $sheet.hasClass('gk-ov-sheet--open')) {
                    closeSheet();
                }
            });
        }
    }

    // ── Personenliste: OV Filter Tabs ────────────────────────────────────

    $(function() { $('.gk-abteilung').each(function() {
        var $container = $(this);
        var $buttons   = $container.find('[data-filter]');
        var $cards     = $container.find('.gk-abteilung__card');
        var $empty     = $container.find('.gk-abteilung__empty');

        if (!$buttons.length) return;

        $buttons.on('click', function() {
            var filter = $(this).data('filter');

            // Update active state
            $buttons.removeClass('is-active').attr('aria-selected', 'false').attr('aria-pressed', 'false');
            $(this).addClass('is-active').attr('aria-selected', 'true').attr('aria-pressed', 'true');

            // Toggle filtered class (hides OV labels when a specific OV is selected)
            $container.toggleClass('gk-abteilung--filtered', filter !== 'all');

            // Filter cards
            var visible = 0;
            $cards.each(function() {
                var ov   = $(this).data('ov');
                var show = filter === 'all' || ov === filter;
                $(this).prop('hidden', !show);
                if (show) visible++;
            });

            $empty.prop('hidden', visible > 0);
        });
    }); });

})(jQuery);
