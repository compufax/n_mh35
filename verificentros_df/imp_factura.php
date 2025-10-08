<?php
require_once('../fpdf153/fpdf.php');
require_once("../numlet.php");	
require_once("../phpqrcode/phpqrcode.php");
function _xmlToArray($node)
{
	$occurance = array();

	if($node->hasChildNodes()){
		foreach($node->childNodes as $child) {
			$occurance[$child->nodeName]++;
		}
	}

	if($node->nodeType == XML_TEXT_NODE) {
		$result = html_entity_decode(htmlentities($node->nodeValue, ENT_COMPAT, 'UTF-8'), ENT_COMPAT,'ISO-8859-15');
	}
	else {
		if($node->hasChildNodes()){
			$children = $node->childNodes;

			for($i=0; $i<$children->length; $i++) {
				$child = $children->item($i);

				if($child->nodeName != '#text') {
					if($occurance[$child->nodeName] > 0 /*1*/) {
						$result[$child->nodeName][] = _xmlToArray($child);
					}
					else {
						$result[$child->nodeName] = _xmlToArray($child);
					}
				}
				else if ($child->nodeName == '#text') {
					$text = _xmlToArray($child);

					if (trim($text) != '') {
						$result[$child->nodeName] = _xmlToArray($child);
					}
				}
			}
		}

		if($node->hasAttributes()) {
			$attributes = $node->attributes;

			if(!is_null($attributes)) {
				foreach ($attributes as $key => $attr) {
					$result["@".$attr->name] = $attr->value;
				}
			}
		}
	}

	return $result;
}

