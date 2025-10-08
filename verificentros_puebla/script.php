<?php

include("subs/cnx_db.php");

mysql_query("insert into asistencia (plaza, fecha, personal, estatus) select plaza, CURDATE(), cve, 0 from personal where estatus=1");

$res = mysql_query("SELECT * FROM datosempresas WHERE maneja_callcenter = 1 AND call_emails!=''");

while($Plaza=mysql_fetch_array($res)){
	$html = genera_html($Plaza['plaza'], date("Y-m-d"),1);
	require_once("phpmailer/class.phpmailer.php");
	/*$mail = new PHPMailer();
	$mail->Host = "localhost";
	$mail->From = "verificentros@verificentros.net";
	$mail->FromName = "Verificentros ".$Plaza['nombre_callcenter'];
	$mail->Subject = "Citas para Verificacion del Dia ".fechaNormal($_POST['fecha']);
	//$mail­>CharSet = "UTF­8";
	//$mail­>Encoding = "quoted­printable";
	$mail->IsHTML(true);
	//$mail->Body = '<html>'.$html.'</html>';
	$mail->MsgHTML('<html>'.$html.'</html>');
	$emails = explode(",",$Plaza['call_emails']);
	foreach($emails as $email){
		if(trim($email)!="")
			$mail->AddAddress(trim($email));
	}
	$mail->Send();
	echo $html;*/
	
	$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
	$cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	// Cabeceras adicionales
	$cabeceras .= 'From: Verificentros '.$Plaza['nombre_callcenter'].' <verificentros@verificentros.net>' . "\r\n";
	
	mail($Plaza['call_emails'], "Citas para Verificacion del Dia ".date("d/m/Y"), $html, $cabeceras);
}

?>