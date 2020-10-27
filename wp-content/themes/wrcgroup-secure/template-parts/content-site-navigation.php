<?php
/**
* This outputs the horizontal nav across the top of sites, beneath the external nav
**/
?>
<div class="section_navigation_container">
  <ul class="section_navigation_menu">
  <!-- Menu at the top w/ Resources/Claims etc. -->
  <?php
    //Get the top level "site" then grab all direct children;
    $ancestors = get_ancestors(get_the_ID(), 'page');
    $topLevelID = $ancestors[count($ancestors) - 1];
    wp_list_pages(array('child_of' => $topLevelID, 'title_li' => null, 'depth' => 1));

  ?>
  </ul>
</div>