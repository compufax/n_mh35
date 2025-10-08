<?php
include ("main.php"); 
$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$res=mysql_query("SELECT local, validar_certificado FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];
$ValidarCertificados = $row[1];

$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND entrega=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio_compra'];
}

$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

if($_POST['cmd']==1)
	$res = mysql_query("SELECT * FROM anios_certificados  ORDER BY nombre DESC LIMIT 2");
else
	$res = mysql_query("SELECT * FROM anios_certificados  ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
	$array_fechainianio[$row['cve']]=$row['fecha_ini'];
}


$array_estatus = array('A'=>'Activo','C'=>'Cancelado','E'=>'Confirmado');

if($_POST['ajax']==1){

$select= " SELECT * FROM compra_certificados 
		WHERE plaza='".$_POST['plazausuario']."'";
		if($_POST['fecha_ini'] != '') $select.=" AND fecha_compra >= '".$_POST['fecha_ini']."'";
		if($_POST['fecha_fin'] != '')$select .=" AND fecha_compra <= '".$_POST['fecha_fin']."'";
		if ($_POST['usuario']!="") { $select.=" AND usuario='".$_POST['usuario']."' "; }
		if ($_POST['engomado']!="") { $select.=" AND engomado='".$_POST['engomado']."' "; }
		if ($_POST['estatus']!="") { $select.=" AND estatus='".$_POST['estatus']."' "; }
		if ($_POST['anio']!="") { $select.=" AND anio='".$_POST['anio']."' "; }
		$select.=" ORDER BY fecha_compra DESC,cve DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);

	if($_POST['anio'] >= 4){
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">
			<tr bgcolor="#E9F2F8"><th>Tipo de Certificado</th><th>Almacen</th><th>Compras</th><th>Total</th>';
			if($_POST['cveusuario'] >= 1){
				echo '<th>Consumidos</th><th>Remanente</th>';
			}
			echo '</tr>';
			$res = mysql_query("SELECT a.engomado,COUNT(b.cve) as compras FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra AND b.tipo!=1 WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio='".$_POST['anio']."' AND a.estatus!='C' GROUP BY a.engomado");
			$array_compras = array();
			while($row = mysql_fetch_array($res)){
				$array_compras[$row[0]] = $row[1];
			}
			foreach($array_engomado as $k=>$v){
				if($k==19 || $k==3){
					if($_POST['anio']==4){
						$res = mysql_query("SELECT existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$_POST['plazausuario']."' AND engomado = '$k' ORDER BY cve DESC LIMIT 1");
						$row = mysql_fetch_array($res);
						$almacen = $row[0];
					}
					else{
						$res = mysql_query("SELECT existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$_POST['plazausuario']."' AND engomado = '$k' ORDER BY cve DESC LIMIT 1");
						$row = mysql_fetch_array($res);
						$almacen = $row[0];
						$res = mysql_query("SELECT SUM(foliofin+1-folioini) FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='$k' AND anio>=4 AND fecha>='2016-01-01' AND fecha<'".$array_fechainianio[$_POST['anio']]."' AND estatus!='C'");
						$row = mysql_fetch_array($res);
						$almacen += $row[0];
						$res = mysql_query("SELECT COUNT(cve) FROM certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='$k' AND fecha>='2016-01-01' AND anio>=4 AND fecha<'".$array_fechainianio[$_POST['anio']]."' AND estatus!='C'");
						$row = mysql_fetch_array($res);
						$almacen -= $row[0];
						$res = mysql_query("SELECT COUNT(cve) FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND engomado='$k' AND fecha>='2016-01-01' AND anio>=4 AND fecha<'".$array_fechainianio[$_POST['anio']]."' AND estatus!='C'");
						$row = mysql_fetch_array($res);
						$almacen -= $row[0];
					}
					if($_POST['cveusuario'] >= 1){
						$mes = substr($array_fechainianio[$_POST['anio']],5,2);
						if(intval($mes)<=6){
							$fini = substr($array_fechainianio[$_POST['anio']],0,4).'-01-01';
							$ffin = substr($array_fechainianio[$_POST['anio']],0,4).'-06-30';
						}
						else{
							$fini = substr($array_fechainianio[$_POST['anio']],0,4).'-07-01';
							$ffin = substr($array_fechainianio[$_POST['anio']],0,4).'-12-01';
						}
						$res1=mysql_query("SELECT count(a.cve)
						FROM certificados a 
						INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
						INNER JOIN anios_certificados c ON c.cve = a.anio
						WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.anio='".$_POST['anio']."' AND a.engomado='$k' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) BETWEEN '".$fini."' AND '".$ffin."'");
						$row1 = mysql_fetch_array($res1);
						$res2=mysql_query("SELECT count(a.cve)
						FROM certificados_cancelados a 
						INNER JOIN anios_certificados c ON c.cve = a.anio
						WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.anio='".$_POST['anio']."' AND a.engomado='$k' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) BETWEEN '".$fini."' AND '".$ffin."'");
						$row2 = mysql_fetch_array($res2);
					}
				}
				else{
					$almacen = 0;
					if($_POST['cveusuario'] >= 1){
						$res1=mysql_query("SELECT count(a.cve)
						FROM certificados a 
						INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
						INNER JOIN anios_certificados c ON c.cve = a.anio
						WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.anio='".$_POST['anio']."' AND a.engomado='$k' AND a.estatus!='C'");
						$row1 = mysql_fetch_array($res1);
						$res2=mysql_query("SELECT count(a.cve)
						FROM certificados_cancelados a 
						INNER JOIN anios_certificados c ON c.cve = a.anio
						WHERE a.plaza='".$_POST['plazausuario']."' AND a.anio>=4 AND a.anio='".$_POST['anio']."' AND a.engomado='$k' AND a.estatus!='C'");
						$row2 = mysql_fetch_array($res2);
					}
				}
				rowb();
				echo '<!--<tr>--><td>'.$v.'</td><td align="right">'.$almacen.'</td><td align="right">'.$array_compras[$k].'</td><td align="right">'.($almacen+$array_compras[$k]).'</td>';
				if($_POST['cveusuario'] >= 1){
					echo '<td align="right">'.($row1[0]+$row2[0]).'</td>';
					echo '<td align="right">'.(($almacen+$array_compras[$k])-($row1['0']+$row2[0])).'</td>';
				}
				echo '</tr>';
			}
			echo '</table>';
		}else {
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


if ($_POST['cmd']<1) {
	
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	//echo '<td><a href="#" onClick="atcr(\'detalle_compras_certificados.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
	//echo'<td><a href="#" onClick="atcr(\'\',\'_blank\',\'100\',\'0\')"><img src="images/b_print.png" border="0" title="Imprimir"></a>Imprimir&nbsp;</td>';
	if($_POST['cveusuario']==1){
		if($ValidarCertificados==1)
			echo '<td><input type="checkbox" checked onClick="atcr(\'detalle_compras_certificados.php\',\'\',13,0)">Validacion de Certificados</td></tr>';
		else
			echo '<td><input type="checkbox" onClick="atcr(\'detalle_compras_certificados.php\',\'\',12,0)">Validacion de Certificados</td></tr>';
	}
	echo '
		 </tr>';
	echo '</table>';
	echo '<table width="100%"><tr><td width="50%">';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="" >&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="" >&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>A&ntilde;o</td><td><select name="anio" id="anio"><option value="">Todos</option>';
	$primero = true;
	foreach($array_anios as $k=>$v){
		echo '<option value="'.$k.'"';
		if($primero) echo ' selected';
		echo '>'.$v.'</option>';
		$primero = false;
	}
	echo '</select></td></tr>';
/*	echo '<tr><td>Tipo de Certificado</td><td><select name="engomado" id="engomado"><option value="">Todos</option>';
	
	foreach($array_engomado as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">Todos</option>';
	$res=mysql_query("SELECT b.cve,b.usuario FROM compra_certificados a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY b.usuario");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['usuario'].'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="">Todos</option>';
	foreach($array_estatus as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Mostrar</td><td><select name="mostrar" id="mostrar"><option value="0">Todos</option>
	<option value="1" selected>Con pendientes de entrega</option>
	<option value="2">Sin pendientes de entrega</option></select></td></tr>';*/
	echo '</table>';
	echo '</td><td id="concentrado"></td></tr></table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
//objeto.send("ajax=1&btn="+btn+"&mostrar="+document.getElementById("mostrar").value+"&anio="+document.getElementById("anio").value+"&estatus="+document.getElementById("estatus").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&engomado="+document.getElementById("engomado").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
echo '
<Script language="javascript">

	function buscarRegistros(btn)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","detalle_compras_certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&btn="+btn+"&anio="+document.getElementById("anio").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					document.getElementById("Resultados").innerHTML = objeto.responseText;

				}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}
	buscarRegistros(0); //Realizar consulta de todos los registros al iniciar la forma.
		
	
	</Script>
	';

	
}

bottom();

?>