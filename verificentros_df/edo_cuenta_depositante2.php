<?php
include ("main.php"); 
/*** ARREGLOS ***********************************************************/

$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}


$rsconductor=mysql_db_query($base,"SELECT * FROM depositantes WHERE plaza = '".$_POST['plazausuario']."'");
while($Conductor=mysql_fetch_array($rsconductor)){
	$array_depositante[$Conductor['cve']]=$Conductor['nombre'];
}

$array_tipo_pago = array();
$res = mysql_query("SELECT * FROM tipos_pago WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}

$array_tipo_venta[0] = 'Normal';
$res = mysql_query("SELECT * FROM tipo_venta ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_venta[$row['cve']] = $row['nombre'];
}

if($_POST['cmd']==100){
    ob_end_clean();

    include('../fpdf153/fpdf.php');
	include("../numlet.php");
	$pdf=new FPDF('P','mm','LETTER');
	$pdf->AddPage();
	$pdf->SetXY(20,20);
	//$pdf->Image('images/membrete.JPG',30,3,150,15);
	$pdf->Cell(0,10,"VEREFICENTROS",0,0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','',17);
	$pdf->Cell(0,10,"Listado de Estado de Depositantes del ".$_POST['fecha_ini'].' al '.$_POST['fecha_fin'],0,0,"L");
    $pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','',9);
	$pdf->Cell(0,6,"".fechaLocal()."  ".horaLocal(),0,0,"L");
	$pdf->Ln();
	$pdf->Cell(84,6,"Nombre",1,0,'C');
	$pdf->Cell(28,6,"Saldo Anterior",1,0,'C');
	$pdf->Cell(28,6,"Cargos",1,0,'C');
	$pdf->Cell(28,6,"Abonos",1,0,'C');
	$pdf->Cell(28,6,"Saldo",1,0,'C');
	$pdf->Ln();
	$i=0;
	$x=0;
	$totales=array();
		for($i=0;$i<count($_POST['depositantes']);$i++){
		  $select= " SELECT * FROM depositante WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['depositantes'][$i]."' ";
	      $res=mysql_db_query($base,$select);
		  $row=mysql_fetch_array($res);
         // 
	     // $pdf->Ln();
			$pdf->Cell(84,6,"".$row['nombre'],1,0,'C');
			$saldo_anterior = saldo_depositante2($row['cve'],1,0,$_POST['fecha_ini'],"");
			$cargo = saldo_depositante2($row['cve'],2,1,$_POST['fecha_ini'],$_POST['fecha_fin']);
			$abono = saldo_depositante2($row['cve'],2,2,$_POST['fecha_ini'],$_POST['fecha_fin']);
			$pdf->Cell(28,6,"".number_format($saldo_anterior,2),1,0,'C');
			$pdf->Cell(28,6,"".number_format($cargo,2),1,0,'C');
			$pdf->Cell(28,6,"".number_format($abono,2),1,0,'C');
			$pdf->Cell(28,6,"".number_format($saldo_anterior+$abono-$cargo,2),1,0,'C');
			$totales[0]+=$saldo_anterior;
			$totales[1]+=$cargo;
			$totales[2]+=$abono;
			$totales[3]+=$saldo_anterior+$abono-$cargo;
			$pdf->Ln();
			}
			$pdf->Cell(28,6,"".$i." Registro(s)",0,0,'L');
			$pdf->Cell(28,6,"",0,0,'');
			$pdf->Cell(28,6,"Totales",0,0,'C');
		foreach($totales as $v){
			$pdf->Cell(28,6,"".number_format($v,2),0,0,'C');
			}
		
	
	$pdf->Output();
	exit();	
}
if($_POST['cmd']==102){

    include('../fpdf153/fpdf.php');
	include("../numlet.php");
	$pdf=new FPDF('P','mm','LETTER');
	$pdf->AddPage();
	$pdf->SetXY(20,20);
	//$pdf->Image('images/membrete.JPG',30,3,150,15);
	
	$pdf->SetFont('Arial','',13);
	$pdf->Cell(0,10,"VEREFICENTROS",0,0,'C');
	$pdf->Ln();
	
	$i=0;
	for($i=0;$i<count($_POST['depositantes']);$i++){
		$res=mysql_db_query($base,"SELECT * FROM depositantes WHERE plaza = '".$_POST['plazausuario']."' AND cve='".$_POST['depositantes'][$i]."'");
		$row=mysql_fetch_array($res);
		$cveori=$row['cve_ori'];
		$pdf->SetFont('Arial','',13);
		$pdf->Cell(0,10,"Estado de Cuenta del Depositante ".$row['nombre']." ".fechaLocal()." ".horaLocal(),0,0,"L");
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetFont('Arial','',9);
		$pdf->Cell(18,6,"Fecha",1,0,'C');
		$pdf->Cell(65,6,"Motivo",1,0,'C');
		$pdf->Cell(20,6,"Cargo",1,0,'C');
		$pdf->Cell(20,6,"Abono",1,0,'C');
		$pdf->Cell(20,6,"Saldo",1,0,'C');
		$pdf->Cell(55,6,"Observaciones",1,0,'C');
		$pdf->Ln();
		$x=$abono=$cargo=0;
		if($_POST['fecha_ini']<"2013-10-01") $fecha="2013-10-01";
		else $fecha=$_POST['fecha_ini'];
		$saldo = saldo_depositante2($row['cve'],1,0,$fecha,"");
		$pdf->Cell(18,6,$fecha,1,0,'C');
		$pdf->Cell(65,6,"Saldo Anterior",1,0,'C');
		$pdf->Cell(20,6," ",1,0,'C');
		$pdf->Cell(20,6," ",1,0,'C');
		$pdf->Cell(20,6,number_format($saldo,2),1,0,'C');
		$pdf->Cell(55,6," ",1,0,'C');
		$pdf->Ln();
		$pdf->Ln();
		while($fecha<=$_POST['fecha_fin']){
			
			$res=mysql_db_query($base,"SELECT * FROM cobro_engomado WHERE fecha>='2016-01-01' AND depositante='".$_POST['depositantes'][$i]."' AND estatus!='C' AND fecha='$fecha'");
			while($row=mysql_fetch_array($res)){
				if($row['tipo_pago']==6){
					$abono+=$row['monto'];
					$saldo+=$row['monto'];
					$pdf->Cell(18,6,$fecha.' '.$row['hora'],1,0,'C');
					$pdf->Cell(65,6,"Venta de Engomado Anticipado # ".$row['cve'],1,0,'C');
					$pdf->Cell(20,6,number_format(0,2),1,0,'C');
					$pdf->Cell(20,6,number_format($row['monto'],2),1,0,'C');
					$pdf->Cell(20,6,number_format($saldo,2),1,0,'C');
					$pdf->Cell(55,6,$row['concepto'],1,0,'C');
					$pdf->Ln();
				}
				elseif($row['tipo_pago']==2){
					$cargo+=$row['monto'];
					$saldo-=$row['monto'];
					$pdf->Cell(18,6,$fecha.' '.$row['hora'],1,0,'C');
					$pdf->Cell(65,6,"Venta de Engomado a Credito # ".$row['cve'],1,0,'C');
					$pdf->Cell(20,6,number_format($row['monto'],2),1,0,'C');
					$pdf->Cell(20,6,number_format(0,2),1,0,'C');
					$pdf->Cell(20,6,number_format($saldo,2),1,0,'C');
					$pdf->Cell(55,6,$row['concepto'],1,0,'C');
					$pdf->Ln();
				}
				else{
					$cargo+=$row['monto'];
					$abono+=$row['monto'];
					$pdf->Cell(18,6,$fecha.' '.$row['hora'],1,0,'C');
					$pdf->Cell(65,6,"Venta de Engomado a Contado # ".$row['cve'],1,0,'C');
					$pdf->Cell(20,6,number_format($row['monto'],2),1,0,'C');
					$pdf->Cell(20,6,number_format($row['monto'],2),1,0,'C');
					$pdf->Cell(20,6,number_format($saldo,2),1,0,'C');
					$pdf->Cell(55,6,$row['concepto'],1,0,'C');
					$pdf->Ln();
				}
				$x++;
			}
			
			$fecha=date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fecha) ) );
		}
		$pdf->Cell(18,6,$x.' Registros',1,0,'C');
		$pdf->Cell(65,6," ",1,0,'C');
		$pdf->Cell(20,6,number_format($cargo,2),1,0,'C');
		$pdf->Cell(20,6,number_format($abono,2),1,0,'C');
		$pdf->Cell(20,6,number_format($saldo,2),1,0,'C');
		$pdf->Cell(55,6," ",1,0,'C');
		$pdf->Ln();
	}
	$pdf->Output();
	exit();	
}

