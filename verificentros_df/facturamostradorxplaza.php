<?php
include("main.php");

//ARREGLOS


$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsUsuario=mysql_query("SELECT * FROM plazas where estatus!='I' ORDER BY numero");
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

$array_clientes=array();
$res=mysql_query("SELECT * FROM clientes WHERE plaza='".$_POST['plazausuario']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_clientes[$row['cve']]=$row['nombre'];
	if($row['rfc']=="" || $row['nombre']=="" || $row['calle']=="" || $row['numexterior']=="" || $row['colonia']=="" || $row['municipio']=="" || $row['codigopostal']=="")
		$array_colorcliente[$row['cve']] = "#FF0000";
	else
		$array_colorcliente[$row['cve']] = "#000000";
}
function mestexto($fec){
	global $array_meses;
	$datos=explode("-",$fec);
	return $array_meses[intval($datos[1])].' '.$datos[0];
}
//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);
$rechazos = "9,19";

$abono=0;

if($_POST['cmd']==100){
	echo '<h2>Ventas de Plazas del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';
	if($_POST['fecha_ini']<'2015-05-01') $_POST['fecha_ini'] = '2015-05-01';
	if($_POST['fecha_fin']<'2015-05-01') $_POST['fecha_fin'] = '2015-05-01';
	echo fechaLocal().' '.horaLocal().'<br>';
	$array_plazas=array();
	if($_POST['localidad']!='all'){
		$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.localidad_id = '".$_POST['localidad']."' ORDER BY a.numero");
	}
	else{
		$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].")");
	}
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}

	$array_importes = array();
	$res = mysql_query("SELECT plaza, fecha, SUM(monto) as vendido FROM cobro_engomado WHERE plaza IN (".$_POST['plaza'].") AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND tipo_pago NOT IN (2,6) GROUP BY plaza, fecha");
	while($row = mysql_fetch_array($res)){
		$array_importes[$row['plaza']][$row['fecha']]['vendido'] = $row['vendido'];
		$array_importes[$row['plaza']][$row['fecha']]['facturado'] = 0;
		$array_importes[$row['plaza']][$row['fecha']]['restante'] = $row['vendido'];
	}
	$res = mysql_query("SELECT plaza, fecha, SUM(monto) as vendido FROM pagos_caja WHERE plaza IN (".$_POST['plaza'].") AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY plaza, fecha");
	while($row = mysql_fetch_array($res)){
		$array_importes[$row['plaza']][$row['fecha']]['vendido'] += $row['vendido'];
		$array_importes[$row['plaza']][$row['fecha']]['facturado'] -= 0;
		$array_importes[$row['plaza']][$row['fecha']]['restante'] += $row['vendido'];
	}
	$res = mysql_query("SELECT plaza, fecha, SUM(recuperacion) as vendido FROM recuperacion_certificado WHERE plaza IN (".$_POST['plaza'].") AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY plaza, fecha");
	while($row = mysql_fetch_array($res)){
		$array_importes[$row['plaza']][$row['fecha']]['vendido'] += $row['vendido'];
		$array_importes[$row['plaza']][$row['fecha']]['facturado'] -= 0;
		$array_importes[$row['plaza']][$row['fecha']]['restante'] += $row['vendido'];
	}
	$res = mysql_query("SELECT plaza, fecha, SUM(devolucion) as vendido FROM devolucion_certificado WHERE plaza IN (".$_POST['plaza'].") AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY plaza, fecha");
	while($row = mysql_fetch_array($res)){
		$array_importes[$row['plaza']][$row['fecha']]['vendido'] -= $row['vendido'];
		$array_importes[$row['plaza']][$row['fecha']]['facturado'] -= 0;
		$array_importes[$row['plaza']][$row['fecha']]['restante'] -= $row['vendido'];
	}
	$res = mysql_query("SELECT plaza, fecha, SUM(monto) as vendido FROM devolucion_ajuste WHERE plaza IN (".$_POST['plaza'].") AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY plaza, fecha");
	while($row = mysql_fetch_array($res)){
		$array_importes[$row['plaza']][$row['fecha']]['vendido'] -= $row['vendido'];
		$array_importes[$row['plaza']][$row['fecha']]['facturado'] -= 0;
		$array_importes[$row['plaza']][$row['fecha']]['restante'] -= $row['vendido'];
	}
	$res = mysql_query("SELECT plaza, fecha, SUM(total) as facturado FROM facturas WHERE plaza IN (".$_POST['plaza'].") AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY plaza, fecha");
	while($row = mysql_fetch_array($res)){
		$array_importes[$row['plaza']][$row['fecha']]['vendido'] += 0;
		$array_importes[$row['plaza']][$row['fecha']]['facturado'] += $row['facturado'];
		$array_importes[$row['plaza']][$row['fecha']]['restante'] -= $row['facturado'];
	}
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	if($_POST['cveusuario']==1) $c++;
	echo '<tr bgcolor="#E9F2F8"><th rowspan="2">Plaza</th>';
	$fecha = $_POST['fecha_ini'];
	while($fecha <= $_POST['fecha_fin']){
		echo '<th colspan="3">'.$fecha.'</th>';
		$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
	}
	echo '</tr><tr bgcolor="#E9F2F8">';
	$fecha = $_POST['fecha_ini'];
	$c=1;
	$sumacargo=array();
	while($fecha <= $_POST['fecha_fin']){
		echo '<th>Vendido</th><th>Facturado</th><th>Restante</th>';
		$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );	
		$sumacargo[$c] = 0; $c++;
		$sumacargo[$c] = 0; $c++;
		$sumacargo[$c] = 0; $c++;
	}
	
	echo '</tr>';
	$x=0;
	foreach($array_plazas as $k=>$v){
		rowb();
		echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
		$c=1;
		$fecha = $_POST['fecha_ini'];
		while($fecha <= $_POST['fecha_fin']){
			echo '<td align="right">'.number_format($array_importes[$k][$fecha]['vendido'],2).'</td>';
			echo '<td align="right">'.number_format($array_importes[$k][$fecha]['facturado'],2).'</td>';
			echo '<td align="right"><font color="RED">'.number_format($array_importes[$k][$fecha]['restante'],2).'</font></td>';
			$sumacargo[$c]+=$array_importes[$k][$fecha]['vendido']; $c++;
			$sumacargo[$c]+=$array_importes[$k][$fecha]['facturado']; $c++;
			$sumacargo[$c]+=$array_importes[$k][$fecha]['restante']; $c++;
			$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
		}
		echo '</tr>';
		$x++;
	}
	echo '<tr>';
	echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		$decimal=2;
		if(($k%3) == 0)
			echo '<td bgcolor="#E9F2F8" align="right"><font color="RED">&nbsp;'.number_format($v,$decimal).'</font></td>';
		else
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==1){
	$filtro="";
	/*$select= " SELECT a.* FROM facturas as a WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
	if($_POST['plaza']!="all") $select.=" AND a.plaza='".$_POST['plaza']."'";
	$rsabonos=mysql_query($select) or die(mysql_error());*/
	if($_POST['fecha_ini']<'2015-05-01') $_POST['fecha_ini'] = '2015-05-01';
	if($_POST['fecha_fin']<'2015-05-01') $_POST['fecha_fin'] = '2015-05-01';
	$array_plazas=array();
	if($_POST['localidad']!='all'){
		$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.localidad_id = '".$_POST['localidad']."' ORDER BY a.numero");
	}
	else{
		$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].")");
	}
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}

	$array_importes = array();
	$res = mysql_query("SELECT plaza, fecha, SUM(monto) as vendido FROM cobro_engomado WHERE plaza IN (".$_POST['plaza'].") AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND tipo_pago NOT IN (2,6) GROUP BY plaza, fecha");
	while($row = mysql_fetch_array($res)){
		$array_importes[$row['plaza']][$row['fecha']]['vendido'] = $row['vendido'];
		$array_importes[$row['plaza']][$row['fecha']]['facturado'] = 0;
		$array_importes[$row['plaza']][$row['fecha']]['restante'] = $row['vendido'];
	}
	$res = mysql_query("SELECT plaza, fecha, SUM(monto) as vendido FROM pagos_caja WHERE plaza IN (".$_POST['plaza'].") AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY plaza, fecha");
	while($row = mysql_fetch_array($res)){
		$array_importes[$row['plaza']][$row['fecha']]['vendido'] += $row['vendido'];
		$array_importes[$row['plaza']][$row['fecha']]['facturado'] -= 0;
		$array_importes[$row['plaza']][$row['fecha']]['restante'] += $row['vendido'];
	}
	$res = mysql_query("SELECT plaza, fecha, SUM(recuperacion) as vendido FROM recuperacion_certificado WHERE plaza IN (".$_POST['plaza'].") AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY plaza, fecha");
	while($row = mysql_fetch_array($res)){
		$array_importes[$row['plaza']][$row['fecha']]['vendido'] += $row['vendido'];
		$array_importes[$row['plaza']][$row['fecha']]['facturado'] -= 0;
		$array_importes[$row['plaza']][$row['fecha']]['restante'] += $row['vendido'];
	}
	$res = mysql_query("SELECT plaza, fecha, SUM(devolucion) as vendido FROM devolucion_certificado WHERE plaza IN (".$_POST['plaza'].") AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY plaza, fecha");
	while($row = mysql_fetch_array($res)){
		$array_importes[$row['plaza']][$row['fecha']]['vendido'] -= $row['vendido'];
		$array_importes[$row['plaza']][$row['fecha']]['facturado'] -= 0;
		$array_importes[$row['plaza']][$row['fecha']]['restante'] -= $row['vendido'];
	}
	$res = mysql_query("SELECT plaza, fecha, SUM(monto) as vendido FROM devolucion_ajuste WHERE plaza IN (".$_POST['plaza'].") AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY plaza, fecha");
	while($row = mysql_fetch_array($res)){
		$array_importes[$row['plaza']][$row['fecha']]['vendido'] -= $row['vendido'];
		$array_importes[$row['plaza']][$row['fecha']]['facturado'] -= 0;
		$array_importes[$row['plaza']][$row['fecha']]['restante'] -= $row['vendido'];
	}
	$res = mysql_query("SELECT plaza, fecha, SUM(total) as facturado FROM facturas WHERE plaza IN (".$_POST['plaza'].") AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY plaza, fecha");
	while($row = mysql_fetch_array($res)){
		$array_importes[$row['plaza']][$row['fecha']]['vendido'] += 0;
		$array_importes[$row['plaza']][$row['fecha']]['facturado'] += $row['facturado'];
		$array_importes[$row['plaza']][$row['fecha']]['restante'] -= $row['facturado'];
	}
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	if($_POST['cveusuario']==1) $c++;
	echo '<tr bgcolor="#E9F2F8"><th rowspan="2">Plaza</th>';
	$fecha = $_POST['fecha_ini'];
	while($fecha <= $_POST['fecha_fin']){
		echo '<th colspan="3">'.$fecha.'</th>';
		$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
	}
	echo '</tr><tr bgcolor="#E9F2F8">';
	$fecha = $_POST['fecha_ini'];
	$c=1;
	$sumacargo=array();
	while($fecha <= $_POST['fecha_fin']){
		echo '<th>Vendido</th><th>Facturado</th><th>Restante</th>';
		$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );	
		$sumacargo[$c] = 0; $c++;
		$sumacargo[$c] = 0; $c++;
		$sumacargo[$c] = 0; $c++;
	}
	
	echo '</tr>';
	$x=0;
	foreach($array_plazas as $k=>$v){
		rowb();
		echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
		$c=1;
		$fecha = $_POST['fecha_ini'];
		while($fecha <= $_POST['fecha_fin']){
			echo '<td align="right">'.number_format($array_importes[$k][$fecha]['vendido'],2).'</td>';
			echo '<td align="right">'.number_format($array_importes[$k][$fecha]['facturado'],2).'</td>';
			echo '<td align="right"><font color="RED">'.number_format($array_importes[$k][$fecha]['restante'],2).'</font></td>';
			$sumacargo[$c]+=$array_importes[$k][$fecha]['vendido']; $c++;
			$sumacargo[$c]+=$array_importes[$k][$fecha]['facturado']; $c++;
			$sumacargo[$c]+=$array_importes[$k][$fecha]['restante']; $c++;
			$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
		}
		echo '</tr>';
		$x++;
	}
	echo '<tr>';
	echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		$decimal=2;
		if(($k%3) == 0)
			echo '<td bgcolor="#E9F2F8" align="right"><font color="RED">&nbsp;'.number_format($v,$decimal).'</font></td>';
		else
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';
	echo '</table>';
	exit();
}


top($_SESSION);
	

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="';
				/*if($_POST['plazausuario']==0)*/ echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
				echo 'atcr(\'facturamostradorxplaza.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.substr(fechaLocal(),0,8).'01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		/*if($_POST['plazausuario']>0){
			echo '<tr><td>Plaza</td><td>'.$array_plazas[$_POST['plazausuario']].'<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'"><input type="hidden" name="localidad" id="localidad" value="all"></td></tr>';
		}
		else{*/
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
		//}
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
bottom();
echo '
<Script language="javascript">';
//if($_POST['plazausuario']==0){
	echo '
	$("#plazas").multipleSelect({
		width: 500
	});	
	function buscarRegistros(){
		document.forma.plaza.value=$("#plazas").multipleSelect("getSelects");
	';
/*}
else{
	echo 'function buscarRegistros(){
	';
}*/
echo '  document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","facturamostradorxplaza.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&localidad="+document.getElementById("localidad").value+"&plaza="+document.getElementById("plaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
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
		
	';	
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(0,1); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
	

	</Script>
';

?>