<?php
include("main.php");

$res = mysql_query("SELECT * FROM anios_certificados WHERE 1 ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
}

$rsUsuario=mysql_query("SELECT * FROM plazas where estatus!='I' ORDER BY numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}

$localidadplaza = 0;


$rsUsuario=mysql_query("SELECT * FROM datosempresas");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazaslocalidad[$Usuario['plaza']]=$Usuario['localidad_id'];
	if($Usuario['plaza'] == $_POST['plazausuario']) $localidadplaza = $Usuario['localidad_id'];
}

$res=mysql_query("SELECT * FROM areas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_localidad[$row['cve']]=$row['nombre'];
}
$res=mysql_query("SELECT * FROM motivos_cancelacion_certificados ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_motivo_cancelacion[$row['cve']]=$row['nombre'];
}


$array_engomado = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' ORDER BY nombre");
$res = mysql_query("SELECT numero, nombre, max(precio) as precio, group_concat(cve) as cves FROM engomados WHERE localidad='".$_POST['localidad']."' AND entrega=1 GROUP BY numero ORDER BY numero");
while($row=mysql_fetch_array($res)){
	$res1=mysql_query("SELECT nombre,precio,abreviatura FROM engomados WHERE localidad = '".$_POST['localidad']."'  AND numero='".$row['numero']."' AND entrega=1 ORDER BY cve");
	$row1=mysql_fetch_array($res1);
	$array_engomado['normal'][$row['numero']]['nombre']=$row1['nombre'];
	$array_engomado['normal'][$row['numero']]['cves']=$row['cves'];
	$array_engomado['normal'][$row['numero']]['total']=0;
}

$array_motivos_intento = array();
$res = mysql_query("SELECT * FROM motivos_intento WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_motivos_intento[$row['cve']]=$row['nombre'];
}
$res = mysql_query("SELECT * FROM engomados WHERE cve in (1,2,3,4,5,19) ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomados[$row['cve']]=$row['nombre'];
}

if($_POST['cmd']==100){
require_once('../dompdf/dompdf_config.inc.php');
		$html='<html><head>
      <style type="text/css">
	                    @page{ margin: 0px 0.5in 1px 1.3in;}
		</style>
		<h1 align="center">Plaza: '.$array_plazas[$_POST['plazausuario']].'</h2><h3 align="right">Fecha: '.fechaLocal().'-'.horaLocal().'</h3></br>
		<h2>Inventario del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>
		 </head><body>';
	$html.= '<table width="100%" border="1" cellpadding="4"class="" style="font-size:13px">';
	$html.= '<tr><th bgcolo="#E9F2F8" width="230">Motivo</th>';
	foreach($array_engomados as $k=>$v) {$html.= '<th bgcolo="#E9F2F8">'.$v.'</th>';}
	$html.= '<th bgcolo="#E9F2F8">Total</th>';
	$html.= '</tr>';
	
	$total = 0;
//	if($_POST['fecha_ini']<'2015-05-01') {$_POST['fecha_ini'] = '2015-05-01';}
//	if($_POST['fecha_fin']<'2015-05-01') {$_POST['fecha_fin'] = '2015-05-01';}
	$array_plazas=array();

	/*$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].")");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}*/
	$tot=array();
	$tott=0;
	foreach($array_motivo_cancelacion as $k1=>$v1){
		$html.='<tr>';
		$html.= '<td align="left">'.htmlentities(utf8_encode($v1)).'</td>';
		$c=0;
		/*foreach($array_engomado['normal'] as $k=>$v){
			$array_engomado['normal'][$k]['cant'] = 0;
			$select = "SELECT cve
			FROM certificados_cancelados
			WHERE motivo = '".$k1."' and plaza = '".$_POST['plazausuario']."' and engomado = '".$k."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus != 'C' ";
//			if($_POST['anio'] != 'all') $select .= " AND anio = '".$_POST['anio']."'";
			$res = mysql_query($select) or die(mysql_error());
			$fcertificado=-1;
			while($row = mysql_fetch_array($res)){
				$array_engomado['normal'][$k]['cant']++;
				$array_engomado['normal'][$k]['total']++;
			}
		}*/
		$total = 0;
		foreach($array_engomados as $k=>$v){
		$select = "SELECT COUNT(cve) AS cant
			FROM certificados_cancelados
			WHERE motivo = '".$k1."' and plaza = '".$_POST['plazausuario']."' and engomado = '".$k."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus != 'C' ";
        $res = mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
			$html.= '<td align="center">'.$row['cant'].'</td>';
			$total+=$row['cant'];
			$tot[$k]+=$row['cant'];
		}
		$html.= '<td align="center">'.$total.'</td>';
		$tott+=$total;
		$html.= '</tr>';
	}
	$html.= '<tr><th align="left" bgcolo="#E9F2F8">Total</th>';
	$total = 0;
	foreach($array_engomados as $k=>$v){
		$html.= '<th bgcolo="#E9F2F8">'.$tot[$k].'</th>';
	}
	$html.= '<th bgcolo="#E9F2F8">'.$tott.'</th>';
	$html.= '</tr>';
	$html.= '</table></body></html>';
	$mipdf= new DOMPDF();
//	$mipdf->margin: "0";
	//$mipdf->set_paper("A4", "portrait");
//	$mipdf->set_paper("A4", "portrait");
    
//    $mipdf->set_margin("Legal", "landscape");
	$mipdf->set_paper("Legal", "landscape");
	$mipdf->load_html($html);
	$mipdf->render();
	$mipdf ->stream();
	exit();	
}