if($_POST['cmd']==101){
	echo '<html><body>';

	$res=mysql_db_query($base,"SELECT * FROM depositantes WHERE plaza = '".$_POST['plazausuario']."' AND cve=".$_POST['reg']);
	$row=mysql_fetch_array($res);
	$cveori=$row['cve_ori'];
	echo '<table align="center">';
	//echo '<tr><td><img src="images/membrete.JPG"></td></tr>';
	echo '</table>';
	echo '<br>';
	echo '<h2>Estado de Cuenta del Depositante '.$row['nombre'].'</h2>'.fechaLocal().' '.horaLocal().'</br>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>Fecha</th><th>Motivo</th><th>Cargo</th><th>Abono</th><th>Saldo</th><th>Observaciones</th>';
	echo '</tr>';
	$x=$abono=$cargo=0;
	rowb();
	$fecha=$_POST['fecha_ini'];
	if($_POST['fecha_ini']<"2013-10-01") $fecha="2013-10-01";
	else $fecha=$_POST['fecha_ini'];
	$saldo = saldo_depositante2($row['cve'],1,0,$fecha,"");
	echo '<td align=center>&nbsp;'.$fecha.'</td>';
	echo '<td align=left>&nbsp;Saldo Anterior</td>';
	echo '<td align="right">&nbsp;</td>';
	echo '<td align="right">&nbsp;</td>';
	echo '<td align="right">'.number_format($saldo,2).'</td>';
	echo '<td align="left">&nbsp;</td>';
	echo '</tr>';
	while($fecha<=$_POST['fecha_fin']){
		$res=mysql_db_query($base,"SELECT * FROM cobro_engomado WHERE fecha>='2016-01-01' AND depositante='".$_POST['reg']."' AND estatus!='C' AND fecha='$fecha'");
		while($row=mysql_fetch_array($res)){
			if($row['tipo_pago']==6){
				$abono+=abs($row['monto']);
				$saldo+=abs($row['monto']);
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.' '.$row['hora'].'</td>';
				echo '<td align=left>Venta de Engomado Anticipado # '.$row['cve'].'&nbsp;</td>';
				echo '<td align="right">&nbsp;</td>';
				echo '<td align="right">'.number_format(abs($row['monto']),2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align="left">&nbsp;</td>';
				echo '</tr>';
			}
			elseif($row['tipo_pago']==2){
				$cargo+=abs($row['monto']);
				$saldo-=abs($row['monto']);
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.' '.$row['hora'].'</td>';
				echo '<td align=left>Venta de Engomado a Credito # '.$row['cve'].'&nbsp;</td>';
				echo '<td align="right">'.number_format(abs($row['monto']),2).'</td>';
				echo '<td align="right">&nbsp;</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align="left">&nbsp;</td>';
				echo '</tr>';
			}
			else{
				$cargo+=abs($row['monto']);
				$abono+=abs($row['monto']);
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.' '.$row['hora'].'</td>';
				echo '<td align=left>Venta de Engomado a Contado # '.$row['cve'].'&nbsp;</td>';
				echo '<td align="right">'.number_format(abs($row['monto']),2).'</td>';
				echo '<td align="right">'.number_format(abs($row['monto']),2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align="left">&nbsp;</td>';
				echo '</tr>';
			}
			$x++;
		}
		
		$fecha=date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fecha) ) );
	}
	echo '	
			<tr>
			<td colspan="2" bgcolor="#E9F2F8">'.$x.' Registro(s)</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($cargo,2).'</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($abono,2).'</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($saldo,2).'</td>
			<td colspan="2" bgcolor="#E9F2F8">&nbsp;</td>
			</tr>';
	echo '</table>';
	echo '<script>window.print();</script>
	</body></html>';
	exit();
}

