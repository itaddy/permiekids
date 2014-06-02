
	
</div> <!-- /container -->

<div class="line-separator"></div>

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
	
<?php wp_footer(); ?>

</body>
</html>