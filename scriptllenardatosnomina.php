<?php
require_once('subs/cnx_db.php');


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

$res = mysql_query("SELECT * FROM personal_nomina WHERE uuid!='' AND plazatimbro>0");
while($row=mysql_fetch_array($res)){
	$arch = "cfdi/comprobantes/cfdin_1_".$row['cve'].".xml";
	$cadena= file_get_contents($arch);
	if($cadena!=''){
		$dom = new DOMDocument;
		$dom->loadXML($cadena);
		$arreglo = _processToArray($dom);

		$rfc_emisor = $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@rfc'];
		$datos['nombre_empresa']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@nombre']);
		$datos['rfc_empresa']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@rfc'];
		$datos['calle']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@calle']);
		$datos['numexterior']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@noExterior'];
		$datos['numinterior']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@noInterior'];
		$datos['colonia']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@colonia']);
		$datos['municipio']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@municipio']);
		$datos['estado']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@estado']);
		$datos['codigopostal']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['cfdi:DomicilioFiscal'][0]['@codigoPostal'];

		$campos = '';
		foreach($datos as $campo=>$valor){
			$campos .= ",".$campo."='".$valor."'";
		}

		mysql_query("UPDATE personal_nomina SET ".substr($campos,1)." WHERE cve='".$row['cve']."'") or die(mysql_error());
								
	}
	else{
		echo $row['cve'].'<br>';
	}

}

?>