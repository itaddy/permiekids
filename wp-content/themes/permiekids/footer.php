
	
</div> <!-- /container -->

<?php if (!is_front_page()) { ?>
	<div class="line-separator"></div>
		<div class="container">
			<div class="row">
				<div class="footer-widget">
					<?php if ( is_active_sidebar( 'footer-top-widget' ) ) : ?>
						<?php dynamic_sidebar( 'footer-top-widget' ); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
<?php } ?>
<div class="line-separator"></div>
<div class="container">
	<div class="row">
		<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
			<?php dynamic_sidebar( 'Footer Bottom Widget' ); ?>
		<?php endif; ?>	
	</div>
</div>


<div class="container">
	<div class="footer_menu">
		<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
			<?php dynamic_sidebar( 'Footer Menu Widget' ); ?>
		<?php endif; ?>	
	</div>
	<div class="designer_info">
		A Iron Bound Design
	</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function($){
  var _custom_media = true,
      _orig_send_attachment = wp.media.editor.send.attachment;

  $('.uploadbutton').click(function(e) {
    var send_attachment_bkp = wp.media.editor.send.attachment;
    var button = $(this);
    var id = button.attr('id').replace('_button', '');
    _custom_media = true;
    wp.media.editor.send.attachment = function(props, attachment){
      if ( _custom_media ) {
        $("#"+id).val(attachment.url);
      } else {
        return _orig_send_attachment.apply( this, [props, attachment] );
      };
    }

    wp.media.editor.open(button);
    return false;
  });

  $('.add_media').on('click', function(){
    _custom_media = false;
  });
});
</script>	
<?php wp_footer(); ?>

</body>
</html>