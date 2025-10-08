<?php
include ("main.php"); 
$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}
$array_supervisores = array();
$res = mysql_query("SELECT * FROM mantenimiento_supervisores ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_supervisores[$row['cve']] = $row['nombre'];
}

$array_plaza=array();
$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = 2 ORDER BY a.numero");
while($row=mysql_fetch_array($res)){
	$array_plaza[$row['cve']]=$row['numero'].' '.$row['nombre'];
}

if($_POST['cmd']==101){
		
	$select=" SELECT * FROM mantenimiento_supervisiones WHERE cve='".$_POST['reg']."' ";
	$res=mysql_query($select);
	$row=mysql_fetch_array($res);			
	
	//Menu
	
	//Formulario 
	echo '<h1>Supervision Folio '.$_POST['reg'].'</h1>';
	
	echo '<table>';
	echo '<tr><th align="left">Plaza</th><td>'.$array_plaza[$row['plaza']].'</td></tr>';
	echo '<tr><th align="left">Fecha</th><td>'.$row['fecha'].'</td></tr>';
	echo '<tr><th align="left">Fecha Visita</th><td>'.$row['fecha_visita'].'</td></tr>';
	echo '<tr><th align="left">Supervisor</th><td>'.$array_supervisores[$row['supervisor']].'</td></tr>';
	echo '</table>';

	echo '<table width="100%" border="1"><tr><th>Nombre</th><th>Observaciones</th></tr>';
	$res1=mysql_query("SELECT a.cve, a.nombre, b.obs FROM mantenimiento_nombres a LEFT JOIN mantenimiento_supervisiones_nombres b ON a.cve = b.nombre AND b.supervision = '".$_POST['reg']."' WHERE 1 ORDER BY a.nombre");
	while($row1 = mysql_fetch_array($res1)){
		rowc();
		echo '<td>'.$row1['nombre'].'</td>';
		echo '<td align="left">'.$row1['obs'].'</td>'; 
		echo '</tr>';
	}
	echo '</table>';
	echo '<br><br><br><table align="center"><tr><td align="center">___________________________________</td></tr><tr><td align="center">'.$array_supervisores[$row['supervisor']].'</td></tr></table>';
	
	exit();	
}

