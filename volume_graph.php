<?php
include 'functions.php';
include('phpgraphlib.php');
//$uid=$_GET["uid"];
//$timestamp=$_GET["timestamp"];
//$captures=$_GET["captures"]+1;
$uid=1210;
$timestamp="1490864107";
$captures="24";
$mysqli = mysql_link ();
date_default_timezone_set('Europe/Madrid');

$dataY1 = array();
$dataY2 = array();
$min=0;
$max=0;
$mlinka=0;
$mlinkb=0;
$sql="select distinct interface from interface_volume where uid='".$uid."' and inbytes<>0 and outbytes<>0 order by interface asc";
$result = $mysqli->query($sql);
while($row = $result->fetch_assoc()) {
	//Per cada interficie agafo les X darreres captures
	$inbytes=0;
	$outbytes=0;
	$sql2 = "select * from (SELECT distinct timestamp_captura from nodes where timestamp_captura <='".$timestamp."' order by timestamp_captura DESC limit ".$captures.") as a order by timestamp_captura asc";
	$result2 = $mysqli->query($sql2);
	while($row2 = $result2->fetch_assoc()) {
		$sql3="select * from interface_volume where uid='".$uid."' and interface like '".$row['interface']."' and timestamp_captura like '".$row2['timestamp_captura']."'";
		echo $sql3."\n";
		$result3 = $mysqli->query($sql3);
		while($row3 = $result3->fetch_assoc()) {
			if (($inbytes!=0)&&($outbytes!=0)){
				//Son les succesives iteracions, calculem sempre la resta respecte la anterior captura
				//Per saber quan trafic ha mogut en la darrera hora la interficie
				$dataY1[$row["interface"]][date('d/m H:i', $row2["timestamp_captura"])] = $row3["inbytes"]-$inbytes;
				$dataY2[$row["interface"]][date('d/m H:i', $row2["timestamp_captura"])] = $row3["outbytes"]-$outbytes;
			}
                        $inbytes=$row3["inbytes"];
                        $outbytes=$row3["outbytes"];
		}
	}
}
var_dump($dataY1);
var_dump($dataY2);
exit();

$sql = "select * from (SELECT distinct timestamp_captura from nodes 
where timestamp_captura <='".$timestamp."' order by timestamp_captura DESC limit ".$captures.") as a order by timestamp_captura asc";
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
