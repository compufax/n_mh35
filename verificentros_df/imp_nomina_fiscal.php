<?php

include ("main.php"); 
include('fpdf153/fpdf.php');
include("numlet.php");	
require_once("phpqrcode/phpqrcode.php");

function no_quincena($periodo){
	if($periodo['aguinaldo'] == 1){
		return 24;
	}
	else{
		if(substr($periodo['fecha_ini'],5,5) == '12-16'){
			return 25;
		}	
		else{
			$mes=intval(substr($periodo['fecha_ini'],5,2));
			$dia=intval(substr($periodo['fecha_ini'],8,2));
			$quincena = $mes*2;
			if($dia==1) $quincena--;
			return $quincena;
		}
	}
}
/*** ARREGLOS ***********************************************************/

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['nombre'];
}

$rsPuestos=mysql_query("SELECT * FROM puestos WHERE 1 ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_puestos[$Puestos['cve']]=$Puestos['nombre'];
}


$res=mysql_query("SELECT * FROM tipo_regimen ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_regimen[$row['cve']]=$row['nombre'];
}

$res = mysql_query("SELECT * FROM periodos_nomina WHERE cve='".$_POST['periodo']."'");
$Periodo=mysql_fetch_array($res);
$dato1 = explode("-",$Periodo['fecha_ini']);
$dato2 = explode("-",$Periodo['fecha_fin']);
if(substr($Periodo['fecha_ini'],0,7) == substr($Periodo['fecha_ini'],0,7)){
	$periodo="Periodo del ".$dato1[2]." al ".$dato2[2]." de ".$array_meses[intval($dato1[1])]." del ".$dato1[0];
}
else{
	$periodo="Periodo del ".$dato1[2]." de ".$array_meses[intval($dato1[1])]." del ".$dato1[0]." al ".$dato2[2]." de ".$array_meses[intval($dato2[1])]." del ".$dato2[0];
}


