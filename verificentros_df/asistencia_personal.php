<?php 

include ("main.php"); 

/*** ARREGLOS ***********************************************************/

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$array_puestos = array();

$rsPuestos=mysql_query("SELECT * FROM puestos ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_puestos[$Puestos['cve']]=$Puestos['nombre'];
}

if($_POST['plazausuario']==0)
	$rsPuesto=mysql_query("SELECT * FROM plazas ORDER BY numero");
else
	$rsPuesto=mysql_query("SELECT * FROM plazas WHERE cve='".$_POST['plazausuario']."' ORDER BY numero");
while($Puesto=mysql_fetch_array($rsPuesto)){
	$array_plaza[$Puesto['cve']]=$Puesto['numero'].' '.$Puesto['nombre'];
}

$res = mysql_query("SELECT * FROM motivos_checada ORDER BY orden");
while($row = mysql_fetch_array($res)) $array_motivo[$row['cve']] = $row['nombre'];

if($_POST['cmd']==101) {
	
	header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=Asistencia.xls");
header("Pragma: no-cache");
header("Expires: 0");


echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1" >';
	echo '<!--<tr style="font-size:26px"><td align="center">'.$array_plaza[$_POST['plazausuario']].'</td></tr>-->
	<tr style="font-size:24px">
			<td align="center" colspan="10" style="font-size:27px">Asistencia</td>
		 </tr>
		 <tr><td  align=""left" colspan="10" style="font-size:17px" >Fecha: '.$_POST['fecha'].'</td></tr>';
	echo '</table>';
	echo '<br>';


		//Listado de personal
		
		$select= " SELECT a.cve,IF(IFNULL(c.cve,0)>0,1,0) as estatus,b.cve as cvepersonal,b.rfc,b.puesto,a.plaza,b.nombre,
		MIN(c.fechahora) as entrada, MAX(c.fechahora) as salida
		FROM asistencia a 
		INNER JOIN personal b ON a.personal=b.cve 
		LEFT JOIN checada_lector c ON a.personal = c.cvepersonal AND a.fecha = DATE(c.fechahora)
		WHERE a.fecha='".$_POST['fecha']."'";
		if ($_POST['estatus']!="all") { $select.=" AND a.estatus='".$_POST['estatus']."'"; }
		if ($_POST['nombre']!="") { $select.=" AND b.nombre LIKE '%".$_POST['nombre']."%'"; }
		if ($_POST['num']!="") { $select.=" AND b.cve='".$_POST['num']."'"; }
		if ($_POST['puesto']!="all") { $select.=" AND b.puesto='".$_POST['puesto']."'"; }
		if ($_POST['plaza']!="all") { $select.=" AND b.plaza='".$_POST['plaza']."'"; }else{$select.=" AND b.plaza='".$_POST['plazausuario']."'";}
		$select.=" GROUP BY a.cve ORDER BY b.cve";
		$rspersonal=mysql_query($select);
		$totalRegistros = mysql_num_rows($rspersonal);
//		echo '<h2>Asistencia del dia '.fecha_letra($_POST['fecha']).'</h2>';
		if(mysql_num_rows($rspersonal)>0) 
		{
			
			echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="" id="tabla1" style="font-size:12px">';
			echo '<thead><tr bgcolo="#E9F2F8"><td colspan="10">'.mysql_num_rows($rspersonal).' Registro(s)</td></tr>';
			echo '<tr bgcolr="#E9F2F8">';
			$c=0;
			if(count($array_plaza)>1){
				echo '<th>Plaza</th>';
				$c++;
			}
			echo '<th>No.</th>
			<th>Nombre</th>
					<th>RFC</th><th>Puesto</th>';		
			echo '<th>Asistio</th>';
			foreach($array_motivo as $v) echo '<th>'.$v.'</th>';
			echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
//				rowb();
				echo'<tr>';
				if(count($array_plaza)>1)
					echo '<td align="center">'.$array_plaza[$Personal['plaza']].'</td>';
				echo '<td align="center">'.$Personal['cvepersonal'].'</td>';
				echo '<td align="left">'.$Personal['nombre'].'</td>';
				echo '<td align="center">'.$Personal['rfc'].'</td>';
				echo '<td align="center">'.$array_puestos[$Personal['puesto']].'</td>';
				
				/*if(nivelUsuario()>1){
					echo '<td align="center"><select onChange="cambiar_estatus('.$Personal['cve'].',this.value)">';
					foreach($array_nosi as $k=>$v){
						echo '<option value='.$k.'"';
						if($Personal['estatus']==$k) echo ' selected';
						echo '>'.$v.'</option>';
					}	
					echo '</select></td>';
				}
				else{
					echo '<td align="center">'.$array_nosi[$Personal['estatus']].'</td>';
				}*/
				echo '<td align="center">'.$array_nosi[$Personal['estatus']].'</td>';
				$horas = array();
				$resPersonal1 = mysql_query("SELECT IF(tipo=0,1,tipo),fechahora FROM checada_lector WHERE cvepersonal='{$Personal['cvepersonal']}' AND DATE(fechahora)='".substr($_POST['fecha'],0,10)."' GROUP BY IF(tipo=0,1,tipo)");
				while($Personal1 = mysql_fetch_array($resPersonal1)) $horas[$Personal1[0]] = $Personal1[1];
				foreach($array_motivo as $k=>$v) echo '<td align="center">'.substr($horas[$k],11,8).'</td>';
				
				echo '</tr>';
			}
			
			echo '</tbody>
				<tr>
				<td colspan="10" bcolor="#E9F2F8">';menunavegacion(); echo '</td>
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


if($_POST['cmd']==100) {
		//Listado de personal
		
		$select= " SELECT a.estatus,b.cve,b.rfc,b.puesto,a.plaza,b.nombre,
		MIN(c.fechahora) as entrada, MAX(c.fechahora) as salida
		FROM asistencia a 
		INNER JOIN personal b ON a.personal=b.cve 
		LEFT JOIN checada_lector c ON a.personal = c.cvepersonal AND a.fecha = DATE(c.fechahora)
		WHERE a.fecha='".$_POST['fecha']."' AND b.administrativo != 1";
		if ($_POST['estatus']!="all") { $select.=" AND a.estatus='".$_POST['estatus']."'"; }
		if ($_POST['nombre']!="") { $select.=" AND b.nombre LIKE '%".$_POST['nombre']."%'"; }
		if ($_POST['num']!="") { $select.=" AND b.cve='".$_POST['num']."'"; }
		if ($_POST['puesto']!="all") { $select.=" AND b.puesto='".$_POST['puesto']."'"; }
		if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; }
		$select.=" GROUP BY b.cve ORDER BY b.cve";
		$rspersonal=mysql_query($select);
		$totalRegistros = mysql_num_rows($rspersonal);
		echo '<h2>Asistencia del dia '.fecha_letra($_POST['fecha']).'</h2>';
		if(mysql_num_rows($rspersonal)>0) 
		{
			
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<thead><tr bgcolor="#E9F2F8"><td colspan="15">'.mysql_num_rows($rspersonal).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8">';
			$c=0;
			if(count($array_plaza)>1){
				echo '<th>Plaza</th>';
				$c++;
			}
			echo '<th><a href="#" onclick="SortTable('.$c.',\'N\',\'tabla1\');">No.</a></th>
			<th><a href="#" onclick="SortTable('.($c+1).',\'S\',\'tabla1\');">Nombre</a></th>
					<th>RFC</th><th>Puesto</th>';
					
			echo '<th>Asistio</th>';
			foreach($array_motivo as $v) echo '<th>'.$v.'</th>';
			echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
				rowb();
				if(count($array_plaza)>1)
					echo '<td align="center">'.$array_plaza[$Personal['plaza']].'</td>';
				echo '<td align="center">'.$Personal['cve'].'</td>';
				echo '<td align="left">'.$Personal['nombre'].'</td>';
				echo '<td align="center">'.$Personal['rfc'].'</td>';
				echo '<td align="center">'.$array_puestos[$Personal['puesto']].'</td>';
				
				echo '<td align="center">'.$array_nosi[$Personal['estatus']].'</td>';
				$horas = array();
				$resPersonal1 = mysql_query("SELECT IF(tipo=0,1,tipo),fechahora FROM checada_lector WHERE cvepersonal='{$Personal['cvepersonal']}' AND DATE(fechahora)='".substr($_POST['fecha'],0,10)."' GROUP BY IF(tipo=0,1,tipo)");
				while($Personal1 = mysql_fetch_array($resPersonal1)) $horas[$Personal1[0]] = $Personal1[1];
				foreach($array_motivo as $k=>$v) echo '<td align="center">'.substr($horas[$k],11,8).'</td>';
				echo '</tr>';
			}
			
			echo '</tbody>
				<tr>
				<td colspan="15" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
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


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de personal
		
		$select= " SELECT a.cve,IF(IFNULL(c.cve,0)>0,1,0) as estatus,b.cve as cvepersonal,b.rfc,b.puesto,a.plaza,b.nombre,
		MIN(c.fechahora) as entrada, MAX(c.fechahora) as salida
		FROM asistencia a 
		INNER JOIN personal b ON a.personal=b.cve 
		LEFT JOIN checada_lector c ON a.personal = c.cvepersonal AND a.fecha = DATE(c.fechahora)
		WHERE a.fecha='".$_POST['fecha']."'";
		if ($_POST['estatus']!="all") { $select.=" AND a.estatus='".$_POST['estatus']."'"; }
		if ($_POST['nombre']!="") { $select.=" AND b.nombre LIKE '%".$_POST['nombre']."%'"; }
		if ($_POST['num']!="") { $select.=" AND b.cve='".$_POST['num']."'"; }
		if ($_POST['puesto']!="all") { $select.=" AND b.puesto='".$_POST['puesto']."'"; }
		if ($_POST['plaza']!="all") { $select.=" AND b.plaza='".$_POST['plaza']."'"; }else{$select.=" AND b.plaza='".$_POST['plazausuario']."'";}
		$select.=" GROUP BY a.cve ORDER BY b.cve";
		$rspersonal=mysql_query($select);
		$totalRegistros = mysql_num_rows($rspersonal);
		echo '<h2>Asistencia del dia '.fecha_letra($_POST['fecha']).'</h2>';
		if(mysql_num_rows($rspersonal)>0) 
		{
			
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<thead><tr bgcolor="#E9F2F8"><td colspan="15">'.mysql_num_rows($rspersonal).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8">';
			$c=0;
			if(count($array_plaza)>1){
				echo '<th>Plaza</th>';
				$c++;
			}
			echo '<th><a href="#" onclick="SortTable('.$c.',\'N\',\'tabla1\');">No.</a></th>
			<th><a href="#" onclick="SortTable('.($c+1).',\'S\',\'tabla1\');">Nombre</a></th>
					<th>RFC</th><th>Puesto</th>';		
			echo '<th>Asistio</th>';
			foreach($array_motivo as $v) echo '<th>'.$v.'</th>';
			echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
				rowb();
				if(count($array_plaza)>1)
					echo '<td align="center">'.$array_plaza[$Personal['plaza']].'</td>';
				echo '<td align="center">'.$Personal['cvepersonal'].'</td>';
				echo '<td align="left">'.$Personal['nombre'].'</td>';
				echo '<td align="center">'.$Personal['rfc'].'</td>';
				echo '<td align="center">'.$array_puestos[$Personal['puesto']].'</td>';
				
				/*if(nivelUsuario()>1){
					echo '<td align="center"><select onChange="cambiar_estatus('.$Personal['cve'].',this.value)">';
					foreach($array_nosi as $k=>$v){
						echo '<option value='.$k.'"';
						if($Personal['estatus']==$k) echo ' selected';
						echo '>'.$v.'</option>';
					}	
					echo '</select></td>';
				}
				else{
					echo '<td align="center">'.$array_nosi[$Personal['estatus']].'</td>';
				}*/
				echo '<td align="center">'.$array_nosi[$Personal['estatus']].'</td>';
				$horas = array();
				$resPersonal1 = mysql_query("SELECT IF(tipo=0,1,tipo),fechahora FROM checada_lector WHERE cvepersonal='{$Personal['cvepersonal']}' AND DATE(fechahora)='".substr($_POST['fecha'],0,10)."' GROUP BY IF(tipo=0,1,tipo)");
				while($Personal1 = mysql_fetch_array($resPersonal1)) $horas[$Personal1[0]] = $Personal1[1];
				foreach($array_motivo as $k=>$v) echo '<td align="center">'.substr($horas[$k],11,8).'</td>';
				
				echo '</tr>';
			}
			
			echo '</tbody>
				<tr>
				<td colspan="15" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
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
	mysql_query("UPDATE asistencia SET estatus='".$_POST['estatus']."',cambios=CONCAT(cambios,',') WHERE cve='".$_POST['asistencia']."'");
	exit();
}	

