<?php
include ("main.php"); 

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsPuestos=mysql_query("SELECT * FROM puestos ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_puestos[$Puestos['cve']]=$Puestos['nombre'];
	$array_riesgopuestos[$Puestos['cve']]=$Puestos['riesgo'];
}

$array_departamento = array();

$rsPuestos=mysql_query("SELECT * FROM departamentos ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_departamento[$Puestos['cve']]=$Puestos['nombre'];
}

$array_plazanombre = array();

$rsPuestos=mysql_query("SELECT * FROM datosempresas ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_plazanombre[$Puestos['plaza']]=$Puestos['nombre'];
	$array_plazadomicilio[$Puestos['plaza']]=$Puestos['domicilio'];
	$array_plaza_regimen[$Puestos['plaza']]=$Puestos['registro_patronal'];
}

$_POST['cveempresa']=1;
$tipo_nomina = 90;
$lusa = 0;


if($_POST['cmd']==50){
	require_once('excel/Worksheet.php');
	require_once('excel/Workbook.php');
	function HeaderingExcel($filename) {
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$filename" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}		
	// HTTP headers
	HeaderingExcel('finiquito.xls');
	$workbook = new Workbook("-");
	// Creating the first worksheet
	$worksheet1 =& $workbook->add_worksheet('Listado de Personal');
	$normal =& $workbook->add_format();
	$normal->set_align('left');
	$normal->set_align('vjustify');
	
	$worksheet1->write_string(0,0,'Finiquitos');
	$worksheet1->write_string(1,0,'Periodo: '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin']);
	
	$select= " SELECT a.nombre,a.plaza,a.nombre,a.puesto,a.rfc,a.imss,b.cve,b.totalpercepciones,b.totaldeducciones,a.salario_integrado as salario_base_personal, b.sal_diario,
		a.sdi as salario_integrado_personal, b.dias_tra, a.cobro_prestamo as monto_prestamo, a.cve as cvepersonal, b.uuid
		FROM personal as a 
		INNER JOIN personal_nomina as b ON (b.personal=a.cve AND b.tipo='3' AND b.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."')
		WHERE a.empresa='".$_POST['cveempresa']."'";
	if($_POST['plaza']>0) $select.=" AND a.plaza='".$_POST['plaza']."'";
	if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
	$rspersonal=mysql_query($select) or die(mysql_error());
	$totalRegistros = mysql_num_rows($rspersonal);
	$c=0;
	if(count($array_plaza)>1){ 
		$worksheet1->write_string(3,$c,'Plaza');$c++;
	}
	$worksheet1->write_string(3,$c,'Nombre');$c++;
	$worksheet1->write_string(3,$c,'Puesto');$c++;
	$worksheet1->write_string(3,$c,'R.F.C.');$c++;
	$worksheet1->write_string(3,$c,'N.S.S.');$c++;
	$worksheet1->write_string(3,$c,'Salario Diario');$c++;
	$array_percepciones=array();
	$resp=mysql_query("SELECT a.cve,a.nombre,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje FROM cat_percepciones a 
					WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=3 ORDER BY a.cve");
	while($rowp=mysql_fetch_array($resp)){
		$worksheet1->write_string(3,$c,$rowp['nombre']);$c++;
		$array_percepciones[$rowp['cve']]=$rowp['nombre'];
	}	
	$worksheet1->write_string(3,$c,'Total Percepciones');$c++;
	$array_deducciones=array();
	$resp=mysql_query("SELECT a.cve,a.nombre,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje FROM cat_deducciones a 
					WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=3 ORDER BY a.cve");
	while($rowp=mysql_fetch_array($resp)){
		$worksheet1->write_string(3,$c,$rowp['nombre']);$c++;
		$array_deducciones[$rowp['cve']]=$rowp['nombre'];
	}	
	$worksheet1->write_string(3,$c,'Total Deducciones');$c++;
	$worksheet1->write_string(3,$c,'Total a Pagar');$c++;
	$l=4;
	while($Personal=mysql_fetch_array($rspersonal)) {
		$c=0;
		if(count($array_plaza)>1){
			$worksheet1->write_string($l,$c,$array_plaza[$Personal['plaza']]);$c++;
		}
		$worksheet1->write_string($l,$c,$Personal['nombre']);$c++;
		$worksheet1->write_string($l,$c,$array_puestos[$Personal['puesto']]);$c++;
		$worksheet1->write_string($l,$c,$Personal['rfc']);$c++;
		$worksheet1->write_string($l,$c,$Personal['imss']);$c++;
		$worksheet1->write_string($l,$c,$Personal['sal_diario']);$c++;
		$total_percepciones=0;
		foreach($array_percepciones as $k=>$v){
			$resp=mysql_query("SELECT total FROM personal_nomina_percepcion WHERE nomina = '".$Personal['cve']."' AND percepcion = '$k'");
			$rowp=mysql_fetch_array($resp);
			$worksheet1->write_string($l,$c,$rowp['total']);$c++;
			$total_percepciones+=$rowp['total'];
		}
		$worksheet1->write_string($l,$c,$total_percepciones);$c++;
		$total_deducciones=0;
		foreach($array_deducciones as $k=>$v){
			$resp=mysql_query("SELECT total FROM personal_nomina_deduccion WHERE nomina = '".$Personal['cve']."' AND deduccion = '$k'");
			$rowp=mysql_fetch_array($resp);
			$worksheet1->write_string($l,$c,$rowp['total']);$c++;
			$total_deducciones+=$rowp['total'];
		}
		$worksheet1->write_string($l,$c,$total_deducciones);$c++;
		$worksheet1->write_string($l,$c,$total_percepciones-$total_deducciones);$c++;
		$l++;
	}
	
	$workbook->close();
	exit();
}

if($_POST['cmd']==7){
	require_once("nusoap/nusoap.php");
	foreach($_POST['cvepersonal'] as $personal){
		$res=mysql_query("SELECT * FROM personal_nomina WHERE cve='$personal' AND tipo='3' AND uuid!=''");
		if($row=mysql_fetch_array($res)){
			$Empresa = mysql_fetch_array(mysql_query("SELECT * FROM datosempresas WHERE plaza='".$row['plazatimbro']."'"));
			$oSoapClient = new nusoap_client("http://servicios.solucionesfe.com/wscfdi2013.php?wsdl",true);		
			$err = $oSoapClient->getError();
			if($err!="")
				echo "error1:".$err;
			else{
				//print_r($documento);
				$oSoapClient->timeout = 300;
				$oSoapClient->response_timeout = 300;
				//$respuesta = $oSoapClient->call("cancelar", array ('id' => $Empresa['idplaza'],'rfcemisor' => $Empresa['rfc'],'idcertificado' => $Empresa['idcertificado'],'uuid' => $row['uuid'], 'usuario' => $Empresa['usuario'],'password' => $Empresa['pass']));
				$respuesta = $oSoapClient->call("cancelar", array ('id' => $Empresa['idplazanomina'],'rfcemisor' => $Empresa['rfc'],'idcertificado' => $Empresa['idcertificadonomina'],'uuid' => $row['uuid'], 'usuario' => $Empresa['usuarionomina'],'password' => $Empresa['passnomina']));
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
						$strmsg='';
						if($respuesta['resultado']){
							mysql_query("UPDATE personal_nomina SET cancelacion_sat='".$respuesta['mensaje']."',uuid='' WHERE cve='".$row['cve']."'");
							mysql_query("UPDATE historial_timbrado SET cancelacion_sat='".$respuesta['mensaje']."',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE nomina='".$row['cve']."' AND uuid = '".$row['uuid']."'");
							/*generaFacturaPdf($_POST['cveempresa'],$cvefact);
							if($emailenvio!=""){
								$mail = new PHPMailer();
								$mail->Host = "localhost";
								$mail->From = "sunomina@sunomina.com";
								$mail->FromName = "SuNomina";
								$mail->Subject = "Cancelacion Timbre de Nomina ".$row['folio'];
								$mail->Body = "Cancelacion Factura ".$cvefact;
								//$mail->AddAddress(trim($emailenvio));
								$correos = explode(",",trim($emailenvio));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("cfdi/comprobantes/facturac_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
								$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
								$mail->Send();
							}	
							if($rowempresa['email']!=""){
								$mail = new PHPMailer();
								$mail->Host = "localhost";
								$mail->From = "hoyfactura@hoyfactura.com";
								$mail->FromName = "MiFactura";
								$mail->Subject = "Cancelacion Factura ".$cvefact;
								$mail->Body = "Cancelacion Factura ".$cvefact;
								//$mail->AddAddress(trim($rowempresa['email']));
								$correos = explode(",",trim($rowempresa['email']));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("cfdi/comprobantes/facturac_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
								$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
								$mail->Send();
							}*/
						}
						else
							$strmsg=$respuesta['mensaje'];
						//print_r($respuesta);	
						echo $strmsg;
					}
				}
			}
		}
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==6){
	require_once("nusoap/nusoap.php");
	$nominas = array();
	$numnominas = 0;
	$plazas_nominas=array();
	foreach($_POST['cvepersonal'] as $personal){
		//$res=mysql_query("SELECT * FROM personal WHERE cve='".$personal."'");
		//$Personal=mysql_fetch_array($res);
		//$res=mysql_query("SELECT * FROM personal_nomina WHERE periodo='".$_POST['periodo']."' AND personal='$personal' AND tipo='1' AND uuid=''");
		$res=mysql_query("SELECT * FROM personal_nomina WHERE cve='$personal' AND tipo='3' AND uuid=''");
		while($row=mysql_fetch_array($res)){
			$res1=mysql_query("SELECT * FROM personal WHERE cve='".$row['personal']."'");
			$Personal=mysql_fetch_array($res1);
			$Empresa = mysql_fetch_array(mysql_query("SELECT * FROM datosempresas WHERE plaza='".$Personal['plaza']."'"));
			$Empresa['mismadireccion']=1;
			$res2=mysql_query("SELECT * FROM plazas WHERE cve='".$Personal['plaza']."'");
			$Plaza=mysql_fetch_array($res2);
			$Plaza['riesgo']=1;
			if($Empresa['mismadireccion']==1){
				$Plaza['registro_patronal']=$Empresa['registro_patronal'];
				$Plaza['calle']=$Empresa['calle'];
				$Plaza['num_ext']=$Empresa['numexterior'];
				$Plaza['num_int']=$Empresa['numinterior'];
				$Plaza['colonia']=$Empresa['colonia'];
				$Plaza['municipio']=$Empresa['municipio'];
				$Plaza['estado']=$Empresa['estado'];
				$Plaza['codigopostal']=$Empresa['codigopostal'];
			}
			$monto=$row['totalpercepciones']-$row['total_deducciones'];
			if($monto>0){
				$nomina=array();
				$nomina['serie']='';
				$nomina['folio']=$row['folio'];
				//$nomina['fecha']=$Periodo['fecha_fin'];
				$nomina['formapago']='PAGO EN UNA SOLA EXHIBICION';
				$nomina['metodopago']=$array_tipo_pago[$Personal['metodo_pago']];
				$nomina['numerocuentapago']=$Empresa['cuenta_pago'];
				$nomina['empleado']=array(
					'numero'=>$Personal['folio'],
					'nombre'=>$Personal['nombre'],
					'domicilio'=>array(
						'calle'=>$Personal['calle'],
						'num_ext'=>$Personal['num_ext'],
						'num_int'=>$Personal['num_int'],
						'colonia'=>$Personal['colonia'],
						'localidad'=>$Personal['localidad'],
						'municipio'=>$Personal['municipio'],
						'estado'=>$Personal['estado'],
						'pais'=>'MEXICO',
						'codigopostal'=>$Personal['codigopostal']
					),
					'rfc'=>$Personal['rfc'].$Personal['homoclave'],
					'curp'=>$Personal['curp'],
					'registropatronal'=>$Plaza['registro_patronal'],
					'claseriesgo'=>$Plaza['riesgo'],
					'nss'=>$Personal['imss'],
					'tiporegimen'=>$Personal['tipo_regimen'],
					'departamento'=>$array_departamento[$Personal['departamento']],
					'clabe'=>$Personal['clabe'],
					'banco'=>$Personal['banco'],
					'fechacontratacion'=>$row['fecha_ini'],
					'antiguedad'=>antiguedad($row['fecha_ini']),
					'puesto'=>$array_puestos[$Personal['puesto']],
					'tipocontrato'=>$array_tipo_contrato[$Personal['tipo_contrato']],
					'tipojornada'=>$array_tipo_jornada[$Personal['tipo_jornada']],
					'salarioBase'=>$Personal['salario_base'],
					'sdi'=>$Personal['salario_integrado']
				);
				$nomina['lugarexpedicion']=array(
					'calle'=>$Plaza['calle'],
					'num_ext'=>$Plaza['num_ext'],
					'num_int'=>$Plaza['num_int'],
					'colonia'=>$Plaza['colonia'],
					'localidad'=>$Plaza['localidad'],
					'municipio'=>$Plaza['municipio'],
					'estado'=>$Plaza['estado'],
					'pais'=>'MEXICO',
					'codigopostal'=>$Plaza['codigopostal']
				);
				$nomina['concepto']='FINIQUITO DE '.$Personal['nombre'];
				$nomina['netoapercibir']=$monto;
				$nomina['moneda']=0;
				$nomina['tipocambio']=1;
				$nomina['fechapago']=$row['fecha_fin'];
				$nomina['fechainicial']=$row['fecha_ini'];
				$nomina['fechafinal']=$row['fecha_fin'];
				$nomina['diaspagados']=$row['dias_tra'];
				$nomina['periodicidadpago']=$array_tipo_nomina[$Empresa['tipo_nomina']];
				//$nomina['percepciones']=array();
				//$nomina['deducciones']=array();
				//$nomina['incapacidades']=array();
				//$nomina['horasextras']=array();
				$percepciones=array();
				$resdetalle=mysql_query("SELECT b.nombre, b.cve, b.codigosat, totalg as gravado, (total-totalg) as excento FROM cat_percepciones b INNER JOIN personal_nomina_percepcion a on a.percepcion=b.cve AND a.nomina='".$row['cve']."' WHERE b.tipo_nomina=3");
				while($Detalle=mysql_fetch_array($resdetalle)){
					$percepciones[] = array(
						'tipo'=>$Detalle['codigosat'],
						'clave'=>sprintf("%03s",$Detalle['cve']),
						'concepto'=>$Detalle['nombre'],
						'importegravado'=>$Detalle['gravado'],
						'importeexento'=>$Detalle['excento']);
				}
				$nomina['percepciones']=$percepciones;
				$deducciones=array();
				$resdetalle=mysql_query("SELECT b.nombre, b.cve, b.codigosat, totalg as gravado, (total-totalg) as excento FROM cat_deducciones b INNER JOIN personal_nomina_deduccion a on a.deduccion=b.cve AND a.nomina='".$row['cve']."' WHERE b.tipo_nomina=3");
				while($Detalle=mysql_fetch_array($resdetalle)){
					$deducciones[] = array(
						'tipo'=>$Detalle['codigosat'],
						'clave'=>sprintf("%03s",$Detalle['cve']),
						'concepto'=>$Detalle['nombre'],
						'importegravado'=>$Detalle['gravado'],
						'importeexento'=>$Detalle['excento']);
				}
				$nomina['deducciones']=$deducciones;
				$nomina['observaciones']='';
				//$nominas['recibos'][$row['cve']] = $nomina;
				$nominas[$row['cve']] = $nomina;
				$plazas_nominas[$row['cve']] = $Personal['plaza'];
				$numnominas++;
				if($numnominas==1){
					//$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
					$oSoapClient = new nusoap_client("http://servicios.solucionesfe.com/wscfdi2013.php?wsdl",true);
					$err = $oSoapClient->getError();
					if($err!="")
						echo "error1:".$err;
					else{
						//print_r($documento);
						$oSoapClient->timeout = 300;
						$oSoapClient->response_timeout = 300;
						//$respuestas = $oSoapClient->call("generar_recibos_nomina", array ('id' => $Empresa['idplaza'],'rfcemisor' => $Empresa['rfc'],'idcertificado' => $Empresa['idcertificado'],'recibos' => $nominas, 'usuario' => $Empresa['usuario'],'password' => $Empresa['pass']));
						$respuestas = $oSoapClient->call("generar_recibos_nomina", array ('id' => $Empresa['idplazanomina'],'rfcemisor' => $Empresa['rfc'],'idcertificado' => $Empresa['idcertificadonomina'],'recibos' => $nominas, 'usuario' => $Empresa['usuarionomina'],'password' => $Empresa['passnomina']));
						//$respuestas = $oSoapClient->call("generar_recibos_nomina", array ('id' => 592,'rfcemisor' => $Empresa['id,'idcertificado' => 561,'recibos' => $nominas, 'usuario' => 'ANASTAC10','password' => '3232fd6ec779d4ec11938d7a52db72ab'));
						if ($oSoapClient->fault) {
							echo '<p><b>Fault: ';
							print_r($respuestas);
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
								$indice = 0;
								foreach($nominas as $k=>$v){
									$respuesta = $respuestas[$indice];
									if($respuesta['resultado']){
										mysql_query("UPDATE personal_nomina SET seriecertificado='".$respuesta['seriecertificado']."',
										sellodocumento='".$respuesta['sellodocumento']."',uuid='".$respuesta['uuid']."',seriecertificadosat='".$respuesta['seriecertificadosat']."',
										sellotimbre='".$respuesta['sellotimbre']."',cadenaoriginal='".$respuesta['cadenaoriginal']."',
										fechatimbre='".substr($respuesta['fechatimbre'],0,10)." ".substr($respuesta['fechatimbre'],-8)."',
										cancelacion_sat='',registro_patronal = '".$v['empleado']['registropatronal']."', 
										calle = '".$v['lugarexpedicion']['calle']."', numexterior = '".$v['lugarexpedicion']['num_ext']."',
										numinterior = '".$v['lugarexpedicion']['num_int']."',colonia = '".$v['lugarexpedicion']['colonia']."',
										localidad = '".$v['lugarexpedicion']['localidad']."',municipio = '".$v['lugarexpedicion']['municipio']."',
										estado = '".$v['lugarexpedicion']['estado']."',codigopostal = '".$v['lugarexpedicion']['codigopostal']."',
										plazatimbro='".$plazas_nominas[$k]."',rfc='".$v['rfc']."',rfc_empresa='".$Empresa['rfc']."'
										WHERE cve=".$k);
										mysql_query("INSERT historial_timbrado SET nomina='".$k."',seriecertificado='".$respuesta['seriecertificado']."',
										sellodocumento='".$respuesta['sellodocumento']."',uuid='".$respuesta['uuid']."',seriecertificadosat='".$respuesta['seriecertificadosat']."',
										sellotimbre='".$respuesta['sellotimbre']."',cadenaoriginal='".$respuesta['cadenaoriginal']."',
										fechatimbre='".substr($respuesta['fechatimbre'],0,10)." ".substr($respuesta['fechatimbre'],-8)."',
										usuario='".$_POST['cveusuario']."',registro_patronal = '".$v['empleado']['registropatronal']."', 
										calle = '".$v['lugarexpedicion']['calle']."', numexterior = '".$v['lugarexpedicion']['num_ext']."',
										numinterior = '".$v['lugarexpedicion']['num_int']."',colonia = '".$v['lugarexpedicion']['colonia']."',
										localidad = '".$v['lugarexpedicion']['localidad']."',municipio = '".$v['lugarexpedicion']['municipio']."',
										estado = '".$v['lugarexpedicion']['estado']."',codigopostal = '".$v['lugarexpedicion']['codigopostal']."',
										plazatimbro='".$plazas_nominas[$k]."',rfc='".$v['rfc']."',rfc_empresa='".$Empresa['rfc']."',
										fecha_ini='".$v['fechainicial']."',fecha_fin='".$v['fechafinal']."'");
										//Tomar la informacion de Retorno
										$dir="cfdi/comprobantes/";
										//$dir=dirname(realpath(getcwd()))."/solucionesfe_facturacion/cfdi/comprobantes/";
										//el zip siempre se deja fuera
										$dir2="cfdi/";
										//Leer el Archivo Zip
										$fileresult=$respuesta['archivos'];
										$strzipresponse=base64_decode($fileresult);
										$filename='cfdifin_'.$_POST['cveempresa'].'_'.$k;
										file_put_contents($dir2.$filename.'.zip', $strzipresponse);
										$zip = new ZipArchive;
										if ($zip->open($dir2.$filename.'.zip') === TRUE){
											$strxml=$zip->getFromName('xml.xml');
											file_put_contents($dir.$filename.'.xml', $strxml);
											$strpdf=$zip->getFromName('formato.pdf');
											file_put_contents($dir.$filename.'.pdf', $strpdf);
											$zip->close();		
											/*generaFacturaPdf($_POST['cveempresa'],$cvefact);
											if($emailenvio!=""){
												$mail = new PHPMailer();
												$mail->Host = "localhost";
												$mail->From = "hoyfactura@hoyfactura.com";
												$mail->FromName = "MiFactura";
												$mail->Subject = "Factura ".$cvefact;
												$mail->Body = "Factura ".$cvefact;
												$mail->AddAddress(trim($emailenvio));
												$mail->AddAttachment("cfdi/comprobantes/factura_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
												$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
												$mail->Send();
											}	
											if($rowempresa['email']!=""){
												$mail = new PHPMailer();
												$mail->Host = "localhost";
												$mail->From = "hoyfactura@hoyfactura.com";
												$mail->FromName = "MiFactura";
												$mail->Subject = "Factura ".$cvefact;
												$mail->Body = "Factura ".$cvefact;
												//$mail->AddAddress(trim($rowempresa['email']));
												$correos = explode(",",trim($rowempresa['email']));
												foreach($correos as $correo)
													$mail->AddAddress(trim($correo));
												$mail->AddAttachment("cfdi/comprobantes/factura_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
												$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
												$mail->Send();
											}*/	
										}
										else{
											$strmsg='Error al descomprimir el archivo';
										}
									}
									else{
										$strmsg=$respuesta['mensaje'];
									}
									$indice++;
								}
							}
						}
					}
					$numnominas = 0;
					$nominas = array();
					$plazas_nominas=array();
				}
			}
		}
	}
	$_POST['cmd']=0;
}


if($_POST['ajax']==1){
	$select= " SELECT a.nombre,a.plaza,a.nombre,a.puesto,a.rfc,a.imss,b.cve,b.totalpercepciones,b.totaldeducciones,a.salario_integrado as salario_base_personal, b.sal_diario,
		a.sdi as salario_integrado_personal, b.dias_tra, a.cobro_prestamo as monto_prestamo, a.cve as cvepersonal, b.uuid, a.monto_infonavit,b.totaldeducciones,b.totalpercepciones, b.cancelacion_sat
		FROM personal as a 
		INNER JOIN personal_nomina as b ON (b.personal=a.cve AND b.tipo='3' AND b.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."')
		WHERE a.empresa='".$_POST['cveempresa']."'";
	if($_POST['plaza']>0) $select.=" AND a.plaza='".$_POST['plaza']."'";
	if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
	$rspersonal=mysql_query($select) or die(mysql_error());
	$totalRegistros = mysql_num_rows($rspersonal);
	echo '<input type="hidden" name="cveperiodo" value="'.$_POST['periodo'].'">';
	if(mysql_num_rows($rspersonal)>0) 
	{
		echo '<h1>'.$Periodo['nombre'].'</h1>';
		echo '<input type="hidden" name="generada" id="generada" value="'.$Periodo['generada_fiscal'].'">';
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
		echo '<tr bgcolor="#E9F2F8">';
		echo '<th>&nbsp;</th>';
		echo '<th><input type="checkbox" name="selall" value="1" onClick="if(this.checked) $(\'.chkpersonal\').attr(\'checked\',\'checked\'); else $(\'.chkpersonal\').removeAttr(\'checked\');"></td>';
		if(count($array_plaza)>1) echo '<th>Plaza</th>';
		echo '<th>Estatus</th>
		<th><a href="#" onclick="SortTable(2,\'S\',\'tabla1\');">Nombre</a></th><th>Puesto</th><th>R.F.C.</th><th>N.S.S.</th>
				<th>Salario Diario</th><th>Total Percepciones</th><th>Total Deducciones</th><th>Total a Pagar</th>';
		echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
		$total=0;
		$timp=$tper=$tded=0;
		$i=0;
		while($Personal=mysql_fetch_array($rspersonal)) {
			rowb();
			echo '<td align=center width="5%"><a href="#" onClick="atcr(\'finiquito.php\',\'\',1,\''.$Personal['cve'].'\');"><img src="images/modificar.gif" border="0"></a>
			&nbsp;&nbsp;<a href="#" onClick="atcr(\'imp_finiquito.php\',\'_blank\',1,\''.$Personal['cve'].'\');"><img src="images/b_print.png" border="0"></a></td>';
			echo '<td align="center"><input type="checkbox" name="cvepersonal[]" value="'.$Personal['cve'].'" class="chkpersonal"';
			//if($Personal['uuid']!="") echo ' disabled';
			echo '></td>';
			if(count($array_plaza)>1) echo '<td>'.$array_plaza[$Personal['plaza']].'</td>';
			echo '<td align="center">';
			if($Personal['uuid']=='' && $Personal['cancelacion_sat']=='') echo 'Pendiente de Timbrar';
			elseif($Personal['uuid']!='') echo 'Timbrada';
			else echo 'Timbre Cancelado';
			echo '</td>';
			$total_deducciones=$Personal['totaldeducciones'];
			$total_percepciones=$Personal['totalpercepciones'];
			echo '<td>'.$Personal["nombre"].'</td>';
			echo '<td>'.$array_puestos[$Personal['puesto']].'</td>';
			echo '<td align="center">'.$Personal['rfc'].'</td>';
			echo '<td align="center">'.$Personal['imss'].'</td>';
			echo '<td align="right">'.number_format($Personal['sal_diario'],2).'</td>';
			echo '<td align="right">'.number_format($total_percepciones,2).'</td>';
			echo '<td align="right">'.number_format($total_deducciones,2).'</td>';
			echo '<td align="right"><span style="cursor: pointer; text-decoration: underline; color: blue;" onClick="mostrarDetalle('.$Personal['cve'].')">'.number_format($total_percepciones-$total_deducciones,2).'</span></td>';
			echo '</tr>';
			$i++;
			$timp+=$Personal['sal_diario']*$Personal['dias_tra'];
			$tper+=$total_percepciones;
			$tded+=$total_deducciones;
			$total+=($total_percepciones-$total_deducciones);
		}
		$col=7;
		/*if($nominagen!=0){
			$col++;
		}*/
		if(count($array_plaza)>1) $col++;
		echo '</tbody>
			<tr bgcolor="#E9F2F8"><td colspan="'.$col.'">'.$i.' Registro(s)</td><td>Total</td>
			<td align="right">'.number_format($tper,2).'</td>
			<td align="right">'.number_format($tded,2).'</td>
			<td align="right">'.number_format($total,2).'</td></tr>
		</table>';
		
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

if($_POST['ajax']==2){
	$res=mysql_query("SELECT b.nombre as nompersonal,a.totalpercepciones,a.totaldeducciones
	FROM personal_nomina a INNER JOIN personal b on a.personal = b.cve 
	WHERE a.cve='".$_POST['cvenomina']."'");
	$row=mysql_fetch_array($res);
	echo '<h1>'.$row['nompersonal'].'<br>Finiquito</h1>';
	echo '<table width="100%"><tr><td width="50%" valign="top">
	<table width="100%"><tr><th>Concepto</th><th>Monto</th></tr>';
	$res1=mysql_query("SELECT b.nombre, a.total FROM personal_nomina_percepcion a INNER JOIN cat_percepciones b on a.percepcion=b.cve WHERE a.nomina='".$_POST['cvenomina']."'");
	while($row1=mysql_fetch_array($res1)){
		echo '<tr>';
		echo '<td>'.$row1['nombre'].'</td>';
		echo '<td align="right">'.number_format($row1['total'],2).'</td>';
		echo '</tr>';
	}
	echo '</table></td><td width="50%" valign="top">
	<table width="100%"><tr><th>Concepto</th><th>Monto</th></tr>';
	$res1=mysql_query("SELECT b.nombre, a.total FROM personal_nomina_deduccion a INNER JOIN cat_deducciones b on a.deduccion=b.cve WHERE a.nomina='".$_POST['cvenomina']."'");
	while($row1=mysql_fetch_array($res1)){
		echo '<tr>';
		echo '<td>'.$row1['nombre'].'</td>';
		echo '<td align="right">'.number_format($row1['total'],2).'</td>';
		echo '</tr>';
	}
	echo '</table></td></tr>
	<tr><th align="right">Total: '.number_format($row['totalpercepciones'],2).'</th>
	<th align="right">Total: '.number_format($row['totaldeducciones'],2).'</th></tr></table>';
	
	exit();
}

if($_GET['ajax']==3){
	$select= " SELECT cve, folio, nombre FROM personal WHERE plaza='".$_GET['plaza']."' AND estatus=2 AND ";
	$select.=" (nombre like '%".$_GET['term']."%' or imss like '".$_GET['term']."%')";
	$select .= " ORDER BY nombre";
	$res=mysql_query($select) or die(mysql_error());
	$matches = array();
	while($row=mysql_fetch_assoc($res)){
		// Adding the necessary "value" and "label" fields and appending to result set
		$row['value'] = "";
		$row['label'] = "{$row['folio']}, ".utf8_encode($row['nombre']).", ".$row['imss']."";
		$row['cve'] = htmlentities($row['cve']);
		$row['nombre'] = htmlentities($row['nombre']);
		$matches[] = $row;
	  } 
	  // Truncate, encode and return the results
	  $matches = array_slice($matches, 0, 15);
	  print json_encode($matches);
	exit();
}

top($_SESSION);

if($_POST['cmd']==2){
	if($_POST['reg']>0){
		mysql_query("UPDATE personal_nomina SET sal_diario='".$_POST['montoxdia'][10]."',dias_tra='".$_POST['dias'][9]."',faltas='".$_POST['diasd'][1]."',
		totalpercepciones='".$_POST['totalpercepciones']."',totaldeducciones='".$_POST['totaldeducciones']."' WHERE cve='".$_POST['reg']."'");
	}
	else{
		mysql_query("INSERT personal_nomina SET empresa='".$_POST['cveempresa']."',tipo=3,fecha_gen='".fechaLocal()."',fecha='".fechaLocal()."',personal='".$_POST['personal']."',sal_diario='".$_POST['montoxdia'][10]."',dias_tra='".$_POST['dias'][9]."',faltas='".$_POST['diasd'][1]."',
		totalpercepciones='".$_POST['totalpercepciones']."',totaldeducciones='".$_POST['totaldeducciones']."',
		fecha_ini='".$_POST['fecha_alta']."',fecha_fin='".$_POST['fecha_baja']."'")or die(mysql_error());
		$_POST['reg']=mysql_insert_id();
	}
	$resp=mysql_query("SELECT * FROM cat_percepciones a 
					WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=3 ORDER BY a.cve");
	while($rowp=mysql_fetch_array($resp)){
		$cve = $rowp['cve'];
		if($_POST['cvepercepcion'][$cve] <= 0){
			$select = "INSERT ";
		}
		else{
			$select = "UPDATE ";
		}
		$select .= "personal_nomina_percepcion SET empresa='".$_POST['cveempresa']."',nomina = '".$_POST['reg']."',percepcion='".$cve."',
		dias='".$_POST['dias'][$cve]."',montoxdia='".$_POST['montoxdia'][$cve]."',total='".$_POST['total'][$cve]."',totalg='".$_POST['totalg'][$cve]."'";
		if($_POST['cvepercepcion'][$cve] > 0){
			$select .= " WHERE cve = '".$_POST['cvepercepcion'][$cve]."'";
		}
		//echo $select.'<br>';
		mysql_query($select);
	}
	
	$resp=mysql_query("SELECT * FROM cat_deducciones a 
					WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=3 ORDER BY a.cve");
	while($rowp=mysql_fetch_array($resp)){
		$cve = $rowp['cve'];
		if($_POST['cvededuccion'][$cve] <= 0){
			$select = "INSERT ";
		}
		else{
			$select = "UPDATE ";
		}
		$select .= "personal_nomina_deduccion SET empresa='".$_POST['cveempresa']."',nomina = '".$_POST['reg']."',deduccion='".$cve."',
		dias='".$_POST['diasd'][$cve]."',montoxdia='".$_POST['montoxdiad'][$cve]."',total='".$_POST['totald'][$cve]."',totalg='".$_POST['totaldg'][$cve]."'";
		if($_POST['cvededuccion'][$cve] > 0){
			$select .= " WHERE cve = '".$_POST['cvededuccion'][$cve]."'";
		}
		//echo $select.'<br>';
		mysql_query($select);
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==1){
	if($_POST['reg']>0){
		$resNomina=mysql_query("SELECT * FROM personal_nomina WHERE cve='".$_POST['reg']."'");
		$Nomina=mysql_fetch_array($resNomina);
		$resPersonal=mysql_query("SELECT * FROM personal WHERE cve='".$Nomina['personal']."'");
		$Personal=mysql_fetch_array($resPersonal);
		$_POST['fecha_alta']=$Nomina['fecha_ini'];
		$_POST['fecha_baja']=$Nomina['fecha_fin'];
	}
	else{
		$resPersonal=mysql_query("SELECT * FROM personal WHERE cve='".$_POST['personal']."'");
		$Personal=mysql_fetch_array($resPersonal);
	}
	$personal = $_POST['personal'];
	$res = mysql_query("SELECT * FROM impuestos_imss ORDER BY cve DESC LIMIT 1");
	$row = mysql_fetch_array($res);
	$minimo = $row['smdf'];
	echo '<table><tr>';
	if(nivelUsuario()>1 && $Nomina['uuid']==""){
		echo '<td><a href="#" onClick="atcr(\'finiquito.php\',\'\',2,\''.$_POST['reg'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	}
	echo '<td><a href="#" onclick="atcr(\'finiquito.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
	echo '</tr></table>';
	echo '<br>';
	echo '<input type="hidden" name="personal" id="personal" value="'.$Personal['cve'].'">';
	echo '<input type="hidden" name="fecha_alta" id="fecha_alta" value="'.$_POST['fecha_alta'].'">';
	echo '<input type="hidden" name="fecha_baja" id="fecha_baja" value="'.$_POST['fecha_baja'].'">';
	echo '<table>';
	echo '<tr><td class="tableEnc">Finiquito Personal '.$Personal['nombre'].' del '.$_POST['fecha_alta'].' al '.$_POST['fecha_baja'].'</td></tr>';
	echo '</table>';
	echo '<br>';
	echo '<table>';
	echo '<tr><td colspan="3" class="tableEnc">Percepciones</td></tr>';
	echo '<tr><th>Concepto</th><th>Dias</th><th>Importe</th></tr>';
	$total_percepciones=0;
	$total_percepcionesg=0;
	//print_r(dias_vacaciones($personal,1,$_POST['fecha_alta'],$_POST['fecha_baja'],0));
	$resp=mysql_query("SELECT a.cve,a.nombre,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje,
					b.cve as cvepercepcion, b.dias, b.montoxdia, b.total, b.totalg FROM cat_percepciones a 
					LEFT JOIN personal_nomina_percepcion b ON a.cve = b.percepcion AND b.nomina = '".$Nomina['cve']."'
					WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=3 ORDER BY a.cve");
	while($rowp=mysql_fetch_array($resp)){
		if($rowp['cve']==9){
			if($_POST['reg']==0 || $Nomina['uuid']==''){
				$res=mysql_query("SELECT (DATEDIFF('".$_POST['fecha_baja']."',b.fecha_fin)) as dias_pendientes FROM personal_nomina a INNER JOIN periodos_nomina b ON a.periodo=b.cve WHERE a.personal = '".$Personal['cve']."' AND b.aguinaldo!=1 ORDER BY b.fecha_fin DESC LIMIT 1");
				if($row=mysql_fetch_array($res)){
					$rowp['dias']=$row['dias_pendientes'];
				}
				else{
					$res=mysql_query("SELECT (DATEDIFF('".$Personal['fecha_sta']."',fecha)) as dias_pendientes FROM cambios_datos_personal WHERE cve_personal='".$Personal['cve']."' AND dato='Estatus' AND valor_nuevo=1 ORDER BY fecha DESC LIMIT 1");
					$row=mysql_fetch_array($res);
					$rowp['dias']=$row['dias_pendientes'];
				}
				if($rowp['dias']<0) $rowp['dias']=0;
				$rowp['montoxdia']=$Personal['salario_integrado'];
				$rowp['total']=round($rowp['montoxdia']*$rowp['dias'],2);
				$rowp['totalg']=round($rowp['montoxdia']*$rowp['dias'],2);
			}
			if($rowp['total']>0){
				echo '<input type="hidden" name="cvepercepcion['.$rowp['cve'].']" value="'.$rowp['cvepercepcion'].'">';
				echo '<tr><th align="left">'.$rowp['nombre'].'</th>';
				echo '<td align="center"><input type="text" class="readOnly" id="dias_'.$rowp['cve'].'" name="dias['.$rowp['cve'].']" value="'.$rowp['dias'].'" readOnly><input type="hidden" class="readOnly" id="montoxdia_'.$rowp['cve'].'" name="montoxdia['.$rowp['cve'].']" value="'.$rowp['montoxdia'].'" readOnly></td>';
				echo '<td align="left"><input type="text" class="readOnly percepciones" id="total_'.$rowp['cve'].'" name="total['.$rowp['cve'].']" value="'.round($rowp['total'],2).'" readOnly>
				<input type="checkbox" onClick="
				if(this.checked){
					document.getElementById(\'montoxdia_'.$rowp['cve'].'\').value=\''.$rowp['montoxdia'].'\';
					document.getElementById(\'dias_'.$rowp['cve'].'\').value=\''.$rowp['dias'].'\';
					document.getElementById(\'total_'.$rowp['cve'].'\').value=\''.$rowp['total'].'\';
					document.getElementById(\'totalg_'.$rowp['cve'].'\').value=\''.$rowp['totalg'].'\';
				}
				else{
					document.getElementById(\'montoxdia_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'dias_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'total_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'totalg_'.$rowp['cve'].'\').value=\'\';
				}
				totales();" checked>
				<input type="hidden" class="readOnly percepcionesgravables" id="totalg_'.$rowp['cve'].'" name="totalg['.$rowp['cve'].']" value="'.round($rowp['totalg'],2).'" readOnly></td></tr>';
			}
		}
		elseif($rowp['cve']==10){
			if($_POST['reg']==0 || $Nomina['uuid']==''){
				if($_POST['fecha_alta']>(substr($_POST['fecha_baja'],0,4).'-01-01'))
					$res=mysql_query("SELECT (DATEDIFF('".$_POST['fecha_baja']."','".$_POST['fecha_alta']."')+1) as dias_recorridos");
				else
					$res=mysql_query("SELECT (DATEDIFF('".$_POST['fecha_baja']."','".date("Y")."-01-01')+1) as dias_recorridos");
				$row=mysql_fetch_array($res);
				$rowp['dias']=round($row['dias_recorridos']*15/365,2);
				$rowp['montoxdia']=$Personal['salario_integrado'];
				$rowp['total']=round($rowp['montoxdia']*$rowp['dias'],2);
				$rowp['totalg']=round($rowp['montoxdia']*$rowp['dias'],2)-round($minimo*30,2);
				if($rowp['totalg']<0) $rowp['totalg']=0;
			}
			if($rowp['total']>0){
				echo '<input type="hidden" name="cvepercepcion['.$rowp['cve'].']" value="'.$rowp['cvepercepcion'].'">';
				echo '<tr><th align="left">'.$rowp['nombre'].'</th>';
				echo '<td align="center"><input type="text" class="readOnly" id="dias_'.$rowp['cve'].'" name="dias['.$rowp['cve'].']" value="'.$rowp['dias'].'" readOnly><input type="hidden" class="readOnly" id="montoxdia_'.$rowp['cve'].'" name="montoxdia['.$rowp['cve'].']" value="'.$rowp['montoxdia'].'" readOnly></td>';
				echo '<td align="left"><input type="text" class="readOnly percepciones" id="total_'.$rowp['cve'].'" name="total['.$rowp['cve'].']" value="'.round($rowp['total'],2).'" readOnly>
				<input type="checkbox" onClick="
				if(this.checked){
					document.getElementById(\'montoxdia_'.$rowp['cve'].'\').value=\''.$rowp['montoxdia'].'\';
					document.getElementById(\'dias_'.$rowp['cve'].'\').value=\''.$rowp['dias'].'\';
					document.getElementById(\'total_'.$rowp['cve'].'\').value=\''.$rowp['total'].'\';
					document.getElementById(\'totalg_'.$rowp['cve'].'\').value=\''.$rowp['totalg'].'\';
				}
				else{
					document.getElementById(\'montoxdia_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'dias_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'total_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'totalg_'.$rowp['cve'].'\').value=\'\';
				}
				totales();" checked><input type="hidden" class="readOnly percepcionesgravables" id="totalg_'.$rowp['cve'].'" name="totalg['.$rowp['cve'].']" value="'.round($rowp['totalg'],2).'" readOnly></td></tr>';
			}
		}
		elseif($rowp['cve']==11){
			if($_POST['reg']==0 || $Nomina['uuid']==''){
				$arreglo_vacaciones = dias_vacaciones($personal,1,$_POST['fecha_alta'],$_POST['fecha_baja'],0);
				$rowp['dias']=$arreglo_vacaciones['dias_pagar'];
				$rowp['montoxdia']=$Personal['salario_integrado'];
				$rowp['total']=round($rowp['montoxdia']*$rowp['dias'],2);
				$rowp['totalg']=round($rowp['montoxdia']*$rowp['dias'],2);
			}
			if($rowp['total']>0){
				echo '<input type="hidden" name="cvepercepcion['.$rowp['cve'].']" value="'.$rowp['cvepercepcion'].'">';
				echo '<tr><th align="left">'.$rowp['nombre'].'</th>';
				echo '<td align="center"><input type="text" class="readOnly" id="dias_'.$rowp['cve'].'" name="dias['.$rowp['cve'].']" value="'.$rowp['dias'].'" readOnly><input type="hidden" class="readOnly" id="montoxdia_'.$rowp['cve'].'" name="montoxdia['.$rowp['cve'].']" value="'.$rowp['montoxdia'].'" readOnly></td>';
				echo '<td align="left"><input type="text" class="readOnly percepciones" id="total_'.$rowp['cve'].'" name="total['.$rowp['cve'].']" value="'.round($rowp['total'],2).'" readOnly>
				<input type="checkbox" onClick="
				if(this.checked){
					document.getElementById(\'montoxdia_'.$rowp['cve'].'\').value=\''.$rowp['montoxdia'].'\';
					document.getElementById(\'dias_'.$rowp['cve'].'\').value=\''.$rowp['dias'].'\';
					document.getElementById(\'total_'.$rowp['cve'].'\').value=\''.$rowp['total'].'\';
					document.getElementById(\'totalg_'.$rowp['cve'].'\').value=\''.$rowp['totalg'].'\';
					if($(\'#montoxdia_12\').length>0){
						document.getElementById(\'montoxdia_12\').value=document.getElementById(\'rmontoxdia_12\').value;
						document.getElementById(\'dias_12\').value=document.getElementById(\'rdias_12\').value;
						document.getElementById(\'total_12\').value=document.getElementById(\'rtotal_12\').value;
						document.getElementById(\'totalg_12\').value=document.getElementById(\'rtotalg_12\').value;
					}
				}
				else{
					document.getElementById(\'montoxdia_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'dias_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'total_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'totalg_'.$rowp['cve'].'\').value=\'\';
					if($(\'#montoxdia_12\').length>0){
						document.getElementById(\'montoxdia_12\').value=\'\';
						document.getElementById(\'dias_12\').value=\'\';
						document.getElementById(\'total_12\').value=\'\';
						document.getElementById(\'totalg_12\').value=\'\';
					}
				}
				totales();" checked>
				<input type="hidden" class="readOnly percepcionesgravables" id="totalg_'.$rowp['cve'].'" name="totalg['.$rowp['cve'].']" value="'.round($rowp['totalg'],2).'" readOnly></td></tr>';
			}
		}
		elseif($rowp['cve']==12){
			if($_POST['reg']==0 || $Nomina['uuid']==''){
				$arreglo_vacaciones = dias_vacaciones($personal,1,$_POST['fecha_alta'],$_POST['fecha_baja'],0);
				$rowp['dias']=round($arreglo_vacaciones['dias_pagar']*0.25,2);
				$rowp['montoxdia']=$Personal['salario_integrado'];
				$rowp['total']=round($rowp['montoxdia']*$rowp['dias'],2);
				$rowp['totalg']=round($rowp['montoxdia']*$rowp['dias'],2)-round($minimo*15,2);
				if($rowp['totalg']<0) $rowp['totalg']=0;
			}
			if($rowp['total']>0){
				echo '<input type="hidden" id="rdias_'.$rowp['cve'].'" value="'.$rowp['dias'].'">';
				echo '<input type="hidden" id="rmontoxdia_'.$rowp['cve'].'" value="'.$rowp['montoxdia'].'">';
				echo '<input type="hidden" id="rtotal_'.$rowp['cve'].'" value="'.$rowp['total'].'">';
				echo '<input type="hidden" id="rtotalg_'.$rowp['cve'].'" value="'.$rowp['totalg'].'">';
				echo '<input type="hidden" name="cvepercepcion['.$rowp['cve'].']" value="'.$rowp['cvepercepcion'].'">';
				echo '<tr><th align="left">'.$rowp['nombre'].'</th>';
				echo '<td align="center"><input type="hidden" class="readOnly" id="dias_'.$rowp['cve'].'" name="dias['.$rowp['cve'].']" value="'.$rowp['dias'].'" readOnly><input type="hidden" class="readOnly" id="montoxdia_'.$rowp['cve'].'" name="montoxdia['.$rowp['cve'].']" value="'.$rowp['montoxdia'].'" readOnly></td>';
				echo '<td align="left"><input type="text" class="readOnly percepciones" id="total_'.$rowp['cve'].'" name="total['.$rowp['cve'].']" value="'.round($rowp['total'],2).'" readOnly><input type="hidden" class="readOnly percepcionesgravables" id="totalg_'.$rowp['cve'].'" name="totalg['.$rowp['cve'].']" value="'.round($rowp['totalg'],2).'" readOnly></td></tr>';
			}
		}
		elseif($rowp['cve']==13){
			if($_POST['reg']==0 || $Nomina['uuid']==''){
				$arreglo_vacaciones = dias_vacaciones($personal,1,$_POST['fecha_alta'],$_POST['fecha_baja'],1);
				$rowp['dias']=$arreglo_vacaciones['dias_pagar'];
				$rowp['montoxdia']=$Personal['salario_integrado'];
				$rowp['total']=round($rowp['montoxdia']*$rowp['dias'],2);
				$rowp['totalg']=round($rowp['montoxdia']*$rowp['dias'],2);
			}
			if($rowp['total']>0){
				echo '<input type="hidden" name="cvepercepcion['.$rowp['cve'].']" value="'.$rowp['cvepercepcion'].'">';
				echo '<tr><th align="left">'.$rowp['nombre'].'</th>';
				echo '<td align="center"><input type="text" class="readOnly" id="dias_'.$rowp['cve'].'" name="dias['.$rowp['cve'].']" valor="'.$rowp['dias'].'" value="'.$rowp['dias'].'" readOnly><input type="hidden" class="readOnly" id="montoxdia_'.$rowp['cve'].'" name="montoxdia['.$rowp['cve'].']" valor="'.$rowp['montoxdia'].'" value="'.$rowp['montoxdia'].'" readOnly></td>';
				echo '<td align="left"><input type="text" class="readOnly percepciones" id="total_'.$rowp['cve'].'" name="total['.$rowp['cve'].']" valor="'.round($rowp['total'],2).'" value="'.round($rowp['total'],2).'" readOnly>
				<input type="checkbox" onClick="
				if(this.checked){
					document.getElementById(\'montoxdia_'.$rowp['cve'].'\').value=\''.$rowp['montoxdia'].'\';
					document.getElementById(\'dias_'.$rowp['cve'].'\').value=\''.$rowp['dias'].'\';
					document.getElementById(\'total_'.$rowp['cve'].'\').value=\''.$rowp['total'].'\';
					document.getElementById(\'totalg_'.$rowp['cve'].'\').value=\''.$rowp['totalg'].'\';
					if($(\'#montoxdia_14\').length>0){
						document.getElementById(\'montoxdia_14\').value=document.getElementById(\'rmontoxdia_14\').value;
						document.getElementById(\'dias_14\').value=document.getElementById(\'rdias_14\').value;
						document.getElementById(\'total_14\').value=document.getElementById(\'rtotal_14\').value;
						document.getElementById(\'totalg_14\').value=document.getElementById(\'rtotalg_14\').value;
					}
				}
				else{
					document.getElementById(\'montoxdia_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'dias_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'total_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'totalg_'.$rowp['cve'].'\').value=\'\';
					if($(\'#montoxdia_14\').length>0){
						document.getElementById(\'montoxdia_14\').value=\'\';
						document.getElementById(\'dias_14\').value=\'\';
						document.getElementById(\'total_14\').value=\'\';
						document.getElementById(\'totalg_14\').value=\'\';
					}
				}
				totales();" checked>
				<input type="hidden" class="readOnly percepcionesgravables" id="totalg_'.$rowp['cve'].'" name="totalg['.$rowp['cve'].']" valor="'.round($rowp['totalg'],2).'" value="'.round($rowp['totalg'],2).'" readOnly></td></tr>';
			}
		}
		elseif($rowp['cve']==14){
			if($_POST['reg']==0 || $Nomina['uuid']==''){
				$arreglo_vacaciones = dias_vacaciones($personal,1,$_POST['fecha_alta'],$_POST['fecha_baja'],1);
				$rowp['dias']=round($arreglo_vacaciones['dias_pagar']*0.25,2);
				$rowp['montoxdia']=$Personal['salario_integrado'];
				$rowp['total']=round($rowp['montoxdia']*$rowp['dias'],2);
				$rowp['totalg']=round($rowp['montoxdia']*$rowp['dias'],2)-round($minimo*15,2);
				if($rowp['totalg']<0) $rowp['totalg']=0;
			}
			if($rowp['total']>0){
				echo '<input type="hidden" id="rdias_'.$rowp['cve'].'" value="'.$rowp['dias'].'">';
				echo '<input type="hidden" id="rmontoxdia_'.$rowp['cve'].'" value="'.$rowp['montoxdia'].'">';
				echo '<input type="hidden" id="rtotal_'.$rowp['cve'].'" value="'.$rowp['total'].'">';
				echo '<input type="hidden" id="rtotalg_'.$rowp['cve'].'" value="'.$rowp['totalg'].'">';
				echo '<input type="hidden" name="cvepercepcion['.$rowp['cve'].']" value="'.$rowp['cvepercepcion'].'">';
				echo '<tr><th align="left">'.$rowp['nombre'].'</th>';
				echo '<td align="center"><input type="hidden" class="readOnly" valor='.$rowp['dias'].' id="dias_'.$rowp['cve'].'" name="dias['.$rowp['cve'].']" value="'.$rowp['dias'].'" readOnly><input type="hidden" class="readOnly" id="montoxdia_'.$rowp['cve'].'" name="montoxdia['.$rowp['cve'].']" valor="'.$rowp['montoxdia'].'" value="'.$rowp['montoxdia'].'" readOnly></td>';
				echo '<td align="left"><input type="text" class="readOnly percepciones" id="total_'.$rowp['cve'].'" name="total['.$rowp['cve'].']" valor="'.round($rowp['total'],2).'" value="'.round($rowp['total'],2).'" readOnly><input type="hidden" class="readOnly percepcionesgravables" id="totalg_'.$rowp['cve'].'" name="totalg['.$rowp['cve'].']" valor="'.round($rowp['totalg'],2).'" value="'.round($rowp['totalg'],2).'" readOnly></td></tr>';
			}
		}
		elseif($rowp['cve']==30){
			if($_POST['reg']==0 || $Nomina['uuid']==''){
				$res=mysql_query("SELECT (DATEDIFF('".$_POST['fecha_baja']."','".$_POST['fecha_alta']."')+1) as dias_recorridos");
				$row=mysql_fetch_array($res);
				$anios_trabajados = round($row['dias_recorridos']/365,2);
				if($anios_trabajados>=15){
					$rowp['dias']=round(12*$anios_trabajados,2);
					$rowp['montoxdia']=$minimo*2;
					$rowp['total']=round($rowp['montoxdia']*$rowp['dias'],2);
					$rowp['totalg']=round($rowp['montoxdia']*$rowp['dias'],2)-round($minimo*90*round($anios_trabajados,0),2);
					if($rowp['totalg']<0) $rowp['totalg']=0;
				}
			}
			if($rowp['total']>0){
				echo '<input type="hidden" name="cvepercepcion['.$rowp['cve'].']" value="'.$rowp['cvepercepcion'].'">';
				echo '<tr><th align="left">'.$rowp['nombre'].'</th>';
				echo '<td align="center"><input type="text" class="readOnly" id="dias_'.$rowp['cve'].'" name="dias['.$rowp['cve'].']" valor="'.$rowp['dias'].'" value="'.$rowp['dias'].'" readOnly><input type="hidden" class="readOnly" id="montoxdia_'.$rowp['cve'].'" name="montoxdia['.$rowp['cve'].']" valor="'.$rowp['montoxdia'].'" value="'.$rowp['montoxdia'].'" readOnly></td>';
				echo '<td align="left"><input type="text" class="readOnly percepciones" id="total_'.$rowp['cve'].'" name="total['.$rowp['cve'].']" valor="'.round($rowp['total'],2).'" value="'.round($rowp['total'],2).'" readOnly>
				<input type="checkbox" onClick="
				if(this.checked){
					document.getElementById(\'montoxdia_'.$rowp['cve'].'\').value=\''.$rowp['montoxdia'].'\';
					document.getElementById(\'dias_'.$rowp['cve'].'\').value=\''.$rowp['dias'].'\';
					document.getElementById(\'total_'.$rowp['cve'].'\').value=\''.$rowp['total'].'\';
					document.getElementById(\'totalg_'.$rowp['cve'].'\').value=\''.$rowp['totalg'].'\';
				}
				else{
					document.getElementById(\'montoxdia_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'dias_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'total_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'totalg_'.$rowp['cve'].'\').value=\'\';
				}
				totales();" checked>
				<input type="hidden" class="readOnly percepcionesgravables" id="totalg_'.$rowp['cve'].'" name="totalg['.$rowp['cve'].']" valor="'.round($rowp['totalg'],2).'" value="'.round($rowp['totalg'],2).'" readOnly></td></tr>';
			}
		}
		elseif($rowp['cve']==31){
			if($_POST['reg']==0 || $Nomina['uuid']==''){
				$res=mysql_query("SELECT (DATEDIFF('".$_POST['fecha_baja']."','".$_POST['fecha_alta']."')+1) as dias_recorridos");
				$row=mysql_fetch_array($res);
				$anios_trabajados = round($row['dias_recorridos']/365,2);
				if($anios_trabajados>=15){
					$rowp['dias']=round(20*$anios_trabajados,2);
					$rowp['montoxdia']=$minimo*2;
					$rowp['total']=round($rowp['montoxdia']*$rowp['dias'],2);
					$rowp['totalg']=round($rowp['montoxdia']*$rowp['dias'],2)-round($minimo*90*round($anios_trabajados,0),2);
					if($rowp['totalg']<0) $rowp['totalg']=0;
				}
			}
			if($rowp['total']>0){
				echo '<input type="hidden" name="cvepercepcion['.$rowp['cve'].']" value="'.$rowp['cvepercepcion'].'">';
				echo '<tr><th align="left">'.$rowp['nombre'].'</th>';
				echo '<td align="center"><input type="text" class="readOnly" id="dias_'.$rowp['cve'].'" name="dias['.$rowp['cve'].']" valor="'.$rowp['dias'].'" value="'.$rowp['dias'].'" readOnly><input type="hidden" class="readOnly" id="montoxdia_'.$rowp['cve'].'" name="montoxdia['.$rowp['cve'].']" valor="'.$rowp['montoxdia'].'" value="'.$rowp['montoxdia'].'" readOnly></td>';
				echo '<td align="left"><input type="text" class="readOnly percepciones" id="total_'.$rowp['cve'].'" name="total['.$rowp['cve'].']" valor="'.round($rowp['total'],2).'" value="'.round($rowp['total'],2).'" readOnly>
				<input type="checkbox" onClick="
				if(this.checked){
					document.getElementById(\'montoxdia_'.$rowp['cve'].'\').value=\''.$rowp['montoxdia'].'\';
					document.getElementById(\'dias_'.$rowp['cve'].'\').value=\''.$rowp['dias'].'\';
					document.getElementById(\'total_'.$rowp['cve'].'\').value=\''.$rowp['total'].'\';
					document.getElementById(\'totalg_'.$rowp['cve'].'\').value=\''.$rowp['totalg'].'\';
				}
				else{
					document.getElementById(\'montoxdia_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'dias_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'total_'.$rowp['cve'].'\').value=\'\';
					document.getElementById(\'totalg_'.$rowp['cve'].'\').value=\'\';
				}
				totales();" checked>
				<input type="hidden" class="readOnly percepcionesgravables" id="totalg_'.$rowp['cve'].'" name="totalg['.$rowp['cve'].']" valor="'.round($rowp['totalg'],2).'" value="'.round($rowp['totalg'],2).'" readOnly></td></tr>';
			}
		}
		else{
			if($_POST['reg']==0){
				if($rowp['cve']==18){
					$rowp['totalg']=$rowp['total'];
				}
			}
			if($rowp['cve']==18){
				$porcentaje=1;
			}
			else{
				$porcentaje=0;
			}
			echo '<input type="hidden" name="cvepercepcion['.$rowp['cve'].']" value="'.$rowp['cvepercepcion'].'">';
			echo '<tr><th align="left">'.$rowp['nombre'].'</th>';
			echo '<td align="center">&nbsp;<input type="hidden" class="readOnly" id="dias_'.$rowp['cve'].'" name="dias['.$rowp['cve'].']" value="'.$rowp['dias'].'" readOnly><input type="hidden" class="readOnly" id="montoxdia_'.$rowp['cve'].'" name="montoxdia['.$rowp['cve'].']" value="'.$rowp['montoxdia'].'" readOnly></td>';
			echo '<td align="left"><input type="text" class="textField percepciones" id="total_'.$rowp['cve'].'" name="total['.$rowp['cve'].']" value="'.round($rowp['total'],2).'" onKeyUp="document.forma.totalg_'.$rowp['cve'].'.value=this.value*'.$porcentaje.';totales();"><input type="hidden" class="readOnly percepcionesgravables" id="totalg_'.$rowp['cve'].'" name="totalg['.$rowp['cve'].']" value="'.round($rowp['totalg'],2).'" readOnly></td></tr>';
		}
		$total_percepciones += $rowp['total'];
		$total_percepcionesg += $rowp['totalg'];
	}
	echo '<tr><th align="left">Total Percepciones</th><td>&nbsp;</td><td><input type="text" class="readOnly" id="totalpercepciones" name="totalpercepciones" value="'.round($total_percepciones,2).'" readOnly><input type="hidden" class="readOnly" id="totalpercepcionesg" name="totalpercepcionesg" value="'.round($total_percepcionesg,2).'" readOnly></td></tr>';
				
	echo '<tr><td colspan="2" class="tableEnc">Deducciones</td></tr>';
	$total_deducciones = 0;
	$resp=mysql_query("SELECT a.cve,a.nombre,a.tipo_captura,a.salarios_minimos,a.tipo_monto,a.monto_porcentaje,
					b.cve as cvededuccion, b.dias, b.montoxdia, b.total,b.totalg FROM cat_deducciones a 
					LEFT JOIN personal_nomina_deduccion b ON a.cve = b.deduccion AND b.nomina = '".$Nomina['cve']."'
					WHERE a.empresa IN (0,".$_POST['cveempresa'].") AND a.tipo_nomina=3 ORDER BY a.cve");
	while($rowp=mysql_fetch_array($resp)){
		if($rowp['cve']==10){
			if($_POST['reg']==0 || $Nomina['uuid']==''){
				$rowp['total']=round(montoisr(($Personal['salario_integrado']*30.4),$tipo_nomina)/($Personal['salario_integrado']*30.4)*$total_percepcionesg,2);
			}
			echo '<input type="hidden" name="cvededuccion['.$rowp['cve'].']" value="'.$rowp['cvededuccion'].'">';
			echo '<tr><th align="left">'.$rowp['nombre'].'</th>';
			echo '<td align="center">&nbsp;<input type="hidden" class="readOnly" id="diasd_'.$rowp['cve'].'" name="diasd['.$rowp['cve'].']" value="'.$rowp['dias'].'" readOnly><input type="hidden" class="readOnly" id="montoxdiad_'.$rowp['cve'].'" name="montoxdiad['.$rowp['cve'].']" value="'.$rowp['montoxdia'].'" readOnly></td>';
			echo '<td align="left"><input type="text" class="readOnly deducciones" id="totald_'.$rowp['cve'].'" name="totald['.$rowp['cve'].']" value="'.round($rowp['total'],2).'" readOnly></td></tr>';
		}
		else{
			echo '<input type="hidden" class="textField" id="diasd_'.$rowp['cve'].'" name="diasd['.$rowp['cve'].']" value="'.$rowp['dias'].'"><input type="hidden" class="readOnly" id="montoxdiad_'.$rowp['cve'].'" name="montoxdiad['.$rowp['cve'].']" value="'.$rowp['montoxdia'].'" readOnly>';
			if($rowp['cve']==4)
				echo '<tr><th align="left">'.$rowp['nombre'].'</th><td align="center">&nbsp;</td><td><input type="text" class="readOnly deducciones" id="totald_'.$rowp['cve'].'" name="totald['.$rowp['cve'].']" value="'.round($rowp['total'],2).'" readOnly></td></tr>';
			else
				echo '<tr><th align="left">'.$rowp['nombre'].'</th><td align="center">&nbsp;</td><td><input type="text" class="textField deducciones" id="totald_'.$rowp['cve'].'" name="totald['.$rowp['cve'].']" value="'.round($rowp['total'],2).'" onKeyUp="totales()"></td></tr>';
		}
		$total_deducciones += $rowp['total'];
	}
	echo '<tr><th align="left">Total Deducciones</th><td>&nbsp;</td><td><input type="text" class="readOnly" id="totaldeducciones" name="totaldeducciones" value="'.round($total_deducciones,2).'" readOnly></td></tr>';
	
	echo '<tr><th align="left">Total a Pagar</th><td>&nbsp;</td><td><input type="text" class="readOnly" id="total" name="total_a_pagar" value="'.round($total_percepciones-$total_deducciones,2).'" readOnly></td></tr>';
	echo '</table>';
	
	echo '<script>
			function montoisr(tot){
				var monto = 0;
				';
				$res1 = mysql_query("SELECT * FROM nomina WHERE tipo_nomina = '$tipo_nomina' ORDER BY limite_inferior DESC");
				$i=0;
				while($row1=mysql_fetch_array($res1)){
					if($i==0)
						echo 'if';
					else
						echo 'else if';
					echo '('.$row1['limite_inferior'].'<=(tot/1)){
						monto2 = (tot/1)-'.$row1['limite_inferior'].';
						monto3 = monto2*'.$row1['porcentaje'].'/100;
						monto4 = monto3.toFixed(2);
						monto = (monto4/1)+('.$row1['cuota'].'/1);
					}
					';
					$i++;
				}
			echo '	
				return monto.toFixed(2);
			}
			
			function montosubsidio(tot){
				var monto = 0;
				';
				$res1 = mysql_query("SELECT * FROM nomina_subsidio WHERE tipo_nomina = '$tipo_nomina' ORDER BY ingreso_min DESC");
				$i=0;
				while($row1=mysql_fetch_array($res1)){
					if($i==0)
						echo 'if';
					else
						echo 'else if';
					echo '('.$row1['ingreso_min'].'<=(tot/1)){
						monto = '.$row1['subsidio'].';
					}
					';
					$i++;
				}
			echo '	
				return monto.toFixed(2);
			}
			
			function calcular(cve){
				if(cve!=1){
					total = (document.getElementById("dias_"+cve).value*document.getElementById("montoxdia_"+cve).value);
					document.getElementById("total_"+cve).value = total.toFixed(2);
				}
				if(cve==1 || cve==2){
					total = (document.getElementById("total_1").value*1) + (document.getElementById("total_2").value*1) - (document.getElementById("totald_1").value*1);
					var total2 = montosubsidio(total) - montoisr(total);
					if(total2>=0){
						document.getElementById("total_"+3).value = total2.toFixed(2);
						document.getElementById("totald_"+3).value = 0;
					}
					else{
						total2 = total2*(-1);
						document.getElementById("total_"+3).value = 0;
						document.getElementById("totald_"+3).value = total2.toFixed(2);
					}
					total = calcular_imss(document.getElementById("montoxdiad_2").value) * ((document.getElementById("dias_1").value/1)-(document.getElementById("diasd_1").value/1));
					document.getElementById("totald_"+2).value = total.toFixed(2);
				}
				totales();
			}
			
			function calculard(cve){
				total = (document.getElementById("diasd_"+cve).value*document.getElementById("montoxdiad_"+cve).value);
				document.getElementById("totald_"+cve).value = total.toFixed(2);
				if(cve==1){
					total = (document.getElementById("total_1").value*1) + (document.getElementById("total_2").value*1) - (document.getElementById("totald_1").value*1);
					var total2 = montosubsidio(total) - montoisr(total);
					if(total2>=0){
						document.getElementById("total_"+3).value = total2.toFixed(2);
						document.getElementById("totald_"+3).value = 0;
					}
					else{
						total2 = total2*(-1);
						document.getElementById("total_"+3).value = 0;
						document.getElementById("totald_"+3).value = total2.toFixed(2);
					}
					total = calcular_imss(document.getElementById("montoxdiad_2").value) * ((document.getElementById("dias_1").value/1)-(document.getElementById("diasd_1").value/1));
					document.getElementById("totald_"+2).value = total.toFixed(2);
				}
				totales();
			}
			
			function totales(){
		
				var total = 0;
				$(".percepciones").each(function(){
					total += this.value/1;
				});
				document.forma.totalpercepciones.value=total.toFixed(2);
				
				total = 0;
				$(".percepcionesgravables").each(function(){
					total += this.value/1;
				});
				document.forma.totalpercepcionesg.value=total.toFixed(2);
				total = montoisr('.($Personal['salario_base']*30.4).')/'.($Personal['salario_base']*30.4).'*document.forma.totalpercepcionesg.value;
				document.getElementById("totald_10").value=total.toFixed(2);
				total = 0;
				$(".deducciones").each(function(){
					total += this.value/1;
				});
				document.forma.totaldeducciones.value=total.toFixed(2);
				
				total = document.forma.totalpercepciones.value - document.forma.totaldeducciones.value;
				
				document.forma.total_a_pagar.value = total.toFixed(2);
			}
			
			//calcular(1);
			//calcular(2);
			//calculard(1);
	</script>';
}

if($_POST['cmd']==0){
	echo '<div id="dialogdetalle" style="display:none"></div>';
	echo '<div id="dialog" style="display:none">
	<table>
	<tr><td class="tableEnc">Seleccione Empleado para Finiquito</td></tr>
	</table>
	<table width="100%">
		<tr><th align="left">Buscar: </th><td><input type="text" class="textField" id="buscador" name="buscador" value=""></td></tr>
		<tr><th align="left">Empleado: </th><td><input type="text" class="readOnly" id="nombrepersonal" name="nombrepersonal" size="50" value="" readOnly></td></tr>
		<tr><td>Fecha Alta</td><td><input type="text" id="dfecha_alta" value="" class="readOnly" size="12" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.getElementById(\'dfecha_alta\'),\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>
		<tr><td>Fecha Baja</td><td><input type="text" id="dfecha_baja" value="" class="readOnly" size="12" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.getElementById(\'dfecha_baja\'),\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>
	
	</table>
	</div>';
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>';
	if($_POST['plazausuario']>0){
		echo '<td><a href="#" onclick="nuevofiniquito();"><img src="images/nuevo.gif" border="0"></a>&nbsp;&nbsp;Nuevo&nbsp;&nbsp;</td>';
	}
	echo '	<td><a href="#" onclick="atcr(\'finiquito.php\',\'_blank\',\'50\',\'\');"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Excel Detallado&nbsp;&nbsp;</td>
			<!--<td><a href="#" onclick="atcr(\'imp_finiquito.php\',\'_blank\',\'\',\'SI\');"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir Listado&nbsp;&nbsp;</td>-->
			<!--<td><a href="#" onclick="atcr(\'imp_finiquito.php\',\'_blank\',\'1\',\'\');"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir Recibos&nbsp;&nbsp;</td>-->';
	if(nivelUsuario()>1){
		echo '<!--<td><a href="#" onclick="atcr(\'finiquito.php\',\'\',\'3\',\'\');"><img src="images/guardar.gif" border="0"></a>&nbsp;&nbsp;Guardar Nomina&nbsp;&nbsp;</td>-->';
		echo '<td><a href="#" onclick="if(!$(\'.chkpersonal\').is(\':checked\')) alert(\'Necesita seleccionar al menos un finiquito\'); else atcr(\'finiquito.php\',\'\',\'6\',\'\');"><img src="images/guardar.gif" border="0"></a>&nbsp;&nbsp;Timbrar Finiquito&nbsp;&nbsp;</td>';
		if(nivelUsuario()>2){
			echo '<td><a href="#" onclick="if(confirm(\'Esta seguro de cancelar los timbres\')){ if(!$(\'.chkpersonal\').is(\':checked\')) alert(\'Necesita seleccionar al menos un finiquito\'); else atcr(\'finiquito.php\',\'\',\'7\',\'\');}"><img src="images/validono.gif" border="0"></a>&nbsp;&nbsp;Cancelar Timbrado de Finiquito&nbsp;&nbsp;</td>';
		}
	}
	echo '</tr>';
	echo '</table>';
	echo '<input type="hidden" name="personal" id="personal" value="">';
	echo '<input type="hidden" name="fecha_alta" id="fecha_alta" value="">';
	echo '<input type="hidden" name="fecha_baja" id="fecha_baja" value="">';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.fechaLocal().'" class="readOnly" size="12" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="readOnly" size="12" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	/*if(count($array_plaza)>1){
		echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" class="textField"><option value="0">---Seleccione---</option>';
		foreach($array_plaza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
	}
	else{
		foreach($array_plaza as $k=>$v) echo '<input type="hidden" name="plaza" id="plaza" value="'.$k.'">';
	}*/
	if($_POST['plazausuario']>0){
		echo '<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
	}
	elseif(count($array_plaza)>1){
		echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" class="textField"><option value="0">---Seleccione---</option>';
		foreach($array_plaza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.' '.$array_plazanombre[$k].'</option>';
		}
		echo '</select></td></tr>';
	}
	else{
		foreach($array_plaza as $k=>$v) echo '<input type="hidden" name="plaza" id="plaza" value="'.$k.'">';
	}
	echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" class="textField"></td></tr>'; 
	echo '</table>';
	echo '<br>';
	//Listado
	echo '<div id="Resultados">';
	echo '</div>';
	echo '<script>
	
		function buscarRegistros(ordenamiento,orden)
		{
			document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
			objeto=crearObjeto();
			if (objeto.readyState != 0) {
				alert("Error: El Navegador no soporta AJAX");
			} else {
				objeto.open("POST","finiquito.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=1&plaza="+document.getElementById("plaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&nombre="+document.getElementById("nombre").value+"&cveempresa="+document.getElementById("cveempresa").value+"&cvemenu="+document.getElementById("cvemenu").value+"&cveusuario="+document.getElementById("cveusuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
				objeto.onreadystatechange = function()
				{
					if (objeto.readyState==4)
					{document.getElementById("Resultados").innerHTML = objeto.responseText;}
				}
			}
			document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
		}
		
		//function mostrarDetalle(
		//Funcion para navegacion de Registros. 20 por pagina.
		function moverPagina(x) {
			document.getElementById("numeroPagina").value = x;
			buscarRegistros();
		}
		
		var ac_config = {
			source: "finiquito.php?ajax=3&plaza='.$_POST['plazausuario'].'",
			select: function(event, ui){
				$("#personal").val(ui.item.cve);
				$("#nombrepersonal").val(ui.item.nombre);
			},
			minLength:3
		};
		$("#buscador").autocomplete(ac_config);
		
		function nuevofiniquito(cvenomina){
			$("#dialog").dialog("open");
		}
		
		$("#dialog").dialog({ 
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 600,
			height: 250,
			autoResize: true,
			position: "center",
			beforeClose: function( event, ui ) {
				document.forma.personal.value="";
				$("#nombrepersonal").val("");
			},
			buttons: {
				"Aceptar": function(){ 
					if(document.forma.personal.value==""){
						alert("Necesita seleccionar el empleado");
					}
					else if(document.getElementById("dfecha_alta").value==""){
						alert("Necesita seleccionar la fecha de alta");
					}
					else if(document.getElementById("dfecha_baja").value==""){
						alert("Necesita seleccionar la fecha de baja");
					}
					else{
						document.forma.fecha_alta.value=document.getElementById("dfecha_alta").value;
						document.forma.fecha_baja.value=document.getElementById("dfecha_baja").value;
						atcr("finiquito.php","",1,0);
					}
				},
				"Cerrar": function(){ 
					document.forma.personal.value="";
					document.getElementById("dfecha_alta").value="";
					document.getElementById("dfecha_baja").value="";
					$("#nombrepersonal").val("");
					$(this).dialog("close"); 
				}
			},
		}); 
		
		$("#dialogdetalle").dialog({ 
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 600,
			height: 250,
			autoResize: true,
			position: "center",
			buttons: {
				"Cerrar": function(){ 
					$(this).dialog("close"); 
				}
			},
		}); 
		
		function mostrarDetalle(cvenomina){
			objeto=crearObjeto();
			if (objeto.readyState != 0) {
				alert("Error: El Navegador no soporta AJAX");
			} else {
				objeto.open("POST","finiquito.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=2&cvenomina="+cvenomina);
				objeto.onreadystatechange = function()
				{
					if (objeto.readyState==4)
					{
						$("#dialogdetalle").html(objeto.responseText);
						$("#dialogdetalle").dialog("open");
					}
				}
			}
		}
	</script>';
}

bottom();
?>
