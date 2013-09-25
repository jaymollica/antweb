;
(function(win, $) {

    var _cachedDataRequests = new Cache(50);
    var _openDataRequest = undefined;

    var _abortOpenRequest = function() {
      if (_openDataRequest !== undefined && _openDataRequest.readyState !== 4) {

        console.log('Api : Active Data Request Canceled');

        _openDataRequest.abort();
        _openDataRequest = undefined;
      }
    };

    var _getData = function(path, success, error) {

      console.log('Api : Get Data');

      _abortOpenRequest();

      var cachedResponse = _cachedDataRequests.getItem(path);

      if (cachedResponse) {

        console.log('Api : Data Response Cached');

        success(cachedResponse);

      } else {

        console.log('Api : Data Response Not Cached');

        _openDataRequest =
          $.getJSON(path)
          .done(function(response) {

            console.log('Api : Data Request Done');

            _abortOpenRequest();

            _cachedDataRequests.setItem(path, response);

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

    Namespace('AntWeb.Controller.Api', {
      getData: _getData
    });

    return {
      init: function() {
        console.log('Api : Init');
      }
    };

  }(window, jQuery)
  .init());

// End