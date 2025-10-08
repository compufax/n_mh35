<?php 
include ("main.php"); 
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
						$result[$child->nodeName][] = _xmlToArray2($child);
					}
					else {
						$result[$child->nodeName] = _xmlToArray2($child);
					}
				}
				else if ($child->nodeName == '#text') {
					$text = _xmlToArray2($child);

					if (trim($text) != '') {
						$result[$child->nodeName] = _xmlToArray2($child);
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

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		$array_tipo_pag=array('Contado', 'Credito');
		$array_usuario[-1] = 'WEB';
		$rsUsuario=mysql_query("SELECT * FROM usuarios");
		while($Usuario=mysql_fetch_array($rsUsuario)){
			$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
		}
		
		$dom = new DOMDocument;
		//Listado de plazas
		$select= " SELECT a.rfc as rfccliente, a.nombre as nom_cliente, b.*, c.bloqueada_sat
		FROM clientes a INNER JOIN facturas b on a.cve = b.cliente and b.estatus!='C' and b.fecha>='2016-09-01' AND b.respuesta1!='' 
		LEFT JOIN plazas c ON c.cve = b.plaza";
		if($_POST['uuid']=='') $select .= " WHERE a.rfc='".$_POST['rfc']."'";
		else $select .= " WHERE b.uuid = '".$_POST['uuid']."'";
		
		$select.=" ORDER BY b.cve DESC";

		$res=mysql_query($select) or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
			echo '<th>Serie</th><th>Folio</th><th>Fecha</th><th>RFC Emisor</th><th>Tipo Factura</th>
			<th>Cliente</th><th>RFC Cliente</th><th>Tipo Pago</th><th>Subtotal</th>
			<th>Iva</th><th>Total</th><th>Ticket</th><th>Fecha Ticket</th><th>Placa Ticket</th><th>Usuario Ticket</th>
			<th>Usuario</th></tr>'; 
			$t=$t2=0;
			while($Abono=mysql_fetch_array($res)) {
				rowb();
				$estatus='';
				if($Abono['estatus']=='C'){
					$estatus='(CANCELADO)';
					$Abono['subtotal']=0;
					$Abono['iva']=0;
					$Abono['total']=0;
					$Abono['iva_retenido']=0;
					echo '<td align="center">CANCELADO<br>';
					if(file_exists('../cfdi/comprobantes/facturac_'.$Abono['plaza'].'_'.$Abono['cve'].'.pdf')){
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'../cfdi/comprobantes/facturac_'.$Abono['plaza'].'_'.$Abono['cve'].'.pdf\',\'_blank\',\'0\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
						if($_POST['cveusuario']==1){
							echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'103\',\'../cfdi/comprobantes/facturac_'.$Abono['plaza'].'_'.$Abono['cve'].'.pdf\');"><img src="images/basura.gif" border="0" title="Borrar PDF '.$Abono['folio'].'"></a>';
						}
					}
					else{
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'101\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
					}
					echo '</td>';
				}
				elseif($Abono['respuesta1']==""){
					echo '<td align="center" width="40" nowrap>';
					if($Abono['bloqueada_sat'] != 1 && $_POST['cveusuario']==1)
						echo '<input type="button" style=" font-size:25px;" value="TIMBRAR" onClick="if(confirm(\'Esta seguro que desea timbrar?\')){$(\'#panel\').show();atcr(\'facturas.php\',\'_blank\',\'5\',\''.$Abono['cve'].'\');}"><br>';
					//echo '&nbsp;&nbsp;<span style="cursor:pointer;" href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')){$(\'#panel\').show();cancelarsintimbrar(\''.$Abono['cve'].'\');}"><img src="images/validono.gif" border="0" title="Cancelar '.$Abono['folio'].'"></span>';
					echo '</td>';
				}
				else{
					echo '<td align="center" width="40" nowrap>';
					if($Abono['estatus'] == 'D') echo 'DEVUELTO<br>';
					if(file_exists('../cfdi/comprobantes/factura_'.$Abono['plaza'].'_'.$Abono['cve'].'.pdf')){
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'../cfdi/comprobantes/factura_'.$Abono['plaza'].'_'.$Abono['cve'].'.pdf\',\'_blank\',\'0\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
						if($_POST['cveusuario']==1){
							echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'103\',\'../cfdi/comprobantes/factura_'.$Abono['plaza'].'_'.$Abono['cve'].'.pdf\');"><img src="images/basura.gif" border="0" title="Borrar PDF '.$Abono['folio'].'"></a>';
						}
					}
					else{
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'101\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
					}
					if($nivelUsuario>2 && $Abono['estatus'] != 'D' && $bloqueada_sat!=1){
						//if($_POST['cveusuario'] == 1)
							echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')){$(\'#panel\').show();atcr(\'facturas.php\',\'\',\'3\',\''.$Abono['cve'].'\');}"><img src="images/validono.gif" border="0" title="Cancelar '.$Abono['folio'].'"></a>';
						/*echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de generar la nota de credito de la factura?\')){mostrar_obs_nota(\''.$Abono['cve'].'\');}"><img src="images/cerrar.gif" border="0" title="Nota Credito '.$Abono['folio'].'"></a>';*/
					}
					//if($_POST['cveusuario']==1)
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'../cfdi/comprobantes/cfdi_'.$Abono['plaza'].'_'.$Abono['cve'].'.xml\',\'_blank\',\'0\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir XML '.$Abono['folio'].'"></a>';
					echo '</td>';
					if($nivelUsuario>0){
						echo '<td><input type="checkbox" class="checks" name="checksf[]" value="'.$Abono['cve'].'"></td>';
					}
				}
				echo '<td align="center">'.$Abono['serie'].'</td>';
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
				if($folioxml != $Abono['folio'] && $_POST['cveusuario']==1)
				{
					$Abono['folio'] .= '<font color="RED">'.$folioxml.'</font>';
				}
				echo '<td align="center">'.$Abono['folio'].'</td>';
				echo '<td align="center">'.htmlentities($Abono['fecha'].' '.$Abono['hora']).'</td>';
				echo '<td align="center">'.$Abono['rfc_factura'].'</td>';
				echo '<td>'.htmlentities($array_tipo_pag[$Abono['tipo_pag']]).'</td>';
				echo '<td>'.htmlentities(utf8_encode($Abono['nom_cliente'])).'</td>';
				echo '<td align="center">'.$Abono['rfccliente'].'</td>';
				echo '<td>'.htmlentities($array_tipo_pago[$Abono['tipo_pago']]).'</td>';
				echo '<td align="right">'.number_format($Abono['subtotal'],2).'</td>';
				echo '<td align="right">'.number_format($Abono['iva'],2).'</td>';
				echo '<td align="right">'.number_format($Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'],2).'</td>';
				$array_tickets=array();
				$res2=mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$Abono['plaza']."' AND factura='".$Abono['cve']."' AND estatus!='C'");
				while($row2 = mysql_fetch_array($res2)) $array_tickets[$row2['cve']] = array('placa'=>$row2['placa'],'fecha'=>$row2['fecha'],'usuario'=>$row2['usuario']);
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
						$res3=mysql_query("SELECT a.cve FROM facturas a INNER JOIN cobro_engomado b ON a.plaza=b.plaza AND a.cve=b.factura WHERE a.plaza='".$Abono['plaza']."' AND a.cve!='".$Abono['cve']."' AND b.placa = '".$datosticket['placa']."' AND ABS(DATEDIFF('".$Abono['fecha']."',fecha))<=60");
						if(mysql_num_rows($res3)>0){
							echo '<font color="RED">'.$datosticket['placa'].'</font><br>';
						}
						else{
							echo ''.$datosticket['placa'].'<br>';
						}
					}
					echo '</td><td align="center">';
					foreach($array_tickets as $ticket=>$datosticket){
						echo $array_usuario[$datosticket['usuario']].'<br>';
					}
					echo '</td>';
				}
				else{
					echo '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
				}
				echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
				echo '</tr>';
				$x++;
				$sumacargo[0]+=$Abono['subtotal'];
				$sumacargo[1]+=$Abono['iva'];
				$sumacargo[2]+=$Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'];
			}
			
			$c=8;
			echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.$x.' Registro(s)</td>';
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
			foreach($sumacargo as $k=>$v){
				echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
			}
			echo '<td bgcolor="#E9F2F8" colspan="5">&nbsp;</td>';
			echo '</tr>';
			echo '</table>';
			
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


top($_SESSION);

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	echo '
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>RFC</td><td><input type="text" name="rfc" id="rfc" size="20" class="textField" value=""></td></tr>';
	echo '<tr';
	if($_POST['cveusuario']!=1) echo ' style="display:none;"';
	echo '><td>Folio Fiscal</td><td><input type="text" name="uuid" id="uuid" size="50" class="textField" value=""></td></tr>';
	echo '</table>';
	echo '<br>';
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
			objeto.open("POST","consulta_rfc.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&uuid="+document.getElementById("uuid").value+"&rfc="+document.getElementById("rfc").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

