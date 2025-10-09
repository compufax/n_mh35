<?php 

include ("main.php"); 


$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$res = mysql_query("SELECT * FROM motivos_cancelacion_certificados");
while($row=mysql_fetch_array($res)){
	$array_motivos[$row['cve']]=$row['nombre'];
}

if($_POST['cmd'] == 1)
	$res = mysql_query("SELECT * FROM anios_certificados ORDER BY nombre DESC LIMIT 2");
else
	$res = mysql_query("SELECT * FROM anios_certificados  ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
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

$res=mysql_query("SELECT local, validar_certificado FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];
$ValidarCertificados=$row[1];
$ValidarCertificados=1;

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);
$array_engomado = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND plazas like '%|".$_POST['plazausuario']."|%' AND entrega=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_datos_engomado[] = $row;
}
$re=mysql_query("SELECT a.cve, CONCAT(a.numero, ' ', a.nombre) as nombre_plaza, b.rfc, b.nombre FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id=2 ORDER BY a.tipo_plaza,a.lista,a.numero");
while($row=mysql_fetch_array($re)){
	$array_rfcempresa[$row['rfc']]=$row['nombre'];
	$array_datos_plaza[] = $row;
}
$res = mysql_query("SELECT * FROM certificados_cancelados where plaza='".$_POST['plazausuario']."' group by usuario");
while($row=mysql_fetch_array($res)){
	$array_usuario_[$row['usuario']]=$array_usuario[$row['usuario']];
}
if($_POST['cmd']==100){
	include('fpdf153/fpdf.php');
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
		$select= " SELECT * FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."'";
		if($_POST['certificado']!=""){ 
			$select.=" AND CAST(certificado as UNSIGNED)='".intval($_POST['certificado'])."' "; 
		}
		else{
			$select.=" AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
			if ($_POST['motivo']!="all") { $select.=" AND motivo='".$_POST['motivo']."' "; }
//			if ($_POST['engomado']!="all") { $select.=" AND engomado='".$_POST['engomado']."' "; }
			if ($_POST['engomado']!="") { $select .= " AND engomado IN (".$_POST['engomado'].")"; }
			
			if ($_POST['anio']!="all") { $select.=" AND anio='".$_POST['anio']."' "; }
			//if ($_POST['certificado']!="") { $select.=" AND certificado='".$_POST['certificado']."' "; }
			
		}
		if ($_POST['usuario']!="") { $select.=" AND usuario='".$_POST['usuario']."' "; }
		$select.=" ORDER BY cve DESC";
		//if($_POST['btn']==0) $select.=" LIMIT 1";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Motivo</th><th>Tipo de Certificado</th><th>A&ntilde;o</th><th>Holograma</th><th>Placa</th><th>Tecnico</th><th>Linea</th><th>Observaciones</th><th>Usuario</th>';
			if($_POST['cveusuario']==1){
				echo '<th>Auditoria</th>';
			}
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center">';
				if($row['estatus']=='C'){
					$row['certificado']='';
					echo 'Cancelado<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
				}
				else{
					if(nivelUsuario()>2 && $PlazaLocal != 1 && ($row['fecha']==fechaLocal() || $_POST['cveusuario']==1 || $permite_editar == 1))
						echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el registro\')) atcr(\'cancelar_certificados.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
					if($_POST['cveusuario']==1){
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'cancelar_certificados.php\',\'\',\'21\','.$row['cve'].')"><img src="images/historial.gif" border="0" title="Agregar Empleados '.$row['cve'].'"></a>';
					}
				}
				echo '</td>';
				if($_POST['cveusuario']==1 && $row['usu_auditoria']>0)
				{
					echo '<td align="center"><font color="GREEN">'.htmlentities($row['cve']).'</font></td>';
				}
				else{
					echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				}
				//echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				if($permite_editar==1 && $PlazaLocal != 1){
					echo '<td align="center"><input type="text" id="fechan_'.$row['cve'].'" class="textField" size="23" value="'.$row['fecha'].' '.$row['hora'].'">&nbsp;<span style="cursor:pointer;" onClick="displayCalendar(document.getElementById(\'fechan_'.$row['cve'].'\'),\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></span><br>
					<input type="button" value="Guardar" onClick="guardarFecha('.$row['cve'].',\''.$row['fecha'].' '.$row['hora'].'\')"></td>';
				}
				else{
					echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				}
				echo '<td align="left">'.htmlentities(utf8_encode($array_motivos[$row['motivo']])).'</td>';
				
				echo '<td align="left">'.htmlentities(utf8_encode($array_engomado[$row['engomado']])).'</td>';
				if($_POST['cveusuario']==1){
					echo '<td align="center"><select id="anio_'.$row['cve'].'">';
					foreach($array_anios as $k=>$v){
						echo '<option value="'.$k.'"';
						if($k==$row['anio']) echo ' selected';
						echo '>'.$v.'</option>';
					}
					echo '</select><br>
					<input type="button" value="Guardar" onClick="guardarAnio('.$row['cve'].', \''.$row['anio'].'\')"></td>';
				}
				else{
					echo '<td align="left">'.htmlentities(utf8_encode($array_anios[$row['anio']])).'</td>';
				}
				echo '<td align="center">'.htmlentities($row['certificado']).'</td>';
				echo '<td align="center">'.htmlentities($row['placa']).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($array_personal[$row['tecnico']])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($array_lineas[$row['linea']])).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($row['obs'])).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				if($_POST['cveusuario']==1){
					echo '<td align="center">';
					if($row['estatus']=='C'){
						echo '&nbsp;';
					}
					else{
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'cancelar_certificados.php\',\'\',\'11\','.$row['cve'].')"><img src="images/finalizar.gif" border="0" title="Auditar '.$row['cve'].'"></a>';
					}
					echo '</td>';
				}
				echo '</tr>';
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
		exit();	
}	

