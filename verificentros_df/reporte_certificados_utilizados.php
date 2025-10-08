<?php
include("main.php");

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);
$PlazaLocalidad=$Plaza['localidad_id'];
$array_engomado = array();
$array_engomado2 = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' ORDER BY nombre");
$res = mysql_query("SELECT numero, nombre, max(precio) as precio, group_concat(cve) as cves FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND entrega=1 GROUP BY numero ORDER BY numero");
while($row=mysql_fetch_array($res)){
	$res1=mysql_query("SELECT nombre,precio,abreviatura FROM engomados WHERE localidad = '".$Plaza['localidad_id']."'  AND numero='".$row['numero']."' AND entrega=1 ORDER BY cve");
	$row1=mysql_fetch_array($res1);
	$array_engomado['normal'][$row['numero']]['nombre']=$row1['nombre'];
	$array_engomado['normal'][$row['numero']]['precio']=$row1['precio'];
	$array_engomado['normal'][$row['numero']]['cves']=$row['cves'];
	$array_engomado['normal'][$row['numero']]['cant']=0;
	$array_engomado['cancelados'][$row['numero']]['nombre']=$row1['abreviatura'];
	$array_engomado['cancelados'][$row['numero']]['precio']=$row1['precio'];
	$array_engomado['cancelados'][$row['numero']]['cves']=$row['cves'];
	$array_engomado['cancelados'][$row['numero']]['cant']=0;
	if($row1['nombre']!='RECHAZO'){
		$array_engomado['cortesia'][$row['numero']]['nombre']=$row1['abreviatura'];
		$array_engomado['cortesia'][$row['numero']]['precio']=$row1['precio'];
		$array_engomado['cortesia'][$row['numero']]['cves']=$row['cves'];
		$array_engomado['cortesia'][$row['numero']]['cant']=0;
		$array_engomado['reverificacion'][$row['numero']]['nombre']=$row1['abreviatura'];
		$array_engomado['reverificacion'][$row['numero']]['precio']=$row1['precio'];
		$array_engomado['reverificacion'][$row['numero']]['cves']=$row['cves'];
		$array_engomado['reverificacion'][$row['numero']]['cant']=0;
		$array_engomado['pagos_anteriores'][$row['numero']]['nombre']=$row1['abreviatura'];
		$array_engomado['pagos_anteriores'][$row['numero']]['precio']=$row1['precio'];
		$array_engomado['pagos_anteriores'][$row['numero']]['cves']=$row['cves'];
		$array_engomado['pagos_anteriores'][$row['numero']]['cant']=0;
	}
}

$array_motivos_intento = array();
$res = mysql_query("SELECT * FROM motivos_intento WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_motivos_intento[$row['cve']]=$row['nombre'];
}

