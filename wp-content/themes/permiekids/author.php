<?php get_header(); 

global $wp_query;
$member = get_queried_object();
$user_info = get_user_meta($member->ID);
?>

<div class="line-separator"></div>	
	<div class="container">
		<div class="row">
			<div class="title"><h1><?php echo $member->display_name; ?></h1></div>
			<div class="breadcrumb"><?php the_breadcrumb(); ?></div>
		</div>
	</div>
<div class="line-separator"></div>
	<div class="container">
		<div class="motto">
				<span class="the-motto"><?php echo $user_info['motto'][0]; ?></span>
				<div style="clear:both;"></div>
				<span class="who-said-it"><?php echo $user_info['who_said_it'][0]; ?></span>
			
		</div>
	</div>
<div class="line-separator"></div>

<div class="container">
	<div class="row">
		<div class="span8">

			<h1 class="member-name"><?php echo $user_info['first_name'][0]; ?>&nbsp;<?php echo $user_info['last_name'][0]; ?></h1>
			<div class="row">
				<div class="span2">
					<img src="<?php echo $user_info['custom_avatar'][0]; ?>" class="the-member-avatar" />
				</div>
				<div class="span4">
					<ul class="member-info"></li>
						<li class="location"><?php echo $user_info['location'][0]; ?></li>
						<li class="family"><?php echo $user_info['family'][0]; ?></li>
						<li class="occupation"><?php echo $user_info['occupation'][0]; ?></li>
						<li class="experience"><?php echo $user_info['experience'][0]; ?></li>
					</ul>
				</div>
				<div class="span2">
					<ul class="social-network-info">
						<li><a href="<?php echo $member->user_email; ?>" class="email">Email</a></li>
						<li><a href="<?php echo $user_info['linkedin'][0]; ?>" class="linkedin">Linkedin</a></li>
						<li><a href="<?php echo $user_info['twitter'][0]; ?>" class="twitter">Twitter</a></li>
						<li><a href="<?php echo $user_info['facebook'][0]; ?>" class="facebook">Facebook</a></li>
					</ul>
				</div>
			</div>
			<div class="row">
				<div class="span8">
					<div class="biography-block">
						<?php echo $user_info['biography'][0]; ?>
					</div>
				</div>
				<div class="span8">
					<div class="what-iam-working-right-now-block">
						<h3>What I'm Working On</h3>
						<?php echo $user_info['what_are_you_working_on'][0]; ?>
					</div>
				</div>				
			</div>
			<div style="clear:both;"></div>
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

		
		<div class="span4">
			<?php get_sidebar(); ?>	
		</div>
	</div>
	
<?php get_footer(); ?>