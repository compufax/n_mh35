<?php

include("main.php");

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$res=mysql_query("SELECT local,vende_seguros FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];
$VendeSeguros=$row[1];

$array_engomado = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND plazas like '%|".$_POST['plazausuario']."|%' AND entrega=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
}

$res = mysql_query("SELECT * FROM anios_certificados  ORDER BY nombre DESC LIMIT 2");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
}

$array_tipo_pago = array();
$res = mysql_query("SELECT * FROM tipos_pago WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}

$array_motivos_intento = array();
$res = mysql_query("SELECT * FROM motivos_intento WHERE localidad IN (0,1) ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_motivos_intento[$row['cve']]=$row['nombre'];
}

$array_depositantes = array();
$res = mysql_query("SELECT * FROM depositantes WHERE plaza='".$_POST['plazausuario']."' AND edo_cuenta=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_depositantes[$row['cve']]=$row['nombre'];
}

if($_POST['ajax']==1){
	
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>No Rechazos</th><th>No Placas</th>';
	$detallengomado="";
	foreach($array_engomado as $k=>$v){
		echo '<th>'.$v.'</th>';
		$detallengomado.=",SUM(IF(a.engomado='$k',1,0)) as engomado".$k."";
	}
	echo '<th>Total Movimientos</th><th>reverificaciones</th></tr>';
	


	$entregas=array();
	$res=mysql_query("SELECT TRIM(b.placa)".$detallengomado.",
		COUNT(a.cve) as total,SUM(IF(a.engomado!=19,1,0)) as certificados
		FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND b.estatus!='C' and a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
		GROUP BY TRIM(a.placa) ORDER BY TRIM(placa)") or die(mysql_error());
	while($row=mysql_fetch_array($res)){
		$entregas[$row['engomado19']]['placas']++;
		$entregas[$row['engomado19']]['total']+=$row['total'];
		if($row['certificados']>1)
			$entregas[$row['engomado19']]['reverificaciones']+=$row['certificados']-1;
		foreach($array_engomado as $k=>$v)
			$entregas[$row['engomado19']]['engomado'.$k] += $row['engomado'.$k];
	}
	$x=0;
	ksort($entregas);
	foreach($entregas as $rechazos=>$row){
		rowb();
		echo '<td align="center">'.$rechazos.'</td>';
		$c=0;
		echo '<td align="right">'.$row['placas'].'</td>';
		$total[$c]+=$row['placas'];$c++;
		foreach($array_engomado as $k=>$v){
				echo '<td align="right">'.$row['engomado'.$k].'</td>';
				$total[$c]+=$row['engomado'.$k];$c++;
		}
		echo '<td align="right">'.$row['total'].'</td>';
		$total[$c]+=$row['total'];$c++;
		echo '<td align="right">'.$row['reverificaciones'].'</td>';
		$total[$c]+=$row['reverificaciones'];$c++;
		$x++;
	}
	
	echo '<tr bgcolor="#E9F2F8"><th align="left" colspan="1">'.$x.' Registro(s)</th>';
	foreach($total as $k=>$v){
		echo '<th align="right">'.number_format($v,0).'</th>';
	}
	echo '</tr>';
	echo '</table>';
	exit();
}


top($_SESSION);
echo '<input type="hidden" name="rep" id="rep" value="4">';


if ($_POST['cmd']<1) {
	$nivelUsuario=nivelUsuario();
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	echo '
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr ><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="'.substr(fechaLocal(),0,8).'01">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr ><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="'.fechaLocal().'">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';

	echo '</table>';
	echo '<br>';
	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">
	$("#anios").multipleSelect({
		width: 500
	});	
	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","rep_certificadosporrechazos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					document.getElementById("Resultados").innerHTML = objeto.responseText;
				}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}
	//buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.

	
	</Script>
	';

	
}

bottom();
?>