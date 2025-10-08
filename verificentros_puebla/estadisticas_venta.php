<?php 
include ("main.php"); 

$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio'];
}

$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$array_tipo_pago = array();
$res = mysql_query("SELECT * FROM tipos_pago WHERE plaza = '".$_POST['plazausuario']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}

/*** CONSULTA AJAX  **************************************************/

if($_POST['cmd']==100) {
	echo '<h1>Estadisticas de Entreda de Certificados de '.$array_plaza[$_POST['plazausuario']].'<br>
	del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h1>';
		//Listado de plazas
		$select= " SELECT a.engomado,sum(b.monto),count(b.cve) FROM certificados a INNER JOIN cobro_engomado b ON b.plaza=a.plaza AND b.cve = a.ticket
		WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$select.=" GROUP BY a.engomado";
		$select.=" ORDER BY a.engomado";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Engomado</th><th>No</th><th>Importe</th>';
			echo '</tr>';
			$t=0;
			$c=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="left">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
				echo '<td align="center">'.number_format($row[2],0).'</td>';
				echo '<td align="right">'.number_format($row[1],2).'</td>';
				
				echo '</tr>';
				$t+=$row[1];
				$c+=$row[2];
			}
			echo '	
				<tr>
				<th align="left" bgcolor="#E9F2F8">';menunavegacion();echo '</th>
				<th align="center" bgcolor="#E9F2F8">'.number_format($c,0).'</th>
				<th align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</th>
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

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT a.engomado,sum(b.monto),count(b.cve) FROM certificados a INNER JOIN cobro_engomado b ON b.plaza=a.plaza AND b.cve = a.ticket
		WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$select.=" GROUP BY a.engomado";
		$select.=" ORDER BY a.engomado";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		$reporte = '';
		if(mysql_num_rows($res)>0) 
		{
			$data=array();
			$legends=array();
			$reporte.= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			$reporte.= '<tr bgcolor="#E9F2F8"><th>Engomado</th><th>No</th><th>Importe</th>';
			$reporte.= '</tr>';
			$t=0;
			$c=0;
			while($row=mysql_fetch_array($res)) {
				$reporte.= rowb(false);
				$reporte.= '<td align="left">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
				$reporte.= '<td align="center">'.number_format($row[2],0).'</td>';
				$reporte.= '<td align="right">'.number_format($row[1],2).'</td>';
				
				$reporte.= '</tr>';
				$t+=$row[1];
				$c+=$row[2];
				$data[]=$row[1];
				$legends[]=$array_engomado[$row['engomado']];
			}
			$reporte.= '	
				<tr>
				<th align="left" bgcolor="#E9F2F8">';menunavegacion();$reporte.= '</th>
				<th align="center" bgcolor="#E9F2F8">'.number_format($c,0).'</th>
				<th align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</th>
				</tr>
			</table>';
			
			if(count($data)>0){
				$data2=array();
				$data2[0] = array('Venta');
				foreach($data as $datos){
					$data2[0][] = $datos;
				}
				//$reporte.='<img src="graficabar.php?fecha_ini='.$_POST['fecha_ini'].'&fecha_fin='.$_POST['fecha_fin'].'&reporte=desglose_cuentas_grupo">';
				require_once("phplot/phplot.php");
				$plot = new PHPlot(1000,800);
				$plot->SetDataValues($data2);
				$plot->SetDataType('text-data');
				$plot->SetPlotType('pie');
				$plot->SetTitle('Estadistica de Entrega de Certificados');
				$plot->SetLegend($legends);
				$plot->SetFileFormat("jpg");
				$plot->SetFailureImage(False);
				//$plot->SetPrintImage(False);
				$plot->SetIsInline(True);
				$plot->SetOutputFile("cfdi/grafica.jpg");
				$plot->SetImageBorderType('plain');
				
				//$plot->SetXDataLabelPos('plotin');
				
				
				//foreach ($data as $row) $plot->SetLegend($row[0]); // Copy labels to legend
				//$plot->SetXTickLabelPos('none');
				//$plot->SetXTickPos('none');
				$plot->DrawGraph();
				$reporte .= '<img src="cfdi/grafica.jpg?'.date("Y-m-d H:i:s").'">';
			}
			
		} else {
			$reporte.= '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
		}
		echo $reporte;
		exit();	
}	



top($_SESSION);



/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'estadisticas_venta.php\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>';
	echo '
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
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
			objeto.open("POST","estadisticas_venta.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

