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
	$rsPuesto=mysql_query("SELECT * FROM plazas WHERE plaza='".$_POST['plazausuario']."' ORDER BY numero");
while($Puesto=mysql_fetch_array($rsPuesto)){
	$array_plaza[$Puesto['cve']]=$Puesto['numero'].' '.$Puesto['nombre'];
}

$res = mysql_query("SELECT * FROM motivos_checada WHERE reporte=1 ORDER BY cve");
while($row = mysql_fetch_array($res)) $array_motivo[$row['cve']] = $row['nombre'];

function restar_horas($hora_fin, $hora_ini){
	$datos_fin = explode(':', $hora_fin);
	$segundos_fin = $datos_fin[0] * 3600 + $datos_fin[1] * 60 + $datos_fin[2];
	$datos_ini = explode(':', $hora_ini);
	$segundos_ini = $datos_ini[0] * 3600 + $datos_ini[1] * 60 + $datos_ini[2];
	$segundos = $segundos_fin - $segundos_ini;
	$horas = intval($segundos/3600);
	$segundos = $segundos - $horas*3600;
	$minutos = intval($segundos/60);
	$segundos = $segundos - $minutos*60;
	return printf('%02s',$horas).':'.printf('%02s',$minutos).':'.printf('%02s',$segundos);
}

function sumar_horas($hora_fin, $hora_ini){
	$datos_fin = explode(':', $hora_fin);
	$segundos_fin = $datos_fin[0] * 3600 + $datos_fin[1] * 60 + $datos_fin[2];
	$datos_ini = explode(':', $hora_ini);
	$segundos_ini = $datos_ini[0] * 3600 + $datos_ini[1] * 60 + $datos_ini[2];
	$segundos = $segundos_fin + $segundos_ini;
	$horas = intval($segundos/3600);
	$segundos = $segundos - $horas*3600;
	$minutos = intval($segundos/60);
	$segundos = $segundos - $minutos*60;
	return printf('%02s',$horas).':'.printf('%02s',$minutos).':'.printf('%02s',$segundos);
}

