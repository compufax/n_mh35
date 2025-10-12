<?php 
include ("main.php"); 

$res = mysql_query("SELECT a.plaza,a.localidad_id, a.rfc FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$res=mysql_query("SELECT local,nombre FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];
$PlazaLocal_nombre=$row[1];


$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}


$array_tipo_pago = array();
$res = mysql_query("SELECT * FROM tipos_pago WHERE cve!=5 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}
$array_tipo_pago[2] = 'RECUPERACION DE CREDITO';

$array_depositantes = array();
$res = mysql_query("SELECT * FROM depositantes WHERE plaza='".$_POST['plazausuario']."' and solo_contado!=1 AND edo_cuenta=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_depositantes[$row['cve']]=$row['nombre'];
}

$array_entidades = array();
/*$res = mysql_query("SELECT cve,nombre FROM cat_entidades  ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_entidades[$row['cve']]=$row['nombre'];
}*/

if($_POST['cveusuario']==1)
	$res = mysql_query("SELECT cve,nombre FROM anios_certificados WHERE 1 ORDER BY nombre DESC");
else
	$res = mysql_query("SELECT cve,nombre FROM anios_certificados  WHERE venta=1 ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
}

$array_forma_pago = array(1=>"Efectivo",2=>"Deposito Bancario",3=>"Cheque",4=>"Transferencia",5=>'Tarjeta Bancaria');

$array_estatus = array('A'=>'Activo','C'=>'Cancelado');
/*** CONSULTA AJAX  **************************************************/

$array_engomado = array();
$sel="SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND plazas like '%|".$_POST['plazausuario']."|%' AND entrega=1 and cve in (3,4,5,6,19,24) ORDER BY nombre";
//echo''.$sel.'';
$res = mysql_query($sel) or die(mysql_error());
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomado_precio[$row['cve']]=$row['precio_compra'];
	$array_engomado_precio_venta[$row['cve']]=$row['precio'];

}

$sel="SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND plazas like '%|".$_POST['plazausuario']."|%' AND entrega=1  ORDER BY nombre";
//echo''.$sel.'';
$res = mysql_query($sel) or die(mysql_error());
while($row=mysql_fetch_array($res)){
	$array_engomadoo[$row['cve']]=$row['nombre'];
	

}

$costo_verificacion=$array_engomado_precio_venta[2];

if($_POST['cmd']==-110) {
	require_once('../dompdf/dompdf_config.inc.php');
		$html='<html><head>
      <style type="text/css">
	                    top  lado      ladoiz
		 @page{ margin: 5in 0.5in 1px 0.5in;}
		</style>
		 </head><body>';

$html.= '<table width="100%" border="0" cellpadding="4" cellspacing="1"  id="tabla1" >';
	$html.= '<tr style="font-size:25px"><td align="center">'.$array_plaza[$_POST['plazausuario']].' - '.$PlazaLocal_nombre.'</td></tr><tr>
			<td align="center" style="font-size:20px">Desglose de Ventas</td>
		 </tr>
		 <tr style="font-size:16px"><td align="" colspan="11">Periodo: '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</td></tr>';
	$html.= '</table>';
	$html.= '<br>';
		 

		

$select= " SELECT sum(if(a.tipo_venta=0 AND a.tipo_pago=1 AND a.estatus='A' AND a.engomado!=23,1,0)) as efectivos,
				  sum(if(a.estatus='A' AND a.engomado=23,1,0)) as exentos,
				  sum(if(a.tipo_pago in (5,7) AND a.estatus='A',1,0)) as bancos,
				  sum(if(a.tipo_venta=1 AND a.estatus='A',1,0)) as intentos,
				  sum(if(a.tipo_venta=2 AND a.estatus='A',1,0)) as cortesias,
				  sum(if(a.estatus='A' and a.vale_pago_anticipado>0 AND a.tipo_pago=6,1,0)) as vales_usados,
				  sum(if(a.tipo_venta=3 AND a.estatus='A',1,0)) as reposiciones,
				  
				  sum(if(a.tipo_venta=0 AND a.estatus='A',if(a.tipo_pago in (5,7,2,6),0,a.monto),0)) as efectivos_monto,
				  sum(if(a.estatus='A' AND a.engomado=23,a.monto,0)) as exentos_monto,
				  sum(if(a.tipo_pago in (5,7) AND a.estatus='A',a.monto,0)) as bancos_monto,
				  sum(if(a.tipo_venta=1 AND a.estatus='A',a.monto,0)) as intentos_monto,
				  sum(if(a.tipo_venta=2 AND a.estatus='A',a.monto,0)) as cortesias_monto,
				  sum(if(a.tipo_venta=3 AND a.estatus='A',a.monto,0)) as reposiciones_monto,
				  
				  sum(if((a.tipo_venta=0) and (a.estatus in('A','B','C')),if((a.tipo_pago in (2,6,5,7,12)) or (a.engomado=23),0,a.copias),0)) as efectivos_copias2,
				  sum(if(a.tipo_pago not in (5,7,2,6,12) and (a.estatus in('A','B','C')),a.copias,0)) as efectivos_copias,
				  sum(if(a.tipo_pago in (5,7),a.copias,0)) as bancos_copias,
				  
				  sum(if(((IFNULL(b.cve,0)=0) AND a.estatus='A'),1,0)) as no_verificado,
				  sum(if(b.fecha > a.fecha,1,0)) as pagos_anteriores
						
		FROM cobro_engomado a 
		LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
		LEFT JOIN depositantes c ON c.cve = a.depositante AND c.plaza = a.plaza 
		WHERE a.plaza='".$_POST['plazausuario']."' ";
			$select.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
			//if ($_POST['entidad']!="all") { $select.=" AND a.entidad='".$_POST['entidad']."' "; }
			if ($_POST['anio']!="all") { $select.=" AND a.anio='".$_POST['anio']."' "; }
			//////sum(if(a.tipo_venta=0,if((a.tipo_pago in (5,7)) or (a.engomado=23) or (a.tipo_pago=6),0,a.copias),0)) as efectivos_copias,
			//////sum(if(a.tipo_pago in (5,7) and a.estatus in(A,B,C),a.copias,0)) as bancos_copias
			//AND a.estatus='A'
			
		$res=mysql_query($select);
		
		$select1= " SELECT  sum(if(a.forma_pago=1,1,0)) as pagos_efectivos,
							sum(if(a.forma_pago=1,a.monto,0)) as pagos_efectivos_monto,
							sum(if(a.forma_pago in (2,3,4,5),1,0)) as pagos_bancos,
							sum(if(a.forma_pago in (2,3,4,5),a.monto,0)) as pagos_bancos_monto
		FROM pagos_caja a

		WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus='A'";
		$select1.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
//		$select1.=" GROUP BY a.cve ORDER BY a.cve DESC";
		$res1=mysql_query($select1);
		
		
//		$html.= $select6;
		
		
		
//		$totalRegistros = mysql_num_rows($res);
		
//		$html.= $select;
		
			$html.= '<table width="100%" border="0" cellpadding="4" cellspacing="1"  style="font-size:10px">';
			$html.= '<tr bglor="#E9F2F8">
					 <td width="100">Tipo</td>
					 <td>Cantidad</td>
					 <td>Importe</td>';
			$html.= '</tr>';
			$t=0;
			$row=mysql_fetch_array($res);
				//rowb();
				$html.='<tr>';
				$html.= '<td>Efectivo</td>
						 <td align="center">'.htmlentities($row['efectivos']).'</td>
						 <td align="right">'.number_format($row['efectivos_monto'],2).'</td>
						 </tr>';
				$html.= '<tr>
						 <td>Exentos</td>
						 <td align="center">'.htmlentities($row['exentos']).'</td>
						 <td align="right">'.number_format($row['exentos_monto'],2).'</td>
						 </tr>';
				$html.= '<tr>
						 <td>Banco</td>
						 <td align="center">'.htmlentities($row['bancos']).'</td>
						 <td align="right">'.number_format($row['bancos_monto'],2).'</td>
						 </tr>';
				$html.= '<tr>
						 <td>Intentos</td>
						 <td align="center">'.htmlentities($row['intentos']).'</td>
						 <td align="right">'.number_format($row['intentos_monto'],2).'</td>
						 </tr>';
				$html.= '<tr>
						 <td>Cortesias</td>
						 <td align="center">'.htmlentities($row['cortesias']).'</td>
						 <td align="right">'.number_format($row['cortesias_monto'],2).'</td>
						 </tr>';
				$html.= '<tr>
						 <td>Vales Utilizados</td>
						 <td align="center">'.htmlentities($row['vales_usados']).'</td>
						 <td align="right">'.number_format(0,2).'</td>
						 </tr>';
				$html.= '<tr>
						 <td>Reposiciones</td>
						 <td align="center">'.htmlentities($row['reposiciones']).'</td>
						 <td align="right">'.number_format($row['reposiciones_monto'],2).'</td>
						 </tr>';
						 
				$cant=$row['efectivos']+$row['exentos']+$row['bancos']+$row['intentos']+$row['cortesias']+$row['reposiciones'];
				$tot=$row['efectivos_monto']+$row['exentos_monto']+$row['bancos_monto']+$row['intentos_monto']+$row['cortesias_monto']+$row['reposiciones_monto'];
				$html.= '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
				          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.$cant.'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">'.number_format($tot,2).'</td></tr>';
						  
				$html.= '<tr>
						<td colspan="3"></td>
						</tr>';
				$cant=0;
				$html.= '<tr><td>Copias en Efectivo</td><td></td><td align="right">'.number_format($row['efectivos_copias'],2).'</td></tr>';
				$html.= '<tr><td>Copias en Bancos</td><td></td><td align="right">'.number_format($row['bancos_copias'],2).'</td></tr>';
				$cant+=$row['efectivos_copias']+$row['bancos_copias'];
				$html.= '<tr>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
				          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">'.number_format($cant,2).'</td></tr>';
				
				$html.= '<tr><td colspan="3"></td></tr>';		
			//		$html.= $select1;
			$row1=mysql_fetch_array($res1);
				$html.='<tr><td>Pagos Anticipados Efectivo</td><td align="center">'.htmlentities($row1['pagos_efectivos']).'</td><td align="right">'.number_format($row1['pagos_efectivos_monto'],2).'</td></tr>';
				$html.='<tr><td>Pagos Anticipados Bancos</td><td align="center">'.htmlentities($row1['pagos_bancos']).'</td><td align="right">'.number_format($row1['pagos_bancos_monto'],2).'</td></tr>';
				$tot1=$row1['pagos_bancos_monto']+$row1['pagos_efectivos_monto'];
				$t55=$row1['pagos_efectivos_monto'];
				$t66=$row1['pagos_bancos_monto'];
				$total1+=$tot1;
				$html.= '
					  <tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
				          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.($row1['pagos_efectivos']+$row1['pagos_bancos']).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">'.number_format($tot1,2).'</td></tr>
					<tr><td colspan="3"></td></tr>';
				$html.= '<tr>
						 <td align="left" colspan="3">DESGLOSE DE CERTIFICADOS DE ENTREGA</td>
						 </tr>';
						 $cant=0;
				foreach($array_engomado as $k=>$v){
		
				$select="SELECT count(a.cve) as resultado,sum(a.monto) as resultado_monto FROM certificados a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") and a.estatus!='C' ORDER BY a.cve DESC";
				$re1=mysql_query($select);
				$row3 = mysql_fetch_array($re1);
				/*$selec="SELECT count(a.cve) as resultado FROM certificados a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") and a.estatus='C' ORDER BY a.cve DESC";
				$re2=mysql_query($selec);
				$row4 = mysql_fetch_array($re2);*/
		///		$selec= " SELECT count(cve) as resultado FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND engomado IN (".$k.") AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		///		$re2=mysql_query($selec);
		///		$row4 = mysql_fetch_array($re2);
				$sele="SELECT count(a.cve) as resultado FROM certificados a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") ORDER BY a.cve DESC";
				$re3=mysql_query($sele);
				$row5 = mysql_fetch_array($re3);
				$re=0;
				$html.= '<tr>
				<td align="left">'.$v.'</td>
				<td align="center">&nbsp;'.$row3['resultado'].'</td>
				<!--<td align="left">&nbsp;'.$row3['resultado_monto'].'</td>-->
				</tr>';
	//			$re=$row3['resultado'] + $row4['resultado'];
//				$html.='<td align="right">&nbsp;'.$re.'</td></tr>';
				$t_act=$t_act + $row3['resultado'];
				$t_can=$t_can + $row4['resultado'];
				$cant+=$row3['resultado'];
				}
				
				$html.= '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
				          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.$cant.'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right"></td></tr>';
				$html.= '<tr>
						<td colspan="3"></td>
						</tr>';
				
				$html.= '<tr>
						<td align="left" colspan="3">DESGLOSE DE CERTIFICADOS CANCELADOS</td>
						</tr>';
						$cant=0;
				foreach($array_engomado as $k=>$v){
				$select2= " SELECT count(cve) as cancelados FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND engomado IN (".$k.")";
				$select2.=" AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
				if ($_POST['anio']!="all") { $select.=" AND anio='".$_POST['anio']."' "; }
				//if ($_POST['certificado']!="") { $select.=" AND certificado='".$_POST['certificado']."' "; }
				$select2.=" ORDER BY cve DESC";
				//$html.= $select2;
				$res2=mysql_query($select2);
				$row2 = mysql_fetch_array($res2);
				$html.= '<tr>
				<td align="left">'.$v.'</td>
				<td align="center">&nbsp;'.$row2['cancelados'].'</td>
				<td align="left">&nbsp;'.$row2['cancelados_monto'].'</td>
				</tr>';
				$cant+=$row2['cancelados'];
				}
				
				$html.= '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
				          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.$cant.'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right"></td></tr>';
				$html.= '<tr><td colspan="3"></td></tr>';
				
				//$html.= '<tr><td align="left" colspan="3">EXISTENCIA EN ALMACEN</td></tr>';
				
				$_POST['mostrar'] == 1;
				$select6= " SELECT * FROM compra_certificados 
				WHERE plaza='".$_POST['plazausuario']."' ";
