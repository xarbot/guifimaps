<?php
//header("Content-type: application/json");
//echo json_encode($_GET["dia"]);
//exit();
include 'functions.php';

if ($_GET["dia"]==""){
	$mysqli = mysql_link ();
	$sql = "SELECT distinct from_unixtime(timestamp_captura, '%d-%m-%Y') AS 'data_captura' from nodes order by data_captura ASC";
	$result = $mysqli->query($sql);
	$dates=array();
	while($row = $result->fetch_assoc()) {
		$dates[$row["data_captura"]]=$row["data_captura"];
	}
	//var_dump($dates);
	header("Content-type: application/json");
	echo json_encode($dates);
}else{
	date_default_timezone_set('UTC');
	$mysqli = mysql_link ();
	list($dia,$mes,$any)=explode("/",$_GET["dia"]);
	$data_inici=mktime("00","00","00",$mes,$dia,$any);
	$data_fi=mktime("23","59","59",$mes,$dia,$any);
        //echo date("d/m/Y H:m:s", $data_inici)."<br>";
	//echo date("d/m/Y H:m:s", $data_fi)."<br>";
	//echo $data_inici;
	$sql = "SELECT distinct timestamp_captura from nodes where timestamp_captura>'".$data_inici."' and timestamp_captura<'".$data_fi."' order by timestamp_captura ASC";
	//echo $sql;
	$result = $mysqli->query($sql);
        $dates=array();
        while($row = $result->fetch_assoc()) {
                $dates[$row["timestamp_captura"]]=date('H:i:s',$row["timestamp_captura"]);
        }
        //var_dump($dates);
        header("Content-type: application/json");
        echo json_encode($dates);
}
?>
