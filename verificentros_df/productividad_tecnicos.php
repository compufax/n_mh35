<?php 

include ("main.php"); 

$res=mysql_query("SELECT local FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT a.nombre, COUNT(b.cve) FROM tecnicos a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.tecnico WHERE a.plaza='".$_POST['plazausuario']."' ";
		$select .= " AND b.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.estatus!='C'";
		$select.=" GROUP BY a.cve ORDER BY a.nombre";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			$reporte.= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			$reporte.= '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			$reporte.= '<tr bgcolor="#E9F2F8"><th>Tecnico</th><th>Cantidad</th>';
			$reporte.= '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$x++;
			$data=array();
			$legends=array();
			while($row=mysql_fetch_array($res)) {
				$reporte.=rowb(false);
				$reporte.= '<td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
				$reporte.= '<td align="right">'.number_format($row[1],0).'</td>';
				$reporte.= '</tr>';
				$total+=$row[1];
				$x++;
				$data[]=$row[1];
				$legends[]=$row['nombre'];
			}
			$reporte.= '	
				<tr>
				<td bgcolor="#E9F2F8">'.$x.' Registro(s)</td>
				<td bgcolor="#E9F2F8" align="right">'.number_format($total,0).'</td>
				</tr>
			</table>';


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
				$plot->SetTitle('Productividad Tecnicos');
				$plot->SetLegend($legends);
				$plot->SetFileFormat("jpg");
				$plot->SetFailureImage(False);
				//$plot->SetPrintImage(False);
				$plot->SetIsInline(True);
				$plot->SetOutputFile("../cfdi/grafica.jpg");
				$plot->SetImageBorderType('plain');
				
				//$plot->SetXDataLabelPos('plotin');
				
				
				//foreach ($data as $row) $plot->SetLegend($row[0]); // Copy labels to legend
				//$plot->SetXTickLabelPos('none');
				//$plot->SetXTickPos('none');
				$plot->DrawGraph();
				$reporte .= '<img src="../cfdi/grafica.jpg?'.date("Y-m-d H:i:s").'">';
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
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		
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

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","productividad_tecnicos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
	window.onload = function () {
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
	
	</Script>
';

?>

