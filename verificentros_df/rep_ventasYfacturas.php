<?php
include("main.php");
$res = mysql_query("SELECT * FROM plazas WHERE 1 ORDER BY cve");
		while($row=mysql_fetch_array($res)){
			$array_plaza[$row['cve']]=$row['numero'].' -'.$row['nombre'];
			$array_plazanom[$row['cve']]=$row['nombre'];
			$array_plazanum[$row['cve']]=$row['numero'];
		}

if($_POST['cmd']==101){
	require_once('../dompdf/dompdf_config.inc.php');
		$html='<html><head>
      <style type="text/css">
	                    top  lado      ladoiz
		 @page{ margin: 1.5in 0.5in 1px 0.5in;}
		</style>
		 </head><body>';
	

	$res = mysql_query("SELECT SUM(monto) FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'");
	$row = mysql_fetch_array($res);
	$html.= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" style="font-size:27px">';
	$html.= '<tr bgcolo="#E9F2F8" align="center"><td>'.$array_plaza[$_POST['plazausuario']].'</td></tr></table>';
	$html.= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" style="font-size:25px">';
	$html.= '<tr bgcolo="#E9F2F8" align="center"><td>Reporte de Ventas y Facturas</td></tr></table>';
	$html.= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" style="font-size:22px">';
	$html.= '<tr bgcolo="#E9F2F8" align="left"><td>Periodo: '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</td></tr></table>';
	$html.= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" style="font-size:21px">';
	$html.= '<tr bgcolo="#E9F2F8" align="left"><td>Total Venta: '.number_format($row[0],2).'</td></tr><tr><td>&nbsp;</td></tr></table>';
	$html.= '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="" style="font-size:14px">';
	$html.= '<tr bgcolo="#E9F2F8">';
	$html.= '<th>RFC</th><th>Cantidad</th><th>Subtotal</th><th>IVA</th><th>Total</th></tr>';
	$res = mysql_query("SELECT rfc_factura,COUNT(cve), SUM(subtotal),sum(iva),sum(total) FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' AND rfc_factura!='' GROUP BY rfc_factura");
	$totales = array();
	while($row = mysql_fetch_array($res)){
//		rowb();
		$html.= '<tr><td>'.$row[0].'</td>';
		$html.= '<td align="right">'.number_format($row[1],0).'</td>';
		$html.= '<td align="right">'.number_format($row[2],2).'</td>';
		$html.= '<td align="right">'.number_format($row[3],2).'</td>';
		$html.= '<td align="right">'.number_format($row[4],2).'</td>';
		$html.= '</tr>';
		$totales[0]+=$row[1];
		$totales[1]+=$row[2];
		$totales[2]+=$row[3];
		$totales[3]+=$row[4];
	}
	$html.= '<tr bgcolo="#E9F2F8"><td>Totales</td>';
			
	foreach($totales as $k=>$v){
		if($k==0)
			$html.= '<td align="right">&nbsp;'.number_format($v,0).'</td>';
		else
			$html.= '<td align="right">&nbsp;'.number_format($v,2).'</td>';
	}
	$html.= '</tr>';
	$html.= '</table>';
	$html.='</table></body></html>';		
	$mipdf= new DOMPDF();
//	$mipdf->margin: "0";
	//$mipdf->set_paper("A4", "portrait");
	  $mipdf->set_paper("A4", "portrait");
    
//    $mipdf->set_margin("Legal", "landscape");
//	$mipdf->set_paper("Legal", "landscape");
	$mipdf->load_html($html);
	$mipdf->render();
	$mipdf ->stream();
		exit();
}

if($_POST['ajax']==1){

	$res = mysql_query("SELECT SUM(monto) FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'");
	$row = mysql_fetch_array($res);
	echo '<h3>Total Venta: '.number_format($row[0],2).'</h3>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>RFC</th><th>Cantidad</th><th>Subtotal</th><th>IVA</th><th>Total</th></tr>';
	$res = mysql_query("SELECT rfc_factura,COUNT(cve), SUM(subtotal),sum(iva),sum(total) FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' AND rfc_factura!='' GROUP BY rfc_factura");
	$totales = array();
	while($row = mysql_fetch_array($res)){
		rowb();
		echo '<td>'.$row[0].'</td>';
		echo '<td align="right">'.number_format($row[1],0).'</td>';
		echo '<td align="right">'.number_format($row[2],2).'</td>';
		echo '<td align="right">'.number_format($row[3],2).'</td>';
		echo '<td align="right">'.number_format($row[4],2).'</td>';
		echo '</tr>';
		$totales[0]+=$row[1];
		$totales[1]+=$row[2];
		$totales[2]+=$row[3];
		$totales[3]+=$row[4];
	}
	echo '<tr bgcolor="#E9F2F8"><td>Totales</td>';
			
	foreach($totales as $k=>$v){
		if($k==0)
			echo '<td align="right">&nbsp;'.number_format($v,0).'</td>';
		else
			echo '<td align="right">&nbsp;'.number_format($v,2).'</td>';
	}
	echo '</tr>';
	echo '</table>';
	exit();
}

top($_SESSION);




if ($_POST['cmd']<1) {
		
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;
			    <a href="#" onClick="atcr(\'\',\'_blank\',\'101\',\'\')"><img src="images/b_search.png" border="0">&nbsp;Imprimir</a></td>';
	
	echo '</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.substr(fechaLocal(),0,8).'01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	
	echo '</table>';
	
	echo '<div id="Resultados">';
	echo '</div>';

	echo '
	<Script language="javascript">
		
		function buscarRegistros()
		{
			document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
			objeto=crearObjeto();
			if (objeto.readyState != 0) {
				alert("Error: El Navegador no soporta AJAX");
			} else {
				objeto.open("POST","rep_ventasYfacturas.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
				objeto.onreadystatechange = function()
				{
					if (objeto.readyState==4)
					{
						datos = objeto.responseText.split("|");
						document.getElementById("Resultados").innerHTML = datos[0];
					}
				}
			}
			document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
		}
	</script>';


}
bottom();

?>