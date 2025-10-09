<?php
include ("main.php"); 
/*** ARREGLOS ***********************************************************/

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}






if($_POST['cmd']==100){
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
		
		$pdf->SetFont('Arial','',13);
		$pdf->Cell(0,10,"Estado de Cuenta de Caja de Gatos  ".fechaLocal()." ".horaLocal(),0,0,"L");
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
		$saldo = saldo_gasto(1,0,$fecha,"");
		$pdf->Cell(18,6,$fecha,1,0,'C');
		$pdf->Cell(65,6,"Saldo Anterior",1,0,'C');
		$pdf->Cell(20,6," ",1,0,'C');
		$pdf->Cell(20,6," ",1,0,'C');
		$pdf->Cell(20,6,number_format($saldo,2),1,0,'C');
		$pdf->Cell(55,6," ",1,0,'C');
		$pdf->Ln();
		$pdf->Ln();
		while($fecha<=$_POST['fecha_fin']){
			
			$res=mysql_query("SELECT * FROM cobro_engomado WHERE depositante='".$_POST['depositantes'][$i]."' AND estatus!='C' AND tipo_pago IN (2,6) AND fecha='$fecha'");
			while($row=mysql_fetch_array($res)){
				if($row['tipo_pago']==6){
					$abono+=$row['monto'];
					$saldo+=$row['monto'];
					$pdf->Cell(18,6,$fecha,1,0,'C');
					$pdf->Cell(65,6,"Venta de Engomado Anticipado # ".$row['cve'],1,0,'C');
					$pdf->Cell(20,6,number_format(0,2),1,0,'C');
					$pdf->Cell(20,6,number_format($row['monto'],2),1,0,'C');
					$pdf->Cell(20,6,number_format($saldo,2),1,0,'C');
					$pdf->Cell(55,6,$row['concepto'],1,0,'C');
					$pdf->Ln();
				}
				else{
					$cargo+=$row['monto'];
					$saldo-=$row['monto'];
					$pdf->Cell(18,6,$fecha,1,0,'C');
					$pdf->Cell(65,6,"Venta de Engomado a Credito # ".$row['cve'],1,0,'C');
					$pdf->Cell(20,6,number_format($row['monto'],2),1,0,'C');
					$pdf->Cell(20,6,number_format(0,2),1,0,'C');
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
	$pdf->Output();
	exit();	
}

if($_POST['cmd']==101){
	echo '<html><body>';
	
	$res=mysql_query("SELECT * FROM depositantes WHERE plaza = '".$_POST['plazausuario']."' AND cve=".$_POST['reg']);
	$row=mysql_fetch_array($res);
	$cveori=$row['cve_ori'];
	echo '<table align="center">';
	//echo '<tr><td><img src="images/membrete.JPG"></td></tr>';
	echo '</table>';
	echo '<br>';
	echo '<h1>Estado de Cuenta Caja Gastos</h1>'.fechaLocal().' '.horaLocal().'</br>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>Fecha</th><th>Motivo</th><th>Cargo</th><th>Abono</th><th>Saldo</th><th>Observaciones</th>';
	echo '</tr>';
	$x=$abono=$cargo=0;
	rowb();
	$fecha=$_POST['fecha_ini'];
	$saldo = saldo_gasto(1,0,$fecha,"");
	echo '<td align=center>&nbsp;'.$fecha.'</td>';
	echo '<td align=left>&nbsp;Saldo Anterior</td>';
	echo '<td align="right">&nbsp;</td>';
	echo '<td align="right">&nbsp;</td>';
	echo '<td align="right">'.number_format($saldo,2).'</td>';
	echo '<td align="left">&nbsp;</td>';
	echo '</tr>';
	while($fecha<=$_POST['fecha_fin']){
		$res=mysql_query("SELECT * FROM cobro_engomado WHERE depositante='".$_POST['reg']."' AND estatus!='C' AND tipo_pago IN (2,6) AND fecha='$fecha'");
		while($row=mysql_fetch_array($res)){
			if($row['tipo_pago']==6){
				$abono+=abs($row['monto']);
				$saldo+=abs($row['monto']);
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.'</td>';
				echo '<td align=left>Venta de Engomado Anticipado # '.$row['cve'].'&nbsp;</td>';
				echo '<td align="right">&nbsp;</td>';
				echo '<td align="right">'.number_format(abs($row['monto']),2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align="left">&nbsp;</td>';
				echo '</tr>';
			}
			else{
				$cargo+=abs($row['monto']);
				$saldo-=abs($row['monto']);
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.'</td>';
				echo '<td align=left>Venta de Engomado a Credito # '.$row['cve'].'&nbsp;</td>';
				echo '<td align="right">'.number_format(abs($row['monto']),2).'</td>';
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
	echo '<script>window.print();</script>
	</body></html>';
	exit();
}

if($_POST['ajax']==1){
	
	
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>Fecha</th><th>Motivo</th><th>Cargo</th><th>Abono</th><th>Saldo</th><th>Observaciones</th>';
	echo '</tr>';
	$x=$abono=$cargo=0;
	rowb();
	$fecha=$_POST['fecha_ini'];
	if($_POST['fecha_ini']<"2013-10-01") $fecha="2013-10-01";
	else $fecha=$_POST['fecha_ini'];
	$saldo = saldo_gasto(1,0,$fecha,"");
	echo '<td align=center>&nbsp;'.$fecha.'</td>';
	echo '<td align=left>&nbsp;Saldo Anterior</td>';
	echo '<td align="right">&nbsp;</td>';
	echo '<td align="right">&nbsp;</td>';
	echo '<td align="right">'.number_format($saldo,2).'</td>';
	echo '<td align="left">&nbsp;</td>';
	echo '</tr>';
	$array_movimientos = array();
	$res = mysql_query("SELECT * FROM depositos_gastos WHERE estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' ORDER BY cve");
	while($row = mysql_fetch_array($res)){
		$array_movimientos[$row['fecha'].' '.$row['hora']] = array(
			'fecha'=>$row['fecha'],
			'descripcion'=>'Deposito a caja #'.$row['cve'],
			'cargo'=>0,
			'abono'=>$row['monto'],
			'obs'=>$row['obs']
		);
	}
	$res = mysql_query("SELECT * FROM comprobacion_gastos WHERE estatus!='C' AND reembolso>0 AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' ORDER BY cve");
	while($row = mysql_fetch_array($res)){
		$array_movimientos[$row['fecha'].' '.$row['hora']] = array(
			'fecha'=>$row['fecha'],
			'descripcion'=>'Reembolso de gasto #'.$row['cve'],
			'cargo'=>0,
			'abono'=>$row['reembolso'],
			'obs'=>$row['obs']
		);
	}
	$res = mysql_query("SELECT * FROM salida_gastos WHERE estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' ORDER BY cve");
	while($row = mysql_fetch_array($res)){
		$array_movimientos[$row['fecha'].' '.$row['hora']] = array(
			'fecha'=>$row['fecha'],
			'descripcion'=>'Vale de gasto #'.$row['cve'],
			'cargo'=>$row['monto'],
			'abono'=>0,
			'obs'=>$row['obs']
		);
	}
	
	ksort($array_movimientos);
	foreach($array_movimientos as $datos){
		$cargo+=$datos['cargo'];
		$abono+=$datos['abono'];
		$saldo+=$datos['abono'];
		$saldo-=$datos['cargo'];
		rowb();
		echo '<td align=center>&nbsp;'.$datos['fecha'].'</td>';
		echo '<td align=left>'.$datos['descripcion'].'</td>';
		if($datos['cargo']>0)
			echo '<td align="right">'.number_format(abs($datos['cargo']),2).'</td>';
		else
			echo '<td align="right">&nbsp;</td>';
		if($datos['abono']>0)
			echo '<td align="right">'.number_format(abs($datos['abono']),2).'</td>';
		else
			echo '<td align="right">&nbsp;</td>';
		echo '<td align="right">'.number_format($saldo,2).'</td>';
		echo '<td align="left">'.$datos['obs'].'&nbsp;</td>';
		echo '</tr>';
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



if($_POST['cmd']<1){
	/*** PAGINA PRINCIPAL **************************************************/

		//Busqueda
		//<td><a href="#" onclick="atcr(\'edo_cuenta_parque.php\',\'_blank\',101,'.$_POST['reg'].');"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir Detalles</td>
				
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>
				<!--<td><a href="#" onclick="atcr(\'edo_cuenta_gastos.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir</td>
				<td><a href="#" onclick="atcr(\'edo_cuenta_gastos.php\',\'_blank\',101,0);"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir HTML</td>-->
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.substr(fechaLocal(),0,8).'01'.'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
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
			objeto.open("POST","edo_cuenta_gastos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value);
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
	
	</Script>
';


?>