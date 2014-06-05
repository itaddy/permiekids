<?php get_header(); ?>


<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<div class="line-separator"></div>	
	<div class="container">
		<div class="row">
			<div class="title"><h1><?php the_title(); ?></h1></div>
			<div class="breadcrumb"><?php the_breadcrumb(); ?></div>
		</div>
	</div>
<div class="line-separator"></div>

<div class="container">
	<div class="row">
		<div class="span12">
			<?php 
				if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
					the_post_thumbnail();
				} 
			?>		
			<?php the_content(); ?>
			<?php if ( is_active_sidebar( 'content-bottom-a' ) ) : ?>
				<div class="span6">
					<?php dynamic_sidebar( 'content-bottom-a' ); ?>
				</div>		
			<?php endif; ?>
			<?php if ( is_active_sidebar( 'content-bottom-b' ) ) : ?>
				<div class="span6">
				<?php dynamic_sidebar( 'content-bottom-b' ); ?>
				</div>		
			<?php endif; ?>			
		 </div>
		<?php endwhile; else: ?>
		<div class="row">
			<div class="span12">
				<p><?php _e('Sorry, this page does not exist.'); ?></p>
			</div>
		</div>	
		<?php endif; ?>
		
	</div>
	test
	
<?php get_footer(); ?>