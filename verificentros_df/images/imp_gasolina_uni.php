<?php
require('fpdf153/fpdf.php');
include("main.php");
include("numlet.php");
$rsPlaza=mysql_db_query($base,"SELECT * FROM plazas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['nombre'];
}

$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsMotivos=mysql_db_query($base,"SELECT * FROM cat_cargos_unidades");
while($Motivo=mysql_fetch_array($rsMotivos)){
	$array_motivo[$Motivo['cve']]=$Motivo['nombre'];
}

$rsUnidad=mysql_db_query($base,"SELECT * FROM parque");
while($Unidad=mysql_fetch_array($rsUnidad)){
	$array_unidad[$Unidad['cve']]=$Unidad['no_eco'].$Unidad['letra'].' - '.$array_tipo_vehiculo[$Unidad['tipo_vehiculo']];
	$array_propietario[$Unidad['cve']]=$Unidad['propietario'];
	$array_numeco[$Unidad['cve']]=$Unidad['no_eco'].$Unidad['letra'];
	$array_tipo_unidad[$Unidad['cve']]=$array_tipo_vehiculo[$Unidad['tipo_vehiculo']];
}
$array_tipogas=array("Gasolina","Diesel");
if($_GET['cmd']==30){
	$res=mysql_db_query($base,"SELECT * FROM parque_abono WHERE cve='".$_GET['abono']."'");
	$row=mysql_fetch_array($res);
	$impresion='<iframe src="http://localhost/imp.php?folio='.$row['folio'].'&fecha='.fechaNormal($row['fecha']).'&hora='.$row['hora'].'&fecha_rec='.fechaNormal($row['fecha_rec']).'&monto='.$row['monto'].'&unidad='.$array_unidad[$row['unidad']].'&tipo_uni='.$array_tipo_unidad[$row['unidad']].'" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>window.close();</script>';
	exit();
}
if($_GET['cmd']==20){
	$res=mysql_db_query($base,"SELECT * FROM parque_abono WHERE cve='".$_GET['abono']."'");
	$row=mysql_fetch_array($res);
	echo '<table width="100%" style="font-size:12px">';
	echo '<tr><td colspan="2" align="center"><img src="images/membrete.JPG" width="500" height="100"></td></tr>';
	echo '<tr><td width="50%">&nbsp;</td><td width="50%"></tr>';
	echo '<tr><td>&nbsp;</td></tr>';
	echo '<tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;RECIBO DE CUENTA&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ORIGINAL</td><td align="right">FOLIO: '.$row['folio'].'&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>';
	echo '<tr><td colspan="2" align="right">Fecha del Movimiento:&nbsp;'.$row['fecha'].'&nbsp;'.$row['hora'].'</td></tr>';
	echo '<tr><td>&nbsp;</td></tr>';
	echo '<tr><td>&nbsp;</td></tr>';
	echo '<tr><td align="left" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;Recibi de la Unidad '.$array_unidad[$row['unidad']].' la cantidad de $ '.number_format($row['monto'],2).'</td></tr>';
	echo '<tr><td colspan="2" align="left">&nbsp;&nbsp;&nbsp;&nbsp;'.numlet($row['monto']).'</td></tr>';
	echo '<tr><td align="left" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;Por concepto de recaudacion de la fecha '.$row['fecha_rec'].'</td></tr>';
	echo '<tr><td>&nbsp;</td></tr>';
	echo '<tr><td>&nbsp;</td></tr>';
	echo '<tr><td align="center" colspan="2">Recaudacion</td></tr>';
	echo '<tr><td>&nbsp;</td></tr>';
	echo '<tr><td align="left" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;Observaciones</td></tr>';
	echo '</table>';
	echo '<br><br><br><br><br><hr><br>';
	echo '<table width="100%" style="font-size:12px">';
	echo '<tr><td colspan="2" align="center"><img src="images/membrete.JPG" width="500" height="100"></td></tr>';
	echo '<tr><td width="50%">&nbsp;</td><td width="50%"></tr>';
	echo '<tr><td>&nbsp;</td></tr>';
	echo '<tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;RECIBO DE CUENTA&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;COPIA</td><td align="right">FOLIO: '.$row['folio'].'&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>';
	echo '<tr><td colspan="2" align="right">Fecha del Movimiento:&nbsp;'.$row['fecha'].'&nbsp;'.$row['hora'].'</td></tr>';
	echo '<tr><td>&nbsp;</td></tr>';
	echo '<tr><td>&nbsp;</td></tr>';
	echo '<tr><td align="left" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;Recibi de la Unidad '.$array_unidad[$row['unidad']].' la cantidad de $ '.number_format($row['monto'],2).'</td></tr>';
	echo '<tr><td colspan="2" align="left">&nbsp;&nbsp;&nbsp;&nbsp;'.numlet($row['monto']).'</td></tr>';
	echo '<tr><td align="left" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;Por concepto de recaudacion de la fecha '.$row['fecha_rec'].'</td></tr>';
	echo '<tr><td>&nbsp;</td></tr>';
	echo '<tr><td>&nbsp;</td></tr>';
	echo '<tr><td align="center" colspan="2">Recaudacion</td></tr>';
	echo '<tr><td>&nbsp;</td></tr>';
	echo '<tr><td align="left" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;Observaciones</td></tr>';
	echo '</table>';
	echo '<script>window.print();window.close();</script>';
	exit();
}

