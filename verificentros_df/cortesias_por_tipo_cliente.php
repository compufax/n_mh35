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
	$array_precioengomado[$row['cve']]=$row['precio_compra'];
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
	
	
	
	$filtro_fechas = "";
	//$filtro_fechas .= " AND LEFT(a.fecha,7) = '{$_POST['mes']}'";
	$filtro_fechas .= " AND a.fecha BETWEEN '{$_POST['fecha_ini']}' AND '{$_POST['fecha_fin']}'";


	$aforo=0;
	$entregas=array();
	$res1=mysql_query("SELECT TRIM(b.placa), a.engomado, IFNULL(c.agencia,'-1') as agencia, b.tipo_cortesia
		FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		LEFT JOIN depositantes c ON c.plaza = b.plaza AND c.cve = b.depositante
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND b.estatus!='C' AND b.tipo_venta=2 {$filtro_fechas} ORDER BY b.cve") or die(mysql_error());
	$total = 0;
	while($row1=mysql_fetch_array($res1)){
		$entregas[$row1['agencia']]['aforo']++;
		$tipo='verificacion';
		$entregas[$row1['agencia']][$tipo.$row1['engomado']]++;
		$entregas[$row1['agencia']]['cortesia'.$row1['tipo_cortesia']]++;
	}

	
	$orden_engomado = array(3,2,5,1,19);

	$array_nomagencia = array(-1=>'Particulares',0=>'Taller', 1=>'Agencia');


	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Tipos Clientes</th>';
	foreach($orden_engomado as $v){
		echo '<th>'.$array_engomado[$v].'</th>';
	}
	echo '<th>Cortesias 10x1</th>';
	echo '<th>Cortesias Autorizadas</th>';
	echo '<th>Total Cortesias</th>';
	echo '<th>Importe Cortesias</th>';
	echo '</tr>';

	$totales=array();
	
	foreach($array_nomagencia as $tipoventa => $nombre){
		$c=0;
		rowb();
		echo '<td>'.$nombre.'</td>';
		$importe = 0;
		foreach($orden_engomado as $v){
			echo '<td align="center">'.$entregas[$tipoventa]['verificacion'.$v].'</td>';
			$totales[$c]+=$entregas[$tipoventa]['verificacion'.$v];$c++;	
			$importe+=$entregas[$tipoventa]['verificacion'.$v]*$array_precioengomado[$v];
		}
		echo '<td align="center">'.$entregas[$tipoventa]['cortesia2'].'</td>';
		$totales[$c]+=$entregas[$tipoventa]['cortesia2'];$c++;
		echo '<td align="center">'.$entregas[$tipoventa]['cortesia1'].'</td>';
		$totales[$c]+=$entregas[$tipoventa]['cortesia1'];$c++;
		echo '<td align="center">'.$entregas[$tipoventa]['aforo'].'</td>';
		$totales[$c]+=$entregas[$tipoventa]['aforo'];$c++;
		echo '<td align="right">'.number_format($importe,2).'</td>';
		$totales[$c]+=$importe;$c++;
		echo '</tr>';
	}

	echo '<tr bgcolor="#E9F2F8"><th>Totales</th>';
	foreach($totales as $k=>$t){
		if(($k+1)==count($totales)) echo '<th align="right">'.number_format($t,2).'</th>';
		else echo '<th>'.$t.'</th>';
	}
	echo '</tr>';

	echo '</table>';
	
	exit();
}


top($_SESSION);


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
	echo '<tr style="display:none;"><td>Mes</td><td><select name="mes" id="mes">';
	echo '<option value="'.date('Y-m').'">'.date('Y-m').'</option>';
	$mes = date( "Y-m" , strtotime ( "-1 month" , strtotime(date('Y-m').'-01') ) );
	while($mes>='2016-01'){
		echo '<option value="'.$mes.'">'.$mes.'</option>';
		$mes = date( "Y-m" , strtotime ( "-1 month" , strtotime($mes.'-01') ) );
	}
	echo '</select></td></tr>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.date('Y-m').'-01" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
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
			objeto.open("POST","cortesias_por_tipo_cliente.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_fin="+document.getElementById("fecha_fin").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&mes="+document.getElementById("mes").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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