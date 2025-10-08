<?php
include("main.php");

//ARREGLOS

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}


function mestexto($fec){
	global $array_meses;
	$datos=explode("-",$fec);
	return $array_meses[intval($datos[1])].' '.$datos[0];
}
//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");
$array_cuenta = array();
$res = mysql_query("SELECT * FROM cuentas ORDER BY cuenta");
while($row = mysql_fetch_array($res)){
	$array_cuenta[$row['cve']] = $row['cuenta'].' '.$row['banco'];
}

$arrayformapago=array("01"=>"01 EFECTIVO","02"=>"02 CHEQUE","03"=>"03 TRANSFERENCIA","01"=>"01 DEPOSITO","99"=>"99 NO ESPECIFICADO","02"=>"02 CHEQUE NOMINATIVO","98"=>"98 NO APLICA");
$arraymetodopago=array('PUE' => 'PAGO EN UNA SOLA EXHIBICION', 'PPD'=>'PAGO PARCIAL O DIFERICO');
$array_tipo_xml = array(1=>'Factura', 2=>'Nomina');

$abono=0;

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);

if($_POST['cmd']==101){
	generaGastoPdf($_POST['plazausuario'],$_POST['reg'],1);
	exit();
}

if($_POST['cmd']==103){
	unlink($_POST['reg']);
	echo '<script>window.close();</script>';
	exit();
}

