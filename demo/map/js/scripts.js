;
(function(win, $) {

    console.log('Closure');

    var _map = undefined;
    var _markersGroup = undefined;

    var _init = function() {

      console.log('Init');

      $(function() {

        console.log('Ready');

        _createMap();
        _loadSubfamily();

      });

    };

    var _createMap = function() {

      console.log('Create Map');

      var h = $(win)
        .height();

      console.log('h', h);

      $('#map')
        .height(h);

      var apiKey = 'BC9A493B41014CAABB98F0471D759707';
      var mapId = '999';

      var cloudmadeLayer = L.tileLayer(
        'http://{s}.tile.cloudmade.com/' + apiKey + '/' + mapId + '/256/{z}/{x}/{y}.png', {
          attribution: 'antweb'
        });

      _markersGroup = new L.MarkerClusterGroup();

      _map = L.map('map', {
        center: L.latLng(0, 0),
        zoom: 2,
        minZoom: 2,
        maxZoom: 18,
        layers: [cloudmadeLayer, _markersGroup]
      })
        .locate({
          setView: true
        })
        .on('locationfound', function(evt) {

          console.log('Location Found', evt);

          _loadSpecimensByCoord(evt.latlng, function(response) {

            _mapSpecimens(response);

          });

        })
        .on('locationerror', function(evt) {
          console.log('Location Error', evt);
        });
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

    var _loadSpecimensByCoord = function(latlng, success) {

      console.log('Load Specimens By Coord');

      var coord = latlng.lat + ',' + latlng.lng;

      _getData('/api/?coord=' + coord, function(response) {

        console.log("Specimens: ", response);

        success(response);

      });

    };

    var _mapSpecimens = function(specimens) {

      console.log('Add Markers');

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

          _markersGroup.addLayer(marker);

        }

      });
    };

    var _addMarkers = function() {

      console.log('Add Markers');

      var data;
      var lon;
      var lat
      var marker;

      // $.getJSON('data/sample.json', function(response) {
      _getData('/api/?rank=subfamily', function(response) {

        console.log("Response: ", response);

        $.each(response, function(key, val) {
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

            _markersGroup.addLayer(marker);

          }

        });

      });
    };

    var _getData = function(path, success) {

      console.log('Get Data');

      $.getJSON(path, function(response) {

        // console.log("Response: ", response);

        success(response);

      });
    };

    return {
      init: _init
    };

  }(window, jQuery)
  .init());

// End