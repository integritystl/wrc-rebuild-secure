(function($){
  $(document).ready(function(){
    //Emergency Announcement cookie functionality
    function createCookie(name,value,minutes) {
        if (minutes) {
            var date = new Date();
            date.setTime(date.getTime()+(minutes*60*1000));
            var expires = "; expires="+date.toGMTString();
        }
        else var expires = "";
        document.cookie = name+"="+value+expires+"; path=/";
    }

    function readCookie(name) {
          var nameEQ = name + "=";
          var ca = document.cookie.split(';');
          for(var i=0;i < ca.length;i++) {
              var c = ca[i];
              while (c.charAt(0)==' ') c = c.substring(1,c.length);
              if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
          }
          return null;
      }

      var bannerCookie = readCookie('bannerCookie');
      if(!bannerCookie){
        $('.announcements-banner').removeClass('closed');
      }

      $('a.announcement-close').on('click', function(e){
        e.preventDefault();
        $('.announcements-banner').addClass('closed');
        var expiration = 60;
        if ($('.announcements-banner').data('expiration')) {
          expiration = $('.announcements-banner').data('expiration');
        }

        createCookie('bannerCookie', true, expiration);
      });
    //emergency annoucement cookie

    //Thi is from the file common-services.js which is in the same dir as this
    var services = Object.create(CommonServicesProtoype);

    $('.dropdown-toggle').click(function(e){
      e.preventDefault();
      var dropdownTarget = $(this).siblings('.dropdown-target')
      var isSiblingOpen = dropdownTarget.hasClass('open');
      $('.dropdown-target').removeClass('open');
      if(!isSiblingOpen){
          dropdownTarget.toggleClass('open');
      }
    });

    $('.dropdown-toggle, .dropdown-target').click(function(e){
      e.stopPropagation();
    });

    $('html').click(function(){
      $('.dropdown-target.open').removeClass('open');
    });

    function markAsRead(itemID, countObject,countCookie) {
      //format of newItemCookie is "site_id|post_id|viewed,site_id|post_id|viewed,..."
      var newItemCookie = services.getCookie(countCookie);
      var siteID = $('#site-holder').data('site');
      //var postID = $(this).data('id');
      var newCount = 0;
      if(newItemCookie !== ""){
        var tempArray = [];
        var cookieArray = newItemCookie.split(',');
        //loop through all cookie values
        for(var i=0; i<cookieArray.length; i++){
          var cookieValues = cookieArray[i].split('|');
          //if the cookie value is associate with the current post
          if(cookieValues[0] == siteID && cookieValues[1] == itemID.toString()){
            //mark as viewed
            cookieValues[2] = "1";
          }else if(cookieValues[0] == siteID && cookieValues[2] === "0"){
            //otherwise mark as a tally for the new post count
            newCount++;
          }
          tempArray.push(cookieValues.join('|'));
        }
        console.log(tempArray.join(','));
        //set a cookie with the new values;
        services.setCookie(countCookie, tempArray.join(','), 1000);
      }

      //update the new count notification, hide if there are no new posts
      countObject.html(newCount);
      if(newCount <= 0){
        countObject.removeClass('visible');
      }
      // console.log(services.getCookie(countCookie));
    }

    //count new items and make count visible if they exist
    //format of newItemCookie is "site_id|post_id|viewed,site_id|post_id|viewed,..."
    function newItemCount(countObject,countCookie){
      var newCookie = services.getCookie(countCookie);
      var siteID = $('#site-holder').data('site');

      if(newCookie !== ""){
        var newCount = 0;
        var cookieArray = newCookie.split(',');
        for(var i=0; i<cookieArray.length; i++){
          var cookieValues = cookieArray[i].split('|');
          if(cookieValues[0] == siteID && cookieValues[2] === "0"){
            newCount++;
          }
        }
        countObject.html(newCount);
        if(newCount > 0){
          countObject.addClass('visible');
        }
      }
    };
    //For new News Posts
     var newsCountObject = $('.news-link .new-count');
     var newsPostCookie = 'new_post_ids';
    newItemCount(newsCountObject, newsPostCookie);
    $('.news-item .mark-as-read').click(function(e) {
      e.preventDefault();
      var postID = $(this).data('id');
      markAsRead(postID, newsCountObject, newsPostCookie);
      $(this).closest('.new-item').removeClass('new-item');
    });
    //make news items stop being "unread" when you follow the link
    $('.news-item-link').click(function(e) {
      e.preventDefault();
      var postID = $(this).data('id');
      markAsRead(postID, newsCountObject, newsPostCookie);
      $(this).closest('.new-item').removeClass('new-item');
      var targetLink = $(this).attr('href');
      window.location.href = targetLink;
    })

    //For new Library Posts
     var libCountObject = $('.library-link .new-count');
     var libPostCookie = 'lib_post_ids';
    newItemCount(libCountObject, libPostCookie);
    $('.library-item .mark-as-read').click(function(e) {
      e.preventDefault();
      var postID = $(this).data('id');
      markAsRead(postID, libCountObject, libPostCookie);
      $(this).closest('.new-item').removeClass('new-item');
    });
    //make Library items stop being "unread" when you follow the link
    $('.library-item-link').click(function(e) {
      e.preventDefault();
      var postID = $(this).data('id');
      markAsRead(postID, libCountObject, libPostCookie);
      $(this).closest('.new-item').removeClass('new-item');
      var targetLink = $(this).attr('href');
      window.location.href = targetLink;
    })

    //For new Events Posts
    var eventsCountObject = $('.events-link .new-count');
    var eventsPostCookie = 'new_event_ids';
    newItemCount(eventsCountObject, eventsPostCookie);
    $('.event-item .mark-as-read').click(function(e) {
      e.preventDefault();
      var eventID = $(this).data('id');
      markAsRead(eventID, eventsCountObject, eventsPostCookie);
      $(this).closest('.new-item').removeClass('new-item');
    });
    //make events items stop being "unread" when you follow the link
    $('.event-item-link').click(function(e) {
      e.preventDefault();
      var eventID = $(this).data('id');
      console.log(eventID);
      markAsRead(eventID, eventsCountObject, eventsPostCookie);
      $(this).closest('.new-item').removeClass('new-item');
      var targetLink = $(this).attr('href');
      window.location.href = targetLink;
    });



    $('#print-article').click(function(e){
      e.preventDefault();
    });

    //initalize side menu for mobile
    var sidrName = 'sidr-main';

    $('#mobile-nav-trigger').sidr({
        name: sidrName,
        side: 'right',
        source: '#mobile-nav-dropdown',
        renaming: false,
        onOpen: function(){
            $(window).on('click.sidr', function (e) {
                $.sidr('close', sidrName);
            });
            $('#mobile-nav-trigger').addClass('menu-open');
        },
        onClose: function(){
            $(window).off('click.sidr');
            $('#mobile-nav-trigger').removeClass('menu-open');
            $(window).on("taphold",function(){
              $('#mobile-nav-trigger').toggleClass('menu-open');
            });
        }

    });


    // Don't you dare close me out!
    $('#' + sidrName).on('click.sidr', function (e) {
        e.stopPropagation();
    });

    $('#' + sidrName + ' .companies-link').on('click', function(e){
      e.preventDefault();
      $('#' + sidrName + ' .companies-dropdown').slideToggle();
    });

    $('#' + sidrName + ' .user-link').on('click', function(e){
      e.preventDefault();
      $('#' + sidrName + ' .user-dropdown').slideToggle();
    });

  });

  //Back to Top button
  if ($('#back-to-top').length) {
    var scrollTrigger = 100, // px
        backToTop = function () {
            var scrollTop = $(window).scrollTop();
            if (scrollTop > scrollTrigger) {
                $('#back-to-top').addClass('show');
            } else {
                $('#back-to-top').removeClass('show');
            }
        };
    backToTop();
    $(window).on('scroll', function () {
        backToTop();
    });
    $('#back-to-top').on('click', function (e) {
        e.preventDefault();
        $('html,body').animate({
            scrollTop: 0
        }, 700);
    });
  }
  var form = document.getElementById("header_search");
  //Bind search open/close
  $('.user-links__search-item .fa-search').click(toggleSearchBox);
  $('.header_search_form .close_header_search').click(toggleSearchBox);

  //Search hookup
  function toggleSearchBox()
  {
    jQuery('.header_search_form').toggleClass('active');
    jQuery('.header_search_form input[type="text"]').focus();
    var toggleExpanded = $('.header_search_form').attr('aria-expanded');
    if (toggleExpanded === 'false') {
        $('.header_search_form').attr('aria-expanded', 'true');
    } else {
        $('.header_search_form').attr('aria-expanded', 'false');
    }
    form.reset();
  }


  //Get help from FastClick so we can close mobile menu when clicking outside of it
  // https://github.com/ftlabs/fastclick
  // $(function() {
  // 	FastClick.attach(document.body);
  // });

})(jQuery);
