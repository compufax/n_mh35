<?php
$zona_horaria = file_get_contents('https://pendientes.hgaribay.com/zonahoraria.txt');
date_default_timezone_set ($zona_horaria);
eval(file_get_contents('/var/www/config.cfg'));
//Conexion con la base
if (!$MySQL=@mysql_connect($DB_HOST, 'mh35', 'bAllenA#66#')) {
	$t=time();
	while (time()<$t+5) {}
	if (!$MySQL=@mysql_connect($DB_HOST, 'mh35', 'bAllenA#66#')) {
		$t=time();
		while (time()<$t+10) {}
		if (!$MySQL=@mysql_connect($DB_HOST, 'mh35', 'bAllenA#66#')) {
		echo '<br><br><br><h3 align=center">Hay problemas de comunicaci&oacute;n con la Base de datos.</h3>';
		echo '<h4>Por favor intente mas tarde.-</h4>';
		exit;
		}
	}
}
$base='mh35';
mysql_select_db($base);
$now = new DateTime();
$mins = $now->getOffset()/60;
$sgn = ($mins < 0 ? -1 : 1);
$mins = abs($mins);
$hrs = floor($mins/60);
$mins -= $hrs*60;
$offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
mysql_query("SET time_zone='$offset'");


function genera_html($plaza, $fecha, $tipo = 0){
	$Plaza = mysql_fetch_array(mysql_query("SELECT * FROM datosempresas WHERE plaza='".$plaza."'"));
	$horarios = array();
	$hora = $Plaza['horainicio'].':00';
	while($hora<$Plaza['horafin'].':00'){
		$horarios[$hora] = $hora;
		$hora = date( "H:i:s" , strtotime ( "+ ".$Plaza['minutos']." minute" , strtotime($hora) ) );
	}
	$max=$Plaza['numero_lineas'];
	$totalcitas=0;
	$res = mysql_query("SELECT hora,COUNT(cve) FROM call_citas WHERE plaza='$plaza' AND fecha='$fecha' AND estatus!='C' GROUP BY hora ORDER BY hora");
	while($row = mysql_fetch_array($res)){
		$horarios[$row[0]]=$row[0];
		if($max<$row[1]) $max=$row[1];
		$totalcitas+=$row[1];
	}
	
	$html = '';
	
	if($tipo==2){
		$html .= '<h1>Reporte de citas del Centro '.$Plaza['nombre_callcenter'].' del dia '.$fecha.'</h1>';
	}
	
	$html .= '<h1>Total de Citas: '.$totalcitas.'</h1>';
	
	if($tipo!=0) $html .= '<table width="100%" border="1">';
	else $html .= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$html .= '<tr bgcolor="#E9F2F8"><th>Horario</th>';
	for($i=1;$i<=$max;$i++){
		$html .= '<th>';
		if($i<=$Plaza['numero_lineas']) $html .= 'Linea '.$i;
		else $html .= 'Extra';
		$html .= '</th>';
	}
	$html .= '</tr>';
	ksort($horarios);
	foreach($horarios as $k=>$v){
		//$html .= '<tr>';
		if($tipo!=0) $html .= '<tr>';
		else $html .= rowb(false, '2');
		//$html .= '<th bgcolor="#E9F2F8">'.$k.'</th>';
		if($tipo!=0) $html .= '<td align=center"><b>'.$k.'</b></td>';
		else $html .= '<th style="background-color: #E9F2F8;">'.$k.'</th>';
		$res = mysql_query("SELECT placa,cve,nombre FROM call_citas WHERE plaza='$plaza' AND fecha='$fecha' AND hora='$k' AND estatus!='C' ORDER BY cve");
		for($i=1;$i<=$max;$i++){
			$row = mysql_fetch_array($res);
			$html .= '<td align=center>'.htmlentities(utf8_encode(trim($row[0]))).'<br>'.$row[1].'<br>'.htmlentities(utf8_encode($row[2])).'</td>';
		}
		$html .= '</tr>';
	}
	$html .= '</table>';
	return $html;
}

