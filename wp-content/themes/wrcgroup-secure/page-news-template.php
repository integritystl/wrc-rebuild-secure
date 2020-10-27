<?php
/**
 * Template Name: News Archive
 *
 * @package WRC Secure
 */

get_header();
  if ( have_posts() ): while ( have_posts() ): the_post();
  $permissions = build_user_permissions();

  $site = get_current_site();

  $news_page = get_field('news_page', $site->ID);

  //get the Categories ACF field from the site's News page so we only get categories for our Current Site
  $siteNewsCategories = get_field('news_categories', $news_page->ID);

?>

<div class="page_container">
  <?php get_template_part('template-parts/content', 'news-navigation');?>
  <div class="news-listing-container">
    <div class="news-wrapper-all">
      <div class="news-category-list-container">
        <div class="news-filter-container">
          <div class="tag-filter__container">
            <h4>Tags</h4>
            <i class="fa fa-caret-down" aria-hidden="true"></i>
            <select id="tag-filter" class="filter-input tag-filter">
              <?php //built from tags pull from ajax call ?>
            </select>
          </div>
          <div class="search-filter__container">
            <h4>Search</h4>
            <i class="fa fa-search" aria-hidden="true"></i>
            <input id="search-filter" class="filter-input search-filter" type="text" placeholder="Search Articles">
            <i class="fa fa-close" id="search-filter-clear"></i>
          </div>
        </div>
        <div class="news-list-container">
          <div class="clear-filters-container hidden">
            <p><?php the_field('empty_search_text', 'option'); ?></p>
            <a class="clear-filters" href="#">Clear All Filters</a>
          </div>
          <ul class="news-list" id="news-list" data-site="<?php echo($site->ID); ?>" data-newscats="<?php echo implode(",", $siteNewsCategories);?>">
            <?php //just use load more ajax by default ?>
          </ul>
          <div class="loading-screen">
            <div class="spinner"></div>
          </div>
          <div class="load-more-container hidden">
            <a href="#" id="load-more-link">Load More</a>
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
