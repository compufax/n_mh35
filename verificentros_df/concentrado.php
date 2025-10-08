<?php
include("main.php");

//ARREGLOS

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsUsuario=mysql_query("SELECT * FROM plazas where estatus!='I' ORDER BY numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}


$res=mysql_query("SELECT * FROM areas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_localidad[$row['cve']]=$row['nombre'];
}



$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);

$abono=0;

if($_POST['cmd']==101){
		$datos = explode('|',$_POST['reg']);
		$res = mysql_query("SELECT * FROM engomados WHERE cve='".$datos[0]."' ORDER BY nombre");
		$row=mysql_fetch_array($res);
		echo '<table>';
		echo '
			<tr>';
			//echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'concentrado.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
			//echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'\',\'_blank\',\'101\',\''.$_POST['reg'].'\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;HTML</a></td><td>&nbsp;</td>';
			echo'</tr>';
		echo '</table>';
		echo '<input type="hidden" name="engomado" value="'.$_POST['reg'].'">';
		echo '<input type="hidden" name="fecha_ini" value="'.$_POST['fecha_ini'].'">';
		echo '<input type="hidden" name="fecha_fin" value="'.$_POST['fecha_fin'].'">';
		echo '<br>';
		$tipo = ($datos[1] == 1) ? 'RV' : 'PA';
		$condicion = ($datos[1] == 1) ? '=' : '!=';
		//echo '<h2>Intentos '.$tipo.' del tipo '.$row['nombre'].' del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';
		echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><th>Placa</th><th>Cantidad</th></tr>';
		$total=0;
		$x=0;
		$res1=mysql_query("SELECT TRIM(b.placa),COUNT(b.cve)
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND b.tipo_venta=1 AND 
			a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'
			GROUP BY TRIM(b.placa)");
		/*and IFNULL((SELECT d.fecha FROM cobro_engomado d WHERE d.placa = b.placa AND d.anio = b.anio AND CONCAT(d.fecha,' ',d.hora) < CONCAT(b.fecha, ' ', b.hora) AND d.estatus != 'C' AND d.tipo_venta IN (0,2) LIMIT 1), '0000-00-00') ".$condicion." b.fecha*/
		while($row1 = mysql_fetch_array($res1)){
			//rowb();
			echo'<tr>';
			echo '<td><!--<a href="#" onClick="atcr(\'concentrado.php\',\'\',2,\''.$row1[0].'\')">-->'.$row1[0].'<!--</a>--></td>';
			echo '<td align="center">'.$row1[1].'</td>';
			echo '</tr>';
			$total+=$row1[1];
			$x++;
		}
		echo '<tr><th align="left">'.$x.' Registro(s)</th><th>'.$total.'</th></tr>';
		exit();
}
if($_POST['cmd']==100){
	require_once('../dompdf/dompdf_config.inc.php');
	$mes = substr($_POST['fecha_ini'],5,2);
	if(intval($mes)<=6){
		$fecha_semestre = substr($_POST['fecha_ini'],0,4).'-01-01';
	}
	else{
		$fecha_semestre = substr($_POST['fecha_ini'],0,4).'-07-01';
	}
	$html='<html><head>
      <style type="text/css">
	                    top  lado      ladoiz
		 @page{ margin: 5in 0.5in 1px 0.5in;}
		</style>
		 </head><body><h1 align="center">Plaza: '.$array_plazas[$_POST['plazausuario']].'</h2><h1 align="right">Fecha: '.fechaLocal().'-'.horaLocal().'</h2></br><h2>Inventario del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';
	$html.= '<table width="100%" border="1"  class="" style="font-size:13px">';
	$html.= '<tr bgcolr="#E9F2F8"><th>Tipo de Verificacion</th><th>Inicial</th><th>Compra</th><th>Total</th><th>Consumidos</th><th>Cancelados</th>
	<th>Cortesia</th><th>Intentos</th><th>Intentos PA</th><!--<th>F/Ingreso</th>--><th>Existencia</th><!--<th>Total Compra</th><th>Total Venta</th>--></tr>';
	$totales = array();
	$res = mysql_query("SELECT * FROM engomados WHERE entrega=1 ORDER BY nombre");
	while($row = mysql_fetch_array($res)){
		$html.='<tr>';
		$html.= '<td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
		$anterior=0;
		$compras=0;
		$consumidos=0;
		$cortesias=0;
		$cancelados=0;
		$total_compra=0;
		$ventas=0;
		$intentosrv=0;
		$intentospa=0;
		$t_com_ant=0;
		$filtro='';
		if($row['cve'] != 3 && $row['cve']!=19){
			$filtro = " AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='$fecha_semestre'";
			$filtro1 = " AND a.fecha>='$fecha_semestre'";
		}
		else{
			$res1=mysql_query("SELECT minimo,cantidad_restar,existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$row['cve']."' ORDER BY cve DESC LIMIT 1");
			$row1=mysql_fetch_array($res1);
			$anterior = $row1['existencia2016'];
		}
		$res1=mysql_query("SELECT SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)<'".$_POST['fecha_ini']."',1,0)) as anterior, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."',1,0)) as rango, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."',a.costo,0)) as total_compra
			FROM compra_certificados a 
			INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) <= '".$_POST['fecha_fin']."'".$filtro);
		$row1 = mysql_fetch_array($res1);
		$anterior+=$row1['anterior'];
		$compras=$row1['rango'];
		$total_compra=$row1['total_compra'];
		$res1=mysql_query("SELECT SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)<'".$_POST['fecha_ini']."',1,0)) as anterior, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta<1,1,0)) as rango, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta=2,1,0)) as cortesia, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta=1,1,0)) as intento
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) <= '".$_POST['fecha_fin']."'".$filtro);
		$row1 = mysql_fetch_array($res1);
		$anterior-=$row1['anterior'];
		$consumidos=$row1['rango'];
		$cortesias=$row1['cortesia'];
		//$intentos=$row1['intento'];
		$res1=mysql_query("SELECT b.placa, b.fecha, b.anio, IFNULL((SELECT d.fecha FROM cobro_engomado d WHERE d.placa = b.placa AND d.anio = b.anio AND CONCAT(d.fecha,' ',d.hora) < CONCAT(b.fecha, ' ', b.hora) AND d.estatus != 'C' AND d.tipo_venta IN (0,2) LIMIT 1), '0000-00-00') as fecha_venta
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$row['cve']."' AND b.tipo_venta=1 AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'".$filtro);
		while($row1 = mysql_fetch_array($res1))
		{
			if($row1['fecha'] == $row1['fecha_venta']) $intentosrv++;
			else $intentospa++;
		}
		$res1=mysql_query("SELECT SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)<'".$_POST['fecha_ini']."',1,0)) as anterior, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."',1,0)) as rango
			FROM certificados_cancelados a 
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) <= '".$_POST['fecha_fin']."'".$filtro);
		$row1 = mysql_fetch_array($res1);
		$anterior-=$row1['anterior'];
		$cancelados=$row1['rango'];
		$c=0;
		$html.= '<td align="right">'.number_format($anterior,0).'</td>';
		$totales[$c]+=$anterior;$c++;
		$html.= '<td align="right">'.number_format($compras,0).'</td>';
		$totales[$c]+=$compras;$c++;
		$t_com_ant=$anterior+$compras;
		$html.= '<td align="right">'.number_format($t_com_ant,0).'</td>';
		$totales[$c]+=$t_com_ant;$c++;
		$html.= '<td align="right">'.number_format($consumidos,0).'</td>';
		$totales[$c]+=$consumidos;$c++;
		$html.= '<td align="right">'.number_format($cancelados,0).'</td>';
		$totales[$c]+=$cancelados;$c++;
		$html.= '<td align="right">'.number_format($cortesias,0).'</td>';
		$totales[$c]+=$cortesias;$c++;
		/*$html.= '<td align="right">'.number_format($intentosrv,0).'</td>';
		$totales[$c]+=$intentosrv;$c++;
		$html.= '<td align="right">'.number_format($intentospa,0).'</td>';
		$totales[$c]+=$intentospa;$c++;*/
		//		echo '<td align="right"><a href="#" onClick="atcr(\'concentrado.php\',\'\',1,\''.$row['cve'].'|1\')">'.number_format($intentosrv,0).'</a></td>';
		if($row['cve']==19){
    	$html.= '<td align="right">'.number_format($intentospa + $intentosrv,0).'</td>';$totales[$c]+=$intentospa + $intentosrv;$c++;
		  }else{$c++;
		$html.='<td></td>';
		  }
	//	$totales[$c]+=$intentosrv;$c++;
	//	echo '<td align="right"><a href="#" onClick="atcr(\'concentrado.php\',\'\',1,\''.$row['cve'].'|2\')">'.number_format($intentospa,0).'</a></td>';
		  if($row['cve']!=19){
    	$html.='<td align="right">'.number_format($intentospa + $intentosrv,0).'</td>';$totales[$c]+=$intentospa + $intentosrv;$c++;
		  }else{$c++;
		$html.='<td></td>';
		  }
	//	$totales[$c]+=$intentospa;$c++;
//		$html.= '<td align="right"><a href="#" onClick="atcr(\'concentrado.php\',\'\',1,'.$row['cve'].')">'.number_format($intentos,0).'</a></td>';
        $fi=$cancelados + $cortesias + $intentosrv + $intentospa;
	//	$html.='<td align="right">'.number_format($fi,0).'</td>';
		$totales[$c]+=$fi;$c++;
		$html.= '<td align="right">'.number_format(($anterior+$compras-$consumidos-$cancelados-$cortesias-$intentospa-$intentosrv),0).'</td>';
		$totales[$c]+=($anterior+$compras-$consumidos-$cancelados-$cortesias-$intentospa-$intentosrv);$c++;
	//	$html.= '<td align="right">'.number_format($total_compra,2).'</td>';
		$totales[$c]+=$total_compra;$c++;
		$res1=mysql_query("SELECT SUM(a.monto) FROM cobro_engomado a WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND a.tipo_venta=0 AND a.tipo_pago NOT IN (2,6) AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'".$filtro1);
		$row1=mysql_fetch_array($res1);
		$ventas=$row1[0];
//		$html.= '<td align="right">'.number_format($ventas,2).'</td>';

		$tfi=$tfi + $fi;
		$totales[$c]+=$ventas;$c++;
		$html.= '</tr>';
	}
	$html.= '<tr bgcoor="#E9F2F8"><th>Totales</th>';
	for($i=0;$i<=9;$i++){
		if($i!=8){
	    $html.='<th align="right">'.number_format($totales[$i],0).'</th>';
		}
	}
	/*foreach($totales as $k=>$v){
	   if($k<=8){
		if($k<5) $html.= '<th align="right">'.number_format($v,0).'</th>';
		else $html.= '<th align="right">'.number_format($v,0).'</th>';
		}
	}*/
	//$html.= '<th align="right">'.number_format($tfi,0).'</th>';
	$html.= '</tr>';
	$res=mysql_query("SELECT SUM(monto) FROM pagos_caja a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro1");
	$row=mysql_fetch_array($res);
	/*$html.='<tr>';
	$html.= '<td>Pagos Anticipados</td><td colspan="8">&nbsp;</td><td align="right">'.number_format($row[0],2).'</td></tr>';
	$totales[8]+=$row[0];
	$res=mysql_query("SELECT SUM(monto) FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' AND tipo_pago=5 $filtro1");
	$row=mysql_fetch_array($res);
	$html.='<tr>';
	$html.= '<td>Tarjeta de Credito</td><td colspan="8">&nbsp;</td><td align="right">'.number_format($row[0],2).'</td></tr>';
	$res=mysql_query("SELECT SUM(monto) FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' AND tipo_venta=3 $filtro1");
	$row=mysql_fetch_array($res);
	$html.='<tr>';
	$html.= '<td>Reposicion</td><td colspan="8">&nbsp;</td><td align="right">'.number_format($row[0],2).'</td></tr>';
	$totales[8]+=$row[0];
	$res=mysql_query("SELECT SUM(a.recuperacion)  FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' $filtro1");
	$row=mysql_fetch_array($res);
	if($row[0]>0){
		$html.='<tr>';
		$html.= '<td>Recuperacion por Diferencia</td><td colspan="8">&nbsp;</td><td align="right">'.number_format($row[0],2).'</td></tr>';
		$totales[8]+=$row[0];
	}
	$res=mysql_query("SELECT SUM(a.devolucion)  FROM devolucion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' $filtro1");
	$row=mysql_fetch_array($res);
	if($row[0]>0){
		$html.='<tr>';
		$html.= '<td>Devolucion de Dinero</td><td colspan="8">&nbsp;</td><td align="right">-'.number_format($row[0],2).'</td></tr>';
		$totales[8]-=$row[0];
	}
	$html.= '<tr bgcolor="#E9F2F8"><th>Totales</th>';
	foreach($totales as $k=>$v){
		if($k<5) $html.= '<th align="right">'.number_format($v,0).'</th>';
		else $html.= '<th align="right">'.number_format($v,2).'</th>';
	}
	$html.= '</tr>';*/
	$html.= '</table></br></br></br><h3>&nbsp;</h3>';
	
	$html.= '<table width="100%" border="1" style="font-size:13px" class="">';
	$html.= '<tr bgclor="#E9F2F8"><th>Tipo de Verificacion</th><th>Consumidos</th><th>Total de Venta</th>';
	$totales = array();
	$res = mysql_query("SELECT * FROM engomados WHERE entrega=1 and cve!=19 ORDER BY nombre");
	while($row = mysql_fetch_array($res)){
		
		$html.= '<tr><td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
		$anterior=0;
		$compras=0;
		$consumidos=0;
		$cortesias=0;
		$cancelados=0;
		$total_compra=0;
		$ventas=0;
		$intentos=0;
		$filtro='';
		$filtro1='';
		$filtro2='';
		if($row['cve'] != 3 && $row['cve']!=19){
			$filtro = " AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='$fecha_semestre'";
			$filtro1 = " AND a.fecha>='$fecha_semestre'";
			$filtro2 = " AND IF(a.fecha_compra<c.fecha_ini,c.fecha_ini,a.fecha_compra)>='$fecha_semestre'";
		}
		else{
			$res1=mysql_query("SELECT minimo,cantidad_restar,existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$row['cve']."' ORDER BY cve DESC LIMIT 1");
			$row1=mysql_fetch_array($res1);
			$anterior = $row1['existencia2016'];
		}
		$res1=mysql_query("SELECT SUM(IF(IF(a.fecha_compra<c.fecha_ini,c.fecha_ini,a.fecha_compra)<'".$_POST['fecha_ini']."',1,0)) as anterior, SUM(IF(IF(a.fecha_compra<c.fecha_ini,c.fecha_ini,a.fecha_compra)>='".$_POST['fecha_ini']."',1,0)) as rango, SUM(IF(IF(a.fecha_compra<c.fecha_ini,c.fecha_ini,a.fecha_compra)>='".$_POST['fecha_ini']."',a.costo,0)) as total_compra
			FROM compra_certificados a 
			INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND IF(a.fecha_compra<c.fecha_ini,c.fecha_ini,a.fecha_compra) <= '".$_POST['fecha_fin']."'".$filtro2);
		$row1 = mysql_fetch_array($res1);
		$anterior+=$row1['anterior'];
		$compras=$row1['rango'];
		$total_compra=$row1['total_compra'];
		$res1=mysql_query("SELECT SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)<'".$_POST['fecha_ini']."',1,0)) as anterior, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta<1,1,0)) as rango, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta=2,1,0)) as cortesia, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta=1,1,0)) as intento
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) <= '".$_POST['fecha_fin']."'".$filtro);
		$row1 = mysql_fetch_array($res1);
		$anterior-=$row1['anterior'];
		$consumidos=$row1['rango'];
		$cortesias=$row1['cortesia'];
		//$intentos=$row1['intento'];
		$res1=mysql_query("SELECT SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)<'".$_POST['fecha_ini']."',1,0)) as anterior, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."',1,0)) as rango
			FROM certificados_cancelados a 
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) <= '".$_POST['fecha_fin']."'".$filtro) or die(mysql_error());
		$row1 = mysql_fetch_array($res1);
		$anterior-=$row1['anterior'];
		$cancelados=$row1['rango'];
		$c=0;
	//	$html.= '<td align="right">'.number_format($anterior,0).'</td>';
		$totales[$c]+=$anterior;$c++;
//		$html.= '<td align="right">'.number_format($compras,0).'</td>';
		$totales[$c]+=$compras;$c++;
		$html.= '<td align="right">'.number_format($consumidos,0).'</td>';
		$totales[$c]+=	$consumidos;$c++;
//		$html.= '<td align="right">'.number_format($cancelados,0).'</td>';
		$totales[$c]+=$cancelados;$c++;
//		$html.= '<td align="right">'.number_format($cortesias,0).'</td>';
		$totales[$c]+=$cortesias;$c++;
//		$html.= '<td align="right"><a href="#" onClick="atcr(\'concentrado.php\',\'\',1,'.$row['cve'].')">'.number_format($intentos,0).'</a></td>';
		$totales[$c]+=$intentos;$c++;
//		$html.= '<td align="right">'.number_format(($anterior+$compras-$consumidos-$cancelados-$cortesias-$intentos),0).'</td>';
		$totales[$c]+=($anterior+$compras-$consumidos-$cancelados-$cortesias);$c++;
		//$html.= '<td align="right">'.number_format($total_compra,2).'</td>';
		$t_venta=$consumidos * 472;
		$html.= '<td align="right">'.number_format($t_venta,0).'</td>';
		$totales[$c]+=$total_compra;$c++;
		$res1=mysql_query("SELECT SUM(a.monto) FROM cobro_engomado a WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND a.tipo_venta=0 AND a.tipo_pago NOT IN (2,6) AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'".$filtro1);
		$row1=mysql_fetch_array($res1);
		$ventas=$row1[0];
		$t1=$t1 + $consumidos;
		$t2=$t2 + $t_venta;
		//$html.= '<td align="right">'.number_format($ventas,2).'</td>';
		$totales[$c]+=$ventas;$c++;
		$html.= '</tr>';
	}
		$html.='<tr>
	     <td align="right">Total</td>
		 <td align="right">'.number_format($t1,0).'</td>
		 <td align="right">'.number_format($t2,2).'</td>
	     </tr>';
	$html.='</table>';

	$html .= '<br>';

	$html .= '<table width="100%" border="1" style="font-size:13px" class="">';
	$html .= '<tr bgcolr="#E9F2F8"><th>&nbsp;</th><th>Contado</th><th>Credito</th><th>Pago Anticipado</th><th>Tarjeta de Credito</th><th>Intento</th><th>Cortesia</th><th>Aforo</th></tr>';
	$res = mysql_query("SELECT IF(a.engomado!=19,0,1) as tipo, 
		SUM(IF(b.tipo_venta = 0 AND b.tipo_pago = 1, 1, 0)) as contado, 
		SUM(IF(b.tipo_venta = 0 AND b.tipo_pago = 2, 1, 0)) as credito,
		SUM(IF(b.tipo_venta = 0 AND b.tipo_pago = 5, 1, 0)) as tarjeta_credito,
		SUM(IF(b.tipo_venta = 0 AND b.tipo_pago = 6, 1, 0)) as pago_anticipado,
		SUM(IF(b.tipo_venta = 1, 1, 0)) as intento,
		SUM(IF(b.tipo_venta = 2, 1, 0)) as cortesia,
		COUNT(a.cve) as aforo FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'
		GROUP BY IF(a.engomado!=19,0,1)");
	$array_renglon = array();
	while($row = mysql_fetch_assoc($res)){
		$array_renglon[$row['tipo']] = $row;
	}
	$array_totales = array();
	$html .= '<tr><td>Holograma</td>
	<td align="right">'.number_format($array_renglon[0]['contado'],0).'</td>
	<td align="right">'.number_format($array_renglon[0]['credito'],0).'</td>
	<td align="right">'.number_format($array_renglon[0]['pago_anticipado'],0).'</td>
	<td align="right">'.number_format($array_renglon[0]['tarjeta_credito'],0).'</td>
	<td align="right">'.number_format($array_renglon[0]['intento'],0).'</td>
	<td align="right">'.number_format($array_renglon[0]['cortesia'],0).'</td>
	<td align="right">'.number_format($array_renglon[0]['aforo'],0).'</td></tr>';
	$array_totales[0]+=$array_renglon[0]['contado'];
	$array_totales[1]+=$array_renglon[0]['credito'];
	$array_totales[2]+=$array_renglon[0]['pago_anticipado'];
	$array_totales[3]+=$array_renglon[0]['tarjeta_credito'];
	$array_totales[4]+=$array_renglon[0]['intento'];
	$array_totales[5]+=$array_renglon[0]['cortesia'];
	$array_totales[6]+=$array_renglon[0]['aforo'];
	$html .= '<tr><td>Rechazo</td>
	<td align="right">'.number_format($array_renglon[1]['contado'],0).'</td>
	<td align="right">'.number_format($array_renglon[1]['credito'],0).'</td>
	<td align="right">'.number_format($array_renglon[1]['pago_anticipado'],0).'</td>
	<td align="right">'.number_format($array_renglon[1]['tarjeta_credito'],0).'</td>
	<td align="right">'.number_format($array_renglon[1]['intento'],0).'</td>
	<td align="right">'.number_format($array_renglon[1]['cortesia'],0).'</td>
	<td align="right">'.number_format($array_renglon[1]['aforo'],0).'</td></tr>';
	$array_totales[0]+=$array_renglon[1]['contado'];
	$array_totales[1]+=$array_renglon[1]['credito'];
	$array_totales[2]+=$array_renglon[1]['pago_anticipado'];
	$array_totales[3]+=$array_renglon[1]['tarjeta_credito'];
	$array_totales[4]+=$array_renglon[1]['intento'];
	$array_totales[5]+=$array_renglon[1]['cortesia'];
	$array_totales[6]+=$array_renglon[1]['aforo'];

	$res = mysql_query("SELECT 
		SUM(IF(b.tipo_venta = 0 AND b.tipo_pago = 1, 1, 0)) as contado, 
		SUM(IF(b.tipo_venta = 0 AND b.tipo_pago = 2, 1, 0)) as credito,
		SUM(IF(b.tipo_venta = 0 AND b.tipo_pago = 5, 1, 0)) as tarjeta_credito,
		SUM(IF(b.tipo_venta = 0 AND b.tipo_pago = 6, 1, 0)) as pago_anticipado,
		SUM(IF(b.tipo_venta = 1, 1, 0)) as intento,
		SUM(IF(b.tipo_venta = 2, 1, 0)) as cortesia,
		COUNT(a.cve) as aforo FROM cobro_engomado b 
		LEFT JOIN certificados a ON b.plaza = a.plaza AND b.cve = a.ticket AND a.estatus!='C'  AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'
		WHERE b.plaza = '".$_POST['plazausuario']."' AND b.estatus!='C' AND b.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND ISNULL(a.cve)");
	$row = mysql_fetch_assoc($res);
	$html .= '<tr><td>Cobro sin verificar</td>
	<td align="right">'.number_format($row['contado'],0).'</td>
	<td align="right">'.number_format($row['credito'],0).'</td>
	<td align="right">'.number_format($row['pago_anticipado'],0).'</td>
	<td align="right">'.number_format($row['tarjeta_credito'],0).'</td>
	<td align="right">'.number_format($row['intento'],0).'</td>
	<td align="right">'.number_format($row['cortesia'],0).'</td>
	<td align="right">'.number_format($row['aforo'],0).'</td></tr>';
	$array_totales[0]+=$row['contado'];
	$array_totales[1]+=$row['credito'];
	$array_totales[2]+=$row['pago_anticipado'];
	$array_totales[3]+=$row['tarjeta_credito'];
	$array_totales[4]+=$row['intento'];
	$array_totales[5]+=$row['cortesia'];
	$array_totales[6]+=$row['aforo'];
	$html .= '<tr bgcoor="#E9F2F8"><th>Totales</th>';
	foreach($array_totales as $t) $html .= '<th align="right">'.number_format($t,0).'</th>';
	$html .= '</tr></table>';
	
	/*$html.= '<br>';
	$html.= '<h2>Desglose de Certificados Entregados por pagos Anticipados</h2>';
	$html.= '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="" style="font-size:15px">';
	$html.= '<tr bgcolor="#E9F2F8"><th>Tipo de Verificacion</th><th>Cantidad Entregada</th><th>Cortesia</th><th>Intentos</th><th>Total Importe</th></tr>';
	$totales = array();
	$res = mysql_query("SELECT * FROM engomados WHERE entrega=1 ORDER BY nombre");
	while($row = mysql_fetch_array($res)){
		$html.='<tr>';
		$html.= '<td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
		$res1 = mysql_query("SELECT COUNT(a.cve),SUM(b.monto),SUM(IF(b.tipo_venta=1,1,0)),SUM(IF(b.tipo_venta=2,1,0)) FROM certificados a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket  WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus!='C' AND a.engomado='".$row['cve']."' AND b.tipo_pago=6 $filtro1");
		$row1 = mysql_fetch_array($res1);
		$html.= '<td align="right">'.number_format($row1[0],0).'</td>';
		$html.= '<td align="right">'.number_format($row1[3],0).'</td>';
		$html.= '<td align="right">'.number_format($row1[2],0).'</td>';
		$html.= '<td align="right">'.number_format($row1[1],2).'</td>';
		$html.= '</tr>';
		$totales[0]+=$row1[0];
		$totales[1]+=$row1[3];
		$totales[2]+=$row1[2];
		$totales[3]+=$row1[1];
	}
	$html.= '<tr bgcolor="#E9F2F8"><th>Totales</th>';
	foreach($totales as $k=>$v){
		if($k<2) $html.= '<th align="right">'.number_format($v,0).'</th>';
		else $html.= '<th align="right">'.number_format($v,2).'</th>';
	}
	$html.= '</tr>';
	$html.= '</table>';*/$html.='</body></html>';
	$mipdf = new DOMPDF();
//	$mipdf->margin: "0";
	//$mipdf->set_paper("A4", "portrait");
//	$mipdf->set_paper("A4", "portrait");
	$mipdf->set_paper("Legal", "landscape");
	$mipdf->load_html($html);
	$mipdf->render();
	$mipdf ->stream();
	exit();
}
if($_POST['ajax']==1){

	
	$mes = substr($_POST['fecha_ini'],5,2);
	if(intval($mes)<=6){
		$fecha_semestre = substr($_POST['fecha_ini'],0,4).'-01-01';
	}
	else{
		$fecha_semestre = substr($_POST['fecha_ini'],0,4).'-07-01';
	}
	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Tipo de Verificacion</th><th>Inicial</th><th>Compra</th><th>Total</th><th>Consumidos</th><th>Cancelados</th>
	<th>Cortesia</th><th>Intentos</th><th>Intentos PA</th><!--<th>Intentos PA</th><th>S/Ingreso</th>--><th>Existencia</th><!--<th>Total Compra</th><th>Total Venta</th>--></tr>';
	$totales = array();
	$res = mysql_query("SELECT * FROM engomados WHERE entrega=1 ORDER BY nombre");
	while($row = mysql_fetch_array($res)){
		rowb();
		echo '<td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
		$anterior=0;
		$compras=0;
		$consumidos=0;
		$cortesias=0;
		$cancelados=0;
		$total_compra=0;
		$ventas=0;
		$intentosrv=0;
		$intentospa=0;
		$t_com_ant=0;
		$filtro='';
		$filtro1='';
		$filtro2='';
		if($row['cve'] != 3 && $row['cve']!=19){
			$filtro = " AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='$fecha_semestre'";
			$filtro1 = " AND a.fecha>='$fecha_semestre'";
			$filtro2 = " AND IF(a.fecha_compra<c.fecha_ini,c.fecha_ini,a.fecha_compra)>='$fecha_semestre'";
		}
		else{
			$res1=mysql_query("SELECT minimo,cantidad_restar,existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$row['cve']."' ORDER BY cve DESC LIMIT 1");
			$row1=mysql_fetch_array($res1);
			$anterior = $row1['existencia2016'];
		}
		$res1=mysql_query("SELECT SUM(IF(IF(a.fecha_compra<c.fecha_ini,c.fecha_ini,a.fecha_compra)<'".$_POST['fecha_ini']."',1,0)) as anterior, SUM(IF(IF(a.fecha_compra<c.fecha_ini,c.fecha_ini,a.fecha_compra)>='".$_POST['fecha_ini']."',1,0)) as rango, SUM(IF(IF(a.fecha_compra<c.fecha_ini,c.fecha_ini,a.fecha_compra)>='".$_POST['fecha_ini']."',a.costo,0)) as total_compra
			FROM compra_certificados a 
			INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND IF(a.fecha_compra<c.fecha_ini,c.fecha_ini,a.fecha_compra) <= '".$_POST['fecha_fin']."'".$filtro2);
		$row1 = mysql_fetch_array($res1);
		$anterior+=$row1['anterior'];
		$compras=$row1['rango'];
		$total_compra=$row1['total_compra'];
		$res1=mysql_query("SELECT SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)<'".$_POST['fecha_ini']."',1,0)) as anterior, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta<1,1,0)) as rango, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta=2,1,0)) as cortesia, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta=1,1,0)) as intento
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) <= '".$_POST['fecha_fin']."'".$filtro);
		$row1 = mysql_fetch_array($res1);
		$anterior-=$row1['anterior'];
		$consumidos=$row1['rango'];
		$cortesias=$row1['cortesia'];
		//$intentos=$row1['intento'];
		$res1=mysql_query("SELECT b.placa, b.fecha, b.anio, IFNULL((SELECT d.fecha FROM cobro_engomado d WHERE d.placa = b.placa AND d.anio = b.anio AND CONCAT(d.fecha,' ',d.hora) < CONCAT(b.fecha, ' ', b.hora) AND d.estatus != 'C' AND d.tipo_venta IN (0,2) LIMIT 1), '0000-00-00') as fecha_venta
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$row['cve']."' AND b.tipo_venta=1 AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'".$filtro);
		while($row1 = mysql_fetch_array($res1))
		{
			if($row1['fecha'] == $row1['fecha_venta']) $intentosrv++;
			else $intentospa++;
		}
		$res1=mysql_query("SELECT SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)<'".$_POST['fecha_ini']."',1,0)) as anterior, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."',1,0)) as rango
			FROM certificados_cancelados a 
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) <= '".$_POST['fecha_fin']."'".$filtro) or die(mysql_error());
		$row1 = mysql_fetch_array($res1);
		$anterior-=$row1['anterior'];
		$cancelados=$row1['rango'];
		$c=0;
		echo '<td align="right">'.number_format($anterior,0).'</td>';
		$totales[$c]+=$anterior;$c++;
		echo '<td align="right">'.number_format($compras,0).'</td>';
		$totales[$c]+=$compras;$c++;
		$t_com_ant=$anterior+$compras;
		echo '<td align="right">'.number_format($t_com_ant,0).'</td>';
		$totales[$c]+=$t_com_ant;$c++;
		echo '<td align="right"><a href="#" onClick="atcr(\'concentrado.php\',\'\',3,\''.$row['cve'].'|1\')">'.number_format($consumidos,0).'</a></td>';
		$totales[$c]+=	$consumidos;$c++;
		echo '<td align="right"><a href="#" onClick="atcr(\'concentrado.php\',\'\',3,\''.$row['cve'].'|2\')">'.number_format($cancelados,0).'</a></td>';
		$totales[$c]+=$cancelados;$c++;
		echo '<td align="right"><a href="#" onClick="atcr(\'concentrado.php\',\'\',3,\''.$row['cve'].'|3\')">'.number_format($cortesias,0).'</a></td>';
		$totales[$c]+=$cortesias;$c++;
//		echo '<td align="right"><a href="#" onClick="atcr(\'concentrado.php\',\'\',1,\''.$row['cve'].'|1\')">'.number_format($intentosrv,0).'</a></td>';
		if($row['cve']==19){
    	echo '<td align="right"><a href="#" onClick="atcr(\'concentrado.php\',\'\',1,\''.$row['cve'].'|1\')">'.number_format($intentospa + $intentosrv,0).'</a></td>';$totales[$c]+=$intentospa + $intentosrv;$c++;
		  }else{$c++;
		echo'<td></td>';
		  }
	//	$totales[$c]+=$intentosrv;$c++;
	//	echo '<td align="right"><a href="#" onClick="atcr(\'concentrado.php\',\'\',1,\''.$row['cve'].'|2\')">'.number_format($intentospa,0).'</a></td>';
		  if($row['cve']!=19){
    	echo '<td align="right"><a href="#" onClick="atcr(\'concentrado.php\',\'\',1,\''.$row['cve'].'|1\')">'.number_format($intentospa + $intentosrv,0).'</a></td>';$totales[$c]+=$intentospa + $intentosrv;$c++;
		  }else{$c++;
		echo'<td></td>';
		  }
	//	$totales[$c]+=$intentospa;$c++;
		$fi=$cancelados + $cortesias + $intentosrv + $intentospa;
	//	echo '<td align="right">****'.number_format($fi,0).'</td>';
		$totales[$c]+=$fi;$c++;
		echo '<td align="right">'.number_format(($anterior+$compras-$consumidos-$cancelados-$cortesias-$intentospa-$intentosrv),0).'</td>';
		$totales[$c]+=($anterior+$compras-$consumidos-$cancelados-$cortesias-$intentospa-$intentosrv);$c++;
//		$totales[$c]+=($anterior+$compras-$consumidos-$cancelados-$cortesias);$c++;
		//echo '<td align="right">'.number_format($total_compra,2).'</td>';
		$totales[$c]+=$total_compra;$c++;
		$res1=mysql_query("SELECT SUM(a.monto) FROM cobro_engomado a WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND a.tipo_venta=0 AND a.tipo_pago NOT IN (2,6) AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'".$filtro1);
		$row1=mysql_fetch_array($res1);
		$ventas=$row1[0];
		//echo '<td align="right">'.number_format($ventas,2).'</td>';
		
		$totales[$c]+=$ventas;$c++;
		echo '</tr>';
	}
	echo '<tr bgcolor="#E9F2F8"><th>Totales</th>';
	for($i=0;$i<=9;$i++){
		if($i!=8){
		echo '<th align="right">'.number_format($totales[$i],0).'</th>';
		}
	}
	/*foreach($totales as $k=>$v){
		if($k<=8){
		echo '<th align="right">'.number_format($v,0).'-'.$k.'</th>';
		}
	}*/
	echo '</tr>';
	$res=mysql_query("SELECT SUM(monto) FROM pagos_caja a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' $filtro1");
	$row=mysql_fetch_array($res);
	rowb();
//	echo '<td>Pagos Anticipados</td><td colspan="7">&nbsp;</td><td align="right">'.number_format($row[0],2).'</td></tr>';
	$totales[8]+=$row[0];
	$res=mysql_query("SELECT SUM(monto) FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' AND tipo_pago=5 $filtro1");
	$row=mysql_fetch_array($res);
	rowb();
	//echo '<td>Tarjeta de Credito</td><td colspan="7">&nbsp;</td><td align="right">'.number_format($row[0],2).'</td></tr>';
	$res=mysql_query("SELECT SUM(monto) FROM cobro_engomado a WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND estatus!='C' AND tipo_venta=3 $filtro1");
	$row=mysql_fetch_array($res);
	rowb();
//	echo '<td>Reposicion</td><td colspan="7">&nbsp;</td><td align="right">'.number_format($row[0],2).'</td></tr>';
	$totales[8]+=$row[0];
	$res=mysql_query("SELECT SUM(a.recuperacion)  FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' $filtro1");
	$row=mysql_fetch_array($res);
	if($row[0]>0){
		rowb();
//		echo '<td>Recuperacion por Diferencia</td><td colspan="7">&nbsp;</td><td align="right">'.number_format($row[0],2).'</td></tr>';
		$totales[8]+=$row[0];
	}
	$res=mysql_query("SELECT SUM(a.devolucion)  FROM devolucion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' $filtro1");
	$row=mysql_fetch_array($res);
	if($row[0]>0){
		rowb();
//		echo '<td>Devolucion de Dinero</td><td colspan="7">&nbsp;</td><td align="right">-'.number_format($row[0],2).'</td></tr>';
		$totales[8]-=$row[0];
	}
/* 	echo '<tr bgcolor="#E9F2F8"><th>Totales</th>';
	foreach($totales as $k=>$v){
	    if($k<8){
		if($k<5) echo '<th align="right">'.number_format($v,0).'</th>';
		else echo '<th align="right">'.number_format($v,2).'</th>';
		}
	}
	echo '</tr>';*/
	echo '</table></br></br></br>';
//	if($_POST['cveusuario']=1){
	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Tipo de Verificacion</th><th>Consumidos</th><th>Total de Venta</th>';
	$totales = array();
	$res = mysql_query("SELECT * FROM engomados WHERE entrega=1 and cve!=19 ORDER BY nombre");
	while($row = mysql_fetch_array($res)){
		rowb();
		echo '<td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
		$anterior=0;
		$compras=0;
		$consumidos=0;
		$cortesias=0;
		$cancelados=0;
		$total_compra=0;
		$ventas=0;
		$intentos=0;
		$filtro='';
		$filtro1='';
		$filtro2='';
		if($row['cve'] != 3 && $row['cve']!=19){
			$filtro = " AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='$fecha_semestre'";
			$filtro1 = " AND a.fecha>='$fecha_semestre'";
			$filtro2 = " AND IF(a.fecha_compra<c.fecha_ini,c.fecha_ini,a.fecha_compra)>='$fecha_semestre'";
		}
		else{
			$res1=mysql_query("SELECT minimo,cantidad_restar,existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$row['cve']."' ORDER BY cve DESC LIMIT 1");
			$row1=mysql_fetch_array($res1);
			$anterior = $row1['existencia2016'];
		}
		$res1=mysql_query("SELECT SUM(IF(IF(a.fecha_compra<c.fecha_ini,c.fecha_ini,a.fecha_compra)<'".$_POST['fecha_ini']."',1,0)) as anterior, SUM(IF(IF(a.fecha_compra<c.fecha_ini,c.fecha_ini,a.fecha_compra)>='".$_POST['fecha_ini']."',1,0)) as rango, SUM(IF(IF(a.fecha_compra<c.fecha_ini,c.fecha_ini,a.fecha_compra)>='".$_POST['fecha_ini']."',a.costo,0)) as total_compra
			FROM compra_certificados a 
			INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND IF(a.fecha_compra<c.fecha_ini,c.fecha_ini,a.fecha_compra) <= '".$_POST['fecha_fin']."'".$filtro2);
		$row1 = mysql_fetch_array($res1);
		$anterior+=$row1['anterior'];
		$compras=$row1['rango'];
		$total_compra=$row1['total_compra'];
		$res1=mysql_query("SELECT SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)<'".$_POST['fecha_ini']."',1,0)) as anterior, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta<1,1,0)) as rango, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta=2,1,0)) as cortesia, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta=1,1,0)) as intento
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) <= '".$_POST['fecha_fin']."'".$filtro);
		$row1 = mysql_fetch_array($res1);
		$anterior-=$row1['anterior'];
		$consumidos=$row1['rango'];
		$cortesias=$row1['cortesia'];
		$intentos=$row1['intento'];
		$res1=mysql_query("SELECT SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)<'".$_POST['fecha_ini']."',1,0)) as anterior, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."',1,0)) as rango
			FROM certificados_cancelados a 
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) <= '".$_POST['fecha_fin']."'".$filtro) or die(mysql_error());
		$row1 = mysql_fetch_array($res1);
		$anterior-=$row1['anterior'];
		$cancelados=$row1['rango'];
		$c=0;
	//	echo '<td align="right">'.number_format($anterior,0).'</td>';
		$totales[$c]+=$anterior;$c++;
//		echo '<td align="right">'.number_format($compras,0).'</td>';
		$totales[$c]+=$compras;$c++;
		echo '<td align="right">'.number_format($consumidos,0).'</td>';
		$totales[$c]+=	$consumidos;$c++;
//		echo '<td align="right">'.number_format($cancelados,0).'</td>';
		$totales[$c]+=$cancelados;$c++;
//		echo '<td align="right">'.number_format($cortesias,0).'</td>';
		$totales[$c]+=$cortesias;$c++;
//		echo '<td align="right"><a href="#" onClick="atcr(\'concentrado.php\',\'\',1,'.$row['cve'].')">'.number_format($intentos,0).'</a></td>';
		$totales[$c]+=$intentos;$c++;
//		echo '<td align="right">'.number_format(($anterior+$compras-$consumidos-$cancelados-$cortesias-$intentos),0).'</td>';
		$totales[$c]+=($anterior+$compras-$consumidos-$cancelados-$cortesias);$c++;
		//echo '<td align="right">'.number_format($total_compra,2).'</td>';
		$t_venta=$consumidos * 472;
		echo '<td align="right">'.number_format($t_venta,0).'</td>';
        $t1=$t1 + $consumidos;
		$t2=$t2 + $t_venta;
		$totales[$c]+=$total_compra;$c++;
		$res1=mysql_query("SELECT SUM(a.monto) FROM cobro_engomado a WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND a.tipo_venta=0 AND a.tipo_pago NOT IN (2,6) AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'".$filtro1);
		$row1=mysql_fetch_array($res1);
		$ventas=$row1[0];
		//echo '<td align="right">'.number_format($ventas,2).'</td>';
		$totales[$c]+=$ventas;$c++;
		echo '</tr>';
	}
	echo'<tr>
	     <td align="right">Total</td>
		 <td align="right">'.number_format($t1,0).'</td>
		 <td align="right">'.number_format($t2,2).'</td>
	     </tr>';
	echo'</table>';
	
	echo '<br>';

	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Contado</th><th>Credito</th><th>Pago Anticipado</th><th>Tarjeta de Credito</th><th>Intento</th><th>Cortesia</th><th>Aforo</th></tr>';
	$res = mysql_query("SELECT IF(a.engomado!=19,0,1) as tipo, 
		SUM(IF(b.tipo_venta = 0 AND b.tipo_pago = 1, 1, 0)) as contado, 
		SUM(IF(b.tipo_venta = 0 AND b.tipo_pago = 2, 1, 0)) as credito,
		SUM(IF(b.tipo_venta = 0 AND b.tipo_pago = 5, 1, 0)) as tarjeta_credito,
		SUM(IF(b.tipo_venta = 0 AND b.tipo_pago = 6, 1, 0)) as pago_anticipado,
		SUM(IF(b.tipo_venta = 1, 1, 0)) as intento,
		SUM(IF(b.tipo_venta = 2, 1, 0)) as cortesia,
		COUNT(a.cve) as aforo FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		WHERE a.plaza = '".$_POST['plazausuario']."' AND a.estatus!='C' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'
		GROUP BY IF(a.engomado!=19,0,1)");
	$array_renglon = array();
	while($row = mysql_fetch_assoc($res)){
		$array_renglon[$row['tipo']] = $row;
	}
	$array_totales = array();
	rowb();
	echo '<td>Holograma</td>
	<td align="right">'.number_format($array_renglon[0]['contado'],0).'</td>
	<td align="right">'.number_format($array_renglon[0]['credito'],0).'</td>
	<td align="right">'.number_format($array_renglon[0]['pago_anticipado'],0).'</td>
	<td align="right">'.number_format($array_renglon[0]['tarjeta_credito'],0).'</td>
	<td align="right">'.number_format($array_renglon[0]['intento'],0).'</td>
	<td align="right">'.number_format($array_renglon[0]['cortesia'],0).'</td>
	<td align="right">'.number_format($array_renglon[0]['aforo'],0).'</td></tr>';
	$array_totales[0]+=$array_renglon[0]['contado'];
	$array_totales[1]+=$array_renglon[0]['credito'];
	$array_totales[2]+=$array_renglon[0]['pago_anticipado'];
	$array_totales[3]+=$array_renglon[0]['tarjeta_credito'];
	$array_totales[4]+=$array_renglon[0]['intento'];
	$array_totales[5]+=$array_renglon[0]['cortesia'];
	$array_totales[6]+=$array_renglon[0]['aforo'];
	rowb();
	echo '<td>Rechazo</td>
	<td align="right">'.number_format($array_renglon[1]['contado'],0).'</td>
	<td align="right">'.number_format($array_renglon[1]['credito'],0).'</td>
	<td align="right">'.number_format($array_renglon[1]['pago_anticipado'],0).'</td>
	<td align="right">'.number_format($array_renglon[1]['tarjeta_credito'],0).'</td>
	<td align="right">'.number_format($array_renglon[1]['intento'],0).'</td>
	<td align="right">'.number_format($array_renglon[1]['cortesia'],0).'</td>
	<td align="right">'.number_format($array_renglon[1]['aforo'],0).'</td></tr>';
	$array_totales[0]+=$array_renglon[1]['contado'];
	$array_totales[1]+=$array_renglon[1]['credito'];
	$array_totales[2]+=$array_renglon[1]['pago_anticipado'];
	$array_totales[3]+=$array_renglon[1]['tarjeta_credito'];
	$array_totales[4]+=$array_renglon[1]['intento'];
	$array_totales[5]+=$array_renglon[1]['cortesia'];
	$array_totales[6]+=$array_renglon[1]['aforo'];

	$res = mysql_query("SELECT 
		SUM(IF(b.tipo_venta = 0 AND b.tipo_pago = 1, 1, 0)) as contado, 
		SUM(IF(b.tipo_venta = 0 AND b.tipo_pago = 2, 1, 0)) as credito,
		SUM(IF(b.tipo_venta = 0 AND b.tipo_pago = 5, 1, 0)) as tarjeta_credito,
		SUM(IF(b.tipo_venta = 0 AND b.tipo_pago = 6, 1, 0)) as pago_anticipado,
		SUM(IF(b.tipo_venta = 1, 1, 0)) as intento,
		SUM(IF(b.tipo_venta = 2, 1, 0)) as cortesia,
		COUNT(a.cve) as aforo FROM cobro_engomado b 
		LEFT JOIN certificados a ON b.plaza = a.plaza AND b.cve = a.ticket AND a.estatus!='C'  AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'
		WHERE b.plaza = '".$_POST['plazausuario']."' AND b.estatus!='C' AND b.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND ISNULL(a.cve)");
	$row = mysql_fetch_assoc($res);
	echo '<td>Cobro sin verificar</td>
	<td align="right">'.number_format($row['contado'],0).'</td>
	<td align="right">'.number_format($row['credito'],0).'</td>
	<td align="right">'.number_format($row['pago_anticipado'],0).'</td>
	<td align="right">'.number_format($row['tarjeta_credito'],0).'</td>
	<td align="right">'.number_format($row['intento'],0).'</td>
	<td align="right">'.number_format($row['cortesia'],0).'</td>
	<td align="right">'.number_format($row['aforo'],0).'</td></tr>';
	$array_totales[0]+=$row['contado'];
	$array_totales[1]+=$row['credito'];
	$array_totales[2]+=$row['pago_anticipado'];
	$array_totales[3]+=$row['tarjeta_credito'];
	$array_totales[4]+=$row['intento'];
	$array_totales[5]+=$row['cortesia'];
	$array_totales[6]+=$row['aforo'];
	echo '<tr bgcolor="#E9F2F8"><th>Totales</th>';
	foreach($array_totales as $t) echo '<th align="right">'.number_format($t,0).'</th>';
	echo '</tr></table>';
	/*echo '<h2>Desglose de Certificados Entregados por pagos Anticipados</h2>';
	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Tipo de Verificacion</th><th>Cantidad Entregada</th><th>Cortesia</th><th>Intentos</th><!--<th>Total Importe</th>--></tr>';
	$totales = array();
	$res = mysql_query("SELECT * FROM engomados WHERE entrega=1 ORDER BY nombre");
	while($row = mysql_fetch_array($res)){
		rowb();
		echo '<td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
		$res1 = mysql_query("SELECT COUNT(a.cve),SUM(b.monto),SUM(IF(b.tipo_venta=1,1,0)),SUM(IF(b.tipo_venta=2,1,0)) FROM certificados a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket  WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus!='C' AND a.engomado='".$row['cve']."' AND b.tipo_pago=6 $filtro1");
		$row1 = mysql_fetch_array($res1);
		echo '<td align="right">'.number_format($row1[0],0).'</td>';
		echo '<td align="right">'.number_format($row1[3],0).'</td>';
		echo '<td align="right">'.number_format($row1[2],0).'</td>';
//		echo '<td align="right">'.number_format($row1[1],2).'</td>';
		echo '</tr>';
		$totales[0]+=$row1[0];
		$totales[1]+=$row1[3];
		$totales[2]+=$row1[2];
		$totales[3]+=$row1[1];
	}
	echo '<tr bgcolor="#E9F2F8"><th>Totales</th>';
	foreach($totales as $k=>$v){
	    if($k<3){
		if($k<2) echo '<th align="right">'.number_format($v,0).'</th>';
		else echo '<th align="right">'.number_format($v,2).'</th>';
		}
	}
	echo '</tr></table>';*/
	exit();
}


top($_SESSION);
	if($_POST['cmd']>0){
		echo '<input type="hidden" name="archivoname" value="inventario">';
		echo '<input type="hidden" name="fecha_ini" value="'.$_POST['fecha_ini'].'">';
		echo '<input type="hidden" name="fecha_fin" value="'.$_POST['fecha_fin'].'">';
	}
	if($_POST['cmd']==3){
		$anterior=0;
		$compras=0;
		$consumidos=0;
		$cortesias=0;
		$cancelados=0;
		$total_compra=0;
		$ventas=0;
		$intentos=0;
		$x=0;
		$opc="";
	$filtro='';
		if($row['cve'] != 3 && $row['cve']!=19){
			$filtro = " AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='$fecha_semestre'";
		}
		else{
			$res1=mysql_query("SELECT minimo,cantidad_restar,existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$row['cve']."' ORDER BY cve DESC LIMIT 1");
			$row1=mysql_fetch_array($res1);
			$anterior = $row1['existencia2016'];
		}
	$datos = explode('|',$_POST['reg']);
	if($datos[1]==1){
		$opc="placa";
//				$res1=mysql_query("SELECT SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)<'".$_POST['fecha_ini']."',1,0)) as anterior, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta<1,1,0)) as rango, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta=2,1,0)) as cortesia, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta=1,1,0)) as intento
		$res1=mysql_query("SELECT a.*
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$datos[0]."' AND a.estatus!='C' AND IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta<1,1,0) AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) <= '".$_POST['fecha_fin']."'".$filtro) or die(mysql_error());
	//	$row1 = mysql_fetch_array($res1);
	//	$anterior-=$row1['anterior'];
//		$consumidos=$row1['rango'];
		
	}
	if($datos[1]==2){
		$opc="certificado";
//			$res1=mysql_query("SELECT *, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)<'".$_POST['fecha_ini']."',1,0)) as anterior, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."',1,0)) as rango
			$res1=mysql_query("SELECT a.*
			FROM certificados_cancelados a 
			left JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$datos[0]."' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' and IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) <= '".$_POST['fecha_fin']."'".$filtro) or die(mysql_error());
//		$row1 = mysql_fetch_array($res1);
	//	$anterior-=$row1['anterior'];
		//$cancelados=$row1['rango'];

	}
	if($datos[1]==3){
		$opc="placa";
//				$res1=mysql_query("SELECT a.*, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)<'".$_POST['fecha_ini']."',1,0)) as anterior, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta<1,1,0)) as rango, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta=2,1,0)) as cortesia, SUM(IF(IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta=1,1,0)) as intento
				$res1=mysql_query("SELECT a.*
			FROM certificados a
			inner JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
			inner JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='".$datos[0]."' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha)>='".$_POST['fecha_ini']."' AND b.tipo_venta=2 AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) <= '".$_POST['fecha_fin']."'".$filtro) or die(mysql_error());
	//	$row1 = mysql_fetch_array($res1);
	//	$cortesias=$row1['cortesia'];
		
	}
			echo '<table>';
		echo '
			<tr>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'concentrado.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
//			echo '<td><a href="#" onClick="atcr(\'\',\'_blank\',\'101\',\''.$_POST['reg'].'\');">&nbsp;HTML</a></td><td>&nbsp;</td>';
			echo'</tr>';
		echo '</table>';
		echo '<input type="hidden" name="engomado" value="'.$datos[0].'">';
		echo '<input type="hidden" name="fecha_ini" value="'.$_POST['fecha_ini'].'">';
		echo '<input type="hidden" name="fecha_fin" value="'.$_POST['fecha_fin'].'">';
	//	echo '<input type="hidden" name="tipo" value="'.$datos[1].'">';
		echo '<br>';
	//	$tipo = ($datos[1] == 1) ? 'RV' : 'PA';
	//	$condicion = ($datos[1] == 1) ? '=' : '!=';
		echo '<h2>'.$opc.'s del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';
		echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>'.$opc.'</th><th>Fecha</th></tr>';
		while($row1 = mysql_fetch_array($res1)){
			rowb();
//			echo '<td><a href="#" onClick="atcr(\'concentrado.php\',\'\',2,\''.$row1[0].'\')">'.$row1[0].'</a></td>';
			echo '<td>'.$row1[$opc].'</td>';
			echo '<td align="center">'.$row1[2].'</td>';
			echo '</tr>';
			$total+=$row1[1];
			$x++;
		}
		echo '<tr bgcolor="#E9F2F8"><th colspan="2" align="left">'.$x.' Registro(s)</th></tr>';
		
	}
	if($_POST['cmd']==2){
		$res = mysql_query("SELECT * FROM anios_certificados ORDER BY nombre DESC");
		while($row=mysql_fetch_array($res)){
			$array_anios[$row['cve']]=$row['nombre'];
		}

		$res = mysql_query("SELECT * FROM tipo_combustible ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_tipo_combustible[$row['cve']]=$row['nombre'];
		}

		$array_tipo_pago = array();
		$res = mysql_query("SELECT * FROM tipos_pago WHERE 1 ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_tipo_pago[$row['cve']]=$row['nombre'];
		}

		$array_motivos_intento = array();
		$res = mysql_query("SELECT * FROM motivos_intento WHERE localidad IN (0,2) ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_motivos_intento[$row['cve']]=$row['nombre'];
		}

		$array_depositantes = array();
		$res = mysql_query("SELECT * FROM depositantes WHERE plaza='".$_POST['plazausuario']."' AND edo_cuenta=1 ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_depositantes[$row['cve']]=$row['nombre'];
		}
		$res = mysql_query("SELECT * FROM engomados WHERE cve='".$_POST['engomado']."' ORDER BY nombre");
		$row=mysql_fetch_array($res);
		echo '<table>';
		echo '
			<tr>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'concentrado.php\',\'\',\'1\',\''.$_POST['engomado'].'|'.$_POST['tipo'].'\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		$tipo = ($datos[1] == 1) ? 'RV' : 'PA';
		$condicion = ($datos[1] == 1) ? '=' : '!=';
		echo '<input type="hidden" name="fecha_ini" value="'.$_POST['fecha_ini'].'">';
		echo '<input type="hidden" name="fecha_fin" value="'.$_POST['fecha_fin'].'">';
		echo '<h2>Intentos '.$tipo.' del tipo '.$row['nombre'].' del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].' de la placa '.$_POST['reg'].'</h2>';
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>Ticket</th><th>Fecha</th></th><th>Placa</th>
		<th>Tiene Multa</th><th>A&ntilde;o Certificacion</th><th>Tipo de Pago</th><th>Depositante</th>
		<th>Tipo Combustible</th><th>Entrega Certificado</th><th>Fecha Entrega</th><th>Holograma Entregado</th><th>Usuario</th>';
		echo '</tr>';
		$t=0;
		$res=mysql_query("SELECT b.*,a.cve as certificado, a.certificado as holograma, a.fecha as fecha_entrega,a.engomado as engomado_entrega, b.depositante
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND b.tipo_venta=1 AND 
			IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			TRIM(b.placa) = '".$_POST['reg']."' AND 
			IFNULL((SELECT d.fecha FROM cobro_engomado d WHERE d.placa = b.placa AND d.anio = b.anio AND CONCAT(d.fecha,' ',d.hora) < CONCAT(b.fecha, ' ', b.hora) AND d.estatus != 'C' AND d.tipo_venta IN (0,2) LIMIT 1), '0000-00-00') ".$condicion." b.fecha");
		$totalRegistros = mysql_num_rows($res);
		while($row=mysql_fetch_array($res)) {
			rowb();
			echo '<td align="center">'.htmlentities($row['cve']).'</td>';
			echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
			echo '<td align="center">'.htmlentities($row['placa']).'</td>';
			echo '<td align="center">'.htmlentities($array_nosi[$row['multa']]).'<br>'.$row['folio_multa'].'</td>';
			echo '<td align="center">'.htmlentities($array_anios[$row['anio']]).'</td>';
			echo '<td align="center">'.htmlentities($array_tipo_pago[$row['tipo_pago']]).'</td>';
			echo '<td align="center">'.htmlentities($array_depositantes[$row['depositante']]).'</td>';
			echo '<td align="center">'.htmlentities($array_tipo_combustible[$row['tipo_combustible']]).'</td>';
			echo '<td align="center">'.$row['certificado'].'</td>';
			echo '<td align="center">'.$row['fecha_entrega'].'</td>';
			echo '<td align="center">'.$row['holograma'].'</td>';
			echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
			
			echo '</tr>';
			$t+=$row['monto'];
		}
		echo '	
			<tr>
			<td colspan="13" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
			</tr>
		</table>';
	}
	
	if($_POST['cmd']==1){
		$datos = explode('|',$_POST['reg']);
		$res = mysql_query("SELECT * FROM engomados WHERE cve='".$datos[0]."' ORDER BY nombre");
		$row=mysql_fetch_array($res);
		echo '<table>';
		echo '
			<tr>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'concentrado.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="atcr(\'\',\'_blank\',\'101\',\''.$_POST['reg'].'\');">&nbsp;HTML</a></td><td>&nbsp;</td>';
			echo'</tr>';
		echo '</table>';
		echo '<input type="hidden" name="engomado" value="'.$datos[0].'">';
		echo '<input type="hidden" name="fecha_ini" value="'.$_POST['fecha_ini'].'">';
		echo '<input type="hidden" name="fecha_fin" value="'.$_POST['fecha_fin'].'">';
		echo '<input type="hidden" name="tipo" value="'.$datos[1].'">';
		echo '<br>';
		$tipo = ($datos[1] == 1) ? 'RV' : 'PA';
		$condicion = ($datos[1] == 1) ? '=' : '!=';
	//	echo '<h2>Intentos '.$tipo.' del tipo '.$row['nombre'].' del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';
		echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>Placa</th><th>Cantidad</th></tr>';
		$total=0;
		$x=0;
		$res1=mysql_query("SELECT TRIM(b.placa),COUNT(b.cve)
			FROM certificados a 
			INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
			INNER JOIN anios_certificados c ON c.cve = a.anio
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.engomado='".$row['cve']."' AND a.estatus!='C' AND b.tipo_venta=1 AND 
			IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'
			GROUP BY TRIM(b.placa)");
			/* AND
			IFNULL((SELECT d.fecha FROM cobro_engomado d WHERE d.placa = b.placa AND d.anio = b.anio AND CONCAT(d.fecha,' ',d.hora) <
			CONCAT(b.fecha, ' ',b.hora) AND d.estatus != 'C' AND d.tipo_venta IN (0,2) LIMIT 1), '0000-00-00') = b.fecha*/
		while($row1 = mysql_fetch_array($res1)){
			rowb();
			echo '<td><a href="#" onClick="atcr(\'concentrado.php\',\'\',2,\''.$row1[0].'\')">'.$row1[0].'</a></td>';
			echo '<td align="center">'.$row1[1].'</td>';
			echo '</tr>';
			$total+=$row1[1];
			$x++;
		}
		echo '<tr bgcolor="#E9F2F8"><th align="left">'.$x.' Registro(s)</th><th>'.$total.'</th></tr>';
	}
	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		if($_POST['archivoname']!='inventario'){
			$_POST['fecha_ini'] = date('Y-m').'-01';
			$_POST['fecha_fin'] = date('Y-m-d');
		}
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'concentrado.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.$_POST['fecha_ini'].'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.$_POST['fecha_fin'].'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '</table>';
		echo '<br>';
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
			objeto.open("POST","concentrado.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
		
	
	

	</Script>
';

?>