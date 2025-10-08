<?php 

include ("call_main.php"); 


$array_tipo=array("Telefonista","Administrador");
		
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	
		//Listado de tecnicos y administradores
		if($_SESSION['CveUsuario']>1){
			$select= " SELECT * FROM call_usuarios WHERE estatus!='I' AND cve>'1'";
		}
		else{
			$select= " SELECT * FROM call_usuarios WHERE estatus!='I'";
		}
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		$rsusuarios=mysql_query($select);
		$totalRegistros = mysql_num_rows($rsusuarios);
		$select .= " ORDER BY nombre";
		$rsusuarios=mysql_query($select);
		
		if(mysql_num_rows($rsusuarios)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="6">'.mysql_num_rows($rsusuarios).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Accesos</th><th>Nombre</th><th>Tipo</th><th>Usuario</th><th>Borrar</th></tr>';
			while($Usuario=mysql_fetch_array($rsusuarios)) {
				rowb();
				if($Usuario['cve']==1 && $_SESSION['CallCveUsuario']!=1)
					echo '<td align="center" width="40" nowrap>&nbsp;</td>';
				else
					echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$Usuario['cve'].')"><img src="images/key.png" border="0" title="Editar '.$Usuario['nombre'].'"></a></td>';
				$extra="";
				if($Usuario['estatus']=="I")
					$extra=" (INACTIVO)";
				$title='';
				
				echo '<td title="'.$title.'">'.htmlentities(utf8_encode($Usuario['nombre'])).$extra.'</td>';
				echo '<td>'.htmlentities($array_tipo[$Usuario['tipo']]).'</td>';
				echo '<td>'.htmlentities($Usuario['usuario']).'</td>';
				if($Usuario['cve']==1)
					echo '<td align="center" width="40" nowrap>&nbsp;</td>';
				else
					echo '<td align="center" width="40" nowrap><a href="#" onClick="borrar('.$Usuario['cve'].')"><img src="images/basura.gif" border="0" title="Borrar '.$Usuario['nombre'].'"></a></td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="6" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
	$res=mysql_query("SELECT * FROM call_usuarios WHERE usuario='".$_POST['usuario']."' AND estatus!='I' AND cve!='".$_POST['cveusu']."'");
	if(mysql_num_rows($res)>0){
		echo "1";
	}
	else{
		echo "0";
	}
	exit();
}

top($_SESSION);

/*** ELIMINAR REGISTRO  **************************************************/

if ($_POST['cmd']==3) {
	$delete= "UPDATE call_usuarios SET estatus='I' WHERE cve='".$_POST['reg']."' ";
	$ejecutar=mysql_query($delete);
	mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Estatus',nuevo='I',anterior='A',arreglo='',usuario='".$_POST['cveusuario']."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	if($_POST['reg']) {
		$select=" SELECT * FROM call_usuarios WHERE cve='".$_POST['reg']."' ";
		$rssuario=mysql_query($select);
		$Usuario=mysql_fetch_array($rssuario);
		if($Usuario['nombre']!=$_POST['nombre']){
			mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Nombre',nuevo='".$_POST['nombre']."',anterior='".$Usuario['nombre']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['password']!=$_POST['password']){
			mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Password',nuevo='".$_POST['password']."',anterior='".$Usuario['password']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['tipo']!=intval($_POST['tipo'])){
			mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Tipo',nuevo='".intval($_POST['tipo'])."',anterior='".$Usuario['tipo']."',arreglo='tipo',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['permitir_mas_dias']!=intval($_POST['permitir_mas_dias'])){
			mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Permitir mas de 3 dias',nuevo='".intval($_POST['permitir_mas_dias'])."',anterior='".$Usuario['permitir_mas_dias']."',arreglo='nosi',usuario='".$_POST['cveusuario']."'");
		}
		
		
		//Actualizar el Registro
			$update = " UPDATE call_usuarios 
					SET nombre='".$_POST['nombre']."',password='".$_POST['password']."',tipo='".$_POST['tipo']."',permitir_mas_dias='".$_POST['permitir_mas_dias']."' 
					WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_query($update);
		$cveusu=$_POST['reg'];
	} else {
		//Insertar el Registro
		$insert = " INSERT INTO call_usuarios (nombre,usuario,password,tipo,permitir_mas_dias)
					VALUES 
					( '".$_POST['nombre']."','".$_POST['usuario']."','".$_POST['password']."','".$_POST['tipo']."','".$_POST['permitir_mas_dias']."')";
		$ejecutar = mysql_query($insert) or die(mysql_error());
		$cveusu=mysql_insert_id();
		mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
		dato='Estatus',nuevo='A',anterior='',arreglo='',usuario='".$_POST['cveusuario']."'");
		
	}
	
	
	$_POST['cmd']=0;
}


/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM call_usuarios WHERE cve='".$_POST['reg']."' ";
		$rssuario=mysql_query($select);
		$Usuario=mysql_fetch_array($rssuario);
		//Menu
		echo '<table>';
		echo '
			<tr>';
		if($_POST['tipousuario']==1)
		echo '<td><a href="#" onClick="$(\'#panel\').show();validarUsuario('.$_POST['reg'].');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '
			<td><a href="#" onClick="$(\'#panel\').show();atcr(\'call_usuarios.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Usuarios</td></tr>';
		echo '</table>';

		//Formulario 
		echo '<table>';
		
		echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" value="'.$Usuario['nombre'].'" size="40" class="textField"></td></tr>';
		if($_POST['reg']>0){
			echo '<tr><th align="left">Usuario</th><td><input autocomplete="off" type="text" name="usuario" id="usuario" value="'.$Usuario['usuario'].'" class="readOnly" readOnly></td></tr>';
		}
		else{
			echo '<tr><th align="left">Usuario</th><td><input autocomplete="off" type="text" name="usuario" id="usuario" value="'.$Usuario['usuario'].'" class="textField"></td></tr>';
		}
		echo '<tr><th align="left">Password</th><td><input autocomplete="off" type="password" name="password" id="password" value="'.$Usuario['password'].'" class="textField"></td></tr>';
		echo '<tr><th align="left">Tipo</th><td><select name="tipo" id="tipo">';
		foreach($array_tipo as $k=>$v)
		{
			echo '<option value="'.$k.'"';
			if($k==$Usuario['tipo']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Permitir generar citas despues de 3 dias</th><td><input type="checkbox" name="permitir_mas_dias" id="permitir_mas_dias" value="1"';
		if($Usuario['permitir_mas_dias']==1) echo ' checked';
		echo '></td></tr>';
		echo '</table>';
		
		
		if($_POST['reg']==0){
			echo '<script>
			window.onload = function () {
				document.forma.usuario.value="";
				document.forma.password.value="";
			}
			</script>';
		}
		echo '<script>
				function validarUsuario(reg)
				{
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto.open("POST","call_usuarios.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=2&cveusu="+reg+"&usuario="+document.getElementById("usuario").value);
						objeto.onreadystatechange = function()
						{
							if (objeto.readyState==4)
							{
								if(objeto.responseText=="1"){
									$("#panel").hide();
									alert("El usuario ya esta registrado");
								}
								else{
									atcr("call_usuarios.php","",2,reg);
								}
							}
						}
					}
				}
			</script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'call_usuarios.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="20" class="textField"></td></tr>';		
		echo '</table>';
		echo '<br>';		

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
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
					objeto.open("POST","call_usuarios.php",true);
					objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
			}	
			
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
			</Script>';
	}
	
bottom();





?>

