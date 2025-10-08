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
	
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Placa</th><th>Tipo de Pago</th><th>Depositante</th><th>$ Ingresos</th>';
	foreach($array_engomado as $v) echo '<th>'.$v.'</th>';
//	echo '<th>$ Entregas</th><th>$ Perdida</th>';
	echo '<th>Total Movimientos</th><th>Reverificacion</th></tr>';
	$filtro_depositante = "";
	if($_POST['depositante'] != 'all') $filtro_depositante = " AND a.depositante='".$_POST['depositante']."'";
	$filtro_fechas = "";
	if($_POST['fecha_ini'] != '') $filtro_fechas .= " AND a.fecha >= '{$_POST['fecha_ini']}'";
	if($_POST['fecha_fin'] != '') $filtro_fechas .= " AND a.fecha <= '{$_POST['fecha_fin']}'";


	$tentregas=array();
	$entregas=array();
	$res1=mysql_query("SELECT TRIM(a.placa),b.engomado,COUNT(b.cve),SUM(b.monto) FROM cobro_engomado a 
		INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.anio IN (".$_POST['anio'].") AND b.estatus!='C' {$filtro_depositante} {$filtro_fechas}
		GROUP BY TRIM(a.placa),b.engomado");
	while($row1=mysql_fetch_array($res1)){
		$entregas[$row1[0]][$row1[1]]=$row1[2];
		$tentregas[$row1[0]]+=$row1[3];
	}
	
	$res = mysql_query("SELECT TRIM(a.placa),SUM(a.monto),SUM(IF(a.monto=0,1,0)) as tiene_cero, a.tipo_pago, a.depositante FROM cobro_engomado a 
	WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus!='C' AND a.anio IN (".$_POST['anio'].") AND a.tipo_venta != 3 {$filtro_depositante} {$filtro_fechas}
	GROUP BY TRIM(a.placa) HAVING tiene_cero > 0");
	$total = array();
	
	$x=0;
	while($row=mysql_fetch_array($res)){
		rowb();
		$c=0;
		$t=0;
		$tt=0;
		$t1=0;
		$c1=0;
		$t_reveri = array();
		echo '<td align="center"><a href="#" onClick="atcr(\'rep_perdida.php\',\'\',1,\''.$row[0].'\');">'.$row[0].'</a></td>';
		echo '<td>'.$array_tipo_pago[$row['tipo_pago']].'</td>';
		echo '<td>'.htmlentities(utf8_encode($array_depositantes[$row['depositante']])).'</td>';
		echo '<td align="right">'.number_format($row[1],2).'</td>';
		$total[$c]+=$row[1];$c++;
		
		foreach($array_engomado as $k=>$v){
//			echo '<td align="right">'.$entregas[$k].'-'.$k.'-'.$v.'</td>';
			echo '<td align="right">'.$entregas[$row[0]][$k].'</td>';
			if($k!=19){
			$t_reveri[$c1]+=$entregas[$row[0]][$k];$c1++;
			$tt=$tt+$entregas[$row[0]][$k];
			}
			$t=$t+$entregas[$row[0]][$k];
			$total[$c]+=$entregas[$row[0]][$k];$c++;
		}

		echo'<td align="right">'.$t.'</td>';
//		$t1=$t1 - $t_reveri[0];
		foreach($t_reveri as $k=>$v){
		//if($k>0){
//			if($k==0 and $v!=""){$t1=$tt-$v;}
			if($k==0 and $v!=""){if($k==0 and $v>1){$t2=$v - 1;    $t1=$t2;}else{$t1=$tt-$v;}}
			if($k==1 and $v!=""){if($k==1 and $v>1){$t2=$v - 1;    $t1=$t2;}else{$t1=$tt-$v;}}
			if($k==2 and $v>1 and $t_reveri[1]=="" and $t_reveri[0]==""){$t2=$v - 1;    $t1=$t2;}
			if($k==3 and $v>1 and $t_reveri[1]=="" and $t_reveri[0]==""){$t2=$v - 1;    $t1=$t2;}
//			if($k==3 and $v>1){$t2=$v - 1;    $t1=$t2;}
		//}
/*//		if($k>0){
		if($k==1 and $v!=""){$t1=$t-$v;}
		if($k==0 and $v!=""){$t1=$t-$v;}
		if($k==2 and $v>1){$t2=$v - 1;    $t1=$t2;}
		if($k==3 and $v>1){$t2=$v - 1;    $t1=$t2;}
//		}*/

		}
	echo'<td align="right">';echo''.$t1.'';echo'</td>';
	    
		$total[$c]+=$t;$c++;
		$total[$c]+=$t1;$c++;
		//echo '<td align="right">'.number_format($tentregas,2).'</td>';
		$total[$c]+=$tentregas[$row[0]];$c++;
		$perdida = (($tentregas[$row[0]]-$row[1])>0) ? ($tentregas[$row[0]]-$row[1]) : 0;
		//echo '<td align="right">'.number_format($perdida,2).'</td>'; 
		$total[$c]+=$perdida;$c++;
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8"><th align="left" colspan="3">'.$x.' Registro(s)</th>';
	foreach($total as $k=>$v){
		if($k<8){
		echo '<th align="right">'.number_format($v,2).'</th>';
		}
	}
	echo '</tr>';
	echo '</table>';
	exit();
}


top($_SESSION);

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
		$titulo_fechas .= ' del '.$_POST['fecha_ini'];
		$filtro_fechas .= " AND a.fecha >= '{$_POST['fecha_ini']}'";
	}
	if($_POST['fecha_fin'] != ''){
		$titulo_fechas .= ' al '.$_POST['fecha_fin'];
		$filtro_fechas .= " AND a.fecha <= '{$_POST['fecha_fin']}'";
	}
	echo '<h2>Desglose de la placa '.$_POST['reg'].' del '.$array_anios[$_POST['anio']].$titulo_fechas.'</h2>';
	$select= " SELECT a.*,b.cve as certificado, b.certificado as holograma,b.engomado as engomado_entrega FROM cobro_engomado a 
	LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
	WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus!='C' AND a.placa='".$_POST['reg']."' AND a.anio IN (".$_POST['anio']."){$filtro_fechas}";
	
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
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	echo '
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
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
	<option value="0">Sin Depositante</option>';
	foreach($array_depositantes as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
	}
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
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&depositante="+document.getElementById("depositante").value+"&anio="+document.getElementById("anio").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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