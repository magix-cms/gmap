class GoogleMap {
	/**
	 * @param {Object} Libraries
	 * @param {Object} options
	 */
	constructor(Libraries, options) {
		this.g = Libraries;
		this.OS = null;
		this.lang = null;
		this.origin = null;
		this.map = {
			id: 'gmap_map',
			options: {
				zoom: 15,
				mapTypeControl: true,
				mapTypeControlOptions: {
					style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
					position: google.maps.ControlPosition.TOP_RIGHT
				},
				navigationControl: true,
				panControl: true,
				scrollwheel: false,
				streetViewControl: true
			},
			instance: null,
			directions: null,
			markers: [],
			infowindows: []
		};
		this.marker = null;
		this.markers = [];
		this.flags = [];
		this.goTo = null;
		this.layers = 0;

		this.options = {
			marker: {
				label: false,
				autoLabel: false
			}
		};

		if(typeof options === 'object') this.set(options);
		if(this.markers.length > 0) this.init(Libraries.Map, Libraries.Marker,  Libraries.LatLngBounds);
	}

	/**
	 * @param {object} options
	 */
	set(options) {
		let instance = this;
		for (var key in options) {
			if (options.hasOwnProperty(key)) instance[key] = options[key];
		}
	}

	/**
	 * Retrieve address information from marker content
	 * @param content
	 * @returns {{company: string, address: string, city: string, country: string}}
	 */
	getAddressInfos(content) {
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
	 * @param {InfoWindow} infowindow
	 */
	changeDirection(infowindow) {
		let GM = this;
		GM.goTo = infowindow;
		let content = GM.getAddressInfos(infowindow.getContent());
		let pos = infowindow.getPosition();
		document.querySelector('#address .address').textContent = content.address;
		document.querySelector('#address .city').textContent = content.city;
		document.querySelector('#address .country').textContent = content.country;
		let href = (GM.OS === 'IOS' ? 'http://maps.apple.com/maps?ll=' : 'geo:') + pos.lat() + ',' + pos.lng() + '?q=' + encodeURIComponent(content.address + ',' + content.city + ',' + content.country);
		document.getElementById('openapp').setAttribute('href',href);
	}

	/**
	 * Re center map
	 * @param toBounds
	 * @param offsetX
	 * @param offsetY
	 */
	centerMap(toBounds, offsetX, offsetY) {
		let GM = this;
		let center;
		let newCenter;

		if (toBounds) {
			GM.map.instance.panToBounds(GM.map.instance.getBounds());
			/*GM.map.instance.fitBounds(GM.map.instance.getBounds(),{
				bottom: 0,
				left: offsetX,
				right: 0,
				top: offsetY
			});*/
			center = GM.map.instance.getCenter();
		} else {
			center = GM.map.options.center;
			GM.map.instance.setZoom(GM.map.options.zoom);
		}

		let scale = Math.pow(2, GM.map.instance.getZoom());

		let worldCoordinateCenter = GM.map.instance.getProjection().fromLatLngToPoint(center);
		let pixelOffset = new GM.g.Point( (offsetX/scale) || 0,(offsetY/scale) || 0 );

		let worldCoordinateNewCenter = new GM.g.Point(
			worldCoordinateCenter.x - pixelOffset.x,
			worldCoordinateCenter.y + pixelOffset.y
		);

		newCenter = GM.map.instance.getProjection().fromPointToLatLng(worldCoordinateNewCenter);

		GM.map.instance.setCenter(newCenter);
	}

	/**
	 * Empty direction panel then check for new content to apply custom scrollbar
	 */
	showDirectionPanel() {
		let directions = document.getElementById('r-directions');
		directions.classList.add('sizedirection');
	}

	/**
	 * Show initials Markers from the map
	 * @param markers
	 */
	showMarkers(markers) {
		let GM = this;
		let map = GM.map.instance;
		let mm = GM.map.markers;
		for (let i = 0; i < mm.length; i++) {
			mm[i].setMap(map);
			mm[i].setOpacity(1);
			mm[i].setVisible(true);
		}
	}

	/**
	 * Hide initials Markers from the map
	 */
	hideMarkers(markers) {
		let GM = this;
		for (let i = 0; i < markers.length; i++) {
			markers[i].setMap(null);
			markers[i].setOpacity(0);
			markers[i].setVisible(false);
		}
	}

	/**
	 * @param layer
	 */
	hideLayer(layer) {
		if(layer.length > 1) {
			layer.forEach((item) => GM.hideLayer(item));
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
	clearMap() {
		let GM = this;
		let layers = GM.map.instance.data;
		for (let i = GM.layers.length; i < layers.length; i++) {
			let layer = GM.map.instance.data[i];
			GM.hideLayer(layer);
		}
		GM.layers = GM.map.instance.data;
	}

	/**
	 * Remove direction renderer
	 */
	delDirections() {
		let GM = this;
		if (GM.map.directions !== null) {
			GM.hideMarkers(GM.flags);
			GM.map.directions.set('directions',null);
			let directions = document.getElementById('r-directions');
			directions.classList.remove('sizedirection');
		}
		GM.clearMap();
		GM.showMarkers(GM.map.markers);
	}

	/**
	 * Trace la route sur la carte
	 */
	setDirection() {
		let GM = this;
		let item = GM.origin;
		let dest = document.getElementById('getadress').value;

		if (dest.length > 0) {
			GM.delDirections();

			//GM.map.instance;
			let request = {
				origin: dest,
				destination: GM.goTo.getPosition(),
				travelMode: google.maps.DirectionsTravelMode.DRIVING
			};
			let directionService = new GM.g.DirectionsService();
			directionService.route(request)
				.then((result) => {
					if (result) {
						let leg = result.routes[0].legs[0];
						let bounds = new GM.g.LatLngBounds();
						bounds.extend(leg.start_location);
						bounds.extend(leg.end_location);

						let flags = [
							{
								position: leg.start_location,
								icon: {
									url: "/"+GM.lang+"/gmap/?marker=grey&dotless=true",
									labelOrigin: {x:14, y:13}
								},
								label: 'A',
								content: leg.start_address
							},
							{
								position: leg.end_location,
								icon: {
									url: "/"+GM.lang+"/gmap/?marker=main&dotless=true",
									labelOrigin: {x:14, y:13}
								},
								label: 'B',
								content: GM.goTo.getContent()
							}
						];
						GM.hideMarkers(GM.map.markers);
						flags.forEach((flagDetails,index) => {
							flagDetails.map = GM.map.instance;
							let flag = new GM.g.Marker(flagDetails);
							let infowindow = new google.maps.InfoWindow({
								content: flagDetails.content
							});
							flag.addListener('click', () => {
								infowindow.open({
									map: GM.map.instance,
									anchor: flag
								});
							});
							GM.flags[index] = flag;
						});
						if (GM.map.directions === null) {
							GM.map.directions = new google.maps.DirectionsRenderer({
								preserveViewport: true,
								suppressMarkers: true
							});
						}
						GM.map.directions.setMap(GM.map.instance);
						GM.map.directions.setPanel(document.getElementById('r-directions'));
						GM.map.directions.setDirections(result);
						GM.showDirectionPanel();

						let x = document.getElementById('gmap-address').getBoundingClientRect().width;

						GM.map.instance.fitBounds(bounds,{
							bottom: 0,
							left: x,
							right: 0,
							top: 0
						});
					}
				})
				.catch((e) => {
					alert("Could not display directions due to: " + e);
				});
		}
	}

	/**
	 * Formulaire de recherche d'itinéraire
	 */
	getDirection() {
		let GM = this;
		document.querySelector('.form-search').addEventListener('submit',(e) => {
			e.preventDefault();
			GM.setDirection();
		});

		let btn = document.querySelector('.hidepanel');
		btn.addEventListener('click',() => {
			let block = document.getElementById('gmap-address');

			btn.classList.toggle('open');
			block.classList.toggle('open');
		});
		let showform = document.getElementById('showform');
		showform.addEventListener('click',() => {
			if(showform.classList.contains('open')) {
				GM.delDirections();
				//if(self.markers.length > 1) {
				let bounds = new GM.g.LatLngBounds();
				GM.markers.forEach((markerDetails, index) => {
					let point = new google.maps.LatLng(markerDetails.lat, markerDetails.lng);
					bounds.extend(point);
				});
				GM.map.instance.fitBounds(bounds);
				if(GM.map.markers.length === 1) {
					GM.map.instance.setZoom(GM.map.options.zoom);
				}
				document.getElementById('getadress').value = '';
			}
			showform.classList.toggle('open');
		});
	}

	/**
	 * @returns {{OriginContent: *, OriginAddress: *, originPosition: *}}
	 */
	init() {
		let GM = this;

		if (GM.markers.length > 0) {
			GM.origin = {
				OriginContent : GM.markers[0].company,
				OriginAddress : GM.markers[0].address,
				OriginCity : GM.markers[0].postcode + ' ' + GM.markers[0].city,
				OriginCountry : GM.markers[0].country,
				OriginPosition : {lat: GM.markers[0].lat, lng: GM.markers[0].lng},
				OriginRoute: 1,
				OriginMarker: GM.marker
			};
			GM.map.options['center'] = new google.maps.LatLng(GM.markers[0].lat,GM.markers[0].lng);
			GM.map.instance = new GM.g.Map(document.getElementById(GM.map.id),GM.map.options);

			let bounds = new GM.g.LatLngBounds();
			GM.markers.forEach((markerDetails,index) => {
				let company = (markerDetails.link === '' || markerDetails.link === null) ? markerDetails.company : '<a href="'+markerDetails.link+'">'+markerDetails.company+'</a>';

				let point = new google.maps.LatLng(markerDetails.lat,markerDetails.lng);
				let markerOptions = {
					map: GM.map.instance,
					position: point,
					icon: "/"+GM.lang+"/gmap/?marker=main",
				};

				if (GM.options.marker.label) {
					if (markerDetails.label && !GM.options.marker.autoLabel) {
						markerOptions.icon += '&dotless=true';
						markerOptions.label = markerDetails.label;
					}
					else if (GM.options.marker.autoLabel && index > 0) {
						markerOptions.icon += '&dotless=true';
						let label = '';
						let labelLength = 1;
						let i = index+1;
						if (index > 25) {
							labelLength = Math.floor(Math.log(i) / Math.log(26));
							for (let k = 1; k < labelLength; k++) {
								label += String.fromCharCode(64 + k);
							}
						}
						label += String.fromCharCode(64 + i%26);
						markerOptions.label = label;
					}
				}

				let marker = new GM.g.Marker(markerOptions);
				bounds.extend(point);

				let infowindow = new google.maps.InfoWindow({
					content: company +'<br />'+markerDetails.address+'<br />'+markerDetails.postcode+' '+markerDetails.city+', '+markerDetails.country
				});

				GM.map.infowindows[index] = infowindow;
				GM.map.markers[index] = marker;
				if (index === 0) {
					GM.goTo = infowindow;
					infowindow.open({
						map: GM.map.instance,
						anchor: marker
					});
				}

				infowindow.addListener('closeclick', () => GM.changeDirection(GM.map.infowindows[0]));
				marker.addListener('click', () => {
					GM.map.infowindows.forEach((iw) => iw.close());
					infowindow.open({
						map: GM.map.instance,
						anchor: marker
					});
					GM.changeDirection(infowindow);
				});
			});
			if(GM.map.markers.length > 1) {
				GM.map.instance.fitBounds(bounds);
			}

			GM.layers = GM.map.instance.data;

			document.querySelectorAll('.select-marker').forEach((select) => {
				select.addEventListener('click',(e) => {
					e.preventDefault();
					let target = document.getElementById('#address');
					let targetPosition = getPosition(target);
					window.scroll({
						left: 0,
						top: targetPosition.y,
						behavior: 'smooth'
					});
					let i = select.dataset.marker;
					google.maps.event.trigger(GM.map.markers[i], "click");
				});
			});
			if(GM.origin.OriginRoute && GM.goTo !== null) GM.getDirection();
			if(GM.map.options.streetViewControl) {
				let stv = GM.map.instance.getStreetView();
				google.maps.event.addListener(stv, 'visible_changed', () => {
					let visible = stv.getVisible();
					let gmapAddress = document.getElementById('gmap-address');
					gmapAddress.style.opacity = visible ? '0' : '1';
					gmapAddress.style.visibility = visible ? 'hidden' : 'visible';
				});
			}
		}
	}
}
async function initMap() {
	const { Map } = await google.maps.importLibrary("maps");
	const { Marker } = await google.maps.importLibrary("marker");
	const { LatLngBounds } = await google.maps.importLibrary("core");
	const { Point } = await google.maps.importLibrary("core")
	const { DirectionsService } = await google.maps.importLibrary("routes");
	const { DirectionsRenderer } = await google.maps.importLibrary("routes");

	let gMap = new GoogleMap({
		Map: Map,
		Marker: Marker,
		LatLngBounds: LatLngBounds,
		Point: Point,
		DirectionsService: DirectionsService,
		DirectionsRenderer: DirectionsRenderer
	}, configMap);
}

(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
	key: configMap.api_key,
	v: "weekly",
	lang: configMap.lang
	// Use the 'v' parameter to indicate the version to use (weekly, beta, alpha, etc.).
	// Add other bootstrap parameters as needed, using camel case.
});

initMap();