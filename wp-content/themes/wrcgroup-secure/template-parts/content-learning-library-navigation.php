<?php
$logo = get_field('side_nav_logo', 'option');
if(!empty($logo)){
  $logo = $logo['sizes']['large'];
}
  $site = get_current_site();

$favorites = get_field('favorites_page', $site->ID);
$favorites_url = '';
if(!empty($favorites)){
  $favorites_url = get_the_permalink($favorites->ID);
}

?>
<div class="left_section_navigation_container">
    <ul class="left_section_navigation_menu news_category_navigation_menu">
      <li><a href="ll-intro">Introduction</a></li>
      <li><a href="<?php echo $favorites_url ?>">Favorite Programs</a></li>
    <?php
        $permissions = build_user_permissions();
        $current_site = get_current_site();
        output_library_navigation($permissions, $current_site);
      ?>
    </ul>

    <div class="side-nav-logo">
      <?php if(!empty($logo)) : ?>
        <img src="<?php echo $logo?>"/>
      <?php endif; ?>
    </div>
</div>
