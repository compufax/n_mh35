<?php 
include ("main.php"); 



$res = mysql_query("SELECT * FROM anios_certificados ORDER BY cve DESC");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
}



$array_plazas = array();
$res = mysql_query("SELECT * FROM plazas WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
}



$array_estatus = array('A'=>'Activo','C'=>'Cancelado');
/*** CONSULTA AJAX  **************************************************/


if($_POST['ajax']==1) {
	$array_engomado = array();
	$array_engomadoprecio = array();
	$res = mysql_query("SELECT * FROM engomados  ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		if($row['venta']==1)
			$array_engomadoventa[$row['cve']]=$row['nombre'];
		if($row['entrega']==1)
			$array_engomadoentrega[$row['cve']]=$row['nombre'];
	}


	$res = mysql_query("SELECT * FROM tipo_combustible ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_tipo_combustible[$row['cve']]=$row['nombre'];
	}

	$array_tipo_pago = array();
	$res = mysql_query("SELECT * FROM tipos_pago WHERE 1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_tipo_pago[$row['cve']]=$row['nombre'];
	}
	$array_tipo_venta = array('Con Importe');
	$array_tipo_costoventa = array(0);
	$res = mysql_query("SELECT * FROM tipo_venta WHERE 1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_tipo_venta[$row['cve']]=$row['nombre'];
		$array_tipo_costoventa[$row['cve']]=$row['costo'];
	}

	$array_tipo_cortesia = array(1=>'Autorizada', 2=>'10x1');

	$array_motivos_intento = array();
	$res = mysql_query("SELECT * FROM motivos_intento WHERE localidad IN (0,2) ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_motivos_intento[$row['cve']]=$row['nombre'];
	}

	$array_depositantes = array();
	$res = mysql_query("SELECT * FROM depositantes WHERE 1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_depositantes[$row['plaza']][$row['cve']]=$row['nombre'];
	}

	$res = mysql_query("SELECT * FROM tecnicos WHERE 1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_personal[$row['plaza']][$row['cve']]=$row['nombre'];
	}

	$res = mysql_query("SELECT * FROM cat_lineas WHERE 1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_lineas[$row['plaza']][$row['cve']]=$row['nombre'];
	}
		
		
		//Listado de plazas
		$select= " SELECT CONCAT(c.numero,' ',c.nombre) as nomplaza,
		a.*,
		b.cve as certificado, 
		b.certificado as holograma,
		b.engomado as engomado_entrega, b.tecnico as tecnico_entrega,
		b.linea as linea_entrega
		FROM cobro_engomado a 
		INNER JOIN plazas c ON c.cve = a.plaza
		LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
		WHERE a.estatus!='C'";
		if($_POST['plaza']!=0) $select.= " AND a.plaza = '".$_POST['plaza']."'";
		if($_POST['ticket']!=''){
			 $select .= " AND a.cve='".$_POST['ticket']."'";
		}
		elseif($_POST['placa']!=''){
			$select .= " AND a.placa='".$_POST['placa']."'";
		}
		elseif($_POST['holograma']!=''){
			$select .= " AND b.certificado='".$_POST['holograma']."'";
		}
		else{
			if ($_POST['fecha_ini']>"0000-00-00") { $select.=" AND a.fecha>='".$_POST['fecha_ini']."' "; }
			if ($_POST['fecha_fin']>"0000-00-00") { $select.=" AND a.fecha<='".$_POST['fecha_fin']."' "; }
			if ($_POST['anio']!="all") { $select.=" AND a.anio='".$_POST['anio']."' "; }
		}
		$select.=" ORDER BY a.fecha DESC, a.hora DESC";

		$res=mysql_query($select) or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Plaza</th><th>Ticket</th><th>Fecha</th></th><th>Placa</th>
			<th>Tiene Multa</th><th>Tipo de Certificado</th>
			<th>A&ntilde;o Certificacion</th>
			<th>Tipo de Venta</th>
			<th>Motivo de Intento</th>
			<th>Tipo de Cortesia</th>
			<th>Monto</th>
			<th>Tipo de Combustible</th>
			<th>Tipo de Pago</th>
			<th>Depositante</th>
			<th>Entrega Certificado</th>
			<th>Tipo de Verificacion Entregada</th>
			<th>Holograma Entregado</th><th>Tecnico</th><th>Linea</th><th>&nbsp;</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td>'.$row['nomplaza'].'</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="center"><input type="text" id="placa_'.$t.'" value="'.$row['placa'].'" size="10"></td>';
				echo '<td align="center"><select id="multa_'.$t.'">';
				foreach($array_nosi as $k=>$v){
					echo '<option value="'.$k.'"';
					if($k==$row['multa']) echo ' selected';
					echo '>'.$v.'</option>';
				}
				echo '</select></td>';
				echo '<td align="center"><select id="engomado_'.$t.'">';
				foreach($array_engomadoventa as $k=>$v){
					echo '<option value="'.$k.'"';
					if($k==$row['engomado']) echo ' selected';
					echo '>'.$v.'</option>';
				}
				echo '</select></td>';
				echo '<td align="center"><select id="anio_'.$t.'">';
				foreach($array_anios as $k=>$v){
					echo '<option value="'.$k.'"';
					if($k==$row['anio']) echo ' selected';
					echo '>'.$v.'</option>';
				}
				echo '</select></td>';
				echo '<td align="center"><select id="tipo_venta_'.$t.'">';
				foreach($array_tipo_venta as $k=>$v){
					echo '<option value="'.$k.'"';
					if($k==$row['tipo_venta']) echo ' selected';
					echo '>'.$v.'</option>';
				}
				echo '</select></td>';
				echo '<td align="center"><select id="motivo_intento_'.$t.'"><option value="0">Seleccione</option>';
				foreach($array_motivos_intento as $k=>$v){
					echo '<option value="'.$k.'"';
					if($k==$row['motivo_intento']) echo ' selected';
					echo '>'.$v.'</option>';
				}
				echo '</select></td>';
				echo '<td align="center"><select id="tipo_cortesia_'.$t.'"><option value="0">Seleccione</option>';
				foreach($array_tipo_cortesia as $k=>$v){
					echo '<option value="'.$k.'"';
					if($k==$row['tipo_cortesia']) echo ' selected';
					echo '>'.$v.'</option>';
				}
				echo '</select></td>';
				echo '<td align="right">'.number_format($row['monto'],2).'</td>';
				echo '<td align="center"><select id="tipo_combustible_'.$t.'">';
				foreach($array_tipo_combustible as $k=>$v){
					echo '<option value="'.$k.'"';
					if($k==$row['tipo_combustible']) echo ' selected';
					echo '>'.$v.'</option>';
				}
				echo '</select></td>';
				echo '<td align="center"><select id="tipo_pago_'.$t.'">';
				foreach($array_tipo_pago as $k=>$v){
					echo '<option value="'.$k.'"';
					if($k==$row['tipo_pago']) echo ' selected';
					echo '>'.$v.'</option>';
				}
				echo '</select></td>';
				echo '<td align="center"><select id="depositante_'.$t.'"><option value="0">Seleccione</option>';
				foreach($array_depositantes[$row['plaza']] as $k=>$v){
					echo '<option value="'.$k.'"';
					if($k==$row['depositante']) echo ' selected';
					echo '>'.$v.'</option>';
				}
				echo '</select></td>';
				echo '<td align="center">'.$row['certificado'].'</td>';
				echo '<td align="center">'.htmlentities($array_engomadoentrega[$row['engomado_entrega']]).'</td>';
				echo '<td align="center"><input type="text" id="certificado_'.$t.'" value="'.$row['holograma'].'" size="20"></td>';
				echo '<td align="center"><select id="tecnico_'.$t.'"><option value="0">Seleccione</option>';
				foreach($array_personal[$row['plaza']] as $k=>$v){
					echo '<option value="'.$k.'"';
					if($k==$row['tecnico_entrega']) echo ' selected';
					echo '>'.$v.'</option>';
				}
				echo '</select></td>';
				echo '<td align="center"><select id="linea_'.$t.'"><option value="0">Seleccione</option>';
				foreach($array_lineas[$row['plaza']] as $k=>$v){
					echo '<option value="'.$k.'"';
					if($k==$row['linea_entrega']) echo ' selected';
					echo '>'.$v.'</option>';
				}
				echo '</select></td>';
				echo '<td align="center"><span style="cursor:pointer" onClick="guardar_datos(\''.$t.'\',\''.$row['plaza'].'\',\''.$row['cve'].'\',\''.$row['certificado'].'\')"><img src="images/guardar.gif"></span></td>';
				echo '</tr>';
				$t++;
			}
			echo '	
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
	$resultado = array('error' => 0, 'mensaje_error' => '');
	$array_engomado = array();
	$array_engomadoprecio = array();
	$res = mysql_query("SELECT * FROM engomados WHERE 1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_engomado[$row['cve']]=$row['nombre'];
		$array_engomadoprecio[$row['cve']]=$row['precio'];
	}
	$engomado_entrega = 0;
	$costo_engomado_entrega = 0;
	if($_POST['cveentrega']>0){
		$res = mysql_query("SELECT cve FROM certificados_cancelados WHERE plaza='".$_POST['plaza']."' AND anio='".$_POST['anio']."' AND CAST(certificado AS UNSIGNED)='".intval($_POST['certificado'])."' AND estatus!='C'");
		if(mysql_num_rows($res)==0){
			$res = mysql_query("SELECT a.engomado, b.estatus, b.tipo, a.anio FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra WHERE a.plaza='".$_POST['plaza']."' AND a.estatus!='C' AND b.folio='".intval($_POST['certificado'])."' ORDER BY b.cve DESC LIMIT 1");
			if($row = mysql_fetch_array($res)){
				if($row['engomado'] == 3 || $row['engomado'] == 19 || $row['anio'] == $_POST['anio']){
					$engomado_entrega = $row['engomado'];
					$costo_engomado_entrega = $array_engomadoprecio[$row['engomado']];
				}
				else{
					$resultado['error'] = 1;
					$resultado['mensaje_error'] = 'El holograma no existe';
				}
			}
			else{
				$resultado['error'] = 1;
				$resultado['mensaje_error'] = 'El holograma no existe';
			}
		}
		else{
			$resultado['error'] = 1;
			$resultado['mensaje_error'] = 'El holograma esta cancelado';
		}
	}

	if($resultado['error'] == 0){
		if($_POST['tipo_venta'] == 1 || $_POST['tipo_venta'] == 2){
			$monto = 0;
		}
		else{
			$monto = 'monto_verificacion';
		}
		$res = mysql_query("SELECT tipo_venta FROM cobro_engomado WHERE plaza = '".$_POST['plaza']."' AND cve = '".$_POST['ticket']."'");
		$row = mysql_fetch_array($res);
		if($_POST['tipo_venta'] == 3 && $row['tipo_venta'] != 3){
			$res = mysql_query("SELECT costo FROM tipo_venta WHERE cve=3 ORDER BY nombre");
			$row=mysql_fetch_array($res);
			$monto = $row['costo'];
		}
		mysql_query("UPDATE cobro_engomado SET 
			placa = '".$_POST['placa']."', multa = '".$_POST['multa']."', engomado = '".$_POST['engomado']."',
			anio = '".$_POST['anio']."', tipo_venta = '".$_POST['tipo_venta']."',
			motivo_intento = '".$_POST['motivo_intento']."', tipo_cortesia = '".$_POST['tipo_cortesia']."',
			tipo_combustible = '".$_POST['tipo_combustible']."', tipo_pago = '".$_POST['tipo_pago']."',
			depositante = '".$_POST['depositante']."', monto = ".$monto."
			WHERE plaza = '".$_POST['plaza']."' AND cve = '".$_POST['ticket']."'") or die(mysql_error());
		mysql_query("UPDATE certificados SET 
			placa = '".$_POST['placa']."', anio = '".$_POST['anio']."'
			WHERE plaza = '".$_POST['plaza']."' AND ticket = '".$_POST['ticket']."'") or die(mysql_error());
		if($_POST['cveentrega']>0){
			mysql_query("UPDATE certificados SET 
				certificado = '".$_POST['certificado']."', engomado = '$engomado_entrega', 
				monto = '$costo_engomado_entrega', tecnico = '".$_POST['tecnico']."',
				linea = '".$_POST['linea']."'
				WHERE plaza = '".$_POST['plaza']."' AND cve = '".$_POST['cveentrega']."'") or die(mysql_error());
		}
	}

	echo json_encode($resultado);

	exit();
}


top($_SESSION);

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	echo '
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza"><option value="0">Todas</option>';
	foreach($array_plazas as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Placa</td><td><input type="text" name="placa" id="placa" size="10" class="textField" value=""></td></tr>';
	echo '<tr><td>A&ntilde;o</td><td><select name="anio" id="anio">';
	foreach($array_anios as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Ticket</td><td><input type="text" name="ticket" id="ticket" size="10" class="textField" value=""></td></tr>';
	echo '<tr><td>Certificado</td><td><input type="text" name="holograma" id="holograma" size="30" class="textField" value=""></td></tr>';
	echo '</table>';
	echo '<br>';
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
			objeto.open("POST","edicion_datos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&ticket="+document.getElementById("ticket").value+"&holograma="+document.getElementById("holograma").value+"&plaza="+document.getElementById("plaza").value+"&anio="+document.getElementById("anio").value+"&placa="+document.getElementById("placa").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

	function guardar_datos(linea, plaza, ticket, cveentrega){
		$.ajax({
			url: "edicion_datos.php",
		 	type: "POST",
		  	async: false,
		  	dataType: "json",
		  	data: {
		  		ticket: ticket,
		  		plaza: plaza, 
		  		placa: document.getElementById("placa_"+linea).value,
				multa: document.getElementById("multa_"+linea).value,
				engomado: document.getElementById("engomado_"+linea).value,
				anio: document.getElementById("anio_"+linea).value,
				tipo_venta: document.getElementById("tipo_venta_"+linea).value,
				motivo_intento: document.getElementById("motivo_intento_"+linea).value,
				tipo_cortesia: document.getElementById("tipo_cortesia_"+linea).value,
				tipo_combustible: document.getElementById("tipo_combustible_"+linea).value,
				tipo_pago: document.getElementById("tipo_pago_"+linea).value,
				depositante: document.getElementById("depositante_"+linea).value,
				cveentrega: cveentrega,
				certificado: document.getElementById("certificado_"+linea).value,
				tecnico: document.getElementById("tecnico_"+linea).value,
				linea: document.getElementById("linea_"+linea).value,
				ajax: 2,
		  	},
			success: function(data) {
				if(data.error == 1)
				{
					alert(data.mensaje_error);
				}
				else{
					buscarRegistros();
				}
			}
		});
	}
		
	
	</Script>
	';

	
}
	
bottom();


?>

