;
(function(win, $) {

    var Map = Namespace('AntWeb.View.Map');
    var Coord = Namespace('AntWeb.Controller.Api.Coord');

    var _success = undefined;

    var _queue = [];
    var _radius = 0;
    var _success = undefined;

    var _request = function() {

      console.log('Api : Coord : Travel : Request');

      var center = _queue.shift();

      Coord.getSpecimens(center, _radius, function(response) {

        console.log('Api : Coord : Travel : Request : Success');

        _success(response);

        _check();

      }, function() {

        console.log('Api : Coord : Travel : Request : Failure');

        _check();

      });
    };

    var _check = function() {

      console.log('Api : Coord : Travel : Check');

      if (_queue.length > 0) {
        _request();
      }
    };

    var _getLatLng = function(center, radius, offsetX, offsetY) {

      var offsetMargin = radius / 61;

      console.log('offsetMargin: ', offsetMargin);

      var p = new L.Point(center.lat, center.lng)
        .add(new L.Point(offsetX * offsetMargin, offsetY * offsetMargin));
      return new L.LatLng(p.x, p.y);
    };

    var _getQueue = function(center) {

      var radius = Coord.normalizeRadius(_radius);
      var xOffset = 0.75;
      var yOffset = 1.0;

      return [
        _getLatLng(center, radius, 0, 0),
        _getLatLng(center, radius, 0, yOffset),
        _getLatLng(center, radius, 0, -yOffset),
        _getLatLng(center, radius, xOffset, 0),
        _getLatLng(center, radius, -xOffset, 0),
        _getLatLng(center, radius, xOffset, yOffset),
        _getLatLng(center, radius, -xOffset, -yOffset),
        _getLatLng(center, radius, xOffset, -yOffset),
        _getLatLng(center, radius, -xOffset, yOffset)
      ];
    };

    var _getSpecimens = function(center, radius, success) {

      console.log('Api : Coord : Travel : Get Specimens');

      _radius = radius;
      _success = success;
      _queue = _getQueue(center);

      _request();

    };

    Namespace('AntWeb.Controller.Api.Coord.Travel', {
      getSpecimens: _getSpecimens
    });

    return {
      init: function() {
        console.log('Api : Coord : Travel : Init');
      }
    };

  }(window, jQuery)
  .init());

// End