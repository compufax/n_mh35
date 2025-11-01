<?php
include("main.php");

$res = mysql_query("SELECT * FROM anios_certificados WHERE 1 ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
}

$rsUsuario=mysql_query("SELECT * FROM plazas where estatus!='I' ORDER BY numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}

$localidadplaza = 0;


$rsUsuario=mysql_query("SELECT * FROM datosempresas");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazaslocalidad[$Usuario['plaza']]=$Usuario['localidad_id'];
	if($Usuario['plaza'] == $_POST['plazausuario']) $localidadplaza = $Usuario['localidad_id'];
}

$res=mysql_query("SELECT * FROM areas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_localidad[$row['cve']]=$row['nombre'];
}


$array_engomado = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' ORDER BY nombre");
$res = mysql_query("SELECT cve, nombre, precio FROM engomados WHERE localidad='".$_POST['localidad']."' AND entrega=1 GROUP BY cve ORDER BY numero");
while($row=mysql_fetch_array($res)){
	$array_engomado['normal'][$row['cve']]['nombre']=$row['nombre'];
	$array_engomado['normal'][$row['cve']]['precio']=$row['precio'];
}

if($_POST['ajax']==3){
	$res = mysql_query("SELECT cve FROM descuento_factura_global WHERE plaza={$_POST['plaza']} AND mes = '{$_POST['mes']}'");
	if($row = mysql_fetch_assoc($res)){
		mysql_query("UPDATE descuento_factura_global SET monto = '{$_POST['monto']}', cambios = CONCAT(cambios,'|{$_POST['cveusuario']},{$_POST['monto']},".date('Y-m-d H:i:s')."')");
	}	
	else{
		mysql_query("INSERT descuento_factura_global SET plaza='{$_POST['plaza']}', mes='{$_POST['mes']}', monto = '{$_POST['monto']}', cambios = CONCAT(cambios,'|{$_POST['cveusuario']},{$_POST['monto']},".date('Y-m-d H:i:s')."')");
	}

	exit();
}

