<?php
include("main.php");

//ARREGLOS

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsUsuario=mysql_query("SELECT * FROM plazas where estatus!='I' ORDER BY numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}

$rsUsuario=mysql_query("SELECT * FROM datosempresas");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazasrfc[$Usuario['plaza']]=$Usuario['rfc'];
	$array_plazaslocalidad[$Usuario['plaza']]=$Usuario['localidad_id'];
}
$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$importe_iva=round($row['precio']*16/116,2);
	$array_engomadoprecio[$row['cve']]=$row['precio']-$importe_iva;
}

$res=mysql_query("SELECT * FROM areas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_localidad[$row['cve']]=$row['nombre'];
}

$array_clientes=array();
$res=mysql_query("SELECT * FROM clientes WHERE plaza='".$_POST['plazausuario']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_clientes[$row['cve']]=$row['nombre'];
	if($row['rfc']=="" || $row['nombre']=="" || $row['calle']=="" || $row['numexterior']=="" || $row['colonia']=="" || $row['municipio']=="" || $row['codigopostal']=="")
		$array_colorcliente[$row['cve']] = "#FF0000";
	else
		$array_colorcliente[$row['cve']] = "#000000";
}
function mestexto($fec){
	global $array_meses;
	$datos=explode("-",$fec);
	return $array_meses[intval($datos[1])].' '.$datos[0];
}
//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);

$abono=0;

if($_POST['cmd']==100){
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") ORDER BY a.numero, a.nombre");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	if(count($array_plazas) == 1){
		foreach($array_plazas as $k=>$v){
			$select= " SELECT a.nombre, COUNT(b.cve) FROM cat_lineas a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.linea WHERE a.plaza='".$k."' ";
			$select .= " AND b.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.estatus!='C'";
			$select.=" GROUP BY a.cve ORDER BY a.nombre";
			$res=mysql_query($select);
			$totalRegistros = mysql_num_rows($res);
			
			
			if(mysql_num_rows($res)>0) 
			{
				$reporte = '<h2>Productividad Lineas de '.$v.' del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';
				$reporte.= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
				$reporte.= '<tr bgcolor="#E9F2F8"><th>Linea</th><th>Cantidad</th>';
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
					$plot->SetTitle('Productividad Lineas');
					$plot->SetLegend($legends);
					$plot->SetFileFormat("jpg");
					$plot->SetFailureImage(False);
					//$plot->SetPrintImage(False);
					$plot->SetIsInline(True);
					$plot->SetOutputFile("grafica.jpg");
					$plot->SetImageBorderType('plain');
					
					//$plot->SetXDataLabelPos('plotin');
					
					
					//foreach ($data as $row) $plot->SetLegend($row[0]); // Copy labels to legend
					//$plot->SetXTickLabelPos('none');
					//$plot->SetXTickPos('none');
					$plot->DrawGraph();
					//$reporte .= '<img src="grafica.jpg?'.date("Y-m-d H:i:s").'">';
				}
				
			} else {
				$reporte.= '
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
				</tr>	  
				</table>';
			}

		}
	}
	else{	
		$reporte = '<h2>Productividad General del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';
		$reporte.= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$reporte.= '<tr bgcolor="#E9F2F8"><th>Plaza</th>';
		$reporte.= '<th>Ingresos</th><th>Porcentaje</th></tr>'; 
		$sumacargo=0;
		$x=0;
		$array_ingresos=array();
		foreach($array_plazas as $k=>$v){
			$res = mysql_query("SELECT SUM(monto) FROM cobro_engomado WHERE plaza='$k' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' AND tipo_pago NOT IN (2,6)");
			$row = mysql_fetch_array($res);
			$array_ingresos[$k]+=$row[0];
			$sumacargo+=$row[0];
			$res = mysql_query("SELECT SUM(a.recuperacion) FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='$k' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C'");
			$row = mysql_fetch_array($res);
			$array_ingresos[$k]+=$row[0];
			$sumacargo+=$row[0];
			$res = mysql_query("SELECT SUM(monto) FROM pagos_caja WHERE plaza='$k' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' AND tipo_pago NOT IN (2,6)");
			$row = mysql_fetch_array($res);
			$array_ingresos[$k]+=$row[0];
			$sumacargo+=$row[0];
			$res = mysql_query("SELECT SUM(a.devolucion) FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='$k' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C'");
			$row = mysql_fetch_array($res);
			$array_ingresos[$k]-=$row[0];
			$sumacargo-=$row[0];
			$res = mysql_query("SELECT SUM(monto) FROM devolucion_ajuste WHERE plaza='$k' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' AND tipo_pago NOT IN (2,6)");
			$row = mysql_fetch_array($res);
			$array_ingresos[$k]-=$row[0];
			$sumacargo-=$row[0];
			$res = mysql_query("SELECT SUM(monto) FROM bonos WHERE plaza='$k' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' AND tipo_pago NOT IN (2,6)");
			$row = mysql_fetch_array($res);
			$array_ingresos[$k]-=$row[0];
			$sumacargo-=$row[0];
		}
		$data=array();
		$legends=array();
		foreach($array_ingresos as $k=>$v){
			$reporte.=rowb(false);
			$reporte.= '<td>'.htmlentities(utf8_encode($array_plazas[$k])).'</td>';
			$reporte.= '<td align="right">'.number_format($v,0).'</td>';
			$reporte.= '<td align="right">'.number_format($v*100/$sumacargo,1).'</td>';
			$reporte.= '</tr>';
			$data[]=$v;
			$legends[]=$array_plazas[$k];
		}
		$c=4;
		$reporte.= '<tr>';
		$reporte.= '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		$reporte.= '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($sumacargo,2).'</td>';
		$reporte.= '<td bgcolor="#E9F2F8" align="right">&nbsp;100%</td>';
		$reporte.= '</tr>';
		$reporte.= '</table>';
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
			$plot->SetTitle('Productividad General');
			$plot->SetLegend($legends);
			$plot->SetFileFormat("jpg");
			$plot->SetFailureImage(False);
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
	}
	echo $reporte;
	exit();
}

