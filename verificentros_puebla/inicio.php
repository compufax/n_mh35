<?php 

include ("main.php"); 
if($_POST['ajax']==99){
	mysql_query("INSERT registros_sistemamov SET cveacceso='".$_POST['cvereg']."',usuario='".$_POST['usuario']."',menu='".$_POST['idmenu']."',fechahora='".fechaLocal()." ".horaLocal()."'");
	exit();
}
if(!$_POST['plazausuario'] && count($array_plaza) == 1){
	foreach($array_plaza as $k=>$v)
		$_POST['plazausuario']=$k;
}
if($_POST['cmd']=='cambiarplaza'){
	$_POST['plazausuario']=0;
}
top($_SESSION);
	$rsDepto=mysql_query("SELECT * FROM areas");
	while($Depto=mysql_fetch_array($rsDepto)){
		$array_localidad[$Depto['cve']]=$Depto['nombre'];
	}
	
	$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a");
	while($row=mysql_fetch_array($res)){
		$array_localidad_plaza[$row['plaza']]=$array_localidad[$row['localidad_id']];
	}
	if($_POST['plazausuario']){
		echo '<h1><font color="BLACK">Bienvenidos a la plaza '.$array_plaza[$_POST['plazausuario']].'</font></h1>';
	}
	else{
		echo '<h1><font color="BLACK">Seleccionar Plaza</font></h1><br><ul>';
		if($_POST['cveusuario']==1 || $_SESSION['TipoUsuario']==1)
			$res = mysql_query("SELECT a.cve,a.numero,a.nombre FROM plazas a LEFT JOIN datosempresas b on a.cve = b.plaza WHERE a.estatus!='I' ORDER BY b.localidad_id, a.numero");
		else
			$res = mysql_query("SELECT a.cve,a.numero,a.nombre FROM plazas a INNER JOIN usuario_accesos b ON a.cve=b.plaza AND b.usuario='".$_POST['cveusuario']."' AND b.acceso>0 LEFT JOIN datosempresas c on a.cve = c.plaza WHERE a.estatus!='I' GROUP BY a.cve ORDER BY c.localidad_id,a.numero");
		$localidad = "";
		while($row = mysql_fetch_array($res)){
			if($localidad=="") $localidad = $array_localidad_plaza[$row['cve']];
			if($localidad!=$array_localidad_plaza[$row['cve']]) echo '<br>';
			echo '<li><a href="#" onClick="document.forma.plazausuario.value='.$row['cve'].';atcr(\'inicio.php\',\'\',\'\',\'\');">'.$row['numero'].' '.$row['nombre'].' '.$array_localidad_plaza[$row['cve']].'</li>';
			$localidad = $array_localidad_plaza[$row['cve']];
		}
		echo '</ul>';
	}
bottom(); 
 
?>