//				WHERE plaza='".$_POST['plazausuario']."' and fecha_compra BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
				$select6.=" AND anio IN (".$_POST['anio'].") and engomado not in (12,22)" ; 
				$select6.=" ORDER BY fecha_compra DESC,cve DESC";
				$res6=mysql_query($select6) or die(mysql_error());
				$t=$t2=$t3=0;
				$exist =array();
		//	$nivelUsuario = nivelUsuario();
			//$html.=''.$select6.'';
			while($row=mysql_fetch_array($res6)) {
				if($row['estatus']=='C')
					$cantidad = 0;
				else
					$cantidad=$row['foliofin']+1-$row['folioini'];
				$puede_cancelar = 0;
				$diferente=0;
				$res1=mysql_query("SELECT cve FROM certificados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."' AND estatus!='C'");
				$res2=mysql_query("SELECT cve FROM certificados_cancelados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."' AND estatus!='C'");
				if(mysql_num_rows($res1)==0 && mysql_num_rows($res2)==0) $puede_cancelar = 1;
				$entregados = mysql_num_rows($res1) + mysql_num_rows($res2);
				$faltantes = $cantidad-$entregados;
				if($faltantes < 0) $faltantes = 0;
				if($_POST['mostrar'] == 0 || ($_POST['mostrar'] == 1 && $cantidad>$entregados) || ($_POST['mostrar'] == 2 && $cantidad<=$entregados)){
					
					if($row['estatus']=='C'){
						$html.= 'Cancelado';
						$cantidad=0;
					}
					$exist[$row['engomado']]+=$faltantes;
					$faltantes;
					
					
					$t+=$cantidad;
					$t2+=round($row['costo']*$cantidad,2);
					$t3+=$faltantes;
				}
			}
			
			$x=0;
			$y=count($exist);
			$cant=0;
			foreach($exist as $k => $v){
				if($x==0){$html.= '<tr><th align="left" colspan="3">EXISTENCIA EN ALMACEN</th></tr>';}
				$html.= '<tr>
				<td align="left">'.$array_engomado[$k].'</td>
				<td align="center">&nbsp;'.$v.'</td>
				</tr>';
				$cant+=$v;
				$x++;
				if($y==$x){
					$html.= '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
				          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.$cant.'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right"></td></tr>';
				}
			}
			
			
			
			
			
			$html.= '	
				<tr>
				<td colspan="2" bgclor="#E9F2F8">';$html.= '</td>
				<td align="center" bgolor="#E9F2F8"></td>

				</tr>
			</table>';
			
			$html.='</body></html>';
	 	$mipdf= new DOMPDF();
//	$mipdf->margin: "0";
	//$mipdf->set_paper("A4", "portrait");
	$mipdf->set_paper("A4", "portrait");
    
//    $mipdf->set_margin("Legal", "landscape");
//	$mipdf->set_paper("Legal", "landscape");
	$mipdf->load_html($html);
	$mipdf->render();
	$mipdf ->stream();		
		exit();	
}	

if($_POST['cmd']==1100) {
	require_once('../dompdf/dompdf_config.inc.php');
		$html='<html><head>
      <style type="text/css">
	                    top  lado      ladoiz
		 @page{ margin: 5in 0.5in 1px 0.5in;}
		</style>
		 </head><body>';

$html.= '<table width="100%" border="0" cellpadding="4" cellspacing="1"  id="tabla1" >';
	$html.= '<tr style="font-size:22px"><td align="center">'.$array_plaza[$_POST['plazausuario']].' - '.$PlazaLocal_nombre.'</td></tr><tr>
			<td align="center" style="font-size:16px"> Reporte de Utilidad Reporte de Utilidad    : '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</td>
		 </tr>
		 ';
	$html.= '</table>';
	$html.= '<br>';
		 
$html.= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			$html.= '<tr bgcolo="#E9F2F8"><th width="100">Tipo</th><th>Utilizados</th><th>Pagados</th><th>Venta</th><th>Costo Utilizados</th><th>Fondo Ambiental</th><th>Utilidad</th>';
			$array_engomado_precio_venta[19]=628.00;
			foreach($array_engomado as $k=>$v){
				
				$select1="SELECT count(a.cve) as resultado,sum(a.monto) as resultado_monto ,sum(if((b.tipo_pago=1 or b.tipo_pago=5 or b.tipo_pago=7 and b.tipo_venta=0),1,0)) as pagados
				FROM certificados a 
				INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante 
				WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") and a.estatus!='C' ORDER BY a.cve DESC";
				$re1=mysql_query($select1);
				$row1 = mysql_fetch_array($re1);
				
				
				
				
				
			
			$html.= '<tr><td>'.$v.'</td>
			    <td align="center">'.number_format($row1['resultado'],0).'</td>
				<td align="center">'.number_format($row1['pagados'],0).'</td>
				<td align="center">'.number_format(($row1['pagados'] * $array_engomado_precio_venta[$k] ),2).'</td>
				<td align="center">'.number_format(($row1['resultado'] * $array_engomado_precio[$k] ),2).'</td>
				<td align="center">'.number_format(($row1['resultado'] * $fondo_ambiental ),2).'</td>
				<td align="center">'.number_format(($row1['pagados'] * $array_engomado_precio_venta[$k] )-($row1['resultado'] * $array_engomado_precio[$k] )-($row1['resultado'] * $fondo_ambiental ),2).'</td>
				
				</tr>';
				
				$cant += $row1['resultado'];
				$cant1 += ($row1['pagados']);
				$cant2 += ($row1['pagados'] * $array_engomado_precio_venta[$k] );
				$cant3 += ($row1['resultado'] * $array_engomado_precio[$k] );
				$cant4 += (($row1['resultado'] * $fondo_ambiental ));
				$cant5 += ($row1['pagados'] * $array_engomado_precio_venta[$k] )-($row1['resultado'] * $array_engomado_precio[$k] )-($row1['resultado'] * $fondo_ambiental );
				
				
				$cant6 += ($existencia+$row3['cant'] -$row4['resultado']);
				$cant7 += (($existencia * $array_engomado_precio[$row1['cve']])+($row3['cant'] * $array_engomado_precio[$row1['cve']])-($row4['resultado'] * $array_engomado_precio[$row1['cve']]));
				
			}
			$html.= '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
				          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant,0).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1,0).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant2,2).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant3,2).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant4,2).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant5,2).'</td>
						  </tr>';
			
			
			
			
			
			$html.= '	
				
			';
			
			
			$select2= "SELECT sum(if(a.tipo in (0) and b.forma_pago =1,1,0)) as pagos_efectivos,
							sum(if(a.tipo in (0) and b.forma_pago =1,a.monto,0)) as pagos_efectivos_monto,
							sum(if(a.tipo in (0) and b.forma_pago in (2,3,4),1,0)) as pagos_bancos,
							sum(if(a.tipo in (0) and b.forma_pago in (2,3,4),a.monto,0)) as pagos_bancos_monto
		FROM vales_pago_anticipado a
        LEFT JOIN pagos_caja b ON b.cve = a.pago AND b.plaza = a.plaza
        
        WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus='A' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		$res2=mysql_query($select2);
		$row2=mysql_fetch_array($res2);


		$select3= " SELECT sum(if(a.tipo_venta IN (0,3) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,1,0)) as efectivos1,
				  sum(if(a.tipo_venta IN (0) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,1,0)) as efectivos,
				  sum(if(a.tipo_pago in (2) AND a.estatus='A',1,0)) as credito,
				  sum(if(a.estatus='A' AND a.engomado=23,1,0)) as exentos,
				  sum(if(a.tipo_pago in (5,7) AND a.estatus='A',1,0)) as bancos,
				  sum(if(a.tipo_venta=1 AND a.estatus='A',1,0)) as intentos,
				  sum(if(a.tipo_venta=2 AND a.estatus='A',1,0)) as cortesias,
				  sum(if(a.estatus='A' and a.vale_pago_anticipado>0 AND a.tipo_pago=6,1,0)) as vales_usados,
				  sum(if(a.tipo_venta=3 AND a.estatus='A',1,0)) as reposiciones,
				  
				  sum(if(a.tipo_venta=0 AND a.estatus='A',if((a.tipo_pago in (5,7)) or (a.tipo_pago=6),0,a.monto),0)) as efectivos_montooooo,
				  sum(if(a.tipo_venta in (0,3) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,a.monto,0)) as efectivos_monto1,
				  sum(if(a.tipo_venta in (0) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,a.monto,0)) as efectivos_monto,
				  sum(if(a.tipo_pago in (2) AND a.estatus='A',a.monto,0)) as credito_monto,
				  sum(if(a.estatus='A' AND a.engomado=23,a.monto,0)) as exentos_monto,
				  sum(if(a.tipo_pago in (5,7) AND a.estatus='A',a.monto,0)) as bancos_monto,
				  sum(if(a.tipo_venta=1 AND a.estatus='A',a.monto,0)) as intentos_monto,
				  sum(if(a.tipo_venta=2 AND a.estatus='A',a.monto,0)) as cortesias_monto,
				  sum(if(a.tipo_venta=3 AND a.estatus='A',a.monto,0)) as reposiciones_monto,
				  
				  sum(if((a.tipo_venta=0) and (a.estatus in('A','B','C')),if((a.tipo_pago in (2,6,5,7,12)) or (a.engomado=23),0,a.copias),0)) as efectivos_copias2,
				  sum(if(a.tipo_pago in (1,2,6,12),a.copias,0)) as efectivos_copias,
				  sum(if(a.tipo_pago not in (5,7,2,6,12) and (a.estatus in('A','B','C')),a.copias,0)) as efectivos_copiassssss,
				  sum(if(a.tipo_pago in (5,7),a.copias,0)) as bancos_copias,
				  
				  sum(if(((IFNULL(b.cve,0)=0) AND a.estatus='A'),1,0)) as no_verificado,
				  sum(if(b.fecha > a.fecha,1,0)) as pagos_anteriores
				  
						
		FROM cobro_engomado a 
		LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
		LEFT JOIN depositantes c ON c.cve = a.depositante AND c.plaza = a.plaza 
		WHERE a.plaza='".$_POST['plazausuario']."' ";
			$select3.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
			//if ($_POST['entidad']!="all") { $select.=" AND a.entidad='".$_POST['entidad']."' "; }
			if ($_POST['anio']!="all") { $select.=" AND a.anio='".$_POST['anio']."' "; }
			//////sum(if(a.tipo_venta=0,if((a.tipo_pago in (5,7)) or (a.engomado=23) or (a.tipo_pago=6),0,a.copias),0)) as efectivos_copias,
			//////sum(if(a.tipo_pago in (5,7) and a.estatus in(A,B,C),a.copias,0)) as bancos_copias
			//AND a.estatus='A'
		$res3=mysql_query($select3) or die(mysql_error());
		$row3=mysql_fetch_array($res3);

			$html.='
			<tr><td colspan="7">&nbsp;</td></tr>
			<tr><td colspan="2">Tipo</td><td colspan="">Cantidad</td><td colspan="">Costo</td><td colspan="3">&nbsp;</td></tr>
			<tr><td colspan="2">Compras De Vales Antticipados En Efectivo</td><td align="center">'.htmlentities($row2['pagos_efectivos']).'</td><td align="center">'.number_format($row2['pagos_efectivos_monto'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Banco Tarjetas Debito Y Credito</td> <td align="center">'.htmlentities($row3['bancos']).'</td><td align="center">'.number_format($row3['bancos_monto'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Creditos Verificados</td><td align="center">'.htmlentities($row3['credito']).'</td><td align="center">'.number_format($row3['credito_monto'],2).'</td><td colspan="3"></td></tr>';
			$cant6=$cant1+$row2['pagos_efectivos']+$row3['bancos']+$row3['credito'];
			$cant7=$cant2+$row2['pagos_efectivos_monto']+$row3['bancos_monto']+$row3['credito_monto'];
			
			
			$html.='<tr><td colspan="2" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant6,0).'</td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant7,2).'</td>
						  <td colspan="3"></td>
						  </tr>';
			
			
			$select4=" SELECT count(a.cve) as devoluciones,a.*, f.cve as referencia ,sum(a.monto_venta) as devoluciones_monto
				FROM devolucion_certificado a
							LEFT JOIN cobro_engomado_referencia f ON a.plaza = f.plaza AND a.ticket = f.ticket
							WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and a.estatus!='C'";
				
				$re4=mysql_query($select4);
				$row4 = mysql_fetch_array($re4);
			$html.='
			<tr><td colspan="7">&nbsp;</td></tr>
			<tr><td colspan="2">Copias en Bancos</td><td></td><td align="center">'.number_format($row3['bancos_copias'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Reposiciones</td><td align="center">'.htmlentities($row3['reposiciones']).'</td><td align="center">'.number_format($row3['reposiciones_monto'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Devoluciones</td><td align="center">'.htmlentities($row4['devoluciones']).'</td><td align="center">'.number_format($row4['devoluciones_monto'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Pagos no Verificados</td><td align="center">'.htmlentities($row3['no_verificado']).'</td><td align="center">'.number_format($row3['no_verificado']*$costo_verificacion,2).'</td><td colspan="3"></td></tr>
			';
			$cant8=0;
			$cant9=$cant7+$row3['bancos_copias']+$row3['reposiciones_monto']+$row4['devoluciones_monto']+($row3['no_verificado']*$costo_verificacion);
			
			
			$html.='<tr><td colspan="2" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">VENTA TOTAL</td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant9,2).'</td>
						  <td colspan="3"></td>
						  </tr>
						  
				 <tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Utilizados mas Fondo Ambiental</td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant3+$cant4,2).'</td>
				 
				 <tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Utilidad</td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant7-($cant3+$cant4),2).'</td>
				 
				 <tr>
				 <td colspan="2" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Venta</td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($costo_verificacion,2).'</td>
				 
				  <tr>
				 <td colspan="2" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Promedio de Gastos</td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format(($cant3+$cant4)/($cant6),2).'</td>
				 
				  <tr>
				 <td colspan="2" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Utilidad</td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format( $costo_verificacion-(($cant3+$cant4)/($cant6)),2).'</td>
				 </tr>';



			
			$html.='</table>';
			
			
			$html.='</table></body></html>';
	 	$mipdf= new DOMPDF();
//	$mipdf->margin: "0";
	//$mipdf->set_paper("A4", "portrait");
	$mipdf->set_paper("A4", "portrait");
    
//    $mipdf->set_margin("Legal", "landscape");
//	$mipdf->set_paper("Legal", "landscape");
	$mipdf->load_html($html);
	$mipdf->render();
	$mipdf ->stream();		
		exit();	
}	