if($_POST['cmd']==100) {
		//Listado de personal
		
		$select= " SELECT a.cve as cveasistencia,a.estatus,b.cve,b.rfc,b.puesto,a.plaza,b.nombre,SUM(IF(a.estatus > 0, 1, 0)) as asistencias,
		(DATEDIFF('".$_POST['fecha_fin']."','".$_POST['fecha_ini']."')+1) as diass, count(a.cve) as dias
		FROM asistencia a 
		INNER JOIN personal b ON a.personal=b.cve 
		WHERE a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.administrativo != 1";
		if ($_POST['estatus']!="all") { $select.=" AND a.estatus='".$_POST['estatus']."'"; }
		if ($_POST['nombre']!="") { $select.=" AND b.nombre LIKE '%".$_POST['nombre']."%'"; }
		if ($_POST['num']!="") { $select.=" AND b.cve='".$_POST['num']."'"; }
		if ($_POST['puesto']!="all") { $select.=" AND b.puesto='".$_POST['puesto']."'"; }
		if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; }
		$select.=" GROUP BY b.cve ORDER BY b.cve";
		$rspersonal=mysql_query($select);
		$totalRegistros = mysql_num_rows($rspersonal);
		echo '<h1>Asistencia del dia '.fecha_letra($_POST['fecha_ini']).' al '.fecha_letra($_POST['fecha_fin']).'</h1>';
		if(mysql_num_rows($rspersonal)>0) 
		{
			
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<thead><tr bgcolor="#E9F2F8"><td colspan="15">'.mysql_num_rows($rspersonal).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8">';
			$c=0;
			$cols=5;
			if(count($array_plaza)>1){
				echo '<th rowspan="2">Plaza</th>';
				$c++;
				$cols++;
			}
			echo '<th rowspan="2"><a href="#" onclick="SortTable('.$c.',\'N\',\'tabla1\');">No.</a></th>
			<th rowspan="2"><a href="#" onclick="SortTable('.($c+1).',\'S\',\'tabla1\');">Nombre</a></th>
					<th rowspan="2">RFC</th><th rowspan="2">Puesto</th><th rowspan="2">Faltas</th>';
			$fecha=$_POST['fecha_ini'];
			while($fecha<=$_POST['fecha_fin']){
				echo '<th colspan="2">'.$fecha.'</th>';
				$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
				$cols+=2;
			}
			echo '</tr>';
			echo '<tr bgcolor="#E9F2F8">';
			$fecha=$_POST['fecha_ini'];
			while($fecha<=$_POST['fecha_fin']){
				echo '<th>Hora Entrada</th><th>Hora Salida</th>';
				$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
			}
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
				echo '<td align="center">'.($Personal['dias']-$Personal['asistencias']).'</td>';
				//echo '<td align="center">'.$array_nosi[$Personal['estatus']].'</td>';
				$fecha=$_POST['fecha_ini'];
				while($fecha<=$_POST['fecha_fin']){
					$res=mysql_query("SELECT * FROM checada_lector WHERE cvepersonal = '".$Personal['cve']."' AND DATE(fechahora)='$fecha' ORDER BY fechahora");
					if(mysql_num_rows($res)>=2){
						$row=mysql_fetch_array($res);
						echo '<td align="center">'.substr($row['fechahora'],11,8).'</td>';
						$row=mysql_fetch_array($res);
						echo '<td align="center">'.substr($row['fechahora'],11,8).'</td>';
					}
					elseif(mysql_num_rows($res)>0){
						$row=mysql_fetch_array($res);
						echo '<td align="center"><div style="width:100%; background-color: #FFFF00 !important;">'.substr($row['fechahora'],11,8).'</div></td>';
						echo '<td align="center"><div style="width:100%; background-color: #FFFF00 !important;">&nbsp;</div></td>';
					}
					else{
						echo '<td align="center"><div style="width:100%; background-color: #FF0000 !important;">&nbsp;</div></td>';
						echo '<td align="center"><div style="width:100%; background-color: #FF0000 !important;">&nbsp;</div></td>';
					}
					
					$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
				}
				echo '</tr>';
			}
			
			echo '</tbody>
				<tr>
				<td colspan="'.$cols.'" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
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
		$nivel = nivelUsuario();
		$select= " SELECT a.cve as cveasistencia,a.estatus,b.cve,b.rfc,b.puesto,a.plaza,b.nombre,SUM(IF(a.estatus > 0 AND a.domingo = 0, 1, 0)) as asistencias,
		(DATEDIFF('".$_POST['fecha_fin']."','".$_POST['fecha_ini']."')+1) as diass, SUM(IF(a.estatus > 0 AND a.domingo = 0, 1, 0)) as dias
		FROM asistencia a 
		INNER JOIN personal b ON a.personal=b.cve 
		WHERE a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.administrativo != 1";
		if ($_POST['estatus']!="all") { $select.=" AND a.estatus='".$_POST['estatus']."'"; }
		if ($_POST['nombre']!="") { $select.=" AND b.nombre LIKE '%".$_POST['nombre']."%'"; }
		if ($_POST['num']!="") { $select.=" AND b.cve='".$_POST['num']."'"; }
		if ($_POST['puesto']!="all") { $select.=" AND b.puesto='".$_POST['puesto']."'"; }
		if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; }
		$select.=" GROUP BY b.cve ORDER BY b.cve";
		$rspersonal=mysql_query($select) or die(mysql_error());
		$totalRegistros = mysql_num_rows($rspersonal);
		if(mysql_num_rows($rspersonal)>0) 
		{
			
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<thead>';
			echo '<tr bgcolor="#E9F2F8">';
			$c=0;
			$cols=4;
			if(count($array_plaza)>1){
				echo '<th rowspan="2">Plaza</th>';
				$c++;
				$cols++;
			}
			echo '<th rowspan="2"><a href="#" onclick="SortTable('.$c.',\'N\',\'tabla1\');">No.</a></th>
			<th rowspan="2"><a href="#" onclick="SortTable('.($c+1).',\'S\',\'tabla1\');">Nombre</a></th>
					<th rowspan="2">RFC</th><th rowspan="2" style="border-left: 2px solid #000000;">Faltas</th>
					<th rowspan="2" style="border-left: 2px solid #000000;">Horas Extras</th>';
			$fecha=$_POST['fecha_ini'];
			while($fecha<=$_POST['fecha_fin']){
				$arfecha=explode("-",$fecha);
				echo '<th colspan="'.count($array_motivo).'" style="border-left: 2px solid #000000;">'.$fecha.'</th>';
				$cols+=count($array_motivo);
				$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
			}
			echo '</tr>';
			echo '<tr bgcolor="#E9F2F8">';
			$fecha=$_POST['fecha_ini'];
			while($fecha<=$_POST['fecha_fin']){
				$arfecha=explode("-",$fecha);
				$c=1;
				foreach($array_motivo as $v){
					echo '<th';
					if($c==1) echo ' style="border-left: 2px solid #000000;"';
					echo '>'.$v.'</th>';
					$c++;
				}
				$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
			}
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
				echo '<td align="center" style="border-left: 2px solid #000000;">'.($Personal['dias']-$Personal['asistencias']).'</td>';
				//echo '<td align="center">'.$array_nosi[$Personal['estatus']].'</td>';
				$checadores = '';
				$fecha=$_POST['fecha_ini'];
				$extras = 0;
				while($fecha<=$_POST['fecha_fin']){
					$arfecha=explode("-",$fecha);
					$horas = array();
					$resPersonal1 = mysql_query("SELECT IF(tipo=0,1,tipo),fechahora FROM checada_lector WHERE cvepersonal='{$Personal['cve']}' AND DATE(fechahora)='".$fecha."' GROUP BY IF(tipo=0,1,tipo)");
					$turno=2;
					while($Personal1 = mysql_fetch_array($resPersonal1)) $horas[$Personal1[0]] = $Personal1[1];
					if(substr($horas[$k],11,8)<'11:00:00') $turno=1;
					if(count($horas)>=count($array_motivo)){
						$c=1;
						foreach($array_motivo as $k=>$v){
							$checadores.= '<td align="center"';
							if($c==1) $checadores.= ' style="border-left: 2px solid #000000;"';
							$checadores.= '>';
							if($c==1 && (substr($horas[$k],11,8) <= '11:00:00' && substr($horas[$k],11,8) >'08:15:00') || (substr($horas[$k],11,8) > '11:00:00' && substr($horas[$k],11,8) >'12:15:00'))
								$checadores .= '<font color="GREEN">';
							else
								$checadores .= '<font color="BLACK">';
							$checadores.= substr($horas[$k],11,8).'</font></td>';
							$c++;
							if($c==4){
								$extra = 0;
								if($turno==1 && substr($horas[$k],11,8) > '16:00:00'){
									$extra = restar_horas(substr($horas[$k],11,8), '16:00:00');
								}
								elseif($turno==2 && substr($horas[$k],11,8) > '20:00:00'){
									$extra = restar_horas(substr($horas[$k],11,8), '16:00:00');
								}
								if ($extra > '03:00:00') $extra='03:00:00';
								$extras+=sumar_horas($extras, $extra);
							}
						}
					}
					elseif(count($horas) > 0 && count($horas) < count($array_motivo)){
						$row=mysql_fetch_array($res);
						$color = 'FFFF00';
						if(substr($row['fechahora'],11,8)=='00:00:00'){
							$color = '00FF00';
						}
						$c=1;
						foreach($array_motivo as $k=>$v){
							$checadores.= '<td align="center"';
							if($c==1) $checadores.= ' style="border-left: 2px solid #000000;"';
							$checadores.= '><div style="width:100%; background-color: #'.$color.' !important;">';
							if($c==1 && substr($horas[$k],11,8) <= '00:00:00')
								$checadores .= '<font color="YELLOW">';
							elseif($c==1 && (substr($horas[$k],11,8) <= '11:00:00' && substr($horas[$k],11,8) >'08:15:00') || (substr($horas[$k],11,8) > '11:00:00' && substr($horas[$k],11,8) >'12:15:00'))
								$checadores .= '<font color="GREEN">';
							else
								$checadores .= '<font color="BLACK">';
							$checadores.=substr($horas[$k],11,8).'</font></div></td>';
							$c++;
						}
					}
					else{
						$checadores.= '<td align="center" style="border-left: 2px solid #000000;"><div style="width:100%; background-color: #FFFF00 !important;"';
						//if($nivel >= 3){
						//	echo ' onClick="if(confirm(\'Esta seguro de justificar la falta\')) cambiar('.$Personal['cve'].',1,\''.$fecha.'\');"';
						//}
						$checadores.= '>&nbsp;</div></td>';
						for($i=2;$i<=count($array_motivo);$i++)
							$checadores.= '<td align="center"><div style="width:100%; background-color: #FFFF00 !important;">&nbsp;</div></td>';
					}
					$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
				}
				echo '<td align="center">'.$extras.'</td>';
				echo $checadores;
				echo '</tr>';
			}
			
			echo '</tbody>
				<tr>
				<td colspan="'.$cols.'" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
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

