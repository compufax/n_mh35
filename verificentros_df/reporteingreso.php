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
$res = mysql_query("SELECT * FROM engomados WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomados[$row['cve']]=$row['nombre'];
}
$rsUsuario=mysql_query("SELECT * FROM plazas where estatus!='I' ORDER BY numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
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
	$pdf->SetFont('Arial','B',7);
	$fecha=$_POST['fecha_ini'];
	$pdf->GetX();
	$pdf->Cell(20,4,'Fecha',1,0,'C');
	$pdf->Cell(23,4,'Efectivo',1,0,'C');
	$pdf->Cell(23,4,'Pagos Anticipados',1,0,'C');
	$pdf->Cell(20,4,'Devoluciones',1,0,'C');
	$pdf->Cell(23,4,'Cometra',1,0,'C');
	$pdf->Cell(23,4,'Tarjeta de Credito',1,0,'C');
	$pdf->Cell(22,4,'Bancos',1,0,'C');
	$pdf->Cell(23,4,'Creditos',1,0,'C');
	$pdf->Cell(25,4,'Total de Venta',1,0,'C');
	$pdf->Cell(32,4,'Recuperacion de Creditos',1,0,'C');
	$pdf->Cell(28,4,'Total de Ingresos',1,0,'C');
	$pdf->Ln();
	$t0=0; $t1=0; $t2=0; $t3=0; $t4=0; $t5=0; $t6=0; $t7=0; $t8=0; 
	while($fecha<=$_POST['fecha_fin']){
	//rowb();
	$sel="SELECT sum(monto_verificacion) as efectivo from cobro_engomado where estatus='A'and tipo_venta in('0','3') and tipo_pago='1' and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs=mysql_query($sel) or die(mysql_error());
	$row=mysql_fetch_array($rs) or die(mysql_error());

	$sel1="SELECT sum(monto) as p_anticipado from pagos_caja where estatus='A' and tipo_pago='6' and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs1=mysql_query($sel1) or die(mysql_error());
	$row1=mysql_fetch_array($rs1) or die(mysql_error());
	
	$sel2="SELECT sum(devolucion) as m_devoluvion from devolucion_certificado where estatus='A' and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs2=mysql_query($sel2) or die(mysql_error());
	$row2=mysql_fetch_array($rs2) or die(mysql_error());
	
	$sel3="SELECT sum(monto_verificacion) as t_credito from cobro_engomado where estatus='A' and tipo_pago='5' and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs3=mysql_query($sel3) or die(mysql_error());
	$row3=mysql_fetch_array($rs3) or die(mysql_error());
	
	$sel5="SELECT sum(monto) as credito from cobro_engomado where estatus='A'and tipo_venta='0' and tipo_pago in ('2') and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs5=mysql_query($sel5) or die(mysql_error());
	$row5=mysql_fetch_array($rs5) or die(mysql_error());
	
	$sel7="SELECT sum(monto) as r_credito from pagos_caja where estatus='A' and tipo_pago in('2') and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs7=mysql_query($sel7) or die(mysql_error());
	$row7=mysql_fetch_array($rs7) or die(mysql_error());
	//$row=0;
	//$row5_credito=$row5['credito'] + $row5_1['credito'];	
//	$sel7="SELECT sum(monto) as bono from bonos where estatus='A'and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
//	$rs7=mysql_query($sel7) or die(mysql_error());
//	$row7=mysql_fetch_array($rs7) or die(mysql_error());
	
	$cometra=$row['efectivo']+$row1['p_anticipado'];
	$bancos=$cometra+$row3['t_credito'];
	$t_venta=$bancos+$row5['credito'];
	$t_ingreso=$bancos+$row7['r_credito'];
	$pdf->Cell(20,4,''.$fecha,1,0,'C');
	$pdf->Cell(23,4,''.number_format($row['efectivo'],2),1,0,'R');
	$pdf->Cell(23,4,''.number_format($row1['p_anticipado'],2),1,0,'R');
	$pdf->Cell(20,4,''.number_format($row['m_devoluvion'],2),1,0,'R');
	$pdf->Cell(23,4,''.number_format($cometra,2),1,0,'R');
	$pdf->Cell(23,4,''.$row3['t_credito'],1,0,'R');
	$pdf->Cell(22,4,''.number_format($bancos,2),1,0,'R');
	$pdf->Cell(23,4,''.number_format($row5['credito'],2),1,0,'R');
	$pdf->Cell(25,4,''.number_format($t_venta,2),1,0,'R');
//	echo'<td>'.number_format($row7['bono'],2).'</td>';$pdf->Cell(25,4,'',1,0,'R');
	$pdf->Cell(32,4,''.number_format($row7['r_credito'],2),1,0,'R');
	$pdf->Cell(28,4,''.number_format($t_ingreso,2),1,0,'R');
	$t0=$t0 + $row['efectivo']; $t1=$t1 + $row1['p_anticipado']; $t2=$t2 + $cometra; $td=$td + $row2['m_devoluvion']; $t3=$t3 + $row3['t_credito'];
	$t4=$t4 + $bancos; $t5=$t5 + $row5['credito']; $t6=$t6 + $t_venta; $t8=$t8+ $t_ingreso; $t7=$t7 + $row7['r_credito'];
	$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
	$pdf->Ln();
	}
	$pdf->Ln();
	$pdf->Cell(20,4,'Total: ',1,0,'R');
	$pdf->Cell(23,4,''.number_format($t0,2),1,0,'R');
	$pdf->Cell(23,4,''.number_format($t1,2),1,0,'R');
	$pdf->Cell(20,4,''.number_format($td,2),1,0,'R');
	$pdf->Cell(23,4,''.number_format($t2,2),1,0,'R');
	$pdf->Cell(23,4,''.number_format($t3,2),1,0,'R');
	$pdf->Cell(22,4,''.number_format($t4,2),1,0,'R');
	$pdf->Cell(23,4,''.number_format($t5,2),1,0,'R');
	$pdf->Cell(25,4,''.number_format($t6,2),1,0,'R');
	$pdf->Cell(32,4,''.number_format($t7,2),1,0,'R');
	$pdf->Cell(28,4,''.number_format($t8,2),1,0,'R');	
	$pdf->Output();
	exit();
}
if($_POST['cmd']==101){
require_once('../dompdf/dompdf_config.inc.php');
		$html='<html><head>
      <style type="text/css">
		 @page{ margin: 0px 0.5in 1px 0.5in;}
		</style>
		 </head><body>
 		 <h1 align="center">Plaza: '.$array_plazas[$_POST['plazausuario']].'</h2>
		 <table width="100%" height="100%">
		 <tr>
		 <td align="left">Reporte de: '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</td>
		 <td align="right">Fecha: '.fechaLocal().'-'.horaLocal().'</td>
		 </tr>
		 </table>
 		 <h3 align="center" border="0">Gran Total de Venta: '.number_format($_POST['t_ventas'],2).'</h3>
		 <table width="100%" border="1">
		 <tr>
		 <th>Venta por Tipo de Certificado</th><th>Cantidad de Certificados</th><th>Total de Venta</th>
         </tr>';
 foreach ($array_engomados as $k=>$v){
     if($k<6){
    $sel="SELECT tipo_pago,SUM(monto) as m_veri,COUNT(cve) as re FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND engomado='".$k."' AND tipo_venta=0 AND tipo_pago=1 AND estatus!='C' ";
 //   $sel="SELECT sum(monto) as m_veri from cobro_engomado where estatus='A' and engomado='".$k."' and fecha BETWEEN '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
	$rs=mysql_query($sel) or die(mysql_error());
	$row=mysql_fetch_array($rs) or die(mysql_error());
	$html.='<tr><td>'.$v.'</td><td>'.$row['re'].'</td><td>'.number_format($row['m_veri'],2).'</td></tr>';
	$t1=$t1 + $row['re'];
	$t2=$t2 + $row['m_veri'];
     }
		}
		$html.='<tr><td align="right">Total</td><td>'.$t1.'</td><td>'.number_format($t2,2).'</td><td width="100">'.number_format($t2,2).'</td></tr>';
       $html.='</table></br></br>
	            <h3>&nbsp;</h3>';
	$html.='<table width="100%" border="1">
		 <tr>
		 <th>Tipo de Venta</th><th>Total de Venta</th>
         </tr>';
		 	$se1="SELECT sum(monto) as p_anticipado from pagos_caja where estatus='A' and tipo_pago='6' and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
			$r1=mysql_query($se1) or die(mysql_error());
			$ro1=mysql_fetch_array($r1) or die(mysql_error());
			$se2="SELECT sum(monto) as t_credito from cobro_engomado where estatus='A' and tipo_venta in('0','3') and tipo_pago='5' and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
			$r2=mysql_query($se2) or die(mysql_error());
			$ro2=mysql_fetch_array($r2) or die(mysql_error());
			$se3="SELECT sum(monto) as credito from cobro_engomado where estatus='A' and tipo_venta='0' and tipo_pago in('2') and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
			$r3=mysql_query($se3) or die(mysql_error());
			$ro3=mysql_fetch_array($r3) or die(mysql_error());
			$se4="SELECT sum(devolucion) as m_devoluvion from devolucion_certificado where estatus='A' and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
			$r4=mysql_query($se4) or die(mysql_error());
			$ro4=mysql_fetch_array($r4) or die(mysql_error());
			$se5="SELECT sum(monto) as r_credito from pagos_caja where estatus='A' and tipo_pago in('2') and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
			$r5=mysql_query($se5) or die(mysql_error());
			$ro5=mysql_fetch_array($r5) or die(mysql_error());
		$tre=0;
//	 foreach ($array_engomados as $k=>$v){
  //   if($k==26){
    $sel="SELECT tipo_pago,SUM(monto) as m_veri,COUNT(cve) as re FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND tipo_venta=3 AND tipo_pago=1 AND estatus!='C' ";
//	    $sel="SELECT tipo_pago,SUM(monto) as m_veri,COUNT(cve) as re FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND engomado='".$k."' AND tipo_venta=3 AND tipo_pago=1 AND estatus!='C' ";
 //   $sel="SELECT sum(monto) as m_veri from cobro_engomado where estatus='A' and engomado='".$k."' and fecha BETWEEN '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
	$rs=mysql_query($sel) or die(mysql_error());
	$row=mysql_fetch_array($rs) or die(mysql_error());
	$html.='<tr><td>Reposicion</td><td>'.number_format($row['m_veri'],2).'</td></tr>';
     $tre=$row['m_veri'];
     //}
	//	}
	$html.='<tr><td>Pagos Anticipados</td><td>'.number_format($ro1['p_anticipado'],2).'</td></tr>';
	$html.='<tr><td>Tarjeta de Credito</td><td>'.number_format($ro2['t_credito'],2).'</td></tr>';
//	$html.='<tr><td>Credito</td><td>'.number_format($ro3['credito'],2).'</td></tr>';
	$html.='<tr><td>Recuperacion de Creditos</td><td>'.number_format($ro5['r_credito'],2).'</td></tr>';
//	$html.='<tr><td>Devolucion de Dinero</td><td>'.number_format($ro4['m_devoluvion'],2).'</td></tr>';
	$t=$ro1['p_anticipado'] + $ro2['t_credito'] + $ro5['r_credito'] + $tre;
	$html.='<tr><td align="right">Total</td><td>'.number_format($t,2).'</td><td width="100">'.number_format($t,2).'</td></tr>';
	$html.='</table></br>';
	$html.='<h3 align="center" border="0">Devoluciones</h3>
	     <table width="100%" border="1">
		 <tr>
		 <th>Tipo de Venta</th><th>Total de Venta</th>
         </tr>';
    $html.='<tr><td>Devolucion</td><td>'.number_format($ro4['m_devoluvion'],2).'</td></tr>';
	$td=$ro4['m_devoluvion'];
	$html.='<tr><td align="right">Total</td><td>'.number_format($td,2).'</td><td width="100">'.number_format($td,2).'</td></tr>
	     </table>';
	$html.='<h3 align="center" border="0">Ventas por Credito</h3>
	        <table width="100%" border="1">
			<tr>
		    <th>Tipo de Venta</th><th>Total de Venta</th>
            </tr>';
	$html.='<tr><td>Credito</td><td>'.number_format($ro3['credito'],2).'</td></tr>';
	$tc=$ro3['credito'];
	$html.='<tr><td align="right">Total</td><td>'.number_format($tc,2).'</td><td width="100">'.number_format($tc,2).'</td></tr>
	        </table>
		 <h3 align="right" border="0">Gran Total de Venta '.number_format($_POST['t_ventas'],2).'</h3>
		 </body></html>';
	$mipdf= new DOMPDF();
//	$mipdf->margin: "0";
	//$mipdf->set_paper("A4", "portrait");
//	$mipdf->set_paper("A4", "portrait");
    
//    $mipdf->set_margin("Legal", "landscape");
	$mipdf->set_paper("Legal", "landscape");
	$mipdf->load_html($html);
	$mipdf->render();
	$mipdf ->stream();
exit();
}
if($_POST['ajax']==4){
echo'<td><h2 align="center" border="0">Gran Total de Venta '.number_format($_POST['t_ventas'],2).'</h2></td>';
exit();
}
/*
if($_POST['ajax']==-1){
		echo'<table width="100%" border="1">
		 <tr><td id="t_vent" colspan="3" border="0"><h2 align="center" border="0">Gran Total de Venta</h2></td></tr>
		 <tr bgcolor="#E9F2F8">
		 <th>Venta por Tipo de Certificado</th><th>Cantidad de Certificados</th><th>Total de Venta</th>
         </tr>';
 foreach ($array_engomados as $k=>$v){
     if($k<6){
    $sel="SELECT tipo_pago,SUM(monto) as m_veri,COUNT(cve) as re FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND engomado='".$k."' AND tipo_venta=0 AND tipo_pago=1 AND estatus!='C' ";
 //   $sel="SELECT sum(monto) as m_veri from cobro_engomado where estatus='A' and engomado='".$k."' and fecha BETWEEN '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
	$rs=mysql_query($sel) or die(mysql_error());
	$row=mysql_fetch_array($rs) or die(mysql_error());
	echo '<tr><td>'.$v.'</td><td>'.$row['re'].'</td><td>'.number_format($row['m_veri'],2).'</td></tr>';
	$t1=$t1 + $row['re'];
	$t2=$t2 + $row['m_veri'];
     }
		}
		echo '<tr><td align="right">Total</td><td>'.$t1.'</td><td>'.number_format($t2,2).'</td></tr>';
       echo'</table></br></br>';
	echo'<table width="100%" border="1">
		 <tr bgcolor="#E9F2F8">
		 <th>Tipo de Venta</th><th>Total de Venta</th>
         </tr>';
		 	$se1="SELECT sum(monto) as p_anticipado from pagos_caja where estatus='A' and tipo_pago='6' and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
			$r1=mysql_query($se1) or die(mysql_error());
			$ro1=mysql_fetch_array($r1) or die(mysql_error());
			$se2="SELECT sum(monto_verificacion) as t_credito from cobro_engomado where estatus='A' and tipo_pago='5' and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
			$r2=mysql_query($se2) or die(mysql_error());
			$ro2=mysql_fetch_array($r2) or die(mysql_error());
			$se3="SELECT sum(monto) as credito from cobro_engomado where estatus='A' and tipo_venta='0' and tipo_pago in('2') and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
			$r3=mysql_query($se3) or die(mysql_error());
			$ro3=mysql_fetch_array($r3) or die(mysql_error());
			$se4="SELECT sum(devolucion) as m_devoluvion from devolucion_certificado where estatus='A' and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
			$r4=mysql_query($se4) or die(mysql_error());
			$ro4=mysql_fetch_array($r4) or die(mysql_error());
			$se5="SELECT sum(monto) as r_credito from pagos_caja where estatus='A' and tipo_pago in('2') and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
			$r5=mysql_query($se5) or die(mysql_error());
			$ro5=mysql_fetch_array($r5) or die(mysql_error());
	$tre=0;
	 foreach ($array_engomados as $k=>$v){
     if($k==26){
    $sel="SELECT tipo_pago,SUM(monto) as m_veri,COUNT(cve) as re FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND engomado='".$k."' AND tipo_venta=3 AND tipo_pago=1 AND estatus!='C' ";
 //   $sel="SELECT sum(monto) as m_veri from cobro_engomado where estatus='A' and engomado='".$k."' and fecha BETWEEN '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
	$rs=mysql_query($sel) or die(mysql_error());
	$row=mysql_fetch_array($rs) or die(mysql_error());
	echo '<tr><td>'.$v.'</td><td>'.number_format($row['m_veri'],2).'</td></tr>';
     $tre=$row['m_veri'];
     }
		}
	echo '<tr><td>Pagos Anticipados</td><td>'.number_format($ro1['p_anticipado'],2).'</td></tr>';
	echo '<tr><td>Tarjeta de Credito</td><td>'.number_format($ro2['t_credito'],2).'</td></tr>';
//	echo '<tr><td>Credito</td><td>'.number_format($ro3['credito'],2).'</td></tr>';
	echo '<tr><td>Recuperacion de Creditos</td><td>'.number_format($ro5['r_credito'],2).'</td></tr>';
	//echo '<tr><td>Devolucion de Dinero</td><td>'.number_format($ro4['m_devoluvion'],2).'</td></tr>';
	$t=$ro1['p_anticipado'] + $ro2['t_credito'] + $ro5['r_credito'] + $tre;
	echo '<tr><td align="right">Total</td><td>'.number_format($t,2).'</td></tr>';
	echo'</table></br></br>';
	echo'<table width="100%" border="1">
	     <tr><h2 align="center" border="0">Devoluciones</h2></tr>
		 <tr bgcolor="#E9F2F8">
		 <th>Tipo de Venta</th><th>Total de Venta</th>
         </tr>';
    echo '<tr><td>Devolucion</td><td>'.number_format($ro4['m_devoluvion'],2).'</td></tr>';
	$td=$ro4['m_devoluvion'];
	echo'
	     <tr><td align="right">Total</td><td>'.number_format($td,2).'</td></tr>
	     </table>';
	echo'<table width="100%" border="1">
	     <tr><h2 align="center" border="0">Ventas por Credito</h2></tr>
		 <tr bgcolor="#E9F2F8">
		 <th>Tipo de Venta</th><th>Total de Venta</th>
         </tr>';
	echo'<tr>';
	echo '<tr><td>Credito</td><td>'.number_format($ro3['credito'],2).'</td></tr>';
	$tc=$ro3['credito'];
	$t_ventas=$t2 + $t - $td + $tc;
	echo'</tr>
	     <tr><td align="right">Total</td><td>'.number_format($tc,2).'</td><input type="hidden" name="t_ventas" id="t_ventas" value="'.$t_ventas.'"></tr>
	     </table>';
exit();
}

if($_POST['ajax']==-1){
	$fecha=$_POST['fecha_ini'];
	echo'<table width="100%" border="0">
	     <tr bgcolor="#E9F2F8">
		 <th>Fecha</th><th>Efectivo</th><th>Pagos Anticipados</th><th>Devoluciones</th><th>Cometra</th><th>Tarjeta de Credito</th><th>Bancos</th><th>Creditos</th>
		 <th>Total de Venta</th><!--<th>Bonos</th>--><th>Recuperacion de Creditos</th><th>Total de Ingresos</th>
		 </tr>';
	while($fecha<=$_POST['fecha_fin']){
	rowb();
	$sel="SELECT sum(monto_verificacion) as efectivo from cobro_engomado where estatus='A' and tipo_venta in('0','3') and tipo_pago='1' and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs=mysql_query($sel) or die(mysql_error());
	$row=mysql_fetch_array($rs) or die(mysql_error());

	$sel1="SELECT sum(monto) as p_anticipado from pagos_caja where estatus='A' and tipo_pago='6' and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs1=mysql_query($sel1) or die(mysql_error());
	$row1=mysql_fetch_array($rs1) or die(mysql_error());
	
	$sel2="SELECT sum(devolucion) as m_devoluvion from devolucion_certificado where estatus='A' and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs2=mysql_query($sel2) or die(mysql_error());
	$row2=mysql_fetch_array($rs2) or die(mysql_error());	
	
	$sel3="SELECT sum(monto_verificacion) as t_credito from cobro_engomado where estatus='A' and tipo_pago='5' and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs3=mysql_query($sel3) or die(mysql_error());
	$row3=mysql_fetch_array($rs3) or die(mysql_error());
	
	$sel5="SELECT sum(monto) as credito from cobro_engomado where estatus='A' and tipo_venta='0' and tipo_pago in('2') and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs5=mysql_query($sel5) or die(mysql_error());
	$row5=mysql_fetch_array($rs5) or die(mysql_error());
	
	$sel7="SELECT sum(monto) as r_credito from pagos_caja where estatus='A' and tipo_pago in('2') and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
	$rs7=mysql_query($sel7) or die(mysql_error());
	$row7=mysql_fetch_array($rs7) or die(mysql_error());
//	$row5_credito=0;
	//$row5_credito=$row5['credito'] + $row5_1['credito'];
	
//	$sel7="SELECT sum(monto) as bono from bonos where estatus='A'and fecha='".$fecha."' and plaza='".$_POST['plazausuario']."'";
//	$rs7=mysql_query($sel7) or die(mysql_error());
//	$row7=mysql_fetch_array($rs7) or die(mysql_error());
	
	$cometra=$row['efectivo']+$row1['p_anticipado']-$row['m_devoluvion'];
	$bancos=$cometra+$row3['t_credito'];
	$t_venta=$bancos+$row5['credito'];
	$t_ingreso=$bancos+$row7['r_credito'];
	echo'<td align="center">'.$fecha.'</td>';
	echo'<td align="right">'.number_format($row['efectivo'],2).'</td>';
	echo'<td align="right">'.number_format($row1['p_anticipado'],2).'</td>';
	echo'<td align="right">'.number_format($row2['m_devoluvion'],2).'</td>';
	echo'<td align="right">'.number_format($cometra,2).'</td>';
	echo'<td align="right">'.$row3['t_credito'].'</td>';
	echo'<td align="right">'.number_format($bancos,2).'</td>';
	echo'<td align="right">'.number_format($row5['credito'],2).'</td>';
	echo'<td align="right">'.number_format($t_venta,2).'</td>';
//	echo'<td>'.number_format($row7['bono'],2).'</td>';
	echo'<td align="right">'.number_format($row7['r_credito'],2).'</td>';
	echo'<td align="right">'.number_format($t_ingreso,2).'</td>';
	$t0=$t0 + $row['efectivo']; $t1=$t1 + $row1['p_anticipado']; $t2=$t2 + $cometra; $td=$td + $row2['m_devoluvion']; $t3=$t3 + $row3['t_credito'];
	$t4=$t4 + $bancos; $t5=$t5 + $row5['credito']; $t6=$t6 + $t_venta; $t8=$t8+ $t_ingreso; $t7=$t7 + $row7['r_credito'];
	$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
	}
    echo'</tr>';
	echo'<tr bgcolor="#E9F2F8">
	     <td align="right"></td><td align="right">'.number_format($t0,2).'</td><td align="right">'.number_format($t1,2).'</td><td align="right">'.number_format($td,2).'</td><td align="right">'.number_format($t2,2).'</td><td align="right">'.number_format($t3,2).'</td>
		 <td align="right">'.number_format($t4,2).'</td><td align="right">'.number_format($t5,2).'</td><td align="right">'.number_format($t6,2).'</td><td align="right">'.number_format($t7,2).'</td>
		 <td align="right">'.number_format($t8,2).'</td>
	     </tr>';
    echo'</table>';	
	
	exit();
}
*/
top($_SESSION);
if($_POST['cmd']<1){
	echo '<table>';
	echo '<tr><td><input type="hidden" name="plazausuario" id="plazausuario" value="'.$_POST['plazausuario'].'"><a href="#" onclick="atcr(\'reporteingreso.php\',\'\',\'0\',\'0\');"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td>
	<td><a href="#" onClick="atcr(\'\',\'_blank\',\'101\',\'0\');"><img src="images/b_print.png" border="0" title="Imprimir"></a>Imprimir</td></tr>';
	$fech1;
	$fech;
	if($_POST['fecha_ini']!=""){$fech=$_POST['fecha_ini'];}else{$fech=firstday();}
	if($_POST['fecha_fin']!=""){$fech1=$_POST['fecha_fin'];}else{$fech1=fechaLocal();}
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.$fech.'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.$fech1.'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	/*echo '<tr><td>Tipo de Pago</td><td><select name="tipo_pago" id="tipo_pago"><option value="all" selected>Todos</option>';
	foreach($array_tipo_pago as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';*/
	echo '</table>';
	echo'<table width="100%" border="1">
		 <tr><td id="t_vent" colspan="3" border="0" ><h2 align="center" border="0">Gran Total de Venta</h2></td></tr>
		 <tr bgcolor="#E9F2F8">
		 <th>Venta por Tipo de Certificado</th><th>Cantidad de Certificados</th><th>Total de Venta</th>
         </tr>';
 foreach ($array_engomados as $k=>$v){
     if($k<6){
    $sel="SELECT tipo_pago,SUM(monto) as m_veri,COUNT(cve) as re FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND engomado='".$k."' AND tipo_venta=0 AND tipo_pago=1 AND estatus!='C' ";
 //   $sel="SELECT sum(monto) as m_veri from cobro_engomado where estatus='A' and engomado='".$k."' and fecha BETWEEN '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
	$rs=mysql_query($sel) or die(mysql_error());
	$row=mysql_fetch_array($rs) or die(mysql_error());
	echo '<tr><td>'.$v.'</td><td>'.$row['re'].'</td><td>'.number_format($row['m_veri'],2).'</td></tr>';
	$t1=$t1 + $row['re'];
	$t2=$t2 + $row['m_veri'];
     }
		}
		echo '<tr><td align="right">Total</td><td>'.$t1.'</td><td>'.number_format($t2,2).'</td><td width="100">'.number_format($t2,2).'</td></tr>';
       echo'</table></br></br>';
	echo'<table width="100%" border="1">
		 <tr bgcolor="#E9F2F8">
		 <th>Tipo de Venta</th><th>Total de Venta</th>
         </tr>';
		 	$se1="SELECT sum(monto) as p_anticipado from pagos_caja where estatus='A' and tipo_pago='6' and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
			$r1=mysql_query($se1) or die(mysql_error());
			$ro1=mysql_fetch_array($r1) or die(mysql_error());
			$se2="SELECT sum(monto) as t_credito from cobro_engomado where estatus='A' 
			and tipo_venta in('0','3') and tipo_pago='5' 
			and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
			$r2=mysql_query($se2) or die(mysql_error());
			$ro2=mysql_fetch_array($r2) or die(mysql_error());
			$se3="SELECT sum(monto) as credito from cobro_engomado where estatus='A' and tipo_venta='0' and tipo_pago in('2') and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
			$r3=mysql_query($se3) or die(mysql_error());
			$ro3=mysql_fetch_array($r3) or die(mysql_error());
			$se4="SELECT sum(devolucion) as m_devoluvion from devolucion_certificado where estatus='A' and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
			$r4=mysql_query($se4) or die(mysql_error());
			$ro4=mysql_fetch_array($r4) or die(mysql_error());
			$se5="SELECT sum(monto) as r_credito from pagos_caja where estatus='A' and tipo_pago in('2') and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
			$r5=mysql_query($se5) or die(mysql_error());
			$ro5=mysql_fetch_array($r5) or die(mysql_error());
	$tre=0;
	 //foreach ($array_engomados as $k=>$v){
     //if($k==26){
    $sel="SELECT tipo_pago,SUM(monto) as m_veri,COUNT(cve) as re FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND tipo_venta=3 AND tipo_pago=1 AND estatus!='C' ";
//    $sel="SELECT tipo_pago,SUM(monto) as m_veri,COUNT(cve) as re FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND engomado='".$k."' AND tipo_venta=3 AND tipo_pago=1 AND estatus!='C' ";
 //   $sel="SELECT sum(monto) as m_veri from cobro_engomado where estatus='A' and engomado='".$k."' and fecha BETWEEN '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
	$rs=mysql_query($sel) or die(mysql_error());
	$row=mysql_fetch_array($rs) or die(mysql_error());
	echo '<tr><td>Reposicion</td><td>'.number_format($row['m_veri'],2).'</td></tr>';
     $tre=$row['m_veri'];
     //}
	 //}
		
	echo '<tr><td>Pagos Anticipados</td><td>'.number_format($ro1['p_anticipado'],2).'</td></tr>';
	echo '<tr><td>Tarjeta de Credito</td><td>'.number_format($ro2['t_credito'],2).'</td></tr>';
//	echo '<tr><td>Credito</td><td>'.number_format($ro3['credito'],2).'</td></tr>';
	echo '<tr><td>Recuperacion de Creditos</td><td>'.number_format($ro5['r_credito'],2).'</td></tr>';
	//echo '<tr><td>Devolucion de Dinero</td><td>'.number_format($ro4['m_devoluvion'],2).'</td></tr>';
	$t=$ro1['p_anticipado'] + $ro2['t_credito'] + $ro5['r_credito'] + $tre;
	echo '<tr><td align="right">Total</td><td>'.number_format($t,2).'</td><td width="100">'.number_format($t,2).'</td></tr>';
	echo'</table></br></br>';
	echo'<table width="100%" border="1">
	     <tr><h2 align="center" border="0">Devoluciones</h2></tr>
		 <tr bgcolor="#E9F2F8">
		 <th>Tipo de Venta</th><th>Total de Venta</th>
         </tr>';
    echo '<tr><td>Devolucion</td><td>'.number_format($ro4['m_devoluvion'],2).'</td></tr>';
	$td=$ro4['m_devoluvion'];
	echo'
	     <tr><td align="right">Total</td><td>'.number_format($td,2).'</td><td width="100">'.number_format($td,2).'</td></tr>
	     </table>';
	echo'<table width="100%" border="1">
	     <tr><h2 align="center" border="0">Ventas por Credito</h2></tr>
		 <tr bgcolor="#E9F2F8">
		 <th>Tipo de Venta</th><th>Total de Venta</th>
         </tr>';
	echo'<tr>';
	echo '<tr><td>Credito</td><td>'.number_format($ro3['credito'],2).'</td></tr>';
	$tc=$ro3['credito'];
	$t_ventas=$t2 + $t - $td + $tc;
	echo'</tr>
	     <tr><td align="right">Total</td><td>'.number_format($tc,2).'</td><td rowspan="4" border="0" width="100">'.number_format($tc,2).'</td><input type="hidden" name="t_ventas" id="t_ventas" value="'.$t_ventas.'"></tr>
	     </table></br></br>
		 <h2 align="right" border="0">Gran Total de Venta '.number_format($t_ventas,2).'</h2>';
	echo'<br>';
//	echo '<div id="Resultados"></div>';
	echo'<script language="javascript">
	  function traer_total(){
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto.open("POST","reporteingreso.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=4&t_ventas="+document.getElementById("t_ventas").value);
						objeto.onreadystatechange = function(){
							if (objeto.readyState==4){
								document.getElementById("t_vent").innerHTML = objeto.responseText;
							}
						}
					}
				}
			 traer_total();

			 </script>';
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
			objeto.open("POST","reporteingreso.php",true);
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

</Script>';
bottom();
?>