<?php 
include ("main.php"); 


$array_engomado = array();
$array_engomadoprecio = array();
$array_engomado = array();
$res = mysql_query("SELECT * FROM engomados WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio'];
}

$res = mysql_query("SELECT * FROM anios_certificados WHERE cve > 1 ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
}

$res = mysql_query("SELECT * FROM motivos_cancelacion_certificados");
while($row=mysql_fetch_array($res)){
	$array_motivos[$row['cve']]=$row['nombre'];
}

$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$res = mysql_query("SELECT * FROM tipo_combustible ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_combustible[$row['cve']]=$row['nombre'];
}

$res = mysql_query("SELECT * FROM tecnicos");
while($row=mysql_fetch_array($res)){
	$array_personal[$row['plaza']][$row['cve']]=$row['nombre'];
}


$array_estatus = array('A'=>'Activo','C'=>'Cancelado');
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		
		
		
		//Listado de plazas
		$select= " SELECT CONCAT(c.numero,' ',c.nombre) as nomplaza,a.*
		FROM certificados a 
		INNER JOIN plazas c ON c.cve = a.plaza
		WHERE CAST(a.certificado as UNSIGNED)='".intval($_POST['folio'])."'";
		
		$select.=" ORDER BY a.fecha DESC, a.hora DESC";

		$res=mysql_query($select) or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<h2>Entregas</h2>';
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Plaza</th><th>Folio</th><th>Fecha</th><th>Ticket</th><th>Placa</th><th>Tipo de Combustible</th>
			<th>Tipo de Certificado</th><th>Tecnico</th><th>Holograma</th><th>Entregado</th><th>Usuario</th><th>Folio de </br>Compra</th><th>Fecha de</br>Compra</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				$res1=mysql_query("SELECT engomado,factura,tipo_combustible FROM cobro_engomado WHERE plaza = '".$row['plaza']."' AND cve='".$row['ticket']."'");
				$row1=mysql_fetch_array($res1);
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
					$row['monto']=0;
				}
				else{
					echo '&nbsp;';
				}	
				echo '</td>';
				echo '<td>'.$row['nomplaza'].'</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="center">'.htmlentities($row['ticket']).'</td>';
				echo '<td align="center">'.htmlentities($row['placa']).'</td>';
				echo '<td align="center">'.htmlentities($array_tipo_combustible[$row1['tipo_combustible']]).'</td>';
				echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_personal[$row['plaza']][$row['tecnico']])).'</td>';
				echo '<td align="center">'.htmlentities($row['certificado']).'</td>';
				echo '<td align="center">'.htmlentities($array_nosi[$row['entregado']]).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				$sele= " SELECT folio,fecha_compra FROM compra_certificados WHERE folioini<='".$row['certificado']."' and foliofin>='".$row['certificado']."'";
				$re=mysql_query($sele) or die(mysql_error());
				$roww=mysql_fetch_array($re);
				echo '<td align="center">'.htmlentities($roww['folio']).'</td>';
				echo '<td align="center">'.htmlentities($roww['fecha_compra']).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="14" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				</tr>
			</table>';
			
		} else {
			/*echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';*/
/*			$select= " SELECT CONCAT(c.numero,' ',c.nombre) as nomplaza,
						FROM certificados a 
						INNER JOIN plazas c ON c.cve = a.plaza
						WHERE a.certificado='".intval($_POST['folio'])."'";
		
		$select.=" ORDER BY a.fecha DESC, a.hora DESC";

		$res=mysql_query($select) or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);*/
		
			echo '<h2> Sin Entregas</h2>';
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Plaza</th><th>Folio</th><th>Fecha</th><th>Ticket</th><th>Placa</th><th>Tipo de Combustible</th>
			<th>Tipo de Certificado</th><th>Tecnico</th><th>Holograma</th><th>Entregado</th><th>Usuario</th><th>Folio de </br>Compra</th><th>Fecha de</br>Compra</th>';
			echo '</tr>';
			$t=0;
			//while($row=mysql_fetch_array($res)) {
			//	$res1=mysql_query("SELECT engomado,factura,tipo_combustible FROM cobro_engomado WHERE plaza = '".$row['plaza']."' AND cve='".$row['ticket']."'");
			//	$row1=mysql_fetch_array($res1);
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
					$row['monto']=0;
				}
				else{
					echo '&nbsp;';
				}	
				echo '</td>';
				echo '<td>'.$row['nomplaza'].'</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="center">'.htmlentities($row['ticket']).'</td>';
				echo '<td align="center">'.htmlentities($row['placa']).'</td>';
				echo '<td align="center">'.htmlentities($array_tipo_combustible[$row1['tipo_combustible']]).'</td>';
				echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_personal[$row['plaza']][$row['tecnico']])).'</td>';
				echo '<td align="center">'.htmlentities($row['certificado']).'</td>';
				echo '<td align="center">'.htmlentities($array_nosi[$row['entregado']]).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				$sele= " SELECT folio,fecha_compra FROM compra_certificados WHERE folioini<='".$_POST['folio']."' and foliofin>='".$_POST['folio']."'";
				$re=mysql_query($sele) or die(mysql_error());
				$roww=mysql_fetch_array($re);
				echo '<td align="center">'.htmlentities($roww['folio']).'</td>';
				echo '<td align="center">'.htmlentities($roww['fecha_compra']).'</td>';
				echo '</tr>';
			//}
			echo '	
				<tr>
				<td colspan="14" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				</tr>
			</table>';
		}

		$select= " SELECT CONCAT(c.numero,' ',c.nombre) as nomplaza,a.*
		FROM certificados_cancelados a 
		INNER JOIN plazas c ON c.cve = a.plaza
		WHERE CAST(a.certificado as UNSIGNED)='".intval($_POST['folio'])."'";
		
		$select.=" ORDER BY a.fecha DESC, a.hora DESC";

		$res=mysql_query($select) or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<h2>Cancelaciones</h2>';
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Plaza</th><th>Folio</th><th>Fecha</th><th>Motivo</th><th>Tipo de Certificado</th><th>A&ntilde;o</th><th>Holograma</th><th>Usuario</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
					$row['monto']=0;
				}
				else{
					echo '&nbsp;';
				}	
				echo '</td>';
				echo '<td>'.$row['nomplaza'].'</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_motivos[$row['motivo']])).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_engomado[$row['engomado']])).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_anios[$row['anio']])).'</td>';
				echo '<td align="center">'.htmlentities($row['certificado']).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="12" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
	echo '<tr><td>Folio Certificado</td><td><input type="text" name="folio" id="folio" size="10" class="textField" value=""></td></tr>';
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
			objeto.open("POST","consulta_certificado.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&folio="+document.getElementById("folio").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

