(function($){
  $(document).ready(function(){
    var pageNumber = 1;
    var listContainer = $('#news-list');
    var currentSite = listContainer.data('site');
    var defaultPPP = 8;
    var categorySelector = ".news_category_navigation_menu .current-category a";
    var currentCategory = ($(categorySelector).length > 0 ? $(categorySelector).data('category') : "");
    var services = Object.create(CommonServicesProtoype);

    function load_posts(category, page, ppp, clearListing){
      if(category === null || category === undefined){category = '';}
      if(page === null || page === undefined){page = 1;}
      if(ppp === null || ppp === undefined){ppp = defaultPPP;}
      if(clearListing === null || clearListing === undefined){clearListing = false;}
      pageNumber = page;

      var data = {
        page: page,
        ppp: ppp,
        site: currentSite,
        category: category
      }

      $.ajax({
          type: "POST",
          dataType: "html",
          url: rest_object.api_url + 'load_more_email_news/',
          data: data,
          cache: false,
          beforeSend: function(xhr){
            xhr.setRequestHeader( 'X-WP-Nonce', rest_object.api_nonce );
            $('.loading-screen').addClass('visible');
          },
          success : function( response ) {
            console.log(response)
            var parsedResponse = $.parseJSON(response);
            var posts_html = parsedResponse["posts"]["html"];
            if(clearListing){
              listContainer.html("");
            }
            pageNumber++;
            $('#news-list').append(posts_html);

            if((!posts_html || posts_html === '')){
              if(!$('.load-more-container').hasClass('hidden')){
                $('.load-more-container').addClass('hidden');
              }
              if($('#news-list').html().trim().length === 0){
                $('.clear-filters-container').removeClass('hidden');
              }
            }else{
              $('.load-more-container').removeClass('hidden');
              if(!$('.clear-filters-container').hasClass('hidden')){
                $('.clear-filters-container').addClass('hidden');
              }
            }

          },
          fail : function( response ) {
            console.log(response);
          },
          complete : function(){
            $('.loading-screen').removeClass('visible');
          }
      });
    }

    grabQueryParams();
    load_posts(currentCategory, pageNumber, defaultPPP, false);

    $('.clear-filters').attr('href', $('.news_category_navigation_menu li.all-categories a').attr('href'));

    $("#load-more-link").on("click",function(e){
        e.preventDefault();
        load_posts(currentCategory, pageNumber, defaultPPP);
    });

    function grabQueryParams(){
      var catParam = services.getUrlParameter('cat');
      if(catParam){
        currentCategory = catParam;
        switchCurrentSideMenuCategoryItem(currentCategory);
      }
    }

    function switchCurrentSideMenuCategoryItem(category){
      $('.news_category_navigation_menu .current-category').removeClass('current-category');
      var categoryMenuItem = $('.news_category_navigation_menu').find('li a[data-category="' + category + '"]');
      if(categoryMenuItem.length > 0){
        categoryMenuItem.parent().addClass('current-category');
      }
    }
  })
})(jQuery);
