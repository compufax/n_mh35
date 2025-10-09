<?php 

include ("main.php"); 


$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}


if($_POST['cmd']==101){
$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['nombre'];
}
	$res=mysql_query("SELECT * FROM plazas");
	while($Plaza=mysql_fetch_array($res)){
		$array_plaza[$row['cve']]=$row['nombre'];
	}

	$rsPuesto=mysql_query("SELECT * FROM puestos");
	while($Puesto=mysql_fetch_array($rsPuesto)){
		$array_puesto[$Puesto['cve']]=$Puesto['nombre'];
	}
	include('../fpdf153/fpdf.php');
	include("numlet.php");
	$res=mysql_query("SELECT * FROM cometra WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	$pdf=new FPDF('P','mm','LETTER');
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',16);
	$Plaza = mysql_fetch_array(mysql_query("SELECT nombre FROM plazas WHERE cve='".$_POST['plazausuario']."'"));
	$pdf->Cell(190,10,$array_plaza[$_POST['plazausuario']].' '.$Plaza['nombre'],0,0,'C');
	$pdf->Ln();
	$pdf->Cell(95,10,'Cometra',0,0,'L');
	$pdf->Cell(95,10,'Folio: '.$row['folio'],0,0,'R');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(95,5,'',0,0,'L');
	$pdf->Cell(95,5,'Bueno por: $ '.number_format($row['monto'],2),0,0,'R');
	$pdf->Ln();
	$y=$pdf->GetY();
	$pdf->MultiCell(95,5,'Referencia: '.$row['referencia'],0,'L');
	$pdf->SetXY(105,$y);
	$pdf->Cell(95,5,'Fecha: '.fecha_letra($row['fecha']),0,0,'R');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','',10);
	$pdf->MultiCell(190,5,"Recibi la cantidad de ".numlet($row['monto']),0,"R");
	$pdf->Ln();
	$pdf->MultiCell(190,5,"Por Concepto de: ".$row['obs'],0,"R");
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','U',12);
	$pdf->Cell(190,5,$array_usuario[$row['usuario']],0,0,'C');
	$pdf->Ln();
	$pdf->SetFont('Arial','',12);
	$pdf->Cell(190,5,"Entrego",0,0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	/*$rsfirmas=mysql_query("SELECT * FROM administradores WHERE recibo_salida='1' AND plaza='".$_POST['plazausuario']."' AND fecha_ini<='".$row['fecha']."' AND (fecha_fin>='".$row['fecha']."' OR fecha_fin='0000-00-00')");
	$numfirmas=mysql_num_rows($rsfirmas);
	$ancho=190/$numfirmas;
	$array_puestoadmon=array();
	$i=0;
	$pdf->SetFont('Arial','U',9);
	while($Firmas=mysql_fetch_array($rsfirmas)){
	
		$pdf->Cell($ancho,5,$Firmas['nombre'],0,0,'C');
		$array_puestoadmon[$i]=$array_puesto[$Firmas['puesto']];
		$i++;
	}
	$pdf->Ln();
	$pdf->SetFont('Arial','',9);
	for($x=0;$x<$i;$x++){
		$pdf->Cell($ancho,5,$array_puestoadmon[$x],0,0,'C');
	}*/
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();

	$pdf->SetFont('Arial','',10);
	$pdf->Cell(95,5,'Impreso por: '.$array_usuario[$_POST['cveusuario']],0,0,'L');
	$pdf->Cell(95,5,'Creado por: '.$array_usuario[$row['usuario']],0,0,'R');
	$pdf->Output();	
	exit();	
}

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM cometra WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		if ($_POST['referencia']!="") { $select.=" AND referencia='".$_POST['referencia']."' "; }
		if ($_POST['usuario']!="") { $select.=" AND usuario='".$_POST['usuario']."' "; }
		$select.=" ORDER BY cve DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th>
			<th>Referencia</th><th>Monto</th><th>Concepto</th><th>Usuario</th>';
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
					if($_POST['cveusuario']==1){echo '<a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef[''].'"></a>';}
					echo '<a href="#" onClick="atcr(\'cometra.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					if(nivelUsuario()>1)
						echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el recibo\')) atcr(\'cometra.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				}	
				echo '</td>';
				echo '<td align="center">'.htmlentities($row['folio']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($row['referencia'])).'</td>';
				echo '<td align="right">'.number_format($row['monto'],2).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($row['obs'])).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
				$t+=$row['monto'];
			}
			echo '	
				<tr>
				<td colspan="4" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</td>
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

