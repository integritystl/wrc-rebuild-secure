<?php
/**
 *
 * Page with right sidebar and content area
 *
 *Template Name: Team Page
 *
 */

get_header(); ?>

	<?php while ( have_posts() ) : the_post(); ?>
		<div id="primary" class="content-area">
			<div class="page-title-banner">
				<div class="container">
					<h1><?php the_title(); ?></h1>
				</div>
			</div>
			<div id="content" class="site-content container" role="main">
				<div class="interior">

					<div class="left-col">
						<?php the_content();

						$locations = get_field("location");

						foreach($locations as $l) {
						$string = str_replace(' ', '', $l['location_name']); // Removes spacing.
						$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
						$string = strtolower($string);
   						?>
						<div id="<?php echo $string ?>" class="location">
							<h2><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/yellow-header-arrow.png"/><?php echo $l['location_name']; ?></h2>
							<h4><?php echo $l['location_type']; ?></h4>
						</div>
						<?php
							$members = $l['team_member'];
							foreach($members as $m) {
						?>
								<div class="team-member">
									<div class="profile-image">
										<img class="alignleft" alt="" src="<?php echo $m['image']; ?>" >
										<a class="team-btn" href="/contact-us/?hall_rep=<?php echo $m['name'] ?>">Contact</a>
									</div>
									<div class="bio">
										<h3 class="member-name"><?php echo $m['name'] ?></h3>
										<h4><?php echo $m['title']; ?></h4>
										<p><?php echo $m['bio']; ?></p>
									</div>
								</div>
						<?php } } ?>
					</div>

					<div class="right-col">
						<?php
							$options = get_post_custom(get_the_ID());
							if(isset($options['custom_sidebar'])) {
								get_sidebar();
							}
						?>
					</div>
				</div>

			</div><!-- #content -->
		</div><!-- #primary -->
	<?php endwhile; // end of the loop. ?>

<?php get_footer(); ?>
