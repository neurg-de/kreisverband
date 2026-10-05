/**
 * Keyboard and drag ordering for per-OV controls.
 *
 * @package Neurg_Kreisverband
 */

(function () {
    'use strict';
    document.querySelectorAll( '.gk-ov-order' ).forEach(
        function (list) {
			var dragged;
			function announce(row) {
				var status = document.getElementById( 'gk-ov-order-status' );
				if (status) {
					status.textContent = row.querySelector( 'span' ).textContent + ': ' + (Array.from( list.children ).indexOf( row ) + 1) + '/' + list.children.length;
				}
			}
			list.addEventListener(
                'click',
                function (event) {
                    var button = event.target.closest( 'button' );
                    if ( ! button) {
                        return; }
                    var row = button.closest( 'li' );
                    if (button.classList.contains( 'gk-ov-up' ) && row.previousElementSibling) {
                        list.insertBefore( row, row.previousElementSibling );
                    } else if (button.classList.contains( 'gk-ov-down' ) && row.nextElementSibling) {
                        list.insertBefore( row.nextElementSibling, row );
                    }
                    button.focus();
                    announce( row );
                }
			);
			list.addEventListener(
                'dragstart',
                function (event) {
                    dragged = event.target.closest( 'li' );
                    event.dataTransfer.setData( 'text/plain', dragged.querySelector( 'input' ).value );
                }
			);
			list.addEventListener(
                'dragover',
                function (event) {
                    event.preventDefault(); }
            );
			list.addEventListener(
                'drop',
                function (event) {
                    event.preventDefault();
                    var target = event.target.closest( 'li' );
                    if (dragged && target && target.parentElement === list && dragged !== target) {
                        list.insertBefore( dragged, target );
                        announce( dragged );
                    }
                    dragged = null;
                }
			);
		}
    );
}());
