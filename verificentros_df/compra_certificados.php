<?php 
include ("main.php"); 
$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$res=mysql_query("SELECT local, validar_certificado FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];
$ValidarCertificados = $row[1];

$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND entrega=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio_compra'];
}

$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

if($_POST['cmd']==1 && $_POST['cveusuario']!=1)
	$res = mysql_query("SELECT * FROM anios_certificados  ORDER BY nombre DESC LIMIT 2");
else
	$res = mysql_query("SELECT * FROM anios_certificados  ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
	$array_fechainianio[$row['cve']]=$row['fecha_ini'];
	$array_fechafinanio[$row['cve']]=$row['fecha_fin'];
}
$rsUsuario=mysql_query("SELECT * FROM plazas where estatus!='I' ORDER BY numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}


$array_estatus = array('A'=>'Activo','C'=>'Cancelado','E'=>'Confirmado');
$array_estatus2 = array(1=>'Sin Resultado', 2=>'Con resultado');
/*** CONSULTA AJAX  **************************************************/


/*
if($_POST['cmd']==101){
	require_once("numlet.php");
	$res=mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	$texto=chr(27)."@";
	$texto.='|';
	$resPlaza = mysql_query("SELECT nombre FROM plazas WHERE cve='".$row['plaza']."'");
	$rowPlaza = mysql_fetch_array($resPlaza);
	$resPlaza2 = mysql_query("SELECT rfc FROM datosempresas WHERE plaza='".$row['plaza']."'");
	$rowPlaza2 = mysql_fetch_array($resPlaza2);
	$texto.=chr(27).'!'.chr(30)." ".$array_plaza[$row['plaza']]."|".$rowPlaza['nombre'];
	$texto.='| RFC: '.$rowPlaza2['rfc'];
	$texto.='||';
	$texto.=chr(27).'!'.chr(8)." TICKET: ".$row['cve'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." VENTA DE CERTIFICADO";
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." FECHA: ".$row['fecha']."   ".$row['hora'].'|';
	$texto.=chr(27).'!'.chr(40)."PLACA: ".$row['placa'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." T. CERTIFICADO: ".$array_engomado[$row['engomado']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." MODELO: ".$row['modelo'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." TIPO PAGO ".$array_tipo_pago[$row['tipo_pago']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." MONTO: ".$row['monto'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." ".numlet($row['monto']);
	$texto.='|';
	
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$texto.'&logo='.str_replace(' ','',$array_plaza[$row['plaza']]).'&barcode=1'.sprintf("%011s",(intval($row['cve']))).'&copia=1" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",2000);</script>';
	exit();
}*/


if($_POST['cmd']==100){
	//ini_set("session.auto_start", 0);
	
	include('fpdf153/fpdf.php');
	include("numlet.php");	
//	$pdf=new FPDF('P','mm','LETTER');
	$pdf=new FPDF('L','mm','LETTER');
//		$row=mysql_fetch_array($res);
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',24);
	$pdf->Cell(260,10,''.$array_plazas[$_POST['plazausuario']],0,0,'C');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',17);
	$pdf->Cell(260,10,'Compra de Certificados',0,0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','B',8);
	/*if($_POST['anio'] >= 4){
			echo '<table border="1"><tr><th>Tipo de Certificado</th><th>Almacen</th><th>Compras</th><th>Total</th></tr>';
			$pdf->Cell(30,8,'Tipo de Certificado',1,0,'C');
			$pdf->Cell(15,8,'Almacen',1,0,'C');
			$pdf->Cell(15,8,'Compras',1,0,'C');
			$pdf->Cell(20,8,'Total',1,0,'C');
			$pdf->Ln();
			$res = mysql_query("SELECT engomado,SUM(foliofin+1-folioini) as compras FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND anio='".$_POST['anio']."' GROUP BY engomado");
			$array_compras = array();
			while($row = mysql_fetch_array($res)){
				$array_compras[$row[0]] = $row[1];
			}
			foreach($array_engomado as $k=>$v){
				if($k==19 || $k==3){
					if($_POST['anio']==4){
						$res = mysql_query("SELECT existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$_POST['plazausuario']."' AND engomado = '$k' ORDER BY cve DESC LIMIT 1");
						$row = mysql_fetch_array($res);
						$almacen = $row[0];
					}
					else{
						$res = mysql_query("SELECT existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$_POST['plazausuario']."' AND engomado = '$k' ORDER BY cve DESC LIMIT 1");
						$row = mysql_fetch_array($res);
						$almacen = $row[0];
						$res = mysql_query("SELECT SUM(foliofin+1-folioini) FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='$k' AND fecha>='2016-01-01' AND fecha<'".$array_fechainianio[$_POST['anio']]."' AND estatus!='C'");
						$row = mysql_fetch_array($res);
						$almacen += $row[0];
						$res = mysql_query("SELECT COUNT(cve) FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='$k' AND fecha>='2016-01-01' AND fecha<'".$array_fechainianio[$_POST['anio']]."' AND estatus!='C'");
						$row = mysql_fetch_array($res);
						$almacen -= $row[0];
					}
				}
				else{
					$almacen = 0;
				}
				$pdf->Cell(30,5,''.$v,1,0,'C');
				$pdf->Cell(15,5,''.$almacen,1,0,'C');
				$pdf->Cell(15,5,''.$array_compras[$k],1,0,'C');
				$pdf->Cell(20,5,''.($almacen+$array_compras[$k]),1,0,'C');
				$pdf->Ln();
			}
			echo '</table>';
		}*/
	$select= " SELECT * FROM compra_certificados 
		WHERE plaza='".$_POST['plazausuario']."'";
		if($_POST['fecha_ini'] != '') $select.=" AND fecha_compra >= '".$_POST['fecha_ini']."'";
		if($_POST['fecha_fin'] != '')$select .=" AND fecha_compra <= '".$_POST['fecha_fin']."'";
		if ($_POST['usuario']!="") { $select.=" AND usuario='".$_POST['usuario']."' "; }
		if ($_POST['engomado']!="") { $select.=" AND engomado='".$_POST['engomado']."' "; }
		if ($_POST['estatus']!="") { $select.=" AND estatus='".$_POST['estatus']."' "; }
		if ($_POST['anio']!="") { $select.=" AND anio='".$_POST['anio']."' "; }
		$select.=" ORDER BY fecha_compra DESC,cve DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
	$pdf->Ln();
    $pdf->SetFont('Arial','B',13);
	$periodo="-";
	if($_POST['fecha_ini'] == "" and $_POST['fecha_fin'] == ""){$periodo="".$array_anios[$_POST['anio']]."";}
	if($_POST['fecha_ini'] != "" and $_POST['fecha_fin'] != ""){$periodo="".$_POST['fecha_ini']." al ".$_POST['fecha_fin'].""; }
	$pdf->Cell(260,10,'Periodo: '.$periodo,0,0,'L');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',8);
    $pdf->Cell(20,8,'',1,0,'C');
	$pdf->Cell(15,8,'Cheque',1,0,'C');
	$pdf->Cell(28,8,'Folio de Compra',1,0,'C');
	$pdf->Cell(28,8,'Fecha de Compra',1,0,'C');
	$pdf->Cell(30,8,'Tipo de Certificado',1,0,'C');
	$pdf->Cell(25,8,'Folio Inicial',1,0,'C');
	$pdf->Cell(25,8,'Folio Final',1,0,'C');
	$pdf->Cell(15,8,'Cantidad',1,0,'C');
	$pdf->Cell(20,8,'Total',1,0,'C');
	$pdf->Cell(20,8,'Usuario',1,0,'C');
	$pdf->Ln();
	$t=$t2=0;
			while($row=mysql_fetch_array($res)) {
				
				if($row['estatus']=='C'){
					$pdf->Cell(20,5,'Cancelado',0,0,'C');
					$cantidad=0;
				}
				/*elseif($row['estatus']=='E'){
					$cantidad=$row['foliofin']+1-$row['folioini'];
					$pdf->Cell(20,5,'Confirmado',0,0,'C');
				}*/
				else{
					//echo '<a href="#" onClick="atcr(\'cobro_engomado.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					$cantidad=$row['foliofin']+1-$row['folioini'];
					$puede_cancelar = 0;
					$res1=mysql_query("SELECT cve FROM certificados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."'");
					$res2=mysql_query("SELECT cve FROM certificados_cancelados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."'");
					if(mysql_num_rows($res1)==0 && mysql_num_rows($res2)==0) $puede_cancelar = 1;
					if(nivelUsuario()>1 && $puede_cancelar == 1){}
					$pdf->Cell(20,5,'',1,0,'C');
	
				}
				$pdf->Cell(15,5,'',1,0,'C');
				$pdf->Cell(28,5,''.$row['folio'],1,0,'C');
				$pdf->Cell(28,5,''.$row['fecha_compra'],1,0,'C');
                $pdf->Cell(30,5,''.$array_engomado[$row['engomado']],1,0,'C');
				$pdf->Cell(25,5,''.$row['folioini'],1,0,'C');
				$pdf->Cell(25,5,''.$row['foliofin'],1,0,'C');
				$pdf->Cell(15,5,''.$cantidad,1,0,'C');
				$pdf->Cell(20,5,''.number_format($row['costo']*$cantidad,2),1,0,'C');
				$pdf->Cell(20,5,''.$array_usuario[$row['usuario']],1,0,'C');
				
				$t+=$cantidad;
				$t2+=round($row['costo']*$cantidad,2);
				$pdf->Ln();
			}
			    $pdf->Cell(20,5,''.menunavegacion(),0,0,'C');
				$pdf->Cell(15,5,'',0,0,'C');
				$pdf->Cell(28,5,'',0,0,'C');
				$pdf->Cell(28,5,'',0,0,'C');
				$pdf->Cell(30,5,'',0,0,'C');
				$pdf->Cell(25,5,'',0,0,'C');
				$pdf->Cell(25,5,'Total',1,0,'R');
				$pdf->Cell(15,5,''.number_format($t,0),1,0,'C');
				$pdf->Cell(20,5,''.number_format($t2,2),1,0,'C');
				$pdf->Cell(20,5,'',0,0,'C');
	ob_end_clean();		
	$pdf->Output();
	exit();
}
if($_POST['ajax']==3){
	$res = mysql_query("SELECT * FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$_POST['engomado']."' AND anio='".$_POST['anio']."' AND ((folioini BETWEEN '".$_POST['folioini']."' AND '".$_POST['foliofin']."') OR (foliofin BETWEEN '".$_POST['folioini']."' AND '".$_POST['foliofin']."')) AND estatus!='C' ORDER BY cve DESC LIMIT 1");
	if(mysql_num_rows($res)>0){
		echo "1";
	}
	exit();
}
if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM compra_certificados 
		WHERE plaza='".$_POST['plazausuario']."'";
		if($_POST['fecha_ini'] != '') $select.=" AND fecha_compra >= '".$_POST['fecha_ini']."'";
		if($_POST['fecha_fin'] != '')$select .=" AND fecha_compra <= '".$_POST['fecha_fin']."'";
		if ($_POST['usuario']!="") { $select.=" AND usuario='".$_POST['usuario']."' "; }
		if ($_POST['engomado']!="") { $select.=" AND engomado='".$_POST['engomado']."' "; }
		if ($_POST['estatus']!="") { $select.=" AND estatus='".$_POST['estatus']."' "; }
		if($_POST['anio']!='') $select.=" AND anio IN (".$_POST['anio'].")"; 
		$select.=" ORDER BY fecha_compra DESC,cve DESC";
		$res=mysql_query($select) or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Consecutivo</th><th>Folio Compra</th><th>Fecha Compra</th><th>Fecha</th><th>Tipo de Certificado</th><th>Folio Inicial</th><th>Folio Final</th><th>Cantidad</th><!--<th>Total</th>--><th>A&ntilde;o</th><th>Remanente</th><th>Usuario</th>';
			echo '</tr>';
			$t=$t2=$t3=0;
			$nivelUsuario = nivelUsuario();
			while($row=mysql_fetch_array($res)) {
				if($row['estatus']=='C')
					$cantidad = 0;
				else
					$cantidad=$row['foliofin']+1-$row['folioini'];
				$puede_cancelar = 0;
				$diferente=0;
				$res1=mysql_query("SELECT cve FROM certificados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."' AND estatus!='C'");
				$res2=mysql_query("SELECT cve FROM certificados_cancelados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."' AND estatus!='C'");
				if(mysql_num_rows($res1)==0 && mysql_num_rows($res2)==0) $puede_cancelar = 1;
				$entregados = mysql_num_rows($res1) + mysql_num_rows($res2);
				$faltantes = $cantidad-$entregados;
				if($faltantes < 0) $faltantes = 0;
				if($_POST['mostrar'] == 0 || ($_POST['mostrar'] == 1 && $cantidad>$entregados) || ($_POST['mostrar'] == 2 && $cantidad<=$entregados)){
					rowb();
					echo '<td align="center" width="40" nowrap>';
					if($row['estatus']=='C'){
						echo 'Cancelado';
						$cantidad=0;
					}
					/*elseif($row['estatus']=='E'){
						$cantidad=$row['foliofin']+1-$row['folioini'];
						if($_POST['cveusuario'] == 1){
							echo '<a href="#" onClick="atcr(\'compra_certificados.php\',\'\',\'10\','.$row['cve'].')"><img src="images/b_search.png" border="0" title="Imprimir '.$row['cve'].'"></a><br>';
						}
						echo 'Confirmado';
					}*/
					else{
						if($_POST['cveusuario'] == 1){
							echo '<a href="#" onClick="atcr(\'compra_certificados.php\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$row['cve'].'"></a>&nbsp;&nbsp;';
						}
						if($nivelUsuario > 0){
							echo '<a href="#" onClick="atcr(\'compra_certificados.php\',\'\',\'10\','.$row['cve'].')"><img src="images/b_search.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
						}
						/*$cantidad=$row['foliofin']+1-$row['folioini'];
						$puede_cancelar = 0;
						$res1=mysql_query("SELECT cve FROM certificados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."' AND estatus!='C'");
						$res2=mysql_query("SELECT cve FROM certificados_cancelados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."' AND estatus!='C'");
						if(mysql_num_rows($res1)==0 && mysql_num_rows($res2)==0) $puede_cancelar = 1;
						$entregados = mysql_num_rows($res1) + mysql_num_rows($res2);*/
						if(($nivelUsuario>1 && $puede_cancelar == 1) || $_POST['cveusuario']==1)
							echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar la compra\')) atcr(\'compra_certificados.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
						if ($nivelUsuario>2 && $row['estatus']!='C' && $array_fechafinanio[$row['anio']] < date('Y-m-d') && $faltantes > 0 && $row['engomado'] != 3 && $row['engomado']!=19){
							echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar los faltantes\')) atcr(\'compra_certificados.php\',\'\',\'33\','.$row['cve'].')"><img src="images/cerrar.gif" border="0" title="Cancelar Faltantes'.$row['cve'].'"></a>';
						}
					}	
					echo '</td>';
					if($_POST['cveusuario'] != 1)
						echo '<td align="center">'.htmlentities($row['cve']).'</td>';
					elseif($cantidad > $entregados)
						echo '<td align="center"><font color="RED">'.htmlentities($row['cve']).'</font></td>';
					else
						echo '<td align="center"><font color="BLUE">'.htmlentities($row['cve']).'</font></td>';
					echo '<td align="center">'.htmlentities($row['folio']).'</td>';
					echo '<td align="center">'.htmlentities($row['fecha_compra']).'</td>';
					echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
					echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
					echo '<td align="center">'.$row['folioini'].'</td>';
					echo '<td align="center">'.$row['foliofin'].'</td>';
					echo '<td align="center">'.$cantidad.'</td>';
//					echo '<td align="right">'.number_format($row['costo']*$cantidad,2).'</td>';
					echo '<td align="center">'.htmlentities($array_anios[$row['anio']]).'</td>';
					echo '<td align="center">'.$faltantes.'</td>';
					echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
					echo '</tr>';
					$t+=$cantidad;
					$t2+=round($row['costo']*$cantidad,2);
					$t3+=$faltantes;
				}
			}
			echo '	
				<tr>
				<td colspan="8" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t,0).'</td>
				<!--<td align="right" bgcolor="#E9F2F8">'.number_format($t2,2).'</td>-->

				<td bgcolor="#E9F2F8">&nbsp;</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t3,0).'</td>
				<td bgcolor="#E9F2F8">&nbsp;</td>
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
		echo '|*|';
		$anios = explode(",",$_POST['anio']);
		$_POST['anio'] = $anios[0];
		if($_POST['anio'] >= 4){
			echo '<table border="1"><tr><th>Tipo de Certificado</th><!--<th>Almacen</th><th>Compras</th><th>Total</th>';
			if($_POST['cveusuario'] >= 1){
				echo '<th>Consumidos</th>--><th>Existencia</th>';
			}
			echo '</tr>';
			/*$res = mysql_query("SELECT a.engomado,COUNT(b.cve) as compras FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra AND b.tipo!=1 WHERE a.plaza='".$_POST['plazausuario']."' AND ((a.anio='".$_POST['anio']."' AND a.engomado NOT IN (3, 4,19)) OR (a.anio>=4 AND a.engomado IN (3, 4,19))) AND a.estatus!='C' GROUP BY a.engomado") or die(mysql_error());*/
			$res = mysql_query("SELECT a.engomado,COUNT(b.cve) as compras FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra AND b.tipo!=1 WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.estatus!='C' GROUP BY a.engomado") or die(mysql_error());
			$array_compras = array();
			while($row = mysql_fetch_array($res)){
				$array_compras[$row[0]] = $row[1];
			}
			foreach($array_engomado as $k=>$v){
				//if($k==19 || $k==3){
				if($k<0){
					if($_POST['anio']==4){
						$res = mysql_query("SELECT existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$_POST['plazausuario']."' AND engomado = '$k' ORDER BY cve DESC LIMIT 1");
						$row = mysql_fetch_array($res);
						$almacen = $row[0];
					}
					else{
						$res = mysql_query("SELECT existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$_POST['plazausuario']."' AND engomado = '$k' ORDER BY cve DESC LIMIT 1");
						$row = mysql_fetch_array($res);
						$almacen = $row[0];
						$res = mysql_query("SELECT SUM(foliofin+1-folioini) FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='$k' AND anio>=4 AND fecha_compra>='2016-01-01' AND fecha_compra<'".$array_fechainianio[$_POST['anio']]."' AND estatus!='C'");
						$row = mysql_fetch_array($res);
						$almacen += $row[0];
						$res = mysql_query("SELECT COUNT(cve) FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='$k' AND fecha>='2016-01-01' AND anio>=4 AND fecha<'".$array_fechainianio[$_POST['anio']]."' AND estatus!='C'");
						$row = mysql_fetch_array($res);
						$almacen -= $row[0];
						$res = mysql_query("SELECT COUNT(cve) FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND engomado='$k' AND fecha>='2016-01-01' AND anio>=4 AND fecha<'".$array_fechainianio[$_POST['anio']]."' AND estatus!='C'");
						$row = mysql_fetch_array($res);
						$almacen -= $row[0];
					}
					if($_POST['cveusuario'] >= 1){
						$mes = substr($array_fechainianio[$_POST['anio']],5,2);
						if(intval($mes)<=6){
							$fini = substr($array_fechainianio[$_POST['anio']],0,4).'-01-01';
							$ffin = substr($array_fechainianio[$_POST['anio']],0,4).'-06-30';
						}
						else{
							$fini = substr($array_fechainianio[$_POST['anio']],0,4).'-07-01';
							$ffin = substr($array_fechainianio[$_POST['anio']],0,4).'-12-31';
						}
						$res1=mysql_query("SELECT count(a.cve)
						FROM certificados a 
						INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
						INNER JOIN anios_certificados c ON c.cve = a.anio
						WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.anio='".$_POST['anio']."' AND a.engomado='$k' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) BETWEEN '".$fini."' AND '".$ffin."'");
						$row1 = mysql_fetch_array($res1);
						$res2=mysql_query("SELECT count(a.cve)
						FROM certificados_cancelados a 
						INNER JOIN anios_certificados c ON c.cve = a.anio
						WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.anio='".$_POST['anio']."' AND a.engomado='$k' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) BETWEEN '".$fini."' AND '".$ffin."'");
						$row2 = mysql_fetch_array($res2);
					}
				}
				else{
					$almacen = 0;
					if($_POST['cveusuario'] >= 1){
						//if($k==4 || $k==3 || $k==19){
							$res1=mysql_query("SELECT count(a.cve)
							FROM certificados a 
							INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
							INNER JOIN anios_certificados c ON c.cve = a.anio
							WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='$k' AND a.estatus!='C'");
						/*}
						else{
							$res1=mysql_query("SELECT count(a.cve)
							FROM certificados a 
							INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
							INNER JOIN anios_certificados c ON c.cve = a.anio
							WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.anio='".$_POST['anio']."' AND a.engomado='$k' AND a.estatus!='C'");
						}*/
						$row1 = mysql_fetch_array($res1);
						//if($k==4 || $k==3 || $k==19){
							$res2=mysql_query("SELECT count(a.cve)
							FROM certificados_cancelados a 
							INNER JOIN anios_certificados c ON c.cve = a.anio
							WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='$k' AND a.estatus!='C'");
						/*}
						else{
							$res2=mysql_query("SELECT count(a.cve)
							FROM certificados_cancelados a 
							INNER JOIN anios_certificados c ON c.cve = a.anio
							WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.anio='".$_POST['anio']."' AND a.engomado='$k' AND a.estatus!='C'");
						}*/
						$row2 = mysql_fetch_array($res2);
					}
				}
				
				echo '<tr><td>'.$v.'</td><!--<td align="right">'.$almacen.'</td><td align="right">'.$array_compras[$k].'</td><td align="right">'.($almacen+$array_compras[$k]).'</td>';
				if($_POST['cveusuario'] >= 1){
					echo '<td align="right">'.($row1[0]+$row2[0]).'</td>-->';
					//echo '<td align="right">'.(($almacen+$array_compras[$k])-($row1['0']+$row2[0])).'</td>';
					echo '<td align="right"><a href="#" onClick="atcr(\'compra_certificados.php\',\'\',20,'.$k.')">'.(($almacen+$array_compras[$k])-($row1['0']+$row2[0])).'</a></td>';
				}
				echo '</tr>';
			}
			echo '</table>';
		}
		exit();	
}	

top($_SESSION);
if($_POST['cmd']>0){
	echo '<input type="hidden" name="fecha_ini" value="'.$_POST['fecha_ini'].'">';
	echo '<input type="hidden" name="fecha_fin" value="'.$_POST['fecha_fin'].'">';
	echo '<input type="hidden" name="aniof" value="'.$_POST['anio'].'">';
	echo '<input type="hidden" name="engomadof" value="'.$_POST['engomado'].'">';
	echo '<input type="hidden" name="mostrar" value="'.$_POST['mostrar'].'">';
	echo '<input type="hidden" name="estatus" value="'.$_POST['estatus'].'">';
	echo '<input type="hidden" name="archivoname" value="compras">';
}
if($_POST['cmd']==20){

	echo '<table>';
		echo '
			<tr>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'compra_certificados.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
	$array_engomado = array();
	$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND plazas like '%|".$_POST['plazausuario']."|%' AND entrega=1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_engomado[$row['cve']]=$row['nombre'];
		$array_engomadoprecio[$row['cve']]=$row['precio'];
	}

	$res = mysql_query("SELECT * FROM anios_certificados WHERE venta=1  ORDER BY nombre DESC LIMIT 2");
	while($row=mysql_fetch_array($res)){
		$array_anios[$row['cve']]=$row['nombre'];
	}

	$res=mysql_query("SELECT local, validar_certificado FROM plazas WHERE cve='".$_POST['plazausuario']."'");
	$row=mysql_fetch_array($res);
	$PlazaLocal=$row[0];
	$ValidarCertificados=$row[1];

	$res = mysql_query("SELECT * FROM usuarios");
	while($row=mysql_fetch_array($res)){
		$array_usuario[$row['cve']]=$row['usuario'];
	}

	$res = mysql_query("SELECT * FROM tipo_combustible ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_tipo_combustible[$row['cve']]=$row['nombre'];
	}

	$res = mysql_query("SELECT * FROM tecnicos WHERE plaza='".$_POST['plazausuario']."'");
	while($row=mysql_fetch_array($res)){
		$array_personal[$row['cve']]=$row['nombre'];
	}

	$res = mysql_query("SELECT * FROM cat_lineas WHERE plaza='".$_POST['plazausuario']."'");
	while($row=mysql_fetch_array($res)){
		$array_lineas[$row['cve']]=$row['nombre'];
	}

	$array_estatus = array('A'=>'Activo','C'=>'Cancelado');

	$array_tipo_venta[0] = 'Con Importe';
	$res = mysql_query("SELECT * FROM tipo_venta ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_tipo_venta[$row['cve']] = $row['nombre'];
	}
	$res = mysql_query("SELECT * FROM motivos_cancelacion_certificados");
	while($row=mysql_fetch_array($res)){
		$array_motivos[$row['cve']]=$row['nombre'];
	}
	$array_certificados=array();
	if($_POST['reg']==19 || $_POST['reg']==3){
		$mes = substr($array_fechainianio[$_POST['anio']],5,2);
		if(intval($mes)<=6){
			$fini = substr($array_fechainianio[$_POST['anio']],0,4).'-01-01';
			$ffin = substr($array_fechainianio[$_POST['anio']],0,4).'-06-30';
		}
		else{
			$fini = substr($array_fechainianio[$_POST['anio']],0,4).'-07-01';
			$ffin = substr($array_fechainianio[$_POST['anio']],0,4).'-12-01';
		}
		$res1=mysql_query("SELECT a.*, b.tipo_venta, b.engomado as engomadoticket, b.tipo_combustible, b.factura
		FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
		INNER JOIN anios_certificados c ON c.cve = a.anio
		WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha>='2016-01-01' AND a.engomado='".$_POST['reg']."' AND a.estatus!='C' AND a.anio>=4 ORDER BY a.cve DESC");
		$res2=mysql_query("SELECT a.*
		FROM certificados_cancelados a 
		INNER JOIN anios_certificados c ON c.cve = a.anio
		WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha>='2016-01-01' AND a.engomado='".$_POST['reg']."' AND a.estatus!='C' AND a.anio>=4 ORDER BY a.cve DESC");
	}
	else{
		$almacen = 0;
		if($_POST['cveusuario'] >= 1){
			$res1=mysql_query("SELECT a.*, b.tipo_venta, b.engomado as engomadoticket, b.tipo_combustible, b.factura
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha>='2016-01-01' AND a.anio>=4 AND a.anio='".$_POST['anio']."' AND a.engomado='".$_POST['reg']."' AND a.estatus!='C' ORDER BY a.cve DESC");
			$res2=mysql_query("SELECT a.*
			FROM certificados_cancelados a 
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha>='2016-01-01' AND a.anio>=4 AND a.anio='".$_POST['anio']."' AND a.engomado='".$_POST['reg']."' AND a.estatus!='C' ORDER BY a.cve DESC");
		}
	}
	/*echo '<h2>Entregas</h2>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Folio</th><th>Fecha</th><th>Ticket</th><th>Tipo de Venta</th><th>Placa</th><th>Tipo de Combustible</th>
	<th>Tipo de Certificado</th><th>Tecnico</th><th>Holograma</th><th>Entregado</th><th>Usuario</th>';
	echo '</tr>';*/
	$t=0;
	while($row=mysql_fetch_array($res1)) {
		$array_certificados[]=intval($row['certificado']);
		/*rowb();
		if($row['engomadoticket']!=$row['engomado'])
			echo '<td align="center"><font color="RED">'.htmlentities($row['cve']).'</font></td>';
		else
			echo '<td align="center">'.htmlentities($row['cve']).'</td>';
		echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
		if($_POST['ticket']==$row['ticket'])
			echo '<td align="center"><font color="BLUE">'.htmlentities($row['ticket']).'</font></td>';
		else
			echo '<td align="center">'.htmlentities($row['ticket']).'</td>';
		echo '<td align="center">'.htmlentities($array_tipo_venta[$row['tipo_venta']]).'</td>';
		echo '<td align="center">'.htmlentities($row['placa']).'</td>';
		echo '<td align="center">'.htmlentities($array_tipo_combustible[$row['tipo_combustible']]).'</td>';
		echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
		echo '<td align="left">'.htmlentities(utf8_encode($array_personal[$row['tecnico']])).'</td>';
		echo '<td align="center">'.htmlentities($row['certificado']).'</td>';
		echo '<td align="center">'.htmlentities($array_nosi[$row['entregado']]).'</td>';
		echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
		echo '</tr>';*/
		$t++;
	}
	/*echo '	
		<tr>
		<td colspan="12" bgcolor="#E9F2F8">'.$t.' Registro(s)</td>
		</tr>
	</table>';
	echo '<h2>Cancelaciones</h2>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Folio</th><th>Fecha</th><th>Motivo</th><th>Tipo de Certificado</th><th>A&ntilde;o</th><th>Holograma</th><th>Usuario</th>';
	echo '</tr>';*/
	$t=0;
	while($row=mysql_fetch_array($res2)) {
		$array_certificados[]=intval($row['certificado']);
		/*rowb();
		echo '<td align="center">'.htmlentities($row['cve']).'</td>';
		echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
		echo '<td align="left">'.htmlentities(utf8_encode($array_motivos[$row['motivo']])).'</td>';
		echo '<td align="left">'.htmlentities(utf8_encode($array_engomado[$row['engomado']])).'</td>';
		echo '<td align="left">'.htmlentities(utf8_encode($array_anios[$row['anio']])).'</td>';
		echo '<td align="center">'.htmlentities($row['certificado']).'</td>';
		echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
		echo '</tr>';*/
		$t++;
	}
	/*echo '	
		<tr>
		<td colspan="10" bgcolor="#E9F2F8">'.$t.' Registro(s)</td>
		</tr>
	</table>';*/
	echo '<table><tr><th>Certificados No entregados en el periodo del semestre '.$array_anios[$_POST['anio']].'</th><th>Fecha Compra</th><th>Tipo</th><th>Folio</th><th>Fecha</th></tr>';
	if($_POST['reg']==19 || $_POST['reg']==3)
		$res = mysql_query("SELECT a.fecha_compra,b.folio FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra AND b.tipo=0 WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>='4' AND a.engomado='".$_POST['reg']."' AND a.estatus!='C' AND b.tipo=0");
	else
		$res = mysql_query("SELECT a.fecha_compra,b.folio FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra AND b.tipo=0 WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio='".$_POST['anio']."' AND a.engomado='".$_POST['reg']."' AND a.estatus!='C' AND b.tipo=0");
	$array_compras = array();
	//echo mysql_num_rows($res).'<br>';
	$t=0;
	while($row = mysql_fetch_array($res)){
		$array_compras[]=$row['folio'];
		if(!in_array($row['folio'], $array_certificados)){
			rowb();
			echo '<td>'.$row['folio'].'</td>';
			echo '<td>'.$row['fecha_compra'].'</td>';
			$res1=mysql_query("SELECT a.cve, a.fecha FROM certificados a  WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha>='2016-01-01' AND CAST(a.certificado AS UNSIGNED)='".$row['folio']."' AND a.estatus!='C'") or die(mysql_error());
			if($row1=mysql_fetch_array($res1)){
				echo '<td>Entrega</td>';
				echo '<td>'.$row1['cve'].'</td>';
				echo '<td>'.$row1['fecha'].'</td>';
				
			}
			else{
				$res1=mysql_query("SELECT cve, fecha FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND a.fecha>='2016-01-01' AND CAST(certificado AS UNSIGNED)='".$row['folio']."' AND estatus!='C'");
				if($row1=mysql_fetch_array($res1)){
					echo '<td>Cancelacion</td>';
					echo '<td>'.$row1['cve'].'</td>';
					echo '<td>'.$row1['fecha'].'</td>';
				}
			}
			echo '</tr>';
			$t++;
		}
	}
	echo '<tr><th colspan="5">'.$t.' Registro(s)</th></tr>';
	echo '</table>';
	echo '<h2>Entregas sin compra</h2>';
	echo '<table>';
	$t=0;
	foreach($array_certificados as $certificado){
		if(!in_array($certificado, $array_compras)){
			rowb();
			echo '<td>'.$certificado.'</td>';
			echo '</tr>';
			$t++;
		}

	}
	echo '<tr><th>'.$t.' Registro(s)</th></tr>';
	echo '</table>';
}

if($_POST['cmd']==13){
	mysql_query("UPDATE plazas SET validar_certificado=0, cambios_validar_certificado = CONCAT(cambios_validar_certificado,'|0,".$_POST['cveusuario'].",".fechaLocal()." ".horaLocal()."') WHERE cve = '".$_POST['plazausuario']."'") or die(mysql_error());
	$ValidarCertificados = 0;
	$_POST['cmd']=0;
}

if($_POST['cmd']==12){
	mysql_query("UPDATE plazas SET validar_certificado=1, cambios_validar_certificado = CONCAT(cambios_validar_certificado,'|1,".$_POST['cveusuario'].",".fechaLocal()." ".horaLocal()."') WHERE cve = '".$_POST['plazausuario']."'");
	$ValidarCertificados = 1;
	$_POST['cmd']=0;
}

if($_POST['cmd']==33){
	$resF = mysql_query("SELECT a.anio, a.engomado, b.folio FROM compra_certificados a INNER JOIN compra_certificados_detalle b on a.plaza = b.plaza AND a.cve = b.cvecompra WHERE a.plaza='".$_POST['plazausuario']."' AND a.cve='{$_POST['reg']}' AND b.tipo=0");
	while($folios = mysql_fetch_assoc($resF)){

		$holograma = $folios['folio'];
		$res = mysql_query("SELECT cve, fecha FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$folios['engomado']."' AND certificado ='".intval($holograma)."' AND estatus!='C'");
		if(mysql_num_rows($res)==0){
			$res = mysql_query("SELECT cve, fecha FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$folios['engomado']."' AND certificado='".intval($holograma)."' AND estatus!='C'");
			if(mysql_num_rows($res)==0){
					$ticket = 0;
					$fechaticket='0000-00-00 00:00:00';
					
					$insert = " INSERT certificados_cancelados
										SET 
										plaza = '".$_POST['plazausuario']."',fecha='".$array_fechafinanio[$folios['anio']]."',hora='".horaLocal()."',
										certificado='".$holograma."',motivo='12',anio='".$folios['anio']."',
										usuario='".$_POST['cveusuario']."',engomado='".$folios['engomado']."',estatus='A',
										placa='',obs='".$_POST['obs']."',tecnico='',
										linea='',ticket='$ticket',fechaticket='$fechaticket',
										cobro_tecnico='0',cant_empleados='0'";
					mysql_query($insert);
					$idcancelacion = mysql_insert_id();
					mysql_query("UPDATE compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra SET b.estatus=1 WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$folios['engomado']."' AND b.folio='".intval($holograma)."' AND a.estatus!='C'");
					
				
			}
			
		}
		
	}

	$_POST['cmd']=0;
}

if($_POST['cmd']==3){
	mysql_query("UPDATE compra_certificados SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	mysql_query("UPDATE compra_certificados_detalle SET estatus=3 WHERE plaza='".$_POST['plazausuario']."' AND cvecompra='".$_POST['reg']."' AND estatus=0");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	
		$res = mysql_query("SELECT * FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$_POST['engomado']."' AND anio='".$_POST['anio']."' AND ((folioini BETWEEN '".$_POST['folioini']."' AND '".$_POST['foliofin']."') OR (foliofin BETWEEN '".$_POST['folioini']."' AND '".$_POST['foliofin']."')) AND estatus!='C' ORDER BY cve DESC LIMIT 1");
		if(mysql_num_rows($res)==0 || $_POST['cveusuario']==1){
			if($_POST['reg']>0){
				$res = mysql_query("SELECT * FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
				$row = mysql_fetch_array($res);
				$cvecompra =$_POST['reg'];
				if($row['folioini']<$_POST['folioini']){
					mysql_query("DELETE FROM compra_certificados_detalle WHERE plaza='".$_POST['plazausuario']."' AND cvecompra='$cvecompra' AND folio<'".$_POST['folioini']."'");
				}
				elseif($row['folioini']>$_POST['folioini']){
					$fin = ($row['folioini'] < $_POST['foliofin']) ? $row['folioini'] : ($_POST['foliofin']+1);
					for($i=$_POST['folioini'];$i<$fin;$i++){
						mysql_query("INSERT compra_certificados_detalle SET plaza='".$_POST['plazausuario']."',cvecompra='$cvecompra',folio='$i',tipo=0");
					}
				}
				if($row['foliofin']>$_POST['foliofin']){
					mysql_query("DELETE FROM compra_certificados_detalle WHERE plaza='".$_POST['plazausuario']."' AND cvecompra='$cvecompra' AND folio>'".$_POST['foliofin']."'");
				}
				elseif($row['foliofin']<$_POST['foliofin']){
					$ini = ($row['foliofin'] > $_POST['folioini']) ? $row['foliofin'] : ($_POST['folioini']-1);
					for($i=($ini+1);$i<=$_POST['foliofin'];$i++){
						mysql_query("INSERT compra_certificados_detalle SET plaza='".$_POST['plazausuario']."',cvecompra='$cvecompra',folio='$i',tipo=0");
					}
				}
				$update = " UPDATE compra_certificados 
								SET 
								folio='".$_POST['folio']."',fecha_compra='".$_POST['fecha_compra']."',costo='".$array_engomadoprecio[$_POST['engomado']]."',
								engomado='".$_POST['engomado']."',folioini='".$_POST['folioini']."',anio='".$_POST['anio']."',
								foliofin='".$_POST['foliofin']."'
							WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'";
				mysql_query($update);
				mysql_query("UPDATE compra_certificados_detalle SET tipo=0,estatus=0 WHERE plaza='".$_POST['plazausuario']."' AND cvecompra='$cvecompra' AND tipo='1' AND estatus='2'"); 
			}
			else{
				$insert = " INSERT compra_certificados 
								SET 
								folio='".$_POST['folio']."',fecha_compra='".$_POST['fecha_compra']."',costo='".$array_engomadoprecio[$_POST['engomado']]."',
								plaza = '".$_POST['plazausuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',
								engomado='".$_POST['engomado']."',folioini='".$_POST['folioini']."',anio='".$_POST['anio']."',
								foliofin='".$_POST['foliofin']."',usuario='".$_POST['cveusuario']."',estatus='A'";
				mysql_query($insert);
				$cvecompra = mysql_insert_id();
				for($i=$_POST['folioini'];$i<=$_POST['foliofin'];$i++){
					mysql_query("INSERT compra_certificados_detalle SET plaza='".$_POST['plazausuario']."',cvecompra='$cvecompra',folio='$i',tipo=0");
				}
			}
			foreach($_POST['faltante'] as $valor){
				if($valor>0){
					mysql_query("UPDATE compra_certificados_detalle SET tipo=1,estatus=2 WHERE plaza='".$_POST['plazausuario']."' AND cvecompra='$cvecompra' AND folio='$valor'");
				}
			}
		}
	$_POST['cmd']=0;
}
/*if($_POST['cmd'] == 10){
	$res = mysql_query("SELECT * FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	echo '<table>';
	echo '
		<tr>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'compra_certificados.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';

	echo '<table>';
	echo '<tr><td class="tableEnc">Compra de Certificados</td></tr>';
	echo '</table>';
	echo '<table style="font-size:15px">';
	echo '<tr><th align="left">Folio Compra</th><td>'.$row['folio'].'</td></tr>';
	echo '<tr><th align="left">Fecha Compra</th><td>'.$row['fecha_compra'].'</td></tr>';
	echo '<tr><th align="left">A&ntilde;o</th><td>'.$array_anios[$row['anio']].'</td></tr>';
	echo '<tr><th align="left">Tipo de Certificado</th><td>'.$array_engomado[$row['engomado']].'</td></tr></table>';
	echo '<br>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Folio</th><th>Tipo Ponchado</th><th>Folio</th><th>Fecha</th><th>Ticket</th><th>Fecha Ticket</th><th>Usuario</th>';
	echo '</tr>';
	$t1=$t2=0;
	$res1 = mysql_query("SELECT * FROM compra_certificados_detalle WHERE plaza='".$_POST['plazausuario']."' AND cvecompra='".$row['cve']."' AND tipo=0");
	while($row1 = mysql_fetch_array($res1)){
		rowb();
		echo '<td>'.$row1['folio'].'</td>';
		$res2 = mysql_query("SELECT a.cve, a.fecha, a.hora, a.usuario, a.ticket, b.fecha as fecha_venta, b.hora as hora_venta FROM certificados a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$row['engomado']."' AND CAST(a.certificado AS UNSIGNED) = '".$row1['folio']."' AND a.estatus != 'C'");
		if($row2 = mysql_fetch_array($res2)){
			echo '<td>Entrega</td>';
			echo '<td>'.$row2['cve'].'</td>';
			echo '<td>'.$row2['fecha'].' '.$row2['hora'].'</td>';
			echo '<td>'.$row2['ticket'].'</td>';
			echo '<td>'.$row2['fecha_venta'].' '.$row2['hora_venta'].'</td>';
			echo '<td>'.$array_usuario[$row2['usuario']].'</td>';
		}
		else{
			$res2 = mysql_query("SELECT * FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND engomado = '".$row['engomado']."' AND CAST(certificado AS UNSIGNED) = '".$row1['folio']."' AND estatus != 'C'");
			if($row2 = mysql_fetch_array($res2)){
				echo '<td>Cancelacion</td>';
				echo '<td>'.$row2['cve'].'</td>';
				echo '<td>'.$row2['fecha'].' '.$row['hora'].'</td>';
				echo '<td>&nbsp;</td>';
				echo '<td>&nbsp;</td>';
				echo '<td>'.$array_usuario[$row2['usuario']].'</td>';
			}
			else{
				echo '<td>&nbsp;</td>';
				echo '<td>&nbsp;</td>';
				echo '<td>&nbsp;</td>';
				echo '<td>&nbsp;</td>';
				echo '<td>&nbsp;</td>';
				echo '<td>&nbsp;</td>';
				$t2++;
			}
		}
		echo '</tr>';
		$t1++;
	}
	echo '<tr bgcolor="#E9F2F8"><th align="left" colspan="3">'.$t1.' Registro(s)</th><th colspan="4">'.$t2.' Faltantes de ponchar</th></tr>';
	echo '</table>';
}
*/

if($_POST['cmd'] == 10){

	 
	
	$res = mysql_query("SELECT * FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	echo '<table>';
	echo '
		<tr>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'compra_certificados.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			  <td><a href="#" onClick="atcr(\'compra_certificados.php\',\'\',\'10\','.$_POST['reg'].')"><img src="images/buscar.gif" border="0" ">Buscar</a></td>
		</tr>';
		echo '<tr><td>Estatus</td><td><select name="fill" id="fill">';
	foreach($array_estatus2 as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$_POST['fill']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
	echo '<br>';

	echo '<table>';
	echo '<tr><td class="tableEnc">Compra de Certificados</td></tr>';
	echo '</table>';
	echo '<table style="font-size:15px">';
	echo '<tr><th align="left">Folio Compra</th><td>'.$row['folio'].'</td></tr>';
	echo '<tr><th align="left">Fecha Compra</th><td>'.$row['fecha_compra'].'</td></tr>';
	echo '<tr><th align="left">A&ntilde;o</th><td>'.$array_anios[$row['anio']].'</td></tr>';
	echo '<tr><th align="left">Tipo de Certificado</th><td>'.$array_engomado[$row['engomado']].'</td></tr></table>';
	echo '<br>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Folio</th><th>Tipo Ponchado</th><th>Folio</th><th>Fecha</th><th>Ticket</th><th>Fecha Ticket</th><th>Usuario</th>';
	echo '</tr>';
	$t1=$t2=0;
	$res1 = mysql_query("SELECT * FROM compra_certificados_detalle WHERE plaza='".$_POST['plazausuario']."' AND cvecompra='".$row['cve']."' AND tipo=0");
	while($row1 = mysql_fetch_array($res1)){
	//	rowb();
	//	echo '<td>'.$row1['folio'].'</td>';
		$res2 = mysql_query("SELECT a.cve, a.fecha, a.hora, a.usuario, a.ticket, b.fecha as fecha_venta, b.hora as hora_venta 
		FROM certificados a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza=".$_POST['plazausuario']." 
		AND a.engomado = ".$row['engomado']." AND a.certificado = ".$row1['folio']." AND a.estatus != 'C'");
		if($row2 = mysql_fetch_array($res2)){
			if($_POST['fill']==2){rowb();echo '<td>'.$row1['folio'].'</td>';
			echo '<td>Entrega</td>';
			echo '<td>'.$row2['cve'].'</td>';
			echo '<td>'.$row2['fecha'].' '.$row2['hora'].'</td>';
			echo '<td>'.$row2['ticket'].'</td>';
			echo '<td>'.$row2['fecha_venta'].' '.$row2['hora_venta'].'</td>';
			echo '<td>'.$array_usuario[$row2['usuario']].'</td>';
			$t1++;}
		}
		else{
			$res2 = mysql_query("SELECT cve, fecha, hora, usuario FROM certificados_cancelados WHERE plaza=".$_POST['plazausuario']." AND engomado = ".$row['engomado']." 
			AND certificado = ".$row1['folio']." AND estatus != 'C'");
			if($row2 = mysql_fetch_array($res2)){
			if($_POST['fill']==2){rowb();	echo '<td>'.$row1['folio'].'</td>';
				echo '<td>Cancelacion</td>';
				echo '<td>'.$row2['cve'].'</td>';
				echo '<td>'.$row2['fecha'].' '.$row2['hora'].'</td>';
				echo '<td>&nbsp;</td>';
				echo '<td>&nbsp;</td>';
				echo '<td>'.$array_usuario[$row2['usuario']].'</td>';
				$t1++;}
			}
			else{
				rowb();echo '<td>'.$row1['folio'].'</td>';
				echo '<td>&nbsp;</td>';
				echo '<td>&nbsp;</td>';
				echo '<td>&nbsp;</td>';
				echo '<td>&nbsp;</td>';
				echo '<td>&nbsp;</td>';
				echo '<td>&nbsp;</td>';
				$t2++;$t1++;
			}
		}
		echo '</tr>';
		//$t1++;
	}
	echo '<tr bgcolor="#E9F2F8"><th align="left" colspan="3">'.$t1.' Registro(s)</th><th colspan="4">'.$t2.' Faltantes de ponchar</th></tr>';
	echo '</table>';
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$res = mysql_query("SELECT * FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();validar('.$_POST['reg'].');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'compra_certificados.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Compra de Certificados</td></tr>';
		echo '</table>';
		echo '<table style="font-size:15px">';
		echo '<tr><th align="left">Folio Compra</th><td><input type="text" name="folio" id="folio" class="textField" size="30" style="font-size:12px" value="'.$row['folio'].'"></td></tr>';
		echo '<tr><th align="left">Fecha Compra</th><td><input type="text" name="fecha_compra" id="fecha_compra" class="readOnly" style="font-size:12px" size="12" value="'.$row['fecha_compra'].'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_compra,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">A&ntilde;o</th><td><select name="anio" id="anio"><option value="0">Seleccione</option>';
		foreach($array_anios as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['anio']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Tipo de Certificado</th><td><input type="hidden" name="engomado" id="engomado" value="'.intval($row['engomado']).'"><table><tr>';
		$i=0;
		foreach($array_engomado as $k=>$v){
			if($i==4){
				echo '</tr><tr>';
				$i=0;
			}
			echo '<td><input type="radio" name="auxengomado" id="auxengomado_'.$k.'" value="'.$k.'" onClick="if(this.checked){document.forma.engomado.value=this.value; }"';
			if($row['engomado']==$k) echo ' checked';
			echo '>'.$v.'&nbsp;&nbsp;&nbsp;</td>';
			$i++;
		}
		echo '</tr></table></td></tr>';
		echo '<tr><th align="left">Folio Inicial</th><td><input type="text" name="folioini" id="folioini" class="textField enteros" size="30" style="font-size:12px" value="'.$row['folioini'].'" onKeyUp="calcular()"></td></tr>';
		echo '<tr><th align="left">Folio Final</th><td><input type="text" name="foliofin" id="foliofin" class="textField enteros" size="30" style="font-size:12px" value="'.$row['foliofin'].'" onKeyUp="calcular()"></td></tr>';
		echo '<tr><th align="left">Cantidad</th><td><input type="text" name="cantidad" id="cantidad" class="readOnly enteros" size="15" style="font-size:12px" value="'.($row['foliofin']+1-$row['folioini']).'" readOnly></td></tr>';
		echo '<tr><th align="left">Folios Faltantes<br><span style="cursor:pointer;" onClick="agregar_faltante()"><font color="BLUE">Agregar</font></span></th>
		<td><table id="faltantes">';
		if($_POST['reg']==0){
			echo '<tr><td><input type="text" class="textField cfaltantes" style="font-size:12px" onKeyUp="validar_faltante(this)" size="30" name="faltante[]"></td></tr>';
			$cantfaltante='';
		}
		else{
			$cantfaltante=0;
			$res2=mysql_query("SELECT * FROM compra_certificados_detalle WHERE plaza='".$_POST['plazausuario']."' AND cvecompra='".$_POST['reg']."' AND tipo=1");
			while($row2=mysql_fetch_array($res2)){
				echo '<tr><td><input type="text" class="textField cfaltantes" style="font-size:12px" onKeyUp="validar_faltante(this)" size="30" name="faltante[]" value="'.$row2['folio'].'"></td></tr>';
				$cantfaltante++;
			}
		}
		echo '</table></td></tr>';
		echo '<tr><th align="left">Cantidad Faltantes</th><td><input type="text" name="cantidadf" id="cantidadf" class="readOnly enteros" size="15" style="font-size:12px" value="'.$cantfaltante.'" readOnly></td></tr>';
		echo '</table>';
		
		echo '<script>
				function agregar_faltante(){
					$("#faltantes").append(\'<tr><td><input type="text" class="textField cfaltantes" style="font-size:12px" onKeyUp="validar_faltante(this)" size="30" name="faltante[]"></td></tr>\');
				}
				
				function validar_faltante(campo){
					/*if((campo.value/1) > 0 && (campo.value/1)<(document.forma.folioini.value/1))
						campo.value = document.forma.folioini.value;
					else if((campo.value/1) > 0 && (campo.value/1)>(document.forma.foliofin.value/1))
						campo.value = document.forma.foliofin.value;*/
						
					var cantf=0;
					$(".cfaltantes").each(function(){
						if((this.value/1) > 0 && (this.value/1)>=(document.forma.folioini.value/1) && (this.value/1)<=(document.forma.foliofin.value/1))
							cantf++;
					});
					document.forma.cantidadf.value=cantf;
				}
				
				function validar(reg){
					if($.trim(document.forma.folio.value)==""){
						$("#panel").hide();
						alert("Necesita ingresar el folio de la compra");
					}
					else if(document.forma.fecha_compra.value==""){
						$("#panel").hide();
						alert("Necesita seleccionar la fecha de la compra");
					}
					else if(document.forma.anio.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el año de certificado");
					}
					else if(document.forma.engomado.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el tipo de certificado");
					}
					else if((document.forma.cantidad.value/1)==0){
						$("#panel").hide();
						alert("La cantidad no puede ser cero");
					}
					else if(validarFolios()==false){
						$("#panel").hide();
						alert("Error en los folios chocan con folios ya comprados");
					}
					else{
						atcr("compra_certificados.php","",2,reg);
					}
				}
				
				function calcular(){
					if((document.forma.folioini.value/1)>0 && (document.forma.foliofin.value/1)>0 && (document.forma.foliofin.value/1)>=(document.forma.folioini.value/1)){
						document.forma.cantidad.value=1+(document.forma.foliofin.value/1)-(document.forma.folioini.value/1);
					}
					else{
						document.forma.cantidad.value=0;
					}
				}
				
				function validarFolios(){
					if(document.forma.cveusuario.value==1) return true;
					var regresar = true;
					$.ajax({
					  url: "compra_certificados.php",
					  type: "POST",
					  async: false,
					  data: {
						engomado: document.getElementById("engomado").value,
						anio: document.getElementById("anio").value,
						plazausuario: document.forma.plazausuario.value,
						folioini: document.forma.folioini.value,
						foliofin: document.forma.foliofin.value,
						ajax: 3
					  },
						success: function(data) {
							if(data == "1"){
								regresar = false;
							}
						}
					});
					return regresar;
				}
				
			</script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	
	if($_POST['archivoname']!='compras'){
		$_POST['fecha_ini'] = '';
		$_POST['fecha_fin'] = '';
		$_POST['aniof'] = '';
		$_POST['engomadof'] = '';
		$_POST['mostrar'] = '';
		$_POST['estatus'] = '';
		$anios = array();
	}
	else{
		$anios = explode(',',$_POST['aniof']);
	}
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	echo '<td><a href="#" onClick="atcr(\'compra_certificados.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
	echo'<td><a href="#" onClick="atcr(\'\',\'_blank\',\'100\',\'0\')"><img src="images/b_print.png" border="0" title="Imprimir"></a>Imprimir&nbsp;</td>';
	if($_POST['cveusuario']==1){
		if($ValidarCertificados==1)
			echo '<td><input type="checkbox" checked onClick="atcr(\'compra_certificados.php\',\'\',13,0)">Validacion de Certificados</td></tr>';
		else
			echo '<td><input type="checkbox" onClick="atcr(\'compra_certificados.php\',\'\',12,0)">Validacion de Certificados</td></tr>';
	}
	
	echo '
		 </tr>';
	echo '</table>';
	echo '<table width="100%"><tr><td width="50%">';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="'.$_POST['fecha_ini'].'" >&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="'.$_POST['fecha_fin'].'" >&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>A&ntilde;o</td><td><select multiple="multiple" name="anios" id="anios">';
	$primero = true;
	foreach($array_anios as $k=>$v){
		echo '<option value="'.$k.'"';
		if(($primero && $_POST['archivoname']!='compras') || in_array($k, $anios)) echo ' selected';
		echo '>'.$v.'</option>';
		$primero = false;
	}
	echo '</select><input type="hidden" name="anio" id="anio" value="'.$_POST['aniof'].'"></td></tr>';
	echo '<tr><td>Tipo de Certificado</td><td><select name="engomado" id="engomado"><option value="">Todos</option>';
	
	foreach($array_engomado as $k=>$v){
		echo '<option value="'.$k.'"';
		if($_POST['engomadof']==$k) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">Todos</option>';
	$res=mysql_query("SELECT b.cve,b.usuario FROM compra_certificados a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY b.usuario");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['usuario'].'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="">Todos</option>';
	foreach($array_estatus as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$_POST['estatus']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr><input type="hidden" name="fill" id="fill" value="1">';
	$array_mostrar = array(0=>'Todos',1=>'Con pendietes de entrega', 2=>'Sin pendientes de entrega');
	echo '<tr><td>Mostrar</td><td><select name="mostrar" id="mostrar">';
	foreach($array_mostrar as $k=>$v){
		echo '<option value="'.$k.'"';
		if(($k==1 && $_POST['archivoname']!='compras') || $_POST['mostrar']==$k) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
	echo '</td><td id="concentrado"></td></tr></table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">
	
	$("#anios").multipleSelect({
		width: 500
	});	
	document.forma.anio.value=$("#anios").multipleSelect("getSelects");
	function buscarRegistros(btn)
	{
		document.forma.anio.value=$("#anios").multipleSelect("getSelects");
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","compra_certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&btn="+btn+"&mostrar="+document.getElementById("mostrar").value+"&anio="+document.getElementById("anio").value+"&estatus="+document.getElementById("estatus").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&engomado="+document.getElementById("engomado").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					datos = objeto.responseText.split("|*|");
					document.getElementById("Resultados").innerHTML = datos[0];
					document.getElementById("concentrado").innerHTML = datos[1];
				}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}
	//buscarRegistros(0); //Realizar consulta de todos los registros al iniciar la forma.
		
	
	</Script>
	';

	
}
	
bottom();

if($cvecobro>0){
		echo '<script>atcr(\'cobro_engomado.php\',\'_blank\',\'101\','.$cvecobro.');</script>';
	}
?>

