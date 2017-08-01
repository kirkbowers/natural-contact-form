/**
 * Custom script needed for the navigation tabs.
 *
 * This code is pilfered from, er, um, inspired by Options Framework.
 * That's the cool thing about open source, eh?
 * My deepest gratitude to Devin Price for a truly wonderful plugin.
 */

jQuery(document).ready(function($) {
	// Loads tabbed sections if they exist
	if ( $('.nfc-nav-tab-wrapper').length > 0 ) {
		options_framework_tabs();
	}

	function options_framework_tabs() {
		var $group = $('.group'),
			$navtabs = $('.nfc-nav-tab-wrapper a'),
			active_tab = '';

		// Hides all the .group sections to start
		$group.hide();

		active_tab = '';

		// Find if a selected tab is saved in localStorage
		if ( typeof(localStorage) != 'undefined' ) {
			active_tab = localStorage.getItem('natural-contact-form-active_tab');
		}

		// If active tab is saved and exists, load it's .group
		if ( (active_tab !== '') && $(active_tab).length ) {
			$(active_tab).fadeIn();
			$(active_tab + '-tab').addClass('nav-tab-active');
		} else {
			$('.group:first').fadeIn();
			$('.nav-tab-wrapper a:first').addClass('nav-tab-active');
		}

		// Bind tabs clicks
		$navtabs.click(function(e) {
			e.preventDefault();

			// Remove active class from all tabs
			$navtabs.removeClass('nav-tab-active');

			$(this).addClass('nav-tab-active').blur();

			if (typeof(localStorage) != 'undefined' ) {
				localStorage.setItem('natural-contact-form-active_tab', $(this).attr('href') );
			}

			var selected = $(this).attr('href');

			$group.hide();
			$(selected).fadeIn();

		});
	}
});
