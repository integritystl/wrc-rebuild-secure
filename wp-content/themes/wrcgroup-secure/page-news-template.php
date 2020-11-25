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
        <div class="news-filter-bar">
          <div class="news-filters-container">
            <?php echo do_shortcode('[searchandfilter slug="wrc-news"]'); ?>
          </div>
        </div>
        <div class="news-results-container">
          <?php

          $args['search_filter_slug'] = "wrc-news";
          $query = new WP_Query($args);
          $total = $query->found_posts;
          $totalPages = $query->max_num_pages;
          
          ?>

          <div class="results-found" page_count="<?php echo $totalPages;?>"><span><?php echo $total; ?></span> Results Found</div>

          <?php
          if ( $query->have_posts() ) { ?>

            <?php
                echo '<ul class="news-list" id="results-list">';
                while ( $query->have_posts() ) {
                  $query->the_post();

                  $title = get_the_title();
                  $post_id = get_the_ID();
                  $params = apply_filters( 'wp_terms_checklist_args', $args, $post_id );
                  $current_site = get_field('site');
                  $category_object = get_the_category($post_id);
                  $category_name = $category_object[0]->name;
                  $link = get_permalink();
                  $imgObject = wp_get_attachment_image( get_post_thumbnail_id(), 'full' );
                  $date = get_the_date('F j, Y');
                  $summary = excerpt(60);
                  ?>
                  <li>
                      <div class="featured-image-container">
                        <?php echo '<a href="' . get_permalink() . '?current_site=' . $current_site . '">' . $imgObject . '</a>' ?>
                        <h2 class="post_item_title"><?php echo '<a href="' . get_permalink() . '?current_site=' . $current_site . '">' . $title . '</a>' ?></h2>
                      </div>
                      <div class="post-meta">
                        <span><?php echo $date ?></span>
                        <span class="categories"><a class="archive-listing-category" href="#" data-category="<?php echo $category_object ?>"><?php echo $category_name ?></a></span>
                        <?php 
                          $tag_entries = get_the_tags();
                          if(!empty($tag_entries)) {
                            echo '<span class="tags">Tags:';

                            $tagsNames = array();
                            foreach($tag_entries as $tag_entry){
                              $tagsNames[] = '<a class="archive-listing-tag" href="#" data-tag="' . $tag_entry->term_id . '">' . $tag_entry->name . '</a>';
                            }
                            $allTags = implode(',',$tagsNames );
                            echo ' '. $allTags . '</span>';
                          }
                      ?>
                      </div>
                      <p>
                        <?php echo $summary ?>
                        <span class="read-more"><?php echo '<a href="' . get_permalink() . '?current_site=' . $current_site . '">Read More</a>' ?></span>
                      </p>
                  </li>
                <?php }
                echo '</ul>'; ?>

                <div class="loading-screen">
                  <div class="spinner"></div>
                </div>
                <div class="load-more-container hidden">
                  <a href="#" id="load-more-link">Load More</a>
                </div>
                
            <?php } else { ?>
              <div class="no-results">
                <h3>No Results Found</h3>
              </div>
            <?php }
            /* Restore original Post Data */
            wp_reset_postdata(); ?>
        </div>
        <!-- <div class="news-filter-container">
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
        </div> -->
      </div>
    </div>
  </div>
</div>
<?php

//end loop
endwhile;
endif;

get_footer();
