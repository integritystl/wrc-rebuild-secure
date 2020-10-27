<?php
namespace WRCInfrastructure\News;
/**
*
* This class handles grabbing News posts.
**/
class EmailNewsHelper
{

  public static function loadMore($params){

  	$newsArgs = array(
      'post_type' => 'email_news',
      'posts_per_page' => htmlspecialchars($params["ppp"]),
  		'paged' => htmlspecialchars($params["page"])
		);
		
		$tax_query = array();
    if(!empty($params["category"])){
      array_push($tax_query, array(
        'taxonomy' => 'email_news_category',
        'field' => 'term_id',
        'terms' => htmlspecialchars($params["category"])
      ));

  		$newsArgs['tax_query'] = $tax_query;
    }

		$current_site = '';
		if(!empty($params["site"])){
			$current_site = htmlspecialchars($params["site"]);
		}

		$posts_html = '';
		$all_query_tags = array();
		$newsQuery = new \WP_Query($newsArgs);

		if($newsQuery->have_posts()){
			while($newsQuery->have_posts()){
					$newsQuery->the_post();
					$title = get_the_title();
					$email_link = get_field('email_news_link', get_the_ID());

          $posts_html .= '<li>';
					$posts_html .= '<div class="link-wrapper">';
					$posts_html .= '<h2><a href="' . $email_link . '" target="_blank">' . $title . '</a></h2>';
          $posts_html .= '<p><span>' . get_the_date('F j, Y') . '</span></p>';
					$posts_html .= '<a class="email-news-link-button" href="' . $email_link . '" target="_blank">Read Email</a>';
					$posts_html .= '</div>';
					$posts_html .= '</li>';
			}
		}

		$response = new \stdClass();
		$response->html = $posts_html;
		return $response;

  }

}