if($_POST['ajax']==1){

	$select= " SELECT * FROM depositantes WHERE plaza='".$_POST['plazausuario']."' AND edo_cuenta=1 ";
	if ($_POST['nombre']!="") { $select.=" AND nombre LIKE '%".$_POST['nombre']."%'"; }
	$select.=" ORDER BY nombre";
	$res=mysql_db_query($base,$select);
	if(mysql_num_rows($rsconductor)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8">';
		echo '<th><input type="checkbox" onClick="if(this.checked) $(\'.chks\').attr(\'checked\',\'checked\'); else $(\'.chks\').removeAttr(\'checked\');"></th>
		<th>Nombre</th>';
		$nivel = nivelUsuario();
		if($_POST['cveusuario']==1) echo '<th>Saldo</th>';
		echo '<th>Saldo Anterior</th><th>Cargos<th>Abonos</th><th>Saldo</th>';
		echo '</tr>';
		$i=0;
		$x=0;
		$totales=array();
		while($row=mysql_fetch_array($res)){
			rowb();
			echo'<td align="center"><input type="checkbox" name="depositantes[]" class="chks" value="'.$row['cve'].'"></td>';
			echo '<td align="left">'.utf8_encode($row['nombre']).'</td>';
			if($_POST['cveusuario']==1){
				echo '<td align="center"><input type="text" class="textField" id="saldo_'.$row['cve'].'" value="'.$row['saldo2016'].'" size="10">
				<br><input type="button" class="textField" value="Guardar" onClick="guardarsaldo('.$row['cve'].')"></td>';
			}
			$saldo_anterior = saldo_depositante2($row['cve'],1,0,$_POST['fecha_ini'],"");
			$cargo = saldo_depositante2($row['cve'],2,1,$_POST['fecha_ini'],$_POST['fecha_fin']);
			$abono = saldo_depositante2($row['cve'],2,2,$_POST['fecha_ini'],$_POST['fecha_fin']);
			echo '<td align="right">'.number_format($saldo_anterior,2).'</td>';
			echo '<td align="right">'.number_format($cargo,2).'</td>';
			echo '<td align="right">'.number_format($abono,2).'</td>';
			echo '<td align="right"><a href="#" onClick="atcr(\'edo_cuenta_depositante2.php\',\'\',1,'.$row['cve'].')">'.number_format($saldo_anterior+$abono-$cargo,2).'</a></td>';
			echo '</tr>';
			$totales[0]+=$saldo_anterior;
			$totales[1]+=$cargo;
			$totales[2]+=$abono;
			$totales[3]+=$saldo_anterior+$abono-$cargo;
			$i++;
		}
		echo '	
			<tr>
			<td bgcolor="#E9F2F8">'.$i.' Registro(s)</td>';
		if($_POST['cveusuario']==1) echo '<td bgcolor="#E9F2F8">&nbps;</td>';
		echo '
			<td bgcolor="#E9F2F8" align="right">Totales:&nbsp;</td>';
		foreach($totales as $v)
			echo '<td bgcolor="#E9F2F8" align="right">'.number_format($v,2).'</td>';
		echo '
			</tr>
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

	$res=mysql_db_query($base,"SELECT * FROM depositantes WHERE plaza='".$_POST['plazausuario']."' AND cve=".$_POST['depositante']);
	$row=mysql_fetch_array($res);
	echo '<table width="100%">';
	echo '<tr><td class="tableEnc">Estado de Cuenta del Depositante '.utf8_encode($row['nombre']).'</td></tr>';
	echo '</table>';
	echo '<br>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>Fecha</th><th>Motivo</th><th>Cargo</th><th>Abono</th><th>Saldo</th><th>Observaciones</th>';
	echo '</tr>';
	$x=$abono=$cargo=0;
	rowb();
	$fecha=$_POST['fecha_ini'];
	if($_POST['fecha_ini']<"2013-10-01") $fecha="2013-10-01";
	else $fecha=$_POST['fecha_ini'];
	$saldo = saldo_depositante2($row['cve'],1,0,$fecha,"");
	echo '<td align=center>&nbsp;'.$fecha.'</td>';
	echo '<td align=left>&nbsp;Saldo Anterior</td>';
	echo '<td align="right">&nbsp;</td>';
	echo '<td align="right">&nbsp;</td>';
	echo '<td align="right">'.number_format($saldo,2).'</td>';
	echo '<td align="left">&nbsp;</td>';
	echo '</tr>';
	while($fecha<=$_POST['fecha_fin']){
		$res=mysql_db_query($base,"SELECT * FROM cobro_engomado WHERE fecha>='2016-01-01' AND depositante='".$_POST['depositante']."' AND estatus!='C' AND fecha='$fecha'");
		while($row=mysql_fetch_array($res)){
			if($row['tipo_pago']==6){
				$cargo+=abs($row['monto']);
				$saldo-=abs($row['monto']);
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.' '.$row['hora'].'</td>';
				echo '<td align=left>Venta de Engomado Anticipado # '.$row['cve'].'&nbsp;Placa: '.$row['placa'].'</td>';
				echo '<td align="right">'.number_format(abs($row['monto']),2).'</td>';
				echo '<td align="right">&nbsp;</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align="left">Tipo Venta: '.$array_tipo_venta[$row['tipo_venta']].', Tipo Pago: '.$array_tipo_pago[$row['tipo_pago']].'&nbsp;</td>';
				echo '</tr>';
			}
			elseif($row['tipo_pago']==2){
				$cargo+=abs($row['monto']);
				$saldo-=abs($row['monto']);
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.' '.$row['hora'].'</td>';
				echo '<td align=left>Venta de Engomado a Credito # '.$row['cve'].'&nbsp;Placa: '.$row['placa'].'</td>';
				echo '<td align="right">'.number_format(abs($row['monto']),2).'</td>';
				echo '<td align="right">&nbsp;</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align="left">&nbsp;</td>';
				echo '</tr>';
			}
			else{
				$cargo+=abs($row['monto']);
				$abono+=abs($row['monto']);
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.' '.$row['hora'].'</td>';
				echo '<td align=left>Venta de Engomado a Contado # '.$row['cve'].'&nbsp;Placa: '.$row['placa'].'</td>';
				echo '<td align="right">'.number_format(abs($row['monto']),2).'</td>';
				echo '<td align="right">'.number_format(abs($row['monto']),2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align="left">&nbsp;</td>';
				echo '</tr>';
			}
			$x++;
		}
		
		$res=mysql_db_query($base,"SELECT a.* FROM recuperacion_certificado a INNER JOIN cobro_engomado b on b.plaza = a.plaza AND b.cve = a.ticket WHERE a.fecha>='2016-01-01' AND a.estatus!='C' AND a.fecha='$fecha' AND b.depositante='".$_POST['depositante']."' AND b.estatus!='C' AND b.tipo_pago IN (2,6)");
		while($row=mysql_fetch_array($res)){
			if($row['tipo_pago']==6){
				$cargo+=abs($row['recuperacion']);
				$saldo-=abs($row['recuperacion']);
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.' '.$row['hora'].'</td>';
				echo '<td align=left>Recuperacion por Diferencia # '.$row['cve'].'&nbsp;Placa: '.$row['placa'].'</td>';
				echo '<td align="right">'.number_format(abs($row['recuperacion']),2).'</td>';
				echo '<td align="right">&nbsp;</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align="left">&nbsp;</td>';
				echo '</tr>';
			}
			else{
				$cargo+=abs($row['recuperacion']);
				$saldo-=abs($row['recuperacion']);
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.' '.$row['hora'].'</td>';
				echo '<td align=left>Recuperacion por Diferencia # '.$row['cve'].'&nbsp;Placa: '.$row['placa'].'</td>';
				echo '<td align="right">'.number_format(abs($row['recuperacion']),2).'</td>';
				echo '<td align="right">&nbsp;</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align="left">&nbsp;</td>';
				echo '</tr>';
			}
			$x++;
		}
		
		$res=mysql_db_query($base,"SELECT * FROM pagos_caja WHERE fecha>='2016-01-01' AND depositante='".$_POST['depositante']."' AND estatus!='C' AND tipo_pago IN (2,6) AND fecha='$fecha'");
		while($row=mysql_fetch_array($res)){
			if($row['tipo_pago']==6){
				$abono+=abs($row['monto']);
				$saldo+=abs($row['monto']);
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.' '.$row['hora'].'</td>';
				echo '<td align=left>Pago de Engomado Anticipado # '.$row['cve'].'&nbsp;</td>';
				echo '<td align="right">&nbsp;</td>';
				echo '<td align="right">'.number_format(abs($row['monto']),2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align="left">&nbsp;</td>';
				echo '</tr>';
			}
			else{
				$abono+=abs($row['monto']);
				$saldo+=abs($row['monto']);
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.' '.$row['hora'].'</td>';
				echo '<td align=left>Pago de Engomado a Credito # '.$row['cve'].'&nbsp;</td>';
				echo '<td align="right">&nbsp;</td>';
				echo '<td align="right">'.number_format(abs($row['monto']),2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align="left">&nbsp;</td>';
				echo '</tr>';
			}
			$x++;
		}
		
		$res=mysql_db_query($base,"SELECT a.* FROM devolucion_certificado a INNER JOIN cobro_engomado b on b.plaza = a.plaza AND b.cve = a.ticket WHERE a.fecha>='2016-01-01' AND a.estatus!='C' AND a.fecha='$fecha' AND b.depositante='".$_POST['depositante']."' AND b.estatus!='C' AND b.tipo_pago IN (2,6)");
		while($row=mysql_fetch_array($res)){
			if($row['tipo_pago']==6){
				$abono+=abs($row['devolucion']);
				$saldo+=abs($row['devolucion']);
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.' '.$row['hora'].'</td>';
				echo '<td align=left>Devolucion de dinero # '.$row['cve'].'&nbsp;Placa: '.$row['placa'].'</td>';
				echo '<td align="right">'.number_format(abs($row['devolucion']),2).'</td>';
				echo '<td align="right">&nbsp;</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align="left">&nbsp;</td>';
				echo '</tr>';
			}
			else{
				$abono+=abs($row['recuperacion']);
				$saldo+=abs($row['recuperacion']);
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.' '.$row['hora'].'</td>';
				echo '<td align=left>Devolucion de dinero # '.$row['cve'].'&nbsp;Placa: '.$row['placa'].'</td>';
				echo '<td align="right">'.number_format(abs($row['devolucion']),2).'</td>';
				echo '<td align="right">&nbsp;</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align="left">&nbsp;</td>';
				echo '</tr>';
			}
			$x++;
		}
		
		$fecha=date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fecha) ) );
	}
	echo '	
			<tr>
			<td colspan="2" bgcolor="#E9F2F8">'.$x.' Registro(s)</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($cargo,2).'</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($abono,2).'</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($saldo,2).'</td>
			<td colspan="2" bgcolor="#E9F2F8">&nbsp;</td>
			</tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==3){
	mysql_query("UPDATE depositantes SET saldo2016='".$_POST['saldo']."' WHERE cve='".$_POST['depositante']."'");
	exit();
}

if($_POST['ajax']==4){
	mysql_query("UPDATE depositantes SET intentos='".$_POST['intentos']."' WHERE cve='".$_POST['depositante']."'");
	exit();
}

top($_SESSION);
echo '<input type="hidden" name="rep" value="2">';
if($_POST['cmd']==1){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscar_cargos(\''.$_POST['reg'].'\');"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar Cargos</a></td>
			<td><a href="#" onclick="atcr(\'edo_cuenta_depositante2.php\',\'_blank\',101,'.$_POST['reg'].');"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a></td>
			<td><a href="#" onclick="atcr(\'edo_cuenta_depositante2.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>
		</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.$_POST['fecha_ini'].'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.$_POST['fecha_fin'].'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '</table>';
	echo '<br>';
	echo '<input type="hidden" name="sel[]" value="'.$_POST['reg'].'">';
	//Listado
	echo '<div id="idCargos">';
	echo '</div>';
	echo '
	<script>
	function buscar_cargos(depositante)
	{
		if(document.forma.fecha_ini.value<"2009-09-01") document.forma.fecha_ini.value="2009-09-01";
		if(document.forma.fecha_fin.value<"2009-09-01") document.forma.fecha_fin.value="2009-09-01";
		document.getElementById("idCargos").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","edo_cuenta_depositante2.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&depositante="+depositante+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&plazausuario="+document.forma.plazausuario.value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("idCargos").innerHTML = objeto.responseText;}
			}
		}
	}
	window.onload = function () {
			buscar_cargos(\''.$_POST['reg'].'\'); //Realizar consulta de todos los registros al iniciar la forma.
	}
	</script>';
}


