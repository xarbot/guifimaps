<?php
include dirname(__FILE__).'/../functions.php';
date_default_timezone_set('UTC');
$mysqli = mysql_link ();
$sql = "SELECT timestamp_captura from nodes order by timestamp_captura DESC";
$result = $mysqli->query($sql);
$row=$result->fetch_assoc();
$last_timestamp=$row["timestamp_captura"];
$nodes_trobats=0;

//var_dump($xml->network->zone->zone[3]);
//exit();

//Barcelones
$xml = simplexml_load_file('http://guifi.net/ca/guifi/cnml/2435/detail');
$nodes_trobats=$nodes_trobats+recursive_zone($xml->network->zone,$last_timestamp);
//BaixLlobregat
$xml = simplexml_load_file('http://guifi.net/es/guifi/cnml/2431/detail');
$nodes_trobats=$nodes_trobats+recursive_zone($xml->network->zone,$last_timestamp);
//Maresme
$xml = simplexml_load_file('http://guifi.net/es/guifi/cnml/2441/detail');
$nodes_trobats=$nodes_trobats+recursive_zone($xml->network->zone,$last_timestamp);
//Valles or.
$xml = simplexml_load_file('http://guifi.net/es/guifi/cnml/2476/detail');
$nodes_trobats=$nodes_trobats+recursive_zone($xml->network->zone,$last_timestamp);
//Valles occ.
$xml = simplexml_load_file('http://guifi.net/es/guifi/cnml/2604/detail');
$nodes_trobats=$nodes_trobats+recursive_zone($xml->network->zone,$last_timestamp);

echo "----------------------------------------------\n";
echo "S'han trobat coincidencies en ".$nodes_trobats." nodes\n";

