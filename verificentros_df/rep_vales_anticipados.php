<?php

include("main.php");


/*
$array_depositantes = array();
$res = mysql_query("SELECT * FROM depositantes WHERE plaza='".$_POST['plazausuario']."' AND edo_cuenta=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_depositantes[$row['cve']]=$row['nombre'];
}
*/


$array_depositantes = array();
$res = mysql_query("SELECT * FROM depositantes WHERE plaza='".$_POST['plazausuario']."' AND edo_cuenta=1 AND solo_contado=0 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_depositantes[$row['cve']]=$row['nombre'];
	if($row['estatus']==1)$array_depositantes[$row['cve']].=' (Inactivo)';
}

if($_POST['ajax']==1){

	mysql_query("UPDATE pagos_caja a INNER JOIN vales_pago_anticipado b ON a.plaza = b.plaza AND a.cve = b.pago
			INNER JOIN cobro_engomado c ON c.plaza = a.plaza AND c.tipo_pago = 6 AND c.estatus!='C' AND (c.tipo_venta = 0 OR (c.tipo_venta=2 AND c.tipo_cortesia=2)) AND IF(c.tipo_venta = 0, c.vale_pago_anticipado, c.codigo_cortesia) = b.cve
			SET b.usado = 1 WHERE a.plaza = '".$_POST['plazausuario']."' $filtro AND a.estatus!='C' AND a.tipo_pago=6");
	$filtro = "";
	if($_POST['fecha_ini']!='') $filtro.=" AND a.fecha>='".$_POST['fecha_ini']."'";
	if($_POST['fecha_fin']!='') $filtro.=" AND a.fecha<='".$_POST['fecha_fin']."'";

	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Depositante</th><th>Folio Vale</th><th>Folio Pago</th><th>Fecha Pago</th><th>Tipo Vale</th><th>Folio Venta</th><th>Placa</th><th>Fecha Venta</th></th>';
		
		if($_POST['vale']>0) $filtro .= " AND b.cve=".$_POST['vale'];
		if($_POST['tipo_vale']>0) $filtro .= " AND b.tipo='".($_POST['tipo_vale']-1)."'";
		if($_POST['depositante']!="all") $filtro .= " AND a.depositante='".$_POST['depositante']."'";
		if($_POST['mostrar']==1){ 
			$filtro.= " AND b.usado=0";
			$res = mysql_query("SELECT a.*,b.cve as cvevale, IF(b.tipo=0, 'Normal', 'Cortesia') as tipo_vale FROM pagos_caja a
			INNER JOIN vales_pago_anticipado b ON a.plaza = b.plaza AND a.cve = b.pago
			WHERE a.plaza = '".$_POST['plazausuario']."' $filtro AND a.estatus!='C' AND a.tipo_pago=6
			ORDER BY a.cve") or die(mysql_error());
		}
		elseif($_POST['mostrar']==2){ 
			$filtro.= " AND b.usado=1";
			$res = mysql_query("SELECT a.*,b.cve as cvevale, IF(b.tipo=0, 'Normal', 'Cortesia') as tipo_vale, c.cve as ticket, c.placa,
			c.fecha as fechaticket FROM pagos_caja a
			INNER JOIN vales_pago_anticipado b ON a.plaza = b.plaza AND a.cve = b.pago
			INNER JOIN cobro_engomado c ON c.plaza = a.plaza AND c.tipo_pago = 6 AND c.estatus!='C' AND (c.tipo_venta = 0 OR (c.tipo_venta=2 AND c.tipo_cortesia=2)) AND IF(c.tipo_venta = 0, c.vale_pago_anticipado, c.codigo_cortesia) = b.cve
			WHERE a.plaza = '".$_POST['plazausuario']."' $filtro AND a.estatus!='C' AND a.tipo_pago=6
			ORDER BY a.cve") or die(mysql_error());
		}
		else{
			$res = mysql_query("SELECT a.*,b.cve as cvevale, IF(b.tipo=0, 'Normal', 'Cortesia') as tipo_vale, c.cve as ticket, c.placa,
			c.fecha as fechaticket FROM pagos_caja a INNER JOIN vales_pago_anticipado b ON a.plaza = b.plaza AND a.cve = b.pago
			LEFT JOIN cobro_engomado c ON c.plaza = a.plaza AND c.tipo_pago = 6 AND c.estatus!='C' AND (c.tipo_venta = 0 OR (c.tipo_venta=2 AND c.tipo_cortesia=2)) AND IF(c.tipo_venta = 0, c.vale_pago_anticipado, c.codigo_cortesia) = b.cve
			WHERE a.plaza = '".$_POST['plazausuario']."' $filtro AND a.estatus!='C' AND a.tipo_pago=6
			ORDER BY a.cve") or die(mysql_error());
		}
		while($row = mysql_fetch_array($res)){
			
			echo '<tr>';
			echo '<td>'.$array_depositantes[$row['depositante']].'</td>';
			echo '<td align="center">'.$row['cvevale'].'</td>';
			echo '<td align="center">'.$row['cve'].'</td>';
			echo '<td align="center">'.$row['fecha'].'</td>';
			echo '<td align="center">'.$row['tipo_vale'].'</td>';
			echo '<td align="center">'.$row['ticket'].'</td>';
			echo '<td align="left">'.$row['placa'].'</td>';
			echo '<td align="center">'.$row['fechaticket'].'</td>';
			echo '</tr>';
		}
	echo '</table>';
	exit();
}


if($_POST['ajax']==1.1){
	$filtro = "";
	if($_POST['fecha_ini']!='') $filtro.=" AND a.fecha>='".$_POST['fecha_ini']."'";
	if($_POST['fecha_fin']!='') $filtro.=" AND a.fecha<='".$_POST['fecha_fin']."'";

	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Depositante</th><th>Folio Vale</th><th>Folio Pago</th><th>Fecha Pago</th><th>Folio Venta</th><th>Placa</th><th>Fecha Venta</th></th>';
	if($_POST['tipo_vale']==0){
		if($_POST['vale']>0) $filtro .= " AND a.vale_ini<=".$_POST['vale']." AND a.vale_fin>=".$_POST['vale'];
		$res = mysql_query("SELECT a.* FROM pagos_caja a LEFT JOIN vales_pago_anticipado b ON a.plaza = b.plaza AND a.cve = b.pago
			WHERE a.plaza = '".$_POST['plazausuario']."' $filtro AND a.vale_ini>0 AND a.vale_fin>0 AND a.estatus!='C' AND a.tipo_pago=6
			AND ISNULL(b.cve) ORDER BY a.cve");
		while($row = mysql_fetch_array($res)){
			for($i=$row['vale_ini'];$i<=$row['vale_fin'];$i++){
				if($_POST['vale']<=0 || $_POST['vale']==$i){
					$res1 = mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND tipo_pago=6 AND 
						depositante='".$row['depositante']."' AND tipo_vale!=2 AND fecha>='".$row['fecha']."' AND vale_pago_anticipado = '".$i."'");
					$rows = mysql_num_rows($res1);
					if($rows==0) $rows=1;
					for($j=0;$j<$rows;$j++){
						$row1=mysql_fetch_array($res1);
						if(($_POST['mostrar'] == 0) || ($_POST['mostrar'] == 1 && $rows == 1 && $row1['cve'] == 0) || ($_POST['mostrar'] == 2 && $row1['cve'] > 0)){
							echo '<tr>';
							if($j==0){
								echo '<td rowspan="'.$rows.'">'.$array_depositantes[$row['depositante']].'</td>';
								echo '<td rowspan="'.$rows.'" align="center">'.$i.'</td>';

								echo '<td rowspan="'.$rows.'" align="center">'.$row['placa'].'</td>';
								echo '<td rowspan="'.$rows.'" align="center">'.$row['fecha'].'</td>';
							}
							echo '<td align="center">'.$row1['cve'].'</td>';
							echo '<td align="left">'.$row1['placa'].'</td>';
							echo '<td align="center">'.$row1['fecha'].'</td>';
							echo '</tr>';
						}
					}
				}
			}
		}
	}
	else{
		if($_POST['vale']>0) $filtro .= " AND b.cve=".$_POST['vale'];
		$res = mysql_query("SELECT a.*,b.cve as cvevale FROM pagos_caja a INNER JOIN vales_pago_anticipado b ON a.plaza = b.plaza AND a.cve = b.pago
			WHERE a.plaza = '".$_POST['plazausuario']."' $filtro AND a.estatus!='C' AND a.tipo_pago=6
			ORDER BY a.cve") or die(mysql_error());
		while($row = mysql_fetch_array($res)){
			$res1 = mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND tipo_pago=6 AND 
				depositante='".$row['depositante']."' AND tipo_vale=2 AND fecha>='".$row['fecha']."' AND vale_pago_anticipado = '".$row['cvevale']."'");
			$rows = mysql_num_rows($res1);
			if($rows==0) $rows=1;
			for($j=0;$j<$rows;$j++){
				$row1=mysql_fetch_array($res1);
				if(($_POST['mostrar'] == 0) || ($_POST['mostrar'] == 1 && $rows == 1 && $row1['cve'] == 0) || ($_POST['mostrar'] == 2 && $row1['cve'] > 0)){
					echo '<tr>';
					if($j==0){
						echo '<td rowspan="'.$rows.'">'.$array_depositantes[$row['depositante']].'</td>';
						echo '<td rowspan="'.$rows.'" align="center">'.$row['cvevale'].'</td>';
						echo '<td rowspan="'.$rows.'" align="center">'.$row['cve'].'</td>';
						echo '<td rowspan="'.$rows.'" align="center">'.$row['fecha'].'</td>';
					}
					echo '<td align="center">'.$row1['cve'].'</td>';
					echo '<td align="left">'.$row1['placa'].'</td>';
					echo '<td align="center">'.$row1['fecha'].'</td>';
					echo '</tr>';
				}
			}
		}
	}
	echo '</table>';
	exit();
}
if($_POST['cmd']==101){
	require_once('dompdf/dompdf_config.inc.php');
		$html='<html><head>
      <style type="text/css">
	                    top  lado      ladoiz
		 @page{ margin: 5in 0.5in 1px 0.5in;}
		</style>
		 </head><body>';

	$filtro = "";
	if($_POST['fecha_ini']!='') $filtro.=" AND a.fecha>='".$_POST['fecha_ini']."'";
	if($_POST['fecha_fin']!='') $filtro.=" AND a.fecha<='".$_POST['fecha_fin']."'";

	$html.= '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="" style="font-size:26px">';
	$html.= '<tr bgcolo="#E9F2F8"><th>Depositante</th><th>Folio Vale</th><th>Folio Pago</th><th>Fecha Pago</th><th>Folio Venta</th><th>Placa</th><th>Fecha Venta</th></th>';
	if($_POST['tipo_vale']==0){
		if($_POST['vale']>0) $filtro .= " AND a.vale_ini<=".$_POST['vale']." AND a.vale_fin>=".$_POST['vale'];
		$res = mysql_query("SELECT a.* FROM pagos_caja a LEFT JOIN vales_pago_anticipado b ON a.plaza = b.plaza AND a.cve = b.pago
			WHERE a.plaza = '".$_POST['plazausuario']."' $filtro AND a.vale_ini>0 AND a.vale_fin>0 AND a.estatus!='C' AND a.tipo_pago=6
			AND ISNULL(b.cve) ORDER BY a.cve");
		while($row = mysql_fetch_array($res)){
			for($i=$row['vale_ini'];$i<=$row['vale_fin'];$i++){
				if($_POST['vale']<=0 || $_POST['vale']==$i){
					$res1 = mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND tipo_pago=6 AND 
						depositante='".$row['depositante']."' AND tipo_vale!=2 AND fecha>='".$row['fecha']."' AND vale_pago_anticipado = '".$i."'");
					$rows = mysql_num_rows($res1);
					if($rows==0) $rows=1;
					for($j=0;$j<$rows;$j++){
						$row1=mysql_fetch_array($res1);
						if(($_POST['mostrar'] == 0) || ($_POST['mostrar'] == 1 && $rows == 1 && $row1['cve'] == 0) || ($_POST['mostrar'] == 2 && $row1['cve'] > 0)){
							$html.= '<tr>';
							if($j==0){
								$html.= '<td rowspan="'.$rows.'">'.$array_depositantes[$row['depositante']].'</td>';
								$html.= '<td rowspan="'.$rows.'" align="center">'.$i.'</td>';

								$html.= '<td rowspan="'.$rows.'" align="center">'.$row['placa'].'</td>';
								$html.= '<td rowspan="'.$rows.'" align="center">'.$row['fecha'].'</td>';
							}
							$html.= '<td align="center">'.$row1['cve'].'</td>';
							$html.= '<td align="left">'.$row1['placa'].'</td>';
							$html.= '<td align="center">'.$row1['fecha'].'</td>';
							$html.= '</tr>';
						}
					}
				}
			}
		}
	}
	else{
		if($_POST['vale']>0) $filtro .= " AND b.cve=".$_POST['vale'];
		$res = mysql_query("SELECT a.*,b.cve as cvevale FROM pagos_caja a INNER JOIN vales_pago_anticipado b ON a.plaza = b.plaza AND a.cve = b.pago
			WHERE a.plaza = '".$_POST['plazausuario']."' $filtro AND a.estatus!='C' AND a.tipo_pago=6
			ORDER BY a.cve") or die(mysql_error());
		while($row = mysql_fetch_array($res)){
			$res1 = mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND tipo_pago=6 AND 
				depositante='".$row['depositante']."' AND tipo_vale=2 AND fecha>='".$row['fecha']."' AND vale_pago_anticipado = '".$row['cvevale']."'");
			$rows = mysql_num_rows($res1);
			if($rows==0) $rows=1;
			for($j=0;$j<$rows;$j++){
				$row1=mysql_fetch_array($res1);
				if(($_POST['mostrar'] == 0) || ($_POST['mostrar'] == 1 && $rows == 1 && $row1['cve'] == 0) || ($_POST['mostrar'] == 2 && $row1['cve'] > 0)){
					$html.= '<tr>';
					if($j==0){
						$html.= '<td rowspan="'.$rows.'">'.$array_depositantes[$row['depositante']].'</td>';
						$html.= '<td rowspan="'.$rows.'" align="center">'.$row['cvevale'].'</td>';
						$html.= '<td rowspan="'.$rows.'" align="center">'.$row['cve'].'</td>';
						$html.= '<td rowspan="'.$rows.'" align="center">'.$row['fecha'].'</td>';
					}
					$html.= '<td align="center">'.$row1['cve'].'</td>';
					$html.= '<td align="left">'.$row1['placa'].'</td>';
					$html.= '<td align="center">'.$row1['fecha'].'</td>';
					$html.= '</tr>';
				}
			}
		}
	}
	$html.= '</table></body></html>';
		 	$mipdf= new DOMPDF();
//	$mipdf->margin: "0";
	//$mipdf->set_paper("A4", "portrait");
	$mipdf->set_paper("A4", "portrait");
    
//    $mipdf->set_margin("Legal", "landscape");
//	$mipdf->set_paper("Legal", "landscape");
	$mipdf->load_html($html);
	$mipdf->render();
	$mipdf ->stream();
	exit();
}


top($_SESSION);


if ($_POST['cmd']<1) {
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td></td>';
	echo '
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr ><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr ><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Tipo Vale</td><td><select name="tipo_vale" id="tipo_vale"><option value="0" selected>Todos</option><option value="1">Normal</option><option value="2">Cortesia</option>';
	echo '</select></td></tr>';
	echo '<tr><td>Depositante</td><td><select name="depositante" id="depositante"><option value="all">---Todos---</option>';
		foreach($array_depositantes as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
	echo '<tr ><td>Folio Vale</td><td><input type="text" name="vale" id="vale" class="textField" size="12" value=""></td></tr>';
	echo '<tr><td>Mostrar</td><td><select name="mostrar" id="mostrar"><option value="0">Todos</option>
	<option value="1" selected>No usados</option><option value="2">Usados</option></select></td></tr>';
	echo '</table>';
	echo '<br>';
	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","rep_vales_anticipados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&mostrar="+document.getElementById("mostrar").value+"&vale="+document.getElementById("vale").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&tipo_vale="+document.getElementById("tipo_vale").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&depositante="+document.getElementById("depositante").value);
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
	//buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.

	
	</Script>
	';

	
}

bottom();
?>