function saldo_timbres($plaza)
{

	$res = mysql_query("SELECT SUM(IF(estatus='C',2,1)) FROM facturas WHERE plaza = '$plaza' AND fecha>='2017-08-01' AND respuesta1 != ''");
	$row = mysql_fetch_array($res);
	$res2 = mysql_query("SELECT SUM(IF(estatus='C',2,1)) FROM notascredito WHERE plaza = '$plaza' AND fecha>='2017-08-01' AND respuesta1 != ''");
	$row2 = mysql_fetch_array($res2);
	$res1 = mysql_query("SELECT SUM(cantidad) FROM compra_timbres WHERE plaza='$plaza' AND estatus='P'");
	$row1 = mysql_fetch_array($res1);
	$saldo = $row1[0]-$row[0]-$row2[0];
	return $saldo;
}

function tiene_timbres($plaza)
{
	$cantidades = array(50, 45, 40, 35, 30, 25, 20, 15, 10, 5, 0);
	$res = mysql_query("SELECT validar_timbres FROM plazas WHERE cve = '$plaza'");
	$row = mysql_fetch_array($res);
	if($row[0] == 1)
	{
		$saldo = saldo_timbres($plaza);
		if(in_array($saldo, $cantidades)) enviar_correo_timbres();
		if($saldo <= 0)
		{
			return false;
		}
	}
	return true;
}

function validar_timbres($plaza){
	$resultado = array('seguir' => false, 'cvecompra' => 0);
	$resultado['seguir'] = tiene_timbres($plaza);
	return $resultado;
	$res = mysql_query("SELECT validar_timbres FROM plazas WHERE cve='".$plaza."'");
	$row = mysql_fetch_array($res);
	if($row[0] == 0){
		$resultado['seguir'] = true;
	}
	else{
		$res = mysql_query("SELECT cve FROM compra_timbres WHERE plaza = '".$plaza."' AND estatus!='C' AND cantidad > usados ORDER BY cve LIMIT 1");
		if($row = mysql_fetch_array($res)){
			$resultado['seguir'] = true;
			$resultado['cvecompra'] = $row[0];
			mysql_query("UPDATE compra_timbres SET usados = usados+1 WHERE cve='".$row[0]."'");
		}
		else{
			$resultado['seguir'] = false;
		}
	}
	return $resultado;

}

function desbloquear_timbre($plaza, $cvecompra){
	mysql_query("UPDATE compra_timbres SET usados = usados-1 WHERE plaza = '".$plaza."' AND cve='".$cvecompra."'");
}


function enviar_correo_timbres(){
	$res = mysql_query("SELECT correotimbres FROM usuarios WHERE cve=1");
	$row = mysql_fetch_array($res);
	$emailenvio = $row[0];
	if($emailenvio!=""){
		require_once('../fpdf153/fpdf.php');
		class FPDF2 extends PDF_MC_Table {
			//Pie de página
			function Footer(){
				//Posición: a 1,5 cm del final
				$this->SetY(-15);
				//Arial bold 12
				$this->SetFont('Arial','B',11);
				//Número de página
				$this->Cell(0,10,'Página '.$this->PageNo().' de {nb}',0,0,'C');
			}
		}

		$pdf=new FPDF2('P','mm','LETTER');
		$pdf->AliasNbPages();
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',16);
		$pdf->SetY(23);
		$pdf->Cell(190,5,"MH35",0,0,'C');
		$pdf->Ln();
		$tit='';
		$pdf->MultiCell(200,5,'REPORTE DE EXISTENCIA DE TIMBRES',0,'C');
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(150,4,'Centro',0,0,'C',0);
		$pdf->Cell(30,4,'Timbres',0,0,'C',0);
		$pdf->Ln();		
		$pdf->SetFont('Arial','',10);
		$pdf->SetWidths(array(150,30));
		$res = mysql_query("SELECT * FROM plazas where estatus='A' AND timbres_exis = 1");
		while($row=mysql_fetch_array($res)){
			$renglon=array();
			$renglon[] = $row['numero'].' '.$row['nombre'];
			$renglon[] = saldo_timbres($row['cve']);
			$pdf->Row($renglon);
		}
		$nombre = "../cfdi/rep_existencia".date('Y_m_d_H_i_s');
		$pdf->Output($nombre.".pdf","F");	
		require_once('../phpmailer/class.phpmailer.php');
	
		$mail = obtener_mail();   
		$mail->FromName = "MH35";
		$mail->Subject = "Reporte de Existencia de Timbres MH35";
		$mail->Body = "Reporte";
		$correos = explode(",",trim($emailenvio));
		foreach($correos as $correo)
			$mail->AddAddress(trim($correo));
		$mail->AddAttachment($nombre.".pdf", "Reporte.pdf");
		$mail->Send();
		@unlink($nombre.".pdf");
	}	
}

