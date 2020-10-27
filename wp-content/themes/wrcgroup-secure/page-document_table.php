<?php
/**
 * Template Name: Document Table
 *
 * This template contains a small react app that renders the document table based
 * on the parameters of this page (ie: category/site etc.) as well as the permissions
 * of the viewing user (eg: state)
 * @package WRC Secure
 */

get_header();
  if ( have_posts() ): while ( have_posts() ): the_post();
?>

<div class="page_container">

  <div class="mobile_table_nav_wrapper">
    <span class="mobile_table_nav_title">Menu</span>
    <div class="mobile_table_nav">
      <?php
        //output left nav for mobile
          get_template_part('template-parts/content', 'left-navigation');
      ?>
    </div>
  </div>
  <?php
    $permissions = build_user_permissions();
    $site = get_current_site();
    //Query for the relevant documents for this page, then localize them into the react script.
    //The react script is enqueued into the footer, so it's safe to localize it here.

    //First we grab categories
    $hide_states = get_field('hide_doc_states', $site->ID);
    $categories = get_field('document_categories');
    $sortDirection = get_field('reverse_sort');
    //Then we create the state meta query
    $stateMetaQuery = array('relation' => 'OR');
    if($permissions['states']){
      foreach($permissions['states'] as $state) {
        $stateQuery = array(
          'key' => 'states',
          'value' => $state,
          'compare' => 'LIKE'
        );
        array_push($stateMetaQuery, $stateQuery);
      }
    }
    //Then the site meta query
    $siteMetaQuery = array(
      'key' => 'site',
      'value' => $site->ID,
      'compare' => 'LIKE'
    );
    //Then we stitch all the parts together into an actual query
    $documentArgs = array(
      'post_type' => 'document',
      'posts_per_page' => -1,
      'tax_query' => array(
        array(
          'taxonomy' => 'document_category',
          'field' => 'term_id',
          'terms' => $categories
        )
      ),
      'meta_query' => array(
        $stateMetaQuery,
        $siteMetaQuery
      )
    );
    $documentQuery = new WP_Query($documentArgs);

    //Then we loop through and build an array of documents to give react
    $documents = array();
    if( $documentQuery->have_posts()): while( $documentQuery->have_posts()):
      $documentQuery->the_post();
      $files = array();
      if(have_rows('files')): while(have_rows('files')): the_row();
        $file = get_sub_field('file');
        $modified = date('F j, Y', strtotime($file['modified']));
        $fileReturn = array(
          'url' => $file['url'],
          'mime' => $file['mime_type'],
          'modified' => $modified
        );
        array_push($files, $fileReturn);
      endwhile; endif;
      $currentDocument = array(
        'id' => get_the_ID(),
        'title' => html_entity_decode( get_the_title() ), //avoid weird special characters
        'description' => get_field('description'),
        'states' => get_field('states'),
        'files' => $files,
        'modified' => get_the_modified_date()
      );
      array_push($documents, $currentDocument);
    endwhile;
    endif;

    //Then we give Them to react
    wp_localize_script('react-document-table', 'tableParams', array('documents' => $documents, 'user_states' => $permissions['states'], 'reverse_sort' => $sortDirection, 'hide_states' => $hide_states));

    //Reset the query so it's not all jacked up.
    wp_reset_query();
    /**
    * THIS STARTS THE ACTUAL OUTPUT
    **/
    //get_template_part('template-parts/content', 'site-navigation');
  ?>
  <div class="page_content_wrapper">
  <?php
    //Output left navigation
    $documentTableClass = "full_width_document_table";
      $documentTableClass = "";
      get_template_part('template-parts/content', 'left-navigation');
  ?>
    <div class="document_template_right_section <?php echo $documentTableClass; ?>">
      <header class="entry-header">
        <h1><?php the_title();?></h1>
      </header>
      <?php if(get_the_content()) : ?>
      <div class="document_template_content wysiwyg_content">
        <?php
          the_content();
        ?>
      </div>
      <?php endif; ?>
      <div id="document_table" class="document_table">
        <!-- React app outputs in here -->
      </div>
      <?php if ( get_field('document_table_additional_info') ) { ?>
        <div class="document_template_additional_content wysiwyg_content">
          <?php
            the_field('document_table_additional_info');
          ?>
        </div>
      <?php } ?>

    </div>
  </div>
</div>


<?php

//end loop
endwhile;
endif;

get_footer();
