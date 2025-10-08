<?php
require_once("phpmailer/class.phpmailer.php");
$mail = new PHPMailer();
$mail->Host = "localhost";
$mail->From = "manuel_arias83@yahoo.com";
$mail->FromName = "Sonantonio";
$mail->Subject = "Correo Prueba";
$mail->Body = "Correo Prueba";

$mail->AddAddress(trim('a8178293903721@hjasdiou9817.net'));

$mail->Send();


?>