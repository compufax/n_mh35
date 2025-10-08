<?php
include("main.php");
$array_tipo_pago=array();
$res = mysql_query("SELECT * FROM tipos_pago WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}

if($_POST['ajax']==1){

	$datos = array();
	$res=mysql_query("SELECT a.fecha, b.tipo_venta, b.tipo_pago, IF(a.engomado!=19,1,0) as conholograma, 
		c.tipo_venta as tipo_venta_intento, c.tipo_pago as tipo_pago_intento
		FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		LEFT JOIN cobro_engomado c ON c.plaza = b.plaza AND c.cve = b.ticketpago 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND b.estatus!='C' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'") or die(mysql_error());
	while($row=mysql_fetch_array($res)){
		if($row['tipo_venta']==1){
			$datos[$row['fecha']]['totalintentos']++;
			$datos[$row['fecha']]['tipopagointento'.$row['tipo_pago_intento']][$row['conholograma']]++;
			$datos[$row['fecha']]['intentos'][$row['conholograma']]++;
		}
		elseif($row['tipo_venta']==2){
			$datos[$row['fecha']]['totalcortesias']++;
			$datos[$row['fecha']]['cortesias'][$row['conholograma']]++;
		}
		else{
			$datos[$row['fecha']]['total'.$row['tipo_pago']]++;
			$datos[$row['fecha']]['tipopago'.$row['tipo_pago']][$row['conholograma']]++;
		}
		$datos[$row['fecha']]['aforo']++;
		$datos[$row['fecha']]['utilizados'][$row['conholograma']]++;
	}
	$res=mysql_query("SELECT a.fecha, a.tipo_venta, a.tipo_pago
		FROM cobro_engomado a 
		LEFT JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket AND b.fecha = a.fecha
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND ISNULL(b.cve) AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'") or die(mysql_error());
	while($row=mysql_fetch_array($res)){
		if($row['tipo_venta']==2){
			$datos[$row['fecha']]['cortesiasnv']++;
		}
		else{
			$datos[$row['fecha']]['totalnv'.$row['tipo_pago']]++;
		}
	}
	
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th rowspan="2">Fecha</th>';
	foreach($array_tipo_pago as $v){
		echo '<th rowspan="2">'.$v.'</th>';
	}
	echo '<th rowspan="2">Intentos</th><th rowspan="2">Cortesias</th><th rowspan="2">Aforo</th>';
	foreach($array_tipo_pago as $v){
		echo '<th colspan="2">'.$v.'</th>';
		echo '<th colspan="3">Intentos '.$v.'</th>';
	}
	echo '<th colspan="3">Intentos</th>';
	echo '<th colspan="3">Cortesias</th>';
	echo '<th colspan="'.(count($array_tipo_pago)+1).'">Nuevas Ventas</th>';
	echo '<th colspan="2">Utilizados</th></tr>';
	echo '<tr bgcolor="#E9F2F8">';
	foreach($array_tipo_pago as $v){
		echo '<th>H</th>';
		echo '<th>R</th>';
		echo '<th>Cant.</th>';
		echo '<th>H</th>';
		echo '<th>R</th>';
	}
	echo '<th>Cant.</th>';
	echo '<th>H</th>';
	echo '<th>R</th>';
	echo '<th>Cant.</th>';
	echo '<th>H</th>';
	echo '<th>R</th>';
	foreach($array_tipo_pago as $v){
		echo '<th>'.$v.'</th>';
	}
	echo '<th>Cortesia</th>';
	echo '<th>H</th>';
	echo '<th>R</th></tr>';
	$total=array();
	$fecha=$_POST['fecha_ini'];
	while($fecha<=$_POST['fecha_fin']){
		rowb();
		echo '<td align="center">'.$fecha.'</td>';
		$c=0;
		foreach($array_tipo_pago as $k=>$v){
			echo '<td align="right">'.intval($datos[$fecha]['total'.$k]).'</td>';
			$total[$c]+=$datos[$fecha]['total'.$k];$c++;
		}
		echo '<td align="right">'.intval($datos[$fecha]['totalintentos']).'</td>';
		$total[$c]+=$datos[$fecha]['totalintentos'];$c++;
		echo '<td align="right">'.intval($datos[$fecha]['totalcortesias']).'</td>';
		$total[$c]+=$datos[$fecha]['totalcortesias'];$c++;
		echo '<td align="right">'.intval($datos[$fecha]['aforo']).'</td>';
		$total[$c]+=$datos[$fecha]['aforo'];$c++;
		foreach($array_tipo_pago as $k=>$v){
			echo '<td align="right">'.intval($datos[$fecha]['tipopago'.$k][1]).'</td>';
			$total[$c]+=$datos[$fecha]['tipopago'.$k][1];$c++;
			echo '<td align="right">'.intval($datos[$fecha]['tipopago'.$k][0]).'</td>';
			$total[$c]+=$datos[$fecha]['tipopago'.$k][0];$c++;
			echo '<td align="right">'.array_sum($datos[$fecha]['tipopagointento'.$k]).'</td>';
			$total[$c]+=array_sum($datos[$fecha]['tipopagointento'.$k]);$c++;
			echo '<td align="right">'.intval($datos[$fecha]['tipopagointento'.$k][1]).'</td>';
			$total[$c]+=$datos[$fecha]['tipopagointento'.$k][1];$c++;
			echo '<td align="right">'.intval($datos[$fecha]['tipopagointento'.$k][0]).'</td>';
			$total[$c]+=$datos[$fecha]['tipopagointento'.$k][0];$c++;
		}
		echo '<td align="right">'.array_sum($datos[$fecha]['intentos']).'</td>';
		$total[$c]+=array_sum($datos[$fecha]['intentos']);$c++;
		echo '<td align="right">'.intval($datos[$fecha]['intentos'][1]).'</td>';
		$total[$c]+=$datos[$fecha]['intentos'][1];$c++;
		echo '<td align="right">'.intval($datos[$fecha]['intentos'][0]).'</td>';
		$total[$c]+=$datos[$fecha]['intentos'][0];$c++;
		echo '<td align="right">'.array_sum($datos[$fecha]['cortesias']).'</td>';
		$total[$c]+=array_sum($datos[$fecha]['cortesias']);$c++;
		echo '<td align="right">'.intval($datos[$fecha]['cortesias'][1]).'</td>';
		$total[$c]+=$datos[$fecha]['cortesias'][1];$c++;
		echo '<td align="right">'.intval($datos[$fecha]['cortesias'][0]).'</td>';
		$total[$c]+=$datos[$fecha]['cortesias'][0];$c++;
		foreach($array_tipo_pago as $k=>$v){
			echo '<td align="right">'.intval($datos[$fecha]['totalnv'.$k]).'</td>';
			$total[$c]+=$datos[$fecha]['totalnv'.$k];$c++;
		}
		echo '<td align="right">'.intval($datos[$fecha]['cortesiasnv']).'</td>';
		$total[$c]+=$datos[$fecha]['cortesiasnv'];$c++;
		echo '<td align="right">'.intval($datos[$fecha]['utilizados'][1]).'</td>';
		$total[$c]+=$datos[$fecha]['utilizados'][1];$c++;
		echo '<td align="right">'.intval($datos[$fecha]['utilizados'][0]).'</td>';
		$total[$c]+=$datos[$fecha]['utilizados'][0];$c++;
		$fecha = date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
	}
	
	echo '<tr bgcolor="#E9F2F8"><th align="left" colspan="1">Totales:</th>';
	foreach($total as $k=>$v){
		echo '<th align="right">'.number_format($v,0).'</th>';
	}
	echo '</tr>';
	echo '</table>';
	exit();
}

top($_SESSION);

if ($_POST['cmd']<1) {
	$nivelUsuario=nivelUsuario();
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	echo '
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr ><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="'.substr(fechaLocal(),0,8).'01">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr ><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="'.fechaLocal().'">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';

	echo '</table>';
	echo '<br>';
	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">
	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","detalleentregasxdia.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
	//buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.

	
	</Script>
	';

	
}

bottom();
?>