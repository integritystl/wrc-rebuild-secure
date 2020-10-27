<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package wrcgroup-secure
 */


get_header();

$site = get_current_site();

//Get the current site's News Page so we can add a Back to News link
$news_page = get_field('news_page', $site->ID);
$news_page_url = get_the_permalink($news_page->ID);

while ( have_posts() ) : the_post(); ?>
<div class="page_container">
	<?php get_template_part('template-parts/content', 'news-navigation');?>
	<div id="primary" class="content-area">

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php
				//shared news heading items like featured image and meta
				get_template_part( 'template-parts/content', 'news-header' );

			 if(have_rows('news_flexible_content', get_the_ID())) {
				 get_template_part( 'template-parts/content', 'flex-news' );
			 } else {
				 //fallback to default WP content in case News is just dropped into default WizzyWig ?>
					<div class="post-entry"><?php the_content();?></div>
			<?php } ?>
		 </article>

		 <div class="news-links">
		 	<a href="<?php echo $news_page_url; ?>">Back to All News</a>
		</div>
	</div><!-- #primary -->
</div>
<?php
endwhile;
get_footer();
