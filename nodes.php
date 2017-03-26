<?php
date_default_timezone_set('Europe/Madrid');
include 'functions.php';
$mysqli = mysql_link ();

$timestamp=$_GET["timestamp"];
$uid=$_GET["uid"];
$touid=$_GET["touid"];
if ($_GET["frecuencies_actives"]!=""){
	$canals_actius=explode("-",$_GET["frecuencies_actives"]);
}else{
	$sql4 = "SELECT distinct canal from adjacencies where timestamp_captura='".$timestamp."' order by canal desc";
	$result4 = $mysqli->query($sql4);
	while($row4 = $result4->fetch_assoc()) {
		if ($row4["canal"]!="0"){
			$canals_actius[]=$row4["canal"];
		}
	}
}

//$sql2 = "SELECT distinct * from nodes n inner join zones z on z.id=n.zona where n.timestamp_captura='".$timestamp."' and lat<>'' and lon<>'' order by uid ASC";
$sql2 = "SELECT distinct * from nodes n inner join zones z on z.id=n.zona where n.timestamp_captura='".$timestamp."' order by uid ASC";
$result2 = $mysqli->query($sql2);
echo "var info = L.control({position: 'bottomright'});\n";
echo "info.onAdd = function (map) {\n";
echo "	this._div = L.DomUtil.create('div', 'info');\n";
echo "	this._div.innerHTML ='<img width=\"30\" src=\"./icon/outinet_icon.png\">&nbsp;<span>Internet gateway</span><br/>';\n";
echo "  this._div.innerHTML +='<img width=\"30\" src=\"./icon/outmesh_icon.png\">&nbsp;<span>Mesh gateway</span><br/>';\n";
echo "  this._div.innerHTML +='<img width=\"30\" src=\"./icon/outinetmesh_icon.png\">&nbsp;<span>Internet and Mesh gateway</span><br/>';\n";
echo "  this._div.innerHTML +='<img width=\"30\" src=\"./icon/mesh_icon.png\">&nbsp;<span>Mesh device</span><br/>';\n";
echo "  this._div.innerHTML +='<img width=\"30\" src=\"./icon/mesh_gw.png\">&nbsp;<span>Mesh path gateway</span><br/>';\n";
echo "  this._div.innerHTML +='<img width=\"30\" src=\"./icon/inet_gw.png\">&nbsp;<span>Internet path gateway</span><br/>';\n";
echo "	return this._div;\n";
echo "};\n";
echo "info.addTo(map);\n";

echo "var meshIcon = L.icon({\n";
echo "    iconUrl: './icon/mesh_icon.png',\n";
echo "    iconSize:     [30, 30], // size of the icon\n";
echo "    iconAnchor:   [15, 33], // point of the icon which will correspond to marker's location\n";
echo "    popupAnchor:  [15, -30] // point from which the popup should open relative to the iconAnchor\n";
echo "});\n";
echo "var outmeshIcon = L.icon({\n";
echo "    iconUrl: './icon/outmesh_icon.png',\n";
echo "    iconSize:     [30, 30], // size of the icon\n";
echo "    iconAnchor:   [18, 30], // point of the icon which will correspond to marker's location\n";
echo "    popupAnchor:  [15, -30] // point from which the popup should open relative to the iconAnchor\n";
echo "});\n";
echo "var outinetIcon = L.icon({\n";
echo "    iconUrl: './icon/outinet_icon.png',\n";
echo "    iconSize:     [30, 30], // size of the icon\n";
echo "    iconAnchor:   [18, 30], // point of the icon which will correspond to marker's location\n";
echo "    popupAnchor:  [15, -30] // point from which the popup should open relative to the iconAnchor\n";
echo "});\n";
echo "var outinetmeshIcon = L.icon({\n";
echo "    iconUrl: './icon/outinetmesh_icon.png',\n";
echo "    iconSize:     [30, 30], // size of the icon\n";
echo "    iconAnchor:   [18, 30], // point of the icon which will correspond to marker's location\n";
echo "    popupAnchor:  [15, -30] // point from which the popup should open relative to the iconAnchor\n";
echo "});\n";

