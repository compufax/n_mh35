<?php 

include ("main.php"); 

$res=mysql_query("SELECT local FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];
$array_estatus=array('Activo', 'Inactivo');
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM cat_clientes WHERE plaza='".$_POST['plazausuario']."'";
		if ($_POST['estatus']!="all") { $select.=" AND estatus = '".$_POST['estatus']."' "; }
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		$select.=" ORDER BY nombre";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		$nivelUsuario = nivelUsuario();
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th><th>Placa</th><th>Nombre</th><th>Mail</th><th>Telefono</th><th>Estatus</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>
				<a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef['nombre'].'"></a>';
				if($nivelUsuario > 2) echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de inactivar al tecnico?\')) atcr(\'\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Inactivar '.$Benef['nombre'].'"></a>';
				echo '</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($row['placa'])).'</td>';
				echo '<td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($row['mail'])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($row['telefono'])).'</td>';
				echo '<td align="center">'.$array_estatus[$row['estatus']].'</td>';
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

if($_POST['cmd'] == 3){
	mysql_query("UPDATE cat_clientes SET estatus=1 WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$_POST['cmd'] = 0;
}

if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE cat_clientes 
						SET nombre='".$_POST['nombre']."',placa='".$_POST['placa']."',mail='".$_POST['mail']."',telefono='".$_POST['telefono']."'
						WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);			
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO cat_clientes 
						(nombre,placa,plaza,mail,telefono)
						VALUES 
						('".$_POST['nombre']."','".$_POST['placa']."','".$_POST['plazausuario']."','".$_POST['mail']."','".$_POST['telefono']."')";
			$ejecutar = mysql_query($insert);
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM cat_clientes WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);

		//$res1=mysql_query("SELECT COUNT(cve) FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND tecnico='".$_POST['reg']."' AND tecnico>0");
		//$row1=mysql_fetch_array($res1);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)// && $row1[0]<=10
				echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'cat_clientes.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'cat_clientes.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Clientes</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">Placa</th><td><input type="text" name="placa" id="placa" class="textField" size="15" value="'.$row['placa'].'"></td></tr>';
		echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"><small>Comenzar con apellido paterno</small></td></tr>';
		echo '<tr><th align="left">Mail</th><td><input type="text" name="mail" id="mail" class="textField" size="30" value="'.$row['mail'].'"></td></tr>';
		echo '<tr><th align="left">Telefono</th><td><input type="text" name="telefono" id="telefono" class="textField" size="30" value="'.$row['telefono'].'"></td></tr>';
		echo '</table>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
			if($PlazaLocal!=1)
				echo '<td><a href="#" onClick="atcr(\'cat_clientes.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="all">Todos</option>';
		foreach($array_estatus as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==0) echo ' selected';
			echo '>'.$v.'</option>';
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
			objeto.open("POST","cat_clientes.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&estatus="+document.getElementById("estatus").value+"&nom="+document.getElementById("nom").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

