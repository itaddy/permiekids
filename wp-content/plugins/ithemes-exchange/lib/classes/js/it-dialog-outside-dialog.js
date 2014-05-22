/*
Written by Chris Jean for ithemes.com
Version: 1.0.1

Loosely based on Thickbox version 3.1.

Version History
	1.0.0 - 2012-07-26 - Chris Jean
		Initial version
	1.0.1 - 2013-04-04 - Chris Jean
		Fixed a bug that caused issues with iframe identification in Firefox 20.
*/


var it_dialog = {};

( function( $ ) {
	it_dialog = {
		default_settings: {
			width:     'auto',
			max_width: '',
			height:    'auto',
			modal:     ''
		},
		
		init: function( selector ) {
			if ( 'undefined' == typeof this.preloader ) {
				this.preloader = new Image();
				this.preloader.src = it_dialog_vars.loading_image;
			}
			
			var doc = $(document);
			
			if ( 'undefined' != typeof doc.on ) {
				doc.on( 'click', selector, this.handle_click );
			} else if ( 'undefined' != typeof doc.delegate ) {
				doc.delegate( selector, 'click', this.handle_click );
			} else {
				$(selector).live( 'click', this.handle_click );
			}
		},
		
		handle_click: function() {
			it_dialog.caption = this.title || this.name || '';
			it_dialog.url = this.href || this.alt || '';
			
			if ( '' == it_dialog.url )
				return false;
			
			it_dialog.parse_settings();
			it_dialog.show();
			
			this.blur();
			
			return false;
		},
		
		parse_settings: function() {
			if ( -1 == this.url.indexOf( '?' ) )
				return;
			
			
			// Clone default_settings
			this.settings = jQuery.extend( {}, this.default_settings );
			
			
			var parts = this.url.split( '?', 2 );
			
			this.base_url = parts[0];
			var pairs = parts[1].split( /[;&]/ );
			
			var query_args = [];
			
			for ( var i = 0; i < pairs.length; i++ ) {
				var key_val = pairs[i].split( '=' );
				
				if ( ! key_val || ( key_val.length != 2 ) ) {
					query_args.push( pairs[i] );
					continue;
				}
				
				var matches = key_val[0].match( /^it-dialog-(.+)/ );
				
				if ( null == matches ) {
					query_args.push( pairs[i] );
					continue;
				}
				
				var key = unescape( matches[1] ).replace( '-', '_' );
				var val = unescape( key_val[1] );
				
				this.settings[key] = val.replace(/\+/g, ' ');
			}
			
			if ( query_args.length > 0 )
				this.url = this.base_url + '?' + query_args.join( '&' );
		},
		
		show: function() {
			try {
				if ( 'undefined' == typeof this.body )
					this.body = $('body');
				if ( 'undefined' == typeof this.main_window )
					this.main_window = $('html');
				
				this.show_overlay();
				this.show_loader();
				
				this.load_window();
			}
			catch( e ) {
				console.log( e );
			}
		},
		
		show_overlay: function() {
			if ( 'undefined' == typeof this.overlay ) {
				this.body.append( '<div id="it-dialog-overlay"></div>' );
				this.overlay = $('#it-dialog-overlay');
				
				if ( '' == this.settings.modal )
					this.overlay.click( it_dialog.remove );
			}
			
			this.overlay.show();
		},
		
		show_loader: function() {
			if ( 'undefined' == typeof this.loader ) {
				this.body.append( '<div id="it-dialog-loader"><img src="' + this.preloader.src + '" /></div>' );
				this.loader = $('#it-dialog-loader');
			}
			
			this.loader.show();
		},
		
		show_window: function() {
			if ( 'undefined' == typeof this.window ) {
				this.load_window();
				return;
			}
			
			this.window.show();
			
			// Call update_size twice as this seems to fix some auto-sizing issues.
			this.update_size();
			this.update_size();
			
			$(window).resize( it_dialog.update_size );
		},
		
		load_window: function() {
			if ( 'undefined' != typeof this.window )
				this.window.remove();
			
			
			// Each iframe needs a unique name in order for it to load.
			if ( 'undefined' == typeof this.iframe_count )
				this.iframe_count = 1;
			
			this.iframe_name = "it_dialog_iframe_content_" + this.iframe_count;
			this.iframe_count++;
			
			
			var markup = '<div id="it-dialog-window">';
			
			if ( '' == this.settings.modal ) {
				markup += '<div id="it-dialog-window-titlebar">';
				
				if ( '' != this.caption )
					markup += '<div id="it-dialog-caption">' + this.caption + '</div>';
				
				markup += '<div id="it-dialog-close-button-wrapper">';
				markup += '<a id="it-dialog-close-button" href="#" title="' + it_dialog_vars.close_message + '"><img src="' + it_dialog_vars.close_image + '" /></a>';
				markup += '</div>';
				markup += '</div>';
			}
			
			markup += '<iframe id="it-dialog-iframe" frameborder="0" hspace="0" src="' + this.url + '&header=header" name="' + this.iframe_name + '" onload="it_dialog.handle_iframe_load()">' + it_dialog_vars.no_iframes_message + '</iframe>';
			markup += '</div>';
			
			
			this.body.append( markup );
			
			this.window = $('#it-dialog-window');
			this.iframe = $('#it-dialog-iframe');
			this.admin_bar = $('#wpadminbar');
			
			
			if ( '' == this.settings.modal )
				$("#it-dialog-close-button").click( it_dialog.remove );
		},
		
		handle_iframe_load: function() {
			var iframe;
			
			if ( 'undefined' != typeof frames[it_dialog.iframe_name] )
				iframe = frames[it_dialog.iframe_name].document;
			else if ( ( 'undefined' != typeof document[it_dialog.iframe_name] ) && ( 'undefined' != typeof document[it_dialog.iframe_name].document ) )
				iframe = document[it_dialog.iframe_name].document;
			else
				iframe = document.getElementById( 'it-dialog-iframe' ).contentDocument;
			
			
			if ( 'undefined' != typeof it_dialog.loader ) {
				it_dialog.loader.remove();
				delete it_dialog.loader;
			}
			
			it_dialog.container = $('#it-dialog-content-container', iframe);
			it_dialog.iframe_body = $('body', iframe);
			
			it_dialog.show_window();
		},
		
		remove: function() {
			if ( 'undefined' != typeof it_dialog.window ) {
				it_dialog.window.remove();
				delete it_dialog.window;
				delete it_dialog.iframe;
				delete it_dialog.container;
				delete it_dialog.iframe_body;
				delete it_dialog.admin_bar;
			}
			if ( 'undefined' != typeof it_dialog.loader ) {
				it_dialog.loader.remove();
				delete it_dialog.loader;
			}
			if ( 'undefined' != typeof it_dialog.overlay ) {
				it_dialog.overlay.remove();
				delete it_dialog.overlay;
			}
		},
		
		update_size: function( force_update ) {
			if ( 'undefined' == typeof it_dialog.container )
				return;
			
			
			var admin_bar_height = ( it_dialog.admin_bar.length ) ? it_dialog.admin_bar.outerHeight() : 0;
			var window_border_height = it_dialog.window.outerHeight() - it_dialog.iframe.height();
			var window_edge_gap = 50;
			
			
			var main_window_width = it_dialog.main_window.width();
			var current_width = it_dialog.iframe.width();
			var width = 0;
			
			var max_width = main_window_width;
			
			if ( main_window_width > 400 )
				max_width -= window_edge_gap;
			
			if ( ( '' != it_dialog.settings.max_width ) && ( it_dialog.settings.max_width < max_width ) )
				max_width = it_dialog.settings.max_width;
			
			
			it_dialog.iframe.width( max_width );
			it_dialog.container.css( {width: 'auto'} );
			
			if ( 'auto' == it_dialog.settings.width )
				width = it_dialog.container.outerWidth();
			else if ( 'full' == it_dialog.settings.width )
				width = max_width;
			else
				width = it_dialog.settings.width;
			
			if ( width < 50 )
				width = 50;
			else if ( width > max_width )
				width = max_width;
			
			var width_difference = width - current_width;
			
			if ( ( width_difference <= 0 ) && ( width_difference > -100 ) && ( current_width <= max_width ) )
				width = current_width;
			
			it_dialog.iframe.width( width );
			
			
			var main_window_height = it_dialog.main_window.outerHeight();
			var height = 0;
			
			var max_height = main_window_height - admin_bar_height - window_border_height;
			
			if ( main_window_height > 400 )
				max_height -= window_edge_gap;
			
			
			it_dialog.iframe.height( max_height );
			
			if ( 'auto' == it_dialog.settings.height )
				height = it_dialog.container.outerHeight();
			else if ( 'full' == it_dialog.settings.height )
				height = max_height;
			else
				height = it_dialog.settings.height;
			
			
			if ( height < 50 )
				height = 50;
			else if ( height > max_height )
				height = max_height
			
			
			it_dialog.iframe.height( height );
			
			
			// Remove horizontal scrollbar if possible
			var container_width = it_dialog.container.outerWidth();
			var iframe_body_width = it_dialog.iframe_body.width();
			
			if ( container_width > it_dialog.iframe_body.width() ) {
				width = width + container_width - iframe_body_width;
				
				if ( width > max_width )
					width = max_width;
				
				it_dialog.iframe.width( width );
			}
			
			it_dialog.container.css( {width: '100%'} );
			
			
			// Remove vertical scrollbar if possible
			var container_height = it_dialog.container.outerHeight();
			var iframe_body_height = it_dialog.iframe_body.height();
			
			if ( container_height > it_dialog.iframe_body.height() ) {
				height = height + container_height - iframe_body_height;
				
				if ( height > max_height )
					height = max_height;
				
				
				it_dialog.iframe.height( height );
			}
			
			
			var position_change = {
				marginLeft: '-' + Math.ceil( it_dialog.window.outerWidth() / 2 ) + 'px',
				marginTop:  '-' + Math.ceil( ( it_dialog.window.outerHeight() - admin_bar_height ) / 2 ) + 'px'
			};
			
			it_dialog.window.css( position_change );
		},
		
		show_loading_message: function( message ) {
			it_dialog.container.children().each(
				function() {
					$(this).hide();
				}
			);
			
			
			var markup = '<div id="it-dialog-loading-message">';
			
			if ( ( 'undefined' != typeof message ) && ( '' != message ) )
				markup += '<p>' + message + '</p>';
			
			markup += '<img src="' + it_dialog.preloader.src + '" /></div>';
			
			it_dialog.container.append( markup );
			
			
			it_dialog.update_size();
			
			
			return false;
		}
	};
	
	it_dialog.init( '.it-dialog' );
} )( jQuery );
