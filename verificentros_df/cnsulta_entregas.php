<?php 
include ("main.php"); 


$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados  ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio'];
}

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

$array_depositantes = array();
$res = mysql_query("SELECT * FROM depositantes WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_depositantes[$row['cve']]=$row['nombre'];
}



$array_estatus = array('A'=>'Activo','C'=>'Cancelado');
/*** CONSULTA AJAX  **************************************************/


if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM certificados WHERE CAST(certificado as UNSIGNED)='".intval($_POST['folio'])."' "; 
		
		$select.=" ORDER BY fecha DESC, hora DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		$array_totales_engomados=array();
		$rechazados=0;
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Ticket</th><th>Placa</th><th>Tipo de Combustible</th>
			<th>Tipo de Certificado</th><th>Tecnico</th><th>Holograma</th><th>Entregado</th><th>Usuario</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				$res1=mysql_query("SELECT engomado,factura,tipo_combustible FROM cobro_engomado WHERE plaza = '".$row['plaza']."' AND cve='".$row['ticket']."'");
				$row1=mysql_fetch_array($res1);
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
					$row['certificado']='';
				}
				else{
					echo '&nbsp;';
				}	
				echo '</td>';
				if($row1['engomado']!=$row['engomado'])
					echo '<td align="center"><font color="RED">'.htmlentities($row['cve']).'</font></td>';
				else
					echo '<td align="center">'.htmlentities($row['cve']).'</td>';

				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="center">'.htmlentities($row['ticket']).'</td>';
				echo '<td align="center">'.htmlentities($row['placa']).'</td>';
				echo '<td align="center">'.htmlentities($array_tipo_combustible[$row1['tipo_combustible']]).'</td>';
				echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_personal[$row['tecnico']])).'</td>';
				echo '<td align="center">'.htmlentities($row['certificado']).'</td>';
				echo '<td align="center">'.htmlentities($array_nosi[$row['entregado']]).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
				$array_totales_engomados[$row['engomado']][$row1['tipo_combustible']]++;
				if($row['engomado']==9) $rechazados++;
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
		
		//echo '<br><font color="RED" style="font-size: 20px">A partir de este momento se tendran que capturar placa y ticket para la entrega de certificados.<br>Instrucciones del Licenciado Miguel Espina</font>';
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
	echo '<tr><td>Folio</td><td><input type="text" name="folio" id="folio" size="10" class="textField" value=""></td></tr>';
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
			objeto.open("POST","cnsulta_entregas.php",true);
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

if($cvecobro>0){
		echo '<script>atcr(\'cobro_engomado.php\',\'_blank\',\'101\','.$cvecobro.');</script>';
	}
?>

