<?php
include("main.php");
include("imp_prefactura.php");
//ARREGLOS

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);
$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$importe_iva=round($row['precio']*16/116,2);
	$array_engomadoprecio[$row['cve']]=$row['precio']-$importe_iva;
}
$res=mysql_query("SELECT * FROM bancos");
while($row=mysql_fetch_array($res)){
	$array_bancos[$row['cve']]=$row['nombre'];
}


$array_clientes=array();
$res=mysql_query("SELECT * FROM clientes WHERE plaza='".$_POST['plazausuario']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_clientes[$row['cve']]=$row['nombre'];
	if($row['rfc']=="" || $row['nombre']=="" || $row['calle']=="" || $row['numexterior']=="" || $row['colonia']=="" || $row['municipio']=="" || $row['codigopostal']=="")
		$array_colorcliente[$row['cve']] = "#FF0000";
	else
		$array_colorcliente[$row['cve']] = "#000000";
}
function mestexto($fec){
	global $array_meses;
	$datos=explode("-",$fec);
	return $array_meses[intval($datos[1])].' '.$datos[0];
}
//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);

$abono=0;

if($_POST['cmd']==101){
	generaPreFacturaPdf($_POST['plazausuario'],$_POST['reg'],1);
	exit();
}

if($_POST['cmd']==103){
	unlink($_POST['reg']);
	echo '<script>window.close();</script>';
	exit();
}

