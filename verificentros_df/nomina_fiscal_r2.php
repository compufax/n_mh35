<?php
include ("main.php"); 

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsPuestos=mysql_query("SELECT * FROM puestos ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_puestos[$Puestos['cve']]=$Puestos['nombre'];
	$array_riesgopuestos[$Puestos['cve']]=$Puestos['riesgo'];
}

$array_departamento = array();

$rsPuestos=mysql_query("SELECT * FROM departamentos ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_departamento[$Puestos['cve']]=$Puestos['nombre'];
}

$rsDepto=mysql_query("SELECT * FROM areas");
while($Depto=mysql_fetch_array($rsDepto)){
	$array_localidad[$Depto['cve']]=$Depto['nombre'];
}

$array_plazanombre = array();

$rsPuestos=mysql_query("SELECT * FROM datosempresas ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_plazanombre[$Puestos['plaza']]=$Puestos['nombre'];
	$array_plazadomicilio[$Puestos['plaza']]=$Puestos['domicilio'];
	$array_plaza_regimen[$Puestos['plaza']]=$Puestos['registro_patronal'];
}

$_POST['cveempresa']=1;
$tipo_nomina = 3;
$lusa = 0;

$res = mysql_query("SELECT * FROM impuestos_imss ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$minimo = $row['smdf'];


if($_POST['cmd']==50){
	require_once('excel/Worksheet.php');
	require_once('excel/Workbook.php');
	function HeaderingExcel($filename) {
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$filename" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}		
	// HTTP headers
	HeaderingExcel('nomina.xls');
	$workbook = new Workbook("-");
	// Creating the first worksheet
	$worksheet1 =& $workbook->add_worksheet('Listado de Nomina Personal');
	$normal =& $workbook->add_format();
	$normal->set_align('left');
	$normal->set_align('vjustify');
	
	$res=mysql_query("SELECT cve,nombre,generada_fiscal,fecha_ini,fecha_fin,dias as dias_trabajados,factor FROM periodos_nomina WHERE empresa='".$_POST['cveempresa']."' AND cve='".$_POST['periodo']."'");
	$Periodo=mysql_fetch_array($res);
	$worksheet1->write_string(0,0,'Nomina Fiscal');
	$worksheet1->write_string(1,0,'Periodo: '.$Periodo['nombre'].' del '.$Periodo['fecha_ini'].' al '.$Periodo['fecha_fin']);
	
	$select= " SELECT a.nombre,a.plaza,a.nombre,a.puesto,a.rfc,a.imss,b.cve,b.totalpercepciones,b.totaldeducciones,a.salario_integrado as salario_base_personal, b.sal_diario,
			a.sdi as salario_integrado_personal, b.dias_tra, a.cobro_prestamo as monto_prestamo, a.cve as cvepersonal, b.uuid, b.cadenaoriginal,
			IF(a.infonavit='SI',a.monto_infonavit,0) as monto_infonavit, b.cancelacion_sat, b.registro_patronal, b.baja, b.honorarios, b.fechatimbre
			FROM personal as a 
			INNER JOIN personal_nomina as b ON (b.personal=a.cve AND b.periodo='".$_POST['periodo']."' AND b.tipo='1' AND b.eliminada!=1)
			WHERE 1";
	
	if($_POST['plaza']>0) $select.=" AND a.plaza='".$_POST['plaza']."'";
	if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
	if ($_POST['metodo_pago']!="all") $select.=" AND a.metodo_pago = '".$_POST['metodo_pago']."'";
	$rspersonal=mysql_query($select) or die(mysql_error());
	$totalRegistros = mysql_num_rows($rspersonal);
	$c=0;
	if(count($array_plaza)>1){ 
		$worksheet1->write_string(3,$c,'Plaza');$c++;
	}
	$worksheet1->write_string(3,$c,'Nombre');$c++;
	$worksheet1->write_string(3,$c,'Puesto');$c++;
	$worksheet1->write_string(3,$c,'R.F.C.');$c++;
	$worksheet1->write_string(3,$c,'N.S.S.');$c++;
	$worksheet1->write_string(3,$c,'Salario Diario');$c++;
	$worksheet1->write_string(3,$c,'Dias Trabajados');$c++;
	$array_percepciones=array();
	$resp=mysql_query("SELECT a.cve,a.nombre,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje FROM cat_percepciones a 
					WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=1 ORDER BY a.cve");
	while($rowp=mysql_fetch_array($resp)){
		$worksheet1->write_string(3,$c,$rowp['nombre']);$c++;
		$array_percepciones[$rowp['cve']]=$rowp['nombre'];
	}	
	$worksheet1->write_string(3,$c,'Total Percepciones');$c++;
	$array_deducciones=array();
	$resp=mysql_query("SELECT a.cve,a.nombre,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje FROM cat_deducciones a 
					WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=1 ORDER BY a.cve");
	while($rowp=mysql_fetch_array($resp)){
		$worksheet1->write_string(3,$c,$rowp['nombre']);$c++;
		$array_deducciones[$rowp['cve']]=$rowp['nombre'];
	}	
	$worksheet1->write_string(3,$c,'Total Deducciones');$c++;
	$worksheet1->write_string(3,$c,'Total a Pagar');$c++;
	$l=4;
	while($Personal=mysql_fetch_array($rspersonal)) {
		$c=0;
		if(count($array_plaza)>1){
			$worksheet1->write_string($l,$c,$array_plaza[$Personal['plaza']]);$c++;
		}
		$worksheet1->write_string($l,$c,$Personal['nombre']);$c++;
		$worksheet1->write_string($l,$c,$array_puestos[$Personal['puesto']]);$c++;
		$worksheet1->write_string($l,$c,$Personal['rfc'].$Personal['homoclave']);$c++;
		$worksheet1->write_string($l,$c,$Personal['imss']);$c++;
		$worksheet1->write_string($l,$c,$Personal['sal_diario']);$c++;
		$worksheet1->write_string($l,$c,$Personal['dias_tra']);$c++;
		$total_percepciones=0;
		foreach($array_percepciones as $k=>$v){
			$resp=mysql_query("SELECT total FROM personal_nomina_percepcion WHERE nomina = '".$Personal['cve']."' AND percepcion = '$k'");
			$rowp=mysql_fetch_array($resp);
			$worksheet1->write_string($l,$c,$rowp['total']);$c++;
			$total_percepciones+=$rowp['total'];
		}
		$worksheet1->write_string($l,$c,$total_percepciones);$c++;
		$total_deducciones=0;
		foreach($array_deducciones as $k=>$v){
			$resp=mysql_query("SELECT total FROM personal_nomina_deduccion WHERE nomina = '".$Personal['cve']."' AND deduccion = '$k'");
			$rowp=mysql_fetch_array($resp);
			$worksheet1->write_string($l,$c,$rowp['total']);$c++;
			$total_deducciones+=$rowp['total'];
		}
		$worksheet1->write_string($l,$c,$total_deducciones);$c++;
		$worksheet1->write_string($l,$c,$total_percepciones-$total_deducciones);$c++;
		$l++;
	}
	
	$workbook->close();
	exit();
}

if($_POST['cmd']==101){
	require_once("phpmailer/class.phpmailer.php");
	require_once('fpdf153/fpdf.php');
	require_once("numlet.php");	
	require_once("phpqrcode/phpqrcode.php");
	

	$rsPuestos=mysql_query("SELECT * FROM administradores ORDER BY nombre");
	while($Puestos=mysql_fetch_array($rsPuestos)){
		$array_administrativos[$Puestos['plaza']][$Puestos['cve']]=$Puestos['nombre'];
		$array_administrativospuestos[$Puestos['plaza']][$Puestos['cve']]=$array_puestos[$Puestos['puesto']];
	}
	if($_POST['reg']==1){
		foreach($_POST['cvepersonal'] as $personal){
			$select= " SELECT b.*,a.nombre,if(b.rfc='',a.rfc,b.rfc) as rfc,a.imss,a.puesto,a.folio as numpersonal,a.departamento,a.email,a.plaza as plazaempleado,a.metodo_pago FROM personal_nomina as b
			INNER JOIN personal as a ON (b.personal=a.cve)
			WHERE b.cve='".$personal."'";
			$rspersonal=mysql_query($select) or die(mysql_error());
			$pdf=new FPDF('P','mm','LETTER');
			$pdf->SetFillColor(240,240,240);
			$email='';
			while($Personal=mysql_fetch_array($rspersonal)){
				$email=$Personal['email'];
				$cvefact=$Personal['folio'];
				$resPeriodo = mysql_query("SELECT * FROM periodos_nomina WHERE cve='".$Personal['periodo']."'");
				$Periodo = mysql_fetch_array($resPeriodo);
				$rsPlaza = mysql_query("SELECT CONCAT(a.numero,' ',a.nombre) as nomplaza,b.* FROM plazas a INNER JOIN datosempresas b on a.cve=b.plaza WHERE a.cve = '".$Personal['plazatimbro']."'");
				$Plaza = mysql_fetch_array($rsPlaza);
				$Plaza['registro_patronal']=$Personal['registro_patronal'];
				$Plaza['calle']=$Personal['calle'];
				$Plaza['numexterior']=$Personal['numexterior'];
				$Plaza['numinterior']=$Personal['numinterior'];
				$Plaza['colonia']=$Personal['colonia'];
				$Plaza['municipio']=$Personal['municipio'];
				$Plaza['estado']=$Personal['estado'];
				$Plaza['codigopostal']=$Personal['codigopostal'];
				/*if($Empresa['mismadireccion']==1){
					$Plaza['registro_patronal']=$Empresa['regimen'];
					$Plaza['calle']=$Empresa['calle'];
					$Plaza['numexterior']=$Empresa['numexterior'];
					$Plaza['numinterior']=$Empresa['numinterior'];
					$Plaza['colonia']=$Empresa['colonia'];
					$Plaza['municipio']=$Empresa['municipio'];
					$Plaza['estado']=$Empresa['estado'];
					$Plaza['codigo_postal']=$Empresa['codigo_postal'];
				}*/
				$pdf->AddPage('P');
				$pdf->SetFont('Arial','B',12);
				if($Personal['cancelacion_sat']!=''){
					if(file_exists("images/cancelado.jpg")) $pdf->Image("images/cancelado.jpg",10,45,190,200);
				}
				$pdf->Cell(190,5,$Empresa['nombre'],0,0,'C');
				$pdf->Ln();
				$re=$Empresa['rfc'];
				$rr=$Personal['rfc'].$Personal['homoclave'];
				$pdf->SetFont('Arial','B',10);
				if($Plaza['numinterior']!="") $Plaza['numexterior'].' - '.$Plaza['numinterior'];
				$pdf->MultiCell(190,5,'PLAZA: '.$Plaza['nomplaza'].'  
RFC: '.$Empresa['rfc'].' 
NRP: '.$Plaza['registro_patronal'].'
'.$Plaza['calle'].' '.$Plaza['numexterior'].', '.$Plaza['colonia'],0,'J');     
				$pdf->Cell(190,5,$Plaza['municipio'].', '.$Plaza['estado'].', C.P. '.$Plaza['codigo_postal'],'B',0,'L');
				$pdf->Ln();
				//$pdf->SetFont('Arial','B',12);
				//$pdf->Cell(190,5,"Recibo de Nomina",0,0,'C');
				//$pdf->Ln();
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(190,5,$Personal['numpersonal'].' '.$Personal['nombre'],0,0,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,"RFC: ".$Personal['rfc'].$Personal['homoclave'],0,0,'L');
				$pdf->Cell(60,5,"Dias Trabajados: ".$Personal['dias_tra'],0,0,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,"R.IMSS: ".$Personal['imss'],0,0,'L');
				$pdf->Cell(60,5,"Faltas: ".$Personal['faltas'],0,0,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,"Depto: ".$array_departamento[$Personal['departamento']],0,0,'L');
				$pdf->Cell(60,5,"Periodo del: ".$Periodo['fecha_ini'],0,0,'');
				$pdf->Ln();
				$pdf->Cell(130,5,"Puesto: ".$array_puestos[$Personal['puesto']],'B',0,'L');
				$pdf->Cell(60,5,"         al: ".$Periodo['fecha_fin'],'B',0,'L');
				$pdf->Ln();
				$pdf->SetFont('Arial','B',10);
				$pdf->Cell(120,4,"");
				$pdf->Cell(35,4,"Percepciones",0,0,'R');
				$pdf->Cell(35,4,"Deducciones",0,0,'R');
				$pdf->Ln();
				$pdf->SetFont('Arial','',10);
				$res1=mysql_query("SELECT b.nombre, a.total FROM personal_nomina_percepcion a INNER JOIN cat_percepciones b on a.percepcion=b.cve WHERE a.nomina='".$Personal['cve']."' AND a.total>0");
				$res2=mysql_query("SELECT b.nombre, a.total FROM personal_nomina_deduccion a INNER JOIN cat_deducciones b on a.deduccion=b.cve WHERE a.nomina='".$Personal['cve']."' AND a.total>0");
				while($row1=mysql_fetch_array($res1)){
					$pdf->Cell(120,4,$row1['nombre'],0,0,'L');$pdf->Cell(35,4,number_format($row1['total'],2),0,0,'R');$pdf->Cell(35,4,"",0,0,'R');$pdf->Ln();
				}
				while($row2=mysql_fetch_array($res2)){
					$pdf->Cell(120,4,$row2['nombre'],0,0,'L');$pdf->Cell(35,4,"",0,0,'R');$pdf->Cell(35,4,number_format($row2['total'],2),0,0,'R');$pdf->Ln();
				}
		
				if($Personal['cancelacion_sat']!=""){
					$pdf->SetXY(10,115);
				}
				else{
					$pdf->SetXY(10,120);
				}
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(120,4,"TOTALES ",'T',0,'L');$pdf->Cell(35,4,number_format($Personal['totalpercepciones'],2),'T',0,'R');$pdf->Cell(35,4,number_format($Personal['totaldeducciones'],2),'T',0,'R');
				$pdf->Ln();
				$pdf->Cell(120,4,"Neto Pagado ",0,0,'L');$pdf->Cell(35,4,"",0,0,'R');$pdf->Cell(35,4,number_format($Personal['totalpercepciones']-$Personal['totaldeducciones'],2),0,0,'R');
				$pdf->Ln(10);
				$pdf->SetFont('Arial','B',12);
				$pdf->Cell(120,5,"Total a Pagar ",0,0,'L');$pdf->Cell(35,5,"",0,0,'R');$pdf->Cell(35,5,number_format($Personal['totalpercepciones']-$Personal['totaldeducciones'],2),0,0,'R');
				$tt=number_format($Personal['totalpercepciones']-$Personal['totaldeducciones'],6,".","");
				$pdf->Ln(10);
				$pdf->SetFont('Arial','',10);
				$pdf->MultiCell(190,4,'Hago constar que con este pago me ha sido liquidado totalmente mi salario y prestaciones de ley que a la fecha tengo derecho',0,'J');
				$pdf->Ln();
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(190,4,"FIRMA:");
				$pdf->Ln();
				$pdf->Ln();
				if($Personal['cancelacion_sat']!=""){
					$res1=mysql_query("SELECT * FROM historial_timbrado WHERE nomina = '".$Personal['cve']."' ORDER BY cve DESC LIMIT 1");
					$row1=mysql_fetch_array($res1);
					$Personal['uuid'] = $row1['uuid'];
				}
				$pdf->Cell(98.5,4,"UUID",1,0,"C",1);
				$pdf->Cell(98.5,4,"FOLIO",1,0,"C",1);
				$pdf->Ln();
				$pdf->Cell(98.5,4,$Personal['uuid'],0,0,'C');
				$pdf->Cell(98.8,4,$Personal['folio'],0,0,'C');
				$pdf->Ln();
				$pdf->Cell(66,4,"FECHA EMISION",1,0,"C",1);
				$pdf->Cell(65,4,"FECHA TIMBRE",1,0,"C",1);
				$pdf->Cell(66,4,"CERTIFICADO EMISOR",1,0,"C",1);
				$pdf->Ln();
				$pdf->Cell(66,4,$Personal['fechatimbre'],0,0,'C');
				$pdf->Cell(65,4,$Personal['fechatimbre'],0,0,'C');
				$pdf->Cell(66,4,$Personal['seriecertificado'],0,0,'C');
				$pdf->Ln();
				$pdf->Cell(66,4,"CERTIFICADO SAT",1,0,"C",1);
				$pdf->Cell(65,4,"METODO DE PAGO",1,0,"C",1);
				$pdf->Cell(66,4,"FORMA DE PAGO",1,0,"C",1);
				$pdf->Ln();
				$pdf->Cell(66,4,$Personal['seriecertificadosat'],0,0,'C');
				$pdf->Cell(65,4,$array_tipo_pago[$Personal['metodo_pago']],0,0,'C');
				$pdf->Cell(66,4,"UNA SOLA EXHIBICION",0,0,'C');
				$pdf->Ln();
				$y=$pdf->GetY();
				$pdf->SetX(45);
				$pdf->Cell(162.5,6.5,"CADENA ORIGINAL",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->MultiCell(162.5,4,$Personal['cadenaoriginal'],0,"J",0);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->Cell(162.5,6.5,"SELLO DIGITAL EMISOR",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->MultiCell(162.5,4,$Personal['sellodocumento'],0,"J",0);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->Cell(162.5,6.5,"SELLO DIGITAL SAT",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->MultiCell(162.5,4,$Personal['sellotimbre'],0,"J",0);
				if($Personal['cancelacion_sat']!=""){
					$pdf->SetX(45);
					$pdf->Cell(162.5,6.5,"CODIGO CANCELACION",1,0,"C",1);
					$pdf->Ln();
					$pdf->SetX(45);
					$cancelacion = explode("|",$Personal['cancelacion_sat']);
					$pdf->MultiCell(162.5,4,$cancelacion[1],0,"J",0);
				}
				if(!file_exists("cfdi/comprobantes/barcode_".$Personal['empresa'].'_'.$Personal['cve'].".png")) QRcode::png("?re=".$re."&rr=".$rr."&tt=".$tt."&id=".$Personal['uuid'],"cfdi/comprobantes/barcode_".$Personal['empresa'].'_'.$Personal['cve'].".png","L",4,0);
				if(file_exists("cfdi/comprobantes/barcode_".$Personal['empresa'].'_'.$Personal['cve'].".png")) $pdf->Image("cfdi/comprobantes/barcode_".$Personal['empresa'].'_'.$Personal['cve'].".png",10,$y,34,34);
			}
			$pdf->Output("cfdi/comprobantes/nomina_".$_POST['cveempresa']."_".$personal.".pdf","F");
			if($email != ''){
				$mail = new PHPMailer();
				$mail->Host = "localhost";
				$mail->From = "sunomina@sunomina.com";
				$mail->FromName = "Su Nomina";
				$mail->Subject = "Recibo de Nomina ".$cvefact;
				$mail->Body = "Recibo de Nomina ".$cvefact;
				//$mail->AddAddress(trim($emailenvio));
				$correos = explode(",",trim($email));
				foreach($correos as $correo)
					$mail->AddAddress(trim($correo));
				$mail->AddAttachment("cfdi/comprobantes/nomina_".$_POST['cveempresa']."_".$personal.".pdf", "Recibo de Nomina ".$cvefact.".pdf");
				$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$personal.".xml", "Recibo de Nomina ".$cvefact.".xml");
				$mail->Send();
				unlink("cfdi/comprobantes/nomina_".$_POST['cveempresa']."_".$personal.".pdf");
			}
		}
	}
	else{
		$cveperiodo=$_POST['periodo'];
		$periodo=0;
		$email=$Empresa['email'];
		$pdf=new FPDF('P','mm','LETTER');
		$pdf->SetFillColor(240,240,240);
		$zip = new ZipArchive();
		if($zip->open("cfdi/nominas_".$_POST['cveempresa']."_".$cveperiodo.".zip",ZipArchive::CREATE));
		
		foreach($_POST['cvepersonal'] as $personal){
			$select= " SELECT b.*,a.nombre,if(b.rfc='',a.rfc,b.rfc) as rfc,a.imss,a.puesto,a.folio as numpersonal,a.departamento,a.email,a.plaza as plazaempleado,a.metodo_pago FROM personal_nomina as b
			INNER JOIN personal as a ON (b.personal=a.cve)
			WHERE b.cve='".$personal."'";
			$rspersonal=mysql_query($select) or die(mysql_error());
			while($Personal=mysql_fetch_array($rspersonal)){
				$resPeriodo = mysql_query("SELECT * FROM periodos_nomina WHERE cve='".$Personal['periodo']."'");
				$Periodo = mysql_fetch_array($resPeriodo);
				$periodo = ' DEL '.$Periodo['fecha_ini'].' AL '.$Periodo['fecha_fin'];
				$rsPlaza = mysql_query("SELECT * FROM plazas WHERE cve = '".$Personal['plazatimbro']."'");
				$Plaza = mysql_fetch_array($rsPlaza);
				$Plaza['registro_patronal']=$Personal['registro_patronal'];
				$Plaza['calle']=$Personal['calle'];
				$Plaza['numexterior']=$Personal['numexterior'];
				$Plaza['numinterior']=$Personal['numinterior'];
				$Plaza['colonia']=$Personal['colonia'];
				$Plaza['municipio']=$Personal['municipio'];
				$Plaza['estado']=$Personal['estado'];
				$Plaza['codigopostal']=$Personal['codigopostal'];
				/*if($Empresa['mismadireccion']==1){
					$Plaza['registro_patronal']=$Empresa['regimen'];
					$Plaza['calle']=$Empresa['calle'];
					$Plaza['numexterior']=$Empresa['numexterior'];
					$Plaza['numinterior']=$Empresa['numinterior'];
					$Plaza['colonia']=$Empresa['colonia'];
					$Plaza['municipio']=$Empresa['municipio'];
					$Plaza['estado']=$Empresa['estado'];
					$Plaza['codigo_postal']=$Empresa['codigo_postal'];
				}*/
				$pdf->AddPage('P');
				$pdf->SetFont('Arial','B',12);
				if($Personal['cancelacion_sat']!=''){
					if(file_exists("images/cancelado.jpg")) $pdf->Image("images/cancelado.jpg",10,45,190,200);
				}
				$pdf->Cell(190,5,$Empresa['nombre'],0,0,'C');
				$pdf->Ln();
				$re=$Empresa['rfc'];
				$rr=$Personal['rfc'].$Personal['homoclave'];
				$pdf->SetFont('Arial','B',10);
				if($Plaza['numinterior']!="") $Plaza['numexterior'].' - '.$Plaza['numinterior'];
				$pdf->MultiCell(190,5,'PLAZA: '.$Plaza['nombre'].'  
RFC: '.$Empresa['rfc'].' 
NRP: '.$Plaza['registro_patronal'].'
'.$Plaza['calle'].' '.$Plaza['numexterior'].', '.$Plaza['colonia'],0,'J');   
				$pdf->Cell(190,5,$Plaza['municipio'].', '.$Plaza['estado'].', C.P. '.$Plaza['codigo_postal'],'B',0,'L');
				$pdf->Ln();
				//$pdf->SetFont('Arial','B',12);
				//$pdf->Cell(190,5,"Recibo de Nomina",0,0,'C');
				//$pdf->Ln();
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(190,5,$Personal['numpersonal'].' '.$Personal['nombre'],0,0,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,"RFC: ".$Personal['rfc'].$Personal['homoclave'],0,0,'L');
				$pdf->Cell(60,5,"Dias Trabajados: ".$Personal['dias_tra'],0,0,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,"R.IMSS: ".$Personal['imss'],0,0,'L');
				$pdf->Cell(60,5,"Faltas: ".$Personal['faltas'],0,0,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,"Depto: ".$array_departamento[$Personal['departamento']],0,0,'L');
				$pdf->Cell(60,5,"Periodo del: ".$Periodo['fecha_ini'],0,0,'');
				$pdf->Ln();
				$pdf->Cell(130,5,"Puesto: ".$array_puestos[$Personal['puesto']],'B',0,'L');
				$pdf->Cell(60,5,"         al: ".$Periodo['fecha_fin'],'B',0,'L');
				$pdf->Ln();
				$pdf->SetFont('Arial','B',10);
				$pdf->Cell(120,4,"");
				$pdf->Cell(35,4,"Percepciones",0,0,'R');
				$pdf->Cell(35,4,"Deducciones",0,0,'R');
				$pdf->Ln();
				$pdf->SetFont('Arial','',10);
				$res1=mysql_query("SELECT b.nombre, a.total FROM personal_nomina_percepcion a INNER JOIN cat_percepciones b on a.percepcion=b.cve WHERE a.nomina='".$Personal['cve']."' AND a.total>0");
				$res2=mysql_query("SELECT b.nombre, a.total FROM personal_nomina_deduccion a INNER JOIN cat_deducciones b on a.deduccion=b.cve WHERE a.nomina='".$Personal['cve']."' AND a.total>0");
				while($row1=mysql_fetch_array($res1)){
					$pdf->Cell(120,4,$row1['nombre'],0,0,'L');$pdf->Cell(35,4,number_format($row1['total'],2),0,0,'R');$pdf->Cell(35,4,"",0,0,'R');$pdf->Ln();
				}
				while($row2=mysql_fetch_array($res2)){
					$pdf->Cell(120,4,$row2['nombre'],0,0,'L');$pdf->Cell(35,4,"",0,0,'R');$pdf->Cell(35,4,number_format($row2['total'],2),0,0,'R');$pdf->Ln();
				}
		
				if($Personal['cancelacion_sat']!=""){
					$pdf->SetXY(10,115);
				}
				else{
					$pdf->SetXY(10,120);
				}
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(120,4,"TOTALES ",'T',0,'L');$pdf->Cell(35,4,number_format($Personal['totalpercepciones'],2),'T',0,'R');$pdf->Cell(35,4,number_format($Personal['totaldeducciones'],2),'T',0,'R');
				$pdf->Ln();
				$pdf->Cell(120,4,"Neto Pagado ",0,0,'L');$pdf->Cell(35,4,"",0,0,'R');$pdf->Cell(35,4,number_format($Personal['totalpercepciones']-$Personal['totaldeducciones'],2),0,0,'R');
				$pdf->Ln(10);
				$pdf->SetFont('Arial','B',12);
				$pdf->Cell(120,5,"Total a Pagar ",0,0,'L');$pdf->Cell(35,5,"",0,0,'R');$pdf->Cell(35,5,number_format($Personal['totalpercepciones']-$Personal['totaldeducciones'],2),0,0,'R');
				$tt=number_format($Personal['totalpercepciones']-$Personal['totaldeducciones'],6,".","");
				$pdf->Ln(10);
				$pdf->SetFont('Arial','',10);
				$pdf->MultiCell(190,4,'Hago constar que con este pago me ha sido liquidado totalmente mi salario y prestaciones de ley que a la fecha tengo derecho',0,'J');
				$pdf->Ln();
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(190,4,"FIRMA:");
				$pdf->Ln();
				$pdf->Ln();
				if($Personal['cancelacion_sat']!=""){
					$res1=mysql_query("SELECT * FROM historial_timbrado WHERE nomina = '".$Personal['cve']."' ORDER BY cve DESC LIMIT 1");
					$row1=mysql_fetch_array($res1);
					$Personal['uuid'] = $row1['uuid'];
				}
				$pdf->Cell(98.5,4,"UUID",1,0,"C",1);
				$pdf->Cell(98.5,4,"FOLIO",1,0,"C",1);
				$pdf->Ln();
				$pdf->Cell(98.5,4,$Personal['uuid'],0,0,'C');
				$pdf->Cell(98.8,4,$Personal['folio'],0,0,'C');
				$pdf->Ln();
				$pdf->Cell(66,4,"FECHA EMISION",1,0,"C",1);
				$pdf->Cell(65,4,"FECHA TIMBRE",1,0,"C",1);
				$pdf->Cell(66,4,"CERTIFICADO EMISOR",1,0,"C",1);
				$pdf->Ln();
				$pdf->Cell(66,4,$Personal['fechatimbre'],0,0,'C');
				$pdf->Cell(65,4,$Personal['fechatimbre'],0,0,'C');
				$pdf->Cell(66,4,$Personal['seriecertificado'],0,0,'C');
				$pdf->Ln();
				$pdf->Cell(66,4,"CERTIFICADO SAT",1,0,"C",1);
				$pdf->Cell(65,4,"METODO DE PAGO",1,0,"C",1);
				$pdf->Cell(66,4,"FORMA DE PAGO",1,0,"C",1);
				$pdf->Ln();
				$pdf->Cell(66,4,$Personal['seriecertificadosat'],0,0,'C');
				$pdf->Cell(65,4,$array_tipo_pago[$Personal['metodo_pago']],0,0,'C');
				$pdf->Cell(66,4,"UNA SOLA EXHIBICION",0,0,'C');
				$pdf->Ln();
				$y=$pdf->GetY();
				$pdf->SetX(45);
				$pdf->Cell(162.5,6.5,"CADENA ORIGINAL",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->MultiCell(162.5,4,$Personal['cadenaoriginal'],0,"J",0);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->Cell(162.5,6.5,"SELLO DIGITAL EMISOR",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->MultiCell(162.5,4,$Personal['sellodocumento'],0,"J",0);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->Cell(162.5,6.5,"SELLO DIGITAL SAT",1,0,"C",1);
				$pdf->Ln();
				$pdf->SetX(45);
				$pdf->MultiCell(162.5,4,$Personal['sellotimbre'],0,"J",0);
				if($Personal['cancelacion_sat']!=""){
					$pdf->SetX(45);
					$pdf->Cell(162.5,6.5,"CODIGO CANCELACION",1,0,"C",1);
					$pdf->Ln();
					$pdf->SetX(45);
					$cancelacion = explode("|",$Personal['cancelacion_sat']);
					$pdf->MultiCell(162.5,4,$cancelacion[1],0,"J",0);
				}
				if(!file_exists("cfdi/comprobantes/barcode_".$Personal['empresa'].'_'.$Personal['cve'].".png")) QRcode::png("?re=".$re."&rr=".$rr."&tt=".$tt."&id=".$Personal['uuid'],"cfdi/comprobantes/barcode_".$Personal['empresa'].'_'.$Personal['cve'].".png","L",4,0);
				if(file_exists("cfdi/comprobantes/barcode_".$Personal['empresa'].'_'.$Personal['cve'].".png")) $pdf->Image("cfdi/comprobantes/barcode_".$Personal['empresa'].'_'.$Personal['cve'].".png",10,$y,34,34);
			}
			$zip->addFile("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$personal.".xml","recibo_nomina_".$_POST['cveempresa']."_".$personal.".xml");
		}
		$zip->close();
		$pdf->Output("cfdi/nominas_".$_POST['cveempresa']."_".$cveperiodo.".pdf","F");
	
		if($email != ''){
			$mail = new PHPMailer();
			$mail->Host = "localhost";
			$mail->From = "sunomina@sunomina.com";
			$mail->FromName = "Su Nomina";
			$mail->Subject = "Recibos de Nomina ".$periodo;
			$mail->Body = "Recibos de Nomina ".$periodo;
			//$mail->AddAddress(trim($emailenvio));
			$correos = explode(",",trim($email));
			foreach($correos as $correo)
				$mail->AddAddress(trim($correo));
			$mail->AddAttachment("cfdi/nominas_".$_POST['cveempresa']."_".$cveperiodo.".pdf", "Recibos de Nomina.pdf");
			$mail->AddAttachment("cfdi/nominas_".$_POST['cveempresa']."_".$cveperiodo.".zip", "Recibos de Nomina.zip");
			$mail->Send();
			unlink("cfdi/nominas_".$_POST['cveempresa']."_".$cveperiodo.".pdf");
			unlink("cfdi/nominas_".$_POST['cveempresa']."_".$cveperiodo.".zip");
		}
	
	}

	$_POST['cmd']=0;
}

if($_POST['cmd']==7){
	require_once("nusoap/nusoap.php");
	foreach($_POST['cvepersonal'] as $personal){
		$res=mysql_query("SELECT * FROM personal_nomina WHERE cve='$personal' AND tipo='1' AND uuid!=''");
		if($row=mysql_fetch_array($res)){
			$Empresa = mysql_fetch_array(mysql_query("SELECT * FROM datosempresas WHERE plaza='".$row['plazatimbro']."'"));
			$oSoapClient = new nusoap_client("http://servicios.solucionesfe.com/wscfdi2013.php?wsdl",true);		
			$err = $oSoapClient->getError();
			if($err!="")
				echo "error1:".$err;
			else{
				//print_r($documento);
				$oSoapClient->timeout = 300;
				$oSoapClient->response_timeout = 300;
				$respuesta = $oSoapClient->call("cancelar", array ('id' => $Empresa['idplazanomina'],'rfcemisor' => $Empresa['rfc'],'idcertificado' => $Empresa['idcertificadonomina'],'uuid' => $row['uuid'], 'usuario' => $Empresa['usuarionomina'],'password' => $Empresa['passnomina']));
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
					}
					else{
						$strmsg='';
						if($respuesta['resultado']){
							mysql_query("UPDATE personal_nomina SET cancelacion_sat='".$respuesta['mensaje']."',uuid='' WHERE cve='".$row['cve']."'");
							mysql_query("UPDATE historial_timbrado SET cancelacion_sat='".$respuesta['mensaje']."',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE nomina='".$row['cve']."' AND uuid = '".$row['uuid']."'");
							/*generaFacturaPdf($_POST['cveempresa'],$cvefact);
							if($emailenvio!=""){
								$mail = new PHPMailer();
								$mail->Host = "localhost";
								$mail->From = "sunomina@sunomina.com";
								$mail->FromName = "SuNomina";
								$mail->Subject = "Cancelacion Timbre de Nomina ".$row['folio'];
								$mail->Body = "Cancelacion Factura ".$cvefact;
								//$mail->AddAddress(trim($emailenvio));
								$correos = explode(",",trim($emailenvio));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("cfdi/comprobantes/facturac_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
								$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
								$mail->Send();
							}	
							if($rowempresa['email']!=""){
								$mail = new PHPMailer();
								$mail->Host = "localhost";
								$mail->From = "hoyfactura@hoyfactura.com";
								$mail->FromName = "MiFactura";
								$mail->Subject = "Cancelacion Factura ".$cvefact;
								$mail->Body = "Cancelacion Factura ".$cvefact;
								//$mail->AddAddress(trim($rowempresa['email']));
								$correos = explode(",",trim($rowempresa['email']));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("cfdi/comprobantes/facturac_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
								$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
								$mail->Send();
							}*/
						}
						else
							$strmsg=$respuesta['mensaje'];
						//print_r($respuesta);	
						echo $strmsg;
					}
				}
			}
		}
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==6){
	require_once("nusoap/nusoap.php");
	$res=mysql_query("SELECT * FROM periodos_nomina WHERE cve='".$_POST['periodo']."'");
	$Periodo=mysql_fetch_array($res);
	$nominas = array();
	$numnominas = 0;
	$plazas_nominas=array();
	foreach($_POST['cvepersonal'] as $personal){
		//$res=mysql_query("SELECT * FROM personal WHERE cve='".$personal."'");
		//$Personal=mysql_fetch_array($res);
		//$res=mysql_query("SELECT * FROM personal_nomina WHERE periodo='".$_POST['periodo']."' AND personal='$personal' AND tipo='1' AND uuid=''");
		$res=mysql_query("SELECT * FROM personal_nomina WHERE cve='$personal' AND tipo='1' AND uuid=''");
		while($row=mysql_fetch_array($res)){
			$resF=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM personal_nomina WHERE empresa='".$_POST['cveempresa']."' AND tipo=1");
			$rowF=mysql_fetch_array($resF);
			mysql_query("UPDATE personal_nomina SET folio='".$rowF[0]."' WHERE cve='".$personal."' AND folio=0");
			$res1=mysql_query("SELECT * FROM personal WHERE cve='".$row['personal']."'");
			$Personal=mysql_fetch_array($res1);
			$Empresa = mysql_fetch_array(mysql_query("SELECT * FROM datosempresas WHERE plaza='".$Personal['plaza']."'"));
			$Empresa['mismadireccion']=1;
			$Empresa['tipo_nomina']=3;
			$res2=mysql_query("SELECT cve,CONCAT(a.numero,' ',a.nombre) as nombre FROM plazas WHERE cve='".$Personal['plaza']."'");
			$Plaza=mysql_fetch_array($res2);
			$Plaza['riesgo']=1;
			if($Empresa['mismadireccion']==1){
				$Plaza['registro_patronal']=$Empresa['registro_patronal'];
				$Plaza['calle']=$Empresa['calle'];
				$Plaza['num_ext']=$Empresa['numexterior'];
				$Plaza['num_int']=$Empresa['numinterior'];
				$Plaza['colonia']=$Empresa['colonia'];
				$Plaza['municipio']=$Empresa['municipio'];
				$Plaza['estado']=$Empresa['estado'];
				$Plaza['codigopostal']=$Empresa['codigopostal'];
			}
			$monto=$row['totalpercepciones']-$row['total_deducciones'];
			if($monto>0){
				$nomina=array();
				$nomina['serie']='';
				$nomina['folio']=$row['folio'];
				//$nomina['fecha']=$Periodo['fecha_fin'];
				$nomina['formapago']='PAGO EN UNA SOLA EXHIBICION';
				$nomina['metodopago']=$array_tipo_pago[$Personal['metodo_pago']];
				$nomina['numerocuentapago']=$Empresa['cuenta_pago'];
				$nomina['empleado']=array(
					'numero'=>$Personal['cve'],
					'nombre'=>$Personal['nombre'],
					'domicilio'=>array(
						'calle'=>$Personal['calle'],
						'num_ext'=>$Personal['num_ext'],
						'num_int'=>$Personal['num_int'],
						'colonia'=>$Personal['colonia'],
						'localidad'=>$Personal['localidad'],
						'municipio'=>$Personal['municipio'],
						'estado'=>$Personal['estado'],
						'pais'=>'MÉXICO',
						'codigopostal'=>$Personal['codigopostal']
					),
					'rfc'=>$Personal['rfc'].$Personal['homoclave'],
					'curp'=>$Personal['curp'],
					'registropatronal'=>$Plaza['registro_patronal'],
					'claseriesgo'=>$Plaza['riesgo'],
					'nss'=>$Personal['imss'],
					'tiporegimen'=>$Personal['tipo_regimen'],
					'departamento'=>$array_departamento[$Personal['departamento']],
					'clabe'=>$Personal['clabe'],
					'banco'=>$Personal['banco'],
					'fechacontratacion'=>$Personal['fecha_imss'],
					'antiguedad'=>antiguedad($Personal['fecha_imss']),
					'puesto'=>$array_puestos[$Personal['puesto']],
					'tipocontrato'=>$array_tipo_contrato[$Personal['tipo_contrato']],
					'tipojornada'=>$array_tipo_jornada[$Personal['tipo_jornada']],
					'salarioBase'=>$Personal['salario_integrado'],
					'sdi'=>$Personal['sdi']
				);
				$nomina['lugarexpedicion']=array(
					'calle'=>$Plaza['calle'],
					'num_ext'=>$Plaza['num_ext'],
					'num_int'=>$Plaza['num_int'],
					'colonia'=>$Plaza['colonia'],
					'localidad'=>$Plaza['localidad'],
					'municipio'=>$Plaza['municipio'],
					'estado'=>$Plaza['estado'],
					'pais'=>'MÉXICO',
					'codigopostal'=>$Plaza['codigopostal']
				);
				$nomina['concepto']=$Periodo['nombre'];
				$nomina['netoapercibir']=$monto;
				$nomina['moneda']=0;
				$nomina['tipocambio']=1;
				$nomina['fechapago']=$Periodo['fecha_fin'];
				$nomina['fechainicial']=$Periodo['fecha_ini'];
				$nomina['fechafinal']=$Periodo['fecha_fin'];
				$nomina['diaspagados']=$row['dias_tra'];
				$nomina['periodicidadpago']=$array_tipo_nomina[$Empresa['tipo_nomina']];
				$percepciones=array();
				$resdetalle=mysql_query("SELECT b.nombre, b.cve, b.codigosat, IF(b.cve!=3,a.total,0) as gravado, IF(b.cve=3,a.total,0) as excento FROM cat_percepciones b INNER JOIN personal_nomina_percepcion a on a.percepcion=b.cve AND a.nomina='".$row['cve']."' WHERE b.tipo_nomina=1");
				while($Detalle=mysql_fetch_array($resdetalle)){
					if($Detalle['cve']==3){
						$resi = mysql_query("SELECT sum(total) FROM personal_nomina_percepcion WHERE nomina = '".$row['cve']."' AND percepcion!=3");
						$rowi=mysql_fetch_array($resi);
						$resf = mysql_query("SELECT total FROM personal_nomina_deduccion WHERE nomina = '".$row['cve']."' AND deduccion=1");
						$rowf=mysql_fetch_array($resf);
						$totalingreso = ($rowi[0]) - ($rowf[0]);
						$total = round(montosubsidio($totalingreso, $tipo_nomina),2);
						$Detalle['excento']=$total;
					}
					elseif($row['honorarios']==1){
						$Detalle['excento'] = $Detalle['gravado'];
						$Detalle['gravado'] = 0;
					}
					elseif($Detalle['cve']==32){
						$tope=$minimo*30;
						if($Detalle['gravado']>$tope && $row['baja']!=1){
							$Detalle['excento'] = $tope;
							$Detalle['gravado'] = round($Detalle['gravado']-$tope);
						}
						else{
							$Detalle['excento'] = $Detalle['gravado'];
							$Detalle['gravado'] = 0;
						}
					}
					$percepciones[] = array(
						'tipo'=>$Detalle['codigosat'],
						'clave'=>sprintf("%03s",$Detalle['cve']),
						'concepto'=>$Detalle['nombre'],
						'importegravado'=>$Detalle['gravado'],
						'importeexento'=>$Detalle['excento']);
				}
				$nomina['percepciones']=$percepciones;
				$deducciones=array();
				$resdetalle=mysql_query("SELECT b.nombre, b.cve, b.codigosat, IF(b.cve=1,a.total,0) as gravado, IF(b.cve!=1,a.total,0) as excento FROM cat_deducciones b INNER JOIN personal_nomina_deduccion a on a.deduccion=b.cve AND a.nomina='".$row['cve']."' WHERE b.tipo_nomina=1");
				while($Detalle=mysql_fetch_array($resdetalle)){
					if($Detalle['cve']==3){
						$resi = mysql_query("SELECT sum(total) FROM personal_nomina_percepcion WHERE nomina = '".$row['cve']."' AND percepcion!=3");
						$rowi=mysql_fetch_array($resi);
						$resf = mysql_query("SELECT total FROM personal_nomina_deduccion WHERE nomina = '".$row['cve']."' AND deduccion=1");
						$rowf=mysql_fetch_array($resf);
						$totalingreso = ($rowi[0]) - ($rowf[0]);
						$total = round(montoisr($totalingreso, $tipo_nomina),2);
						$Detalle['excento']=$total;
					}
					elseif($row['honorarios']==1 && $Detalle['gravado'] > 0){
						$Detalle['excento'] = $Detalle['gravado'];
						$Detalle['gravado'] = 0;
					}
					$deducciones[] = array(
						'tipo'=>$Detalle['codigosat'],
						'clave'=>sprintf("%03s",$Detalle['cve']),
						'concepto'=>$Detalle['nombre'],
						'importegravado'=>$Detalle['gravado'],
						'importeexento'=>$Detalle['excento']);
				}
				$nomina['deducciones']=$deducciones;
				//$nomina['incapacidades']=array();
				//$nomina['horasextras']=array();
				$nomina['observaciones']='';
				//$nominas['recibos'][$row['cve']] = $nomina;
				$nominas[$row['cve']] = $nomina;
				$plazas_nominas[$row['cve']] = $Personal['plaza'];
				$numnominas++;
				if($numnominas==1){
					//$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
					$oSoapClient = new nusoap_client("http://servicios.solucionesfe.com/wscfdi2013.php?wsdl",true);
					$err = $oSoapClient->getError();
					if($err!="")
						echo "error1:".$err;
					else{
						//print_r($documento);
						$oSoapClient->timeout = 300;
						$oSoapClient->response_timeout = 300;
						$respuestas = $oSoapClient->call("generar_recibos_nomina", array ('id' => $Empresa['idplazanomina'],'rfcemisor' => $Empresa['rfc'],'idcertificado' => $Empresa['idcertificadonomina'],'recibos' => $nominas, 'usuario' => $Empresa['usuarionomina'],'password' => $Empresa['passnomina']));
						//$respuestas = $oSoapClient->call("generar_recibos_nomina", array ('id' => 592,'rfcemisor' => $Empresa['id,'idcertificado' => 561,'recibos' => $nominas, 'usuario' => 'ANASTAC10','password' => '3232fd6ec779d4ec11938d7a52db72ab'));
						if ($oSoapClient->fault) {
							echo '<p><b>Fault: ';
							print_r($respuestas);
							echo '</b></p>';
							echo '<p><b>Request: <br>';
							echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
							echo '<p><b>Response: <br>';
							echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
							echo '<p><b>Debug: <br>';
							echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
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
							}
							else{		
								$indice = 0;
								foreach($nominas as $k=>$v){
									$strmsg='';
									$respuesta = $respuestas[$indice];
									if($respuesta['resultado']){
										mysql_query("UPDATE personal_nomina SET seriecertificado='".$respuesta['seriecertificado']."',
										sellodocumento='".$respuesta['sellodocumento']."',uuid='".$respuesta['uuid']."',seriecertificadosat='".$respuesta['seriecertificadosat']."',
										sellotimbre='".$respuesta['sellotimbre']."',cadenaoriginal='".$respuesta['cadenaoriginal']."',
										fechatimbre='".substr($respuesta['fechatimbre'],0,10)." ".substr($respuesta['fechatimbre'],-8)."',
										cancelacion_sat='',registro_patronal = '".$v['empleado']['registropatronal']."', 
										calle = '".$v['lugarexpedicion']['calle']."', numexterior = '".$v['lugarexpedicion']['num_ext']."',
										numinterior = '".$v['lugarexpedicion']['num_int']."',colonia = '".$v['lugarexpedicion']['colonia']."',
										localidad = '".$v['lugarexpedicion']['localidad']."',municipio = '".$v['lugarexpedicion']['municipio']."',
										estado = '".$v['lugarexpedicion']['estado']."',codigopostal = '".$v['lugarexpedicion']['codigopostal']."',
										plazatimbro='".$plazas_nominas[$k]."',rfc='".$v['rfc']."',rfc_empresa='".$Empresa['rfc']."'
										WHERE cve=".$k);
										mysql_query("INSERT historial_timbrado SET nomina='".$k."',seriecertificado='".$respuesta['seriecertificado']."',
										sellodocumento='".$respuesta['sellodocumento']."',uuid='".$respuesta['uuid']."',seriecertificadosat='".$respuesta['seriecertificadosat']."',
										sellotimbre='".$respuesta['sellotimbre']."',cadenaoriginal='".$respuesta['cadenaoriginal']."',
										fechatimbre='".substr($respuesta['fechatimbre'],0,10)." ".substr($respuesta['fechatimbre'],-8)."',
										usuario='".$_POST['cveusuario']."',registro_patronal = '".$v['empleado']['registropatronal']."', 
										calle = '".$v['lugarexpedicion']['calle']."', numexterior = '".$v['lugarexpedicion']['num_ext']."',
										numinterior = '".$v['lugarexpedicion']['num_int']."',colonia = '".$v['lugarexpedicion']['colonia']."',
										localidad = '".$v['lugarexpedicion']['localidad']."',municipio = '".$v['lugarexpedicion']['municipio']."',
										estado = '".$v['lugarexpedicion']['estado']."',codigopostal = '".$v['lugarexpedicion']['codigopostal']."',
										plazatimbro='".$plazas_nominas[$k]."',rfc='".$v['rfc']."',rfc_empresa='".$Empresa['rfc']."',
										fecha_ini='".$Periodo['fecha_ini']."',fecha_fin='".$Periodo['fecha_fin']."'");
										//Tomar la informacion de Retorno
										$dir="cfdi/comprobantes/";
										//$dir=dirname(realpath(getcwd()))."/solucionesfe_facturacion/cfdi/comprobantes/";
										//el zip siempre se deja fuera
										$dir2="cfdi/";
										//Leer el Archivo Zip
										$fileresult=$respuesta['archivos'];
										$strzipresponse=base64_decode($fileresult);
										$filename='cfdin_'.$_POST['cveempresa'].'_'.$k;
										file_put_contents($dir2.$filename.'.zip', $strzipresponse);
										$zip = new ZipArchive;
										if ($zip->open($dir2.$filename.'.zip') === TRUE){
											$strxml=$zip->getFromName('xml.xml');
											file_put_contents($dir.$filename.'.xml', $strxml);
											$strpdf=$zip->getFromName('formato.pdf');
											file_put_contents($dir.$filename.'.pdf', $strpdf);
											$zip->close();		
											/*generaFacturaPdf($_POST['cveempresa'],$cvefact);
											if($emailenvio!=""){
												$mail = new PHPMailer();
												$mail->Host = "localhost";
												$mail->From = "hoyfactura@hoyfactura.com";
												$mail->FromName = "MiFactura";
												$mail->Subject = "Factura ".$cvefact;
												$mail->Body = "Factura ".$cvefact;
												$mail->AddAddress(trim($emailenvio));
												$mail->AddAttachment("cfdi/comprobantes/factura_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
												$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
												$mail->Send();
											}	
											if($rowempresa['email']!=""){
												$mail = new PHPMailer();
												$mail->Host = "localhost";
												$mail->From = "hoyfactura@hoyfactura.com";
												$mail->FromName = "MiFactura";
												$mail->Subject = "Factura ".$cvefact;
												$mail->Body = "Factura ".$cvefact;
												//$mail->AddAddress(trim($rowempresa['email']));
												$correos = explode(",",trim($rowempresa['email']));
												foreach($correos as $correo)
													$mail->AddAddress(trim($correo));
												$mail->AddAttachment("cfdi/comprobantes/factura_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
												$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
												$mail->Send();
											}*/	
										}
										else{
											$strmsg='Error al descomprimir el archivo';
										}
									}
									else{
										$strmsg=$respuesta['mensaje'];
									}
									echo $strmsg;
									$indice++;
								}
							}
						}
					}
					$numnominas = 0;
					$nominas = array();
					$plazas_nominas = array();
				}
			}
		}
	}
	if($numnominas>0){
		//print_r($nominas);
		//$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
		$oSoapClient = new nusoap_client("http://servicios.solucionesfe.com/wscfdi2013.php?wsdl",true);
		$err = $oSoapClient->getError();
		if($err!="")
			echo "error1:".$err;
		else{
			//print_r($documento);
			$oSoapClient->timeout = 300;
			$oSoapClient->response_timeout = 300;
			$respuestas = $oSoapClient->call("generar_recibos_nomina", array ('id' => $Empresa['idplazanomina'],'rfcemisor' => $Empresa['rfc'],'idcertificado' => $Empresa['idcertificadonomina'],'recibos' => $nominas, 'usuario' => $Empresa['usuarionomina'],'password' => $Empresa['passnomina']));
			//$respuestas = $oSoapClient->call("generar_recibos_nomina", array ('id' => 592,'rfcemisor' => 'VIRA720921842','idcertificado' => 561,'recibos' => $nominas, 'usuario' => 'ANASTAC10','password' => '3232fd6ec779d4ec11938d7a52db72ab'));
			if ($oSoapClient->fault) {
				echo '<p><b>Fault: ';
				print_r($respuestas);
				echo '</b></p>';
				echo '<p><b>Request: <br>';
				echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
				echo '<p><b>Response: <br>';
				echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
				echo '<p><b>Debug: <br>';
				echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
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
				}
				else{		
					$indice = 0;
					foreach($nominas as $k=>$v){
						$respuesta = $respuestas[$indice];
						if($respuesta['resultado']){
							mysql_query("UPDATE personal_nomina SET seriecertificado='".$respuesta['seriecertificado']."',
							sellodocumento='".$respuesta['sellodocumento']."',uuid='".$respuesta['uuid']."',seriecertificadosat='".$respuesta['seriecertificadosat']."',
							sellotimbre='".$respuesta['sellotimbre']."',cadenaoriginal='".$respuesta['cadenaoriginal']."',
							fechatimbre='".substr($respuesta['fechatimbre'],0,10)." ".substr($respuesta['fechatimbre'],-8)."',
							cancelacion_sat='',registro_patronal = '".$v['empleado']['registropatronal']."', 
							calle = '".$v['lugarexpedicion']['calle']."', numexterior = '".$v['lugarexpedicion']['num_ext']."',
							numinterior = '".$v['lugarexpedicion']['num_int']."',colonia = '".$v['lugarexpedicion']['colonia']."',
							localidad = '".$v['lugarexpedicion']['localidad']."',municipio = '".$v['lugarexpedicion']['municipio']."',
							estado = '".$v['lugarexpedicion']['estado']."',codigopostal = '".$v['lugarexpedicion']['codigopostal']."',
							plazatimbro='".$plazas_nominas[$k]."',rfc='".$v['rfc']."',rfc_empresa='".$Empresa['rfc']."'
							WHERE cve=".$k);
							mysql_query("INSERT historial_timbrado SET nomina='".$k."',seriecertificado='".$respuesta['seriecertificado']."',
							sellodocumento='".$respuesta['sellodocumento']."',uuid='".$respuesta['uuid']."',seriecertificadosat='".$respuesta['seriecertificadosat']."',
							sellotimbre='".$respuesta['sellotimbre']."',cadenaoriginal='".$respuesta['cadenaoriginal']."',
							fechatimbre='".substr($respuesta['fechatimbre'],0,10)." ".substr($respuesta['fechatimbre'],-8)."',
							usuario='".$_POST['cveusuario']."',registro_patronal = '".$v['empleado']['registropatronal']."', 
							calle = '".$v['lugarexpedicion']['calle']."', numexterior = '".$v['lugarexpedicion']['num_ext']."',
							numinterior = '".$v['lugarexpedicion']['num_int']."',colonia = '".$v['lugarexpedicion']['colonia']."',
							localidad = '".$v['lugarexpedicion']['localidad']."',municipio = '".$v['lugarexpedicion']['municipio']."',
							estado = '".$v['lugarexpedicion']['estado']."',codigopostal = '".$v['lugarexpedicion']['codigopostal']."',
							plazatimbro='".$plazas_nominas[$k]."',rfc='".$v['rfc']."',rfc_empresa='".$Empresa['rfc']."',
							fecha_ini='".$Periodo['fecha_ini']."',fecha_fin='".$Periodo['fecha_fin']."'");
							//Tomar la informacion de Retorno
							$dir="cfdi/comprobantes/";
							//$dir=dirname(realpath(getcwd()))."/solucionesfe_facturacion/cfdi/comprobantes/";
							//el zip siempre se deja fuera
							$dir2="cfdi/";
							//Leer el Archivo Zip
							$fileresult=$respuesta['archivos'];
							$strzipresponse=base64_decode($fileresult);
							$filename='cfdi_'.$_POST['cveempresa'].'_'.$k;
							file_put_contents($dir2.$filename.'.zip', $strzipresponse);
							$zip = new ZipArchive;
							if ($zip->open($dir2.$filename.'.zip') === TRUE){
								$strxml=$zip->getFromName('xml.xml');
								file_put_contents($dir.$filename.'.xml', $strxml);
								$strpdf=$zip->getFromName('formato.pdf');
								file_put_contents($dir.$filename.'.pdf', $strpdf);
								$zip->close();		
								/*generaFacturaPdf($_POST['cveempresa'],$cvefact);
								if($emailenvio!=""){
									$mail = new PHPMailer();
									$mail->Host = "localhost";
									$mail->From = "hoyfactura@hoyfactura.com";
									$mail->FromName = "MiFactura";
									$mail->Subject = "Factura ".$cvefact;
									$mail->Body = "Factura ".$cvefact;
									$mail->AddAddress(trim($emailenvio));
									$mail->AddAttachment("cfdi/comprobantes/factura_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
									$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
									$mail->Send();
								}	
								if($rowempresa['email']!=""){
									$mail = new PHPMailer();
									$mail->Host = "localhost";
									$mail->From = "hoyfactura@hoyfactura.com";
									$mail->FromName = "MiFactura";
									$mail->Subject = "Factura ".$cvefact;
									$mail->Body = "Factura ".$cvefact;
									//$mail->AddAddress(trim($rowempresa['email']));
									$correos = explode(",",trim($rowempresa['email']));
									foreach($correos as $correo)
										$mail->AddAddress(trim($correo));
									$mail->AddAttachment("cfdi/comprobantes/factura_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
									$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
									$mail->Send();
								}*/	
							}
							else{
								$strmsg='Error al descomprimir el archivo';
							}
						}
						else{
							$strmsg=$respuesta['mensaje'];
						}
						echo $strmsg;
						$indice++;
					}
				}
			}
		}
	}
	$_POST['cmd']=0;
}




if($_POST['ajax']==1){
	$res=mysql_query("SELECT cve,nombre,generada_fiscal,fecha_ini,fecha_fin,dias as dias_trabajados,factor,aguinaldo,aguinaldo_excento FROM periodos_nomina WHERE empresa='".$_POST['cveempresa']."' AND cve='".$_POST['periodo']."'");
	$Periodo=mysql_fetch_array($res);
	if($Periodo['generada_fiscal']==1){
		$select= " SELECT a.nombre,a.plaza,a.nombre,a.puesto,a.rfc,a.imss,b.cve,b.totalpercepciones,b.totaldeducciones,a.salario_integrado as salario_base_personal, b.sal_diario,
			a.sdi as salario_integrado_personal, b.dias_tra, a.cobro_prestamo as monto_prestamo, a.cve as cvepersonal, b.uuid, b.cadenaoriginal,
			IF(a.infonavit='SI',a.monto_infonavit,0) as monto_infonavit, b.cancelacion_sat, b.registro_patronal, b.baja, b.honorarios, b.fechatimbre
			FROM personal as a 
			INNER JOIN personal_nomina as b ON (b.personal=a.cve AND b.periodo='".$_POST['periodo']."' AND b.tipo='1' AND b.eliminada!=1)
			INNER JOIN datosempresas c ON c.plaza = IF(b.uuid!='',b.plazatimbro,a.plaza)
			WHERE 1";
	}
	else{
		//mysql_query("DELETE FROM personal_nomina WHERE periodo='".$_POST['periodo']."'");
		$res1=mysql_query("SELECT a.* FROM personal_nomina a INNER JOIN personal b ON a.personal = b.cve WHERE a.tipo=1 AND a.periodo = '".$_POST['periodo']."' AND b.estatus NOT IN (1,4)");
		while($row1=mysql_fetch_array($res1)){
			mysql_query("DELETE FROM personal_nomina_percepcion WHERE nomina='".$row1['cve']."'");
			mysql_query("DELETE FROM personal_nomina_deduccion WHERE nomina='".$row1['cve']."'");
			mysql_query("DELETE FROM personal_nomina WHERE cve='".$row1['cve']."'");
		}
		if($Periodo['aguinaldo']==1){
			$select= " SELECT a.nombre,a.plaza,a.nombre,a.puesto,a.rfc,a.imss,b.cve,b.totalpercepciones,b.totaldeducciones,a.salario_integrado as salario_base_personal, b.sal_diario,
			a.sdi as salario_integrado_personal, b.dias_tra, a.cobro_prestamo as monto_prestamo, a.cve as cvepersonal, b.uuid, b.baja, 0 as honorarios,
			IF(a.infonavit='SI',a.monto_infonavit,0) as monto_infonavit, b.cancelacion_sat, b.cadenaoriginal, IF(a.fecha_imss>'".$Periodo['fecha_ini']."',ROUND(((DATEDIFF('".$Periodo['fecha_fin']."',a.fecha_imss)+1)*".$Periodo['dias_trabajados']."/(DATEDIFF('".$Periodo['fecha_fin']."','".$Periodo['fecha_ini']."')+1)),2),'".$Periodo['dias_trabajados']."') as dias_lab
			FROM personal as a 
			LEFT JOIN personal_nomina as b ON (b.personal=a.cve AND b.periodo='".$_POST['periodo']."' AND b.tipo='1')
			INNER JOIN datosempresas c ON c.plaza = a.plaza
			WHERE a.estatus IN (1,4) AND a.fecha_imss <='".$Periodo['fecha_fin']."'";
		}
		else{
			$select= " SELECT a.nombre,a.plaza,a.nombre,a.puesto,a.rfc,a.imss,b.cve,b.totalpercepciones,b.totaldeducciones,a.salario_integrado as salario_base_personal, b.sal_diario,
			a.sdi as salario_integrado_personal, b.dias_tra, a.cobro_prestamo as monto_prestamo, a.cve as cvepersonal, b.uuid, b.baja, 0 as honorarios,
			IF(a.infonavit='SI',a.monto_infonavit,0) as monto_infonavit, b.cancelacion_sat, b.cadenaoriginal, IF(a.fecha_imss>'".$Periodo['fecha_ini']."',(DATEDIFF('".$Periodo['fecha_fin']."',a.fecha_imss)+1),'".$Periodo['dias_trabajados']."') as dias_lab
			FROM personal as a 
			LEFT JOIN personal_nomina as b ON (b.personal=a.cve AND b.periodo='".$_POST['periodo']."' AND b.tipo='1')
			INNER JOIN datosempresas c ON c.plaza = a.plaza
			WHERE a.estatus IN (1,4) AND a.fecha_imss <='".$Periodo['fecha_fin']."'";
		}
	}
	if ($_POST['credencial']!="") { $select.=" AND a.cve='".$_POST['credencial']."'"; }
	if($_POST['plaza']>0) $select.=" AND a.plaza='".$_POST['plaza']."'";
	if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
	if ($_POST['metodo_pago']!="all") $select.=" AND a.metodo_pago = '".$_POST['metodo_pago']."'";
	if ($_POST['localidad']!="all") $select.=" AND c.localidad_id = '".$_POST['localidad']."'";
	if($_POST['mostrar']==1) $select.=" AND IFNULL(b.uuid,'')!=''";
	elseif($_POST['mostrar']==2) $select.=" AND IFNULL(b.uuid,'')=''";
	$select .= " ORDER BY a.nombre";
	$rspersonal=mysql_query($select) or die(mysql_error());
	$totalRegistros = mysql_num_rows($rspersonal);
	echo '<input type="hidden" name="cveperiodo" value="'.$_POST['periodo'].'">';
	if(mysql_num_rows($rspersonal)>0) 
	{
		echo '<h2>'.$Periodo['nombre'].'</h2>';
		echo '<input type="hidden" name="generada" id="generada" value="'.$Periodo['generada_fiscal'].'">';
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1"><thead>';
		echo '<tr bgcolor="#E9F2F8">';
		echo '<th rowspan="2">&nbsp;</th>';
		if($Periodo['generada_fiscal']==1){
			echo '<th rowspan="2"><input type="checkbox" name="selall" value="1" onClick="if(this.checked) $(\'.chkpersonal\').attr(\'checked\',\'checked\'); else $(\'.chkpersonal\').removeAttr(\'checked\');"></th>';
			echo '<th rowspan="2">Estatus Timbre</th><th rowspan="2">Fecha Timbre</th>';
		}
		if(count($array_plaza)>1) echo '<th rowspan="2">Plaza</th>';
		echo '
		<th rowspan="2">Registro Patronal</th><th rowspan="2"><a href="#" onclick="SortTable(2,\'S\',\'tabla1\');">Nombre</a></th><th rowspan="2">Puesto</th><th rowspan="2">R.F.C.</th><th rowspan="2">N.S.S.</th>';
		if($Periodo['aguinaldo']==1){
			$res1=mysql_query("SELECT * FROM cat_percepciones WHERE empresa IN (0,".$_POST['cveempresa'].") AND cve=32 AND tipo_nomina=1 ORDER BY cve");
			$cpercepciones = 4;
		}
		else{
			$res1=mysql_query("SELECT * FROM cat_percepciones WHERE empresa IN (0,".$_POST['cveempresa'].") AND cve!=32 AND tipo_nomina=1 ORDER BY cve");
			$cpercepciones = mysql_num_rows($res1)+6;
		}
		
		if($Periodo['aguinaldo']==1){
			$res2=mysql_query("SELECT * FROM cat_deducciones WHERE empresa IN (0,".$_POST['cveempresa'].") AND cve=3 AND tipo_nomina=1 ORDER BY cve");
			$cdeducciones = 2;
		}
		else{
			$res2=mysql_query("SELECT * FROM cat_deducciones WHERE empresa IN (0,".$_POST['cveempresa'].") AND tipo_nomina=1 ORDER BY cve");
			$cdeducciones = mysql_num_rows($res2)+2;
		}
		echo '<th colspan="'.$cpercepciones.'">Percepciones</th><th colspan="'.$cdeducciones.'">Deducciones</th><th rowspan="2">Total a Pagar</th></tr><tr bgcolor="#E9F2F8">';
		
		while($row1=mysql_fetch_array($res1)){
			if($row1['cve']==1){
				echo '<th>Sueldo Base</th><th>Dias Trabajados</th><th>Importe</th>';
			}
			elseif($row1['cve']==2){
				echo '<th>Domingos Trabajados</th><th>Prima Dominical</th>';
			}
			elseif($row1['cve']==4){
				echo '<th>Sueldo por Hora</th><th>H.E. Trabajados</th><th>Importe H.E.</th>';
			}
			else{
				echo '<th>'.$row1['nombre'].'</th>';
			}
		}
		echo '<th>Total Percepciones</th>';
		
		
		while($row2=mysql_fetch_array($res2)){
			if($row2['cve']==1){
				echo '<th>Dias Falto</th><th>Importe Faltas</th>';
			}
			else{
				echo '<th>'.$row2['nombre'].'</th>';
			}
		}
		echo '<th>Total Deducciones</th>';
				//<th>Salario Diario</th><th>Dias Trabajados</th><th>Dias que Falto</th><th>Importe</th><th>Total Percepciones</th><th>Total Deducciones</th>
		
		echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
		$gtotal=0;
		$timp=$tper=$tded=0;
		$i=0;
		$array_totales = array();
		while($Personal=mysql_fetch_array($rspersonal)) {
			$Empresa = mysql_fetch_array(mysql_query("SELECT * FROM datosempresas WHERE plaza='".$Personal['plaza']."'"));
			$Empresa['mismadireccion']=1;
			$Empresa['tipo_nomina']=3;
			$Empresa['factordiario']=1;
			$periodofactor = $Periodo['factor'];
			$creonomina=0;
			if($Personal['cve']>0){
				if($Periodo['generada_fiscal']!=1){
					mysql_query("UPDATE personal_nomina SET sal_diario='".$Personal['salario_base_personal']."',
					dias_tra='".$Personal['dias_lab']."',totaldeducciones=0,totalpercepciones=0,tipo=1, honorarios='".$Personal['honorarios']."' 
					WHERE cve='".$Personal['cve']."'");
					$Personal['sal_diario']=$Personal['salario_base_personal'];
					$Personal['dias_tra']=$Personal['dias_lab'];
					$creonomina = 1;
				}
			}
			else{
				mysql_query("INSERT personal_nomina SET empresa='".$_POST['cveempresa']."',periodo='".$_POST['periodo']."',
				personal='".$Personal['cvepersonal']."',sal_diario='".$Personal['salario_base_personal']."',baja=0, honorarios='".$Personal['honorarios']."',
				dias_tra='".$Personal['dias_lab']."',totaldeducciones=0,totalpercepciones=0,tipo=1,folio='$folioi'");
				$Personal['cve']=mysql_insert_id();
				$Personal['sal_diario']=$Personal['salario_base_personal'];
				$Personal['dias_tra']=$Personal['dias_lab'];
				$creonomina = 1;
			}
			rowb();
			echo '<td align=center width="5%"><a href="#" onClick="atcr(\'nomina_fiscal.php\',\'\',1,\''.$Personal['cve'].'\');"><img src="images/modificar.gif" border="0"></a>';
			if($_POST['cveusuario']==1 && $Periodo['generada_fiscal']==1 && $Personal['uuid']==""){
				echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de borrar el empleado\')){ atcr(\'nomina_fiscal.php\',\'\',5,\''.$Personal['cve'].'\');}"><img src="images/validono.gif" border="0"></a>';
			}
			echo '</td>';
			if($Periodo['generada_fiscal']!=1){
				$periodofactor=$Personal['dias_lab']*$Periodo['factor']/$Periodo['dias_trabajados'];
				if($creonomina==1){
					$total_percepciones=0;
					if($Periodo['aguinaldo']==1){
						$resp=mysql_query("SELECT a.empresa, a.cve,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje,
						b.cve as cvepercepcion, b.dias, b.montoxdia, b.total FROM cat_percepciones a 
						LEFT JOIN personal_nomina_percepcion b ON a.cve = b.percepcion AND b.nomina = '".$Personal['cve']."'
						WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.cve=32 AND a.tipo_nomina=1 ORDER BY a.cve");
					}
					else{
						$resp=mysql_query("SELECT a.empresa, a.cve,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje,
						b.cve as cvepercepcion, b.dias, b.montoxdia, b.total FROM cat_percepciones a 
						LEFT JOIN personal_nomina_percepcion b ON a.cve = b.percepcion AND b.nomina = '".$Personal['cve']."'
						WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.cve!=32 AND a.tipo_nomina=1 ORDER BY a.cve");
					}
					//WHERE a.cve IN (1,2,3)");
					$total_percepciones = 0;
					while($rowp=mysql_fetch_array($resp)){
						if($rowp['empresa']==0){
							if($rowp['cvepercepcion']<=0){
								$select = "INSERT personal_nomina_percepcion SET empresa='".$_POST['cveempresa']."',nomina='".$Personal['cve']."',
								percepcion='".$rowp['cve']."'";
							}
							else{
								$select = "UPDATE personal_nomina_percepcion SET empresa='".$_POST['cveempresa']."',nomina='".$Personal['cve']."',
								percepcion='".$rowp['cve']."'";
							}
						}
						if($rowp['cve']==1){
							$total = round($periodofactor*$Personal['sal_diario'],2);
							$select.=",dias='".$Personal['dias_tra']."',montoxdia='".$Personal['sal_diario']."',total='".$total."'";
						}
						elseif($rowp['cve']==32){
							$total = round($periodofactor*$Personal['sal_diario'],2);
							$select.=",dias='".$Personal['dias_tra']."',montoxdia='".$Personal['sal_diario']."',total='".$total."'";
						}
						elseif($rowp['cve']==2){
							$total = round($rowp['dias']*$Personal['sal_diario']*$Empresa['factordiario']*0.25,2);
							$select.=",dias='".$rowp['dias']."',montoxdia='".($Personal['sal_diario']*$Empresa['factordiario']*0.25)."',total='".$total."'";
						}
						elseif($rowp['cve']==3){
							$resi = mysql_query("SELECT sum(total) FROM personal_nomina_percepcion WHERE nomina = '".$Personal['cve']."' AND percepcion!=3");
							$rowi=mysql_fetch_array($resi);
							$resf = mysql_query("SELECT dias FROM personal_nomina_deduccion WHERE nomina = '".$Personal['cve']."' AND deduccion=1");
							$rowf=mysql_fetch_array($resf);
							$totalingreso = ($rowi[0]) - ($rowf[0]*$Personal['sal_diario']*$Empresa['factordiario']);
							$total = round(montosubsidio($totalingreso, $tipo_nomina)-montoisr($totalingreso, $tipo_nomina),2);
							if($total<0) $total = 0;
							$select.=",total='".$total."'";
						}
						elseif($rowp['cve']==4){
							$montoxdia=round($Personal['sal_diario']/8,2);
							$total = 0;
							$total = round($rowp['dias']*($montoxdia*3),2);
							/*if($rowp['dias']<=9){
								$total = round($rowp['dias']*($montoxdia*2),2);
							}
							else{
								$total = round(9*($montoxdia*2),2);
								$total+=round(($rowp['dias']-9)*($montoxdia*3),2);
							}*/
							$select.=",montoxdia='".$montoxdia."',total='".$total."'";
						}
						else{
							$total = $rowp['total'];
						}
						if($rowp['cvepercepcion']>0){
							$select.=" WHERE cve='".$rowp['cvepercepcion']."'";
						}
						//echo $select;
						if($rowp['cve']!=3 || $Personal['honorarios']!=1){
							if($rowp['empresa'] == 0) mysql_query($select);
							$total_percepciones+=$total;
						}
						elseif($rowp['cvepercepcion']>0){
							mysql_query("DELETE FROM personal_nomina_percepcion WHERE cve='".$rowp['cvepercepcion']."'");
						}
					}
					$total_deducciones=0;
					if($Periodo['aguinaldo_excento']!=1){
						if($Periodo['aguinaldo']==1){
							$resp=mysql_query("SELECT a.empresa, a.cve,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje,
							b.cve as cvededuccion, b.dias, b.montoxdia, b.total FROM cat_deducciones a 
							LEFT JOIN personal_nomina_deduccion b ON a.cve = b.deduccion AND b.nomina = '".$Personal['cve']."'
							WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.cve=3 AND a.tipo_nomina=1 ORDER BY a.cve");
						}
						else{
							$resp=mysql_query("SELECT a.empresa, a.cve,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje,
							b.cve as cvededuccion, b.dias, b.montoxdia, b.total FROM cat_deducciones a 
							LEFT JOIN personal_nomina_deduccion b ON a.cve = b.deduccion AND b.nomina = '".$Personal['cve']."'
							WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=1 ORDER BY a.cve");
						}
						//WHERE a.cve IN (1,2,3,5)");
						while($rowp=mysql_fetch_array($resp)){
							if($rowp['empresa']==0){
								if($rowp['cvededuccion']<=0){
									$select = "INSERT personal_nomina_deduccion SET empresa='".$_POST['cveempresa']."',nomina='".$Personal['cve']."',
									deduccion='".$rowp['cve']."'";
								}
								else{
									$select = "UPDATE personal_nomina_deduccion SET empresa='".$_POST['cveempresa']."',nomina='".$Personal['cve']."',
									deduccion='".$rowp['cve']."'";
								}
							}
							if($rowp['cve']==1){
								$total = round($rowp['dias']*$Personal['sal_diario']*$Empresa['factordiario'],2);
								$select.=",dias='".$rowp['dias']."',montoxdia='".($Personal['sal_diario']*$Empresa['factordiario'])."',total='".$total."'";
							}
							elseif($rowp['cve']==3){
								if($Periodo['aguinaldo']==1){
									if($Personal['baja']==1){
										$total=0;
									}
									else{
										$resi = mysql_query("SELECT sum(total) FROM personal_nomina_percepcion WHERE nomina = '".$Personal['cve']."' AND percepcion=32");
										$rowi=mysql_fetch_array($resi);
										$totalingreso = $rowi[0]-($minimo*30);
										if($totalingreso<0) $totalingreso=0;
										$total = round(montoisr($totalingreso, 4),2);
									}
								}
								else{
									$resi = mysql_query("SELECT sum(total) FROM personal_nomina_percepcion WHERE nomina = '".$Personal['cve']."' AND percepcion!=3");
									$rowi=mysql_fetch_array($resi);
									$resf = mysql_query("SELECT dias FROM personal_nomina_deduccion WHERE nomina = '".$Personal['cve']."' AND deduccion=1");
									$rowf=mysql_fetch_array($resf);
									$totalingreso = ($rowi[0]) - ($rowf[0]*$Personal['sal_diario']*$Empresa['factordiario']);
									$total = round(montoisr($totalingreso, $tipo_nomina)-montosubsidio($totalingreso, $tipo_nomina),2);
								}
								if($total<0) $total = 0;
								$select.=",total='".$total."'";
							}
							elseif($rowp['cve']==2){
								if($Personal['baja']==1){
									$resf = mysql_query("SELECT dias FROM personal_nomina_deduccion WHERE nomina = '".$Personal['cve']."' AND deduccion=1");
									$rowf=mysql_fetch_array($resf);
									$diasimss = $Personal['dias_tra'] - $rowf[0];
								}
								else{
									$diasimss = $Personal['dias_tra'];
								}
								$total = round(calcular_imss($Personal['salario_integrado_personal'])*$diasimss,2);
								$select.=",dias='".$rowp['diasimss']."',montoxdia='".$Personal['salario_integrado_personal']."',total='".$total."'";
							}
							elseif($rowp['cve']==5){
								$monto_prestamo=0;
								if($rowp['total']>0){
									$monto_prestamo=$rowp['total'];
								}
								elseif($Personal['monto_prestamo']>0){
									$rsCargos=mysql_query("SELECT SUM(monto) as cargo FROM personal_cargo WHERE personal='".$Personal['cvepersonal']."' AND fecha<='".$Periodo['fecha_fin']."' AND estatus!='C' GROUP BY personal") or die(mysql_error());	
									$Cargo=mysql_fetch_array($rsCargos);
									//if($Personal['tipo_prestamo']==1)
										$Cargo['cargo']+=$Personal['saldo_prestamo'];
									//$rsAbonos=mysql_query("SELECT sum(monto_prestamo) as abono FROM personal_nomina WHERE personal='".$Personal['cvepersonal']."' AND fecha<='".fechaLocal()."' GROUP BY personal");
									$rsAbonos=mysql_query("SELECT sum(b.total) as abono FROM personal_nomina a INNER JOIN periodos_nomina c ON c.cve=a.periodo INNER JOIN personal_nomina_deduccion b ON a.cve = b.nomina WHERE a.personal='".$Personal['cvepersonal']."' AND c.fecha_fin<'".$Periodo['fecha_fin']."' AND b.deduccion IN (5,8) GROUP BY a.personal");
									$Abono=mysql_fetch_array($rsAbonos);
									$rsAbonos2=mysql_query("SELECT sum(monto) as abono FROM personal_abono WHERE personal='".$Personal['cvepersonal']."' AND fecha<='".$Periodo['fecha_fin']."' AND estatus!='C' GROUP BY personal");
									$Abono2=mysql_fetch_array($rsAbonos2);
									$saldo=$Cargo['cargo']-$Abono['abono']-$Abono2['abono'];
									if($saldo<0)$saldo=0;
									if($Personal['monto_prestamo']>$saldo) $monto_prestamo = $saldo;
									else $monto_prestamo = $Personal['monto_prestamo'];
								}
								$total = round($monto_prestamo,2);
								$select.=",total='".$total."'";
							}
							elseif($rowp['cve']==4){
								$total = round($Personal['monto_infonavit'],2);
								$select.=",total='".$total."'";
							}
							else{
								$total = $rowp['total'];
							}
							//echo $select;
							if($rowp['cvededuccion']>0){
								$select.=" WHERE cve='".$rowp['cvededuccion']."'";
							}
							if(($rowp['cve']!=3 && $rowp['cve']!=2) || $Personal['honorarios']!=1){
								if($rowp['empresa']==0) mysql_query($select);
								$total_deducciones+=$total;
							}
							elseif($rowp['cvededuccion']>0){
								mysql_query("DELETE FROM personal_nomina_deduccion WHERE cve='".$rowp['cvededuccion']."'");
							}
						}
					}
					else{
						mysql_query("DELETE FROM personal_nomina_deduccion WHERE nomina='".$Personal['cve']."'");
					}
					@mysql_query("UPDATE personal_nomina SET totalpercepciones='$total_percepciones',totaldeducciones='$total_deducciones' WHERE cve='".$Personal['cve']."'");
				}
				else{
					$total_percepciones = $Personal['totalpercepciones'];
					$total_deducciones = $Personal['totaldeducciones'];
				}
			}
			else{
				$total_percepciones = $Personal['totalpercepciones'];
				$total_deducciones = $Personal['totaldeducciones'];
			}
			
			if($Periodo['generada_fiscal']==1){
				echo '<td align="center"><input type="checkbox" name="cvepersonal[]" value="'.$Personal['cve'].'" class="chkpersonal"';
				//if((!datos_correctos_timbre($Personal['cvepersonal']) && $Personal['uuid']=="") || ($total_percepciones-$total_deducciones) < 0) echo ' disabled';
				echo '></td>';
				if($Personal['uuid']!="" && $Personal['cancelacion_sat']==""){
					echo '<td align="center"><font color="BLUE">Timbrada</font></td><td>'.$Personal['fechatimbre'].'</td>';
				}
				elseif($Personal['cancelacion_sat']!=""){
					echo '<td align="center">Cancelada</td><td>&nbsp;</td>';
				}
				else{
					echo '<td align="center">Pendiente de Timbrar</td><td>&nbsp;</td>';
				}
			}
			
			if(count($array_plaza)>1) echo '<td>'.$array_plaza[$Personal['plaza']].'</td>';
			if($Personal['uuid']!=""){
				echo '<td>'.$Personal['registro_patronal'].'</td>';
			}
			elseif($Empresa['mismadireccion']==1){
				echo '<td>'.$Empresa['registro_patronal'].'</td>';
			}
			else{
				echo '<td>'.$array_plaza_regimen[$Personal['plaza']].'</td>';
			}
			echo '<td>'.$Personal["nombre"].'</td>';
			echo '<td>'.$array_puestos[$Personal['puesto']].'</td>';
			echo '<td align="center">'.$Personal['rfc'].$Personal['homoclave'].'</td>';
			echo '<td align="center">'.$Personal['imss'].'</td>';
			//echo '<td align="right">'.number_format($Personal['sal_diario'],2).'</td>';
			//echo '<td align="center">'.$Personal['dias_tra'].'</td>';
			//$res1=mysql_query("SELECT dias FROM personal_nomina_deduccion WHERE nomina = '".$Personal['cve']."' AND deduccion=1");
			//$row1=mysql_fetch_array($res1);
			//echo '<td align="center">'.$row1[0].'</td>';
			//echo '<td align="right">'.number_format($Personal['sal_diario']*$Personal['dias_tra'],2).'</td>';
			$indice_total = 0;
			if($Periodo['aguinaldo']==1){
				$res1=mysql_query("SELECT a.cve, b.montoxdia, b.dias, b.total FROM cat_percepciones a LEFT JOIN personal_nomina_percepcion b ON a.cve = b.percepcion AND b.nomina = '".$Personal['cve']."' WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.cve=32 AND a.tipo_nomina=1 ORDER BY a.cve");
			}
			else{
				$res1=mysql_query("SELECT a.cve, b.montoxdia, b.dias, b.total FROM cat_percepciones a LEFT JOIN personal_nomina_percepcion b ON a.cve = b.percepcion AND b.nomina = '".$Personal['cve']."' WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.cve!=32 AND a.tipo_nomina=1 ORDER BY a.cve");
			}
			while($row1=mysql_fetch_array($res1)){
				if($row1['cve']==1){
					echo '<td align="right">'.number_format($Personal['sal_diario'],2).'</td>';
					$array_totales[$indice_total]=-1;$indice_total++;
					echo '<td align="right">'.number_format($Personal['dias_tra'],0).'</td>';
					$array_totales[$indice_total]=-1;$indice_total++;
				}
				elseif($row1['cve']==2){
					echo '<td align="right">'.number_format($row1['dias'],0).'</td>';
					$array_totales[$indice_total]=-1;$indice_total++;
				}
				elseif($row1['cve']==4){
					echo '<td align="right">'.number_format($row1['montoxdia'],2).'</td>';
					$array_totales[$indice_total]=-1;$indice_total++;
					echo '<td align="right">'.number_format($row1['dias'],0).'</td>';
					$array_totales[$indice_total]=-1;$indice_total++;
				}
				echo '<td align="right">'.number_format($row1['total'],2).'</td>';
				$array_totales[$indice_total]+=round($row1['total'],2);$indice_total++;
			}
			echo '<td align="right">'.number_format($total_percepciones,2).'</td>';
			$array_totales[$indice_total]+=round($total_percepciones,2);$indice_total++;
			if($Periodo['aguinaldo']==1){
				$res2=mysql_query("SELECT a.cve, b.montoxdia, b.dias, b.total FROM cat_deducciones a LEFT JOIN personal_nomina_deduccion b ON a.cve = b.deduccion AND b.nomina = '".$Personal['cve']."' WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.cve=3 AND a.tipo_nomina=1 ORDER BY a.cve");
			}
			else{
				$res2=mysql_query("SELECT a.cve, b.montoxdia, b.dias, b.total FROM cat_deducciones a LEFT JOIN personal_nomina_deduccion b ON a.cve = b.deduccion AND b.nomina = '".$Personal['cve']."' WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=1 ORDER BY a.cve");
			}
			while($row2=mysql_fetch_array($res2)){
				if($row2['cve']==1){
					echo '<td align="right">'.number_format($row1['dias'],0).'</td>';
					$array_totales[$indice_total]=-1;$indice_total++;
				}
				echo '<td align="right">'.number_format($row2['total'],2).'</td>';
				$array_totales[$indice_total]+=round($row2['total'],2);$indice_total++;
			}
			echo '<td align="right">'.number_format($total_deducciones,2).'</td>';
			$array_totales[$indice_total]+=round($total_deducciones,2);$indice_total++;
			echo '<td align="right"><span style="cursor: pointer; text-decoration: underline; color: blue;" onClick="mostrarDetalle('.$Personal['cve'].')">'.number_format($total_percepciones-$total_deducciones,2).'</span></td>';
			$array_totales[$indice_total]+=round($total_percepciones-$total_deducciones,2);$indice_total++;
			echo '</tr>';
			$i++;
			$timp+=$Personal['sal_diario']*$Personal['dias_tra'];
			$tper+=$total_percepciones;
			$tded+=$total_deducciones;
			$gtotal+=($total_percepciones-$total_deducciones);
		}
		$col=5;
		/*if($nominagen!=0){
			$col++;
		}*/
		if($Periodo['generada_fiscal']==1) $col+=3;
		if(count($array_plaza)>1) $col++;
		echo '</tbody>
			<tr bgcolor="#E9F2F8"><td colspan="'.$col.'">'.$i.' Registro(s)</td><td>Total</td>';
		foreach($array_totales as $v){
			if($v>='letra')
				echo '<td align="right">'.number_format($v,2).'</td>';
			else
				echo '<td>&nbsp;</td>';
		}
			/*<td align="right">'.number_format($timp,2).'</td>
			<td align="right">'.number_format($tper,2).'</td>
			<td align="right">'.number_format($tded,2).'</td>
			<td align="right">'.number_format($gtotal,2).'</td>*/
			
		echo '</tr>
		</table>';
		
	} else {
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
	$res=mysql_query("SELECT b.nombre as nompersonal, c.nombre as nomperiodo, c.fecha_ini, c.fecha_fin,a.totalpercepciones,a.totaldeducciones
	FROM personal_nomina a INNER JOIN personal b on a.personal = b.cve INNER JOIN periodos_nomina c ON a.periodo = c.cve
	WHERE a.cve='".$_POST['cvenomina']."'");
	$row=mysql_fetch_array($res);
	echo '<h2>'.$row['nompersonal'].'<br>'.$row['nomperiodo'].'<br>'.$row['fecha_ini'].' al '.$row['fecha_fin'].'</h2>';
	echo '<table width="100%"><tr><td width="50%" valign="top">
	<table width="100%"><tr><th>Concepto</th><th>Monto</th></tr>';
	$res1=mysql_query("SELECT b.nombre, a.total FROM personal_nomina_percepcion a INNER JOIN cat_percepciones b on a.percepcion=b.cve WHERE a.nomina='".$_POST['cvenomina']."'");
	while($row1=mysql_fetch_array($res1)){
		echo '<tr>';
		echo '<td>'.$row1['nombre'].'</td>';
		echo '<td align="right">'.number_format($row1['total'],2).'</td>';
		echo '</tr>';
	}
	echo '</table></td><td width="50%" valign="top">
	<table width="100%"><tr><th>Concepto</th><th>Monto</th></tr>';
	$res1=mysql_query("SELECT b.nombre, a.total FROM personal_nomina_deduccion a INNER JOIN cat_deducciones b on a.deduccion=b.cve WHERE a.nomina='".$_POST['cvenomina']."'");
	while($row1=mysql_fetch_array($res1)){
		echo '<tr>';
		echo '<td>'.$row1['nombre'].'</td>';
		echo '<td align="right">'.number_format($row1['total'],2).'</td>';
		echo '</tr>';
	}
	echo '</table></td></tr>
	<tr><th align="right">Total: '.number_format($row['totalpercepciones'],2).'</th>
	<th align="right">Total: '.number_format($row['totaldeducciones'],2).'</th></tr></table>';
	
	exit();
}

top($_SESSION);

if($_POST['cmd']==5){
	mysql_query("DELETE FROM personal_nomina WHERE cve='".$_POST['reg']."'");
	mysql_query("DELETE FROM personal_nomina_deduccion WHERE nomina='".$_POST['reg']."'");
	mysql_query("DELETE FROM personal_nomina_percepcion WHERE nomina='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

if($_POST['cmd']==4){
	mysql_query("UPDATE periodos_nomina a SET generada_fiscal=0 WHERE cve='".$_POST['cveperiodo']."'");
	$_POST['cmd']=0;
}

if($_POST['cmd']==3){
	mysql_query("UPDATE periodos_nomina a SET generada_fiscal=1 WHERE cve='".$_POST['cveperiodo']."'");
	$_POST['cmd']=0;
}

if($_POST['cmd']==2){
	mysql_query("UPDATE personal_nomina SET sal_diario='".$_POST['montoxdia'][1]."',dias_tra='".$_POST['dias'][1]."',faltas='".$_POST['diasd'][1]."',
	totalpercepciones='".$_POST['totalpercepciones']."',totaldeducciones='".$_POST['totaldeducciones']."',baja='".$_POST['baja']."',
	honorarios='".$_POST['honorarios']."' WHERE cve='".$_POST['reg']."'");
	$resp=mysql_query("SELECT * FROM cat_percepciones a 
					WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=1 ORDER BY a.cve");
	while($rowp=mysql_fetch_array($resp)){
		$cve = $rowp['cve'];
		if(($_POST['aguinaldo']!=1 && $cve!=32) || ($_POST['aguinaldo']==1 && $cve==32)){
			if($_POST['cvepercepcion'][$cve] <= 0){
				$select = "INSERT ";
			}
			else{
				$select = "UPDATE ";
			}
			$select .= "personal_nomina_percepcion SET empresa='".$_POST['cveempresa']."',nomina = '".$_POST['reg']."',percepcion='".$cve."',
			dias='".$_POST['dias'][$cve]."',montoxdia='".$_POST['montoxdia'][$cve]."',total='".$_POST['total'][$cve]."'";
			if($_POST['cvepercepcion'][$cve] > 0){
				$select .= " WHERE cve = '".$_POST['cvepercepcion'][$cve]."'";
			}
			//echo $select.'<br>';
			if($rowp['cve']!=3 || $_POST['honorarios']!=1){
				mysql_query($select);
			}
			elseif($_POST['cvepercepcion'][$cve]>0){
				mysql_query("DELETE FROM personal_nomina_percepcion WHERE cve='".$_POST['cvepercepcion'][$cve]."'");
			}
		}
		elseif($_POST['aguinaldo']==1){
			mysql_query("DELETE FROM personal_nomina_percepcion WHERE empresa='".$_POST['cveempresa']."' AND nomina = '".$_POST['reg']."' AND percepcion='".$cve."'");
		}
	}
	
	
	if($_POST['aguinaldo_excento']!=1){
		$resp=mysql_query("SELECT * FROM cat_deducciones a 
						WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=1 ORDER BY a.cve");
		while($rowp=mysql_fetch_array($resp)){
			$cve = $rowp['cve'];
			if($_POST['aguinaldo']!=1 || $cve==3){
				if($_POST['cvededuccion'][$cve] <= 0){
					$select = "INSERT ";
				}
				else{
					$select = "UPDATE ";
				}
				$select .= "personal_nomina_deduccion SET empresa='".$_POST['cveempresa']."',nomina = '".$_POST['reg']."',deduccion='".$cve."',
				dias='".$_POST['diasd'][$cve]."',montoxdia='".$_POST['montoxdiad'][$cve]."',total='".$_POST['totald'][$cve]."',incapacidad='".$_POST['incapacidad'][$cve]."'";
				if($_POST['cvededuccion'][$cve] > 0){
					$select .= " WHERE cve = '".$_POST['cvededuccion'][$cve]."'";
				}
				//echo $select.'<br>';
				if(($rowp['cve']!=3 && $rowp['cve']!=2) || $_POST['honorarios']!=1){
					mysql_query($select);
				}
				elseif($_POST['cvededuccion'][$cve]>0){
					mysql_query("DELETE FROM personal_nomina_deduccion WHERE cve='".$_POST['cvededuccion'][$cve]."'");
				}
			}
			elseif($_POST['aguinaldo']==1){
				mysql_query("DELETE FROM personal_nomina_deduccion WHERE empresa='".$_POST['cveempresa']."' AND nomina = '".$_POST['reg']."' AND deduccion='".$cve."'");
			}
		}
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==12){
	$res=mysql_query("SELECT cve,nombre,generada_fiscal,fecha_ini,fecha_fin,dias as dias_trabajados,factor,aguinaldo,aguinaldo_excento FROM periodos_nomina WHERE empresa='".$_POST['cveempresa']."' AND cve='".$_POST['periodo']."'");
	$Periodo=mysql_fetch_array($res);
	if($Periodo['aguinaldo']==1){
		$select= " SELECT b.eliminada,a.nombre,a.plaza,a.nombre,a.puesto,a.rfc,a.imss,b.cve,b.totalpercepciones,b.totaldeducciones,a.salario_integrado as salario_base_personal, b.sal_diario,
			a.sdi as salario_integrado_personal, b.dias_tra, a.cobro_prestamo as monto_prestamo, a.cve as cvepersonal, b.uuid, 0 as honorarios,
			IF(a.infonavit='SI',a.monto_infonavit,0) as monto_infonavit, b.cancelacion_sat, ROUND(((DATEDIFF('".$Periodo['fecha_fin']."',a.fecha_imss)+1)*".$Periodo['dias_trabajados']."/(DATEDIFF('".$Periodo['fecha_fin']."','".$Periodo['fecha_ini']."')+1)),2),'".$Periodo['dias_trabajados']."') as dias_lab
			FROM personal as a 
			LEFT JOIN personal_nomina as b ON (b.personal=a.cve AND b.periodo='".$_POST['periodo']."' AND b.tipo='1')
			WHERE a.cve='".$_POST['personal']."'";
	}
	else{
		$select= " SELECT b.eliminada,a.nombre,a.plaza,a.nombre,a.puesto,a.rfc,a.imss,b.cve,b.totalpercepciones,b.totaldeducciones,a.salario_integrado as salario_base_personal, b.sal_diario,
			a.sdi as salario_integrado_personal, b.dias_tra, a.cobro_prestamo as monto_prestamo, a.cve as cvepersonal, b.uuid, 0 as honorarios,
			IF(a.infonavit='SI',a.monto_infonavit,0) as monto_infonavit, b.cancelacion_sat, '".$Periodo['dias_trabajados']."' as dias_lab
			FROM personal as a 
			LEFT JOIN personal_nomina as b ON (b.personal=a.cve AND b.periodo='".$_POST['periodo']."' AND b.tipo='1')
			WHERE a.cve='".$_POST['personal']."'";
	}
	$resPersonal=mysql_query($select) or die(mysql_error());
	$Personal=mysql_fetch_array($resPersonal);
	$Empresa = mysql_fetch_array(mysql_query("SELECT * FROM datosempresas WHERE plaza='".$Personal['plaza']."'"));
	$Empresa['mismadireccion']=1;
	$Empresa['tipo_nomina']=3;
	$Empresa['factordiario']=1;
	if($Personal['cve']>0 && $Personal['eliminada']!=1){
		echo '<b>El empleado ya existe en el periodo seleccionado</b>';
		$_POST['cmd']=11;
	}
	else{
		if($Personal['eliminada']==1){
			mysql_query("UPDATE personal_nomina SET empresa='".$_POST['cveempresa']."',periodo='".$_POST['periodo']."',
						personal='".$Personal['cvepersonal']."',sal_diario='".$Personal['salario_base_personal']."',
						dias_tra='".$Personal['dias_lab']."',totaldeducciones=0,totalpercepciones=0,tipo=1,folio='$folioi',
						honorarios='".$Personal['honorarios']."' WHERE cve='".$Personal['cve']."'");
			$_POST['reg']=$Personal['cve'];
		}
		else{
			mysql_query("INSERT personal_nomina SET empresa='".$_POST['cveempresa']."',periodo='".$_POST['periodo']."',
						personal='".$Personal['cvepersonal']."',sal_diario='".$Personal['salario_base_personal']."',
						dias_tra='".$Personal['dias_lab']."',totaldeducciones=0,totalpercepciones=0,tipo=1,folio='$folioi',
						honorarios='".$Personal['honorarios']."'");
			$_POST['reg']=mysql_insert_id();
		}
		$periodofactor=$Personal['dias_lab']*$Periodo['factor']/$Periodo['dias_trabajados'];
		$Personal['cve']=$_POST['reg'];
		$Personal['sal_diario']=$Personal['salario_base_personal'];
		$Personal['dias_tra']=$Personal['dias_lab'];
		
		$total_percepciones=0;
		if($Periodo['aguinaldo']==1){
			$resp=mysql_query("SELECT a.empresa, a.cve,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje,
			b.cve as cvepercepcion, b.dias, b.montoxdia, b.total FROM cat_percepciones a 
			LEFT JOIN personal_nomina_percepcion b ON a.cve = b.percepcion AND b.nomina = '".$Personal['cve']."'
			WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.cve=32 AND a.tipo_nomina=1 ORDER BY a.cve");
		}
		else{
			$resp=mysql_query("SELECT a.empresa, a.cve,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje,
			b.cve as cvepercepcion, b.dias, b.montoxdia, b.total FROM cat_percepciones a 
			LEFT JOIN personal_nomina_percepcion b ON a.cve = b.percepcion AND b.nomina = '".$Personal['cve']."'
			WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.cve!=32 AND a.tipo_nomina=1 ORDER BY a.cve");
		}
		//WHERE a.cve IN (1,2,3)");
		$total_percepciones = 0;
		while($rowp=mysql_fetch_array($resp)){
			if($rowp['empresa']==0){
				if($rowp['cvepercepcion']<=0){
					$select = "INSERT personal_nomina_percepcion SET empresa='".$_POST['cveempresa']."',nomina='".$Personal['cve']."',
					percepcion='".$rowp['cve']."'";
				}
				else{
					$select = "UPDATE personal_nomina_percepcion SET empresa='".$_POST['cveempresa']."',nomina='".$Personal['cve']."',
					percepcion='".$rowp['cve']."'";
				}
			}
			if($rowp['cve']==1){
				$total = round($periodofactor*$Personal['sal_diario'],2);
				$select.=",dias='".$Personal['dias_tra']."',montoxdia='".$Personal['sal_diario']."',total='".$total."'";
			}
			elseif($rowp['cve']==2){
				$total = round($rowp['dias']*$Personal['sal_diario']*$Empresa['factordiario']*0.25,2);
				$select.=",dias='".$rowp['dias']."',montoxdia='".($Personal['sal_diario']*$Empresa['factordiario']*0.25)."',total='".$total."'";
			}
			elseif($rowp['cve']==32){
				$total = round($periodofactor*$Personal['sal_diario'],2);
				$select.=",dias='".$Personal['dias_tra']."',montoxdia='".$Personal['sal_diario']."',total='".$total."'";
			}
			elseif($rowp['cve']==3){
				$resi = mysql_query("SELECT sum(total) FROM personal_nomina_percepcion WHERE nomina = '".$Personal['cve']."' AND percepcion!=3");
				$rowi=mysql_fetch_array($resi);
				$resf = mysql_query("SELECT dias FROM personal_nomina_deduccion WHERE nomina = '".$Personal['cve']."' AND deduccion=1");
				$rowf=mysql_fetch_array($resf);
				$totalingreso = ($rowi[0]) - ($rowf[0]*$Personal['sal_diario']*$Empresa['factordiario']);
				$total = round(montosubsidio($totalingreso, $tipo_nomina)-montoisr($totalingreso, $tipo_nomina),2);
				if($total<0) $total = 0;
				$select.=",total='".$total."'";
			}
			else{
				$total = $rowp['total'];
			}
			if($rowp['cvepercepcion']>0){
				$select.=" WHERE cve='".$rowp['cvepercepcion']."'";
			}
			//echo $select;
			if($rowp['cve']!=3 || $Personal['honorarios']!=1){
				if($rowp['empresa'] == 0) mysql_query($select);
				$total_percepciones+=$total;
			}
			elseif($rowp['cvepercepcion']>0){
				mysql_query("DELETE FROM personal_nomina_percepcion WHERE cve='".$rowp['cvepercepcion']."'");
			}
		}
		$total_deducciones=0;
		if($Periodo['aguinaldo_excento']!=1){
			if($Periodo['aguinaldo']==1){
				$resp=mysql_query("SELECT a.empresa, a.cve,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje,
				b.cve as cvededuccion, b.dias, b.montoxdia, b.total FROM cat_deducciones a 
				LEFT JOIN personal_nomina_deduccion b ON a.cve = b.deduccion AND b.nomina = '".$Personal['cve']."'
				WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.cve=3 AND a.tipo_nomina=1 ORDER BY a.cve");
			}
			else{
				$resp=mysql_query("SELECT a.empresa, a.cve,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje,
				b.cve as cvededuccion, b.dias, b.montoxdia, b.total FROM cat_deducciones a 
				LEFT JOIN personal_nomina_deduccion b ON a.cve = b.deduccion AND b.nomina = '".$Personal['cve']."'
				WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=1 ORDER BY a.cve");
			}
			//WHERE a.cve IN (1,2,3,5)");
			while($rowp=mysql_fetch_array($resp)){
				if($rowp['empresa']==0){
					if($rowp['cvededuccion']<=0){
						$select = "INSERT personal_nomina_deduccion SET empresa='".$_POST['cveempresa']."',nomina='".$Personal['cve']."',
						deduccion='".$rowp['cve']."'";
					}
					else{
						$select = "UPDATE personal_nomina_deduccion SET empresa='".$_POST['cveempresa']."',nomina='".$Personal['cve']."',
						deduccion='".$rowp['cve']."'";
					}
				}
				if($rowp['cve']==1){
					$total = round($rowp['dias']*$Personal['sal_diario']*$Empresa['factordiario'],2);
					$select.=",dias='".$rowp['dias']."',montoxdia='".($Personal['sal_diario']*$Empresa['factordiario'])."',total='".$total."'";
				}
				elseif($rowp['cve']==3){
					if($Periodo['aguinaldo']==1){
						$resi = mysql_query("SELECT sum(total) FROM personal_nomina_percepcion WHERE nomina = '".$Personal['cve']."' AND percepcion=32");
						$rowi=mysql_fetch_array($resi);
						$totalingreso = $rowi[0]-($minimo*30);
						if($totalingreso<0) $totalingreso=0;
						$total = round(montoisr($totalingreso, 4),2);
					}
					else{
						$resi = mysql_query("SELECT sum(total) FROM personal_nomina_percepcion WHERE nomina = '".$Personal['cve']."' AND percepcion!=3");
						$rowi=mysql_fetch_array($resi);
						$resf = mysql_query("SELECT dias FROM personal_nomina_deduccion WHERE nomina = '".$Personal['cve']."' AND deduccion=1");
						$rowf=mysql_fetch_array($resf);
						$totalingreso = ($rowi[0]) - ($rowf[0]*$Personal['sal_diario']*$Empresa['factordiario']);
						$total = round(montoisr($totalingreso, $tipo_nomina)-montosubsidio($totalingreso, $tipo_nomina),2);
					}
					if($total<0) $total = 0;
					$select.=",total='".$total."'";
				}
				elseif($rowp['cve']==2){
					$total = round(calcular_imss($Personal['salario_integrado_personal'])*$Personal['dias_tra'],2);
					$select.=",montoxdia='".$Personal['salario_integrado_personal']."',total='".$total."'";
				}
				elseif($rowp['cve']==5){
					$monto_prestamo=0;
					if($Personal['monto_prestamo']>0){
						$rsCargos=mysql_query("SELECT SUM(monto) as cargo FROM personal_cargo WHERE personal='".$Personal['cvepersonal']."' AND fecha<='".$Periodo['fecha_fin']."' AND estatus!='C' GROUP BY personal") or die(mysql_error());	
						$Cargo=mysql_fetch_array($rsCargos);
						//if($Personal['tipo_prestamo']==1)
							$Cargo['cargo']+=$Personal['saldo_prestamo'];
						//$rsAbonos=mysql_query("SELECT sum(monto_prestamo) as abono FROM personal_nomina WHERE personal='".$Personal['cvepersonal']."' AND fecha<='".fechaLocal()."' GROUP BY personal");
						$rsAbonos=mysql_query("SELECT sum(b.total) as abono FROM personal_nomina a INNER JOIN periodos_nomina c ON c.cve=a.periodo INNER JOIN personal_nomina_deduccion b ON a.cve = b.nomina WHERE a.personal='".$Personal['cvepersonal']."' AND c.fecha_fin<'".$Peridoo['fecha_fin']."' AND b.deduccion IN (5,8) GROUP BY a.personal");
						$Abono=mysql_fetch_array($rsAbonos);
						$rsAbonos2=mysql_query("SELECT sum(monto) as abono FROM personal_abono WHERE personal='".$Personal['cvepersonal']."' AND fecha<='".$Periodo['fecha_fin']."' AND estatus!='C' GROUP BY personal");
						$Abono2=mysql_fetch_array($rsAbonos2);
						$saldo=$Cargo['cargo']-$Abono['abono']-$Abono2['abono'];
						if($saldo<0)$saldo=0;
						if($Personal['monto_prestamo']>$saldo) $monto_prestamo = $saldo;
						else $monto_prestamo = $Personal['monto_prestamo'];
					}
					$total = round($monto_prestamo,2);
					$select.=",total='".$total."'";
				}
				elseif($rowp['cve']==4){
					$total = round($Personal['monto_infonavit'],2);
					$select.=",total='".$total."'";
				}
				else{
					$total = $rowp['total'];
				}
				//echo $select;
				if($rowp['cvededuccion']>0){
					$select.=" WHERE cve='".$rowp['cvededuccion']."'";
				}
				if(($rowp['cve']!=3 && $rowp['cve']!=2) || $Personal['honorarios']!=1){
					if($rowp['empresa']==0) mysql_query($select);
					$total_deducciones+=$total;
				}
				elseif($rowp['cvepercepcion']>0){
					mysql_query("DELETE FROM personal_nomina_deduccion WHERE cve='".$rowp['cvededuccion']."'");
				}
			}
		}
		else{
			mysql_query("DELETE FROM personal_nomina_deduccion WHERE nomina='".$Personal['cve']."'");
		}
		@mysql_query("UPDATE personal_nomina SET totalpercepciones='$total_percepciones',totaldeducciones='$total_deducciones' WHERE cve='".$Personal['cve']."'");
	
		$_POST['cmd']=1;
	}
}

if($_POST['cmd']==1){
	$resNomina=mysql_query("SELECT * FROM personal_nomina WHERE cve='".$_POST['reg']."'");
	$Nomina=mysql_fetch_array($resNomina);
	$resPersonal=mysql_query("SELECT * FROM personal WHERE cve='".$Nomina['personal']."'");
	$Personal=mysql_fetch_array($resPersonal);
	$Empresa = mysql_fetch_array(mysql_query("SELECT * FROM datosempresas WHERE plaza='".$Personal['plaza']."'"));
	$Empresa['mismadireccion']=1;
	$Empresa['tipo_nomina']=3;
	$Empresa['factordiario']=1;
	$resPeriodo=mysql_query("SELECT cve,nombre,generada_fiscal,fecha_ini,fecha_fin,dias as dias_trabajados,factor,aguinaldo,aguinaldo_excento FROM periodos_nomina WHERE empresa='".$_POST['cveempresa']."' AND cve='".$Nomina['periodo']."'");
	$Periodo=mysql_fetch_array($resPeriodo);
	$periodofactor=$Periodo['factor'];
	if($Periodo['generada_fiscal']!=1) $Nomina['honorarios']=$Personal['honorarios'];
	echo '<table><tr>';
	if(nivelUsuario()>1 && $Nomina['uuid']==""){
		echo '<td><a href="#" onClick="atcr(\'nomina_fiscal.php\',\'\',2,\''.$_POST['reg'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	}
	echo '<td><a href="#" onclick="atcr(\'nomina_fiscal.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
	echo '</tr></table>';
	echo '<br>';
	echo '<table>';
	echo '<tr><td class="tableEnc">Nomina del Personal '.$Personal['nombre'].'<br>'.$Periodo['nombre'].'<br>'.$Periodo['fecha_ini'].'<br>'.$Periodo['fecha_fin'].'</td></tr>';
	echo '</table>';
	echo '<br>';
	echo '<input type="hidden" name="honorarios" id="honorarios" value="'.$Nomina['honorarios'].'">';
	echo '<input type="hidden" name="aguinaldo" id="aguinaldo" value="'.$Periodo['aguinaldo'].'">';
	echo '<input type="hidden" name="aguinaldo_excento" id="aguinaldo_excento" value="'.$Periodo['aguinaldo_excento'].'">';
	echo '<table>';
	echo '<tr><td colspan="2" class="tableEnc">Percepciones</td></tr>';
	$total_percepciones=0;
	if($Periodo['aguinaldo']==1){
		$tipo_nomina=4;
		$resp=mysql_query("SELECT a.cve,a.nombre,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje,
					b.cve as cvepercepcion, b.dias, b.montoxdia, b.total FROM cat_percepciones a 
					LEFT JOIN personal_nomina_percepcion b ON a.cve = b.percepcion AND b.nomina = '".$Nomina['cve']."'
					WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.cve=32 AND a.tipo_nomina=1 ORDER BY a.cve");
	}
	else{
		$resp=mysql_query("SELECT a.cve,a.nombre,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje,
					b.cve as cvepercepcion, b.dias, b.montoxdia, b.total FROM cat_percepciones a 
					LEFT JOIN personal_nomina_percepcion b ON a.cve = b.percepcion AND b.nomina = '".$Nomina['cve']."'
					WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.cve!=32 AND a.tipo_nomina=1 ORDER BY a.cve");
	}
	while($rowp=mysql_fetch_array($resp)){
		echo '<input type="hidden" name="cvepercepcion['.$rowp['cve'].']" value="'.$rowp['cvepercepcion'].'">';
		if($rowp['cve']==1){
			if($Periodo['generada_fiscal']!=1){
				$Periodo['factor']=$rowp['dias']*$periodofactor/$Periodo['dias_trabajados'];
				$rowp['montoxdia']=$Personal['salario_integrado'];
				$rowp['total']=round($rowp['montoxdia']*$Periodo['factor'],2);
			}
			if($_POST['cveusuario']==1){
				$Periodo['factor']=$rowp['dias']*$periodofactor/$Periodo['dias_trabajados'];				
				echo '<tr><th align="left">Sueldo Base</th><td><input type="text" class="textField" id="montoxdia_1" name="montoxdia[1]" value="'.$rowp['montoxdia'].'" onKeyUp="calcular(1)" ></td></tr>';
			}
			else{
				echo '<tr><th align="left">Sueldo Base</th><td><input type="text" class="readOnly" id="montoxdia_1" name="montoxdia[1]" value="'.$rowp['montoxdia'].'" readOnly></td></tr>';
			}
			echo '<tr><th align="left">Dias Trabajados</th><td><input type="text" class="readOnly" id="dias_1" name="dias[1]" value="'.$rowp['dias'].'" readOnly></td></tr>';
			echo '<tr><th align="left">Importe</th><td><input type="text" class="readOnly impuestos percepciones" id="total_1" name="total[1]" value="'.round($rowp['total'],2).'" readOnly></td></tr>';
		}
		elseif($rowp['cve']==32){
			if($Periodo['generada_nofiscal']!=1){
				$resDias=mysql_query("SELECT IF('".$Personal['fecha_imss']."'>'".$Periodo['fecha_ini']."',ROUND(((DATEDIFF('".$Periodo['fecha_fin']."','".$Personal['fecha_imss']."')+1)*".$Periodo['dias_trabajados']."/(DATEDIFF('".$Periodo['fecha_fin']."','".$Periodo['fecha_ini']."')+1)),2),'".$Periodo['dias_trabajados']."')");
				$Dias=mysql_fetch_array($resDias);
				$rowp['dias']=$Dias[0];
				$Periodo['factor']=$rowp['dias']*$periodofactor/$Periodo['dias_trabajados'];
				$rowp['montoxdia']=$Personal['salario_integrado'];
				$rowp['total']=round($rowp['montoxdia']*$Periodo['factor'],2);
			}
			if($_POST['cveusuario']==1){
				$Periodo['factor']=$rowp['dias']*$periodofactor/$Periodo['dias_trabajados'];
				echo '<tr><th align="left">Sueldo Base</th><td><input type="text" class="textField" id="montoxdia_'.$rowp['cve'].'" name="montoxdia['.$rowp['cve'].']" value="'.$rowp['montoxdia'].'" onKeyUp="calcular('.$rowp['cve'].')" ></td></tr>';
			}
			else{
				echo '<tr><th align="left">Sueldo Base</th><td><input type="text" class="readOnly" id="montoxdia_'.$rowp['cve'].'" name="montoxdia['.$rowp['cve'].']" value="'.$rowp['montoxdia'].'" readOnly></td></tr>';
			}
			//echo '<tr><th align="left">Sueldo Base</th><td><input type="text" class="readOnly" id="montoxdia_'.$rowp['cve'].'" name="montoxdia['.$rowp['cve'].']" value="'.$rowp['montoxdia'].'" readOnly></td></tr>';
			echo '<tr><th align="left">Dias de Aguinaldo</th><td><input type="text" class="readOnly" id="dias_'.$rowp['cve'].'" name="dias['.$rowp['cve'].']" value="'.$rowp['dias'].'" readOnly></td></tr>';
			echo '<tr><th align="left">Importe</th><td><input type="text" class="readOnly percepciones" id="total_'.$rowp['cve'].'" name="total['.$rowp['cve'].']" value="'.round($rowp['total'],2).'" readOnly></td></tr>';
		}
		elseif($rowp['cve']==2){
			if($Periodo['generada_fiscal']!=1){
				$rowp['montoxdia']=$Personal['salario_integrado']*$Empresa['factordiario']*0.25;
				$rowp['total']=round($rowp['montoxdia']*$rowp['dias'],2);
			}
			echo '<tr><th align="left">Domingos Trabajados</th><td><input type="text" class="textField" id="dias_2" name="dias[2]" value="'.$rowp['dias'].'" onKeyUp="calcular(2);"><input type="hidden" class="readOnly" id="montoxdia_2" name="montoxdia[2]" value="'.$rowp['montoxdia'].'" readOnly></td></tr>';
			echo '<tr><th align="left">Prima Dominical</th><td><input type="text" class="readOnly impuestos percepciones" id="total_2" name="total[2]" value="'.round($rowp['total'],2).'" readOnly></td></tr>';
		}
		elseif($rowp['cve']==3){
			if($Nomina['honorarios']==1) $rowp['total']=0;
			echo '<input type="hidden" class="textField" id="dias_3" name="dias[3]" value="'.$rowp['dias'].'"><input type="hidden" class="readOnly" id="montoxdia_3" name="montoxdia[3]" value="'.$rowp['montoxdia'].'" readOnly>';
			echo '<tr><th align="left">Subsidio al Empleo</th><td><input type="text" class="readOnly percepciones" id="total_3" name="total[3]" value="'.round($rowp['total'],2).'" readOnly></td></tr>';
		}
		elseif($rowp['cve']==4){
			if($Periodo['generada_fiscal']!=1){
				$rowp['montoxdia']=$Personal['salario_integrado']/8;
				$rowp['total']=round($rowp['montoxdia']*$rowp['dias'],2);
			}
			echo '<tr><th align="left">Sueldo por Hora</th><td><input type="text" class="readOnly" id="montoxdia_4" name="montoxdia[4]" value="'.$rowp['montoxdia'].'" readOnly></td></tr>';
			echo '<tr><th align="left">Horas Extras Trabajadas</th><td><input type="text" class="textField" id="dias_4" name="dias[4]" value="'.$rowp['dias'].'" onKeyUp="calcular('.$rowp['cve'].')"></td></tr>';
			echo '<tr><th align="left">Importe Horas Extras</th><td><input type="text" class="readOnly impuestos percepciones" id="total_4" name="total[4]" value="'.round($rowp['total'],2).'" readOnly></td></tr>';
		}
		else{
			echo '<input type="hidden" class="textField" id="dias_'.$rowp['cve'].'" name="dias['.$rowp['cve'].']" value="'.$rowp['dias'].'"><input type="hidden" class="readOnly" id="montoxdia_'.$rowp['cve'].'" name="montoxdia['.$rowp['cve'].']" value="'.$rowp['montoxdia'].'" readOnly>';
			echo '<tr><th align="left">'.$rowp['nombre'].'</th><td><input type="text" class="textField impuestos percepciones" id="total_'.$rowp['cve'].'" name="total['.$rowp['cve'].']" value="'.round($rowp['total'],2).'" onKeyUp="calcula_impuestos();"></td></tr>';
		}
		$total_percepciones += $rowp['total'];
	}
	echo '<tr><th align="left">Total Percepciones</th><td><input type="text" class="readOnly" id="totalpercepciones" name="totalpercepciones" value="'.round($total_percepciones,2).'" readOnly></td></tr>';
	
	
	$ocultardeduccciones='';
	if($Periodo['aguinaldo_excento']==1) $ocultar_deducciones = ' style="display:none;"';
	if($Periodo['aguinaldo']==1){
		echo '<tr'.$ocultar_deducciones.'><th align="left">Excento de Impuestos</th><td><input type="radio" name="baja" value="0" onClick="calcular(32)"';
		if($Nomina['baja']!=1 && $Periodo['aguinaldo_excento']!=1) echo ' checked';
		echo '>No&nbsp;&nbsp;<input type="radio" name="baja" value="1" onClick="calcular(32)"';
		if($Nomina['baja']==1 || $Periodo['aguinaldo_excento']==1) echo ' checked';
		echo '>Si&nbsp;<b><font color="RED">Usar cuando se quiera quitar el calculo de impuestos</font></b></td></tr>';
	}
	else{
		echo '<tr><th align="left">Baja</th><td><input type="radio" name="baja" value="0" onClick="calcularimss(0,1)"';
		if($Nomina['baja']!=1) echo ' checked';
		echo '>No&nbsp;&nbsp;<input type="radio" name="baja" value="1" onClick="calcularimss(1,1)"';
		if($Nomina['baja']==1) echo ' checked';
		echo '>Si&nbsp;<b><font color="RED">Usar cuando se quiera quitar el calculo de impuestos como por ejemplo una incapacidad</font></b></td></tr>';
	}
	
	echo '<tr'.$ocultar_deducciones.'><td colspan="2" class="tableEnc">Deducciones</td></tr>';
	$total_deducciones = 0;
	if($Periodo['aguinaldo']==1){
		$resp=mysql_query("SELECT a.cve,a.nombre,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje,
					b.cve as cvededuccion, b.dias, b.montoxdia, b.total, b.incapacidad FROM cat_deducciones a 
					LEFT JOIN personal_nomina_deduccion b ON a.cve = b.deduccion AND b.nomina = '".$Nomina['cve']."'
					WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.cve=3 AND a.tipo_nomina=1 ORDER BY a.cve");
	}
	else{
		$resp=mysql_query("SELECT a.cve,a.nombre,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje,
					b.cve as cvededuccion, b.dias, b.montoxdia, b.total, b.incapacidad FROM cat_deducciones a 
					LEFT JOIN personal_nomina_deduccion b ON a.cve = b.deduccion AND b.nomina = '".$Nomina['cve']."'
					WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=1 ORDER BY a.cve");
	}
	while($rowp=mysql_fetch_array($resp)){
		echo '<input type="hidden" name="cvededuccion['.$rowp['cve'].']" value="'.$rowp['cvededuccion'].'">';
		if($rowp['cve']==1){
			if($Periodo['generada_fiscal']!=1){
				$rowp['montoxdia']=$Personal['salario_integrado']*$Empresa['factordiario'];
				$rowp['total']=round($rowp['montoxdia']*$rowp['dias'],2);
			}
			echo '<tr><th align="left">Dias que Falto</th><td><input type="text" class="textField" id="diasd_1" name="diasd[1]" value="'.$rowp['dias'].'" onKeyUp="calculard(1);"><input type="hidden" class="readOnly" id="montoxdiad_1" name="montoxdiad[1]" value="'.$rowp['montoxdia'].'" readOnly>&nbsp;<input type="checkbox" name="incapacidad[1]" value="1"';
			if($rowp['incapacidad']==1) echo ' checked';
			echo '>&nbsp;Por Incapacidad</td></tr>';
			echo '<tr><th align="left">Importe por Faltas</th><td><input type="text" class="readOnly deducciones" id="totald_1" name="totald[1]" value="'.round($rowp['total'],2).'" readOnly></td></tr>';
		}
		elseif($rowp['cve']==2){
			if($Periodo['generada_fiscal']!=1){
				$rowp['montoxdia']=$Personal['sdi'];
			}
			if($Nomina['honorarios']==1) $rowp['total']=0;
			echo '<input type="hidden" class="textField" id="diasd_2" name="diasd[2]" value="'.$rowp['dias'].'"><input type="hidden" class="readOnly" id="montoxdiad_2" name="montoxdiad[2]" value="'.$rowp['montoxdia'].'" readOnly>';
			echo '<tr><th align="left">IMSS</th><td><input type="text" class="readOnly deducciones" id="totald_2" name="totald[2]" value="'.round($rowp['total'],2).'" readOnly></td></tr>';
		}
		elseif($rowp['cve']==3){
			if($Nomina['honorarios']==1 || ($Periodo['aguinaldo']==1 && $Nomina['baja']==1) || $Periodo['aguinaldo_excento'] == 1){
				$rowp['total']=0;
			}
			echo '<input type="hidden" class="textField" id="diasd_3" name="diasd[3]" value="'.$rowp['dias'].'"><input type="hidden" class="readOnly" id="montoxdiad_3" name="montoxdiad[3]" value="'.$rowp['montoxdia'].'" readOnly>';
			echo '<tr'.$ocultar_deducciones.'><th align="left">ISR</th><td><input type="text" class="readOnly deducciones" id="totald_3" name="totald[3]" value="'.round($rowp['total'],2).'" readOnly></td></tr>';
		}
		elseif($rowp['cve']==5){
			if($rowp['total']==0){
				$monto_prestamo=0;
				if($Personal['cobro_prestamo']>0){
					$rsCargos=mysql_query("SELECT SUM(monto) as cargo FROM personal_cargo WHERE personal='".$Personal['cve']."' AND fecha<='".$Periodo['fecha_fin']."' AND estatus!='C' GROUP BY personal") or die(mysql_error());	
					$Cargo=mysql_fetch_array($rsCargos);
					//if($Personal['tipo_prestamo']==1)
						$Cargo['cargo']+=$Personal['saldo_prestamo'];
					//$rsAbonos=mysql_query("SELECT sum(monto_prestamo) as abono FROM personal_nomina WHERE personal='".$Personal['cvepersonal']."' AND fecha<='".fechaLocal()."' GROUP BY personal");
					$rsAbonos=mysql_query("SELECT sum(b.total) as abono FROM personal_nomina a INNER JOIN periodos_nomina c ON c.cve=a.periodo INNER JOIN personal_nomina_deduccion b ON a.cve = b.nomina WHERE a.personal='".$Personal['cve']."' AND c.fecha_fin<='".$Periodo['fecha_fin']."' AND b.deduccion IN (5,8) GROUP BY a.personal");
					$Abono=mysql_fetch_array($rsAbonos);
					$rsAbonos2=mysql_query("SELECT sum(monto) as abono FROM personal_abono WHERE personal='".$Personal['cve']."' AND fecha<='".$Periodo['fecha_fin']."' AND estatus!='C' GROUP BY personal");
					$Abono2=mysql_fetch_array($rsAbonos2);
					$saldo=$Cargo['cargo']-$Abono['abono']-$Abono2['abono'];
					if($saldo<0)$saldo=0;
					if($Personal['cobro_prestamo']>$saldo) $monto_prestamo = $saldo;
					else $monto_prestamo = $Personal['cobro_prestamo'];
				}
				$rowp['total'] = round($monto_prestamo,2);
			}
			echo '<input type="hidden" class="textField" id="diasd_'.$rowp['cve'].'" name="diasd['.$rowp['cve'].']" value="'.$rowp['dias'].'"><input type="hidden" class="readOnly" id="montoxdiad_'.$rowp['cve'].'" name="montoxdiad['.$rowp['cve'].']" value="'.$rowp['montoxdia'].'" readOnly>';
			echo '<tr><th align="left">'.$rowp['nombre'].'</th><td><input type="text" class="textField deducciones" id="totald_'.$rowp['cve'].'" name="totald['.$rowp['cve'].']" value="'.round($rowp['total'],2).'" onKeyUp="totales()"></td></tr>';
		}
		else{
			echo '<input type="hidden" class="textField" id="diasd_'.$rowp['cve'].'" name="diasd['.$rowp['cve'].']" value="'.$rowp['dias'].'"><input type="hidden" class="readOnly" id="montoxdiad_'.$rowp['cve'].'" name="montoxdiad['.$rowp['cve'].']" value="'.$rowp['montoxdia'].'" readOnly>';
			if($rowp['cve']==4)
				echo '<tr><th align="left">'.$rowp['nombre'].'</th><td><input type="text" class="readOnly deducciones" id="totald_'.$rowp['cve'].'" name="totald['.$rowp['cve'].']" value="'.round($rowp['total'],2).'" readOnly></td></tr>';
			else
				echo '<tr><th align="left">'.$rowp['nombre'].'</th><td><input type="text" class="textField deducciones" id="totald_'.$rowp['cve'].'" name="totald['.$rowp['cve'].']" value="'.round($rowp['total'],2).'" onKeyUp="totales()"></td></tr>';
		}
		$total_deducciones += $rowp['total'];
	}
	echo '<tr><th align="left">Total Deducciones</th><td><input type="text" class="readOnly" id="totaldeducciones" name="totaldeducciones" value="'.round($total_deducciones,2).'" readOnly></td></tr>';
	
	echo '<tr><th align="left">Total a Pagar</th><td><input type="text" class="readOnly" id="total" name="total_a_pagar" value="'.round($total_percepciones-$total_deducciones,2).'" readOnly></td></tr>';
	echo '</table>';
	
	echo '<script>
			function calcularimss(tipo,calculartotal)
			{
				if('.intval($Nomina['honorarios']).' == 1){
					document.getElementById("totald_2").value = 0.00;
				}
				else{
					if(tipo==0){
						dias = '.intval($Nomina['dias_tra']).';
					}
					else{
						dias = '.intval($Nomina['dias_tra']).' - document.getElementById("diasd_1").value;
					}
					document.getElementById("diasd_2").value=dias;
					total = dias * calcular_imss(document.getElementById("montoxdiad_2").value);
					document.getElementById("totald_2").value = total.toFixed(2);
				}
				
				if(calculartotal==1) totales();
			}
	
			function montoisr(tot){
				var monto = 0;
				if('.intval($Nomina['honorarios']).' == 1) return monto;
				';
				$res1 = mysql_query("SELECT * FROM nomina WHERE tipo_nomina = '$tipo_nomina' ORDER BY limite_inferior DESC");
				$i=0;
				while($row1=mysql_fetch_array($res1)){
					if($i==0)
						echo 'if';
					else
						echo 'else if';
					echo '('.$row1['limite_inferior'].'<=(tot/1)){
						monto2 = (tot/1)-'.$row1['limite_inferior'].';
						monto3 = monto2*'.$row1['porcentaje'].'/100;
						monto4 = monto3.toFixed(2);
						monto = (monto4/1)+('.$row1['cuota'].'/1);
					}
					';
					$i++;
				}
			echo '	
				return monto.toFixed(2);
			}
			
			function montosubsidio(tot){
				var monto = 0;
				if('.intval($Nomina['honorarios']).' == 1) return monto;
				';
				$res1 = mysql_query("SELECT * FROM nomina_subsidio WHERE tipo_nomina = '$tipo_nomina' ORDER BY ingreso_min DESC");
				$i=0;
				while($row1=mysql_fetch_array($res1)){
					if($i==0)
						echo 'if';
					else
						echo 'else if';
					echo '('.$row1['ingreso_min'].'<=(tot/1)){
						monto = '.$row1['subsidio'].';
					}
					';
					$i++;
				}
			echo '	
				return monto.toFixed(2);
			}
			
			function calcula_impuestos()
			{
				var total = 0;
				$(".impuestos").each(function(){
					total += this.value/1;
				});
				total = total - (document.getElementById("totald_1").value*1);
				var total2 = montosubsidio(total) - montoisr(total);
				if(total2>=0){
					document.getElementById("total_"+3).value = total2.toFixed(2);
					document.getElementById("totald_"+3).value = 0;
				}
				else{
					total2 = total2*(-1);
					document.getElementById("total_"+3).value = 0;
					document.getElementById("totald_"+3).value = total2.toFixed(2);
				}
				totales();
			}
			
			function calcular(cve){
				if(cve!=1  && cve!=32){
					if(cve==4){
						montoxdia4 = document.getElementById("montoxdia_1").value/8;
						document.getElementById("montoxdia_"+cve).value = montoxdia4.toFixed(2);
						/*if((document.getElementById("dias_"+cve).value/1)<=9){
							total = (document.getElementById("dias_"+cve).value*(document.getElementById("montoxdia_"+cve).value*2));
						}
						else{
							total = (9*(document.getElementById("montoxdia_"+cve).value*2));
							total = total.toFixed(2);
							total = (total/1)+((document.getElementById("dias_"+cve).value-9)*(document.getElementById("montoxdia_"+cve).value*3));
						}*/
						total = (document.getElementById("dias_"+cve).value*(document.getElementById("montoxdia_"+cve).value*3));
					}
					else{
						total = (document.getElementById("dias_"+cve).value*document.getElementById("montoxdia_"+cve).value);
					}
					document.getElementById("total_"+cve).value = total.toFixed(2);
				}
				else if(cve==32){
					total = ('.$Periodo['factor'].'*document.getElementById("montoxdia_"+cve).value);
					document.getElementById("total_"+cve).value = total.toFixed(2);
				}
				else{
					total = '.$Periodo['factor'].'*document.getElementById("montoxdia_"+cve).value;
					document.getElementById("total_"+cve).value = total.toFixed(2);
					total = '.$Empresa['factordiario'].'*document.getElementById("montoxdia_"+cve).value*0.25;
					document.getElementById("montoxdia_2").value = total.toFixed(2);
					total = (document.getElementById("dias_2").value*document.getElementById("montoxdia_2").value);
					document.getElementById("total_2").value = total.toFixed(2);
					total = '.$Empresa['factordiario'].'*document.getElementById("montoxdia_"+cve).value;
					document.getElementById("montoxdiad_1").value = total.toFixed(2);
					total = (document.getElementById("diasd_1").value*document.getElementById("montoxdiad_1").value);
					document.getElementById("totald_1").value = total.toFixed(2);
				}
				if(cve==1 || cve==2 || cve==4){
					total = 0;
					$(".impuestos").each(function(){
						total += this.value/1;
					});
					total = total - (document.getElementById("totald_1").value*1);
					var total2 = montosubsidio(total) - montoisr(total);
					if(total2>=0){
						document.getElementById("total_"+3).value = total2.toFixed(2);
						document.getElementById("totald_"+3).value = 0;
					}
					else{
						total2 = total2*(-1);
						document.getElementById("total_"+3).value = 0;
						document.getElementById("totald_"+3).value = total2.toFixed(2);
					}
					//total = calcular_imss(document.getElementById("montoxdiad_2").value) * ((document.getElementById("dias_1").value/1)-(document.getElementById("diasd_1").value/1));
					//document.getElementById("totald_"+2).value = total.toFixed(2);
				}
				if(cve==32){
					if(document.forma.baja[0].checked){
						total = document.getElementById("total_"+32).value - '.floatval($minimo*30).';
						if(total<0) total=0;
						var total2 = montoisr(total)/1;
						document.getElementById("totald_"+3).value = total2.toFixed(2);
					}
					else{
						document.getElementById("totald_"+3).value = 0;
					}
				}
				if(cve==1){
					calcular(4)
				}
				totales();
			}
			
			function calculard(cve){
				total = (document.getElementById("diasd_"+cve).value*document.getElementById("montoxdiad_"+cve).value);
				document.getElementById("totald_"+cve).value = total.toFixed(2);
				if(cve==1){
					total = 0;
					$(".impuestos").each(function(){
						total += this.value/1;
					});
					total = total - (document.getElementById("totald_1").value*1);
					var total2 = montosubsidio(total) - montoisr(total);
					if(total2>=0){
						document.getElementById("total_"+3).value = total2.toFixed(2);
						document.getElementById("totald_"+3).value = 0;
					}
					else{
						total2 = total2*(-1);
						document.getElementById("total_"+3).value = 0;
						document.getElementById("totald_"+3).value = total2.toFixed(2);
					}
					//total = calcular_imss(document.getElementById("montoxdiad_2").value) * ((document.getElementById("dias_1").value/1)-(document.getElementById("diasd_1").value/1));
					//document.getElementById("totald_"+2).value = total.toFixed(2);
					
					if(document.forma.baja[0].checked)
						calcularimss(0,0);
					else
						calcularimss(1,0);
				}
				totales();
			}
			
			function totales(){
				var total = 0;
				$(".percepciones").each(function(){
					total += this.value/1;
				});
				document.forma.totalpercepciones.value=total.toFixed(2);
				
				total = 0;
				$(".deducciones").each(function(){
					total += this.value/1;
				});
				document.forma.totaldeducciones.value=total.toFixed(2);
				
				total = document.forma.totalpercepciones.value - document.forma.totaldeducciones.value;
				
				document.forma.total_a_pagar.value = total.toFixed(2);
			}
			
			if('.intval($Periodo['aguinaldo']).'==1){
				calcular(32);
			}	
			else{
				calcular(1);
				calcular(2);
				calculard(1);
			}
	</script>';
}

if($_POST['cmd']==11){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="if(document.forma.periodo.value==\'0\'){ alert(\'Necesita seleccionar el periodo\');} else if(document.forma.personal.value==\'0\'){ alert(\'Necesita seleccionar el empleado\')} else atcr(\'nomina_fiscal.php\',\'\',12,0);"><img src="images/guardar.gif" border="0"></a>&nbsp;&nbsp;Agregar&nbsp;&nbsp;</td>
			<td><a href="#" onclick="atcr(\'nomina_fiscal.php\',\'\',\'0\',\'\');"><img src="images/flecha-izquierda.gif" border="0"></a>&nbsp;&nbsp;Regresar&nbsp;&nbsp;</td>';
	echo '</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Periodo Nomina</td><td><select name="periodo" id="periodo"><option value="0">Seleccione</option>';
	$res=mysql_query("SELECT * FROM periodos_nomina WHERE empresa='".$_POST['cveempresa']."' ORDER BY fecha_fin DESC");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['nombre'].' del '.$row['fecha_ini'].' al '.$row['fecha_fin'].'(Sin generar)</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Empleado</td><td><select name="personal" id="personal"><option value="0">Seleccione</option>';
	$res=mysql_query("SELECT * FROM personal WHERE 1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['nombre'].'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
}

if($_POST['cmd']==0){
	echo '<style>
			.timbrado{
				color: #0000FF;
			}
		</style>';
	echo '<div id="dialog" style="display:none"></div>';
	echo '<div id="confirm" style="display:none"><h2>A quien desea enviar los archivos?</h2></div>';
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>
			<td><a href="#" onclick="atcr(\'nomina_fiscal.php\',\'_blank\',\'50\',\'\');"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Excel Detallado&nbsp;&nbsp;</td>
			<td><a href="#" onclick="atcr(\'imp_nomina_fiscal.php\',\'_blank\',\'\',\'SI\');"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir Listado&nbsp;&nbsp;</td>
			<td><a href="#" onclick="if(!$(\'.chkpersonal\').is(\':checked\')) alert(\'Necesita seleccionar al menos un personal\'); else atcr(\'imp_nomina_fiscal.php\',\'_blank\',\'1\',\'\');"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir Recibos&nbsp;&nbsp;</td>
			<td><a href="#" onclick="$(\'#confirm\').dialog(\'open\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;&nbsp;Enviar Email&nbsp;&nbsp;</td>';
	if(nivelUsuario()>1){
		echo '<td><a href="#" onclick="atcr(\'nomina_fiscal.php\',\'\',\'3\',\'\');"><img src="images/guardar.gif" border="0"></a>&nbsp;&nbsp;Guardar Nomina&nbsp;&nbsp;</td>';
		echo '<td><a href="#" onclick="if(confirm(\'Esta seguro de timbrar los recibos\')){ if(!$(\'.chkpersonal\').is(\':checked\')) alert(\'Necesita seleccionar al menos un personal\'); else atcr(\'nomina_fiscal.php\',\'\',\'6\',\'\');}"><img src="images/validosi.gif" border="0"></a>&nbsp;&nbsp;Timbrar Recibos de Nomina&nbsp;&nbsp;</td>';
		if(nivelUsuario()>2){
			echo '<td><a href="#" onclick="if(confirm(\'Esta seguro de cancelar los timbres\')){ if(!$(\'.chkpersonal\').is(\':checked\')) alert(\'Necesita seleccionar al menos un personal\'); else atcr(\'nomina_fiscal.php\',\'\',\'7\',\'\');}"><img src="images/validono.gif" border="0"></a>&nbsp;&nbsp;Cancelar Timbrado de Recibos de Nomina&nbsp;&nbsp;</td>';
		}
	}
	if($_POST['cveusuario']==1){
		echo '<td><a href="#" onclick="if(confirm(\'Esta seguro de abrir la nomina \'+$(\'#periodo option[value=\\\'\'+document.forma.periodo.value+\'\\\']\').html()+\'?\')) atcr(\'nomina_fiscal.php\',\'\',\'4\',document.forma.periodo.value);"><img src="images/guardar.gif" border="0"></a>&nbsp;&nbsp;Abrir Nomina&nbsp;&nbsp;</td>';
		echo '<td><a href="#" onclick="atcr(\'nomina_fiscal.php\',\'\',\'11\',0);"><img src="images/nuevo.gif" border="0"></a>&nbsp;&nbsp;Agregar Empleado al Periodo&nbsp;&nbsp;</td>';
	}
	echo '</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Periodo Nomina</td><td><select name="periodo" id="periodo">';
	$res=mysql_query("SELECT * FROM periodos_nomina WHERE empresa='".$_POST['cveempresa']."' ORDER BY fecha_fin DESC");
	while($row=mysql_fetch_array($res)){
		//$res1=mysql_query("SELECT fecha FROM personal_nomina WHERE empresa='".$_POST['cveempresa']."' AND tipo='1' AND perdiodo='".$row['cve']."'");
		//if(mysql_num_rows($res1)>0)
		if($row['generada_fiscal']==1){
			echo '<option value="'.$row['cve'].'"';
			$res1=mysql_query("SELECT * FROM personal_nomina WHERE periodo=".$row['cve']." AND tipo=1 AND uuid='' AND eliminada!=1");
			if(mysql_num_rows($res1) == 0){
				echo ' class="timbrado"';
				echo '>'.$row['nombre'].' del '.$row['fecha_ini'].' al '.$row['fecha_fin'].'(Timbrada)</option>';
			}
			else
				echo '>'.$row['nombre'].' del '.$row['fecha_ini'].' al '.$row['fecha_fin'].'(Generada)</option>';
		}
		else
			echo '<option value="'.$row['cve'].'">'.$row['nombre'].' del '.$row['fecha_ini'].' al '.$row['fecha_fin'].'(Sin generar)</option>';
	}
	echo '</select></td></tr>';
	if($_POST['plazausuario']>0){
		echo '<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
	}
	elseif(count($array_plaza)>1){
		echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" class="textField"><option value="0">---Seleccione---</option>';
		foreach($array_plaza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.' '.$array_plazanombre[$k].'</option>';
		}
		echo '</select></td></tr>';
	}
	else{
		foreach($array_plaza as $k=>$v) echo '<input type="hidden" name="plaza" id="plaza" value="'.$k.'">';
	}
	echo '<tr><td>Credencia</td><td><input type="text" name="credencial" id="credencial" class="textField"></td></tr>'; 
	echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" class="textField"></td></tr>'; 
	echo '<tr><td>Metodo de Pago</td><td><select name="metodo_pago" id="metodo_pago" class="textField"><option value="all">---Todos---</option>';
	foreach($array_tipo_pago as $k=>$v){
		echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Localidad</td><td><select name="localidad" id="localidad" class="textField"><option value="all">---Todas---</option>';
	foreach($array_localidad as $k=>$v){
		echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Mostrar</td><td><select name="mostrar" id="mostrar"><option value="all">Todos</option>
	<option value="1">Timbradas</option><option value="2">Sin Timbrar</option></select></td></tr>';
	echo '</table>';
	echo '<br>';
	//Listado
	echo '<div id="Resultados">';
	echo '</div>';
	echo '<script>
	
		function buscarRegistros(ordenamiento,orden)
		{
			document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
			objeto=crearObjeto();
			if (objeto.readyState != 0) {
				alert("Error: El Navegador no soporta AJAX");
			} else {
				objeto.open("POST","nomina_fiscal.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=1&localidad="+document.getElementById("localidad").value+"&credencial="+document.getElementById("credencial").value+"&mostrar="+document.getElementById("mostrar").value+"&plaza="+document.getElementById("plaza").value+"&metodo_pago="+document.getElementById("metodo_pago").value+"&periodo="+document.getElementById("periodo").value+"&nombre="+document.getElementById("nombre").value+"&cvemenu="+document.getElementById("cvemenu").value+"&cveusuario="+document.getElementById("cveusuario").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
				objeto.onreadystatechange = function()
				{
					if (objeto.readyState==4)
					{document.getElementById("Resultados").innerHTML = objeto.responseText;}
				}
			}
			document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
		}
		
		
		//Funcion para navegacion de Registros. 20 por pagina.
		function moverPagina(x) {
			document.getElementById("numeroPagina").value = x;
			buscarRegistros();
		}
		
		function mostrarDetalle(cvenomina){
			objeto=crearObjeto();
			if (objeto.readyState != 0) {
				alert("Error: El Navegador no soporta AJAX");
			} else {
				objeto.open("POST","nomina_fiscal.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=2&cvenomina="+cvenomina);
				objeto.onreadystatechange = function()
				{
					if (objeto.readyState==4)
					{
						$("#dialog").html(objeto.responseText);
						$("#dialog").dialog("open");
					}
				}
			}
		}
		
		$("#dialog").dialog({ 
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 600,
			height: 400,
			autoResize: true,
			position: "center",
			buttons: {
				"Cerrar": function(){ 
					$("#dialog").html("");
					$(this).dialog("close"); 
				}
			},
		}); 
		
		$("#confirm").dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 400,
			height: 100,
			autoResize: true,
			position: "center",
			buttons: {
				"Empresa": function(){ 
					atcr("nomina_fiscal.php","",101,2);
				},
				"Empleados": function(){ 
					atcr("nomina_fiscal.php","",101,1);
				},
				"Cancelar": function(){ 
					$(this).dialog("close"); 
				}
			},
		});
		
		buscarRegistros(0,0);
	</script>';
}

bottom();
?>
