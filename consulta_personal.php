<?php 

include ("main.php"); 

/*** ARREGLOS ***********************************************************/

$rsPlaza=mysql_query("SELECT * FROM plazas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['numero'];
}

$rsPuestos=mysql_query("SELECT * FROM puestos ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_puestos[$Puestos['cve']]=$Puestos['nombre'];
}

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de personal
		$select= " SELECT a.* FROM personal a INNER JOIN datosempresas b on a.plaza = b.plaza WHERE 1 ";
		if ($_POST['credencial']!="") { $select.=" AND a.cve='".$_POST['credencial']."'"; }
		if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
		if ($_POST['rfc']!="") { $select.=" AND a.rfc='".$_POST['rfc']."'"; }		
		$select.=" ORDER BY trim(a.nombre)";
		$rspersonal=mysql_query($select);
		$totalRegistros = mysql_num_rows($rspersonal);
		
		if(mysql_num_rows($rspersonal)>0) 
		{
			
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<thead>';
			echo '<tr bgcolor="#E9F2F8">';
			echo '<th>Plaza</th>';
			echo '<th>No.</th>
			<th>Nombre</th><th>Fecha de Estatus</th><th>Estatus</th><th>RFC</th><th>Puesto</th>';
			echo '</tr></thead><tbody>';
			$i=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
				rowb();
				echo '<td>'.utf8_encode($array_plaza[$Personal['plaza']]).'</td>';
				echo '<td align="center">'.$Personal['cve'].'</td>';
				if(file_exists("imgpersonal/foto".$Personal['cve'].".jpg"))
					echo '<td align="left" onMouseOver="document.getElementById(\'foto'.$Personal['cve'].'\').style.visibility=\'visible\';" onMouseOut="document.getElementById(\'foto'.$Personal['cve'].'\').style.visibility=\'hidden\';">'.$Personal['nombre'].'<img width="200" id="foto'.$Personal['cve'].'" height="250" style="position:absolute;visibility:hidden" src="imgpersonal/foto'.$Personal['cve'].'.jpg?'.date('h:i:s').'" border="1"></td>';
				else
					echo '<td align="left">'.htmlentities(utf8_encode(trim($Personal['nombre']))).'</td>';
				echo '<td align="center">'.$Personal['fecha_sta'].'</td>';
				echo '<td align="center">'.$array_estatus_personal[$Personal['estatus']].'</td>';
				echo '<td align="center">'.$Personal['rfc'].'</td>';
				echo '<td align="center">'.$array_puestos[$Personal['puesto']].'</td>';
				echo '</tr>';
			}
			
			echo '</tbody>
				<tr>
				<td colspan="7" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
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
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Credencia</td><td><input type="text" name="credencial" id="credencial" class="textField"></td></tr>'; 
		echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" class="textField"></td></tr>'; 
		echo '<tr><td>RFC</td><td><input type="text" name="rfc" id="rfc" class="textField"></td></tr>'; 
		echo '</table>';
		echo '<br>';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
	
bottom();



/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros(ordenamiento,orden)
	{
		if($.trim(document.forma.nombre.value) == "" && $.trim(document.forma.rfc.value) == "" && $.trim(document.forma.credencial.value) == ""){
			alert("Necesita capturar al menos un campo de busqueda");
		}
		else{
			document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
			objeto=crearObjeto();
			if (objeto.readyState != 0) {
				alert("Error: El Navegador no soporta AJAX");
			} else {
				objeto.open("POST","consulta_personal.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=1&credencial="+document.getElementById("credencial").value+"&nombre="+document.getElementById("nombre").value+"&rfc="+document.getElementById("rfc").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
				objeto.onreadystatechange = function()
				{
					if (objeto.readyState==4)
					{document.getElementById("Resultados").innerHTML = objeto.responseText;}
				}
			}
			document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
		}
	}
	
	
	
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}
	
	</Script>
';

?>

