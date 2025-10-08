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

$rsUsuario=mysql_query("SELECT * FROM datosempresas");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazasrfc[$Usuario['plaza']]=$Usuario['rfc'];
	$array_plazaslocalidad[$Usuario['plaza']]=$Usuario['localidad_id'];
}
$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$importe_iva=round($row['precio']*16/116,2);
	$array_engomadoprecio[$row['cve']]=$row['precio']-$importe_iva;
}

$res=mysql_query("SELECT * FROM areas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_localidad[$row['cve']]=$row['nombre'];
}

$array_clientes=array();
$res=mysql_query("SELECT * FROM clientes WHERE plaza='".$_POST['plazausuario']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_clientes[$row['cve']]=$row['nombre'];
	if($row['rfc']=="" || $row['nombre']=="" || $row['calle']=="" || $row['numexterior']=="" || $row['colonia']=="" || $row['municipio']=="" || $row['codigopostal']=="")
		$array_colorcliente[$row['cve']] = "#FF0000";
	else
		$array_colorcliente[$row['cve']] = "#000000";
}
function mestexto($fec){
	global $array_meses;
	$datos=explode("-",$fec);
	return $array_meses[intval($datos[1])].' '.$datos[0];
}
//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);

$abono=0;

if($_POST['cmd']==100){
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") ORDER BY a.numero, a.nombre");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$fecha_ini = substr($_POST['fecha_ini'],0,8).'01';
	$fecha_fin = date( "Y-m-t" , strtotime ( "+0 day" , strtotime($_POST['fecha_fin'])));
	if(count($array_plazas) == 1){
		foreach($array_plazas as $k=>$v){
			$select= " SELECT a.nombre, COUNT(b.cve), LEFT(b.fecha,7) FROM cat_lineas a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.linea WHERE a.plaza='".$k."' ";
			$select .= " AND b.fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND b.estatus!='C'";
			$select.=" GROUP BY a.cve, LEFT(b.fecha,7)  ORDER BY a.nombre";
			$res=mysql_query($select);
			$totalRegistros = mysql_num_rows($res);
			
			
			if(mysql_num_rows($res)>0) 
			{
				$array_info=array();
				while($row = mysql_fetch_array($res)){
					$array_info[$row['cve']]['nombre'] = $row['nombre'];
					$array_info[$row['cve']][$row[2]] = $row[1];
					$array_info[$row['cve']]['total'] += $row[1];
				}
				$reporte = '<h2>Productividad de Lineas por Mes de '.$v.' del '.$fecha_ini.' al '.$fecha_fin.'</h2>';
				$reporte .= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
				$reporte.= '<tr bgcolor="#E9F2F8"><th>Linea</th>';
				$fecha = $fecha_ini;
				while($fecha <= $fecha_fin){
					$reporte.='<th>'.substr($fecha,0,7).'</th>';
					$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
				}
				$reporte .= '<th>Total</th>';
				$reporte.= '</tr>';//<th>P.Costo</th><th>P.Venta</th>
				$total=array();
				$x++;
				$data=array();
				$legends=array();
				foreach($array_info as $row){
					$reporte.=rowb(false);
					$reporte.= '<td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
					$fecha = $fecha_ini;
					$c=0;
					while($fecha <= $fecha_fin){
						$reporte.= '<td align="right">'.number_format($row[$fecha],0).'</td>';
						$total[$c]+=$row[$fecha];$c++;
						$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
					}
					$reporte.= '<td align="right">'.number_format($row['total'],0).'</td>';
					$reporte.= '</tr>';
					$total[$c]+=$row['total'];
					$x++;
					$data[]=$row[1];
					$legends[]=$row['nombre'];
				}
				$reporte.= '	
					<tr>
					<td bgcolor="#E9F2F8">'.$x.' Registro(s)</td>';
				foreach($total as $t) $reporte .= '<td bgcolor="#E9F2F8" align="right">'.number_format($t,0).'</td>';
				$reporte .= '</tr>
				</table>';


				
				
			} else {
				$reporte.= '
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
				</tr>	  
				</table>';
			}

		}
	}
	else{	
		$reporte = '<h2>Productividad General por Mes del '.$fecha_ini.' al '.$fecha_fin.'</h2>';
		$reporte .= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$reporte.= '<tr bgcolor="#E9F2F8"><th rowspan="2">Plaza</th>';
		$fecha = $fecha_ini;
		while($fecha <= $fecha_fin){
			$reporte.='<th colspan="2">'.substr($fecha,0,7).'</th>';
			$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
		}
		$reporte.='<th colspan="2">Totales</th><th rowspan="2">Promedio de Ingresos</th><th rowspan="2">Mes con Menor Ingreso</th>
		<th rowspan="2">Mes con Mayor Ingreso</th></tr><tr bgcolor="#E9F2F8">';
		$fecha = $fecha_ini;
		$meses=0;
		while($fecha <= $fecha_fin){
			$reporte.= '<th>Ingresos</th><th>Porcentaje</th>';
			$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
			$meses++;
		}
		$reporte.= '<th>Ingresos</th><th>Porcentaje</th></tr>'; 
		$sumacargo=array();
		$x=0;
		$array_ingresos=array();
		foreach($array_plazas as $k=>$v){
			$res = mysql_query("SELECT SUM(monto),LEFT(fecha,7) FROM cobro_engomado WHERE plaza='$k' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND estatus!='C' AND tipo_pago NOT IN (2,6) GROUP BY LEFT(fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]+=$row[0];
				$sumacargo[$row[1]]+=$row[0];
				$array_ingresos[$k]['total']+=$row[0];
				$sumacargo['total']+=$row[0];
			}
			$res = mysql_query("SELECT SUM(a.recuperacion),LEFT(a.fecha,7) FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='$k' AND a.fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' GROUP BY LEFT(a.fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]+=$row[0];
				$sumacargo[$row[1]]+=$row[0];
				$array_ingresos[$k]['total']+=$row[0];
				$sumacargo['total']+=$row[0];
			}
			$res = mysql_query("SELECT SUM(monto),LEFT(fecha,7) FROM pagos_caja WHERE plaza='$k' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND estatus!='C' AND tipo_pago NOT IN (2,6) GROUP BY LEFT(fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]+=$row[0];
				$sumacargo[$row[1]]+=$row[0];
				$array_ingresos[$k]['total']+=$row[0];
				$sumacargo['total']+=$row[0];
			}
			$res = mysql_query("SELECT SUM(a.devolucion),LEFT(a.fecha,7) FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='$k' AND a.fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' GROUP BY LEFT(a.fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]-=$row[0];
				$sumacargo[$row[1]]-=$row[0];
				$array_ingresos[$k]['total']-=$row[0];
				$sumacargo['total']-=$row[0];
			}
			$res = mysql_query("SELECT SUM(monto),LEFT(fecha,7) FROM devolucion_ajuste WHERE plaza='$k' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND estatus!='C' AND tipo_pago NOT IN (2,6) GROUP BY LEFT(fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]-=$row[0];
				$sumacargo[$row[1]]-=$row[0];
				$array_ingresos[$k]['total']-=$row[0];
				$sumacargo['total']-=$row[0];
			}
			$res = mysql_query("SELECT SUM(monto),LEFT(fecha,7) FROM bonos WHERE plaza='$k' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND estatus!='C' AND tipo_pago NOT IN (2,6) GROUP BY LEFT(fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]-=$row[0];
				$sumacargo[$row[1]]-=$row[0];
				$array_ingresos[$k]['total']-=$row[0];
				$sumacargo['total']-=$row[0];
			}
		}
		$data=array();
		$legends=array();
		foreach($array_ingresos as $k=>$v){
			$menor = '-1';
			$mesmenor='';
			$mayor = '-1';
			$mesmayor='';
			$reporte.=rowb(false);
			$reporte.= '<td>'.htmlentities(utf8_encode($array_plazas[$k])).'</td>';
			$fecha = $fecha_ini;
			while($fecha <= $fecha_fin){
				$reporte.= '<td align="right">'.number_format($v[substr($fecha,0,7)],2).'</td>';
				$reporte.= '<td align="right">'.number_format($v[substr($fecha,0,7)]*100/$sumacargo[substr($fecha,0,7)],1).'</td>';
				if($menor<0 || $menor>$v[substr($fecha,0,7)]){
					$menor = $v[substr($fecha,0,7)];
					$mesmenor = substr($fecha,0,7);
				}
				if($mayor<0 || $mayor<$v[substr($fecha,0,7)]){
					$mayor = $v[substr($fecha,0,7)];
					$mesmayor = substr($fecha,0,7);
				}
				$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
			}
			$reporte.= '<td align="right">'.number_format($v['total'],2).'</td>';
			$reporte.= '<td align="right">'.number_format($v['total']*100/$sumacargo['total'],1).'</td>';
			$reporte.= '<td align="right">'.number_format($v['total']/$meses,1).'</td>';
			$reporte.= '<td align="center">'.$mesmenor.'</td>';
			$reporte.= '<td align="center">'.$mesmayor.'</td>';
			$reporte.= '</tr>';
			$data[]=$v;
			$legends[]=$array_plazas[$k];
		}
		$c=4;
		$reporte.= '<tr>';
		$reporte.= '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		$fecha = $fecha_ini;
		while($fecha <= $fecha_fin){
			$reporte.= '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($sumacargo[substr($fecha,0,7)],2).'</td>';
			$reporte.= '<td bgcolor="#E9F2F8" align="right">&nbsp;100%</td>';
			$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
		}
		$reporte.= '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($sumacargo['total'],2).'</td>';
		$reporte.= '<td bgcolor="#E9F2F8" align="right">&nbsp;100%</td>';
		$reporte.= '<td bgcolor="#E9F2F8" colspan="3" align="right">&nbsp;</td>';
		$reporte.= '</tr>';
		$reporte.= '</table>';
	}
	echo $reporte;
	exit();
}

