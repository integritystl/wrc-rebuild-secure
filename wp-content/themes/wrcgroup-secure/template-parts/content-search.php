<?php
/**
 * Template part for displaying results in search pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package wrcgroup-secure
 */


$news_page = get_field('news_page', $current_site);
//get the Categories ACF field from the site's News page so we only get categories for our Current Site for Posts
$siteNewsCategories = get_field('news_categories', $news_page->ID);

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<h2>
			<?php
				if('document' !== get_post_type()):
					echo '<a href="' . get_permalink() . '?current_site=' . $current_site . '">';
				the_title();?>
				</a>
			<?php
				else:
					the_title();
				endif;
			?>
		</h2>
		<h4 class="search-result-post-type">
			<span class="search-result-post-type--site">
				<?php
					//Get the current site's name
					echo get_the_title($current_site);
 				?>
			</span>
			<?php
				//Output the type of post so the user knows if it's a document or post or page
				echo get_post_type();
			?>
		</h4>

		<?php if ( 'post' === get_post_type() ) : ?>
		<div class="post-meta">
			<span class="posted-on"><?php echo get_the_date('F j, Y'); ?></span>
				<?php
				// Get Catgories for Post
				$categories = get_the_category();
				$cat_entries = array();
				foreach ($categories as $category) {
					if($category->category_parent !== 0 && in_array($category->term_id, $siteNewsCategories)){
						array_push($cat_entries, array('id' => $category->term_id, 'name' => $category->name));
					}
				} ?>
				<span class="categories">
					<?php
						foreach($cat_entries as $cat_entry){
							echo '<span class="archive-listing-category">' . $cat_entry['name'] . '</span>';
						}
						?>
				</span>

				<?php
					//Get Tags for Posts
					$all_query_tags = array();
					$tags = wp_get_post_tags(get_the_ID());
					$tag_entries = array();
					foreach($tags as $tag){
						array_push($tag_entries, array('id' => $tag->term_id, 'name' => $tag->name));
					}
					$all_query_tags = array_merge($all_query_tags, $tag_entries);

					if ( !empty($all_query_tags) ) { ?>
						<span class="tags">Tags:
								<?php
								$tagsNames = array();
								foreach($tag_entries as $tag_entry){
									$tagsNames[] = '<span class="archive-listing-tag">' . $tag_entry['name'] . '</span>';
								}
								$allTags = implode(', ',$tagsNames );
								echo $allTags;
								?>
						</span>
					<?php } ?>
		</div><!-- .post-meta -->
		<?php endif; ?>
	</header><!-- .entry-header -->

	<div class="entry-summary">

			<?php
				if('post' === get_post_type() ):

					echo '<p>' . get_the_excerpt() . '</p>';

				elseif('document' === get_post_type()):
					if ( get_field('description') ) {
						echo '<p>' . get_field('description') . '</p>';
					}
				endif;
			?>
			<?php
				if('document' !== get_post_type()):
			?>
			<span class="read-more">
				<?php echo '<a href="' . get_permalink() . '?current_site=' . $current_site . '">Read More</a>'; ?>
			</span>
			<?php
				else:
					?>
						<h3>File Downloads</h3>
					<?php
					if(have_rows('files')): while(have_rows('files')): the_row();
					$currentFile = get_sub_field('file');
					?>
						<a class="search-results-file-download" href="<?php echo $currentFile['url'];?>" target="_blank" download>
							<?php
								switch($currentFile['mime_type']) {
									case 'application/pdf':
										echo '<i class="fa fa-file-pdf-o"></i> PDF';
										break;
									case 'application/msword':
										echo '<i class="fa fa-file-word-o"></i> WORD';
										break;
									case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
										echo '<i class="fa fa-file-word-o"></i> WORD';
										break;
									case 'application/vnd.ms-excel':
										echo '<i class="fa fa-file-excel-o"></i> EXCEL';
										break;
									case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
										echo '<i class="fa fa-file-excel-o"></i> EXCEL';
										break;
									default:
										echo '<i class="fa fa-file-text-o"></i> FILE';
										break;
								}
							?>
						</a>
					<?php

					endwhile; endif;

				endif;
			?>
		</p>
	</div><!-- .entry-summary -->

</article><!-- #post-## -->
