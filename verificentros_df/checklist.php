<?php
include("main.php");
$rsUsuario=mysql_query("SELECT * FROM plazas where estatus!='I' ORDER BY numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}
$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}
$res = mysql_query("SELECT * FROM plazas WHERE 1 ORDER BY cve");
		while($row=mysql_fetch_array($res)){
			$array_plazanom[$row['cve']]=$row['nombre'];
			$array_plazanum[$row['cve']]=$row['numero'];
		}

if($_POST['cmd']==101){
	ini_set("session.auto_start", 0);
	include('fpdf153/fpdf.php');
	include("numlet.php");	
	$pdf=new FPDF('P','mm','LETTER');
	$select = "SELECT * FROM checklist WHERE cve='".$_POST['reg']."'";
	$res = mysql_query($select) or die(mysql_error());
	$row=mysql_fetch_array($res);
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',14);
	$pdf->Cell(13.5);
	$pdf->Cell(48,25,'',1,0,'C');
	$pdf->Cell(63,25,'IMAGEN INSTITUCIONAL',1,0,'C');
	$pdf->SetFont('Arial','B',9);
	$pdf->MultiCell(57,8,'Clave:            			'.$array_plazanum[$row['plaza']].'                      Fecha de Revision: '.$row['fecha'].' Revision: '.$row['folio'].'',0,'J');
	$pdf->Ln();
	$pdf->Cell(13.5);
	$pdf->Cell(38,4,'RESPONSABLE',1,0,'C');
	$pdf->Cell(96,4,'ACTIVIDAD',1,0,'C');
	$pdf->Cell(35,4,'CHECK',1,0,'C');
	$pdf->Ln();
	$pdf->Cell(13.5);
	$pdf->Cell(169,10,'LOGOTIPOS',1,0,'C');
	$pdf->Ln();
	$pdf->Cell(13.5);	
	$pdf->Cell(38,16,'RESPONSABLE',1,0,'C');
	$pdf->SetFont('Arial','B',9);
	$pdf->MultiCell(96,4,''.utf8_decode("En la señalizacion de las Areas de Atencion Ciudadana se dispone la colocacion de los logotipo del GDF,CDMX,SEDEMA y el de Verificentros de acuerdo al Manual de Comunicacion e Identidad Grafica de la CDMX"),0,'J');
	$pdf->Cell(13.5);
	$pdf->Cell(38,15,'',1,0,'C');
	$pdf->Cell(96,15,'Logo del GDF',1,0,'L');
	$pdf->Cell(35,15,'',1,0,'C');
	$pdf->Ln();
	$pdf->Cell(13.5);
	$pdf->Cell(38,15,'',1,0,'C');
	$pdf->Cell(96,15,'Logo del la CDMX',1,0,'L');
	$pdf->Cell(35,15,'',1,0,'C');
	$pdf->Ln();
	$pdf->Cell(13.5);
	$pdf->Cell(38,15,'',1,0,'C');
	$pdf->Cell(96,15,'Logo del SEDEMA',1,0,'L');
	$pdf->Cell(35,15,'',1,0,'C');
	$pdf->Ln();
	$pdf->Cell(13.5);
	$pdf->Cell(38,15,'',1,0,'C');
	$pdf->Cell(96,15,'Logo verificacion vehicular CDMX',1,0,'L');
	$pdf->Cell(35,15,'',1,0,'C');
	$pdf->Ln();
	$pdf->Cell(13.5);
	$pdf->Cell(169,10,''.utf8_decode("SEÑALETICA"),1,0,'C');
	$pdf->Ln();
	$pdf->Cell(13.5);
	$pdf->Cell(77,38,'',1,0,'C');
	$pdf->MultiCell(55,4.2,''.utf8_decode("Señalizacion bienvenido para puerta. en la señalizacion de Bbienvenido conservar el alto de 15 cm y ajustar el largo de acuerdo al espacio requerido, centrado los elementos como se muestra. para el arreglo de los logos oficiales, conservar el tamaño de 12 cm de alto"),0,'J');
	$pdf->Cell(13.5);
	$pdf->Cell(77,33,'',1,0,'C');
	$pdf->MultiCell(55,4.2,''.utf8_decode("Señalizacion exterior. Este tipo de señalizacion sera colocada en las entradas  de cada centro de verificacion vehicular. El tamaño debera ajustarse al espacio, sin embargo el tamaño que se propone a fin de Que sea visible esde 1.00 m de ancho "),0,'J');
	$pdf->Cell(77,7,'',0,0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','B',6);
	$pdf->Cell(15.5);
	$pdf->Cell(59,3.3,'Elaboro:',0,0,'C');
	$pdf->Cell(54,3.3,'Reviso:',0,0,'C');
	$pdf->Cell(58,3.3,'Autorizo:',0,0,'C');
	$pdf->Ln();
	$pdf->Cell(15.5);
	$pdf->Cell(59,3.3,'Arturo Galicia de la O',0,0,'C');
	$pdf->Cell(54,3.3,'Miguel Maron/Sabino Adame',0,0,'C');
	$pdf->Cell(58,3.3,'Miguel Maron Manzur',0,0,'C');
	$pdf->Ln();
	$pdf->Cell(15.5);
	$pdf->Cell(59,3.3,'',0,0,'C');
	$pdf->Cell(54,3.3,'',0,0,'C');
	$pdf->Cell(58,3.3,'Director',0,0,'C');
	$pdf->Ln();
	
	
	$pdf->Image('img_check/top.jpg',24,11,47,23.8);
		
	$pdf->Image('img_check/gdf.jpg',24,73,37,14);
		if($row['logo_gdf']==1){$pdf->Image('img_check/ok.jpg',167,76,15,7);}else{$pdf->Image('img_check/x.jpg',167,76,15,7);}
	$pdf->Image('img_check/cdmx.jpg',24,87.5,37,14);
		if($row['logo_cdmx']==1){$pdf->Image('img_check/ok.jpg',167,91,15,7);}else{$pdf->Image('img_check/x.jpg',167,91,15,7);}
	$pdf->Image('img_check/sedema.jpg',24,102.5,37,14);
		if($row['logo_sedema']==1){$pdf->Image('img_check/ok.jpg',167,106,15,7);}else{$pdf->Image('img_check/x.jpg',167,106,15,7);}
	$pdf->Image('img_check/very_cdmx.jpg',24,117.5,37,14);
		if($row['logo_very_cdmx']==1){$pdf->Image('img_check/ok.jpg',167,121,15,7);}else{$pdf->Image('img_check/x.jpg',167,121,15,7);}
	$pdf->Image('img_check/puerta.jpg',24,142.5,75,37);
	$pdf->Image('img_check/exterior.jpg',24,180.5,75,30);
//	$pdf->Image('images/autobus3.jpg',10,160,190,100);
	$pdf->setXY(71.5,10);
	$pdf->SetFont('Arial','B',14);
	$pdf->Cell(63,6,'CHECKLIST',1,0,'C');
	$pdf->setXY(134.5,10);
	$pdf->Cell(58,25,'',1,0,'C');
	$pdf->setXY(157.5,56);
	$pdf->Cell(35,16,'',1,0,'C');
	$pdf->setXY(157.5,142);
	$pdf->Cell(35,38,'',1,0,'C');
		if($row['senal_puerta']==1){$pdf->Image('img_check/ok.jpg',167,158,15,7);}else{$pdf->Image('img_check/x.jpg',167,158,15,7);}
	$pdf->setXY(157.5,180);
	$pdf->Cell(35,33,'',1,0,'C');
		if($row['senal_exterior']==1){$pdf->Image('img_check/ok.jpg',167,191,15,7);}else{$pdf->Image('img_check/x.jpg',167,191,15,7);}
	$pdf->setXY(100.5,180);
	$pdf->Cell(57,33,'',1,0,'C');
	$pdf->setXY(25.5,234.5);
	$pdf->Cell(59,10,'',1,0,'C');
	$pdf->Cell(54,10,'',1,0,'C');
	$pdf->Cell(52,10,'',1,0,'C');
	
	$pdf->Output();
	exit();
}

if($_POST['ajax']==1){
	echo'<table border="0" width="100%">
		 <tr bgcolor="#E9F2F8"><th width="90"></th><th>Folio</th><th>Fecha</th><th>Usuario</th></tr>';
		 
		$select = "SELECT * FROM checklist WHERE plaza='".$_POST['plazausuario']."' and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		 if($_POST['folio']!=""){$select.=" AND folio='".$_POST['folio']."'";}
		$select.=" ORDER BY CVE DESC";
		//echo''.$select.'';
		 $res = mysql_query($select) or die(mysql_error());
		 while($row= mysql_fetch_array($res)){
		 rowb();
		 if($row['estatus']=="C"){
			echo'<td align="center">Cancelado</br>'.$array_usuario[$row['usuario']].'</br>'.$row['fechacan'].'</br>'.$row['horacan'].'</td>';
		 }else{
			echo'<td align="center"><a href="#" onClick="atcr(\'\',\'_blank\',\'101\','.$row['cve'].');"><img src="images/b_print.png" border="0" title="Imprimir"></a>
			                        <a href="#" onClick="atcr(\'checklist.php\',\'\',\'3\','.$row['cve'].');"><img src="images/validono.gif" border="0" title=""></a></td>';
		 }
		 echo'<td align="center">'.$row['folio'].'</td>';
		 echo'<td align="center">'.$row['fecha'].'</td>';
		 echo'<td align="center">'.$array_usuario[$row['usuario']].'</td>';
		 echo'</tr>';
		 }
	echo'</table>';
	exit();
}
top($_SESSION);
if($_POST['cmd']==3){
     $insert="UPDATE checklist SET fechacan='".fechaLocal()."', horacan='".horaLocal()."',usuariocan='".$_POST['cveusuario']."',estatus='C' where cve='".$_POST['reg']."'";
	 $ejecutar=mysql_query($insert);
	 $_POST['cmd']=0;
}
if($_POST['cmd']==2){

		$select = "SELECT max(folio) as fol FROM checklist WHERE plaza='".$_POST['plazausuario']."'";
		 $res = mysql_query($select) or die(mysql_error());
		 $row=mysql_fetch_array($res);
		 $folio=$row['fol'] + 1;
     $insert="INSERT checklist SET plaza='".$_POST['plazausuario']."', fecha='".fechaLocal()."', hora='".horaLocal()."',logo_gdf='".$_POST['logo_gdf']."',
				logo_cdmx='".$_POST['logo_cdmx']."',
			  logo_sedema='".$_POST['logo_sedema']."',logo_very_cdmx='".$_POST['logo_very_cdmx']."',senal_puerta='".$_POST['senal_puerta']."',
			  senal_exterior='".$_POST['senal_exterior']."', usuario='".$_POST['cveusuario']."',estatus='A', folio='".$folio."'";
	 $ejecutar=mysql_query($insert);
	 $_POST['cmd']=0;
 }
if($_POST['cmd']==1){


	echo'<table>
		 <tr>
		 <td><a href="#" onclick="atcr(\'checklist.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0"></a>&nbsp;&nbsp;Volver</td>
		 <td><a href="#" onClick="atcr(\'checklist.php\',\'\',\'2\',\'0\');">&nbsp;<img src="images/guardar.gif" border="0"> &nbsp;Guardar</a></td>
		 </tr>
	     </table></br></br>
		 <table>
		 <tr>
		 <th>Logo del GDF</th><td><input type="checkbox" name="logo_gdf" id="logo_gdf" value="1"></td>
		 </tr>
		 <tr>
		 <th>Logo de la CDMX</th><td><input type="checkbox" name="logo_cdmx" id="logo_cdmx" value="1"></td>
		 </tr>
		 <tr>
		 <th>Logo de SEDEMA</th><td><input type="checkbox" name="logo_sedema" id="logo_sedema" value="1"></td>
		 </tr>
		 <tr>
		 <th>Logo Verificacion Vehicular CDMX</th><td><input type="checkbox" name="logo_very_cdmx" id="logo_very_cdmx" value="1"></td>
		 </tr>
		 <tr>
		 <th>'.utf8_decode("Señal").' de Puerta</th><td><input type="checkbox" name="senal_puerta" id="senal_puerta" value="1"></td>
		 </tr>
		 <tr>
		 <th>'.utf8_decode("Señal").' Exterior</th><td><input type="checkbox" name="senal_exterior" id="senal_exterior" value="1"></td>
		 </tr>
		 </table>';
}
if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick=" buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onclick="atcr(\'checklist.php\',\'\',1,0);"><img src="images/nuevo.gif" border="0"></a>&nbsp;&nbsp;Nuevo</td><td>&nbsp;</td>
			<!--<td><a href="#" onclick="if(document.forma.localidad.value==\'all\') alert(\'Necesita seleccionar la localidad\'); else{ document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');atcr(\'certificadoscanceladosxplaza.php\',\'_blank\',100,0);}"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir</td><td>&nbsp;</td>-->
			<!--<td><a href="#" onclick="atcr(\'checklist.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir</td><td>&nbsp;</td>-->
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Fin</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<!--<tr><td align="left">Localidad</td><td><select name="localidad" id="" >';
	foreach($array_localidad as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$localidadplaza) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>-->';
	echo '<tr><td>Folio</td><td><input type="text" name="folio" id="folio" value=""></td></tr>';
	echo '<input type="hidden" id="plazausuario" name="plazausuario" value="'.$_POST['plazausuario'].'"></table>';
	echo '<br>';
	//Listado
	echo '<div id="Resultados">';
	echo '</div>';
/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros(){
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","checklist.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&folio="+document.getElementById("folio").value);
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
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}
	</Script>
	';	
}
bottom();
?>