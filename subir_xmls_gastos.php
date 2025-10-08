<?php
include("main.php");

top($_SESSION);
function _processToArray($node)
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
						$result[$child->nodeName][] = _processToArray($child);
					}
					else {
						$result[$child->nodeName] = _processToArray($child);
					}
				}
				else if ($child->nodeName == '#text') {
					$text = _processToArray($child);

					if (trim($text) != '') {
						$result[$child->nodeName] = _processToArray($child);
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

function eliminarDir($carpeta)
{
	foreach(glob($carpeta . "/*") as $archivos_carpeta)
	{
		//echo $archivos_carpeta;
		 
		if (is_dir($archivos_carpeta))
		{
			eliminarDir($archivos_carpeta);
		}
		else
		{
			@unlink($archivos_carpeta);
		}
	}
 
	@rmdir($carpeta);
}

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);

if($_POST['cmd']==2){
	$cheques='';
	if(trim($_POST['cheques'])!=""){
		$acheques = explode(",",trim($_POST['cheques']));
		foreach($acheques as $cheque){
			if(trim($cheque)!=''){
				$cheques.=',|'.trim($cheque).'|';
			}
		}
		$cheques = substr($cheques,1);
	}
	if(is_uploaded_file ($_FILES['archivo']['tmp_name'])){
		if(substr($_FILES['archivo']['name'],-3)=='zip'){
			$zip = new ZipArchive;
			if ($zip->open($_FILES['archivo']['tmp_name']) === TRUE){
				$nombre_carpeta = 'zipg_'.date('Y_m_d_H_i_s');
				if(!mkdir('xmls/'.$nombre_carpeta, 0777, true)){
					echo "no se pudo ".$nombre_carpeta;
				}
				for($i = 0; $i < $zip->numFiles; $i++)
				{
					$filename = $zip->getNameIndex($i);
					$zip->extractTo('xmls/'.$nombre_carpeta.'/',$filename);
				}
				
				$nombres_nuevos=array();
				for($i = 0; $i < $zip->numFiles; $i++)
				{
					$filename = $zip->getNameIndex($i);
					if(substr($filename,-3)=='xml'){
						//$arch = $_FILES['archivo']['tmp_name'];
						$arch = 'xmls/'.$nombre_carpeta.'/'.$filename;
						$cadena= file_get_contents($arch);
						$dom = new DOMDocument;
						$dom->loadXML($cadena);
						$arreglo = _processToArray($dom);
						$datos = array();
						$datos['folio'] = $arreglo['cfdi:Comprobante'][0]['@folio'];
						$datos['serie'] = $arreglo['cfdi:Comprobante'][0]['@serie'];
						$datos['uuid'] = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@UUID'];
						$rfc_emisor = $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['@rfc'];
						
						if($rowempresa['rfc']==$rfc_emisor){
			
							$res = mysql_query("SELECT * FROM gastos_xml WHERE plaza='".$_POST['plazausuario']."' AND uuid='".$datos['uuid']."'");
							if(mysql_num_rows($res)==0){
								$datos['tipo_pago'] = $arreglo['cfdi:Comprobante'][0]['@metodoDePago'];
								$datos['total'] = $arreglo['cfdi:Comprobante'][0]['@total'];
								$datos['subtotal'] = $arreglo['cfdi:Comprobante'][0]['@subTotal'];
								$datos['iva'] = $datos['total']-$datos['subtotal'];
								$datos['sellodocumento'] = $arreglo['cfdi:Comprobante'][0]['@sello'];
								$datos['seriecertificado'] = $arreglo['cfdi:Comprobante'][0]['@noCertificado'];
								$datos['seriecertificadosat'] = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@noCertificadoSAT'];
								$datos['sellotimbre'] = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@selloSAT'];
								$datos['fechatimbre'] = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@FechaTimbrado'];
								$version = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@version'];
								$datos['cadenaoriginal'] = '||'.$version.'|'.$datos['uuid'].'|'.$datos['fechatimbre'].'|'.$datos['sellodocumento'].'|'.$datos['seriecertificadosat'].'||';
								$fechas = explode("T",$arreglo['cfdi:Comprobante'][0]['@fecha']);
								$datos['fecha']=$fechas[0];
								$datos['hora']=$fechas[1];
								$datos['nombre']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@nombre']);
								$datos['rfc']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@rfc'];
								$datos['calle']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@calle']);
								$datos['numexterior']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@noExterior'];
								$datos['numinterior']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@noInterior'];
								$datos['colonia']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@colonia']);
								$datos['municipio']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@municipio']);
								$datos['estado']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@estado']);
								$datos['codigopostal']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@codigoPostal'];
								$conceptos = array();
								/*echo '<pre>';
								print_r($arreglo);
								echo '</pre>';*/
								foreach($arreglo['cfdi:Comprobante'][0]['cfdi:Conceptos'][0]['cfdi:Concepto'] as $valores){
									$conceptos[]=array(
										'cantidad' => $valores['@cantidad'],
										'concepto' => $valores['@descripcion'],
										'unidad' => $valores['@unidad'],
										'precio' => $valores['@valorUnitario'],
										'importe' => $valores['@importe']
									);
								}
		
								/*echo '<pre>';
								//var_dump($arreglo);
								print_r($datos);
								print_r($conceptos);
								echo '</pre>';*/
								$campos="";
								foreach($datos as $campo=>$valor){
									$campos .= ",".$campo."='".$valor."'";
								}
								mysql_query("INSERT gastos_xml SET plaza='".$_POST['plazausuario']."',fecha_creacion='".fechaLocal()."',cheques='$cheques',
								hora_creacion='".horaLocal()."',obs='".$_POST['obs']."',usuario='".$_POST['cveusuario']."'".$campos);
								$cvefact=mysql_insert_id();
								foreach($conceptos as $concepto){
									$campos = "";
									foreach($concepto as $campo=>$valor){
										$campos.= ",".$campo."='".$valor."'";
									}
									mysql_query("INSERT gastosmov_xml SET plaza='".$_POST['plazausuario']."',cvefact='".$cvefact."'".$campos);
								}
								copy($arch,"xmls/cfdig_".$_POST['plazausuario']."_".$cvefact.".xml");
								chmod("xmls/cfdig_".$_POST['plazausuario']."_".$cvefact.".xml", 0777);
								$arch2=substr($arch,0,(count($arch)-4)).'pdf';
								$nombres_nuevos[$arch2] = "xmls/cfdig_".$_POST['plazausuario']."_".$cvefact;
							}
							
						}
					}
				}
				for($i = 0; $i < $zip->numFiles; $i++)
				{
					$filename = $zip->getNameIndex($i);
					if(substr($filename,-3)=='pdf'){
						$arch = 'xmls/'.$nombre_carpeta.'/'.$filename;
						if($nombres_nuevos[$arch]!=""){
							copy($arch,$nombres_nuevos[$arch].".pdf");
							chmod($nombres_nuevos[$arch].".xml", 0777);
						}
					}
				}
				eliminarDir('xmls/'.$nombre_carpeta);
			}
			else{
				echo 'No se pudo abrir';
			}
		}
		else{
			echo '<script>alert("El archivo tiene que ser un zip el cual contenga los xml a subir");</script>';
		}
	}
	else{
		echo 'No se pudo subir';
	}
}




echo '<table>
<tr><th>Archivo</th><td><input type="file" name="archivo" id="archivo" class="textField">&nbsp;&nbsp;<a href="#" onClick="atcr(\'subir_xmls_gastos.php\',\'\',2,0);"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td></tr>
<tr><th>Cheques</th><td><input type="text" name="cheques" id="cheques" value="" size="100">&nbsp;&nbsp;<font color="RED">Separar con comas los folios de cheques</font></td></tr>
</table>';

bottom();