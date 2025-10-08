<?php
include("main.php");

//ARREGLOS

//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$res = mysql_query("SELECT * FROM anios_certificados WHERE 1 ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
}

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);
$rechazos = "9,19";

$abono=0;

if($_POST['cmd']==100){
	require_once('fpdf153/fpdf.php');
	$pdf = new FPDF('P','mm','LETTER');
	$pdf->AddPage();
	
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 1 ORDER BY b.localidad_id, a.lista, a.numero");
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
		if($_POST['anio'] != 'all') $select .= " AND a.anio='".$_POST['anio']."'";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$select1= " SELECT COUNT(a.cve),SUM(a.recuperacion)
			FROM recuperacion_certificado as a";
		$select1 .= " INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND a.ticket = b.cve";
		$select1 .= " WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C' AND b.tipo_pago NOT IN (2,6)";
		if($_POST['anio'] != 'all') $select1 .= " AND b.anio = '".$_POST['anio']."'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		/*if($_POST['anio'] == 'all'){
			$res4=mysql_query("SELECT SUM(monto),COUNT(cve)  FROM devolucion_ajuste a WHERE plaza='".$k."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C'");
			$row4=mysql_fetch_array($res4);
			$row[1]-=$row4[0];
		}*/
		$pdf->Cell(70,4,$v,1,0,'L');
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		if($_POST['anio'] != 'all') $select .= " AND anio='".$_POST['anio']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$pdf->Cell(20,4,number_format($row2[0],0),1,0,'C');
		$pdf->Cell(20,4,number_format($row2[1],0),1,0,'C');
		$pdf->Cell(20,4,number_format($row2[0]+$row2[1],0),1,0,'C');
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a";
		$select .= " INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND a.ticket = b.cve";
		$select .= " WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C' AND b.tipo_pago NOT IN (2,6)";
		if($_POST['anio'] != 'all') $select .= " AND b.anio = '".$_POST['anio']."'";
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
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 2 ORDER BY b.localidad_id, a.lista, a.numero");
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
		if($_POST['anio'] != 'all') $select .= " AND a.anio='".$_POST['anio']."'";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$select1= " SELECT COUNT(a.cve),SUM(a.recuperacion)
			FROM recuperacion_certificado as a";
		$select1 .= " INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND a.ticket = b.cve AND b.anio = '".$_POST['anio']."'";
		$select1 .= " WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C' AND b.tipo_pago NOT IN (2,6)";
		if($_POST['anio'] != 'all') $select1 .= " AND b.anio = '".$_POST['anio']."'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		/*if($_POST['anio'] == 'all'){
			$res4=mysql_query("SELECT SUM(monto),COUNT(cve)  FROM devolucion_ajuste a WHERE plaza='".$k."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C'");
			$row4=mysql_fetch_array($res4);
			$row[1]-=$row4[0];
		}*/
		$pdf->Cell(70,4,$v,1,0,'L');
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		if($_POST['anio'] != 'all') $select .= " AND anio='".$_POST['anio']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$pdf->Cell(20,4,number_format($row2[0],0),1,0,'C');
		$pdf->Cell(20,4,number_format($row2[1],0),1,0,'C');
		$pdf->Cell(20,4,number_format($row2[0]+$row2[1],0),1,0,'C');
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a";
		$select .= " INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND a.ticket = b.cve AND b.anio = '".$_POST['anio']."'";
		$select .= " WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C' AND b.tipo_pago NOT IN (2,6)";
		if($_POST['anio'] != 'all') $select .= " AND b.anio = '".$_POST['anio']."'";
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
	$pdf->Ln();
	
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 3 ORDER BY b.localidad_id, a.lista, a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$x=0;
	$sumacargo2=array(0,0,0,0);
	foreach($array_plazas as $k=>$v){
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		if($_POST['anio'] != 'all') $select .= " AND a.anio='".$_POST['anio']."'";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$select1= " SELECT COUNT(a.cve),SUM(a.recuperacion)
			FROM recuperacion_certificado as a";
		$select1 .= " INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND a.ticket = b.cve AND b.anio = '".$_POST['anio']."'";
		$select1 .= " WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C' AND b.tipo_pago NOT IN (2,6)";
		if($_POST['anio'] != 'all') $select1 .= " AND b.anio = '".$_POST['anio']."'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		/*if($_POST['anio'] == 'all'){
			$res4=mysql_query("SELECT SUM(monto),COUNT(cve)  FROM devolucion_ajuste a WHERE plaza='".$k."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C'");
			$row4=mysql_fetch_array($res4);
			$row[1]-=$row4[0];
		}*/
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		if($_POST['anio'] != 'all') $select .= " AND anio='".$_POST['anio']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a";
		$select .= " INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND a.ticket = b.cve AND b.anio = '".$_POST['anio']."'";
		$select .= " WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C' AND b.tipo_pago NOT IN (2,6)";
		if($_POST['anio'] != 'all') $select .= " AND b.anio = '".$_POST['anio']."'";
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
	}
	$pdf->Output();
	exit();
}

