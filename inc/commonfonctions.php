<?php

//Function to format the string of coordinats provided in XML to a string formatted for PostGIS)
function formatCoordinatesGMLtoPostgis($string){
	

	$output = "";
	$objectType = "";
	$explodedString = explode("\n", $string);
	
	//print_r($explodedString);
	
	if (count($explodedString) == 1){
		$objectType = "POINT";
	}else {
		$objectType = "LINESTRING";
	}

	foreach($explodedString as $onePoint) {
		$explodedPoint = explode(",", $onePoint);
		$output .= $explodedPoint[0] . " " . $explodedPoint[1] . ",";
	}
	
	//Check why we need to do a substring 3...
	$output = $objectType . "(" . substr($output, 0, strlen($output)-3) . ")";	
	//echo $output , "<br/>";
	
	return $output;
}	

function sendMail($to, $subject, $body){

	$headers = 'From: hoedic@mon-ile.net' . "\r\n" .
     'X-Mailer: PHP/' . phpversion();

	if(mail($to, $subject, $body, $headers)){
		echo "Envoi de courriel reussi";
	} else {
		echo "Envoi de courriel rate";
	}
	
}

?>