<?php
include("main.php");
$array_tipo_pago = array();
$res = mysql_query("SELECT * FROM tipos_pago WHERE mostrar_ventas=1 AND cve!=6 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}
$rsUsuario=mysql_query("SELECT * FROM plazas where estatus!='I' ORDER BY numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}


$array_tipo_depositante = array(
	-1 => 'Particular',
	0 => 'Taller',
	1 => 'Agencias'
);
if($_POST['cmd'] == 100){
	require_once('../dompdf/dompdf_config.inc.php');
		$html='<html><head>
      <style type="text/css">
	                    top  lado      ladoiz
		 @page{ margin: 5in 0.5in 1px 0.5in;}
		</style>
		 </head><body>';
	
	$res = mysql_query("SELECT a.placa, IFNULL(b.agencia,-1) as tipo_depositante, SUM(IF(a.tipo_venta=0,1,0)) as importe, SUM(IF(a.tipo_venta=4,1,0)) as intento 
		FROM cobro_engomado a LEFT JOIN depositantes b ON b.cve = a.depositante AND b.plaza = a.plaza 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus != 'C' AND a.tipo_venta IN (0,4) AND 
		a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
		GROUP BY a.placa, IFNULL(b.agencia,-1)") or die(mysql_error());
	$array_total = array();
	$array_desglose = array();
	$maxintento = 0;
	while($row = mysql_fetch_array($res)){
		if($maxintento < $row['intento']) $maxintento = $row['intento'];
		$cant_pagos = ($row['importe'] > 1) ? 2 : 1;
		$array_total[$cant_pagos][$row['intento']]++;
		$array_desglose[$row['tipo_depositante']][$cant_pagos][$row['intento']]++;
	}
	$html.= '<h3>'.$array_plazas[$_POST['plazausuario']].'</h3>';
	$html.= '<h3>Periodo del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h3>';
	$html.= '<h3>Total</h3>';
	$html.= '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="" class="" style="font-size:13px">
	<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>497</th>';
	for($i=1; $i<=$maxintento; $i++){
		$html.= '<th>'.$i.' Pago(s) de 250</th>';
	}
	$html.= '</tr>';
	$total = array();
	$html.= '<tr><td>1 Pago</td>';
	$c=0;
	for($i=0; $i<=$maxintento; $i++){
		$html.= '<td align="center">'.$array_total[1][$i].'</td>';
		$total[$c]+=$array_total[1][$i];$c++;
	}
	$html.= '</tr>';
	$html.= '<tr><td>Mas de 1 Pago</td>';
	$c=0;
	for($i=0; $i<=$maxintento; $i++){
		$html.= '<td align="center">'.$array_total[2][$i].'</td>';
		$total[$c]+=$array_total[2][$i];$c++;
	}
	$html.= '</tr>';
	$html.= '<tr bgcolor="#E9F2F8"><th>Total</th>';
	foreach($total as $t) $html.= '<th>'.$t.'</th>';
	$html.= '</tr>';
	$html.= '</table>';
	foreach($array_tipo_depositante as $k=>$v){
		$html.= '<h3>'.$v.'</h3>';
		$html.= '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="" class="" style="font-size:13px">
		<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>497</th>';
		for($i=1; $i<=$maxintento; $i++){
			$html.= '<th>'.$i.' Pago(s) de 250</th>';
		}
		$html.= '</tr>';
		$total = array();
		$html.= '<tr><td>1 Pago</td>';
		$c=0;
		for($i=0; $i<=$maxintento; $i++){
			$html.= '<td align="center">'.$array_desglose[$k][1][$i].'</td>';
			$total[$c]+=$array_desglose[$k][1][$i];$c++;
		}
		$html.= '</tr>';
		$html.= '<tr><td>Mas de 1 Pago</td>';
		$c=0;
		for($i=0; $i<=$maxintento; $i++){
			$html.= '<td align="center">'.$array_desglose[$k][2][$i].'</td>';
			$total[$c]+=$array_desglose[$k][2][$i];$c++;
		}
		$html.= '</tr>';
		$html.= '<tr bgcolor="#E9F2F8"><th>Total</th>';
		foreach($total as $t) $html.= '<th>'.$t.'</th>';
		$html.= '</tr>';
		$html.= '</table>';
	}
	$html.= '</body></html>';
			 	$mipdf= new DOMPDF();
//	$mipdf->margin: "0";
	//$mipdf->set_paper("A4", "portrait");
//	$mipdf->set_paper("A4", "portrait");
    
//    $mipdf->set_margin("Legal", "landscape");
	$mipdf->set_paper("Legal", "landscape");
	$mipdf->load_html($html);
	$mipdf->render();
	$mipdf ->stream();
	exit();
}


if($_POST['ajax'] == 1){
	$res = mysql_query("SELECT a.placa, IFNULL(b.agencia,-1) as tipo_depositante, SUM(IF(a.tipo_venta=0,1,0)) as importe, SUM(IF(a.tipo_venta=4,1,0)) as intento 
		FROM cobro_engomado a LEFT JOIN depositantes b ON b.cve = a.depositante AND b.plaza = a.plaza 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus != 'C' AND a.tipo_venta IN (0,4) AND 
		a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
		GROUP BY a.placa, IFNULL(b.agencia,-1)") or die(mysql_error());
	$array_total = array();
	$array_desglose = array();
	$maxintento = 0;
	while($row = mysql_fetch_array($res)){
		if($maxintento < $row['intento']) $maxintento = $row['intento'];
		$cant_pagos = ($row['importe'] > 1) ? 2 : 1;
		$array_total[$cant_pagos][$row['intento']]++;
		$array_desglose[$row['tipo_depositante']][$cant_pagos][$row['intento']]++;
	}
	echo '<h3>Total</h3>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">
	<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>497</th>';
	for($i=1; $i<=$maxintento; $i++){
		echo '<th>'.$i.' Pago(s) de 250</th>';
	}
	echo '</tr>';
	$total = array();
	echo '<tr><td>1 Pago</td>';
	$c=0;
	for($i=0; $i<=$maxintento; $i++){
		echo '<td align="center">'.$array_total[1][$i].'</td>';
		$total[$c]+=$array_total[1][$i];$c++;
	}
	echo '</tr>';
	echo '<tr><td>Mas de 1 Pago</td>';
	$c=0;
	for($i=0; $i<=$maxintento; $i++){
		echo '<td align="center">'.$array_total[2][$i].'</td>';
		$total[$c]+=$array_total[2][$i];$c++;
	}
	echo '</tr>';
	echo '<tr bgcolor="#E9F2F8"><th>Total</th>';
	foreach($total as $t) echo '<th>'.$t.'</th>';
	echo '</tr>';
	echo '</table>';
	foreach($array_tipo_depositante as $k=>$v){
		echo '<h3>'.$v.'</h3>';
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">
		<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>497</th>';
		for($i=1; $i<=$maxintento; $i++){
			echo '<th>'.$i.' Pago(s) de 250</th>';
		}
		echo '</tr>';
		$total = array();
		echo '<tr><td>1 Pago</td>';
		$c=0;
		for($i=0; $i<=$maxintento; $i++){
			echo '<td align="center">'.$array_desglose[$k][1][$i].'</td>';
			$total[$c]+=$array_desglose[$k][1][$i];$c++;
		}
		echo '</tr>';
		echo '<tr><td>Mas de 1 Pago</td>';
		$c=0;
		for($i=0; $i<=$maxintento; $i++){
			echo '<td align="center">'.$array_desglose[$k][2][$i].'</td>';
			$total[$c]+=$array_desglose[$k][2][$i];$c++;
		}
		echo '</tr>';
		echo '<tr bgcolor="#E9F2F8"><th>Total</th>';
		foreach($total as $t) echo '<th>'.$t.'</th>';
		echo '</tr>';
		echo '</table>';
	}
	exit();
}

top($_SESSION);
if($_POST['cmd']==0){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td> <a href="#" onClick="atcr(\'\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0" title="Imprimir"></a>Imprimir</td>
		</tr>
		</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '</table>';
	echo '<div id="Resultados">';
	echo '</div>';

	echo '
	<Script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","desglose_pagos2.php",true);
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
	</script>';
}
bottom();
?>