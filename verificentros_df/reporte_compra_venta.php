<?php
include ("main.php");
  function lastday() { 
      $month = date('m');
      $year = date('Y');
      $day = date("d", mktime(0,0,0, $month+1, 0, $year));
 
      return date('Y-m-d', mktime(0,0,0, $month, $day, $year));
  };
 
  /** Actual month first day **/
  function firstday() {
      $month = date('m');
      $year = date('Y');
      return date('Y-m-d', mktime(0,0,0, $month, 1, $year));
  }
$array_tipo_pago = array();
$res = mysql_query("SELECT * FROM tipos_pago WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}
$res = mysql_query("SELECT * FROM cat_lineas WHERE plaza='".$_POST['plazausuario']."'");
while($row=mysql_fetch_array($res)){
	$array_lineas[$row['cve']]=$row['nombre'];
}

$res = mysql_query("SELECT * FROM tecnicos WHERE plaza='".$_POST['plazausuario']."'");
while($row=mysql_fetch_array($res)){
	$array_tecnico[$row['cve']]=$row['nombre'];
	$array_tecnico_clave[$row['cve']]=$row['clave'];
}
$array_engomado = array();
$sel="SELECT * FROM engomados WHERE plazas like '%|".$_POST['plazausuario']."|%' AND entrega=1 ORDER BY nombre";
//echo''.$sel.'';
$res = mysql_query($sel);
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio'];
}
if($_POST['cmd']==103){
	ini_set("session.auto_start", 0);
	include('fpdf153/fpdf.php');
	include("numlet.php");	
	$pdf=new FPDF('L','mm','LETTER');
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',25);
	$pdf->Cell(263,10,'Verificentro - '.$array_plaza[$_POST['plazausuario']],0,0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','B',20);
	$pdf->Cell(263,10,'Ingresos de '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'],0,0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$sel="SELECT sum(monto) as efectivo from cobro_engomado where estatus!='C' and tipo_venta =4 and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
	$rs=mysql_query($sel) or die(mysql_error());
	$row=mysql_fetch_array($rs) or die(mysql_error());
	//$pdf->Cell(263,10,'Total de Intentos de 250.00: '.number_format($row[0],2),0,0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','B',7);
	$fecha=$_POST['fecha_ini'];
	$pdf->GetX();
	$pdf->Cell(20,4,'Fecha',1,0,'C');
	$pdf->Cell(20.5,4,'Efectivo',1,0,'C');
	$pdf->Cell(20.5,4,'Pag Ant.',1,0,'C');
	$pdf->Cell(20,4,'Rec Cred.',1,0,'C');
	$pdf->Cell(20,4,'Devoluciones',1,0,'C');
	$pdf->Cell(20,4,'Devoluciones',1,0,'C');
	$pdf->Cell(18,4,'Cometra',1,0,'C');
	$pdf->Cell(18,4,'Pag. Ant. Banc.',1,0,'C');
	$pdf->Cell(18,4,'Rec. Cre. Banc.',1,0,'C');
	$pdf->Cell(18,4,'Tar. Cred.',1,0,'C');
	$pdf->Cell(18,4,'Tar. Debt.',1,0,'C');
	$pdf->Cell(18,4,'Bancos',1,0,'C');
	$pdf->Cell(18,4,'Creditos',1,0,'C');
	$pdf->Cell(20.5,4,'Total de Venta',1,0,'C');
	//$pdf->Cell(28,4,'Total de Ingresos',1,0,'C');
	$pdf->Ln();
	$t0=0; $t1=0; $t2=0; $t3=0; $t4=0; $t5=0; $t6=0; $t7=0; $t8=0; 
	while($fecha<=$_POST['fecha_fin']){
	//rowb();
	$sel="SELECT sum(monto) as efectivo from cobro_engomado where estatus='A'and tipo_venta in('0','3') and tipo_pago='1' and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs=mysql_query($sel) or die(mysql_error());
	$row=mysql_fetch_array($rs) or die(mysql_error());

	$sel1="SELECT sum(if(forma_pago=1,monto,0)) as p_anticipado,sum(if(forma_pago!=1,monto,0)) as bancos from pagos_caja where estatus='A' and tipo_pago='6' and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs1=mysql_query($sel1) or die(mysql_error());
	$row1=mysql_fetch_array($rs1) or die(mysql_error());
	
	$sel2="SELECT sum(devolucion) as m_devoluvion from devolucion_certificado where estatus='A' and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs2=mysql_query($sel2) or die(mysql_error());
	$row2=mysql_fetch_array($rs2) or die(mysql_error());
	
	$sel3="SELECT sum(monto) as t_credito from cobro_engomado where estatus='A' and tipo_venta in('0','3') and tipo_pago='5' and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs3=mysql_query($sel3) or die(mysql_error());
	$row3=mysql_fetch_array($rs3) or die(mysql_error());
	
	$sel5="SELECT sum(monto) as credito from cobro_engomado where estatus='A'and tipo_venta='0' and tipo_pago in ('2') and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs5=mysql_query($sel5) or die(mysql_error());
	$row5=mysql_fetch_array($rs5) or die(mysql_error());
	
	$sel7="SELECT sum(if(forma_pago=1,monto,0)) as r_credito, sum(if(forma_pago!=1,monto,0)) as bancos from pagos_caja where estatus='A' and tipo_pago in('2') and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs7=mysql_query($sel7) or die(mysql_error());
	$row7=mysql_fetch_array($rs7) or die(mysql_error());
	//$row=0;
	//$row5_credito=$row5['credito'] + $row5_1['credito'];	
//	$sel7="SELECT sum(monto) as bono from bonos where estatus='A'and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
//	$rs7=mysql_query($sel7) or die(mysql_error());
//	$row7=mysql_fetch_array($rs7) or die(mysql_error());
	$sel8="SELECT sum(monto) as t_debito from cobro_engomado where estatus='A' 
	and tipo_venta in('0','3') and tipo_pago='7' and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs8=mysql_query($sel8) or die(mysql_error());
	$row8=mysql_fetch_array($rs8) or die(mysql_error());
	
	$sel9="SELECT sum(monto) as vale_descuento FROM devolucion_ajuste WHERE fecha='".$fecha."' AND plaza='".$_POST['plazausuario']."' and estatus='A'";
	$rs9=mysql_query($sel9) or die(mysql_error());
	$row9=mysql_fetch_array($rs9) or die(mysql_error());

	
	$cometra=$row['efectivo']+$row1['p_anticipado']+$row7['r_credito']-$row2['m_devoluvion']-$row9['vale_descuento'];
	$bancos=$cometra+$row3['t_credito']+$row8['t_debito']+$row1['bancos']+$row7['bancos'];
	$t_venta=$bancos+$row5['credito']-$row7['r_credito']-$row7['bancos'];
	$t_ingreso=$t_venta+$row7['r_credito'];
	$pdf->Cell(20,4,''.$fecha,1,0,'C');
	$pdf->Cell(20.5,4,''.number_format($row['efectivo'],2),1,0,'R');
	$pdf->Cell(20.5,4,''.number_format($row1['p_anticipado'],2),1,0,'R');
	$pdf->Cell(20,4,''.number_format($row7['r_credito'],2),1,0,'R');
	$pdf->Cell(20,4,''.number_format($row2['m_devoluvion'],2),1,0,'R');
	$pdf->Cell(20,4,''.number_format($row9['vale_descuento'],2),1,0,'R');
	$pdf->Cell(18,4,''.number_format($cometra,2),1,0,'R');
	$pdf->Cell(18,4,''.number_format($row1['bancos'],2),1,0,'R');
	$pdf->Cell(18,4,''.number_format($row7['bancos'],2),1,0,'R');
	$pdf->Cell(18,4,''.number_format($row3['t_credito'],2),1,0,'R');
	$pdf->Cell(18,4,''.number_format($row8['t_debito'],2),1,0,'R');
	$pdf->Cell(18,4,''.number_format($bancos,2),1,0,'R');
	$pdf->Cell(18,4,''.number_format($row5['credito'],2),1,0,'R');
	$pdf->Cell(20.5,4,''.number_format($t_venta,2),1,0,'R');
//	echo'<td>'.number_format($row7['bono'],2).'</td>';$pdf->Cell(25,4,'',1,0,'R');
	//$pdf->Cell(28,4,''.number_format($t_ingreso,2),1,0,'R');
	$t0=$t0 + $row['efectivo']; 
	$t1=$t1 + $row1['p_anticipado']; 
	$t2=$t2 + $row7['r_credito'];
	$t3=$t3 + $row2['m_devoluvion']; 
	$t12=$t12 + $row9['vale_descuento']; 
	$t4=$t4 + $cometra; 
	$t5=$t5 + $row1['bancos'];
	$t6=$t6 + $row7['bancos'];
	$t7=$t7 + $row3['t_credito'];
	$t8=$t8 + $row8['t_debito'];
	$t9=$t9 + $bancos; 
	$t10=$t10 + $row5['credito']; 
	$t11=$t11 + $t_venta; 
	$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
	$pdf->Ln();
	}
	$pdf->Ln();
	$pdf->Cell(20,4,'Total: ',1,0,'R');
	$pdf->Cell(20.5,4,''.number_format($t0,2),1,0,'R');
	$pdf->Cell(20.5,4,''.number_format($t1,2),1,0,'R');
	$pdf->Cell(20,4,''.number_format($t2,2),1,0,'R');
	$pdf->Cell(20,4,''.number_format($t3,2),1,0,'R');
	$pdf->Cell(20,4,''.number_format($t4,2),1,0,'R');
	$pdf->Cell(18,4,''.number_format($t4,2),1,0,'R');
	$pdf->Cell(18,4,''.number_format($t5,2),1,0,'R');
	$pdf->Cell(18,4,''.number_format($t6,2),1,0,'R');
	$pdf->Cell(18,4,''.number_format($t7,2),1,0,'R');
	$pdf->Cell(18,4,''.number_format($t8,2),1,0,'R');
	$pdf->Cell(18,4,''.number_format($t9,2),1,0,'R');
	$pdf->Cell(18,4,''.number_format($t10,2),1,0,'R');
	$pdf->Cell(20.5,4,''.number_format($t11,2),1,0,'R');
	//$pdf->Cell(28,4,''.number_format($t8,2),1,0,'R');	
	$pdf->Output();
	exit();
}
if($_POST['ajax']==1){
	
	foreach($array_engomado as $k=>$v){
	$sel="SELECT *,right(fecha,2) as dia from certificados where estatus!='C' and engomado ='".$k."' and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."' order by engomado desc";
	$rs=mysql_query($sel) or die(mysql_error());
	//$row=mysql_fetch_array($rs) or die(mysql_error());
//	echo '<h3>Total de Intentos de 250.00: '.number_format($row[0],2).'</h3>';
	//$fecha=$_POST['fecha_ini'];
	echo'<h2>'.$v.'</h2>';
	echo'<table width="100%" border="0">
	     <tr bgcolor="#E9F2F8">
		 <th>Holograma</th>
		 <th>Fecha Utilizado</th>
		<th>Dia de la Semana</th>
		<th>Placa</th>
		<th>Ultimo Digito</th>
		<th>Linea</th>
		<th>Tecnico</th>
		 </tr>';
	while($row=mysql_fetch_array($rs)){
	rowb();
	echo'<td align="center">'.$row['certificado'].'</td>';
	echo'<td align="center">'.$row['fecha'].'</td>';
	echo'<td align="center">'.diaSemana($row['fecha']).'</td>';
	echo'<td align="center">'.$row['placa'].'</td>';
	$resultado =preg_replace("/[^0-9]/", "", $row['placa']);
	$rest = substr($resultado, -1);
	echo'<td align="center">'.$rest.'</td>';
	echo'<td align="center">'.$array_lineas[$row['linea']].'</td>';
	echo'<td align="center">'.$array_tecnico_clave[$row['tecnico']].'</td>';


	//$t8=$t8 + $t_ingreso; 
//	$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
	}
    echo'</tr>';
	echo'<tr bgcolor="#E9F2F8">
	     <td align="right" colspan="7"></td>

	     </tr>';
    echo'</table>';	
	}
	
	exit();
}
top($_SESSION);
if($_POST['cmd']<1){
	echo '<table>';
	echo '<tr><td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td><!--<a href="#" onClick="atcr(\'\',\'_blank\',\'103\',\'0\');"><img src="images/b_print.png" border="0" title="Imprimir"></a>Imprimir--></td></tr>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.firstday().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	/*echo '<tr><td>Tipo de Pago</td><td><select name="tipo_pago" id="tipo_pago"><option value="all" selected>Todos</option>';
	foreach($array_tipo_pago as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';*/
	echo '</table>';
	echo'<br>';
	echo '<div id="Resultados"></div>';
}
?>
<?php
echo '
<Script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","reporte_compra_venta.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&plazausuario="+document.getElementById("plazausuario").value);
 			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					document.getElementById("Resultados").innerHTML = objeto.responseText;
				}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	buscarRegistros();
</Script>';
bottom();
?>