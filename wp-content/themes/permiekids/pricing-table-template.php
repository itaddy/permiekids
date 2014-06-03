<?php 

/*
Template Name: Pricing Table Template
*/

get_header(); ?>


<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
	<div class="container">
		<div class="row">
			<div class="title-center"><h1 class="text-center"><?php the_title(); ?></h1></div>
		</div>
	</div>

<div class="container">
	<div class="row">
		<div class="span12">
			<?php 
				if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
					the_post_thumbnail();
				} 
			?>		
			
				<?php the_content(); ?>

				<div class ="monthly-yearly">
				<span class="monthly text-center" id="monthly">Monthly</span><span class="yearly text-center" id="yearly">Yearly</span>
				<div style="clear:both;"></div>
				</div>
				<script>
					jQuery( "#monthly" ).click(function() {
					jQuery( ".monthly-table" ).show( "slow");
					jQuery( ".yearly-table" ).hide( "slow");
					});

					jQuery( "#yearly" ).click(function() {
					jQuery( ".yearly-table" ).show( "slow");
					jQuery( ".monthly-table" ).hide( "slow");
					});					
				</script>				
				<div class="monthly-table">
					<table class="table table-striped">
					  <tr>
						<td><h2 class="text-center">Free</h2></td>
						<td><h2 class="text-center">Basic</h2></td>
						<td><h2 class="text-center">Contributing</h2></td>
					  </tr>
					  <tr>
						<td><p class="text-center">Pay for Curriculum</p></td>
						<td><p class="text-center">Access all Curricula for</p></td>
						<td><p class="text-center">Access all curricula for</p></td>
					  </tr>
					  <tr>
						<td><p class="text-center">-</p></td>
						<td><p class="text-center">Connect with the worldwide</p></td>
						<td><p class="text-center">Connect with the Worldwide</p></td>
					  </tr>
					  <tr>
						<td><p class="text-center">-</p></td>
						<td><p class="text-center">Access to Exclusive</p></td>
						<td><p class="text-center">Access to Exclusive</p></td>
					  </tr>
					  <tr>
						<td><p class="text-center">-</p></td>
						<td><p class="text-center">-</p></td>
						<td><p class="text-center">Partner</p></td>
					  </tr>
					  <tr>
						<td><p class="text-center">-</p></td>
						<td><p class="text-center">-</p></td>
						<td><p class="text-center">Training</p></td>
					  </tr>
					  <tr>
						<td><p class="text-center">Free</p></td>
						<td><p class="text-center">$10.00/month</p></td>
						<td><p class="text-center">$25.00/month</p></td>
					  </tr>
					  <tr>
						<td><a href="<?php echo get_permalink(mytheme_option('free_registration_url')); ?>"><button class="pricing-table-button">Join</button></a></td>
						<td><a href="<?php echo get_permalink(mytheme_option('basic_registration_url_for_monthly')); ?>"><button class="pricing-table-button">Join</button></a></td>
						<td><a href="<?php echo get_permalink(mytheme_option('contributing_registration_url_for_monthly')); ?>"><button class="pricing-table-button">Join</button></a></td>
					  </tr>
					</table>
				</div>

				<div class="yearly-table">
					<table class="table table-striped">
					  <tr>
						<td><h2 class="text-center">Free</h2></td>
						<td><h2 class="text-center">Basic</h2></td>
						<td><h2 class="text-center">Contributing</h2></td>
					  </tr>
					  <tr>
						<td><p class="text-center">Pay for Curriculum</p></td>
						<td><p class="text-center">Access all Curricula for</p></td>
						<td><p class="text-center">Access all curricula for</p></td>
					  </tr>
					  <tr>
						<td><p class="text-center">-</p></td>
						<td><p class="text-center">Connect with the worldwide</p></td>
						<td><p class="text-center">Connect with the Worldwide</p></td>
					  </tr>
					  <tr>
						<td><p class="text-center">-</p></td>
						<td><p class="text-center">Access to Exclusive</p></td>
						<td><p class="text-center">Access to Exclusive</p></td>
					  </tr>
					  <tr>
						<td><p class="text-center">-</p></td>
						<td><p class="text-center">-</p></td>
						<td><p class="text-center">Partner</p></td>
					  </tr>
					  <tr>
						<td><p class="text-center">-</p></td>
						<td><p class="text-center">-</p></td>
						<td><p class="text-center">Training</p></td>
					  </tr>
					  <tr>
						<td><p class="text-center">Free</p></td>
						<td><p class="text-center">$20.00/month</p></td>
						<td><p class="text-center">$50.00/month</p></td>
					  </tr>
					  <tr>
						<td><a href="<?php echo get_permalink(mytheme_option('free_registration_url')); ?>"><button class="pricing-table-button"><Join</button></a></td>
						<td><a href="<?php echo get_permalink(mytheme_option('basic_registration_url_for_yearly')); ?>"><button class="pricing-table-button">Join</button></a></td>
						<td><a href="<?php echo get_permalink(mytheme_option('contributing_registration_url_for_yearly')); ?>"><button class="pricing-table-button">Join</button></a></td>
					  </tr>
					</table>
				</div>				
		 </div>
		<?php endwhile; else: ?>
		<div class="row">
			<div class="span12">
				<p><?php _e('Sorry, this page does not exist.'); ?></p>
			</div>
		</div>	
		<?php endif; ?>		
	</div>
	
<?php get_footer("pricing"); ?>