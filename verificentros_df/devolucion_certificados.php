<?php 

include ("main.php"); 

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$array_engomado = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_montoengomado[$row['cve']]=$row['precio'];
}

$res=mysql_query("SELECT local FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];

$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$res = mysql_query("SELECT * FROM tecnicos WHERE plaza='".$_POST['plazausuario']."'");
while($row=mysql_fetch_array($res)){
	$array_personal[$row['cve']]=$row['nombre'];
}

$array_estatus = array('A'=>'Activo','C'=>'Cancelado');

if($_POST['cmd']==101){
	require_once("../numlet.php");
	$res=mysql_query("SELECT * FROM devolucion_certificado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
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
	$texto.=chr(27).'!'.chr(8)." FOLIO: ".$row['cve'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." DEVOLUCION DE CERTIFICADO";
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." FECHA: ".$row['fecha']."   ".$row['hora'].'|';
	$texto.=chr(27).'!'.chr(40)."PLACA: ".$row['placa'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." T. CERTIFICADO:|".$array_engomado[$row['engomado']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." MONTO VENTA: ".number_format($row['monto_venta'],2);
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." T. CERTIFICADO ENTREGADO:|".$array_engomado[$row['engomadoentrega']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." MONTO ENTREGA: ".number_format($row['montoentrega'],2);
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." DEVOLUCION: ".number_format($row['monto_venta']-$row['montoentrega'],2);
	$texto.='|';
	$texto.='|RECIBE DINERO|IFE/INE:___________________________________|ADJUNTAR COPIA DE IFE/INE';
	$texto.='|';
	
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$texto.'&logo='.str_replace(' ','',$array_plaza[$row['plaza']]).'&barcode=1'.sprintf("%011s",(intval($row['cve']))).'&copia=1" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",2000);</script>';
	exit();
}

if($_POST['cmd']==101.1){
	$res=mysql_db_query($base,"SELECT * FROM plazas");
	while($Plaza=mysql_fetch_array($res)){
		$array_plaza[$row['cve']]=$row['nombre'];
	}
	include('../fpdf153/fpdf.php');
	include("../numlet.php");
	$res=mysql_db_query($base,"SELECT * FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	$pdf=new FPDF('P','mm','LETTER');
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',16);
	$pdf->Cell(190,10,$array_plaza[$_POST['plazausuario']],0,0,'C');
	$pdf->Ln();
	$pdf->Cell(95,10,'Entrega de Certificado',0,0,'L');
	$pdf->Cell(95,10,'Folio: '.$_POST['reg'],0,0,'R');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(95,5,'',0,0,'L');
	$pdf->Cell(95,5,'Fecha: '.fecha_letra($row['fecha']),0,0,'R');
	$pdf->Ln();
	$pdf->Cell(95,5,'Ticket: '.$row['ticket'],0,0,'L');
	$pdf->Ln();
	$pdf->Cell(95,5,'Placa: '.$row['placa'],0,0,'L');
	$pdf->Ln();
	$pdf->Cell(95,5,'Engomado: '.$array_engomado[$row['engomado']],0,0,'L');
	$pdf->Ln();
	$pdf->Cell(95,5,'Tecnico: '.$array_personal[$row['tecnico']],0,0,'L');
	$pdf->Ln();
	$pdf->Cell(95,5,'Certificado: '.$row['certificado'],0,0,'L');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();

	$pdf->SetFont('Arial','',10);
	$pdf->Cell(95,5,'Impreso por: '.$array_usuario[$_POST['cveusuario']],0,0,'L');
	$pdf->Cell(95,5,'Creado por: '.$array_usuario[$row['usuario']],0,0,'R');
	$pdf->Output();	
	exit();	
}

if($_POST['cmd']==100.1){
	include('../fpdf153/fpdf.php');
	include("../numlet.php");	
	$res=mysql_db_query($base,"SELECT * FROM plazas");
	while($Plaza=mysql_fetch_array($res)){
		$array_plaza[$row['cve']]=$row['nombre'];
	}
	class FPDF2 extends PDF_MC_Table {
		function Header(){
			global $_POST, $array_plaza;
			$this->SetFont('Arial','B',16);
			//$this->Image('images/membrete.JPG',30,3,150,15);
			$this->SetY(23);
			$this->MultiCell(190,5,'Listado de Entrega de Certificados de la Plaza '.$array_plaza[$_POST['plazausuario']].' del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'],0,'C');
			$this->MultiCell(190,5,$tit,0,'C');
			$this->Ln();
			$this->SetFont('Arial','B',9);
			$this->Cell(20,4,'Folio',0,0,'C',0);
			$this->Cell(20,4,'Fecha',0,0,'C',0);
			$this->Cell(20,4,'Ticket',0,0,'C',0);
			$this->Cell(20,4,'Placa',0,0,'C',0);
			$this->Cell(20,4,'Engomado',0,0,'C',0);
			$this->Cell(50,4,'Tecnico',0,0,'C',0);
			$this->Cell(30,4,'Certificado',0,0,'C',0);
			$this->Cell(20,4,'Usuario',0,0,'C',0);
			$this->Ln();		
		}
		function Footer(){
			$this->SetY(-15);
			$this->SetFont('Arial','B',11);
			$this->Cell(0,10,'P·gina '.$this->PageNo().' de {nb}',0,0,'C');
		}
	}
	$pdf=new FPDF2('P','mm','LETTER');
	$pdf->AliasNbPages();
	$pdf->AddPage('P');
	$pdf->SetFont('Arial','',9);
	$total=array();
	$i=0;
	$pdf->SetWidths(array(20,20,20,20,20,50,30,20));
	$pdf->SetAligns(array('C','C','C','C','C','L','C','C'));
	$select= " SELECT * FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	if ($_POST['ticket']!="") { $select.=" AND ticket='".$_POST['ticket']."' "; }
	if ($_POST['placa']!="") { $select.=" AND placa='".$_POST['placa']."' "; }
	if ($_POST['usuario']!="") { $select.=" AND usuario='".$_POST['usuario']."' "; }
	if ($_POST['engomado']!="") { $select.=" AND engomado='".$_POST['engomado']."' "; }
	if ($_POST['certificado']!="") { $select.=" AND certificado='".$_POST['certificado']."' "; }
	$select.=" ORDER BY cve DESC";
	$res=mysql_query($select);
	while($row=mysql_fetch_array($res)){
		$renglon=array();
		$aux='';
		if($row['estatus']=='C') $aux='(C)';
		$renglon[]=$row['cve'].$aux;
		$renglon[]=$row['fecha'];
		$renglon[]=$row['ticket'];
		$renglon[]=$row['placa'];
		$renglon[]=$array_engomado[$row['engomado']];
		$renglon[]=$array_personal[$row['tecnico']];
		$renglon[]=$row['certificado'];
		$renglon[]=$array_usuario[$row['usuario']];
		$pdf->Row($renglon);
		$i++;
	}
	$pdf->Ln();
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(40,4,$i.' Registro(s)',0,0,'L',0);
	$pdf->Output();	
	exit();	
}
if($_POST['cmd']==100) {

	echo '<h2>Listado de Devolucion de Certificados de la Plaza '.$array_plaza[$_POST['plazausuario']].' del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';
		//Listado de plazas
		$select= " SELECT * FROM devolucion_certificado WHERE plaza='".$_POST['plazausuario']."'";
		if ($_POST['certificado']!="") { $select.=" AND certificado='".$_POST['certificado']."' "; }
		elseif ($_POST['placa']!="") { $select.=" AND placa='".$_POST['placa']."' "; }
		elseif ($_POST['ticket']!="") { $select.=" AND ticket='".$_POST['ticket']."' "; }
		else{
			$select.=" AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
			if ($_POST['usuario']!="") { $select.=" AND usuario='".$_POST['usuario']."' "; }
			if ($_POST['engomado']!="") { $select.=" AND engomado='".$_POST['engomado']."' "; }
			if ($_POST['estatus']!="") { $select.=" AND estatus='".$_POST['estatus']."' "; }
		}
		$select.=" ORDER BY cve DESC";
		if($_POST['btn']==0) $select.=" LIMIT 1";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Ticket</th><th>Placa</th>
			<th>Tipo de Certificado Venta</th><th>Monto Venta</th><th>Tipo Certificado Entregado</th><th>Monto</th><th>Diferencia</th>
			<th>Usuario</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado';
					$row['certificado']='';
				}
				else{
					//echo '<a href="#" onClick="atcr(\'certificados.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					if(nivelUsuario()>1)
						echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el registro\')) atcr(\'devolucion_certificados.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				}	
				echo '</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="center">'.htmlentities($row['ticket']).'</td>';
				echo '<td align="center">'.htmlentities($row['placa']).'</td>';
				echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
				echo '<td align="right">'.number_format($row['monto_venta'],2).'</td>';
				echo '<td align="center">'.htmlentities($array_engomado[$row['engomadoentrega']]).'</td>';
				echo '<td align="right">'.number_format($row['montoentrega'],2).'</td>';
				echo '<td align="right">'.number_format($row['monto_venta']-$row['montoentrega'],2).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="9" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$res = mysql_query("SELECT * FROM usuarios WHERE cve='".$_POST['cveusuario']."'");
		$row = mysql_fetch_array($res);
		$permite_editar = $row['permite_editar'];
		$select= " SELECT a.* FROM devolucion_certificado a
		WHERE a.plaza='".$_POST['plazausuario']."'";
		if ($_POST['nota']>0) { $select.=" AND f.cve='".$_POST['nota']."' "; }
		if ($_POST['certificado']!="") { $select.=" AND a.certificado='".$_POST['certificado']."' "; }
		elseif ($_POST['placa']!="") { $select.=" AND a.placa='".$_POST['placa']."' "; }
		elseif ($_POST['ticket']!="") { $select.=" AND a.ticket='".$_POST['ticket']."' "; }
		else{
			$select.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
			if ($_POST['usuario']!="") { $select.=" AND a.usuario='".$_POST['usuario']."' "; }
			if ($_POST['engomado']!="") { $select.=" AND a.engomado='".$_POST['engomado']."' "; }
			if ($_POST['estatus']!="") { $select.=" AND a.estatus='".$_POST['estatus']."' "; }
		}
		$select.=" ORDER BY a.cve DESC";
		if($_POST['btn']==0) $select.=" LIMIT 1";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Ticket</th><th>Placa</th>
			<th>Tipo de Certificado Venta</th><th>Monto Venta</th><!--<th>Tipo Certificado Entregado</th><th>Monto</th>--><th>Devolucion</th>
			<th>Motivo</th><th>Usuario</th><th>Usuario Aplica</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
					$row['devolucion']=0;
				}
				else{
					echo '<a href="#" onClick="atcr(\'devolucion_certificados.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					if(nivelUsuario()>2 && $PlazaLocal != 1)
						echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el registro\')) atcr(\'devolucion_certificados.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				}	
				echo '</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="center">'.htmlentities($row['ticket']).'</td>';
				echo '<td align="center">'.htmlentities($row['placa']).'</td>';
				echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
				echo '<td align="right">'.number_format($row['monto_venta'],2).'</td>';
				//echo '<td align="center">'.htmlentities($array_engomado[$row['engomadoentrega']]).'</td>';
				//echo '<td align="right">'.number_format($row['montoentrega'],2).'</td>';
				if($_POST['cveusuario']==1){
					echo '<td align="center"><input type="text" id="devolucion_'.$row['cve'].'" class="textField" size="23" value="'.$row['devolucion'].'"><br>
					<input type="button" value="Guardar" onClick="guardarCampo('.$row['cve'].',\''.$row['devolucion'].'\',\'Devolucion\',\'devolucion\')"></td>';
				}
				else{
					echo '<td align="right">'.number_format($row['devolucion'],2).'</td>';
				}
				echo '<td>'.htmlentities(utf8_encode($row['motivo'])).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario_aplica']]).'</td>';
				echo '</tr>';
				$t+=$row['devolucion'];
			}
			echo '	
				<tr>
				<td colspan="6" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</td><td colspan="2" bgcolor="#E9F2F8">&nbsp;</td>
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
	$res = mysql_query("SELECT a.cve as ticket,a.fecha,a.placa,a.engomado, a.tipo_venta,a.monto as monto_venta, a.tipo_pago, 
		a.estatus, a.factura, a.notacredito, b.cve as folioentrega, b.engomado as engomadoentrega, b.certificado, b.entregado, 
		c.cve as cveintento 
		FROM cobro_engomado a 
		LEFT JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket AND b.estatus!='C' 
		LEFT JOIN cobro_engomado c ON c.plaza = a.plaza AND c.ticketpago = a.cve AND c.estatus != 'C'
		WHERE a.plaza='".$_POST['plazausuario']."' AND a.placa = '".$_POST['placa']."' AND a.cve = '".$_POST['ticket']."' GROUP BY a.cve ORDER BY a.cve DESC LIMIT 1");
	if($row=mysql_fetch_array($res)){
		if($row['estatus']=='C'){
			echo '-6|';
		}
		elseif($row['estatus']=='D'){
			echo '-2|';
		}
		elseif($row['folioentrega']>0){
			echo '-3|';
		}
		elseif($row['factura']>0 || $row['notacredito']>0){
			echo '-4|';
		}
		elseif($row['tipo_venta']!=0 || $row['tipo_pago'] == 6 || $row['tipo_pago'] == 2){
			echo '-5|';
		}
		elseif($row['cveintento']>0){
			echo '-7|';
		}
		elseif($row['tipo_pago']==5 || $row['tipo_pago']==7){
			echo '-8|';
		}
		else{
			echo $row['ticket'].'|'.$row['placa'].'|'.$row['engomado'].'|'.$array_engomado[$row['engomado']].'|'.$row['monto_venta'].'|'.$row['folioentrega'].'|'.$row['engomadoentrega'].'|'.$array_engomado[$row['engomadoentrega']].'|'.$array_montoengomado[$row['engomadoentrega']].'|'.$row['certificado'];
		}
	}
	else{
		echo '-1|';
	}
	exit();
}


if($_POST['ajax']==2.1){
	$res = mysql_query("SELECT a.cve as ticket,a.engomado,a.monto as monto_venta, b.cve as folioentrega, b.engomado as engomadoentrega, b.certificado FROM cobro_engomado a LEFT JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket AND b.estatus!='C' WHERE a.plaza='".$_POST['plazausuario']."' AND a.placa = '".$_POST['placa']."' AND a.estatus!='C' AND a.factura=0 ORDER BY a.cve DESC LIMIT 1");
	if($row=mysql_fetch_array($res)){
		$res1=mysql_query("SELECT cve FROM devolucion_certificado WHERE plaza='".$_POST['plazausuario']."' AND ticket='".$row['ticket']."' AND estatus!='C'");
		if(mysql_num_rows($res1)==0){
			echo $row['ticket'].'|'.$row['engomado'].'|'.$array_engomado[$row['engomado']].'|'.$row['monto_venta'].'|'.$row['folioentrega'].'|'.$row['engomadoentrega'].'|'.$array_engomado[$row['engomadoentrega']].'|'.$array_montoengomado[$row['engomadoentrega']].'|'.$row['certificado'];
		}
		else{
			echo '-2|';
		}
	}
	else{
		echo '-1|';
	}
	exit();
}

if($_POST['ajax']==6){
	mysql_query("UPDATE devolucion_certificado SET ".$_POST['campo']."='".$_POST['valor']."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
	mysql_query("INSERT historial SET menu='50',cveaux='".$_POST['folio']."',fecha='".fechaLocal()." ".horaLocal()."',obs='".$_POST['plazausuario']."',
			dato='".$_POST['descripcion']."',nuevo='".$_POST['valor']."',anterior='".$_POST['valor_anterior']."',arreglo='',usuario='".$_POST['cveusuario']."'");
	exit();
}

if($_POST['ajax']==10){
	$res = mysql_query("SELECT * FROM devolucion_certificado WHERE estatus!='C' AND plaza='".$_POST['plazausuario']."' AND placa='".$_POST['placa']."'");
	if(mysql_num_rows($res)>0){
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>Folio</th><th>Fecha</th><th>Ticket</th><th>Placa</th>
		<th>Devolucion</th>
		<th>Motivo</th><th>Usuario</th>';
		echo '</tr>';
		while($row = mysql_fetch_array($res)){
			rowb();
			echo '<td align="center">'.htmlentities($row['cve']).'</td>';
			echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
			echo '<td align="center">'.htmlentities($row['ticket']).'</td>';
			echo '<td align="center">'.htmlentities($row['placa']).'</td>';
			echo '<td align="right">'.number_format($row['devolucion'],2).'</td>';
			echo '<td>'.htmlentities(utf8_encode($row['motivo'])).'</td>';
			echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
			echo '</tr>';

		}
		echo '</table>';
	}

	exit();
}

top($_SESSION);

if($_POST['cmd']==3){
	mysql_query("UPDATE devolucion_certificado SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$res = mysql_query("SELECT ticket FROM devolucion_certificado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	mysql_query("UPDATE cobro_engomado SET estatus='A' WHERE plaza = '".$_POST['plazausuario']."' AND cve = '".$row['ticket']."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	if(strlen($_POST['ticket'])==12) $folio = intval(substr($_POST['ticket'],1));
	else $_POST['ticket'] = intval($_POST['ticket']);
	$res = mysql_query("SELECT cve FROM devolucion_certificado WHERE plaza='".$_POST['plazausuario']."' AND ticket='".$_POST['ticket']."' AND ticket > 0 AND estatus!='C'");
	if(mysql_num_rows($res)==0){
		if($_POST['cveusuario']!=1) $_POST['fecha'] = fechaLocal();
			$res = mysql_query("SELECT engomado FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['ticket']."' AND estatus!='C' ORDER BY cve DESC LIMIT 1");
			if(mysql_num_rows($res)>0 || ($_POST['cveusuario']==1 && $_POST['ticket']==0)){
				$insert = " INSERT devolucion_certificado 
								SET 
								plaza = '".$_POST['plazausuario']."',fecha='".$_POST['fecha']."',hora='".horaLocal()."',
								placa='".$_POST['placa']."',engomado='".$_POST['engomado']."',certificado='".$_POST['certificado']."',
								usuario='".$_POST['cveusuario']."',estatus='A',ticket='".$_POST['ticket']."',devolucion='".$_POST['devolucion']."',
								monto_venta='".$_POST['monto_venta']."',folioentrega='".$_POST['folioentrega']."',motivo='".$_POST['motivo']."',
								engomadoentrega='".$_POST['engomadoentrega']."',montoentrega='".$_POST['montoentrega']."', usuario_aplica='".$_POST['usuario_aplica']."'";
				mysql_query($insert);
				$cvedevolucion=mysql_insert_id();
				mysql_query("UPDATE cobro_engomado SET estatus='D' WHERE plaza = '".$_POST['plazausuario']."' AND cve = '".$_POST['ticket']."'");
			}
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		echo '<div id="dialog" style="display:none"><div id="capadevoluciones">
		</div>
		</div>'; 
				
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();validar();"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'devolucion_certificados.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">DEVOLUCION DE CERTIFICADO</td></tr>';
		echo '</table>';
		echo '<input type="hidden" name="ticket" id="ticket" value="">';
		echo '<table>';
		if($_POST['cveusuario']==1){
			echo '<tr><th align="left">Fecha</th><td><input type="text" name="fecha" id="fecha" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		}
		echo '<tr><th align="left">Usuario Aplica</th><td><select name="usuario_aplica" id="usuario_aplica"><option value="0">Seleccione</option>';
		$res1 = mysql_query("SELECT b.cve, b.usuario FROM cobro_engomado a INNER JOIN usuarios b ON b.cve = a.usuario WHERE a.fecha=CURDATE() GROUP BY a.usuario");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['cve'].'">'.$row1['usuario'].'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Placa</th><td><input type="text" name="placa" id="placa" class="textField" style="font-size:20px" size="10" value="" onKeyUp="if(event.keyCode==13){ traeTicket();} else{ this.value = this.value.toUpperCase();}"></td></tr>';
		echo '<tr><th align="left">Ticket</th><td><input type="text" name="fticket" id="fticket" class="textField" style="font-size:20px" size="10" value="" onKeyUp="if(event.keyCode==13){ traeTicket();} else{ this.value = this.value.toUpperCase();}">&nbsp;&nbsp;<font color="RED">Dar enter para traer el engomado de la nota</font></td></tr>';
		
		echo '<tr><th align="left">Tipo de Certificado</th><td><input type="hidden" name="engomado" id="engomado" value="0">';
		echo '<input type="text" name="nomengomado" value="" class="readOnly" size="50" style="font-size:20px" readOnly>';
		echo '</td></tr>';
		echo '<tr><th align="left">Monto Venta</th><td><input type="text" name="monto_venta" id="monto_venta" class="readOnly" style="font-size:20px" size="10" value="" onKeyUp="if(event.keyCode==13){ traeTicket();} else{ this.value = this.value.toUpperCase();}" readOnly></td></tr>';
		echo '<tr style="display:none;"><th align="left">Folio Entrega Certificado</th><td><input type="text" name="folioentrega" id="folioentrega" class="readOnly" style="font-size:20px" size="10" value="" onKeyUp="if(event.keyCode==13){ traeTicket();} else{ this.value = this.value.toUpperCase();}" readOnly></td></tr>';
		echo '<tr style="display:none;"><th align="left">Tipo de Certificado Entregado</th><td><input type="hidden" name="engomadoentrega" id="engomadoentrega" value="0">';
		echo '<input type="text" name="nomengomadoentrega" value="" class="readOnly" size="50" style="font-size:20px" readOnly>';
		echo '</td></tr>';
		echo '<tr style="display:none;"><th align="left">Monto Entrega</th><td><input type="text" name="montoentrega" id="montoentrega" class="readOnly" style="font-size:20px" size="10" value="" onKeyUp="if(event.keyCode==13){ traeTicket();} else{ this.value = this.value.toUpperCase();}" readOnly></td></tr>';
		echo '<tr style="display:none;"><th align="left">Holograma</th><td><input type="text" name="certificado" id="certificado" class="readOnly" style="font-size:20px" size="30" value="" readOnly></td></tr>';
		echo '<tr><th align="left">Devolucion</th><td><input type="text" name="devolucion" id="devolucion" class="textField" style="font-size:20px" size="10" value=""></td></tr>';
		echo '<tr><th align="left">Motivo de Devolucion</th><td><textarea name="motivo" id="motivo" class="textField" rows="3" cols="50"></textarea></td></tr>';
		echo '</table>';
		
		echo '<script>
				function validar(){
					if(document.forma.ticket.value=="" && (document.forma.cveusuario.value!="1" || document.forma.fticket.value!="00")){
						$("#panel").hide();
						alert("Necesita ingresar un ticket");
					}
					else if(document.forma.usuario_aplica.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el usuario aplica");
					}
					else if(document.forma.placa.value=="" && (document.forma.cveusuario.value!="1" || document.forma.fticket.value!="00")){
						$("#panel").hide();
						alert("Necesita ingresar la placa");
					}
					else if(document.forma.engomado.value=="0" && (document.forma.cveusuario.value!="1" || document.forma.fticket.value!="00")){
						$("#panel").hide();
						alert("Necesita seleccionar el tipo de certificado");
					}
					else if((document.forma.devolucion.value/1)==0){
						$("#panel").hide();
						alert("La devolucion no puede ser cero");
					}
					else if($.trim(document.forma.motivo.value)==""){
						$("#panel").hide();
						alert("Necesita ingresar el motivo de la devolucion");
					}
					else{
						revisar_placa();
					}
				}

				function revisar_placa(){
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto.open("POST","devolucion_certificados.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=10&placa="+document.getElementById("placa").value+"&plazausuario="+document.getElementById("plazausuario").value);
						objeto.onreadystatechange = function()
						{
							if (objeto.readyState==4)
							{
								if(objeto.responseText == ""){
									atcr("devolucion_certificados.php","",2,0);
								}
								else{
									$("#capadevoluciones").html(objeto.responseText);
									$("#dialog").dialog("open"); 
								}
							}
						}
					}
				}
				
				
				function traeTicket(){
					if(document.forma.fticket.value=="" && document.forma.placa.value==""){
						document.forma.engomado.value="";
						document.forma.nomengomado.value="";
						document.forma.monto_venta.value="";
						document.forma.engomadoentrega.value="";
						document.forma.nomengomadoentrega.value="";
						document.forma.folioentrega.value="";
						document.forma.certificado.value="";
						document.forma.montoentrega.value="";
						document.forma.devolucion.value="";
					}
					else{
						objeto=crearObjeto();
						if (objeto.readyState != 0) {
							alert("Error: El Navegador no soporta AJAX");
						} else {
							objeto.open("POST","devolucion_certificados.php",true);
							objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
							objeto.send("ajax=2&placa="+document.getElementById("placa").value+"&ticket="+document.getElementById("fticket").value+"&plazausuario="+document.getElementById("plazausuario").value);
							objeto.onreadystatechange = function()
							{
								if (objeto.readyState==4)
								{
									datos=objeto.responseText.split("|");
									if(datos[0]=="-1"){
										alert("La nota no existe");
										document.forma.engomado.value="";
										document.forma.nomengomado.value="";
										document.forma.ticket.value="";
										document.forma.monto_venta.value="";
										document.forma.engomadoentrega.value="";
										document.forma.nomengomadoentrega.value="";
										document.forma.folioentrega.value="";
										document.forma.certificado.value="";
										document.forma.montoentrega.value="";
										document.forma.devolucion.value="";
									}
									else if(datos[0]=="-2"){
										alert("Ya se le hizo la devolucion a la nota");
										document.forma.engomado.value="";
										document.forma.nomengomado.value="";
										document.forma.ticket.value="";
										document.forma.monto_venta.value="";
										document.forma.engomadoentrega.value="";
										document.forma.nomengomadoentrega.value="";
										document.forma.folioentrega.value="";
										document.forma.certificado.value="";
										document.forma.montoentrega.value=""
										document.forma.devolucion.value="";
									}
									else if(datos[0]=="-3"){
										alert("La nota tiene certificado entregado");
										document.forma.engomado.value="";
										document.forma.nomengomado.value="";
										document.forma.ticket.value="";
										document.forma.monto_venta.value="";
										document.forma.engomadoentrega.value="";
										document.forma.nomengomadoentrega.value="";
										document.forma.folioentrega.value="";
										document.forma.certificado.value="";
										document.forma.montoentrega.value="";
										document.forma.devolucion.value="";
									}
									else if(datos[0]=="-4"){
										alert("La nota esta facturada");
										document.forma.engomado.value="";
										document.forma.nomengomado.value="";
										document.forma.ticket.value="";
										document.forma.monto_venta.value="";
										document.forma.engomadoentrega.value="";
										document.forma.nomengomadoentrega.value="";
										document.forma.folioentrega.value="";
										document.forma.certificado.value="";
										document.forma.montoentrega.value="";
										document.forma.devolucion.value="";
									}
									else if(datos[0]=="-5"){
										alert("La nota debe de ser de contado y con importe");
										document.forma.engomado.value="";
										document.forma.nomengomado.value="";
										document.forma.ticket.value="";
										document.forma.monto_venta.value="";
										document.forma.engomadoentrega.value="";
										document.forma.nomengomadoentrega.value="";
										document.forma.folioentrega.value="";
										document.forma.certificado.value="";
										document.forma.montoentrega.value="";
										document.forma.devolucion.value="";
									}
									else if(datos[0]=="-6"){
										alert("La nota esta cancelada");
										document.forma.engomado.value="";
										document.forma.nomengomado.value="";
										document.forma.ticket.value="";
										document.forma.monto_venta.value="";
										document.forma.engomadoentrega.value="";
										document.forma.nomengomadoentrega.value="";
										document.forma.folioentrega.value="";
										document.forma.certificado.value="";
										document.forma.montoentrega.value="";
										document.forma.devolucion.value="";
									}
									else if(datos[0]=="-7"){
										alert("La nota tiene intentos");
										document.forma.engomado.value="";
										document.forma.nomengomado.value="";
										document.forma.ticket.value="";
										document.forma.monto_venta.value="";
										document.forma.engomadoentrega.value="";
										document.forma.nomengomadoentrega.value="";
										document.forma.folioentrega.value="";
										document.forma.certificado.value="";
										document.forma.montoentrega.value="";
										document.forma.devolucion.value="";
									}
									else if(datos[0]=="-8"){
										alert("La nota debe de ser de contado y con importe");
										document.forma.engomado.value="";
										document.forma.nomengomado.value="";
										document.forma.ticket.value="";
										document.forma.monto_venta.value="";
										document.forma.engomadoentrega.value="";
										document.forma.nomengomadoentrega.value="";
										document.forma.folioentrega.value="";
										document.forma.certificado.value="";
										document.forma.montoentrega.value="";
										document.forma.devolucion.value="";
									}
									else{
										document.forma.ticket.value=datos[0];
										document.forma.engomado.value=datos[2];
										document.forma.nomengomado.value=datos[3];
										document.forma.monto_venta.value=datos[4];
										document.forma.folioentrega.value=datos[5];
										document.forma.engomadoentrega.value=datos[6];
										document.forma.nomengomadoentrega.value=datos[7];
										document.forma.montoentrega.value=datos[8];
										document.forma.certificado.value=datos[9];
										tot=datos[4]-datos[8];
										document.forma.devolucion.value="";
									}
								}
							}
						}
					}
				}
				
				
				$("#dialog").dialog({ 
					bgiframe: true,
					autoOpen: false,
					modal: true,
					width: 450,
					height: 200,
					autoResize: true,
					position: "center",
					beforeClose: function( event, ui ) {
						$("#capadevoluciones").html("");
					},
					buttons: {
						"Continuar": function(){ 
							atcr("devolucion_certificados.php","",2,0);
						},
						"Cancelar": function(){ 
							$(this).dialog("close"); 
						}
					},
				}); 
			</script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
		if($PlazaLocal!=1)
			echo '<td><a href="#" onClick="atcr(\'devolucion_certificados.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="atcr(\'devolucion_certificados.php\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Ticket</td><td><input type="text" name="ticket" id="ticket" size="10" class="textField" value=""></td></tr>';
		echo '<tr';
	if($_POST['cveusuario'] != 1) echo ' style="display:none;"';
	echo '><td>Nota</td><td><input type="text" name="nota" id="nota" size="10" class="textField" value=""></td></tr>';
		echo '<tr><td>Placa</td><td><input type="text" name="placa" id="placa" size="10" class="textField" value="" onKeyUp="if(event.keyCode==13){ traeRegistro();}else{this.value = this.value.toUpperCase();}"></td></tr>';
		echo '<tr style="display:none;"><td>Tipo de Certificado</td><td><select name="engomado" id="engomado"><option value="">Todos</option>';
		foreach($array_engomado as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Holograma</td><td><input type="text" name="certificado" id="certificado" size="30" class="textField" value=""></td></tr>';
		echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">Todos</option>';
		$res=mysql_query("SELECT b.cve,b.usuario FROM devolucion_certificado a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY b.cve ORDER BY b.usuario");
		while($row=mysql_fetch_array($res)){
			echo '<option value="'.$row['cve'].'">'.$row['usuario'].'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="">Todos</option>';
		foreach($array_estatus as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	



/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros(btn)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","devolucion_certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&btn="+btn+"&estatus="+document.getElementById("estatus").value+"&ticket="+document.getElementById("ticket").value+"&certificado="+document.getElementById("certificado").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&placa="+document.getElementById("placa").value+"&engomado="+document.getElementById("engomado").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&nota="+document.getElementById("nota").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	function guardarCertificado(folio){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","devolucion_certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=4&folio="+folio+"&certificado="+document.getElementById("certificado_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros(1);}
			}
		}
	}

	function guardarCampo(folio, valor_anterior, descripcion, campo){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","devolucion_certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=6&folio="+folio+"&valor_anterior="+valor_anterior+"&descripcion="+descripcion+"&campo="+campo+"&valor="+document.getElementById("devolucion_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros(1);}
			}
		}
	}
	
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}
	buscarRegistros(0); //Realizar consulta de todos los registros al iniciar la forma.
		
	
	
	</Script>
	';

	
}
	
bottom();

if($cvedevolucion>0){
		echo '<script>atcr(\'devolucion_certificados.php\',\'_blank\',\'101\','.$cvedevolucion.');</script>';
	}
?>

