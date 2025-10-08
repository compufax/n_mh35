<?php
include("main.php");

//ARREGLOS

//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);
$rechazos = "9,19";

$abono=0;
$array_tipo_plaza=array(1=>"Capital",2=>"Municipio",3=>"Movil"
,4=>"Teopanzolco"
,5=>"San Diego"
,6=>"Jojutla"
,7=>"Jantetelco"
,8=>"Mazatepec"
,9=>"Arrastradero"
,10=>"Cuahutemoc"
,11=>"Temixco"
,12=>"Cuautla"
,13=>"Jiutepec"
,14=>"Yecapixtla"
,15=>"Yautepec");

if($_POST['cmd']==102){
	 header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=Informe.xls");
header("Pragma: no-cache");
header("Expires: 0");
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
				$array_plazass[$row['cve']]=$row['tipo_plaza'];
	}
	$html= '<h2>Informe de Ventas del Periodo de : '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2><br><table width="100%" border="1" cellpadding="4" cellspacing="1" class="" >';
	//$html= '<b>Importe = Ventas (Efectivo y Tarjeta de Credito) + Pagos Caja - Devoluciones</b><br><table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	$html.= '<tr bgclor="#E9F2F8"><th>Ubicacion</th><th>Centro</th>';
	$html.= '<th>Aforo</th><th>Rechazo</th><th>Total</th><th>Importe</th><th>Pago Ant</th><th>R. Credito</th><th>Venta Total</th></tr>'; 
	$sumacargo=array(0,0,0,0);
	$x=0;
	$array_importes_plaza=array();
	$data=array();
	foreach($array_plazas as $k=>$v){
//		rowc();
//				$select= " SELECT SUM(IF(a.estatus!='C' AND a.tipo_pago='6',a.monto,0)) as pago,SUM(IF(a.estatus!='C' AND a.tipo_pago='2',a.monto,0)) as cre FROM pagos_caja a
//		LEFT JOIN vales_pago_anticipado b ON a.cve = b.pago
//		WHERE a.plaza='".$k."'";
//		$select.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
//		$res=mysql_query($select) or die(mysql_error());
//		$roww=mysql_fetch_array($res);
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0)),SUM(IF(a.estatus!='C' AND a.tipo_pago='6',a.monto,0)) as p_ant
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto),SUM(IF(a.estatus!='C' AND a.tipo_pago='6',a.monto,0)),SUM(IF(a.estatus!='C' AND a.tipo_pago='2',a.monto,0))
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		//$row[1]+=$row1[1];
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$array_importes_plaza[$k][0]=$row2[0];
		$array_importes_plaza[$k][1]=$row2[1];
		$array_importes_plaza[$k][2]=$row2[0]+$row2[1];
		$array_importes_plaza[$k][4]=$row1[2];
		$array_importes_plaza[$k][5]=$row1[3];
		$sumacargo[4]+=$row1[2];
		$sumacargo[5]+=$row1[3];
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$array_importes_plaza[$k][3]=$row[1]-$row1[1];
		$array_importes_plaza[$k][6]=$array_importes_plaza[$k][3]+$array_importes_plaza[$k][4]+$array_importes_plaza[$k][5];
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
		$sumacargo[6]+=$array_importes_plaza[$k][3]+$array_importes_plaza[$k][4]+$array_importes_plaza[$k][5];
		$data[$k][0] = $v;
		$data[$k][1] += round($row[1]-$row1[1],2);
	}
	foreach($array_plazas as $k=>$v){
//		rowc();
echo'<tr>';
//		if($x==0){
//			$html.= '<td rowspan="'.count($array_plazas).'">Capital.</td>';
//		}
		$html.= '<td>'.$array_tipo_plaza[$array_plazass[$k]].'</td>';
		$html.= '<td>'.htmlentities(utf8_encode($v)).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][0],0).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][1],0).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][2],0).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][3],2).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][4],2).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][5],2).'</td>';
		$html.= '<td align="right">'.number_format($array_importes_plaza[$k][6],2).'</td>';
		$html.= '</tr>';
		$x++;
	}
	/*echo '<tr><td colspan="6">&nbsp;</td></tr>';
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 2 ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$x=0;
	foreach($array_plazas as $k=>$v){
		rowc();
		if($x==0){
			echo '<td rowspan="'.count($array_plazas).'">Municipios</td>';
		}
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		echo '<td align="right">'.number_format($row2[0],0).'</td>';
		echo '<td align="right">'.number_format($row2[1],0).'</td>';
		echo '<td align="right">'.number_format($row2[0]+$row2[1],0).'</td>';
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		echo '<td align="right">'.number_format($row[1]-$row1[1],2).'</td>';
		echo '</tr>';
		$x++;
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
	}*/
	$c=4;
	$html.= '<tr>';
	$html.= '<td bgcoor="#E9F2F8" align="right" colspan="2">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		if($k>2) $decimal=2;
		else $decimal=0;
		$html.= '<td bcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	$html.= '</tr>';
	
	/*echo '<tr><td colspan="6">&nbsp;</td></tr>';
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 3 ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$x=0;
	$sumacargo2=array(0,0,0,0);
	foreach($array_plazas as $k=>$v){
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
		
		$sumacargo2[0]+=$row2[0];
		$sumacargo2[1]+=$row2[1];
		$sumacargo2[2]+=$row2[0]+$row2[1];
		$sumacargo2[3]+=$row[1]-$row1[1];
	}
	echo '<tr>';
	echo '<td bgcolor="#E9F2F8" align="center" colspan="2">&nbsp;Unidades Moviles</td>';
	foreach($sumacargo2 as $k=>$v){
		if($k>2) $decimal=2;
		else $decimal=0;
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';
	echo '<tr><td colspan="6">&nbsp;</td></tr>';
	echo '<tr>';
	echo '<td bgcolor="#E9F2F8" align="right" colspan="2">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		if($k>2) $decimal=2;
		else $decimal=0;
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';*/
	$html.= '</table>';
	echo $html;
	exit();
}


