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
if($_POST['cmd']==102) {
			require_once('../dompdf/dompdf_config.inc.php');
		$reporte.='<html><head>
      <style type="text/css">
	                    top  lado      ladoiz
		 @page{ margin: 8in 0.5in 1px 0.5in;}
		</style>
		 </head><body><table width="100%"><tr><th align="center" style="font-size:26px">Soporte del Centro: '.$array_plazas[$_POST['plazausuario']].'</th></tr><tr><th align="right">Fecha: '.fechaLocal().'  '.horaLocal().'</th></tr><tr><td>&nbsp;</td></tr>
		 <tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr></table>';
		//Listado de plazas
		$select= " SELECT a.cve,a.nombre, COUNT(b.cve), SUM(b.monto) FROM cat_lineas a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.linea  
		INNER JOIN cobro_engomado c ON c.plaza = b.plaza AND c.cve = b.ticket WHERE a.plaza='".$_POST['plazausuario']."' ";
		if ($_POST['engomado']!="") { $select.=" AND b.engomado='".$_POST['engomado']."' "; }	
		$select .= " AND b.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.estatus!='C'";
		$select.=" GROUP BY a.cve ORDER BY a.nombre";
		$res=mysql_query($select) or die ( mysql_error());
		$totalRegistros = mysql_num_rows($res);		
		
		$indice_=mysql_num_rows($res);
		if(mysql_num_rows($res)>0) 
		{
			$array_lineas = array();
			$total = 0;
			$total2 = 0;
			while($row = mysql_fetch_array($res)){
				$array_lineas[] = $row;
				$total+=$row[2];
				$total2+=$row[3];
			}

			$x++;
			$data=array();
			$legends=array();
			$z=0;
		$reporte.='<table border="0" style="font-size:13px" width="100%"><tr>';
			foreach($array_lineas as $row) {
				$cve_=$row['cve'];
				$nom_=$row['nombre'];
				$res=mysql_query("SELECT * FROM cat_lineas WHERE plaza='".$_POST['plazausuario']."' AND cve='".$row['cve']."'");
		$row = mysql_fetch_assoc($res);
//		$reporte.= '<h2>Productividad de la Linea '.$row['nombre'].' del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'];
		$filtro_engomado = "";
		if($_POST['engomado'] > 0){
//			$reporte.= ' del certificado '.$array_engomado[$_POST['engomado']];
			$filtro_engomado .= " AND a.engomado = '".$_POST['engomado']."'";
		}
//		$reporte.= '</h2>';

		$res = mysql_query("SELECT COUNT(a.cve),SUM(b.monto_verificacion),SUM(b.monto),b.tipo_venta FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 AND a.fecha<='".$_POST['fecha_fin']."' AND a.linea = '".$row['cve']."' GROUP BY b.tipo_venta");
		$array_tipo_venta = array();
		$total1 = 0;
		$total2 = 0;
		while($row = mysql_fetch_array($res)){
			$array_tipo_venta[$row[3]][0]=$row[0];
			$array_tipo_venta[$row[3]][1]=$row[1];
			$array_tipo_venta[$row[3]][2]=$row[2];
			$total1+=$row[0];
			$total2+=$row[2];
		}
		if($z==4){$reporte.='<tr><td colspan="3"><table width="100%" border=""><tr><th align="center" style="font-size:26px">Soporte del Centro: '.$array_plazas[$_POST['plazausuario']].'</th></tr><tr><th align="right">Fecha: '.fechaLocal().'  '.horaLocal().'</th></tr><tr><td>&nbsp;</td></tr>
		 <tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr></table></td></tr>';}
		 if($z==2 || $z==4 || $z==6 ||$z==8 || $z==$indice_){$reporte.='<tr>';}
		$reporte.='<td><table><tr><th style="font-size:15px">'.$nom_.' '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</th></tr></table>
				<table width="100%" border="1" style="font-size:13px"><tr bgclor="#E9F2F8"><th>&nbsp;</th><th>Cantidad de Certificados</th><th>Importe</th></tr>';
//		rowb();
		$reporte.= '<tr><td>AFORO</td><td align="right">'.number_format($total1,0).'</td><td align="right">'.number_format($total2,2).'</td></tr>';
		$reporte.= '<tr><td colspan="3">&nbsp;</td></tr>';
//		$reporte.= '<tr><td colspan="3">&nbsp;</td></tr>';
//		rowb();
		$reporte.= '<tr><td>Con Pago</td><td align="right">'.number_format($array_tipo_venta[0][0],0).'</td><td align="right">'.number_format($array_tipo_venta[0][1],2).'</td></tr>';
//		rowb();
		$reporte.= '<tr><td>Cortesia</td><td align="right">'.number_format($array_tipo_venta[2][0],0).'</td><td align="right">'.number_format($array_tipo_venta[2][1],2).'</td></tr>';
//		rowb();
		$reporte.= '<tr><td>Sin Pago</td><td align="right">'.number_format($array_tipo_venta[1][0],0).'</td><td align="right">'.number_format($array_tipo_venta[1][1],2).'</td></tr>';

		$res = mysql_query("SELECT a.cve, b.monto, b.monto_verificacion, a.engomado, 
			IF(a.engomado != 19, 1, 0) as certificado, IF(a.engomado = 19, 1, 0) as rechazo, 
			IF(a.engomado = 19, b.monto, 0) as rechazo_importe
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 AND a.fecha<='".$_POST['fecha_fin']."' AND a.linea = '".$cve_."' AND b.tipo_venta IN (0,2) GROUP BY b.cve");
		$array_renglon = array();
		$array_tickets = array();
		while($row = mysql_fetch_array($res)){
			$arry_tickets[$row['cve']] = $row;
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
			 	AND a.fecha<='".$_POST['fecha_fin']."' AND a.linea = '".$cve_."' AND b.tipo_venta = 1 GROUP BY b.ticketpago");
		while($row = mysql_fetch_array($res)){
			$arry_tickets[$row['cve']] = $row;
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
//		rowb();
		$reporte.= '<tr><td>Certificados</td><td align="right">'.number_format($array_renglon['certificado']['cantidad'],0).'</td><td align="right">'.number_format($array_renglon['certificado']['importe'],2).'</td></tr>';
		$t1+=$array_renglon['certificado']['cantidad'];
		$t2+=$array_renglon['certificado']['importe'];
		
//		rowb();
		$reporte.= '<tr><td>Reverificaciones</td><td align="right">'.number_format($array_renglon['reverificacion']['cantidad'],0).'</td><td align="right">'.number_format($array_renglon['reverificacion']['importe'],2).'</td></tr>';
		$t1+=$array_renglon['reverificacion']['cantidad'];
		$t2+=$array_renglon['reverificacion']['importe'];
//		rowb();
		$reporte.= '<tr><td>Rechazos</td><td align="right">'.number_format($array_renglon['rechazos']['cantidad'],0).'</td><td align="right">'.number_format($array_renglon['rechazos']['importe'],2).'</td></tr>';
		$t1+=$array_renglon['rechazos']['cantidad'];
		$t2+=$array_renglon['rechazos']['importe'];
//		rowb();
		$res = mysql_query("SELECT COUNT(a.cve),SUM(b.precio) FROM certificados_cancelados a INNER JOIN engomados b ON b.cve = a.engomado 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.linea = '".$cve_."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 AND a.fecha<='".$_POST['fecha_fin']."'");
		$row = mysql_fetch_array($res);
//		rowb();
		$reporte.= '<tr><td>Cancelados</td><td align="right">'.number_format($row[0],0).'</td><td align="right">'.number_format(0,2).'</td></tr>';
		$reporte.= '<tr bcolor="#E9F2F8"><th>Totales</th><td align="right">'.number_format($t1,0).'</th><td align="right">'.number_format($t2,2).'</th></tr>
		<tr gcolor="#E9F2F8"><td colspan="3" rowspan="2">&nbsp;</td></tr>';
		$reporte.= '</table>';$reporte.='</td><td>&nbsp;</td>';
		$z++;
		if($z==2 || $z==4 || $z==6 ||$z==8 || $z==$indice_){$reporte.='</tr>';}
			
			}
			$report.= '
			</table>';
			
		} else {
			$reporte.= '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
		}
				$reporte.='</body></html>';
	$mipdf = new DOMPDF();
//	$mipdf->margin: "0";
	//$mipdf->set_paper("A4", "portrait");
	$mipdf->set_paper("A4", "portrait");
//	$mipdf->set_paper("Legal", "landscape");
	$mipdf->load_html($reporte);
	$mipdf->render();
	$mipdf ->stream();

//		echo $reporte;
		exit();	
}	

	if($_POST['cmd']==101){
			require_once('../dompdf/dompdf_config.inc.php');
		$html='<html><head>
      <style type="text/css">
	                    top  lado      ladoiz
		 @page{ margin: 5in 0.5in 1px 0.5in;}
		</style>
		 </head><body><h1 align="center">Plaza: '.$array_plazas[$_POST['plazausuario']].'</h2><h1 align="right">Fecha: '.fechaLocal().'-'.horaLocal().'</h2></br><h2>Productividad de la Linea '.$row['nombre'].' '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';
/*		echo '<table>';
		$html.= '
			<tr>';
			$html.= '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'productividad_lineas.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
			$html.='</tr>';
		$html.= '</table>';*/
		$res=mysql_query("SELECT * FROM cat_lineas WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
		$row = mysql_fetch_assoc($res);
//		$html.= '<h2>Productividad de la Linea '.$row['nombre'].' del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'];
		$filtro_engomado = "";
		if($_POST['engomado'] > 0){
			$html.= ' del certificado '.$array_engomado[$_POST['engomado']];
			$filtro_engomado .= " AND a.engomado = '".$_POST['engomado']."'";
		}
//		$html.= '</h2>';

		$res = mysql_query("SELECT COUNT(a.cve),SUM(b.monto_verificacion),SUM(b.monto),b.tipo_venta FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 AND a.fecha<='".$_POST['fecha_fin']."' AND a.linea = '".$_POST['reg']."' GROUP BY b.tipo_venta");
		$array_tipo_venta = array();
		$total1 = 0;
		$total2 = 0;
		while($row = mysql_fetch_array($res)){
			$array_tipo_venta[$row[3]][0]=$row[0];
			$array_tipo_venta[$row[3]][1]=$row[1];
			$array_tipo_venta[$row[3]][2]=$row[2];
			$total1+=$row[0];
			$total2+=$row[2];
		}
		$html.= '<table width="100%" border="1" style="font-size:13px"><tr bcolor="#E9F2F8"><th>&nbsp;</th><th>Cantidad de Certificados</th><th>Importe</th></tr>';
//		rowb();
		$html.= '<tr><td>AFORO</td><td align="right">'.number_format($total1,0).'</td><td align="right">'.number_format($total2,2).'</td></tr>';
		$html.= '<tr><td colspan="3">&nbsp;</td></tr>';
		$html.= '<tr><td colspan="3">&nbsp;</td></tr>';
//		rowb();
		$html.= '<tr><td>Con Pago</td><td align="right">'.number_format($array_tipo_venta[0][0],0).'</td><td align="right">'.number_format($array_tipo_venta[0][1],2).'</td></tr>';
//		rowb();
		$html.= '<tr><td>Cortesia</td><td align="right">'.number_format($array_tipo_venta[2][0],0).'</td><td align="right">'.number_format($array_tipo_venta[2][1],2).'</td></tr>';
//		rowb();
		$html.= '<tr><td>Sin Pago</td><td align="right">'.number_format($array_tipo_venta[1][0],0).'</td><td align="right">'.number_format($array_tipo_venta[1][1],2).'</td></tr>';

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
		$res = mysql_query("SELECT a.cve, b.monto, b.monto_verificacion, a.engomado, 
			IF(a.engomado != 19, 1, 0) as certificado, IF(a.engomado = 19, 1, 0) as rechazo, 
			IF(a.engomado = 19, b.monto, 0) as rechazo_importe
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 AND a.fecha<='".$_POST['fecha_fin']."' AND a.linea = '".$_POST['reg']."' AND b.tipo_venta IN (0,2) GROUP BY b.cve");
		$array_renglon = array();
		$array_tickets = array();
		while($row = mysql_fetch_array($res)){
			$arry_tickets[$row['cve']] = $row;
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
			 	AND a.fecha<='".$_POST['fecha_fin']."' AND a.linea = '".$_POST['reg']."' AND b.tipo_venta = 1 GROUP BY b.ticketpago");
		while($row = mysql_fetch_array($res)){
			$arry_tickets[$row['cve']] = $row;
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
		$html.= '<tr><td colspan="3">&nbsp;</td></tr>';
		$html.= '<tr><td colspan="3">&nbsp;</td></tr>';
		$t1=0;
		$t2=0;
//		rowb();
		$html.= '<tr><td>Certificados</td><td align="right">'.number_format($array_renglon['certificado']['cantidad'],0).'</td><td align="right">'.number_format($array_renglon['certificado']['importe'],2).'</td></tr>';
		$t1+=$array_renglon['certificado']['cantidad'];
		$t2+=$array_renglon['certificado']['importe'];
		
//		rowb();
		$html.= '<tr><td>Reverificaciones</td><td align="right">'.number_format($array_renglon['reverificacion']['cantidad'],0).'</td><td align="right">'.number_format($array_renglon['reverificacion']['importe'],2).'</td></tr>';
		$t1+=$array_renglon['reverificacion']['cantidad'];
		$t2+=$array_renglon['reverificacion']['importe'];
//		rowb();
		$html.= '<tr><td>Rechazos</td><td align="right">'.number_format($array_renglon['rechazos']['cantidad'],0).'</td><td align="right">'.number_format($array_renglon['rechazos']['importe'],2).'</td></tr>';
		$t1+=$array_renglon['rechazos']['cantidad'];
		$t2+=$array_renglon['rechazos']['importe'];
//		rowb();
		$res = mysql_query("SELECT COUNT(a.cve),SUM(b.precio) FROM certificados_cancelados a INNER JOIN engomados b ON b.cve = a.engomado 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.linea = '".$_POST['reg']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 AND a.fecha<='".$_POST['fecha_fin']."'");
		$row = mysql_fetch_array($res);
//		rowb();
		$html.= '<tr><td>Cancelados</td><td align="right">'.number_format($row[0],0).'</td><td align="right">'.number_format(0,2).'</td></tr>';
		$html.= '<tr bcolor="#E9F2F8"><th>Totales</th><td align="right">'.number_format($t1,0).'</th><td align="right">'.number_format($t2,2).'</th></tr>';
		$html.= '</table>';$html.='</body></html>';
	$mipdf = new DOMPDF();
//	$mipdf->margin: "0";
	//$mipdf->set_paper("A4", "portrait");
//	$mipdf->set_paper("A4", "portrait");
	$mipdf->set_paper("Legal", "landscape");
	$mipdf->load_html($html);
	$mipdf->render();
	$mipdf ->stream();
	}

if($_POST['cmd']==100) {
			require_once('../dompdf/dompdf_config.inc.php');
		$reporte.='<html><head>
      <style type="text/css">
	                    top  lado      ladoiz
		 @page{ margin: 5in 0.5in 1px 0.5in;}
		</style>
		 </head><body><h1 align="center">Plaza: '.$array_plazas[$_POST['plazausuario']].'</h2><h1 align="right">Fecha: '.fechaLocal().'-'.horaLocal().'</h2></br><h2>Productividad de Lineas del: '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';
		//Listado de plazas
		$select= " SELECT a.cve,a.nombre, COUNT(b.cve), SUM(b.monto) FROM cat_lineas a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.linea  
		INNER JOIN cobro_engomado c ON c.plaza = b.plaza AND c.cve = b.ticket WHERE a.plaza='".$_POST['plazausuario']."' ";
		if ($_POST['engomado']!="") { $select.=" AND b.engomado='".$_POST['engomado']."' "; }	
		$select .= " AND b.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.estatus!='C'";
		$select.=" GROUP BY a.cve ORDER BY a.nombre";
		$res=mysql_query($select) or die ( mysql_error());
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			$array_lineas = array();
			$total = 0;
			$total2 = 0;
			while($row = mysql_fetch_array($res)){
				$array_lineas[] = $row;
				$total+=$row[2];
				$total2+=$row[3];
			}
			$reporte.= '<table width="100%" border="1" style="font-size:13px" class="">';
			$reporte.= '<tr><td bgcoor="#E9F2F8" colspan="4">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			$reporte.= '<tr bgcolr="#E9F2F8"><th>Linea</th><th>Aforo</th><th>%</th><th>Importe</th>';
			$reporte.= '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$x++;
			$data=array();
			$legends=array();
			foreach($array_lineas as $row) {
		//		$reporte.=rowb(false);
				$reporte.= '<tr><td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
				$reporte.= '<td align="right">'.number_format($row[2],0).'</td>';
				$reporte.= '<td align="right">'.number_format($row[2]*100/$total,1).'</td>';
				$reporte.= '<td align="right">'.number_format($row[3],0).'</td>';
				$reporte.= '</tr>';
				//$total+=$row[1];
				$x++;
				$data[]=$row[1];
				$legends[]=$row['nombre'];
			}
			$reporte.= '	
				<tr>
				<td bgcolr="#E9F2F8">'.$x.' Registro(s)</td>
				<td bgcoor="#E9F2F8" align="right">'.number_format($total,0).'</td>
				<td bgclor="#E9F2F8" align="right">100</td>
				<td bgolor="#E9F2F8" align="right">'.number_format($total2,0).'</td>
				</tr>
			</table>';


			/*if(count($data)>0){
				$data2=array();
				$data2[0] = array('Entrega');
				foreach($data as $datos){
					$data2[0][] = $datos;
				}
				//$reporte.='<img src="graficabar.php?fecha_ini='.$_POST['fecha_ini'].'&fecha_fin='.$_POST['fecha_fin'].'&reporte=desglose_cuentas_grupo">';
				require_once("../phplot/phplot.php");
				$plot = new PHPlot(1000,800);
				$plot->SetDataValues($data2);
				$plot->SetDataType('text-data');
				$plot->SetPlotType('pie');
				$plot->SetTitle('Productividad Lineas');
				$plot->SetLegend($legends);
				$plot->SetFileFormat("jpg");
				$plot->SetFailureImage(False);
				//$plot->SetPrintImage(False);
				$plot->SetIsInline(True);
				$plot->SetOutputFile("../cfdi/grafica.jpg");
				$plot->SetImageBorderType('plain');
				
				//$plot->SetXDataLabelPos('plotin');
				
				
				//foreach ($data as $row) $plot->SetLegend($row[0]); // Copy labels to legend
				//$plot->SetXTickLabelPos('none');
				//$plot->SetXTickPos('none');
				$plot->DrawGraph();
				//$reporte .= '<img src="../cfdi/grafica.jpg?'.date("Y-m-d H:i:s").'">';
			}*/
			
		} else {
			$reporte.= '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
		}
				$html.='</body></html>';
	$mipdf = new DOMPDF();
//	$mipdf->margin: "0";
	//$mipdf->set_paper("A4", "portrait");
//	$mipdf->set_paper("A4", "portrait");
	$mipdf->set_paper("Legal", "landscape");
	$mipdf->load_html($reporte);
	$mipdf->render();
	$mipdf ->stream();

//		echo $reporte;
		exit();	
}	

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT a.cve,a.nombre, COUNT(b.cve), SUM(b.monto) FROM cat_lineas a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.linea  
		INNER JOIN cobro_engomado c ON c.plaza = b.plaza AND c.cve = b.ticket WHERE a.plaza='".$_POST['plazausuario']."' ";
		if ($_POST['engomado']!="") { $select.=" AND b.engomado='".$_POST['engomado']."' "; }	
		$select .= " AND b.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.estatus!='C'";
		$select.=" GROUP BY a.cve ORDER BY a.nombre";
		$res=mysql_query($select) or die ( mysql_error());
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			$array_lineas = array();
			$total = 0;
			$total2 = 0;
			while($row = mysql_fetch_array($res)){
				$array_lineas[] = $row;
				$total+=$row[2];
				$total2+=$row[3];
			}
			$reporte.= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			$reporte.= '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			$reporte.= '<tr bgcolor="#E9F2F8"><th>Linea</th><th>Aforo</th><th>%</th><th>Importe</th>';
			$reporte.= '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$x++;
			$data=array();
			$legends=array();
			foreach($array_lineas as $row) {
				$reporte.=rowb(false);
				$reporte.= '<td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
				$reporte.= '<td align="right">'.number_format($row[2],0).'</td>';
				$reporte.= '<td align="right">'.number_format($row[2]*100/$total,1).'</td>';
				$reporte.= '<td align="right"><a href="#" onClick="atcr(\'productividad_lineas.php\',\'\',1,'.$row['cve'].')">'.number_format($row[3],0).'</a></td>';
				$reporte.= '</tr>';
				//$total+=$row[1];
				$x++;
				$data[]=$row[1];
				$legends[]=$row['nombre'];
			}
			$reporte.= '	
				<tr>
				<td bgcolor="#E9F2F8">'.$x.' Registro(s)</td>
				<td bgcolor="#E9F2F8" align="right">'.number_format($total,0).'</td>
				<td bgcolor="#E9F2F8" align="right">100</td>
				<td bgcolor="#E9F2F8" align="right">'.number_format($total2,0).'</td>
				</tr>
			</table>';


			/*if(count($data)>0){
				$data2=array();
				$data2[0] = array('Entrega');
				foreach($data as $datos){
					$data2[0][] = $datos;
				}
				//$reporte.='<img src="graficabar.php?fecha_ini='.$_POST['fecha_ini'].'&fecha_fin='.$_POST['fecha_fin'].'&reporte=desglose_cuentas_grupo">';
				require_once("../phplot/phplot.php");
				$plot = new PHPlot(1000,800);
				$plot->SetDataValues($data2);
				$plot->SetDataType('text-data');
				$plot->SetPlotType('pie');
				$plot->SetTitle('Productividad Lineas');
				$plot->SetLegend($legends);
				$plot->SetFileFormat("jpg");
				$plot->SetFailureImage(False);
				//$plot->SetPrintImage(False);
				$plot->SetIsInline(True);
				$plot->SetOutputFile("../cfdi/grafica.jpg");
				$plot->SetImageBorderType('plain');
				
				//$plot->SetXDataLabelPos('plotin');
				
				
				//foreach ($data as $row) $plot->SetLegend($row[0]); // Copy labels to legend
				//$plot->SetXTickLabelPos('none');
				//$plot->SetXTickPos('none');
				$plot->DrawGraph();
				//$reporte .= '<img src="../cfdi/grafica.jpg?'.date("Y-m-d H:i:s").'">';
			}*/
			
		} else {
			$reporte.= '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
		}

		echo $reporte;
		exit();	
}	


top($_SESSION);

	if($_POST['cmd']>0){
		echo '<input type="hidden" name="archivoname" value="productividadlineas">';
		echo '<input type="hidden" name="fecha_ini" value="'.$_POST['fecha_ini'].'">';
		echo '<input type="hidden" name="fecha_fin" value="'.$_POST['fecha_fin'].'">';
		echo '<input type="hidden" name="engomado" value="'.$_POST['engomado'].'">';
	}

	if($_POST['cmd']==1){
			echo'<input type="hidden" id="engomado" name="engomado" value="'.$_POST['engomado'].'">
			 <input type="hidden" id="reg" name="reg" value="'.$_POST['reg'].'">';
		echo '<table>';
		echo '
			<tr>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'productividad_lineas.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'\',\'_blank\',\'101\',\'0\');"><img src="images/b_print.png" border="0" title="Imprimir"></a>Imprimir</td>';
			echo'</tr>';
		echo '</table>';
		$res=mysql_query("SELECT * FROM cat_lineas WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
		$row = mysql_fetch_assoc($res);
		echo '<h2>Productividad de la Linea '.$row['nombre'].' del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'];
		$filtro_engomado = "";
		if($_POST['engomado'] > 0){
			echo ' del certificado '.$array_engomado[$_POST['engomado']];
			$filtro_engomado .= " AND a.engomado = '".$_POST['engomado']."'";
		}
		echo '</h2>';

		$res = mysql_query("SELECT COUNT(a.cve),SUM(b.monto_verificacion),SUM(b.monto),b.tipo_venta FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 AND a.fecha<='".$_POST['fecha_fin']."' AND a.linea = '".$_POST['reg']."' GROUP BY b.tipo_venta");
		$array_tipo_venta = array();
		$total1 = 0;
		$total2 = 0;
		while($row = mysql_fetch_array($res)){
			$array_tipo_venta[$row[3]][0]=$row[0];
			$array_tipo_venta[$row[3]][1]=$row[1];
			$array_tipo_venta[$row[3]][2]=$row[2];
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
		echo '<td>Cortesia</td><td align="right">'.number_format($array_tipo_venta[2][0],0).'</td><td align="right">'.number_format($array_tipo_venta[2][1],2).'</td></tr>';
		rowb();
		echo '<td>Sin Pago</td><td align="right">'.number_format($array_tipo_venta[1][0],0).'</td><td align="right">'.number_format($array_tipo_venta[1][1],2).'</td></tr>';

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
		$res = mysql_query("SELECT a.cve, b.monto, b.monto_verificacion, a.engomado, 
			IF(a.engomado != 19, 1, 0) as certificado, IF(a.engomado = 19, 1, 0) as rechazo, 
			IF(a.engomado = 19, b.monto, 0) as rechazo_importe
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 AND a.fecha<='".$_POST['fecha_fin']."' AND a.linea = '".$_POST['reg']."' AND b.tipo_venta IN (0,2) GROUP BY b.cve");
		$array_renglon = array();
		$array_tickets = array();
		while($row = mysql_fetch_array($res)){
			$arry_tickets[$row['cve']] = $row;
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
			 	AND a.fecha<='".$_POST['fecha_fin']."' AND a.linea = '".$_POST['reg']."' AND b.tipo_venta = 1 GROUP BY b.ticketpago");
		while($row = mysql_fetch_array($res)){
			$arry_tickets[$row['cve']] = $row;
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
		echo '<td>Certificados</td><td align="right">'.number_format($array_renglon['certificado']['cantidad'],0).'</td><td align="right">'.number_format($array_renglon['certificado']['importe'],2).'</td></tr>';
		$t1+=$array_renglon['certificado']['cantidad'];
		$t2+=$array_renglon['certificado']['importe'];
		
		rowb();
		echo '<td>Reverificaciones</td><td align="right">'.number_format($array_renglon['reverificacion']['cantidad'],0).'</td><td align="right">'.number_format($array_renglon['reverificacion']['importe'],2).'</td></tr>';
		$t1+=$array_renglon['reverificacion']['cantidad'];
		$t2+=$array_renglon['reverificacion']['importe'];
		rowb();
		echo '<td>Rechazos</td><td align="right">'.number_format($array_renglon['rechazos']['cantidad'],0).'</td><td align="right">'.number_format($array_renglon['rechazos']['importe'],2).'</td></tr>';
		$t1+=$array_renglon['rechazos']['cantidad'];
		$t2+=$array_renglon['rechazos']['importe'];
		rowb();
		$res = mysql_query("SELECT COUNT(a.cve),SUM(b.precio) FROM certificados_cancelados a INNER JOIN engomados b ON b.cve = a.engomado 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.linea = '".$_POST['reg']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 AND a.fecha<='".$_POST['fecha_fin']."'");
		$row = mysql_fetch_array($res);
		rowb();
		echo '<td>Cancelados</td><td align="right">'.number_format($row[0],0).'</td><td align="right">'.number_format(0,2).'</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th>Totales</th><td align="right">'.number_format($t1,0).'</th><td align="right">'.number_format($t2,2).'</th></tr>';
		echo '</table>';
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		if($_POST['archivoname']!='productividadlineas'){
			$_POST['fecha_ini']=fechaLocal();
			$_POST['fecha_fin']=fechaLocal();
			$_POST['engomado']=0;
		}
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0" title="Imprimir"></a>Imprimir</td>
				<td><a href="#" onClick="atcr(\'\',\'_blank\',\'102\',\'0\');"><img src="images/b_print.png" border="0" title="Imprimir"></a>Impresion de Soporte</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.$_POST['fecha_ini'].'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.$_POST['fecha_fin'].'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Tipo de Certificado</td><td><select name="engomado" id="engomado"><option value="">Todos</option>';
		foreach($array_engomado as $k=>$v){
			echo '<option value="'.$k.'"';
			if($_POST['engomado'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
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
			objeto.open("POST","productividad_lineas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&engomado="+document.getElementById("engomado").value);
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

