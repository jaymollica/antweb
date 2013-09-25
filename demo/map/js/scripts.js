;
(function(win, $) {

    var Coord = Namespace('AntWeb.Controller.Api.Coord');
    var Specimen = Namespace('AntWeb.Model.Specimen');
    var Map = Namespace('AntWeb.View.Map');

    var $_win = $(win);

    var _addEvents = function() {

      $_win.on(Map.events.UPDATE, function(evt) {

        console.log('Main : Map Update');

        _updateSpecimens();

      });

    };

    var _updateSpecimens = function() {

      console.log('Main : Update Specimens');

      var latlng = Map.getCenter();
      var radius = Math.min(Math.max(Map.getRadius(), 1), 50);

      Coord.getSpecimens(latlng, radius, function(response) {

        _mapSpecimens(response);

      });
    };

    var _mapSpecimens = function(specimens) {

      console.log('Main : Map Specimens');

      var markers = [];
      var marker = undefined;
      var model = undefined;

      $.each(specimens, function(key, val) {

        // console.log(val);

        model = Specimen.createModel(val.meta);

        if (Specimen.hasModel(model.key) === false) {

          Specimen.saveModel(model.key, model);

          if (model.hasLatLng) {

            marker = Map.getMarker(model);
            marker.bindPopup(model.popupContent);

            markers.push(marker);

          }
        }

      });

      Map.addMarkers(markers);

    };

    var _getSquareInsideCircle = function(diameter) {
      return diameter / Math.sqrt(2);
    };

    return {
      init: function() {

        console.log('Main : Init');

        _addEvents();

        $(function() {

          console.log('Main : Ready');

          Map.init(function() {
            console.log('Main : Map Done');
          });

        });

      }
    };

  }(window, jQuery)
  .init());

// End