if($_POST['cmd']==110) {
	require_once('../dompdf/dompdf_config.inc.php');
		$html='<html><head>
      <style type="text/css">
	                    top  lado      ladoiz
		 @page{ margin: 5in 0.5in 1px 0.5in;}
		</style>
		 </head><body>';

$html.= '<table width="100%" border="0" cellpadding="4" cellspacing="1"  id="tabla1" >';
	$html.= '<tr style="font-size:22px"><td align="center">'.$array_plaza[$_POST['plazausuario']].' - '.$PlazaLocal_nombre.'</td></tr><tr>
			<td align="center" style="font-size:16px"> Reporte de Utilidad : '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</td>
		 </tr>
		 ';
	$html.= '</table>';
	$html.= '<br>';
		 
$html.= '<table width="100%" border="0" cellpadding="1" cellspacing="1" class="" style="font-size:12">';
			$html.= '<tr bgclor="#E9F2F8">
			<th width="100">Tipo</th>
			
			<th>Pagados</th>
			<th>Pagos Anticipados</th>
			<th>Creditos</th>
			<th>Intentos</th>
			<th>Cortesias</th>
			<th>Entregados</th>
			
			
			<th>Cancelados</th>
			<th>Utilizados</th>

			
			<th>Venta</th>
			<th>Costo Utilizados</th>
			<th>Fondo Ambiental</th>
			<!--<th>Utilidad</th>-->';
			$array_engomado_precio_venta[19]=628.00;
			foreach($array_engomado as $k=>$v){
				if($k==24){$fondo_ambiental=$fondo_ambiental_excento;}
				$select1="SELECT 
				sum(if(a.estatus!='C',1,0)) as resultado,
				sum(if( a.estatus!='C',a.monto,0)) as resultado_monto ,
				
				sum(if(((b.tipo_pago=1 or b.tipo_pago=5 or b.tipo_pago=7) and b.tipo_venta=0 and a.estatus!='C'),1,0)) as pagados,
				SUM(IF(b.tipo_venta=1 and a.estatus!='C',1,0)) as intentos,
				SUM(IF(b.tipo_venta=2 and a.estatus!='C',1,0)) as cortesias, 
				SUM(IF(b.tipo_venta=0 and b.tipo_pago=6 and a.estatus!='C',1,0)) as pagos_anticipados,
				SUM(IF(b.tipo_venta=0 and b.tipo_pago=2 and a.estatus!='C',1,0)) as creditos,
				sum(if(a.estatus='C',1,0)) as resultado_cancelados,
				count(a.cve) as resultado_todos
				
				FROM certificados a 
				INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante 
				WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") ORDER BY a.cve DESC";
				/*$select1="SELECT 
				count(a.cve) as resultado,
				sum(a.monto) as resultado_monto ,
				
				sum(if(((b.tipo_pago=1 or b.tipo_pago=5 or b.tipo_pago=7) and b.tipo_venta=0),1,0)) as pagados,
				SUM(IF(b.tipo_venta=1,1,0)) as intentos,
				SUM(IF(b.tipo_venta=2,1,0)) as cortesias, 
				SUM(IF(b.tipo_venta=0 and b.tipo_pago=6,1,0)) as pagos_anticipados,
				SUM(IF(b.tipo_venta=0 and b.tipo_pago=2,1,0)) as creditos
				
				
				FROM certificados a 
				INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante 
				WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") and a.estatus!='C' ORDER BY a.cve DESC";*/
				$re1=mysql_query($select1) or die (mysql_error());
				$row1 = mysql_fetch_array($re1);
				
				
				$select6="SELECT 
				count(a.cve) as cancelados
				
				FROM certificados_cancelados a
				WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") ORDER BY a.cve DESC";
				$re6=mysql_query($select6);
				$row6 = mysql_fetch_array($re6);
				
				
				
			
			$html.= '<tr><td>'.$v.'</td>
			    
				<td align="center">'.number_format($row1['pagados'],0).'</td>
				<td align="center">'.number_format($row1['pagos_anticipados'],0).'</td>
				<td align="center">'.number_format($row1['creditos'],0).'</td>
				<td align="center">'.number_format($row1['intentos'],0).'</td>
				<td align="center">'.number_format($row1['cortesias'],0).'</td>
				<td align="center">'.number_format($row1['resultado'],0).'</td>
				<td align="center">'.number_format($row6['cancelados'],0).'</td>
				<td align="center">'.number_format($row1['resultado']+$row6['cancelados'],0).'</td>
				
				
				
				
				
				
				
				
				<td align="center">'.number_format((($row1['pagados']) * $array_engomado_precio_venta[$k] ),2).'</td>
				<td align="center">'.number_format(($row6['cancelados']+$row1['resultado'] * $array_engomado_precio[$k] ),2).'</td>
				<td align="center">'.number_format(($row1['resultado'] * $fondo_ambiental ),2).'</td>
				<!--<td align="center">'.number_format(($row1['pagados'] * $array_engomado_precio_venta[$k] )-($row1['resultado'] * $array_engomado_precio[$k] )-($row1['resultado'] * $fondo_ambiental ),2).'</td>-->
				
				</tr>';
				
				$cant += $row1['resultado'];
				$cant1 += ($row1['pagados']);
				$cant1_1 += ($row1['intentos']);
				$cant1_2 += ($row1['cortesias']);
				$cant1_3 += ($row1['pagos_anticipados']);
				$cant1_4 += ($row1['creditos']);
				$cant2 += (($row6['cancelados']+$row1['pagados']) * $array_engomado_precio_venta[$k] );
				$cant3 += ($row1['resultado'] * $array_engomado_precio[$k] );
				$cant4 += (($row1['resultado'] * $fondo_ambiental ));
				$cant5 += ($row1['pagados'] * $array_engomado_precio_venta[$k] )-($row1['resultado'] * $array_engomado_precio[$k] )-($row1['resultado'] * $fondo_ambiental );
				$can8 += $row6['cancelados'];
				$can9 += $row1['resultado']+$row6['cancelados'];
				
				
				$cant6 += ($existencia+$row3['cant'] -$row4['resultado']);
				$cant7 += (($existencia * $array_engomado_precio[$row1['cve']])+($row3['cant'] * $array_engomado_precio[$row1['cve']])-($row4['resultado'] * $array_engomado_precio[$row1['cve']]));
				
			}
			$html.= '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_3,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_4,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_1,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_2,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($can8,0).'</td>
						  
						  <!--<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_1,0).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_2,0).'</td>-->
						  
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($can9,0).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant2,2).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant3,2).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant4,2).'</td>
						  <!--<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant5,2).'</td>-->
						  </tr>';
			
			
			
			
			
			$html.= '	
				
			';
			
			
			$select2= "SELECT sum(if(a.tipo in (0) and b.forma_pago =1,1,0)) as pagos_efectivos,
							sum(if(a.tipo in (0) and b.forma_pago =1,a.monto,0)) as pagos_efectivos_monto,
							sum(if(a.tipo in (0) and b.forma_pago in (2,3,4,5),1,0)) as pagos_bancos,
							sum(if(a.tipo in (0) and b.forma_pago in (2,3,4,5),a.monto,0)) as pagos_bancos_monto
		FROM vales_pago_anticipado a
        LEFT JOIN pagos_caja b ON b.cve = a.pago AND b.plaza = a.plaza
        
        WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus='A' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		$res2=mysql_query($select2);
		$row2=mysql_fetch_array($res2);


		$select3= " SELECT sum(if(a.tipo_venta IN (0,3) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,1,0)) as efectivos1,
				  sum(if(a.tipo_venta IN (0) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,1,0)) as efectivos,
				  sum(if(a.tipo_pago in (2) AND a.estatus='A',1,0)) as credito,
				  sum(if(a.estatus='A' AND a.engomado=23,1,0)) as exentos,
				  sum(if(a.tipo_pago in (5,7) AND a.estatus='A',1,0)) as bancos,
				  sum(if(a.tipo_venta=1 AND a.estatus='A',1,0)) as intentos,
				  sum(if(a.tipo_venta=2 AND a.estatus='A',1,0)) as cortesias,
				  sum(if(a.estatus='A' and a.vale_pago_anticipado>0 AND a.tipo_pago=6,1,0)) as vales_usados,
				  sum(if(a.tipo_venta=3 AND a.estatus='A',1,0)) as reposiciones,
				  
				  sum(if(a.tipo_venta=0 AND a.estatus='A',if((a.tipo_pago in (5,7)) or (a.tipo_pago=6),0,a.monto),0)) as efectivos_montooooo,
				  sum(if(a.tipo_venta in (0,3) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,a.monto,0)) as efectivos_monto1,
				  sum(if(a.tipo_venta in (0) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,a.monto,0)) as efectivos_monto,
				  sum(if(a.tipo_pago in (2) AND a.estatus='A',a.monto,0)) as credito_monto,
				  sum(if(a.estatus='A' AND a.engomado=23,a.monto,0)) as exentos_monto,
				  sum(if(a.tipo_pago in (5,7) AND a.estatus='A',a.monto,0)) as bancos_monto,
				  sum(if(a.tipo_venta=1 AND a.estatus='A',a.monto,0)) as intentos_monto,
				  sum(if(a.tipo_venta=2 AND a.estatus='A',a.monto,0)) as cortesias_monto,
				  sum(if(a.tipo_venta=3 AND a.estatus='A',a.monto,0)) as reposiciones_monto,
				  
				  sum(if((tipo_venta in (0,1,2)) and tipo_pago in (1,2,6,12) and a.estatus!='C' ,a.copias,0)) as efectivos_copias2,
				  sum(if(a.tipo_pago in (1,2,6,12),a.copias,0)) as efectivos_copias,
				  sum(if(a.tipo_pago not in (5,7,2,6,12) and (a.estatus in('A','B','C')),a.copias,0)) as efectivos_copiassssss,
				  sum(if((tipo_pago=7 or tipo_pago=5 )and tipo_venta=0 and a.estatus!='C',a.copias,0)) as bancos_copias,
				   sum(if(a.estatus!='C',a.copias,0)) as copiassssssssss,
				  
				  sum(if(((IFNULL(b.cve,0)=0) AND a.estatus='A'),1,0)) as no_verificado,
				  sum(if(b.fecha > a.fecha,1,0)) as pagos_anteriores
				  
						
		FROM cobro_engomado a 
		LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
		LEFT JOIN depositantes c ON c.cve = a.depositante AND c.plaza = a.plaza 
		WHERE a.plaza='".$_POST['plazausuario']."' ";
			$select3.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
			//if ($_POST['entidad']!="all") { $select.=" AND a.entidad='".$_POST['entidad']."' "; }
			if ($_POST['anio']!="all") { $select.=" AND a.anio='".$_POST['anio']."' "; }
			//////sum(if(a.tipo_venta=0,if((a.tipo_pago in (5,7)) or (a.engomado=23) or (a.tipo_pago=6),0,a.copias),0)) as efectivos_copias,
			//////sum(if(a.tipo_pago in (5,7) and a.estatus in(A,B,C),a.copias,0)) as bancos_copias
			//AND a.estatus='A'
		$res3=mysql_query($select3) or die(mysql_error());
		$row3=mysql_fetch_array($res3);

			$html.='
			<tr><td colspan="7">&nbsp;</td></tr>
			<tr><td colspan="2">Tipo</td><td colspan="">Cantidad</td><td colspan="">Costo</td><td colspan="3">&nbsp;</td></tr>
			<tr><td colspan="2">VALES ANTICIPADOS PAGADOS EN EFECTIVO</td><td align="center">'.htmlentities($row2['pagos_efectivos']).'</td><td align="center">'.number_format($row2['pagos_efectivos_monto'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">VALES ANTICIPADOS PAGADOS EN BANCOS</td><td align="center">'.htmlentities($row2['pagos_bancos']).'</td><td align="center">'.number_format($row2['pagos_bancos_monto'],2).'</td><td colspan="3"></td></tr>
			<!--<tr><td colspan="2">Banco Tarjetas Debito Y Credito</td> <td align="center">'.htmlentities($row3['bancos']).'</td><td align="center">'.number_format($row3['bancos_monto'],2).'</td><td colspan="3"></td></tr>-->
			<tr><td colspan="2">Creditos Verificados</td><td align="center">'.htmlentities($row3['credito']).'</td><td align="center">'.number_format($row3['credito_monto'],2).'</td><td colspan="3"></td></tr>';
			$cant6=$row2['pagos_efectivos']+$row2['pagos_bancos'];
			$cant7=$row2['pagos_efectivos_monto']+$row2['pagos_bancos_monto']+$row3['credito_monto'];
			/*$cant6=$cant1+$row2['pagos_efectivos']+$row3['bancos']+$row3['credito'];
			$cant7=$cant2+$row2['pagos_efectivos_monto']+$row3['bancos_monto']+$row3['credito_monto'];*/
			
			
			$html.='<tr><td colspan="2" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant7,2).'</td>
						  <td colspan="3"></td>
						  </tr>';
			
			
			$select4=" SELECT count(a.cve) as devoluciones,a.*, f.cve as referencia ,sum(a.monto_venta) as devoluciones_monto
				FROM devolucion_certificado a
							LEFT JOIN cobro_engomado_referencia f ON a.plaza = f.plaza AND a.ticket = f.ticket
							WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and a.estatus!='C'";
				
				$re4=mysql_query($select4);
				$row4 = mysql_fetch_array($re4);
			$html.='
			<tr><td colspan="7">&nbsp;</td></tr>
			<tr><td colspan="2">Copias en Bancos</td><td></td><td align="center">'.number_format($row3['bancos_copias'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Copias en Efectivo</td><td></td><td align="center">'.number_format($row3['efectivos_copias2'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Reposiciones</td><td align="center"><!--'.htmlentities($row3['reposiciones']).'--></td><td align="center">'.number_format($row3['reposiciones_monto'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Devoluciones</td><td align="center">'.htmlentities($row4[' 	']).'</td><td align="center">'.number_format($row4['devoluciones_monto'],2).'</td><td colspan="3"></td></tr>
			<!--<tr><td colspan="2">Pagos no Verificados</td><td align="center">'.htmlentities($row3['no_verificado']).'</td><td align="center">'.number_format($row3['no_verificado']*$costo_verificacion,2).'</td><td colspan="3"></td></tr>-->
			';
			$cant8=0;
			$cant9=$row3['bancos_copias']+$row3['reposiciones_monto']+$row4['devoluciones_monto']+($row3['efectivos_copias2']);
			/*$cant9=$cant7+$row3['bancos_copias']+$row3['reposiciones_monto']+$row4['devoluciones_monto']+($row3['no_verificado']*$costo_verificacion);*/
			$cant10=$cant9+$cant7+$cant2;
			$cant11=$cant3+$cant4;
			$cant12=$cant10-$cant11;
			
			
			$html.='<tr><td colspan="2" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right"> TOTAL</td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant9,2).'</td>
						  <td colspan="3"></td>
						  </tr>
						  
				<tr><td colspan="2" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right"> TOTAL DE INGRESOS</td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant10,2).'</td>
						  <td colspan="3"></td>
						  </tr>
						  
				 <tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Utilizados mas Fondo Ambiental</td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant3+$cant4,2).'</td>
				 </tr>
				 
				 <tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Utilidad de Operacion</td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant12,2).'</td>
				 </tr>
				 
				 <!--<tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align=""> COSTO DE VENTA POR VERIFICACION</td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($array_engomado_precio_venta[5],2).'</td>
				 </tr>
				 <tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">PROMEDIO DE GASTOS</td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format(($cant3+$cant4)/$can9,2).'</td>
				 </tr>
				 <tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">UTILIDAD POR CERTIFICADO UTILIZADO </td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format(($cant12)/($can9),2).'</td>
				 </tr>
				 -->
				 
				






				<!--<tr>
				 <td colspan="2" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Venta</td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($array_engomado_precio_venta[5],2).'</td>
				 
				  <tr>
				 <td colspan="2" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Promedio de Gastos</td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format(($cant3+$cant4)/($can9),2).'</td>
				 
				  <tr>
				 <td colspan="2" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Utilidad</td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format( ($cant12)/($can9),2).'</td>
				 </tr>-->';



			
			$html.='</table>';
			
			
			$html.='</table></body></html>';
	 	$mipdf= new DOMPDF();
