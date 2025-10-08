<?php

include("subs/cnx_db.php");

$verificacion00 = 3;
$verificacion0 = 2;
$verificacion1 = 5;
$verificacion2 = 1;
$rechazo = 19;

$plazas = array(3, 10);

$motivo = 23;
$anio = 6;

$certificados = array(
	3 => array(
		$verificacion00 => array(
			array(11868041, 11868050)
		),
		$verificacion0 => array(
			array(129063201, 129063300),
			array(129073901, 129074000)
		),
		$verificacion1 => array(
			array(131033601, 131033700)
		)
	),
	10 => array(
		$verificacion00 => array(
			array(11870462, 11870470),
			array(11873121, 11873130),
			array(11878041, 11878070)
		),
		$verificacion0 => array(
			array(129106408, 129106500),
			array(129113101, 129113300)
		),
		$verificacion1 => array(
			array(131051125, 131051300)
		),
		$verificacion2 => array(
			array(132008137, 132008200)
		),
		$rechazo => array(
			array(22655645, 22655700),
			array(22659001, 22659200)
		)
	)
);

foreach($certificados as $plaza => $tipos_verificaciones){
	foreach($tipos_verificaciones as $tipo_verificacion => $arreglofolios){
		foreach($arreglofolios as $folios){
			for($i=$folios[0];$i<=$folios[1];$i++){
				$insert = " INSERT certificados_cancelados
							SET 
							plaza = '".$plaza."',fecha='".date('Y-m-d')."',hora='".date('H:i:s')."',
							certificado='".$i."',motivo='".$motivo."',anio='".$anio."',
							usuario='1',engomado='".$tipo_verificacion."',estatus='A',
							placa='',obs='',tecnico='',
							linea='',ticket='',fechaticket='',tecnico2=''";
				mysql_query($insert);
				mysql_query("UPDATE compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra SET b.estatus=1 WHERE a.plaza='".$plaza."' AND a.engomado = '".$tipo_verificacion."' AND b.folio='".intval($i)."' AND a.estatus!='C'");
			}
		}
	}
}
?>