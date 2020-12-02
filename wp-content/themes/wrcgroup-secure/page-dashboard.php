<?php
/**
 * Template Name: User Dashboard
 *
 * @package WRC Secure
 */

get_header();
  
?>

  <div class="page_container user-dashboard">
    <div class="comp-row row-1">
      <div class="comp-col dash-nav">
        <?php 
          $siteArgs = array(
            'orderby'=>'menu_order',
            'order' => 'ASC',
            'post_type' => 'site'
          );
          $siteQuery = new WP_Query($siteArgs);
          $permissions = build_user_permissions();
          $site_landing_page = get_field('landing_page');
          $image = the_field('site_logo');
          ?>
          <div class="document_template_right_section">
          <?php if( have_posts() ) {
              while ( $siteQuery->have_posts() ) {
                $siteQuery->the_post(); ?>
                  <div id="document_table" class="document_table">
                      <a href="<?php the_field('landing_page'); ?>"><img src="<?php  wp_get_attachment_image_src(the_field('site_logo')); ?>"><?php the_title(); ?></a>
                  </div>
              <?php }
          }
          else {
              echo '<li>No Title</li>';
          }
          wp_reset_query();

        ?>
      </div>

      <div class="com-col dash-docs">
        <h2>Recently Added Docs</h2>
       <?php
          $permissions = build_user_permissions();

          //Then we stitch all the parts together into an actual query
          $documentArgs = array(
            'order' => 'DESC',
            'orderby' => 'date',
            'post_type' => 'document',
            'posts_per_page' => 5,
          );
          $documentQuery = new WP_Query($documentArgs);?>
          <div class="document_template_right_section">
          <?php if( have_posts() ) {
              while ( $documentQuery->have_posts() ) {
                $documentQuery->the_post(); ?>
                  <div id="document_table" class="document_table">
                      <p><?php the_title(); ?></p>
                  </div>
              <?php }
          }
          else {
              echo '<li>No Title</li>';
          }
          wp_reset_query();

        ?>
        </div>
        
      </div>
    </div>

    <div class="comp-row row-2">
      
      <div class="com-col dash-news">
        <h2>News/Newsletters</h2>
       <?php
          $permissions = build_user_permissions();

          //Then we stitch all the parts together into an actual query
          $newsArgs = array(
            'order' => 'DESC',
            'orderby' => 'date',
            'posts_per_page' => 5,
          );
          $newsQuery = new WP_Query($newsArgs);?>
          <div class="document_template_right_section">
          <?php if( have_posts() ) {
              while ( $newsQuery->have_posts() ) {
                $newsQuery->the_post(); ?>
                  <div id="document_table" class="document_table">
                      <p><?php the_title(); ?></p>
                  </div>
              <?php }
          }
          else {
              echo '<li>No Title</li>';
          }
          wp_reset_query();

        ?>
        </div>
        
      </div>

      <div class="com-col dash-news">
        <h2>Learning Library</h2>
       <?php
          $permissions = build_user_permissions();

          //Then we stitch all the parts together into an actual query
          $llArgs = array(
            'order' => 'DESC',
            'orderby' => 'date',
            'posts_per_page' => 5,
            'post_type' => 'learning-library',
          );
          $llQuery = new WP_Query($llArgs);?>
          <div class="document_template_right_section">
          <?php if( have_posts() ) {
              while ( $llQuery->have_posts() ) {
                $llQuery->the_post(); ?>
                  <div id="document_table" class="document_table">
                      <p><?php the_title(); ?></p>
                  </div>
              <?php }
          }
          else {
              echo '<li>No Title</li>';
          }
          wp_reset_query();

        ?>
        </div>
        
      </div>

     <div class="com-col dash-news">
        <h2>Upcoming Events</h2>

          <?php //Then we stitch all the parts together into an actual query
          $eventsAtts = array(
            'limit' => 5,
            'show_expired' => FALSE,
            'month' => NULL,
            'order_by' => 'start_date',
            'sort' => 'ASC',
          );
          //using a query from Event Espresso to access dates/expirations easier
          $event_query = new EventEspresso\core\domain\services\wp_queries\EventListQuery( $eventsAtts );
          ?>
           <div class="document_template_right_section">
            <?php if( have_posts() ) {
              while ( $event_query->have_posts() ) {
                $event_query->the_post(); ?>
                  <div id="document_table" class="document_table">
                      <p><?php the_title(); ?></p>
                  </div>
              <?php }
            }
            else {
                echo '<li>No Title</li>';
            }
            wp_reset_query();

        ?>
        </div>  
        </div>
        
      </div>

    
  </div>
  <?php



get_footer();
