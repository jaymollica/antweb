;
(function(win, $) {

    var Map = Namespace('AntWeb.View.Map');
    var Coord = Namespace('AntWeb.Controller.Api.Coord');

    var _limit = 10;
    var _queue = undefined;
    var _level = undefined;
    var _center = undefined;
    var _radius = undefined;
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

      } else {

        console.log('Api : Coord : Travel : *** Next Level Queue ***');

        if (_level <= _limit) {
          _start();
        }
      }
    };

    var _getLatLng = function(radius, offsetX, offsetY) {

      var offsetMargin = radius / 61;

      var p = new L.Point(_center.lat, _center.lng)
        .add(new L.Point(offsetX * offsetMargin, offsetY * offsetMargin));
      return new L.LatLng(p.x, p.y);
    };

    var _getQueue = function() {

      var queue = [];
      var radius = Coord.normalizeRadius(_radius) * _level;
      var xOffset = 0.75; // Magic Number
      var yOffset = 1.0; // Magic Number

      var addCenter = function() {
        if (_level === 1) {
          queue.push(_getLatLng(radius, 0, 0));
        }
      };

      var addCorners = function() {
        queue.push(_getLatLng(radius, xOffset, yOffset));
        queue.push(_getLatLng(radius, -xOffset, -yOffset));
        queue.push(_getLatLng(radius, xOffset, -yOffset));
        queue.push(_getLatLng(radius, -xOffset, yOffset));
      };

      var addSides = function() {

        queue.push(_getLatLng(radius, xOffset, 0));
        queue.push(_getLatLng(radius, -xOffset, 0));
        queue.push(_getLatLng(radius, 0, yOffset));
        queue.push(_getLatLng(radius, 0, -yOffset));

        if (_level > 1) {

          var xOffsetNew = 0;
          var yOffsetNew = 0;
          var i = 1;
          var l = _level;
          var t = 0.5; // Magic Number
          var t2 = undefined; // Very Magic Number

          for (i; i < l; i++) {

            t2 = t / (i * 1.05);

            yOffsetNew = yOffset * (i * t2);

            queue.push(_getLatLng(radius, xOffset, yOffsetNew));
            queue.push(_getLatLng(radius, xOffset, -yOffsetNew));
            queue.push(_getLatLng(radius, -xOffset, yOffsetNew));
            queue.push(_getLatLng(radius, -xOffset, -yOffsetNew));

            xOffsetNew = xOffset * (i * t2);

            queue.push(_getLatLng(radius, xOffsetNew, yOffset));
            queue.push(_getLatLng(radius, -xOffsetNew, yOffset));
            queue.push(_getLatLng(radius, xOffsetNew, -yOffset));
            queue.push(_getLatLng(radius, -xOffsetNew, -yOffset));
          }
        }
      };

      addCenter();
      addSides();
      addCorners();

      return queue;
    };

    var _start = function() {

      console.log('Api : Coord : Travel : Start');

      _level++;

      _queue = _getQueue();

      _request();
    };

    var _getSpecimens = function(center, radius, success) {

      console.log('Api : Coord : Travel : Get Specimens');

      _level = 0;
      _center = center;
      _radius = radius;
      _success = success;

      _start();
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