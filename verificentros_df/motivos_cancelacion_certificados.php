<?php 

include ("main.php"); 

if($_POST['cmd'] == 3){
	$res1 = mysql_query("SELECT COUNT(cve) FROM certificados_cancelados WHERE motivo='".$_POST['reg']."'");
	$row1 = mysql_fetch_array($res1);
	if($row1[0] == 0) mysql_query("DELETE FROM motivos_cancelacion_certificados WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM motivos_cancelacion_certificados WHERE 1 ";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		$select.=" ORDER BY nombre";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			$nivelUsuario = nivelUsuario();
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th><th>Nombre</th>';
			if($nivelUsuario > 1) echo '<th>Borrar</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef['nombre'].'"></a></td>';
				echo '<td>'.htmlentities($row['nombre']).'</td>';
				if($nivelUsuario > 1){
					$res1 = mysql_query("SELECT COUNT(cve) FROM certificados_cancelados WHERE motivo='".$row['cve']."'");
					$row1 = mysql_fetch_array($res1);
					if($row1[0] > 0)
						echo '<td>&nbsp;</td>';
					else
						echo '<td align="center" width="40" nowrap><a href="#" onClick="if(confirm(\'Esta seguro de eliminar el registro?\'))atcr(\'\',\'\',\'3\','.$row['cve'].')"><img src="images/basura.gif" border="0" title="Borrar '.$Benef['nombre'].'"></a></td>';
				}
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
if($_POST['cmd']==12){
	if($_POST['motivo1']>0 && $_POST['motivo2']>0){
		mysql_query("UPDATE certificados_cancelados SET motivo = '".$_POST['motivo2']."' WHERE motivo='".$_POST['motivo1']."'");
	}
	$_POST['cmd']=0;
}

if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE motivos_cancelacion_certificados 
						SET nombre='".$_POST['nombre']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);			
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO motivos_cancelacion_certificados 
						(nombre)
						VALUES 
						('".$_POST['nombre']."')";
			$ejecutar = mysql_query($insert);
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

if($_POST['cmd'] == 11)
{
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1)
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'motivos_cancelacion_certificados.php\',\'\',\'12\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'motivos_cancelacion_certificados.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';

	echo '<table>';
	echo '<tr><td class="tableEnc">Reemplazo Motivos de Cancelacion</td></tr>';
	echo '</table>';
	
	echo '<table>';
	echo '<tr><th align="left">Motivo Origen</th><td><select name="motivo1"><option value="0">Seleccione</option>';
	$res = mysql_query("SELECT * FROM motivos_cancelacion_certificados ORDER BY nombre");
	while($row = mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['nombre'].'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Motivo Destino</th><td><select name="motivo2"><option value="0">Seleccione</option>';
	$res = mysql_query("SELECT * FROM motivos_cancelacion_certificados ORDER BY nombre");
	while($row = mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['nombre'].'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
}

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM motivos_cancelacion_certificados WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'motivos_cancelacion_certificados.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'motivos_cancelacion_certificados.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Motivos de Cancelacion de Certificados</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"></td></tr>';
		echo '</table>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'motivos_cancelacion_certificados.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
		if(nivelUsuario() > 1){	
			echo '<td><a href="#" onClick="atcr(\'motivos_cancelacion_certificados.php\',\'\',\'11\',\'0\');"><img src="images/modificar.gif" border="0"></a>&nbsp;Reemplazar</td><td>&nbsp;</td>';
		}
		echo '
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
			objeto.open("POST","motivos_cancelacion_certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
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

