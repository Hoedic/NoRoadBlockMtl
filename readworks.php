<?php

$conn = pg_pconnect("dbname=space_db user=postsql password=5QL4Dmin");
if (!$conn) {
  echo "An error occured on connect.\n";
  exit;
}

$xml = simplexml_load_file('http://applicatif.ville.montreal.qc.ca/e-cite/kml/chantiers_vgml.asp'); 
//print_r($xml); 

foreach ($xml->Document->Folder as $aFolder) {
	foreach ($aFolder->Placemarks->Placemark as $aPlace) {
		echo $aPlace->name, "<br/>";
		insertWork($aPlace);
   }
}



function insertWork($myWorkObject){

	$fullCoordinates = formatInsertCoordinatesfromGML($myWorkObject->LineString->coordinates . "");

	$myQuery = "INSERT INTO points_chantier(\"name\", \"where\", \"raw_description\", \"point\")";
	$myQuery .= " VALUES ('". pg_escape_string($myWorkObject->name) ."', 'Where vide', 'Description vide pour le moment', ";
	$myQuery .= "ST_GeomFromText('". $fullCoordinates ."', 4326))";


	echo $myQuery . "<br/>";

	$result = pg_query($GLOBALS["conn"], $myQuery);
	if (!$result) {
		echo "An error occured on query.\n";
		exit;
	}else {
		echo "On dirait que l'insert est OK!";
	}

}

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