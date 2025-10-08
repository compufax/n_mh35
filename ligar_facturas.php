<?php
include("main.php");
//ARREGLOS

$array_usuario[-1]='WEB';
$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);
$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$importe_iva=round($row['precio']*16/116,2);
	$array_engomadoprecio[$row['cve']]=$row['precio']-$importe_iva;
}
$res=mysql_query("SELECT * FROM bancos");
while($row=mysql_fetch_array($res)){
	$array_bancos[$row['cve']]=$row['nombre'];
}


$array_clientes=array();
$res=mysql_query("SELECT * FROM clientes WHERE plaza='".$_POST['plazausuario']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	if($_POST['cveusuario'] == 1)
		$array_clientes[$row['cve']]=$row['nombre'].'('.$row['cve'].')';
	else
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
$datossucursal='';


$abono=0;



if($_POST['ajax']==1){
	$filtro="";
	$select= " SELECT a.* FROM facturas as a LEFT JOIN cobro_engomado as b ON a.plaza = b.plaza AND a.cve = b.factura WHERE a.plaza='".$_POST['plazausuario']."' AND a.estatus!='C' AND ISNULL(b.cve)";
	if($_POST['folio'] != ""){
		$select .= " AND a.cve='".$_POST['folio']."' GROUP BY a.cve";
	}
	else{
		$select .= " AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		//if($_POST['tipo']!="all") $select.=" AND a.tipo='".$_POST['tipo']."'";
		if($_POST['cliente']!="all") $select.=" AND a.cliente='".$_POST['cliente']."'";
		$select.=" GROUP BY a.cve ORDER BY a.cve DESC";
	}
	$rsabonos=mysql_query($select) or die(mysql_error());
	if(mysql_num_rows($rsabonos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8">';
		echo '<th>Folio</th><th>Fecha</th>
		<th>Cliente</th><th>Tipo Pago</th><th>Subtotal</th>
		<th>Iva</th><th>Total</th>
		<th>Ticket</th><th>Usuario</th></tr>'; 
		$sumacargo=array();
		$x=0;
		while ($Abono=mysql_fetch_array($rsabonos)){	
			rowb();
			$estatus='';
			
			echo '<td align="center">'.$Abono['cve'].'</td>';
			//echo '<td align="center">'.$Abono['fecha'].' '.$Abono['hora'].'</td>';
			echo '<td align="center">'.htmlentities($Abono['fecha'].' '.$Abono['hora']).'</td>';
			
			echo '<td>'.htmlentities(utf8_encode($array_clientes[$Abono['cliente']])).'</td>';
			echo '<td>'.htmlentities($array_tipo_pago[$Abono['tipo_pago']]).'</td>';
			echo '<td align="right">'.number_format($Abono['subtotal'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'],2).'</td>';
			if($Plaza['localidad_id'] == 2){
				echo '<td align="center"><input type="text" class="textField" id="ticket_'.$Abono['cve'].'" value=""><br>
				<input type="button" name="Guardar" onClick="if(confirm(\'El ticket es correcto?\')) guardarTicket('.$Abono['cve'].')" class="textField"></td>';
			}
			else{
				echo '<td>&nbsp;</td>';
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
		}
	
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



if($_POST['ajax']==2){
	if($_POST['ticket'] != ''){
		$tickets = explode(",", $_POST['ticket']);
		$res = mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND cve IN (".$_POST['ticket'].")");
		$res=mysql_query("SELECT a.cve, a.estatus, a.factura, (a.monto+IFNULL(f.recuperacion,0)-IFNULL(e.devolucion,0)) as monto FROM cobro_engomado a 
			LEFT JOIN certificados b ON a.plaza = b.plaza AND a.cve = b.ticket AND b.estatus!='C' 
			LEFT JOIN 
				(SELECT ticket, SUM(devolucion) as devolucion FROM devolucion_certificado WHERE plaza = '".$_POST['plazausuario']."' AND ticket IN (".$_POST['ticket'].") AND estatus != 'C' GROUP BY ticket) e ON a.cve = e.ticket
			LEFT JOIN 
				(SELECT ticket, SUM(recuperacion) as recuperacion FROM recuperacion_certificado WHERE plaza = '".$_POST['plazausuario']."' AND ticket IN (".$_POST['ticket'].") AND estatus != 'C' GROUP BY ticket) f ON a.cve = f.ticket
			WHERE a.plaza='".$_POST['plazausuario']."' AND a.cve IN (".$_POST['ticket'].") GROUP BY a.cve") or die(mysql_error());
		
		$total = 0;
		$error = 0;
		if(mysql_num_rows($res) != count($tickets)){
			echo "Uno de los tickets no existe";
		}
		else{
			while($row = mysql_fetch_array($res)){
				if($row['estatus'] == 'C'){
					echo 'El ticket esta cancelado';
					$error = 1;
				}
				elseif($row['factura'] > 0){
					echo 'El ticket ya esta facturado';
					$error = 1;
				}
				$total+=$row['monto'];
			}
			if($error == 0){
				$res1 = mysql_query("SELECT total FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
				$row1 = mysql_fetch_array($res1);
				if(abs($row1['total']-$total) > 2){
					echo 'El monto del ticket no coincide con el de la factura';
				}
				else{
					mysql_query("UPDATE cobro_engomado SET factura='".$_POST['folio']."',documento=1 WHERE plaza='".$_POST['plazausuario']."' AND cve IN (".$_POST['ticket'].")");
					mysql_query("INSERT INTO venta_engomado_factura (plaza,venta,factura) SELECT ".$_POST['plazausuario'].",cve,factura FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND factura='".$_POST['folio']."'");
					echo 'Se ligo correctamente el ticket con la factura';
				}
			}
		}
	}
	else{
		echo 'Error en el ticket';
	}
	exit();
}



top($_SESSION);
	

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.substr(fechaLocal(),0,8).'01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Folio</td><td><input type="text" name="folio" id="folio"  size="15" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Cliente</td><td><select name="cliente" id="cliente"><option value="all">--- Todos ---</option>';
		foreach($array_clientes as $k=>$v){
			echo '<option class="cexternos" value="'.$k.'" style="color: '.$array_colorcliente[$k].';">'.$v.'</option>';
		}
		echo '</select></td></tr>';
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
			objeto.open("POST","ligar_facturas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&folio="+document.getElementById("folio").value+"&cliente="+document.getElementById("cliente").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}

	function guardarTicket(folio){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","ligar_facturas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&folio="+folio+"&ticket="+document.getElementById("ticket_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					alert(objeto.responseText);
					buscarRegistros();
				}
			}
		}
	}
	
		
	';	
	if($_POST['cmd']<1){
	echo '
	/*window.onload = function () {
			buscarRegistros(0,1); //Realizar consulta de todos los registros al iniciar la forma.
	}*/';
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