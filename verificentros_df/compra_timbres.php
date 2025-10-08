<?php 
include ("main.php"); 


$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}



$array_estatus = array('A'=>'Activo','P'=>'Pagado','C'=>'Cancelado');
/*** CONSULTA AJAX  **************************************************/


if($_POST['cmd']==110){
	$res = mysql_query("SELECT correotimbres FROM usuarios WHERE cve=1");
	$row = mysql_fetch_array($res);
	$emailenvio = $row[0];
	if($emailenvio!=""){
		require_once('../fpdf153/fpdf.php');
		class FPDF2 extends PDF_MC_Table {
			//Pie de página
			function Footer(){
				//Posición: a 1,5 cm del final
				$this->SetY(-15);
				//Arial bold 12
				$this->SetFont('Arial','B',11);
				//Número de página
				$this->Cell(0,10,'Página '.$this->PageNo().' de {nb}',0,0,'C');
			}
		}

		$pdf=new FPDF2('P','mm','LETTER');
		$pdf->AliasNbPages();
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',16);
		$pdf->SetY(23);
		$pdf->Cell(190,5,"VERIMORELOS",0,0,'C');
		$pdf->Ln();
		$tit='';
		$pdf->MultiCell(200,5,'REPORTE DE EXISTENCIA DE TIMBRES',0,'C');
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(150,4,'Centro',0,0,'C',0);
		$pdf->Cell(30,4,'Timbres',0,0,'C',0);
		$pdf->Ln();		
		$pdf->SetFont('Arial','',10);
		$pdf->SetWidths(array(150,30));
		$res = mysql_query("SELECT * FROM plazas where estatus='A' AND timbres_exis = 1");
		while($row=mysql_fetch_array($res)){
			$renglon=array();
			$renglon[] = $row['numero'].' '.$row['nombre'];
			$renglon[] = saldo_timbres($row['cve']);
			$pdf->Row($renglon);
		}
		$nombre = "../cfdi/rep_existencia".date('Y_m_d_H_i_s');
		$pdf->Output($nombre.".pdf","F");	
		require_once('../phpmailer/class.phpmailer.php');
	
		$mail = new PHPMailer();
		$mail->Host = "localhost";
		$mail->From = "verimorelos@capturabd.net";                        // Enable encryption, only 'tls' is accepted							
		$mail->FromName = "Verificentros Morelos";
		$mail->Subject = "Reporte de Existencia de Timbres";
		$mail->Body = "Reporte";
		$correos = explode(",",trim($emailenvio));
		foreach($correos as $correo)
			$mail->AddAddress(trim($correo));
		$mail->AddAttachment($nombre.".pdf", "Reporte.pdf");
		$mail->Send();
		@unlink($nombre.".pdf");
	}	
	$_POST['cmd']=0;
}

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM compra_timbres 
		WHERE plaza='".$_POST['plazausuario']."'";
		if($_POST['fecha_ini'] != '') $select.=" AND fecha_compra >= '".$_POST['fecha_ini']."'";
		if($_POST['fecha_fin'] != '')$select .=" AND fecha_compra <= '".$_POST['fecha_fin']."'";
		$select.=" ORDER BY fecha_compra DESC,cve DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
			$c=5;
			/*if($_POST['cveusuario'] == 1){
				$c++;
				echo '<th><input type="checkbox" id="selall" onClick="
				if(this.checked) $(\'.chks\').attr(\'checked\',\'checked\');
				else $(\'.chks\').removeAttr(\'checked\');"></th>';
			}*/
			echo '<th>Consecutivo</th><th>Fecha Compra</th>
			<th>Factura</th>
			<th>Fecha</th><th>Cantidad</th><!--<th>Usados</th><th>Libres</th>--><th>Observaciones</th><th>Usuario</th>';
			echo '</tr>';
			$t=$t2=$t3=0;
			$nivelUsuario = nivelUsuario();
			while($row=mysql_fetch_array($res)) {
				if($row['estatus']=='C')
					$row['cantidad'] = 0;
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado';
				}
				elseif($row['estatus']=='P'){
					echo 'Pagado';
				}
				else{
					if($nivelUsuario>1 && $row['usados']==0)
						echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar la compra\')) atcr(\'compra_timbres.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				}	
				echo '</td>';
				/*if($_POST['cveusuario'] == 1){
					echo '<td>';
					if($row['estatus']=='A') echo '<input type="checked" class="chks" name="compra[]" value="'.$row['cve'].'">';
					echo '</td>';
				}*/
				echo '<td align="center">'.htmlentities($row['folio']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha_compra']).'</td>';
				echo '<td align="center">'.htmlentities($row['factura']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="right">'.number_format($row['cantidad'],0).'</td>';
				//echo '<td align="right">'.number_format($row['usados'],0).'</td>';
				//echo '<td align="right">'.number_format($row['cantidad']-$row['usados'],0).'</td>';
				echo '<td align="left">'.$row['obs'].'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
				$t+=$row['cantidad'];
				$t2+=$row['usados'];
				$t3+=$row['cantidad']-$row['usados'];
			}
			echo '	
				<tr>
				<td colspan="'.$c.'" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t,0).'</td>
				<!--<td align="right" bgcolor="#E9F2F8">'.number_format($t2,0).'</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t3,0).'</td>-->
				<td colspan="2" bgcolor="#E9F2F8">&nbsp;</td>
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