if($_POST['ajax']==1){
	$filtro="";
	$select= " SELECT a.* FROM prefacturas as a WHERE a.plaza='".$_POST['plazausuario']."' ";
	//if($_POST['tipo']!="all") $select.=" AND a.tipo='".$_POST['tipo']."'";
	if($_POST['fecha_ini']!='') $select.=" AND a.fecha>='".$_POST['fecha_ini']."'";
	if($_POST['fecha_fin']!='') $select.=" AND a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['cliente']!="all") $select.=" AND a.cliente='".$_POST['cliente']."'";
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	if ($_POST['estatus']!="all") { 
		if($_POST['estatus']=='A') $select.=" AND a.estatus IN ('','A')";
		else $select.=" AND a.estatus='".$_POST['estatus']."'"; 
	}
	$select.=" ORDER BY a.cve DESC";
	$rsabonos=mysql_query($select) or die(mysql_error());
	if(mysql_num_rows($rsabonos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$c=13;
		if(nivelUsuario()>1) $c++;
		if($rowempresa['localidad_id']==2) $c++;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.mysql_num_rows($rsabonos).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		if(nivelUsuario()>1){
			echo '<th><input type="checkbox" name="selt" value="1" onClick="if(this.checked) $(\'.checks\').attr(\'checked\',\'checked\'); else $(\'.checks\').removeAttr(\'checked\');"></th>';
		}
		echo '<th>Folio</th><th>Fecha</th>';
		if($rowempresa['localidad_id']==2) echo '<th>Folio de Venta</th>';
		echo '<th>Cliente</th><th>Tipo Pago</th><th>Subtotal</th>
		<th>Iva</th><th>Total</th><!--<th>Retencion I.S.R.</th><th>Retencion I.V.A.</th><th>Total</th>-->
		<th>Usuario<br>';
		echo '<select name="usuario" onchange="document.forma.usu.value=this.value;buscarRegistros();"><option value="all">---Todos---</option>';
		$res1=mysql_query("SELECT a.usuario FROM prefacturas as a WHERE plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY a.usuario");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['usuario'].'"';
			if($row1['usuario']==$_POST['usu']) echo ' selected';
			echo '>'.$array_usuario[$row1['usuario']].'</option>';
		}
		echo '</select></th></tr>'; 
		$sumacargo=array();
		$x=0;
		while ($Abono=mysql_fetch_array($rsabonos)){	
			rowb();
			$estatus='';
			if($Abono['estatus']=='C'){
				$estatus='(CANCELADO)';
				if($_POST['estatus']!='C'){
					$Abono['subtotal']=0;
					$Abono['iva']=0;
					$Abono['total']=0;
					$Abono['iva_retenido']=0;
				}
				echo '<td align="center">CANCELADO<br>';
				echo '<a href="#" onClick="atcr(\'prefacturas.php\',\'_blank\',\'101\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				echo '</td>';
				if(nivelUsuario()>1){
					echo '<td>&nbsp;</td>';
				}
				
			}
			elseif($Abono['estatus']=="F"){
				echo '<td align="center" width="40" nowrap>FACTURADA<br>';
				echo '<a href="#" onClick="atcr(\'prefacturas.php\',\'_blank\',\'101\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				//echo '<a href="#" onClick="if(confirm(\'Esta seguro que desea timbrar?\')){$(\'#panel\').show();atcr(\'prefacturas.php\',\'\',\'5\',\''.$Abono['cve'].'\');}"><img src="images/validosi.gif" border="0" title="Timbrar '.$Abono['folio'].'"></a>';
				
				echo '</td>';
				if(nivelUsuario()>1){
					echo '<td>&nbsp;</td>';
				}
			}
			else{
				echo '<td align="center" width="40" nowrap>';
				echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'prefacturas.php\',\'_blank\',\'101\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				if(nivelUsuario()>2){
					echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')){$(\'#panel\').show();atcr(\'prefacturas.php\',\'\',\'3\',\''.$Abono['cve'].'\');}"><img src="images/validono.gif" border="0" title="Cancelar '.$Abono['folio'].'"></a>';
				}
				echo '</td>';
				if(nivelUsuario()>1){
					echo '<td align="center"><input type="checkbox" class="checks" name="checksf[]" value="'.$Abono['cve'].'"></td>';
				}
			}
			echo '<td align="center">'.$Abono['cve'].'</td>';
			echo '<td align="center">'.$Abono['fecha'].' '.$Abono['hora'].'</td>';
			if($rowempresa['localidad_id']==2) echo '<td align="center">'.$Abono['venta'].'</td>';
			echo '<td>'.htmlentities(utf8_encode($array_clientes[$Abono['cliente']])).'</td>';
			echo '<td>'.htmlentities($array_tipo_pago[$Abono['tipo_pago']]).'</td>';
			echo '<td align="right">'.number_format($Abono['subtotal'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'],2).'</td>';
			//echo '<td align="right">'.number_format($Abono['isr_retenido'],2).'</td>';
			//echo '<td align="right">'.number_format($Abono['iva_retenido'],2).'</td>';
			//echo '<td align="right">'.number_format($Abono['total'],2).'</td>';
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$sumacargo[0]+=$Abono['subtotal'];
			$sumacargo[1]+=$Abono['iva'];
			$sumacargo[2]+=$Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'];
			//$sumacargo[3]+=$Abono['isr_retenido'];
			//$sumacargo[4]+=$Abono['iva_retenido'];
			//$sumacargo[5]+=$Abono['total'];
		}
		$c=4;
		if(nivelUsuario()>1) $c++;
		if($rowempresa['localidad_id']==2) $c++;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.$x.' Registro(s)</td>';
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		foreach($sumacargo as $k=>$v){
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
		}
		echo '<td bgcolor="#E9F2F8" colspan="1">&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
	}
	else {
		echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
	}
	exit();
}

if($_GET['ajax']==2){
	$select= " SELECT rfc, nombre, cve, codigopostal, email FROM clientes WHERE plaza='".$_GET['plazausuario']."' AND (";
	//$select.=" nomina LIKE '%".$_GET['term']."%' OR ";
	$select.=" rfc LIKE '%".$_GET['term']."%' OR nombre like '%".$_GET['term']."%')";
	//$select.=" concat(apellidop,' ',apellidom,' ',nombre) like '%".$_GET['term']."%')";
	$select .= " ORDER BY rfc";
	$res=mysql_query($select) or die(mysql_error());
  $matches = array();
  while($row=mysql_fetch_assoc($res)){
    // Adding the necessary "value" and "label" fields and appending to result set
    $row['value'] = "";
    $row['label'] = "{$row['rfc']}, ".utf8_encode($row['nombre'])."";
	$row['nombre'] = utf8_encode($row['nombre']);
	$cuentas = '<option value="0">Seleccione</option>';
	$res1=mysql_query("SELECT * FROM clientes_cuentas WHERE cliente='".$row['cve']."'");
	while($row1=mysql_fetch_array($res1)){
		$cuentas.='<option value="'.$row1['cve'].'">'.$array_bancos[$row1['banco']].' '.$row1['cuenta'].'</option>';
	}
	$row['cuentas'] = $cuentas;
    $matches[] = $row;
  } 
  // Truncate, encode and return the results
  $matches = array_slice($matches, 0, 15);
  print json_encode($matches);
	exit();
}


if($_POST['cmd']==6){
	require_once("../phpmailer/class.phpmailer.php");
	require_once("../nusoap/nusoap.php");
	require_once("imp_factura.php");
	$resplaza = mysql_query("SELECT * FROM plazas WHERE cve='".$_POST['plazausuario']."'");
	$rowplaza = mysql_fetch_array($resplaza);
	foreach($_POST['checksf'] as $cveprefact){
		$res = mysql_query("SELECT * FROM prefacturas WHERE plaza='".$_POST['plazausuario']."' AND cve='".$cveprefact."' AND estatus!='C' AND estatus!='F'");
		if($row = mysql_fetch_array($res)){
			$res2 = mysql_query("SELECT folio_inicial FROM foliosiniciales WHERE plaza='".$_POST['plazausuario']."' AND tipo=0 AND tipodocumento=1");
			$row2 = mysql_fetch_array($res2);
			$res1 = mysql_query("SELECT cve FROM facturas WHERE plaza='".$_POST['plazausuario']."'");
			if(mysql_num_rows($res1) > 0){
				mysql_query("INSERT facturas SET plaza='".$_POST['plazausuario']."',fecha='".$row['fecha']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$row['obs']."',
				cliente='".$row['cliente']."',tipo_pago='".$row['tipo_pago']."',forma_pago='".$row['forma_pago']."',usuario='".$_POST['cveusuario']."',baniva_retenido='".$row['baniva_retenido']."',banisr_retenido='".$row['banisr_retenido']."',
				carta_porte='".$row['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$row['nombre_cliente']."',direccion_cliente='".$row['direccion_cliente']."',
				tipopago_cliente='".$row['tipopago_cliente']."',banco_cliente='".$row['banco_cliente']."',cuenta_cliente='".$row['cuenta_cliente']."',tipo_factura='".$row['tipo_factura']."',
				engomado='".$row['engomado']."',banco='".$row['banco']."',cuenta_cheque='".$row['cuenta_cheque']."'") or die(mysql_error());
			}
			else{
				mysql_query("INSERT facturas SET plaza='".$_POST['plazausuario']."',cve='".$row2['folio_inicial']."',fecha='".$row['fecha']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$row['obs']."',
				cliente='".$row['cliente']."',tipo_pago='".$row['tipo_pago']."',forma_pago='".$row['forma_pago']."',usuario='".$_POST['cveusuario']."',baniva_retenido='".$row['baniva_retenido']."',banisr_retenido='".$row['banisr_retenido']."',
				carta_porte='".$row['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$row['nombre_cliente']."',direccion_cliente='".$row['direccion_cliente']."',
				tipopago_cliente='".$row['tipopago_cliente']."',banco_cliente='".$row['banco_cliente']."',cuenta_cliente='".$row['cuenta_cliente']."',tipo_factura='".$row['tipo_factura']."',
				engomado='".$row['engomado']."',banco='".$row['banco']."',cuenta_cheque='".$row['cuenta_cheque']."'") or die(mysql_error());
			}
			$cvefact=mysql_insert_id();
			mysql_query("UPDATE prefacturas SET factura='$cvefact',estatus='F' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$cveprefact."'");
			$documento=array();
		
			//Generamos la Factura
			$documento['serie']=$rowplaza['numero'];
			$documento['folio']=$cvefact;
			$documento['fecha']=$row['fecha'].' '.$row['hora'];
			$documento['formapago']=$array_forma_pago[$row['forma_pago']];
			$documento['idtipodocumento']=1;
			$documento['observaciones']=$row['obs'];
			$documento['metodopago']=$array_tipo_pago[$row['tipo_pago']];
			$res1 = mysql_query("SELECT * FROM clientes WHERE cve='".$row['cliente']."'");
			$row1 = mysql_fetch_array($res1);
			$row1['cve']=0;
			$emailenvio = $row1['email'];
			$documento['receptor']['codigo']=$row1['cve'];
			$documento['receptor']['rfc']=$row1['rfc'].$row1['homoclave'];
			$documento['receptor']['nombre']=$row1['nombre'];
			$documento['receptor']['calle']=$row1['calle'];
			$documento['receptor']['num_ext']=$row1['numexterior'];
			$documento['receptor']['num_int']=$row1['numinterior'];
			$documento['receptor']['colonia']=$row1['colonia'];
			$documento['receptor']['localidad']=$row1['localidad'];
			$documento['receptor']['municipio']=$row1['municipio'];
			$documento['receptor']['estado']=$row1['estado'];
			$documento['receptor']['pais']='MEXICO';
			$documento['receptor']['codigopostal']=$row1['codigopostal'];
			//Agregamos los conceptos
			$res2 = mysql_query("SELECT * FROM prefacturasmov WHERE plaza='".$_POST['plazausuario']."' AND cvefact='".$cveprefact."'");
	
			$i=0;
			while($row2 = mysql_fetch_array($res2))
			{
				mysql_query("INSERT facturasmov SET plaza='".$_POST['plazausuario']."',cvefact='$cvefact',cantidad='".$row2['cantidad']."',concepto='".$row2['concepto']."',
				precio='".$row2['precio']."',importe='".$row2['importe']."',iva='".$row2['iva']."',importe_iva='".$row2['importe_iva']."',unidad='".$row2['unidad']."',
				engomado='".$row2['engomado']."'");
				$documento['conceptos'][$i]['cantidad']=$row2['cantidad'];
				$documento['conceptos'][$i]['unidad']=$row2['unidad'];
				$documento['conceptos'][$i]['descripcion']=iconv('UTF-8','ISO-8859-1',$row2['concepto']);
				$documento['conceptos'][$i]['valorUnitario']=$row2['precio'];
				$documento['conceptos'][$i]['importe']=$row2['importe'];
				$documento['conceptos'][$i]['importe_iva']=$row2['importe_iva'];
				$i++;
			}
			mysql_query("UPDATE facturas SET subtotal='".$row['subtotal']."',iva='".$row['iva']."',total='".$row['total']."',
			isr_retenido='".$row['isr_retenido']."',por_isr_retenido='".$row['por_isr_retenido']."',
			iva_retenido='".$row['iva_retenido']."',por_iva_retenido='".$row['por_iva_retenido']."' WHERE plaza='".$_POST['plazausuario']."' AND cve=".$cvefact);
			$documento['subtotal']=$row['subtotal'];
			$documento['descuento']=0;
			//Traslados
			#IVA
			if($row['iva']>0){
				$documento['tasaivatrasladado']=16;
				$documento['ivatrasladado']=$row['iva'];  //Solo 200 grava iva
			}
			if($row['iva_retenido'] > 0){
				$documento['ivaretenido']=$row['iva_retenido'];  
			}
			if($row['isr_retenido'] > 0){
				$documento['isrretenido']=$row['isr_retenido'];  
			}
	
			//total
			$documento['total']=$row['total'];
			//Moneda
			$documento['moneda']     = 1; //1=pesos, 2=Dolar, 3=Euro
			$documento['tipocambio'] = 1;
	
			//print_r($documento);
			$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
			$err = $oSoapClient->getError();
			if($err!="")
				echo "error1:".$err;
			else{
				//print_r($documento);
				$oSoapClient->timeout = 300;
				$oSoapClient->response_timeout = 300;
				$respuesta = $oSoapClient->call("generar", array ('id' => $rowempresa['idplaza'],'rfcemisor' =>$rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'documento' => $documento, 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
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
							generaFacturaPdf($_POST['plazausuario'],$cvefact);
							//Tomar la informacion de Retorno
							$dir="../cfdi/comprobantes/";
							//$dir=dirname(realpath(getcwd()))."/solucionesfe_facturacion/cfdi/comprobantes/";
							//el zip siempre se deja fuera
							$dir2="../cfdi/";
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
									if($rowempresa['email']!=""){
										$correos = explode(",",trim($rowempresa['email']));
										foreach($correos as $correo)
											$mail->AddCC(trim($correo));
									}
									$mail->AddAttachment("../cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
									$mail->AddAttachment("../cfdi/comprobantes/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
									$mail->Send();
								}
								if($rowempresa['email']!=""){
									$mail = new PHPMailer();
									$mail->Host = "localhost";
									$mail->From = "verificentros@verificentros.net";
									$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
									$mail->Subject = "Factura ".$cvefact;
									$mail->Body = "Factura ".$cvefact;
									$correos = explode(",",trim($rowempresa['email']));
									foreach($correos as $correo)
										$mail->AddAddress(trim($correo));
									$mail->AddAttachment("../cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
									$mail->AddAttachment("../cfdi/comprobantes/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
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
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==3){
	$res = mysql_query("SELECT * FROM prefacturas WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	if($row['estatus']!='C'){
		$cvefact=$row['cve'];
		mysql_query("UPDATE prefacturas SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==2){
	$resClienteCuenta=mysql_query("SELECT * FROM clientes_cuentas WHERE cve='".$_POST['cliente_cuenta']."'");
	$ClienteCuenta=mysql_fetch_array($resClienteCuenta);
	mysql_query("INSERT prefacturas SET plaza='".$_POST['plazausuario']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
	cliente='".$_POST['cliente']."',tipo_pago='".$_POST['tipo_pago']."',forma_pago='".$_POST['forma_pago']."',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
	carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
	tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."',
	engomado='".$_POST['engomado']."',banco='".$ClienteCuenta['banco']."',cuenta_cheque='".$ClienteCuenta['cuenta']."',
	venta='".$_POST['venta']."'") or die(mysql_error());
	$cvefact=mysql_insert_id();
	
	//Agregamos los conceptos
	$i=0;
	foreach($_POST['cant'] as $k=>$v){
		if($v>0){
			if(trim($_POST['unidad'][$k])=="") $_POST['unidad'][$k] = "NO APLICA";
			$importe_iva=round($_POST['importe'][$k]*$_POST['ivap'][$k]/100,2);
			mysql_query("INSERT prefacturasmov SET plaza='".$_POST['plazausuario']."',cvefact='$cvefact',cantidad='".$v."',concepto='".$_POST['concepto'][$k]."',
			precio='".$_POST['precio'][$k]."',importe='".$_POST['importe'][$k]."',iva='".$_POST['ivap'][$k]."',importe_iva='$importe_iva',unidad='".$_POST['unidad'][$k]."',
			engomado='".$_POST['engomado_id'][$k]."'");
		}
	}
	mysql_query("UPDATE prefacturas SET subtotal='".$_POST['subtotal']."',iva='".$_POST['iva']."',total='".$_POST['total']."',
	isr_retenido='".$_POST['isr_retenido']."',por_isr_retenido='".$_POST['por_isr_retenido']."',
	iva_retenido='".$_POST['iva_retenido']."',por_iva_retenido='".$_POST['por_iva_retenido']."' WHERE plaza='".$_POST['plazausuario']."' AND cve=".$cvefact);
	
	$_POST['cmd']=0;
}

top($_SESSION);
	$res = mysql_query("SELECT por_iva_retenido, mod_iva_retenido, por_isr_retenido, mod_isr_retenido FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
	$row = mysql_fetch_array($res);
	$por_iva_retenido = $row['por_iva_retenido'];
	$bloquearivaret = " readOnly";
	$claseivaret = "readOnly";
	if($row['mod_iva_retenido'] == 1){
		$bloquearivaret = "";
		$claseivaret = "textField";
	}
	$por_isr_retenido = $row['por_isr_retenido'];
	$bloquearisrret = " readOnly";
	$claseisrret = "readOnly";
	if($row['mod_isr_retenido'] == 1){
		$bloquearisrret = "";
		$claseisrret = "textField";
	}
	if($_POST['cmd']==1){
		echo '<table><tr>';
		if(nivelUsuario()>1){
			echo '<td><a href="#" onClick="$(\'#panel\').show();
			if(document.forma.cliente.value==\'0\'){
				alert(\'Necesita seleccionar el cliente\');
				$(\'#panel\').hide();
			}
			else if($.trim(document.forma.total.value)==\'\'){
				alert(\'El total debe de ser mayor a cero\');
				$(\'#panel\').hide();
			}
			else if(document.forma.tipo_pago.value == \'5\' && document.forma.cliente_cuenta.value == \'0\'){
				alert(\'Necesita seleccionar la cuenta de cheque\');
				$(\'#panel\').hide();
			}
			else if(document.forma.carta_porte.checked == true && $.trim(document.forma.load.value)==\'\'){
				alert(\'Necesita ingresar el load\');
				$(\'#panel\').hide();
			}
			else if(document.forma.carta_porte.checked == true && $.trim(document.forma.nombre_cliente.value)==\'\'){
				alert(\'Necesita ingresar el nombre del cliente\');
				$(\'#panel\').hide();
			}
			else if(document.forma.carta_porte.checked == true && $.trim(document.forma.direccion_cliente.value)==\'\'){
				alert(\'Necesita ingresar la direccion del cliente\');
				$(\'#panel\').hide();
			}
			else if(document.forma.carta_porte.checked == true && $.trim(document.forma.tipopago_cliente.value)==\'\'){
				alert(\'Necesita seleccionar el tipo de pago de la carta porte\');
				$(\'#panel\').hide();
			}
			else if((document.forma.total1.value/1)<=0){
				alert(\'La factura debe de ser mayor a cero\');
				$(\'#panel\').hide();
			}
			else{
				atcr(\'prefacturas.php\',\'\',2,\'0\');
			}
			"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		}
		echo '<td><a href="#" onclick="$(\'#panel\').show();atcr(\'prefacturas.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
		echo '</tr></table>';
		echo '<br>';
		echo '<table>';
		echo '<tr><td align="left">Fecha</td><td><input type="text" name="fecha" id="fecha"  size="15" class="readOnly" value="'.fechaLocal().'" readOnly>&nbsp;&nbsp;<!--<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>--></td></tr>';
		$fecha_rec=date( "Y-m-d" , strtotime ( "-1 day" , strtotime(fechaLocal()) ) );
		
		echo '<tr style="display:none;"><td align="left">Tipo</td><td><select name="tipo_factura" id="tipo_factura"><option value="0">Factura</option><option value="1">Honorarios</option></select></td></tr>';
		/*echo '<tr><td align="left">Cliente</td><td><select name="cliente" id="cliente"><option value="0">--- Seleccione ---</option>';
		foreach($array_clientes as $k=>$v){
			echo '<option value="'.$k.'" style="color: '.$array_colorcliente[$k].';"';
			if($array_colorcliente[$k] == "#FF0000") echo ' disabled';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';*/
		if($rowempresa['localidad_id']==2)
			echo '<tr><th align="left">Folio de Venta</th><td><input type="text" name="venta" id="venta" class="textField" value=""></td></tr>';
		echo '<tr><th align="left">Busqueda Cliente</th><td><input type="text" name="busqueda" id="busqueda" class="textField" value=""></td></tr>';
		echo '<tr><th align="left">RFC</th><td><input type="hidden" name="cliente" id="cliente" value="0"><input type="text" name="rfc" id="rfc" class="readOnly" size="15" value="" readOnly></td></tr>';
		echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="readOnly" size="50" value="" readOnly></td></tr>';
		echo '<tr><td>Factura de Engomado</td><td><input type="checkbox" name="engomado" id="engomado" value="1" onClick="facturar_engomado()" checked></td></tr>';
		echo '<tr><td>Forma de Pago</td><td><select name="forma_pago" id="forma_pago">';
		foreach($array_forma_pago as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Tipo de Pago</td><td><select name="tipo_pago" id="tipo_pago" onChange="if(this.value==\'5\'){
			$(\'#cliente_cuenta\').parents(\'tr:first\').show();
		}
		else{
			$(\'#cliente_cuenta\').parents(\'tr:first\').hide();
			document.forma.cliente_cuenta.value=\'0\';
		}">';
		foreach($array_tipo_pago as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr style="display:none;"><td>Cuenta Cheque</td><td><select name="cliente_cuenta" id="cliente_cuenta"><option value="0">Seleccione</option>';
		echo '</select></td></tr>';
		/*echo '<tr><td align="left">Mes</td><td><select name="mes" id="mes"><option value="0">Seleccione</option>';
		$res = mysql_query("SELECT LEFT(fechaapl,7) FROM depositos WHERE estatus!='C' AND fechaapl>'0000-00-00' GROUP BY LEFT(fechaapl,7) ORDER BY LEFT(fechaapl,7) DESC");
		while($row=mysql_fetch_array($res)){
			$dat=explode("-",$row[0]);
			echo '<option value="'.$row[0].'">'.$array_meses[intval($dat[1])].' '.$dat[0].'</option>';
		}
		echo '</select></td></tr>';*/
		echo '<tr';
		if($rowempresa['carta_porte']!=1){
			echo ' style="display:none;"';
		}
		echo '><td>Carta Porte</th><td><input type="checkbox" id="carta_porte" name="carta_porte" value="1" onClick="
			if(this.checked){ 
				$(\'.rcarta_porte\').show(); 
			}
			else{ 
				$(\'.rcarta_porte\').hide();
			}"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Load</td><td><input type="text" class="textField" name="load" id="load" value="" size="30"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Nombre del Cliente</td><td><input type="text" class="textField" name="nombre_cliente" id="nombre_cliente" value="" size="50"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Direccion del Cliente</td><td><input type="text" class="textField" name="direccion_cliente" id="direccion_cliente" value="" size="100"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Tipo de Pago</td><td><input type="text" class="textField" name="tipopago_cliente" id="tipopago_cliente" value="" size="50"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Banco</td><td><input type="text" class="textField" name="banco_cliente" id="banco_cliente" value="" size="30"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Cuenta</td><td><input type="text" class="textField" name="cuenta_cliente" id="cuenta_cliente" value="" size="30"></td></tr>';
		echo '<tr><td>Observaciones</td><td><textarea class="textField" name="obs" id="obs" cols="30" rows="3"></textarea></td></tr>';
		echo '</table>';
		echo '<input type="hidden" name="clickguardar" id="clickguardar" value="no">';
		echo '<table id="tablaproductos"><tr>';
		echo '<th>Cantidad</th>';
		echo '<th id="encabezadoengomado">Engomado</th>';
		echo '<th>Unidad</th>';
		echo '<th>Descripcion</th><th>Precio Unitario</th><th>Importe</th><th style="display:none;">IVA</th></tr>';
		$i=0;
		$cadenaengomado='';
		if($i==0){
			echo '<tr class="renglon" ren="'.$i.'">';
			echo '<td align="center"><input type="text" class="textField" size="10" name="cant['.$i.']" id="cant'.$i.'" value=""  onKeyUp="sumarproductos()"></td>';
			echo '<td align="center"><select name="engomado_id['.$i.']" id="engomado_id'.$i.'" onChange="seleccionengomado('.$i.')">';
			echo '<option value="" nombre="" precio="">Seleccione</option>';
			$cadenaengomado.='<option value="" nombre="" precio="">Seleccione</option>';
			foreach($array_engomado as $k=>$v){
				echo '<option value="'.$k.'" nombre="'.$v.'" precio="'.$array_engomadoprecio[$k].'">'.$v.'</option>';
				$cadenaengomado.='<option value="'.$k.'" nombre="'.$v.'" precio="'.$array_engomadoprecio[$k].'">'.$v.'</option>';
			}
			echo '</select></td>';
			echo '<td><input type="text" name="unidad['.$i.']" id="unidad'.$i.'" class="readOnly" size="20" value="" readOnly></td>';
			echo '<td><input type="text" name="concepto['.$i.']" id="concepto'.$i.'" class="readOnly" size="50" value="" readOnly></td>';
			echo '<td align="center"><input type="text" class="readOnly" size="10" name="precio['.$i.']" id="precio'.$i.'" value=""  onKeyUp="sumarproductos()" readOnly></td>';
			echo '<td align="center"><input type="text" class="readOnly" size="10" name="importe['.$i.']" id="importe'.$i.'" value="" readOnly></td>';
			echo '<td align="center" style="display:none;"><input type="checkbox" name="ivap['.$i.']" id="ivap'.$i.'" value="16" onClick="sumarproductos()" checked></td>';
			echo '</tr>';
			$i++;
		}
		echo '<tr id="idsubtotal"><th align="right" colspan="5">Subtotal&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="subtotal" id="subtotal" value="" readOnly></td></tr>';
		echo '<tr id="idiva"><th align="right" colspan="5">Iva 16%&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva" id="iva" value="" readOnly></td></tr>';
		echo '<tr id="idtotal1"><th align="right" colspan="5">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total1" id="total1" value="" readOnly></td></tr>';
		echo '<tr style="display:none;" id="idisr_ret"><th align="right" colspan="5"><input type="checkbox" name="banisr_retenido" id="banisr_retenido" value="1" onClick="sumarproductos()">Retencion I.S.R.&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="isr_retenido" id="isr_retenido" value="" readOnly></td><td><input type="text" class="'.$claseisrret.'" size="5" name="por_isr_retenido" id="por_isr_retenido" value="'.$por_isr_retenido.'" onKeyUp="sumarproductos()" '.$bloquearisrret.'>%</td></tr>';
		echo '<tr style="display:none;" id="idiva_ret"><th align="right" colspan="5"><input type="checkbox" name="baniva_retenido" id="baniva_retenido" value="1" onClick="sumarproductos()">Retencion I.V.A.&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva_retenido" id="iva_retenido" value="" readOnly></td><td><input type="text" class="'.$claseivaret.'" size="5" name="por_iva_retenido" id="por_iva_retenido" value="'.$por_iva_retenido.'" onKeyUp="sumarproductos()" '.$bloquearivaret.'>%</td></tr>';
		echo '<tr style="display:none;" id="idtotal"><th align="right" colspan="5">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total" id="total" value="" readOnly></td></tr>';
		echo '</table>';		
		echo '<input type="button" value="Agregar" onClick="agregarproducto()" class="textField">';
		echo '<input type="hidden" name="cantprod" value="'.$i.'">';
		echo '<script>
			function facturar_engomado(){
				if(document.forma.engomado.checked==false){
					$("#encabezadoengomado").hide();
					$(".renglon").each(function(){
						linea = $(this).attr("ren");
						$("#unidad"+linea).removeAttr("readOnly").removeClass("readOnly").addClass("textField").val("");
						$("#concepto"+linea).removeAttr("readOnly").removeClass("readOnly").addClass("textField").val("");
						$("#precio"+linea).removeAttr("readOnly").removeClass("readOnly").addClass("textField").val("");
						$("#engomado_id"+linea).val("").parents("td:first").hide();
					});
					colspan="4";
				}
				else{
					$("#encabezadoengomado").show();
					$(".renglon").each(function(){
						linea = $(this).attr("ren");
						$("#unidad"+linea).attr("readOnly","readOnly").removeClass("textField").addClass("readOnly").val("");
						$("#concepto"+linea).attr("readOnly","readOnly").removeClass("textField").addClass("readOnly").val("");
						$("#precio"+linea).attr("readOnly","readOnly").removeClass("textField").addClass("readOnly").val("");
						$("#engomado_id"+linea).val("").parents("td:first").show();
					});
					colspan="5";
				}
				$("#idsubtotal").find("th:first").attr("colspan",colspan);
				$("#idiva").find("th:first").attr("colspan",colspan);
				$("#idtotal1").find("th:first").attr("colspan",colspan);
				$("#idisr_ret").find("th:first").attr("colspan",colspan);
				$("#idiva_ret").find("th:first").attr("colspan",colspan);
				$("#idtotal").find("th:first").attr("colspan",colspan);
				sumarproductos();
			}
			
			
			
			var ac_config = {
				source: "facturas.php?ajax=2&plazausuario='.$_POST['plazausuario'].'",
				select: function(event, ui){
					$("#rfc").val(ui.item.rfc);
					$("#nombre").val($("<div />").html(ui.item.nombre).text());
					$("#cliente").val(ui.item.cve);
					$("#cliente_cuenta").html(ui.item.cuentas);
				},
				minLength:3
			};
			$("#busqueda").autocomplete(ac_config);
					
			function agregarproducto(){
				var checkeado=\'\';
				var clase=\'readOnly\';
				var bloqueo=\' readOnly\';
				var estilo=\'\';
				var colspan="5";
				if(document.forma.engomado.checked==false){
					clase=\'textField\';
					bloqueo=\'\';
					estilo=\' style="display:none;"\';
					var colspan="4";
				}
				if($("#baniva_retenido").is(":checked")){
					checkeado=\'checked\';
				}
				tot=$("#total").val();
				$("#idtotal").remove();
				subtot=$("#subtotal").val();
				$("#idsubtotal").remove();
				iv=$("#iva").val();
				$("#idiva").remove();
				tot1=$("#total1").val();
				$("#idtotal1").remove();
				iva_ret=$("#iva_retenido").val();
				piva_ret=$("#por_iva_retenido").val();
				$("#idiva_ret").remove();
				isr_ret=$("#isr_retenido").val();
				pisr_ret=$("#por_isr_retenido").val();
				$("#idisr_ret").remove();
				num=document.forma.cantprod.value;
				$("#tablaproductos").append(\'<tr class="renglon" ren="\'+num+\'">\
				<td align="center"><input type="text" class="textField" size="10" name="cant[\'+num+\']" id="cant\'+num+\'" value=""  onKeyUp="sumarproductos()"></td>\</td>\
				<td\'+estilo+\' align="center"><select name="engomado_id[\'+num+\']" id="engomado_id\'+num+\'" onChange="seleccionengomado(\'+num+\')">'.$cadenaengomado.'</select></td>\
				<td><input type="text" name="unidad[\'+num+\']" id="unidad\'+num+\'" class="\'+clase+\'" size="20" value=""\'+bloqueo+\'></td>\
				<td><input type="text" name="concepto[\'+num+\']" id="concepto\'+num+\'" class="\'+clase+\'" size="50" value=""\'+bloqueo+\'></td>\
				<td align="center"><input type="text" class="\'+clase+\'" size="10" name="precio[\'+num+\']" id="precio\'+num+\'" value=""  onKeyUp="sumarproductos()"\'+bloqueo+\'></td>\
				<td align="center"><input type="text" class="readOnly" size="10" name="importe[\'+num+\']" id="importe\'+num+\'" value="" readOnly></td>\
				<td align="center" style="display:none;"><input type="checkbox" name="ivap[\'+num+\']" id="ivap\'+num+\'" value="16" onClick="sumarproductos()" checked></td>\
				</tr>\
				<tr id="idsubtotal"><th align="right" colspan="\'+colspan+\'">Subtotal&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="subtotal" id="subtotal" value="\'+subtot+\'" readOnly></td></tr>\
				<tr id="idiva"><th align="right" colspan="\'+colspan+\'">Iva 16%&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva" id="iva" value="\'+iv+\'" readOnly></td></tr>\
				<tr id="idtotal1"><th align="right" colspan="\'+colspan+\'">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total1" id="total1" value="\'+tot1+\'" readOnly></td></tr>\
				<tr style="display:none;" id="idisr_ret"><th align="right" colspan="\'+colspan+\'"><input type="checkbox" name="banisr_retenido" id="banisr_retenido" value="1" onClick="sumarproductos()" \'+checkeado+\'>Retencion I.S.R.&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="isr_retenido" id="isr_retenido" value="\'+isr_ret+\'" readOnly></td><td><input type="text" class="'.$claseisrret.'" size="5" name="por_isr_retenido" id="por_isr_retenido" value="\'+pisr_ret+\'" onKeyUp="sumarproductos()" '.$bloquearisrret.'>%</td></tr>\
				<tr style="display:none;" id="idiva_ret"><th align="right" colspan="\'+colspan+\'"><input type="checkbox" name="baniva_retenido" id="baniva_retenido" value="1" onClick="sumarproductos()" \'+checkeado+\'>Retencion I.V.A.&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva_retenido" id="iva_retenido" value="\'+iva_ret+\'" readOnly></td><td><input type="text" class="'.$claseivaret.'" size="5" name="por_iva_retenido" id="por_iva_retenido" value="\'+piva_ret+\'" onKeyUp="sumarproductos()" '.$bloquearivaret.'>%</td></tr>\
				<tr style="display:none;" id="idtotal"><th align="right" colspan="\'+colspan+\'">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total" id="total" value="\'+tot+\'" readOnly></td></tr>\');
				num++;
				document.forma.cantprod.value=num;
			}
			
			function seleccionengomado(linea){
				campoengomado=$("#engomado_id"+linea);
				nombre = campoengomado.find(\'option[value="\'+campoengomado.val()+\'"]\').attr("nombre");
				precio = campoengomado.find(\'option[value="\'+campoengomado.val()+\'"]\').attr("precio");
				$("#concepto"+linea).val(nombre);
				$("#precio"+linea).val(precio);
				sumarproductos();
			}
			
			function sumarproductos(){
				var sumar=0;
				var iv=0;
				var iv_ret=0;
				var is_ret=0;
				for(i=0;i<(document.forma.cantprod.value/1);i++){
					impo=(document.getElementById("cant"+i).value/1)*(document.getElementById("precio"+i).value/1);
					document.getElementById("importe"+i).value=impo.toFixed(2);
					sumar+=(document.getElementById("importe"+i).value/1);
					is_ret+=document.getElementById("importe"+i).value*document.forma.por_isr_retenido.value/100;
					if(document.getElementById("ivap"+i).checked){
						iv+=document.getElementById("importe"+i).value*0.16;
						iv_ret+=document.getElementById("importe"+i).value*document.forma.por_iva_retenido.value/100;
					}
				}
				document.forma.subtotal.value=sumar.toFixed(2);
				document.forma.iva.value=iv.toFixed(2);
				document.forma.total1.value=(document.forma.subtotal.value/1)+(document.forma.iva.value/1);
				if($("#banisr_retenido").is(":checked")){
					document.forma.isr_retenido.value=is_ret.toFixed(2);
				}
				else{
					document.forma.isr_retenido.value=0;
				}
				if($("#baniva_retenido").is(":checked")){
					document.forma.iva_retenido.value=iv_ret.toFixed(2);
				}
				else{
					document.forma.iva_retenido.value=0;
				}
				
				tot=(document.forma.subtotal.value/1)+(document.forma.iva.value/1)-(document.forma.isr_retenido.value/1)-(document.forma.iva_retenido.value/1);
				document.forma.total.value=tot.toFixed(2);
			}
			
			
		  </script>';
	}

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onClick="atcr(\'prefacturas.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0">&nbsp;Nuevo</a></td><td>&nbsp;</td>';
		if(nivelUsuario()>2){
			echo '<td><a href="#" onClick="atcr(\'prefacturas.php\',\'\',\'6\',\'0\');"><img src="images/validosi.gif" border="0">&nbsp;Facturar</a></td><td>&nbsp;</td>';
		}
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="textField" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="textField" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Cliente</td><td><select name="cliente" id="cliente"><option value="all">--- Todos ---</option>';
		foreach($array_clientes as $k=>$v){
			echo '<option class="cexternos" value="'.$k.'" style="color: '.$array_colorcliente[$k].';">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td align="left">Estatus</td><td><select name="estatus" id="estatus"><option value="all">Todos</option><option value="A" selected>En Proceso</option><option value="F">Facturado</option>
		<option value="C">Cancelado</option></select></td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
bottom();
echo '
<Script language="javascript">
	
	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","prefacturas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&estatus="+document.getElementById("estatus").value+"&cliente="+document.getElementById("cliente").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
		
	';	
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(0,1); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
	function validanumero(campo) {
		var ValidChars = "0123456789.";
		var cadena=campo.value;
		var cadenares="";
		var digito;
		for(i=0;i<cadena.length;i++) {
			digito=cadena.charAt(i);
			if (ValidChars.indexOf(digito) != -1)
				cadenares+=""+digito;
		}
		campo.value=cadenares;
	}

	</Script>
';

?>