;
(function(win, $) {

    var Api = Namespace('AntWeb.Controller.Api');

    var _getSpecimens = function(latlng, radius, success) {

      console.log('Api : Coord : Get Specimens');

      var coord = latlng.lat + ',' + latlng.lng;

      Api.getData('/api/?coord=' + coord + '&r=' + radius, function(response) {

        if (response) {
          console.log('Api : Coord : Found (' + response.length + ') Specimens');
          console.log(response);
          success(response);
        } else {
          console.log('Api : Coord : Found (0) Specimens');
        }

      });

    };

    Namespace('AntWeb.Controller.Api.Coord', {
      getSpecimens: _getSpecimens
    });

    return {
      init: function() {
        console.log('Api : Coord : Init');
      }
    };

  }(window, jQuery)
  .init());

// End