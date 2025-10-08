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
}
$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$importe_iva=round($row['precio']*16/116,2);
	$array_engomadoprecio[$row['cve']]=$row['precio']-$importe_iva;
}

$array_clientes=array();
$res=mysql_query("SELECT * FROM clientes WHERE 1 ORDER BY nombre");
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
	require_once('../excel/Worksheet.php');
	require_once('../excel/Workbook.php');
	function HeaderingExcel($filename) {
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$filename" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}		
	// HTTP headers
	HeaderingExcel('reporte_facturas.xls');
	// Creating a workbook
	$workbook = new Workbook("-");
	// Creating the first worksheet
	$worksheet1 =& $workbook->add_worksheet('Facturas');
	$normal =& $workbook->add_format();
	$normal->set_align('left');
	$normal->set_align('vjustify');
	$worksheet1->write_string(0,0,'Facturacion de Plazas del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'');
	$worksheet1->write_string(1,0,fechaLocal().' '.horaLocal());
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.rfc LIKE '%".$_POST['rfc']."%'");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	
	$c=0;
	$l=3;
	$worksheet1->write_string($l,$c,'Plaza');$c++;
	$worksheet1->write_string($l,$c,'Folio');$c++;
	$worksheet1->write_string($l,$c,'Fecha');$c++;
	$worksheet1->write_string($l,$c,'Cliente');$c++;
	$worksheet1->write_string($l,$c,'Tipo Pago');$c++;
	$worksheet1->write_string($l,$c,'Subtotal');$c++;
	$worksheet1->write_string($l,$c,'IVA');$c++;
	$worksheet1->write_string($l,$c,'Total');$c++;
	$worksheet1->write_string($l,$c,'Estatus');$c++;
	$worksheet1->write_string($l,$c,'Timbrada');$c++;
	$l++;
	$sumacargo=array(0,0,0,0,0,0);
	$x=0;
	foreach($array_plazas as $k=>$v){
		//if($_POST['rfc']=='' || $_POST['rfc']==$array_plazasrfc[$k]){
		//	if($_POST['plaza']=='all' || $k==$_POST['plaza']){
				$sumacargop=array(0,0,0);
				$xp=0;
				$select= " SELECT *
				FROM facturas as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
				$res=mysql_query($select) or die(mysql_error());
				while($Abono=mysql_fetch_array($res)){
					$estatus='Activa';
					$timbre='Si';
					if($Abono['respuesta1']=='') $timbre='No';
					if($Abono['estatus']=='C'){
						$estatus='Cancelada';
						$Abono['subtotal']=0;
						$Abono['iva']=0;
						$Abono['total']=0;
						$Abono['iva_retenido']=0;
					}
					$c=0;
					$worksheet1->write_string($l,$c,$v);$c++;
					$worksheet1->write_string($l,$c,$Abono['cve']);$c++;
					$worksheet1->write_string($l,$c,$Abono['fecha'].' '.$Abono['hora']);$c++;
					$worksheet1->write_string($l,$c,$array_clientes[$Abono['cliente']]);$c++;
					$worksheet1->write_string($l,$c,$array_tipo_pago[$Abono['tipo_pago']]);$c++;
					$worksheet1->write_string($l,$c,$Abono['subtotal']);$c++;
					$worksheet1->write_string($l,$c,$Abono['iva']);$c++;
					$worksheet1->write_string($l,$c,($Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido']));$c++;
					$worksheet1->write_string($l,$c,$estatus);$c++;
					$worksheet1->write_string($l,$c,$timbre);$c++;
					$l++;
					$x++;
					$sumacargo[0]+=$Abono['subtotal'];
					$sumacargo[1]+=$Abono['iva'];
					$sumacargo[2]+=$Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'];
					$xp++;
					$sumacargop[0]+=$Abono['subtotal'];
					$sumacargop[1]+=$Abono['iva'];
					$sumacargop[2]+=$Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'];
				}
				$c=0;
				$worksheet1->write_string($l,$c,$v);$c++;
				$worksheet1->write_string($l,$c,$xp.' Registro(s)');$c++;
				$c=5;
				foreach($sumacargop as $k=>$v){
					$worksheet1->write_string($l,$c,$v);$c++;
				}
				$l++;
				
		//	}
		//}
	}
	$c=0;
	$worksheet1->write_string($l,$c,$x.' Registro(s)');$c++;
	$c=5;
	foreach($sumacargo as $k=>$v){
		$worksheet1->write_string($l,$c,$v);$c++;
	}
	$l++;
	$workbook->close();
	exit();
}

if($_POST['ajax']==1){
	$filtro="";
	/*$select= " SELECT a.* FROM facturas as a WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
	if($_POST['plaza']!="all") $select.=" AND a.plaza='".$_POST['plaza']."'";
	$rsabonos=mysql_query($select) or die(mysql_error());*/
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.rfc LIKE '%".$_POST['rfc']."%'");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	if($_POST['cveusuario']==1) $c++;
	echo '<tr bgcolor="#E9F2F8"><th>Plaza</th>';
	echo '<th>Folio</th><th>Fecha</th><th>Cliente</th><th>Tipo Pago</th><th>Subtotal</th>
	<th>IVA</th><th>Total</th><th>Estatus</th><th>Timbrada</th></tr>'; 
	$sumacargo=array(0,0,0);
	$x=0;
	foreach($array_plazas as $k=>$v){
		//if($_POST['rfc']=='' || $_POST['rfc']==$array_plazasrfc[$k]){
		//	if($_POST['plaza']=='all' || $k==$_POST['plaza']){
				$sumacargop=array(0,0,0);
				$xp=0;
				rowb();
				$select= " SELECT *
				FROM facturas as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
				$res=mysql_query($select) or die(mysql_error());
				while($Abono=mysql_fetch_array($res)){
					if($Abono['estatus']=='C'){
						$Abono['subtotal']=0;
						$Abono['iva']=0;
						$Abono['total']=0;
						$Abono['iva_retenido']=0;
					}
					echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
					echo '<td align="center">'.$Abono['cve'].'</td>';
					echo '<td align="center">'.$Abono['fecha'].' '.$Abono['hora'].'</td>';	
					echo '<td>'.htmlentities(utf8_encode($array_clientes[$Abono['cliente']])).'</td>';
					echo '<td>'.htmlentities($array_tipo_pago[$Abono['tipo_pago']]).'</td>';
					echo '<td align="right">'.number_format($Abono['subtotal'],2).'</td>';
					echo '<td align="right">'.number_format($Abono['iva'],2).'</td>';
					echo '<td align="right">'.number_format($Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'],2).'</td>';
					echo '<td align="center">';if($Abono['estatus']=='C') echo 'CANCELADO'; else echo 'ACTIVA'; echo '</td>';
					echo '<td align="center">';if($Abono['respuesta1']=='') echo 'NO'; else echo 'SI'; echo '</td>';
					echo '</tr>';
					$x++;
					$sumacargo[0]+=$Abono['subtotal'];
					$sumacargo[1]+=$Abono['iva'];
					$sumacargo[2]+=$Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'];
					$xp++;
					$sumacargop[0]+=$Abono['subtotal'];
					$sumacargop[1]+=$Abono['iva'];
					$sumacargop[2]+=$Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'];
				}
				echo '<tr><td bgcolor="#E9F2F8">'.htmlentities(utf8_encode($v)).'</td>
				<td bgcolor="#E9F2F8" colspan="3">'.$xp.' Registro(s) </td>';
				echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
				foreach($sumacargop as $k=>$v){
					echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
				}
				echo '<td bgcolor="#E9F2F8" colspan="2">&nbsp;</td>';
				echo '</tr>';
		//	}
		//}
	}
	echo '<tr><td bgcolor="#E9F2F8" colspan="4">'.$x.' Registro(s)</td>';
	echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
	}
	echo '<td bgcolor="#E9F2F8" colspan="2">&nbsp;</td>';
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
				echo 'atcr(\'rep_facturas.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Excel</a>&nbsp;&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.substr(fechaLocal(),0,8).'01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		if($_POST['plazausuario']>0){
			echo '<tr><td>Plaza</td><td>'.$array_plazas[$_POST['plazausuario']].'<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'"></td></tr>';
		}
		else{
			echo '<tr><td align="left">Plaza</td><td><select multiple="multiple" name="plazas" id="plazas">';
			foreach($array_plazas as $k=>$v){
				echo '<option value="'.$k.'" selected>'.$v.'</option>';
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
			objeto.open("POST","rep_facturas.php",true);
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