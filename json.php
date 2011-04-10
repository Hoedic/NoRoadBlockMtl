<?php

	include_once("config.php");

	//Connect to DB
	$conn = pg_pconnect("dbname=". $GLOBALS["db_name"] ." user=". $GLOBALS["db_user"] ." password=". $GLOBALS["db_password"]);
	if (!$conn) {
		echo "An error occured on connect.\n";
		exit;
	}

	$boundaries = 0;
	if (isset($_GET["nelat"])){
		$boundaries = 1;
		$northEastLat = $_GET["nelat"];
		$northEastLon = $_GET["nelon"];
		$southWestLat = $_GET["swlat"];
		$southWestLon = $_GET["swlon"];
	}

	//echo $northEastLat . $northEastLon . $southWestLat . $southWestLon . '---------------------';
	
	$mapBoundaries = $northEastLon . " " . $northEastLat . ","; 
	$mapBoundaries .= $northEastLon . " " . $southWestLat . ","; 
	$mapBoundaries .= $southWestLon . " " . $southWestLat . ","; 
	$mapBoundaries .= $southWestLon . " " . $northEastLat . ","; 
	$mapBoundaries .= $northEastLon . " " . $northEastLat; 
	
	//Create SELECT query to retrieve the points within the distance selected in the form
	$selectString = 'select "name", ST_AsText(point) from points_chantier where ';
	$selectString .= 'ST_Contains(ST_MakePolygon(ST_GeomFromText(\'LINESTRING(';
	$selectString .= $mapBoundaries;
	$selectString .= ")', 4326)), points_chantier.point)";
	
	//echo $selectString . "++++++++++";
	
/*	select "name", ST_AsText(point) 
	from points_chantier 
	where ST_Contains(ST_MakePolygon(ST_GeomFromText(
	'LINESTRING(-73.53176150756838 45.53164438295237,-73.53176150756838 45.5015734959899,-73.62960849243166 45.5015734959899,-73.62960849243166 45.53164438295237,-73.53176150756838 45.53164438295237)',
	 4326)), points_chantier.point)*/

	//echo "Select string : " . $selectString . "<br/>";

	$result = pg_query($conn, $selectString);
	if (!$result) {
		echo "An error occured on query.\n";
		exit;
	}

	$i = 0;
	$arrayPoint;
	//Print in the page the list of the road work retrieved
	while ($row = pg_fetch_row($result)) {
		//echo $row[1];		
		//echo "<br />\n";
		$explodedLine = "";
		if (substr($row[1], 0, 10) == "LINESTRING"){
			$theline = substr(substr($row[1], 0, strlen($row[1])-1), 11);
			$explodedLine = explode(",", $theline);
		}
		
		$arrayPoint[$i] =  $explodedLine;
		$i++;
		
	}
	
	$encoded = json_encode($arrayPoint);	
	
	echo $encoded;
	//print_r($arrayPoint);

?>
