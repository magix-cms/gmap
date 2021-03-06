var gmap = (function ($, undefined) {
	/**
	 * Globals
	 */
	this.config = null;
	this.origin = null;
	this.defaults = null;
	this.gMap = null;
	this.map = null;
	this.marker = null;
	this.markers = [];
	this.flags = [];
	this.goTo = null;
	this.bounds = null;
	this.layers = 0;

    /**
     * @param config
     * @param options
     * @returns {{OriginContent: *, OriginAddress: *, originPosition: *}}
     */
    function init(config,options) {
    	var self = this;
    	self.config = config;
    	var company = null;

        if(config.markers[0].link === '' || config.markers[0].link === null) {
            company = config.markers[0].company;
        } else {
            company = '<a href="'+config.markers[0].link+'">'+config.markers[0].company+'</a>';
		}

        self.origin = {
            OriginContent : config.markers[0].company,
            OriginAddress : config.markers[0].address,
            OriginCity : config.markers[0].postcode + ' ' + config.markers[0].city,
            OriginCountry : config.markers[0].country,
            OriginPosition : [config.markers[0].lat, config.markers[0].lng],
            OriginRoute: 1,
            OriginMarker: config.marker
        };

        self.defaults = {
			center: self.origin.OriginPosition,
			zoom: 15,
			mapTypeControl: true,
			mapTypeControlOptions: {
				style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
				position: google.maps.ControlPosition.TOP_RIGHT
			},
			navigationControl: true,
			panControl: true,
			scrollwheel: true,
			streetViewControl: true
		};

        if(config.markers.length > 1) {
			var labels = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			var labelIndex = 0;

			$.each(config.markers, function(key, val) {
				var latLng = [val.lat, val.lng];
				if(val.link === '' || val.link === null) {
					company = val.company;
				} else {
					company = '<a href="'+val.link+'">'+val.company+'</a>';
				}
				var content = company+'<br />'+val.address+'<br />'+val.postcode+' '+val.city+', '+val.country;
				self.markers.push({
					position: latLng,
					icon: {
						url: "/"+self.config.lang+"/gmap/?marker=main&dotless=true",
						labelOrigin: {x:13, y:13}
					},
					label: labels[labelIndex++ % labels.length],
					content: content
				});
			});
		}
		else {
        	var val = config.markers[0];
			var latLng = [val.lat, val.lng];
			if(val.link === '' || val.link === null) {
				company = val.company;
			} else {
				company = '<a href="'+val.link+'">'+val.company+'</a>';
			}
			var content = company+'<br />'+val.address+'<br />'+val.postcode+' '+val.city+', '+val.country;
			self.markers.push({
				position: latLng,
				icon: "/"+self.config.lang+"/gmap/?marker=main",
				content: content
			});
		}

		loadMap(options);
    }

	/**
	 * Retrieve address information from marker content
	 * @param content
	 * @returns {{company: string, address: string, city: string, country: string}}
	 */
	function getAddressInfos(content) {
    	var newC = {
    		company: '',
			address: '',
			city: '',
			country: ''
		};

		content = content.split('<br />');
		newC.company = content[0];
		newC.address = content[1];
		content = content[2].split(', ');
		newC.city = content[0];
		newC.country = content[1];

		return newC;
	}

    /**
	 * Change address into the direction panel
     * @param item
     */
	function changeDirection(item) {
        this.goTo = item;
        var content = getAddressInfos(item.content);
        var pos = item.getPosition();
        $('#address .address').text(content.address);
        $('#address .city').text(content.city);
        $('#address .country').text(content.country);
        if(self.config.OS === 'IOS') {
            $('#openapp').attr('href','http://maps.apple.com/maps?ll=' + pos.lat() + ',' + pos.lng() + '&q=' + encodeURIComponent(content.address + ',' + content.city + ',' + content.country));
		}
		else {
            $('#openapp').attr('href','geo:' + pos.lat() + ',' + pos.lng() + '?q=' + encodeURIComponent(content.address + ',' + content.city + ',' + content.country));
		}
    }

    /**
     * Chargement de la carte par défaut
     * @param options
     */
    function loadMap(options) {
    	var self = this;
    	var item = self.origin;

    	if (typeof options !== 'undefined' && typeof options === 'object') {
			for (var attr in options) { self.defaults[attr] = options[attr]; }
		}

		self.gMap = $('#map_adress').gmap3(self.defaults);
        self.gMap
			.marker(self.markers)
			.infowindow(self.markers)
			.then(function (infowindow) {
				self.map = this.get(0);
				self.defaults.center = self.map.getCenter();
				self.markers = this.get(1);
				self.goTo = infowindow[0];
                var layers = this.get();
                self.layers = layers.length;
				if(self.markers.length > 1) {
					self.gMap.fit();
				} else {
					centerMap(false);
				}
				self.markers.forEach(function(item,i){
                    google.maps.event.addListener(infowindow[i],'closeclick', function() {
                        changeDirection(infowindow[0]);
					});
                    google.maps.event.addListener(item,'click', function() {
						infowindow.forEach(function(item,i){ infowindow[i].close(); });
						infowindow[i].open(self.map, item);
                        changeDirection(infowindow[i]);
					});
				});
				$('.select-marker').on('click', function(e) {
					e.preventDefault();
                    var tar = $('#address').position().top;
                    $('html, body').animate({ scrollTop: (tar) }, 500);
					var i = $(this).data('marker');
                    google.maps.event.trigger(self.markers[i], "click");
				});
				if(self.origin.OriginRoute) {
					if(self.goTo != null) { getDirection(); }
				}
				if(self.defaults.streetViewControl) {
					var stv = self.map.getStreetView();
					google.maps.event.addListener(stv, 'visible_changed', function() {
						if (stv.getVisible()) {
							$('#gmap-address').css('opacity',0).css('visibility','hidden');
						} else {
							$('#gmap-address').css('opacity',1).css('visibility','visible');
						}
					});
				}
			});
    }

	/**
	 * Re center map
	 * @param toBounds
	 * @param offsetX
	 * @param offsetY
	 */
	function centerMap(toBounds, offsetX, offsetY) {
		var self = this;
		var center;
		var newCenter;

		if (toBounds) {
			self.bounds = self.map.getBounds();
			self.map.panToBounds(self.bounds);
			center = self.map.getCenter();
		} else {
			center = self.defaults.center;
			//center = self.map.getCenter();
			self.map.setZoom(self.defaults.zoom);
		}

		var scale = Math.pow(2, self.map.getZoom());

		var worldCoordinateCenter = self.map.getProjection().fromLatLngToPoint(center);
		var pixelOffset = new google.maps.Point( (offsetX/scale) || 0,(offsetY/scale) || 0 );

		var worldCoordinateNewCenter = new google.maps.Point(
			worldCoordinateCenter.x - pixelOffset.x,
			worldCoordinateCenter.y + pixelOffset.y
		);

		newCenter = self.map.getProjection().fromPointToLatLng(worldCoordinateNewCenter);

		self.map.setCenter(newCenter);
	}

	/**
	 * Empty direction panel then check for new content to apply custom scrollbar
	 */
	function showDirectionPanel() {
		$('#r-directions').addClass('sizedirection');
		var checkRoute = setInterval(function () {
			var fill = $("#r-directions .adp").html();
			if (fill !== undefined) {
				// Custom Scrollbar
				$('#r-directions .adp').perfectScrollbar({suppressScrollX: true});
				clearInterval(checkRoute);
			}
		}, 50);
	}

    /**
	 * Show initials Markers from the map
     * @param markers
     */
	function showMarkers(markers) {
		var self = this;
		var map = self.map;
		var mm = self.markers;
        for (var i = 0; i < mm.length; i++) {
            mm[i].setMap(map);
            mm[i].setOpacity(1);
            mm[i].setVisible(true);
        }
	}

    /**
	 * Hide initials Markers from the map
     */
	function hideMarkers(markers) {
        var mm = self.markers;
        for (var i = 0; i < mm.length; i++) {
            mm[i].setMap(null);
            mm[i].setOpacity(0);
            mm[i].setVisible(false);
        }
	}

    /**
     * @param layer
     */
	function hideLayer(layer) {
        if(layer.length !== 'undefined' && layer.length > 1) {
            layer.forEach(function(item,i){
                hideLayer(item);
            })
        }
        else {
            if(typeof layer.setMap === 'function')
                layer.setMap(null);
            if(typeof layer.setOpacity === 'function')
                layer.setOpacity(0);
            if(typeof layer.setVisible === 'function')
                layer.setVisible(false);
        }
    }

    /**
	 * Hide everything on the map except the initials markers
     */
	function clearMap() {
		var self = this;
		var layers = self.gMap.get();
        for (var i = self.layers; i < layers.length; i++) {
        	var layer = self.gMap.get(i);
            hideLayer(layer);
        }
        self.layers = layers.length;
    }

	/**
	 * Remove direction renderer
	 */
	function delDirections() {
		var self = this;
		var layers = self.gMap.get();

		for (var index in layers) {
			if(layers[index].hasOwnProperty('directions')) {
				$('#r-directions').empty().removeClass('sizedirection');
				layers[index].setMap(null);
			}
		}

		clearMap();
		showMarkers(self.markers);
	}

    /**
     * Trace la route sur la carte
     */
    function setDirection() {
    	var self = this;
    	var item = self.origin;
    	var dest = $('#getadress').val();

		if (dest.length != 0) {
			delDirections();

			self.gMap
				.route({
					origin: dest,
					//destination: item.OriginAddress,
					destination: self.goTo.position,
					travelMode: google.maps.DirectionsTravelMode.DRIVING
				})
				.directionsrenderer(function (results) {
						if (results) {
                            var leg = results.routes[0].legs[0];
                            self.flags = [
                            	{
									position: leg.start_location,
									icon: {
										url: "/"+self.config.lang+"/gmap/?marker=grey&dotless=true",
										labelOrigin: {x:14, y:13}
									},
									label: 'A',
									content: leg.start_address
                            	},
								{
									position: leg.end_location,
									icon: {
										url: "/"+self.config.lang+"/gmap/?marker=main&dotless=true",
										labelOrigin: {x:14, y:13}
									},
									label: 'B',
									content: leg.end_address
								}
                            ];

                            hideMarkers(self.markers);

                            self.gMap
								.marker(self.flags)
								.infowindow(self.flags)
								.then(function (infowindow) {
									var layers = this.get();
                                	self.flags = this.get((layers.length - 2));
                                	self.flags.forEach(function(item,i){
                                    item.addListener('click', function() {
                                        infowindow[i].open(map, item);
                                    });
                                });
                            });

							showDirectionPanel();
							return {
								panel: "#r-directions",
                                suppressMarkers: true,
								directions: results
							}
						}
					}
				)
				.then(function(){
					var x = $('#gmap-address').width()/2;
					centerMap(true,x,0);
				})
				.catch(function (error) {
					console.error('catched: ' + error);
				});
		}
    }

    /**
     * Formulaire de recherche d'itinéraire
     */
    function getDirection() {
        $('.form-search').submit(function(e) {
            e.preventDefault();
            setDirection();
        });

		$('.hidepanel').on('click',function(){
			var $self = $(this),
				bloc = $('#gmap-address');

			if(!$self.hasClass('open')) {
				$self.addClass('open');
				bloc.addClass('open');
			}
			else {
				$self.removeClass('open');
				bloc.removeClass('open');
			}
		});

		$('#showform').on('click',function(){
			if(!$(this).hasClass('open')) {
                $(this).addClass('open');
			}
			else {
                $(this).removeClass('open');
				delDirections();
				if(self.markers.length > 1) {
					self.gMap.fit();
				} else {
					centerMap(false);
				}
				$('#getadress').val('');
			}
		});
    }

    return {
        //Fonction Public
        run:function (config,options) {
            init(config,options);
        }
    };
})(jQuery);