<?php 

include ("main.php"); 


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM refacciones WHERE 1 ";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		if ($_POST['noparte']!="") { $select.=" AND noparte = '".$_POST['noparte']."' "; }
		if ($_POST['noserie']!="") { $select.=" AND noserie = '".$_POST['noserie']."' "; }
		$select.=" ORDER BY noparte";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th>';
			echo '<th>No Parte</th><th>No Serie</th><th>Descripci&oacute;n</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef['nombre'].'"></a></td>';
				echo '<td>'.utf8_encode($row['noparte']).'</td>';
				echo '<td>'.utf8_encode($row['noserie']).'</td>';
				echo '<td>'.utf8_encode($row['nombre']).'</td>';
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


/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE refacciones 
						SET nombre='".$_POST['nombre']."',noparte='".$_POST['noparte']."',noserie='".$_POST['noserie']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);			
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO refacciones 
						(nombre,noparte,noserie)
						VALUES 
						('".$_POST['nombre']."','".$_POST['noparte']."','".$_POST['noserie']."')";
			$ejecutar = mysql_query($insert);
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM refacciones WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show(); atcr(\'refacciones.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'refacciones.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Refacciones</td></tr>';
		echo '</table>';
		
		echo '<table>';

		echo '<tr><th align="left">No Parte</th><td><input type="text" name="noparte" id="noparte" class="textField" size="10" value="'.$row['noparte'].'"></td></tr>';
		echo '<tr><th align="left">No Serie</th><td><input type="text" name="noserie" id="noserie" class="textField" size="10" value="'.$row['noserie'].'"></td></tr>';
		echo '<tr><th align="left">Descripcio&oacute;n</th><td><input type="text" name="descripcion" id="descripcion" class="textField" size="100" value="'.$row['nombre'].'"></td></tr>';
		echo '</table>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'refacciones.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<tr><td>No Parte</td><td><input type="text" name="noparte" id="noparte" size="10" class="textField" value=""></td></tr>';
		echo '<tr><td>No Serie</td><td><input type="text" name="noserie" id="noserie" size="50" class="textField" value=""></td></tr>';
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
			objeto.open("POST","refacciones.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&noparte="+document.getElementById("noparte").value+"&noserie="+document.getElementById("noserie").value+"&nom="+document.getElementById("nom").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

