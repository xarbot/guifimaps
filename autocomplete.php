<?php
include 'functions.php';
header( 'Content-type: text/html; charset=iso-8859-1' );
$search = $_GET['node_nom'];
$timestamp= $_GET['timestamp'];
if ($search!=""){ 
	date_default_timezone_set('Europe/Madrid');
	$mysqli = mysql_link ();

	$timestamp=$_GET["timestamp"];
	$sql2 = "SELECT distinct name,uid from nodes n where timestamp_captura='".$timestamp."' and n.name like '%".$search."%' order by uid ASC limit 10";
	$result2 = $mysqli->query($sql2);?>
	<ul id="resultat_cerca">
	<?php 
	while ($row2 = mysqli_fetch_array($result2)) {
		$sql3 = "SELECT distinct name,uid from nodes n where uid=".$row2['uid']." and lat<>0 and lon<>0";
        	$result3 = $mysqli->query($sql3);
		if ($result3->num_rows>0){
		?>
		<li onClick="trianode('<?=$row2["uid"]?>','<?=$row2["name"]?>');"><?=$row2["name"]?></li>
	<?php   }
	}?>
	</ul>
<?php }?>
