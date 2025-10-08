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
	
//	if($_POST['mes']==''){
//		$_POST['fecha_ini']='';
//		$_POST['fecha_fin']='';
//	}
//	else{
//		$_POST['fecha_ini']=$_POST['mes'].'-01';
//		$_POST['fecha_fin']=$_POST['mes'].'-31';	
//	}
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Placa</th><th>Tipo de Pago</th><th>Depositante</th><th>$ Ingresos</th>';
	$detallengomado="";
	$detallengomado2="";
	foreach($array_engomado as $k=>$v){
		echo '<th>'.$v.'</th>';
		$detallengomado.=",SUM(IF(a.engomado='$k',1,0)+IFNULL(c.engomado".$k.",0)) as engomado".$k."";
		$detallengomado2.=",SUM(IF(a.engomado='$k',1,0)) as engomado".$k."";
	}
	echo '<th>Total Movimientos</th><th>Reverificacion</th><!--<th>Certificado</br>Cancelado</th>--></tr>';
	$filtro_depositante = "";
	if($_POST['depositante'] != 'all'){
		if($_POST['depositante']>=0)
			$filtro_depositante = " AND b.depositante='".$_POST['depositante']."'";
		elseif($_POST['depositante']==-1)
			$filtro_depositante = " AND b.depositante>0";
		elseif($_POST['depositante']==-2)
			$filtro_depositante = " AND b.depositante>0 AND d.agencia=1";
		elseif($_POST['depositante']==-3)
			$filtro_depositante = " AND b.depositante>0 AND d.agencia=0";
	}
