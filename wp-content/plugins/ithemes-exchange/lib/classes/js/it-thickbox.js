it_init_thickbox();

function it_init_thickbox() {
	jQuery("#TB_iframeContent", top.document).load(auto_resize_thickbox_height);
}

var auto_resize_thickbox_height = function() {
	var win = window.dialogArguments || opener || parent || top;
	
	if(((typeof noAutoResize == "undefined") || (noAutoResize != true)) && (typeof win.jQuery != "undefined")) {
		if(jQuery("#thickbox-content-container").is(":visible")) {
			var de = win.document.documentElement;
			var viewHeight = win.window.innerHeight || win.self.innerHeight || (de && de.clientHeight) || win.document.body.clientHeight;
			
			var maxHeight = parseInt(viewHeight * 0.8);
			
			var newHeight = jQuery("#thickbox-content-container").outerHeight();
			if(newHeight > maxHeight) {
				newHeight = maxHeight;
			}
			
			if(newHeight > 0) {
				win.jQuery("#TB_iframeContent").height(newHeight);
				
				if(win.jQuery("#TB_iframeContent").height() > jQuery("#thickbox-content-container").outerHeight()) {
					var newHeight = jQuery("#thickbox-content-container").outerHeight();
					if(newHeight > maxHeight) {
						newHeight = maxHeight;
					}
					
					win.jQuery("#TB_iframeContent").height(newHeight);
				}
				
				auto_center_thickbox();
			}
		}
		else {
			setTimeout(auto_resize_thickbox_height, 100);
		}
	}
}

function resize_thickbox_width(width) {
	var win = window.dialogArguments || opener || parent || top;
	
	win.jQuery("#TB_iframeContent").css({width: width + 'px'});
	win.jQuery("#TB_window").css({width: width + 'px'});
	
	auto_resize_thickbox_height();
}

function resize_thickbox(width, height) {
	var win = window.dialogArguments || opener || parent || top;
	var isIE6 = typeof document.body.style.maxHeight === "undefined";
	
	win.jQuery("#TB_iframeContent").css({width: width + 'px'});
	win.jQuery("#TB_window").css({width: width + 'px'});
	win.jQuery("#TB_window").css({marginLeft: '-' + parseInt((width / 2), 10) + 'px'});
	
	win.jQuery("#TB_iframeContent").height(height);
	if ( ! isIE6 ) {
		win.jQuery("#TB_window").css({marginTop: '-' + parseInt((height / 2), 10) + 'px'});
	}
}

function auto_center_thickbox() {
	var win = window.dialogArguments || opener || parent || top;
	
	var isIE6 = typeof document.body.style.maxHeight === "undefined";
	
	win.jQuery("#TB_window").css({marginLeft: '-' + parseInt((win.jQuery("#TB_window").width() / 2), 10) + 'px'});
	
	if ( ! isIE6 ) {
		win.jQuery("#TB_window").css({marginTop: '-' + parseInt((win.jQuery("#TB_window").height() / 2), 10) + 'px'});
	}
}

function close_thickbox() {
	var win = window.dialogArguments || opener || parent || top;
	win.tb_remove();
}
