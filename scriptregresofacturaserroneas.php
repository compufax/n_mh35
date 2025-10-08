<?php

include("subs/cnx_db.php");
require_once('numlet.php');
require_once("phpmailer/class.phpmailer.php");
$array_forma_pago=array("PAGO EN UNA SOLA EXHIBICION","PAGO EN PARCIALIDADES O DIFERIDO");
$array_tipo_pago=array(0=>"01 EFECTIVO",2=>"03 TRANSFERENCIA ELECTRONICA DE FONDOS",3=>"01 DEPOSITO",4=>"99 NO ESPECIFICADO",5=>"02 CHEQUE DENOMINATIVO",6=>"98 NO APLICA",7=>"04 CREDITO", 9=>"28 TARJETA DE DEBITO");//,1=>"CHEQUE"

$Facturas = mysql_query("SELECT * FROM facturas WHERE estatus='A' AND respuesta1='' AND DATEDIFF(CURDATE(), fecha) > 3");
while($Factura = mysql_fetch_array($Facturas)){
	$plaza = $Factura['plaza'];
	$tickets = implode(',',$datos['ticket']);
	$res = mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$plaza."' AND factura='".$Factura['cve']."'");
	$row=mysql_fetch_array($res);
	$cliente = mysql_query("SELECT * FROM clientes WHERE cve='".$Factura['cliente']."'");
	$datos = mysql_fetch_array($cliente);

	$forma_pago = 0;
	if($row['tipo_pago']==5) $tipo_pago=7;
	elseif($row['tipo_pago']==7) $tipo_pago=9;
	elseif($row['tipo_pago']==6) $tipo_pago=$datos['tipo_pago'];
	else $tipo_pago=0;
	$cuentapago='NO APLICA';
	if($tipo_pago == 2 || $tipo_pago == 5){
		$cuentapago = $datos['cuenta'];
	}
	$res1 = mysql_query("SELECT * FROM plazas WHERE cve='".$plaza."'");
	$row1 = mysql_fetch_array($res1);
	$numeroPlaza=$row1['numero'];
	$res1 = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$plaza."'");
	$row1 = mysql_fetch_array($res1);
	$html = '<h1>No se pudo timbrar la siguiente factura favor de revisar sus datos</h1><table width="100%"><tr><td width="30%"><img src="logos/logo1.jpg" width="300px" height="100px"></td>
		<td width="40%">'.utf8_encode($row1['nombre']).'<br>
		'.utf8_encode($row1['rfc'].' '.$row1['calle'].' '.$row1['numexterior'].' '.$row1['numinterior']).'<br>
		'.utf8_encode($row1['colonia']).',<br>
		'.utf8_encode($row1['localidad'].' '.$row1['codigopostal']).'<br>
		'.utf8_encode($row1['municipio']).' '.utf8_encode($row1['estado']).'<br>
		MEXICO</td><td width="30%">&nbsp;</td></tr></table>';
	$html .= '<br><br>';
	$html .= '<table width="100%"><tr><th width="50%">R E C E P T O R</th><td width="50%"></tr>
	<tr><td valign="top">
		<table>
		<tr><th align="left">Cliente:</th><td colspan=3>'.($datos['nombre']).'</td></tr>
		<tr><th align="left">R.F.C.:</th><td colspan=3>'.($datos['rfc']).'</td></tr>
		<tr style="display:none;"><th align="left">Domicilio:</th><td colspan=3>'.($datos['calle'].' No. '.$datos['numexterior'].' '.$datos['numinterior']).'</td></tr>
		<tr style="display:none;"><th align="left">Colonia:</th><td colspan=3>'.($datos['colonia']).'</td></tr>
		<!--<tr><th align="left">Localidad:</th><td colspan=3>'.($datos['localidad']).'</td></tr>-->
		<tr style="display:none;"><th align="left">Municipio:</th><td colspan=3>'.($datos['municipio']).'</td></tr>
		<tr style="display:none;"><th align="left">Estado:</th><td colspan=3>'.($datos['estado']).'</td></tr>
		<tr style="display:none;"><th align="left">C.P.:</th><td>'.($datos['codigopostal']).'</td><th>PAIS:</th><td>MEXICO</td></tr>
		</table></td><td valign="top">
		<table>
		<tr><th align="left">REGIMEN</th><td>'.utf8_encode($row1['regimen']).'</td></tr>
		<tr><th align="left" colspan="2">LUGAR DE EXPEDICI&Oacute;N</th></tr>
		<tr><td copslan="2">'.utf8_encode($row1['calle'].' '.$row1['numexterior'].' '.$row1['numinterior']).'<br>
		'.utf8_encode($row1['colonia']).',<br>
		'.utf8_encode($row1['localidad'].', '.$row1['codigopostal'].', '.$row1['municipio'].', '.$row1['estado']).', MEXICO</td></tr>
		</table></td></tr></table>';
	$html .= '<br><br>';
	$html .= '<table width="100%" border="1">
	<tr><th>Cantidad</th><th>Unidad</th><th>Concepto/Descripci&oacute;n</th><th>Valor Unit</th><th>Importe</th></tr>';

	$res=mysql_query("SELECT * FROM facturasmov WHERE plaza='".$plaza."' AND cvefact = '".$Factura['cve']."'");
	while($row=mysql_fetch_array($res)){
	
	
		$html .= '<tr><td align="right">'.$row['cantidad'].'</td>
		<td align="center">'.$row['unidad'].'</td>
		<td align="left">'.$row['concepto'].'</td>
		<td align="right">'.number_format($row['precio'],2).'</td>
		<td align="right">'.number_format($row['importe'],2).'</td>
		</tr>';
	}
	$html .= '<tr><th colspan="3">IMPORTE CON LETRA</th><td colspan="2" rowspan="2" align="center">
	<table width="80%" border="1">
	<tr><td>SUBTOTAL:</td><td align="right">'.number_format($Factura['subtotal'],2).'</td></tr>
	<tr><td>I.V.A. 16%:</td><td align="right">'.number_format($Factura['iva'],2).'</td></tr>';
	
	$html .= '
	<tr><td>TOTAL:</td><td align="right">'.number_format($Factura['total'],2).'</td></tr>
	</table></td></tr>';
	
	$html .= '<tr><td colspan="3" align="left">'.utf8_encode(numlet($Factura['total'])).'<br><br>
	M&eacute;todo pago: '.$array_forma_pago[$forma_pago].'
	Forma de pago: '.$array_tipo_pago[$tipo_pago].'<br>
	Condiciones: CONTADO<br>
	No. Cta pago: '.$cuentapago.'<br></td></tr></table>';

	if($datos['email']!=""){
		$mail = new PHPMailer;		
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.gmail.com';                     // Specify main and backup SMTP servers
		$mail->Port = 587;    
		$mail->SMTPSecure = 'tls';     
		$mail->SMTPAuth = true;                               // Enable encryption, only 'tls' is accepted
		$mail->Username = 'gverificentros@gmail.com';   // SMTP username
		$mail->Password = 'bAllenA6##6';    
		$mail->From = "gverificentros@gmail.com";
		$mail->FromName = "Verificentros";
		$mail->Subject = "Error Timbrado Factura ";
		$mail->isHTML(true);
		$mail->Body = $html;
		$correos = explode(",",trim($datos['email']));
		foreach($correos as $correo)
			$mail->AddAddress(trim($correo));
		/*if($rowempresa['email']!=""){
			$correos = explode(",",trim($rowempresa['email']));
			foreach($correos as $correo)
				$mail->AddCC(trim($correo));
		}*/
		$mail->Send();
	}	
}