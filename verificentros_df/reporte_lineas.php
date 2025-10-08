<?php

include("main.php");

$array_proveedores=array();
$res=mysql_query("SELECT * FROM mantenimiento.proveedores ORDER BY nombre");
while($row = mysql_fetch_array($res)) $array_proveedores[$row['cve']] = $row['nombre'];
$array_nomenclaturas=array();
$res=mysql_query("SELECT * FROM mantenimiento.nomenclatura ORDER BY nombre");
while($row = mysql_fetch_array($res)) $array_nomenclaturas[$row['cve']] = $row['nombre'];
$array_refacciones=array();
$res=mysql_query("SELECT * FROM mantenimiento.refacciones ORDER BY nombre");
while($row = mysql_fetch_array($res)) $array_refacciones[$row['cve']] = $row['nombre'];
$array_plazamantenimiento=array();
if($_POST['plazausuario']>0)
	$res=mysql_query("SELECT * FROM mantenimiento.plazas WHERE empresa='$empresamantenimiento' AND cve_aux='".$_POST['plazausuario']."' ORDER BY nombre");
else
	$res=mysql_query("SELECT * FROM mantenimiento.plazas WHERE empresa='$empresamantenimiento' ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_plazamantenimiento[$row['cve']] = $row['nombre'];
	if($_POST['plazausuario'] > 0) $plazamantenimiento=$row['cve'];
}
$array_lineas=array();
if($_POST['plazausuario']>0){
	$res=mysql_query("SELECT * FROM mantenimiento.lineas WHERE empresa='".$empresamantenimiento."' AND plaza='".$plazamantenimiento."' ORDER BY nombre");
}
else
	$res=mysql_query("SELECT * FROM mantenimiento.lineas WHERE empresa='".$empresamantenimiento."' ORDER BY nombre");
