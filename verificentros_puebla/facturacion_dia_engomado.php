<?php 

exit();

include ("subs/cnx_db.php"); 

function fechaLocal(){
	return date("Y-m-d");
}

function horaLocal(){
	return date("H:i:s");
}

$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio'];
}

$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$array_tipo_pago = array();
$res = mysql_query("SELECT * FROM tipos_pago WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}

$array_plaza = array();
$res = mysql_query("SELECT * FROM plazas WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_plaza[$row['cve']]=$row['numero'];
}


include("imp_factura.php");
$resVentas = mysql_query("SELECT plaza FROM cobro_engomado WHERE fecha='".fechaLocal()."' AND estatus!='C' AND factura=0 GROUP BY plaza");
while($rowVentas = mysql_fetch_array($resVentas)){
	$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$rowVentas['plaza']."'");
	$rowempresa = mysql_fetch_array($resempresa);
	require_once("phpmailer/class.phpmailer.php");

	$array_detalles = array();
	$ventas = '';
	$res=mysql_query("SELECT cve FROM clientes WHERE plaza='".$rowVentas['plaza']."' AND rfc='XAXX010101000'");
	$row=mysql_fetch_array($res);
	$cliente_facturar=$row['cve'];
	$res=mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$rowVentas['plaza']."' AND fecha='".fechaLocal()."' AND estatus!='C' AND factura=0");
	while($row=mysql_fetch_array($res)){
		$array_detalles[$row['engomado']]['cant']+=1;
		$array_detalles[$row['engomado']]['monto']+=$row['monto'];
		$ventas .= ','.$row['cve'];
	}
	if($cliente_facturar>0 && count($array_detalles)>0){
		$res = mysql_query("SELECT folio_inicial FROM foliosiniciales WHERE plaza='".$rowVentas['plaza']."' AND tipo=0 AND tipodocumento=1");
		$row = mysql_fetch_array($res);
		$res1 = mysql_query("SELECT cve FROM facturas WHERE plaza='".$rowVentas['plaza']."'");
		if(mysql_num_rows($res1) > 0){
			mysql_query("INSERT facturas SET plaza='".$rowVentas['plaza']."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
			cliente='".$cliente_facturar."',tipo_pago='0',usuario='13',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
			carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
			tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."'") or die(mysql_error());
		}
		else{
			mysql_query("INSERT facturas SET plaza='".$rowVentas['plaza']."',cve='".$row['folio_inicial']."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
			cliente='".$cliente_facturar."',tipo_pago='0',usuario='13',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
			carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
			tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."'") or die(mysql_error());
		}
		$cvefact=mysql_insert_id();

		$documento=array();
		require_once("nusoap/nusoap.php");
		//Generamos la Factura
		$documento['serie']=$array_plaza[$rowVentas['plaza']];
		$documento['folio']=$cvefact;
		$documento['fecha']=fechaLocal().' '.horaLocal();
		$documento['formapago']='PAGO EN UNA SOLA EXHIBICION';
		$documento['idtipodocumento']=1;
		$documento['observaciones']=$_POST['obs'];
		$documento['metodopago']=$array_tipo_pago[0];
		$res = mysql_query("SELECT * FROM clientes WHERE cve='".$cliente_facturar."'");
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
			mysql_query("INSERT facturasmov SET plaza='".$rowVentas['plaza']."',cvefact='$cvefact',cantidad='".$v['cant']."',
			concepto='Venta de Engomado ".$array_engomado[$k]."',
			precio='".round(round($v['monto']-$importe_iva,2)/$v['cant'],2)."',importe='".round($v['monto']-$importe_iva,2)."',
			iva='16',importe_iva='$importe_iva',unidad='No Aplica'");
			$documento['conceptos'][$i]['cantidad']=$v['cant'];
			$documento['conceptos'][$i]['unidad']='No Aplica';
			$documento['conceptos'][$i]['descripcion']='Venta de Engomado '.$array_engomado[$k];
			$documento['conceptos'][$i]['valorUnitario']=round(round($v['monto']-$importe_iva,2)/$v['cant'],2);
			$documento['conceptos'][$i]['importe']=round($v['monto']-$importe_iva,2);
			$documento['conceptos'][$i]['importe_iva']=$importe_iva;
			$i++;
		}
		mysql_query("UPDATE facturas SET subtotal='".$subtotal."',iva='".$iva."',total='".$total."',
		isr_retenido='".$_POST['isr_retenido']."',por_isr_retenido='".$_POST['por_isr_retenido']."',
		iva_retenido='".$_POST['iva_retenido']."',por_iva_retenido='".$_POST['por_iva_retenido']."' 
		WHERE plaza='".$rowVentas['plaza']."' AND cve=".$cvefact);
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
		mysql_query("UPDATE cobro_engomado SET factura='".$cvefact."',documento=1 WHERE plaza='".$rowVentas['plaza']."' AND cve IN (".substr($ventas,1).")");
		mysql_query("INSERT INTO venta_engomado_factura (plaza,venta,factura) SELECT ".$rowVentas['plaza'].",cve,factura FROM cobro_engomado WHERE plaza='".$rowVentas['plaza']."' AND factura='".$cvefact."'");
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
						WHERE plaza='".$rowVentas['plaza']."' AND cve=".$cvefact);
						//Tomar la informacion de Retorno
						$dir="cfdi/comprobantes/";
						//$dir=dirname(realpath(getcwd()))."/solucionesfe_facturacion/cfdi/comprobantes/";
						//el zip siempre se deja fuera
						$dir2="cfdi/";
						//Leer el Archivo Zip
						$fileresult=$respuesta['archivos'];
						$strzipresponse=base64_decode($fileresult);
						$filename='cfdi_'.$rowVentas['plaza'].'_'.$cvefact;
						file_put_contents($dir2.$filename.'.zip', $strzipresponse);
						$zip = new ZipArchive;
						if ($zip->open($dir2.$filename.'.zip') === TRUE){
							$strxml=$zip->getFromName('xml.xml');
							file_put_contents($dir.$filename.'.xml', $strxml);
							$strpdf=$zip->getFromName('formato.pdf');
							file_put_contents($dir.$filename.'.pdf', $strpdf);
							$zip->close();		
							generaFacturaPdf($rowVentas['plaza'],$cvefact);
							if($emailenvio!=""){
								$mail = new PHPMailer();
								$mail->Host = "localhost";
								$mail->From = "vereficentros@vereficentros.com";
								$mail->FromName = "Vereficentros Plaza ".$array_plaza[$_POST['plazausuario']];
								$mail->Subject = "Factura ".$cvefact;
								$mail->Body = "Factura ".$cvefact;
								//$mail->AddAddress(trim($emailenvio));
								$correos = explode(",",trim($emailenvio));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("cfdi/comprobantes/factura_".$rowVentas['plaza']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
								$mail->AddAttachment("cfdi/comprobantes/cfdi_".$rowVentas['plaza']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
								$mail->Send();
							}	
							if($rowempresa['email']!=""){
								$mail = new PHPMailer();
								$mail->Host = "localhost";
								$mail->From = "vereficentros@vereficentros.com";
								$mail->FromName = "Vereficentros Plaza ".$array_plaza[$rowVentas['plaza']];
								$mail->Subject = "Factura ".$cvefact;
								$mail->Body = "Factura ".$cvefact;
								//$mail->AddAddress(trim($rowempresa['email']));
								$correos = explode(",",trim($rowempresa['email']));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("cfdi/comprobantes/factura_".$rowVentas['plaza']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
								$mail->AddAttachment("cfdi/comprobantes/cfdi_".$rowVentas['plaza']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
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
	