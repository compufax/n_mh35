<?php
include("main.php");
$array_tipo_pago=array(0=>"01 EFECTIVO",2=>"03 TRANSFERENCIA ELECTRONICA DE FONDOS",3=>"01 DEPOSITO",4=>"99 NO ESPECIFICADO",5=>"02 CHEQUE NOMINATIVO",6=>"98 NO APLICA",7=>"04 CREDITO",9=>"28 TARJETA DE DEBITO");//,1=>"CHEQUE"
						 
$rsUsuario=mysql_query("SELECT * FROM clientes where plaza='".$_POST['plazausuario']."'");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_clientes[$Usuario['cve']]=$Usuario['nombre'];
}

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM clientes WHERE plaza='".$_POST['plazausuario']."' and credito='1'";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		$select.=" ORDER BY nombre";
		$res=mysql_query($select) or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);
//		echo''.$select.'';
		$nivelUsuario = nivelUsuario();
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Nombre</th><th width=20%">Saldo</th>';
			echo '</tr>';
			$t = 0;
			while($row=mysql_fetch_array($res)) {	
				rowb();
//				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef['nombre'].'"></a></td>';
				echo '<td>'.utf8_encode($row['nombre']).'</td>';
				$select1= " SELECT sum(total) as total_facturas FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' and cliente='".$row['cve']."' 
				and fechapago='0000-00-00' AND tipo_pago='7'";				
				$res1=mysql_query($select1);
				$row1=mysql_fetch_array($res1) or die(mysql_error());
				
				
				echo '<td align="right" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')">'.number_format($row1['total_facturas'],2).'</a></td>';
				echo '</tr>';
				$t += $row1['total_facturas'];
			}
			echo '	
				<tr>
				<td colspan="1" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</td>
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
		$select1= " SELECT * FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."' ";				
		$res=mysql_query($select1) or die(mysql_error());
		$row=mysql_fetch_array($res);
		
		
		mysql_query("UPDATE facturas SET fechapago_can='".$row['fechapago']."',monto_can='".$row['importepago']."',usu_can='".$_POST['cveusuario']."',
		fecha_can='".fechaLocal()."',hora_can='".horaLocal()."',fechapago='0000-00-00',importepago='0',formapago='0' 
		WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	
	$_POST['cmd']=0;
}

