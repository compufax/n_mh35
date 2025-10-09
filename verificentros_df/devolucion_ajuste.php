<?php 

include ("main.php"); 
$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza ORDER BY b.localidad_id, a.lista, a.numero");
while($Plaza=mysql_fetch_array($res)){
	$array_plaza[$Plaza['cve']]=$Plaza['numero'].' '.$Plaza['nombre'];
}

if($_POST['cmd']==100){
	require_once('fpdf153/fpdf.php');
	$pdf = new FPDF('L','mm','LETTER');
	$pdf->AddPage('L');

	$select= " SELECT b.numero,b.nombre,SUM(a.monto) as monto, COUNT(a.cve) as cantidad,
	MIN(a.fecha) as primera, MAX(a.fecha) as ultima FROM devolucion_ajuste a INNER JOIN plazas b on b.cve = a.plaza WHERE a.estatus!='C'";
	$select.=" GROUP BY a.plaza ORDER BY b.numero";
	$pdf->SetFont("Arial","B",14);
	$pdf->Cell(270,5,"DESCUENTO DE AJUSTE",0,0,'C');
	$pdf->Ln();
	$pdf->SetFont("Arial","B",10);
	$pdf->Cell(160,5,"Plaza",1,0,'C');
	$pdf->Cell(20,5,"Cantidad",1,0,'C');
	$pdf->Cell(30,5,"Importe",1,0,'C');
	$pdf->Cell(25,5,"Fecha Ini",1,0,'C');
	$pdf->Cell(25,5,"Fecha Fin",1,0,'C');
	$pdf->Ln();
	$pdf->SetFont("Arial","",10);
	$totales = array(0,0);
	$res = mysql_query($select);
	while($row = mysql_fetch_array($res)){
		$pdf->Cell(160,5,$row['numero'].' '.$row['nombre'],1,0,'L');
		$pdf->Cell(20,5,number_format($row['cantidad'],0),1,0,'R');
		$pdf->Cell(30,5,number_format($row['monto'],2),1,0,'R');
		$pdf->Cell(25,5,$row['primera'],1,0,'C');
		$pdf->Cell(25,5,$row['ultima'],1,0,'C');
		$pdf->Ln();
		$totales[0]+=$row['cantidad'];
		$totales[1]+=$row['monto'];
	}
	$pdf->SetFont("Arial","B",10);
	$pdf->Cell(160,5,"Totales ",1,0,'R');
	$pdf->Cell(20,5,number_format($totales[0],0),1,0,'R');
	$pdf->Cell(30,5,number_format($totales[1],2),1,0,'R');
	$pdf->Cell(25,5," ",1,0,'C');
	$pdf->Cell(25,5," ",1,0,'C');
	$pdf->Output();
	exit();
}

