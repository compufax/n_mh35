<?php
include("main.php");

$res = mysql_query("SELECT * FROM anios_certificados WHERE 1 ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
}

$rsUsuario=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza where a.estatus!='I' ORDER BY b.localidad_id, a.lista, a.numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}

$localidadplaza = 0;


$rsUsuario=mysql_query("SELECT * FROM datosempresas");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazaslocalidad[$Usuario['plaza']]=$Usuario['localidad_id'];
	if($Usuario['plaza'] == $_POST['plazausuario']) $localidadplaza = $Usuario['localidad_id'];
}

$res=mysql_query("SELECT * FROM areas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_localidad[$row['cve']]=$row['nombre'];
}


$array_engomado = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' ORDER BY nombre");
$res = mysql_query("SELECT numero, nombre, max(precio) as precio, group_concat(cve) as cves FROM engomados WHERE localidad='".$_POST['localidad']."' AND entrega=1 GROUP BY numero ORDER BY numero");
while($row=mysql_fetch_array($res)){
	$res1=mysql_query("SELECT nombre,precio,abreviatura FROM engomados WHERE localidad = '".$_POST['localidad']."'  AND numero='".$row['numero']."' AND entrega=1 ORDER BY cve");
	$row1=mysql_fetch_array($res1);
	$array_engomado['normal'][$row['numero']]['nombre']=$row1['nombre'];
	$array_engomado['normal'][$row['numero']]['precio']=$row1['precio'];
	$array_engomado['normal'][$row['numero']]['cves']=$row['cves'];
	$array_engomado['normal'][$row['numero']]['total']=0;
	$array_engomado['cancelados'][$row['numero']]['nombre']=$row1['abreviatura'];
	$array_engomado['cancelados'][$row['numero']]['precio']=$row1['precio'];
	$array_engomado['cancelados'][$row['numero']]['cves']=$row['cves'];
	$array_engomado['cancelados'][$row['numero']]['total']=0;
}

$array_motivos_intento = array();
$res = mysql_query("SELECT * FROM motivos_intento WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_motivos_intento[$row['cve']]=$row['nombre'];
}

if($_POST['cmd']==100){
	echo '<h1>'.$array_plaza[$_POST['plazausuario']].'<br>Reporte de Certificados por Plaza</h1>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr><th bgcolor="#E9F2F8" rowspan="2">Centro</th>';
	foreach($array_engomado['normal'] as $k=>$v) echo '<th rowspan="2" bgcolor="#E9F2F8">'.$v['nombre'].'</th>';
	echo '<th bgcolor="#E9F2F8" rowspan="2">Total Ventas en Efectivo</th><th rowspan="2">&nbsp;</th>
	<th bgcolor="#E9F2F8" colspan="'.count($array_engomado['cancelados']).'">Cancelados</th>';
	echo '</tr>';
	echo '<tr>';
	foreach($array_engomado['cancelados'] as $k=>$v) echo '<th bgcolor="#E9F2F8">'.$v['nombre'].'</th>';
	echo '</tr>';
	
	$total = 0;
	if($_POST['fecha_ini']<'2015-05-01') $_POST['fecha_ini'] = '2015-05-01';
	if($_POST['fecha_fin']<'2015-05-01') $_POST['fecha_fin'] = '2015-05-01';
	$array_plazas=array();
	if($_POST['localidad']!='all'){
		$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.localidad_id = '".$_POST['localidad']."' ORDER BY b.localidad_id, a.lista, a.numero");
	}
	else{
		$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") ORDER BY b.localidad_id, a.lista, a.numero");
	}
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	foreach($array_plazas as $k1=>$v1){
		rowb();
		echo '<td align="left">'.htmlentities(utf8_encode($v1)).'</td>';
		$c=0;
		foreach($array_engomado['normal'] as $k=>$v){
			$array_engomado['cancelados'][$k]['cant'] = 0;
			$array_engomado['normal'][$k]['cant'] = 0;
			$select = "SELECT a.* FROM (
			SELECT cve, 0 as cancelado
			FROM certificados 
			WHERE plaza = '".$k1."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus != 'C' AND engomado IN (".$v['cves'].")";
			if($_POST['anio'] != 'all') $select .= " AND anio = '".$_POST['anio']."'";
			$select .= "
			UNION ALL 
			SELECT cve, 1 as cancelado FROM certificados_cancelados 
			WHERE plaza='".$k1."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND engomado IN (".$v['cves'].") AND estatus!='C'";
			if($_POST['anio'] != 'all') $select .= " AND anio = '".$_POST['anio']."'";
			$select .= ") as a";
			$res = mysql_query($select) or die(mysql_error());
			$fcertificado=-1;
			while($row = mysql_fetch_array($res)){
				if($row['cancelado']==1){
					$array_engomado['cancelados'][$k]['cant']++;
					$array_engomado['cancelados'][$k]['total']++;
				}
				else{
					$array_engomado['normal'][$k]['cant']++;
					$array_engomado['normal'][$k]['total']++;
				}
			}
		}
		foreach($array_engomado['normal'] as $k=>$v){
			echo '<td align="center">'.$v['cant'].'</td>';
		}
		$select = "SELECT SUM(monto) FROM cobro_engomado WHERE plaza = '".$k1."' AND fecha  BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C'";
		if($_POST['anio'] != 'all') $select .= " AND anio='".$_POST['anio']."'";
		$res = mysql_query($select);
		$row = mysql_fetch_array($res);
		echo '<td align="right">'.number_format($row[0],2).'</td>';
		$total += $row[0];
		echo '<td>&nbsp;</td>';
		foreach($array_engomado['cancelados'] as $k=>$v){
			echo '<td align="center">'.$v['cant'].'</td>';
		}
		echo '</tr>';
	}
	echo '<tr><th align="left" bgcolor="#E9F2F8">Total</th>';
	foreach($array_engomado['normal'] as $k=>$v){
		echo '<th bgcolor="#E9F2F8">'.$v['total'].'</th>';
	}
	echo '<th bgcolor="#E9F2F8" align="right">'.number_format($total,2).'</th>';
	echo '<td>&nbsp;</td>';
	foreach($array_engomado['cancelados'] as $k=>$v){
		echo '<th bgcolor="#E9F2F8">'.$v['total'].'</th>';
	}
	echo '</tr>';
	echo '</table>';
	exit();	
}

