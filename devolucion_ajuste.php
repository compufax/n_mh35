<?php 

include ("main.php"); 
$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza ORDER BY b.localidad_id, a.lista, a.numero");
while($Plaza=mysql_fetch_array($res)){
	$array_plaza[$Plaza['cve']]=$Plaza['numero'].' '.$Plaza['nombre'];
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
	$pdf->Cell(95,10,'Vale de Devolucion por Ajuste',0,0,'L');
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
	$pdf->MultiCell(190,5,"Por Concepto de: DEVOLUCION POR IMPORTE ERRONEO EN VENTAS VARIAS DEL DIA ".$row['fecha_importe'],0,"R");
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
					if($_POST['cveusuario'] == 1)
						echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el recibo\')) atcr(\'devolucion_ajuste.php\',\'\',\'3\',\''.$row['plaza'].'|'.$row['cve'].'\')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
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

if ($_POST['cmd']==2) {

	$insert = " UPDATE devolucion_ajuste 
					SET 
					plaza = '".$_POST['plazausuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',
					motivo='".$_POST['motivo']."',beneficiario='".$_POST['beneficiario']."',monto='".$_POST['monto']."',
					concepto='".$_POST['concepto']."',
					usuario='".$_POST['cveusuario']."',estatus='A'";
	mysql_query($insert);
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
				
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();validar();"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'devolucion_ajuste.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Devolucion de Ajuste</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">Fecha</th><td><input type="text" name="placa" id="placa" class="readOnly" style="font-size:20px" size="10" value="'.fechaLocal().'" readOnly></td></tr>';
		echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="textField" size="10" style="font-size:20px" value=""></td></tr>';
		echo '<tr><th align="left">Motivo</th><td><select name="motivo" id="motivo" style="font-size:20px"><option value="0">Seleccione</option>';
		foreach($array_motivo as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Beneficiario</th><td><select name="beneficiario" id="beneficiario" style="font-size:20px"><option value="0">Seleccione</option>';
		foreach($array_beneficiarios as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Concepto</th><td><textarea name="concepto" id="concepto" class="textField" style="font-size:20px" cols="50" rows="3"></textarea></td></tr>';
		echo '</table>';
		
		echo '<script>
				function validar(){
					if(document.forma.motivo.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el motivo");
					}
					else if(document.forma.beneficiario.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el beneficiario");
					}
					else if((document.forma.monto.value/1)==0){
						$("#panel").hide();
						alert("Necesita ingresar el monto");
					}
					else{
						atcr("devolucion_ajuste.php","",2,0);
					}
				}
				
			</script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<!--<td><a href="#" onClick="atcr(\'devolucion_ajuste.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>-->
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
