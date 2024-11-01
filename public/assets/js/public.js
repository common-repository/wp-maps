(function($) {
    "use strict";

    $(function() {

        function mmanimToQueue(theQueue, selector, animationprops) {

            theQueue.queue(function(next) {
                $(selector).animate(animationprops, next);

            });
        }

        // Mimo Queue Function
        function mmqueue(start) {

            var rest = [].splice.call(arguments, 1),
                promise = $.Deferred();

            if (start) {
                $.when(start()).then(function() {
                    mmqueue.apply(window, rest);
                });
            } else {
                promise.resolve();
            }
            return promise;
        }

        /* ========================================================================
         * DOM-based Routing
         * Based on http://goo.gl/EUTi53 by Paul Irish
         *
         * Only fires on body classes that match. If a body class contains a dash,
         * replace the dash with an underscore when adding it to the object below.
         *
         * .noConflict()
         * The routing is enclosed within an anonymous function so that you can
         * always reference jQuery with $, even when in .noConflict() mode.
         * ======================================================================== */

        // Use this variable to set up the common and page specific functions. If you
        // rename this variable, you will also need to rename the namespace below.
        var WpMaps_Display = {
            // All pages
            common: {
                init: function() {
                    // JavaScript to be fired on all pagesinitialize()


                }
            },
            // Home page
            home: {
                init: function() {
                    // JavaScript to be fired on the home page

                }
            }
        };

        // The routing fires all common scripts, followed by the page specific scripts.
        // Add additional events for more control over timing e.g. a finalize event
        var UTIL = {
            fire: function(func, funcname, args) {
                var namespace = WpMaps_Display;
                funcname = (funcname === undefined) ? 'init' : funcname;
                if (func !== '' && namespace[func] && typeof namespace[func][funcname] === 'function') {
                    namespace[func][funcname](args);
                }
            },
            loadEvents: function() {
                UTIL.fire('common');

                $.each(document.body.className.replace(/-/g, '_').split(/\s+/), function(i, classnm) {
                    UTIL.fire(classnm);
                });
            }
        };

        $(document).ready(UTIL.loadEvents);

        var locations = $.parseJSON(map_data);
        var directionsDisplay;
        var directionsService = new google.maps.DirectionsService();
        var bounds = new google.maps.LatLngBounds();
        var map;

        


        function calcRoute(from, to, map, waypts, color,travelmode) {
            //use only  a single maps-instance

            
            var directionsRequest = {
                origin: from,
                destination: to,
                waypoints: waypts,
                optimizeWaypoints: true,
                travelMode: google.maps.DirectionsTravelMode[travelmode],
                unitSystem: google.maps.UnitSystem.METRIC
            };

            directionsService.route(
                directionsRequest,
                function(response, status) {
                    if (status == google.maps.DirectionsStatus.OK) {
                        new google.maps.DirectionsRenderer({
                            map: map,
                            preserveViewport: true, 
                            directions: response,
                            suppressMarkers: true,
                            polylineOptions: {
                                strokeColor: color,
                                strokeOpacity: 0.5,
                                strokeWeight: 5
                            }
                        });
                         
                        
                         
                         
                    } else
                        $("#error").append("Unable to retrieve your route<br />");
                }
            );
            


        }


        function initialize() {

            directionsDisplay = new google.maps.DirectionsRenderer({
                suppressMarkers: true
            });
            var infowindow = [];
            var marker, i;
            var parsedzoom = parseInt(wp_maps_zoom, 10);
            var map = new google.maps.Map(document.getElementById('wpmaps'), {
                zoom: parsedzoom,
                scrollwheel: false,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                 mapTypeControl: true,
                    mapTypeControlOptions: {
                    	style: google.maps.MapTypeControlStyle.DEFAULT,
                        mapTypeIds: [
                            google.maps.MapTypeId.ROADMAP,
                            google.maps.MapTypeId.TERRAIN,
                            google.maps.MapTypeId.HYBRID,
                            google.maps.MapTypeId.SATELLITE
                        ]
                    },
                    zoomControl: true,
                    zoomControlOptions: {
                        style: google.maps.ZoomControlStyle.SMALL
                    }
            });

           

            for (i = 0; i < locations.length; i++) {

                

                    if ((locations[i][18] !== false) && (locations[i][18] !== null) && (locations[i][18] !== '')) {
                        var mmlink = '<div class="card-footer"><a class="btn btn-sm btn-primary" href="' + locations[i][18] + '" class="wp-maps-read-more">' + locations[i][15] + '</a></div>';

                    } else {
                        var mmlink = '';


                    };

               if ((locations[i][4] !== false) && (locations[i][4] !== null) && (locations[i][4] !== '')) {
                        var mmcolor = locations[i][4];

                    } else {
                        var mmcolor = '#d62d20';


                    };

                    if ((locations[i][5] !== false) && (locations[i][5] !== null) && (locations[i][5] !== '')) {
                        var mmicon = '<i class="' + locations[i][5] + '"></i>';;

                    } else {
                        var mmicon = '<i class="map-icon map-icon-zoom-in"></i>';


                    };

                    if ( (locations[i][9] !== false) && (locations[i][9] !== null) && (locations[i][9] !== '')) {
                        var mmicontype = locations[i][9];

                    } else {
                        var mmicontype = 'SQUARE_PIN';


                    };
                
                    console.log(mmicontype);
                var mimocontent = '<div class="card"><div class="card-block"><div class="card-img">' + locations[i][11] + '</div><h5 class="card-title">' + locations[i][0] + '</h5><span class="card-subtitle text-muted">' + locations[i][17] + '</span><p class="card-content">' + locations[i][10] + '</p></div>' + mmlink + '</div>';

                var mimoboxstyle = {
                        background: "transparent",
                        opacity: 1,
                        width: "200px",
                        padding: 0,
                        position:"relative"
                    }

                 var mimo_icon_array = {
                        path: window[mmicontype],
                        fillColor: mmcolor,
                        fillOpacity: 1,
                        strokeColor: '',
                        strokeWeight: 0
                    }

                if ((locations[i][7]) && (locations[i][7] !== '0')) {
                    // Render our directions on the map
                    

                    // Set the current route - default: walking
                    marker = new Marker({
                        position: new google.maps.LatLng(locations[i][7], locations[i][8]),
                        map: map,                        
                        icon: mimo_icon_array,
                     map_icon_label: mmicon
                });
                    bounds.extend(marker.position);
                    InfoBox[i] = new InfoBox({
                    content: mimocontent,
                    disableAutoPan: false,
                    boxClass: "wp-maps-info-box",
                    maxWidth: 200,
                    pixelOffset: new google.maps.Size(25, -45),
                    zIndex: null,
                    boxStyle: mimoboxstyle,
                    closeBoxMargin: "0",
                    closeBoxURL: locations[i][14] + "/public/assets/images/close.gif",
                    infoBoxClearance: new google.maps.Size(1, 1)
                });



                google.maps.event.addListener(marker, 'click', function(marker, i) {
                    return function() {
                        InfoBox[i].open(map, marker);

                    }
                }(marker, i));

                google.maps.event.addListenerOnce(map, 'bounds_changed', function(event) {
                  if (this.getZoom() > parsedzoom) {
                    this.setZoom(parsedzoom);
                  }
                });
                   

                var start = '' + locations[i][1] + ',' + locations[i][2] + '';
                    var end = '' + locations[i][7] + ',' + locations[i][8] + '';

                    var waypts = [];
                    var mywaypoints = [];
                    if (locations[i][13] !== null) {
                        var mywaypoints = locations[i][13];
                        
                        for (var mycounter = 0; mycounter < mywaypoints.length ; mycounter++) {

                            var coordinates = mywaypoints[mycounter].split(',');
                            var myLatlng = new google.maps.LatLng(coordinates[0], coordinates[1]);
                            marker = new Marker({
                                position: myLatlng,
                                map: map,
                                icon: mimo_icon_array,
                                 map_icon_label: mmicon,
                                 title: locations[i][0]
                            });

                            InfoBox[i] = new InfoBox({
                    content: mimocontent,
                    disableAutoPan: false,
                    boxClass: "wp-maps-info-box",
                    maxWidth: 200,
                    pixelOffset: new google.maps.Size(25, -45),
                    zIndex: null,
                    boxStyle: mimoboxstyle,
                    closeBoxMargin: "0",
                    closeBoxURL: locations[i][14] + "/public/assets/images/close.gif",
                    infoBoxClearance: new google.maps.Size(1, 1)
                });



                google.maps.event.addListener(marker, 'click', function(marker, i) {
                    return function() {
                        InfoBox[i].open(map, marker);

                    }
                }(marker, i));


                            bounds.extend(marker.position);
                          waypts.push({
                            location: myLatlng,
                            stopover: true
                          });

                           
                        }

                    };
                    
                    var travelmode = locations[i][16];
                    

                    calcRoute(start, end, map, waypts, mmcolor, travelmode);
                   
                    
                            
                };





                marker = new Marker({
                    position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                    map: map,                   
                    icon: mimo_icon_array,
                     map_icon_label: mmicon
                });

                InfoBox[i] = new InfoBox({
                    content: mimocontent,
                    disableAutoPan: false,
                    boxClass: "wp-maps-info-box",
                    maxWidth: 200,
                    pixelOffset: new google.maps.Size(25, -45),
                    zIndex: null,
                    boxStyle: mimoboxstyle,
                    closeBoxMargin: "0",
                    closeBoxURL: locations[i][14] + "/public/assets/images/close.gif",
                    infoBoxClearance: new google.maps.Size(1, 1)
                });



                google.maps.event.addListener(marker, 'click', function(marker, i) {
                    return function() {
                        InfoBox[i].open(map, marker);

                    }
                }(marker, i));

                google.maps.event.addListenerOnce(map, 'bounds_changed', function(event) {
                  if (this.getZoom() > parsedzoom) {
                    this.setZoom(parsedzoom);
                  }
                });


                bounds.extend(marker.position);

                // Don't zoom in too far on only one marker
                if (bounds.getNorthEast().equals(bounds.getSouthWest())) {
                    var extendPoint1 = new google.maps.LatLng(bounds.getNorthEast().lat() + 0.01, bounds.getNorthEast().lng() + 0.01);
                    var extendPoint2 = new google.maps.LatLng(bounds.getNorthEast().lat() - 0.01, bounds.getNorthEast().lng() - 0.01);
                    bounds.extend(extendPoint1);
                    bounds.extend(extendPoint2);
                }

                
                
                $.ajaxSetup({
                    cache: false
                });
                var markerCluster = new MarkerClusterer(map, marker, i);
                map.fitBounds(bounds);
                $(".wp-maps-read-more").live('click', function(){
                      window.location.href=this.href;
                    });
            } //End for i






            

            


        } //End initialize functio9n


        // Place your public-facing JavaScript here
        //Associate the styled map with the MapTypeId and set it to display.


        // Calculate our route between the markers & set/change the mode of travel


        $(window).load(function() {

            initialize();

             


        });



    });


}(jQuery));
