<?php
include("main.php");

//ARREGLOS

$rsUsuario=mysql_query("SELECT * FROM plazas where estatus!='I' ORDER BY numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}

$res=mysql_query("SELECT * FROM areas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_localidad[$row['cve']]=$row['nombre'];
}

$abono=0;

if($_POST['cmd']==100){
	echo '<h2>Timbres Usados del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';
	echo fechaLocal().' '.horaLocal().'<br>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	if($_POST['cveusuario']==1) $c++;
	echo '<tr bgcolor="#E9F2F8"><th>Plaza</th>';
	echo '<th>No de Timbres</th><th>Timbres Cancelados</th><th>Timbres Ocupados</th></tr>'; 
	$sumacargo=array(0,0,0);
	$x=0;
	//$res1=mysql_query("SELECT a.* FROM empresas a WHERE a.cve IN (".$_POST['empresa'].")");
	if($_POST['localidad']!='all'){
		$res1=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.localidad_id = '".$_POST['localidad']."' ORDER BY a.numero");
	}
	else{
		$res1=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].")");
	}
	while($row1=mysql_fetch_array($res1)){
		rowb();
		$select= " SELECT COUNT(a.cve),SUM(IF(c.cancelacion_sat!='',1,0)),
		SUM(IF(c.cancelacion_sat!='',2,1)) 
		FROM personal_nomina a 
		INNER JOIN historial_timbrado c ON a.cve = c.nomina
		WHERE a.plazatimbro='".$row1['cve']."' AND date(c.fechatimbre)>='".$_POST['fecha_ini']."' AND date(c.fechatimbre)<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select= " SELECT COUNT(a.cve),SUM(IF(c.cancelacion_sat!='',1,0)),
		SUM(IF(c.cancelacion_sat!='',2,1)) 
		FROM personal_nomina a 
		INNER JOIN historial_timbrado c ON a.cve = c.nomina
		WHERE a.plazatimbro='".$row1['cve']."' AND LEFT(c.cancelacion_sat,10)>='".$_POST['fecha_ini']."' AND LEFT(c.cancelacion_sat,10)<='".$_POST['fecha_fin']."' ";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		echo '<td>'.htmlentities(utf8_encode($row1['numero'].' '.$row1['nombre'])).'</td>';
		echo '<td align="right">'.number_format($row[0],0).'</td>';
		echo '<td align="right">'.number_format($row2[0],0).'</td>';
		echo '<td align="right">'.number_format($row[0]+$row2[0],0).'</td>';
		echo '</tr>';
		$x++;
		$sumacargo[0]+=$row[0];
		$sumacargo[1]+=$row2[0];
		$sumacargo[2]+=$row[0]+$row2[0];
	}
	$c=4;
	echo '<tr>';
	echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		$decimal=0;
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==1){
	$filtro="";
	/*$select= " SELECT a.* FROM facturas as a WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
	if($_POST['plaza']!="all") $select.=" AND a.plaza='".$_POST['plaza']."'";
	$rsabonos=mysql_query($select) or die(mysql_error());*/
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	if($_POST['cveusuario']==1) $c++;
	echo '<tr bgcolor="#E9F2F8"><th>Plaza</th>';
	echo '<th>No de Timbres</th><th>Timbres Cancelados</th><th>Timbres Ocupados</th></tr>'; 
	$sumacargo=array(0,0,0);
	$x=0;
	if($_POST['localidad']!='all'){
		$res1=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.localidad_id = '".$_POST['localidad']."' ORDER BY a.numero");
	}
	else{
		$res1=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].")");
	}
	while($row1=mysql_fetch_array($res1)){
		rowb();
		$select= " SELECT COUNT(a.cve),SUM(IF(c.cancelacion_sat!='',1,0)),
		SUM(IF(c.cancelacion_sat!='',2,1)) 
		FROM personal_nomina a 
		INNER JOIN historial_timbrado c ON a.cve = c.nomina
		WHERE a.plazatimbro='".$row1['cve']."' AND date(c.fechatimbre)>='".$_POST['fecha_ini']."' AND date(c.fechatimbre)<='".$_POST['fecha_fin']."' ";
		$res=mysql_query($select) or die(mysql_error());
		$row=mysql_fetch_array($res);
		$select= " SELECT COUNT(a.cve),SUM(IF(c.cancelacion_sat!='',1,0)),
		SUM(IF(c.cancelacion_sat!='',2,1)) 
		FROM personal_nomina a 
		INNER JOIN historial_timbrado c ON a.cve = c.nomina
		WHERE a.plazatimbro='".$row1['cve']."' AND LEFT(c.cancelacion_sat,10)>='".$_POST['fecha_ini']."' AND LEFT(c.cancelacion_sat,10)<='".$_POST['fecha_fin']."' ";
		$res2=mysql_query($select) or die(mysql_error());
		$row2=mysql_fetch_array($res2);
		echo '<td>'.htmlentities(utf8_encode($row1['numero'].' '.$row1['nombre'])).'</td>';
		echo '<td align="right">'.number_format($row[0],0).'</td>';
		echo '<td align="right">'.number_format($row2[0],0).'</td>';
		echo '<td align="right">'.number_format($row[0]+$row2[0],0).'</td>';
		echo '</tr>';
		$x++;
		$sumacargo[0]+=$row[0];
		$sumacargo[1]+=$row2[0];
		$sumacargo[2]+=$row[0]+$row2[0];
	}
	$c=4;
	echo '<tr>';
	echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
	foreach($sumacargo as $k=>$v){
		$decimal=0;
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,$decimal).'</td>';
	}
	echo '</tr>';
	echo '</table>';
	exit();
}


top($_SESSION);
	

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="';
				/*if($_POST['plazausuario']==0)*/ echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
				echo 'atcr(\'nominaxplaza.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.substr(fechaLocal(),0,8).'01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		/*if($_POST['plazausuario']>0){
			echo '<tr><td>Plaza</td><td>'.$array_plazas[$_POST['plazausuario']].'<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'"><input type="hidden" name="localidad" id="localidad" value="all"></td></tr>';
		}
		else{*/
			echo '<tr><td align="left">Localidad</td><td><select name="localidad" id="localidad"><option value="all">Todas</option>';
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
		//}
		echo '<tr style="display:none;"><td>RFC</td><td><input type="text" size="20" name="rfc" id="rfc" class="textField"></td></tr>';
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
//if($_POST['plazausuario']==0){
	echo '
	$("#plazas").multipleSelect({
		width: 500
	});	
	function buscarRegistros(){
		document.forma.plaza.value=$("#plazas").multipleSelect("getSelects");
	';
/*}
else{
	echo 'function buscarRegistros(){
	';
}*/
echo '  document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","nominaxplaza.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&localidad="+document.getElementById("localidad").value+"&rfc="+document.getElementById("rfc").value+"&plaza="+document.getElementById("plaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
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
	

	</Script>
';
?>