;
(function(win, $) {

    var _cache = new Cache(50);
    var _openRequest = undefined;

    var _abortOpenRequest = function() {
      if (_openRequest !== undefined && _openRequest.readyState !== 4) {

        console.log('Api : Active Data Request Cancelled');

        _openRequest.abort();
        _openRequest = undefined;
      }
    };

    var _getData = function(path, success, error) {

      console.log('Api : Get Data');

      path = 'http://localhost:8888' + path;

      _abortOpenRequest();

      var cachedResponse = _cache.getItem(path);

      if (cachedResponse) {

        console.log('Api : Data Response Cached');

        success(cachedResponse);

      } else {

        console.log('Api : Data Response Not Cached');

        _openRequest =
          $.getJSON(path)
          .done(function(response) {

            console.log('Api : Data Request Done');

            _abortOpenRequest();

            _cache.setItem(path, response);

            success(response);

          })
          .fail(function(response) {
            console.log('Api : Data Request Fail');

            if (error !== undefined) {
              error(response);
            }
          })
          .always(function(response) {
            console.log('Api : Data Request Always');
          });
      }
    };

    _hasResponse = function(key) {

      console.log('Api : Has Response');

      return (_cache.getItem(key) !== null);
    };

    Namespace('AntWeb.Controller.Api', {
      getData: _getData,
      hasResponse: _hasResponse
    });

    return {
      init: function() {
        console.log('Api : Init');
      }
    };

  }(window, jQuery)
  .init());

// End