<?php
/**
 * Template Name: Learning Library Intro
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

get_header(); ?>

<div class="full-width-page child-page">
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<div class="page-header">

				<h1>Introduction to the Learning Library</h1>
				
			</div>
			<div class="page-content">

                <?php // The ll-intro-container is for an introductory period of the WLL and can be removed when the client is done with this are. The scss can also be removed from the _learning_library.scss file.

                //site id variables
                $WRC = 75;
                $auto1st = 5;
                $WRCagency = 77;
                $WASI = 72;
                ?>
                <div class="ll-intro-container">
                    <?php
                    if ( $site->ID === $WRC ) : ?>
                        <p><span style="background-color: #8b2131; color: #fff; font-weight: bold; padding: 0.5rem;"> WRC </span></p>
                        <p>View this brief video for an overview of the Learning Library, including how to find videos, launch programs, enter and exit full screen mode, mark videos as favorites and share feedback.</p>
                        <p>To watch this video again, just click on the replay icon in the bottom left corner of the video. You also can click on the refresh icon on your browser to display the video, then click the play button.</p>
                        <p><a href="/wrc/learning-library/">Return to Learning Library</a></p>
                        <style>.embed-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; } .embed-container iframe, .embed-container object, .embed-container embed { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }</style><div class='embed-container'><iframe src='https://www.youtube.com/embed/CuUgq6Hp7tg' frameborder='0' allowfullscreen></iframe></div>
                    <?php elseif ( $site->ID === $auto1st ) : ?>
                        <p><span style="background-color: #8b2131; color: #fff; font-weight: bold; padding: 0.5rem;"> 1st Auto </span></p>
                        <p>View this brief video for an overview of the Learning Library, including how to find videos, launch programs, enter and exit full screen mode, mark videos as favorites and share feedback. </p>
                        <p>To watch this video again, just click on the replay icon in the bottom left corner of the video. You also can click on the refresh icon on your browser to display the video, then click the play button.</p>
                        <p><a href="/1st-auto/learning-library/">Return to Learning Library</a></p>
                        <style>.embed-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; } .embed-container iframe, .embed-container object, .embed-container embed { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }</style><div class='embed-container'><iframe src='https://www.youtube.com/embed/ZKjaCSV-nb8' frameborder='0' allowfullscreen></iframe></div>
                    <?php elseif ( $site->ID === $WRCagency ) : ?>
                        <p><span style="background-color: #8b2131; color: #fff; font-weight: bold; padding: 0.5rem;"> WRC Agency </span></p>
                        <p>View this brief video for an overview of the Learning Library, including how to find videos, launch programs, enter and exit full screen mode, mark videos as favorites and share feedback. </p>
                        <p>To watch this video again, just click on the replay icon in the bottom left corner of the video. You also can click on the refresh icon on your browser to display the video, then click the play button.</p>
                        <p><a href="/wrc-agency/learning-library/">Return to Learning Library</a></p>
                        <style>.embed-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; } .embed-container iframe, .embed-container object, .embed-container embed { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }</style><div class='embed-container'><iframe src='https://www.youtube.com/embed/rlHkSC9A8_A' frameborder='0' allowfullscreen></iframe></div>
                    <?php elseif ( $site->ID === $WASI ) : ?>
                        <p><span style="background-color: #8b2131; color: #fff; font-weight: bold; padding: 0.5rem;"> WASI </span></p>
                        <p>View this brief video for an overview of the Learning Library, including how to find videos, launch programs, enter and exit full screen mode, mark videos as favorites and share feedback.</p>
                        <p>To watch this video again, just click on the replay icon in the bottom left corner of the video. You also can click on the refresh icon on your browser to display the video, then click the play button.</p>
                        <p><a href="/wasi/learning-library/">Return to Learning Library</a></p>
                        <style>.embed-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; } .embed-container iframe, .embed-container object, .embed-container embed { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }</style><div class='embed-container'><iframe src='https://www.youtube.com/embed/CuUgq6Hp7tg' frameborder='0' allowfullscreen></iframe></div>
                    <?php else: //do nothing
                    endif; ?>
                </div>

			</div>
		</main>
	</div>
</div>



<?php
get_footer();
