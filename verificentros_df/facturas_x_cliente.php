<?php
include("main.php");
include("imp_factura.php");
//ARREGLOS

$array_usuario[-1] = 'WEB';
$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}
$array_clientes=array();
$res=mysql_query("SELECT * FROM clientes WHERE plaza='".$_POST['plazausuario']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_clientes[$row['cve']]=$row['nombre'].'-('.$row['rfc'].')';
	$array_clientes_[$row['cve']]=$row['nombre'];
}
if($_POST['ajax']==2){
	$filtro="";
	$select= " SELECT a.* FROM facturas as a LEFT JOIN clientes b ON b.plaza = a.plaza AND b.cve = a.cliente WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus!='C'";
		$select .= " and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' ";
		if($_POST['cliente']!="all") $select.=" AND b.nombre LIKE '%".$array_clientes_[$_POST['cliente']]."%'";
		$select.=" ORDER BY a.cve DESC";
//	echo''.$select.'';
	$rsabonos=mysql_query($select) or die(mysql_error());
	if(mysql_num_rows($rsabonos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		
		$c=14;

		echo '<tr bgcolor="#E9F2F8">';
	//			echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.mysql_num_rows($rsabonos).' Registro(s)</td></tr>';
			echo '<th><input type="checkbox" name="selt" value="1" onClick="if(this.checked) $(\'.checks\').attr(\'checked\',\'checked\'); else $(\'.checks\').removeAttr(\'checked\');"></th>';
		
		echo '<th>No</th><th>Serie</th><th>Folio</th><th>Fecha</th>
		<th>Cliente</th><th>Tipo Pago</th><th>Subtotal</th>
		<th>Iva</th><th>Total</th><!--<th>Retencion I.S.R.</th><th>Retencion I.V.A.</th><th>Total</th>-->
		<th>Ticket</th><th>Fecha Ticket</th><th>Placa Ticket</th>
		<th>Usuario</th></tr>'; 
		$sumacargo=array();
		$x=0;
		$cant=1;
		while ($Abono=mysql_fetch_array($rsabonos)){	
			rowb();
			$estatus='';
			echo '<td><input type="checkbox" class="checks" name="checksf[]" value="'.$Abono['cve'].'"></td>';	
			echo '<td align="center">'.$cant.'</td>';
			echo '<td align="center">'.$Abono['serie'].'</td>';
			echo '<td align="center">'.$Abono['folio'].'</td>';
			
				echo '<td align="center">'.htmlentities($Abono['fecha'].' '.$Abono['hora']).'</td>';
			
			echo '<td>'.htmlentities(utf8_encode($array_clientes[$Abono['cliente']])).'</td>';
			echo '<td>'.htmlentities($array_tipo_pago[$Abono['tipo_pago']]).'</td>';
			echo '<td align="right">'.number_format($Abono['subtotal'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'],2).'</td>';
			//echo '<td align="right">'.number_format($Abono['isr_retenido'],2).'</td>';
			//echo '<td align="right">'.number_format($Abono['iva_retenido'],2).'</td>';
			//echo '<td align="right">'.number_format($Abono['total'],2).'</td>';
			$array_tickets=array();
			$res2=mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$Abono['plaza']."' AND factura='".$Abono['cve']."' AND estatus!='C'");
			while($row2 = mysql_fetch_array($res2)) $array_tickets[$row2['cve']] = array('placa'=>$row2['placa'],'fecha'=>$row2['fecha']);
			//if($row2=mysql_fetch_array($res2)){
			if(count($array_tickets) > 0){
				echo '<td align="center">';
				foreach($array_tickets as $ticket=>$datosticket){
					echo $ticket.'<br>';
				}
				echo '</td><td align="center">';
				foreach($array_tickets as $ticket=>$datosticket){
					echo $datosticket['fecha'].'<br>';
				}
				echo '</td><td align="center">';
				foreach($array_tickets as $ticket=>$datosticket){
					$res3=mysql_query("SELECT a.cve FROM facturas a INNER JOIN cobro_engomado b ON a.plaza=b.plaza AND a.cve=b.factura WHERE a.plaza='".$Abono['plaza']."' AND a.cve!='".$Abono['cve']."' AND b.placa = '".$datosticket['placa']."' AND ABS(DATEDIFF('".$Abono['fecha']."',fecha))<=60");
					if(mysql_num_rows($res3)>0){
						echo '<font color="RED">'.$datosticket['placa'].'</font><br>';
					}
					else{
						echo ''.$datosticket['placa'].'<br>';
					}
				}
				echo '</td>';
			}
			else{
				echo '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
			}
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$sumacargo[0]+=$Abono['subtotal'];
			$sumacargo[1]+=$Abono['iva'];
			$sumacargo[2]+=$Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'];
			//$sumacargo[3]+=$Abono['isr_retenido'];
			//$sumacargo[4]+=$Abono['iva_retenido'];
			//$sumacargo[5]+=$Abono['total'];
			$cant++;
		}
		$c=5;
		if(nivelUsuario()>0) $c++;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.$x.' Registro(s)</td>';
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		foreach($sumacargo as $k=>$v){
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
		}
		echo '<td bgcolor="#E9F2F8" colspan="4">&nbsp;</td>';
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

if($_POST['ajax']==1){
		$filtro="";
/*	$select= " SELECT * FROM facturas_x_cliente  WHERE fecha between '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."'";
		if($_POST['cliente']!="") $select.=" AND cliente='".$_POST['cliente']."'";
		$select.=" ORDER BY cve DESC";*/
		
		$select= " SELECT a.* FROM facturas_x_cliente a LEFT JOIN clientes b ON b.plaza = a.plaza AND b.cve = a.cliente WHERE a.fecha between '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."'";
		if($_POST['cliente']!="all") $select.=" AND b.nombre LIKE '%".$array_clientes_[$_POST['cliente']]."%'";
		$select.=" ORDER BY a.cve DESC";
	
	$rsabonos=mysql_query($select) or die(mysql_error());
	echo '<table width="100%" border="0" cellpadding="" cellspacing="" class="">';
	echo'<tr bgcolor="#E9F2F8"><th>Folio</th><th>Periodo</th><th>Cliente</th><th>No</br>Facturas</th><th>Total</th><th>Usuario</th></tr>';
	$total=0;
	while ($row=mysql_fetch_array($rsabonos)){
		rowb();
		echo'<td align="center">'.$row['cve'].'</td>';
		echo'<td align="center">'.$row['inicio'].'  al  '.$row['inicio'].'</td>';
		echo'<td align="center">'.utf8_decode($array_clientes_[$row['cliente']]).'</td>';
		echo'<td align="center">'.$row['facturas'].'</td>';
		echo'<td align="center">'.number_format($row['monto'],2).'</td>';
		echo'<td align="center">'.$row['usuario'].'</td>';
		echo'</tr>';
		$total=$total + $row['monto'];
	}
	echo'<tr bgcolor="#E9F2F8"><td colspan="4" align="right">Total</td><td>'.number_format($total,2).'</td><td></td></tr></table>';
	exit();
}

top($_SESSION);
	
	if ($_POST['cmd']==2) {
		
		if($_POST['checksf']!=""){	
			$x=0;
			$y="";
			$fac="";
			foreach($_POST['checksf'] as $cvefact){
			if($x!=0){$y=",";}
			$fac.=$y.$cvefact;
			$x++;
			}
//			echo''.$fac.'';
				$select= " SELECT sum(total) as tot FROM facturas WHERE cve in (".$fac.") and plaza='".$_POST['plazausuario']."'";
	$rsabonos=mysql_query($select) or die(mysql_error());
   $row1=mysql_fetch_array($rsabonos);
//   echo''.$select.'';
			mysql_query("INSERT facturas_x_cliente SET cliente='".$_POST['cliente']."',facturas='".$x."',monto='".$row1['tot']."',usuario='".$_POST['cveusuario']."',
			cve_aux='".$fac."',fecha='".fechaLocal()."', hora='".horaLocal()."',plaza='".$_POST['plazausuario']."',inicio='".$_POST['fecha_ini']."',fin	='".$_POST['fecha_fin']."'");
		}
		
		
		$_POST['cmd']=0;
	}

	/*** PAGINA PRINCIPAL **************************************************/
	if ($_POST['cmd']==1) {
		echo '<table>';
		echo '<tr>
			<td><a href="#" onClick="$(\'#panel\').show();
			if(document.forma.cliente.value==\'0\'){
				alert(\'Necesita seleccionar el cliente\');
				$(\'#panel\').hide();
			}
			else {
				atcr(\'facturas_x_cliente.php\',\'\',2,\'0\');
			}
			"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>
			<td><a href="#" onclick="Buscar_cliente();"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>';
			echo '<td><a href="#" onclick="$(\'#panel\').show();atcr(\'facturas_x_cliente.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';

		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Cliente</td><td><select name="cliente" id="cliente" onchange="Buscar_cliente();"><option value="0">--- Seleccione ---</option>';
		foreach($array_clientes as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';

		echo '</table>';
		echo '<div id="Resultados1">';
		echo '</div>';
	}
	if ($_POST['cmd']<1) {

		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>';
			echo '<td><a href="#" onClick="atcr(\'facturas_x_cliente.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0">&nbsp;Nuevo</a></td><td>&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';

		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Cliente</td><td><select name="cliente" id="cliente"><option value="">--- Todos ---</option>';
		foreach($array_clientes as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'-'.$k.'</option>';
		}
		echo '</select></td></tr>';

		echo '</table>';
		echo '<br>';
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
			objeto.open("POST","facturas_x_cliente.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&cliente="+document.getElementById("cliente").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	function Buscar_cliente()
	{
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","facturas_x_cliente.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&cliente="+document.getElementById("cliente").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados1").innerHTML = objeto.responseText;}
			}
		}

	}

	function guardarFecha(folio){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","facturas_x_cliente.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=16&folio="+folio+"&fecha="+document.getElementById("fechan_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros();}
			}
		}
	}

	$("#dialog2").dialog({ 
		bgiframe: true,
		autoOpen: false,
		modal: true,
		width: 450,
		height: 200,
		autoResize: true,
		position: "center",
		beforeClose: function( event, ui ) {
			document.forma.correos_envio.value="";
			$("#correos_envio").val("");
		},
		buttons: {
			"Aceptar": function(){ 
				document.forma.correos_envio.value=$("#correos_envio").val();
				atcr(\'facturas_x_cliente.php\',\'\',\'6\',\'0\');
			},
			"Cerrar": function(){ 
				document.forma.correos_envio.value="";
				$("#correos_envio").val("");
				$(this).dialog("close"); 
			}
		},
	}); 
	
		
	';	
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
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