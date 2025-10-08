<?php 
include ("main.php"); 
$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$res=mysql_query("SELECT local, validar_certificado FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];
$ValidarCertificados = $row[1];

$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND entrega=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio_compra'];
}


$res = mysql_query("SELECT * FROM anios_certificados  ORDER BY nombre DESC");
$rowAnio=mysql_fetch_array($res);


$array_estatus = array('A'=>'Activo','C'=>'Cancelado','E'=>'Confirmado');
/*** CONSULTA AJAX  **************************************************/



if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM compra_certificados 
		WHERE plaza='".$_POST['plazausuario']."'";
		if($_POST['fecha_ini'] != '') $select.=" AND fecha_compra >= '".$_POST['fecha_ini']."'";
		if($_POST['fecha_fin'] != '')$select .=" AND fecha_compra <= '".$_POST['fecha_fin']."'";
		if ($_POST['usuario']!="") { $select.=" AND usuario='".$_POST['usuario']."' "; }
		if ($_POST['engomado']!="") { $select.=" AND engomado='".$_POST['engomado']."' "; }
		if ($_POST['estatus']!="") { $select.=" AND estatus='".$_POST['estatus']."' "; }
		$select.=" AND anio IN (".$_POST['anio'].")"; 
		$select.=" ORDER BY fecha_compra DESC,cve DESC";
		$res=mysql_query($select) or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Consecutivo</th><th>Folio Compra</th><th>Fecha Compra</th><th>Fecha</th><th>Tipo de Certificado</th><th>Folio Inicial</th><th>Folio Final</th><th>Cantidad</th><th>Total</th><th>A&ntilde;o</th><th>Remanente</th><th>Usuario</th>';
			echo '</tr>';
			$t=$t2=$t3=0;
			$nivelUsuario = nivelUsuario();
			while($row=mysql_fetch_array($res)) {
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
					rowb();
					echo '<td align="center" width="40" nowrap>';
					if($row['estatus']=='C'){
						echo 'Cancelado';
						$cantidad=0;
					}
					/*elseif($row['estatus']=='E'){
						$cantidad=$row['foliofin']+1-$row['folioini'];
						if($_POST['cveusuario'] == 1){
							echo '<a href="#" onClick="atcr(\'compra_certificados.php\',\'\',\'10\','.$row['cve'].')"><img src="images/b_search.png" border="0" title="Imprimir '.$row['cve'].'"></a><br>';
						}
						echo 'Confirmado';
					}*/
					else{
						if($_POST['cveusuario'] == 1){
							echo '<a href="#" onClick="atcr(\'compra_certificados.php\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$row['cve'].'"></a>&nbsp;&nbsp;';
						}
						if($nivelUsuario > 0){
							echo '<a href="#" onClick="atcr(\'compra_certificados.php\',\'\',\'10\','.$row['cve'].')"><img src="images/b_search.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
						}
						/*$cantidad=$row['foliofin']+1-$row['folioini'];
						$puede_cancelar = 0;
						$res1=mysql_query("SELECT cve FROM certificados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."' AND estatus!='C'");
						$res2=mysql_query("SELECT cve FROM certificados_cancelados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."' AND estatus!='C'");
						if(mysql_num_rows($res1)==0 && mysql_num_rows($res2)==0) $puede_cancelar = 1;
						$entregados = mysql_num_rows($res1) + mysql_num_rows($res2);*/
						if(($nivelUsuario>1 && $puede_cancelar == 1) || $_POST['cveusuario']==1)
							echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar la compra\')) atcr(\'compra_certificados.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
					}	
					echo '</td>';
					if($_POST['cveusuario'] != 1)
						echo '<td align="center">'.htmlentities($row['cve']).'</td>';
					elseif($cantidad > $entregados)
						echo '<td align="center"><font color="RED">'.htmlentities($row['cve']).'</font></td>';
					else
						echo '<td align="center"><font color="BLUE">'.htmlentities($row['cve']).'</font></td>';
					echo '<td align="center">'.htmlentities($row['folio']).'</td>';
					echo '<td align="center">'.htmlentities($row['fecha_compra']).'</td>';
					echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
					echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
					echo '<td align="center">'.$row['folioini'].'</td>';
					echo '<td align="center">'.$row['foliofin'].'</td>';
					echo '<td align="center">'.$cantidad.'</td>';
					echo '<td align="right">'.number_format($row['costo']*$cantidad,2).'</td>';
					echo '<td align="center">'.htmlentities($array_anios[$row['anio']]).'</td>';
					echo '<td align="center">'.$faltantes.'</td>';
					echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
					echo '</tr>';
					$t+=$cantidad;
					$t2+=round($row['costo']*$cantidad,2);
					$t3+=$faltantes;
				}
			}
			echo '	
				<tr>
				<td colspan="8" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t,0).'</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t2,2).'</td>
				<td bgcolor="#E9F2F8">&nbsp;</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t3,0).'</td>
				<td bgcolor="#E9F2F8">&nbsp;</td>
				</tr>
			</table>';
			
		} else {
			echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
		}
		echo '|';
		$anios = explode(",",$_POST['anio']);
		$_POST['anio'] = $anios[0];
		if($_POST['anio'] >= 4){
			echo '<table border="1"><tr><th>Tipo de Certificado</th><th>Almacen</th><th>Compras</th><th>Total</th>';
			if($_POST['cveusuario'] >= 1){
				echo '<th>Consumidos</th><th>Existencia</th>';
			}
			echo '</tr>';
			$res = mysql_query("SELECT a.engomado,COUNT(b.cve) as compras FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra AND b.tipo!=1 WHERE a.plaza='".$_POST['plazausuario']."' AND ((a.anio='".$_POST['anio']."' AND a.engomado NOT IN (3,19)) OR (a.anio>=4 AND a.engomado IN (3,19))) AND a.estatus!='C' GROUP BY a.engomado") or die(mysql_error());
			$array_compras = array();
			while($row = mysql_fetch_array($res)){
				$array_compras[$row[0]] = $row[1];
			}
			foreach($array_engomado as $k=>$v){
				//if($k==19 || $k==3){
				if($k<0){
					if($_POST['anio']==4){
						$res = mysql_query("SELECT existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$_POST['plazausuario']."' AND engomado = '$k' ORDER BY cve DESC LIMIT 1");
						$row = mysql_fetch_array($res);
						$almacen = $row[0];
					}
					else{
						$res = mysql_query("SELECT existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$_POST['plazausuario']."' AND engomado = '$k' ORDER BY cve DESC LIMIT 1");
						$row = mysql_fetch_array($res);
						$almacen = $row[0];
						$res = mysql_query("SELECT SUM(foliofin+1-folioini) FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='$k' AND anio>=4 AND fecha_compra>='2016-01-01' AND fecha_compra<'".$array_fechainianio[$_POST['anio']]."' AND estatus!='C'");
						$row = mysql_fetch_array($res);
						$almacen += $row[0];
						$res = mysql_query("SELECT COUNT(cve) FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='$k' AND fecha>='2016-01-01' AND anio>=4 AND fecha<'".$array_fechainianio[$_POST['anio']]."' AND estatus!='C'");
						$row = mysql_fetch_array($res);
						$almacen -= $row[0];
						$res = mysql_query("SELECT COUNT(cve) FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND engomado='$k' AND fecha>='2016-01-01' AND anio>=4 AND fecha<'".$array_fechainianio[$_POST['anio']]."' AND estatus!='C'");
						$row = mysql_fetch_array($res);
						$almacen -= $row[0];
					}
					if($_POST['cveusuario'] >= 1){
						$mes = substr($array_fechainianio[$_POST['anio']],5,2);
						if(intval($mes)<=6){
							$fini = substr($array_fechainianio[$_POST['anio']],0,4).'-01-01';
							$ffin = substr($array_fechainianio[$_POST['anio']],0,4).'-06-30';
						}
						else{
							$fini = substr($array_fechainianio[$_POST['anio']],0,4).'-07-01';
							$ffin = substr($array_fechainianio[$_POST['anio']],0,4).'-12-31';
						}
						$res1=mysql_query("SELECT count(a.cve)
						FROM certificados a 
						INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
						INNER JOIN anios_certificados c ON c.cve = a.anio
						WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.anio='".$_POST['anio']."' AND a.engomado='$k' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) BETWEEN '".$fini."' AND '".$ffin."'");
						$row1 = mysql_fetch_array($res1);
						$res2=mysql_query("SELECT count(a.cve)
						FROM certificados_cancelados a 
						INNER JOIN anios_certificados c ON c.cve = a.anio
						WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.anio='".$_POST['anio']."' AND a.engomado='$k' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) BETWEEN '".$fini."' AND '".$ffin."'");
						$row2 = mysql_fetch_array($res2);
					}
				}
				else{
					$almacen = 0;
					if($_POST['cveusuario'] >= 1){
						if($k==3 || $k==19){
							$res1=mysql_query("SELECT count(a.cve)
							FROM certificados a 
							INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
							INNER JOIN anios_certificados c ON c.cve = a.anio
							WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='$k' AND a.estatus!='C'");
						}
						else{
							$res1=mysql_query("SELECT count(a.cve)
							FROM certificados a 
							INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
							INNER JOIN anios_certificados c ON c.cve = a.anio
							WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.anio='".$_POST['anio']."' AND a.engomado='$k' AND a.estatus!='C'");
						}
						$row1 = mysql_fetch_array($res1);
						if($k==3 || $k==19){
							$res2=mysql_query("SELECT count(a.cve)
							FROM certificados_cancelados a 
							INNER JOIN anios_certificados c ON c.cve = a.anio
							WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.engomado='$k' AND a.estatus!='C'");
						}
						else{
							$res2=mysql_query("SELECT count(a.cve)
							FROM certificados_cancelados a 
							INNER JOIN anios_certificados c ON c.cve = a.anio
							WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.anio='".$_POST['anio']."' AND a.engomado='$k' AND a.estatus!='C'");
						}
						$row2 = mysql_fetch_array($res2);
					}
				}
				
				echo '<tr><td>'.$v.'</td><td align="right">'.$almacen.'</td><td align="right">'.$array_compras[$k].'</td><td align="right">'.($almacen+$array_compras[$k]).'</td>';
				if($_POST['cveusuario'] >= 1){
					echo '<td align="right">'.($row1[0]+$row2[0]).'</td>';
					//echo '<td align="right">'.(($almacen+$array_compras[$k])-($row1['0']+$row2[0])).'</td>';
					echo '<td align="right"><a href="#" onClick="atcr(\'compra_certificados.php\',\'\',20,'.$k.')">'.(($almacen+$array_compras[$k])-($row1['0']+$row2[0])).'</a></td>';
				}
				echo '</tr>';
			}
			echo '</table>';
		}
		exit();	
}	

top($_SESSION);

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	
	
	//Listado
	echo '<div id="Resultados">';
	foreach($array_engomado as $k=>$v){

		echo '<h2>'.$v.'</h2>';
		$res = mysql_query("SELECT COUNT(cve),MIN(certificado/1),MAX(certificado/1) FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='$k' AND fecha=CURDATE()");
		$row = mysql_fetch_array($res);
		echo '<h3>Primer Folio: '.$row[1].'</h3>';
		echo '<h3>Ultimo Folio: '.$row[2].'</h3>';
		echo '<h3>Entregados: '.$row[0].'</h3>';
		$existencia=0;
		$array_compras=array();
		if($k==3 || $k==19){
			$res=mysql_query("SELECT * FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado = '$k' AND anio>='".($rowAnio['cve']-1)."' AND estatus!='C'");
			while($row = mysql_fetch_array($res)){
				$cantidad=$row['foliofin']+1-$row['folioini'];
				$res1=mysql_query("SELECT cve FROM certificados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."' AND estatus!='C'");
				$res2=mysql_query("SELECT cve FROM certificados_cancelados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."' AND estatus!='C'");
				if(mysql_num_rows($res1)==0 && mysql_num_rows($res2)==0) $puede_cancelar = 1;
				$entregados = mysql_num_rows($res1) + mysql_num_rows($res2);
				$faltantes = $cantidad-$entregados;
				if($faltantes < 0) $faltantes = 0;
				$existencia+=$faltantes;
				if($faltantes>0){
					$array_compras[]=array(
						'consecutivo' => $row['cve'],
						'folio' => $row['folio'],
						'fecha_compra' => $row['fecha_compra'],
						'folioini' => $row['folioini'],
						'foliofin' => $row['foliofin'],
						'cantidad' => $cantidad,
						'faltante' => $faltantes
					);
				}
			}
		}
		else{
			$res=mysql_query("SELECT * FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado = '$k' AND anio='".$rowAnio['cve']."' AND estatus!='C'");
			while($row = mysql_fetch_array($res)){
				$cantidad=$row['foliofin']+1-$row['folioini'];
				$res1=mysql_query("SELECT cve FROM certificados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."' AND estatus!='C'");
				$res2=mysql_query("SELECT cve FROM certificados_cancelados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."' AND estatus!='C'");
				if(mysql_num_rows($res1)==0 && mysql_num_rows($res2)==0) $puede_cancelar = 1;
				$entregados = mysql_num_rows($res1) + mysql_num_rows($res2);
				$faltantes = $cantidad-$entregados;
				if($faltantes < 0) $faltantes = 0;
				$existencia+=$faltantes;
				if($faltantes>0){
					$array_compras[]=array(
						'consecutivo' => $row['cve'],
						'folio' => $row['folio'],
						'fecha_compra' => $row['fecha_compra'],
						'folioini' => $row['folioini'],
						'foliofin' => $row['foliofin'],
						'cantidad' => $cantidad,
						'faltante' => $faltantes
					);
				}
			}
		}
		echo '<h3>Existencia en compras: '.$existencia.'</h3>';
		if($existencia > 0){
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Consecutivo</th><th>Folio Compra</th><th>Fecha Compra</th><th>Folio Inicial</th><th>Folio Final</th><th>Cantidad</th><th>Remanente</th>';
			echo '</tr>';
			foreach($array_compras as $v1){
				rowb();
				echo '<td align="center">'.$v1['consecutivo'].'</td>';
				echo '<td align="center">'.$v1['folio'].'</td>';
				echo '<td align="center">'.$v1['fecha_compra'].'</td>';
				echo '<td align="center">'.$v1['folioini'].'</td>';
				echo '<td align="center">'.$v1['foliofin'].'</td>';
				echo '<td align="center">'.$v1['cantidad'].'</td>';
				echo '<td align="center">'.$v1['faltante'].'</td>';
				echo '</tr>';
				if($v1['faltante']<$v1['cantidad']){
					$res=mysql_query("SELECT * FROM compra_certificados_detalle WHERE plaza='".$_POST['plazausuario']."' AND cvecompra='".$v1['consecutivo']."'");
					while($row=mysql_fetch_array($res)){
						$res2 = mysql_query("SELECT * FROM certificados  WHERE plaza='".$_POST['plazausuario']."' AND engomado = '".$k."' AND CAST(certificado AS UNSIGNED) = '".$row['folio']."' AND estatus != 'C'");
						if(!$row2=mysql_fetch_array($res2)){
							$res2 = mysql_query("SELECT * FROM certificados_cancelados  WHERE plaza='".$_POST['plazausuario']."' AND engomado = '".$k."' AND CAST(certificado AS UNSIGNED) = '".$row['folio']."' AND estatus != 'C'");
							if(!$row2=mysql_fetch_array($res2)){
								echo '<tr><td colspan="6">&nbsp;</td><td align="center">'.$row['folio'].'</td></tr>';
							}	
						}
					}
				}
			}
			echo '</table>';
		}
	}

	echo '</div>';






	
}
	
bottom();

?>

