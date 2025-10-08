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

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);


if($_POST['ajax']==1){

	$res = mysql_query("SELECT * FROM engomados WHERE localidad = '".$_POST['localidad']."' and entrega=1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_engomados[$row['cve']]['nombre']=$row['nombre'];
		$array_engomados[$row['cve']]['certificados']=array();
		$array_engomados[$row['cve']]['cantidad']=0;
	}

	$res = mysql_query("SELECT a.engomado, a.plaza, b.folio FROM compra_certificados a 
		INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra
		WHERE a.plaza IN (".$_POST['plaza'].") AND a.anio=4 AND a.estatus!='C' AND b.estatus=0 ORDER BY b.folio");
	while($row = mysql_fetch_array($res)){
		$array_engomados[$row['engomado']]['certificados'][] = array(
			'plaza' => $array_plazas[$row['plaza']],
			'certificado' => $row['folio']
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
			echo '<th>'.$datos['nombre'].' ('.$datos['cantidad'].')</th>';
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

if($_POST['cmd']==2){
	mysql_query("UPDATE compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra 
	INNER JOIN certificados c ON a.plaza = c.plaza AND a.engomado = c.engomado AND b.folio =  CAST(c.certificado AS UNSIGNED) AND c.estatus!='C' 
	SET b.estatus=1 
	WHERE a.estatus!='C' AND b.estatus=0 AND a.anio=4");
	mysql_query("UPDATE compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra 
	INNER JOIN certificados_cancelados c ON a.plaza = c.plaza AND a.engomado = c.engomado AND b.folio =  CAST(c.certificado AS UNSIGNED) AND c.estatus!='C' 
	SET b.estatus=1 
	WHERE a.estatus!='C' AND b.estatus=0 AND a.anio=4");
	$_POST['cmd']=0;
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
				echo 'atcr(\'rep_certificados_sin_entregar.php\',\'_blank\',100,0);}"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>
				<td><a href="#" onClick="atcr(\'rep_certificados_sin_entregar.php\',\'\',2,0);"><img src="images/validosi.gif" border="0">&nbsp;&nbsp;Marcar</a></td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr style="display:none;"><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.date( "Y-m-d" , strtotime ( "-6 day" , strtotime(fechaLocal()) ) ).'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr style="display:none;"><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
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
			objeto.open("POST","rep_certificados_sin_entregar.php",true);
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