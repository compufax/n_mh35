<?php
include ("main.php"); 
$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}
$array_cuenta = array();
$res = mysql_query("SELECT * FROM cuentas ORDER BY cuenta");
while($row = mysql_fetch_array($res)){
	$array_cuenta[$row['cve']] = $row['cuenta'].' '.$row['banco'];
}
$array_motivo = array();
$res = mysql_query("SELECT * FROM motivos ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_motivo[$row['cve']]=$row['nombre'];
}
$array_beneficiario = array();
$res = mysql_query("SELECT * FROM beneficiarios_chequera ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_beneficiario[$row['cve']]=$row['nombre'];
}

if($_POST['cmd']==101){
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
	$res=mysql_db_query($base,"SELECT * FROM transferencias WHERE cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	$pdf=new FPDF('P','mm','LETTER');
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',16);
	$pdf->Cell(190,10,$array_plaza[$row['plaza']],0,0,'C');
	$pdf->Ln();
	$pdf->Cell(95,10,'Transferencias',0,0,'L');
	$pdf->Cell(95,10,'Folio: '.$row['folio'],0,0,'R');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(95,5,'Referencia: '.$row['referencia'],0,0,'L');
	$pdf->Cell(95,5,'Bueno por: $ '.number_format($row['monto'],2),0,0,'R');
	$pdf->Ln();
	$y=$pdf->GetY();
	$pdf->MultiCell(95,5,'Motivo: '.$array_motivo[$row['motivo']],0,'L');
	$pdf->SetXY(105,$y);
	$pdf->Cell(95,5,'Fecha: '.fecha_letra($row['fecha']),0,0,'R');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','',10);
	$pdf->MultiCell(190,5,"Transferi la cantidad de ".numlet($row['monto']),0,"R");
	$pdf->Ln();
	$pdf->MultiCell(190,5,"Por Concepto de: ".$row['concepto'],0,"R");
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','U',12);
	$pdf->Cell(60,5,'');
	$pdf->MultiCell(70,5,$array_beneficiario[$row['beneficiario']],0,'C');
	$pdf->Ln();
	$pdf->SetFont('Arial','',12);
	$pdf->Cell(190,5,"Recibi",0,0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$rsfirmas=mysql_db_query($base,"SELECT * FROM administradores WHERE transferencias='1' AND plaza='".$row['plaza']."' AND fecha_ini<='".$row['fecha']."' AND (fecha_fin>='".$row['fecha']."' OR fecha_fin='0000-00-00')");
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

if($_POST['ajax']==1){
	$plazas_ids = "";
	foreach($array_plaza as $k=>$v){
		$plazas_ids.=",".$k;
	}
	$plazas_ids=substr($plazas_ids,1);
	$select = "SELECT * FROM transferencias WHERE plaza IN (".$plazas_ids.") AND ".$_POST['tipofecha']." BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	if($_POST['plaza']!="all") $select .= " AND plaza='".$_POST['plaza']."'";
	if($_POST['cuenta']!="all") $select .= " AND cuenta='".$_POST['cuenta']."'";
	if($_POST['motivo']!="all") $select .= " AND motivo='".$_POST['motivo']."'";
	if($_POST['beneficiario']!="all") $select .= " AND beneficiario='".$_POST['beneficiario']."'";
	if($_POST['usuario']!="all") $select .= " AND usuario='".$_POST['usuario']."'";
	$select.=" ORDER BY cve DESC";
	$res=mysql_query($select);
	if(mysql_num_rows($res)>0) 
	{
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		if($_POST['plazausuario']==0) echo '<th>Plaza</th>';
		echo '<th>Folio</th><th>Fecha</th><th>Fecha Corte</th>
		<th>Cuenta</th><th>Referencia</th><th>Motivo</th><th>Beneficiario</th><th>Monto</th><th>Concepto</th><th>Usuario</th>';
		echo '</tr>';
		$t=0;
		$x=0;
		while($row=mysql_fetch_array($res)) {
			rowb();
			echo '<td align="center" width="40" nowrap>';
			if($row['estatus']=='C'){
				echo 'Cancelado';
				$row['monto']=0;
			}
			else{
				echo '<a href="#" onClick="atcr(\'transferencias.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
				if((nivelUsuario() == 2 && $row['fecha']==fechaLocal()) || nivelUsuario() > 2){
					echo '<a href="#" onClick="atcr(\'transferencias.php\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Modificar '.$row['cve'].'"></a>';
				}
				if(nivelUsuario()>1)
					echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar la transferencia\')) atcr(\'transferencias.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
			}	
			echo '</td>';
			if($_POST['plazausuario']==0) echo '<td>'.$array_plaza[$row['plaza']].'</td>';
			echo '<td align="center">'.htmlentities($row['folio']).'</td>';
			echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
			echo '<td align="center">'.htmlentities($row['fecha_corte']).'</td>';
			echo '<td align="left">'.htmlentities($array_cuenta[$row['cuenta']]).'</td>';
			echo '<td align="center">'.htmlentities($row['referencia']).'</td>';
			echo '<td align="left">'.utf8_encode($array_motivo[$row['motivo']]).'</td>';
			echo '<td align="left">'.utf8_encode($array_beneficiario[$row['beneficiario']]).'</td>';
			echo '<td align="right">'.number_format($row['monto'],2).'</td>';
			echo '<td align="left">'.utf8_encode($row['concepto']).'</td>';
			echo '<td align="center">'.utf8_encode($array_usuario[$row['usuario']]).'</td>';
			echo '</tr>';
			$t+=$row['monto'];
			$x++;
		}
		$c=8;
		if($_POST['plazausuario']==0) ++$c;
		echo '	
			<tr>
			<td colspan="'.$c.'" bgcolor="#E9F2F8">'.$x.' Registro(s)</td>
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

if($_POST['ajax']==2){
	$res = mysql_query("SELECT cve,cuenta,banco FROM cuentas WHERE plaza='".$_POST['plaza']."'");
	if(mysql_num_rows($res)!=1) echo '<option value="0">Seleccione</option>';
	while($row = mysql_fetch_array($res)) echo '<option value="'.$row['cve'].'">'.$row['cuenta'].' '.$row['banco'].'</option>';
	exit();
}


if($_POST['ajax']==3){
	$res = mysql_query("SELECT cve,nombre FROM beneficiarios WHERE plaza='".$_POST['plaza']."'");
	if(mysql_num_rows($res)!=1) echo '<option value="0">Seleccione</option>';
	while($row = mysql_fetch_array($res)) echo '<option value="'.$row['cve'].'">'.$row['nombre'].'</option>';
	exit();
}

top($_SESSION);

if($_POST['cmd']==3){
	mysql_query("UPDATE transferencias SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	if($_POST['reg']>0){
		$insert = " UPDATE transferencias 
						SET 
						plaza = '".$_POST['plaza']."',fecha_corte='".$_POST['fecha_corte']."',
						cuenta='".$_POST['cuenta']."',motivo='".$_POST['motivo']."',referencia='".$_POST['referencia']."',
						beneficiario='".$_POST['beneficiario']."',monto='".$_POST['monto']."',concepto='".$_POST['concepto']."'
					WHERE cve='".$_POST['reg']."'";
			mysql_db_query($base,$insert);
		$deposito = $_POST['reg'];
	}	
	else{
		$rsAbono=mysql_db_query($base,"SELECT IFNULL(MAX(folio)+1,1) FROM transferencias WHERE plaza='".$_POST['plaza']."'");
		$Abono=mysql_fetch_array($rsAbono);
		$folio=$Abono[0];
		$insert = " INSERT transferencias 
						SET 
						plaza = '".$_POST['plaza']."',fecha='".fechaLocal()."',hora='".horaLocal()."',fecha_corte='".$_POST['fecha_corte']."',
						cuenta='".$_POST['cuenta']."',motivo='".$_POST['motivo']."',referencia='".$_POST['referencia']."',
						beneficiario='".$_POST['beneficiario']."',monto='".$_POST['monto']."',concepto='".$_POST['concepto']."',
						usuario='".$_POST['cveusuario']."',estatus='A'";
		while(!$res = mysql_query($insert.",folio='".$folio."'")){
			$folio++;
		}
		$deposito = mysql_insert_id();
	}
	$_POST['cmd']=0;
}


if ($_POST['cmd']==1) {
		
	$select=" SELECT * FROM transferencias WHERE cve='".$_POST['reg']."' ";
	$res=mysql_query($select);
	$row=mysql_fetch_array($res);			
	if($_POST['reg']==0){
		$row['fecha_corte']=fechaLocal();
	}
	//Menu
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();validar();"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'transferencias.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Transferencias</td></tr>';
	echo '</table>';
	
	echo '<table>';
	if($_POST['plazausuario']==0 && $row['plaza']==0)
	{
		echo '<tr><th align="left">Plaza</th><td><select name="plaza" id="plaza" onChange="traerCuentas()"><option value="0">Seleccione</option>';
		foreach($array_plaza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
	}
	elseif($_POST['plazausuario']==0 && $row['plaza']>0){
		echo '<tr><th align="left">Plaza</th><td><b>'.$array_plaza[$row['plaza']].'</b><input type="hidden" name="plaza" id="plaza" value="'.$row['plaza'].'"></td></tr>';
	}
	else{
		echo '<tr style="display:none;"><th align="left">Plaza</th><td><b>'.$array_plaza[$_POST['plazausuario']].'</b><input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'"></td></tr>';
	}
	echo '<tr><th align="left">Fecha Corte</th><td><input type="text" name="fecha_corte" id="fecha_corte" class="readOnly" size="12" value="'.$row['fecha_corte'].'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_corte,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><th align="left">Cuenta</th><td><select name="cuenta" id="cuenta">';
	if($_POST['plazausuario']==0 && $row['plaza']==0){
		echo '<option value="0">Seleccione Plaza</option>';
	}
	else{
		if($_POST['plazausuario']>0)
			$res1 = mysql_query("SELECT cve,cuenta,banco,folio_siguiente FROM cuentas WHERE plaza='".$_POST['plazausuario']."'");
		else
			$res1 = mysql_query("SELECT cve,cuenta,banco,folio_siguiente FROM cuentas WHERE plaza='".$row['plaza']."'");
		if(mysql_num_rows($res1)!=1){
			echo '<option value="0">Seleccione</option>';
		}
		while($row1 = mysql_fetch_array($res1)){ 
			echo '<option value="'.$row1['cve'].'"';
			if($row1['cve']==$row['cuenta']) echo ' selected';
			echo '>'.$row1['cuenta'].' '.$row1['banco'].'</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Referencia</th><td><input type="text" name="referencia" id="referencia" class="textField" size="10" value="'.$row['referencia'].'"></td></tr>';
	echo '<tr><th align="left">Motivo</th><td><select name="motivo" id="motivo"><option value="0">Seleccione</option>';
	foreach($array_motivo as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$row['motivo']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Beneficiario</th><td><select name="beneficiario" id="beneficiario"><option value="0">Seleccione</option>';
	foreach($array_beneficiario as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$row['beneficiario']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="textField" size="10" value="'.$row['monto'].'"></td></tr>';
	echo '<tr><th align="left">Concepto</th><td><textarea cols="30" rows="3" name="concepto" id="concepto" class="textField">'.$row['concepto'].'</textarea></td></tr>';
	echo '</table>';
	
	echo '<script>
			function validar(){
				if(document.forma.plaza.value=="0"){
					$("#panel").hide();
					alert("Necesita seleccionar una plaza");
				}
				else if(document.forma.cuenta.value=="0"){
					$("#panel").hide();
					alert("Necesita seleccionar una cuenta");
				}
				else if(document.forma.referencia.value=="0"){
					$("#panel").hide();
					alert("Necesita ingresar una referencia");
				}
				else if(document.forma.motivo.value=="0"){
					$("#panel").hide();
					alert("Necesita seleccionar un motivo");
				}
				else if(document.forma.beneficiario.value=="0"){
					$("#panel").hide();
					alert("Necesita seleccionar un beneficiario");
				}
				else if((document.forma.monto.value/1)<=0){
					$("#panel").hide();
					alert("El monto debe de ser mayor a cero");
				}
				else{
					atcr("transferencias.php","",2,'.$_POST['reg'].');
				}
			}
			
			function traerCuentas(){
				if(document.forma.plaza.value=="0"){
					$("#cuenta").html(\'<option value="0">Seleccione Plaza</option>\');
					$("#beneficiario").html(\'<option value="0">Seleccione Plaza</option>\');
				}
				else{
					$.ajax({
						url: "transferencias.php",
						type: "POST",
						data: "ajax=2&plaza="+document.forma.plaza.value,
						success: function(resultado)
						{
							$("#cuenta").html(resultado);
						}
					});
				}
			}
			
			
		</script>';
	
}

if($_POST['cmd']==0){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'transferencias.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Filtrar por</td><td><select name="tipofecha" id="tipofecha"><option value="fecha">Fecha de Creacion</option><option value="fecha_corte">Fecha de Corte</option></select></td></tr>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.substr(fechaLocal(),0,8).'01" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	if($_POST['plazausuario']==0){
		echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" onChange="traerCuentas()"><option value="all">Todas</option>';
		foreach($array_plaza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
	}
	else{
		echo '<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
	}

	echo '<tr><td>Cuenta</td><td><select name="cuenta" id="cuenta"><option value="all">Todas</option>';
	if($_POST['plazausuario']>0){
		$res=mysql_query("SELECT cve,cuenta,banco FROM cuentas WHERE plaza='".$_POST['plazausuario']."' ORDER BY cuenta");
		while($row=mysql_fetch_array($res)){
			echo '<option value="'.$row['cve'].'">'.$row['cuenta'].' '.$row['banco'].'</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr><td>Motivo</td><td><select name="motivo" id="motivo"><option value="all">Todos</option>';
	foreach($array_motivo as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Beneficiario</td><td><select name="beneficiario" id="beneficiario"><option value="all">Todos</option>';
	foreach($array_beneficiario as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="all">Todos</option>';
	if($_POST['plazausuario']==0)
		$res=mysql_query("SELECT b.cve,b.usuario FROM transferencias a INNER JOIN usuarios b ON a.usuario = b.cve WHERE 1 GROUP BY a.usuario ORDER BY b.usuario");
	else
		$res=mysql_query("SELECT b.cve,b.usuario FROM transferencias a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY b.usuario");
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
			objeto.open("POST","transferencias.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&tipofecha="+document.getElementById("tipofecha").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&plaza="+document.getElementById("plaza").value+"&cuenta="+document.getElementById("cuenta").value+"&motivo="+document.getElementById("motivo").value+"&beneficiario="+document.getElementById("beneficiario").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
		
	
	function traerCuentas(){
		if(document.forma.plaza.value=="0"){
			$("#cuenta").html(\'<option value="all">Todas</option>\');
			$("#beneficiario").html(\'<option value="all">Todos</option>\');
		}
		else{
			$.ajax({
				url: "transferencias.php",
				type: "POST",
				data: "ajax=2&plaza="+document.forma.plaza.value,
				success: function(resultado)
				{
					$("#cuenta").html(resultado);
				}
			});
		}
	}
	
	
	</Script>
	';

	
}
	
bottom();




?>