<?php
// The template part for Espresso Event content. You can find a a fresh copy of it & the parts it references in the plugin at:
// plugins/event-espresso-core-reg/public/Espresso_Arabica_2014
//
// We're using a custom template, but there's also custom ordering in the admin that sorts the order of modules in Events > Templates.
// However, this template file will display partials twice if you want custom ordering, so revert to default Espresso templates if enabling custom ordering (https://eventespresso.com/topic/event-details-showing-more-than-just-details/)
//add_filter( 'FHEE__EED_Event_Single__template_include__allow_custom_selected_template', '__return_true' );
// Excluding that action filter is the opposite of what EE documentation on setting up a custom Single View tells you to do (https://eventespresso.com/wiki/ee4-custom-post-types/)
//
//echo '<br/><h6 style="color:#2EA2CC;">'. __FILE__ . ' &nbsp; <span style="font-weight:normal;color:#E76700"> Line #: ' . __LINE__ . '</span></h6>';
/**
 * This template will display a single event - copy it to your theme folder
 *
 * @ package		Event Espresso
 * @ author		Seth Shoultes
 * @ copyright	(c) 2008-2013 Event Espresso  All Rights Reserved.
 * @ license		http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link			http://www.eventespresso.com
 * @ version		4+
 */

global $post;
$event_class = has_excerpt( $post->ID ) ? ' has-excerpt' : '';
$event_class = apply_filters( 'FHEE__content_espresso_events__event_class', $event_class );
?>
<?php do_action( 'AHEE_event_details_before_post', $post ); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $event_class ); ?>>

<?php if ( is_single() ) : ?>
	<div id="espresso-event-header-dv-<?php echo $post->ID;?>" class="espresso-event-header-dv">
		<header class="event-header">
				<h1 id="event-details-h1-<?php echo $post->ID; ?>" class="entry-title">
					<?php the_title(); ?>
				</h1>
		</header>
		<?php espresso_get_template_part( 'content', 'espresso_events-thumbnail' ); ?>
	</div>
	<div class="espresso-event-wrapper-dv">
			<?php //espresso_get_template_part( 'content', 'espresso_events-details' ); ?>
			<?php espresso_get_template_part( 'content', 'espresso_events-datetimes' ); ?>
			<?php //espresso_venue_name( 0, '', TRUE ); ?>
			<?php get_template_part( 'template-parts/content', 'espresso_events-venues' ); ?>
			<?php //get_template_part( 'template-parts/content', 'espresso_events-details' ); ?>
			<?php echo espresso_event_content_or_excerpt( 55, null, false ); ?>
			<?php espresso_get_template_part( 'content', 'espresso_events-tickets' ); ?>

	</div>


<?php
	//This isn't what displays the events in Event List view currently
	elseif ( is_archive() ) : ?>

	<div id="espresso-event-list-header-dv-<?php echo $post->ID;?>" class="espresso-event-header-dv event-archive-listing">
		<?php espresso_get_template_part( 'content', 'espresso_events-thumbnail' ); ?>
		<h2 id="event-details-h2-<?php echo $post->ID; ?>" class="entry-title">
			<a class="ee-event-header-lnk" href="<?php the_permalink(); ?>"<?php echo \EED_Events_Archive::link_target();?>>
				<?php the_title(); ?>
			</a>
		</h2>
	</div>

	<div class="espresso-event-list-wrapper-dv">
		<?php espresso_get_template_part( 'content', 'espresso_events-datetimes' ); ?>
		<?php espresso_get_template_part( 'content', 'espresso_events-details' ); ?>
	</div>

<?php endif; ?>

</article>
<!-- #post -->
<?php do_action( 'AHEE_event_details_after_post', $post );
