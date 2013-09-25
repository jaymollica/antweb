;
(function(win, $) {

    var Coord = Namespace('AntWeb.Controller.Api.Coord');

    var _getSpecimens = function(center, radius, success) {

      console.log('Api : Coord : Travel : Get Specimens');

      Coord.getSpecimens(center, radius, success);

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