if($_POST['cmd']==100){
	echo '<h2>'.$array_plaza[$_POST['plazausuario']].'<br>Reporte de Certificados Utilizados</h2>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr><th bgcolor="#E9F2F8" rowspan="2">Dia</th>';
	foreach($array_engomado['normal'] as $k=>$v) echo '<th rowspan="2" bgcolor="#E9F2F8">'.$v['nombre'].'</th>';
	echo '<th rowspan="2">&nbsp;</th><th bgcolor="#E9F2F8" colspan="'.count($array_engomado['cortesia']).'">Cortesias</th>
	<th bgcolor="#E9F2F8" colspan="'.count($array_engomado['cancelados']).'">Cancelados</th>
	<th bgcolor="#E9F2F8" colspan="'.count($array_engomado['reverificacion']).'">Reverificaciones</th>
	<th bgcolor="#E9F2F8" colspan="'.count($array_engomado['pagos_anteriores']).'">Pagos Anteriores</th>';
	echo '<th bgcolor="#E9F2F8" rowspan="2">Total</th></tr>';
	echo '<tr>';
	foreach($array_engomado['cortesia'] as $k=>$v) echo '<th bgcolor="#E9F2F8">'.$v['nombre'].'</th>';
	foreach($array_engomado['cancelados'] as $k=>$v) echo '<th bgcolor="#E9F2F8">'.$v['nombre'].'</th>';
	foreach($array_engomado['reverificacion'] as $k=>$v) echo '<th bgcolor="#E9F2F8">'.$v['nombre'].'</th>';
	foreach($array_engomado['pagos_anteriores'] as $k=>$v) echo '<th bgcolor="#E9F2F8">'.$v['nombre'].'</th>';
	echo '</tr>';
	
	$fecha = $_POST['fecha_ini'];
	while($fecha<=$_POST['fecha_fin']){
		rowb();
		foreach($array_engomado['normal'] as $k=>$v){
			$array_engomado['normal'][$k]['dia']=0;
			$array_engomado['cancelados'][$k]['dia']=0;
			if($v['nombre']!='RECHAZO'){
				$array_engomado['cortesia'][$k]['dia']=0;
				$array_engomado['reverificacion'][$k]['dia']=0;
				$array_engomado['pagos_anteriores'][$k]['dia']=0;
			}
		}
		$total = 0;
		echo '<td align="center">'.$fecha.'</td>';
		foreach($array_engomado['normal'] as $k=>$v){
			$select = "SELECT a.* FROM (
			SELECT a.placa,a.ticket,b.tipo_venta, b.fecha as fechaticket,a.cve, IF(b.fecha!=a.fecha,6,b.tipo_pago) as tipo_pago, 0 as cancelado, a.engomado as engomadoentrega, 
			if(b.tipo_pago=6 OR b.fecha!=a.fecha,0,b.monto) as monto, a.certificado as certificado, IF(a.fecha!=b.fecha,1,0) as diffechas, b.motivo_intento, b.engomado as engomadoventa
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.fecha='".$fecha."' AND a.estatus != 'C' AND a.engomado IN (".$v['cves'].")
			UNION ALL 
			SELECT '' as placa,'' as ticket, 0 as tipo_venta,'' as fechaticket,'' as cve, 0 as tipo_pago, 1 as cancelado, engomado as engomadoentrega,  0 as monto, certificado, 0 as diffechas, 0 as motivo_intento, 0 as engomadoventa FROM certificados_cancelados 
			WHERE plaza='".$_POST['plazausuario']."' AND fecha='".$fecha."' AND engomado IN (".$v['cves'].") AND estatus!='C') as a ORDER BY CAST(a.certificado as UNSIGNED)";
			$select;
			$res = mysql_query($select) or die(mysql_error());
			$fcertificado=-1;
			while($row = mysql_fetch_array($res)){
				if($row['monto']>0){
					$res1 = mysql_query("SELECT fecha,cve,monto FROM cobro_engomado WHERE plaza = '".$_POST['plazausuario']."' AND cve>'".$row['ticket']."' AND placa = '".$row['placa']."' AND estatus!='C' ORDER BY cve LIMIT 1");
					$row1 = mysql_fetch_array($res1);
				}
				else{
					$res1 = mysql_query("SELECT fecha,cve,monto FROM cobro_engomado WHERE plaza = '".$_POST['plazausuario']."' AND cve<'".$row['ticket']."' AND placa = '".$row['placa']."' AND estatus!='C' ORDER BY cve DESC LIMIT 1");
					$row1 = mysql_fetch_array($res1);
				}
				
				if($row['tipo_venta']==2 && $v['nombre']!='RECHAZO'){
					$array_engomado['cortesia'][$k]['cant']++;
					$array_engomado['cortesia'][$k]['dia']++;
				}
				elseif($row['tipo_pago']==6 && $v['nombre']!='RECHAZO'){
					$array_engomado['pagos_anteriores'][$k]['cant']++;
					$array_engomado['pagos_anteriores'][$k]['dia']++;
				}
				elseif($row['cancelado']==1){
					$array_engomado['cancelados'][$k]['cant']++;
					$array_engomado['cancelados'][$k]['dia']++;
				}
				elseif($row['monto']==0 && $row['fechaticket']>$row1['fecha'] && $v['nombre']!='RECHAZO'){
					$array_engomado['pagos_anteriores'][$k]['cant']++;
					$array_engomado['pagos_anteriores'][$k]['dia']++;
				}
				elseif($row1['monto']==0 && $row['monto']>0 && $row['fechaticket']==$row1['fecha'] && $v['nombre']!='RECHAZO'){
					$array_engomado['reverificacion'][$k]['cant']++;
					$array_engomado['reverificacion'][$k]['dia']++;
				}
				else{
					$array_engomado['normal'][$k]['cant']++;
					$array_engomado['normal'][$k]['dia']++;
				}
			}
		}
		foreach($array_engomado['normal'] as $k=>$v){
			echo '<td align="center">'.$v['dia'].'</td>';
			$total+=$v['dia'];
		}
		echo '<td>&nbsp;</td>';
		foreach($array_engomado['cortesia'] as $k=>$v){
			echo '<td align="center">'.$v['dia'].'</td>';
			$total+=$v['dia'];
		}
		foreach($array_engomado['cancelados'] as $k=>$v){
			echo '<td align="center">'.$v['dia'].'</td>';
			$total+=$v['dia'];
		}
		foreach($array_engomado['reverificacion'] as $k=>$v){
			echo '<td align="center">'.$v['dia'].'</td>';
			$total+=$v['dia'];
		}
		foreach($array_engomado['pagos_anteriores'] as $k=>$v){
			echo '<td align="center">'.$v['dia'].'</td>';
			$total+=$v['dia'];
		}
		echo '<td align="center">'.$total.'</td>';
		echo '</tr>';
		$fecha=date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fecha) ) );
	}
	$total=0;
	echo '<tr><th align="left" bgcolor="#E9F2F8">Total</th>';
	foreach($array_engomado['normal'] as $k=>$v){
		echo '<th bgcolor="#E9F2F8">'.$v['cant'].'</th>';
		$total+=$v['cant'];
	}
	echo '<td>&nbsp;</td>';
	foreach($array_engomado['cortesia'] as $k=>$v){
		echo '<th bgcolor="#E9F2F8">'.$v['cant'].'</th>';
		$total+=$v['cant'];
	}
	foreach($array_engomado['cancelados'] as $k=>$v){
		echo '<th bgcolor="#E9F2F8">'.$v['cant'].'</th>';
		$total+=$v['cant'];
	}
	foreach($array_engomado['reverificacion'] as $k=>$v){
		echo '<th bgcolor="#E9F2F8">'.$v['cant'].'</th>';
		$total+=$v['cant'];
	}
	foreach($array_engomado['pagos_anteriores'] as $k=>$v){
		echo '<th bgcolor="#E9F2F8">'.$v['cant'].'</th>';
		$total+=$v['cant'];
	}
	echo '<th bgcolor="#E9F2F8">'.$total.'</th>';
	echo '</tr>';
	echo '</table>';
	exit();	
}