if($_POST['ajax']==1){
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") ORDER BY a.numero, a.nombre");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	if(count($array_plazas) == 1){
		foreach($array_plazas as $k=>$v){
			$select= " SELECT a.nombre, COUNT(b.cve) FROM cat_lineas a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.linea WHERE a.plaza='".$k."' ";
			$select .= " AND b.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.estatus!='C'";
			$select.=" GROUP BY a.cve ORDER BY a.nombre";
			$res=mysql_query($select);
			$totalRegistros = mysql_num_rows($res);
			
			
			if(mysql_num_rows($res)>0) 
			{
				$reporte = '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
				$reporte.= '<tr bgcolor="#E9F2F8"><th>Linea</th><th>Cantidad</th>';
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
					$plot->SetTitle('Productividad Lineas');
					$plot->SetLegend($legends);
					$plot->SetFileFormat("jpg");
					$plot->SetFailureImage(False);
					//$plot->SetPrintImage(False);
					$plot->SetIsInline(True);
					$plot->SetOutputFile("grafica.jpg");
					$plot->SetImageBorderType('plain');
					
					//$plot->SetXDataLabelPos('plotin');
					
					
					//foreach ($data as $row) $plot->SetLegend($row[0]); // Copy labels to legend
					//$plot->SetXTickLabelPos('none');
					//$plot->SetXTickPos('none');
					$plot->DrawGraph();
					//$reporte .= '<img src="grafica.jpg?'.date("Y-m-d H:i:s").'">';
				}
				
			} else {
				$reporte.= '
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
				</tr>	  
				</table>';
			}

		}
	}
	else{	
		$reporte = '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$reporte.= '<tr bgcolor="#E9F2F8"><th>Plaza</th>';
		$reporte.= '<th>Ingresos</th><th>Porcentaje</th></tr>'; 
		$sumacargo=0;
		$x=0;
		$array_ingresos=array();
		foreach($array_plazas as $k=>$v){
			$res = mysql_query("SELECT SUM(monto) FROM cobro_engomado WHERE plaza='$k' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' AND tipo_pago NOT IN (2,6)");
			$row = mysql_fetch_array($res);
			$array_ingresos[$k]+=$row[0];
			$sumacargo+=$row[0];
			$res = mysql_query("SELECT SUM(a.recuperacion) FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='$k' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C'");
			$row = mysql_fetch_array($res);
			$array_ingresos[$k]+=$row[0];
			$sumacargo+=$row[0];
			$res = mysql_query("SELECT SUM(monto) FROM pagos_caja WHERE plaza='$k' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' AND tipo_pago NOT IN (2,6)");
			$row = mysql_fetch_array($res);
			$array_ingresos[$k]+=$row[0];
			$sumacargo+=$row[0];
			$res = mysql_query("SELECT SUM(a.devolucion) FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='$k' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C'");
			$row = mysql_fetch_array($res);
			$array_ingresos[$k]-=$row[0];
			$sumacargo-=$row[0];
			$res = mysql_query("SELECT SUM(monto) FROM devolucion_ajuste WHERE plaza='$k' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' AND tipo_pago NOT IN (2,6)");
			$row = mysql_fetch_array($res);
			$array_ingresos[$k]-=$row[0];
			$sumacargo-=$row[0];
			$res = mysql_query("SELECT SUM(monto) FROM bonos WHERE plaza='$k' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' AND tipo_pago NOT IN (2,6)");
			$row = mysql_fetch_array($res);
			$array_ingresos[$k]-=$row[0];
			$sumacargo-=$row[0];
		}
		$data=array();
		$legends=array();
		foreach($array_ingresos as $k=>$v){
			$reporte.=rowb(false);
			$reporte.= '<td>'.htmlentities(utf8_encode($array_plazas[$k])).'</td>';
			$reporte.= '<td align="right">'.number_format($v,0).'</td>';
			$reporte.= '<td align="right">'.number_format($v*100/$sumacargo,1).'</td>';
			$reporte.= '</tr>';
			$data[]=$v;
			$legends[]=$array_plazas[$k];
		}
		$c=4;
		$reporte.= '<tr>';
		$reporte.= '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		$reporte.= '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($sumacargo,2).'</td>';
		$reporte.= '<td bgcolor="#E9F2F8" align="right">&nbsp;100%</td>';
		$reporte.= '</tr>';
		$reporte.= '</table>';
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
			$plot->SetTitle('Productividad General');
			$plot->SetLegend($legends);
			$plot->SetFileFormat("jpg");
			$plot->SetFailureImage(False);
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
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="';
				echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
				echo 'atcr(\'ingresosxplazas.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.substr(fechaLocal(),0,8).'01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Plaza</td><td><select multiple="multiple" name="plazas" id="plazas">';
		$optionsplazas = array();
		foreach($array_plazas as $k=>$v){
			echo '<option value="'.$k.'" selected>'.$v.'</option>';
		}
		echo '</select>';
		echo '<input type="hidden" name="plaza" id="plaza" value=""></td></tr>';
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
	$("#plazas").multipleSelect({
		width: 500
	});	
	function buscarRegistros(){
		document.forma.plaza.value=$("#plazas").multipleSelect("getSelects");
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","ingresosxplazas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&plaza="+document.getElementById("plaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
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