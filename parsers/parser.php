<?php
include dirname(__FILE__).'/../functions.php';
date_default_timezone_set('Europe/Madrid');
$mysqli = mysql_link ();
$malles = array(
	    array('http://dsg.ac.upc.edu/qmpsu/download_json.php','1'),
	    array('http://dsg.ac.upc.edu/qmpgsf/download_json.php','2'),
	    array('http://dsg.ac.upc.edu/qmpvc/download_json.php','3'), 
	    array('http://dsg.ac.upc.edu/qmprv/download_json.php','4'),  
	    array('http://dsg.ac.upc.edu/qmpp9/download_json.php','5'),
	    array('http://dsg.ac.upc.edu/qmpquesa/download_json.php','6'),
	    array('http://dsg.ac.upc.edu/qmpsa/download_json.php','7'),
	    array('http://dsg.ac.upc.edu/qmpbll/download_json.php','8'),
            array('http://dsg.ac.upc.edu/qmphorta/download_json.php','9'),
	    array('http://dsg.ac.upc.edu/qmpguinardo/download_json.php','12'),
	);
//$json = file_get_contents('http://dsg.ac.upc.edu/qmpquesa/download_json.php');
//$data = json_decode($json, true);
//var_dump($data);
//exit();

$k=0;
$timestamp="";
foreach ($malles as $filename) {
	$json = file_get_contents($filename[0]);   
	//$json = str_replace('uid":', 'uid":'.$k, $json);
	//$json = preg_replace('/uid":(\d+),/', 'uid":'.$k.'$1,', $json);
	$data = json_decode($json, true);
	//var_dump($data);
	//exit();
	$k++;
	$date=str_replace("_","-",$data["date"]);
	list($any,$mes,$dia,$hora,$minuts,$segons)=explode("-",$date);
	$timestamp_json = mktime($hora,$minuts,$segons,$mes,$dia,"20".$any);
	if ($timestamp==""){
		//$date=str_replace("_","-",$data["date"]);
		//list($any,$mes,$dia,$hora,$minuts,$segons)=explode("-",$date);
		//$timestamp = $timestamp_json;
		$timestamp=time();
	}

	$i=0;
	foreach ($data["nodeList"] as $row) {
                $i++;
		//$uid=$k.$row["uid"];
		$id=$row["id"];
		$name=$row["name"];
                $sql2 = "select * from nodes where name='".$name."' and timestamp_captura='".$timestamp."'";
                $result2=$mysqli->query($sql2);
                if ($result2->num_rows>0){
                	$row2 = $result2->fetch_assoc();
                        $uid= $row2["uid"];
                }else{
                   //El node ha estat abans en alguna captura, reutilitzem el ID, pero cal donar-lo d'alta
                   //Agafem sempre el uid mes baix per assegurarnos que agafem, si hi ha, el del Llorens
                   //$sql2 = "select * from nodes where name='".$name."' order by uid asc";
		   //echo $sql2."\n";
                   //$result2=$mysqli->query($sql2);
                   //if ($result2->num_rows>0){
			//$row2 = $result2->fetch_assoc();
			//$uid = $row2["uid"];
                   //}else{
                       //Es node nou
                       $uid=$k."000".$row["uid"];
		       //$uid=hexdec(crc32($name));
                   //}
                }
        	if (array_key_exists('gdev', $row["data"])){
                	$gdev=$row["data"]["gdev"];
		}else{
        	        $gdev="0";
	        }
		if (array_key_exists('system', $row["data"])){
			$system=$row["data"]["system"];
		}else{
			$system="";
		}
                if (array_key_exists('gwuid', $row["data"])){
                        $gwinetuid=$k."000".$row["data"]["gwuid"];
                }else{
                        $gwinetuid="0";
                }
                if (array_key_exists('community_gwuid', $row["data"])){
                        $gwmeshuid=$k."000".$row["data"]["community_gwuid"];
                }else{
                        $gwmeshuid="0";
                }
                if (array_key_exists('qmpversion', $row["data"])){
                        $qmpversion=$row["data"]["qmpversion"];
                }else{
                        $qmpversion="";
                }
		if (array_key_exists('lon', $row)){
			$lon=ltrim($row["lon"],'0');
		}else{
			$lon="";
		}
       		if (array_key_exists('lat', $row)){
			$lat=ltrim($row["lat"],'0');
		}else{
			$lat="";
		}
		$gwpath_uid= array();
                if (array_key_exists('gwpath_uid', $row["data"])){
                        $tmp=$row["data"]["gwpath_uid"];
			foreach ($tmp as $tmpid){
				$gwpath_uid[]=$k."000".strval($tmpid);
			}
                }
		$community_gwpath_uid= array();
                if (array_key_exists('community_gwpath_uid', $row["data"])){
                        $tmp=$row["data"]["community_gwpath_uid"];
                        foreach ($tmp as $tmpid){
                                $community_gwpath_uid[]=$k."000".strval($tmpid);
                        }
                }  
		if ($uid==""){
			$uid=hexdec(crc32($row["name"]));
		}
		$sql="INSERT INTO nodes(uid,id,gwmeshid,gwinetid,gdev,name,system,qmpversion,lon,lat,zona,timestamp_captura,timestamp_json) VALUES ('$uid', '$id', '".$gwmeshuid."','".$gwinetuid."', '$gdev', '$name', '$system','$qmpversion' , '$lon', '$lat','".$filename[1]."', '$timestamp','".$timestamp_json."')";
		$mysqli->query($sql);
		if (count($gwpath_uid)>0){
			$sqltmp="insert into inet_paths(uid,gwpath,timestamp_captura) values ('".$uid."','".json_encode($gwpath_uid,JSON_FORCE_OBJECT)."','".$timestamp."')";
			$mysqli->query($sqltmp);
		}
                if (count($community_gwpath_uid)>0){
                        $sqltmp="insert into community_paths(uid,gwpath,timestamp_captura) values ('".$uid."','".json_encode($community_gwpath_uid,JSON_FORCE_OBJECT)."','".$timestamp."')";
                        $mysqli->query($sqltmp);
                }

		if (array_key_exists('ipv4', $row["data"])){
			foreach ($row["data"]["ipv4"] as $ip) {
				$tipus="ipv4";
				$sql2 = "INSERT INTO ip(uid,ip,tipus,timestamp_captura) VALUES ('$uid', '$ip', '$tipus', '$timestamp')";
				echo $sql2."\n";
				$mysqli->query($sql2);
			}
		}
	        if (array_key_exists('ipv6gl', $row["data"])){
		        foreach ($row["data"]["ipv6gl"] as $ip) {
               			$tipus="ipv6gl";
	                	$sql2 = "INSERT INTO ip(uid,ip,tipus,timestamp_captura) VALUES ('$uid', '$ip', '$tipus', '$timestamp')";
        		        $mysqli->query($sql2);
	        	}	
		}
       		if (array_key_exists('ipv6ll', $row["data"])){
        		foreach ($row["data"]["ipv6ll"] as $ip) {
                		$tipus="ipv6ll";
		                $sql2 = "INSERT INTO ip(uid,ip,tipus,timestamp_captura) VALUES ('$uid', '$ip', '$tipus', '$timestamp')";
       			        $mysqli->query($sql2);
		        }
		}
		if (array_key_exists('adjacencies', $row)){
			foreach ($row["adjacencies"] as $vei) {
				$uid_vei=$k."000".$vei["nodeTouid"];
				$id_vei=$k."000".$vei["nodeTo"];
				$canal=$vei["data"]["channel"];
				$power=$vei["data"]["powerav"];
				if ($canal=="?"){
					$canal="eth";
				}
				if ($canal=="gre-bcnllngudc"||$canal=="gre-bcnpale"){
					$canal="gre";
					$power=0;
				}
        	                if ($canal=="br-lan"||$canal=="lan_12"||$canal=="mesh_e0_12"||$canal=="mesh_e1_12"||$canal=="ptp_e0"||$canal=="ptp_e1"){
                	                $canal="eth";
					$power=0;
                                }
				if (array_key_exists('rtt', $vei["data"])){
					$ping=$vei["data"]["rtt"];
				}else{
					$ping=0;
				}
				if (array_key_exists('bw', $vei["data"])){
					$ample=$vei["data"]["bw"];
				}else{
					$ample=0;
				}
				if (!excluded_channel($canal)){
					$sql3="INSERT INTO adjacencies(nodeuid,node,nodeTouid,nodeTo,canal,ping,ample,senyal,timestamp_captura) VALUES ('$uid','$id','$uid_vei', '$id_vei', '$canal', '$ping', '$ample','$power', '$timestamp')";       
					echo $sql3."\n";					
			        	$mysqli->query($sql3);
				}
			}	
		}
	}
}
$mysqli->close();
excluded_links($timestamp);
update_gateways($timestamp)
?>

