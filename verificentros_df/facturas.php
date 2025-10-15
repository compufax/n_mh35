<?php
include("main.php");
if($_POST['cmd']==1000){
	$res = mysql_query("SELECT a.folio, a.serie, b.nombre FROM facturas a INNER JOIN plazas b ON b.cve = a.plaza INNER JOIN clientes c ON c.cve = a.cliente WHERE a.plaza={$_POST['plazausuario']} AND a.cve={$_POST['reg']}");
	$row = mysql_fetch_assoc(($res));
	// Ruta del archivo XML en el servidor
	$archivo = "../cfdi/comprobantes/cfdi_{$_POST['plazausuario']}_{$_POST['reg']}.xml";
	$nombre_archivo = "FACT {$row['nombre']} {$row['serie']} {$row['folio']}.xml";

	// Verificar que exista
	if (!file_exists($archivo)) {
	    die("El archivo {$archivo} no existe.");
	}

	// Cabeceras para forzar descarga
	header('Content-Description: File Transfer');
	header('Content-Type: application/xml');
	header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($archivo));

	// Enviar contenido
	readfile($archivo);
	exit();
}
include("imp_factura.php");
//ARREGLOS
$array_tipo_pag=array('Contado', 'Credito');
$array_usuario[-1] = 'WEB';
$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$res = mysql_query("SELECT bloqueada_sat FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row = mysql_fetch_array($res);
$bloqueada_sat = $row[0];

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);
$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$importe_iva=round($row['precio']*16/116,2);
	$array_engomadoprecio[$row['cve']]=$row['precio']-$importe_iva;
}
$res=mysql_query("SELECT * FROM bancos");
while($row=mysql_fetch_array($res)){
	$array_bancos[$row['cve']]=$row['nombre'];
}

$array_tiporelacionsat=array();
$res = mysql_query("SELECT * FROM tiporelacion_sat ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tiporelacionsat[$row['cve']] = $row['nombre'];
}

$array_clientes=array();
$res=mysql_query("SELECT * FROM clientes WHERE plaza='".$_POST['plazausuario']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_clientes[$row['cve']]=$row['nombre'];
	if($row['rfc']=="" || $row['nombre']=="" || $row['codigopostal'] == "" || $row['regimensat'] == "" || $row['usocfdi'] == "")// || $row['calle']=="" || $row['numexterior']=="" || $row['colonia']=="" || $row['municipio']=="" || $row['codigopostal']=="")
		$array_colorcliente[$row['cve']] = "#FF0000";
	else
		$array_colorcliente[$row['cve']] = "#000000";
	$array_clientessinticket[$row['cve']] = $row['facturasinticket'];
}
function mestexto($fec){
	global $array_meses;
	$datos=explode("-",$fec);
	return $array_meses[intval($datos[1])].' '.$datos[0];
}
//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);
$datossucursal='';
if($rowempresa['check_sucursal']==1){
	$datossucursal=",check_sucursal='".$rowempresa['check_sucursal']."',nombre_sucursal='".$rowempresa['nombre_sucursal']."',
	calle_sucursal='".$rowempresa['calle_sucursal']."',numero_sucursal='".$rowempresa['numero_sucursal']."',
	colonia_sucursal='".$rowempresa['colonia_sucursal']."',rfc_sucursal='".$rowempresa['rfc_sucursal']."',
	localidad_sucursal='".$rowempresa['localidad_sucursal']."',municipio_sucursal='".$rowempresa['municipio_sucursal']."',
	estado_sucursal='".$rowempresa['estado_sucursal']."',cp_sucursal='".$rowempresa['cp_sucursal']."'";
}

function _xmlToArray2($node)
{
	$occurance = array();

	if($node->hasChildNodes()){
		foreach($node->childNodes as $child) {
			$occurance[$child->nodeName]++;
		}
	}

	if($node->nodeType == XML_TEXT_NODE) {
		$result = html_entity_decode(htmlentities($node->nodeValue, ENT_COMPAT, 'UTF-8'), ENT_COMPAT,'ISO-8859-15');
	}
	else {
		if($node->hasChildNodes()){
			$children = $node->childNodes;

			for($i=0; $i<$children->length; $i++) {
				$child = $children->item($i);

				if($child->nodeName != '#text') {
					if($occurance[$child->nodeName] > 0 /*1*/) {
						$result[$child->nodeName][] = _xmlToArray($child);
					}
					else {
						$result[$child->nodeName] = _xmlToArray($child);
					}
				}
				else if ($child->nodeName == '#text') {
					$text = _xmlToArray($child);

					if (trim($text) != '') {
						$result[$child->nodeName] = _xmlToArray($child);
					}
				}
			}
		}

		if($node->hasAttributes()) {
			$attributes = $node->attributes;

			if(!is_null($attributes)) {
				foreach ($attributes as $key => $attr) {
					$result["@".$attr->name] = $attr->value;
				}
			}
		}
	}

	return $result;
}
$abono=0;