if($_POST['cmd']==101){
	 header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=productividad.xls");
header("Pragma: no-cache");
header("Expires: 0");
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") ORDER BY a.numero, a.nombre");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$fecha_ini = substr($_POST['fecha_ini'],0,8).'01';
	$fecha_fin = date( "Y-m-t" , strtotime ( "+0 day" , strtotime($_POST['fecha_fin'])));
	if(count($array_plazas) == 1){
		
		foreach($array_plazas as $k=>$v){
			$select= " SELECT a.nombre, COUNT(b.cve), LEFT(b.fecha,7) FROM cat_lineas a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.linea WHERE a.plaza='".$k."' ";
			$select .= " AND b.fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND b.estatus!='C'";
			$select.=" GROUP BY a.cve, LEFT(b.fecha,7)  ORDER BY a.nombre";
			$res=mysql_query($select);
			$totalRegistros = mysql_num_rows($res);
			
			
			if(mysql_num_rows($res)>0) 
			{
				$array_info=array();
				while($row = mysql_fetch_array($res)){
					$array_info[$row['cve']]['nombre'] = $row['nombre'];
					$array_info[$row['cve']][$row[2]] = $row[1];
					$array_info[$row['cve']]['total'] += $row[1];
				}
				$reporte = '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
				$reporte.= '<tr bgcolo="#E9F2F8"><th>Linea</th>';
				$fecha = $fecha_ini;
				while($fecha <= $fecha_fin){
					$reporte.='<th>'.substr($fecha,0,7).'</th>';
					$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
				}
				$reporte .= '<th>Total</th>';
				$reporte.= '</tr>';//<th>P.Costo</th><th>P.Venta</th>
				$total=array();
				$x++;
				$data=array();
				$legends=array();
				foreach($array_info as $row){
//					$reporte.=rowb(false);
					$reporte.='<tr>';
					$reporte.= '<td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
					$fecha = $fecha_ini;
					$c=0;
					while($fecha <= $fecha_fin){
						$reporte.= '<td align="right">'.number_format($row[$fecha],0).'</td>';
						$total[$c]+=$row[$fecha];$c++;
						$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
					}
					$reporte.= '<td align="right">'.number_format($row['total'],0).'</td>';
					$reporte.= '</tr>';
					$total[$c]+=$row['total'];
					$x++;
					$data[]=$row[1];
					$legends[]=$row['nombre'];
				}
				$reporte.= '	
					<tr>
					<td bgcolo="#E9F2F8">'.$x.' Registro(s)</td>';
				foreach($total as $t) $reporte .= '<td bgcolo="#E9F2F8" align="right">'.number_format($t,0).'</td>';
				$reporte .= '</tr>
				</table>';


				
				
			} else {
				$reporte.= '
				<table width="100%" border="1" cellspacing="0" cellpadding="0">
				<tr>
					<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
				</tr>	  
				</table>';
			}

		}
	}
	else{	
		$reporte = '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
		$reporte.= '<tr bgcolo="#E9F2F8"><th rowspan="2">Plaza</th>';
		$fecha = $fecha_ini;
		while($fecha <= $fecha_fin){
			$reporte.='<th colspan="2">'.substr($fecha,0,7).'</th>';
			$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
		}
		$reporte.='<th colspan="2">Totales</th><th rowspan="2">Promedio de Ingresos</th><th rowspan="2">Mes con Menor Ingreso</th>
		<th rowspan="2">Mes con Mayor Ingreso</th></tr><tr bgcolo="#E9F2F8">';
		$fecha = $fecha_ini;
		$meses=0;
		while($fecha <= $fecha_fin){
			$reporte.= '<th>Ingresos</th><th>Porcentaje</th>';
			$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
			$meses++;
		}
		$reporte.= '<th>Ingresos</th><th>Porcentaje</th></tr>'; 
		$sumacargo=array();
		$x=0;
		$array_ingresos=array();
		foreach($array_plazas as $k=>$v){
			$res = mysql_query("SELECT SUM(monto),LEFT(fecha,7) FROM cobro_engomado WHERE plaza='$k' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND estatus!='C' AND tipo_pago NOT IN (2,6) GROUP BY LEFT(fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]+=$row[0];
				$sumacargo[$row[1]]+=$row[0];
				$array_ingresos[$k]['total']+=$row[0];
				$sumacargo['total']+=$row[0];
			}
			$res = mysql_query("SELECT SUM(a.recuperacion),LEFT(a.fecha,7) FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='$k' AND a.fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' GROUP BY LEFT(a.fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]+=$row[0];
				$sumacargo[$row[1]]+=$row[0];
				$array_ingresos[$k]['total']+=$row[0];
				$sumacargo['total']+=$row[0];
			}
			$res = mysql_query("SELECT SUM(monto),LEFT(fecha,7) FROM pagos_caja WHERE plaza='$k' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND estatus!='C' AND tipo_pago NOT IN (2,6) GROUP BY LEFT(fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]+=$row[0];
				$sumacargo[$row[1]]+=$row[0];
				$array_ingresos[$k]['total']+=$row[0];
				$sumacargo['total']+=$row[0];
			}
			$res = mysql_query("SELECT SUM(a.devolucion),LEFT(a.fecha,7) FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='$k' AND a.fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' GROUP BY LEFT(a.fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]-=$row[0];
				$sumacargo[$row[1]]-=$row[0];
				$array_ingresos[$k]['total']-=$row[0];
				$sumacargo['total']-=$row[0];
			}
			$res = mysql_query("SELECT SUM(monto),LEFT(fecha,7) FROM devolucion_ajuste WHERE plaza='$k' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND estatus!='C' AND tipo_pago NOT IN (2,6) GROUP BY LEFT(fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]-=$row[0];
				$sumacargo[$row[1]]-=$row[0];
				$array_ingresos[$k]['total']-=$row[0];
				$sumacargo['total']-=$row[0];
			}
			$res = mysql_query("SELECT SUM(monto),LEFT(fecha,7) FROM bonos WHERE plaza='$k' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND estatus!='C' AND tipo_pago NOT IN (2,6) GROUP BY LEFT(fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]-=$row[0];
				$sumacargo[$row[1]]-=$row[0];
				$array_ingresos[$k]['total']-=$row[0];
				$sumacargo['total']-=$row[0];
			}
		}
		$data=array();
		$legends=array();
		foreach($array_ingresos as $k=>$v){
			$menor = '-1';
			$mesmenor='';
			$mayor = '-1';
			$mesmayor='';
//			$reporte.=rowb(false);
			$reporte.='<tr>';
			$reporte.= '<td>'.htmlentities(utf8_encode($array_plazas[$k])).'</td>';
			$fecha = $fecha_ini;
			while($fecha <= $fecha_fin){
				$reporte.= '<td align="right">'.number_format($v[substr($fecha,0,7)],2).'</td>';
				$reporte.= '<td align="right">'.number_format($v[substr($fecha,0,7)]*100/$sumacargo[substr($fecha,0,7)],1).'</td>';
				if($menor<0 || $menor>$v[substr($fecha,0,7)]){
					$menor = $v[substr($fecha,0,7)];
					$mesmenor = substr($fecha,0,7);
				}
				if($mayor<0 || $mayor<$v[substr($fecha,0,7)]){
					$mayor = $v[substr($fecha,0,7)];
					$mesmayor = substr($fecha,0,7);
				}
				$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
			}
			$reporte.= '<td align="right">'.number_format($v['total'],2).'</td>';
			$reporte.= '<td align="right">'.number_format($v['total']*100/$sumacargo['total'],1).'</td>';
			$reporte.= '<td align="right">'.number_format($v['total']/$meses,1).'</td>';
			$reporte.= '<td align="center">'.$mesmenor.'</td>';
			$reporte.= '<td align="center">'.$mesmayor.'</td>';
			$reporte.= '</tr>';
			$data[]=$v;
			$legends[]=$array_plazas[$k];
		}
		$c=4;
		$reporte.= '<tr>';
		$reporte.= '<td bgcolo="#E9F2F8" align="right">&nbsp;Total</td>';
		$fecha = $fecha_ini;
		while($fecha <= $fecha_fin){
			$reporte.= '<td bgcolo="#E9F2F8" align="right">&nbsp;'.number_format($sumacargo[substr($fecha,0,7)],2).'</td>';
			$reporte.= '<td bgcolo="#E9F2F8" align="right">&nbsp;100%</td>';
			$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
		}
		$reporte.= '<td bgcolo="#E9F2F8" align="right">&nbsp;'.number_format($sumacargo['total'],2).'</td>';
		$reporte.= '<td bgcolo="#E9F2F8" align="right">&nbsp;100%</td>';
		$reporte.= '<td bgcolo="#E9F2F8" colspan="3" align="right">&nbsp;</td>';
		$reporte.= '</tr>';
		$reporte.= '</table>';
		
	}
	echo $reporte;
	exit();
}


