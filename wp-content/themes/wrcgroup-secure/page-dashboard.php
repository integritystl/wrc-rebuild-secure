<?php
/**
 * Template Name: User Dashboard
 *
 * @package WRC Secure
 */

get_header();
  
?>

  <div class="page_container user-dashboard">

    <div class="com-row row-1">
      <div class="com-col dash-nav">
        <?php 
          $siteArgs = array(
            'orderby'=>'menu_order',
            'order' => 'ASC',
            'post_type' => 'site'
          );
          $query = new WP_Query( $siteArgs );
          ?>
          <?php if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
              $query->the_post(); 
              $landingPage = get_field('landing_page');
              $image = get_field('site_logo');
              $imageURL = $image['url'];
              $imageALT = $image['alt'];
              ?>
                  <div id="" class="site_logos">
                      <a href="<?php echo $landingPage; ?>">
                        <img src="<?php echo $imageURL; ?>" alt="<?php echo $imageALT; ?>"/>
                      </a>
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
          <div class="document-grid">
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

    <div class="com-row row-2">
      
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
          <div class="news">
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
          <div class="learning-library-list">
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
           <div class="events">
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

    <div class="com-row row-3">
      <div class="com-col dash-contact">
        <?php $phoneNumber = get_field('tech_assistance_phone', 'option'); ?>
        <h2>Support</h2>
        <p><i class="fa fa-envelope"></i><a href="mailto:<?php echo get_field('tech_assistance_email', 'option'); ?>"><?php echo get_field('tech_assistance_email', 'options'); ?></a></p>
        <p><i class="fa fa-phone"></i><a href="tel:<?php echo $phoneNumber; ?>"><?php echo $phoneNumber; ?></a></p>
      </div>
      <div class="com-col dash-update">
        <h2>Update Your Account</h2>
      </div>
    </div>

    
  </div>
  <?php



get_footer();