//	$mipdf->margin: "0";
	//$mipdf->set_paper("A4", "portrait");
//	$mipdf->set_paper("A4", "portrait");
    
//    $mipdf->set_margin("Legal", "landscape");
	$mipdf->set_paper("Legal", "landscape");
	$mipdf->load_html($html);
	$mipdf->render();
	$mipdf ->stream();		
		exit();	
}	

if($_POST['cmd']==115) {
	header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=Reporte de Utilidad .xls");
header("Pragma: no-cache");
header("Expires: 0");

$html.= '<table width="100%" border="0" cellpadding="4" cellspacing="1"  id="tabla1" >';
	$html.= '<tr style="font-size:22px"><td align="center" colspan="12">'.$array_plaza[$_POST['plazausuario']].' - '.$PlazaLocal_nombre.'</td></tr><tr>
			<td align="center" style="font-size:16px" colspan="12"> Reporte de Utilidad : '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</td>
		 </tr>
		 ';
	$html.= '</table>';
	$html.= '<br>';
		 
$html.= '<table width="100%" border="0" cellpadding="1" cellspacing="1" class="" style="font-size:12">';
			$html.= '<tr bgclor="#E9F2F8">
			<th width="100">Tipo</th>
			
			<th>Pagados</th>
			<th>Pagos Anticipados</th>
			<th>Creditos</th>
			<th>Intentos</th>
			<th>Cortesias</th>
			<th>Entregados</th>
			
			
			<th>Cancelados</th>
			<th>Utilizados</th>

			
			<th>Venta</th>
			<th>Costo Utilizados</th>
			<th>Fondo Ambiental</th>
			<!--<th>Utilidad</th>-->';
			$array_engomado_precio_venta[19]=628.00;
			foreach($array_engomado as $k=>$v){
				if($k==24){$fondo_ambiental=$fondo_ambiental_excento;}
				$select1="SELECT 
				sum(if(a.estatus!='C',1,0)) as resultado,
				sum(if( a.estatus!='C',a.monto,0)) as resultado_monto ,
				
				sum(if(((b.tipo_pago=1 or b.tipo_pago=5 or b.tipo_pago=7) and b.tipo_venta=0 and a.estatus!='C'),1,0)) as pagados,
				SUM(IF(b.tipo_venta=1 and a.estatus!='C',1,0)) as intentos,
				SUM(IF(b.tipo_venta=2 and a.estatus!='C',1,0)) as cortesias, 
				SUM(IF(b.tipo_venta=0 and b.tipo_pago=6 and a.estatus!='C',1,0)) as pagos_anticipados,
				SUM(IF(b.tipo_venta=0 and b.tipo_pago=2 and a.estatus!='C',1,0)) as creditos,
				sum(if(a.estatus='C',1,0)) as resultado_cancelados,
				count(a.cve) as resultado_todos
				
				FROM certificados a 
				INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante 
				WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") ORDER BY a.cve DESC";
				/*$select1="SELECT 
				count(a.cve) as resultado,
				sum(a.monto) as resultado_monto ,
				
				sum(if(((b.tipo_pago=1 or b.tipo_pago=5 or b.tipo_pago=7) and b.tipo_venta=0),1,0)) as pagados,
				SUM(IF(b.tipo_venta=1,1,0)) as intentos,
				SUM(IF(b.tipo_venta=2,1,0)) as cortesias, 
				SUM(IF(b.tipo_venta=0 and b.tipo_pago=6,1,0)) as pagos_anticipados,
				SUM(IF(b.tipo_venta=0 and b.tipo_pago=2,1,0)) as creditos
				
				
				FROM certificados a 
				INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante 
				WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") and a.estatus!='C' ORDER BY a.cve DESC";*/
				$re1=mysql_query($select1) or die (mysql_error());
				$row1 = mysql_fetch_array($re1);
				
				
				$select6="SELECT 
				count(a.cve) as cancelados
				
				FROM certificados_cancelados a
				WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") ORDER BY a.cve DESC";
				$re6=mysql_query($select6);
				$row6 = mysql_fetch_array($re6);
				
				
				
			
			$html.= '<tr><td>'.$v.'</td>
			    
				<td align="center">'.number_format($row1['pagados'],0).'</td>
				<td align="center">'.number_format($row1['pagos_anticipados'],0).'</td>
				<td align="center">'.number_format($row1['creditos'],0).'</td>
				<td align="center">'.number_format($row1['intentos'],0).'</td>
				<td align="center">'.number_format($row1['cortesias'],0).'</td>
				<td align="center">'.number_format($row1['resultado'],0).'</td>
				<td align="center">'.number_format($row6['cancelados'],0).'</td>
				<td align="center">'.number_format($row1['resultado']+$row6['cancelados'],0).'</td>
				
				
				
				
				
				
				
				
				<td align="center">'.number_format((($row1['pagados']) * $array_engomado_precio_venta[$k] ),2).'</td>
				<td align="center">'.number_format(($row6['cancelados']+$row1['resultado'] * $array_engomado_precio[$k] ),2).'</td>
				<td align="center">'.number_format(($row1['resultado'] * $fondo_ambiental ),2).'</td>
				<!--<td align="center">'.number_format(($row1['pagados'] * $array_engomado_precio_venta[$k] )-($row1['resultado'] * $array_engomado_precio[$k] )-($row1['resultado'] * $fondo_ambiental ),2).'</td>-->
				
				</tr>';
				
				$cant += $row1['resultado'];
				$cant1 += ($row1['pagados']);
				$cant1_1 += ($row1['intentos']);
				$cant1_2 += ($row1['cortesias']);
				$cant1_3 += ($row1['pagos_anticipados']);
				$cant1_4 += ($row1['creditos']);
				$cant2 += (($row6['cancelados']+$row1['pagados']) * $array_engomado_precio_venta[$k] );
				$cant3 += ($row1['resultado'] * $array_engomado_precio[$k] );
				$cant4 += (($row1['resultado'] * $fondo_ambiental ));
				$cant5 += ($row1['pagados'] * $array_engomado_precio_venta[$k] )-($row1['resultado'] * $array_engomado_precio[$k] )-($row1['resultado'] * $fondo_ambiental );
				$can8 += $row6['cancelados'];
				$can9 += $row1['resultado']+$row6['cancelados'];
				
				
				$cant6 += ($existencia+$row3['cant'] -$row4['resultado']);
				$cant7 += (($existencia * $array_engomado_precio[$row1['cve']])+($row3['cant'] * $array_engomado_precio[$row1['cve']])-($row4['resultado'] * $array_engomado_precio[$row1['cve']]));
				
			}
			$html.= '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_3,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_4,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_1,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_2,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($can8,0).'</td>
						  
						  <!--<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_1,0).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_2,0).'</td>-->
						  
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($can9,0).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant2,2).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant3,2).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant4,2).'</td>
						  <!--<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant5,2).'</td>-->
						  </tr>';
			
			
			
			
			
			$html.= '	
				
			';
			
			
			$select2= "SELECT sum(if(a.tipo in (0) and b.forma_pago =1,1,0)) as pagos_efectivos,
							sum(if(a.tipo in (0) and b.forma_pago =1,a.monto,0)) as pagos_efectivos_monto,
							sum(if(a.tipo in (0) and b.forma_pago in (2,3,4,5),1,0)) as pagos_bancos,
							sum(if(a.tipo in (0) and b.forma_pago in (2,3,4,5),a.monto,0)) as pagos_bancos_monto
		FROM vales_pago_anticipado a
        LEFT JOIN pagos_caja b ON b.cve = a.pago AND b.plaza = a.plaza
        
        WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus='A' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		$res2=mysql_query($select2);
		$row2=mysql_fetch_array($res2);


		$select3= " SELECT sum(if(a.tipo_venta IN (0,3) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,1,0)) as efectivos1,
				  sum(if(a.tipo_venta IN (0) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,1,0)) as efectivos,
				  sum(if(a.tipo_pago in (2) AND a.estatus='A',1,0)) as credito,
				  sum(if(a.estatus='A' AND a.engomado=23,1,0)) as exentos,
				  sum(if(a.tipo_pago in (5,7) AND a.estatus='A',1,0)) as bancos,
				  sum(if(a.tipo_venta=1 AND a.estatus='A',1,0)) as intentos,
				  sum(if(a.tipo_venta=2 AND a.estatus='A',1,0)) as cortesias,
				  sum(if(a.estatus='A' and a.vale_pago_anticipado>0 AND a.tipo_pago=6,1,0)) as vales_usados,
				  sum(if(a.tipo_venta=3 AND a.estatus='A',1,0)) as reposiciones,
				  
				  sum(if(a.tipo_venta=0 AND a.estatus='A',if((a.tipo_pago in (5,7)) or (a.tipo_pago=6),0,a.monto),0)) as efectivos_montooooo,
				  sum(if(a.tipo_venta in (0,3) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,a.monto,0)) as efectivos_monto1,
				  sum(if(a.tipo_venta in (0) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,a.monto,0)) as efectivos_monto,
				  sum(if(a.tipo_pago in (2) AND a.estatus='A',a.monto,0)) as credito_monto,
				  sum(if(a.estatus='A' AND a.engomado=23,a.monto,0)) as exentos_monto,
				  sum(if(a.tipo_pago in (5,7) AND a.estatus='A',a.monto,0)) as bancos_monto,
				  sum(if(a.tipo_venta=1 AND a.estatus='A',a.monto,0)) as intentos_monto,
				  sum(if(a.tipo_venta=2 AND a.estatus='A',a.monto,0)) as cortesias_monto,
				  sum(if(a.tipo_venta=3 AND a.estatus='A',a.monto,0)) as reposiciones_monto,
				  
				  sum(if((tipo_venta in (0,1,2)) and tipo_pago in (1,2,6,12) and a.estatus!='C' ,a.copias,0)) as efectivos_copias2,
				  sum(if(a.tipo_pago in (1,2,6,12),a.copias,0)) as efectivos_copias,
				  sum(if(a.tipo_pago not in (5,7,2,6,12) and (a.estatus in('A','B','C')),a.copias,0)) as efectivos_copiassssss,
				  sum(if((tipo_pago=7 or tipo_pago=5 )and tipo_venta=0 and a.estatus!='C',a.copias,0)) as bancos_copias,
				   sum(if(a.estatus!='C',a.copias,0)) as copiassssssssss,
				  
				  sum(if(((IFNULL(b.cve,0)=0) AND a.estatus='A'),1,0)) as no_verificado,
				  sum(if(b.fecha > a.fecha,1,0)) as pagos_anteriores
				  
						
		FROM cobro_engomado a 
		LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
		LEFT JOIN depositantes c ON c.cve = a.depositante AND c.plaza = a.plaza 
		WHERE a.plaza='".$_POST['plazausuario']."' ";
			$select3.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
			//if ($_POST['entidad']!="all") { $select.=" AND a.entidad='".$_POST['entidad']."' "; }
			if ($_POST['anio']!="all") { $select.=" AND a.anio='".$_POST['anio']."' "; }
			//////sum(if(a.tipo_venta=0,if((a.tipo_pago in (5,7)) or (a.engomado=23) or (a.tipo_pago=6),0,a.copias),0)) as efectivos_copias,
			//////sum(if(a.tipo_pago in (5,7) and a.estatus in(A,B,C),a.copias,0)) as bancos_copias
			//AND a.estatus='A'
		$res3=mysql_query($select3) or die(mysql_error());
		$row3=mysql_fetch_array($res3);

			$html.='
			<tr><td colspan="7">&nbsp;</td></tr>
			<tr><td colspan="2">Tipo</td><td colspan="">Cantidad</td><td colspan="">Costo</td><td colspan="3">&nbsp;</td></tr>
			<tr><td colspan="2">VALES ANTICIPADOS PAGADOS EN EFECTIVO</td><td align="center">'.htmlentities($row2['pagos_efectivos']).'</td><td align="center">'.number_format($row2['pagos_efectivos_monto'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">VALES ANTICIPADOS PAGADOS EN BANCOS</td><td align="center">'.htmlentities($row2['pagos_bancos']).'</td><td align="center">'.number_format($row2['pagos_bancos_monto'],2).'</td><td colspan="3"></td></tr>
			<!--<tr><td colspan="2">Banco Tarjetas Debito Y Credito</td> <td align="center">'.htmlentities($row3['bancos']).'</td><td align="center">'.number_format($row3['bancos_monto'],2).'</td><td colspan="3"></td></tr>-->
			<tr><td colspan="2">Creditos Verificados</td><td align="center">'.htmlentities($row3['credito']).'</td><td align="center">'.number_format($row3['credito_monto'],2).'</td><td colspan="3"></td></tr>';
			$cant6=$row2['pagos_efectivos']+$row2['pagos_bancos'];
			$cant7=$row2['pagos_efectivos_monto']+$row2['pagos_bancos_monto']+$row3['credito_monto'];
			/*$cant6=$cant1+$row2['pagos_efectivos']+$row3['bancos']+$row3['credito'];
			$cant7=$cant2+$row2['pagos_efectivos_monto']+$row3['bancos_monto']+$row3['credito_monto'];*/
			
			
			$html.='<tr><td colspan="2" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant7,2).'</td>
						  <td colspan="3"></td>
						  </tr>';
			
			
			$select4=" SELECT count(a.cve) as devoluciones,a.*, f.cve as referencia ,sum(a.monto_venta) as devoluciones_monto
				FROM devolucion_certificado a
							LEFT JOIN cobro_engomado_referencia f ON a.plaza = f.plaza AND a.ticket = f.ticket
							WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and a.estatus!='C'";
				
				$re4=mysql_query($select4);
				$row4 = mysql_fetch_array($re4);
			$html.='
			<tr><td colspan="7">&nbsp;</td></tr>
			<tr><td colspan="2">Copias en Bancos</td><td></td><td align="center">'.number_format($row3['bancos_copias'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Copias en Efectivo</td><td></td><td align="center">'.number_format($row3['efectivos_copias2'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Reposiciones</td><td align="center"><!--'.htmlentities($row3['reposiciones']).'--></td><td align="center">'.number_format($row3['reposiciones_monto'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Devoluciones</td><td align="center">'.htmlentities($row4[' 	']).'</td><td align="center">'.number_format($row4['devoluciones_monto'],2).'</td><td colspan="3"></td></tr>
			<!--<tr><td colspan="2">Pagos no Verificados</td><td align="center">'.htmlentities($row3['no_verificado']).'</td><td align="center">'.number_format($row3['no_verificado']*$costo_verificacion,2).'</td><td colspan="3"></td></tr>-->
			';
			$cant8=0;
			$cant9=$row3['bancos_copias']+$row3['reposiciones_monto']+$row4['devoluciones_monto']+($row3['efectivos_copias2']);
			/*$cant9=$cant7+$row3['bancos_copias']+$row3['reposiciones_monto']+$row4['devoluciones_monto']+($row3['no_verificado']*$costo_verificacion);*/
			$cant10=$cant9+$cant7+$cant2;
			$cant11=$cant3+$cant4;
			$cant12=$cant10-$cant11;
			
			
			$html.='<tr><td colspan="2" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right"> TOTAL</td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant9,2).'</td>
						  <td colspan="3"></td>
						  </tr>
						  
				<tr><td colspan="2" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right"> TOTAL DE INGRESOS</td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant10,2).'</td>
						  <td colspan="3"></td>
						  </tr>
						  
				 <tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Utilizados mas Fondo Ambiental</td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant3+$cant4,2).'</td>
				 </tr>
				 
				 <tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Utilidad de Operacion</td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant12,2).'</td>
				 </tr>
				 
				 <!--<tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align=""> COSTO DE VENTA POR VERIFICACION</td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($array_engomado_precio_venta[5],2).'</td>
				 </tr>
				 <tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">PROMEDIO DE GASTOS</td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format(($cant3+$cant4)/$can9,2).'</td>
				 </tr>
				 <tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">UTILIDAD POR CERTIFICADO UTILIZADO </td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format(($cant12)/($can9),2).'</td>
				 </tr>
				 -->
				 
				






				<!--<tr>
				 <td colspan="2" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Venta</td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($array_engomado_precio_venta[5],2).'</td>
				 
				  <tr>
				 <td colspan="2" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Promedio de Gastos</td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format(($cant3+$cant4)/($can9),2).'</td>
				 
				  <tr>
				 <td colspan="2" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Utilidad</td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format( ($cant12)/($can9),2).'</td>
				 </tr>-->';



			
			$html.='</table>';
			
		echo $html;
		exit();	
}	