if($_POST['cmd']==1){
	class PDF1 extends FPDF{
		//Cabecera de página
		function Header(){
			//Arial bold 15
			$this->SetFont('Arial','B',15);
			//Título
			$this->Cell(0,10,'CORTE DEL DIA DE ABONOS DE UNIDADES',0,0,'C');
			$this->Ln();
			$this->Cell(0,10,'Lista de Abonos del dia: '.fechaLocal(),0,0,'C');
			//Salto de línea
			$this->Ln(20);
		}

		//Pie de página
		function Footer(){
			//Posición: a 1,5 cm del final
			$this->SetY(-15);
			//Arial italic 8
			$this->SetFont('Arial','I',8);
			//Número de página
			$this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
		}
	}

	//Creación del objeto de la clase heredada
	$pdf=new PDF1();
	$pdf->AliasNbPages();
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',10);
	$filtro="";
	if ($_POST['searchplaza']!="all") {$filtro=" AND plaza='".$_POST['searchplaza']."'";}
	if ($_POST['usuario']!="all") { $filtro.=" AND usuario='".$_POST['usuario']."'"; }
	$total=0;
	$res1=mysql_db_query($base,"SELECT usuario,sum(monto) as tot FROM parque_abono WHERE fecha='".fechaLocal()."' AND estatus!='C' $filtro GROUP BY usuario ORDER BY usuario");
	while($row1=mysql_fetch_array($res1)){
		$pdf->Cell(60,5,$array_usuario[$row1['usuario']],0,0,'R');
		$pdf->Cell(30,5,number_format($row1['tot'],2),0,0,'R');
		$pdf->Ln();
		$total+=$row1['tot'];
	}
	$pdf->Cell(60,5,"Total:",0,0,'R');
	$pdf->Cell(30,5,number_format($total,2),0,0,'R');
	$pdf->Ln();
	$pdf->Cell(30,5,'Folio',1,0,'C');
	$pdf->Cell(30,5,'Fec. Recaudacion',1,0,'C');
	$pdf->Cell(35,5,'Unidad',1,0,'C');
	$pdf->Cell(40,5,'Monto',1,0,'C');
	$pdf->Cell(55,5,'Usuario',1,0,'C');
	$pdf->SetFont('Arial','',10);
	$res=mysql_db_query($base,"SELECT * FROM parque_abono WHERE fecha='".fechaLocal()."' $filtro ORDER BY plaza,folio DESC");
	while ($row=mysql_fetch_array($res)){	
		$pdf->Ln();
		$estatus='';
		if($row['estatus']=='C'){
			$estatus='(CANCELADO)';
			$row['monto']=0;
		}
		$pdf->Cell(30,5,$row['folio'].$estatus,1,0,'C');
		$pdf->Cell(30,5,$row['fecha_rec'],1,0,'C');
		$pdf->Cell(35,5,$array_unidad[$row['unidad']],1,0,'L');
		$pdf->Cell(40,5,number_format($row['monto'],2),1,0,'R');
		$pdf->Cell(55,5,$array_usuario[$row['usuario']],1,0,'C');
	}
	$pdf->Output();
	exit();
}

