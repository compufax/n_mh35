<?php
include("subs/cnx_db.php");

function _xmlToArray($node)
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

$base='maron';
mysql_select_db($base);
$res = mysql_query("SELECT * FROM facturas WHERE respuesta1!=''");
while($row = mysql_fetch_array($res)){
	$arch = 'cfdi/comprobantes/cfdi_'.$row['plaza'].'_'.$row['cve'].'.xml';
	$cadena= file_get_contents($arch);
	$dom = new DOMDocument;
	$dom->loadXML($cadena);
	$arreglo = _xmlToArray($dom);
	$rfc = $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@rfc'];
	mysql_query("UPDATE facturas SET rfc_factura='$rfc' WHERE plaza='".$row['plaza']."' AND cve='".$row['cve']."'");
}



?>