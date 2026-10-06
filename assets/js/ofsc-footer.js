/**
 * Quick-links repeater for the Footer Settings meta box.
 *
 * Expects `window.ofscFooterLinks` to be defined by an inline script attached
 * before this file, providing the translated placeholder/button labels.
 */
( function () {
	'use strict';

	var ROW_CLASS = 'ofsc-footer-link-row';
	var INDEX_PATTERN = /ofsc_footer_links\[(\d+)\]/;

	var config = window.ofscFooterLinks || {};
	var strings = config.i18n || {};

	var repeater = document.getElementById( 'ofsc-footer-links-repeater' );
	var addButton = document.getElementById( 'ofsc-add-footer-link' );

	if ( ! repeater || ! addButton ) {
		return;
	}

	/**
	 * Highest index already present in the repeater, plus one.
	 *
	 * @return {number} Index that is guaranteed not to collide with an existing row.
	 */
	function nextIndex() {
		var highest = -1;
		var inputs = repeater.querySelectorAll( 'input[name*="ofsc_footer_links"]' );

		Array.prototype.forEach.call( inputs, function ( input ) {
			var match = INDEX_PATTERN.exec( input.name );

			if ( ! match ) {
				return;
			}

			var index = parseInt( match[ 1 ], 10 );

			if ( index > highest ) {
				highest = index;
			}
		} );

		return highest + 1;
	}

	/**
	 * @param {string} type      Input type attribute.
	 * @param {string} name      Field name, including the repeater index.
	 * @param {string} value     Initial value.
	 * @param {string} placeholder Placeholder label.
	 * @return {HTMLInputElement} The created input.
	 */
	function createInput( type, name, value, placeholder ) {
		var input = document.createElement( 'input' );

		input.type = type;
		input.name = name;
		input.value = value;
		input.placeholder = placeholder;

		return input;
	}

	/**
	 * @param {number} index Repeater index for the new row.
	 * @return {HTMLElement} A repeater row.
	 */
	function createRow( index ) {
		var row = document.createElement( 'div' );
		var remove = document.createElement( 'button' );

		row.className = ROW_CLASS;
		row.appendChild(
			createInput( 'text', 'ofsc_footer_links[' + index + '][text]', '', strings.text || '' )
		);
		row.appendChild(
			createInput( 'url', 'ofsc_footer_links[' + index + '][url]', '', strings.url || '' )
		);

		remove.type = 'button';
		remove.className = 'button ofsc-remove-link';
		remove.textContent = strings.remove || '';
		row.appendChild( remove );

		return row;
	}

	addButton.addEventListener( 'click', function () {
		repeater.appendChild( createRow( nextIndex() ) );
	} );

	repeater.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.ofsc-remove-link' );

		if ( ! button || ! repeater.contains( button ) ) {
			return;
		}

		var row = button.closest( '.' + ROW_CLASS );

		if ( row ) {
			row.remove();
		}
	} );
}() );