if($_POST['cmd']==2){
	$fecha1=$_POST['fecha_ini'];
	$fecha2=$_POST['fecha_fin'];
	class PDF1 extends FPDF{
		//Cabecera de página
		function Header(){
			global $fecha1,$fecha2;
			//Arial bold 15
			$this->SetFont('Arial','B',15);
			//Título
			$this->Cell(0,10,'CORTE DEL DIA DE ABONOS DE UNIDADES',0,0,'C');
			$this->Ln();
			$this->Cell(0,10,'Lista de Abonos del dia: '.$fecha1.' al dia '.$fecha2,0,0,'C');
			//Salto de línea
			$this->Ln(20);
		}

		//Pie de página
		function Footer(){
			//Posición: a 1,5 cm del final
			$this->SetY(-15);
			//Arial italic 8
			$this->SetFont('Arial','I',8);
			//Número de página
			$this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
		}
	}

	//Creación del objeto de la clase heredada
	$pdf=new PDF1();
	$pdf->AliasNbPages();
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',10);
	$filtro="";
	if ($_POST['searchplaza']!="all") {$filtro=" AND plaza='".$_POST['searchplaza']."'";}
	if ($_POST['usuario']!="all") { $filtro.=" AND usuario='".$_POST['usuario']."'"; }
	$total=0;
	$res1=mysql_db_query($base,"SELECT usuario,sum(monto) as tot FROM parque_abono WHERE ".$_POST['tipofe'].">='".$_POST['fecha_ini']."' AND ".$_POST['tipofec']."<='".$_POST['fecha_fin']."' AND estatus!='C' $filtro GROUP BY usuario ORDER BY usuario");
	while($row1=mysql_fetch_array($res1)){
		$pdf->Cell(60,5,$array_usuario[$row1['usuario']],0,0,'R');
		$pdf->Cell(30,5,number_format($row1['tot'],2),0,0,'R');
		$pdf->Ln();
		$total+=$row1['tot'];
	}
	$pdf->Cell(60,5,"Total:",0,0,'R');
	$pdf->Cell(30,5,number_format($total,2),0,0,'R');
	$pdf->Ln();
	$pdf->Cell(30,5,'Folio',1,0,'C');
	$pdf->Cell(30,5,'Fec. Recaudacion',1,0,'C');
	$pdf->Cell(35,5,'Unidad',1,0,'C');
	$pdf->Cell(40,5,'Monto',1,0,'C');
	$pdf->Cell(55,5,'Usuario',1,0,'C');
	$pdf->SetFont('Arial','',10);
	$res=mysql_db_query($base,"SELECT * FROM parque_abono WHERE ".$_POST['tipofec'].">='".$_POST['fecha_ini']."' AND ".$_POST['tipofec']."<='".$_POST['fecha_fin']."' $filtro ORDER BY plaza,folio DESC");
	while ($row=mysql_fetch_array($res)){	
		$pdf->Ln();
		$estatus='';
		if($row['estatus']=='C'){
			$estatus='(CANCELADO)';
			$row['monto']=0;
		}
		$pdf->Cell(30,5,$row['folio'].$estatus,1,0,'C');
		$pdf->Cell(30,5,$row['fecha_rec'],1,0,'C');
		$pdf->Cell(35,5,$array_unidad[$row['unidad']],1,0,'L');
		$pdf->Cell(40,5,number_format($row['monto'],2),1,0,'R');
		$pdf->Cell(55,5,$array_usuario[$row['usuario']],1,0,'C');
	}
	$pdf->Output();
	exit();
}

