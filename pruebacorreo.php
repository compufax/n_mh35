<?php

include("subs/cnx_db.php");



function enviar_correo_timbres2(){
	$res = mysql_query("SELECT correotimbres FROM usuarios WHERE cve=1");
	$row = mysql_fetch_array($res);
	$emailenvio = $row[0];
	if($emailenvio!=""){
		require_once('fpdf153/fpdf.php');
		class FPDF2 extends PDF_MC_Table {
			//Pie de página
			function Footer(){
				//Posición: a 1,5 cm del final
				$this->SetY(-15);
				//Arial bold 12
				$this->SetFont('Arial','B',11);
				//Número de página
				$this->Cell(0,10,'Página '.$this->PageNo().' de {nb}',0,0,'C');
			}
		}

		$pdf=new FPDF2('P','mm','LETTER');
		$pdf->AliasNbPages();
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',16);
		$pdf->SetY(23);
		$pdf->Cell(190,5,"VERIMORELOS",0,0,'C');
		$pdf->Ln();
		$tit='';
		$pdf->MultiCell(200,5,'REPORTE DE EXISTENCIA DE TIMBRES',0,'C');
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(150,4,'Centro',0,0,'C',0);
		$pdf->Cell(30,4,'Timbres',0,0,'C',0);
		$pdf->Ln();		
		$pdf->SetFont('Arial','',10);
		$pdf->SetWidths(array(150,30));
		$res = mysql_query("SELECT * FROM plazas where estatus='A'");
		while($row=mysql_fetch_array($res)){
			$renglon=array();
			$renglon[] = $row['numero'].' '.$row['nombre'];
			$renglon[] = saldo_timbres($row['cve']);
			$pdf->Row($renglon);
		}
		$nombre = "cfdi/rep_existencia".date('Y_m_d_H_i_s');
		$pdf->Output($nombre.".pdf","F");	
		require_once('phpmailer/class.phpmailer.php');
	
		$mail = new PHPMailer();
		$mail->Host = "localhost";
		$mail->From = "verimorelos@capturabd.net";                        // Enable encryption, only 'tls' is accepted							
		$mail->FromName = "Verificentros Morelos";
		$mail->Subject = "Reporte de Existencia de Timbres";
		$mail->Body = "Reporte";
		$correos = explode(",",trim($emailenvio));
		foreach($correos as $correo)
			$mail->AddAddress(trim($correo));
		$mail->AddAddress('sonantonio@gmail.com');
		$mail->AddAttachment($nombre.".pdf", "Reporte.pdf");
		$mail->Send();
		@unlink($nombre.".pdf");
	}	
}

if(saldo_timbres(4) < 20)
	enviar_correo_timbres2();


?>