<?php
include("main2.php");

if($_POST['ajax']==1){
	$res = mysql_query("SELECT * FROM claves_facturacion WHERE cve = '".trim($_POST['codigofactura'])."'");
	if($row=mysql_fetch_array($res)){
		$res=mysql_query("SELECT a.factura,a.estatus,a.cve,a.plaza,a.monto,a.placa,b.nombre as engomado,c.nombre as nomplaza 
		FROM cobro_engomado a 
		INNER JOIN engomados b ON b.cve = a.engomado 
		INNER JOIN plazas c ON c.cve = a.plaza 
		WHERE a.plaza = '".$row['plaza']."' AND a.cve='".$row['ticket']."'");
		$row = mysql_fetch_array($res);
		if($row['estatus']=='C'){
			echo '<h1>El ticket esta cancelado</h1>';
		}
		elseif($row['factura']>0){
			echo '<h1>El ticket ya se encuentra facturado</h1>';
		}
		else{
			echo '<table>
			<tr><th align="left">Plaza</th><td>'.$row['nomplaza'].'</td></tr>
			<tr><th align="left">Ticket</th><td>'.$row['cve'].'</td></tr>
			<tr><th align="left">Tipo de Certificado</th><td>'.$row['engomado'].'</td></tr>
			<tr><th align="left">Importe</th><td>'.$row['monto'].'</td></tr></table>';
			echo '<input type="hidden" name="ticket" id="ticket" value="'.$row['cve'].'">
			<input type="hidden" name="plaza" id="plaza" value="'.$row['plaza'].'">
			<input type="hidden" name="placat" id="placat" value="'.$row['placa'].'">';
		}
	}
	else{
		echo '<h1>No se encontr&oacute; la clave</h1>';
	}

	exit();
}

top();


include("imp_factura.php");

if($_POST['cmd']==2){
	$res = mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plaza']."' AND cve='".$_POST['ticket']."' AND estatus!='C' AND factura = '0'");
	if($row=mysql_fetch_array($res)){
		mysql_query("INSERT clientes SET plaza='".$_POST['plaza']."',fechayhora=NOW(),usuario='-1',
							rfc='".$_POST['rfc']."',nombre='".$_POST['nombre']."',email='".$_POST['email']."',calle='".$_POST['calle']."',
							numexterior='".$_POST['numexterior']."',numinterior='".$_POST['numinterior']."',colonia='".$_POST['colonia']."',
							municipio='".$_POST['municipio']."',estado='".$_POST['estado']."',codigopostal='".$_POST['codigopostal']."'");
	
		$cliente_id = mysql_insert_id();
		$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plaza']."'");
		$rowempresa = mysql_fetch_array($resempresa);
		$resplaza = mysql_query("SELECT * FROM plazas WHERE cve='".$_POST['plaza']."'");
		$rowplaza = mysql_fetch_array($resplaza);
		$datossucursal='';
		if($rowempresa['check_sucursal']==1){
			$datossucursal=",check_sucursal='".$rowempresa['check_sucursal']."',nombre_sucursal='".$rowempresa['nombre_sucursal']."',
			calle_sucursal='".$rowempresa['calle_sucursal']."',numero_sucursal='".$rowempresa['numero_sucursal']."',
			colonia_sucursal='".$rowempresa['colonia_sucursal']."',rfc_sucursal='".$rowempresa['rfc_sucursal']."',
			localidad_sucursal='".$rowempresa['localidad_sucursal']."',municipio_sucursal='".$rowempresa['municipio_sucursal']."',
			estado_sucursal='".$rowempresa['estado_sucursal']."',cp_sucursal='".$rowempresa['cp_sucursal']."'";
		}
		require_once("../phpmailer/class.phpmailer.php");
	
		$array_detalles = array();
		$ventas = '';
		$res=mysql_query("SELECT a.cve, b.monto FROM cobro_engomado a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket WHERE a.plaza='".$_POST['plaza']."' AND a.cve='".$_POST['ticket']."' AND a.estatus!='C' AND a.factura=0");
		while($row=mysql_fetch_array($res)){
			$array_detalles[$row['engomado']]['cant']+=1;
			$array_detalles[$row['engomado']]['monto']+=$row['monto'];
			$ventas .= ','.$row['cve'];
		}
		if(count($array_detalles)>0){
			$res = mysql_query("SELECT folio_inicial FROM foliosiniciales WHERE plaza='".$_POST['plaza']."' AND tipo=0 AND tipodocumento=1");
			$row = mysql_fetch_array($res);
			$res1 = mysql_query("SELECT cve FROM facturas WHERE plaza='".$_POST['plaza']."'");
			if(mysql_num_rows($res1) > 0){
				mysql_query("INSERT facturas SET plaza='".$_POST['plaza']."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
				cliente='".$cliente_id."',tipo_pago='0',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
				carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
				tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."'".$datossucursal) or die(mysql_error());
			}
			else{
				mysql_query("INSERT facturas SET plaza='".$_POST['plaza']."',cve='".$row['folio_inicial']."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
				cliente='".$cliente_id."',tipo_pago='0',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
				carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
				tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."'".$datossucursal) or die(mysql_error());
			}
			$cvefact=mysql_insert_id();
	
			$documento=array();
			require_once("../nusoap/nusoap.php");
			//Generamos la Factura
			$documento['serie']=$rowplaza['numero'];
			$documento['folio']=$cvefact;
			$documento['fecha']=fechaLocal().' '.horaLocal();
			$documento['formapago']='PAGO EN UNA SOLA EXHIBICION';
			$documento['idtipodocumento']=1;
			$documento['observaciones']=$_POST['obs'];
			$documento['metodopago']=$array_tipo_pago[0];
			$res = mysql_query("SELECT * FROM clientes WHERE cve='".$cliente_id."'");
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
				mysql_query("INSERT facturasmov SET plaza='".$_POST['plaza']."',cvefact='$cvefact',cantidad='".$v['cant']."',
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
			WHERE plaza='".$_POST['plaza']."' AND cve=".$cvefact);
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
			mysql_query("UPDATE cobro_engomado SET factura='".$cvefact."',documento=1 WHERE plaza='".$_POST['plaza']."' AND cve IN (".substr($ventas,1).")");
			mysql_query("INSERT INTO venta_engomado_factura (plaza,venta,factura) SELECT ".$_POST['plaza'].",cve,factura FROM cobro_engomado WHERE plaza='".$_POST['plaza']."' AND factura='".$cvefact."'");
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
								$strpdf=$zip->getFromName('formato.pdf');
								file_put_contents($dir.$filename.'.pdf', $strpdf);
								$zip->close();		
								generaFacturaPdf($_POST['plaza'],$cvefact);
								if($emailenvio!=""){
									$mail = new PHPMailer();
									$mail->Host = "localhost";
									$mail->From = "verificentros@verificentros.net";
									$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plaza']];
									$mail->Subject = "Factura ".$cvefact;
									$mail->Body = "Factura ".$cvefact;
									//$mail->AddAddress(trim($emailenvio));
									$correos = explode(",",trim($emailenvio));
									foreach($correos as $correo)
										$mail->AddAddress(trim($correo));
									$mail->AddAttachment("../cfdi/comprobantes/factura_".$_POST['plaza']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
									$mail->AddAttachment("../cfdi/comprobantes/cfdi_".$_POST['plaza']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
									$mail->Send();
								}	
								if($rowempresa['email']!=""){
									$mail = new PHPMailer();
									$mail->Host = "localhost";
									$mail->From = "verificentros@verificentros.net";
									$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plaza']];
									$mail->Subject = "Factura ".$cvefact;
									$mail->Body = "Factura ".$cvefact;
									//$mail->AddAddress(trim($rowempresa['email']));
									$correos = explode(",",trim($rowempresa['email']));
									foreach($correos as $correo)
										$mail->AddAddress(trim($correo));
									$mail->AddAttachment("../cfdi/comprobantes/factura_".$_POST['plaza']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
									$mail->AddAttachment("../cfdi/comprobantes/cfdi_".$_POST['plaza']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
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

echo '<h1>Facturaci&oacute;n</h1>';

echo '<script>
				function validarRFC(){
					var ValidChars2 = "0123456789";
					var ValidChars1 = "abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ&";
					var cadena=document.getElementById("rfc").value;
					correcto = true;
					if(cadena.length!=13 && cadena.length!=12){
						correcto = false;
					}
					else{
						if(cadena.length==12)
							resta=1;
						else
							resta=0;
						for(i=0;i<cadena.length;i++) {
							digito=cadena.charAt(i);
							if (i<(4-resta) && ValidChars1.indexOf(digito) == -1){
								correcto = false;
							}
							if (i>=(4-resta) && i<(10-resta) && ValidChars2.indexOf(digito) == -1){
								correcto = false;
							}
							if (i>=(10-resta) && ValidChars1.indexOf(digito) == -1 && ValidChars2.indexOf(digito) == -1){
								correcto = false;
							}
						}
					}
					return correcto;
				}
			
				function validar(){
					if(document.getElementById("nombre").value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar el nombre");
					}
					else if(document.getElementById("email").value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar el email");
					}
					else if(document.getElementById("email").value!="" && document.getElementById("confirmacionemail").value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar la confirmacion email");
					}
					else if(document.getElementById("email").value!="" && document.getElementById("confirmacionemail").value!=document.getElementById("email").value){
						$(\'#panel\').hide();
						alert("No son iguales los emails");
					}
					else if(document.forma.rfc.value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar el rfc");
					}
					else if(document.forma.calle.value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar la calle");
					}
					else if(document.forma.numexterior.value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar el número exterior");
					}
					else if(document.forma.colonia.value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar la colonia");
					}
					else if(document.forma.municipio.value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar el municipio");
					}
					else if(document.forma.estado.value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar el estado");
					}
					else if(document.forma.codigopostal.value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar el código postal");
					}
					else if($("#ticket").length==0){
						$(\'#panel\').hide();
						alert("No se ha cargado correctamente el ticket");
					}
					else if($.trim(document.forma.placa.value)!=$.trim(document.forma.placat.value)){
						$(\'#panel\').hide();
						alert("La placa capturada no coincide con la placa del ticket");
					}
					elseif(confirm("Esta seguro de seguir la factura se timbrara al guardarla?")){
						atcr("facturacion_web.php","",2,0);
					}
				}
			
				function agregar_cuenta(){
					$("#cuentas").append(\'<tr>\
					<td align="center"><select name="banco[]"><option value="">Seleccione</option>'.$bancos.'</select></td>\
					<td align="center"><input type="text" class="textField" name="cuenta[]" value=""></td></tr>\');
				}
			</script>';
	
		//Menu
		echo '<table>';
		echo '
			<tr>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();if(document.getElementById(\'requiere_factura_1\').checked == false || validarRFC()){validar(\'\');} else{ $(\'#panel\').hide(); alert(\'RFC invalido\');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<br>';

echo '<table>';
echo '<tr><th align="left">Placa</th><td><input type="text" class="textField" name="placa" id="placa" value="" size="10" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
echo '<tr><th align="left">Nombre</th><td><input type="text" class="textField" name="nombre" id="nombre" value="" size="50" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
echo '<tr><th align="left">Correo electr&oacute;nico</th><td><input type="text" class="textField" name="email" id="email" value="" size="100"></td></tr>';
echo '<tr><th align="left">Confirmaci&oacute;n correo electr&oacute;nico</th><td><input type="text" class="textField" id="confirmacionemail" value="" size="100"><br>En caso de no encontrar el correo en su bandeja de entrada buscarlo en correo no deseado(spam)</td></tr>';
echo '<tr><th align="left">RFC</th><td><input type="text" class="textField" name="rfc" id="rfc" value="" size="15" maxlength="13" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
echo '<tr><th align="left">Calle</th><td><input type="text" class="textField" name="calle" id="calle" value="'.$row['calle'].'" size="30" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
echo '<tr><th align="left">N&uacute;mero exterior</th><td><input type="text" class="textField" name="numexterior" id="numexterior" value="'.$row['numexterior'].'" size="10" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
echo '<tr><th align="left">N&uacute;mero interior</th><td><input type="text" class="textField" name="numinterior" id="numinterior" value="'.$row['numinterior'].'" size="10" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
echo '<tr><th align="left">Colonia</th><td><input type="text" class="textField" name="colonia" id="colonia" value="'.$row['colonia'].'" size="30" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
echo '<tr><th align="left">Localidad</th><td><input type="text" class="textField" name="localidad" id="localidad" value="'.$row['localidad'].'" size="50" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
echo '<tr><th align="left">Municipio</th><td><input type="text" class="textField" name="municipio" id="municipio" value="'.$row['municipio'].'" size="50" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
echo '<tr><th align="left">Estado</th><td><input type="text" class="textField" name="estado" id="estado" value="'.$row['estado'].'" size="50" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
echo '<tr><th align="left">C&oacute;digo Postal</th><td><input type="text" class="textField" name="codigopostal" id="codigopostal" value="'.$row['codigopostal'].'" size="50"></td></tr>';
echo '<tr><th align="left">C&oacute;digo de Facturaci&oacute;n</th><td><input type="text" class="textField" name="codigofactura" id="codigofactura" value="">&nbsp;&nbsp;<input type="button" class="textField" value="Buscar" onClick="buscarcodigo()"></td></tr>';
echo '</table>';
echo '<div id="Resultados">';
echo '</div>';
echo '<script>

		function buscarcodigo(){
			if($.trim(document.forma.codigofactura.value)==""){
				alert("Necesita ingresar el código de facturación");
			}
			else{
				$.ajax({
				  url: "facturacion_web.php",
				  type: "POST",
				  async: false,
				  data: {
					codigofactura: document.getElementById("codigofactura").value,
					ajax: 1
				  },
					success: function(data) {
						$("#Resultados").html(data);
					}
				});
			}
		}
	</script>';
bottom();

?>