<?php
include("main2_beta.php");
$array_forma_pago=array("PAGO EN UNA SOLA EXHIBICION");
$array_tipo_pago=array(0=>"EFECTIVO",2=>"TRANSFERENCIA",3=>"DEPOSITO",4=>"NO ESPECIFICADO",5=>"CHEQUE DENOMINATIVO",6=>"NO APLICA",7=>"CREDITO");//,1=>"CHEQUE"


if($_POST['cmd'] == 200){
	$datos = explode('|', $_POST['reg']);
	$zip = new ZipArchive();
	if($zip->open("cfdi/zipfactura_".$datos[0]."_".$datos[1].".zip",ZipArchive::CREATE)){
		$zip->addFile("cfdi/comprobantes/factura_".$datos[0]."_".$datos[1].".pdf","Factura.pdf");
		$zip->addFile("cfdi/comprobantes/cfdi_".$datos[0]."_".$datos[1].".xml","Factura.xml");
		$zip->close(); 
	    if(file_exists("cfdi/zipfactura_".$datos[0]."_".$datos[1].".zip")){ 
	        header('Content-type: "application/zip"'); 
	        header('Content-Disposition: attachment; filename="Factura_'.$datos[1].'.zip"'); 
	        readfile("cfdi/zipfactura_".$datos[0]."_".$datos[1].".zip"); 
	         
	        unlink("cfdi/zipfactura_".$datos[0]."_".$datos[1].".zip"); 
	    } 
	    else{
			echo '<h1>Ocurrio un problema al generar el archivo favor de intentarlo de nuevo</h1>';
		}
	}
	else{
		echo '<h1>Ocurrio un problema al generar el archivo favor de intentarlo de nuevo</h1>';
	}
	exit();
}

