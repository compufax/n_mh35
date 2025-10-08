<?
include ("main.php"); 
include('fpdf153/fpdf.php');
include("numlet.php");	
/*** ARREGLOS ***********************************************************/

$rsPlaza=mysql_query("SELECT * FROM plazas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['nombre'];
}

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['nombre'];
}

$rsPuestos=mysql_query("SELECT * FROM puestos ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_puestos[$Puestos['cve']]=$Puestos['nombre'];
}

function fecha_texto($f) {

	$meses=array('','ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE');

	$fecha=substr($f,-2).' DE '.$meses[intval(substr($f,5,2))].' DE '.substr($f,0,4);

	return $fecha;

}

if($_POST['cmd']==3){
	$rsBenef=mysql_query("SELECT * FROM personal");
	while($Benef=mysql_fetch_array($rsBenef)){
		$array_personal[$Benef['cve']]=$Benef['nombre'];
	}
	
	$rsMotivo=mysql_query("SELECT * FROM motivos_chequera");
	while($Motivo=mysql_fetch_array($rsMotivo)){
		$array_motivo[$Motivo['cve']]=$Motivo['nombre'];
	}
	$pdf=new PDF_MC_Table('P','mm','A4');

	$pdf->Open();
	
	$plaza=$_POST['plaza'];
	$fecha=$_POST['fec_nom'];
	$reg=$_POST['reg'];
	$select= " SELECT b.*,a.nombre FROM personal as a 
	INNER JOIN personal_nomina as b ON (b.personal=a.cve AND b.fecha='$fecha' AND b.tipo='1' AND b.salida>0 AND b.tipo_salida = 1)
	WHERE 1";
	if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
	if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; }
	$select.=" ORDER BY a.nombre";
	$rspersonal=mysql_query($select) or die(mysql_error());
	$z=0;
	while($Personal=mysql_fetch_array($rspersonal)){
		$result=mysql_query("select * from chequera where cve='".$Personal['salida']."' ");
		$row=mysql_fetch_array($result);
		

		if($row['folio_accidente']>0){
			$rs=mysql_query("SELECT * FROM accidentes WHERE cve='".$row['folio_accidente']."'");
			$ro=mysql_fetch_array($rs);
			$row['concepto'].="
			Accidente #".$ro['folio'].", Fecha: ".$ro['fecha']."
			Unidad: ".$array_eco[$ro['unidad']]." - ".$array_unidad[$ro['unidad']]."
			Conductor: ".$array_credencial[$ro['conductor']]." - ".$array_conductor[$ro['conductor']];
		}
		
		if($row['tipo_beneficiario']==0) $nombre=$array_benef[$row['beneficiario']];
		elseif($row['tipo_beneficiario']==1) $nombre=$array_unidad[$row['beneficiario']];
		elseif($row['tipo_beneficiario']==2) $nombre=$array_conductor[$row['beneficiario']];
		elseif($row['tipo_beneficiario']==3) $nombre=$array_personal[$row['beneficiario']];
		else $nombre=$row['nombeneficiario'];



		$result3=mysql_query("select * from cuentas_chequera where cve='".$row['cuenta']."' ");

		$row3=mysql_fetch_array($result3);
		$copia=$row3['copia'];
		$banco=$row3['banco'];
		

		$res4=mysql_query("select nombre from referencias_chequera where cve='".$row['ref']."' ");

		$row4=mysql_fetch_array($res4);



		/*** Creamos el objeto y opciones para la pagina***/


		if($row['fecha']<'2007-07-06') $tesorero='ENRIQUE MONTERO GARCIA';

		else $tesorero='BERNARDINO GARCIA VAZQUEZ';
		
		if($row['tp']==0){
			$pdf->AddPage();

			$pdf->SetFont('Arial','',8);

			$pdf->SetFillColor(217, 249, 238);

			$pdf->Ln(10);

			//$pdf->Rect(5,5,200,42,"DF"); //( x, y, width 0=todo el ancho, height )

			//fecha

			$pdf->SetFont('Arial','B',14);

			$pdf->Cell(95,5," ",0,0,'C',0);	

			$pdf->SetFont('Arial','',10);	
			$datos=explode(",",$row3['coorfecha']);
			$pdf->SetXY($datos[0],$datos[1]);
			$pdf->Cell(10,5,fecha_texto($row['fecha']),0,0,'L',0);

			//nombre e importe

			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln(2.5);
			
			if($row["estatus"]!=1){
				$pdf->SetFont('Arial','',10);
			}
			else{
				$pdf->SetFont('Arial','',10);
				$row['monto']=0;
			}
			
			$datos=explode(",",$row3['coorbeneficiario']);
			$pdf->SetXY($datos[0],$datos[1]);
			$pdf->Cell(10,5,$nombre,0,0,'L',0);
			$pdf->SetFont('Arial','',10);
			$datos=explode(",",$row3['coormonto']);
			$pdf->SetXY($datos[0],$datos[1]);
			$pdf->Cell(10,5,number_format($row['monto'],'2','.',','),0,0,'L',0);

			//cantidad con letra

			$pdf->Ln();
			$pdf->Ln(2.5);
			$datos=explode(",",$row3['coormontoletra']);
			$pdf->SetXY($datos[0],$datos[1]);
			$pdf->Cell(180,5,numlet($row['monto']),0,0,'C',0);

			//cuenta y cheque 

			$pdf->Ln();

			$pdf->Ln();
			$nomtip="CHEQUE";
		}
		else{
			$nomtip="DEPOSITO";
		}
		$c=1;
		if($copia>0)
			$c=2;
		
		for($h=1;$h<=$c;$h++) {	

			$pdf->AddPage();
			$pdf->Image('images/membrete.JPG',30,3,150,15);
			$pdf->SetFont('Arial','',8);

			$pdf->SetFillColor(217, 249, 238);

			$pdf->Ln(10);
			$pdf->SetFont('Arial','B',14);
			$pdf->Cell(180,5,"POLIZA DE ".$nomtip,0,0,'C');
			$pdf->Ln(10);
			$pdf->Rect(5,25,200,42,"DF"); //( x, y, width 0=todo el ancho, height )

			//fecha

			$pdf->SetFont('Arial','B',14);

			$pdf->Cell(95,5," ",0,0,'C',1);	

			$pdf->SetFont('Arial','',10);	

			$pdf->Cell(90,5,fecha_texto($row['fecha']),0,0,'R',1);

			//nombre e importe

			$pdf->Ln();
			if($h==2)$pdf->Cell(120,5,'COPIA',0,0,'C',1);
			$pdf->Ln();
			
			if($row["estatus"]!=1){
				$pdf->SetFont('Arial','',10);
			}
			else{
				$pdf->SetFont('Arial','',10);
				$row['monto']=0;
			}

			$pdf->Cell(120,5,$nombre,0,0,'C',1);
			$pdf->SetFont('Arial','',10);
			$pdf->Cell(70,5,number_format($row['monto'],'2','.',','),0,0,'R',1);

			//cantidad con letra

			$pdf->Ln();

			$pdf->Ln();

			$pdf->Cell(180,5,numlet($row['monto']),0,0,'C',1);

			//cuenta y cheque 

			$pdf->Ln();

			$pdf->Cell(180,5,"MOTIVO: ".$array_motivo[$row['motivo']],0,0,'C',1);
			
			$pdf->Ln();

			$pdf->Cell(60,5,"CUENTA: ".$row3['cuenta'],0,0,'L',1);

			//$pdf->Cell(70,5,"MOTIVO: ".$array_motivo[$row['motivo']],0,0,'C',1);

			$pdf->Cell(60,5,$nomtip.": ".$row['folio'],0,0,'R',1);
			$pdf->Cell(60,5,"BANCO: ".$banco,0,0,'R',1);
			//encabezados concepto y firma 

			$pdf->Ln();

			$pdf->Ln();

			$pdf->SetX(5);

			$pdf->Cell(135,5," ",0,0,'C',0);

			$pdf->Cell(65,5," ",0,0,'C',0);

			//textos concepto y firma

			$pdf->Ln();

			$Y=$pdf->GetY()-5;

			$pdf->SetX(5);

			/*if($h==2) {

				$pdf->SetFont('Arial','B',12);

				$pdf->MultiCell(133,5,"GASTOS POR COMPROBAR",0,'C',0);

			}	

			else */	
				if($h==2){$pdf->Cell(133,5,'COPIA',0,0,'C',1);$pdf->Ln();}
				$pdf->MultiCell(133,5,$row['concepto'],0,'C',0);

			$pdf->SetFont('Arial','',8);		

			//rectangulos grandes de concepto y firma

			$pdf->Rect(5,$Y,133,30,"D"); //( x, y, width 0=todo el ancho, height )

			$pdf->Rect(140,$Y,65,30,"D"); //( x, y, width 0=todo el ancho, height )

			$pdf->Ln();

			//rows

			$pdf->SetWidths(array(30,30,50,30,30,30));

			$Y=$pdf->GetY()+18;

			$pdf->SetY($Y);

			$pdf->SetX(5);

			$pdf->RowHeaderGetColor(array("CUENTA","SUBCUENTA","NOMBRE","PARCIAL","DEBE","HABER"),array(255, 153, 51),1);

			for($i=0;$i<6;$i++) {

				$pdf->SetX(5);

				if(fmod($i,2)==0) 

					$color=array(255,255,255);

				else

					$color=array(255,255,153);

				$pdf->RowGetColor(array($row['cuenta'.$i]." ",$row['subcuenta'.$i]." ",$row['nombre'.$i]." ",$row['parcial'.$i]." ",$row['debe'.$i]." ",$row['haber'.$i]." "),$color,1);

			}

			/*//presidente, tesorero y secretario

			$pdf->SetX(5);

			$pdf->SetFillColor(255,255,255);
			$rsfirmas=mysql_query("SELECT * FROM cargos_admon WHERE chequera='Si' AND (plaza='".$row['plaza']."' OR plaza='0') AND fecha<='".$row['fecha']."' AND (fechaf>='".$row['fecha']."' OR fechaf='0000-00-00')");
			$numfirmas=mysql_num_rows($rsfirmas);
			$ancho=200/$numfirmas;
			$array_puestoadmon=array();
			$i=0;
			while($Firmas=mysql_fetch_array($rsfirmas)){
				$pdf->Cell($ancho,5,$Firmas['nombre'],1,0,'C',1);
				$array_puestoadmon[$i]=$array_puesto[$Firmas['puesto']];
				$i++;
			}
			$pdf->Ln();
			$pdf->SetX(5);
			$pdf->SetFillColor(255,255,255);
			for($x=0;$x<$i;$x++){
				$pdf->Cell($ancho,5,$array_puestoadmon[$x],1,0,'C',1);
			}
			$pdf->Ln();

			/*for($i=1;$i<8;$i++) {

				$pdf->SetX(5);

				if(fmod($i,2)==0) 

					$color=array(255,255,255);

				else

					$color=array(255,255,153);

				$pdf->RowGetColor(array(" "," "," "," "," "," "),$color,1);

			}*/



			//firmas en pie de pagina

			//encabezados concepto y firma 

			/*$pdf->Ln();

			$pdf->SetX(5);

			$pdf->Cell(66,5,"ELABORADO POR: ",0,0,'L',0);

			$pdf->SetX(72);

			$pdf->Cell(65,5,"AUTORIZADO POR: ",0,0,'L',0);

			$pdf->SetX(139);

			$pdf->Cell(66,5,"POLIZA NUM:: ",0,0,'L',0);

			//textos concepto y firma

			$pdf->Ln();

			$Y=$pdf->GetY()-5;

			$pdf->SetX(5);

			$pdf->Cell(66,5,$array_usuario[$row['emite']],0,0,'C',0);

			$pdf->Cell(65,5," ",0,0,'C',0);

			$pdf->Cell(66,5," ",0,0,'C',0);

			//rectangulos grandes de concepto y firma

			$pdf->Rect(5,$Y,66,15,"D"); //( x, y, width 0=todo el ancho, height )

			$pdf->Rect(72,$Y,66,15,"D"); //( x, y, width 0=todo el ancho, height )

			$pdf->Rect(139,$Y,66,15,"D"); //( x, y, width 0=todo el ancho, height )*/



		}

		if($row['credito']>0 && $row['estatus']!=1){
			$select=" SELECT * FROM credito_parque WHERE folio='".$row['credito']."' ";
			$res1=mysql_query($select);
			$row1=mysql_fetch_array($res1);
			$pdf->AddPage();
			$pdf->SetFont('Arial','',8);
			$pdf->SetFillColor(217, 249, 238);
			$pdf->Ln(25);
			$pdf->SetFont('Arial','',10);	
			$pdf->Cell(90,5,'PARA ABONO EN CUENTA',0,0,'L',0);
			$pdf->Ln();
			$pdf->Cell(90,5,$row1['arrendadora'],0,0,'L',0);
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Cell(90,5,$row1['sucursal'],0,0,'L',0);
			$pdf->Cell(90,5,$row1['referencia'],0,0,'L',0);
			$pdf->Ln();
			$pdf->Cell(90,5,$row1['cuenta'],0,0,'L',0);
			$pdf->Cell(90,5,'CHEQ. '.$row['folio'],0,0,'L',0);
			$pdf->Ln();
			
			$pdf->Ln();
			

			$pdf->Ln();

			$pdf->Ln();
		}
	}
	$pdf->Output();	
}