if($_POST['ajax']==1){
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr><th bgcolor="#E9F2F8" rowspan="2">Centro</th>';
	foreach($array_engomado['normal'] as $k=>$v) echo '<th rowspan="2" bgcolor="#E9F2F8">'.$v['nombre'].'</th>';
	echo '<th bgcolor="#E9F2F8" rowspan="2">Total Ventas en Efectivo</th><th rowspan="2">&nbsp;</th>
	<th bgcolor="#E9F2F8" colspan="'.count($array_engomado['cancelados']).'">Cancelados</th>';
	echo '</tr>';
	echo '<tr>';
	foreach($array_engomado['cancelados'] as $k=>$v) echo '<th bgcolor="#E9F2F8">'.$v['nombre'].'</th>';
	echo '</tr>';
	
	$total = 0;
	if($_POST['fecha_ini']<'2015-05-01') $_POST['fecha_ini'] = '2015-05-01';
	if($_POST['fecha_fin']<'2015-05-01') $_POST['fecha_fin'] = '2015-05-01';
	$array_plazas=array();
	if($_POST['localidad']!='all'){
		$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.localidad_id = '".$_POST['localidad']."' ORDER BY b.localidad_id, a.lista, a.numero");
	}
	else{
		$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") ORDER BY b.localidad_id, a.lista, a.numero");
	}
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	foreach($array_plazas as $k1=>$v1){
		rowb();
		echo '<td align="left">'.htmlentities(utf8_encode($v1)).'</td>';
		$c=0;
		foreach($array_engomado['normal'] as $k=>$v){
			$array_engomado['cancelados'][$k]['cant'] = 0;
			$array_engomado['normal'][$k]['cant'] = 0;
			$select = "SELECT a.* FROM (
			SELECT cve, 0 as cancelado
			FROM certificados 
			WHERE plaza = '".$k1."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus != 'C' AND engomado IN (".$v['cves'].")";
			if($_POST['anio'] != 'all') $select .= " AND anio = '".$_POST['anio']."'";
			$select .= "
			UNION ALL 
			SELECT cve, 1 as cancelado FROM certificados_cancelados 
			WHERE plaza='".$k1."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND engomado IN (".$v['cves'].") AND estatus!='C'";
			if($_POST['anio'] != 'all') $select .= " AND anio = '".$_POST['anio']."'";
			$select .= ") as a";
			$res = mysql_query($select) or die(mysql_error());
			$fcertificado=-1;
			while($row = mysql_fetch_array($res)){
				if($row['cancelado']==1){
					$array_engomado['cancelados'][$k]['cant']++;
					$array_engomado['cancelados'][$k]['total']++;
				}
				else{
					$array_engomado['normal'][$k]['cant']++;
					$array_engomado['normal'][$k]['total']++;
				}
			}
		}
		foreach($array_engomado['normal'] as $k=>$v){
			echo '<td align="center">'.$v['cant'].'</td>';
		}
		$select = "SELECT SUM(monto) FROM cobro_engomado WHERE plaza = '".$k1."' AND fecha  BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C'";
		if($_POST['anio'] != 'all') $select .= " AND anio='".$_POST['anio']."'";
		$res = mysql_query($select);
		$row = mysql_fetch_array($res);
		echo '<td align="right">'.number_format($row[0],2).'</td>';
		$total += $row[0];
		echo '<td>&nbsp;</td>';
		foreach($array_engomado['cancelados'] as $k=>$v){
			echo '<td align="center">'.$v['cant'].'</td>';
		}
		echo '</tr>';
	}
	echo '<tr><th align="left" bgcolor="#E9F2F8">Total</th>';
	foreach($array_engomado['normal'] as $k=>$v){
		echo '<th bgcolor="#E9F2F8">'.$v['total'].'</th>';
	}
	echo '<th bgcolor="#E9F2F8" align="right">'.number_format($total,2).'</th>';
	echo '<td>&nbsp;</td>';
	foreach($array_engomado['cancelados'] as $k=>$v){
		echo '<th bgcolor="#E9F2F8">'.$v['total'].'</th>';
	}
	echo '</tr>';
	echo '</table>';
	exit();	
}

