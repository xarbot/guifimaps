<?php
include 'functions.php';
$mysqli = mysql_link ();
?>
<!DOCTYPE html><html>
<meta charset="utf-8" />
<head>
	<script src="https://unpkg.com/leaflet@1.0.2/dist/leaflet.js"></script>
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.2/dist/leaflet.css" />
	<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.0.0/dist/MarkerCluster.css" />
	<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.0.0/dist/MarkerCluster.Default.css" />
	<script src="https://unpkg.com/leaflet.markercluster@1.0.0/dist/leaflet.markercluster-src.js"></script>
	<link rel="stylesheet" href="http://code.jquery.com/ui/1.12.1/themes/flick/jquery-ui.css">
	<script src="http://code.jquery.com/jquery-3.1.1.min.js"></script>
	<script src="http://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

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
	}
  #map {
        height: 93%;
        width: 100%;
    	left: 20px;
	z-index:10 !important;
  }
  #formulari{
    width: 15%;
    margin-left:0px;
    display:none;
  }
  #taula{
    width: 100%;
    margin-left:20px;
    margin-bottom: 8px;
  }
  .info {
	padding:10px;
	background-color: #d2dadb;
	border-radius: 10px;
  }
  .ui-datepicker {font-size:11px;}
  #resultat_cerca{float:left;list-style:none;margin:0;padding:0;width:190px;}
  #resultat_cerca li{padding: 10px; background:#FAFAFA;border-bottom:#F0F0F0 1px solid;}
  #resultat_cerca li:hover{background:#F0F0F0;}
  #service{
	padding: 3px;
	border: #F0F0F0 1px solid;
  }
  #suggestions{
	position: absolute; 
	cursor: default;
	z-index:30 !important;
	background-color: white;
	margin-left:100px;
  }
.modalDialog {
	position: fixed;
	font-family: Arial, Helvetica, sans-serif;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	z-index: 99999;
	opacity:0;
	-webkit-transition: opacity 400ms ease-in;
	-moz-transition: opacity 400ms ease-in;
	transition: opacity 400ms ease-in;
	pointer-events: none;
}
.modalDialog:target {
	opacity:1;
	pointer-events: auto;
}

.modalDialog > div {
	width: 70%;
	position: relative;
	margin: 55px auto;
	padding: 5px 20px 13px 20px;
	border-radius: 5px;
	background: #cccccc;
}
.close {
	background: #606061;
	color: #FFFFFF;
	line-height: 25px;
	position: absolute;
	right: -12px;
	text-align: center;
	top: -10px;
	width: 24px;
	text-decoration: none;
	font-weight: bold;
	-webkit-border-radius: 12px;
	-moz-border-radius: 12px;
	border-radius: 12px;
	-moz-box-shadow: 1px 1px 3px #000;
	-webkit-box-shadow: 1px 1px 3px #000;
	box-shadow: 1px 1px 3px #000;
}

