;
(function(win, $) {

    console.log('Closure');

    var $_win = $(win);
    var $_map = $('#map');
    var _map = undefined;
    var _cloudmadeLayer = undefined;
    var _markersGroup = undefined;

    var _init = function() {

      console.log('Init');

      $(function() {

        console.log('Ready');

        _addEvents();
        _setMapContainerHeight();
        _createMap();
        _loadSubfamily();

      });

    };

    var _addEvents = function() {

      console.log('Add Events');

      $_win.on('resize', function(evt) {
        _setMapContainerHeight();
      });

    };

    var _setMapContainerHeight = function() {

      console.log('Set Map Height');

      $_map
        .height($_win
          .height());
    };

    var _showMap = function() {

      console.log('Show Map');

      $_map.animate({
        opacity: 1
      });

    };

    var _createMap = function() {

      console.log('Create Map');

      var apiKey = 'BC9A493B41014CAABB98F0471D759707';
      var mapId = '77922';

      _cloudmadeLayer = L.tileLayer(
        'http://{s}.tile.cloudmade.com/' + apiKey + '/' + mapId + '/256/{z}/{x}/{y}.png', {
          attribution: 'antweb'
        });

      _markersGroup = new L.MarkerClusterGroup({
        singleMarkerMode: true
      });

      _map = L.map('map', {
        center: L.latLng(0, 0),
        zoom: 2,
        minZoom: 2,
        maxZoom: 18,
        layers: [_cloudmadeLayer, _markersGroup]
      })
        .locate({
          setView: true,
          maxZoom: 15
        })
        .on('locationfound', function(evt) {

          console.log('Location Found', evt);

          _showMap();

        })
        .on('locationerror', function(evt) {

          console.log('Location Error', evt);

        })
        .on('zoomend', function(evt) {

          console.log('Zoom End', evt);

          _updateSpecimens();

        });
    };

    var _updateSpecimens = function() {

      console.log('Update Specimens');

      _loadSpecimensByCoord(function(response) {

        _mapSpecimens(response);

      });
    };

    var _getMapRadiusInKilometers = function() {

      var mapBoundNorthEast = _map.getBounds()
        .getNorthEast();
      var mapDistance = mapBoundNorthEast.distanceTo(_map.getCenter());

      return mapDistance / 1000;
    };

    var _getMapRadiusInMiles = function() {
      return KM_TO_M(_getMapRadiusInKilometers());
    };

    var KM_TO_M = function(km) {
      return km * 0.6214
    };

    var _loadSubfamily = function() {

      console.log('Load Subfamily');

      _getData('/api/?rank=subfamily', function(response) {

        console.log("Subfamily: ", response);

        var names = [];

        $.each(response, function(key, val) {

          names.push(val.subfamily);

        });

        $('.subfamily-typeahead')
          .typeahead({
            source: names
          });

      });

    };

    var _loadSpecimensByCoord = function(success) {

      console.log('Load Specimens By Coord');

      var latlng = _map.getCenter();
      var coord = latlng.lat + ',' + latlng.lng;
      var radius = parseInt(_getMapRadiusInMiles());

      _getData('/api/?coord=' + coord + '&r=' + radius, function(response) {

        console.log('Loaded (' + response.length + ') Specimens');
        console.log(response);

        success(response);

      });

    };

    var _mapSpecimens = function(specimens) {

      console.log('Add Markers');

      var markers = [];

      $.each(specimens, function(key, val) {
        // console.log(val);

        data = val.meta;
        lat = data.decimal_latitude;
        lng = data.decimal_longitude;

        name = "";
        name += "<b>" + data.code + "</b><br>";
        name += "<p>";
        name += "<b>Subfamily:</b> " + data.subfamily + "<br>";
        name += "<b>Genus:</b> " + data.genus + "<br>";
        name += "<b>Species:</b> " + data.species + "<br>";
        name += "</p>";

        if (lat !== null && lng !== null) {

          marker = L.marker([lat, lng], {
            title: name
          });
          marker.bindPopup(name);

          markers.push(marker);

        }

      });

      _markersGroup.clearLayers();
      _markersGroup.addLayers(markers);

    };

    var _getData = function(path, success) {

      console.log('Get Data');

      $.getJSON(path, function(response) {

        success(response);

      });
    };

    return {
      init: _init
    };

  }(window, jQuery)
  .init());

// End