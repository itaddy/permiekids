<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<meta charset="utf-8">
<title><?php wp_title('|',1,'right'); ?> <?php bloginfo('name'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Le styles -->
<link href="<?php bloginfo('stylesheet_url');?>" rel="stylesheet">

<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<?php wp_enqueue_script("jquery"); ?>
<?php wp_head(); ?>

<?php if (is_front_page()) { ?>

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script>
		$(function() {
		  $('a[href*=#]:not([href=#])').click(function() {
			if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
		
			  var target = $(this.hash);
			  target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
			  if (target.length) {
				$('html,body').animate({
				  scrollTop: target.offset().top
				}, 1000);
				return false;
			  }
			}
		  });
		});
	</script>
<?php } ?>


</head>

<body>

<div class="container">
	<div class="navbar">
		<div class="navbar-custom">
		
			  <div class="container">
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
					  <span class="icon-bar"></span>
					  <span class="icon-bar"></span>
					  <span class="icon-bar"></span>
					</a>
					
					
					<?php 
						$logo=mytheme_option('header_logo');
						
						if ($logo!='') { ?>
							<div class="logo_placeholder">
								<a class="brand" href="<?php echo site_url(); ?>" style="outline:none;"><img src="<?php echo mytheme_option('header_logo'); ?>" class="logo" alt="<?php bloginfo('name'); ?>"></a>
							</div>	
						<?php } else { ?>
							<div class="site_name">
								<a class="brand" href="<?php echo site_url(); ?>"><?php bloginfo('name'); ?></a>
							</div>
						<?php } ?>
					
					<div class="nav-collapse collapse">
						  <ul class="nav">
								<?php
								wp_nav_menu( array(
									'theme_location' => 'top_menu',
									'depth' => 2,
									'container' => false,
									'menu_class' => 'nav navbar-nav',
									'fallback_cb' => 'wp_page_menu',
									//Process nav menu using our custom nav walker
									'walker' => new wp_bootstrap_navwalker())
								);
								?>						 	
						  </ul>
					</div><!--/.nav-collapse -->

			  </div>
		</div>
	</div>
</div>
<div class="line-separator"></div>
<div class="container">
<?php if (!is_front_page()) { ?>
	<div class="the-search-container">
		<div class="the-search-box">
			<form action="<?php bloginfo('siteurl'); ?>" id="searchform" method="get">
			<fieldset class="pk-search-tax-container pk-tax-ethics">
				<h4>Ethics</h4>
				<div class="pk-search-tax">
					<input type="checkbox" value="155" id="_ethic_155" name="_ethic[]">
					<label for="_ethic_155" class="label label-default">Care of Earth</label>
	        	</div>
				<div class="pk-search-tax">
					<input type="checkbox" value="157" id="_ethic_157" name="_ethic[]">
					<label for="_ethic_157" class="label label-default">Care of People</label>
	        	</div>
				<div class="pk-search-tax">
					<input type="checkbox" value="158" id="_ethic_158" name="_ethic[]">
					<label for="_ethic_158" class="label label-default">Return of Surplus</label>
	        	</div>
			</fieldset>
			<fieldset class="pk-search-tax-container pk-tax-principles">
				<h4>Principles</h4>
				<div class="pk-search-tax">
					<input type="checkbox" value="159" id="_principle_159" name="_principle[]">
					<label for="_principle_159" class="label label-default">Apply Self-Regulation and Accept Feedback</label>
	        	</div>
				<div class="pk-search-tax">
					<input type="checkbox" value="160" id="_principle_160" name="_principle[]">
					<label for="_principle_160" class="label label-default">Catch and Store Energy</label>
	        	</div>
				<div class="pk-search-tax">
					<input type="checkbox" value="161" id="_principle_161" name="_principle[]">
					<label for="_principle_161" class="label label-default">Design from Patterns and Details</label>
	        	</div>
				<div class="pk-search-tax">
					<input type="checkbox" value="162" id="_principle_162" name="_principle[]">
					<label for="_principle_162" class="label label-default">Integrate Rather than Segregate</label>
	        	</div>
				<div class="pk-search-tax">
					<input type="checkbox" value="163" id="_principle_163" name="_principle[]">
					<label for="_principle_163" class="label label-default">Observe and Interact</label>
	        	</div>
				<div class="pk-search-tax">
					<input type="checkbox" value="156" id="_principle_156" name="_principle[]">
					<label for="_principle_156" class="label label-default">Obtain a Yield</label>
	        	</div>
				<div class="pk-search-tax">
					<input type="checkbox" value="164" id="_principle_164" name="_principle[]">
					<label for="_principle_164" class="label label-default">Produce No Waste</label>
	        	</div>
				<div class="pk-search-tax">
					<input type="checkbox" value="165" id="_principle_165" name="_principle[]">
					<label for="_principle_165" class="label label-default">Small and Slow Solutions</label>
	        	</div>
				<div class="pk-search-tax">
					<input type="checkbox" value="166" id="_principle_166" name="_principle[]">
					<label for="_principle_166" class="label label-default">Use and Value Resources</label>
	        	</div>
				<div class="pk-search-tax">
					<input type="checkbox" value="169" id="_principle_169" name="_principle[]">
					<label for="_principle_169" class="label label-default">Use Creativity and Adapt to Change</label>
	        	</div>
				<div class="pk-search-tax">
					<input type="checkbox" value="167" id="_principle_167" name="_principle[]">
					<label for="_principle_167" class="label label-default">Use the Edges and Value the Marginal</label>
	        	</div>
				<div class="pk-search-tax">
					<input type="checkbox" value="168" id="_principle_168" name="_principle[]">
					<label for="_principle_168" class="label label-default">Value Diversity</label>
	        	</div>
				</fieldset>
						 
				<fieldset> 
					 <input type="search" id="s" name="s" placeholder="Search" required />
					 <input type="submit" id="searchsubmit" value="Search"  class="search_button"/>
				 </fieldset>
			</form>		
		</div>
	</div>
<?php } ?>
</div>