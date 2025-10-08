<?php 
include ("main.php"); 


$array_tipo_cortesia = array(1=>'Autorizada', 2=>'10x1');
$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$res=mysql_query("SELECT local,vende_seguros,intentoporcertificadodif,num_intentos,num_intentosanticipados FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];
$VendeSeguros=$row[1];
$num_intentos_plaza = $row['num_intentos'];
$num_intentos_anticipados = $row['num_intentosanticipados'];
$intentoporcertificadodif = $row['intentoporcertificadodif'];

$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND plazas like '%|".$_POST['plazausuario']."|%' AND venta=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio'];
}

$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

if($_POST['cmd']==1)
	$res = mysql_query("SELECT * FROM anios_certificados WHERE venta=1 ORDER BY nombre DESC LIMIT 2");
else
	$res = mysql_query("SELECT * FROM anios_certificados  WHERE venta=1 ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
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

$array_tipo_venta[0] = array('nombre'=>'Con Importe','costo'=>-1,'maneja_motivo'=>0);
$res = mysql_query("SELECT * FROM tipo_venta ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_venta[$row['cve']] = array('nombre'=>$row['nombre'],'costo'=>$row['costo'],'maneja_motivo'=>$row['maneja_motivo'],'maneja_autoriza'=>$row['maneja_autoriza']);
}

function generar_clave($plaza){
	$r1=sprintf("%03s",$plaza);
	$r2=sprintf("%03s",rand(0,999));
	$r3=sprintf("%03s",rand(0,999));
	$r4=sprintf("%03s",rand(0,999));
	//$r5=sprintf("%04s",rand(0,9999));
	
	return $r1.$r2.$r3.$r4;
}

function guardaClave($plaza,$ticket){
	$clave = generar_clave($plaza);
	//mysql_query("INSERT claves_facturacion SET cve='$clave', plaza = '$plaza', ticket = '$ticket'") or die(mysql_error());
	while(!$res = mysql_query("INSERT claves_facturacion SET cve='$clave', plaza = '$plaza', ticket = '$ticket'")){
		$clave = generar_clave($plaza);
	}
}

function diferencia_tiempo($fechaf,$fechai){
	$resta = strtotime($fechaf) - strtotime($fechai);
	if($resta<0) return '';
	$horadif=intval($resta/3600);
	$minutosdif=intval(($resta-($horadif*3600))/60);
	$segundosdif=intval($resta-($horadif*3600)-($minutosdif*60));
	if($horadif<10) $horadif="0".$horadif;
	if($minutosdif<10) $minutosdif="0".$minutosdif;
	if($segundosdif<10) $segundosdif="0".$segundosdif;
	if($negativo) $horadif='-'.$horadif;
	return $horadif.':'.$minutosdif.':'.$segundosdif;
}

function numeroPlaca($placa){
	$numero = '';
	for($i=0;$i<strlen($placa);$i++){
		if($placa[$i]>='0' && $placa[$i]<='9'){
			$numero = $placa[$i];
		}
	}
	return $numero;
}

$array_estatus = array('A'=>'Activo','C'=>'Cancelado');
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		$res = mysql_query("SELECT * FROM usuarios WHERE cve='".$_POST['cveusuario']."'");
		$row = mysql_fetch_array($res);
		$permite_editar = $row['permite_editar'];
		//$permite_editar = ($_POST['cveusuario']==1) ? 1 : 0;
		$res = mysql_query("SELECT SUM(IF(estatus!='C',monto,0)),SUM(IF(estatus!='C' AND monto>0,1,0)),
		SUM(IF(estatus!='C' AND monto=0,1,0)),SUM(IF(estatus='C',1,0)) FROM cobro_engomado WHERE 
		plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND tipo_pago NOT IN (2,6)");
		$row = mysql_fetch_array($res);
		
		$res1 = mysql_query("SELECT SUM(devolucion),COUNT(cve) FROM devolucion_certificado WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'");
		$row1 = mysql_fetch_array($res1);
		
		$res2 = mysql_query("SELECT SUM(recuperacion),COUNT(cve) FROM recuperacion_certificado WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'");
		$row2 = mysql_fetch_array($res2);
		
		echo '<table><tr><th align="right">Subtotal</th><td align="right">&nbsp;'.number_format($row[0],2).'</td>
		<th align="right">&nbsp;&nbsp;Registros con Importe</th><td>&nbsp;'.intval($row[1]).'</td></tr>
		<tr><th align="right">Devoluciones</th><td align="right">&nbsp;'.number_format($row1[0],2).'</td>
		<th align="right">&nbsp;&nbsp;Registros en cero</th><td>&nbsp;'.intval($row[2]).'</td></tr>
		<tr><th align="right">Recuperacion</th><td align="right">&nbsp;'.number_format($row2[0],2).'</td>
		<th align="right">&nbsp;&nbsp;Cancelados</th><td>&nbsp;'.intval($row[3]).'</td></tr>
		<tr><th align="right">Gran Total</th><td align="right">&nbsp;'.number_format($row[0]+$row2[0]-$row1[0],2).'</td>
		<th align="right">&nbsp;&nbsp;Total Registro Ventas</th><td>&nbsp;'.intval($row[1]+$row[2]+$row[3]).'</td></tr>
		<tr><th align="right">&nbsp;</th><td align="right">&nbsp;</td>
		<th align="right">&nbsp;&nbsp;Devoluciones</th><td>&nbsp;'.$row1[1].'</td>
		<tr><th align="right">&nbsp;</th><td align="right">&nbsp;</td>
		<th align="right">&nbsp;&nbsp;Recuperaciones</th><td>&nbsp;'.$row2[1].'</td></tr></tr></table>';
		//if($Plaza['localidad_id']==2)
		//	echo '<br><font color="RED" style="font-size: 20px">A partir de este momento todo certificado pagado solo tendra un intento adicional, el segundo intento ya se tendra que pagar.<br>Instrucciones del Licenciado Miguel Espina</font>';
		echo '|*|';
		
		//Listado de plazas
		$select= " SELECT a.*,b.cve as certificado, b.certificado as holograma,b.engomado as engomado_entrega, CONCAT(b.fecha,' ',b.hora) as fechaentrega, TIMEDIFF(IFNULL(CONCAT(b.fecha,' ',b.hora),NOW()),CONCAT(a.fecha,' ',a.hora)) as diferencia FROM cobro_engomado a LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
		LEFT JOIN depositantes c ON c.cve = a.depositante AND c.plaza = a.plaza 
		WHERE a.plaza='".$_POST['plazausuario']."'";
		if ($_POST['folio']!=""){ 
			//$select.=" AND a.cve='".$_POST['cve']."' "; 
			$row = mysql_fetch_array(mysql_query("SELECT placa FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'"));
			$select.=" AND a.placa='".$row['placa']."' "; 
		}
		elseif ($_POST['placa']!=""){ 
			$select.=" AND a.placa='".$_POST['placa']."' "; 
			if ($_POST['tipo_venta']!="all") { $select.=" AND a.tipo_venta='".$_POST['tipo_venta']."' "; }
			if($_POST['mostrar']!='all'){ 
				if($_POST['mostrar']==1) $select.=" AND IFNULL(b.cve,0)>0";
				elseif($_POST['mostrar']==2) $select.=" AND IFNULL(b.cve,0)=0";
			}
		}
		else{
			$select.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
			if ($_POST['usuario']!="") { $select.=" AND a.usuario='".$_POST['usuario']."' "; }
			if ($_POST['engomado']!="") { $select.=" AND a.engomado='".$_POST['engomado']."' "; }
			if ($_POST['estatus']!="") { $select.=" AND a.estatus='".$_POST['estatus']."' "; }
			if ($_POST['tipo_pago']!="all") { $select.=" AND a.tipo_pago='".$_POST['tipo_pago']."' "; }
			if ($_POST['tipo_venta']!="all") { $select.=" AND a.tipo_venta='".$_POST['tipo_venta']."' "; }
			if ($_POST['multa']!="all") { $select.=" AND a.multa='".$_POST['multa']."' "; }
			if ($_POST['tipo_combustible']!="all") { $select.=" AND a.tipo_combustible='".$_POST['tipo_combustible']."' "; }
			if ($_POST['anio']!="all") { $select.=" AND a.anio='".$_POST['anio']."' "; }
			if($_POST['tipo_cliente']!='all') $select .= " AND IFNULL(c.agencia,-1)='".$_POST['tipo_cliente']."'";
			if($_POST['mostrar']!='all'){ 
				if($_POST['mostrar']==1) $select.=" AND IFNULL(b.cve,0)>0";
				elseif($_POST['mostrar']==2) $select.=" AND IFNULL(b.cve,0)=0";
			}
		}
		$select.=" ORDER BY a.cve DESC";
		if($_POST['btn']==0) $select.=" LIMIT 1";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Facturar<br>
			<input type="checkbox" onClick="if(this.checked) $(\'.chksfacturar\').attr(\'checked\',\'checked\'); else $(\'.chksfacturar\').removeAttr(\'checked\');"></th><th>Ticket</th><th>Fecha</th><!--<th>Referencia Maquina Registradora--></th>';
			if($_SESSION['TipoUsuario'] == 1) echo '<th>Fecha Entrega</th><th>Diferencia</th>';
			echo '<th>Placa</th>
			<th>Tiene Multa</th><th>Tipo de Certificado</th><th>Tipo Venta</th><th>Monto</th><th>A&ntilde;o Certificacion</th><th>Tipo de Pago</th><th>Depositante</th><th>Tipo Combustible</th><!--<th>Documento</th>--><th>Factura</th><th>Entrega Certificado</th><th>Holograma Entregado</th><th>Usuario</th><th>Motivo Cancelacion</th>';
			echo '<th>Motivo Intento</th><th>Observaciones</th></tr>';
			$t=0;
			$resultado = array();
			for($i=0;$row=mysql_fetch_array($res);$i++) {
				$resultado[$i] = $row;
			}
			foreach($resultado as $i => $row){
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
					$row['monto']=0;
				}
				else{
					if($row['fecha']==fechaLocal() || $_POST['cveusuario'] == 1)
						echo '<a href="#" onClick="atcr(\'cobro_engomado.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					if($_POST['cveusuario']==1 && $PlazaLocal != 1)
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'cobro_engomado.php\',\'\',\'1\','.$row['cve'].');"><img src="images/modificar.gif" border="0" title="Editar '.$row['cve'].'"></a>';
					if(nivelUsuario()>2 && $PlazaLocal != 1 && ($row['factura']==0 || $row['notacredito']>0) && $row['certificado']=="" && ($row['fecha']==fechaLocal() || $_POST['cveusuario']==1))
						echo '&nbsp;&nbsp;<a href="#" onClick="if(validarCancelacion('.$row['cve'].') == true && '.$_POST['cveusuario'].' != 1) alert(\'La placa tiene movimientos posteriores\'); else if(confirm(\'Esta seguro de cancelar el cobro\')) atcr(\'cobro_engomado.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				}	
				echo '</td>';
				if(($row['tipo_pago']==2 || $row['tipo_pago']==6 || $row['tipo_pago']==5) && $_POST['tipo_pago']=="all") $row['monto']=0;
				echo '<td align="center" width="40" nowrap>&nbsp;';
				if($row['estatus']!='C' && $row['tipo_pago']==1 && $row['factura']==0 && $row['certificado']>0 && date('Y')==substr($row['fecha'],0,4)){
					echo '<input type="checkbox" name="facturar[]" value="'.$row['cve'].'" monto="'.$row['monto'].'" class="chksfacturar">';
				}
				echo '&nbsp;</td>';
				if($_POST['folio']==$row['cve'])
					echo '<td align="center"><font color="BLUE">'.htmlentities($row['cve']).'</font></td>';
				else
					echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				//echo '<td align="center">'.htmlentities($row['referencia_maquina_registradora']).'</td>';
				/*if($_POST['cveusuario']==1)
				{
					$anterior = $resultado[$i+1]['fecha'].' '.$resultado[$i+1]['hora'];
					echo '<td align="center">'.diferencia_tiempo($row['fecha'].' '.$row['hora'], $anterior).'</td>';
				}*/
				if($_SESSION['TipoUsuario']==1){
					if($row['certificado']>0){
						echo '<td align="center">'.$row['fechaentrega'].'</td>';
						echo '<td align="center">'.$row['diferencia'].'</td>';
					}
					else{
						echo '<td>&nbsp;</td><td>&nbsp;</td>';
					}
				}
				if($row['estatus']=='C'){
					echo '<td align="center">'.htmlentities($row['placa']).'</td>';
				}
				elseif($permite_editar==1){
					echo '<td align="center"><input type="text" id="placa_'.$row['cve'].'" class="textField" size="23" value="'.$row['placa'].'"><br>
					<input type="button" value="Guardar" onClick="guardarPlaca('.$row['cve'].',\''.$row['placa'].'\')"></td>';
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
				echo '<td align="center">'.htmlentities($array_tipo_venta[$row['tipo_venta']]['nombre']);
				if($row['tipo_venta'] == 2) echo '<br>'.$row['autoriza'];
				echo '</td>';
				echo '<td align="center">'.number_format($row['monto'],2).'</td>';
				if($_POST['cveusuario']==1){
					echo '<td align="center"><select id="anio_'.$row['cve'].'">';
					foreach($array_anios as $k=>$v){
						echo '<option value="'.$k.'"';
						if($k==$row['anio']) echo ' selected';
						echo '>'.$v.'</option>';
					}
					echo '</select><br>
					<input type="button" value="Guardar" onClick="guardarAnio('.$row['cve'].', \''.$row['anio'].'\')"></td>';
				}
				else{
					echo '<td align="center">'.htmlentities($array_anios[$row['anio']]).'</td>';
				}
				if($permite_editar==1){
					echo '<td align="center"><select id="tipo_pago_'.$row['cve'].'">';
					foreach($array_tipo_pago as $k=>$v){
						echo '<option value="'.$k.'"';
						if($k==$row['tipo_pago']) echo ' selected';
						echo '>'.$v.'</option>';
					}
					echo '</select><br>
					<input type="button" value="Guardar" onClick="guardarTipoPago('.$row['cve'].', \''.$row['tipo_pago'].'\')"></td>';
				}
				else{
					echo '<td align="center">'.htmlentities($array_tipo_pago[$row['tipo_pago']]).'</td>';
				}
				if($permite_editar==1){
					echo '<td align="center"><select id="depositante_'.$row['cve'].'"><option value="0">Seleccione</option>';
					foreach($array_depositantes as $k=>$v){
						echo '<option value="'.$k.'"';
						if($k==$row['depositante']) echo ' selected';
						echo '>'.$v.'</option>';
					}
					echo '</select><br>
					<input type="button" value="Guardar" onClick="guardarDepositante('.$row['cve'].', \''.$row['depositante'].'\')"></td>';
				}
				else{
					echo '<td align="center">'.htmlentities(utf8_encode($array_depositantes[$row['depositante']])).'</td>';
				}
				echo '<td align="center">'.htmlentities($array_tipo_combustible[$row['tipo_combustible']]).'</td>';
				/*if($row['estatus']!='C' && nivelUsuario()>1){
					echo '<td align="center"><select onChange="cambiarDocumento('.$row['cve'].',this.value)">';
					foreach($array_documentos as $k=>$v){
						echo '<option value="'.$k.'"';
						if($k==$row['documento']) echo ' selected';
						echo '>'.$v.'</option>';
					}
					echo '</select></td>';
				}
				else{*/
					//echo '<td align="center">'.htmlentities($array_documentos[$row['documento']]).'</td>';
				/*}*/
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
				echo '<td align="center">'.htmlentities(utf8_encode($array_usuario[$row['usuario']])).'</td>';
				echo '<td>'.htmlentities(utf8_encode($row['obscan'])).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_motivos_intento[$row['motivo_intento']])).'</td>';
				echo '<td>'.htmlentities(utf8_encode($row['obs'])).'</td>';
				echo '</tr>';
				$t+=$row['monto'];
			}
			echo '	
				<tr>
				<td colspan="'.(($_SESSION['TipoUsuario']==1)?10:8).'" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</td>
				<td colspan="11" bgcolor="#E9F2F8">&nbsp;</td>
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
		exit();	
}	

