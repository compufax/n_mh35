<?php
include("main.php");

//ARREGLOS

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsUsuario=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza where a.estatus!='I' ORDER BY b.localidad_id, a.lista, a.numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}


$res=mysql_query("SELECT * FROM areas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_localidad[$row['cve']]=$row['nombre'];
}

$array_color=array('5 y 6'=>'FFFF00','7 y 8'=>'FF00FF', '3 y 4'=>'FF0000','1 y 2'=>'00FF00','0 y 9'=>'0000FF');

//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);

$abono=0;

function numeroPlaca($placa){
	$numero = '';
	for($i=0;$i<strlen($placa);$i++){
		if($placa[$i]>='0' && $placa[$i]<='9'){
			$numero = $placa[$i];
		}
	}
	return $numero;
}

if($_POST['cmd']==100){
	echo '<h1>Reporte de la Secretaria por Color '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'];
	if($_POST['plazausuario']>0) echo '<br>Plaza: '.$array_plazas[$_POST['plazausuario']];
	echo '</h1>';
	echo fechaLocal().' '.horaLocal().'<br>';
	$res = mysql_query("SELECT * FROM engomados WHERE localidad = '".$_POST['localidad']."' AND entrega=1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		echo '<h1>'.$row['nombre'].'</h1>';
		echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><th>Dia</th><th>&nbsp;</th>';
		foreach($array_color as $k=>$v){
			echo '<th bgcolor="#'.$v.'">'.$k.'</th>';
		}
		echo '<th>Cancelados</th><th>Total</th><th>Total Utilizados</th></tr>';
		$fecha = $_POST['fecha_ini'];
		$array_totales=array();
		while($fecha<=$_POST['fecha_fin'])
		{
			echo '<tr>';
			echo '<td align="center">'.substr($fecha,8,2).'</td>';
			$arfecha=explode("-",$fecha);
			$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
			echo '<td align="center">'.$array_dias[$dia].'</td>';
			$array_verificaciones=array();
			$total=0;
			$res1 = mysql_query("SELECT placa FROM certificados WHERE plaza IN (".$_POST['plaza'].") AND fecha='".$fecha."' AND estatus!='C' AND engomado='".$row['cve']."'");
			while($row1 = mysql_fetch_array($res1)){
				$array_verificaciones[numeroPlaca($row1['placa'])]++;
				$total++;
			}
			echo '<td align="center">'.($array_verificaciones[5]+$array_verificaciones[6]).'</td>';
			echo '<td align="center">'.($array_verificaciones[7]+$array_verificaciones[8]).'</td>';
			echo '<td align="center">'.($array_verificaciones[3]+$array_verificaciones[4]).'</td>';
			echo '<td align="center">'.($array_verificaciones[1]+$array_verificaciones[2]).'</td>';
			echo '<td align="center">'.($array_verificaciones[9]+$array_verificaciones[0]).'</td>';
			$res1 = mysql_query("SELECT COUNT(cve) FROM certificados_cancelados WHERE plaza IN (".$_POST['plaza'].") AND fecha='".$fecha."' AND estatus!='C' AND engomado='".$row['cve']."'");
			$row1 = mysql_fetch_array($res1);
			echo '<td align="center">'.$row1[0].'</td>';
			echo '<td align="center">'.$total.'</td>';
			echo '<td align="center">'.($row1[0]+$total).'</td>';
			echo '</tr>';
			$array_totales[0]+=($array_verificaciones[5]+$array_verificaciones[6]);
			$array_totales[1]+=($array_verificaciones[7]+$array_verificaciones[8]);
			$array_totales[2]+=($array_verificaciones[3]+$array_verificaciones[4]);
			$array_totales[3]+=($array_verificaciones[1]+$array_verificaciones[2]);
			$array_totales[4]+=($array_verificaciones[9]+$array_verificaciones[0]);
			$array_totales[5]+=$row1[0];
			$array_totales[6]+=$total;
			$array_totales[7]+=$row1[0]+$total;
			$fecha=date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fecha) ) );
		}
		echo '<tr><th>&nbsp;</th><th>Total</th>';
		foreach($array_totales as $v) echo '<th>'.$v.'</th>';
		echo '</tr>';
		echo '</table><br><br>';
	}
	exit();
}

