<?php
include ("main.php"); 


$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$array_engomado = array();
$array_engomadoprecio = array();
$optionsengomado='';
$res = mysql_query("SELECT * FROM engomados WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio'];
	$optionsengomado.='<option value="'.$row['cve'].'" precio="'.$row['precio'].'">'.$row['nombre'].'</option>';
}

$array_empresa = array();
$optionsempresa='';
$res = mysql_query("SELECT * FROM empresas WHERE plaza = '".$_POST['plazausuario']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_empresa[$row['cve']]=$row['nombre'];
	$optionsempresa.='<option value="'.$row['cve'].'">'.$row['nombre'].'</option>';
}

if($_POST['cmd']==101){
	$res = mysql_query("SELECT * FROM cortes WHERE plaza = '".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	echo '<table>';
		echo '<tr><td class="tableEnc">Corte No. '.$_POST['reg'].'</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">Fecha</th><td>'.$row['fecha'].' '.$row['hora'].'</td></tr>';
		echo '<tr><td>Fecha Corte</td><td>'.$row['fecha_corte'].'</td></tr>';
		echo '<tr><td colspan="2"><h3>Ventas Hologramas</h2>
		<table id="venta_hologramas"><tr><th>Tipo</th><th>Cantidad</th><th>Importe</th></tr>';
		$res1=mysql_query("SELECT * FROM cortes_detalles WHERE plaza = '".$_POST['plazausuario']."' AND corte='".$_POST['reg']."' AND tipo = 1 AND engomado > 0");
		while($row1=mysql_fetch_array($res1)){
			echo '<tr>';
			echo '<td>'.$array_engomado[$row1['engomado']].'</td><td align="right">'.$row1['cantidad'].'</td><td align="right">'.number_format($row1['monto'],2).'</td></tr>';
		}
		$res1=mysql_query("SELECT * FROM cortes_detalles WHERE plaza = '".$_POST['plazausuario']."' AND corte='".$_POST['reg']."' AND tipo = 1 AND engomado < 0");
		$row1=mysql_fetch_array($res1);
		echo '
		<tr><th>Rechazados</th>
		<td align="right">'.$row1['cantidad'].'</td><td>&nbsp;</td></tr>
		<tr><th align="left">Total</th><td>&nbsp;</td>
		<td align="right">'.number_format($row['total_holograma'],2).'</td></tr>';
		echo '</table></td></tr>';
		
		echo '<tr><td colspan="2"><h3>Creditos</h2>
		<table id="credito"><tr><th>Tipo</th><th>Cantidad</th><th>Importe</th></tr>';
		$res1=mysql_query("SELECT * FROM cortes_detalles WHERE plaza = '".$_POST['plazausuario']."' AND corte='".$_POST['reg']."' AND tipo = 2 AND engomado > 0");
		while($row1=mysql_fetch_array($res1)){
			echo '<tr>';
			echo '<td>'.$array_engomado[$row1['engomado']].'</td><td align="right">'.$row1['cantidad'].'</td><td align="right">'.number_format($row1['monto'],2).'</td></tr>';
		}
		$res1=mysql_query("SELECT * FROM cortes_detalles WHERE plaza = '".$_POST['plazausuario']."' AND corte='".$_POST['reg']."' AND tipo = 2 AND engomado < 0");
		$row1=mysql_fetch_array($res1);
		echo '
		<tr><th>Rechazados</th>
		<td align="right">'.$row1['cantidad'].'</td><td>&nbsp;</td></tr>
		<tr><th align="left">Total</th><td>&nbsp;</td>
		<td align="right">'.number_format($row['total_credito'],2).'</td></tr>';
		echo '</table></td></tr>';
		
		echo '<tr><td colspan="2"><h3>Hologramas Pagados Anticipados</h2>
		<table id="hologramas_anticipo"><tr><th>Empresa</th><th>Tipo</th><th>Cantidad</th><th>Importe</th></tr>';
		$res1=mysql_query("SELECT * FROM cortes_detalles WHERE plaza = '".$_POST['plazausuario']."' AND corte='".$_POST['reg']."' AND tipo = 3 ");
		while($row1=mysql_fetch_array($res1)){
			echo '<tr>';
			echo '<td>'.$array_empresa[$row1['empresa']].'</td><td>'.$array_engomado[$row1['engomado']].'</td>
			<td align="right">'.$row1['cantidad'].'</td><td align="right">'.number_format($row1['monto'],2).'</td></tr>';
		}
		echo '<tr><th align="left">Total</th><td>&nbsp;</td><td>&nbsp;</td>
		<td align="center">'.number_format($row['total_holograma_anticipo'],2).'</td></tr>';
		echo '</table></td></tr>';
		
		echo '<tr><td colspan="2"><h3>Pagos Anticipados</h2>
		<table id="pagos_anticipados"><tr><th>Empresa</th><th>Importe</th><th>Concepto</th></tr>';
		$res1=mysql_query("SELECT * FROM cortes_detalles WHERE plaza = '".$_POST['plazausuario']."' AND corte='".$_POST['reg']."' AND tipo = 4");
		while($row1=mysql_fetch_array($res1)){
			echo '<tr>';
			echo '<td>'.$array_empresa[$row1['empresa']].'</td>
			<td align="right">'.number_format($row1['monto'],2).'</td><td>'.$row1['concepto'].'</td></tr>';
		}
		echo '<tr><th align="left">Total</th>
		<td align="center">'.number_format($row['total_anticipo'],2).'</td><td>&nbsp;</td></tr>';
		echo '</table></td></tr>';
		echo '</table>';
		exit();
}

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM cortes WHERE plaza='".$_POST['plazausuario']."' AND ".$_POST['tipo_fecha']." BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		if ($_POST['usuario']!="") { $select.=" AND usuario='".$_POST['usuario']."' "; }
		$select.=" ORDER BY cve DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Fecha Corte</th>
			<th>Total Holograma</th><th>Total Credito</th><th>Total Holograma Pagado Anticipado</th><th>Total Pagos Anticipados</th>
			<th>Usuario</th>';
			echo '</tr>';
			$t=array(0,0,0,0);
			while($row=mysql_fetch_array($res)) {
				rowb();
				$factor=1;
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado';
					$factor=0;
				}
				else{
					echo '<a href="#" onClick="atcr(\'cortes.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					if(nivelUsuario()>1)
						echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el corte\')) atcr(\'cortes.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				}	
				echo '</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha_corte']).'</td>';
				echo '<td align="right">'.number_format($row['total_holograma']*$factor,2).'</td>';
				echo '<td align="right">'.number_format($row['total_credito']*$factor,2).'</td>';
				echo '<td align="right">'.number_format($row['total_holograma_anticipo']*$factor,2).'</td>';
				echo '<td align="right">'.number_format($row['total_anticipo']*$factor,2).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
				$t[0]+=$row['total_holograma']*$factor;
				$t[1]+=$row['total_credito']*$factor;
				$t[2]+=$row['total_holograma_anticipo']*$factor;
				$t[3]+=$row['total_anticipo']*$factor;
			}
			echo '	
				<tr>
				<td colspan="4" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t[0],2).'</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t[1],2).'</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t[2],2).'</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t[3],2).'</td>
				<td colspan="1" bgcolor="#E9F2F8">&nbsp;</td>
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

