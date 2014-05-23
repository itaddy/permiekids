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

<div class="container">