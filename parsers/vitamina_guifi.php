<?php
	include dirname(__FILE__).'/../functions.php';
	date_default_timezone_set('UTC');
	$mysqli = mysql_link ();

        $sql = "SELECT timestamp_captura from nodes order by timestamp_captura DESC";
        $result = $mysqli->query($sql);
        $row=$result->fetch_assoc();
        $last_timestamp=$row["timestamp_captura"];
        $data_cerca=date('Y-m-d', strtotime('-2 day', time()));
        $url_map='http://libremap.guifi.net/api/routers_by_mtime?startkey="'.$data_cerca.'"';
        $json = file_get_contents($url_map);  

	$data = json_decode($json, true);
	$nodes= array();
	foreach ($data["rows"] as $row) {
                $json2 = file_get_contents('http://libremap.guifi.net/api/router/'.$row["id"]);   
                $data2 = json_decode($json2, true);
		$nodes[$row["id"]]=$data2;
		if ($data2["community"]=="qMp.cat"){
			if ($data2["lat"]!="0"){
				$lat=$data2["lat"];
				if ($lat<1){
					$lat="";
				}
			}else{
				$lat="";
			}
			if ($data2["lon"]!="0"){
				$lon=$data2["lon"];
				if ($lat<1){
					$lat="";
				}
			}else{
				$lon="";
			}
			if (array_key_exists('hostname', $data2)){
                                $host=$data2["hostname"];
                        }else{
                                $host="";
                        }
			$uid="";
			//Primer busquem el node al qual fer el update
			if ($host!=""){
	                        $sql2 = "select * from nodes where name='".$host."' and timestamp_captura='".$last_timestamp."'";
        	                $result2=$mysqli->query($sql2);
                	        if ($result2->num_rows>0){
	                	        $row2 = $result2->fetch_assoc();
                                	$uid= $row2["uid"];
	                        }
			}
 			if ($uid==""){
				if (array_key_exists('aliases', $data2)){
	        			foreach ($data2["aliases"] as $alias) {
						if ($uid==""){
							$ip=strtolower($alias['alias']);
	                		                $sql2 = "select * from ip where ip='".$ip."' and timestamp_captura='".$last_timestamp."'";
        	                		        $result2=$mysqli->query($sql2);
							if ($result2->num_rows>0){
								$row2 = $result2->fetch_assoc();
								$uid= $row2["uid"];
							}
						}
					}
				}
			}
 			if ($uid==""){
				if (array_key_exists('links', $data2)){
					foreach ($data2["links"] as $link) {
						if ($uid==""){
							$ip=strtolower($link['alias_local']);
                        		                $sql2 = "select * from ip where ip='".$ip."' and timestamp_captura='".$last_timestamp."'";
                                		        $result2=$mysqli->query($sql2);
		                                	if ($result2->num_rows>0){
		                	                        $row2 = $result2->fetch_assoc();
        		                	                $uid= $row2["uid"];
	        	                                }
						}
			        	}		
				}
			}
			//Un cop tenim el node mirem d'enriquirlo
			if (($uid!="")&&($lat!="")&&($lon!="")){
				echo "enriquim ".$host."-".$row["id"]."\n";
                                $sql2 = "select * from nodes where uid='".$uid."' and timestamp_captura='".$last_timestamp."'";
                                $result2=$mysqli->query($sql2);
                                if ($result2->num_rows>0){
					$row2 = $result2->fetch_assoc();
					if (($row2['lat']=="")&&($row2['lon']=="")){
						//Si el script del Llorens no tenia lat i lon fem el update amb les dades que ens venen de libremap
		                                $sql2 = "update nodes set lat='".$lat."',lon='".$lon."' where uid='".$uid."' and timestamp_captura='".$last_timestamp."'";
						echo $sql2."\n";
		                                $result2=$mysqli->query($sql2);
					}
				}
			}
                        if (array_key_exists('aliases', $data2)&&$uid!=""){
                                foreach ($data2["aliases"] as $alias) {
                                     $ip=strtolower($alias['alias']);
				     if ($alias['type']=="wifi"){
                                	     $sql2 = "insert into ip(uid,ip,tipus,timestamp_captura) values ('".$uid."','".$ip."','mac','".$last_timestamp."')";
        	                             $result2=$mysqli->query($sql2);
				     }
                                }
                        }
                        if (array_key_exists('links', $data2)&&$uid!=""){
                                foreach ($data2["links"] as $link) {
				     $ip=strtolower($link['alias_local']);
				     if ($link['type']=="wifi"){
                                   	     $sql2 = "insert into ip(uid,ip,tipus,timestamp_captura) values ('".$uid."','".$ip."','mac','".$last_timestamp."')";
                                             $result2=$mysqli->query($sql2);
				     }

                                }
                        }
		}
	}
	exit();
	echo "----------------------------------------\n";
	foreach ($data["rows"] as $row) {
		$data2=$nodes[$row["id"]];
                if ($data2["community"]=="qMp.cat"){	
			if (array_key_exists('links', $data2)){
	                        if (array_key_exists('hostname', $data2)){
        	                        $host=$data2["hostname"];
                	        }else{
                        	        $host="";
	                        }
	                        foreach ($data2["links"] as $link) {
       	                                switch ($link['type']){
               	                                case "bmx6":
							$uid="";
							$touid="";
        	                                        $ip=strtolower($link['alias_local']);
	        	                                $sql2 = "select * from ip where ip='".$ip."' and timestamp_captura='".$last_timestamp."'";
                        	                        $result2=$mysqli->query($sql2);
                                	                        if ($result2->num_rows>0){
                                        	                $row2 = $result2->fetch_assoc();
                                                	        $uid= $row2["uid"];
	                                                }
	                                                $ip=strtolower($link['alias_remote']);
        	                                        $sql2 = "select * from ip where ip='".$ip."' and timestamp_captura='".$last_timestamp."'";
                	                                $result2=$mysqli->query($sql2);
                        	                                if ($result2->num_rows>0){
                                	                        $row2 = $result2->fetch_assoc();
                                        	                $touid= $row2["uid"];
                                                	}
							if (($uid!="")&&($touid!="")){
				                                echo "actualitzem links".$host."-".$row["id"]."\n";
								$sql2 = "update adjacencies set rxrate='".$link['attributes']['rxRate']."' where nodeTouid='".$touid."' and nodeuid='".$uid."' and timestamp_captura='".$last_timestamp."'";
                	               	                        $result2=$mysqli->query($sql2);
							}
                                       	                break;
	                                        case "wifi":
                                                        $uid="";
                                                        $touid="";
                                                        $ip=strtolower($link['alias_local']);
                                                        $sql2 = "select * from ip where ip='".$ip."' and timestamp_captura='".$last_timestamp."'";
                                                        $result2=$mysqli->query($sql2);
                                                                if ($result2->num_rows>0){
                                                                $row2 = $result2->fetch_assoc();
                                                                $uid= $row2["uid"];
                                                        }
                                                        $ip=strtolower($link['alias_remote']);
                                                        $sql2 = "select * from ip where ip='".$ip."' and timestamp_captura='".$last_timestamp."'";
                                                        $result2=$mysqli->query($sql2);
                                                                if ($result2->num_rows>0){
                                                                $row2 = $result2->fetch_assoc();
                                                                $touid= $row2["uid"];
                                                        }
                                                        if (($uid!="")&&($touid!="")){
				                                echo "actualitzem links".$host."-".$row["id"]."\n";
								$sql2 = "update adjacencies set senyal='".$link['attributes']['signal']."' where nodeTouid='".$touid."' and nodeuid='".$uid."' and timestamp_captura='".$last_timestamp."'";
                	                                       	$result2=$mysqli->query($sql2);
							}
                                                        break;
       	                                }
				}
       	               } 
		}
	}
?>

