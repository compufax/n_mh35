<?php
/*echo '<h1>Pagina en mantenimiento<br>Disculpe las molestias</h1>';
exit();*/
if($_POST['recargado'] != 1){
	echo '<form name="forma" id="forma" method="POST" action="facturacion_web.php" enctype="multipart/form-data" class="form-horizontal">
	<input type="hidden" name="recargado" id="recargado" value="1">
	</form>
	<script>document.forma.submit();</script>';
	exit();
}
include("main2_beta.php");
$array_forma_pago=array("PAGO EN UNA SOLA EXHIBICION");
$array_tipo_pago=array(0=>"01 EFECTIVO",2=>"03 TRANSFERENCIA ELECTRONICA DE FONDOS",3=>"01 DEPOSITO",4=>"99 NO ESPECIFICADO",5=>"02 CHEQUE DENOMINATIVO",6=>"99 NO APLICA",7=>"04 CREDITO", 9=>"28 TARJETA DE DEBITO");//,1=>"CHEQUE"
$array_tipo_pagosat=array(0=>"01",2=>"03",3=>"01",4=>"99",5=>"02",6=>"99",7=>"04",8=>"99", 9=>"28");//,1=>"CHEQUE"

if($_POST['cmd'] == 200){
	include("imp_factura.php");
	$datos = explode('|', $_POST['reg']);
	$archivo='factura';
	$postfijo='';
	$nombre='Factura';
	if($datos[2] == 2){
		$archivo='nc';
		$postfijo='nc';
		$nombre='NotaCredito';
	}
	$zip = new ZipArchive();
	if($zip->open("cfdi/zip".$archivo."_".$datos[0]."_".$datos[1].".zip",ZipArchive::CREATE)){
		generaFacturaPdf($datos[0], $datos[1], 0, $datos[2]);
		$zip->addFile("cfdi/comprobantes/".$archivo."_".$datos[0]."_".$datos[1].".pdf",$nombre.".pdf");
		$zip->addFile("cfdi/comprobantes/cfdi".$postfijo."_".$datos[0]."_".$datos[1].".xml",$nombre.".xml");
		$zip->close(); 
	    if(file_exists("cfdi/zip".$archivo."_".$datos[0]."_".$datos[1].".zip")){ 
	        header('Content-type: "application/zip"'); 
	        header('Content-Disposition: attachment; filename="'.$nombre.'_'.$datos[1].'.zip"'); 
	        readfile("cfdi/zip".$archivo."_".$datos[0]."_".$datos[1].".zip"); 
	         
	        unlink("cfdi/zip".$archivo."_".$datos[0]."_".$datos[1].".zip"); 
	        @unlink("cfdi/comprobantes/".$archivo."_".$datos[0]."_".$datos[1].".pdf"); 
	    } 
	    else{
			echo '<h1>Ocurrio un problema al generar el archivo favor de intentarlo de nuevo</h1>';
		}
	}
	else{
		echo '<h1>Ocurrio un problema al generar el archivo favor de intentarlo de nuevo</h1>';
	}
	exit();
}

