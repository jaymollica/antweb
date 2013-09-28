;
(function(win, $) {

  Namespace('AntWeb.Debug', KweryString.getVars(location.href, {
      "parseBool": true,
      "parseNum": true
    })
    .debug);

}(window, jQuery));

// End