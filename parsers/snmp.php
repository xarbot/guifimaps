<?php
	include dirname(__FILE__).'/../functions.php';
	date_default_timezone_set('Europe/Madrid');
	$mysqli = mysql_link ();

        $sql = "SELECT timestamp_captura from nodes order by timestamp_captura DESC";
        $result = $mysqli->query($sql);
        $row=$result->fetch_assoc();
        $last_timestamp=$row["timestamp_captura"];
	echo $last_timestamp."\n";
	echo "-------------------------------------------\n";

	$sql2 = "SELECT distinct(ip),tipus,uid FROM ip where tipus like 'ipv4' and ip like '%/32' and timestamp_captura='".$last_timestamp."'";
	$result2 = $mysqli->query($sql2);
	while($row2 = $result2->fetch_assoc()) {
		echo "Analitzant IP: ".substr($row2['ip'], 0, -3)."\n";
		$uid=$row2["uid"];

                $session = new SNMP(SNMP::VERSION_1, substr($row2['ip'], 0, -3), "public");
		$fulltree = $session->walk(".");
		//$session = new SNMP(SNMP::VERSION_1, "10.0.30.1", "public");
		if ($fulltree["DISMAN-EVENT-MIB::sysUpTimeInstance"]==""){
			//Provem port alternatiu i si no descartem
			$session = new SNMP(SNMP::VERSION_1, substr($row2['ip'], 0, -3).":170", "public");
			$fulltree = $session->walk(".");
			echo "SNMP sense resposta, busco port alternatiu\n";
		}
		if ($fulltree["DISMAN-EVENT-MIB::sysUpTimeInstance"]!=""){
                	//$fulltree = $session->walk(".");
	                //print_r($fulltree);
			list($brossa,$uptime)=explode(")",$fulltree["DISMAN-EVENT-MIB::sysUpTimeInstance"]);
			//echo $uptime;
			//exit();
			$uptime=trim($uptime);
			echo $uptime."\n";
	                $session->close();
	
			$sql3 = "update nodes set uptime='".$uptime."' where uid='".$uid."' and timestamp_captura='".$last_timestamp."'";
			$result3 = $mysqli->query($sql3);

			$i=1;	
			$num_interficies=str_replace("INTEGER: ","",$fulltree["IF-MIB::ifNumber.0"])+1;
			while ($i<$num_interficies){
				$nom_interficie=str_replace("STRING: ","",$fulltree["IF-MIB::ifDescr.".$i]);
				$inbytes=str_replace("Counter32: ","",$fulltree["IF-MIB::ifInOctets.".$i]);
				$outbytes=str_replace("Counter32: ","",$fulltree["IF-MIB::ifOutOctets.".$i]);
				$sql4 = "insert into interface_volume(uid,interface,inbytes,outbytes,timestamp_captura) values ('".$uid."','".$nom_interficie."','".$inbytes."','".$outbytes."','".$last_timestamp."')";
				echo $sql4."\n";
				$result4 = $mysqli->query($sql4);
				$i++;
			}
		}else{
		     echo "S'ha produit un error: SNMP sense resposta.\n";
		}

		echo "--------------------------------------------------------------\n";
	}
?>
