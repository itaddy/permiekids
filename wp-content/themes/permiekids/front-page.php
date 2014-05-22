<?php get_header(); ?>

<div id="featured" class="grid col-940">

	<div class="welcome_text_area">
		<h2>Welcome to our Community</h2>
		<h3>We hope You'll Join Us</h3>
	</div>
	
	<div class="get_started_form">
		<form action="" method="get">
			<input type="text" name="email" placeholder = "email" class="signup_fields">
			<input type="password" name="password" placeholder="password" class="signup_fields">
			<input type="submit" value="Get Started" class="get_started_button">
		</form>
		<div class="already_a_member">Already a member, sign in</div>
	</div>
	
	<div class="learn-more"><a href="#learn_more">Learn More</a></div>
</div>
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

<div class="row">

		<h1><?php the_title(); ?></h1>
		
		<?php 
			if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
				the_post_thumbnail();
			} 
		?>
	
	  	<div class="span8">
	  
			<a name="learn_more"></a>
			
			<?php the_content(); ?>
	
	 	</div>

<?php endwhile; else: ?>
	<div class="span8">
		<p><?php _e('Sorry, this page does not exist.'); ?></p>
	</div>
<?php endif; ?>
	  
  <div class="span4">
	<?php get_sidebar('home'); ?>	
  </div>
</div>


<?php get_footer(); ?>