if($_POST['ajax']==2){
	include("imp_factura.php");
	//require_once('../PHPMailer-master/PHPMailerAutoload.php');
	$_POST['tipo_serie'] = 0;
	$resplaza = mysql_query("SELECT * FROM plazas WHERE cve='".$_POST['plaza']."'");
	$rowplaza = mysql_fetch_array($resplaza);
	$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plaza']."'");
	$rowempresa = mysql_fetch_array($resempresa);
	if($_POST['tipo_serie']==0)
		$res = mysql_query("SELECT serie,folio_inicial FROM foliosiniciales WHERE plaza='".$_POST['plaza']."' AND tipo=0 AND tipodocumento=1");
	else
		$res = mysql_query("SELECT serie,folio_inicial FROM foliosiniciales WHERE plaza='".$_POST['plaza']."' AND tipo=0 AND tipodocumento=5");
	$row = mysql_fetch_array($res);
	$resClienteCuenta=mysql_query("SELECT * FROM clientes_cuentas WHERE cve='".$_POST['cliente_cuenta']."'");
	$ClienteCuenta=mysql_fetch_array($resClienteCuenta);
	$res1 = mysql_query("SELECT IFNULL(MAX(folio+1),1) FROM facturas WHERE plaza='".$_POST['plaza']."' AND serie='".$row['serie']."'");
	$row1 = mysql_fetch_array($res1);
	if($row['folio_inicial']<$row1[0]){
		$row['folio_inicial'] = $row1[0];
	}
	$res1=mysql_query("SELECT cve FROM clientes WHERE plaza='".$_POST['plaza']."' AND rfc='XAXX010101000'");
	$row1=mysql_fetch_array($res1);
	$mes = substr($_POST['fecha'], 5, 2);
	$anio = substr($_POST['fecha'], 0, 4);
	$_POST['cliente'] = $row1[0];
	if($_POST['cliente'] > 0 && $_POST['monto'] > 0){
		$insert = "INSERT facturas SET plaza='".$_POST['plaza']."',serie='".$row['serie']."',folio='".$row['folio_inicial']."',fecha=CURDATE(),fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
		cliente='".$_POST['cliente']."',tipo_pago='0',forma_pago='0',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
		carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
		tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."',
		engomado='".$_POST['engomado']."',banco='".$ClienteCuenta['banco']."',cuenta_cheque='".$ClienteCuenta['cuenta']."',tiene_descuento='".$_POST['tiene_descuento']."', tipo_serie='".$_POST['tipo_serie']."',
		tipo_relacion='".$_POST['tipo_relacion']."',uuidsrelacionados='".$_POST['uuidsrelacionados']."',periodicidad='04', meses='{$mes}', anio='{$anio}',
		tipo_documento_origen='".$_POST['tipo_documento_origen']."',tipo_pag='".$_POST['tipo_pag']."'".$datossucursal;
		while(!$resinsert=mysql_query($insert)){
			$row['folio_inicial']++;
			$insert = "INSERT facturas SET plaza='".$_POST['plaza']."',serie='".$row['serie']."',folio='".$row['folio_inicial']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."', tipo_serie='".$_POST['tipo_serie']."',
			cliente='".$_POST['cliente']."',tipo_pago='".$_POST['tipo_pago']."',forma_pago='".$_POST['forma_pago']."',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
			carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
			tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."',
			engomado='".$_POST['engomado']."',banco='".$ClienteCuenta['banco']."',cuenta_cheque='".$ClienteCuenta['cuenta']."',tiene_descuento='".$_POST['tiene_descuento']."',
			tipo_relacion='".$_POST['tipo_relacion']."',uuidsrelacionados='".$_POST['uuidsrelacionados']."',periodicidad='04', meses='{$mes}', anio='{$anio}',
			tipo_documento_origen='".$_POST['tipo_documento_origen']."',tipo_pag='".$_POST['tipo_pag']."'".$datossucursal;
		}
		$foliofactura = $row['serie'].' '.$row['folio_inicial'];
		$cvefact=mysql_insert_id();
		$documento=array();
		require_once("../nusoap/nusoap.php");
		$importe = $_POST['monto']/1.16;
		$importe_iva = $_POST['monto']-$importe;
		mysql_query("INSERT facturasmov SET plaza='".$_POST['plaza']."',cvefact='$cvefact',cantidad='1',concepto='VENTAS DE CERTIFICADOS',
			precio='".$importe."',importe='".$importe."',iva='16',importe_iva='$importe_iva',unidad='".$_POST['unidad'][$k]."',
			engomado='0',claveprodsat='77121503',claveunidadsat='E48'");

		mysql_query("UPDATE facturas SET subtotal='".$importe."',iva='".$importe_iva."',total='".$_POST['monto']."',
		isr_retenido='".$_POST['isr_retenido']."',por_isr_retenido='".$_POST['por_isr_retenido']."',
		iva_retenido='".$_POST['iva_retenido']."',por_iva_retenido='".$_POST['por_iva_retenido']."' WHERE plaza='".$_POST['plaza']."' AND cve=".$cvefact);
		$documento = genera_arreglo_facturacion($_POST['plaza'], $cvefact, 'I');
		$resultadotimbres = validar_timbres($_POST['plaza']);
		if($resultadotimbres['seguir']){
			//$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
			$oSoapClient = new nusoap_client("https://servicios.integratucfdi.net/wscfdi.php?wsdl", true);
			$err = $oSoapClient->getError();
			if($err!=""){
				echo "error1:".$err;
				desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
			}
			else{
				//print_r($documento);
				$oSoapClient->timeout = 300;
				$oSoapClient->response_timeout = 300;
				$respuesta = $oSoapClient->call("generarComprobante", array ('id' => $rowempresa['idplaza'],'rfcemisor' => $rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'documento' => $documento, 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
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
					desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
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
						desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
					}
					else{
						if($respuesta['resultado']){
							mysql_query("UPDATE facturas SET respuesta1='".$respuesta['uuid']."',seriecertificado='".$respuesta['seriecertificado']."',
							sellodocumento='".$respuesta['sellodocumento']."',uuid='".$respuesta['uuid']."',seriecertificadosat='".$respuesta['seriecertificadosat']."',
							sellotimbre='".$respuesta['sellotimbre']."',cadenaoriginal='".$respuesta['cadenaoriginal']."',
							fechatimbre='".substr($respuesta['fechatimbre'],0,10)." ".substr($respuesta['fechatimbre'],-8)."'
							WHERE plaza='".$_POST['plaza']."' AND cve=".$cvefact);
							mysql_query("UPDATE facturas SET rfc_cli='".$row['rfc']."', nombre_cli='".$row['nombre']."', calle_cli='".$row['calle']."', numext_cli='".$row['numexterior']."', numint_cli = '".$row['numinterior']."', colonia_cli = '".$row['colonia']."', localidad_cli = '".$row['localidad']."', municipio_cli = '".$row['municipio']."',
								estado_cli='".$row['estado']."', cp_cli='".$row['codigopostal']."'
							WHERE plaza='".$_POST['plaza']."' AND cve=".$cvefact);
							//Tomar la informacion de Retorno
							$dir="../cfdi/comprobantes/";
							//$dir=dirname(realpath(getcwd()))."/solucionesfe_facturacion/cfdi/comprobantes/";
							//el zip siempre se deja fuera
							$dir2="../cfdi/";
							//Leer el Archivo Zip
							$fileresult=$respuesta['archivos'];
							$strzipresponse=base64_decode($fileresult);
							$filename='cfdi_'.$_POST['plaza'].'_'.$cvefact;
							file_put_contents($dir2.$filename.'.zip', $strzipresponse);
							$zip = new ZipArchive;
							if ($zip->open($dir2.$filename.'.zip') === TRUE){
								$strxml=$zip->getFromName('xml.xml');
								file_put_contents($dir.$filename.'.xml', $strxml);
								//$strpdf=$zip->getFromName('formato.pdf');
								//file_put_contents($dir.$filename.'.pdf', $strpdf);
								$zip->close();		
								generaFacturaPdf($_POST['plaza'],$cvefact);
								if($emailenvio!="" && 1==2){
									//$mail = new PHPMailer();
									//$mail->Host = "localhost";
									$mail = new PHPMailer;		
									$mail->isSMTP();                                      // Set mailer to use SMTP
									$mail->Host = 'smtp.mailgun.org';                     // Specify main and backup SMTP servers
									$mail->SMTPAuth = true;                               // Enable SMTP authentication
									$mail->Username = 'postmaster@verificentrosgp1.net';   // SMTP username
									$mail->Password = 'a4f9c1bb34ed1c639a0cdeedb5f79aea';                           // SMTP password
									$mail->SMTPSecure = 'tls';                            // Enable encryption, only 'tls' is accepted							
									$mail->From = "verificentros@verificentrosgp1.net";
									$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plaza']];
									$mail->Subject = "Factura ".$fserie." ".$ffolio;
									$mail->Body = "Factura ".$fserie." ".$ffolio;
									//$mail->AddAddress(trim($emailenvio));
									$correos = explode(",",trim($emailenvio));
									foreach($correos as $correo)
										$mail->AddAddress(trim($correo));
									/*if($rowempresa['email']!=""){
										$correos = explode(",",trim($rowempresa['email']));
										foreach($correos as $correo)
											$mail->AddCC(trim($correo));
									}*/
									$mail->AddAttachment("../cfdi/comprobantes/factura_".$_POST['plaza']."_".$cvefact.".pdf", "Factura ".$fserie." ".$ffolio.".pdf");
									$mail->AddAttachment("../cfdi/comprobantes/cfdi_".$_POST['plaza']."_".$cvefact.".xml", "Factura ".$fserie." ".$ffolio.".xml");
									$mail->Send();
								}	
								@unlink("../cfdi/comprobantes/factura_".$_POST['plaza']."_".$cvefact.".pdf");
								echo 'Se genero la Factura '.$foliofactura;
							}
							else 
								$strmsg='Error al descomprimir el archivo';
						}
						else{
							$strmsg=$respuesta['mensaje'];
							desbloquear_timbre($_POST['plaza'], $resultadotimbres['cvecompra']);
						}
						//print_r($respuesta);	
						echo $strmsg;
					}
				}
			}
		}
	}
	else{
		echo 'No se encontro al cliente publico general o el importe es cero';
	}

	exit();
}

if($_POST['ajax']==1){
	$nivelUsuario = nivelUsuario();
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	if($_POST['cveusuario']==1) echo '<th>Facturar</th>';
	echo '<th>Centro</th><th>Ventas</th><th>Pagos en Caja</th><th>Devoluciones</th><th>Facturacion</th><th>Descuentos</th><th>Por Facturar</th><th>Facturacion Publico General</th>';
	echo '</tr>';
	
	$total = 0;
	$_POST['fecha_ini'] = $_POST['mes'].'-01';
	$_POST['fecha_fin'] = date( "Y-m-t" , strtotime ( "+ 1 day" , strtotime($_POST['fecha_ini']) ) );
	if($_POST['fecha_ini']<'2015-05-01') $_POST['fecha_ini'] = '2015-05-01';
	if($_POST['fecha_fin']<'2015-05-01') $_POST['fecha_fin'] = '2015-05-01';
	$array_plazas=array();
	if($_POST['localidad']!='all'){
		$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.localidad_id = '".$_POST['localidad']."' ORDER BY a.numero");
	}
	else{
		$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].")");
	}
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]['nombre']=$row['numero'].' '.$row['nombre'];
	}
	$res = mysql_query("SELECT plaza, SUM(monto) FROM cobro_engomado WHERE estatus!='C' AND tipo_pago NOT IN (2,6,10,11) AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY plaza");
	while($row = mysql_fetch_array($res)){
		$array_plazas[$row['plaza']]['ventas']=$row[1];
		$array_plazas[$row['plaza']]['porfacturar']=$row[1];
	}
	$res = mysql_query("SELECT plaza, SUM(monto) FROM pagos_caja WHERE estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY plaza");
	while($row = mysql_fetch_array($res)){
		$array_plazas[$row['plaza']]['pagoscaja']=$row[1];
		$array_plazas[$row['plaza']]['porfacturar']+=$row[1];
	}
	$res = mysql_query("SELECT plaza, SUM(devolucion) FROM devolucion_certificado WHERE estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY plaza");
	while($row = mysql_fetch_array($res)){
		$array_plazas[$row['plaza']]['devoluciones']=$row[1];
		$array_plazas[$row['plaza']]['porfacturar']-=$row[1];
	}
	$res = mysql_query("SELECT plaza, monto FROM descuento_factura_global WHERE mes = '{$_POST['mes']}'");
	while($row = mysql_fetch_array($res)){
		$array_plazas[$row['plaza']]['descuentos']=$row[1];
		$array_plazas[$row['plaza']]['porfacturar']-=$row[1];
	}
	$res = mysql_query("SELECT a.plaza, SUM(IF(b.rfc!='XAXX010101000',a.total,0)), SUM(IF(b.rfc='XAXX010101000',a.total,0)) FROM facturas a INNER JOIN clientes b ON b.cve=a.cliente WHERE a.estatus!='C' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY a.plaza");
	while($row = mysql_fetch_array($res)){
		$array_plazas[$row['plaza']]['facturacion']=$row[1];
		$array_plazas[$row['plaza']]['porfacturar']-=($row[1]+$row[2]);
		$array_plazas[$row['plaza']]['facturaciongeneral']=$row[2];
	}
	foreach($array_plazas as $k1=>$v1){
		rowb();
		if($_POST['cveusuario']==1){
			echo '<td align="center"><input type="button" onClick="facturar('.$k1.')" value="Facturar" class="textField"></td>';
		}
		echo '<td align="left">'.htmlentities(utf8_encode($v1['nombre'])).'</td>';
		$c=0;
		echo '<td align="right">'.number_format($v1['ventas'],2).'</td>';
		echo '<td align="right">'.number_format($v1['pagoscaja'],2).'</td>';
		echo '<td align="right">'.number_format($v1['devoluciones'],2).'</td>';
		echo '<td align="right">'.number_format($v1['facturacion'],2).'</td>';
		if ($nivelUsuario > 2){
			echo '<td align="center"><input type="text" id="descuentos_'.$k1.'" size="15" class="textField" value="'.$v1['descuentos'].'"><br><input type="button" onClick="guardar_descuento('.$k1.',\''.$_POST['mes'].'\')" value="Guardar" class="textField"></td>';
		}
		else{
			echo '<td align="right">'.number_format($v1['descuentos'],2).'</td>';
		}
		echo '<td align="right">'.number_format($v1['porfacturar'],2).'<input type="hidden" id="monto_'.$k1.'" value="'.$v1['porfacturar'].'"></td>';
		echo '<td align="right">'.number_format($v1['facturaciongeneral'],2).'</td>';
		echo '</tr>';
	}
	echo '</table>';
	exit();	
}

