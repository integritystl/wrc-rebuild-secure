<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package wrcgroup-secure
 */
?>

<?php
$current_site = $_GET['current_site'];
?>

<section id="primary" class="no-results not-found">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'No Items Found', 'wrcgroup-secure' ); ?></h1>
		<p>Please try another search term</p>
	</header><!-- .page-header -->
	<div class="no-results-search-form">
		<form action="<?php echo home_url();?>">
			<input class="full_width" type="text" value="" name="s" id="s" />
			<input type="hidden" name="current_site" id="current_site" value="<?php echo $current_site ?>"/>
			<input type="submit" id="searchsubmit" class="btn-primary" value="Search" />
		</form>
	</div>

</section><!-- .no-results -->
