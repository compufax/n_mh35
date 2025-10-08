<?php 

include ("main.php"); 
$array_tipo = array(1=>"Pago Anticipado", 2=>"Credito");
$res = mysql_query("SELECT * FROM cat_plantillas WHERE 1 order by nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_plantilla[$row['cve']]=$row['nombre']; 
	}
$rsDepto=mysql_query("SELECT * FROM tipo_plaza");
while($Depto=mysql_fetch_array($rsDepto)){
	$array_tipo_plaza[$Depto['cve']]=$Depto['nombre'];
}
$rsUsuario=mysql_query("SELECT * FROM plazas where estatus!='I' ORDER BY numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM plantilla WHERE 1 ";
		if($_POST['plaza']!="all") $select .= " AND plaza='".$_POST['plaza']."'";
		if($_POST['plantilla']!="all") $select .= " AND plantilla='".$_POST['plantilla']."'";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
//		if($_POST['estatus']!="all") $select .= " AND estatus='".$_POST['estatus']."'";
		$select.=" ORDER BY nombre";
	//	echo''.$select.'';
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		$nivelUsuario = nivelUsuario();
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th></th>';
			if($_POST['plazausuario']==0) echo '<th>Plaza</th>';
			echo '<th>Folio</th><th>Fecha</th><th>Nombre</th><th>Usuario</th><th>Tipo Plaza</th><th>Plantilla</th>';
			//if($nivelUsuario > 2) echo '<th>Inactivar</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				if($_POST['cveusuario']==1){
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef['nombre'].'"></a></td>';
				}else{
					echo'<td></td>';
				}
				
				if($_POST['plazausuario']==0) echo '<td>'.$array_plaza[$row['plaza']].'</td>';
				echo '<td>'.utf8_encode($row['folio']).'</td>';
				echo '<td>'.utf8_encode($row['fecha']).'  '.$row['hora'].'</td>';
				echo '<td>'.utf8_encode(utf8_encode($row['nombre'])).'</td>';
				echo '<td>'.utf8_encode(utf8_encode($row['usuario'])).'</td>';
				echo '<td>'.utf8_encode($array_tipo_plaza[$row['tipo_plaza']]).'</td>';
				echo '<td>'.utf8_encode($array_plantilla[$row['plantilla']]).'</td>';
//				if($nivelUsuario > 2){
				//	echo '<td align="center" width="40" nowrap>';
					/*$res1=mysql_query("SELECT COUNT(cve) FROM cobro_engomado WHERE plaza='".$row['plaza']."' AND depositante='".$row['cve']."'");
					$row1=mysql_fetch_array($res1);
					if($row1[0] > 0){
						echo '&nbsp;';
					}
					else{*/
	//					if($row['estatus']==0)
	//						echo '<a href="#" onClick="if(confirm(\'Esta seguro de inactivar el registro?\')) atcr(\'\',\'\',\'3\','.$row['cve'].');"><img src="images/basura.gif" border="0" title="Inactivar '.$Benef['nombre'].'"></a>';
	//					elseif($_POST['cveusuario']==1)
	//						echo '<a href="#" onClick="if(confirm(\'Esta seguro de activar el registro?\')) atcr(\'\',\'\',\'4\','.$row['cve'].');"><img src="images/validosi.gif" border="0" title="Activar '.$Benef['nombre'].'"></a>';
						//else
							//echo '&nbsp;';
					//}
				//	echo '</td>';
				//}
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
/*if($_POST['cmd']==4){
	/*$res1=mysql_query("SELECT COUNT(cve) FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND depositante='".$_POST['reg']."'");
	$row1=mysql_fetch_array($res1);
	if($row1[0] > 0){*/
//		$select=" UPDATE deposi SET estatus=0 WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."' ";
//		mysql_query($select);
	/*}
	else{
		$select=" DELETE FROM plantilla WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."' ";
		mysql_query($select);
	}*/
	//$_POST['cmd']=0;