if($_POST['cmd']==101){
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
				$array_plazass[$row['cve']]=$row['tipo_plaza'];
	}
	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	echo '<tr bgcolo="#E9F2F8"><th>Ubicacion</th><th>Centro</th>';
	echo '<th>Aforo</th><th>Rechazo</th><th>Total</th><th>Importe</th><th>Pago Ant</th><th>R. Credito</th><th>Venta Total</th></tr>'; 
	$sumacargo=array(0,0,0,0);
	$x=0;
	foreach($array_plazas as $k=>$v){
		rowc();
		//if($x==0){
		//	echo '<td rowspan="'.count($array_plazas).'">Capital</td>';
	//	}
//				$select= " SELECT SUM(IF(a.estatus!='C' AND a.tipo_pago='6',a.monto,0)) as pago,SUM(IF(a.estatus!='C' AND a.tipo_pago='2',a.monto,0)) as cre FROM pagos_caja a
//		LEFT JOIN vales_pago_anticipado b ON a.cve = b.pago
//		WHERE a.plaza='".$k."'";
///		$select.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
//		$res=mysql_query($select) or die(mysql_error());
//		$roww=mysql_fetch_array($res);
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0)),SUM(IF(a.estatus!='C' AND a.tipo_pago='6',a.monto,0)) as p_ant
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto),SUM(IF(a.estatus!='C' AND a.tipo_pago='6',a.monto,0)),SUM(IF(a.estatus!='C' AND a.tipo_pago='2',a.monto,0))
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		//$row[1]+=$row1[1];
		$pago=$row1[2];
		$cre=$row1[3];
		echo '<td>'.$array_tipo_plaza[$array_plazass[$k]].'</td>';
		echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		echo '<td align="right">'.number_format($row2[0],0).'</td>';
		echo '<td align="right">'.number_format($row2[1],0).'</td>';
		echo '<td align="right">'.number_format($row2[0]+$row2[1],0).'</td>';
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		echo '<td align="right">'.number_format($row[1]-$row1[1],2).'</td>';
		echo '<td align="right">'.number_format($pago,2).'</td>';
		echo '<td align="right">'.number_format($cre,2).'</td>';
		$to=$row[1]-$row1[1]+$pago+$cre;
		echo '<td align="right">'.number_format($to,2).'</td>';
		echo '</tr>';
		$x++;
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
		$sumacargo[4]+=$pago;
		$sumacargo[5]+=$cre;
		$sumacargo[6]+=$to;
	}
	//echo '<tr><td colspan="6">&nbsp;</td></tr>';
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 2 ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$x=0;
	foreach($array_plazas as $k=>$v){
		rowc();
		if($x==0){
			echo '<td rowspan="'.count($array_plazas).'">Municipios</td>';
		}
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$html.= '<td>'.$array_tipo_plaza[$array_plazass[$k]].'</td>';
		echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		echo '<td align="right">'.number_format($row2[0],0).'</td>';
		echo '<td align="right">'.number_format($row2[1],0).'</td>';
		echo '<td align="right">'.number_format($row2[0]+$row2[1],0).'</td>';
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		echo '<td align="right">'.number_format($row[1]-$row1[1],2).'</td>';
		echo '</tr>';
		$x++;
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
	}
	$c=4;
	echo '<tr>';
	echo '<td bgcolo="#E9F2F8" align="right" colspan="2">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		if($k>2) $decimal=2;
		else $decimal=0;
		echo '<td bgcolo="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';
	
	/*echo '<tr><td colspan="6">&nbsp;</td></tr>';
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 3 ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$x=0;
	$sumacargo2=array(0,0,0,0);
	foreach($array_plazas as $k=>$v){
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
		
		$sumacargo2[0]+=$row2[0];
		$sumacargo2[1]+=$row2[1];
		$sumacargo2[2]+=$row2[0]+$row2[1];
		$sumacargo2[3]+=$row[1]-$row1[1];
	}
	echo '<tr>';
	echo '<td bgcolo="#E9F2F8" align="center" colspan="2">&nbsp;Unidades Moviles</td>';
	foreach($sumacargo2 as $k=>$v){
		if($k>2) $decimal=2;
		else $decimal=0;
		echo '<td bgcolo="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';
	echo '<tr><td colspan="6">&nbsp;</td></tr>';
	echo '<tr>';
	echo '<td bgcolo="#E9F2F8" align="right" colspan="2">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		if($k>2) $decimal=2;
		else $decimal=0;
		echo '<td bgcolo="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';*/
	echo '</table>';
	exit();
}
if($_POST['cmd']==100){
	require_once('../fpdf153/fpdf.php');
	$pdf = new FPDF('P','mm','LETTER');
	$pdf->AddPage();
	
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 1 ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'];
	}
	$pdf->SetFont("Arial","B",14);
	$pdf->Cell(190,5,"INFORME DE VENTAS",0,0,'C');
	$pdf->Ln();
	$pdf->SetFont("Arial","B",13);
	if($_POST['fecha_ini']!=$_POST['fecha_fin'])
		$pdf->Cell(190,5,"Del ".fecha_letra($_POST['fecha_ini'])." al ".fecha_letra($_POST['fecha_fin']),0,0,'C');
	else
		$pdf->Cell(190,5,fecha_letra($_POST['fecha_ini']),0,0,'C');
	$pdf->Ln();
	$pdf->SetFont("Arial","",8);
	$pdf->Cell(190,5,"(miles de pesos)",0,0,'C');
	$pdf->Ln();
	$pdf->SetFont("Arial","B",10);
	$pdf->Cell(30,5,"Tipo Plaza",1,0,'C');
	$pdf->Cell(70,5,"Centro",1,0,'C');
	$pdf->Cell(20,5,"Aforo",1,0,'C');
	$pdf->Cell(20,5,"Rechazo",1,0,'C');
	$pdf->Cell(20,5,"Total",1,0,'C');
	$pdf->Cell(30,5,"Importe",1,0,'C');
	
	$pdf->SetFont("Arial","",10);
	$sumacargo=array(0,0,0,0);
	$x=0;
	foreach($array_plazas as $k=>$v){
		$pdf->Ln();
		if($x==0){
			$pdf->Cell(30,4,"Capital",'LTR',0,'L');
		}
		else{
			$pdf->Cell(30,4,"",'LR',0,'L');
		}
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$pdf->Cell(70,4,$v,1,0,'L');
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$pdf->Cell(20,4,number_format($row2[0],0),1,0,'C');
		$pdf->Cell(20,4,number_format($row2[1],0),1,0,'C');
		$pdf->Cell(20,4,number_format($row2[0]+$row2[1],0),1,0,'C');
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$pdf->Cell(30,4,number_format($row[1]-$row1[1],2),1,0,'R');
		$x++;
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
	}
	$pdf->Ln();
	$pdf->Cell(30,4,"",'T',0,'L');
	
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 2 ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'];
	}
	$x=0;
	foreach($array_plazas as $k=>$v){
		$pdf->Ln();
		if($x==0){
			$pdf->Cell(30,4,"Municipios",'LTR',0,'L');
		}
		else{
			$pdf->Cell(30,4,"",'LR',0,'L');
		}
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$pdf->Cell(70,4,$v,1,0,'L');
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$pdf->Cell(20,4,number_format($row2[0],0),1,0,'C');
		$pdf->Cell(20,4,number_format($row2[1],0),1,0,'C');
		$pdf->Cell(20,4,number_format($row2[0]+$row2[1],0),1,0,'C');
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$pdf->Cell(30,4,number_format($row[1]-$row1[1],2),1,0,'R');
		$x++;
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
	}
	$pdf->Ln();
	$pdf->SetFont("Arial","B",10);
	$pdf->Cell(100,4,"Totales",'T',0,'L');
	foreach($sumacargo as $k=>$v){
		if($k>2) $pdf->Cell(30,4,number_format($v,0),0,0,'R');
		else $pdf->Cell(20,4,number_format($v,0),0,0,'C');
	}
	/*$pdf->Ln();
	
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' AND a.tipo_plaza = 3 ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$x=0;
	$sumacargo2=array(0,0,0,0);
	foreach($array_plazas as $k=>$v){
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C' AND a.tipo_pago NOT IN (2,6),a.monto,0)),SUM(IF(a.estatus='C',1,0))
			FROM cobro_engomado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select1= " SELECT COUNT(a.cve),SUM(a.monto)
			FROM pagos_caja as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' AND a.estatus!='C'";
		$res1=mysql_query($select1) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$row[1]+=$row1[1];
		$select = "SELECT SUM(IF(engomado NOT IN ($rechazos),1,0)), SUM(IF(engomado IN ($rechazos),1,0)) FROM certificados WHERE plaza='".$k."' AND fecha>='".$_POST['fecha_ini']."' AND fecha<='".$_POST['fecha_fin']."'";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		$select= " SELECT COUNT(a.cve),SUM(IF(a.estatus!='C',a.devolucion,0)),SUM(IF(a.estatus='C',1,0))
		FROM devolucion_certificado as a WHERE a.plaza='".$k."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		$res1=mysql_query($select) or die(mysql_error());
		$row1=mysql_fetch_array($res1);
		$sumacargo[0]+=$row2[0];
		$sumacargo[1]+=$row2[1];
		$sumacargo[2]+=$row2[0]+$row2[1];
		$sumacargo[3]+=$row[1]-$row1[1];
		
		$sumacargo2[0]+=$row2[0];
		$sumacargo2[1]+=$row2[1];
		$sumacargo2[2]+=$row2[0]+$row2[1];
		$sumacargo2[3]+=$row[1]-$row1[1];
	}
	$pdf->Ln();
	$pdf->SetFont("Arial","",10);
	$pdf->Cell(100,4,"Unidades Moviles",0,0,'C');
	foreach($sumacargo2 as $k=>$v){
		if($k>2) $pdf->Cell(30,4,number_format($v,0),0,0,'R');
		else $pdf->Cell(20,4,number_format($v,0),0,0,'C');
	}
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont("Arial","B",10);
	$pdf->Cell(100,4,"Totales",0,0,'L');
	foreach($sumacargo as $k=>$v){
		if($k>2) $pdf->Cell(30,4,number_format($v,0),0,0,'R');
		else $pdf->Cell(20,4,number_format($v,0),0,0,'C');
	}*/
	$pdf->Output();
	exit();
}