if($_POST['ajax']==1){
	$filtro="";
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") ORDER BY a.numero, a.nombre");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	$fecha_ini = substr($_POST['fecha_ini'],0,8).'01';
	$fecha_fin = date( "Y-m-t" , strtotime ( "+0 day" , strtotime($_POST['fecha_fin'])));
	if(count($array_plazas) == 1){
		
		foreach($array_plazas as $k=>$v){
			$select= " SELECT a.nombre, COUNT(b.cve), LEFT(b.fecha,7) FROM cat_lineas a INNER JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.linea WHERE a.plaza='".$k."' ";
			$select .= " AND b.fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND b.estatus!='C'";
			$select.=" GROUP BY a.cve, LEFT(b.fecha,7)  ORDER BY a.nombre";
			$res=mysql_query($select);
			$totalRegistros = mysql_num_rows($res);
			
			
			if(mysql_num_rows($res)>0) 
			{
				$array_info=array();
				while($row = mysql_fetch_array($res)){
					$array_info[$row['cve']]['nombre'] = $row['nombre'];
					$array_info[$row['cve']][$row[2]] = $row[1];
					$array_info[$row['cve']]['total'] += $row[1];
				}
				$reporte = '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
				$reporte.= '<tr bgcolor="#E9F2F8"><th>Linea</th>';
				$fecha = $fecha_ini;
				while($fecha <= $fecha_fin){
					$reporte.='<th>'.substr($fecha,0,7).'</th>';
					$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
				}
				$reporte .= '<th>Total</th>';
				$reporte.= '</tr>';//<th>P.Costo</th><th>P.Venta</th>
				$total=array();
				$x++;
				$data=array();
				$legends=array();
				foreach($array_info as $row){
					$reporte.=rowb(false);
					$reporte.= '<td>'.htmlentities(utf8_encode($row['nombre'])).'</td>';
					$fecha = $fecha_ini;
					$c=0;
					while($fecha <= $fecha_fin){
						$reporte.= '<td align="right">'.number_format($row[$fecha],0).'</td>';
						$total[$c]+=$row[$fecha];$c++;
						$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
					}
					$reporte.= '<td align="right">'.number_format($row['total'],0).'</td>';
					$reporte.= '</tr>';
					$total[$c]+=$row['total'];
					$x++;
					$data[]=$row[1];
					$legends[]=$row['nombre'];
				}
				$reporte.= '	
					<tr>
					<td bgcolor="#E9F2F8">'.$x.' Registro(s)</td>';
				foreach($total as $t) $reporte .= '<td bgcolor="#E9F2F8" align="right">'.number_format($t,0).'</td>';
				$reporte .= '</tr>
				</table>';


				
				
			} else {
				$reporte.= '
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
				</tr>	  
				</table>';
			}

		}
	}
	else{	
		$reporte = '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$reporte.= '<tr bgcolor="#E9F2F8"><th rowspan="2">Plaza</th>';
		$fecha = $fecha_ini;
		while($fecha <= $fecha_fin){
			$reporte.='<th colspan="2">'.substr($fecha,0,7).'</th>';
			$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
		}
		$reporte.='<th colspan="2">Totales</th><th rowspan="2">Promedio de Ingresos</th><th rowspan="2">Mes con Menor Ingreso</th>
		<th rowspan="2">Mes con Mayor Ingreso</th></tr><tr bgcolor="#E9F2F8">';
		$fecha = $fecha_ini;
		$meses=0;
		while($fecha <= $fecha_fin){
			$reporte.= '<th>Ingresos</th><th>Porcentaje</th>';
			$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
			$meses++;
		}
		$reporte.= '<th>Ingresos</th><th>Porcentaje</th></tr>'; 
		$sumacargo=array();
		$x=0;
		$array_ingresos=array();
		foreach($array_plazas as $k=>$v){
			$res = mysql_query("SELECT SUM(monto),LEFT(fecha,7) FROM cobro_engomado WHERE plaza='$k' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND estatus!='C' AND tipo_pago NOT IN (2,6) GROUP BY LEFT(fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]+=$row[0];
				$sumacargo[$row[1]]+=$row[0];
				$array_ingresos[$k]['total']+=$row[0];
				$sumacargo['total']+=$row[0];
			}
			$res = mysql_query("SELECT SUM(a.recuperacion),LEFT(a.fecha,7) FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='$k' AND a.fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' GROUP BY LEFT(a.fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]+=$row[0];
				$sumacargo[$row[1]]+=$row[0];
				$array_ingresos[$k]['total']+=$row[0];
				$sumacargo['total']+=$row[0];
			}
			$res = mysql_query("SELECT SUM(monto),LEFT(fecha,7) FROM pagos_caja WHERE plaza='$k' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND estatus!='C' AND tipo_pago NOT IN (2,6) GROUP BY LEFT(fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]+=$row[0];
				$sumacargo[$row[1]]+=$row[0];
				$array_ingresos[$k]['total']+=$row[0];
				$sumacargo['total']+=$row[0];
			}
			$res = mysql_query("SELECT SUM(a.devolucion),LEFT(a.fecha,7) FROM recuperacion_certificado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket WHERE a.plaza='$k' AND a.fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND b.tipo_pago NOT IN (2,6) AND a.estatus!='C' GROUP BY LEFT(a.fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]-=$row[0];
				$sumacargo[$row[1]]-=$row[0];
				$array_ingresos[$k]['total']-=$row[0];
				$sumacargo['total']-=$row[0];
			}
			$res = mysql_query("SELECT SUM(monto),LEFT(fecha,7) FROM devolucion_ajuste WHERE plaza='$k' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND estatus!='C' AND tipo_pago NOT IN (2,6) GROUP BY LEFT(fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]-=$row[0];
				$sumacargo[$row[1]]-=$row[0];
				$array_ingresos[$k]['total']-=$row[0];
				$sumacargo['total']-=$row[0];
			}
			$res = mysql_query("SELECT SUM(monto),LEFT(fecha,7) FROM bonos WHERE plaza='$k' AND fecha BETWEEN '".$fecha_ini."' AND '".$fecha_fin."' AND estatus!='C' AND tipo_pago NOT IN (2,6) GROUP BY LEFT(fecha,7)");
			while($row = mysql_fetch_array($res)){
				$array_ingresos[$k][$row[1]]-=$row[0];
				$sumacargo[$row[1]]-=$row[0];
				$array_ingresos[$k]['total']-=$row[0];
				$sumacargo['total']-=$row[0];
			}
		}
		$data=array();
		$legends=array();
		foreach($array_ingresos as $k=>$v){
			$menor = '-1';
			$mesmenor='';
			$mayor = '-1';
			$mesmayor='';
			$reporte.=rowb(false);
			$reporte.= '<td>'.htmlentities(utf8_encode($array_plazas[$k])).'</td>';
			$fecha = $fecha_ini;
			while($fecha <= $fecha_fin){
				$reporte.= '<td align="right">'.number_format($v[substr($fecha,0,7)],2).'</td>';
				$reporte.= '<td align="right">'.number_format($v[substr($fecha,0,7)]*100/$sumacargo[substr($fecha,0,7)],1).'</td>';
				if($menor<0 || $menor>$v[substr($fecha,0,7)]){
					$menor = $v[substr($fecha,0,7)];
					$mesmenor = substr($fecha,0,7);
				}
				if($mayor<0 || $mayor<$v[substr($fecha,0,7)]){
					$mayor = $v[substr($fecha,0,7)];
					$mesmayor = substr($fecha,0,7);
				}
				$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
			}
			$reporte.= '<td align="right">'.number_format($v['total'],2).'</td>';
			$reporte.= '<td align="right">'.number_format($v['total']*100/$sumacargo['total'],1).'</td>';
			$reporte.= '<td align="right">'.number_format($v['total']/$meses,1).'</td>';
			$reporte.= '<td align="center">'.$mesmenor.'</td>';
			$reporte.= '<td align="center">'.$mesmayor.'</td>';
			$reporte.= '</tr>';
			$data[]=$v;
			$legends[]=$array_plazas[$k];
		}
		$c=4;
		$reporte.= '<tr>';
		$reporte.= '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		$fecha = $fecha_ini;
		while($fecha <= $fecha_fin){
			$reporte.= '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($sumacargo[substr($fecha,0,7)],2).'</td>';
			$reporte.= '<td bgcolor="#E9F2F8" align="right">&nbsp;100%</td>';
			$fecha = date( "Y-m-d" , strtotime ( "+1 month" , strtotime($fecha)));
		}
		$reporte.= '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($sumacargo['total'],2).'</td>';
		$reporte.= '<td bgcolor="#E9F2F8" align="right">&nbsp;100%</td>';
		$reporte.= '<td bgcolor="#E9F2F8" colspan="3" align="right">&nbsp;</td>';
		$reporte.= '</tr>';
		$reporte.= '</table>';
		
	}
	echo $reporte;
	exit();
}


top($_SESSION);
	

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="';
				echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
				echo 'atcr(\'productividadxmes.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;
				     <td><a href="#" onclick="';
				echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
				echo 'atcr(\'productividadxmes.php\',\'_blank\',101,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Excel</a></td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.substr(fechaLocal(),0,4).'-01-01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Plaza</td><td><select multiple="multiple" name="plazas" id="plazas">';
		$optionsplazas = array();
		foreach($array_plazas as $k=>$v){
			echo '<option value="'.$k.'" selected>'.$v.'</option>';
		}
		echo '</select>';
		echo '<input type="hidden" name="plaza" id="plaza" value=""></td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		//Listado
		echo '<b><font color="RED">Mostrar&aacute; el reporte agrupado por mes de los meses que abarque el rango de fecha</font></b>';
		echo '<div id="Resultados">';
		echo '</div>';
	}
bottom();
echo '
<Script language="javascript">
	$("#plazas").multipleSelect({
		width: 500
	});	
	function buscarRegistros(){
		document.forma.plaza.value=$("#plazas").multipleSelect("getSelects");
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","productividadxmes.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&plaza="+document.getElementById("plaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
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