if($_POST['cmd']==-115) {
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=ventas periodo.xls");
header("Pragma: no-cache");
header("Expires: 0");
		$html='<html><body>';

$html.= '<table width="100%" border="0" cellpadding="4" cellspacing="1"  id="tabla1" >';
	$html.= '<tr style="font-size:22px"><td align="center">'.$array_plaza[$_POST['plazausuario']].' - '.$PlazaLocal_nombre.'</td></tr><tr>
			<td align="center" style="font-size:16px">Desglose de Venta por Periodo: '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</td>
		 </tr>
		 ';
	$html.= '</table>';
	$html.= '<br>';
		 
		 		
//				  sum(if(a.tipo_venta=0 AND a.estatus='A',if((a.tipo_pago in (5,7)) or (a.engomado=23) or (a.tipo_pago=6),0,a.monto),0)) as efectivos_monto,
$select= " SELECT sum(if(a.tipo_venta IN (0,3) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,1,0)) as efectivos1,
				  sum(if(a.tipo_venta IN (0) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,1,0)) as efectivos,
				  sum(if(a.tipo_pago in (2) AND a.estatus='A',1,0)) as credito,
				  sum(if(a.estatus='A' AND a.engomado=23,1,0)) as exentos,
				  sum(if(a.tipo_pago in (5,7) AND a.estatus='A',1,0)) as bancos,
				  sum(if(a.tipo_venta=1 AND a.estatus='A',1,0)) as intentos,
				  sum(if(a.tipo_venta=2 AND a.estatus='A',1,0)) as cortesias,
				  sum(if(a.estatus='A' and a.vale_pago_anticipado>0 AND a.tipo_pago=6,1,0)) as vales_usados,
				  sum(if(a.tipo_venta=3 AND a.estatus='A',1,0)) as reposiciones,
				  
				  sum(if(a.tipo_venta=0 AND a.estatus='A',if((a.tipo_pago in (5,7)) or (a.tipo_pago=6),0,a.monto),0)) as efectivos_montooooo,
				  sum(if(a.tipo_venta in (0,3) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,a.monto,0)) as efectivos_monto1,
				  sum(if(a.tipo_venta in (0) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,a.monto,0)) as efectivos_monto,
				  sum(if(a.tipo_pago in (2) AND a.estatus='A',a.monto,0)) as credito_monto,
				  sum(if(a.estatus='A' AND a.engomado=23,a.monto,0)) as exentos_monto,
				  sum(if(a.tipo_pago in (5,7) AND a.estatus='A',a.monto,0)) as bancos_monto,
				  sum(if(a.tipo_venta=1 AND a.estatus='A',a.monto,0)) as intentos_monto,
				  sum(if(a.tipo_venta=2 AND a.estatus='A',a.monto,0)) as cortesias_monto,
				  sum(if(a.tipo_venta=3 AND a.estatus='A',a.monto,0)) as reposiciones_monto,
				  
				  sum(if((a.tipo_venta=0) and (a.estatus in('A','B','C')),if((a.tipo_pago in (2,6,5,7,12)) or (a.engomado=23),0,a.copias),0)) as efectivos_copias2,
				  sum(if(a.tipo_pago in (1,2,6,12),a.copias,0)) as efectivos_copias,
				  sum(if(a.tipo_pago not in (5,7,2,6,12) and (a.estatus in('A','B','C')),a.copias,0)) as efectivos_copiassssss,
				  sum(if(a.tipo_pago in (5,7),a.copias,0)) as bancos_copias,
				  
				  sum(if(((IFNULL(b.cve,0)=0) AND a.estatus='A'),1,0)) as no_verificado,
				  sum(if(b.fecha > a.fecha,1,0)) as pagos_anteriores
				  
						
		FROM cobro_engomado a 
		LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
		LEFT JOIN depositantes c ON c.cve = a.depositante AND c.plaza = a.plaza 
		WHERE a.plaza='".$_POST['plazausuario']."' ";
			$select.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
			//if ($_POST['entidad']!="all") { $select.=" AND a.entidad='".$_POST['entidad']."' "; }
			if ($_POST['anio']!="all") { $select.=" AND a.anio='".$_POST['anio']."' "; }
			//////sum(if(a.tipo_venta=0,if((a.tipo_pago in (5,7)) or (a.engomado=23) or (a.tipo_pago=6),0,a.copias),0)) as efectivos_copias,
			//////sum(if(a.tipo_pago in (5,7) and a.estatus in(A,B,C),a.copias,0)) as bancos_copias
			//AND a.estatus='A'
		$res=mysql_query($select) or die(mysql_error());
		
		
		//$select1= " SELECT  count(a.cve) as pagos,sum(b.monto) as pagos_monto
		$_select1= " SELECT  sum(if(a.forma_pago=1,1,0)) as pagos_efectivosss,
							sum(if(a.forma_pago=1,a.monto,0)) as pagos_efectivos_montooo,
							sum(if(a.forma_pago in (2,3,4,5),1,0)) as pagos_bancosss,
							sum(if(a.forma_pago in (2,3,4,5),a.monto,0)) as pagos_bancos_montooo
		FROM pagos_caja a

		WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus='A'";
		$_select1.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		
		$select1= "SELECT sum(if(a.tipo in (0) and b.forma_pago =1,1,0)) as pagos_efectivos,
							sum(if(a.tipo in (0) and b.forma_pago =1,a.monto,0)) as pagos_efectivos_monto,
							sum(if(a.tipo in (0) and b.forma_pago in (2,3,4),1,0)) as pagos_bancos,
							sum(if(a.tipo in (0) and b.forma_pago in (2,3,4),a.monto,0)) as pagos_bancos_monto
		FROM vales_pago_anticipado a
        LEFT JOIN pagos_caja b ON b.cve = a.pago AND b.plaza = a.plaza
        
        WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus='A' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		
		/*$select1= " SELECT  sum(if(a.forma_pago=1,1,0)) as pagos_efectivos,
							sum(if(a.forma_pago=1,a.monto,0)) as pagos_efectivos_monto,
							sum(if(a.forma_pago in (2,3,4,5),1,0)) as pagos_bancos,
							sum(if(a.forma_pago in (2,3,4,5),a.monto,0)) as pagos_bancos_monto
		FROM pagos_caja a
		LEFT JOIN vales_pago_anticipado b ON a.cve = b.pago AND a.plaza = b.plaza
		WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus='A'";
		$select1.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";*/
//		$select1.=" GROUP BY a.cve ORDER BY a.cve DESC";
		$res1=mysql_query($select1);
		
		
//		echo $select1;
		
		
		
//		$totalRegistros = mysql_num_rows($res);
		
//		echo $select;

		

		
			$html.= '<table width="100%" border="0" cellpadding="4" cellspacing="1"  style="font-size:8.7px">';
			$html.= '<tr bglor="#E9F2F8">
					 <td width="100" align="center">Tipo</td>
					 <td align="center">Cantidad</td>
					 <td align="center">Importe</td>';
			$html.= '</tr>';

						$t=0;
			$total1;
			$row=mysql_fetch_array($res);

			$sel2="SELECT sum(devolucion) as m_devoluvion from devolucion_certificado where estatus='A' and fecha BETWEEN '".$_POST['fecha_ini']."' AND '{$_POST['fecha_fin']}' and plaza='".$_POST['plazausuario']."'";
			$rs2=mysql_query($sel2) or die(mysql_error());
			$row2=mysql_fetch_array($rs2) or die(mysql_error());	
			$row['efectivos_monto']-=$row2['m_devoluvion'];
				//rowb();
				
	//			$html.= '<td>Exentos</td><td align="center">'.htmlentities($row['exentos']).'</td><td align="right">'.number_format($row['exentos_monto'],2).'</td></tr>';
				$html.= '<tr> <td>Canje de Pagos Anticipados</td><td align="center">'.htmlentities($row['vales_usados']).'</td><td align="right">'.number_format(0,2).'</td></tr>';
				$html.= '<tr><td>Intentos</td><td align="center">'.htmlentities($row['intentos']).'</td><td align="right">'.number_format($row['intentos_monto'],2).'</td></tr>';
				$html.= '<tr> <td>Cortesias</td><td align="center">'.htmlentities($row['cortesias']).'</td><td align="right">'.number_format($row['cortesias_monto'],2).'</td></tr>';
				$cant=$row['intentos']+$row['cortesias']+$row['vales_usados'];
				$tot=$row['exentos_monto']+$row['intentos_monto']+$row['cortesias_monto'];
				$html.= '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
							 <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.$cant.'</td>
					         <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">'.number_format($tot,2).'</td></tr>';
				$html.= '<tr><td colspan="3"></td></tr>';
				
				
				 $html.= '<tr><td>Creditos Verificados</td><td align="center">'.htmlentities($row['credito']).'</td><td align="right">'.number_format($row['credito_monto'],2).'</td></tr>';
				 $html.= '<tr><td>Depositos en Efectivo</td><td align="center">'.htmlentities($row['efectivos']).'</td><td align="right">'.number_format($row['efectivos_monto'],2).'</td></tr>';				
				 $html.= '<tr><td>Banco Tarjetas Debito Y Credito</td> <td align="center">'.htmlentities($row['bancos']).'</td><td align="right">'.number_format($row['bancos_monto'],2).'</td></tr>';
				 $cant2=$row['efectivos']+$row['bancos']+$row['credito'];
				 $tot2=$row['efectivos_monto']+$row['bancos_monto']+$row['credito_monto'];
				 $html.= '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
					    <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.$cant2.'</td>
					   <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">'.number_format($tot2,2).'</td></tr>';
				 $html.= '<tr><td colspan="3"></td></tr>';
				
				
				
//				$cant=$row['efectivos']+$row['exentos']+$row['bancos']+$row['intentos']+$row['cortesias']+$row['vales_usados']+$row['reposiciones'];
//				$tot=$row['efectivos_monto']+$row['exentos_monto']+$row['bancos_monto']+$row['intentos_monto']+$row['cortesias_monto']+$row['reposiciones_monto'];



						  $t11=$row['efectivos_monto']+$row['exentos_monto'];
						  $t22=$row['bancos_monto'];
						  $t77=$row['reposiciones_monto'];
						  $total1+=$tot+$tot2;

				$html.= '<tr><td colspan="3"></td></tr>';
				

	//			$html.= '<td>Pagos Anteriores</td><td align="center">'.number_format($row['pagos_anteriores'],0).'</td><td></td></tr>';
	//			$html.= '<tr><td>Pagados No verificados</td><td align="center">'.number_format($row['no_verificado'],0).'</td><td></td></tr>';
				$html.= '<tr><td colspan="3"></td></tr>';
				$row1=mysql_fetch_array($res1);
				$html.='<tr><td>Compras De Vales Antticipados En Efectivo</td><td align="center">'.htmlentities($row1['pagos_efectivos']).'</td><td align="right">'.number_format($row1['pagos_efectivos_monto'],2).'</td></tr>';
				$html.='<tr><td>Compra De Vales Anticipados En Banco</td><td align="center">'.htmlentities($row1['pagos_bancos']).'</td><td align="right">'.number_format($row1['pagos_bancos_monto'],2).'</td></tr>';
				$tot1=$row1['pagos_bancos_monto']+$row1['pagos_efectivos_monto'];
				$t55=$row1['pagos_efectivos_monto'];
				$t66=$row1['pagos_bancos_monto'];
				$total1+=$tot1;
				$html.= '
					  <tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
				          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.($row1['pagos_efectivos']+$row1['pagos_bancos']).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">'.number_format($tot1,2).'</td></tr>
				<tr><td colspan="3"></td></tr>';
				
				$html.= '
					  <tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL VERIFICACIONES PAGADAS</td>
				          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.($row1['pagos_efectivos']+$row1['pagos_bancos']+$cant2).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">'.number_format($tot1+$tot2,2).'</td></tr>
				<tr><td colspan="3"></td></tr>';
				
				//$cant+=$row['efectivos_copias']+$row['bancos_copias'];
			//	$html.= '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right"></td>
			//	          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
			//			  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right"></td></tr>';
						 // $t33=$row['efectivos_copias'];
						 // $t44=$row['bancos_copias'];
						 // $total1+=$cant;				
			//	$html.= '<tr><td colspan="3"></td></tr>';
				
				$html.='';
				$cant=0;
				$html.= '<tr> <td>Reposiciones</td><td align="center">'.htmlentities($row['reposiciones']).'</td><td align="right">'.number_format($row['reposiciones_monto'],2).'</td></tr>';
				$html.='<tr><td colspan="3"></td></tr>';				
	//			$html.= '<td>Copias en Efectivo</td><td></td><td align="right">'.number_format($row['efectivos_copias'],2).'</td></tr>';
				$html.= '<tr><td>Copias en Bancos</td><td></td><td align="right">'.number_format($row['bancos_copias'],2).'</td></tr>';
				$cant+=$row['reposiciones_monto']+$row['bancos_copias'];
				$html.= '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
				          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">'.number_format($cant+$tot1+$tot2,2).'</td></tr>';
						  $t33=$row['efectivos_copias'];
						  $t44=$row['bancos_copias'];
						  $total1+=$cant+$row['reposiciones_monto'];				
				$html.= '<tr><td colspan="3"></td></tr>';
				
				$html.= '<tr><td>Pagados No verificados</td><td align="center">'.number_format($row['no_verificado'],0).'</td><td></td></tr>';
				$html.= '<tr><td>Copias en Efectivo</td><td></td><td align="right">'.number_format($row['efectivos_copias'],2).'</td></tr>';
				$html.= '
					  <tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL  </td>
				          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"><!--'.($row1['pagos_efectivos']+$row1['pagos_bancos']+$cant2).'--></td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">'.number_format($row['efectivos_copias']+$cant+$tot1+$tot2,2).'</td></tr>
				<tr><td colspan="3"></td></tr>';
			//		$html.= $select1;
			
	

				$select7=" SELECT sum(a.num_ticket) as recuperados ,a.*, IF(ISNULL(MAX(b.cve)), '', CONCAT(MIN(b.cve),' - ',MAX(b.cve))) as vales ,sum(a.monto) as recuperados_monto
						   FROM pagos_caja a
						   LEFT JOIN vales_pago_anticipado b ON a.cve = b.pago AND a.plaza = b.plaza
						   WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and a.estatus!='C' AND a.tipo_pago='2'";
				
				$re7=mysql_query($select7);
				$row7 = mysql_fetch_array($re7);
				
				$select8=" SELECT count(a.cve) as devoluciones,a.*, f.cve as referencia ,sum(a.monto_venta) as devoluciones_monto
				FROM devolucion_certificado a
							LEFT JOIN cobro_engomado_referencia f ON a.plaza = f.plaza AND a.ticket = f.ticket
							WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and a.estatus!='C'";
				
				$re8=mysql_query($select8);
				$row8 = mysql_fetch_array($re8);
				$html.= '<tr><th align="left" colspan="3">Creditos</th></tr>';
				$html.= '<tr><td>Recuperacion del Mes</td><td align="center">'.number_format($row7['recuperados'],0).'</td><td align="right">'.number_format($row7['recuperados_monto'],2).'</td></tr>';
				$html.= '
					  <tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="left">Devoluciones  </td>
				          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($row8['devoluciones'],0).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">'.number_format($row8['devoluciones_monto'],2).'</td></tr>
						  <tr><td colspan="3" align="right">'.number_format($cant+$tot1+$tot2,2).'</td></tr>
				<tr><td colspan="3"></td></tr>';
				// $html.= '
					  // <tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">Devoluciones  </td>
				          // <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.($row8['devoluciones'].'</td>
						  // <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">'.number_format($row8['devoluciones_monto'],2).'</td></tr>
					  // <tr><td colspan="3" align="right"></td></tr>'
				// $html.=' <tr><td colspan="3"></td></tr>';
				
				$html.= '<tr><th align="left" colspan="3">DESGLOSE DE CERTIFICADOS DE ENTREGA (utilizados)</th></tr>';
				$cant=0;
				foreach($array_engomado as $k=>$v){
		
				$select="SELECT count(a.cve) as resultado,sum(a.monto) as resultado_monto 
				FROM certificados a 
				INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante 
				WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") and a.estatus!='C' ORDER BY a.cve DESC";
				$re1=mysql_query($select);
				$row3 = mysql_fetch_array($re1);
				/*$selec="SELECT count(a.cve) as resultado FROM certificados a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") and a.estatus='C' ORDER BY a.cve DESC";
				$re2=mysql_query($selec);
				$row4 = mysql_fetch_array($re2);*/
		///		$selec= " SELECT count(cve) as resultado FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND engomado IN (".$k.") AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		///		$re2=mysql_query($selec);
		///		$row4 = mysql_fetch_array($re2);
				$sele="SELECT count(a.cve) as resultado FROM certificados a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") ORDER BY a.cve DESC";
				$re3=mysql_query($sele);
				$row5 = mysql_fetch_array($re3);
				$re=0;
				$html.= '<tr><td align="left">'.$v.'</td>
				<td align="center">&nbsp;'.$row3['resultado'].'</td>
				<td align="right">&nbsp;'.number_format($row3['resultado']*$array_engomado_precio[$k],2).' </td>';
	//			$re=$row3['resultado'] + $row4['resultado'];
//				$html.='<td align="right">&nbsp;'.$re.'</td></tr>';
				$html.='</tr>';
				$cant+=$row3['resultado'];
				$tot_uti+=$row3['resultado']*$array_engomado_precio[$k];
				}
				
				$html.= '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
				          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.$cant.'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">'.number_format($tot_uti,2).'</td></tr>';
				$html.= '<tr><td colspan="3"></td></tr>';
				
				$html.= '<tr><th align="left" colspan="3">DESGLOSE DE CERTIFICADOS CANCELADOS</th></tr>';
				$cant=0;
				foreach($array_engomado as $k=>$v){
				$select2= " SELECT count(cve) as cancelados FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND engomado IN (".$k.")";
				$select2.=" AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
				if ($_POST['anio']!="all") { $select.=" AND anio='".$_POST['anio']."' "; }
				//if ($_POST['certificado']!="") { $select.=" AND certificado='".$_POST['certificado']."' "; }
				$select2.=" ORDER BY cve DESC";
			//	$html.= $select2;
				$res2=mysql_query($select2)or die(mysql_error());
				$row2 = mysql_fetch_array($res2);
				$html.= '<tr><td align="left">'.$v.'</td>
				<td align="center">&nbsp;'.$row2['cancelados'].'</td>
				<td align="right">&nbsp;'.number_format($row2['cancelados']*$array_engomado_precio[$k],2).'</td></tr>';
				$cant+=$row2['cancelados'];
				$tot_can+=$row2['cancelados']*$array_engomado_precio[$k];
				}
				
				$html.= '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
				          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.$cant.'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">'.number_format($tot_can,2).'</td></tr>';
				$html.= '<tr><td colspan="3"></td></tr>';
				
				$html.= '<tr><td colspan="3"></td></tr>';
				
				//$html.= '<tr><th align="left" colspan="3">EXISTENCIA EN ALMACEN</th></tr>';
				
				/*$_POST['mostrar'] == 1;
				$select6= " SELECT * FROM compra_certificados 
				WHERE plaza='".$_POST['plazausuario']."'";
//								WHERE plaza='".$_POST['plazausuario']."' and fecha_compra BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
				$select6.=" AND anio IN (".$_POST['anio'].") and engomado not in (12,22)" ; 
				$select6.=" ORDER BY fecha_compra DESC,cve DESC";
				$res6=mysql_query($select6) or die(mysql_error());
				$t=$t2=$t3=0;
				$exist =array();
			$nivelUsuario = nivelUsuario();
			//$html.=''.$select6.'';
			while($row=mysql_fetch_array($res6)) {
				if($row['estatus']=='C')
					$cantidad = 0;
				else
					$cantidad=$row['foliofin']+1-$row['folioini'];
				$puede_cancelar = 0;
				$diferente=0;
				$res1=mysql_query("SELECT cve FROM certificados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."' AND estatus!='C'");
				$res2=mysql_query("SELECT cve FROM certificados_cancelados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."' AND estatus!='C'");
				if(mysql_num_rows($res1)==0 && mysql_num_rows($res2)==0) $puede_cancelar = 1;
				$entregados = mysql_num_rows($res1) + mysql_num_rows($res2);
				$faltantes = $cantidad-$entregados;
				if($faltantes < 0) $faltantes = 0;
				if($_POST['mostrar'] == 0 || ($_POST['mostrar'] == 1 && $cantidad>$entregados) || ($_POST['mostrar'] == 2 && $cantidad<=$entregados)){
					
					if($row['estatus']=='C'){
						//$html.= 'Cancelado';
						$cantidad=0;
					}
					$exist[$row['engomado']]+=$faltantes;
					$faltantes;
					
					
					$t+=$cantidad;
					$t2+=round($row['costo']*$cantidad,2);
					$t3+=$faltantes;
				}
			}
			$x=0;
			$y=count($exist);
			$cant=0;
			foreach($exist as $k => $v){
				if($x==0){$html.= '<tr><th align="left" colspan="3">EXISTENCIA EN ALMACEN</th></tr>';}
				$html.= '<tr><td align="left">'.$array_engomadoo[$k].'</td>
				<td align="center">&nbsp;'.$v.'</td>';
				$cant+=$v;
				$x++;
				if($y==$x){
					$html.= '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
				          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.$cant.'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right"></td></tr>';
				}
			}*/

				$html.= '<tr><th align="left" colspan="3">EXISTENCIA EN ALMACEN</th></tr>';
			$res1=mysql_query("SELECT cve, nombre FROM engomados WHERE entrega=1 ORDER BY nombre");
			$cant=0;
			$dato1=0;
			if ($_POST['plazausuario'] == '') $_POST['plazausuario']=0;
			while($row1 = mysql_fetch_assoc($res1)){
				$existencia = 0;
				$res2=mysql_query("SELECT SUM(foliofin+1-folioini) as registros FROM compra_certificados WHERE plaza={$_POST['plazausuario']} AND engomado={$row1['cve']} AND fecha_compra <= '{$_POST['fecha_fin']}' AND estatus!='C'");
				$row2 = mysql_fetch_assoc($res2);
				$existencia += $row2['registros'];
				$res2=mysql_query("SELECT COUNT(cve) as registros FROM certificados WHERE plaza={$_POST['plazausuario']} AND engomado={$row1['cve']} AND fecha <= '{$_POST['fecha_fin']}' AND estatus!='C'");
				$row2 = mysql_fetch_assoc($res2);
				$existencia -= $row2['registros'];
				$res2=mysql_query("SELECT COUNT(cve) as registros FROM certificados_cancelados WHERE plaza={$_POST['plazausuario']} AND engomado={$row1['cve']} AND fecha <= '{$_POST['fecha_fin']}' AND estatus!='C'");
				$row2 = mysql_fetch_assoc($res2);
				$existencia -= $row2['registros'];
				$html.= '<tr><td>'.$row1['nombre'].'</td><td align="center">'.number_format($existencia,0).'</td></tr>';
				if($row1['cve']==23)$dato1=$existencia;
				$cant += $existencia;
			}
			$html.= '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
				          <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant,0).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right"></td></tr>';
			
			
			
			
			
			$html.= '	
				<tr>
				<td colspan="2" gcolor="#E9F2F8">';;$html.= '</td>
				<td align="center" gcolor="#E9F2F8">'.$total.'</td>

				</tr>
			</table>';
			
			$select9="SELECT count(a.cve) as resultado,sum(a.monto) as resultado_monto 
				FROM certificados a 
				INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante 
				WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (23) and a.estatus!='C' ORDER BY a.cve DESC";
				$re9=mysql_query($select9);
				$row9 = mysql_fetch_array($re9);
				
			$select10= " SELECT count(cve) as cancelados FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND engomado IN (23)";
				$select10.=" AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
				//if ($_POST['anio']!="all") { $select.=" AND anio='".$_POST['anio']."' "; }
				//if ($_POST['certificado']!="") { $select.=" AND certificado='".$_POST['certificado']."' "; }

			//	$html.= $select2;
				$res10=mysql_query($select10)or die(mysql_error());
				$row10 = mysql_fetch_array($res10);
			$html.='<table>
			<tr><th>Desglose de Certificados Exentos</th><th>Utilizados</th><th>Cancelados</th><th>Existencia Almacen</th></tr>
			<tr>
			<td>Verificacion Exento</td>
			<td>'.$row9['resultado'].'</td>
			<td>'.$row10['cancelados'].'</td>
			<td>'.$dato1.'</td>
			</tr>
			</table>';
			
			
			$html.='</table></body></html>';
	 