if($_POST['cmd']==110){
	$dom = new DOMDocument;
	$res=mysql_query("SELECT * FROM plazas WHERE cve='".$_POST['plazausuario']."'");
	$Plaza=mysql_fetch_array($res);
	$filtro="";
	$select= " SELECT a.* FROM facturas as a WHERE a.plaza='".$_POST['plazausuario']."' AND a.tipo_serie=0";
	$select .= " AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
	if($_POST['rfc_factura']!="") $select.=" AND a.rfc_factura='".$_POST['rfc_factura']."'";
	
	
	$select.=" GROUP BY a.cve ORDER BY a.cve DESC";
	$rsabonos=mysql_query($select) or die(mysql_error());
	if(mysql_num_rows($rsabonos)>0) {
		header("Content-type: application/vnd.ms-excel; name='excel'");
		header("Content-Disposition: filename=Facturas.xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo '<h2>Facturas '.$Plaza['numero'].' '.$Plaza['nombre'].' del dia '.$_POST['fecha'].'</h2>';
		$res=mysql_query("SELECT CONCAT(serie,' ',folio) as folio FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."' ORDER BY fecha,hora LIMIT 1");
		$row = mysql_fetch_array($res);
	//	echo '<h2>Folio Inicial: '.$row['folio'].'</h2>';
		$res=mysql_query("SELECT CONCAT(serie,' ',folio) as folio FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."' ORDER BY fecha DESC,hora DESC LIMIT 1");
		$row = mysql_fetch_array($res);
	//	echo '<h2>Folio Final: '.$row['folio'].'</h2>';
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$c=9;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.mysql_num_rows($rsabonos).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		echo '<th>Serie</th><th>Folio</th><th>Fecha</th>
		<th>Cliente</th><th>Concepto</th><th>Subtotal</th>
		<th>Iva</th><th>Total</th><!--<th>Version</th>--></tr>'; 
		$sumacargo=array();
		$x=0;
		while ($Abono=mysql_fetch_array($rsabonos)){	
			rowb();
			$estatus='';
			if($Abono['estatus']=='C'){
				$estatus='(CANCELADO)';
				if($_POST['estatus']!='C'){
					$Abono['subtotal']=0;
					$Abono['iva']=0;
					$Abono['total']=0;
					$Abono['iva_retenido']=0;
				}
				echo '<td align="center">CANCELADO</td>';
				
			}
			elseif($Abono['respuesta1']==""){
				echo '<td align="center" width="40" nowrap>SIN TIMBRAR</td>';
			}
			else{
				echo '<td align="center" width="40" nowrap>';
				if($Abono['estatus'] == 'D') echo 'DEVUELTO';
				else echo 'TIMBRADA';
				echo '</td>';
			}
			echo '<td align="center">'.$Abono['serie'].'</td>';
			echo '<td align="center">'.$Abono['folio'].'</td>';
			echo '<td align="center">'.htmlentities($Abono['fecha'].' '.$Abono['hora']).'</td>';
			echo '<td>'.htmlentities(utf8_encode($array_clientes[$Abono['cliente']])).'</td>';
			echo '<td>'.htmlentities($Abono['obs']).'</td>';
			echo '<td align="right">'.number_format($Abono['subtotal'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'],2).'</td>';
			$arch = '../cfdi/comprobantes/cfdi_'.$Abono['plaza'].'_'.$Abono['cve'].'.xml';
			$version = '';
			if(file_exists($arch)){
				$cadena= file_get_contents($arch);
				$dom->loadXML($cadena);
				$arreglo = _xmlToArray2($dom);
			}
			else{
				$arreglo=array();
			}
			if(isset($arreglo['cfdi:Comprobante'][0]['@Folio'])){
				$folioxml = $arreglo['cfdi:Comprobante'][0]['@Folio'];
				$version = 3.3;
			}
			else{
				$folioxml = $arreglo['cfdi:Comprobante'][0]['@folio'];
				$version = 3.2;
			}
	//		echo '<td align="center">'.$version.'</td>';
			echo '</tr>';
			$x++;
			$sumacargo[0]+=$Abono['subtotal'];
			$sumacargo[1]+=$Abono['iva'];
			$sumacargo[2]+=$Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'];
		}
		$c=5;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.$x.' Registro(s)</td>';
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		foreach($sumacargo as $k=>$v){
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
		}
		echo '</tr>';
		echo '</table>';
	}
	else {
		echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
	}
	exit();
}

if($_POST['cmd']==101){
	generaFacturaPdf($_POST['plazausuario'],$_POST['reg'],1);
	exit();
}

if($_POST['cmd']==103){
	unlink($_POST['reg']);
	echo '<script>window.close();</script>';
	exit();
}

if($_POST['cmd']==16){
	$zip = new ZipArchive();
	$fecha=date('Y_m_d_H_i_s');
	if($zip->open("../cfdi/zipcfdis".$fecha.".zip",ZipArchive::CREATE)){
		foreach($_POST['checksf'] as $cvefact){
			$res = mysql_query("SELECT * FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND cve='".$cvefact."'");
			$row = mysql_fetch_array($res);
			generaFacturaPdf($_POST['plazausuario'],$cvefact);
			if($row['estatus']=='C')
				$zip->addFile("../cfdi/comprobantes/facturac_".$_POST['plazausuario']."_".$cvefact.".pdf","Factura_".$_POST['plazausuario']."_".$cvefact.".pdf");
			else
				$zip->addFile("../cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf","Factura_".$_POST['plazausuario']."_".$cvefact.".pdf");
			$zip->addFile("../cfdi/comprobantes/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml","Factura_".$_POST['plazausuario']."_".$cvefact.".xml");
		}
		$zip->close(); 
	    if(file_exists("../cfdi/zipcfdis".$fecha.".zip")){ 
	        header('Content-type: "application/zip"'); 
	        header('Content-Disposition: attachment; filename="zipcfdis'.$fecha.'.zip"'); 
	        readfile("../cfdi/zipcfdis".$fecha.".zip"); 
	         
	        unlink("../cfdi/zipcfdis".$fecha.".zip"); 
	        foreach($_POST['checksf'] as $cvefact){
				$res = mysql_query("SELECT * FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND cve='".$cvefact."'");
				$row = mysql_fetch_array($res);
				if($row['estatus']=='C')
					@unlink("../cfdi/comprobantes/facturac_".$_POST['plazausuario']."_".$cvefact.".pdf");
				else
					@unlink("../cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf");
			}
	    } 
	    else{
			echo '<h2>Ocurrio un problema al cerrar el archivo favor de intentarlo de nuevo</h2>';
		}
	}
	else{
		echo '<h2>Ocurrio un problema al generar el archivo favor de intentarlo de nuevo</h2>';
	}
	exit();
}

if($_POST['ajax']==1){
error_reporting(0);
	
	
	
	$filtro="";
	$select= " SELECT a.*, b.rfc as rfccliente FROM facturas as a 
	LEFT JOIN clientes b ON b.plaza = a.plaza AND b.cve = a.cliente 
	LEFT JOIN cobro_engomado as c ON a.plaza = c.plaza AND a.cve = c.factura 
	WHERE a.plaza='".$_POST['plazausuario']."' AND a.tipo_serie = 0";
	if($_POST['folio'] != ""){
		$select .= " AND folio='".$_POST['folio']."'";
	}
	else{
		$select .= " AND ".$_POST['tipofecha'].".fecha>='".$_POST['fecha_ini']."' AND ".$_POST['tipofecha'].".fecha<='".$_POST['fecha_fin']."' ";
		//if($_POST['tipo']!="all") $select.=" AND a.tipo='".$_POST['tipo']."'";
		if($_POST['rfc_factura']!="") $select.=" AND a.rfc_factura='".$_POST['rfc_factura']."'";
		if($_POST['rfc_repector']!="") $select.=" AND b.rfc='".$_POST['rfc_repector']."'";
		if($_POST['cliente']!="all") $select.=" AND a.cliente='".$_POST['cliente']."'";
		if($_POST['nomcliente'] != '') $select .= " AND b.nombre LIKE '%".$_POST['nomcliente']."%'";
		if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
		if ($_POST['tipo_pag']!="all") { $select.=" AND a.tipo_pag='".$_POST['tipo_pag']."'"; }
		if($_POST['mostrar'] == 1) $select .=" AND IFNULL(c.fecha,'0000-00-00')>'0000-00-00' AND LEFT(c.fecha,7)<LEFT(a.fecha,7)";
		elseif($_POST['mostrar'] == 2) $select .=" AND (IFNULL(c.fecha,'0000-00-00')='0000-00-00' OR LEFT(c.fecha,7)=LEFT(a.fecha,7))";
		if($_POST['estatus']==1) $select.=" AND a.estatus!='C'";
		elseif($_POST['estatus']==2) $select.=" AND a.estatus='C'";
		elseif($_POST['estatus']==3) $select.=" AND a.estatus='D'";
		elseif($_POST['estatus']==4) $select.=" AND a.estatus!='C' AND a.respuesta1 = ''";
		elseif($_POST['estatus']==5) $select.=" AND a.respuesta1 != ''";
		
	}
	$select.=" GROUP BY a.cve ORDER BY a.cve DESC";
	$rsabonos=mysql_query($select) or die(mysql_error());
	if(mysql_num_rows($rsabonos)>0) {
		//echo''.$select.'';
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$c=16;
		if(nivelUsuario()>0) $c++;
		if($_POST['cveusuario']==1) $c+=2;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.mysql_num_rows($rsabonos).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		if(nivelUsuario()>0){
			echo '<th><input type="checkbox" name="selt" value="1" onClick="if(this.checked) $(\'.checks\').attr(\'checked\',\'checked\'); else $(\'.checks\').removeAttr(\'checked\');"></th>';
		}
		if($_POST['cveusuario']==1) echo '<th>IDExterno</th><!--<th>UUID</th>-->';
		echo '<th>Serie</th><th>Folio</th><th>Fecha</th><!--<th>RFC Emisor</th>--><th>Tipo Factura</th>
		<th>Cliente</th><th>RFC Cliente</th><th>Tipo Pago</th><th>UUID</th><th>Subtotal</th>
		<th>Iva</th><th>Total</th><!--<th>Retencion I.S.R.</th><th>Retencion I.V.A.</th><th>Total</th>-->
		<!--<th>Version</th>--><th>Ticket</th><th>Fecha Ticket</th><th>Placa Ticket</th>
		<th>Usuario<br>';
		echo '<select name="usuario" onchange="document.forma.usu.value=this.value;buscarRegistros();"><option value="all">---Todos---</option>';
		$res1=mysql_query("SELECT a.usuario FROM facturas as a WHERE plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY a.usuario");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['usuario'].'"';
			if($row1['usuario']==$_POST['usu']) echo ' selected';
			echo '>'.$array_usuario[$row1['usuario']].'</option>';
		}
		echo '</select></th></tr>'; 
		$sumacargo=array();
		$x=0;
		$nivelUsuario = nivelUsuario();
		while ($Abono=mysql_fetch_array($rsabonos)){	
			rowb();
			$estatus='';
			if($Abono['estatus']=='C'){
				$estatus='(CANCELADO)';
				if($_POST['estatus']!='C'){
					$Abono['subtotal']=0;
					$Abono['iva']=0;
					$Abono['total']=0;
					$Abono['iva_retenido']=0;
				}
				echo '<td align="center">CANCELADO<br>';
				if(file_exists('../cfdi/comprobantes/facturac_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf')){
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'../cfdi/comprobantes/facturac_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf\',\'_blank\',\'0\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
					if($_POST['cveusuario']==1){
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'103\',\'../cfdi/comprobantes/facturac_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf\');"><img src="images/basura.gif" border="0" title="Borrar PDF '.$Abono['folio'].'"></a>';
					}
				}
				else{
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'101\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				}
				echo '</br>'.$array_usuario[$Abono['usucan']].'</br>'.$Abono['fechacan'].'</td>';
				if($nivelUsuario>0){
					echo '<td><input type="checkbox" class="checks" name="checksf[]" value="'.$Abono['cve'].'"></td>';
				}
				
			}
			elseif($Abono['respuesta1']==""){
				echo '<td align="center" width="40" nowrap>';
				if($bloqueada_sat!=1)
					echo '<input type="button" style=" font-size:25px;" value="TIMBRAR" onClick="if(confirm(\'Esta seguro que desea timbrar?\')){$(\'#panel\').show();atcr(\'facturas.php\',\'\',\'5\',\''.$Abono['cve'].'\');}"><br>';
				//echo '<a href="#" onClick="if(confirm(\'Esta seguro que desea timbrar?\')){$(\'#panel\').show();atcr(\'facturas.php\',\'\',\'5\',\''.$Abono['cve'].'\');}"><img src="images/validosi.gif" border="0" title="Timbrar '.$Abono['folio'].'"></a>';
				//if(nivelUsuario()>2){
				if($_POST['cveusuario'] == 1 || $nivelUsuario>2){
					echo '&nbsp;&nbsp;<span style="cursor:pointer;" href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')){$(\'#panel\').show();cancelarfactura(\''.$Abono['cve'].'\',0);}"><img src="images/validono.gif" border="0" title="Cancelar '.$Abono['folio'].'"></span>';
				}
				if(file_exists('../cfdi/comprobantes/cfdi_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.xml')){
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'1000\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir XML '.$Abono['folio'].'"></a>';
				}
				echo '</td>';
				if($nivelUsuario>0){
					echo '<td>&nbsp;</td>';
				}
			}
			else{
				echo '<td align="center" width="40" nowrap>';
				if($Abono['estatus'] == 'D') echo 'DEVUELTO<br>';
				//if($bloqueada_sat!=1 && $_POST['cveusuario']==1)
				//	echo '<input type="button" style=" font-size:25px;" value="RE TIMBRAR" onClick="if(confirm(\'Esta seguro que desea re timbrar?\')){$(\'#panel\').show();atcr(\'facturas.php\',\'\',\'5\',\''.$Abono['cve'].'\');}"><br>';
				//<a href="#" onClick="atcr(\'cfdi/comprobantes/cfdi_'.$_POST['cveempresa'].'_'.$Abono['cve'].'.pdf\',\'_blank\',\'\',\'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				if(file_exists('../cfdi/comprobantes/factura_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf')){
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'../cfdi/comprobantes/factura_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf\',\'_blank\',\'0\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
					if($_POST['cveusuario']==1){
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'103\',\'../cfdi/comprobantes/factura_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf\');"><img src="images/basura.gif" border="0" title="Borrar PDF '.$Abono['folio'].'"></a>';
					}
				}
				else{
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'101\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				}
				if($nivelUsuario>2 && $Abono['estatus'] != 'D' && $bloqueada_sat!=1){
					//if($_POST['cveusuario'] == 1)
						echo '&nbsp;&nbsp;<a href="#" onClick="cancelarfactura(\''.$Abono['cve'].'\',1);"><img src="images/validono.gif" border="0" title="Cancelar '.$Abono['folio'].'"></a>';
					/*echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de generar la nota de credito de la factura?\')){mostrar_obs_nota(\''.$Abono['cve'].'\');}"><img src="images/cerrar.gif" border="0" title="Nota Credito '.$Abono['folio'].'"></a>';*/
				}
				//if($_POST['cveusuario']==1)
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'1000\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir XML '.$Abono['folio'].'"></a>';
				echo '</td>';
				if($nivelUsuario>0){
					echo '<td><input type="checkbox" class="checks" name="checksf[]" value="'.$Abono['cve'].'"></td>';
				}
			}
			if($_POST['cveusuario']==1) echo '<td align="center">'.$Abono['idexterno'].'</td><!--<td>'.$Abono['uuid'].'</td>-->';
			echo '<td align="center">'.$Abono['serie'].'</td>';
			$arch = '../cfdi/comprobantes/cfdi_'.$Abono['plaza'].'_'.$Abono['cve'].'.xml';
			$version = '';
			$arreglo=array();
			$folioxml = $Abono['folio'];
			if(file_exists($arch)){
				$cadena= file_get_contents($arch);
				$dom = new DOMDocument;
				$dom->loadXML($cadena);
				$arreglo = _xmlToArray2($dom);
			}
			else{
				$arreglo=array();
			}
			if(isset($arreglo['cfdi:Comprobante'][0]['@Folio'])){
				$folioxml = $arreglo['cfdi:Comprobante'][0]['@Folio'];
				$version = 3.3;
			}
			else{
				$folioxml = $arreglo['cfdi:Comprobante'][0]['@folio'];
				$version = 3.2;
			}
			if($folioxml != $Abono['folio'] && $_POST['cveusuario']==1)
			{
				$Abono['folio'] .= '<font color="RED">'.$folioxml.'</font>';
			}
			echo '<td align="center">'.$Abono['folio'].'</td>';
			if($_POST['cveusuario']==1){
				echo '<td align="center"><input type="text" id="fechan_'.$Abono['cve'].'" class="textField" size="23" value="'.$Abono['fecha'].' '.$Abono['hora'].'">&nbsp;<span style="cursor:pointer;" onClick="displayCalendar(document.getElementById(\'fechan_'.$Abono['cve'].'\'),\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></span><br>
				<input type="button" value="Guardar" onClick="guardarFecha('.$Abono['cve'].')"></td>';
			}
			else{
				echo '<td align="center">'.htmlentities($Abono['fecha'].' '.$Abono['hora']).'</td>';
			}
//			echo '<td align="center">'.$Abono['rfc_factura'].'</td>';
			if($nivelUsuario > 0){
				echo '<td align="center"><select id="tipo_pag_'.$Abono['cve'].'">';
				foreach($array_tipo_pag as $k=>$v){
					echo '<option value="'.$k.'"';
					if($k==$Abono['tipo_pag']) echo ' selected';
					echo '>'.$v.'</option>';
				}
				echo '</select><br>
				<input type="button" value="Guardar" onClick="guardarTipoPag('.$Abono['cve'].')"></td>';
			}
			else{
				echo '<td>'.htmlentities($array_tipo_pag[$Abono['tipo_pag']]).'</td>';
			}
			echo '<td>'.htmlentities(($array_clientes[$Abono['cliente']])).'</td>';
			echo '<td align="center">'.$Abono['rfccliente'].'</td>';
			echo '<td>'.htmlentities($array_tipo_pago[$Abono['tipo_pago']]).'</td>';
			echo '<td align="center">'.$Abono['uuid'].'</td>';
			echo '<td align="right">'.number_format($Abono['subtotal'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'],2).'</td>';
			//echo '<td align="right">'.number_format($Abono['isr_retenido'],2).'</td>';
			//echo '<td align="right">'.number_format($Abono['iva_retenido'],2).'</td>';
			//echo '<td align="right">'.number_format($Abono['total'],2).'</td>';
//			echo '<td align="center">'.$version.'</td>';
			$array_tickets=array();
			$res2=mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$Abono['plaza']."' AND factura='".$Abono['cve']."' AND estatus!='C'");
			while($row2 = mysql_fetch_array($res2)) $array_tickets[$row2['cve']] = array('placa'=>$row2['placa'],'fecha'=>$row2['fecha']);
			//if($row2=mysql_fetch_array($res2)){
			if(count($array_tickets) > 0){
				echo '<td align="center">';
				foreach($array_tickets as $ticket=>$datosticket){
					echo $ticket.'<br>';
				}
				echo '</td><td align="center">';
				foreach($array_tickets as $ticket=>$datosticket){
					echo $datosticket['fecha'].'<br>';
				}
				echo '</td><td align="center">';
				foreach($array_tickets as $ticket=>$datosticket){
					/*$res3=mysql_query("SELECT a.cve FROM facturas a INNER JOIN cobro_engomado b ON a.plaza=b.plaza AND a.cve=b.factura WHERE a.plaza='".$Abono['plaza']."' AND a.cve!='".$Abono['cve']."' AND b.placa = '".$datosticket['placa']."' AND ABS(DATEDIFF('".$Abono['fecha']."',fecha))<=60");
					if(mysql_num_rows($res3)>0){
						echo '<font color="RED">'.$datosticket['placa'].'</font><br>';
					}
					else{*/
						echo ''.$datosticket['placa'].'<br>';
					//}
				}
				echo '</td>';
			}
			else{
				echo '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
			}
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$sumacargo[0]+=$Abono['subtotal'];
			$sumacargo[1]+=$Abono['iva'];
			$sumacargo[2]+=$Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'];
			//$sumacargo[3]+=$Abono['isr_retenido'];
			//$sumacargo[4]+=$Abono['iva_retenido'];
			//$sumacargo[5]+=$Abono['total'];
		}
		$c=8;
		if($nivelUsuario>0) $c++;
		//if($_POST['cveusuario']==1) $c+=2;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.$x.' Registro(s)</td>';
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		foreach($sumacargo as $k=>$v){
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
		}
		echo '<td bgcolor="#E9F2F8" colspan="5">&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		
		echo '|*|';
		echo '<table width="" border="1" cellpadding="4" cellspacing="1" class="">';
			echo '	
				<tr><td>Existencia de Timbres</td><td align="right" >'.number_format(saldo_timbres($_POST['plazausuario']),0).'</td></tr>';
				echo'</table>';
	}
	else {
		echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
	}
	exit();
}

if($_GET['ajax']==2){
	if($_GET['cveusuario']==1)
		$select= " SELECT rfc, nombre, cve, codigopostal, email FROM clientes WHERE plaza='".$_GET['plazausuario']."' AND usuario>=0 AND (";
	else
		$select= " SELECT rfc, nombre, cve, codigopostal, email FROM clientes WHERE plaza='".$_GET['plazausuario']."' AND autorizado=1 AND usuario>=0 AND (";
	//$select.=" nomina LIKE '%".$_GET['term']."%' OR ";
	$select.=" rfc LIKE '%".$_GET['term']."%' OR nombre like '%".$_GET['term']."%')";
	//$select.=" concat(apellidop,' ',apellidom,' ',nombre) like '%".$_GET['term']."%')";
	$select .= " ORDER BY rfc";
	$res=mysql_query($select) or die(mysql_error());
  $matches = array();
  while($row=mysql_fetch_assoc($res)){
    // Adding the necessary "value" and "label" fields and appending to result set
    $row['value'] = "";
    $row['label'] = utf8_encode($row['rfc']).", ".utf8_encode($row['nombre'])."";
	$row['nombre'] = utf8_encode($row['nombre']);
	$row['rfc'] = utf8_encode($row['rfc']);
	$row['email'] = utf8_encode($row['email']);
	$cuentas = '<option value="0">Seleccione</option>';
	$res1=mysql_query("SELECT * FROM clientes_cuentas WHERE cliente='".$row['cve']."'");
	while($row1=mysql_fetch_array($res1)){
		$cuentas.='<option value="'.$row1['cve'].'">'.utf8_encode($array_bancos[$row1['banco']]).' '.utf8_encode($row1['cuenta']).'</option>';
	}
	$row['cuentas'] = $cuentas;
    $matches[] = $row;
  } 
  // Truncate, encode and return the results
  $matches = array_slice($matches, 0, 15);
  print json_encode($matches);
	exit();
}

if($_POST['ajax']==20){
	echo '<option value="0">Seleccione</option>';
	$res1=mysql_query("SELECT * FROM clientes_cuentas WHERE cliente='".$_POST['cliente']."' AND cliente>0");
	while($row1=mysql_fetch_array($res1)){
		echo '<option value="'.$row1['cve'].'">'.utf8_encode($array_bancos[$row1['banco']]).' '.utf8_encode($row1['cuenta']).'</option>';
	}
	exit();
}

if($_POST['ajax']==3){
	$regresar = array('error'=>0,'mensaje'=>'');
	$tickets = explode(",", $_POST['ticket']);
	if(count($tickets) > 10){
		$regresar = array('error'=>1,'mensaje'=>'Solo se pueden poner un maximo de 10 tickets por factura');
	}
	else{
		$fechaticket = '';
		foreach($tickets as $ticket){
			$res = mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$ticket."'");
			if($row=mysql_fetch_array($res)){
				if($fechaticket == '') $fechaticket = $row['fecha'];
				if($row['estatus']=='C'){
					$regresar['error'] = 1;
					$regresar['mensaje'] .= "El ticket ".$ticket." esta cancelado\n";
				}
				elseif(date('Y')==2016 && substr($row['fecha'],0,4)==2015){
					$regresar['error'] = 1;
					$regresar['mensaje'] .= "El ticket ".$ticket." es del 2015\n";
				}
				elseif($row['factura']>0 && $row['notacredito'] == 0){
					$regresar['error'] = 1;
					$regresar['mensaje'] .= "El ticket ".$ticket." ya esta facturado\n";
				}
				elseif($row['monto']==0){
					$regresar['error'] = 1;
					$regresar['mensaje'] .= "El ticket ".$ticket." tiene monto cero\n";
				}
				if(($fechaticket == date('Y-m-d') && $row['fecha'] < date('Y-m-d')) || ($fechaticket < date('Y-m-d') && $row['fecha'] == date('Y-m-d'))){
					$regresar['error'] = 1;
					$regresar['mensaje'] .= "Los tickets deben de ser o del dia de hoy o atrasados\n";	
				}
			}
			else{
				$regresar['error'] = 1;
				$regresar['mensaje'] .= "No se encontro ticket ".$ticket."\n";
			}
		}
		/*if($fechaticket != '' && $fechaticket == date('Y-m-d') && $_POST['forma_pago'] == 1){
			$regresar['error'] = 1;
			$regresar['mensaje'] .= "Los tickets de la fecha actual solo se pueden facturar con forma pago PAGO EN UNA SOLA EXHIBICION\n";
		}
		elseif($fechaticket != '' && $fechaticket < date('Y-m-d') && $_POST['forma_pago'] == 0){
			$regresar['error'] = 1;
			$regresar['mensaje'] .= "Los tickets de fechas anteriores solo se pueden facturar con forma pago PAGO EN PARCIALIDADES O DIFERIDO\n";
		}*/
	}
	echo json_encode($regresar);
	exit();
}

if($_POST['ajax']==4){
	$regresar = array('error'=>0,'mensaje'=>'');
	$tickets = explode(",", $_POST['pagoscaja']);
	if(count($tickets) > 10){
		$regresar = array('error'=>1,'mensaje'=>'Solo se pueden poner un maximo de 10 pagos por factura');
	}
	else{
		foreach($tickets as $ticket){
			$res = mysql_query("SELECT * FROM pagos_caja WHERE plaza='".$_POST['plazausuario']."' AND tipo_pago = 6 AND cve='".$ticket."'");
			if($row=mysql_fetch_array($res)){
				if($row['estatus']=='C'){
					$regresar['error'] = 1;
					$regresar['mensaje'] .= "El pago ".$ticket." esta cancelado\n";
				}
				elseif(date('Y')==2016 && substr($row['fecha'],0,4)==2015){
					$regresar['error'] = 1;
					$regresar['mensaje'] .= "El pago ".$ticket." es del 2015\n";
				}
				elseif($row['factura']>0){
					$regresar['error'] = 1;
					$regresar['mensaje'] .= "El pago ".$ticket." ya esta facturado\n";
				}
				else{
					$res1 = mysql_query("SELECT a.cve FROM vales_pago_anticipado a INNER JOIN cobro_engomado b ON a.plaza = b.plaza AND a.cve = b.vales_pago_anticipado WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus!='C' AND a.pago= '".$ticket."' AND b.factura>0");
					if($row1 = mysql_fetch_array($res1)){
						$regresar['error'] = 1;
						$regresar['mensaje'] .= "Ya se facturaron vales del pago ".$ticket."\n";
					}
					
				}
			}
			else{
				$regresar['error'] = 1;
				$regresar['mensaje'] .= "No se encontro el pago ".$ticket."\n";
			}
		}
	}
	echo json_encode($regresar);
	exit();
}

if($_POST['ajax']==16){
	$datos = explode(" ",$_POST['fecha']);
	mysql_query("UPDATE facturas SET fecha='".$datos[0]."',hora='".$datos[1]."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
	exit();
}

if($_POST['ajax']==17){
	mysql_query("UPDATE facturas SET tipo_pag='".$_POST['tipo_pag']."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
	exit();
}

if($_POST['cmd']==6){
	foreach($_POST['checksf'] as $cvefact){
		$res = mysql_query("SELECT * FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND cve='".$cvefact."'");
		$row = mysql_fetch_array($res);
		$res1 = mysql_query("SELECT * FROM clientes WHERE cve='".$row['cliente']."'");
		$row1 = mysql_fetch_array($res1);
		$row1['cve']=0;
		$emailenvio = $row1['email'];
		$emailenvio .= ','.$_POST['correos_envio'];
		
		$mail = obtener_mail();
		$mail->FromName = "Vereficentro Plaza ".$array_plaza[$_POST['plazausuario']];
		$mail->Subject = "Factura ".$row['serie']." ".$row['folio'];
		$mail->Body = "Factura ".$row['serie']." ".$row['folio'];
		//$mail->AddAddress(trim($emailenvio));
		$correos = explode(",",trim($emailenvio));
		foreach($correos as $correo)
			if(trim($correo)!='')
				$mail->AddAddress(trim($correo));
		/*if($rowempresa['email']!=""){
			$correos = explode(",",trim($rowempresa['email']));
			foreach($correos as $correo)
				$mail->AddCC(trim($correo));
		}*/
		generaFacturaPdf($_POST['plazausuario'],$cvefact);
		if($row['estatus']=='C')
			$mail->AddAttachment("../cfdi/comprobantes/facturac_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$row['serie']." ".$row['folio'].".pdf");
		else
			$mail->AddAttachment("../cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$row['serie']." ".$row['folio'].".pdf");
		$mail->AddAttachment("../cfdi/comprobantes/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$row['serie']." ".$row['folio'].".xml");
		$mail->Send();

		if($row['estatus']=='C')
			@unlink("../cfdi/comprobantes/facturac_".$_POST['plazausuario']."_".$cvefact.".pdf");
		else
			@unlink("../cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf");
		/*if($rowempresa['email']!=""){
			$mail = new PHPMailer;		
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = 'smtp.gmail.com';                     // Specify main and backup SMTP servers
			$mail->Port = 587;
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = 'gverificentros@gmail.com';   // SMTP username
			$mail->Password = 'loscocos720';                           // SMTP password
			$mail->SMTPSecure = 'tls';    
			$mail->From = "gverificentros@gmail.com";
			$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
			$mail->Subject = "Factura ".$row['serie']." ".$row['folio'];
			$mail->Body = "Factura ".$row['serie']." ".$row['folio'];
			$correos = explode(",",trim($rowempresa['email']));
			foreach($correos as $correo)
				$mail->AddAddress(trim($correo));
			if($row['estatus']=='C')
				$mail->AddAttachment("../cfdi/comprobantes/facturac_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$row['serie']." ".$row['folio'].".pdf");
			else
				$mail->AddAttachment("../cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$row['serie']." ".$row['folio'].".pdf");
			$mail->AddAttachment("../cfdi/comprobantes/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$row['serie']." ".$row['folio'].".xml");
			$mail->Send();
		}*/
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==5){
	mysql_query("UPDATE facturas SET fecha=CURDATE(),hora=CURTIME() WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$res = mysql_query("SELECT * FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	$fserie=$row['serie'];
	$ffolio=$row['folio'];
	$resplaza = mysql_query("SELECT * FROM plazas WHERE cve='".$_POST['plazausuario']."'");
	$rowplaza = mysql_fetch_array($resplaza);
	$cvefact=$_POST['reg'];
	$documento=array();
	require_once("../nusoap/nusoap.php");
	//Generamos la Factura
	$documento['serie']=$row['serie'];
	$documento['folio']=$row['folio'];
	$documento['fecha']=$row['fecha'].' '.$row['hora'];
	$documento['formapago']=$array_forma_pago[$row['forma_pago']];
	$documento['idtipodocumento']=1;
	$documento['observaciones']=$row['obs'];
	$documento['metodopago']=$array_tipo_pagosat[$row['tipo_pago']];
	$res1 = mysql_query("SELECT * FROM clientes WHERE cve='".$row['cliente']."'");
	$row1 = mysql_fetch_array($res1);
	$row1['cve']=0;
	$emailenvio = $row1['email'];
	if($row['tipo_pago']==2 || $row['tipo_pago']==5)
		$documento['numerocuentapago']=$row['cuenta_cheque'];
	$documento['receptor']['codigo']=$row1['cve'];
	$documento['receptor']['rfc']=$row1['rfc'].$row1['homoclave'];
	$documento['receptor']['nombre']=$row1['nombre'];
	$documento['receptor']['calle']=$row1['calle'];
	$documento['receptor']['num_ext']=$row1['numexterior'];
	$documento['receptor']['num_int']=$row1['numinterior'];
	$documento['receptor']['colonia']=$row1['colonia'];
	$documento['receptor']['localidad']=$row1['localidad'];
	$documento['receptor']['municipio']=$row1['municipio'];
	$documento['receptor']['estado']=$row1['estado'];
	$documento['receptor']['pais']='MEXICO';
	$documento['receptor']['codigopostal']=$row1['codigopostal'];
	//Agregamos los conceptos
	$res2 = mysql_query("SELECT * FROM facturasmov WHERE plaza='".$_POST['plazausuario']."' AND cvefact='".$cvefact."'");
	
	$i=0;
	while($row2 = mysql_fetch_array($res2))
	{
		$documento['conceptos'][$i]['cantidad']=$row2['cantidad'];
		$documento['conceptos'][$i]['unidad']=$row2['unidad'];
		$documento['conceptos'][$i]['descripcion']=$row2['concepto'];
		$documento['conceptos'][$i]['valorUnitario']=$row2['precio'];
		$documento['conceptos'][$i]['importe']=$row2['importe'];
		$documento['conceptos'][$i]['importe_iva']=$row2['importe_iva'];
		$i++;
	}
	$documento['subtotal']=$row['subtotal'];
	$documento['descuento']=0;
	//Traslados
	#IVA
	if($row['iva']>0){
		$documento['tasaivatrasladado']=16;
		$documento['ivatrasladado']=$row['iva'];  //Solo 200 grava iva
	}
	if($row['iva_retenido'] > 0){
		$documento['ivaretenido']=$row['iva_retenido'];  
	}
	if($row['isr_retenido'] > 0){
		$documento['isrretenido']=$row['isr_retenido'];  
	}
	
	//total
	$documento['total']=$row['total'];
	//Moneda
	$documento['moneda']     = 1; //1=pesos, 2=Dolar, 3=Euro
	$documento['tipocambio'] = 1;
	//echo '<pre>';
	//print_r($documento);
	//echo '</pre>';
	//print_r($documento);
	$documento = genera_arreglo_facturacion($_POST['plazausuario'], $cvefact, 'I');
	$resultadotimbres = validar_timbres($_POST['plazausuario']);
	if($resultadotimbres['seguir']){
		//$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
		$oSoapClient = new nusoap_client("https://servicios.integratucfdi.net/wscfdi.php?wsdl", true);	
		$err = $oSoapClient->getError();
		if($err!=""){
			echo "error1:".$err;
			desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
		}
		else{
			//print_r($documento);
			$oSoapClient->timeout = 300;
			$oSoapClient->response_timeout = 300;
			/*if($_POST['cveusuario']==1){
				echo json_encode(array ('id' => $rowempresa['idplaza'],'rfcemisor' =>$rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'documento' => $documento, 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
			}*/
			$respuesta = $oSoapClient->call("generarComprobante", array ('id' => $rowempresa['idplaza'],'rfcemisor' =>$rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'documento' => $documento, 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
			if ($oSoapClient->fault) {
				echo '<p><b>Fault: ';
				print_r($respuesta);
				echo '</b></p>';
				echo '<p><b>Request: <br>';
				echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
				echo '<p><b>Response: <br>';
				echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
				echo '<p><b>Debug: <br>';
				echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
				desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
			}
			else{
				$err = $oSoapClient->getError();
				if ($err){
					echo '<p><b>Error: ' . $err . '</b></p>';
					echo '<p><b>Request: <br>';
					echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
					echo '<p><b>Response: <br>';
					echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
					echo '<p><b>Debug: <br>';
					echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
					desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
				}
				else{
					if($respuesta['resultado']){
						mysql_query("UPDATE facturas SET respuesta1='".$respuesta['uuid']."',seriecertificado='".$respuesta['seriecertificado']."',
						sellodocumento='".$respuesta['sellodocumento']."',uuid='".$respuesta['uuid']."',seriecertificadosat='".$respuesta['seriecertificadosat']."',
						sellotimbre='".$respuesta['sellotimbre']."',cadenaoriginal='".$respuesta['cadenaoriginal']."',
						fechatimbre='".substr($respuesta['fechatimbre'],0,10)." ".substr($respuesta['fechatimbre'],-8)."'
						WHERE plaza='".$_POST['plazausuario']."' AND cve=".$cvefact);

						mysql_query("UPDATE facturas SET rfc_cli='".$row1['rfc']."', nombre_cli='".$row1['nombre']."', calle_cli='".$row1['calle']."', numext_cli='".$row1['numexterior']."', numint_cli = '".$row1['numinterior']."', colonia_cli = '".$row1['colonia']."', localidad_cli = '".$row1['localidad']."', municipio_cli = '".$row1['municipio']."',
							estado_cli='".$row1['estado']."', cp_cli='".$row1['codigopostal']."'
						WHERE plaza='".$_POST['plazausuario']."' AND cve=".$cvefact);

						//generaFacturaPdf($_POST['plazausuario'],$cvefact);
						//Tomar la informacion de Retorno
						$dir="../cfdi/comprobantes/";
						//$dir=dirname(realpath(getcwd()))."/solucionesfe_facturacion/cfdi/comprobantes/";
						//el zip siempre se deja fuera
						$dir2="../cfdi/";
						//Leer el Archivo Zip
						$fileresult=$respuesta['archivos'];
						$strzipresponse=base64_decode($fileresult);
						$filename='cfdi_'.$_POST['plazausuario'].'_'.$cvefact;
						file_put_contents($dir2.$filename.'.zip', $strzipresponse);
						$zip = new ZipArchive;
						if ($zip->open($dir2.$filename.'.zip') === TRUE){
							$strxml=$zip->getFromName('xml.xml');
							file_put_contents($dir.$filename.'.xml', $strxml);
							//$strpdf=$zip->getFromName('formato.pdf');
							//file_put_contents($dir.$filename.'.pdf', $strpdf);
							$zip->close();	
							generaFacturaPdf($_POST['plazausuario'],$cvefact);
							if($emailenvio!=""){
								$mail = obtener_mail();
								$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
								$mail->Subject = "Factura ".$fserie." ".$ffolio;
								$mail->Body = "Factura ".$fserie." ".$ffolio;
								//$mail->AddAddress(trim($emailenvio));
								$correos = explode(",",trim($emailenvio));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								/*if($rowempresa['email']!=""){
									$correos = explode(",",trim($rowempresa['email']));
									foreach($correos as $correo)
										$mail->AddCC(trim($correo));
								}*/
								$mail->AddAttachment("../cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$fserie." ".$ffolio.".pdf");
								$mail->AddAttachment("../cfdi/comprobantes/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$fserie." ".$ffolio.".xml");
								$mail->Send();
							}
							/*if($rowempresa['email']!=""){
								$mail = new PHPMailer;		
								$mail->isSMTP();                                      // Set mailer to use SMTP
								$mail->Host = 'smtp.gmail.com';                     // Specify main and backup SMTP servers
								$mail->Port = 587;
								$mail->SMTPAuth = true;                               // Enable SMTP authentication
								$mail->Username = 'gverificentros@gmail.com';   // SMTP username
								$mail->Password = 'loscocos720';                           // SMTP password
								$mail->SMTPSecure = 'tls';    
								$mail->From = "gverificentros@gmail.com";
								$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
								$mail->Subject = "Factura ".$cvefact;
								$mail->Body = "Factura ".$cvefact;
								$correos = explode(",",trim($rowempresa['email']));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("../cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$fserie." ".$ffolio.".pdf");
								$mail->AddAttachment("../cfdi/comprobantes/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$fserie." ".$ffolio.".xml");
								$mail->Send();
							}*/	
							@unlink("../cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf");
						}
						else 
							$strmsg='Error al descomprimir el archivo';
						if(file_exists($dir2.$filename.'.zip')){
							unlink($dir2.$filename.'.zip');
						}
					}
					else{
						$fileresult=$respuesta['archivos'];
							$strzipresponse=base64_decode($fileresult);
							$filename='../cfdi/comprobantes/cfdi_'.$_POST['plazausuario']."_".$cvefact.'.xml';
							file_put_contents($filename, $strzipresponse);
						$strmsg=$respuesta['mensaje'];
						desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
					}
					//print_r($respuesta);	
					echo $strmsg;
				}
			}
		}
	}
//Recarga por segunda vez	
    $_POST['cmd']='recargar';
	$_POST['reg']=0;
}

if($_POST['cmd']==30){
	
	$resfactura=mysql_query("SELECT * FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."' AND estatus NOT IN ('C','D')");
	if($Factura = mysql_fetch_array($resfactura)){
		$Factura['obs'] = $_POST['obsnota'];
		$resplaza = mysql_query("SELECT * FROM plazas WHERE cve='".$_POST['plazausuario']."'");
		$rowplaza = mysql_fetch_array($resplaza);
		$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
		$rowempresa = mysql_fetch_array($resempresa);
		if($rowempresa['check_sucursal']==1){
			$datossucursal=",check_sucursal='".$Factura['check_sucursal']."',nombre_sucursal='".addslashes($Factura['nombre_sucursal'])."',
			calle_sucursal='".addslashes($Factura['calle_sucursal'])."',numero_sucursal='".$Factura['numero_sucursal']."',
			colonia_sucursal='".addslashes($Factura['colonia_sucursal'])."',rfc_sucursal='".$Factura['rfc_sucursal']."',
			localidad_sucursal='".addslashes($Factura['localidad_sucursal'])."',municipio_sucursal='".addslashes($Factura['municipio_sucursal'])."',
			estado_sucursal='".addslashes($Factura['estado_sucursal'])."',cp_sucursal='".$Factura['cp_sucursal']."'";
		}
		$res = mysql_query("SELECT serie,folio_inicial FROM foliosiniciales WHERE plaza='".$_POST['plazausuario']."' AND tipo=0 AND tipodocumento=2");
		$row = mysql_fetch_array($res);
		$res1 = mysql_query("SELECT IFNULL(MAX(folio+1),1) FROM notascredito WHERE plaza='".$_POST['plazausuario']."' AND serie='".$row['serie']."'");
		$row1 = mysql_fetch_array($res1);
		if($row['folio_inicial']<$row1[0]){
			$row['folio_inicial'] = $row1[0];
		}
		$insert = "INSERT notascredito SET plaza='".$_POST['plazausuario']."',serie='".$row['serie']."',folio='".$row['folio_inicial']."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$Factura['obs']."',
		cliente='".$Factura['cliente']."',tipo_pago='".$Factura['tipo_pago']."',forma_pago='".$Factura['forma_pago']."',usuario='".$_POST['cveusuario']."',baniva_retenido='".$Factura['baniva_retenido']."',banisr_retenido='".$Factura['banisr_retenido']."',
		carta_porte='".$Factura['carta_porte']."',load_cliente='".$Factura['load']."',nombre_cliente='".$Factura['nombre_cliente']."',direccion_cliente='".$Factura['direccion_cliente']."',
		tipopago_cliente='".$Factura['tipopago_cliente']."',banco_cliente='".$Factura['banco_cliente']."',cuenta_cliente='".$Factura['cuenta_cliente']."',tipo_factura='".$Factura['tipo_factura']."',
		tipo_relacion='01',uuidsrelacionados='".$Factura['uuid']."',
		factura='".$Factura['cve']."',engomado='".$Factura['engomado']."',banco='".$Factura['banco']."',cuenta_cheque='".$Factura['cuenta_cheque']."',tiene_descuento='".$Factura['tiene_descuento']."'".$datossucursal;
		while(!$resinsert=mysql_query($insert)){
			$row['folio_inicial']++;
			$insert = "INSERT notascredito SET plaza='".$_POST['plazausuario']."',serie='".$row['serie']."',folio='".$row['folio_inicial']."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$Factura['obs']."',
			cliente='".$Factura['cliente']."',tipo_pago='".$Factura['tipo_pago']."',forma_pago='".$Factura['forma_pago']."',usuario='".$_POST['cveusuario']."',baniva_retenido='".$Factura['baniva_retenido']."',banisr_retenido='".$Factura['banisr_retenido']."',
			carta_porte='".$Factura['carta_porte']."',load_cliente='".$Factura['load']."',nombre_cliente='".$Factura['nombre_cliente']."',direccion_cliente='".$Factura['direccion_cliente']."',
			tipopago_cliente='".$Factura['tipopago_cliente']."',banco_cliente='".$Factura['banco_cliente']."',cuenta_cliente='".$Factura['cuenta_cliente']."',tipo_factura='".$Factura['tipo_factura']."',
			tipo_relacion='01',uuidsrelacionados='".$Factura['uuid']."',
			factura='".$Factura['cve']."',engomado='".$Factura['engomado']."',banco='".$Factura['banco']."',cuenta_cheque='".$Factura['cuenta_cheque']."',tiene_descuento='".$Factura['tiene_descuento']."'".$datossucursal;
		}
		
		$cvefact=mysql_insert_id();
		$documento=array();
		require_once("../nusoap/nusoap.php");
		$fserie=$row['serie'];
		$ffolio=$row['folio_inicial'];
		//Generamos la Factura
		$documento['serie']=$row['serie'];
		$documento['folio']=$row['folio_inicial'];
		$documento['fecha']=fechaLocal().' '.horaLocal();
		$documento['formapago']=$array_forma_pago[$Factura['forma_pago']];
		$documento['idtipodocumento']=2;
		$documento['observaciones']=$Factura['obs'];
		$documento['metodopago']='NA';
		$res = mysql_query("SELECT * FROM clientes WHERE cve='".$Factura['cliente']."'");
		$row = mysql_fetch_array($res);
		$emailenvio = $row['email'];
		$row['cve']=0;
		$documento['receptor']['codigo']=$row['cve'];
		$documento['receptor']['rfc']=$row['rfc'];
		$documento['receptor']['nombre']=$row['nombre'];
		$documento['receptor']['calle']=$row['calle'];
		$documento['receptor']['num_ext']=$row['numexterior'];
		$documento['receptor']['num_int']=$row['numinterior'];
		$documento['receptor']['colonia']=$row['colonia'];
		$documento['receptor']['localidad']=$row['localidad'];
		$documento['receptor']['municipio']=$row['municipio'];
		$documento['receptor']['estado']=$row['estado'];
		$documento['receptor']['pais']='MEXICO';
		$documento['receptor']['codigopostal']=$row['codigopostal'];
		//Agregamos los conceptos
		$i=0;
		

			$rowD['cantidad'] = 1;
			$rowD['precio'] = $Factura['subtotal'];
			$rowD['importe'] = $Factura['total'];
			$rowD['importe_iva'] = $Factura['iva'];
			$rowD['iva'] = 16;
			$rowD['descuento'] = 0;

			if(trim($rowD['unidad'])=="") $rowD['unidad'] = "Unidad de servicio";
			mysql_query("INSERT notascreditomov SET plaza='".$Factura['plaza']."',cvefact='$cvefact',cantidad='".$rowD['cantidad']."',concepto='".$_POST['conceptonota']."',
			precio='".$rowD['precio']."',descuento='".$rowD['descuento']."',importe='".$rowD['importe']."',iva='".$rowD['iva']."',
			importe_iva='".$rowD['importe_iva']."',unidad='".$rowD['unidad']."',
			engomado='".$rowD['engomado']."',claveprodsat='77121503',claveunidadsat='E48'");
			$documento['conceptos'][$i]['cantidad']=$rowD['cantidad'];
			$documento['conceptos'][$i]['unidad']=$rowD['unidad'];
			$documento['conceptos'][$i]['descripcion']=$_POST['conceptonota'];
			$documento['conceptos'][$i]['valorUnitario']=$rowD['precio'];
			$documento['conceptos'][$i]['importe']=$rowD['importe'];
			$documento['conceptos'][$i]['importe_iva']=$rowD['importe_iva'];
			$i++;

		mysql_query("UPDATE notascredito SET subtotal='".$Factura['subtotal']."',iva='".$Factura['iva']."',total='".$Factura['total']."',
		isr_retenido='".$Factura['isr_retenido']."',por_isr_retenido='".$Factura['por_isr_retenido']."',
		iva_retenido='".$Factura['iva_retenido']."',por_iva_retenido='".$Factura['por_iva_retenido']."' WHERE plaza='".$Factura['plaza']."' AND cve=".$cvefact);
		mysql_query("UPDATE cobro_engomado SET notacredito = '$cvefact' WHERE plaza='".$Factura['plaza']."' AND factura='".$Factura['cve']."' AND factura>0");
		mysql_query("UPDATE facturas SET estatus = 'D' WHERE plaza='".$Factura['plaza']."' AND cve='".$Factura['cve']."'");
		mysql_query("UPDATE venta_engomado_factura SET notacredito = '$cvefact' WHERE plaza='".$Factura['plaza']."' AND factura='".$Factura['cve']."'");
		$documento['subtotal']=$Factura['subtotal'];
		$documento['descuento']=0;
		//Traslados
		#IVA
		if($Factura['iva']>0){
			$documento['tasaivatrasladado']=16;
			$documento['ivatrasladado']=$Factura['iva'];  //Solo 200 grava iva
		}
		if($Factura['iva_retenido'] > 0){
			$documento['ivaretenido']=$Factura['iva_retenido'];  
		}
		if($Factura['isr_retenido'] > 0){
			$documento['isrretenido']=$Factura['isr_retenido'];  
		}
		//total
		$documento['total']=$Factura['total'];
		//Moneda
		$documento['moneda']     = 1; //1=pesos, 2=Dolar, 3=Euro
		$documento['tipocambio'] = 1;
		
		//print_r($documento);
		$documento = genera_arreglo_facturacion($Factura['plaza'], $cvefact, 'E');
		$resultadotimbres = validar_timbres($_POST['plazausuario']);
		if($resultadotimbres['seguir']){
			//$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
			$oSoapClient = new nusoap_client("https://servicios.integratucfdi.net/wscfdi.php?wsdl", true);	
			$err = $oSoapClient->getError();
			if($err!=""){
				echo "error1:".$err;
				desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
			}
			else{
				//print_r($documento);
				$oSoapClient->timeout = 300;
				$oSoapClient->response_timeout = 300;
				$respuesta = $oSoapClient->call("generarComprobante", array ('id' => $rowempresa['idplaza'],'rfcemisor' => $rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'documento' => $documento, 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
				if ($oSoapClient->fault) {
					echo '<p><b>Fault: ';
					print_r($respuesta);
					echo '</b></p>';
					echo '<p><b>Request: <br>';
					echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
					echo '<p><b>Response: <br>';
					echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
					echo '<p><b>Debug: <br>';
					echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
					desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
				}
				else{
					$err = $oSoapClient->getError();
					if ($err){
						echo '<p><b>Error: ' . $err . '</b></p>';
						echo '<p><b>Request: <br>';
						echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
						echo '<p><b>Response: <br>';
						echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
						echo '<p><b>Debug: <br>';
						echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
						desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
					}
					else{
						if($respuesta['resultado']){
							mysql_query("UPDATE notascredito SET respuesta1='".$respuesta['uuid']."',seriecertificado='".$respuesta['seriecertificado']."',
							sellodocumento='".$respuesta['sellodocumento']."',uuid='".$respuesta['uuid']."',seriecertificadosat='".$respuesta['seriecertificadosat']."',
							sellotimbre='".$respuesta['sellotimbre']."',cadenaoriginal='".$respuesta['cadenaoriginal']."',
							fechatimbre='".substr($respuesta['fechatimbre'],0,10)." ".substr($respuesta['fechatimbre'],-8)."'
							WHERE plaza='".$_POST['plazausuario']."' AND cve=".$cvefact);
							//Tomar la informacion de Retorno
							$dir="../cfdi/comprobantes/";
							//$dir=dirname(realpath(getcwd()))."/solucionesfe_facturacion/cfdi/comprobantes/";
							//el zip siempre se deja fuera
							$dir2="../cfdi/";
							//Leer el Archivo Zip
							$fileresult=$respuesta['archivos'];
							$strzipresponse=base64_decode($fileresult);
							$filename='cfdinc_'.$_POST['plazausuario'].'_'.$cvefact;
							file_put_contents($dir2.$filename.'.zip', $strzipresponse);
							$zip = new ZipArchive;
							if ($zip->open($dir2.$filename.'.zip') === TRUE){
								$strxml=$zip->getFromName('xml.xml');
								file_put_contents($dir.$filename.'.xml', $strxml);
								//$strpdf=$zip->getFromName('formato.pdf');
								//file_put_contents($dir.$filename.'.pdf', $strpdf);
								$zip->close();		
								generaFacturaPdf($_POST['plazausuario'],$cvefact,0,2);
								if($emailenvio!=""){
									$mail = obtener_mail();
									$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
									$mail->Subject = "Nota de Credito ".$fserie." ".$ffolio;
									$mail->Body = "Nota de Credito ".$fserie." ".$ffolio;
									//$mail->AddAddress(trim($emailenvio));
									$correos = explode(",",trim($emailenvio));
									foreach($correos as $correo)
										$mail->AddAddress(trim($correo));
									/*if($rowempresa['email']!=""){
										$correos = explode(",",trim($rowempresa['email']));
										foreach($correos as $correo)
											$mail->AddCC(trim($correo));
									}*/
									$mail->AddAttachment("../cfdi/comprobantes/nc_".$_POST['plazausuario']."_".$cvefact.".pdf", "Nota de Credito ".$fserie." ".$ffolio.".pdf");
									$mail->AddAttachment("../cfdi/comprobantes/cfdinc_".$_POST['plazausuario']."_".$cvefact.".xml", "Nota de Credito ".$fserie." ".$ffolio.".xml");
									$mail->Send();
								}	
								/*if($rowempresa['email']!=""){
									$mail = new PHPMailer;		
									$mail->isSMTP();                                      // Set mailer to use SMTP
									$mail->Host = 'smtp.gmail.com';                     // Specify main and backup SMTP servers
									$mail->Port = 587;
									$mail->SMTPAuth = true;                               // Enable SMTP authentication
									$mail->Username = 'gverificentros@gmail.com';   // SMTP username
									$mail->Password = 'loscocos720';                           // SMTP password
									$mail->SMTPSecure = 'tls';    
									$mail->From = "gverificentros@gmail.com";
									$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
									$mail->Subject = "Nota de Credito ".$fserie." ".$ffolio;
									$mail->Body = "Nota de Credito ".$fserie." ".$ffolio;
									//$mail->AddAddress(trim($rowempresa['email']));
									$correos = explode(",",trim($rowempresa['email']));
									foreach($correos as $correo)
										$mail->AddAddress(trim($correo));
									$mail->AddAttachment("../cfdi/comprobantes/nc_".$_POST['plazausuario']."_".$cvefact.".pdf", "Nota de Credito ".$fserie." ".$ffolio.".pdf");
									$mail->AddAttachment("../cfdi/comprobantes/cfdinc_".$_POST['plazausuario']."_".$cvefact.".xml", "Nota de Credito ".$fserie." ".$ffolio.".xml");
									$mail->Send();
								}	*/
								@unlink("../cfdi/comprobantes/nc_".$_POST['plazausuario']."_".$cvefact.".pdf");
							}
							else 
								$strmsg='Error al descomprimir el archivo';
							if(file_exists($dir2.$filename.'.zip')){
								unlink($dir2.$filename.'.zip');
							}
							echo '<h2>Se genero el folio de nota de credito '.$fserie." ".$ffolio.'</h2>';
						}
						else{
							$strmsg=$respuesta['mensaje'];
							desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
						}
						//print_r($respuesta);	
						echo $strmsg;
					}
				}
			}
		}
	}
	else{
		echo '<h2>Error en la factura</h2>';
	}
	$_POST['cmd'] = 'recargar';
	$_POST['reg'] = 0;
}

if($_POST['cmd']==3){
	$res = mysql_query("SELECT * FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	$fserie=$row['serie'];
	$ffolio=$row['folio'];
	if($row['estatus']!='C'){
		$cvefact=$row['cve'];
		if($row['respuesta1']!=""){
			$res1 = mysql_query("SELECT * FROM clientes WHERE cve='".$row['cliente']."'");
			$row1 = mysql_fetch_array($res1);
			$emailenvio = $row1['email'];
			$resultadotimbres = validar_timbres($_POST['plazausuario']);
			if($resultadotimbres['seguir']){
				require_once("../nusoap/nusoap.php");
				//$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
				$oSoapClient = new nusoap_client("https://servicios.integratucfdi.net/wscfdi.php?wsdl", true);	
				$err = $oSoapClient->getError();
				if($err!=""){
					echo "error1:".$err;
					desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
				}
				else{
					//print_r($documento);
					$oSoapClient->timeout = 300;
					$oSoapClient->response_timeout = 300;
					$respuesta = $oSoapClient->call("cancelarCFDISAT", array ('id' => $rowempresa['idplaza'],'rfcemisor' =>$rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'uuid' => $row['respuesta1'], 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass'],'motivo' => $_POST['motivocancelacion'], 'uuidsustituye' => $_POST['uuidsustituye'],'rfcreceptor'=>$row1['rfc'],'importe'=>$row['total']));
					if ($oSoapClient->fault) {
						echo '<p><b>Fault: ';
						print_r($respuesta);
						echo '</b></p>';
						echo '<p><b>Request: <br>';
						echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
						echo '<p><b>Response: <br>';
						echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
						echo '<p><b>Debug: <br>';
						echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
						desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
					}
					else{
						$err = $oSoapClient->getError();
						if ($err){
							echo '<p><b>Error: ' . $err . '</b></p>';
							echo '<p><b>Request: <br>';
							echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
							echo '<p><b>Response: <br>';
							echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
							echo '<p><b>Debug: <br>';
							echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
							desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
						}
						else{
							if($respuesta['resultado']){
								mysql_query("UPDATE facturas SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."',respuesta2='".$respuesta['mensaje']."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
								generaFacturaPdf($_POST['plazausuario'],$cvefact);
								mysql_query("UPDATE cobro_engomado SET factura=0 WHERE plaza='".$_POST['plazausuario']."' AND factura='".$_POST['reg']."'");
								mysql_query("UPDATE prefacturas SET factura=0,estatus='A' WHERE plaza='".$_POST['plazausuario']."' AND factura='".$_POST['reg']."'");
								if($emailenvio!=""){
									//$mail = new PHPMailer();
									//$mail->Host = "localhost";
									$mail = obtener_mail();
									$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
									$mail->Subject = "Cancelacion Factura ".$fserie." ".$ffolio;
									$mail->Body = "Cancelacion Factura ".$fserie." ".$ffolio;
									//$mail->AddAddress(trim($emailenvio));
									$correos = explode(",",trim($emailenvio));
									foreach($correos as $correo)
										$mail->AddAddress(trim($correo));
									/*if($rowempresa['email']!=""){
										$correos = explode(",",trim($rowempresa['email']));
										foreach($correos as $correo)
											$mail->AddCC(trim($correo));
									}*/
									$mail->AddAttachment("../cfdi/comprobantes/facturac_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$fserie." ".$ffolio.".pdf");
									$mail->AddAttachment("../cfdi/comprobantes/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$fserie." ".$ffolio.".xml");
									$mail->Send();
								}	
								/*if($rowempresa['email']!=""){
									$mail = new PHPMailer;		
									$mail->isSMTP();                                      // Set mailer to use SMTP
									$mail->Host = 'smtp.gmail.com';                     // Specify main and backup SMTP servers
									$mail->Port = 587;
									$mail->SMTPAuth = true;                               // Enable SMTP authentication
									$mail->Username = 'gverificentros@gmail.com';   // SMTP username
									$mail->Password = 'loscocos720';                           // SMTP password
									$mail->SMTPSecure = 'tls';    
									$mail->From = "gverificentros@gmail.com";
									$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
									$mail->Subject = "Cancelacion Factura ".$fserie." ".$ffolio;
									$mail->Body = "Cancelacion Factura ".$fserie." ".$ffolio;
									//$mail->AddAddress(trim($rowempresa['email']));
									$correos = explode(",",trim($rowempresa['email']));
									foreach($correos as $correo)
										$mail->AddAddress(trim($correo));
									$mail->AddAttachment("../cfdi/comprobantes/facturac_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$fserie." ".$ffolio.".pdf");
									$mail->AddAttachment("../cfdi/comprobantes/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$fserie." ".$ffolio.".xml");
									$mail->Send();
								}*/
								@unlink("../cfdi/comprobantes/facturac_".$_POST['plazausuario']."_".$cvefact.".pdf");
							}
							else{
								$strmsg=$respuesta['mensaje'];
								//print_r($row);
								//print_r($rowempresa);
								//print_r($respuesta);	
								echo 'Mensaje de error: '.$strmsg;
								desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
							}
						}
					}
				}
			}
		}
		else{
			mysql_query("UPDATE facturas SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
			//generaFacturaPdf($_POST['plazausuario'],$cvefact);
			mysql_query("UPDATE cobro_engomado SET factura=0,documento=2 WHERE plaza='".$_POST['plazausuario']."' AND factura='".$_POST['reg']."'");
			mysql_query("UPDATE prefacturas SET factura=0,estatus='A' WHERE plaza='".$_POST['plazausuario']."' AND factura='".$_POST['reg']."'");
			$Facturas = mysql_query("SELECT * FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."' AND usuario='-1'");
			while($Factura = mysql_fetch_array($Facturas)){
				$plaza = $Factura['plaza'];
				$tickets = implode(',',$datos['ticket']);
				$res = mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$plaza."' AND factura='".$Factura['cve']."'");
				$row=mysql_fetch_array($res);
				$cliente = mysql_query("SELECT * FROM clientes WHERE cve='".$Factura['cliente']."'");
				$datos = mysql_fetch_array($cliente);

				$forma_pago = 0;
				if($row['tipo_pago']==5) $tipo_pago=7;
				elseif($row['tipo_pago']==7) $tipo_pago=9;
				elseif($row['tipo_pago']==6) $tipo_pago=$datos['tipo_pago'];
				else $tipo_pago=0;
				$cuentapago='NO APLICA';
				if($tipo_pago == 2 || $tipo_pago == 5){
					$cuentapago = $datos['cuenta'];
				}
				$res1 = mysql_query("SELECT * FROM plazas WHERE cve='".$plaza."'");
				$row1 = mysql_fetch_array($res1);
				$numeroPlaza=$row1['numero'];
				$res1 = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$plaza."'");
				$row1 = mysql_fetch_array($res1);
				$html = '<h2>No se pudo timbrar la siguiente factura favor de revisar sus datos y volver a generar la factura</h2><table width="100%"><tr><td width="30%"><img src="logos/logo1.jpg" width="300px" height="100px"></td>
					<td width="40%">'.utf8_encode($row1['nombre']).'<br>
					'.utf8_encode($row1['rfc'].' '.$row1['calle'].' '.$row1['numexterior'].' '.$row1['numinterior']).'<br>
					'.utf8_encode($row1['colonia']).',<br>
					'.utf8_encode($row1['localidad'].' '.$row1['codigopostal']).'<br>
					'.utf8_encode($row1['municipio']).' '.utf8_encode($row1['estado']).'<br>
					MEXICO</td><td width="30%">&nbsp;</td></tr></table>';
				$html .= '<br><br>';
				$html .= '<table width="100%"><tr><th width="50%">R E C E P T O R</th><td width="50%"></tr>
				<tr><td valign="top">
					<table>
					<tr><th align="left">Cliente:</th><td colspan=3>'.($datos['nombre']).'</td></tr>
					<tr><th align="left">R.F.C.:</th><td colspan=3>'.($datos['rfc']).'</td></tr>
					<tr style="display:none;"><th align="left">Domicilio:</th><td colspan=3>'.($datos['calle'].' No. '.$datos['numexterior'].' '.$datos['numinterior']).'</td></tr>
					<tr style="display:none;"><th align="left">Colonia:</th><td colspan=3>'.($datos['colonia']).'</td></tr>
					<!--<tr><th align="left">Localidad:</th><td colspan=3>'.($datos['localidad']).'</td></tr>-->
					<tr style="display:none;"><th align="left">Municipio:</th><td colspan=3>'.($datos['municipio']).'</td></tr>
					<tr style="display:none;"><th align="left">Estado:</th><td colspan=3>'.($datos['estado']).'</td></tr>
					<tr style="display:none;"><th align="left">C.P.:</th><td>'.($datos['codigopostal']).'</td><th>PAIS:</th><td>MEXICO</td></tr>
					</table></td><td valign="top">
					<table>
					<tr><th align="left">REGIMEN</th><td>'.utf8_encode($row1['regimen']).'</td></tr>
					<tr><th align="left" colspan="2">LUGAR DE EXPEDICI&Oacute;N</th></tr>
					<tr><td copslan="2">'.utf8_encode($row1['calle'].' '.$row1['numexterior'].' '.$row1['numinterior']).'<br>
					'.utf8_encode($row1['colonia']).',<br>
					'.utf8_encode($row1['localidad'].', '.$row1['codigopostal'].', '.$row1['municipio'].', '.$row1['estado']).', MEXICO</td></tr>
					</table></td></tr></table>';
				$html .= '<br><br>';
				$html .= '<table width="100%" border="1">
				<tr><th>Cantidad</th><th>Unidad</th><th>Concepto/Descripci&oacute;n</th><th>Valor Unit</th><th>Importe</th></tr>';

				$res=mysql_query("SELECT * FROM facturasmov WHERE plaza='".$plaza."' AND cvefact = '".$Factura['cve']."'");
				while($row=mysql_fetch_array($res)){
				
				
					$html .= '<tr><td align="right">'.$row['cantidad'].'</td>
					<td align="center">'.$row['unidad'].'</td>
					<td align="left">'.$row['concepto'].'</td>
					<td align="right">'.number_format($row['precio'],2).'</td>
					<td align="right">'.number_format($row['importe'],2).'</td>
					</tr>';
				}
				$html .= '<tr><th colspan="3">IMPORTE CON LETRA</th><td colspan="2" rowspan="2" align="center">
				<table width="80%" border="1">
				<tr><td>SUBTOTAL:</td><td align="right">'.number_format($Factura['subtotal'],2).'</td></tr>
				<tr><td>I.V.A. 16%:</td><td align="right">'.number_format($Factura['iva'],2).'</td></tr>';
				
				$html .= '
				<tr><td>TOTAL:</td><td align="right">'.number_format($Factura['total'],2).'</td></tr>
				</table></td></tr>';
				
				$html .= '<tr><td colspan="3" align="left">'.utf8_encode(numlet($Factura['total'])).'<br><br>
				M&eacute;todo pago: '.$array_forma_pago[$forma_pago].'
				Forma de pago: '.$array_tipo_pago[$tipo_pago].'<br>
				Condiciones: CONTADO<br>
				No. Cta pago: '.$cuentapago.'<br></td></tr></table>';

				if($datos['email']!=""){
					$mail = obtener_mail();
					$mail->FromName = "Verificentros";
					$mail->Subject = "Error Timbrado Factura ";
					$mail->isHTML(true);
					$mail->Body = $html;
					$correos = explode(",",trim($datos['email']));
					foreach($correos as $correo)
						$mail->AddAddress(trim($correo));
					/*if($rowempresa['email']!=""){
						$correos = explode(",",trim($rowempresa['email']));
						foreach($correos as $correo)
							$mail->AddCC(trim($correo));
					}*/
					$mail->Send();
				}	
			}
		}
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==2){
	$_POST['tipo_serie'] = 0;
	$resplaza = mysql_query("SELECT * FROM plazas WHERE cve='".$_POST['plazausuario']."'");
	$rowplaza = mysql_fetch_array($resplaza);
	if($_POST['tipo_serie']==0)
		$res = mysql_query("SELECT serie,folio_inicial FROM foliosiniciales WHERE plaza='".$_POST['plazausuario']."' AND tipo=0 AND tipodocumento=1");
	else
		$res = mysql_query("SELECT serie,folio_inicial FROM foliosiniciales WHERE plaza='".$_POST['plazausuario']."' AND tipo=0 AND tipodocumento=5");
	$row = mysql_fetch_array($res);
	$resClienteCuenta=mysql_query("SELECT * FROM clientes_cuentas WHERE cve='".$_POST['cliente_cuenta']."'");
	$ClienteCuenta=mysql_fetch_array($resClienteCuenta);
	$res1 = mysql_query("SELECT IFNULL(MAX(folio+1),1) FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND serie='".$row['serie']."'");
	$row1 = mysql_fetch_array($res1);
	if($row['folio_inicial']<$row1[0]){
		$row['folio_inicial'] = $row1[0];
	}
	$insert = "INSERT facturas SET plaza='".$_POST['plazausuario']."',serie='".$row['serie']."',folio='".$row['folio_inicial']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
	cliente='".$_POST['cliente']."',tipo_pago='".$_POST['tipo_pago']."',forma_pago='".$_POST['forma_pago']."',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
	carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
	tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."',
	engomado='".$_POST['engomado']."',banco='".$ClienteCuenta['banco']."',cuenta_cheque='".$ClienteCuenta['cuenta']."',tiene_descuento='".$_POST['tiene_descuento']."', tipo_serie='".$_POST['tipo_serie']."',
	tipo_relacion='".$_POST['tipo_relacion']."',uuidsrelacionados='".$_POST['uuidsrelacionados']."', periodicidad='{$_POST['periodicidad']}', meses='{$_POST['meses']}', anio='{$_POST['anio']}',
	tipo_documento_origen='".$_POST['tipo_documento_origen']."',tipo_pag='".$_POST['tipo_pag']."'".$datossucursal;
	while(!$resinsert=mysql_query($insert)){
		$row['folio_inicial']++;
		$insert = "INSERT facturas SET plaza='".$_POST['plazausuario']."',serie='".$row['serie']."',folio='".$row['folio_inicial']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."', tipo_serie='".$_POST['tipo_serie']."',
		cliente='".$_POST['cliente']."',tipo_pago='".$_POST['tipo_pago']."',forma_pago='".$_POST['forma_pago']."',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
		carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
		tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."',
		engomado='".$_POST['engomado']."',banco='".$ClienteCuenta['banco']."',cuenta_cheque='".$ClienteCuenta['cuenta']."',tiene_descuento='".$_POST['tiene_descuento']."',
		tipo_relacion='".$_POST['tipo_relacion']."',uuidsrelacionados='".$_POST['uuidsrelacionados']."', periodicidad='{$_POST['periodicidad']}', meses='{$_POST['meses']}', anio='{$_POST['anio']}',
		tipo_documento_origen='".$_POST['tipo_documento_origen']."',tipo_pag='".$_POST['tipo_pag']."'".$datossucursal;
	}
	/*$res1 = mysql_query("SELECT cve FROM facturas WHERE plaza='".$_POST['plazausuario']."'");
	if(mysql_num_rows($res1) > 0){
		mysql_query("INSERT facturas SET plaza='".$_POST['plazausuario']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
		cliente='".$_POST['cliente']."',tipo_pago='".$_POST['tipo_pago']."',forma_pago='".$_POST['forma_pago']."',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
		carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
		tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."',
		engomado='".$_POST['engomado']."',banco='".$ClienteCuenta['banco']."',cuenta_cheque='".$ClienteCuenta['cuenta']."'".$datossucursal) or die(mysql_error());
	}
	else{
		mysql_query("INSERT facturas SET plaza='".$_POST['plazausuario']."',cve='".$row['folio_inicial']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
		cliente='".$_POST['cliente']."',tipo_pago='".$_POST['tipo_pago']."',forma_pago='".$_POST['forma_pago']."',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
		carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
		tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."',
		engomado='".$_POST['engomado']."',banco='".$ClienteCuenta['banco']."',cuenta_cheque='".$ClienteCuenta['cuenta']."'".$datossucursal) or die(mysql_error());
	}*/
	$cvefact=mysql_insert_id();
	$documento=array();
	require_once("../nusoap/nusoap.php");
	$fserie=$row['serie'];
	$ffolio=$row['folio_inicial'];
	//Generamos la Factura
	$documento['serie']=$rowplaza['numero'];
	$documento['folio']=$ffolio;
	$documento['fecha']=$_POST['fecha'].' '.horaLocal();
	$documento['formapago']=$array_forma_pago[$_POST['forma_pago']];
	$documento['idtipodocumento']=1;
	$documento['observaciones']=$_POST['obs'];
	$documento['metodopago']=$array_tipo_pagosat[$_POST['tipo_pago']];
	$res = mysql_query("SELECT * FROM clientes WHERE cve='".$_POST['cliente']."'");
	$row = mysql_fetch_array($res);
	$emailenvio = $row['email'];
	if($_POST['tipo_pago']==2 || $_POST['tipo_pago']==5)
		$documento['numerocuentapago']=$ClienteCuenta['cuenta'];
	$row['cve']=0;
	$documento['receptor']['codigo']=$row['cve'];
	$documento['receptor']['rfc']=$row['rfc'];
	$documento['receptor']['nombre']=$row['nombre'];
	$documento['receptor']['calle']=$row['calle'];
	$documento['receptor']['num_ext']=$row['numexterior'];
	$documento['receptor']['num_int']=$row['numinterior'];
	$documento['receptor']['colonia']=$row['colonia'];
	$documento['receptor']['localidad']=$row['localidad'];
	$documento['receptor']['municipio']=$row['municipio'];
	$documento['receptor']['estado']=$row['estado'];
	$documento['receptor']['pais']='MEXICO';
	$documento['receptor']['codigopostal']=$row['codigopostal'];
	//Agregamos los conceptos
	$i=0;
	foreach($_POST['cant'] as $k=>$v){
		if($v>0){
			if ($_POST['engomado']==1){
				$_POST['claveprod'][$k]='77121503';
			}
			$_POST['claveunid'][$k]='E48';
			if($_POST['claveprod'][$k]=='80141600'){
				$_POST['claveunid'][$k]='H87';
			}
			if(trim($_POST['unidad'][$k])=="") $_POST['unidad'][$k] = "Unidad de servicio";
			$importe_iva=round($_POST['importe'][$k]*$_POST['ivap'][$k]/100,2);
			mysql_query("INSERT facturasmov SET plaza='".$_POST['plazausuario']."',cvefact='$cvefact',cantidad='".$v."',concepto='".$_POST['concepto'][$k]."',
			precio='".$_POST['precio'][$k]."',importe='".$_POST['importe'][$k]."',iva='".$_POST['ivap'][$k]."',importe_iva='$importe_iva',unidad='".$_POST['unidad'][$k]."',
			engomado='".$_POST['engomado_id'][$k]."',claveprodsat='{$_POST['claveprod'][$k]}',claveunidadsat='{$_POST['claveunid'][$k]}'");
			$documento['conceptos'][$i]['cantidad']=$v;
			$documento['conceptos'][$i]['unidad']=$_POST['unidad'][$k];
			$documento['conceptos'][$i]['descripcion']=$_POST['concepto'][$k];
			$documento['conceptos'][$i]['valorUnitario']=$_POST['precio'][$k];
			$documento['conceptos'][$i]['importe']=$_POST['importe'][$k];
			$documento['conceptos'][$i]['importe_iva']=$importe_iva;
			$i++;
		}
	}
	mysql_query("UPDATE facturas SET subtotal='".$_POST['subtotal']."',iva='".$_POST['iva']."',total='".$_POST['total']."',
	isr_retenido='".$_POST['isr_retenido']."',por_isr_retenido='".$_POST['por_isr_retenido']."',
	iva_retenido='".$_POST['iva_retenido']."',por_iva_retenido='".$_POST['por_iva_retenido']."' WHERE plaza='".$_POST['plazausuario']."' AND cve=".$cvefact);
	if($_POST['tipo_documento_origen']==2){
		mysql_query("UPDATE pagos_caja SET factura='".$cvefact."' WHERE plaza='".$_POST['plazausuario']."' AND cve IN (".$_POST['pagoscaja'].")");
	}
	else{
		mysql_query("UPDATE cobro_engomado SET factura='".$cvefact."',documento=1,notacredito=0 WHERE plaza='".$_POST['plazausuario']."' AND cve IN (".$_POST['ticket'].")");
		mysql_query("INSERT INTO venta_engomado_factura (plaza,venta,factura) SELECT ".$_POST['plazausuario'].",cve,factura FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND factura='".$cvefact."'");
	}

	$documento['subtotal']=$_POST['subtotal'];
	$documento['descuento']=0;
	//Traslados
	#IVA
	if($_POST['iva']>0){
		$documento['tasaivatrasladado']=16;
		$documento['ivatrasladado']=$_POST['iva'];  //Solo 200 grava iva
	}
	if($_POST['iva_retenido'] > 0){
		$documento['ivaretenido']=$_POST['iva_retenido'];  
	}
	if($_POST['isr_retenido'] > 0){
		$documento['isrretenido']=$_POST['isr_retenido'];  
	}
	//total
	$documento['total']=$_POST['total'];
	//Moneda
	$documento['moneda']     = 1; //1=pesos, 2=Dolar, 3=Euro
	$documento['tipocambio'] = 1;
	
	//print_r($documento);
	$documento = genera_arreglo_facturacion($_POST['plazausuario'], $cvefact, 'I');
	$resultadotimbres = validar_timbres($_POST['plazausuario']);
	if($resultadotimbres['seguir']){
		//$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
		$oSoapClient = new nusoap_client("https://servicios.integratucfdi.net/wscfdi.php?wsdl", true);	
		$err = $oSoapClient->getError();
		if($err!=""){
			echo "error1:".$err;
			desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
		}
		else{
			//print_r($documento);
			$oSoapClient->timeout = 300;
			$oSoapClient->response_timeout = 300;
			$respuesta = $oSoapClient->call("generarComprobante", array ('id' => $rowempresa['idplaza'],'rfcemisor' => $rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'documento' => $documento, 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
			if ($oSoapClient->fault) {
				echo '<p><b>Fault: ';
				print_r($respuesta);
				echo '</b></p>';
				echo '<p><b>Request: <br>';
				echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
				echo '<p><b>Response: <br>';
				echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
				echo '<p><b>Debug: <br>';
				echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
				desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
			}
			else{
				$err = $oSoapClient->getError();
				if ($err){
					echo '<p><b>Error: ' . $err . '</b></p>';
					echo '<p><b>Request: <br>';
					echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
					echo '<p><b>Response: <br>';
					echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
					echo '<p><b>Debug: <br>';
					echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
					desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
				}
				else{
					if($respuesta['resultado']){
						mysql_query("UPDATE facturas SET respuesta1='".$respuesta['uuid']."',seriecertificado='".$respuesta['seriecertificado']."',
						sellodocumento='".$respuesta['sellodocumento']."',uuid='".$respuesta['uuid']."',seriecertificadosat='".$respuesta['seriecertificadosat']."',
						sellotimbre='".$respuesta['sellotimbre']."',cadenaoriginal='".$respuesta['cadenaoriginal']."',
						fechatimbre='".substr($respuesta['fechatimbre'],0,10)." ".substr($respuesta['fechatimbre'],-8)."'
						WHERE plaza='".$_POST['plazausuario']."' AND cve=".$cvefact);
						mysql_query("UPDATE facturas SET rfc_cli='".$row['rfc']."', nombre_cli='".$row['nombre']."', calle_cli='".$row['calle']."', numext_cli='".$row['numexterior']."', numint_cli = '".$row['numinterior']."', colonia_cli = '".$row['colonia']."', localidad_cli = '".$row['localidad']."', municipio_cli = '".$row['municipio']."',
							estado_cli='".$row['estado']."', cp_cli='".$row['codigopostal']."'
						WHERE plaza='".$_POST['plazausuario']."' AND cve=".$cvefact);
						//Tomar la informacion de Retorno
						$dir="../cfdi/comprobantes/";
						//$dir=dirname(realpath(getcwd()))."/solucionesfe_facturacion/cfdi/comprobantes/";
						//el zip siempre se deja fuera
						$dir2="../cfdi/";
						//Leer el Archivo Zip
						$fileresult=$respuesta['archivos'];
						$strzipresponse=base64_decode($fileresult);
						$filename='cfdi_'.$_POST['plazausuario'].'_'.$cvefact;
						file_put_contents($dir2.$filename.'.zip', $strzipresponse);
						$zip = new ZipArchive;
						if ($zip->open($dir2.$filename.'.zip') === TRUE){
							$strxml=$zip->getFromName('xml.xml');
							file_put_contents($dir.$filename.'.xml', $strxml);
							//$strpdf=$zip->getFromName('formato.pdf');
							//file_put_contents($dir.$filename.'.pdf', $strpdf);
							$zip->close();		
							generaFacturaPdf($_POST['plazausuario'],$cvefact);
							if($emailenvio!=""){
								//$mail = new PHPMailer();
								//$mail->Host = "localhost";
								$mail = obtener_mail();
								$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
								$mail->Subject = "Factura ".$fserie." ".$ffolio;
								$mail->Body = "Factura ".$fserie." ".$ffolio;
								//$mail->AddAddress(trim($emailenvio));
								$correos = explode(",",trim($emailenvio));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								/*if($rowempresa['email']!=""){
									$correos = explode(",",trim($rowempresa['email']));
									foreach($correos as $correo)
										$mail->AddCC(trim($correo));
								}*/
								$mail->AddAttachment("../cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$fserie." ".$ffolio.".pdf");
								$mail->AddAttachment("../cfdi/comprobantes/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$fserie." ".$ffolio.".xml");
								$mail->Send();
							}	
							/*if($rowempresa['email']!=""){
								$mail = new PHPMailer;		
								$mail->isSMTP();                                      // Set mailer to use SMTP
								$mail->Host = 'smtp.gmail.com';                     // Specify main and backup SMTP servers
								$mail->Port = 587;
								$mail->SMTPAuth = true;                               // Enable SMTP authentication
								$mail->Username = 'gverificentros@gmail.com';   // SMTP username
								$mail->Password = 'loscocos720';                           // SMTP password
								$mail->SMTPSecure = 'tls';    
								$mail->From = "gverificentros@gmail.com";
								$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
								$mail->Subject = "Factura ".$fserie." ".$ffolio;
								$mail->Body = "Factura ".$fserie." ".$ffolio;
								//$mail->AddAddress(trim($rowempresa['email']));
								$correos = explode(",",trim($rowempresa['email']));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("../cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$fserie." ".$ffolio.".pdf");
								$mail->AddAttachment("../cfdi/comprobantes/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$fserie." ".$ffolio.".xml");
								$mail->Send();
							}	*/
							@unlink("../cfdi/comprobantes/factura_".$_POST['plazausuario']."_".$cvefact.".pdf");
						}
						else 
							$strmsg='Error al descomprimir el archivo';
						if(file_exists($dir2.$filename.'.zip')){
							unlink($dir2.$filename.'.zip');
						}
					}
					else{
						$strmsg=$respuesta['mensaje'];
						desbloquear_timbre($_POST['plazausuario'], $resultadotimbres['cvecompra']);
					}
					//print_r($respuesta);	
					echo $strmsg;
				}
			}
		}
	}
	$_POST['cmd']='recargar';
	$_POST['reg']=0;
}


top($_SESSION);
if($_POST['cmd']=='recargar'){
	$res = mysql_query("SELECT recargar_facturas FROM usuarios WHERE cve=1");
	$row = mysql_fetch_array($res);
	if($_POST['reg'] < 2 && $row[0]==1){
		echo '<script>atcr("facturas.php","","recargar",'.($_POST['reg']+1).');</script>';
	}

	$_POST['cmd'] = 0;
}
	$res = mysql_query("SELECT por_iva_retenido, mod_iva_retenido, por_isr_retenido, mod_isr_retenido FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
	$row = mysql_fetch_array($res);
	$por_iva_retenido = $row['por_iva_retenido'];
	$bloquearivaret = " readOnly";
	$claseivaret = "readOnly";
	if($row['mod_iva_retenido'] == 1){
		$bloquearivaret = "";
		$claseivaret = "textField";
	}
	$por_isr_retenido = $row['por_isr_retenido'];
	$bloquearisrret = " readOnly";
	$claseisrret = "readOnly";
	if($row['mod_isr_retenido'] == 1){
		$bloquearisrret = "";
		$claseisrret = "textField";
	}
	if($_POST['cmd']==1){
		echo '<table><tr>';
		if(nivelUsuario()>1 && $bloqueada_sat!=1){
			echo '<td><a href="#" onClick="$(\'#panel\').show();
			if(document.forma.cliente.value==\'0\'){
				alert(\'Necesita seleccionar el cliente\');
				$(\'#panel\').hide();
			}
			else if($(\'#cliente option:selected\').attr(\'facturasinticket\') == \'1\'){
				alert(\'Necesita agregar los tickets a facturar\');
				$(\'#panel\').hide();
			}
			else if(document.forma.tipo_pag.value==\'\'){
				alert(\'Debe de seleccionar el tipo de factura\');
				$(\'#panel\').hide();
			}
			else if($.trim(document.forma.total.value)==\'\'){
				alert(\'El total debe de ser mayor a cero\');
				$(\'#panel\').hide();
			}
			else if((document.forma.tipo_pago.value == \'5\' || document.forma.tipo_pago.value == \'2\') && document.forma.cliente_cuenta.value == \'0\'){
				alert(\'Necesita seleccionar la cuenta de cheque\');
				$(\'#panel\').hide();
			}
			else if((document.forma.forma_pago.value==\'1\'  && document.forma.tipo_pago.value!=\'4\') || (document.forma.forma_pago.value!=\'1\'  && document.forma.tipo_pago.value==\'4\')){
				alert(\'Para pago en parcialidades o diferido solo se puede seleccionar el tipo de pago no definido\');
				$(\'#panel\').hide();
			}
			else if(document.forma.tipo_relacion.value!=\'\' && document.forma.uuidsrelacionados.value==\'\'){
				alert(\'Necesita ingresar los CFDIS relacionados\');
				$(\'#panel\').hide();
			}
			else if(document.forma.periodicidad.value!=\'\' && document.forma.meses.value==\'\'){
				alert(\'Necesita ingresar el mes de la periodicidad\');
				$(\'#panel\').hide();
			}
			else if(document.forma.periodicidad.value!=\'\' && document.forma.anio.value==\'\'){
				alert(\'Necesita ingresar el año de la periodicidad\');
				$(\'#panel\').hide();
			}
			else if(document.forma.carta_porte.checked == true && $.trim(document.forma.load.value)==\'\'){
				alert(\'Necesita ingresar el load\');
				$(\'#panel\').hide();
			}
			else if(document.forma.carta_porte.checked == true && $.trim(document.forma.nombre_cliente.value)==\'\'){
				alert(\'Necesita ingresar el nombre del cliente\');
				$(\'#panel\').hide();
			}
			else if(document.forma.carta_porte.checked == true && $.trim(document.forma.direccion_cliente.value)==\'\'){
				alert(\'Necesita ingresar la direccion del cliente\');
				$(\'#panel\').hide();
			}
			else if(document.forma.carta_porte.checked == true && $.trim(document.forma.tipopago_cliente.value)==\'\'){
				alert(\'Necesita seleccionar el tipo de pago de la carta porte\');
				$(\'#panel\').hide();
			}
			else if((document.forma.total1.value/1)<=0){
				alert(\'La factura debe de ser mayor a cero\');
				$(\'#panel\').hide();
			}
			/*else if($.trim(document.forma.ticket.value) == \'\' && document.forma.engomado.checked == true){
				alert(\'Necesita ingresar los tickets\');
				$(\'#panel\').hide();
			}*/
			else if(!validarConcepto())
			{
				alert(\'Hay detalles sin concepto\');
				$(\'#panel\').hide();
			}
			else if(!validarTicket()){
				$(\'#panel\').hide();
			}
			else if(!validarPagoAnticipado()){
				$(\'#panel\').hide();
			}
			else{
				atcr(\'facturas.php\',\'\',2,\'0\');
			}
			"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		}
		echo '<td><a href="#" onclick="$(\'#panel\').show();atcr(\'facturas.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
		echo '</tr></table>';
		echo '<br>';
		echo '<table>';
		echo '<tr><td align="left">Fecha</td><td><input type="text" name="fecha" id="fecha"  size="15" class="readOnly" value="'.fechaLocal().'" readOnly>&nbsp;&nbsp;<!--<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>--></td></tr>';
		$fecha_rec=date( "Y-m-d" , strtotime ( "-1 day" , strtotime(fechaLocal()) ) );
		echo '<tr style="display:none;"><td align="left">Tipo</td><td><select name="tipo_factura" id="tipo_factura"><option value="0">Factura</option><option value="1">Honorarios</option></select></td></tr>';
		/*echo '<tr><td align="left">Cliente</td><td><select name="cliente" id="cliente"><option value="0">--- Seleccione ---</option>';
		foreach($array_clientes as $k=>$v){
			echo '<option value="'.$k.'" style="color: '.$array_colorcliente[$k].';"';
			if($array_colorcliente[$k] == "#FF0000") echo ' disabled';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';*/
		//echo '<tr><th align="left">Busqueda Cliente</th><td><input type="text" name="busqueda" id="busqueda" class="textField" value=""></td></tr>';
		//echo '<tr><th align="left">RFC</th><td><input type="hidden" name="cliente" id="cliente" value="0"><input type="text" name="rfc" id="rfc" class="readOnly" size="15" value="" readOnly></td></tr>';
		//echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="readOnly" size="50" value="" readOnly></td></tr>';
		echo '<tr><th align="left">Cliente</th><td><select style="width: 500px" name="cliente" id="cliente" onChange="traerCuenta()"><option value="0">Seleccione</option>';
		$res=mysql_query("SELECT * FROM clientes WHERE plaza='".$_POST['plazausuario']."' ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			echo '<option value="'.$row['cve'].'" facturasinticket="'.$array_clientessinticket[$row['cve']].'">'.$row['rfc'].' '.$row['nombre'].'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Tipo Factura</th><td><select name="tipo_pag" id="tipo_pag"><option value="">Seleccione</option>';
		foreach($array_tipo_pag as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Pagos Anticipados</th><td><input type="checkbox" name="tipo_documento_origen" id="tipo_documento_origen" 
		value="2" onClick="
		if(this.checked){
			$(\'#ticket\').parents(\'tr:first\').hide();
			$(\'#ticket\').val(\'\');
			$(\'#pagoscaja\').parents(\'tr:first\').show();
		}
		else{
			$(\'#pagoscaja\').parents(\'tr:first\').hide();
			$(\'#pagoscaja\').val(\'\');
			$(\'#ticket\').parents(\'tr:first\').show();
		}
		"></td></tr>';
		echo '<tr style="display:none;"><th align="left">Folios Pagos Anticipados</th><td><input type="text" name="pagoscaja" id="pagoscaja" class="textField" value=""></tr>';
		echo '<tr><th align="left">Ticket</th><td><input type="text" name="ticket" id="ticket" class="textField" value=""></tr>';
		echo '<tr><td>Factura de Engomado</td><td><input type="checkbox" name="engomado" id="engomado" value="1" onClick="facturar_engomado()" checked></td></tr>';
		echo '<tr><td>Forma de Pago</td><td><select name="forma_pago" id="forma_pago">';
		foreach($array_forma_pago as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Tipo de Pago</td><td><select name="tipo_pago" id="tipo_pago" onChange="if(this.value==\'5\' || this.value==\'2\'){
			$(\'#cliente_cuenta\').parents(\'tr:first\').show();
		}
		else{
			$(\'#cliente_cuenta\').parents(\'tr:first\').hide();
			document.forma.cliente_cuenta.value=\'0\';
		}">';
		unset($array_tipo_pago[6]);
		foreach($array_tipo_pago as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr style="display:none;"><td>Cuenta Cheque</td><td><select name="cliente_cuenta" id="cliente_cuenta"><option value="0">Seleccione</option>';
		echo '</select></td></tr>';
		/*echo '<tr><td align="left">Mes</td><td><select name="mes" id="mes"><option value="0">Seleccione</option>';
		$res = mysql_query("SELECT LEFT(fechaapl,7) FROM depositos WHERE estatus!='C' AND fechaapl>'0000-00-00' GROUP BY LEFT(fechaapl,7) ORDER BY LEFT(fechaapl,7) DESC");
		while($row=mysql_fetch_array($res)){
			$dat=explode("-",$row[0]);
			echo '<option value="'.$row[0].'">'.$array_meses[intval($dat[1])].' '.$dat[0].'</option>';
		}
		echo '</select></td></tr>';*/
		echo '<tr><td>Tipo de Relacion</td><td><select name="tipo_relacion" id="tipo_relacion"><option value="">Ninguna</option>';
		foreach($array_tiporelacionsat as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>CFDIS Relacionados</td><td><input type="text" size="100" class="textField" name="uuidsrelacionados" id="uuidsrelacionados" value=""></td></tr>';
		echo '<tr><td>Periodicidad</td><td><select name="periodicidad" id="periodicidad"><option value="">Ninguna</option><option value="04">Mensual</option></select></td></tr>';
		echo '<tr><td>Mes</td><td><select name="meses" id="meses"><option value="">Ninguna</option>';
		foreach($array_meses as $k=>$v){
			if($k>0)
				echo '<option value="'.sprintf("%02s",$k).'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>A&ntilde;o</td><td><input type="text" size="10" class="textField" name="anio" id="anio" value=""></td></tr>';
		echo '<tr';
		if($rowempresa['carta_porte']!=1){
			echo ' style="display:none;"';
		}
		echo '><td>Carta Porte</th><td><input type="checkbox" id="carta_porte" name="carta_porte" value="1" onClick="
			if(this.checked){ 
				$(\'.rcarta_porte\').show(); 
			}
			else{ 
				$(\'.rcarta_porte\').hide();
			}"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Load</td><td><input type="text" class="textField" name="load" id="load" value="" size="30"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Nombre del Cliente</td><td><input type="text" class="textField" name="nombre_cliente" id="nombre_cliente" value="" size="50"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Direccion del Cliente</td><td><input type="text" class="textField" name="direccion_cliente" id="direccion_cliente" value="" size="100"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Tipo de Pago</td><td><input type="text" class="textField" name="tipopago_cliente" id="tipopago_cliente" value="" size="50"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Banco</td><td><input type="text" class="textField" name="banco_cliente" id="banco_cliente" value="" size="30"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Cuenta</td><td><input type="text" class="textField" name="cuenta_cliente" id="cuenta_cliente" value="" size="30"></td></tr>';
		echo '<tr><td>Observaciones</td><td><textarea class="textField" name="obs" id="obs" cols="30" rows="3"></textarea></td></tr>';
		echo '</table>';
		echo '<input type="hidden" name="clickguardar" id="clickguardar" value="no">';
		echo '<table id="tablaproductos"><tr><th id="encabezadoclaveprod" style="display:none;">Clave Producto SAT</th>';
		echo '<th>Cantidad</th>';
		echo '<th id="encabezadoengomado">Engomado</th>';
		echo '<th>Descripcion</th><th>Precio Unitario</th><th>Importe</th><th style="display:none;">IVA</th></tr>';
		$i=0;
		$cadenaclaveprod='<option value="77121503">77121503</option><option value="80141600">80141600</option>';
		$cadenaengomado='';
		if($i==0){
			echo '<tr class="renglon" ren="'.$i.'">';
			echo '<td align="center" style="display:none;"><select id="claveprod_'.$i.'" name="claveprod['.$i.']">'.$cadenaclaveprod.'</select></td>';
			echo '<td align="center"><input type="text" class="textField" size="10" name="cant['.$i.']" id="cant'.$i.'" value=""  onKeyUp="sumarproductos()"></td>';
			echo '<td align="center"><select name="engomado_id['.$i.']" id="engomado_id'.$i.'" onChange="seleccionengomado('.$i.')">';
			echo '<option value="" nombre="" precio="">Seleccione</option>';
			$cadenaengomado.='<option value="" nombre="" precio="">Seleccione</option>';
			foreach($array_engomado as $k=>$v){
				echo '<option value="'.$k.'" nombre="'.$v.'" precio="'.$array_engomadoprecio[$k].'">'.$v.'</option>';
				$cadenaengomado.='<option value="'.$k.'" nombre="'.$v.'" precio="'.$array_engomadoprecio[$k].'">'.$v.'</option>';
			}
			echo '</select></td>';
			echo '<td><input type="text" name="concepto['.$i.']" id="concepto'.$i.'" class="readOnly" size="50" value="" readOnly></td>';
			echo '<td align="center"><input type="text" class="textField" size="10" name="precio['.$i.']" id="precio'.$i.'" value=""  onKeyUp="sumarproductos()"></td>';
			echo '<td align="center"><input type="text" class="readOnly" size="10" name="importe['.$i.']" id="importe'.$i.'" value="" readOnly></td>';
			echo '<td align="center" style="display:none;"><input type="checkbox" name="ivap['.$i.']" id="ivap'.$i.'" value="16" onClick="sumarproductos()" checked></td>';
			echo '</tr>';
			$i++;
		}
		echo '<tr id="idsubtotal"><th align="right" colspan="4">Subtotal&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="subtotal" id="subtotal" value="" readOnly></td></tr>';
		echo '<tr id="idiva"><th align="right" colspan="4">Iva 16%&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva" id="iva" value="" readOnly></td></tr>';
		echo '<tr id="idtotal1"><th align="right" colspan="4">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total1" id="total1" value="" readOnly></td></tr>';
		echo '<tr style="display:none;" id="idisr_ret"><th align="right" colspan="4"><input type="checkbox" name="banisr_retenido" id="banisr_retenido" value="1" onClick="sumarproductos()">Retencion I.S.R.&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="isr_retenido" id="isr_retenido" value="" readOnly></td><td><input type="text" class="'.$claseisrret.'" size="5" name="por_isr_retenido" id="por_isr_retenido" value="'.$por_isr_retenido.'" onKeyUp="sumarproductos()" '.$bloquearisrret.'>%</td></tr>';
		echo '<tr style="display:none;" id="idiva_ret"><th align="right" colspan="4"><input type="checkbox" name="baniva_retenido" id="baniva_retenido" value="1" onClick="sumarproductos()">Retencion I.V.A.&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva_retenido" id="iva_retenido" value="" readOnly></td><td><input type="text" class="'.$claseivaret.'" size="5" name="por_iva_retenido" id="por_iva_retenido" value="'.$por_iva_retenido.'" onKeyUp="sumarproductos()" '.$bloquearivaret.'>%</td></tr>';
		echo '<tr style="display:none;" id="idtotal"><th align="right" colspan="4">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total" id="total" value="" readOnly></td></tr>';
		echo '</table>';		
		echo '<input type="button" value="Agregar" onClick="agregarproducto()" class="textField">';
		echo '<input type="hidden" name="cantprod" value="'.$i.'">';
		echo '<script>
		$("#cliente").select2();
			function validarConcepto(){
				regresar = true;
				for(i=0;i<(document.forma.cantprod.value/1);i++){
					if((document.getElementById("cant"+i).value/1)>0 && $.trim(document.getElementById("concepto"+i).value)==""){
						regresar = false;
					}
				}
				return regresar;
			}

			function facturar_engomado(){
				if(document.forma.engomado.checked==false){
					$("#encabezadoengomado").hide();
					$("#encabezadoclaveprod").show();
					$(".renglon").each(function(){
						linea = $(this).attr("ren");
						$("#unidad"+linea).removeAttr("readOnly").removeClass("readOnly").addClass("textField").val("");
						$("#concepto"+linea).removeAttr("readOnly").removeClass("readOnly").addClass("textField").val("");
						$("#precio"+linea).removeAttr("readOnly").removeClass("readOnly").addClass("textField").val("");
						$("#engomado_id"+linea).val("").parents("td:first").hide();
						$("#claveprod_"+linea).val("").parents("td:first").show();
					});
					colspan="3";
				}
				else{
					$("#encabezadoclaveprod").hide();
					$("#encabezadoengomado").show();
					$(".renglon").each(function(){
						linea = $(this).attr("ren");
						$("#unidad"+linea).attr("readOnly","readOnly").removeClass("textField").addClass("readOnly").val("");
						$("#concepto"+linea).attr("readOnly","readOnly").removeClass("textField").addClass("readOnly").val("");
						//$("#precio"+linea).attr("readOnly","readOnly").removeClass("textField").addClass("readOnly").val("");
						$("#precio"+linea).removeAttr("readOnly").removeClass("readOnly").addClass("textField").val("");
						$("#engomado_id"+linea).val("").parents("td:first").show();
						$("#claveprod_"+linea).val("").parents("td:first").hide();
					});
					colspan="4";
				}
				$("#idsubtotal").find("th:first").attr("colspan",colspan);
				$("#idiva").find("th:first").attr("colspan",colspan);
				$("#idtotal1").find("th:first").attr("colspan",colspan);
				$("#idisr_ret").find("th:first").attr("colspan",colspan);
				$("#idiva_ret").find("th:first").attr("colspan",colspan);
				$("#idtotal").find("th:first").attr("colspan",colspan);
				sumarproductos();
			}
			
			
			
			var ac_config = {
				source: "facturas.php?ajax=2&plazausuario='.$_POST['plazausuario'].'&cveusuario='.$_POST['cveusuario'].'",
				select: function(event, ui){
					$("#rfc").val(ui.item.rfc);
					$("#nombre").val($("<div />").html(ui.item.nombre).text());
					$("#cliente").val(ui.item.cve);
					$("#cliente_cuenta").html(ui.item.cuentas);
				},
				minLength:3
			};
			$("#busqueda").autocomplete(ac_config);
					
			function agregarproducto(){
				var checkeado=\'\';
				var clase=\'readOnly\';
				var bloqueo=\' readOnly\';
				var estilo=\'\';
				var estilo2=\' style="display:none;"\';
				var colspan="4";
				if(document.forma.engomado.checked==false){
					clase=\'textField\';
					bloqueo=\'\';
					estilo=\' style="display:none;"\';
					estilo2=\'\';
					colspan="3";
				}
				if($("#baniva_retenido").is(":checked")){
					checkeado=\'checked\';
				}
				tot=$("#total").val();
				$("#idtotal").remove();
				subtot=$("#subtotal").val();
				$("#idsubtotal").remove();
				iv=$("#iva").val();
				$("#idiva").remove();
				tot1=$("#total1").val();
				$("#idtotal1").remove();
				iva_ret=$("#iva_retenido").val();
				piva_ret=$("#por_iva_retenido").val();
				$("#idiva_ret").remove();
				isr_ret=$("#isr_retenido").val();
				pisr_ret=$("#por_isr_retenido").val();
				$("#idisr_ret").remove();
				num=document.forma.cantprod.value;
				$("#tablaproductos").append(\'<tr class="renglon" ren="\'+num+\'">\
				<td\'+estilo2+\' align="center"><select id="claveprod_\'+num+\'" name="claveprod[\'+num+\']">'.$cadenaclaveprod.'</select></td>\
				<td align="center"><input type="text" class="textField" size="10" name="cant[\'+num+\']" id="cant\'+num+\'" value=""  onKeyUp="sumarproductos()"></td>\</td>\
				<td\'+estilo+\' align="center"><select name="engomado_id[\'+num+\']" id="engomado_id\'+num+\'" onChange="seleccionengomado(\'+num+\')">'.$cadenaengomado.'</select></td>\
				<td><input type="text" name="concepto[\'+num+\']" id="concepto\'+num+\'" class="\'+clase+\'" size="50" value=""\'+bloqueo+\'></td>\
				<td align="center"><input type="text" class="textField" size="10" name="precio[\'+num+\']" id="precio\'+num+\'" value=""  onKeyUp="sumarproductos()"></td>\
				<td align="center"><input type="text" class="readOnly" size="10" name="importe[\'+num+\']" id="importe\'+num+\'" value="" readOnly></td>\
				<td align="center" style="display:none;"><input type="checkbox" name="ivap[\'+num+\']" id="ivap\'+num+\'" value="16" onClick="sumarproductos()" checked></td>\
				</tr>\
				<tr id="idsubtotal"><th align="right" colspan="\'+colspan+\'">Subtotal&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="subtotal" id="subtotal" value="\'+subtot+\'" readOnly></td></tr>\
				<tr id="idiva"><th align="right" colspan="\'+colspan+\'">Iva 16%&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva" id="iva" value="\'+iv+\'" readOnly></td></tr>\
				<tr id="idtotal1"><th align="right" colspan="\'+colspan+\'">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total1" id="total1" value="\'+tot1+\'" readOnly></td></tr>\
				<tr style="display:none;" id="idisr_ret"><th align="right" colspan="\'+colspan+\'"><input type="checkbox" name="banisr_retenido" id="banisr_retenido" value="1" onClick="sumarproductos()" \'+checkeado+\'>Retencion I.S.R.&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="isr_retenido" id="isr_retenido" value="\'+isr_ret+\'" readOnly></td><td><input type="text" class="'.$claseisrret.'" size="5" name="por_isr_retenido" id="por_isr_retenido" value="\'+pisr_ret+\'" onKeyUp="sumarproductos()" '.$bloquearisrret.'>%</td></tr>\
				<tr style="display:none;" id="idiva_ret"><th align="right" colspan="\'+colspan+\'"><input type="checkbox" name="baniva_retenido" id="baniva_retenido" value="1" onClick="sumarproductos()" \'+checkeado+\'>Retencion I.V.A.&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva_retenido" id="iva_retenido" value="\'+iva_ret+\'" readOnly></td><td><input type="text" class="'.$claseivaret.'" size="5" name="por_iva_retenido" id="por_iva_retenido" value="\'+piva_ret+\'" onKeyUp="sumarproductos()" '.$bloquearivaret.'>%</td></tr>\
				<tr style="display:none;" id="idtotal"><th align="right" colspan="\'+colspan+\'">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total" id="total" value="\'+tot+\'" readOnly></td></tr>\');
				num++;
				document.forma.cantprod.value=num;
			}
			
			function seleccionengomado(linea){
				campoengomado=$("#engomado_id"+linea);
				nombre = campoengomado.find(\'option[value="\'+campoengomado.val()+\'"]\').attr("nombre");
				precio = campoengomado.find(\'option[value="\'+campoengomado.val()+\'"]\').attr("precio");
				$("#concepto"+linea).val(nombre);
				$("#precio"+linea).val(precio);
				sumarproductos();
			}
			
			function sumarproductos(){
				var sumar=0;
				var iv=0;
				var iv_ret=0;
				var is_ret=0;
				for(i=0;i<(document.forma.cantprod.value/1);i++){
					impo=(document.getElementById("cant"+i).value/1)*(document.getElementById("precio"+i).value/1);
					document.getElementById("importe"+i).value=impo.toFixed(2);
					sumar+=(document.getElementById("importe"+i).value/1);
					is_ret+=document.getElementById("importe"+i).value*document.forma.por_isr_retenido.value/100;
					if(document.getElementById("ivap"+i).checked){
						iv+=document.getElementById("importe"+i).value*0.16;
						iv_ret+=document.getElementById("importe"+i).value*document.forma.por_iva_retenido.value/100;
					}
				}
				document.forma.subtotal.value=sumar.toFixed(2);
				document.forma.iva.value=iv.toFixed(2);
				document.forma.total1.value=(document.forma.subtotal.value/1)+(document.forma.iva.value/1);
				if($("#banisr_retenido").is(":checked")){
					document.forma.isr_retenido.value=is_ret.toFixed(2);
				}
				else{
					document.forma.isr_retenido.value=0;
				}
				if($("#baniva_retenido").is(":checked")){
					document.forma.iva_retenido.value=iv_ret.toFixed(2);
				}
				else{
					document.forma.iva_retenido.value=0;
				}
				
				tot=(document.forma.subtotal.value/1)+(document.forma.iva.value/1)-(document.forma.isr_retenido.value/1)-(document.forma.iva_retenido.value/1);
				document.forma.total.value=tot.toFixed(2);
			}

			function traerCuenta(){
				$.ajax({
	                url: "facturas.php",
	                type: "POST",
	                async: false,
	                data: {
	                	ajax: 20,
	                	cliente: document.forma.cliente.value,
	                	plazausuario: document.forma.plazausuario.value
	                },
	                success: function(data) {   
	                	$("#cliente_cuenta").html(data);
	                }
	            });
			}
			
			function validarTicket(){
				var regresar = true;
				if(document.forma.ticket.value!=""){
					$.ajax({
		                url: "facturas.php",
		                type: "POST",
		                async: false,
		                dataType: "json",
		                data: {
		                	ajax: 3,
		                	ticket: document.forma.ticket.value,
		                	plazausuario: document.forma.plazausuario.value,
		                	forma_pago: document.forma.forma_pago.value
		                },
		                success: function(data) {   
		                	if(data.error == 1){
		                		alert(data.mensaje);
		                		regresar = false;
		                	}   
		                }
		            });
				}
				return regresar;
			}

			function validarPagoAnticipado(){
				var regresar = true;
				if(!document.forma.tipo_documento_origen.checked)
					return regresar;
				if(document.forma.ticket.value!=""){
					$.ajax({
		                url: "facturas.php",
		                type: "POST",
		                async: false,
		                dataType: "json",
		                data: {
		                	ajax: 4,
		                	pagoscaja: document.forma.pagoscaja.value,
		                	plazausuario: document.forma.plazausuario.value
		                },
		                success: function(data) {   
		                	if(data.error == 1){
		                		alert(data.mensaje);
		                		regresar = false;
		                	}   
		                }
		            });
				}
				return regresar;
			}
		  </script>';
	}

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<div id="dialog" style="display:none">
		<table>
		<tr><td class="tableEnc">Escriba el concepto para la nota de credito</td></tr>
		</table>
		<table width="100%">
			<tr><td><input type="text" id="conceptonotadialog" class="textField" value="" size="50"></td></tr>
		</table>
		<table>
		<tr><td class="tableEnc">Escriba la observacion para la nota de credito</td></tr>
		</table>
		<table width="100%">
			<tr><td><textarea id="obsnotadialog" class="textField" cols="50" rows="5"></textarea></td></tr>
		</table>
		</div>
		<div id="dialog2" style="display:none">
		<table>
		<tr><td class="tableEnc">Escriba los correos (separados por comas)</td></tr>
		</table>
		<table width="100%">
			<tr><td><input type="text" id="correos_envio" class="textField" value="" size="50"></td></tr>
		</table>
		</div>'; 
		echo '<div id="dialog3" style="display:none">
		<table><tr><th align="left">Motivo Cancelacion</th><td><select id="motivocancelacion" onChange="if(this.value==\'01\'){$(\'#uuidsustituye\').parents(\'tr:first\').show();} else{$(\'#uuidsustituye\').parents(\'tr:first\').hide();$(\'#uuidsustituye\').val(\'\');}"><option value="">Seleccione</option>';
		$res = mysql_query("SELECT * FROM motivos_cancelacion_sat");
		while($row = mysql_fetch_assoc($res)){
			echo '<option value="'.$row['clave'].'">'.$row['clave'].' '.$row['nombre'].'</option>';
		}
		echo '</select></td></tr><tr><th align="left">UUID Sustituye</th><td><input type="text" id="uuidsustituye" value="" class="textField"></td></tr></table>
		<table>
		<tr><td class="tableEnc">Observaciones de Cancelacion para el correo</td></tr>
		</table>
		<table width="100%">
			<tr><td><textarea id="obscancelacion" class="textField" cols="50" rows="5"></textarea></td></tr>
		</table>
		</div>'; 
		echo '<input type="hidden" name="cvecancelacion" value="">';
		echo '<input type="hidden" name="motivocancelacion" value="">';
		echo '<input type="hidden" name="uuidsustituye" value="">';
		echo '<input type="hidden" id="mostrar_motivo_sat" value="">';
		echo '<textarea style="display:none;" name="obscancelacion" cols="50" rows="5"></textarea>';
		echo '<input type="hidden" id="facturanota" value="">';
		echo '<input type="hidden" name="conceptonota" value="">';
		echo '<input type="hidden" name="correos_envio" value="">';
		echo '<textarea style="display:none;" name="obsnota" cols="50" rows="5"></textarea>';
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>';
		if($bloqueada_sat!=1)
			echo '<td><a href="#" onClick="atcr(\'facturas.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0">&nbsp;Nuevo</a></td><td>&nbsp;</td>';
		if(nivelUsuario()>0){
			echo '<td><a href="#" onClick="mostrar_correo()"><img src="images/nuevo.gif" border="0">&nbsp;Reenviar Archivos</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'16\',\'0\');"><img src="../images/zip_grande.png" border="0" width="15px" height="15px" title="Descargar">&nbsp;Descargar Archivos</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'110\',\'0\');"><img src="images/b_print.png" border="0" width="15px" height="15px" title="Descargar">&nbsp;Descargar Excel</a></td><td>&nbsp;</td>';
		}
		echo '</tr>';
		echo '</table>';
		echo '<table><tr><td><table>';
		echo '<tr><td align="left">Tipo Fecha</td><td><select name="tipofecha" id="tipofecha"><option value="a">Factura</option><option value="b">Venta</option></select></td></tr>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.substr(fechaLocal(),0,8).'01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Folio</td><td><input type="text" name="folio" id="folio"  size="15" class="textField" value=""></td></tr>';
		echo '<tr style="display:none;"><td align="left">Cliente</td><td><select name="cliente" id="cliente"><option value="all">--- Todos ---</option>';
		foreach($array_clientes as $k=>$v){
			echo '<option class="cexternos" value="'.$k.'" style="color: '.$array_colorcliente[$k].';">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr style="display:none"><td align="left">RFC Emisor</td><td><input type="text" name="rfc_factura" id="rfc_factura"  size="20" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">RFC Receptor</td><td><input type="text" name="rfc_repector" id="rfc_repector"  size="20" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Cliente</td><td><input type="text" name="nomcliente" id="nomcliente"  size="30" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Estatus</td><td><select name="estatus" id="estatus"><option value="0">Todos</option><option value="1">Activos</option>
		<option value="2">Cancelado</option>';
		echo '<option value="4">Sin Timbrar</option><option value="5">Timbradas</option>';
		echo '</select></td></tr>';
		echo '<tr><td align="left">Mostrar</td><td><select name="mostrar" id="mostrar"><option value="0">Todos</option><option value="1">Mes Anterior</option><option value="2">Mismo Mes</option></td></tr>';
		echo '<tr><td>Tipo Factura</td><td><select name="tipo_pag" id="tipo_pag"><option value="all" selected>Todos</option>';
		foreach($array_tipo_pag as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table></td><td><div id="Resultadoss"></div></td></tr></table>';
		echo '<br>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		//Listado
		$res = mysql_query("SELECT mensaje FROM etiquetas_plazas WHERE estatus='A' AND plazas LIKE '%|".$_POST['plazausuario']."|%' ORDER BY cve DESC LIMIT 1");
		$row = mysql_fetch_array($res);
		if($row['mensaje']!=''){
			echo '<p style="color:#FF0000;font-size:16px;">'.$row['mensaje'].'</p>';
		}
		echo '<div id="Resultados">';
		echo '</div>';
	}
bottom();
echo '
<Script language="javascript">
	
	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","facturas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&rfc_factura="+document.getElementById("rfc_factura").value+"&rfc_repector="+document.getElementById("rfc_repector").value+"&tipo_pag="+document.getElementById("tipo_pag").value+"&nomcliente="+document.getElementById("nomcliente").value+"&tipofecha="+document.getElementById("tipofecha").value+"&mostrar="+document.getElementById("mostrar").value+"&folio="+document.getElementById("folio").value+"&estatus="+document.getElementById("estatus").value+"&cliente="+document.getElementById("cliente").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4){
					opc=objeto.responseText.split("|*|");
				document.getElementById("Resultados").innerHTML =opc[0];
				document.getElementById("Resultadoss").innerHTML =opc[1];				
					}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}

	function guardarFecha(folio){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","facturas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=16&folio="+folio+"&fecha="+document.getElementById("fechan_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros();}
			}
		}
	}

	function guardarTipoPag(folio){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","facturas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=17&folio="+folio+"&tipo_pag="+document.getElementById("tipo_pag_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros();}
			}
		}
	}

	$("#dialog").dialog({ 
		bgiframe: true,
		autoOpen: false,
		modal: true,
		width: 450,
		height: 300,
		autoResize: true,
		position: "center",
		beforeClose: function( event, ui ) {
			document.forma.obsnota.value="";
			document.forma.conceptonota.value="";
			$("#facturanota").val("");
			$("#obsnotadialog").val("");
			$("#conceptonotadialog").val("");
		},
		buttons: {
			"Aceptar": function(){ 
				if($("#conceptonotadialog").val()==""){
					alert("Necesita ingesar el concepto");
				}
				else if($("#obsnotadialog").val()==""){
					alert("Necesita ingesar la observacion");
				}
				else{
					document.forma.obsnota.value=$("#obsnotadialog").val();
					document.forma.conceptonota.value=$("#conceptonotadialog").val();
					atcr("facturas.php","","30",$("#facturanota").val());
				}
			},
			"Cerrar": function(){ 
				document.forma.obsnota.value="";
				document.forma.conceptonota.value="";
				$("#facturanota").val("");
				$("#obsnotadialog").val("");
				$("#conceptonotadialog").val("");
				$(this).dialog("close"); 
			}
		},
	}); 

	function mostrar_obs_nota(factura){
		$("#facturanota").val(factura);
		$("#dialog").dialog("open"); 
	}

	$("#dialog2").dialog({ 
		bgiframe: true,
		autoOpen: false,
		modal: true,
		width: 450,
		height: 200,
		autoResize: true,
		position: "center",
		beforeClose: function( event, ui ) {
			document.forma.correos_envio.value="";
			$("#correos_envio").val("");
		},
		buttons: {
			"Aceptar": function(){ 
				document.forma.correos_envio.value=$("#correos_envio").val();
				atcr(\'facturas.php\',\'\',\'6\',\'0\');
			},
			"Cerrar": function(){ 
				document.forma.correos_envio.value="";
				$("#correos_envio").val("");
				$(this).dialog("close"); 
			}
		},
	}); 
	
	function mostrar_correo(factura){
		$("#dialog2").dialog("open"); 
	}

	$("#dialog3").dialog({ 
		bgiframe: true,
		autoOpen: false,
		modal: true,
		width: 600,
		height: 300,
		autoResize: true,
		position: "center",
		beforeClose: function( event, ui ) {
			document.forma.cvecancelacion.value="";
			document.forma.obscancelacion.value="";
			document.forma.motivocancelacion.value="";
			document.forma.uuidsustituye.value="";
			$("#motivocancelacion").val("");
			$("#uuidsustituye").val("");
			$("#mostrar_motivo_sat").val("");
			$("#obscancelacion").val("");
		},
		buttons: {
			"Aceptar": function(){ 
				if ($("#mostrar_motivo_sat").val() == 1 && $("#motivocancelacion").val() == ""){
					alert("Necesita seleccionar un motivo de cancelacion");
				}
				else if ($("#mostrar_motivo_sat").val() == 1 && $("#motivocancelacion").val() == ""){
					alert("Necesita seleccionar un motivo de cancelacion");
				}
				else{
					document.forma.obscancelacion.value=$("#obscancelacion").val();
					document.forma.motivocancelacion.value=$("#motivocancelacion").val();
					document.forma.uuidsustituye.value=$("#uuidsustituye").val();
					atcr(\'facturas.php\',\'\',\'3\',document.forma.cvecancelacion.value);
				}
			},
			"Cerrar": function(){ 
				document.forma.cvecancelacion.value="";
				document.forma.obscancelacion.value="";
				document.forma.motivocancelacion.value="";
				document.forma.uuidsustituye.value="";
				$("#motivocancelacion").val("");
				$("#uuidsustituye").val("");
				$("#mostrar_motivo_sat").val("");
				$("#obscancelacion").val("");
				$(this).dialog("close"); 
			}
		},
	}); 

	function cancelarfactura(factura, mostrar_motivo_sat){
		document.forma.cvecancelacion.value=factura;
		$("#mostrar_motivo_sat").val(mostrar_motivo_sat);
		$("#uuidsustituye").val("");
		$("#uuidsustituye").parents("tr:first").hide();
		if(mostrar_motivo_sat==1){
			$("#motivocancelacion").parents("tr:first").show();
		}
		else{
			$("#motivocancelacion").parents("tr:first").hide();
		}
		$("#dialog3").dialog("open"); 
	}
		
	';	
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(0,1); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
	function validanumero(campo) {
		var ValidChars = "0123456789.";
		var cadena=campo.value;
		var cadenares="";
		var digito;
		for(i=0;i<cadena.length;i++) {
			digito=cadena.charAt(i);
			if (ValidChars.indexOf(digito) != -1)
				cadenares+=""+digito;
		}
		campo.value=cadenares;
	}

	</Script>
';

?>