echo "var markers = L.markerClusterGroup({maxClusterRadius: 5},{ disableClusteringAtZoom: 14 });\n";
$sql2 = "SELECT distinct * from nodes n inner join zones z on z.id=n.zona where n.timestamp_captura='".$timestamp."' order by uid ASC";
$result2 = $mysqli->query($sql2);
while($row2 = $result2->fetch_assoc()) {
  $lat=$row2['lat'];
  $lon=$row2['lon'];
  if ($lat==""||$lon==""){
	$sql3="SELECT * from nodes n where n.uid='".$row2['uid']."' and lat<>'' and lon<>'' order by timestamp_captura DESC";
	$result3 = $mysqli->query($sql3);
	$row3 = $result3->fetch_assoc();
        $lat=$row3['lat'];
        $lon=$row3['lon'];
  }
  if ($lat!=""&&$lon!=""){
	if ($row2['uid']==$row2['gwmeshid']&&$row2['uid']!=$row2['gwinetid']){
		echo "var marker".$row2['uid']." = L.marker([".$lat.", ".$lon."],{icon: outmeshIcon}).addTo(map);\n";
	}
        if ($row2['uid']!=$row2['gwmeshid']&&$row2['uid']==$row2['gwinetid']){
                echo "var marker".$row2['uid']." = L.marker([".$lat.", ".$lon."],{icon: outinetIcon}).addTo(map);\n";
        }
        if ($row2['uid']==$row2['gwmeshid']&&$row2['uid']==$row2['gwinetid']){
                echo "var marker".$row2['uid']." = L.marker([".$lat.", ".$lon."],{icon: outinetmeshIcon}).addTo(map);\n";
        }
        if ($row2['uid']!=$row2['gwmeshid']&&$row2['uid']!=$row2['gwinetid']){
                echo "var marker".$row2['uid']." = L.marker([".$lat.", ".$lon."],{icon: meshIcon}).addTo(map);\n";
        }
	echo "marker".$row2['uid'].".on('click', function(e) {document.getElementById(\"uid\").value=\"".$row2['uid']."\";document.getElementById(\"touid\").value=\"\";});\n";
	$html="";
	if ($row2['gdev']!="0"){
		$html=$html."<a target=\"_blank\" href=\"https://guifi.net/ca/guifi/device/".$row2['gdev']."\"><img width=\"50\" src=\"http://dsg.ac.upc.edu/qmpsu/img/guifi.net_logo.gif\"></a>&nbsp;&nbsp;";
	}
	$sql4 = "SELECT distinct canal from adjacencies where timestamp_captura='".$timestamp."' and nodeuid='".$row2['uid']."'";
	$result4 = $mysqli->query($sql4);
	$canals="";
	while($row4 = $result4->fetch_assoc()) {
		$canal=$row4['canal'];
		if ($canal=="0"){
			$canals=$canals."canal desconegut,";
		}else{
			$canals=$canals.$canal.",";
		}
	}
	$html=$html."<a href=\"?service=".$row2['name']."\"><b>".$row2['name']."</b></a><br>Origen: <b><a target=\"_blank\" href=\"".$row2['url']."\">".$row2['zona']."</a></b><br/>";
	if ($row2['system']!=""){
		$html=$html."<b>".$row2['system']."</b><br/>";
	}
	$html=$html."Canal: <b>".rtrim($canals,",")."</b><br/>";

	$sql4 = "select * from ip where uid='".$row2['uid']."' and timestamp_captura='".$timestamp."' and tipus='ipv4'";
	$result4 = $mysqli->query($sql4);
	$ip32="";
	if ($result4->num_rows>0){
		$html=$html."<b>IPv4</b></br>";
		while($row4 = $result4->fetch_assoc()) {
			if (substr($row4['ip'], -2)=="32"){
				$ip32="<a href=\"ssh://root@".substr($row4['ip'], 0, -3)."\">Entra per ssh</a><br/>";
				$ip32=$ip32."<a target=\"_blank\" href=\"http://".substr($row4['ip'], 0, -3)."\">Entra per http</a><br/>";
			}		
			$html=$html.$row4['ip']."<br/>";
		}	
	}
        $sql4 = "select * from ip where uid='".$row2['uid']."' and timestamp_captura='".$timestamp."' and tipus='ipv6gl'";
        $result4 = $mysqli->query($sql4);
	if ($result4->num_rows>0){
	        $html=$html."<b>IPv6gl</b></br>";
        	while($row4 = $result4->fetch_assoc()) {
                	$html=$html.$row4['ip']."<br/>";
	        }
	}
        $sql4 = "select * from ip where uid='".$row2['uid']."' and timestamp_captura='".$timestamp."' and tipus='ipv6ll'";
        $result4 = $mysqli->query($sql4);
	if ($result4->num_rows>0){
	        $html=$html."<b>IPv6ll</b></br>";
        	while($row4 = $result4->fetch_assoc()) {
                	$html=$html.$row4['ip']."<br/>";
	        }
	}
	$html=$html.$ip32;
	if ($row2["uptime"]!=""){
		$html=$html."Uptime: <b>".$row2["uptime"]."</b><br/>";
	}
	$html=$html."Data de la captura: <b>".date('d/m/Y H:i:s', $row2['timestamp_json'])."</b>";;
	echo "marker".$row2['uid'].".bindPopup('".$html."');\n";
	$markers[$h][0]=$row2["uid"];
	$markers[$h][1]=$row2['name'];
	$markers[$h][2]=$html;
	$h++;
	echo "markers.addLayer(marker".$row2['uid'].");\n";
  }
}
echo "map.addLayer(markers);\n";
$sql4 = "SELECT distinct canal from adjacencies where timestamp_captura='".$timestamp."' order by canal desc";
$result4 = $mysqli->query($sql4);
echo "var baseLayers = new Array();\n";
$html="";
$paleta_colors= array ('#F57E00','#e22eba','#11ad34','#f0c20c','#7297e6','#afad18','#2f6c3d','#30b9b3','#0078a8','#6b5e93','#C4942B','#ff4959','#7278a5','#c97f70','#c7e008');
$color_debils="#828180";
$i=0;
echo "var capes = new Array();\n";
while($row4 = $result4->fetch_assoc()) {
	$canal=$row4['canal'];
	echo "capes.push(\"".$canal."\");\n";
	echo "var f".$canal." = new L.layerGroup();\n";
	if (in_array($canal, $canals_actius)) {
		echo "f".$canal.".addTo(map);\n";
	}	
	$color[$canal]=$paleta_colors[$i];
	$i++;
	if ($canal=="0"){
		$html=$html."'<font style=\"color: #000000; background-color: ".$color[$canal]."\">Canal desconegut</font>': f".$canal.",\n";
	}else{
		$html=$html."'<font style=\"color: #000000; background-color: ".$color[$canal]."\">Frec. ".$canal."</font>': f".$canal.",\n";
	}
}
echo "var fdebils = new L.layerGroup();\n";
echo "capes.push(\"debils\");\n";
if (in_array("debils", $canals_actius)) {
   echo "fdebils.addTo(map);\n";
}
$html=$html."'<font style=\"color: #000000; background-color: ".$color_debils."\">Links debils</font>' : fdebils,\n";
echo "var overlayMaps ={\n";
echo $html;
echo "}\n";
$sql4 = "SELECT a.* from adjacencies a where a.timestamp_captura='".$timestamp."' order by nodeuid ASC";
$result4 = $mysqli->query($sql4);
$nodes_pintats=array();
while($row4 = $result4->fetch_assoc()) {
	
	if (!in_array($row4['nodeTouid']."-".$row4['nodeuid'], $nodes_pintats)) {
       	        $canal=$row4['canal'];
		$sql3 = "SELECT * from nodes where timestamp_captura='".$timestamp."' and uid=".$row4['nodeuid']." and lat<>'' and lon<>''";
		$result3 = $mysqli->query($sql3);
		$row3 = $result3->fetch_assoc();
	        $sql5 = "SELECT * from nodes where timestamp_captura='".$timestamp."' and uid=".$row4['nodeTouid']." and lat<>'' and lon<>''";	
        	$result5 = $mysqli->query($sql5);
		$row5 = $result5->fetch_assoc();
        
		if ($result3->num_rows>0&&$result5->num_rows>0){
                        $sql7 = "SELECT * from adjacencies where nodeuid=".$row4['nodeTouid']." and nodeTouid=".$row4['nodeuid']." and timestamp_captura='".$timestamp."' and canal='".$row4['canal']."'";
                        $result7 = $mysqli->query($sql7);
                        if ($result7->num_rows>0){
				$row7 = $result7->fetch_assoc();
                                $ample7=$row7["ample"];
                                $ping7=$row7["ping"];
                                $signal7=$row7["senyal"];
                                $rxrate7=$row7["rxrate"];
                        }else{
                                $ample7="0";
                                $ping7="0";
                                $signal7="";
                                $rxrate7="";
                        }

                        $sql6 = "SELECT * from adjacencies where nodeuid=".$row4['nodeuid']." and nodeTouid=".$row4['nodeTouid']." and timestamp_captura='".$timestamp."' and canal='".$row4['canal']."'"; 
                        $result6 = $mysqli->query($sql6);
			if ($result6->num_rows>0){
				$row6 = $result6->fetch_assoc();
				$ample6=$row6["ample"];
				$ping6=$row6["ping"];
				$signal6=$row6["senyal"];
				$rxrate6=$row6["rxrate"];
			}else{
				$ample6="0";
				$ping6="0";
				$signal6="";
				$rxrate6="";
			}
			if ($canal=="0"){
				$html="<b>Canal desconegut</b><br>";  
			}else{
				$html="Canal <b>".$canal."</b><br>"; 
			}
                        $html=$html.substr($row3["name"],0,-5)."->".substr($row5["name"],0,-5).": <b>";
			$link6="";
			if ($ample6!=""){
				$link6=$link6.$ample6."mbps";
			}
			if ($ping6!=""){
				if ($link6!=""){
					$link6=$link6.", ";
				}
				$link6=$link6.$ping6." ms";
			}
			if ($signal6!=""){
                                if ($link6!=""){
                                        $link6=$link6.", ";
                                }
				$link6=$link6.$signal6."db";
			}
			if ($rxrate6!=""){
				//$link6=$link66.$rxrate6."/100 rxrate";
			}
			$html=$html.$link6."</b><br/>";
                        $html=$html.substr($row5["name"],0,-5)."->".substr($row3["name"],0,-5).": <b>";
                        $link7="";
                        if ($ample7!=""){
                                $link7=$link7.$ample7."mbps";
                        }
                        if ($ping7!=""){
                                if ($link7!=""){
                                        $link7=$link7.", ";
                                }
                                $link7=$link7.$ping7." ms";
                        }
                        if ($signal7!=""){
                                if ($link7!=""){
                                        $link7=$link7.", ";
                                }
                                $link7=$link7.$signal7."db";
                        }
                        if ($rxrate7!=""){
                                //$link7=$link7.$rxrate7."/100 rxrate";
                        }
                        $html=$html.$link7."</b><br/>";


			echo "var distancia".$row4['nodeuid'].$row4['nodeTouid']."=marker".$row4['nodeuid'].".getLatLng().distanceTo(marker".$row4['nodeTouid'].".getLatLng());\n";
        		echo "var latlngs".$row4['nodeuid'].$row4['nodeTouid']." = Array();\n";
			echo "latlngs".$row4['nodeuid'].$row4['nodeTouid'].".push(marker".$row4['nodeuid'].".getLatLng());\n";
			echo "latlngs".$row4['nodeuid'].$row4['nodeTouid'].".push(marker".$row4['nodeTouid'].".getLatLng());\n";
			$link_debil=false;
			if ((($ample6<=1&&$ample7<=1)&&(abs($signal6)>=80||abs($signal7)>=80))||(($ample6<=1&&$ample7<=1)&&($signal6==0||$signal7==0))){
				$link_debil=true;
			}
			if ($link_debil){
				echo "var polyline".$row4['nodeuid'].$row4['nodeTouid']." = L.polyline(latlngs".$row4['nodeuid'].$row4['nodeTouid'].", {color: '".$color_debils."'})\n";
			}else{
				echo "var polyline".$row4['nodeuid'].$row4['nodeTouid']." = L.polyline(latlngs".$row4['nodeuid'].$row4['nodeTouid'].", {color: '".$color[$canal]."'})\n";
			}
			$mesinfo="<a target=\"_blank\" href=\"./link_advanced.php?dist='+eval(distancia".$row4['nodeuid'].$row4['nodeTouid'].")+'&canal=".$row4['canal']."&uid=".$row4['nodeuid']."&touid=".$row4['nodeTouid']."&timestamp=".$timestamp."\">M&eacute;s informaci&oacute;</a><br/>";
			echo "polyline".$row4['nodeuid'].$row4['nodeTouid'].".bindPopup('".$html."Dist&agrave;ncia de l\'enlla&ccedil;: <b>'+(distancia".$row4['nodeuid'].$row4['nodeTouid']."/1000).toFixed(2)+' km</b><br/>".$mesinfo."');\n";
			if ($link_debil){
				echo "polyline".$row4['nodeuid'].$row4['nodeTouid'].".addTo(fdebils);\n";
			}else{
				echo "polyline".$row4['nodeuid'].$row4['nodeTouid'].".addTo(f".$canal.");\n";
			}
			echo "polyline".$row4['nodeuid'].$row4['nodeTouid'].".on('click', function(e) {document.getElementById(\"uid\").value=\"".$row4['nodeuid']."\";document.getElementById(\"touid\").value=\"".$row4['nodeTouid']."\";});\n";
		}
		array_push($nodes_pintats,$row4['nodeuid']."-".$row4['nodeTouid']);
	}
}
//Ara pinto els paths
//Primer inetpaths
foreach ($markers as $marker){
	$markeruid=$marker[0];
	$name=$marker[1];
	$html=$marker[2];
	$pathinet= array();
	$pathcom=array();
	echo "var gwinet".$markeruid." = new L.layerGroup();\n";
	echo "var gwcom".$markeruid." = new L.layerGroup();\n";
        echo "marker".$markeruid.".on(\"popupopen\", function(e) {\n";
		echo "if (document.getElementById(\"rutes\").checked){\n";
		echo "marker".$markeruid.".setPopupContent('".$name."');\n";
                $sql4 = "SELECT * from inet_paths where timestamp_captura='".$timestamp."' and uid='".$markeruid."'";
                $result4 = $mysqli->query($sql4);
                $inetorigen=$markeruid;
                $inetdesti="";
                if ($result4->num_rows>0){
                        $row4 = $result4->fetch_assoc();
                        $inetpath=json_decode($row4["gwpath"]);
                        foreach($inetpath as $inetdesti){
	                        echo "var latlngsgwinet".$inetorigen.$inetdesti." = Array();\n";
        	                echo "latlngsgwinet".$inetorigen.$inetdesti.".push(marker".$inetorigen.".getLatLng());\n";
                	        echo "latlngsgwinet".$inetorigen.$inetdesti.".push(marker".$inetdesti.".getLatLng());\n";
				echo "var polylinegwinet".$inetorigen.$inetdesti." = L.polyline(latlngsgwinet".$inetorigen.$inetdesti.", {color: 'black',weight: '8'})\n";
				echo "polylinegwinet".$inetorigen.$inetdesti.".addTo(gwinet".$markeruid.");\n";
				echo "polylinegwinet".$inetorigen.$inetdesti.".bringToFront();\n";
				$pathinet[]="polylinegwinet".$inetorigen.$inetdesti;
				$inetorigen=$inetdesti;
                        }
			echo "gwinet".$markeruid.".addTo(map);\n";
                }
                $sql4 = "SELECT * from community_paths where timestamp_captura='".$timestamp."' and uid='".$markeruid."'";
                $result4 = $mysqli->query($sql4);
                $inetorigen=$markeruid;
                $inetdesti="";
                if ($result4->num_rows>0){
                        $row4 = $result4->fetch_assoc();
                        $inetpath=json_decode($row4["gwpath"]);
                        foreach($inetpath as $inetdesti){
                                echo "var latlngscminet".$inetorigen.$inetdesti." = Array();\n";
                                echo "latlngscminet".$inetorigen.$inetdesti.".push(marker".$inetorigen.".getLatLng());\n";
                                echo "latlngscminet".$inetorigen.$inetdesti.".push(marker".$inetdesti.".getLatLng());\n";
				echo "var polylinecminet".$inetorigen.$inetdesti." = L.polyline(latlngscminet".$inetorigen.$inetdesti.", {color: 'red',weight: '4'})\n";
				echo "polylinecminet".$inetorigen.$inetdesti.".addTo(gwcom".$markeruid.");\n";
				echo "polylinecminet".$inetorigen.$inetdesti.".bringToFront();\n";
				$pathcom[]="polylinegwinet".$inetorigen.$inetdesti;
                                $inetorigen=$inetdesti;
                        }
			echo "gwcom".$markeruid.".addTo(map);\n";
                }
		echo "};\n";
        echo "});\n";
        echo "marker".$markeruid.".on(\"popupclose\", function(e) {\n";
		echo "marker".$markeruid.".unbindPopup()\n";
		echo "marker".$markeruid.".bindPopup('".$html."');\n";
		echo "if(typeof gwinet".$markeruid." != \"undefined\"){\n";
			echo  "map.removeLayer(gwinet".$markeruid.");\n";
		echo "};\n";
		echo "if(typeof gwcom".$markeruid." != \"undefined\"){\n";
			echo  "map.removeLayer(gwcom".$markeruid.");\n";
		echo "};\n";
        echo "});\n";

}
echo "L.control.layers(baseLayers,overlayMaps).addTo(map);\n";
$bounds=explode(",",$_GET["bounds"]);
echo "map.fitBounds([\n";
echo "[".$bounds[1].", ".$bounds[0]."],\n";
echo "[".$bounds[3].", ".$bounds[2]."]\n";
echo "]);\n";
if (($uid!="")&&($touid!="")){
        $sql3="SELECT * from nodes n where n.uid=".$uid." order by timestamp_captura DESC";
        $result3 = $mysqli->query($sql3);
        $row3 = $result3->fetch_assoc();

        $sql4="SELECT * from nodes n where n.uid=".$touid." order by timestamp_captura DESC";
        $result4 = $mysqli->query($sql4);
        $row4 = $result4->fetch_assoc();

	echo "if (typeof polyline".$uid.$touid." === \"undefined\"){\n";
        echo "     alert(\"El link ".substr($row3["name"],0,-5)." <-> ".substr($row4["name"],0,-5)." no estava disponible en aquesta captura.\");\n";
        echo "}else{\n";
	echo "     polyline".$uid.$touid.".openPopup();\n";
        echo "}\n";
        echo "document.getElementById(\"uid\").value=\"".$uid."\";\n";
        echo "document.getElementById(\"touid\").value=\"".$touid."\";\n";

}
if (($uid!="")&&($touid=="")){
        $sql3="SELECT * from nodes n where n.uid=".$uid." order by timestamp_captura DESC";
        $result3 = $mysqli->query($sql3);
        $row3 = $result3->fetch_assoc();

	echo "if (typeof marker".$uid." === \"undefined\"){\n";
	echo "     alert(\"La antena ".$row3['name']." no ha reportat dades en aquesta captura.\");\n";
	echo "}else{\n";
	echo "        lat=marker".$uid.".getLatLng();\n";
        echo "        marker".$uid.".openPopup();\n";
	echo "        map.setView(lat, 17);\n";

	echo "}\n";
	echo "document.getElementById(\"uid\").value=\"".$uid."\";\n";
	echo "document.getElementById(\"touid\").value=\"\";\n";
}
?>