if($_POST['cmd']<1){
	/*** PAGINA PRINCIPAL **************************************************/

		//Busqueda
		//<td><a href="#" onclick="atcr(\'edo_cuenta_parque.php\',\'_blank\',101,'.$_POST['reg'].');"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir Detalles</td>
		if($_POST['rep']!=2){
			$_POST['fecha_ini']=date("Y-m").'-01';
			$_POST['fecha_fin']=date("Y-m-d");
		}
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'edo_cuenta_depositante2.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir</td>
				<td><a href="#" onclick="atcr(\'edo_cuenta_depositante2.php\',\'_blank\',102,0);"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir Detalle</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.$_POST['fecha_ini'].'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.$_POST['fecha_fin'].'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Nombre</td><td><input type="text" class="textField" name="nombre" id="nombre" size="30"></td></tr>';
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';

}
bottom();
echo '
<Script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","edo_cuenta_depositante2.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nombre="+document.getElementById("nombre").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&cveusuario="+document.getElementById("cveusuario").value);
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
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}

	echo '

	function guardarsaldo(depositante){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","edo_cuenta_depositante2.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=3&depositante="+depositante+"&saldo="+document.getElementById("saldo_"+depositante).value+"&plazausuario="+document.getElementById("plazausuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&cveusuario="+document.getElementById("cveusuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros();}
			}
		}
	}

	function guardarintentos(depositante){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","edo_cuenta_depositante2.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=4&depositante="+depositante+"&intentos="+document.getElementById("numintentos_"+depositante).value+"&plazausuario="+document.getElementById("plazausuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&cveusuario="+document.getElementById("cveusuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros();}
			}
		}
	}
	
	</Script>
';


?>