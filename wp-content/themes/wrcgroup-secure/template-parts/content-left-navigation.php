<?php
$logo = get_field('side_nav_logo', 'option');
if(!empty($logo)){
  $logo = $logo['sizes']['large'];
}
?>
<div class="left_section_navigation_container">
    <ul class="left_section_navigation_menu">
    <?php
        $permissions = build_user_permissions();
        output_left_navigation(get_the_ID(), $permissions);
      ?>
    </ul>

    <div class="side-nav-logo">
      <?php if(!empty($logo)) : ?>
        <img src="<?php echo $logo?>"/>
      <?php endif; ?>
    </div>

</div>
