<?php
include ("main.php"); 

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsPuestos=mysql_query("SELECT * FROM puestos ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_puestos[$Puestos['cve']]=$Puestos['nombre'];
	$array_riesgopuestos[$Puestos['cve']]=$Puestos['riesgo'];
}

$array_departamento = array();

$rsPuestos=mysql_query("SELECT * FROM departamentos  ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_departamento[$Puestos['cve']]=$Puestos['nombre'];
}

$array_plazanombre = array();

$rsPuestos=mysql_query("SELECT * FROM datosempresas ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_plazanombre[$Puestos['plaza']]=$Puestos['nombre'];
	$array_plazadomicilio[$Puestos['plaza']]=$Puestos['domicilio'];
	$array_plaza_regimen[$Puestos['plaza']]=$Puestos['registro_patronal'];
}

$_POST['cveempresa']=1;
$tipo_nomina = 3;
$lusa = $Empresa['lusa'];

if($_POST['cmd']==102){
	header('Content-type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=cc.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
	
	$select= " SELECT c.nombre, c.fecha_ini, c.fecha_fin, b.uuid, b.fechatimbre, SUM(b.totalpercepciones) as totalpercepciones,SUM(b.totaldeducciones) as totaldeducciones, GROUP_CONCAT(b.cve) as nominas
		FROM personal as a 
		INNER JOIN personal_nomina as b ON (b.personal=a.cve AND b.tipo='1' AND b.eliminada!=1)
		INNER JOIN periodos_nomina as c on c.cve = b.periodo
		WHERE a.cve='".$_POST['reg']."' AND c.fecha_fin BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	$select.=" AND IFNULL(b.uuid,'')!=''";
	$select .= " GROUP BY b.cve ORDER BY a.nombre";
	$rspersonal=mysql_query($select) or die(mysql_error());
	$totalRegistros = mysql_num_rows($rspersonal);
	//print_r($_POST);
	if(mysql_num_rows($rspersonal)>0) 
	{
		echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="" id="tabla1">';
		echo '<tr bgcolor="#E9F2F8">';
		
		echo '
		<th rowspan="2">Periodo</th><th rowspan="2">Fecha Inicial</th><th rowspan="2">Fecha Final</th>
		<th rowspan="2">UUID</th><th rowspan="2">Fecha Timbre</th>';
		$res1=mysql_query("SELECT * FROM cat_percepciones WHERE empresa IN (0,".$_POST['cveempresa'].") AND tipo_nomina=1 ORDER BY cve");
		$cpercepciones = mysql_num_rows($res1)+1;
		$res2=mysql_query("SELECT * FROM cat_deducciones WHERE empresa IN (0,".$_POST['cveempresa'].") AND tipo_nomina=1 ORDER BY cve");
		$cdeducciones = mysql_num_rows($res2)+1;
		echo '<th colspan="'.$cpercepciones.'">Percepciones</th><th colspan="'.$cdeducciones.'">Deducciones</th><th rowspan="2">Total a Pagar</th></tr><tr bgcolor="#E9F2F8">';
		
		while($row1=mysql_fetch_array($res1)){
			echo '<th>'.$row1['nombre'].'</th>';
		}
		echo '<th>Total Percepciones</th>';
		
		
		while($row2=mysql_fetch_array($res2)){
			echo '<th>'.$row2['nombre'].'</th>';
		}
		echo '<th>Total Deducciones</th>';

		echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
		$gtotal=0;
		$timp=$tper=$tded=0;
		$i=0;
		$array_totales = array();
		while($Personal=mysql_fetch_array($rspersonal)) {
			rowb();
			$total_percepciones = $Personal['totalpercepciones'];
			$total_deducciones = $Personal['totaldeducciones'];
			echo '<td>'.$Personal['nombre'].'</td>';
			echo '<td align="center">'.$Personal['fecha_ini'].'</td>';
			echo '<td align="center">'.$Personal['fecha_fin'].'</td>';
			echo '<td align="center">'.$Personal['uuid'].'</td>';
			echo '<td align="center">'.$Personal['fechatimbre'].'</td>';
			$indice_total=0;
			$res1=mysql_query("SELECT a.cve, SUM(b.total) as total FROM cat_percepciones a LEFT JOIN personal_nomina_percepcion b ON a.cve = b.percepcion AND b.nomina IN (".$Personal['nominas'].") WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=1 GROUP BY a.cve ORDER BY a.cve");
			while($row1=mysql_fetch_array($res1)){
				echo '<td align="right">'.number_format($row1['total'],2).'</td>';
				$array_totales[$indice_total]+=round($row1['total'],2);$indice_total++;
			}
			echo '<td align="right">'.number_format($total_percepciones,2).'</td>';
			$array_totales[$indice_total]+=round($total_percepciones,2);$indice_total++;
			$res1=mysql_query("SELECT a.cve, SUM(b.total) as total FROM cat_deducciones a LEFT JOIN personal_nomina_deduccion b ON a.cve = b.deduccion AND b.nomina IN (".$Personal['nominas'].") WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=1 GROUP BY a.cve ORDER BY a.cve");
			while($row1=mysql_fetch_array($res1)){
				echo '<td align="right">'.number_format($row1['total'],2).'</td>';
				$array_totales[$indice_total]+=round($row1['total'],2);$indice_total++;
			}
			echo '<td align="right">'.number_format($total_deducciones,2).'</td>';
			$array_totales[$indice_total]+=round($total_deducciones,2);$indice_total++;
			echo '<td align="right">'.number_format($total_percepciones-$total_deducciones,2).'</td>';
			$array_totales[$indice_total]+=round($total_percepciones-$total_deducciones,2);$indice_total++;
			echo '</tr>';
			$i++;
			$tper+=$total_percepciones;
			$tded+=$total_deducciones;
			$gtotal+=($total_percepciones-$total_deducciones);
		}
		$col=4;
		/*if($nominagen!=0){
			$col++;
		}*/
		echo '</tbody>
			<tr bgcolor="#E9F2F8"><td colspan="'.$col.'">'.$i.' Registro(s)</td><td>Total</td>';
			/*<td align="right">'.number_format($tper,2).'</td>
			<td align="right">'.number_format($tded,2).'</td>
			<td align="right">'.number_format($gtotal,2).'</td>';*/
		foreach($array_totales as $v) echo '<td align="right">'.number_format($v,2).'</td>';
		echo '</tr>
		</table>';
		
	} else {
		echo '
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
		</tr>	  
		</table>';
	}	
	exit();
	
	}
if($_POST['cmd']==101){
	header('Content-type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=cc.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
	
	$select= " SELECT a.cve,a.nombre,a.plaza,a.nombre,a.puesto,a.rfc,a.curp,a.imss,a.fecha_ini as fecha_ingreso,SUM(b.totalpercepciones) as totalpercepciones,SUM(b.totaldeducciones) as totaldeducciones, GROUP_CONCAT(b.cve) as nominas
		FROM personal as a 
		INNER JOIN personal_nomina as b ON (b.personal=a.cve AND b.tipo='1' AND b.eliminada!=1)
		INNER JOIN periodos_nomina as c on c.cve = b.periodo
		WHERE c.fecha_fin BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	
	if($_POST['plaza']>0) $select.=" AND IF(IFNULL(b.uuid,'')!='',b.plazatimbro,a.plaza)='".$_POST['plaza']."'";
	if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
	$select.=" AND IFNULL(b.uuid,'')!=''";
	$select .= " GROUP BY a.cve ORDER BY a.nombre";
	$rspersonal=mysql_query($select) or die(mysql_error());
	$totalRegistros = mysql_num_rows($rspersonal);
	if(mysql_num_rows($rspersonal)>0) 
	{
		echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="" id="tabla1">';
		echo '<tr bgcolor="#E9F2F8">';
		
		if(count($array_plaza)>1) echo '<th rowspan="2">Plaza</th>';
		//<a href="#" onclick="SortTable(2,\'S\',\'tabla1\');">Nombre</a>
		echo '
		<th rowspan="2">Nombre</th><th rowspan="2">Puesto</th><th rowspan="2">R.F.C.</th><th rowspan="2">CURP</th><th rowspan="2">N.S.S.</th>';
		$res1=mysql_query("SELECT * FROM cat_percepciones WHERE empresa IN (0,".$_POST['cveempresa'].") AND tipo_nomina=1 ORDER BY cve");
		$cpercepciones = mysql_num_rows($res1)+1;
		$res2=mysql_query("SELECT * FROM cat_deducciones WHERE empresa IN (0,".$_POST['cveempresa'].") AND tipo_nomina=1 ORDER BY cve");
		$cdeducciones = mysql_num_rows($res2)+1;
		echo '<th colspan="'.$cpercepciones.'">Percepciones</th><th colspan="'.$cdeducciones.'">Deducciones</th><th rowspan="2">Total a Pagar</th>
		<th rowspan="2">Fecha Alta</th><th rowspan="2">Fecha Baja</th>
		</tr><tr bgcolor="#E9F2F8">';
		
		while($row1=mysql_fetch_array($res1)){
			echo '<th>'.$row1['nombre'].'</th>';
		}
		echo '<th>Total Percepciones</th>';
		
		
		while($row2=mysql_fetch_array($res2)){
			echo '<th>'.$row2['nombre'].'</th>';
		}
		echo '<th>Total Deducciones</th>';

		echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
		$gtotal=0;
		$timp=$tper=$tded=0;
		$i=0;
		$array_totales = array();
		while($Personal=mysql_fetch_array($rspersonal)) {
			$array_fecha_estatus = array();
			$res1=mysql_query("SELECT valor_nuevo, MAX(fecha) FROM cambios_datos_personal WHERE cve_personal='".$Personal['cve']."' AND dato='Estatus' GROUP BY valor_nuevo");
			while($row1=mysql_fetch_array($res1)) $array_fecha_estatus[$row1[0]] = $row1[1];
			if($array_fecha_estatus[1]<='0000-00-00') $array_fecha_estatus[1] = $Personal['fecha_ingreso'];
			rowb();
			$total_percepciones = $Personal['totalpercepciones'];
			$total_deducciones = $Personal['totaldeducciones'];
			if(count($array_plaza)>1) echo '<td>'.$array_plaza[$Personal['plaza']].'</td>';
			//echo '<td><a href="#" onClick="atcr(\'rep_personal_nomina.php\',\'\',1,'.$Personal['cve'].')">'.$Personal["nombre"].'</a></td>';
			echo '<td>'.$Personal["nombre"].'</td>';
			echo '<td>'.$array_puestos[$Personal['puesto']].'</td>';
			echo '<td align="center">'.$Personal['rfc'].$Personal['homoclave'].'</td>';
			echo '<td align="center">'.$Personal['curp'].'</td>';
			echo '<td align="center">'.$Personal['imss'].'</td>';
			$indice_total=0;
			$res1=mysql_query("SELECT a.cve, SUM(b.total) as total FROM cat_percepciones a LEFT JOIN personal_nomina_percepcion b ON a.cve = b.percepcion AND b.nomina IN (".$Personal['nominas'].") WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=1 GROUP BY a.cve ORDER BY a.cve");
			while($row1=mysql_fetch_array($res1)){
				echo '<td align="right">'.number_format($row1['total'],2).'</td>';
				$array_totales[$indice_total]+=round($row1['total'],2);$indice_total++;
			}
			echo '<td align="right">'.number_format($total_percepciones,2).'</td>';
			$array_totales[$indice_total]+=round($total_percepciones,2);$indice_total++;
			$res1=mysql_query("SELECT a.cve, SUM(b.total) as total FROM cat_deducciones a LEFT JOIN personal_nomina_deduccion b ON a.cve = b.deduccion AND b.nomina IN (".$Personal['nominas'].") WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=1 GROUP BY a.cve ORDER BY a.cve");
			while($row1=mysql_fetch_array($res1)){
				echo '<td align="right">'.number_format($row1['total'],2).'</td>';
				$array_totales[$indice_total]+=round($row1['total'],2);$indice_total++;
			}
			echo '<td align="right">'.number_format($total_deducciones,2).'</td>';
			$array_totales[$indice_total]+=round($total_deducciones,2);$indice_total++;
			echo '<td align="right">'.number_format($total_percepciones-$total_deducciones,2).'</td>';
			$array_totales[$indice_total]+=round($total_percepciones-$total_deducciones,2);$indice_total++;
			echo '<td align="center">'.$array_fecha_estatus[1].'</td>';
			echo '<td align="center">'.$array_fecha_estatus[2].'</td>';
			echo '</tr>';
			$i++;
			$tper+=$total_percepciones;
			$tded+=$total_deducciones;
			$gtotal+=($total_percepciones-$total_deducciones);
		}
		$col=4;
		/*if($nominagen!=0){
			$col++;
		}*/
		if(count($array_plaza)>1) $col++;
		echo '</tbody>
			<tr bgcolor="#E9F2F8"><td colspan="'.$col.'">'.$i.' Registro(s)</td><td>Total</td>';
			/*<td align="right">'.number_format($tper,2).'</td>
			<td align="right">'.number_format($tded,2).'</td>
			<td align="right">'.number_format($gtotal,2).'</td>';*/
		foreach($array_totales as $v) echo '<td align="right">'.number_format($v,2).'</td>';
		echo '</tr>
		</table>';
		
	} else {
		echo '
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
		</tr>	  
		</table>';
	}	
	exit();
	
	
	}
if($_POST['ajax']==1){
	$select= " SELECT a.cve,a.nombre,a.plaza,a.nombre,a.puesto,a.rfc,a.imss,a.curp,c.fecha_ini as fecha_ingreso,SUM(b.totalpercepciones) as totalpercepciones,SUM(b.totaldeducciones) as totaldeducciones, GROUP_CONCAT(b.cve) as nominas
		FROM personal as a 
		INNER JOIN personal_nomina as b ON (b.personal=a.cve AND b.tipo='1' AND b.eliminada!=1)
		INNER JOIN periodos_nomina as c on c.cve = b.periodo
		WHERE c.fecha_fin BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	
	if($_POST['plaza']>0) $select.=" AND IF(IFNULL(b.uuid,'')!='',b.plazatimbro,a.plaza)='".$_POST['plaza']."'";
	if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
	$select.=" AND IFNULL(b.uuid,'')!=''";
	$select .= " GROUP BY a.cve ORDER BY a.nombre";
	$rspersonal=mysql_query($select) or die(mysql_error());
	$totalRegistros = mysql_num_rows($rspersonal);
	if(mysql_num_rows($rspersonal)>0) 
	{
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
		echo '<tr bgcolor="#E9F2F8">';
		
		if(count($array_plaza)>1) echo '<th rowspan="2">Plaza</th>';
		echo '
		<th rowspan="2"><a href="#" onclick="SortTable(2,\'S\',\'tabla1\');">Nombre</a></th><th rowspan="2">Puesto</th><th rowspan="2">R.F.C.</th><th rowspan="2">N.S.S.</th>';
		$res1=mysql_query("SELECT * FROM cat_percepciones WHERE empresa IN (0,".$_POST['cveempresa'].") AND tipo_nomina=1 ORDER BY cve");
		$cpercepciones = mysql_num_rows($res1)+1;
		$res2=mysql_query("SELECT * FROM cat_deducciones WHERE empresa IN (0,".$_POST['cveempresa'].") AND tipo_nomina=1 ORDER BY cve");
		$cdeducciones = mysql_num_rows($res2)+1;
		echo '<th colspan="'.$cpercepciones.'">Percepciones</th><th colspan="'.$cdeducciones.'">Deducciones</th><th rowspan="2">Total a Pagar</th><th rowspan="2">Fecha Alta</th><th rowspan="2">Fecha Baja</th></tr><tr bgcolor="#E9F2F8">';
		
		while($row1=mysql_fetch_array($res1)){
			echo '<th>'.$row1['nombre'].'</th>';
		}
		echo '<th>Total Percepciones</th>';
		
		
		while($row2=mysql_fetch_array($res2)){
			echo '<th>'.$row2['nombre'].'</th>';
		}
		echo '<th>Total Deducciones</th>';

		echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
		$gtotal=0;
		$timp=$tper=$tded=0;
		$i=0;
		$array_totales = array();
		while($Personal=mysql_fetch_array($rspersonal)) {
			$array_fecha_estatus = array();
			$res1=mysql_query("SELECT valor_nuevo, MAX(fecha) FROM cambios_datos_personal WHERE cve_personal='".$Personal['cve']."' AND dato='Estatus' GROUP BY valor_nuevo");
			while($row1=mysql_fetch_array($res1)) $array_fecha_estatus[$row1[0]] = $row1[1];
			if($array_fecha_estatus[1]<='0000-00-00') $array_fecha_estatus[1] = $Personal['fecha_ingreso'];
			rowb();
			$total_percepciones = $Personal['totalpercepciones'];
			$total_deducciones = $Personal['totaldeducciones'];
			if(count($array_plaza)>1) echo '<td>'.$array_plaza[$Personal['plaza']].'</td>';
			//echo '<td><a href="#" onClick="atcr(\'rep_personal_nomina.php\',\'\',1,'.$Personal['cve'].')">'.$Personal["nombre"].'</a></td>';
			echo '<td><a href="#" onClick="atcr(\'rep_personal_nomina.php\',\'\',1,'.$Personal['cve'].')">'.$Personal["nombre"].'</a></td>';
			echo '<td>'.$array_puestos[$Personal['puesto']].'</td>';
			echo '<td align="center">'.$Personal['rfc'].$Personal['homoclave'].'</td>';
			echo '<td align="center">'.$Personal['curp'].'</td>';
			echo '<td align="center">'.$Personal['imss'].'</td>';
			$indice_total=0;
			$res1=mysql_query("SELECT a.cve, SUM(b.total) as total FROM cat_percepciones a LEFT JOIN personal_nomina_percepcion b ON a.cve = b.percepcion AND b.nomina IN (".$Personal['nominas'].") WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=1 GROUP BY a.cve ORDER BY a.cve");
			while($row1=mysql_fetch_array($res1)){
				echo '<td align="right">'.number_format($row1['total'],2).'</td>';
				$array_totales[$indice_total]+=round($row1['total'],2);$indice_total++;
			}
			echo '<td align="right">'.number_format($total_percepciones,2).'</td>';
			$array_totales[$indice_total]+=round($total_percepciones,2);$indice_total++;
			$res1=mysql_query("SELECT a.cve, SUM(b.total) as total FROM cat_deducciones a LEFT JOIN personal_nomina_deduccion b ON a.cve = b.deduccion AND b.nomina IN (".$Personal['nominas'].") WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=1 GROUP BY a.cve ORDER BY a.cve");
			while($row1=mysql_fetch_array($res1)){
				echo '<td align="right">'.number_format($row1['total'],2).'</td>';
				$array_totales[$indice_total]+=round($row1['total'],2);$indice_total++;
			}
			echo '<td align="right">'.number_format($total_deducciones,2).'</td>';
			$array_totales[$indice_total]+=round($total_deducciones,2);$indice_total++;
			echo '<td align="right">'.number_format($total_percepciones-$total_deducciones,2).'</td>';
			$array_totales[$indice_total]+=round($total_percepciones-$total_deducciones,2);$indice_total++;
			echo '<td align="center">'.$array_fecha_estatus[1].'</td>';
			echo '<td align="center">'.$array_fecha_estatus[2].'</td>';
			echo '</tr>';
			$i++;
			$tper+=$total_percepciones;
			$tded+=$total_deducciones;
			$gtotal+=($total_percepciones-$total_deducciones);
		}
		$col=4;
		/*if($nominagen!=0){
			$col++;
		}*/
		if(count($array_plaza)>1) $col++;
		echo '</tbody>
			<tr bgcolor="#E9F2F8"><td colspan="'.$col.'">'.$i.' Registro(s)</td><td>Total</td>';
			/*<td align="right">'.number_format($tper,2).'</td>
			<td align="right">'.number_format($tded,2).'</td>
			<td align="right">'.number_format($gtotal,2).'</td>';*/
		foreach($array_totales as $v) echo '<td align="right">'.number_format($v,2).'</td>';
		echo '</tr>
		</table>';
		
	} else {
		echo '
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
		</tr>	  
		</table>';
	}	
	exit();
}


if($_POST['ajax']==2){
	$select= " SELECT c.nombre, c.fecha_ini, c.fecha_fin, b.uuid, b.fechatimbre, SUM(b.totalpercepciones) as totalpercepciones,SUM(b.totaldeducciones) as totaldeducciones, GROUP_CONCAT(b.cve) as nominas
		FROM personal as a 
		INNER JOIN personal_nomina as b ON (b.personal=a.cve AND b.tipo='1' AND b.eliminada!=1)
		INNER JOIN periodos_nomina as c on c.cve = b.periodo
		WHERE a.cve='".$_POST['personal']."' AND c.fecha_fin BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	$select.=" AND IFNULL(b.uuid,'')!=''";
	$select .= " GROUP BY b.cve ORDER BY a.nombre";
	$rspersonal=mysql_query($select) or die(mysql_error());
	$totalRegistros = mysql_num_rows($rspersonal);
	if(mysql_num_rows($rspersonal)>0) 
	{
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
		echo '<tr bgcolor="#E9F2F8">';
		
		echo '
		<th rowspan="2">Periodo</th><th rowspan="2">Fecha Inicial</th><th rowspan="2">Fecha Final</th>
		<th rowspan="2">UUID</th><th rowspan="2">Fecha Timbre</th>';
		$res1=mysql_query("SELECT * FROM cat_percepciones WHERE empresa IN (0,".$_POST['cveempresa'].") AND tipo_nomina=1 ORDER BY cve");
		$cpercepciones = mysql_num_rows($res1)+1;
		$res2=mysql_query("SELECT * FROM cat_deducciones WHERE empresa IN (0,".$_POST['cveempresa'].") AND tipo_nomina=1 ORDER BY cve");
		$cdeducciones = mysql_num_rows($res2)+1;
		echo '<th colspan="'.$cpercepciones.'">Percepciones</th><th colspan="'.$cdeducciones.'">Deducciones</th><th rowspan="2">Total a Pagar</th></tr><tr bgcolor="#E9F2F8">';
		
		while($row1=mysql_fetch_array($res1)){
			echo '<th>'.$row1['nombre'].'</th>';
		}
		echo '<th>Total Percepciones</th>';
		
		
		while($row2=mysql_fetch_array($res2)){
			echo '<th>'.$row2['nombre'].'</th>';
		}
		echo '<th>Total Deducciones</th>';

		echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
		$gtotal=0;
		$timp=$tper=$tded=0;
		$i=0;
		$array_totales = array();
		while($Personal=mysql_fetch_array($rspersonal)) {
			rowb();
			$total_percepciones = $Personal['totalpercepciones'];
			$total_deducciones = $Personal['totaldeducciones'];
			echo '<td>'.$Personal['nombre'].'</td>';
			echo '<td align="center">'.$Personal['fecha_ini'].'</td>';
			echo '<td align="center">'.$Personal['fecha_fin'].'</td>';
			echo '<td align="center">'.$Personal['uuid'].'</td>';
			echo '<td align="center">'.$Personal['fechatimbre'].'</td>';
			$indice_total=0;
			$res1=mysql_query("SELECT a.cve, SUM(b.total) as total FROM cat_percepciones a LEFT JOIN personal_nomina_percepcion b ON a.cve = b.percepcion AND b.nomina IN (".$Personal['nominas'].") WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=1 GROUP BY a.cve ORDER BY a.cve");
			while($row1=mysql_fetch_array($res1)){
				echo '<td align="right">'.number_format($row1['total'],2).'</td>';
				$array_totales[$indice_total]+=round($row1['total'],2);$indice_total++;
			}
			echo '<td align="right">'.number_format($total_percepciones,2).'</td>';
			$array_totales[$indice_total]+=round($total_percepciones,2);$indice_total++;
			$res1=mysql_query("SELECT a.cve, SUM(b.total) as total FROM cat_deducciones a LEFT JOIN personal_nomina_deduccion b ON a.cve = b.deduccion AND b.nomina IN (".$Personal['nominas'].") WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=1 GROUP BY a.cve ORDER BY a.cve");
			while($row1=mysql_fetch_array($res1)){
				echo '<td align="right">'.number_format($row1['total'],2).'</td>';
				$array_totales[$indice_total]+=round($row1['total'],2);$indice_total++;
			}
			echo '<td align="right">'.number_format($total_deducciones,2).'</td>';
			$array_totales[$indice_total]+=round($total_deducciones,2);$indice_total++;
			echo '<td align="right">'.number_format($total_percepciones-$total_deducciones,2).'</td>';
			$array_totales[$indice_total]+=round($total_percepciones-$total_deducciones,2);$indice_total++;
			echo '</tr>';
			$i++;
			$tper+=$total_percepciones;
			$tded+=$total_deducciones;
			$gtotal+=($total_percepciones-$total_deducciones);
		}
		$col=4;
		/*if($nominagen!=0){
			$col++;
		}*/
		echo '</tbody>
			<tr bgcolor="#E9F2F8"><td colspan="'.$col.'">'.$i.' Registro(s)</td><td>Total</td>';
			/*<td align="right">'.number_format($tper,2).'</td>
			<td align="right">'.number_format($tded,2).'</td>
			<td align="right">'.number_format($gtotal,2).'</td>';*/
		foreach($array_totales as $v) echo '<td align="right">'.number_format($v,2).'</td>';
		echo '</tr>
		</table>';
		
	} else {
		echo '
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
		</tr>	  
		</table>';
	}	
	exit();
}

top($_SESSION);

if($_POST['cmd']==1){
	$Personal = mysql_fetch_array(mysql_query("SELECT * FROM personal WHERE cve='".$_POST['reg']."'"));
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>
			<td><a href="#" onclick="atcr(\'rep_personal_nomina.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0"></a>&nbsp;&nbsp;Regresar&nbsp;&nbsp;</td>';
			echo'<td><a href="#" onClick="atcr(\'rep_personal_nomina.php\',\'_blank\',\'102\','.$_POST['reg'].')"><img src="images/b_print.png" border="0" title="Imprimir"></a>Excell</td>';
	   
	echo '</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.$_POST['fecha_ini'].'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.$_POST['fecha_fin'].'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '</table>';
	echo '<h2>Nominas del Empleado: '.$Personal['nombre'].'</h2>';
	echo '<br>';
	//Listado
	echo '<div id="Resultados">';
	echo '</div>';
	echo '<script>
	
		function buscarRegistros()
		{
			document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
			objeto=crearObjeto();
			if (objeto.readyState != 0) {
				alert("Error: El Navegador no soporta AJAX");
			} else {
				objeto.open("POST","rep_personal_nomina.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=2&personal='.$_POST['reg'].'&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveempresa="+document.getElementById("cveempresa").value+"&cvemenu="+document.getElementById("cvemenu").value+"&cveusuario="+document.getElementById("cveusuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
		}
		
		buscarRegistros();
	
	</script>';
}

if($_POST['cmd']==0){

	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>';
	   echo'<td><a href="#" onClick="atcr(\'rep_personal_nomina.php\',\'_blank\',\'101\',\'0\')"><img src="images/b_print.png" border="0" title="Imprimir"></a>Excel</td>';
			
	echo '</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.date('Y').'-01-01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.date('Y-m-t').'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	if($_POST['plazausuario'] == 0){
		echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" class="textField"><option value="0">---Seleccione---</option>';
		foreach($array_plaza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
	}
	else{
		echo '<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
	}
	echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" class="textField"></td></tr>'; 
	echo '</table>';
	echo '<br>';
	//Listado
	echo '<div id="Resultados">';
	echo '</div>';
	echo '<script>
	
		function buscarRegistros()
		{
			document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
			objeto=crearObjeto();
			if (objeto.readyState != 0) {
				alert("Error: El Navegador no soporta AJAX");
			} else {
				objeto.open("POST","rep_personal_nomina.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=1&plaza="+document.getElementById("plaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&nombre="+document.getElementById("nombre").value+"&cvemenu="+document.getElementById("cvemenu").value+"&cveusuario="+document.getElementById("cveusuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
		}
		
	
	</script>';
}

bottom();
?>