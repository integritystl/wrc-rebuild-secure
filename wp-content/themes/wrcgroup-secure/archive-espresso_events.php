<?php
/**
 *  Event List Archive from Event Espresso
 *
 * This template will display a list of your events. Needed for links to Events from Critical Pages
 *
 * @ package		Event Espresso
 * @ author		Seth Shoultes
 * @ copyright	(c) 2008-2014 Event Espresso  All Rights Reserved.
 * @ license		http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link			http://www.eventespresso.com
 * @ version		EE4+
 */
get_header();
?>
<div class="page_container events-page">
	<section id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			<?php espresso_get_template_part( 'loop', 'espresso_events' ); ?>
		</div><!-- #content -->
	</section><!-- #primary -->
</div>
<?php
get_footer();
