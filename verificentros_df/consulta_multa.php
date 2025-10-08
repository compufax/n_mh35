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
		$select= " SELECT CONCAT(c.numero,' ',c.nombre) as nomplaza,a.*,b.cve as certificado, b.certificado as holograma,b.engomado as engomado_entrega FROM cobro_engomado a 
		INNER JOIN plazas c ON c.cve = a.plaza
		LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
		WHERE a.multa = 1 AND a.folio_multa='".$_POST['folio']."' AND a.folio_multa != ''";
		
		$select.=" ORDER BY a.fecha DESC, a.hora DESC";

		$res=mysql_query($select) or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Plaza</th><th>Ticket</th><th>Fecha</th></th><th>Placa</th>
			<th>Multa</th><th>No. Placas</th><th>Tipo de Certificado</th><th>Monto</th><th>A&ntilde;o Certificacion</th><th>Tipo de Pago</th><th>Tipo Combustible</th><th>Factura</th><th>Entrega Certificado</th><th>Holograma Entregado</th><th>Usuario</th>';
			if($_POST['cveusuario']==1) echo '<th>Clave Facturacion</th>';
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
				if($row['estatus']=='C'){
					echo '<td align="center">'.htmlentities($row['placa']).'</td>';
				}
				elseif($row['certificado']>0 && $row['engomado']!=$row['engomado_entrega']){
					echo '<td align="center"><font color="RED">'.htmlentities($row['placa']).'</font></td>';
				}
				else{
					$res1 = mysql_query("SELECT cve FROM certificados WHERE placa='".$row['placa']."' AND fecha>='".$row['fecha']."' AND DATE_ADD(fecha,INTERVAL 30 DAY)>='".$row['fecha']."'");
					if(mysql_num_rows($res1)==0)
						echo '<td align="center"><font color="GREEN">'.htmlentities($row['placa']).'</font></td>';
					else
						echo '<td align="center">'.htmlentities($row['placa']).'</td>';
				}
				echo '<td align="center">'.$row['folio_multa'].'</td>';
				echo '<td align="center">'.$row['placas_multa'].'</td>';
				echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
				echo '<td align="center">'.number_format($row['monto'],2).'</td>';
				echo '<td align="center">'.htmlentities($array_anios[$row['anio']]).'</td>';
				echo '<td align="center">'.htmlentities($array_tipo_pago[$row['tipo_pago']]).'</td>';
				echo '<td align="center">'.htmlentities($array_tipo_combustible[$row['tipo_combustible']]).'</td>';
				echo '<td align="center">'; echo ($row['factura']==0)?'&nbsp;':$row['factura']; echo '</td>';
				echo '<td align="center">'.$row['certificado'].'</td>';
				echo '<td align="center">'.$row['holograma'].'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				if($_POST['cveusuario']==1){
					$res2=mysql_query("SELECT * FROM claves_facturacion WHERE plaza='".$row['plaza']."' AND ticket='".$row['cve']."'");
					$row2=mysql_fetch_array($res2);
					echo '<td>'.$row2['cve'].'</td>';
				}
				echo '</tr>';
				$t+=$row['monto'];
			}
			echo '	
				<tr>
				<td colspan="8" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</td>
				<td colspan="8" bgcolor="#E9F2F8">&nbsp;</td>
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
	echo '<tr><td>Folio Multa</td><td><input type="text" name="folio" id="folio" size="10" class="textField" value=""></td></tr>';
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
			objeto.open("POST","consulta_multa.php",true);
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

