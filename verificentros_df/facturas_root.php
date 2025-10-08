<?php
include("main.php");
include("imp_factura.php");
//ARREGLOS
$array_tipo_pag=array('Contado', 'Credito');
$array_usuario[-1] = 'WEB';
$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}


$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$res=mysql_query("SELECT * FROM bancos");
while($row=mysql_fetch_array($res)){
	$array_bancos[$row['cve']]=$row['nombre'];
}

$array_tiporelacionsat=array();
$res = mysql_query("SELECT * FROM tiporelacion_sat ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tiporelacionsat[$row['cve']] = $row['nombre'];
}

$array_clientes=array();
$res=mysql_query("SELECT * FROM clientes WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_clientes[$row['cve']]=$row['nombre'];
	if($row['rfc']=="" || $row['nombre']=="" || $row['calle']=="" || $row['numexterior']=="" || $row['colonia']=="" || $row['municipio']=="" || $row['codigopostal']=="")
		$array_colorcliente[$row['cve']] = "#FF0000";
	else
		$array_colorcliente[$row['cve']] = "#000000";
	$array_clientessinticket[$row['cve']] = $row['facturasinticket'];
}
function mestexto($fec){
	global $array_meses;
	$datos=explode("-",$fec);
	return $array_meses[intval($datos[1])].' '.$datos[0];
}
//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");


$datossucursal='';
if($rowempresa['check_sucursal']==1){
	$datossucursal=",check_sucursal='".$rowempresa['check_sucursal']."',nombre_sucursal='".$rowempresa['nombre_sucursal']."',
	calle_sucursal='".$rowempresa['calle_sucursal']."',numero_sucursal='".$rowempresa['numero_sucursal']."',
	colonia_sucursal='".$rowempresa['colonia_sucursal']."',rfc_sucursal='".$rowempresa['rfc_sucursal']."',
	localidad_sucursal='".$rowempresa['localidad_sucursal']."',municipio_sucursal='".$rowempresa['municipio_sucursal']."',
	estado_sucursal='".$rowempresa['estado_sucursal']."',cp_sucursal='".$rowempresa['cp_sucursal']."'";
}