while($row = mysql_fetch_array($res)) $array_lineas[$row['cve']] = $row['nombre'];
$array_usuario=array();
$res=mysql_query("SELECT * FROM usuarios ORDER BY usuario");
while($row = mysql_fetch_array($res)) $array_usuario[$row['cve']] = $row['usuario'];
//$array_tipo=array(1=>'En Operacion', 2=>'Programada', 3=>'Correctivo');
$array_tipo=array(2=>'Programada', 3=>'Correctivo');
$array_estatus=array('A'=>'Activo','T'=>'Cerrado');
$rsMotivo=mysql_query("SELECT * FROM mantenimiento.refacciones ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_refacciones[$Motivo['cve']]=$Motivo['nombre'];
}
if($_POST['ajax']==1){
	$select= " SELECT a.* FROM mantenimiento.reporte_lineas a WHERE empresa='".$empresamantenimiento."' ";
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
				echo '<td>'.utf8_encode($array_plazamantenimiento[$row['plaza']]).'</td>';
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

if($_POST['ajax']==2){
	echo '<option value="0">Seleccione</option>';
	if($_POST['proveedor'] > 0){
		$res = mysql_query("SELECT cve, nombre FROM mantenimiento.usuarios WHERE proveedor = '".$_POST['proveedor']."' AND estatus!='I' ORDER BY nombre");
		while($row = mysql_fetch_array($res))
			echo '<option value="'.$row['cve'].'">'.utf8_encode($row['nombre']).'</option>';
	}
	exit();
}

if($_POST['ajax']==3){
	echo '<option value="0">Seleccione</option>';
	if($_POST['plaza'] > 0){
		$res = mysql_query("SELECT cve, nombre FROM mantenimiento.lineas WHERE empresa = '".$empresamantenimiento."' AND plaza='".$_POST['plaza']."' ORDER BY nombre");
		while($row = mysql_fetch_array($res))
			echo '<option value="'.$row['cve'].'">'.utf8_encode($row['nombre']).'</option>';
	}
	exit();
}


top($_SESSION);

if($_POST['cmd']==12){
	if($_POST['estatus']=='T'){
		mysql_query("UPDATE mantenimiento.reporte_lineas SET estatus='T',usuariocerro='-".$_POST['cveusuario']."',fechacerro=NOW() WHERE cve='".$_POST['reg']."'");
	}
	if($_POST['obs']!=""){
		mysql_query("INSERT mantenimiento.reporte_lineas_obs SET reporte='".$_POST['reg']."',obs='".$_POST['obs']."',usuario='-".$_POST['cveusuario']."',fecha=NOW()");
	}
	foreach($_POST['refacciones'] as $k=>$v){
		if($v>0){
			mysql_query("INSERT mantenimiento.reporte_lineas_refacciones SET reporte='".$_POST['reg']."',refaccion='".$v."',cantidad='".$_POST['cantrefacciones'][$k]."',usuario='-".$_POST['cveusuario']."',fecha=NOW()");		
		}
	}
	foreach($_POST['cvefolios'] as $k=>$v){
		$campos="";
		if($_POST['estatusfolios'][$k]==2){
			$campos.=" AND usuariocerro='".$_POST['cveusuario']."',fechacerro=NOW()";
		}
		if($v>0){
			if($_POST['folios'][$k]>0){
				mysql_query("UPDATE mantenimiento.reporte_lineas_folios SET folio='".$_POST['folios'][$k]."',estatus='".$_POST['estatusfolios'][$k]."'".$campos." WHERE cve='".$v."'");		
			}
			else{
				mysql_query("DELETE FROM mantenimiento.reporte_lineas_folios WHERE cve='".$v."'");
			}
		}
		elseif($_POST['folios'][$k]>0){
			mysql_query("INSERT mantenimiento.reporte_lineas_folios SET reporte='".$_POST['reg']."',folio='".$_POST['folios'][$k]."',estatus='".$_POST['estatusfolios'][$k]."',usuario='-".$_POST['cveusuario']."',fecha=NOW()".$campos);		
		}
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==11){
	$res = mysql_query("SELECT * FROM mantenimiento.reporte_lineas WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	$array_lineas=array();
	$res1=mysql_query("SELECT * FROM mantenimiento.lineas WHERE plaza='".$row['plaza']."' ORDER BY nombre");
	while($row1 = mysql_fetch_array($res1)) $array_lineas[$row1['cve']] = $row1['nombre'];
	echo '<table>
		<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show(); atcr(\'reporte_lineas.php\',\'\',\'12\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'reporte_lineas.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	echo '<table>';
	echo '<tr><td class="tableEnc">Edicion Reporte de Lineas #'.$_POST['reg'].'</td></tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><th align="left">Centro</th><td>'.$array_plazamantenimiento[$row['plaza']].'</td></tr>';
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
	echo '</select><td>Cantidad: <input type="text" size="10" name="cantrefacciones[]"></td></td></tr>';
	$res1=mysql_query("SELECT * FROM reporte_lineas_refacciones WHERE reporte='".$row['cve']."'");
	while($row1=mysql_fetch_array($res1)) echo '<tr><td>Cantidad: '.$array_refacciones[$row1['refaccion']].'</td><td>'.$row1['cantidad'].'</td></tr>';
	echo '</table><br><input type="button" class="textField" value="Agregar Refaccion" onClick="agregarRefaccion()"></td></tr>';
	echo '<tr><th align="left" valign="top">Folios Proveedor</th><td><table id="tfolios"><tr><th>Folio</th><th>Estatus Folio</th></tr><tr id="rfol" style="display:none;">
	<td align="center"><input type="hidden" name="cvefolios[]" value=""><input type="text" class="textField" size="10" name="folios[]" value=""></td>
	<td><select name="estatusfolios[]"><option value="0">Abierto</option>';
	echo '<option value="1">Cerrado</option>';
	echo '</select></td></tr>';
	$res1=mysql_query("SELECT * FROM mantenimiento.reporte_lineas_folios WHERE reporte='".$row['cve']."'");
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
	$res1=mysql_query("SELECT * FROM mantenimiento.reporte_lineas_obs WHERE reporte='".$row['cve']."' ORDER BY cve DESC");
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

if ($_POST['cmd']==2) {
	$camposi = json_decode($_POST['campos']);
	$campos="";
	foreach($camposi->camposi as $k=>$v){
		$campos.=",".$k."='".$v."'";
		$array_campo[$k]=$v;
	}

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE mantenimiento.mantenimiento_reportes 
						set empresa='".$camposi->camposi['empresa']."',no_reporte='".$_POST['no_reporte']."',tecnico='".$_POST['tecnico']."',operaciones='".$_POST['operaciones']."',
						obs='".$_POST['obs']."',tipo_cargo='".$_POST['tipo_cargo']."',monto='".$_POST['monto']."' where cve='".$_POST['reg']."'";
			$ejecutar = mysql_query($update) or die(mysql_error());

			foreach ($array_materiales as $k=>$v) { 
			
			$insert = " update mantenimiento.mantenimiento_reportes_detalle_material 

						set cantidad='".$_POST['cve_'.$k.'']."'  where cve_aux='".$_POST['reg']."' and cve_mat='".$k."'";
			$ejecutar = mysql_query($insert);
			}
	} else {
		$res = mysql_query("SELECT IFNULL(MAX(folio)+1,1) as folio FROM mantenimiento.reporte_lineas WHERE empresa='$empresamantenimiento'");
		$row = mysql_fetch_array($res);
		$folio = $row[0];
		$select = "INSERT mantenimiento.reporte_lineas SET folio='$folio',estatus='A',fecha=CURDATE(), hora=CURTIME(), plaza='".$_POST['plaza']."',linea='".$_POST['linea']."',
		tipo='".$_POST['tipo']."',observacion='".$_POST['obs']."',fechaprogramacion='".$_POST['fechaprogramacion']."',
		usuario='-".$_POST['cveusuario']."',proveedor='".$_POST['proveedor']."',nomenclatura='".$_POST['nomenclatura']."',
		empresa='".$empresamantenimiento."'";
		while(!$insert = mysql_query($select)){
			$folio++;
			$select = "INSERT mantenimiento.reporte_lineas SET folio='$folio',estatus='A',fecha=CURDATE(), hora=CURTIME(), plaza='".$_POST['plaza']."',linea='".$_POST['linea']."',
			tipo='".$_POST['tipo']."',observacion='".$_POST['obs']."',fechaprogramacion='".$_POST['fechaprogramacion']."',
			usuario='-".$_POST['cveusuario']."',proveedor='".$_POST['proveedor']."',nomenclatura='".$_POST['nomenclatura']."',
			empresa='".$empresamantenimiento."'";
		}
			$idd=mysql_insert_id();
			foreach($_POST['refacciones'] as $k=>$v){
				if($v>0){
					mysql_query("INSERT mantenimiento.reporte_lineas_refacciones SET reporte='".$idd."',refaccion='".$v."',cantidad='".$_POST['cantrefacciones'][$k]."',usuario='-".$_POST['cveusuario']."',fecha=NOW()");		
				}
			}
			
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==1){
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'reporte_lineas.php\',\'\',\'2\',\'0\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'reporte_lineas.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Reporte de Lineas</td></tr>';
	echo '</table>';

	echo '<table>';
	echo '<tr';
	if($_POST['plazausuario']>0) echo ' style="display:none;"';
	echo '><th align="left">Plaza</th><td><select name="plaza" id="plaza" onChange="traerLineas()">';
	if($_POST['plazausuario']==0) echo '<option value="0">Seleccione</option>';
	foreach($array_plazamantenimiento as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Linea</th><td><select name="linea" id="linea"><option value="0">Seleccione</option>';
	if($_POST['plazausuario']>0){
		$res = mysql_query("SELECT a.* FROM mantenimiento.lineas a WHERE a.empresa='".$empresamantenimiento."' AND a.plaza='$plazamantenimiento' ORDER BY a.nombre");
		while($row=mysql_fetch_array($res)){
			echo '<option value="'.$row['cve'].'">'.$row['nombre'].'</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Tipo</th><td><select name="tipo" id="tipo" onChange="cambiatipo()"><option value="0">Seleccione</option>';
	foreach($array_tipo as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
	echo '</select></td></tr>';
	echo '<tr><th align="left">Fecha de Programación</th><td><input type="text" name="fechaprogramacion" id="fechaprogramacion" class="readOnly" size="12" value="" readOnly>&nbsp;<a style="display:none;" id="imgfecha" href="#" onClick="displayCalendar(document.forms[0].fechaprogramacion,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	$array_tipo_cargo=Array(1=>"Con Cargo",2=>"Garantia",3=>"Poliza");
	echo '<tr><th align="left">Empresa</th><td><select name="proveedor" id="proveedor" class="textField" onChange="traerTecnicos()"><option value="">---Seleccione---</option>';
	foreach ($array_proveedores as $k=>$v) { 
	    echo '<option value="'.$k.'"';
	    echo'>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Reporte</th><td><input type="text" name="no_reporte" id="no_reporte" class="textField" size="" value=""></td></tr>';
	echo '<tr><th align="left">Tecnico</th><td><select name="tecnico" id="tecnico" class="textField"><option value="0">---Seleccione---</option>';
	echo '</select></td></tr>';
	echo '<tr><th align="left">Motivo</th><td><select name="nomenclatura" id="nomenclatura"><option value="0">Todos</option>';
	foreach($array_nomenclaturas as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Tipo Cargo</th><td><select name="tipo_cargo" id="tipo_cargo" onChange="cargo()"><option value="">---Seleccione---</option>';
	foreach ($array_tipo_cargo as $k=>$v) { 
		echo '<option value="'.$k.'"';
		echo'>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr class="monto_" style="display:none;"><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="textField" size="" value=""></td></tr>';

	echo '<tr><th align="left" valign="top">Refacciones</th><td><table id="trefacciones"><tr id="rref" style="display:none;"><td><select name="refacciones[]"><option value="0">Seleccione</option>';
	foreach($array_refacciones as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
	echo '</select></td><td>Cantidad: <input type="text" size="10" name="cantrefacciones[]"></td></tr>';
	echo '</table><br><input type="button" class="textField" value="Agregar Refaccion" onClick="agregarRefaccion()"></td></tr>';

		
		
	echo '<tr><th align="left">Operaciones</th><td><textarea cols="50" rows="5" name="operaciones" id="operaciones"></textarea></td></tr>';
	echo '<tr><th align="left">Observaciones</th><td><textarea cols="50" rows="5" name="obs" id="obs"></textarea></td></tr>';
		
	echo '</table>';
	echo '<script>
		function traerTecnicos(){
			$.ajax({
				url: "reporte_lineas.php",
				type: "POST",
				async: false,
				data: {
					ajax: 2,
					proveedor: document.forma.proveedor.value,
				},
				success: function(data) {
					$("#tecnico").html(data);
				}
			});
		}

		function traerLineas(){
			$.ajax({
				url: "reporte_lineas.php",
				type: "POST",
				async: false,
				data: {
					ajax: 3,
					plaza: document.forma.plaza.value,
					plazausuario: document.forma.plazausuario.value,
				},
				success: function(data) {
					$("#linea").html(data);
				}
			});
		}

		function agregarRefaccion(){
			$("#trefacciones").append(\'<tr>\'+$("#rref").html()+\'</tr>\');
		}

		function cambiatipo(){
			if($("#tipo").val()<=1){
				$("#fechaprogramacion").val("");
				$("#imgfecha").hide();
			}
			else if($("#tipo").val()==2){
				$("#fechaprogramacion").val("");
				$("#imgfecha").show();
			}
			else if($("#tipo").val()==3){
				$("#fechaprogramacion").val("");
				$("#imgfecha").hide();
			}
		}
	
	
		function cargo(){
	
			if(document.getElementById("tipo_cargo").value==1 ){
				$(".monto_").show();
			}
			if(document.getElementById("tipo_cargo").value==2 || document.getElementById("tipo_cargo").value==3){
				$(".monto_").hide();
			}
		}
	</script>';
}

if ($_POST['cmd']<1) {
	
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	if($_POST['plazausuario'] > 0)
		echo '<td><a href="#" onClick="atcr(\'reporte_lineas.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
	echo '
		 </tr>';
	echo '</table>';
	echo '<table width="100%"><tr><td width="50%">';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	if($_POST['plazausuario']>0){
		echo '<input type="hidden" name="plaza" id="plaza" value="'.$plazamantenimiento.'">';
	}
	else{
		echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza"><option value="">Todos</option>';
		foreach($array_plazamantenimiento as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
	}
	echo '<tr><td>Linea</td><td><select name="linea" id="linea"><option value="">Todos</option>';
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
	echo '<tr><td>Proveedor</td><td><select name="proveedor" id="proveedor"><option value="">Todos</option>';
	foreach($array_proveedores as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
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
			objeto.open("POST","reporte_lineas.php",true);
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