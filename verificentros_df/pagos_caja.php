<?php 
include ("main.php"); 

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$res=mysql_query("SELECT local FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];


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
if($_POST['cmd']==1)
	$res = mysql_query("SELECT * FROM depositantes WHERE plaza='".$_POST['plazausuario']."' and solo_contado!=1 AND edo_cuenta=1 AND estatus=0 ORDER BY nombre");
else
	$res = mysql_query("SELECT * FROM depositantes WHERE plaza='".$_POST['plazausuario']."' and solo_contado!=1 AND edo_cuenta=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_depositantes[$row['cve']]=$row['nombre'];
}

$array_forma_pago = array(1=>"Efectivo",2=>"Deposito Bancario",3=>"Cheque",4=>"Transferencia",5=>'Tarjeta Bancaria');

$array_estatus = array('A'=>'Activo','C'=>'Cancelado');
/*** CONSULTA AJAX  **************************************************/

if($_POST['cmd']==300){
	header("Content-disposition: attachment; filename=impresioncajaverificentros.php");
	header("Content-type: MIME");
	readfile("impresioncajaverificentros.php");
	exit();
}

if($_POST['ajax']==1) {
		
		$res = mysql_query("SELECT * FROM usuarios WHERE cve='".$_POST['cveusuario']."'");
		$row = mysql_fetch_array($res);
		$permite_editar = $row['permite_editar'];
		
		//Listado de plazas
		$select= " SELECT a.*, IF(ISNULL(MAX(b.cve)), '', CONCAT(MIN(b.cve),' - ',MAX(b.cve))) as vales FROM pagos_caja a
		LEFT JOIN vales_pago_anticipado b ON a.cve = b.pago AND a.plaza = b.plaza
		WHERE a.plaza='".$_POST['plazausuario']."'";
		$select.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		if ($_POST['usuario']!="") { $select.=" AND a.usuario='".$_POST['usuario']."' "; }
		if ($_POST['depositante']!="") { $select.=" AND a.depositante='".$_POST['depositante']."' "; }
		if ($_POST['estatus']!="") { $select.=" AND a.estatus='".$_POST['estatus']."' "; }
		if ($_POST['tipo_pago']!="all") { $select.=" AND a.tipo_pago='".$_POST['tipo_pago']."' "; }
			
		$select.=" GROUP BY a.cve ORDER BY a.cve DESC";
		if($_POST['btn']==0) $select.=" LIMIT 1";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha Creacion</th><th>Fecha Aplicacion</th><th>Forma Pago</th>
			<th>Referencia</th><th>Tipo de Pago</th><th>Vales</th><th>Depositante</th><th>Monto</th><th>Observaciones</th><th>Usuario</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado </br>'.$array_usuario[$row['usucan']];
					$row['monto']=0;
				}
				else{
					echo '<a href="#" onClick="atcr(\'pagos_caja.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					$res1 = mysql_query("SELECT COUNT(a.cve) FROM vales_pago_anticipado a INNER JOIN cobro_engomado b ON a.plaza = b.plaza AND a.cve = b.vale_pago_anticipado AND b.tipo_vale=2 WHERE a.plaza = '".$row['plaza']."' AND a.pago = '".$row['cve']."'");
					$row1 = mysql_fetch_array($res1);
					if($row1[0] == 0 || $row['tipo_pago'] != 6){
						$permite_cancelar = 1;
					}
					else{
						$permite_cancelar = 0;
					}
					//if((nivelUsuario()>1 && ($row['fecha']==fechaLocal() || $_POST['cveusuario']==1 || $permite_editar==1)) && $permite_cancelar==1)
					if($_POST['cveusuario']==1 && $_POST['cvepago'] == 0 && nivelUsuario()>2)
						echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el pago\')) atcr(\'pagos_caja.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				}	
				echo '</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha_creacion'].' '.$row['hora']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha']).'</td>';
				if($permite_editar==1 && $row['estatus'] != 'C'){
					echo '<td align="center"><select id="forma_pago_'.$row['cve'].'">';
					foreach($array_forma_pago as $k=>$v){
						echo '<option value="'.$k.'"';
						if($k==$row['forma_pago']) echo ' selected';
						echo '>'.$v.'</option>';
					}
					echo '</select><br>
					<input type="button" value="Guardar" onClick="guardarCampo('.$row['cve'].', \''.$row['forma_pago'].'\',\'forma_pago\',\'Forma Pago\',\'array_forma_pago\')"></td>';
				}
				else{
					echo '<td align="center">'.htmlentities($array_forma_pago[$row['forma_pago']]).'</td>';
				}
				echo '<td align="center">'.htmlentities($row['referencia']).'</td>';
				if($permite_editar==2 && $row['estatus'] != 'C'){
					echo '<td align="center"><select id="tipo_pago_'.$row['cve'].'">';
					foreach($array_tipo_pago as $k=>$v){
						echo '<option value="'.$k.'"';
						if($k==$row['tipo_pago']) echo ' selected';
						echo '>'.$v.'</option>';
					}
					echo '</select><br>
					<input type="button" value="Guardar" onClick="guardarCampo('.$row['cve'].', \''.$row['tipo_pago'].'\',\'tipo_pago\',\'Tipo Pago\',\'array_tipo_pago\')"></td>';
				}
				else{
					echo '<td align="center">'.htmlentities($array_tipo_pago[$row['tipo_pago']]).'</td>';
				}
				if($row['tipo_pago']==6 && $row['vale_ini'] > 0 && $row['vale_fin'] > 0){
					echo '<td>'.$row['vale_ini'].' - '.$row['vale_fin'].'</td>';
				}
				elseif(($row['tipo_pago']==6 || $row['tipo_pago']==9) && $row['vales'] != ''){
					echo '<td>'.$row['vales'].'</td>';
				}
				else{
					echo '<td>&nbsp;</td>';
				}
				//if($permite_editar==2 && $row['estatus'] != 'C'){
				if($_POST['cveusuario']==1 && $row['estatus'] != 'C'){
					echo '<td align="center"><select id="depositante_'.$row['cve'].'">';
					foreach($array_depositantes as $k=>$v){
						echo '<option value="'.$k.'"';
						if($k==$row['depositante']) echo ' selected';
						echo '>'.$v.'</option>';
					}
					echo '</select><br>
					<input type="button" value="Guardar" onClick="guardarCampo('.$row['cve'].', \''.$row['depositante'].'\',\'depositante\',\'Depositante\',\'array_depositantes\')"></td>';
				}
				else{
					echo '<td align="center">'.htmlentities(utf8_encode($array_depositantes[$row['depositante']])).'</td>';
				}
				echo '<td align="center">'.number_format($row['monto'],2).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($row['obs'])).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
				$t+=$row['monto'];
			}
			echo '	
				<tr>
				<td colspan="9" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</td>
				<td colspan="2" bgcolor="#E9F2F8">&nbsp;</td>
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
		exit();	
}	