function genera_arreglo_facturacion($plaza, $id, $tipo){
	global $array_tipo_pagos, $array_tipo_pagosat;
	return genera_arreglo_facturacion40($plaza, $id, $tipo);
	if($tipo == 'I'){
		$tabla = 'facturas';
	}
	else{
		$tabla = 'notascredito';
	}

	$Plaza = mysql_fetch_array(mysql_query("SELECT regimensat FROM plazas WHERE cve='".$plaza."'"));
	$documento = array();
	$res = mysql_query("SELECT * FROM $tabla WHERE plaza='".$plaza."' AND cve='".$id."'");
	$row = mysql_fetch_array($res);

	if($row['idexterno']==0){
		mysql_query("INSERT INTO idexterno_wstimbre (cve) VALUES ('')");
		$row['idexterno'] = mysql_insert_id();
		mysql_query("UPDATE $tabla SET idexterno = '".$row['idexterno']."' WHERE plaza='".$plaza."' AND cve='".$id."'");
	}
	
	$documento=array();
	$documento['idexterno'] = $row['idexterno'];
	$documento['serie']=$row['serie'];
	$documento['folio']=$row['folio'];
	$documento['fecha']=$row['fecha'].' '.$row['hora'];
	$documento['metodopago']=($row['forma_pago']==1) ? 'PPD' : 'PUE';
	$documento['regimenfiscal']=$Plaza['regimensat'];
	//$documento['idtipodocumento']=1;
	$documento['tipodocumento']=$tipo;
	$documento['observaciones']=$row['obs'];
	if($row['tipo_relacion'] != ''){
		$documento['cfdirelacionados'] = array(
			'TipoRelacion' => $row['tipo_relacion'],
			'UUIDS' => $row['uuidsrelacionados']
		);
	}
	if($row['descripciontipopago']==1)
		$documento['formapago']=$array_tipo_pagos[$row['tipo_pago']];
	else
		$documento['formapago']=$array_tipo_pagosat[$row['tipo_pago']];
	$res1 = mysql_query("SELECT * FROM clientes WHERE cve='".$row['cliente']."'");
	$row1 = mysql_fetch_array($res1);
	$row1['cve']=0;
	$emailenvio = $row1['email'];
	//if(($row['tipo_pago']==1 || $row['tipo_pago'] == 2 || $row['tipo_pago']==5) && $row1['cuenta_pago']!='')
	//	$documento['numerocuentapago']=$row1['cuenta_pago'];
	$documento['receptor']['codigo']=$row1['cve'];
	$documento['receptor']['rfc']=$row1['rfc'].$row1['homoclave'];
	$documento['receptor']['nombre']=$row1['nombre'];
	$documento['receptor']['calle']=$row1['calle'];
	$documento['receptor']['num_ext']=$row1['numexterior'];
	$documento['receptor']['num_int']=$row1['numinterior'];
	$documento['receptor']['colonia']=$row1['colonia'];
	$documento['receptor']['localidad']=$row1['localidad'];
	$documento['receptor']['municipio']=$row1['municipio'];
	$documento['receptor']['estado']=$row1['estado'];
	//$documento['receptor']['pais']='MEX';
	$documento['receptor']['codigopostal']=$row1['codigopostal'];
	//echo $row1['usocfdi'];
	$documento['receptor']['usodelcomprobante'] = $row1['usocfdi'];
	//Agregamos los conceptos
	$res2 = mysql_query("SELECT * FROM {$tabla}mov WHERE plaza='".$plaza."' AND cvefact='".$id."'");
	
	$i=0;
	$partidas = mysql_num_rows($res2);
	$saldoiva = $row['iva'];
	while($row2 = mysql_fetch_array($res2)){
		if(abs($row2['importe']-($row2['cantidad'] * $row2['precio'])) >= 1)
			$row2['importe'] = round($row2['cantidad'] * $row2['precio'],2);
		$documento['conceptos'][$i]['clave']=$row2['claveprodsat'];
		$documento['conceptos'][$i]['codigounidad']=$row2['claveunidadsat'];
		$documento['conceptos'][$i]['cantidad']=$row2['cantidad'];
		$documento['conceptos'][$i]['unidad']=$row2['unidad'];
		$documento['conceptos'][$i]['descripcion']=iconv('UTF-8','ISO-8859-1',$row2['concepto']);
		$documento['conceptos'][$i]['valorUnitario']=$row2['precio'];
		$documento['conceptos'][$i]['importe']=$row2['importe'];
		//$documento['conceptos'][$i]['importe_iva']=$row2['importe_iva'];
		if($row2['importe_iva'] > 0){
			if(($i+1)==$partidas) $row2['importe_iva'] = $saldoiva;
			$documento['conceptos'][$i]['impuestostrasladados'][] = array(
				'impuesto' => '002',
				'base' => $row2['importe'],
				'factor' => 'Tasa',
				'tasaocuota' => 0.16,
				'importe' => $row2['importe_iva']
			);
			$saldoiva-=$row2['importe_iva'];
		}
		if($row2['retiene_iva'] > 0 || $row2['retiene_isr'] > 0){
			if($row2['retiene_iva'] > 0 ){
				$documento['conceptos'][$i]['impuestosretenidos'][] = array(
					'impuesto' => '002',
					'base' => $row2['importe'],
					'factor' => 'Tasa',
					'tasaocuota' => $row['por_iva_retenido']/100,
					'importe' => round($row2['importe'] * $row['por_iva_retenido']/100,2)
				);
			}
			if($row2['retiene_isr'] > 0 ){
				$documento['conceptos'][$i]['impuestosretenidos'][] = array(
					'impuesto' => '001',
					'base' => $row2['importe'],
					'factor' => 'Tasa',
					'tasaocuota' => $row['por_isr_retenido']/100,
					'importe' => round($row2['importe'] * $row['por_isr_retenido']/100,2)
				);
			}
		}
		$i++;
	}
	$documento['subtotal']=$row['subtotal'];
	//$documento['descuento']=0;
	//Traslados
	#IVA
	if($row['iva']>0){
		$documento['traslados']['totaltraslados']=$row['iva'];
		$documento['traslados']['impuestostrasladados'][] = array(
			'importe' => $row['iva'],
			'impuesto' => '002',
			'tipofactor' => 'Tasa',
			'tasaocuota' => 0.16
		);
	}

	if($row['iva_retenido'] > 0 || $row['isr_retenido'] > 0){
		$documento['retenciones']['total_retenciones']=$row['iva_retenido'] + $row['isr_retenido'];  
		if($row['iva_retenido'] > 0 ){
			$documento['retenciones']['impuestosretenidos'][] = array('importe'=>$row['iva_retenido'], 'impuesto' => '002');
		}
		if($row['isr_retenido'] > 0 ){
			$documento['retenciones']['impuestosretenidos'][] = array('importe'=>$row['isr_retenido'], 'impuesto'=>'001');  
		}
	}
	
	//total
	$documento['total']=$row['total'];
	//Moneda
	$documento['moneda']     = 'MXN'; //1=pesos, 2=Dolar, 3=Euro
	$documento['tipocambio'] = 1;


	return $documento;
}

