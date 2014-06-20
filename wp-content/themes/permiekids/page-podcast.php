<?php 

/*
Template Name: Podcast Template
*/

get_header(); ?>


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
		<div class="span8">
		<?php 
			$args = array(
			'category_name' => 'podcast', 
			'paged' => $paged
			);
	
			$list_of_posts = new WP_Query( $args );
			if ( $list_of_posts->have_posts() ) : while ( $list_of_posts->have_posts() ) : $list_of_posts->the_post(); ?>
	
			<div class="span4">	
				<h2><?php the_title(); ?></h2>
				<?php the_content(); ?>
				<p><?php the_tags('','',''); ?></p>
			</div>		
	
			<?php endwhile; else: ?>
				<div class="span4">
					<p><?php _e('Sorry, there are no podcast items added yet.'); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<div class="span4">
			<?php get_sidebar(); ?>	
		</div>
	</div>
<?php get_footer(); ?>