if($_POST['ajax']==1){
error_reporting(0);
	
	$dom = new DOMDocument;
	
	$filtro="";
	$select= " SELECT a.*,CONCAT(c.numero, ' ', c.nombre) as nomplaza FROM facturas as a 
	LEFT JOIN clientes b ON b.plaza = a.plaza AND b.cve = a.cliente 
	INNER JOIN plazas c ON c.cve = a.plaza 
	WHERE a.plaza>0 and a.estatus!='C' AND a.respuesta1 = '' AND c.estatus!='I'";
	
	$rsabonos=mysql_query($select) or die(mysql_error());
	if(mysql_num_rows($rsabonos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$c=16;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.mysql_num_rows($rsabonos).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		echo '<th>Plaza</th><th>Serie</th><th>Folio</th><th>Fecha</th><th>Tipo Factura</th>
		<th>Cliente</th><th>Tipo Pago</th><th>Subtotal</th>
		<th>Iva</th><th>Total</th>
		<th>Usuario</th></tr>'; 
		$sumacargo=array();
		$x=0;
		$nivelUsuario = nivelUsuario();
		while ($Abono=mysql_fetch_array($rsabonos)){	
			rowb();
			$estatus='';
			
			echo '<td align="center" width="40" nowrap>';
				echo '<input type="button" style=" font-size:25px;" value="TIMBRAR" onClick="if(confirm(\'Esta seguro que desea timbrar?\')){$(\'#panel\').show();atcr(\'facturas_root.php\',\'\',\'5\',\''.$Abono['plaza'].'|'.$Abono['cve'].'\');}"><br>';
			
			echo '</td>';
			echo '<td>'.htmlentities($Abono['nomplaza']).'</td>';
			echo '<td align="center">'.$Abono['serie'].'</td>';
			echo '<td align="center">'.$Abono['folio'].'</td>';
			
			echo '<td align="center">'.htmlentities($Abono['fecha'].' '.$Abono['hora']).'</td>';
			
			echo '<td>'.htmlentities($array_tipo_pag[$Abono['tipo_pag']]).'</td>';
			echo '<td>'.htmlentities(utf8_encode($array_clientes[$Abono['cliente']])).'</td>';
			echo '<td>'.htmlentities($array_tipo_pago[$Abono['tipo_pago']]).'</td>';
			echo '<td align="right">'.number_format($Abono['subtotal'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'],2).'</td>';
			
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$sumacargo[0]+=$Abono['subtotal'];
			$sumacargo[1]+=$Abono['iva'];
			$sumacargo[2]+=$Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'];
		}
		$c=7;
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



if($_POST['cmd']==5){
	$datos = explode('|', $_POST['reg']);

	//require_once("../phpmailer/class.phpmailer.php");
	require_once('../PHPMailer-master/PHPMailerAutoload.php');

	$res = mysql_query("SELECT * FROM facturas WHERE plaza='".$datos[0]."' AND cve='".$datos[1]."'");
	$row = mysql_fetch_array($res);

	$res1 = mysql_query("SELECT * FROM clientes WHERE cve = '".$row['cliente']."'");
	$row1 = mysql_fetch_array($res1);
	
	$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$datos[0]."'");
	$rowempresa = mysql_fetch_array($resempresa);
	
	$cvefact = $datos[1];
	$documento = genera_arreglo_facturacion($datos[0], $datos[1], 'I');
	$resultadotimbres = validar_timbres($datos[0]);
	if($resultadotimbres['seguir']){
		//$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
		$oSoapClient = new nusoap_client("http://integratucfdi.com/webservices/wscfdi.php?wsdl", true);	
		$err = $oSoapClient->getError();
		if($err!=""){
			echo "error1:".$err;
			desbloquear_timbre($datos[0], $resultadotimbres['cvecompra']);
		}
		else{
			//print_r($documento);
			$oSoapClient->timeout = 300;
			$oSoapClient->response_timeout = 300;
			$respuesta = $oSoapClient->call("GenerarComprobante", array ('id' => $rowempresa['idplaza'],'rfcemisor' =>$rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'documento' => $documento, 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
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
				desbloquear_timbre($datos[0], $resultadotimbres['cvecompra']);
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
					desbloquear_timbre($datos[0], $resultadotimbres['cvecompra']);
				}
				else{
					if($respuesta['resultado']){
						mysql_query("UPDATE facturas SET respuesta1='".$respuesta['uuid']."',seriecertificado='".$respuesta['seriecertificado']."',
						sellodocumento='".$respuesta['sellodocumento']."',uuid='".$respuesta['uuid']."',seriecertificadosat='".$respuesta['seriecertificadosat']."',
						sellotimbre='".$respuesta['sellotimbre']."',cadenaoriginal='".$respuesta['cadenaoriginal']."',
						fechatimbre='".substr($respuesta['fechatimbre'],0,10)." ".substr($respuesta['fechatimbre'],-8)."'
						WHERE plaza='".$datos[0]."' AND cve=".$cvefact);

						mysql_query("UPDATE facturas SET rfc_cli='".$row1['rfc']."', nombre_cli='".$row1['nombre']."', calle_cli='".$row1['calle']."', numext_cli='".$row1['numexterior']."', numint_cli = '".$row1['numinterior']."', colonia_cli = '".$row1['colonia']."', localidad_cli = '".$row1['localidad']."', municipio_cli = '".$row1['municipio']."',
							estado_cli='".$row1['estado']."', cp_cli='".$row1['codigopostal']."'
						WHERE plaza='".$datos[0]."' AND cve=".$cvefact);

						//generaFacturaPdf($_POST['plazausuario'],$cvefact);
						//Tomar la informacion de Retorno
						$dir="../cfdi/comprobantes/";
						//$dir=dirname(realpath(getcwd()))."/solucionesfe_facturacion/cfdi/comprobantes/";
						//el zip siempre se deja fuera
						$dir2="../cfdi/";
						//Leer el Archivo Zip
						$fileresult=$respuesta['archivos'];
						$strzipresponse=base64_decode($fileresult);
						$filename='cfdi_'.$datos[0].'_'.$cvefact;
						file_put_contents($dir2.$filename.'.zip', $strzipresponse);
						$zip = new ZipArchive;
						if ($zip->open($dir2.$filename.'.zip') === TRUE){
							$strxml=$zip->getFromName('xml.xml');
							file_put_contents($dir.$filename.'.xml', $strxml);
							//$strpdf=$zip->getFromName('formato.pdf');
							//file_put_contents($dir.$filename.'.pdf', $strpdf);
							$zip->close();	
							generaFacturaPdf($datos[0],$cvefact);
							if($emailenvio!=""){
								$mail = new PHPMailer;		
								$mail->isSMTP();                                      // Set mailer to use SMTP
								$mail->Host = 'smtp.mailgun.org';                     // Specify main and backup SMTP servers
								$mail->SMTPAuth = true;                               // Enable SMTP authentication
								$mail->Username = 'postmaster@verificentrosgp1.net';   // SMTP username
								$mail->Password = '066b945d0b83b43f37d4fbc63a1ec288';                           // SMTP password
								$mail->SMTPSecure = 'tls';                            // Enable encryption, only 'tls' is accepted
								//$mail = new PHPMailer();
								//$mail->Host = "localhost";
								$mail->From = "verificentros@verificentrosgp1.net";
								$mail->FromName = "Verificentros Plaza ".$array_plaza[$$datos[0]];
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
								$mail->AddAttachment("../cfdi/comprobantes/factura_".$datos[0]."_".$cvefact.".pdf", "Factura ".$fserie." ".$ffolio.".pdf");
								$mail->AddAttachment("../cfdi/comprobantes/cfdi_".$datos[0]."_".$cvefact.".xml", "Factura ".$fserie." ".$ffolio.".xml");
								$mail->Send();
							}
							@unlink("../cfdi/comprobantes/factura_".$datos[0]."_".$cvefact.".pdf");
						}
						else 
							$strmsg='Error al descomprimir el archivo';
					}
					else{
						$strmsg=$respuesta['mensaje'];
						desbloquear_timbre($datos[0], $resultadotimbres['cvecompra']);
					}
					//print_r($respuesta);	
					echo $strmsg;
				}
			}
		}
	}
//Recarga por segunda vez	
    $_POST['cmd']='recargar';
	$_POST['reg']=0;
}




top($_SESSION);
if($_POST['cmd']=='recargar'){
	$res = mysql_query("SELECT recargar_facturas FROM usuarios WHERE cve=1");
	$row = mysql_fetch_array($res);
	if($_POST['reg'] < 2 && $row[0]==1){
		echo '<script>atcr("facturas_root.php","","recargar",'.($_POST['reg']+1).');</script>';
	}

	$_POST['cmd'] = 0;
}

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>';
		
		echo '</tr>';
		echo '</table>';
		
		echo '<div id="Resultados">';
		echo '</div>';
	
echo '
<Script language="javascript">
	
	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","facturas_root.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1");
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4){
					document.getElementById("Resultados").innerHTML = objeto.responseText;
				}
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


	</Script>
';
}
bottom();
?>