if($_POST['cmd']==12){
	foreach($_POST['checksp'] as $cvefact){
		mysql_query("UPDATE facturas SET fechapago='".$_POST['fechapago']."',referencia='".$_POST['referencia']."',
		formapago='".$_POST['formapago']."',importepago='".$_POST['importepago']."',usupago='".$_POST['cveusuario']."',
		fechahorapago=NOW() WHERE plaza='".$_POST['plazausuario']."' AND cve='".$cvefact."'");
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==1) {
echo '<div id="dialog" style="display:none">
		<table width="100%">
		<tr><th>Fecha Pago</th><td><input type="text" class="readOnly" id="fechapagodialog" size="12" value="'.date('Y-m-d').'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.getElementById(\'fechapagodialog\'),\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>
		<tr><th>Tipo de Pago</th><td><select id="tipopagodialog">';
		foreach($array_tipo_pago as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>
		<tr><th>Referencia</th><td><input type="text" class="textField" id="referenciadialog" size="10" value=""></td></tr>
		<tr><th>Importe</th><td><input type="text" class="textField" id="importedialog" size="10" value=""></td></tr>
		</table>
		</div>';
				echo '<input type="hidden" name="formapago" id="formapago" value="">';
		echo '<input type="hidden" name="referencia" id="referencia" value="">';
		echo '<input type="hidden" name="importepago" id="importepago" value="">';
		echo '<input type="hidden" name="fechapago" id="fechapago" value="">';
		echo'<table>
		<tr><td><a href="#" onclick="$(\'#panel\').show();atcr(\'edo_cuenta_credito.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>
		<td><a href="#" onClick="var importe = 0;
			$(\'.checksp\').each(function(){
				if(this.checked){
					importe+=$(this).attr(\'total\')/1;
				}
			});
			$(\'#importedialog\').val(importe.toFixed(2));
			$(\'#dialog\').dialog(\'open\');"><img src="images/finalizar.gif" border="0">Pagar</a></td><td>&nbsp;</td>
		</tr></table></br></br>';
		echo '<table><tr><th>Mostrar</th><td><select name="estatusf" onChange="atcr(\'\',\'\',1,\''.$_POST['reg'].'\')"><option value="0">Todos</option>
		<option value="1"'; if($_POST['estatusf']==1) echo ' selected'; echo '>Pendientes de Pago</option>
		<option value="2"'; if($_POST['estatusf']==2) echo ' selected'; echo '>Pagadas</option>
		</select></td></tr></table>';
		//Listado de plazas
		$select1= " SELECT * FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' and cliente='".$_POST['reg']."' AND tipo_pago='7'";				
		if($_POST['estatusf']==1) $select1.=" AND fechapago='0000-00-00'";
		elseif($_POST['estatusf']==2) $select1.=" AND fechapago>'0000-00-00'";
		$res=mysql_query($select1) or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);
//		echo''.$select.'';
		$nivelUsuario = nivelUsuario();
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Pagar<br><input type="checkbox" name="seltp" value="1" onClick="if(this.checked) $(\'.checksp\').attr(\'checked\',\'checked\'); else $(\'.checksp\').removeAttr(\'checked\');"></th><th width="">Folio</th><th width="">Fecha</th><th width="">Fecha Pago</th><th width="">Cliente</th><th width="">Tipo Pago</th><th width="">Total</th>';
			echo '</tr>';
			while($row=mysql_fetch_array($res)) {	
				rowb();
				echo '<td align="center">';
				
				if($row['fechapago']=='0000-00-00'){
						echo '<input type="checkbox" class="checksp" name="checksp[]" total="'.$row['total'].'" value="'.$row['cve'].'">';
					}else{
						if($_POST['cveusuario']==1){
					echo'<a href="#" onClick="atcr(\'\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="cancelar"></a>&nbsp;Pagado';
						}

					}
					echo '</td>';
					
				echo '<td align="center">'.$row['folio'].'</td>';
				echo '<td align="center">'.$row['fecha'].'</td>';
				echo '<td align="center">'.$row['fechapago'].'</td>';
				echo '<td align="">'.$array_clientes[$row['cliente']].'</td>';
				if($row['fechapago']!='0000-00-00'){
						echo '<td align="center">'.$array_tipo_pago[$row['formapago']].'</td>';
					}else{
					echo '<td align="center"></td>';
					}
				
				echo '<td align="right">'.number_format($row['total'],2).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="8" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
echo '
	<Script language="javascript">

	$("#dialog").dialog({ 
		bgiframe: true,
		autoOpen: false,
		modal: true,
		width: 400,
		height: 300,
		autoResize: true,
		position: "center",
		buttons: {
			"Aceptar": function(){ 
				document.forma.fechapago.value=$("#fechapagodialog").val();
				document.forma.formapago.value=$("#tipopagodialog").val();
				document.forma.referencia.value=$("#referenciadialog").val();
				document.forma.importepago.value=$("#importedialog").val();
				atcr("edo_cuenta_credito.php","","12","0");
			},
			"Cerrar": function(){ 
				$("#importedialog").val("");
				$("#referenciadialog").val("");
				$(this).dialog("close"); 
			}
		},
	});

	</Script>
';

}	

if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<!--<td><a href="#" onclick="atcr(\'edo_cuenta_credito.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>-->';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr style="display:none;"><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.$_POST['fecha_ini'].'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr style="display:none;"><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.$_POST['fecha_fin'].'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
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
			objeto.open("POST","edo_cuenta_credito.php",true);
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
	
		buscarRegistros();
		
	
	

	</Script>
';

?>