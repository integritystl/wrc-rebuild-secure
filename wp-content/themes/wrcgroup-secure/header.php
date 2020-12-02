<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package wrcgroup-secure
 */


  $permissions = build_user_permissions();

  //grab all pages using the site page template
  $site_pages = get_pages(array(
      'meta_key' => '_wp_page_template',
      'meta_value' => 'page-site-template.php'
  ));

  $logo = get_field('logo_img', 'option');
  $logo = $logo['sizes']['large'];
  $current_site = get_current_site();
  //current_site should never be empty. get_current_site function will redirect if empty
  //check if empty for now since this isn't implemented
  $news_page = NULL;
  $news_query = NULL;
  $news_item_markup = '';
  $NEW_NEWS_MAX = 3;
  $userID = get_current_user_id();
  $prev_login = NULL;

  if(!empty($current_site)){
    $news_page = get_field('news_page', $current_site->ID);

    if(!empty($news_page)){
      $news_categories = get_field('news_categories', $news_page->ID);

      $news_query = new WP_Query(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'cat' => $news_categories,
        'orderby' => 'date',
        'posts_per_page' => $NEW_NEWS_MAX
      ));

      //setting new news cookies should be called before markup output
      //markup will look at cookie to see if new post
      $new_post_IDs = array();
      if($userID !== 0){
        $prev_login = get_user_meta( $userID, '_prev_last_login', true );
        if(!empty($news_query) && $news_query->have_posts()){
          foreach ($news_query->posts as $post) {
            $timestamp = strtotime($post->post_date);

            //add post ID to array if it was created after the user last logged in
            if($timestamp > ((int) $prev_login)){
              //used to determine which posts have been viewed by the user
              array_push($new_post_IDs, $post->ID);
            }
          }

          set_new_news_cookie($new_post_IDs, $current_site->ID);
        }
      }

      /* this markup will be used to display the news menu item for desktop and mobile */
      $news_item_markup = get_news_nav_item_markup($news_query, $news_page, $current_site);
    }
  }

  //Library for Library Dropdown
  $library_page = NULL;
  $library_query = NULL;
  $learning_nav_item_markup = '';
  $NEW_LIB_MAX = 3;

   if(!empty($current_site)){
     $library_page = get_field('library_page', $current_site->ID);

     if(!empty($library_page)){
         $learning_categories = get_field('learning_category', $library_page->ID);
         $query_args = array(
           'post_type' => 'learning-library',
           'post_status' => 'publish',
           'tax_query' => array(
             'taxonomy' => 'learning_category',
             'field'    => 'term_id',
             'terms'    => $learning_categories,
           ),
           'orderby' => 'date',
           //'posts_per_page' => $NEW_LIB_MAX
         );
         //var_dump($query_args); die;
         $library_query = new WP_Query($query_args);
         //var_dump($library_query);
      

       //setting new Library cookies should be called before markup output
       //markup will look at cookie to see if new post
       $library_post_IDs = array();
       if($userID !== 0){
         $prev_login = get_user_meta( $userID, '_prev_last_login', true );
         if(!empty($library_query) && $library_query->have_posts()){
           foreach ($library_query->posts as $post) {
             if(learning_post_sites_contain_id($post->ID, $current_site->ID)){
               $timestamp = strtotime($post->post_date);

               //add post ID to array if it was created after the user last logged in
               if($timestamp > ((int) $prev_login)){
                 //used to determine which posts have been viewed by the user
                 array_push($library_post_IDs, $post->ID);
               }
             }
           }

          
           set_new_library_cookie($library_post_IDs, $current_site->ID);
         }
       }

       $learning_nav_item_markup = get_learning_nav_item_markup($library_query, $library_page, $current_site);
     }
   }


  //Events for Event Dropdown
  $events_page = NULL;
  $events_query = NULL;
  $events_nav_item_markup = '';
  include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
  if (!is_plugin_active('event-espresso-core-reg/espresso.php') ) {
    //dunno what we'd do if the plugin isn't here
  } else {
    if(!empty($current_site)){
      $events_page = get_field('events_page', $current_site->ID);

      if(!empty($events_page)){
        $eventsAtts = array(
          'title' => NULL,
          'limit' => 3,
          'css_class' => NULL,
          'show_expired' => FALSE,
          'month' => NULL,
          'category_slug' => $current_site->post_name,
          'order_by' => 'start_date',
          'sort' => 'ASC',
        );

        //using a query from Event Espresso to access dates/expirations easier
        $event_query = new EventEspresso\core\domain\services\wp_queries\EventListQuery( $eventsAtts );

        //setting new news cookies should be called before markup output
        //markup will look at cookie to see if new post
        $new_event_IDs = array();
        //check query so we can get ids to use for New Event cookie
        // same as News cookie, except with events & have to make sure plugin is active
        if($userID !== 0){
          $prev_login = get_user_meta( $userID, '_prev_last_login', true );

          if(!empty($event_query && $event_query->have_posts()) ) {
            foreach($event_query->posts as $event) {
              //event_start_date instead of post_date
                $timestamp = strtotime($event->post_date);

                if($timestamp > ((int) $prev_login)) {
                    array_push($new_event_IDs, $event->ID);
                }
            }

            set_new_events_cookie($new_event_IDs, $current_site->ID);
          }
        } //end user if check

        $events_nav_item_markup = get_events_nav_item_markup($event_query, $events_page, $current_site);

      }
    }
  }

  //generate reusable menu item markup
  $user_account_nav_item_markup = get_user_account_nav_item_markup($current_site);
  $secure_email_nav_item_markup = get_secure_email_nav_item_markup();
  $sites_dropdown_markup = get_sites_dropdown_markup($permissions, $site_pages, $current_site);

  if(isset($current_site)){
    $site_menu = get_field('top_menu', $current_site->ID);
    $site_landing_page = get_field('landing_page', $current_site->ID);
    $site_logo = get_field('site_logo', $current_site->ID);
    $site_logo = $site_logo['sizes']['large'];
    $help_page = get_field('help_page', $current_site->ID);
  }
  else if ( is_page_template('page-events.php') || is_post_type_archive('espresso_events')) {
    // can't use any of the site specific stuff on Event Espresso critical pages, so just do nothing
    $site_menu = NULL;
    $site_logo = NULL;
    $help_page = NULL;
  }
  else {
    $current_site = $permissions['sites'][0];
    $site_menu = get_field('top_menu', $current_site->ID);
    $site_landing_page = get_field('landing_page', $current_site->ID);
    $site_logo = get_field('site_logo', $current_site->ID);
    $site_logo = $site_logo['sizes']['large'];
    $help_page = get_field('help_page', $current_site->ID);
  }


