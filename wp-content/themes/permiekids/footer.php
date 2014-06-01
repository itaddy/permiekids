
	
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
	
<?php wp_footer(); ?>

</body>
</html>