top($_SESSION);


/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;
					<a href="#" onClick="atcr(\'asistencia_personal.php\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;
					<a href="#" onClick="atcr(\'\',\'_blank\',\'101\',\'0\');"><img src="images/b_print.png" border="0" title="Imprimir"></a>&nbsp;Excell</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr style="display:none;"><td>Fecha</td><td><input type="text" name="fecha" id="fecha" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" class="textField"></td></tr>'; 
		echo '<tr><td>No. Personal</td><td><input type="text" name="num" id="num" class="textField"></td></tr>'; 
		echo '<tr><td>Puesto</td><td><select name="puesto" id="puesto" class="textField"><option value="all">---Todos---</option>';
		foreach($array_puestos as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		if(count($array_plaza)>1){
			echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" class="textField"><option value="all">---Todas---</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td></tr>';
		}
		else{
			foreach($array_plaza as $k=>$v) echo '<input type="hidden" name="plaza" id="plaza" value="'.$k.'">';
		}
		echo '<tr><td>Mostrar</td><td><select name="estatus" id="estatus"><option value="all" selected>Todos</option><option value="0">Sin asistencia</option><option value="1">Con asistencia</option></select></td></tr>';
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
			objeto.open("POST","asistencia_personal.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&plaza="+document.getElementById("plaza").value+"&nombre="+document.getElementById("nombre").value+"&puesto="+document.getElementById("puesto").value+"&estatus="+document.getElementById("estatus").value+"&fecha="+document.getElementById("fecha").value+"&num="+document.getElementById("num").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	function cambiar_estatus(asistencia, estatus){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","asistencia_personal.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&asistencia="+asistencia+"&estatus="+estatus+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{}
			}
		}
	}
	
	
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}
	
	</Script>
';

?>