if($_POST['ajax']==1){
	$resultado = array('error' => 0, 'html' => '', 'mensaje' => '');
	$res = mysql_query("SELECT * FROM claves_facturacion WHERE cve = '".trim($_POST['codigofactura'])."'");
	if($row=mysql_fetch_array($res)){
		$tickets_capturados = json_decode($_POST['tickets_capturados'], true);
		$encontrado = false;
		foreach($tickets_capturados as $ticket_capturado){
			if($ticket_capturado['plaza'] = $row['plaza'] && $ticket_capturado['cve'] == $row['ticket']){
				$encontrado = true;
			}
		}

		if($encontrado){
			$resultado['error'] = 1;
			$resultado['mensaje'] = 'El ticket ya esta capturado';
		}
		else{
			$res=mysql_query("SELECT a.tipo_pago,a.fecha,a.factura,a.estatus,a.cve,a.plaza,(a.monto+IFNULL(f.recuperacion,0)-IFNULL(e.devolucion,0)) as monto,a.placa,b.nombre as engomado,c.nombre as nomplaza 
			FROM cobro_engomado a 
			LEFT JOIN engomados b ON b.cve = a.engomado 
			INNER JOIN plazas c ON c.cve = a.plaza 
			INNER JOIN datosempresas d ON c.cve = d.plaza
			LEFT JOIN 
				(SELECT ticket, SUM(devolucion) as devolucion FROM devolucion_certificado WHERE plaza = '".$row['plaza']."' AND estatus != 'C' AND ticket='".$row['ticket']."') e ON a.cve = e.ticket
			LEFT JOIN 
				(SELECT ticket, SUM(recuperacion) as recuperacion FROM recuperacion_certificado WHERE plaza = '".$row['plaza']."' AND estatus != 'C' AND ticket='".$row['ticket']."') f ON a.cve = f.ticket
			WHERE a.plaza = '".$row['plaza']."' AND a.cve='".$row['ticket']."'");
			$row = mysql_fetch_array($res);
			if($row['estatus']=='C'){
				$resultado['error'] = 1;
				$resultado['mensaje'] = 'El ticket esta cancelado';
			}
			elseif($row['placa'] != $_POST['placa']){
				$resultado['error'] = 1;
				$resultado['mensaje'] = 'La placa capturada no coincide con la del ticket';
			}
			elseif($row['factura']>0){
				$resultado['html'] .=  '
				<tr><td align="center"><span style="cursor:pointer;" onClick="if(confirm(\'Esta seguro de quitar el ticket?\')) quitar_ticket($(this))"><img src="images/validono.gif"></span>';
				$res1=mysql_query("SELECT * FROM facturas WHERE plaza='".$row['plaza']."' AND cve='".$row['factura']."'");
				$row1=mysql_fetch_array($res1);
				if($row1['respuesta1']==""){
					$resultado['html'] .= '<br><b>No Timbrada</b>';
				}
				else{
					$resultado['html'] .=  '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturacion_web.php\',\'_blank\',\'200\',\''.$row1['plaza'].'|'.$row1['cve'].'\');"><img src="images/zip_grande.png" border="0" width="15px" height="15px" title="Descargar"></a>';
				}
				$resultado['html'] .=  '</td>
				<td align="left" style="padding: 10px!important">'.$row['nomplaza'].'</td>
				<td align="center" style="padding: 10px!important">'.$row['cve'].'</td>
				<td align="left" style="padding: 10px!important">'.$row['engomado'].'</td>
				<td align="right" style="padding: 10px!important">'.$row['monto'].'';
				$resultado['html'] .=  '</td></tr>';
			}
			elseif($row['fecha']<'2015-05-01'){
				$resultado['error'] = 1;
				$resultado['mensaje'] = 'Solo se pueden facturar por esta pagina ventas de mayo 2015 en adelante';
			}
			elseif($row['fecha']<date("Y-m").'-01' && $row['fecha']>=$mesanterior && date("d")>'07'){
				$resultado['error'] = 1;
				$resultado['mensaje'] = 'Vencio el limite de facturacion del mes anterior';
			}
			elseif($row['tipo_pago'] == 2 || $row['tipo_pago'] == 6){
				$resultado['error'] = 1;
				$resultado['mensaje'] = 'No se pueden facturar pagos a credito o con pago anticipado';
			}
			else{
				$resultado['html'] .=  '
				<tr><td align="center"><span style="cursor:pointer;" onClick="if(confirm(\'Esta seguro de quitar el ticket?\')) quitar_ticket($(this))"><img src="images/validono.gif"></span></td>
				<td align="left" style="padding: 10px!important">'.$row['nomplaza'].'</td>
				<td align="center" style="padding: 10px!important">'.$row['cve'].'</td>
				<td align="left" style="padding: 10px!important">'.$row['engomado'].'</td>
				<td align="right" style="padding: 10px!important">'.$row['monto'].'';
				$resultado['html'] .=  '<input type="hidden" name="ticket[]" id="ticket_'.$row['plaza'].'_'.$row['cve'].'" plaza="'.$row['plaza'].'" class="tickets" value="'.$row['cve'].'">
				<input type="hidden" name="plaza[]" id="plaza_'.$row['plaza'].'_'.$row['cve'].'" value="'.$row['plaza'].'">
				<input type="hidden" name="placat[]" id="placat_'.$row['plaza'].'_'.$row['cve'].'" value="'.$row['placa'].'"></td></tr>';
			}
		}
	}
	else{
		$resultado['error'] = 1;
		$resultado['mensaje'] = 'No se encontr&oacute; la clave';
	}
	echo json_encode($resultado);
	exit();
}

top();


include("imp_factura.php");

if($_POST['cmd']==2){
	foreach($_POST as $k=>$v){
		if($k!='plaza' && $k!='placat' && $k!='ticket'){
			$_POST[$k] = mb_strtoupper(utf8_decode($v));
		}
	}
	$plaza = $_POST['plaza'][0];
	$tickets = implode(',',$_POST['ticket']);
	$res = mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$plaza."' AND cve IN (".$tickets.") AND estatus!='C' AND factura = '0'");
	if($row=mysql_fetch_array($res)){
		mysql_query("INSERT clientes SET plaza='".$plaza."',fechayhora=NOW(),usuario='-1',
							rfc='".$_POST['rfc']."',nombre='".addslashes($_POST['nombre'])."',email='".$_POST['email']."',calle='".addslashes($_POST['calle'])."',
							numexterior='".addslashes($_POST['numexterior'])."',numinterior='".addslashes($_POST['numinterior'])."',colonia='".addslashes($_POST['colonia'])."',
							municipio='".addslashes($_POST['municipio'])."',estado='".addslashes($_POST['estado'])."',codigopostal='".$_POST['codigopostal']."'");
	
		$cliente_id = mysql_insert_id();
		$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$plaza."'");
		$rowempresa = mysql_fetch_array($resempresa);
		$resplaza = mysql_query("SELECT * FROM plazas WHERE cve='".$plaza."'");
		$rowplaza = mysql_fetch_array($resplaza);
		$datossucursal='';
		if($rowempresa['check_sucursal']==1){
			$datossucursal=",check_sucursal='".$rowempresa['check_sucursal']."',nombre_sucursal='".addslashes($rowempresa['nombre_sucursal'])."',
			calle_sucursal='".addslashes($rowempresa['calle_sucursal'])."',numero_sucursal='".$rowempresa['numero_sucursal']."',
			colonia_sucursal='".addslashes($rowempresa['colonia_sucursal'])."',rfc_sucursal='".$rowempresa['rfc_sucursal']."',
			localidad_sucursal='".addslashes($rowempresa['localidad_sucursal'])."',municipio_sucursal='".addslashes($rowempresa['municipio_sucursal'])."',
			estado_sucursal='".addslashes($rowempresa['estado_sucursal'])."',cp_sucursal='".$rowempresa['cp_sucursal']."'";
		}
		require_once("phpmailer/class.phpmailer.php");
	
		$array_detalles = array();
		$ventas = '';
		$res=mysql_query("SELECT a.cve, (a.monto+IFNULL(f.recuperacion,0)-IFNULL(e.devolucion,0)) as monto FROM cobro_engomado a 
			LEFT JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket AND b.estatus!='C' 
			LEFT JOIN 
				(SELECT ticket, SUM(devolucion) as devolucion FROM devolucion_certificado WHERE plaza = '".$plaza."' AND estatus != 'C' AND ticket IN (".$tickets.") GROUP BY ticket) e ON a.cve = e.ticket
			LEFT JOIN 
				(SELECT ticket, SUM(recuperacion) as recuperacion FROM recuperacion_certificado WHERE plaza = '".$plaza."' AND estatus != 'C' AND ticket IN (".$tickets.") GROUP BY ticket) f ON a.cve = f.ticket
			WHERE a.plaza='".$plaza."' AND a.cve IN (".$tickets.") AND a.estatus!='C' AND a.factura=0 GROUP BY a.cve");
		while($row=mysql_fetch_array($res)){
			$array_detalles[$row['engomado']]['cant']+=1;
			$array_detalles[$row['engomado']]['monto']+=$row['monto'];
			$ventas .= ','.$row['cve'];
		}
		if(count($array_detalles)>0){
			$ip = getRealIP();
			$res = mysql_query("SELECT folio_inicial FROM foliosiniciales WHERE plaza='".$plaza."' AND tipo=0 AND tipodocumento=1");
			$row = mysql_fetch_array($res);
			$res1 = mysql_query("SELECT cve FROM facturas WHERE plaza='".$plaza."'");
			if(mysql_num_rows($res1) > 0){
				mysql_query("INSERT facturas SET plaza='".$plaza."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',ip='$ip',
				cliente='".$cliente_id."',tipo_pago='0',usuario='-1',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
				carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
				tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."'".$datossucursal) or die(mysql_error());
			}
			else{
				mysql_query("INSERT facturas SET plaza='".$plaza."',cve='".$row['folio_inicial']."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',ip='$ip',
				cliente='".$cliente_id."',tipo_pago='0',usuario='-1',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
				carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
				tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."'".$datossucursal) or die(mysql_error());
			}
			$cvefact=mysql_insert_id();
	
			$documento=array();
			require_once("nusoap/nusoap.php");
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
				mysql_query("INSERT facturasmov SET plaza='".$plaza."',cvefact='$cvefact',cantidad='".$v['cant']."',
				concepto='VERIFICACION VEHICULAR',
				precio='".round(round($v['monto']-$importe_iva,2)/$v['cant'],2)."',importe='".round($v['monto']-$importe_iva,2)."',
				iva='16',importe_iva='$importe_iva',unidad='No Aplica'");
				$documento['conceptos'][$i]['cantidad']=$v['cant'];
				$documento['conceptos'][$i]['unidad']='No Aplica';
				$documento['conceptos'][$i]['descripcion']='VERIFICACION VEHICULAR';
				$documento['conceptos'][$i]['valorUnitario']=round(round($v['monto']-$importe_iva,2)/$v['cant'],2);
				$documento['conceptos'][$i]['importe']=round($v['monto']-$importe_iva,2);
				$documento['conceptos'][$i]['importe_iva']=$importe_iva;
				$i++;
			}
			mysql_query("UPDATE facturas SET subtotal='".$subtotal."',iva='".$iva."',total='".$total."',
			isr_retenido='".$_POST['isr_retenido']."',por_isr_retenido='".$_POST['por_isr_retenido']."',
			iva_retenido='".$_POST['iva_retenido']."',por_iva_retenido='".$_POST['por_iva_retenido']."' 
			WHERE plaza='".$plaza."' AND cve=".$cvefact);
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
			mysql_query("UPDATE cobro_engomado SET factura='".$cvefact."',documento=1 WHERE plaza='".$plaza."' AND cve IN (".substr($ventas,1).")");
			mysql_query("INSERT INTO venta_engomado_factura (plaza,venta,factura) SELECT ".$plaza.",cve,factura FROM cobro_engomado WHERE plaza='".$plaza."' AND factura='".$cvefact."'");
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
							WHERE plaza='".$plaza."' AND cve=".$cvefact);
							//Tomar la informacion de Retorno
							$dir="cfdi/comprobantes/";
							//$dir=dirname(realpath(getcwd()))."/solucionesfe_facturacion/cfdi/comprobantes/";
							//el zip siempre se deja fuera
							$dir2="cfdi/";
							//Leer el Archivo Zip
							$fileresult=$respuesta['archivos'];
							$strzipresponse=base64_decode($fileresult);
							$filename='cfdi_'.$plaza.'_'.$cvefact;
							file_put_contents($dir2.$filename.'.zip', $strzipresponse);
							$zip = new ZipArchive;
							if ($zip->open($dir2.$filename.'.zip') === TRUE){
								$strxml=$zip->getFromName('xml.xml');
								file_put_contents($dir.$filename.'.xml', $strxml);
								$strpdf=$zip->getFromName('formato.pdf');
								file_put_contents($dir.$filename.'.pdf', $strpdf);
								$zip->close();		
								generaFacturaPdf($plaza,$cvefact);
								if($emailenvio!=""){
									$mail = new PHPMailer();
									$mail->Host = "localhost";
									$mail->From = "verificentros@verificentros.net";
									$mail->FromName = "Verificentros Plaza ".$array_plaza[$plaza];
									$mail->Subject = "Factura ".$cvefact;
									$mail->Body = "Factura ".$cvefact;
									//$mail->AddAddress(trim($emailenvio));
									$correos = explode(",",trim($emailenvio));
									foreach($correos as $correo)
										$mail->AddAddress(trim($correo));
									$mail->AddAttachment("cfdi/comprobantes/factura_".$plaza."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
									$mail->AddAttachment("cfdi/comprobantes/cfdi_".$plaza."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
									$mail->Send();
								}	
								if($rowempresa['email']!=""){
									$mail = new PHPMailer();
									$mail->Host = "localhost";
									$mail->From = "verificentros@verificentros.net";
									$mail->FromName = "Verificentros Plaza ".$array_plaza[$plaza];
									$mail->Subject = "Factura ".$cvefact;
									$mail->Body = "Factura ".$cvefact;
									//$mail->AddAddress(trim($rowempresa['email']));
									$correos = explode(",",trim($rowempresa['email']));
									foreach($correos as $correo)
										$mail->AddAddress(trim($correo));
									$mail->AddAttachment("cfdi/comprobantes/factura_".$plaza."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
									$mail->AddAttachment("cfdi/comprobantes/cfdi_".$plaza."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
									$mail->Send();
								}	
							}
							else 
								$strmsg='Error al descomprimir el archivo';
								
							echo '<h1>Se genero el folio de factura '.$cvefact.'</h1>';
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
?>
<div id="dialog" style="display:none;"><h1>El horario de facturaci&oacute;n es de 7:00 a 23:00 horas.</h1></div>
<div class="container">
<section class="col-sm-9">

    <h1 class="text-center">Elabora tu factura</h1>
    <p class="text-center">Capture la información solicitada y finalice dando clic al botón<b>"Generar factura"</b></br>
	Campos marcados con <span class="label label-danger">borde rojo</span> son obligatorios</p>
    <hr />    
    
    <div class="form-group">
        <label for="nombre" class="col-sm-3 control-label">Razon Social</label>
        <div class="col-sm-6">
            <input type="text" class="required mayusculas form-control" id="nombre" name="nombre" onKeyUp="this.value=this.value.toUpperCase();">
        </div>
    </div>
    
    <div class="form-group">
        <label for="email" class="col-sm-3 control-label">Correo electrónico</label>
        <div class="col-sm-6">
            <input type="text" class="required form-control" id="email" name="email">
			<br><span class="text-muted"><small>Si desea entrar mas de un email, solo separelos por comas</small></span>			
        </div>
    </div>
    
    <div class="form-group">
        <label for="confirmacionemail" class="col-sm-3 control-label">Confirmación correo electrónico</label>
        <div class="col-sm-6">
            <input type="text" class="required form-control" id="confirmacionemail" name="confirmacionemail">
            <br><span class="text-muted"><small>En caso de no encontrar el correo en su bandeja de entrada buscarlo en correo no deseado(spam)</small></span>
        </div>
    </div>
    
    <div class="form-group">
        <label for="rfc" class="col-sm-3 control-label">RFC</label>
        <div class="col-sm-6">
            <input type="text" class="required mayusculas form-control" id="rfc" name="rfc" maxlength="13" onKeyUp="this.value=this.value.toUpperCase();">
            <br><span class="text-muted"><small>El RFC es sin espacios ni guiones, con homoclave</small></span>
        </div>
    </div>
    
    <div class="form-group">
        <label for="calle" class="col-sm-3 control-label">Calle</label>
        <div class="col-sm-6">
            <input type="text" class="required mayusculas form-control" id="calle" name="calle" onKeyUp="this.value=this.value.toUpperCase();">
        </div>
    </div>
    
    <div class="form-group">
        <label for="numexterior" class="col-sm-3 control-label">Número exterior</label>
        <div class="col-sm-6">
            <input type="text" class="required mayusculas form-control" id="numexterior" name="numexterior" onKeyUp="this.value=this.value.toUpperCase();">
        </div>
    </div>
    
    <div class="form-group" style="display:none;">
        <label for="numinterior" class="col-sm-3 control-label">Número interior</label>
        <div class="col-sm-6">
            <input type="text" class="mayusculas form-control" id="numinterior" name="numeinterior" onKeyUp="this.value=this.value.toUpperCase();">
        </div>
    </div>
    
    <div class="form-group">
        <label for="colonia" class="col-sm-3 control-label">Colonia</label>
        <div class="col-sm-6">
            <input type="text" class="mayusculas form-control" id="colonia" name="colonia" onKeyUp="this.value=this.value.toUpperCase();">
        </div>
    </div>
    
    <div class="form-group">
        <label for="localidad" class="col-sm-3 control-label">Localidad</label>
        <div class="col-sm-6">
            <input type="text" class="mayusculas form-control" id="localidad" name="localidad" onKeyUp="this.value=this.value.toUpperCase();">
        </div>
    </div>
    
    <div class="form-group">
        <label for="municipio" class="col-sm-3 control-label">Municipio</label>
        <div class="col-sm-6">
            <input type="text" class="required mayusculas form-control" id="municipio" name="municipio" onKeyUp="this.value=this.value.toUpperCase();">
        </div>
    </div>
    
    <div class="form-group">
        <label for="estado" class="col-sm-3 control-label">Estado</label>
        <div class="col-sm-6">
            <input type="text" class="required mayusculas form-control" id="estado" name="estado" onKeyUp="this.value=this.value.toUpperCase();">
        </div>
    </div>

    <div class="form-group">
        <label for="codigopostal" class="col-sm-3 control-label">Código Postal</label>
        <div class="col-sm-6">
            <input type="text" class="required mayusculas form-control" id="codigopostal" name="codigopostal" onKeyUp="this.value=this.value.toUpperCase();">
        </div>
    </div>
    <div class="form-group">
        <label for="placa" class="col-sm-3 control-label">Placa</label>
        <div class="col-sm-6">
            <input type="text" class="required mayusculas form-control" id="placa" name="placa" placeholder="Sin espacios" onKeyUp="this.value=this.value.toUpperCase();">
        </div>
    </div>
    <div class="form-group">
        <label for="codigofactura" class="col-sm-3 control-label">Código de Facturación</label>
        <div class="col-sm-6">
            <div class="input-group">
                <input type="text" class="form-control" id="codigofactura" name="codigofactura" placeholder="Buscar por código de facturación" onKeyUp="this.value=this.value.toUpperCase();">
                <span class="input-group-btn">
                  <button class="btn btn-default" type="button" onClick="buscarcodigo()">Buscar</button>
                </span>
            </div>     
            <span class="text-muted"><small>Buscar el código de facturación para traer los datos del ticket y asi activar el boton para generar factura. Ya puedes hacer tu factura con varios tickets del mismo centro de verificaci&oacute;n. <font color="RED">Para descargar tu factura buscar la factura por el c&oacute;digo de facturaci&oacute;n del ticket.</font></small></span>   
        </div>
    </div>
    
    <span id="Resultados"><span id="divespera"></span><table border="1"  id="tresultados" style="display:none;">
    <tr><th align="center" style="padding: 10px!important">Quitar</th>
    <th align="center" style="padding: 10px!important">Centro de Verificaci&oacute;n</th>
    <th align="center" style="padding: 10px!important">Folio</th>
    <th align="center" style="padding: 10px!important">Tipo de Verificaci&oacute;n</th>
    <th align="center" style="padding: 10px!important">Importe</th></tr></table></span>
    <p class="text-center"><input type="button" id="generar_factura" class="btn btn-primary" value="Generar factura" onClick="$('#panel').show();if(validarRFC()){validar('');} else{ $('#panel').hide(); alert('RFC invalido');}" disabled></p><br /><br />
</section>
<section class="col-sm-3">
    <div class="alert alert-info text-center" role="alert"><b><span class="glyphicon glyphicon-question-sign" aria-hidden="true" style="font-size: 100px"></span><br>¿Problemas con su factura?</b></div>
    <p><b>Soporte para facturación:</b><br><span class="text-info">Soporte Puebla: pueblasoporte@gmail.com</span><br><br><span class="text-info">Soporte D.F.: verifactura@gmail.com</span><p>
    <p>Enviar en Asunto folio de ticket, placa y número y/o nombre del centro de verificación</p>
</section>

</div><!-- end container -->
<script>
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
        if(confirm(" Nombre: "+document.getElementById("nombre").value+"\n Email: "+document.getElementById("email").value+"\n Los datos son correctos?")){
            if(document.getElementById("nombre").value==""){
                $('#panel').hide();
                alert("Necesita ingresar la razon social");
            }
            else if(document.getElementById("email").value==""){
                $('#panel').hide();
                alert("Necesita ingresar el email");
            }
            else if(document.getElementById("email").value!="" && document.getElementById("confirmacionemail").value==""){
                $('#panel').hide();
                alert("Necesita ingresar la confirmacion email");
            }
            else if(document.getElementById("email").value!="" && document.getElementById("confirmacionemail").value!=document.getElementById("email").value){
                $('#panel').hide();
                alert("No son iguales los emails");
            }
            else if(document.forma.rfc.value==""){
                $('#panel').hide();
                alert("Necesita ingresar el rfc");
            }
            else if(document.forma.calle.value==""){
                $('#panel').hide();
                alert("Necesita ingresar la calle");
            }
            else if(document.forma.municipio.value==""){
                $('#panel').hide();
                alert("Necesita ingresar el municipio");
            }
            else if(document.forma.estado.value==""){
                $('#panel').hide();
                alert("Necesita ingresar el estado");
            }
            else if(document.forma.codigopostal.value==""){
                $('#panel').hide();
                alert("Necesita ingresar el código postal");
            }
            else if($(".tickets").length==0){
                $('#panel').hide();
                alert("No hay ningun ticket cargado");
            }
            else if(hayplazasdiferentes()){
                $('#panel').hide();
                alert("Los tickets deben de pertenecer al mismo centro de verificación");
            }
            else if(confirm("Esta seguro de seguir la factura se timbrara al guardarla?")){
                atcr("facturacion_web.php","",2,0);
            }
        }
        else{
            $('#panel').hide();
        }
    }

    function agregar_cuenta(){
        $("#cuentas").append('<tr><td align="center"><select name="banco[]"><option value="">Seleccione</option><?php echo $bancos ?></select></td>\<td align="center"><input type="text" class="form-control" name="cuenta[]" value=""></td></tr>');
    }
</script>
<!--section input[type="text"]{ text-transform: uppercase; }-->
<style>
    .mayusculas { text-transform: uppercase; }
    #Resultados { min-height: 50px; text-align: center; }
	.required { border-left: 2px solid red; }
</style>
<script>
    function buscarcodigo(){
        if($.trim(document.forma.codigofactura.value)==""){
            alert("Necesita ingresar el código de facturación");
        }
        else{
        	tickets_capturados = [];
        	$('.tickets').each(function(){
        		campo_ticket = $(this);
        		ticket = {};
        		ticket.plaza = campo_ticket.attr('plaza');
        		ticket.cve = campo_ticket.val();
        		tickets_capturados.push(ticket);
        	});
            $.ajax({
              url: "facturacion_web.php",
              type: "POST",
              async: false,
              dataType: "json",
              data: {
                codigofactura: document.getElementById("codigofactura").value,
                placa: document.getElementById("placa").value,
                ajax: 1,
                tickets_capturados: JSON.stringify(tickets_capturados)
              },
                beforeSend: function(){
                    console.log("Do something....");
                    $("#divespera").html('<div class="text-center"><p><i class="fa fa-spinner fa-3x fa-spin"></i></p></div>');
                },
                success: function(data) {   
                	$("#divespera").html('');
                	if(data.error == 1){
                		alert(data.mensaje);
                	}   
                	else{
	                    $("#tresultados").hide().append(data.html).fadeIn("slow");
	                    if($(".tickets").length==0){
	                    	$("#generar_factura").attr("disabled","disabled");
	                    }
	                    else{
	                    	$("#generar_factura").removeAttr("disabled");
	                    }
	                    document.getElementById("codigofactura").value = '';
	                    document.getElementById("placa").value = '';
	                }
                }
            });
        }
    }

    function quitar_ticket(campo){
    	campo.parents('tr:first').remove();
    	if($(".tickets").length==0){
    		$("#tresultados").hide();
        	$("#generar_factura").attr("disabled","disabled");
        }
        else{
        	$("#generar_factura").removeAttr("disabled");
        }
    }
    function hayplazasdiferentes(){
    	plaza = 0;
    	regresar = false;
    	$('.tickets').each(function(){
    		plaza_ticket = $(this).attr('plaza');
    		if(plaza == 0){
    			plaza = plaza_ticket
    		}
    		if(plaza != plaza_ticket){
    			regresar = true;
    		}
    	});
    	return regresar;
    }
	
	$("#placa").on("keydown", function(e){
		return e.which !== 32;
	});
	
</script>
<?php bottom(); 

if(date("H:i:s")>'23:00:00' || date('H:i:s')<'01:00:00'){
	echo '<script>$("#panel").show();
	alert("El horario de facturación es de 01:00 a 23:00 horas");
	</script>';
}
?>