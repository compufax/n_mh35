<?php
include("main.php");

//ARREGLOS

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsUsuario=mysql_query("SELECT * FROM plazas where estatus!='I' ORDER BY numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}


$res=mysql_query("SELECT * FROM areas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_localidad[$row['cve']]=$row['nombre'];
}

$array_color=array(
	'5'=>array(1,2,7,8),'6'=>array(1,2,7,8),
	'7'=>array(2,3,8,9),'8'=>array(2,3,8,9),
	'3'=>array(3,4,9,10),'4'=>array(3,4,9,10),
	'1'=>array(4,5,10,11),'2'=>array(4,5,10,11),
	'0'=>array(5,6,11,12),'9'=>array(5,6,11,12)
	);

//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$array_tipo_placa = array(1=>"Particular",2=>"Intensivo");

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);

$abono=0;

function tipoPlaca($placa,$fecha,$array_tipos_placa){
	global $array_color;
	$tipo = 0;
	$numero = '';
	for($i=0;$i<strlen($placa);$i++){
		if($placa[$i]>='0' && $placa[$i]<='9'){
			$numero = $placa[$i];
		}
	}
	if(array_search(intval(substr($fecha,5,2)), $array_color[$numero]) === false){
		$extemporaneo=1;
	}
	else{
		$extemporaneo=0;
	}
	$idplaca=0;
	foreach($array_tipos_placa as $placa_id => $tipos)
	{
		if($tipo == 0){
			if(strlen($tipos['placa']) == strlen($placa))
			{
				if(preg_match($tipos['expresion'], $placa)){
					$tipo = $tipos['tipo'];
					$idplaca = $placa_id;
				}
			}
		}
	}
	if($extemporaneo == 1 && $idplaca != 9){
		$tipo = $tipo+2;
	}
	return $tipo;
}

