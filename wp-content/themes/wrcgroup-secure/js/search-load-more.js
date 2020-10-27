jQuery(document).ready(function($) {
  var listContainer = $('#search-results-container');
  var pageNumber = 2;
  var defaultPPP = rest_object.posts_per_page;
  var search = listContainer.data('search-terms'); //nab the current search terms
  var currentSite = listContainer.data('current-site');

  function load_posts(search, page, ppp){
    if(page === null || page === undefined){page = 1;}
    if(ppp === null || ppp === undefined){ppp = defaultPPP;}

    var data = {
      page: page,
      ppp: ppp,
      search : search,
      //offset: (page * ppp) + 1,
      current_site: currentSite
    }
    
    $.ajax({
        url: rest_object.api_url + 'load_more_results/',
        dataType: "html",
        type: 'post',
        data: data,
        cache: false,
        beforeSend: function(xhr){
          xhr.setRequestHeader( 'X-WP-Nonce', rest_object.api_nonce );
          $('.loading-screen').addClass('visible');
          $('#load-more-btn').addClass('btn-hide');
        },
        success: function( response ){
         
          listContainer.append(response);
          pageNumber++;
        },
        fail : function( response ) {
       
          listContainer.append('<p>Sorry, no posts can be loaded.</p>');
        },
        complete : function(){
          $('.loading-screen').removeClass('visible');
          $("#load-more-btn").removeClass('btn-hide');
          var noMorePostsMsg = $('p').hasClass('load-more-no-posts');
          if (noMorePostsMsg) {
            $("#load-more-btn").addClass('btn-hide');
          }
        }
    });
  };


//  only do initial page load if its first page

  $("#load-more-btn").on("click",function(e){
      e.preventDefault();
      load_posts(search, pageNumber, defaultPPP);
  });

});
