<?php

	include_once("config.php");

	//Connect to DB
	$conn = pg_pconnect("dbname=". $GLOBALS["db_name"] ." user=". $GLOBALS["db_user"] ." password=". $GLOBALS["db_password"]);
	if (!$conn) {
		echo "An error occured on connect.\n";
		exit;
	}


	//Create SELECT query to retrieve the points within the distance selected in the form
	$selectString = 'select "name", ST_AsText(point) from points_chantier where ';
	$selectString .= 'ST_DWithin(point, ST_GeomFromText(\'LINESTRING(';
	$selectString .= $_POST["hiddenpath"];
	$selectString .= ")', 4326), " . ($_POST["distance"]/316) .")";

	//echo "Select string : " . $selectString . "<br/>";

	$result = pg_query($conn, $selectString);
	if (!$result) {
		echo "An error occured on query.\n";
		exit;
	}

	//Print in the page the list of the road work retrieved
	while ($row = pg_fetch_row($result)) {
		echo $row[0];
		echo "<br />\n";
	}

?>
