<?php get_header(); ?>

<div id="featured" class="grid col-940">

	<div class="welcome_text_area">
		<h2>Welcome to the Permie Kids Commmunity!</h2>
		<h3>This is a tagline about what Permie Kids is</h3>
	</div>
	


	<div class="get_started_form">

		<?php do_action ('process_customer_registration_form'); ?>
		<form action="<?php get_home_url(); ?>" method="post" class="form-inline">
			<input type="text" name="email" placeholder = "email" class="signup_fields">
			<input type="password" name="password" placeholder="password" class="signup_fields">
			<?php wp_nonce_field( 'adduserfield', 'add-nonce' ) ?>
			<input name="action" type="hidden" id="action" value="adduser" />
			<input type="submit" value="Get Started" class="get_started_button">
		</form>
		<div class="already_a_member">Already a member, <a href="<?php echo wp_login_url(); ?>" title="Sign In">sign in</a></div>
	</div>
	

</div>

<div class="container">
	<div class="information-window">
		<div class="row">
			<div class="span3"><a href="#who" class="home-buttons">Who we Are</a></div>
			<div class="span3"><a href="#podcast" class="home-buttons">Podcast</a></div>
			<div class="span3"><a href="#curricula" class="home-buttons">Curricula</a></div>
			<div class="span3"><a href="#getstarted" class="home-buttons">Get Started</a></div>
		</div>
		
		<div class="information">
			<h1 id ="who">Who we are</h1>
			<div class="content-information">
				<div class="row">
					<div class="span6">
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla auctor nisl nec arcu tincidunt accumsan. Sed eu nunc aliquet, condimentum ligula vitae, accumsan nunc. Ut ut nulla porttitor, hendrerit lectus a, dictum justo. Fusce a sapien nulla. In posuere sed ipsum vel pharetra. Nullam commodo purus ante, nec venenatis tortor imperdiet nec. Mauris blandit quis massa auctor iaculis. Phasellus ultricies lacinia purus, volutpat ullamcorper justo rutrum at. <a href="#">Keep Reading.</a></p>
						<h2>Learn about our Beliefs</h2>
						<ul>
							<li><a href="#">Care of People</a></li>
							<li><a href="#">Care of Earth</a></li>
							<li><a href="#">Return of Excess</a></li>
						</ul>
					</div>
					<div class="span5">
						<img src="http://localhost/permiekids/images/image-1.jpg" class="img-responsive" />
					</div>
				</div>
			</div>
		</div>
		<div style="clear:both;"></div>
		<div class="information">
			<h1 id ="podcast">Podcast</h1>
			<h3>Each podcast is an exploration into how Permie Kids can better your child</h3>
			<div class="content-information">
				<div class="row">
					<div class="span5">
						<div style="text-align:center;">
							<div class="podcast-content">
								<h3>A Lesson Plan: Time</h3>
								<center>
									<img src="http://localhost/permiekids/images/podcast.jpg" class="img-responsive" />
								</center>
							</div>
						</div>
					</div>
					<div class="span5">
						<div style="text-align:center;">
							<div class="podcast-content">
								<h3>Resource Review: Kurent Journal</h3>
								<center>
									<img src="http://localhost/permiekids/images/podcast.jpg" class="img-responsive" />
								</center>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div style="clear:both;"></div>
		<div class="information">
			<h1 id ="curricula">Curricula</h1>
			<h3>Enrich your Child's Education with Permie Kids Curricula</h3>
			<div class="content-information">
				<div class="row">
					<div class="span7">
						<h4 class="featured-h4">Featured Curriculum</h4>
						<img src="http://localhost/permiekids/images/featured-curricula.jpg" class="img-responsive" />
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla auctor nisl nec arcu tincidunt accumsan. Sed eu nunc aliquet, condimentum ligula vitae, accumsan nunc. Ut ut nulla porttitor, hendrerit lectus a, dictum justo.</p>
					</div>
					<div class="span4">
						<h4>Curriculum Title</h4>
						<img src="http://localhost/permiekids/images/curriculum.jpg" class="img-responsive" />
					</div>					
				</div>
				<div class="row">
					<div class="span7">
						<h4>Curriculum Title</h4>
						<img src="http://localhost/permiekids/images/curriculum.jpg" class="img-responsive" />
					</div>
					<div class="span4">
						<h4>Curriculum Title</h4>
						<img src="http://localhost/permiekids/images/curriculum.jpg" class="img-responsive" />
					</div>					
				</div>	
				<hr class="divider-bar" />
				<div class="row">
					<div class="span7">
						<h4>Get (Some Course) for Free!</h4>
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla auctor nisl nec arcu tincidunt accumsan. Sed eu nunc aliquet, condimentum ligula vitae, accumsan nunc. Ut ut nulla porttitor, hendrerit lectus a, dictum justo.</p>
						
					</div>
					<div class="span3">
						<form class="form-horizontal">
						<div class="control-group">
							<div class="controls">
								<input type="text" name="email" placeholder = "email" class="signup_fields">
							</div>
						</div>
						<div class="control-group">
							<div class="controls">
								<input type="password" name="password" placeholder="password" class="signup_fields">
							</div>
						</div>
						<div class="control-group">
							<div class="controls">
								<input type="submit" value="Download Course Material" class="download_button">
							</div>
						</div>
						</form>					
					</div>
				</div>			
			</div>
		</div>						
		<div style="clear:both;"></div>
		<div class="information">
			<h1 id ="getstarted">Get Started</h1>
			<div class="content-information">
				<div class="row">
					<div class="span7">
						<h2>What Permie Kids is:</h2>

						<p>Permie Kids connects like-minded kids and parents and allows them to contribute in developing a Permaculture-Based curriculum.</p>

						<p>The goal of this curriculum is to produce kids, of all ages, who are aware of the role in society and are active in preserving and caring for the earth.</p>

						<p>Permie Kids also provides an outlet for Individuals to share curriculae that they have developed with a community that shares their values and goals.</p>
						
						<p><a href="#" class="take-a-look">Take a Look</a></p>
					</div>
					<div class="span4">
						<img src="http://localhost/permiekids/images/curriculum.jpg" class="img-responsive" />
					</div>
				</div>	
			</div>
		</div>						
		<div style="clear:both;"></div>		
	</div>
</div>


<?php get_footer(); ?>