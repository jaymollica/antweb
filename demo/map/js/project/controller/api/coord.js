;
(function(win, $) {

    var Map = Namespace('AntWeb.View.Map');
    var Api = Namespace('AntWeb.Controller.Api');

    var _isUsingMiles = false;

    var _normalizeRadius = function(radius) {

      var radiusInKm = radius / 1000;

      radius = _isUsingMiles ? KM_TO_M(radiusInKm) : radiusInKm

      if (radius > 50) {
        radius = 50;
      }

      if (radius < 1) {
        radius = 1;
      }

      return parseInt(radius);
    };

    var _getSpecimens = function(center, radius, success) {

      console.log('Api : Coord : Get Specimens');

      var coord = center.lat + ',' + center.lng;

      radius = _normalizeRadius(radius);

      /** 
       *
       * Begin Debug
       *
       */
      Map.addDebugger(L.circle(center, radius * 1000));
      /** 
       *
       * End Debug
       *
       */

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

    var KM_TO_M = function(km) {
      return km * 0.6214
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