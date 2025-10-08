<?php
include("main.php");
$array_engomado = array();
$res = mysql_query("SELECT * FROM engomados WHERE plazas like '%|".$_POST['plazausuario']."|%' AND entrega=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
}

if($_POST['ajax']==1){
	$array_engomado = array();
	$res = mysql_query("SELECT * FROM engomados WHERE cve IN (".$_POST['engomado'].") ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_engomado[$row['cve']]=$row['nombre'];
	}
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Tipo de Certificado</th><th>Entregas</th><th>Normal</th><th>Cortesia</th><th>Reposicion</th><th>Intentos</th><th>Intentos sin pago</th>';
	echo '</tr>';
	$res = mysql_query("SELECT a.cve, b.tipo_venta,b.cve, c.plaza, IFNULL(c.cve, 0) as pago, a.engomado
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.cve 
			LEFT JOIN cobro_engomado c ON c.estatus != 'C' AND c.placa = b.placa AND c.tipo_venta != 1 AND CONCAT(c.fecha,' ',c.hora) < CONCAT(b.fecha,' ',b.hora) AND CONCAT(c.fecha,' ',c.hora) > DATE_ADD(CONCAT(b.fecha,' ',b.hora), INTERVAL -60 DAY) 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.engomado IN (".$_POST['engomado'].") AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY a.cve");
	$array_cantidades = array();
	while($row=mysql_fetch_array($res)){
		$array_cantidades[$row['engomado']][0]++;
		if($row['tipo_venta'] == 1){
			$array_cantidades[$row['engomado']][4]++;
			if($row['pago'] == 0)
				$array_cantidades[$row['engomado']][5]++;
		}
		elseif($row['tipo_venta']==2){
			$array_cantidades[$row['engomado']][2]++;
		}
		elseif($row['tipo_venta']==3){
			$array_cantidades[$row['engomado']][3]++;
		}
		else{
			$array_cantidades[$row['engomado']][1]++;
		}
	}
	$array_totales = array();
	foreach($array_engomado as $k=>$v){
		rowb();
		echo '<td>'.$v.'</td>';
		for($i=0;$i<6;$i++){
			if($i==5)
				echo '<td align="center"><a href="#" onClick="atcr(\'reporte_certificados_entregados.php\',\'\',1,'.$k.')">'.number_format($array_cantidades[$k][$i],0).'</a></td>';
			else
				echo '<td align="center">'.number_format($array_cantidades[$k][$i],0).'</td>';
			$array_totales[$i]+=$array_cantidades[$k][$i];
		}
		echo '</tr>';
	}
	echo '<tr bgcolor="#E9F2F8"><th>Totales</th>';
	foreach($array_totales as $v) echo '<th>'.number_format($v,0).'</th>';
	echo '</tr>';	
	echo '</table>';
	exit();	
}

top($_SESSION);


if($_POST['cmd']==1){

	$res = mysql_query("SELECT * FROM usuarios");
	while($row=mysql_fetch_array($res)){
		$array_usuario[$row['cve']]=$row['usuario'];
	}

	$res = mysql_query("SELECT * FROM anios_certificados ORDER BY nombre DESC");
	while($row=mysql_fetch_array($res)){
		$array_anios[$row['cve']]=$row['nombre'];
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

	$array_motivos_intento = array();
	$res = mysql_query("SELECT * FROM motivos_intento WHERE localidad IN (0,2) ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_motivos_intento[$row['cve']]=$row['nombre'];
	}
	echo '<table>';
		echo '
			<tr>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'reporte_certificados_entregados.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';

		$select= " SELECT b.*,a.cve as certificado, a.certificado as holograma, a.fecha as fecha_entrega,a.engomado as engomado_entrega, IFNULL(c.cve, 0) as pago
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.cve 
			LEFT JOIN cobro_engomado c ON c.estatus != 'C' AND c.placa = b.placa AND c.tipo_venta != 1 AND CONCAT(c.fecha,' ',c.hora) < CONCAT(b.fecha,' ',b.hora) AND CONCAT(c.fecha,' ',c.hora) > DATE_ADD(CONCAT(b.fecha,' ',b.hora), INTERVAL -60 DAY) 
			WHERE a.plaza = '".$_POST['plazausuario']."' AND b.tipo_venta=1 AND a.estatus!='C' AND a.engomado = ".$_POST['reg']." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
			GROUP BY a.cve HAVING pago = 0";

		$res=mysql_query($select) or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Ticket</th><th>Fecha</th></th><th>Placa</th>
			<th>Tiene Multa</th><th>Tipo de Certificado</th><th>A&ntilde;o Certificacion</th><th>Tipo de Pago</th>
			<th>Tipo Combustible</th><th>Factura</th><th>Entrega Certificado</th><th>Fecha Entrega</th><th>Holograma Entregado</th><th>Usuario</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="center">'.htmlentities($row['placa']).'</td>';
				echo '<td align="center">'.htmlentities($array_nosi[$row['multa']]).'<br>'.$row['folio_multa'].'</td>';
				echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
				echo '<td align="center">'.number_format($row['monto'],2).'</td>';
				echo '<td align="center">'.htmlentities($array_anios[$row['anio']]).'</td>';
				echo '<td align="center">'.htmlentities($array_tipo_pago[$row['tipo_pago']]).'</td>';
				echo '<td align="center">'.htmlentities($array_tipo_combustible[$row['tipo_combustible']]).'</td>';
				echo '<td align="center">'.$row['certificado'].'</td>';
				echo '<td align="center">'.$row['fecha_entrega'].'</td>';
				echo '<td align="center">'.$row['holograma'].'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				
				echo '</tr>';
				$t+=$row['monto'];
			}
			echo '	
				<tr>
				<td colspan="13" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Fin</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td align="left">Tipo de Certificado</td><td><select multiple="multiple" name="engomados" id="engomados">';
	foreach($array_engomado as $k=>$v){
		echo '<option value="'.$k.'" selected>'.$v.'</option>';
	}
	echo '</select>';
	echo '<input type="hidden" name="engomado" id="engomado" value=""></td></tr>';
	echo '</table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">
	
	$("#engomados").multipleSelect({
		width: 500
	});	

	function buscarRegistros()
	{
		document.forma.engomado.value=$("#engomados").multipleSelect("getSelects");
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","reporte_certificados_entregados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&engomado="+document.getElementById("engomado").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					document.getElementById("Resultados").innerHTML = objeto.responseText;
					document.getElementById("depositos").innerHTML = document.getElementById("depositos2").innerHTML;
					document.getElementById("depositos2").innerHTML = "";
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