if($_POST['ajax']==2){
	mysql_query("UPDATE documento SET documento='".$_POST['documento']."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['cvecobro']."'");
 	exit();
}

if($_POST['ajax']==3){
	$res = mysql_query("SELECT a.cve,a.modelo,a.engomado FROM call_citas a WHERE a.plaza='".$_POST['plazausuario']."' AND a.placa='".$_POST['placa']."' AND a.cvecobro=0 AND a.estatus!='C'");
	if($row=mysql_fetch_array($res)){
		echo $row['cve'].'|'.$row['modelo'].'|'.$row['engomado'].'|'.$array_engomadoprecio[$row['engomado']];
	}
	else{
		echo '|||';
	}
	exit();
}

if($_POST['ajax']==4){
	if($Plaza['localidad_id'] == 2){
		$res = mysql_query("SELECT monto FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND placa='".$_POST['placa']."' AND estatus!='C' ORDER BY cve DESC LIMIT 1");
		if($row=mysql_fetch_array($res)){
			if($row['monto'] == 0)
				echo '0';
			else
				echo '1';
		}
		else{
			echo '1';
		}
	}
	else{
		echo '1';
	}
	exit();
}

if($_POST['ajax']==5){
	$res = mysql_query("SELECT monto FROM cobro_engomado WHERE placa='".$_POST['placa']."' AND estatus!='C' AND tipo_venta IN (0,2)  AND DATEDIFF(CURDATE(),fecha)<65 ORDER BY cve DESC LIMIT 1");
	if($row=mysql_fetch_array($res)){
		echo '1';
	}
	else{
		echo '0';
	}
	exit();
}

if($_POST['ajax']==6){
	$res = mysql_query("SELECT intentos, tipo, agencia FROM depositantes WHERE cve = '".$_POST['depositante']."'");
	if($row = mysql_fetch_assoc($res)){
		$intentos = $row['intentos'];
		if($row['agencia'] == 1)
			$intentos=99;
		else
			$intentos=3;

	}
	else
		$intentos = 3;
	
	$res = mysql_query("SELECT cve FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND placa='".$_POST['placa']."' AND estatus!='C' AND tipo_venta IN (0,2)  AND DATEDIFF(CURDATE(), fecha)<=65 ORDER BY cve DESC LIMIT 1");
	if($row=mysql_fetch_array($res)){
		$res1=mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND placa='".$_POST['placa']."' AND estatus!='C' AND cve>'".$row['cve']."' AND tipo_venta=1");
		//echo mysql_num_rows($res1);
		if(mysql_num_rows($res1) <= $intentos)
			echo mysql_num_rows($res1);
		else 
			echo '-1';
	}
	else{
		$res1=mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND placa='".$_POST['placa']."' AND estatus!='C' AND tipo_venta=1");
		//echo mysql_num_rows($res1);
		if(mysql_num_rows($res1) <= $intentos)
			echo mysql_num_rows($res1);
		else 
			echo '-1';
	}
	exit();
}

if($_POST['ajax']==7){
	$res = mysql_query("SELECT cve FROM cobro_engomado WHERE placa='".$_POST['placa']."' AND estatus!='C' AND tipo_venta = 2 AND anio='".$_POST['anio']."'");
	if($row = mysql_fetch_assoc($res)){
		echo '2';
		exit();
	}
	if($_POST['depositante'] == 0){
		echo '1';
		exit();
	}
	$res = mysql_query("SELECT SUM(monto) FROM pagos_caja WHERE plaza='{$_POST['plazausuario']}' AND depositante='{$_POST['depositante']}' AND estatus!='C' AND YEAR(fecha)='".date('Y-m')."'");
	$row = mysql_fetch_array($res);
	$precios = array_values($array_engomadoprecio);
	$cortesias = intval($row[0]/$precios[1]/10);

	$res = mysql_query("SELECT COUNT(cve) FROM cobro_engomado WHERE plaza='{$_POST['plazausuario']}' AND depositante='{$_POST['depositante']}' AND estatus!='C' AND tipo_venta='2' AND YEAR(fecha)='".date('Y-m')."'");
	$row = mysql_fetch_array($res);

	if($cortesias > $row[0])
		echo '1';
	else
		echo '0';
	exit();
}

if($_POST['ajax']==8){
	$res = mysql_query("SELECT fecha, hora FROM cobro_engomado WHERE plaza = '".$_POST['plazausuario']."' AND cve = '".$_POST['id']."'");
	$row = mysql_fetch_assoc($res);
	if($row['fecha'] == ''){
		$row['fecha'] = date('Y-m-d');
		$row['hora'] = date('H:i:s');
	}
	if($intentoporcertificadodif == 1 && $_POST['depositante'] > 0){
		$res1 = mysql_query("SELECT intentos, tipo, agencia FROM depositantes WHERE cve = '".$_POST['depositante']."'");
		$row1 = mysql_fetch_assoc($res1);
		if($row1['agencia'] == 1){
			echo '0';
			exit();
		}
		else{
			$res = mysql_query("SELECT b.engomado FROM cobro_engomado a LEFT JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket AND b.estatus!='C'
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.placa='".$_POST['placa']."' AND a.tipo_venta!=3 AND a.estatus!='C' AND a.anio='".$_POST['anio']."' AND CONCAT(a.fecha,' ',a.hora)<'".$row['fecha']." ".$row['hora']."' ORDER BY a.fecha DESC, a.hora DESC LIMIT 1");
			$row = mysql_fetch_array($res);
			if($row['engomado'] != 19)
				echo '1';
			else 
				echo '0';
		}
	}
	else{
		$res = mysql_query("SELECT b.engomado FROM cobro_engomado a LEFT JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket AND b.estatus!='C'
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.placa='".$_POST['placa']."' AND a.tipo_venta!=3 AND a.estatus!='C' AND a.anio='".$_POST['anio']."' AND CONCAT(a.fecha,' ',a.hora)<'".$row['fecha']." ".$row['hora']."' ORDER BY a.fecha DESC, a.hora DESC LIMIT 1");
		$row = mysql_fetch_array($res);
		if($row['engomado'] != 19)
			echo '1';
		else 
			echo '0';
	}
	exit();
}

if($_POST['ajax']==9){
	$res = mysql_query("SELECT cve FROM cobro_engomado WHERE placa='".$_POST['placa']."' AND anio='".$_POST['anio']."' AND engomado=3");
	if($row = mysql_fetch_assoc($res))
		echo '1';
	exit();
}

if($_POST['ajax']==11){
	$numero = numeroPlaca($_POST['placa']);
	$dia = date('w');
	if($dia == 1 && ($numero == 5 || $numero == 6))
		echo '1';
	elseif($dia == 2 && ($numero == 7 || $numero == 8))
		echo '1';
	elseif($dia == 3 && ($numero == 3 || $numero == 4))
		echo '1';
	elseif($dia == 4 && ($numero == 1 || $numero == 2))
		echo '1';
	elseif($dia == 5 && ($numero == 9 || $numero == 0))
		echo '1';
	exit();
}

if($_POST['ajax']==12){
	$res = mysql_query("SELECT * FROM depositantes WHERE cve='".$_POST['depositante']."'");
	$row = mysql_fetch_array($res);
	if($row['agencia']!=1){
		$saldo = saldo_depositante($row['cve']);
		if($saldo<$_POST['monto']);
			echo '1';
	}

	exit();
}

if($_POST['ajax']==13){
	$res = mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND TRIM(placa)='".trim($_POST['placa'])."' AND anio='".$_POST['anio']."' AND tipo_venta IN (0,2)");
	if($row = mysql_fetch_array($res))
		echo '1';

	exit();
}

if($_POST['ajax']==30){
	$res = mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['cvecobro']."'");
	$row = mysql_fetch_array($res);
	$res1=mysql_query("SELECT fecha FROM cobro_engomado WHERE placa='".$row['placa']."' AND estatus!='C' AND fecha>'".$row['fecha']."' ORDER BY fecha LIMIT 1");
	if($row1=mysql_fetch_array($res1)){
		echo '1';
	}
	else{
		echo '0';
	}
	exit();
	if($row['tipo_venta']==0){
		$res1=mysql_query("SELECT fecha FROM cobro_engomado WHERE placa='".$row['placa']."' AND estatus!='C' AND fecha>'".$row['fecha']."' AND tipo_venta=0 ORDER BY fecha LIMIT 1");
		if($row1=mysql_fetch_array($res1)){
			$res2=mysql_query("SELECT * FROM cobro_engomado WHERE placa='".$row['placa']."' AND estatus!='C' AND fecha>='".$row['fecha']."' AND fecha<'".$row1['fecha']."' AND tipo_venta=1");
		}
		else{
			$res2=mysql_query("SELECT * FROM cobro_engomado WHERE placa='".$row['placa']."' AND estatus!='C' AND fecha>='".$row['fecha']."' AND tipo_venta=1");
		}
		if(mysql_num_rows($res2)==0)
			echo '0';
		else
			echo '1';
	}
	else{
		echo '0';
	}
	exit();
}

if($_GET['ajax']==50){
	$select= " SELECT placa FROM cobro_engomado WHERE plaza='".$_GET['plazausuario']."'";
	$select.=" AND trim(placa) LIKE '".trim($_GET['term'])."%'";
	$select .= " GROUP BY placa ORDER BY placa";
	$res=mysql_query($select) or die(mysql_error());
	  $matches = array();
	  while($row=mysql_fetch_assoc($res)){
	    // Adding the necessary "value" and "label" fields and appending to result set
	    $row['value'] = $row['placa'];
	    $row['label'] = $row['placa'];
	    $matches[] = $row;
	  } 
	  // Truncate, encode and return the results
  	$matches = array_slice($matches, 0, 15);
  	print json_encode($matches);
 	exit();
}


if($_POST['cmd']==103.9){
	$array_forma_pago = array(1=>"Efectivo",2=>"Deposito Bancario",3=>"Cheque",4=>"Transferencia");
	$resPlaza = mysql_query("SELECT nombre FROM plazas WHERE cve='".$_POST['plazausuario']."'");
	$rowPlaza = mysql_fetch_array($resPlaza);
	echo '<h2>'.$array_plaza[$_POST['plazausuario']].'<br>'.$rowPlaza['nombre'].'<br>CORTE VENTA CERTIFICADO<br>'.fechaLocal().' '.horaLocal();
	if($_POST['fecha_ini']==$_POST['fecha_fin']) echo "<br>FECHA: ".$_POST['fecha_ini'];
	else echo "<br>FECHA INICIO: ".$_POST['fecha_ini']."<br>FECHA FIN: ".$_POST['fecha_fin'];
	if ($_POST['usuario']!=""){ 
		$filtro.=" AND usuario='".$_POST['usuario']."' "; 
		echo '<br>USUARIO: '.$array_usuario[$_POST['usuario']];
	}
	$texto.='<br><br>';
	$t1=$t2=$t3=$t4=0;
	
	$res=mysql_query("SELECT engomado,SUM(IF(tipo_pago!=6,monto,0)),COUNT(cve),SUM(IF(tipo_pago IN (2),monto,0)),SUM(IF(tipo_pago IN (2),1,0)) FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' ".$filtro." GROUP BY engomado");
	while($row=mysql_fetch_array($res)){
		echo '<h2>'.$array_engomado[$row['engomado']].'</h2>';
		echo '<table><tr><th>Tipo Pago</th><th>Cantidad</th><th>Importe</th></tr>';
		$res1=mysql_query("SELECT tipo_pago,SUM(monto),COUNT(cve)  FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND engomado='".$row['engomado']."' AND estatus!='C' ".$filtro." GROUP BY tipo_pago");
		while($row1=mysql_fetch_array($res1)){
			echo '<tr><td>'.$array_tipo_pago[$row1['tipo_pago']].'</td><td>'.$row1[2].'</td><td>'.number_format($row1[1],2).'</td></tr>';
		}
		echo '<tr><td>TOTAL CONTADO</td><td>'.($row[2]-$row[4]).'</td><td>'.number_format($row[1]-$row[3],2).'</td></tr>';
		echo '<tr><td>TOTAL CREDITO</td><td>'.$row[4].'</td><td>'.number_format($row[3],2).'</td></tr>';
		echo '<tr><td>TOTAL</td><td>'.$row[2].'</td><td>'.number_format($row[1],2).'</td></tr></table>';
		$t1+=$row[2];
		$t2+=$row[1];
		$t3+=$row[4];
		$t4+=$row[3];
	}
	echo '<br>GRAN TOTAL VENTA CONTADO CANT: '.($t1-$t3).', IMP: '.number_format($t2-$t4,2).'';
	echo '<br>GRAN TOTAL VENTA CREDITO CANT: '.$t3.', IMP: '.number_format($t4,2).'';
	echo '<br>GRAN TOTAL VENTA CANT: '.$t1.', IMP: '.number_format($t2,2).'';
	
	echo '<h2>DEVOLUCION</h2>';
	$res1=mysql_query("SELECT SUM(devolucion),COUNT(cve)  FROM devolucion_certificado WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro");
	$row1=mysql_fetch_array($res1);
	echo ' CANT: '.$row1[1].', IMP: '.number_format($row1[0],2).'';
	
	echo '<h2>RECUPERACION</h2>';
	$res2=mysql_query("SELECT SUM(recuperacion),COUNT(cve)  FROM recuperacion_certificado WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro");
	$row2=mysql_fetch_array($res2);
	echo ' CANT: '.$row2[1].', IMP: '.number_format($row2[0],2).'';
	
	echo '<h2>PAGOS EN CAJA</h2>';
	echo '<table><tr><th>Tipo Pago</th><th>Cantidad</th><th>Importe</th></tr>';
	$t31=$t32=0;
	$res3=mysql_query("SELECT forma_pago,SUM(monto),COUNT(cve)  FROM pagos_caja WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro GROUP BY forma_pago");
	while($row3=mysql_fetch_array($res3)){
		echo '<tr><td>'.$array_forma_pago[$row3['tipo_pago']].'</td><td>'.$row3[2].'</td><td>'.number_format($row3[1],2).'</td></tr>';
		$t31+=$row3[1];
		$t32+=$row3[2];
	}
	echo '<tr><td>TOTAL</td><td>'.$t32.'</td><td>'.number_format($t31,2).'</td></tr></table>';

	echo '<h2>BONOS</h2>';
	$res5=mysql_query("SELECT SUM(monto),COUNT(cve)  FROM bonos WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro");
	$row5=mysql_fetch_array($res5);
	echo ' CANT: '.$row5[1].', IMP: '.number_format($row5[0],2).'';
	
	echo '<br><br>GRAN TOTAL IMP: '.number_format($t2+$row2[0]-$row1[0]+$t31-$row5[0],2).'';
	echo '<br>TOTAL A DEPOSITAR IMP: '.number_format($t2-$t4+$row2[0]-$row1[0]+$t31-$row5[0],2).'';
	exit();
}

if($_POST['cmd']==102.9){
	$array_forma_pago = array(1=>"Efectivo",2=>"Deposito Bancario",3=>"Cheque",4=>"Transferencia");
	$texto=chr(27)."@";
	$texto.='|';
	$resPlaza = mysql_query("SELECT nombre FROM plazas WHERE cve='".$_POST['plazausuario']."'");
	$rowPlaza = mysql_fetch_array($resPlaza);
	$texto.=chr(27).'!'.chr(8)." ".$array_plaza[$_POST['plazausuario']]."|".$rowPlaza['nombre']."|| CORTE VENTA CERTIFICADO";
	$texto.='|'.fechaLocal().' '.horaLocal().'|';
	if($_POST['fecha_ini']==$_POST['fecha_fin']) $texto.=" FECHA: |".$_POST['fecha_ini'];
	else $texto.=" FECHA INI: |".$_POST['fecha_ini']."|FECHA FIN: |".$_POST['fecha_fin'];
	$filtro="";
	if ($_POST['usuario']!=""){ 
		$filtro.=" AND usuario='".$_POST['usuario']."' "; 
		$texto.='|USUARIO: '.$array_usuario[$_POST['usuario']];
	}
	$texto.='|| INGRESOS||';
	$t1=$t2=$t3=$t4=0;
	$res=mysql_query("SELECT engomado,SUM(IF(tipo_pago!=6,monto,0)),COUNT(cve),SUM(IF(tipo_pago IN (2),monto,0)),SUM(IF(tipo_pago IN (2),1,0)) FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro GROUP BY engomado");
	while($row=mysql_fetch_array($res)){
		/*$texto.=" ".$array_engomado[$row['engomado']].'|';
		$res1=mysql_query("SELECT tipo_pago,SUM(monto),COUNT(cve)  FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND engomado='".$row['engomado']."' AND estatus!='C' $filtro GROUP BY tipo_pago");
		while($row1=mysql_fetch_array($res1)){
			$texto.=" ".$array_tipo_pago[$row1['tipo_pago']].' CANT: '.$row1[2].', IMP: '.number_format($row1[1],2).'|';
		}
		$texto.=' TOTAL CONT CANT: '.($row[2]-$row[4]).', IMP: '.number_format($row[1]-$row[3],2).'|';
		$texto.=' TOTAL CRED CANT: '.$row[4].', IMP: '.number_format($row[3],2).'|';
		$texto.=' TOTAL      CANT: '.$row[2].', IMP: '.number_format($row[1],2).'||';*/
		$t1+=$row[2];
		$t2+=$row[1];
		$t3+=$row[4];
		$t4+=$row[3];
	}
	$texto.=' VENTA  CANT: '.($t1-$t3).', IMP: '.number_format($t2-$t4,2).'|';
	//$texto.=' GRAN TOTAL VENTA CRED CANT: '.$t3.', IMP: '.number_format($t4,2).'|';
	//$texto.=' GRAN TOTAL VENTA      CANT: '.$t1.', IMP: '.number_format($t2,2).'||';
	
	
	$texto.=' RECUPERACION ';
	$res2=mysql_query("SELECT SUM(recuperacion),COUNT(cve)  FROM recuperacion_certificado WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro");
	$row2=mysql_fetch_array($res2);
	$texto.=' CANT: '.$row2[1].', IMP: '.number_format($row2[0],2).'|';
	
	$texto.=' PAGOS EN CAJA ';
	$t31=$t32=0;
	$res3=mysql_query("SELECT forma_pago,SUM(monto),COUNT(cve)  FROM pagos_caja WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro GROUP BY forma_pago");
	while($row3=mysql_fetch_array($res3)){
		$texto.=" ".$array_forma_pago[$row3['forma_pago']].' CANT: '.$row3[2].', IMP: '.number_format($row3[1],2).'|';
		$t31+=$row3[1];
		$t32+=$row3[2];
	}
	$texto.=' TOTAL  CANT: '.$t32.', IMP: '.number_format($t31,2).'||';
	
	$texto.='EGRESOS || DEVOLUCION ';
	$res1=mysql_query("SELECT SUM(devolucion),COUNT(cve)  FROM devolucion_certificado WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro");
	$row1=mysql_fetch_array($res1);
	$texto.=' CANT: '.$row1[1].', IMP: '.number_format($row1[0],2).'|BONOS ';
	$res5=mysql_query("SELECT SUM(monto),COUNT(cve)  FROM bonos WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro");
	$row5=mysql_fetch_array($res5);
	$texto.=' CANT: '.$row5[1].', IMP: '.number_format($row5[0],2).'||';
	
	$texto.=' GRAN TOTAL IMP: '.number_format($t2+$row2[0]-$row1[0]+$t31-$row5[0],2).'|';
	$texto.=' TOTAL A DEPOSITAR IMP: '.number_format($t2-$t4+$row2[0]-$row1[0]+$t31-$row5[0],2).'||';
	
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$texto.'&logo='.str_replace(' ','',$array_plaza[$_POST['plazausuario']]).'" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",2000);</script>';
	exit();
}

if($_POST['cmd']==103){
	$array_forma_pago = array(1=>"Efectivo",2=>"Deposito Bancario",3=>"Cheque",4=>"Transferencia");
	$resPlaza = mysql_query("SELECT nombre FROM plazas WHERE cve='".$_POST['plazausuario']."'");
	$rowPlaza = mysql_fetch_array($resPlaza);
	echo '<h2>'.$array_plaza[$_POST['plazausuario']].'<br>'.$rowPlaza['nombre'].'<br>CORTE VENTA CERTIFICADO<br>'.fechaLocal().' '.horaLocal();
	if($_POST['fecha_ini']==$_POST['fecha_fin']) echo "<br>FECHA: ".$_POST['fecha_ini'];
	else echo "<br>FECHA INICIO: ".$_POST['fecha_ini']."<br>FECHA FIN: ".$_POST['fecha_fin'];
	if ($_POST['usuario']!=""){ 
		$filtro.=" AND a.usuario='".$_POST['usuario']."' "; 
		echo '<br>USUARIO: '.$array_usuario[$_POST['usuario']];
	}
	$texto.='<br><br>';
	$t1=$t2=$t3=$t4=$t5=$t6=0;
	
	$res=mysql_query("SELECT engomado,
		SUM(IF(tipo_pago!=6,monto,0)),
		COUNT(cve),
		SUM(IF(tipo_pago IN (2),monto,0)),
		SUM(IF(tipo_pago IN (2),1,0)),
		SUM(IF(tipo_pago IN (5),monto,0)),
		SUM(IF(tipo_pago IN (5),1,0)) 
	FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' ".$filtro." AND tipo_venta!=3 GROUP BY engomado");
	while($row=mysql_fetch_array($res)){
		echo '<h2>'.$array_engomado[$row['engomado']].'</h2>';
		echo '<table><tr><th>Tipo Pago</th><th>Cantidad</th><th>Importe</th></tr>';
		$res1=mysql_query("SELECT tipo_pago,SUM(monto),COUNT(cve)  FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND engomado='".$row['engomado']."' AND tipo_venta!=3 AND estatus!='C' ".$filtro." GROUP BY tipo_pago");
		while($row1=mysql_fetch_array($res1)){
			echo '<tr><td>'.$array_tipo_pago[$row1['tipo_pago']].'</td><td>'.$row1[2].'</td><td>'.number_format($row1[1],2).'</td></tr>';
		}
		echo '<tr><td>TOTAL CONTADO</td><td>'.($row[2]-$row[4]-$row[6]).'</td><td>'.number_format($row[1]-$row[3]-$row[5],2).'</td></tr>';
		echo '<tr><td>TOTAL CREDITO</td><td>'.$row[4].'</td><td>'.number_format($row[3],2).'</td></tr>';
		echo '<tr><td>TOTAL T. CREDITO</td><td>'.$row[6].'</td><td>'.number_format($row[5],2).'</td></tr>';
		echo '<tr><td>TOTAL</td><td>'.$row[2].'</td><td>'.number_format($row[1],2).'</td></tr></table>';
		$t1+=$row[2];
		$t2+=$row[1];
		$t3+=$row[4];
		$t4+=$row[3];
		$t5+=$row[6];
		$t6+=$row[5];
	}
	$res=mysql_query("SELECT engomado,
		SUM(IF(tipo_pago!=6,monto,0)),
		COUNT(cve),
		SUM(IF(tipo_pago IN (2),monto,0)),
		SUM(IF(tipo_pago IN (2),1,0)),
		SUM(IF(tipo_pago IN (5),monto,0)),
		SUM(IF(tipo_pago IN (5),1,0)) 
	FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' ".$filtro." AND tipo_venta=3");
	while($row=mysql_fetch_array($res)){
		echo '<h2>REPOSICION</h2>';
		echo '<table><tr><th>Tipo Pago</th><th>Cantidad</th><th>Importe</th></tr>';
		$res1=mysql_query("SELECT tipo_pago,SUM(monto),COUNT(cve)  FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' ".$filtro." AND tipo_venta=3 GROUP BY tipo_pago");
		while($row1=mysql_fetch_array($res1)){
			echo '<tr><td>'.$array_tipo_pago[$row1['tipo_pago']].'</td><td>'.$row1[2].'</td><td>'.number_format($row1[1],2).'</td></tr>';
		}
		echo '<tr><td>TOTAL REPOSICION CONTADO</td><td>'.($row[2]-$row[4]-$row[6]).'</td><td>'.number_format($row[1]-$row[3]-$row[5],2).'</td></tr>';
		echo '<tr><td>TOTAL REPOSICION CREDITO</td><td>'.$row[4].'</td><td>'.number_format($row[3],2).'</td></tr>';
		echo '<tr><td>TOTAL REPOSICION T. CREDITO</td><td>'.$row[6].'</td><td>'.number_format($row[5],2).'</td></tr>';
		echo '<tr><td>TOTAL REPOSICION</td><td>'.$row[2].'</td><td>'.number_format($row[1],2).'</td></tr></table>';
		$t1+=$row[2];
		$t2+=$row[1];
		$t3+=$row[4];
		$t4+=$row[3];
		$t5+=$row[6];
		$t6+=$row[5];
	}
	echo '<br>GRAN TOTAL VENTA CONTADO CANT: '.($t1-$t3-$t5).', IMP: '.number_format($t2-$t4-$t6,2).'';
	echo '<br>GRAN TOTAL VENTA CREDITO CANT: '.$t3.', IMP: '.number_format($t4,2).'';
	echo '<br>GRAN TOTAL VENTA T. CREDITO CANT: '.$t5.', IMP: '.number_format($t6,2).'';
	echo '<br>GRAN TOTAL VENTA CANT: '.$t1.', IMP: '.number_format($t2,2).'';
	
	echo '<h2>DEVOLUCION</h2>';
	$res1=mysql_query("SELECT SUM(a.devolucion),COUNT(a.cve)  FROM devolucion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' $filtro");
	$row1=mysql_fetch_array($res1);
	echo ' CANT: '.$row1[1].', IMP: '.number_format($row1[0],2).'';
	
	echo '<h2>RECUPERACION</h2>';
	$res2=mysql_query("SELECT SUM(a.recuperacion),COUNT(a.cve)  FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' $filtro");
	$row2=mysql_fetch_array($res2);
	echo ' CANT: '.$row2[1].', IMP: '.number_format($row2[0],2).'';
	
	echo '<h2>PAGOS EN CAJA</h2>';
	echo '<table><tr><th>Tipo Pago</th><th>Cantidad</th><th>Importe</th></tr>';
	$t31=$t32=$t33=0;
	$res3=mysql_query("SELECT forma_pago,SUM(monto),COUNT(cve)  FROM pagos_caja a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro GROUP BY forma_pago");
	while($row3=mysql_fetch_array($res3)){
		echo '<tr><td>'.$array_forma_pago[$row3['forma_pago']].'</td><td>'.$row3[2].'</td><td>'.number_format($row3[1],2).'</td></tr>';
		$t31+=$row3[1];
		$t32+=$row3[2];
		if($row3['forma_pago'] == 1 || $row3['forma_pago'] == 3) $t33+=$row3[1];
	}
	echo '<tr><td>TOTAL</td><td>'.$t32.'</td><td>'.number_format($t31,2).'</td></tr></table>';

	echo '<h2>DEVOLUCION AJUSTE</h2>';
	$res4=mysql_query("SELECT SUM(monto),COUNT(cve)  FROM devolucion_ajuste a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro");
	$row4=mysql_fetch_array($res4);
	echo ' CANT: '.$row4[1].', IMP: '.number_format($row4[0],2).'';

	echo '<h2>BONOS</h2>';
	$res5=mysql_query("SELECT SUM(monto),COUNT(cve)  FROM bonos a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro");
	$row5=mysql_fetch_array($res5);
	echo ' CANT: '.$row5[1].', IMP: '.number_format($row5[0],2).'';
	
	echo '<br><br>GRAN TOTAL IMP: '.number_format($t2+$row2[0]-$row1[0]+$t31-$row4[0]-$row5[0],2).'';
	echo '<br>TOTAL A DEPOSITAR IMP: '.number_format($t2-$t4-$t6+$row2[0]-$row1[0]+$t33-$row4[0]-$row5[0],2).'';
	exit();
}

if($_POST['cmd']==102){
	$array_forma_pago = array(1=>"Efectivo",2=>"Deposito Bancario",3=>"Cheque",4=>"Transferencia");
	$texto=chr(27)."@";
	$texto.='|';
	$resPlaza = mysql_query("SELECT nombre FROM plazas WHERE cve='".$_POST['plazausuario']."'");
	$rowPlaza = mysql_fetch_array($resPlaza);
	$texto.=chr(27).'!'.chr(8)." ".$array_plaza[$_POST['plazausuario']]."|".$rowPlaza['nombre']."|| CORTE VENTA CERTIFICADO";
	$texto.='|'.fechaLocal().' '.horaLocal().'|';
	if($_POST['fecha_ini']==$_POST['fecha_fin']) $texto.=" FECHA: |".$_POST['fecha_ini'];
	else $texto.=" FECHA INI: |".$_POST['fecha_ini']."|FECHA FIN: |".$_POST['fecha_fin'];
	$filtro="";
	if ($_POST['usuario']!=""){ 
		$filtro.=" AND a.usuario='".$_POST['usuario']."' "; 
		$texto.='|USUARIO: '.$array_usuario[$_POST['usuario']];
	}
	$texto.='|| INGRESOS||';
	$t1=$t2=$t3=$t4=$t5=$t6=0;
	$res=mysql_query("SELECT engomado,SUM(IF(tipo_pago!=6,monto,0)),COUNT(cve),SUM(IF(tipo_pago IN (2),monto,0)),SUM(IF(tipo_pago IN (2),1,0)),SUM(IF(tipo_pago IN (5),monto,0)),SUM(IF(tipo_pago IN (5),1,0)) FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' ".$filtro." GROUP BY engomado");
	while($row=mysql_fetch_array($res)){
		/*$texto.=" ".$array_engomado[$row['engomado']].'|';
		$res1=mysql_query("SELECT tipo_pago,SUM(monto),COUNT(cve)  FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND engomado='".$row['engomado']."' AND estatus!='C' $filtro GROUP BY tipo_pago");
		while($row1=mysql_fetch_array($res1)){
			$texto.=" ".$array_tipo_pago[$row1['tipo_pago']].' CANT: '.$row1[2].', IMP: '.number_format($row1[1],2).'|';
		}
		$texto.=' TOTAL CONT CANT: '.($row[2]-$row[4]).', IMP: '.number_format($row[1]-$row[3],2).'|';
		$texto.=' TOTAL CRED CANT: '.$row[4].', IMP: '.number_format($row[3],2).'|';
		$texto.=' TOTAL      CANT: '.$row[2].', IMP: '.number_format($row[1],2).'||';*/
		$t1+=$row[2];
		$t2+=$row[1];
		$t3+=$row[4];
		$t4+=$row[3];
		$t5+=$row[6];
		$t6+=$row[5];
	}
	$texto.=' VENTA  CANT: '.($t1-$t3-$t5).', IMP: '.number_format($t2-$t4-$t6,2).'|';
	//$texto.=' GRAN TOTAL VENTA CRED CANT: '.$t3.', IMP: '.number_format($t4,2).'|';
	//$texto.=' GRAN TOTAL VENTA      CANT: '.$t1.', IMP: '.number_format($t2,2).'||';
	
	
	$texto.=' RECUPERACION ';
	$res2=mysql_query("SELECT SUM(a.recuperacion),COUNT(a.cve)  FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' $filtro");
	$row2=mysql_fetch_array($res2);
	$texto.=' CANT: '.$row2[1].', IMP: '.number_format($row2[0],2).'|';
	
	$texto.=' PAGOS EN CAJA ';
	$t31=$t32=$t33=0;
	$res3=mysql_query("SELECT forma_pago,SUM(monto),COUNT(cve)  FROM pagos_caja a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro GROUP BY forma_pago");
	while($row3=mysql_fetch_array($res3)){
		$texto.=" ".$array_forma_pago[$row3['forma_pago']].' CANT: '.$row3[2].', IMP: '.number_format($row3[1],2).'|';
		$t31+=$row3[1];
		$t32+=$row3[2];
		if($row3['forma_pago'] == 1 || $row3['forma_pago'] == 3) $t33+=$row3[1];
	}
	$texto.=' TOTAL  CANT: '.$t32.', IMP: '.number_format($t31,2).'||';
	
	$texto.='EGRESOS || DEVOLUCION ';
	$res1=mysql_query("SELECT SUM(a.devolucion),COUNT(a.cve)  FROM devolucion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' $filtro");
	$row1=mysql_fetch_array($res1);
	$texto.=' CANT: '.$row1[1].', IMP: '.number_format($row1[0],2).'|';
	$texto.='DEVOLUCION AJUSTE';
	$res4=mysql_query("SELECT SUM(monto),COUNT(cve)  FROM devolucion_ajuste a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro");
	$row4=mysql_fetch_array($res4);
	$texto.=' CANT: '.$row4[1].', IMP: '.number_format($row4[0],2).'|';
	$texto.='DEVOLUCION AJUSTE';
	$res5=mysql_query("SELECT SUM(monto),COUNT(cve)  FROM bonos a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro");
	$row5=mysql_fetch_array($res4);
	$texto.=' CANT: '.$row5[1].', IMP: '.number_format($row5[0],2).'||';
	
	$texto.=' GRAN TOTAL IMP: '.number_format($t2+$row2[0]-$row1[0]+$t31-$row4[0]-$row5[0],2).'|';
	$texto.=' TOTAL A DEPOSITAR IMP: '.number_format($t2-$t4-$t6+$row2[0]-$row1[0]+$t33-$row4[0]-$row5[0],2).'||';
	
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$texto.'&logo='.str_replace(' ','',$array_plaza[$_POST['plazausuario']]).'" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",2000);</script>';
	exit();
}

if($_POST['cmd']==102.1){
	$texto=chr(27)."@";
	$texto.='|';
	$resPlaza = mysql_query("SELECT nombre FROM plazas WHERE cve='".$_POST['plazausuario']."'");
	$rowPlaza = mysql_fetch_array($resPlaza);
	$texto.=chr(27).'!'.chr(8)." ".$array_plaza[$_POST['plazausuario']]."|".$rowPlaza['nombre']."|| CORTE VENTA CERTIFICADO";
	$texto.='|'.fechaLocal().' '.horaLocal().'|';
	if($_POST['fecha_ini']==$_POST['fecha_fin']) $texto.=" FECHA: |".$_POST['fecha_ini'];
	else $texto.=" FECHA INI: |".$_POST['fecha_ini']."|FECHA FIN: |".$_POST['fecha_fin'];
	$filtro="";
	if ($_POST['usuario']!=""){ 
		$filtro.=" AND a.usuario='".$_POST['usuario']."' "; 
		$texto.='|USUARIO: '.$array_usuario[$_POST['usuario']];
	}
	$texto.='||';
	$t1=$t2=$t3=$t4=$t5=$t6=0;
	$res=mysql_query("SELECT engomado,SUM(IF(tipo_pago!=6,monto,0)),COUNT(cve),SUM(IF(tipo_pago IN (2),monto,0)),SUM(IF(tipo_pago IN (2),1,0)),SUM(IF(tipo_pago IN (5),monto,0)),SUM(IF(tipo_pago IN (5),1,0)) FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' ".$filtro." GROUP BY engomado");
	while($row=mysql_fetch_array($res)){
		$texto.=" ".$array_engomado[$row['engomado']].'|';
		$res1=mysql_query("SELECT tipo_pago,SUM(monto),COUNT(cve)  FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND engomado='".$row['engomado']."' AND estatus!='C' $filtro GROUP BY tipo_pago");
		while($row1=mysql_fetch_array($res1)){
			$texto.=" ".$array_tipo_pago[$row1['tipo_pago']].' CANT: '.$row1[2].', IMP: '.number_format($row1[1],2).'|';
		}
		$texto.=' TOTAL   CONT CANT: '.($row[2]-$row[4]-$row[6]).', IMP: '.number_format($row[1]-$row[3]-$row[5],2).'|';
		$texto.=' TOTAL   CRED CANT: '.$row[4].', IMP: '.number_format($row[3],2).'|';
		$texto.=' TOTAL T.CRED CANT: '.$row[6].', IMP: '.number_format($row[5],2).'|';
		$texto.=' TOTAL        CANT: '.$row[2].', IMP: '.number_format($row[1],2).'||';
		$t1+=$row[2];
		$t2+=$row[1];
		$t3+=$row[4];
		$t4+=$row[3];
		$t5+=$row[6];
		$t6+=$row[5];
	}
	$texto.=' GRAN TOTAL VENTA   CONT CANT: '.($t1-$t3-$t5).', IMP: '.number_format($t2-$t4-$t6,2).'|';
	$texto.=' GRAN TOTAL VENTA   CRED CANT: '.$t3.', IMP: '.number_format($t4,2).'|';
	$texto.=' GRAN TOTAL VENTA T.CRED CANT: '.$t5.', IMP: '.number_format($t6,2).'|';
	$texto.=' GRAN TOTAL VENTA        CANT: '.$t1.', IMP: '.number_format($t2,2).'||';
	$texto.=' DEVOLUCION ';
	$res1=mysql_query("SELECT SUM(a.devolucion),COUNT(a.cve)  FROM devolucion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' $filtro");
	$row1=mysql_fetch_array($res1);
	$texto.=' CANT: '.$row1[1].', IMP: '.number_format($row1[0],2).'||';
	
	$texto.=' RECUPERACION ';
	$res2=mysql_query("SELECT SUM(a.recuperacion),COUNT(a.cve)  FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' $filtro");
	$row2=mysql_fetch_array($res1);
	$texto.=' CANT: '.$row2[1].', IMP: '.number_format($row2[0],2).'||';
	
	$texto.=' PAGOS EN CAJA ';
	//echo '<table><tr><th>Tipo Pago</th><th>Cantidad</th><th>Importe</th></tr>';
	$t31=$t32=$t33=0;
	$res3=mysql_query("SELECT tipo_pago,SUM(monto),COUNT(cve)  FROM pagos_caja a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro GROUP BY tipo_pago");
	while($row3=mysql_fetch_array($res3)){
		$texto.=" ".$array_tipo_pago[$row3['tipo_pago']].' CANT: '.$row3[2].', IMP: '.number_format($row3[1],2).'|';
		$t31+=$row3[1];
		$t32+=$row3[2];
		if($row3['forma_pago'] == 1 || $row3['forma_pago'] == 3) $t33+=$row3[1];
	}
	$texto.=' GRAN TOTAL       CANT: '.$t32.', IMP: '.number_format($t31,2).'||';
	
	$texto.=' GRAN TOTAL IMP: '.number_format($t2+$row2[0]-$row1[0]+$t31,2).'|';
	$texto.=' TOTAL A DEPOSITAR IMP: '.number_format($t2-$t4-$t6+$row2[0]-$row1[0]+$t33,2).'||';
	
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$texto.'&logo='.str_replace(' ','',$array_plaza[$_POST['plazausuario']]).'" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",2000);</script>';
	exit();
}

if($_POST['cmd']==101){
	require_once("numlet.php");
	$res=mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	$texto=chr(27)."@";
	$texto.='|';
	$resPlaza = mysql_query("SELECT nombre FROM plazas WHERE cve='".$row['plaza']."'");
	$rowPlaza = mysql_fetch_array($resPlaza);
	$resPlaza2 = mysql_query("SELECT rfc FROM datosempresas WHERE plaza='".$row['plaza']."'");
	$rowPlaza2 = mysql_fetch_array($resPlaza2);
	$texto.=chr(27).'!'.chr(30)." ".$array_plaza[$row['plaza']]."|".$rowPlaza['nombre'];
	$texto.='| RFC: '.$rowPlaza2['rfc'];
	$texto.='||';
	$texto.=chr(27).'!'.chr(8)." TICKET: ".$row['cve'];
	$texto.='|';
	if($row['tipo_pago'] != 2 && $row['tipo_pago'] != 6){
		$res1=mysql_query("SELECT * FROM claves_facturacion WHERE plaza='".$row['plaza']."' AND ticket='".$row['cve']."'");
		if($row1=mysql_fetch_array($res1)){
			//$texto.=chr(27).'!'.chr(8)."     CLAVE FACTURACION:|".$row1['cve'];
			//$texto.='|';
			$texto.=chr(27).'!'.chr(8)."     FECHA LIMITE FACTURACION:|    ".date( "Y-m-d" , strtotime ( "+1 month" , strtotime(date("Y-m").'-06') ) );
			$texto.='|';
			if($row['plaza']!=59 && $row['plaza']!=1 && $row['plaza']!=15) 
				$texto.=chr(27).'!'.chr(8)."     PAGINA PARA FACTURAR:|    www.facturaciongp1.com|";
		}
	}
	$texto.=chr(27).'!'.chr(8)." VENTA DE CERTIFICADO";
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." FECHA: ".$row['fecha']."   ".$row['hora'].'|';
	$texto.=chr(27).'!'.chr(40)."PLACA: ".$row['placa'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." A. CERTIFICADO: ".$array_anios[$row['anio']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." T. CERTIFICADO: ".$array_engomado[$row['engomado']];
	$texto.='|';
	//$texto.=chr(27).'!'.chr(8)." MODELO: ".$row['modelo'];
	//$texto.='|';
	$texto.=chr(27).'!'.chr(8)." TIPO VENTA: ".$array_tipo_venta[$row['tipo_venta']]['nombre'].'|';
	if($row['tipo_venta']==1) $texto.=chr(27).'!'.chr(8)." NUM INTENTO: ".$row['num_intento'].'|';
	$texto.=chr(27).'!'.chr(8)." TIPO PAGO: ".$array_tipo_pago[$row['tipo_pago']];
	$texto.='|';
	if($row['tipo_pago'] == 2 || $row['tipo_pago'] == 6 || $row['depositante']>0){
		$texto.=chr(27).'!'.chr(8)." DEPOSITANTE: ".$array_depositantes[$row['depositante']];
		$texto.='|';
	}
	$texto.=chr(27).'!'.chr(8)." TIPO COMBUSTIBLE ".$array_tipo_combustible[$row['tipo_combustible']];
	if($row['descuento'] > 0){
		$texto.='|';
		$texto.=chr(27).'!'.chr(8)." DESCUENTO PROMOCION ";
	}
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." MONTO: ".$row['monto'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." ".numlet($row['monto']);
	$texto.='|';
	
	if($row['tipo_venta'] == 2){
		$texto.='|___________________|'.$row['autoriza'].'|Autoriza|';
	}
	if($row['tipo_venta'] == 1){
		$texto.='|'.chr(27).'!'.chr(8)." MOTIVO INTENTO:|".$array_motivos_intento[$row['motivo_intento']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(8)." OBSERVACIONES:|".$row['obs'];
		$texto.='|';
		$res=mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND placa='".$row['placa']."' AND monto>0 ORDER BY cve DESC LIMIT 1");
		$row = mysql_fetch_array($res);
		$texto.=chr(27).'!'.chr(8)." TICKET PAGADO: ".$row['cve'];
		$texto.='|';
		$texto.=chr(27).'!'.chr(8)." FECHA: ".$row['fecha']."   ".$row['hora'].'|';
		$texto.='|';
		$texto.=chr(27).'!'.chr(8)." T. CERTIFICADO: ".$array_engomado[$row['engomado']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(8)." TIPO PAGO ".$array_tipo_pago[$row['tipo_pago']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(8)." TIPO COMBUSTIBLE ".$array_tipo_combustible[$row['tipo_combustible']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(8)." MONTO: ".$row['monto'];
		$texto.='|';
		$texto.=chr(27).'!'.chr(8)." ".numlet($row['monto']);
		$texto.='|';
	}
	
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$texto.'&logo='.str_replace(' ','',$array_plaza[$row['plaza']]).'&barcod=1'.sprintf("%011s",(intval($row['cve']))).'&copia=1" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",2000);</script>';
	exit();
}

if($_POST['ajax']==40){
	mysql_query("UPDATE cobro_engomado SET ".$_POST['campo']."='".$_POST['valor']."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
	if($_POST['campo']=='placa'){
		mysql_query("UPDATE certificados SET placa='".$_POST['placa']."' WHERE plaza='".$_POST['plazausuario']."' AND ticket='".$_POST['folio']."'");
		$nombre='Placa';
		$arreglo='';
	}
	elseif($_POST['campo']=='anio'){
		mysql_query("UPDATE certificados SET anio='".$_POST['anio']."' WHERE plaza='".$_POST['plazausuario']."' AND ticket='".$_POST['folio']."'");
		$nombre='Año';
		$arreglo='array_anios';
	}
	elseif($_POST['campo']=='tipo_pago'){
		$nombre = 'Tipo Pago';
		$arreglo='array_tipo_pago';
	}
	elseif($_POST['campo']=='tipo_pago'){
		$nombre = 'Depositante';
		$arreglo='array_depositantes';
	}
	mysql_query("INSERT historial SET menu='6',cveaux='".$_POST['folio']."',fecha='".fechaLocal()." ".horaLocal()."',obs='".$_POST['plazausuario']."',
			dato='$nombre',nuevo='".$_POST['valor']."',anterior='".$_POST['valor_anterior']."',arreglo='$arreglo',usuario='".$_POST['cveusuario']."'");
	exit();
}

top($_SESSION);
$resPlaza = mysql_query("SELECT * FROM plazas WHERE cve='".$_POST['plazausuario']."'");
if($rowPlaza=mysql_fetch_array($resPlaza)){
	echo '<h2>Plaza: '.$rowPlaza['numero'].' '.$rowPlaza['nombre'].'</h2>';
}
if($_POST['cmd']==12){
	include("imp_factura.php");
	$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
	$rowempresa = mysql_fetch_array($resempresa);
	$datossucursal='';
	if($rowempresa['check_sucursal']==1){
		$datossucursal=",check_sucursal='".$rowempresa['check_sucursal']."',nombre_sucursal='".$rowempresa['nombre_sucursal']."',
		calle_sucursal='".$rowempresa['calle_sucursal']."',numero_sucursal='".$rowempresa['numero_sucursal']."',
		colonia_sucursal='".$rowempresa['colonia_sucursal']."',rfc_sucursal='".$rowempresa['rfc_sucursal']."',
		localidad_sucursal='".$rowempresa['localidad_sucursal']."',municipio_sucursal='".$rowempresa['municipio_sucursal']."',
		estado_sucursal='".$rowempresa['estado_sucursal']."',cp_sucursal='".$rowempresa['cp_sucursal']."'";
	}
	require_once("phpmailer/class.phpmailer.php");
	
	$array_detalles = array();
	$ventas = '';
	if($_POST['reg']==0){
		foreach($_POST['facturar'] as $cveventa){
			$res=mysql_query("SELECT a.cve, b.monto FROM cobro_engomado a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.cve='$cveventa' AND a.estatus!='C' AND a.factura=0");
			$row=mysql_fetch_array($res);
			$array_detalles[$row['engomado']]['cant']+=1;
			$array_detalles[$row['engomado']]['monto']+=$row['monto'];
			$ventas .= ','.$row['cve'];
		}
	}
	else{
		$res=mysql_query("SELECT cve FROM clientes WHERE plaza='".$_POST['plazausuario']."' AND rfc='XAXX010101000'");
		$row=mysql_fetch_array($res);
		$_POST['cliente_facturar']=$row['cve'];
		$res=mysql_query("SELECT a.cve, b.monto FROM cobro_engomado a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus!='C' AND a.factura=0");
		while($row=mysql_fetch_array($res)){
			$array_detalles[$row['engomado']]['cant']+=1;
			$array_detalles[$row['engomado']]['monto']+=$row['monto'];
			$ventas .= ','.$row['cve'];
		}
	}
	if($_POST['cliente_facturar']>0 && count($array_detalles)>0){
		$res = mysql_query("SELECT serie,folio_inicial FROM foliosiniciales WHERE plaza='".$_POST['plazausuario']."' AND tipo=0 AND tipodocumento=1");
		$row = mysql_fetch_array($res);
		$res1 = mysql_query("SELECT IFNULL(MAX(folio+1),1) FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND serie='".$row['serie']."'");
		$row1 = mysql_fetch_array($res1);
		if($row['folio_inicial']<$row1[0]){
			$row['folio_inicial'] = $row1[0];
		}
		$insert = "INSERT facturas SET plaza='".$_POST['plazausuario']."',serie='".$row['serie']."',folio='".$row['folio_inicial']."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
		cliente='".$_POST['cliente_facturar']."',tipo_pago='0',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
		carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
		tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."'".$datossucursal;
		while(!$resinsert=mysql_query($insert)){
			$row['folio_inicial']++;
			$insert = "INSERT facturas SET plaza='".$_POST['plazausuario']."',serie='".$row['serie']."',folio='".$row['folio_inicial']."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
			cliente='".$_POST['cliente_facturar']."',tipo_pago='0',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
			carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
			tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."'".$datossucursal;
		}
		/*$res1 = mysql_query("SELECT cve FROM facturas WHERE plaza='".$_POST['plazausuario']."'");
		if(mysql_num_rows($res1) > 0){
			mysql_query("INSERT facturas SET plaza='".$_POST['plazausuario']."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
			cliente='".$_POST['cliente_facturar']."',tipo_pago='0',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
			carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
			tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."'".$datossucursal) or die(mysql_error());
		}
		else{
			mysql_query("INSERT facturas SET plaza='".$_POST['plazausuario']."',cve='".$row['folio_inicial']."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
			cliente='".$_POST['cliente_facturar']."',tipo_pago='0',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
			carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
			tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."'".$datossucursal) or die(mysql_error());
		}*/
		$cvefact=mysql_insert_id();
	
		$documento=array();
		require_once("nusoap/nusoap.php");
		$fserie=$row['serie'];
		$ffolio=$row['folio_inicial'];
		//Generamos la Factura
		$documento['serie']='';
		$documento['folio']=$cvefact;
		$documento['fecha']=fechaLocal().' '.horaLocal();
		$documento['formapago']='PAGO EN UNA SOLA EXHIBICION';
		$documento['idtipodocumento']=1;
		$documento['observaciones']=$_POST['obs'];
		$documento['metodopago']=$array_tipo_pago[0];
		$res = mysql_query("SELECT * FROM clientes WHERE cve='".$_POST['cliente_facturar']."'");
		$row = mysql_fetch_array($res);
		$emailenvio = $row['email'];
		$row['cve']=0;
		$documento['receptor']['codigo']=$row['cve'];
		$documento['receptor']['rfc']=$row['rfc'];
		$documento['receptor']['nombre']=$row['nombre'];
		$documento['receptor']['calle']=$row['calle'];
		$documento['receptor']['num_ext']=$row['numexterior'];
		$documento['receptor']['num_int']=$row['numinterior'];
		$documento['receptor']['colonia']=$row['colonia'];
		$documento['receptor']['localidad']=$row['localidad'];
		$documento['receptor']['municipio']=$row['municipio'];
		$documento['receptor']['estado']=$row['estado'];
		$documento['receptor']['pais']='MEXICO';
		$documento['receptor']['codigopostal']=$row['codigopostal'];
		
		//Agregamos los conceptos
		$i=0;
		$iva=0;
		$subtotal=0;
		$total=0;
		foreach($array_detalles as $k=>$v){
			$importe_iva=round($v['monto']*16/116,2);
			$total+=round($v['monto'],2);
			$subtotal+=round($v['monto']-$importe_iva,2);
			$iva+=$importe_iva;
			mysql_query("INSERT facturasmov SET plaza='".$_POST['plazausuario']."',cvefact='$cvefact',cantidad='".$v['cant']."',
			concepto='Venta de Engomado ".$array_engomado[$k]."',
			precio='".round(round($v['monto']-$importe_iva,2)/$v['cant'],2)."',importe='".round($v['monto']-$importe_iva,2)."',
			iva='16',importe_iva='$importe_iva',unidad='No Aplica'");
			$documento['conceptos'][$i]['cantidad']=$v['cant'];
			$documento['conceptos'][$i]['unidad']='No Aplica';
			$documento['conceptos'][$i]['descripcion']='Venta de Certificado '.$array_engomado[$k];
			$documento['conceptos'][$i]['valorUnitario']=round(round($v['monto']-$importe_iva,2)/$v['cant'],2);
			$documento['conceptos'][$i]['importe']=round($v['monto']-$importe_iva,2);
			$documento['conceptos'][$i]['importe_iva']=$importe_iva;
			$i++;
		}
		mysql_query("UPDATE facturas SET subtotal='".$subtotal."',iva='".$iva."',total='".$total."',
		isr_retenido='".$_POST['isr_retenido']."',por_isr_retenido='".$_POST['por_isr_retenido']."',
		iva_retenido='".$_POST['iva_retenido']."',por_iva_retenido='".$_POST['por_iva_retenido']."' 
		WHERE plaza='".$_POST['plazausuario']."' AND cve=".$cvefact);
		$documento['subtotal']=$subtotal;
		$documento['descuento']=0;
		//Traslados
		#IVA
		if($iva>0){
			$documento['tasaivatrasladado']=16;
			$documento['ivatrasladado']=$iva;  //Solo 200 grava iva
		}
		if($_POST['iva_retenido'] > 0){
			$documento['ivaretenido']=$_POST['iva_retenido'];  
		}
		if($_POST['isr_retenido'] > 0){
			$documento['isrretenido']=$_POST['isr_retenido'];  
		}
		//total
		$documento['total']=$total;
		//Moneda
		$documento['moneda']     = 1; //1=pesos, 2=Dolar, 3=Euro
		$documento['tipocambio'] = 1;
		mysql_query("UPDATE cobro_engomado SET factura='".$cvefact."',documento=1 WHERE plaza='".$_POST['plazausuario']."' AND cve IN (".substr($ventas,1).")");
		mysql_query("INSERT INTO venta_engomado_factura (plaza,venta,factura) SELECT ".$_POST['plazausuario'].",cve,factura FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND factura='".$cvefact."'");
		//print_r($documento);
		$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
		$err = $oSoapClient->getError();
		if($err!="")
			echo "error1:".$err;
		else{
			//print_r($documento);
			$oSoapClient->timeout = 300;
			$oSoapClient->response_timeout = 300;
			$respuesta = $oSoapClient->call("generar", array ('id' => $rowempresa['idplaza'],'rfcemisor' => $rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'documento' => $documento, 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
			if ($oSoapClient->fault) {
				echo '<p><b>Fault: ';
				print_r($respuesta);
				echo '</b></p>';
				echo '<p><b>Request: <br>';
				echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
				echo '<p><b>Response: <br>';
				echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
				echo '<p><b>Debug: <br>';
				echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
			}
			else{
				$err = $oSoapClient->getError();
				if ($err){
					echo '<p><b>Error: ' . $err . '</b></p>';
					echo '<p><b>Request: <br>';
					echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
					echo '<p><b>Response: <br>';
					echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
					echo '<p><b>Debug: <br>';
					echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
				}
				else{
					if($respuesta['resultado']){
						mysql_query("UPDATE facturas SET respuesta1='".$respuesta['uuid']."',seriecertificado='".$respuesta['seriecertificado']."',
						sellodocumento='".$respuesta['sellodocumento']."',uuid='".$respuesta['uuid']."',seriecertificadosat='".$respuesta['seriecertificadosat']."',
						sellotimbre='".$respuesta['sellotimbre']."',cadenaoriginal='".$respuesta['cadenaoriginal']."',
						fechatimbre='".substr($respuesta['fechatimbre'],0,10)." ".substr($respuesta['fechatimbre'],-8)."'
						WHERE plaza='".$_POST['plazausuario']."' AND cve=".$cvefact);
						//Tomar la informacion de Retorno
						$dir="cfdi/comprobantes/";
						//$dir=dirname(realpath(getcwd()))."/solucionesfe_facturacion/cfdi/comprobantes/";
						//el zip siempre se deja fuera
						$dir2="cfdi/";
						//Leer el Archivo Zip
						$fileresult=$respuesta['archivos'];
						$strzipresponse=base64_decode($fileresult);
						$filename='cfdi_'.$_POST['plazausuario'].'_'.$cvefact;
						file_put_contents($dir2.$filename.'.zip', $strzipresponse);
						$zip = new ZipArchive;
						if ($zip->open($dir2.$filename.'.zip') === TRUE){
							$strxml=$zip->getFromName('xml.xml');
							file_put_contents($dir.$filename.'.xml', $strxml);
							$strpdf=$zip->getFromName('formato.pdf');
							file_put_contents($dir.$filename.'.pdf', $strpdf);
							$zip->close();		
							generaFacturaPdf($_POST['plazausuario'],$cvefact);
							if($emailenvio!=""){
								$mail = new PHPMailer();
								$mail->Host = "localhost";
								$mail->From = "verificentros@verificentros.net";
								$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
								$mail->Subject = "Factura ".$fserie." ".$ffolio;
								$mail->Body = "Factura ".$fserie." ".$ffolio;
								//$mail->AddAddress(trim($emailenvio));
								$correos = explode(",",trim($emailenvio));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$fserie." ".$ffolio.".pdf");
								$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$fserie." ".$ffolio.".xml");
								$mail->Send();
							}	
							if($rowempresa['email']!=""){
								$mail = new PHPMailer();
								$mail->Host = "localhost";
								$mail->From = "verificentros@verificentros.net";
								$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
								$mail->Subject = "Factura ".$fserie." ".$ffolio;
								$mail->Body = "Factura ".$fserie." ".$ffolio;
								//$mail->AddAddress(trim($rowempresa['email']));
								$correos = explode(",",trim($rowempresa['email']));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$fserie." ".$ffolio.".pdf");
								$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$fserie." ".$ffolio.".xml");
								$mail->Send();
							}	
						}
						else 
							$strmsg='Error al descomprimir el archivo';
					}
					else
						$strmsg=$respuesta['mensaje'];
					//print_r($respuesta);	
					echo $strmsg;
				}
			}
		}
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==3){
	mysql_query("UPDATE cobro_engomado SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$res = mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	mysql_query("UPDATE call_citas SET cvecobro='' WHERE cve='".$row['registro_web']."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	if($_POST['engomado']>0){
		if($_POST['reg']>0){
			$insert = " INSERT cobro_engomado_historial
							SET 
							plaza = '".$_POST['plazausuario']."',modelo='".$_POST['modelo']."',tipo_pago='".$_POST['tipo_pago']."',
							documento='".$_POST['documento']."',placa='".$_POST['placa']."',engomado='".$_POST['engomado']."',monto='".$_POST['monto']."',
							cobro_id='".$_POST['reg']."',usuario='".$_POST['cveusuario']."',fecha='".fechaLocal()." ".horaLocal()."'";
			mysql_query($insert);
		
		
			$insert = " UPDATE cobro_engomado 
							SET monto='".$_POST['monto']."',autoriza='".$_POST['autoriza']."',
							placa='".$_POST['placa']."',modelo='".$_POST['modelo']."',tipo_pago='".$_POST['tipo_pago']."',multa='".$_POST['multa']."',tipo_venta='".$_POST['tipo_venta']."',
							depositante='".$_POST['depositante']."',anio='".$_POST['anio']."',motivo_intento='".$_POST['motivo_intento']."',obs='".$_POST['obs']."',tipo_combustible='".$_POST['tipo_combustible']."'
						WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'";
			mysql_query($insert);
			
			mysql_query("UPDATE certificados SET placa = '".$_POST['placa']."' WHERE plaza = '".$_POST['plazausuario']."' AND ticket = '".$_POST['reg']."'");
		}
		else{
			$res = mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' ORDER BY cve DESC LIMIT 1");
			$row = mysql_fetch_array($res);
			if($row['placa'] != $_POST['placa'] || $row['engomado'] != $_POST['engomado'] || $_POST['anio'] != $row['anio']){
				if($_POST['registro_web']>0){
					$res1=mysql_query("SELECT * FROM registros_web WHERE cve='".$_POST['registro_web']."'");
					$row1=mysql_fetch_array($res1);
					if($row1['placa']!=$_POST['placa']){
						$_POST['registro_web']=0;
					}
				}
				$aplicadescuento = 0;
				$descuento = 0;
				$numero = numeroPlaca($_POST['placa']);
				/*if($_POST['anio']==2 && $_POST['multa']!=1){
					$numero = numeroPlaca($_POST['placa']);
					if(($numero == 7 || $numero == 8) && intval(date("m"))==4 && intval(date("d")) <= 15) $aplicadescuento=10;
					elseif(($numero == 3 || $numero == 4) && intval(date("m"))==5 && intval(date("d")) <= 15) $aplicadescuento=10;
					elseif(($numero == 1 || $numero == 2) && intval(date("m"))==6 && intval(date("d")) <= 15) $aplicadescuento=10;
					elseif(($numero == 9 || $numero == 0) && intval(date("m"))==7 && intval(date("d")) <= 15) $aplicadescuento=10;
				}*/
				if($aplicadescuento>0){
					$descuento = round($_POST['monto']*$aplicadescuento/100,2);
					$_POST['monto'] -= $descuento;
				}
				if($_POST['tipo_venta']!=1) $_POST['num_intento']=0;
				$insert = " INSERT cobro_engomado 
								SET 
								plaza = '".$_POST['plazausuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',registro_web='".$_POST['registro_web']."',
								placa='".$_POST['placa']."',engomado='".$_POST['engomado']."',monto='".$_POST['monto']."',tipo_combustible='".$_POST['tipo_combustible']."',
								modelo='".$_POST['modelo']."',tipo_pago='".$_POST['tipo_pago']."',documento='".$_POST['documento']."',depositante='".$_POST['depositante']."',
								usuario='".$_POST['cveusuario']."',estatus='A',motivo_intento='".$_POST['motivo_intento']."',
								referencia_maquina_registradora='".$_POST['referencia_maquina_registradora']."',num_intento='".$_POST['num_intento']."',
								obs='".$_POST['obs']."',multa='".$_POST['multa']."',anio='".$_POST['anio']."',descuento='$descuento',
								tipo_venta = '".$_POST['tipo_venta']."',monto_verificacion='".$_POST['monto_verificacion']."',autoriza='".$_POST['autoriza']."',
								tipo_cortesia='".$_POST['tipo_cortesia']."',codigo_cortesia='".$_POST['codigo_cortesia']."'";
				mysql_query($insert);
				$cvecobro = mysql_insert_id();
				if($_POST['registro_web']>0){
					mysql_query("UPDATE call_citas SET cvecobro='$cvecobro' WHERE cve='".$_POST['registro_web']."'");
					if($row1['requiere_factura']==1){
						$res2=mysql_query("SELECT * FROM clientes WHERE plaza='".$_POST['plazausuario']."' AND rfc='".$row1['rfc']."'");
						if(mysql_num_rows($res2)==0){
							mysql_query("INSERT clientes SET plaza='".$_POST['plazausuario']."',fechayhora='".$row1['fechayhora']."',usuario='".$_POST['cveusuario']."',
							rfc='".$_POST['rfc']."',nombre='".$_POST['nombre']."',email='".$_POST['email']."',calle='".$_POST['calle']."',
							numexterior='".$_POST['numexterior']."',numinterior='".$_POST['numinterior']."',colonia='".$_POST['colonia']."',
							municipio='".$_POST['municipio']."',estado='".$_POST['estado']."',codigopostal='".$_POST['codigopostal']."'");
						}
					}
				}
				if($_POST['monto']>0 && $_POST['tipo_pago'] != 2 && $_POST['tipo_pago'] != 6){
					guardaClave($_POST['plazausuario'], $cvecobro);
				}
			}
		}
	}
	else{
		echo '<script>alert("Ocurrio un error al guardar la venta favor de capturarla de nuevo");</script>';	
	}
	$_POST['cmd']=1;
}

/*** EDICION  **************************************************/
if($_POST['cmd']==11.2){
	mysql_query("INSERT microseguros SET 
		plaza='".$_POST['plazausuario']."',fecha=CURDATE(),hora=CURTIME(),
		ramo=281,movimiento='A',fecha_vigencia='".$_POST['fecha_vigencia']."',
		formapago=1,nombre='".addslashes($_POST['nombre'])."',apaterno='".addslashes($_POST['apaterno'])."',
		amaterno='".addslashes($_POST['amaterno'])."',sexo='".$_POST['sexo']."',
		fecha_nacimiento='".$_POST['fecha_nacimiento']."',calle='".addslashes($_POST['calle'])."',
		numero='".addslashes($_POST['numero'])."',colonia='".addslashes($_POST['colonia'])."',
		codigopostal='".addslashes($_POST['codigopostal'])."',telefono='".addslashes($_POST['telefono'])."',
		usuario='".$_POST['cveusuario']."',estatus='A'");
	$_POST['cmd'] = 0;
}

/*** EDICION  **************************************************/

	if($_POST['cmd'] == 11){
		$res = mysql_query("SELECT * FROM microseguros WHERE cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();validar('.$_POST['reg'].');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'cobro_engomado.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Venta de Seguro</td></tr>';
		echo '</table>';
		echo '<table style="font-size:15px">';
		echo '<tr><td>Fecha Vigencia</td><td><input type="text" name="fecha_vigencia" id="fecha_vigencia" style="font-size:30px" class="readOnly" size="12" value="" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_vigencia,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" style="font-size:30px" class="textField" size="50" value=""></td></tr>';
		echo '<tr><td>Apellido Paterno</td><td><input type="text" name="apaterno" id="apaterno" style="font-size:30px" class="textField" size="50" value=""></td></tr>';
		echo '<tr><td>Apellido Materno</td><td><input type="text" name="amaterno" id="amaterno" style="font-size:30px" class="textField" size="50" value=""></td></tr>';
		echo '<tr><td>Sexo</td><td><select name="sexo" id="sexo"><option value="">Seleccion</option><option value="M">Masculino</option><option value="F">Femenino</option></select></td></tr>';
		echo '<tr><td>Fecha Nacimiento</td><td><input type="text" name="fecha_nacimiento" id="fecha_nacimiento" style="font-size:30px" class="readOnly" size="12" value="" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_nacimiento,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Calle</td><td><input type="text" name="calle" id="calle" style="font-size:30px" class="textField" size="50" value=""></td></tr>';
		echo '<tr><td>N&uacute;mero</td><td><input type="text" name="numero" id="numero" style="font-size:30px" class="textField" size="20" value=""></td></tr>';
		echo '<tr><td>Colonia</td><td><input type="text" name="colonia" id="colonia" style="font-size:30px" class="textField" size="50" value=""></td></tr>';
		echo '<tr><td>C&oacute;digo Postal</td><td><input type="text" name="codigopostal" id="codigopostal" style="font-size:30px" class="textField" size="10" value=""></td></tr>';
		echo '<tr><td>Tel&eacute;fono</td><td><input type="text" name="telefono" id="telefono" style="font-size:30px" class="textField" size="20" value=""></td></tr>';
		echo '</table>';
		echo '<script>
				function validar(reg){
					if(document.forma.fecha_vigencia.value==""){
						$("#panel").hide();
						alert("Necesita ingresar la fecha de vigencia");
					}
					else if($.trim(document.forma.nombre.value)==""){
						$("#panel").hide();
						alert("Necesita ingresar el nombre");
					}
					else if($.trim(document.forma.apaterno.value)==""){
						$("#panel").hide();
						alert("Necesita ingresar el apellido paterno");
					}
					else if($.trim(document.forma.amaterno.value)==""){
						$("#panel").hide();
						alert("Necesita ingresar el apellido materno");
					}
					else if($.trim(document.forma.sexo.value)==""){
						$("#panel").hide();
						alert("Necesita seleccionar el sexo");
					}
					else if($.trim(document.forma.fecha_nacimiento.value)==""){
						$("#panel").hide();
						alert("Necesita seleccionar la fecha de nacimiento");
					}
					else if($.trim(document.forma.calle.value)==""){
						$("#panel").hide();
						alert("Necesita ingresar la calle");
					}
					else if($.trim(document.forma.colonia.value)==""){
						$("#panel").hide();
						alert("Necesita ingresar la colonia");
					}
					else if($.trim(document.forma.codigopostal.value)==""){
						$("#panel").hide();
						alert("Necesita ingresar el codigo postal");
					}
					else{
						atcr("cobro_engomado.php","",11.2,reg);
					}
				}
			</script>';
	}

	if ($_POST['cmd']==1) {
		
		$res = mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();validar('.$_POST['reg'].');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'cobro_engomado.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Venta de Engomado</td></tr>';
		echo '</table>';
		echo '<input type="hidden" value="" name="registro_web" id="registro_web">';
		echo '<input type="hidden" value="" name="num_intento" id="num_intento">';
		echo '<input type="hidden" value="'.$row['monto_verificacion'].'" name="monto_verificacion" id="monto_verificacion">';
		echo '<table style="font-size:15px">';
		if($_POST['reg']==0){
			echo '<tr><th align="left">Placa</th><td><input type="text" name="placa" id="placa" autocomplete="off" class="textField placas" style="font-size:30px" size="10" value="'.$row['placa'].'" onKeyUp="if(event.keyCode==13){ traeRegistro();}else{this.value = this.value.toUpperCase();}"></td></tr>';
			echo '<tr><th align="left" style="font-size:18px">Tiene Multa</th><td><input type="checkbox" name="multa" id="multa" value="1" style="font-size:18px"></td></tr>';
			echo '<tr><th align="left">Año Certificado</th><td><select name="anio" id="anio" style="font-size:20px">';
			if(count($array_anios)>1) echo '<option value="0">Seleccione</option>';
			foreach($array_anios as $k=>$v){
				echo '<option value="'.$k.'"';
				if($row['anio'] == $k) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Tipo de Certificado</th><td><select name="engomado" id="engomado" style="font-size:20px" onChange="muestra_precio()"><option value="0" costo="0">Seleccione</option>';
			$i=0;
			foreach($array_engomado as $k=>$v){
				echo '<option value="'.$k.'" costo="'.$array_engomadoprecio[$k].'"';
				if($row['engomado']==$k) echo ' checked';
				echo '>'.$v.'</option>';
				$i++;
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Tipo Venta</th><td><select name="tipo_venta" id="tipo_venta" style="font-size:20px" onChange="muestra_precio()">';
			foreach($array_tipo_venta as $k=>$v){
				echo '<option value="'.$k.'" costo="'.$v['costo'].'" maneja_motivo="'.$v['maneja_motivo'].'" maneja_autoriza="'.$v['maneja_autoriza'].'"';
				if($k==$row['tipo_venta']) echo ' checked';
				echo '>'.$v['nombre'].'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr';
			if($row['tipo_venta']!=2) echo 'display:none;';
			echo '><th align="left">Tipo Cortesia</th><td><select name="tipo_cortesia" id="tipo_cortesia" style="font-size:20px" onChange="campos_cortesia()">
			<option value="0">Seleccione</option>';
			foreach($array_tipo_cortesia as $k=>$v){
				echo '<option value="'.$k.'"';
				if($k==$row['tipo_cortesia']) echo ' selected';
				echo '>'.$v['nombre'].'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="readOnly" size="10" style="font-size:12px" value="'.$row['monto'].'" readOnly></td></tr>';
			echo '<tr';
			if($row['tipo_cortesia']!=2 || $_POST['reg']==0){
				echo ' style="display:none;"';
			}
			echo '><th align="left">Codigo Cortesia</th><td><input type="text" class="textField" style="font-size:12px" size="50" name="codigo_cortesia" id="codigo_cortesia" value="'.$row['codigo_cortesia'].'"><font color="RED" style="font-size:20px">Escriba el codigo de autorizacion de la cortesia</font></td></tr>';
			
			echo '<tr';
			if($row['tipo_cortesia']!=1 || $_POST['reg']==0){
				echo ' style="display:none;"';
			}
			echo '><th align="left">Autoriza</th><td><input type="text" class="textField" style="font-size:12px" size="50" name="autoriza" id="autoriza" value="'.$row['autoriza'].'"><font color="RED" style="font-size:20px">Escriba la persona que autoriza la venta</font></td></tr>';
			echo '<tr';
			if($array_tipo_venta[$row['tipo_venta']]['maneja_motivo']!=1 || $_POST['reg']==0){
				echo ' style="display:none;"';
			}
			echo '><th align="left">Motivo de Intento</th><td><select name="motivo_intento" id="motivo_intento" style="font-size:20px"><option value="0">Seleccione</option>';
			foreach($array_motivos_intento as $k=>$v){
				echo '<option value="'.$k.'"';
				if($row['motivo_intento'] == $k) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select><font color="RED" style="font-size:20px">Complemente en el espacio de texto libre</font></td></tr>';
			echo '<tr><th align="left">Tipo de Combustible</th><td><select name="tipo_combustible" id="tipo_combustible" style="font-size:20px"><option value="0">Seleccione</option>';
			foreach($array_tipo_combustible as $k=>$v){
				echo '<option value="'.$k.'"';
				if($row['tipo_combustible'] == $k) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Tipo de Pago</th><td><select name="tipo_pago" id="tipo_pago" style="font-size:20px"><!--<option value="0">Seleccione</option>-->';
			foreach($array_tipo_pago as $k=>$v){
				echo '<option value="'.$k.'"';
				if($row['tipo_pago'] == $k) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select><b><font color="RED">Ya se pueden hacer pagos de contado a depositantes, estos se deben de cobrar</font></b></td></tr>';
			echo '<tr><th align="left">Depositante</th><td><select name="depositante" id="depositante" style="font-size:20px"><option value="0">Seleccione</option>';
			foreach($array_depositantes as $k=>$v){
				//$saldo = saldo_depositante2($k); (Saldo: '.number_format($saldo,2).')
				echo '<option value="'.$k.'" saldo_6="'.$saldo.'"';
				if($row['depositante']==$k) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Documento</th><td><select name="documento" id="documento" style="font-size:20px">';
			foreach($array_documentos as $k=>$v){
				if(($k==2 && $_POST['reg']==0) || $row['documento']==$k){
					echo '<option value="'.$k.'"';
					if(($k==2 && $_POST['reg']==0) || $row['documento']==$k) echo ' selected';
					echo '>'.$v.'</option>';
				}
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Observaciones</th><td><textarea name="obs" id="obs" class="textField" rows="3" cols="30"></textarea></td></tr>';
		}
		else{
			echo '<tr><th align="left">Placa</th><td><input type="text" name="placa" id="placa" autocomplete="off" class="textField placas" style="font-size:30px" size="10" value="'.$row['placa'].'" onKeyUp="this.value = this.value.toUpperCase();"></td></tr>';
			echo '<tr><th align="left">Tiene Multa</th><td><input type="hidden" name="multa" id="multa" value="'.$row['multa'].'"><input type="checkbox"';
			if($row['multa']==1) echo ' checked';
			echo ' disabled></td></tr>';
			echo '<tr><th align="left">Año Certificado</th><td><select name="anio" id="anio" style="font-size:20px">';
			if(count($array_anios)>1) echo '<option value="0">Seleccione</option>';
			foreach($array_anios as $k=>$v){
				echo '<option value="'.$k.'"';
				if($row['anio'] == $k) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Tipo de Certificado</th><td><select name="engomado" id="engomado" style="font-size:20px" onChange="muestra_precio()">';
			$i=0;
			foreach($array_engomado as $k=>$v){
				if($row['engomado']==$k){
					echo '<option value="'.$k.'" costo="'.$array_engomadoprecio[$k].'"';
					echo ' selected';
					echo '>'.$v.'</option>';
					$i++;
				}
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Tipo Venta</th><td><select name="tipo_venta" id="tipo_venta" style="font-size:20px" onChange="muestra_precio()">';
			foreach($array_tipo_venta as $k=>$v){
				if($k==$row['tipo_venta'] || $_POST['cveusuario']==1){
					echo '<option value="'.$k.'" costo="'.$v['costo'].'" maneja_motivo="'.$v['maneja_motivo'].'" maneja_autoriza="'.$v['maneja_autoriza'].'"';
					if($k==$row['tipo_venta'])
						echo ' selected';
					echo '>'.$v['nombre'].'</option>';
				}
			}
			echo '</select></td></tr>';
			echo '<tr';
			if($row['tipo_venta']!=2) echo 'display:none;';
			echo '><th align="left">Tipo Cortesia</th><td><select name="tipo_cortesia" id="tipo_cortesia" style="font-size:20px" onChange="campos_cortesia()">
			<option value="0">Seleccione</option>';
			foreach($array_tipo_cortesia as $k=>$v){
				echo '<option value="'.$k.'"';
				if($k==$row['tipo_cortesia']) echo ' selected';
				echo '>'.$v['nombre'].'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="readOnly" size="10" style="font-size:12px" value="'.$row['monto'].'" readOnly></td></tr>';
			echo '<tr';
			if($row['tipo_cortesia']!=2 || $_POST['reg']==0){
				echo ' style="display:none;"';
			}
			echo '><th align="left">Codigo Cortesia</th><td><input type="text" class="textField" style="font-size:12px" size="50" name="codigo_cortesia" id="codigo_cortesia" value="'.$row['codigo_cortesia'].'"><font color="RED" style="font-size:20px">Escriba el codigo de autorizacion de la cortesia</font></td></tr>';
			
			echo '<tr';
			if($row['tipo_cortesia']!=1 || $_POST['reg']==0){
				echo ' style="display:none;"';
			}
			echo '><th align="left">Autoriza</th><td><input type="text" class="textField" style="font-size:12px" size="50" name="autoriza" id="autoriza" value="'.$row['autoriza'].'"><font color="RED" style="font-size:20px">Escriba la persona que autoriza la venta</font></td></tr>';
			
			echo '<tr';
			if($array_tipo_venta[$row['tipo_venta']]['maneja_motivo']!=1 || $_POST['reg']==0){
				echo ' style="display:none;"';
			}
			echo '><th align="left">Motivo de Intento</th><td><select name="motivo_intento" id="motivo_intento" style="font-size:20px"><option value="0">Seleccione</option>';
			foreach($array_motivos_intento as $k=>$v){
				if($row['motivo_intento'] == $k || $_POST['cveusuario']==1){
					echo '<option value="'.$k.'"';
					echo ' selected';
					echo '>'.$v.'</option>';
				}
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Tipo de Combustible</th><td><select name="tipo_combustible" id="tipo_combustible" style="font-size:20px">';
			foreach($array_tipo_combustible as $k=>$v){
				if($row['tipo_combustible'] == $k){
				echo '<option value="'.$k.'"';
				echo ' selected';
				echo '>'.$v.'</option>';
				}
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Tipo de Pago</th><td><select name="tipo_pago" id="tipo_pago" style="font-size:20px"><!--<option value="0">Seleccione</option>-->';
			foreach($array_tipo_pago as $k=>$v){
				if($row['tipo_pago'] == $k || $_POST['cveusuario']==1){
					echo '<option value="'.$k.'"';
					if($row['tipo_pago'] == $k)	echo ' selected';
					echo '>'.$v.'</option>';
				}
			}
			echo '</select><b><font color="RED">Ya se pueden hacer pagos de contado a depositantes, estos se deben de cobrar</font></b></td></tr>';
			echo '<tr><th align="left">Depositante</th><td><select name="depositante" id="depositante" style="font-size:20px"><option value="0">Seleccione</option>';
			foreach($array_depositantes as $k=>$v){
				//$saldo = saldo_depositante2($k); (Saldo: '.number_format($saldo,2).')
				echo '<option value="'.$k.'" saldo_6="'.$saldo.'"';
				if($row['depositante']==$k) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Documento</th><td><select name="documento" id="documento" style="font-size:20px">';
			foreach($array_documentos as $k=>$v){
				if(($k==2 && $_POST['reg']==0) || $row['documento']==$k){
					echo '<option value="'.$k.'"';
					if(($k==2 && $_POST['reg']==0) || $row['documento']==$k) echo ' selected';
					echo '>'.$v.'</option>';
				}
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Observaciones</th><td><textarea name="obs" id="obs" class="readOnly" rows="3" cols="30" readOnly></textarea></td></tr>';
		}
		echo '</table>';
		
		echo '<script>

				function muestra_precio(){
					costo = $("#tipo_venta").find("option:selected").attr("costo");
					monto_verificacion = $("#engomado").find("option:selected").attr("costo");
					if(costo >= 0){
						document.forma.monto.value = costo;
					}
					else{
						document.forma.monto.value = monto_verificacion;
					}
					document.forma.monto_verificacion.value = monto_verificacion;
					mostrar_motivo_intento();
				}

				function validar_numero_placa(){
					regresar = true;
					return true;
					$.ajax({
					  url: "cobro_engomado.php",
					  type: "POST",
					  async: false,
					  data: {
						placa: document.getElementById("placa").value,
						plazausuario: document.forma.plazausuario.value,
						ajax: 11
					  },
						success: function(data) {
							if(data == "1"){
								regresar = false;
							}
						}
					});
					return regresar;
				}

				function validar(reg){
					if(document.forma.placa.value==""){
						$("#panel").hide();
						alert("Necesita ingresar la placa");
					}
					else if(!validar_numero_placa())
					{
						$("#panel").hide();
						alert("No se le pueden vender certificado a la placa el dia de hoy");
					}
					else if(document.forma.anio.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el año del certificado");
					}
					else if(document.forma.engomado.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el tipo de certificado");
					}
					/*else if(document.forma.placa2.value==""){
						$("#panel").hide();
						alert("Necesita ingresar la confirmacion de la placa");
					}
					else if(document.forma.placa2.value!=document.forma.placa.value){
						$("#panel").hide();
						alert("La placa y confirmacion de la placa no son iguales");
					}*/
					else if(document.forma.tipo_combustible.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el tipo de combustible");
					}
					else if(document.forma.tipo_pago.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el tipo de pago");
					}
					else if((document.forma.tipo_pago.value=="2" || document.forma.tipo_pago.value=="6") && document.forma.depositante.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el depositante");
					}
					else if( (1==2) && document.forma.tipo_pago.value=="6" && (document.forma.monto.value/1)>($("#depositante option:selected").attr("saldo_6")/1)){
						$("#panel").hide();
						alert("No tiene saldo suficiente el depositante");
					}
					else if(document.forma.documento.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el documento");
					}
					else if($("#tipo_venta").find("option:selected").attr("maneja_motivo") == "1" && document.forma.motivo_intento.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el motivo de intento");
					}
					else if(validarPlaca() == false && document.forma.plazausuario.value!=59){
						$("#panel").hide();
						alert("La placa ya tiene un intento");
					}
					else if(validarIntento() == false && document.forma.plazausuario.value!=59){
						$("#panel").hide();
						alert("La placa debe de tener una venta del semestre");	
					}
					else if(validarEntregasAnteriores() == false){
						$("#panel").hide();
					}
					else if(validarNumIntento() == false && document.forma.plazausuario.value!=59){
						alert("La placa ya excedio el numero de intentos");
						$("#panel").hide();
					}
					else if(document.forma.tipo_venta.value=="2" && document.forma.tipo_cortesia.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el tipo de cortesia");
					}
					else if(document.forma.tipo_venta.value=="2" && document.forma.tipo_cortesia.value=="1" && $.trim(document.forma.autoriza.value)==""){
						$("#panel").hide();
						alert("Necesita ingresar la persona que autoriza");
					}
					else if(document.forma.tipo_venta.value=="2" && document.forma.tipo_cortesia.value=="2" && $.trim(document.forma.codigo_cortesia.value)==""){
						$("#panel").hide();
						alert("Necesita ingresar el codigo de cortesia");
					}
					else if(document.forma.tipo_venta.value=="2" && document.forma.tipo_cortesia.value=="2" && document.forma.depositante.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el depositante");
					}
					else if(validarAutoriza() == false)
					{
						$("#panel").hide();
					}
					else if(validarCortesia() == false)
					{
						alert("La placa ya tiene un pago en este semestre");
						$("#panel").hide();
					}
					/*else if(validarSaldo() == false)
					{
						alert("El depositante no tiene saldo suficiente para la venta");
						$("#panel").hide();
					}*/
					else if(validarVerificacion00() == false && document.forma.plazausuario.value!=59){
						$("#panel").hide();
						alert("La placa ya tiene una verificacion 00");
					}
					else{
						atcr("cobro_engomado.php","",2,reg);
					}
				}

				function validarCortesia(){
					if((document.getElementById("tipo_venta").value/1)!=2)
						return true;
					regresar = true;
					$.ajax({
					  url: "cobro_engomado.php",
					  type: "POST",
					  async: false,
					  data: {
					  	plazausuario: document.getElementById("plazausuario").value,
						anio: document.getElementById("anio").value,
						placa: document.getElementById("placa").value,
						ajax: 13
					  },
						success: function(data) {
							if(data == 1)
								regresar = false;
							else
								regresar = true;
						}
					});
					return regresar;
				}

				function validarSaldo(){
					if((document.getElementById("depositante").value/1)==0 || document.getElementById("tipo_pago").value != 6)
						return true;
					regresar = true;
					$.ajax({
					  url: "cobro_engomado.php",
					  type: "POST",
					  async: false,
					  data: {
					  	plazausuario: document.getElementById("plazausuario").value,
						depositante: document.getElementById("depositante").value,
						monto: document.getElementById("monto").value,
						ajax: 12
					  },
						success: function(data) {
							if(data == 1)
								regresar = false;
							else
								regresar = true;
						}
					});
					return regresar;
				}
				
				function validarVerificacion00(){
					return true;
					regresar = false;
					$.ajax({
					  url: "cobro_engomado.php",
					  type: "POST",
					  async: false,
					  data: {
						placa: document.getElementById("placa").value,
						anio: document.forma.anio.value,
						ajax: 9
					  },
						success: function(data) {
							if(data == 1)
								regresar = false;
							else
								regresar = true;
						}
					});
					return regresar;
				}

				function campos_cortesia(){
					$("#autoriza").parents("tr:first").hide();
					document.forma.autoriza.value="";
					$("#codigo_cortesia").parents("tr:first").hide();
					document.forma.codigo_cortesia.value="";
					if(document.forma.tipo_cortesia.value=="1"){
						$("#codigo_cortesia").parents("tr:first").show();
					}
					else if(document.forma.tipo_cortesia.value=="2"){
						$("#autoriza").parents("tr:first").show();
					}
				}
				
				function mostrar_motivo_intento(){
					if($("#tipo_venta").find("option:selected").attr("maneja_motivo") == "1"){
						$("#motivo_intento").parents("tr:first").show();
					}
					else{
						$("#motivo_intento").parents("tr:first").hide();
						document.forma.motivo_intento.value="0";
					}

					$("#autoriza").parents("tr:first").hide();
					document.forma.autoriza.value="";
					$("#codigo_cortesia").parents("tr:first").hide();
					document.forma.codigo_cortesia.value="";
					$("#tipo_cortesia").parents("tr:first").hide();
					document.forma.tipo_cortesia.value="0";

					if(document.forma.tipo_venta.value=="2"){
						$("#tipo_cortesia").parents("tr:first").show();
					}

					/*if($("#tipo_venta").find("option:selected").attr("maneja_autoriza") == "1"){
						$("#autoriza").parents("tr:first").show();
					}
					else{
						$("#autoriza").parents("tr:first").hide();
						document.forma.autoriza.value="";
					}
					
					if(document.forma.tipo_venta.value!="2")
					{
						$("#tipo_pago").find("option").show();
					}
					else if(confirm("La cortesia es para pago anticipado?"))
					{
						document.forma.tipo_pago.value = "6";
						$("#tipo_pago").find("option").each(function(){
							if(this.value != "6"){
								$(this).hide();
							}
						});
						$("#depositante").parents("tr:first").show();
					}
					else
					{
						document.forma.tipo_pago.value = "1";
						$("#tipo_pago").find("option").each(function(){
							if(this.value != "1"){
								$(this).hide();
							}
						});
						//$("#depositante").parents("tr:first").hide();
						$("#autoriza").parents("tr:first").show();
					}*/
				}

				function validarEntregasAnteriores(){
					if($("#tipo_venta").val() == "1" && document.forma.plazausuario.value!=59){
						regresar = false;
						$.ajax({
						  url: "cobro_engomado.php",
						  type: "POST",
						  async: false,
						  data: {
						  	id: '.$_POST['reg'].',
							placa: document.getElementById("placa").value,
							anio: document.getElementById("anio").value,
							plazausuario: document.forma.plazausuario.value,
							depositante: document.forma.depositante.value,
							ajax: 8
						  },
							success: function(data) {
								if(data == "1"){
									regresar = false;
									alert("La placa ya se termino su numero de intentos");
								}
								else{
									regresar = true;
								}
							}
						});
						return regresar;
					}
					else{
						return true;
					}
				}

				function validarAutoriza(){
					if(document.forma.tipo_venta.value == "2" && document.forma.plazausuario.value!=59){
						regresar = false;
						$.ajax({
						  url: "cobro_engomado.php",
						  type: "POST",
						  async: false,
						  data: {
							depositante: document.getElementById("depositante").value,
							placa: document.getElementById("placa").value,
							anio: document.getElementById("anio").value,
							plazausuario: document.forma.plazausuario.value,
							ajax: 7
						  },
							success: function(data) {
								if(data == 2){
									regresar = false;
									alert("La placa ya tiene una cortesia");
								}
								else if(data == 1){
									regresar = true;
								}
								else{
									regresar = false;
								}
							}
						});
						return regresar;
					}
					else{
						return true;
					}
				}
				
				function validarPlaca(){
					if((document.forma.monto.value/1)>0){
						return true;
					}
					else{
						regresar = false;
						$.ajax({
						  url: "cobro_engomado.php",
						  type: "POST",
						  async: false,
						  data: {
							placa: document.getElementById("placa").value,
							plazausuario: document.forma.plazausuario.value,
							ajax: 4
						  },
							success: function(data) {
								if(data == 1)
									regresar = true;
								else
									regresar = false;
							}
						});
						return regresar;
					}
				}

				function validarIntento(){
					if(document.forma.tipo_venta.value!=1){
						return true;
					}
					else{
						regresar = false;
						$.ajax({
						  url: "cobro_engomado.php",
						  type: "POST",
						  async: false,
						  data: {
							placa: document.getElementById("placa").value,
							plazausuario: document.forma.plazausuario.value,
							anio: document.forma.anio.value,
							ajax: 5
						  },
							success: function(data) {
								if(data == 1)
									regresar = true;
								else
									regresar = false;
							}
						});
						return regresar;
					}
				}
				
				function validarNumIntento(){
					if(document.forma.tipo_venta.value!=1){
						return true;
					}
					else{
						regresar = false;
						$.ajax({
						  url: "cobro_engomado.php",
						  type: "POST",
						  async: false,
						  data: {
							placa: document.getElementById("placa").value,
							id: '.intval($_POST['reg']).',
							plazausuario: document.forma.plazausuario.value,
							depositante: document.forma.depositante.value,
							anio: document.forma.anio.value,
							ajax: 6
						  },
							success: function(data) {
								/*if((data/1) == 0)
									regresar = true;
								else
									regresar = confirm("La placa tiene "+data+" intento(s), ¿Desea continuar?");*/
								if(data=="-1")
									regresar = false;
								else
									regresar = true;
								document.forma.num_intento.value = (data/1)+1;
								alert(document.forma.num_intento.value+" intento de 2");
							}
						});
						return regresar;
					}
				}
				
				function traeRegistro(){
					$.ajax({
					  url: "cobro_engomado.php",
					  type: "POST",
					  async: false,
					  data: {
						placa: document.getElementById("placa").value,
						plazausuario: document.forma.plazausuario.value,
						ajax: 3
					  },
						success: function(data) {
							datos = data.split("|");
							document.forma.registro_web.value=datos[0];
							document.forma.modelo.value=datos[1];
							document.forma.engomado.value=datos[2];
							document.getElementById("auxengomado_"+datos[2]).checked=true;
							document.forma.monto.value=datos[3];
						}
					});
				}
				
			</script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	$nivelUsuario=nivelUsuario();
	echo '<div id="dialog" style="display:none">
	<table>
	<tr><td class="tableEnc">Seleccione Cliente para Facturar</td></tr>
	</table>
	<table width="100%">
		<tr><th align="left">Busqueda Cliente: </th><td><input type="text" class="textField" id="buscador" name="buscador" value=""></td></tr>
		<tr><th align="left">RFC: </th><td><input type="text" class="readOnly" id="rfccliente" name="rfccliente" size="15" value="" readOnly></td></tr>
		<tr><th align="left">Nombre: </th><td><input type="text" class="readOnly" id="nombrecliente" name="nombrecliente" size="50" value="" readOnly></td></tr>
		<tr><th align="left">E-Mail: </th><td><input type="text" class="readOnly" id="mailcliente" name="mailcliente" size="50" value="" readOnly></td></tr>
		<tr><th align="left">CP: </th><td><input type="text" class="readOnly" id="cpcliente" name="cpcliente" size="10" value="" readOnly></td></tr>
		<tr><td>Subtotal</td><td><input type="text" id="subtotal" value="" class="readOnly" size="12" readonly></td></tr>
		<tr><td>IVA</td><td><input type="text" id="iva" value="" class="readOnly" size="12" readonly></td></tr>
		<tr><td>Total</td><td><input type="text" id="total" value="" class="readOnly" size="12" readonly></td></tr>
	</table>
	</div>'; 
	echo '<input type="hidden" name="cliente_facturar" id="cliente_facturar" value="">';
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	if($PlazaLocal!=1)
		echo '<td><a href="#" onClick="atcr(\'cobro_engomado.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
	if($nivelUsuario>1 && $VendeSeguros==1)
		echo '<td><a href="#" onClick="atcr(\'cobro_engomado.php\',\'\',\'11\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo Seguro</td><td>&nbsp;</td>';
	echo '<td><a href="#" onClick="atcr(\'cobro_engomado.php\',\'_blank\',\'102\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Corte</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'cobro_engomado.php\',\'_blank\',\'102.1\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Corte Detallado</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'cobro_engomado.php\',\'_blank\',\'103\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Corte HTML</td><td>&nbsp;</td>';
	if($nivelUsuario>3){
		echo '<td><a href="#" onClick="mostrarFacturar()"><img src="images/finalizar.gif" border="0"></a>Facturar</td>';
	}	
	echo '
		 </tr>';
	echo '</table>';
	echo '<table width="100%"><tr><td valign="top" width="50%">';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Ticket</td><td><input type="text" name="folio" id="folio" size="10" class="textField" value=""></td></tr>';
	echo '<tr><td>Placa</td><td><input type="text" name="placa" id="placa" size="10" class="textField" value=""></td></tr>';
	echo '<tr><td>Tipo de Certificado</td><td><select name="engomado" id="engomado"><option value="">Todos</option>';
	foreach($array_engomado as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">Todos</option>';
	$res=mysql_query("SELECT b.cve,b.usuario FROM cobro_engomado a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY b.usuario");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['usuario'].'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="">Todos</option>';
	foreach($array_estatus as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Tipo de Pago</td><td><select name="tipo_pago" id="tipo_pago"><option value="all" selected>Todos</option>';
	foreach($array_tipo_pago as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Tipo de Venta</td><td><select name="tipo_venta" id="tipo_venta"><option value="all" selected>Todos</option>';
	foreach($array_tipo_venta as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v['nombre'].'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Tipo de Combustible</td><td><select name="tipo_combustible" id="tipo_combustible"><option value="all" selected>Todos</option>';
	foreach($array_tipo_combustible as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Tiene Multa</td><td><select name="multa" id="multa"><option value="all" selected>Todos</option>';
	foreach($array_nosi as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Año Certificacion</td><td><select name="anio" id="anio"><option value="all">Todos</option>';
	$first=true;
	foreach($array_anios as $k=>$v){
			echo '<option value="'.$k.'"';
			if($first) echo ' selected';
			echo '>'.$v.'</option>';
			$first=false;
	}
	echo '</select></td></tr>';
	echo '<tr><td>Mostrar</td><td><select name="mostrar" id="mostrar"><option value="all">Todos</option><option value="1">Con certificado</option><option value="2">Sin certificado</option></select></td></tr>';
	echo '<tr><td>Tipo Cliente</td><td><select name="tipo_cliente" id="tipo_cliente"><option value="all" selected>Todos</option>';
	echo '<option value="-1">Particulares</option>';
	echo '<option value="0">Talleres</option>';
	echo '<option value="1">Agencias</option>';
	echo '</select></td></tr>';
	echo '</table>';
	echo '</td><td width="50%" valign="top" id="capacorte"></td></tr></table>';
	echo '<br>';
	if($PlazaLocal==1)echo '<h2>La captura de la venta en esta plaza es de forma local</h2>';
	echo '<h2>Las modificaciones a la operación del sistema se deberán enviar al correo: <font color="BLUE">contacto@hilosnegros.com.mx</font> con el L.C. Arturo Galicia de la O</h2>';
	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros(btn)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","cobro_engomado.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&btn="+btn+"&tipo_cliente="+document.getElementById("tipo_cliente").value+"&folio="+document.getElementById("folio").value+"&tipo_venta="+document.getElementById("tipo_venta").value+"&anio="+document.getElementById("anio").value+"&multa="+document.getElementById("multa").value+"&tipo_pago="+document.getElementById("tipo_pago").value+"&tipo_combustible="+document.getElementById("tipo_combustible").value+"&estatus="+document.getElementById("estatus").value+"&mostrar="+document.getElementById("mostrar").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&placa="+document.getElementById("placa").value+"&engomado="+document.getElementById("engomado").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					datos = objeto.responseText.split("|*|");
					document.getElementById("capacorte").innerHTML = datos[0];
					document.getElementById("Resultados").innerHTML = datos[1];
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
	buscarRegistros(0); //Realizar consulta de todos los registros al iniciar la forma.
		
	function cambiarDocumento(cvecobro,documento)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","cobro_engomado.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&cvecobro="+cvecobro+"&documento="+documento+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}

	function guardarPlaca(folio,valor_anterior){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","cobro_engomado.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=40&campo=placa&folio="+folio+"&valor_anterior="+valor_anterior+"&valor="+document.getElementById("placa_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros(1);}
			}
		}
	}

	function guardarTipoPago(folio,valor_anterior){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","cobro_engomado.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=40&campo=tipo_pago&folio="+folio+"&valor_anterior="+valor_anterior+"&valor="+document.getElementById("tipo_pago_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros(1);}
			}
		}
	}

	function guardarDepositante(folio,valor_anterior){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","cobro_engomado.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=40&campo=depositante&folio="+folio+"&valor_anterior="+valor_anterior+"&valor="+document.getElementById("depositante_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros(1);}
			}
		}
	}

	function guardarAnio(folio,valor_anterior){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","cobro_engomado.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=40&campo=anio&folio="+folio+"&valor_anterior="+valor_anterior+"&valor="+document.getElementById("anio_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros(1);}
			}
		}
	}
	
	function mostrarFacturar(){
		if($(".chksfacturar").is(":checked")){
			var total = 0;
			$(".chksfacturar").each(function(){
				if(this.checked){
					total += ($(this).attr("monto")/1);
				}
			});
			subtotal = total*100/116;
			subtotal = subtotal.toFixed(2);
			iva = total - subtotal;
			iva = iva.toFixed(2);
			$("#total").val(total);
			$("#subtotal").val(subtotal);
			$("#iva").val(iva);
			$("#dialog").dialog("open");
		}
		else{
			alert("Necesita seleccionar al menos una venta");
		}
	}
	
	$("#dialog").dialog({ 
		bgiframe: true,
		autoOpen: false,
		modal: true,
		width: 600,
		height: 300,
		autoResize: true,
		position: "center",
		beforeClose: function( event, ui ) {
			document.forma.cliente_facturar.value="";
			$("#rfccliente").val("");
			$("#mailcliente").val("");
			$("#cpcliente").val("");
			$("#nombrecliente").val("");
			$("#subtotal").val("");
			$("#iva").val("");
			$("#total").val("");
		},
		buttons: {
			"Aceptar": function(){ 
				if(document.forma.cliente_facturar.value==""){
					alert("Necesita seleccionar al cliente");
				}
				else{
					atcr("cobro_engomado.php","",12,0);
				}
			},
			"Cerrar": function(){ 
				document.forma.cliente_facturar.value="";
				$("#rfccliente").val("");
				$("#nombrecliente").val("");
				$("#mailcliente").val("");
				$("#cpcliente").val("");
				$("#subtotal").val("");
				$("#iva").val("");
				$("#total").val("");
				$(this).dialog("close"); 
			}
		},
	}); 
	
	var ac_config = {
		source: "facturas.php?ajax=2&plazausuario='.$_POST['plazausuario'].'",
		select: function(event, ui){
			$("#rfccliente").val(ui.item.rfc);
			$("#mailcliente").val(ui.item.email);
			$("#cpcliente").val(ui.item.codigopostal);
			$("#nombrecliente").val($("<div />").html(ui.item.nombre).text());
			$("#cliente_facturar").val(ui.item.cve);
		},
		minLength:3
	};
	$("#buscador").autocomplete(ac_config);

	var ac_config2 = {
		source: "cobro_engomado.php?ajax=50&plazausuario='.$_POST['plazausuario'].'",
		minLength:3
	};
	$("#placa").autocomplete(ac_config2);
	
	function validarCancelacion(cvecobro){
		regresar = false;
		$.ajax({
		  url: "pedidos.php",
		  type: "POST",
		  async: false,
		  data: {
			plazausuario: $("#plazausuario").val(),
			cvecobro: cvecobro,
			ajax: 30
		  },
		  success: function(data) {
			if(data=="1") regresar=true;
			else regresar=false;
		  }
		});
		return regresar;
	}
	
	</Script>
	';

	
}
	
bottom();

if($cvecobro>0){
		echo '<script>atcr(\'cobro_engomado.php\',\'_blank\',\'101\','.$cvecobro.');</script>';
	}
?>

