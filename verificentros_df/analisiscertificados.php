<?php
include ("main.php"); 
$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plaza']."'");
$Plaza=mysql_fetch_array($res);

$res=mysql_query("SELECT local, validar_certificado FROM plazas WHERE cve='".$_POST['plaza']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];
$ValidarCertificados = $row[1];

$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE  entrega=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio_compra'];
}

$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$res = mysql_query("SELECT * FROM plazas ORDER BY numero,nombre");
while($row=mysql_fetch_array($res)){
	$array_plaza[$row['cve']]=$row['numero'].' '.$row['nombre'];
}

if($_POST['cmd']==1)
	$res = mysql_query("SELECT * FROM anios_certificados  ORDER BY nombre DESC LIMIT 2");
else
	$res = mysql_query("SELECT * FROM anios_certificados  ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
	$array_fechainianio[$row['cve']]=$row['fecha_ini'];
}


$array_estatus = array('A'=>'Activo','C'=>'Cancelado','E'=>'Confirmado');

if($_POST['ajax']==1){
	$filtro = "";
	if($_POST['plaza']>0) $filtro .= " AND plaza='".$_POST['plaza']."'";
	$reporte= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">
			<tr bgcolor="#E9F2F8"><th>Centro</th><th>Movimientos</th><th>Rechazos en Total</th><th>Rechazos por OBDII</th>
			<th>% de Rechazos OBDII con Respecto a los Movimientos</th>';
	$reporte.= '</tr>';
	$total = array();
	$cantidades = array();
	$res = mysql_query("SELECT a.plaza,b.numero,b.nombre,COUNT(a.cve),SUM(IF(a.engomado=19,1,0)),SUM(IF(a.engomado=19 AND a.problema_obdii=1,1,0)) FROM certificados a 
		INNER JOIN plazas b ON b.cve = a.plaza
		WHERE a.estatus!='C' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY a.plaza ORDER BY b.numero, b.nombre");
	while($row = mysql_fetch_array($res)){
		$reporte.=rowb(false);
		$reporte.= '<td>'.$row['numero'].' '.utf8_encode($row['nombre']).'</td>
		<td align="right">'.$row[3].'</td>
		<td align="right">'.$row[4].'</td>
		<td align="right">'.$row[5].'</td>
		<td align="right">'.round($row[5]*100/$row[3],1).'</td>
		</tr>';
		$totales[0]+=$row[3];
		$totales[1]+=$row[4];
		$totales[2]+=$row[5];
	}
	$reporte.= '<tr bgcolor="#E9F2F8"><th align="right">Totales</th>';
	foreach($totales as $t) $reporte.= '<th align="right">'.number_format($t,0).'</th>';
	$reporte.= '<th align="right">'.number_format($totales[2]*100/$totales[0],1).'</th></tr></table>';
	$data[]=$totales[0]-$totales[1];
	$data[]=$totales[1]-$totales[2];
	$data[]=$totales[2];
	$legends[]='Movimientos';
	$legends[]='Rechazos';
	$legends[]='Rechazos OBDII';
	if(count($data)>0){
		$data2=array();
		$data2[0] = array('Entrega');
		foreach($data as $datos){
			$data2[0][] = $datos;
		}
		//$reporte.='<img src="graficabar.php?fecha_ini='.$_POST['fecha_ini'].'&fecha_fin='.$_POST['fecha_fin'].'&reporte=desglose_cuentas_grupo">';
		require_once("../phplot/phplot.php");
		$plot = new PHPlot(1000,800);
		$plot->SetDataValues($data2);
		$plot->SetDataType('text-data');
		$plot->SetPlotType('pie');
		$plot->SetTitle('Productividad Lineas');
		$plot->SetLegend($legends);
		$plot->SetFileFormat("jpg");
		$plot->SetFailureImage(False);
		$plot->SetXDataLabelPos('plotin');
		$plot->SetPieLabelType('value', 'data', 1);
		//$plot->SetPrintImage(False);
		$plot->SetIsInline(True);
		$plot->SetOutputFile("grafica.jpg");
		$plot->SetImageBorderType('plain');
		
		//$plot->SetXDataLabelPos('plotin');
		
		
		//foreach ($data as $row) $plot->SetLegend($row[0]); // Copy labels to legend
		//$plot->SetXTickLabelPos('none');
		//$plot->SetXTickPos('none');
		$plot->DrawGraph();
		$reporte .= '<img src="grafica.jpg?'.date("Y-m-d H:i:s").'">';
	}
	echo $reporte;
	exit();
}
top($_SESSION);


if ($_POST['cmd']<1) {
	
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	//echo '<td><a href="#" onClick="atcr(\'detalle_compras_certificados.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
	//echo'<td><a href="#" onClick="atcr(\'\',\'_blank\',\'100\',\'0\')"><img src="images/b_print.png" border="0" title="Imprimir"></a>Imprimir&nbsp;</td>';
	/*if($_POST['cveusuario']==1){
		if($ValidarCertificados==1)
			echo '<td><input type="checkbox" checked onClick="atcr(\'detalle_compras_certificados.php\',\'\',13,0)">Validacion de Certificados</td></tr>';
		else
			echo '<td><input type="checkbox" onClick="atcr(\'detalle_compras_certificados.php\',\'\',12,0)">Validacion de Certificados</td></tr>';
	}*/
	echo '
		 </tr>';
	echo '</table>';
	echo '<table width="100%"><tr><td width="50%">';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza"><option value="0">Todos</option>';
	
	foreach($array_plaza as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
	echo '</td><td id="concentrado"></td></tr></table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
//objeto.send("ajax=1&btn="+btn+"&mostrar="+document.getElementById("mostrar").value+"&anio="+document.getElementById("anio").value+"&estatus="+document.getElementById("estatus").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&engomado="+document.getElementById("engomado").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
echo '
<Script language="javascript">

	function buscarRegistros(btn)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","analisiscertificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&plaza="+document.getElementById("plaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
	buscarRegistros(0); //Realizar consulta de todos los registros al iniciar la forma.
		
	
	</Script>
	';

	
}

bottom();

?>