if($_POST['ajax']==1){
	$res = mysql_query("SELECT * FROM engomados WHERE localidad = '".$_POST['localidad']."' AND entrega=1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		echo '<h1>'.$row['nombre'].'</h1>';
		echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><th>Dia</th><th>&nbsp;</th>';
		foreach($array_color as $k=>$v){
			echo '<th bgcolor="#'.$v.'">'.$k.'</th>';
		}
		echo '<th>Cancelados</th><th>Total</th><th>Total Utilizados</th></tr>';
		$fecha = $_POST['fecha_ini'];
		$array_totales=array();
		while($fecha<=$_POST['fecha_fin'])
		{
			echo '<tr>';
			echo '<td align="center">'.substr($fecha,8,2).'</td>';
			$arfecha=explode("-",$fecha);
			$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
			echo '<td align="center">'.$array_dias[$dia].'</td>';
			$array_verificaciones=array();
			$total=0;
			$res1 = mysql_query("SELECT placa FROM certificados WHERE plaza IN (".$_POST['plaza'].") AND fecha='".$fecha."' AND estatus!='C' AND engomado='".$row['cve']."'");
			while($row1 = mysql_fetch_array($res1)){
				$array_verificaciones[numeroPlaca($row1['placa'])]++;
				$total++;
			}
			echo '<td align="center"><a href="#" onClick="atcr(\'rep_secretaria_color.php\',\'\',1,\''.$fecha.'|'.$row['cve'].'|1\')">'.($array_verificaciones[5]+$array_verificaciones[6]).'</a></td>';
			echo '<td align="center"><a href="#" onClick="atcr(\'rep_secretaria_color.php\',\'\',1,\''.$fecha.'|'.$row['cve'].'|2\')">'.($array_verificaciones[7]+$array_verificaciones[8]).'</a></td>';
			echo '<td align="center"><a href="#" onClick="atcr(\'rep_secretaria_color.php\',\'\',1,\''.$fecha.'|'.$row['cve'].'|3\')">'.($array_verificaciones[3]+$array_verificaciones[4]).'</a></td>';
			echo '<td align="center"><a href="#" onClick="atcr(\'rep_secretaria_color.php\',\'\',1,\''.$fecha.'|'.$row['cve'].'|4\')">'.($array_verificaciones[1]+$array_verificaciones[2]).'</a></td>';
			echo '<td align="center"><a href="#" onClick="atcr(\'rep_secretaria_color.php\',\'\',1,\''.$fecha.'|'.$row['cve'].'|5\')">'.($array_verificaciones[9]+$array_verificaciones[0]).'</a></td>';
			$res1 = mysql_query("SELECT COUNT(cve) FROM certificados_cancelados WHERE plaza IN (".$_POST['plaza'].") AND fecha='".$fecha."' AND estatus!='C' AND engomado='".$row['cve']."'");
			$row1 = mysql_fetch_array($res1);
			echo '<td align="center"><a href="#" onClick="atcr(\'rep_secretaria_color.php\',\'\',2,\''.$fecha.'|'.$row['cve'].'\')">'.$row1[0].'</a></td>';
			echo '<td align="center"><a href="#" onClick="atcr(\'rep_secretaria_color.php\',\'\',1,\''.$fecha.'|'.$row['cve'].'|0\')">'.$total.'</td>';
			echo '<td align="center">'.($row1[0]+$total).'</td>';
			echo '</tr>';
			$array_totales[0]+=($array_verificaciones[5]+$array_verificaciones[6]);
			$array_totales[1]+=($array_verificaciones[7]+$array_verificaciones[8]);
			$array_totales[2]+=($array_verificaciones[3]+$array_verificaciones[4]);
			$array_totales[3]+=($array_verificaciones[1]+$array_verificaciones[2]);
			$array_totales[4]+=($array_verificaciones[9]+$array_verificaciones[0]);
			$array_totales[5]+=$row1[0];
			$array_totales[6]+=$total;
			$array_totales[7]+=$row1[0]+$total;
			$fecha=date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fecha) ) );
		}
		echo '<tr><th>&nbsp;</th><th>Total</th>';
		foreach($array_totales as $v) echo '<th>'.$v.'</th>';
		echo '</tr>';
		echo '</table><br><br>';
	}
	exit();
}


