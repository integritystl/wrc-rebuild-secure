<?php 
/**
 * Template Name: Learning Library List
 *
 * @package WRC Secure
 */

get_header();

$args = array( 
  'post_type'  => 'learning-library',
);
$learningLibrary = new WP_Query( $args );
if ( $learningLibrary->have_posts() ) : 

$permissions = build_user_permissions();

$site = get_current_site();

$LearningLibrary_page = get_field('library_page', $site->ID);
//get the Categories ACF field from the site's News page so we only get categories for our Current Site
$siteLearningCategories = get_field('library_category', $LearningLibrary_page->ID);


?>
<div class="page_container learning-library">
  <?php get_template_part('template-parts/content', 'learning-library-navigation');?>
  <div class="news-listing-container">
    <div class="news-wrapper-all">
      <div class="news-category-list-container">
          <div class="news-filter-container">
          <?php /*
          <div class="tag-filter__container">
            <h4>Tags</h4>
            <i class="fa fa-caret-down" aria-hidden="true"></i>
            <select id="tag-filter" class="filter-input tag-filter">
              <?php //built from tags pull from ajax call ?>
            </select>
          </div>
          */ ?>
          <div class="search-filter__container">
            <h4>Search</h4>
            <i class="fa fa-search" aria-hidden="true"></i>
            <input id="search-filter" class="filter-input search-filter" type="text">
            <i class="fa fa-close" id="search-filter-clear"></i>
          </div>
        </div>
        <div class="news-list-container">
          <div class="clear-filters-container hidden">
            <p><?php the_field('empty_search_text', 'option'); ?></p>
            <a class="clear-filters" href="#">Clear All Filters</a>
          </div>
          <ul class="news-list" id="library-list" data-site="<?php echo($site->ID); ?>">
            <?php //just use load more ajax by default ?>
          </ul>
          <div class="loading-screen">
            <div class="spinner"></div>
          </div>
          <div class="load-more-container">
            <a href="#" id="load-more-link">Load More</a>
          </div>
            <?php posts_nav_link(); ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php

endif;

get_footer();
