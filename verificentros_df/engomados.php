<?php 

include ("main.php"); 
$rsDepto=mysql_query("SELECT * FROM areas");
while($Depto=mysql_fetch_array($rsDepto)){
	$array_localidad[$Depto['cve']]=$Depto['nombre'];
}

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM engomados WHERE 1 ";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		if($_POST['localidad']!=0) $select.=" AND localidad='".$_POST['localidad']."'";
		$select.=" ORDER BY nombre";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th><th>Nombre</th><th>Precio</th><th>Localidad</th><th>Numero Agrupador</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef['nombre'].'"></a></td>';
				echo '<td>'.htmlentities($row['nombre']).'</td>';
				echo '<td align="right">'.$row['precio'].'</td>';
				echo '<td>'.htmlentities(utf8_encode($array_localidad[$row['localidad']])).'</td>';
				echo '<td align="center">'.htmlentities($row['numero']).'</td>';
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
			$update = " UPDATE engomados 
						SET nombre='".$_POST['nombre']."',precio='".$_POST['precio']."',localidad='".$_POST['localidad']."',mostrar_registro='".$_POST['mostrar_registro']."',numero='".$_POST['numero']."',venta='".$_POST['venta']."',entrega='".$_POST['entrega']."',plazas='$plazas'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);			
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO engomados 
						(nombre,precio,localidad,mostrar_registro,numero,venta,entrega,plazas)
						VALUES 
						('".$_POST['nombre']."','".$_POST['precio']."','".$_POST['localidad']."','".$_POST['mostrar_registro']."','".$_POST['numero']."','".$_POST['venta']."','".$_POST['entrega']."','$plazas')";
			$ejecutar = mysql_query($insert);
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM engomados WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'engomados.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'engomados.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Engomados</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"></td></tr>';
		echo '<tr><th align="left">Precio</th><td><input type="text" name="precio" id="precio" class="textField" size="10" value="'.$row['precio'].'"></td></tr>';
		echo '<tr><th align="left">Localidad</th><td><select name="localidad" id="localidad" onChange="mostrarPlazas()"><option value="0">Seleccione</option>';
		$res1=mysql_query("SELECT cve,nombre FROM areas ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['cve'].'"';
			if($row1['cve']==$row['localidad']) echo ' selected';
			echo '>'.$row1['nombre'].'</option>';
		}
		echo '</td></tr>';
		echo '<tr><th align="left">Numero</th><td><input type="text" name="numero" id="numero" class="textField" size="10" value="'.$row['numero'].'"></td></tr>';
		echo '<tr><th align="left">Mostrar Registro Web</th><td><input type="checkbox" name="mostrar_registro" id="mostrar_registro" value="1"';
		if($row['mostrar_registro']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr><th align="left">Venta</th><td><input type="checkbox" name="venta" id="venta" value="1"';
		if($row['venta']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr><th align="left">Entrega</th><td><input type="checkbox" name="entrega" id="entrega" value="1"';
		if($row['entrega']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr><th align="left" valign="top">Plazas<input type="checkbox" onClick="if(this.checked){$(\'.clase_\'+document.forma.localidad.value).attr(\'checked\',\'checked\');} else{$(\'.cplazas\').removeAttr(\'checked\');}"></th><td>';
		$res1 = mysql_query("SELECT a.cve, a.numero, a.nombre, b.localidad_id FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus != 'I'");
		while($row1 = mysql_fetch_array($res1)){
			echo '<div';
			if($row1['localidad_id'] != $row['localidad']) echo ' style="display:none;"><input type="checkbox" name="rplazas[]"';
			else echo '><input type="checkbox" name="mplazas[]"';
			echo ' id="plazas_'.$row1['cve'].'" value="'.$row1['cve'].'" class="cplazas clase_'.$row1['localidad_id'].'"';
			$pos = strpos($row['plazas'], '|'.$row1['cve'].'|');
			if ($pos !== false) echo ' checked';
			echo '>'.$row1['numero'].'    '.$row1['nombre'].'</div>';
		}
		echo '</td>';
		echo '</table>';
		
		echo '<script>
				function mostrarPlazas(){
					$(".cplazas").each(function(){
						campo = $(this);
						campo.parents("div:first").hide();
						campo.attr("name","rplazas[]");
					});
					
					$(".clase_"+document.forma.localidad.value).each(function(){
						campo = $(this);
						campo.parents("div:first").show();
						campo.attr("name","mplazas[]");
					});
				}
			</script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'engomados.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td></tr>';
		echo '<tr><th>Localidad</th><td><select name="localidad" id="localidad"><option value="0">Todas</option>';
		$res1=mysql_query("SELECT cve,nombre FROM areas ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['cve'].'"';
			echo '>'.$row1['nombre'].'</option>';
		}
		echo '</td></tr>';
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
			objeto.open("POST","engomados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&localidad="+document.getElementById("localidad").value+"&nom="+document.getElementById("nom").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