//	$filtro_fechas = "";
//	if($_POST['fecha_ini'] != '') $filtro_fechas .= " AND a.fecha >= '{$_POST['fecha_ini']}'";
//	if($_POST['fecha_fin'] != '') $filtro_fechas .= " AND a.fecha <= '{$_POST['fecha_fin']}'";
	$filtro_movimientos = "";
	if($_POST['movimientos']>0)
		if($_POST['movimientos'] <= 5) $filtro_movimientos = " HAVING COUNT(a.cve) = ".$_POST['movimientos']."";
		else $filtro_movimientos = " HAVING COUNT(a.cve) >= 6";
	if($_POST['reverificacion'] > 0){
		if($filtro_movimientos=="") 
			$filtro_movimientos =" HAVING ";
		else
			$filtro_movimientos .= " AND ";
		if($_POST['reverificacion']==1) $filtro_movimientos.=" certificados > 1";
		else  $filtro_movimientos.=" certificados <= 1";
	}


	$entregas=array();
	$res=mysql_query("SELECT b.cve, TRIM(b.placa),b.tipo_pago,b.depositante,SUM(b.monto) as ingresos".$detallengomado.",
		(COUNT(a.cve)+IFNULL(c.intentos,0)) as total,SUM(IF(a.engomado!=19,1,0)+IFNULL(c.certificados,0)) as certificados
		FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		LEFT JOIN (
				SELECT b.ticketpago".$detallengomado2.",COUNT(a.cve) as intentos,
				SUM(IF(a.engomado!=19,1,0)) as certificados
				FROM certificados a 
				INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."'
			 	AND a.fecha<='".$_POST['fecha_fin']."' AND b.tipo_venta = 1 GROUP BY b.ticketpago
		) c ON b.cve = c.ticketpago 
		LEFT JOIN depositantes d ON d.cve = b.depositante AND d.plaza = b.plaza
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND b.anio IN (".$_POST['anio'].") AND b.estatus!='C' and a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' {$filtro_depositante}
		 AND b.tipo_venta IN (0,2)
		GROUP BY b.cve ".$filtro_movimientos." ORDER BY TRIM(placa)") or die(mysql_error());
		//WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND b.anio IN (".$_POST['anio'].") AND b.estatus!='C' and a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' {$filtro_depositante} {$filtro_fechas}
	while($row=mysql_fetch_array($res)){
		$entregas[$row[0]]['placa'] = $row[1];
		$entregas[$row[0]]['tipo_pago'] = $array_tipo_pago[$row['tipo_pago']];
		$entregas[$row[0]]['depositante'] = $array_depositantes[$row['depositante']];
		$entregas[$row[0]]['ingresos'] = $row['ingresos'];
		foreach($array_engomado as $k=>$v)
			$entregas[$row[0]]['engomado'.$k] = $row['engomado'.$k];
		$entregas[$row[0]]['total'] = $row['total'];
		$entregas[$row[0]]['reverificacion'] = ($row['certificados']>0) ? ($row['certificados']-1) : 0;
	}
	$x=0;
	foreach($entregas as $cve=>$row){
		rowb();
		$placa = $row['placa'];
		echo '<td align="center"><a href="#" onClick="atcr(\'rep_perdida.php\',\'\',1,\''.$cve.'\');">'.$placa.'</a></td>';
		$selec= " SELECT count(cve) as cancel FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'
					and placa='".$row['placa']."' and estatus!='C'";
					$res1=mysql_query($selec);
					$roww=mysql_fetch_array($res1);
		
		echo '<td>'.$row['tipo_pago'].'</td>';
		echo '<td>'.htmlentities(utf8_encode($row['depositante'])).'</td>';
		echo '<td align="right">'.number_format($row['ingresos'],2).'</td>';
		$c=0;
		$total[$c]+=$row['ingresos'];$c++;
		foreach($array_engomado as $k=>$v){
			echo '<td align="right">'.$row['engomado'.$k].'</td>';
			$total[$c]+=$row['engomado'.$k];$c++;
		}
		echo '<td align="right">'.$row['total'].'</td>';
		$total[$c]+=$row['total'];$c++;
		echo '<td align="right">'.$row['reverificacion'].'</td>';
	//	echo '<td align="right">'.$roww['cancel'].'</td>';
		$total[$c]+=$row['reverificacion'];$c++;
	//	$total[$c]+=$roww['cancel'];$c++;
		$x++;
	}
	
	echo '<tr bgcolor="#E9F2F8"><th align="left" colspan="3">'.$x.' Registro(s)</th>';
	foreach($total as $k=>$v){
		if($k==0){
			echo '<th align="right">'.number_format($v,2).'</th>';
		}
		else{
			echo '<th align="right">'.number_format($v,0).'</th>';
		}
	}
	echo '</tr>';
	echo '</table>';
	exit();
}