function genera_arreglo_facturacion40($plaza, $id, $tipo){
	global $array_tipo_pagos, $array_tipo_pagosat;
	if($tipo == 'I'){
		$tabla = 'facturas';
	}
	else{
		$tabla = 'notascredito';
	}

	$Plaza = mysql_fetch_array(mysql_query("SELECT regimensat FROM plazas WHERE cve='".$plaza."'"));
	$documento = array();
	$res = mysql_query("SELECT * FROM $tabla WHERE plaza='".$plaza."' AND cve='".$id."'");
	$row = mysql_fetch_array($res);

	if($row['idexterno']==0){
		mysql_query("INSERT INTO idexterno_wstimbre (cve) VALUES ('')");
		$row['idexterno'] = mysql_insert_id();
		mysql_query("UPDATE $tabla SET idexterno = '".$row['idexterno']."' WHERE plaza='".$plaza."' AND cve='".$id."'");
	}
	
	$documento=array();
	$documento['idexterno'] = $row['idexterno'];
	$documento['serie']=$row['serie'];
	$documento['folio']=$row['folio'];
	$documento['fecha']=$row['fecha'].' '.$row['hora'];
	$documento['metodopago']=($row['forma_pago']==1) ? 'PPD' : 'PUE';
	$documento['regimenfiscal']=$Plaza['regimensat'];
	//$documento['idtipodocumento']=1;
	$documento['tipodocumento']=$tipo;
	$documento['observaciones']=$row['obs'];
	$documento['exportacion']='01';
	if($row['tipo_relacion'] != ''){
		$documento['cfdirelacionados'] = array(
			'TipoRelacion' => $row['tipo_relacion'],
			'UUIDS' => $row['uuidsrelacionados']
		);
	}

	if($row['periodicidad']!=''){
		$documento['informacionglobal']=array(
			'periodicidad' => $row['periodicidad'],
			'meses' => $row['meses'],
			'anio' => $row['anio']
		);
	}
	if($row['descripciontipopago']==1)
		$documento['formapago']=$array_tipo_pagos[$row['tipo_pago']];
	else
		$documento['formapago']=$array_tipo_pagosat[$row['tipo_pago']];
	$res1 = mysql_query("SELECT * FROM clientes WHERE cve='".$row['cliente']."'");
	$row1 = mysql_fetch_array($res1);
	$row1['cve']=0;
	$emailenvio = $row1['email'];
	//if(($row['tipo_pago']==1 || $row['tipo_pago'] == 2 || $row['tipo_pago']==5) && $row1['cuenta_pago']!='')
	//	$documento['numerocuentapago']=$row1['cuenta_pago'];
	$documento['receptor']['codigo']=$row1['cve'];
	$documento['receptor']['rfc']=$row1['rfc'].$row1['homoclave'];
	$documento['receptor']['nombre']=$row1['nombre'];
	$documento['receptor']['calle']=$row1['calle'];
	$documento['receptor']['num_ext']=$row1['numexterior'];
	$documento['receptor']['num_int']=$row1['numinterior'];
	$documento['receptor']['colonia']=$row1['colonia'];
	$documento['receptor']['localidad']=$row1['localidad'];
	$documento['receptor']['municipio']=$row1['municipio'];
	$documento['receptor']['estado']=$row1['estado'];
	//$documento['receptor']['pais']='MEX';
	$documento['receptor']['codigopostal']=$row1['codigopostal'];
	//echo $row1['usocfdi'];
	$documento['receptor']['usodelcomprobante'] = $row1['usocfdi'];
	$documento['receptor']['regimenfiscal'] = $row1['regimensat'];
	//Agregamos los conceptos
	$res2 = mysql_query("SELECT * FROM {$tabla}mov WHERE plaza='".$plaza."' AND cvefact='".$id."'");
	
	$i=0;
	$partidas = mysql_num_rows($res2);
	$saldoiva = $row['iva'];
	$baseiva=0;
	$totaliva=0;
	while($row2 = mysql_fetch_array($res2)){
		if(abs($row2['importe']-($row2['cantidad'] * $row2['precio'])) >= 1)
			$row2['importe'] = round($row2['cantidad'] * $row2['precio'],2);
		$documento['conceptos'][$i]['clave']=$row2['claveprodsat'];
		$documento['conceptos'][$i]['codigounidad']=$row2['claveunidadsat'];
		$documento['conceptos'][$i]['cantidad']=$row2['cantidad'];
		$documento['conceptos'][$i]['unidad']=$row2['unidad'];
		$documento['conceptos'][$i]['descripcion']=iconv('UTF-8','ISO-8859-1',$row2['concepto']);
		$documento['conceptos'][$i]['valorUnitario']=$row2['precio'];
		$documento['conceptos'][$i]['importe']=$row2['importe'];
		//$documento['conceptos'][$i]['importe_iva']=$row2['importe_iva'];
		if($row2['importe_iva'] > 0){
			if(($i+1)==$partidas) $row2['importe_iva'] = $saldoiva;
			$documento['conceptos'][$i]['impuestostrasladados'][] = array(
				'impuesto' => '002',
				'base' => $row2['importe'],
				'tipofactor' => 'Tasa',
				'tasaocuota' => 0.16,
				'importe' => round($row2['importe_iva'],2)
			);
			$saldoiva-=$row2['importe_iva'];
			$baseiva+=$row2['importe'];
			$totaliva=$row2['importe_iva'];
		}
		if($row2['retiene_iva'] > 0 || $row2['retiene_isr'] > 0){
			if($row2['retiene_iva'] > 0 ){
				$documento['conceptos'][$i]['impuestosretenidos'][] = array(
					'impuesto' => '002',
					'base' => $row2['importe'],
					'factor' => 'Tasa',
					'tasaocuota' => $row['por_iva_retenido']/100,
					'importe' => round($row2['importe'] * $row['por_iva_retenido']/100,2)
				);
			}
			if($row2['retiene_isr'] > 0 ){
				$documento['conceptos'][$i]['impuestosretenidos'][] = array(
					'impuesto' => '001',
					'base' => $row2['importe'],
					'factor' => 'Tasa',
					'tasaocuota' => $row['por_isr_retenido']/100,
					'importe' => round($row2['importe'] * $row['por_isr_retenido']/100,2)
				);
			}
		}
		$i++;
	}
	$documento['subtotal']=$row['subtotal'];
	//$documento['descuento']=0;
	//Traslados
	#IVA
	if($row['iva']>0){
		$documento['traslados']['totaltraslados']=$row['iva'];
		$documento['traslados']['impuestostrasladados'][] = array(
			'importe' => $row['iva'],
			'impuesto' => '002',
			'base' => $baseiva,
			'tipofactor' => 'Tasa',
			'tasaocuota' => 0.16
		);
	}

	if($row['iva_retenido'] > 0 || $row['isr_retenido'] > 0){
		$documento['retenciones']['total_retenciones']=$row['iva_retenido'] + $row['isr_retenido'];  
		if($row['iva_retenido'] > 0 ){
			$documento['retenciones']['impuestosretenidos'][] = array('importe'=>$row['iva_retenido'], 'impuesto' => '002');
		}
		if($row['isr_retenido'] > 0 ){
			$documento['retenciones']['impuestosretenidos'][] = array('importe'=>$row['isr_retenido'], 'impuesto'=>'001');  
		}
	}
	
	//total
	$documento['total']=$row['total'];
	//Moneda
	$documento['moneda']     = 'MXN'; //1=pesos, 2=Dolar, 3=Euro
	$documento['tipocambio'] = 1;


	return $documento;
}

