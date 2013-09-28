;
(function(win, $) {

  var _cache = new Cache();

  var _createModel = function(data) {

    var key = data.code;

    if (_hasModel(key)) {
      return _getModel(key);
    }

    var model = {};
    model.key = key;
    model.lat = data.decimal_latitude;
    model.lng = data.decimal_longitude;
    model.subfamily = data.subfamily;
    model.genus = data.genus;
    model.species = data.species;
    model.hasLatLng = (model.lat !== null && model.lng !== null);

    var popupContent = "";
    popupContent += "<b>" + model.key + "</b><br>";
    popupContent += "<p>";
    popupContent += "<b>Subfamily:</b> " + data.subfamily + "<br>";
    popupContent += "<b>Genus:</b> " + data.genus + "<br>";
    popupContent += "<b>Species:</b> " + data.species + "<br>";
    popupContent += "</p>";

    model.popupContent = popupContent;

    return model;
  };

  var _hasModel = function(key) {
    return (_getModel(key) !== null);
  };

  var _getModel = function(key) {
    return _cache.getItem(key);
  };

  var _saveModel = function(key, model) {
    _cache.setItem(key, model);
  };

  Namespace('AntWeb.Model.Specimen', {
    createModel: _createModel,
    hasModel: _hasModel,
    getModel: _getModel,
    saveModel: _saveModel
  });

  return {};

}(window, jQuery));