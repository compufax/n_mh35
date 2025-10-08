<?php
include ("main.php"); 
/*** ARREGLOS ***********************************************************/

$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}


$array_motivo = array();
$res = mysql_query("SELECT * FROM motivos");
while($row=mysql_fetch_array($res)){
	$array_motivo[$row['cve']]=$row['nombre'];
}


if($_POST['cmd']==102){
    include('fpdf153/fpdf.php');
	include("numlet.php");
	$pdf=new FPDF('P','mm','LETTER');
	$pdf->AddPage();
	$pdf->SetXY(20,20);
	//$pdf->Image('images/membrete.JPG',30,3,150,15);
	
	$pdf->SetFont('Arial','',13);
	$pdf->Cell(0,10,"VEREFICENTROS",0,0,'C');
	$pdf->Ln();
	
	$i=0;
		$pdf->SetFont('Arial','',13);
		$pdf->Cell(0,10,"Estado de Cuenta de Gastos ".fechaLocal()." ".horaLocal(),0,0,"L");
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
		$res1=mysql_query("SELECT SUM(monto) FROM reembolsos WHERE plaza='".$_POST['plazausuario']."' AND fecha_mov<'$fecha' AND estatus!='C'");
		$row1=mysql_fetch_array($res1);
		$res2=mysql_query("SELECT SUM(monto) FROM recibos_salida WHERE plaza='".$_POST['plazausuario']."' AND fecha<'$fecha' AND estatus!='C'");
		$row2=mysql_fetch_array($res2);
		$saldo = $row1[0]-$row2[0];
		$pdf->Cell(18,6,$fecha,1,0,'C');
		$pdf->Cell(65,6,"Saldo Anterior",1,0,'C');
		$pdf->Cell(20,6," ",1,0,'C');
		$pdf->Cell(20,6," ",1,0,'C');
		$pdf->Cell(20,6,number_format($saldo,2),1,0,'C');
		$pdf->Cell(55,6," ",1,0,'C');
		$pdf->Ln();
		$pdf->Ln();
		while($fecha<=$_POST['fecha_fin']){
			
			$res=mysql_db_query($base,"SELECT * FROM reembolsos WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha_mov='$fecha'");
			while($row=mysql_fetch_array($res)){
				$abono+=$row['monto'];
				$saldo+=$row['monto'];
				$pdf->Cell(18,6,$fecha,1,0,'C');
				$pdf->Cell(65,6,"Reembolso # ".$row['cve'],1,0,'C');
				$pdf->Cell(20,6,number_format(0,2),1,0,'C');
				$pdf->Cell(20,6,number_format($row['monto'],2),1,0,'C');
				$pdf->Cell(20,6,number_format($saldo,2),1,0,'C');
				$pdf->Cell(55,6,$row['obs'],1,0,'C');
				$pdf->Ln();
				
				$x++;
			}
			$res=mysql_db_query($base,"SELECT * FROM recibos_salida WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha='$fecha'");
			while($row=mysql_fetch_array($res)){
				$cargo+=$row['monto'];
				$saldo-=$row['monto'];
				$pdf->Cell(18,6,$fecha,1,0,'C');
				$pdf->Cell(65,6,"Recibo de Salida # ".$row['cve'].' '.$array_motivo[$row['motivo']],1,0,'C');
				$pdf->Cell(20,6,number_format($row['monto'],2),1,0,'C');
				$pdf->Cell(20,6,number_format(0,2),1,0,'C');
				$pdf->Cell(20,6,number_format($saldo,2),1,0,'C');
				$pdf->Cell(55,6,$row['concepto'],1,0,'C');
				$pdf->Ln();
				
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
	$pdf->Output();
	exit();	
}



if($_POST['ajax']==2){
	
	echo '<table width="100%">';
	echo '<tr><td class="tableEnc">Estado de Cuenta de Gastos</td></tr>';
	echo '</table>';
	echo '<br>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>Fecha</th><th>Motivo</th><th>Cargo</th><th>Abono</th><th>Saldo</th><th>Observaciones</th>';
	echo '</tr>';
	$x=$abono=$cargo=0;
	$filtro = "";
	if($_POST['tipo_pago'] > 0) $filtro = " AND tipo_pago = '".$_POST['tipo_pago']."'";
	rowb();
	$fecha=$_POST['fecha_ini'];
	if($_POST['fecha_ini']<"2013-10-01") $fecha="2013-10-01";
	else $fecha=$_POST['fecha_ini'];
	$res1=mysql_query("SELECT SUM(monto) FROM reembolsos WHERE plaza='".$_POST['plazausuario']."' AND fecha_mov<'$fecha' AND estatus!='C'");
	$row1=mysql_fetch_array($res1);
	$res2=mysql_query("SELECT SUM(monto) FROM recibos_salida WHERE plaza='".$_POST['plazausuario']."' AND fecha<'$fecha' AND estatus!='C'");
	$row2=mysql_fetch_array($res2);
	$saldo = $row1[0]-$row2[0];
	echo '<td align=center>&nbsp;'.$fecha.'</td>';
	echo '<td align=left>&nbsp;Saldo Anterior</td>';
	echo '<td align="right">&nbsp;</td>';
	echo '<td align="right">&nbsp;</td>';
	echo '<td align="right">'.number_format($saldo,2).'</td>';
	echo '<td align="left">&nbsp;</td>';
	echo '</tr>';
	while($fecha<=$_POST['fecha_fin']){
		
		$res=mysql_db_query($base,"SELECT * FROM reembolsos WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha_mov='$fecha'");
		while($row=mysql_fetch_array($res)){
				$abono+=abs($row['monto']);
				$saldo+=abs($row['monto']);
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.'</td>';
				echo '<td align=left>Reembolso # '.$row['cve'].'&nbsp;</td>';
				echo '<td align="right">&nbsp;</td>';
				echo '<td align="right">'.number_format(abs($row['monto']),2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align="left">'.utf8_encode($row['obs']).'&nbsp;</td>';
				echo '</tr>';
			
			$x++;
		}
		
		$res=mysql_db_query($base,"SELECT * FROM recibos_salida WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha='$fecha'");
		while($row=mysql_fetch_array($res)){
				$cargo+=abs($row['monto']);
				$saldo-=abs($row['monto']);
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.'</td>';
				echo '<td align=left>Recibo de Salida # '.$row['cve'].'&nbsp;'.$array_motivo[$row['motivo']].'</td>';
				echo '<td align="right">'.number_format(abs($row['monto']),2).'</td>';
				echo '<td align="right">&nbsp;</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align="left">'.utf8_encode($row['concepto']).'&nbsp;</td>';
				echo '</tr>';
			
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

top($_SESSION);

if($_POST['cmd']==0){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscar_cargos();"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar Cargos</a></td>
			<td><a href="#" onclick="atcr(\'edo_cuenta_gastos.php\',\'_blank\',102,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a></td>
			
		</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.date('Y-m').'-01" class="readOnly" size="12" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.date('Y-m-d').'" class="readOnly" size="12" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '</table>';
	echo '<br>';
	//Listado
	echo '<div id="idCargos">';
	echo '</div>';
	echo '
	<script>
	function buscar_cargos()
	{
		if(document.forma.fecha_ini.value<"2009-09-01") document.forma.fecha_ini.value="2009-09-01";
		if(document.forma.fecha_fin.value<"2009-09-01") document.forma.fecha_fin.value="2009-09-01";
		document.getElementById("idCargos").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","edo_cuenta_gastos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&plazausuario="+document.forma.plazausuario.value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("idCargos").innerHTML = objeto.responseText;}
			}
		}
	}
	window.onload = function () {
			buscar_cargos(); //Realizar consulta de todos los registros al iniciar la forma.
	}
	</script>';
}




bottom();


?>