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

$rsUsuario=mysql_query("SELECT * FROM datosempresas");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazasrfc[$Usuario['plaza']]=$Usuario['rfc'];
}
$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$importe_iva=round($row['precio']*16/116,2);
	$array_engomadoprecio[$row['cve']]=$row['precio']-$importe_iva;
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
	echo '<h1>Ventas de Plazas del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h1>';
	echo fechaLocal().' '.horaLocal().'<br>';
	$array_plazas=array();
	if($_POST['localidad']!='all'){
		$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.localidad_id = '".$_POST['localidad']."' ORDER BY a.numero");
	}
	else{
		$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.rfc LIKE '%".$_POST['rfc']."%'");
	}
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	if($_POST['cveusuario']==1) $c++;
	echo '<tr bgcolor="#E9F2F8"><th>Plaza</th>';
	echo '<th>Aforo</th><th>Rechazos</th><th>Total Venta</th><th>Devoluciones</th><th>Total</tr>'; 
	$sumacargo=array(0,0,0);
	$x=0;
	foreach($array_plazas as $k=>$v){
		//if($_POST['rfc']=='' || $_POST['rfc']==$array_plazasrfc[$k]){
		//	if($_POST['plaza']=='all' || $k==$_POST['plaza']){
				rowb();
				$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
				FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
				$res=mysql_query($select) or die(mysql_error());
				$row=mysql_fetch_array($res);
				echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
				$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
				$res2=mysql_query($select) or die(mysql_error());
				$row2=mysql_fetch_array($res2);
				echo '<td align="right">'.number_format($row2[0],0).'</td>';
				echo '<td align="right">'.number_format($row2[1],0).'</td>';
				echo '<td align="right">'.number_format($row[1],2).'</td>';
				$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
				FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
				$res1=mysql_query($select) or die(mysql_error());
				$row1=mysql_fetch_array($res1);
				echo '<td align="right">'.number_format($row1[1],2).'</td>';
				echo '<td align="right">'.number_format($row[1]-$row1[1],2).'</td>';
				echo '</tr>';
				$x++;
				$sumacargo[0]+=$row2[0];
				$sumacargo[1]+=$row2[1];
				$sumacargo[2]+=$row[1];
				$sumacargo[3]+=$row1[1];
				$sumacargo[4]+=$row[1]-$row1[1];
				
		//	}
		//}
	}
	$c=4;
	echo '<tr>';
	echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		if($k>1) $decimal=2;
		else $decimal=0;
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
	$array_plazas=array();
	if($_POST['localidad']!='all'){
		$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.localidad_id = '".$_POST['localidad']."' ORDER BY a.numero");
	}
	else{
		$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.rfc LIKE '%".$_POST['rfc']."%'");
	}
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	if($_POST['cveusuario']==1) $c++;
	echo '<tr bgcolor="#E9F2F8"><th>Plaza</th>';
	echo '<th>Aforo</th><th>Rechazos</th><th>Total Venta</th><th>Devoluciones</th><th>Total</th></tr>'; 
	$sumacargo=array(0,0,0);
	$x=0;
	foreach($array_plazas as $k=>$v){
		//if($_POST['rfc']=='' || $_POST['rfc']==$array_plazasrfc[$k]){
		//	if($_POST['plaza']=='all' || $k==$_POST['plaza']){
				rowb();
				$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
				FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
				$res=mysql_query($select) or die(mysql_error());
				$row=mysql_fetch_array($res);
				echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
				$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
				$res2=mysql_query($select) or die(mysql_error());
				$row2=mysql_fetch_array($res2);
				echo '<td align="right">'.number_format($row2[0],0).'</td>';
				echo '<td align="right">'.number_format($row2[1],0).'</td>';
				echo '<td align="right">'.number_format($row[1],2).'</td>';
				$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
				FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
				$res1=mysql_query($select) or die(mysql_error());
				$row1=mysql_fetch_array($res1);
				echo '<td align="right">'.number_format($row1[1],2).'</td>';
				echo '<td align="right">'.number_format($row[1]-$row1[1],2).'</td>';
				echo '</tr>';
				$x++;
				$sumacargo[0]+=$row2[0];
				$sumacargo[1]+=$row2[1];
				$sumacargo[2]+=$row[1];
				$sumacargo[3]+=$row1[1];
				$sumacargo[4]+=$row[1]-$row1[1];
		//	}
		//}
	}
	$c=4;
	echo '<tr>';
	echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		if($k>1) $decimal=2;
		else $decimal=0;
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
				echo 'atcr(\'ventasxplaza.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.substr(fechaLocal(),0,8).'01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		/*if($_POST['plazausuario']>0){
			echo '<tr><td>Plaza</td><td>'.$array_plazas[$_POST['plazausuario']].'<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'"><input type="hidden" name="localidad" id="localidad" value="all"></td></tr>';
		}
		else{*/
			echo '<tr><td align="left">Localidad</td><td><select name="localidad" id="localidad"><option value="all">Todas</option>';
			foreach($array_localidad as $k=>$v){
				echo '<option value="'.$k.'"';
				if($k==2) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select>';
			echo '<tr><td align="left">Plaza</td><td><select multiple="multiple" name="plazas" id="plazas">';
			foreach($array_plazas as $k=>$v){
				echo '<option value="'.$k.'" selected>'.$v.'</option>';
			}
			echo '</select>';
			echo '<input type="hidden" name="plaza" id="plaza" value=""></td></tr>';
		//}
		echo '<tr><td>RFC</td><td><input type="text" size="20" name="rfc" id="rfc" class="textField"></td></tr>';
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
			objeto.open("POST","ventasxplaza.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&localidad="+document.getElementById("localidad").value+"&rfc="+document.getElementById("rfc").value+"&plaza="+document.getElementById("plaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
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