if($_POST['ajax']==6){
	$opc = explode(" ", $_POST['fecha']);
	mysql_query("UPDATE certificados_cancelados SET fecha='".$opc[0]."',hora='".$opc[1]."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
	mysql_query("INSERT historial SET menu='46',cveaux='".$_POST['folio']."',fecha='".fechaLocal()." ".horaLocal()."',obs='".$_POST['plazausuario']."',
			dato='Fecha Entrega',nuevo='".$_POST['fecha']."',anterior='".$_POST['fecha_anterior']."',arreglo='',usuario='".$_POST['cveusuario']."'");
	exit();
}

if($_POST['ajax']==7){
	mysql_query("UPDATE certificados_cancelados SET anio='".$_POST['anio']."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
	mysql_query("INSERT historial SET menu='46',cveaux='".$_POST['folio']."',fecha='".fechaLocal()." ".horaLocal()."',obs='".$_POST['plazausuario']."',
			dato='Año Certificacion',nuevo='".$_POST['anio']."',anterior='".$_POST['anio_anterior']."',arreglo='array_anios',usuario='".$_POST['cveusuario']."'");
	exit();
}

if($_POST['ajax']==9){
	$res = mysql_query("SELECT * FROM usuarios WHERE cve='".$_POST['cveusuario']."'");
	$row = mysql_fetch_array($res);
	$permite_editar = $row['permite_editar'];
	if($permite_editar== 1){
		echo "0";
		exit();
	}
	$res = mysql_query("SELECT a.cve FROM cobro_engomado a LEFT JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket AND b.estatus != 'C'
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.placa = '".$_POST['placa']."' AND a.anio='".$_POST['anio']."' AND a.estatus!='C' AND ISNULL(b.cve) ORDER BY a.cve DESC");
	if($row = mysql_fetch_array($res)){
		echo "0";
	}
	else{
		echo "1";
	}
	exit();
}

top($_SESSION);



/*** ACTUALIZAR REGISTRO  **************************************************/

if($_POST['cmd']==3){
	mysql_query("UPDATE certificados_cancelados SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$res = mysql_query("SELECT * FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	mysql_query("UPDATE compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra SET b.estatus=0 WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$row['engomado']."' AND b.folio='".intval($row['certificado'])."' AND b.estatus=1");
	
	$_POST['cmd']=0;
}

if($_POST['cmd']==12){
	mysql_query("UPDATE certificados_cancelados SET motivo_auditoria='".$_POST['motivo']."', placa_auditoria='".$_POST['placa']."',
		tecnico_auditoria='".$_POST['tecnico']."',linea_auditoria='".$_POST['linea']."',obs_auditoria='".$_POST['obs']."',
		usu_auditoria='".$_POST['cveusuario']."',fecha_auditoria=NOW() WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

if ($_POST['cmd']==22) {
	
	
		$insert = " UPDATE certificados_cancelados
							SET 
							cobro_tecnico='".$_POST['cobro_tecnico']."',cant_empleados='".count($_POST['personales'])."'
					WHERE plaza = '".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'";
		mysql_query($insert);
		$idcancelacion = $_POST['reg'];
		if($_POST['cobro_tecnico']==1){
			foreach($_POST['personales'] as $personal){
				mysql_query("INSERT certificados_cancelados_cobro SET plaza='".$_POST['plazausuario']."', cancelacion='".$idcancelacion."', personal='".$personal."'") or die(mysql_error());
			}
		}
			
	$_POST['cmd']=0;
}

if($_POST['cmd']==32){
	for($holograma=$_POST['certificado']; $holograma<=$_POST['certificado2']; $holograma++){
		$res = mysql_query("SELECT cve, fecha FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$_POST['engomado']."' AND certificado ='".intval($holograma)."' AND estatus!='C'");
		if(mysql_num_rows($res)==0){
			$res = mysql_query("SELECT cve, fecha FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$_POST['engomado']."' AND certificado='".intval($holograma)."' AND estatus!='C'");
			if(mysql_num_rows($res)==0){
				$res = mysql_query("SELECT b.cve FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$_POST['engomado']."' AND b.folio='".intval($holograma)."' AND a.estatus!='C'");
				if(mysql_num_rows($res)>0){
					$ticket = 0;
					$fechaticket='0000-00-00 00:00:00';
					
					$insert = " INSERT certificados_cancelados
										SET 
										plaza = '".$_POST['plazausuario']."',fecha='".$_POST['fecha']."',hora='".horaLocal()."',
										certificado='".$holograma."',motivo='".$_POST['motivo']."',anio='".$_POST['anio']."',
										usuario='".$_POST['cveusuario']."',engomado='".$_POST['engomado']."',estatus='A',
										placa='',obs='".$_POST['obs']."',tecnico='',
										linea='',ticket='$ticket',fechaticket='$fechaticket',
										cobro_tecnico='0',cant_empleados='0'";
					mysql_query($insert);
					$idcancelacion = mysql_insert_id();
					mysql_query("UPDATE compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra SET b.estatus=1 WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$_POST['engomado']."' AND b.folio='".intval($holograma)."' AND a.estatus!='C'");
					
				}
				
			}
			
		}
		
	}

	$_POST['cmd']=0;
}

if ($_POST['cmd']==2) {
	
	$res = mysql_query("SELECT cve, fecha FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$_POST['engomado']."' AND anio='".$_POST['anio']."' AND CAST(certificado AS UNSIGNED)='".intval($_POST['certificado'])."' AND estatus!='C'");
	if(mysql_num_rows($res)==0){
		$res = mysql_query("SELECT cve, fecha FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$_POST['engomado']."' AND anio='".$_POST['anio']."' AND CAST(certificado AS UNSIGNED)='".intval($_POST['certificado'])."' AND estatus!='C'");
		if(mysql_num_rows($res)==0){
			if($_POST['engomado'] == 3 || $_POST['engomado'] == 19)
				$res = mysql_query("SELECT b.cve FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$_POST['engomado']."' AND b.folio='".intval($_POST['certificado'])."' AND a.estatus!='C'");
			else
				$res = mysql_query("SELECT b.cve FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$_POST['engomado']."' AND a.anio='".$_POST['anio']."' AND b.folio='".intval($_POST['certificado'])."' AND a.estatus!='C'");
			if(mysql_num_rows($res)>0 || $ValidarCertificados == 0){
				$ticket = 0;
				$fechaticket='0000-00-00 00:00:00';
				$res = mysql_query("SELECT a.cve,a.fecha,a.hora FROM cobro_engomado a LEFT JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket AND b.estatus != 'C'
				WHERE a.plaza = '".$_POST['plazausuario']."' AND a.placa = '".$_POST['placa']."' AND a.anio='".$_POST['anio']."' AND a.estatus!='C' AND ISNULL(b.cve) ORDER BY a.cve DESC");
				if($row = mysql_fetch_array($res)){
					$ticket = $row['cve'];
					$fechaticket=$row['fecha'].' '.$row['hora'];
				}
				$insert = " INSERT certificados_cancelados
									SET 
									plaza = '".$_POST['plazausuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',
									certificado='".$_POST['certificado']."',motivo='".$_POST['motivo']."',anio='".$_POST['anio']."',
									usuario='".$_POST['cveusuario']."',engomado='".$_POST['engomado']."',estatus='A',
									placa='".$_POST['placa']."',obs='".$_POST['obs']."',tecnico='".$_POST['tecnico']."',
									linea='".$_POST['linea']."',ticket='$ticket',fechaticket='$fechaticket',
									cobro_tecnico='".$_POST['cobro_tecnico']."',cant_empleados='".count($_POST['personales'])."'";
				mysql_query($insert);
				$idcancelacion = mysql_insert_id();
				mysql_query("UPDATE compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra SET b.estatus=1 WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$_POST['engomado']."' AND b.folio='".intval($_POST['certificado'])."' AND a.estatus!='C'");
				if($_POST['cobro_tecnico']==1){
					foreach($_POST['personales'] as $personal){
						mysql_query("INSERT certificados_cancelados_cobro SET plaza='".$_POST['plazausuario']."', cancelacion='".$idcancelacion."', personal='".$personal."'") or die(mysql_error());
					}
				}
			}
			else{
				echo '<script>alert("El holograma no existe o no esta activo");</script>';
			}
		}
		else{
			$row = mysql_fetch_array($res);
			echo '<script>alert("El holograma ya esta entregado en el folio '.$row['cve'].' del dia '.$row['fecha'].'");</script>';
		}
	}
	else{
		$row = mysql_fetch_array($res);
		echo '<script>alert("El holograma ya esta cancelado en el folio '.$row['cve'].' del dia '.$row['fecha'].'");</script>';
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

if($_POST['cmd']==11){
	$res = mysql_query("SELECT * FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	echo '<table>';
		echo '<tr>';
			echo '<td><a href="#" onClick="
				$(\'#panel\').show();
				if(document.forma.motivo.value==\'all\'){ 
					alert(\'Necesita seleccionar el motivo de cancelacion\');
					$(\'#panel\').hide(); 
				} 
				else if(document.forma.tecnico.value==\'\'){ 
					alert(\'Necesita ingresar el tecnico\'); 
					$(\'#panel\').hide();
				} 
				else if(document.forma.linea.value==\'\'){ 
					alert(\'Necesita ingresar la linea\'); 
					$(\'#panel\').hide();
				} 
				else if($.trim(document.forma.placa.value)==\'\'){ 
					alert(\'Necesita ingresar la placa\'); 
					$(\'#panel\').hide();
				} 
				else{ 
					atcr(\'cancelar_certificados.php\',\'\',\'12\',\''.$_POST['reg'].'\');
				}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'cancelar_certificados.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
		echo '
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Motivo Cancelacion</td><td><select name="motivo" id="motivo"><option value="all">Seleccione</option>';
		foreach($array_motivos as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row['motivo_auditoria'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>A&ntilde;o</td><td><select name="anio" id="anio">';
		foreach($array_anios as $k=>$v){
			if($row['anio']==$k)
				echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Holograma</td><td><input type="text" name="certificado" id="certificado" size="30" class="readOnly" value="'.$row['certificado'].'" readOnly></td></tr>';
		echo '<tr><td>Tipo de Certificado</td><td><select name="engomado" id="engomado">';
		foreach($array_engomado as $k=>$v){
			if($row['engomado']==$k)
				echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Placa</td><td><input type="text" name="placa" id="placa" size="10" class="textField" value="'.$row['placa_auditoria'].'"></td></tr>';
		echo '<tr><td>Tecnico</td><td><select name="tecnico" id="tecnico"><option value="all">Seleccione</option>';
		foreach($array_personal as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row['tecnico_auditoria'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Linea</td><td><select name="linea" id="linea"><option value="all">Seleccione</option>';
		foreach($array_lineas as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row['linea_auditoria'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Observaciones</td><td><textarea name="obs" id="obs" rows="3" cols="50"  class="textField">'.$row['obs_auditoria'].'</textarea></td></tr>';
		echo '<tr style="display:none;"><td><input type="text" name="noenter" value=""></td></tr>';
		echo '</table>';


}

if($_POST['cmd']==21){
	$optionspersonal='';
	$res = mysql_query("SELECT * FROM personal WHERE plaza = '".$_POST['plazausuario']."' ORDER BY nombre");
	while($row = mysql_fetch_array($res)){
		$optionspersonal.='<option value="'.$row['cve'].'">'.$row['nombre'].'</option>';
	}
	$res = mysql_query("SELECT * FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	echo '<table>';
		echo '<tr>';
		if(nivelUsuario()>1 && $PlazaLocal!=1)
			echo '<td><a href="#" onClick="
					atcr(\'cancelar_certificados.php\',\'\',\'22\',\''.$_POST['reg'].'\');
				"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'cancelar_certificados.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
		echo '
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Motivo Cancelacion</td><td><select name="motivo" id="motivo">';
		foreach($array_motivos as $k=>$v){
			if($k==$row['motivo'])
				echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>A&ntilde;o</td><td><select name="anio" id="anio">';
		foreach($array_anios as $k=>$v){
			if($k==$row['anio'])
				echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Holograma</td><td><input type="text" name="certificado" id="certificado" size="30" class="textField" value="'.$row['certificado'].'" readOnly></td></tr>';
		echo '<tr><td>Tipo de Certificado</td><td><select name="engomado" id="engomado">';
		foreach($array_engomado as $k=>$v){
			if($k==$row['engomado'])
				echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Placa</td><td><input type="text" name="placa" id="placa" size="10" class="textField" value="'.$row['placa'].'" readOnly></td></tr>';
		echo '<tr><td>Tecnico</td><td><select name="tecnico" id="tecnico">';
		foreach($array_personal as $k=>$v){
			if($row['tecnico']==$k)
				echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Linea</td><td><select name="linea" id="linea">';
		foreach($array_lineas as $k=>$v){
			if($row['linea']==$k)
				echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Observaciones</td><td><textarea name="obs" id="obs" rows="3" cols="50"  class="textField" readOnly>'.$row['obs'].'</textarea></td></tr>';
		echo '<tr><td>Cobro a tecnico</td><td><input type="checkbox" id="cobro_tecnico" name="cobro_tecnico" value="1" onClick="
			if(this.checked){
				$(\'#trempleados\').show();
			}
			else{
				$(\'#trempleados\').hide();
			}
		"></td></tr>';
		echo '<tr style="display:none;" id="trempleados"><td>Empleado(s)<br>
		<span style="cursor:pointer;" onClick="agregarpersonal()"><font color="BLUE">Agregar</font></span></td><td><table id="tablepersonal"><tr><td><select name="personales[]"><option value="0">Seleccione</option>'.$optionspersonal.'</select></td><td><span style="cursor:pointer" onClick="$(this).parents(\'tr:first\').remove();"><font color="BLUE">Quitar</font></span></td></tr>';
		echo '<tr style="display:none;"><td><input type="text" name="noenter" value=""></td></tr>';
		echo '</table>';
		echo '<script>
		
				function agregarpersonal(){
					$("#tablepersonal").append(\'<tr><td><select name="personales[]"><option value="0">Seleccione</option>'.$optionspersonal.'</select></td><td><span style="cursor:pointer" onClick="$(this).parents(\\\'tr:first\\\').remove();"><font color="BLUE">Quitar</font></span></td></tr>\');
				}
				
				
				function validarPlaca(){
					regresar = true;
					$.ajax({
					  url: "cancelar_certificados.php",
					  type: "POST",
					  async: false,
					  data: {
						placa: document.getElementById("placa").value,
						cveusuario: document.getElementById("cveusuario").value,
						anio: document.forma.anio.value,
						ajax: 9,
						plazausuario: document.getElementById("plazausuario").value
					  },
						success: function(data) {
							if(data == "1")
							{
								regresar = false;
							}
						}
					});
					return regresar;
				}

				function traeTipoCertificado(){
					if(document.forma.certificado.value!=""){
						$.ajax({
						  url: "certificados.php",
						  type: "POST",
						  async: false,
						  dataType: "json",
						  data: {
							certificado: document.getElementById("certificado").value,
							anio: document.forma.anio.value,
							ajax: 9,
							plazausuario: document.getElementById("plazausuario").value
						  },
							success: function(data) {
								if(data.error == 1)
								{
									alert(data.mensaje_error);
									document.forma.certificado.value="";
									$("#engomado").html(\'<option value="0">Seleccione</option>\');
								}
								else
								{
									$("#engomado").html(data.engomado);
								}
							}
						});
					}
					else{
						$("#engomado").html(\'<option value="0">Seleccione</option>\');
					}
				}
			</script>';

}

if($_POST['cmd']==31){
	echo '<table>';
		echo '<tr>';
		if(nivelUsuario()>1 && $PlazaLocal!=1)
			echo '<td><a href="#" onClick="
				$(\'#panel\').show();
				if(document.forma.motivo.value==\'all\'){ 
					alert(\'Necesita seleccionar el motivo de cancelacion\');
					$(\'#panel\').hide(); 
				} 
				else if(document.forma.anio.value==\'all\'){ 
					alert(\'Necesita seleccionar el año de certificado\'); 
					$(\'#panel\').hide();
				} 
				else if(document.forma.engomado.value==\'all\'){ 
					alert(\'Necesita seleccionar el tipo de certificado\'); 
					$(\'#panel\').hide();
				} 
				else if(document.forma.certificado.value==\'\'){ 
					alert(\'Necesita ingresar el holograma inicial\'); 
					$(\'#panel\').hide();
				} 
				else if(document.forma.certificado2.value==\'\'){ 
					alert(\'Necesita ingresar el holograma final\'); 
					$(\'#panel\').hide();
				} 
				else if(document.forma.engomado.value==\'\'){ 
					alert(\'Necesita ingresar el tipo de certificado\'); 
					$(\'#panel\').hide();
				} 
				else{ 
					atcr(\'cancelar_certificados.php\',\'\',\'32\',\'0\');
				}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'cancelar_certificados.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
		echo '
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha</td><td><input type="text" name="fecha" id="fecha" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Motivo Cancelacion</td><td><select name="motivo" id="motivo"><option value="all">Seleccione</option>';
		foreach($array_motivos as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>A&ntilde;o</td><td><select name="anio" id="anio"><option value="all">Selccione</option>';
		foreach($array_anios as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Tipo de Certificado</td><td><select name="engomado" id="engomado"><option value="all">Selccione</option>';
		foreach($array_engomado as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Holograma Inicial</td><td><input type="text" name="certificado" id="certificado" size="30" class="textField" value="" onKeyUp="calcular()"></td></tr>';
		echo '<tr><td>Holograma Final</td><td><input type="text" name="certificado2" id="certificado2" size="30" class="textField" value="" onKeyUp="calcular()"></td></tr>';
		echo '<tr><td>Cantidad</td><td><input type="text" class="readOnly" id="cant" value="" readOnly></td></tr>';
		
		echo '<tr><td>Observaciones</td><td><textarea name="obs" id="obs" rows="3" cols="50"  class="textField"></textarea></td></tr>';

		echo '<tr style="display:none;"><td><input type="text" name="noenter" value=""></td></tr>';
		echo '</table>';
		echo '<script>
				function calcular(){
					if((document.forma.certificado2.value/1) > 0 && (document.forma.certificado.value/1) > 0){
						var cant = document.forma.certificado2.value/1 + 1 - document.forma.certificado.value/1;
						document.getElementById("cant").value=cant;
					}
					else{
						document.getElementById("cant").value="";
					}
				}
				</script>';
}

if($_POST['cmd']==1){
	$optionspersonal='';
	$res = mysql_query("SELECT * FROM personal WHERE plaza = '".$_POST['plazausuario']."' ORDER BY nombre");
	while($row = mysql_fetch_array($res)){
		$optionspersonal.='<option value="'.$row['cve'].'">'.$row['nombre'].'</option>';
	}
	echo '<table>';
		echo '<tr>';
		if(nivelUsuario()>1 && $PlazaLocal!=1)
			echo '<td><a href="#" onClick="
				traeTipoCertificado();
				$(\'#panel\').show();
				if(document.forma.motivo.value==\'all\'){ 
					alert(\'Necesita seleccionar el motivo de cancelacion\');
					$(\'#panel\').hide(); 
				} 
				else if(document.forma.anio.value==\'all\'){ 
					alert(\'Necesita seleccionar el año de certificado\'); 
					$(\'#panel\').hide();
				} 
				else if(document.forma.engomado.value==\'all\'){ 
					alert(\'Necesita seleccionar el tipo de certificado\'); 
					$(\'#panel\').hide();
				} 
				else if(document.forma.certificado.value==\'\'){ 
					alert(\'Necesita ingresar el holograma\'); 
					$(\'#panel\').hide();
				} 
				else if(document.forma.tecnico.value==\'\'){ 
					alert(\'Necesita ingresar el tecnico\'); 
					$(\'#panel\').hide();
				} 
				else if(document.forma.linea.value==\'\'){ 
					alert(\'Necesita ingresar la linea\'); 
					$(\'#panel\').hide();
				} 
				/*else if($.trim(document.forma.placa.value)==\'\'){ 
					alert(\'Necesita ingresar la placa\'); 
					$(\'#panel\').hide();
				} 
				else if(!validarPlaca() && $.trim(document.forma.placa.value)!=\'0000\'){ 
					alert(\'La placa ya se le entregaron sus certificados\'); 
					$(\'#panel\').hide();
				} */
				else{ 
					atcr(\'cancelar_certificados.php\',\'\',\'2\',\'0\');
				}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'cancelar_certificados.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
		echo '
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Motivo Cancelacion</td><td><select name="motivo" id="motivo"><option value="all">Seleccione</option>';
		foreach($array_motivos as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>A&ntilde;o</td><td><select name="anio" id="anio"><option value="all">Selccione</option>';
		foreach($array_anios as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Holograma</td><td><input type="text" name="certificado" id="certificado" size="30" class="textField" value="" onKeyUp="if(event.keyCode==13){ traeTipoCertificado();}" onChange="traeTipoCertificado();"></td></tr>';
		echo '<tr><td>Tipo de Certificado</td><td><select name="engomado" id="engomado"><option value="all">Selccione</option>';
		/*foreach($array_engomado as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}*/
		echo '</select></td></tr>';
		echo '<tr><td>Placa</td><td><input type="text" name="placa" id="placa" size="10" class="textField" value="" onKeyUp="if(event.keyCode==13){ traeRegistro();}else{this.value = this.value.toUpperCase();}"></td></tr>';
		echo '<tr><td>Tecnico</td><td><select name="tecnico" id="tecnico"><option value="all">Seleccione</option>';
		foreach($array_personal as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Linea</td><td><select name="linea" id="linea"><option value="">Seleccione</option>';
		foreach($array_lineas as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Observaciones</td><td><textarea name="obs" id="obs" rows="3" cols="50"  class="textField"></textarea></td></tr>';
		echo '<tr><td>Cobro a tecnico</td><td><input type="checkbox" id="cobro_tecnico" name="cobro_tecnico" value="1" onClick="
			if(this.checked){
				$(\'#trempleados\').show();
			}
			else{
				$(\'#trempleados\').hide();
			}
		"></td></tr>';
		echo '<tr style="display:none;" id="trempleados"><td>Empleado(s)<br>
		<span style="cursor:pointer;" onClick="agregarpersonal()"><font color="BLUE">Agregar</font></span></td><td><table id="tablepersonal"><tr><td><select name="personales[]"><option value="0">Seleccione</option>'.$optionspersonal.'</select></td><td><span style="cursor:pointer" onClick="$(this).parents(\'tr:first\').remove();"><font color="BLUE">Quitar</font></span></td></tr>';
		echo '<tr style="display:none;"><td><input type="text" name="noenter" value=""></td></tr>';
		echo '</table>';
		echo '<script>
		
				function agregarpersonal(){
					$("#tablepersonal").append(\'<tr><td><select name="personales[]"><option value="0">Seleccione</option>'.$optionspersonal.'</select></td><td><span style="cursor:pointer" onClick="$(this).parents(\\\'tr:first\\\').remove();"><font color="BLUE">Quitar</font></span></td></tr>\');
				}
				
				
				function validarPlaca(){
					regresar = true;
					$.ajax({
					  url: "cancelar_certificados.php",
					  type: "POST",
					  async: false,
					  data: {
						placa: document.getElementById("placa").value,
						cveusuario: document.getElementById("cveusuario").value,
						anio: document.forma.anio.value,
						ajax: 9,
						plazausuario: document.getElementById("plazausuario").value
					  },
						success: function(data) {
							if(data == "1")
							{
								regresar = false;
							}
						}
					});
					return regresar;
				}

				function traeTipoCertificado(){
					if(document.forma.certificado.value!=""){
						$.ajax({
						  url: "certificados.php",
						  type: "POST",
						  async: false,
						  dataType: "json",
						  data: {
							certificado: document.getElementById("certificado").value,
							anio: document.forma.anio.value,
							ajax: 9,
							plazausuario: document.getElementById("plazausuario").value
						  },
							success: function(data) {
								if(data.error == 1)
								{
									alert(data.mensaje_error);
									document.forma.certificado.value="";
									$("#engomado").html(\'<option value="0">Seleccione</option>\');
								}
								else
								{
									$("#engomado").html(data.engomado);
								}
							}
						});
					}
					else{
						$("#engomado").html(\'<option value="0">Seleccione</option>\');
					}
				}
			</script>';

}
	

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
			
		if(nivelUsuario()>1 && $PlazaLocal!=1)
			echo '<td><a href="#" onClick="atcr(\'cancelar_certificados.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';	
			//echo '<td><a href="#" onClick="if(document.forma.motivo.value==\'all\') alert(\'Necesita seleccionar el motivo de cancelacion\'); else if(document.forma.engomado.value==\'all\') alert(\'Necesita seleccionar el engomado\'); else if(document.forma.certificado.value==\'\') alert(\'Necesita ingresar el certificado\'); else atcr(\'cancelar_certificados.php\',\'\',\'2\',\'0\');"><img src="images/validono.gif" border="0"></a>&nbsp;Cancelar</td><td>&nbsp;</td>';
		if($_POST['cveusuario']==1) {
			echo '<td><a href="#" onClick="atcr(\'cancelar_certificados.php\',\'\',\'31\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Cancelacion Masiva</td><td>&nbsp;</td>';	
		}
		echo '
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Motivo Cancelacion</td><td><select name="motivo" id="motivo"><option value="all">Todos</option>';
		foreach($array_motivos as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		
		echo '<tr><td>A&ntilde;o</td><td><select name="anio" id="anio"><option value="all">Todos</option>';
		foreach($array_anios as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
	/*	echo '<tr><td>Tipo de Certificado</td><td><select name="engomado" id="engomado"><option value="all">Todos</option>';
		foreach($array_engomado as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';*/
		echo '<tr><td>Holograma</td><td><input type="text" name="certificado" id="certificado" size="30" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Tipo de certificado</td><td><select multiple="multiple" name="engomado" id="engomado">';
	foreach($array_datos_engomado as $datoengomado){
		echo '<option value="'.$datoengomado['cve'].'" selected>'.$datoengomado['nombre'].'</option>';
	}
	echo '</select><input type="hidden" name="engomad" id="engomad" value=""></td></tr>';
		echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">Todos</option>';
		foreach($array_usuario_ as $k=>$v){
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
	var datos_engomado = '.json_encode($array_engomado).';
	var campo_engomado = $("#engomado");
	campo_engomado.multipleSelect({
		width: 500
	});
	function buscarRegistros(btn)
	{
		document.forma.engomad.value=$("#engomado").multipleSelect("getSelects");
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","cancelar_certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&btn="+btn+"&anio="+document.getElementById("anio").value+"&engomado="+document.getElementById("engomado").value+"&motivo="+document.getElementById("motivo").value+"&certificado="+document.getElementById("certificado").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&engomado="+document.getElementById("engomad").value+"&usuario="+document.getElementById("usuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}

	function guardarFecha(folio, fecha_anterior){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","cancelar_certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=6&folio="+folio+"&fecha_anterior="+fecha_anterior+"&fecha="+document.getElementById("fechan_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros(1);}
			}
		}
	}

	function guardarAnio(folio, anio_anterior){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","cancelar_certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=7&folio="+folio+"&anio_anterior="+anio_anterior+"&anio="+document.getElementById("anio_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros(1);}
			}
		}
	}

	buscarRegistros(0); //Realizar consulta de todos los registros al iniciar la forma.
		
	
	
	</Script>
	';

	
}
	
bottom();

?>