if($_POST['cmd']==3){
	mysql_query("UPDATE cometra SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {

	$res = mysql_query("SELECT IFNULL(MAX(folio+1),1) FROM cometra WHERE plaza='".$_POST['plazausuario']."'");
	$row = mysql_fetch_array($res);
	$folio = $row[0];
	$insert = " INSERT cometra 
					SET folio='$folio',
					plaza = '".$_POST['plazausuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',
					referencia='".$_POST['referencia']."',monto='".$_POST['monto']."',
					obs='".$_POST['obs']."',
					usuario='".$_POST['cveusuario']."',estatus='A'";
	while(!$res = mysql_query($insert)){
		$folio++;
		$insert = " INSERT cometra 
					SET folio='$folio',
					plaza = '".$_POST['plazausuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',
					referencia='".$_POST['referencia']."',monto='".$_POST['monto']."',
					obs='".$_POST['obs']."',
					usuario='".$_POST['cveusuario']."',estatus='A'";
	}
	
	
	$id = mysql_insert_id();

	if(is_uploaded_file ($_FILES['archivo']['tmp_name'])){
		$arch = $_FILES['archivo']['tmp_name'];
		$nombre = explode(".",$_FILES['archivo']['name']);
		
		
		
		if(end($nombre)=="jpg"){$nuevonombre = 'cometra'.$id.'.'.end($nombre);
		copy($arch,"cometra/".$nuevonombre);
		chmod("cometra/".$nuevonombre, 0777);
		mysql_query("UPDATE cometra SET archivo='$nuevonombre' WHERE cve = '$id'");
		}
	}
	
	$_POST['cmd']=0;
	
	
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		
		$rsFotos=mysql_query("SELECT * FROM cometra WHERE cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($rsFotos);	

		$fecha=fechaLocal();	
		if($_POST['reg']){$bloc='class="readOnly" readOnly'; $fecha=$row['fecha'];}
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1){
				if($_POST['reg']){echo'&nbsp;';}else{echo '<td><a href="#" onClick="$(\'#panel\').show();validar('.$_POST['reg'].');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';}
	}
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'cometra.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Cometra</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">Fecha</th><td><input type="text" name="fecha" id="fecha" class="readOnly" style="font-size:20px" size="10" value="'.$fecha.'" readOnly '.$bloc.'></td></tr>';
	echo '<tr><th align="left">Referencia</th><td><input type="text" name="referencia" id="referencia" class="textField" size="20" style="font-size:20px" value="'.$row['referencia'].'" '.$bloc.'></td></tr>';
		echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="textField" size="10" style="font-size:20px" value="'.number_format($row['monto'],2).'" '.$bloc.'></td></tr>';
		if($row['archivo']=="" and $_POST['reg']!=""){echo '<tr><th align="left">Archivo</th><td><input type="file" name="archivo" id="archivo"><font color="red" class="" style="font-size:18px">**Solo Imagenes(.jpg) </font></td>';}
		echo '<tr><th align="left">Concepto</th><td><textarea name="obs" id="obs" class="textField" style="font-size:20px" cols="50" rows="3" '.$bloc.'>'.$row['obs'].'</textarea></td></tr>';
		if($_POST['cveusuario']==1 and $row['archivo']!=""){echo '<tr><th align="left">Foto</th><td><img src="cometra/'.$row['archivo'].'" border="1" height="200" width="200"></td></tr>';}
		echo '</table>';
		
		echo '<script>
				function validar(reg){
					if(document.forma.referencia.value==""){
						$("#panel").hide();
						alert("Necesita ingresar la referencia");
					}
					else if(document.forma.archivo.value==""){
						$("#panel").hide();
						alert("Necesita seleccionar el archivo");
					}
					else if((document.forma.monto.value/1)==0){
						$("#panel").hide();
						alert("Necesita ingresar el monto");
					}
					else{
						atcr("cometra.php",reg,2,0);
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
			<td><a href="#" onClick="atcr(\'cometra.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Referencia</td><td><input type="text" class="textField" name="referencia" id="referencia" value=""></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">Todos</option>';
	$res=mysql_query("SELECT b.cve,b.usuario FROM recibos_salida a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY b.usuario");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['usuario'].'</option>';
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
			objeto.open("POST","cometra.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&referencia="+document.getElementById("referencia").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
