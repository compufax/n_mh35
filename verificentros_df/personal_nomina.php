<?php

include ("main.php"); 

/*** ARREGLOS ***********************************************************/

$rsPlaza=mysql_query("SELECT * FROM plazas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['nombre'];
}

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsPuestos=mysql_query("SELECT * FROM puestos ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_puestos[$Puestos['cve']]=$Puestos['nombre'];
}

if($_POST['cmd']==11){
	$select= " UPDATE personal SET dias_tra='".$_POST['dias_tra']."' WHERE estatus!='2' AND afiliado_imss='SI'";
	mysql_query($select);
	$res=mysql_query("SELECT * FROM personal WHERE estatus!=2 AND afiliado_imss='SI'");
	while($row = mysql_fetch_array($res)){
		$total=$row['dias_tra']*$row['salario_integrado'];
		$isr = montoisr($total,3);
		$subsidio = montosubsidio($total,3);
		mysql_query("UPDATE personal SET otras_per=".$subsidio.",isr=".$isr." WHERE cve='".$row['cve']."'");
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==5){
	$res=mysql_query("SELECT * FROM motivos_chequera WHERE plaza='1' AND nombre='NOMINA EMPLEADOS'");
	$row=mysql_fetch_array($res);
	$motivosueldo=$row['cve'];
	foreach($_POST['cvepersonal'] as $personal){
		$res=mysql_query("SELECT * FROM personal_nomina WHERE fecha='".$_POST['fec_nom']."' AND personal='$personal' AND tipo='1' AND salida='0'");
		while($row=mysql_fetch_array($res)){
			$monto=($row['sal_diario']*$row['dias_tra'])+$row['tiempo_ex']+$row['otras_per']-$row['isr']-$row['imp_imss']-$row['otras_ded']-$row['imp_infonavit']-$row['prestamo'];
			if($monto>0){
				$res1=mysql_query("select * from cuentas_chequera where cve='".$_POST['cuenta']."'");
				$row1=mysql_fetch_array($res1);
				$folio=$row1['folio_sig'];
				$nombre_cuenta=$row1['cuenta'];
				$sSQL="insert into chequera 
						(folio,emite,cuenta,fecha,hora,monto,beneficiario,motivo,concepto,estatus,usuario,tp,plaza,
						tipo_beneficiario,nombeneficiario) 
						values 
						('$folio','".$_POST['cveusuario']."','".$_POST['cuenta']."','".$_POST['fec_nom']."','".horaLocal()."','$monto','".$row['personal']."','$motivosueldo','Nomina ".$_POST['fec_nom']."','0','".$_POST['cveusuario']."','0','1',
						'3','')";
				mysql_query($sSQL) or die(mysql_error());
				$salida=mysql_insert_id();
			}
			else{
				$salida=-1;
			}
			mysql_query("UPDATE personal_nomina SET salida='".$salida."',tipo_salida=1 WHERE cve='".$row['cve']."'");
		}
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==4){
	$res=mysql_query("SELECT * FROM motivos WHERE plaza='1' AND nombre='NOMINA'");
	$row=mysql_fetch_array($res);
	$motivosueldo=$row['cve'];
	foreach($_POST['cvepersonal'] as $personal){
		$res=mysql_query("SELECT * FROM personal_nomina WHERE fecha='".$_POST['fec_nom']."' AND personal='$personal' AND tipo='1' AND salida='0'");
		while($row=mysql_fetch_array($res)){
			$monto=($row['sal_diario']*$row['dias_tra'])+$row['tiempo_ex']+$row['otras_per']-$row['isr']-$row['imp_imss']-$row['otras_ded']-$row['imp_infonavit']-$row['prestamo'];
			if($monto>0){
				$insert = " INSERT recibos_salidas 
							SET formapago='0',monto='".$monto."',tipo_beneficiario='3',
								motivo='".$motivosueldo."',beneficiario='".$row['personal']."',concepto='Nomina ".$_POST['fec_nom']."',plaza='".$row['plaza']."',
								usuario='".$_POST['cveusuario']."',fecha='".$_POST['fec_nom']."'";
				$ejecutar = mysql_query($insert);
				$salida=mysql_insert_id();
			}
			else{
				$salida=-1;
			}
			mysql_query("UPDATE personal_nomina SET salida='".$salida."' WHERE cve='".$row['cve']."'");
		}
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==3){
	/*if((intval(substr(fechaLocal(),0,4))%4)==0 && intval(substr(fechaLocal(),5,2))==2)
		$dias=29;
	else
		$dias=$array_diasmes[intval(substr(fechaLocal(),5,2))];
	$fechas=explode("-",fechaLocal());
	if($fechas[2]<=10) $fec_ini=$fechas[0]."-".$fechas[1]."-10";
	elseif($fechas[2]<=20) $fec_ini=$fechas[0]."-".$fechas[1]."-20";
	else $fec_ini=$fechas[0]."-".$fechas[1]."-".$dias;
	if($fec_ini==$_POST['fec_nom'])*/
		mysql_query("DELETE FROM personal_nomina WHERE fecha='".$_POST['fec_nom']."' AND tipo='1' AND salida='0'");
	$res=mysql_query("SELECT * FROM personal_nomina WHERE fecha='".$_POST['fec_nom']."' AND tipo='1' AND salida='0'");
	if(mysql_num_rows($res)==0){
		$select= " SELECT a.* FROM personal as a LEFT JOIN personal_nomina as b ON (b.personal=a.cve AND b.tipo='1' AND b.fecha='".$_POST['fec_nom']."') WHERE ISNULL(b.cve) AND a.estatus='1' AND a.afiliado_imss='SI' ORDER BY a.nombre";
		$rspersonal=mysql_query($select);
		if(mysql_num_rows($rspersonal)>0){
			while($Personal=mysql_fetch_array($rspersonal)){
				$Personal['dias_tra']=15;
				/*$saldoprestamo = $Personal['saldo_prestamo']*(-1);
				$res1=mysql_query("SELECT SUM(monto) FROM recibos_salidas WHERE motivo=56 AND tipo_beneficiario=3 AND beneficiario='".$Personal['cve']."' AND estatus!=2");
				$row1=mysql_fetch_array($res1);
				$saldoprestamo-=$row1[0];
				$res1=mysql_query("SELECT SUM(prestamo) FROM personal_nomina WHERE personal='".$Personal['cve']."'");
				$row1=mysql_fetch_array($res1);
				$saldoprestamo+=$row1[0];
				if($saldoprestamo<0){
					$saldoprestamo = abs($saldoprestamo);
				}
				else{
					$saldoprestamo = 0;
				}
				if($saldoprestamo<$Personal['cobro_prestamo']) $Personal['cobro_prestamo']=$saldoprestamo;*/
				$rsAbono=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM personal_nomina WHERE tipo='1'");
				$Abono=mysql_fetch_array($rsAbono);
				$folio=$Abono[0];
				mysql_query("INSERT personal_nomina SET plaza='".$Personal['plaza']."',folio='$folio',
						personal='".$Personal['cve']."',tipo='1',fecha='".$_POST['fec_nom']."',fecha_gen='".fechaLocal()."',
						sal_diario='".$Personal['salario_integrado']."',dias_tra='".$Personal['dias_tra']."',
						tiempo_ex='".$Personal['compensacion']."',otras_per='".$Personal['otras_per']."',isr='".$Personal['isr']."',
						imp_imss='".round(calcular_imss($Personal['sdi'])*$Personal['dias_tra'],2)."',otras_ded='".$Personal['otras_ded']."',imp_infonavit='".$Personal['monto_infonavit']."',
						prestamo='".$Personal['cobro_prestamo']."'") or die (mysql_error());
			}
		}
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==2){
	mysql_query("UPDATE personal SET sal_diario='".$_POST['sal_diario']."',dias_tra='".$_POST['dias_tra']."',compensacion='".$_POST['tiempo_ex']."',
						tiempo_ex='".$_POST['tiempo_ex']."',otras_per='".$_POST['otras_per']."',isr='".$_POST['isr']."',monto_infonavit='".$_POST['monto_infonavit']."',
						imp_imss='".$_POST['imp_imss']."',otras_ded='".$_POST['otras_ded']."',cobro_prestamo='".$_POST['cobro_prestamo']."' WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

if($_POST['ajax']==1) {
	/*$select= " SELECT * FROM personal WHERE estatus!='2' AND afiliado_imss='SI' ";
		if ($_POST['nombre']!="") { $select.=" AND nombre LIKE '%".$_POST['nombre']."%'"; }
		if ($_POST['plaza']!="all") { $select.=" AND plaza='".$_POST['plaza']."'"; }
		$select.=" ORDER BY nombre";*/
		if((intval(substr(fechaLocal(),0,4))%4)==0 && intval(substr(fechaLocal(),5,2))==2)
			$dias=29;
		else
			$dias=$array_diasmes[intval(substr(fechaLocal(),5,2))];
		$fechas=explode("-",fechaLocal());
		if($fechas[2]<=10) $fec_ini=$fechas[0]."-".$fechas[1]."-10";
		elseif($fechas[2]<=20) $fec_ini=$fechas[0]."-".$fechas[1]."-20";
		else $fec_ini=$fechas[0]."-".$fechas[1]."-".$dias;
	
		$res=mysql_query("SELECT * FROM personal_nomina WHERE fecha='".$_POST['fec_nom']."' AND fecha<'".$fec_ini."' AND tipo='1'");
		$nominagen=mysql_num_rows($res);
		if($nominagen==0){
			$select= " SELECT * FROM personal WHERE estatus='1' AND (afiliado_imss='SI' OR afiliado_imss='SN')";
			if ($_POST['nombre']!="") { $select.=" AND nombre LIKE '%".$_POST['nombre']."%'"; }
			if ($_POST['plaza']!="all") { $select.=" AND plaza='".$_POST['plaza']."'"; }
			$select.=" ORDER BY nombre";
		}
		else{
			$select= " SELECT a.plaza,b.sal_diario as salario_integrado,b.dias_tra as dias_tra,b.tiempo_ex as compensacion,b.otras_per as otras_per,
			b.isr as isr,b.imp_imss as imp_imss, b.imp_infonavit as monto_infonavit,b.otras_ded as otras_ded,a.cve,a.nombre,a.puesto,a.rfc,a.imss, b.salida,
			b.prestamo as cobro_prestamo
			FROM personal as a 
			INNER JOIN personal_nomina as b ON (b.personal=a.cve AND b.fecha='".$_POST['fec_nom']."' AND b.tipo='1')
			WHERE 1";
			if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
			if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; }
			$select.=" ORDER BY a.nombre";
		}
		$rspersonal=mysql_query($select) or die(mysql_error());
		$totalRegistros = mysql_num_rows($rspersonal);
		if(mysql_num_rows($rspersonal)>0) 
		{
			
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<thead><tr bgcolor="#E9F2F8"><td colspan="25">'.mysql_num_rows($rspersonal).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8">';
			echo '<th>&nbsp;</th>';
			if($nominagen!=0){
				echo '<th><input type="checkbox" name="selall" value="1" onClick="if(this.checked) $(\'.chkpersonal\').attr(\'checked\',\'checked\'); else $(\'.chkpersonal\').removeAttr(\'checked\');"></td>';
			}
			echo '<th>Plaza</th>';
			echo '
			<th><a href="#" onclick="SortTable(2,\'S\',\'tabla1\');">Nombre</a></th><th>Puesto</th><th>R.F.C.</th><th>N.S.S.</th>
					<th>Salario Diario</th><th>Dias Trabajados</th><th>Importe</th><th>Gratificacion</th><th>Total Percepciones</th><th>Subsidio al Empleado</th>
					<th>ISR</th><th>IMSS</th><th>Infonavit</th><th>Prestamo</th><th>Otras Deducciones</th><th>Total Deducciones</th><th>Total a Pagar</th>';
			echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$timp=$ttiempo=$totrasp=$tper=$tded=$totrasd=$tisr=$tims=$tinfonavit=$tprestamo=0;
			$i=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
				rowb();
				echo '<td align=center width="5%"><a href="#" onClick="atcr(\'personal_nomina.php\',\'\',1,\''.$Personal['cve'].'\');"><img src="images/modificar.gif" border="0"></a></td>';
				if($nominagen!=0){
					echo '<td align="center"><input type="checkbox" name="cvepersonal[]" value="'.$Personal['cve'].'" class="chkpersonal"';
					if($Personal['salida']>0) echo ' disabled';
					echo '></td>';
				}
				else{
					/*$saldoprestamo = $Personal['saldo_prestamo']*(-1);
					$res1=mysql_query("SELECT SUM(monto) FROM recibos_salidas WHERE motivo=56 AND tipo_beneficiario=3 AND beneficiario='".$Personal['cve']."' AND estatus!=2");
					$row1=mysql_fetch_array($res1);
					$saldoprestamo-=$row1[0];
					$res1=mysql_query("SELECT SUM(prestamo) FROM personal_nomina WHERE personal='".$Personal['cve']."'");
					$row1=mysql_fetch_array($res1);
					$saldoprestamo+=$row1[0];
					if($saldoprestamo<0){
						$saldoprestamo = abs($saldoprestamo);
					}
					else{
						$saldoprestamo = 0;
					}
					if($saldoprestamo<$Personal['cobro_prestamo']) $Personal['cobro_prestamo']=$saldoprestamo;*/
					$Personal['imp_imss'] = round(calcular_imss($Personal['sdi'])*$Personal['dias_tra'],2);
					$Personal['dias_tra']=15;
				}
				echo '<td>'.htmlentities(utf8_encode($array_plaza[$Personal['plaza']])).'</td>';
				echo '<td>'.htmlentities(utf8_encode($Personal["nombre"])).'</td>';
				echo '<td>'.htmlentities(utf8_encode($array_puestos[$Personal['puesto']])).'</td>';
				echo '<td align="center">'.$Personal['rfc'].'</td>';
				echo '<td align="center">'.$Personal['imss'].'</td>';
				echo '<td align="right">'.number_format($Personal['salario_integrado'],2).'</td>';
				echo '<td align="center">'.$Personal['dias_tra'].'</td>';
				echo '<td align="right">'.number_format($Personal['salario_integrado']*$Personal['dias_tra'],2).'</td>';
				echo '<td align="right">'.number_format($Personal['compensacion'],2).'</td>';
				$total_percepciones=($Personal['salario_integrado']*$Personal['dias_tra'])+$Personal['compensacion'];
				echo '<td align="right">'.number_format($total_percepciones,2).'</td>';
				echo '<td align="right">'.number_format($Personal['otras_per'],2).'</td>';
				echo '<td align="right">'.number_format($Personal['isr'],2).'</td>';
				echo '<td align="right">'.number_format($Personal['imp_imss'],2).'</td>';
				echo '<td align="right">'.number_format($Personal['monto_infonavit'],2).'</td>';
				echo '<td align="right">'.number_format($Personal['cobro_prestamo'],2).'</td>';
				echo '<td align="right">'.number_format($Personal['otras_ded'],2).'</td>';
				$total_deducciones=$Personal['isr']+$Personal['imp_imss']+$Personal['otras_ded']+$Personal['monto_infonavit']+$Personal['cobro_prestamo'];
				echo '<td align="right">'.number_format($total_deducciones,2).'</td>';
				echo '<td align="right">'.number_format($total_percepciones+$Personal['otras_per']-$total_deducciones,2).'</td>';
				echo '</tr>';
				$i++;
				$timp+=$Personal['salario_integrado']*$Personal['dias_tra'];
				$ttiempo+=$Personal['compensacion'];
				$totrasp+=$Personal['otras_per'];
				$tper+=$total_percepciones;
				$tisr+=$Personal['isr'];
				$timss+=$Personal['imp_imss'];
				$tinfonavit+=$Personal['monto_infonavit'];
				$tprestamo+=$Personal['cobro_prestamo'];
				$totrasd+=$Personal['otras_ded'];
				$tded+=$total_deducciones;
				$total+=($total_percepciones+$Personal['otras_per']-$total_deducciones);
			}
			$col=7;
			if($nominagen!=0){
				$col++;
			}
			echo '</tbody>
				<tr bgcolor="#E9F2F8"><td colspan="'.$col.'">'.$i.' Registro(s)</td><td>Total</td>
				<td align="right">'.number_format($timp,2).'</td>
				<td align="right">'.number_format($ttiempo,2).'</td>
				<td align="right">'.number_format($tper,2).'</td>
				<td align="right">'.number_format($totrasp,2).'</td>
				<td align="right">'.number_format($tisr,2).'</td>
				<td align="right">'.number_format($timss,2).'</td>
				<td align="right">'.number_format($tinfonavit,2).'</td>
				<td align="right">'.number_format($tprestamo,2).'</td>
				<td align="right">'.number_format($totrasd,2).'</td>
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

top($_SESSION);
if($_POST['cmd']==10){
	echo '<table><tr>';
	if(nivelUsuario()>1){
		echo '<td><a href="#" onClick="atcr(\'personal_nomina.php\',\'\',11,\'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	}
	echo '<td><a href="#" onclick="atcr(\'personal_nomina.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
	echo '</tr></table>';
	echo '<br>';
	echo '<table>';
	echo '<tr><td class="tableEnc">Cambiar dias laborados para todo el personal</td></tr>';
	echo '</table>';
	
	echo '<table><tr><th align="left">Dias Laborados</th><td><input type="text" class="textField" name="dias_tra"></td></tr></table>';
}

if($_POST['cmd']==1){
	echo '<table><tr>';
	if(nivelUsuario()>1){
		echo '<td><a href="#" onClick="atcr(\'personal_nomina.php\',\'\',2,\''.$_POST['reg'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	}
	echo '<td><a href="#" onclick="atcr(\'personal_nomina.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
	echo '</tr></table>';
	echo '<br>';
	$res=mysql_query("select * from personal where cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	$row['dias_tra']=15;
	echo '<table>';
	echo '<tr><td class="tableEnc">Nomina del Personal '.$row['nombre'].'</td></tr>';
	echo '</table>';
	echo '<br>';
	echo '<table>';
	/*$saldoprestamo = $row['saldo_prestamo']*(-1);
	$res1=mysql_query("SELECT SUM(monto) FROM recibos_salidas WHERE motivo=56 AND tipo_beneficiario=3 AND beneficiario='".$row['cve']."' AND estatus!=2");
	$row1=mysql_fetch_array($res1);
	$saldoprestamo-=$row1[0];
	$res1=mysql_query("SELECT SUM(prestamo) FROM personal_nomina WHERE personal='".$row['cve']."'");
	$row1=mysql_fetch_array($res1);
	$saldoprestamo+=$row1[0];
	if($saldoprestamo<0){
		$saldoprestamo = abs($saldoprestamo);
	}
	else{
		$saldoprestamo = 0;
	}
	if($saldoprestamo<$row['cobro_prestamo']) $row['cobro_prestamo']=$saldoprestamo;*/
	$total_percepciones=($row['salario_integrado']*$row['dias_tra'])+$row['compensacion']+$row['otras_per'];
	$total_deducciones=$row['isr']+$row['imp_imss']+$row['otras_ded'];
	echo '<tr><th align="left">Salario Diario</th><td><input type="text" class="textField" name="sal_diario" value="'.$row['salario_integrado'].'" onkeyup="suma();" readonly></td></tr>
	<tr><th align="left">Dias Laborados</th><td><input type="text" class="readOnly" name="dias_tra" value="'.$row['dias_tra'].'" onkeyup="suma();" readOnly></td></tr>
	<tr><th align="left">Importe</th><td><input type="text" name="imp_tot" class="readOnly" value="'.($row['salario_integrado']*$row['dias_tra']).'" readonly></td></tr>
	<tr><th align="left">Gratificacion</th><td><input type="text" class="textField" name="tiempo_ex" value="'.$row['compensacion'].'" onkeyup="suma();"></td></tr>
	<tr><th align="left">Total Percepciones</th><td><input type="text" class="readOnly" name="tot_per" value="'.$total_percepciones.'" readonly></td></tr>
	<tr><th align="left">Subsidio al Empleado</th><td><input type="text" class="readOnly" name="otras_per" value="'.$row['otras_per'].'" readOnly></td></tr>';
	echo '<tr><th align="left">Deducciones ISR</th><td><input type="text" class="readOnly" name="isr" value="'.$row['isr'].'" readOnly></td></tr>
	<tr><th align="left">IMSS</th><td><input type="text" name="imp_imss" class="readOnly" value="'.round(calcular_imss($row['sdi'])*$row['dias_tra'],2).'" onkeyup="suma();" readOnly></td></tr>
	<tr><th align="left">Infonavit</th><td><input type="text" name="monto_infonavit" class="textField" value="'.$row['monto_infonavit'].'" onkeyup="suma();"></td></tr>
	<tr><th align="left">Descuento por prestamo</th><td><input type="text" name="cobro_prestamo" class="textField" value="'.$row['cobro_prestamo'].'" onkeyup="suma();"></td></tr>';
	echo '
	<tr><th align="left">Otras Deducciones</th><td><input type="text" class="textField" name="otras_ded" value="'.$row['otras_ded'].'" onkeyup="suma();"></td></tr>
	<tr><th align="left">Total Deducciones</th><td><input type="text" class="readOnly" name="tot_ded" value="'.$total_deducciones.'" readonly></td></tr>
	<tr><th align="left">Neto a Pagar</th><td><input type="text" class="readOnly" name="tot_pagar" value="'.($total_percepciones-$total_deducciones).'" readonly></td></tr>';
	echo '</table>';
	
	echo '<script language="javascript">
			function suma(){
				if('.floatval($saldoprestamo).'<(document.forma.cobro_prestamo.value/1)) document.forma.cobro_prestamo.value='.floatval($saldoprestamo).';
				tot1=(document.forma.sal_diario.value/1)*(document.forma.dias_tra.value/1);
				document.forma.otras_per.value = montosubsidio(tot1);
				document.forma.isr.value = montoisr(tot1);
				tot2=(tot1/1)+(document.forma.tiempo_ex.value/1);
				tot_imss = calcular_imss('.$row['sdi'].')*(document.forma.dias_tra.value/1);
				document.forma.imp_imss.value = tot_imss.toFixed(2);
				tot3=(document.forma.isr.value/1)+(document.forma.imp_imss.value/1)+(document.forma.otras_ded.value/1)+(document.forma.monto_infonavit.value/1)+(document.forma.cobro_prestamo.value/1);
				tot4=tot2+(document.forma.otras_per.value/1)-tot3;
				document.forma.imp_tot.value=tot1;
				document.forma.tot_per.value=tot2;
				document.forma.tot_ded.value=tot3;
				document.forma.tot_pagar.value=tot4;
			}
			
			function montoisr(tot){
				var monto = 0;
				';
				$res1 = mysql_db_query("nomina","SELECT * FROM nomina WHERE tipo_nomina = 3 ORDER BY limite_inferior DESC");
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
				$res1 = mysql_db_query("nomina","SELECT * FROM nomina_subsidio WHERE tipo_nomina = 3 ORDER BY ingreso_min DESC");
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
		  </script>';
		  mysql_select_db($base);
}

//* Conductores activos/baja
if ($_POST['cmd']<1) {  //* Recibe como parametro el valor ALTA o BAJA, para desplegar la informacion correspondiente
/*** PAGINA PRINCIPAL **************************************************/
echo '<style>
    .divM {
        background:#DFE6EF;
        top:180px;
        left:150px;
        padding:5px;
        float:left;
        display:none;
        position:absolute;
        border-style: outset;
        width: 600px;
        heigth: 170px;
    }
</style>';
echo '<div class="divM" id="capacuenta"><table border=1 width="100%"><table><tr><th>Cuenta</th></tr><tr><td align="center"><select name="cuenta">';
$res1=mysql_query("SELECT * FROM cuentas_chequera WHERE 1  ORDER BY cuenta");
while ($row1=mysql_fetch_array($res1)) {
	echo '<option value="'.$row1['cve'].'"';
	echo '>'.$row1['cuenta'].'</option>';
}
echo '</select></td></tr><tr><td align="center"><input type="button" value="Aceptar" onClick="atcr(\'personal_nomina.php\',\'\',\'5\',\'\');">&nbsp;&nbsp;&nbsp;<input type="button" value="Cancelar" onClick="$(\'#capacuenta\').hide();"></td></tr></table></div>';
	if((intval(substr(fechaLocal(),0,4))%4)==0 && intval(substr(fechaLocal(),5,2))==2)
		$dias=29;
	else
		$dias=$array_diasmes[intval(substr(fechaLocal(),5,2))];
	$fechas=explode("-",fechaLocal());
	if($fechas[2]<=15) $fec_ini=$fechas[0]."-".$fechas[1]."-15";
	else $fec_ini=$fechas[0]."-".$fechas[1]."-".$dias;
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'imp_personal_nomina.php\',\'_blank\',\'\',\'SI\');"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir Listado&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'imp_personal_nomina.php\',\'_blank\',\'1\',\'\');"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir Recibos&nbsp;&nbsp;</td>';
		if(nivelUsuario()>1){
			//echo '<td><a href="#" onclick="atcr(\'personal_nomina.php\',\'\',\'3\',\'\');"><img src="images/guardar.gif" border="0"></a>&nbsp;&nbsp;Guardar Nomina&nbsp;&nbsp;</td>';
			//echo '<td><a href="#" onclick="if(!$(\'.chkpersonal\').is(\':checked\')) alert(\'Necesita seleccionar al menos un personal\'); else atcr(\'personal_nomina.php\',\'\',\'4\',\'\');"><img src="images/guardar.gif" border="0"></a>&nbsp;&nbsp;Generar Recibos de Salida&nbsp;&nbsp;</td>
			//<td><a href="#" onclick="atcr(\'imp_personal_nomina.php\',\'_blank\',\'2\',\'\');"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir Recibos de Salida&nbsp;&nbsp;</td>';
			//echo '<td><a href="#" onclick="if(!$(\'.chkpersonal\').is(\':checked\')) alert(\'Necesita seleccionar al menos un personal\'); else $(\'#capacuenta\').show();"><img src="images/guardar.gif" border="0"></a>&nbsp;&nbsp;Generar Cheques&nbsp;&nbsp;</td>
			//<td><a href="#" onclick="atcr(\'imp_personal_nomina.php\',\'_blank\',\'3\',\'\');"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir Cheques&nbsp;&nbsp;</td>';
			//echo '<td><a href="#" onclick="atcr(\'personal_nomina.php\',\'\',\'10\',\'\');"><img src="images/modificar.gif" border="0"></a>&nbsp;&nbsp;Editar dias trabajados&nbsp;&nbsp;</td>';
		}
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" class="textField"><option value="all">---Todas---</option>';
		foreach($array_plaza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td><td></td><td>&nbsp;</td></tr>';
		echo '<tr><td>Fecha Nomina</td><td><select name="fec_nom" id="fec_nom"><option value="'.$fec_ini.'">'.$fec_ini.'</option>';
		/*$res=mysql_query("SELECT fecha FROM personal_nomina WHERE tipo='1' GROUP BY fecha ORDER BY fecha DESC");
		while($row=mysql_fetch_array($res)){
			echo '<option value="'.$row[0].'">'.$row[0].'</option>';
		}*/
		//$res=mysql_query("SELECT fecha FROM personal_nomina WHERE tipo='1' GROUP BY fecha ORDER BY fecha");
		//if($row=mysql_fetch_array($res)){
			$row['fecha'] = '2014-09-15';
			//echo '<option value="'.$row[0].'">'.$row[0].'</option>';
			$fecha=$fec_ini;
			$fechas=explode("-",$fecha);
			if((intval($fechas[0])%4)==0 && intval($fechas[1])==2)
				$dias=29;
			else
				$dias=$array_diasmes[intval($fechas[1])];
			if($fechas[2]==15) $fecha=date( "Y-m-d" , strtotime ( "-15 day" , strtotime($fecha) ) );
			else $fecha=date( "Y-m-d" , strtotime ( "-".($dias-15)." day" , strtotime($fecha) ) );
			$res=mysql_query("SELECT fecha FROM personal_nomina WHERE tipo='1' AND fecha='$fecha'");
			if(mysql_num_rows($res)>0)
				echo '<option value="'.$fecha.'">'.$fecha.'(Generada)</option>';
			else
				echo '<option value="'.$fecha.'">'.$fecha.'(Sin generar)</option>';
			while($fecha>$row['fecha']){
				$fechas=explode("-",$fecha);
				if((intval($fechas[0])%4)==0 && intval($fechas[1])==2)
					$dias=29;
				else
					$dias=$array_diasmes[intval($fechas[1])];
				if($fechas[2]==15) $fecha=date( "Y-m-d" , strtotime ( "-15 day" , strtotime($fecha) ) );
				else $fecha=date( "Y-m-d" , strtotime ( "-".($dias-15)." day" , strtotime($fecha) ) );
				$res=mysql_query("SELECT fecha FROM personal_nomina WHERE tipo='1' AND fecha='$fecha'");
				if(mysql_num_rows($res)>0)
					echo '<option value="'.$fecha.'">'.$fecha.'(Generada)</option>';
				else
					echo '<option value="'.$fecha.'">'.$fecha.'(Sin generar)</option>';
			}
		//}
		//echo '<option value="2009-05-31">2009-05-31</option>';
		echo '</select></td></tr>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" class="textField"></td></tr>'; 
		echo '</table>';
		echo '<br>';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';



/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros(ordenamiento,orden)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","personal_nomina.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fec_nom="+document.getElementById("fec_nom").value+"&nombre="+document.getElementById("nombre").value+"&plaza="+document.getElementById("plaza").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}';	
	/*if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(1,1); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}*/
	echo '
	
	</Script>
';
	}
	
bottom();
?>
