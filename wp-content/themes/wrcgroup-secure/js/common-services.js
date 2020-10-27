var CommonServicesProtoype = {
    getCookie: function(cookieName){
      var name = cookieName + "=";
      var decodedCookie = decodeURIComponent(document.cookie);
      var ca = decodedCookie.split(';');
      for(var i = 0; i <ca.length; i++) {
          var c = ca[i];
          while (c.charAt(0) == ' ') {
              c = c.substring(1);
          }
          if (c.indexOf(name) == 0) {
              return c.substring(name.length, c.length);
          }
      }
      return "";
    },

    setCookie: function setCookie(cookieName, cookieValue, expDays) {
        var date = new Date();
        date.setTime(date.getTime() + (expDays * 24 * 60 * 60 * 1000));
        var expires = "expires="+date.toUTCString();
        document.cookie = cookieName + "=" + cookieValue + ";" + expires + ";path=/";
    },

    clearCookie: function(cookieName){
      var past = new Date(0);
      this.setCookie(cookieName, -1, -1);
    },

    getUrlParameter: function(sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;
    
        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');
    
            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    }
};
