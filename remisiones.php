<?php
include("main.php");
include("imp_remision.php");
//ARREGLOS

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
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

if($_POST['cmd']==101){
	generaRemisionPdf($_POST['plazausuario'],$_POST['reg'],1);
	exit();
}

if($_POST['cmd']==103){
	unlink($_POST['reg']);
	echo '<script>window.close();</script>';
	exit();
}

if($_POST['ajax']==1){
	$filtro="";
	$select= " SELECT a.* FROM remisiones as a WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
	//if($_POST['tipo']!="all") $select.=" AND a.tipo='".$_POST['tipo']."'";
	if($_POST['cliente']!="all") $select.=" AND a.cliente='".$_POST['cliente']."'";
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	if($_POST['estatus']==1) $select.=" AND a.estatus!='C'";
	elseif($_POST['estatus']==2) $select.=" AND a.estatus='C'";
	$select.=" ORDER BY a.cve DESC";
	$rsabonos=mysql_query($select) or die(mysql_error());
	if(mysql_num_rows($rsabonos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$c=13;
		//if($_POST['cveusuario']==1) $c++;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.mysql_num_rows($rsabonos).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		/*if($_POST['cveusuario']==1){
			echo '<th><input type="checkbox" name="selt" value="1" onClick="if(this.checked) $(\'.checks\').attr(\'checked\',\'checked\'); else $(\'.checks\').removeAttr(\'checked\');"></th>';
		}*/
		echo '<th>Folio</th><th>Fecha</th><th>Concepto</th>
		<th>Cliente</th><th>Tipo Pago</th><th>Subtotal</th>
		<th>Iva</th><th>Total</th><th>Retencion I.S.R.</th><th>Retencion I.V.A.</th><th>Total</th>
		<th>Usuario<br>';
		echo '<select name="usuario" onchange="document.forma.usu.value=this.value;buscarRegistros();"><option value="all">---Todos---</option>';
		$res1=mysql_query("SELECT a.usuario FROM remisiones as a WHERE plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY a.usuario");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['usuario'].'"';
			if($row1['usuario']==$_POST['usu']) echo ' selected';
			echo '>'.$array_usuario[$row1['usuario']].'</option>';
		}
		echo '</select></th></tr>'; 
		$sumacargo=array();
		$x=0;
		while ($Abono=mysql_fetch_array($rsabonos)){	
			rowb();
			$estatus='';
			if($Abono['estatus']=='C'){
				$estatus='(CANCELADO)';
				if($_POST['estatus']!='C'){
					$Abono['subtotal']=0;
					$Abono['iva']=0;
					$Abono['total']=0;
					$Abono['iva_retenido']=0;
				}
				echo '<td align="center">CANCELADO<br>';
				/*if(file_exists('cfdi/comprobantes/facturac_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf')){
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'cfdi/comprobantes/remision_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf\',\'_blank\',\'0\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
					if($_POST['cveusuario']==1){
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'remisiones.php\',\'_blank\',\'103\',\'cfdi/comprobantes/facturac_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf\');"><img src="images/basura.gif" border="0" title="Borrar PDF '.$Abono['folio'].'"></a>';
					}
				}
				else{*/
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'remisiones.php\',\'_blank\',\'101\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				//}
				echo '</td>';
				/*if($_POST['cveusuario']==1){
					echo '<td>&nbsp;</td>';
				}*/
				
			}
			else{
				echo '<td align="center" width="40" nowrap>';
				//<a href="#" onClick="atcr(\'cfdi/comprobantes/cfdi_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf\',\'_blank\',\'\',\'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				/*if(file_exists('cfdi/comprobantes/factura_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf')){
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'cfdi/comprobantes/factura_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf\',\'_blank\',\'0\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
					if($_POST['cveusuario']==1){
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'103\',\'cfdi/comprobantes/factura_'.$_POST['plazausuario'].'_'.$Abono['cve'].'.pdf\');"><img src="images/basura.gif" border="0" title="Borrar PDF '.$Abono['folio'].'"></a>';
					}
				}
				else{*/
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'remisiones.php\',\'_blank\',\'101\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				//}
				echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')){$(\'#panel\').show();atcr(\'remisiones.php\',\'\',\'3\',\''.$Abono['cve'].'\');}"><img src="images/validono.gif" border="0" title="Cancelar '.$Abono['folio'].'"></a>';
				echo '</td>';
				/*if($_POST['cveusuario']==1){
					echo '<td><input type="checkbox" class="checks" name="checksf[]" value="'.$Abono['cve'].'"></td>';
				}*/
			}
			echo '<td align="center">'.$Abono['cve'].'</td>';
			echo '<td align="center">'.$Abono['fecha'].' '.$Abono['hora'].'</td>';
			echo '<td align="center">'.$Abono['obs'].'</td>';
			echo '<td>'.htmlentities($array_clientes[$Abono['cliente']]).'</td>';
			echo '<td>'.htmlentities($array_tipo_pago[$Abono['tipo_pago']]).'</td>';
			echo '<td align="right">'.number_format($Abono['subtotal'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['isr_retenido'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva_retenido'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total'],2).'</td>';
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$sumacargo[0]+=$Abono['subtotal'];
			$sumacargo[1]+=$Abono['iva'];
			$sumacargo[2]+=$Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'];
			$sumacargo[3]+=$Abono['isr_retenido'];
			$sumacargo[4]+=$Abono['iva_retenido'];
			$sumacargo[5]+=$Abono['total'];
		}
		$c=5;
		//if($_POST['cveusuario']==1) $c++;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.$x.' Registro(s)</td>';
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		foreach($sumacargo as $k=>$v){
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
		}
		echo '<td bgcolor="#E9F2F8" colspan="1">&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
	}
	else {
		echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
	}
	exit();
}

