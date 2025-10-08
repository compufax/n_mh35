<?php
include("main.php");
$array_plaza = array();
$res = mysql_query("SELECT * FROM plazas WHERE estatus='A' AND vende_seguros = 1 ORDER BY numero, nombre");
while($row = mysql_fetch_array($res)){
	$array_plaza[$row['cve']] = $row['numero'].' '.$row['nombre'];
}
$array_usuario = array();
$res = mysql_query("SELECT * FROM usuarios ORDER BY usuario");
while($row = mysql_fetch_array($res)){
	$array_usuario[$row['cve']] = $row['usuario'];
}

if($_POST['cmd'] == 10){
	$cadena="";
	foreach($_POST['seguros'] as $seguro){
		$res = mysql_query("SELECT * FROM microseguros WHERE cve='".$seguro."'");
		$row = mysql_fetch_array($res);
		$cadena .= $row['ramo'].'|||'.$row['movimiento'].'||'.fechaseguro($row['fecha_vigencia']).'|'.$row['formapago'].'|||';
		$cadena .= $row['nombre'].'|'.$row['apaterno'].'|'.$row['amaterno'].'|'.$row['sexo'].'|'.fechaseguro($row['fecha_nacimiento']).'||';
		$cadena .= $row['calle'].'|'.$row['numero'].'|'.$row['colonia'].'|'.$row['codigopostal'].'|||'.$row['telefono'].'||||||||||';
		$cadena .= "\n";
	}
	header( "Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=microseguros.txt");
    print $cadena;
	exit();
}

if($_POST['ajax']==1){
	$select = "SELECT * FROM microseguros WHERE fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	if($_POST['nombre'] != '') $select .= " AND CONCAT(nombre,' ',apaterno,' ',amaterno) LIKE '%".$_POST['nombre']."%'";
	if($_POST['plaza'] != 'all') $select .= " AND plaza='".$_POST['plaza']."'";
	$select .= " ORDER BY cve DESC";

	$res=mysql_query($select);
	$totalRegistros = mysql_num_rows($res);
	
	
	if(mysql_num_rows($res)>0) 
	{
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>
		<input type="checkbox" onClick="if(this.checked) $(\'.chks\').attr(\'checked\',\'checked\'); else $(\'.chks\').removeAttr(\'checked\');"></th>
		<th>Folio</th><th>Plaza</th><th>Fecha</th><th>Fecha Vigencia</th><th>Nombre</th><th>Apellido Paterno</th><th>Apellido Materno</th><th>Sexo</th>
		<th>Fecha de Nacimiento</th><th>Calle</th><th>N&uacute;mero</th><th>Colonia</th><th>C&oacute;digo Postal</th><th>T&eacute;lefono</th><th>Usuario</th>';
		echo '</tr>';
		$t=0;
		while($row=mysql_fetch_array($res)) {
			rowb();
			echo '<td align="center" width="40" nowrap>';
			echo '<input type="checkbox" name="seguros[]" value="'.$row['cve'].'" class="chks">';
			echo '</td>';
			echo '<td align="center">'.htmlentities($row['cve']).'</td>';
			echo '<td align="left">'.$array_plaza[$row['plaza']].'</td>';
			echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
			echo '<td align="center">'.htmlentities($row['fecha_vigencia']).'</td>';
			echo '<td align="left">'.htmlentities($row['nombre']).'</td>';
			echo '<td align="left">'.htmlentities($row['apaterno']).'</td>';
			echo '<td align="left">'.htmlentities($row['amaterno']).'</td>';
			echo '<td align="center">'.htmlentities($row['sexo']).'</td>';
			echo '<td align="center">'.$row['fecha_nacimiento'].'</td>';
			echo '<td align="left">'.htmlentities($row['calle']).'</td>';
			echo '<td align="left">'.htmlentities($row['numero']).'</td>';
			echo '<td align="left">'.htmlentities($row['colonia']).'</td>';
			echo '<td align="left">'.htmlentities($row['codigopostal']).'</td>';
			echo '<td align="left">'.htmlentities($row['telefono']).'</td>';
			echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
			echo '</tr>';
		}
		echo '	
			<tr>
			<td colspan="16" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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

if ($_POST['cmd']<1) {
	$nivelUsuario=nivelUsuario();
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="atcr(\'migroseguros.php\',\'_blank\',10,0)"><img src="images/finalizar.gif" border="0"></a>Generar Txt</td>';
	echo '
		 </tr>';
	echo '</table>';
	echo '<table width="100%"><tr><td valign="top" width="50%">';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" size="30" class="textField" value=""></td></tr>';
	echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza"><option value="all">Todas</option>';
	foreach($array_plaza as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
	echo '</select></td></tr>';
	echo '</table>';
	echo '</td><td width="50%" valign="top" id="capacorte"></td></tr></table>';
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
			objeto.open("POST","microseguros.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nombre="+document.getElementById("nombre").value+"&plaza="+document.getElementById("plaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

	</script>';
}
bottom();

?>