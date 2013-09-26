;
(function(win, $) {

    var Map = Namespace('AntWeb.View.Map');
    var Api = Namespace('AntWeb.Controller.Api');

    var _cache = new Cache(50000);

    var _normalizeRadius = function(radius) {

      var radiusInKm = radius / 1000;

      if (radiusInKm > 50) {
        radiusInKm = 50;
      }

      if (radiusInKm < 1) {
        radiusInKm = 1;
      }

      return parseInt(radiusInKm);
    };

    var _getSpecimens = function(center, radius, success, failure) {

      console.log('Api : Coord : Get Specimens');

      var coord = center.lat + ',' + center.lng;

      radius = _normalizeRadius(radius);

      var path = '/api/?coord=' + coord + '&r=' + radius;

      if (_cache.getItem(path) === true) {

        console.log('Api : Coord : Found Specimens Prevoiusly');

        failure();

      } else {

        Api.getData(path, function(response) {

          // Begin Debug
          Map.addDebugger(L.circle(center, radius * 1000, {
            stroke: false
          }));
          // End Debug

          _cache.setItem(path, true);

          var length = response ? response.length : 0;

          console.log('Api : Coord : Found (' + length + ') Specimens');
          console.log(response);

          if (response !== null && response.length > 0) {

            success(response);

          } else {

            failure();

          }

        });
      }

    };

    Namespace('AntWeb.Controller.Api.Coord', {
      getSpecimens: _getSpecimens,
      normalizeRadius: _normalizeRadius
    });

    return {
      init: function() {
        console.log('Api : Coord : Init');
      }
    };

  }(window, jQuery)
  .init());

// End