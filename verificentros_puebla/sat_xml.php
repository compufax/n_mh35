<?php
include("main.php");
include("imp_factura_xml.php");
//ARREGLOS

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsUsuario=mysql_query("SELECT * FROM sat_empresas ORDER BY nombre");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_empresas[$Usuario['plaza']]=$Usuario['nombre'];
}

function mestexto($fec){
	global $array_meses;
	$datos=explode("-",$fec);
	return $array_meses[intval($datos[1])].' '.$datos[0];
}
//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");


$abono=0;

if($_POST['cmd']==101){
	generaGastoPdf($_POST['plazausuario'],$_POST['reg'],1);
	exit();
}

if($_POST['cmd']==103){
	unlink($_POST['reg']);
	echo '<script>window.close();</script>';
	exit();
}

if($_POST['ajax']==1){
	$filtro="";
	$select= " SELECT a.* FROM sat_xml as a WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
	if($_POST['empresa']!="all") $select.=" AND a.plaza='".$_POST['empresa']."'";
	if($_POST['tipo']!="all") $select.=" AND a.tipo='".$_POST['tipo']."'";
	if($_POST['emisor']!="") $select.=" AND (a.rfc='".$_POST['emisor']."' OR a.nombre LIKE '%".$_POST['emisor']."%')";
	if($_POST['receptor']!="") $select.=" AND (a.rfc_r='".$_POST['receptor']."' OR a.nombre_r LIKE '%".$_POST['receptor']."%')";
	if($_POST['estado']!="") $select.=" AND a.estado LIKE '%".$_POST['estado']."%'";
	if($_POST['estado_r']!="") $select.=" AND a.estado_r LIKE '%".$_POST['estado_r']."%'";
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	if($_POST['estatus']==1) $select.=" AND a.estatus!='C'";
	elseif($_POST['estatus']==2) $select.=" AND a.estatus='C'";
	$select.=" ORDER BY a.fecha DESC,hora DESC";
	$rsabonos=mysql_query($select) or die(mysql_error());
	if(mysql_num_rows($rsabonos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$c=15;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.mysql_num_rows($rsabonos).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		echo '<th>Folio</th><th>Fecha</th><th>Emisor</th><th>RFC Emisor</th><th>Estado Emisor</th>
		<th>Receptor</th><th>RFC Receptor</th><th>Estado Receptor</th><th>Tipo Pago</th><th>Subtotal</th>
		<th>Iva</th><th>Total</th><th>UUID</th>
		<th>Usuario<br>';
		echo '<select name="usuario" onchange="document.forma.usu.value=this.value;buscarRegistros();"><option value="all">---Todos---</option>';
		$res1=mysql_query("SELECT a.usuario FROM sat_xml as a WHERE plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY a.usuario");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['usuario'].'"';
			if($row1['usuario']==$_POST['usu']) echo ' selected';
			echo '>'.$array_usuario[$row1['usuario']].'</option>';
		}
		echo '</select></th></tr>'; 
		$sumacargo=array();
		$x=0;
		$folio=0;
		while ($Abono=mysql_fetch_array($rsabonos)){	
			if($folio==0) $folio=$Abono['cve'];
			rowb();
			$estatus='';
			
			echo '<td align="center" width="40" nowrap>';
			if(file_exists('../xmls/cfdis_'.$Abono['plaza'].'_'.$Abono['cve'].'.pdf')){
				echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'../xmls/cfdis_'.$Abono['plaza'].'_'.$Abono['cve'].'.pdf\',\'_blank\',\'0\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
			}
			
			echo '</td>';
			if($Abono['cve']!=$folio){
				echo '<td align="center"><font color="RED">'.$Abono['folio'].'</font></td>';
				$folio=$Abono['cve'];
			}
			else{
				echo '<td align="center">'.$Abono['folio'].'</td>';
			}
			echo '<td align="center">'.$Abono['fecha'].' '.$Abono['hora'].'</td>';
			echo '<td>'.htmlentities(utf8_encode($Abono['nombre'])).'</td>';
			echo '<td align="center">'.$Abono['rfc'].'</td>';
			echo '<td>'.$Abono['estado'].'</td>';
			echo '<td>'.htmlentities(utf8_encode($Abono['nombre_r'])).'</td>';
			echo '<td align="center">'.$Abono['rfc_r'].'</td>';
			echo '<td>'.$Abono['estado_r'].'</td>';
			echo '<td>'.htmlentities($Abono['tipo_pago']).'</td>';
			echo '<td align="right">'.number_format($Abono['subtotal'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'],2).'</td>';
			echo '<td>'.$Abono['uuid'].'</td>';
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$sumacargo[0]+=$Abono['subtotal'];
			$sumacargo[1]+=$Abono['iva'];
			$sumacargo[2]+=$Abono['total']+$Abono['iva_retenido']+$Abono['isr_retenido'];
			$folio--;
		}
		$c=9;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.$x.' Registro(s)</td>';
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		foreach($sumacargo as $k=>$v){
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
		}
		echo '<td bgcolor="#E9F2F8" colspan="2">&nbsp;</td>';
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




top($_SESSION);
	
	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>';
		/*if(nivelUsuario()>1){
			echo '<td><a href="#" onClick="atcr(\'gastos_xml.php\',\'\',\'6\',\'0\');"><img src="images/nuevo.gif" border="0">&nbsp;Reenviar Archivos</a></td><td>&nbsp;</td>';
		}*/
		/*if(nivelUsuario()>2){
			echo '<td><a href="#" onClick="if(confirm(\'Esta seguro de cancelar los gastos\')){ atcr(\'gastos_xml.php\',\'\',\'7\',\'0\'); }"><img src="images/validono.gif" border="0">&nbsp;Cancelar Facturas</a></td><td>&nbsp;</td>';
		}*/
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.substr(fechaLocal(),0,8).'01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Empresa</td><td><select name="empresa" id="empresa"><option value="all">Todas</option>';
		foreach($array_empresas as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td align="left">Emisor</td><td><input type="text" name="emisor" id="emisor"  size="30" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Estado Emisor</td><td><input type="text" name="estado" id="estado"  size="30" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Receptor</td><td><input type="text" name="receptor" id="receptor"  size="30" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Estado Receptor</td><td><input type="text" name="estado_r" id="estado_r"  size="30" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Tipo</td><td><select name="estatus" id="estatus"><option value="all" selected>Todos</option><option value="0">Emitidos</option>
		<option value="1">Recibidos</option></select></td></tr>';
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
			objeto.open("POST","sat_xml.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&empresa="+document.getElementById("empresa").value+"&tipo="+document.getElementById("tipo").value+"&receptor="+document.getElementById("receptor").value+"&estado_r="+document.getElementById("estado_r").value+"&estado="+document.getElementById("estado").value+"&estatus="+document.getElementById("estatus").value+"&emisor="+document.getElementById("emisor").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
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