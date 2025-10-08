<?php 
include ("main.php"); 

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$res=mysql_query("SELECT local FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];

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

$res = mysql_query("SELECT * FROM anios_certificados where cve=2 ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=2015;
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
		$select= " SELECT a.*,b.cve as certificado, b.certificado as holograma,b.engomado as engomado_entrega FROM cobro_engomado a LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
		WHERE a.plaza='".$_POST['plazausuario']."'";
		if ($_POST['folio']!=""){ 
			//$select.=" AND a.cve='".$_POST['cve']."' "; 
			$row = mysql_fetch_array(mysql_query("SELECT placa FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'"));
			$select.=" AND a.placa='".$row['placa']."' "; 
		}
		elseif ($_POST['placa']!=""){ 
			$select.=" AND a.placa='".$_POST['placa']."' "; 
		}
		else{
			$select.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
			if ($_POST['usuario']!="") { $select.=" AND a.usuario='".$_POST['usuario']."' "; }
			if ($_POST['engomado']!="") { $select.=" AND a.engomado='".$_POST['engomado']."' "; }
			if ($_POST['estatus']!="") { $select.=" AND a.estatus='".$_POST['estatus']."' "; }
			if ($_POST['tipo_pago']!="all") { $select.=" AND a.tipo_pago='".$_POST['tipo_pago']."' "; }
			if ($_POST['multa']!="all") { $select.=" AND a.multa='".$_POST['multa']."' "; }
			if ($_POST['tipo_combustible']!="all") { $select.=" AND a.tipo_combustible='".$_POST['tipo_combustible']."' "; }
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
			<input type="checkbox" onClick="if(this.checked) $(\'.chksfacturar\').attr(\'checked\',\'checked\'); else $(\'.chksfacturar\').removeAttr(\'checked\');"></th><th>Ticket</th><th>Fecha</th><!--<th>Referencia Maquina Registradora--></th><th>Placa</th>
			<th>Tiene Multa</th><th>Tipo de Certificado</th><th>Monto</th><th>A&ntilde;o Certificacion</th><th>Tipo de Pago</th><th>Tipo Combustible</th><!--<th>Documento</th>--><th>Factura</th><th>Entrega Certificado</th><th>Holograma Entregado</th><th>Usuario</th><th>Motivo Cancelacion</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
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
					if(nivelUsuario()>2 && $PlazaLocal != 1 && $row['factura']==0 && $row['certificado']=="" && ($row['fecha']==fechaLocal() || $_POST['cveusuario']==1))
						echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el cobro\')) atcr(\'cobro_engomado.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				}	
				echo '</td>';
				if(($row['tipo_pago']==2 || $row['tipo_pago']==6) && $_POST['tipo_pago']=="all") $row['monto']=0;
				echo '<td align="center" width="40" nowrap>&nbsp;';
				if($row['estatus']!='C' && $row['tipo_pago']==1 && $row['factura']==0 && $row['certificado']>0){
					echo '<input type="checkbox" name="facturar[]" value="'.$row['cve'].'" monto="'.$row['monto'].'" class="chksfacturar">';
				}
				echo '&nbsp;</td>';
				if($_POST['folio']==$row['cve'])
					echo '<td align="center"><font color="BLUE">'.htmlentities($row['cve']).'</font></td>';
				else
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
				echo '<td align="center">'.number_format($row['monto'],2).'</td>';
				echo '<td align="center">'.htmlentities($array_anios[$row['anio']]).'</td>';
				echo '<td align="center">'.htmlentities($array_tipo_pago[$row['tipo_pago']]).'</td>';
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
				echo '<td align="center">'; echo ($row['factura']==0)?'&nbsp;':$row['factura']; echo '</td>';
				echo '<td align="center">'.$row['certificado'].'</td>';
				echo '<td align="center">'.$row['holograma'].'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '<td>'.htmlentities(utf8_encode($row['obscan'])).'</td>';
				echo '</tr>';
				$t+=$row['monto'];
			}
			echo '	
				<tr>
				<td colspan="7" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</td>
				<td colspan="8" bgcolor="#E9F2F8">&nbsp;</td>
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
	
	echo '<br><br>GRAN TOTAL IMP: '.number_format($t2+$row2[0]-$row1[0]+$t31,2).'';
	echo '<br>TOTAL A DEPOSITAR IMP: '.number_format($t2-$t4+$row2[0]-$row1[0]+$t31,2).'';
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
	if($_POST['fecha_ini']==$_POST['fecha_fin']) $texto.=" FECHA: ".$_POST['fecha_ini'];
	else $texto.=" FECHA INI: ".$_POST['fecha_ini']."|FECHA FIN: ".$_POST['fecha_fin'];
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
	$texto.=' CANT: '.$row1[1].', IMP: '.number_format($row1[0],2).'||';
	
	$texto.=' GRAN TOTAL IMP: '.number_format($t2+$row2[0]-$row1[0]+$t31,2).'|';
	$texto.=' TOTAL A DEPOSITAR IMP: '.number_format($t2-$t4+$row2[0]-$row1[0]+$t31,2).'||';
	
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
	$t1=$t2=$t3=$t4=0;
	
	$res=mysql_query("SELECT engomado,SUM(IF(tipo_pago!=6,monto,0)),COUNT(cve),SUM(IF(tipo_pago IN (2),monto,0)),SUM(IF(tipo_pago IN (2),1,0)) FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' ".$filtro." GROUP BY engomado");
	while($row=mysql_fetch_array($res)){
		echo '<h2>'.$array_engomado[$row['engomado']].'</h2>';
		echo '<table><tr><th>Tipo Pago</th><th>Cantidad</th><th>Importe</th></tr>';
		$res1=mysql_query("SELECT tipo_pago,SUM(monto),COUNT(cve)  FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND engomado='".$row['engomado']."' AND estatus!='C' ".$filtro." GROUP BY tipo_pago");
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
	$res1=mysql_query("SELECT SUM(a.devolucion),COUNT(a.cve)  FROM devolucion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' $filtro");
	$row1=mysql_fetch_array($res1);
	echo ' CANT: '.$row1[1].', IMP: '.number_format($row1[0],2).'';
	
	echo '<h2>RECUPERACION</h2>';
	$res2=mysql_query("SELECT SUM(a.recuperacion),COUNT(a.cve)  FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' $filtro");
	$row2=mysql_fetch_array($res2);
	echo ' CANT: '.$row2[1].', IMP: '.number_format($row2[0],2).'';
	
	echo '<h2>PAGOS EN CAJA</h2>';
	echo '<table><tr><th>Tipo Pago</th><th>Cantidad</th><th>Importe</th></tr>';
	$t31=$t32=0;
	$res3=mysql_query("SELECT forma_pago,SUM(monto),COUNT(cve)  FROM pagos_caja a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro GROUP BY forma_pago");
	while($row3=mysql_fetch_array($res3)){
		echo '<tr><td>'.$array_forma_pago[$row3['forma_pago']].'</td><td>'.$row3[2].'</td><td>'.number_format($row3[1],2).'</td></tr>';
		$t31+=$row3[1];
		$t32+=$row3[2];
	}
	echo '<tr><td>TOTAL</td><td>'.$t32.'</td><td>'.number_format($t31,2).'</td></tr></table>';

	echo '<h2>DEVOLUCION AJUSTE</h2>';
	$res4=mysql_query("SELECT SUM(monto),COUNT(cve)  FROM devolucion_ajuste a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro");
	$row4=mysql_fetch_array($res4);
	echo ' CANT: '.$row4[1].', IMP: '.number_format($row4[0],2).'';
	
	echo '<br><br>GRAN TOTAL IMP: '.number_format($t2+$row2[0]-$row1[0]+$t31-$row4[0],2).'';
	echo '<br>TOTAL A DEPOSITAR IMP: '.number_format($t2-$t4+$row2[0]-$row1[0]+$t31-$row4[0],2).'';
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
	if($_POST['fecha_ini']==$_POST['fecha_fin']) $texto.=" FECHA: ".$_POST['fecha_ini'];
	else $texto.=" FECHA INI: ".$_POST['fecha_ini']."|FECHA FIN: ".$_POST['fecha_fin'];
	$filtro="";
	if ($_POST['usuario']!=""){ 
		$filtro.=" AND a.usuario='".$_POST['usuario']."' "; 
		$texto.='|USUARIO: '.$array_usuario[$_POST['usuario']];
	}
	$texto.='|| INGRESOS||';
	$t1=$t2=$t3=$t4=0;
	$res=mysql_query("SELECT engomado,SUM(IF(tipo_pago!=6,monto,0)),COUNT(cve),SUM(IF(tipo_pago IN (2),monto,0)),SUM(IF(tipo_pago IN (2),1,0)) FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro GROUP BY engomado");
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
	$res2=mysql_query("SELECT SUM(a.recuperacion),COUNT(a.cve)  FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' $filtro");
	$row2=mysql_fetch_array($res2);
	$texto.=' CANT: '.$row2[1].', IMP: '.number_format($row2[0],2).'|';
	
	$texto.=' PAGOS EN CAJA ';
	$t31=$t32=0;
	$res3=mysql_query("SELECT forma_pago,SUM(monto),COUNT(cve)  FROM pagos_caja a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro GROUP BY forma_pago");
	while($row3=mysql_fetch_array($res3)){
		$texto.=" ".$array_forma_pago[$row3['forma_pago']].' CANT: '.$row3[2].', IMP: '.number_format($row3[1],2).'|';
		$t31+=$row3[1];
		$t32+=$row3[2];
	}
	$texto.=' TOTAL  CANT: '.$t32.', IMP: '.number_format($t31,2).'||';
	
	$texto.='EGRESOS || DEVOLUCION ';
	$res1=mysql_query("SELECT SUM(a.devolucion),COUNT(a.cve)  FROM devolucion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' $filtro");
	$row1=mysql_fetch_array($res1);
	$texto.=' CANT: '.$row1[1].', IMP: '.number_format($row1[0],2).'|';
	$texto.='DEVOLUCION AJUSTE';
	$res4=mysql_query("SELECT SUM(monto),COUNT(cve)  FROM devolucion_ajuste a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro");
	$row4=mysql_fetch_array($res4);
	$texto.=' CANT: '.$row4[1].', IMP: '.number_format($row4[0],2).'||';
	
	$texto.=' GRAN TOTAL IMP: '.number_format($t2+$row2[0]-$row1[0]+$t31-$row4[0],2).'|';
	$texto.=' TOTAL A DEPOSITAR IMP: '.number_format($t2-$t4+$row2[0]-$row1[0]+$t31-$row4[0],2).'||';
	
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
	if($_POST['fecha_ini']==$_POST['fecha_fin']) $texto.=" FECHA: ".$_POST['fecha_ini'];
	else $texto.=" FECHA INI: ".$_POST['fecha_ini']."|FECHA FIN: ".$_POST['fecha_fin'];
	$filtro="";
	if ($_POST['usuario']!=""){ 
		$filtro.=" AND a.usuario='".$_POST['usuario']."' "; 
		$texto.='|USUARIO: '.$array_usuario[$_POST['usuario']];
	}
	$texto.='||';
	$t1=$t2=$t3=$t4=0;
	$res=mysql_query("SELECT engomado,SUM(IF(tipo_pago!=6,monto,0)),COUNT(cve),SUM(IF(tipo_pago IN (2),monto,0)),SUM(IF(tipo_pago IN (2),1,0)) FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro GROUP BY engomado");
	while($row=mysql_fetch_array($res)){
		$texto.=" ".$array_engomado[$row['engomado']].'|';
		$res1=mysql_query("SELECT tipo_pago,SUM(monto),COUNT(cve)  FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND engomado='".$row['engomado']."' AND estatus!='C' $filtro GROUP BY tipo_pago");
		while($row1=mysql_fetch_array($res1)){
			$texto.=" ".$array_tipo_pago[$row1['tipo_pago']].' CANT: '.$row1[2].', IMP: '.number_format($row1[1],2).'|';
		}
		$texto.=' TOTAL CONT CANT: '.($row[2]-$row[4]).', IMP: '.number_format($row[1]-$row[3],2).'|';
		$texto.=' TOTAL CRED CANT: '.$row[4].', IMP: '.number_format($row[3],2).'|';
		$texto.=' TOTAL      CANT: '.$row[2].', IMP: '.number_format($row[1],2).'||';
		$t1+=$row[2];
		$t2+=$row[1];
		$t3+=$row[4];
		$t4+=$row[3];
	}
	$texto.=' GRAN TOTAL VENTA CONT CANT: '.($t1-$t3).', IMP: '.number_format($t2-$t4,2).'|';
	$texto.=' GRAN TOTAL VENTA CRED CANT: '.$t3.', IMP: '.number_format($t4,2).'|';
	$texto.=' GRAN TOTAL VENTA      CANT: '.$t1.', IMP: '.number_format($t2,2).'||';
	$texto.=' DEVOLUCION ';
	$res1=mysql_query("SELECT SUM(a.devolucion),COUNT(a.cve)  FROM devolucion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' $filtro");
	$row1=mysql_fetch_array($res1);
	$texto.=' CANT: '.$row1[1].', IMP: '.number_format($row1[0],2).'||';
	
	$texto.=' RECUPERACION ';
	$res2=mysql_query("SELECT SUM(a.recuperacion),COUNT(a.cve)  FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' $filtro");
	$row2=mysql_fetch_array($res1);
	$texto.=' CANT: '.$row2[1].', IMP: '.number_format($row2[0],2).'||';
	
	$texto.=' PAGOS EN CAJA ';
	echo '<table><tr><th>Tipo Pago</th><th>Cantidad</th><th>Importe</th></tr>';
	$t31=$t32=0;
	$res3=mysql_query("SELECT tipo_pago,SUM(monto),COUNT(cve)  FROM pagos_caja a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro GROUP BY tipo_pago");
	while($row3=mysql_fetch_array($res3)){
		$texto.=" ".$array_tipo_pago[$row3['tipo_pago']].' CANT: '.$row3[2].', IMP: '.number_format($row3[1],2).'|';
		$t31+=$row3[1];
		$t32+=$row3[2];
	}
	$texto.=' GRAN TOTAL       CANT: '.$t32.', IMP: '.number_format($t31,2).'||';
	
	$texto.=' GRAN TOTAL IMP: '.number_format($t2+$row2[0]-$row1[0]+$t31,2).'|';
	$texto.=' TOTAL A DEPOSITAR IMP: '.number_format($t2-$t4+$row2[0]-$row1[0]+$t31,2).'||';
	
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
	$texto.=chr(27).'!'.chr(8)." TIPO PAGO: ".$array_tipo_pago[$row['tipo_pago']];
	$texto.='|';
	if($row['tipo_pago'] == 2 || $row['tipo_pago'] == 6){
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
	$res1=mysql_query("SELECT * FROM claves_facturacion WHERE plaza='".$row['plaza']."' AND ticket='".$row['cve']."'");
	if($row1=mysql_fetch_array($res1)){
		$texto.=chr(27).'!'.chr(8)."     CLAVE FACTURACION:|".$row1['cve'];
		$texto.='|';
		$texto.=chr(27).'!'.chr(8)."     FECHA LIMITE FACTURACION:|    ".date( "Y-m-d" , strtotime ( "+1 month" , strtotime(date("Y-m").'-06') ) );
		$texto.='|';
		$texto.=chr(27).'!'.chr(8)."     PAGINA PARA FACTURAR:|    www.verifactura.com|";
	}
	if($row['monto']==0){
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
	
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$texto.'&logo='.str_replace(' ','',$array_plaza[$row['plaza']]).'&barcode=1'.sprintf("%011s",(intval($row['cve']))).'&copia=1" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",2000);</script>';
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
		$res = mysql_query("SELECT folio_inicial FROM foliosiniciales WHERE plaza='".$_POST['plazausuario']."' AND tipo=0 AND tipodocumento=1");
		$row = mysql_fetch_array($res);
		$res1 = mysql_query("SELECT cve FROM facturas WHERE plaza='".$_POST['plazausuario']."'");
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
		}
		$cvefact=mysql_insert_id();
	
		$documento=array();
		require_once("nusoap/nusoap.php");
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
								$mail->Subject = "Factura ".$cvefact;
								$mail->Body = "Factura ".$cvefact;
								//$mail->AddAddress(trim($emailenvio));
								$correos = explode(",",trim($emailenvio));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
								$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
								$mail->Send();
							}	
							if($rowempresa['email']!=""){
								$mail = new PHPMailer();
								$mail->Host = "localhost";
								$mail->From = "verificentros@verificentros.net";
								$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
								$mail->Subject = "Factura ".$cvefact;
								$mail->Body = "Factura ".$cvefact;
								//$mail->AddAddress(trim($rowempresa['email']));
								$correos = explode(",",trim($rowempresa['email']));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
								$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
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
							SET 
							placa='".$_POST['placa']."',modelo='".$_POST['modelo']."',tipo_pago='".$_POST['tipo_pago']."',multa='".$_POST['multa']."',
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
				$insert = " INSERT cobro_engomado 
								SET 
								plaza = '".$_POST['plazausuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',registro_web='".$_POST['registro_web']."',
								placa='".$_POST['placa']."',engomado='".$_POST['engomado']."',monto='".$_POST['monto']."',tipo_combustible='".$_POST['tipo_combustible']."',
								modelo='".$_POST['modelo']."',tipo_pago='".$_POST['tipo_pago']."',documento='".$_POST['documento']."',depositante='".$_POST['depositante']."',
								usuario='".$_POST['cveusuario']."',estatus='A',motivo_intento='".$_POST['motivo_intento']."',
								referencia_maquina_registradora='".$_POST['referencia_maquina_registradora']."',
								obs='".$_POST['obs']."',multa='".$_POST['multa']."',anio='".$_POST['anio']."',descuento='$descuento'";
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
				if($_POST['monto']>0){
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
		echo '<table style="font-size:15px">';
		if($_POST['reg']==0){
			//echo '<tr><th align="left">Referencia Maquina Registradora</th><td><input type="text" name="referencia_maquina_registradora" id="referencia_maquina_registradora" class="textField" style="font-size:20px" size="50" value=""></td></tr>';
			//echo '<tr><th align="left">Placa</th><td><input type="password" name="placa" id="placa" autocomplete="off" class="textField" style="font-size:30px" size="10" value="'.$row['placa'].'" onKeyUp="this.value = this.value.toUpperCase();"></td></tr>';
			echo '<tr><th align="left">Placa</th><td><input type="text" name="placa" id="placa" autocomplete="off" class="textField placas" style="font-size:30px" size="10" value="'.$row['placa'].'" onKeyUp="if(event.keyCode==13){ traeRegistro();}else{this.value = this.value.toUpperCase();}"><b><font color="RED">Dar enter para traer el registro web</font></b></td></tr>';
			//echo '<tr><th align="left">Modelo</th><td><input type="text" name="modelo" id="modelo" class="textField" style="font-size:30px" size="10" value="'.$row['modelo'].'"></td></tr>';
			//echo '<tr><th align="left">Confirmacion Placa</th><td><input type="text" name="placa2" id="placa2" class="textField" style="font-size:30px" size="10" value="'.$row['placa'].'" onKeyUp="this.value = this.value.toUpperCase();"></td></tr>';
			/*echo '<tr><th align="left">Engomado</th><td><select name="engomado" id="engomado" style="font-size:20px" onChange="document.forma.monto.value=$(\'#engomado option[value=\\\'\'+this.value+\'\\\']\').attr(\'precio\');"><option value="0" precio="">Seleccione</option>';
			foreach($array_engomado as $k=>$v){
				echo '<option value="'.$k.'" precio="'.$array_engomadoprecio[$k].'">'.$v.'</option>';
			}
			echo '</select></td></tr>';*/
			echo '<tr><th align="left" style="font-size:18px">Tiene Multa</th><td><input type="checkbox" name="multa" id="multa" value="1" style="font-size:18px"></td></tr>';
			echo '<tr><th align="left">Año Certificado</th><td><select name="anio" id="anio" style="font-size:20px">';
			if(count($array_anios)>1) echo '<option value="0">Seleccione</option>';
			foreach($array_anios as $k=>$v){
				echo '<option value="'.$k.'"';
				if($row['anio'] == $k) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Tipo de Certificado</th><td><input type="hidden" name="engomado" id="engomado" value="'.$row['engomado'].'"><table><tr>';
			$i=0;
			foreach($array_engomado as $k=>$v){
				if($i==4){
					echo '</tr><tr>';
					$i=0;
				}
				echo '<td><input type="radio" name="auxengomado" id="auxengomado_'.$k.'" value="'.$k.'" onClick="if(this.checked){document.forma.engomado.value=this.value; document.forma.monto.value=\''.$array_engomadoprecio[$k].'\';mostrar_motivo_intento();}"';
				if($row['engomado']==$k) echo ' checked';
				echo '>'.$v.'&nbsp;&nbsp;&nbsp;</td>';
				$i++;
			}
			echo '</tr></table></td></tr>';
			echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="readOnly" size="10" style="font-size:12px" value="'.$row['monto'].'" readOnly></td></tr>';
			echo '<tr';
			if($row['monto']>0 || $_POST['reg']==0){
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
			echo '<tr><th align="left">Tipo de Pago</th><td><select name="tipo_pago" id="tipo_pago" style="font-size:20px" onChange="if(this.value==\'2\' || this.value==\'6\'){ $(\'#depositante\').parents(\'tr:first\').show();} else{$(\'#depositante\').parents(\'tr:first\').hide();document.forma.depositante.value=\'0\';}"><!--<option value="0">Seleccione</option>-->';
			foreach($array_tipo_pago as $k=>$v){
				echo '<option value="'.$k.'"';
				if($row['tipo_pago'] == $k) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr';
			if($row['tipo_pago']!=2 && $row['tipo_pago']!=6){
				echo ' style="display:none;"';
			}
			echo '><th align="left">Depositante</th><td><select name="depositante" id="depositante" style="font-size:20px"><option value="0">Seleccione</option>';
			foreach($array_depositantes as $k=>$v){
				echo '<option value="'.$k.'"';
				if($row['depositante']==$k) echo ' selected';
				echo '>'.$v.' (Saldo: '.number_format(saldo_depositante($k),2).')</option>';
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
			//echo '<tr><th align="left">Referencia Maquina Registradora</th><td><input type="text" name="referencia_maquina_registradora" id="referencia_maquina_registradora" class="textField" style="font-size:20px" size="50" value=""></td></tr>';
			//echo '<tr><th align="left">Placa</th><td><input type="password" name="placa" id="placa" autocomplete="off" class="textField" style="font-size:30px" size="10" value="'.$row['placa'].'" onKeyUp="this.value = this.value.toUpperCase();"></td></tr>';
			echo '<tr><th align="left">Placa</th><td><input type="text" name="placa" id="placa" autocomplete="off" class="textField placas" style="font-size:30px" size="10" value="'.$row['placa'].'" onKeyUp="this.value = this.value.toUpperCase();"></td></tr>';
			//echo '<tr><th align="left">Modelo</th><td><input type="text" name="modelo" id="modelo" class="textField" style="font-size:30px" size="10" value="'.$row['modelo'].'"></td></tr>';
			//echo '<tr><th align="left">Confirmacion Placa</th><td><input type="text" name="placa2" id="placa2" class="textField" style="font-size:30px" size="10" value="'.$row['placa'].'" onKeyUp="this.value = this.value.toUpperCase();"></td></tr>';
			/*echo '<tr><th align="left">Engomado</th><td><select name="engomado" id="engomado" style="font-size:20px" onChange="document.forma.monto.value=$(\'#engomado option[value=\\\'\'+this.value+\'\\\']\').attr(\'precio\');"><option value="0" precio="">Seleccione</option>';
			foreach($array_engomado as $k=>$v){
				echo '<option value="'.$k.'" precio="'.$array_engomadoprecio[$k].'">'.$v.'</option>';
			}
			echo '</select></td></tr>';*/
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
			echo '<tr><th align="left">Tipo de Certificado</th><td><input type="hidden" name="engomado" id="engomado" value="'.$row['engomado'].'"><table><tr>';
			$i=0;
			foreach($array_engomado as $k=>$v){
				if($i==4){
					echo '</tr><tr>';
					$i=0;
				}
				echo '<td><input type="radio" name="auxengomado" id="auxengomado_'.$k.'" value="'.$k.'" onClick="if(this.checked){document.forma.engomado.value=this.value; document.forma.monto.value=\''.$array_engomadoprecio[$k].'\';}"';
				if($row['engomado']==$k) echo ' checked';
				else echo ' disabled';
				echo '>'.$v.'&nbsp;&nbsp;&nbsp;</td>';
				$i++;
			}
			echo '</tr></table></td></tr>';
			echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="readOnly" size="10" style="font-size:12px" value="'.$row['monto'].'" readOnly></td></tr>';
			echo '<tr';
			if($row['monto']>0 || $_POST['reg']==0){
				echo ' style="display:none;"';
			}
			echo '><th align="left">Motivo de Intento</th><td><select name="motivo_intento" id="motivo_intento" style="font-size:20px"><option value="0">Seleccione</option>';
			foreach($array_motivos_intento as $k=>$v){
				if($row['motivo_intento'] == $k){
					echo '<option value="'.$k.'"';
					echo ' selected';
					echo '>'.$v.'</option>';
				}
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Tipo de Combustible</th><td><select name="tipo_combustible" id="tipo_combustible" style="font-size:20px">';
			foreach($array_tipo_combustible as $k=>$v){
				echo '<option value="'.$k.'"';
				if($row['tipo_combustible'] == $k) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Tipo de Pago</th><td><select name="tipo_pago" id="tipo_pago" style="font-size:20px" onChange="if(this.value==\'2\' || this.value==\'6\'){ $(\'#depositante\').parents(\'tr:first\').show();} else{$(\'#depositante\').parents(\'tr:first\').hide();document.forma.depositante.value=\'0\';}"><!--<option value="0">Seleccione</option>-->';
			foreach($array_tipo_pago as $k=>$v){
				if($row['tipo_pago'] == $k || $_POST['cveusuario']==1){
					echo '<option value="'.$k.'"';
					if($row['tipo_pago'] == $k)	echo ' selected';
					echo '>'.$v.'</option>';
				}
			}
			echo '</select></td></tr>';
			echo '<tr';
			if($row['tipo_pago']!=2 && $row['tipo_pago']!=6){
				echo ' style="display:none;"';
			}
			echo '><th align="left">Depositante</th><td><select name="depositante" id="depositante" style="font-size:20px"><option value="0">Seleccione</option>';
			foreach($array_depositantes as $k=>$v){
				echo '<option value="'.$k.'"';
				if($row['depositante']==$k) echo ' selected';
				echo '>'.$v.' (Saldo: '.number_format(saldo_depositante($k),2).')</option>';
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
				function validar(reg){
					if(document.forma.placa.value==""){
						$("#panel").hide();
						alert("Necesita ingresar la placa");
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
					else if(document.forma.documento.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el documento");
					}
					else if((document.forma.monto.value/1)==0 && document.forma.motivo_intento.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el motivo de intento");
					}
					else if(validarPlaca() == false){
						$("#panel").hide();
						alert("La placa ya tiene un intento");
					}
					else{
						atcr("cobro_engomado.php","",2,reg);
					}
				}
				
				function mostrar_motivo_intento(){
					if((document.forma.monto.value/1)==0){
						$("#motivo_intento").parents("tr:first").show();
					}
					else{
						$("#motivo_intento").parents("tr:first").hide();
						document.forma.motivo_intento.value="0";
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
	echo '<td><a href="#" onClick="atcr(\'cobro_engomado.php\',\'_blank\',\'102\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Corte</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'cobro_engomado.php\',\'_blank\',\'102.1\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Corte Detallado</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'cobro_engomado.php\',\'_blank\',\'103\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Corte HTML</td><td>&nbsp;</td>';
	if(nivelUsuario()>3){
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
	echo '<tr><td>Año Certificacion</td><td><select name="anio" id="anio"><option value="all" selected>Todos</option>';
	foreach($array_anios as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Mostrar</td><td><select name="mostrar" id="mostrar"><option value="all">Todos</option><option value="1">Con certificado</option><option value="2">Sin certificado</option></select></td></tr>';
	echo '</table>';
	echo '</td><td width="50%" valign="top" id="capacorte"></td></tr></table>';
	echo '<br>';
	if($PlazaLocal==1)echo '<h2>La captura de la venta en esta plaza es de forma local</h2>';
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
			objeto.send("ajax=1&btn="+btn+"&folio="+document.getElementById("folio").value+"&anio="+document.getElementById("anio").value+"&multa="+document.getElementById("multa").value+"&tipo_pago="+document.getElementById("tipo_pago").value+"&tipo_combustible="+document.getElementById("tipo_combustible").value+"&estatus="+document.getElementById("estatus").value+"&mostrar="+document.getElementById("mostrar").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&placa="+document.getElementById("placa").value+"&engomado="+document.getElementById("engomado").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
	
	</Script>
	';

	
}
	
bottom();

if($cvecobro>0){
		echo '<script>atcr(\'cobro_engomado.php\',\'_blank\',\'101\','.$cvecobro.');</script>';
	}
?>

