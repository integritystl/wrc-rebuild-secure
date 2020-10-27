<?php
/**
 * Template Name: Email News Archive
 *
 * @package WRC Secure
 */

 get_header();
   if ( have_posts() ): while ( have_posts() ): the_post();
   $permissions = build_user_permissions();

   $site = get_current_site();
 ?>

<div class="page_container">
  <?php $isEmailNews = true; ?>
  <?php //use locate template so our isEmailNews flag persists ?>
  <?php include(locate_template('template-parts/content-news-navigation.php'));?>
  <div class="news-listing-container">
    <div class="news-wrapper-all">
      <div class="news-category-list-container">
        <div class="news-list-container">
          <ul class="news-list" id="news-list" data-site="<?php echo($site->ID); ?>">
            <?php //just use load more ajax by default ?>
          </ul>
          <div class="loading-screen">
            <div class="spinner"></div>
          </div>
          <div class="load-more-container hidden">
            <a href="#" id="load-more-link">Load More</a>
          </div>
          <div class="clear-filters-container hidden">
            <p><?php the_field('empty_search_text', 'option'); ?></p>
            <a class="clear-filters" href="#">View All News</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php

//end loop
endwhile;
endif;

get_footer();
