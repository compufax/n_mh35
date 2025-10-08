// Using Awesome https://github.com/PHPMailer/PHPMailer
<?php
require 'PHPMailer-master/PHPMailerAutoload.php';

$mail = new PHPMailer;

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.mailgun.org';                     // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'postmaster@verificentrosdf.net';   // SMTP username
$mail->Password = '066b945d0b83b43f37d4fbc63a1ec288';                           // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable encryption, only 'tls' is accepted

$mail->From = 'no-reply@verificentrosdf.net';
$mail->FromName = 'Mailer';
$mail->addAddress('eduardo.heredia@gmail.com');                 // Add a recipient

$mail->WordWrap = 50;                                 // Set word wrap to 50 characters

$mail->Subject = 'Hello';
$mail->Body    = 'Testing some Mailgun awesomness';

if(!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message has been sent';
}

