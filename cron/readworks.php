<?php

//Connect to DB
include_once("config.php");
include_once("commonfonctions.php");
	
$conn = pg_pconnect("dbname=". $GLOBALS["db_name"] ." user=". $GLOBALS["db_user"] ." password=". $GLOBALS["db_password"]);
if (!$conn) {
	echo "An error occured on connect.\n";
	exit;
}

//Reset update flag
$resetWorksQuery = "update points_chantier SET update_flag=false";
$result = pg_query($conn, $resetWorksQuery);	

if (!$result) {
	echo "An error occured on query : $resetWorksQuery.\n";
	exit;
}else {
	echo "Looks like the starting with ". substr($resetWorksQuery, 0, 15) . " is OK<hr/>";
}


//Read XML file from Ville de Montreal
//TODO : make XML file path configurable...
$xml = simplexml_load_file($GLOBALS["xml_roadwork"]); 
//print_r($xml); 

$mailBody = "";

//Parse XML and insert in DB each roadwork found
foreach ($xml->Document->Folder as $aFolder) {
	foreach ($aFolder->Placemarks->Placemark as $aPlace) {
		echo $aPlace->name, "<br/>";
		$mailBody .= insertWork($aPlace);
  	}
}

if (strlen($mailBody) > 0) {
	sendMail("stephane.guidoin@gmail.com", "[OpenMyRoad] Ajout de nouveaux travail", $mailBody);
}	
//Find work that disappeared
$checkDeletedWorksQuery = "SELECT name FROM points_chantier WHERE update_flag=false and is_active=true";	

$missingResult = pg_query($GLOBALS["conn"], $checkDeletedWorksQuery);	
$mailBody = "";

while ($row = pg_fetch_row($missingResult)) {
	$mailBody = "Suppression : " . $row[0] . "\n-----------------------------------\n";
}

if (strlen($mailBody) > 0) {
	sendMail("stephane.guidoin@gmail.com", "[OpenMyRoad] Suppression de travaux", $mailBody);
}
$deactivateWorksQuery = "update points_chantier SET is_active=false WHERE update_flag=false";
pg_query($GLOBALS["conn"], $deactivateWorksQuery);	



//Function to insert a "place" in the DB
function insertWork($myWorkObject){

	//Reformat coordinates

	if (isset($myWorkObject->LineString->coordinates)){
		$fullCoordinates = formatCoordinatesGMLtoPostgis($myWorkObject->LineString->coordinates . "");
	} elseif(isset($myWorkObject->Point->coordinates)) {
		$fullCoordinates = formatCoordinatesGMLtoPostgis($myWorkObject->Point->coordinates . "");
	}

	//Check if current work is already in the DB
	$selectQuery = "SELECT point FROM points_chantier WHERE point = ST_GeomFromText('". $fullCoordinates ."', 4326)";

	//echo $selectQuery . "<br/>";
	$mailBody = "";


	if(pg_num_rows(pg_query($GLOBALS["conn"], $selectQuery)) > 0){
		echo "Roadwork is already exists : ". pg_escape_string($myWorkObject->name) . "<br/>";
		$myQuery = "update points_chantier SET update_flag=true WHERE point = ST_GeomFromText('". $fullCoordinates ."', 4326)";
	} else {
		echo "Roadwork does not exists : " . pg_escape_string($myWorkObject->name) . " - will insert it<br/>";
		$myQuery = "INSERT INTO points_chantier(\"name\", \"where\", \"raw_description\", \"point\", \"update_flag\", \"is_active\")";
		$myQuery .= " VALUES ('". pg_escape_string($myWorkObject->name) ."', 'Where vide', '". pg_escape_string($myWorkObject->description) ."', ";
		$myQuery .= "ST_GeomFromText('". $fullCoordinates ."', 4326), true, true)";
			
		$mailBody = "Nouveau : " . $myWorkObject->name . "\n-----------------------------------\n";
	}
		

	$result = pg_query($GLOBALS["conn"], $myQuery);

	if (!$result) {
		echo "An error occured on query : $myQuery.\n";
		exit;
	}else {
		echo "Looks like the starting with ". substr($myQuery, 0, 15) . " is OK<hr/>";
	}
	
	return $mailBody;

	
}



?>