if($_POST['cmd']==1){
	$fecha=$_POST['fec_nom'];
	$reg=$_POST['reg'];
	$select= " SELECT b.*,a.nombre,if(b.rfc='',a.rfc,b.rfc) as rfc,a.curp,a.tipo_regimen,a.imss,a.puesto,a.cve as numpersonal,a.departamento,a.plaza as plazaempleado,a.metodo_pago FROM personal_nomina as b 
	INNER JOIN personal as a ON (b.personal=a.cve)
	WHERE b.cve IN (".implode(",",$_POST['cvepersonal']).")";
	//if($_POST['plaza']>0) $select.=" AND a.plaza='".$_POST['plaza']."'";
	//if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
	$select.=" ORDER BY a.nombre";
	$rspersonal=mysql_query($select) or die(mysql_error());
	$pdf=new FPDF('P','mm','LETTER');
	$pdf->SetFillColor(240,240,240);
	/*$dato=explode("-",$fecha);
	if($dato[2]=="10") $periodo="Periodo del 01";
	elseif($dato[2]=="20") $periodo="Periodo del 11";
	else $periodo="Periodo del 21";
	$periodo.=" al ".$dato[2]." de ".$array_meses[intval($dato[1])]." del ".$dato[0];*/
	$barcodes = array();
	while($Personal=mysql_fetch_array($rspersonal)){
		$rsPlaza = mysql_query("SELECT * FROM plazas WHERE cve = '".$Personal['plazatimbro']."'");
		$Plaza = mysql_fetch_array($rsPlaza);
		$rsEmpresa=mysql_query("SELECT * FROM datosempresas WHERE plaza='".$Personal['plazatimbro']."'");
		$Empresa=mysql_fetch_array($rsEmpresa);
		$Plaza['registro_patronal']=$Personal['registro_patronal'];
		/*if($Empresa['check_sucursal']==1){
			$Empresa['nombre'] = $Empresa['nombre_sucursal'];
			$Plaza['calle']=$Empresa['calle_sucursal'];
			$Plaza['numexterior']=$Empresa['numero_sucursal'];
			$Plaza['numinterior']='';
			$Plaza['colonia']=$Empresa['colonia_sucursal'];
			$Plaza['municipio']=$Empresa['municipio_sucursal'];
			$Plaza['estado']=$Empresa['estado_sucursal'];
			$Plaza['codigopostal']=$Empresa['cp_sucursal'];
		}
		else{*/
			
			$Plaza['calle']=$Personal['calle'];
			$Plaza['numexterior']=$Personal['numexterior'];
			$Plaza['numinterior']=$Personal['numinterior'];
			$Plaza['colonia']=$Personal['colonia'];
			$Plaza['municipio']=$Personal['municipio'];
			$Plaza['estado']=$Personal['estado'];
			$Plaza['codigopostal']=$Personal['codigopostal'];
			$Empresa['nombre'] = $Personal['nombre_empresa'];
			$Plaza['nombre'] = $Personal['nombre_empresa'];
			$Empresa['rfc'] = $Personal['rfc_empresa'];
		//}
		
		$pdf->AddPage('P');
		$pdf->SetFont('Arial','B',12);
		if($Personal['cancelacion_sat']!=''){
			if(file_exists("images/cancelado.jpg")) $pdf->Image("images/cancelado.jpg",10,45,190,200);
		}
		if(file_exists("logos/logo".$Plaza['cve'].".jpg")){
			// img, w, h, x, y
			$pdf->Image("logos/logo".$Plaza['cve'].".jpg",110, 20, 40);
			
		}

		if(file_exists("images/recibo_nomina_200x80.jpg")){
			// img, w, h, x, y
			$pdf->Image("images/recibo_nomina_200x80.jpg",160, 20, 40);
		}		

		$pdf->Cell(190,5,$Empresa['nombre'],0,0,'C');
		$pdf->Ln();
		$re=$Empresa['rfc'];
		$rr=$Personal['rfc'].$Personal['homoclave'];
		$pdf->SetFont('Arial','B',10);
		if($Plaza['numinterior']!="") $Plaza['numexterior'].' - '.$Plaza['numinterior'];
		$pdf->MultiCell(190,5,'PLAZA: '.$Plaza['nombre'].'  
RFC: '.$Empresa['rfc'].' 
No. Registro Patronal: '.substr($Plaza['registro_patronal'],0,3).'-'.substr($Plaza['registro_patronal'],3,5).'-'.substr($Plaza['registro_patronal'],8,2).'-'.substr($Plaza['registro_patronal'],10).'
'.$Plaza['calle'].' '.$Plaza['numexterior'].', '.$Plaza['colonia'],0,'J');   
		$pdf->Cell(190,5,$Plaza['municipio'].', '.$Plaza['estado'].', C.P. '.$Plaza['codigopostal'],0,0,'L');
		$pdf->Ln();
		$pdf->Cell(190,5,'Régimen General de Ley','B',0,'L');
		$pdf->Ln();
		//$pdf->SetFont('Arial','B',12);
		//$pdf->Cell(190,5,"Recibo de Nomina",0,0,'C');
		//$pdf->Ln();
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(130,4.5,$Personal['numpersonal'].' '.$Personal['nombre'],0,0,'L');
		$pdf->Cell(60,4.5,"CURP: ".$Personal['curp'],0,0,'L');
		$pdf->Ln();
		$pdf->Cell(130,4.5,"RFC: ".$Personal['rfc'].$Personal['homoclave'],0,0,'L');
		$pdf->Cell(60,4.5,"NOMINA: ".no_quincena($Periodo),0,0,'L');
		$pdf->Ln();
		if($Personal['recibo_honorarios']==1)
			$pdf->Cell(130,4.5,"",0,0,'L');
		else
			$pdf->Cell(130,4.5,"R.IMSS: ".$Personal['imss'],0,0,'L');
		$pdf->Cell(60,4.5,"Días Trabajados: ".$Personal['dias_tra'],0,0,'L');
		$pdf->Ln();
		$pdf->Cell(130,4.5,"Régimen: ".$array_tipo_regimen[$Personal['tipo_regimen']],0,0,'L');
		$pdf->Cell(60,4.5,"Faltas: ".$Personal['faltas'],0,0,'L');
		$pdf->Ln();
		$pdf->Cell(130,4.5,"Puesto: ".$array_puestos[$Personal['puesto']],0,0,'L');
		$pdf->Cell(60,4.5,"Periodo del: ".$Periodo['fecha_ini'],0,0,'');
		$pdf->Ln();
		$pdf->Cell(130,4.5,"",'B',0,'L');
		$pdf->Cell(60,4.5,"         al: ".$Periodo['fecha_fin'],'B',0,'L');
		$pdf->Ln();
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(120,4,"");
		$pdf->Cell(35,4,"Percepciones",0,0,'R');
		$pdf->Cell(35,4,"Deducciones",0,0,'R');
		$pdf->Ln();
		$pdf->SetFont('Arial','',10);
		$res1=mysql_query("SELECT b.cve, b.nombre, a.total FROM personal_nomina_percepcion a INNER JOIN cat_percepciones b on a.percepcion=b.cve WHERE a.nomina='".$Personal['cve']."' AND a.total>0");
		$res2=mysql_query("SELECT b.cve, b.nombre, a.total, a.dias FROM personal_nomina_deduccion a INNER JOIN cat_deducciones b on a.deduccion=b.cve WHERE a.nomina='".$Personal['cve']."' AND a.total>0");
		while($row1=mysql_fetch_array($res1)){
			if($row1['cve']==1 && $Personal['recibo_honorarios']==1) $row1['nombre'] = 'Importe de Honorarios Asimilables';
			$pdf->Cell(120,4,$row1['nombre'],0,0,'L');$pdf->Cell(35,4,number_format($row1['total'],2),0,0,'R');$pdf->Cell(35,4,"",0,0,'R');$pdf->Ln();
		}
		while($row2=mysql_fetch_array($res2)){
			if($row2['cve']==2 && $Personal['recibo_honorarios']==1) $row2['nombre'] = '';
			if($row2['cve']==2 && $Personal['recibo_honorarios']!=1) $row2['nombre'] .= ' (Dias: '.$row2['dias'].')'; 
			$pdf->Cell(120,4,$row2['nombre'],0,0,'L');$pdf->Cell(35,4,"",0,0,'R');$pdf->Cell(35,4,number_format($row2['total'],2),0,0,'R');$pdf->Ln();
		}
		if($Personal['cancelacion_sat']!=""){
			$pdf->SetXY(10,95);
		}
		else{
			$pdf->SetXY(10,100);
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
		if($Personal['recibo_honorarios']==1)
			$pdf->MultiCell(190,4,'Se extiende este recibo en términos del articulo 78, fracc. IV de la ley de impuesto sobre la renta.
			
			Exento del impuesto al valor agregado de conformidad con el articulo 14, penúltimo párrafo.',0,'J');
		else
			$pdf->MultiCell(190,4,'Hago constar que con este pago me ha sido liquidado totalmente mi salario y prestaciones de ley que a la fecha tengo derecho',0,'J');
		$pdf->Ln();
		//if($row['check_sucursal']==1){
			$expedido_en = $Empresa['calle_sucursal'].' '.$Empresa['numero_sucursal'].', '.$Empresa['colonia_sucursal'].' '.$Empresa['localidad_sucursal'].', C.P. '.$Empresa['cp_sucursal'].', '.$Empresa['municipio_sucursal'].', '.$Empresa['estado_sucursal'];
			$pdf->SetFont("Arial","B",8);
			$pdf->MultiCell(190,4,'Lugar de Expedicion: ' . $expedido_en, 0,"L",0);
			$pdf->Ln();
		//}
		//else{
			//$pdf->Cell(100,4,'Lugar de Expedicion: '.$row1['municipio'].', '.$row1['estado'].'',0,0,"L",0);
		//}
		$pdf->Ln();
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(190,4,"FIRMA:");
		/*$pdf->SetY(140);
		$pdf->Cell(190,5,$Empresa['nombre'],0,0,'C');
		$pdf->Ln();
		$pdf->SetFont('Arial','B',10);
		if($Empresa['numinterior']!="") $Empresa['numexterior'].' - '.$Empresa['numinterior'];
		$pdf->MultiCell(190,5,$Empresa['nombre'].'  '.$Empresa['rfc'].'  '.$Empresa['regimen'].'
'.$Empresa['calle'].' '.$Empresa['numexterior'].', '.$Empresa['colonia'],0,'J');   
		$pdf->Cell(190,5,$Empresa['municipio'].', '.$Empresa['estado'].', C.P. '.$Empresa['codigo_postal'],'B',0,'L');
		$pdf->Ln();
		//$pdf->SetFont('Arial','B',12);
		//$pdf->Cell(190,5,"Recibo de Nomina",0,0,'C');
		//$pdf->Ln();
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(190,5,$Personal['folio'].' '.$Personal['nombre'],0,0,'L');
		$pdf->Ln();
		$pdf->Cell(130,5,"RFC: ".$Personal['rfc'],0,0,'L');
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
		$pdf->Cell(120,4,"P001 SUELDO ",0,0,'L');$pdf->Cell(35,4,number_format($Personal['sal_diario']*$Personal['dias_tra'],2),0,0,'R');$pdf->Cell(35,4,"",0,0,'R');$pdf->Ln();
		if($Personal['tiempo_ex']>0){
			$pdf->Cell(120,4,"TIEMPO EXTRA ",0,0,'L');$pdf->Cell(35,4,number_format($Personal['tiempo_ex'],2),0,0,'R');$pdf->Cell(35,4,"",0,0,'R');$pdf->Ln();
		}
		if($Personal['isr']>0){
			$pdf->Cell(120,4,"ISR ",0,0,'L');$pdf->Cell(35,4,"",0,0,'R');$pdf->Cell(35,4,number_format($Personal['isr'],2),0,0,'R');$pdf->Ln();
		}
		if($Personal['imp_imss']>0){
			$pdf->Cell(120,4,"D002 IMSS ",0,0,'L');$pdf->Cell(35,4,"",0,0,'R');$pdf->Cell(35,4,number_format($Personal['imp_imss'],2),0,0,'R');$pdf->Ln();
		}
		if($Personal['otras_per']>0){
			$pdf->Cell(120,4,"D100 SUBSIDIO PARA EL EMPLEO ",0,0,'L');$pdf->Cell(35,4,"",0,0,'R');$pdf->Cell(35,4,'-'.number_format($Personal['otras_per'],2),0,0,'R');$pdf->Ln();
		}
		if($Personal['imp_infonavit']>0){
			$pdf->Cell(120,4,"INFONAVIT ",0,0,'L');$pdf->Cell(35,4,"",0,0,'R');$pdf->Cell(35,4,number_format($Personal['imp_infonavit'],2),0,0,'R');$pdf->Ln();
		}
		if($Personal['prestamo']>0){
			$pdf->Cell(120,4,"PRESTAMO ",0,0,'L');$pdf->Cell(35,4,"",0,0,'R');$pdf->Cell(35,4,number_format($Personal['prestamo'],2),0,0,'R');$pdf->Ln();
		}
		if($Personal['otras_ded']>0){
			$pdf->Cell(120,4,"OTRAS DEDUCCIONES ",0,0,'L');$pdf->Cell(35,4,"",0,0,'R');$pdf->Cell(35,4,number_format($Personal['otras_ded'],2),0,0,'R');$pdf->Ln();
		}
		$totper=($Personal['sal_diario']*$Personal['dias_tra'])+$Personal['tiempo_ex'];
		$totded=$Personal['isr']+$Personal['imp_imss']+$Personal['otras_ded']+$Personal['imp_infonavit']+$Personal['prestamo']-$Personal['otras_per'];
		$pdf->SetXY(10,230);
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(120,4,"TOTALES ",'T',0,'L');$pdf->Cell(35,4,number_format($totper,2),'T',0,'R');$pdf->Cell(35,4,number_format($totded,2),'T',0,'R');
		$pdf->Ln();
		$pdf->Cell(120,4,"Neto Pagado ",0,0,'L');$pdf->Cell(35,4,"",0,0,'R');$pdf->Cell(35,4,number_format($totper-$totded,2),0,0,'R');
		$pdf->Ln();
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(120,5,"Total en Efectivo ",0,0,'L');$pdf->Cell(35,5,"",0,0,'R');$pdf->Cell(35,5,number_format($totper-$totded,2),0,0,'R');
		$pdf->Ln();
		$pdf->SetFont('Arial','',10);
		$pdf->MultiCell(190,4,'Hago constar que con este pago me ha sido liquidado totalmente mi salario y prestaciones de ley que a la fecha tengo derecho',0,'J');
		$pdf->Ln();
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(190,4,"FIRMA:");*/
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
		//$pdf->Ln();
		$pdf->SetX(45);
		$pdf->Cell(162.5,6.5,"SELLO DIGITAL EMISOR",1,0,"C",1);
		$pdf->Ln();
		$pdf->SetX(45);
		$pdf->MultiCell(162.5,4,$Personal['sellodocumento'],0,"J",0);
		//$pdf->Ln();
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
		if(!file_exists("cfdi/comprobantes/barcoden_".$Personal['empresa'].'_'.$Personal['cve'].".png")) QRcode::png("?re=".$re."&rr=".$rr."&tt=".$tt."&id=".$Personal['uuid'],"cfdi/comprobantes/barcoden_".$Personal['empresa'].'_'.$Personal['cve'].".png","L",4,0);
		if(file_exists("cfdi/comprobantes/barcoden_".$Personal['empresa'].'_'.$Personal['cve'].".png")) $pdf->Image("cfdi/comprobantes/barcoden_".$Personal['empresa'].'_'.$Personal['cve'].".png",10,$y,34,34);
		$barcodes[] = "cfdi/comprobantes/barcoden_".$Personal['empresa'].'_'.$Personal['cve'].".png";
	}
	$pdf->Output();
	foreach($barcodes as $barcode)
	{
		@unlink($barcode);
	}
	exit();
}


$fecha=$_POST['fec_nom'];
$reg=$_POST['reg'];
$select= " SELECT b.*,a.nombre,a.puesto,if(b.rfc='',a.rfc,b.rfc) as rfc,a.imss FROM personal as a 
INNER JOIN personal_nomina as b ON (b.personal=a.cve AND b.tipo='1' AND b.eliminada!=1)
INNER JOIN datosempresas c ON c.plaza = IF(b.plazatimbro!=0,b.plazatimbro,a.plaza)
WHERE b.periodo='".$_POST['periodo']."'";
if($_POST['plaza']>0) $select.=" AND IF(IFNULL(b.plazatimbro,0)!=0,b.plazatimbro,a.plaza)='".$_POST['plaza']."'";
if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
if ($_POST['metodo_pago']!="all") $select.=" AND a.metodo_pago = '".$_POST['metodo_pago']."'";
$select.=" ORDER BY a.nombre";
$rspersonal=mysql_query($select);

class FPDF2 extends PDF_MC_Table {
	function Header(){
		global $plaza,$array_plaza,$numcond,$reg,$fecha,$array_meses,$Empresa,$Periodo;
		$this->SetFont('Arial','B',10);
		if($Empresa['numinterior']!="") $Empresa['numexterior'].' - '.$Empresa['numinterior'];
		$this->MultiCell(270,5,$Empresa['nombre'],0,'C');
		$this->MultiCell(270,5,$Empresa['calle'].' '.$Empresa['numexterior'].', '.$Empresa['colonia'],0,'J'); 
		$this->Cell(135,5,$Empresa['municipio'].', '.$Empresa['estado'].', C.P. '.$Empresa['codigopostal'],0,0,'L');
		$this->Cell(135,5,'Registro Patronal IMSS : '.$Empresa['regimen'],0,0,'R');
		$this->Ln();
		$this->Cell(135,5,'Periodo de Pago: '.$Periodo['fecha_ini'].' a '.$Periodo['fecha_fin'],0,0,'L');
		$this->Cell(135,5,'Registro Federal de Causantes: '.$Empresa['rfc'],0,0,'R');
		$this->Ln();
		$this->SetFont('Arial','B',12);
		//$this->Cell(190,10,'Autobuses Rapidos del Valle de Mexico',0,0,'C');
		$this->Ln();
		//$this->SetY(23);
		$tit='';
		$datos=explode("-",$fecha);
		if($datos[2]=="10") $num="primera";
		elseif($datos[2]=="20") $num="segunda";
		else $num="tercera";
		$mes=$array_meses[intval($datos[1])];
		$anio=$datos[0];
		$this->MultiCell(270,5,'Nomina de los Empleados
		'.$periodo,0,'C');
		$this->Ln();
		$this->SetFont('Arial','B',9);
		$this->Cell(100,4,' ',0,0,'C',0);
		$this->Cell(30,4,' ',0,0,'C',0);
		$this->Cell(30,4,' ',0,0,'C',0);
		$this->Cell(30,4,' ',0,0,'C',0);
		$this->Cell(20,4,'Total',0,0,'C',0);
		$this->Cell(20,4,'Total',0,0,'C',0);
		$this->Cell(20,4,'Total',0,0,'C',0);
		$this->Ln();
		$this->SetFont('Arial','B',9);
		$this->Cell(100,4,'Nombre',0,0,'C',0);
		$this->Cell(30,4,'Puesto',0,0,'C',0);
		$this->Cell(30,4,'R.F.C.',0,0,'C',0);
		$this->Cell(30,4,'N.S.S.',0,0,'C',0);
		$this->Cell(20,4,'Percep.',0,0,'C',0);
		$this->Cell(20,4,'Deduc.',0,0,'C',0);
		$this->Cell(20,4,'a Pagar',0,0,'C',0);
		$this->Cell(32.5,4,'Firma',0,0,'C',0);
		$this->Ln();		
	}
	
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

$pdf=new FPDF2('L','mm','A4');
$i=0;	
$total=0;
$primero = true;
while($Personal=mysql_fetch_array($rspersonal)) {
	$rsPlaza = mysql_query("SELECT * FROM plazas WHERE cve = '".$Personal['plazatimbro']."'");
	$Plaza = mysql_fetch_array($rsPlaza);
	$rsEmpresa=mysql_query("SELECT * FROM datosempresas WHERE plaza='".$Personal['plazatimbro']."'");
	$Empresa=mysql_fetch_array($rsEmpresa);
	$Empresa['calle']=$Personal['calle'];
	$Empresa['numexterior']=$Personal['numexterior'];
	$Empresa['numinterior']=$Personal['numinterior'];
	$Empresa['colonia']=$Personal['colonia'];
	$Empresa['municipio']=$Personal['municipio'];
	$Empresa['estado']=$Personal['estado'];
	$Empresa['codigopostal']=$Personal['codigopostal'];
	$Empresa['nombre'] = $Personal['nombre_empresa'];
	$Empresa['rfc'] = $Personal['rfc_empresa'];
	if($primero){
		
		$pdf->AliasNbPages();
		$pdf->AddPage('L');

		$pdf->SetFont('Arial','',9);
		$pdf->SetWidths(array(100,30,30,30,20,20,20,32.5));
		$pdf->SetAligns(array('L','L','C','C','R','R','R','C'));
		$primero = false;
	}
	
	$renglon=array();
	$renglon[]=$Personal['nombre'];
	$renglon[]=$array_puestos[$Personal['puesto']];
	$renglon[]=$Personal['rfc'].$Personal['homoclave'];
	$renglon[]=$Personal['imss'];
	$renglon[]=number_format($Personal['totalpercepciones'],2);
	$renglon[]=number_format($Personal['totaldeducciones'],2);
	$renglon[]=number_format($Personal['totalpercepciones']-$Personal['totaldeducciones'],2);
	$renglon[]="               _________________";
	if(($i%2)==0){
		$pdf->RowGetColor($renglon,array(238,238,238),0,5);
		//$renglon=array(" "," "," "," "," "," "," "," ");
		//$pdf->RowGetColor($renglon,array(238,238,238),0,2);
	}
	else{
		$pdf->RowGetColor($renglon,array(255,255,255),0,5);
		//$renglon=array(" "," "," "," "," "," "," "," ");
		//$pdf->RowGetColor($renglon,array(255,255,255),0,2);
	}
	$i++;
	$tper+=$Personal['totalpercepciones'];
	$tded+=$Personal['totaldeducciones'];
	$total+=($Personal['totalpercepciones']-$Personal['totaldeducciones']);
}
$pdf->Ln();
$pdf->Cell(160,5,$i." Registro(s)");
$pdf->Cell(30,5,"Total: ",0,0,'R');
$pdf->Cell(20,5,number_format($tper,2),0,0,'R');
$pdf->Cell(20,5,number_format($tded,2),0,0,'R');
$pdf->Cell(20,5,number_format($total,2),0,0,'R');


$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$rsfirmas=mysql_db_query($base,"SELECT * FROM administradores WHERE nomina='1' AND plaza='".$_POST['plaza']."' AND fecha_ini<='".$row['fecha']."' AND (fecha_fin>='".$row['fecha']."' OR fecha_fin='0000-00-00')");
$array_administrativos=array();
$array_administrativospuestos=array();
while($Firmas=mysql_fetch_array($rsfirmas)){

	$array_administrativos[$i]=$Firmas['nombre'];
	$array_administrativospuestos[$i]=$array_puestos[$Firmas['puesto']];
	$i++;
}
if(count($array_administrativos)>0){
	$ancho=190/count($array_administrativos);
	$i=0;
	$pdf->SetFont('Arial','U',8);
	if($pdf->GetY()<90) $pdf->SetXY(10,90);
	foreach($array_administrativos as $k=>$v){
		$pdf->Cell($ancho,3,$v,0,0,'C');
		$i++;
	}
	$pdf->Ln();
	$pdf->SetFont('Arial','',8);
	foreach($array_administrativospuestos as $k=>$v){
		$pdf->Cell($ancho,3,$v,0,0,'C');
	}
}
/*$pdf->Cell(40,5,"_______________________________________",0,0,'C');
$pdf->Cell(5,5," ",0,0,'C');
$pdf->Cell(40,5,"_______________________________________",0,0,'C');
$pdf->Cell(5,5," ",0,0,'C');
$pdf->Cell(40,5,"_______________________________________",0,0,'C');
$pdf->Cell(5,5," ",0,0,'C');
$pdf->Cell(40,5,"_______________________________________",0,0,'C');
$pdf->Ln();
$pdf->Cell(40,5,"Elaboro",0,0,'C');
$pdf->Cell(5,5," ",0,0,'C');
$pdf->Cell(40,5,"Reviso",0,0,'C');
$pdf->Cell(5,5," ",0,0,'C');
$pdf->Cell(40,5,"Autorizo",0,0,'C');
$pdf->Cell(5,5," ",0,0,'C');
$pdf->Cell(40,5,"Vo.Bo.",0,0,'C');
$pdf->Ln();
$pdf->Cell(60,5,"Srita. Veronica Castañeda R.",0,0,'C');
$pdf->Cell(20,5," ",0,0,'C');
$pdf->Cell(60,5," Sr. Hilario Ramirez Martinez",0,0,'C');
$pdf->Cell(20,5," ",0,0,'C');
$pdf->Cell(60,5,"Sr. Luis Manuel Dias Flores",0,0,'C');
$pdf->Cell(20,5," ",0,0,'C');
$pdf->Cell(60,5,"C. Joel Hernandez Vera",0,0,'C');*/
$pdf->Output();	


?>