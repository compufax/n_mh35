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
	if($_POST['plazausuario']){
		$res = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
		$row = mysql_fetch_array($res);

		echo '<h2><font color="BLACK">Bienvenidos a la plaza '.$array_plaza[$_POST['plazausuario']].'</font></h2>';
		if($row['mensajeinicio']!=''){
			echo '<p style="color:#FF0000;font-size:16px;">'.$row['mensajeinicio'].'</p>';
		}
		$res = mysql_query("SELECT mensaje FROM etiquetas_plazas WHERE plazas LIKE '%|".$_POST['plazausuario']."|%' AND estatus='A' ORDER BY cve DESC");
		$row = mysql_fetch_array($res);
		if($row['mensaje']!=''){
			echo '<p style="color:#FF0000;font-size:16px;">'.$row['mensaje'].'</p>';
		}
	}
	else{
		$rsDepto=mysql_query("SELECT * FROM areas");
		while($Depto=mysql_fetch_array($rsDepto)){
			$array_localidad[$Depto['cve']]=$Depto['nombre'];
		}
	
		$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a");
		while($row=mysql_fetch_array($res)){
			$array_localidad_plaza[$row['plaza']]=$array_localidad[$row['localidad_id']];
			$array_localidad_id_plaza[$row['plaza']]=$row['localidad_id'];
		}
		echo '<h2><font color="BLACK">Seleccionar Plaza</font></h2><br><ul>';
		if($_POST['cveusuario']==1 || $_SESSION['TipoUsuario']==1)
			$res = mysql_query("SELECT a.cve,a.numero,a.nombre FROM plazas a LEFT JOIN datosempresas b on a.cve = b.plaza WHERE a.estatus!='I' AND a.tipo_plaza != 3 ORDER BY b.localidad_id, a.lista");
		else
			$res = mysql_query("SELECT a.cve,a.numero,a.nombre FROM plazas a INNER JOIN usuario_accesos b ON a.cve=b.plaza AND b.usuario='".$_POST['cveusuario']."' AND b.acceso>0 LEFT JOIN datosempresas c on a.cve = c.plaza WHERE a.estatus!='I' AND a.tipo_plaza != 3 GROUP BY a.cve ORDER BY c.localidad_id,a.lista");
		$localidad = "";
		while($row = mysql_fetch_array($res)){
			if($localidad=="") $localidad = $array_localidad_plaza[$row['cve']];
			if($localidad!=$array_localidad_plaza[$row['cve']]) echo '<br>';
			if($array_localidad_id_plaza[$row['cve']] == 1)
				echo '<li><a href="#" onClick="document.forma.plazausuario.value='.$row['cve'].';atcr(\'inicio.php\',\'\',\'\',\'\');">'.$row['numero'].' '.$row['nombre'].' '.$array_localidad_plaza[$row['cve']].'</li>';
			else
				echo '<li><a href="#" onClick="document.forma.plazausuario.value='.$row['cve'].';atcr(\'inicio.php\',\'\',\'\',\'\');">'.$row['numero'].' '.$row['nombre'].' '.$array_localidad_plaza[$row['cve']].'</li>';
			$localidad = $array_localidad_plaza[$row['cve']];
		}

		if($_POST['cveusuario']==1 || $_SESSION['TipoUsuario']==1)
			$res = mysql_query("SELECT a.cve,a.numero,a.nombre,b.municipio FROM plazas a LEFT JOIN datosempresas b on a.cve = b.plaza WHERE a.estatus!='I' AND a.tipo_plaza = 3 ORDER BY b.localidad_id,a.lista, a.numero");
		else
			$res = mysql_query("SELECT a.cve,a.numero,a.nombre,c.municipio FROM plazas a INNER JOIN usuario_accesos b ON a.cve=b.plaza AND b.usuario='".$_POST['cveusuario']."' AND b.acceso>0 LEFT JOIN datosempresas c on a.cve = c.plaza WHERE a.estatus!='I' AND a.tipo_plaza = 3 GROUP BY a.cve ORDER BY c.localidad_id,a.lista,a.numero");
		$localidad = "";
		$municipio = "";
		if(mysql_num_rows($res)>0) echo '<br>';
		while($row = mysql_fetch_array($res)){
			//if($localidad=="") $localidad = $array_localidad_plaza[$row['cve']];
			//if($municipio=="") $municipio = $row['municipio'];
			//if($localidad!=$array_localidad_plaza[$row['cve']] || trim($municipio) != trim($row['municipio'])) echo '<br>';
			if($array_localidad_id_plaza[$row['cve']] == 1)
				echo '<li><a href="#" onClick="document.forma.plazausuario.value='.$row['cve'].';atcr(\'inicio.php\',\'\',\'\',\'\');">'.$row['numero'].' -'.$row['municipio'].'- '.$row['nombre'].' '.$array_localidad_plaza[$row['cve']].'</li>';
			else
				echo '<li><a href="#" onClick="document.forma.plazausuario.value='.$row['cve'].';atcr(\'inicio.php\',\'\',\'\',\'\');">'.$row['numero'].' -'.$row['municipio'].'- '.$row['nombre'].' '.$array_localidad_plaza[$row['cve']].'</li>';
			$localidad = $array_localidad_plaza[$row['cve']];
			$municipio = $row['municipio'];
		}
		echo '</ul>';
	}
bottom(); 
 
?>

