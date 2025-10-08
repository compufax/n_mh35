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
if($_POST['cmd']==2){
	if(is_uploaded_file ($_FILES['archivo']['tmp_name'])){
		$arch = $_FILES['archivo']['tmp_name'];
		$cadena= file_get_contents($arch);
		$dom = new DOMDocument;
		$dom->loadXML($cadena);
		$arreglo = _processToArray($dom);
		
		$datos = array();
		$datos['cve'] = $arreglo['cfdi:Comprobante'][0]['@folio'];
		$datos['tipo_pago'] = $arreglo['cfdi:Comprobante'][0]['@metodoDePago'];
		$datos['total'] = $arreglo['cfdi:Comprobante'][0]['@total'];
		$datos['subtotal'] = $arreglo['cfdi:Comprobante'][0]['@subTotal'];
		$datos['iva'] = $datos['total']-$datos['subtotal'];
		$datos['sellodocumento'] = $arreglo['cfdi:Comprobante'][0]['@sello'];
		$datos['seriecertificado'] = $arreglo['cfdi:Comprobante'][0]['@noCertificado'];
		$datos['uuid'] = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@UUID'];
		$datos['seriecertificadosat'] = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@noCertificadoSAT'];
		$datos['sellotimbre'] = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@selloSAT'];
		$datos['fechatimbre'] = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@FechaTimbrado'];
		$version = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@version'];
		$datos['cadenaoriginal'] = '||'.$version.'|'.$datos['uuid'].'|'.$datos['fechatimbre'].'|'.$datos['sellodocumento'].'|'.$datos['seriecertificadosat'].'||';
		$fechas = explode("T",$arreglo['cfdi:Comprobante'][0]['@fecha']);
		$datos['fecha']=$fechas[0];
		$datos['hora']=$fechas[1];
		$datos['nombre']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['@nombre']);
		$datos['rfc']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['@rfc'];
		$datos['calle']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@calle']);
		$datos['numexterior']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@noExterior'];
		$datos['numinterior']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@noInterior'];
		$datos['colonia']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@colonia']);
		$datos['municipio']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@municipio']);
		$datos['estado']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@estado']);
		$datos['codigopostal']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@codigoPostal'];
		$conceptos = array();
		foreach($arreglo['cfdi:Comprobante'][0]['cfdi:Conceptos'] as $valores){
			$conceptos[]=array(
				'cantidad' => $valores['cfdi:Concepto'][0]['@cantidad'],
				'concepto' => $valores['cfdi:Concepto'][0]['@descripcion'],
				'unidad' => $valores['cfdi:Concepto'][0]['@unidad'],
				'precio' => $valores['cfdi:Concepto'][0]['@valorUnitario'],
				'importe' => $valores['cfdi:Concepto'][0]['@importe']
			);
		}
		
		echo '<pre>';
		//var_dump($arreglo);
		print_r($datos);
		print_r($conceptos);
		echo '</pre>';
		/*$campos="";
		foreach($datos as $campo=>$valor){
			$campos .= ",".$campo."='".$valor."'";
		}
		mysql_query("INSERT facturas_xml SET plaza='".$_POST['plazausuario']."',fecha_creacion='".fechaLocal()."',
		hora_creacion='".horaLocal()."',obs='".$_POST['obs']."',usuario='".$_POST['cveusuario']."'".$campos);
		foreach($conceptos as $concepto){
			$campos = "";
			foreach($concepto as $campo=>$valor){
				$campos.= ",".$campo."='".$valor."'";
			}
			mysql_query("INSERT facturasmov_xml SET plaza='".$_POST['plazausuario']."',cvefact='".$datos['cve']."'".$campos);
		}
		copy($arch,"xmls/cfdi_".$_POST['plazausuario']."_".$datos['cve'].".xml");
		chmod("xmls/cfdi_".$_POST['plazausuario']."_".$datos['cve'].".xml", 0777);*/
	}
}




echo '<table><tr><th>Archivo</th><td><input type="file" name="archivo" id="archivo" class="textField">&nbsp;&nbsp;<a href="#" onClick="atcr(\'subir_xmls.php\',\'\',2,0);"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td></tr></table>';

bottom();