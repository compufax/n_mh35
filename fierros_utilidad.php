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


$res=mysql_query("SELECT * FROM areas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_localidad[$row['cve']]=$row['nombre'];
}



$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);

$abono=0;




if($_POST['cmd']==100){
	echo '<h1>Reporte de Utilidad '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'];
	if($_POST['plazausuario']>0) echo '<br>Plaza: '.$array_plazas[$_POST['plazausuario']];
	echo '</h1>';
	echo fechaLocal().' '.horaLocal().'<br>';
	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Tipo de Verificacion</th><th>Precio Compra</th><th>Cantidad</th><th>Costo de Venta</th><th>Venta</th><th>Utilidad Bruta</th><th>Cancelados</th><th>Importe Cancelados</th><th>Utilidad - Cancelados</th></tr>';
	$array_totales = array();
	$res = mysql_query("SELECT * FROM engomados WHERE localidad = '".$_POST['localidad']."' AND entrega=1 ORDER BY nombre");
	while($row = mysql_fetch_array($res)){
		echo '<tr>';
		echo '<td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
		echo '<td align="right">'.$row['precio_compra'].'</td>';
		$row1=mysql_fetch_array(mysql_query("SELECT COUNT(cve) FROM certificados WHERE plaza IN (".$_POST['plaza'].") AND engomado = '".$row['cve']."' AND estatus != 'C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'"));
		echo '<td align="right">'.intval($row1[0]).'</td>';
		$array_totales[0]+=$row1[0];
		echo '<td align="right">'.number_format($row1[0]*$row['precio_compra'],2).'</td>';
		$array_totales[1]+=$row1[0]*$row['precio_compra'];
		$row2=mysql_fetch_array(mysql_query("SELECT SUM(monto) FROM cobro_engomado WHERE plaza IN (".$_POST['plaza'].") AND engomado = '".$row['cve']."' AND estatus != 'C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'"));
		echo '<td align="right">'.number_format($row2[0],2).'</td>';
		$array_totales[2]+=$row2[0];
		$utilidad = $row2[0]-($row1[0]*$row['precio_compra']);
		echo '<td align="right">'.number_format($utilidad,2).'</td>';
		$array_totales[3]+=$utilidad;
		$row3=mysql_fetch_array(mysql_query("SELECT COUNT(cve) FROM certificados_cancelados WHERE plaza IN (".$_POST['plaza'].") AND engomado = '".$row['cve']."' AND estatus != 'C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'"));
		echo '<td align="right">'.intval($row3[0]).'</td>';
		$array_totales[4]+=$row3[0];
		echo '<td align="right">'.number_format($row3[0]*$row['precio_compra'],2).'</td>';
		$array_totales[5]+=$row3[0]*$row['precio_compra'];
		echo '<td align="right">'.number_format($utilidad - ($row3[0]*$row['precio_compra']),2).'</td>';
		$array_totales[6]+=$utilidad - ($row3[0]*$row['precio_compra']);
		echo '</tr>';
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="2">Totales</th>';
	foreach($array_totales as $v) echo '<th align="right">'.number_format($v,2).'</th>';
	echo '</tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==1){

	

	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Tipo de Verificacion</th><th>Precio Compra</th><th>Cantidad</th><th>Costo de Venta</th><th>Venta</th><th>Utilidad Bruta</th><th>Cancelados</th><th>Importe Cancelados</th><th>Utilidad - Cancelados</th></tr>';
	$array_totales = array();
	$res = mysql_query("SELECT * FROM engomados WHERE localidad = '".$_POST['localidad']."' AND entrega=1 ORDER BY nombre");
	while($row = mysql_fetch_array($res)){
		rowb();
		echo '<td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
		echo '<td align="right">'.$row['precio_compra'].'</td>';
		$row1=mysql_fetch_array(mysql_query("SELECT COUNT(cve) FROM certificados WHERE plaza IN (".$_POST['plaza'].") AND engomado = '".$row['cve']."' AND estatus != 'C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'"));
		echo '<td align="right">'.intval($row1[0]).'</td>';
		$array_totales[0]+=$row1[0];
		echo '<td align="right">'.number_format($row1[0]*$row['precio_compra'],2).'</td>';
		$array_totales[1]+=$row1[0]*$row['precio_compra'];
		$row2=mysql_fetch_array(mysql_query("SELECT SUM(monto) FROM cobro_engomado WHERE plaza IN (".$_POST['plaza'].") AND engomado = '".$row['cve']."' AND estatus != 'C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'"));
		echo '<td align="right">'.number_format($row2[0],2).'</td>';
		$array_totales[2]+=$row2[0];
		$utilidad = $row2[0]-($row1[0]*$row['precio_compra']);
		echo '<td align="right">'.number_format($utilidad,2).'</td>';
		$array_totales[3]+=$utilidad;
		$row3=mysql_fetch_array(mysql_query("SELECT COUNT(cve) FROM certificados_cancelados WHERE plaza IN (".$_POST['plaza'].") AND engomado = '".$row['cve']."' AND estatus != 'C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'"));
		echo '<td align="right">'.intval($row3[0]).'</td>';
		$array_totales[4]+=$row3[0];
		echo '<td align="right">'.number_format($row3[0]*$row['precio_compra'],2).'</td>';
		$array_totales[5]+=$row3[0]*$row['precio_compra'];
		echo '<td align="right">'.number_format($utilidad - ($row3[0]*$row['precio_compra']),2).'</td>';
		$array_totales[6]+=$utilidad - ($row3[0]*$row['precio_compra']);
		echo '</tr>';
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="2">Totales</th>';
	foreach($array_totales as $v) echo '<th align="right">'.number_format($v,2).'</th>';
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
				<td><a href="#" onclick="if(document.forma.localidad.value==\'all\'){
					alert(\'Necesita seleccionar una localidad\');
				}
				else{
					buscarRegistros(0,1);
				}"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="if(document.forma.localidad.value==\'all\'){
					alert(\'Necesita seleccionar una localidad\');
				}
				else{';
				if($_POST['plazausuario']==0) echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
				echo 'atcr(\'fierros_utilidad.php\',\'_blank\',100,0);}"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.date( "Y-m-d" , strtotime ( "-6 day" , strtotime(fechaLocal()) ) ).'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		if($_POST['plazausuario']>0){
			echo '<tr><td>Plaza</td><td>'.$array_plazas[$_POST['plazausuario']].'<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'"><input type="hidden" name="localidad" id="localidad" value="'.$rowempresa['localidad_id'].'"></td></tr>';
		}
		else{
			echo '<tr><td align="left">Localidad</td><td><select name="localidad" id="localidad"><option value="all">Seleccione</option>';
			foreach($array_localidad as $k=>$v){
				echo '<option value="'.$k.'"';
				echo '>'.$v.'</option>';
			}
			echo '</select>';
			echo '<tr><td align="left">Plaza</td><td><select multiple="multiple" name="plazas" id="plazas">';
			foreach($array_plazas as $k=>$v){
				echo '<option value="'.$k.'" selected>'.$v.'</option>';
			}
			echo '</select>';
			echo '<input type="hidden" name="plaza" id="plaza" value=""></td></tr>';
		}
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
if($_POST['plazausuario']==0){
	echo '
	$("#plazas").multipleSelect({
		width: 500
	});	
	function buscarRegistros(){
		document.forma.plaza.value=$("#plazas").multipleSelect("getSelects");
	';
}
else{
	echo 'function buscarRegistros(){
	';
}
echo '  document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","fierros_utilidad.php",true);
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
	
		
	
	

	</Script>
';

?>