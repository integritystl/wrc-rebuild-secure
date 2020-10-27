<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package wrcgroup-secure
 */

get_header(); ?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
		<div id="search-results-container" data-search-terms="<?php echo get_search_query();?>" data-current-site="<?php echo $_GET['current_site']?>">
			<header class="page-header">
				<h1 class="page-title"><?php printf( esc_html__( 'Search Results for: %s', 'wrcgroup-secure' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
			</header><!-- .page-header -->
		<?php

		$hasPosts = false;
		if ( have_posts() ) :
			$hasPosts = true;

			?>


			<?php
			/* Start the Loop */
			while ( have_posts() ) : the_post();

				/**
				 * Run the loop for the search to output the results.
				 * If you want to overload this in a child theme then include a file
				 * called content-search.php and that will be used instead.
				 */
				get_template_part( 'template-parts/content', 'search' );

			endwhile;


		else :

			get_template_part( 'template-parts/content', 'none' );

		endif; ?>
		</div>
		<div class="loading-screen">
			<div class="loader">Loading...</div>
		</div>
		<?php if($hasPosts): ?>
			<button class="search-load-more btn-secondary" id="load-more-btn">
				Load More
			</button>
		<?php endif; ?>
		</main><!-- #main -->
	</section><!-- #primary -->

<?php
// get_sidebar();
get_footer();