if($_POST['cmd']==3){
	$fecha1=$_POST['fecha_ini'];
	$fecha2=$_POST['fecha_fin'];
	class PDF1 extends FPDF{
		//Cabecera de página
		function Header(){
			global $fecha1,$fecha2;
			//Arial bold 15
			$this->SetFont('Arial','B',15);
			//Título
			$this->Cell(0,10,'CORTE DEL DIA DE ABONOS DE UNIDADES POR MOTIVOS',0,0,'C');
			$this->Ln();
			$this->Cell(0,10,'Lista de Abonos del dia: '.$fecha1.' al dia '.$fecha2,0,0,'C');
			//Salto de línea
			$this->Ln(20);
		}

		//Pie de página
		function Footer(){
			//Posición: a 1,5 cm del final
			$this->SetY(-15);
			//Arial italic 8
			$this->SetFont('Arial','I',8);
			//Número de página
			$this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
		}
	}

	//Creación del objeto de la clase heredada
	$pdf=new PDF1();
	$pdf->AliasNbPages();
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',10);
	$filtro="";
	if ($_POST['searchplaza']!="all") {$filtro=" AND a.plaza='".$_POST['searchplaza']."'";}
	//if ($_POST['usuario']!="all") { $filtro.=" AND a.usuario='".$_POST['usuario']."'"; }
	$total=0;
	$res=mysql_db_query($base,"SELECT a.plaza,c.motivo,sum(b.monto) as abonos 
				FROM parque_abono as a
				INNER JOIN parque_abonomov as b ON (b.abono=a.cve)
				INNER JOIN cargos_parque as c ON (c.cve=b.cargo)
				WHERE a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' $filtro
				GROUP BY c.motivo");
	$pdf->Cell(25,5,' ',0,0,'C');
	$pdf->Cell(100,5,'Motivo',1,0,'C');
	$pdf->Cell(40,5,'Monto',1,0,'C');
	$pdf->SetFont('Arial','',10);
	$total=0;
	while ($row=mysql_fetch_array($res)){	
		$pdf->Ln();
		$pdf->Cell(25,5,' ',0,0,'C');
		$pdf->Cell(100,5,$array_motivo[$row['motivo']],1,0,'L');
		$pdf->Cell(40,5,number_format($row['abonos'],2),1,0,'R');
		$total+=$row['abonos'];
	}
	$select= " SELECT a.plaza,sum(b.monto) as abonos 
			FROM parque_abono as a
			INNER JOIN parque_abonomov as b ON (b.abono=a.cve AND b.cargo='0')
			WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."'";
	if ($_POST['searchplaza']!="all") { $select.=" AND a.plaza='".$_POST['searchplaza']."'"; $filtro=" AND a.plaza='".$_POST['searchplaza']."'";}
	$select.=" GROUP BY a.plaza";
	$rsabonos=mysql_db_query($base,$select);
	$Abono=mysql_fetch_array($rsabonos);
	$pdf->Ln();
	$pdf->Cell(25,5,' ',0,0,'C');
	$pdf->Cell(100,5,"SALDO A FAVOR",1,0,'L');
	$pdf->Cell(40,5,number_format($Abono['abonos'],2),1,0,'R');
	$total+=$Abono['abonos'];
	$pdf->Ln();
	$pdf->Cell(25,5,' ',0,0,'C');
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(100,5,"Total:",1,0,'L');
	$pdf->Cell(40,5,number_format($total,2),1,0,'R');
	$pdf->Output();
	exit();
}

if($_POST['cmd']==10){
$tamano=array(260.0,134.5);
$pdf=new FPDF('P','mm',$tamano);
$res=mysql_db_query($base,"SELECT * FROM parque_abono WHERE cve='".$_POST['reg']."'");
$row=mysql_fetch_array($res);
$pdf->AddPage();
$pdf->SetY(25);
$pdf->SetFont('Arial','B',16);
$pdf->Cell(90,5,'Folio: '.$row['folio'],0,0,'R');
$pdf->Ln(10);
$pdf->Cell(90,5,'CUENTA DIARIA',0,0,'C');
$pdf->Ln(10);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(90,5,'Cuenta del dia:    '.$row['fecha_rec'],0,0,'L');
$pdf->Ln(5);
$pdf->Cell(90,5,'Fecha:    '.$row['fecha'].' '.$row['hora'],0,0,'L');
$pdf->Ln(10);
$pdf->Cell(90,5,'Numero Economico:    '.$array_numeco[$row['unidad']],0,0,'L');
$pdf->Ln(5);
$pdf->Cell(90,5,'Tipo:    '.$array_tipo_unidad[$row['unidad']],0,0,'L');
$pdf->Ln(10);
$pdf->Cell(90,5,'INGRESOS BRUTOS:    '.number_format($row['monto'],2),0,0,'L');
$pdf->Output();
exit();
}

$tamano=array(75,100);
$pdf=new FPDF('P','mm',$tamano);
$res=mysql_db_query($base,"SELECT * FROM parque_gasolina WHERE cve='".$_POST['reg']."'");
$row=mysql_fetch_array($res);
$pdf->SetLeftMargin(5);
$pdf->AddPage();
$pdf->Image('images/bruja.jpg',15,3,45,15);
$pdf->SetY(18);
$pdf->SetFont('Arial','',8);
$pdf->MultiCell(75,5,'Vale Gasolina No.: '.$row['folio']);
$pdf->MultiCell(75,5,'Fecha: '.$row['fecha'].' '.$row['hora']);
$pdf->MultiCell(75,5,'Unidad: '.$array_unidad[$row['unidad']]);
$pdf->MultiCell(75,5,'Propietario: '.$array_propietario[$row['unidad']]);
$pdf->MultiCell(75,5,'Combustible: '.$array_tipogas[$row['tipo']]);
$pdf->MultiCell(75,5,'Monto: '.number_format($row['monto'],2));
$pdf->Ln();
$pdf->Ln();
$pdf->MultiCell(75,5,'___________________   __________________');
$pdf->MultiCell(75,5,'Autolrizo                            Recibio');
$pdf->MultiCell(75,5,'Taquillero: '.$array_usuario[$row['usuario']]);
$pdf->Output();

?>