if($_POST['ajax']==1.1){
	$resultado = array('error' => 0, 'html' => '', 'mensaje' => '', 'generar_factura' => 0);
	$limite30 = date( "Y-m-d" , strtotime ( "+ 30 day" , strtotime(date('Y-m-d')) ) );
	$claves = json_decode($_POST['tickets_capturados'], true);
	if(count($claves) == 0){
		$resultado['error'] = 1;
		$resultado['mensaje'] = 'Debe de capturar al menos un código de facturación';
	}
	else{
		$clavesencontradas = array();
		$fecha='';
		foreach($claves as $datosclaves){
			$clave = $datosclaves['codigo'];
			$res = mysql_query("SELECT * FROM claves_facturacion WHERE cve = '".trim($clave)."'");
			if($row=mysql_fetch_array($res)){
				$encontrado = false;
				if(in_array(trim($clave), $clavesencontradas)){
					$encontrado = true;
				}

				if($encontrado){
					$resultado['error'] = 1;
					$resultado['mensaje'] .= 'El código '.$clave.' esta duplicado'."\n";
				}
				else{
					$res=mysql_query("SELECT a.tipo_pago,a.fecha,a.factura,a.estatus,a.cve,a.plaza,(a.monto+IFNULL(f.recuperacion,0)-IFNULL(e.devolucion,0)) as monto,a.placa,b.nombre as engomado,c.nombre as nomplaza, a.notacredito, d.localidad_id 
					FROM cobro_engomado a 
					LEFT JOIN engomados b ON b.cve = a.engomado 
					INNER JOIN plazas c ON c.cve = a.plaza 
					INNER JOIN datosempresas d ON c.cve = d.plaza
					LEFT JOIN 
						(SELECT ticket, SUM(devolucion) as devolucion FROM devolucion_certificado WHERE plaza = '".$row['plaza']."' AND estatus != 'C' AND ticket='".$row['ticket']."') e ON a.cve = e.ticket
					LEFT JOIN 
						(SELECT ticket, SUM(recuperacion) as recuperacion FROM recuperacion_certificado WHERE plaza = '".$row['plaza']."' AND estatus != 'C' AND ticket='".$row['ticket']."') f ON a.cve = f.ticket
					WHERE a.plaza = '".$row['plaza']."' AND a.cve='".$row['ticket']."'") or die(mysql_error());
					$row = mysql_fetch_array($res);
					if($row['estatus']=='C'){
						$resultado['error'] = 1;
						$resultado['mensaje'] .= 'El ticket del código '.$clave.' esta cancelado'."\n";
					}
					elseif(date('Y') == 2016 && substr($row['fecha'],0,4) == 2015 && 1==2){
						$resultado['error'] = 1;
						$resultado['mensaje'] .= 'No se pueden facturar tickets del 2015'."\n";	
					}
					elseif($fecha!='' && substr($fecha,0,7) != substr($row['fecha'],0,7) && $row['localidad_id'] == 2){
						$resultado['error'] = 1;
						$resultado['mensaje'] .= 'No se pueden facturar tickets de diferente mes en la misma factura'."\n";	
					}
					elseif($row['factura']>0 && $row['notacredito']==0){
						$resultado['html'] .=  '
						<tr><td align="center"><!--<span style="cursor:pointer;" onClick="if(confirm(\'Esta seguro de quitar el ticket?\')) quitar_ticket($(this))"><img src="images/validono.gif"></span>-->';
						$res1=mysql_query("SELECT * FROM facturas WHERE plaza='".$row['plaza']."' AND cve='".$row['factura']."'");
						$row1=mysql_fetch_array($res1);
						if($row1['respuesta1']==""){
							$resultado['html'] .= '<br><b>No Timbrada</b>';
						}
						else{
							$resultado['html'] .=  '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturacion_web.php\',\'_blank\',\'200\',\''.$row1['plaza'].'|'.$row1['cve'].'|1\');"><img src="images/zip_grande.png" border="0" width="30px" height="30px" title="Descargar"></a>';
							if($row1['estatus'] == 'D') $resultado['html'] .= '<br>DEVUELTA';
							//else $resultado['html'] .=  '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de generar la nota de credito de descuento? Se generara por el total de la factura.\'))atcr(\'facturacion_web.php\',\'\',\'3\',\''.$row1['plaza'].'|'.$row1['cve'].'\');"><img src="images/cerrar.gif" border="0" width="15px" height="15px" title="Generar Nota de Credito"></a>';
						}
						$resultado['html'] .=  '</td>
						<td align="left" style="padding: 10px!important">'.$row['nomplaza'].'</td>
						<td align="center" style="padding: 10px!important">(Facturado)<br>'.$row['cve'].'</td>
						<td align="left" style="padding: 10px!important">'.$row['engomado'].'</td>
						<td align="right" style="padding: 10px!important">'.$row['monto'].'';
						$resultado['html'] .=  '</td></tr>';
						if($row['notacredito']>0){
							$resultado['html'] .=  '
							<tr><td align="center"><!--<span style="cursor:pointer;" onClick="if(confirm(\'Esta seguro de quitar el ticket?\')) quitar_ticket($(this))"><img src="images/validono.gif"></span>-->';
							$res1=mysql_query("SELECT * FROM notascredito WHERE plaza='".$row['plaza']."' AND cve='".$row['factura']."'");
							$row1=mysql_fetch_array($res1);
							if($row1['respuesta1']==""){
								$resultado['html'] .= '<br><b>No Timbrada</b>';
							}
							else{
								$resultado['html'] .=  '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturacion_web.php\',\'_blank\',\'200\',\''.$row1['plaza'].'|'.$row1['cve'].'|2\');"><img src="images/zip_grande.png" border="0" width="25px" height="25px" title="Descargar"></a>';
							}
							$resultado['html'] .=  '</td>
							<td align="left" style="padding: 10px!important">'.$row['nomplaza'].'</td>
							<td align="center" style="padding: 10px!important">(Nota Credito)<br>'.$row['cve'].'</td>
							<td align="left" style="padding: 10px!important">'.$row['engomado'].'</td>
							<td align="right" style="padding: 10px!important">'.$row['monto'].'';
							$resultado['html'] .=  '</td></tr>';
						}
					}
					elseif($row['fecha']<'2015-05-01'){
						$resultado['error'] = 1;
						$resultado['mensaje'] .= 'Error en el código '.$clave.', solo se pueden facturar por esta pagina ventas de mayo 2015 en adelante'."\n";
					}
					/*elseif($row['fecha']<date("Y-m").'-01' && $row['localidad_id'] == 2){
						$resultado['error'] = 1;
						$resultado['mensaje'] .= 'Error en el cÃ³digo '.$clave.', solo se pueden facturar ventas del mes en curso, para facturar ventas del mes anterior mandar sus datos al correo de Soporte Puebla: pueblasoporte@gmail.com'."\n";
					}*/
					elseif($row['fecha']>$limite30 && $row['localidad_id'] == 2){
						$resultado['error'] = 1;
						$resultado['mensaje'] .= 'Vencio el limite de facturación del código '.$clave.''."\n";
					}
					/*elseif($row['fecha']<date("Y-m").'-01' && $row['fecha']>=$mesanterior && date("d")>'07' && $row['localidad_id'] == 1){
						$resultado['error'] = 1;
						$resultado['mensaje'] = 'Vencio el limite de facturacion del mes anterior';
					}*/
					elseif($row['tipo_pago'] == 2 || $row['tipo_pago'] == 6){
						$resultado['error'] = 1;
						$resultado['mensaje'] = 'Error en el código '.$clave.', no se pueden facturar pagos a credito o con pago anticipado'."\n";
					}
					/*elseif(($row['plaza']==1 || $row['plaza']==15) && date('Y-m-d')<'2016-05-15'){
						$resultado['error'] = 1;
						$resultado['mensaje'] = 'La facturación de tickets de los centros CU 9006 y CU 9073 será apartir del lunes 15 de mayo por mantenimiento de información'."\n";	
					}*/
					else{
						$clavesencontradas[]=trim($clave);
						$resultado['generar_factura'] = 1;
						$resultado['html'] .=  '
						<tr><td align="center"><!--<span style="cursor:pointer;" onClick="if(confirm(\'Esta seguro de quitar el ticket?\')) quitar_ticket($(this))"><img src="images/validono.gif"></span>--></td>
						<td align="left" style="padding: 10px!important">'.utf8_encode($row['nomplaza']).'</td>
						<td align="center" style="padding: 10px!important">'.$row['cve'].'</td>
						<td align="left" style="padding: 10px!important">'.$row['engomado'].'</td>
						<td align="right" style="padding: 10px!important">'.$row['monto'].'';
						$resultado['html'] .=  '<input type="hidden" name="ticket[]" id="ticket_'.$row['plaza'].'_'.$row['cve'].'" plaza="'.$row['plaza'].'" class="tickets" value="'.$row['cve'].'">
						<input type="hidden" name="plaza[]" id="plaza_'.$row['plaza'].'_'.$row['cve'].'" value="'.$row['plaza'].'">
						<input type="hidden" name="placat[]" id="placat_'.$row['plaza'].'_'.$row['cve'].'" value="'.$row['placa'].'"></td></tr>';
					}
					$fecha = $row['fecha'];
				}
			}
			else{
				$resultado['error'] = 1;
				$resultado['mensaje'] .= 'El código '.$clave.' esta mal capturado'."\n";
			}
		}
	}	
	echo json_encode($resultado);
	exit();
}

if($_POST['ajax']==1.2){
	$resultado = array('error' => 0, 'html' => '', 'mensaje' => '', 'generar_factura' => 0);
	$limite30 = date( "Y-m-d" , strtotime ( "+ 30 day" , strtotime(date('Y-m-d')) ) );
	
	$fecha='';
	$res = mysql_query("SELECT plaza, cve as ticket FROM cobro_engomado WHERE placa = '".$_POST['placa']."' AND cve = '".$_POST['folio']."'");
	if($row=mysql_fetch_array($res)){
		
		$res=mysql_query("SELECT a.tipo_pago,a.tipo_venta,a.fecha,a.factura,a.estatus,a.cve,a.plaza,(a.monto+IFNULL(f.recuperacion,0)-IFNULL(e.devolucion,0)) as monto,a.placa,b.nombre as engomado,c.nombre as nomplaza, a.notacredito, d.localidad_id 
		FROM cobro_engomado a 
		LEFT JOIN engomados b ON b.cve = a.engomado 
		INNER JOIN plazas c ON c.cve = a.plaza 
		INNER JOIN datosempresas d ON c.cve = d.plaza
		LEFT JOIN 
			(SELECT ticket, SUM(devolucion) as devolucion FROM devolucion_certificado WHERE plaza = '".$row['plaza']."' AND estatus != 'C' AND ticket='".$row['ticket']."') e ON a.cve = e.ticket
		LEFT JOIN 
			(SELECT ticket, SUM(recuperacion) as recuperacion FROM recuperacion_certificado WHERE plaza = '".$row['plaza']."' AND estatus != 'C' AND ticket='".$row['ticket']."') f ON a.cve = f.ticket
		WHERE a.plaza = '".$row['plaza']."' AND a.cve='".$row['ticket']."'") or die(mysql_error());
		$row = mysql_fetch_array($res);
		if($row['estatus']=='C'){
			$resultado['error'] = 1;
			$resultado['mensaje'] .= 'El ticket esta cancelado'."\n";
		}
		elseif($row['tipo_venta']==1){
			$resultado['error'] = 1;
			$resultado['mensaje'] .= 'No se pueden facturar intentos'."\n";
		}
		elseif($row['tipo_venta']==2){
			$resultado['error'] = 1;
			$resultado['mensaje'] .= 'No se pueden facturar cortesias'."\n";
		}
		elseif(date('Y') == 2016 && substr($row['fecha'],0,4) == 2015 && 1==2){
			$resultado['error'] = 1;
			$resultado['mensaje'] .= 'No se pueden facturar tickets del 2015'."\n";	
		}
		elseif($row['factura']>0 && $row['notacredito']==0){
			$resultado['html'] .=  '
			<tr><td align="center"><!--<span style="cursor:pointer;" onClick="if(confirm(\'Esta seguro de quitar el ticket?\')) quitar_ticket($(this))"><img src="images/validono.gif"></span>-->';
			$res1=mysql_query("SELECT * FROM facturas WHERE plaza='".$row['plaza']."' AND cve='".$row['factura']."'");
			$row1=mysql_fetch_array($res1);
			if($row1['respuesta1']==""){
				$resultado['html'] .= '<br><b>No Timbrada</b>';
			}
			else{
				$resultado['html'] .=  '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturacion_web.php\',\'_blank\',\'200\',\''.$row1['plaza'].'|'.$row1['cve'].'|1\');"><img src="images/zip_grande.png" border="0" width="30px" height="30px" title="Descargar"></a>';
				if($row1['estatus'] == 'D') $resultado['html'] .= '<br>DEVUELTA';
			}
			$resultado['html'] .=  '</td>
			<td align="left" style="padding: 10px!important">'.$row['nomplaza'].'</td>
			<td align="center" style="padding: 10px!important">(Facturado)<br>'.$row['cve'].'</td>
			<td align="left" style="padding: 10px!important">'.$row['engomado'].'</td>
			<td align="right" style="padding: 10px!important">'.$row['monto'].'';
			$resultado['html'] .=  '</td></tr>';
			if($row['notacredito']>0){
				$resultado['html'] .=  '
				<tr><td align="center"><!--<span style="cursor:pointer;" onClick="if(confirm(\'Esta seguro de quitar el ticket?\')) quitar_ticket($(this))"><img src="images/validono.gif"></span>-->';
				$res1=mysql_query("SELECT * FROM notascredito WHERE plaza='".$row['plaza']."' AND cve='".$row['factura']."'");
				$row1=mysql_fetch_array($res1);
				if($row1['respuesta1']==""){
					$resultado['html'] .= '<br><b>No Timbrada</b>';
				}
				else{
					$resultado['html'] .=  '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturacion_web.php\',\'_blank\',\'200\',\''.$row1['plaza'].'|'.$row1['cve'].'|2\');"><img src="images/zip_grande.png" border="0" width="25px" height="25px" title="Descargar"></a>';
				}
				$resultado['html'] .=  '</td>
				<td align="left" style="padding: 10px!important">'.$row['nomplaza'].'</td>
				<td align="center" style="padding: 10px!important">(Nota Credito)<br>'.$row['cve'].'</td>
				<td align="left" style="padding: 10px!important">'.$row['engomado'].'</td>
				<td align="right" style="padding: 10px!important">'.$row['monto'].'';
				$resultado['html'] .=  '</td></tr>';
			}
		}
		elseif($row['fecha']<'2015-05-01'){
			$resultado['error'] = 1;
			$resultado['mensaje'] .= 'Error, solo se pueden facturar por esta pagina ventas de mayo 2015 en adelante'."\n";
		}
		elseif($row['fecha']>$limite30 && $row['localidad_id'] == 2){
			$resultado['error'] = 1;
			$resultado['mensaje'] .= 'Vencio el limite de facturación'."\n";
		}
		elseif($row['tipo_pago'] == 2 || $row['tipo_pago'] == 6){
			$resultado['error'] = 1;
			$resultado['mensaje'] = 'Error, no se pueden facturar pagos a credito o con pago anticipado'."\n";
		}
		/*elseif(($row['plaza']==1 || $row['plaza']==15) && date('Y-m-d')<'2016-05-15'){
			$resultado['error'] = 1;
			$resultado['mensaje'] = 'La facturación de tickets de los centros CU 9006 y CU 9073 será apartir del lunes 15 de mayo por mantenimiento de información'."\n";	
		}*/
		else{
			$clavesencontradas[]=trim($clave);
			$resultado['generar_factura'] = 1;
			$resultado['html'] .=  '
			<tr><td align="center"><!--<span style="cursor:pointer;" onClick="if(confirm(\'Esta seguro de quitar el ticket?\')) quitar_ticket($(this))"><img src="images/validono.gif"></span>--></td>
			<td align="left" style="padding: 10px!important">'.utf8_encode($row['nomplaza']).'</td>
			<td align="center" style="padding: 10px!important">'.$row['cve'].'</td>
			<td align="left" style="padding: 10px!important">'.$row['engomado'].'</td>
			<td align="right" style="padding: 10px!important">'.$row['monto'].'';
			$resultado['html'] .=  '<input type="hidden" name="ticket[]" id="ticket_'.$row['plaza'].'_'.$row['cve'].'" plaza="'.$row['plaza'].'" class="tickets" value="'.$row['cve'].'">
			<input type="hidden" name="plaza[]" id="plaza_'.$row['plaza'].'_'.$row['cve'].'" value="'.$row['plaza'].'">
			<input type="hidden" name="placat[]" id="placat_'.$row['plaza'].'_'.$row['cve'].'" value="'.$row['placa'].'"></td></tr>';
		}
		$fecha = $row['fecha'];
	}
	else{
		$resultado['error'] = 1;
		$resultado['mensaje'] .= 'Error en la relaci&oacute;n de la placa y folio de ticket'."\n";
	}
	echo json_encode($resultado);
	exit();
}

if($_POST['ajax']==1){
	$resultado = array('error' => 0, 'html' => '', 'htmlf' => '', 'mensaje' => '', 'generar_factura' => 0, 'plaza' => 0, 'factura' => 0);
	$limite30 = date( "Y-m-d" , strtotime ( "-30 day" , strtotime(date('Y-m-d')) ) );
	//$res = mysql_query("SELECT plaza, cve as ticket, tipo_pago FROM cobro_engomado WHERE placa = '".$_POST['placa']."' AND cve = '".$_POST['folio']."'");
	$res = mysql_query("SELECT a.plaza, a.cve as ticket FROM cobro_engomado a INNER JOIN claves_facturacion b ON a.plaza = b.plaza AND a.cve = b.ticket WHERE a.placa = '".$_POST['placa']."' AND a.cve = '".$_POST['folio']."' AND b.cve = '".$_POST['clavefacturacion']."'");
	if($row=mysql_fetch_array($res)){
		$tickets_capturados = json_decode($_POST['tickets_capturados'], true);
		$repetido = false;
		foreach($tickets_capturados as $ticket_capturado)
		{
			if($ticket_capturado['plaza'] == $row['plaza'] && $ticket_capturado['cve'] == $row['ticket']){
				$repetido = true;
			}
		}
		if($repetido){
			$resultado['error'] = 1;
			$resultado['mensaje'] .= 'El ticket ya esta en el listado'."\n";
		}
		else{
			$res=mysql_query("SELECT a.tipo_pago,a.tipo_venta,a.fecha,a.factura,a.estatus,a.cve,a.plaza,(a.monto+IFNULL(f.recuperacion,0)-IFNULL(e.devolucion,0)) as monto,a.placa,b.nombre as engomado,c.nombre as nomplaza, a.notacredito, d.localidad_id, c.bloqueada_sat 
			FROM cobro_engomado a 
			LEFT JOIN engomados b ON b.cve = a.engomado 
			INNER JOIN plazas c ON c.cve = a.plaza 
			INNER JOIN datosempresas d ON c.cve = d.plaza
			LEFT JOIN 
				(SELECT ticket, SUM(devolucion) as devolucion FROM devolucion_certificado WHERE plaza = '".$row['plaza']."' AND estatus != 'C' AND ticket='".$row['ticket']."') e ON a.cve = e.ticket
			LEFT JOIN 
				(SELECT ticket, SUM(recuperacion) as recuperacion FROM recuperacion_certificado WHERE plaza = '".$row['plaza']."' AND estatus != 'C' AND ticket='".$row['ticket']."') f ON a.cve = f.ticket
			WHERE a.plaza = '".$row['plaza']."' AND a.cve='".$row['ticket']."'") or die(mysql_error());
			$row = mysql_fetch_array($res);
			if($row['estatus']=='C'){
				$resultado['error'] = 1;
				$resultado['mensaje'] .= 'El ticket esta cancelado'."\n";
			}
			elseif($row['tipo_venta']==1){
				$resultado['error'] = 1;
				$resultado['mensaje'] .= 'No se pueden facturar intentos'."\n";
			}
			elseif($row['tipo_venta']==2){
				$resultado['error'] = 1;
				$resultado['mensaje'] .= 'No se pueden facturar cortesias'."\n";
			}
			elseif($row['bloqueada_sat']==1){
				$resultado['error'] = 1;
				$resultado['mensaje'] .= 'No se pueden facturar tickets del centro '.utf8_encode($row['nomplaza'])."\n";
			}
			elseif(date('Y') == 2016 && substr($row['fecha'],0,4) == 2015 && 1==2){
				$resultado['error'] = 1;
				$resultado['mensaje'] .= 'No se pueden facturar tickets del 2015'."\n";	
			}
			elseif($row['factura']>0 && $row['notacredito']==0){
				$resultado['error'] = 2;
				$resultado['plaza'] = $row['plaza'];
				$resultado['factura'] = $row['factura'];
			}
			elseif($row['fecha']<'2015-05-01'){
				$resultado['error'] = 1;
				$resultado['mensaje'] .= 'Error, solo se pueden facturar por esta pagina ventas de mayo 2015 en adelante'."\n";
			}
			elseif($row['fecha']<$limite30){
				$resultado['error'] = 1;
				$resultado['mensaje'] .= 'Vencio el limite de facturación'."\n";
			}
			elseif($row['tipo_pago'] == 2){
				$resultado['error'] = 1;
				$resultado['mensaje'] = 'Error, no se pueden facturar pagos a credito'."\n";
			}
			else{
				$clavesencontradas[]=trim($clave);
				$resultado['generar_factura'] = 1;
				$resultado['html'] .=  '
				<tr><td align="center"><span style="cursor:pointer;" onClick="if(confirm(\'Esta seguro de quitar el ticket?\')) quitar_ticket($(this))"><img src="images/validono.gif"></span></td>
				<td align="left" style="padding: 10px!important">'.utf8_encode($row['nomplaza']).'</td>
				<td align="center" style="padding: 10px!important">'.$row['cve'].'</td>
				<td align="left" style="padding: 10px!important">'.$row['engomado'].'</td>
				<td align="right" style="padding: 10px!important">'.$row['monto'].'';
				$resultado['html'] .=  '<input type="hidden" name="ticket[]" id="ticket_'.$row['plaza'].'_'.$row['cve'].'" plaza="'.$row['plaza'].'" tipo_pago="'.$row['tipo_pago'].'" class="tickets" value="'.$row['cve'].'">
				<input type="hidden" name="plaza[]" id="plaza_'.$row['plaza'].'_'.$row['cve'].'" value="'.$row['plaza'].'">
				<input type="hidden" name="placat[]" id="placat_'.$row['plaza'].'_'.$row['cve'].'" value="'.$row['placa'].'"></td></tr>';
			}
			$fecha = $row['fecha'];
		}
	}
	else{
		$resultado['error'] = 1;
		$resultado['mensaje'] .= 'Error en la relación de la placa y folio de ticket'."\n";
	}
	echo json_encode($resultado);

	exit();
}

top();


include("imp_factura.php");

if($_POST['cmd']==3){
	$datos=explode('|',$_POST['reg']);
	require_once("phpmailer/class.phpmailer.php");
	$resfactura=mysql_query("SELECT * FROM facturas WHERE plaza='".$datos[0]."' AND cve='".$datos[1]."' AND estatus NOT IN ('C','D')");
	if($Factura = mysql_fetch_array($resFactura)){
		$resplaza = mysql_query("SELECT * FROM plazas WHERE cve='".$datos[0]."'");
		$rowplaza = mysql_fetch_array($resplaza);
		$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$datos[0]."'");
		$rowempresa = mysql_fetch_array($resempresa);
		if($rowempresa['check_sucursal']==1){
			$datossucursal=",check_sucursal='".$Factura['check_sucursal']."',nombre_sucursal='".addslashes($Factura['nombre_sucursal'])."',
			calle_sucursal='".addslashes($Factura['calle_sucursal'])."',numero_sucursal='".$Factura['numero_sucursal']."',
			colonia_sucursal='".addslashes($Factura['colonia_sucursal'])."',rfc_sucursal='".$Factura['rfc_sucursal']."',
			localidad_sucursal='".addslashes($Factura['localidad_sucursal'])."',municipio_sucursal='".addslashes($Factura['municipio_sucursal'])."',
			estado_sucursal='".addslashes($Factura['estado_sucursal'])."',cp_sucursal='".$Factura['cp_sucursal']."'";
		}
		$res = mysql_query("SELECT serie,folio_inicial FROM foliosiniciales WHERE plaza='".$datos[0]."' AND tipo=0 AND tipodocumento=2");
		$row = mysql_fetch_array($res);
		$res1 = mysql_query("SELECT IFNULL(MAX(folio+1),1) FROM notascredito WHERE plaza='".$datos[0]."' AND serie='".$row['serie']."'");
		$row1 = mysql_fetch_array($res1);
		if($row['folio_inicial']<$row1[0]){
			$row['folio_inicial'] = $row1[0];
		}
		$insert = "INSERT notascredito SET plaza='".$datos[0]."',serie='".$row['serie']."',folio='".$row['folio_inicial']."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$Factura['obs']."',
		cliente='".$Factura['cliente']."',tipo_pago='".$Factura['tipo_pago']."',forma_pago='".$Factura['forma_pago']."',usuario='-1',baniva_retenido='".$Factura['baniva_retenido']."',banisr_retenido='".$Factura['banisr_retenido']."',
		carta_porte='".$Factura['carta_porte']."',load_cliente='".$Factura['load']."',nombre_cliente='".$Factura['nombre_cliente']."',direccion_cliente='".$Factura['direccion_cliente']."',
		tipopago_cliente='".$Factura['tipopago_cliente']."',banco_cliente='".$Factura['banco_cliente']."',cuenta_cliente='".$Factura['cuenta_cliente']."',tipo_factura='".$Factura['tipo_factura']."',
		factura='".$Factura['cve']."',engomado='".$Factura['engomado']."',banco='".$Factura['banco']."',cuenta_cheque='".$Factura['cuenta_cheque']."',tiene_descuento='".$Factura['tiene_descuento']."'".$datossucursal;
		while(!$resinsert=mysql_query($insert)){
			$row['folio_inicial']++;
			$insert = "INSERT notascredito SET plaza='".$datos[0]."',serie='".$row['serie']."',folio='".$row['folio_inicial']."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$Factura['obs']."',
			cliente='".$Factura['cliente']."',tipo_pago='".$Factura['tipo_pago']."',forma_pago='".$Factura['forma_pago']."',usuario='-1',baniva_retenido='".$Factura['baniva_retenido']."',banisr_retenido='".$Factura['banisr_retenido']."',
			carta_porte='".$Factura['carta_porte']."',load_cliente='".$Factura['load']."',nombre_cliente='".$Factura['nombre_cliente']."',direccion_cliente='".$Factura['direccion_cliente']."',
			tipopago_cliente='".$Factura['tipopago_cliente']."',banco_cliente='".$Factura['banco_cliente']."',cuenta_cliente='".$Factura['cuenta_cliente']."',tipo_factura='".$Factura['tipo_factura']."',
			factura='".$Factura['cve']."',engomado='".$Factura['engomado']."',banco='".$Factura['banco']."',cuenta_cheque='".$Factura['cuenta_cheque']."',tiene_descuento='".$Factura['tiene_descuento']."'".$datossucursal;
		}
		
		$cvefact=mysql_insert_id();
		$documento=array();
		require_once("nusoap/nusoap.php");
		$fserie=$row['serie'];
		$ffolio=$row['folio_inicial'];
		//Generamos la Factura
		$documento['serie']=$row['serie'];
		$documento['folio']=$row['folio_inicial'];
		$documento['fecha']=fechaLocal().' '.horaLocal();
		$documento['formapago']=$array_forma_pago[$Factura['forma_pago']];
		$documento['idtipodocumento']=2;
		$documento['observaciones']='';//$Factura['obs'];
		$documento['metodopago']=$array_tipo_pagosat[$Factura['tipo_pago']];
		$res = mysql_query("SELECT * FROM clientes WHERE cve='".$Factura['cliente']."'");
		$row = mysql_fetch_array($res);
		$emailenvio = $row['email'];
		$row['cve']=0;
		$documento['receptor']['codigo']=$row['cve'];
		$documento['receptor']['rfc']=$row['rfc'];
		$documento['receptor']['nombre']=$row['nombre'];
		$documento['receptor']['calle']=$row['calle'];
		$documento['receptor']['num_ext']=$row['numexterior'];
		$documento['receptor']['num_int']=$row['numinterior'];
		$documento['receptor']['colonia']=$row['colonia'];
		$documento['receptor']['localidad']=$row['localidad'];
		$documento['receptor']['municipio']=$row['municipio'];
		$documento['receptor']['estado']=$row['estado'];
		$documento['receptor']['pais']='MEXICO';
		$documento['receptor']['codigopostal']=$row['codigopostal'];
		//Agregamos los conceptos
		$i=0;
		$resD=mysql_query("SELECT * FROM facturasmov WHERE plaza='".$Factura['plaza']."' AND cvefact='".$Factura['cve']."'");
		while($rowD=mysql_fetch_array($resD)){
			if(trim($rowD['unidad'])=="") $rowD['unidad'] = "NO APLICA";
			mysql_query("INSERT notascreditomov SET plaza='".$rowD['plaza']."',cvefact='$cvefact',cantidad='".$rowD['cantidad']."',concepto='".$rowD['concepto']."',
			precio='".$rowD['precio'][$k]."',descuento='".$rowD['descuento']."',importe='".$rowD['importe']."',iva='".$rowD['iva']."',
			importe_iva='".$rowD['importe_iva']."',unidad='".$rowD['unidad']."',
			engomado='".$rowD['engomado']."'");
			$documento['conceptos'][$i]['cantidad']=$rowD['cantidad'];
			$documento['conceptos'][$i]['unidad']=$rowD['unidad'];
			$documento['conceptos'][$i]['descripcion']=$rowD['concepto'];
			$documento['conceptos'][$i]['valorUnitario']=$rowD['precio'];
			$documento['conceptos'][$i]['importe']=$rowD['importe'];
			$documento['conceptos'][$i]['importe_iva']=$rowD['importe_iva'];
			$i++;
		}
		mysql_query("UPDATE notascredito SET subtotal='".$Factura['subtotal']."',iva='".$Factura['iva']."',total='".$Factura['total']."',
		isr_retenido='".$Factura['isr_retenido']."',por_isr_retenido='".$Factura['por_isr_retenido']."',
		iva_retenido='".$Factura['iva_retenido']."',por_iva_retenido='".$Factura['por_iva_retenido']."' WHERE plaza='".$Factura['plaza']."' AND cve=".$cvefact);
		mysql_query("UPDATE cobro_engomado SET notacredito = '$cvefact' WHERE plaza='".$Factura['plaza']."' AND factura='".$Factura['cve']."' AND factura>0");
		mysql_query("UPDATE facturas SET estatus = 'D' WHERE plaza='".$Factura['plaza']."' AND cve='".$Factura['cve']."'");
		mysql_query("UPDATE venta_engomado_factura SET notacredito = '$cvefact' WHERE plaza='".$Factura['plaza']."' AND factura='".$Factura['cve']."'");
		$documento['subtotal']=$Factura['subtotal'];
		$documento['descuento']=0;
		//Traslados
		#IVA
		if($Factura['iva']>0){
			$documento['tasaivatrasladado']=16;
			$documento['ivatrasladado']=$Factura['iva'];  //Solo 200 grava iva
		}
		if($Factura['iva_retenido'] > 0){
			$documento['ivaretenido']=$Factura['iva_retenido'];  
		}
		if($Factura['isr_retenido'] > 0){
			$documento['isrretenido']=$Factura['isr_retenido'];  
		}
		//total
		$documento['total']=$Factura['total'];
		//Moneda
		$documento['moneda']     = 1; //1=pesos, 2=Dolar, 3=Euro
		$documento['tipocambio'] = 1;
		
		//print_r($documento);
		$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
		$err = $oSoapClient->getError();
		if($err!="")
			echo "error1:".$err;
		else{
			//print_r($documento);
			$oSoapClient->timeout = 300;
			$oSoapClient->response_timeout = 300;
			$respuesta = $oSoapClient->call("generar", array ('id' => $rowempresa['idplaza'],'rfcemisor' => $rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'documento' => $documento, 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
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
						mysql_query("UPDATE notascredito SET respuesta1='".$respuesta['uuid']."',seriecertificado='".$respuesta['seriecertificado']."',
						sellodocumento='".$respuesta['sellodocumento']."',uuid='".$respuesta['uuid']."',seriecertificadosat='".$respuesta['seriecertificadosat']."',
						sellotimbre='".$respuesta['sellotimbre']."',cadenaoriginal='".$respuesta['cadenaoriginal']."',
						fechatimbre='".substr($respuesta['fechatimbre'],0,10)." ".substr($respuesta['fechatimbre'],-8)."'
						WHERE plaza='".$Factura['plaza']."' AND cve=".$cvefact);
						//Tomar la informacion de Retorno
						$dir="cfdi/comprobantes/";
						//$dir=dirname(realpath(getcwd()))."/solucionesfe_facturacion/cfdi/comprobantes/";
						//el zip siempre se deja fuera
						$dir2="cfdi/";
						//Leer el Archivo Zip
						$fileresult=$respuesta['archivos'];
						$strzipresponse=base64_decode($fileresult);
						$filename='cfdinc_'.$Factura['plaza'].'_'.$cvefact;
						file_put_contents($dir2.$filename.'.zip', $strzipresponse);
						$zip = new ZipArchive;
						if ($zip->open($dir2.$filename.'.zip') === TRUE){
							$strxml=$zip->getFromName('xml.xml');
							file_put_contents($dir.$filename.'.xml', $strxml);
							$strpdf=$zip->getFromName('formato.pdf');
							file_put_contents($dir.$filename.'.pdf', $strpdf);
							$zip->close();		
							generaFacturaPdf($Factura['plaza'],$cvefact,0,2);
							if($emailenvio!=""){
								$mail = new PHPMailer();
								$mail->Host = "localhost";
								$mail->From = "verificentros@verificentros.net";
								$mail->FromName = "Verificentros Plaza ".$array_plaza[$Factura['plaza']];
								$mail->Subject = "Nota de Credito ".$fserie." ".$ffolio;
								$mail->Body = "Nota de Credito ".$fserie." ".$ffolio;
								//$mail->AddAddress(trim($emailenvio));
								$correos = explode(",",trim($emailenvio));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								if($rowempresa['email']!=""){
									$correos = explode(",",trim($rowempresa['email']));
									foreach($correos as $correo)
										$mail->AddCC(trim($correo));
								}
								$mail->AddAttachment("cfdi/comprobantes/nc_".$Factura['plaza']."_".$cvefact.".pdf", "Nota de Credito ".$fserie." ".$ffolio.".pdf");
								$mail->AddAttachment("cfdi/comprobantes/cfdinc_".$Factura['plaza']."_".$cvefact.".xml", "Nota de Credito ".$fserie." ".$ffolio.".xml");
								$mail->Send();
							}	
							if($rowempresa['email']!=""){
								$mail = new PHPMailer();
								$mail->Host = "localhost";
								$mail->From = "verificentros@verificentros.net";
								$mail->FromName = "Verificentros Plaza ".$array_plaza[$Factura['plaza']];
								$mail->Subject = "Nota de Credito ".$fserie." ".$ffolio;
								$mail->Body = "Nota de Credito ".$fserie." ".$ffolio;
								//$mail->AddAddress(trim($rowempresa['email']));
								$correos = explode(",",trim($rowempresa['email']));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("cfdi/comprobantes/nc_".$Factura['plaza']."_".$cvefact.".pdf", "Nota de Credito ".$fserie." ".$ffolio.".pdf");
								$mail->AddAttachment("cfdi/comprobantes/cfdinc_".$Factura['plaza']."_".$cvefact.".xml", "Nota de Credito ".$fserie." ".$ffolio.".xml");
								$mail->Send();
							}	
						}
						else 
							$strmsg='Error al descomprimir el archivo';
						echo '<h1>Se genero el folio de nota de credito '.$fserie." ".$ffolio.'</h1>';
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
		echo '<h1>Error en la factura</h1>';
	}
	$_POST['cmd'] = 0;
}

if($_POST['cmd']==2){
	foreach($_POST as $k=>$v){
		if($k!='plaza' && $k!='placat' && $k!='ticket'){
			$_POST[$k] = mb_strtoupper(utf8_decode($v));
		}
	}
	$plaza = $_POST['plaza'][0];
	$tickets = implode(',',$_POST['ticket']);
	$res = mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$plaza."' AND cve IN (".$tickets.") AND estatus!='C' AND (factura=0 OR notacredito>0)");
	if($row=mysql_fetch_array($res)){
		if($row['tipo_pago']==5) $tipo_pago=7;
		elseif($row['tipo_pago']==7) $tipo_pago=9;
		else $tipo_pago=0;
		mysql_query("INSERT clientes SET plaza='".$plaza."',fechayhora=NOW(),usuario='-1',
							rfc='".$_POST['rfc']."',nombre='".addslashes($_POST['nombre'])."',email='".$_POST['email']."',calle='".addslashes($_POST['calle'])."',
							numexterior='".addslashes($_POST['numexterior'])."',numinterior='".addslashes($_POST['numinterior'])."',colonia='".addslashes($_POST['colonia'])."',
							municipio='".addslashes($_POST['municipio'])."',estado='".addslashes($_POST['estado'])."',codigopostal='".$_POST['codigopostal']."'");
	
		$cliente_id = mysql_insert_id();
		$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$plaza."'");
		$rowempresa = mysql_fetch_array($resempresa);
		$resplaza = mysql_query("SELECT * FROM plazas WHERE cve='".$plaza."'");
		$rowplaza = mysql_fetch_array($resplaza);
		$datossucursal='';
		if($rowempresa['check_sucursal']==1){
			$datossucursal=",check_sucursal='".$rowempresa['check_sucursal']."',nombre_sucursal='".addslashes($rowempresa['nombre_sucursal'])."',
			calle_sucursal='".addslashes($rowempresa['calle_sucursal'])."',numero_sucursal='".$rowempresa['numero_sucursal']."',
			colonia_sucursal='".addslashes($rowempresa['colonia_sucursal'])."',rfc_sucursal='".$rowempresa['rfc_sucursal']."',
			localidad_sucursal='".addslashes($rowempresa['localidad_sucursal'])."',municipio_sucursal='".addslashes($rowempresa['municipio_sucursal'])."',
			estado_sucursal='".addslashes($rowempresa['estado_sucursal'])."',cp_sucursal='".$rowempresa['cp_sucursal']."'";
		}
		//require_once("phpmailer/class.phpmailer.php");
		require_once('PHPMailer-master/PHPMailerAutoload.php');
	
		$array_detalles = array();
		$ventas = '';
		$res=mysql_query("SELECT a.cve, (a.monto+IFNULL(f.recuperacion,0)-IFNULL(e.devolucion,0)) as monto, a.fecha, a.placa FROM cobro_engomado a 
			LEFT JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket AND b.estatus!='C' 
			LEFT JOIN 
				(SELECT ticket, SUM(devolucion) as devolucion FROM devolucion_certificado WHERE plaza = '".$plaza."' AND estatus != 'C' AND ticket IN (".$tickets.") GROUP BY ticket) e ON a.cve = e.ticket
			LEFT JOIN 
				(SELECT ticket, SUM(recuperacion) as recuperacion FROM recuperacion_certificado WHERE plaza = '".$plaza."' AND estatus != 'C' AND ticket IN (".$tickets.") GROUP BY ticket) f ON a.cve = f.ticket
			WHERE a.plaza='".$plaza."' AND a.cve IN (".$tickets.") AND a.estatus!='C' AND (a.factura=0 OR a.notacredito>0) GROUP BY a.cve");
		while($row=mysql_fetch_array($res)){
			if(substr($row['fecha'],0,7) < date("Y-m")){
				$array_detalles[] = array(
					'cant' => 1,
					'monto' => $row['monto'],
					'descripcion' => 'VERIFICACION DEL MES DE '.strtoupper($array_meses[intval(substr($row['fecha'],5,2))]).' '.$row['fecha'].', '.$row['placa'].', '.$row['cve']
				);
				$observaciones = 'FACTURADO EN EL MES DE '.strtoupper($array_meses[intval(date('m'))]).' A SOLICITUD DEL CLIENTE POR NO FACTURAR EN EL MES QUE VERIFICO';
			}
			else{
				$array_detalles[$row['engomado']]['cant']+=1;
				$array_detalles[$row['engomado']]['monto']+=$row['monto'];
				$observaciones = '';
			}
			$ventas .= ','.$row['cve'];
		}
		if(count($array_detalles)>0){
			$ip = getRealIP();
			$res = mysql_query("SELECT serie,folio_inicial FROM foliosiniciales WHERE plaza='".$plaza."' AND tipo=0 AND tipodocumento=1");
			$row = mysql_fetch_array($res);
			$res1 = mysql_query("SELECT IFNULL(MAX(folio+1),1) FROM facturas WHERE plaza='".$plaza."' AND serie='".$row['serie']."'");
			$row1 = mysql_fetch_array($res1);
			if($row['folio_inicial']<$row1[0]){
				$row['folio_inicial'] = $row1[0];
			}
			$insert = "INSERT facturas SET plaza='".$plaza."',serie='".$row['serie']."',folio='".$row['folio_inicial']."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$observaciones."',ip='$ip',
			cliente='".$cliente_id."',tipo_pago='$tipo_pago',usuario='-1',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
			carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
			tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."'".$datossucursal;
			while(!$resinsert=mysql_query($insert)){
				$row['folio_inicial']++;
				$insert = "INSERT facturas SET plaza='".$plaza."',serie='".$row['serie']."',folio='".$row['folio_inicial']."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$observaciones."',ip='$ip',
				cliente='".$cliente_id."',tipo_pago='$tipo_pago',usuario='-1',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
				carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
				tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."'".$datossucursal;
			}
			/*$res1 = mysql_query("SELECT cve FROM facturas WHERE plaza='".$plaza."'");
			if(mysql_num_rows($res1) > 0){
				mysql_query("INSERT facturas SET plaza='".$plaza."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',ip='$ip',
				cliente='".$cliente_id."',tipo_pago='0',usuario='-1',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
				carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
				tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."'".$datossucursal) or die(mysql_error());
			}
			else{
				mysql_query("INSERT facturas SET plaza='".$plaza."',cve='".$row['folio_inicial']."',fecha='".fechaLocal()."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',ip='$ip',
				cliente='".$cliente_id."',tipo_pago='0',usuario='-1',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
				carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
				tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."'".$datossucursal) or die(mysql_error());
			}*/
			$cvefact=mysql_insert_id();
	
			$documento=array();
			require_once("nusoap/nusoap.php");
			$fserie=$row['serie'];
			$ffolio=$row['folio_inicial'];
			//Generamos la Factura
			$documento['serie']=$row['serie'];
			$documento['folio']=$row['folio_inicial'];
			$documento['fecha']=fechaLocal().' '.horaLocal();
			$documento['formapago']='PAGO EN UNA SOLA EXHIBICION';
			$documento['idtipodocumento']=1;
			$documento['observaciones']=$observaciones;//$_POST['obs'];
			$documento['metodopago']=$array_tipo_pagosat[$tipo_pago];
			$res = mysql_query("SELECT * FROM clientes WHERE cve='".$cliente_id."'");
			$row = mysql_fetch_array($res);
			$emailenvio = $row['email'];
			$row['cve']=0;
			$documento['receptor']['codigo']=$row['cve'];
			$documento['receptor']['rfc']=$row['rfc'];
			$documento['receptor']['nombre']=$row['nombre'];
			$documento['receptor']['calle']=$row['calle'];
			$documento['receptor']['num_ext']=$row['numexterior'];
			$documento['receptor']['num_int']=$row['numinterior'];
			$documento['receptor']['colonia']=$row['colonia'];
			$documento['receptor']['localidad']=$row['localidad'];
			$documento['receptor']['municipio']=$row['municipio'];
			$documento['receptor']['estado']=$row['estado'];
			$documento['receptor']['pais']='MEXICO';
			$documento['receptor']['codigopostal']=$row['codigopostal'];
		
			//Agregamos los conceptos
			$i=0;
			$iva=0;
			$subtotal=0;
			$total=0;
			foreach($array_detalles as $k=>$v){
				$importe_iva=round($v['monto']*16/116,2);
				$total+=round($v['monto'],2);
				$subtotal+=round($v['monto']-$importe_iva,2);
				$iva+=$importe_iva;
				$concepto = 'VERIFICACION VEHICULAR';
				if($v['descripcion'] != '') $concepto = $v['descripcion'];
				mysql_query("INSERT facturasmov SET plaza='".$plaza."',cvefact='$cvefact',cantidad='".$v['cant']."',
				concepto='".$concepto."',
				precio='".round(round($v['monto']-$importe_iva,2)/$v['cant'],2)."',importe='".round($v['monto']-$importe_iva,2)."',
				iva='16',importe_iva='$importe_iva',unidad='No Aplica'");
				$documento['conceptos'][$i]['cantidad']=$v['cant'];
				$documento['conceptos'][$i]['unidad']='No Aplica';
				$documento['conceptos'][$i]['descripcion']=$concepto;
				$documento['conceptos'][$i]['valorUnitario']=round(round($v['monto']-$importe_iva,2)/$v['cant'],2);
				$documento['conceptos'][$i]['importe']=round($v['monto']-$importe_iva,2);
				$documento['conceptos'][$i]['importe_iva']=$importe_iva;
				$i++;
			}
			mysql_query("UPDATE facturas SET subtotal='".$subtotal."',iva='".$iva."',total='".$total."',
			isr_retenido='".$_POST['isr_retenido']."',por_isr_retenido='".$_POST['por_isr_retenido']."',
			iva_retenido='".$_POST['iva_retenido']."',por_iva_retenido='".$_POST['por_iva_retenido']."' 
			WHERE plaza='".$plaza."' AND cve=".$cvefact);
			$documento['subtotal']=$subtotal;
			$documento['descuento']=0;
			//Traslados
			#IVA
			if($iva>0){
				$documento['tasaivatrasladado']=16;
				$documento['ivatrasladado']=$iva;  //Solo 200 grava iva
			}
			if($_POST['iva_retenido'] > 0){
				$documento['ivaretenido']=$_POST['iva_retenido'];  
			}
			if($_POST['isr_retenido'] > 0){
				$documento['isrretenido']=$_POST['isr_retenido'];  
			}
			//total
			$documento['total']=$total;
			//Moneda
			$documento['moneda']     = 1; //1=pesos, 2=Dolar, 3=Euro
			$documento['tipocambio'] = 1;
			mysql_query("UPDATE cobro_engomado SET factura='".$cvefact."',documento=1,notacredito=0 WHERE plaza='".$plaza."' AND cve IN (".substr($ventas,1).")");
			mysql_query("INSERT INTO venta_engomado_factura (plaza,venta,factura) SELECT ".$plaza.",cve,factura FROM cobro_engomado WHERE plaza='".$plaza."' AND factura='".$cvefact."'");
			//print_r($documento);
			if(trim($_POST['rfc'])!='RAGS660522NT3'){
				$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
				$err = $oSoapClient->getError();
				if($err!="")
					echo "error1:".$err;
				else{
					//print_r($documento);
					$oSoapClient->timeout = 300;
					$oSoapClient->response_timeout = 300;
					$respuesta = $oSoapClient->call("generar", array ('id' => $rowempresa['idplaza'],'rfcemisor' => $rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'documento' => $documento, 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
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
								mysql_query("UPDATE facturas SET respuesta1='".$respuesta['uuid']."',seriecertificado='".$respuesta['seriecertificado']."',
								sellodocumento='".$respuesta['sellodocumento']."',uuid='".$respuesta['uuid']."',seriecertificadosat='".$respuesta['seriecertificadosat']."',
								sellotimbre='".$respuesta['sellotimbre']."',cadenaoriginal='".$respuesta['cadenaoriginal']."',
								fechatimbre='".substr($respuesta['fechatimbre'],0,10)." ".substr($respuesta['fechatimbre'],-8)."'
								WHERE plaza='".$plaza."' AND cve=".$cvefact);
								//Tomar la informacion de Retorno
								$dir="cfdi/comprobantes/";
								//$dir=dirname(realpath(getcwd()))."/solucionesfe_facturacion/cfdi/comprobantes/";
								//el zip siempre se deja fuera
								$dir2="cfdi/";
								//Leer el Archivo Zip
								$fileresult=$respuesta['archivos'];
								$strzipresponse=base64_decode($fileresult);
								$filename='cfdi_'.$plaza.'_'.$cvefact;
								file_put_contents($dir2.$filename.'.zip', $strzipresponse);
								$zip = new ZipArchive;
								if ($zip->open($dir2.$filename.'.zip') === TRUE){
									$strxml=$zip->getFromName('xml.xml');
									file_put_contents($dir.$filename.'.xml', $strxml);
									$strpdf=$zip->getFromName('formato.pdf');
									file_put_contents($dir.$filename.'.pdf', $strpdf);
									$zip->close();		
									generaFacturaPdf($plaza,$cvefact);
									if($emailenvio!=""){
										$mail = new PHPMailer;		
										$mail->isSMTP();                                      // Set mailer to use SMTP
										$mail->Host = 'smtp.mailgun.org';                     // Specify main and backup SMTP servers
										$mail->SMTPAuth = true;                               // Enable SMTP authentication
										$mail->Username = 'postmaster@verificentrosgp1.net';   // SMTP username
										$mail->Password = 'a4f9c1bb34ed1c639a0cdeedb5f79aea';                           // SMTP password
										$mail->SMTPSecure = 'tls';    
										$mail->From = "verificentros@verificentrosgp1.net";
										$mail->FromName = "Verificentros Plaza ".$array_plaza[$plaza];
										$mail->Subject = "Factura ".$fserie." ".$ffolio;
										$mail->Body = "Factura ".$fserie." ".$ffolio;
										//$mail->AddAddress(trim($emailenvio));
										$correos = explode(",",trim($emailenvio));
										foreach($correos as $correo)
											$mail->AddAddress(trim($correo));
										$mail->AddAttachment("cfdi/comprobantes/factura_".$plaza."_".$cvefact.".pdf", "Factura ".$fserie." ".$ffolio.".pdf");
										$mail->AddAttachment("cfdi/comprobantes/cfdi_".$plaza."_".$cvefact.".xml", "Factura ".$fserie." ".$ffolio.".xml");
										$mail->Send();
									}	
									if(1==2 && $rowempresa['email']!=""){
										$mail = new PHPMailer;		
										$mail->isSMTP();                                      // Set mailer to use SMTP
										$mail->Host = 'smtp.mailgun.org';                     // Specify main and backup SMTP servers
										$mail->SMTPAuth = true;                               // Enable SMTP authentication
										$mail->Username = 'postmaster@verificentrosgp1.net';   // SMTP username
										$mail->Password = 'a4f9c1bb34ed1c639a0cdeedb5f79aea';                           // SMTP password
										$mail->SMTPSecure = 'tls';    
										$mail->From = "verificentros@verificentrosgp1.net";
										$mail->FromName = "Verificentros Plaza ".$array_plaza[$plaza];
										$mail->Subject = "Factura ".$fserie." ".$ffolio;
										$mail->Body = "Factura ".$fserie." ".$ffolio;
										//$mail->AddAddress(trim($rowempresa['email']));
										$correos = explode(",",trim($rowempresa['email']));
										foreach($correos as $correo)
											$mail->AddAddress(trim($correo));
										$mail->AddAttachment("cfdi/comprobantes/factura_".$plaza."_".$cvefact.".pdf", "Factura ".$fserie." ".$ffolio.".pdf");
										$mail->AddAttachment("cfdi/comprobantes/cfdi_".$plaza."_".$cvefact.".xml", "Factura ".$fserie." ".$ffolio.".xml");
										$mail->Send();
									}	
									@unlink("cfdi/comprobantes/factura_".$plaza."_".$cvefact.".pdf");
								}
								else 
									$strmsg='Error al descomprimir el archivo';
									
								echo '<h1>Se genero el folio de factura '.$fserie." ".$ffolio.'  <a href="#" onClick="atcr(\'facturacion_web.php\',\'_blank\',\'200\',\''.$plaza.'|'.$cvefact.'|1\');"><img src="images/zip_grande.png" border="0" width="45px" height="45px" title="Descargar">Descarga aqui su factura</a></h1>';
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
	}
	$_POST['cmd']=0;
}
if($_POST['cmd'] == 0){
?>
<link href="assets/lightbox2/dist/css/lightbox.min.css" rel="stylesheet">
<script src="assets/lightbox2/dist/js/lightbox.min.js"></script>

<div class="container">
<section class="col-sm-9">
<br><br><br>
<div class="form-group" align="center">
	<div class="col-sm-12">
		<div class="row">
			<div class="col-sm-4">
				<!--<a href="images/ticket_clavefacturacion_highres.jpg" data-lightbox="Ticket" data-title="Ticket">
				<img src="images/ticket_clavefacturacion_highres.jpg" class="img-responsive" alt="Responsive image">
				</a>-->&nbsp;
			</div>
			<div class="col-sm-6">
				<!--<label for="codigofactura">Clave de Facturación <br />(12 dígitos que se encuentran dentro de su ticket)</label>
				<br><br>
				<div class="input-group">
					<textarea class="form-control input-sm" id="codigofactura" name="codigofactura" placeholder="Escribir clave de facturación" cols="70" rows="7"></textarea>									
				</div>-->
				<label for="codigofactura">Placa</label>
				<br>
				<div class="input-group">
					<input type="text" size="20" class="form-control input-sm" id="placa" name="placa" placeholder="Escriba la Placa">									
				</div>
				<br>
				<label for="codigofactura">Folio de Ticket</label>
				<br>
				<div class="input-group">
					<input type="text" size="20" class="form-control input-sm" id="folio" name="folio" placeholder="Escriba el folio">									
				</div>
				<br>
				<label for="codigofactura">Clave Facturación</label>
				<br>
				<div class="input-group">
					<input type="text" size="20" class="form-control input-sm" id="clavefacturacion" name="clavefacturacion" placeholder="Clave de facturación">									
				</div>
				<br>
				<button class="btn btn-default btn-sm" type="button" onClick="buscarcodigo()">Buscar</button><br>
				<!--<span class="text-muted"><small>Escribir los C&oacute;digos de Facturaci&oacute;n que desee facturar <br> m&aacute;ximo 7 por factura (1 codigo por linea).</font></small></span>-->
				<!--<span class="text-muted"><small>Escribir la placa y el folio del ticket<br>NO el c&oacute;digo de facturaci&oacute;n<br>NI el c&oacute;digo de barras.</font></small></span>-->
				<span class="text-muted"><small>Escribir la placa, el folio del ticket y la clave de facturaci&oacute;n<br>No el c&oacute;digo de barras.</font></small></span>
				<br>
				<span id="Resultados"><span id="divespera"></span><table border="1"  id="tresultados" style="display:none;">
				<tr><th align="center" style="padding: 10px!important">&nbsp;</th>
				<th align="center" style="padding: 10px!important">Centro de Verificaci&oacute;n</th>
				<th align="center" style="padding: 10px!important">Folio</th>
				<th align="center" style="padding: 10px!important">Tipo de Verificaci&oacute;n</th>
				<th align="center" style="padding: 10px!important">Importe</th></tr></table></span>
    			<p class="text-center"><input type="button" id="continuar" class="btn btn-primary" value="Continuar" onClick="$('#panel').show();validar('');" disabled></p><br />
			</div>
		</div>
	</div>
	
</div>
<textarea style="display:none;" name="htmlcodigos" id="htmlcodigos"></textarea>
<input type="hidden" name="mostrar_campos" id="mostrar_campos" value="0">
<br><br><br>
</section>
<section class="col-sm-3" style="margin-top: 2em;">
				<div class="alert alert-info text-center" role="alert"><b><span class="glyphicon glyphicon-question-sign" aria-hidden="true" style="font-size: 100px"></span><br>¿Problemas con su factura?</b></div>
				<p><b>Soporte para facturación:</b><br><span class="text-info">Soporte D.F.: verifactura@gmail.com</span><p>
				<p>Enviar en Asunto folio de ticket, placa y número y/o nombre del centro de verificación</p>
				<p><h2>Se recomienda el uso de Google Chrome <img src="images/chrome.png" width="40px" height="40px"> o Mozilla Firefox<img src="images/firefox.png" width="40px" height="40px"></h2></p>
			
</section>
</div>
<script>
	function validar(){
		if(hayplazasdiferentes()){
            $('#panel').hide();
            alert("Los tickets deben de pertenecer al mismo centro de verificación");
        }
        else if(haytipospagosdiferentes()){
            $('#panel').hide();
            alert("Los tickets deben de ser pagados con el mismo tipo de pago");
        }
        else{
        	$('#tresultados').find('span').remove();
        	html_tickets = '';
        	$('.tickets').each(function(){
        		html_tickets += '<tr>'+$(this).parents('tr:first').html()+'</tr>';
        	});
            $("#htmlcodigos").val(html_tickets);
    		$("#mostrar_campos").val(1);
            atcr("facturacion_web.php","",1,0);
        }
	}

	function buscarcodigo(){
        if($.trim(document.forma.placa.value)==""){
            alert("Necesita ingresar la placa");
        }
        else if($.trim(document.forma.folio.value)==""){
            alert("Necesita ingresar el folio del ticket");
        }
        else{
        	tickets_capturados = [];
        	$('.tickets').each(function(){
        		campo_ticket = $(this);
        		ticket = {};
        		ticket.plaza = campo_ticket.attr('plaza');
        		ticket.cve = campo_ticket.val();
        		tickets_capturados.push(ticket);
        	});
            $.ajax({
              url: "facturacion_web.php",
              type: "POST",
              async: false,
              dataType: "json",
              data: {
                placa: $.trim(document.forma.placa.value),
                folio: $.trim(document.forma.folio.value),
                clavefacturacion: $.trim(document.forma.clavefacturacion.value),
                ajax: 1,
                tickets_capturados: JSON.stringify(tickets_capturados),
                recargado: 1
              },
                beforeSend: function(){
                    console.log("Do something....");
                    $("#divespera").html('<div class="text-center"><p><i class="fa fa-spinner fa-3x fa-spin"></i></p></div>');
                },
                success: function(data) {   
                	$("#divespera").html('');
                	if(data.error == 1){
                		alert(data.mensaje);
                	}   
                	else if(data.error == 2){
                		if(confirm("Ticket ya facturado desea descargar la factura?")){
                			atcr('facturacion_web.php','_blank','200',''+data.plaza+'|'+data.factura+'|1');
                		}
                		document.getElementById("folio").value = '';
	                    document.getElementById("placa").value = '';
                	}   
                	else{
                		$("#tresultados").show();
	                    $("#tresultados").hide().append(data.html).fadeIn("slow");
	                    if($(".tickets").length==0){
	                    	$("#continuar").attr("disabled","disabled");
	                    }
	                    else{
	                    	$("#continuar").removeAttr("disabled");
	                    }
	                    document.getElementById("folio").value = '';
	                    document.getElementById("placa").value = '';
	                }
                }
            });
        }
    }

    function quitar_ticket(campo){
    	campo.parents('tr:first').remove();
    	if($(".tickets").length==0){
    		$("#tresultados").hide();
        	$("#continuar").attr("disabled","disabled");
        }
        else{
        	$("#continuar").removeAttr("disabled");
        }
    }

    function hayplazasdiferentes(){
    	plaza = 0;
    	regresar = false;
    	$('.tickets').each(function(){
    		plaza_ticket = $(this).attr('plaza');
    		if(plaza == 0){
    			plaza = plaza_ticket
    		}
    		if(plaza != plaza_ticket){
    			regresar = true;
    		}
    	});
    	return regresar;
    }

    function haytipospagosdiferentes(){
    	tipopago = 0;
    	regresar = false;
    	$('.tickets').each(function(){
    		tipopago_ticket = $(this).attr('tipopago');
    		if(tipopago == 0){
    			tipopago = tipopago_ticket
    		}
    		if(tipopago != tipopago_ticket){
    			regresar = true;
    		}
    	});
    	return regresar;
    }
    
	function buscarcodigoR2(){
        if($.trim(document.forma.placa.value)==""){
            alert("Necesita ingresar la placa");
        }
        else if($.trim(document.forma.folio.value)==""){
            alert("Necesita ingresar el folio del ticket");
        }
        else{
        	$("#panel").show();
            $.ajax({
              url: "facturacion_web.php",
              type: "POST",
              async: false,
              dataType: "json",
              data: {
                ajax: 1,
                placa: $.trim(document.forma.placa.value),
                folio: $.trim(document.forma.folio.value),
                recargado: 1
              },
                beforeSend: function(){
                    console.log("Do something....");
                    $("#divespera").html('<div class="text-center"><p><i class="fa fa-spinner fa-3x fa-spin"></i></p></div>');
                },
                success: function(data) {   
                	$("#divespera").html('');
                	if(data.error == 1){
                		$("#panel").hide();
                		alert(data.mensaje);
                	}   
                	else{
                		$("#htmlcodigos").val(data.html);
                		$("#mostrar_campos").val(data.generar_factura)
	                    atcr("facturacion_web.php","",1,0);
	                }
                }
            });
        }
    }

	function buscarcodigoR(){
        if($.trim(document.forma.codigofactura.value)==""){
            alert("Necesita ingresar el código de facturación");
        }
        else{
        	$("#panel").show();
        	codigos = document.forma.codigofactura.value.split("\n");
        	tickets_capturados = [];
        	for(i=0;i<codigos.length;i++){
        		if($.trim(codigos[i]) != ""){
	        		campo_ticket = $(this);
	        		ticket = {};
	        		ticket.codigo = codigos[i];
	        		tickets_capturados.push(ticket);
	        	}
        	};
            $.ajax({
              url: "facturacion_web.php",
              type: "POST",
              async: false,
              dataType: "json",
              data: {
                ajax: 1,
                tickets_capturados: JSON.stringify(tickets_capturados),
                recargado: 1
              },
                beforeSend: function(){
                    console.log("Do something....");
                    $("#divespera").html('<div class="text-center"><p><i class="fa fa-spinner fa-3x fa-spin"></i></p></div>');
                },
                success: function(data) {   
                	$("#divespera").html('');
                	if(data.error == 1){
                		$("#panel").hide();
                		alert(data.mensaje);
                	}   
                	else{
                		$("#htmlcodigos").val(data.html);
                		$("#mostrar_campos").val(data.generar_factura)
	                    atcr("facturacion_web.php","",1,0);
	                }
                }
            });
        }
    }

    $("#placa").on("keydown", function(e){
		return e.which !== 32;
	});

	$("#folio").on("keydown", function(e){
		return e.which !== 32;
	});
</script>
<?php
}
else{
?>
<div id="dialog" style="display:none;"><h1>El horario de facturaci&oacute;n es de 7:00 a 23:30 horas.</h1></div>
<div class="container">
<section class="col-sm-9">
    <h1 class="text-center">Elabora tu factura</h1>
    <p class="text-center">Capture la información solicitada y finalice dando clic al botón<b>"Generar factura"</b></br>
	Campos marcados con <span class="label label-danger">borde rojo</span> son obligatorios</p>
	
	<div class="row">
		<!-- col izq -->
		<?php
		if($_POST['mostrar_campos'] == 1){
		?>
		<div class="col-sm-6">
			<div class="form-group">
				<label for="nombre" class="col-sm-5 control-label">Razon Social</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="nombre" name="nombre" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group">
				<label for="email" class="col-sm-5 control-label">Correo electrónico</label>
				<div class="col-sm-7">
					<input type="text" class="required form-control input-sm" id="email" name="email">
					<br><span class="text-muted"><small>Si desea entrar mas de un email, solo separelos por comas</small></span>			
				</div>
			</div>
			
			<div class="form-group">
				<label for="confirmacionemail" class="col-sm-5 control-label">Confirmación correo electrónico</label>
				<div class="col-sm-7">
					<input type="text" class="required form-control input-sm" id="confirmacionemail" name="confirmacionemail">
					<br><span class="text-muted"><small>En caso de no encontrar el correo en su bandeja de entrada buscarlo en correo no deseado(spam)</small></span>
				</div>
			</div>
			
			<div class="form-group">
				<label for="rfc" class="col-sm-5 control-label">RFC</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="rfc" name="rfc" maxlength="13" onKeyUp="this.value=this.value.toUpperCase();">
					<br><span class="text-muted"><small>El RFC es sin espacios ni guiones, con homoclave</small></span>
				</div>
			</div>
			
		</div>
		
		<!-- col der -->
		<div class="col-sm-6">
			<div class="form-group">
				<label for="calle" class="col-sm-5 control-label">Calle</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="calle" name="calle" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group">
				<label for="numexterior" class="col-sm-5 control-label">Número exterior</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="numexterior" name="numexterior" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group" style="display:none;">
				<label for="numinterior" class="col-sm-5 control-label">Número interior</label>
				<div class="col-sm-7">
					<input type="text" class="mayusculas form-control input-sm" id="numinterior" name="numeinterior" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group">
				<label for="colonia" class="col-sm-5 control-label">Colonia</label>
				<div class="col-sm-7">
					<input type="text" class="mayusculas form-control input-sm" id="colonia" name="colonia" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group">
				<label for="localidad" class="col-sm-5 control-label">Localidad</label>
				<div class="col-sm-7">
					<input type="text" class="mayusculas form-control input-sm" id="localidad" name="localidad" onKeyUp="this.value=this.value.toUpperCase();">
					<span class="text-muted"><small>Esta prohibido usar Distrito Federal</small></span>
				</div>
			</div>
			
			<div class="form-group">
				<label for="municipio" class="col-sm-5 control-label">Municipio</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="municipio" name="municipio" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group">
				<label for="estado" class="col-sm-5 control-label">Estado</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="estado" name="estado" onKeyUp="this.value=this.value.toUpperCase();">
					<span class="text-muted"><small>Esta prohibido usar Distrito Federal</small></span>
				</div>
			</div>
		
			<div class="form-group">
				<label for="codigopostal" class="col-sm-5 control-label">Código Postal</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="codigopostal" name="codigopostal" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			<div style="display:none;" class="form-group">
				<label for="placa" class="col-sm-5 control-label">Placa</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="placa" name="placa" placeholder="Sin espacios" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
		</div>

		<?php
		}
		?>
		
		<!-- columna codigo facturacion -->
		<div class="col-sm-12">
			<div style="display:none;" class="form-group">
				<label for="codigofactura" class="col-sm-4 control-label">Código de Facturación</label>
				<div class="col-sm-12">
					<div class="row">
						<div class="col-sm-6">
							<div class="input-group">
								<input type="text" class="form-control input-sm" id="codigofactura" name="codigofactura" placeholder="Buscar por cÃ³digo de facturaciÃ³n" onKeyUp="this.value=this.value.toUpperCase();">
								<span class="input-group-btn">
								  <button class="btn btn-default btn-sm" type="button" onClick="buscarcodigo()">Buscar</button>
								</span>
							</div>					
						</div>
						<div class="col-sm-6">
							<div class="alert alert-warning text-center" role="alert"><span class="glyphicon glyphicon-hand-left pull-left" aria-hidden="true" style="font-size: 50px"></span><b>Ya puedes bajar tu factura desde este sitio con tus datos de placa y código de facturación!</b></div>					
						</div>
					</div>

					<span class="text-muted"><small>Buscar el código de facturación para traer los datos del ticket y asi activar el boton para generar factura. Ya puedes hacer tu factura con varios tickets del mismo centro de verificaci&oacute;n. <font color="RED">Para descargar tu factura buscar la factura con los datos de placa y c&oacute;digo de facturaci&oacute;n del ticket.</font></small></span>
				</div>
				
			</div>
			
			<span id="Resultados"><span id="divespera"></span><table border="1"  id="tresultados">
			<tr><th align="center" style="padding: 10px!important">Archivos</th>
			<th align="center" style="padding: 10px!important">Centro de Verificaci&oacute;n</th>
			<th align="center" style="padding: 10px!important">Folio</th>
			<th align="center" style="padding: 10px!important">Tipo de Verificaci&oacute;n</th>
			<th align="center" style="padding: 10px!important">Importe</th></tr><?php echo $_POST['htmlcodigos']; ?></table></span>
			<p class="text-center"><input type="button" id="generar_factura" class="btn btn-primary" value="Generar factura" onClick="$('#panel').show();if(validarRFC()){validar('');} else{ $('#panel').hide(); alert('RFC invalido');}" disabled></p><br /><br />		
		</div>				
	</div>
	
</section>
<section class="col-sm-3" style="margin-top: 2em;">
				<div class="alert alert-info text-center" role="alert"><b><span class="glyphicon glyphicon-question-sign" aria-hidden="true" style="font-size: 100px"></span><br>¿Problemas con su factura?</b></div>
				<p><b>Soporte para facturación:</b><br><span class="text-info">Soporte D.F.: verifactura@gmail.com</span><p>
				<p>Enviar en Asunto folio de ticket, placa y número y/o nombre del centro de verificación</p>
				<p><h2>Se recomienda el uso de Google Chrome <img src="images/chrome.png" width="40px" height="40px"> o Mozilla Firefox<img src="images/firefox.png" width="40px" height="40px"></h2></p>
			
</section>

</div><!-- end container -->
<script>
    function validarRFC(){
        var ValidChars2 = "0123456789";
        var ValidChars1 = "abcdefghijklmnÃ±opqrstuvwxyzABCDEFGHIJKLMNÃ‘OPQRSTUVWXYZ&";
        var cadena=document.getElementById("rfc").value;
        correcto = true;
        if(cadena.length!=13 && cadena.length!=12){
            correcto = false;
        }
        else{
            if(cadena.length==12)
                resta=1;
            else
                resta=0;
            for(i=0;i<cadena.length;i++) {
                digito=cadena.charAt(i);
                if (i<(4-resta) && ValidChars1.indexOf(digito) == -1){
                    correcto = false;
                }
                if (i>=(4-resta) && i<(10-resta) && ValidChars2.indexOf(digito) == -1){
                    correcto = false;
                }
                if (i>=(10-resta) && ValidChars1.indexOf(digito) == -1 && ValidChars2.indexOf(digito) == -1){
                    correcto = false;
                }
            }
        }
        return correcto;
    }

    function validar(){
        if(confirm(" Nombre: "+document.getElementById("nombre").value+"\n Email: "+document.getElementById("email").value+"\n Los datos son correctos?")){
            if(document.getElementById("nombre").value==""){
                $('#panel').hide();
                alert("Necesita ingresar la razon social");
            }
            else if(document.getElementById("email").value==""){
                $('#panel').hide();
                alert("Necesita ingresar el email");
            }
            else if(document.getElementById("email").value!="" && document.getElementById("confirmacionemail").value==""){
                $('#panel').hide();
                alert("Necesita ingresar la confirmacion email");
            }
            else if(document.getElementById("email").value!="" && document.getElementById("confirmacionemail").value!=document.getElementById("email").value){
                $('#panel').hide();
                alert("No son iguales los emails");
            }
            else if(document.forma.rfc.value==""){
                $('#panel').hide();
                alert("Necesita ingresar el rfc");
            }
            else if(document.forma.calle.value==""){
                $('#panel').hide();
                alert("Necesita ingresar la calle");
            }
            else if($.trim(document.forma.localidad.value)=="D.F." || $.trim(document.forma.localidad.value)=="D. F." || $.trim(document.forma.localidad.value).indexOf("DISTRITO FEDERAL")!=-1){
                $('#panel').hide();
                alert("En la localidad puede ir distrito federal");
            }
            else if(document.forma.municipio.value==""){
                $('#panel').hide();
                alert("Necesita ingresar el municipio");
            }
            else if(document.forma.estado.value==""){
                $('#panel').hide();
                alert("Necesita ingresar el estado");
            }
            else if($.trim(document.forma.estado.value)=="D.F." || $.trim(document.forma.estado.value)=="D. F." || $.trim(document.forma.estado.value).indexOf("DISTRITO FEDERAL")!=-1){
                $('#panel').hide();
                alert("En el estado no puede ir distrito federal");
            }
            else if(document.forma.codigopostal.value==""){
                $('#panel').hide();
                alert("Necesita ingresar el código postal");
            }
            else if($(".tickets").length==0){
                $('#panel').hide();
                alert("No hay ningun ticket cargado");
            }
            else if($('.tickets').length > 7){
            	$('#panel').hide();
	        	alert("Solo se pueden agregar un máximo de 7 tickets en una factura");
	        }
            else if(hayplazasdiferentes()){
                $('#panel').hide();
                alert("Los tickets deben de pertenecer al mismo centro de verificación");
            }
            else if(confirm("Esta seguro de seguir la factura se timbrara al guardarla?")){
                atcr("facturacion_web.php","",2,0);
            }
        }
        else{
            $('#panel').hide();
        }
    }

    function agregar_cuenta(){
        $("#cuentas").append('<tr><td align="center"><select name="banco[]"><option value="">Seleccione</option><?php echo $bancos ?></select></td>\<td align="center"><input type="text" class="form-control input-sm" name="cuenta[]" value=""></td></tr>');
    }
</script>
<!--section input[type="text"]{ text-transform: uppercase; }-->
<style>
    .mayusculas { text-transform: uppercase; }
    #Resultados { min-height: 50px; text-align: center; }
	.required { border-left: 2px solid red; }
</style>
<script>
    function buscarcodigo(){
        if($.trim(document.forma.codigofactura.value)==""){
            alert("Necesita ingresar el código de facturación");
        }
        else if($('.tickets').length > 7){
        	alert("Solo se pueden agregar un máximo de 7 tickets en una factura");
        }
        else{
        	tickets_capturados = [];
        	$('.tickets').each(function(){
        		campo_ticket = $(this);
        		ticket = {};
        		ticket.plaza = campo_ticket.attr('plaza');
        		ticket.cve = campo_ticket.val();
        		tickets_capturados.push(ticket);
        	});
            $.ajax({
              url: "facturacion_web.php",
              type: "POST",
              async: false,
              dataType: "json",
              data: {
                codigofactura: document.getElementById("codigofactura").value,
                placa: document.getElementById("placa").value,
                ajax: 1,
                tickets_capturados: JSON.stringify(tickets_capturados),
                recargado: 1
              },
                beforeSend: function(){
                    console.log("Do something....");
                    $("#divespera").html('<div class="text-center"><p><i class="fa fa-spinner fa-3x fa-spin"></i></p></div>');
                },
                success: function(data) {   
                	$("#divespera").html('');
                	if(data.error == 1){
                		alert(data.mensaje);
                	}   
                	else{
	                    $("#tresultados").hide().append(data.html).fadeIn("slow");
	                    if($(".tickets").length==0){
	                    	$("#generar_factura").attr("disabled","disabled");
	                    }
	                    else{
	                    	$("#generar_factura").removeAttr("disabled");
	                    }
	                    document.getElementById("codigofactura").value = '';
	                    document.getElementById("placa").value = '';
	                }
                }
            });
        }
    }

    function quitar_ticket(campo){
    	campo.parents('tr:first').remove();
    	if($(".tickets").length==0){
    		$("#tresultados").hide();
        	$("#generar_factura").attr("disabled","disabled");
        }
        else{
        	$("#generar_factura").removeAttr("disabled");
        }
    }
    function hayplazasdiferentes(){
    	plaza = 0;
    	regresar = false;
    	$('.tickets').each(function(){
    		plaza_ticket = $(this).attr('plaza');
    		if(plaza == 0){
    			plaza = plaza_ticket
    		}
    		if(plaza != plaza_ticket){
    			regresar = true;
    		}
    	});
    	return regresar;
    }
	
	$("#placa").on("keydown", function(e){
		return e.which !== 32;
	});

	if($(".tickets").length==0){
    	$("#generar_factura").attr("disabled","disabled");
    }
    else{
    	$("#generar_factura").removeAttr("disabled");
    }
	
</script>
<?php 
}
bottom(); 

if(date("H:i:s")>'20:00:00' || date('H:i:s')<'08:30:00' || date('Y-m-d') == '2015-10-29' || date('w') == 0){
	if(date('Y-m-d') == '2015-10-29')
		echo '<script>$("#panel").show();
		alert("El dia de hoy permanecera cerrado el formulario de facturación por mantenimiento, disculpe las molestas");
		</script>';
	else
		echo '<script>$("#panel").show();
		alert("El horario de facturación es de 08:30 a 20:00 horas de lunes a sabado");
		</script>';
}
?>