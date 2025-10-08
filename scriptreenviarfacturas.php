<?php

require_once("subs/cnx_db.php");
require_once("phpmailer/class.phpmailer.php");
$res=mysql_query("SELECT * FROM plazas");
while($row=mysql_fetch_array($res)){
	$array_plaza[$row['cve']]=$row['numero'].' '.$row['nombre'];
}
$hora = date( "Y-m-d H:i:s" , strtotime ( "-5 minute" , strtotime(date('Y-m-d H:i:s')) ) );
$res = mysql_query("SELECT * FROM facturas WHERE usuario=-1 AND estatus!='C' AND uuid!='' AND reenviado=0 AND CONCAT(fecha_creacion,' ',hora)<='".$hora."'");
while($row = mysql_fetch_array($res)){
	$cvefact=$row['cve'];
	$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$row['plaza']."'");
	$rowempresa = mysql_fetch_array($resempresa);
	$res1 = mysql_query("SELECT * FROM clientes WHERE cve='".$row['cliente']."'");
	$row1 = mysql_fetch_array($res1);
	$row1['cve']=0;
	$emailenvio = $row1['email'];
	//$emailenvio = 'sonantonio@gmail.com';
	//$rowempresa['email'] = 'sonantonio@gmail.com';
	$mail = new PHPMailer();
	$mail->Host = "localhost";
	$mail->From = "verificentros@verificentros.net";
	$mail->FromName = "Vereficentro Plaza ".$array_plaza[$row['plaza']];
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
	if($row['estatus']=='C')
		$mail->AddAttachment("cfdi/comprobantes/facturac_".$row['plaza']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
	else
		$mail->AddAttachment("cfdi/comprobantes/factura_".$row['plaza']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
	$mail->AddAttachment("cfdi/comprobantes/cfdi_".$row['plaza']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
	$mail->Send();
	if($rowempresa['email']!=""){
		$mail = new PHPMailer();
		$mail->Host = "localhost";
		$mail->From = "verificentros@verificentros.net";
		$mail->FromName = "Verificentros Plaza ".$array_plaza[$row['plaza']];
		$mail->Subject = "Factura ".$cvefact;
		$mail->Body = "Factura ".$cvefact;
		$correos = explode(",",trim($rowempresa['email']));
		foreach($correos as $correo)
			$mail->AddAddress(trim($correo));
		if($row['estatus']=='C')
			$mail->AddAttachment("cfdi/comprobantes/facturac_".$row['plaza']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
		else
			$mail->AddAttachment("cfdi/comprobantes/factura_".$row['plaza']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
		$mail->AddAttachment("cfdi/comprobantes/cfdi_".$row['plaza']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
		$mail->Send();
	}
	mysql_query("UPDATE facturas SET reenviado=1 WHERE plaza='".$row['plaza']."' AND cve='$cvefact'");
}

?>