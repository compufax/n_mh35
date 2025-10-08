<?php
include("main.php");

$res=mysql_query("SELECT * FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);
$PlazaLocalidad=$Plaza['localidad_id'];
$array_engomado = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' ORDER BY nombre");
$res = mysql_query("SELECT numero, nombre, max(precio) as precio, group_concat(cve) as cves FROM engomados WHERE localidad='".$Plaza['localidad_id']."' GROUP BY numero ORDER BY numero");
while($row=mysql_fetch_array($res)){
	$res1=mysql_query("SELECT nombre,precio FROM engomados WHERE localidad = '".$Plaza['localidad_id']."'  AND numero='".$row['numero']."' ORDER BY cve");
	$row1=mysql_fetch_array($res1);
	$array_engomado[$row['numero']]['nombre']=$row1['nombre'];
	$array_engomado[$row['numero']]['precio']=$row1['precio'];
	$array_engomado[$row['numero']]['cves']=$row['cves'];
	$array_engomado[$row['numero']]['cant']=0;
	if($row1['nombre']=='RECHAZO')
		$numero_rechazo = $row['numero'];
}

$array_motivos_intento = array();
$res = mysql_query("SELECT * FROM motivos_intento WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_motivos_intento[$row['cve']]=$row['nombre'];
}

if($_POST['cmd']==101){
	$res=mysql_query("SELECT * FROM plazas WHERE cve='".$_POST['plazausuario']."'");
	$Plaza=mysql_fetch_array($res);
	header("Content-type: application/vnd.ms-excel; name='excel'");
	header("Content-Disposition: filename=CitasxDia.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo '<h1>Poliza '.$Plaza['numero'].' '.$Plaza['nombre'].' del dia '.$_POST['fecha'].'</h1>';
	echo '<table><tr>
	<td valign="top"><h1>Tipo de Certificado</h1>
		<table border=1><tr><th>Numero</th><th>Nombre</th><th>Precio</th></tr>';
		foreach($array_engomado as $k=>$v){
			echo '<tr>';
			echo '<td align="center">'.$k.'</td>';
			echo '<td align="left">'.$v['nombre'].'</td>';
			echo '<td align="right">'.$v['precio'].'</td>';
			echo '</tr>';
		}
		echo '</table>
	</td>
	<td>&nbsp;</td>
	<td valign="top"><h1>Tipos</h1>
		<table border="1"><tr><td align="center">E</td><td>Venta Efectivo</td></tr>
		<tr><td align="center">C</td><td>Venta Credito</td></tr>
		<tr><td align="center">TC</td><td>Tarjeta Credito</td></tr>
		<tr><td align="center">NV</td><td>No Verificado</td></tr>
		<tr><td align="center">PA</td><td>Pago Anticipado</td></tr>
		<tr><td align="center">RV</td><td>Reverificacion</td></tr>
		<tr><td align="center">CT</td><td>Cortesia</td></tr>
		<tr><td align="center">DV</td><td>Devolucion</td></tr>
		<tr><td align="center">RE</td><td>Recuperacion</td></tr>
		<tr><td align="center">DRV</td><td>Descuentos y Rebajas de Venta</td></tr>
		</table>
	</td>
	<td>&nbsp;</td>
	<td valign="top" id="depositos"></td></tr></table>';
	
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Tipo</th><th>&nbsp</th><th>Certificado</th><th>Ticket</th><th>Placa</th><th>Fecha Venta</th>';
	foreach($array_engomado as $k=>$v) echo '<th>'.$v['nombre'].'</th>';
	echo '<th>Monto</th><th>IVA</th><th>Total</th><th>Folio de Entrega</th>';
	echo '</tr>';
	
	$tc=0;
	$ttc=0;
	$tp=0;
	$tnv=0;
	$c=1;
	$totales = array(0,0,0);
	$c1=0;
	$c2=count($array_engomado)-1;
	foreach($array_engomado as $k=>$v){
		$select = "SELECT a.* FROM (
		SELECT a.placa,a.ticket,b.fecha as fechaticket,a.cve, IF(b.fecha!=a.fecha,6,b.tipo_pago) as tipo_pago, 0 as cancelado, a.engomado as engomadoentrega, 
		if(b.tipo_pago=6 OR b.fecha!=a.fecha,0,b.monto) as monto, a.certificado as certificado, IF(a.fecha!=b.fecha,1,0) as diffechas, b.motivo_intento, b.engomado as engomadoventa
		FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.fecha='".$_POST['fecha']."' AND a.estatus != 'C' AND a.engomado IN (".$v['cves'].")
		UNION ALL 
		SELECT '' as placa,'' as ticket,'' as fechaticket,'' as cve, 0 as tipo_pago, 1 as cancelado, engomado as engomadoentrega, 0 as monto, certificado, 0 as diffechas, 0 as motivo_intento, 0 as engomadoventa FROM certificados_cancelados 
		WHERE plaza='".$_POST['plazausuario']."' AND fecha='".$_POST['fecha']."' AND engomado IN (".$v['cves'].") AND estatus!='C') as a ORDER BY a.certificado";
		$select;
		$res = mysql_query($select);
		$fcertificado=-1;
		while($row = mysql_fetch_array($res)){
			if($fcertificado<0) $fcertificado = $row['certificado'];
			if($fcertificado!=$row['certificado']){
				$resOmiso = mysql_query("SELECT b.folio FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus!='C' AND a.engomado = '".$row['engomadoentrega']."' AND b.folio>='".$fcertificado."' AND b.folio<'".intval($row['certificado'])."' AND b.tipo=1 ORDER BY b.folio");
				while($rowOmiso = mysql_fetch_array($resOmiso)){
					rowb();
					echo '<td colspan="2">OMISO</td>';
					echo '<td align="center"><font color="RED">'.$rowOmiso['folio'].'</font></td>';
					echo '<td colspan="'.(7+count($array_engomado)).'">&nbsp;</td>';
					echo '</tr>';
					$fcertificado=$rowOmiso['folio']+1;
				}
			}
			rowb();
			if($row['tipo_pago']==1 && $row['monto']>0){
				$tipo='E';
			}
			elseif(($row['tipo_pago']==2) && $row['monto']>0){
				$tipo='C';
			}
			elseif(($row['tipo_pago']==5) && $row['monto']>0){
				$tipo='TC';
			}
			else{
				$tipo='';
			}
			$res2 = mysql_query("SELECT a.fecha,a.cve,a.monto FROM cobro_engomado a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket WHERE a.plaza = '".$_POST['plazausuario']."' AND a.cve>'".$row['ticket']."' AND a.placa = '".$row['placa']."' AND a.estatus!='C' AND b.estatus!='C' ORDER BY a.cve LIMIT 1");
			$row2 = mysql_fetch_array($res2);
			$res3 = mysql_query("SELECT b.fecha,a.cve,b.cve FROM cobro_engomado a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket WHERE a.plaza = '".$_POST['plazausuario']."' AND a.cve<'".$row['ticket']."' AND a.placa = '".$row['placa']."' AND a.estatus!='C' AND b.estatus!='C' AND b.engomado NOT IN (9,19) ORDER BY a.cve DESC LIMIT 1");
			$row3 = mysql_fetch_array($res3);
			$res1 = mysql_query("SELECT fecha,cve,monto FROM cobro_engomado WHERE plaza = '".$_POST['plazausuario']."' AND cve<'".$row['ticket']."' AND placa = '".$row['placa']."' AND estatus!='C' ORDER BY cve DESC LIMIT 1");
			$row1 = mysql_fetch_array($res1);
			echo '<td align="center">'.$tipo.'</td>';
			echo '<td align="center">'.$c.'</td>';
			if($fcertificado!=$row['certificado']){
				echo '<td align="center"><font color="RED">'.$row['certificado'].'</font></td>';
				$fcertificado=$row['certificado'];
			}
			else{
				echo '<td align="center">'.$row['certificado'].'</td>';
			}
			echo '<td align="center">'.$row['ticket'].'</td>';
			echo '<td align="center">'.$row['placa'].'</td>';
			if($row['monto']==0 && $row['fechaticket']>$row1['fecha']){
				if($row1['fecha']=='' && $row['tipo_pago']==6)
					echo '<td align="center"><font color="RED">DEPOSITO</font></td>';
				elseif($row1['fecha']=='')
					echo '<td align="center"><font color="RED">'.$array_motivos_intento[$row['motivo_intento']].'</font></td>';
				else
					echo '<td align="center"><font color="RED">'.$row1['fecha'].'</font></td>';
				
			}
			elseif($row['diffechas']!=1)
				echo '<td align="center">'.$row['fechaticket'].'</td>';
			else
				echo '<td align="center"><font color="RED">'.$row['fechaticket'].'</font></td>';
			for($i=0;$i<$c1;$i++) echo '<td>&nbsp;</td>';
			if($row['cancelado']==1)
				echo '<td align="center">CA</td>';
			elseif($k==$numero_rechazo)
				echo '<td align="center">NV</td>';
			elseif($row['engomadoventa']==23)
				echo '<td align="center">CT</td>';
			elseif($row['tipo_pago']==6)
				echo '<td align="center">PA</td>';
			elseif($row2['monto']==0 && $row['fechaticket']==$row2['fecha'] && $row3['fecha']!=$row['fechaticket'])
				echo '<td align="center">RV</td>';
			elseif($row['monto']==0 && $row['fechaticket']>$row1['fecha'])
				echo '<td align="center">PA</td>';
			else
				echo '<td align="center">'.$k.'</td>';
			for($i=0;$i<$c2;$i++) echo '<td>&nbsp;</td>';
			if($tipo == 'C' || $tipo == 'TC') $row['monto'] = 0;
			$iva=round($row['monto']*16/116,2);
			$subtotal=round($row['monto']-$iva,2);
			echo '<td align="right">'.number_format($subtotal,2).'</td>';
			echo '<td align="right">'.number_format($iva,2).'</td>';
			echo '<td align="right">'.number_format($row['monto'],2).'</td>';
			echo '<td align="center">'.$row['cve'].'</td>';
			$totales[0]+=$subtotal;
			$totales[1]+=$iva;
			$totales[2]+=$row['monto'];
			//if($row['diffechas']!=1)
			$array_engomado[$k]['cant']++;
			$c++;
			if($tipo=='C')
				$tc+=$row['monto'];
			elseif($tipo=='TC')
				$ttc+=$row['monto'];
			else
				$tp+=$row['monto'];
			$fcertificado++;
			echo '</tr>';
		}
		$c1++;
		$c2--;
	}
	
	$c1=0;
	$c2=count($array_engomado)-1;
	foreach($array_engomado as $k=>$v){
		$select = "SELECT a.cve,a.fecha,a.placa,  IF(a.tipo_pago=6,0,a.monto) as monto, a.tipo_pago FROM cobro_engomado a  
		LEFT JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket AND a.fecha=b.fecha AND b.estatus!='C'
		WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha='".$_POST['fecha']."' AND a.engomado IN (".$v['cves'].") AND a.estatus!='C'";
		if($PlazaLocalidad == 1) $select .= " AND a.monto > 0";
		$select .= " AND ISNULL(b.cve) ORDER BY a.cve";
		$res = mysql_query($select);
		while($row = mysql_fetch_array($res)){
			rowb();
			$tipo='NV';
			echo '<td align="center">'.$tipo.'</td>';
			echo '<td align="center">'.$c.'</td>';
			echo '<td align="center">&nbsp;</td>';
			echo '<td align="center">'.$row['cve'].'</td>';
			echo '<td align="center">'.$row['placa'].'</td>';
			echo '<td align="center">'.$row['fecha'].'</td>';
			for($i=0;$i<$c1;$i++) echo '<td>&nbsp;</td>';
			if($row['monto']==0)
				echo '<td align="center">PA</td>';
			else
				echo '<td align="center">'.$k.'</td>';
			for($i=0;$i<$c2;$i++) echo '<td>&nbsp;</td>';
			if($row['tipo_pago'] == 2 || $row['tipo_pago'] == 5) $row['monto'] = 0;	
			$iva=round($row['monto']*16/116,2);
			$subtotal=round($row['monto']-$iva,2);
			echo '<td align="right">'.number_format($subtotal,2).'</td>';
			echo '<td align="right">'.number_format($iva,2).'</td>';
			echo '<td align="right">'.number_format($row['monto'],2).'</td>';
			echo '<td align="center">'.$row['cve'].'</td>';
			$totales[0]+=$subtotal;
			$totales[1]+=$iva;
			$totales[2]+=$row['monto'];
			if($row['cancelado']!=1) $array_engomado[$k]['cant']++;
			$c++;
			$tnv+=$row['monto'];
			echo '</tr>';
		}
		$c1++;
		$c2--;
	}
	
	$tpa=0;
	$res = mysql_query("SELECT * FROM pagos_caja WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha='".$_POST['fecha']."' ORDER BY cve");
	while($row = mysql_fetch_array($res)){
		rowb();
		$tipo='PA';
		echo '<td align="center">'.$tipo.'</td>';
		echo '<td align="center">'.$c.'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		for($i=0;$i<count($array_engomado);$i++) echo '<td>&nbsp;</td>';
		if($row['forma_pago'] != 1 && $row['forma_pago'] != 3) $row['monto'] = 0;
		$iva=round($row['monto']*16/116,2);
		$iva=0;
		$subtotal=round($row['monto']-$iva,2);
		echo '<td align="right">'.number_format($subtotal,2).'</td>';
		echo '<td align="right">'.number_format($iva,2).'</td>';
		echo '<td align="right">'.number_format($row['monto'],2).'</td>';
		echo '<td align="center">&nbsp;</td>';
		$c++;
		$tpa+=$row['monto'];
		$totales[0]+=$subtotal;
		$totales[1]+=$iva;
		$totales[2]+=$row['monto'];
	}
	
	$tdv=0;
	$res = mysql_query("SELECT * FROM devolucion_certificado WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha='".$_POST['fecha']."' ORDER BY cve");
	while($row = mysql_fetch_array($res)){
		rowb();
		$tipo='DV';
		echo '<td align="center">'.$tipo.'</td>';
		echo '<td align="center">'.$c.'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['placa'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		for($i=0;$i<count($array_engomado);$i++) echo '<td>&nbsp;</td>';
		
		$iva=round($row['devolucion']*16/116,2);
		$iva=0;
		$subtotal=round($row['devolucion']-$iva,2);
		echo '<td align="right">-'.number_format($subtotal,2).'</td>';
		echo '<td align="right">'.number_format($iva,2).'</td>';
		echo '<td align="right">-'.number_format($row['devolucion'],2).'</td>';
		echo '<td align="center">&nbsp;</td>';
		$c++;
		$tdv+=$row['devolucion'];
		$totales[0]-=$subtotal;
		$totales[1]-=$iva;
		$totales[2]-=$row['devolucion'];
	}

	$res = mysql_query("SELECT * FROM devolucion_ajuste WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha='".$_POST['fecha']."' ORDER BY cve");
	while($row = mysql_fetch_array($res)){
		rowb();
		$tipo='DRV';
		echo '<td align="center">'.$tipo.'</td>';
		echo '<td align="center">'.$c.'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		for($i=0;$i<count($array_engomado);$i++) echo '<td>&nbsp;</td>';
		
		$iva=round($row['monto']*16/116,2);
		$iva=0;
		$subtotal=round($row['monto']-$iva,2);
		echo '<td align="right">-'.number_format($subtotal,2).'</td>';
		echo '<td align="right">'.number_format($iva,2).'</td>';
		echo '<td align="right">-'.number_format($row['monto'],2).'</td>';
		echo '<td align="center">&nbsp;</td>';
		$c++;
		$tdvr+=$row['monto'];
		$totales[0]-=$subtotal;
		$totales[1]-=$iva;
		$totales[2]-=$row['monto'];
	}
	
	$tre=0;
	$res = mysql_query("SELECT * FROM recuperacion_certificado WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha='".$_POST['fecha']."' ORDER BY cve");
	while($row = mysql_fetch_array($res)){
		rowb();
		$tipo='RE';
		echo '<td align="center">'.$tipo.'</td>';
		echo '<td align="center">'.$c.'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['placa'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		for($i=0;$i<count($array_engomado);$i++) echo '<td>&nbsp;</td>';
		
		$iva=round($row['recuperacion']*16/116,2);
		$iva=0;
		$subtotal=round($row['recuperacion']-$iva,2);
		echo '<td align="right">'.number_format($subtotal,2).'</td>';
		echo '<td align="right">'.number_format($iva,2).'</td>';
		echo '<td align="right">'.number_format($row['recuperacion'],2).'</td>';
		echo '<td align="center">&nbsp;</td>';
		$c++;
		$tre+=$row['recuperacion'];
		$totales[0]+=$subtotal;
		$totales[1]+=$iva;
		$totales[2]+=$row['recuperacion'];
	}
	
	echo '<tr bgcolor="#E9F2F8"><th align="left" colspan="6">Totales:</th>';
	foreach($array_engomado as $k=>$v){
		echo '<th align="center">'.number_format($v['cant'],0).'</th>';
	}
	foreach($totales as $t){
		echo '<th align="right">'.number_format($t,2).'</th>';
	}
	echo '<th>&nbsp;</th>';
	echo '</tr>';
	echo '</table><span id="depositos2"><h1>Deposito</h1><table border="1"><tr><th>Tipo</th><th>Importe</th></tr>';
	echo '<tr>';
	echo '<td align="center">P</td><td align="right">'.number_format($tp,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">C</td><td align="right">'.number_format($tc,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">TC</td><td align="right">'.number_format($ttc,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">NV</td><td align="right">'.number_format($tnv,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">PA</td><td align="right">'.number_format($tpa,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">DV</td><td align="right">-'.number_format($tdv,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">DRV</td><td align="right">-'.number_format($tdvr,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">RE</td><td align="right">'.number_format($tre,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">Total</td><td align="right">'.number_format($tnv+$tp+$tpa-$tdv-$tdvr+$tre+$ttc,2).'</td></tr>';
	echo '</table></span>';
	exit();
	
}

if($_POST['cmd']==100){
	echo '<table><tr>
	<td valign="top"><h1>Tipo de Certificado</h1>
		<table border=1><tr><th>Numero</th><th>Nombre</th><th>Precio</th></tr>';
		foreach($array_engomado as $k=>$v){
			echo '<tr>';
			echo '<td align="center">'.$k.'</td>';
			echo '<td align="left">'.$v['nombre'].'</td>';
			echo '<td align="right">'.$v['precio'].'</td>';
			echo '</tr>';
		}
		echo '</table>
	</td>
	<td>&nbsp;</td>
	<td valign="top"><h1>Tipos</h1>
		<table border="1"><tr><td align="center">E</td><td>Venta Efectivo</td></tr>
		<tr><td align="center">C</td><td>Venta Credito</td></tr>
		<tr><td align="center">TC</td><td>Tarjeta Credito</td></tr>
		<tr><td align="center">NV</td><td>No Verificado</td></tr>
		<tr><td align="center">PA</td><td>Pago Anticipado</td></tr>
		<tr><td align="center">RV</td><td>Reverificacion</td></tr>
		<tr><td align="center">CT</td><td>Cortesia</td></tr>
		<tr><td align="center">DV</td><td>Devolucion</td></tr>
		<tr><td align="center">DRV</td><td>Descuentos y Rebajas de Venta</td></tr>
		<tr><td align="center">RE</td><td>Recuperacion</td></tr>
		</table>
	</td>
	<td>&nbsp;</td>
	<td valign="top" id="depositos"></td></tr></table>';
	
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Tipo</th><th>&nbsp</th><th>Certificado</th><th>Ticket</th><th>Placa</th><th>Fecha Venta</th>';
	foreach($array_engomado as $k=>$v) echo '<th>'.$v['nombre'].'</th>';
	echo '<th>Monto</th><th>IVA</th><th>Total</th><th>Folio de Entrega</th>';
	echo '</tr>';
	
	$tc=0;
	$ttc=0;
	$tp=0;
	$tnv=0;
	$c=1;
	$totales = array(0,0,0);
	$c1=0;
	$c2=count($array_engomado)-1;
	foreach($array_engomado as $k=>$v){
		$select = "SELECT a.* FROM (
		SELECT a.placa,a.ticket,b.fecha as fechaticket,a.cve, IF(b.fecha!=a.fecha,6,b.tipo_pago) as tipo_pago, 0 as cancelado, a.engomado as engomadoentrega, 
		if(b.tipo_pago=6 OR b.fecha!=a.fecha,0,b.monto) as monto, a.certificado as certificado, IF(a.fecha!=b.fecha,1,0) as diffechas, b.motivo_intento, b.engomado as engomadoventa
		FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.fecha='".$_POST['fecha']."' AND a.estatus != 'C' AND a.engomado IN (".$v['cves'].")
		UNION ALL 
		SELECT '' as placa,'' as ticket,'' as fechaticket,'' as cve, 0 as tipo_pago, 1 as cancelado, engomado as engomadoentrega, 0 as monto, certificado, 0 as diffechas, 0 as motivo_intento, 0 as engomadoventa FROM certificados_cancelados 
		WHERE plaza='".$_POST['plazausuario']."' AND fecha='".$_POST['fecha']."' AND engomado IN (".$v['cves'].") AND estatus!='C') as a ORDER BY a.certificado";
		$select;
		$res = mysql_query($select);
		$fcertificado=-1;
		while($row = mysql_fetch_array($res)){
			if($fcertificado<0) $fcertificado = $row['certificado'];
			if($fcertificado!=$row['certificado']){
				$resOmiso = mysql_query("SELECT b.folio FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus!='C' AND a.engomado = '".$row['engomadoentrega']."' AND b.folio>='".$fcertificado."' AND b.folio<'".intval($row['certificado'])."' AND b.tipo=1 ORDER BY b.folio");
				while($rowOmiso = mysql_fetch_array($resOmiso)){
					rowb();
					echo '<td colspan="2">OMISO</td>';
					echo '<td align="center"><font color="RED">'.$rowOmiso['folio'].'</font></td>';
					echo '<td colspan="'.(7+count($array_engomado)).'">&nbsp;</td>';
					echo '</tr>';
					$fcertificado=$rowOmiso['folio']+1;
				}
			}
			rowb();
			if($row['tipo_pago']==1 && $row['monto']>0){
				$tipo='E';
			}
			elseif(($row['tipo_pago']==2) && $row['monto']>0){
				$tipo='C';
			}
			elseif(($row['tipo_pago']==5) && $row['monto']>0){
				$tipo='TC';
			}
			else{
				$tipo='';
			}
			$res2 = mysql_query("SELECT a.fecha,a.cve,a.monto FROM cobro_engomado a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket WHERE a.plaza = '".$_POST['plazausuario']."' AND a.cve>'".$row['ticket']."' AND a.placa = '".$row['placa']."' AND a.estatus!='C' AND b.estatus!='C' ORDER BY a.cve LIMIT 1");
			$row2 = mysql_fetch_array($res2);
			$res3 = mysql_query("SELECT b.fecha,a.cve,b.cve FROM cobro_engomado a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket WHERE a.plaza = '".$_POST['plazausuario']."' AND a.cve<'".$row['ticket']."' AND a.placa = '".$row['placa']."' AND a.estatus!='C' AND b.estatus!='C' AND b.engomado NOT IN (9,19) ORDER BY a.cve DESC LIMIT 1");
			$row3 = mysql_fetch_array($res3);
			$res1 = mysql_query("SELECT fecha,cve,monto FROM cobro_engomado WHERE plaza = '".$_POST['plazausuario']."' AND cve<'".$row['ticket']."' AND placa = '".$row['placa']."' AND estatus!='C' ORDER BY cve DESC LIMIT 1");
			$row1 = mysql_fetch_array($res1);
			echo '<td align="center">'.$tipo.'</td>';
			echo '<td align="center">'.$c.'</td>';
			if($fcertificado!=$row['certificado']){
				echo '<td align="center"><font color="RED">'.$row['certificado'].'</font></td>';
				$fcertificado=$row['certificado'];
			}
			else{
				echo '<td align="center">'.$row['certificado'].'</td>';
			}
			echo '<td align="center">'.$row['ticket'].'</td>';
			echo '<td align="center">'.$row['placa'].'</td>';
			if($row['monto']==0 && $row['fechaticket']>$row1['fecha']){
				if($row1['fecha']=='' && $row['tipo_pago']==6)
					echo '<td align="center"><font color="RED">DEPOSITO</font></td>';
				elseif($row1['fecha']=='')
					echo '<td align="center"><font color="RED">'.$array_motivos_intento[$row['motivo_intento']].'</font></td>';
				else
					echo '<td align="center"><font color="RED">'.$row1['fecha'].'</font></td>';
				
			}
			elseif($row['diffechas']!=1)
				echo '<td align="center">'.$row['fechaticket'].'</td>';
			else
				echo '<td align="center"><font color="RED">'.$row['fechaticket'].'</font></td>';
			for($i=0;$i<$c1;$i++) echo '<td>&nbsp;</td>';
			if($row['cancelado']==1)
				echo '<td align="center">CA</td>';
			elseif($k==$numero_rechazo)
				echo '<td align="center">NV</td>';
			elseif($row['engomadoventa']==23)
				echo '<td align="center">CT</td>';
			elseif($row['tipo_pago']==6)
				echo '<td align="center">PA</td>';
			elseif($row2['monto']==0 && $row['fechaticket']==$row2['fecha'] && $row3['fecha']!=$row['fechaticket'])
				echo '<td align="center">RV</td>';
			elseif($row['monto']==0 && $row['fechaticket']>$row1['fecha'])
				echo '<td align="center">PA</td>';
			else
				echo '<td align="center">'.$k.'</td>';
			for($i=0;$i<$c2;$i++) echo '<td>&nbsp;</td>';
			if($tipo == 'C' || $tipo == 'TC') $row['monto'] = 0;
			$iva=round($row['monto']*16/116,2);
			$subtotal=round($row['monto']-$iva,2);
			echo '<td align="right">'.number_format($subtotal,2).'</td>';
			echo '<td align="right">'.number_format($iva,2).'</td>';
			echo '<td align="right">'.number_format($row['monto'],2).'</td>';
			echo '<td align="center">'.$row['cve'].'</td>';
			$totales[0]+=$subtotal;
			$totales[1]+=$iva;
			$totales[2]+=$row['monto'];
			//if($row['diffechas']!=1)
			$array_engomado[$k]['cant']++;
			$c++;
			if($tipo=='C')
				$tc+=$row['monto'];
			elseif($tipo=='TC')
				$ttc+=$row['monto'];
			else
				$tp+=$row['monto'];
			$fcertificado++;
			echo '</tr>';
		}
		$c1++;
		$c2--;
	}
	
	$c1=0;
	$c2=count($array_engomado)-1;
	foreach($array_engomado as $k=>$v){
		$select = "SELECT a.cve,a.fecha,a.placa,  IF(a.tipo_pago=6,0,a.monto) as monto, a.tipo_pago FROM cobro_engomado a  
		LEFT JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket AND a.fecha=b.fecha AND b.estatus!='C'
		WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha='".$_POST['fecha']."' AND a.engomado IN (".$v['cves'].") AND a.estatus!='C'";
		if($PlazaLocalidad == 1) $select .= " AND a.monto > 0";
		$select .= " AND ISNULL(b.cve) ORDER BY a.cve";
		$res = mysql_query($select);
		while($row = mysql_fetch_array($res)){
			rowb();
			$tipo='NV';
			echo '<td align="center">'.$tipo.'</td>';
			echo '<td align="center">'.$c.'</td>';
			echo '<td align="center">&nbsp;</td>';
			echo '<td align="center">'.$row['cve'].'</td>';
			echo '<td align="center">'.$row['placa'].'</td>';
			echo '<td align="center">'.$row['fecha'].'</td>';
			for($i=0;$i<$c1;$i++) echo '<td>&nbsp;</td>';
			if($row['monto']==0)
				echo '<td align="center">PA</td>';
			else
				echo '<td align="center">'.$k.'</td>';
			for($i=0;$i<$c2;$i++) echo '<td>&nbsp;</td>';
			if($row['tipo_pago'] == 2 || $row['tipo_pago'] == 5) $row['monto'] = 0;	
			$iva=round($row['monto']*16/116,2);
			$subtotal=round($row['monto']-$iva,2);
			echo '<td align="right">'.number_format($subtotal,2).'</td>';
			echo '<td align="right">'.number_format($iva,2).'</td>';
			echo '<td align="right">'.number_format($row['monto'],2).'</td>';
			echo '<td align="center">'.$row['cve'].'</td>';
			$totales[0]+=$subtotal;
			$totales[1]+=$iva;
			$totales[2]+=$row['monto'];
			if($row['cancelado']!=1) $array_engomado[$k]['cant']++;
			$c++;
			$tnv+=$row['monto'];
			echo '</tr>';
		}
		$c1++;
		$c2--;
	}
	
	$tpa=0;
	$res = mysql_query("SELECT * FROM pagos_caja WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha='".$_POST['fecha']."' ORDER BY cve");
	while($row = mysql_fetch_array($res)){
		rowb();
		$tipo='PA';
		echo '<td align="center">'.$tipo.'</td>';
		echo '<td align="center">'.$c.'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		for($i=0;$i<count($array_engomado);$i++) echo '<td>&nbsp;</td>';
		if($row['forma_pago'] != 1 && $row['forma_pago'] != 3) $row['monto'] = 0;
		$iva=round($row['monto']*16/116,2);
		$iva=0;
		$subtotal=round($row['monto']-$iva,2);
		echo '<td align="right">'.number_format($subtotal,2).'</td>';
		echo '<td align="right">'.number_format($iva,2).'</td>';
		echo '<td align="right">'.number_format($row['monto'],2).'</td>';
		echo '<td align="center">&nbsp;</td>';
		$c++;
		$tpa+=$row['monto'];
		$totales[0]+=$subtotal;
		$totales[1]+=$iva;
		$totales[2]+=$row['monto'];
	}
	
	$tdv=0;
	$res = mysql_query("SELECT * FROM devolucion_certificado WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha='".$_POST['fecha']."' ORDER BY cve");
	while($row = mysql_fetch_array($res)){
		rowb();
		$tipo='DV';
		echo '<td align="center">'.$tipo.'</td>';
		echo '<td align="center">'.$c.'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['placa'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		for($i=0;$i<count($array_engomado);$i++) echo '<td>&nbsp;</td>';
		
		$iva=round($row['devolucion']*16/116,2);
		$iva=0;
		$subtotal=round($row['devolucion']-$iva,2);
		echo '<td align="right">-'.number_format($subtotal,2).'</td>';
		echo '<td align="right">'.number_format($iva,2).'</td>';
		echo '<td align="right">-'.number_format($row['devolucion'],2).'</td>';
		echo '<td align="center">&nbsp;</td>';
		$c++;
		$tdv+=$row['devolucion'];
		$totales[0]-=$subtotal;
		$totales[1]-=$iva;
		$totales[2]-=$row['devolucion'];
	}

	$res = mysql_query("SELECT * FROM devolucion_ajuste WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha='".$_POST['fecha']."' ORDER BY cve");
	while($row = mysql_fetch_array($res)){
		rowb();
		$tipo='DRV';
		echo '<td align="center">'.$tipo.'</td>';
		echo '<td align="center">'.$c.'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		for($i=0;$i<count($array_engomado);$i++) echo '<td>&nbsp;</td>';
		
		$iva=round($row['monto']*16/116,2);
		$iva=0;
		$subtotal=round($row['monto']-$iva,2);
		echo '<td align="right">-'.number_format($subtotal,2).'</td>';
		echo '<td align="right">'.number_format($iva,2).'</td>';
		echo '<td align="right">-'.number_format($row['monto'],2).'</td>';
		echo '<td align="center">&nbsp;</td>';
		$c++;
		$tdvr+=$row['monto'];
		$totales[0]-=$subtotal;
		$totales[1]-=$iva;
		$totales[2]-=$row['monto'];
	}
	
	$tre=0;
	$res = mysql_query("SELECT * FROM recuperacion_certificado WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha='".$_POST['fecha']."' ORDER BY cve");
	while($row = mysql_fetch_array($res)){
		rowb();
		$tipo='RE';
		echo '<td align="center">'.$tipo.'</td>';
		echo '<td align="center">'.$c.'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['placa'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		for($i=0;$i<count($array_engomado);$i++) echo '<td>&nbsp;</td>';
		
		$iva=round($row['recuperacion']*16/116,2);
		$iva=0;
		$subtotal=round($row['recuperacion']-$iva,2);
		echo '<td align="right">'.number_format($subtotal,2).'</td>';
		echo '<td align="right">'.number_format($iva,2).'</td>';
		echo '<td align="right">'.number_format($row['recuperacion'],2).'</td>';
		echo '<td align="center">&nbsp;</td>';
		$c++;
		$tre+=$row['recuperacion'];
		$totales[0]+=$subtotal;
		$totales[1]+=$iva;
		$totales[2]+=$row['recuperacion'];
	}
	
	echo '<tr bgcolor="#E9F2F8"><th align="left" colspan="6">Totales:</th>';
	foreach($array_engomado as $k=>$v){
		echo '<th align="center">'.number_format($v['cant'],0).'</th>';
	}
	foreach($totales as $t){
		echo '<th align="right">'.number_format($t,2).'</th>';
	}
	echo '<th>&nbsp;</th>';
	echo '</tr>';
	echo '</table><span id="depositos2"><h1>Deposito</h1><table border="1"><tr><th>Tipo</th><th>Importe</th></tr>';
	echo '<tr>';
	echo '<td align="center">P</td><td align="right">'.number_format($tp,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">C</td><td align="right">'.number_format($tc,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">TC</td><td align="right">'.number_format($ttc,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">NV</td><td align="right">'.number_format($tnv,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">PA</td><td align="right">'.number_format($tpa,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">DV</td><td align="right">-'.number_format($tdv,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">DRV</td><td align="right">-'.number_format($tdvr,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">RE</td><td align="right">'.number_format($tre,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">Total</td><td align="right">'.number_format($tnv+$tp+$tpa-$tdv-$tdvr+$tre+$ttc,2).'</td></tr>';
	echo '</table></span>';
	echo '<script>
		document.getElementById("depositos").innerHTML = document.getElementById("depositos2").innerHTML;
		document.getElementById("depositos2").innerHTML="";
		window.print();
		</script>';
	exit();
	
}

if($_POST['ajax']==1){
	echo '<table><tr>
	<td valign="top"><h1>Tipo de Certificado</h1>
		<table border=1><tr><th>Numero</th><th>Nombre</th><th>Precio</th></tr>';
		foreach($array_engomado as $k=>$v){
			echo '<tr>';
			echo '<td align="center">'.$k.'</td>';
			echo '<td align="left">'.$v['nombre'].'</td>';
			echo '<td align="right">'.$v['precio'].'</td>';
			echo '</tr>';
		}
		echo '</table>
	</td>
	<td>&nbsp;</td>
	<td valign="top"><h1>Tipos</h1>
		<table border="1"><tr><td align="center">E</td><td>Venta Efectivo</td></tr>
		<tr><td align="center">C</td><td>Venta Credito</td></tr>
		<tr><td align="center">TC</td><td>Tarjeta Credito</td></tr>
		<tr><td align="center">NV</td><td>No Verificado</td></tr>
		<tr><td align="center">PA</td><td>Pago Anticipado</td></tr>
		<tr><td align="center">RV</td><td>Reverificacion</td></tr>
		<tr><td align="center">CT</td><td>Cortesia</td></tr>
		<tr><td align="center">DV</td><td>Devolucion</td></tr>
		<tr><td align="center">DRV</td><td>Descuentos y Rebajas de Venta</td></tr>
		<tr><td align="center">RE</td><td>Recuperacion</td></tr>
		</table>
	</td>
	<td>&nbsp;</td>
	<td valign="top" id="depositos"></td></tr></table>';
	
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Tipo</th><th>&nbsp</th><th>Certificado</th><th>Ticket</th><th>Placa</th><th>Fecha Venta</th>';
	foreach($array_engomado as $k=>$v) echo '<th>'.$v['nombre'].'</th>';
	echo '<th>Monto</th><th>IVA</th><th>Total</th><th>Folio de Entrega</th>';
	echo '</tr>';
	
	$tc=0;
	$ttc=0;
	$tp=0;
	$tnv=0;
	$c=1;
	$totales = array(0,0,0);
	$c1=0;
	$c2=count($array_engomado)-1;
	foreach($array_engomado as $k=>$v){
		$select = "SELECT a.* FROM (
		SELECT a.placa,a.ticket,b.fecha as fechaticket,a.cve, IF(b.fecha!=a.fecha,6,b.tipo_pago) as tipo_pago, 0 as cancelado, a.engomado as engomadoentrega, 
		if(b.tipo_pago=6 OR b.fecha!=a.fecha,0,b.monto) as monto, a.certificado as certificado, IF(a.fecha!=b.fecha,1,0) as diffechas, b.motivo_intento, b.engomado as engomadoventa
		FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.fecha='".$_POST['fecha']."' AND a.estatus != 'C' AND a.engomado IN (".$v['cves'].")
		UNION ALL 
		SELECT '' as placa,'' as ticket,'' as fechaticket,'' as cve, 0 as tipo_pago, 1 as cancelado, engomado as engomadoentrega, 0 as monto, certificado, 0 as diffechas, 0 as motivo_intento, 0 as engomadoventa FROM certificados_cancelados 
		WHERE plaza='".$_POST['plazausuario']."' AND fecha='".$_POST['fecha']."' AND engomado IN (".$v['cves'].") AND estatus!='C') as a ORDER BY a.certificado";
		$select;
		$res = mysql_query($select);
		$fcertificado=-1;
		while($row = mysql_fetch_array($res)){
			if($fcertificado<0) $fcertificado = $row['certificado'];
			if($fcertificado!=$row['certificado']){
				$resOmiso = mysql_query("SELECT b.folio FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus!='C' AND a.engomado = '".$row['engomadoentrega']."' AND b.folio>='".$fcertificado."' AND b.folio<'".intval($row['certificado'])."' AND b.tipo=1 ORDER BY b.folio");
				while($rowOmiso = mysql_fetch_array($resOmiso)){
					rowb();
					echo '<td colspan="2">OMISO</td>';
					echo '<td align="center"><font color="RED">'.$rowOmiso['folio'].'</font></td>';
					echo '<td colspan="'.(7+count($array_engomado)).'">&nbsp;</td>';
					echo '</tr>';
					$fcertificado=$rowOmiso['folio']+1;
				}
			}
			rowb();
			if($row['tipo_pago']==1 && $row['monto']>0){
				$tipo='E';
			}
			elseif(($row['tipo_pago']==2) && $row['monto']>0){
				$tipo='C';
			}
			elseif(($row['tipo_pago']==5) && $row['monto']>0){
				$tipo='TC';
			}
			else{
				$tipo='';
			}
			$res2 = mysql_query("SELECT a.fecha,a.cve,a.monto FROM cobro_engomado a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket WHERE a.plaza = '".$_POST['plazausuario']."' AND a.cve>'".$row['ticket']."' AND a.placa = '".$row['placa']."' AND a.estatus!='C' AND b.estatus!='C' ORDER BY a.cve LIMIT 1");
			$row2 = mysql_fetch_array($res2);
			$res3 = mysql_query("SELECT b.fecha,a.cve,b.cve FROM cobro_engomado a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket WHERE a.plaza = '".$_POST['plazausuario']."' AND a.cve<'".$row['ticket']."' AND a.placa = '".$row['placa']."' AND a.estatus!='C' AND b.estatus!='C' AND b.engomado NOT IN (9,19) ORDER BY a.cve DESC LIMIT 1");
			$row3 = mysql_fetch_array($res3);
			$res1 = mysql_query("SELECT fecha,cve,monto FROM cobro_engomado WHERE plaza = '".$_POST['plazausuario']."' AND cve<'".$row['ticket']."' AND placa = '".$row['placa']."' AND estatus!='C' ORDER BY cve DESC LIMIT 1");
			$row1 = mysql_fetch_array($res1);
			echo '<td align="center">'.$tipo.'</td>';
			echo '<td align="center">'.$c.'</td>';
			if($fcertificado!=$row['certificado']){
				echo '<td align="center"><font color="RED">'.$row['certificado'].'</font></td>';
				$fcertificado=$row['certificado'];
			}
			else{
				echo '<td align="center">'.$row['certificado'].'</td>';
			}
			echo '<td align="center">'.$row['ticket'].'</td>';
			echo '<td align="center">'.$row['placa'].'</td>';
			if($row['monto']==0 && $row['fechaticket']>$row1['fecha']){
				if($row1['fecha']=='' && $row['tipo_pago']==6)
					echo '<td align="center"><font color="RED">DEPOSITO</font></td>';
				elseif($row1['fecha']=='')
					echo '<td align="center"><font color="RED">'.$array_motivos_intento[$row['motivo_intento']].'</font></td>';
				else
					echo '<td align="center"><font color="RED">'.$row1['fecha'].'</font></td>';
				
			}
			elseif($row['diffechas']!=1)
				echo '<td align="center">'.$row['fechaticket'].'</td>';
			else
				echo '<td align="center"><font color="RED">'.$row['fechaticket'].'</font></td>';
			for($i=0;$i<$c1;$i++) echo '<td>&nbsp;</td>';
			if($row['cancelado']==1)
				echo '<td align="center">CA</td>';
			elseif($k==$numero_rechazo)
				echo '<td align="center">NV</td>';
			elseif($row['engomadoventa']==23)
				echo '<td align="center">CT</td>';
			elseif($row['tipo_pago']==6)
				echo '<td align="center">PA</td>';
			elseif($row2['monto']==0 && $row['fechaticket']==$row2['fecha'] && $row3['fecha']!=$row['fechaticket'])
				echo '<td align="center">RV</td>';
			elseif($row['monto']==0 && $row['fechaticket']>$row1['fecha'])
				echo '<td align="center">PA</td>';
			else
				echo '<td align="center">'.$k.'</td>';
			for($i=0;$i<$c2;$i++) echo '<td>&nbsp;</td>';
			if($tipo == 'C' || $tipo == 'TC') $row['monto'] = 0;
			$iva=round($row['monto']*16/116,2);
			$subtotal=round($row['monto']-$iva,2);
			echo '<td align="right">'.number_format($subtotal,2).'</td>';
			echo '<td align="right">'.number_format($iva,2).'</td>';
			echo '<td align="right">'.number_format($row['monto'],2).'</td>';
			echo '<td align="center">'.$row['cve'].'</td>';
			$totales[0]+=$subtotal;
			$totales[1]+=$iva;
			$totales[2]+=$row['monto'];
			//if($row['diffechas']!=1)
			$array_engomado[$k]['cant']++;
			$c++;
			if($tipo=='C')
				$tc+=$row['monto'];
			elseif($tipo=='TC')
				$ttc+=$row['monto'];
			else
				$tp+=$row['monto'];
			$fcertificado++;
			echo '</tr>';
		}
		$c1++;
		$c2--;
	}
	
	$c1=0;
	$c2=count($array_engomado)-1;
	foreach($array_engomado as $k=>$v){
		$select = "SELECT a.cve,a.fecha,a.placa,  IF(a.tipo_pago=6,0,a.monto) as monto, a.tipo_pago FROM cobro_engomado a  
		LEFT JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket AND a.fecha=b.fecha AND b.estatus!='C'
		WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha='".$_POST['fecha']."' AND a.engomado IN (".$v['cves'].") AND a.estatus!='C'";
		if($PlazaLocalidad == 1) $select .= " AND a.monto > 0";
		$select .= " AND ISNULL(b.cve) ORDER BY a.cve";
		$res = mysql_query($select);
		while($row = mysql_fetch_array($res)){
			rowb();
			$tipo='NV';
			echo '<td align="center">'.$tipo.'</td>';
			echo '<td align="center">'.$c.'</td>';
			echo '<td align="center">&nbsp;</td>';
			echo '<td align="center">'.$row['cve'].'</td>';
			echo '<td align="center">'.$row['placa'].'</td>';
			echo '<td align="center">'.$row['fecha'].'</td>';
			for($i=0;$i<$c1;$i++) echo '<td>&nbsp;</td>';
			if($row['monto']==0)
				echo '<td align="center">PA</td>';
			else
				echo '<td align="center">'.$k.'</td>';
			for($i=0;$i<$c2;$i++) echo '<td>&nbsp;</td>';
			if($row['tipo_pago'] == 2 || $row['tipo_pago'] == 5) $row['monto'] = 0;	
			$iva=round($row['monto']*16/116,2);
			$subtotal=round($row['monto']-$iva,2);
			echo '<td align="right">'.number_format($subtotal,2).'</td>';
			echo '<td align="right">'.number_format($iva,2).'</td>';
			echo '<td align="right">'.number_format($row['monto'],2).'</td>';
			echo '<td align="center">'.$row['cve'].'</td>';
			$totales[0]+=$subtotal;
			$totales[1]+=$iva;
			$totales[2]+=$row['monto'];
			if($row['cancelado']!=1) $array_engomado[$k]['cant']++;
			$c++;
			$tnv+=$row['monto'];
			echo '</tr>';
		}
		$c1++;
		$c2--;
	}
	
	$tpa=0;
	$res = mysql_query("SELECT * FROM pagos_caja WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha='".$_POST['fecha']."' ORDER BY cve");
	while($row = mysql_fetch_array($res)){
		rowb();
		$tipo='PA';
		echo '<td align="center">'.$tipo.'</td>';
		echo '<td align="center">'.$c.'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		for($i=0;$i<count($array_engomado);$i++) echo '<td>&nbsp;</td>';
		if($row['forma_pago'] != 1 && $row['forma_pago'] != 3) $row['monto'] = 0;
		$iva=round($row['monto']*16/116,2);
		$iva=0;
		$subtotal=round($row['monto']-$iva,2);
		echo '<td align="right">'.number_format($subtotal,2).'</td>';
		echo '<td align="right">'.number_format($iva,2).'</td>';
		echo '<td align="right">'.number_format($row['monto'],2).'</td>';
		echo '<td align="center">&nbsp;</td>';
		$c++;
		$tpa+=$row['monto'];
		$totales[0]+=$subtotal;
		$totales[1]+=$iva;
		$totales[2]+=$row['monto'];
	}
	
	$tdv=0;
	$res = mysql_query("SELECT * FROM devolucion_certificado WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha='".$_POST['fecha']."' ORDER BY cve");
	while($row = mysql_fetch_array($res)){
		rowb();
		$tipo='DV';
		echo '<td align="center">'.$tipo.'</td>';
		echo '<td align="center">'.$c.'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['placa'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		for($i=0;$i<count($array_engomado);$i++) echo '<td>&nbsp;</td>';
		
		$iva=round($row['devolucion']*16/116,2);
		$iva=0;
		$subtotal=round($row['devolucion']-$iva,2);
		echo '<td align="right">-'.number_format($subtotal,2).'</td>';
		echo '<td align="right">'.number_format($iva,2).'</td>';
		echo '<td align="right">-'.number_format($row['devolucion'],2).'</td>';
		echo '<td align="center">&nbsp;</td>';
		$c++;
		$tdv+=$row['devolucion'];
		$totales[0]-=$subtotal;
		$totales[1]-=$iva;
		$totales[2]-=$row['devolucion'];
	}

	$res = mysql_query("SELECT * FROM devolucion_ajuste WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha='".$_POST['fecha']."' ORDER BY cve");
	while($row = mysql_fetch_array($res)){
		rowb();
		$tipo='DRV';
		echo '<td align="center">'.$tipo.'</td>';
		echo '<td align="center">'.$c.'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		for($i=0;$i<count($array_engomado);$i++) echo '<td>&nbsp;</td>';
		
		$iva=round($row['monto']*16/116,2);
		$iva=0;
		$subtotal=round($row['monto']-$iva,2);
		echo '<td align="right">-'.number_format($subtotal,2).'</td>';
		echo '<td align="right">'.number_format($iva,2).'</td>';
		echo '<td align="right">-'.number_format($row['monto'],2).'</td>';
		echo '<td align="center">&nbsp;</td>';
		$c++;
		$tdvr+=$row['monto'];
		$totales[0]-=$subtotal;
		$totales[1]-=$iva;
		$totales[2]-=$row['monto'];
	}
	
	$tre=0;
	$res = mysql_query("SELECT * FROM recuperacion_certificado WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha='".$_POST['fecha']."' ORDER BY cve");
	while($row = mysql_fetch_array($res)){
		rowb();
		$tipo='RE';
		echo '<td align="center">'.$tipo.'</td>';
		echo '<td align="center">'.$c.'</td>';
		echo '<td align="center">&nbsp;</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['placa'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		for($i=0;$i<count($array_engomado);$i++) echo '<td>&nbsp;</td>';
		
		$iva=round($row['recuperacion']*16/116,2);
		$iva=0;
		$subtotal=round($row['recuperacion']-$iva,2);
		echo '<td align="right">'.number_format($subtotal,2).'</td>';
		echo '<td align="right">'.number_format($iva,2).'</td>';
		echo '<td align="right">'.number_format($row['recuperacion'],2).'</td>';
		echo '<td align="center">&nbsp;</td>';
		$c++;
		$tre+=$row['recuperacion'];
		$totales[0]+=$subtotal;
		$totales[1]+=$iva;
		$totales[2]+=$row['recuperacion'];
	}
	
	echo '<tr bgcolor="#E9F2F8"><th align="left" colspan="6">Totales:</th>';
	foreach($array_engomado as $k=>$v){
		echo '<th align="center">'.number_format($v['cant'],0).'</th>';
	}
	foreach($totales as $t){
		echo '<th align="right">'.number_format($t,2).'</th>';
	}
	echo '<th>&nbsp;</th>';
	echo '</tr>';
	echo '</table><span id="depositos2"><h1>Deposito</h1><table border="1"><tr><th>Tipo</th><th>Importe</th></tr>';
	echo '<tr>';
	echo '<td align="center">P</td><td align="right">'.number_format($tp,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">C</td><td align="right">'.number_format($tc,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">TC</td><td align="right">'.number_format($ttc,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">NV</td><td align="right">'.number_format($tnv,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">PA</td><td align="right">'.number_format($tpa,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">DV</td><td align="right">-'.number_format($tdv,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">DRV</td><td align="right">-'.number_format($tdvr,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">RE</td><td align="right">'.number_format($tre,2).'</td></tr>';
	echo '<tr>';
	echo '<td align="center">Total</td><td align="right">'.number_format($tnv+$tp+$tpa-$tdv-$tdvr+$tre+$ttc,2).'</td></tr>';
	echo '</table></span>';
	exit();
	
}

top($_SESSION);

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onclick="atcr(\'poliza_certificado.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir</td><td>&nbsp;</td>
			<td><a href="#" onclick="atcr(\'poliza_certificado.php\',\'_blank\',101,0);"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Excel</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha</td><td><input type="text" name="fecha" id="fecha" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
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
			objeto.open("POST","poliza_certificado.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha="+document.getElementById("fecha").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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