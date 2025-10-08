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

$rsUsuario=mysql_query("SELECT * FROM datosempresas");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazasrfc[$Usuario['plaza']]=$Usuario['rfc'];
}
$res=mysql_query("SELECT * FROM areas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_localidad[$row['cve']]=$row['nombre'];
}
$plazas17=array(40,39,30);
$array_clientes=array();
$res=mysql_query("SELECT * FROM clientes WHERE plaza='".$_POST['plazausuario']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
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

$abono=0;

if($_POST['cmd']==100){
	echo '<h2>Compras de Plazas del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</h2>';
	echo fechaLocal().' '.horaLocal().'<br>';
	$sumacargo=array();
	$array_engomado = array();
	$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$_POST['localidad']."' AND entrega=1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_engomado[$row['cve']]=$row['nombre'];
		$sumacargo[$row['cve']][0]=0;
		$sumacargo[$row['cve']][1]=0;
		$sumacargo[$row['cve']][2]=0;
	}
	
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.rfc LIKE '%".$_POST['rfc']."%' AND b.localidad_id = '".$_POST['localidad']."' ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	if($_POST['cveusuario']==1) $c++;
	echo '<tr bgcolor="#E9F2F8"><th rowspan="2">Plaza</th>';
	foreach($array_engomado as $v){
		echo '<th colspan="3">'.$v.'</th>';
	}
	echo '</tr>';
	echo '<tr bgcolor="#E9F2F8">';
	foreach($array_engomado as $v){
		echo '<th>Comprados</th><th>Usados</th><th>Existencia</th>';
	}
	echo '</tr>'; 
	
	$x=0;
	foreach($array_plazas as $k=>$v){
		//if($_POST['rfc']=='' || $_POST['rfc']==$array_plazasrfc[$k]){
		//	if($_POST['plaza']=='all' || $k==$_POST['plaza']){
		if(in_array($k,$plazas17))
			$fecha='2015-03-17';
		else
			$fecha='2015-03-01';
				rowb();
				$select= " SELECT a.engomado, COUNT(b.cve), SUM(IF(b.estatus=1,1,0)) FROM compra_certificados a 
				INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra
				WHERE a.plaza='".$k."' AND a.estatus='E' AND a.anio>=4 GROUP BY a.engomado";
				$res=mysql_query($select) or die(mysql_error());
				$array_montoengomados=array();
				while($row=mysql_fetch_array($res)){
					$array_montoengomados[$row[0]][0]=$row[1];
					$row1=mysql_fetch_array(mysql_query("SELECT COUNT(cve) FROM certificados WHERE plaza='".$k."' AND fecha>='$fecha' AND estatus!='C' AND engomado='".$row[0]."'"));
					$row2=mysql_fetch_array(mysql_query("SELECT COUNT(cve) FROM certificados_cancelados WHERE plaza='".$k."' AND fecha>='$fecha' AND estatus!='C' AND engomado='".$row[0]."'"));
					$array_montoengomados[$row[0]][1]=$row1[0]+$row2[0];
					$array_montoengomados[$row[0]][2]=$row[1]-$row1[0]-$row2[0];
				}
				echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
				foreach($array_engomado as $k1=>$v1){
					$res=mysql_query("SELECT minimo,cantidad_restar,existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$k."' AND engomado='$k1' ORDER BY cve DESC");
					$row=mysql_fetch_array($res);
					if(count($array_montoengomados[$k1])>0){
						$array_montoengomados[$k1][0]-=$row[1];
						$array_montoengomados[$k1][0]+=$row[2];
						$array_montoengomados[$k1][2]-=$row[1];
						foreach($array_montoengomados[$k1] as $k2=>$v2){
							if($k2==2 && $row['minimo']>=$v2){
								echo '<td align="right"><font color="RED"><b>'.number_format($v2,0).'</b></font></td>';
							}
							else{
								echo '<td align="right">'.number_format($v2,0).'</td>';
							}
							$sumacargo[$k1][$k2]+=$v2;
						}
					}
					else{
						echo '<td align="right">0</td>';
						echo '<td align="right">0</td>';
						if($row['minimo']>0){
							echo '<td align="right"><font color="RED"><b>0</b></font></td>';
						}
						else{
							echo '<td align="right">0</td>';
						}
					}
				}
				echo '</tr>';
				$x++;
				
		//	}
		//}
	}
	$c=4;
	echo '<tr>';
	echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
	foreach($sumacargo as $valores){
		foreach($valores as $v)
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,0).'</td>';
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
	$sumacargo=array();
	$array_engomado = array();
	$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$_POST['localidad']."' AND entrega=1 ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_engomado[$row['cve']]=$row['nombre'];
		$sumacargo[$row['cve']][0]=0;
		$sumacargo[$row['cve']][1]=0;
		$sumacargo[$row['cve']][2]=0;
	}
	
	$array_plazas=array();
	$res=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus!='I' AND a.cve IN (".$_POST['plaza'].") AND b.rfc LIKE '%".$_POST['rfc']."%' AND b.localidad_id = '".$_POST['localidad']."' ORDER BY a.numero");
	while($row=mysql_fetch_array($res)){
		$array_plazas[$row['cve']]=$row['numero'].' '.$row['nombre'];
	}
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	$c=13;
	if($_POST['cveusuario']==1) $c++;
	echo '<tr bgcolor="#E9F2F8"><th rowspan="2">Plaza</th>';
	foreach($array_engomado as $v){
		echo '<th colspan="3">'.$v.'</th>';
	}
	echo '</tr>';
	echo '<tr bgcolor="#E9F2F8">';
	foreach($array_engomado as $v){
		echo '<th>Comprados</th><th>Usados</th><th>Existencia</th>';
	}
	echo '</tr>'; 
	
	$x=0;
	foreach($array_plazas as $k=>$v){
		//if($_POST['rfc']=='' || $_POST['rfc']==$array_plazasrfc[$k]){
		//	if($_POST['plaza']=='all' || $k==$_POST['plaza']){
		if(in_array($k,$plazas17))
			$fecha='2015-03-17';
		else
			$fecha='2015-03-01';
				rowb();
				$select= " SELECT a.engomado, COUNT(b.cve), SUM(IF(b.estatus=1,1,0)) FROM compra_certificados a 
				INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra
				WHERE a.plaza='".$k."' AND a.estatus='E' AND a.anio>=4  GROUP BY a.engomado";
				$res=mysql_query($select) or die(mysql_error());
				$array_montoengomados=array();
				while($row=mysql_fetch_array($res)){
					$array_montoengomados[$row[0]][0]=$row[1];
					$row1=mysql_fetch_array(mysql_query("SELECT COUNT(cve) FROM certificados WHERE plaza='".$k."' AND fecha>='$fecha' AND estatus!='C' AND engomado='".$row[0]."'"));
					$row2=mysql_fetch_array(mysql_query("SELECT COUNT(cve) FROM certificados_cancelados WHERE plaza='".$k."' AND fecha>='$fecha' AND estatus!='C' AND engomado='".$row[0]."'"));
					$array_montoengomados[$row[0]][1]=$row1[0]+$row2[0];
					$array_montoengomados[$row[0]][2]=$row[1]-$row1[0]-$row2[0];
				}
				echo '<td>'.htmlentities(utf8_encode($v)).'</td>';
				foreach($array_engomado as $k1=>$v1){
					$res=mysql_query("SELECT minimo,cantidad_restar,existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$k."' AND engomado='$k1' ORDER BY cve DESC");
					$row=mysql_fetch_array($res);
					if(count($array_montoengomados[$k1])>0){
						$array_montoengomados[$k1][0]-=$row[1];
						$array_montoengomados[$k1][0]+=$row[2];
						$array_montoengomados[$k1][2]-=$row[1];
						foreach($array_montoengomados[$k1] as $k2=>$v2){
							if($k2==2 && $row['minimo']>=$v2){
								echo '<td align="right"><font color="RED"><b>'.number_format($v2,0).'</b></font></td>';
							}
							else{
								echo '<td align="right">'.number_format($v2,0).'</td>';
							}
							$sumacargo[$k1][$k2]+=$v2;
						}
					}
					else{
						echo '<td align="right">0</td>';
						echo '<td align="right">0</td>';
						if($row['minimo']>0){
							echo '<td align="right"><font color="RED"><b>0</b></font></td>';
						}
						else{
							echo '<td align="right">0</td>';
						}
					}
				}
				echo '</tr>';
				$x++;
				
		//	}
		//}
	}
	$c=4;
	echo '<tr>';
	echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
	foreach($sumacargo as $valores){
		foreach($valores as $v)
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,0).'</td>';
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
				<td><a href="#" onclick="if(document.forma.localidad.value==\'0\'){
					alert(\'Necesita seleccionar la localidad\');
				}
				else{
					buscarRegistros(0,1);
				}"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="if(document.forma.localidad.value==\'0\'){
					alert(\'Necesita seleccionar la localidad\');
				}
				else{';
				/*if($_POST['plazausuario']==0)*/ echo 'document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');';
				echo 'atcr(\'compras_por_plaza.php\',\'_blank\',100,0);
				}"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr style="display:none;"><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="2015-03-01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr style="display:none;"><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		/*if($_POST['plazausuario']>0){
			$Plaza=mysql_fetch_array(mysql_query("SELECT localidad_id FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'"));
			echo '<tr><td>Plaza</td><td>'.$array_plazas[$_POST['plazausuario']].'<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'"><input type="hidden" name="localidad" id="localidad" value="'.$Plaza[0].'"></td></tr>';
		}
		else{*/
			echo '<tr><td align="left">Localidad</td><td><select name="localidad" id="localidad">';
			foreach($array_localidad as $k=>$v){
				echo '<option value="'.$k.'"';
				if($k==1) echo ' selected';
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
		echo '<tr><td>RFC</td><td><input type="text" size="20" name="rfc" id="rfc" class="textField"></td></tr>';
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
			objeto.open("POST","compras_por_plaza.php",true);
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
	/*if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(0,1); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}*/
	echo '
	

	</Script>
';

?>