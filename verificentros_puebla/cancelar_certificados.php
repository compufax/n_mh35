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

$res=mysql_query("SELECT local, validar_certificado FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];
$ValidarCertificados=$row[1];

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);
$array_engomado = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND plazas like '%|".$_POST['plazausuario']."|%' AND entrega=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
}

if($_POST['cmd']==100){
	include('../fpdf153/fpdf.php');
	include("../numlet.php");	
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
			$this->Cell(0,10,'Página '.$this->PageNo().' de {nb}',0,0,'C');
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
		//Listado de plazas
		$select= " SELECT * FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."'";
		if($_POST['certificado']!=""){ 
			$select.=" AND CAST(certificado as UNSIGNED)='".intval($_POST['certificado'])."' "; 
		}
		else{
			$select.=" AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
			if ($_POST['motivo']!="all") { $select.=" AND motivo='".$_POST['motivo']."' "; }
			if ($_POST['engomado']!="all") { $select.=" AND engomado='".$_POST['engomado']."' "; }
			//if ($_POST['certificado']!="") { $select.=" AND certificado='".$_POST['certificado']."' "; }
		}
		$select.=" ORDER BY cve DESC";
		//if($_POST['btn']==0) $select.=" LIMIT 1";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Motivo</th><th>Tipo de Certificado</th><th>Holograma</th><th>Usuario</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center">';
				if($row['estatus']=='C'){
					$row['certificado']='';
					echo 'CANCELADO';
				}
				else{
					if((nivelUsuario()>2 && $row['fecha']==fechaLocal() && $PlazaLocal != 1) || $_POST['cveusuario']==1)
						echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el registro\')) atcr(\'cancelar_certificados.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				}
				echo '</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_motivos[$row['motivo']])).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_engomado[$row['engomado']])).'</td>';
				echo '<td align="center">'.htmlentities($row['certificado']).'</td>';
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



top($_SESSION);



/*** ACTUALIZAR REGISTRO  **************************************************/

if($_POST['cmd']==3){
	mysql_query("UPDATE certificados_cancelados SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$res = mysql_query("SELECT * FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	mysql_query("UPDATE compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra SET b.estatus=0 WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$row['engomado']."' AND b.folio='".intval($row['certificado'])."' AND b.estatus=1");
	
	$_POST['cmd']=0;
}

if ($_POST['cmd']==2) {
	
	$res = mysql_query("SELECT cve, fecha FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$_POST['engomado']."' AND CAST(certificado AS UNSIGNED)='".intval($_POST['certificado'])."' AND estatus!='C'");
	if(mysql_num_rows($res)==0){
		$res = mysql_query("SELECT cve, fecha FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$_POST['engomado']."' AND CAST(certificado AS UNSIGNED)='".intval($_POST['certificado'])."' AND estatus!='C'");
		if(mysql_num_rows($res)==0){
			$res = mysql_query("SELECT b.cve FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$_POST['engomado']."' AND b.folio='".intval($_POST['certificado'])."' AND b.estatus=0");
			if(mysql_num_rows($res)>0 || $ValidarCertificados == 0){
				$insert = " INSERT certificados_cancelados
									SET 
									plaza = '".$_POST['plazausuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',
									certificado='".$_POST['certificado']."',motivo='".$_POST['motivo']."',
									usuario='".$_POST['cveusuario']."',engomado='".$_POST['engomado']."',estatus='A'";
				mysql_query($insert);
				mysql_query("UPDATE compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra SET b.estatus=1 WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado = '".$_POST['engomado']."' AND b.folio='".intval($_POST['certificado'])."' AND b.estatus=0");
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

if($_POST['cmd']==1){
	echo '<table>';
		echo '<tr>';
		if(nivelUsuario()>1 && $PlazaLocal!=1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();if(document.forma.motivo.value==\'all\'){ alert(\'Necesita seleccionar el motivo de cancelacion\');$(\'#panel\').hide(); } else if(document.forma.engomado.value==\'all\'){ alert(\'Necesita seleccionar el tipo de certificado\'); $(\'#panel\').hide();} else if(document.forma.certificado.value==\'\'){ alert(\'Necesita ingresar el holograma\'); $(\'#panel\').hide();} else{ atcr(\'cancelar_certificados.php\',\'\',\'2\',\'0\');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
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
		echo '<tr><td>Tipo de Certificado</td><td><select name="engomado" id="engomado"><option value="all">Selccione</option>';
		foreach($array_engomado as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Holograma</td><td><input type="text" name="certificado" id="certificado" size="30" class="textField" value=""></td></tr>';
		echo '<tr style="display:none;"><td><input type="text" name="noenter" value=""></td></tr>';
		echo '</table>';

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
		echo '<tr><td>Tipo de Certificado</td><td><select name="engomado" id="engomado"><option value="all">Todos</option>';
		foreach($array_engomado as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Holograma</td><td><input type="text" name="certificado" id="certificado" size="30" class="textField" value=""></td></tr>';
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
			objeto.open("POST","cancelar_certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&btn="+btn+"&engomado="+document.getElementById("engomado").value+"&motivo="+document.getElementById("motivo").value+"&certificado="+document.getElementById("certificado").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
	buscarRegistros(0); //Realizar consulta de todos los registros al iniciar la forma.
		
	
	
	</Script>
	';

	
}
	
bottom();

?>

