<?php
namespace WRCInfrastructure\News;

class NewsMenuBuilder{

  public static function buildMenu($permissions, $current_site, $isEmailNews){
    /**
      * Outputs the news category menu on the left
      * this needs to grab the user's site permissions and all categories included in that site's News Category ACF.
      * The top level categories are only containers and will not actually be used to categorize posts,
      * so we will be looking for second level categories if they exist.
      **/

      $user_sites = $permissions['sites'];
      usort($user_sites, 'siteMenuSort');
      //var_dump($user_sites);
      //for each site the user has permission to view
      foreach($user_sites as $site){
        //var_dump($site);
        $news_page = get_field('news_page', $site->ID);
        $news_page_link = (!empty($news_page) ? get_the_permalink($news_page->ID) : '');
        $is_single = is_single();

        //if the site is the current site
        if($site->ID === $current_site->ID){
          echo('<li class="current-site">');

          echo('<a href="' . $news_page_link . '">' . $site->post_title . '</a>');

          echo('<ul class="children">');

          //grab all news categories associated with the news page
          $news_categories = get_field('news_categories', $news_page->ID);

          //create an all categories list item
          $all_categories_class = ($isEmailNews ? '' : 'current-category');
          $all_categories = (empty($news_categories) ? '' : implode(",", $news_categories));

          //if we're on a single news post, we want the
          echo('<li class="all-categories category-item ' . $all_categories_class . '"><a href="' . $news_page_link . '" data-category="' . $all_categories . '">All News</a></li>');

          if(!empty($news_categories)){
            foreach($news_categories as $category){
              $cat = get_category($category);
              //if category is not a container category
              if($cat->category_parent !== 0){
                echo('<li class="category-item">');
                echo('<a href="' . $news_page_link . '?cat=' . $category . '" data-category="'. $category . '">' . $cat->name . '</a>');
                echo('</li>');
              }
            }
          }

          //output email news if included
          $email_news_page = get_field('email_news_page', $site->ID);
          if(!empty($email_news_page)){
            $email_news_page_link = (!empty($email_news_page) ? get_the_permalink($email_news_page->ID) : '');
            $email_news_categories = get_field('email_news_categories', $email_news_page->ID);

            //javascript determining side menu needs a current category to change styles
            //doesn't matter which because other js will change this before loading posts. meh
            $have_set_current = false;

            if(!empty($email_news_categories)){
              foreach($email_news_categories as $category){
                $item_class = "category-item";
                if(!$have_set_current && $isEmailNews){
                  $item_class .= " current-category";
                  $have_set_current = true;
                }

                $cat = get_term($category, 'email_news_category');
                echo('<li class="'. $item_class .'">');
                echo('<a class="email-news-link" href="' . $email_news_page_link . '?cat=' . $category . '&current_site=' . $current_site->ID . '" data-category="'. $category . '">' . $cat->name . '</a>');
                echo('</li>');
              }
            }

          } 

          echo('</ul>');
        }else{
          //don't output any markup if there's not a news page assigned, such as Staff Only 'Company'
          if (!empty($news_page)) {
            echo('<li>');
            echo('<a href="' . $news_page_link . '">' . $site->post_title . '</a>');
          }

        }
        echo('</li>');
      }

  }


}

?>
