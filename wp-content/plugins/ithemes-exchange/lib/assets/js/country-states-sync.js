/**
 * jQuery Country States Sync Plugin by iThemes
 * Plugin framework via <http://coding.smashingmagazine.com/2011/10/11/essential-jquery-plugin-patterns/>
*/
;(function ( $, window, document, undefined ) {
	// Create the defaults once
	var pluginName = 'itCountryStatesSync',
		defaults   = {
			stateWrapper      : '.it-exchange-state',
			stateFieldID      : '#it-exchange-address-state',
			action            : 'ite-country-states-update',
			templatePart      : '',
			clearTextValue    : false,
			ajaxUrl           : itExchangeAjaxCountryStatesAjaxURL,
			adminPrefix       : false,
			autoCompleteState : false,
			autoFocusState    : false
		};

	// The actual plugin constructor
	function Plugin( countrySelectElement, options ) {

		// Set element
		this.element = countrySelectElement;

		// jQuery has an extend method that merges the
		// contents of two or more objects, storing the
		// result in the first object. The first object
		// is generally empty because we don't want to alter
		// the default options for future instances of the plugin
		this.options = $.extend( {}, defaults, options) ;

		// Country Field ID
		this.options.countryFieldID = '#' + $(this.element).attr('id');

		this._defaults = defaults;
		this._name     = pluginName;

		this.init();
	}

	Plugin.prototype.init = function () {
		// Place initialization logic here
		// You already have access to the DOM element and
		// the options via the instance, e.g. this.element
		// and this.options

		$(this.element).on('change', this.options, this.updateStates );
	};

	Plugin.prototype.updateStates = function( event ) {

		var iteCountryStatesSyncPostData = {};

		iteCountryStatesSyncPostData.ite_base_country_ajax  = $(event.data.countryFieldID).val();
		iteCountryStatesSyncPostData.ite_base_state_ajax    = $(event.data.stateFieldID).val();
		iteCountryStatesSyncPostData.ite_state_name_ajax    = $(event.data.stateFieldID).attr('name');
		iteCountryStatesSyncPostData.ite_action_ajax        = event.data.action;
		iteCountryStatesSyncPostData.ite_template_part_ajax = event.data.templatePart;
		iteCountryStatesSyncPostData.ite_admin_prefix_ajax  = event.data.adminPrefix;
		iteCountryStatesSyncPostData.ite_clearTextValue     = event.data.clearTextValue;

		$.post(event.data.ajaxUrl, iteCountryStatesSyncPostData, function(response) {
			if (response) {
				var parentForm = $(event.data.countryFieldID).closest('form');
				$(event.data.stateWrapper, parentForm).html(response);
				if (event.data.autoCompleteState)
					$(event.data.stateFieldID).filter('select').selectToAutocomplete();
				if (event.data.autoFocusState) {
					if ( $(event.data.stateWrapper).children('.ui-autocomplete-input').length !== 0 ) {
						$(event.data.stateWrapper).children('.ui-autocomplete-input').focus();
					} else {
						$(event.data.stateFieldID).focus();
					}
				}
			}
		});
	}

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function ( options ) {
		return this.each(function () {
			if (!$.data(this, 'plugin_' + pluginName)) {
				$.data(this, 'plugin_' + pluginName,
				new Plugin( this, options ));
			}
		});
	}

})( jQuery, window, document );
