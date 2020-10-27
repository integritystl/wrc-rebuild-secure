<?php
namespace WRCInfrastructure\News;
/**
*
* This class handles grabbing News posts.
**/
class NewsHelper
{
	const COOKIE_NAME = "new_post_ids";

  public static function loadMore($params){

  	$newsArgs = array(
      'post_type' => 'post',
      'posts_per_page' => $params["ppp"],
  		'paged' => $params["page"]
		);
		

		$tax_query = array();
		$news_category = null;
    if(!empty($params["category"])){
			$news_category = htmlspecialchars($params["category"]);
      array_push($tax_query, array(
        'taxonomy' => 'category',
        'field' => 'term_id',
        'terms' => $news_category
      ));

  		$newsArgs['tax_query'] = $tax_query;
    }

  	if(!empty($params["tag"])){
  		$newsArgs["tag_id"] = htmlspecialchars($params["tag"]);
  	}

		if(!empty($params["search"])){
				$newsArgs['s'] = htmlspecialchars($params["search"]);
		}

		$current_site = '';
		if(!empty($params["site"])){
			$current_site = htmlspecialchars($params["site"]);
		}

		//Use Current Site to find the News page for the site, then only output Categories from that Site
		// this happens in the News Template page and is attached to our params
		$newsPageCats = '';
		if(!empty($params["newscat"])){
			$newsPageCats = htmlspecialchars($params["newscat"]);
		}
		$newsPageCatsArray = explode(',', $newsPageCats);

		$posts_html = '';
		$all_query_tags = null;
		if(empty($news_category) || strpos($news_category, ',') !== false){
			$all_query_tags = self::getTagsByCategory($news_category, true);
		}else{
			$int_cat = intval($news_category);
			$all_query_tags = self::getTagsByCategory($int_cat);
		}

		$newsQuery = new \WP_Query($newsArgs);

		//handle if no relevannsi
	 	if( function_exists('relevanssi_do_query')) {
			if(!empty($params["search"]) && isset($params['search']) ){
					$newsQuery->query_vars['s'] = sanitize_text_field($params['search']);
					relevanssi_do_query($newsQuery);
			} else {
				//regular, non-search query will happen
			}
		}


		if($newsQuery->have_posts()){
			while($newsQuery->have_posts()){
					$newsQuery->the_post();
					$title = get_the_title();

					$categories = get_the_category();
					$cat_entries = array();
					foreach ($categories as $category) {
						if($category->category_parent !== 0 && in_array($category->term_id, $newsPageCatsArray) ){
							array_push($cat_entries, array('id' => $category->term_id, 'name' => $category->name));
						}
					}

					$imgObject = wp_get_attachment_image( get_post_thumbnail_id(), 'full' );


					$posts_html .= '<li>';
					if($imgObject) {
						$posts_html .= '<div class="featured-image-container">';
						$posts_html .= '<a href="' . get_permalink() . '?current_site=' . $current_site . '">' . $imgObject . '</a>';
						$posts_html .= '<h2 class="post_item_title"><a href="' . get_permalink() . '?current_site=' . $current_site . '">' . $title . '</a></h2>';
						$posts_html .= '</div>';
					} else {
						$posts_html .= '<div class="no-featured-img">';
						$posts_html .= '<h2 class="post_item_title"><a href="' . get_permalink() . '?current_site=' . $current_site . '">' . $title . '</a></h2>';
						$posts_html .= '</div>';
					}
					$posts_html .= '<div class="post-meta">';
					$posts_html .= '<span>' . get_the_date('F j, Y') . '</span>';
					$posts_html .= '<span class="categories">';
					foreach($cat_entries as $cat_entry){
						$posts_html .= '<a class="archive-listing-category" href="#" data-category="' . $cat_entry['id'] . '">' . $cat_entry['name'] . '</a>';
					}
					$posts_html .= '</span>';
					$tag_entries = get_the_tags();
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
					$posts_html .= '<p>' . excerpt(60) . '<span class="read-more"><a href="' . get_permalink() . '?current_site=' . $current_site . '">Read More</a></span></p>';
					$posts_html .= '</li>';
			}
		}

		$response = new \stdClass();
		$response->html = $posts_html;
		$response->query_tags = $all_query_tags;
		return $response;

  }

	public static function setNewPostsCookie($new_post_IDs, $site_id){
		//original: cookie format "post_id|view_flag,post_id|view_flag,..."
		//changed to: "site_id|post_id|view_flag" so we can determine which site the post is for
		//ex: "1|123|0,2|124|1"
		$new_posts_cookie = (!empty($_COOKIE[self::COOKIE_NAME]) ? $_COOKIE[self::COOKIE_NAME] : '');
		//if a new_posts cookie has not been set
		if(!empty($new_posts_cookie)){
			//if there have been new posts created since the last time the user logged in
			if(count($new_post_IDs) > 0){
				//check to see if there are new posts not already included in the cookie
				//add them to tempt array if there are
				$new_posts_cookie_array = explode(",", $new_posts_cookie);
				$new_values = array();
				foreach($new_post_IDs as $id){
					//check to see if cookie with this site/post combo exists
					if(self::isNewCookieValue("$site_id|$id", $new_posts_cookie_array)){
						//view flag starts as a 0 for not viewed
						$cookie_value = "$site_id|$id|0";
						//var_dump($cookie_value);
						array_push($new_values, $cookie_value);
					}
				}
				//if there are new values, merge them into the cookie array and set the cookie
				if(count($new_values) > 0){
					$new_posts_cookie_array = array_merge($new_posts_cookie_array, $new_values);
					//set cookie and make sure it's immediately accessible
					$cookie_val = implode(",", $new_posts_cookie_array);
					setcookie(self::COOKIE_NAME, $cookie_val, 0, '/');
					$_COOKIE[self::COOKIE_NAME] = $cookie_val;
				}
			}
		}else{
			//if the cookie has not been set
			if(count($new_post_IDs) > 0){
				$new_posts_cookie_array = array();
				//create cookie values from post IDs and set in cookie
				foreach($new_post_IDs as $id){
						$cookie_value = "$site_id|$id|0";
						array_push($new_posts_cookie_array, $cookie_value);
				}
				$cookie_val = implode(",", $new_posts_cookie_array);
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

	public static function deleteNewPostsCookie(){
		//destroy that cookie!
		unset($_COOKIE[self::COOKIE_NAME]);
		setcookie(self::COOKIE_NAME, '', time() - 3600, '/');
	}

	public static function isPostNew($post_id){
		//check to see if we should mark the post as new
		$new_post = false;
		if(!empty($_COOKIE[self::COOKIE_NAME])){
			$cookie_array = explode(",", $_COOKIE[self::COOKIE_NAME]);
			if(count($cookie_array) > 0){
				foreach($cookie_array as $cookie_value){
					$value_array = explode("|", $cookie_value);
					if(intval($value_array[1]) == $post_id){
						$new_post = empty($value_array[2]);
					}
				}
			}
		}
		return $new_post;
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
							t1.taxonomy = 'category' AND p1.post_status = 'publish' AND
							t2.taxonomy = 'post_tag' AND p2.post_status = 'publish'
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