if($_POST['ajax']==11){
	$res = mysql_query("SELECT cve FROM pagos_caja WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND 
		((vale_ini<='".$_POST['vale_ini']."' AND vale_fin>='".$_POST['vale_ini']."') OR 
			(vale_ini<='".$_POST['vale_fin']."' AND vale_fin>='".$_POST['vale_fin']."'))");
	if($row = mysql_fetch_array($res)){
		echo '1';
	}
	exit();
}

if($_POST['ajax']==40){
	mysql_query("UPDATE pagos_caja SET ".$_POST['campo']."='".$_POST['valor']."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
	if($_POST['campo'] == 'depositante'){
		mysql_query("UPDATE vales_pago_anticipado SET depositante='".$_POST['valor']."' WHERE plaza='".$_POST['plazausuario']."' AND pago='".$_POST['folio']."'");
	}
	
	mysql_query("INSERT historial SET menu='77',cveaux='".$_POST['folio']."',fecha='".fechaLocal()." ".horaLocal()."',obs='".$_POST['plazausuario']."',
			dato='".$_POST['nombre']."',nuevo='".$_POST['valor']."',anterior='".$_POST['valor_anterior']."',arreglo='".$_POST['arreglo']."',usuario='".$_POST['cveusuario']."'");
	exit();
}


if($_POST['ajax']==50){
	echo '<table><tr><th>&nbsp;</th><th>Plaza</th><th>Folio</th><th>Fecha</th><th>Depositante</th><th>Monto</th><th>Observaciones</th></tr>';
	$res = mysql_query("SELECT a.plaza,a.cve,a.monto,a.obs,a.fecha,a.hora,b.nombre,c.numero FROM pagos_caja a 
		INNER JOIN depositantes b ON b.plaza = a.plaza AND b.cve = a.depositante 
		INNER JOIN plazas c ON c.cve = a.plaza 
		WHERE a.plaza!='".$_POST['plazausuario']."' AND a.tipo_pago=8 AND a.estatus!='C' AND a.cvepago=0 
		ORDER BY fecha,hora");
	while($row = mysql_fetch_array($res)){
		rowb();
		echo '<td align="center"><input type="checkbox" name="pagosplazas[]" class="otrospagos" value="'.$row['plaza'].'|'.$row['cve'].'" monto="'.$row['monto'].'" onClick="sumarotropagos()"></td>';
		echo '<td align="center">'.$row['numero'].'</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['fecha'].' '.$row['hora'].'</td>';
		echo '<td>'.utf8_encode($row['nombre']).'</td>';
		echo '<td align="right">'.number_format($row['monto'],2).'</td>';
		echo '<td>'.utf8_encode($row['obs']).'</td>';
		echo '</tr>';
	}
	echo '</table>';
	exit();
}


if($_POST['cmd']==101){
	require_once("numlet.php");
	$res=mysql_query("SELECT * FROM pagos_caja WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	$texto=chr(27)."@";
	$texto.='|';
	$resPlaza = mysql_query("SELECT nombre FROM plazas WHERE cve='".$row['plaza']."'");
	$rowPlaza = mysql_fetch_array($resPlaza);
	$resPlaza2 = mysql_query("SELECT rfc FROM datosempresas WHERE plaza='".$row['plaza']."'");
	$rowPlaza2 = mysql_fetch_array($resPlaza2);
	$texto.=chr(27).'!'.chr(30)." ".$array_plaza[$row['plaza']]."|".$rowPlaza['nombre'];
	$texto.='| RFC: '.$rowPlaza2['rfc'];
	$texto.='||';
	$texto.=chr(27).'!'.chr(8)." FOLIO: ".$row['cve'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." PAGO";
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." FECHA: ".$row['fecha']."   ".$row['hora'].'|';
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." FORMA PAGO: ".$array_forma_pago[$row['forma_pago']];
	if($row['forma_pago']>1){
		$texto.=chr(27).'!'.chr(8)." REFERENCIA: ".$row['referencia'];
		$texto.='|';
	}
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." TIPO PAGO: ".$array_tipo_pago[$row['tipo_pago']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." DEPOSITANTE: ".$array_depositantes[$row['depositante']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." MONTO: ".$row['monto'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." ".numlet($row['monto']);
	$texto.='|';
	
	$variables='plaza='.$array_plaza[$row['plaza']];
	$variables.='&nomplaza='.$rowPlaza['nombre'];
	$variables.='&rfc='.$rowPlaza2['rfc'];	
	$variables.='&folio='.$row['cve'];
	$variables.='&fecha='.$row['fecha'].' '.$row['hora'];
	$variables.='&formapago='.$array_forma_pago[$row['forma_pago']];
	$variables.='&cveformapago='.$row['forma_pago'];
	$variables.='&referencia='.$row['referencia'];
	$variables.='&tipopago='.$array_tipo_pago[$row['tipo_pago']];
	$variables.='&depositante='.$array_depositantes[$row['depositante']];
	$variables.='&monto='.$row['monto'];
	$variables.='&montoletra='.numlet($row['monto']);
	$vales = '';
	if($row['tipo_pago']==6 || $row['tipo_pago']==9){
		$res1=mysql_query("SELECT cve,tipo FROM vales_pago_anticipado WHERE plaza='".$row['plaza']."' AND pago='".$row['cve']."'");
		while($row1 = mysql_fetch_array($res1)){
			$vales .= ','.$row1['cve'].'|'.$row1['tipo'];
		}
		$vales = substr($vales, 1);
	}
	$variables.='&vales='.$vales;
	//$variables.='&montol='.numlet($row['monto']);
	$impresion='<iframe src="http://localhost/impresioncajaverificentros.php?'.$variables.'" width=200 height=200></iframe>';
	//$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$texto.'&logo='.str_replace(' ','',$array_plaza[$row['plaza']]).'&barcode=1'.sprintf("%011s",(intval($row['cve']))).'&copia=1" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",5000);</script>';
	exit();
}

top($_SESSION);


if($_POST['cmd']==3){
	$res = mysql_query("SELECT b.cve FROM vales_pago_anticipado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.vale_pago_anticipado = a.cve AND b.estatus!='C' AND b.tipo_venta=0 AND b.tipo_pago=6 WHERE a.plaza={$_POST['plazausuario']} AND a.pago={$_POST['reg']} AND a.tipo=0");
	if (mysql_num_rows($res)>0){
		echo '<script>alert("Ya fueron usados vales de pago anticipado");</script>';
	}
	else{
		$res = mysql_query("SELECT b.cve FROM vales_pago_anticipado a INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.codigo_cortesia = a.cve AND b.estatus!='C' AND b.tipo_venta=2 AND b.tipo_cortesia=2 WHERE a.plaza={$_POST['plazausuario']} AND a.pago={$_POST['reg']} AND tipo=1");
		if (mysql_num_rows($res)>0){
			echo '<script>alert("Ya fueron usados vales de pago anticipado");</script>';
		}
		else{
			mysql_query("UPDATE pagos_caja SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
			mysql_query("UPDATE vales_pago_anticipado SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND pago='".$_POST['reg']."'");
			mysql_query("UPDATE pagos_caja SET plazapago=0, cvepago=0 WHERE plazapago='".$_POST['plazausuario']."' AND cvepago='".$_POST['reg']."'");
		}
	}
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
		$res = mysql_query("SELECT * FROM pagos_caja WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' ORDER BY cve DESC LIMIT 1");
		$row = mysql_fetch_array($res);
		$fecha4min = date('Y-m-d H:i:s', strtotime ( "+ 4 minute" , strtotime($row['fecha'].' '.$row['hora']) ) );
		if($row['monto']!=$_POST['monto'] || $row['depositante']!=$_POST['depositante'] || $fecha4min<date('Y-m-d H:i:s')){
			if($_POST['tipo_pago']==6 || $_POST['tipo_pago'] == 8){
				$res1 = mysql_query("SELECT MAX(precio) FROM engomados WHERE cve=1");
				$row1=mysql_fetch_array($res1);
				$importe_verificacion = $row1[0];
				$verificaciones = round($_POST['monto']/$importe_verificacion,2);
			}
			else{
				$importe_verificacion=0;
				$verificaciones=0;
			}
			$monto_r=$_POST['monto'];
			if($_POST['tipo_pago']==9) $_POST['monto'] = 0;
			$insert = " INSERT pagos_caja 
							SET 
							plaza = '".$_POST['plazausuario']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',
							forma_pago='".$_POST['forma_pago']."',referencia='".$_POST['referencia']."',monto='".$_POST['monto']."',
							tipo_pago='".$_POST['tipo_pago']."',depositante='".$_POST['depositante']."',
							usuario='".$_POST['cveusuario']."',estatus='A',vale_ini='".$_POST['vale_ini']."',
							vale_fin='".$_POST['vale_fin']."',monto_r='".$monto_r."',
							obs='".$_POST['obs']."',importe_verificacion='$importe_verificacion',
							verificaciones='".$verificaciones."'";
			mysql_query($insert);
			$cvecobro = mysql_insert_id();
			if($_POST['tipo_pago']==6){
				$resD = mysql_query("SELECT pagos_cortesia FROM depositantes WHERE cve='{$_POST['depositante']}'");
				$rowD = mysql_fetch_array($resD);
				$pagos_para_cortesia = $rowD['pagos_cortesia'];
				for($i=0;$i<$verificaciones;$i++){
					mysql_query("INSERT vales_pago_anticipado SET 
						plaza = '".$_POST['plazausuario']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',
						hora='".horaLocal()."',depositante='".$_POST['depositante']."',monto='$importe_verificacion',
						estatus='A',usuario='".$_POST['cveusuario']."',pago=$cvecobro");
				}
				$cortesias = intval($verificaciones/$pagos_para_cortesia);
				for($i=0;$i<$cortesias;$i++){
					mysql_query("INSERT vales_pago_anticipado SET 
						plaza = '".$_POST['plazausuario']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',
						hora='".horaLocal()."',depositante='".$_POST['depositante']."',monto='0',
						estatus='A',usuario='".$_POST['cveusuario']."',pago=$cvecobro,tipo=1");
				}
			}
			if($_POST['tipo_pago']==9){
				foreach($_POST['pagosplazas'] as $pago){
					$datos = explode('|', $pago);
					$res = mysql_query("SELECT * FROM pagos_caja WHERE plaza='".$datos[0]."' AND cve='".$datos[1]."'");
					$row = mysql_fetch_array($res);
					for($i=0;$i<$row['verificaciones'];$i++){
						mysql_query("INSERT vales_pago_anticipado SET 
							plaza = '".$_POST['plazausuario']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',
							hora='".horaLocal()."',depositante='".$_POST['depositante']."',monto='$importe_verificacion',
							estatus='A',usuario='".$_POST['cveusuario']."',pago=$cvecobro");
					}
					mysql_query("UPDATE pagos_caja SET plazapago='".$_POST['plazausuario']."',cvepago='".$cvecobro."' WHERE plaza='".$datos[0]."' AND cve='".$datos[1]."'");
				}
			}
		}
				
	
	$_POST['cmd']=1;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		$res1 = mysql_query("SELECT MAX(precio) FROM engomados WHERE cve=1");
		$row1=mysql_fetch_array($res1);
		$importe_verificacion = $row1[0];
		$res = mysql_query("SELECT * FROM usuarios WHERE cve='".$_POST['cveusuario']."'");
		$row = mysql_fetch_array($res);
		$permite_editar = $row['permite_editar'];
		
		$res = mysql_query("SELECT * FROM pagos_caja WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		$nivel=nivelUsuario();
		echo '
			<tr>';
			if($nivel>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();validar('.$_POST['reg'].');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'pagos_caja.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Pagos</td></tr>';
		echo '</table>';
		echo '<table style="font-size:15px">';
		echo '<tr><th align="left">Fecha Aplicacion</td><td><input type="text" name="fecha" id="fecha" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>';
		/*if($permite_editar==1)*/ echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
		echo '</td></tr>';
		echo '<tr><th align="left">Forma de Pago</th><td><select name="forma_pago" id="forma_pago" style="font-size:20px" onChange="
		if((this.value/1)>1){
			$(\'#referencia\').parents(\'tr:first\').show();
		}
		else{
			$(\'#referencia\').parents(\'tr:first\').hide();
			$(\'#referencia\').val(\'\');
		}"><option value="0">Seleccione</option>';
		foreach($array_forma_pago as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row['forma_pago'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr style="display:none;"><th align="left">Referencia</th><td><input type="text" name="referencia" id="referencia" class="textField" size="20" style="font-size:12px" value="'.$row['referencia'].'"></td></tr>';
		echo '<tr><th align="left">Tipo de Pago</th><td><select name="tipo_pago" id="tipo_pago" style="font-size:20px" onChange="
		if(this.value==\'6\'){
			$(\'.cpagoanticipado\').show();
			$(\'.cpagootraplaza\').hide();
			$(\'#tdotrospagos\').html(\'\');
			$(\'#monto\').addClass(\'textField\').removeClass(\'readOnly\').removeAttr(\'readOnly\');
		}
		else if(this.value==\'9\'){
			document.forma.vale_ini.value=\'\';
			document.forma.vale_fin.value=\'\';
			$(\'.cpagoanticipado\').hide();
			$(\'.cpagootraplaza\').show();
			$(\'#monto\').addClass(\'readOnly\').removeClass(\'textField\').attr(\'readOnly\',\'readOnly\');
			traer_otros_pagos_plaza();
		}
		else{
			$(\'#monto\').addClass(\'textField\').removeClass(\'readOnly\').removeAttr(\'readOnly\');
			document.forma.vale_ini.value=\'\';
			document.forma.vale_fin.value=\'\';
			$(\'.cpagoanticipado\').hide();
			$(\'.cpagootraplaza\').hide();
			$(\'#tdotrospagos\').html(\'\');
		}
		"><option value="0">Seleccione</option>';
		foreach($array_tipo_pago as $k=>$v){
			if($k>1){
				echo '<option value="'.$k.'"';
				if($row['tipo_pago'] == $k) echo ' selected';
				echo '>'.$v.'</option>';
			}
		}
		echo '</select></td></tr>';
		echo '<tr class="cpagootraplaza" style="display:none;"><td colspan="2" id="tdotrospagos"></td></tr>';
		echo '<tr class="cpagoanticipador" style="display:none;"><th align="left">Vale Inicial</th><td><input type="text" name="vale_ini" id="vale_ini" class="textField" size="10" style="font-size:12px" value="'.$row['vale_ini'].'"></td></tr>';
		echo '<tr class="cpagoanticipador" style="display:none;"><th align="left">Vale Final</th><td><input type="text" name="vale_fin" id="vale_fin" class="textField" size="10" style="font-size:12px" value="'.$row['vale_fin'].'"></td></tr>';
		echo '<tr><th align="left">Depositante</th><td><select name="depositante" id="depositante" style="font-size:20px"><option value="0">Seleccione</option>';
		foreach($array_depositantes as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row['depositante']==$k) echo ' selected';
			echo '>'.$v.' (Saldo: '.number_format(saldo_depositante($k),2).')</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="readOnly" size="10" style="font-size:12px" value="'.$row['monto'].'"></td></tr>';
		
		echo '<tr><th align="left">Observaciones</th><td><textarea name="obs" id="obs" class="textField" rows="3" cols="30"></textarea></td></tr>';
		
		echo '</table>';
		
		echo '<script>
				function traer_otros_pagos_plaza(){
					$.ajax({
					  url: "pagos_caja.php",
					  type: "POST",
					  async: false,
					  data: {
						plazausuario: document.forma.plazausuario.value,
						ajax: 50,
					  },
						success: function(data) {
							$(\'#tdotrospagos\').html(data);
						}
					});
				}

				function sumarotropagos(){
					var total = 0;
					$(".otrospagos").each(function(){
						if(this.checked){
							total+=($(this).attr("monto")/1);
						}
					});
					document.forma.monto.value=total.toFixed(2);
				}

				function validar(reg){
					if(document.forma.depositante.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar depositante");
					}
					else if(document.forma.forma_pago.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar la forma de pago");
					}
					else if(document.forma.tipo_pago.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el tipo de pago");
					}
					else if((document.forma.forma_pago.value=="2" || document.forma.tipo_pago.value=="3" || document.forma.forma_pago.value == "4") && document.forma.referencia.value==""){
						$("#panel").hide();
						alert("Necesita ingresar la referencia");
					}
					else if(document.forma.tipo_pago.value=="6" && (document.forma.monto.value%'.$importe_verificacion.') > 0){
						$("#panel").hide();
						alert("El importe debe de contemplar verificaciones completas");
					}
					/*else if(document.forma.tipo_pago.value=="6" && (document.forma.vale_ini.value/1)<=0){
						$("#panel").hide();
						alert("Necesita ingresar el vale inicial");
					}
					else if(document.forma.tipo_pago.value=="6" && (document.forma.vale_fin.value/1)<=0){
						$("#panel").hide();
						alert("Necesita ingresar el vale final");
					}
					else if(!validarVales()){
						$("#panel").hide();
						alert("El rango de vales ya esta en otro pago");
					}*/
					else if((document.forma.monto.value/1)==0){
						$("#panel").hide();
						alert("El monto no puede ser cero");
					}
					else{
						atcr("pagos_caja.php","",2,reg);
					}
				}
				
				function validarVales(){
					if(document.forma.tipo_pago.value!="6")
						return true;
					regresar = true;
					return true;
					$.ajax({
					  url: "pagos_caja.php",
					  type: "POST",
					  async: false,
					  data: {
						plazausuario: document.forma.plazausuario.value,
						ajax: 11,
						vale_ini: document.forma.vale_ini.value,
						vale_fin: document.forma.vale_fin.value,
					  },
						success: function(data) {
							if(data == "1"){
								regresar = false;
							}
						}
					});
					return regresar;
				}
				
			</script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	if($PlazaLocal!=1)
		echo '<td><a href="#" onClick="atcr(\'pagos_caja.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
	echo '
		 </tr>';
	echo '</table>';
	echo '<table width="100%"><tr><td valign="top" width="50%">';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Depositante</td><td><select name="depositante" id="depositante"><option value="">Todos</option>';
	foreach($array_depositantes as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">Todos</option>';
	$res=mysql_query("SELECT b.cve,b.usuario FROM pagos_caja a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY b.usuario");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['usuario'].'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="">Todos</option>';
	foreach($array_estatus as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Tipo de Pago</td><td><select name="tipo_pago" id="tipo_pago"><option value="all" selected>Todos</option>';
	foreach($array_tipo_pago as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
	echo '</td><td width="50%" valign="top" id="capacorte"></td></tr></table>';
	echo '<br>';

	//echo '<span style="cursor:pointer" onClick="atcr(\'pagos_caja.php\',\'\',300,0)"><font size="20px" color="BLUE">Descargar Archivo de Impresion</font></span>';
	//echo '<br><font color="RED">Guardarlo en C:/xampp/htdocs</font>';
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
			objeto.open("POST","pagos_caja.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&btn="+btn+"&tipo_pago="+document.getElementById("tipo_pago").value+"&depositante="+document.getElementById("depositante").value+"&estatus="+document.getElementById("estatus").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					document.getElementById("Resultados").innerHTML = objeto.responseText;
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
			objeto.open("POST","pagos_caja.php",true);
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
		echo '<script>atcr(\'pagos_caja.php?nuevo=1\',\'_blank\',\'101\','.$cvecobro.');</script>';
	}
?>

