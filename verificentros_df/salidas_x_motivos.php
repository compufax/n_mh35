<?php 

include ("main.php"); 

$array_cuentas = array();
$res = mysql_query("SELECT * FROM cuentas_contables ORDER BY cuenta,nombre");
while($row = mysql_fetch_array($res)){
	$array_cuentas[$row['cve']]=$row['cuenta'].' '.$row['nombre'];
}
$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$array_motivo = array();
$res = mysql_query("SELECT * FROM motivos");
while($row=mysql_fetch_array($res)){
	$array_motivo[$row['cve']]=$row['nombre'];
}

$array_beneficiarios = array();
$res = mysql_query("SELECT * FROM beneficiarios WHERE plaza = '".$_POST['plazausuario']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_beneficiarios[$row['cve']]=$row['nombre'];
}

if($_POST['cmd']==102){
	$res=mysql_db_query($base,"SELECT * FROM plazas");
	while($Plaza=mysql_fetch_array($res)){
		$array_plaza[$row['cve']]=$row['nombre'];
	}

	$rsPuesto=mysql_db_query($base,"SELECT * FROM puestos");
	while($Puesto=mysql_fetch_array($rsPuesto)){
		$array_puesto[$Puesto['cve']]=$Puesto['nombre'];
	}
	include('../fpdf153/fpdf.php');
	include("numlet.php");
	$res=mysql_db_query($base,"SELECT * FROM recibos_salida WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	$pdf=new FPDF('P','mm','LETTER');
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',16);
	$Plaza = mysql_fetch_array(mysql_db_query($base,"SELECT nombre FROM plazas WHERE cve='".$_POST['plazausuario']."'"));
	$pdf->Cell(190,10,$array_plaza[$_POST['plazausuario']].' '.$Plaza['nombre'],0,0,'C');
	$pdf->Ln();
	$pdf->Cell(95,10,'Recibo de Salida',0,0,'L');
	$pdf->Cell(95,10,'Folio: '.$_POST['reg'],0,0,'R');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(95,5,'',0,0,'L');
	$pdf->Cell(95,5,'Bueno por: $ '.number_format($row['monto'],2),0,0,'R');
	$pdf->Ln();
	$y=$pdf->GetY();
	$pdf->MultiCell(95,5,'Motivo: '.$array_motivo[$row['motivo']],0,'L');
	$pdf->SetXY(105,$y);
	$pdf->Cell(95,5,'Fecha: '.fecha_letra($row['fecha']),0,0,'R');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','',10);
	$pdf->MultiCell(190,5,"Recibi la cantidad de ".numlet($row['monto']),0,"R");
	$pdf->Ln();
	$pdf->MultiCell(190,5,"Por Concepto de: ".$row['concepto'],0,"R");
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','U',12);
	$pdf->Cell(60,5,'');
	$pdf->MultiCell(70,5,$array_beneficiarios[$row['beneficiario']],0,'C');
	$pdf->Ln();
	$pdf->SetFont('Arial','',12);
	$pdf->Cell(190,5,"Recibi",0,0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$rsfirmas=mysql_db_query($base,"SELECT * FROM administradores WHERE recibo_salida='1' AND plaza='".$_POST['plazausuario']."' AND fecha_ini<='".$row['fecha']."' AND (fecha_fin>='".$row['fecha']."' OR fecha_fin='0000-00-00')");
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
	}
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();

	$pdf->SetFont('Arial','',10);
	$pdf->Cell(95,5,'Impreso por: '.$array_usuario[$_POST['cveusuario']],0,0,'L');
	$pdf->Cell(95,5,'Creado por: '.$array_usuario[$row['usuario']],0,0,'R');
	$pdf->Output();	
	exit();	
}

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM motivos WHERE 1 ";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		$select.=" ORDER BY nombre";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Detalles</th><th>Nombre</th><th>Cuenta</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$fill="";
			$total=0;
			while($row=mysql_fetch_array($res)) {
				if($_POST['fecha_ini']!="" and $_POST['fecha_fin']!=""){$fill=" and fecha between '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."'";}
		        $sel= " SELECT sum(monto) as total FROM recibos_salida WHERE plaza='".$_POST['plazausuario']."' and estatus='A' and motivo='".$row['cve']."'".$fill."";
                $re=mysql_query($sel) or die(mysql_error());
				$row1=mysql_fetch_array($re) or die(mysql_error());
				if($row1['total']>0){
					rowb();
					echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'101\','.$row['cve'].')"><img src="images/b_search.png" border="0" title="Editar '.$Benef['nombre'].'"></a></td>';
					echo '<td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
					echo '<td align="right">'.htmlentities(number_format($row1['total'],2)).'</td>';
					echo '</tr>';
					$total=$total + $row1['total'];
				}
			}
			echo '	
				<tr bgcolor="#E9F2F8">
				<td colspan="2" bgcolor="#E9F2F8">';menunavegacion();echo '</td><td>'.number_format($total,2).'</td>
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

if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE motivos 
						SET nombre='".$_POST['nombre']."',cuenta='".$_POST['cuenta']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);			
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO motivos 
						(nombre,cuenta)
						VALUES 
						('".$_POST['nombre']."','".$_POST['cuenta']."')";
			$ejecutar = mysql_query($insert);
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/
if($_POST['cmd']==101){
			//Listado de plazas
		if($_POST['fecha_ini']!="" and $_POST['fecha_fin']!=""){$fill=" and fecha between '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."'";}
		$select= " SELECT * FROM recibos_salida WHERE plaza='".$_POST['plazausuario']."' and motivo='".$_POST['reg']."'".$fill." ";
//		if ($_POST['motivo']!="") { $select.=" AND motivo='".$_POST['motivo']."' "; }
//		if ($_POST['usuario']!="") { $select.=" AND usuario='".$_POST['usuario']."' "; }
//		if ($_POST['beneficiario']!="") { $select.=" AND beneficiario='".$_POST['beneficiario']."' "; }
		$select.=" ORDER BY cve DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		echo'<table>
				<tr>
				<td><a href="#" onClick="$(\'#panel\').show();atcr(\'salidas_x_motivos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
				</tr>
			 </table></br>';
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><h2>Salidas por Motivo'.$array_motivo[$_POST['reg']].'</h2></tr><tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Beneficiario</th>
			<th>Motivo</th><th>Monto</th><th>Concepto</th><th>Usuario</th>';
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
					echo '<a href="#" onClick="atcr(\'salidas_x_motivos.php\',\'_blank\',\'102\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					if(nivelUsuario()>1){}
//						echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el recibo\')) atcr(\'recibos_salida.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				}	
				echo '</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_beneficiarios[$row['beneficiario']])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($array_motivo[$row['motivo']])).'</td>';
				echo '<td align="right">'.number_format($row['monto'],2).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($row['concepto'])).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
				$t+=$row['monto'];
			}
			echo '	
				<tr>
				<td colspan="5" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
	
}

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM motivos WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'salidas_x_motivos.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'salidas_x_motivos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Motivos</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"></td></tr>';
		echo '<tr><th align="left">Cuenta</th><td><select name="cuenta" id="cuenta"><option value="0">Seleccione</option>';
		foreach($array_cuentas as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['cuenta']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<!--<td><a href="#" onClick="atcr(\'salidas_x_motivos.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>-->
			 </tr>';
		echo '</table>';
		echo '<table>';
//		echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td></tr>';
		echo '<tr>
	       <td><span>Fecha inicial</span></td>
           <td><input size="10" value="" name="fini" id="fini" type="text" class="readOnly" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td>
           </tr>
           <tr>
           <td><span>Fecha final</span></td>
           <td><input size="10" value="" name="ffin" id="ffin" type="text" class="readOnly" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].ffin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td>
           </tr>
		</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
	
bottom();



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
			objeto.open("POST","salidas_x_motivos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fini").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&fecha_fin="+document.getElementById("ffin").value);
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
	}';	
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
	
	</Script>
';

?>