echo $html;	 
		exit();	
}	


if($_POST['ajax']=='1.res') {
		
		
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8">
			<th width="100">Tipo</th>
			
			<th>Pagados</th>
			<th>Pagos Anticipados</th>
			<th>Creditos</th>
			<th>Intentos</th>
			<th>Cortesias</th>
			<th>Entregados</th>
			
			
			<th>Cancelados</th>
			<th>Utilizados</th>

			
			<th>Venta</th>
			<th>Costo Utilizados</th>
			<th>Fondo Ambiental</th>
			<!--<th>Utilidad</th>-->';
			$array_engomado_precio_venta[19]=628.00;
			foreach($array_engomado as $k=>$v){
				if($k==24){$fondo_ambiental=$fondo_ambiental_excento;}
				$select1="SELECT 
				sum(if(a.estatus!='C',1,0)) as resultado,
				sum(if( a.estatus!='C',a.monto,0)) as resultado_monto ,
				
				sum(if(((b.tipo_pago=1 or b.tipo_pago=5 or b.tipo_pago=7) and b.tipo_venta=0 and a.estatus!='C'),1,0)) as pagados,
				SUM(IF(b.tipo_venta=1 and a.estatus!='C',1,0)) as intentos,
				SUM(IF(b.tipo_venta=2 and a.estatus!='C',1,0)) as cortesias, 
				SUM(IF(b.tipo_venta=0 and b.tipo_pago=6 and a.estatus!='C',1,0)) as pagos_anticipados,
				SUM(IF(b.tipo_venta=0 and b.tipo_pago=2 and a.estatus!='C',1,0)) as creditos,
				sum(if(a.estatus='C',1,0)) as resultado_cancelados,
				count(a.cve) as resultado_todos
				
				FROM certificados a 
				INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante 
				WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") ORDER BY a.cve DESC";
				/*$select1="SELECT 
				count(a.cve) as resultado,
				sum(a.monto) as resultado_monto ,
				
				sum(if(((b.tipo_pago=1 or b.tipo_pago=5 or b.tipo_pago=7) and b.tipo_venta=0),1,0)) as pagados,
				SUM(IF(b.tipo_venta=1,1,0)) as intentos,
				SUM(IF(b.tipo_venta=2,1,0)) as cortesias, 
				SUM(IF(b.tipo_venta=0 and b.tipo_pago=6,1,0)) as pagos_anticipados,
				SUM(IF(b.tipo_venta=0 and b.tipo_pago=2,1,0)) as creditos
				
				
				FROM certificados a 
				INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante 
				WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") and a.estatus!='C' ORDER BY a.cve DESC";*/
				$re1=mysql_query($select1) or die (mysql_error());
				$row1 = mysql_fetch_array($re1);
				
				
				$select6="SELECT 
				count(a.cve) as cancelados
				
				FROM certificados_cancelados a
				WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") ORDER BY a.cve DESC";
				$re6=mysql_query($select6);
				$row6 = mysql_fetch_array($re6);
				
				
				
			
			echo '<tr><td>'.$v.'</td>
			    
				<td align="center">'.number_format($row1['pagados'],0).'</td>
				<td align="center">'.number_format($row1['pagos_anticipados'],0).'</td>
				<td align="center">'.number_format($row1['creditos'],0).'</td>
				<td align="center">'.number_format($row1['intentos'],0).'</td>
				<td align="center">'.number_format($row1['cortesias'],0).'</td>
				<td align="center">'.number_format($row1['resultado'],0).'</td>
				<td align="center">'.number_format($row6['cancelados'],0).'</td>
				<td align="center">'.number_format($row1['resultado']+$row6['cancelados'],0).'</td>
				
				
				
				
				
				
				
				
				<td align="center">'.number_format((($row1['pagados']) * $array_engomado_precio_venta[$k] ),2).'</td>
				<td align="center">'.number_format(($row6['cancelados']+$row1['resultado'] * $array_engomado_precio[$k] ),2).'</td>
				<td align="center">'.number_format(($row1['resultado'] * $fondo_ambiental ),2).'</td>
				<!--<td align="center">'.number_format(($row1['pagados'] * $array_engomado_precio_venta[$k] )-($row1['resultado'] * $array_engomado_precio[$k] )-($row1['resultado'] * $fondo_ambiental ),2).'</td>-->
				
				</tr>';
				
				$cant += $row1['resultado'];
				$cant1 += ($row1['pagados']);
				$cant1_1 += ($row1['intentos']);
				$cant1_2 += ($row1['cortesias']);
				$cant1_3 += ($row1['pagos_anticipados']);
				$cant1_4 += ($row1['creditos']);
				$cant2 += (($row6['cancelados']+$row1['pagados']) * $array_engomado_precio_venta[$k] );
				$cant3 += ($row1['resultado'] * $array_engomado_precio[$k] );
				$cant4 += (($row1['resultado'] * $fondo_ambiental ));
				$cant5 += ($row1['pagados'] * $array_engomado_precio_venta[$k] )-($row1['resultado'] * $array_engomado_precio[$k] )-($row1['resultado'] * $fondo_ambiental );
				$can8 += $row6['cancelados'];
				$can9 += $row1['resultado']+$row6['cancelados'];
				
				
				$cant6 += ($existencia+$row3['cant'] -$row4['resultado']);
				$cant7 += (($existencia * $array_engomado_precio[$row1['cve']])+($row3['cant'] * $array_engomado_precio[$row1['cve']])-($row4['resultado'] * $array_engomado_precio[$row1['cve']]));
				
			}
			echo '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_3,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_4,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_1,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_2,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($can8,0).'</td>
						  
						  <!--<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_1,0).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_2,0).'</td>-->
						  
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($can9,0).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant2,2).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant3,2).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant4,2).'</td>
						  <!--<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant5,2).'</td>-->
						  </tr>';
			
			
			
			
			
			echo '	
				
			';
			
			
			$select2= "SELECT sum(if(a.tipo in (0) and b.forma_pago =1,1,0)) as pagos_efectivos,
							sum(if(a.tipo in (0) and b.forma_pago =1,a.monto,0)) as pagos_efectivos_monto,
							sum(if(a.tipo in (0) and b.forma_pago in (2,3,4),1,0)) as pagos_bancos,
							sum(if(a.tipo in (0) and b.forma_pago in (2,3,4),a.monto,0)) as pagos_bancos_monto
		FROM vales_pago_anticipado a
        LEFT JOIN pagos_caja b ON b.cve = a.pago AND b.plaza = a.plaza
        
        WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus='A' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		$res2=mysql_query($select2);
		$row2=mysql_fetch_array($res2);


		$select3= " SELECT sum(if(a.tipo_venta IN (0,3) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,1,0)) as efectivos1,
				  sum(if(a.tipo_venta IN (0) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,1,0)) as efectivos,
				  sum(if(a.tipo_pago in (2) AND a.estatus='A',1,0)) as credito,
				  sum(if(a.estatus='A' AND a.engomado=23,1,0)) as exentos,
				  sum(if(a.tipo_pago in (5,7) AND a.estatus='A',1,0)) as bancos,
				  sum(if(a.tipo_venta=1 AND a.estatus='A',1,0)) as intentos,
				  sum(if(a.tipo_venta=2 AND a.estatus='A',1,0)) as cortesias,
				  sum(if(a.estatus='A' and a.vale_pago_anticipado>0 AND a.tipo_pago=6,1,0)) as vales_usados,
				  sum(if(a.tipo_venta=3 AND a.estatus='A',1,0)) as reposiciones,
				  
				  sum(if(a.tipo_venta=0 AND a.estatus='A',if((a.tipo_pago in (5,7)) or (a.tipo_pago=6),0,a.monto),0)) as efectivos_montooooo,
				  sum(if(a.tipo_venta in (0,3) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,a.monto,0)) as efectivos_monto1,
				  sum(if(a.tipo_venta in (0) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,a.monto,0)) as efectivos_monto,
				  sum(if(a.tipo_pago in (2) AND a.estatus='A',a.monto,0)) as credito_monto,
				  sum(if(a.estatus='A' AND a.engomado=23,a.monto,0)) as exentos_monto,
				  sum(if(a.tipo_pago in (5,7) AND a.estatus='A',a.monto,0)) as bancos_monto,
				  sum(if(a.tipo_venta=1 AND a.estatus='A',a.monto,0)) as intentos_monto,
				  sum(if(a.tipo_venta=2 AND a.estatus='A',a.monto,0)) as cortesias_monto,
				  sum(if(a.tipo_venta=3 AND a.estatus='A',a.monto,0)) as reposiciones_monto,
				  
				  sum(if((a.tipo_venta=0) and (a.estatus in('A','B','C')),if((a.tipo_pago in (2,6,5,7,12)) or (a.engomado=23),0,a.copias),0)) as efectivos_copias2,
				  sum(if(a.tipo_pago in (1,2,6,12),a.copias,0)) as efectivos_copias,
				  sum(if(a.tipo_pago not in (5,7,2,6,12) and (a.estatus in('A','B','C')),a.copias,0)) as efectivos_copiassssss,
				  sum(if(a.tipo_pago in (5,7),a.copias,0)) as bancos_copias,
				  
				  sum(if(((IFNULL(b.cve,0)=0) AND a.estatus='A'),1,0)) as no_verificado,
				  sum(if(b.fecha > a.fecha,1,0)) as pagos_anteriores
				  
						
		FROM cobro_engomado a 
		LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
		LEFT JOIN depositantes c ON c.cve = a.depositante AND c.plaza = a.plaza 
		WHERE a.plaza='".$_POST['plazausuario']."' ";
			$select3.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
			//if ($_POST['entidad']!="all") { $select.=" AND a.entidad='".$_POST['entidad']."' "; }
			if ($_POST['anio']!="all") { $select.=" AND a.anio='".$_POST['anio']."' "; }
			//////sum(if(a.tipo_venta=0,if((a.tipo_pago in (5,7)) or (a.engomado=23) or (a.tipo_pago=6),0,a.copias),0)) as efectivos_copias,
			//////sum(if(a.tipo_pago in (5,7) and a.estatus in(A,B,C),a.copias,0)) as bancos_copias
			//AND a.estatus='A'
		$res3=mysql_query($select3) or die(mysql_error());
		$row3=mysql_fetch_array($res3);

			echo'
			<tr><td colspan="7">&nbsp;</td></tr>
			<tr><td colspan="2">Tipo</td><td colspan="">Cantidad</td><td colspan="">Costo</td><td colspan="3">&nbsp;</td></tr>
			<tr><td colspan="2">Compras De Vales Antticipados En Efectivo</td><td align="center">'.htmlentities($row2['pagos_efectivos']).'</td><td align="center">'.number_format($row2['pagos_efectivos_monto'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Banco Tarjetas Debito Y Credito</td> <td align="center">'.htmlentities($row3['bancos']).'</td><td align="center">'.number_format($row3['bancos_monto'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Creditos Verificados</td><td align="center">'.htmlentities($row3['credito']).'</td><td align="center">'.number_format($row3['credito_monto'],2).'</td><td colspan="3"></td></tr>';
			$cant6=$cant1+$row2['pagos_efectivos']+$row3['bancos']+$row3['credito'];
			$cant7=$cant2+$row2['pagos_efectivos_monto']+$row3['bancos_monto']+$row3['credito_monto'];
			
			
			echo'<tr><td colspan="2" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant6,0).'</td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant7,2).'</td>
						  <td colspan="3"></td>
						  </tr>';
			
			
			$select4=" SELECT count(a.cve) as devoluciones,a.*, f.cve as referencia ,sum(a.monto_venta) as devoluciones_monto
				FROM devolucion_certificado a
							LEFT JOIN cobro_engomado_referencia f ON a.plaza = f.plaza AND a.ticket = f.ticket
							WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and a.estatus!='C'";
				
				$re4=mysql_query($select4);
				$row4 = mysql_fetch_array($re4);
			echo'
			<tr><td colspan="7">&nbsp;</td></tr>
			<tr><td colspan="2">Copias en Bancos</td><td></td><td align="center">'.number_format($row3['bancos_copias'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Reposiciones</td><td align="center">'.htmlentities($row3['reposiciones']).'</td><td align="center">'.number_format($row3['reposiciones_monto'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Devoluciones</td><td align="center">'.htmlentities($row4['devoluciones']).'</td><td align="center">'.number_format($row4['devoluciones_monto'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Pagos no Verificados</td><td align="center">'.htmlentities($row3['no_verificado']).'</td><td align="center">'.number_format($row3['no_verificado']*$costo_verificacion,2).'</td><td colspan="3"></td></tr>
			';
			$cant8=0;
			$cant9=$cant7+$row3['bancos_copias']+$row3['reposiciones_monto']+$row4['devoluciones_monto']+($row3['no_verificado']*$costo_verificacion);
			
			
			echo'<tr><td colspan="2" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">VENTA TOTAL</td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant9,2).'</td>
						  <td colspan="3"></td>
						  </tr>
						  
				 <tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Utilizados mas Fondo Ambiental</td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant3+$cant4,2).'</td>
				 
				 <tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Utilidad</td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant7-($cant3+$cant4),2).'</td>
				 
				 <tr>
				 <td colspan="2" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Venta</td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($costo_verificacion,2).'</td>
				 
				  <tr>
				 <td colspan="2" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Promedio de Gastos</td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format(($cant3+$cant4)/($cant6),2).'</td>
				 
				  <tr>
				 <td colspan="2" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Utilidad</td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format( $costo_verificacion-(($cant3+$cant4)/($cant6)),2).'</td>
				 </tr>';



			
			echo'</table>';
			
			
			

			
		
		exit();	
}	