if($_POST['cmd']==100){
	echo '<h1>Reporte de Clasificacion de Vehiculo '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'];
	if($_POST['plazausuario']>0) echo '<br>Plaza: '.$array_plazas[$_POST['plazausuario']];
	echo '</h1>';
	echo fechaLocal().' '.horaLocal().'<br>';
	$res=mysql_query("SELECT * FROM tipo_placa WHERE localidad='".$_POST['localidad']."' ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_tipos_placa[$row['cve']]['tipo']=$row['tipo'];
		$array_tipos_placa[$row['cve']]['placa']=$row['placa'];
		$array_tipos_placa[$row['cve']]['expresion']=$row['expresion'];
	}
	$res = mysql_query("SELECT * FROM engomados WHERE localidad = '".$_POST['localidad']."' AND entrega=1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		echo '<h1>'.$row['nombre'].'</h1>';
		echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><th>Dia</th><th>&nbsp;</th>';
		foreach($array_tipo_placa as $v){
			echo '<th>'.$v.'</th><th>'.$v.'<br>Extemporaneo</th>';
		}
		echo '<th>Total</th></tr>';
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
			$particular = $particular_extemporaneo = $intensivo = $intensivo_extemporaneo = 0;
			$res1 = mysql_query("SELECT placa FROM certificados WHERE plaza IN (".$_POST['plaza'].") AND fecha='".$fecha."' AND estatus!='C' AND engomado='".$row['cve']."'");
			while($row1 = mysql_fetch_array($res1)){
				$tipo = tipoPlaca($row1['placa'],$fecha,$array_tipos_placa);
				if($tipo > 0) $total++;
				if($tipo == 1) $particular++;
				elseif($tipo == 3) $particular_extemporaneo++;
				elseif($tipo == 2) $intensivo++;
				elseif($tipo == 4) $intensivo_extemporaneo++;
			}
			echo '<td align="center">'.$particular.'</td>';
			echo '<td align="center">'.$particular_extemporaneo.'</td>';
			echo '<td align="center">'.$intensivo.'</td>';
			echo '<td align="center">'.$intensivo_extemporaneo.'</td>';
			echo '<td align="center">'.$total.'</td>';
			echo '</tr>';
			$array_totales[0]+=$particular;
			$array_totales[1]+=$particular_extemporaneo;
			$array_totales[2]+=$intensivo;
			$array_totales[3]+=$intensivo_extemporaneo;
			$array_totales[4]+=$total;
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

	$res=mysql_query("SELECT * FROM tipo_placa WHERE localidad='".$_POST['localidad']."' ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_tipos_placa[$row['cve']]['tipo']=$row['tipo'];
		$array_tipos_placa[$row['cve']]['placa']=$row['placa'];
		$array_tipos_placa[$row['cve']]['expresion']=$row['expresion'];
	}
	$res = mysql_query("SELECT * FROM engomados WHERE localidad = '".$_POST['localidad']."' AND entrega=1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		echo '<h1>'.$row['nombre'].'</h1>';
		echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><th>Dia</th><th>&nbsp;</th>';
		foreach($array_tipo_placa as $v){
			echo '<th>'.$v.'</th><th>'.$v.'<br>Extemporaneo</th>';
		}
		echo '<th>Total</th></tr>';
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
			$particular = $particular_extemporaneo = $intensivo = $intensivo_extemporaneo = 0;
			$res1 = mysql_query("SELECT placa FROM certificados WHERE plaza IN (".$_POST['plaza'].") AND fecha='".$fecha."' AND estatus!='C' AND engomado='".$row['cve']."'");
			while($row1 = mysql_fetch_array($res1)){
				$tipo = tipoPlaca($row1['placa'],$fecha,$array_tipos_placa);
				if($tipo > 0) $total++;
				if($tipo == 1) $particular++;
				elseif($tipo == 3) $particular_extemporaneo++;
				elseif($tipo == 2) $intensivo++;
				elseif($tipo == 4) $intensivo_extemporaneo++;
			}
			echo '<td align="center"><a href="#" onClick="atcr(\'rep_clasificacion_vehiculo.php\',\'\',1,\''.$fecha.'|'.$row['cve'].'|1\')">'.$particular.'</a></td>';
			echo '<td align="center"><a href="#" onClick="atcr(\'rep_clasificacion_vehiculo.php\',\'\',1,\''.$fecha.'|'.$row['cve'].'|3\')">'.$particular_extemporaneo.'</a></td>';
			echo '<td align="center"><a href="#" onClick="atcr(\'rep_clasificacion_vehiculo.php\',\'\',1,\''.$fecha.'|'.$row['cve'].'|2\')">'.$intensivo.'</a></td>';
			echo '<td align="center"><a href="#" onClick="atcr(\'rep_clasificacion_vehiculo.php\',\'\',1,\''.$fecha.'|'.$row['cve'].'|4\')">'.$intensivo_extemporaneo.'</a></td>';
			echo '<td align="center"><a href="#" onClick="atcr(\'rep_clasificacion_vehiculo.php\',\'\',1,\''.$fecha.'|'.$row['cve'].'|0\')">'.$total.'</a></td>';
			echo '</tr>';
			$array_totales[0]+=$particular;
			$array_totales[1]+=$particular_extemporaneo;
			$array_totales[2]+=$intensivo;
			$array_totales[3]+=$intensivo_extemporaneo;
			$array_totales[4]+=$total;
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

	if($_POST['cmd']==1){
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="atcr(\'rep_clasificacion_vehiculo.php\',\'\',0,0)"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a>&nbsp;&nbsp;</td>
				';
		echo '</tr>';
		echo '</table>';
		$res=mysql_query("SELECT * FROM tipo_placa WHERE localidad='".$_POST['localidad']."' ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_tipos_placa[$row['cve']]['tipo']=$row['tipo'];
			$array_tipos_placa[$row['cve']]['placa']=$row['placa'];
			$array_tipos_placa[$row['cve']]['expresion']=$row['expresion'];
		}
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
		$array_tipo_placa = array(1=>"Particular",2=>"Intensivo",3=>"Particular Extemporaneo",4=>"Intensivo Extemporaneo");
		$datos = explode("|",$_POST['reg']);
		echo '<h1>Reporte de Clasificacion de vehiculo de '.$array_engomado[$datos[1]].' tipo '.$array_tipo_placa[$datos[2]].' del dia '.$datos[0].'</h1>';
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
				$numero = $tipo = tipoPlaca($row['placa'],$datos[0],$array_tipos_placa);
				$mostrar = 0;
				if($datos[2]==0 && $numero>0) $mostrar = 1;
				elseif($datos[2]==$numero) $mostrar = 1;
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
				echo 'atcr(\'rep_clasificacion_vehiculo.php\',\'_blank\',100,0);}"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>';
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
			objeto.open("POST","rep_clasificacion_vehiculo.php",true);
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