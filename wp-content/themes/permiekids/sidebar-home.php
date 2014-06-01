<div class="sidebar-home">
<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
	<?php dynamic_sidebar( 'Home Page Sidebar' ); ?>
<?php endif; ?>
</div>