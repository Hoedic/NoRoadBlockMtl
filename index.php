<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no"/>
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
<title>Google Maps JavaScript API v3 Example: Directions Simple</title>
<link href="http://code.google.com/apis/maps/documentation/javascript/examples/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript">
  var directionDisplay;
  var directionsService = new google.maps.DirectionsService();
  var map;

var rendererOptions = {
  draggable: true
};


  function initialize() {
    directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions);
    var myCentre = new google.maps.LatLng(45.50, -73.55);
    var myOptions = {
      zoom:7,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      center: myCentre
    }

    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
    directionsDisplay.setMap(map);


//Pour le moment, on ne détecte pas les changements de direction manuels...
/*
    google.maps.event.addListener(directionsDisplay, 'directions_changed', function() {
    calcRoute();
   });
*/

  }
  


  function calcRoute() {
  	
	  var start = document.getElementById("start").value;
	  var end = document.getElementById("end").value;
    /*var start = "Musée d'art contemporain de Montréal, Montréal, Canada";
    var end = "7000 Avenue du Parc, Montréal, Québec, Canada";*/
    var request = {
        origin:start, 
        destination:end,
        travelMode: google.maps.DirectionsTravelMode.DRIVING
    };
    directionsService.route(request, function(response, status) {
      if (status == google.maps.DirectionsStatus.OK) {
        directionsDisplay.setDirections(response);

		/*alert ("Taill du overview : " +  response.routes[0].overview_path.length);*/
		var fullString = "";
	  	var myRoute = response.routes[0].overview_path;

	  	for (var i = 0; i < myRoute.length; i++) {
			var exploded = (myRoute[i] + ",").split(",");
			fullString +=  exploded[1].replace(")", "").substring(1, 11) + " " + exploded[0].replace("(", "").substring(0, 10) + ",";


/*
			var marker = new google.maps.Marker({position: myRoute[i], map: map});

			google.maps.event.addListener(marker, 'click', function() {
			  infowindow.open(map,marker);
			});*/
        }
	  }

	  fullString = fullString.replace(")", "");
	  fullString = fullString.replace("(", "");
	  
	  var postValue = document.getElementById('hiddenpath');

	  postValue.value = fullString.substring(0, fullString.length - 2);

    });
  }
</script>
</head>
<body onload="initialize()">
<div>
<b>Start: </b> <input id="start" type="text" name="start" />&nbsp;&nbsp;&nbsp;
<b>End: </b> <input id="end" type="text" name="end" />&nbsp;&nbsp;&nbsp;
<input type="button" value="Tracer trajet" onclick="calcRoute();"/>&nbsp;&nbsp;&nbsp;

<form action="postme.php" method="post">
<input type="hidden" name="hiddenpath" id="hiddenpath" value=""/>
<input type="text" name="distance" id="distance" value="distance" />
<input type="submit" value="Valider trajet"/>&nbsp;&nbsp;&nbsp;
</form>
</div>
<div id="map_canvas"></div>
</body>
</html>