if($_POST['ajax']==1){
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr><th bgcolor="#E9F2F8" rowspan="2">Dia</th>';
	foreach($array_engomado['normal'] as $k=>$v) echo '<th rowspan="2" bgcolor="#E9F2F8">'.$v['nombre'].'</th>';
	echo '<th rowspan="2">&nbsp;</th><th bgcolor="#E9F2F8" colspan="'.count($array_engomado['cortesia']).'">Cortesias</th>
	<th bgcolor="#E9F2F8" colspan="'.count($array_engomado['cancelados']).'">Cancelados</th>
	<th bgcolor="#E9F2F8" colspan="'.count($array_engomado['reverificacion']).'">Reverificaciones</th>
	<th bgcolor="#E9F2F8" colspan="'.count($array_engomado['pagos_anteriores']).'">Pagos Anteriores</th>';
	echo '<th bgcolor="#E9F2F8" rowspan="2">Total</th></tr>';
	echo '<tr>';
	foreach($array_engomado['cortesia'] as $k=>$v) echo '<th bgcolor="#E9F2F8">'.$v['nombre'].'</th>';
	foreach($array_engomado['cancelados'] as $k=>$v) echo '<th bgcolor="#E9F2F8">'.$v['nombre'].'</th>';
	foreach($array_engomado['reverificacion'] as $k=>$v) echo '<th bgcolor="#E9F2F8">'.$v['nombre'].'</th>';
	foreach($array_engomado['pagos_anteriores'] as $k=>$v) echo '<th bgcolor="#E9F2F8">'.$v['nombre'].'</th>';
	echo '</tr>';
	
	$fecha = $_POST['fecha_ini'];
	while($fecha<=$_POST['fecha_fin']){
		rowb();
		foreach($array_engomado['normal'] as $k=>$v){
			$array_engomado['normal'][$k]['dia']=0;
			$array_engomado['cancelados'][$k]['dia']=0;
			if($v['nombre']!='RECHAZO'){
				$array_engomado['cortesia'][$k]['dia']=0;
				$array_engomado['reverificacion'][$k]['dia']=0;
				$array_engomado['pagos_anteriores'][$k]['dia']=0;
			}
		}
		$total = 0;
		echo '<td align="center">'.$fecha.'</td>';
		foreach($array_engomado['normal'] as $k=>$v){
			$select = "SELECT a.* FROM (
			SELECT a.placa,a.ticket, b.tipo_venta,b.fecha as fechaticket,a.cve, IF(b.fecha!=a.fecha,6,b.tipo_pago) as tipo_pago, 0 as cancelado, a.engomado as engomadoentrega, 
			if(b.tipo_pago=6 OR b.fecha!=a.fecha,0,b.monto) as monto, a.certificado as certificado, IF(a.fecha!=b.fecha,1,0) as diffechas, b.motivo_intento, b.engomado as engomadoventa
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.fecha='".$fecha."' AND a.estatus != 'C' AND a.engomado IN (".$v['cves'].")
			UNION ALL 
			SELECT '' as placa,'' as ticket, 0 as tipo_venta,'' as fechaticket,'' as cve, 0 as tipo_pago, 1 as cancelado, engomado as engomadoentrega,  0 as monto, certificado, 0 as diffechas, 0 as motivo_intento, 0 as engomadoventa FROM certificados_cancelados 
			WHERE plaza='".$_POST['plazausuario']."' AND fecha='".$fecha."' AND engomado IN (".$v['cves'].") AND estatus!='C') as a ORDER BY CAST(a.certificado as UNSIGNED)";
			$select;
			$res = mysql_query($select) or die(mysql_error());
			$fcertificado=-1;
			while($row = mysql_fetch_array($res)){
				if($row['monto']>0){
					$res1 = mysql_query("SELECT fecha,cve,monto FROM cobro_engomado WHERE plaza = '".$_POST['plazausuario']."' AND cve>'".$row['ticket']."' AND placa = '".$row['placa']."' AND estatus!='C' ORDER BY cve LIMIT 1");
					$row1 = mysql_fetch_array($res1);
				}
				else{
					$res1 = mysql_query("SELECT fecha,cve,monto FROM cobro_engomado WHERE plaza = '".$_POST['plazausuario']."' AND cve<'".$row['ticket']."' AND placa = '".$row['placa']."' AND estatus!='C' ORDER BY cve DESC LIMIT 1");
					$row1 = mysql_fetch_array($res1);
				}
				
				if($row['tipo_venta']==2 && $v['nombre']!='RECHAZO'){
					$array_engomado['cortesia'][$k]['cant']++;
					$array_engomado['cortesia'][$k]['dia']++;
				}
				elseif($row['tipo_pago']==6 && $v['nombre']!='RECHAZO'){
					$array_engomado['pagos_anteriores'][$k]['cant']++;
					$array_engomado['pagos_anteriores'][$k]['dia']++;
				}
				elseif($row['cancelado']==1){
					$array_engomado['cancelados'][$k]['cant']++;
					$array_engomado['cancelados'][$k]['dia']++;
				}
				elseif($row['monto']==0 && $row['fechaticket']>$row1['fecha'] && $v['nombre']!='RECHAZO'){
					$array_engomado['pagos_anteriores'][$k]['cant']++;
					$array_engomado['pagos_anteriores'][$k]['dia']++;
				}
				elseif($row1['monto']==0 && $row['monto']>0 && $row['fechaticket']==$row1['fecha'] && $v['nombre']!='RECHAZO'){
					$array_engomado['reverificacion'][$k]['cant']++;
					$array_engomado['reverificacion'][$k]['dia']++;
				}
				else{
					$array_engomado['normal'][$k]['cant']++;
					$array_engomado['normal'][$k]['dia']++;
				}
			}
		}
		foreach($array_engomado['normal'] as $k=>$v){
			echo '<td align="center">'.$v['dia'].'</td>';
			$total+=$v['dia'];
		}
		echo '<td>&nbsp;</td>';
		foreach($array_engomado['cortesia'] as $k=>$v){
			echo '<td align="center">'.$v['dia'].'</td>';
			$total+=$v['dia'];
		}
		foreach($array_engomado['cancelados'] as $k=>$v){
			echo '<td align="center">'.$v['dia'].'</td>';
			$total+=$v['dia'];
		}
		foreach($array_engomado['reverificacion'] as $k=>$v){
			echo '<td align="center">'.$v['dia'].'</td>';
			$total+=$v['dia'];
		}
		foreach($array_engomado['pagos_anteriores'] as $k=>$v){
			echo '<td align="center">'.$v['dia'].'</td>';
			$total+=$v['dia'];
		}
		echo '<td align="center">'.$total.'</td>';
		echo '</tr>';
		$fecha=date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fecha) ) );
	}
	$total=0;
	echo '<tr><th align="left" bgcolor="#E9F2F8">Total</th>';
	foreach($array_engomado['normal'] as $k=>$v){
		echo '<th bgcolor="#E9F2F8">'.$v['cant'].'</th>';
		$total+=$v['cant'];
	}
	echo '<td>&nbsp;</td>';
	foreach($array_engomado['cortesia'] as $k=>$v){
		echo '<th bgcolor="#E9F2F8">'.$v['cant'].'</th>';
		$total+=$v['cant'];
	}
	foreach($array_engomado['cancelados'] as $k=>$v){
		echo '<th bgcolor="#E9F2F8">'.$v['cant'].'</th>';
		$total+=$v['cant'];
	}
	foreach($array_engomado['reverificacion'] as $k=>$v){
		echo '<th bgcolor="#E9F2F8">'.$v['cant'].'</th>';
		$total+=$v['cant'];
	}
	foreach($array_engomado['pagos_anteriores'] as $k=>$v){
		echo '<th bgcolor="#E9F2F8">'.$v['cant'].'</th>';
		$total+=$v['cant'];
	}
	echo '<th bgcolor="#E9F2F8">'.$total.'</th>';
	echo '</tr>';
	echo '</table>';
	exit();	
}

top($_SESSION);

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onclick="atcr(\'reporte_certificados_utilizados.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Fin</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
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
			objeto.open("POST","reporte_certificados_utilizados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
	
	
	</Script>
	';

	
}
	
bottom();
?>