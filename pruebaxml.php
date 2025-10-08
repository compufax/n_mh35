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

$res = mysql_query("SELECT * FROM facturas WHERE plaza in (1,6,9,8) AND folioxml > 0");



while($row = mysql_fetch_array($res)){
	$dom = new DOMDocument;
	$arch = 'cfdi/comprobantes/cfdi_'.$row['plaza'].'_'.$row['cve'].'.xml';
	$cadena= file_get_contents($arch);
	$dom->loadXML($cadena);
	$arreglo = _xmlToArray($dom);
	if(isset($arreglo['cfdi:Comprobante'][0]['@Folio']))
		$folioxml = 'Folio';
	else
		$folioxml = 'folio';

	$children = $dom->childNodes;

	$child = $children->item(0);

	$child->setAttribute( $folioxml, $row['folio'] );

	$dom->save($arch);

	unset($dom);

}

$dom = new DOMDocument;
/*$arch = 'prueba.xml';
$cadena= file_get_contents($arch);
$dom->loadXML($cadena);
if($dom->hasChildNodes()){
	foreach($dom->childNodes as $child) {
		echo  $child->nodeName.'<br>';
	}
}

$children = $dom->childNodes;

$child = $children->item(0);

if($child->hasAttributes()) {
	$child->setAttribute( "Folio", "4" );
	$attributes = $child->attributes;

	if(!is_null($attributes)) {
		foreach ($attributes as $key => $attr) {
			echo $attr->name.' = '.$attr->value.'<br>';
		}
	}
}

$dom->save("prueba.xml");*/

?>