<?php
/**
 * The template for displaying Learning Library single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-learning-library
 *
 * @package wrcgroup-secure
 */


get_header();

$permissions = build_user_permissions();
$site = get_current_site();

$favorites = get_field('favorites_page', $site->ID);
$favorites_url = '';
if(!empty($favorites)){
  $favorites_url = get_the_permalink($favorites->ID);
}

$learning_lib_sites = get_field('attached_site');

//Get the current site's Library Page so we can add a Back to Library link
$LearningLibrary_page = get_field('library_page', $site->ID);
$LearningLibrary_url = get_the_permalink($LearningLibrary_page->ID);

while ( have_posts() ) : the_post(); ?>

<div class="page_container">
	<div id="primary" class="content-area full-width">
		<div class="news-links">
<a href="<?php echo $LearningLibrary_url; ?>">Back to Learning Library</a> <?php if($favorites_url != '') { ?><a class="back-to-favs" href="<?php echo $favorites_url; ?>">Back to Favorite Programs</a><?php } ?>
		</div>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h2><?php the_title(); ?></h2>
			<?php /* The client asked for the date, catagories, and tags to be removed. Instead of just deleting it I'm hiding it because it probably will want to be used in the future. 
			<div class="post-meta">
				<span><?php the_time('F j, Y') ?></span>
				<span class="categories">
					<?php
						$categories = get_the_terms( get_the_id(), 'learning_category' );
						foreach( $categories as $category ) { ?>
						    <a href="<?php echo(empty($LearningLibrary_page) ? '#' : get_permalink($LearningLibrary_page->ID) . '?cat=' . $category->term_id); ?>">
						    	<?php echo($category->name); ?>		
					    	</a>
						<?php }
					?>
				</span>

			<?php 
				$tags = get_the_terms( get_the_id(), 'learning_tag' );
				if(!empty($tags)) : ?>
				<span class="tags single-learning-library">Tags:
				<?php

					foreach( $tags as $tag ) { ?>
					    <a href="<?php echo(empty($LearningLibrary_page) ? '#' : get_permalink($LearningLibrary_page->ID) .'?cat='. $tag->term_id); ?>">
					    	<?php echo($tag->name); ?>	
				    	</a>
					<?php }
					?>
				</span>
			<?php endif; ?>

			</div>
			*/ ?>
			<div class='learning-library-video'>
				<?php the_favorites_button(); ?>
				<div id="ll__modal-trigger" class="simplefavorite-button preset"><i class="fa fa-comments" aria-hidden="true"></i> Leave Feedback</div>
			</div>

            <div class='learning-library-content'>
                <?php
                the_field ('intro_content');
                ?>
            </div>

			<div class='learning-library-video'>
					<?php the_content(); ?> 
			</div>



			<?php 
			$posts = get_field('related_videos');

			if( $posts ): ?>
                <h2>Related Programs</h2>
			    <div class="related-videos">

			    <?php foreach( $posts as $post) : // variable must be called $post (IMPORTANT) ?>
                    <div class="related-video-single">
                        <?php setup_postdata($post); ?>
                        <div class="related-thumbnail">
                            <?php echo get_the_post_thumbnail($post->ID, 'thumbnail'); ?>
                        </div>
                        <div id="post-<?php the_ID(); ?>">
                            <h5><a href="<?php echo get_permalink( $post->ID ); ?>"><?php the_title();?></a></h5>
                            <div class="post-meta">
                                <span>Posted On <?php the_time('m/d/Y') ?></span>
                            </div>
                            <p><?php the_excerpt();?></p>
                        </div>
                    </div>
				<?php endforeach; ?>

                </div><!-- end .related-videos -->

			    <?php wp_reset_postdata(); // IMPORTANT - reset the $post object so the rest of the page works correctly

		 	endif; ?>

	 	</article>


	</div><!-- #primary -->
</div>
<?php
endwhile;
get_footer();



