<?php

include("main.php");

$res = mysql_query("SELECT * FROM engomados WHERE entrega=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
}

$rsUsuario=mysql_query("SELECT * FROM plazas where estatus!='I' ORDER BY numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}

if($_POST['cmd']==101){
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=reporte.xls");
header("Pragma: no-cache");
header("Expires: 0");
	$filtro = "";
	if($_POST['engomado'] > 0){
		$filtro = " AND a.engomado = '{$_POST['engomado']}'";
	}
	$query = "";
	if($_POST['mostrar'] == 0 || $_POST['mostrar'] == 1){
		$query .= "SELECT 'Entregado' as estatus, a.plaza, b.fecha as fecha_venta, b.cve as ticket, a.placa, a.fecha, a.hora, a.engomado, a.certificado, a.tecnico, a.linea FROM certificados a INNER JOIN cobro_engomado b ON a.plaza = b.plaza AND a.ticket = b.cve WHERE a.plaza IN ({$_POST['plaza']}) AND a.fecha BETWEEN '{$_POST['fecha_ini']}' AND '{$_POST['fecha_fin']}' AND a.estatus != 'C' {$filtro}";
	}
	if ($_POST['mostrar'] == 0) {
		$query .= " UNION ALL ";
	}
	if($_POST['mostrar'] == 0 || $_POST['mostrar'] == 2){
		$query .= "SELECT 'Cancelado' as estatus, a.plaza, a.fechaticket as fecha_venta, a.ticket, a.placa, a.fecha, a.hora, a.engomado, a.certificado, a.tecnico, a.linea FROM certificados_cancelados a WHERE a.plaza IN ({$_POST['plaza']}) AND a.fecha BETWEEN '{$_POST['fecha_ini']}' AND '{$_POST['fecha_fin']}' AND a.estatus != 'C' {$filtro}";
	}
	$res = mysql_query("SELECT a.estatus,  CONCAT(b.numero, ' ', b.nombre) as nombreplaza, a.fecha_venta, a.ticket, a.placa, a.fecha, a.hora, c.nombre as tipocertificado, a.certificado, d.nombre as nombretecnico, e.nombre as nombrelinea FROM ({$query}) a
		INNER JOIN plazas b ON b.cve = a.plaza 
		INNER JOIN engomados c ON c.cve = a.engomado
		LEFT JOIN tecnicos d ON d.cve = a.tecnico AND a.plaza = d.plaza
		LEFT JOIN cat_lineas e ON e.cve = a.linea AND e.plaza = a.plaza
		ORDER BY b.lista, a.fecha, a.hora");
	
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			//echo'<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
			//echo' <tr><td colspan="12" align="center"><h2>'.$Plaza['numero'].' '.$Plaza['nombre'].'</h2></td></tr>';
			echo'<tr ><td style="font-size:32px" align="center" colspan="12">Reporte de Certificados General</td></tr>';
			echo' <tr style="font-size:22px"><td colspan="5" align="left"> Periodo '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</td><td colspan="5" align="right">Fecha '.fechaLocal().'</td></tr>';
			echo'</table>';
	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolo="#E9F2F8">';
	echo '<th>Estatus</th><th>Nombre Plaza</th><th>Fecha Venta</th><th>Folio Ticket</th><th>Placa</th><th>Fecha Entrega/Cancelacion</th><th>Tipo Certificado</th><th>Numero Certificado</th><th>Tecnico</th><th>Linea</th></tr>';
	echo '</tr>';
	$x=0;
	while($row = mysql_fetch_array($res)){
		
		echo '<tr><td align="center">'.$row['estatus'].'</td>';
		echo '<td>'.$row['nombreplaza'].'</td>';
		echo '<td align="center">'.substr($row['fecha_venta'], 0, 10).'</td>';
		echo '<td align="center">'.$row['ticket'].'</td>';
		echo '<td align="center">'.$row['placa'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		echo '<td>'.$row['tipocertificado'].'</td>';
		echo '<td align="center">'.$row['certificado'].'</td>';
		echo '<td>'.$row['nombretecnico'].'</td>';
		echo '<td>'.$row['nombrelinea'].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr  bgcolo="#E9F2F8"><th align="left" colspan="10">'.$x.' Registro(s)</th></tr>';
	echo '</table>';
	exit();
}
if($_POST['cmd']==100){
	$filtro = "";
	if($_POST['engomado'] > 0){
		$filtro = " AND a.engomado = '{$_POST['engomado']}'";
	}
	$query = "";
	if($_POST['mostrar'] == 0 || $_POST['mostrar'] == 1){
		$query .= "SELECT 'Entregado' as estatus, a.plaza, b.fecha as fecha_venta, b.cve as ticket, a.placa, a.fecha, a.hora, a.engomado, a.certificado, a.tecnico, a.linea FROM certificados a INNER JOIN cobro_engomado b ON a.plaza = b.plaza AND a.ticket = b.cve WHERE a.plaza IN ({$_POST['plaza']}) AND a.fecha BETWEEN '{$_POST['fecha_ini']}' AND '{$_POST['fecha_fin']}' AND a.estatus != 'C' {$filtro}";
	}
	if ($_POST['mostrar'] == 0) {
		$query .= " UNION ALL ";
	}
	if($_POST['mostrar'] == 0 || $_POST['mostrar'] == 2){
		$query .= "SELECT 'Cancelado' as estatus, a.plaza, a.fechaticket as fecha_venta, a.ticket, a.placa, a.fecha, a.hora, a.engomado, a.certificado, a.tecnico, a.linea FROM certificados_cancelados a WHERE a.plaza IN ({$_POST['plaza']}) AND a.fecha BETWEEN '{$_POST['fecha_ini']}' AND '{$_POST['fecha_fin']}' AND a.estatus != 'C' {$filtro}";
	}
	$filename = "ReporteEntregasGeneral.xls";
    header("Content-type: application/octet-stream");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=\"$filename\"\n");
    
	$res = mysql_query("SELECT a.estatus,  CONCAT(b.numero, ' ', b.nombre) as nombreplaza, a.fecha_venta, a.ticket, a.placa, a.fecha, a.hora, c.nombre as tipocertificado, a.certificado, d.nombre as nombretecnico, e.nombre as nombrelinea FROM ({$query}) a
		INNER JOIN plazas b ON b.cve = a.plaza 
		INNER JOIN engomados c ON c.cve = a.engomado
		LEFT JOIN tecnicos d ON d.cve = a.tecnico AND a.plaza = d.plaza
		LEFT JOIN cat_lineas e ON e.cve = a.linea AND e.plaza = a.plaza
		ORDER BY b.lista, a.fecha, a.hora");
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>Estatus</th><th>Nombre Plaza</th><th>Fecha Venta</th><th>Folio Ticket</th><th>Placa</th><th>Fecha Entrega/Cancelacion</th><th>Tipo Certificado</th><th>Numero Certificado</th><th>Tecnico</th><th>Linea</th></tr>';
	echo '</tr>';
	$x=0;
	while($row = mysql_fetch_array($res)){
		echo '<tr>';
		echo '<td align="center">'.$row['estatus'].'</td>';
		echo '<td>'.$row['nombreplaza'].'</td>';
		echo '<td align="center">'.substr($row['fecha_venta'], 0, 10).'</td>';
		echo '<td align="center">'.$row['ticket'].'</td>';
		echo '<td align="center">'.$row['placa'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		echo '<td>'.$row['tipocertificado'].'</td>';
		echo '<td align="center">'.$row['certificado'].'</td>';
		echo '<td>'.$row['nombretecnico'].'</td>';
		echo '<td>'.$row['nombrelinea'].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr  bgcolor="#E9F2F8"><th align="left" colspan="10">'.$x.' Registro(s)</th></tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==1){
	$filtro = "";
	if($_POST['engomado'] > 0){
		$filtro = " AND a.engomado = '{$_POST['engomado']}'";
	}
	$query = "";
	if($_POST['mostrar'] == 0 || $_POST['mostrar'] == 1){
		$query .= "SELECT 'Entregado' as estatus, a.plaza, b.fecha as fecha_venta, b.cve as ticket, a.placa, a.fecha, a.hora, a.engomado, a.certificado, a.tecnico, a.linea FROM certificados a INNER JOIN cobro_engomado b ON a.plaza = b.plaza AND a.ticket = b.cve WHERE a.plaza IN ({$_POST['plaza']}) AND a.fecha BETWEEN '{$_POST['fecha_ini']}' AND '{$_POST['fecha_fin']}' AND a.estatus != 'C' {$filtro}";
	}
	if ($_POST['mostrar'] == 0) {
		$query .= " UNION ALL ";
	}
	if($_POST['mostrar'] == 0 || $_POST['mostrar'] == 2){
		$query .= "SELECT 'Cancelado' as estatus, a.plaza, a.fechaticket as fecha_venta, a.ticket, a.placa, a.fecha, a.hora, a.engomado, a.certificado, a.tecnico, a.linea FROM certificados_cancelados a WHERE a.plaza IN ({$_POST['plaza']}) AND a.fecha BETWEEN '{$_POST['fecha_ini']}' AND '{$_POST['fecha_fin']}' AND a.estatus != 'C' {$filtro}";
	}
	$res = mysql_query("SELECT a.estatus,  CONCAT(b.numero, ' ', b.nombre) as nombreplaza, a.fecha_venta, a.ticket, a.placa, a.fecha, a.hora, c.nombre as tipocertificado, a.certificado, d.nombre as nombretecnico, e.nombre as nombrelinea FROM ({$query}) a
		INNER JOIN plazas b ON b.cve = a.plaza 
		INNER JOIN engomados c ON c.cve = a.engomado
		LEFT JOIN tecnicos d ON d.cve = a.tecnico AND a.plaza = d.plaza
		LEFT JOIN cat_lineas e ON e.cve = a.linea AND e.plaza = a.plaza
		ORDER BY b.lista, a.fecha, a.hora");
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>Estatus</th><th>Nombre Plaza</th><th>Fecha Venta</th><th>Folio Ticket</th><th>Placa</th><th>Fecha Entrega/Cancelacion</th><th>Tipo Certificado</th><th>Numero Certificado</th><th>Tecnico</th><th>Linea</th></tr>';
	echo '</tr>';
	$x=0;
	while($row = mysql_fetch_array($res)){
		rowb();
		echo '<td align="center">'.$row['estatus'].'</td>';
		echo '<td>'.$row['nombreplaza'].'</td>';
		echo '<td align="center">'.substr($row['fecha_venta'], 0, 10).'</td>';
		echo '<td align="center">'.$row['ticket'].'</td>';
		echo '<td align="center">'.$row['placa'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		echo '<td>'.$row['tipocertificado'].'</td>';
		echo '<td align="center">'.$row['certificado'].'</td>';
		echo '<td>'.$row['nombretecnico'].'</td>';
		echo '<td>'.$row['nombrelinea'].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr  bgcolor="#E9F2F8"><th align="left" colspan="10">'.$x.' Registro(s)</th></tr>';
	echo '</table>';
	exit();
}


top($_SESSION);

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onclick="document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');atcr(\'certificadosxplaza.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'\',\'_blank\',\'101\',\'0\');"><img src="images/b_print.png" border="0" title="Imprimir"></a>&nbsp;Excell</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Fin</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td align="left">Tipo Certificado</td><td><select name="engomado" id="engomado"><option value="0">Todos</option>';
	foreach($array_engomado as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select>';
	echo '<tr><td align="left">Plaza</td><td><select multiple="multiple" name="plazas" id="plazas">';
	foreach($array_plazas as $k=>$v){
		echo '<option value="'.$k.'" selected>'.$v.'</option>';
	}
	echo '</select>';
	echo '<input type="hidden" name="plaza" id="plaza" value=""></td></tr>';
	echo '<tr><td align="left">Mostrar</td><td><select name="mostrar" id="mostrar"><option value="0">Todos</option>';
	echo '<option value="1">Entregas</option>';
	echo '<option value="2">Cancelaciones</option>';
	echo '</select>';
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
			objeto.open("POST","rep_certificados_general.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&mostrar="+document.getElementById("mostrar").value+"&engomado="+document.getElementById("engomado").value+"&plaza="+document.getElementById("plaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					document.getElementById("Resultados").innerHTML = objeto.responseText;
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

	
	
	</Script>
	';

	
}
	
bottom();
?>