<?php
include("main.php");

//ARREGLOS

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsUsuario=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza where a.estatus!='I' ORDER BY b.localidad_id, a.lista, a.numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}


$res=mysql_query("SELECT * FROM areas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_localidad[$row['cve']]=$row['nombre'];
}

$array_color=array('5 y 6'=>array(5,6),'7 y 8'=>array(7,8), '3 y 4'=>array(3,4),'1 y 2'=>array(1,2),'0 y 9'=>array(0,9));


//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$array_tipo_placa = array(1=>"Particular",2=>"Intensivo");

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);

$abono=0;

function numeroPlaca($placa){
	$numero = '';
	for($i=0;$i<strlen($placa);$i++){
		if($placa[$i]>='0' && $placa[$i]<='9'){
			$numero = $placa[$i];
		}
	}
	return $numero;
}

function tipoPlaca($placa,$array_tipos_placa){
	global $array_color;
	$tipo = 0;
	$numero = '';
	for($i=0;$i<strlen($placa);$i++){
		if($placa[$i]>='0' && $placa[$i]<='9'){
			$numero = $placa[$i];
		}
	}
	
	foreach($array_tipos_placa as $tipos)
	{
		if($tipo == 0){
			if(strlen($tipos['placa']) == strlen($placa))
			{
				if(preg_match($tipos['expresion'], $placa)){
					$tipo = $tipos['tipo'];
				}
			}
		}
	}
	
	return $tipo;
}




