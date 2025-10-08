<?php 

include ("main.php"); 


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM nomenclatura WHERE 1 ";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		$select.=" ORDER BY nombre";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th><th>Nombre</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef['nombre'].'"></a></td>';
				echo '<td>'.htmlentities(utf8_encode($row['abreviacion']).' - '.utf8_encode($row['nombre'])).'</td>';
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

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE nomenclatura 
						SET nombre='".$_POST['nombre']."',abreviacion='".$_POST['abreviacion']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);	

		$i=0;
		
	foreach($_POST['unidad'] as $k=>$v){
//		alert($_POST['unidad'][$k]);
		$res1=mysql_query("SELECT * FROM nomenclatura_detalle WHERE cve_nomen = '".$_POST['reg']."' and tipo='".$k."' ");
		$row=mysql_num_rows($res1);
			if($row>0){
			 $ejecutar = mysql_query("update nomenclatura_detalle SET nombre='".$_POST['unidad'][$k]."' where cve_nomen = '".$_POST['reg']."' and tipo='".$k."'") or die(mysql_error());
			}else{
			$ejecutar = mysql_query("INSERT nomenclatura_detalle SET cve_nomen='".$_POST['reg']."',nombre='".$_POST['unidad'][$k]."',tipo='".$k."'") or die(mysql_error());
			}
			$i++;
	}
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO nomenclatura 
						(nombre,abreviacion)
						VALUES 
						('".$_POST['nombre']."','".$_POST['abreviacion']."')";
			$ejecutar = mysql_query($insert);
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM nomenclatura WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'nomenclatura.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'nomenclatura.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Nomenclatura</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">Abreviacion</th><td><input type="text" name="abreviacion" id="abreviacion" class="textField" size="20" value="'.$row['abreviacion'].'"></td></tr>';
		echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"></td></tr>
			  </table>
			  <h2>Submodulo de '.$row['abreviacion'].'</h2>
			  <table id="tablaproductos"><tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
		
		$i=0;
		$res1=mysql_query("SELECT * FROM nomenclatura_detalle WHERE cve_nomen = '".$_POST['reg']."' ");
		$class='textField';
		$attr='';
		if($row['factura_pedido']==1){
			$class='readOnly';
			$attr=' readOnly';
		}
		while($row1 = mysql_fetch_array($res1)){
			echo '<tr class="detallefactura"><td align="left">Nombre</td><td><input type="text" name="unidad['.$i.']" id="unidad['.$i.']" class="textField" size="100" value="'.$row1['nombre'].'"></td></tr>';
			$i++;
		}
		if($i==0){
			echo '<tr class="detallefactura"><td align="left">Nombre</td><td><input type="text" name="unidad['.$i.']" id="unidad['.$i.']" class="textField" size="100" value="'.$row1['nombre'].'"></td></tr>';			
			$i++;
		}
		
		echo '<input type="hidden" name="cantprod" id="cantprod" value="'.$i.'">
			  </table>
			  <input type="button" value="Agregar" id="agregar_detalle" onClick="agregarproducto()" class="textField">';
		echo '
		<script>
		function agregarproducto(){
				num=document.forma.cantprod.value;
				$("#tablaproductos").append(\'<tr class="detallefactura">\
		<td>Nombre</td><td><input type="text" name="unidad[\'+num+\']" id="unidad\'+num+\'" class="textField" size="100" value=""></td></tr>\');
				num++;
				document.forma.cantprod.value=num;
			}
		</script>';
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'nomenclatura.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td></tr>';
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
			objeto.open("POST","nomenclatura.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

