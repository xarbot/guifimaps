<?php
	include dirname(__FILE__).'/../functions.php';
	include ('Dijkstra.class.php');
	date_default_timezone_set('Europe/Madrid');
	$mysqli = mysql_link ();
	$timestamp=$_GET["timestamp"];
        //$sql = "SELECT timestamp_captura from nodes order by timestamp_captura DESC";
        //$result = $mysqli->query($sql);
        //$row=$result->fetch_assoc();
        //$timestamp=$row["timestamp_captura"];
	$nom_node=$_GET["name"];
	$mode=$_GET["mode"];

        //Creem els nodes
        $sql = "SELECT * FROM maps.nodes where name like '".$nom_node."' and timestamp_captura like '".$timestamp."'";
        $result = $mysqli->query($sql);
	$row = $result->fetch_assoc();
	$uid_optim=$row["uid"];

	//Creem els nodes
	$sql = "SELECT distinct uid,name FROM maps.nodes where timestamp_captura like '".$timestamp."' order by uid asc";
        $result = $mysqli->query($sql);
	//$trastos=array();
        while($row = $result->fetch_assoc()) {
		$str="\$node".$row['uid']." = new DijkstraNode('".$row['name']."');";
		//$trastos[]=$row["id"];
		eval($str);
	}

	//Afegim les aristes
	//El nivell de senyal ens marca el cost del cami
	//A senyal mes baixa cost mes reduit
        $sql2 = "SELECT * FROM maps.adjacencies a inner join nodes n on n.uid=a.nodeuid and n.timestamp_captura=a.timestamp_captura where n.timestamp_captura like '".$timestamp."'";
        $result2 = $mysqli->query($sql2);
        while($row2 = $result2->fetch_assoc()) {
	        $sql = "SELECT * FROM maps.nodes where uid='".$row2['nodeuid']."' and timestamp_captura like '".$timestamp."'";
        	$result = $mysqli->query($sql);
		$origen = mysqli_num_rows($result);

                $sql = "SELECT * FROM maps.nodes where uid='".$row2['nodeTouid']."' and timestamp_captura like '".$timestamp."'";
                $result = $mysqli->query($sql);
                $desti = mysqli_num_rows($result);
                $best_link="30";
                $worst_link="82";
		//Augmentem en 15 a tots els  links
		//Aixo provocara tot i que en termes absoluts un cami sigui mes curt si te mes salts
		//Que un altre cami es vegi penalitzat
		$hop= "15";
		if ($origen>0&&$desti>0){
			if ((abs($row2['senyal'])!=0)&&(abs($row2['senyal'])<$worst_link)){
				$percent=100/($worst_link-$best_link);
				//Es considera que worst_link es 0% de senyal i que best_link 100% senyal
				//Per tant, per cada db que m'allunyo dels best_link estic perdent percent de senyal
				$pes=round($percent*(abs($row2['senyal'])-$best_link));
				if ($pes<0){
					$pes=20;
				}
				$pes=$pes+$hop;
		        	$str2="\$node".$row2['nodeuid']."->addNeighbour(\$node".$row2['nodeTouid'].",".$pes.",false);";
       	        		eval($str2);
			}else{
				if (round(abs($row2['ample']))>0){
					if (($row2['canal']!="eth")&&($row2['canal']!="gre")){
						$max_link="60";
					}else{
						$max_link="100";
					}
					$pes=100-round(100*(abs($row2['ample'])/$max_link));
					if ($pes<0){
						$pes=20;
					}
					$pes=$pes+$hop;
        	        	        $str2="\$node".$row2['nodeuid']."->addNeighbour(\$node".$row2['nodeTouid'].",".$pes.",false);";
		                        eval($str2);
				}
			}
		}
        }
	//Mirem quines son les sortides predeterminades de malla
	if ($mode=="inet"){
	        $sql = "SELECT distinct uid,name,zona FROM maps.nodes where uid=gwinetid and timestamp_captura like '".$timestamp."' order by zona";
	}else{
		$sql = "SELECT distinct uid,name,zona FROM maps.nodes where uid=gwmeshid and timestamp_captura like '".$timestamp."' order by zona";
	}


        $result = $mysqli->query($sql);
	$gw= array();
        while($row = $result->fetch_assoc()) {
		$gw[]=$row["name"];
        }

	$nodes = DijkstraNode::getNodes();
	$str="\$dijkstra = Dijkstra::findRoute(\$nodes,\$node".$uid_optim.");";
	eval($str);
        $paths = $dijkstra['paths'];
        $pathsCosts = $dijkstra['pathsCosts'];
	if ($mode=="inet"){
	        echo "<b>Ruta de sortida optima cap a Internet per la antena: ".$nom_node."</b><br/><br/>";
	}else{
		echo "<b>Ruta de sortida optima cap a Guifi per la antena: ".$nom_node."</b><br/><br/>";
	}
	$ruta_optima=1000000;
        foreach($paths as $nodeId => $nodePred) {
	  //Si el node desde on sortim es gateway
	  if (in_array($nodeId,$gw)) {
              $pred = $nodePred;
              $cPath = array($nodeId);
              while($pred) {
	              $cPath[] = $pred;
                      $pred = $paths[$pred];
              }
              $cPath = array_reverse($cPath);
	      if ($pathsCosts[$nodeId]!=-1){
		      echo 'Ruta fins a la sortida '.$nodeId.' (Cost total: '.$pathsCosts[$nodeId].') : <br />';
		      if ($pathsCosts[$nodeId]!=-1){
			      $cost=0;
			      foreach ($cPath as $salt){
				$cost=$pathsCosts[$salt]-$cost;
				if ($cost!=0){
					echo " --(C: ".$cost.")-> ";
				}
				echo $salt;
				$cost=$pathsCosts[$salt];
			      }
		      }
		      echo '<br /><br />';
	      }
	      if ($pathsCosts[$nodeId]>0 and $pathsCosts[$nodeId]<$ruta_optima){
		      $ruta_optima=$pathsCosts[$nodeId];	
	              $htmltmp= 'La teva sortida optima de la mesh es '.$nodeId.'<br />';
	      }
	  }
        }
	echo "<b>".$htmltmp."</b><br/>";
?>
