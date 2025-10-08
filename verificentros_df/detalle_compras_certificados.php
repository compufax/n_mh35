<?php
include ("main.php"); 
$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plaza']."'");
$Plaza=mysql_fetch_array($res);

$res=mysql_query("SELECT local, validar_certificado FROM plazas WHERE cve='".$_POST['plaza']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];
$ValidarCertificados = $row[1];

$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE  entrega=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio_compra'];
}

$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$res = mysql_query("SELECT * FROM plazas ORDER BY numero,nombre");
while($row=mysql_fetch_array($res)){
	$array_plaza[$row['cve']]=$row['numero'].' '.$row['nombre'];
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

	

	if($_POST['anio'] >= 4){
		$filtroc="";
		$filtro="";
		$filtrob="";
		if($_POST['fecha_ini']!=''){
			$filtroc.=" AND a.fecha_compra>='".$_POST['fecha_ini']."'";
			$filtro.=" AND a.fecha>='".$_POST['fecha_ini']."'";
			$filtrob.=" AND b.fecha>='".$_POST['fecha_ini']."'";
		}
		if($_POST['fecha_fin']!=''){
			$filtroc.=" AND a.fecha_compra<='".$_POST['fecha_fin']."'";
			$filtro.=" AND a.fecha<='".$_POST['fecha_fin']."'";
			$filtrob.=" AND b.fecha<='".$_POST['fecha_fin']."'";
		}
		if($_POST['anio']>0){
			$filtroc.=" AND a.anio='".$_POST['anio']."'";
			$filtro.=" AND a.anio='".$_POST['anio']."'";
			$filtrob.=" AND b.anio='".$_POST['anio']."'";
		}
		$res = mysql_query("SELECT * FROM anios_certificados WHERE cve='".$_POST['anio']."'");
		if($row = mysql_fetch_array($res)){
			$fechaini=$row['fecha_ini'];
			$fechafin=$row['fecha_fin'];
		}
		else
		{
			$fechaini=$_POST['fecha_ini'];
			$fechafin=$_POST['fecha_fin'];
			$array_fechainianio[0]=$_POST['fecha_ini'];
		}
		if($fechafin>date('Y-m-d')) $fechafin=date('Y-m-d');
		$res=mysql_query("SELECT WEEK('".$fechaini."'),WEEK('".$fechafin."')");
		$row=mysql_fetch_array($res);
		$numerosemanas=$row[1]+1-$row[0];
		if($_POST['plaza']>0){
			$resPlaza = mysql_query("SELECT * FROM plazas WHERE cve='".$_POST['plaza']."' ORDER BY numero,nombre");
		}
		else{
			$resPlaza = mysql_query("SELECT a.* FROM plazas a LEFT JOIN certificados b ON a.cve = b.plaza AND b.engomado=2 AND b.estatus!='C' $filtrob GROUP BY a.cve ORDER BY COUNT(b.cve) DESC,a.numero,a.nombre") or die(mysql_error());
		}
		while($Plaza = mysql_fetch_array($resPlaza)){
			$_POST['plaza']=$Plaza['cve'];
			
			$res = mysql_query("SELECT SUM(foliofin+1-folioini),MAX(fecha_compra),MIN(fecha_compra),WEEK(fecha_compra) FROM 
				compra_certificados a WHERE plaza='".$_POST['plaza']."' AND engomado=2 AND estatus!='C' $filtroc
				GROUP BY WEEK(fecha_compra)");
			$semanamax=-1;
			$semanamin=-1;
			$maximo=-1;
			$minimo=-1;
			$diainimax='';
			$diafinmax='';
			$diainimin='';
			$diafinmin='';
			while($row = mysql_fetch_array($res)){
				if($maximo==-1 || $maximo<$row[0]){
					$semanamax=$row[3];
					$maximo=$row[0];
					$diainimax=$row[2];
					$diafinmax=$row[1];
				}
				if($minimo==-1 || $minimo>$row[0]){
					$semanamin=$row[3];
					$minimo=$row[0];
					$diainimin=$row[2];
					$diafinmin=$row[1];
				}
			}

			$res = mysql_query("SELECT COUNT(cve),MAX(fecha),MIN(fecha),WEEK(fecha) FROM 
				certificados a WHERE plaza='".$_POST['plaza']."' AND engomado=2 $filtro AND estatus!='C' 
				GROUP BY WEEK(fecha)");
			$semanamax2=-1;
			$semanamin2=-1;
			$maximo2=-1;
			$minimo2=-1;
			$diainimax2='';
			$diafinmax2='';
			$diainimin2='';
			$diafinmin2='';
			while($row = mysql_fetch_array($res)){
				if($maximo2==-1 || $maximo2<$row[0]){
					$semanamax2=$row[3];
					$maximo2=$row[0];
					$diainimax2=$row[2];
					$diafinmax2=$row[1];
				}
				if($minimo2==-1 || $minimo2>$row[0]){
					$semanamin2=$row[3];
					$minimo2=$row[0];
					$diainimin2=$row[2];
					$diafinmin2=$row[1];
				}
			}
			echo '<h2>'.$array_plaza[$_POST['plaza']].'</h2>';
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">
			<tr bgcolor="#E9F2F8"><th>Tipo de Certificado</th><th>Almacen</th><th>Compras</th><th>Total</th>';
			if($_POST['cveusuario'] >= 1){
				echo '<th>Consumidos</th><th>Remanente</th>';
			}
			if($_POST['cveusuario']>=1){
				echo '<th>Semana Minimo Compra<br># '.$semanamin.'<br>'.$diainimin.'<br>'.$diafinmin.'</th>
				<th>Semana Maximo Compra<br># '.$semanamax.'<br>'.$diainimax.'<br>'.$diafinmax.'</th>
				<th>Promedio por Semana Compra</th>';
				echo '<th>Semana Minimo Entrega<br># '.$semanamin2.'<br>'.$diainimin2.'<br>'.$diafinmin2.'</th>
				<th>Semana Maximo Entrega<br># '.$semanamax2.'<br>'.$diainimax2.'<br>'.$diafinmax2.'</th>
				<th>Promedio por Semana Entrega</th>';
			}
			echo '</tr>';
			$array_comprasmax=array();
			$array_comprasmin=array();
			$res = mysql_query("SELECT a.engomado,COUNT(b.cve) as compras,a.cve,WEEK(a.fecha_compra) FROM compra_certificados a INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra AND b.tipo!=1 WHERE a.plaza='".$_POST['plaza']."' AND a.estatus!='C' $filtroc GROUP BY a.cve") or die(mysql_error());
			$array_compras = array();
			while($row = mysql_fetch_array($res)){
				$array_compras[$row[0]] += $row[1];
				if($row[3]==$semanamin)
					$array_comprasmin[$row[0]] += $row[1];
				if($row[3]==$semanamax)
					$array_comprasmax[$row[0]] += $row[1];
			}
			$array_entregasmax=array();
			$array_entregasmin=array();
			$array_entregas = array();
			$res = mysql_query("SELECT engomado,COUNT(cve),WEEK(fecha) FROM certificados a WHERE plaza='".$_POST['plaza']."' $filtro AND estatus!='C' GROUP BY engomado,WEEK(fecha)") or die(mysql_error());
			while($row = mysql_fetch_array($res)){
				$array_entregas[$row[0]] += $row[1];
				if($row[2]==$semanamin2)
					$array_entregasmin[$row[0]] += $row[1];
				if($row[2]==$semanamax2)
					$array_entregasmax[$row[0]] += $row[1];
			}
			foreach($array_engomado as $k=>$v){
				if($k==19 || $k==3){
					if($_POST['anio']==4){
						$res = mysql_query("SELECT existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$_POST['plaza']."' AND engomado = '$k' ORDER BY cve DESC LIMIT 1");
						$row = mysql_fetch_array($res);
						$almacen = $row[0];
					}
					else{
						$res = mysql_query("SELECT existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$_POST['plaza']."' AND engomado = '$k' ORDER BY cve DESC LIMIT 1");
						$row = mysql_fetch_array($res);
						$almacen = $row[0];
						$res = mysql_query("SELECT SUM(foliofin+1-folioini) FROM compra_certificados WHERE plaza='".$_POST['plaza']."' AND engomado='$k' AND anio>=4 AND fecha>='2016-01-01' AND fecha<'".$array_fechainianio[$_POST['anio']]."' AND estatus!='C'");
						$row = mysql_fetch_array($res);
						$almacen += $row[0];
						$res = mysql_query("SELECT COUNT(cve) FROM certificados WHERE plaza='".$_POST['plaza']."' AND engomado='$k' AND fecha>='2016-01-01' AND anio>=4 AND fecha<'".$array_fechainianio[$_POST['anio']]."' AND estatus!='C'");
						$row = mysql_fetch_array($res);
						$almacen -= $row[0];
						$res = mysql_query("SELECT COUNT(cve) FROM certificados_cancelados WHERE plaza='".$_POST['plaza']."' AND engomado='$k' AND fecha>='2016-01-01' AND anio>=4 AND fecha<'".$array_fechainianio[$_POST['anio']]."' AND estatus!='C'");
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
						if($_POST['fecha_ini']!='') $fini=$_POST['fecha_ini'];
						if($_POST['fecha_fin']!='') $ffin=$_POST['fecha_fin'];
						$res1=mysql_query("SELECT count(a.cve)
						FROM certificados a 
						INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket
						INNER JOIN anios_certificados c ON c.cve = a.anio
						WHERE a.plaza='".$_POST['plaza']."' AND a.anio>=4 AND a.engomado='$k' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) BETWEEN '".$fini."' AND '".$ffin."' $filtro");
						$row1 = mysql_fetch_array($res1);
						$res2=mysql_query("SELECT count(a.cve)
						FROM certificados_cancelados a 
						INNER JOIN anios_certificados c ON c.cve = a.anio
						WHERE a.plaza='".$_POST['plaza']."' AND a.anio>=4 AND a.engomado='$k' AND a.estatus!='C' AND IF(a.fecha<c.fecha_ini,c.fecha_ini,a.fecha) BETWEEN '".$fini."' AND '".$ffin."' $filtro");
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
						WHERE a.plaza='".$_POST['plaza']."' AND a.anio>=4 AND a.engomado='$k' AND a.estatus!='C' $filtro");
						$row1 = mysql_fetch_array($res1);
						$res2=mysql_query("SELECT count(a.cve)
						FROM certificados_cancelados a 
						INNER JOIN anios_certificados c ON c.cve = a.anio
						WHERE a.plaza='".$_POST['plaza']."' AND a.anio>=4 AND a.engomado='$k' AND a.estatus!='C' $filtro");
						$row2 = mysql_fetch_array($res2);
					}
				}
				rowb();
				echo '<td>'.$v.'</td>
				<td align="right">'.$almacen.'</td>
				<td align="right">'.$array_compras[$k].'</td>
				<td align="right">'.($almacen+$array_compras[$k]).'</td>';
				if($_POST['cveusuario'] >= 1){
					echo '<td align="right">'.($row1[0]+$row2[0]).'</td>';
					echo '<td align="right">'.(($almacen+$array_compras[$k])-($row1['0']+$row2[0])).'</td>';
				}
				if($_POST['cveusuario']>=1){
					echo '<td align="right">'.number_format($array_comprasmin[$k],0).'</td>';
					echo '<td align="right">'.number_format($array_comprasmax[$k],0).'</td>';
					echo '<td align="right">'.number_format($array_compras[$k]/$numerosemanas,0).'</td>';
					echo '<td align="right">'.number_format($array_entregasmin[$k],0).'</td>';
					echo '<td align="right">'.number_format($array_entregasmax[$k],0).'</td>';
					echo '<td align="right">'.number_format($array_entregas[$k]/$numerosemanas,0).'</td>';
				}
				echo '</tr>';
			}
			echo '</table>';
		}
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
	/*if($_POST['cveusuario']==1){
		if($ValidarCertificados==1)
			echo '<td><input type="checkbox" checked onClick="atcr(\'detalle_compras_certificados.php\',\'\',13,0)">Validacion de Certificados</td></tr>';
		else
			echo '<td><input type="checkbox" onClick="atcr(\'detalle_compras_certificados.php\',\'\',12,0)">Validacion de Certificados</td></tr>';
	}*/
	echo '
		 </tr>';
	echo '</table>';
	echo '<table width="100%"><tr><td width="50%">';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="" >&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="" >&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>A&ntilde;o</td><td><select name="anio" id="anio"><option value="0">Todos</option>';
	$primero = true;
	foreach($array_anios as $k=>$v){
		echo '<option value="'.$k.'"';
		if($primero) echo ' selected';
		echo '>'.$v.'</option>';
		$primero = false;
	}
	echo '</select></td></tr>';
	echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza"><option value="0">Todos</option>';
	
	foreach($array_plaza as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
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
			objeto.send("ajax=1&btn="+btn+"&anio="+document.getElementById("anio").value+"&plaza="+document.getElementById("plaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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