if($_POST['cmd']==2){
	$pdf=new FPDF('P','mm','LETTER');
	$plaza=$_POST['plaza'];
	$fecha=$_POST['fec_nom'];
	$reg=$_POST['reg'];
	$select= " SELECT b.*,a.nombre FROM personal as a 
	INNER JOIN personal_nomina as b ON (b.personal=a.cve AND b.fecha='$fecha' AND b.tipo='1' AND b.salida>0 AND b.tipo_salida = 0)
	WHERE 1";
	if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
	if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; }
	$select.=" ORDER BY a.nombre";
	$rspersonal=mysql_query($select) or die(mysql_error());
	$z=0;
	while($Personal=mysql_fetch_array($rspersonal)){
		$rssalida=mysql_query("SELECT * FROM recibos_salidas WHERE plaza='".$Personal['plaza']."' AND cve='".$Personal['salida']."'");
		$Salida=mysql_fetch_array($rssalida);
		if(($z%2)==0){
			$pdf->AddPage();
			$pdf->Image('images/membrete.JPG',30,3,150,15);
			$pdf->SetY(23);
		}
		else{
			$pdf->Image('images/membrete.JPG',30,135,150,15);
			$pdf->SetY(150);
		}
		$pdf->SetFont('Arial','B',16);
		$pdf->Cell(95,10,'Recibo de Salida',0,0,'L');
		$pdf->Cell(95,10,'Folio: '.$Salida['cve'],0,0,'R');
		$pdf->Ln();
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(95,5,'',0,0,'L');
		$pdf->Cell(95,5,'Bueno por: $ '.number_format($Salida['monto'],2),0,0,'R');
		$pdf->Ln();
		$pdf->Cell(95,5,'Motivo: SUELDO ASEGURADOS',0,0,'L');
		$pdf->Cell(95,5,'Fecha: '.fecha_letra($Salida['fecha']),0,0,'R');
		$pdf->Ln();
		if($Salida['estatus']==1){
			$pdf->SetFont('Arial','B',16);
			$pdf->Cell(190,6,'PAGADO',1,0,'C');
			$pdf->SetFont('Arial','B',12);
		}
		if($Salida['estatus']==2){
			$pdf->SetFont('Arial','B',16);
			$pdf->Cell(190,6,'CANCELADO',1,0,'C');
			$pdf->SetFont('Arial','B',12);
		}
		$pdf->Ln();
		$pdf->SetFont('Arial','',12);
		$pdf->MultiCell(190,5,"Recibi la cantidad de ".numlet($Salida['monto']),0,"R");
		$pdf->Ln();
		$pdf->MultiCell(190,5,"Por Concepto de: ".$Salida['concepto'],0,"R");
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetFont('Arial','U',12);
		$pdf->Cell(190,5,$Personal['nombre'],0,0,'C');
		$pdf->Ln();
		$pdf->SetFont('Arial','',12);
		$pdf->Cell(190,5,"Recibi",0,0,'C');
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$rsfirmas=mysql_query("SELECT * FROM cargos_admon WHERE firma='Si' AND plaza='".$Salida['plaza']."' AND fecha<='".$Salida['fecha']."' AND (fechaf>='".$Salida['fecha']."' OR fechaf='0000-00-00')");
		$numfirmas=mysql_num_rows($rsfirmas);
		$ancho=190/$numfirmas;
		$array_puestoadmon=array();
		$i=0;
		$pdf->SetFont('Arial','U',12);
		while($Firmas=mysql_fetch_array($rsfirmas)){
			
			$pdf->Cell($ancho,5,$Firmas['nombre'],0,0,'C');
			$array_puestoadmon[$i]=$array_puesto[$Firmas['puesto']];
			$i++;
		}
		$pdf->Ln();
		$pdf->SetFont('Arial','',12);
		for($x=0;$x<$i;$x++){
			$pdf->Cell($ancho,5,$array_puestoadmon[$x],0,0,'C');
		}
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();

		$pdf->SetFont('Arial','',10);
		$pdf->Cell(95,5,'Impreso por: '.$array_usuario[$_SESSION['CveUsuario']],0,0,'L');
		$pdf->Cell(95,5,'Creado por: '.$array_usuario[$Salida['usuario']],0,0,'R');
		$z++;
	}
	$pdf->Output();	
}

