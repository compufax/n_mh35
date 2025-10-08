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

$res = mysql_query("SELECT * FROM motivos_checada WHERE reporte=1 ORDER BY orden");
while($row = mysql_fetch_array($res)) $array_motivo[$row['cve']] = $row['nombre'];

if($_POST['cmd']==101) {
	header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=Asistencia por Periodo.xls");
header("Pragma: no-cache");
header("Expires: 0");


echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1" >';
	echo '<!--<tr style="font-size:26px"><td align="center">'.$array_plaza[$_POST['plazausuario']].'</td></tr>-->
	<tr style="font-size:24px">
			<td align="center" colspan="10" style="font-size:27px">Asistencia por Periodo</td>
		 </tr>
		 <tr><td  align=""left" colspan="10" style="font-size:17px" >Periodo: '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</td></tr>';
	echo '</table>';
	echo '<br>';
		//Listado de personal
		$nivel = nivelUsuario();
		$select= " SELECT a.cve as cveasistencia,a.estatus,b.cve,b.rfc,b.puesto,a.plaza,b.nombre,SUM(IF(a.estatus > 0, 1, 0)) as asistencias,
		(DATEDIFF('".$_POST['fecha_fin']."','".$_POST['fecha_ini']."')+1) as diass, count(a.cve) as dias
		FROM asistencia a 
		INNER JOIN personal b ON a.personal=b.cve 
		INNER JOIN plazas c ON c.cve = b.plaza
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
			
			echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="" id="tabla1" style="font-size:12px">';
			echo '<thead>';
			echo '<tr bgclor="#E9F2F8">';
			$c=0;
			$cols=6;
			if(count($array_plaza)>1){
				echo '<th rowspan="2">Plaza</th>';
				$c++;
				$cols++;
			}
			echo '<th rowspan="2">No.</th>
			<th rowspan="2">Nombre</th>
					<th rowspan="2">RFC</th><th rowspan="2" style="border-left: 2px solid #000000;">Faltas</th>';
			$fecha=$_POST['fecha_ini'];
			while($fecha<=$_POST['fecha_fin']){
				$arfecha=explode("-",$fecha);
				$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
				if($dia != 0){
					echo '<th colspan="6" style="border-left: 2px solid #000000;">'.$fecha.'</th>';
					$cols+=6;
				}
				$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
			}
			echo '<th style="border-left: 2px solid #000000;" rowspan="2">Total Tiempo<br>Comida</th><th rowspan="2">Total Tiempo<br>Trabajo</th>';
			echo '</tr>';
			echo '<tr bgolor="#E9F2F8">';
			$fecha=$_POST['fecha_ini'];
			while($fecha<=$_POST['fecha_fin']){
				$arfecha=explode("-",$fecha);
				$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
				if($dia != 0){
					echo '<th style="border-left: 2px solid #000000;">Hora Entrada<br>Trabajar</th><th>Hora Salida<br>Comer</th>
					<th>Hora Entrada<br>Comer</th><th>Hora Salida<br>Trabajar</th>
					<th>Tiempo<br>Comida</th><th>Tiempo<br>Trabajo</th>';
				}
				$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
			}
			echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
//				rowb();
				echo'<tr>';
				if(count($array_plaza)>1)
					echo '<td align="center">'.$array_plaza[$Personal['plaza']].'</td>';
				echo '<td align="center">'.$Personal['cve'].'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($Personal['nombre'])).'</td>';
				echo '<td align="center">'.$Personal['rfc'].'</td>';
				echo '<td align="center" style="border-left: 2px solid #000000;">'.($Personal['dias']-$Personal['asistencias']).'</td>';
				//echo '<td align="center">'.$array_nosi[$Personal['estatus']].'</td>';
				$fecha=$_POST['fecha_ini'];
				$tcomer=$ttrabajo=0;
				while($fecha<=$_POST['fecha_fin']){
					$arfecha=explode("-",$fecha);
					$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
					if($dia != 0){
						$res=mysql_query("SELECT RIGHT(fechahora, 8) as hora, TIME_TO_SEC(RIGHT(fechahora, 8)) as segundos  FROM checada_lector WHERE cvepersonal = '".$Personal['cve']."' AND DATE(fechahora)='$fecha' ORDER BY fechahora");
						$row = mysql_fetch_array($res);
						$hora_entrada_trabajo = $row['hora'];
						$segundos_entrada_trabajo = $row['segundos'];
						$row = mysql_fetch_array($res);
						$hora_salida_comer = $row['hora'];
						$segundos_salida_comer = $row['segundos'];
						$row = mysql_fetch_array($res);
						$hora_entrada_comer = $row['hora'];
						$segundos_entrada_comer = $row['segundos'];
						$row = mysql_fetch_array($res);
						$hora_salida_trabajo = $row['hora'];
						$segundos_salida_trabajo = $row['segundos'];
						echo '<td style="border-left: 2px solid #000000;" align="center">'.$hora_entrada_trabajo.'</td>';
						echo '<td align="center">'.$hora_salida_comer.'</td>';
						echo '<td align="center">'.$hora_entrada_comer.'</td>';
						echo '<td align="center">'.$hora_salida_trabajo.'</td>';
						$tiempo_trabajo=0;
						$tiempo_comer=0;
						if($segundos_entrada_trabajo > 0 && $segundos_salida_comer > 0){
							$tiempo_trabajo += $segundos_salida_comer - $segundos_entrada_trabajo;
						}
						if($segundos_entrada_comer > 0 && $segundos_salida_comer > 0){
							$tiempo_comer += $segundos_entrada_comer - $segundos_salida_comer;
						}
						if($segundos_entrada_comer > 0 && $segundos_salida_trabajo > 0){
							$tiempo_trabajo += $segundos_salida_trabajo - $segundos_entrada_comer;
						}
						$res = mysql_query("SELECT SEC_TO_TIME(".$tiempo_trabajo.") as tiempo_trabajo, SEC_TO_TIME(".$tiempo_comer.") as tiempo_comer");
						$row = mysql_fetch_array($res);
						echo '<td align="center">'.$row['tiempo_comer'].'</td>';
						echo '<td align="center">'.$row['tiempo_trabajo'].'</td>';
						$tcomer+=$tiempo_comer;
						$ttrabajo+=$tiempo_trabajo;
					}
					$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
				}
				$res = mysql_query("SELECT SEC_TO_TIME(".$ttrabajo.") as tiempo_trabajo, SEC_TO_TIME(".$tcomer.") as tiempo_comer");
				$row = mysql_fetch_array($res);
				echo '<td style="border-left: 2px solid #000000;" align="center">'.$row['tiempo_comer'].'</td>';
				echo '<td align="center">'.$row['tiempo_trabajo'].'</td>';
				
				echo '</tr>';
			}
			
			echo '</tbody>
				<tr>
				<td colspan="'.$cols.'" bgcolo="#E9F2F8">';menunavegacion(); echo '</td>
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
		
		$select= " SELECT a.cve as cveasistencia,a.estatus,b.cve,b.rfc,b.puesto,a.plaza,b.nombre,SUM(IF(a.estatus > 0, 1, 0)) as asistencias,
		(DATEDIFF('".$_POST['fecha_fin']."','".$_POST['fecha_ini']."')+1) as diass, count(a.cve) as dias
		FROM asistencia a 
		INNER JOIN personal b ON a.personal=b.cve 
		INNER JOIN plazas c ON c.cve = b.plaza
		WHERE a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.administrativo != 1";
		if ($_POST['estatus']!="all") { $select.=" AND a.estatus='".$_POST['estatus']."'"; }
		if ($_POST['nombre']!="") { $select.=" AND b.nombre LIKE '%".$_POST['nombre']."%'"; }
		if ($_POST['num']!="") { $select.=" AND b.cve='".$_POST['num']."'"; }
		if ($_POST['puesto']!="all") { $select.=" AND b.puesto='".$_POST['puesto']."'"; }
		if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; }
		$select.=" GROUP BY b.cve ORDER BY b.cve";
		$rspersonal=mysql_query($select);
		$totalRegistros = mysql_num_rows($rspersonal);
		echo '<h2>Asistencia del dia '.fecha_letra($_POST['fecha_ini']).' al '.fecha_letra($_POST['fecha_fin']).'</h2>';
		if(mysql_num_rows($rspersonal)>0) 
		{
			
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<thead><tr bgcolor="#E9F2F8"><td colspan="15">'.mysql_num_rows($rspersonal).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8">';
			$c=0;
			$cols=7;
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
				echo '<th colspan="6">'.$fecha.'</th>';
				$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
				$cols+=6;
			}
			echo '<th rowspan="2">Total Tiempo<br>Comida</th><th rowspan="2">Total Tiempo<br>Trabajo</th>';
			echo '</tr>';
			echo '<tr bgcolor="#E9F2F8">';
			$fecha=$_POST['fecha_ini'];
			while($fecha<=$_POST['fecha_fin']){
				echo '<th>Hora Entrada<br>Trabajar</th><th>Hora Salida<br>Comer</th>
				<th>Hora Entrada<br>Comer</th><th>Hora Salida<br>Trabajar</th>
				<th>Tiempo<br>Comida</th><th>Tiempo<br>Trabajo</th>';
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
				$tcomer=$ttrabajo=0;
				while($fecha<=$_POST['fecha_fin']){
					$res=mysql_query("SELECT RIGHT(fechahora, 8) as hora, TIME_TO_SEC(RIGHT(fechahora, 8)) as segundos  FROM checada_lector WHERE cvepersonal = '".$Personal['cve']."' AND DATE(fechahora)='$fecha' ORDER BY fechahora");
					$row = mysql_fetch_array($res);
					$hora_entrada_trabajo = $row['hora'];
					$segundos_entrada_trabajo = $row['segundos'];
					$row = mysql_fetch_array($res);
					$hora_salida_comer = $row['hora'];
					$segundos_salida_comer = $row['segundos'];
					$row = mysql_fetch_array($res);
					$hora_entrada_comer = $row['hora'];
					$segundos_entrada_comer = $row['segundos'];
					$row = mysql_fetch_array($res);
					$hora_salida_trabajo = $row['hora'];
					$segundos_salida_trabajo = $row['segundos'];
					echo '<td align="center">'.$hora_entrada_trabajo.'</td>';
					echo '<td align="center">'.$hora_salida_comer.'</td>';
					echo '<td align="center">'.$hora_entrada_comer.'</td>';
					echo '<td align="center">'.$hora_salida_trabajo.'</td>';
					$tiempo_trabajo=0;
					$tiempo_comer=0;
					if($segundos_entrada_trabajo > 0 && $segundos_salida_comer > 0){
						$tiempo_trabajo += $segundos_salida_comer - $segundos_entrada_trabajo;
					}
					if($segundos_entrada_comer > 0 && $segundos_salida_comer > 0){
						$tiempo_comer += $segundos_entrada_comer - $segundos_salida_comer;
					}
					if($segundos_entrada_comer > 0 && $segundos_salida_trabajo > 0){
						$tiempo_trabajo += $segundos_salida_trabajo - $segundos_entrada_comer;
					}
					$res = mysql_query("SELECT SEC_TO_TIME(".$tiempo_trabajo.") as tiempo_trabajo, SEC_TO_TIME(".$tiempo_comer.") as tiempo_comer");
					$row = mysql_fetch_array($res);
					echo '<td align="center">'.$row['tiempo_comer'].'</td>';
					echo '<td align="center">'.$row['tiempo_trabajo'].'</td>';
					$tcomer+=$tiempo_comer;
					$ttrabajo+=$tiempo_trabajo;
					$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
				}
				$res = mysql_query("SELECT SEC_TO_TIME(".$ttrabajo.") as tiempo_trabajo, SEC_TO_TIME(".$tcomer.") as tiempo_comer");
				$row = mysql_fetch_array($res);
				echo '<td align="center">'.$row['tiempo_comer'].'</td>';
				echo '<td align="center">'.$row['tiempo_trabajo'].'</td>';
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
		$select= " SELECT a.cve as cveasistencia,a.estatus,b.cve,b.rfc,b.puesto,a.plaza,b.nombre,SUM(IF(a.estatus > 0, 1, 0)) as asistencias,
		(DATEDIFF('".$_POST['fecha_fin']."','".$_POST['fecha_ini']."')+1) as diass, count(a.cve) as dias
		FROM asistencia a 
		INNER JOIN personal b ON a.personal=b.cve 
		INNER JOIN plazas c ON c.cve = b.plaza
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
			$cols=6;
			if(count($array_plaza)>1){
				echo '<th rowspan="2">Plaza</th>';
				$c++;
				$cols++;
			}
			echo '<th rowspan="2"><a href="#" onclick="SortTable('.$c.',\'N\',\'tabla1\');">No.</a></th>
			<th rowspan="2"><a href="#" onclick="SortTable('.($c+1).',\'S\',\'tabla1\');">Nombre</a></th>
					<th rowspan="2">RFC</th><th rowspan="2" style="border-left: 2px solid #000000;">Faltas</th>';
			$fecha=$_POST['fecha_ini'];
			while($fecha<=$_POST['fecha_fin']){
				$arfecha=explode("-",$fecha);
				$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
				if($dia != 0){
					echo '<th colspan="6" style="border-left: 2px solid #000000;">'.$fecha.'</th>';
					$cols+=6;
				}
				$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
			}
			echo '<th style="border-left: 2px solid #000000;" rowspan="2">Total Tiempo<br>Comida</th><th rowspan="2">Total Tiempo<br>Trabajo</th>';
			echo '</tr>';
			echo '<tr bgcolor="#E9F2F8">';
			$fecha=$_POST['fecha_ini'];
			while($fecha<=$_POST['fecha_fin']){
				$arfecha=explode("-",$fecha);
				$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
				if($dia != 0){
					echo '<th style="border-left: 2px solid #000000;">Hora Entrada<br>Trabajar</th><th>Hora Salida<br>Comer</th>
					<th>Hora Entrada<br>Comer</th><th>Hora Salida<br>Trabajar</th>
					<th>Tiempo<br>Comida</th><th>Tiempo<br>Trabajo</th>';
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
				echo '<td align="left">'.htmlentities(utf8_encode($Personal['nombre'])).'</td>';
				echo '<td align="center">'.$Personal['rfc'].'</td>';
				echo '<td align="center" style="border-left: 2px solid #000000;">'.($Personal['dias']-$Personal['asistencias']).'</td>';
				//echo '<td align="center">'.$array_nosi[$Personal['estatus']].'</td>';
				$fecha=$_POST['fecha_ini'];
				$tcomer=$ttrabajo=0;
				while($fecha<=$_POST['fecha_fin']){
					$arfecha=explode("-",$fecha);
					$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
					if($dia != 0){
						$res=mysql_query("SELECT RIGHT(fechahora, 8) as hora, TIME_TO_SEC(RIGHT(fechahora, 8)) as segundos  FROM checada_lector WHERE cvepersonal = '".$Personal['cve']."' AND DATE(fechahora)='$fecha' ORDER BY fechahora");
						$row = mysql_fetch_array($res);
						$hora_entrada_trabajo = $row['hora'];
						$segundos_entrada_trabajo = $row['segundos'];
						$row = mysql_fetch_array($res);
						$hora_salida_comer = $row['hora'];
						$segundos_salida_comer = $row['segundos'];
						$row = mysql_fetch_array($res);
						$hora_entrada_comer = $row['hora'];
						$segundos_entrada_comer = $row['segundos'];
						$row = mysql_fetch_array($res);
						$hora_salida_trabajo = $row['hora'];
						$segundos_salida_trabajo = $row['segundos'];
						echo '<td style="border-left: 2px solid #000000;" align="center">'.$hora_entrada_trabajo.'</td>';
						echo '<td align="center">'.$hora_salida_comer.'</td>';
						echo '<td align="center">'.$hora_entrada_comer.'</td>';
						echo '<td align="center">'.$hora_salida_trabajo.'</td>';
						$tiempo_trabajo=0;
						$tiempo_comer=0;
						if($segundos_entrada_trabajo > 0 && $segundos_salida_comer > 0){
							$tiempo_trabajo += $segundos_salida_comer - $segundos_entrada_trabajo;
						}
						if($segundos_entrada_comer > 0 && $segundos_salida_comer > 0){
							$tiempo_comer += $segundos_entrada_comer - $segundos_salida_comer;
						}
						if($segundos_entrada_comer > 0 && $segundos_salida_trabajo > 0){
							$tiempo_trabajo += $segundos_salida_trabajo - $segundos_entrada_comer;
						}
						$res = mysql_query("SELECT SEC_TO_TIME(".$tiempo_trabajo.") as tiempo_trabajo, SEC_TO_TIME(".$tiempo_comer.") as tiempo_comer");
						$row = mysql_fetch_array($res);
						echo '<td align="center">'.$row['tiempo_comer'].'</td>';
						echo '<td align="center">'.$row['tiempo_trabajo'].'</td>';
						$tcomer+=$tiempo_comer;
						$ttrabajo+=$tiempo_trabajo;
					}
					$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
				}
				$res = mysql_query("SELECT SEC_TO_TIME(".$ttrabajo.") as tiempo_trabajo, SEC_TO_TIME(".$tcomer.") as tiempo_comer");
				$row = mysql_fetch_array($res);
				echo '<td style="border-left: 2px solid #000000;" align="center">'.$row['tiempo_comer'].'</td>';
				echo '<td align="center">'.$row['tiempo_trabajo'].'</td>';
				
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
					<a href="#" onClick="atcr(\'asistencia_personal_periodo.php\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;
					<a href="#" onClick="atcr(\'\',\'_blank\',\'101\',\'0\');"><img src="images/b_print.png" border="0" title="Imprimir"></a>&nbsp;Excell</td>
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
		if(count($array_plaza)>1 && $_POST['plazausuario'] == 0){
			echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" class="textField"><option value="all">---Todas---</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td></tr>';
		}
		else{
			//foreach($array_plaza as $k=>$v) echo '<input type="hidden" name="plaza" id="plaza" value="'.$k.'">';
			echo '<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
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

