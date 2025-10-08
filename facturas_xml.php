<?php
include("main.php");
include("imp_factura_xml.php");
//ARREGLOS

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsUsuario=mysql_query("SELECT * FROM datosempresas");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazasrfc[$Usuario['plaza']]=$Usuario['rfc'];
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
	generaFacturaPdf($_POST['plazausuario'],$_POST['reg'],1);
	exit();
}

if($_POST['cmd']==103){
	unlink($_POST['reg']);
	echo '<script>window.close();</script>';
	exit();
}

if($_POST['ajax']==1){
	$filtro="";
	$select= " SELECT a.* FROM facturas_xml as a WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
	//if($_POST['tipo']!="all") $select.=" AND a.tipo='".$_POST['tipo']."'";
	if($_POST['cliente']!="all") $select.=" AND (a.rfc='".$_POST['cliente']."' OR a.nombre LIKE '%".$_POST['cliente']."%')";
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	if($_POST['estatus']==1) $select.=" AND a.estatus!='C'";
	elseif($_POST['estatus']==2) $select.=" AND a.estatus='C'";
	$select.=" ORDER BY a.fecha DESC,hora DESC";
	$rsabonos=mysql_query($select) or die(mysql_error());
	if(mysql_num_rows($rsabonos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$c=17;
		if($_POST['cveusuario']==1) $c++;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.mysql_num_rows($rsabonos).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		if($_POST['cveusuario']==1){
			echo '<th><input type="checkbox" name="selt" value="1" onClick="if(this.checked) $(\'.checks\').attr(\'checked\',\'checked\'); else $(\'.checks\').removeAttr(\'checked\');"></th>';
		}
		echo '<th>Folio</th><th>Fecha</th><th>RFC Emisor</th>
		<th>Cliente</th><th>RFC Cliente</th><th>Tipo Pago</th><th>Subtotal</th>
		<th>Iva</th><th>Total</th><th>UUID</th><th>Cancelacion</th>
		<th>Usuario<br>';
		echo '<select name="usuario" onchange="document.forma.usu.value=this.value;buscarRegistros();"><option value="all">---Todos---</option>';
		$res1=mysql_query("SELECT a.usuario FROM facturas_xml as a WHERE plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY a.usuario");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['usuario'].'"';
			if($row1['usuario']==$_POST['usu']) echo ' selected';
			echo '>'.$array_usuario[$row1['usuario']].'</option>';
		}
		echo '</select></th></tr>'; 
		$sumacargo=array();
		$x=0;
		$folio=0;
		while ($Abono=mysql_fetch_array($rsabonos)){	
			if($folio==0) $folio=$Abono['cve'];
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
				if(file_exists('xmls/facturac_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf')){
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'xmls/facturac_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf\',\'_blank\',\'0\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
					if($_POST['cveusuario']==1){
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas_xml.php\',\'_blank\',\'103\',\'xmls/facturac_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf\');"><img src="images/basura.gif" border="0" title="Borrar PDF '.$Abono['folio'].'"></a>';
					}
				}
				else{
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas_xml.php\',\'_blank\',\'101\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				}
				echo '</td>';
				if($_POST['cveusuario']==1){
					echo '<td><input type="checkbox" class="checks" name="checksf[]" value="'.$Abono['cve'].'"></td>';
				}
				
			}
			else{
				echo '<td align="center" width="40" nowrap>';
				if(file_exists('xmls/factura_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf')){
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'xmls/factura_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf\',\'_blank\',\'0\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
					if($_POST['cveusuario']==1){
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas_xml.php\',\'_blank\',\'103\',\'xmls/factura_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf\');"><img src="images/basura.gif" border="0" title="Borrar PDF '.$Abono['folio'].'"></a>';
					}
				}
				else{
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas_xml.php\',\'_blank\',\'101\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				}
				if(nivelUsuario()>2)
					echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')){$(\'#panel\').show();atcr(\'facturas_xml.php\',\'\',\'3\',\''.$Abono['cve'].'\');}"><img src="images/validono.gif" border="0" title="Cancelar '.$Abono['folio'].'"></a>';
				echo '</td>';
				if($_POST['cveusuario']==1){
					echo '<td><input type="checkbox" class="checks" name="checksf[]" value="'.$Abono['cve'].'"></td>';
				}
			}
			if($Abono['cve']!=$folio){
				echo '<td align="center"><font color="RED">'.$Abono['folio'].'</font></td>';
				$folio=$Abono['cve'];
			}
			else{
				echo '<td align="center">'.$Abono['folio'].'</td>';
			}
			echo '<td align="center">'.$Abono['fecha'].' '.$Abono['hora'].'</td>';
			echo '<td align="center">'.$array_plazasrfc[$Abono['plaza']].'</td>';
			echo '<td>'.htmlentities(utf8_encode($Abono['nombre'])).'</td>';
			echo '<td align="center">'.$Abono['rfc'].'</td>';
			echo '<td>'.htmlentities($Abono['tipo_pago']).'</td>';
			echo '<td align="right">'.number_format($Abono['subtotal'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'],2).'</td>';
			echo '<td>'.$Abono['uuid'].'</td>';
			$cancelacion = explode("|",$Abono['respuesta2']);
			echo '<td>'.$cancelacion[1].'</td>';
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$sumacargo[0]+=$Abono['subtotal'];
			$sumacargo[1]+=$Abono['iva'];
			$sumacargo[2]+=$Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'];
			$folio--;
		}
		$c=6;
		if($_POST['cveusuario']==1) $c++;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.$x.' Registro(s)</td>';
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		foreach($sumacargo as $k=>$v){
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
		}
		echo '<td bgcolor="#E9F2F8" colspan="3">&nbsp;</td>';
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




if($_POST['cmd']==6){
	require_once("phpmailer/class.phpmailer.php");
	foreach($_POST['checksf'] as $cvefact){
		$res = mysql_query("SELECT * FROM facturas_xml WHERE plaza='".$_POST['plazausuario']."' AND cve='".$cvefact."'");
		$row = mysql_fetch_array($res);
		$foliofact = $row['folio'];
		$row1['cve']=0;
		$row1['email']=$_POST['email'];
		$emailenvio = $row1['email'];
		
		$mail = new PHPMailer();
		$mail->Host = "localhost";
		$mail->From = "verificentros@verificentros.net";
		$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
		$mail->Subject = "Factura ".$foliofact;
		$mail->Body = "Factura ".$foliofact;
		//$mail->AddAddress(trim($emailenvio));
		$correos = explode(",",trim($emailenvio));
		foreach($correos as $correo)
			$mail->AddAddress(trim($correo));
		if($row['estatus']=='C')
			$mail->AddAttachment("xmls/facturac_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$foliofact.".pdf");
		else
			$mail->AddAttachment("xmls/factura_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$foliofact.".pdf");
		$mail->AddAttachment("xmls/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$foliofact.".xml");
		$mail->Send();
		if($rowempresa['email']!=""){
			$mail = new PHPMailer();
			$mail->Host = "localhost";
			$mail->From = "verificentros@verificentros.net";
			$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
			$mail->Subject = "Factura ".$foliofact;
			$mail->Body = "Factura ".$foliofact;
			$correos = explode(",",trim($rowempresa['email']));
			foreach($correos as $correo)
				$mail->AddAddress(trim($correo));
			if($row['estatus']=='C')
				$mail->AddAttachment("xmls/facturac_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$foliofact.".pdf");
			else
				$mail->AddAttachment("xmls/factura_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$foliofact.".pdf");
			$mail->AddAttachment("xmls/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$foliofact.".xml");
			$mail->Send();
		}
	}
	$_POST['cmd']=0;
}


if($_POST['cmd']==7){
	require_once("phpmailer/class.phpmailer.php");
	foreach($_POST['checksf'] as $cvefact){
		$res = mysql_query("SELECT * FROM facturas_xml WHERE plaza='".$_POST['plazausuario']."' AND cve='".$cvefact."'");
		$row = mysql_fetch_array($res);
		if($row['estatus']!='C'){
			$cvefact=$row['cve'];
			$foliofact=$row['folio'];
			if($row['uuid']!=""){
				require_once("nusoap/nusoap.php");
				$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
				$err = $oSoapClient->getError();
				if($err!="")
					echo "error1:".$err;
				else{
					//print_r($documento);
					$oSoapClient->timeout = 300;
					$oSoapClient->response_timeout = 300;
					$respuesta = $oSoapClient->call("cancelar", array ('id' => $rowempresa['idplaza'],'rfcemisor' =>$rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'uuid' => $row['uuid'], 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
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
						}
						else{
							if($respuesta['resultado']){
								mysql_query("UPDATE facturas_xml SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."',respuesta2='".$respuesta['mensaje']."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$cvefact."'");
								generaFacturaPdf($_POST['plazausuario'],$cvefact);
								if($emailenvio!=""){
									$mail = new PHPMailer();
									$mail->Host = "localhost";
									$mail->From = "verificentros@verificentros.net";
									$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
									$mail->Subject = "Cancelacion Factura ".$foliofact;
									$mail->Body = "Cancelacion Factura ".$foliofact;
									//$mail->AddAddress(trim($emailenvio));
									$correos = explode(",",trim($emailenvio));
									foreach($correos as $correo)
										$mail->AddAddress(trim($correo));
									$mail->AddAttachment("xmls/facturac_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$foliofact.".pdf");
									$mail->AddAttachment("xmls/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$foliofact.".xml");
									$mail->Send();
								}	
								if($rowempresa['email']!=""){
									$mail = new PHPMailer();
									$mail->Host = "localhost";
									$mail->From = "verificentros@verificentros.net";
									$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
									$mail->Subject = "Cancelacion Factura ".$foliofact;
									$mail->Body = "Cancelacion Factura ".$foliofact;
									//$mail->AddAddress(trim($rowempresa['email']));
									$correos = explode(",",trim($rowempresa['email']));
									foreach($correos as $correo)
										$mail->AddAddress(trim($correo));
									$mail->AddAttachment("xmls/facturac_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$foliofact.".pdf");
									$mail->AddAttachment("xmls/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$foliofact.".xml");
									$mail->Send();
								}
							}
							else
								$strmsg=$respuesta['mensaje'];
							//print_r($respuesta);	
							echo $strmsg;
						}
					}
				}
			}
			else{
				mysql_query("UPDATE facturas_xml SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$cvefact."'");
				generaFacturaPdf($_POST['plazausuario'],$cvefact);
			}
		}
	}
	$_POST['cmd']=0;
}


if($_POST['cmd']==3){
	require_once("phpmailer/class.phpmailer.php");
	$res = mysql_query("SELECT * FROM facturas_xml WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	if($row['estatus']!='C'){
		$cvefact=$row['cve'];
		$foliofact=$row['folio'];
		if($row['uuid']!=""){
			require_once("nusoap/nusoap.php");
			$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
			$err = $oSoapClient->getError();
			if($err!="")
				echo "error1:".$err;
			else{
				//print_r($documento);
				$oSoapClient->timeout = 300;
				$oSoapClient->response_timeout = 300;
				$respuesta = $oSoapClient->call("cancelar", array ('id' => $rowempresa['idplaza'],'rfcemisor' =>$rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'uuid' => $row['uuid'], 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
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
					}
					else{
						if($respuesta['resultado']){
							mysql_query("UPDATE facturas_xml SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."',respuesta2='".$respuesta['mensaje']."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
							generaFacturaPdf($_POST['plazausuario'],$cvefact);
							if($emailenvio!=""){
								$mail = new PHPMailer();
								$mail->Host = "localhost";
								$mail->From = "verificentros@verificentros.net";
								$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
								$mail->Subject = "Cancelacion Factura ".$foliofact;
								$mail->Body = "Cancelacion Factura ".$foliofact;
								//$mail->AddAddress(trim($emailenvio));
								$correos = explode(",",trim($emailenvio));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("xmls/facturac_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$foliofact.".pdf");
								$mail->AddAttachment("xmls/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$foliofact.".xml");
								$mail->Send();
							}	
							if($rowempresa['email']!=""){
								$mail = new PHPMailer();
								$mail->Host = "localhost";
								$mail->From = "verificentros@verificentros.net";
								$mail->FromName = "Verificentros Plaza ".$array_plaza[$_POST['plazausuario']];
								$mail->Subject = "Cancelacion Factura ".$foliofact;
								$mail->Body = "Cancelacion Factura ".$foliofact;
								//$mail->AddAddress(trim($rowempresa['email']));
								$correos = explode(",",trim($rowempresa['email']));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("xmls/facturac_".$_POST['plazausuario']."_".$cvefact.".pdf", "Factura ".$foliofact.".pdf");
								$mail->AddAttachment("xmls/cfdi_".$_POST['plazausuario']."_".$cvefact.".xml", "Factura ".$foliofact.".xml");
								$mail->Send();
							}
						}
						else
							$strmsg=$respuesta['mensaje'];
						//print_r($respuesta);	
						echo $strmsg;
					}
				}
			}
		}
		else{
			mysql_query("UPDATE facturas_xml SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
			generaFacturaPdf($_POST['plazausuario'],$cvefact);
		}
	}
	$_POST['cmd']=0;
}


top($_SESSION);
	
	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>';
		if(nivelUsuario()>1){
			echo '<td><a href="#" onClick="atcr(\'facturas_xml.php\',\'\',\'6\',\'0\');"><img src="images/nuevo.gif" border="0">&nbsp;Reenviar Archivos</a></td><td>&nbsp;</td>';
		}
		if(nivelUsuario()>2){
			echo '<td><a href="#" onClick="if(confirm(\'Esta seguro de cancelar las facturas\')){ atcr(\'facturas_xml.php\',\'\',\'7\',\'0\'); }"><img src="images/validono.gif" border="0">&nbsp;Cancelar Facturas</a></td><td>&nbsp;</td>';
		}
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.substr(fechaLocal(),0,8).'01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Cliente</td><td><input type="text" name="cliente" id="cliente"  size="30" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Estatus</td><td><select name="estatus" id="estatus"><option value="0">Todos</option><option value="1">Activos</option>
		<option value="2">Cancelado</option></select></td></tr>';
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
	
	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","facturas_xml.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&estatus="+document.getElementById("estatus").value+"&cliente="+document.getElementById("cliente").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
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