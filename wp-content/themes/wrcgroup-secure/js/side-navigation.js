/**
 * File side-navigation.js.
 *
 * Handles toggling the side navigation menu
 */
(function($){
  $(document).ready(function(){
    $('.left_section_navigation_container ul li:has(ul.children) > a').removeAttr("href");
    $('.left_section_navigation_container ul li:has(ul.children ul li:has(ul.children)) > a').removeAttr("href");

    $('.left_section_navigation_container > ul > li:has(ul > li.current_page_item)').addClass("parent-class");
    $('.left_section_navigation_container > ul > li:has(ul > li.current-category)').addClass("parent-class");
     $('li:has(ul > li.current_page_item) > ul > li:has(ul > li.current_page_item)').addClass("child-class");

    $('.parent-class > ul.children').show();
    $('.child-class > ul.children').show();

    $('li.page_item_has_children a').click(function(){
        $(this).next('ul').slideToggle("slow");
    });

    //Open/close class for mobile version of side navigation
    $('.mobile_table_nav_title').click(function(){
        $(this).toggleClass('open-menu');
    });

  });
})(jQuery);
