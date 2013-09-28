;
(function(win, $) {

  var Debug = Namespace('AntWeb.Debug');

  var $_win = $(win);
  var $_map = $('#map');
  var _map = undefined;
  var _events = {
    UPDATE: 'map:update'
  };
  var _cloudmadeLayer = undefined;
  var _debugGroup = undefined;
  var _markersGroup = undefined;

  var _addEvents = function() {
    $_win.on('resize', function(evt) {
      _setMapContainerHeight();
    });
  };

  var _setMapContainerHeight = function() {
    $_map.height($_win.height());
  };

  var _showMap = function() {
    $_map.animate({
      opacity: 1
    });
  };

  var _createMap = function() {

    console.log('Map : Create');

    var apiKey = 'BC9A493B41014CAABB98F0471D759707';
    var mapId = '77922';

    _cloudmadeLayer = L.tileLayer(
      'http://{s}.tile.cloudmade.com/' + apiKey + '/' + mapId + '/256/{z}/{x}/{y}.png', {
        attribution: 'antweb'
      });

    _debugGroup = L.layerGroup();

    _markersGroup = new L.MarkerClusterGroup({
      singleMarkerMode: true
    });

    _map = L.map('map', {
      center: L.latLng(0, 0),
      zoom: 6,
      minZoom: 6,
      maxZoom: 18,
      layers: [_cloudmadeLayer, _debugGroup, _markersGroup]
    })
      .locate({
        setView: true,
        maxZoom: 15
      })
      .on('locationfound', function(evt) {

        console.log('Map : Event : Location Found', evt);

        _showMap();

      })
      .on('locationerror', function(evt) {

        console.log('Map : Event : Location Error', evt);

      })
      .on('moveend', function(evt) {

        console.log('Map : Event : Move End', evt);

        // _updateSpecimens();

        $_win.trigger(_events.UPDATE);

      });
  };

  // Public

  var _init = function(success, failure) {

    console.log('Map : Init');

    _addEvents();
    _setMapContainerHeight();
    _createMap();
  };

  var _getCenter = function() {
    return _map.getCenter();
  };

  var _getRadius = function() {

    var mapBoundNorthEast = _map.getBounds()
      .getNorthEast();
    var radius = mapBoundNorthEast.distanceTo(_map.getCenter());

    return radius;
  };

  var _getMarker = function(model) {
    return L.marker([model.lat, model.lng]);
  };

  var _addMarkers = function(array) {

    console.log('Map : Adding (' + array.length + ') Markers');

    _markersGroup.addLayers(array);
  };

  var _addDebugger = function(layer) {

    if (Debug.map === true) {

      _debugGroup.addLayer(layer);

    }
  };

  Namespace('AntWeb.View.Map', {
    init: _init,
    events: _events,
    getCenter: _getCenter,
    getRadius: _getRadius,
    getMarker: _getMarker,
    addMarkers: _addMarkers,
    addDebugger: _addDebugger
  });

  return {};

}(window, jQuery));