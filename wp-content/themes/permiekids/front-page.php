<?php get_header(); ?>

<div id="featured" class="grid col-940">
	<h2>Welcome to our Community</h2>
	<h3>We hope You'll Join Us</h3>
	
	<div class="get_started_form">
		<form action="" method="get">
			<input type="text" name="email" placeholder = "email" class="signup_fields">
			<input type="password" name="password" placeholder="password" class="signup_fields">
			<input type="submit" value="Get Started" class="get_started_button">
		</form>
	</div>
</div>

<div class="row">
  <div class="span8">

	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<h1><?php the_title(); ?></h1>
	  	<?php the_content(); ?>

	<?php endwhile; else: ?>
		<p><?php _e('Sorry, this page does not exist.'); ?></p>
	<?php endif; ?>

  </div>
  <div class="span4">
	<?php get_sidebar('home'); ?>	
  </div>
</div>


<?php get_footer(); ?>