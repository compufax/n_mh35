<?php 

include ("main.php"); 

$rsDepto=mysql_query("SELECT * FROM areas");
while($Depto=mysql_fetch_array($rsDepto)){
	$array_localidad[$Depto['cve']]=$Depto['nombre'];
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	$campos="";
	foreach($_POST['camposi'] as $k=>$v){
		$campos.=",".$k."='".$v."'";
	}	
	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE sucursales 
						SET nombre='".$_POST['nombre']."',localidad='".$_POST['localidad']."',direccion='".$_POST['direccion']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);	
			
					
			$id=$_POST['reg'];
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO sucursales 
						(nombre,localidad,direccion)
						VALUES 
						('".$_POST['nombre']."','".$_POST['localidad']."','".$_POST['direccion']."')";
			$ejecutar = mysql_query($insert);
			$id = mysql_insert_id();
	}
	
	
	$_POST['cmd']=0;
	
}


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM sucursales WHERE 1 ";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		$select .= " ORDER BY nombre";
		$rsplaza=mysql_query($select);
		$totalRegistros = mysql_num_rows($rsplaza);
		
		
		if(mysql_num_rows($rsplaza)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="4">'.mysql_num_rows($rsplaza).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th><th>Nombre</th><th>Direccion</th><th>Localidad</th></tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($Plaza=mysql_fetch_array($rsplaza)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$Plaza['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Plaza['nombre'].'"></a></td>';
				echo '<td>'.htmlentities(utf8_encode($Plaza['nombre'])).'</td>';
				echo '<td>'.htmlentities(utf8_encode($Plaza['direccion'])).'</td>';
				echo '<td>'.htmlentities(utf8_encode($array_localidad[$Plaza['localidad']])).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="4" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM sucursales WHERE cve='".$_POST['reg']."' ";
		$rsplaza=mysql_query($select);
		$Plaza=mysql_fetch_array($rsplaza);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="atcr(\'sucursales.php\',\'\',\'2\',\''.$Plaza['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="atcr(\'sucursales.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Sucursales</td></tr>';
		echo '</table>';
		echo '<table width="100%"><tr><td>';
		echo '<table>';
		echo '<tr><th>Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$Plaza['nombre'].'"></td></tr>';
		echo '<tr><th>Direccion</th><td><input type="text" name="direccion" id="direccion" class="textField" size="100" value="'.$Plaza['direccion'].'"></td></tr>';
		echo '<tr><th>Localidad</th><td><select name="localidad" id="localidad"><option value="0">Seleccione</option>';
		foreach($array_localidad as $k=>$v){
			echo '<option value="'.$k.'"';
			if($Plaza['localidad']==$k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		echo '</td></tr></table>';
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td><td>&nbsp;</td><td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'sucursales.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
				</tr>';
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

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","sucursales.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value);
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
	}';	
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
	
	</Script>
';

?>

