<?php
include("main.php");

//ARREGLOS

//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);
$rechazos = "9,19";

$abono=0;
if($_POST['cmd']==101){
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	echo '<tr bgcolo="#E9F2F8"><th>Tipo</th><th>Centro</th>';
	echo '<th>Aforo</th><th>Rechazo</th><th>Total</th><th>Importe</th></tr>'; 
	$sumacargo=array(0,0,0,0);
	$x=0;
	foreach($array_plazas as $k=>$v){
		rowc();
		if($x==0){
			echo '<td rowspan="'.count($array_plazas).'">Capital</td>';
		}
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."' AND estatus!='C'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		echo '<td align="right">'.number_format($row2[0],0).'</td>';
		echo '<td align="right">'.number_format($row2[1],0).'</td>';
		echo '<td align="right">'.number_format($row2[0]+$row2[1],0).'</td>';
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		echo '<td align="right">'.number_format($row[1]-$row1[1],2).'</td>';
		echo '</tr>';
		$x++;
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
	}
	//echo '<tr><td colspan="6">&nbsp;</td></tr>';
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 2 ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$x=0;
	foreach($array_plazas as $k=>$v){
		rowc();
		if($x==0){
			echo '<td rowspan="'.count($array_plazas).'">Municipios</td>';
		}
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."' AND estatus!='C'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		echo '<td align="right">'.number_format($row2[0],0).'</td>';
		echo '<td align="right">'.number_format($row2[1],0).'</td>';
		echo '<td align="right">'.number_format($row2[0]+$row2[1],0).'</td>';
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		echo '<td align="right">'.number_format($row[1]-$row1[1],2).'</td>';
		echo '</tr>';
		$x++;
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
	}
	$c=4;
	echo '<tr>';
	echo '<td bgcolo="#E9F2F8" align="right" colspan="2">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		if($k>2) $decimal=2;
		else $decimal=0;
		echo '<td bgcolo="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';
	
	/*echo '<tr><td colspan="6">&nbsp;</td></tr>';
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 3 ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$x=0;
	$sumacargo2=array(0,0,0,0);
	foreach($array_plazas as $k=>$v){
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
		
		$sumacargo2[0]+=$row2[0];
		$sumacargo2[1]+=$row2[1];
		$sumacargo2[2]+=$row2[0]+$row2[1];
		$sumacargo2[3]+=$row[1]-$row1[1];
	}
	echo '<tr>';
	echo '<td bgcolo="#E9F2F8" align="center" colspan="2">&nbsp;Unidades Moviles</td>';
	foreach($sumacargo2 as $k=>$v){
		if($k>2) $decimal=2;
		else $decimal=0;
		echo '<td bgcolo="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';
	echo '<tr><td colspan="6">&nbsp;</td></tr>';
	echo '<tr>';
	echo '<td bgcolo="#E9F2F8" align="right" colspan="2">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		if($k>2) $decimal=2;
		else $decimal=0;
		echo '<td bgcolo="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';*/
	echo '</table>';
	exit();
}
if($_POST['cmd']==-100){
	require_once('../fpdf153/fpdf.php');
	$pdf = new FPDF('P','mm','LETTER');
	$pdf->AddPage();
	
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 1 ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'];
	}
	$pdf->SetFont("Arial","B",14);
	$pdf->Cell(190,5,"INFORME DE VENTAS",0,0,'C');
	$pdf->Ln();
	$pdf->SetFont("Arial","B",13);
	if($_POST['fecha_ini']!=$_POST['fecha_fin'])
		$pdf->Cell(190,5,"Del ".fecha_letra($_POST['fecha_ini'])." al ".fecha_letra($_POST['fecha_fin']),0,0,'C');
	else
		$pdf->Cell(190,5,fecha_letra($_POST['fecha_ini']),0,0,'C');
	$pdf->Ln();
	$pdf->SetFont("Arial","",8);
	$pdf->Cell(190,5,"(miles de pesos)",0,0,'C');
	$pdf->Ln();
	$pdf->SetFont("Arial","B",10);
	$pdf->Cell(30,5,"Tipo Plaza",1,0,'C');
	$pdf->Cell(70,5,"Centro",1,0,'C');
	$pdf->Cell(20,5,"Aforo",1,0,'C');
	$pdf->Cell(20,5,"Rechazo",1,0,'C');
	$pdf->Cell(20,5,"Total",1,0,'C');
	$pdf->Cell(30,5,"Importe",1,0,'C');
	
	$pdf->SetFont("Arial","",10);
	$sumacargo=array(0,0,0,0);
	$x=0;
	foreach($array_plazas as $k=>$v){
		$pdf->Ln();
		if($x==0){
			$pdf->Cell(30,4,"Capital",'LTR',0,'L');
		}
		else{
			$pdf->Cell(30,4,"",'LR',0,'L');
		}
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$pdf->Cell(70,4,$v,1,0,'L');
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."' AND estatus!='C'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$pdf->Cell(20,4,number_format($row2[0],0),1,0,'C');
		$pdf->Cell(20,4,number_format($row2[1],0),1,0,'C');
		$pdf->Cell(20,4,number_format($row2[0]+$row2[1],0),1,0,'C');
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$pdf->Cell(30,4,number_format($row[1]-$row1[1],2),1,0,'R');
		$x++;
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
	}
	$pdf->Ln();
	$pdf->Cell(30,4,"",'T',0,'L');
	
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 2 ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'];
	}
	$x=0;
	foreach($array_plazas as $k=>$v){
		$pdf->Ln();
		if($x==0){
			$pdf->Cell(30,4,"Municipios",'LTR',0,'L');
		}
		else{
			$pdf->Cell(30,4,"",'LR',0,'L');
		}
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$pdf->Cell(70,4,$v,1,0,'L');
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."' AND estatus!='C'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$pdf->Cell(20,4,number_format($row2[0],0),1,0,'C');
		$pdf->Cell(20,4,number_format($row2[1],0),1,0,'C');
		$pdf->Cell(20,4,number_format($row2[0]+$row2[1],0),1,0,'C');
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$pdf->Cell(30,4,number_format($row[1]-$row1[1],2),1,0,'R');
		$x++;
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
	}
	$pdf->Ln();
	$pdf->SetFont("Arial","B",10);
	$pdf->Cell(100,4,"Totales",'T',0,'L');
	foreach($sumacargo as $k=>$v){
		if($k>2) $pdf->Cell(30,4,number_format($v,0),0,0,'R');
		else $pdf->Cell(20,4,number_format($v,0),0,0,'C');
	}
	/*$pdf->Ln();
	
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 3 ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$x=0;
	$sumacargo2=array(0,0,0,0);
	foreach($array_plazas as $k=>$v){
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
		
		$sumacargo2[0]+=$row2[0];
		$sumacargo2[1]+=$row2[1];
		$sumacargo2[2]+=$row2[0]+$row2[1];
		$sumacargo2[3]+=$row[1]-$row1[1];
	}
	$pdf->Ln();
	$pdf->SetFont("Arial","",10);
	$pdf->Cell(100,4,"Unidades Moviles",0,0,'C');
	foreach($sumacargo2 as $k=>$v){
		if($k>2) $pdf->Cell(30,4,number_format($v,0),0,0,'R');
		else $pdf->Cell(20,4,number_format($v,0),0,0,'C');
	}
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont("Arial","B",10);
	$pdf->Cell(100,4,"Totales",0,0,'L');
	foreach($sumacargo as $k=>$v){
		if($k>2) $pdf->Cell(30,4,number_format($v,0),0,0,'R');
		else $pdf->Cell(20,4,number_format($v,0),0,0,'C');
	}*/
	$pdf->Output();
	exit();
}
if($_POST['cmd']==100){
		ini_set("session.auto_start", 0);
	require_once('../fpdf153/fpdf.php');
	$pdf = new FPDF('P','mm','LETTER');
	$pdf->AddPage();
	
	/*$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 1 ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'];
	}*/
	$pdf->SetFont("Arial","B",14);
	$pdf->Cell(190,5,"INFORME DE VENTAS",0,0,'C');
	$pdf->Ln();
	$pdf->SetFont("Arial","B",13);
	if($_POST['fecha_ini']!=$_POST['fecha_fin'])
		$pdf->Cell(190,5,"Del ".fecha_letra($_POST['fecha_ini'])." al ".fecha_letra($_POST['fecha_fin']),0,0,'C');
	else
		$pdf->Cell(190,5,fecha_letra($_POST['fecha_ini']),0,0,'C');
	$pdf->Ln();
	$pdf->SetFont("Arial","",8);
//	$pdf->Cell(190,5,"(miles de pesos)",0,0,'C');
	$pdf->Ln();
	/*$pdf->SetFont("Arial","B",10);
	$pdf->Cell(30,5,"Tipo Plaza",1,0,'C');
	$pdf->Cell(70,5,"Centro",1,0,'C');
	$pdf->Cell(20,5,"Aforo",1,0,'C');
	$pdf->Cell(20,5,"Rechazo",1,0,'C');
	$pdf->Cell(20,5,"Total",1,0,'C');
	$pdf->Cell(30,5,"Importe",1,0,'C');*/
		$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$html= '<b>Importe = Ventas (Efectivo y Tarjeta de Credito) + Pagos Caja - Devoluciones</b><br><table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	$html.= '<tr bgcolor="#E9F2F8"><th>Tipo</th><th>Centro</th>';
	$html.= '<th>Aforo</th><th>Rechazo</th><th>Total</th><th>Importe</th><th>Porcentaje</th></tr>'; 
	$sumacargo=array(0,0,0,0);
	$x=0;
	$array_importes_plaza=array();
	$data=array();
	foreach($array_plazas as $k=>$v){
//		rowc();
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."' AND estatus!='C'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$array_importes_plaza[$k][0]=$row2[0];
		$array_importes_plaza[$k][1]=$row2[1];
		$array_importes_plaza[$k][2]=$row2[0]+$row2[1];
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$array_importes_plaza[$k][3]=$row[1]-$row1[1];
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
		$data[$k][0] = $v;
		$data[$k][1] += round($row[1]-$row1[1],2);
	}
	foreach($array_plazas as $k=>$v){
	//	rowc();
		if($x==0){
			$html.= '<td rowspan="'.count($array_plazas).'">Capital.</td>';
		}
		$html.= '<td>'.htmlentities(utf8_encode($v)).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][0],0).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][1],0).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][2],0).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][3],2).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][3]*100/$sumacargo[3],1).'</td>';
		$html.= '</tr>';
		$x++;
	}
	$c=4;
	$html.= '<tr>';
	$html.= '<td bgcolor="#E9F2F8" align="right" colspan="2">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		if($k>2) $decimal=2;
		else $decimal=0;
		$html.= '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	$html.= '<td bgcolor="#E9F2F8" align="right">&nbsp;100%</td>';
	$html.= '</tr>';
	$html.= '</table>';
	if(count($data)>0){
		$data2 = array();
		foreach($data as $datos){
			$data2[] = array($datos[0], $datos[1]);
		}
		//$reporte.='<img src="graficabar.php?fecha_ini='.$_POST['fecha_ini'].'&fecha_fin='.$_POST['fecha_fin'].'&reporte=desglose_cuentas_grupo">';
		require_once("../phplot/phplot.php");
		$plot = new PHPlot(1000,800);
		$plot->SetFileFormat("jpg");
		$plot->SetFailureImage(False);
		//$plot->SetPrintImage(False);
		$plot->SetIsInline(True);
		$plot->SetOutputFile("grafica.jpg");
		$plot->SetImageBorderType('plain');
		$plot->SetDataType('text-data-yx');
		$plot->SetXDataLabelPos('plotin');
		$plot->SetDataValues($data2);
		$plot->SetPlotType('bars');
		//foreach ($data as $row) $plot->SetLegend($row[0]); // Copy labels to legend
		$plot->SetXTickLabelPos('none');
		$plot->SetXTickPos('none');
		$plot->DrawGraph();
		$html .= '<img src="grafica.jpg?'.date("Y-m-d H:i:s").'">';
	}
	$pdf->Image('grafica.jpg',10,30,200);