function generaFacturaPdf($plaza,$cvefact,$mostrar=0,$tipodocumento=1){
	global $base,$array_tipo_pago,$array_forma_pago;
	$res1 = mysql_query("SELECT * FROM plazas WHERE cve='".$plaza."'");
	$row1 = mysql_fetch_array($res1);
	$numeroPlaza=$row1['numero'];
	if($row1['nuevo_formato'] == 1)
	{
		generaFacturaPdfPuebla($plaza, $cvefact, $mostrar, $tipodocumento);
		return false;
	}
	$tabla='facturas';
	$campo='factura';
	$archivo='factura';
	if($tipodocumento == 2){
		$tabla='notascredito';
		$campo='notacredito';
		$archivo='nc';
	}

	$color="#F0F0F0";
	$pdf = new PDF_MC_Table('P','mm','LETTER');
	$pdf->AddPage();
	$res = mysql_query("SELECT * FROM ".$tabla." WHERE plaza='".$plaza."' AND cve='".$cvefact."'");
	$row = mysql_fetch_array($res);
	$pdf->SetFont("Arial","",8);
	$pdf->SetFillColor(240,240,240);
	
	$res1 = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$row['plaza']."'");
	$row1 = mysql_fetch_array($res1);
	
	if($row1['logoencabezado']==1){
		if(file_exists("../logos/logo".$plaza.".jpg")) $pdf->Image("logos/logo".$plaza.".jpg",10,5,197.5,40);
		$pdf->SetXY(122.5,45);
	}
	else{
		if(file_exists("../logos/logo".$plaza.".jpg")) $pdf->Image("logos/logo".$plaza.".jpg",10,5,100,35);
		$pdf->SetXY(122.5,5);
	}
	if($row['estatus']=='C'){
		if(file_exists("../images/cancelado.jpg")) $pdf->Image("images/cancelado.jpg",10,45,190,200);
	}
	if($row['tipo_factura']==1)
		$pdf->Cell(85,6.5,"HONORARIOS",1,0,"C",1);
	elseif($tipodocumento==1)
		$pdf->Cell(85,6.5,"FACTURA",1,0,"C",1);
	else
		$pdf->Cell(85,6.5,"NOTA DE CREDITO",1,0,"C",1);
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
	$pdf->Cell(52,4,':'.$array_tipo_pago[$row['tipo_pago']],0,0,"L",0);
	$pdf->Ln();
	if($row['tipo_pago']==5){
		$pdf->SetX(122.5);
		$pdf->Cell(33,4,'CUENTA',0,0,"L",0);
		$Banco=mysql_fetch_array(mysql_query("SELECT * FROM bancos WHERE cve='".$row['banco']."'"));
		$pdf->Cell(52,4,':'.$Banco['nombre'].' '.$row['cuenta_cheque'],0,0,"L",0);
		$pdf->Ln();
	}
	$pdf->SetX(122.5);
	$pdf->Cell(33,4,'FORMA DE PAGO',0,0,"L",0);
	$pdf->Cell(52,4,':'.$array_forma_pago[$row['forma_pago']],0,0,"L",0);
	$pdf->Ln();
	$pdf->SetX(122.5);
	$pdf->Cell(33,4,'EFECTOS FISCALES AL PAGO',0,0,"L",0);
	$pdf->Ln();
	/*if($row['check_sucursal']==1){
		$y=$pdf->GetY();
		$pdf->Cell(197.5,6.5,"DATOS DE LA SUCURSAL",1,0,"C",1);
		$pdf->Ln();
		$pdf->Cell(100,4,$row['nombre_sucursal'].'                     '.$row['rfc_sucursal'],0,0,"L",0);
		$pdf->Ln();
		$pdf->Cell(100,4,$row['calle_sucursal'].' '.$row['numero_sucursal'].' '.$row['colonia_sucursal'].' '.$row['localidad_sucursal'].', '.$row['municipio_sucursal'].', '.$row['estado_sucursal'].' C.P.'.$row['cp_sucursal'],0,0,"L",0);
		$pdf->Ln();
	}*/
	$y=$pdf->GetY();
	$pdf->Cell(100,6.5,"DATOS DEL EMISOR",1,0,"C",1);
	$pdf->Ln();
	$pdf->Cell(100,4,$row1['nombre'],0,0,"L",0);
	$pdf->Ln();
	$pdf->Cell(100,4,$row1['rfc'],0,0,"L",0);
	$re=$row1['rfc'];
	$pdf->Ln();
	$pdf->Cell(100,4,$row1['calle'].' '.$row1['numexterior'].' '.$row1['numinterior'],0,0,"L",0);
	$pdf->Ln();
	$pdf->Cell(100,4,$row1['colonia'],0,0,"L",0);
	$pdf->Ln();
	$pdf->Cell(100,4,$row1['localidad'].' C.P. '.$row1['codigopostal'].', '.$row1['municipio'].', '.$row1['estado'].'',0,0,"L",0);
	$pdf->Ln();
	if($row['check_sucursal']==1){
		$pdf->SetFont("Arial","B",8);
		$pdf->Cell(100,4,'Lugar de Expedicion: '.$row1['calle_sucursal'].' '.$row1['numero_sucursal'].'',0,0,"L",0);
		$pdf->Ln();
		$pdf->Cell(100,4,'     '.$row1['colonia'].' '.$row1['localidad_sucursal'],0,0,"L",0);
		$pdf->Ln();
		$pdf->Cell(100,4,'     C.P. '.$row1['cp_sucursal'].', '.$row1['municipio_sucursal'].', '.$row1['estado_sucursal'],0,0,"L",0);
		$pdf->SetFont("Arial","",8);
	}
	else{
		$pdf->Cell(100,4,'Lugar de Expedicion: '.$row1['municipio'].', '.$row1['estado'].'',0,0,"L",0);
	}
	$pdf->Ln();
	$pdf->Cell(100,4,$row1['regimen'],0,0,"L",0);
	$pdf->Ln();
	$y2=$pdf->GetY();
	$pdf->SetXY(110,$y);
	$pdf->Cell(97.5,6.5,"DATOS DEL RECEPTOR",1,0,"C",1);
	$res1 = mysql_query("SELECT * FROM clientes WHERE cve='".$row['cliente']."'");
	$row1 = mysql_fetch_array($res1);
	$pdf->Ln();
	$pdf->SetX(110);
	$pdf->MultiCell(97.5,4,$row1['nombre'],0,"L",0);
	//$pdf->Ln();
	$pdf->SetX(110);
	$pdf->Cell(97.5,4,$row1['rfc'],0,0,"L",0);
	$rr=$row1['rfc'];
	$pdf->Ln();
	$pdf->SetX(110);
	$pdf->Cell(97.5,4,$row1['calle'].' '.$row1['numexterior'].' '.$row1['numinterior'],0,0,'L',0);
	$pdf->Ln();
	$pdf->SetX(110);
	$pdf->Cell(97.5,4,$row1['colonia'],0,0,"L",0);
	$pdf->Ln();
	$pdf->SetX(110);
	if($row1['localidad']!="")
		$pdf->Cell(97.5,4,$row1['localidad'].' C.P. '.$row1['codigopostal'].', '.$row1['municipio'].', '.$row1['estado'],0,0,"L",0);
	else
		$pdf->Cell(97.5,4,'C.P. '.$row1['codigopostal'].', '.$row1['municipio'].', '.$row1['estado'],0,0,"L",0);
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
	$res2 = mysql_query("SELECT * FROM ".$tabla."mov WHERE plaza='".$plaza."' AND cvefact='".$cvefact."'");
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
	if($row['carta_porte']==1){
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Cell(26,4,"LOAD:",0,0,'L',0);
		$pdf->MultiCell(111.5,4,$row['load_cliente'],0,'L',0);
		$pdf->Cell(26,4,"CLIENTE:",0,0,'L',0);
		$pdf->MultiCell(111.5,4,$row['nombre_cliente'],0,'L',0);
		$pdf->Cell(26,4,"DIRECCION:",0,0,'L',0);
		$pdf->MultiCell(111.5,4,$row['direccion_cliente'],0,'L',0);
		$pdf->Cell(26,4,"TIPO DE PAGO:",0,0,'L',0);
		$pdf->MultiCell(111.5,4,$row['tipopago_cliente'],0,'L',0);
		//if($row['tipopago_cliente']>0){
			//$pdf->Cell(26,4,"BANCO:",0,0,'L',0);
			//$pdf->MultiCell(111.5,4,$row['banco_cliente'],0,'L',0);
			$pdf->Cell(26,4,"CUENTA:",0,0,'L',0);
			$pdf->MultiCell(111.5,4,$row['cuenta_cliente'],0,'L',0);
		//}
	}
	$pdf->Cell(197.5,4,"",'T',0,'C',0);
	//$pdf->Ln();
	$y=$pdf->GetY();
	$pdf->Ln();
	$pdf->MultiCell(137.5,4,numlet($row['total']),0,'L',0);
	$pdf->Ln();
	if($pdf->GetY() < $y) $y=$pdf->GetY();
	$pdf->Cell(26,4,"OBSERVACIONES:",0,0,'L',0);
	$pdf->MultiCell(111.5,4,$row['obs'],0,'L',0);
	if($tipodocumento == 1){
		$resTicket=mysql_query("SELECT * FROM cobro_engomado WHERE plaza='$plaza' AND ".$campo."='$cvefact' AND estatus!='C'");
		while($rowTicket=mysql_fetch_array($resTicket)){
			$pdf->SetFont("Arial","",14);
			$pdf->MultiCell(130,4,'Ticket: '.$rowTicket['cve'].' Fecha Ticket: '.$rowTicket['fecha'].' Placa: '.$rowTicket['placa'],0,'L',0);
		}
	}
	elseif($tipodocumento == 2){
		$resTicket=mysql_query("SELECT * FROM facturas WHERE plaza='$plaza' AND cve='".$row['factura']."' AND estatus!='C'");
		if($rowTicket=mysql_fetch_array($resTicket)){
			$pdf->SetFont("Arial","",14);
			$pdf->MultiCell(111.5,4,'Factura: '.$rowTicket['folio'].' Fecha Factura: '.$rowTicket['fecha'].' UUID: '.$rowTicket['uuid'],0,'L',0);
		}
	}
	$pdf->SetFont("Arial","",8);
	if($pdf->GetY() < $y) $y=$pdf->GetY();
	$y2=$pdf->GetY();
	$pdf->SetXY(147.5,$y);
	$pdf->Cell(30,6.5,"SUBTOTAL",1,0,"R",1);
	$pdf->Cell(30,6.5,$row['subtotal'],0,0,"R",0);
	$pdf->Ln();
	$pdf->SetX(147.5);
	$pdf->Cell(30,6.5,"I.V.A. 16%",1,0,"R",1);
	$pdf->Cell(30,6.5,$row['iva'],0,0,"R",0);
	$pdf->Ln();
	/*$pdf->SetX(147.5);
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
	$pdf->Ln();*/
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
	if($row['estatus']=='C'){
		//$pdf->Ln();
		$pdf->SetX(45);
		$pdf->Cell(162.5,6.5,"FOLIO CANCELACION",1,0,"C",1);
		$pdf->Ln();
		$pdf->SetX(45);
		$pdf->MultiCell(162.5,4,$row['respuesta2'],0,"J",0);
	}
	if($empresa==21){
		$pdf->MultiCell(190,4,"
		
		
		ESTE DOCUMENTO ES UNA REPRESENTACION IMPRESA DE UN CFDI",0,'C');
	}
	QRcode::png("?re=".$re."&rr=".$rr."&tt=".$tt."&id=".$row['uuid'],"../cfdi/comprobantes/barcode_".$row['plaza'].'_'.$row['cve'].".png","L",4,0);
	if(file_exists("../cfdi/comprobantes/barcode_".$row['plaza'].'_'.$row['cve'].".png")) $pdf->Image("../cfdi/comprobantes/barcode_".$row['plaza'].'_'.$row['cve'].".png",10,$y,34,34);
	if($row['estatus']=='C'){
		$pdf->Output("../cfdi/comprobantes/".$archivo."c_".$row['plaza']."_".$row['cve'].".pdf","F");
	}
	else{
		$pdf->Output("../cfdi/comprobantes/".$archivo."_".$row['plaza']."_".$row['cve'].".pdf","F");
	}
	if(file_exists("../cfdi/comprobantes/barcode_".$row['plaza'].'_'.$row['cve'].".png")) unlink("../cfdi/comprobantes/barcode_".$row['plaza'].'_'.$row['cve'].".png");
	
	
	if($mostrar==1){
		$pdf->Output();
	}
}


