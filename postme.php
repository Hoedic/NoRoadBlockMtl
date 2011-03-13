<?php

$conn = pg_pconnect("dbname=space_db user=postsql password=5QL4Dmin");
if (!$conn) {
  echo "An error occured on connect.\n";
  exit;
}



$selectString = 'select "name", ST_AsText(point) from points_chantier where ';
$selectString .= 'ST_DWithin(point, ST_GeomFromText(\'LINESTRING(';
$selectString .= $_POST["hiddenpath"];
$selectString .= ")', 4326), " . ($_POST["distance"]/316) .")";

//echo "Le select : " . $selectString . "<br/>";

$result = pg_query($conn, $selectString);
if (!$result) {
  echo "An error occured on query.\n";
  exit;
}

while ($row = pg_fetch_row($result)) {
  echo $row[0];
  echo "<br />\n";
}

?>