if($_POST['ajax']==3){
	$res=mysql_query("SELECT * FROM checada_lector WHERE cvepersonal = '".$_POST['personal']."' AND DATE(fechahora)='".$_POST['fecha']."' ORDER BY fechahora LIMIT ".($_POST['checada']-1).",1");
	if($row=mysql_fetch_array($res)){
		mysql_query("UPDATE checada_lector SET cambios=CONCAT(cambios,'|".$_POST['usuario'].",".$row['fechahora'].",".$_POST['fecha']." 00:00:00,".fechaLocal()." ".horaLocal()."'),fechahora = '".$_POST['fecha']." 00:00:00' WHERE cve='".$row['cve']."'");
	}
	else{
		mysql_query("INSERT checada_lector SET cambios=CONCAT(cambios,'|".$_POST['usuario'].",".$row['fechahora'].",".$_POST['fecha']." ".$_POST['hora'].",".fechaLocal()." ".horaLocal()."'),fechahora = '".$_POST['fecha']." ".$_POST['hora']."',cvepersonal='".$_POST['personal']."'");
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
					<a href="#" onClick="atcr(\'asistencia_personal_periodo.php\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		if(intval(date('d'))<16) $fecha_ini = date('Y-m').'-01';
		else $fecha_ini = date('Y-m').'-16';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.$fecha_ini.'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
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
			objeto.open("POST","asistencia_personal_periodo.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&plaza="+document.getElementById("plaza").value+"&nombre="+document.getElementById("nombre").value+"&puesto="+document.getElementById("puesto").value+"&estatus="+document.getElementById("estatus").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&num="+document.getElementById("num").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
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

	function cambiar(personal, checada, fecha){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","asistencia_personal_periodo.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			//objeto.send("ajax=3&personal="+personal+"&checada="+checada+"&fecha="+fecha+"&hora="+document.getElementById("h_"+personal+"_"+checada+"_"+fecha).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.send("ajax=3&personal="+personal+"&checada="+checada+"&fecha="+fecha+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{alert("Se cambio con exito la hora de la checada")}
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