function generaFacturaPdfPuebla($plaza,$cvefact,$mostrar=0,$tipodocumento=1){
	global $base,$array_tipo_pago,$array_forma_pago;
	$tabla='facturas';
	$campo='factura';
	$archivo='factura';
	$nombre='FACTURA';
	$cfdi = 'cfdi';
	if($tipodocumento == 2){
		$tabla='notascredito';
		$campo='notacredito';
		$archivo='nc';
		$nombre='NOTA DE CREDITO';
		$cfdi = 'cfdinc';
	}
	$arch = '../cfdi/comprobantes/'.$cfdi.'_'.$plaza.'_'.$cvefact.'.xml';
	$cadena= file_get_contents($arch);
	$dom = new DOMDocument;
	$dom->loadXML($cadena);
	$arreglo = _xmlToArray($dom);
	if($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@nombre']==''){
		generaFacturaPdfPuebla33($plaza, $cvefact, $mostrar, $tipodocumento);
		return false;
	}
	$color="#F0F0F0";
	$pdf = new PDF_MC_Table('P','mm','LETTER');
	$pdf->AddPage();
	$res = mysql_query("SELECT * FROM $tabla WHERE plaza='".$plaza."' AND cve='".$cvefact."'");
	$row = mysql_fetch_array($res);
	
	$row['folio'] = $arreglo['cfdi:Comprobante'][0]['@folio'];
	$cuenta_pago = 'NO APLICA';
	$pdf->SetFont("Arial","",8);
	$pdf->SetFillColor(240,240,240);
	$res1 = mysql_query("SELECT * FROM plazas WHERE cve='".$row['plaza']."'");
	$row1 = mysql_fetch_array($res1);
	$numeroPlaza=$row1['numero'];
	$res1 = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$row['plaza']."'");
	$row1 = mysql_fetch_array($res1);
	//echo "SELECT * FROM datosempresas WHERE plaza='".$row['plaza']."'";
	//echo '<pre>'; print_r($row1); exit;
	if($arreglo['cfdi:Comprobante'][0]['@NumCtaPago']!='')
		$cuenta_pago=$arreglo['cfdi:Comprobante'][0]['@NumCtaPago'];
	$row1['nombre']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@nombre']);
	$row1['rfc']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@rfc'];
	$row1['calle']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@calle']);
	$row1['numexterior']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@noExterior'];
	$row1['numinterior']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@noInterior'];
	$row1['colonia']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@colonia']);
	$row1['localidad']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@localidad']);
	$row1['municipio']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@municipio']);
	$row1['estado']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@estado']);
	$row1['codigopostal']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@codigoPostal'];
	$row1['regimen']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:RegimenFiscal'][0]['@Regimen'];

	$row1['calle_sucursal']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:ExpedidoEn'][0]['@calle']);
	$row1['numero_sucursal']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:ExpedidoEn'][0]['@noExterior'].' '.$arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:cfdi:ExpedidoEn'][0]['@noInterior'];
	$row1['colonia_sucursal']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:ExpedidoEn'][0]['@colonia']);
	$row1['localidad_sucursal']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:ExpedidoEn'][0]['@localidad']);
	$row1['municipio_sucursal']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:ExpedidoEn'][0]['@municipio']);
	$row1['estado_sucursal']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:ExpedidoEn'][0]['@estado']);
	$row1['cp_sucursal']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:ExpedidoEn'][0]['@codigoPostal'];
	if($tipodocumento != 2){
		if($row['rfc_factura'] == ''){
			mysql_query("UPDATE facturas SET rfc_factura = '".$row1['rfc']."' WHERE plaza='".$plaza."' AND cve='".$cvefact."'");
		}
	}
	$re=$row1['rfc'];
	$res2 = mysql_query("SELECT * FROM clientes WHERE cve='".$row['cliente']."'");
	$row2 = mysql_fetch_array($res2);
	$row2['nombre']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['@nombre']);
	$row2['rfc']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['@rfc'];
	$row2['calle']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@calle']);
	$row2['numexterior']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@noExterior'];
	$row2['numinterior']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@noInterior'];
	$row2['colonia']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@colonia']);
	$row2['localidad']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@localidad'];
	$row2['municipio']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@municipio']);
	$row2['estado']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@estado']);
	$row2['codigopostal']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@codigoPostal'];
	if($row1['logoencabezado']==1){
		if(file_exists("../logos/logo".$plaza.".jpg")) $pdf->Image("../logos/logo".$plaza.".jpg",10,5,197.5,40);
		$pdf->SetXY(122.5,45);
	}
	else{
		if(file_exists("../logos/logo".$plaza.".jpg")) $pdf->Image("../logos/logo".$plaza.".jpg",10,10,50,40);
		$pdf->SetXY(122.5,5);
	}
	if($row['estatus']=='C'){
		if(file_exists("../images/cancelado.jpg")) $pdf->Image("../images/cancelado.jpg",10,45,190,200);
	}
	
	$pdf->SetXY(65,10);
	$pdf->MultiCell(80,5,$row1['nombre'].'
'.$row1['rfc'].' '.$row1['calle'].' '.$row1['numexterior'].' '.$row1['numinterior'].'
'.$row1['colonia'].',
'.$row1['localidad'].' '.$row1['codigopostal'].'
'.$row1['municipio'].' '.$row1['estado'].'
MEXICO');
	$y = $pdf->GetY();
	$pdf->SetXY(150,14);
	$pdf->Cell(20,4,'SERIE:  ',0,0,'R');
	$pdf->Cell(20,4,$row['serie'],0,0,'L');
	$pdf->Ln();
	$pdf->SetX(150);
	$pdf->Cell(20,4,$nombre.':  ',0,0,'R');
	$pdf->Cell(20,4,$row['folio'],0,0,'L');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetX(150);
	$pdf->Cell(20,4,'FECHA:  ',0,0,'R');
	$pdf->Cell(20,4,date('j/n/Y', strtotime($row['fecha'])),0,0,'L');
	$pdf->Ln();
	$pdf->SetX(150);
	$pdf->Cell(20,4,'HORA:  ',0,0,'R');
	$pdf->Cell(20,4,$row['hora'],0,0,'L');
	$pdf->Ln();
	if($y<$pdf->GetY()){
		$y = $pdf->GetY();
	}
	$pdf->SetXY(150, 50);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(60,5,'Documento Válido',1);
	$pdf->Ln();
	$pdf->Cell(90,4,'R E C E P T O R',0,0,'C');
	$pdf->Ln();
	$y = $pdf->GetY();
	$pdf->Cell(20,4,'Cliente:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(70,4,$row2['nombre']);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'R.F.C.:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(70,4,$row2['rfc']);
	$rr=$row2['rfc'];
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'Domicilio:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(70,4,$row2['calle'].' No. '.$row2['numexterior'].' '.$row2['numinterior']);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'Colonia:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(70,4,$row2['colonia']);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'Ciudad:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(70,4,$row2['localidad']);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'DEL/MUN:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(70,4,$row2['municipio']);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'Estado:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(70,4,$row2['estado']);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'C.P.:');
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(20,4,$row2['codigopostal']);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'PAIS:');
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(20,4,'MEXICO');
	$pdf->Ln();
	$pdf->Ln();
	$y2=$pdf->GetY();

	$pdf->SetXY(110,$y);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'REGIMEN:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(70,4,$row1['regimen']);
	$pdf->Ln();
	$pdf->SetX(110);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(35,4,'LUGAR DE EXPEDICION:');
	$pdf->SetFont('Arial','',8);
	if($row['check_sucursal'] == 1)
		$pdf->Cell(30,4,'Sucursal D.F.');
	$pdf->Ln();
	$pdf->SetX(110);
	//if($row['check_sucursal'] == 1){
		$pdf->MultiCell(90,4,$row1['calle_sucursal'].' No. '.$row1['numero_sucursal'].'
'.$row1['colonia_sucursal'].',
'.$row1['cp_sucursal'].', '.$row1['municipio_sucursal'].', '.$row1['estado_sucursal'].', MEXICO');
	/*}
	else{
		$pdf->MultiCell(90,4,$row1['calle'].' '.$row1['numexterior'].' '.$row1['numinterior'].'
'.$row1['colonia'].',
'.$row1['localidad'].', '.$row1['codigopostal'].', '.$row1['municipio'].', '.$row1['estado'].', MEXICO');
	}*/
	$pdf->Ln();
	if($y2<$pdf->GetY()){
		$y2 = $pdf->GetY();
	}

	$pdf->SetXY(10,$y2);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(30,4,"Cantidad",1,0,"C",0);
	$pdf->Cell(30,4,"Unidad",1,0,"C",0);
	$pdf->Cell(77.5,4,"Concepto / Descripción",1,0,"C",0);
	$pdf->Cell(30,4,"Valor Unit",1,0,"C",0);
	$pdf->Cell(30,4,"Importe",1,0,"C",0);
	$pdf->Ln();
	$y=$pdf->GetY();
	$pdf->SetFont('Arial','',8);
	$res2 = mysql_query("SELECT * FROM ".$tabla."mov WHERE plaza='".$plaza."' AND cvefact='".$cvefact."'");
	while($row2 = mysql_fetch_array($res2)){
		$pdf->SetXY(10,$y);
		$pdf->Cell(30,4,$row2['cantidad'],1,0,"R",0);
		$pdf->Cell(30,4,$row2['unidad'],1,0,"C",0);
		$y3=$pdf->GetY();
		if($y!=$y3) $y=$y3;
		$pdf->MultiCell(77.5,4,$row2['concepto'],1,"J",0);
		$y2=$pdf->GetY();
		$pdf->SetXY(147.5,$y);
		$pdf->Cell(30,4,$row2['precio'],1,0,"R",0);
		$pdf->Cell(30,4,$row2['importe'],1,0,"R",0);
		$y=$y2;
	}
	$pdf->SetXY(10,$y);
	$pdf->Cell(137.5,4,"IMPORTE CON LETRA",'1',0,'C',0);
	$pdf->Cell(5,4,'');
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'SUBTOTAL:',1);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(30,4,$row['subtotal'],1,0,'R');
	$pdf->Cell(5,4,'','R');
	$pdf->Ln();
	$pdf->Cell(137.5,4," ",'LR',0,'C',0);
	$pdf->Cell(5,4,'');
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'I.V.A. 16%:',1);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(30,4,$row['iva'],1,0,'R');
	$pdf->Cell(5,4,'','R');
	$pdf->Ln();
	$y=$pdf->GetY();
	$pdf->Cell(137.5,4,'','LR',0,'L',0);
	$pdf->Cell(5,4,'');
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'TOTAL:',1);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(30,4,$row['total'],1,0,'R');
	$pdf->Cell(5,4,'','R');
	$pdf->Ln();
	$pdf->Cell(137.5,24,'','LBR',0,'L',0);
	$pdf->Cell(60,24,'','LBR',0,'L',0);
	$pdf->Ln();
	$y2=$pdf->GetY();
	$pdf->SetXY(13,$y);
	$pdf->MultiCell(137,4,numlet($row['total']).'

Método pago: '.$array_tipo_pago[$row['tipo_pago']].'
Condiciones: CONTADO
No. Cta pago: '.$cuenta_pago);
	$pdf->SetX(13);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(137,4,'Este documento es una representación impresa de un CFDI');
	$pdf->Ln();
	$pdf->SetX(13);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(137,4,'*Efectos fiscales al pago, *Pago en una sola exhibición');
	$y2+=4;

	$pdf->SetXY(10,$y2);
	$tt=number_format($row['total'],6,".","");
	QRcode::png("?re=".$re."&rr=".$rr."&tt=".$tt."&id=".$row['uuid'],"../cfdi/comprobantes/barcode_".$row['plaza'].'_'.$row['cve'].".png","L",4,0);
	if(file_exists("../cfdi/comprobantes/barcode_".$row['plaza'].'_'.$row['cve'].".png")) $pdf->Image("../cfdi/comprobantes/barcode_".$row['plaza'].'_'.$row['cve'].".png",20,$y2,34,34);
	$pdf->SetXY(60,$y2);
	$pdf->Cell(26,4,"OBSERVACIONES:",0,0,'L',0);
	$pdf->Ln();
	$pdf->SetX(60);
	$pdf->MultiCell(130,4,$row['obs'],0,'L',0);
	
	//$resTicket=mysql_query("SELECT * FROM cobro_engomado WHERE plaza='$plaza' AND LEFT(fecha,7)='".substr($row['fecha'],0,7)."' AND ".$campo."='$cvefact' AND estatus!='C'");
	$resTicket=mysql_query("SELECT * FROM cobro_engomado WHERE plaza='$plaza' AND ".$campo."='$cvefact' AND estatus!='C'");
	while($rowTicket=mysql_fetch_array($resTicket)){
		$pdf->SetFont("Arial","",8);
		$pdf->SetX(60);
		$pdf->MultiCell(130,2.5,'Ticket: '.$rowTicket['cve'].' Fecha Venta: '.$rowTicket['fecha'].' Placa: '.$rowTicket['placa'],0,'L',0);
	}
	$pdf->SetXY(80, $y2+30);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(100,4,'Folio fiscal: '.$row['uuid']);
	$pdf->Ln();
	$pdf->SetX(80);
	$pdf->Cell(100,4,'SERIE DEL SELLO: '.$row['seriecertificado']);
	$pdf->Ln();
	$pdf->Cell(70,4,'No de Serie del Certificado del SAT:          ',0,0,'R');
	$pdf->Cell(100,4,$row['seriecertificadosat']);
	$pdf->Ln();
	$pdf->Cell(70,4,'Fecha y hora de certificación:          ',0,0,'R');
	$pdf->Cell(100,4,date('j/n/Y - H:i:s', strtotime($row['fechatimbre'])));
	$pdf->Ln();
	$pdf->Cell(162.5,4,"Sello digital del CFDI",0,0,"L");
	$pdf->Ln();
	$pdf->SetX(20);
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(170,4,$row['sellodocumento'],0,"C",0);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(162.5,4,"Sello del SAT",0,0,"L");
	$pdf->Ln();
	$pdf->SetX(20);
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(170,4,$row['sellotimbre'],0,"C",0);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(162.5,4,"Cadena original del complemento de certificación digital del SAT",0,0,"L");
	$pdf->Ln();
	$pdf->SetX(20);
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(170,4,$row['cadenaoriginal'],0,"C",0);
	if($row['estatus']=='C'){
		//$pdf->Ln();
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(162.5,4,"Folio de Cancelación",0,0,"L");
		$pdf->Ln();
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',8);
		$pdf->MultiCell(150,4,$row['respuesta2'],0,"C",0);
	}
	
	if($mostrar==1){
		$pdf->Output();
	}
	else{
		if($row['estatus']=='C'){
			$pdf->Output("../cfdi/comprobantes/".$archivo."c_".$row['plaza']."_".$row['cve'].".pdf","F");
		}
		else{
			$pdf->Output("../cfdi/comprobantes/".$archivo."_".$row['plaza']."_".$row['cve'].".pdf","F");
		}
	}
	if(file_exists("../cfdi/comprobantes/barcode_".$row['plaza'].'_'.$row['cve'].".png")) unlink("../cfdi/comprobantes/barcode_".$row['plaza'].'_'.$row['cve'].".png");
}

