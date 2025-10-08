<?php
include("main.php");

//ARREGLOS

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsUsuario=mysql_query("SELECT a.* FROM plazas a inner join datosempresas b on a.cve = b.plaza where a.estatus!='I' ORDER BY b.localidad_id, a.lista, a.numero");
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
if($_POST['cmd']==101){
 ob_end_clean();
require("fpdf153/fpdf.php");
$pdf = new PDF('L','mm','LETTER');
$pdf->AliasNbPages();
    $array_plaza=array();
	$pdf->AddPage();
	//global $row,$array_motivo,$array_plaza;
  $pdf->SetFont('Arial','B',14);
  $pdf->Cell(260,10,'Facturacion de Plazas del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'],0,0,'L');
  $pdf->Ln();
  $pdf->Cell(260,10,''.fechaLocal().' '.horaLocal(),0,0,'L');
  $array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.rfc LIKE '%".$_POST['rfc']."%' ORDER BY b.localidad_id, a.lista, a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$c=13;
	if($_POST['cveusuario']==1) $c++;
  $pdf->Ln();
  $pdf->SetFont('Arial','B',7);
  $pdf->Cell(103,10,'Plaza',1,0,'C');
  $pdf->Cell(15,10,'No de',1,0,'C');
  $pdf->Cell(20,10,'Subtotal',1,0,'C');
  $pdf->Cell(19,10,'IVA',1,0,'C');
  $pdf->Cell(20,10,'Total',1,0,'C');
  $pdf->Cell(25,10,'No Canceladas',1,0,'C');
  $pdf->Cell(20,10,'Facturas',1,0,'C');
  $pdf->Cell(28,10,'Facturas sin ',1,0,'C');
  $pdf->Cell(15,10,'Timbres',1,0,'C');
  $pdf->SetY(35); 
  $pdf->Cell(117,5,'',0,0,'C');
  $pdf->Cell(15,5,'Facturas',0,0,'C');
  $pdf->Cell(45,5,'',0,0,'C');
  $pdf->Cell(25,5,'Canceladas',0,0,'C');
  $pdf->Cell(20,5,'sin Timbrar',0,0,'C');
  $pdf->Cell(28,5,'Timbrar Canceladas',0,0,'C');
  $pdf->Cell(15,5,'Ocupados',0,0,'C');
  $pdf->Ln();
  $sumacargo=array(0,0,0,0,0,0);
	$x=0;
	foreach($array_plazas as $k=>$v){
		//if($_POST['rfc']=='' || $_POST['rfc']==$array_plazasrfc[$k]){
		//	if($_POST['plaza']=='all' || $k==$_POST['plaza']){
				
				$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.subtotal,0)),SUM(IF(a.estatus!='C',a.iva,0)),
				SUM(IF(a.estatus!='C',a.total,0)),SUM(IF(a.estatus='C' AND DATE(a.fechacan)>='".$_POST['fecha_ini']."' AND DATE(a.fechacan)<='".$_POST['fecha_fin']."',1,0)),
				SUM(IF(a.respuesta1='',1,0)),SUM(IF(a.respuesta1='' && a.estatus='C' AND DATE(a.fechacan)>='".$_POST['fecha_ini']."' AND DATE(a.fechacan)<='".$_POST['fecha_fin']."',1,0)),
				SUM(IF(a.respuesta1!='' AND DATE(a.fechatimbre)>='".$_POST['fecha_ini']."' AND DATE(a.fechatimbre)<='".$_POST['fecha_fin']."',1,0)),
				SUM(IF(a.respuesta2!='' AND DATE(a.fechacan)>='".$_POST['fecha_ini']."' AND DATE(a.fechacan)<='".$_POST['fecha_fin']."',1,0))
				FROM facturas as a WHERE a.plaza='".$k."' AND ((DATE(a.fechatimbre)>='".$_POST['fecha_ini']."' AND DATE(a.fechatimbre)<='".$_POST['fecha_fin']."') OR (DATE(a.fechacan)>='".$_POST['fecha_ini']."' AND DATE(a.fechacan)<='".$_POST['fecha_fin']."')) ";
				$res=mysql_query($select) or die(mysql_error());
				$row=mysql_fetch_array($res);
				$pdf->Cell(103,5,''.htmlentities(utf8_encode($v)),1,0,'L');
				$pdf->Cell(15,5,''.number_format($row[0],0),1,0,'R');
				$pdf->Cell(20,5,''.number_format($row[1],2),1,0,'R');
				$pdf->Cell(19,5,''.number_format($row[2],2),1,0,'R');
				$pdf->Cell(20,5,''.number_format($row[3],2),1,0,'R');
				$pdf->Cell(25,5,''.number_format($row[4],0),1,0,'R');
				$pdf->Cell(20,5,''.number_format($row[5],0),1,0,'R');
				$pdf->Cell(28,5,''.number_format($row[6],0),1,0,'R');
				$pdf->Cell(15,5,''.number_format($row[7]+$row[8],0),1,0,'R');
				$x++;
				$sumacargo[0]+=$row[0];
				$sumacargo[1]+=$row[1];
				$sumacargo[2]+=$row[2];
				$sumacargo[3]+=$row[3];
				$sumacargo[4]+=$row[4];
				$sumacargo[5]+=$row[5];
				$sumacargo[6]+=$row[6];
				$sumacargo[7]+=$row[7]+$row[8];
				$pdf->Ln();
		//	}
		//}
	}
	$c=4;
	$pdf->Cell(103,5,'Total',0,0,'R');
	$cel=array (15,20,19,20,25,20,28,15);
	foreach($sumacargo as $k=>$v){
		if($k>0 && $k<4) $decimal=2;
		else $decimal=0;
		$pdf->Cell($cel[$k],5,''.number_format($v,$decimal),1,0,'R');
	}
  
 
  $pdf->Cell(57,5,'',0,0,'R');
  $pdf->Cell(25,5,'Total ',0,0,'R');
  $pdf->Cell(25,5,'',0,0,'R');
  $pdf->Cell(25,5,'',0,0,'R');
  $pdf->Cell(25,5,'',0,0,'R');
  $pdf->Cell(25,5,'',0,0,'R');
  $pdf->Cell(25,5,'',0,0,'R');
  $pdf->Cell(28,5,'',0,0,'R');
  $pdf->Cell(25,5,'',0,0,'R');
  
  $pdf->Output();
  $exit();
  
}
if($_POST['cmd']==100){
	echo '<h1>Facturacion de Plazas del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h1>';
	echo fechaLocal().' '.horaLocal().'<br>';
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.rfc LIKE '%".$_POST['rfc']."%' ORDER BY b.localidad_id, a.lista, a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	if($_POST['cveusuario']==1) $c++;
	echo '<tr bgcolor="#E9F2F8"><th>Plaza</th>';
	echo '<th>No de Facturas</th><th>Subtotal</th>
	<th>IVA</th><th>Total</th><th>No Canceladas</th>
	<th>Facturas sin Timbrar</th><th>Facturas sin Timbrar Canceladas</th><th>Timbres Ocupados</th></tr>'; 
	$sumacargo=array(0,0,0,0,0,0);
	$x=0;
	foreach($array_plazas as $k=>$v){
		//if($_POST['rfc']=='' || $_POST['rfc']==$array_plazasrfc[$k]){
		//	if($_POST['plaza']=='all' || $k==$_POST['plaza']){
				rowb();
				$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.subtotal,0)),SUM(IF(a.estatus!='C',a.iva,0)),
				SUM(IF(a.estatus!='C',a.total,0)),SUM(IF(a.estatus='C' AND DATE(a.fechacan)>='".$_POST['fecha_ini']."' AND DATE(a.fechacan)<='".$_POST['fecha_fin']."',1,0)),
				SUM(IF(a.respuesta1='',1,0)),SUM(IF(a.respuesta1='' && a.estatus='C' AND DATE(a.fechacan)>='".$_POST['fecha_ini']."' AND DATE(a.fechacan)<='".$_POST['fecha_fin']."',1,0)),
				SUM(IF(a.respuesta1!='' AND DATE(a.fechatimbre)>='".$_POST['fecha_ini']."' AND DATE(a.fechatimbre)<='".$_POST['fecha_fin']."',1,0)),
				SUM(IF(a.respuesta2!='' AND DATE(a.fechacan)>='".$_POST['fecha_ini']."' AND DATE(a.fechacan)<='".$_POST['fecha_fin']."',1,0))
				FROM facturas as a WHERE a.plaza='".$k."' AND ((DATE(a.fechatimbre)>='".$_POST['fecha_ini']."' AND DATE(a.fechatimbre)<='".$_POST['fecha_fin']."') OR (DATE(a.fechacan)>='".$_POST['fecha_ini']."' AND DATE(a.fechacan)<='".$_POST['fecha_fin']."')) ";
				$res=mysql_query($select) or die(mysql_error());
				$row=mysql_fetch_array($res);
				echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
				echo '<td align="right">'.number_format($row[0],0).'</td>';
				echo '<td align="right">'.number_format($row[1],2).'</td>';
				echo '<td align="right">'.number_format($row[2],2).'</td>';
				echo '<td align="right">'.number_format($row[3],2).'</td>';
				echo '<td align="right">'.number_format($row[4],0).'</td>';
				echo '<td align="right">'.number_format($row[5],0).'</td>';
				echo '<td align="right">'.number_format($row[6],0).'</td>';
				echo '<td align="right">'.number_format($row[7]+$row[8],0).'</td>';
				echo '</tr>';
				$x++;
				$sumacargo[0]+=$row[0];
				$sumacargo[1]+=$row[1];
				$sumacargo[2]+=$row[2];
				$sumacargo[3]+=$row[3];
				$sumacargo[4]+=$row[4];
				$sumacargo[5]+=$row[5];
				$sumacargo[6]+=$row[6];
				$sumacargo[7]+=$row[7]+$row[8];
		//	}
		//}
	}
	$c=4;
	echo '<tr>';
	echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		if($k>0 && $k<4) $decimal=2;
		else $decimal=0;
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==1){
	$filtro="";
	/*$select= " SELECT a.* FROM facturas as a WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
	if($_POST['plaza']!="all") $select.=" AND a.plaza='".$_POST['plaza']."'";
	$rsabonos=mysql_query($select) or die(mysql_error());*/
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.rfc LIKE '%".$_POST['rfc']."%' ORDER BY b.localidad_id, a.lista, a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	if($_POST['cveusuario']==1) $c++;
	echo '<tr bgcolor="#E9F2F8"><th>Plaza</th>';
	echo '<th>No de Facturas</th><th>Subtotal</th>
	<th>IVA</th><th>Total</th><th>No Canceladas</th>
	<th>Facturas sin Timbrar</th><th>Facturas sin Timbrar Canceladas</th><th>Timbres Ocupados</th></tr>'; 
	$sumacargo=array(0,0,0,0,0,0);
	$x=0;
	foreach($array_plazas as $k=>$v){
		//if($_POST['rfc']=='' || $_POST['rfc']==$array_plazasrfc[$k]){
		//	if($_POST['plaza']=='all' || $k==$_POST['plaza']){
				rowb();
				/*$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.subtotal,0)),SUM(IF(a.estatus!='C',a.iva,0)),
				SUM(IF(a.estatus!='C',a.total,0)),SUM(IF(a.estatus='C',1,0)),
				SUM(IF(a.respuesta1='',1,0)),SUM(IF(a.respuesta1='' && a.estatus='C',1,0)),
				SUM(IF(a.respuesta2!='',2,IF(a.respuesta1!='',1,0))) 
				FROM facturas as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";*/
				$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.subtotal,0)),SUM(IF(a.estatus!='C',a.iva,0)),
				SUM(IF(a.estatus!='C',a.total,0)),SUM(IF(a.estatus='C' AND DATE(a.fechacan)>='".$_POST['fecha_ini']."' AND DATE(a.fechacan)<='".$_POST['fecha_fin']."',1,0)),
				SUM(IF(a.respuesta1='',1,0)),SUM(IF(a.respuesta1='' && a.estatus='C' AND DATE(a.fechacan)>='".$_POST['fecha_ini']."' AND DATE(a.fechacan)<='".$_POST['fecha_fin']."',1,0)),
				SUM(IF(a.respuesta1!='' AND DATE(a.fechatimbre)>='".$_POST['fecha_ini']."' AND DATE(a.fechatimbre)<='".$_POST['fecha_fin']."',1,0)),
				SUM(IF(a.respuesta2!='' AND DATE(a.fechacan)>='".$_POST['fecha_ini']."' AND DATE(a.fechacan)<='".$_POST['fecha_fin']."',1,0))
				FROM facturas as a WHERE a.plaza='".$k."' AND ((DATE(a.fechatimbre)>='".$_POST['fecha_ini']."' AND DATE(a.fechatimbre)<='".$_POST['fecha_fin']."') OR (DATE(a.fechacan)>='".$_POST['fecha_ini']."' AND DATE(a.fechacan)<='".$_POST['fecha_fin']."')) ";
				$res=mysql_query($select) or die(mysql_error());
				$row=mysql_fetch_array($res);
				echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
				echo '<td align="right">'.number_format($row[0],0).'</td>';
				echo '<td align="right">'.number_format($row[1],2).'</td>';
				echo '<td align="right">'.number_format($row[2],2).'</td>';
				echo '<td align="right">'.number_format($row[3],2).'</td>';
				echo '<td align="right">'.number_format($row[4],0).'</td>';
				echo '<td align="right">'.number_format($row[5],0).'</td>';
				echo '<td align="right">'.number_format($row[6],0).'</td>';
				echo '<td align="right">'.number_format($row[7]+$row[8],0).'</td>';
				echo '</tr>';
				$x++;
				$sumacargo[0]+=$row[0];
				$sumacargo[1]+=$row[1];
				$sumacargo[2]+=$row[2];
				$sumacargo[3]+=$row[3];
				$sumacargo[4]+=$row[4];
				$sumacargo[5]+=$row[5];
				$sumacargo[6]+=$row[6];
				$sumacargo[7]+=$row[7]+$row[8];
		//	}
		//}
	}
	$c=4;
	echo '<tr>';
	echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		if($k>0 && $k<4) $decimal=2;
		else $decimal=0;
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';
	echo '</table>';
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
				if($_POST['plazausuario']==0) echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
				echo 'atcr(\'facturasxplazas.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>';
				echo'<td><a href="#" onclick="';
				//if($_POST['plazausuario']==0) echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
				echo 'atcr(\'facturasxplazas.php\',\'_blank\',101,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.substr(fechaLocal(),0,8).'01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		if($_POST['plazausuario']>0){
			echo '<tr><td>Plaza</td><td>'.$array_plazas[$_POST['plazausuario']].'<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'"><input type="hidden" name="localidad" id="localidad" value="all"></td></tr>';
		}
		else{
			echo '<tr><td align="left">Localidad</td><td><select name="localidad" id="localidad" onChange="muestraplazas()"><option value="all">Todas</option>';
			foreach($array_localidad as $k=>$v){
				echo '<option value="'.$k.'"';
				echo '>'.$v.'</option>';
			}
			echo '</select>';
			echo '<tr><td align="left">Plaza</td><td><select multiple="multiple" name="plazas" id="plazas">';
			$optionsplazas = array();
			foreach($array_plazas as $k=>$v){
				echo '<option value="'.$k.'" selected>'.$v.'</option>';
				$optionsplazas['all'] .= '<option value="'.$k.'" selected>'.$v.'</option>';
				$optionsplazas[$array_plazaslocalidad[$k]] .= '<option value="'.$k.'" selected>'.$v.'</option>';
			}
			echo '</select>';
			echo '<input type="hidden" name="plaza" id="plaza" value=""></td></tr>';
		}
		echo '<tr><td>RFC</td><td><input type="text" size="20" name="rfc" id="rfc" class="textField"></td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
bottom();
echo '
<Script language="javascript">';
if($_POST['plazausuario']==0){
	echo '
	$("#plazas").multipleSelect({
		width: 500
	});	
	function buscarRegistros(){
		document.forma.plaza.value=$("#plazas").multipleSelect("getSelects");
	';
}
else{
	echo 'function buscarRegistros(){
	';
}
echo '  document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","facturasxplazas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&rfc="+document.getElementById("rfc").value+"&plaza="+document.getElementById("plaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	function muestraplazas(){

		';

		foreach($optionsplazas as $k=>$v){
			echo '
				if(document.forma.localidad.value == "'.$k.'"){
					$("#plazas").html(\''.$v.'\');
				}
			';
		}

	echo '	

		$("#plazas").multipleSelect("refresh");	
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