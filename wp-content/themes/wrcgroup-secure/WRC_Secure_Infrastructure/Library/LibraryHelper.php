<?php
namespace WRCInfrastructure\Library;
/**
*
* This class handles grabbing News posts.
**/
class LibraryHelper
{
	const COOKIE_NAME = "lib_post_ids";

  	public static function loadMore($params){

  	//The first Query is to check for taxonomy terms in "Learning Tags", then add them to the posts in the second query.
  	$taxonomyArgs = array(
        'post_type' => 'learning-library',
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'learning_tag',
                'field' => 'slug',
                'terms' => $params["search"],
            ),
        ),
    );
  	$taxonomyQuery = new \WP_Query($taxonomyArgs);
  	$terms_ID = array();

  	if($taxonomyQuery->have_posts) :
        while($taxonomyQuery->have_posts) : $taxonomyQuery->the_post;
  	    $terms_ID[] = get_the_ID();
  	    endwhile;
  	    wp_reset_postdate();
  	endif;


    //The second Query to get the rest of the posts for this site
  	$libraryArgs = array(
      	'post_type' => 'learning-library',
      	'posts_per_page' => $params["ppp"],
		'posts__not_in' => $terms_ID,
		'paged' => $params["page"],
		'orderby' => 'date',
		'order' => 'DESC'
		);			
	$tax_query = array('relation' => 'AND');
	$library_category = null;
	$libraryPageCatsArray = null;
    if(!empty($params["learning_category"]) || !empty($params["learning_tag"])){

			if(!empty($params["learning_category"])){
				$library_category = htmlspecialchars($params["learning_category"]);
				$libraryPageCatsArray = explode(',', $library_category);
				array_push($tax_query, array(
					'taxonomy' => 'learning_category',
					'field' => 'term_id',
					'terms' => $libraryPageCatsArray
				));
			}

			if(!empty($params["learning_tag"])){
				$library_tag = htmlspecialchars($params["learning_tag"]);
				array_push($tax_query, array(
					'taxonomy' => 'learning_tag',
					'field' => 'term_id',
					'terms' => explode(',', $library_tag)
				));
			}

  		$libraryArgs['tax_query'] = $tax_query;
		}

		if(!empty($params["search"])){
				$libraryArgs['s'] = htmlspecialchars($params["search"]);
		}

		$current_site = '';
		if(!empty($params["site"])){
			$current_site = htmlspecialchars($params["site"]);
		}

		$posts_html = '';
		$all_query_tags = null;
		if(empty($library_category) || strpos($library_category, ',') !== false){
			$all_query_tags = self::getTagsByCategory($library_category, true);
		}else{
			$int_cat = intval($library_category);
			$all_query_tags = self::getTagsByCategory($int_cat);
		}

		$libraryQuery = new \WP_Query($libraryArgs);

		//handle if no relevannsi
	 	if( function_exists('relevanssi_do_query')) {
			if(!empty($params["search"]) && isset($params['search']) ){
					$libraryQuery->query_vars['s'] = sanitize_text_field($params['search']);
					relevanssi_do_query($libraryQuery);
			} else {
				//regular, non-search query will happen
			}
		}

	 	//combine the two queries into one
        $libraryQuery->posts = array_merge($taxonomyQuery->posts, $libraryQuery->posts);
        $libraryQuery->post_count = count($libraryQuery->posts);

        $libraryIDs = array();
		if($libraryQuery->have_posts()){
			while($libraryQuery->have_posts()){
				$libraryQuery->the_post();

				//check the ID of the post and skip this iteration of the loop if it exists
				$currentID = get_the_ID();
				if (in_array($currentID, $libraryIDs)) {
				    continue;
                } else {
                    $libraryIDs[] = $currentID;
                }

				$title = get_the_title();
				$cat_entries = array();

				$post_date = get_the_date('Ymd');
				$current_date = date('Ymd');
				$new_check = $current_date - $post_date;
				$new_item = '';
				if ( $new_check > 7 ) {
					//post isn't new and set flex order so new items appear first on archive page
					$flex_order = 'order:2;';
				} else {
					$new_item = ' show-new';
					$flex_order = 'order:1;';
				}

				if(!empty($libraryPageCatsArray)){
					$categories = get_the_terms( get_the_id(), 'learning_category' );
					foreach ($categories as $category) {
						if(in_array($category->term_id, $libraryPageCatsArray) ){
							array_push($cat_entries, array('id' => $category->term_id, 'name' => $category->name));
						}
					}
				}

				$imgObject = wp_get_attachment_image( get_post_thumbnail_id(), 'full' );
				
				$launchURL = get_field('launch_url');

				$posts_html .= '<li class="'. $attachedSite->post_name .'" style="'. $flex_order .'">';
				// gets the post image
					if($imgObject) {
						$posts_html .= '<div class="featured-image-container">';
						$posts_html .= '<a href="' . get_permalink() . '?current_site=' . $current_site . '" target="_blank">' . $imgObject . '</a>';
						$posts_html .= '<h2 class="post_item_title"><a href="' . get_permalink() . '?current_site=' . $current_site . '" target="_blank">' . $title . '</a></h2>';
						$posts_html .= '</div>';
					} else {
						$posts_html .= '<div class="no-featured-img">';
						$posts_html .= '<h2 class="post_item_title"><a href="' . get_permalink() . '?current_site=' . $current_site . '"  target="_blank">' . $title . '</a></h2>';
						$posts_html .= '</div>';
					}
				//gets the post meta with custom Cats and Tags for the CPT
				/* The client asked for the date, catagories, and tags to be removed. Instead of just deleting it I'm hiding it because it probably will want to be used in the futre. 
					$posts_html .= '<div class="post-meta">';
					$posts_html .= '<span>' . get_the_date('F j, Y') . '</span>';
					$posts_html .= '<span class="categories">';
					foreach($cat_entries as $cat_entry){
						$posts_html .= '<a class="archive-listing-category" href="#" data-category="' . $cat_entry['id'] . '">' . $cat_entry['name'] . '</a>';
					}
					$posts_html .= '</span>';
					$tag_entries = get_the_terms( get_the_id(), 'learning_tag' );
					if(!empty($tag_entries)) {
						$posts_html .= '<span class="tags">Tags:';

						$tagsNames = array();
						foreach($tag_entries as $tag_entry){
							$tagsNames[] = '<a class="archive-listing-tag" href="#" data-tag="' . $tag_entry->term_id . '">' . $tag_entry->name . '</a>';
						}
						$allTags = implode(',',$tagsNames );
						$posts_html .= ' '. $allTags . '</span>';
					}
					$posts_html .= '</div>';
					*/
					$posts_html .= '<p>' . excerpt(60) . '</p>';
					
					//Client has asked for this to be there and not a lot so just commenting out.
					// if ($launchURL) {
					// 	$posts_html .= '<span class="news-links"><a href="' . $launchURL . '" target="_blank">Launch Program</a></span>';
					// }

					$posts_html .= '<span class="news-links"><a href="' . get_permalink() . '?current_site=' . $current_site . '" target="_blank">Program Details</a></span>';
					
					
					$posts_html .= '</p></li>';
			}
		}

		$totalPages = $libraryQuery->max_num_pages;

		$response = new \stdClass();
		$response->html = $posts_html;
		$response->totalPages = $totalPages;
		$response->query_tags = $all_query_tags;
		return $response;

  }
  	public static function setNewLibraryCookie($new_lib_IDs, $site_id){
	    //original: cookie format "post_id|view_flag,post_id|view_flag,..."
			//changed to: "site_id|post_id|view_flag" so we can determine which site the post is for
			//ex: "1|123|0,2|124|1"
			$new_lib_cookie = (!empty($_COOKIE[self::COOKIE_NAME]) ? $_COOKIE[self::COOKIE_NAME] : '');
			//if a new_posts cookie has not been set
			if(!empty($new_lib_cookie)){
				//if there have been new posts created since the last time the user logged in
				if(count($new_lib_IDs) > 0){
					//check to see if there are new posts not already included in the cookie
					//add them to tempt array if there are
					$new_lib_cookie_array = explode(",", $new_lib_cookie);
					$new_values = array();
					foreach($new_lib_IDs as $id){
						//check to see if cookie with this site/post combo exists
						if(self::isNewCookieValue("$site_id|$id", $new_lib_cookie_array)){
							//view flag starts as a 0 for not viewed
							$cookie_value = "$site_id|$id|0";
							//var_dump($cookie_value);
							array_push($new_values, $cookie_value);
						}
					}
					//if there are new values, merge them into the cookie array and set the cookie
					if(count($new_values) > 0){
						$new_lib_cookie_array = array_merge($new_lib_cookie_array, $new_values);
						//set cookie and make sure it's immediately accessible
						$cookie_val = implode(",", $new_lib_cookie_array);
						setcookie(self::COOKIE_NAME, $cookie_val, 0, '/');
						$_COOKIE[self::COOKIE_NAME] = $cookie_val;
					}
				}
			}else{
				//var_dump($new_lib_IDs); die;
				//if the cookie has not been set
				if(count($new_lib_IDs) > 0){
					$new_lib_cookie_array = array();
					//create cookie values from post IDs and set in cookie
					foreach($new_lib_IDs as $id){
							$cookie_value = "$site_id|$id|0";
							array_push($new_lib_cookie_array, $cookie_value);
					}
					$cookie_val = implode(",", $new_lib_cookie_array);
					setcookie(self::COOKIE_NAME, $cookie_val, 0, '/');
					$_COOKIE[self::COOKIE_NAME] = $cookie_val;
				}
			}
		}
		
		private static function isNewCookieValue($value, $cookie_array){
			foreach($cookie_array as $cookie){
				if(strpos($cookie, $value) !== false){
					return false;
				}
			}

			return true;
		}

	  public static function isLibNew($post_id){
	    //check to see if we should mark the post as new
	    $new_post = false;
	    if(!empty($_COOKIE[self::COOKIE_NAME])){
	      $cookie_array = explode(",", $_COOKIE[self::COOKIE_NAME]);
	      if(count($cookie_array) > 0){
	        foreach($cookie_array as $cookie_value){
	          $value_array = explode("|", $cookie_value);
	          if(intval($value_array[1]) === $post_id){
	            $new_post = empty($value_array[2]);
	          }
	        }
	      }
	    }

	    return $new_post;
	  }

	  public static function deleteLibPostsCookie(){
	    //destroy that cookie!
	    unset($_COOKIE[self::COOKIE_NAME]);
	    setcookie(self::COOKIE_NAME, '', time() - 3600, '/');
	  }




  private static function getTagsByCategory($category, $multiple = false){
		global $wpdb;
		$sql = "SELECT DISTINCT terms2.term_id as id, terms2.name as name
				FROM
					wp_posts as p1
					LEFT JOIN wp_term_relationships as r1 ON p1.ID = r1.object_ID
					LEFT JOIN wp_term_taxonomy as t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id
					LEFT JOIN wp_terms as terms1 ON t1.term_id = terms1.term_id,

					wp_posts as p2
					LEFT JOIN wp_term_relationships as r2 ON p2.ID = r2.object_ID
					LEFT JOIN wp_term_taxonomy as t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id
					LEFT JOIN wp_terms as terms2 ON t2.term_id = terms2.term_id
				WHERE
					t1.taxonomy = 'learning_category' AND p1.post_status = 'publish' AND
					t2.taxonomy = 'learning_tag' AND p2.post_status = 'publish'
					AND p1.ID = p2.ID ";

		if(!empty($category)){
			if($multiple){
				$categories = explode(',', $category);
				$params = "";
				for($i=0; $i<count($categories); $i++){
					$temp = $i === count($categories) - 1 ? "%d" : "%d,";
					$params .= $temp;
				}
				$sql .= "AND terms1.term_id in (" . $params . ") ORDER by name";
			}else{
				$sql .= "AND terms1.term_id = %d ORDER by name";
			}
		}else{
			$sql .= "ORDER by name";
		}

		$statement = null;
		if(!empty($category)){
			if($multiple){
				$statement = $wpdb->prepare($sql, explode(',', $category));
			}else{
				$statement = $wpdb->prepare($sql, array($category));
			}
		}else{
			$statement = $sql;
		}
		
		$tags = $wpdb->get_results($statement);

		return $tags;
	}
}