class EnvioEmail{
   public $FromName = '';
   public $Subject = '';
   public $Body = '';
   public $Correos = array();
   public $Archivos = array();
   public $html = false;

   public function AddAddress($correo) {
      $this->Correos[] = $correo;
   }

   public function AddAttachment($file, $name) {
   		$datos_nombre = explode('.', $name);
   		$type = end($datos_nombre);
   		$existe = false;
   		if (file_exists($file)) {
   			$file = file_get_contents($file);
   			$existe = true;
   		}
   		elseif(file_exists('../'.$file)){
   			$file = file_get_contents('../'.$file);
   			$existe = true;
   		}

   		if ($existe) {
    		$this->Archivos[] = array('file'=>base64_encode($file), 'type'=>$type, 'name' => $name);
    	}
   }

   public function isHTML($ishtml = true) {
   		$this->html = $ishtml;
    }

   public function Send() {
   	$campotexto = 'text';
   	if($this->html){
   		$campotexto = 'html';
   	}
      $body = array(
         'from' => $this->FromName,
         'subject' => $this->Subject,
         $campotexto => $this->Body,
         'to' => $this->Correos,
         'attachments' => $this->Archivos
      );
      $url = 'https://service.2ai.io/api/correo';

      //create a new cURL resource
      $ch = curl_init($url);

      //setup request to send json via POST
      $payload = http_build_query($body);

      //echo $payload;
      //attach encoded JSON string to the POST fields
      curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

      //set the content type to application/json
      //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','key:AIzaSyB6bHoe8WXBvR2KToHxLv6QixSXrUdN_as'));

      curl_setopt($ch, CURLOPT_HTTPHEADER, array('key:AIzaSyB6bHoe8WXBvR2KToHxLv6QixSXrUdN_as'));

      //return response instead of outputting
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      //execute the POST request
      $resultado = curl_exec($ch);
      //close cURL resource
      $resultado = json_decode($resultado, true);
      curl_close($ch);
      return $resultado['status'];
   }
}

function obtener_mail(){
   $mail = new EnvioEmail();
   return $mail;
}
?>