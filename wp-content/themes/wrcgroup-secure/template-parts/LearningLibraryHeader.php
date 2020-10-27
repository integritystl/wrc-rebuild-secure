<?php //this content block is used by library posts ?>
	<?php if(has_post_thumbnail()) {?>
		<div class="featured-image-container">
			<?php $imgObject = wp_get_attachment_image( get_post_thumbnail_id(), 'full' );
			echo $imgObject; ?>
			<h1 class="post-title post_item_title"><span><?php the_title(); ?></span></h1>
		</div>
	<?php } else { ?>
		<div class="no-featured-img">
			<h1 class="post-title post_item_title"><span><?php the_title(); ?></span></h1>
		</div>
	<?php } ?>

		<?php
		//make sure we can find the current site's set News Page to redirect clicks from Categories and Tags for filtering
		$current_site = get_current_site();
		$library_page = get_field('library_page', $current_site->ID);

		//get the Categories ACF field from the site's News page so we only get categories for our Current Site
		$siteLearningCategories = get_field('learning_categories', $library_page->ID);

			// $tags = get_the_tags();
			// $tag_entries = array();
			// if(!empty($tags)){
			// 	foreach($tags as $tag){
			// 		array_push($tag_entries, array("id" => $tag->term_id, "name" => $tag->name));
			// 	}
			// }

			$categories = get_the_category();
			$category_entries = array();
			if(!empty($categories)){
				foreach($categories as $category){
					if($category->category_parent !== 0 && in_array( $category->term_id, $siteLearningCategories) ){
						array_push($category_entries, array("id" => $category->term_id, "name" => $category->name));
					}
				}
			}

		?>
		<div class="post-meta">
			<span class="post-date"><?php the_date(); ?></span>
			<span class="categories">
			<?php
				//var_dump($category_entries);
				foreach($category_entries as $category_entry) : ?>
				<a href="<?php echo(empty($library_page) ? '#' : get_permalink($library_page->ID) . '?cat=' . $category_entry['id']); ?>"><?php echo($category_entry['name']); ?></a>
			<?php endforeach;?>
			</span>
			<?php if(!empty($tag_entries)) : ?>
				<span class="tags">Tags:
				<?php
					$tagsNames = array();
					foreach($tag_entries as $tag_entry) {
						if (empty($library_page)) {
							$tagHref = '#';
						} else {
							$tagHref = get_permalink($library_page->ID) . '?tag=' . $tag_entry['id'];
						}
						$tagsNames[] = '<a class="archive-listing-tag" href="' . $tagHref . '" data-tag="' . $tag_entry['id'] . '">' . $tag_entry['name'] . '</a>';
					} ?>
				<?php
					$allTags = implode(', ',$tagsNames );
					echo $allTags;
				?>
				</span>
			<?php endif; ?>
		</div>