//}
/*if($_POST['cmd']==-3){
	/*$res1=mysql_query("SELECT COUNT(cve) FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND depositante='".$_POST['reg']."'");
	$row1=mysql_fetch_array($res1);
	if($row1[0] > 0){
		$select=" UPDATE deposit SET estatus=1 WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."' ";
		mysql_query($select);
	/*}
	else{
		$select=" DELETE FROM plantilla WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."' ";
		mysql_query($select);
	}*/
//	$_POST['cmd']=0;
//}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			$select=" SELECT * FROM plantilla WHERE cve='".$_POST['reg']."' ";
		$rssuario=mysql_query($select);
		$Usuario=mysql_fetch_array($rssuario);
		if($Usuario['plantilla']!=$_POST['plantilla']){
			mysql_query("INSERT plantilla_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Plantilla',nuevo='".$_POST['plantilla']."',anterior='".$Usuario['plantilla']."',arreglo='plantilla',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['tipo_plaza']!=$_POST['tipo_plaza']){
			mysql_query("INSERT plantilla_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Tipo plaza',nuevo='".$_POST['tipo_plaza']."',anterior='".$Usuario['tipo_plaza']."',arreglo='plazas',usuario='".$_POST['cveusuario']."'");
		}
			//Actualizar el Registro
			$update = " UPDATE plantilla 
						SET tipo_plaza='".$_POST['tipo_plaza']."',plantilla='".$_POST['plantilla']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);			
	} else {
			//Insertar el Registro
			$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM plantilla WHERE plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
			$insert = " INSERT plantilla 
						SET folio='".$Folio[0]."',fecha='".fechaLocal()."',hora='".horaLocal()."',plaza='".$_POST['plaza']."',
						nombre='".$_POST['nombre']."',usuario='".$_POST['usuario']."',tipo_plaza='".$_POST['tipo_plaza']."',plantilla='".$_POST['plantilla']."',
						usu='".$_POST['cveusuario']."'";
			$ejecutar = mysql_query($insert);
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM plantilla WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="if(document.forma.plaza.value==\'0\'){ alert(\'Necesita seleccionar la plaza\');}else{ atcr(\'plantilla.php\',\'\',\'2\',\''.$row['cve'].'\');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'plantilla.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Plantilla</td></tr>';
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
			echo '<tr><th align="left">Usuario</th><td><input type="text" name="usuario" id="usuario" class="textField" size="100" value="'.$row['usuario'].'"><small></small></td></tr>';
		else
			echo '<tr><th align="left">Usuario</th><td><input type="text" name="usuario" id="usuario" class="readOnly" size="100" value="'.$row['usuario'].'" readOnly><small></small></td></tr>';
		if($_POST['reg']==0 || $_POST['cveusuario']==1)
			echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"><small>Comenzar con el nombre</small></td></tr>';
		else
			echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="readOnly" size="100" value="'.$row['nombre'].'" readOnly><small>Comenzar con el nombre</small></td></tr>';

		echo '<tr';
//		if(nivelUsuario() < 3) echo ' style="display:none;"';
		echo '><th align="left">Tipo Plaza</th><td><select name="tipo_plaza" id="tipo_plaza"><option value="0">Seleccione</option>';
		foreach($array_tipo_plaza as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row['tipo_plaza'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr';
//		if(nivelUsuario() < 3) echo ' style="display:none;"';
		echo '><th align="left">Plantilla</th><td><select name="plantilla" id="plantilla"><option value="0">Seleccione</option>';
		foreach($array_plantilla as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row['plantilla'] == $k) echo ' selected';
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
				<td><a href="#" onClick="atcr(\'plantilla.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		if($_POST['plazausuario']==0){
			echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" onChange=""><option value="all">Todas</option>';
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
		echo '<tr><td>Plantilla</td><td><select name="plantilla" id="plantilla" onChange=""><option value="all">Todas</option>';
			foreach($array_plantilla as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td></tr>';
		echo '<tr';
		/*if(nivelUsuario()<=2)*/ echo ' style="display:none;"';
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
			objeto.open("POST","plantilla.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&estatus="+document.getElementById("estatus").value+"&plaza="+document.getElementById("plaza").value+"&nom="+document.getElementById("nom").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plantilla="+document.getElementById("plantilla").value);
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

