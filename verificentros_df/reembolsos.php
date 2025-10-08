<?php 

include ("main.php"); 


$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}


/*if($_POST['cmd']==101){
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
*/
if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM reembolsos WHERE plaza='".$_POST['plazausuario']."' AND fecha_aplicacion BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		if ($_POST['banco']!="") { $select.=" AND banco LIKE '%".$_POST['banco']."%' "; }
		if ($_POST['usuario']!="") { $select.=" AND usuario='".$_POST['usuario']."' "; }
		if ($_POST['referencia']!="") { $select.=" AND referencia='".$_POST['referencia']."' "; }
		$select.=" ORDER BY cve DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha Movimiento</th><th>Fecha Captura</th><th>Fecha Aplicacion</th><th>Banco</th>
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
					//echo '<a href="#" onClick="atcr(\'reembolsos.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					if(nivelUsuario()>1)
						echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el reembolso\')) atcr(\'reembolsos.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				}	
				echo '</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha_mov']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha_aplicacion']).'</td>';
				echo '<td align="left">'.htmlentities($row['banco']).'</td>';
				echo '<td align="center">'.htmlentities($row['referencia']).'</td>';
				echo '<td align="right">'.number_format($row['monto'],2).'</td>';
				echo '<td align="left">'.htmlentities($row['obs']).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
				$t+=$row['monto'];
			}
			echo '	
				<tr>
				<td colspan="7" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
	mysql_query("UPDATE reembolsos SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {

	$insert = " INSERT reembolsos 
					SET 
					plaza = '".$_POST['plazausuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',
					fecha_mov='".$_POST['fecha_mov']."',fecha_aplicacion='".$_POST['fecha_aplicacion']."',
					banco='".$_POST['banco']."',referencia='".$_POST['referencia']."',monto='".$_POST['monto']."',
					obs='".$_POST['obs']."',
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
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'reembolsos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Reembolso</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">Fecha</th><td><input type="text" name="fecha_mov" id="fecha_mov" class="readOnly" style="font-size:20px" size="10" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_mov,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Fecha Aplicacion</th><td><input type="text" name="fecha_aplicacion" id="fecha_aplicacion" class="readOnly" style="font-size:20px" size="10" value="" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_aplicacion,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Banco</th><td><input type="text" name="banco" id="banco" class="textField" size="30" style="font-size:20px" value=""></td></tr>';
		echo '<tr><th align="left">Referencia Cheque</th><td><input type="text" name="referencia" id="referencia" class="textField" size="30" style="font-size:20px" value=""></td></tr>';
		echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="textField" size="10" style="font-size:20px" value=""></td></tr>';
		echo '<tr><th align="left">Concepto</th><td><textarea name="obs" id="obs" class="textField" style="font-size:20px" cols="50" rows="3"></textarea></td></tr>';
		echo '</table>';
		
		echo '<script>
				function validar(){
					if(document.forma.banco.value==""){
						$("#panel").hide();
						alert("Necesita ingresar el banco");
					}
					else if(document.forma.referencia.value==""){
						$("#panel").hide();
						alert("Necesita ingresar la referencia");
					}
					else if((document.forma.monto.value/1)==0){
						$("#panel").hide();
						alert("Necesita ingresar el monto");
					}
					else{
						atcr("reembolsos.php","",2,0);
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
			<td><a href="#" onClick="atcr(\'reembolsos.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Banco</td><td><input type="text" name="banco" id="banco" class="textField" value=""></td></tr>';
	echo '<tr><td>Referencia</td><td><input type="text" name="referencia" id="referencia" class="textField" value=""></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">Todos</option>';
	$res=mysql_query("SELECT b.cve,b.usuario FROM reembolsos a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY b.usuario");
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
			objeto.open("POST","reembolsos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&banco="+document.getElementById("banco").value+"&referencia="+document.getElementById("referencia").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
