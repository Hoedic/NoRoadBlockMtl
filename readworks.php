<?php

//Connect to DB
	include_once("config.php");
	$conn = pg_pconnect("dbname=". $GLOBALS["db_name"] ." user=". $GLOBALS["db_user"] ." password=". $GLOBALS["db_password"]);
	if (!$conn) {
		echo "An error occured on connect.\n";
		exit;
	}

	//Read XML file from Ville de Montreal
	$xml = simplexml_load_file('http://applicatif.ville.montreal.qc.ca/e-cite/kml/chantiers_vgml.asp'); 
	//print_r($xml); 

	//Parse XML and insert in DB each roadwork found
	foreach ($xml->Document->Folder as $aFolder) {
		foreach ($aFolder->Placemarks->Placemark as $aPlace) {
			echo $aPlace->name, "<br/>";
			insertWork($aPlace);
   	}
	}


//Function to insert a "place" in the DB
function insertWork($myWorkObject){

	//Reformat coordinates
	$fullCoordinates = formatInsertCoordinatesfromGML($myWorkObject->LineString->coordinates . "");

	//Prepare insertion query
	$myQuery = "INSERT INTO points_chantier(\"name\", \"where\", \"raw_description\", \"point\")";
	$myQuery .= " VALUES ('". pg_escape_string($myWorkObject->name) ."', 'Where vide', 'Empty description', ";
	$myQuery .= "ST_GeomFromText('". $fullCoordinates ."', 4326))";


	echo $myQuery . "<br/>";
	
	//Run query
	$result = pg_query($GLOBALS["conn"], $myQuery);

	if (!$result) {
		echo "An error occured on query.\n";
		exit;
	}else {
		echo "Looks like the insert is OK";
	}
}

//Function to format the string of coordinats provided in XML to a string formatted for PostGIS)
function formatInsertCoordinatesfromGML($string){
	

	$output = "";
	$objectType = "";
	$explodedString = explode("\n", $string);
	
	if (count($explodedString) == 1){
		$objectType = "POINT";
	}else {
		$objectType = "LINESTRING";
	}

	for($i=0; $i < count($explodedString)-1; $i++) {
		$explodedPoint = explode(",", $explodedString[$i]);
		$output .= $explodedPoint[0] . " " . $explodedPoint[1] . ",";
	}
	
	$output = $objectType . "(" . substr($output, 0, strlen($output)-1) . ")";	
	
	return $output;
}	
?>