if($_POST['cmd']==4){
	mysql_query("UPDATE compra_timbres SET estatus='P',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

if($_POST['cmd']==3){
	mysql_query("UPDATE compra_timbres SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['reg']."' AND usados=0");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	$resPlaza = mysql_query("SELECT a.numero, a.cveclientefactura, b.rfc FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.cve = '".$_POST['plazausuario']."'");
	$rowPlaza = mysql_fetch_array($resPlaza);
	if($rowPlaza['cveclientefactura'] > 0){
		$datos['empresa'] = 25;
		$datos['cantidad'] = $_POST['cantidad'];
		$datos['numeroplaza'] = $rowPlaza['numero'];
		$datos['rfcplaza'] = $rowPlaza['rfc'];
		$datos['cvecliente'] = $rowPlaza['cveclientefactura'];
		$data = array(
			'function' => 'genera_factura_timbres',
		    'datos' => $datos
		 );

		$options = array('http' => array(
			'method'  => 'POST',
			'content' => http_build_query($data)
		));
		$context  = stream_context_create($options);

		$resultado = file_get_contents('http://hoyfactura.com/ws_genera_factura_timbres.php', false, $context);
		$datosresultado = json_decode($resultado, true);
		$_POST['factura'] = $datosresultado['factura'];
		$_POST['fecha_compra'] = fechaLocal();
		$estatus='A';
	}
	else{
		$estatus='P';
	}

	if($_POST['factura'] > 0 || $rowPlaza['cveclientefactura'] == 0){
		$res = mysql_query("SELECT IFNULL(MAX(folio)+1,1) as siguiente FROM compra_timbres WHERE plaza='".$_POST['plazausuario']."'");
		$row=mysql_fetch_array($res);	
			$insert = " INSERT compra_timbres 
							SET 
							folio='".$row[0]."',fecha_compra='".$_POST['fecha_compra']."',
							plaza = '".$_POST['plazausuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',
							cantidad='".$_POST['cantidad']."',usuario='".$_POST['cveusuario']."',estatus='$estatus',
							factura='".$_POST['factura']."',obs='".$_POST['obs']."'";
			mysql_query($insert) or die(mysql_error());
	}
	$_POST['cmd']=0;
}


/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$res = mysql_query("SELECT * FROM compra_timbres WHERE cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();validar('.$_POST['reg'].');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'compra_timbres.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Compra de Timbres</td></tr>';
		echo '</table>';
		echo '<table style="font-size:15px">';
		//echo '<tr><th align="left">Factura</th><td><input type="text" name="factura" id="factura" class="textField" style="font-size:12px" size="12" value=""></td></tr>';
		//echo '<tr><th align="left">Fecha Compra</th><td><input type="text" name="fecha_compra" id="fecha_compra" class="readOnly" style="font-size:12px" size="12" value="" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_compra,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Cantidad</th><td><input type="text" name="cantidad" id="cantidad" class="textField" size="15" style="font-size:12px" value="'.$row['cantidad'].'" ></td></tr>';
		echo '<tr><th align="left">Observaciones</th><td><textarea name="obs" id="obs" class="textField" cols="50" rows="5" style="font-size:12px"></textarea></td></tr>';
		echo '</table>';
		
		echo '<script>
				
				
				function validar(reg){
					/*if(document.forma.fecha_compra.value==""){
						$("#panel").hide();
						alert("Necesita seleccionar la fecha de la compra");
					}
					else */if((document.forma.cantidad.value/1)==0){
						$("#panel").hide();
						alert("La cantidad no puede ser cero");
					}
					else{
						atcr("compra_timbres.php","",2,reg);
					}
				}
				
				
				
			</script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	$res = mysql_query("SELECT * FROM plazas WHERE cve='".$_POST['plazausuario']."'");
	$row = mysql_fetch_array($res);
	//if($row['cveclientefactura'] > 0)
		echo '<td><a href="#" onClick="atcr(\'compra_timbres.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
	/*if($_POST['cveusuario']==1){
		echo '<td><a href="#" onClick="atcr(\'compra_timbres.php\',\'\',\'4\',\'0\');"><img src="images/finalizar.gif" border="0"></a>&nbsp;Marcar como Pagado</td><td>&nbsp;</td>';
	}*/
	if($_POST['cveusuario']==1){
			echo '<td><a href="#" onClick="atcr(\'\',\'\',\'110\',\'0\')"><img src="images/nuevo.gif" border="0" title="Imprimir"></a>Enviar Saldo de Timbres</td>';
	}
	echo '
		 </tr>';
	echo '</table>';
	echo '<table width="100%"><tr><td width="50%">';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="" >&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="" >&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	
	echo '</table>';
	echo '</td><td id="concentrado"><h3>Timbres disponibles: '.saldo_timbres($_POST['plazausuario']).'</h3></td></tr></table>';
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
			objeto.open("POST","compra_timbres.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&btn="+btn+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					datos = objeto.responseText.split("|");
					document.getElementById("Resultados").innerHTML = datos[0];
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
	buscarRegistros(0); //Realizar consulta de todos los registros al iniciar la forma.
		
	
	</Script>
	';

	
}
	
bottom();

if($cvecobro>0){
		echo '<script>atcr(\'cobro_engomado.php\',\'_blank\',\'101\','.$cvecobro.');</script>';
	}
?>

