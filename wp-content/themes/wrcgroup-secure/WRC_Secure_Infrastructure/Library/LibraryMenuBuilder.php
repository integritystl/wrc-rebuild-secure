<?php
namespace WRCInfrastructure\Library;

class LibraryMenuBuilder{

  public static function buildMenu($permissions, $current_site){
    /**
      * Outputs the Learning Library category menu on the left
      * this needs to grab the user's site permissions and all categories included in that site's Learning Library Category ACF.
      * The top level categories are only containers and will not actually be used to categorize posts,
      * so we will be looking for second level categories if they exist.
      **/

      $user_sites = $permissions['sites'];
      usort($user_sites, 'siteMenuSort');
      //var_dump($user_sites);
      //for each site the user has permission to view
      foreach($user_sites as $site){
        if ($site->ID == 1812) {
          continue;
        }
        //var_dump($site);
        $library_page = get_field('library_page', $site->ID);
        $library_page_link = (!empty($library_page) ? get_the_permalink($library_page->ID) : '');

        //if the site is the current site
        if($site->ID === $current_site->ID){
          echo('<li class="current-site">');

          echo('<a href="' . $library_page_link . '">' . $site->post_title . '</a>');

          echo('<ul class="children">');

          //grab all library categories associated with the Library page
          $library_categories = get_field('learning_categories', $library_page);
          //create an all categories list item
          $all_categories_class = 'current-category';
          $all_categories = (empty($library_categories) ? '' : implode(",", $library_categories));

          //give us all categories selection child
          echo('<li class="all-categories category-item ' . $all_categories_class . '"><a href="' . $library_page_link . '" data-category="' . $all_categories . '">All Programs</a></li>');

          if(!empty($library_categories)){
            foreach($library_categories as $category){
              $cat = get_term($category, 'learning_category');

              if($cat->parent !== 0){
                echo('<li class="category-item">');
                echo('<a href="' . $library_page_link . '?cat=' . $category . '" data-category="'. $category . '">' . $cat->name . '</a>');
                echo('</li>');
              }
            }
          }

        echo('</ul>');
          }else{
            //don't output any markup if there's not a library page assigned, such as Staff Only 'Company'
            if (!empty($library_page)) {
              echo('<li>');
              echo('<a href="' . $library_page_link . '">' . $site->post_title . '</a>');
            }
          }
        }
        echo('</li>');
    }

  }

?>
