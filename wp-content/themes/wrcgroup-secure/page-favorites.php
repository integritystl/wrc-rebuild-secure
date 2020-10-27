<?php
/**
 * Template Name: Favorites List
 *
 * This template allows a user to update their account.
 * @package WRC Secure
 */

get_header();

$permissions = build_user_permissions();

$site = get_current_site();
$fav_page = get_field('favorites_page', $site->ID);
$current_user = wp_get_current_user();
$favorites_cats = get_field( 'learning_cats_favorites', $fav_page->ID);
$favorites_cats_slugs = array_column($favorites_cats, 'slug');

$filters = array(
  'post_type' => array(
    'learning-library'
  ),
  'terms' => array(
      'learning_category' => $favorites_cats_slugs
  ),
  'meta_key' => 'attached_site',
  'meta_value' => $site,
);


//The plugin calls Ajax twice reload All Favorites regardless of us setting the filters when No Favorites exist
// whenever we use `the_user_favorites_list` so we have to do this comparison first.
// We're also not including the Favorite button because if a user un-favorites a Post and it was the only one, it'll
// trigger the Ajax that loads the non-filtered posts :\
$current_user_favorites = get_user_favorites($current_user->ID, null, $filters);
wp_reset_postdata();
?>
<div class="page_container learning-library">
  <?php get_template_part('template-parts/content', 'learning-library-navigation');?>
  <div class="news-listing-container">
      <h1>Learning Library Favorites</h1>
    <div class="news-wrapper-all">
      <?php the_content(); ?>
      <div class="favorites-list">
        <?php
            if ($current_user_favorites) {
              the_user_favorites_list($current_user->ID, null, true, $filters, false, false, 'thumbnail', true);
            } else {
                //echo '<div class="news-list-container"><p class="news-no-results">You currently don\'t have any Learning Library favorites.</p></div>';
                //just load all the favorites - This edit should just remove this if else statement and load the function 'the_user_favorites_list', but because this is spaghetti stringed together I don't what to change something that might need to be quickly undone. 
                the_user_favorites_list($current_user->ID, null, true, $filters, false, false, 'thumbnail', true);
            }
        ?>
      </div>
    </div>
  </div>
</div>

<?php
get_footer();