top($_SESSION);

	if($_POST['cmd']==2){
		$res = mysql_query("SELECT * FROM motivos_cancelacion_certificados");
		while($row=mysql_fetch_array($res)){
			$array_motivos[$row['cve']]=$row['nombre'];
		}
		$res = mysql_query("SELECT * FROM engomados WHERE 1 ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_engomado[$row['cve']]=$row['nombre'];
		}
		$res = mysql_query("SELECT * FROM usuarios");
		while($row=mysql_fetch_array($res)){
			$array_usuario[$row['cve']]=$row['usuario'];
		}
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="atcr(\'rep_secretaria_color.php\',\'\',0,0)"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a>&nbsp;&nbsp;</td>
				';
		echo '</tr>';
		echo '</table>';
		$datos = explode("|",$_POST['reg']);
		echo '<h1>Reporte de la Secretaria por color de '.$array_engomado[$datos[1]].' Cancelados del dia '.$datos[0].'</h1>';
		$select= " SELECT * FROM certificados_cancelados WHERE fecha = '".$datos[0]."' AND engomado = '".$datos[1]."' AND estatus!='C'";
		$select.=" ORDER BY cve DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Plaza</th><th>Folio</th><th>Fecha</th><th>Motivo</th><th>Tipo de Certificado</th><th>Holograma</th><th>Usuario</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="left">'.$array_plazas[$row['plaza']].'</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_motivos[$row['motivo']])).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_engomado[$row['engomado']])).'</td>';
				echo '<td align="center">'.htmlentities($row['certificado']).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="9" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
	}
	
	if($_POST['cmd']==1){
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="atcr(\'rep_secretaria_color.php\',\'\',0,0)"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a>&nbsp;&nbsp;</td>
				';
		echo '</tr>';
		echo '</table>';
		$array_engomado = array();
		$res = mysql_query("SELECT * FROM engomados WHERE 1 ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_engomado[$row['cve']]=$row['nombre'];
		}
		$res = mysql_query("SELECT * FROM usuarios");
		while($row=mysql_fetch_array($res)){
			$array_usuario[$row['cve']]=$row['usuario'];
		}

		$res = mysql_query("SELECT * FROM tipo_combustible ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_tipo_combustible[$row['cve']]=$row['nombre'];
		}

		$res = mysql_query("SELECT * FROM tecnicos WHERE plaza='".$_POST['plazausuario']."'");
		while($row=mysql_fetch_array($res)){
			$array_personal[$row['cve']]=$row['nombre'];
		}
		$array_color=array(1=>'5 y 6',2=>'7 y 8',3=>'3 y 4',4=>'1 y 2',5=>'0 y 9');
		$datos = explode("|",$_POST['reg']);
		echo '<h1>Reporte de la Secretaria por color de '.$array_engomado[$datos[1]].' tipo '.$array_color[$datos[2]].' del dia '.$datos[0].'</h1>';
		$select= " SELECT * FROM certificados WHERE fecha = '".$datos[0]."' AND engomado = '".$datos[1]."' AND estatus!='C'";
		$select.=" ORDER BY cve DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		$array_totales_engomados=array();
		$rechazados=0;
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Plaza</th><th>Folio</th><th>Fecha</th><th>Ticket</th><th>Placa</th><th>Tipo de Combustible</th>
			<th>Tipo de Certificado</th><th>Tecnico</th><th>Holograma</th><th>Entregado</th><th>Usuario</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				$numeroPlaca = numeroPlaca($row['placa']);
				$mostrar = 0;
				if($datos[2]==0) $mostrar = 1;
				elseif($datos[2]==1 && ($numeroPlaca==5 || $numeroPlaca==6)) $mostrar = 1;
				elseif($datos[2]==2 && ($numeroPlaca==7 || $numeroPlaca==8)) $mostrar = 1;
				elseif($datos[2]==3 && ($numeroPlaca==3 || $numeroPlaca==4)) $mostrar = 1;
				elseif($datos[2]==4 && ($numeroPlaca==1 || $numeroPlaca==2)) $mostrar = 1;
				elseif($datos[2]==5 && ($numeroPlaca==0 || $numeroPlaca==9)) $mostrar = 1;
				if($mostrar == 1){	
					$res1=mysql_query("SELECT engomado,factura,tipo_combustible FROM cobro_engomado WHERE plaza = '".$row['plaza']."' AND cve='".$row['ticket']."'");
					$row1=mysql_fetch_array($res1);
					rowb();
					echo '<td align="left">'.$array_plazas[$row['plaza']].'</td>';
					echo '<td align="center">'.htmlentities($row['cve']).'</td>';
					echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
					echo '<td align="center">'.htmlentities($row['ticket']).'</td>';
					echo '<td align="center">'.htmlentities($row['placa']).'</td>';
					echo '<td align="center">'.htmlentities($array_tipo_combustible[$row1['tipo_combustible']]).'</td>';
					echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
					echo '<td align="left">'.htmlentities(utf8_encode($array_personal[$row['tecnico']])).'</td>';
					echo '<td align="center">'.htmlentities($row['certificado']).'</td>';
					echo '<td align="center">'.htmlentities($array_nosi[$row['entregado']]).'</td>';
					echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
					echo '</tr>';
					$t++;
				}
			}
			echo '	
				<tr>
				<td colspan="11" bgcolor="#E9F2F8">'.$t.' Registro(s)</td>
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
		
	}

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="if(document.forma.localidad.value==\'all\'){
					alert(\'Necesita seleccionar una localidad\');
				}
				else{
					buscarRegistros(0,1);
				}"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="if(document.forma.localidad.value==\'all\'){
					alert(\'Necesita seleccionar una localidad\');
				}
				else{';
				if($_POST['plazausuario']==0) echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
				echo 'atcr(\'rep_secretaria_color.php\',\'_blank\',100,0);}"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.date( "Y-m-d" , strtotime ( "-6 day" , strtotime(fechaLocal()) ) ).'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		if($_POST['plazausuario']>0){
			echo '<tr><td>Plaza</td><td>'.$array_plazas[$_POST['plazausuario']].'<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'"><input type="hidden" name="localidad" id="localidad" value="'.$rowempresa['localidad_id'].'"></td></tr>';
		}
		else{
			echo '<tr><td align="left">Localidad</td><td><select name="localidad" id="localidad"><option value="all">Seleccione</option>';
			foreach($array_localidad as $k=>$v){
				echo '<option value="'.$k.'"';
				echo '>'.$v.'</option>';
			}
			echo '</select>';
			echo '<tr><td align="left">Plaza</td><td><select multiple="multiple" name="plazas" id="plazas">';
			foreach($array_plazas as $k=>$v){
				echo '<option value="'.$k.'" selected>'.$v.'</option>';
			}
			echo '</select>';
			echo '<input type="hidden" name="plaza" id="plaza" value=""></td></tr>';
		}
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
bottom();
echo '
<Script language="javascript">';
if($_POST['plazausuario']==0){
	echo '
	$("#plazas").multipleSelect({
		width: 500
	});	
	function buscarRegistros(){
		document.forma.plaza.value=$("#plazas").multipleSelect("getSelects");
	';
}
else{
	echo 'function buscarRegistros(){
	';
}
echo '  document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","rep_secretaria_color.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&localidad="+document.getElementById("localidad").value+"&plaza="+document.getElementById("plaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
		
	
	

	</Script>
';

?>