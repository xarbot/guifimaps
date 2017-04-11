<?php
include dirname(__FILE__).'/../functions.php';
date_default_timezone_set('Europe/Madrid');
$mysqli = mysql_link ();
$resultat= array();
if ($_GET["timestamp"]!=""){
	$timestamp=$_GET["timestamp"];
}else{
	$sql = "SELECT distinct timestamp_captura from nodes order by timestamp_captura DESC";
	$result = $mysqli->query($sql);
	$row=$result->fetch_assoc();
	$timestamp=$row["timestamp_captura"];
}
$resultat["timestamp"]=$timestamp;
$sql3 = "SELECT distinct timestamp_captura from nodes where timestamp_captura<'".$timestamp."' order by timestamp_captura DESC";
$result3 = $mysqli->query($sql3);
$row3=$result3->fetch_assoc();
$prev_timestamp=$row3["timestamp_captura"];

$resultat["data"]=date('d/m/Y H:i:s', $timestamp);

$sql = "SELECT * from nodes where timestamp_captura = '".$timestamp."' order by name ASC";
$result = $mysqli->query($sql);
while ($row=$result->fetch_assoc()){
	$resultat["nodes"][$row["uid"]]["name"]=$row["name"];
	$resultat["nodes"][$row["uid"]]["lon"]=$row["lon"];
	$resultat["nodes"][$row["uid"]]["lat"]=$row["lat"];
	$resultat["nodes"][$row["uid"]]["uptime"]=$row["uptime"];
	$resultat["nodes"][$row["uid"]]["system"]=$row["system"];
	$resultat["nodes"][$row["uid"]]["firmware"]=$row["qmpversion"];
	
	//Inet path
	$sql2 = "SELECT * from inet_paths where uid = '".$row["uid"]."' and timestamp_captura = '".$timestamp."'";
	$result2 = $mysqli->query($sql2);
	$row2=$result2->fetch_assoc();
	$tmp=json_decode($row2["gwpath"],true);
	$resultat["nodes"][$row["uid"]]["inet_path"]=$tmp;

        //Mesh path 
        $sql2 = "SELECT * from community_paths where uid = '".$row["uid"]."' and timestamp_captura = '".$timestamp."'";
        $result2 = $mysqli->query($sql2);
        $row2=$result2->fetch_assoc();
        $tmp=json_decode($row2["gwpath"],true);
        $resultat["nodes"][$row["uid"]]["mesh_path"]=$tmp;

        //Links
        $sql2 = "SELECT * from adjacencies where nodeuid = '".$row["uid"]."' and timestamp_captura = '".$timestamp."'";
        $result2 = $mysqli->query($sql2);
        while ($row2=$result2->fetch_assoc()){
        	$resultat["nodes"][$row["uid"]]["links"][$row2["nodeTouid"]]["channel"]=$row2["canal"];
		$resultat["nodes"][$row["uid"]]["links"][$row2["nodeTouid"]]["signal"]=$row2["senyal"];
		$resultat["nodes"][$row["uid"]]["links"][$row2["nodeTouid"]]["ping"]=$row2["ping"];
		$resultat["nodes"][$row["uid"]]["links"][$row2["nodeTouid"]]["bandwidth"]=$row2["ample"];
	}

        //IP's
        $sql2 = "SELECT * from ip where uid = '".$row["uid"]."' and timestamp_captura = '".$timestamp."'";
        $result2 = $mysqli->query($sql2);
        while ($row2=$result2->fetch_assoc()){ 
                $resultat["nodes"][$row["uid"]]["ip"][$row2["tipus"]]=$row2["ip"];
        }

        //Traffic
        $sql2 = "SELECT * from interface_volume where uid = '".$row["uid"]."' and interface not like '%bmx%' and timestamp_captura = '".$timestamp."'";
        $result2 = $mysqli->query($sql2);
        while ($row2=$result2->fetch_assoc()){ 
		$sql3 = "SELECT * from interface_volume where uid = '".$row["uid"]."' and interface like '".$row2["interface"]."' and timestamp_captura = '".$prev_timestamp."'";
                $result3 = $mysqli->query($sql3);
                $row3=$result3->fetch_assoc();
		
		$previn=$row3["inbytes"];
		$prevout=$row3["outbytes"];

		$nowin=$row2["inbytes"];
		$nowout=$row2["outbytes"];
                $resultat["nodes"][$row["uid"]]["volume"][$row2["interface"]]["in"]=$nowin-$previn;
		$resultat["nodes"][$row["uid"]]["volume"][$row2["interface"]]["out"]=$nowout-$prevout;
        }

}

header('Content-Type: application/json');
echo json_encode($resultat,JSON_FORCE_OBJECT);
?>