if($_POST['ajax']==1){

	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr><th bgcolor="#E9F2F8" width="230">Motivo</th>';
	foreach($array_engomados as $k=>$v) echo '<th bgcolor="#E9F2F8">'.$v.'</th>';
	echo '<th bgcolor="#E9F2F8">Total</th>';
	echo '</tr>';
	
	$total = 0;
//	if($_POST['fecha_ini']<'2015-05-01') $_POST['fecha_ini'] = '2015-05-01';
//	if($_POST['fecha_fin']<'2015-05-01') $_POST['fecha_fin'] = '2015-05-01';
	$array_plazas=array();

	/*$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].")");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}*/
	$tot=array();
	$tott=0;
	$indice=0;
	$indice1=1;
	$gd=array();
	$ge=array();
	foreach($array_motivo_cancelacion as $k1=>$v1){
		rowb();
		
		echo '<td align="left">'.htmlentities(utf8_encode($indice1."-".$v1)).'</td>';
		$c=0;
		/*foreach($array_engomado['normal'] as $k=>$v){
			$array_engomado['normal'][$k]['cant'] = 0;
			$select = "SELECT cve
			FROM certificados_cancelados
			WHERE motivo = '".$k1."' and plaza = '".$_POST['plazausuario']."' and engomado = '".$k."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus != 'C' ";
//			if($_POST['anio'] != 'all') $select .= " AND anio = '".$_POST['anio']."'";
			$res = mysql_query($select) or die(mysql_error());
			$fcertificado=-1;
			while($row = mysql_fetch_array($res)){
				$array_engomado['normal'][$k]['cant']++;
				$array_engomado['normal'][$k]['total']++;
			}
		}*/
		$total = 0;

		foreach($array_engomados as $k=>$v){
		$select = "SELECT COUNT(cve) AS cant
			FROM certificados_cancelados
			WHERE motivo = '".$k1."' and plaza = '".$_POST['plazausuario']."' and engomado = '".$k."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus != 'C' ";
        $res = mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
			echo '<td align="center">'.$row['cant'].'</td>';
			$total+=$row['cant'];
			$tot[$k]+=$row['cant'];
		}
		echo '<td align="center">'.$total.'</td>';
		if($total!=0){
		$gd[$indice]=$total;
		$ge[$indice]=$indice1;
		}
		$tott+=$total;
		$indice++;
		$indice1++;
		echo '</tr>';
	}
	echo '<tr><th align="left" bgcolor="#E9F2F8">Total</th>';
	$total = 0;
	foreach($array_engomados as $k=>$v){
		echo '<th bgcolor="#E9F2F8">'.$tot[$k].'</th>';
	}
	echo '<th bgcolor="#E9F2F8">'.$tott.'</th>';
	echo '</tr>';
	//print_r($gd);
	//print_r($ge);
	echo '</table>';
	
// Standard inclusions   
 include("../graficas/class/pData.class");
 include("../graficas/class/pChart.class");
$dir="../graficas/graficapastel.png?time=" . rand();

 // Dataset definition 
 $DataSet = new pData;
 $DataSet->AddPoint($gd,"Serie1");
 $DataSet->AddPoint($ge,"Serie2");
 $DataSet->AddAllSeries();
 $DataSet->SetAbsciseLabelSerie("Serie2");

 // Initialise the graph
 $Test = new pChart(600,250);
 $Test->drawFilledRoundedRectangle(7,7,413,243,5,240,240,240);
 $Test->drawRoundedRectangle(5,5,415,245,5,230,230,230);
 $Test->createColorGradientPalette(195,204,56,223,110,41,5);

 // Draw the pie chart
 $Test->setFontProperties("../graficas/Fonts/tahoma.ttf",8);
 $Test->AntialiasQuality = 0;
//$Test->drawPieGraph($DataSet->GetData(),PIE_PERCENTAGE_LABEL,FALSE,50,20,5);
$Test->drawPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),180,130,110,PIE_PERCENTAGE_LABEL,FALSE,50,20,5);
 $Test->drawPieLegend(330,15,$DataSet->GetData(),$DataSet->GetDataDescription(),250,250,250);

 // Write the title
 //$Test->setFontProperties("../graficas/Fonts/MankSans.ttf",10);
 //$Test->drawTitle(10,20,"Sales per month",100,100,100);
$ran=rand();
 $Test->Render("../graficas/graficapastel.png".$ran);
 $DataSet->removeAllSeries();
 //$Test->Stroke();
 if($_POST[cveusuario]==	1){
//echo ("<img src=\"../graficas/graficapastel.png\">");
echo ("<img src=\"../graficas/graficapastel.png".$ran."\">");
 $dir="../graficas/graficapastel.png".$ran."";
// unlink($dir);
}
$gd=array();
$ge=array();
	exit();	
}

top($_SESSION);

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick=" buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<!--<td><a href="#" onclick="if(document.forma.localidad.value==\'all\') alert(\'Necesita seleccionar la localidad\'); else{ document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');atcr(\'certificadoscanceladosxplaza.php\',\'_blank\',100,0);}"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir</td><td>&nbsp;</td>-->
			<td><a href="#" onclick="atcr(\'reportecancelados.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Fin</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<!--<tr><td align="left">Localidad</td><td><select name="localidad" id="" >';
	foreach($array_localidad as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$localidadplaza) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>-->';
	echo '<input type="hidden" id="plazausuario" name="plazausuario" value="'.$_POST['plazausuario'].'"></table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';





/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros(){
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","reportecancelados.php",true);
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
	</Script>
	';

	
}
	
bottom();
?>