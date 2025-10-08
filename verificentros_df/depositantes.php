<?php 

include ("main.php"); 
$array_tipo = array(1=>"Pago Anticipado", 2=>"Credito");
$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM depositantes WHERE tipo_depositante=0 AND solo_contado=0 ";
		if($_POST['plaza']!="all") $select .= " AND plaza='".$_POST['plaza']."'";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		if($_POST['estatus']!="all") $select .= " AND estatus='".$_POST['estatus']."'";
		$select.=" ORDER BY nombre";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		$nivelUsuario = nivelUsuario();
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="10">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th>';
			if($_POST['plazausuario']==0) echo '<th>Plaza</th>';
			echo '<th>Nombre</th><th>Lugar</th><th>Propietario</th><th>Email</th><th>Telefonos</th><th>Pagos para Cortesia</th>
			<th>Fecha</th><th>Usuario</th>';
			if($nivelUsuario > 2) echo '<th>Inactivar</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef['nombre'].'"></a></td>';
				
				if($_POST['plazausuario']==0) echo '<td>'.$array_plaza[$row['plaza']].'</td>';
				if($row['estatus']==0){
					echo '<td>'.utf8_encode($row['nombre']).'</td>';
				}
				else
				{
					if(saldo_depositante($row['cve'])<0){
						echo '<td><font color="RED">'.utf8_encode($row['nombre']).'</font></td>';
					}
					else{
						echo '<td>'.utf8_encode($row['nombre']).'</td>';
					}
				}
				if($row['agencia']==1){
				echo '<td align="center" width="40" >Agencia</td>';	
				}else{
				echo '<td align="center" width="40" >Taller</td>';		
				}
				echo '<td>'.utf8_encode($row['propietario']).'</td>';
				echo '<td>'.utf8_encode($row['email']).'</td>';
				echo '<td>'.utf8_encode($row['telefono']).'</td>';
				echo '<td>'.utf8_encode($row['pagos_cortesia']).'</td>';
				echo '<td>'.utf8_encode($row['fecha']).'</td>';echo '<td>'.utf8_encode($array_usuario[$row['usuario']]).'</td>';
				if($nivelUsuario > 2){
					echo '<td align="center" width="40" nowrap>';
					/*$res1=mysql_query("SELECT COUNT(cve) FROM cobro_engomado WHERE plaza='".$row['plaza']."' AND depositante='".$row['cve']."'");
					$row1=mysql_fetch_array($res1);
					if($row1[0] > 0){
						echo '&nbsp;';
					}
					else{*/
						if($row['estatus']==0)
							echo '<a href="#" onClick="if(confirm(\'Esta seguro de inactivar el registro?\')) atcr(\'\',\'\',\'3\','.$row['cve'].');"><img src="images/basura.gif" border="0" title="Inactivar '.$Benef['nombre'].'"></a>';
						elseif($_POST['cveusuario']==1)
							echo '<a href="#" onClick="if(confirm(\'Esta seguro de activar el registro?\')) atcr(\'\',\'\',\'4\','.$row['cve'].');"><img src="images/validosi.gif" border="0" title="Activar '.$Benef['nombre'].'"></a>';
						else
							echo '&nbsp;';
					//}
					echo '</td>';
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


top($_SESSION);
if($_POST['cmd']==4){
	/*$res1=mysql_query("SELECT COUNT(cve) FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND depositante='".$_POST['reg']."'");
	$row1=mysql_fetch_array($res1);
	if($row1[0] > 0){*/
		$select=" UPDATE depositantes SET estatus=0 WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."' ";
		mysql_query($select);
	/*}
	else{
		$select=" DELETE FROM depositantes WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."' ";
		mysql_query($select);
	}*/
	$_POST['cmd']=0;
}
if($_POST['cmd']==3){
	/*$res1=mysql_query("SELECT COUNT(cve) FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND depositante='".$_POST['reg']."'");
	$row1=mysql_fetch_array($res1);
	if($row1[0] > 0){*/
		$select=" UPDATE depositantes SET estatus=1 WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."' ";
		mysql_query($select);
	/*}
	else{
		$select=" DELETE FROM depositantes WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."' ";
		mysql_query($select);
	}*/
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE depositantes 
						SET nombre='".$_POST['nombre']."',edo_cuenta='1',tipo='".$_POST['tipo']."',agencia='".$_POST['agencia']."',
						propietario='".$_POST['propietario']."',email='".$_POST['email']."',telefono='".$_POST['telefono']."',
						bloquear_saldo_negativo='".$_POST['bloquear_saldo_negativo']."',bloquear_vales_anterior='".$_POST['bloquear_vales_anterior']."',
						pagos_cortesia='".$_POST['pagos_cortesia']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);			
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO depositantes 
						(nombre,plaza,edo_cuenta,tipo,agencia,propietario,email,telefono,bloquear_saldo_negativo,bloquear_vales_anterior,solo_contado,fecha,usuario,pagos_cortesia)
						VALUES 
						('".$_POST['nombre']."','".$_POST['plaza']."','1','".$_POST['tipo']."','".$_POST['agencia']."',
							'".$_POST['propietario']."','".$_POST['email']."','".$_POST['telefono']."',
							'".$_POST['bloquear_saldo_negativo']."','".$_POST['bloquear_vales_anterior']."',0,'".fechaLocal()."','".$_POST['cveusuario']."','".$_POST['pagos_cortesia']."')";
			$ejecutar = mysql_query($insert);
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM depositantes WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();if(document.forma.plaza.value==\'0\'){$(\'#panel\').show(); alert(\'Necesita seleccionar la plaza\');}else{ atcr(\'depositantes.php\',\'\',\'2\',\''.$row['cve'].'\');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'depositantes.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Depositantes</td></tr>';
		echo '</table>';
		
		echo '<table>';
		if($_POST['plazausuario']==0 && $row['plaza']==0)
		{
			echo '<tr><th align="left">Plaza</th><td><select name="plaza" id="plaza"><option value="0">Seleccione</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td></tr>';
		}
		elseif($_POST['plazausuario']==0 && $row['plaza']>0){
			echo '<tr><th align="left">Plaza</th><td><b>'.$array_plaza[$row['plaza']].'</b><input type="hidden" name="plaza" id="plaza" value="'.$row['plaza'].'"></td></tr>';
		}
		else{
			echo '<tr style="display:none;"><th align="left">Plaza</th><td><b>'.$array_plaza[$_POST['plazausuario']].'</b><input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'"></td></tr>';
		}
		if($_POST['reg']==0 || $_POST['cveusuario']==1)
			echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"><small>Comenzar con apellido paterno</small></td></tr>';
		else
			echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="readOnly" size="100" value="'.$row['nombre'].'" readOnly><small>Comenzar con apellido paterno</small></td></tr>';
		echo '<tr><th align="left">Tiene Estado de Cuenta</th><td><input type="checkbox" id="edo_cuenta" name="edo_cuenta" value="1" checked disabled></td></tr>';
		echo '<tr';
		if(nivelUsuario() < 3) echo ' style="display:none;"';
		echo '><th align="left">Tipo</th><td><select name="tipo" id="tipo"><option value="0">Seleccione</option>';
		foreach($array_tipo as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row['tipo'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th align="left">Bloquear Saldo Negativo</th><td><input type="checkbox" id="bloquear_saldo_negativo" name="bloquear_saldo_negativo" value="1"';
		if($_POST['reg']==0 || $row['bloquear_saldo_negativo']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th align="left">Bloquear Vales Anteriores</th><td><input type="checkbox" id="bloquear_vales_anterior" name="bloquear_vales_anterior" value="1"';
		if($_POST['reg']==0 || $row['bloquear_vales_anterior']==1) echo ' checked';
		echo '></td></tr>';
		if($_POST['reg']==0 || $_POST['cveusuario']==1){
			echo '<tr><th align="left">Agencia</th><td><input type="checkbox" id="agencia" name="agencia" value="1"'; if($row['agencia']==1) echo ' checked'; echo '></td></tr>';
		}
		else{
			echo '<tr><th align="left">Agencia</th><td><input type="hidden"name="agencia" value="'.$row['agencia'].'"><input type="checkbox" id="agencia" value="1" disabled></td></tr>';
		}
		echo '<tr><th align="left">Propietario</th><td><input type="text" name="propietario" id="propietario" class="textField" size="100" value="'.$row['propietario'].'"></td></tr>';
		echo '<tr><th align="left">Email</th><td><input type="text" name="email" id="email" class="textField" size="100" value="'.$row['email'].'"></td></tr>';
		echo '<tr><th align="left">Telefonos</th><td><input type="text" name="telefono" id="telefono" class="textField" size="100" value="'.$row['telefono'].'"></td></tr>';
		echo '<tr';
		if(nivelUsuario()<=2) echo ' style="display:none;"';
		echo '><th align="left">Pagos para Cortesia</th><td><input type="text" name="pagos_cortesia" id="pagos_cortesia" class="textField" size="10" value="'.$row['pagos_cortesia'].'"></td></tr>';
		echo '</table>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'depositantes.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		if($_POST['plazausuario']==0){
			echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" onChange="traerCuentas()"><option value="all">Todas</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td></tr>';
		}
		else{
			echo '<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td></tr>';
		echo '<tr';
		if(nivelUsuario()<=2) echo ' style="display:none;"';
		echo '><td>Estatus</td><td><select name="estatus" id="estatus"><option value="all">Todos</option>
		<option value="0" selected>Activos</option><option value="1">Inactivos</option></select></td></tr>';
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
			objeto.open("POST","depositantes.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&estatus="+document.getElementById("estatus").value+"&plaza="+document.getElementById("plaza").value+"&nom="+document.getElementById("nom").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

