<?php

include("main.php");

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$res=mysql_query("SELECT local,vende_seguros FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];
$VendeSeguros=$row[1];

$array_engomado = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND plazas like '%|".$_POST['plazausuario']."|%' AND entrega=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_precioengomado[$row['cve']]=$row['precio_compra'];
}

$res = mysql_query("SELECT * FROM anios_certificados  ORDER BY nombre DESC LIMIT 2");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
}

$array_tipo_pago = array();
$res = mysql_query("SELECT * FROM tipos_pago WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}

$array_motivos_intento = array();
$res = mysql_query("SELECT * FROM motivos_intento WHERE localidad IN (0,1) ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_motivos_intento[$row['cve']]=$row['nombre'];
}

$array_depositantes = array();
$res = mysql_query("SELECT * FROM depositantes WHERE plaza='".$_POST['plazausuario']."' AND edo_cuenta=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_depositantes[$row['cve']]=$row['nombre'];
}

if($_POST['ajax']==1){
	
	
	
	$filtro_fechas = "";
	//$filtro_fechas .= " AND LEFT(a.fecha,7) = '{$_POST['mes']}'";
	$filtro_fechas .= " AND a.fecha BETWEEN '{$_POST['fecha_ini']}' AND '{$_POST['fecha_fin']}'";

	$filtro_depositantes = "";
	if($_POST['tipo_cliente']!="all"){
		$filtro_depositantes.="  AND IFNULL(c.agencia,-1) = '".$_POST['tipo_cliente']."'";
	}

	$aforo=0;
	$res = mysql_query("SELECT COUNT(cve) FROM certificados a WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C'  {$filtro_fechas}");
	$row =mysql_fetch_array($res);
	$aforo = $row[0];
	$tentregas=array();
	$entregas=array();
	$placas_pagos=array();
	$tipo_depositante = array();
	$res1=mysql_query("SELECT TRIM(b.placa), a.engomado, b.tipo_venta, IFNULL(c.agencia,'-1') as agencia
		FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		LEFT JOIN depositantes c ON c.plaza = b.plaza AND c.cve = b.depositante
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND b.estatus!='C'  {$filtro_fechas} {$filtro_depositantes} ORDER BY b.cve") or die(mysql_error());
	$total = 0;
	while($row1=mysql_fetch_array($res1)){
		$tipo_depositante[$row1['agencia']]++;
		$entregas[$row1['tipo_venta']]['aforo']++;
		if($row1['engomado']==19){
			$entregas[$row1['tipo_venta']]['rechazos']++;
		}
		else{
			if($placas_pagos[$row1[0]]==1){
				$tipo='reverificacion';
			}
			else{
				$tipo='verificacion';
			}
			$entregas[$row1['tipo_venta']][$tipo.$row1['engomado']]++;
			$placas_pagos[$row1[0]]=1;
		}
	}

	
	$orden_engomado = array(3,2,5,1);

	$array_nomagencia = array(-1=>'Particulares',0=>'Taller', 1=>'Agencia');

	foreach ($array_nomagencia as $key => $value) {
		echo '<h3>'.$value.': '.$tipo_depositante[$key].'</h3>';
	}

	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Tipos Venta</th><th>Aforo</th>';
	foreach($orden_engomado as $v){
		echo '<th>'.$array_engomado[$v].'</th>';
	}
	echo '<th>RECHAZOS</th>';
	foreach($orden_engomado as $v){
		echo '<th>RE'.$array_engomado[$v].'</th>';
	}
	echo '</tr>';

	$totales=array();
	
	$orden_pago=array(2=>'Cortesia',0=>'Con Pago',1=>'Sin pago');
	foreach($orden_pago as $tipoventa => $nombre){
		$c=0;
		rowb();
		echo '<td>'.$nombre.'</td>';
		echo '<td align="center">'.$entregas[$tipoventa]['aforo'].'</td>';
		$totales[$c]+=$entregas[$tipoventa]['aforo'];$c++;
		foreach($orden_engomado as $v){
			echo '<td align="center">'.$entregas[$tipoventa]['verificacion'.$v].'</td>';
			$totales[$c]+=$entregas[$tipoventa]['verificacion'.$v];$c++;	
		}
		echo '<td align="center">'.$entregas[$tipoventa]['rechazos'].'</td>';
		$totales[$c]+=$entregas[$tipoventa]['rechazos'];$c++;
		foreach($orden_engomado as $v){
			echo '<td align="center">'.$entregas[$tipoventa]['reverificacion'.$v].'</td>';
			$totales[$c]+=$entregas[$tipoventa]['reverificacion'.$v];$c++;	
		}
		echo '</tr>';
	}

	echo '<tr bgcolor="#E9F2F8"><th>Totales</th>';
	foreach($totales as $t) echo '<th>'.$t.'</th>';
	echo '</tr>';
	$res1=mysql_query("SELECT a.engomado
		FROM certificados_cancelados a 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C'  {$filtro_fechas} ORDER BY a.cve") or die(mysql_error());
	$cancelados=array();
	while($row1 = mysql_fetch_array($res1)){
		$cancelados['verificacion'.$row1[0]]++;
	}
	rowb();
	echo '<th>Cancelados</th>';
	$c=0;
	echo '<th>'.array_sum($cancelados).'</td>';
	$totales[$c]+=array_sum($cancelados);$c++;
	foreach($orden_engomado as $v){
		echo '<th>'.$cancelados['verificacion'.$v].'</td>';
		$totales[$c]+=$cancelados['verificacion'.$v];$c++;
	}
	echo '<th>'.$cancelados['verificacion19'].'</td>';
	$totales[$c]+=$cancelados['verificacion19'];$c++;
	foreach($orden_engomado as $v){
		echo '<th>&nbsp;</th>';
	}
	echo '</tr>';
	echo '<tr bgcolor="#E9F2F8"><th>Totales con Cancelados</th>';
	foreach($totales as $t) echo '<th>'.$t.'</th>';
	echo '</tr>';
	echo '</table>';
	echo '<br><br>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$total2=array();
	echo '<tr bgcolor="#E9F2F8"><th>Tipo de Verificacion</th><th>Costo</th><th>Cantidad Cortesia</th><th>Importe Cortesia</th>
	<th>Cantidad con Pago</th><th>Importe con Pago</th><th>Cantidad sin Pago</th><th>Importe sin Pago</th>
	<th>Cantidad Cancelados</th><th>Importe Cancelados</th>
	<th>Cantidad Total</th><th>Importe Total</th></tr>';
	$x=1;
	foreach($orden_engomado as $v){
		rowb();
		echo '<td>'.$array_engomado[$v].'</td>';
		echo '<td align="right">'.$array_precioengomado[$v].'</td>';
		$c=0;
		echo '<td align="right">'.$entregas[2]['verificacion'.$v].'</td>';
		$totales2[$c]+=$entregas[2]['verificacion'.$v];$c++;
		echo '<td align="right">'.number_format($entregas[2]['verificacion'.$v]*$array_precioengomado[$v],2).'</td>';
		$totales2[$c]+=$entregas[2]['verificacion'.$v]*$array_precioengomado[$v];$c++;
		echo '<td align="right">'.$entregas[0]['verificacion'.$v].'</td>';
		$totales2[$c]+=$entregas[0]['verificacion'.$v];$c++;
		echo '<td align="right">'.number_format($entregas[0]['verificacion'.$v]*$array_precioengomado[$v],2).'</td>';
		$totales2[$c]+=$entregas[0]['verificacion'.$v]*$array_precioengomado[$v];$c++;
		echo '<td align="right">'.$entregas[1]['verificacion'.$v].'</td>';
		$totales2[$c]+=$entregas[1]['verificacion'.$v];$c++;
		echo '<td align="right">'.number_format($entregas[1]['verificacion'.$v]*$array_precioengomado[$v],2).'</td>';
		$totales2[$c]+=$entregas[1]['verificacion'.$v]*$array_precioengomado[$v];$c++;
		echo '<td align="right">'.$cancelados['verificacion'.$v].'</td>';
		$totales2[$c]+=$cancelados['verificacion'.$v];$c++;
		echo '<td align="right">'.number_format($cancelados['verificacion'.$v]*$array_precioengomado[$v],2).'</td>';
		$totales2[$c]+=$cancelados['verificacion'.$v]*$array_precioengomado[$v];$c++;
		echo '<td align="right">'.$totales[$x].'</td>';
		$totales2[$c]+=$totales[$x];$c++;
		echo '<td align="right">'.number_format($totales[$x]*$array_precioengomado[$v],2).'</td>';
		$totales2[$c]+=$totales[$x]*$array_precioengomado[$v];$c++;
		echo '</tr>';
		$x++;
	}
	rowb();
	echo '<td>'.$array_engomado[19].'</td>';
	echo '<td align="right">'.$array_precioengomado[19].'</td>';
	$c=0;
	$v=19;
	echo '<td align="right">'.$entregas[2]['rechazos'].'</td>';
	$totales2[$c]+=$entregas[2]['rechazos'];$c++;
	echo '<td align="right">'.number_format($entregas[2]['rechazos']*$array_precioengomado[$v],2).'</td>';
	$totales2[$c]+=$entregas[2]['rechazos']*$array_precioengomado[$v];$c++;
	echo '<td align="right">'.$entregas[0]['rechazos'].'</td>';
	$totales2[$c]+=$entregas[0]['rechazos'];$c++;
	echo '<td align="right">'.number_format($entregas[0]['rechazos']*$array_precioengomado[$v],2).'</td>';
	$totales2[$c]+=$entregas[0]['rechazos']*$array_precioengomado[$v];$c++;
	echo '<td align="right">'.$entregas[1]['rechazos'].'</td>';
	$totales2[$c]+=$entregas[1]['rechazos'];$c++;
	echo '<td align="right">'.number_format($entregas[1]['rechazos']*$array_precioengomado[$v],2).'</td>';
	$totales2[$c]+=$entregas[1]['rechazos']*$array_precioengomado[$v];$c++;
	echo '<td align="right">'.$cancelados['verificacion19'].'</td>';
	$totales2[$c]+=$cancelados['verificacion19'];$c++;
	echo '<td align="right">'.number_format($cancelados['verificacion19']*$array_precioengomado[$v],2).'</td>';
	$totales2[$c]+=$cancelados['verificacion19']*$array_precioengomado[$v];$c++;
	echo '<td align="right">'.$totales[$x].'</td>';
	$totales2[$c]+=$totales[$x];$c++;
	echo '<td align="right">'.number_format($totales[$x]*$array_precioengomado[19],2).'</td>';
	$totales2[$c]+=$totales[$x]*$array_precioengomado[$v];$c++;
	echo '</tr>';
	$x++;

	foreach($orden_engomado as $v){
		rowb();
		echo '<td>RE'.$array_engomado[$v].'</td>';
		echo '<td align="right">'.$array_precioengomado[$v].'</td>';
		$c=0;
		echo '<td align="right">'.$entregas[2]['reverificacion'.$v].'</td>';
		$totales2[$c]+=$entregas[2]['reverificacion'.$v];$c++;
		echo '<td align="right">'.number_format($entregas[2]['reverificacion'.$v]*$array_precioengomado[$v],2).'</td>';
		$totales2[$c]+=$entregas[2]['reverificacion'.$v]*$array_precioengomado[$v];$c++;
		echo '<td align="right">'.$entregas[0]['reverificacion'.$v].'</td>';
		$totales2[$c]+=$entregas[0]['reverificacion'.$v];$c++;
		echo '<td align="right">'.number_format($entregas[0]['reverificacion'.$v]*$array_precioengomado[$v],2).'</td>';
		$totales2[$c]+=$entregas[0]['reverificacion'.$v]*$array_precioengomado[$v];$c++;
		echo '<td align="right">'.$entregas[1]['reverificacion'.$v].'</td>';
		$totales2[$c]+=$entregas[1]['reverificacion'.$v];$c++;
		echo '<td align="right">'.number_format($entregas[1]['reverificacion'.$v]*$array_precioengomado[$v],2).'</td>';
		$totales2[$c]+=$entregas[1]['reverificacion'.$v]*$array_precioengomado[$v];$c++;
		echo '<td>&nbsp;</td>';$c++;
		echo '<td>&nbsp;</td>';$c++;
		echo '<td align="right">'.$totales[$x].'</td>';
		$totales2[$c]+=$totales[$x];$c++;
		echo '<td align="right">'.number_format($totales[$x]*$array_precioengomado[$v],2).'</td>';
		$totales2[$c]+=$totales[$x]*$array_precioengomado[$v];$c++;
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="2">Totales</th>';
	foreach($totales2 as $k=>$t){
		if($k==0)
			echo '<th align="right">'.$t.'</th>';
		else
			echo '<th align="right">'.number_format($t,2).'</th>';
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
	echo '<tr style="display:none;"><td>Mes</td><td><select name="mes" id="mes">';
	echo '<option value="'.date('Y-m').'">'.date('Y-m').'</option>';
	$mes = date( "Y-m" , strtotime ( "-1 month" , strtotime(date('Y-m').'-01') ) );
	while($mes>='2016-01'){
		echo '<option value="'.$mes.'">'.$mes.'</option>';
		$mes = date( "Y-m" , strtotime ( "-1 month" , strtotime($mes.'-01') ) );
	}
	echo '</select></td></tr>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.date('Y-m').'-01" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Tipo Cliente</td><td><select name="tipo_cliente" id="tipo_cliente"><option value="all" selected>Todos</option>';
	echo '<option value="-1">Particulares</option>';
	echo '<option value="0">Talleres</option>';
	echo '<option value="1">Agencias</option>';
	echo '</select></td></tr>';
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
			objeto.open("POST","reverificacion2.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_fin="+document.getElementById("fecha_fin").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&tipo_cliente="+document.getElementById("tipo_cliente").value+"&mes="+document.getElementById("mes").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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