top($_SESSION);
echo '<input type="hidden" name="rep" id="rep" value="1">';
if($_POST['cmd']==1){
	$res = mysql_query("SELECT * FROM usuarios");
	while($row=mysql_fetch_array($res)){
		$array_usuario[$row['cve']]=$row['usuario'];
	}

	$res = mysql_query("SELECT * FROM tipo_combustible ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_tipo_combustible[$row['cve']]=$row['nombre'];
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

	$array_tipo_venta[0] = array('nombre'=>'Normal','costo'=>-1,'maneja_motivo'=>0);
	$res = mysql_query("SELECT * FROM tipo_venta ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_tipo_venta[$row['cve']] = array('nombre'=>$row['nombre'],'costo'=>$row['costo'],'maneja_motivo'=>$row['maneja_motivo']);
	}
	echo '<input type="hidden" name="mes" id="mes" value="'.$_POST['mes'].'">';
	echo '<input type="hidden" name="fecha_ini" id="fecha_ini" value="'.$_POST['fecha_ini'].'">';
	echo '<input type="hidden" name="fecha_fin" id="fecha_fin" value="'.$_POST['fecha_fin'].'">';
	echo '<table>';
	echo '
		<tr>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'rep_perdida.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	$titulo_fechas='';
	$filtro_fechas='';
	if($_POST['fecha_ini'] != ''){
		$filtro_fechas .= " AND b.fecha >= '{$_POST['fecha_ini']}'";
		$titulo_fechas.=' del '.$_POST['fecha_ini'];
	}
	if($_POST['fecha_fin'] != ''){
		$filtro_fechas .= " AND b.fecha <= '{$_POST['fecha_fin']}'";
		$titulo_fechas.=' al '.$_POST['fecha_fin'];
	}
	if($_POST['mes']!=''){
		//$titulo_fechas.=' de '.$array_meses[intval(substr($_POST['mes'],5,2))].' '.substr($_POST['mes'],0,4);
	}
	echo '<h2>Desglose de la placa '.$_POST['reg'].' del '.$array_anios[$_POST['anio']].$titulo_fechas.'</h2>';
	$select= " SELECT a.*,b.cve as certificado, b.certificado as holograma,b.engomado as engomado_entrega FROM cobro_engomado a 
	LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
	WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus!='C' AND (a.cve='".$_POST['reg']."' OR a.ticketpago='".$_POST['reg']."') AND a.anio IN (".$_POST['anio']."){$filtro_fechas}";
	
	$select.=" ORDER BY a.cve DESC";
	$res=mysql_query($select);
	$totalRegistros = mysql_num_rows($res);
	
	
	if(mysql_num_rows($res)>0) 
	{
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>Ticket</th><th>Fecha</th><!--<th>Referencia Maquina Registradora--></th><th>Placa</th>
		<th>Tiene Multa</th><th>Tipo de Certificado</th><th>Tipo Venta</th><th>Monto</th><th>A&ntilde;o Certificacion</th><th>Tipo de Pago</th><th>Tipo Combustible</th><!--<th>Documento</th>--><th>Factura</th><th>Entrega Certificado</th><th>Holograma Entregado</th><th>Tipo Certificacion Entregada</th><th>Usuario</th><th>Motivo Cancelacion</th>';
		echo '</tr>';
		$t=0;
		while($row=mysql_fetch_array($res)) {
			rowb();
			echo '<td align="center">'.htmlentities($row['cve']).'</td>';
			echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
			//echo '<td align="center">'.htmlentities($row['referencia_maquina_registradora']).'</td>';
			if($row['estatus']=='C'){
				echo '<td align="center">'.htmlentities($row['placa']).'</td>';
			}
			elseif($row['certificado']>0 && $row['engomado']!=$row['engomado_entrega']){
				echo '<td align="center"><font color="RED">'.htmlentities($row['placa']).'</font></td>';
			}
			else{
				$res1 = mysql_query("SELECT cve FROM certificados WHERE placa='".$row['placa']."' AND fecha>='".$row['fecha']."' AND DATE_ADD(fecha,INTERVAL 30 DAY)>='".$row['fecha']."'");
				if(mysql_num_rows($res1)==0)
					echo '<td align="center"><font color="GREEN">'.htmlentities($row['placa']).'</font></td>';
				else
					echo '<td align="center">'.htmlentities($row['placa']).'</td>';
			}
			echo '<td align="center">'.htmlentities($array_nosi[$row['multa']]).'</td>';
			echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
			echo '<td align="center">'.htmlentities($array_tipo_venta[$row['tipo_venta']]['nombre']).'</td>';
			echo '<td align="center">'.number_format($row['monto'],2).'</td>';
			echo '<td align="center">'.htmlentities($array_anios[$row['anio']]).'</td>';
			echo '<td align="center">'.htmlentities($array_tipo_pago[$row['tipo_pago']]).'</td>';
			echo '<td align="center">'.htmlentities($array_tipo_combustible[$row['tipo_combustible']]).'</td>';
			echo '<td align="center">'; 
			if($row['factura']==0){
				echo '&nbsp;';
			}
			else{
				$res1=mysql_query("SELECT serie,folio FROM facturas WHERE plaza='".$row['plaza']."' AND cve='".$row['factura']."'") or die(mysql_error());
				$row1=mysql_fetch_array($res1);
				echo $row1['serie'].' '.$row1['folio']; 
			}
			echo '</td>';
			echo '<td align="center">'.$row['certificado'].'</td>';
			echo '<td align="center">'.$row['holograma'].'</td>';
			echo '<td align="center">'.htmlentities($array_engomado[$row['engomado_entrega']]).'</td>';
			echo '<td align="center">'.htmlentities(utf8_encode($array_usuario[$row['usuario']])).'</td>';
			echo '<td>'.htmlentities(utf8_encode($row['obscan'])).'</td>';
			echo '</tr>';
			$t+=$row['monto'];
		}
		echo '	
			<tr>
			<td colspan="6" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
			<td align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</td>
			<td colspan="9" bgcolor="#E9F2F8">&nbsp;</td>
			</tr>
		</table>';
		
	} else {
		echo '
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
		</tr>	  
		</table>';
	}
}

if ($_POST['cmd']<1) {
	$nivelUsuario=nivelUsuario();
	if($_POST['rep']!=1){
		$_POST['fecha_ini'] = fechaLocal();
		$_POST['fecha_fin'] = fechaLocal();
	}
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	echo '
		 </tr>';
	echo '</table>';
	echo '<table>';
//	echo '<tr style="display:none;"><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
//	echo '<tr style="display:none;"><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr ><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="'.$_POST['fecha_ini'].'">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr ><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="'.$_POST['fecha_fin'].'">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
/*	echo '<tr><td>Mes</td><td><select name="mes" id="mes"><option value="">Todos</option>';
	echo '<option value="'.date('Y-m').'"';
	if($_POST['mes']==date('Y-m')) echo ' selected';
	echo '>'.date('Y-m').'</option>';
	$mes = date( "Y-m" , strtotime ( "-1 month" , strtotime(date('Y-m').'-01') ) );
	while($mes>='2016-01'){
		echo '<option value="'.$mes.'"';
		if($_POST['mes']==$mes) echo ' selected';
		echo '>'.$mes.'</option>';
		$mes = date( "Y-m" , strtotime ( "-1 month" , strtotime($mes.'-01') ) );
	}
	echo '</select></td></tr>';*/
	echo '<tr><td>A&ntilde;o Certificacion</td><td><select multiple="multiple" name="anios" id="anios">';
	$c=0;
	foreach($array_anios as $k=>$v){
			echo '<option value="'.$k.'"';
			if($c==0) echo ' selected';
			echo '>'.$v.'</option>';
			$c++;
	}
	echo '</select><input type="hidden" name="anio" id="anio" value=""></td></tr>';
	echo '<tr><td>Depositante</td><td><select name="depositante" id="depositante"><option value="all" selected>Todos</option>
	<option value="0">Particulares</option>
	<option value="-1">Solo Depositantes</option>
	<option value="-2">Agencias</option>
	<option value="-3">Talleres</option>';
	foreach($array_depositantes as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>No. Movimientos</td><td><select name="movimientos" id="movimientos"><option value="0" selected>Todos</option>
	<option value="1">1</option>
	<option value="2">2</option>
	<option value="3">3</option>
	<option value="4">4</option>
	<option value="5">5</option>
	<option value="6">6 o mas</option>';
	echo '</select></td></tr>';
	echo '<tr><td>Reverificacion</td><td><select name="reverificacion" id="reverificacion"><option value="0" selected>Todos</option>
	<option value="1">Si</option>
	<option value="2">No</option>';
	echo '</select></td></tr>';
	echo '</table>';
	echo '<br>';
	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">
	$("#anios").multipleSelect({
		width: 500
	});	
	function buscarRegistros()
	{
		document.forma.anio.value=$("#anios").multipleSelect("getSelects");
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","rep_perdida.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&reverificacion="+document.getElementById("reverificacion").value+"&movimientos="+document.getElementById("movimientos").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&depositante="+document.getElementById("depositante").value+"&anio="+document.getElementById("anio").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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