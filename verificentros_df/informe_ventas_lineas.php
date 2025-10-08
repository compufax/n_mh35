<?php
include("main.php");

//ARREGLOS

//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);
$rechazos = "9,19";



if($_POST['ajax']==1){
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$html .= '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	$html.= '<tr bgcolor="#E9F2F8"><th rowspan="2">Centro</th>';
	$res = mysql_query("SELECT MAX(clave) FROM cat_lineas");
	$row = mysql_fetch_array($res);
	$maxlinea = $row[0];
	for($j=0;$j<$maxlinea;$j++){
		$html.=  '<th colspan="4">Linea '.($j+1).'</th>';
	}
	$html.= '<th colspan="4">Total Plaza</th>';
	$html.='</tr>'; 
	$html.='<tr bgcolor="#E9F2F8">';
	for($j=0;$j<=$maxlinea;$j++){
		$html.= '<th>Aforo</th><th>Rechazo</th><th>Cancelados</th><th>Total</th>';
	}
	$html.='</tr>';
	$x=0;
	$array_importes_plaza=array();

	$res = mysql_query("SELECT a.plaza,b.clave, SUM(IF(a.engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0))
		FROM certificados a INNER JOIN cat_lineas b ON a.linea = b.cve AND a.plaza = b.plaza 
		WHERE a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'
		GROUP BY a.plaza, b.clave");
	while($row = mysql_fetch_array($res)){
		$array_importe_plaza[$row['plaza']][$row['clave']]['aforo'] += $row[2];
		$array_importe_plaza[$row['plaza']][$row['clave']]['rechazo'] += $row[3];
		$array_importe_plaza[$row['plaza']][$row['clave']]['total'] += $row[2]+$row[3];
		$array_importe_plaza[$row['plaza']]['aforo'] += $row[2];
		$array_importe_plaza[$row['plaza']]['rechazo'] += $row[3];
		$array_importe_plaza[$row['plaza']]['total'] += $row[2]+$row[3];
	}

	$res = mysql_query("SELECT a.plaza,b.clave, COUNT(a.cve)
		FROM certificados_cancelados a INNER JOIN cat_lineas b ON a.linea = b.cve AND a.plaza = b.plaza 
		WHERE a.estatus!='C' AND a.fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'
		GROUP BY a.plaza, b.clave");
	while($row = mysql_fetch_array($res)){
		$array_importe_plaza[$row['plaza']][$row['clave']]['cancelados'] += $row[2];
		$array_importe_plaza[$row['plaza']][$row['clave']]['total'] += $row[2];
		$array_importe_plaza[$row['plaza']]['cancelados'] += $row[2];
		$array_importe_plaza[$row['plaza']]['total'] += $row[2];
	}

	
	foreach($array_plazas as $k=>$v){
		$html.='<tr>';
		$html.= '<td>'.htmlentities(utf8_encode($v)).'</td>';
		for($j=1;$j<=$maxlinea;$j++){
			$html.= '<td align="right">'.number_format($array_importe_plaza[$k][$j]['aforo'],0).'</td>';
			$html.= '<td align="right">'.number_format($array_importe_plaza[$k][$j]['rechazo'],0).'</td>';
			$html.= '<td align="right">'.number_format($array_importe_plaza[$k][$j]['cancelados'],0).'</td>';
			$html.= '<td align="right">'.number_format($array_importe_plaza[$k][$j]['total'],0).'</td>';
			
		}
		$html.= '<td align="right">'.number_format($array_importe_plaza[$k]['aforo'],0).'</td>';
		$html.= '<td align="right">'.number_format($array_importe_plaza[$k]['rechazo'],0).'</td>';
		$html.= '<td align="right">'.number_format($array_importe_plaza[$k]['cancelados'],0).'</td>';
		$html.= '<td align="right">'.number_format($array_importe_plaza[$k]['total'],0).'</td>';
		$html.= '</tr>';
		$x++;
	}
	
	
	$html.= '</table>';
	echo $html;
	exit();
}


top($_SESSION);
	

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<input type="hidden" name="localidad" id="localidad" value="1"></td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
bottom();
echo '
<Script language="javascript">
	function buscarRegistros(){
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","informe_ventas_lineas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&localidad="+document.getElementById("localidad").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
		
	';	
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(0,1); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
	

	</Script>
';

?>