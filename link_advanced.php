<?php
include 'functions.php';
date_default_timezone_set('Europe/Madrid');
$mysqli = mysql_link ();

$uid=$_GET["uid"];
$touid=$_GET["touid"];
$canal=$_GET["canal"];
$timestamp=$_GET["timestamp"];
$distancia=$_GET["dist"];
$captures=24;
?>
<html>
<head>
 <style>
  body {
        padding: 00;
        margin: 40;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 0.8em;
  }
  html, body{
        height: 98%;
        width: 98%;
        text-align:center;
  }
</style>
</head>
<body>
<?php
$sql3 = "SELECT * from nodes where timestamp_captura='".$timestamp."' and uid=".$uid." and lat<>'' and lon<>''";
$result3 = $mysqli->query($sql3);
$row3 = $result3->fetch_assoc();
$sql5 = "SELECT * from nodes where timestamp_captura='".$timestamp."' and uid=".$touid." and lat<>'' and lon<>''";	
$result5 = $mysqli->query($sql5);
$row5 = $result5->fetch_assoc();

$sql7 = "SELECT * from adjacencies where nodeuid=".$touid." and nodeTouid=".$uid." and timestamp_captura='".$timestamp."' and canal='".$canal."'";
$result7 = $mysqli->query($sql7);
if ($result7->num_rows>0){
	$row7 = $result7->fetch_assoc();
        $ample7=$row7["ample"];
        $ping7=$row7["ping"];
	$rxrate7=$row7["rxrate"];
	$signal7=$row7["senyal"];
}else{
        $ample7="0";
        $ping7="0";
        $rxrate7="0";
        $signal7="0";
}

$sql6 = "SELECT * from adjacencies where nodeuid=".$uid." and nodeTouid=".$touid." and timestamp_captura='".$timestamp."' and canal='".$canal."'"; 
$result6 = $mysqli->query($sql6);
if ($result6->num_rows>0){
	$row6 = $result6->fetch_assoc();
	$ample6=$row6["ample"];
	$ping6=$row6["ping"];
        $rxrate6=$row6["rxrate"];
        $signal6=$row6["senyal"];
}else{
	$ample6="0";
	$ping6="0";
        $rxrate6="0";
        $signal6="0"; 
}
$html=$html."<h2>An&agrave;lisi del link ".substr($row3['name'],0,-5)."<->".substr($row5['name'],0,-5)."</h2>";
$html=$html."Canal <b>".$canal."</b><br>";        
$html=$html.substr($row3["name"],0,-5)."->".substr($row5["name"],0,-5).": <b>";
$link6="";
if ($ample6!=""){
	$link6=$link6.$ample6."mbps";
}
if ($ping6!=""){
	if ($link6!=""){
		$link6=$link6.", ";
	}
	$link6=$link6.$ping6."ms";
}
if ($signal6!=""){
        if ($link6!=""){
                $link6=$link6.", ";
        }
	$link6=$link6.$signal6."db";
}
if ($rxrate6!=""){
	//$link6=$link6.", ".$rxrate6."/100 rxrate";
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
        $link7=$link7.$ping7."ms";
}
if ($signal7!=""){
        if ($link7!=""){
                $link7=$link7.", ";
        }
        $link7=$link7.$signal7."db";
}
if ($rxrate7!=""){
        //$link7=$link7.", ".$rxrate6."/100 rxrate";
}
$html=$html.$link7."</b><br/>";
$html=$html."Dist&agrave;ncia del link: <b>".round($distancia/1000,2)." km</b><br/><br/>";
if ($canal!="gre"){
	if ($canal!="eth"){
		$freq=($canal*5)+5000;
		$perfil="Perfil desde <b>".substr($row3["name"], 0, -5)." a ".substr($row5["name"], 0, -5)."</b> <br/><a target=\"_blank\" href=\" http://wisp.heywhatsthat.com/api/profile-rf.png?user=guifi&src=guifi.net&axes=1&curvature=0&metric=1&pt0=".$row3["lat"].",".$row3["lon"].",ff0000&freq=".$freq."&pt1=".$row5["lat"].",".$row5["lon"].",00c000\"><img width=\"500px\" src=\"http://wisp.heywhatsthat.com/api/profile-rf.png?user=guifi&src=guifi.net&axes=1&curvature=0&metric=1&pt0=".$row3["lat"].",".$row3["lon"].",ff0000&freq=".$freq."&pt1=".$row5["lat"].",".$row5["lon"].",00c000\"></a><br/><br/>";
	}else{
		$perfil="Perfil desde <b>".substr($row3["name"], 0, -5)." a ".substr($row5["name"], 0, -5)."</b> <br/><a target=\"_blank\" href=\" http://wisp.heywhatsthat.com/api/profile-rf.png?user=guifi&src=guifi.net&axes=1&curvature=0&metric=1&pt0=".$row3["lat"].",".$row3["lon"].",ff0000&pt1=".$row5["lat"].",".$row5["lon"].",00c000\"><img width=\"500px\" src=\"http://wisp.heywhatsthat.com/api/profile-rf.png?user=guifi&src=guifi.net&axes=1&curvature=0&metric=1&pt0=".$row3["lat"].",".$row3["lon"].",ff0000&pt1=".$row5["lat"].",".$row5["lon"].",00c000\"></a><br/><br/>";
	}
}else{	
	$perfil="";
}
$sql="SELECT ample from adjacencies where ample is not null and nodeuid='".$uid."' and nodeTouid='".$touid."' and canal='".$canal."' and timestamp_captura in (select timestamp_captura from (SELECT distinct timestamp_captura from nodes where timestamp_captura <='".$timestamp."' order by timestamp_captura DESC limit ".$captures.") as a order by timestamp_captura asc)";
$result = $mysqli->query($sql);
$speed_graph=false;
if ($result->num_rows){
	$speed_graph=true;
}
if ($speed_graph){
	$speed= "<b>Ample de banda del link les darreres 12h</b><br/><a target=\"_blank\" href=\"./speed_graph.php?uid=".$uid."&touid=".$touid."&canal=".$canal."&timestamp=".$timestamp."&captures=48\"><img width=\"500px\" src=\"./speed_graph.php?uid=".$uid."&touid=".$touid."&canal=".$canal."&timestamp=".$timestamp."&captures=".$captures."\"></a><br/>";
}else{
	$speed= "<b>No es disposen de dades de velocitat d'aquest link.</b><br/>";
}

$sql="SELECT senyal from adjacencies where senyal is not null and nodeuid='".$uid."' and nodeTouid='".$touid."' and canal='".$canal."' and timestamp_captura in (select timestamp_captura from (SELECT distinct timestamp_captura from nodes where timestamp_captura <='".$timestamp."' order by timestamp_captura DESC limit ".$captures.") as a order by timestamp_captura asc)";
$result = $mysqli->query($sql);
$link_graph=false;
if ($result->num_rows){
        $link_graph=true;
}
if ($link_graph){
	$senyal= "<b>Nivell de senyal del link les darreres 12h</b><br/><a target=\"_blank\" href=\"./link_graph.php?uid=".$uid."&touid=".$touid."&canal=".$canal."&timestamp=".$timestamp."&captures=48\"><img width=\"500px\" src=\"./link_graph.php?uid=".$uid."&touid=".$touid."&canal=".$canal."&timestamp=".$timestamp."&captures=".$captures."\"></a><br/>";
}else{
	$senyal= "<b>No es disposen de dades de senyal d'aquest link.</b><br/>";
}
echo $html;
echo $perfil;
echo $speed;
echo $senyal;
?>
</body>
</html>