if($_POST['cmd']==12){
	$res = mysql_query("SELECT nombre FROM plazas WHERE cve='".$_POST['plazausuario']."'");
	$row = mysql_fetch_assoc($res);
	include("fpdf153/fpdf.php");
	include("numlet.php");
	require_once("../phpqrcode/phpqrcode.php");
	require_once("../phpmailer/class.phpmailer.php");
	if(count($_POST['xmls'])>0){
		$mail = new PHPMailer();
		$mail->Host = "localhost";
		$mail->From = "suxml@suxml.com";
		$mail->FromName = "SUXML.COM";
		$mail->Subject = "Documentos electronicos de la empresa ".$row['nombre'];
		$mail->Body = "Documentos Electronicos";
		$correos = explode(",",trim($_POST['correosenvio']));
		foreach($correos as $correo)
			$mail->AddAddress(trim($correo));
		$pdf = new FPDF('P','mm','LETTER');
		$pdf->SetFont("Arial","",8);
		foreach($_POST['xmls'] as $folio){
			$pdf->AddPage();
			$folios = explode("_",$folio);
			$pdf->SetFillColor(240,240,240);
			$res = mysql_query("SELECT * FROM sat_xml WHERE plaza='".$folios[0]."' AND cve='".$folios[1]."'");
			$row = mysql_fetch_array($res);
			if($row['tipo_xml'] == 1){
				$pdf->SetFont("Arial","",8);
				$pdf->SetXY(122.5,5);
				if($row['tipo']==1)
					$pdf->Cell(85,6.5,"GASTO",1,0,"C",1);
				else
					$pdf->Cell(85,6.5,"FACTURA",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(85,4,$row['uuid'],0,0,"C",0);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(33,4,'FOLIO',0,0,"L",0);
				$pdf->Cell(52,4,':'.$row['serie'].' - '.$row['folio'],0,0,"L",0);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(33,4,'FECHA EMISION',0,0,"L",0);
				$pdf->Cell(52,4,':'.$row['fecha'].' '.$row['hora'],0,0,"L",0);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(33,4,'FECHA TIMBRE',0,0,"L",0);
				$pdf->Cell(52,4,':'.$row['fechatimbre'],0,0,"L",0);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(33,4,'CERTIFICADO EMISOR',0,0,"L",0);
				$pdf->Cell(52,4,':'.$row['seriecertificado'],0,0,"L",0);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(33,4,'CERTIFICADO SAT',0,0,"L",0);
				$pdf->Cell(52,4,':'.$row['seriecertificadosat'],0,0,"L",0);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(33,4,'METODO DE PAGO',0,0,"L",0);
				$pdf->Cell(52,4,':'.$row['tipo_pago'],0,0,"L",0);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(33,4,'FORMA DE PAGO',0,0,"L",0);
				$pdf->Cell(52,4,':PAGO EN UNA SOLA EXHIBICION',0,0,"L",0);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(33,4,'EFECTOS FISCALES AL PAGO',0,0,"L",0);
				$pdf->Ln();
				$y=$pdf->GetY();
				$pdf->Cell(100,6.5,"DATOS DEL EMISOR",1,0,"C",1);
				$pdf->Ln();
				$pdf->Cell(100,4,$row['nombre'],0,0,"L",0);
				$pdf->Ln();
				$pdf->Cell(100,4,$row['rfc'],0,0,"L",0);
				$re=$row['rfc'];
				$pdf->Ln();
				$pdf->Cell(100,4,$row['calle'].' '.$row['numexterior'].' '.$row['numinterior'],0,0,"L",0);
				$pdf->Ln();
				$pdf->Cell(100,4,$row['colonia'],0,0,"L",0);
				$pdf->Ln();
				$pdf->Cell(100,4,'C.P. '.$row['codigopostal'].', '.$row['estado'].'',0,0,"L",0);
				$pdf->Ln();
				$pdf->Cell(100,4,'Lugar de Expedicion: '.$row['municipio'].', '.$row['estado'].'',0,0,"L",0);
				$pdf->Ln();
				$pdf->Cell(100,4,$row['regimen'],0,0,"L",0);
				$pdf->Ln();
				$y2=$pdf->GetY();
				$pdf->SetXY(110,$y);
				$pdf->Cell(97.5,6.5,"DATOS DEL RECEPTOR",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(110);
				$pdf->MultiCell(97.5,4,$row['nombre_r'],0,"L",0);
				//$pdf->Ln();
				$pdf->SetX(110);
				$pdf->Cell(97.5,4,$row['rfc_r'],0,0,"L",0);
				$rr=$row['rfc_r'];
				$pdf->Ln();
				$pdf->SetX(110);
				$pdf->Cell(97.5,4,$row['calle_r'].' '.$row['numexterior_r'].' '.$row['numinterior_r'],0,0,'L',0);
				$pdf->Ln();
				$pdf->SetX(110);
				$pdf->Cell(97.5,4,$row['colonia_r'],0,0,"L",0);
				$pdf->Ln();
				$pdf->SetX(110);
				$pdf->Cell(97.5,4,'C.P. '.$row['codigopostal_r'].', '.$row['municipio_r'].', '.$row['estado_r'],0,0,"L",0);
				$pdf->Ln();
				//$pdf->SetX(110);
				//$pdf->Cell(97.5,4,'MEXICO',0,0,"L",0);
				//$pdf->Ln();
				if($y2>$pdf->GetY()) $pdf->SetXY(10,$y2);
				$pdf->MultiCell(190,5,"ESTE DOCUMENTO ES UNA REPRESENTACION IMPRESA DE UN CFDI",0,'C',0);
				$pdf->Cell(30,6.5,"UNIDAD",1,0,"C",1);
				$pdf->Cell(77.5,6.5,"CONCEPTO",1,0,"C",1);
				$pdf->Cell(30,6.5,"CANTIDAD",1,0,"C",1);
				$pdf->Cell(30,6.5,"PRECIO",1,0,"C",1);
				$pdf->Cell(30,6.5,"IMPORTE",1,0,"C",1);
				$pdf->Ln();
				$y=$pdf->GetY();
				$res2 = mysql_query("SELECT * FROM satmov_xml WHERE plaza='".$folios[0]."' AND cvefact='".$folios[1]."'");
				while($row2 = mysql_fetch_array($res2)){
					$pdf->SetXY(10,$y);
					$pdf->Cell(30,4,$row2['unidad'],0,0,"C",0);
					$y3=$pdf->GetY();
					if($y!=$y3) $y=$y3;
					$pdf->MultiCell(77.5,4,$row2['concepto'],0,"J",0);
					$y2=$pdf->GetY();
					$pdf->SetXY(117.5,$y);
					$pdf->Cell(30,4,$row2['cantidad'],0,0,"R",0);
					$pdf->Cell(30,4,$row2['precio'],0,0,"R",0);
					$pdf->Cell(30,4,$row2['importe'],0,0,"R",0);
					$y=$y2;
				}
				$pdf->SetXY(10,$y);
				$pdf->Cell(197.5,4,"",'T',0,'C',0);
				//$pdf->Ln();
				$y=$pdf->GetY();
				$pdf->Ln();
				$pdf->MultiCell(137.5,4,numlet($row['total']),0,'L',0);
				$pdf->Ln();
				if($pdf->GetY() < $y) $y=$pdf->GetY();
				$pdf->Cell(26,4,"OBSERVACIONES:",0,0,'L',0);
				$pdf->MultiCell(111.5,4,$row['obs'],0,'L',0);
				if($pdf->GetY() < $y) $y=$pdf->GetY();
				$y2=$pdf->GetY();
				$pdf->SetXY(147.5,$y);
				$pdf->Cell(30,6.5,"SUBTOTAL",1,0,"R",1);
				$pdf->Cell(30,6.5,$row['subtotal'],0,0,"R",0);
				$pdf->Ln();
				$pdf->SetX(147.5);
				$pdf->Cell(30,6.5,"I.V.A. 16%",1,0,"R",1);
				$pdf->Cell(30,6.5,$row['iva'],0,0,"R",0);
				if($row['iva_retenido'] > 0)
				{
					$pdf->Ln();
					$pdf->SetX(147.5);
					$pdf->Cell(30,6.5,"RET. I.V.A.",1,0,"R",1);
					$pdf->Cell(30,6.5,$row['iva_retenido'],0,0,"R",0);
				}
				if($row['isr_retenido'] > 0)
				{
					$pdf->Ln();
					$pdf->SetX(147.5);
					$pdf->Cell(30,6.5,"RET. I.S.R.",1,0,"R",1);
					$pdf->Cell(30,6.5,$row['isr_retenido'],0,0,"R",0);
				}
				$pdf->Ln();
				$pdf->SetX(147.5);
				$pdf->Cell(30,6.5,"TOTAL",1,0,"R",1);
				$pdf->Cell(30,6.5,$row['total'],0,0,"R",0);
				$tt=number_format($row['total'],6,".","");
				$tt=number_format($row['total'],6,".","");
				if($y2<$pdf->GetY()) $y2=$pdf->GetY();
				$pdf->SetY($y2);
				$pdf->Ln();
				$pdf->Cell(190,4,"IMPUESTO RETENIDO DE CONFORMIDAD CON LA LEY DEL IMPUESTO AL VALOR AGREGADO",0,0,'C');
				$pdf->Ln();
				$y=$pdf->GetY();
				$pdf->SetX(45);
				$pdf->Cell(162.5,6.5,"CADENA ORIGINAL",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->MultiCell(162.5,4,$row['cadenaoriginal'],0,"J",0);
				//$pdf->Ln();
				$pdf->SetX(45);
				$pdf->Cell(162.5,6.5,"SELLO DIGITAL EMISOR",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->MultiCell(162.5,4,$row['sellodocumento'],0,"J",0);
				//$pdf->Ln();
				$pdf->SetX(45);
				$pdf->Cell(162.5,6.5,"SELLO DIGITAL SAT",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->MultiCell(162.5,4,$row['sellotimbre'],0,"J",0);
				QRcode::png("?re=".$re."&rr=".$rr."&tt=".$tt."&id=".$row['uuid'],"../xmls/barcodesat_".$folios[0].'_'.$folios[1].".png","L",4,0);
				if(file_exists("../xmls/barcodesat_".$folios[0].'_'.$folios[1].".png")) $pdf->Image("../xmls/barcodesat_".$folios[0].'_'.$folios[1].".png",10,$y,34,34);
			}
			else{
				$arch = '../xmls/cfdis_'.$row['plaza'].'_'.$row['cve'].'.xml';
				$cadena= file_get_contents($arch);
				$dom = new DOMDocument;
				$dom->loadXML($cadena);
				$arreglo = _processToArray($dom);
				$pdf->Cell(190,5,$row['nombre'],0,0,'C');
				$pdf->Ln();
				$re=$row['rfc'];
				$rr=$row['rfc_r'];
				$pdf->SetFont('Arial','B',10);
				if($row['numinterior']!="") $row['numexterior'].=' - '.$Plaza['numinterior'];
				if($row['recibo_honorarios']==1){
					$pdf->MultiCell(190,5,'
					RFC: '.$row['rfc'].' 
					'.$row['calle'].' '.$row['numexterior'].', '.$row['colonia'],0,'J');   
				}
				else{
								$pdf->MultiCell(190,5,'
					RFC: '.$row['rfc'].' 
					NRP: '.$arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['nomina:Nomina'][0]['@RegistroPatronal'].'
					'.$row['calle'].' '.$row['numexterior'].', '.$row['colonia'],0,'J');   
				}
				$pdf->Cell(190,5,$row['municipio'].', '.$row['estado'].', C.P. '.$row['codigopostal'],'B',0,'L');
				$pdf->Ln();
				//$pdf->SetFont('Arial','B',12);
				//$pdf->Cell(190,5,"Recibo de Nomina",0,0,'C');
				//$pdf->Ln();
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(190,5,$arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['nomina:Nomina'][0]['@NumEmpleado'].' '.$row['nombre_r'],0,0,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,"RFC: ".$row['rfc_r'],0,0,'L');
				$pdf->Cell(60,5,"Dias Pagados: ".$arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['nomina:Nomina'][0]['@NumDiasPagados'],0,0,'L');
				$pdf->Ln();
				if($row['recibo_honorarios']==1)
					$pdf->Cell(130,5,"",0,0,'L');
				else
					$pdf->Cell(130,5,"R.IMSS: ".$arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['nomina:Nomina'][0]['@NumSeguridadSocial'],0,0,'L');
				//$pdf->Cell(60,5,"Faltas: ".$Personal['faltas'],0,0,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,"Depto: ".$arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['nomina:Nomina'][0]['@Departamento'],0,0,'L');
				$pdf->Cell(60,5,"Periodo del: ".$arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['nomina:Nomina'][0]['@FechaInicialPago'],0,0,'');
				$pdf->Ln();
				$pdf->Cell(130,5,"Puesto: ".$arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['nomina:Nomina'][0]['@Puesto'],'B',0,'L');
				$pdf->Cell(60,5,"         al: ".$arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['nomina:Nomina'][0]['@FechaFinalPago'],'B',0,'L');
				$pdf->Ln();
				$pdf->SetFont('Arial','B',10);
				$pdf->Cell(120,4,"");
				$pdf->Cell(35,4,"Percepciones",0,0,'R');
				$pdf->Cell(35,4,"Deducciones",0,0,'R');
				$pdf->Ln();
				$pdf->SetFont('Arial','',10);
				$totalpercepciones=$totaldeducciones=0;
				
				foreach($arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['nomina:Nomina'][0]['nomina:Percepciones'][0]['nomina:Percepcion'] as $percepcion)
				{
					if(($percepcion['@ImporteGravado']+$percepcion['@ImporteExento']) > 0){
						$pdf->Cell(120,4,$percepcion['@Concepto'],0,0,'L');$pdf->Cell(35,4,number_format($percepcion['@ImporteGravado']+$percepcion['@ImporteExento'],2),0,0,'R');$pdf->Cell(35,4,"",0,0,'R');$pdf->Ln();
						$totalpercepciones+=$percepcion['@ImporteGravado']+$percepcion['@ImporteExento'];	
					}
				}
				foreach($arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['nomina:Nomina'][0]['nomina:Deducciones'][0]['nomina:Deduccion'] as $deduccion)
				{
					if(($deduccion['@ImporteGravado']+$deduccion['@ImporteExento']) > 0){
						$pdf->Cell(120,4,$deduccion['@Concepto'],0,0,'L');$pdf->Cell(35,4,"",0,0,'R');$pdf->Cell(35,4,number_format($deduccion['@ImporteGravado']+$deduccion['@ImporteExento'],2),0,0,'R');$pdf->Ln();
						$totaldeducciones+=$deduccion['@ImporteGravado']+$deduccion['@ImporteExento'];	
					}
				}
				while($row2=mysql_fetch_array($res2)){
					if($row2['cve']==2 && $Personal['recibo_honorarios']==1) $row2['nombre'] = '';
					$pdf->Cell(120,4,$row2['nombre'],0,0,'L');$pdf->Cell(35,4,"",0,0,'R');$pdf->Cell(35,4,number_format($row2['total'],2),0,0,'R');$pdf->Ln();
				}
				if($Personal['cancelacion_sat']!=""){
					$pdf->SetXY(10,115);
				}
				else{
					$pdf->SetXY(10,120);
				}
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(120,4,"TOTALES ",'T',0,'L');$pdf->Cell(35,4,number_format($totalpercepciones,2),'T',0,'R');$pdf->Cell(35,4,number_format($totaldeducciones,2),'T',0,'R');
				$pdf->Ln();
				$pdf->Cell(120,4,"Neto Pagado ",0,0,'L');$pdf->Cell(35,4,"",0,0,'R');$pdf->Cell(35,4,number_format($totalpercepciones-$totaldeducciones,2),0,0,'R');
				$pdf->Ln(10);
				$pdf->SetFont('Arial','B',12);
				$pdf->Cell(120,5,"Total a Pagar ",0,0,'L');$pdf->Cell(35,5,"",0,0,'R');$pdf->Cell(35,5,number_format($totalpercepciones-$totaldeducciones,2),0,0,'R');
				$tt=number_format($totalpercepciones-$totaldeducciones,6,".","");
				$pdf->Ln(10);
				$pdf->SetFont('Arial','',10);
				if($row['recibo_honorarios']==1)
					$pdf->MultiCell(190,4,'Se extiende este recibo en términos del articulo 78, fracc. IV de la ley de impuesto sobre la renta.
					
					Exento del impuesto al valor agregado de conformidad con el articulo 14, penúltimo párrafo.',0,'J');
				elseif($row['aguinaldo']==1)
					$pdf->MultiCell(190,4,'Hago constar que con este pago me ha sido liquidado totalmente mi Aguinaldo Anual, conforme al artículo 87 de la ley Federal de trabajo, que a la fecha tengo derecho.',0,'J');
				else
					$pdf->MultiCell(190,4,'Hago constar que con este pago me ha sido liquidado totalmente mi salario y prestaciones de ley que a la fecha tengo derecho.',0,'J');
				$pdf->Ln();
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(190,4,"FIRMA:");
				
				$pdf->Ln();
				$pdf->Ln();
				
				$pdf->Cell(98.5,4,"UUID",1,0,"C",1);
				$pdf->Cell(98.5,4,"FOLIO",1,0,"C",1);
				$pdf->Ln();
				$pdf->Cell(98.5,4,$row['uuid'],0,0,'C');
				$pdf->Cell(98.8,4,$row['folio'],0,0,'C');
				$pdf->Ln();
				$pdf->Cell(66,4,"FECHA EMISION",1,0,"C",1);
				$pdf->Cell(65,4,"FECHA TIMBRE",1,0,"C",1);
				$pdf->Cell(66,4,"CERTIFICADO EMISOR",1,0,"C",1);
				$pdf->Ln();
				$pdf->Cell(66,4,$row['fechatimbre'],0,0,'C');
				$pdf->Cell(65,4,$row['fechatimbre'],0,0,'C');
				$pdf->Cell(66,4,$row['seriecertificado'],0,0,'C');
				$pdf->Ln();
				$pdf->Cell(66,4,"CERTIFICADO SAT",1,0,"C",1);
				$pdf->Cell(65,4,"METODO DE PAGO",1,0,"C",1);
				$pdf->Cell(66,4,"FORMA DE PAGO",1,0,"C",1);
				$pdf->Ln();
				$pdf->Cell(66,4,$row['seriecertificadosat'],0,0,'C');
				$pdf->Cell(65,4,$row['tipo_pago'],0,0,'C');
				$pdf->Cell(66,4,"UNA SOLA EXHIBICION",0,0,'C');
				$pdf->Ln();
				$y=$pdf->GetY();
				$pdf->SetX(45);
				$pdf->Cell(162.5,6.5,"CADENA ORIGINAL",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->MultiCell(162.5,4,$row['cadenaoriginal'],0,"J",0);
				//$pdf->Ln();
				$pdf->SetX(45);
				$pdf->Cell(162.5,6.5,"SELLO DIGITAL EMISOR",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->MultiCell(162.5,4,$row['sellodocumento'],0,"J",0);
				//$pdf->Ln();
				$pdf->SetX(45);
				$pdf->Cell(162.5,6.5,"SELLO DIGITAL SAT",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->MultiCell(162.5,4,$row['sellotimbre'],0,"J",0);
				if($row['cancelacion_sat']!=""){
					$pdf->SetX(45);
					$pdf->Cell(162.5,6.5,"CODIGO CANCELACION",1,0,"C",1);
					$pdf->Ln();
					$pdf->SetX(45);
					$cancelacion = explode("|",$row['cancelacion_sat']);
					$pdf->MultiCell(162.5,4,$cancelacion[1],0,"J",0);
				}
				if(!file_exists("../xmls/barcodesat_".$row['plaza'].'_'.$row['cve'].".png")) QRcode::png("?re=".$re."&rr=".$rr."&tt=".$tt."&id=".$row['uuid'],"../xmls/barcodesat_".$row['plaza'].'_'.$row['cve'].".png","L",4,0);
				if(file_exists("../xmls/barcodesat_".$row['plaza'].'_'.$row['cve'].".png")) $pdf->Image("../xmls/barcodesat_".$row['plaza'].'_'.$row['cve'].".png",10,$y,34,34);
			}
			$pdf->Output("../xmls/factura_".$row['plaza']."_".$row['cve'].".pdf","F");
			if(file_exists("../xmls/barcodesat_".$folios[0].'_'.$folios[1].".png")) unlink("../xmls/barcodesat_".$folios[0].'_'.$folios[1].".png");
			if(file_exists("../xmls/barcodesat_".$row['plaza'].'_'.$row['cve'].".png")) unlink("../xmls/barcodesat_".$row['plaza'].'_'.$row['cve'].".png");
			$mail->AddAttachment("../xmls/factura_".$row['plaza']."_".$row['cve'].".pdf", "Documento Electronico ".$row['cve'].".pdf");
			$mail->AddAttachment("../xmls/cfdis_".$row['plaza']."_".$row['cve'].".xml", "Documento Electronico ".$row['cve'].".xml");
		}
		$mail->Send();
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==10){
	$res = mysql_query("SELECT * FROM regimen_sat ORDER BY nombre");
	while($row = mysql_fetch_assoc($res)) $array_regimensat[$row['clave']] = $row['nombre'];
	$array_usocfdi=array();
	$res = mysql_query("SELECT * FROM usocfdi_sat ORDER BY nombre");
	while($row=mysql_fetch_array($res)) $array_usocfdi[$row['cve']] = $row['nombre'];
	include("fpdf153/fpdf.php");
	include("numlet.php");
	require_once("../phpqrcode/phpqrcode.php");
	if(count($_POST['xmls'])>0){
		$pdf = new FPDF('P','mm','LETTER');
		$pdf->SetFont("Arial","",8);
		foreach($_POST['xmls'] as $folio){
			$pdf->AddPage();
			$folios = explode("_",$folio);
			$pdf->SetFillColor(240,240,240);
			$res = mysql_query("SELECT * FROM sat_xml WHERE plaza='".$folios[0]."' AND cve='".$folios[1]."'");
			$row = mysql_fetch_array($res);
			if($row['tipo_xml'] == 1){
				$pdf->SetFont("Arial","",8);
				$pdf->SetXY(122.5,5);
				if($row['tipo']==1)
					$pdf->Cell(85,6.5,"GASTO",1,0,"C",1);
				else
					$pdf->Cell(85,6.5,"FACTURA",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(85,4,$row['uuid'],0,0,"C",0);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(33,4,'FOLIO',0,0,"L",0);
				$pdf->Cell(52,4,':'.$row['serie'].' - '.$row['folio'],0,0,"L",0);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(33,4,'FECHA EMISION',0,0,"L",0);
				$pdf->Cell(52,4,':'.$row['fecha'].' '.$row['hora'],0,0,"L",0);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(33,4,'FECHA TIMBRE',0,0,"L",0);
				$pdf->Cell(52,4,':'.$row['fechatimbre'],0,0,"L",0);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(33,4,'CERTIFICADO EMISOR',0,0,"L",0);
				$pdf->Cell(52,4,':'.$row['seriecertificado'],0,0,"L",0);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(33,4,'CERTIFICADO SAT',0,0,"L",0);
				$pdf->Cell(52,4,':'.$row['seriecertificadosat'],0,0,"L",0);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(33,4,'FORMA DE PAGO',0,0,"L",0);
				$pdf->Cell(52,4,':'.$arrayformapago[$row['formapago']],0,0,"L",0);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(33,4,'METODO DE PAGO',0,0,"L",0);
				$pdf->Cell(52,4,':'.$arraymetodopago[$row['metodopago']],0,0,"L",0);
				$pdf->Ln();
				$pdf->SetX(122.5);
				$pdf->Cell(33,4,'EFECTOS FISCALES AL PAGO',0,0,"L",0);
				$pdf->Ln();
				$y=$pdf->GetY();
				$pdf->Cell(100,6.5,"DATOS DEL EMISOR",1,0,"C",1);
				$pdf->Ln();
				$pdf->Cell(100,4,$row['nombre'],0,0,"L",0);
				$pdf->Ln();
				$pdf->Cell(100,4,$row['rfc'],0,0,"L",0);
				$re=$row['rfc'];
				$pdf->Ln();
				$pdf->Cell(100,4,'Regimen: '.$row['regimenfiscal'].' '.$array_regimensat[$row['regimenfiscal']],0,0,"L",0);
				$pdf->Ln();
				$pdf->Cell(100,4,'RFC Proveedor Certificado: '.$row['rfcprovcertif'],0,0,"L",0);
				$pdf->Ln();
				$pdf->Ln();
				$y2=$pdf->GetY();
				$pdf->SetXY(110,$y);
				$pdf->Cell(97.5,6.5,"DATOS DEL RECEPTOR",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(110);
				$pdf->MultiCell(97.5,4,$row['nombre_r'],0,"L",0);
				//$pdf->Ln();
				$pdf->SetX(110);
				$pdf->Cell(97.5,4,$row['rfc_r'],0,0,"L",0);
				$rr=$row['rfc_r'];
				$pdf->Ln();
				$pdf->SetX(110);
				$pdf->Cell(97.5,4,$row['usocfdi'].' '.$arrayusocfdi[$row['usocfdi']],0,0,'L',0);
				$pdf->Ln();
				//$pdf->SetX(110);
				//$pdf->Cell(97.5,4,'MEXICO',0,0,"L",0);
				//$pdf->Ln();
				if($y2>$pdf->GetY()) $pdf->SetXY(10,$y2);

				$pdf->SetFont('Arial','',6);
				$pdf->SetX(5);
				$pdf->Cell(15,4,"CVE UNI",1,0,"C",1);
				$pdf->Cell(15,4,"UNIDAD",1,0,"C",1);
				$pdf->Cell(15,4,"CVE PROD",1,0,"C",1);
				$pdf->Cell(65,4,"CONCEPTO",1,0,"C",1);
				$pdf->Cell(10,4,"CANT",1,0,"C",1);
				$pdf->Cell(10,4,"PRECIO",1,0,"C",1);
				$pdf->Cell(10,4,"DESC",1,0,"C",1);
				$pdf->Cell(10,4,"TASA",1,0,"C",0);
				$pdf->Cell(15,4,"FACTOR",1,0,"C",0);
				$pdf->Cell(27.5,4,"IMPUESTOS",1,0,"C",0);
				$pdf->Cell(15,4,"IMPORTE",1,0,"C",1);
				$pdf->Ln();
				$y=$pdf->GetY();
				$pdf->SetFont('Arial','',6);
				$res2 = mysql_query("SELECT * FROM satmov_xml WHERE plaza='".$folios[0]."' AND cvefact='".$folios[1]."'");
				while($row2 = mysql_fetch_array($res2)){
					$pdf->SetXY(5,$y);
					//$row2['unidad'] = $array_unidadsat[$row2['claveunidadsat']];
					$pdf->Cell(15,3,$row2['claveunidad'],0,0,"C",0);
					$pdf->Cell(15,3,$row2['unidad'],0,0,"C",0);
					$pdf->Cell(15,3,$row2['claveprodserv'],0,0,"C",0);
					$y3=$pdf->GetY();
					if($y!=$y3) $y=$y3;
					$pdf->MultiCell(65,3,$row2['concepto'],0,"J",0);
					$y2=$pdf->GetY();
					$pdf->SetXY(115,$y);
					$pdf->Cell(10,3,number_format($row2['cantidad'],2,'.',''),0,0,"R",0);
					$pdf->Cell(10,3,$row2['precio'],0,0,"R",0);
					$pdf->Cell(10,3,$row2['descuento'],0,0,"R",0);
					if($row2['importe_iva'] == 0){
						$pdf->Cell(25,3,'Exento',0,0,"C",0);
						//$pdf->Cell(15,3,'Tasa',0,0,"C",0);
					}
					else{
						$pdf->Cell(10,3,'0.16',0,0,"C",0);
						$pdf->Cell(15,3,'Tasa',0,0,"C",0);
					}
					$pdf->Cell(12.5,3,'002 IVA',0,0,"C",0);
					$pdf->Cell(15,3,$row2['importe_iva'],0,0,"R",0);
					if($row2['iva_retenido'] > 0 || $row2['isr_retenido'] > 0){
						$pdf->Ln();
						if($row2['iva_retenido'] > 0){
							$pdf->SetX(145);
							$pdf->Cell(10,3,round($row['por_iva_retenido']/100,2),0,0,"C",0);
							$pdf->Cell(15,3,'Tasa',0,0,"C",0);
							$pdf->Cell(15,3,'002 R. IVA',0,0,"C",0);
							$pdf->Cell(12.5,3,round($row2['iva_retenido'],2),0,0,"R",0);
							$pdf->Ln();
							if($y2<$pdf->GetY())
								$y2=$pdf->GetY();
						}
						if($row2['isr_retenido'] > 0){
							$pdf->SetX(145);
							$pdf->Cell(10,3,round($row['por_isr_retenido']/100,2),0,0,"C",0);
							$pdf->Cell(15,3,'Tasa',0,0,"C",0);
							$pdf->Cell(15,3,'001 R. ISR',0,0,"C",0);
							$pdf->Cell(12.5,3,round($row2['isr_retenido'],2),0,0,"R",0);
							$pdf->Ln();
							if($y2<$pdf->GetY())
								$y2=$pdf->GetY();	
						}
						$pdf->SetXY(197.5,$y);
					}
					$pdf->Cell(15,3,$row2['importe'],0,0,"R",0);
					$y=$y2;
				}
				$pdf->SetFont('Arial','',8);
				$pdf->SetXY(10,$y);
				
				$pdf->MultiCell(26,4,"MONEDA: MXN",0,'L',0);
				
				$pdf->Cell(197.5,4,"",'T',0,'C',0);
				//$pdf->Ln();
				$y=$pdf->GetY();
				$pdf->Ln();
				$pdf->MultiCell(137.5,4,numlet($row['total']),0,'L',0);
				$pdf->Ln();
				if($pdf->GetY() < $y) $y=$pdf->GetY();
				$pdf->Cell(26,4,"OBSERVACIONES:",0,0,'L',0);
				$pdf->MultiCell(111.5,4,$row['obs'],0,'L',0);
				if($pdf->GetY() < $y) $y=$pdf->GetY();
				$y2=$pdf->GetY();
				$pdf->SetXY(147.5,$y);
				$pdf->Cell(30,6.5,"SUBTOTAL",1,0,"R",1);
				$pdf->Cell(30,6.5,$row['subtotal'],0,0,"R",0);
				$pdf->Ln();
				if($row['descuento'] > 0){
					$pdf->SetXY(147.5,$y);
					$pdf->Cell(30,6.5,"DESCUENTO",1,0,"R",1);
					$pdf->Cell(30,6.5,$row['descuento'],0,0,"R",0);
					$pdf->Ln();
				}
				$pdf->SetX(147.5);
				if($row['iva'] > 0){
					$pdf->Cell(30,6.5,"I.V.A. 16%",1,0,"R",1);
				}
				else{
					$pdf->Cell(30,6.5,"EXCENTO I.V.A",1,0,"R",1);
				}
				$pdf->Cell(30,6.5,$row['iva'],0,0,"R",0);
				$pdf->Ln();
				$pdf->SetX(147.5);
				$pdf->Cell(30,6.5,"TOTAL",1,0,"R",1);
				$pdf->Cell(30,6.5,number_format($row['total']+$row['iva_retenido']+$row['isr_retenido'],2,".",""),0,0,"R",0);
				$pdf->Ln();
				$pdf->SetX(147.5);
				$pdf->Cell(30,6.5,"RET. I.V.A. ".round($row['por_iva_retenido'],2)."%",1,0,"R",1);
				$pdf->Cell(30,6.5,$row['iva_retenido'],0,0,"R",0);
				$pdf->Ln();
				$pdf->SetX(147.5);
				$pdf->Cell(30,6.5,"RET. I.S.R. ".round($row['por_isr_retenido'],2)."%",1,0,"R",1);
				$pdf->Cell(30,6.5,$row['isr_retenido'],0,0,"R",0);
				$pdf->Ln();
				$pdf->SetX(147.5);
				$pdf->Cell(30,6.5,"TOTAL",1,0,"R",1);
				$pdf->Cell(30,6.5,$row['total'],0,0,"R",0);
				$tt=number_format($row['total'],6,".","");
				if($y2<$pdf->GetY()) $y2=$pdf->GetY();
				$pdf->SetY($y2);
				$pdf->Ln();
				$pdf->Cell(190,4,"IMPUESTO RETENIDO DE CONFORMIDAD CON LA LEY DEL IMPUESTO AL VALOR AGREGADO",0,0,'C');
				$pdf->Ln();
				$y=$pdf->GetY();
				$pdf->SetX(45);
				$pdf->Cell(162.5,5.5,"CADENA ORIGINAL",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->SetFont('Arial','',6);
				$pdf->MultiCell(162.5,3,$row['cadenaoriginal'],0,"J",0);
				//$pdf->Ln();
				$pdf->SetX(45);
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(162.5,5.5,"SELLO DIGITAL EMISOR",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->SetFont('Arial','',6);
				$pdf->MultiCell(162.5,3,$row['sellodocumento'],0,"J",0);
				//$pdf->Ln();
				$pdf->SetX(45);
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(162.5,5.5,"SELLO DIGITAL SAT",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->SetFont('Arial','',6);
				$pdf->MultiCell(162.5,3,$row['sellotimbre'],0,"J",0);
				if($row['estatus']=='C'){
					//$pdf->Ln();
					$pdf->SetFont('Arial','',8);
					$pdf->SetX(45);
					$pdf->Cell(162.5,6.5,"FOLIO CANCELACION",1,0,"C",1);
					$pdf->Ln();
					$pdf->SetX(45);
					$pdf->SetFont('Arial','',6);
					$pdf->MultiCell(162.5,3,$row['respuesta2'],0,"J",0);
				}
				if($empresa==21){
					$pdf->MultiCell(190,3,"
					
					
					ESTE DOCUMENTO ES UNA REPRESENTACION IMPRESA DE UN CFDI",0,'C');
				}
				//QRcode::png("?re=".$re."&rr=".$rr."&tt=".$tt."&id=".$row['uuid'],"cfdi/comprobantes/barcode_".$row['empresa'].'_'.$row['cve'].".png","L",4,0);
				$codigo = "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?id=".$row['uuid']."&re=".$re."&rr=".$rr."&tt=".$tt."&fe=".substr($row['sellodocumento'],-8);

				if(!file_exists("../xmls/barcodesat_".$row['plaza'].'_'.$row['cve'].".png")) QRcode::png($codigo,"../xmls/barcodesat_".$row['plaza'].'_'.$row['cve'].".png","L",4,0);
				if(file_exists("../xmls/barcodesat_".$row['plaza'].'_'.$row['cve'].".png")) $pdf->Image("../xmls/barcodesat_".$row['plaza'].'_'.$row['cve'].".png",10,$y,34,34);
			}
		}
		$pdf->Output();
	}
	exit();
}

if($_POST['cmd']==101){
	if(count($_POST['xmls'])>0){
		$pdf = new FPDF('P','mm','LETTER');
		$pdf->SetFont("Arial","",8);
		foreach($_POST['xmls'] as $folio){
			if(file_exists('../xmls/cfdis_'.$folio.'.xml')){
				$pdf->AddPage();
				$pdf->MultiCell(190,4,file_get_contents('../xmls/cfdis_'.$folio.'.xml'));
			}
		}
		$pdf->Output();
	}
	exit();
}


if($_POST['ajax']==1){
	$array_tipopag=array(1=>"CHEQUE",2=>"TRANSFERENCIA",3=>"EFECTIVO");
	$filtro="";
	$select= " SELECT a.* FROM sat_xml as a WHERE a.plaza='".$_POST['plazausuario']."'";
	if($_POST['uuid']!=""){
		$select .= " AND a.uuid = '".$_POST['uuid']."'";
	}
	else{
		$select .= " AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		if($_POST['tipo']!="all") $select.=" AND a.tipo='".$_POST['tipo']."'";
		if($_POST['tipo_xml']!="all") $select.=" AND a.tipo_xml='".$_POST['tipo_xml']."'";
		if($_POST['emisor']!="") $select.=" AND (a.rfc='".$_POST['emisor']."' OR a.nombre LIKE '%".$_POST['emisor']."%')";
		if($_POST['receptor']!="") $select.=" AND (a.rfc_r='".$_POST['receptor']."' OR a.nombre_r LIKE '%".$_POST['receptor']."%')";
		if($_POST['estado']!="") $select.=" AND a.estado LIKE '%".$_POST['estado']."%'";
		if($_POST['estado_r']!="") $select.=" AND a.estado_r LIKE '%".$_POST['estado_r']."%'";
		if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
		if($_POST['estatus']==1) $select.=" AND a.estatus!='C'";
		elseif($_POST['estatus']==2) $select.=" AND a.estatus='C'";
		if($_POST['mostrar']==1) $select .=" AND (isr_retenido+iva_retenido) > 0";
		elseif($_POST['mostrar']==2) $select .=" AND (isr_retenido+iva_retenido) = 0";
	}
	$select.=" ORDER BY a.fecha DESC,hora DESC";
	$rsabonos=mysql_query($select) or die(mysql_error());
	$nivelUsuario=nivelUsuario();
	if(mysql_num_rows($rsabonos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$c=19;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.mysql_num_rows($rsabonos).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th><input type="checkbox" onClick="if(this.checked) $(\'.chks\').attr(\'checked\',\'checked\'); else $(\'.chks\').removeAttr(\'checked\');"></th><th>&nbsp;</th>';
		echo '<th>Folio</th><th>Tipo XML</th><th>Fecha</th><th>Emisor</th><th>RFC Emisor</th>
		<th>Receptor</th><th>RFC Receptor</th><th>Metodo Pago</th><th>Forma Pago</th><th>Subtotal</th>
		<th>Iva</th><th>Ret Iva</th><th>Ret Isr</th><th>Total</th><th>UUID</th>
		<th>Usuario<br>';
		echo '<select name="usuario" onchange="document.forma.usu.value=this.value;buscarRegistros();"><option value="all">---Todos---</option>';
		$res1=mysql_query("SELECT a.usuario FROM sat_xml as a WHERE plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY a.usuario");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['usuario'].'"';
			if($row1['usuario']==$_POST['usu']) echo ' selected';
			echo '>'.$array_usuario[$row1['usuario']].'</option>';
		}
		echo '</select></th></tr>'; 
		$sumacargo=array();
		$x=0;
		$folio=0;
		while ($Abono=mysql_fetch_array($rsabonos)){	
			if($folio==0) $folio=$Abono['cve'];
			rowb();
			$estatus='';
			echo '<td align="center"><input type="checkbox" class="chks" name="xmls[]" value="'.$Abono['plaza'].'_'.$Abono['cve'].'"></td>';
			echo '<td align="center" width="40" nowrap>';
			if(file_exists('../xmls/cfdis_'.$Abono['plaza'].'_'.$Abono['cve'].'.xml')){
				echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'../xmls/cfdis_'.$Abono['plaza'].'_'.$Abono['cve'].'.xml\',\'_blank\',\'0\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
			}
			if($nivelUsuario>2 && $row['estatus']!='C'){
				echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')){$(\'#panel\').show();atcr(\'sat_xml.php\',\'\',\'3\',\''.$Abono['cve'].'\');}"><img src="images/validono.gif" border="0" title="Cancelar '.$Abono['folio'].'"></a>';
			}
			echo '</td>';
			if($Abono['cve']!=$folio){
				echo '<td align="center"><font color="RED">'.$Abono['serie'].' '.$Abono['folio'].'</font></td>';
				$folio=$Abono['cve'];
			}
			else{
				echo '<td align="center">'.$Abono['serie'].' '.$Abono['folio'].'</td>';
			}
			echo '<td align="center">'.htmlentities(utf8_encode($array_tipo_xml[$Abono['tipo_xml']])).'</td>';
			echo '<td align="center">'.$Abono['fecha'].' '.$Abono['hora'].'</td>';
			echo '<td>'.htmlentities(utf8_encode($Abono['nombre'])).'</td>';
			echo '<td align="center">'.$Abono['rfc'].'</td>';
			echo '<td>'.htmlentities(utf8_encode($Abono['nombre_r'])).'</td>';
			echo '<td align="center">'.$Abono['rfc_r'].'</td>';
			echo '<td>'.htmlentities($arraymetodopago[$Abono['metodopago']]).'</td>';
			echo '<td>'.htmlentities($arrayformapago[$Abono['formapago']]).'</td>';
			echo '<td align="right">'.number_format($Abono['subtotal'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva_retenido'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['isr_retenido'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total'],2).'</td>';
			echo '<td>'.$Abono['uuid'].'</td>';
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$sumacargo[0]+=$Abono['subtotal'];
			$sumacargo[1]+=$Abono['iva'];
			$sumacargo[2]+=$Abono['iva_retenido'];
			$sumacargo[3]+=$Abono['isr_retenido'];
			$sumacargo[4]+=$Abono['total'];
			$folio--;
		}
		$c=9;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.$x.' Registro(s)</td>';
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		foreach($sumacargo as $k=>$v){
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
		}
		echo '<td bgcolor="#E9F2F8" colspan="2">&nbsp;</td>';
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

if($_POST['ajax']==2){
	mysql_query("UPDATE sat_xml SET fechapag='".$_POST['dato']."' WHERE plaza='".$_POST['plaza']."' AND cve='".$_POST['cvexml']."'");
	exit();
}

if($_POST['ajax']==3){
	mysql_query("UPDATE sat_xml SET tipopag='".$_POST['dato']."',cuentapag='".$_POST['cuentapag']."',foliopag='".$_POST['foliopag']."' WHERE plaza='".$_POST['plaza']."' AND cve='".$_POST['cvexml']."'");
	exit();
}

if($_POST['cmd']==3){
	require_once("../phpmailer/class.phpmailer.php");
	$res = mysql_query("SELECT * FROM sat_xml WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	if($row['estatus']!='C'){
		$cvefact=$row['cve'];
		if($row['uuid']!=""){
			require_once("../nusoap/nusoap.php");
			$resultadotimbres = validar_timbres($_POST['plazausuario']);
			if($resultadotimbres['seguir']){
				//$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
				$oSoapClient = new nusoap_client("http://integratucfdi.com/webservices/wscfdi.php?wsdl", true);			
				$err = $oSoapClient->getError();
				if($err!=""){
					echo "error1:".$err;
					desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
				}
				else{
					//print_r($documento);
					$oSoapClient->timeout = 300;
					$oSoapClient->response_timeout = 300;
					//$respuesta = $oSoapClient->call("cancelar", array ('id' => $rowempresa['idplaza'],'rfcemisor' =>$rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'uuid' => $row['respuesta1'], 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
					$respuesta = $oSoapClient->call("CancelarCFDI", array ('id' => $rowempresa['idplaza'],'rfcemisor' =>$rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'uuid' => $row['uuid'], 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
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
						desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
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
							desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
						}
						else{
							if($respuesta['resultado']){
								mysql_query("UPDATE sat_xml SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."',respuesta2='".$respuesta['mensaje']."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
								
							}
							else{
								$strmsg=$respuesta['mensaje'];
								desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
							}
							//print_r($respuesta);	
							echo $strmsg;
						}
					}
				}
			}
		}
		
	}
	$_POST['cmd']=0;
}


top($_SESSION);
	
	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {

		echo '<div id="dialog" style="display:none">
	<table>
	<tr><td class="tableEnc">Enviar por correo</td></tr>
	</table>
	<table width="100%">
		<tr><th align="left">Correos: </th><td><textarea id="correosenvio" cols="30" rows="3"></textarea><br>Separar con coma los correos</td></tr>
	</table>
	</div>'; 
	echo '<textarea style="display:none;" name="correosenvio" cols="30" rows="3"></textarea>';
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>';
		echo '<td><a href="#" onClick="atcr(\'sat_xml.php\',\'_blank\',\'10\',\'0\');"><img src="images/b_print.png" border="0">&nbsp;Imprimir PDF</a></td><td>&nbsp;</td>';
		//echo '<td><a href="#" onClick="mostrar_envio()"><img src="images/b_print.png" border="0">&nbsp;Enviar por Email</a></td><td>&nbsp;</td>';
		/*if(nivelUsuario()>1){
			echo '<td><a href="#" onClick="atcr(\'gastos_xml.php\',\'\',\'6\',\'0\');"><img src="images/nuevo.gif" border="0">&nbsp;Reenviar Archivos</a></td><td>&nbsp;</td>';
		}*/
		/*if(nivelUsuario()>2){
			echo '<td><a href="#" onClick="if(confirm(\'Esta seguro de cancelar los gastos\')){ atcr(\'gastos_xml.php\',\'\',\'7\',\'0\'); }"><img src="images/validono.gif" border="0">&nbsp;Cancelar Facturas</a></td><td>&nbsp;</td>';
		}*/
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.substr(fechaLocal(),0,8).'01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">UUID</td><td><input type="text" name="uuid" id="uuid"  size="30" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Emisor</td><td><input type="text" name="emisor" id="emisor"  size="30" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Estado Emisor</td><td><input type="text" name="estado" id="estado"  size="30" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Receptor</td><td><input type="text" name="receptor" id="receptor"  size="30" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Estado Receptor</td><td><input type="text" name="estado_r" id="estado_r"  size="30" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Tipo</td><td><select name="tipo" id="tipo"><option value="all" selected>Todos</option><option value="0">Emitidos</option>
		<option value="1">Recibidos</option></select></td></tr>';
		echo '<tr><td align="left">Tipo XML</td><td><select name="tipo_xml" id="tipo_xml"><option value="all">Todos</otpion>';
		foreach($array_tipo_xml as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
		echo '</select></td></tr>';
		echo '<tr><td align="left">Estatus</td><td><select name="estatus" id="estatus"><option value="0">Todos</option><option value="1">Activos</option>
		<option value="2">Cancelado</option></select></td></tr>';
		echo '<tr><td align="left">Mostrar</td><td><select name="mostrar" id="mostrar"><option value="0">Todos</option><option value="1">Con Retenciones</option>
		<option value="2">Sin Retenciones</option></select></td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		//Listado
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
			objeto.open("POST","sat_xml.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&mostrar="+document.getElementById("mostrar").value+"&tipo_xml="+document.getElementById("tipo_xml").value+"&uuid="+document.getElementById("uuid").value+"&tipo="+document.getElementById("tipo").value+"&receptor="+document.getElementById("receptor").value+"&estado_r="+document.getElementById("estado_r").value+"&estado="+document.getElementById("estado").value+"&estatus="+document.getElementById("estatus").value+"&emisor="+document.getElementById("emisor").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	

	function mostrar_envio(){
		var seleccionados = 0;
		if($(".chks").is(":checked")){
			$("#dialog").dialog("open");
		}
		else{
			alert("Necesita seleccionar una venta");
		}
	}
		
	';	
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(0,1); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
	function validanumero(campo) {
		var ValidChars = "0123456789.";
		var cadena=campo.value;
		var cadenares="";
		var digito;
		for(i=0;i<cadena.length;i++) {
			digito=cadena.charAt(i);
			if (ValidChars.indexOf(digito) != -1)
				cadenares+=""+digito;
		}
		campo.value=cadenares;
	}

	$("#dialog").dialog({ 
		bgiframe: true,
		autoOpen: false,
		modal: true,
		width: 600,
		height: 300,
		autoResize: true,
		position: "center",
		buttons: {
			"Aceptar": function(){ 
				if($("#correosenvio").val()==""){
					alert("Necesita ingresar el correo");
				}
				else{
					document.forma.correosenvio.value=$("#correosenvio").val();
					atcr("sat_xml.php","",12,0);
				}
			},
			"Cerrar": function(){ 
				document.forma.correosenvio.value="";
				$("#correosenvio").val("")
				$(this).dialog("close"); 
			}
		},
	}); 

	</Script>
';
}
bottom();
?>