top($_SESSION);

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="if(document.forma.localidad.value==\'all\') alert(\'Necesita seleccionar la localidad\'); else buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onclick="if(document.forma.localidad.value==\'all\') alert(\'Necesita seleccionar la localidad\'); else{ document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');atcr(\'certificadosxplaza.php\',\'_blank\',100,0);}"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Fin</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>A&ntilde;o Certificacion</td><td><select name="anio" id="anio"><option value="all" selected>Todos</option>';
	foreach($array_anios as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td align="left">Localidad</td><td><select name="localidad" id="localidad" onChange="muestraplazas()"><option value="all">Todas</option>';
	foreach($array_localidad as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$localidadplaza) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select>';
	echo '<tr><td align="left">Plaza</td><td><select multiple="multiple" name="plazas" id="plazas">';
	$optionsplazas = array();
	foreach($array_plazas as $k=>$v){
		if($localidadplaza == 0 || $localidadplaza == $array_plazaslocalidad[$k]){
			echo '<option value="'.$k.'" selected>'.$v.'</option>';
		}
		$optionsplazas['all'] .= '<option value="'.$k.'" selected>'.$v.'</option>';
		$optionsplazas[$array_plazaslocalidad[$k]] .= '<option value="'.$k.'" selected>'.$v.'</option>';
	}
	echo '</select>';
	echo '<input type="hidden" name="plaza" id="plaza" value=""></td></tr>';
	echo '</table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	$("#plazas").multipleSelect({
		width: 500
	});	
	function buscarRegistros(){
		document.forma.plaza.value=$("#plazas").multipleSelect("getSelects");
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","certificadosxplaza.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&localidad="+document.getElementById("localidad").value+"&plaza="+document.getElementById("plaza").value+"&anio="+document.getElementById("anio").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					document.getElementById("Resultados").innerHTML = objeto.responseText;
					document.getElementById("depositos").innerHTML = document.getElementById("depositos2").innerHTML;
					document.getElementById("depositos2").innerHTML = "";
				}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}

	function muestraplazas(){

		';

		foreach($optionsplazas as $k=>$v){
			echo '
				if(document.forma.localidad.value == "'.$k.'"){
					$("#plazas").html(\''.$v.'\');
				}
			';
		}

	echo '	

		$("#plazas").multipleSelect("refresh");	
	}
	
	</Script>
	';

	
}
	
bottom();
?>