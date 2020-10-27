<?php
$logo = get_field('side_nav_logo', 'option');
if(!empty($logo)){
  $logo = $logo['sizes']['large'];
}
  $site = get_current_site();

?>
<div class="left_section_navigation_container">
    <ul class="left_section_navigation_menu news_category_navigation_menu">
<!--      <li><a href="--><?php //echo get_field('landing_page', $site->ID); ?><!--"><i class="fa fa-chevron-left" aria-hidden="true"></i> Back to Documents</a></li>-->
    <?php
        $permissions = build_user_permissions();
        $current_site = get_current_site();
        output_news_navigation($permissions, $current_site, !(empty($isEmailNews)));
      ?>
    </ul>

    <div class="side-nav-logo">
      <?php if(!empty($logo)) : ?>
        <img src="<?php echo $logo?>"/>
      <?php endif; ?>
    </div>
</div>