.close:hover { background: #00d9ff; }
 </style>
 
 </head>
  <body>
     <table id='taula'>
	<tr><td>
	<div id="formulari">
		<div id="datepicker"></div>
		<div id="datos">
		<p>
		<form  onsubmit="getposicioactual()" action="index.php" method="get">
    			<label for='fecha'>Data:</label>
    			<select name="timestamp"  id="triacaptura"></select>
			<input type="hidden" name="frecuencies_actives" id="frecuencies_actives">
                        <input type="hidden" name="bounds" id="bounds">
			<input type="hidden" name="uid" id="uid">
			<input type="hidden" name="touid" id="touid">
    			<input type="submit" value="Enviar" id="submit" disabled>
		</form>
		</p>
		</div>
        </div>
	    <div>
        <input type='button' id='hideshow' value='Veure altres captures'>
    </div>
	</td>
	<td valign='middle'>
		<div class="frmSearch">
	                <form>
        	           Cerca un node: <input type="text" size="50" id="service" name="service" class="search-box"/>
                	   <div id="suggestions"></div>
	                </form>
		</div>
	<td>
<?php 
date_default_timezone_set('UTC');
if(isset($_GET["bounds"])){
        $bounds=$_GET["bounds"];
}else{
	$bounds="1.915397644042969,41.26103341431304,2.3373413085937504,41.4689718711182";
}
if(isset($_GET["uid"])){
        $uid=$_GET["uid"];
}else{
        $uid="";
}
if(isset($_GET["touid"])){
        $touid=$_GET["touid"];
}else{
        $touid="";
}
if(isset($_GET["frecuencies_actives"])){
        $frecuencies_actives=$_GET["frecuencies_actives"];
}else{
        $frecuencies_actives="";
}
if(isset($_GET["timestamp"])){
        $timestamp=$_GET["timestamp"];
}else{
        $sql = "SELECT timestamp_captura from nodes order by timestamp_captura DESC";
        $result = $mysqli->query($sql);
        $row=$result->fetch_assoc();
        $timestamp=$row["timestamp_captura"];
}
if(isset($_GET["service"])){
        $sql2 = "SELECT distinct name,uid from nodes n where timestamp_captura='".$timestamp."' and n.name like '".$_GET['service']."%'";
        $result2 = $mysqli->query($sql2);
	if ($result2->num_rows>0){
		$row2 = mysqli_fetch_array($result2);
	        $uid=$row2["uid"];
	}else{	
		echo "<script>";
		echo "	alert(\"El trasto ".$_GET["service"]." no estava disponible en aquesta captura.\");\n";	
		echo "</script>";
		$uid="";
	}
}else{
        $uid="";
}

	echo "<td align='right' valign='middle'>Data de la captura: &nbsp;".date('d/m/Y H:i:s', $timestamp)."</td>";
	echo "<td align='right'><a href=\"#openModal\"><img width=\"30px\" src=\"./alert_icon.png\"></a></td>";?>
        <td align='right'>
                        <a href="./index.php"><img width="30px" src="home.png"></a>
        </td></tr></table>
	<div id="openModal" class="modalDialog">
		<div>
			<a href="#close" title="Tancar" class="close">X</a>
			<h3><u>Incid&egrave;ncies detectades</u></h3>
			<table><tr><td valign="top" width="50%">
			<?php
			$sql5 = "SELECT distinct * from nodes n inner join zones z on z.id=n.zona where n.timestamp_captura='".$timestamp."' and lat='' and lon='' order by z.id,n.uid ASC";
			$result5 = $mysqli->query($sql5);
			$zona="";
			echo "<h3>Falta geolocalitzaci&oacute;</h3>";
			echo "<ul>";
			while($row5 = $result5->fetch_assoc()) {
				if ($zona!=$row5["id"]){
					if ($zona==""){
						echo "<li>Mesh de ".$row5['zona']."</li>";
						echo "<ul>";
					}else{
						echo "</ul>";
						echo "<li>Mesh de ".$row5['zona']."</li>";
						echo "<ul>";
					}
					$zona=$row5["id"];
				}
        			$sql3="SELECT * from nodes n where n.uid=".$row5['uid']." and lat<>'' and lon<>'' order by timestamp_captura DESC";
        			$result3 = $mysqli->query($sql3);
        			$row3 = $result3->fetch_assoc();
				if ($result3->num_rows==0){
					echo "<li>".$row5["name"]."</li>";
				}
			}
			echo "</ul></ul>";
			?>
			</td>
			<td valign="top" width="50%">
                        <?php
			$sql5="	select distinct n4.uid,n4.name,n4.zona as id,z.zona 
			       	from nodes n4 inner join zones z on z.id=n4.zona 
				where z.zona not like 'Libremap' and n4.uid in (
					select t72.uid from
						(select distinct n.uid as uid from nodes n inner join 
							(select distinct timestamp_captura from nodes n order by timestamp_captura desc limit 72) as t on 
						 n.timestamp_captura=t.timestamp_captura) as t72
					left outer join 
						(select distinct n.uid as uid from nodes n inner join 
							(select distinct timestamp_captura from nodes n order by timestamp_captura desc limit 2) as t on
						 n.timestamp_captura=t.timestamp_captura) as t2
					on t72.uid=t2.uid where t2.uid is null) 
				order by id,uid,name";
                        $result5 = $mysqli->query($sql5);
                        $zona="";
			$uidnoreport="";
                        echo "<!--<h3>No reporten desde fa m&eacute;s de 2 hores</h3>";
                        echo "<ul>";
                        while($row5 = $result5->fetch_assoc()) {
			    if ($uidnoreport!=$row5["uid"]){
				$uidnoreport=$row5["uid"];
                                if ($zona!=$row5["id"]){
                                        if ($zona==""){
                                                echo "<li>Mesh de ".$row5['zona']."</li>";
                                                echo "<ul>";
                                        }else{
                                                echo "</ul>";
                                                echo "<li>Mesh de ".$row5['zona']."</li>";
                                                echo "<ul>";
                                        }
                                        $zona=$row5["id"];
                                }
				$sql8="select * from nodes where nodes.uid='".$row5['uid']."' order by timestamp_captura desc";
				$result8 = $mysqli->query($sql8);
				$row8 = $result8->fetch_assoc();
                                echo "<li><a href='./index.php?uid=".$row5['uid']."&timestamp=".$row8['timestamp_captura']."'>".$row5["name"]." (".date('d/m/Y H:i:s', $row8['timestamp_json']).")</a></li>";
			    }
                        }
                        echo "</ul></ul>-->";
                        ?>
			</td></tr>
			<tr>
				<td colspan="2"><font size="1">Nota: Dades referides a la darrera captura</font></td>
			</tr>
			</table>
		</div>
	</div>
	<div id="map"></div>
 <script>
map = new L.Map('map');	// create the tile layer with correct attribution
var osmUrl='http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
var osmAttrib='Map data © <a href="http://openstreetmap.org">OpenStreetMap</a> contributors';
var osm = new L.TileLayer(osmUrl, {minZoom: 2, maxZoom: 19, attribution: osmAttrib}); 
map.addLayer(osm);
L.control.scale().addTo(map);
</script>
<?php echo "<script src=\"./nodes.php?weather=".$weather."&uid=".$uid."&touid=".$touid."&frecuencies_actives=".$frecuencies_actives."&bounds=".$bounds."&timestamp=".$timestamp."\"></script>\n";?>
<script>
var onlyThisDates= new Array();
$(document).ready(function(){
    	$('#service').keyup(function(evt){
		if (evt.keyCode != 27) {
        		$.ajax({
		            type: "GET",
        		    url: "autocomplete.php",
	        	    data: 'timestamp=<?=$timestamp?>&node_nom='+$(this).val(),
	        	    success: function(data) {
				if (data!=""){
					$("#suggestions").show();
					$("#suggestions").html(data);
					$("#service").css("background","#FFF");
				}else{
					$("#suggestions").hide();
				}
			    }
        		});
		}else{
			document.getElementById("service").value="";
			$("#suggestions").hide();
		}
	});
        jQuery('#hideshow').on('click', function(event) {        
             jQuery('#formulari').toggle('show');
        });
     	$.ajax({
	    type: "GET",
            contentType: "application/json; charset=utf-8",
            url: "timestamp.php",
            data: "{}",
	    async: false,
            dataType: "json",
            success: function (data) {
		for (elem in data) {
   			onlyThisDates.push(data[elem]);
		}
            },
            error: function (result) {
                alert("Error");
            }
        });
	$( "#datepicker" ).datepicker({
    		dateFormat: "dd/mm/yy",
    		firstDay: 1,
		monthNames: ['Gener', 'Febrer', 'Mar&ccedil;', 'Abril', 'Maig', 'Juny','Juliol', 'Agost', 'Setembre', 'Octubre', 'Novembre', 'Desembre'],
    		monthNamesShort: ['Gen', 'Feb', 'Mar&ccedil;', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Des'],
    		dayNames: ['Diumenge', 'Dilluns', 'Dimarts', 'Dimecres', 'Dijous', 'Divendres', 'Dissabte'],
    		dayNamesShort: ['Diu', 'Dil', 'Dim', 'Dim', 'Dij', 'Div', 'Dis'],
    		dayNamesMin: ['Diu', 'Dil', 'Dim', 'Dim', 'Dij', 'Div', 'Dis'],
		<?php echo "defaultDate: '".date('d/m/Y', $timestamp)."',\n";?>
    		beforeShowDay: function (date) {
    			var dt_ddmmyyyy = leadingZero(date.getDate()) + '-' + leadingZero(date.getMonth() + 1) + '-' + date.getFullYear();
	     	  	if (onlyThisDates.indexOf(dt_ddmmyyyy) != -1) {
        		   return [true, "","Available"]; 
        		} else {
           	  	   return [false, "","unAvailable"]; 
        		}
    		},
    		onSelect: function(dateText) { 
          		$('#fecha').val(dateText);
	  		populate_captures(dateText,'');
      		}	
	});
});
<?php 
echo "populate_captures('".date('d/m/Y', $timestamp)."','".$timestamp."');\n";
?>
function leadingZero(value) {
  if (value < 10) {
    return "0" + value.toString();
  }
  return value.toString();
}

function populate_captures(date,timestamp){
	var select = document.getElementById("triacaptura");
	var options = new Array(); 
    	var i;
    	for(i = select.options.length - 1 ; i >= 0 ; i--){
        	select.remove(i);
    	}

        $.ajax({
            type: "GET",
            contentType: "application/json; charset=utf-8",
            url: "timestamp.php",
            data: {'dia' : date},
            async: false,
	    //dataType: "json",
            success: function (data) {
                for (elem in data) {
                        options[elem]=data[elem];
                }
		//alert(JSON.stringify(data));
            },
            error: function (result) {
                alert("Error");
            }
        });

	//var options = ["1", "2", "3", "4", "5"]; 
	for (var index in options) {
		var el = document.createElement("option");
		el.textContent = options[index];
		el.value = index;
		if (index==timestamp){
			el.selected=true;
		}
		select.appendChild(el);
	}
	document.getElementById("submit").disabled=false;
}

function getposicioactual(){
	bounds=map.getBounds().toBBoxString();
	document.getElementById("bounds").value=bounds;
	var layer;
	var capa="";
	var canal="";
	capes.forEach(function(frec) {	
		layer=eval("f"+frec);
		if (map.hasLayer(layer)){
			canal=canal+frec+"-";;
		}
	});
	document.getElementById("frecuencies_actives").value=canal;
}

function trianode(uid,name){
	$("#suggestions").hide();
	document.getElementById("service").value=name;
        marker=eval("marker"+uid);
        lat=marker.getLatLng();
	marker.openPopup();
	map.setView(lat, 17);
}

</script>
</body> 
</html>
