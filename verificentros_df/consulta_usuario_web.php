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
	if($_POST['ajax']==1){
	$select="SELECT * FROM clientes WHERE usuario_web!='' and usuario_web='".$_POST['usuario']."'";
	$select.=" ORDER BY nombre";
	$res=mysql_query($select);
	$totalRegistros = mysql_num_rows($res);
		
	if(mysql_num_rows($res)>0) 
	{
			
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
		echo '<thead>';
		echo '<tr bgcolor="#E9F2F8"><th>Contrase&ntilde;a</th>';
		echo '<th>Nombre</th>
		<th>RFC</th>
		<th>Domicilio</th>
		<th>Colonia</th>
		<th>Municipio</th>
		<th>Estado</th>
		<th>Codigo Postal</th>';
		echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
		$i=0;
		while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="left">'.utf8_encode($row['contrasena_web']).'</td>';
				echo '<td align="left">'.utf8_encode($row['nombre']).'</td>';	
				echo '<td align="center">'.utf8_encode($row['rfc']).'</td>';
				echo '<td align="center">'.utf8_encode($row['calle'].' '.$row['numexterior'].' '.$row['numinterior'].' '.$row['colonia']).'</td>';
				echo '<td align="center">'.utf8_encode($row['colonia']).'</td>';
				echo '<td align="center">'.utf8_encode($row['municipio']).'</td>';
				echo '<td align="center">'.utf8_encode($row['estado']).'</td>';
				echo '<td align="center">'.utf8_encode($row['codigopostal']).'</td>';
				echo '</tr>';
		}
		
		echo '</tbody>
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
	echo '<tr><td>Usuario</td><td><input type="text" name="usuario" id="usuario" size="10" class="textField" value=""></td></tr>';
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
			objeto.open("POST","consulta_usuario_web.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&usuario="+document.getElementById("usuario").value);
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

	
	</Script>
	';

	
}
	
bottom();


?>

