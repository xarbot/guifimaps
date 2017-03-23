<?php
function mysql_link (){
        $host = "localhost";
        $username = "";
        $password = "";
        $dbname = "maps";
        $mysqli = new mysqli($host, $username, $password, $dbname);
	return($mysqli);
}

function excluded_channel($canal) {
	$mysqli = mysql_link ();
        
        $sql = "select * from excluded_channels where name like '".$canal."'";
        $result = $mysqli->query($sql);
	if ($result->num_rows>0){
		return(true);
	}else{
		return(false);
	}
}

function update_gateways($timestamp){
	$mysqli = mysql_link ();
        $sql = "select distinct gwinetid from nodes where timestamp_captura='".$timestamp."'";
        $result = $mysqli->query($sql);
        while($row = $result->fetch_assoc()) {
		$sql2 = "update nodes set gwinetid='".$row["gwinetid"]."' where timestamp_captura='".$timestamp."' and uid='".$row["gwinetid"]."'";
        	$result2 = $mysqli->query($sql2);
	}
        $sql = "select distinct gwmeshid from nodes where timestamp_captura='".$timestamp."'";
        $result = $mysqli->query($sql);
        while($row = $result->fetch_assoc()) {
                $sql2 = "update nodes set gwmeshid='".$row["gwmeshid"]."' where timestamp_captura='".$timestamp."' and uid='".$row["gwmeshid"]."'";
                $result2 = $mysqli->query($sql2);
        }
}

function excluded_links($timestamp) {
	$mysqli = mysql_link ();
	
	$sql = "select * from excluded_links order by origen";
	$result = $mysqli->query($sql);
	while($row = $result->fetch_assoc()) {
		$sql2 = "SELECT * from nodes where timestamp_captura='".$timestamp."' and name like '".$row["origen"]."'";
        	$result2 = $mysqli->query($sql2);
        	$row2=$result2->fetch_assoc();
        	$nodeuid=$row2["uid"];
		
		$sql3 = "SELECT * from nodes where timestamp_captura='".$timestamp."' and name like '".$row["desti"]."'";
                $result3 = $mysqli->query($sql3);
                $row3=$result3->fetch_assoc();
                $nodeTouid=$row3["uid"];

                $sql4 = "delete from adjacencies  where nodeuid='".$nodeuid."' and nodeTouid='".$nodeTouid."' and canal='".$row['canal']."' and timestamp_captura='".$timestamp."'";
		echo $sql4."\n";
                $result4 = $mysqli->query($sql4);

                $sql5 = "delete from adjacencies  where nodeuid='".$nodeTouid."' and nodeTouid='".$nodeuid."' and canal='".$row['canal']."' and timestamp_captura='".$timestamp."'";
                echo $sql5."\n";
		$result5 = $mysqli->query($sql5);
	}
}

function distanceCalculation($point1_lat, $point1_long, $point2_lat, $point2_long, $unit = 'km', $decimals = 2) {
	// Calcul de la distancia en graus
	$degrees = rad2deg(acos((sin(deg2rad($point1_lat))*sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat))*cos(deg2rad($point2_lat))*cos(deg2rad($point1_long-$point2_long)))));
 
	// Conversio de la distancia en graus a la unitat escollida 
	switch($unit) {
		case 'km':
			$distance = $degrees * 111.13384;
			break;
		case 'mi':
			$distance = $degrees * 69.05482;
			break;
		case 'nmi':
			$distance =  $degrees * 59.97662;
	}
	return round($distance, $decimals);
}
?>
