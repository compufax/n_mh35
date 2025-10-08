<?php 

include ("main.php"); 

$res = mysql_query("SELECT * FROM cat_plantillas WHERE 1 order by nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_plantilla[$row['cve']]=$row['nombre']; 
	}

$res = mysql_query("SELECT * FROM proveedores WHERE 1 order by nombre");
while($row=mysql_fetch_array($res)){
	$array_proveedor_mantenimiento[$row['cve']]=$row['nombre']; 
}
$array_tipo=array("Normal","Administrador");
$array_categoria=array("Ninguna","Caja","Computo","Corporativo","Administrador","Papeleria");
		
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		$select= " SELECT a.* FROM usuarios a";
		//Listado de tecnicos y administradores
		if($_POST['liga'] > 0) {
			$select .= " LEFT JOIN usuario_accesos b ON a.cve = b.usuario AND b.menu='".$_POST['liga']."'";
		}
		if($_SESSION['CveUsuario']>1){
			$select.= " WHERE a.estatus!='I' AND a.borrado!=1 AND a.cve>'1'";
		}
		else{
			 $select.= " WHERE a.borrado!=1";
		}
		
		if ($_POST['nom']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nom']."%' "; }
		if ($_POST['usuario']!="") { $select.=" AND a.usuario = '".$_POST['usuario']."' "; }
		if ($_POST['categoria']!="all") { $select.=" AND a.categoria='".$_POST['categoria']."' "; }
		if ($_POST['estatus']!="all") { $select.=" AND a.estatus='".$_POST['estatus']."' "; }
		if ($_POST['plantilla']!="") { $select.=" AND a.plantilla='".$_POST['plantilla']."' "; }
		$filtroliga = '';
		if($_POST['liga'] > 0) {
			if($_POST['nivel'] == 3)
				$select .= " AND (IFNULL(b.acceso,0) = '".$_POST['nivel']."' OR a.tipo = 1)";
			else
				$select .= " AND IFNULL(b.acceso,0) = '".$_POST['nivel']."'";

			$filtroliga = " AND b.menu = '".$_POST['liga']."' AND IFNULL(b.acceso,0) = '".$_POST['nivel']."'";
		}
		$rsusuarios=mysql_query($select);
		$totalRegistros = mysql_num_rows($rsusuarios);
		$select .= " GROUP BY a.cve ORDER BY a.nombre";
		$rsusuarios=mysql_query($select);
		
		if(mysql_num_rows($rsusuarios)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="10">'.mysql_num_rows($rsusuarios).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Accesos</th><th>Nombre</th><th>Tipo</th><th>Usuario</th><th>Categoria</th><th>Plantilla</th><th>Plazas</th><th>Borrar</th>';
			if($_POST['cveusuario']==1) echo '<th>Usuario Creo</th><th>Fecha Creacion</th>';
			echo '</tr>';
			while($Usuario=mysql_fetch_array($rsusuarios)) {
				rowb();
				if((($Usuario['cve']==1 || $Usuario['cve']==13) && $_SESSION['CveUsuario']!=1) || ($Usuario['tipo']==1 && $_SESSION['TipoUsuario']!=1))
					echo '<td align="center" width="40" nowrap>&nbsp;</td>';
				else
					echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$Usuario['cve'].')"><img src="images/key.png" border="0" title="Editar '.$Usuario['nombre'].'"></a></td>';
				$extra="";
				if($Usuario['estatus']=="I")
					$extra=" (INACTIVO)";
				$title='';
				
				//if($Usuario['empresa']==-1)
				$filtroplaza="";
				$res = mysql_query("SELECT * FROM usuario_accesos WHERE usuario='".$Usuario['cve']."' AND menu = 6  AND acceso > 0".$filtroplaza);
				if($row=mysql_fetch_array($res))
					echo '<td><font color="RED">'.htmlentities(utf8_encode($Usuario['nombre'])).$extra.'</font></td>';
				else
					echo '<td title="'.$title.'">'.htmlentities(utf8_encode($Usuario['nombre'])).$extra.'</td>';
				echo '<td>'.htmlentities($array_tipo[$Usuario['tipo']]).'</td>';
				echo '<td>'.htmlentities($Usuario['usuario']).'</td>';
				echo '<td>'.htmlentities($array_categoria[$Usuario['categoria']]).'</td>';
				echo '<td>'.htmlentities($array_plantilla[$Usuario['plantilla']]).'*</td>';
				echo '<td><ul>';
				if($Usuario['cve']==1 || $Usuario['tipo']==1)
					$res = mysql_query("SELECT a.cve,a.numero,a.nombre FROM plazas a LEFT JOIN datosempresas b on a.cve = b.plaza WHERE a.estatus!='I' ORDER BY b.localidad_id, a.numero");
				else
					$res = mysql_query("SELECT a.cve,a.numero,a.nombre FROM plazas a INNER JOIN usuario_accesos b ON a.cve=b.plaza AND b.usuario='".$Usuario['cve']."' AND b.acceso>0 LEFT JOIN datosempresas c on a.cve = c.plaza WHERE a.estatus!='I' {$filtroliga} GROUP BY a.cve ORDER BY c.localidad_id, a.numero");
				while($row=mysql_fetch_array($res)){
					echo '<li>'.$row['numero'].' '.htmlentities(utf8_encode($row['nombre'])).'</li>';
				}
				echo '</ul></td>';
				if($Usuario['cve']==1)
					echo '<td align="center" width="40" nowrap>&nbsp;</td>';
				elseif($Usuario['estatus'] != 'I')
					echo '<td align="center" width="40" nowrap><a href="#" onClick="borrar('.$Usuario['cve'].')"><img src="images/basura.gif" border="0" title="Borrar '.$Usuario['nombre'].'"></a></td>';
				elseif($Usuario['estatus'] == 'I'){
					echo '<td align="center" width="40" nowrap><a href="#" onClick="activar('.$Usuario['cve'].')"><img src="images/validosi.gif" border="0" title="Activar '.$Usuario['nombre'].'"></a>
					&nbsp;&nbsp;<a href="#" onClick="eliminar('.$Usuario['cve'].')"><img src="images/validono.gif" border="0" title="Eliminar '.$Usuario['nombre'].'"></a></td>';
				}
				if($_POST['cveusuario'] == 1){
					$res = mysql_query("SELECT b.usuario, a.fecha FROM historial a INNER JOIN usuarios b ON b.cve = a.usuario WHERE a.menu=1 AND a.cveaux = '".$Usuario['cve']."' AND a.dato='Estatus' AND a.nuevo = 'A' AND a.anterior = ''");
					$row = mysql_fetch_array($res);
					echo '<td align="center">'.$row['usuario'].'</td>';
					echo '<td align="center">'.$row['fecha'].'</td>';
				}
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="10" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
	$res=mysql_query("SELECT * FROM usuarios WHERE usuario='".$_POST['usuario']."' AND estatus!='I' AND cve!='".$_POST['cveusu']."'");
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
	$delete= "UPDATE usuarios SET estatus='I' WHERE cve='".$_POST['reg']."' ";
	$ejecutar=mysql_query($delete);
	mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Estatus',nuevo='I',anterior='A',arreglo='',usuario='".$_POST['cveusuario']."'");
	$_POST['cmd']=0;
}

if ($_POST['cmd']==4) {
	$delete= "UPDATE usuarios SET estatus='A' WHERE cve='".$_POST['reg']."' ";
	$ejecutar=mysql_query($delete);
	mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Estatus',nuevo='A',anterior='I',arreglo='',usuario='".$_POST['cveusuario']."'");
	$_POST['cmd']=0;
}

if ($_POST['cmd']==5) {
	$delete= "UPDATE usuarios SET estatus='I',borrado=1 WHERE cve='".$_POST['reg']."' ";
	$ejecutar=mysql_query($delete);
	mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Estatus',nuevo='B',anterior='I',arreglo='',usuario='".$_POST['cveusuario']."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	if($_POST['reg']) {
		$select=" SELECT * FROM usuarios WHERE cve='".$_POST['reg']."' ";
		$rssuario=mysql_query($select);
		$Usuario=mysql_fetch_array($rssuario);
		if($Usuario['plantilla']!=$_POST['plantilla']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Plantilla',nuevo='".$_POST['plantilla']."',anterior='".$Usuario['plantilla']."',arreglo='plantilla',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['nombre']!=$_POST['nombre']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Nombre',nuevo='".$_POST['nombre']."',anterior='".$Usuario['nombre']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['password']!=$_POST['password']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Password',nuevo='".$_POST['password']."',anterior='".$Usuario['password']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['plaza']!=$_POST['plaza']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Plaza',nuevo='".$_POST['plaza']."',anterior='".$Usuario['plaza']."',arreglo='plaza',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['autoriza_vales']!=intval($_POST['autoriza_vales'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Autoriza Vales',nuevo='".intval($_POST['autoriza_vales'])."',anterior='".$Usuario['autoriza_vales']."',arreglo='nosi',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['tipo']!=intval($_POST['tipo'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Tipo',nuevo='".intval($_POST['tipo'])."',anterior='".$Usuario['tipo']."',arreglo='tipo',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['empresa']!=intval($_POST['empresa'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Empresa',nuevo='".intval($_POST['empresa'])."',anterior='".$Usuario['empresa']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		
		if($_POST['plazausuario']>0){
			$res = mysql_query("SELECT * FROM menu WHERE modulo>0 ORDER BY cve");
			while($row = mysql_fetch_array($res)){
				$res1=mysql_query("SELECT * FROM usuario_accesos WHERE usuario='".$_POST['reg']."' AND menu='".$row['cve']."' AND plaza='".$_POST['plazausuario']."'");
				$row1=mysql_fetch_array($res1);
				if($row1['acceso']!=$_POST['acceso'.$row['cve']]){
					mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
					dato='".$row['cve']."',nuevo='".$_POST['acceso'.$row['cve']]."',anterior='".$row1['acceso']."',arreglo='',usuario='".$_POST['cveusuario']."'");
				}
			}
		}
		
		
		//Actualizar el Registro
			$update = " UPDATE usuarios 
					SET nombre='".$_POST['nombre']."',password='".$_POST['password']."',cerrar_sistema='".$_POST['cerrar_sistema']."',permite_editar='".$_POST['permite_editar']."',cerrar_portal='".$_POST['cerrar_portal']."',
					plaza='".$_POST['plaza']."',autoriza_vales='".$_POST['autoriza_vales']."',tipo='".$_POST['tipo']."',empresa='".$_POST['empresa']."',
					chat='".$_POST['chat']."',ide='".$_POST['ide']."',categoria='".$_POST['categoria']."',validar_huella='".$_POST['validar_huella']."',
					recargar_facturas='".$_POST['recargar_facturas']."',plantilla='".$_POST['plantilla']."',
					proveedor_mantenimiento='".$_POST['proveedor_mantenimiento']."',correotimbres='".$_POST['correotimbres']."'
					WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_query($update);
		$cveusu=$_POST['reg'];
	} else {
		//Insertar el Registro
		$insert = " INSERT INTO usuarios (nombre,usuario,password,plaza,cerrar_sistema,estatus,autoriza_vales,tipo,empresa,chat,ide,categoria,permite_editar,plantilla,proveedor_mantenimiento)
					VALUES 
					( '".$_POST['nombre']."','".$_POST['usuario']."','".$_POST['password']."','".$_POST['plaza']."','".$_POST['cerrar_sistema']."','A',
					'".$_POST['autoriza_vales']."','".$_POST['tipo']."','".$_POST['empresa']."','".$_POST['chat']."','".$_POST['ide']."','".$_POST['categoria']."','".$_POST['permite_editar']."','".$_POST['plantilla']."',
					'".$_POST['proveedor_mantenimiento']."')";
		$ejecutar = mysql_query($insert) or die(mysql_error());
		$cveusu=mysql_insert_id();
		mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
		dato='Estatus',nuevo='A',anterior='',arreglo='',usuario='".$_POST['cveusuario']."'");
		foreach($array_cc as $cc){
			mysql_query("INSERT usuario_autcc set autoriza='".intval($_POST['autcc_'.$cc])."',usuario='".$cveusu."',centrocostos='".$cc."'");
		}
	}
	
	if($_POST['plazausuario']>0){
	
		$res = mysql_query("SELECT * FROM menu ORDER BY cve");
		while($row = mysql_fetch_array($res)){
			$res1=mysql_query("SELECT * FROM usuario_accesos WHERE usuario='".$cveusu."' AND menu='".$row['cve']."' AND plaza='".$_POST['plazausuario']."'");
			if($row1=mysql_fetch_array($res1)){
				mysql_query("UPDATE usuario_accesos SET acceso='".$_POST['acceso'.$row['cve']]."' WHERE cve='".$row1['cve']."'");
			}
			else{
				mysql_query("INSERT usuario_accesos SET usuario='".$cveusu."',menu='".$row['cve']."',acceso='".$_POST['acceso'.$row['cve']]."',plaza='".$_POST['plazausuario']."'");
			}
		}
	}
	$_POST['cmd']=0;
}


/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM usuarios WHERE cve='".$_POST['reg']."' ";
		$rssuario=mysql_query($select);
		$Usuario=mysql_fetch_array($rssuario);
		$array1=explode(",",$Usuario['accesos']);
		for($i=0;$i<count($array1)-1;$i++){
			$array2=explode("-",$array1[$i]);
			$accesos[$array2[0]]=$array2[1];
		}
		//Menu
		echo '<table>';
		echo '
			<tr>';
		if(nivelUsuario()>1)
		echo '<td><a href="#" onClick="$(\'#panel\').show();validarUsuario('.$_POST['reg'].');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '
			<td><a href="#" onClick="$(\'#panel\').show();atcr(\'accesos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Permisos</td></tr>';
		echo '</table>';

		//Formulario 
		echo '<table>';
		
		echo '<tr><th>Nombre</th><td><input type="text" name="nombre" id="nombre" value="'.$Usuario['nombre'].'" size="40" class="textField"></td></tr>';
		if($_POST['reg']>0 && $_POST['cveusuario']!=1){
			echo '<tr><th>Usuario</th><td><input autocomplete="off" type="text" name="usuario" id="usuario" value="'.$Usuario['usuario'].'" class="readOnly" readOnly></td></tr>';
		}
		else{
			echo '<tr><th>Usuario</th><td><input autocomplete="off" type="text" name="usuario" id="usuario" value="'.$Usuario['usuario'].'" class="textField"></td></tr>';
		}
		echo '<tr><th>Password</th><td><input autocomplete="off" type="password" name="password" id="password" value="'.$Usuario['password'].'" class="textField"></td></tr>';
		if($Usuario['cve']==1 && $_SESSION['CveUsuario']==1){
			echo '<tr><th>Cerrar Sistema</th><td><input type="checkbox" name="cerrar_sistema" value="S"';
			if($Usuario['cerrar_sistema']=='S') echo ' checked';
			echo '></td></tr>';
			echo '<tr><th>Cerrar Portal</th><td><input type="checkbox" name="cerrar_portal" value="1"';
			if($Usuario['cerrar_portal']=='1') echo ' checked';
			echo '></td></tr>';
			echo '<tr><th>Correo Reporte Timbres</th><td><input type="text" class="textField" size="100" name="correotimbres" id="correotimbres" value="'.$Usuario['correotimbres'].'"></td></tr>';
		}
		echo '<tr style="display:none;"><th>Plaza</th><td><select name="plaza" id="plaza"><option value="0">Todas</option>';
		foreach($array_plaza as $k=>$v)
		{
			echo '<option value="'.$k.'"';
			if($k==$Usuario['plaza']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Tipo</th><td><select name="tipo" id="tipo">';
		foreach($array_tipo as $k=>$v)
		{
			echo '<option value="'.$k.'"';
			if($k==$Usuario['tipo']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Categoria</th><td><select name="categoria" id="categoria">';
		foreach($array_tipo as $k=>$v)
		{
			echo '<option value="'.$k.'"';
			if($k==$Usuario['categoria']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>Plantilla</th><td><select name="plantilla" id="plantilla"><option value="0">--Selecciones--</option>';
		foreach($array_plantilla as $k=>$v)
		{
			echo '<option value="'.$k.'"';
			if($k==$Usuario['plantilla']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Proveedor de Mantenimiento</th><td><select name="proveedor_mantenimiento" id="proveedor_mantenimiento"><option value="0">--Selecciones--</option>';
		foreach($array_proveedor_mantenimiento as $k=>$v)
		{
			echo '<option value="'.$k.'"';
			if($k==$Usuario['proveedor_mantenimiento']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Chat</th><td><input type="checkbox" name="chat" value="1"';
		if($Usuario['chat']=='1') echo ' checked';
		echo '></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>IDE</th><td><input type="text" name="ide" id="ide" value="'.$Usuario['ide'].'" size="50" class="textField"></td></tr>';
		if($_POST['cveusuario']==1 && $Usuario['cve']==1){
			echo '<tr><th>Validar Huella</th><td><input type="checkbox" name="validar_huella" value="1"';
			if($Usuario['validar_huella']=='1') echo ' checked';
			echo '></td></tr>';
			echo '<tr><th align="left">Recargar Facturas</th><td><input type="checkbox" name="recargar_facturas" value="1"';
			if($Usuario['recargar_facturas']=='1') echo ' checked';
			echo '></td></tr>';
		}
		else{
			echo '<input type="hidden" name="validar_huella" value="'.$Usuario['validar_huella'].'">';
			echo '<input type="hidden" name="recargar_facturas" value="'.$Usuario['recargar_facturas'].'">';
		}
		if($_POST['cveusuario']==1){
			echo '<tr><th>Permite Editar</th><td><input type="checkbox" name="permite_editar" value="1"';
			if($Usuario['permite_editar']=='1') echo ' checked';
			echo '></td></tr>';
		}
		else{
			echo '<input type="hidden" name="permite_editar" value="'.$Usuario['permite_editar'].'">';
		}
		echo '</table>';
		if($_POST['plazausuario']>0){
			echo '<table width="70%">';		
			echo '<tr><th colspan="5" align="left"><br>Accesos</th></tr>';
			foreach($array_modulos as $k=>$v){
				//if($_POST['cveusuario']==1 || $k<99){
				if($k!=19 || $_POST['cveusuario']==1 || $_POST['cveusuario']==420){
					echo '<tr><th colspan="5" align="left"><hr></th></tr>';
					echo '<tr><th colspan="5" align="left">'.$v.'</th></tr>';
					echo '<tr><th>Modulo</th><th>Sin Acceso</th><th>Lectura</th><th>Escritura</th><th>Supervisor</th></tr>';
					if($_POST['cveusuario']==1)
						$res = mysql_query("SELECT * FROM menu WHERE modulo='$k' ORDER BY orden");
					elseif($_POST['cveusuario']==2)
						$res = mysql_query("SELECT * FROM menu WHERE modulo='$k' AND cve!=2 AND cve!=7 ORDER BY orden");
					else
						$res = mysql_query("SELECT * FROM menu WHERE modulo='$k' AND cve!=2 AND cve!=1 AND cve!=7 ORDER BY orden");
					while($row = mysql_fetch_array($res)){
						$res1=mysql_query("SELECT * FROM usuario_accesos WHERE usuario='".$_POST['reg']."' AND menu='".$row['cve']."' AND plaza='".$_POST['plazausuario']."'");
						$row1=mysql_fetch_array($res1);
						rowb();
						echo '<td>'.$row['nombre'].'</td>';
						echo '<td align="center"><input type="radio" name="acceso'.$row['cve'].'" value="0"';
						if(intval($row1['acceso'])<1) echo ' checked'; 
						echo '></td>';
						echo '<td align="center"><input type="radio" name="acceso'.$row['cve'].'" value="1"';
						if(intval($row1['acceso'])==1) echo ' checked'; 
						echo '></td>';
						echo '<td align="center"><input type="radio" name="acceso'.$row['cve'].'" value="2"';
						if(intval($row1['acceso'])==2) echo ' checked'; 
						echo '></td>';
						echo '<td align="center"><input type="radio" name="acceso'.$row['cve'].'" value="3"';
						if(intval($row1['acceso'])==3) echo ' checked';
						echo '></td>';
					}
				}
			}
		}
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
						objeto.open("POST","accesos.php",true);
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
									atcr("accesos.php","",2,reg);
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
				<td><a href="#" onClick="atcr(\'accesos.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="30" class="textField"></td></tr>';	
		echo '<tr><td>Usuario</td><td><input type="text" name="usuario" id="usuario" size="15" class="textField"></td></tr>';	
		echo '<tr><td>Categoria</td><td><select name="categoria" id="categoria"><option value="all" selected>Todas</option>';
		foreach($array_tipo as $k=>$v)
		{
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Plantilla</td><td><select name="plantilla" id="plantilla"><option value="" selected>Todas</option>';
		foreach($array_plantilla as $k=>$v)
		{
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr';
		if($_POST['cveusuario'] != 1) echo ' style="display:none;"';
		echo '><td>Estatus</td><td><select name="estatus" id="estatus"><option value="all">Todos</option><option value="A" selected>Activo</option><option value="I">Inactivo</option></select></td></tr>';
		echo '<tr><td>Liga</td><td><select name="liga" id="liga" onChange="
		if((this.value/1)>0)
			$(\'#nivel\').parents(\'tr:first\').show();
		else
			$(\'#nivel\').parents(\'tr:first\').hide();"><option value="0">Todas</option>';
		foreach($array_modulos as $k=>$v){
			echo '<optgroup label="'.$v.'">';
			$res = mysql_query("SELECT * FROM menu WHERE modulo = '$k' ORDER BY orden");
			while($row = mysql_fetch_array($res)){
				echo '<option value="'.$row['cve'].'">'.$row['nombre'].'</option>';
			}
			echo '</optgroup>';
		}
		echo '</select></td></tr>';
		echo '<tr style="display:none;"><td>Nivel</td><td><select name="nivel" id="nivel"><option value="0">Sin Acceso</option><option value="1">Solo Lectura</option><option value="2">Escritura</option><option value="3">Supervisor</option></select></td></tr>';
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
					objeto.open("POST","accesos.php",true);
					objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto.send("ajax=1&cveusuario="+document.getElementById("cveusuario").value+"&estatus="+document.getElementById("estatus").value+"&categoria="+document.getElementById("categoria").value+"&usuario="+document.getElementById("usuario").value+"&nom="+document.getElementById("nom").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plantilla="+document.getElementById("plantilla").value+"&liga="+document.getElementById("liga").value+"&nivel="+document.getElementById("nivel").value);
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

			function activar(cveusuario){
				if(confirm("Esta seguro de activar el registro?")){
					atcr("accesos.php","",4,cveusuario);
				}
			}

			function eliminar(cveusuario){
				if(confirm("Esta seguro de eliminar el registro permanentemente?")){
					atcr("accesos.php","",5,cveusuario);
				}
			}
			
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
			</Script>';
	}
	
bottom();





?>