?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<meta name="google-site-verification" content="8bFL_kxfMH4WpFv201tHeY90H1JJW-Q7sGJ6OZVP_TM" />
<?php
  //Event Espresso Critical pages are outside the scope of Current Site, so don't check for site settings to get
  // the favicon, just do the one from our theme
  if( (is_page_template('page-events.php') || is_post_type_archive('espresso_events') ) ) { ?>
    <link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/images/wrc-building-favicon.png">
<?php } else {
          //Non-Event Espresso Critical pages can check Site settings for Favicon
          $siteFavicon = get_field('site_favicon', $current_site->ID);
          if ($siteFavicon) { ?>
            <link rel="shortcut icon" href="<?php echo $siteFavicon['url']; ?>">
      <?php } else { ?>
          <link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/images/wrc-building-favicon.png">
  <?php }
} ?>

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php if(!empty($current_site)) : ?>
  <div id="site-holder" data-site="<?php echo $current_site->ID;?>">This will disappear</div>
<?php endif; ?>
<div id="page" class="site">
  <header id="top-menu" class="top-menu">
    <div class="header-container">
      <div class="branding-container">
        <a class="logo-link header-link" href="<?php the_field('logo_link', 'option'); ?>"><img src="<?php echo $logo; ?>"/></a>
        <?php echo $sites_dropdown_markup; ?>
      </div>
      <div class="navigation-user-container">
        <ul class="navigation-links-list">
          <?php echo $secure_email_nav_item_markup; ?>
          <?php echo $news_item_markup; ?>
          <?php echo $learning_nav_item_markup; ?>
          <?php echo $events_nav_item_markup; ?>
        </ul>
        <ul class="user-links-list">
          <?php echo $user_account_nav_item_markup; ?>

          <?php // if there's a help output it
            if ($help_page) { ?>
              <li class="user-links__help-item"><a href="<?php echo $help_page; ?>" class="header-link"><i class="fa fa-question-circle" aria-hidden="true"></i></a></li>
              <?php }   ?>

          <li class="user-links__search-item">
            <span class="header-link" aria-label="Search"><i class="fa fa-search" aria-hidden="true"></i></span>
            <div class="header_search_form" aria-expanded="false">
              <span class="close_header_search"><i class="fa fa-close"></i></span>
              <form id="header_search" action="<?php echo home_url();?>">
                <input class="full_width" type="text" value="" name="s" id="s" />
                <input type="hidden" name="current_site" id="current_site" value="<?php echo $current_site->ID ?>"/>
                <input type="submit" id="searchsubmit" value="Search" />
              </form>
            </div>
          </li>

          <li class="user-links__mobile-nav-item">
            <button id="mobile-nav-trigger">
              <span class="top"></span>
              <span class="middle"></span>
              <span class="bottom"></span>
            </button>
            <div id="mobile-nav-dropdown">
              <div class="mobile-nav__sites-container">
                <?php echo $sites_dropdown_markup; ?>
              </div>
              <div class="mobile-nav__nav-list-container">
                <?php if( !(is_page_template('page-events.php') || is_post_type_archive('espresso_events') ) )  { ?>
                  <div class="site-top-menu">
                    <?php wp_nav_menu(array('menu' => $site_menu)); ?>
                  </div>
                <?php } ?>
              <ul class="navigation-links-list">
                <?php echo $secure_email_nav_item_markup; ?>
                <?php //Mobile menu for News/Event doesn't include list of New Items ?>
                <a class="news-link" href="<?php echo get_the_permalink($news_page->ID);?>">News<span class="new-count"></span></a>
                <a class="news-link" href="<?php echo get_the_permalink($library_page->ID);?>">Learning Library<span class="new-count"></span></a>
                <a class="events-link" href="<?php echo get_the_permalink($events_page->ID); ?>">Events<span class="new-count"></span></a>
              </ul>
              <ul class="user-links-list">
                <?php echo $user_account_nav_item_markup; ?>
              </ul>

              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </header>
  <?php
  /**
  * Output the site menu just below the header menu.
  **/
  ?>
  <?php if( !(is_page_template('page-events.php') || is_post_type_archive('espresso_events') || is_page_template('page-dashboard.php') ) ) { ?>
  	<div class="site-top-menu-container">
        <div class="site-top-menu-site-name">
          <a href="<?php echo $site_landing_page; ?>">
            <img src="<?php echo $site_logo; ?>"/>
          </a>
        </div>
        <div class="site-top-menu">
          <?php wp_nav_menu(array('menu' => $site_menu)); ?>
        </div>
    </div>
  <?php } ?>

	<div id="content" class="site-content">
    <?php if(get_field('announcements_enabled', 'options')) : ?>
			<?php
				$expirationTime = 60;
				if(get_field('announcement_refresh_time', 'options')){
					$expirationTime = get_field('announcement_refresh_time', 'options');
				}
			?>
			<div data-expiration="<?php echo $expirationTime; ?>" class="announcements-banner closed">
				<div class="container">
					<?php
						$announcementHeader = get_field('announcement_header', 'options');
						$announcementText = get_field('announcement_text', 'options');
					?>

					<h4 class="announcement-header"><?php echo $announcementHeader; ?></h4>
					<p class="announcement-text"><?php echo $announcementText; ?></p>
					<a href="" class="announcement-close">Close</a>
				</div>
			</div>
		<?php endif; ?>
