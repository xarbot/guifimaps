<?php
                $session = new SNMP(SNMP::VERSION_1, "10.0.30.1", "public");
                $fulltree = $session->walk(".");
                //print_r($fulltree);
		//list($brossa,$uptime)=explode(")",$fulltree["DISMAN-EVENT-MIB::sysUpTimeInstance"]);
		//echo $uptime;
		$i=1;
		$num_interficies=str_replace("INTEGER: ","",$fulltree["IF-MIB::ifNumber.0"])+1;
		while ($i<$num_interficies){
			echo "entro aqui\n";
			$nom_interficie=str_replace("STRING: ","",$fulltree["IF-MIB::ifDescr.".$i]);
			$inbytes=str_replace("Counter32: ","",$fulltree["IF-MIB::ifInOctets.".$i]);
			$outbytes=str_replace("Counter32: ","",$fulltree["IF-MIB::ifOutOctets.".$i]);
			$sql3 = "insert into interface_volume(uid,interface,inbytes,outbytes,timestamp_captura) values ('".$uid."','".$nom_interficie."','".$inbytes."','".$outbytes."','".$last_timestamp."')";
			echo $sql3."\n";
			//$result3 = $mysqli->query($sql3);
			$i++;
		}
?>
