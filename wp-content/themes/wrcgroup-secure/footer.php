<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
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

   $current_site = get_current_site();

   if(isset($current_site)){
     $footer_public_link = get_field('link_to_public', $current_site->ID);
     $footer_menu = get_field('footer_menu', $current_site->ID);
     $footer_contact_phone = get_field('contact_phone', $current_site->ID);
     $footer_contact_fax = get_field('contact_fax', $current_site->ID);
     $footer_contact_email = get_field('contact_email', $current_site->ID);
     $footer_suggestions_email = get_field('suggestion_email', $current_site->ID);

     //Get different format for tel attribute
     if (preg_match('/\((\d{3})\)\ (\d{3})\-(\d{4})$/', $footer_contact_phone, $matches)) {
       $formatedPhone = '1' . $matches[1] . '' .$matches[2] . '' .$matches[3];
     } else {
       //fallback to what's in the field if it doesn't match
       $formatedPhone = $footer_contact_phone;
     }
   }
?>

	</div><!-- #content -->

  <a href="#" id="back-to-top" title="Back to top">&uarr;</a>

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="site-info">
			<div class="site-info-row">

				<div class="footer-menu-container">
          <?php if (!empty($footer_menu) ) {
              wp_nav_menu(array('menu' => $footer_menu));
            } ?>

  					<div class="contact-info">
              <?php if(!empty($footer_contact_phone)) { ?>
                <p><i class="fa fa-phone"></i><span>Phone:</span> <a href="tel:<?php echo $formatedPhone; ?>"><?php echo $footer_contact_phone; ?></a></p>
              <?php }?>
              <?php if(!empty($footer_contact_fax)) { ?>
                <p><i class="fa fa-fax"></i><span>Fax:</span> <?php echo $footer_contact_fax; ?></p>
              <?php }?>
              <?php if(!empty($footer_contact_email)) { ?>
  						  <p><i class="fa fa-envelope"></i><span>Email:</span> <a href="mailto:<?php echo $footer_contact_email; ?>"><?php echo $footer_contact_email; ?></a></p>
              <?php }?>
              <?php if(!empty($footer_suggestions_email)) { ?>
  						   <p><i class="fa fa-comment"></i><span>Suggestions:</span> <a href="mailto:<?php echo $footer_suggestions_email; ?>"><?php echo $footer_suggestions_email; ?></a></p>
              <?php }?>
            </div>

				</div>
			</div>
			<div class="site-info-row">
				<div class="copyright-container">
					<span>&copy; <?php echo(date('Y')); ?> The WRC Group. All Rights Reserved.</span>
				</div>
				<div class="secondary-links-container">
					<a href="<?php the_field('legal_notice_link', 'option'); ?>">Legal Notice</a>
					<a href="<?php the_field('privacy_policy_link', 'option'); ?>">Privacy Policy</a>
				</div>
			</div>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
<!--End of Footer-->
</div><!-- #page -->
<?php get_template_part('template-parts/modal', 'bridge'); ?>
<?php if (is_singular('learning-library')) {
  get_template_part('template-parts/modal', 'll-form');
} ?>

<?php wp_footer(); ?>

</body>
</html>