if($_POST['cmd']==100){
	echo '<h1>Reporte de Certificados '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'];
	if($_POST['plazausuario']>0) echo '<br>Plaza: '.$array_plazas[$_POST['plazausuario']];
	echo '</h1>';
	echo fechaLocal().' '.horaLocal().'<br>';
	$res = mysql_query("SELECT * FROM engomados WHERE localidad = '".$_POST['localidad']."' ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_engomados[$row['cve']]=$row['nombre'];
	}

	$res=mysql_query("SELECT * FROM tipo_placa WHERE localidad='".$_POST['localidad']."' ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_tipos_placa[$row['cve']]['tipo']=$row['tipo'];
		$array_tipos_placa[$row['cve']]['placa']=$row['placa'];
		$array_tipos_placa[$row['cve']]['expresion']=$row['expresion'];
	}
	
	$array_total_digitos = array();
	$array_total_engomados = array();
	$res = mysql_query("SELECT a.placa, a.engomado, c.engomado as engomadoventa, b.nombre, c.monto FROM certificados a 
	INNER JOIN engomados b on b.cve = a.engomado 
	INNER JOIN cobro_engomado c ON c.plaza = a.plaza AND c.cve = a.ticket 
	INNER JOIN datosempresas d ON a.plaza = d.plaza
	WHERE a.plaza IN (".$_POST['plaza'].") AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus!='C' AND d.localidad_id = '".$_POST['localidad']."'");
	while($row = mysql_fetch_array($res)){
		$tipo_placa = tipoPlaca($row['placa'],$array_tipos_placa);
		if($row['nombre']=='RECHAZO'){
			if($row['monto']==0){
				$row1=mysql_fetch_array(mysql_query("SELECT engomado FROM cobro_engomado WHERE plaza = '".$row['plaza']."' AND placa='".$row['placa']."' AND estatus!='C' AND monto>0 ORDER BY cve DESC LIMIT 1"));
				if($row1['cve']==""){
					$row['engomadoventa']=8;
				}
				else{
					$row['engomadoventa']=$row1['engomado'];
				}
			}
			$array_total_engomados[$tipo_placa][$row['engomadoventa']][1]++;
			$array_total_digitos[numeroPlaca($row['placa'])][1]++;
		}
		else{
			$array_total_engomados[$tipo_placa][$row['engomado']][0]++;
			$array_total_digitos[numeroPlaca($row['placa'])][0]++;
		}
	}
	foreach($array_tipo_placa as $tipoplacaid=>$tipoplaca){
		echo '<br>';
		echo '<h1>'.$tipoplaca.'</h1>';
		echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><th>Tipo de Verificacion</th><th>Cantidad</th><th>Rechazos</th><th>Total</th></tr>';
		foreach($array_total_engomados[$tipoplacaid] as $tipoengomado=>$cantidades){
			echo '<tr>';
			echo '<td>'.htmlentities(utf8_encode($array_engomados[$tipoengomado])).'</td>';
			echo '<td align="right">'.$cantidades[0].'</td>';
			echo '<td align="right">'.$cantidades[1].'</td>';
			echo '<td align="right">'.($cantidades[0]+$cantidades[1]).'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
		
	echo '<br><br>';	
	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	echo '<tr><th>Digitos</th><th>Aprobados</th><th>Rechazados</th><th>Total</th></tr>';
	foreach($array_color as $k=>$v){
		echo '<tr>';
		echo '<td align="center">'.$k.'</td>';
		echo '<td align="right">'.($array_total_digitos[$v[0]][0]+$array_total_digitos[$v[1]][0]).'</td>';
		echo '<td align="right">'.($array_total_digitos[$v[0]][1]+$array_total_digitos[$v[1]][1]).'</td>';
		echo '<td align="right">'.($array_total_digitos[$v[0]][0]+$array_total_digitos[$v[1]][0]+$array_total_digitos[$v[0]][1]+$array_total_digitos[$v[1]][1]).'</td>';
		echo '</tr>';
	}
	echo '</table><br><br>';
	exit();
}

if($_POST['ajax']==1){

	$res = mysql_query("SELECT * FROM engomados WHERE localidad = '".$_POST['localidad']."' ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_engomados[$row['cve']]=$row['nombre'];
	}

	$res=mysql_query("SELECT * FROM tipo_placa WHERE localidad='".$_POST['localidad']."' ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_tipos_placa[$row['cve']]['tipo']=$row['tipo'];
		$array_tipos_placa[$row['cve']]['placa']=$row['placa'];
		$array_tipos_placa[$row['cve']]['expresion']=$row['expresion'];
	}
	
	$array_total_digitos = array();
	$array_total_engomados = array();
	$res = mysql_query("SELECT a.plaza, a.placa, a.engomado, c.engomado as engomadoventa, b.nombre, c.monto FROM certificados a 
	INNER JOIN engomados b on b.cve = a.engomado 
	INNER JOIN cobro_engomado c ON c.plaza = a.plaza AND c.cve = a.ticket 
	INNER JOIN datosempresas d ON a.plaza = d.plaza
	WHERE a.plaza IN (".$_POST['plaza'].") AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus!='C' AND d.localidad_id = '".$_POST['localidad']."'");
	while($row = mysql_fetch_array($res)){
		$tipo_placa = tipoPlaca($row['placa'],$array_tipos_placa);
		if($row['nombre']=='RECHAZO'){
			if($row['monto']==0){
				$row1=mysql_fetch_array(mysql_query("SELECT engomado FROM cobro_engomado WHERE plaza = '".$row['plaza']."' AND placa='".$row['placa']."' AND estatus!='C' AND monto>0 ORDER BY cve DESC LIMIT 1"));
				if($row1['cve']==""){
					$row['engomadoventa']=8;
				}
				else{
					$row['engomadoventa']=$row1['engomado'];
				}
			}
			$array_total_engomados[$tipo_placa][$row['engomadoventa']][1]++;
			$array_total_digitos[numeroPlaca($row['placa'])][1]++;
		}
		else{
			$array_total_engomados[$tipo_placa][$row['engomado']][0]++;
			$array_total_digitos[numeroPlaca($row['placa'])][0]++;
		}
	}
	
	foreach($array_tipo_placa as $tipoplacaid=>$tipoplaca){
		echo '<br>';
		echo '<h1>'.$tipoplaca.'</h1>';
		echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><th>Tipo de Verificacion</th><th>Cantidad</th><<th>Rechazos</th><th>Total</th></tr>';
		foreach($array_total_engomados[$tipoplacaid] as $tipoengomado=>$cantidades){
			echo '<tr>';
			echo '<td>'.htmlentities(utf8_encode($array_engomados[$tipoengomado])).'</td>';
			echo '<td align="right"><a href="#" onClick="';
			if($_POST['plazausuario']==0) echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
			echo 'atcr(\'rep_certificados.php\',\'\',1,\''.$tipoplacaid.'|'.$tipoengomado.'|1\')">'.$cantidades[0].'</a></td>';
			echo '<td align="right"><a href="#" onClick="';
			if($_POST['plazausuario']==0) echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
			echo 'atcr(\'rep_certificados.php\',\'\',1,\''.$tipoplacaid.'|'.$tipoengomado.'|2\')">'.$cantidades[1].'</a></td>';
			echo '<td align="right"><a href="#" onClick="';
			if($_POST['plazausuario']==0) echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
			echo 'atcr(\'rep_certificados.php\',\'\',1,\''.$tipoplacaid.'|'.$tipoengomado.'|0\')">'.($cantidades[0]+$cantidades[1]).'</a></td>';
			echo '</tr>';
		}
		echo '</table>';
	}
		
	echo '<br><br>';	
	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	echo '<tr><th>Digitos</th><th>Aprobados</th><th>Rechazados</th><th>Total</th></tr>';
	foreach($array_color as $k=>$v){
		echo '<tr>';
		echo '<td align="center">'.$k.'</td>';
		echo '<td align="right"><a href="#" onClick="';
		if($_POST['plazausuario']==0) echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
		echo 'atcr(\'rep_certificados.php\',\'\',2,\''.$k.'|1\')">'.($array_total_digitos[$v[0]][0]+$array_total_digitos[$v[1]][0]).'</a></td>';
		echo '<td align="right"><a href="#" onClick="';
		if($_POST['plazausuario']==0) echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
		echo 'atcr(\'rep_certificados.php\',\'\',2,\''.$k.'|2\')">'.($array_total_digitos[$v[0]][1]+$array_total_digitos[$v[1]][1]).'</a></td>';
		echo '<td align="right"><a href="#" onClick="';
		if($_POST['plazausuario']==0) echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
		echo 'atcr(\'rep_certificados.php\',\'\',2,\''.$k.'|0\')">'.($array_total_digitos[$v[0]][0]+$array_total_digitos[$v[1]][0]+$array_total_digitos[$v[0]][1]+$array_total_digitos[$v[1]][1]).'</a></td>';
		echo '</tr>';
	}
	echo '</table><br><br>';
	exit();
}


top($_SESSION);
	if($_POST['cmd']==2){
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="atcr(\'rep_certificados.php\',\'\',0,0)"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a>&nbsp;&nbsp;</td>
				';
		echo '</tr>';
		echo '</table>';
		
		$res=mysql_query("SELECT * FROM tipo_placa WHERE localidad='".$_POST['localidad']."' ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_tipos_placa[$row['cve']]['tipo']=$row['tipo'];
			$array_tipos_placa[$row['cve']]['placa']=$row['placa'];
			$array_tipos_placa[$row['cve']]['expresion']=$row['expresion'];
		}
		
		$array_engomado = array();
		$res = mysql_query("SELECT * FROM engomados WHERE 1 ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_engomado[$row['cve']]=$row['nombre'];
		}
		$res = mysql_query("SELECT * FROM usuarios");
		while($row=mysql_fetch_array($res)){
			$array_usuario[$row['cve']]=$row['usuario'];
		}

		$res = mysql_query("SELECT * FROM tipo_combustible ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_tipo_combustible[$row['cve']]=$row['nombre'];
		}

		$res = mysql_query("SELECT * FROM tecnicos WHERE plaza='".$_POST['plazausuario']."'");
		while($row=mysql_fetch_array($res)){
			$array_personal[$row['cve']]=$row['nombre'];
		}
		$array_color=array(1=>'5 y 6',2=>'7 y 8',3=>'3 y 4',4=>'1 y 2',5=>'0 y 9');
		$datos = explode("|",$_POST['reg']);
		if($datos[2]==2){
			$tipo2='Rechazados';
			$filtro = " AND c.engomado = '".$datos[1]."' AND b.nombre='RECHAZO'";
		}
		elseif($datos[2]==1){
			$filtro = " AND a.engomado = '".$datos[1]."'";
		}
		else{
			$filtro = " AND (a.engomado = '".$datos[1]."' OR (c.engomado = '".$datos[1]."' AND b.nombre='RECHAZO'))";
		}
		echo '<h1>Reporte de Certificados por tipo placa '.$array_tipo_placa[$datos[0]].' de '.$array_engomado[$datos[1]].' '.$tipo2.' del dia '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h1>';
		$res = mysql_query("SELECT a.plaza, a.cve, a.fecha, a.ticket, a.placa, c.tipo_combustible, a.engomado, 
		c.engomado as engomadoventa, b.nombre, c.monto, a.tecnico, a.certificado, a.entregado, a.usuario FROM certificados a 
		INNER JOIN engomados b on b.cve = a.engomado 
		INNER JOIN cobro_engomado c ON c.plaza = a.plaza AND c.cve = a.ticket 
		INNER JOIN datosempresas d ON a.plaza = d.plaza
		WHERE a.plaza IN (".$_POST['plaza'].") AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus!='C' AND d.localidad_id = '".$_POST['localidad']."'".$filtro);
		$totalRegistros = mysql_num_rows($res);
		
		$array_totales_engomados=array();
		$rechazados=0;
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Plaza</th><th>Folio</th><th>Fecha</th><th>Ticket</th><th>Placa</th><th>Tipo de Combustible</th>
			<th>Tipo de Certificado</th><th>Tecnico</th><th>Holograma</th><th>Entregado</th><th>Usuario</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				$tipo_placa = tipoPlaca($row['placa'],$array_tipos_placa);
				if($tipo_placa == $datos[0]){
						rowb();
						echo '<td align="left">'.$array_plazas[$row['plaza']].'</td>';
						echo '<td align="center">'.htmlentities($row['cve']).'</td>';
						echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
						echo '<td align="center">'.htmlentities($row['ticket']).'</td>';
						echo '<td align="center">'.htmlentities($row['placa']).'</td>';
						echo '<td align="center">'.htmlentities($array_tipo_combustible[$row['tipo_combustible']]).'</td>';
						echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
						echo '<td align="left">'.htmlentities(utf8_encode($array_personal[$row['tecnico']])).'</td>';
						echo '<td align="center">'.htmlentities($row['certificado']).'</td>';
						echo '<td align="center">'.htmlentities($array_nosi[$row['entregado']]).'</td>';
						echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
						echo '</tr>';
						$t++;
				}
			}
			echo '	
				<tr>
				<td colspan="11" bgcolor="#E9F2F8">'.$t.' Registro(s)</td>
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


	if($_POST['cmd']==1){
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="atcr(\'rep_certificados.php\',\'\',0,0)"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a>&nbsp;&nbsp;</td>
				';
		echo '</tr>';
		echo '</table>';
		
		$res=mysql_query("SELECT * FROM tipo_placa WHERE localidad='".$_POST['localidad']."' ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_tipos_placa[$row['cve']]['tipo']=$row['tipo'];
			$array_tipos_placa[$row['cve']]['placa']=$row['placa'];
			$array_tipos_placa[$row['cve']]['expresion']=$row['expresion'];
		}
		
		$array_engomado = array();
		$res = mysql_query("SELECT * FROM engomados WHERE 1 ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_engomado[$row['cve']]=$row['nombre'];
		}
		$res = mysql_query("SELECT * FROM usuarios");
		while($row=mysql_fetch_array($res)){
			$array_usuario[$row['cve']]=$row['usuario'];
		}

		$res = mysql_query("SELECT * FROM tipo_combustible ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_tipo_combustible[$row['cve']]=$row['nombre'];
		}

		$res = mysql_query("SELECT * FROM tecnicos WHERE plaza='".$_POST['plazausuario']."'");
		while($row=mysql_fetch_array($res)){
			$array_personal[$row['cve']]=$row['nombre'];
		}
		$array_color=array(1=>'5 y 6',2=>'7 y 8',3=>'3 y 4',4=>'1 y 2',5=>'0 y 9');
		$datos = explode("|",$_POST['reg']);
		if($datos[2]==2){
			$tipo2='Rechazados';
			$filtro = " AND b.nombre='RECHAZO'";
		}
		elseif($datos[2]==1){
			$filtro = " AND a.engomado = '".$datos[1]."'";
		}
		else{
			$filtro = " AND (a.engomado = '".$datos[1]."' OR b.nombre='RECHAZO')";
		}
		echo '<h1>Reporte de Certificados por tipo placa '.$array_tipo_placa[$datos[0]].' de '.$array_engomado[$datos[1]].' '.$tipo2.' del dia '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h1>';
		$res = mysql_query("SELECT a.plaza, a.cve, a.fecha, a.ticket, a.placa, c.tipo_combustible, a.engomado, 
		c.engomado as engomadoventa, b.nombre, c.monto, a.tecnico, a.certificado, a.entregado, a.usuario FROM certificados a 
		INNER JOIN engomados b on b.cve = a.engomado 
		INNER JOIN cobro_engomado c ON c.plaza = a.plaza AND c.cve = a.ticket 
		INNER JOIN datosempresas d ON a.plaza = d.plaza
		WHERE a.plaza IN (".$_POST['plaza'].") AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus!='C' AND d.localidad_id = '".$_POST['localidad']."'".$filtro);
		$totalRegistros = mysql_num_rows($res);
		
		$array_totales_engomados=array();
		$rechazados=0;
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Plaza</th><th>Folio</th><th>Fecha</th><th>Ticket</th><th>Placa</th><th>Tipo de Combustible</th>
			<th>Tipo de Certificado</th><th>Tecnico</th><th>Holograma</th><th>Entregado</th><th>Usuario</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				$tipo_placa = tipoPlaca($row['placa'],$array_tipos_placa);
				if($row['nombre'] == 'RECHAZO' && $row['monto'] == 0){
					if($row['monto']==0){
						$row1=mysql_fetch_array(mysql_query("SELECT engomado FROM cobro_engomado WHERE plaza = '".$row['plaza']."' AND placa='".$row['placa']."' AND estatus!='C' AND monto>0 ORDER BY cve DESC LIMIT 1"));
						if($row1['cve']==""){
							$row['engomadoventa']=8;
						}
						else{
							$row['engomadoventa']=$row1['engomado'];
						}
					}
				}
				if($tipo_placa == $datos[0] && ($row['nombre']!='RECHAZO' || $row['engomadoventa']==$datos[1])){
						rowb();
						echo '<td align="left">'.$array_plazas[$row['plaza']].'</td>';
						echo '<td align="center">'.htmlentities($row['cve']).'</td>';
						echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
						echo '<td align="center">'.htmlentities($row['ticket']).'</td>';
						echo '<td align="center">'.htmlentities($row['placa']).'</td>';
						echo '<td align="center">'.htmlentities($array_tipo_combustible[$row['tipo_combustible']]).'</td>';
						echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
						echo '<td align="left">'.htmlentities(utf8_encode($array_personal[$row['tecnico']])).'</td>';
						echo '<td align="center">'.htmlentities($row['certificado']).'</td>';
						echo '<td align="center">'.htmlentities($array_nosi[$row['entregado']]).'</td>';
						echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
						echo '</tr>';
						$t++;
				}
			}
			echo '	
				<tr>
				<td colspan="11" bgcolor="#E9F2F8">'.$t.' Registro(s)</td>
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

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="if(document.forma.localidad.value==\'all\'){
					alert(\'Necesita seleccionar una localidad\');
				}
				else{
					buscarRegistros(0,1);
				}"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="if(document.forma.localidad.value==\'all\'){
					alert(\'Necesita seleccionar una localidad\');
				}
				else{';
				if($_POST['plazausuario']==0) echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
				echo 'atcr(\'rep_certificados.php\',\'_blank\',100,0);}"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.date( "Y-m-d" , strtotime ( "-6 day" , strtotime(fechaLocal()) ) ).'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		if($_POST['plazausuario']>0){
			echo '<tr><td>Plaza</td><td>'.$array_plazas[$_POST['plazausuario']].'<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'"><input type="hidden" name="localidad" id="localidad" value="'.$rowempresa['localidad_id'].'"></td></tr>';
		}
		else{
			echo '<tr><td align="left">Localidad</td><td><select name="localidad" id="localidad"><option value="all">Seleccione</option>';
			foreach($array_localidad as $k=>$v){
				echo '<option value="'.$k.'"';
				echo '>'.$v.'</option>';
			}
			echo '</select>';
			echo '<tr><td align="left">Plaza</td><td><select multiple="multiple" name="plazas" id="plazas">';
			foreach($array_plazas as $k=>$v){
				echo '<option value="'.$k.'" selected>'.$v.'</option>';
			}
			echo '</select>';
			echo '<input type="hidden" name="plaza" id="plaza" value=""></td></tr>';
		}
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
bottom();
echo '
<Script language="javascript">';
if($_POST['plazausuario']==0){
	echo '
	$("#plazas").multipleSelect({
		width: 500
	});	
	function buscarRegistros(){
		document.forma.plaza.value=$("#plazas").multipleSelect("getSelects");
	';
}
else{
	echo 'function buscarRegistros(){
	';
}
echo '  document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","rep_certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&localidad="+document.getElementById("localidad").value+"&plaza="+document.getElementById("plaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
		
	
	

	</Script>
';

?>