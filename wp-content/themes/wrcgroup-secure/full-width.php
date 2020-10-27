<?php
/**
 * Template Name: Full Width
 *
 * This template queries for the news Posts for a Site
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package wrcgroup_marketing
 */
 //Get the current site that we are on.
 $site = get_current_site();
 //Using ID of current site, find the ID of the Landing Page it links to use for our Child Menu
	$siteID = $site->ID;
	$parentID = url_to_postid( get_field('landing_page', $siteID) );
	$imgObject = wp_get_attachment_image( get_post_thumbnail_id(), 'full' );

	// Column fields
	$oneCol = get_field('full_width_content');

	$Form = get_field('form_shortcode');
	$twoColLeftContent = get_field('left_content');
	$twoColRtContent = get_field('right_content');

get_header(); ?>

<div class="full-width-page child-page">
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<div class="page-header">
				<div class="image" >
				<?php echo $imgObject; ?>
				</div>
				<h1><?php the_title(); ?></h1>
				
			</div>
			<div class="page-content">
				<?php if (get_field('columns') == '1') { ?>
				 <div class="col">
					<?php if ($oneCol): ?>
				        <?php echo $oneCol; ?>
			      	<?php endif; ?>
				 </div>
				 <?php } elseif (get_field('columns') == '2') { ?>
				 <div class="col-2">
				 	<div class="left-col">
				 		<?php if ($twoColLeftContent): ?>
					        <?php echo $twoColLeftContent; ?>
				      	<?php endif; ?>
				    </div>
				    <div class="right-col">
				    	<?php if ($twoColRtContent): ?>
					        <?php echo $twoColRtContent; ?>
				      	<?php endif; ?>
				      	<?php if ($Form): ?>
					        <?php echo $Form; ?>
				      	<?php endif; ?>
				    </div>
				 </div>
				<?php } ?>

			</div>
		</main>
	</div>
</div>



<?php
get_footer();
