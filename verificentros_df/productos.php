<?php 

include ("main.php"); 

$array_clasificacion = array();
$res = mysql_query("SELECT * FROM clasificacion_productos ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_clasificacion[$row['cve']]=$row['nombre'];
}
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM productos WHERE 1 ";
		if($_POST['clasificacion']!="all") $select .= " AND clasificacion='".$_POST['clasificacion']."'";
		if($_POST['maneja_series']!="all") $select .= " AND maneja_series='".$_POST['maneja_series']."'";
		if ($_POST['codigo']!="") { $select.=" AND codigo='".$_POST['codigo']."' "; }
		if ($_POST['nom']!="") { $select.=" AND descripcion LIKE '%".$_POST['nom']."%' "; }
		$select.=" ORDER BY codigo";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th>';
			echo '<th>Descripcion</th><th>Clasificacion</th><th>Maneja Series</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef['nombre'].'"></a></td>';
				//echo '<td>'.htmlentities($row['codigo']).'</td>';
				echo '<td>'.htmlentities(utf8_encode($row['descripcion'])).'</td>';
				echo '<td>'.htmlentities(utf8_encode($array_clasificacion[$row['clasificacion']])).'</td>';
				echo '<td align="center">'.htmlentities($array_nosi[$row['maneja_series']]).'</td>';
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

if($_POST['ajax']==2){
	echo "no";
	exit();

	$res = mysql_query("SELECT cve FROM productos WHERE codigo='".$_POST['codigo']."'");
	if(mysql_num_rows($res)==0){
		echo "no";
	}
	else{
		echo "si";
	}
	exit();
}

top($_SESSION);


/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE productos 
						SET descripcion='".$_POST['descripcion']."',clasificacion='".$_POST['clasificacion']."',
						maneja_series='".$_POST['maneja_series']."' 
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);			
	} else {
		//$res = mysql_query("SELECT cve FROM productos WHERE codigo='".$_POST['codigo']."'");
		//if(mysql_num_rows($res)==0){
			//Insertar el Registro
			$insert = " INSERT INTO productos 
						(codigo,descripcion,clasificacion,maneja_series)
						VALUES 
						('".$_POST['codigo']."','".$_POST['descripcion']."','".$_POST['clasificacion']."','".$_POST['maneja_series']."')";
			$ejecutar = mysql_query($insert);
		//}
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM productos WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		if($row['fecha_ini']=='0000-00-00') $row['fecha_ini']='';
		if($row['fecha_fin']=='0000-00-00') $row['fecha_fin']='';
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();validar_codigo(\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'productos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Productos</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr style="display:none;"><th align="left">Codigo</th><td><input type="text" name="codigo" id="codigo" size="20" value="'.$row['codigo'].'"';
		if($_POST['reg']==0){
			echo ' class="textField"';
		}
		else{
			echo ' class="readOnly" readOnly';
		}
		echo '></td></tr>';
		echo '<tr><th align="left">Descripcion</th><td><input type="text" name="descripcion" id="descripcion" class="textField" size="100" value="'.$row['descripcion'].'"></td></tr>';
		echo '<tr><th align="left">Clasificacion</th><td><select name="clasificacion" id="clasificacion"><option value="0">Seleccione</option>';
		foreach($array_clasificacion as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['clasificacion']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Maneja Series</th><td><input type="checkbox" name="maneja_series" id="maneja_series" class="textField" value="1"';
		if($row['maneja_series']==1) echo ' checked';
		echo '></td></tr>';
		
		echo '</table>';
		
		echo '<script>
				function validar_codigo(cve)
				{
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto.open("POST","productos.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=2&codigo="+document.getElementById("codigo").value+"&plazausuario="+document.getElementById("plazausuario").value);
						objeto.onreadystatechange = function()
						{
							if (objeto.readyState==4)
							{
								if(objeto.responseText=="si"){
									$("#panel").hide();
									alert("El codigo ya esta dado de alta");
								}
								else{
									atcr("productos.php","",2,cve);
								}
							}
						}
					}
					document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
				}
			</script>';		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'productos.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr style="display:none;"><td>Codigo</td><td><input type="text" name="codigo" id="codigo" size="20" class="textField" value=""></td></tr>';
		echo '<tr><td>Descripcion</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td></tr>';
		echo '<tr><td>Clasificacion</td><td><select name="clasificacion" id="clasificacion"><option value="all">Todos</option>';
		foreach($array_clasificacion as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Maneja Serie</td><td><select name="maneja_series" id="maneja_series"><option value="all" selected>Todos</option>';
		foreach($array_nosi as $k=>$v){
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
			objeto.open("POST","productos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&codigo="+document.getElementById("codigo").value+"&nom="+document.getElementById("nom").value+"&clasificacion="+document.getElementById("clasificacion").value+"&maneja_series="+document.getElementById("maneja_series").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

