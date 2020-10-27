<?php
/**
 * Template Name: Staff
 *
 * @package WRC Secure
 */

get_header();
  if ( have_posts() ): while ( have_posts() ): the_post();
?>

<div class="page_container">
<div class="page_content_wrapper">
  <?php get_template_part('template-parts/content', 'left-navigation');?>
  <?php if(have_rows('divisions')) : ?>
    <div class="staff-container">
      <header class="entry-header">
        <h1><?php the_title();?></h1>
<!--        <div class="staff-buttons-container">-->
<!--          --><?php
//            //nifty plugin that can be used for both the download and print functionality
//            echo do_shortcode('[dkpdf-button]');
//          ?>
<!--        </div>-->
      </header>
      <?php while (have_rows('divisions')) : the_row();?>
        <div class="division-container">
          <h2 class="division-name"><?php the_sub_field('division_name'); ?></h2>
          <?php if(have_rows('division_staff')) : ?>
            <div class="division-staff-container">
              <?php while (have_rows('division_staff')) : the_row();
              $staffImg = get_sub_field('staff_image');
              //Get image tag with srcset attributes, alt, etc
              $staffImgObject = wp_get_attachment_image( $staffImg['ID'], 'medium_large' );
              ?>
                <div class="staff-member">
                  <div class="staff-image">
                    <?php
                    echo $staffImgObject; ?>
                  </div>
                  <div class="staff-info">
                    <h3><?php the_sub_field('staff_name'); ?></h3>
                    <h4 class="staff-title"><?php the_sub_field('staff_title'); ?></h4>

                    <p><span class="info-label">Phone:</span><?php the_sub_field('staff_phone'); ?></p>
                    <p><span class="info-label">Fax:</span><?php the_sub_field('staff_fax'); ?></p>
                    <p class="info-email"><a href="mailto:<?php the_sub_field('staff_email'); ?>"><?php the_sub_field('staff_email'); ?></a></p>
                    <?php if ( get_sub_field('staff_years') ) { ?>
                      <p><span class="info-label">Employee Since:</span><?php the_sub_field('staff_years'); ?></p>
                    <?php } ?>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
  </div>
</div>

<?php

//end loop
endwhile;
endif;

get_footer();
