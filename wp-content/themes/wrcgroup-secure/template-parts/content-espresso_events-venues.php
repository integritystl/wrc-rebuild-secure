<?php
// The template part for Espresso Event venues displayed on Events. You can find a a fresh copy of it & the parts it references in the plugin at:
// plugins/event-espresso-core-reg/public/Espresso_Arabica_2014
//echo '<br/><h6 style="color:#2EA2CC;">'. __FILE__ . ' &nbsp; <span style="font-weight:normal;color:#E76700"> Line #: ' . __LINE__ . '</span></h6>';
if (
	( is_single() && espresso_display_venue_in_event_details() )
	|| ( is_archive() && espresso_display_venue_in_event_list() )
) :
	global $post;
	do_action( 'AHEE_event_details_before_venue_details', $post );
	$venue_name = espresso_venue_name( 0, '', FALSE );
	if ( empty( $venue_name ) && espresso_is_venue_private() ) {
		do_action( 'AHEE_event_details_after_venue_details', $post );
		return '';
	}
?>

<div class="espresso-venue-dv<?php echo espresso_is_venue_private() ? ' espresso-private-venue-dv' : ''; ?>">
	<h3 class="event-venues-h3 ee-event-h3"><?php _e( 'Location:', 'event_espresso' ); ?></h3>

	<h4><?php echo $venue_name; ?></h4>
	<?php if (espresso_venue_categories() ) { ?>
		<p><span class="tags-links"><?php echo espresso_venue_categories(); ?></span></p>
	<?php } ?>

<?php  if ( $venue_phone = espresso_venue_phone( $post->ID, FALSE )) : ?>
	<span class="venue-detail">
		<h5><?php _e( 'Venue Phone:', 'event_espresso' ); ?></h5>
		<?php echo $venue_phone; ?>
	</span>
<?php endif;  ?>

<?php if ( $venue_website = espresso_venue_website( $post->ID, FALSE )) : ?>
	<span class="venue-detail">
		<h5><?php _e( 'Venue Website:', 'event_espresso' ); ?></h5>
		<?php echo $venue_website; ?>
	</span>
<?php endif; ?>

<?php  if ( espresso_venue_has_address( $post->ID )) : ?>
	<span class="venue-detail">
		<h5><?php _e( 'Address:', 'event_espresso' ); ?></h5>
		<?php espresso_venue_address( 'inline' ); ?>
		<?php espresso_venue_gmap( $post->ID ); ?>
		<div class="clear"><br/></div>
	</span>
<?php endif;  ?>

	<?php $VNU_ID = espresso_venue_id( $post->ID ); ?>
	<?php if ( is_single() ) : ?>
		<?php $venue_description = espresso_venue_description( $VNU_ID, FALSE ); ?>
		<?php if ( $venue_description ) : ?>
			<span class="venue-detail">
				<h5><?php _e( 'Venue Description:', 'event_espresso' ); ?></h5>
				<span><?php echo do_shortcode( $venue_description ); ?></span>
			</span>
		<?php endif;  ?>
	<?php else : ?>
		<?php $venue_excerpt = espresso_venue_excerpt( $VNU_ID, FALSE ); ?>
		<?php if ( $venue_excerpt ) : ?>
			<span class="venue-detail">
				<h5><?php _e( 'Venue Description:', 'event_espresso' ); ?></h5>
				<span><?php echo $venue_excerpt; ?></span>
			</span>
			<?php endif;  ?>
		<?php endif;  ?>
</div>
<!-- .espresso-venue-dv -->
<?php
do_action( 'AHEE_event_details_after_venue_details', $post );
else :
	if ( espresso_venue_is_password_protected() ) :
?>
	<div class="espresso-venue-dv  espresso-password-protected-venue-dv" >
		<h3 class="event-venues-h3 ee-event-h3">
			<?php _e( 'Location', 'event_espresso' );?>
		</h3>
		<?php echo espresso_password_protected_venue_form(); ?>
	</div>
<?php
	endif;
endif;
?>
