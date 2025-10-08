<?php
include("main.php");

top($_SESSION);


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

function buscar_impuesto($arreglo, $impuesto, $campo='Importe')
{
	$valor = 0;
	foreach($arreglo as $datos)
	{
		if($datos['@Impuesto'] == $impuesto)
		{
			$valor = $datos['@'.$campo];
		}
	}
	return $valor;
}

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);

if($_POST['cmd']==2){
	
	if(is_uploaded_file ($_FILES['archivo']['tmp_name'])){
		if(substr($_FILES['archivo']['name'],-3)=='zip'){
			$zip = new ZipArchive;
			if ($zip->open($_FILES['archivo']['tmp_name']) === TRUE){
				$nombre_carpeta = 'zips_'.date('Y_m_d_H_i_s');
				if(!mkdir('../xmls/'.$nombre_carpeta, 0777, true)){
					echo "no se pudo ".$nombre_carpeta;
				}
				for($i = 0; $i < $zip->numFiles; $i++)
				{
					$filename = $zip->getNameIndex($i);
					$zip->extractTo('../xmls/'.$nombre_carpeta.'/',$filename);
				}
				
				$nombres_nuevos=array();
				for($i = 0; $i < $zip->numFiles; $i++)
				{
					$filename = $zip->getNameIndex($i);
					if(substr($filename,-3)=='xml'){
						//$arch = $_FILES['archivo']['tmp_name'];
						$arch = '../xmls/'.$nombre_carpeta.'/'.$filename;
						$cadena= file_get_contents($arch);
						$dom = new DOMDocument;
						$dom->loadXML($cadena);
						$arreglo = _processToArray($dom);
						if(isset($arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['nomina:Nomina'])){
							$tipo_xml = 2;
						}
						else{
							$tipo_xml = 1;
						}
						$datos = array();
						$datos['folio'] = $arreglo['cfdi:Comprobante'][0]['@Folio'];
						$datos['serie'] = $arreglo['cfdi:Comprobante'][0]['@Serie'];
						$datos['formapago'] = $arreglo['cfdi:Comprobante'][0]['@FormaPago'];
						$datos['metodopago'] = $arreglo['cfdi:Comprobante'][0]['@MetodoPago'];
						$datos['uuid'] = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@UUID'];
						$rfc_receptor = $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['@Rfc'];
						$rfc_emisor = $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@Rfc'];
						if($rowempresa['rfc']==$rfc_emisor || $rowempresa['rfc']==$rfc_receptor){
							if($rowempresa['rfc']==$rfc_emisor) $datos['tipo']=0;
							else $datos['tipo']=1;
							$res = mysql_query("SELECT * FROM sat_xml WHERE plaza='".$_POST['plazausuario']."' AND uuid='".$datos['uuid']."'");
							if(mysql_num_rows($res)==0){
								$datos['total'] = $arreglo['cfdi:Comprobante'][0]['@Total'];
								$datos['subtotal'] = $arreglo['cfdi:Comprobante'][0]['@SubTotal'];
								$datos['iva'] = buscar_impuesto($arreglo['cfdi:Comprobante'][0]['cfdi:Impuestos'][0]['cfdi:Traslados'][0]['cfdi:Traslado'],'002');
								$datos['iva_retenido'] = buscar_impuesto($arreglo['cfdi:Comprobante'][0]['cfdi:Impuestos'][0]['cfdi:Retenciones'][0]['cfdi:Retencion'],'002');
								$datos['isr_retenido'] = buscar_impuesto($arreglo['cfdi:Comprobante'][0]['cfdi:Impuestos'][0]['cfdi:Retenciones'][0]['cfdi:Retencion'],'001');
								$datos['por_iva_retenido'] = buscar_impuesto($arreglo['cfdi:Comprobante'][0]['cfdi:Impuestos'][0]['cfdi:Retenciones'][0]['cfdi:Retencion'],'002','TasaOCuota')*100;
								$datos['por_isr_retenido'] = buscar_impuesto($arreglo['cfdi:Comprobante'][0]['cfdi:Impuestos'][0]['cfdi:Retenciones'][0]['cfdi:Retencion'],'001','TasaOCuota')*100;
								$datos['sellodocumento'] = $arreglo['cfdi:Comprobante'][0]['@Sello'];
								$datos['seriecertificado'] = $arreglo['cfdi:Comprobante'][0]['@NoCertificado'];
								$datos['seriecertificadosat'] = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@NoCertificadoSAT'];
								$datos['sellotimbre'] = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@SelloSAT'];
								$datos['fechatimbre'] = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@FechaTimbrado'];
								$datos['rfcprovcertif'] = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@RfcProvCertif'];
								$version = $arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['tfd:TimbreFiscalDigital'][0]['@Version'];
								$datos['cadenaoriginal'] = '||'.$version.'|'.$datos['uuid'].'|'.$datos['fechatimbre'].'|'.$datos['sellodocumento'].'|'.$datos['seriecertificadosat'].'||';
								$fechas = explode("T",$arreglo['cfdi:Comprobante'][0]['@Fecha']);
								$datos['fecha']=$fechas[0];
								$datos['hora']=$fechas[1];
								$datos['nombre']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@Nombre']);
								$datos['rfc']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@Rfc'];
								$datos['regimenfiscal']= $arreglo['cfdi:Comprobante'][0]['cfdi:Emisor'][0]['@RegimenFiscal'];
								$datos['nombre_r']= utf8_decode($arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['@Nombre']);
								$datos['rfc_r']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['@Rfc'];
								$datos['usocfdi']= $arreglo['cfdi:Comprobante'][0]['cfdi:Receptor'][0]['@UsoCFDI'];
								
								$conceptos = array();
								/*echo '<pre>';
								print_r($arreglo);
								echo '</pre>';*/
								foreach($arreglo['cfdi:Comprobante'][0]['cfdi:Conceptos'][0]['cfdi:Concepto'] as $indice => $valores){
									$conceptos[]=array(
										'cantidad' => $valores['@Cantidad'],
										'concepto' => $valores['@Descripcion'],
										'unidad' => $valores['@Unidad'],
										'precio' => $valores['@ValorUnitario'],
										'importe' => $valores['@Importe'],
										'claveprodserv' => $valores['@ClaveProdServ'],
										'claveunidad' => $valores['@ClaveUnidad'],
										'importe_iva' => buscar_impuesto($valores['cfdi:Impuestos'][0]['cfdi:Traslados'][0]['cfdi:Traslado'],'002'),
										'iva_retenido' => buscar_impuesto($valores['cfdi:Impuestos'][0]['cfdi:Retenciones'][0]['cfdi:Retencion'],'002'),
										'isr_retenido' => buscar_impuesto($valores['cfdi:Impuestos'][0]['cfdi:Retenciones'][0]['cfdi:Retencion'],'001')
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
								mysql_query("INSERT sat_xml SET plaza='".$_POST['plazausuario']."',fecha_creacion='".fechaLocal()."',cheques='$cheques',
								hora_creacion='".horaLocal()."',obs='".$_POST['obs']."',usuario='".$_POST['cveusuario']."',tipo_xml='".$tipo_xml."'".$campos);
								$cvefact=mysql_insert_id();
								foreach($conceptos as $concepto){
									$campos = "";
									foreach($concepto as $campo=>$valor){
										$campos.= ",".$campo."='".$valor."'";
									}
									mysql_query("INSERT satmov_xml SET plaza='".$_POST['plazausuario']."',cvefact='".$cvefact."'".$campos);
								}

								/*foreach($arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['nomina:Nomina'][0]['nomina:Percepciones'][0]['nomina:Percepcion'] as $percepcion)
								{
									if(($percepcion['@ImporteGravado']+$percepcion['@ImporteExento']) > 0){
										mysql_query("INSERT sat_nomina SET plaza='".$_POST['plazausuario']."',cvefact='".$cvefact."',concepto='".$percepcion['@Concepto']."',monto='".round($percepcion['@ImporteGravado']+$percepcion['@ImporteExento'],2)."',tipo=0");
									}
								}
								foreach($arreglo['cfdi:Comprobante'][0]['cfdi:Complemento'][0]['nomina:Nomina'][0]['nomina:Deducciones'][0]['nomina:Deduccion'] as $deduccion)
								{
									if(($deduccion['@ImporteGravado']+$deduccion['@ImporteExento']) > 0){
										mysql_query("INSERT sat_nomina SET plaza='".$_POST['plazausuario']."',cvefact='".$cvefact."',concepto='".$deduccion['@Concepto']."',monto='".round($deduccion['@ImporteGravado']+$deduccion['@ImporteExento'],2)."',tipo=1");
									}
								}*/
								copy($arch,"../xmls/cfdis_".$_POST['plazausuario']."_".$cvefact.".xml");
								chmod("../xmls/cfdis_".$_POST['plazausuario']."_".$cvefact.".xml", 0777);
								$arch2=substr($arch,0,(count($arch)-4)).'pdf';
								$nombres_nuevos[$arch2] = "../xmls/cfdis_".$_POST['plazausuario']."_".$cvefact;
							}
							
						}
					}
				}
				for($i = 0; $i < $zip->numFiles; $i++)
				{
					$filename = $zip->getNameIndex($i);
					if(substr($filename,-3)=='pdf'){
						$arch = '../xmls/'.$nombre_carpeta.'/'.$filename;
						if($nombres_nuevos[$arch]!=""){
							copy($arch,$nombres_nuevos[$arch].".pdf");
							chmod($nombres_nuevos[$arch].".xml", 0777);
						}
					}
				}
				eliminarDir('../xmls/'.$nombre_carpeta);
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
<tr><th>Archivo</th><td><input type="file" name="archivo" id="archivo" class="textField">&nbsp;&nbsp;<a href="#" onClick="atcr(\'subir_xmls_sat.php\',\'\',2,0);"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td></tr>
</table>';

bottom();