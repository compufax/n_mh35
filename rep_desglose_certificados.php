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

$array_color=array('5 y 6'=>array(5,6),'7 y 8'=>array(7,8), '3 y 4'=>array(3,4),'1 y 2'=>array(1,2),'0 y 9'=>array(0,9));


//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$array_tipo_placa = array(1=>"Particular",2=>"Intensivo");

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

function tipoPlaca($placa,$array_tipos_placa){
	global $array_color;
	$tipo = 0;
	$numero = '';
	for($i=0;$i<strlen($placa);$i++){
		if($placa[$i]>='0' && $placa[$i]<='9'){
			$numero = $placa[$i];
		}
	}
	
	foreach($array_tipos_placa as $tipos)
	{
		if($tipo == 0){
			if(strlen($tipos['placa']) == strlen($placa))
			{
				if(preg_match($tipos['expresion'], $placa)){
					$tipo = $tipos['tipo'];
				}
			}
		}
	}
	
	return $tipo;
}




if($_POST['cmd']==100){
	echo '<h1>Reporte de Desglose de Certificados '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'];
	if($_POST['plazausuario']>0) echo '<br>Plaza: '.$array_plazas[$_POST['plazausuario']];
	echo '</h1>';
	echo fechaLocal().' '.horaLocal().'<br>';
	$res = mysql_query("SELECT * FROM engomados WHERE localidad = '".$_POST['localidad']."' and entrega=1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_engomados[$row['cve']]['nombre']=$row['nombre'];
		$array_engomados[$row['cve']]['certificados']=array();
		$array_engomados[$row['cve']]['cantidad']=0;
	}

	
	$res = mysql_query("SELECT engomado, plaza, certificado FROM (SELECT a.engomado, a.plaza, a.certificado FROM certificados a 
	INNER JOIN datosempresas d ON a.plaza = d.plaza
	WHERE a.plaza IN (".$_POST['plaza'].") AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus!='C' AND d.localidad_id = '".$_POST['localidad']."' 
	UNION ALL
	SELECT a.engomado, a.plaza, a.certificado FROM certificados_cancelados a 
	INNER JOIN datosempresas d ON a.plaza = d.plaza
	WHERE a.plaza IN (".$_POST['plaza'].") AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus!='C' AND d.localidad_id = '".$_POST['localidad']."') a
	ORDER BY CAST(certificado as UNSIGNED)");
	while($row = mysql_fetch_array($res)){
		$array_engomados[$row['engomado']]['certificados'][] = array(
			'plaza' => $array_plazas[$row['plaza']],
			'certificado' => $row['certificado']
		);
		$array_engomados[$row['engomado']]['cantidad']++;
	}
	
	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	echo '<tr>';
	$maximo=0;
	foreach($array_engomados as $k=>$datos){
		if($_POST['plazausuario']==0){
			echo '<th colspan="2">'.$datos['nombre'].'</th>';
		}
		else{
			echo '<th>'.$datos['nombre'].'</th>';
		}
		if($maximo<$datos['cantidad']) $maximo=$datos['cantidad'];
	}
	echo '</tr>';
	if($_POST['plazausuario']==0){
		echo '<tr>';
		foreach($array_engomados as $k=>$datos){
			echo '<th>Plaza</th><th>Certificado</th>';
		}
		echo '</tr>';
	}
	
	for($i=0;$i<$maximo;$i++){
		echo '<tr>';
		foreach($array_engomados as $k=>$datos){
			if($_POST['plazausuario']==0){
				echo '<td>'.$datos['certificados'][$i]['plaza'].'</td>';
			}
			echo '<td align="center">'.$datos['certificados'][$i]['certificado'].'</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
	exit();
}

if($_POST['ajax']==1){

	$res = mysql_query("SELECT * FROM engomados WHERE localidad = '".$_POST['localidad']."' and entrega=1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_engomados[$row['cve']]['nombre']=$row['nombre'];
		$array_engomados[$row['cve']]['certificados']=array();
		$array_engomados[$row['cve']]['cantidad']=0;
	}

	$res = mysql_query("SELECT engomado, plaza, certificado FROM (SELECT a.engomado, a.plaza, a.certificado FROM certificados a 
	INNER JOIN datosempresas d ON a.plaza = d.plaza
	WHERE a.plaza IN (".$_POST['plaza'].") AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus!='C' AND d.localidad_id = '".$_POST['localidad']."' 
	UNION ALL
	SELECT a.engomado, a.plaza, a.certificado FROM certificados_cancelados a 
	INNER JOIN datosempresas d ON a.plaza = d.plaza
	WHERE a.plaza IN (".$_POST['plaza'].") AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND a.estatus!='C' AND d.localidad_id = '".$_POST['localidad']."') a
	ORDER BY CAST(certificado as UNSIGNED)");
	while($row = mysql_fetch_array($res)){
		$array_engomados[$row['engomado']]['certificados'][] = array(
			'plaza' => $array_plazas[$row['plaza']],
			'certificado' => $row['certificado']
		);
		$array_engomados[$row['engomado']]['cantidad']++;
	}
	
	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	echo '<tr>';
	$maximo=0;
	foreach($array_engomados as $k=>$datos){
		if($_POST['plazausuario']==0){
			echo '<th colspan="2">'.$datos['nombre'].'</th>';
		}
		else{
			echo '<th>'.$datos['nombre'].'</th>';
		}
		if($maximo<$datos['cantidad']) $maximo=$datos['cantidad'];
	}
	echo '</tr>';
	if($_POST['plazausuario']==0){
		echo '<tr>';
		foreach($array_engomados as $k=>$datos){
			echo '<th>Plaza</th><th>Certificado</th>';
		}
		echo '</tr>';
	}
	
	for($i=0;$i<$maximo;$i++){
		echo '<tr>';
		foreach($array_engomados as $k=>$datos){
			if($_POST['plazausuario']==0){
				echo '<td>'.$datos['certificados'][$i]['plaza'].'</td>';
			}
			echo '<td align="center">'.$datos['certificados'][$i]['certificado'].'</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
		
	
	exit();
}


top($_SESSION);
	

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
				echo 'atcr(\'rep_desglose_certificados.php\',\'_blank\',100,0);}"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>';
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
				if($k==2) echo ' selected';
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
			objeto.open("POST","rep_desglose_certificados.php",true);
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