//	echo $html;
	
	$pdf->Output();
	exit();
}


if($_POST['ajax']==1){
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$html= '<b>Importe = Ventas (Efectivo y Tarjeta de Credito) + Pagos Caja - Devoluciones</b><br><table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	$html.= '<tr bgcolor="#E9F2F8"><th>Tipo</th><th>Centro</th>';
	$html.= '<th>Aforo</th><th>Rechazo</th><th>Total</th><th>Importe</th><th>Porcentaje</th></tr>'; 
	$sumacargo=array(0,0,0,0);
	$x=0;
	$array_importes_plaza=array();
	$data=array();
	foreach($array_plazas as $k=>$v){
		rowc();
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."' AND estatus!='C'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$array_importes_plaza[$k][0]=$row2[0];
		$array_importes_plaza[$k][1]=$row2[1];
		$array_importes_plaza[$k][2]=$row2[0]+$row2[1];
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$array_importes_plaza[$k][3]=$row[1]-$row1[1];
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
		$data[$k][0] = $v;
		$data[$k][1] += round($row[1]-$row1[1],2);
	}
	foreach($array_plazas as $k=>$v){
		rowc();
		if($x==0){
			$html.= '<td rowspan="'.count($array_plazas).'">Capital.</td>';
		}
		$html.= '<td>'.htmlentities(utf8_encode($v)).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][0],0).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][1],0).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][2],0).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][3],2).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][3]*100/$sumacargo[3],1).'</td>';
		$html.= '</tr>';
		$x++;
	}
	/*echo '<tr><td colspan="6">&nbsp;</td></tr>';
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 2 ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$x=0;
	foreach($array_plazas as $k=>$v){
		rowc();
		if($x==0){
			echo '<td rowspan="'.count($array_plazas).'">Municipios</td>';
		}
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		echo '<td align="right">'.number_format($row2[0],0).'</td>';
		echo '<td align="right">'.number_format($row2[1],0).'</td>';
		echo '<td align="right">'.number_format($row2[0]+$row2[1],0).'</td>';
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		echo '<td align="right">'.number_format($row[1]-$row1[1],2).'</td>';
		echo '</tr>';
		$x++;
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
	}*/
	$c=4;
	$html.= '<tr>';
	$html.= '<td bgcolor="#E9F2F8" align="right" colspan="2">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		if($k>2) $decimal=2;
		else $decimal=0;
		$html.= '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	$html.= '<td bgcolor="#E9F2F8" align="right">&nbsp;100%</td>';
	$html.= '</tr>';
	
	/*echo '<tr><td colspan="6">&nbsp;</td></tr>';
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 3 ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$x=0;
	$sumacargo2=array(0,0,0,0);
	foreach($array_plazas as $k=>$v){
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
		
		$sumacargo2[0]+=$row2[0];
		$sumacargo2[1]+=$row2[1];
		$sumacargo2[2]+=$row2[0]+$row2[1];
		$sumacargo2[3]+=$row[1]-$row1[1];
	}
	echo '<tr>';
	echo '<td bgcolor="#E9F2F8" align="center" colspan="2">&nbsp;Unidades Moviles</td>';
	foreach($sumacargo2 as $k=>$v){
		if($k>2) $decimal=2;
		else $decimal=0;
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';
	echo '<tr><td colspan="6">&nbsp;</td></tr>';
	echo '<tr>';
	echo '<td bgcolor="#E9F2F8" align="right" colspan="2">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		if($k>2) $decimal=2;
		else $decimal=0;
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';*/
	$html.= '</table>';
	if(count($data)>0){
		$data2 = array();
		foreach($data as $datos){
			$data2[] = array($datos[0], $datos[1]);
		}
		//$reporte.='<img src="graficabar.php?fecha_ini='.$_POST['fecha_ini'].'&fecha_fin='.$_POST['fecha_fin'].'&reporte=desglose_cuentas_grupo">';
		require_once("../phplot/phplot.php");
		$plot = new PHPlot(1000,800);
		$plot->SetFileFormat("jpg");
		$plot->SetFailureImage(False);
		//$plot->SetPrintImage(False);
		$plot->SetIsInline(True);
		$plot->SetOutputFile("grafica.jpg");
		$plot->SetImageBorderType('plain');
		$plot->SetDataType('text-data-yx');
		$plot->SetXDataLabelPos('plotin');
		$plot->SetDataValues($data2);
		$plot->SetPlotType('bars');
		//foreach ($data as $row) $plot->SetLegend($row[0]); // Copy labels to legend
		$plot->SetXTickLabelPos('none');
		$plot->SetXTickPos('none');
		$plot->DrawGraph();
		$html .= '<img src="grafica.jpg?'.date("Y-m-d H:i:s").'">';
	}
	echo $html;
	exit();
}


top($_SESSION);
	

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'informe_ventas.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'informe_ventas.php\',\'_blank\',101,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<input type="hidden" name="localidad" id="localidad" value="1"></td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
bottom();
echo '
<Script language="javascript">
	function buscarRegistros(){
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","informe_ventas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&localidad="+document.getElementById("localidad").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
		
	';	
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(0,1); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
	

	</Script>
';

?>