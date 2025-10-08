<?php

include("mantenimiento_main.php");

$array_proveedores=array();
$res=mysql_query("SELECT * FROM proveedores ORDER BY nombre");
while($row = mysql_fetch_array($res)) $array_proveedores[$row['cve']] = $row['nombre'];
$array_nomenclaturas=array();
$res=mysql_query("SELECT * FROM nomenclatura ORDER BY nombre");
while($row = mysql_fetch_array($res)) $array_nomenclaturas[$row['cve']] = $row['nombre'];
$array_refacciones=array();
$res=mysql_query("SELECT * FROM refacciones ORDER BY nombre");
while($row = mysql_fetch_array($res)) $array_refacciones[$row['cve']] = $row['nombre'];
$array_plaza=array();
$res=mysql_query("SELECT * FROM plazas ORDER BY numero,nombre");
while($row = mysql_fetch_array($res)) $array_plaza[$row['cve']] = $row['numero'].' '.$row['nombre'];
$array_lineas=array();
$res=mysql_query("SELECT * FROM cat_lineas ORDER BY nombre");
while($row = mysql_fetch_array($res)) $array_lineas[$row['cve']] = $row['nombre'];
$array_usuario=array();
$res=mysql_query("SELECT * FROM usuarios ORDER BY usuario");
while($row = mysql_fetch_array($res)) $array_usuario[$row['cve']] = $row['usuario'];
$array_tipo=array(1=>'En Operacion', 2=>'Programada', 3=>'Correctivo');
$array_estatus=array('A'=>'Activo','T'=>'Cerrado');
if($_POST['ajax']==1){
	$select= " SELECT a.* FROM reporte_lineas a WHERE 1 ";
		if ($_POST['fecha_ini']!="") { $select.=" AND fecha>='".$_POST['fecha_ini']."' "; }
		if ($_POST['fecha_fin']!="") { $select.=" AND fecha<= '".$_POST['fecha_fin']."' "; }
		if ($_POST['tipo']!="") { $select.=" AND tipo IN (".$_POST['tipo'].") "; }
		if ($_POST['plaza']!="") { $select.=" AND plaza = '".$_POST['plaza']."' "; }
		if ($_POST['linea']!="") { $select.=" AND linea = '".$_POST['linea']."' "; }
		if ($_POST['proveedor']!="") { $select.=" AND proveedor = '".$_POST['proveedor']."' "; }
		if ($_POST['nomenclatura']!="") { $select.=" AND nomenclatura = '".$_POST['nomenclatura']."' "; }
		$select.=" ORDER BY cve DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th>';
			echo '<th>Folio</th><th>Fecha</th><th>Centro</th><th>Linea</th><th>Tipo</th><th>Observacion</th>
			<th>Fecha de Programacion</th><th>Proveedor</th><th>Motivo</th><th>Usuario</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='A'){
					if($row['tipo']==3) echo '<a href="#" onClick="atcr(\'\',\'\',\'11\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef['nombre'].'"></a>';
				}
				else{
					echo 'Cerrado';
				}
				echo '</td>';
				echo '<td align="center">'.utf8_encode($row['cve']).'</td>';
				echo '<td align="center">'.utf8_encode($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td>'.utf8_encode($array_plaza[$row['plaza']]).'</td>';
				echo '<td>'.utf8_encode($array_lineas[$row['linea']]).'</td>';
				echo '<td>'.utf8_encode($array_tipo[$row['tipo']]).'</td>';
				echo '<td>'.utf8_encode($row['observacion']).'</td>';
				echo '<td align="center">'.utf8_encode($row['fechaprogramacion']).'</td>';
				echo '<td>'.utf8_encode($array_proveedores[$row['proveedor']]).'</td>';
				echo '<td>'.utf8_encode($array_nomenclaturas[$row['nomenclatura']]).'</td>';
				echo '<td>'.utf8_encode($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="11" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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

if($_POST['cmd']==12){
	if($_POST['estatus']=='T'){
		mysql_query("UPDATE reporte_lineas SET estatus='T',usuariocerro='".$_POST['cveusuario']."',fechacerro=NOW() WHERE cve='".$_POST['reg']."'");
	}
	if($_POST['obs']!=""){
		mysql_query("INSERT reporte_lineas_obs SET reporte='".$_POST['reg']."',obs='".$_POST['obs']."',usuario='".$_POST['cveusuario']."',fecha=NOW()");
	}
	foreach($_POST['refacciones'] as $k=>$v){
		if($v>0){
			mysql_query("INSERT reporte_lineas_refacciones SET reporte='".$_POST['reg']."',refaccion='".$v."',usuario='".$_POST['cveusuario']."',fecha=NOW()");		
		}
	}
	foreach($_POST['cvefolios'] as $k=>$v){
		$campos="";
		if($_POST['estatusfolios'][$k]==2){
			$campos.=" AND usuariocerro='".$_POST['cveusuario']."',fechacerro=NOW()";
		}
		if($v>0){
			if($_POST['folios'][$k]>0){
				mysql_query("UPDATE reporte_lineas_folios SET folio='".$_POST['folios'][$k]."',estatus='".$_POST['estatusfolios'][$k]."'".$campos." WHERE cve='".$v."'");		
			}
			else{
				mysql_query("DELETE FROM reporte_lineas_folios WHERE cve='".$v."'");
			}
		}
		elseif($_POST['folios'][$k]>0){
			mysql_query("INSERT reporte_lineas_folios SET reporte='".$_POST['reg']."',folio='".$_POST['folios'][$k]."',estatus='".$_POST['estatusfolios'][$k]."',usuario='".$_POST['cveusuario']."',fecha=NOW()".$campos);		
		}
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==11){
	$res = mysql_query("SELECT * FROM reporte_lineas WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	$array_lineas=array();
	$res1=mysql_query("SELECT * FROM cat_lineas WHERE plaza='".$row['plaza']."' ORDER BY nombre");
	while($row1 = mysql_fetch_array($res1)) $array_lineas[$row1['cve']] = $row1['nombre'];
	echo '<table>
		<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show(); atcr(\'mantenimiento_reporte_lineas.php\',\'\',\'12\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'mantenimiento_reporte_lineas.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	echo '<table>';
	echo '<tr><td class="tableEnc">Edicion Reporte de Lineas #'.$_POST['reg'].'</td></tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><th align="left">Centro</th><td>'.$array_plaza[$row['plaza']].'</td></tr>';
	echo '<tr><th align="left">Linea</th><td>'.$array_lineas[$row['linea']].'</td></tr>';
	echo '<tr><th align="left">Tipo</th><td>'.$array_tipo[$row['tipo']].'</td></tr>';
	echo '<tr><th align="left">Observacion</th><td>'.$row['observacion'].'</td></tr>';
	echo '<tr><th align="left">Proveedor</th><td>'.$array_proveedores[$row['proveedor']].'</td></tr>';
	echo '<tr><th align="left">Motivo</th><td>'.$array_nomenclaturas[$row['nomenclatura']].'</td></tr>';
	echo '<tr><th align="left">Estatus</th><td><select name="estatus" id="estatus">';
	foreach($array_estatus as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Nueva Observacion</th><td><textarea name="obs" id="obs" class="textField" cols="50" rows="3"></textarea></td></tr>';
	echo '<tr><th align="left" valign="top">Refacciones</th><td><table id="trefacciones"><tr id="rref" style="display:none;"><td><select name="refacciones[]"><option value="0">Seleccione</option>';
	foreach($array_refacciones as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
	echo '</select></td></tr>';
	$res1=mysql_query("SELECT * FROM reporte_lineas_refacciones WHERE reporte='".$row['cve']."'");
	while($row1=mysql_fetch_array($res1)) echo '<tr><td>'.$array_refacciones[$row1['refaccion']].'</td></tr>';
	echo '</table><br><input type="button" class="textField" value="Agregar Refaccion" onClick="agregarRefaccion()"></td></tr>';
	echo '<tr><th align="left" valign="top">Folios Proveedor</th><td><table id="tfolios"><tr><th>Folio</th><th>Estatus Folio</th></tr><tr id="rfol" style="display:none;">
	<td align="center"><input type="hidden" name="cvefolios[]" value=""><input type="text" class="textField" size="10" name="folios[]" value=""></td>
	<td><select name="estatusfolios[]"><option value="0">Abierto</option>';
	echo '<option value="1">Cerrado</option>';
	echo '</select></td></tr>';
	$res1=mysql_query("SELECT * FROM reporte_lineas_folios WHERE reporte='".$row['cve']."'");
	while($row1=mysql_fetch_array($res1)){
		if($row1['estatus']==1){
			echo '<tr><td>'.$row1['folio'].'</td><td>Cerrado</td></tr>';
		}
		else{
			echo '<tr><td align="center"><input type="hidden" name="cvefolios[]" value="'.$row1['cve'].'"><input type="text" class="textField" size="10" name="folios[]" value="'.$row1['folio'].'"></td>
			<td><select name="estatusfolios[]"><option value="0">Abierto</option>';
			echo '<option value="1">Cerrado</option>';
			echo '</select></td></tr>';
		}
	}
	echo '</table><br><input type="button" class="textField" value="Agregar Folio Proveedor" onClick="agregarFolio()"></td></tr>';
	echo '</table>';
	echo '<h3>Observaciones</h3><table width="100%"><th>Observacion</th><th>Fecha</th><th>Usuario</th></tr>';
	$res1=mysql_query("SELECT * FROM reporte_lineas_obs WHERE reporte='".$row['cve']."' ORDER BY cve DESC");
	while($row1=mysql_fetch_array($res1)){
		rowb();
		echo '<td>'.$row1['obs'].'</td>';
		echo '<td align="center">'.$row1['fecha'].'</td>';
		echo '<td align="center">'.$array_usuario[$row1['usuario']].'</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '<script>
		function agregarRefaccion(){
			$("#trefacciones").append(\'<tr>\'+$("#rref").html()+\'</tr>\');
		}

		function agregarFolio(){
			$("#tfolios").append(\'<tr>\'+$("#rfol").html()+\'</tr>\');
		}
	</script>
';
}
if($_POST['cmd']==2){
	mysql_query("INSERT reporte_lineas SET estatus='A',fecha=CURDATE(), hora=CURTIME(), plaza='".$_POST['plazausuario']."',linea='".$_POST['reg']."',
		tipo='".$_POST['tipo_'.$_POST['reg']]."',observacion='".$_POST['obs_'.$_POST['reg']]."',fechaprogramacion='".$_POST['fechaprogramacion_'.$_POST['reg']]."',
		usuario='".$_POST['cveusuario']."',proveedor='".$_POST['proveedor_'.$_POST['reg']]."',nomenclatura='".$_POST['nomenclatura_'.$_POST['reg']]."'");
	echo '<script>window.close();</script>';
}

if($_POST['cmd']==1){
	echo '<table>';
	echo '
		<tr>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'mantenimiento_reporte_lineas.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Reporte de Lineas</td></tr>';
	echo '</table>';

	echo '<table><tr><th>Linea</th><th>Tipo</th><th>Observacion</th><th>Fecha de Programación</th><th>Proveedor</th><th>Motivo</th><th>Guardar</th></tr>';
	$res = mysql_query("SELECT a.* FROM cat_lineas a LEFT JOIN reporte_lineas b ON a.cve = b.linea AND b.fecha=CURDATE() WHERE a.plaza='".$_POST['plazausuario']."' AND ISNULL(b.cve) ORDER BY a.nombre");
	while($row=mysql_fetch_array($res)){
		rowb();
		echo '<td>'.$row['nombre'].'</td>';
		echo '<td align="center"><select name="tipo_'.$row['cve'].'" id="tipo_'.$row['cve'].'" onChange="cambiatipo('.$row['cve'].')"><option value="0">Seleccione</option>';
		foreach($array_tipo as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
		echo '</select></td>';
		echo '<td align="center"><textarea class="textField" name="obs_'.$row['cve'].'" id="obs_'.$row['cve'].'" cols="30" rows="3"></textarea></td>';
		echo '<td align="center"><input type="text" name="fechaprogramacion_'.$row['cve'].'" id="fechaprogramacion_'.$row['cve'].'" class="readOnly" size="12" value="" readOnly>&nbsp;<a style="display:none;" id="imgfecha'.$row['cve'].'" href="#" onClick="displayCalendar(document.forms[0].fechaprogramacion_'.$row['cve'].',\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td>';
		echo '<td align="center"><select name="proveedor_'.$row['cve'].'" id="proveedor_'.$row['cve'].'" disabled><option value="0">Seleccione</option>';
		foreach($array_proveedores as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
		echo '</select></td>';
		echo '<td align="center"><select name="nomenclatura_'.$row['cve'].'" id="nomenclatura_'.$row['cve'].'" disabled><option value="0">Seleccione</option>';
		foreach($array_nomenclaturas as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
		echo '</select></td>';
		echo '<td align="center"><input class="textField" type="button" value="Guardar" onClick="guardarLinea('.$row['cve'].')">';
		echo '</tr>';
	}
	echo '</table>';
	echo '<script>
		function cambiatipo(linea){
			if($("#tipo_"+linea).val()<=1){
				$("#fechaprogramacion_"+linea).val("");
				$("#imgfecha"+linea).hide();
				$("#proveedor_"+linea).attr("disabled", "disabled");
				$("#proveedor_"+linea).val("0");
				$("#nomenclatura_"+linea).attr("disabled", "disabled");
				$("#nomenclatura_"+linea).val("0");
			}
			else if($("#tipo_"+linea).val()==2){
				$("#fechaprogramacion_"+linea).val("");
				$("#imgfecha"+linea).show();
				$("#proveedor_"+linea).attr("disabled", "disabled");
				$("#proveedor_"+linea).val("0");
				$("#nomenclatura_"+linea).attr("disabled", "disabled");
				$("#nomenclatura_"+linea).val("0");
			}
			else if($("#tipo_"+linea).val()==3){
				$("#fechaprogramacion_"+linea).val("");
				$("#imgfecha"+linea).hide();
				$("#proveedor_"+linea).removeAttr("disabled");
				$("#proveedor_"+linea).val("0");
				$("#nomenclatura_"+linea).removeAttr("disabled");
				$("#nomenclatura_"+linea).val("0");
			}
		}

		function guardarLinea(linea){
			if($("#tipo_"+linea).val() == "0"){
				alert("Necesita seleccionar el tipo");
			}
			else if($("#obs_"+linea).val() == ""){
				alert("Necesita ingresar la observacion");
			}
			else if($("#tipo_"+linea).val() == "2" && $("#fechaprogramacion_"+linea).val() == ""){
				alert("Necesita seleccionar la fecha de programacion");
			}
			else if($("#tipo_"+linea).val() == "3" && $("#proveedor_"+linea).val() == "0"){
				alert("Necesita seleccionar el proveedor");
			}
			else if($("#tipo_"+linea).val() == "3" && $("#nomenclatura_"+linea).val() == "0"){
				alert("Necesita seleccionar el motivo");
			}
			else{
				$("#tipo_"+linea).parents("tr:first").hide();
				atcr("mantenimiento_reporte_lineas.php","_blank",2,linea);
			}
		}
	</script>';
}

if ($_POST['cmd']<1) {
	
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	//echo '<td><a href="#" onClick="atcr(\'mantenimiento_reporte_lineas.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
	echo '
		 </tr>';
	echo '</table>';
	echo '<table width="100%"><tr><td width="50%">';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza"><option value="">Todos</option>';
	foreach($array_plaza as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr style="display:none;"><td>Linea</td><td><select name="linea" id="linea"><option value="">Todos</option>';
	foreach($array_lineas as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Tipo</td><td><select name="tipo" id="tipo"><option value="">Todos</option>';
	foreach($array_tipo as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==3) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr';
	if($_SESSION['MantProvUsuario']>0) echo ' style="display:none;';
	echo '><td>Proveedor</td><td><select name="proveedor" id="proveedor"><option value="">Todos</option>';
	foreach($array_proveedores as $k=>$v){
		echo '<option value="'.$k.'"';
		if($_SESSION['MantProvUsuario']==$k) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Motivo</td><td><select name="nomenclatura" id="nomenclatura"><option value="">Todos</option>';
	foreach($array_nomenclaturas as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
	echo '</td><td id="concentrado"></td></tr></table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';


echo '
<Script language="javascript">

	function buscarRegistros(btn)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","mantenimiento_reporte_lineas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nomenclatura="+document.getElementById("nomenclatura").value+"&proveedor="+document.getElementById("proveedor").value+"&tipo="+document.getElementById("tipo").value+"&linea="+document.getElementById("linea").value+"&plaza="+document.getElementById("plaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
	buscarRegistros(0); //Realizar consulta de todos los registros al iniciar la forma.
		
	
	</Script>
	';

	
}


bottom();