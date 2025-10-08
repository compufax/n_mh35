<?php 

include ("main.php"); 

/*** ARREGLOS ***********************************************************/

$rsPlaza=mysql_query("SELECT * FROM plazas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['numero'];
}

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsPuestos=mysql_query("SELECT * FROM puestos ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_puestos[$Puestos['cve']]=$Puestos['nombre'];
}

$rsDepto=mysql_query("SELECT * FROM areas");
while($Depto=mysql_fetch_array($rsDepto)){
	$arreglo_departamentos[$Depto['cve']]=$Depto['nombre'];
}

$res=mysql_db_query("nomina","SELECT * FROM bancos ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_banco[$row['cve']]=$row['nombre'];
}
$res=mysql_db_query("nomina","SELECT * FROM tipo_contrato ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_contrato[$row['cve']]=$row['nombre'];
}
$res=mysql_db_query("nomina","SELECT * FROM tipo_jornada ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_jornada[$row['cve']]=$row['nombre'];
}
$res=mysql_db_query("nomina","SELECT * FROM tipo_regimen ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_regimen[$row['cve']]=$row['nombre'];
}
$res=mysql_db_query("nomina","SELECT * FROM metodo_pago ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}
$res=mysql_db_query("nomina","SELECT * FROM departamentos WHERE empresa='$empresanomina' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_departamentos_nomina[$row['cveaux']]=$row['cve'];
}
$res=mysql_db_query("nomina","SELECT * FROM personal_puestos WHERE empresa='$empresanomina' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_puestos_nomina[$row['cveaux']]=$row['cve'];
}
$res=mysql_db_query("nomina","SELECT * FROM plazas WHERE empresa='$empresanomina' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_plazas_nomina[$row['cveaux']]=$row['cve'];
}
mysql_select_db($base);
/*** ACTUALIZAR REGISTRO  **************************************************/



/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de personal
		
		$select= " SELECT * FROM personal WHERE clave_ecologica>0 AND estatus_eco = 0";
		if ($_POST['nombre']!="") { $select.=" AND nombre LIKE '%".$_POST['nombre']."%'"; }
		if ($_POST['num']!="") { $select.=" AND clave_ecologica='".$_POST['num']."'"; }
		if ($_POST['plaza']!="all") { $select.=" AND plaza='".$_POST['plaza']."'"; }
		if ($_POST['puesto']!="all") { $select.=" AND puesto='".$_POST['puesto']."'"; }
		$select.=" ORDER BY trim(nombre)";
		$rspersonal=mysql_query($select);
		$totalRegistros = mysql_num_rows($rspersonal);
		
		if(mysql_num_rows($rspersonal)>0) 
		{
			
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<thead>';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
			echo '<th>Nombre</th><th>Puesto</th><th>Clave</th><th>RFC</th><th>Centro</th><th>Fecha</th><th>Fecha Puesto</th><th>Observaciones</th></tr>';
			echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
				if($Personal['estatus']==2)
					echo '<tr bgcolor="#FF0000">';
				else
					rowb();
				echo '<td align="center" width="40" nowrap>
				<a href="#" onClick="atcr(\'ecologia.php\',\'\',\'1\','.$Personal['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Conductor['nombre'].'"></a>';
				if($Personal['estatus']==2) echo '<a href="#" onClick="if(confirm(\'Esta seguro de dar de baja al empleado?\'))atcr(\'ecologia.php\',\'\',\'3\','.$Personal['cve'].')"><img src="images/validono.gif" border="0" title="Dar de Baja '.$Conductor['nombre'].'"></a>';
				echo '</td>';
				if(file_exists("imgpersonal/foto".$Personal['cve'].".jpg"))
					echo '<td align="left" onMouseOver="document.getElementById(\'foto'.$Personal['cve'].'\').style.visibility=\'visible\';" onMouseOut="document.getElementById(\'foto'.$Personal['cve'].'\').style.visibility=\'hidden\';">'.$Personal['nombre'].'<img width="200" id="foto'.$Personal['cve'].'" height="250" style="position:absolute;visibility:hidden" src="imgpersonal/foto'.$Personal['cve'].'.jpg?'.date('h:i:s').'" border="1"></td>';
				else
					echo '<td align="left">'.htmlentities(trim($Personal['nombre'])).'</td>';
				echo '<td align="left">'.$array_puestos[$Personal['puesto']].'</td>';
				echo '<td align="center">'.$Personal['clave_ecologica'].'</td>';
				echo '<td align="center">'.$Personal['rfc'].'</td>';
				echo '<td>'.htmlentities($array_plaza[$Personal['plaza']]).'</td>';
				echo '<td align="center">'.$Personal['fecha_eco'].'</td>';
				echo '<td align="center">'.$Personal['fecha_puesto'].'</td>';
				echo '<td align="left">'.$Personal['obs_eco'].'</td>';
				
				echo '</tr>';
			}
			
			echo '</tbody>
				<tr>
				<td colspan="14" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
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

	

if($_POST['ajax']==3) {
		//Listado de Historial
		$select= " SELECT * FROM cambios_datos_personal WHERE cve_personal='".$_POST['personal']."' AND dato in ('Puesto','Plaza','Estatus Ecologia')";
		$rscambios=mysql_query($select);
		$totalRegistros = mysql_num_rows($rscambios);
		if($totalRegistros / $eRegistrosPagina > 1) 
		{
			$eTotalPaginas = $totalRegistros / $eRegistrosPagina;
			if(is_int($eTotalPaginas))
			{$eTotalPaginas--;}
			else
			{$eTotalPaginas = floor($eTotalPaginas);}
		}
		$select .= " ORDER BY cve desc  LIMIT ".$primerRegistro.",".$eRegistrosPagina;
		$rscambios=mysql_query($select) or die(mysql_error() . $select);
		
		if(mysql_num_rows($rscambios)>0) 
		{
		
			echo '<h3 align="center"> Historial de Cambios </h3>';
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Fecha Mov</th><th>Folio</th>';
			echo '<th>Dato</th><th>Valor Nuevo</th><th>Valor Anterior</th><th>Fecha</th><th>Usuario</th>';
			echo '</tr>';
			$i=0;
			while($Cambios=mysql_fetch_array($rscambios)) {
				rowb();
		//		echo '<td align="center" width="40" nowrap><a href="#" onClick="document.forma.regcve_unidad.value=\''.$Cambios['cve_unidad'].'\';document.forma.regplaza.value=\''.$Cambios['plaza'].'\';atcr(\'parque.php\',\'\',\'1\','.$Cambios['cve'].')">'.$Cambios['folio'].'</a></td>';
				echo '<td align="center">'.$Cambios['fecha_reg'].'</td>';
				echo '<td align="center">'.$Cambios['folio'].'</td>';
				echo '<td align="left">'.htmlentities($Cambios['dato']).'</td>';
				if($Cambios['dato']=="Estatus"){
					echo '<td align="left">'.$array_estatus_personal[$Cambios['valor_nuevo']].'</td>';
					echo '<td align="left">'.$array_estatus_personal[$Cambios['valor_anterior']].'</td>';
				}else{
					if($Cambios['dato']=="Plaza"){
						echo '<td align="left">'.$array_plaza[$Cambios['valor_nuevo']].'</td>';
						echo '<td align="left">'.$array_plaza[$Cambios['valor_anterior']].'</td>';
					}
					else{
						if($Cambios['dato']=="Tipo Conductor"){
							echo '<td align="left">'.$array_tipo_conductor[$Cambios['valor_nuevo']].'</td>';
							echo '<td align="left">'.$array_tipo_conductor[$Cambios['valor_anterior']].'</td>';
						}else{
							if($Cambios['dato']=="Puesto"){
								echo '<td align="left">'.$array_puestos[$Cambios['valor_nuevo']].'</td>';
								echo '<td align="left">'.$array_puestos[$Cambios['valor_anterior']].'</td>';
							}else{
								if($Cambios['dato']=="Unidad"){
									$rsparque_nuevo=mysql_query("SELECT * FROM parque WHERE cve='".$Cambios['valor_nuevo']."'");
									$Parque_nuevo=mysql_fetch_array($rsparque_nuevo);
									$rsparque_anterior=mysql_query("SELECT * FROM parque WHERE cve='".$Cambios['valor_anterior']."'");
									$Parque_anterior=mysql_fetch_array($rsparque_anterior);
									echo '<td align="left">'.$Parque_nuevo['no_eco'].' - '.$array_tipo_vehiculo[$Parque_nuevo['tipo_vehiculo']].'</td>';
									echo '<td align="left">'.$Parque_anterior['no_eco'].' - '.$array_tipo_vehiculo[$Parque_anterior['tipo_vehiculo']].'</td>';
								}else{
									echo '<td align="left">'.$Cambios['valor_nuevo'].'</td>';
									echo '<td align="left">'.$Cambios['valor_anterior'].'</td>';
								}
							}
						}
					}
				}	
				echo '<td align="center">'.$Cambios['fecha'].'</td>';
				echo '<td align="left">'.$array_usuario[$Cambios['usuario']].'';
				$i++;
				echo '</tr>';
			}
			
			echo '	
				<tr>
				<td colspan="9" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
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

	if($_POST['cmd']==3){
		$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Estatus Ecologia' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
		$Folio=mysql_fetch_array($rsfolio);
		$insert_infonavit="	INSERT cambios_datos_personal
					SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Estatus Ecologia',fecha_reg='".fechaLocal()."',
					valor_anterior='Alta',valor_nuevo='Baja',
					cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
					observaciones='".$_POST['obs']."'";
		$ejecutar_estatus=mysql_query($insert_infonavit);
		mysql_query("UPDATE personal SET estatus_eco = 2, fecha_sta_eco='".fechaLocal()."' WHERE cve='".$_POST['reg']."'");
		$_POST['cmd']=0;
	}

	if($_POST['cmd']==2){
		
		$select=" SELECT * FROM personal WHERE cve='".$_POST['reg']."' ";
		$rspersonal=mysql_query($select);
		$Personal=mysql_fetch_array($rspersonal);
		if($Personal['plaza']!=$_POST['plaza']){
			$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Plaza' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_infonavit="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Plaza',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['plaza']."',valor_nuevo='".$_POST['plaza']."',
							cve_personal='".$_POST['reg']."',fecha='".$_POST['fecha_eco']."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_infonavit);		
		}
		
		if($Personal['puesto']!=$_POST['puesto']){
			$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Puesto' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_infonavit="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Puesto',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['puesto']."',valor_nuevo='".$_POST['puesto']."',
							cve_personal='".$_POST['reg']."',fecha='".$_POST['fecha_puesto']."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_infonavit);		
		}
		
		
		mysql_query("UPDATE personal SET fecha_eco = '".$_POST['fecha_eco']."',fecha_puesto='".$_POST['fecha_puesto']."',
		puesto='".$_POST['puesto']."',plaza='".$_POST['plaza']."',obs_eco='".$_POST['obs']."' WHERE cve='".$_POST['reg']."'");
		
		$_POST['cmd']=0;
	}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		echo '<style>
		#Cambios {
			width: 70%;
			border-style: solid;
			border-width: 1px;
			border-color: #96BDE0;
		}
		</style>';
		$select=" SELECT * FROM personal WHERE cve='".$_POST['reg']."' ";
		$rspersonal=mysql_query($select);
		$Personal=mysql_fetch_array($rspersonal);
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1){
				echo '<td><a href="#" onClick="atcr(\'ecologia.php\',\'\',2,\''.$_POST['reg'].'\');"
				><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			
			}
			echo '<td><a href="#" onClick="atcr(\'ecologia.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Catálogo de Personal</td></tr>';
		echo '</table>';
		echo '<table width="100%"><tr><td>';
		echo '<table>';
		echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="50" value="'.$Personal['nombre'].'"></td></tr>';
		echo '<tr><th align="left">RFC</th><td><input type="text" name="rfc" id="rfc" class="textField" size="25" value="'.$Personal['rfc'].'" ></td></tr>';
		
		echo '<tr><th align="left">Centro</th><td><select name="plaza" id="plaza">';
		$rsPlaza=mysql_query("SELECT * FROM plazas ORDER BY nombre");
		while($Plaza=mysql_fetch_array($rsPlaza)){
			echo '<option value="'.$Plaza['cve'].'"';
			if($Plaza['cve']==$Personal['plaza']) echo ' selected';
			echo '>'.$Plaza['numero'].'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr class="recologia"';
		echo '><th align="left">Clave Ecologica</th><td><input type="text" name="clave_ecologica" id="clave_ecologica" class="readOnly" size="30" value="'.$Personal['clave_ecologica'].'" readOnly></td></tr>';
		if($Personal['fecha_eco']=="0000-00-00") $Personal['fecha_eco']="";
		echo '<tr><th align="left">Fecha Cambio Ecologia</th><td><input type="text" name="fecha_eco" id="fecha_eco"  size="15" value="'.$Personal['fecha_eco'].'" class="readOnly" readonly>';
		echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_eco,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
		echo '<tr><th align="left">Puesto</th><td><select name="puesto" id="puesto" class="textField">';
		foreach($array_puestos as $k=>$v){
				echo '<option value="'.$k.'"';
				if($Personal['puesto']==$k) echo ' selected';
				echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		if($Personal['fecha_puesto']=="0000-00-00") $Personal['fecha_puesto']="";
		echo '<tr><th align="left">Fecha Cambio de Puesto</th><td><input type="text" name="fecha_puesto" id="fecha_puesto"  size="15" value="'.$Personal['fecha_puesto'].'" class="readOnly" readonly>';
		echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_puesto,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
		
		echo '<tr><th align="left">Observaciones</th><td><textarea class="textField" name="obs" id="obs" rows="5" cols="50">'.$Personal['obs'].'</textarea></td></tr>';
		echo '</table>';
		echo '</td><td valign="top">';
		echo '<table align="right"><tr><td colspan="2" align="center"><img width="200" height="250" src="imgpersonal/foto'.$_POST['reg'].'.jpg?'.date('h:i:s').'" border="1"></td></tr>';
		echo '</table>';
		echo '</td></tr></table>';
		echo '<BR>';
		echo '<div id="Cambios">';
		echo '</div>';
	//	echo '<input type="hidden" name="regplaza" id="plaza" value="'.$_SESSION['PlazaUsuario'].'">';
	//	echo '<input type="hidden" name="regunidad" id="unidad" value="">';
		
		echo '<script language="javascript">
				function cambiospersonal()
					{
						document.getElementById("Cambios").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
						objeto=crearObjeto();
						if (objeto.readyState != 0) {
							alert("Error: El Navegador no soporta AJAX");
						} else {
							objeto.open("POST","ecologia.php",true);
							objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
							objeto.send("ajax=3&personal='.$_POST['reg'].'&plazausuario='.$_POST['plazausuario'].'&numeroPagina="+document.getElementById("numeroPagina").value);
							objeto.onreadystatechange = function()
							{
								if (objeto.readyState==4)
									{document.getElementById("Cambios").innerHTML = objeto.responseText;}
							}
						}
						document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
					}
				function moverPagina(x) {
					document.getElementById("numeroPagina").value = x;
					cambiospersonal();
				}	
				
				
				cambiospersonal()
				  </script>'; 
			
		
	}
	

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Centro</td><td><select name="plaza" id="plaza" class="textField"><option value="all">---Todas---</option>';
		foreach($array_plaza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td><td></td><td>&nbsp;</td></tr>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" class="textField"></td></tr>'; 
		echo '<tr><td>Clave Ecologica</td><td><input type="text" name="num" id="num" class="textField"></td></tr>'; 
		echo '<tr><td>Puesto</td><td><select name="puesto" id="puesto" class="textField"><option value="all">---Todos---</option>';
		foreach($array_puestos as $k=>$v){
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

	function buscarRegistros(ordenamiento,orden)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","ecologia.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nombre="+document.getElementById("nombre").value+"&puesto="+document.getElementById("puesto").value+"&num="+document.getElementById("num").value+"&plaza="+document.getElementById("plaza").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
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
	/*if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}*/
	echo '
	
	</Script>
';

?>

