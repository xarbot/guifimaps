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
        $data_cerca=date('Y-m-d', strtotime('-2 day', time()));
        $url_map='http://libremap.net/api/routers_by_mtime?startkey="'.$data_cerca.'"';
	//echo $url_map."\n";
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
				if ($lon<1){
					$lon="";
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
			$node_antic=false;
			$system="";
			$zona="";
			//Primer busquem si el node no ha estat donat d'alta abans per algun parser anterior
			if ($host!=""){
	                        $sql2 = "select * from nodes where name='".$host."' and timestamp_captura='".$last_timestamp."'";
        	                $result2=$mysqli->query($sql2);
                	        if ($result2->num_rows>0){
	                	        $row2 = $result2->fetch_assoc();
                                	$uid= $row2["uid"];
	                        }else{
					//El node ha estat abans en alguna captura, reutilitzem el ID, pero cal donar-lo d'alta
					//Agafem sempre el uid mes baix per assegurarnos que agafem, si hi ha, el del Llorens
	                                $sql2 = "select * from nodes where name='".$host."' order by uid asc";
        	                        $result2=$mysqli->query($sql2);
                	                if ($result2->num_rows>0){
	                                        $row2 = $result2->fetch_assoc();
	                                        $uid= $row2["uid"];
						$zona= $row2["zona"];
						$system=$row2["system"];
						$host=$row2["name"];
						$node_antic=true;
					}else{
						//Es node nou
						$uid="";
					}
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
			if (($uid=="")&&($lat!="")&&($lon!="")||($node_antic)){
				//Es node nou que el script del Llorens no rastreja
				//I a mes es un node interessant perque tenim dades
				echo "node nou ".$host."\n";
				echo hexdec(crc32($host))."\n";
				//var_dump($data2);
				//2017-02-04T21:45:33.030Z
				list($dia,$hora)=explode("T",$data2["mtime"]);
				list($any,$mes,$dia)=explode("-",$dia);
				list($hora,$minuts,$segons)=explode(":",$hora);
				$timestamp_json=mktime($hora,$minuts,0,$mes,$dia,$any);
				//echo "lat: ".$lat."\n";
				//echo "lon: ".$lon."\n";
				if ($uid==""){
					$uid=hexdec(crc32($host));
				}
				$id="";
				$gwmeshuid="0";
				$gwinetuid="0";
				$gdev="0";
				$name=$host;
				if ($zona==""){
					$zona=10;
				}
				$sql="INSERT INTO nodes(uid,id,gwmeshid,gwinetid,gdev,name,system,lon,lat,zona,timestamp_captura,timestamp_json) VALUES ('$uid', '$id', '".$gwmeshuid."','".$gwinetuid."', '$gdev', '$name', '$system', '$lon', '$lat','".$zona."', '$last_timestamp','".$timestamp_json."')";
				echo $sql."\n";;
				$mysqli->query($sql);
                		$sql9="update nodes set zona='".$zona."' where uid='".$uid."'";
		                $mysqli->query($sql9);

                        	if (array_key_exists('aliases', $data2)&&$uid!=""){
                                	foreach ($data2["aliases"] as $alias) {
	                                     $ip=strtolower($alias['alias']);
					     if ($alias['type']=="wifi"){
		      	                	     $sql2 = "insert into ip(uid,ip,tipus,timestamp_captura) values ('".$uid."','".$ip."','mac','".$last_timestamp."')";
      	        		                     $result2=$mysqli->query($sql2);
					     }
                                             if ($alias['type']=="bmx6"){
		                                     $sql2 = "insert into ip(uid,ip,tipus,timestamp_captura) values ('".$uid."','".$ip."','ipv6ll','".$last_timestamp."')";
                		                     $result2=$mysqli->query($sql2);
                                             }
                                             if ($alias['type']=="oslr"){
		                                     $sql2 = "insert into ip(uid,ip,tipus,timestamp_captura) values ('".$uid."','".$ip."','ipv4','".$last_timestamp."')";
                		                     $result2=$mysqli->query($sql2);
                                             }
						//echo $sql2."\n";
                	                }
                        	}
	                        if (array_key_exists('links', $data2)&&$uid!=""){
        	                        foreach ($data2["links"] as $link) {
					     $ip=strtolower($link['alias_local']);
					     if ($link['type']=="wifi"){
		                    	   	     $sql2 = "insert into ip(uid,ip,tipus,timestamp_captura) values ('".$uid."','".$ip."','mac','".$last_timestamp."')";
                		                     $result2=$mysqli->query($sql2);
					     }
                                             if ($link['type']=="bmx6"){
				                     $sql2 = "insert into ip(uid,ip,tipus,timestamp_captura) values ('".$uid."','".$ip."','ipv6ll','".$last_timestamp."')";
                    				     $result2=$mysqli->query($sql2);
                                             }
                                             if ($link['type']=="oslr"){
		                                     $sql2 = "insert into ip(uid,ip,tipus,timestamp_captura) values ('".$uid."','".$ip."','ipv4','".$last_timestamp."')";
                		                     $result2=$mysqli->query($sql2);
                                             }
					    //echo $sql2."\n";

        	                        }
                	        }

				echo "------------------------------------------------------------\n";
			}
		}
	}
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
				                                //echo "actualitzem links".$host."-".$row["id"]."\n";
								$sql2="select * from adjacencies where nodeTouid='".$touid."' and nodeuid='".$uid."' and timestamp_captura='".$last_timestamp."'";
								$result2=$mysqli->query($sql2);
								if ($result2->num_rows>0){
									$sql2 = "update adjacencies set rxrate='".$link['attributes']['rxRate']."' where nodeTouid='".$touid."' and nodeuid='".$uid."' and timestamp_captura='".$last_timestamp."'";
								}else{
									$sql2="INSERT INTO adjacencies(nodeuid,nodeTouid,rxrate,timestamp_captura) VALUES ('$uid','$touid','$rxrate', '$last_timestamp')"; 
								}
								//echo $sql2."\n";
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
				                                //echo "actualitzem links".$host."-".$row["id"]."\n";
								$canal=$link['attributes']['channel'];
								$senyal=$link['attributes']['signal'];
								$sql2="select * from adjacencies where canal='".$canal."' and nodeTouid='".$touid."' and nodeuid='".$uid."' and timestamp_captura='".$last_timestamp."'";
					   			$result2=$mysqli->query($sql2);
					   			if ($result2->num_rows>0){
										$sql2 = "update adjacencies set senyal='".$senyal."' where canal='".$canal."' and nodeTouid='".$touid."' and nodeuid='".$uid."' and timestamp_captura='".$last_timestamp."'";
					                        }else{
									//Mirem si tenim el link com canal desconegut
									$sql3="select * from adjacencies where canal='0' and nodeTouid='".$touid."' and nodeuid='".$uid."' and timestamp_captura='".$last_timestamp."'";	
									$result3=$mysqli->query($sql3);
    			                                                if ($result3->num_rows>0){
										$sql2 = "update adjacencies set canal='".$canal."', senyal='".$senyal."' where nodeTouid='".$touid."' and nodeuid='".$uid."' and timestamp_captura='".$last_timestamp."'";
									}else{
										$sql2="INSERT INTO adjacencies(nodeuid,nodeTouid,canal,senyal,timestamp_captura) VALUES ('$uid','$touid','$canal','$senyal', '$last_timestamp')"; 
									}
					                        }
								echo $sql2."\n";
					                        $result2=$mysqli->query($sql2);
							}
                                                        break;
       	                                }
				}
       	               } 
		}
	}
	$sql2="delete from adjacencies where canal='0'";
	echo $sql2."\n";
	$result2=$mysqli->query($sql2);

?>

