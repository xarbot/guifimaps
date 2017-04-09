<?php
include dirname(__FILE__).'/../functions.php';
date_default_timezone_set('Europe/Madrid');
$mysqli = mysql_link ();
$sql = "SELECT distinct timestamp_captura from nodes order by timestamp_captura DESC";
$result = $mysqli->query($sql);
$i=0;
$timestamps= array();
while ($row=$result->fetch_assoc()){
	$timestamps[$i]=$row["timestamp_captura"];
	$i++;
}
header('Content-Type: application/json');
echo json_encode($timestamps,JSON_FORCE_OBJECT);
?>

