<?php 

include ("main.php"); 
$rsDepto=mysql_query("SELECT * FROM areas");
while($Depto=mysql_fetch_array($rsDepto)){
	$array_localidad[$Depto['cve']]=$Depto['nombre'];
}
$array_tipo_placa=array(1=>"Particular",2=>"Intensivo");
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM tipo_placa WHERE 1 ";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		if($_POST['localidad']!=0) $select.=" AND localidad='".$_POST['localidad']."'";
		$select.=" ORDER BY nombre";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th><th>Nombre</th><th>Placa</th><th>Localidad</th><th>Descripciones</th><th>Tipo</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef['nombre'].'"></a></td>';
				echo '<td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($row['placa'])).'</td>';
				echo '<td>'.htmlentities(utf8_encode($array_localidad[$row['localidad']])).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($row['descripciones'])).'</td>';
				echo '<td>'.htmlentities(utf8_encode($array_tipo_placa[$row['tipo']])).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="8" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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


/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	$plazas='';
	
	foreach($_POST['mplazas'] as $k=>$v){
		$plazas .= '|'.$v.'|';
	}
	
	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE tipo_placa 
						SET nombre='".$_POST['nombre']."',placa='".$_POST['placa']."',localidad='".$_POST['localidad']."',descripciones='".$_POST['descripciones']."',tipo='".$_POST['tipo']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);			
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO tipo_placa 
						(nombre,placa,localidad,descripciones,tipo)
						VALUES 
						('".$_POST['nombre']."','".$_POST['placa']."','".$_POST['localidad']."','".$_POST['descripciones']."','".$_POST['tipo']."')";
			$ejecutar = mysql_query($insert);
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM tipo_placa WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'tipo_placa.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'tipo_placa.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Tipos de Placa</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"></td></tr>';
		echo '<tr><th align="left">Placa</th><td><input type="text" name="placa" id="placa" class="textField" size="10" value="'.$row['placa'].'"></td></tr>';
		echo '<tr><th align="left">Localidad</th><td><select name="localidad" id="localidad"><option value="0">Seleccione</option>';
		$res1=mysql_query("SELECT cve,nombre FROM areas ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['cve'].'"';
			if($row1['cve']==$row['localidad']) echo ' selected';
			echo '>'.$row1['nombre'].'</option>';
		}
		echo '</td></tr>';
		echo '<tr><th align="left">Descripciones</th><td><textarea name="descripciones" id="descripciones" class="textField" rows="5" cols="50">'.$row['descripciones'].'</textarea></td></tr>';
		echo '<tr><th align="left">Tipo</th><td><select name="tipo" id="tipo"><option value="0">Seleccione</option>';
		foreach($array_tipo_placa as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row['tipo']==$k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		
		
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'tipo_placa.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td></tr>';
		echo '<tr><td>Localidad</td><td><select name="localidad" id="localidad"><option value="0">Todas</option>';
		$res1=mysql_query("SELECT cve,nombre FROM areas ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['cve'].'"';
			echo '>'.$row1['nombre'].'</option>';
		}
		echo '</td></tr>';
		echo '<tr><td>Tipo</td><td><select name="tipo" id="tipo"><option value="0">Todos</option>';
		foreach($array_tipo_placa as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
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
			objeto.open("POST","tipo_placa.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&tipo="+document.getElementById("tipo").value+"&localidad="+document.getElementById("localidad").value+"&nom="+document.getElementById("nom").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