if($_POST['cmd']==101){
	

	$datos = explode('|',$_POST['reg']);
	include('fpdf153/fpdf.php');
	include("numlet.php");
	$res=mysql_query("SELECT * FROM devolucion_ajuste WHERE plaza='".$datos[0]."' AND cve='".$datos[1]."'");
	$row=mysql_fetch_array($res);
	$pdf=new FPDF('P','mm','LETTER');
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',16);
	$pdf->Cell(190,10,$array_plaza[$row['plaza']],0,0,'C');
	$pdf->Ln();
	$pdf->Cell(95,10,'Vale de Descuento por Ajuste',0,0,'L');
	$pdf->Cell(95,10,'Folio: '.$row['cve'],0,0,'R');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(95,5,'',0,0,'L');
	$pdf->Cell(95,5,'Bueno por: $ '.number_format($row['monto'],2),0,0,'R');
	$pdf->Ln();
	$y=$pdf->GetY();
	$pdf->MultiCell(95,5,'Motivo: VARIOS',0,'L');
	$pdf->SetXY(105,$y);
	$pdf->Cell(95,5,'Fecha: '.fecha_letra($row['fecha']),0,0,'R');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','',10);
	$pdf->MultiCell(190,5,"Recibi la cantidad de ".numlet($row['monto']),0,"R");
	$pdf->Ln();
	$pdf->MultiCell(190,5,"Por Concepto de: DESCUENTO POR IMPORTE ERRONEO EN VENTAS VARIAS DEL DIA ".$row['fecha_importe'],0,"R");
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','',12);
	$pdf->Cell(60,5,'');
	$pdf->MultiCell(70,5,'_______________________________________',0,'C');
	$pdf->Ln();
	$pdf->SetFont('Arial','',12);
	$pdf->Cell(190,5,"Recibi",0,0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	
	$pdf->Output();	
	exit();	
}

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM devolucion_ajuste WHERE fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		if ($_POST['plaza']!="") { $select.=" AND plaza='".$_POST['plaza']."' "; }
		$select.=" ORDER BY fecha DESC,cve DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Plaza</th><th>Folio</th><th>Fecha</th><th>Fecha Importe</th><th>Monto</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado';
					$row['monto']=0;
				}
				else{
					echo '<a href="#" onClick="atcr(\'devolucion_ajuste.php\',\'_blank\',\'101\',\''.$row['plaza'].'|'.$row['cve'].'\')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					if($_POST['cveusuario'] == 1){
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'devolucion_ajuste.php\',\'\',\'1\',\''.$row['plaza'].'|'.$row['cve'].'\')"><img src="images/modificar.gif" border="0" title="Editar '.$row['cve'].'"></a>&nbsp;&nbsp;';
						echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el recibo\')) atcr(\'devolucion_ajuste.php\',\'\',\'3\',\''.$row['plaza'].'|'.$row['cve'].'\')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
					}
				}	
				echo '</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_plaza[$row['plaza']])).'</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha_importe']).'</td>';
				echo '<td align="right">'.number_format($row['monto'],2).'</td>';
				echo '</tr>';
				$t+=$row['monto'];
			}
			echo '	
				<tr>
				<td colspan="5" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</td>
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

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM devolucion_ajuste WHERE fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		if ($_POST['plaza']!="") { $select.=" AND plaza='".$_POST['plaza']."' "; }
		$select.=" ORDER BY cve DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Plaza</th><th>Folio</th><th>Fecha</th><th>Fecha Importe</th><th>Monto</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado';
					$row['monto']=0;
				}
				else{
					echo '<a href="#" onClick="atcr(\'devolucion_ajuste.php\',\'_blank\',\'101\',\''.$row['plaza'].'|'.$row['cve'].'\')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					if($_POST['cveusuario'] == 1){
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'devolucion_ajuste.php\',\'\',\'1\',\''.$row['plaza'].'|'.$row['cve'].'\')"><img src="images/modificar.gif" border="0" title="Editar '.$row['cve'].'"></a>&nbsp;&nbsp;';
						echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el recibo\')) atcr(\'devolucion_ajuste.php\',\'\',\'3\',\''.$row['plaza'].'|'.$row['cve'].'\')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
					}
				}	
				echo '</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_plaza[$row['plaza']])).'</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha_importe']).'</td>';
				echo '<td align="right">'.number_format($row['monto'],2).'</td>';
				echo '</tr>';
				$t+=$row['monto'];
			}
			echo '	
				<tr>
				<td colspan="5" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</td>
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

