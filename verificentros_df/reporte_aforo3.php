<?php 
include ("main.php"); 

$res=mysql_query("SELECT local FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$array_engomado = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND plazas like '%|".$_POST['plazausuario']."|%' AND entrega=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio'];
}
$rsUsuario=mysql_query("SELECT * FROM plazas where estatus!='I' ORDER BY numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}
/*** CONSULTA AJAX  **************************************************/
if($_POST['cmd']==101){
	require_once('../dompdf/dompdf_config.inc.php');
	$reporte='<html><head>
      <style type="text/css">
	                    top  lado      ladoiz
		 @page{ margin: 5in 0.5in 1px 0.5in;}
		</style>
		 </head><table width="100%"><tr><th align="center" style="font-size:20px">Plaza: '.$array_plazas[$_POST['plazausuario']].'</th></tr><tr><th align="center" style="font-size:20px">Desglose de Ventas Soporte del: '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</th></tr><tr><th align="right">Fecha: '.fechaLocal().'  '.horaLocal().'</th></tr>
		 <tr><td>&nbsp;</td></tr></table><body>';
	$reporte.='<table width="100%" border="0" style="font-size:13px">
			   <tr>
			   <td valing="top">';
///////////// //certificados
//			$reporte.= '<h2>Desglose de Verificaciones del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';	

		$res = mysql_query("SELECT a.*
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 AND a.fecha<='".$_POST['fecha_fin']."' AND b.tipo_venta IN (0,2) AND a.engomado != 19 GROUP BY b.cve");
		$array_verificaciones = array();
		$array_tickets = array();
		while($row = mysql_fetch_array($res)){
			$arry_tickets[$row['ticket']] = 1;
			$array_verificaciones[$row['engomado']]++;
		}
		$res = mysql_query("SELECT a.*,b.ticketpago, IFNULL(d.agencia, -1) as tipo_depositante
				FROM certificados a 
				INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN cobro_engomado c ON c.plaza = b.plaza AND c.cve = b.ticketpago
				LEFT JOIN depositantes d ON d.plaza = c.plaza AND d.cve = c.depositante
				WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 	AND a.fecha<='".$_POST['fecha_fin']."' AND a.engomado!=19 AND b.tipo_venta = 1");
		$array_reverificaciones=array();
		$array_tiposdepositantes=array();
		while($row = mysql_fetch_array($res)){
			if($arry_tickets[$row['ticketpago']]!=1){
				$array_verificaciones[$row['engomado']]++;
			}
			else{
				$array_reverificaciones[$row['engomado']]++;
				$array_tiposdepositantes[$row['tipo_depositante']]++;
			}
			$arry_tickets[$row['ticketpago']] = 1;
		}
		$reporte.= '<table width="100%"><tr bcolor="#E9F2F8"><th align="left">Desglose de Certificados</th></tr></table>
		<table width="100%" border="1"><tr bcolor="#E9F2F8"><th>Tipo</th><th>Cantidad</th></tr>';
		$array_renglon=array();
			$array_renglon = $array_verificaciones;
		$t=0;
		foreach($array_renglon as $k=>$v){
//			rowb();
			$reporte.= '<tr><td>'.$array_engomado[$k].'</td>';
			$reporte.= '<td align="right">'.number_format($v,0).'</td>';
			$reporte.= '</tr>';
			$t+=$v;
		}
		$reporte.= '<tr bcolor="#E9F2F8" colspan="2"><th colspan="2">&nbsp;</th></tr>';
		$reporte.= '<tr bcolor="#E9F2F8"><th>Total</th><th align="right">'.number_format($t,0).'</th></tr>';
		$reporte.= '</table>';

			   
	$reporte.='</td>
				<td width="20%">&nbsp;</td>
			   <td>';
///////////////////////////////Reverificaciones
		$_POST['reg']=2;
		

		$res = mysql_query("SELECT a.*
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 AND a.fecha<='".$_POST['fecha_fin']."' AND b.tipo_venta IN (0,2) AND a.engomado != 19 GROUP BY b.cve");
		$array_verificaciones = array();
		$array_tickets = array();
		while($row = mysql_fetch_array($res)){
			$array_tickets[$row['ticket']] = 1;
			$array_verificaciones[$row['engomado']]++;
		}
		$res = mysql_query("SELECT a.*,b.ticketpago, IFNULL(d.agencia, -1) as tipo_depositante
				FROM certificados a 
				INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN cobro_engomado c ON c.plaza = b.plaza AND c.cve = b.ticketpago
				LEFT JOIN depositantes d ON d.plaza = c.plaza AND d.cve = c.depositante
				WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 	AND a.fecha<='".$_POST['fecha_fin']."' AND a.engomado!=19 AND b.tipo_venta = 1");
		$array_reverificaciones=array();
		$array_tiposdepositantes=array();
		while($row = mysql_fetch_array($res)){
			if($array_tickets[$row['ticketpago']]!=1){
				$array_verificaciones[$row['engomado']]++;
			}
			else{
				$array_reverificaciones[$row['engomado']]++;
				$array_tiposdepositantes[$row['tipo_depositante']]++;
			}
			$array_tickets[$row['ticketpago']] = 1;
		}
		$reporte.= '<table width="100%"><tr bcolor="#E9F2F8"><th align="left">Desglose de Reverificaciones</th></tr></table>
		<table width="100%" border="1"><tr bgolor="#E9F2F8"><th>Tipo</th><th>Cantidad</th></tr>';
		$array_renglon=array();
		if($_POST['reg']==1){
			$array_renglon = $array_verificaciones;
		}
		elseif($_POST['reg']==2){
			$array_renglon = $array_reverificaciones;
		}
		$t=0;
		foreach($array_renglon as $k=>$v){
//			rowb();
			$reporte.= '<tr><td>'.$array_engomado[$k].'</td>';
			$reporte.= '<td align="right">'.number_format($v,0).'</td>';
			$reporte.= '</tr>';
			$t+=$v;
		}
		$reporte.= '<tr bcolor="#E9F2F8" colspan="2"><th colspan="2">&nbsp;</th></tr>';
		$reporte.= '<tr bgolor="#E9F2F8"><th>Total</th><th align="right">'.number_format($t,0).'</th></tr>';
		$reporte.= '<tr bcolor="#E9F2F8" colspan="2"><th colspan="2">&nbsp;</th></tr>';
		$reporte.= '</table>';
		if($_POST['reg']==2){
			$tipo_cliente=array(-1=>'Particular', 0=>'Taller', 1=>'Agencia');
			$reporte.= '<table width="100%" border="1"><tr bgclor="#E9F2F8"><th>Tipo Cliente</th><th>Cantidad</th></tr>';
			$t=0;
			foreach($array_tiposdepositantes as $k=>$v){
//				rowb();
				$reporte.= '<tr><td>'.$tipo_cliente[$k].'</td>';
				$reporte.= '<td align="right">'.number_format($v,0).'</td>';
				$reporte.= '</tr>';
				$t+=$v;
			}
			$reporte.= '<tr bcolor="#E9F2F8" colspan="2"><th colspan="2">&nbsp;</th></tr>';
			$reporte.= '<tr bcolor="#E9F2F8"><th>Total</th><th align="right">'.number_format($t,0).'</th></tr>';
			$reporte.= '</table>';
		}

		
	$reporte.='</td>
			   </tr>
			   <tr><td>&nbsp;</td></tr>

			   <tr>
			   <td valing="top">';
//////////////////////////Rechazos
//		echo '<h2>Desglose de Rechazos del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';	
		$res = mysql_query("SELECT a.*, b.tipo_venta, b.ticketpago, b.num_intento, b.tipo_cortesia
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 AND a.fecha<='".$_POST['fecha_fin']."' AND a.engomado = 19 GROUP BY b.cve");
		$array_verificaciones = array();
		$array_tickets = array();
		$total_rechazos=0;
		while($row = mysql_fetch_array($res)){
			$total_rechazos++;
			if($row['tipo_venta']==0){
				$oportunidad = 0;
			}
			elseif($row['tipo_venta']==2){
				$oportunidad = $row['tipo_cortesia'];
			}
			else{
				if($row['num_intento']==0) $row['num_intento']=1;
				elseif($row['num_intento']>4) $row['num_intento']=5;
				$oportunidad = $row['num_intento'];
			}
			$arry_tickets[$row['tipo_venta']][$oportunidad]++;
		}
		
//		$reporte.= '<h3>Aforo: '.$total_rechazos.'</h3>';
		$reporte.= '<table width="100%"><tr bcolor="#E9F2F8"><th align="left">Desglose de Rechazos</th></tr>
					<tr bcolor="#E9F2F8"><th align="left">Aforo: '.$total_rechazos.'</th></tr></table>
		<table width="100%" border="1"><tr bcolor="#E9F2F8"><th>&nbsp;</th><th>Cantidad</th></tr>';
		
//		rowb();
		$reporte.= '<tr><td>Con Pago</td>';
		$reporte.= '<td align="right">'.number_format($arry_tickets[0][0],0).'</td>';
		$reporte.= '</tr>';
//		rowb();
		$reporte.= '<tr><td>Cortesia Autorizada</td>';
		$reporte.= '<td align="right">'.number_format($arry_tickets[2][1],0).'</td>';
		$reporte.= '</tr>';
//		rowb();
		$reporte.= '<tr><td>Cortesia 10 x 1</td>';
		$reporte.= '<td align="right">'.number_format($arry_tickets[2][2],0).'</td>';
		$reporte.= '</tr>';
		foreach($arry_tickets[1] as $k=>$v){
//			rowb();
			$reporte.= '<tr><td>Sin pago '.$k.' oportunidad</td>';
			$reporte.= '<td align="right">'.number_format($v,0).'</td>';
			$reporte.= '</tr>';
		}
		$reporte.= '<tr bcolor="#E9F2F8" colspan="2"><th colspan="2">&nbsp;</th></tr>';
		$reporte.= '<tr bcolor="#E9F2F8"><th>Total</th><th align="right">'.number_format($total_rechazos,0).'</th></tr>';
		$reporte.= '</table>';
	$reporte.='</td>
				<td width="20%">&nbsp;</td>
			   <td>';
	//////////////////////Cancelados
	$t=0;
//				echo '<h2>Desglose de Cancelados del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';	
		$res = mysql_query("SELECT a.nombre, COUNT(b.cve) as cantidad
			FROM engomados a 
			LEFT JOIN certificados_cancelados b ON a.cve = b.engomado AND b.estatus!='C' AND b.plaza = '".$_POST['plazausuario']."' AND b.fecha>='".$_POST['fecha_ini']."' AND b.fecha<='".$_POST['fecha_fin']."'
			WHERE entrega=1 GROUP BY a.cve ORDER BY a.nombre");
		$reporte.= '<table width="100%"><tr bcolor="#E9F2F8"><th align="left">Desglose de Cancelados</th></tr></table>
		<table width="100%" border="1"><tr bgolor="#E9F2F8"><th>Tipo</th><th>Cantidad</th></tr>';
		while($row = mysql_fetch_array($res)){
//			rowb();
			$reporte.= '<tr><td>'.$row['nombre'].'</td>';
			$reporte.= '<td align="right">'.number_format($row['cantidad'],0).'</td>';
			$reporte.= '</tr>';
			$t+=$row['cantidad'];
		}
		$reporte.= '<tr bcolor="#E9F2F8" colspan="2"><th colspan="2">&nbsp;</th></tr>';
		$reporte.= '<tr  bgolor="#E9F2F8"><th>Total</th><th align="right">'.number_format($t,0).'</th></tr></table>';
	$reporte.='</td>
			   </tr>

			   <tr><td>&nbsp;</td></tr>
			   <tr>
			   <td>';
	//////////////Ventascon pagos
	$_POST['reg']=1;
				$array_tipo_cortesia = array(1=>'Autorizada', 2=>'10x1');
		$res = mysql_query("SELECT * FROM tipos_pago WHERE 1 ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_tipo_pago[$row['cve']]=$row['nombre'];
		}
		$res = mysql_query("SELECT * FROM tipo_venta ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_tipo_venta[$row['cve']] = $row['nombre'];
		}


		$select = "SELECT 
		b.tipo_pago, b.tipo_venta, b.tipo_cortesia, COUNT(b.cve) as cantidad FROM cobro_engomado b 
		LEFT JOIN certificados a ON b.plaza = a.plaza AND b.cve = a.ticket AND a.estatus!='C'  AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'
		WHERE b.plaza = '".$_POST['plazausuario']."' AND b.estatus!='C' AND b.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND ISNULL(a.cve)";
		if($_POST['reg']==1) $select .= " AND b.tipo_venta = 0 GROUP BY b.tipo_pago";
		elseif($_POST['reg']==2) $select .= " AND b.tipo_venta IN (1,2) GROUP BY b.tipo_venta, b.tipo_cortesia";
		$select.=" ORDER BY b.cve";
		
		$reporte.= '<table width="100%"><tr bcolor="#E9F2F8"><th align="left">Desglose de ventas con pago sin Certificados</th></tr></table>
		<table width="100%" border="1"><tr bgolor="#E9F2F8">';
		if($_POST['reg']==1) $reporte.= '<th>Tipo de Pago</th>';
		elseif($_POST['reg']==2) $reporte.= '<th>Tipo de Venta</th>';
		$reporte.= '<th>Cantidad</th>';
		$reporte.= '</tr>';
		$res = mysql_query($select) or die(mysql_error());
		$t=0;
		while($row = mysql_fetch_array($res)){
//			rowb();
			if($_POST['reg']==1)$reporte.= '<tr><td align="left">'.$array_tipo_pago[$row['tipo_pago']].'</td>';
			elseif($_POST['reg']==2) $reporte.= '<tr><td align="left">'.$array_tipo_venta[$row['tipo_venta']].' '.$array_tipo_cortesia[$row['tipo_cortesia']].'</td>';
			$reporte.= '<td align="right">'.number_format($row['cantidad'],0).'</td>';
			$reporte.= '</tr>';
			$t+=$row['cantidad'];
		}
		$reporte.= '<tr bcolor="#E9F2F8" colspan="2"><th colspan="2">&nbsp;</th></tr>';
		$reporte.= '<tr bcolor="#E9F2F8"><th>Total</th><th align="right">'.number_format($t,0).'</th></tr>';
		$reporte.= '</table>';
			  
	$reporte.='</td>
				<td width="20%">&nbsp;</td>
			   <td valing="top">';
	//////////////Ventas sin pagos
	$_POST['reg']=2;
				$array_tipo_cortesia = array(1=>'Autorizada', 2=>'10x1');
		$res = mysql_query("SELECT * FROM tipos_pago WHERE 1 ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_tipo_pago[$row['cve']]=$row['nombre'];
		}
		$res = mysql_query("SELECT * FROM tipo_venta ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_tipo_venta[$row['cve']] = $row['nombre'];
		}


		$select = "SELECT 
		b.tipo_pago, b.tipo_venta, b.tipo_cortesia, COUNT(b.cve) as cantidad FROM cobro_engomado b 
		LEFT JOIN certificados a ON b.plaza = a.plaza AND b.cve = a.ticket AND a.estatus!='C'  AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'
		WHERE b.plaza = '".$_POST['plazausuario']."' AND b.estatus!='C' AND b.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND ISNULL(a.cve)";
		if($_POST['reg']==1) $select .= " AND b.tipo_venta = 0 GROUP BY b.tipo_pago";
		elseif($_POST['reg']==2) $select .= " AND b.tipo_venta IN (1,2) GROUP BY b.tipo_venta, b.tipo_cortesia";
		$select.=" ORDER BY b.cve";
		
		$reporte.= '<table width="100%"><tr bcolor="#E9F2F8"><th align="left">Desglose de ventas con pago sin Certificados</th></tr></table>
		<table width="100%" border="1"><tr bgolor="#E9F2F8">';
		if($_POST['reg']==1) $reporte.= '<th>Tipo de Pago</th>';
		elseif($_POST['reg']==2) $reporte.= '<th>Tipo de Venta</th>';
		$reporte.= '<th>Cantidad</th>';
		$reporte.= '</tr>';
		$res = mysql_query($select) or die(mysql_error());
		$t=0;
		while($row = mysql_fetch_array($res)){
//			rowb();
			if($_POST['reg']==1)$reporte.= '<tr><td align="left">'.$array_tipo_pago[$row['tipo_pago']].'</td>';
			elseif($_POST['reg']==2) $reporte.= '<tr><td align="left">'.$array_tipo_venta[$row['tipo_venta']].' '.$array_tipo_cortesia[$row['tipo_cortesia']].'</td>';
			$reporte.= '<td align="right">'.number_format($row['cantidad'],0).'</td>';
			$reporte.= '</tr>';
			$t+=$row['cantidad'];
		}
		$reporte.= '<tr bcolor="#E9F2F8" colspan="2"><th colspan="2">&nbsp;</th></tr>';
		$reporte.= '<tr bcolor="#E9F2F8"><th>Total</th><th align="right">'.number_format($t,0).'</th></tr>';
		$reporte.= '</table>';
	$reporte.='</td>
			   </tr>';
	
	
	
	$reporte.= '</table>';
	
$reporte.='</body></html>';
	$mipdf = new DOMPDF();
//	$mipdf->margin: "0";
	//$mipdf->set_paper("A4", "portrait");
	$mipdf->set_paper("A4", "portrait");
//	$mipdf->set_paper("Legal", "landscape");
	$mipdf->load_html($reporte);
	$mipdf->render();
	$mipdf ->stream();
	exit();
}

if($_POST['cmd']==100){
	require_once('../dompdf/dompdf_config.inc.php');
	$reporte='<html><head>
      <style type="text/css">
	                    top  lado      ladoiz
		 @page{ margin: 5in 0.5in 1px 0.5in;}
		</style>
		 </head><table width="100%"><tr><th align="center" style="font-size:26px">Plaza: '.$array_plazas[$_POST['plazausuario']].'</th></tr><tr><th align="right">Fecha: '.fechaLocal().'  '.horaLocal().'</th></tr><tr><th align="left" style="font-size:19px">Vetas del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</th></tr>
		 <tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr></table><body>';
	$res = mysql_query("SELECT COUNT(a.cve),SUM(b.monto_verificacion),SUM(b.monto),b.tipo_venta,
		SUM(IF(b.tipo_venta=2 AND b.tipo_cortesia=1, 1, 0)),
		SUM(IF(b.tipo_venta=2 AND b.tipo_cortesia=1, b.monto_verificacion, 0)),
		SUM(IF(b.tipo_venta=2 AND b.tipo_cortesia!=1, 1, 0)),
		SUM(IF(b.tipo_venta=2 AND b.tipo_cortesia!=1, b.monto_verificacion, 0))
		FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
		 AND a.fecha<='".$_POST['fecha_fin']."' GROUP BY b.tipo_venta") or die(mysql_error());
	$array_tipo_venta = array();
	$total1 = 0;
	$total2 = 0;
	while($row = mysql_fetch_array($res)){
		$array_tipo_venta[$row[3]][0]=$row[0];
		$array_tipo_venta[$row[3]][1]=$row[1];
		$array_tipo_venta[$row[3]][2]=$row[2];
		$array_tipo_venta[$row[3]][3]=$row[4];
		$array_tipo_venta[$row[3]][4]=$row[5];
		$array_tipo_venta[$row[3]][5]=$row[6];
		$array_tipo_venta[$row[3]][6]=$row[7];
		$total1+=$row[0];
		$total2+=$row[2];
	}
		$reporte.= '<table width="100%"><tr><td><table border="" width=""><tr><td>&nbsp;</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td><td><table width="70%" border="1" style="font-size:15px"><tr color="#E9F2F8"><th>&nbsp;</th><th>Cantidad de Certificados</th><th>Importe</th></tr>';
//	rowb();
	$reporte.= '<tr><td>AFORO</td><td align="right">'.number_format($total1,0).'</td><td align="right">'.number_format($total2,2).'</td></tr>';
	$reporte.= '<tr><td colspan="3">&nbsp;</td></tr>';
	$reporte.= '<tr><td colspan="3">&nbsp;</td></tr>';
//	rowb();
	$reporte.= '<tr><td>Con Pago</td><td align="right">'.number_format($array_tipo_venta[0][0],0).'</td><td align="right">'.number_format($array_tipo_venta[0][1],2).'</td></tr>';
//	rowb();
	$reporte.= '<tr><td>Cortesia Autorizada</td><td align="right">'.number_format($array_tipo_venta[2][3],0).'</td><td align="right">'.number_format($array_tipo_venta[2][4],2).'</td></tr>';
///	rowb();
	$reporte.= '<tr><td>Cortesia 10 x 1</td><td align="right">'.number_format($array_tipo_venta[2][5],0).'</td><td align="right">'.number_format($array_tipo_venta[5][1],2).'</td></tr>';
//	rowb();
	$reporte.= '<tr><td>Intentos</td><td align="right">'.number_format($array_tipo_venta[1][0],0).'</td><td align="right">'.number_format($array_tipo_venta[1][1],2).'</td></tr>';

	$res = mysql_query("SELECT b.cve, b.monto, b.monto_verificacion, a.engomado, 
		IF(a.engomado != 19, 1, 0) as certificado, IF(a.engomado = 19, 1, 0) as rechazo, 
		IF(a.engomado = 19, b.monto, 0) as rechazo_importe
		FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
		 AND a.fecha<='".$_POST['fecha_fin']."' AND b.tipo_venta IN (0,2) GROUP BY b.cve");
	$array_renglon = array();
	$array_tickets = array();
	while($row = mysql_fetch_array($res)){
		$array_tickets[$row['cve']] = $row;
		$array_renglon['rechazos']['cantidad']+=$row['rechazo']+$row['rechazo_intento'];
		$array_renglon['rechazos']['importe']+=$row['rechazo_importe']+$row['rechazo_intento_importe'];
		$reverificacion = 0;
		$reverificacion_importe = 0;
		$reverificacion_importe_supuesto = 0;
		if($row['certificado']==1 && $row['certificado_intento'] > 0){
			$reverificacion = $row['certificado_intento'];
			$reverificacion_importe = $row['certificado_intento'] * $row['monto'];
			$reverificacion_importe_supuesto = $row['certificado_intento'] * $row['monto_verificacion'];
		}
		elseif($row['certificado']==0 && $row['certificado_intento'] > 1){
			$reverificacion = $row['certificado_intento']-1;
			$reverificacion_importe_supuesto = ($row['certificado_intento']-1) * $row['monto_verificacion'];
		}
		$array_renglon['reverificacion']['cantidad']+=$reverificacion;
		$array_renglon['reverificacion']['importe']+=$reverificacion_importe;
		$array_renglon['reverificacion']['importe_supuesto']+=$reverificacion_importe_supuesto;
		$certificados = 0;
		$certificados_importe = 0;
		$certificados_importe_supuesto = 0;
		if($row['certificado']==1){
			$certificados = 1;
			$certificados_importe = $row['monto'];
			$certificados_importe_supuesto = $row['monto_verificacion'];
		}
		elseif($row['certificado']==0 && $row['certificado_intento'] >= 1){
			$certificados = 1;
			$certificados_importe = $row['monto'];
			$certificados_importe_supuesto = $row['monto_verificacion'];
		}
		$array_renglon['certificado']['cantidad']+=$certificados;
		$array_renglon['certificado']['importe']+=$certificados_importe;
		$array_renglon['certificado']['importe_supuesto']+=$certificados_importe_supuesto;
	}
	$res = mysql_query("SELECT b.ticketpago, COUNT(a.cve) as intentos, b.monto, b.monto_verificacion, SUM(IF(a.engomado != 19, 1, 0)) as certificado, 
			SUM(IF(a.engomado = 19, 1, 0)) as rechazo, SUM(IF(a.engomado = 19, b.monto, 0)) as rechazo_importe  
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
		 	AND a.fecha<='".$_POST['fecha_fin']."' AND b.tipo_venta = 1 GROUP BY b.ticketpago");
	while($row = mysql_fetch_array($res)){
		$array_renglon['rechazos']['cantidad']+=$row['rechazo'];
		$array_renglon['rechazos']['importe']+=$row['rechazo_importe'];
		$reverificacion = 0;
		$reverificacion_importe = 0;
		$reverificacion_importe_supuesto = 0;
		if($array_tickets[$row['ticketpago']]['certificado']==1 && $row['certificado'] > 0){
			$reverificacion = $row['certificado'];
			$reverificacion_importe = $row['certificado'] * $row['monto'];
			$reverificacion_importe_supuesto = $row['certificado'] * $row['monto_verificacion'];
		}
		elseif($array_tickets[$row['ticketpago']]['certificado']==0 && $row['certificado'] > 1){
			$reverificacion = $row['certificado']-1;
			$reverificacion_importe = ($row['certificado']-1) * $row['monto'];
			$reverificacion_importe_supuesto = ($row['certificado']-1) * $row['monto_verificacion'];
		}
		$array_renglon['reverificacion']['cantidad']+=$reverificacion;
		$array_renglon['reverificacion']['importe']+=$reverificacion_importe;
		$array_renglon['reverificacion']['importe_supuesto']+=$reverificacion_importe_supuesto;
		$certificados = 0;
		$certificados_importe = 0;
		$certificados_importe_supuesto = 0;
		if($array_tickets[$row['ticketpago']]['certificado']==0 && $row['certificado'] >= 1){
			$certificados = 1;
			$certificados_importe = $row['monto'];
			$certificados_importe_supuesto = $row['monto_verificacion'];
		}
		$array_renglon['certificado']['cantidad']+=$certificados;
		$array_renglon['certificado']['importe']+=$certificados_importe;
		$array_renglon['certificado']['importe_supuesto']+=$certificados_importe_supuesto;
	}
	$reporte.= '<tr><td colspan="3">&nbsp;</td></tr>';
	$reporte.= '<tr><td colspan="3">&nbsp;</td></tr>';
	$t1=0;
	$t2=0;
//	rowb();
	$reporte.= '<tr><td>Certificados</td><td align="right">'.number_format($array_renglon['certificado']['cantidad'],0).'</td><td align="right">'.number_format($array_renglon['certificado']['importe'],2).'</td></tr>';
	$t1+=$array_renglon['certificado']['cantidad'];
	$t2+=$array_renglon['certificado']['importe'];
	
//	rowb();
	$reporte.= '<tr><td>Reverificaciones</td><td align="right">'.number_format($array_renglon['reverificacion']['cantidad'],0).'</td><td align="right">'.number_format($array_renglon['reverificacion']['importe'],2).'</td></tr>';
	$t1+=$array_renglon['reverificacion']['cantidad'];
	$t2+=$array_renglon['reverificacion']['importe'];
//	rowb();
	$reporte.= '<tr><td>Rechazos</td><td align="right">'.number_format($array_renglon['rechazos']['cantidad'],0).'</td><td align="right">'.number_format($array_renglon['rechazos']['importe'],2).'</td></tr>';
	$t1+=$array_renglon['rechazos']['cantidad'];
	$t2+=$array_renglon['rechazos']['importe'];
//	rowb();
	$reporte.= '<tr gcolor="#E9F2F8"><th>Totales</th><td align="right">'.number_format($t1,0).'</td><td align="right">'.number_format($t2,2).'</td></tr>';
	$res = mysql_query("SELECT COUNT(a.cve),SUM(b.precio) FROM certificados_cancelados a INNER JOIN engomados b ON b.cve = a.engomado 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
		 AND a.fecha<='".$_POST['fecha_fin']."'");
	$row = mysql_fetch_array($res);
//	rowb();
	$reporte.= '<tr><td>Cancelados</td><td align="right">'.number_format($row[0],0).'</td><td align="right">'.number_format(0,2).'</td></tr>';
	
	
	$res = mysql_query("SELECT 
		SUM(IF(b.tipo_venta = 0, 1, 0)) as contado, SUM(IF(b.tipo_venta = 0, b.monto, 0)) as importe_contado, 
		SUM(IF(b.tipo_venta = 1, 1, 0)) as intento,
		SUM(IF(b.tipo_venta = 2, 1, 0)) as cortesia,
		COUNT(a.cve) as aforo FROM cobro_engomado b 
		LEFT JOIN certificados a ON b.plaza = a.plaza AND b.cve = a.ticket AND a.estatus!='C'  AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'
		WHERE b.plaza = '".$_POST['plazausuario']."' AND b.estatus!='C' AND b.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND ISNULL(a.cve)") or die(mysql_error());
	$row = mysql_fetch_assoc($res);
	$reporte.= '<tr><td colspan="3">&nbsp;</td></tr>';
	$reporte.= '<tr><td colspan="3">&nbsp;</td></tr>';
//	rowb();
	$reporte.= '<tr><td>Ventas con pago sin Certificado</td><td align="right">'.number_format($row['contado'],0).'</td><td align="right">'.number_format($row['importe_contado'],2).'</td></tr>';
//	rowb();
	$reporte.= '<tr><td>Ventas del Periodo Anterior</td><td align="right">'.number_format($row['intento']+$row['cortesia'],0).'</td><td align="right">'.number_format(0,2).'</td></tr>';
	
	$reporte.= '</table></td></tr></table>';
	
$reporte.='</body></html>';
	$mipdf = new DOMPDF();
//	$mipdf->margin: "0";
	//$mipdf->set_paper("A4", "portrait");
	$mipdf->set_paper("A4", "portrait");
//	$mipdf->set_paper("Legal", "landscape");
	$mipdf->load_html($reporte);
	$mipdf->render();
	$mipdf ->stream();
	exit();
}

if($_POST['ajax']==1){
	$res = mysql_query("SELECT COUNT(a.cve),SUM(b.monto_verificacion),SUM(b.monto),b.tipo_venta,
		SUM(IF(b.tipo_venta=2 AND b.tipo_cortesia=1, 1, 0)),
		SUM(IF(b.tipo_venta=2 AND b.tipo_cortesia=1, b.monto_verificacion, 0)),
		SUM(IF(b.tipo_venta=2 AND b.tipo_cortesia!=1, 1, 0)),
		SUM(IF(b.tipo_venta=2 AND b.tipo_cortesia!=1, b.monto_verificacion, 0))
		FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
		 AND a.fecha<='".$_POST['fecha_fin']."' GROUP BY b.tipo_venta") or die(mysql_error());
	$array_tipo_venta = array();
	$total1 = 0;
	$total2 = 0;
	while($row = mysql_fetch_array($res)){
		$array_tipo_venta[$row[3]][0]=$row[0];
		$array_tipo_venta[$row[3]][1]=$row[1];
		$array_tipo_venta[$row[3]][2]=$row[2];
		$array_tipo_venta[$row[3]][3]=$row[4];
		$array_tipo_venta[$row[3]][4]=$row[5];
		$array_tipo_venta[$row[3]][5]=$row[6];
		$array_tipo_venta[$row[3]][6]=$row[7];
		$total1+=$row[0];
		$total2+=$row[2];
	}
	echo '<table width="100%"><tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Cantidad de Certificados</th><th>Importe</th></tr>';
	rowb();
	echo '<td>AFORO</td><td align="right">'.number_format($total1,0).'</td><td align="right">'.number_format($total2,2).'</td></tr>';
	echo '<tr><td colspan="3">&nbsp;</td></tr>';
	echo '<tr><td colspan="3">&nbsp;</td></tr>';
	rowb();
	echo '<td>Con Pago</td><td align="right">'.number_format($array_tipo_venta[0][0],0).'</td><td align="right">'.number_format($array_tipo_venta[0][1],2).'</td></tr>';
	rowb();
	echo '<td>Cortesia Autorizada</td><td align="right">'.number_format($array_tipo_venta[2][3],0).'</td><td align="right">'.number_format($array_tipo_venta[2][4],2).'</td></tr>';
	rowb();
	echo '<td>Cortesia 10 x 1</td><td align="right">'.number_format($array_tipo_venta[2][5],0).'</td><td align="right">'.number_format($array_tipo_venta[5][1],2).'</td></tr>';
	rowb();
	echo '<td>Intentos</td><td align="right">'.number_format($array_tipo_venta[1][0],0).'</td><td align="right">'.number_format($array_tipo_venta[1][1],2).'</td></tr>';

	/*$res = mysql_query("SELECT a.cve, b.monto, b.monto_verificacion, a.engomado, 
		IF(a.engomado != 19, 1, 0) as certificado, IF(a.engomado = 19, 1, 0) as rechazo, 
		IF(a.engomado = 19, b.monto, 0) as rechazo_importe, c.intentos, 
		c.certificado as certificado_intento, 
		c.monto as monto_intento, c.monto_verificacion as monto_verificacion_intento, 
		c.rechazo as rechazo_intento, c.rechazo_importe as rechazo_intento_importe
		FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		LEFT JOIN (
			SELECT b.ticketpago, COUNT(a.cve) as intentos, b.monto, b.monto_verificacion, SUM(IF(a.engomado != 19, 1, 0)) as certificado, 
			SUM(IF(a.engomado = 19, 1, 0)) as rechazo, SUM(IF(a.engomado = 19, b.monto, 0)) as rechazo_importe  
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
		 	AND a.fecha<='".$_POST['fecha_fin']."' AND b.tipo_venta = 1 GROUP BY b.ticketpago
		) c ON b.cve = c.ticketpago 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
		 AND a.fecha<='".$_POST['fecha_fin']."' AND b.tipo_venta IN (0,2) GROUP BY b.cve");*/
	$res = mysql_query("SELECT b.cve, b.monto, b.monto_verificacion, a.engomado, 
		IF(a.engomado != 19, 1, 0) as certificado, IF(a.engomado = 19, 1, 0) as rechazo, 
		IF(a.engomado = 19, b.monto, 0) as rechazo_importe
		FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
		 AND a.fecha<='".$_POST['fecha_fin']."' AND b.tipo_venta IN (0,2) GROUP BY b.cve");
	$array_renglon = array();
	$array_tickets = array();
	while($row = mysql_fetch_array($res)){
		$array_tickets[$row['cve']] = $row;
		$array_renglon['rechazos']['cantidad']+=$row['rechazo']+$row['rechazo_intento'];
		$array_renglon['rechazos']['importe']+=$row['rechazo_importe']+$row['rechazo_intento_importe'];
		$reverificacion = 0;
		$reverificacion_importe = 0;
		$reverificacion_importe_supuesto = 0;
		if($row['certificado']==1 && $row['certificado_intento'] > 0){
			$reverificacion = $row['certificado_intento'];
			$reverificacion_importe = $row['certificado_intento'] * $row['monto'];
			$reverificacion_importe_supuesto = $row['certificado_intento'] * $row['monto_verificacion'];
		}
		elseif($row['certificado']==0 && $row['certificado_intento'] > 1){
			$reverificacion = $row['certificado_intento']-1;
			$reverificacion_importe_supuesto = ($row['certificado_intento']-1) * $row['monto_verificacion'];
		}
		$array_renglon['reverificacion']['cantidad']+=$reverificacion;
		$array_renglon['reverificacion']['importe']+=$reverificacion_importe;
		$array_renglon['reverificacion']['importe_supuesto']+=$reverificacion_importe_supuesto;
		$certificados = 0;
		$certificados_importe = 0;
		$certificados_importe_supuesto = 0;
		if($row['certificado']==1){
			$certificados = 1;
			$certificados_importe = $row['monto'];
			$certificados_importe_supuesto = $row['monto_verificacion'];
		}
		elseif($row['certificado']==0 && $row['certificado_intento'] >= 1){
			$certificados = 1;
			$certificados_importe = $row['monto'];
			$certificados_importe_supuesto = $row['monto_verificacion'];
		}
		$array_renglon['certificado']['cantidad']+=$certificados;
		$array_renglon['certificado']['importe']+=$certificados_importe;
		$array_renglon['certificado']['importe_supuesto']+=$certificados_importe_supuesto;
	}
	$res = mysql_query("SELECT b.ticketpago, COUNT(a.cve) as intentos, b.monto, b.monto_verificacion, SUM(IF(a.engomado != 19, 1, 0)) as certificado, 
			SUM(IF(a.engomado = 19, 1, 0)) as rechazo, SUM(IF(a.engomado = 19, b.monto, 0)) as rechazo_importe  
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
		 	AND a.fecha<='".$_POST['fecha_fin']."' AND b.tipo_venta = 1 GROUP BY b.ticketpago");
	while($row = mysql_fetch_array($res)){
		$array_renglon['rechazos']['cantidad']+=$row['rechazo'];
		$array_renglon['rechazos']['importe']+=$row['rechazo_importe'];
		$reverificacion = 0;
		$reverificacion_importe = 0;
		$reverificacion_importe_supuesto = 0;
		if($array_tickets[$row['ticketpago']]['certificado']==1 && $row['certificado'] > 0){
			$reverificacion = $row['certificado'];
			$reverificacion_importe = $row['certificado'] * $row['monto'];
			$reverificacion_importe_supuesto = $row['certificado'] * $row['monto_verificacion'];
		}
		elseif($array_tickets[$row['ticketpago']]['certificado']==0 && $row['certificado'] > 1){
			$reverificacion = $row['certificado']-1;
			$reverificacion_importe = ($row['certificado']-1) * $row['monto'];
			$reverificacion_importe_supuesto = ($row['certificado']-1) * $row['monto_verificacion'];
		}
		$array_renglon['reverificacion']['cantidad']+=$reverificacion;
		$array_renglon['reverificacion']['importe']+=$reverificacion_importe;
		$array_renglon['reverificacion']['importe_supuesto']+=$reverificacion_importe_supuesto;
		$certificados = 0;
		$certificados_importe = 0;
		$certificados_importe_supuesto = 0;
		if($array_tickets[$row['ticketpago']]['certificado']==0 && $row['certificado'] >= 1){
			$certificados = 1;
			$certificados_importe = $row['monto'];
			$certificados_importe_supuesto = $row['monto_verificacion'];
		}
		$array_renglon['certificado']['cantidad']+=$certificados;
		$array_renglon['certificado']['importe']+=$certificados_importe;
		$array_renglon['certificado']['importe_supuesto']+=$certificados_importe_supuesto;
	}
	echo '<tr><td colspan="3">&nbsp;</td></tr>';
	echo '<tr><td colspan="3">&nbsp;</td></tr>';
	$t1=0;
	$t2=0;
	rowb();
	echo '<td>Certificados</td><td align="right"><a href="#" onClick="atcr(\'reporte_aforo3.php\',\'\',1,1)">'.number_format($array_renglon['certificado']['cantidad'],0).'</a></td><td align="right">'.number_format($array_renglon['certificado']['importe'],2).'</td></tr>';
	$t1+=$array_renglon['certificado']['cantidad'];
	$t2+=$array_renglon['certificado']['importe'];
	
	rowb();
	echo '<td>Reverificaciones</td><td align="right"><a href="#" onClick="atcr(\'reporte_aforo3.php\',\'\',1,2)">'.number_format($array_renglon['reverificacion']['cantidad'],0).'</a></td><td align="right">'.number_format($array_renglon['reverificacion']['importe'],2).'</td></tr>';
	$t1+=$array_renglon['reverificacion']['cantidad'];
	$t2+=$array_renglon['reverificacion']['importe'];
	rowb();
	echo '<td>Rechazos</td><td align="right"><a href="#" onClick="atcr(\'reporte_aforo3.php\',\'\',2,0)">'.number_format($array_renglon['rechazos']['cantidad'],0).'</a></td><td align="right">'.number_format($array_renglon['rechazos']['importe'],2).'</td></tr>';
	$t1+=$array_renglon['rechazos']['cantidad'];
	$t2+=$array_renglon['rechazos']['importe'];
	rowb();
	echo '<tr bgcolor="#E9F2F8"><th>Totales</th><td align="right">'.number_format($t1,0).'</th><td align="right">'.number_format($t2,2).'</th></tr>';
	$res = mysql_query("SELECT COUNT(a.cve),SUM(b.precio) FROM certificados_cancelados a INNER JOIN engomados b ON b.cve = a.engomado 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
		 AND a.fecha<='".$_POST['fecha_fin']."'");
	$row = mysql_fetch_array($res);
	rowb();
	echo '<td>Cancelados</td><td align="right"><a href="#" onClick="atcr(\'reporte_aforo3.php\',\'\',4,0)">'.number_format($row[0],0).'</a></td><td align="right">'.number_format(0,2).'</td></tr>';
	
	/*echo '<tr><td colspan="3">&nbsp;</td></tr>';
	echo '<tr><td colspan="3">&nbsp;</td></tr>';
	rowb();
	echo '<td>Certificados</td><td align="right">'.number_format($array_renglon['certificado']['cantidad'],0).'</td><td align="right">'.number_format($array_renglon['certificado']['importe_supuesto'],2).'</td></tr>';
	
	rowb();
	echo '<td>Reverificaciones</td><td align="right">'.number_format($array_renglon['reverificacion']['cantidad'],0).'</td><td align="right">'.number_format($array_renglon['reverificacion']['importe_supuesto'],2).'</td></tr>';
	
	rowb();
	echo '<td>Rechazos</td><td align="right">'.number_format($array_renglon['rechazos']['cantidad'],0).'</td><td align="right">'.number_format($array_renglon['rechazos']['importe'],2).'</td></tr>';
	rowb();
	echo '<td>Cancelados</td><td align="right">'.number_format($row[0],0).'</td><td align="right">'.number_format($row[1],2).'</td></tr>';	
*/
	$res = mysql_query("SELECT 
		SUM(IF(b.tipo_venta = 0, 1, 0)) as contado, SUM(IF(b.tipo_venta = 0, b.monto, 0)) as importe_contado, 
		SUM(IF(b.tipo_venta = 1, 1, 0)) as intento,
		SUM(IF(b.tipo_venta = 2, 1, 0)) as cortesia,
		COUNT(a.cve) as aforo FROM cobro_engomado b 
		LEFT JOIN certificados a ON b.plaza = a.plaza AND b.cve = a.ticket AND a.estatus!='C'  AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'
		WHERE b.plaza = '".$_POST['plazausuario']."' AND b.estatus!='C' AND b.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND ISNULL(a.cve)") or die(mysql_error());
	$row = mysql_fetch_assoc($res);
	echo '<tr><td colspan="3">&nbsp;</td></tr>';
	echo '<tr><td colspan="3">&nbsp;</td></tr>';
	rowb();
	echo '<td>Ventas con pago sin Certificado</td><td align="right"><a href="#" onClick="atcr(\'reporte_aforo3.php\',\'\',3,1)">'.number_format($row['contado'],0).'</a></td><td align="right">'.number_format($row['importe_contado'],2).'</td></tr>';
	rowb();
	echo '<td>Ventas del Periodo Anterior</td><td align="right"><a href="#" onClick="atcr(\'reporte_aforo3.php\',\'\',3,2)">'.number_format($row['intento']+$row['cortesia'],0).'</a></td><td align="right">'.number_format(0,2).'</td></tr>';
	
	echo '</table>';
	exit();
}


top($_SESSION);

	if($_POST['cmd']>0){
		echo '<input type="hidden" name="archivoname" value="reporte_aforo3">';
		echo '<input type="hidden" name="fecha_ini" value="'.$_POST['fecha_ini'].'">';
		echo '<input type="hidden" name="fecha_fin" value="'.$_POST['fecha_fin'].'">';
	}

	if($_POST['cmd']==4){
		echo '<table>';
		echo '
			<tr>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'reporte_aforo3.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
			echo'</tr>';
		echo '</table>';
		echo '<h2>Desglose de Cancelados del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';	
		$res = mysql_query("SELECT a.nombre, COUNT(b.cve) as cantidad
			FROM engomados a 
			LEFT JOIN certificados_cancelados b ON a.cve = b.engomado AND b.estatus!='C' AND b.plaza = '".$_POST['plazausuario']."' AND b.fecha>='".$_POST['fecha_ini']."' AND b.fecha<='".$_POST['fecha_fin']."'
			WHERE entrega=1 GROUP BY a.cve ORDER BY a.nombre");
		echo '<table width="100%"><tr bgcolor="#E9F2F8"><th>Tipo</th><th>Cantidad</th></tr>';
		while($row = mysql_fetch_array($res)){
			rowb();
			echo '<td>'.$row['nombre'].'</td>';
			echo '<td align="right">'.number_format($row['cantidad'],0).'</td>';
			echo '</tr>';
			$t+=$row['cantidad'];
		}
		echo '<tr  bgcolor="#E9F2F8"><th>Total</th><th align="right">'.number_format($t,0).'</th></tr></table>';
	}

	if($_POST['cmd']==3){
		$array_tipo_cortesia = array(1=>'Autorizada', 2=>'10x1');
		$res = mysql_query("SELECT * FROM tipos_pago WHERE 1 ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_tipo_pago[$row['cve']]=$row['nombre'];
		}
		$res = mysql_query("SELECT * FROM tipo_venta ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_tipo_venta[$row['cve']] = $row['nombre'];
		}
		echo '<table>';
		echo '
			<tr>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'reporte_aforo3.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
			echo'</tr>';
		echo '</table>';
		if($_POST['reg']==1){
			echo '<h2>Desglose de ventas con pago sin Certificados del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';	
		}
		elseif($_POST['reg']==2){
			echo '<h2>Desglose de ventas sin pago sin Certificados del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';	
		}

		$select = "SELECT 
		b.tipo_pago, b.tipo_venta, b.tipo_cortesia, COUNT(b.cve) as cantidad FROM cobro_engomado b 
		LEFT JOIN certificados a ON b.plaza = a.plaza AND b.cve = a.ticket AND a.estatus!='C'  AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'
		WHERE b.plaza = '".$_POST['plazausuario']."' AND b.estatus!='C' AND b.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND ISNULL(a.cve)";
		if($_POST['reg']==1) $select .= " AND b.tipo_venta = 0 GROUP BY b.tipo_pago";
		elseif($_POST['reg']==2) $select .= " AND b.tipo_venta IN (1,2) GROUP BY b.tipo_venta, b.tipo_cortesia";
		$select.=" ORDER BY b.cve";
		
		echo '<table width="100%"><tr bgcolor="#E9F2F8">';
		if($_POST['reg']==1) echo '<th>Tipo de Pago</th>';
		elseif($_POST['reg']==2) echo '<th>Tipo de Venta</th>';
		echo '<th>Cantidad</th>';
		echo '</tr>';
		$res = mysql_query($select) or die(mysql_error());
		$t=0;
		while($row = mysql_fetch_array($res)){
			rowb();
			if($_POST['reg']==1)echo '<td align="left">'.$array_tipo_pago[$row['tipo_pago']].'</td>';
			elseif($_POST['reg']==2) echo '<td align="left">'.$array_tipo_venta[$row['tipo_venta']].' '.$array_tipo_cortesia[$row['tipo_cortesia']].'</td>';
			echo '<td align="right">'.number_format($row['cantidad'],0).'</td>';
			echo '</tr>';
			$t+=$row['cantidad'];
		}
		echo '<tr bgcolor="#E9F2F8"><th>Total</th><th align="right">'.number_format($t,0).'</th></tr>';
		echo '</table>';
		
	}

	if($_POST['cmd']==2){

		echo '<table>';
		echo '
			<tr>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'reporte_aforo3.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
			echo'</tr>';
		echo '</table>';
		echo '<h2>Desglose de Rechazos del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';	
		$res = mysql_query("SELECT a.*, b.tipo_venta, b.ticketpago, b.num_intento, b.tipo_cortesia
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 AND a.fecha<='".$_POST['fecha_fin']."' AND a.engomado = 19 GROUP BY b.cve");
		$array_verificaciones = array();
		$array_tickets = array();
		$total_rechazos=0;
		while($row = mysql_fetch_array($res)){
			$total_rechazos++;
			if($row['tipo_venta']==0){
				$oportunidad = 0;
			}
			elseif($row['tipo_venta']==2){
				$oportunidad = $row['tipo_cortesia'];
			}
			else{
				if($row['num_intento']==0) $row['num_intento']=1;
				elseif($row['num_intento']>4) $row['num_intento']=5;
				$oportunidad = $row['num_intento'];
			}
			$arry_tickets[$row['tipo_venta']][$oportunidad]++;
		}
		
		echo '<h3>Aforo: '.$total_rechazos.'</h3>';
		echo '<table width="100%"><tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Cantidad</th></tr>';
		
		rowb();
		echo '<td>Con Pago</td>';
		echo '<td align="right">'.number_format($arry_tickets[0][0],0).'</td>';
		echo '</tr>';
		rowb();
		echo '<td>Cortesia Autorizada</td>';
		echo '<td align="right">'.number_format($arry_tickets[2][1],0).'</td>';
		echo '</tr>';
		rowb();
		echo '<td>Cortesia 10 x 1</td>';
		echo '<td align="right">'.number_format($arry_tickets[2][2],0).'</td>';
		echo '</tr>';
		foreach($arry_tickets[1] as $k=>$v){
			rowb();
			echo '<td>Sin pago '.$k.' oportunidad</td>';
			echo '<td align="right">'.number_format($v,0).'</td>';
			echo '</tr>';
		}
		echo '<tr bgcolor="#E9F2F8"><th>Total</th><th align="right">'.number_format($total_rechazos,0).'</th></tr>';
		echo '</table>';
		
	}

	if($_POST['cmd']==1){

		echo '<table>';
		echo '
			<tr>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'reporte_aforo3.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
			echo'</tr>';
		echo '</table>';
		if($_POST['reg']==1){
			echo '<h2>Desglose de Verificaciones del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';	
		}
		elseif($_POST['reg']==2){
			echo '<h2>Desglose de Reverificaciones del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';	
		}

		$res = mysql_query("SELECT a.*
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 AND a.fecha<='".$_POST['fecha_fin']."' AND b.tipo_venta IN (0,2) AND a.engomado != 19 GROUP BY b.cve");
		$array_verificaciones = array();
		$array_tickets = array();
		while($row = mysql_fetch_array($res)){
			$arry_tickets[$row['ticket']] = 1;
			$array_verificaciones[$row['engomado']]++;
		}
		$res = mysql_query("SELECT a.*,b.ticketpago, IFNULL(d.agencia, -1) as tipo_depositante
				FROM certificados a 
				INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN cobro_engomado c ON c.plaza = b.plaza AND c.cve = b.ticketpago
				LEFT JOIN depositantes d ON d.plaza = c.plaza AND d.cve = c.depositante
				WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 	AND a.fecha<='".$_POST['fecha_fin']."' AND a.engomado!=19 AND b.tipo_venta = 1");
		$array_reverificaciones=array();
		$array_tiposdepositantes=array();
		while($row = mysql_fetch_array($res)){
			if($arry_tickets[$row['ticketpago']]!=1){
				$array_verificaciones[$row['engomado']]++;
			}
			else{
				$array_reverificaciones[$row['engomado']]++;
				$array_tiposdepositantes[$row['tipo_depositante']]++;
			}
			$arry_tickets[$row['ticketpago']] = 1;
		}
		echo '<table width="100%"><tr bcolor="#E9F2F8"><th>Tipo</th><th>Cantidad</th></tr>';
		$array_renglon=array();
		if($_POST['reg']==1){
			$array_renglon = $array_verificaciones;
		}
		elseif($_POST['reg']==2){
			$array_renglon = $array_reverificaciones;
		}
		$t=0;
		foreach($array_renglon as $k=>$v){
			rowb();
			echo '<td>'.$array_engomado[$k].'</td>';
			echo '<td align="right">'.number_format($v,0).'</td>';
			echo '</tr>';
			$t+=$v;
		}
		echo '<tr bgcolor="#E9F2F8"><th>Total</th><th align="right">'.number_format($t,0).'</th></tr>';
		echo '</table>';
		if($_POST['reg']==2){
			$tipo_cliente=array(-1=>'Particular', 0=>'Taller', 1=>'Agencia');
			echo '<table width="100%"><tr bgcoor="#E9F2F8"><th>Tipo Cliente</th><th>Cantidad</th></tr>';
			$t=0;
			foreach($array_tiposdepositantes as $k=>$v){
				rowb();
				echo '<td>'.$tipo_cliente[$k].'</td>';
				echo '<td align="right">'.number_format($v,0).'</td>';
				echo '</tr>';
				$t+=$v;
			}
			echo '<tr bgclor="#E9F2F8"><th>Total</th><th align="right">'.number_format($t,0).'</th></tr>';
			echo '</table>';
		}
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		if($_POST['archivoname']!='reporte_aforo3'){
			$_POST['fecha_ini']=fechaLocal();
			$_POST['fecha_fin']=fechaLocal();
		}
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0" title="Imprimir"></a>Imprimir</td>
				<td><a href="#" onClick="atcr(\'\',\'_blank\',\'101\',\'0\');"><img src="images/b_print.png" border="0" title="Imprimir"></a>Soporte</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.$_POST['fecha_ini'].'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.$_POST['fecha_fin'].'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
	
bottom();



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
			objeto.open("POST","reporte_aforo3.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}';	
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
	
	</Script>
';

?>