top($_SESSION);

if($_POST['cmd']==3){
	mysql_query("UPDATE cortes SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {

	$insert = " INSERT cortes 
					SET 
					plaza = '".$_POST['plazausuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',
					fecha_corte='".$_POST['fecha_corte']."',total_holograma='".$_POST['total_holograma']."',
					total_credito='".$_POST['total_credito']."',
					total_holograma_anticipo='".$_POST['total_holograma_anticipo']."',
					total_anticipo='".$_POST['total_anticipo']."',
					usuario='".$_POST['cveusuario']."',estatus='A'";
	mysql_query($insert);
	$corte_id = mysql_insert_id();
	if(count($_POST['tipo'])>0){
		foreach($_POST['tipo'] as $k=>$v){
			if($_POST['monto'][$k] > 0 || $_POST['cantidad'][$k] > 0){
				mysql_query("INSERT cortes_detalles SET plaza='".$_POST['plazausuario']."',corte='".$corte_id."',
				empresa='".$_POST['empresa'][$k]."',engomado='".$_POST['engomado'][$k]."',cantidad='".$_POST['cantidad'][$k]."',
				monto='".$_POST['monto'][$k]."',concepto='".$_POST['concepto'][$k]."',tipo='".$_POST['tipo'][$k]."'");
			}
		}
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
				
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();validar();"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'cortes.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Cortes</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">Fecha</th><td><input type="text" name="placa" id="placa" class="readOnly" style="font-size:20px" size="10" value="'.fechaLocal().'" readOnly></td></tr>';
		echo '<tr><td>Fecha Corte</td><td><input type="text" name="fecha_corte" id="fecha_corte" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_corte,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td colspan="2"><h3>Ventas Hologramas</h2>
		<table id="venta_hologramas"><tr><th>Tipo</th><th>Cantidad</th><th>Importe</th></tr><tbody class="ctbody"></tbody>
		<tr><th>Rechazados
		<input type="hidden" name="tipo[]" value="1">
		<input type="hidden" name="engomado[]" value="-1">
		<input type="hidden" name="empresa[]" value="0">
		<input type="hidden" name="monto[]" value="0">
		<input type="hidden" name="concepto[]" value=""></th>
		<td align="center"><input type="text" class="textField" name="cantidad[]" value="" size="10"></td><td>&nbsp;</td></tr>
		<tr><th align="left">Total</th><td>&nbsp;</td>
		<td align="center"><input type="text" class="readOnly" name="total_holograma" value="" size="10" readOnly></td></tr>';
		echo '</table><br>';
		echo '<input type="button" value="Agregar" onClick="agregar_holograma()"></td></tr>';
		
		echo '<tr><td colspan="2"><h3>Creditos</h2>
		<table id="credito"><tr><th>Tipo</th><th>Cantidad</th><th>Importe</th></tr><tbody class="ctbody"></tbody>
		<tr><th>Rechazados
		<input type="hidden" name="tipo[]" value="2">
		<input type="hidden" name="engomado[]" value="-1">
		<input type="hidden" name="empresa[]" value="0">
		<input type="hidden" name="monto[]" value="0">
		<input type="hidden" name="concepto[]" value=""></th>
		<td align="center"><input type="text" class="textField" name="cantidad[]" value="" size="10"></td><td>&nbsp;</td></tr>
		<tr><th align="left">Total</th><td>&nbsp;</td>
		<td align="center"><input type="text" class="readOnly" name="total_credito" value="" size="10" readOnly></td></tr>';
		echo '</table><br>';
		echo '<input type="button" value="Agregar" onClick="agregar_credito()"></td></tr>';
		
		echo '<tr><td colspan="2"><h3>Hologramas Pagados Anticipados</h2>
		<table id="hologramas_anticipo"><tr><th>Empresa</th><th>Tipo</th><th>Cantidad</th><th>Importe</th></tr><tbody class="ctbody"></tbody>
		<tr><th align="left">Total</th><td>&nbsp;</td><td>&nbsp;</td>
		<td align="center"><input type="text" class="readOnly" name="total_holograma_anticipo" value="" size="10" readOnly></td></tr>';
		echo '</table><br>';
		echo '<input type="button" value="Agregar" onClick="agregar_holograma_anticipo()"></td></tr>';
		
		echo '<tr><td colspan="2"><h3>Pagos Anticipados</h2>
		<table id="pagos_anticipados"><tr><th>Empresa</th><th>Importe</th><th>Concepto</th></tr><tbody class="ctbody"></tbody>
		<tr><th align="left">Total</th>
		<td align="center"><input type="text" class="readOnly" name="total_anticipo" value="" size="10" readOnly></td><td>&nbsp;</td></tr>';
		echo '</table><br>';
		echo '<input type="button" value="Agregar" onClick="agregar_anticipo()"></td></tr>';
		echo '</table>';
		
		echo '<script>
				function validar(){
					atcr("cortes.php","",2,0);
				}
				
				function agregar_holograma(){
					$("#venta_hologramas").find(".ctbody").append(\'<tr>\
					<td align="center"><input type="hidden" name="tipo[]" value="1">\
					<input type="hidden" name="empresa[]" value="0">\
					<input type="hidden" name="concepto[]" value="">\
					<select name="engomado[]" class="cengomado" onChange="activar_monto_holograma($(this))"><option value="0" precio="0">Seleccione</option>'.$optionsengomado.'</select></td>\
					<td align="center"><input type="text" class="readOnly ccantidad" name="cantidad[]" onKeyUp="calcular_monto_holograma($(this))" size="10" readOnly></td>\
					<td align="center"><input type="text" class="readOnly cmonto cmontoholograma" name="monto[]" size="10" readOnly></td>\
					</tr>\');
				}
				
				function activar_monto_holograma(campo){
					campocantidad = campo.parents("tr:first").find(".ccantidad");
					campomonto = campo.parents("tr:first").find(".cmonto");
					if(campo.val() == "0"){
						campocantidad.removeClass("textField").addClass("readOnly").attr("readOly","readOnly").val("");
						campomonto.val("");
					}
					else{
						campocantidad.removeClass("readOnly").addClass("textField").removeAttr("readOnly");
						total = campocantidad.val() * campo.find("option[value=\'"+campo.val()+"\']").attr("precio");
						campomonto.val(total.toFixed(2));
					}
					total_holograma();
				}
				
				function calcular_monto_holograma(campo){
					campoengomado = campo.parents("tr:first").find(".cengomado");
					campomonto = campo.parents("tr:first").find(".cmonto");
					total = campo.val() * campoengomado.find("option[value=\'"+campoengomado.val()+"\']").attr("precio");
					campomonto.val(total.toFixed(2));
					total_holograma();
				}
				
				function total_holograma(){
					var total = 0;
					$(".cmontoholograma").each(function(){
						total+=this.value/1;
					});
					document.forma.total_holograma.value = total.toFixed(2);
				}
				
				function agregar_credito(){
					$("#credito").find(".ctbody").append(\'<tr>\
					<td align="center"><input type="hidden" name="tipo[]" value="2">\
					<input type="hidden" name="empresa[]" value="0">\
					<input type="hidden" name="concepto[]" value="">\
					<select name="engomado[]" class="cengomado" onChange="activar_monto_credito($(this))"><option value="0" precio="0">Seleccione</option>'.$optionsengomado.'</select></td>\
					<td align="center"><input type="text" class="readOnly ccantidad" name="cantidad[]" onKeyUp="calcular_monto_credito($(this))" size="10" readOnly></td>\
					<td align="center"><input type="text" class="readOnly cmonto cmontocredito" name="monto[]" size="10" readOnly></td>\
					</tr>\');
				}
				
				function activar_monto_credito(campo){
					campocantidad = campo.parents("tr:first").find(".ccantidad");
					campomonto = campo.parents("tr:first").find(".cmonto");
					if(campo.val() == "0"){
						campocantidad.removeClass("textField").addClass("readOnly").attr("readOly","readOnly").val("");
						campomonto.val("");
					}
					else{
						campocantidad.removeClass("readOnly").addClass("textField").removeAttr("readOnly");
						total = campocantidad.val() * campo.find("option[value=\'"+campo.val()+"\']").attr("precio");
						campomonto.val(total.toFixed(2));
					}
					total_credito();
				}
				
				function calcular_monto_credito(campo){
					campoengomado = campo.parents("tr:first").find(".cengomado");
					campomonto = campo.parents("tr:first").find(".cmonto");
					total = campo.val() * campoengomado.find("option[value=\'"+campoengomado.val()+"\']").attr("precio");
					campomonto.val(total.toFixed(2));
					total_credito();
				}
				
				function total_credito(){
					var total = 0;
					$(".cmontocredito").each(function(){
						total+=this.value/1;
					});
					document.forma.total_credito.value = total.toFixed(2);
				}
				
				function agregar_holograma_anticipo(){
					$("#hologramas_anticipo").find(".ctbody").append(\'<tr>\
					<td align="center"><select name="empresa[]" class="cempresa" onChange="activar_monto_holograma_anticipo($(this))"><option value="0" precio="0">Seleccione</option>'.$optionsempresa.'</select></td>\
					<td align="center"><input type="hidden" name="tipo[]" value="3">\
					<input type="hidden" name="concepto[]" value="">\
					<select name="engomado[]" class="cengomado" onChange="activar_monto_holograma_anticipo($(this))"><option value="0" precio="0">Seleccione</option>'.$optionsengomado.'<option value="-1" precio="0">Rechazado</option></select></td>\
					<td align="center"><input type="text" class="readOnly ccantidad" name="cantidad[]" onKeyUp="calcular_monto_holograma_anticipo($(this))" size="10" readOnly></td>\
					<td align="center"><input type="text" class="readOnly cmonto cmontohologramaanticipo" name="monto[]" size="10" readOnly></td>\
					</tr>\');
				}
				
				function activar_monto_holograma_anticipo(campor){
					campocantidad = campor.parents("tr:first").find(".ccantidad");
					campomonto = campor.parents("tr:first").find(".cmonto");
					campo = campor.parents("tr:first").find(".cengomado");
					campoempresa = campor.parents("tr:first").find(".cempresa");
					if(campo.val() == "0" || campoempresa.val() == "0"){
						campocantidad.removeClass("textField").addClass("readOnly").attr("readOly","readOnly").val("");
						campomonto.val("");
					}
					else{
						campocantidad.removeClass("readOnly").addClass("textField").removeAttr("readOnly");
						total = campocantidad.val() * campo.find("option[value=\'"+campo.val()+"\']").attr("precio");
						campomonto.val(total.toFixed(2));
					}
					total_holograma_anticipo();
				}
				
				function calcular_monto_holograma_anticipo(campo){
					campoempresa = campo.parents("tr:first").find(".cempresa");
					campoengomado = campo.parents("tr:first").find(".cengomado");
					campomonto = campo.parents("tr:first").find(".cmonto");
					if(campoempresa.val()=="0")
						total = 0;
					else
						total = campo.val() * campoengomado.find("option[value=\'"+campoengomado.val()+"\']").attr("precio");
					campomonto.val(total.toFixed(2));
					total_holograma_anticipo();
				}
				
				function total_holograma_anticipo(){
					var total = 0;
					$(".cmontohologramaanticipo").each(function(){
						total+=this.value/1;
					});
					document.forma.total_holograma_anticipo.value = total.toFixed(2);
				}
				
				function agregar_anticipo(){
					$("#pagos_anticipados").find(".ctbody").append(\'<tr>\
					<td align="center"><input type="hidden" name="tipo[]" value="4">\
					<input type="hidden" name="cantidad[]" value="0">\
					<input type="hidden" name="engomado[]" value="0">\
					<select name="empresa[]" class="cempresa" onChange="activar_monto_pagos_anticipados($(this))"><option value="0" precio="0">Seleccione</option>'.$optionsempresa.'</select></td>\
					<td align="center"><input type="text" class="readOnly cmonto cmontoanticipo" name="monto[]" onKeyUp="total_pagos_anticipados()" size="10" readOnly></td>\
					<td align="center"><textarea name="concepto[]" rows="3" cols="30" class="textField"></textarea></td>\
					</tr>\');
				}
				
				function activar_monto_pagos_anticipados(campo){
					campomonto = campo.parents("tr:first").find(".cmonto");
					if(campo.val() == "0"){
						campomonto.removeClass("textField").addClass("readOnly").attr("readOly","readOnly").val("");
					}
					else{
						campomonto.removeClass("readOnly").addClass("textField").removeAttr("readOnly");
					}
					total_pagos_anticipados();
				}
				
				function total_pagos_anticipados(){
					var total = 0;
					$(".cmontoanticipo").each(function(){
						total+=this.value/1;
					});
					document.forma.total_anticipo.value = total.toFixed(2);
				}
			</script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'cortes.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Tipo Fecha</td><td><select name="tipo_fecha" id="tipo_fecha">
	<option value="fecha">Creacion</option><option value="fecha_corte">Corte</option>';
	echo '</select></td></tr>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">Todos</option>';
	$res=mysql_query("SELECT b.cve,b.usuario FROM cortes a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY b.usuario");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['usuario'].'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","cortes.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&tipo_fecha="+document.getElementById("tipo_fecha").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
	}
	buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
		
	
	
	</Script>
	';

	
}
	
bottom();	
