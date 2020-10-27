<?php
/**
 * Template Name: Events Listing
 *
 * @package wrcgroup-secure
 */

get_header();

$permissions = build_user_permissions();
$site = get_current_site();
// var_dump($site);
  //Look for the category with the same name as the Site to query by. Requires Event Page to be set on Site
  if(!empty($site)){
    $events_page = get_field('events_page', $site->ID);

    if(!empty($events_page)){
      $eventsAtts = array(
        'title' => NULL,
        'limit' => 8,
        'css_class' => NULL,
        'show_expired' => FALSE,
        'month' => NULL,
        'category_slug' => $site->post_name,
        'order_by' => 'start_date',
        'sort' => 'ASC',
      );
    }
  }

  //var_dump($eventsAtts);
  $eventCatQuery = new EventEspresso\core\domain\services\wp_queries\EventListQuery( $eventsAtts );

  //var_dump($eventCatQuery); ?>
  <div class="page_container events-page">
    <div id="primary" class="content-area events-list">
      <h1>Events</h1>
        <?php if ( $eventCatQuery->have_posts() ):
      		while ( $eventCatQuery->have_posts() ) : $eventCatQuery->the_post(); ?>

              <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <div id="espresso-event-list-header-dv-<?php the_ID(); ?>" class="espresso-event-header-dv">
                  <header class="event-header">
                    <h2 id="event-details-<?php the_ID(); ?>" class="entry-title">
                  		<a class="ee-event-header-lnk" href="<?php the_permalink(); ?><?php echo '?current_site='. $site->ID; ?>"<?php echo \EED_Events_Archive::link_target();?>>
                              <?php the_title(); ?>
                          </a>
                  </h2>
                </div>
                <div class="event-datetimes"><?php espresso_event_date(); ?></div>
      			     <?php the_excerpt(); ?>
              </article>
      	<?php endwhile;
        else:
          echo "<p>Currently there are no upcoming events.</p>";
        endif;

        wp_reset_query(); ?>
    </div><!-- #primary -->
  </div>

<?php get_footer();