function generaFacturaPdfPuebla33($plaza,$cvefact,$mostrar=0,$tipodocumento=1){
	global $base,$array_tipo_pago,$array_forma_pago;
	$array_regimensat=array();
	$res = mysql_query("SELECT * FROM regimen_sat ORDER BY nombre");
	while($row = mysql_fetch_assoc($res)) $array_regimensat[$row['clave']] = $row['nombre'];

	$array_usocfdi=array();
	$res = mysql_query("SELECT * FROM usocfdi_sat ORDER BY nombre");
	while($row = mysql_fetch_assoc($res)) $array_usocfdi[$row['clave']] = $row['nombre'];

	$tabla='facturas';
	$campo='factura';
	$archivo='factura';
	$nombre='FACTURA';
	$cfdi = 'cfdi';
	$tipocomprobante = 'INGRESO';
	if($tipodocumento == 2){
		$tabla='notascredito';
		$campo='notacredito';
		$archivo='nc';
		$nombre='NOTA DE CREDITO';
		$cfdi = 'cfdinc';
		$tipocomprobante = 'EGRESO';
	}
	$arch = '../cfdi/comprobantes/'.$cfdi.'_'.$plaza.'_'.$cvefact.'.xml';
	$cadena= file_get_contents($arch);
	$dom = new DOMDocument;
	$dom->loadXML($cadena);
	$arreglo = _xmlToArray($dom);
	$color="#F0F0F0";
	$pdf = new PDF_MC_Table('P','mm','LETTER');
	$pdf->AddPage();
	$res = mysql_query("SELECT * FROM $tabla WHERE plaza='".$plaza."' AND cve='".$cvefact."'");
	$row = mysql_fetch_array($res);
	$cuenta_pago = 'NO APLICA';
	$pdf->SetFont("Arial","",8);
	$pdf->SetFillColor(240,240,240);
	$res1 = mysql_query("SELECT * FROM plazas WHERE cve='".$row['plaza']."'");
	$row1 = mysql_fetch_array($res1);
	$numeroPlaza=$row1['numero'];
	$regimen = $row1['regimensat'];
	$res1 = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$row['plaza']."'");
	$row1 = mysql_fetch_array($res1);
	if($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@nombre']!=''){
		if($arreglo['cfdi:Comprobante'][0]['@NumCtaPago']!='')
			$cuenta_pago=$arreglo['cfdi:Comprobante'][0]['@NumCtaPago'];
	
		$row1['regimen']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:RegimenFiscal'][0]['@Regimen']);
		$row1['nombre']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@nombre']);
		$row1['rfc']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@rfc'];
		$row1['calle']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@calle']);
		$row1['numexterior']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@noExterior'];
		$row1['numinterior']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@noInterior'];
		$row1['colonia']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@colonia']);
		$row1['localidad']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@localidad']);
		$row1['municipio']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@municipio']);
		$row1['estado']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@estado']);
		$row1['codigopostal']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@codigoPostal'];
	}
	else{
		$cuenta_pago=$row['cuenta_cliente'];
		$row1['nombre']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@Nombre']);
		if($row['datosfacturas'] == ''){
			$datosfacturas['emisor'] = array(
				'regimen' => utf8_encode($row1['regimen']),
				'nombre' => utf8_encode($row1['nombre']),
				'rfc' => $row1['rfc'],
				'calle' => utf8_encode($row1['calle']),
				'numexterior' => utf8_encode($row1['numexterior']),
				'numinterior' => utf8_encode($row1['numinterior']),
				'colonia' => utf8_encode($row1['colonia']),
				'localidad' => utf8_encode($row1['localidad']),
				'municipio' => utf8_encode($row1['municipio']),
				'estado' => utf8_encode($row1['estado']),
				'codigopostal' => $row1['codigopostal']
			);
		}
		else{
			$datosfacturas = json_decode($row['datosfacturas'], true);
			$row1['regimen']= utf8_decode($datosfacturas['emisor']['regimen']);
			$row1['nombre']= utf8_decode($datosfacturas['emisor']['nombre']);
			$row1['rfc']= utf8_decode($datosfacturas['emisor']['rfc']);
			$row1['calle']= utf8_decode($datosfacturas['emisor']['calle']);
			$row1['numexterior']= utf8_decode($datosfacturas['emisor']['numexterior']);
			$row1['numinterior']= utf8_decode($datosfacturas['emisor']['numinterior']);
			$row1['colonia']= utf8_decode($datosfacturas['emisor']['colonia']);
			$row1['localidad']= utf8_decode($datosfacturas['emisor']['localidad']);
			$row1['municipio']= utf8_decode($datosfacturas['emisor']['municipio']);
			$row1['estado']= utf8_decode($datosfacturas['emisor']['estado']);
			$row1['codigopostal']= utf8_decode($datosfacturas['emisor']['codigopostal']);
		}
	}
	
	$row1['rfc']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@Rfc'];
	if($tipodocumento != 2){
		if($row['rfc_factura'] == ''){
			mysql_query("UPDATE facturas SET rfc_factura = '".$row1['rfc']."' WHERE plaza='".$plaza."' AND cve='".$cvefact."'");
		}
	}
	$re=$row1['rfc'];
	$res2 = mysql_query("SELECT * FROM clientes WHERE cve='".$row['cliente']."'");
	$row2 = mysql_fetch_array($res2);
	if($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['@nombre'] != '')
	{
		$row2['nombre']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['@nombre']);
		$row2['rfc']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['@rfc'];
		$row2['calle']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@calle']);
		$row2['numexterior']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@noExterior'];
		$row2['numinterior']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@noInterior'];
		$row2['colonia']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@colonia']);
		$row2['localidad']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@localidad'];
		$row2['municipio']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@municipio']);
		$row2['estado']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@estado']);
		$row2['codigopostal']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@codigoPostal'];
	}
	else{
		if($row['rfc_cli'] != ''){
			$row2['nombre']= $row['nombre_cli'];
			$row2['rfc']= $row['rfc_cli'];
			$row2['calle']= $row['calle_cli'];
			$row2['numexterior']= $row['numext_cli'];
			$row2['numinterior']= $row['numint_cli'];
			$row2['colonia']= $row['colonia_cli'];
			$row2['localidad']= $row['localidad_cli'];
			$row2['municipio']= $row['municipio_cli'];
			$row2['estado']= $row['estado_cli'];
			$row2['codigopostal']= $row['cp_cli'];
		}
		elseif($row['datosfacturas'] == '' || $row['datosfacturas'] == 'null'){
			$datosfacturas['receptor'] = array(
				'nombre' => utf8_encode($row2['nombre']),
				'rfc' => $row2['rfc'],
				'calle' => utf8_encode($row2['calle']),
				'numexterior' => utf8_encode($row2['numexterior']),
				'numinterior' => utf8_encode($row2['numinterior']),
				'colonia' => utf8_encode($row2['colonia']),
				'localidad' => utf8_encode($row2['localidad']),
				'municipio' => utf8_encode($row2['municipio']),
				'estado' => utf8_encode($row2['estado']),
				'codigopostal' => $row2['codigopostal']
			);
		}
		else{
			$datosfacturas = json_decode($row['datosfacturas'], true);
			$row2['nombre']= utf8_decode($datosfacturas['receptor']['nombre']);
			$row2['rfc']= utf8_decode($datosfacturas['receptor']['rfc']);
			$row2['calle']= utf8_decode($datosfacturas['receptor']['calle']);
			$row2['numexterior']= utf8_decode($datosfacturas['receptor']['numexterior']);
			$row2['numinterior']= utf8_decode($datosfacturas['receptor']['numinterior']);
			$row2['colonia']= utf8_decode($datosfacturas['receptor']['colonia']);
			$row2['localidad']= utf8_decode($datosfacturas['receptor']['localidad']);
			$row2['municipio']= utf8_decode($datosfacturas['receptor']['municipio']);
			$row2['estado']= utf8_decode($datosfacturas['receptor']['estado']);
			$row2['codigopostal']= utf8_decode($datosfacturas['receptor']['codigopostal']);
		}
	}
	if($row['datosfacturas'] == ''){
		mysql_query("UPDATE $tabla SET datosfacturas='".addslashes(json_encode($datosfacturas))."' WHERE plaza='".$plaza."' AND cve='".$cvefact."'");
	}
	if($row1['logoencabezado']==1){
		if(file_exists("../logos/logo".$plaza.".jpg")) $pdf->Image("../logos/logo".$plaza.".jpg",10,5,197.5,40);
		$pdf->SetXY(122.5,45);
	}
	else{
		if(file_exists("../logos/logo".$plaza.".jpg")) $pdf->Image("../logos/logo".$plaza.".jpg",10,10,50,40);
		$pdf->SetXY(122.5,5);
	}
	if($row['estatus']=='C'){
		if(file_exists("../images/cancelado.jpg")) $pdf->Image("../images/cancelado.jpg",10,45,190,200);
	}
	
	$pdf->SetXY(65,10);
	/*$pdf->MultiCell(80,5,$row1['nombre'].'
'.$row1['rfc'].' '.$row1['calle'].' '.$row1['numexterior'].' '.$row1['numinterior'].'
'.$row1['colonia'].',
'.$row1['localidad'].' '.$row1['codigopostal'].'
'.$row1['municipio'].' '.$row1['estado'].'
MEXICO');*/
	$pdf->MultiCell(80,5,$row1['nombre'].'
'.$row1['rfc'].'
'.$row1['direccionfiscal'].'
MEXICO');
	$y = $pdf->GetY();
	$pdf->SetXY(170,14);
	$pdf->Cell(20,4,'SERIE:  ',0,0,'R');
	$pdf->Cell(20,4,$row['serie'],0,0,'L');
	$pdf->Ln();
	$pdf->SetX(170);
	$pdf->Cell(20,4,$nombre.':  ',0,0,'R');
	$pdf->Cell(20,4,$row['folio'],0,0,'L');
	$pdf->Ln();
	$pdf->SetX(170);
	$pdf->Cell(20,4,'TIPO DE COMPROBANTE:  ',0,0,'R');
	$pdf->Cell(20,4,$tipocomprobante,0,0,'L');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetX(170);
	$pdf->Cell(20,4,'FECHA:  ',0,0,'R');
	$pdf->Cell(20,4,date('j/n/Y', strtotime($row['fecha'])),0,0,'L');
	$pdf->Ln();
	$pdf->SetX(170);
	$pdf->Cell(20,4,'HORA:  ',0,0,'R');
	$pdf->Cell(20,4,$row['hora'],0,0,'L');
	$pdf->Ln();
	if($y<$pdf->GetY()){
		$y = $pdf->GetY();
	}
	$pdf->SetXY(150, 50);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(60,5,'Documento Válido',1);
	$pdf->Ln();
	$pdf->Cell(90,4,'R E C E P T O R',0,0,'C');
	$pdf->Ln();
	$y = $pdf->GetY();
	$pdf->Cell(20,4,'Cliente:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(70,4,$row2['nombre']);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'R.F.C.:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(70,4,$row2['rfc']);
	$rr=$row2['rfc'];
	/*$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'Domicilio:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(70,4,$row2['calle'].' No. '.$row2['numexterior'].' '.$row2['numinterior']);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'Colonia:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(70,4,$row2['colonia']);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'Ciudad:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(70,4,$row2['localidad']);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'DEL/MUN:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(70,4,$row2['municipio']);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'Estado:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(70,4,$row2['estado']);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'C.P.:');
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(20,4,$row2['codigopostal']);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'PAIS:');
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(20,4,'MEXICO');*/
	$pdf->Ln();
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'USO CFDI:');
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(20,4,$row2['usocfdi']);
	$pdf->Ln();
	$pdf->Ln();
	$y2=$pdf->GetY();

	$pdf->SetXY(110,$y);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'REGIMEN:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(90,4,$regimen.' '.$array_regimensat[$regimen]);
	$pdf->Ln();
	$pdf->SetX(110);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(35,4,'LUGAR DE EXPEDICION:');
	$pdf->SetFont('Arial','',8);
	$pdf->Ln();
	$pdf->SetX(110);
	$pdf->MultiCell(90,4,$arreglo['cfdi:Comprobante'][0]['@LugarExpedicion']);
	
	$pdf->Ln();
	$pdf->SetX(110);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(50,4,'RFC PROVEEDOR CERTIFICADO:');
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(90,4,$arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@RfcProvCertif']);
	$pdf->Ln();
	if($y2<$pdf->GetY()){
		$y2 = $pdf->GetY();
	}

	$pdf->SetXY(10,$y2);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(10,4,"Codigo",1,0,"C",0);
	$pdf->Cell(10,4,"Cant.",1,0,"C",0);
	$pdf->Cell(20,4,"Clave Unidad",1,0,"C",0);
	$pdf->Cell(15,4,"Unidad",1,0,"C",0);
	$pdf->Cell(20,4,"Clave Producto",1,0,"C",0);
	$pdf->Cell(40,4,"Concepto / Descripción",1,0,"C",0);
	$pdf->Cell(15,4,"Valor Unit",1,0,"C",0);
	$pdf->Cell(15,4,"Tasa",1,0,"C",0);
	$pdf->Cell(15,4,"Factor",1,0,"C",0);
	$pdf->Cell(22.5,4,"Impuestos",1,0,"C",0);
	$pdf->Cell(15,4,"Importe",1,0,"C",0);
	$pdf->Ln();
	$y=$pdf->GetY();
	$pdf->SetFont('Arial','',8);
	$res2 = mysql_query("SELECT * FROM ".$tabla."mov WHERE plaza='".$plaza."' AND cvefact='".$cvefact."'");
	while($row2 = mysql_fetch_array($res2)){
		$row2['unidad'] = ($row2['claveunidadsat'] == 'E48') ? 'Servicio' : 'Pieza';
		$pdf->SetXY(10,$y);
		$pdf->Cell(10,4,'00001',1,0,"R",0);
		$pdf->Cell(10,4,$row2['cantidad'],1,0,"R",0);
		$pdf->Cell(20,4,$row2['claveunidadsat'],1,0,"C",0);
		$pdf->Cell(15,4,$row2['unidad'],1,0,"C",0);
		$pdf->Cell(20,4,$row2['claveprodsat'],1,0,"C",0);
		$y3=$pdf->GetY();
		if($y!=$y3) $y=$y3;
		$pdf->MultiCell(40,4,$row2['concepto'],1,"J",0);
		$y2=$pdf->GetY();
		$pdf->SetXY(125,$y);
		$pdf->Cell(15,4,$row2['precio'],1,0,"R",0);
		$pdf->Cell(15,4,0.1600,1,0,"R",0);
		$pdf->Cell(15,4,'Tasa',1,0,"C",0);
		$pdf->Cell(12.5,4,'002 IVA',1,0,"C",0);
		$pdf->Cell(10,4,$row2['importe_iva'],1,0,"R",0);
		$pdf->Cell(15,4,$row2['importe'],1,0,"R",0);
		$y=$y2;
	}
	$pdf->SetXY(10,$y);
	$pdf->Cell(157.5,4,"IMPORTE CON LETRA",'1',0,'C',0);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'SUBTOTAL:',1);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(20,4,$row['subtotal'],1,0,'R');
	$pdf->Ln();
	$pdf->Cell(157.5,4," ",'LR',0,'C',0);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'I.V.A. 16%:',1);
	$pdf->Cell(20,4,$row['iva'],1,0,'R');
	$pdf->Ln();
	$y=$pdf->GetY();
	$pdf->Cell(157.5,4,'','LR',0,'L',0);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(20,4,'TOTAL:',1);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(20,4,$row['total'],1,0,'R');
	$pdf->Ln();
	$pdf->Cell(157.5,24,'','LBR',0,'L',0);
	$pdf->Cell(40,24,'','LBR',0,'L',0);
	$pdf->Ln();
	$y2=$pdf->GetY();
	$pdf->SetXY(13,$y);
	if($row['forma_pago'] == 0){
		$pdf->MultiCell(157,4,numlet($row['total']).'
Metodo pago: PUE PAGO EN UNA SOLA EXHIBICION
Forma pago: '.$array_tipo_pago[$row['tipo_pago']].'
Condiciones: CONTADO
MONEDA: MXN');
	}
	else{
		$pdf->MultiCell(157,4,numlet($row['total']).'
Metodo pago: PPD PAGO EN PARCIALIDADES O DIFERIDO
Forma pago: '.$array_tipo_pago[$row['tipo_pago']].'
Condiciones: CONTADO
MONEDA: MXN');		
	}
	$pdf->SetX(13);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(157,4,'Este documento es una representación impresa de un CFDI');
	$pdf->Ln();
	$pdf->SetX(13);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(157,4,'*Efectos fiscales al pago, *Pago en una sola exhibición');
	$y2+=4;

	$pdf->SetXY(10,$y2);
	$tt=number_format($row['total'],6,".","");
	QRcode::png("?re=".$re."&rr=".$rr."&tt=".$tt."&id=".$row['uuid'],"../cfdi/comprobantes/barcode_".$row['plaza'].'_'.$row['cve'].".png","L",4,0);
	if(file_exists("../cfdi/comprobantes/barcode_".$row['plaza'].'_'.$row['cve'].".png")) $pdf->Image("../cfdi/comprobantes/barcode_".$row['plaza'].'_'.$row['cve'].".png",20,$y2,34,34);
	$pdf->SetXY(60,$y2);
	$pdf->Cell(26,4,"OBSERVACIONES:",0,0,'L',0);
	$pdf->Ln();
	$pdf->SetX(60);
	$pdf->MultiCell(130,4,$row['obs'],0,'L',0);
	
	$resTicket=mysql_query("SELECT * FROM cobro_engomado WHERE plaza='$plaza' AND LEFT(fecha,7)='".substr($row['fecha'],0,7)."' AND ".$campo."='$cvefact' AND estatus!='C'");
	while($rowTicket=mysql_fetch_array($resTicket)){
		$pdf->SetFont("Arial","",8);
		$pdf->SetX(60);
		$pdf->MultiCell(130,3,'Ticket: '.$rowTicket['cve'].' Fecha Venta: '.$rowTicket['fecha'].' Placa: '.$rowTicket['placa'],0,'L',0);
	}
	$pdf->SetXY(80, $y2+35);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(100,4,'Folio fiscal: '.$row['uuid']);
	$pdf->Ln();
	$pdf->SetX(80);
	$pdf->Cell(100,4,'SERIE DEL SELLO: '.$row['seriecertificado']);
	$pdf->Ln();
	$pdf->Cell(70,4,'No de Serie del Certificado del SAT:          ',0,0,'R');
	$pdf->Cell(100,4,$row['seriecertificadosat']);
	$pdf->Ln();
	$pdf->Cell(70,4,'Fecha y hora de certificación:          ',0,0,'R');
	$pdf->Cell(100,4,date('j/n/Y - H:i:s', strtotime($row['fechatimbre'])));
	$pdf->Ln();
	$pdf->Cell(162.5,4,"Sello digital del CFDI",0,0,"L");
	$pdf->Ln();
	$pdf->SetX(20);
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(180,3,$row['sellodocumento'],0,"C",0);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(162.5,4,"Sello del SAT",0,0,"L");
	$pdf->Ln();
	$pdf->SetX(20);
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(180,3,$row['sellotimbre'],0,"C",0);
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(162.5,4,"Cadena original del complemento de certificación digital del SAT",0,0,"L");
	$pdf->Ln();
	$pdf->SetX(20);
	$pdf->SetFont('Arial','',8);
	$pdf->MultiCell(180,3,$row['cadenaoriginal'],0,"C",0);
	if($row['estatus']=='C'){
		//$pdf->Ln();
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(162.5,4,"Folio de Cancelación",0,0,"L");
		$pdf->Ln();
		$pdf->SetX(20);
		$pdf->SetFont('Arial','',8);
		$pdf->MultiCell(180,3,$row['respuesta2'],0,"C",0);
	}
	
	if($mostrar==1){
		$pdf->Output();
	}
	else{
		if($row['estatus']=='C'){
			$pdf->Output("../cfdi/comprobantes/".$archivo."c_".$row['plaza']."_".$row['cve'].".pdf","F");
		}
		else{
			$pdf->Output("../cfdi/comprobantes/".$archivo."_".$row['plaza']."_".$row['cve'].".pdf","F");
		}
	}
	if(file_exists("../cfdi/comprobantes/barcode_".$row['plaza'].'_'.$row['cve'].".png")) unlink("../cfdi/comprobantes/barcode_".$row['plaza'].'_'.$row['cve'].".png");
}
?>