if($_POST['cmd']==3){
	$datos = explode('|',$_POST['reg']);
	mysql_query("UPDATE devolucion_ajuste SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$datos[0]."' AND cve='".$datos[1]."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==12) {

	$res = mysql_query("SELECT * FROM plazas WHERE genera_devolucion=1 ORDER BY numero, nombre");
	while($row = mysql_fetch_array($res)){
		if($row['porcentaje_devolucion'] != floatval($_POST['porcentaje'][$row['cve']])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."', cveaux='".$row['cve']."', fecha=NOW(), 
				dato='Porcentaje Devolucion', nuevo = '".floatval($_POST['porcentaje'][$row['cve']])."', 
				anterior='".$row['porcentaje_devolucion']."', usuario = '".$_POST['cveusuario']."'");
		}
		if($row['dias_devolucion'] != floatval($_POST['dias'][$row['cve']])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."', cveaux='".$row['cve']."', fecha=NOW(), 
				dato='Dias Devolucion', nuevo = '".floatval($_POST['dias'][$row['cve']])."', 
				anterior='".$row['dias_devolucion']."', usuario = '".$_POST['cveusuario']."'");
		}
		if($row['importe_devolucion'] != floatval($_POST['importe'][$row['cve']])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."', cveaux='".$row['cve']."', fecha=NOW(), 
				dato='Importe Devolucion', nuevo = '".floatval($_POST['importe'][$row['cve']])."', 
				anterior='".$row['importe_devolucion']."', usuario = '".$_POST['cveusuario']."'");
		}
		$insert = " UPDATE plazas 
					SET 
					dias_devolucion = '".$_POST['dias'][$row['cve']]."',
					porcentaje_devolucion='".$_POST['porcentaje'][$row['cve']]."',
					importe_devolucion='".$_POST['importe'][$row['cve']]."'
					WHERE cve = '".$row['cve']."'";
		mysql_query($insert);
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==11) {
		
				
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();$(\'#panel\').show();atcr(\'devolucion_ajuste.php\',\'\',\'12\',\'0\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'devolucion_ajuste.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Configuración de Devolucion de Ajuste</td></tr>';
		echo '</table>';
		
		echo '<table><tr><th>Plaza</th><th>Dias</th><th>Porcentaje</th><th>Importe</th></tr>';
		$res = mysql_query("SELECT * FROM plazas WHERE genera_devolucion=1 ORDER BY numero, nombre");
		while($row = mysql_fetch_array($res)){
			echo '<tr><th align="left">'.$row['numero'].' '.$row['nombre'].'</th>
			<td><input type="text" name="dias['.$row['cve'].']" class="textField" size="10" value="'.$row['dias_devolucion'].'"></td>
			<td><input type="text" name="porcentaje['.$row['cve'].']" class="textField" size="10" value="'.$row['porcentaje_devolucion'].'"></td>
			<td><input type="text" name="importe['.$row['cve'].']" class="textField" size="10" value="'.$row['importe_devolucion'].'"></td></tr>';
		}
		echo '</table>';
		
	}

	if($_POST['cmd']==2){
		if($_POST['reg'] == '0'){
			mysql_query("INSERT devolucion_ajuste SET plaza = '".$_POST['plaza']."',fecha='".$_POST['fecha']."',monto='".$_POST['monto']."',estatus='A',fecha_importe=CURDATE(),fecha_captura=CURDATE()");
		}
		else{
			$datos = explode('|', $_POST['reg']);
			mysql_query("UPDATE devolucion_ajuste SET fecha='".$_POST['fecha']."',monto='".$_POST['monto']."' WHERE plaza='".$datos[0]."' AND cve='".$datos[1]."'");
		}
		$_POST['cmd'] = 0;
	}


	if($_POST['cmd'] == 1){
		$datos = explode('|', $_POST['reg']);
		$res = mysql_query("SELECT * FROM devolucion_ajuste WHERE plaza='".$datos[0]."' AND cve='".$datos[1]."'");
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();if(validar()) atcr(\'devolucion_ajuste.php\',\'\',\'2\',\''.$_POST['reg'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'devolucion_ajuste.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Descuento de Ajuste</td></tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><th align="left">Plaza</th><td><select name="plaza" id="plaza">';
		if($_POST['reg'] == 0)
			echo '<option value="0">Seleccione</option>';
		foreach($array_plaza as $k=>$v){
			if($_POST['reg'] == 0 || $row['plaza'] == $k){
				echo '<option value="'.$k.'"';
				if($k==$row['plaza']) echo ' selected';
				echo '>'.$v.'</option>';
			}
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Fecha Aplicacion</th><td><input type="text" name="fecha" id="fecha" class="readOnly" style="font-size:12px" size="12" value="'.$row['fecha'].'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="textField" size="15" value="'.$row['monto'].'"></td></tr>';
		echo '</table>';
		echo '<script>
				function validar(){
					if(document.forma.plaza.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar la plaza");
						return false;
					}
					return true;
				}
			</script>';
	}

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
			
		if(nivelUsuario()>2){
			echo '<td><a href="#" onClick="atcr(\'devolucion_ajuste.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="atcr(\'devolucion_ajuste.php\',\'\',\'11\',\'0\');"><img src="images/key.png" border="0"></a>&nbsp;Configuracion</td><td>&nbsp;</td>';
		}
		if($_POST['cveusuario']==1)
			echo '<td><a href="#" onClick="atcr(\'devolucion_ajuste.php\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>';
		echo '
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza"><option value="">Todos</option>';
	$res=mysql_query("SELECT b.cve,(b.numero.' '.b.nombre) as nombre FROM devolucion_ajuste a INNER JOIN plazas b ON a.plaza = b.cve GROUP BY a.plaza ORDER BY b.numero,b.nombre");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['nombre'].'</option>';
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

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","devolucion_ajuste.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&plaza="+document.getElementById("plaza").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
	buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
		
	
	
	</Script>
	';

	
}
	
bottom();	