if($_POST['ajax']==1){
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 1 ORDER BY b.localidad_id, a.lista, a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	echo '<tr bgcolor="#E9F2F8"><th>Tipo</th><th>Centro</th>';
	echo '<th>Aforo</th><th>Rechazo</th><th>Total</th><th>Importe</th></tr>'; 
	$sumacargo=array(0,0,0,0);
	$x=0;
	foreach($array_plazas as $k=>$v){
		rowc();
		if($x==0){
			echo '<td rowspan="'.count($array_plazas).'">Capital</td>';
		}
//		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago =1 ,a.monto,0)),SUM(IF(a.estatus='C',1,0))
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
		
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		if($_POST['anio'] != 'all') $select .= " AND a.anio='".$_POST['anio']."'";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$select1= " SELECT COUNT(a.cve),SUM(a.recuperacion)
			FROM recuperacion_certificado as a";
		$select1 .= " INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND a.ticket = b.cve AND b.anio = '".$_POST['anio']."'";
		$select1 .= " WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C' AND b.tipo_pago NOT IN (2,6)";
		if($_POST['anio'] != 'all') $select1 .= " AND b.anio = '".$_POST['anio']."'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		/*if($_POST['anio'] == 'all'){
			$res4=mysql_query("SELECT SUM(monto),COUNT(cve)  FROM devolucion_ajuste a WHERE plaza='".$k."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C'");
			$row4=mysql_fetch_array($res4);
			$row[1]-=$row4[0];
		}*/
		echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		if($_POST['anio'] != 'all') $select .= " AND anio='".$_POST['anio']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		echo '<td align="right">'.number_format($row2[0],0).'</td>';
		echo '<td align="right">'.number_format($row2[1],0).'</td>';
		echo '<td align="right">'.number_format($row2[0]+$row2[1],0).'</td>';
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a";
		$select .= " INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND a.ticket = b.cve AND b.anio = '".$_POST['anio']."'";
		$select .= " WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C' AND b.tipo_pago NOT IN (2,6)";
//		$select .= " WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C' AND b.tipo_pago = 1";
		if($_POST['anio'] != 'all') $select .= " AND b.anio = '".$_POST['anio']."'";
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
	echo '<tr><td colspan="6">&nbsp;</td></tr>';
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 2 ORDER BY b.localidad_id, a.lista, a.numero");
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
		if($_POST['anio'] != 'all') $select .= " AND a.anio='".$_POST['anio']."'";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$select1= " SELECT COUNT(a.cve),SUM(a.recuperacion)
			FROM recuperacion_certificado as a ";
		$select1 .= " INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND a.ticket = b.cve AND b.anio = '".$_POST['anio']."'";
		$select1 .= " WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C' AND b.tipo_pago NOT IN (2,6)";
		if($_POST['anio'] != 'all') $select1 .= " AND b.anio = '".$_POST['anio']."'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		/*if($_POST['anio'] == 'all'){
			$res4=mysql_query("SELECT SUM(monto),COUNT(cve)  FROM devolucion_ajuste a WHERE plaza='".$k."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C'");
			$row4=mysql_fetch_array($res4);
			$row[1]-=$row4[0];
		}*/
		echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		if($_POST['anio'] != 'all') $select .= " AND anio='".$_POST['anio']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		echo '<td align="right">'.number_format($row2[0],0).'</td>';
		echo '<td align="right">'.number_format($row2[1],0).'</td>';
		echo '<td align="right">'.number_format($row2[0]+$row2[1],0).'</td>';
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a ";
		$select .= " INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND a.ticket = b.cve AND b.anio = '".$_POST['anio']."'";
		$select .= " WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C' AND b.tipo_pago NOT IN (2,6)";
		if($_POST['anio'] != 'all') $select .= " AND b.anio = '".$_POST['anio']."'";
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
	echo '<td bgcolor="#E9F2F8" align="right" colspan="2">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		if($k>2) $decimal=2;
		else $decimal=0;
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';
	
	echo '<tr><td colspan="6">&nbsp;</td></tr>';
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 3 ORDER BY b.localidad_id, a.lista, a.numero");
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
		$select1= " SELECT COUNT(a.cve),SUM(a.recuperacion)
			FROM recuperacion_certificado as a";
		$select1 .= " INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND a.ticket = b.cve AND b.anio = '".$_POST['anio']."'";
		$select1 .= " WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C' AND b.tipo_pago NOT IN (2,6)";
		if($_POST['anio'] != 'all') $select1 .= " AND b.anio = '".$_POST['anio']."'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		/*if($_POST['anio'] == 'all'){
			$res4=mysql_query("SELECT SUM(monto),COUNT(cve)  FROM devolucion_ajuste a WHERE plaza='".$k."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C'");
			$row4=mysql_fetch_array($res4);
			$row[1]-=$row4[0];
		}*/
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a";
		$select .= " INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND a.ticket = b.cve AND b.anio = '".$_POST['anio']."'";
		$select .= " WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C' AND b.tipo_pago NOT IN (2,6)";
		if($_POST['anio'] != 'all') $select .= " AND b.anio = '".$_POST['anio']."'";
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
	echo '</tr>';
	echo '</table>';
	exit();
}


top($_SESSION);
	

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'informe_ventas.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<input type="hidden" name="localidad" id="localidad" value="2"></td></tr>';
		echo '<tr><td>A&ntilde;o Certificacion</td><td><select name="anio" id="anio"><option value="all" selected>Todos</option>';
		foreach($array_anios as $k=>$v){
				echo '<option value="'.$k.'"';
				echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
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
			objeto.send("ajax=1&anio="+document.getElementById("anio").value+"&localidad="+document.getElementById("localidad").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
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