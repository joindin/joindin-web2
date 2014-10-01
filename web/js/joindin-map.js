/**
 * joindIn_map Plugin
 */
(function($, window, document, undefined){
    $.fn.joindIn_map = function(method) {
        var methods = {
            defaults: {
                draggable: false,
                moveMapCallback: function() {}
            },
            init: function(options) {
                return this.each(function(){
                    // Merge options with the defaults.
                    var customOptions = $.extend({}, methods.defaults, options);
                    
                    // Get initial settings from the data-* attribs.
                    var $this = $(this);
                    var mapDiv = $(this);
                    var lat = mapDiv.attr('data-lat');
                    var lon = mapDiv.attr('data-lon');
                    var zoomLevel = mapDiv.attr('data-zoom');
                    if (zoomLevel > 18) {
                        zoomLevel = 18;
                    }

                    // Initialise the map
                    var map = new L.Map(mapDiv.attr('id'), {zoomControl: true});
                    var osmUrl = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                        osmAttribution = 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
                        osm = new L.TileLayer(osmUrl, {maxZoom: 18, attribution: osmAttribution});
                    map.setView(new L.LatLng(lat, lon), zoomLevel).addLayer(osm);
                    map.on('click', function(e){
                        methods.moveMap.call($this, {lat: e.latlng.lat, lon: e.latlng.lng});
                    });
                    map.on('zoomend', function(){
                        methods.moveMap.call($this, {});
                    });
                    
                    // Initialise the marker
                    if (customOptions.draggable) {
                        var marker = L.marker([lat, lon], { draggable: true } ).addTo(map);
                        marker.on('dragend', function() {
                            // Keep the data-* attribs updated with the marker position.
                            var latlng = marker.getLatLng();
                            methods.moveMap.call($this, {lat: latlng.lat, lon: latlng.lng});
                        });
                    } else {
                        var marker = L.marker([lat, lon], { draggable: false } ).addTo(map);
                    }
                    
                    // Store the data for this element.
                    $this.data('joindIn_map', {
                        options: customOptions,
                        map: map,
                        marker: marker
                    });
                    
                    // Trigger an initial moveMapCallback.
                    customOptions.moveMapCallback(this, {lat: lat, lon: lon, zoom: map.getZoom()});
                });
            },
            moveMap: function(options) {
                return this.each(function(){
                    // Get a data store on a per element basis.
                    var $this = $(this);
                    var data = $this.data('joindIn_map');
                    
                    // Update the map and marker positions.
                    if ((options.lat !== undefined) && (options.lon !== undefined)) {
                        data.map.setView(new L.LatLng(options.lat, options.lon), data.map.getZoom());
                        data.marker.setLatLng(data.map.getCenter());
                    }
                    
                    // Update the data-* attribs of the HTML element.
                    // We get the position from the marker incase the user zoomed into another part of the map
                    // but didn't move the marker.
                    // (And also because the soom action takes into account the mouse position!)
                    var latlng = data.marker.getLatLng();
                    $this.attr('data-lat', latlng.lat);
                    $this.attr('data-lon', latlng.lng);
                    $this.attr('data-zoom', data.map.getZoom());
                    
                    // Call the callback.
                    data.options.moveMapCallback(this, {lat: options.lat, lon: options.lon, zoom: data.map.getZoom()});
                });
            }
        };
        
        // Method calling logic
        if ( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist' );
        }

    };
})(jQuery, window, document);