if($_POST['cmd']==3){
	require_once("phpmailer/class.phpmailer.php");
	$res = mysql_query("SELECT * FROM remisiones WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	if($row['estatus']!='C'){
		$cvefact=$row['cve'];
		mysql_query("UPDATE remisiones SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
		generaRemisionPdf($_POST['plazausuario'],$cvefact);
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==2){
	require_once("phpmailer/class.phpmailer.php");
	if($rowempresa['timbra']==1)
		$res = mysql_query("SELECT folio_inicial FROM foliosiniciales WHERE plaza='".$_POST['plazausuario']."' AND tipo=0 AND tipodocumento=4");
	else
		$res = mysql_query("SELECT folio_inicial FROM foliosiniciales WHERE plaza='".$_POST['plazausuario']."' AND tipo=1 AND tipodocumento=4");
	$row = mysql_fetch_array($res);
	$res1 = mysql_query("SELECT cve FROM remisiones WHERE plaza='".$_POST['plazausuario']."'");
	if(mysql_num_rows($res1) > 0){
		mysql_query("INSERT remisiones SET plaza='".$_POST['plazausuario']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
		cliente='".$_POST['cliente']."',tipo_pago='".$_POST['tipo_pago']."',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
		carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
		tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."'") or die("INSERT remisiones SET plaza='".$_POST['plazausuario']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
		cliente='".$_POST['cliente']."',tipo_pago='".$_POST['tipo_pago']."',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',
		carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
		tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."'".mysql_error());
	}
	else{
		mysql_query("INSERT remisiones SET plaza='".$_POST['plazausuario']."',cve='".$row['folio_inicial']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
		cliente='".$_POST['cliente']."',tipo_pago='".$_POST['tipo_pago']."',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
		carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
		tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."'") or die(mysql_error());
	}
	$cvefact=mysql_insert_id();

	//Agregamos los conceptos
	$i=0;
	foreach($_POST['cant'] as $k=>$v){
		if($v>0){
			if(trim($_POST['unidad'][$k])=="") $_POST['unidad'][$k] = "NO APLICA";
			$importe_iva=round($_POST['importe'][$k]*$_POST['ivap'][$k]/100,2);
			mysql_query("INSERT remisionesmov SET plaza='".$_POST['plazausuario']."',cvefact='$cvefact',cantidad='".$v."',concepto='".$_POST['concepto'][$k]."',
			precio='".$_POST['precio'][$k]."',importe='".$_POST['importe'][$k]."',iva='".$_POST['ivap'][$k]."',importe_iva='$importe_iva',unidad='".$_POST['unidad'][$k]."'");
			$i++;
		}
	}
	mysql_query("UPDATE remisiones SET subtotal='".$_POST['subtotal']."',iva='".$_POST['iva']."',total='".$_POST['total']."',
	isr_retenido='".$_POST['isr_retenido']."',por_isr_retenido='".$_POST['por_isr_retenido']."',
	iva_retenido='".$_POST['iva_retenido']."',por_iva_retenido='".$_POST['por_iva_retenido']."' WHERE plaza='".$_POST['plazausuario']."' AND cve=".$cvefact);
	$_POST['cmd']=0;
}

top($_SESSION);
	$res = mysql_query("SELECT por_iva_retenido, mod_iva_retenido, por_isr_retenido, mod_isr_retenido FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
	$row = mysql_fetch_array($res);
	$por_iva_retenido = $row['por_iva_retenido'];
	$bloquearivaret = " readOnly";
	$claseivaret = "readOnly";
	if($row['mod_iva_retenido'] == 1){
		$bloquearivaret = "";
		$claseivaret = "textField";
	}
	$por_isr_retenido = $row['por_isr_retenido'];
	$bloquearisrret = " readOnly";
	$claseisrret = "readOnly";
	if($row['mod_isr_retenido'] == 1){
		$bloquearisrret = "";
		$claseisrret = "textField";
	}
	if($_POST['cmd']==1){
		echo '<table><tr>';
		if(nivelUsuario()>1){
			echo '<td><a href="#" onClick="$(\'#panel\').show();
			if(document.forma.cliente.value==\'0\'){
				alert(\'Necesita seleccionar el cliente\');
				$(\'#panel\').hide();
			}
			else if($.trim(document.forma.total.value)==\'\'){
				alert(\'El total debe de ser mayor a cero\');
				$(\'#panel\').hide();
			}
			else if(document.forma.carta_porte.checked == true && $.trim(document.forma.load.value)==\'\'){
				alert(\'Necesita ingresar el load\');
				$(\'#panel\').hide();
			}
			else if(document.forma.carta_porte.checked == true && $.trim(document.forma.nombre_cliente.value)==\'\'){
				alert(\'Necesita ingresar el nombre del cliente\');
				$(\'#panel\').hide();
			}
			else if(document.forma.carta_porte.checked == true && $.trim(document.forma.direccion_cliente.value)==\'\'){
				alert(\'Necesita ingresar la direccion del cliente\');
				$(\'#panel\').hide();
			}
			else if(document.forma.carta_porte.checked == true && $.trim(document.forma.tipopago_cliente.value)==\'\'){
				alert(\'Necesita seleccionar el tipo de pago de la carta porte\');
				$(\'#panel\').hide();
			}
			else{
				atcr(\'remisiones.php\',\'\',2,\'0\');
			}
			"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		}
		echo '<td><a href="#" onclick="$(\'#panel\').show();atcr(\'remisiones.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
		echo '</tr></table>';
		echo '<br>';
		echo '<table>';
		echo '<tr><td align="left">Fecha</td><td><input type="text" name="fecha" id="fecha"  size="15" class="readOnly" value="'.fechaLocal().'" readOnly>&nbsp;&nbsp;<!--<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>--></td></tr>';
		$fecha_rec=date( "Y-m-d" , strtotime ( "-1 day" , strtotime(fechaLocal()) ) );
		echo '<tr><td align="left">Cliente</td><td><select name="cliente" id="cliente"><option value="0">--- Seleccione ---</option>';
		foreach($array_clientes as $k=>$v){
			echo '<option value="'.$k.'" style="color: '.$array_colorcliente[$k].';"';
			if($array_colorcliente[$k] == "#FF0000") echo ' disabled';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Tipo de Pago</td><td><select name="tipo_pago" id="tipo_pago">';
		foreach($array_tipo_pago as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		/*echo '<tr><td align="left">Mes</td><td><select name="mes" id="mes"><option value="0">Seleccione</option>';
		$res = mysql_query("SELECT LEFT(fechaapl,7) FROM depositos WHERE estatus!='C' AND fechaapl>'0000-00-00' GROUP BY LEFT(fechaapl,7) ORDER BY LEFT(fechaapl,7) DESC");
		while($row=mysql_fetch_array($res)){
			$dat=explode("-",$row[0]);
			echo '<option value="'.$row[0].'">'.$array_meses[intval($dat[1])].' '.$dat[0].'</option>';
		}
		echo '</select></td></tr>';*/
		echo '<tr';
		if($rowempresa['carta_porte']!=1){
			echo ' style="display:none;"';
		}
		echo '><td>Carta Porte</th><td><input type="checkbox" id="carta_porte" name="carta_porte" value="1" onClick="
			if(this.checked){ 
				$(\'.rcarta_porte\').show(); 
			}
			else{ 
				$(\'.rcarta_porte\').hide();
			}"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Load</td><td><input type="text" class="textField" name="load" id="load" value="" size="30"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Nombre del Cliente</td><td><input type="text" class="textField" name="nombre_cliente" id="nombre_cliente" value="" size="50"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Direccion del Cliente</td><td><input type="text" class="textField" name="direccion_cliente" id="direccion_cliente" value="" size="100"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Tipo de Pago</td><td><input type="text" class="textField" name="tipopago_cliente" id="tipopago_cliente" value="" size="50"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Banco</td><td><input type="text" class="textField" name="banco_cliente" id="banco_cliente" value="" size="30"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Cuenta</td><td><input type="text" class="textField" name="cuenta_cliente" id="cuenta_cliente" value="" size="30"></td></tr>';
		echo '<tr><td>Observaciones</td><td><textarea class="textField" name="obs" id="obs" cols="30" rows="3"></textarea></td></tr>';
		echo '</table>';
		echo '<input type="hidden" name="clickguardar" id="clickguardar" value="no">';
		echo '<table id="tablaproductos"><tr>';
		echo '<th>Cantidad</th><th>Unidad</th>';
		echo '<th>Descripcion</th><th>Precio Unitario</th><th>Importe</th><th>IVA</th></tr>';
		$i=0;
		if($i==0){
			echo '<tr>';
			echo '<td align="center"><input type="text" class="textField" size="10" name="cant['.$i.']" id="cant'.$i.'" value=""  onKeyUp="sumarproductos()"></td>';
			echo '<td><input type="text" name="unidad['.$i.']" id="unidad'.$i.'" class="textField" size="20" value=""></td>';
			echo '<td><input type="text" name="concepto['.$i.']" id="concepto'.$i.'" class="textField" size="50" value=""></td>';
			echo '<td align="center"><input type="text" class="textField" size="10" name="precio['.$i.']" id="precio'.$i.'" value=""  onKeyUp="sumarproductos()"></td>';
			echo '<td align="center"><input type="text" class="readOnly" size="10" name="importe['.$i.']" id="importe'.$i.'" value="" readOnly></td>';
			echo '<td align="center"><input type="checkbox" name="ivap['.$i.']" id="ivap'.$i.'" value="16" onClick="sumarproductos()" checked></td>';
			echo '</tr>';
			$i++;
		}
		echo '<tr id="idsubtotal"><th align="right" colspan="4">Subtotal&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="subtotal" id="subtotal" value="" readOnly></td></tr>';
		echo '<tr id="idiva"><th align="right" colspan="4">Iva 16%&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva" id="iva" value="" readOnly></td></tr>';
		echo '<tr id="idtotal1"><th align="right" colspan="4">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total1" id="total1" value="" readOnly></td></tr>';
		echo '<tr id="idisr_ret"><th align="right" colspan="4"><input type="checkbox" name="banisr_retenido" id="banisr_retenido" value="1" onClick="sumarproductos()">Retencion I.S.R.&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="isr_retenido" id="isr_retenido" value="" readOnly></td><td><input type="text" class="'.$claseisrret.'" size="5" name="por_isr_retenido" id="por_isr_retenido" value="'.$por_isr_retenido.'" onKeyUp="sumarproductos()" '.$bloquearisrret.'>%</td></tr>';
		echo '<tr id="idiva_ret"><th align="right" colspan="4"><input type="checkbox" name="baniva_retenido" id="baniva_retenido" value="1" onClick="sumarproductos()">Retencion I.V.A.&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva_retenido" id="iva_retenido" value="" readOnly></td><td><input type="text" class="'.$claseivaret.'" size="5" name="por_iva_retenido" id="por_iva_retenido" value="'.$por_iva_retenido.'" onKeyUp="sumarproductos()" '.$bloquearivaret.'>%</td></tr>';
		echo '<tr id="idtotal"><th align="right" colspan="4">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total" id="total" value="" readOnly></td></tr>';
		echo '</table>';		
		echo '<input type="button" value="Agregar" onClick="agregarproducto()" class="textField">';
		echo '<input type="hidden" name="cantprod" value="'.$i.'">';
		echo '<script>
					
			function agregarproducto(){
				var checkeado=\'\';
				if($("#baniva_retenido").is(":checked")){
					checkeado=\'checked\';
				}
				tot=$("#total").val();
				$("#idtotal").remove();
				subtot=$("#subtotal").val();
				$("#idsubtotal").remove();
				iv=$("#iva").val();
				$("#idiva").remove();
				tot1=$("#total1").val();
				$("#idtotal1").remove();
				iva_ret=$("#iva_retenido").val();
				piva_ret=$("#por_iva_retenido").val();
				$("#idiva_ret").remove();
				isr_ret=$("#isr_retenido").val();
				pisr_ret=$("#por_isr_retenido").val();
				$("#idisr_ret").remove();
				num=document.forma.cantprod.value;
				$("#tablaproductos").append(\'<tr>\
				<td align="center"><input type="text" class="textField" size="10" name="cant[\'+num+\']" id="cant\'+num+\'" value=""  onKeyUp="sumarproductos()"></td>\</td>\
				<td><input type="text" name="unidad[\'+num+\']" id="unidad\'+num+\'" class="textField" size="20" value=""></td>\
				<td><input type="text" name="concepto[\'+num+\']" id="concepto\'+num+\'" class="textField" size="50" value=""></td>\
				<td align="center"><input type="text" class="textField" size="10" name="precio[\'+num+\']" id="precio\'+num+\'" value=""  onKeyUp="sumarproductos()"></td>\
				<td align="center"><input type="text" class="readOnly" size="10" name="importe[\'+num+\']" id="importe\'+num+\'" value="" readOnly></td>\
				<td align="center"><input type="checkbox" name="ivap[\'+num+\']" id="ivap\'+num+\'" value="16" onClick="sumarproductos()" checked></td>\
				</tr>\
				<tr id="idsubtotal"><th align="right" colspan="4">Subtotal&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="subtotal" id="subtotal" value="\'+subtot+\'" readOnly></td></tr>\
				<tr id="idiva"><th align="right" colspan="4">Iva 16%&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva" id="iva" value="\'+iv+\'" readOnly></td></tr>\
				<tr id="idtotal1"><th align="right" colspan="4">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total1" id="total1" value="\'+tot1+\'" readOnly></td></tr>\
				<tr id="idisr_ret"><th align="right" colspan="4"><input type="checkbox" name="banisr_retenido" id="banisr_retenido" value="1" onClick="sumarproductos()" \'+checkeado+\'>Retencion I.S.R.&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="isr_retenido" id="isr_retenido" value="\'+isr_ret+\'" readOnly></td><td><input type="text" class="'.$claseisrret.'" size="5" name="por_isr_retenido" id="por_isr_retenido" value="\'+pisr_ret+\'" onKeyUp="sumarproductos()" '.$bloquearisrret.'>%</td></tr>\
				<tr id="idiva_ret"><th align="right" colspan="4"><input type="checkbox" name="baniva_retenido" id="baniva_retenido" value="1" onClick="sumarproductos()" \'+checkeado+\'>Retencion I.V.A.&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva_retenido" id="iva_retenido" value="\'+iva_ret+\'" readOnly></td><td><input type="text" class="'.$claseivaret.'" size="5" name="por_iva_retenido" id="por_iva_retenido" value="\'+piva_ret+\'" onKeyUp="sumarproductos()" '.$bloquearivaret.'>%</td></tr>\
				<tr id="idtotal"><th align="right" colspan="4">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total" id="total" value="\'+tot+\'" readOnly></td></tr>\');
				num++;
				document.forma.cantprod.value=num;
			}
			
			function sumarproductos(){
				var sumar=0;
				var iv=0;
				var iv_ret=0;
				var is_ret=0;
				for(i=0;i<(document.forma.cantprod.value/1);i++){
					impo=(document.getElementById("cant"+i).value/1)*(document.getElementById("precio"+i).value/1);
					document.getElementById("importe"+i).value=impo.toFixed(2);
					sumar+=(document.getElementById("importe"+i).value/1);
					is_ret+=document.getElementById("importe"+i).value*document.forma.por_isr_retenido.value/100;
					if(document.getElementById("ivap"+i).checked){
						iv+=document.getElementById("importe"+i).value*0.16;
						iv_ret+=document.getElementById("importe"+i).value*document.forma.por_iva_retenido.value/100;
					}
				}
				document.forma.subtotal.value=sumar.toFixed(2);
				document.forma.iva.value=iv.toFixed(2);
				document.forma.total1.value=(document.forma.subtotal.value/1)+(document.forma.iva.value/1);
				if($("#banisr_retenido").is(":checked")){
					document.forma.isr_retenido.value=is_ret.toFixed(2);
				}
				else{
					document.forma.isr_retenido.value=0;
				}
				if($("#baniva_retenido").is(":checked")){
					document.forma.iva_retenido.value=iv_ret.toFixed(2);
				}
				else{
					document.forma.iva_retenido.value=0;
				}
				
				tot=(document.forma.subtotal.value/1)+(document.forma.iva.value/1)-(document.forma.isr_retenido.value/1)-(document.forma.iva_retenido.value/1);
				document.forma.total.value=tot.toFixed(2);
			}
			
			
		  </script>';
	}

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onClick="atcr(\'remisiones.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0">&nbsp;Nuevo</a></td><td>&nbsp;</td>';
		/*if($_POST['cveusuario']==1){
			echo '<td><a href="#" onClick="atcr(\'facturas.php\',\'\',\'6\',\'0\');"><img src="images/nuevo.gif" border="0">&nbsp;Reenviar Archivos</a></td><td>&nbsp;</td>';
		}*/
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.substr(fechaLocal(),0,8).'01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Cliente</td><td><select name="cliente" id="cliente"><option value="all">--- Todos ---</option>';
		foreach($array_clientes as $k=>$v){
			echo '<option class="cexternos" value="'.$k.'" style="color: '.$array_colorcliente[$k].';">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td align="left">Estatus</td><td><select name="estatus" id="estatus"><option value="0">Todos</option><option value="1">Activos</option>
		<option value="2">Cancelado</option></select></td></tr>';
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
	
	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","remisiones.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&estatus="+document.getElementById("estatus").value+"&cliente="+document.getElementById("cliente").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value);
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
	function validanumero(campo) {
		var ValidChars = "0123456789.";
		var cadena=campo.value;
		var cadenares="";
		var digito;
		for(i=0;i<cadena.length;i++) {
			digito=cadena.charAt(i);
			if (ValidChars.indexOf(digito) != -1)
				cadenares+=""+digito;
		}
		campo.value=cadenares;
	}

	</Script>
';

?>