if($_POST['ajax']==1){
	$plazas_ids = "";
	foreach($array_plaza as $k=>$v){
		$plazas_ids.=",".$k;
	}
	$plazas_ids=substr($plazas_ids,1);
	$select = "SELECT * FROM mantenimiento_supervisiones WHERE plaza IN (".$plazas_ids.") AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	if($_POST['plaza']!="all") $select .= " AND plaza='".$_POST['plaza']."'";
	if($_POST['supervisor']!="all") $select .= " AND supervisor='".$_POST['supervisor']."'";
	
	$select.=" ORDER BY cve DESC";
	$res=mysql_query($select);
	if(mysql_num_rows($res)>0) 
	{
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		echo '<th>Folio</th>';
		echo '<th>Plaza</th><th>Fecha</th><th>Fecha Visita</th>
		<th>Supervisor</th><th>Usuario</th>';
		echo '</tr>';
		$t=0;
		while($row=mysql_fetch_array($res)) {
			rowb();
			echo '<td align="center" width="40" nowrap>';
			if($row['estatus']=='C'){
				echo 'Cancelado';
			}
			else{
				echo '<a href="#" onClick="atcr(\'supervisiones.php\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$row['cve'].'"></a>';
				echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'supervisiones.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
				if(nivelUsuario()>1)
					echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar la supervision\')) atcr(\'supervisiones.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
			}	
			echo '</td>';
			echo '<td align="center">'.htmlentities($row['cve']).'</td>';
			echo '<td>'.$array_plaza[$row['plaza']].'</td>';
			echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
			echo '<td align="center">'.htmlentities($row['fecha_visita']).'</td>';
			echo '<td align="left">'.htmlentities(utf8_encode($array_supervisores[$row['supervisor']])).'</td>';
			echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
			echo '</tr>';
			$t+=$row['monto'];
		}
		$c=8;
		if($_POST['plazausuario']==0) ++$c;
		echo '	
			<tr>
			<td colspan="'.$c.'" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
	mysql_query("UPDATE mantenimiento_supervisiones SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {

	if($_POST['reg'] > 0){
		$update = " UPDATE mantenimiento_supervisiones 
					SET 
					plaza = '".$_POST['plaza']."',fecha_visita='".$_POST['fecha_visita']."',
					supervisor='".$_POST['supervisor']."',estatus='A'
					WHERE cve = '".$_POST['reg']."'";
	
		$id = $_POST['reg'];
		mysql_query($update);
		mysql_query("DELETE FROM mantenimiento_supervisiones_nombres WHERE supervision = '$id'");
	}
	else{
		$insert = " INSERT mantenimiento_supervisiones 
					SET 
					plaza = '".$_POST['plaza']."',fecha='".fechaLocal()."',hora='".horaLocal()."',fecha_visita='".$_POST['fecha_visita']."',
					supervisor='".$_POST['supervisor']."',
					usuario='".$_POST['cveusuario']."',estatus='A'";
		mysql_query($insert);
		$id = mysql_insert_id();
	}


	foreach($_POST['obsd'] as $k=>$v){
		mysql_query("INSERT mantenimiento_supervisiones_nombres SET supervision = '$id', nombre = '$k', obs = '$v'");
	}
	$_POST['cmd']=0;
}


if ($_POST['cmd']==1) {
		
	$select=" SELECT * FROM mantenimiento_supervisiones WHERE cve='".$_POST['reg']."' ";
	$res=mysql_query($select);
	$row=mysql_fetch_array($res);			
	
	//Menu
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();validar('.$_POST['reg'].');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'supervisiones.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Supervision</td></tr>';
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
	echo '<tr><th align="left">Fecha Visita</th><td><input type="text" name="fecha_visita" id="fecha_visita" class="readOnly" size="12" value="'.$row['fecha_visita'].'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_visita,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><th align="left">Supervisor</th><td><select name="supervisor" id="supervisor"><option value="0">Seleccione</option>';
	foreach($array_supervisores as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$row['supervisor']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';

	echo '<table width="100%"><tr><th>Nombre</th><th>Observaciones</th></tr>';
	$res1=mysql_query("SELECT a.cve, a.nombre, b.obs FROM mantenimiento_nombres a LEFT JOIN mantenimiento_supervisiones_nombres b ON a.cve = b.nombre AND b.supervision = '".$_POST['reg']."' WHERE 1 ORDER BY a.nombre");
	while($row1 = mysql_fetch_array($res1)){
		rowc();
		echo '<td>'.$row1['nombre'].'</td>';
		echo '<td align="center"><textarea name="obsd['.$row1['cve'].']" id="obsd_'.$row1['cve'].'" cols="70" rows="3">'.$row1['obs'].'</textarea></td>'; 
		echo '</tr>';
	}
	echo '</table>';
	
	echo '<script>
			function validar(reg){
				if(document.forma.plaza.value=="0"){
					$("#panel").hide();
					alert("Necesita seleccionar una plaza");
				}
				else if(document.forma.fecha_visita.value==""){
					$("#panel").hide();
					alert("Necesita seleccionar una fecha de visita");
				}
				else if(document.forma.supervisor.value=="0"){
					$("#panel").hide();
					alert("Necesita seleccionar el supervisor");
				}
				else{
					atcr("supervisiones.php","",2,reg);
				}
			}
			
			
		</script>';
	
}

if($_POST['cmd']==0){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'supervisiones.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.substr(fechaLocal(),0,8).'01" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	if($_POST['plazausuario']==0){
		echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza"><option value="all">Todas</option>';
		foreach($array_plaza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
	}
	else{
		echo '<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
	}
	echo '<tr><td>Supervisor</td><td><select name="supervisor" id="supervisor"><option value="all">Todos</option>';
	foreach($array_supervisores as $k=>$v){
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

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","supervisiones.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&plaza="+document.getElementById("plaza").value+"&supervisor="+document.getElementById("supervisor").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
		
	
	</Script>
	';

	
}
	
bottom();




?>