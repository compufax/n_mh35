<?php 

include ("main.php"); 

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$array_engomado = array();
$sel="SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND plazas like '%|".$_POST['plazausuario']."|%' AND entrega=1 ORDER BY nombre";
//echo''.$sel.'';
$res = mysql_query($sel) or die(mysql_error());
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio'];
}

$res = mysql_query("SELECT * FROM anios_certificados WHERE venta=1 ORDER BY nombre DESC LIMIT 2");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
}

$res=mysql_query("SELECT local, validar_certificado, entrega_simple FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];
$ValidarCertificados=$row[1];
$ValidarCertificados=1;
$EntregaSimple = $row[2];

$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$res = mysql_query("SELECT * FROM tipo_combustible ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_combustible[$row['cve']]=$row['nombre'];
}

if($_POST['cmd']==1)
	$res = mysql_query("SELECT * FROM tecnicos WHERE plaza='".$_POST['plazausuario']."' and estatus!=1");
else
	$res = mysql_query("SELECT * FROM tecnicos WHERE plaza='".$_POST['plazausuario']."'");
while($row=mysql_fetch_array($res)){
	$array_personal[$row['cve']]=$row['nombre'];
}

$res = mysql_query("SELECT * FROM cat_lineas WHERE plaza='".$_POST['plazausuario']."'");
while($row=mysql_fetch_array($res)){
	$array_lineas[$row['cve']]=$row['nombre'];
}
$res = mysql_query("SELECT * FROM cat_marcas WHERE 1");
while($row=mysql_fetch_array($res)){
	$array_marcas[$row['cve']]=$row['nombre'];
}

$array_estatus = array('A'=>'Activo','C'=>'Cancelado');

$array_tipo_venta[0] = 'Con Importe';
$res = mysql_query("SELECT * FROM tipo_venta ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_venta[$row['cve']] = $row['nombre'];
}
$array_tipo_pago = array();
$res = mysql_query("SELECT * FROM tipos_pago WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}

if($_POST['cmd']==200){
	echo '<h2>Desglose de Certificados Plaza '.$array_plaza[$_POST['plazausuario']].' del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Tipo de Certificado</th><th>Entregas</th><th>Cancelaciones</th><th>Total</th></tr>';
	$array_cant_engomado = array();
	$totales = array(0,0,0);
	$res = mysql_query("SELECT engomado, COUNT(cve) FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' GROUP BY engomado");
	while($row = mysql_fetch_array($res)){
		$array_cant_engomado[$row['engomado']]['entregas'] = $row[1];
		$totales[0] += $row[1];
		$array_cant_engomado[$row['engomado']]['total'] += $row[1];
		$totales[2] += $row[1];
	}
	$res = mysql_query("SELECT engomado, COUNT(cve) FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' GROUP BY engomado");
	while($row = mysql_fetch_array($res)){
		$array_cant_engomado[$row['engomado']]['cancelados'] = $row[1];
		$totales[1] += $row[1];
		$array_cant_engomado[$row['engomado']]['total'] += $row[1];
		$totales[2] += $row[1];
	}
	foreach($array_engomado as $k=>$v){
		rowb();
		echo '<td>'.$v.'</td>';
		echo '<td align="center">'.$array_cant_engomado[$k]['entregas'].'</td>';
		echo '<td align="center">'.$array_cant_engomado[$k]['cancelados'].'</td>';
		echo '<td align="center">'.$array_cant_engomado[$k]['total'].'</td>';
		echo '</tr>';
	}
	echo '<tr bgcolor="#E9F2F8"><th>Totales</th>';
	foreach($totales as $total){
		echo '<th>'.$total.'</th>';
	}
	echo '</tr></table>';

	exit();
}

if($_POST['cmd']==102) {
	$filename = "Entregas.xls";
        header("Content-type: application/octet-stream");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=\"$filename\"\n");
		$res=mysql_query("SELECT * FROM plazas WHERE cve='".$_POST['plazausuario']."'");
	$Plaza=mysql_fetch_array($res);

		$res = mysql_query("SELECT * FROM usuarios WHERE cve='".$_POST['cveusuario']."'");
		$row = mysql_fetch_array($res);
		$permite_editar = $row['permite_editar'];
		//Listado de plazas
		$select= " SELECT a.*, b.tipo_venta, b.engomado as engomadoticket, b.tipo_combustible, b.factura FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket";
		if($_POST['mostrar'] == 1) $select .= " LEFT JOIN (SELECT b.cve, a.engomado, b.folio, a.plaza FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus != 'C') c ON a.plaza = c.plaza AND a.engomado = c.engomado AND CAST(a.certificado as UNSIGNED) = c.folio";
		$select .= " WHERE a.plaza='".$_POST['plazausuario']."'";
		if($_POST['mostrar'] == 1) $select .= " AND ISNULL(c.cve)";
		if($_POST['certificado']!=""){ 
			$select.=" AND CAST(a.certificado as UNSIGNED)='".intval($_POST['certificado'])."' "; 
		}
		elseif($_POST['placa']!=""){ 
			$select.=" AND a.placa='".$_POST['placa']."' "; 
			if ($_POST['engomado']!="") { $select.=" AND a.engomado='".$_POST['engomado']."' "; }
		}
		elseif($_POST['ticket']!=""){
			//$select.=" AND a.cve='".$_POST['cve']."' "; 
			$row = mysql_fetch_array(mysql_query("SELECT placa FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['ticket']."'"));
			$select.=" AND a.placa='".$row['placa']."' "; 
		}
		else{
			$select.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
			if ($_POST['usuario']!="") { $select.=" AND a.usuario='".$_POST['usuario']."' "; }
			if ($_POST['engomado']!="") { $select.=" AND a.engomado='".$_POST['engomado']."' "; }
			if ($_POST['estatus']!="") { $select.=" AND a.estatus='".$_POST['estatus']."' "; }
			if ($_POST['entregado']!="all") { $select.=" AND a.entregado='".$_POST['entregado']."' "; }
			if ($_POST['anio']!="all") { $select.=" AND a.anio='".$_POST['anio']."' "; }
			if ($_POST['tipo_venta']!="all") { $select.=" AND b.tipo_venta='".$_POST['tipo_venta']."' "; }
		}
		if ($_POST['linea']!="") { $select.=" AND a.linea='".$_POST['linea']."' "; }
		$select.=" ORDER BY a.cve DESC";
		//if($_POST['btn']==0) $select.=" LIMIT 1";
		$res=mysql_query($select) or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);
		
		$array_totales_engomados=array();
		$rechazados=0;
		if(mysql_num_rows($res)>0) 
		{
			
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo'<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
			echo' <tr><td colspan="12" align="center"><h2>'.$Plaza['numero'].' '.$Plaza['nombre'].'</h2></td></tr>';
			echo'<tr ><td style="font-size:27px" align="left" colspan="12">Entregas</td></tr>';
			echo' <tr style="font-size:22px"><td colspan="6" align="left"> Periodo '.$_POST['fecha_ini'].' '.$_POST['fecha_fin'].'</td><td colspan="6" align="right">Fecha '.fechaLocal().'</td></tr>';
			echo'</table>';

			echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolo="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Ticket</th><th>Tipo de Venta</th><th>Placa</th><th>Marca</th><th>Tipo de Combustible</th>
			<th>Tipo de Certificado</th><th>Tecnico</th><th>Holograma</th><th>Linea<!--Entregado--></th><th>Usuario</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				//$res1=mysql_query("SELECT engomado,factura,tipo_combustible FROM cobro_engomado WHERE plaza = '".$row['plaza']."' AND cve='".$row['ticket']."'");
				//$row1=mysql_fetch_array($res1);
				//rowb();
				echo'<tr>';
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
					$row['certificado']='';
				}
				/*else{
					//echo '<a href="#" onClick="atcr(\'certificados.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					if(nivelUsuario()>2 && $row['factura']==0 && $PlazaLocal != 1 && (fechaLocal() == $row['fecha'] || $_POST['cveusuario']==1 || $permite_editar==1)){
						echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el registro\')) atcr(\'certificados.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
					}
					if(nivelUsuario()>1 && $row['factura']==0 && $PlazaLocal != 1 && (fechaLocal() == $row['fecha'] || $_POST['cveusuario']==1)){
						if(nivelUsuario()>2){
							if($row['entregado']==0){
								echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de que fue entregado el certificado\')) atcr(\'certificados.php\',\'\',\'4\','.$row['cve'].')"><img src="images/validosi.gif" border="0" title="Entregado '.$row['cve'].'"></a>';
							}
							else{
								echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de que no fue entregado el certificado\')) atcr(\'certificados.php\',\'\',\'5\','.$row['cve'].')"><img src="images/cerrar.gif" border="0" title="No Entregado '.$row['cve'].'"></a>';
							}
						}
					}
				}*/	
				echo '</td>';
				if($row['engomadoticket']!=$row['engomado'])
					echo '<td align="center"><font color="RE">'.htmlentities($row['cve']).'</font></td>';
				else
					echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				if($permite_editar==1 && $PlazaLocal != 1){
					echo '<td align="center">'.$row['fecha'].'<!--<input type="text" id="fechan_'.$row['cve'].'" class="textField" size="23" value="'.$row['fecha'].'">&nbsp;<span style="cursor:pointer;" onClick="displayCalendar(document.getElementById(\'fechan_'.$row['cve'].'\'),\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></span>--><br>
					<!--<input type="button" value="Guardar" onClick="guardarFecha('.$row['cve'].',\''.$row['fecha'].'\')"><br>'.$row['hora'].'--></td>';
				}
				else{
					echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				}
				if($_POST['ticket']==$row['ticket'])
					echo '<td align="center"><font color="BLUE">'.htmlentities($row['ticket']).'</font></td>';
				else
					echo '<td align="center">'.htmlentities($row['ticket']).'</td>';
				echo '<td align="center">'.htmlentities($array_tipo_venta[$row['tipo_venta']]).'</td>';
				/*if($_POST['cveusuario']==1 && $PlazaLocal != 1){
					echo '<td align="center"><input type="text" id="placa_'.$row['cve'].'" class="textField" size="10" value="'.htmlentities($row['placa']).'"><br>
					<input type="button" value="Guardar" onClick="guardarPlaca('.$row['cve'].')"></td>';
				}
				else{*/
					echo '<td align="center">'.htmlentities($row['placa']).'</td>';
					echo '<td align="center">'.htmlentities($array_marcas[$row['marca']]).'</td>';
				//}
				echo '<td align="center">'.htmlentities($array_tipo_combustible[$row['tipo_combustible']]).'</td>';
				if($_POST['cveusuario']<0 && $PlazaLocal != 1){
					echo '<td align="center">'.$row['engomado'].'<!--<select id="engomado_'.$row['cve'].'">';
					foreach($array_engomado as $k=>$v){
						echo '<option value="'.$k.'"';
						if($k==$row['engomado']) echo ' selected';
						echo '>'.$v.'</option>';
					}
					echo '</select><br>
					<input type="button" value="Guardar" onClick="guardarEngomado('.$row['cve'].')">--></td>';
				}
				else{
					echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
				}
				echo '<td align="left">'.htmlentities(utf8_encode($array_personal[$row['tecnico']])).'</td>';
				if($permite_editar==1 && $PlazaLocal != 1){
					echo '<td align="center">'.htmlentities($row['certificado']).'<!--<input type="text" id="certificado_'.$row['cve'].'" class="textField" size="20" value="'.htmlentities($row['certificado']).'"><br>
					<input type="button" value="Guardar" onClick="guardarCertificado('.$row['cve'].')">--></td>';
				}
				else{
					echo '<td align="center">'.htmlentities($row['certificado']).'</td>';
				}
				//echo '<td align="center">'.htmlentities($array_nosi[$row['entregado']]).'</td>';
				echo '<td align="center">'.htmlentities($array_lineas[$row['linea']]).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
				$array_totales_engomados[$row['engomado']][$row['tipo_combustible']]++;
				if($row['engomado']==9) $rechazados++;
			}
			echo '	
				<tr>
				<td colspan="12" bgcolo="#E9F2F8">';menunavegacion();echo '</td>
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
		/*echo '|*|<table><tr bgcolo="#E9F2F8"><th>Tipo</th><th>Combustible</th><th>Cantidad</th></tr>';
		$t=0;
		foreach($array_engomado as $k=>$v){
			foreach($array_tipo_combustible as $k1=>$v1){
				if($array_totales_engomados[$k][$k1]>0){
					echo '<tr><td>'.$v.'</td><td>'.$v1.'</td><td align="right">'.$array_totales_engomados[$k][$k1].'</td></tr>';
					$t+=$array_totales_engomados[$k][$k1];
				}
			}
		}
		echo '<tr bgcolor="#E9F2F8"><th align="left">Total</th><th>&nbsp;</th><th align="right">'.$t.'</th></tr>';
		echo '<tr bgcolor="#E9F2F8"><th align="left">Total-Rechazados</th><th>&nbsp;</th><th align="right">'.($t-$rechazados).'</th></tr>';
		echo '</table>';*/
		//echo '<br><font color="RED" style="font-size: 20px">A partir de este momento se tendran que capturar placa y ticket para la entrega de certificados.<br>Instrucciones del Licenciado Miguel Espina</font>';
		exit();	
}	

if($_POST['cmd']==101){
	$res=mysql_query("SELECT * FROM plazas");
	while($Plaza=mysql_fetch_array($res)){
		$array_plaza[$row['cve']]=$row['nombre'];
	}
	include('../fpdf153/fpdf.php');
	include("numlet.php");
	$res=mysql_query("SELECT * FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
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

if($_POST['cmd']==100){
	include('../fpdf153/fpdf.php');
	include("numlet.php");	
	$res=mysql_query("SELECT * FROM plazas");
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

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		$res = mysql_query("SELECT * FROM usuarios WHERE cve='".$_POST['cveusuario']."'");
		$row = mysql_fetch_array($res);
		$permite_editar = $row['permite_editar'];
		//Listado de plazas

		
		//if($_POST['cveusuario']!=3 && $_POST['cveusuario']!=1){
		if(nivelUsuario()<3){
			$_POST['fecha_ini'] = date('Y-m-d');
			$_POST['fecha_fin'] = date('Y-m-d');
		}
		$select= " SELECT a.*, b.tipo_venta, b.tipo_pago, d.nombre as nomdepositante, b.engomado as engomadoticket, b.tipo_combustible, b.factura, b.cambios_documento FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
		LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante ";
		if($_POST['mostrar'] == 1) $select .= " LEFT JOIN (SELECT b.cve, a.engomado, b.folio, a.plaza FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus != 'C') c ON a.plaza = c.plaza AND a.engomado = c.engomado AND CAST(a.certificado as UNSIGNED) = c.folio";
		$select .= " WHERE a.plaza='".$_POST['plazausuario']."'";
		if($_POST['mostrar'] == 1) $select .= " AND ISNULL(c.cve)";
		if($_POST['certificado']!=""){ 
			$select.=" AND CAST(a.certificado as UNSIGNED)='".intval($_POST['certificado'])."' "; 
		}
		elseif($_POST['nota']!=""){
			$select .= " AND b.cve = '".$_POST['nota']."'";
		}
		elseif($_POST['placa']!=""){ 
			$select.=" AND a.placa='".$_POST['placa']."' "; 
			if ($_POST['engomado']!="") { $select.=" AND a.engomado='".$_POST['engomado']."' "; }
		}
		elseif($_POST['ticket']!=""){
			//$select.=" AND a.cve='".$_POST['cve']."' "; 
			$row = mysql_fetch_array(mysql_query("SELECT placa FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['ticket']."'"));
			$select.=" AND a.placa='".$row['placa']."' "; 
		}
		else{
			$select.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
			if ($_POST['usuario']!="") { $select.=" AND a.usuario='".$_POST['usuario']."' "; }
			if ($_POST['engomado']!="") { $select.=" AND a.engomado='".$_POST['engomado']."' "; }
			if ($_POST['estatus']!="") { $select.=" AND a.estatus='".$_POST['estatus']."' "; }
			if ($_POST['entregado']!="all") { $select.=" AND a.entregado='".$_POST['entregado']."' "; }
			if ($_POST['anio']!="all") { $select.=" AND a.anio='".$_POST['anio']."' "; }
			if ($_POST['tipo_venta']!="all") { $select.=" AND b.tipo_venta='".$_POST['tipo_venta']."' "; }
			if ($_POST['tipo_pago']!="all") { $select.=" AND b.tipo_pago='".$_POST['tipo_pago']."' "; }
			if ($_POST['problema_obdii']!="all") { $select.=" AND a.problema_obdii='".$_POST['problema_obdii']."' "; }
			if($_POST['tipo_cliente']!='all') $select .= " AND IFNULL(d.agencia,-1)='".$_POST['tipo_cliente']."'";
		}
		if ($_POST['linea']!="") { $select.=" AND a.linea='".$_POST['linea']."' "; }
		
		$select.=" ORDER BY a.cve DESC";
		//echo''.$select.'';
		if($_POST['btn']==0) $select.=" LIMIT 1";
		$res=mysql_query($select) or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);
		
		$array_totales_engomados=array();
		$rechazados=0;
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th><span style="color: #0000FF; cursor: pointer;" onClick="SortTable(1,\'C\',\'tabla1\')">Folio</span></th><th>Fecha</th>';

			echo '<th>Ticket</th>';

			echo '<th>Tipo de Venta</th><th>Tipo de Pago</th><th>Placa</th><th>Marca</th><th style="display:none;">Problemas con OBDII</th>
			<th>Tipo de Certificado</th><th>Tecnico</th><th><span style="color: #0000FF; cursor: pointer;" onClick="SortTable(10,\'C\',\'tabla1\')">Holograma</span></th><th>Linea<!--Entregado--></th><th>Depositante</th><th>Usuario</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				//$res1=mysql_query("SELECT engomado,factura,tipo_combustible FROM cobro_engomado WHERE plaza = '".$row['plaza']."' AND cve='".$row['ticket']."'");
				//$row1=mysql_fetch_array($res1);
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
					//$row['certificado']='';
				}
				else{
					//echo '<a href="#" onClick="atcr(\'certificados.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					if(nivelUsuario()>2 && $row['factura']==0 && $PlazaLocal != 1 && (fechaLocal() == $row['fecha'] || $_POST['cveusuario']==1 || $permite_editar==1 || $_POST['cveusuario']==3)){
						echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el registro\')){ obscan=prompt(\'Motivo Cancelacion\'); atcr(\'certificados.php?obscan=\'+obscan,\'\',\'3\','.$row['cve'].');}"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
					}
					if(nivelUsuario()>1 && $row['factura']==0 && $PlazaLocal != 1 && (fechaLocal() == $row['fecha'] || $_POST['cveusuario']==1)){
						if(nivelUsuario()>2){
							if($row['entregado']==0){
								echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de que fue entregado el certificado\')) atcr(\'certificados.php\',\'\',\'4\','.$row['cve'].')"><img src="images/validosi.gif" border="0" title="Entregado '.$row['cve'].'"></a>';
							}
							else{
								echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de que no fue entregado el certificado\')) atcr(\'certificados.php\',\'\',\'5\','.$row['cve'].')"><img src="images/cerrar.gif" border="0" title="No Entregado '.$row['cve'].'"></a>';
							}
						}
					}
				}	
				echo '</td>';
				if($row['engomadoticket']!=$row['engomado'])
					echo '<td align="center"><font color="RED">'.htmlentities($row['cve']).'</font></td>';
				else
					echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				if($permite_editar==1 && $PlazaLocal != 1){
					echo '<td align="center"><input type="text" id="fechan_'.$row['cve'].'" class="textField" size="23" value="'.$row['fecha'].'">&nbsp;<span style="cursor:pointer;" onClick="displayCalendar(document.getElementById(\'fechan_'.$row['cve'].'\'),\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></span><br>
					<input type="button" value="Guardar" onClick="guardarFecha('.$row['cve'].',\''.$row['fecha'].'\')"><br>'.$row['hora'].'</td>';
				}
				else{
					echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				}
				if ($_POST['cveusuario']==1){
					if($_POST['ticket']==$row['ticket'])
						echo '<td align="center"><font color="BLUE">'.htmlentities($row['ticket']).'</font></td>';
					else
						echo '<td align="center">'.htmlentities($row['ticket']).'</td>';
				}
				else{
					echo '<td align="center">'.htmlentities($row['ticket']).'</td>';
				}
				echo '<td align="center">'.htmlentities($array_tipo_venta[$row['tipo_venta']]).'</td>';
				echo '<td align="center">'.htmlentities($array_tipo_pago[$row['tipo_pago']]).'</td>';
				/*if($_POST['cveusuario']==1 && $PlazaLocal != 1){
					echo '<td align="center"><input type="text" id="placa_'.$row['cve'].'" class="textField" size="10" value="'.htmlentities($row['placa']).'"><br>
					<input type="button" value="Guardar" onClick="guardarPlaca('.$row['cve'].')"></td>';
				}
				else{*/
					echo '<td align="center">'.htmlentities($row['placa']).'</td>';
					echo '<td align="center">'.htmlentities($array_marcas[$row['marca']]).'</td>';
				//}
				//echo '<td align="center">'.htmlentities($array_tipo_combustible[$row['tipo_combustible']]).'</td>';
				//echo '<td align="left">'.htmlentities(utf8_encode($array_nosi[$row['problema_obdii']])).'</td>';
				if($_POST['cveusuario']==1 && $PlazaLocal != 1){
					echo '<td align="center"><select id="engomado_'.$row['cve'].'">';
					foreach($array_engomado as $k=>$v){
						echo '<option value="'.$k.'"';
						if($k==$row['engomado']) echo ' selected';
						echo '>'.$v.'</option>';
					}
					echo '</select><br>
					<input type="button" value="Guardar" onClick="guardarEngomado('.$row['cve'].')"></td>';
				}
				else{
					echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
				}
				if($row['cambios_documento'] == 'SUBIDO'){
					echo '<td align="center"><select id="tecnico_'.$row['cve'].'">';
					foreach($array_personal as $k=>$v){
						echo '<option value="'.$k.'"';
						if($k==$row['tecnico']) echo ' selected';
						echo '>'.$v.'</option>';
					}
					echo '</select><br>
					<input type="button" value="Guardar" onClick="guardarTecnico('.$row['cve'].')"></td>';
				}
				else{
					echo '<td align="left">'.htmlentities(utf8_encode($array_personal[$row['tecnico']])).'</td>';
				}
				//if($permite_editar==1 && $PlazaLocal != 1){
				if($_POST['cveusuario']==1 && $PlazaLocal != 1){
					echo '<td align="center"><input type="text" id="certificado_'.$row['cve'].'" class="textField" size="20" value="'.htmlentities($row['certificado']).'"><br>
					<input type="button" value="Guardar" onClick="guardarCertificado('.$row['cve'].')"></td>';
				}
				else{
					echo '<td align="center">'.($row['certificado']/1).'</td>';
				}
				//echo '<td align="center">'.htmlentities($array_nosi[$row['entregado']]).'</td>';
				if($row['cambios_documento'] == 'SUBIDO'){
					echo '<td align="center"><select id="linea_'.$row['cve'].'">';
					foreach($array_lineas as $k=>$v){
						echo '<option value="'.$k.'"';
						if($k==$row['linea']) echo ' selected';
						echo '>'.$v.'</option>';
					}
					echo '</select><br>
					<input type="button" value="Guardar" onClick="guardarLineas('.$row['cve'].')"></td>';
				}
				else{
					echo '<td align="center">'.utf8_encode($array_lineas[$row['linea']]).'</td>';
				}
				echo '<td align="left">'.htmlentities(utf8_encode($row['nomdepositante'])).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
				$array_totales_engomados[$row['engomado']][$row['tipo_combustible']]++;
				if($row['engomado']==9) $rechazados++;
			}
			echo '	
				<tr>
				<td colspan="15" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
		$sel1= " SELECT a.*,b.cve as certificado, b.certificado as holograma,b.engomado as engomado_entrega, CONCAT(b.fecha,' ',b.hora) as fechaentrega, TIMEDIFF(IFNULL(CONCAT(b.fecha,' ',b.hora),NOW()),CONCAT(a.fecha,' ',a.hora)) as diferencia FROM cobro_engomado a LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
		LEFT JOIN depositantes c ON c.cve = a.depositante AND c.plaza = a.plaza 
		WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND IFNULL(b.cve,0)=0  AND a.tipo_venta!=3 and a.estatus='A'";
		$re1=mysql_query($sel1);
		$sin_entrega = mysql_num_rows($re1);
				echo '<table>';
			  echo'<tr><th align="right">Sin Entrega</th><td align="right">&nbsp;'.$sin_entrega.'</td></tr></table>
			  
			  <table><tr><th align="left">Tipo</th><th>No</th><th>Cancelados</th><th>Total</th></tr>';
			  $t_act=0;
			  $t_can=0;
			  foreach($array_engomado as $k=>$v){
		
		$select="SELECT count(a.cve) as resultado FROM certificados a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
		AND a.engomado IN (".$k.") and a.estatus!='C' ORDER BY a.cve DESC";
		$re1=mysql_query($select);
		$row3 = mysql_fetch_array($re1);
		/*$selec="SELECT count(a.cve) as resultado FROM certificados a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
		AND a.engomado IN (".$k.") and a.estatus='C' ORDER BY a.cve DESC";
		$re2=mysql_query($selec);
		$row4 = mysql_fetch_array($re2);*/
		$selec= " SELECT count(cve) as resultado FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND engomado IN (".$k.") AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		$re2=mysql_query($selec);
		$row4 = mysql_fetch_array($re2);
		$sele="SELECT count(a.cve) as resultado FROM certificados a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
		AND a.engomado IN (".$k.") ORDER BY a.cve DESC";
		$re3=mysql_query($sele);
		$row5 = mysql_fetch_array($re3);
		$re=0;
				echo '<tr><th align="left">'.$v.'</th>
				<td align="right">&nbsp;'.$row3['resultado'].'</td>
				<td align="right">&nbsp;'.$row4['resultado'].'</td>';
				$re=$row3['resultado'] + $row4['resultado'];
			echo'<td align="right">&nbsp;'.$re.'</td></tr>';
			$t_act=$t_act + $row3['resultado'];
			$t_can=$t_can + $row4['resultado'];
				}
			$t_t=$t_can+$t_act;
				echo'<tr><th align="right">Total</th><td align="right">'.$t_act.'</td><td align="right">'.$t_can.'</td><td align="right">'.$t_t.'</td></tr>';
			  echo'</table>';
	
		/*echo'<table><tr bgcolor="#E9F2F8"><th>Tipo</th><th>Combustible</th><th>Cantidad</th></tr>';
		$t=0;
		foreach($array_engomado as $k=>$v){
			foreach($array_tipo_combustible as $k1=>$v1){
				if($array_totales_engomados[$k][$k1]>0){
					echo '<tr><td>'.$v.'</td><td>'.$v1.'</td><td align="right">'.$array_totales_engomados[$k][$k1].'</td></tr>';
					$t+=$array_totales_engomados[$k][$k1];
				}
			}
		}
		echo '<tr bgcolor="#E9F2F8"><th align="left">Total</th><th>&nbsp;</th><th align="right">'.$t.'</th></tr>';
		echo '<tr bgcolor="#E9F2F8"><th align="left">Total-Rechazados</th><th>&nbsp;</th><th align="right">'.($t-$rechazados).'</th></tr>';
		echo '</table>';*/
		//echo '<br><font color="RED" style="font-size: 20px">A partir de este momento se tendran que capturar placa y ticket para la entrega de certificados.<br>Instrucciones del Licenciado Miguel Espina</font>';
		exit();	
}	

if($_POST['ajax']==2.1){
	/*$res = mysql_query("SELECT a.cve,a.fecha FROM cobro_engomado a LEFT JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket AND b.estatus!='C' WHERE a.plaza='".$_POST['plazausuario']."' AND a.placa = '".$_POST['placa']."' AND a.estatus!='C' AND ISNULL(b.cve) LIMIT 1");
	if($row=mysql_fetch_array($res)){
		echo $row['cve'].'|'.$row['fecha'];
	}
	else{
		echo '-1|';
	}*/
	$res = mysql_query("SELECT a.cve,a.fecha,a.monto,a.placa FROM cobro_engomado a LEFT JOIN certificados b on a.plaza = b.plaza and a.cve = b.ticket AND b.estatus!='C' WHERE a.plaza='".$_POST['plazausuario']."' AND a.placa = '".$_POST['placa']."' AND a.estatus!='C' AND DATEDIFF(CURDATE(),a.fecha)<60 AND ISNULL(b.cve) ORDER BY a.cve DESC LIMIT 1");
	if($row=mysql_fetch_array($res)){
		$res1=mysql_query("SELECT cve,certificado,fecha FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND ticket = '".$row['cve']."' AND estatus!='C'");
		if($row1=mysql_fetch_array($res1)){
			echo '-2|'.$row1['cve'].'|'.$row1['certificado'].'|'.$row1['fecha'];
		}
		else{
			echo $row['cve'].'|'.$row['fecha'].'|';
			if($row['monto']==0){
				$res=mysql_query("SELECT monto FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND placa='".$row['placa']."' AND monto>0 ORDER BY cve DESC LIMIT 1");
				$row = mysql_fetch_array($res);
			}
			$res1 = mysql_query("SELECT SUM(recuperacion) FROM recuperacion_certificado WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus!='C' AND a.ticket = '".$row['cve']."'");
			$row1 = mysql_fetch_array($res1);
			echo round($row['monto']+$row1[0],2);
		}
	}
	else{
		echo '-1|';
	}
	exit();
}

if($_POST['ajax']==3){
	//echo '0';
	//exit();
	/*if($_POST['engomado']==3){
		$res = mysql_query("SELECT cve FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['ticket']."'");
		$row = mysql_fetch_array($res);
		if($row['engomado']!=$_POST['engomado']){
			echo '5';
			exit();
		}
	}*/
	$res = mysql_query("SELECT cve FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$_POST['engomado']."' AND anio='".$_POST['anio']."' AND CAST(certificado AS UNSIGNED)='".intval($_POST['certificado'])."' AND estatus!='C'");
	if(mysql_num_rows($res)==0){
		$res = mysql_query("SELECT cve FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$_POST['engomado']."' AND anio='".$_POST['anio']."' AND CAST(certificado AS UNSIGNED)='".intval($_POST['certificado'])."' AND estatus!='C'");
		if(mysql_num_rows($res)>0){
			echo '1';
		}
		else{
			if($ValidarCertificados == 1 && $_POST['cveusuario'] != 1){
				//if($_POST['engomado'] == 3 || $_POST['engomado'] == 19)
					$res = mysql_query("SELECT b.estatus, b.tipo FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$_POST['engomado']."' AND a.estatus!='C' AND b.folio='".intval($_POST['certificado'])."'");
				//else
				//	$res = mysql_query("SELECT b.estatus, b.tipo FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$_POST['engomado']."' AND a.estatus!='C' AND b.folio='".intval($_POST['certificado'])."'");
				if($row = mysql_fetch_array($res)){
					if($row['tipo']==0)
						echo '0';
					else
						echo '4';
				}
				else{
					echo '3';
				}
			}
			else{
				echo '0';
			}
		}
	}
	else{
		echo '2';
	}
	exit();
}

if($_POST['ajax']==2){
	if(strlen($_POST['nota'])==12) $folio = intval(substr($_POST['nota'],1,11));
	else $folio = intval($_POST['nota']);
	if($EntregaSimple == 1)
		$res = mysql_query("SELECT a.* FROM cobro_engomado a WHERE a.plaza='".$_POST['plazausuario']."' AND a.cve='".$folio."' ORDER BY a.cve DESC LIMIT 1");
	else
		$res = mysql_query("SELECT a.* FROM cobro_engomado a WHERE a.plaza='".$_POST['plazausuario']."' AND a.placa='".$_POST['placa']."' AND a.cve='".$folio."' ORDER BY a.cve DESC LIMIT 1");
	if($row=mysql_fetch_array($res)){
		if($row['estatus']=='C'){
			echo '-2|';
		}
		elseif($row['estatus']=='B'){
			echo '-6|';
		}
		elseif($row['estatus']=='D'){
			echo '-4|';
		}
		elseif($row['anio']==1){
			echo '-3|';
		}
		elseif($row['tipo_venta'] == 3){
			echo '-5|';
		}
		else{
			$res = mysql_query("SELECT * FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND ticket='".$row['cve']."' AND estatus!='C'");
			if(mysql_num_rows($res)==0){
				$placa = $row['placa'];
				$anio = $row['anio'];
				$cve = $row['cve'];
				$fecha = $row['fecha'];
				if($row['monto']==0){
					$res=mysql_query("SELECT cve,if(descuento50=1,monto_verificacion,monto) FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND placa='".$row['placa']."' AND estatus!='D' AND estatus!='C' AND cve<'$folio' AND monto>0 ORDER BY cve DESC LIMIT 1");
					$row = mysql_fetch_array($res);
				}
				if($row['monto'] > 0)
				{
					$res1 = mysql_query("SELECT SUM(a.devolucion) FROM devolucion_certificado a WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus!='C' AND a.ticket = '".$row['cve']."'");
					$row1 = mysql_fetch_array($res1);
					if($row1[0] >= $row['monto']){
						echo '-4|';
						exit();
					}
				}
				$res1 = mysql_query("SELECT SUM(a.recuperacion) FROM recuperacion_certificado a WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus!='C' AND a.ticket = '".$row['cve']."'");
				$row1 = mysql_fetch_array($res1);
				echo $cve.'|'.$fecha.'|'.round($row['monto']+$row1[0],2).'|'.$placa.'|'.$anio;
			}
			else{
				$row=mysql_fetch_array($res);
				echo "-1|Entrega #".$row['cve']." Holograma: ".$row['certificado'];
			}
		}
	}
	else{
		echo "0|";
	}
	exit();
}

if($_POST['ajax']==4){
	$engomado = 0;
	$res1 = mysql_query("SELECT * FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
	$row1 = mysql_fetch_array($res1);
	$res = mysql_query("SELECT cve FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND anio='".$row1['anio']."' AND CAST(certificado AS UNSIGNED)='".intval($_POST['certificado'])."' AND estatus!='C'");
	if(mysql_num_rows($res)==0){
		$res = mysql_query("SELECT cve FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND cve!='".$_POST['folio']."' AND anio='".$row1['anio']."' AND CAST(certificado AS UNSIGNED)='".intval($_POST['certificado'])."' AND estatus!='C'");
		if(mysql_num_rows($res)>0){
			echo 'El holograma ya se entrego';
			exit();
		}
		else{
			$res = mysql_query("SELECT a.engomado, b.estatus, b.tipo, a.anio FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus!='C' AND b.folio='".intval($_POST['certificado'])."' ORDER BY b.cve DESC LIMIT 1");
			if($row = mysql_fetch_array($res)){
				//if($row['engomado'] == 3 || $row['engomado'] == 19 || $row['anio'] == $row1['anio']){
					$engomado = $row['engomado'];
				/*}
				else{
					echo 'El holograma no existe';
					exit();
				}*/
			}
			else{
				echo 'El holograma no existe';
				exit();
			}
		}
	}
	else{
		echo 'El holograma esta cancelado';
		exit();
	}
	if($engomado > 0)
		mysql_query("UPDATE certificados SET engomado='$engomado',certificado='".$_POST['certificado']."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
	exit();
}

if($_POST['ajax']==5){
	mysql_query("UPDATE certificados SET engomado='".$_POST['engomado']."',monto='".$array_engomadoprecio[$_POST['engomado']]."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
	exit();
}

if($_POST['ajax']==6){
	mysql_query("UPDATE certificados SET fecha='".$_POST['fecha']."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
	mysql_query("INSERT historial SET menu='8',cveaux='".$_POST['folio']."',fecha='".fechaLocal()." ".horaLocal()."',obs='".$_POST['plazausuario']."',
			dato='Fecha Entrega',nuevo='".$_POST['fecha']."',anterior='".$_POST['fecha_anterior']."',arreglo='',usuario='".$_POST['cveusuario']."'");
	exit();
}

if($_POST['ajax']==8){
	mysql_query("UPDATE certificados SET ".$_POST['campo']."='".$_POST['valor']."',monto='".$array_engomadoprecio[$_POST['engomado']]."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
	exit();
}

if($_POST['ajax']==7){
	mysql_query("UPDATE certificados SET placa='".$_POST['placa']."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
	exit();
}

if($_POST['ajax']==9){
	$resultado = array('error' => 0, 'mensaje_error' => '', 'engomado' => '', 'valor_engomado' => 0);
	if($_POST['ticket'] > 0){
		$Ticket = mysql_fetch_array(mysql_query("SELECT engomado FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['ticket']."'"));
		$filtroengomado = " AND engomado NOT IN (21, 22)";
		if($Ticket[0]!=1) $filtroengomado = " AND engomado = '".$Ticket[0]."'";
	}
	else{
		$filtroengomado="";
	}
	$res = mysql_query("SELECT cve FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND CAST(certificado AS UNSIGNED)='".intval($_POST['certificado'])."' AND estatus!='C'{$filtroengomado}");
	if(mysql_num_rows($res)==0){
		$res = mysql_query("SELECT cve FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND CAST(certificado AS UNSIGNED)='".intval($_POST['certificado'])."' AND estatus!='C'{$filtroengomado}");
		if(mysql_num_rows($res)>0){
			$resultado['error'] = 1;
			$resultado['mensaje_error'] = 'El holograma ya se entrego';
		}
		else{
			$res = mysql_query("SELECT a.engomado, b.estatus, b.tipo, a.anio FROM compra_certificados a 
			INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra 
			WHERE a.plaza='".$_POST['plazausuario']."' {$filtroengomado} AND a.estatus!='C' AND b.folio='".intval($_POST['certificado'])."' ORDER BY b.cve DESC LIMIT 1");
			$resultado['query'] = utf8_encode("SELECT a.engomado, b.estatus, b.tipo, a.anio FROM compra_certificados a 
			INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra 
			WHERE a.plaza='".$_POST['plazausuario']."' {$filtroengomado} AND a.estatus!='C' AND b.folio='".intval($_POST['certificado'])."' ORDER BY b.cve DESC LIMIT 1");
			if($row = mysql_fetch_array($res)){
				//if($row['engomado'] == 3 || $row['engomado'] == 19 || $row['anio'] == $_POST['anio']){
					$resultado['engomado'] = '+*<option value="'.$row['engomado'].'">'.$array_engomado[$row['engomado']].'</option>';
					$resultado['valor_engomado'] = $array_engomadoprecio[$row['engomado']];
				/*}
				else{
					$resultado['error'] = 1;
					$resultado['mensaje_error'] = 'El holograma no existe!!';
				}*/
			}
			else{
				$resultado['error'] = 1;
				$resultado['mensaje_error'] = 'El holograma no existe!';
			}
		}
	}
	else{
		$resultado['error'] = 1;
		$resultado['mensaje_error'] = 'El holograma esta cancelado';
	}
	echo json_encode($resultado);
	exit();
}

if($_POST['ajax']==20){
	$resultado = array('error' => 0, 'mensaje_error' => '', 'cve' => '', 'nombre' => 0);
	$res = mysql_query("SELECT * FROM tecnicos WHERE plaza='".$_POST['plazausuario']."' AND clave='".$_POST['tecnico']."' AND clave!=''");
	if($row = mysql_fetch_array($res)){
		if($row['estatus']==1){
			$resultado['error'] = 1;
			$resultado['mensaje_error'] = 'El tecnico esta inactivo';
		}
		else{
			$resultado['cve'] = $row['cve'];
			$resultado['nombre'] = utf8_encode($row['nombre']);
		}
	}
	else{
		$resultado['error'] = 1;
		$resultado['mensaje_error'] = 'No se encontro la clave';
	}
	echo json_encode($resultado);
	exit();
}

top($_SESSION);
$resPlaza = mysql_query("SELECT * FROM plazas WHERE cve='".$_POST['plazausuario']."'");
if($rowPlaza=mysql_fetch_array($resPlaza)){
	echo '<h2>Plaza: '.$rowPlaza['numero'].' '.$rowPlaza['nombre'].'</h2>';
}
if($_POST['cmd']==5){
	mysql_query("UPDATE certificados SET entregado=0,cambios_entregado=CONCAT(cambios_entregado,'|0,".$_POST['cveusuario'].",".fechaLocal()." ".horaLocal()."') WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

if($_POST['cmd']==4){
	mysql_query("UPDATE certificados SET entregado=1,cambios_entregado=CONCAT(cambios_entregado,'|1,".$_POST['cveusuario'].",".fechaLocal()." ".horaLocal()."') WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

if($_POST['cmd']==3){
	mysql_query("UPDATE certificados SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."',obscan='".$_GET['obscan']."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$res = mysql_query("SELECT * FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	mysql_query("UPDATE compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra SET b.estatus=0 WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$row['engomado']."' AND a.estatus!='C' AND b.folio='".intval($row['certificado'])."' AND b.estatus=1");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	if($_POST['engomado']>0){
		if(strlen($_POST['ticket'])==12) $folio = intval(substr($_POST['ticket'],1));
		else $_POST['ticket'] = intval($_POST['ticket']);
		$res = mysql_query("SELECT cve FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND ticket='".$_POST['ticket']."' AND estatus!='C'");
		if(mysql_num_rows($res)==0){
			$res = mysql_query("SELECT cve FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$_POST['engomado']."' AND CAST(certificado AS UNSIGNED)='".intval($_POST['certificado'])."' AND estatus!='C'");
			if(mysql_num_rows($res)==0){
				$res = mysql_query("SELECT engomado FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['ticket']."' AND estatus!='C' ORDER BY cve DESC LIMIT 1");
				if($row=mysql_fetch_array($res)){
					//if($_POST['engomado'] == 19 || $_POST['engomado'] == 3)
						$res = mysql_query("SELECT b.estatus, b.tipo FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$_POST['engomado']."' AND a.estatus!='C' AND b.folio='".intval($_POST['certificado'])."' AND b.tipo=0");
					//else
					//	$res = mysql_query("SELECT b.estatus FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$_POST['engomado']."' AND a.estatus!='C' AND b.folio='".intval($_POST['certificado'])."' AND b.tipo=0");
					if(mysql_num_rows($res)>0 || $ValidarCertificados == 0){
						$insert = " INSERT certificados 
										SET 
										plaza = '".$_POST['plazausuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',monto='".$array_engomadoprecio[$_POST['engomado']]."',
										placa='".$_POST['placa']."',engomado='".$_POST['engomado']."',certificado='".$_POST['certificado']."',anio='".$_POST['anio']."',
										usuario='".$_POST['cveusuario']."',estatus='A',ticket='".$_POST['ticket']."',tecnico='".$_POST['tecnico']."',entregado='1',linea='".$_POST['linea']."',
										problema_obdii='".$_POST['problema_obdii']."',marca='".$_POST['marca']."'";
						mysql_query($insert) or die(mysql_error());
						//if($_POST['engomado'] == 19 || $_POST['engomado'] == 3)
							mysql_query("UPDATE compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra SET b.estatus=1 WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$_POST['engomado']."' AND a.estatus!='C' AND b.folio='".intval($_POST['certificado'])."' AND b.tipo=0");
						//else
						//	mysql_query("UPDATE compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra SET b.estatus=1 WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$_POST['engomado']."' AND a.estatus!='C' AND b.folio='".intval($_POST['certificado'])."' AND b.tipo=0");
					}
				}
			}
		}
	}
	else{
		echo '<script>alert("Ocurrio un error al guardar la entrega favor de capturarla de nuevo");</script>';	
	}
	$_POST['cmd']=1;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		echo '<div id="dialog" style="display:none">
		<table>
		<tr><td class="tableEnc">Indique si fue entregado o no el certificado</td></tr>
		</table>
		<table width="100%">
			<tr><td width="100%" style="font-size:25px">
				Entregado&nbsp;&nbsp;<input type="checkbox" class="chk" id="checkentregado" value="1" onClick="if(this.checked){$(\'#entregado\').val(1);$(\'#checknoentregado\').removeAttr(\'checked\');}else{$(\'#entregado\').val(\'\');}">&nbsp;&nbsp;
				No Entregado&nbsp;&nbsp;<input type="checkbox" class="chk" id="checknoentregado" value="1" onClick="if(this.checked){$(\'#entregado\').val(0);$(\'#checkentregado\').removeAttr(\'checked\');}else{$(\'#entregado\').val(\'\');}">&nbsp;&nbsp;
			</td></tr>
		</table>
		</div>';
				
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="traeTipoCertificado();validar()"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'certificados.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">ENTREGA DE CERTIFICADO</td></tr>';
		echo '</table>';
		echo '<input type="hidden" name="entregado" id="entregado" value="1">';
		echo '<input type="hidden" name="montoventa" id="montoventa" value="">';
		echo '<input type="hidden" name="ticket" id="ticket" value="">';
		echo '<input type="hidden" name="anio" id="anio" value="">';
		echo '<table>';
		echo '<tr';
		if($EntregaSimple == 1) echo ' style="display:none;"';
		echo '><th align="left">Placa</th><td><input type="text" name="placa" id="placa" class="textField placas" style="font-size:20px" size="10" value="" onKeyUp="if(event.keyCode==13){ /*traeTicket();*/ document.forma.ticket.focus();} else{ this.value = this.value.toUpperCase();}"></td></tr>';
		echo '<tr><th align="left">';
		if($EntregaSimple == 1)
			echo 'Ticket';
		else
			echo 'Ticket';
		echo '</th><td><input type="text" name="nota" id="nota" class="textField enteros" style="font-size:20px" size="10" value="" onKeyUp="if(event.keyCode==13){ traeTicket();} else{ this.value = this.value.toUpperCase();}">&nbsp;&nbsp;<font color="RED">Dar enter para traer el engomado del ticket</font></td></tr>';
		echo '<tr><th align="left">Fecha Ticket</th><td><input type="text" name="fechaticket" id="fechaticket" class="readOnly" style="font-size:20px" size="10" value="" readOnly></td></tr>';
		echo '<tr><th align="left">Placa Ticket</th><td><input type="text" name="placaticket" id="placaticket" class="readOnly" style="font-size:20px" size="10" value="" readOnly></td></tr>';
		echo '<tr><th align="left">Marca</th><td><select name="marca" id="marca"><option value="0">Seleccione</option>';
		$res1=mysql_query("SELECT * FROM cat_marcas WHERE 1 ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['cve'].'">'.$row1['nombre'].'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr';
		if($EntregaSimple == 1) echo ' style="display:none;"';
		echo '><th align="left">Problemas con OBDII</th><td><select name="problema_obdii" id="problema_obdii">';
		foreach($array_nosi as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		/*echo '<tr><th align="left">Tecnico</th><td><select name="tecnico" id="tecnico"><option value="0">Seleccione</option>';
		$res1=mysql_query("SELECT * FROM tecnicos WHERE plaza='".$_POST['plazausuario']."' AND estatus!=1 ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['cve'].'">'.$row1['nombre'].'</option>';
		}
		echo '</select></td></tr>';*/
		echo '<tr';
		if($EntregaSimple == 1) echo ' style="display:none;"';
		echo '><th align="left">Tecnico</th><td>
		<input type="hidden" name="tecnico" value="0" id="tecnico">
		<input type="text" value="" id="clavetecnico" class="textField" style="font-size:20px" size="5" onKeyUp="if(event.keyCode==13){ traeTecnico(\'\');}">
		<input type="text" value="" id="nomtecnico" class="readOnly" style="font-size:20px" size="50" readOnly>
		</td></tr>';
		echo '<tr';
		if($EntregaSimple == 1) echo ' style="display:none;"';
		echo '><th align="left">Linea</th><td><select name="linea" id="linea"><option value="0">Seleccione</option>';
		$res1=mysql_query("SELECT * FROM cat_lineas WHERE plaza='".$_POST['plazausuario']."' ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['cve'].'">'.$row1['nombre'].'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Holograma</th><td><input type="text" name="certificado" id="certificado" class="textField" style="font-size:20px" size="30" value="" onKeyUp="if(event.keyCode==13){ traeTipoCertificado();}" onChange="traeTipoCertificado();"></td></tr>';
		echo '<tr><th align="left">Tipo de Certificado</th><td><select class="readOnly" name="engomado" id="engomado" style="font-size:20px" onChange="muestra_precio()"><option value="0" costo="0"></option>';
		/*$i=0;
		foreach($array_engomado as $k=>$v){
			echo '<option value="'.$k.'" costo="'.$array_engomadoprecio[$k].'"';
			if($row['engomado']==$k) echo ' checked';
			echo '>'.$v.'</option>';
			$i++;
		}*/
		echo '</select><input type="hidden" name="monto" id="monto" value=""></td></tr>';
		echo '</table>';
		
		echo '<script>
				function traeTipoCertificado(){
					if(document.forma.certificado.value!=""){
						$.ajax({
						  url: "certificados.php",
						  type: "POST",
						  async: false,
						  dataType: "json",
						  data: {
							certificado: document.getElementById("certificado").value,
							ticket: document.getElementById("ticket").value,
							anio: document.forma.anio.value,
							ajax: 9,
							plazausuario: document.getElementById("plazausuario").value
						  },
							success: function(data) {
								if(data.error == 1)
								{
									alert(data.mensaje_error);
									document.forma.certificado.value="";
									document.forma.monto.value="0";
									$("#engomado").html(\'<option value="0">Seleccione</option>\');
								}
								else
								{
									document.forma.monto.value=data.valor_engomado;
									$("#engomado").html(data.engomado);
									if("'.$EntregaSimple.'" == "1"){
										validar();
									}
								}
							}
						});
					}
					else{
						document.forma.monto.value="0";
						$("#engomado").html(\'<option value="0">Seleccione</option>\');
					}
				}

				function muestra_precio(){
					monto_verificacion = $("#engomado").find("option:selected").attr("costo");
					document.forma.monto.value = monto_verificacion;
				}

				$("#dialog").dialog({ 
					bgiframe: true,
					autoOpen: false,
					modal: true,
					width: 600,
					height: 200,
					autoResize: true,
					position: "center",
					beforeClose: function( event, ui ) {
						$(".chk").removeAttr("checked");
					},
					buttons: {
						"Aceptar": function(){ 
							if(!$(".chk").is(":checked")){
								alert("Necesita indicar si fue entregado el certificado");
							}
							else{
								if(document.forma.entregado.value==1)
									resp = confirm("Esta seguro de que fue entregado el certificado");
								else
									resp = confirm("Esta seguro de que no fue entregado el certificado");
								if(resp){
									$(this).dialog("close"); 
									validar();
								}
							}
						},
						"Cerrar": function(){ 
							$(this).dialog("close"); 
						}
					},
				}); 
				
				function validar(){
					$(\'#panel\').show();
					if(document.forma.ticket.value==""){
						$("#panel").hide();
						alert("Necesita ingresar un ticket");
					}
					else if(document.forma.placa.value==""){
						$("#panel").hide();
						alert("Necesita ingresar la placa");
					}
					else if(document.forma.placa.value!=document.forma.placaticket.value){
						$("#panel").hide();
						alert("La placa capturada no coincide con la del ticket");
					}
					else if(document.forma.problema_obdii.value==\'all\'){ 
						alert(\'Necesita seleccionar si tiene problema con OBDII\');
						$(\'#panel\').hide(); 
					} 
					else if(document.forma.engomado.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el tipo de certificado");
					}
					else if(document.forma.tecnico.value=="0" && "'.$EntregaSimple.'" != "1"){
						$("#panel").hide();
						alert("Necesita seleccionar un tecnico");
					}
					else if(document.forma.marca.value=="0"){
						$("#panel").hide();
						alert("Necesita ingresar la marca");
					}
					else if(document.forma.linea.value=="0" && "'.$EntregaSimple.'" != "1"){
						$("#panel").hide();
						alert("Necesita seleccionar una linea");
					}
					else if(document.forma.certificado.value==""){
						$("#panel").hide();
						alert("Necesita ingresar el holograma");
					}
					else if($.trim(document.forma.certificado.value).length>10){
						$("#panel").hide();
						alert("Error en el holograma");
					}
					else{
						validarCertificado();
						//atcr("certificados.php","",2,0);
					}
				}
				
				function validarCertificado(){
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto.open("POST","certificados.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=3&ticket="+document.getElementById("ticket").value+"&anio="+document.getElementById("anio").value+"&engomado="+document.getElementById("engomado").value+"&certificado="+document.getElementById("certificado").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value);
						objeto.onreadystatechange = function()
						{
							if (objeto.readyState==4)
							{
								if(objeto.responseText==1){
									alert("El holograma ya esta capturado");
									$("#panel").hide();
								}
								else if(objeto.responseText==2){
									alert("El holograma esta cancelado");
									$("#panel").hide();
								}
								else if(objeto.responseText==3){
									alert("El holograma no existe");
									$("#panel").hide();
								}
								else if(objeto.responseText==4){
									alert("El holograma no esta activo");
									$("#panel").hide();
								}
								else if(objeto.responseText==5){
									alert("El ticket no es de verificacion 00");
									$("#panel").hide();
								}
								else{
									/*if((document.forma.montoventa.value/1) < (document.forma.monto.value/1) && (document.forma.montoventa.value/1) > 0){
										if(confirm("Esta entregando un certificado con costo mayor al comprado desea continuar?")){
											atcr("certificados.php","",2,0);
										}
										else{
											$("#panel").hide();
										}
									}
									else{*/
										atcr("certificados.php","",2,0);
									//}
								}
							}
						}
					}
				}
				
				function traeTicket_r(){
					if(document.forma.placa.value==""){
						document.forma.engomado.value="";
						document.forma.nomengomado.value="";
						document.forma.ticket.value="";
						document.forma.fechaticket.value="";
						document.forma.montoventa.value="";
					}
					else{
						objeto=crearObjeto();
						if (objeto.readyState != 0) {
							alert("Error: El Navegador no soporta AJAX");
						} else {
							objeto.open("POST","certificados.php",true);
							objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
							objeto.send("ajax=2&placa="+document.getElementById("placa").value+"&plazausuario="+document.getElementById("plazausuario").value);
							objeto.onreadystatechange = function()
							{
								if (objeto.readyState==4)
								{
									datos=objeto.responseText.split("|");
									if(datos[0]=="-1"){
										alert("La placa no tiene ningun certificado por entregar");
										document.forma.placa.value="";
										document.forma.ticket.value="";
										document.forma.fechaticket.value="";
										document.forma.montoventa.value="";
										document.forma.nota.value="";
									}
									else if(datos[0]=="-5"){
										alert("El ticket es una reposición");
										document.forma.placa.value="";
										document.forma.ticket.value="";
										document.forma.fechaticket.value="";
										document.forma.montoventa.value="";
										document.forma.nota.value="";
									}
									else if(datos[0]=="-2"){
										alert("La placa ya tiene certificado entregado el dia "+datos[3]+" con holograma "+datos[2]);
										document.forma.placa.value="";
										document.forma.ticket.value="";
										document.forma.fechaticket.value="";
										document.forma.montoventa.value="";
										document.forma.nota.value="";
									}
									else if(datos[0]=="0"){
										alert("El ticket no existe");
										document.forma.placa.value="";
										document.forma.ticket.value="";
										document.forma.fechaticket.value="";
										document.forma.montoventa.value="";
										document.forma.nota.value="";
									}
									else{
										document.forma.ticket.value=datos[0];
										document.forma.fechaticket.value=datos[1];
										document.forma.montoventa.value=datos[2];
									}
								}
							}
						}
					}
				}
				
				function traeTicket(){
					if(document.forma.nota.value==""){
						document.forma.engomado.value="";
						document.forma.anio.value="";
						document.forma.nomengomado.value="";
						document.forma.fechaticket.value="";
						document.forma.montoventa.value="";
						document.forma.placaticket.value="";
						document.forma.ticket.value="";
						if("'.$EntregaSimple.'" == "1") document.getElementById("placa").value="";
						document.forma.nota.focus();
					}
					else{
						objeto=crearObjeto();
						if (objeto.readyState != 0) {
							alert("Error: El Navegador no soporta AJAX");
						} else {
							objeto.open("POST","certificados.php",true);
							objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
							objeto.send("ajax=2&placa="+document.getElementById("placa").value+"&nota="+document.getElementById("nota").value+"&plazausuario="+document.getElementById("plazausuario").value);
							objeto.onreadystatechange = function()
							{
								if (objeto.readyState==4)
								{
									datos=objeto.responseText.split("|");
									if(datos[0]=="-1"){
										alert("El ticket ya fue entregado, en "+datos[1]);
										document.forma.placaticket.value="";
										document.forma.ticket.value="";
										document.forma.fechaticket.value="";
										document.forma.montoventa.value="";
										document.forma.anio.value="";
										document.forma.nota.focus();
										if("'.$EntregaSimple.'" == "1") document.getElementById("placa").value="";
									}
									else if(datos[0]=="-2"){
										alert("El ticket esta cancelado");
										document.forma.placaticket.value="";
										document.forma.ticket.value="";
										document.forma.fechaticket.value="";
										document.forma.montoventa.value="";
										document.forma.anio.value="";
										document.forma.nota.focus();
										if("'.$EntregaSimple.'" == "1") document.getElementById("placa").value="";
									}
									else if(datos[0]=="-3"){
										alert("El ticket es 2014");
										document.forma.placaticket.value="";
										document.forma.ticket.value="";
										document.forma.fechaticket.value="";
										document.forma.montoventa.value="";
										document.forma.anio.value="";
										document.forma.nota.focus();
										if("'.$EntregaSimple.'" == "1") document.getElementById("placa").value="";
									}
									else if(datos[0]=="0"){
										alert("El ticket no existe");
										document.forma.placaticket.value="";
										document.forma.ticket.value="";
										document.forma.fechaticket.value="";
										document.forma.montoventa.value="";
										document.forma.anio.value="";
										document.forma.nota.focus();
										if("'.$EntregaSimple.'" == "1") document.getElementById("placa").value="";
									}
									else if(datos[0]=="-4"){
										alert("El ticket fue devuelto por su totalidad");
										document.forma.placaticket.value="";
										document.forma.ticket.value="";
										document.forma.fechaticket.value="";
										document.forma.montoventa.value="";
										document.forma.anio.value="";
										document.forma.nota.focus();
										if("'.$EntregaSimple.'" == "1") document.getElementById("placa").value="";
									}
									else if(datos[0]=="-5"){
										alert("El ticket es reposición no se puede entregar certificado");
										document.forma.placaticket.value="";
										document.forma.ticket.value="";
										document.forma.fechaticket.value="";
										document.forma.montoventa.value="";
										document.forma.anio.value="";
										document.forma.nota.focus();
										if("'.$EntregaSimple.'" == "1") document.getElementById("placa").value="";
									}
									else if(datos[0]=="-6"){
										alert("El ticket esta bloqueado");
										document.forma.placaticket.value="";
										document.forma.ticket.value="";
										document.forma.fechaticket.value="";
										document.forma.montoventa.value="";
										document.forma.anio.value="";
										document.forma.nota.focus();
										if("'.$EntregaSimple.'" == "1") document.getElementById("placa").value="";
									}
									else{
										document.forma.ticket.value=datos[0];
										document.forma.fechaticket.value=datos[1];
										document.forma.montoventa.value=datos[2];
										document.forma.placaticket.value=datos[3];
										document.forma.anio.value=datos[4];
										if("'.$EntregaSimple.'" == "1"){
											document.getElementById("placa").value=datos[3];
											document.forma.certificado.focus();
										}
									}
								}
							}
						}
					}
				}

				function traeTecnico(num){
					$.ajax({
					  url: "certificados.php",
					  type: "POST",
					  async: false,
					  dataType: "json",
					  data: {
						tecnico: document.getElementById("clavetecnico"+num).value,
						ajax: 20,
						plazausuario: document.getElementById("plazausuario").value
					  },
						success: function(data) {
							if(data.error == 1)
							{
								document.getElementById("clavetecnico"+num).value="";
								document.getElementById("nomtecnico"+num).value="";
								document.getElementById("tecnico"+num).value="0";
								alert(data.mensaje_error);
							}
							else
							{
								document.getElementById("nomtecnico"+num).value=data.nombre;
								document.getElementById("tecnico"+num).value=data.cve;
							}
						}
					});
				}
				
			</script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
		if($PlazaLocal!=1)
			echo '<td><a href="#" onClick="atcr(\'certificados.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="atcr(\'certificados.php\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>';
		if($_POST['cveusuario']==1)
			echo '<td><a href="#" onClick="atcr(\'certificados.php\',\'_blank\',\'200\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Desglose</td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="atcr(\'certificados.php\',\'_blank\',\'102\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Excell</td><td>&nbsp;</td>';
		echo '
			 </tr>';
		echo '</table>';
		echo '<table width="100%"><tr><td valign="top" width="50%">';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Ticket</td><td><input type="text" name="ticket" id="ticket" size="10" class="textField" value=""></td></tr>';
			echo '<tr><td>Placa</td><td><input type="text" name="placa" id="placa" size="10" class="textField" value="" onKeyUp="if(event.keyCode==13){ traeRegistro();}else{this.value = this.value.toUpperCase();}"></td></tr>';
		echo '<tr';
	if($_POST['cveusuario'] != 1) echo ' style="display:none;"';
	echo '><td>Ticket</td><td><input type="text" name="nota" id="nota" size="10" class="textField" value=""></td></tr>';
		echo '<tr><td>Tipo de Certificado</td><td><select name="engomado" id="engomado"><option value="">Todos</option>';
		foreach($array_engomado as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Holograma</td><td><input type="text" name="certificado" id="certificado" size="30" class="textField" value=""></td></tr>';
		echo '<tr><td>Linea</td><td><select name="linea" id="linea"><option value="">Seleccione</option>';
		$res1=mysql_query("SELECT * FROM cat_lineas WHERE plaza='".$_POST['plazausuario']."' ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['cve'].'">'.$row1['nombre'].'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">Todos</option>';
		$res=mysql_query("SELECT b.cve,b.usuario FROM certificados a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY b.cve ORDER BY b.usuario");
		while($row=mysql_fetch_array($res)){
			echo '<option value="'.$row['cve'].'">'.$row['usuario'].'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="">Todos</option>';
		foreach($array_estatus as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Entreado</td><td><select name="entregado" id="entregado"><option value="all" selected>Todos</option>';
		foreach($array_nosi as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr style="display:none;"><td>Problemas con OBDII</td><td><select name="problema_obdii" id="problema_obdii"><option value="all" selected>Todos</option>';
		foreach($array_nosi as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>A&ntilde;o Certificacion</td><td><select name="anio" id="anio"><option value="all" selected>Todos</option>';
		foreach($array_anios as $k=>$v){
				echo '<option value="'.$k.'"';
				echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Tipo Venta</td><td><select name="tipo_venta" id="tipo_venta"><option value="all" selected>Todos</option>';
		foreach($array_tipo_venta as $k=>$v){
				echo '<option value="'.$k.'"';
				echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Tipo Pago</td><td><select name="tipo_pago" id="tipo_pago"><option value="all" selected>Todos</option>';
		foreach($array_tipo_pago as $k=>$v){
				echo '<option value="'.$k.'"';
				echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;';
		echo '><td>Mostrar</td><td><select name="mostrar" id="mostrar"><option value="0" selected>Todos</option>';
		echo '<option value="1">Sin Compra</option>';
		echo '</select></td></tr>';
		echo '<tr><td>Tipo Cliente</td><td><select name="tipo_cliente" id="tipo_cliente"><option value="all" selected>Todos</option>';
		echo '<option value="-1">Particulares</option>';
		echo '<option value="0">Talleres</option>';
		echo '<option value="1">Agencias</option>';
		echo '</select></td></tr>';
		echo '</table>';
		echo '</td><td width="50%" valign="top" id="capacorte"></td></tr></table>';
		echo '<br>';
		if($PlazaLocal==1)echo '<h2>La captura de la entrega de certificados en esta plaza es de forma local</h2>';
		//echo '<h2>Las modificaciones a la operación del sistema se deberán enviar al correo: <font color="BLUE">contacto@hilosnegros.com.mx</font><!-- con el L.C. Arturo Galicia de la O--></h2>';
		//Listado
		$res = mysql_query("SELECT mensaje FROM etiquetas_plazas WHERE estatus='A' AND plazas LIKE '%|".$_POST['plazausuario']."|%' ORDER BY cve DESC LIMIT 1");
		$row = mysql_fetch_array($res);
		if($row['mensaje']!=''){
			echo '<p style="color:#FF0000;font-size:16px;">'.$row['mensaje'].'</p>';
		}
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
			objeto.open("POST","certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&tipo_cliente="+document.getElementById("tipo_cliente").value+"&tipo_pago="+document.getElementById("tipo_pago").value+"&problema_obdii="+document.getElementById("problema_obdii").value+"&mostrar="+document.getElementById("mostrar").value+"&tipo_venta="+document.getElementById("tipo_venta").value+"&anio="+document.getElementById("anio").value+"&btn="+btn+"&estatus="+document.getElementById("estatus").value+"&entregado="+document.getElementById("entregado").value+"&ticket="+document.getElementById("ticket").value+"&certificado="+document.getElementById("certificado").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&placa="+document.getElementById("placa").value+"&engomado="+document.getElementById("engomado").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&linea="+document.getElementById("linea").value+"&nota="+document.getElementById("nota").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					datos = objeto.responseText.split("|*|");
					document.getElementById("capacorte").innerHTML = datos[1];
					document.getElementById("Resultados").innerHTML = datos[0];
				}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}

	function guardarPlaca(folio){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=7&folio="+folio+"&placa="+document.getElementById("placa_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros(1);}
			}
		}
	}
	
	function guardarCertificado(folio){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=4&folio="+folio+"&certificado="+document.getElementById("certificado_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					if(objeto.responseText != "") alert(objeto.responseText);
					buscarRegistros(1);
				}
			}
		}
	}
	
	function guardarEngomado(folio){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=5&folio="+folio+"&engomado="+document.getElementById("engomado_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros(1);}
			}
		}
	}
	
	function guardarFecha(folio, fecha_anterior){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=6&folio="+folio+"&fecha_anterior="+fecha_anterior+"&fecha="+document.getElementById("fechan_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros(1);}
			}
		}
	}

	function guardarTecnico(folio){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=8&campo=tecnico&folio="+folio+"&valor="+document.getElementById("tecnico_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros(1);}
			}
		}
	}

	function guardarLineas(folio){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=8&campo=linea&folio="+folio+"&valor="+document.getElementById("linea_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
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

if($cvecobro>0){
		echo '<script>atcr(\'cobro_engomado.php\',\'_blank\',\'101\','.$cvecobro.');</script>';
	}
?>

