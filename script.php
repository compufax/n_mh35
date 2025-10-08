<?php

include("subs/cnx_db.php");

/*function generar_clave($plaza){
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

$res1 = mysql_query("SELECT a.* FROM cobro_engomado a left join claves_facturacion b on a.plaza = b.plaza and a.cve=b.ticket where isnull(b.cve)");
while($row1 = mysql_fetch_array($res1)){
	if($row1['monto']>0 && $row1['tipo_pago'] != 2 && $row1['tipo_pago'] != 6){
		if($row1['tipo_pago']==6 && $row1['tipo_vale']==2){
			$res = mysql_query("SELECT a.factura FROM pagos_caja a INNER JOIN vales_pago_anticipado b ON a.plaza = b.plaza AND a.cve = b.pago WHERE a.plaza='".$row1['plaza']."' AND a.estatus!='C' AND b.cve= '".$row1['vale_pago_anticipado']."'");
			$row = mysql_fetch_array($res);
			if($row[0] == 0){
				guardaClave($row1['plaza'], $row1['cve']);
			}
		}
		else{
			guardaClave($row1['plaza'], $row1['cve']);
		}
	}
}

exit();*/


if(date('w') != 0){
	mysql_query("insert into asistencia (plaza, fecha, personal, estatus) select a.plaza, CURDATE(), a.cve, IF(IFNULL(b.motivo,0) > 0, 2, 0) from personal a left join dias_justificados b on a.cve = b.personal and b.fecha = CURDATE() where a.estatus=1");
}
else{
	mysql_query("insert into asistencia (plaza, fecha, personal, estatus, domingo) select a.plaza, CURDATE(), a.cve, IF(IFNULL(b.motivo,0) > 0, 2, 0), 1 from personal a left join dias_justificados b on a.cve = b.personal and b.fecha = CURDATE() where a.estatus=1");	
}

if(date('w') != 0){
	$fecha = date( "w" , strtotime ( "-10 day" , strtotime(date('Y-m-d')) ) );

	if($fecha==0){
		$fecha = date( "Y-m-d" , strtotime ( "-9 day" , strtotime(date('Y-m-d')) ) );
	}
	else{
		$fecha = date( "Y-m-d" , strtotime ( "-10 day" , strtotime(date('Y-m-d')) ) );
	}

	$res = mysql_query("SELECT a.cve,SUM(b.monto) FROM plazas a 
		LEFT JOIN cobro_engomado b ON a.cve = b.plaza AND b.estatus!='C' AND b.tipo_pago=1 AND b.fecha='".$fecha."' 
		WHERE a.genera_devolucion = '1' GROUP BY a.cve");
	while($row=mysql_fetch_array($res)){
		mysql_query("INSERT devolucion_ajuste SET plaza = '".$row['cve']."',fecha=CURDATE(),monto='".round($row[1]*0.15,2)."',estatus='A',fecha_importe='".$fecha."'");
	}
}

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