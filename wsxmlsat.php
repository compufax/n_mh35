<?php
date_default_timezone_set ("America/Mexico_City");
set_time_limit(0);
require_once("nusoap/nusoap.php");
$base='vereficentros';
$namespace = "http://verificentros.net/cfdiservices";
// create a new soap server
$server = new soap_server();
// configure our WSDL
$server->configureWSDL("wsxml");
// set our namespace
$server->wsdl->schemaTargetNamespace = $namespace;
// register our WebMethod
$server->register(
                // nombre del metodo
                'registrarCFDI',
                // lista de parametros
                array('rfc'=>'xsd:string','documentoXML'=>'xsd:string'), 
                // valores de return
                array('return'=>'xsd:string'),
                // namespace
                $namespace,
                // soapaction: (use default)
                false,
                // style: rpc or document
                'rpc',
                // use: encoded or literal
                'encoded',
                // descripcion: documentacion del metodo
                'Registra un CFDI en el buzon');

function registrarCFDI($rfc,$documentoXML)
{
	global $base;
	$respuesta='';
	//Verificar la coneccion a la BD
    $resultcn=ConectarDB();
    if($resultcn!="OK"){
        $respuesta=$resultcn;
    }
	//Verificar los datos del Emisor
	if($respuesta=='')
	{
		$resempresa = mysql_query("SELECT * FROM sat_empresas WHERE rfc='$rfc'");
		if(!$rowempresa = mysql_fetch_array($resempresa))
			$respuesta='Contribuyente no registrado';
	}
	//Cargar el comprobante
	if($respuesta=='')
	{
		$dom = new DOMDocument;
		if (!$dom->loadXML(base64_decode($documentoXML)))
			$respuesta='la informacion recibida no es un XML valido';
		else
		{
			$arreglo = _processToArray($dom);
			$datos = array();
			$datos['folio'] = $arreglo['cfdi:Comprobante'][0]['@folio'];
			$datos['serie'] = $arreglo['cfdi:Comprobante'][0]['@serie'];
			$datos['uuid'] = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@UUID'];
			$rfc_receptor = $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['@rfc'];
			$rfc_emisor = $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@rfc'];
			if($rowempresa['rfc']==$rfc_emisor || $rowempresa['rfc']==$rfc_receptor)
			{
				if($rowempresa['rfc']==$rfc_emisor)
					$datos['tipo']=0;
				else
					$datos['tipo']=1;
				$res = mysql_query("SELECT * FROM sat_xml WHERE plaza='".$rowempresa['cve']."' AND uuid='".$datos['uuid']."'");
				if(mysql_num_rows($res)==0)
				{
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
					$datos['nombre_r']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['@nombre']);
					$datos['rfc_r']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['@rfc'];
					$datos['calle_r']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@calle']);
					$datos['numexterior_r']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@noExterior'];
					$datos['numinterior_r']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@noInterior'];
					$datos['colonia_r']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@colonia']);
					$datos['municipio_r']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@municipio']);
					$datos['estado_r']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@estado']);
					$datos['codigopostal_r']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['cfdi:Domicilio'][0]['@codigoPostal'];
					
					$conceptos = array();
					
					foreach($arreglo['cfdi:Comprobante'][0]['cfdi:Conceptos'][0]['cfdi:Concepto'] as $valores){
						$conceptos[]=array(
							'cantidad' => $valores['@cantidad'],
							'concepto' => $valores['@descripcion'],
							'unidad' => $valores['@unidad'],
							'precio' => $valores['@valorUnitario'],
							'importe' => $valores['@importe']
						);
					}

					$campos="";
					foreach($datos as $campo=>$valor){
						$campos .= ",".$campo."='".$valor."'";
					}
					mysql_query("INSERT sat_xml SET plaza='".$rowempresa['cve']."',fecha_creacion='".fechaLocal()."',cheques='',
					hora_creacion='".horaLocal()."',obs='',usuario=''".$campos);
					$cvefact=mysql_insert_id();
					foreach($conceptos as $concepto){
						$campos = "";
						foreach($concepto as $campo=>$valor)
						{
							$campos.= ",".$campo."='".$valor."'";
						}
						mysql_query("INSERT satmov_xml SET plaza='".$rowempresa['cve']."',cvefact='".$cvefact."'".$campos);
					}
					$dom->save("xmls/cfdis_".$rowempresa['cve']."_".$cvefact.".xml");
					chmod("xmls/cfdis_".$rowempresa['cve']."_".$cvefact.".xml", 0777);
					//$arch2=substr($arch,0,(count($arch)-4)).'pdf';
					//$nombres_nuevos[$arch2] = "xmls/cfdis_".$rowempresa['cve']."_".$cvefact;
					$respuesta='OK';
				}
				else
					$respuesta='Comprobante ya registrado';
			}
			else
				$respuesta='El comprobante no pertenece al contribuyente';
		}	
	}
    return $respuesta;
}
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
function horaLocal() {
		
		$differencetolocaltime=1;

		$new_U=date("U")+$differencetolocaltime*3600;

		//$fulllocaldatetime= date("d-m-Y h:i:s A", $new_U);

		$hora= date("H:i:s", $new_U);
		
		$hora=date( "Y-m-d H:i:s" , strtotime ( "0 hour" , strtotime(date("Y-m-d H:i:s")) ) );
		
		$hora=date( "H:i:s" , strtotime ( "0 minute" , strtotime($hora) ) );

		return $hora;

		//Regards. Mohammed Ahmad. MSN: m@maaking.com

	}
function fechaLocal(){
		$differencetolocaltime=1;

		$new_U=date("U")+$differencetolocaltime*3600;

		//$fulllocaldatetime= date("d-m-Y h:i:s A", $new_U);

		//$fecha= date("Y-m-d", $new_U);
		
		$fecha=date( "Y-m-d H:i:s" , strtotime ( "0 hour" , strtotime(date("Y-m-d H:i:s")) ) );
		
		$fecha=date( "Y-m-d" , strtotime ( "0 minute" , strtotime($fecha) ) );

		return $fecha;
	}
function ConectarDB(){
	$msg="OK";
	eval(file_get_contents('/var/www/config.cfg'));
	//Conexion con la base
	if (!$MySQL=@mysql_connect($DB_HOST, 'tepe_tepe2', 'Ballena6')) {
		$t=time();
		while (time()<$t+5) {}
		if (!$MySQL=@mysql_connect($DB_HOST, 'tepe_tepe2', 'Ballena6')) {
			$t=time();
			while (time()<$t+10) {}
			if (!$MySQL=@mysql_connect($DB_HOST, 'tepe_tepe2', 'Ballena6')) {
			echo '<br><br><br><h3 align=center">Hay problemas de comunicaci&oacute;n con la Base de datos.</h3>';
			echo '<h4>Por favor intente mas tarde.-</h4>';
			exit;
			}
		}
	}
	mysql_select_db("gveri");
	mysql_query("SET time_zone = CST6CDT;");
	return $msg;
}
// Get our posted data if the service is being consumed
// otherwise leave this data blank.                
$POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) 
? $GLOBALS['HTTP_RAW_POST_DATA'] : '';

// pass our posted data (or nothing) to the soap service                    
$server->service($POST_DATA);
?>