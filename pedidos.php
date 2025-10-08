<?php
include("main.php");
$repartidor=0;
$array_clientes = array();
$array_unidad = array();
$array_usuario = array();
$array_delegacion = array();
$array_personal = array();
$array_tipo_ncargo = array();
$array_zona=array();
$array_medio_entero=array();



$res = mysql_query("SELECT * FROM clientes ORDER BY nombre");
while($row = mysql_fetch_array($res))
{
	$array_clientes[$row['cve']] = $row['nombre'];
}

$res = mysql_query("SELECT * FROM medios_entero ORDER BY nombre");
while($row = mysql_fetch_array($res))
{
	$array_medio_entero[$row['cve']] = $row['nombre'];
}

$res = mysql_query("SELECT * FROM delegacion ORDER BY nombre");
while($row = mysql_fetch_array($res))
{
	$array_delegacion[$row['cve']] = $row['nombre'];
}

$res = mysql_query("SELECT * FROM personal WHERE puesto=1  ORDER BY nombre");
while($row = mysql_fetch_array($res))
{
	$array_personal[$row['cve']] = $row['nombre'];
	if($row['corporativo'] == 1) $array_colorpersonal[$row['cve']] = '#00FF00';
	elseif($row['outsourcing'] == 1) $array_colorpersonal[$row['cve']] = '#FF0000';
	else $array_colorpersonal[$row['cve']] = '#FFFF00';
}

$res = mysql_query("SELECT * FROM tipo_ncargo ORDER BY nombre");
while($row = mysql_fetch_array($res))
{
	$array_tipo_ncargo[$row['cve']] = $row['nombre'];
}


$res = mysql_query("SELECT * FROM usuarios ORDER BY usuario");
while($row = mysql_fetch_array($res))
{
	$array_usuario[$row['cve']] = $row['usuario'];
}

$array_estatus_detalle = array('Por entregar', 'Entregado');
$array_estatus_pago = array('Pendiente de Pagar', 'Pagado');
$array_tipo_pago = array(1=>"Efectivo", 2=>"Cheque", 3=>"Transferencia");
$array_pagar_en = array(1=>"Recoleccion", 2=>"Entrega",3=>'Transferencia');
$res = mysql_query("SELECT * FROM estatus_pedido ORDER BY nombre");
while($row = mysql_fetch_array($res))
{
	$array_estatus_pedido[$row['cve']] = $row['nombre'];
}

