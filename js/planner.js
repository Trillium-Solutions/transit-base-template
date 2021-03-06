( function() {
	
	// Use geolocation to fill in trip planner field(s)
	function getLocation(e) {
		var fieldId = e.target.parentElement.value;
		var field = document.getElementById(fieldId);
		var placeholder = field.placeholder;
		field.value = "";
		field.placeholder = "Getting current coordinates...";
		navigator.geolocation.getCurrentPosition( function(position) {
			field.value = position.coords.latitude + ', ' + position.coords.longitude;
			field.placeholder = placeholder;
		});
	}
	
	// For use with Google Places API
	// Must have valid API key and depends on script loaded first
	function initializeAutocomplete() {
		
		/******** *************************************
		Fill in these values with the approximate area
		of the transit system 
		**********************************************/
		
		var bottom_left_lat = 0.0;          				 
		var bottom_left_lon = 0.0;
		var top_right_lat = 0.0;
		var top_right_lon = 0.0;
		
		
		var defaultBounds = new google.maps.LatLngBounds(
			new google.maps.LatLng(bottom_left_lat, bottom_left_lon),
			new google.maps.LatLng(top_right_lat, top_right_lon)
		);

		var origin_input = document.getElementById('saddr');
		var destination_input = document.getElementById('daddr');
		var options = {
			bounds: defaultBounds,
			componentRestrictions: {country: 'us'}
		};

		var autocomplete_origin      = new google.maps.places.Autocomplete( origin_input, options );    
		var autocomplete_destination = new google.maps.places.Autocomplete( destination_input, options );
	}
	
	// Hide crosshair icons if the browser does not support geolocation
	function addGeolocation() {
		var locationButtons = document.querySelectorAll('#trip-planner .crosshair-icon');
		if ( !navigator.geolocation ) {
			for (i = 0; i < locationButtons.length; i++) {
				locationButtons[i].classList.add('hidden');
			}
		} else {
			for (var i = 0; i < locationButtons.length; i++) {
				locationButtons[i].addEventListener('click', getLocation, false);
			}
		}
	}
	
	// Use the hidden class to toggle default settings form section
	function expandDefault() {
		var editButton = document.querySelectorAll('#default-settings button')[0];
		editButton.addEventListener('click', function() {
			document.getElementById('default-settings').classList.add('hidden');
			document.getElementById('additional-settings').classList.remove('hidden');
		}, false);
	}
	
	function plannerSetup() {
		flatpickr("#fdate", {
			defaultDate: 'today',
			dateFormat: 'm/d/Y'
		});
		flatpickr("#ftime", {
			enableTime: true,
			noCalendar: true,
			dateFormat: "h:i K",
		}).setDate(new Date(), false, 'h:i K');
	}
	
	plannerSetup();
	expandDefault();
	addGeolocation();
	
	/***********************************************************
	Uncomment to add autocomplete (requires API script enqueued)
	************************************************************/
	if ( typeof( google ) != 'undefined' && google.maps.places ) {
		google.maps.event.addDomListener( window, 'load', initializeAutocomplete );
	}
})();
