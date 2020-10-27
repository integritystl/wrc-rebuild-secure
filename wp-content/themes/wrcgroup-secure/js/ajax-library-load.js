(function($){
  $(document).ready(function(){
    var pageNumber = 1;
    var categorySelector = ".news_category_navigation_menu .current-category a";
    var currentCategory = ($(categorySelector).length > 0 ? $(categorySelector).data('category') : "");
    var listContainer = $('#library-list');
    var currentSite = listContainer.data('site');
    var currentNewsCats = listContainer.data('newscats');
    var currentTag = null;
    var currentYear = null;
    var currentSearch = "";
    var defaultPPP = 8 ;
    var services = Object.create(CommonServicesProtoype);
    var post_count = "";



    function load_posts(search, cat, tag, page, ppp, clearListing, reloadTags){
      if(page === null || page === undefined){page = 1;}
      if(ppp === null || ppp === undefined){ppp = defaultPPP;}
      if(clearListing === null || clearListing === undefined){clearListing = false;}
      if(reloadTags === null || reloadTags === undefined){reloadTags = false;}

      pageNumber = page;

      //checkTags();

      var data = {
        search: search,
        learning_category: cat,
        learning_tag: tag,
        page: page,
        ppp: ppp,
        site: currentSite,
      }

      $.ajax({
          type: "POST",
          dataType: "html",
          url: rest_object.api_url + 'load_more_library/',
          data: data,
          cache: false,
          beforeSend: function(xhr){
            xhr.setRequestHeader( 'X-WP-Nonce', rest_object.api_nonce );
            $('.loading-screen').addClass('visible');
          },
          success : function( response ) {
            var parsedResponse = JSON.parse(response);
            var posts_html = parsedResponse["posts"]["html"];
            var totalPages = parsedResponse["posts"]["totalPages"];

            if(clearListing){
              listContainer.html("");
            }
            pageNumber++;
            var tags = parsedResponse["posts"]["query_tags"];
            if(reloadTags){
              //grab only unique tags
              buildTagDropdown(tags);
            }
          //  console.log(currentNewsCats);
            $('#library-list').append(posts_html);
            if(!posts_html || posts_html === '') {
              if ($('.news-list-container .news-no-results').length == 0) {
                $('.news-list-container').append('<p class="news-no-results">No posts found.</p>');
              }
              if (!$('.load-more-container').hasClass('hidden')) {
                $('.load-more-container').addClass('hidden');
              }
              if ($('#news-list').html() && $('#news-list').html().trim().length === 0) {
                $('.clear-filters-container').removeClass('hidden');
              }
            }else if (totalPages <= pageNumber) {
              //do nothing
            }else{
              if (search || tag ) {
                $('.clear-filters-container').removeClass('hidden');
              } else {
                if(!$('.clear-filters-container').hasClass('hidden')){
                  $('.clear-filters-container').addClass('hidden');
                }
              }
              $('.load-more-container').removeClass('hidden');
            }

            //bind to onclick event after listing has loaded
            $('#news-list li .archive-listing-category').on("click", function(e){
              e.preventDefault();
              updateNewsCategoryFromListing(this);
            });

            //bind to onclick event after listing has loaded
            $('a.archive-listing-tag').on("click", function(e){
              e.preventDefault();
              updateTagFromNewsListing(this);
            });
          },
          fail : function( response ) {          },
          complete : function(){
            $('.loading-screen').removeClass('visible');
          }
      });
    }

    // function checkTags() {
    //   if(tags){
    //     for(var i=0; i<tags.length; i++){
    //       var t = tags[i].name;
    //       if(t === search){
    //         tag = t;
    //       }
    //     }
    //   }
    //   return tag;
    // }

    grabQueryParams();
    load_posts(currentSearch, currentCategory, currentTag, pageNumber, defaultPPP, false, true);

    console.log(pageNumber);
    console.log(post_count);

    $("#load-more-link").on("click",function(e){
        e.preventDefault();
      console.log('help me!');
        load_posts(currentSearch, currentCategory, currentTag, pageNumber, defaultPPP);
      console.log(pageNumber);
    });

    $('.news_category_navigation_menu .category-item a').on("click", function(e){
      e.preventDefault();
      clearVars();

      $('.news_category_navigation_menu .current-category').removeClass('current-category');
      $(this).closest('.category-item').addClass('current-category');

      currentCategory = $(this).data('category');
      load_posts(currentSearch, currentCategory, currentTag, pageNumber, defaultPPP, true, true);
    });

    $('#tag-filter').on("change", function(e){
      pageNumber = 1;
      currentTag = $(this).val();
      load_posts(currentSearch, currentCategory, currentTag, pageNumber, defaultPPP, true);
    });

    $('#search-filter').change(function(){
        clearVars();
        if(!$('#search-filter-clear').hasClass('btn-visible')) {
          $('#search-filter-clear').addClass('btn-visible');
        }
        currentTag = $('#tag-filter').val();
        currentSearch = $(this).val();
        load_posts(currentSearch, currentCategory, currentTag, pageNumber, defaultPPP, true);
    });

    $('#search-filter-clear').click(function(){
      $('#search-filter').val('');
      clearVars();
      load_posts(currentSearch, currentCategory, currentTag, pageNumber, defaultPPP, true, true);
    });

    $('.clear-filters').click(function(e){
      e.preventDefault();
      $('#search-filter').val('');
      clearVars();
      var allCategoriesData = $('.news_category_navigation_menu li.all-categories a').data('category');
      currentCategory = allCategoriesData;
      switchCurrentSideMenuCategoryItem(allCategoriesData);
      load_posts(currentSearch, currentCategory, currentTag, pageNumber, defaultPPP, true, true);
    });

    function clearVars(){
        currentTag = "";
        currentSearch = "";
        pageNumber = 1;
        //Remove the "No Results" message anytime things are cleared
        $('.news-no-results').remove();
        //Remove Search Bar 'clear' x button
        if($('#search-filter-clear').hasClass('btn-visible')) {
          $('#search-filter-clear').removeClass('btn-visible');
        }
    }

    function buildTagDropdown(tags){
      var output = '<option value="">All</option>';
      for(var i=0; i<tags.length; i++){
        output += '<option ' + (Number(currentTag) === Number(tags[i].id) ? 'selected' : '') + ' value="' + tags[i].id + '">' + tags[i].name + '</option>';
      }

      $('#tag-filter').html(output);
    }

    function grabQueryParams(){
      var tagParam = services.getUrlParameter('tag');
      if(tagParam){
        currentTag = tagParam;
      }

      var catParam = services.getUrlParameter('cat');
      if(catParam){
        currentCategory = catParam;
        switchCurrentSideMenuCategoryItem(currentCategory);
      }
    }

    function updateTagFromNewsListing(object){
      var tag = $(object).data('tag');
      var option = $('#tag-filter option[value="' + tag + '"');
      if(option.length > 0){
        option.prop('selected', true);
        $('#tag-filter').change();
      }
    }

    function updateNewsCategoryFromListing(object){
      clearVars();
      var category = $(object).data('category');

      switchCurrentSideMenuCategoryItem(category);

      currentCategory = category;
      load_posts(currentSearch, currentCategory, currentTag, pageNumber, defaultPPP, true, true);
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