if($_POST['cmd']==100) {
		//Listado de plazas
		$select= " SELECT * FROM pedidos WHERE fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		if($_POST['cliente']!="") $select .= " AND cliente='".$_POST['cliente']."'";
		$select.=" ORDER BY cve DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		$nivel = nivelUsuario();
		if(mysql_num_rows($res)>0) 
		{
			header("Content-type: application/vnd.ms-excel; name='excel'");
			header("Content-Disposition: filename=Ncargos.xls");
			header("Pragma: no-cache");
			header("Expires: 0");
			echo '<h1>Ncargos del dia '.mostrar_fecha($_POST['fecha_ini']).' al '.mostrar_fecha($_POST['fecha_fin']).'</h1>';
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="10">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
			echo '<th>Folio</th><th>Fecha</th><th>Cliente</th><th>Importe</th><th>Pagar en</th><th>Tipo de Pago</th><th>Estatus Pago</th><th>Cantidad Pagada</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$total=$total1=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>&nbsp;';
				if($row['estatus'] == 3){
					echo 'CANCELADO';
					$row['monto'] = 0;
				}
				else{
					if($rw['estatus']==2) echo '<br>Cerrado';
				}
				echo '&nbsp;</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities(mostrar_fecha($row['fecha']).' '.$row['hora']).'</td>';
				echo '<td align="left">'.htmlentities($array_clientes[$row['cliente']]).'</td>';
				echo '<td align="right">'.number_format($row['monto'], 2).'</td>';
				echo '<td align="left">'.htmlentities($array_pagar_en[$row['pagar_en']]).'</td>';
				echo '<td align="left">'.htmlentities($array_tipo_pago[$row['tipo_pago']]).'</td>';
				echo '<td align="left">'.htmlentities($array_estatus_pago[$row['estatus_pago']]).'</td>';
				echo '<td align="right">'.number_format($row['cantidad_pagada'], 2).'</td>';
				echo '</tr>';
				$total += $row['monto'];
				$total1 += $row['cantidad_pagada'];
			}
			echo '	
				<tr>
				<td colspan="4" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td bgcolor="#E9F2F8" align="right">'.number_format($total,2).'</td>
				<td bgcolor="#E9F2F8" colspan="3">&nbsp;</td>
				<td bgcolor="#E9F2F8" align="right">'.number_format($total1,2).'</td>
				<td bgcolor="#E9F2F8">&nbsp;</td>
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

if ($_POST['cmd']==10) {
		
	//Formulario 
	echo '<h1>Ncargo # '.$_POST['reg'].'</h1>';
	$res = mysql_query("SELECT * FROM pedidos WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	$array_contactos=array();
	$array_direcciones=array();
	$resC = mysql_query("SELECT * FROM clientes WHERE cve='".$row['cliente']."'");
	$rowC=mysql_fetch_array($resC);
	$res1=mysql_query("SELECT * FROM clientes_contacto WHERE cliente='".$row['cliente']."' ORDER BY nombre");
	while($row1=mysql_fetch_array($res1)){
		$array_contactos[$row1['cve']] = $row1['nombre'].' (Tel: '.$row1['telefono'].')';
	}
	$res1=mysql_query("SELECT * FROM clientes_direccion WHERE cliente='".$row['cliente']."' ORDER BY direccion");
	while($row1=mysql_fetch_array($res1)){
		$array_direcciones[$row1['cve']] = $row1['direccion'].'';
	}
		
	echo '<table>';
	echo '<tr><th align="left">Cliente</th><td>'.$array_clientes[$row['cliente']].'</td></tr>';
	echo '<tr><th align="left">Sin IVA</th><td>'.$array_nosi[$row['sin_iva']].'</td></tr>';
	echo '<tr><th align="left">Multientrega con descuento</th><td>'.$array_nosi[$row['multientrega_descuento']].'</td></tr>';
	echo '<tr><th align="left">Total Pedido</th><td>'.number_format($row['monto'],2).'</td></tr>';
	echo '</table>';
	echo '<div width="100%" id="encargos">';
	$num_cargo = 0;
	$res1 = mysql_query("SELECT * FROM pedidos_detalles WHERE pedido = '".$_POST['reg']."'");
	while($row1=mysql_fetch_array($res1)){
		echo '<hr>';
		echo '<table id="tabla_'.$num_cargo.'" width="100%"><tr><td align="left" width="50%" valign="top"><h1>Recoleccion</h1>';
		echo '<table>';
		echo '<tr><th align="left">Contacto</th><td>'.$array_contactos[$row1['contacto']].'</td></tr>';
		echo '<tr><th align="left">Delegacion</th><td>'.$array_delegacion[$row1['delegacion_recoleccion']].'</td></tr>';
		echo '<tr><th align="left">Direccion</th><td>'.$array_direcciones[$row1['direccion_recoleccion']].'</td></tr>';
		echo '<tr><th align="left">Fecha Recoleccion</th><td>'.mostrar_fecha($row1['fecha_recoleccion']).'</td></tr>';
		echo '<tr><th align="left">Hora Recoleccion</th><td>'.substr($row1['hora_recoleccion'],0,5).'</td></tr>';
		echo '<tr><th align="left">Ncargado</th><td>'.$array_personal[$row1['mensajero']].'</td></tr>';
		echo '<tr><th align="left">Ncargo</th><td>'.$row1['obs_recoleccion'].'</td></tr>';
		echo '</table>';
		echo '</td><td><h1>Entrega</h1><table>';
		echo '<tr><th align="left">Delegacion</th><td>'.$array_delegacion[$row1['delegacion_entrega']].'</td></tr>';
		echo '<tr><th align="left">Direccion</th><td>'.$array_direcciones[$row1['direccion_entrega']].'</td></tr>';
		echo '<tr><th align="left">Fecha Entrega</th><td>'.mostrar_fecha($row1['fecha_entrega']).'</td></tr>';
		echo '<tr><th align="left">Hora Entrega</th><td>'.substr($row1['hora_entrega'],0,5).'</td></tr>';
		echo '<tr><th align="left">Estatus Ncargo</th><td>'.$array_estatus_detalle[$row1['estatus_ncargo']].'</td></tr>';
		echo '<tr><th align="left">Recibido por</th><td>'.$row1['recibio'].'</td></tr>';
		echo '<tr><th align="left">Observaciones</th><td>'.$row1['obs_entrega'].'</td></tr>';
		echo '<tr><th align="left">Tipo </th><td>'.$array_tipo_ncargo[$row1['tipo_ncargo']].'</td></tr>';
		echo '<tr><th align="left">Servicio</th><td>';
		
		$res2 = mysql_query("SELECT a.cve, a.nombre FROM zonas a WHERE a.cve='".$row1['zona']."' ORDER BY a.nombre");
		while($row2=mysql_fetch_array($res2)){
			echo $row2['nombre'];
		}
		echo '</td></tr>';
		echo '<tr><th align="left">Costo</th><td>'.number_format($row1['subtotal'],2).'</td></tr>';
		echo '<tr><th align="left">IVA</th><td>'.number_format($row1['iva'],2).'</td></tr>';
		echo '<tr><th align="left">Total</th><td>'.number_format($row1['total'],2).'</td></tr>';
		echo '<tr><td colspan="2">&nbsp;</td></tr>';
		echo '<tr><th align="left">Tipo Pago</th><td>'.$array_tipo_pago[$row1['tipo_pago']].'</td></tr>';
		echo '<tr><th align="left">Estatus Pago</th><td>'.$array_estatus_pago[$row1['estatus_pago']].'</td></tr>';
		echo '<tr><th align="left">Cantidad Pagada</th><td>'.number_format($row1['cantidad_pagada'],2).'</td></tr>';
		echo '</table></td></tr>';
		echo '</table>';
		$num_cargo++;
	}
	
	echo '</div>';
}

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM pedidos WHERE fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		if($_POST['cliente']!="") $select .= " AND cliente='".$_POST['cliente']."'";
		$select.=" ORDER BY cve DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		$nivel = nivelUsuario();
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="10">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
			echo '<th>Folio</th><th>Fecha</th><th>Cliente</th><th>Importe</th><th>Pagar en</th><th>Tipo de Pago</th><th>Estatus Pago</th><th>Cantidad Pagada</th><th>Usuario</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$total=$total1=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>&nbsp;';
				if($row['estatus'] == 3){
					echo 'CANCELADO';
					$row['monto'] = 0;
				}
				else{
					echo '<a href="#" onClick="atcr(\'pedidos.php\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$row['cve'].'"></a>';
					if($nivel > 2 && $row['fecha'] == fechaLocal() && $row['estatus']==1){
						echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el pedido?\')) atcr(\'pedidos.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
					}
					if($rw['estatus']==2) echo '<br>Cerrado';
				}
				echo '&nbsp;</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities(mostrar_fecha($row['fecha']).' '.$row['hora']).'</td>';
				echo '<td align="left">'.htmlentities($array_clientes[$row['cliente']]).'</td>';
				echo '<td align="right">'.number_format($row['monto'], 2).'</td>';
				echo '<td align="left">'.htmlentities($array_pagar_en[$row['pagar_en']]).'</td>';
				echo '<td align="left">'.htmlentities($array_tipo_pago[$row['tipo_pago']]).'</td>';
				echo '<td align="left">'.htmlentities($array_estatus_pago[$row['estatus_pago']]).'</td>';
				echo '<td align="right">'.number_format($row['cantidad_pagada'], 2).'</td>';
				echo '<td align="left">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
				$total += $row['monto'];
				$total1 += $row['cantidad_pagada'];
			}
			echo '	
				<tr>
				<td colspan="4" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td bgcolor="#E9F2F8" align="right">'.number_format($total,2).'</td>
				<td bgcolor="#E9F2F8" colspan="3">&nbsp;</td>
				<td bgcolor="#E9F2F8" align="right">'.number_format($total1,2).'</td>
				<td bgcolor="#E9F2F8">&nbsp;</td>
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

if($_POST['ajax'] == 2){
	$array_contactos = array();
	$array_direcciones = array();
	if($_POST['cliente'] > 0){
		$res = mysql_query("SELECT * FROM clientes WHERE cve='".$_POST['cliente']."'");
		$row=mysql_fetch_array($res);
		$res1=mysql_query("SELECT * FROM clientes_contacto WHERE cliente='".$_POST['cliente']."' ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			$array_contactos[$row1['cve']] = array(utf8_encode($row1['nombre']),$row1['telefono'],$row1['email']);
		}
		$res1=mysql_query("SELECT * FROM clientes_direccion WHERE cliente='".$_POST['cliente']."' ORDER BY direccion");
		while($row1=mysql_fetch_array($res1)){
			$array_direcciones[$row1['cve']] = utf8_encode($row1['direccion']).'';
			$array_dir_delegacion[$row1['cve']] = $row1['delegacion'].'';
		}
		
	}
	$num_cargo = $_POST['num_cargo'];
	echo '<hr class="crecoleccion_'.$num_cargo.'">';
	echo '<span class="crecoleccion_'.$num_cargo.'" style="cursor:pointer" onClick="quitar_recoleccion('.$num_cargo.')"><img src="images/basura.gif">Quitar<br></span>';
	echo '<table class="crecoleccion_'.$num_cargo.'" id="tabla_'.$num_cargo.'" width="100%"><tr><td align="left" width="50%" valign="top">';
	/* echo '<h1>Recoleccion</h1>';
	echo '<table>';
	echo '<tr><th align="left">Contacto</th><td><select name="contacto['.$num_cargo.']" id="contacto_'.$num_cargo.'" class="clase_contacto" onChange="mostrar_datos_contacto('.$num_cargo.')"><option value="0">Seleccione</option>';
	foreach($array_contactos as $k=>$v) echo '<option value="'.$k.'" tel="'.$v[1].'" mail="'.$v[2].'">'.$v[0].'</option>';
	echo '</select><span style="cursor:pointer;" onClick="agregar_contacto(\'contacto_'.$num_cargo.'\')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
	echo '<tr><th align="left">Telefono Contacto</th><td><input type="text" id="telefono_contacto_'.$num_cargo.'" class="readOnly" readOnly></td></tr>';
	echo '<tr><th align="left">Email Contacto</th><td><input type="text" id="email_contacto_'.$num_cargo.'" class="readOnly" readOnly></td></tr>';
	echo '<tr><th align="left">Direccion</th><td><select name="direccion_recoleccion['.$num_cargo.']" id="direccion_recoleccion_'.$num_cargo.'" class="clase_direccion" onChange="seleccionar_delegacion(\'recoleccion\','.$num_cargo.')"><option value="0" delegacion="0">Seleccione</option>';
	foreach($array_direcciones as $k=>$v) echo '<option value="'.$k.'" delegacion="'.$array_dir_delegacion[$k].'">'.$v.'</option>';
	echo '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_recoleccion_'.$num_cargo.'\',\'recoleccion\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
	echo '<tr><th align="left">Delegacion</th><td><select name="delegacion_recoleccion['.$num_cargo.']" id="delegacion_recoleccion_'.$num_cargo.'"><option value="0">Seleccione</option>';
	//foreach($array_delegacion as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
	echo '</select></td></tr>';
	echo '<tr><th align="left">Fecha Recoleccion</th><td>'.campo_fecha('fecha_recoleccion['.$num_cargo.']', 'fecha_recoleccion_'.$num_cargo, '', 'fechas_recoleccion').'</td></tr>';
	echo '<tr style="display:none;"><th align="left">Hora Recoleccion</th><td><select name="hora_recoleccion['.$num_cargo.']" id="hora_recoleccion_'.$num_cargo.'">';
	for($i=0;$i<=23;$i++) echo '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
	echo '</select>:<select name="min_recoleccion['.$num_cargo.']" id="min_recoleccion_'.$num_cargo.'">';
	for($i=0;$i<=59;$i++) echo '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
	echo '</select></td></tr>';
	echo '<tr><th align="left">Ncargado</th><td><select name="mensajero['.$num_cargo.']" id="mensajero_'.$num_cargo.'"><option value="0">Por asignar</option>';
	foreach($array_personal as $k=>$v) echo '<option value="'.$k.'" style="color:'.$array_colorpersonal[$k].';">'.$v.'</option>';
	echo '</select></td></tr>';
	echo '<tr><th align="left">Ncargo</th><td><textarea cols="30" rows="3" name="obs_recoleccion['.$num_cargo.']" id="obs_recoleccion_'.$num_cargo.'"></textarea></td></tr>';
	echo '</table>';*/
	echo '</td><td><h1>Entrega</h1><table>';
	echo '<tr><th align="left">Direccion</th><td><select name="direccion_entrega['.$num_cargo.']" id="direccion_entrega_'.$num_cargo.'" class="clase_direccion" onChange="seleccionar_delegacion(\'entrega\','.$num_cargo.')"><option value="0" delegacion="0">Seleccione</option>';
	foreach($array_direcciones as $k=>$v) echo '<option value="'.$k.'" delegacion="'.$array_dir_delegacion[$k].'">'.$v.'</option>';
	echo '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_entrega_'.$num_cargo.'\',\'entrega\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
	echo '<tr><th align="left">Delegacion</th><td><select name="delegacion_entrega['.$num_cargo.']" id="delegacion_entrega_'.$num_cargo.'"><option value="0">Seleccione</option>';
	//foreach($array_delegacion as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
	echo '</select></td></tr>';
	echo '<tr><th align="left">Fecha Entrega</th><td>'.campo_fecha('fecha_entrega['.$num_cargo.']', 'fecha_entrega_'.$num_cargo, '', 'fechas_entrega').'</td></tr>';
	echo '<tr><th align="left">Hora Entrega</th><td><select name="hora_entrega['.$num_cargo.']" id="hora_entrega_'.$num_cargo.'">';
	for($i=0;$i<=23;$i++) echo '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
	echo '</select>:<select name="min_entrega['.$num_cargo.']" id="min_entrega_'.$num_cargo.'">';
	for($i=0;$i<=50;$i+=10) echo '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
	echo '</select></td></tr>';
	echo '<tr style="display:none;"><th align="left">Estatus Ncargo</th><td><select name="estatus_ncargo['.$num_cargo.']" id="estatus_ncargo_'.$num_cargo.'">';
	foreach($array_estatus_detalle as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
	echo '</select></td></tr>';
	echo '<tr style="display:none;"><th align="left">Recibido por</th><td><input type="text" class="textField" name="recibio['.$num_cargo.']" id="recibio_'.$num_cargo.'" value=""></td></tr>';
	echo '<tr><th align="left">Observaciones</th><td><textarea cols="30" rows="3" name="obs_entrega['.$num_cargo.']" id="obs_entrega_'.$num_cargo.'"></textarea></td></tr>';
	echo '<tr><th align="left">Tipo </th><td><select name="tipo_ncargo['.$num_cargo.']" id="tipo_ncargo_'.$num_cargo.'" class="ncargo clase_tipo_ncargo" onChange="traer_zonas('.$num_cargo.')">';
	echo '<option value="0">Seleccione</option>';
	foreach($array_tipo_ncargo as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==1) echo ' selected';
		echo '>'.utf8_encode($v).'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Servicio</th><td><select name="zona['.$num_cargo.']" id="zona_'.$num_cargo.'" class="clase_zona" numcargo="'.$num_cargo.'" onChange="mostrar_costo('.$num_cargo.')">';
	echo '<option value="0" costo="0">Seleccione</option>';
	echo '</select></td></tr>';
	echo '<tr><th align="left">Costo</th><td><input type="text" class="readOnly" id="subtotal_'.$num_cargo.'" name="subtotal['.$num_cargo.']" size="10" value="" readOnly></td></tr>';
	echo '<tr><th align="left">IVA</th><td><input type="text" class="readOnly" id="iva_'.$num_cargo.'" name="iva['.$num_cargo.']" size="10" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Total</th><td><input type="text" class="readOnly totales" id="total_'.$num_cargo.'" name="total['.$num_cargo.']" size="10" value="" readOnly></td></tr>';
	echo '<tr><td colspan="2">&nbsp;</td></tr>';
	/*echo '<tr><th align="left">Tipo Pago</th><td><select name="tipo_pago['.$num_cargo.']" id="tipo_pago_'.$num_cargo.'"><option value="0">Seleccione</option>';
	foreach($array_tipo_pago as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
	echo '</select></td></tr>';
	echo '<tr><th align="left">Estatus Pago</th><td><select name="tipo_pago['.$num_cargo.']" id="tipo_pago_'.$num_cargo.'">';
	foreach($array_estatus_pago as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
	echo '</select></td></tr>';
	echo '<tr><th align="left">Cantidad Pagada</th><td><input type="text" class="textField" id="cantidad_pagada_'.$num_cargo.'" name="cantidad_pagada['.$num_cargo.']" size="10" value="" onKeyUp="validar_cantidad_pagada('.$num_cargo.')"></td></tr>';*/
	echo '</table></td></tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==13){
	if($_POST['cliente'] > 0){
		$res = mysql_query("SELECT * FROM clientes WHERE cve='".$_POST['cliente']."'");
		$rowC=mysql_fetch_array($res);
		$array_contactos = array();
		$array_direcciones = array();
		$res1=mysql_query("SELECT * FROM clientes_contacto WHERE cliente='".$_POST['cliente']."' ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			$resultado['contactos'] .= '<option value="'.$row1['cve'].'" tel="'.$row1['telefono'].'" mail="'.$row1['email'].'">'.$row1['nombre'].'</option>';
			$array_contactos[$row1['cve']] = array($row1['nombre'],$row1['telefono'],$row1['email']);
		}
		$res1=mysql_query("SELECT * FROM clientes_direccion WHERE cliente='".$_POST['cliente']."' ORDER BY direccion");
		while($row1=mysql_fetch_array($res1)){
			$resultado['direcciones'] .= '<option value="'.$row1['cve'].'" delegacion="'.$row1['delegacion'].'">'.$row1['direccion'].'</option>';
			$array_direcciones[$row1['cve']] = $row1['direccion'].'';
			$array_dir_delegacion[$row1['cve']] = $row1['delegacion'].'';
		}
		$resultado['tipo_cliente'] = '<option value="0">Seleccione</option>';
		foreach($array_tipo_cliente as $k=>$v){
			$resultado['tipo_cliente'] .= '<option value="'.$k.'"';
			if($rowC['tipo_cliente'] == $k) $resultado['tipo_cliente'] .= ' selected'; 
			$resultado['tipo_cliente'] .= '>'.$v.'</option>';
		}
		$num_cargo = 0;
		$res1 = mysql_query("SELECT a.cve FROM pedidos a INNER JOIN pedidos b ON a.cve = b.pedido WHERE a.cliente='".$_POST['cliente']."' AND a.estatus!='C' ORDER BY a.cve DESC LIMIT 1");
		if($row1 = mysql_fetch_assoc($res1)){
			$res1 = mysql_query("SELECT * FROM pedidos_detalles WHERE pedido = '".$row1['cve']."' LIMIT 1");
			while($row1=mysql_fetch_array($res1)){
				$resultado['ultimo_ncargo'] .= '<hr class="crecoleccion_'.$num_cargo.'" >';
				$resultado['ultimo_ncargo'] .= '<table class="crecoleccion_'.$num_cargo.'"  id="tabla_'.$num_cargo.'" width="100%"><tr><td align="left" width="50%" valign="top">';
				$resultado['ultimo_ncargo'] .= '<h1>Recoleccion</h1>';
				$resultado['ultimo_ncargo'] .= '<table>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Contacto</th><td><select name="contacto['.$num_cargo.']" id="contacto_'.$num_cargo.'" class="clase_contacto" onChange="mostrar_datos_contacto('.$num_cargo.')"><option value="0">Seleccione</option>';
				foreach($array_contactos as $k=>$v){ 
					$resultado['ultimo_ncargo'] .= '<option value="'.$k.'" tel="'.$v[1].'" mail="'.$v[2].'"';
					if($k==$row1['contacto']) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .=  '>'.$v[0].'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_contacto(\'contacto_'.$num_cargo.'\')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Telefono Contacto</th><td><input type="text" id="telefono_contacto_'.$num_cargo.'" value="'.$array_contactos[$row1['contacto']][1].'" class="readOnly" readOnly></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Email Contacto</th><td><input type="text" id="email_contacto_'.$num_cargo.'" value="'.$array_contactos[$row1['contacto']][2].'" class="readOnly" readOnly></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Direccion</th><td><select name="direccion_recoleccion['.$num_cargo.']" id="direccion_recoleccion_'.$num_cargo.'" onChange="seleccionar_delegacion(\'recoleccion\','.$num_cargo.')" class="clase_direccion" delegacion="0"><option value="0">Seleccione</option>';
				foreach($array_direcciones as $k=>$v){
					$resultado['ultimo_ncargo'] .= '<option value="'.$k.'" delegacion="'.$array_dir_delegacion[$k].'"';
					if($row1['direccion_recoleccion'] == $k) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.$v.'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_recoleccion_'.$num_cargo.'\',\'recoleccion\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Delegacion</th><td><select name="delegacion_recoleccion['.$num_cargo.']" id="delegacion_recoleccion_'.$num_cargo.'"><option value="0">Seleccione</option>';
				foreach($array_delegacion as $k=>$v){
					if($row1['delegacion_recoleccion'] == $k){
						$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
						$resultado['ultimo_ncargo'] .= ' selected';
						$resultado['ultimo_ncargo'] .= '>'.$v.'</option>';
					}
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				if($row1['fecha_recoleccion'] == '0000-00-00') $row1['fecha_recoleccion'] = '';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Fecha Recoleccion</th><td>'.campo_fecha('fecha_recoleccion['.$num_cargo.']', 'fecha_recoleccion_'.$num_cargo, $row1['fecha_recoleccion'], 'fechas_recoleccion').'</td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Hora Recoleccion</th><td><select name="hora_recoleccion['.$num_cargo.']" id="hora_recoleccion_'.$num_cargo.'">';
				for($i=0;$i<=23;$i++){
					$resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'"';
					if($i==intval(substr($row1['hora_recoleccion'],0,2))) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.sprintf('%02s',$i).'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select>:<select name="min_recoleccion['.$num_cargo.']" id="min_recoleccion_'.$num_cargo.'">';
				for($i=0;$i<=50;$i+=10){
					$resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'"';
					if($i==intval(substr($row1['hora_recoleccion'], 3, 2))) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.sprintf('%02s',$i).'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Ncargado</th><td><select name="mensajero['.$num_cargo.']" id="mensajero_'.$num_cargo.'"><option value="0">Por asignar</option>';
				foreach($array_personal as $k=>$v){
					$resultado['ultimo_ncargo'] .= '<option value="'.$k.'" style="color:'.$array_colorpersonal[$k].';"';
					if($row1['mensajero'] == $k) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.$v.'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Ncargo</th><td><textarea cols="30" rows="3" name="obs_recoleccion['.$num_cargo.']" id="obs_recoleccion_'.$num_cargo.'">'.$row1['obs_recoleccion'].'</textarea></td></tr>';
				$resultado['ultimo_ncargo'] .= '</table>';
				$resultado['ultimo_ncargo'] .= '</td><td><h1>Entrega</h1><table>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Direccion</th><td><select name="direccion_entrega['.$num_cargo.']" id="direccion_entrega_'.$num_cargo.'" class="clase_direccion" onChange="seleccionar_delegacion(\'entrega\','.$num_cargo.')"><option value="0" delegacion="0">Seleccione</option>';
				foreach($array_direcciones as $k=>$v){
					$resultado['ultimo_ncargo'] .= '<option value="'.$k.'" delegacion="'.$array_dir_delegacion[$k].'"';
					if($row1['direccion_entrega'] == $k) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.$v.'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_entrega_'.$num_cargo.'\',\'entrega\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Delegacion</th><td><select name="delegacion_entrega['.$num_cargo.']" id="delegacion_entrega_'.$num_cargo.'"><option value="0">Seleccione</option>';
				foreach($array_delegacion as $k=>$v){
					if($row1['delegacion_entrega'] == $k){
						$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
						$resultado['ultimo_ncargo'] .= ' selected';
						$resultado['ultimo_ncargo'] .= '>'.$v.'</option>';
					}
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				if($row1['fecha_entrega'] == '0000-00-00') $row1['fecha_entrega'] = '';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Fecha Entrega</th><td>'.campo_fecha('fecha_entrega['.$num_cargo.']', 'fecha_entrega_'.$num_cargo, $row1['fecha_entrega'], 'fechas_entrega').'</td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Hora Entrega</th><td><select name="hora_entrega['.$num_cargo.']" id="hora_entrega_'.$num_cargo.'">';
				for($i=0;$i<=23;$i++){
					$resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'"';
					if($i==intval(substr($row1['hora_entrega'],0,2))) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.sprintf('%02s',$i).'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select>:<select name="min_entrega['.$num_cargo.']" id="min_entrega_'.$num_cargo.'">';
				for($i=0;$i<=50;$i+=10){
					$resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'"';
					if($i==intval(substr($row1['hora_entrega'], 3, 2))) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.sprintf('%02s',$i).'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr';
				if($nivelusuario < 3) $resultado['ultimo_ncargo'] .= ' style="display:none;"';
				$resultado['ultimo_ncargo'] .= '><th align="left">Estatus Ncargo</th><td><select name="estatus_ncargo['.$num_cargo.']" id="estatus_ncargo_'.$num_cargo.'">';
				foreach($array_estatus_detalle as $k=>$v){
					$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
					if($row1['estatus_ncargo'] == $k) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.$v.'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr';
				if($nivelusuario < 3) $resultado['ultimo_ncargo'] .= ' style="display:none;"';
				$resultado['ultimo_ncargo'] .= '><th align="left">Recibido por</th><td><input type="text" class="textField" name="recibio['.$num_cargo.']" id="recibio_'.$num_cargo.'" value="'.$row1['recibio'].'"></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Observaciones</th><td><textarea cols="30" rows="3" name="obs_entrega['.$num_cargo.']" id="obs_entrega_'.$num_cargo.'">'.$row1['obs_entrega'].'</textarea></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Tipo </th><td><select name="tipo_ncargo['.$num_cargo.']" id="tipo_ncargo_'.$num_cargo.'" class="ncargo clase_tipo_ncargo" onChange="traer_zonas('.$num_cargo.')">';
				if($rowC['servicio'] == 0)
					$resultado['ultimo_ncargo'] .= '<option value="0">Seleccione</option>';
				foreach($array_tipo_ncargo as $k=>$v){
					$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
					if($row1['tipo_ncargo'] == $k || ($k==1 && $row1['tipo_ncargo'] == 0)) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.$v.'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Servicio</th><td><select name="zona['.$num_cargo.']" id="zona_'.$num_cargo.'" class="clase_zona" numcargo="'.$num_cargo.'" onChange="mostrar_costo('.$num_cargo.')">';
				$campomonto = "a.monto";

				if($rowC['checkservicio'] == 1)
					$campomonto = "IF(IFNULL(c.monto,0) = 0, a.monto, c.monto) as monto";
				
				if($row1['delegacion_entrega'] > 0)
					$res2 = mysql_query("SELECT a.cve, a.nombre, $campomonto FROM zonas a INNER JOIN zonas_delegaciones b ON a.cve = b.zona LEFT JOIN clientes_zonas c ON a.cve = c.zona AND c.cliente = '".$row['cliente']."' WHERE a.tipo='".$row1['tipo_ncargo']."' AND b.delegacion='".$row1['delegacion_entrega']."' ORDER BY a.nombre");
				else
					$res2 = mysql_query("SELECT a.cve, a.nombre, $campomonto FROM zonas a LEFT JOIN clientes_zonas c ON a.cve = c.zona AND c.cliente = '".$row['cliente']."' WHERE a.tipo='".$row1['tipo_ncargo']."' ORDER BY a.nombre");
				if(mysql_num_rows($res2) > 1) $resultado['ultimo_ncargo'] .= '<option value="0" costo="0">Seleccione</option>';
				while($row2=mysql_fetch_array($res2)){
					$resultado['ultimo_ncargo'] .= '<option value="'.$row2['cve'].'" costo="'.$row2['monto'].'"';
					if($row1['zona'] == $row2['cve']) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.$row2['nombre'].'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Costo</th><td><input type="text" class="readOnly" id="subtotal_'.$num_cargo.'" name="subtotal['.$num_cargo.']" size="10" value="'.$row1['subtotal'].'" readOnly></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">IVA</th><td><input type="text" class="readOnly" id="iva_'.$num_cargo.'" name="iva['.$num_cargo.']" size="10" value="'.$row1['iva'].'" readOnly></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Total</th><td><input type="text" class="readOnly totales" id="total_'.$num_cargo.'" name="total['.$num_cargo.']" size="10" value="'.$row1['total'].'" readOnly></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><td colspan="2">&nbsp;</td></tr>';
				/*$resultado['ultimo_ncargo'] .= '<tr><th align="left">Tipo Pago</th><td><select name="tipo_pago['.$num_cargo.']" id="tipo_pago_'.$num_cargo.'"><option value="0">Seleccione</option>';
				foreach($array_tipo_pago as $k=>$v){
					$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
					if($row1['tipo_pago'] == $k) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.$v.'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Estatus Pago</th><td><select name="tipo_pago['.$num_cargo.']" id="tipo_pago_'.$num_cargo.'">';
				foreach($array_estatus_pago as $k=>$v){
					$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
					if($row1['estatus_pago'] == $k) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.$v.'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Cantidad Pagada</th><td><input type="text" class="textField" id="cantidad_pagada_'.$num_cargo.'" name="cantidad_pagada['.$num_cargo.']" size="10" value="'.$row1['cantidad_pagada'].'" onKeyUp="validar_cantidad_pagada('.$num_cargo.')"></td></tr>';*/
				$resultado['ultimo_ncargo'] .= '</table></td></tr>';
				$resultado['ultimo_ncargo'] .= '</table>';
			}
		}
		else{
			$resultado['ultimo_ncargo'] .= '<hr class="crecoleccion_'.$num_cargo.'" >';
			$resultado['ultimo_ncargo'] .= '<table class="crecoleccion_'.$num_cargo.'"  id="tabla_'.$num_cargo.'" width="100%"><tr><td align="left" width="50%" valign="top">';
			$resultado['ultimo_ncargo'] .= '<h1>Recoleccion</h1>';
			$resultado['ultimo_ncargo'] .= '<table>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Contacto</th><td><select name="contacto['.$num_cargo.']" id="contacto_'.$num_cargo.'" class="clase_contacto" onChange="mostrar_datos_contacto('.$num_cargo.')"><option value="0">Seleccione</option>';
			foreach($array_contactos as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'" tel="'.$v[1].'" mail="'.$v[2].'">'.$v[0].'</option>';
			$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_contacto(\'contacto_'.$num_cargo.'\')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Telefono Contacto</th><td><input type="text" id="telefono_contacto_'.$num_cargo.'" class="readOnly" readOnly></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Email Contacto</th><td><input type="text" id="email_contacto_'.$num_cargo.'" class="readOnly" readOnly></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Direccion</th><td><select name="direccion_recoleccion['.$num_cargo.']" id="direccion_recoleccion_'.$num_cargo.'" class="clase_direccion" onChange="seleccionar_delegacion(\'recoleccion\','.$num_cargo.')"><option value="0" delegacion="0">Seleccione</option>';
			$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_recoleccion_'.$num_cargo.'\',\'recoleccion\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Delegacion</th><td><select name="delegacion_recoleccion['.$num_cargo.']" id="delegacion_recoleccion_'.$num_cargo.'"><option value="0">Seleccione</option>';
			//foreach($array_delegacion as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'">'.$v.'</option>';
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Fecha Recoleccion</th><td>'.campo_fecha('fecha_recoleccion['.$num_cargo.']', 'fecha_recoleccion_'.$num_cargo, '', 'fechas_recoleccion').'</td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Hora Recoleccion</th><td><select name="hora_recoleccion['.$num_cargo.']" id="hora_recoleccion_'.$num_cargo.'">';
			for($i=0;$i<=23;$i++) $resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
			$resultado['ultimo_ncargo'] .= '</select>:<select name="min_recoleccion['.$num_cargo.']" id="min_recoleccion_'.$num_cargo.'">';
			for($i=0;$i<=50;$i+=10) $resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Ncargado</th><td><select name="mensajero['.$num_cargo.']" id="mensajero_'.$num_cargo.'"><option value="0">Por asignar</option>';
			foreach($array_personal as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'" style="color:'.$array_colorpersonal[$k].';">'.$v.'</option>';
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Ncargo</th><td><textarea cols="30" rows="3" name="obs_recoleccion['.$num_cargo.']" id="obs_recoleccion_'.$num_cargo.'"></textarea></td></tr>';
			$resultado['ultimo_ncargo'] .= '</table>';
			$resultado['ultimo_ncargo'] .= '</td><td><h1>Entrega</h1><table>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Direccion</th><td><select name="direccion_entrega['.$num_cargo.']" id="direccion_entrega_'.$num_cargo.'" class="clase_direccion" onChange="seleccionar_delegacion(\'entrega\','.$num_cargo.')"><option value="0" delegacion="0">Seleccione</option>';
			$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_entrega_'.$num_cargo.'\',\'entrega\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Delegacion</th><td><select name="delegacion_entrega['.$num_cargo.']" id="delegacion_entrega_'.$num_cargo.'"><option value="0">Seleccione</option>';
			//foreach($array_delegacion as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'">'.$v.'</option>';
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Fecha Entrega</th><td>'.campo_fecha('fecha_entrega['.$num_cargo.']', 'fecha_entrega_'.$num_cargo, '', 'fechas_entrega').'</td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Hora Entrega</th><td><select name="hora_entrega['.$num_cargo.']" id="hora_entrega_'.$num_cargo.'">';
			for($i=0;$i<=23;$i++) $resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
			$resultado['ultimo_ncargo'] .= '</select>:<select name="min_entrega['.$num_cargo.']" id="min_entrega_'.$num_cargo.'">';
			for($i=0;$i<=50;$i+=10) $resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr style="display:none;"><th align="left">Estatus Ncargo</th><td><select name="estatus_ncargo['.$num_cargo.']" id="estatus_ncargo_'.$num_cargo.'">';
			foreach($array_estatus_detalle as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'">'.$v.'</option>';
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr style="display:none;"><th align="left">Recibido por</th><td><input type="text" class="textField" name="recibio['.$num_cargo.']" id="recibio_'.$num_cargo.'" value=""></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Observaciones</th><td><textarea cols="30" rows="3" name="obs_entrega['.$num_cargo.']" id="obs_entrega_'.$num_cargo.'"></textarea></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Tipo </th><td><select name="tipo_ncargo['.$num_cargo.']" id="tipo_ncargo_'.$num_cargo.'" class="ncargo clase_tipo_ncargo" onChange="traer_zonas('.$num_cargo.')"><option value="0">Seleccione</option>';
			foreach($array_tipo_ncargo as $k=>$v){
				$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
				if($k==1) $resultado['ultimo_ncargo'] .= ' selected';
				$resultado['ultimo_ncargo'] .= '>'.$v.'</option>';
			}
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Servicio</th><td><select name="zona['.$num_cargo.']" id="zona_'.$num_cargo.'" class="clase_zona" numcargo="'.$num_cargo.'" onChange="mostrar_costo('.$num_cargo.')"><option value="0" costo="0">Seleccione</option>';
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Costo</th><td><input type="text" class="readOnly" id="subtotal_'.$num_cargo.'" name="subtotal['.$num_cargo.']" size="10" value="" readOnly></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">IVA</th><td><input type="text" class="readOnly" id="iva_'.$num_cargo.'" name="iva['.$num_cargo.']" size="10" value="" readOnly></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Total</th><td><input type="text" class="readOnly totales" id="total_'.$num_cargo.'" name="total['.$num_cargo.']" size="10" value="" readOnly></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><td colspan="2">&nbsp;</td></tr>';
			/*$resultado['ultimo_ncargo'] .= '<tr><th align="left">Tipo Pago</th><td><select name="tipo_pago['.$num_cargo.']" id="tipo_pago_'.$num_cargo.'"><option value="0">Seleccione</option>';
			foreach($array_tipo_pago as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'">'.$v.'</option>';
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Estatus Pago</th><td><select name="tipo_pago['.$num_cargo.']" id="tipo_pago_'.$num_cargo.'">';
			foreach($array_estatus_pago as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'">'.$v.'</option>';
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Cantidad Pagada</th><td><input type="text" class="textField" id="cantidad_pagada_'.$num_cargo.'" name="cantidad_pagada['.$num_cargo.']" size="10" value="" onKeyUp="validar_cantidad_pagada('.$num_cargo.')"></td></tr>';*/
			$resultado['ultimo_ncargo'] .= '</table></td></tr>';
			$resultado['ultimo_ncargo'] .= '</table>';
		}
	}
	else{
		$num_cargo = 0;
		$resultado['ultimo_ncargo'] .= '<hr >';
		$resultado['ultimo_ncargo'] .= '<table class="crecoleccion_'.$num_cargo.'"  id="tabla_'.$num_cargo.'" width="100%"><tr><td align="left" width="50%" valign="top">';
		$resultado['ultimo_ncargo'] .= '<h1>Recoleccion</h1>';
		$resultado['ultimo_ncargo'] .= '<table>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Contacto</th><td><select name="contacto['.$num_cargo.']" id="contacto_'.$num_cargo.'" class="clase_contacto" onChange="mostrar_datos_contacto('.$num_cargo.')"><option value="0">Seleccione</option>';
		$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_contacto(\'contacto_'.$num_cargo.'\')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Telefono Contacto</th><td><input type="text" id="telefono_contacto_'.$num_cargo.'" class="readOnly" readOnly></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Email Contacto</th><td><input type="text" id="email_contacto_'.$num_cargo.'" class="readOnly" readOnly></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Direccion</th><td><select name="direccion_recoleccion['.$num_cargo.']" id="direccion_recoleccion_'.$num_cargo.'" class="clase_direccion" onChange="seleccionar_delegacion(\'recoleccion\','.$num_cargo.')"><option value="0" delegacion="0">Seleccione</option>';
		$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_recoleccion_'.$num_cargo.'\',\'recoleccion\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Delegacion</th><td><select name="delegacion_recoleccion['.$num_cargo.']" id="delegacion_recoleccion_'.$num_cargo.'"><option value="0">Seleccione</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Fecha Recoleccion</th><td>'.campo_fecha('fecha_recoleccion['.$num_cargo.']', 'fecha_recoleccion_'.$num_cargo, '', 'fechas_recoleccion').'</td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr style="display:none;"><th align="left">Hora Recoleccion</th><td><select name="hora_recoleccion['.$num_cargo.']" id="hora_recoleccion_'.$num_cargo.'">';
		for($i=0;$i<=23;$i++) $resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
		$resultado['ultimo_ncargo'] .= '</select>:<select name="min_recoleccion['.$num_cargo.']" id="min_recoleccion_'.$num_cargo.'">';
		for($i=0;$i<=59;$i++) $resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Ncargado</th><td><select name="mensajero['.$num_cargo.']" id="mensajero_'.$num_cargo.'"><option value="0">Por asignar</option>';
		foreach($array_personal as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'" style="color:'.$array_colorpersonal[$k].';">'.$v.'</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Ncargo</th><td><textarea cols="30" rows="3" name="obs_recoleccion['.$num_cargo.']" id="obs_recoleccion_'.$num_cargo.'"></textarea></td></tr>';
		$resultado['ultimo_ncargo'] .= '</table>';
		$resultado['ultimo_ncargo'] .= '</td><td><h1>Entrega</h1><table>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Direccion</th><td><select name="direccion_entrega['.$num_cargo.']" id="direccion_entrega_'.$num_cargo.'" class="clase_direccion" onChange="seleccionar_delegacion(\'entrega\','.$num_cargo.')"><option value="0" delegacion="0">Seleccione</option>';
		$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_entrega_'.$num_cargo.'\',\'entrega\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Delegacion</th><td><select name="delegacion_entrega['.$num_cargo.']" id="delegacion_entrega_'.$num_cargo.'"><option value="0">Seleccione</option>';
		//foreach($array_delegacion as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'">'.$v.'</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Fecha Entrega</th><td>'.campo_fecha('fecha_entrega['.$num_cargo.']', 'fecha_entrega_'.$num_cargo, '', 'fechas_entrega').'</td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Hora Entrega</th><td><select name="hora_entrega['.$num_cargo.']" id="hora_entrega_'.$num_cargo.'">';
		for($i=0;$i<=23;$i++) $resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
		$resultado['ultimo_ncargo'] .= '</select>:<select name="min_entrega['.$num_cargo.']" id="min_entrega_'.$num_cargo.'">';
		for($i=0;$i<=50;$i+=10) $resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr style="display:none;"><th align="left">Estatus Ncargo</th><td><select name="estatus_ncargo['.$num_cargo.']" id="estatus_ncargo_'.$num_cargo.'">';
		foreach($array_estatus_detalle as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'">'.$v.'</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr style="display:none;"><th align="left">Recibido por</th><td><input type="text" class="textField" name="recibio['.$num_cargo.']" id="recibio_'.$num_cargo.'" value=""></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Observaciones</th><td><textarea cols="30" rows="3" name="obs_entrega['.$num_cargo.']" id="obs_entrega_'.$num_cargo.'"></textarea></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Tipo </th><td><select name="tipo_ncargo['.$num_cargo.']" id="tipo_ncargo_'.$num_cargo.'" class="ncargo clase_tipo_ncargo" onChange="traer_zonas('.$num_cargo.')">';
		$resultado['ultimo_ncargo'] .= '<option value="0">Seleccione</option>';
		foreach($array_tipo_ncargo as $k=>$v){
			$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
			if($k==1) $resultado['ultimo_ncargo'] .= ' selected';
			$resultado['ultimo_ncargo'] .= '>'.utf8_encode($v).'</option>';
		}
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Servicio</th><td><select name="zona['.$num_cargo.']" id="zona_'.$num_cargo.'" class="clase_zona" numcargo="'.$num_cargo.'" onChange="mostrar_costo('.$num_cargo.')">';
		$resultado['ultimo_ncargo'] .= '<option value="0" costo="0">Seleccione</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Costo</th><td><input type="text" class="readOnly" id="subtotal_'.$num_cargo.'" name="subtotal['.$num_cargo.']" size="10" value="" readOnly></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">IVA</th><td><input type="text" class="readOnly" id="iva_'.$num_cargo.'" name="iva['.$num_cargo.']" size="10" value="" readOnly></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Total</th><td><input type="text" class="readOnly totales" id="total_'.$num_cargo.'" name="total['.$num_cargo.']" size="10" value="" readOnly></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><td colspan="2">&nbsp;</td></tr>';
		/*$resultado['ultimo_ncargo'] .= '<tr><th align="left">Tipo Pago</th><td><select name="tipo_pago['.$num_cargo.']" id="tipo_pago_'.$num_cargo.'"><option value="0">Seleccione</option>';
		foreach($array_tipo_pago as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'">'.$v.'</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Estatus Pago</th><td><select name="tipo_pago['.$num_cargo.']" id="tipo_pago_'.$num_cargo.'">';
		foreach($array_estatus_pago as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'">'.$v.'</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Cantidad Pagada</th><td><input type="text" class="textField" id="cantidad_pagada_'.$num_cargo.'" name="cantidad_pagada['.$num_cargo.']" size="10" value="" onKeyUp="validar_cantidad_pagada('.$num_cargo.')"></td></tr>';*/
		$resultado['ultimo_ncargo'] .= '</table></td></tr>';
		$resultado['ultimo_ncargo'] .= '</table>';
	}
	exit();
}

if($_POST['ajax'] == 3){
	$nivelusuario = nivelUsuario();
	$resultado=array('tipo_cliente'=>'<option value="0">Seleccione</option>', 'contactos' => '<option value="0">Seleccione</option>', 'direcciones' => '<option value="0" delegacion="0">Seleccione</option>', 'tipo_ncargo' => '<option value="0">Seleccione</option>', 'zonas' => '<option value="0" costo="0">Seleccione</option>');
	if($_POST['cliente'] > 0){
		$res = mysql_query("SELECT * FROM clientes WHERE cve='".$_POST['cliente']."'");
		$rowC=mysql_fetch_array($res);
		$array_contactos = array();
		$array_direcciones = array();
		$res1=mysql_query("SELECT * FROM clientes_contacto WHERE cliente='".$_POST['cliente']."' ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			$resultado['contactos'] .= '<option value="'.$row1['cve'].'" tel="'.$row1['telefono'].'" mail="'.$row1['email'].'">'.$row1['nombre'].'</option>';
			$array_contactos[$row1['cve']] = array($row1['nombre'],$row1['telefono'],$row1['email']);
		}
		$res1=mysql_query("SELECT * FROM clientes_direccion WHERE cliente='".$_POST['cliente']."' ORDER BY direccion");
		while($row1=mysql_fetch_array($res1)){
			$resultado['direcciones'] .= '<option value="'.$row1['cve'].'" delegacion="'.$row1['delegacion'].'">'.$row1['direccion'].'</option>';
			$array_direcciones[$row1['cve']] = $row1['direccion'].'';
			$array_dir_delegacion[$row1['cve']] = $row1['delegacion'].'';
		}
		foreach($array_tipo_cliente as $k=>$v){
			$resultado['tipo_cliente'] .= '<option value="'.$k.'"';
			if($rowC['tipo_cliente'] == $k) $resultado['tipo_cliente'] .= ' selected'; 
			$resultado['tipo_cliente'] .= '>'.$v.'</option>';
		}
		$num_cargo = 0;
		$res1 = mysql_query("SELECT a.cve FROM pedidos a INNER JOIN pedidos_detalles b ON a.cve = b.pedido WHERE a.cliente='".$_POST['cliente']."' AND a.estatus!='C' ORDER BY a.cve DESC LIMIT 1");
		if($row1 = mysql_fetch_assoc($res1)){
			$res1 = mysql_query("SELECT * FROM pedidos_detalles WHERE pedido = '".$row1['cve']."' LIMIT 1");
			while($row1=mysql_fetch_array($res1)){
				$resultado['ultimo_ncargo'] .= '<hr class="crecoleccion_'.$num_cargo.'" >';
				$resultado['ultimo_ncargo'] .= '<table class="crecoleccion_'.$num_cargo.'"  id="tabla_'.$num_cargo.'" width="100%"><tr><td align="left" width="50%" valign="top">';
				$resultado['ultimo_ncargo'] .= '<h1>Recoleccion</h1>';
				$resultado['ultimo_ncargo'] .= '<table>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Contacto</th><td><select name="contacto['.$num_cargo.']" id="contacto_'.$num_cargo.'" class="clase_contacto" onChange="mostrar_datos_contacto('.$num_cargo.')"><option value="0">Seleccione</option>';
				foreach($array_contactos as $k=>$v){ 
					$resultado['ultimo_ncargo'] .= '<option value="'.$k.'" tel="'.$v[1].'" mail="'.$v[2].'"';
					if($k==$row1['contacto']) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .=  '>'.utf8_encode($v[0]).'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_contacto(\'contacto_'.$num_cargo.'\')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Telefono Contacto</th><td><input type="text" id="telefono_contacto_'.$num_cargo.'" value="'.$array_contactos[$row1['contacto']][1].'" class="readOnly" readOnly></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Email Contacto</th><td><input type="text" id="email_contacto_'.$num_cargo.'" value="'.$array_contactos[$row1['contacto']][2].'" class="readOnly" readOnly></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Direccion</th><td><select name="direccion_recoleccion['.$num_cargo.']" id="direccion_recoleccion_'.$num_cargo.'" onChange="seleccionar_delegacion(\'recoleccion\','.$num_cargo.')" class="clase_direccion" delegacion="0"><option value="0">Seleccione</option>';
				foreach($array_direcciones as $k=>$v){
					$resultado['ultimo_ncargo'] .= '<option value="'.$k.'" delegacion="'.$array_dir_delegacion[$k].'"';
					if($row1['direccion_recoleccion'] == $k) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.utf8_encode($v).'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_recoleccion_'.$num_cargo.'\',\'recoleccion\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Delegacion</th><td><select name="delegacion_recoleccion['.$num_cargo.']" id="delegacion_recoleccion_'.$num_cargo.'"><option value="0">Seleccione</option>';
				foreach($array_delegacion as $k=>$v){
					if($row1['delegacion_recoleccion'] == $k){
						$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
						$resultado['ultimo_ncargo'] .= ' selected';
						$resultado['ultimo_ncargo'] .= '>'.utf8_encode($v).'</option>';
					}
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				if($row1['fecha_recoleccion'] == '0000-00-00') $row1['fecha_recoleccion'] = '';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Fecha Recoleccion</th><td>'.campo_fecha('fecha_recoleccion['.$num_cargo.']', 'fecha_recoleccion_'.$num_cargo, $row1['fecha_recoleccion'], 'fechas_recoleccion').'</td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Hora Recoleccion</th><td><select name="hora_recoleccion['.$num_cargo.']" id="hora_recoleccion_'.$num_cargo.'">';
				for($i=0;$i<=23;$i++){
					$resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'"';
					if($i==intval(substr($row1['hora_recoleccion'],0,2))) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.sprintf('%02s',$i).'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select>:<select name="min_recoleccion['.$num_cargo.']" id="min_recoleccion_'.$num_cargo.'">';
				for($i=0;$i<=50;$i+=10){
					$resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'"';
					if($i==intval(substr($row1['hora_recoleccion'], 3, 2))) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.sprintf('%02s',$i).'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Ncargado</th><td><select name="mensajero['.$num_cargo.']" id="mensajero_'.$num_cargo.'"><option value="0">Por asignar</option>';
				foreach($array_personal as $k=>$v){
					$resultado['ultimo_ncargo'] .= '<option value="'.$k.'" style="color:'.$array_colorpersonal[$k].';"';
					if($row1['mensajero'] == $k) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.utf8_encode($v).'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Ncargo</th><td><textarea cols="30" rows="3" name="obs_recoleccion['.$num_cargo.']" id="obs_recoleccion_'.$num_cargo.'">'.$row1['obs_recoleccion'].'</textarea></td></tr>';
				$resultado['ultimo_ncargo'] .= '</table>';
				$resultado['ultimo_ncargo'] .= '</td><td><h1>Entrega</h1><table>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Direccion</th><td><select name="direccion_entrega['.$num_cargo.']" id="direccion_entrega_'.$num_cargo.'" class="clase_direccion" onChange="seleccionar_delegacion(\'entrega\','.$num_cargo.')"><option value="0" delegacion="0">Seleccione</option>';
				foreach($array_direcciones as $k=>$v){
					$resultado['ultimo_ncargo'] .= '<option value="'.$k.'" delegacion="'.$array_dir_delegacion[$k].'"';
					if($row1['direccion_entrega'] == $k) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.utf8_encode($v).'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_entrega_'.$num_cargo.'\',\'entrega\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Delegacion</th><td><select name="delegacion_entrega['.$num_cargo.']" id="delegacion_entrega_'.$num_cargo.'"><option value="0">Seleccione</option>';
				foreach($array_delegacion as $k=>$v){
					if($row1['delegacion_entrega'] == $k){
						$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
						$resultado['ultimo_ncargo'] .= ' selected';
						$resultado['ultimo_ncargo'] .= '>'.utf8_encode($v).'</option>';
					}
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				if($row1['fecha_entrega'] == '0000-00-00') $row1['fecha_entrega'] = '';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Fecha Entrega</th><td>'.campo_fecha('fecha_entrega['.$num_cargo.']', 'fecha_entrega_'.$num_cargo, $row1['fecha_entrega'], 'fechas_entrega').'</td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Hora Entrega</th><td><select name="hora_entrega['.$num_cargo.']" id="hora_entrega_'.$num_cargo.'">';
				for($i=0;$i<=23;$i++){
					$resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'"';
					if($i==intval(substr($row1['hora_entrega'],0,2))) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.sprintf('%02s',$i).'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select>:<select name="min_entrega['.$num_cargo.']" id="min_entrega_'.$num_cargo.'">';
				for($i=0;$i<=50;$i+=10){
					$resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'"';
					if($i==intval(substr($row1['hora_entrega'], 3, 2))) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.sprintf('%02s',$i).'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr';
				if($nivelusuario < 3) $resultado['ultimo_ncargo'] .= ' style="display:none;"';
				$resultado['ultimo_ncargo'] .= '><th align="left">Estatus Ncargo</th><td><select name="estatus_ncargo['.$num_cargo.']" id="estatus_ncargo_'.$num_cargo.'">';
				foreach($array_estatus_detalle as $k=>$v){
					$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
					if($row1['estatus_ncargo'] == $k) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.$v.'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr';
				if($nivelusuario < 3) $resultado['ultimo_ncargo'] .= ' style="display:none;"';
				$resultado['ultimo_ncargo'] .= '><th align="left">Recibido por</th><td><input type="text" class="textField" name="recibio['.$num_cargo.']" id="recibio_'.$num_cargo.'" value="'.$row1['recibio'].'"></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Observaciones</th><td><textarea cols="30" rows="3" name="obs_entrega['.$num_cargo.']" id="obs_entrega_'.$num_cargo.'">'.$row1['obs_entrega'].'</textarea></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Tipo </th><td><select name="tipo_ncargo['.$num_cargo.']" id="tipo_ncargo_'.$num_cargo.'" class="ncargo clase_tipo_ncargo" onChange="traer_zonas('.$num_cargo.')">';
				if($rowC['servicio'] == 0)
					$resultado['ultimo_ncargo'] .= '<option value="0">Seleccione</option>';
				foreach($array_tipo_ncargo as $k=>$v){
					$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
					if($row1['tipo_ncargo'] == $k || ($k==1 && $row1['tipo_ncargo'] == 0)) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.utf8_encode($v).'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Servicio</th><td><select name="zona['.$num_cargo.']" id="zona_'.$num_cargo.'" class="clase_zona" numcargo="'.$num_cargo.'" onChange="mostrar_costo('.$num_cargo.')">';
				$campomonto = "a.monto";

				if($rowC['checkservicio'] == 1)
					$campomonto = "IF(IFNULL(c.monto,0) = 0, a.monto, c.monto) as monto";
				
				if($row1['delegacion_entrega'] > 0)
					$res2 = mysql_query("SELECT a.cve, a.nombre, $campomonto FROM zonas a INNER JOIN zonas_delegaciones b ON a.cve = b.zona LEFT JOIN clientes_zonas c ON a.cve = c.zona AND c.cliente = '".$row['cliente']."' WHERE a.tipo='".$row1['tipo_ncargo']."' AND b.delegacion='".$row1['delegacion_entrega']."' ORDER BY a.nombre");
				else
					$res2 = mysql_query("SELECT a.cve, a.nombre, $campomonto FROM zonas a LEFT JOIN clientes_zonas c ON a.cve = c.zona AND c.cliente = '".$row['cliente']."' WHERE a.tipo='".$row1['tipo_ncargo']."' ORDER BY a.nombre");
				if(mysql_num_rows($res2) > 1) $resultado['ultimo_ncargo'] .= '<option value="0" costo="0">Seleccione</option>';
				while($row2=mysql_fetch_array($res2)){
					$resultado['ultimo_ncargo'] .= '<option value="'.$row2['cve'].'" costo="'.$row2['monto'].'"';
					if($row1['zona'] == $row2['cve']) $resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.utf8_encode($row2['nombre']).'</option>';
				}
				$resultado['ultimo_ncargo'] .= '</select></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Costo</th><td><input type="text" class="readOnly" id="subtotal_'.$num_cargo.'" name="subtotal['.$num_cargo.']" size="10" value="'.$row1['subtotal'].'" readOnly></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">IVA</th><td><input type="text" class="readOnly" id="iva_'.$num_cargo.'" name="iva['.$num_cargo.']" size="10" value="'.$row1['iva'].'" readOnly></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><th align="left">Total</th><td><input type="text" class="readOnly totales" id="total_'.$num_cargo.'" name="total['.$num_cargo.']" size="10" value="'.$row1['total'].'" readOnly></td></tr>';
				$resultado['ultimo_ncargo'] .= '<tr><td colspan="2">&nbsp;</td></tr>';
				$resultado['ultimo_ncargo'] .= '</table></td></tr>';
				$resultado['ultimo_ncargo'] .= '</table>';
			}
		}
		else{
			$row1=array();
			$resultado['ultimo_ncargo'] .= '<hr class="crecoleccion_'.$num_cargo.'" >';
			$resultado['ultimo_ncargo'] .= '<table class="crecoleccion_'.$num_cargo.'"  id="tabla_'.$num_cargo.'" width="100%"><tr><td align="left" width="50%" valign="top">';
			$resultado['ultimo_ncargo'] .= '<h1>Recoleccion</h1>';
			$resultado['ultimo_ncargo'] .= '<table>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Contacto</th><td><select name="contacto['.$num_cargo.']" id="contacto_'.$num_cargo.'" class="clase_contacto" onChange="mostrar_datos_contacto('.$num_cargo.')"><option value="0">Seleccione</option>';
			foreach($array_contactos as $k=>$v){ 
				$resultado['ultimo_ncargo'] .= '<option value="'.$k.'" tel="'.$v[1].'" mail="'.$v[2].'"';
				$resultado['ultimo_ncargo'] .=  '>'.utf8_encode($v[0]).'</option>';
			}
			$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_contacto(\'contacto_'.$num_cargo.'\')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Telefono Contacto</th><td><input type="text" id="telefono_contacto_'.$num_cargo.'" value="'.$array_contactos[$row1['contacto']][1].'" class="readOnly" readOnly></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Email Contacto</th><td><input type="text" id="email_contacto_'.$num_cargo.'" value="'.$array_contactos[$row1['contacto']][2].'" class="readOnly" readOnly></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Direccion</th><td><select name="direccion_recoleccion['.$num_cargo.']" id="direccion_recoleccion_'.$num_cargo.'" onChange="seleccionar_delegacion(\'recoleccion\','.$num_cargo.')" class="clase_direccion" delegacion="0"><option value="0">Seleccione</option>';
			foreach($array_direcciones as $k=>$v){
				$resultado['ultimo_ncargo'] .= '<option value="'.$k.'" delegacion="'.$array_dir_delegacion[$k].'"';
				$resultado['ultimo_ncargo'] .= '>'.utf8_encode($v).'</option>';
			}
			$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_recoleccion_'.$num_cargo.'\',\'recoleccion\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Delegacion</th><td><select name="delegacion_recoleccion['.$num_cargo.']" id="delegacion_recoleccion_'.$num_cargo.'"><option value="0">Seleccione</option>';
			foreach($array_delegacion as $k=>$v){
				if($row1['delegacion_recoleccion'] == $k){
					$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
					$resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.utf8_encode($v).'</option>';
				}
			}
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			if($row1['fecha_recoleccion'] == '0000-00-00') $row1['fecha_recoleccion'] = '';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Fecha Recoleccion</th><td>'.campo_fecha('fecha_recoleccion['.$num_cargo.']', 'fecha_recoleccion_'.$num_cargo, $row1['fecha_recoleccion'], 'fechas_recoleccion').'</td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Hora Recoleccion</th><td><select name="hora_recoleccion['.$num_cargo.']" id="hora_recoleccion_'.$num_cargo.'">';
			for($i=0;$i<=23;$i++){
				$resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'"';
				if($i==intval(substr($row1['hora_recoleccion'],0,2))) $resultado['ultimo_ncargo'] .= ' selected';
				$resultado['ultimo_ncargo'] .= '>'.sprintf('%02s',$i).'</option>';
			}
			$resultado['ultimo_ncargo'] .= '</select>:<select name="min_recoleccion['.$num_cargo.']" id="min_recoleccion_'.$num_cargo.'">';
			for($i=0;$i<=50;$i+=10){
				$resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'"';
				if($i==intval(substr($row1['hora_recoleccion'], 3, 2))) $resultado['ultimo_ncargo'] .= ' selected';
				$resultado['ultimo_ncargo'] .= '>'.sprintf('%02s',$i).'</option>';
			}
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Ncargado</th><td><select name="mensajero['.$num_cargo.']" id="mensajero_'.$num_cargo.'"><option value="0">Por asignar</option>';
			foreach($array_personal as $k=>$v){
				$resultado['ultimo_ncargo'] .= '<option value="'.$k.'" style="color:'.$array_colorpersonal[$k].';"';
				if($row1['mensajero'] == $k) $resultado['ultimo_ncargo'] .= ' selected';
				$resultado['ultimo_ncargo'] .= '>'.utf8_encode($v).'</option>';
			}
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Ncargo</th><td><textarea cols="30" rows="3" name="obs_recoleccion['.$num_cargo.']" id="obs_recoleccion_'.$num_cargo.'">'.$row1['obs_recoleccion'].'</textarea></td></tr>';
			$resultado['ultimo_ncargo'] .= '</table>';
			$resultado['ultimo_ncargo'] .= '</td><td><h1>Entrega</h1><table>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Direccion</th><td><select name="direccion_entrega['.$num_cargo.']" id="direccion_entrega_'.$num_cargo.'" class="clase_direccion" onChange="seleccionar_delegacion(\'entrega\','.$num_cargo.')"><option value="0" delegacion="0">Seleccione</option>';
			foreach($array_direcciones as $k=>$v){
				$resultado['ultimo_ncargo'] .= '<option value="'.$k.'" delegacion="'.$array_dir_delegacion[$k].'"';
				if($row1['direccion_entrega'] == $k) $resultado['ultimo_ncargo'] .= ' selected';
				$resultado['ultimo_ncargo'] .= '>'.utf8_encode($v).'</option>';
			}
			$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_entrega_'.$num_cargo.'\',\'entrega\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Delegacion</th><td><select name="delegacion_entrega['.$num_cargo.']" id="delegacion_entrega_'.$num_cargo.'"><option value="0">Seleccione</option>';
			foreach($array_delegacion as $k=>$v){
				if($row1['delegacion_entrega'] == $k){
					$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
					$resultado['ultimo_ncargo'] .= ' selected';
					$resultado['ultimo_ncargo'] .= '>'.utf8_encode($v).'</option>';
				}
			}
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			if($row1['fecha_entrega'] == '0000-00-00') $row1['fecha_entrega'] = '';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Fecha Entrega</th><td>'.campo_fecha('fecha_entrega['.$num_cargo.']', 'fecha_entrega_'.$num_cargo, $row1['fecha_entrega'], 'fechas_entrega').'</td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Hora Entrega</th><td><select name="hora_entrega['.$num_cargo.']" id="hora_entrega_'.$num_cargo.'">';
			for($i=0;$i<=23;$i++){
				$resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'"';
				if($i==intval(substr($row1['hora_entrega'],0,2))) $resultado['ultimo_ncargo'] .= ' selected';
				$resultado['ultimo_ncargo'] .= '>'.sprintf('%02s',$i).'</option>';
			}
			$resultado['ultimo_ncargo'] .= '</select>:<select name="min_entrega['.$num_cargo.']" id="min_entrega_'.$num_cargo.'">';
			for($i=0;$i<=50;$i+=10){
				$resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'"';
				if($i==intval(substr($row1['hora_entrega'], 3, 2))) $resultado['ultimo_ncargo'] .= ' selected';
				$resultado['ultimo_ncargo'] .= '>'.sprintf('%02s',$i).'</option>';
			}
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr';
			if($nivelusuario < 3) $resultado['ultimo_ncargo'] .= ' style="display:none;"';
			$resultado['ultimo_ncargo'] .= '><th align="left">Estatus Ncargo</th><td><select name="estatus_ncargo['.$num_cargo.']" id="estatus_ncargo_'.$num_cargo.'">';
			foreach($array_estatus_detalle as $k=>$v){
				$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
				if($row1['estatus_ncargo'] == $k) $resultado['ultimo_ncargo'] .= ' selected';
				$resultado['ultimo_ncargo'] .= '>'.$v.'</option>';
			}
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr';
			if($nivelusuario < 3) $resultado['ultimo_ncargo'] .= ' style="display:none;"';
			$resultado['ultimo_ncargo'] .= '><th align="left">Recibido por</th><td><input type="text" class="textField" name="recibio['.$num_cargo.']" id="recibio_'.$num_cargo.'" value="'.$row1['recibio'].'"></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Observaciones</th><td><textarea cols="30" rows="3" name="obs_entrega['.$num_cargo.']" id="obs_entrega_'.$num_cargo.'">'.$row1['obs_entrega'].'</textarea></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Tipo </th><td><select name="tipo_ncargo['.$num_cargo.']" id="tipo_ncargo_'.$num_cargo.'" class="ncargo clase_tipo_ncargo" onChange="traer_zonas('.$num_cargo.')">';
			if($rowC['servicio'] == 0)
				$resultado['ultimo_ncargo'] .= '<option value="0">Seleccione</option>';
			foreach($array_tipo_ncargo as $k=>$v){
				$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
				if($row1['tipo_ncargo'] == $k || ($k==1 && $row1['tipo_ncargo'] == 0)) $resultado['ultimo_ncargo'] .= ' selected';
				$resultado['ultimo_ncargo'] .= '>'.utf8_encode($v).'</option>';
			}
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Servicio</th><td><select name="zona['.$num_cargo.']" id="zona_'.$num_cargo.'" class="clase_zona" numcargo="'.$num_cargo.'" onChange="mostrar_costo('.$num_cargo.')">';
			$campomonto = "a.monto";

			if($rowC['checkservicio'] == 1)
				$campomonto = "IF(IFNULL(c.monto,0) = 0, a.monto, c.monto) as monto";
			
			if($row1['delegacion_entrega'] > 0)
				$res2 = mysql_query("SELECT a.cve, a.nombre, $campomonto FROM zonas a INNER JOIN zonas_delegaciones b ON a.cve = b.zona LEFT JOIN clientes_zonas c ON a.cve = c.zona AND c.cliente = '".$row['cliente']."' WHERE a.tipo='".$row1['tipo_ncargo']."' AND b.delegacion='".$row1['delegacion_entrega']."' ORDER BY a.nombre");
			else
				$res2 = mysql_query("SELECT a.cve, a.nombre, $campomonto FROM zonas a LEFT JOIN clientes_zonas c ON a.cve = c.zona AND c.cliente = '".$row['cliente']."' WHERE a.tipo='".$row1['tipo_ncargo']."' ORDER BY a.nombre");
			if(mysql_num_rows($res2) > 1) $resultado['ultimo_ncargo'] .= '<option value="0" costo="0">Seleccione</option>';
			while($row2=mysql_fetch_array($res2)){
				$resultado['ultimo_ncargo'] .= '<option value="'.$row2['cve'].'" costo="'.$row2['monto'].'"';
				if($row1['zona'] == $row2['cve']) $resultado['ultimo_ncargo'] .= ' selected';
				$resultado['ultimo_ncargo'] .= '>'.utf8_encode($row2['nombre']).'</option>';
			}
			$resultado['ultimo_ncargo'] .= '</select></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Costo</th><td><input type="text" class="readOnly" id="subtotal_'.$num_cargo.'" name="subtotal['.$num_cargo.']" size="10" value="'.$row1['subtotal'].'" readOnly></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">IVA</th><td><input type="text" class="readOnly" id="iva_'.$num_cargo.'" name="iva['.$num_cargo.']" size="10" value="'.$row1['iva'].'" readOnly></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><th align="left">Total</th><td><input type="text" class="readOnly totales" id="total_'.$num_cargo.'" name="total['.$num_cargo.']" size="10" value="'.$row1['total'].'" readOnly></td></tr>';
			$resultado['ultimo_ncargo'] .= '<tr><td colspan="2">&nbsp;</td></tr>';
			$resultado['ultimo_ncargo'] .= '</table></td></tr>';
			$resultado['ultimo_ncargo'] .= '</table>';
		}
	}
	else{
		$num_cargo = 0;
		$resultado['ultimo_ncargo'] .= '<hr class="crecoleccion_'.$num_cargo.'" >';
		$resultado['ultimo_ncargo'] .= '<table class="crecoleccion_'.$num_cargo.'"  id="tabla_'.$num_cargo.'" width="100%"><tr><td align="left" width="50%" valign="top">';
		$resultado['ultimo_ncargo'] .= '<h1>Recoleccion</h1>';
		$resultado['ultimo_ncargo'] .= '<table>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Contacto</th><td><select name="contacto['.$num_cargo.']" id="contacto_'.$num_cargo.'" class="clase_contacto" onChange="mostrar_datos_contacto('.$num_cargo.')"><option value="0">Seleccione</option>';
		$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_contacto(\'contacto_'.$num_cargo.'\')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Telefono Contacto</th><td><input type="text" id="telefono_contacto_'.$num_cargo.'" class="readOnly" readOnly></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Email Contacto</th><td><input type="text" id="email_contacto_'.$num_cargo.'" class="readOnly" readOnly></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Direccion</th><td><select name="direccion_recoleccion['.$num_cargo.']" id="direccion_recoleccion_'.$num_cargo.'" class="clase_direccion" onChange="seleccionar_delegacion(\'recoleccion\','.$num_cargo.')"><option value="0" delegacion="0">Seleccione</option>';
		$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_recoleccion_'.$num_cargo.'\',\'recoleccion\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Delegacion</th><td><select name="delegacion_recoleccion['.$num_cargo.']" id="delegacion_recoleccion_'.$num_cargo.'"><option value="0">Seleccione</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Fecha Recoleccion</th><td>'.campo_fecha('fecha_recoleccion['.$num_cargo.']', 'fecha_recoleccion_'.$num_cargo, '', 'fechas_recoleccion').'</td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr style="display:none;"><th align="left">Hora Recoleccion</th><td><select name="hora_recoleccion['.$num_cargo.']" id="hora_recoleccion_'.$num_cargo.'">';
		for($i=0;$i<=23;$i++) $resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
		$resultado['ultimo_ncargo'] .= '</select>:<select name="min_recoleccion['.$num_cargo.']" id="min_recoleccion_'.$num_cargo.'">';
		for($i=0;$i<=59;$i++) $resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Ncargado</th><td><select name="mensajero['.$num_cargo.']" id="mensajero_'.$num_cargo.'"><option value="0">Por asignar</option>';
		foreach($array_personal as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'" style="color:'.$array_colorpersonal[$k].';">'.utf8_encode($v).'</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Ncargo</th><td><textarea cols="30" rows="3" name="obs_recoleccion['.$num_cargo.']" id="obs_recoleccion_'.$num_cargo.'"></textarea></td></tr>';
		$resultado['ultimo_ncargo'] .= '</table>';
		$resultado['ultimo_ncargo'] .= '</td><td><h1>Entrega</h1><table>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Direccion</th><td><select name="direccion_entrega['.$num_cargo.']" id="direccion_entrega_'.$num_cargo.'" class="clase_direccion" onChange="seleccionar_delegacion(\'entrega\','.$num_cargo.')"><option value="0" delegacion="0">Seleccione</option>';
		$resultado['ultimo_ncargo'] .= '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_entrega_'.$num_cargo.'\',\'entrega\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Delegacion</th><td><select name="delegacion_entrega['.$num_cargo.']" id="delegacion_entrega_'.$num_cargo.'"><option value="0">Seleccione</option>';
		//foreach($array_delegacion as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'">'.$v.'</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Fecha Entrega</th><td>'.campo_fecha('fecha_entrega['.$num_cargo.']', 'fecha_entrega_'.$num_cargo, '', 'fechas_entrega').'</td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Hora Entrega</th><td><select name="hora_entrega['.$num_cargo.']" id="hora_entrega_'.$num_cargo.'">';
		for($i=0;$i<=23;$i++) $resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
		$resultado['ultimo_ncargo'] .= '</select>:<select name="min_entrega['.$num_cargo.']" id="min_entrega_'.$num_cargo.'">';
		for($i=0;$i<=50;$i+=10) $resultado['ultimo_ncargo'] .= '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr style="display:none;"><th align="left">Estatus Ncargo</th><td><select name="estatus_ncargo['.$num_cargo.']" id="estatus_ncargo_'.$num_cargo.'">';
		foreach($array_estatus_detalle as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'">'.$v.'</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr style="display:none;"><th align="left">Recibido por</th><td><input type="text" class="textField" name="recibio['.$num_cargo.']" id="recibio_'.$num_cargo.'" value=""></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Observaciones</th><td><textarea cols="30" rows="3" name="obs_entrega['.$num_cargo.']" id="obs_entrega_'.$num_cargo.'"></textarea></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Tipo </th><td><select name="tipo_ncargo['.$num_cargo.']" id="tipo_ncargo_'.$num_cargo.'" class="ncargo clase_tipo_ncargo" onChange="traer_zonas('.$num_cargo.')">';
		$resultado['ultimo_ncargo'] .= '<option value="0">Seleccione</option>';
		foreach($array_tipo_ncargo as $k=>$v){
			$resultado['ultimo_ncargo'] .= '<option value="'.$k.'"';
			if($k==1) $resultado['ultimo_ncargo'] .= ' selected';
			$resultado['ultimo_ncargo'] .= '>'.utf8_encode($v).'</option>';
		}
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Servicio</th><td><select name="zona['.$num_cargo.']" id="zona_'.$num_cargo.'" class="clase_zona" numcargo="'.$num_cargo.'" onChange="mostrar_costo('.$num_cargo.')">';
		$resultado['ultimo_ncargo'] .= '<option value="0" costo="0">Seleccione</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Costo</th><td><input type="text" class="readOnly" id="subtotal_'.$num_cargo.'" name="subtotal['.$num_cargo.']" size="10" value="" readOnly></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">IVA</th><td><input type="text" class="readOnly" id="iva_'.$num_cargo.'" name="iva['.$num_cargo.']" size="10" value="" readOnly></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Total</th><td><input type="text" class="readOnly totales" id="total_'.$num_cargo.'" name="total['.$num_cargo.']" size="10" value="" readOnly></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><td colspan="2">&nbsp;</td></tr>';
		/*$resultado['ultimo_ncargo'] .= '<tr><th align="left">Tipo Pago</th><td><select name="tipo_pago['.$num_cargo.']" id="tipo_pago_'.$num_cargo.'"><option value="0">Seleccione</option>';
		foreach($array_tipo_pago as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'">'.$v.'</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Estatus Pago</th><td><select name="tipo_pago['.$num_cargo.']" id="tipo_pago_'.$num_cargo.'">';
		foreach($array_estatus_pago as $k=>$v) $resultado['ultimo_ncargo'] .= '<option value="'.$k.'">'.$v.'</option>';
		$resultado['ultimo_ncargo'] .= '</select></td></tr>';
		$resultado['ultimo_ncargo'] .= '<tr><th align="left">Cantidad Pagada</th><td><input type="text" class="textField" id="cantidad_pagada_'.$num_cargo.'" name="cantidad_pagada['.$num_cargo.']" size="10" value="" onKeyUp="validar_cantidad_pagada('.$num_cargo.')"></td></tr>';*/
		$resultado['ultimo_ncargo'] .= '</table></td></tr>';
		$resultado['ultimo_ncargo'] .= '</table>';
	}
	//echo $resultado['ultimo_ncargo'];
	echo json_encode($resultado);
	exit();
}

if($_POST['ajax'] == 4){
	$servicio = 0;
	if($_POST['cliente'] > 0){
		$res = mysql_query("SELECT checkservicio FROM clientes WHERE cve='".$_POST['cliente']."'");
		$row = mysql_fetch_array($res);
		$servicio = $row['checkservicio'];
	}

	$campomonto = "a.monto";

	if($servicio == 1)
		$campomonto = "IF(IFNULL(c.monto,0) = 0, a.monto, c.monto) as monto";
	if($_POST['delegacion'] > 0)
		$res2 = mysql_query("SELECT a.cve, a.nombre, $campomonto FROM zonas a INNER JOIN zonas_delegaciones b ON a.cve = b.zona LEFT JOIN clientes_zonas c ON a.cve = c.zona AND c.cliente = '".$_POST['cliente']."' WHERE a.tipo='".$_POST['tipo_ncargo']."' AND b.delegacion='".$_POST['delegacion']."' ORDER BY a.nombre");
	else
		$res2 = mysql_query("SELECT a.cve, a.nombre, $campomonto FROM zonas a LEFT JOIN clientes_zonas c ON a.cve = c.zona AND c.cliente = '".$_POST['cliente']."' WHERE a.tipo='".$_POST['tipo_ncargo']."' ORDER BY a.nombre");
	if(mysql_num_rows($res2) > 1) echo '<option value="0" costo="0">Seleccione</option>';
	while($row2=mysql_fetch_array($res2)){
		echo '<option value="'.$row2['cve'].'" costo="'.$row2['monto'].'">'.utf8_encode($row2['nombre']).'</option>';
	}
	exit();
}

if($_POST['ajax']==5){
	echo '<input type="hidden" id="tipo_alta" value="1">';
	echo '<input type="hidden" id="campo" value="'.$_POST['campo'].'">';
	echo '<input type="hidden" id="num_cargo_alta" value="'.$_POST['num_cargo'].'">';
	echo '<input type="hidden" id="tipo_dato_alta" value="'.$_POST['tipo_dato'].'">';
	echo '<h1>Alta de Contacto</h1>';
	echo '<table width="100"><tr><th>Nombre</th><td><input type="text" class="textField" id="nombre_contacto" value=""></td></tr>
		<tr><th>Telefono</th><td><input type="text" class="textField" id="telefono_contacto" value=""></td></tr>
		<tr><th>E-mail</th><td><input type="text" class="textField" id="email_contacto" value=""></td></tr></table>';
	exit();
}

if($_POST['ajax']==6){
	echo '<input type="hidden" id="tipo_alta" value="2">';
	echo '<input type="hidden" id="campo" value="'.$_POST['campo'].'">';
	echo '<input type="hidden" id="num_cargo_alta" value="'.$_POST['num_cargo'].'">';
	echo '<input type="hidden" id="tipo_dato_alta" value="'.$_POST['tipo_dato'].'">';
	echo '<h1>Alta de Direccion</h1>';
	echo '<table width="100">
	<tr><th>Calle</th><td><input type="text" class="textField" id="calle_direccion" value=""></td></tr>
	<tr><th>No Ext</th><td><input type="text" class="textField" id="numext_direccion" value=""></td></tr>
	<tr><th>No Int</th><td><input type="text" class="textField" id="numint_direccion" value=""></td></tr>
	<tr><th>Colonia</th><td><input type="text" class="textField" id="colonia_direccion" value=""></td></tr>
	<tr><th>Delegacion</th><td><select id="delegacion_direccion"><option value="">Seleccione</option>';
	foreach($array_delegacion as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>
	<tr><th>C.P.</th><td><input type="text" class="textField" id="cp_direccion" value=""></td></tr>
	<tr><th>Referencias</th><td><input type="text" class="textField" id="referencias_direccion" value=""></td></tr>
	</table>';
	exit();
}

if($_POST['ajax']==7){
	$resultado = array('tipo' => $_POST['tipo_alta'], 'campo' => $_POST['campo'], 'tipo_dato' => $_POST['tipo_dato'], 'num_cargo' => $_POST['num_cargo'], 'html' => '<option value="0" tel="" mail="">Seleccione</option>');
	if($_POST['tipo_alta'] == 1){
		mysql_query("INSERT clientes_contacto SET cliente='".$_POST['cliente']."',nombre='".$_POST['nombre']."',telefono='".$_POST['telefono']."',email='".$_POST['email']."'");
		$id=mysql_insert_id();
		$res1=mysql_query("SELECT * FROM clientes_contacto WHERE cliente='".$_POST['cliente']."' ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			$resultado['html'] .= '<option value="'.$row1['cve'].'" tel="'.$row1['telefono'].'" mail="'.$row1['email'].'"';
			if($id == $row1['cve']) $resultado['html'] .= ' selected';
			$resultado['html'] .= '>'.$row1['nombre'].'</option>';
		}
		$resultado['tipo'] = 'clase_contacto';
	}
	elseif($_POST['tipo_alta'] == 2){
		$resultado['html'] = '<option value="0" delegacion="0">Seleccione</option>';
		$direccion = $_POST['calle']." #".$_POST['numext'];
		if($_POST['numint'] != "") $direccion.=" Int. ".$_POST['numint'];
		$direccion.= " ".$_POST['colonia'];
		mysql_query("INSERT clientes_direccion SET cliente='".$_POST['cliente']."',calle='".$_POST['calle']."',numext='".$_POST['numext']."',
			numint='".$_POST['numint']."',delegacion='".$_POST['delegacion']."',colonia='".$_POST['colonia']."',
			cp='".$_POST['cp']."',referencias='".$_POST['referencias_direccion']."',direccion='".$direccion."'");
		$id=mysql_insert_id();
		$res1=mysql_query("SELECT * FROM clientes_direccion WHERE cliente='".$_POST['cliente']."' ORDER BY direccion");
		while($row1=mysql_fetch_array($res1)){
			$resultado['html'] .= '<option value="'.$row1['cve'].'" delegacion="'.$row1['delegacion'].'"';
			if($id == $row1['cve']) $resultado['html'] .= ' selected';
			$resultado['html'] .= '>'.$row1['direccion'].'</option>';
		}
		$resultado['tipo'] = 'clase_direccion';
	}
	echo json_encode($resultado);
	exit();
}

if($_POST['ajax'] == 8){
	$array_sucursales=array();
	$res = mysql_query("SELECT * FROM sucursales WHERE empresa = '".$_POST['cveempresa']."' ORDER BY nombre");
	while($row=mysql_fetch_array($res)) $array_sucursales[$row['cve']] = $row['nombre'];

	$array_grupo_clientes=array();
	$res = mysql_query("SELECT * FROM grupo_clientes WHERE empresa = '".$_POST['cveempresa']."' ORDER BY nombre");
	while($row=mysql_fetch_array($res)) $array_grupo_clientes[$row['cve']] = $row['nombre'];
	$row = array();
	echo '<table>';
	echo '<tr><td class="tableEnc">Edicion Datos de Clientes</td></tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><th align="left">Tipo Cliente</th><td><select name="camposi[tipo_cliente]" id="tipo_cliented" onChange="if(this.value!=\'3\'){
		$(\'.rcliente\').hide();
	}
	else{
		$(\'.rcliente\').show();
	}"><option value="0">Seleccione</option>';
	foreach($array_tipo_cliente as $k=>$v){
		echo '<option value="'.$k.'"';
		if($row['tipo_cliente'] == $k) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Nombre</th><td><input type="text" class="textField" name="camposi[nombre]" id="nombre" value="'.$row['nombre'].'" size="50"></td></tr>';
	echo '<tr><th align="left">Email</th><td><input type="text" class="textField" name="camposi[email]" id="email" value="'.$row['email'].'" size="100"></td></tr>';
	echo '<tr class="rcliente"><th align="left">RFC</th><td><input type="text" class="textField" name="camposi[rfc]" id="rfc" value="'.$row['rfc'].'" size="15" maxlength="13"></td></tr>';
	echo '<tr class="rcliente"><th align="left">Calle</th><td><input type="text" class="textField" name="camposi[calle]" id="calle" value="'.$row['calle'].'" size="30"></td></tr>';
	echo '<tr class="rcliente"><th align="left">Numero Exterior</th><td><input type="text" class="textField" name="camposi[numexterior]" id="numexterior" value="'.$row['numexterior'].'" size="10"></td></tr>';
	echo '<tr class="rcliente"><th align="left">Numero Interior</th><td><input type="text" class="textField" name="camposi[numinterior]" id="numinterior" value="'.$row['numinterior'].'" size="10"></td></tr>';
	echo '<tr class="rcliente"><th align="left">Colonia</th><td><input type="text" class="textField" name="camposi[colonia]" id="colonia" value="'.$row['colonia'].'" size="30"></td></tr>';
	echo '<tr class="rcliente"><th align="left">Localidad</th><td><input type="text" class="textField" name="camposi[localidad]" id="localidad" value="'.$row['localidad'].'" size="50"></td></tr>';
	echo '<tr class="rcliente"><th align="left">Municipio</th><td><input type="text" class="textField" name="camposi[municipio]" id="municipio" value="'.$row['municipio'].'" size="50"></td></tr>';
	echo '<tr class="rcliente"><th align="left">Estado</th><td><input type="text" class="textField" name="camposi[estado]" id="estado" value="'.$row['estado'].'" size="50"></td></tr>';
	echo '<tr class="rcliente"><th align="left">Codigo Postal</th><td><input type="text" class="textField" name="camposi[codigopostal]" id="codigopostal" value="'.$row['codigopostal'].'" size="50"></td></tr>';
	echo '<tr class="rcliente"><th align="left">Grupo</th><td><select name="camposi[grupo]" id="grupo"><option value="0">Seleccione</option>';
	foreach($array_grupo_clientes as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$row['grupo']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	if($Empresa['maneja_sucursal'] == 1){
		echo '<tr class="rcliente"><th align="left">Sucursal</th><td><select name="camposi[sucursal]" id="sucursal"><option value="0">Seleccione</option>';
		foreach($array_sucursales as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['sucursal']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
	}
	echo '<tr class="rcliente"><th align="left">Horario Laboral</th><td><input type="hidden" name="camposi[checkhorario]" id="checkhorario" value="'.intval($row['checkhorario']).'">
	<input type="checkbox" id="chkhorario" onClick="if(this.checked) $(\'#checkhorario\').val(\'1\'); else $(\'#checkhorario\').val(\'0\'); muestrahorarioc();"';
	if($row['checkhorario'] == 1) echo ' checked';
	echo '></td></tr>';
	echo '<tr class="rcliente"';
	if($row['checkhorario'] != 1) echo ' style="display:none;"';
	echo '><th align="left">Horario</th><td><textarea cols="30" rows="3" name="camposi[horario]" id="horario" class="textField">'.$row['horario'].'</textarea></td></tr>';
	echo '<tr><th align="left">Descripcion</th><td><input type="text" class="textField" name="camposi[descripcion]" id="descripcion" value="'.$row['descripcion'].'" size="100"></td></tr>';
	echo '<tr class="rcliente"><th align="left">Maneja Servicio</th><td><input type="hidden" name="camposi[checkservicio]" id="checkservicio" value="'.intval($row['checkservicio']).'">
	<input type="checkbox" id="chkservicio" onClick="if(this.checked) $(\'#checkservicio\').val(\'1\'); else $(\'#checkservicio\').val(\'0\'); muestraservicioc();"';
	if($row['checkservicio'] == 1) echo ' checked';
	echo '></td></tr>';
	$res1 = mysql_query("SELECT a.cve, CONCAT(a.nombre,' ',c.nombre) as nombre, IFNULL(b.cve, 0) as cliente_zona_id, IF(IFNULL(b.monto, 0)=0, a.monto, b.monto) as monto 
		FROM zonas a INNER JOIN tipo_ncargo c ON c.cve = a.tipo LEFT JOIN clientes_zonas b ON a.cve = b.zona AND b.cliente = '".$_POST['reg']."' ORDER BY a.nombre");
	while($row1 = mysql_fetch_array($res1)){
		echo '<tr class="rcliente" ';
		if($row['checkservicio'] != 1) echo ' style="display:none;"';
		echo '><th align="left">'.$row1['nombre'].'</th>
		<td><input type="text" class="textField clientes_zonas" name="cliente_zona['.$row1['cve'].']" size="10" value="'.$row1['monto'].'">
		<input type="hidden" name="cliente_zona_id['.$row1['cve'].']" value="'.$row1['cliente_zona_id'].'"></td></tr>';	
	}
	echo '<tr class="rcliente" style="display:none;"><th align="left">Cuenta contable</th><td><input type="hidden" name="idcuentacontable" id="idcuentacontable" value="'.$row['idcuentacontable'].'"><input type="text" class="textField" name="cuentacontable" id="cuentacontable" value="'.$row['cuentacontable'].'" size="10" readonly><input type="text" class="textField" name="nombrecuentacontable" id="nombrecuentacontable" value="'.$row['nombrecuentacontable'].'" size="50" readonly><a href="#" onclick="buscarCuentas(0);"> <img src="images/visualizar.gif" border="0"></a></td></tr>';
	echo '</table>';
	echo '<script>


			function muestraservicioc(){
				if($("#checkservicio").val()=="1"){
					$(".clientes_zonas").each(function(){
						this.value = 0;
						$(this).parents("tr:first").show();
					});
				}
				else{
					$(".clientes_zonas").each(function(){
						this.value = 0;
						$(this).parents("tr:first").hide();
					});
				}
			}

			
			function muestrahorarioc(){
				if($("#checkhorario").val()=="1"){
					$("#horario").val("").parents("tr:first").show();
				}
				else{
					$("#horario").val("").parents("tr:first").hide();
				}
			}
		</script>';
	exit();
}

if($_POST['ajax']==15){
	echo '<h1>Alta de medio por el que se entero</h1>';
	echo '<table width="100"><tr><th>Nombre</th><td><input type="text" class="textField" id="nombre_medio" value=""></td></tr></table>';
	exit();
}

if($_POST['ajax']==17){
	$resultado = array('html' => '<option value="0">Seleccione</option>');
	mysql_query("INSERT medios_entero SET nombre='".$_POST['nombre']."'");
	$id=mysql_insert_id();
	$res1=mysql_query("SELECT * FROM medios_entero ORDER BY nombre");
	while($row1=mysql_fetch_array($res1)){
		$resultado['html'] .= '<option value="'.$row1['cve'].'"';
		if($id == $row1['cve']) $resultado['html'] .= ' selected';
		$resultado['html'] .= '>'.utf8_encode($row1['nombre']).'</option>';
	}
	echo json_encode($resultado);
	exit();
}

if($_POST['ajax'] == 18){
	$resultado = array('selects'=>'<option value="0">Seleccione</option>');
	$datos = json_decode($_POST['campos'], true);
	$campos="";
	foreach($datos['camposi'] as $k=>$v){
		$campos.=",".$k."='".$v."'";
	}
	$campos.=",idcuentacontable='".$datos['idcuentacontable']."',cuentacontable='".$datos['cuentacontable']."',nombrecuentacontable='".$datos['nombrecuentacontable']."'";
	mysql_query("INSERT clientes SET empresa='1',fechayhora='".fechaLocal()." ".horaLocal()."',usuario='".$_POST['cveusuario']."'".$campos) or die(mysql_error());
	$_POST['reg'] = mysql_insert_id();
	if($datos['camposi']['checkservicio'] == 1){
		$res1 = mysql_query("SELECT a.cve, CONCAT(a.nombre,' ',c.nombre) as nombre, IFNULL(b.cve, 0) as cliente_zona_id, IF(IFNULL(b.monto, 0)=0, a.monto, b.monto) as monto 
			FROM zonas a INNER JOIN tipo_ncargo c ON c.cve = a.tipo LEFT JOIN clientes_zonas b ON a.cve = b.zona AND b.cliente = '".$_POST['reg']."' ORDER BY a.nombre");
		while($row1 = mysql_fetch_array($res1)){
			if($datos['cliente_zona_id'][$row1['cve']] == 0){
				mysql_query("INSERT clientes_zonas SET cliente='".$_POST['reg']."',zona='".$row1['cve']."',monto='".$datos['cliente_zona'][$row1['cve']]."',cambios = '".$_POST['cveusuario'].",".fechaLocal()." ".horaLocal().",".$datos['cliente_zona'][$row1['cve']]."'");
			}
			elseif($datos['cliente_zona'][$row1['cve']] != $row1['monto']){
				mysql_query("UPDATE clientes_zonas SET monto = '".$datos['cliente_zona'][$row1['cve']]."',cambios = CONCAT(cambios,'|".$_POST['cveusuario'].",".fechaLocal()." ".horaLocal().",".$datos['cliente_zona'][$row1['cve']]."') WHERE cve='".$datos['cliente_zona_id'][$row1['cve']]."' ");
			}
		}	
	}
	$res = mysql_query("SELECT * FROM clientes ORDER BY nombre");
	while($row = mysql_fetch_array($res))
	{
		$resultado['selects'] .= '<option value="'.$row['cve'].'"';
		if($row['cve'] == $_POST['reg']) $resultado['selects'] .= ' selected';
		$resultado['selects'] .= '>'.utf8_encode($row['nombre']).'</option>';
	}
	echo json_encode($resultado);
	exit();
}

top($_SESSION);
if($_POST['cmd'] == 3){
	mysql_query("UPDATE pedidos SET estatus = '3',usucan='".$_POST['cveusuario']."',fechacan=NOW() WHERE cve='".$_POST['reg']."'");
	$_POST['cmd'] = 0;
}

if($_POST['cmd']==2){
	if($_POST['reg'] > 0){
		$camposcerrado="";
		$res = mysql_query("SELECT estatus FROM pedidos WHERE cve='".$_POST['reg']."'");
		$row = mysql_fetch_array($res);
		if($row['estatus'] != '2' && $_POST['estatus'] == '2'){
			$camposcerrado=",usucerro='".$_POST['cveusuario']."',fechacerro=NOW()";
		}
		mysql_query("UPDATE pedidos SET cliente='".$_POST['cliente']."',monto='".$_POST['monto']."',sin_iva='".$_POST['sin_iva']."',
			multientrega_descuento='".$_POST['multientrega_descuento']."',tipo_cliente='".$_POST['tipo_cliente']."',
			medio_entero='".$_POST['medio_entero']."',pagar_en='".$_POST['pagar_en']."',tipo_pago='".$_POST['tipo_pago']."', 
			estatus_pago='".$_POST['estatus_pago']."',cantidad_pagada='".$_POST['cantidad_pagada']."',estatus='".$_POST['estatus']."' $camposcerrado
			WHERE cve='".$_POST['reg']."'");
		mysql_query("UPDATE pedidos_detalles SET pedido=pedido*(-1) WHERE pedido='".$_POST['reg']."'");
		$id = $_POST['reg'];
	}
	else{
		mysql_query("INSERT pedidos SET cliente='".$_POST['cliente']."',monto='".$_POST['monto']."',sin_iva='".$_POST['sin_iva']."',
			multientrega_descuento='".$_POST['multientrega_descuento']."',estatus='A',usuario='".$_POST['cveusuario']."',
			fecha=CURDATE(),hora=CURTIME(),tipo_cliente='".$_POST['tipo_cliente']."',
			medio_entero='".$_POST['medio_entero']."',pagar_en='".$_POST['pagar_en']."',tipo_pago='".$_POST['tipo_pago']."', 
			estatus_pago='".$_POST['estatus_pago']."',cantidad_pagada='".$_POST['cantidad_pagada']."'");
		$id = mysql_insert_id();
	}
	$create_at = date("Y-m-d H:i:s");
	foreach($_POST['contacto'] as $k=>$v){
		mysql_query("INSERT pedidos_detalles SET create_at='$create_at',pedido='$id',contacto='$v',
			delegacion_recoleccion='".$_POST['delegacion_recoleccion'][$k]."',
			direccion_recoleccion='".$_POST['direccion_recoleccion'][$k]."',
			fecha_recoleccion='".$_POST['fecha_recoleccion'][$k]."',
			hora_recoleccion='".$_POST['hora_recoleccion'][$k].":".$_POST['min_recoleccion'][$k]."',
			mensajero='".$_POST['mensajero'][$k]."',
			obs_recoleccion='".$_POST['obs_recoleccion'][$k]."',
			delegacion_entrega='".$_POST['delegacion_entrega'][$k]."',
			direccion_entrega='".$_POST['direccion_entrega'][$k]."',
			fecha_entrega='".$_POST['fecha_entrega'][$k]."',
			hora_entrega='".$_POST['hora_entrega'][$k].":".$_POST['min_entrega'][$k]."',
			obs_entrega='".$_POST['obs_entrega'][$k]."',
			estatus_ncargo='".$_POST['estatus_ncargo'][$k]."',
			recibio='".$_POST['recibio'][$k]."',
			subtotal='".$_POST['subtotal'][$k]."',
			iva='".$_POST['iva'][$k]."',
			total='".$_POST['total'][$k]."',
			tipo_pago='".$_POST['tipo_pago'][$k]."',
			estatus_pago='".$_POST['estatus_pago'][$k]."',
			cantidad_pagada='".$_POST['cantidad_pagada'][$k]."',
			tipo_ncargo='".$_POST['tipo_ncargo'][$k]."',
			zona='".$_POST['zona'][$k]."'") or die(mysql_error());
	}
	$_POST['cmd'] = 0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/



if ($_POST['cmd']==1) {
	$res = mysql_query("SELECT * FROM pedidos WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	echo '<div id="altas1" style="display:none;"></div>';	
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1 && $row['estatus'] != 2 && $row['estatus'] != 3)
			echo '<td><a href="#" onClick="$(\'#panel\').show();if(validar_ncargo()){atcr(\'pedidos.php\',\'\',\'2\',\''.$_POST['reg'].'\');}/*validar_datos(\''.$_POST['reg'].'\');*/"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'pedidos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	$nivelusuario = nivelUsuario();
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Edicion Ncargo</td></tr>';
	echo '</table>';
	
	$array_contactos=array();
	$array_direcciones=array();
	if($row['cliente'] > 0){
		$resC = mysql_query("SELECT * FROM clientes WHERE cve='".$row['cliente']."'");
		$rowC=mysql_fetch_array($resC);
		$res1=mysql_query("SELECT * FROM clientes_contacto WHERE cliente='".$row['cliente']."' ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			$array_contactos[$row1['cve']] = array($row1['nombre'],$row1['telefono'],$row1['email']);
		}
		$res1=mysql_query("SELECT * FROM clientes_direccion WHERE cliente='".$row['cliente']."' ORDER BY direccion");
		while($row1=mysql_fetch_array($res1)){
			$array_direcciones[$row1['cve']] = $row1['direccion'].'';
			$array_dir_delegacion[$row1['cve']] = $row1['delegacion'].'';
		}
		
	}
	echo '<div id="altas" style="display:none;"></div>';
	echo '<table width="100%"><tr><td width="50%"><table>';
	echo '<tr><th align="left">No Ncargo</th><td><b>'.$row['cve'].'</b></td></tr>';
	echo '<tr><th align="left">Cliente</th><td><select style="width:300px" name="cliente" id="cliente" onChange="traer_datos()"><option value="0">Seleccione</option>';
	foreach($array_clientes as $k=>$v){
		echo '<option value="'.$k.'"';
		if($row['cliente'] == $k) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select><span style="cursor:pointer;" onClick="agregar_cliente()"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
	echo '<script>$("#cliente").select2();</script>';
	echo '<tr><th align="left">Tipo Cliente</th><td><select name="tipo_cliente" id="tipo_cliente" disabled><option value="0">Seleccione</option>';
	foreach($array_tipo_cliente as $k=>$v){
		echo '<option value="'.$k.'"';
		if($rowC['tipo_cliente'] == $k) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	if($_POST['reg'] > 0){
		echo '<tr><th align="left">Estatus</th><td><select name="estatus" id="estatus">';
		foreach($array_estatus_pedido as $k=>$v){
			if($k!='C'){
				echo '<option value="'.$k.'"';
				if($row['estatus'] == $k) echo ' selected';
				echo '>'.$v.'</option>';
			}
		}
		echo '</select></td></tr>';
	}
	echo '<tr><th align="left">Medio por el que se entero</th><td><select name="medio_entero" id="medio_entero"><option value="0">Seleccione</option>';
	foreach($array_medio_entero as $k=>$v){
		echo '<option value="'.$k.'"';
		if($row['medio_entero'] == $k) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select><span style="cursor:pointer;" onClick="agregarmedio()"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
	echo '<tr><th align="left">Sin IVA</th><td><input type="checkbox" name="sin_iva" id="sin_iva" value="1" onClick="calcular_todas()"';
	if($row['sin_iva'] == 1) echo ' checked';
	echo '></td></tr>';
	echo '<tr style="display:none;"><th align="left">Multientrega con descuento</th><td><input type="checkbox" name="multientrega_descuento" id="multientrega_descuento" value="1" onClick="calcular_todas()"';
	if($row['multientrega_descuento'] == 1) echo ' checked';
	echo '></td></tr>';
	echo '<tr><th align="left">Total Ncargo</th><td><input type="text" class="readOnly" name="monto" id="monto" value="'.$row['monto'].'" readOnly></td></tr>';
	echo '</table></td>';
	echo '<td width="50%"><table>';
	echo '<tr><th align="left">Pagar en</th><td><select name="pagar_en" id="pagar_en"><option value="0">Seleccione</option>';
	foreach($array_pagar_en as $k=>$v){
		echo '<option value="'.$k.'"';
		if($row['pagar_en'] == $k) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Tipo Pago</th><td><select name="tipo_pago" id="tipo_pago"><option value="0">Seleccione</option>';
	foreach($array_tipo_pago as $k=>$v){
		echo '<option value="'.$k.'"';
		if($row['tipo_pago'] == $k) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Estatus Pago</th><td><select name="estatus_pago" id="estatus_pago">';
	foreach($array_estatus_pago as $k=>$v){
		echo '<option value="'.$k.'"';
		if($row['estatus_pago'] == $k) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Cantidad Pagada</th><td><input type="text" class="textField" id="cantidad_pagada" name="cantidad_pagada" size="10" value="'.$row['cantidad_pagada'].'" onKeyUp="validar_cantidad_pagada('.$num_cargo.')"></td></tr>';
	echo '</table></td></tr></table>';
	echo '<input type="hidden" name="num_cargo" id="num_cargo" value=""><div width="100%" id="encargos">';
	$num_cargo = 0;
	$res1 = mysql_query("SELECT * FROM pedidos_detalles WHERE pedido = '".$_POST['reg']."'");
	while($row1=mysql_fetch_array($res1)){
		echo '<hr class="crecoleccion_'.$num_cargo.'">';
		if($num_cargo>0)
			echo '<span class="crecoleccion_'.$num_cargo.'" style="cursor:pointer" onClick="quitar_recoleccion('.$num_cargo.')"><img src="images/basura.gif">Quitar<br></span>';
		echo '<table class="crecoleccion_'.$num_cargo.'" id="tabla_'.$num_cargo.'" width="100%"><tr><td align="left" width="50%" valign="top">';
		if($num_cargo == 0){
			echo '<h1>Recoleccion</h1>';
			echo '<table>';
			echo '<tr><th align="left">Contacto</th><td><select name="contacto['.$num_cargo.']" id="contacto_'.$num_cargo.'" class="clase_contacto" onChange="mostrar_datos_contacto('.$num_cargo.')"><option value="0">Seleccione</option>';
			foreach($array_contactos as $k=>$v){ 
				echo '<option value="'.$k.'" tel="'.$v[1].'" mail="'.$v[2].'"';
				if($k==$row1['contacto']) echo ' selected';
				echo  '>'.$v[0].'</option>';
			}
			echo '</select><span style="cursor:pointer;" onClick="agregar_contacto(\'contacto_'.$num_cargo.'\')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
			echo '<tr><th align="left">Telefono Contacto</th><td><input type="text" id="telefono_contacto_'.$num_cargo.'" value="'.$array_contactos[$row1['contacto']][1].'" class="readOnly" readOnly></td></tr>';
			echo '<tr><th align="left">Email Contacto</th><td><input type="text" id="email_contacto_'.$num_cargo.'" value="'.$array_contactos[$row1['contacto']][2].'" class="readOnly" readOnly></td></tr>';
			echo '<tr><th align="left">Direccion</th><td><select name="direccion_recoleccion['.$num_cargo.']" id="direccion_recoleccion_'.$num_cargo.'" onChange="seleccionar_delegacion(\'recoleccion\','.$num_cargo.')" class="clase_direccion" delegacion="0"><option value="0">Seleccione</option>';
			foreach($array_direcciones as $k=>$v){
				echo '<option value="'.$k.'" delegacion="'.$array_dir_delegacion[$k].'"';
				if($row1['direccion_recoleccion'] == $k) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_recoleccion_'.$num_cargo.'\',\'recoleccion\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
			echo '<tr><th align="left">Delegacion</th><td><select name="delegacion_recoleccion['.$num_cargo.']" id="delegacion_recoleccion_'.$num_cargo.'"><option value="0">Seleccione</option>';
			foreach($array_delegacion as $k=>$v){
				if($row1['delegacion_recoleccion'] == $k){
					echo '<option value="'.$k.'"';
					echo ' selected';
					echo '>'.$v.'</option>';
				}
			}
			echo '</select></td></tr>';
			if($row1['fecha_recoleccion'] == '0000-00-00') $row1['fecha_recoleccion'] = '';
			echo '<tr><th align="left">Fecha Recoleccion</th><td>'.campo_fecha('fecha_recoleccion['.$num_cargo.']', 'fecha_recoleccion_'.$num_cargo, $row1['fecha_recoleccion'], 'fechas_recoleccion').'</td></tr>';
			echo '<tr><th align="left">Hora Recoleccion</th><td><select name="hora_recoleccion['.$num_cargo.']" id="hora_recoleccion_'.$num_cargo.'">';
			for($i=0;$i<=23;$i++){
				echo '<option value="'.sprintf('%02s',$i).'"';
				if($i==intval(substr($row1['hora_recoleccion'],0,2))) echo ' selected';
				echo '>'.sprintf('%02s',$i).'</option>';
			}
			echo '</select>:<select name="min_recoleccion['.$num_cargo.']" id="min_recoleccion_'.$num_cargo.'">';
			for($i=0;$i<=50;$i+=10){
				echo '<option value="'.sprintf('%02s',$i).'"';
				if($i==intval(substr($row1['hora_recoleccion'], 3, 2))) echo ' selected';
				echo '>'.sprintf('%02s',$i).'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Ncargado</th><td><select name="mensajero['.$num_cargo.']" id="mensajero_'.$num_cargo.'"><option value="0">Por asignar</option>';
			//foreach($array_personal as $k=>$v){
			$resNcargado = mysql_query("SELECT a.cve, a.nombre FROM personal a LEFT JOIN asignacion b ON a.cve = b.personal AND b.estatus='A' WHERE a.puesto = 1 AND (IFNULL(b.cve,0)>0 OR a.cve = '".$row1['mensajero']."') ORDER BY a.nombre");
			while($Ncargado = mysql_fetch_array($resNcargado)){
				echo '<option value="'.$Ncargado['cve'].'" style="color:'.$array_colorpersonal[$Ncargado['cve']].';"';
				if($row1['mensajero'] == $Ncargado['cve']) echo ' selected';
				echo '>'.$Ncargado['nombre'].'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr><th align="left">Ncargo</th><td><textarea cols="30" rows="3" name="obs_recoleccion['.$num_cargo.']" id="obs_recoleccion_'.$num_cargo.'">'.$row1['obs_recoleccion'].'</textarea></td></tr>';
			echo '</table>';
		}
		echo '</td><td><h1>Entrega</h1><table>';
		echo '<tr><th align="left">Direccion</th><td><select name="direccion_entrega['.$num_cargo.']" id="direccion_entrega_'.$num_cargo.'" class="clase_direccion" onChange="seleccionar_delegacion(\'entrega\','.$num_cargo.')"><option value="0" delegacion="0">Seleccione</option>';
		foreach($array_direcciones as $k=>$v){
			echo '<option value="'.$k.'" delegacion="'.$array_dir_delegacion[$k].'"';
			if($row1['direccion_entrega'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_entrega_'.$num_cargo.'\',\'entrega\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
		echo '<tr><th align="left">Delegacion</th><td><select name="delegacion_entrega['.$num_cargo.']" id="delegacion_entrega_'.$num_cargo.'"><option value="0">Seleccione</option>';
		foreach($array_delegacion as $k=>$v){
			if($row1['delegacion_entrega'] == $k){
				echo '<option value="'.$k.'"';
				echo ' selected';
				echo '>'.$v.'</option>';
			}
		}
		echo '</select></td></tr>';
		if($row1['fecha_entrega'] == '0000-00-00') $row1['fecha_entrega'] = '';
		echo '<tr><th align="left">Fecha Entrega</th><td>'.campo_fecha('fecha_entrega['.$num_cargo.']', 'fecha_entrega_'.$num_cargo, $row1['fecha_entrega'], 'fechas_entrega').'</td></tr>';
		echo '<tr><th align="left">Hora Entrega</th><td><select name="hora_entrega['.$num_cargo.']" id="hora_entrega_'.$num_cargo.'">';
		for($i=0;$i<=23;$i++){
			echo '<option value="'.sprintf('%02s',$i).'"';
			if($i==intval(substr($row1['hora_entrega'],0,2))) echo ' selected';
			echo '>'.sprintf('%02s',$i).'</option>';
		}
		echo '</select>:<select name="min_entrega['.$num_cargo.']" id="min_entrega_'.$num_cargo.'">';
		for($i=0;$i<=50;$i+=10){
			echo '<option value="'.sprintf('%02s',$i).'"';
			if($i==intval(substr($row1['hora_entrega'], 3, 2))) echo ' selected';
			echo '>'.sprintf('%02s',$i).'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr';
		if($nivelusuario < 3) echo ' style="display:none;"';
		echo '><th align="left">Estatus Ncargo</th><td><select name="estatus_ncargo['.$num_cargo.']" id="estatus_ncargo_'.$num_cargo.'">';
		foreach($array_estatus_detalle as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row1['estatus_ncargo'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr';
		if($nivelusuario < 3) echo ' style="display:none;"';
		echo '><th align="left">Recibido por</th><td><input type="text" class="textField" name="recibio['.$num_cargo.']" id="recibio_'.$num_cargo.'" value="'.$row1['recibio'].'"></td></tr>';
		echo '<tr><th align="left">Observaciones</th><td><textarea cols="30" rows="3" name="obs_entrega['.$num_cargo.']" id="obs_entrega_'.$num_cargo.'">'.$row1['obs_entrega'].'</textarea></td></tr>';
		echo '<tr><th align="left">Tipo </th><td><select name="tipo_ncargo['.$num_cargo.']" id="tipo_ncargo_'.$num_cargo.'" class="ncargo clase_tipo_ncargo" onChange="traer_zonas('.$num_cargo.')">';
		if($rowC['servicio'] == 0)
			echo '<option value="0">Seleccione</option>';
		foreach($array_tipo_ncargo as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row1['tipo_ncargo'] == $k || ($k==1 && $row1['tipo_ncargo'] == 0)) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Servicio</th><td><select name="zona['.$num_cargo.']" id="zona_'.$num_cargo.'" class="clase_zona" numcargo="'.$num_cargo.'" onChange="mostrar_costo('.$num_cargo.')">';
		$campomonto = "a.monto";

		if($rowC['checkservicio'] == 1)
			$campomonto = "IF(IFNULL(c.monto,0) = 0, a.monto, c.monto) as monto";
		
		if($row1['delegacion_entrega'] > 0)
			$res2 = mysql_query("SELECT a.cve, a.nombre, $campomonto FROM zonas a INNER JOIN zonas_delegaciones b ON a.cve = b.zona LEFT JOIN clientes_zonas c ON a.cve = c.zona AND c.cliente = '".$row['cliente']."' WHERE a.tipo='".$row1['tipo_ncargo']."' AND b.delegacion='".$row1['delegacion_entrega']."' ORDER BY a.nombre");
		else
			$res2 = mysql_query("SELECT a.cve, a.nombre, $campomonto FROM zonas a LEFT JOIN clientes_zonas c ON a.cve = c.zona AND c.cliente = '".$row['cliente']."' WHERE a.tipo='".$row1['tipo_ncargo']."' ORDER BY a.nombre");
		if(mysql_num_rows($res2) > 1) echo '<option value="0" costo="0">Seleccione</option>';
		while($row2=mysql_fetch_array($res2)){
			echo '<option value="'.$row2['cve'].'" costo="'.$row2['monto'].'"';
			if($row1['zona'] == $row2['cve']) echo ' selected';
			echo '>'.$row2['nombre'].'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Costo</th><td><input type="text" class="readOnly" id="subtotal_'.$num_cargo.'" name="subtotal['.$num_cargo.']" size="10" value="'.$row1['subtotal'].'" readOnly></td></tr>';
		echo '<tr><th align="left">IVA</th><td><input type="text" class="readOnly" id="iva_'.$num_cargo.'" name="iva['.$num_cargo.']" size="10" value="'.$row1['iva'].'" readOnly></td></tr>';
		echo '<tr><th align="left">Total</th><td><input type="text" class="readOnly totales" id="total_'.$num_cargo.'" name="total['.$num_cargo.']" size="10" value="'.$row1['total'].'" readOnly></td></tr>';
		echo '<tr><td colspan="2">&nbsp;</td></tr>';
		/*echo '<tr><th align="left">Tipo Pago</th><td><select name="tipo_pago['.$num_cargo.']" id="tipo_pago_'.$num_cargo.'"><option value="0">Seleccione</option>';
		foreach($array_tipo_pago as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row1['tipo_pago'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Estatus Pago</th><td><select name="tipo_pago['.$num_cargo.']" id="tipo_pago_'.$num_cargo.'">';
		foreach($array_estatus_pago as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row1['estatus_pago'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Cantidad Pagada</th><td><input type="text" class="textField" id="cantidad_pagada_'.$num_cargo.'" name="cantidad_pagada['.$num_cargo.']" size="10" value="'.$row1['cantidad_pagada'].'" onKeyUp="validar_cantidad_pagada('.$num_cargo.')"></td></tr>';*/
		echo '</table></td></tr>';
		echo '</table>';
		$num_cargo++;
	}
	if($num_cargo == 0){
		echo '<hr class="crecoleccion_'.$num_cargo.'">';
		echo '<table class="crecoleccion_'.$num_cargo.'" id="tabla_'.$num_cargo.'" width="100%"><tr><td align="left" width="50%" valign="top"><h1>Recoleccion</h1>';
		echo '<table>';
		echo '<tr><th align="left">Contacto</th><td><select name="contacto['.$num_cargo.']" id="contacto_'.$num_cargo.'" class="clase_contacto" onChange="mostrar_datos_contacto('.$num_cargo.')"><option value="0">Seleccione</option>';
		foreach($array_contactos as $k=>$v) echo '<option value="'.$k.'" tel="'.$v[1].'" mail="'.$v[2].'">'.$v[0].'</option>';
		echo '</select><span style="cursor:pointer;" onClick="agregar_contacto(\'contacto_'.$num_cargo.'\')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
		echo '<tr><th align="left">Telefono Contacto</th><td><input type="text" id="telefono_contacto_'.$num_cargo.'" class="readOnly" readOnly></td></tr>';
		echo '<tr><th align="left">Email Contacto</th><td><input type="text" id="email_contacto_'.$num_cargo.'" class="readOnly" readOnly></td></tr>';
		echo '<tr><th align="left">Direccion</th><td><select name="direccion_recoleccion['.$num_cargo.']" id="direccion_recoleccion_'.$num_cargo.'" class="clase_direccion" onChange="seleccionar_delegacion(\'recoleccion\','.$num_cargo.')"><option value="0" delegacion="0">Seleccione</option>';
		echo '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_recoleccion_'.$num_cargo.'\',\'recoleccion\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
		echo '<tr><th align="left">Delegacion</th><td><select name="delegacion_recoleccion['.$num_cargo.']" id="delegacion_recoleccion_'.$num_cargo.'"><option value="0">Seleccione</option>';
		//foreach($array_delegacion as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
		echo '</select></td></tr>';
		echo '<tr><th align="left">Fecha Recoleccion</th><td>'.campo_fecha('fecha_recoleccion['.$num_cargo.']', 'fecha_recoleccion_'.$num_cargo, '', 'fechas_recoleccion').'</td></tr>';
		echo '<tr><th align="left">Hora Recoleccion</th><td><select name="hora_recoleccion['.$num_cargo.']" id="hora_recoleccion_'.$num_cargo.'">';
		for($i=0;$i<=23;$i++) echo '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
		echo '</select>:<select name="min_recoleccion['.$num_cargo.']" id="min_recoleccion_'.$num_cargo.'">';
		for($i=0;$i<=50;$i+=10) echo '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
		echo '</select></td></tr>';
		echo '<tr><th align="left">Ncargado</th><td><select name="mensajero['.$num_cargo.']" id="mensajero_'.$num_cargo.'"><option value="0">Por asignar</option>';
		$resNcargado = mysql_query("SELECT a.cve, a.nombre FROM personal a LEFT JOIN asignacion b ON a.cve = b.personal AND b.estatus='A' WHERE a.puesto = 1 AND IFNULL(b.cve,0)>0 ORDER BY a.nombre");
		while($Ncargado = mysql_fetch_array($resNcargado)){
			echo '<option value="'.$Ncargado['cve'].'" style="color:'.$array_colorpersonal[$Ncargado['cve']].';"';
			echo '>'.$Ncargado['nombre'].'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Ncargo</th><td><textarea cols="30" rows="3" name="obs_recoleccion['.$num_cargo.']" id="obs_recoleccion_'.$num_cargo.'"></textarea></td></tr>';
		echo '</table>';
		echo '</td><td><h1>Entrega</h1><table>';
		echo '<tr><th align="left">Direccion</th><td><select name="direccion_entrega['.$num_cargo.']" id="direccion_entrega_'.$num_cargo.'" class="clase_direccion" onChange="seleccionar_delegacion(\'entrega\','.$num_cargo.')"><option value="0" delegacion="0">Seleccione</option>';
		echo '</select><span style="cursor:pointer;" onClick="agregar_direccion(\'direccion_entrega_'.$num_cargo.'\',\'entrega\','.$num_cargo.')"><img src="images/add.png" width="12" height="12" border="0"></span></td></tr>';
		echo '<tr><th align="left">Delegacion</th><td><select name="delegacion_entrega['.$num_cargo.']" id="delegacion_entrega_'.$num_cargo.'"><option value="0">Seleccione</option>';
		//foreach($array_delegacion as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
		echo '</select></td></tr>';
		echo '<tr><th align="left">Fecha Entrega</th><td>'.campo_fecha('fecha_entrega['.$num_cargo.']', 'fecha_entrega_'.$num_cargo, '', 'fechas_entrega').'</td></tr>';
		echo '<tr><th align="left">Hora Entrega</th><td><select name="hora_entrega['.$num_cargo.']" id="hora_entrega_'.$num_cargo.'">';
		for($i=0;$i<=23;$i++) echo '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
		echo '</select>:<select name="min_entrega['.$num_cargo.']" id="min_entrega_'.$num_cargo.'">';
		for($i=0;$i<=50;$i+=10) echo '<option value="'.sprintf('%02s',$i).'">'.sprintf('%02s',$i).'</option>';
		echo '</select></td></tr>';
		echo '<tr style="display:none;"><th align="left">Estatus Ncargo</th><td><select name="estatus_ncargo['.$num_cargo.']" id="estatus_ncargo_'.$num_cargo.'">';
		foreach($array_estatus_detalle as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
		echo '</select></td></tr>';
		echo '<tr style="display:none;"><th align="left">Recibido por</th><td><input type="text" class="textField" name="recibio['.$num_cargo.']" id="recibio_'.$num_cargo.'" value=""></td></tr>';
		echo '<tr><th align="left">Observaciones</th><td><textarea cols="30" rows="3" name="obs_entrega['.$num_cargo.']" id="obs_entrega_'.$num_cargo.'"></textarea></td></tr>';
		echo '<tr><th align="left">Tipo </th><td><select name="tipo_ncargo['.$num_cargo.']" id="tipo_ncargo_'.$num_cargo.'" class="ncargo clase_tipo_ncargo" onChange="traer_zonas('.$num_cargo.')"><option value="0">Seleccione</option>';
		foreach($array_tipo_ncargo as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==1) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Servicio</th><td><select name="zona['.$num_cargo.']" id="zona_'.$num_cargo.'" class="clase_zona" numcargo="'.$num_cargo.'" onChange="mostrar_costo('.$num_cargo.')"><option value="0" costo="0">Seleccione</option>';
		echo '</select></td></tr>';
		echo '<tr><th align="left">Costo</th><td><input type="text" class="readOnly" id="subtotal_'.$num_cargo.'" name="subtotal['.$num_cargo.']" size="10" value="" readOnly></td></tr>';
		echo '<tr><th align="left">IVA</th><td><input type="text" class="readOnly" id="iva_'.$num_cargo.'" name="iva['.$num_cargo.']" size="10" value="" readOnly></td></tr>';
		echo '<tr><th align="left">Total</th><td><input type="text" class="readOnly totales" id="total_'.$num_cargo.'" name="total['.$num_cargo.']" size="10" value="" readOnly></td></tr>';
		echo '<tr><td colspan="2">&nbsp;</td></tr>';
		/*echo '<tr><th align="left">Tipo Pago</th><td><select name="tipo_pago['.$num_cargo.']" id="tipo_pago_'.$num_cargo.'"><option value="0">Seleccione</option>';
		foreach($array_tipo_pago as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
		echo '</select></td></tr>';
		echo '<tr><th align="left">Estatus Pago</th><td><select name="tipo_pago['.$num_cargo.']" id="tipo_pago_'.$num_cargo.'">';
		foreach($array_estatus_pago as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
		echo '</select></td></tr>';
		echo '<tr><th align="left">Cantidad Pagada</th><td><input type="text" class="textField" id="cantidad_pagada_'.$num_cargo.'" name="cantidad_pagada['.$num_cargo.']" size="10" value="" onKeyUp="validar_cantidad_pagada('.$num_cargo.')"></td></tr>';*/
		echo '</table></td></tr>';
		echo '</table>';
		$num_cargo++;
	}
	echo '</div>';
	echo '<input type="button" class="textField" onClick="agregar_ncargo()" value="Agregar Recolecci&oacute;n">';
	echo '<script>
			delegaciones = {};
			delegaciones[0] = "Seleccione";
			';
	foreach($array_delegacion as $k=>$v){
		echo 'delegaciones['.$k.'] = "'.$v.'";
		';
	}
	echo    ';

			function seleccionar_delegacion(tipo, num_cargo){
				delegacion = $("#direccion_"+tipo+"_"+num_cargo).find(\'option[value="\'+$("#direccion_"+tipo+"_"+num_cargo).val()+\'"]\').attr("delegacion");
				$("#delegacion_"+tipo+"_"+num_cargo).html(\'<option value="\'+delegacion+\'">\'+delegaciones[delegacion]+\'</option>\');
				if(tipo=="entrega"){
					traer_zonas(num_cargo);
				}
			}

			function quitar_recoleccion(num_cargo){
				$(".crecoleccion_"+num_cargo).remove();
				activar_multiencargo();
			}

			function activar_multiencargo(){
				if($(".clase_contacto").length > 1){
					document.forma.multientrega_descuento.checked=true;
				}
				else{
					document.forma.multientrega_descuento.checked=false;
				}
				calcular_todas();
			}

			function validar_ncargo(){
				if(document.forma.cliente.value=="0"){
					alert("Necesita seleccionar el cliente");
					$("#panel").hide();
					return false;
				}
				else if(document.forma.pagar_en.value=="0"){
					alert("Necesita seleccionar pagar en");
					$("#panel").hide();
					return false;
				}

				return true;
			}

			document.forma.num_cargo.value='.$num_cargo.';

			function mostrar_datos_contacto(num_cargo){
				campo = $("#contacto_"+num_cargo);
				option = campo.find(\'option[value="\'+campo.val()+\'"]\');
				$("#telefono_contacto_"+num_cargo).val(option.attr("tel"));
				$("#email_contacto_"+num_cargo).val(option.attr("mail"));
			}

			function validar_cantidad_pagada(num_cargo){
				if( ($("#cantidad_pagada_"+num_cargo).val()/1) > ($("#total_"+num_cargo).val()/1) ){
					$("#cantidad_pagada_"+num_cargo).val($("#total_"+num_cargo).val());
				}
			}

			function traer_zonas(num_cargo){
				$.ajax({
				  url: "pedidos.php",
				  type: "POST",
				  async: false,
				  data: {
					tipo_ncargo: document.getElementById("tipo_ncargo_"+num_cargo).value,
					cliente: document.getElementById("cliente").value,
					delegacion: document.getElementById("delegacion_entrega_"+num_cargo).value,						
					ajax: 4
				  },
				  success: function(data) {
				  	$("#zona_"+num_cargo).html(data);
				  	mostrar_costo(num_cargo)
				  }
				});
			}

			function agregar_ncargo(){
				$.ajax({
				  url: "pedidos.php",
				  type: "POST",
				  async: false,
				  data: {
					cliente: document.getElementById("cliente").value,
					num_cargo: document.getElementById("num_cargo").value,
					ajax: 2
				  },
				  success: function(data) {
				  	$("#encargos").append(data);
				  	document.getElementById("num_cargo").value++;
				  	activar_multiencargo();
				  }
				});
			}

			function traer_datos(){
				$.ajax({
				  url: "pedidos.php",
				  type: "POST",
				  async: false,
				  dataType: "json",
				  data: {
					cliente: document.getElementById("cliente").value,
					cveusuario: document.getElementById("cveusuario").value,
					cvemenu: document.getElementById("cvemenu").value,
					ajax: 3
				  },
				  success: function(data) {
				  	$("#encargos").html(data.ultimo_ncargo);
				  	$("#tipo_cliente").html(data.tipo_cliente);
				  	document.getElementById("num_cargo").value=1;
				  	/*$(".clase_contacto").each(function(){
				  		$(this).html(data.contactos);
				  	});
					$(".clase_direccion").each(function(){
				  		$(this).html(data.direcciones);
				  	});
					$(".clase_tipo_ncargo").each(function(){
				  		this.value = "1";
				  	});
					$(".clase_zona").each(function(){
				  		$(this).html(\'<option value="0" costo="0">Seleccione</option>\');
				  	});*/
				  }
				});
			}

			function calcular_todas(){
				$(".clase_zona").each(function(){
			  		num_cargo=$(this).attr("numcargo");
			  		mostrar_costo(num_cargo)
			  	});
			}

			function mostrar_costo(num_cargo){
				porcentaje = trae_porcentaje(num_cargo);
				costo = $("#zona_"+num_cargo).find(\'option[value="\'+$("#zona_"+num_cargo).val()+\'"]\').attr("costo");
				if(costo == undefined) costo = 0;
				costo = costo  * porcentaje;
				if(document.forma.sin_iva.checked){
					iva=0;
				}
				else{
					iva = costo * 0.16;
					iva = iva.toFixed(2);
				}
				total = costo/1 + iva/1;
				total = total.toFixed(2);
				$("#subtotal_"+num_cargo).val(costo);
				$("#iva_"+num_cargo).val(iva);
				$("#total_"+num_cargo).val(total);
				var monto = 0;
				$(".totales").each(function(){
					monto+=this.value/1;
				});
				document.forma.monto.value=monto.toFixed(2);
			}

			function trae_porcentaje(num_cargo){
				if(document.forma.multientrega_descuento.checked){
					var mayor = -1;
					var num_cargo_mayor = -1;
					$(".clase_zona").each(function(){
				  		num_cargoz=$(this).attr("numcargo");
				  		costo = $("#zona_"+num_cargoz).find(\'option[value="\'+$("#zona_"+num_cargoz).val()+\'"]\').attr("costo")/1;
				  		if(costo>mayor){
				  			mayor = costo;
				  			num_cargo_mayor = num_cargoz;
				  		}
				  	});
					if(num_cargo_mayor == num_cargo) return 1;
					else return 0.5;
				}
				else{
					return 1;
				}
			}

			function validar_datos(cve)
			{
				if(document.forma.unidad.value == ""){
					alert("Necesita seleccionar la unidad");
					$("#panel").hide();
				}
				else if(document.forma.personal.value == ""){
					alert("Necesita seleccionar el repartidor");
					$("#panel").hide();
				}
				else{
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto.open("POST","pedidos.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=2&personal="+document.getElementById("personal").value+"&unidad="+document.getElementById("unidad").value);
						objeto.onreadystatechange = function()
						{
							if (objeto.readyState==4)
							{
								if(objeto.responseText!="no"){
									$("#panel").hide();
									alert(objeto.responseText);
								}
								else{
									atcr("pedidos.php","",2,cve);
								}
							}
						}
					}
				}
			}

			function agregar_contacto(campo){
				if($("#cliente").val() == "0"){
					alert("Necesita seleccionar el cliente");
				}
				else{
					$.ajax({
					  url: "pedidos.php",
					  type: "POST",
					  async: false,
					  data: {
						ajax: 5,
						campo: campo
					  },
					  success: function(data) {
					  	$("#altas").html(data);
					  	$("#altas").dialog("open");
					  }
					});
				}
			}

			function agregar_direccion(campo, tipo_dato, num_cargo){
				if($("#cliente").val() == "0"){
					alert("Necesita seleccionar el cliente");
				}
				else{
					$.ajax({
					  url: "pedidos.php",
					  type: "POST",
					  async: false,
					  data: {
						ajax: 6,
						campo: campo,
						num_cargo: num_cargo,
						tipo_dato: tipo_dato
					  },
					  success: function(data) {
					  	$("#altas").html(data);
					  	$("#altas").dialog("open");
					  }
					});
				}
			}

			function agregar_cliente(){
				$.ajax({
				  url: "pedidos.php",
				  type: "POST",
				  async: false,
				  data: {
					ajax: 8
				  },
				  success: function(data) {
				  	$("#altas").html(data);
				  	$("#altas").dialog("open");
				  }
				});
			}

			$("#altas").dialog({ 
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				height: 250,
				autoResize: true,
				position: "center",
				buttons: {
					"Aceptar": function(){ 
						guardar_alta();
					},
					"Cerrar": function(){ 
						$("#altas").val("");
						$(this).dialog("close"); 
					}
				},
			}); 

			function guardar_alta(){
				var error = 0;
				if($("#tipo_alta").val()=="1"){
					if($("#nombre_contacto").val()==""){
						alert("Necesita ingresar el nombre del contacto");
						error = 1;
					}
					else if($("#telefono_contacto").val()==""){
						alert("Necesita ingresar el telefono del contacto");
						error = 1;
					}
					else if($("#email_contacto").val()==""){
						alert("Necesita ingresar el email del contacto");
						error = 1;
					}
				}
				else if($("#tipo_alta").val()=="2"){
					if($("#calle_direccion").val()==""){
						alert("Necesita ingresar la calle");
						error = 1;
					}
				}
				else{
					if($("#tipo_cliented").val()=="0"){
						alert("Necesita seleccionar el tipo de cliente");
						error = 1;
					}
					else if($("#nombre").val()==""){
						alert("Necesita ingresar el nombre del cliente");
						error = 1;
					}
					if(error == 0){
						$.ajax({
						  url: "pedidos.php",
						  type: "POST",
						  async: false,
						  dataType: "json",
						  data: {
						  	ajax: 18,
						  	cveusuario: document.forma.cveusuario.value,
						  	campos: JSON.stringify($("#altas").serializeForm())
						  },
						  success: function(data) {
						  	$("#cliente").select2("destroy");
						  	$("#cliente").html(data.selects);
						  	$("#cliente").select2();
						  	traer_datos();
							$("#altas").val("");
							$("#altas").dialog("close"); 
						  }
						});
					}
					error = 1;
				}
				if(error == 0){
					$.ajax({
					  url: "pedidos.php",
					  type: "POST",
					  async: false,
					  dataType: "json",
					  data: {
					  	campo: $("#campo").val(),
					  	tipo_alta: $("#tipo_alta").val(),
						calle: $("#calle_direccion").val(),
						numext: $("#numext_direccion").val(),
						numint: $("#numint_direccion").val(),
					 	colonia: $("#colonia_direccion").val(),
						cp: $("#cp_direccion").val(),
						delegacion: $("#delegacion_direccion").val(),
						cliente: $("#cliente").val(),
						nombre: $("#nombre_contacto").val(),
						telefono: $("#telefono_contacto").val(),
						email: $("#email_contacto").val(),
						ajax: 7,
						tipo_dato: $("#tipo_dato_alta").val(),
						num_cargo: $("#num_cargo_alta").val()
					  },
					  success: function(data) {
					  	$("."+data.tipo).each(function(){
					  		valor = this.value;
					  		$(this).html(data.html);
					  		if(valor != "0"){
					  			this.value = valor;
					  		}
					  		else if($(this).attr("id") != data.campo){
					  			this.value = "0";
					  		}
					  		else if(data.tipo == "clase_direccion"){
					  			seleccionar_delegacion(data.tipo_dato, data.num_cargo);
					  		}
					  	});
						$("#altas").val("");
						$("#altas").dialog("close"); 
					  }
					});
				}
			}

			function agregarmedio(){
				$.ajax({
				  url: "pedidos.php",
				  type: "POST",
				  async: false,
				  data: {
					ajax: 15
				  },
				  success: function(data) {
				  	$("#altas1").html(data);
				  	$("#altas1").dialog("open");
				  }
				});
			}

			$("#altas1").dialog({ 
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				height: 250,
				autoResize: true,
				position: "center",
				buttons: {
					"Aceptar": function(){ 
						guardar_alta1();
					},
					"Cerrar": function(){ 
						$("#altas1").val("");
						$(this).dialog("close"); 
					}
				},
			}); 

			function guardar_alta1(){
				var error = 0;
				if($("#nombre_medio").val()==""){
					alert("Necesita ingresar el nombre del medio");
					error = 1;
				}
				if(error == 0){
					$.ajax({
					  url: "pedidos.php",
					  type: "POST",
					  async: false,
					  dataType: "json",
					  data: {
						nombre: $("#nombre_medio").val(),
						ajax: 17
					  },
					  success: function(data) {
					  	$("#medio_entero").html(data.html);
						$("#altas1").val("");
						$("#altas1").dialog("close"); 
					  }
					});
				}
			}
		</script>';		
}

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'pedidos.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'pedidos.php\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Excel</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td>'.campo_fecha('fecha_ini', 'fecha_ini', fechaLocal()).'</td></tr>';
	echo '<tr><td>Fecha Final</td><td>'.campo_fecha('fecha_fin', 'fecha_fin', fechaLocal()).'</td></tr>';
	echo '<tr><td>Cliente</td><td><select name="cliente" id="cliente"><option value="">Todos</option>';
	foreach($array_clientes as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
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
				objeto.open("POST","pedidos.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cliente="+document.getElementById("cliente").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cvemenu="+document.getElementById("cvemenu").value+"&cveusuario="+document.getElementById("cveusuario").value);
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
?>