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

        queue.push(_getLatLng(radius, xOffset, 0)); // Right
        queue.push(_getLatLng(radius, -xOffset, 0)); // Left
        queue.push(_getLatLng(radius, 0, yOffset)); // Top
        queue.push(_getLatLng(radius, 0, -yOffset)); // Bottom

        if (_level > 1) {

          var i = 1;
          var l = _level;
          var xOffsetInterval = xOffset / _level;
          var yOffsetInterval = yOffset / _level;
          var xOffsetNew = undefined;
          var yOffsetNew = undefined;

          for (i; i < l; i++) {

            xOffsetNew = xOffsetInterval * i;
            yOffsetNew = yOffsetInterval * i;

            queue.push(_getLatLng(radius, xOffset, yOffsetNew)); // Right
            queue.push(_getLatLng(radius, xOffset, -yOffsetNew)); // Right
            queue.push(_getLatLng(radius, -xOffset, yOffsetNew)); // Left
            queue.push(_getLatLng(radius, -xOffset, -yOffsetNew)); // Left
            queue.push(_getLatLng(radius, xOffsetNew, yOffset)); // Top
            queue.push(_getLatLng(radius, -xOffsetNew, yOffset)); // Top
            queue.push(_getLatLng(radius, xOffsetNew, -yOffset)); // Bottom
            queue.push(_getLatLng(radius, -xOffsetNew, -yOffset)); // Bottom

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