function recursive_zone($zona,$last_timestamp){
	$trobats=0;
	$variables_zona=$zona->attributes();
        foreach ($zona->node as $node) {
                $variables_node=$node->attributes();
                foreach ($node->device as $device) {
			$uid="";
                        $variables_device=$device->attributes();
                        if ((strpos(strtolower($variables_device["firmware"]), "qmp")!==false)&&(strtolower($variables_device["status"])=="working"||strtolower($variables_device["status"])=="testing")){
				$mysqli = mysql_link ();
	                        $sql2 = "select * from ip where ip ='".$variables_device["mainipv4"]."/32' and timestamp_captura='".$last_timestamp."'";
				//echo $sql2."\n";
        	                $result2=$mysqli->query($sql2);
                                $sql3 = "select * from nodes where (name like '".$variables_device["title"]."' or SUBSTRING(name, 1, CHAR_LENGTH(name) - 5) like '".$variables_device["title"]."') and timestamp_captura='".$last_timestamp."'";
				//echo $sql3."\n";
                                $result3=$mysqli->query($sql3);
                	        if ($result2->num_rows>0||$result3->num_rows>0){
					$trobats++;
					echo "Coincidencia de trasto entre CNML i altres captures\n";
					if ($result2->num_rows>0){
                                                $row2=$result2->fetch_assoc();
                                                $uid=$row2["uid"];
						echo "Coincideix la IP\n";
					}
					if ($result3->num_rows>0){
						$row3=$result3->fetch_assoc();
						$uid=$row3["uid"];
						echo "Coincideix el nom\n";

					}
                                        $sql4="select * from ip where uid='".$uid."' and tipus like 'ipv4' and timestamp_captura='".$last_timestamp."'";
					//echo $sql4."\n";
                                        $result4=$mysqli->query($sql4);
                                        if ($result4->num_rows==0){
					   $sql5="INSERT INTO ip(uid,ip,tipus,timestamp_captura) VALUES ('".$uid."', '".$variables_device["mainipv4"]."/32','ipv4','".$last_timestamp."')";
				           echo $sql5."\n";
				           $result5=$mysqli->query($sql5);
				           $sql5="INSERT INTO ip(uid,ip,tipus,timestamp_captura) VALUES ('".$uid."', '".$variables_device["mainipv4"]."/27','ipv4','".$last_timestamp."')";
				           echo $sql5."\n";
				           $result5=$mysqli->query($sql5);
                                        }
					if ($result3->num_rows==0){
						//Si tinc el ID per coincidencia de IP, miro les dades del node, ja que no se qui es
	        	                        $sql3="select * from nodes where uid='".$uid."' and timestamp_captura='".$last_timestamp."'";
						//echo $sql3."\n";
        	                                $result3=$mysqli->query($sql3);
						$row3=$result3->fetch_assoc();
					}
					if ($row3["lon"]==""){
						$sql5 = "update nodes set lon='".$variables_node["lon"]."' where uid='".$uid."' and timestamp_captura='".$last_timestamp."'";
					        echo $sql5."\n";
					        //$result5=$mysqli->query($sql5);
					}
				        if ($row3["lat"]==""){
					        $sql5 = "update nodes set lat='".$variables_node["lat"]."' where uid='".$uid."' and timestamp_captura='".$last_timestamp."'";
				              	echo $sql5."\n";
				               	//$result5=$mysqli->query($sql5);
				        }
				        if ($row3["gdev"]=="0"){
					        $sql5 = "update nodes set gdev='".$variables_device["id"]."' where uid='".$uid."' and timestamp_captura='".$last_timestamp."'";
					        echo $sql5."\n";
					        //$result5=$mysqli->query($sql5);
			                }
	                                echo $variables_zona["title"]."\n";
        	                        echo "Nom node: ".$variables_node["title"]."\n"; 
					echo "lat: ".$variables_node["lat"]."\n"; 
					echo "lon: ".$variables_node["lon"]."\n"; 
                                	echo "guifidevice: ".$variables_device["id"]."\n";
					echo "Nom trasto: ".$variables_device["title"]."\n";
        	                        echo $variables_device["mainipv4"]."\n";
                	                echo $variables_device["firmware"]."\n";
                        	        echo "--------------------\n";
				}else{
					$uptime="";
					echo "No hi ha coincidencia\n";
                                        echo $variables_zona["title"]."\n";
                                        echo "Nom node: ".$variables_node["title"]."\n"; 
                                        echo "lat: ".$variables_node["lat"]."\n"; 
                                        echo "lon: ".$variables_node["lon"]."\n"; 
                                        echo "guifidevice: ".$variables_device["id"]."\n";
                                        echo "Nom trasto: ".$variables_device["title"]."\n";
                                        echo $variables_device["mainipv4"]."\n";
                                        echo $variables_device["firmware"]."\n";
			                $session = new SNMP(SNMP::VERSION_1, $variables_device["mainipv4"], "public");
			                $fulltree = $session->walk(".");
			                //print_r($fulltree);
					list($brossa,$uptime)=explode(")",$fulltree["iso.3.6.1.2.1.1.3.0"]);
					$uptime=trim($uptime);
					echo $uptime."\n";
			                $session->close();
					if ($uptime!=""){
					   //Tenim el trasto actiu
					   $host=$variables_device["title"];
					   echo "node nou ".$host."\n";
					   echo hexdec(crc32($host))."\n";
					   $uid=hexdec(crc32($host));
					   $id="";
					   $gwmeshuid="0";
					   $gwinetuid="0";
					   $gdev=$variables_device["id"];
				 	   $name=$host;
					   $system=$variables_device["name"];
					   $lat=$variables_node["lat"];
					   $lon=$variables_node["lon"];
					   $zonaid=11;
					   $sql5="INSERT INTO nodes(uid,id,gwmeshid,gwinetid,gdev,name,system,lon,lat,zona,uptime,timestamp_captura,timestamp_json) VALUES ('$uid', '$id', '".$gwmeshuid."','".$gwinetuid."', '$gdev', '$name', '$system', '$lon', '$lat','".$zonaid."','".$uptime."', '$last_timestamp','".$last_timestamp."')";
	  				   echo $sql5."\n";
					   $mysqli->query($sql5);
				           $sql5="INSERT INTO ip(uid,ip,tipus,timestamp_captura) VALUES ('".$uid."', '".$variables_device["mainipv4"]."/32','ipv4','".$last_timestamp."')";
				           echo $sql5."\n";
				           $result5=$mysqli->query($sql5);
				           $sql5="INSERT INTO ip(uid,ip,tipus,timestamp_captura) VALUES ('".$uid."', '".$variables_device["mainipv4"]."/27','ipv4','".$last_timestamp."')";
				           echo $sql5."\n";
				           $result5=$mysqli->query($sql5);
					}
					echo "--------------------\n";
				}
                        }
                }
        }
	foreach ($zona->zone as $zona_recursiva) {
		$trobats=$trobats+recursive_zone($zona_recursiva,$last_timestamp);
	}
	return($trobats);

}
?>