if($_POST['ajax']==1){
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND b.localidad_id = '".$_POST['localidad']."' ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
		$array_plazass[$row['cve']]=$row['tipo_plaza'];
	}
	$html='<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	$html.= '<tr bgcolor="#E9F2F8">
			 <th>Plaza</th>
			 <th>Total Vendidos</th>
			 <th>Total con Importe</th>
			 <th>Total Intentos</th>
			 <th>Total Cortesia</th>
			 <th>Total </br>Pago Anticipados</th>
			 <th>Entregados</th>
			 <th>Entregados</br>Cancelados</th></tr>'; 
	$sumacargo=array(0,0,0,0);
	$x=0;
	foreach($array_plazas as $k=>$v){
		rowc();
	$html.= '<td>'.htmlentities(utf8_encode($v)).'</td>';
		$select= " SELECT count(a.cve) as t_vendidos,a.*,b.cve as certificado, b.certificado as holograma,b.engomado as engomado_entrega, CONCAT(b.fecha,' ',b.hora) as fechaentrega, TIMEDIFF(IFNULL(CONCAT(b.fecha,' ',b.hora),NOW()),CONCAT(a.fecha,' ',a.hora)) as diferencia FROM cobro_engomado a LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' LEFT JOIN depositantes c ON c.cve = a.depositante AND c.plaza = a.plaza 
		WHERE a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and  a.plaza='".$k."' AND a.estatus!='C'";
		$res=mysql_query($select) or die(mysql_error());
		$roww1=mysql_fetch_array($res);
	$html.= '<td align="right">'.$roww1['t_vendidos'].'</td>';
		$select2= " SELECT count(a.cve) as t_importe,a.*,b.cve as certificado, b.certificado as holograma,b.engomado as engomado_entrega, CONCAT(b.fecha,' ',b.hora) as fechaentrega, TIMEDIFF(IFNULL(CONCAT(b.fecha,' ',b.hora),NOW()),CONCAT(a.fecha,' ',a.hora)) as diferencia FROM cobro_engomado a LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' LEFT JOIN depositantes c ON c.cve = a.depositante AND c.plaza = a.plaza WHERE a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and a.plaza='".$k."' AND a.estatus!='C' and a.tipo_pago not in(2,5,6,7)";
		$res2=mysql_query($select2) or die(mysql_error());
		$roww2=mysql_fetch_array($res2);
	$html.= '<td align="right">'.$roww2['t_importe'].'</td>';
		$select3= " SELECT count(a.cve) as t_intento,a.*,b.cve as certificado, b.certificado as holograma,b.engomado as engomado_entrega, CONCAT(b.fecha,' ',b.hora) as fechaentrega, TIMEDIFF(IFNULL(CONCAT(b.fecha,' ',b.hora),NOW()),CONCAT(a.fecha,' ',a.hora)) as diferencia FROM cobro_engomado a LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' LEFT JOIN depositantes c ON c.cve = a.depositante AND c.plaza = a.plaza WHERE a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.plaza='".$k."' AND a.estatus!='C' and  a.tipo_venta='1'";
		$res3=mysql_query($select3) or die(mysql_error());
		$roww3=mysql_fetch_array($res3);
	$html.= '<td align="right">'.$roww3['t_intento'].'</td>';	
//	$html.= '<td align="right">-'.$select3.'</td>';	
		$select4= " SELECT count(a.cve) as t_cortecia,a.*,b.cve as certificado, b.certificado as holograma,b.engomado as engomado_entrega, CONCAT(b.fecha,' ',b.hora) as fechaentrega, TIMEDIFF(IFNULL(CONCAT(b.fecha,' ',b.hora),NOW()),CONCAT(a.fecha,' ',a.hora)) as diferencia FROM cobro_engomado a LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' LEFT JOIN depositantes c ON c.cve = a.depositante AND c.plaza = a.plaza WHERE a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.plaza='".$k."' AND a.estatus!='C' and  a.tipo_venta='2'";
		$res4=mysql_query($select4) or die(mysql_error());
		$roww4=mysql_fetch_array($res4);
	$html.= '<td align="right">'.$roww4['t_cortecia'].'</td>';
	$select7= " SELECT count(a.cve) as t_anticipado,a.*,b.cve as certificado, b.certificado as holograma,b.engomado as engomado_entrega, CONCAT(b.fecha,' ',b.hora) as fechaentrega, TIMEDIFF(IFNULL(CONCAT(b.fecha,' ',b.hora),NOW()),CONCAT(a.fecha,' ',a.hora)) as diferencia FROM cobro_engomado a LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' LEFT JOIN depositantes c ON c.cve = a.depositante AND c.plaza = a.plaza WHERE a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.plaza='".$k."' AND a.tipo_pago='6'";
		$res7=mysql_query($select7) or die(mysql_error());
		$roww7=mysql_fetch_array($res7);
	$html.= '<td align="right">'.$roww7['t_anticipado'].'</td>';


		$select5= " SELECT count(a.cve) as t_entregado,a.*, b.tipo_venta, b.tipo_pago, d.nombre as nomdepositante, b.engomado as engomadoticket, b.tipo_combustible, b.factura FROM certificados a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante WHERE a.plaza='".$k."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus='A'";
		$res5=mysql_query($select5) or die(mysql_error());
		$roww5=mysql_fetch_array($res5);
	$html.= '<td align="right">'.$roww5['t_entregado'].'</td>';
		$select6= " SELECT count(cve) as t_cancel FROM certificados_cancelados WHERE plaza='".$k."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND engomado IN (19,2,3,5,1) ";
		$res6=mysql_query($select6) or die(mysql_error());
		$roww6=mysql_fetch_array($res6);
	$html.= '<td align="right">'.$roww6['t_cancel'].'</td>';
		$html.= '</tr>';
		$t1=$t1+$roww1['t_vendidos'];
		$t2=$t2+$roww2['t_importe'];
		$t3=$t3+$roww3['t_intento'];
		$t4=$t4+$roww4['t_cortecia'];
		$t5=$t5+$roww5['t_entregado'];
		$t6=$t6+$roww6['t_cancel'];
		$t7=$t7+$roww7['t_anticipado'];
		$x++;
	}
	$c=4;
	$html.= '<tr>';
	$html.= '<td bgcolor="#E9F2F8" align="right" colspan="">&nbsp;Total</td>';
		$html.= '<td bgcolor="#E9F2F8" align="right">&nbsp;'.$t1.'</td>';
		$html.= '<td bgcolor="#E9F2F8" align="right">&nbsp;'.$t2.'</td>';
		$html.= '<td bgcolor="#E9F2F8" align="right">&nbsp;'.$t3.'</td>';
		$html.= '<td bgcolor="#E9F2F8" align="right">&nbsp;'.$t4.'</td>';
		$html.= '<td bgcolor="#E9F2F8" align="right">&nbsp;'.$t7.'</td>';
		$html.= '<td bgcolor="#E9F2F8" align="right">&nbsp;'.$t5.'</td>';
		$html.= '<td bgcolor="#E9F2F8" align="right">&nbsp;'.$t6.'</td>';
	
	$html.= '</tr>';
	
	$html.= '</table>';
	echo $html;
	exit();
}


top($_SESSION);
	

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<!--<td><a href="#" onclick="atcr(\'#informe_x.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'#informe_x.php\',\'_blank\',101,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'#informe_x.php\',\'_blank\',102,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>-->';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<input type="hidden" name="localidad" id="localidad" value="1"></td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
bottom();
echo '
<Script language="javascript">
	function buscarRegistros(){
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","#informe_x.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&localidad="+document.getElementById("localidad").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
		
	';	
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(0,1); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
	

	</Script>
';

?>