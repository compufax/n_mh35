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

$array_motivos = array();
$rsPuestos=mysql_query("SELECT * FROM motivo_justificacion ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_motivos[$Puestos['cve']]=$Puestos['nombre'];
}

$rsDepto=mysql_query("SELECT * FROM areas");
while($Depto=mysql_fetch_array($rsDepto)){
	$arreglo_departamentos[$Depto['cve']]=$Depto['nombre'];
}



mysql_select_db($base);
/*** ACTUALIZAR REGISTRO  **************************************************/

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de personal
		$select= " SELECT a.* FROM personal a  WHERE 1 ";
		if ($_POST['credencial']!="") { $select.=" AND a.cve='".$_POST['credencial']."'"; }
		if ($_POST['estatus']!="all") { $select.=" AND a.estatus='".$_POST['estatus']."'"; }
		if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
		if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; }
		if ($_POST['puesto']!="all") { $select.=" AND a.puesto='".$_POST['puesto']."'"; }
		
		$select.=" ORDER BY trim(a.nombre)";
		$rspersonal=mysql_query($select);
		$totalRegistros = mysql_num_rows($rspersonal);
		
		if(mysql_num_rows($rspersonal)>0) 
		{
			
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<thead><tr bgcolor="#E9F2F8"><td colspan="6">'.mysql_num_rows($rspersonal).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th>';
			echo '<th>Plaza</th>';
			echo '<th><a href="#" onclick="SortTable(3,\'N\',\'tabla1\');">No.</a></th>
			<th><a href="#" onclick="SortTable(4,\'S\',\'tabla1\');">Nombre</a></th><th>Estatus</th><th>Puesto</th>';
			echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'personal_justificaciones.php\',\'\',\'1\','.$Personal['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Conductor['nombre'].'"></a></td>';
				echo '<td>'.utf8_encode($array_plaza[$Personal['plaza']]).'</td>';
				echo '<td align="center">'.$Personal['cve'].'</td>';
				if(file_exists("imgpersonal/foto".$Personal['cve'].".jpg"))
					echo '<td align="left" onMouseOver="document.getElementById(\'foto'.$Personal['cve'].'\').style.visibility=\'visible\';" onMouseOut="document.getElementById(\'foto'.$Personal['cve'].'\').style.visibility=\'hidden\';">'.$Personal['nombre'].'<img width="200" id="foto'.$Personal['cve'].'" height="250" style="position:absolute;visibility:hidden" src="imgpersonal/foto'.$Personal['cve'].'.jpg?'.date('h:i:s').'" border="1"></td>';
				else
					echo '<td align="left">'.htmlentities(utf8_encode(trim($Personal['nombre']))).'</td>';
				echo '<td align="center">'.$array_estatus_personal[$Personal['estatus']].'</td>';
				echo '<td align="center">'.$array_puestos[$Personal['puesto']].'</td>';
				echo '</tr>';
			}
			
			echo '</tbody>
				<tr>
				<td colspan="6" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
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

if($_POST['ajax']==2)
{
	$res = mysql_query("SELECT * FROM dias_justificados WHERE personal = '".$_POST['personal']."' AND fecha = '".$_POST['fecha']."'");
	if($row=mysql_fetch_array($res)){
		mysql_query("UPDATE dias_justificados SET motivo='".$_POST['motivo']."',
			fecharegistro = '".fechaLocal()." ".horaLocal()."', usuario='".$_POST['cveusuario']."', estatus=0,
			cambios=CONCAT(cambios,'|".fechaLocal()." ".horaLocal().",".$_POST['cveusuario'].",".$_POST['motivo']."')
			WHERE cve='".$row['cve']."'");
	}
	else{
		mysql_query("INSERT dias_justificados SET fecha='".$_POST['fecha']."',personal='".$_POST['personal']."',motivo='".$_POST['motivo']."',
			fecharegistro = '".fechaLocal()." ".horaLocal()."', usuario='".$_POST['cveusuario']."',estatus=0,
			cambios='".fechaLocal()." ".horaLocal().",".$_POST['cveusuario'].",".$_POST['motivo']."'");
	}
	exit();
}



top($_SESSION);

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM personal WHERE cve='".$_POST['reg']."' ";
		$rspersonal=mysql_query($select);
		$Personal=mysql_fetch_array($rspersonal);
		//Menu
		echo '<table>';
		echo '
			<tr>';
			
			echo '<td><a href="#" onClick="atcr(\'personal_justificaciones.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Justifiaciones del empleado '.$Personal['nombre'].'</td></tr>';
		echo '</table>';

		$nivel = nivelUsuario();
		
		if($_POST['mes']=="") $_POST['mes']=date("Y-m");
		$fecha = $_POST['mes'];
	
		echo '<input type="hidden" name="mes" id="mes" value="'.$_POST['mes'].'">';
		echo '<h1 style="font-size:20px">';
		$datos = explode("-",$_POST['mes']);
		echo $array_meses[intval($datos[1])].' '.$datos[0].'</h2>';
		echo '<table width="100%"><td width="50%" align="left"><input type="button" value="Anterior" style="font-size:20px" onClick="document.forma.mes.value=\''.date( "Y-m" , strtotime ( "-1 month" , strtotime($fecha) ) ).'\';atcr(\'personal_justificaciones.php\',\'\',1,'.$_POST['reg'].');"></td>
		<td width="50%" align="right"><input type="button" value="Siguiente" style="font-size:20px" onClick="document.forma.mes.value=\''.date( "Y-m" , strtotime ( "+ 1 month" , strtotime($fecha) ) ).'\';atcr(\'personal_justificaciones.php\',\'\',1,'.$_POST['reg'].');"></td></tr></table>';
		echo '<table style="font-size:20px" width="100%" border="1">
		<tr bgcolor="#E9F2F8"><th>Domingo</th><th>Lunes</th><th>Martes</th><th>Miercoles</th><th>Jueves</th><th>Viernes</th><th>Sabado</th></tr>';
		$sumren=0;
		$arfecha=explode("-",$fecha);
		$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval(1), $arfecha[0]));
		for($i=0;$i<$dia;$i++){
			if($i==0) echo '<tr>';
			echo '<td align="center"><br><br>&nbsp;</td>';
		}
		$fec=$fecha.'-01';
		
		$fecultima=date( "Y-m-t" , strtotime ( "+ 1 day" , strtotime($fec) ) );
		
		$array_fechas_justificacion = array();
		$res=mysql_query("SELECT * FROM dias_justificados WHERE personal='".$_POST['reg']."' AND fecha BETWEEN '".date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime(date("Y-m-d")) ) )."' AND '".date( "Y-m-d" , strtotime ( "+ ".$diasusuario." day" , strtotime(date("Y-m-d")) ) )."' GROUP BY fecha");
		while($row=mysql_fetch_array($res)){
			$array_fechas_justificacion[$row['fecha']] = $row['motivo'];
		}

		for($i=$dia;$fec<=$fecultima;$i++){
			$arfecha=explode("-",$fec);
			$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
			if($i==7){
				echo '</tr>';
				$i=0;
			}
			if($i==0){
				echo '<tr>';
			}
			if($fec<=date("Y-m-d") || $nivel<2 || $Personal['estatus']!=1 || $dia==0){
				echo '<td align="center" valign="center" bgcolor="#CCCCCC"><table width="100%"><tr><td width="100%" align="center">'.substr($fec,8,2).'</td></tr><tr><td style="font-size:10px" align="center">'.$array_motivos[$array_fechas_justificacion[$fec]].'</td></tr></table></td>';
			}
			else{
				echo '<td align="center" valign="center" bgcolor="#FFFFFF"><table width="100%"><tr><td width="100%" align="center">'.substr($fec,8,2).'</td></tr>
				<tr><td style="font-size:10px" align="center"><select id="motivo_'.substr($fec,8,2).'"><option value="0">Seleccione</option>';
				foreach($array_motivos as $k=>$v){
					echo '<option value="'.$k.'"';
					if($k==$array_fechas_justificacion[$fec]) echo ' selected';
					echo '>'.$v.'</option>';
				}
				echo '</select><br><input type="button" value="Guardar" onClick="justificar(\''.$fec.'\',\''.substr($fec,8,2).'\')"></td></tr></table></td>';
			}
			$fec=date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fec) ) );
		}
		$arfecha=explode("-",$fecultima);
		$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
		for($i=$dia;$i<6;$i++){
			echo '<td align="center"><br><br>&nbsp;</td>';
		}
		echo '</tr>';
		echo '</table>';

		echo '<script>
			function justificar(fecha, dia){
				if(confirm("Esta seguro de guardar el cambio?")){
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto.open("POST","personal_justificaciones.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=2&fecha="+fecha+"&motivo="+document.getElementById("motivo_"+dia).value+"&personal='.$_POST['reg'].'&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
						objeto.onreadystatechange = function()
						{
							if (objeto.readyState==4)
							{alert("El cambio se guardo correctamente!")}
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
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" class="textField">';
		if($_POST['plazausuario']==0)
			echo '<option value="all">---Todas---</option>';
		foreach($array_plaza as $k=>$v){
			if($_POST['plazausuario']==0 || $_POST['plazausuario']==$k)
				echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td><td></td><td>&nbsp;</td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus" class="textField"><option value="all">---Todos---</option>';
		foreach($array_estatus_personal as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==1) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td><td></td><td>&nbsp;</td></tr>';
		echo '<tr><td>Credencia</td><td><input type="text" name="credencial" id="credencial" class="textField"></td></tr>'; 
		echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" class="textField"></td></tr>'; 
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
			objeto.open("POST","personal_justificaciones.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&credencial="+document.getElementById("credencial").value+"&nombre="+document.getElementById("nombre").value+"&puesto="+document.getElementById("puesto").value+"&estatus="+document.getElementById("estatus").value+"&plaza="+document.getElementById("plaza").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
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
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	';
	}
	echo '
	
	</Script>
';
}
	
bottom();

?>

