<?php

get_header();
  if ( have_posts() ): while ( have_posts() ): the_post();
?>

<div class="page_container">
  <div class="mobile_table_nav_wrapper">
    <span class="mobile_table_nav_title">Menu</span>
    <div class="mobile_table_nav">
      <?php
        //check how deep we are. If we are only 1 deep, then output full width since we have no left nav.
      //  $ancestorCheck = get_ancestors(get_the_ID(), 'page');
        get_template_part('template-parts/content', 'left-navigation');
      ?>
    </div>
  </div>
  <?php
    $permissions = build_user_permissions();
    $site = get_current_site();
  ?>
  <div class="page_content_wrapper">
  <?php
    $documentTableClass = "full_width_document_table";
    $documentTableClass = "";
    get_template_part('template-parts/content', 'left-navigation');
  ?>
    <div class="document_template_right_section <?php echo $documentTableClass; ?>">
      <header class="entry-header">
        <h1><?php the_title();?></h1>
      </header>
      <div class="document_template_content wysiwyg_content">
        <?php
          the_content();
        ?>
      </div>
     </div>
</div>


<?php

//end loop
endwhile;
endif;

get_footer();
