<?php
include 'functions.php';
include('phpgraphlib.php');
$uid=$_GET["uid"];
$touid=$_GET["touid"];
$canal=$_GET["canal"];
$timestamp=$_GET["timestamp"];
$captures=$_GET["captures"];
$mysqli = mysql_link ();
date_default_timezone_set('UTC');

$dataY1 = array();
$dataY2 = array();
$min=0;
$max=0;
$mlinka=0;
$mlinkb=0;
$sql = "select * from (SELECT distinct timestamp_captura from nodes where timestamp_captura <='".$timestamp."' order by timestamp_captura DESC limit ".$captures.") as a order by timestamp_captura asc";
$result = $mysqli->query($sql);
while($row = $result->fetch_assoc()) {
	$sql2 = "SELECT * from adjacencies where nodeuid='".$uid."' and nodeTouid='".$touid."' and canal='".$canal."' and timestamp_captura='".$row['timestamp_captura']."'";
	$result2 = $mysqli->query($sql2);
	$row2 = $result2->fetch_assoc();
	$dataY1[date('d/m H:i', $row["timestamp_captura"])] = $row2["ample"];
	$mlinka=$mlinka+$row2["ample"];
	if ($min==0&&$max==0){
		$min=$row2["ample"];
		$max=$row2["ample"];
	}
	if ($row2["ample"]<$min){
		$min=$row2["ample"];
	}
        if ($row2["ample"]>$max){
                $max=$row2["ample"];
        }
        if ($row2["ample"]!=""){
                $dataY1[date('d/m H:i', $row["timestamp_captura"])] = $row2["ample"];
        }else{
                $dataY1[date('d/m H:i', $row["timestamp_captura"])] = 0;
        }



	$sql3 = "SELECT * from adjacencies where nodeuid='".$touid."' and nodeTouid='".$uid."' and canal='".$canal."' and timestamp_captura='".$row['timestamp_captura']."'";
	$result3 = $mysqli->query($sql3);
        $row3 = $result3->fetch_assoc();
	if ($row3["ample"]!=""){
	        $dataY2[date('d/m H:i', $row["timestamp_captura"])] = $row3["ample"];
	}else{
		$dataY2[date('d/m H:i', $row["timestamp_captura"])] = 0;
	}
	$mlinkb=$mlinkb+$row3["ample"];
        if ($row3["ample"]<$min){
                $min=$row3["ample"];
        }
        if ($row3["ample"]>$max){
                $max=$row3["ample"];
        }

}
//var_dump($dataY1);
//var_dump($dataY2);
//exit();
$sql = "SELECT * from nodes where uid='".$uid."'";
$result = $mysqli->query($sql);
$row = $result->fetch_assoc();
$nameuid=$row["name"];

$sql = "SELECT * from nodes where uid='".$touid."'";
$result = $mysqli->query($sql);
$row = $result->fetch_assoc();
$nametouid=$row["name"];

$linka=substr($nameuid, 0, -5)."->".substr($nametouid, 0, -5);
$linkb=substr($nametouid, 0, -5)."->".substr($nameuid, 0, -5);;

$mlinka=round($mlinka/$captures,2);
$mlinkb=round($mlinkb/$captures,2);

$graph = new PHPGraphLib(1024,800);
$graph->setTitle('Velocitat del link les darreres '.$captures.' hores');
$graph->setTitleLocation('left');
$graph->setLegend(true);
$graph->setLegendTitle($linka,$linkb);

if ($min>0){
	$min=round($min/1.20,0,PHP_ROUND_HALF_UP);
}
$max=round($max*1.20,0,PHP_ROUND_HALF_UP);
$graph->setRange($max,$min);
$graph->addData($dataY1, $dataY2);
$graph->setLineColor('red', 'blue');
$graph->setBars(false);
$graph->setLine(true);
$graph->setDataPoints(false);
$graph->setGoalLine($mlinka,'red');
$graph->setGoalLine($mlinkb,'blue');
$graph->createGraph();

?>
