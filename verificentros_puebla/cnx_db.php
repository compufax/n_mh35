<?php

//Conexion con la base
if (!$MySQL=@mysql_connect('localhost', 'vereficentros', 'bAllenA6##6')) {
	$t=time();
	while (time()<$t+5) {}
	if (!$MySQL=@mysql_connect('localhost', 'vereficentros', 'bAllenA6##6')) {
		$t=time();
		while (time()<$t+10) {}
		if (!$MySQL=@mysql_connect('localhost', 'vereficentros', 'bAllenA6##6')) {
		echo '<br><br><br><h3 align=center">Hay problemas de comunicaci&oacute;n con la Base de datos.</h3>';
		echo '<h4>Por favor intente mas tarde.-</h4>';
		exit;
		}
	}
}
$base='vereficentros';
mysql_select_db($base);


function genera_html($plaza, $fecha, $correo = false){
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
	
	$html = '<h1>Total de Citas: '.$totalcitas.'</h1>';
	
	if($correo) $html .= '<table width="100%" border="1">';
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
		if($correo) $html .= '<tr>';
		else $html .= rowb(false, '2');
		//$html .= '<th bgcolor="#E9F2F8">'.$k.'</th>';
		if($correo) $html .= '<td align=center"><b>'.$k.'</b></td>';
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
?>