if($_POST['cmd']==1){
	$plaza=$_POST['plaza'];
	$fecha=$_POST['fec_nom'];
	$reg=$_POST['reg'];
	$select= " SELECT b.*,a.nombre,a.rfc,a.imss,a.puesto FROM personal as a 
	INNER JOIN personal_nomina as b ON (b.personal=a.cve AND b.fecha='$fecha' AND b.tipo='1')
	WHERE 1";
	if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
	if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; }
	$select.=" ORDER BY a.nombre";
	$rspersonal=mysql_query($select) or die(mysql_error());
	$pdf=new FPDF('P','mm','LETTER');
	$dato=explode("-",$fecha);
	if($dato[2]=="10") $periodo="Periodo del 01";
	elseif($dato[2]=="20") $periodo="Periodo del 11";
	else $periodo="Periodo del 21";
	$periodo.=" al ".$dato[2]." de ".$array_meses[intval($dato[1])]." del ".$dato[0];
	while($Personal=mysql_fetch_array($rspersonal)){
		$pdf->AddPage('P');
		$pdf->SetFont('Arial','B',12);
		$pdf->MultiCell(190,5,"VEREFICENTROS",0,'C');
		$pdf->Cell(60,5," ",0,0,'L');
		$pdf->Cell(60,5," ",0,0,'C');
		$pdf->Cell(65,5,"Fecha: ".fechaLocal(),0,0,'R');
		$pdf->Ln();
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(190,5,"Recibo de Nomina",0,0,'C');
		$pdf->Ln();
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(190,5,$periodo,0,0,'L');
		$pdf->Ln();
		$pdf->Cell(130,5,"Nombre: ".$Personal['nombre'],0,0,'L');
		$pdf->Cell(60,5,"Puesto: ".$array_puestos[$Personal['puesto']],0,0,'L');
		$pdf->Ln();
		$pdf->Cell(130,5,"RFC: ".$Personal['rfc'],0,0,'L');
		$pdf->Cell(60,5,"Sueldo: ".number_format($Personal['sal_diario'],2),0,0,'L');
		$pdf->Ln();
		$pdf->Cell(130,5,"NSS: ".$Personal['imss'],0,0,'L');
		$pdf->Cell(60,5,"Dias Trabajados: ".$Personal['dias_tra'],0,0,'L');
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(95,4,"Percepciones",1,0,'C');
		$pdf->Cell(95,4,"Deducciones",1,0,'C');
		$pdf->Ln();
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(60,4,"Importe: ",'L',0,'L');$pdf->Cell(35,4,number_format($Personal['sal_diario']*$Personal['dias_tra'],2),'R',0,'R');
		$pdf->Cell(60,4,"ISR: ",'L',0,'L');$pdf->Cell(35,4,number_format($Personal['isr'],2),'R',0,'R');
		$pdf->Ln();
		$pdf->Cell(60,4,"Tiempo Extra: ",'L',0,'L');$pdf->Cell(35,4,number_format($Personal['tiempo_ex'],2),'R',0,'R');
		$pdf->Cell(60,4,"IMSS: ",'L',0,'L');$pdf->Cell(35,4,number_format($Personal['imp_imss'],2),'R',0,'R');
		$pdf->Ln();
		$pdf->Cell(60,4,"Subsidio al Empleado: ",'L',0,'L');$pdf->Cell(35,4,number_format($Personal['otras_per'],2),'R',0,'R');
		$pdf->Cell(60,4,"Infonavit: ",'L',0,'L');$pdf->Cell(35,4,number_format($Personal['imp_infonavit'],2),'R',0,'R');
		$pdf->Ln();
		$pdf->Cell(60,4,"",'L',0,'L');$pdf->Cell(35,4,"",'R',0,'R');
		$pdf->Cell(60,4,"Descuento por Prestamo: ",'L',0,'L');$pdf->Cell(35,4,number_format($Personal['prestamo'],2),'R',0,'R');
		$pdf->Ln();
		$pdf->Cell(60,4,"",'L',0,'L');$pdf->Cell(35,4,"",'R',0,'R');
		$pdf->Cell(60,4,"Otras Deducciones: ",'L',0,'L');$pdf->Cell(35,4,number_format($Personal['otras_ded'],2),'R',0,'R');
		$pdf->Ln();
		$totper=($Personal['sal_diario']*$Personal['dias_tra'])+$Personal['tiempo_ex'];
		$totded=$Personal['isr']+$Personal['imp_imss']+$Personal['otras_ded']+$Personal['imp_infonavit']+$Personal['prestamo'];
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(60,4,"Total Percepciones: ",'LTB',0,'L');$pdf->Cell(35,4,number_format($totper,2),'RTB',0,'R');
		$pdf->Cell(60,4,"Total Deducciones: ",'LTB',0,'L');$pdf->Cell(35,4,number_format($totded,2),'RTB',0,'R');
		$pdf->Ln();
		//$pdf->Ln();
		$pdf->SetFont('Arial','',10);
		$y=$pdf->GetY();
		$pdf->MultiCell(130,4,"Recibi de Vereficentros por concepto del pago total de mi salario y de mas prestaciones del periodo indicado sin que a la fecha se me adeude cantidad alguna por ningun concepto.",0,"J");
		$pdf->SetXY(140,$y);
		$pdf->MultiCell(30,4,"Neto a Pagar",1,"J");
		$pdf->SetXY(170,$y);
		$pdf->MultiCell(30,4,number_format($totper+$Personal['otras_per']-$totded,2),1,"R");
		$pdf->Ln();
		$pdf->Ln();
		$pdf->MultiCell(190,4,numlet(($totper+$Personal['otras_per']-$totded)),0,"C");
		$pdf->Ln();
		$pdf->MultiCell(190,4,"____________________________________
		Firma del Empleado",0,"C");
		$pdf->SetY(140);
		$pdf->SetFont('Arial','B',12);
		$pdf->MultiCell(190,5,"VEREFICENTROS",0,'C');
		$pdf->Ln();
		$pdf->SetFont('Arial','',12);
		$pdf->Cell(60,5," ",0,0,'L');
		$pdf->Cell(60,5," ",0,0,'C');
		$pdf->Cell(65,5,"Fecha: ".fechaLocal(),0,0,'R');
		$pdf->Ln();
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(190,5,"Recibo de Nomina",0,0,'C');
		$pdf->Ln();
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(190,5,$periodo,0,0,'L');
		$pdf->Ln();
		$pdf->Cell(130,5,"Nombre: ".$Personal['nombre'],0,0,'L');
		$pdf->Cell(60,5,"Puesto: ".$array_puestos[$Personal['puesto']],0,0,'L');
		$pdf->Ln();
		$pdf->Cell(130,5,"RFC: ".$Personal['rfc'],0,0,'L');
		$pdf->Cell(60,5,"Sueldo: ".number_format($Personal['sal_diario'],2),0,0,'L');
		$pdf->Ln();
		$pdf->Cell(130,5,"NSS: ".$Personal['imss'],0,0,'L');
		$pdf->Cell(60,5,"Dias Trabajados: ".$Personal['dias_tra'],0,0,'L');
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(95,4,"Percepciones",1,0,'C');
		$pdf->Cell(95,4,"Deducciones",1,0,'C');
		$pdf->Ln();
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(60,4,"Importe: ",'L',0,'L');$pdf->Cell(35,4,number_format($Personal['sal_diario']*$Personal['dias_tra'],2),'R',0,'R');
		$pdf->Cell(60,4,"ISR: ",'L',0,'L');$pdf->Cell(35,4,number_format($Personal['isr'],2),'R',0,'R');
		$pdf->Ln();
		$pdf->Cell(60,4,"Tiempo Extra: ",'L',0,'L');$pdf->Cell(35,4,number_format($Personal['tiempo_ex'],2),'R',0,'R');
		$pdf->Cell(60,4,"IMSS: ",'L',0,'L');$pdf->Cell(35,4,number_format($Personal['imp_imss'],2),'R',0,'R');
		$pdf->Ln();
		$pdf->Cell(60,4,"Subsidio al Empleado: ",'L',0,'L');$pdf->Cell(35,4,number_format($Personal['otras_per'],2),'R',0,'R');
		$pdf->Cell(60,4,"Infonavit: ",'L',0,'L');$pdf->Cell(35,4,number_format($Personal['imp_infonavit'],2),'R',0,'R');
		$pdf->Ln();
		$pdf->Cell(60,4,"",'L',0,'L');$pdf->Cell(35,4,"",'R',0,'R');
		$pdf->Cell(60,4,"Descuento por Prestamo: ",'L',0,'L');$pdf->Cell(35,4,number_format($Personal['prestamo'],2),'R',0,'R');
		$pdf->Ln();
		$pdf->Cell(60,4,"",'L',0,'L');$pdf->Cell(35,4,"",'R',0,'R');
		$pdf->Cell(60,4,"Otras Deducciones: ",'L',0,'L');$pdf->Cell(35,4,number_format($Personal['otras_ded'],2),'R',0,'R');
		$pdf->Ln();
		$totper=($Personal['sal_diario']*$Personal['dias_tra'])+$Personal['tiempo_ex'];
		$totded=$Personal['isr']+$Personal['imp_imss']+$Personal['otras_ded']+$Personal['imp_infonavit']+$Personal['prestamo'];
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(60,4,"Total Percepciones: ",'LTB',0,'L');$pdf->Cell(35,4,number_format($totper,2),'RTB',0,'R');
		$pdf->Cell(60,4,"Total Deducciones: ",'LTB',0,'L');$pdf->Cell(35,4,number_format($totded,2),'RTB',0,'R');
		$pdf->Ln();
		//$pdf->Ln();
		$pdf->SetFont('Arial','',10);
		$y=$pdf->GetY();
		$pdf->MultiCell(130,4,"Recibi de Vereficentros por concepto del pago total de mi salario y de mas prestaciones del periodo indicado sin que a la fecha se me adeude cantidad alguna por ningun concepto.",0,"J");
		$pdf->SetXY(140,$y);
		$pdf->MultiCell(30,4,"Neto a Pagar",1,"J");
		$pdf->SetXY(170,$y);
		$pdf->MultiCell(30,4,number_format($totper+$Personal['otras_per']-$totded,2),1,"R");
		$pdf->Ln();
		$pdf->Ln();
		$pdf->MultiCell(190,4,numlet(($totper+$Personal['otras_per']-$totded)),0,"C");
		$pdf->Ln();
		$pdf->MultiCell(190,4,"____________________________________
		Firma del Empleado",0,"C");
	}
	$pdf->Output();
	exit();
}

$plaza=$_POST['plaza'];
$fecha=$_POST['fec_nom'];
$reg=$_POST['reg'];
$select= " SELECT b.*,a.nombre,a.puesto,a.rfc,a.imss FROM personal as a 
INNER JOIN personal_nomina as b ON (b.personal=a.cve AND b.fecha='$fecha' AND b.tipo='1')
WHERE 1";
if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; }
$select.=" ORDER BY a.nombre";
$rspersonal=mysql_query($select);
class FPDF2 extends PDF_MC_Table {
	function Header(){
		global $plaza,$array_plaza,$numcond,$reg,$fecha,$array_meses;
		$this->SetFont('Arial','B',12);
		$this->MultiCell(360,5,'VEREFICENTROS',0,'J');   
		$this->Cell(180,5,'Fecha de Elaboracion: '.$fecha,0,0,'L');
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
		if($plaza!="all") $tit.=' de la plaza '.$array_plaza[$plaza];
		$this->MultiCell(360,5,'Nomina de los Empleados correspondiente a la '.$num.' decena del mes '.$mes.' del '.$anio.' '.$tit,0,'C');
		$this->Ln();
		$this->SetFont('Arial','B',9);
		$this->Cell(32.5,4,' ',0,0,'C',0);
		$this->Cell(25,4,' ',0,0,'C',0);
		$this->Cell(25,4,' ',0,0,'C',0);
		$this->Cell(30,4,' ',0,0,'C',0);
		$this->Cell(15,4,'Salario',0,0,'C',0);
		$this->Cell(15,4,'Dias',0,0,'C',0);
		$this->Cell(15,4,' ',0,0,'C',0);
		$this->Cell(15,4,'Tiempo',0,0,'C',0);
		$this->Cell(15,4,'Total',0,0,'C',0);
		$this->Cell(15,4,'Subsidio al',0,0,'C',0);
		$this->Cell(15,4,' ',0,0,'C',0);
		$this->Cell(15,4,' ',0,0,'C',0);
		$this->Cell(15,4,'Info-',0,0,'C',0);
		$this->Cell(15,4,'Desc.',0,0,'C',0);
		$this->Cell(15,4,'Otras',0,0,'C',0);
		$this->Cell(15,4,'Total',0,0,'C',0);
		$this->Cell(15,4,'Total',0,0,'C',0);
		$this->Ln();
		$this->SetFont('Arial','B',9);
		$this->Cell(32.5,4,'Nombre',0,0,'C',0);
		$this->Cell(25,4,'Puesto',0,0,'C',0);
		$this->Cell(25,4,'R.F.C.',0,0,'C',0);
		$this->Cell(30,4,'N.S.S.',0,0,'C',0);
		$this->Cell(15,4,'Diario',0,0,'C',0);
		$this->Cell(15,4,'Trabajados',0,0,'C',0);
		$this->Cell(15,4,'Importe',0,0,'C',0);
		$this->Cell(15,4,'Extra',0,0,'C',0);
		$this->Cell(15,4,'Percep.',0,0,'C',0);
		$this->Cell(15,4,'Empleado',0,0,'C',0);
		$this->Cell(15,4,'ISR',0,0,'C',0);
		$this->Cell(15,4,'IMSS',0,0,'C',0);
		$this->Cell(15,4,'navit',0,0,'C',0);
		$this->Cell(15,4,'Prestamo',0,0,'C',0);
		$this->Cell(15,4,'Deduc.',0,0,'C',0);
		$this->Cell(15,4,'Deduc.',0,0,'C',0);
		$this->Cell(15,4,'a Pagar',0,0,'C',0);
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

$pdf=new FPDF2('L','mm','LEGAL');
$pdf->AliasNbPages();
$pdf->AddPage('L');

$pdf->SetFont('Arial','',9);
$pdf->SetWidths(array(32.5,25,25,30,15,15,15,15,15,15,15,15,15,15,15,15,15,32.5));
$pdf->SetAligns(array('L','L','C','C','R','R','R','R','R','R','R','R','R','R','R','R','R','C'));
$i=0;	
$total=0;
while($Personal=mysql_fetch_array($rspersonal)) {
	$renglon=array();
	$renglon[]=$Personal['nombre'];
	$renglon[]=$array_puestos[$Personal['puesto']];
	$renglon[]=$Personal['rfc'];
	$renglon[]=$Personal['imss'];
	$renglon[]=number_format($Personal['sal_diario'],2);
	$renglon[]=$Personal['dias_tra'];
	$importe=$Personal['sal_diario']*$Personal['dias_tra'];
	$renglon[]=number_format($importe,2);
	$renglon[]=number_format($Personal['tiempo_ex'],2);
	$tot_per=$importe+$Personal['tiempo_ex'];
	$renglon[]=number_format($tot_per,2);
	$renglon[]=number_format($Personal['otras_per'],2);
	$renglon[]=number_format($Personal['isr'],2);
	$renglon[]=number_format($Personal['imp_imss'],2);
	$renglon[]=number_format($Personal['imp_infonavit'],2);
	$renglon[]=number_format($Personal['prestamo'],2);
	$renglon[]=number_format($Personal['otras_ded'],2);
	$tot_ded=$Personal['isr']+$Personal['imp_imss']+$Personal['otras_ded']+$Personal['imp_infonavit']+$Personal['prestamo'];
	$renglon[]=number_format($tot_ded,2);
	$renglon[]=number_format($tot_per+$Personal['otras_per']-$tot_ded,2);
	$renglon[]="               _________________";
	if(($i%2)==0){
		$pdf->RowGetColor($renglon,array(238,238,238),0,5);
		$renglon=array(" "," "," "," "," "," "," "," "," "," "," "," "," "," "," "," "," "," ");
		$pdf->RowGetColor($renglon,array(238,238,238),0,2);
	}
	else{
		$pdf->RowGetColor($renglon,array(255,255,255),0,5);
		$renglon=array(" "," "," "," "," "," "," "," "," "," "," "," "," "," "," "," "," "," ");
		$pdf->RowGetColor($renglon,array(255,255,255),0,2);
	}
	$i++;
	$timp+=$Personal['sal_diario']*$Personal['dias_tra'];
	$ttiempo+=$Personal['tiempo_ex'];
	$totrasp+=$Personal['otras_per'];
	$tper+=$tot_per;
	$tisr+=$Personal['isr'];
	$timss+=$Personal['imp_imss'];
	$tinfonavit+=$Personal['imp_infonavit'];
	$tprestamo+=$Personal['prestamo'];
	$totrasd+=$Personal['otras_ded'];
	$tded+=$tot_ded;
	$total+=($tot_per+$Personal['otras_per']-$tot_ded);
}
$pdf->Ln();
$pdf->Cell(127.5,5,$i." Registro(s)");
$pdf->Cell(15,5,"Total: ",0,0,'R');
$pdf->Cell(15,5,number_format($timp,2),0,0,'R');
$pdf->Cell(15,5,number_format($ttiempo,2),0,0,'R');
$pdf->Cell(15,5,number_format($totrasp,2),0,0,'R');
$pdf->Cell(15,5,number_format($tper,2),0,0,'R');
$pdf->Cell(15,5,number_format($tisr,2),0,0,'R');
$pdf->Cell(15,5,number_format($timss,2),0,0,'R');
$pdf->Cell(15,5,number_format($tinfonavit,2),0,0,'R');
$pdf->Cell(15,5,number_format($tprestamo,2),0,0,'R');
$pdf->Cell(15,5,number_format($totrasd,2),0,0,'R');
$pdf->Cell(15,5,number_format($tded,2),0,0,'R');
$pdf->Cell(15,5,number_format($total,2),0,0,'R');


$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Cell(60,5,"_______________________________________",0,0,'C');
$pdf->Cell(20,5," ",0,0,'C');
$pdf->Cell(60,5,"_______________________________________",0,0,'C');
$pdf->Cell(20,5," ",0,0,'C');
$pdf->Cell(60,5,"_______________________________________",0,0,'C');
$pdf->Cell(20,5," ",0,0,'C');
$pdf->Cell(60,5,"_______________________________________",0,0,'C');
$pdf->Ln();
$pdf->Cell(60,5,"Elaboro",0,0,'C');
$pdf->Cell(20,5," ",0,0,'C');
$pdf->Cell(60,5,"Reviso",0,0,'C');
$pdf->Cell(20,5," ",0,0,'C');
$pdf->Cell(60,5,"Autorizo",0,0,'C');
$pdf->Cell(20,5," ",0,0,'C');
$pdf->Cell(60,5,"Vo.Bo.",0,0,'C');
$pdf->Ln();
/*$pdf->Cell(60,5,"Srita. Veronica Castañeda R.",0,0,'C');
$pdf->Cell(20,5," ",0,0,'C');
$pdf->Cell(60,5," Sr. Hilario Ramirez Martinez",0,0,'C');
$pdf->Cell(20,5," ",0,0,'C');
$pdf->Cell(60,5,"Sr. Luis Manuel Dias Flores",0,0,'C');
$pdf->Cell(20,5," ",0,0,'C');
$pdf->Cell(60,5,"C. Joel Hernandez Vera",0,0,'C');*/
$pdf->Output();	


?>