if($_POST['ajax']==1) {
		
		
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8">
			<th width="100">Tipo</th>
			
			<th>Pagados</th>
			<th>Pagos Anticipados</th>
			<th>Creditos</th>
			<th>Intentos</th>
			<th>Cortesias</th>
			<th>Entregados</th>
			
			
			<th>Cancelados</th>
			<th>Utilizados</th>

			
			<th>Venta</th>
			<th>Costo Utilizados</th>
			<th>Fondo Ambiental</th>
			<!--<th>Utilidad</th>-->';
			$array_engomado_precio_venta[19]=628.00;
			foreach($array_engomado as $k=>$v){
				if($k==24){$fondo_ambiental=$fondo_ambiental_excento;}
				$select1="SELECT 
				sum(if(a.estatus!='C',1,0)) as resultado,
				sum(if( a.estatus!='C',a.monto,0)) as resultado_monto ,
				
				sum(if(((b.tipo_pago=1 or b.tipo_pago=5 or b.tipo_pago=7) and b.tipo_venta=0 and a.estatus!='C'),1,0)) as pagados,
				SUM(IF(b.tipo_venta=1 and a.estatus!='C',1,0)) as intentos,
				SUM(IF(b.tipo_venta=2 and a.estatus!='C',1,0)) as cortesias, 
				SUM(IF(b.tipo_venta=0 and b.tipo_pago=6 and a.estatus!='C',1,0)) as pagos_anticipados,
				SUM(IF(b.tipo_venta=0 and b.tipo_pago=2 and a.estatus!='C',1,0)) as creditos,
				sum(if(a.estatus='C',1,0)) as resultado_cancelados,
				count(a.cve) as resultado_todos
				
				FROM certificados a 
				INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante 
				WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") ORDER BY a.cve DESC";
				/*$select1="SELECT 
				count(a.cve) as resultado,
				sum(a.monto) as resultado_monto ,
				
				sum(if(((b.tipo_pago=1 or b.tipo_pago=5 or b.tipo_pago=7) and b.tipo_venta=0),1,0)) as pagados,
				SUM(IF(b.tipo_venta=1,1,0)) as intentos,
				SUM(IF(b.tipo_venta=2,1,0)) as cortesias, 
				SUM(IF(b.tipo_venta=0 and b.tipo_pago=6,1,0)) as pagos_anticipados,
				SUM(IF(b.tipo_venta=0 and b.tipo_pago=2,1,0)) as creditos
				
				
				FROM certificados a 
				INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
				LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante 
				WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") and a.estatus!='C' ORDER BY a.cve DESC";*/
				$re1=mysql_query($select1) or die (mysql_error());
				$row1 = mysql_fetch_array($re1);
				
				
				$select6="SELECT 
				count(a.cve) as cancelados
				
				FROM certificados_cancelados a
				WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' 
				AND a.engomado IN (".$k.") ORDER BY a.cve DESC";
				$re6=mysql_query($select6);
				$row6 = mysql_fetch_array($re6);
				
				
				
			
			echo '<tr><td>'.$v.'</td>
			    
				<td align="center">'.number_format($row1['pagados'],0).'</td>
				<td align="center">'.number_format($row1['pagos_anticipados'],0).'</td>
				<td align="center">'.number_format($row1['creditos'],0).'</td>
				<td align="center">'.number_format($row1['intentos'],0).'</td>
				<td align="center">'.number_format($row1['cortesias'],0).'</td>
				<td align="center">'.number_format($row1['resultado'],0).'</td>
				<td align="center">'.number_format($row6['cancelados'],0).'</td>
				<td align="center">'.number_format($row1['resultado']+$row6['cancelados'],0).'</td>
				
				
				
				
				
				
				
				
				<td align="center">'.number_format((($row1['pagados']) * $array_engomado_precio_venta[$k] ),2).'</td>
				<td align="center">'.number_format(($row6['cancelados']+$row1['resultado'] * $array_engomado_precio[$k] ),2).'</td>
				<td align="center">'.number_format(($row1['resultado'] * $fondo_ambiental ),2).'</td>
				<!--<td align="center">'.number_format(($row1['pagados'] * $array_engomado_precio_venta[$k] )-($row1['resultado'] * $array_engomado_precio[$k] )-($row1['resultado'] * $fondo_ambiental ),2).'</td>-->
				
				</tr>';
				
				$cant += $row1['resultado'];
				$cant1 += ($row1['pagados']);
				$cant1_1 += ($row1['intentos']);
				$cant1_2 += ($row1['cortesias']);
				$cant1_3 += ($row1['pagos_anticipados']);
				$cant1_4 += ($row1['creditos']);
				$cant2 += (($row6['cancelados']+$row1['pagados']) * $array_engomado_precio_venta[$k] );
				$cant3 += ($row1['resultado'] * $array_engomado_precio[$k] );
				$cant4 += (($row1['resultado'] * $fondo_ambiental ));
				$cant5 += ($row1['pagados'] * $array_engomado_precio_venta[$k] )-($row1['resultado'] * $array_engomado_precio[$k] )-($row1['resultado'] * $fondo_ambiental );
				$can8 += $row6['cancelados'];
				$can9 += $row1['resultado']+$row6['cancelados'];
				
				
				$cant6 += ($existencia+$row3['cant'] -$row4['resultado']);
				$cant7 += (($existencia * $array_engomado_precio[$row1['cve']])+($row3['cant'] * $array_engomado_precio[$row1['cve']])-($row4['resultado'] * $array_engomado_precio[$row1['cve']]));
				
			}
			echo '<tr><td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_3,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_4,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_1,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_2,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant,0).'</td>
			<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($can8,0).'</td>
						  
						  <!--<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_1,0).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant1_2,0).'</td>-->
						  
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($can9,0).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant2,2).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant3,2).'</td>
						  <td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant4,2).'</td>
						  <!--<td style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant5,2).'</td>-->
						  </tr>';
			
			
			
			
			
			echo '	
				
			';
			
			
			$select2= "SELECT sum(if(a.tipo in (0) and b.forma_pago =1,1,0)) as pagos_efectivos,
							sum(if(a.tipo in (0) and b.forma_pago =1,a.monto,0)) as pagos_efectivos_monto,
							sum(if(a.tipo in (0) and b.forma_pago in (2,3,4,5),1,0)) as pagos_bancos,
							sum(if(a.tipo in (0) and b.forma_pago in (2,3,4,5),a.monto,0)) as pagos_bancos_monto
		FROM vales_pago_anticipado a
        LEFT JOIN pagos_caja b ON b.cve = a.pago AND b.plaza = a.plaza
        
        WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus='A' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		$res2=mysql_query($select2);
		$row2=mysql_fetch_array($res2);


		$select3= " SELECT sum(if(a.tipo_venta IN (0,3) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,1,0)) as efectivos1,
				  sum(if(a.tipo_venta IN (0) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,1,0)) as efectivos,
				  sum(if(a.tipo_pago in (2) AND a.estatus='A',1,0)) as credito,
				  sum(if(a.estatus='A' AND a.engomado=23,1,0)) as exentos,
				  sum(if(a.tipo_pago in (5,7) AND a.estatus='A',1,0)) as bancos,
				  sum(if(a.tipo_venta=1 AND a.estatus='A',1,0)) as intentos,
				  sum(if(a.tipo_venta=2 AND a.estatus='A',1,0)) as cortesias,
				  sum(if(a.estatus='A' and a.vale_pago_anticipado>0 AND a.tipo_pago=6,1,0)) as vales_usados,
				  sum(if(a.tipo_venta=3 AND a.estatus='A',1,0)) as reposiciones,
				  
				  sum(if(a.tipo_venta=0 AND a.estatus='A',if((a.tipo_pago in (5,7)) or (a.tipo_pago=6),0,a.monto),0)) as efectivos_montooooo,
				  sum(if(a.tipo_venta in (0,3) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,a.monto,0)) as efectivos_monto1,
				  sum(if(a.tipo_venta in (0) AND a.tipo_pago=1 AND a.estatus!='C' AND a.engomado!=23,a.monto,0)) as efectivos_monto,
				  sum(if(a.tipo_pago in (2) AND a.estatus='A',a.monto,0)) as credito_monto,
				  sum(if(a.estatus='A' AND a.engomado=23,a.monto,0)) as exentos_monto,
				  sum(if(a.tipo_pago in (5,7) AND a.estatus='A',a.monto,0)) as bancos_monto,
				  sum(if(a.tipo_venta=1 AND a.estatus='A',a.monto,0)) as intentos_monto,
				  sum(if(a.tipo_venta=2 AND a.estatus='A',a.monto,0)) as cortesias_monto,
				  sum(if(a.tipo_venta=3 AND a.estatus='A',a.monto,0)) as reposiciones_monto,
				  
				  sum(if((tipo_venta in (0,1,2)) and tipo_pago in (1,2,6,12) and a.estatus!='C' ,a.copias,0)) as efectivos_copias2,
				  sum(if(a.tipo_pago in (1,2,6,12),a.copias,0)) as efectivos_copias,
				  sum(if(a.tipo_pago not in (5,7,2,6,12) and (a.estatus in('A','B','C')),a.copias,0)) as efectivos_copiassssss,
				  sum(if((tipo_pago=7 or tipo_pago=5 )and tipo_venta=0 and a.estatus!='C',a.copias,0)) as bancos_copias,
				   sum(if(a.estatus!='C',a.copias,0)) as copiassssssssss,
				  
				  sum(if(((IFNULL(b.cve,0)=0) AND a.estatus='A'),1,0)) as no_verificado,
				  sum(if(b.fecha > a.fecha,1,0)) as pagos_anteriores
				  
						
		FROM cobro_engomado a 
		LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
		LEFT JOIN depositantes c ON c.cve = a.depositante AND c.plaza = a.plaza 
		WHERE a.plaza='".$_POST['plazausuario']."' ";
			$select3.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
			//if ($_POST['entidad']!="all") { $select.=" AND a.entidad='".$_POST['entidad']."' "; }
			if ($_POST['anio']!="all") { $select.=" AND a.anio='".$_POST['anio']."' "; }
			//////sum(if(a.tipo_venta=0,if((a.tipo_pago in (5,7)) or (a.engomado=23) or (a.tipo_pago=6),0,a.copias),0)) as efectivos_copias,
			//////sum(if(a.tipo_pago in (5,7) and a.estatus in(A,B,C),a.copias,0)) as bancos_copias
			//AND a.estatus='A'
		$res3=mysql_query($select3) or die(mysql_error());
		$row3=mysql_fetch_array($res3);

			echo'
			<tr><td colspan="7">&nbsp;</td></tr>
			<tr><td colspan="2">Tipo</td><td colspan="">Cantidad</td><td colspan="">Costo</td><td colspan="3">&nbsp;</td></tr>
			<tr><td colspan="2">VALES ANTICIPADOS PAGADOS EN EFECTIVO</td><td align="center">'.htmlentities($row2['pagos_efectivos']).'</td><td align="center">'.number_format($row2['pagos_efectivos_monto'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">VALES ANTICIPADOS PAGADOS EN BANCOS</td><td align="center">'.htmlentities($row2['pagos_bancos']).'</td><td align="center">'.number_format($row2['pagos_bancos_monto'],2).'</td><td colspan="3"></td></tr>
			<!--<tr><td colspan="2">Banco Tarjetas Debito Y Credito</td> <td align="center">'.htmlentities($row3['bancos']).'</td><td align="center">'.number_format($row3['bancos_monto'],2).'</td><td colspan="3"></td></tr>-->
			<tr><td colspan="2">Creditos Verificados</td><td align="center">'.htmlentities($row3['credito']).'</td><td align="center">'.number_format($row3['credito_monto'],2).'</td><td colspan="3"></td></tr>';
			$cant6=$row2['pagos_efectivos']+$row2['pagos_bancos'];
			$cant7=$row2['pagos_efectivos_monto']+$row2['pagos_bancos_monto']+$row3['credito_monto'];
			/*$cant6=$cant1+$row2['pagos_efectivos']+$row3['bancos']+$row3['credito'];
			$cant7=$cant2+$row2['pagos_efectivos_monto']+$row3['bancos_monto']+$row3['credito_monto'];*/
			
			
			echo'<tr><td colspan="2" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right">TOTAL</td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant7,2).'</td>
						  <td colspan="3"></td>
						  </tr>';
			
			
			$select4=" SELECT count(a.cve) as devoluciones,a.*, f.cve as referencia ,sum(a.monto_venta) as devoluciones_monto
				FROM devolucion_certificado a
							LEFT JOIN cobro_engomado_referencia f ON a.plaza = f.plaza AND a.ticket = f.ticket
							WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and a.estatus!='C'";
				
				$re4=mysql_query($select4);
				$row4 = mysql_fetch_array($re4);
			echo'
			<tr><td colspan="7">&nbsp;</td></tr>
			<tr><td colspan="2">Copias en Bancos</td><td></td><td align="center">'.number_format($row3['bancos_copias'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Copias en Efectivo</td><td></td><td align="center">'.number_format($row3['efectivos_copias2'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Reposiciones</td><td align="center"><!--'.htmlentities($row3['reposiciones']).'--></td><td align="center">'.number_format($row3['reposiciones_monto'],2).'</td><td colspan="3"></td></tr>
			<tr><td colspan="2">Devoluciones</td><td align="center">'.htmlentities($row4[' 	']).'</td><td align="center">'.number_format($row4['devoluciones_monto'],2).'</td><td colspan="3"></td></tr>
			<!--<tr><td colspan="2">Pagos no Verificados</td><td align="center">'.htmlentities($row3['no_verificado']).'</td><td align="center">'.number_format($row3['no_verificado']*$costo_verificacion,2).'</td><td colspan="3"></td></tr>-->
			';
			$cant8=0;
			$cant9=$row3['bancos_copias']+$row3['reposiciones_monto']+$row4['devoluciones_monto']+($row3['efectivos_copias2']);
			/*$cant9=$cant7+$row3['bancos_copias']+$row3['reposiciones_monto']+$row4['devoluciones_monto']+($row3['no_verificado']*$costo_verificacion);*/
			$cant10=$cant9+$cant7+$cant2;
			$cant11=$cant3+$cant4;
			$cant12=$cant10-$cant11;
			
			
			echo'<tr><td colspan="2" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right"> TOTAL</td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant9,2).'</td>
						  <td colspan="3"></td>
						  </tr>
						  
				<tr><td colspan="2" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="right"> TOTAL DE INGRESOS</td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
						  <td colspan="" style="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant10,2).'</td>
						  <td colspan="3"></td>
						  </tr>
						  
				 <tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Utilizados mas Fondo Ambiental</td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant3+$cant4,2).'</td>
				 </tr>
				 
				 <tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Utilidad de Operacion</td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($cant12,2).'</td>
				 </tr>
				 
				 <!--<tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align=""> COSTO DE VENTA POR VERIFICACION</td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($array_engomado_precio_venta[5],2).'</td>
				 </tr>
				 <tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">PROMEDIO DE GASTOS</td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format(($cant3+$cant4)/$can9,2).'</td>
				 </tr>
				 <tr>
				 <td colspan="2" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">UTILIDAD POR CERTIFICADO UTILIZADO </td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" tyle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format(($cant12)/($can9),2).'</td>
				 </tr>
				 -->
				 
				






				<!--<tr>
				 <td colspan="2" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Venta</td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format($array_engomado_precio_venta[5],2).'</td>
				 
				  <tr>
				 <td colspan="2" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Promedio de Gastos</td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format(($cant3+$cant4)/($can9),2).'</td>
				 
				  <tr>
				 <td colspan="2" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="">Utilidad</td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center"></td>
				 <td colspan="" stle="border-top-color:#000099; border-top-style:solid; border-top-width:1px;" align="center">'.number_format( ($cant12)/($can9),2).'</td>
				 </tr>-->';



			
			echo'</table>';
			
			
			

			
		
		exit();	
}	


