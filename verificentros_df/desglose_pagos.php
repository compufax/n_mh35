<?php
include("main.php");
$array_tipo_pago = array();
$res = mysql_query("SELECT * FROM tipos_pago WHERE mostrar_ventas=1 AND cve!=6 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}

$array_tipo_venta = array(
	0 => 'Venta de 497',
	4 => 'Venta de 250',
	3 => 'Reposicion'
);

if($_POST['ajax'] == 1){
	echo '<h3>Ventas</h3>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">
	<tr bgcolor="#E9F2F8"><th>Tipo de Venta</th>';
	foreach($array_tipo_pago as $k=>$v){
		echo '<th>'.$v.'</th>';
	}
	echo '<th>Importe</th><th>Pago Anticipado</th></tr>';
	$res = mysql_query("SELECT tipo_venta, tipo_pago, COUNT(cve) as cantidad, SUM(monto) as importe
		FROM cobro_engomado WHERE plaza = '".$_POST['plazausuario']."' AND estatus != 'C' AND tipo_venta IN (0,3,4) AND 
		fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY tipo_venta, tipo_pago");
	$array_datos = array();
	while($row = mysql_fetch_array($res)){
		$array_datos[$row[0]][$row[1]]['cantidad'] = $row['cantidad'];
		$array_datos[$row[0]][$row[1]]['importe'] = $row['importe'];
	}
	$totales = array();
	foreach($array_tipo_venta as $k=>$v){
		rowb();
		echo '<td>'.$v.'</td>';
		$c=0;
		$importe = 0;
		foreach($array_tipo_pago as $k1=>$v1){
			$totales[$c]+=$array_datos[$k][$k1]['cantidad'];$c++;
			$importe+=$array_datos[$k][$k1]['importe'];
			echo '<td align="right">'.$array_datos[$k][$k1]['cantidad'].'</td>';
		}
		$totales[$c]+=$importe;$c++;
		echo '<td align="right">'.$importe.'</td>';
		$totales[$c]+=$array_datos[$k][6]['cantidad'];$c++;
		echo '<td align="right">'.$array_datos[$k][6]['cantidad'].'</td>';
		echo '</tr>';
	}
	echo '<tr  bgcolor="#E9F2F8"><th>Totales</th>';
	foreach($totales as $t){
		echo '<th align="right">'.$t.'</th>';
	}
	echo '</tr></table>';
	echo '<h3>Pagos de Caja</h3>';
	$array_tipo_venta = array(1 => 'Pago Anticipado', 2=>'Recuperacion de Credito');
	$array_tipo_pago = array(1 => 'Contado', 2=>'Bancos');
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">
	<tr bgcolor="#E9F2F8"><th>Tipo de Pago</th><th>Contado</th><th>Banco</th><th>Importe</th></tr>';
	$res = mysql_query("SELECT IF(tipo_pago!=2, 1, 2) as tipo_pago, IF(forma_pago=1,1,2) as forma_pago, COUNT(cve) as cantidad, SUM(monto) as importe
		FROM pagos_caja WHERE plaza = '".$_POST['plazausuario']."' AND estatus != 'C' AND 
		fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY IF(tipo_pago!=2, 1, 2), IF(forma_pago=1,1,2)") or die(mysql_error());
	$array_datos = array();
	while($row = mysql_fetch_array($res)){
		$array_datos[$row[0]][$row[1]]['cantidad'] = $row['cantidad'];
		$array_datos[$row[0]][$row[1]]['importe'] = $row['importe'];
	}
	$totales = array();
	foreach($array_tipo_venta as $k=>$v){
		rowb();
		echo '<td>'.$v.'</td>';
		$c=0;
		$importe = 0;
		foreach($array_tipo_pago as $k1=>$v1){
			$totales[$c]+=$array_datos[$k][$k1]['importe'];$c++;
			$importe+=$array_datos[$k][$k1]['importe'];
			echo '<td align="right">'.number_format($array_datos[$k][$k1]['importe'],2).'</td>';
		}
		$totales[$c]+=$importe;$c++;
		echo '<td align="right">'.number_format($importe,2).'</td>';
		echo '</tr>';
	}
	echo '<tr  bgcolor="#E9F2F8"><th>Totales</th>';
	foreach($totales as $t){
		echo '<th align="right">'.number_format($t,2).'</th>';
	}
	echo '</tr></table>';
	exit();
}

top($_SESSION);
if($_POST['cmd']==0){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
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

	function buscarRegistros(btn)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","desglose_pagos.php",true);
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