top($_SESSION);

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="if(document.forma.localidad.value==\'all\') alert(\'Necesita seleccionar la localidad\'); else buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onclick="if(document.forma.localidad.value==\'all\') alert(\'Necesita seleccionar la localidad\'); else{ document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');atcr(\'certificadosxplaza.php\',\'_blank\',100,0);}"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	$res = mysql_query("SELECT LEFT(fecha,7) as mes FROM cobro_engomado GROUP BY LEFT(fecha,7) ORDER BY LEFT(fecha,7) DESC");
	echo '<tr><td>Mes</td><td><select name="mes" id="mes">';
	while($row = mysql_fetch_assoc($res)) echo '<option value="'.$row['mes'].'">'.$row['mes'].'</option>';
	echo '</select></td></tr>';
	/*echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.substr(fechaLocal(),0,8).'01" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Fin</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';*/
	echo '<tr style="display:none;"><td>A&ntilde;o Certificacion</td><td><select name="anio" id="anio"><option value="all" selected>Todos</option>';
	foreach($array_anios as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr style="display:none;"><td align="left">Localidad</td><td><select name="localidad" id="localidad" onChange="muestraplazas()">';
	foreach($array_localidad as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$localidadplaza) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select>';
	echo '<tr><td align="left">Plaza</td><td><select multiple="multiple" name="plazas" id="plazas">';
	$optionsplazas = array();
	foreach($array_plazas as $k=>$v){
		if($localidadplaza == 0 || $localidadplaza == $array_plazaslocalidad[$k]){
			echo '<option value="'.$k.'" selected>'.$v.'</option>';
		}
		$optionsplazas['all'] .= '<option value="'.$k.'" selected>'.$v.'</option>';
		$optionsplazas[$array_plazaslocalidad[$k]] .= '<option value="'.$k.'" selected>'.$v.'</option>';
	}
	echo '</select>';
	echo '<input type="hidden" name="plaza" id="plaza" value=""></td></tr>';
	echo '</table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	$("#plazas").multipleSelect({
		width: 500
	});	
	function buscarRegistros(){
		document.forma.plaza.value=$("#plazas").multipleSelect("getSelects");
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","repventasfacturacion.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&localidad="+document.getElementById("localidad").value+"&plaza="+document.getElementById("plaza").value+"&anio="+document.getElementById("anio").value+"&mes="+document.getElementById("mes").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

	function muestraplazas(){

		';

		foreach($optionsplazas as $k=>$v){
			echo '
				if(document.forma.localidad.value == "'.$k.'"){
					$("#plazas").html(\''.$v.'\');
				}
			';
		}

	echo '	

		$("#plazas").multipleSelect("refresh");	
	}

	function facturar(plaza){
		$.ajax({
		  url: "repventasfacturacion.php",
		  type: "POST",
		  async: false,
		  data: {
			monto: document.getElementById("monto_"+plaza).value,
			cveusuario: document.getElementById("cveusuario").value,
			fecha: document.getElementById("mes").value,
			plaza: plaza,
			ajax: 2
		  },
			success: function(data) {
				alert(data);
			}
		});
	}

	function guardar_descuento(plaza, mes){
		$.ajax({
		  url: "repventasfacturacion.php",
		  type: "POST",
		  async: false,
		  data: {
			monto: document.getElementById("descuentos_"+plaza).value,
			cveusuario: document.getElementById("cveusuario").value,
			mes: mes,
			plaza: plaza,
			ajax: 3
		  },
			success: function(data) {
				buscarRegistros();
			}
		});
	}
	
	</Script>
	';

	
}
	
bottom();
?>