top($_SESSION);

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	
	//Busqueda
	echo '<table><tr><td valign="top"><table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td>
			<td><a href="#" onClick="atcr(\'\',\'_blank\',\'110\',\'0\');"><img src="images/b_print.png" border="0" title="Imprimir"></a>Imprimir</td>
			<td><a href="#" onClick="atcr(\'\',\'_blank\',\'115\',\'0\');"><img src="images/b_print.png" border="0" title="Imprimir"></a>Excel</td>';
	echo '
		 </tr>';
	echo '</table>';

	echo '<table border="0">';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
//	echo '<tr><td>Entidad</td><td><select name="entidad" id="entidad"><option value="all">Todos</option>';
//	foreach($array_entidades as $k=>$v){
//		echo '<option value="'.$k.'">'.$v.'</option>';
//	}
//	echo '</select></td></tr>';
	echo '<tr><td>Certificacion</td><td><select name="anio" id="anio"><option value="all">Todos</option>';
	$first=true;
	foreach($array_anios as $k=>$v){
			echo '<option value="'.$k.'"';
			if($first) echo ' selected';
			echo '>'.$v.'</option>';
			$first=false;
	}
	echo '</select></td></tr>';

	echo '</table></td><td align="right" width="100">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div name="Totales" id="Totales"><div></td></tr></table>';
	echo '<br>';

//	echo '<span style="cursor:pointer" onClick="atcr(\'reporte_utilidad.php\',\'\',300,0)"><font size="20px" color="BLUE">Descargar Archivo de Impresion</font></span>';
//	echo '<br><font color="RED">Guardarlo en C:/xampp/htdocs</font>';
	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros(btn)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","reporte_utilidad.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&btn="+btn+"&anio="+document.getElementById("anio").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					datos = objeto.responseText.split("|*|");
					document.getElementById("Resultados").innerHTML = datos[0];
					//document.getElementById("Totales").innerHTML = datos[1];
					
					//document.getElementById("Resultados").innerHTML = objeto.responseText;
				}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}
	buscarRegistros(0); //Realizar consulta de todos los registros al iniciar la forma.
		
	function guardarCampo(folio, valor_anterior, campo, nombre, arreglo){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","reporte_utilidad.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=40&arreglo="+arreglo+"&nombre="+nombre+"&campo="+campo+"&folio="+folio+"&valor_anterior="+valor_anterior+"&valor="+document.getElementById(campo+"_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros(1);}
			}
		}
	}
	
	
	
	
	</Script>
	';

	
}
	
bottom();

if($cvecobro>0){
		echo '<script>atcr(\'reporte_utilidad.php?nuevo=1\',\'_blank\',\'101\','.$cvecobro.');</script>';
	}
?>

