<?php
include("subs/cnx_db.php");

if(date('w') > 0){
	$res = mysql_query("SELECT cve,porcentaje_devolucion,dias_devolucion,importe_devolucion FROM plazas a 
		WHERE genera_devolucion = '1' GROUP BY cve");
	while($row=mysql_fetch_array($res)){
		if($row['porcentaje_devolucion']>0){
			$fecha = date( "w" , strtotime ( "-".$row['dias_devolucion']." day" , strtotime(date('Y-m-d')) ) );

			if($fecha==0){
				$fecha = date( "Y-m-d" , strtotime ( "-".($row['dias_devolucion']-1)." day" , strtotime(date('Y-m-d')) ) );
			}
			else{
				$fecha = date( "Y-m-d" , strtotime ( "-".$row['dias_devolucion']." day" , strtotime(date('Y-m-d')) ) );
			}
			$res1=mysql_query("SELECT SUM(monto) FROM cobro_engomado WHERE plaza = '".$row['cve']."' AND estatus!='C' AND tipo_pago = 1 AND fecha='".$fecha."'");
			$row1=mysql_fetch_array($res1);
			mysql_query("INSERT devolucion_ajuste SET plaza = '".$row['cve']."',fecha=CURDATE(),monto='".round($row1[0]*$row['porcentaje_devolucion']/100,2)."',estatus='A',fecha_importe='".$fecha."',fecha_captura=CURDATE()");
		}
		else{
			mysql_query("INSERT devolucion_ajuste SET plaza = '".$row['cve']."',fecha=CURDATE(),monto='".$row['importe_devolucion']."',estatus='A',fecha_importe=CURDATE(),fecha_captura=CURDATE()");
		}
	}
}
/*require_once("phpmailer/class.phpmailer.php");
$mail = new PHPMailer();
$mail->Host = "localhost";
$mail->From = "manuel_arias83@yahoo.com";
$mail->FromName = "Sonantonio";
$mail->Subject = "Correo Prueba";
$mail->Body = "Correo Prueba";

$mail->AddAddress(trim('a8178293903721@hjasdiou9817.net'));

$mail->Send();
*/

?>