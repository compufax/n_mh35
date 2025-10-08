<?php 

include ("main.php"); 


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM depositantes WHERE tipo_depositante = 0 ";
		if($_POST['plaza']!="all") $select .= " AND plaza='".$_POST['plaza']."'";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		if ($_POST['agencia']!="all") { $select.=" AND agencia='".$_POST['agencia']."' "; }
		$select.=" ORDER BY nombre";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8">';
			echo '<th>Nombre</th><th>Pagos Anticipados Importe</th><th>Pagos Anticipados Cantidad</th><th>Cantidad de Cortesias</th><th>Cortesias Usadas</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$totales = array();
			$precio = mysql_fetch_array(mysql_query("SELECT precio FROM engomados WHERE localidad=1 ORDER BY precio DESC LIMIT 1"));
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td>'.utf8_encode($row['nombre']).'</td>';
				$res1 = mysql_query("SELECT SUM(monto),SUM(verificaciones) FROM pagos_caja WHERE plaza='{$row['plaza']}' AND depositante='{$row['cve']}' AND estatus!='C' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'");
				$row1 = mysql_fetch_array($res1);
				echo '<td align="right">'.number_format($row1[0],2).'</td>';
				$certificados = round($row1[1],2);
				echo '<td align="right">'.number_format($certificados,2).'</td>';
				$cortesias=intval($row1[1]/10);
				echo '<td align="right">'.number_format($cortesias,0).'</td>';
				$res2 = mysql_query("SELECT COUNT(cve) FROM cobro_engomado WHERE plaza='{$row['plaza']}' AND depositante='{$row['cve']}' AND estatus!='C' AND tipo_venta='2' AND tipo_cortesia!=1 AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'");
				$row2 = mysql_fetch_array($res2);
				echo '<td align="right">'.number_format($row2[0],0).'</td>';
				echo '</tr>';
				$totales[0]+=$row1[0];
				$totales[1]+=round($row1[1],2);
				$totales[2]+=intval($row1[1]/10);
				$totales[3]+=$row2[0];
			}
			echo '	
				<tr>
				<td colspan="1" bgcolor="#E9F2F8">';menunavegacion();echo '</td>';
			foreach($totales as $k=>$v)
			{
				if($k<=1)
					echo '<td align="right" bgcolor="#E9F2F8">'.number_format($v,2).'</td>';
				else
					echo '<td align="right" bgcolor="#E9F2F8">'.number_format($v,0).'</td>';
			}
			echo '
				</tr>
			</table>';

			echo '<h2>Cortesias Autorizadas</h2>';
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8">';
			$totales = array();
			echo '<th>Mes</th><th>Cortesias</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$res = mysql_query("SELECT LEFT(fecha,7) as mes, COUNT(cve) FROM cobro_engomado WHERE plaza='{$_POST['plazausuario']}' AND tipo_pago = 1 AND estatus!='C' AND tipo_venta='2' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' GROUP BY LEFT(fecha,7)");
			while($row=mysql_fetch_array($res)) {
				rowb();
				$mes = explode('-', $row[0]);
				echo '<td>'.$array_meses[intval($mes[1])].' '.$mes[0].'</td>';
				echo '<td align="right"><a href="#" onClick="atcr(\'depositantes_cortesias.php\',\'\',1,\''.$row[0].'\');">'.number_format($row[1],0).'</td>';
				echo '</tr>';
				$totales[0]+=$row[1];
			}
			echo '	
				<tr>
				<td colspan="1" bgcolor="#E9F2F8">';menunavegacion();echo '</td>';
			foreach($totales as $k=>$v)
			{
				echo '<td align="right" bgcolor="#E9F2F8">'.number_format($v,0).'</td>';
			}
			echo '
				</tr>
			</table>';
			

		exit();	
}	


top($_SESSION);


if($_POST['cmd']==1)
{
$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio'];
}

$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

	$res = mysql_query("SELECT * FROM anios_certificados  ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
}

$res = mysql_query("SELECT * FROM tipo_combustible ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_combustible[$row['cve']]=$row['nombre'];
}

$array_tipo_pago = array();
$res = mysql_query("SELECT * FROM tipos_pago WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}

	echo '<table>';
	echo '
		<tr>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'depositantes_cortesias.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	$mes = explode('-', $_POST['reg']);
	echo '<h2>'.$array_meses[intval($mes[1])].' '.$mes[0].'</h2>';
	 $select= " SELECT a.*,b.cve as certificado, b.certificado as holograma,b.engomado as engomado_entrega FROM cobro_engomado a LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
	WHERE a.plaza='".$_POST['plazausuario']."' AND a.tipo_pago = 1 AND a.estatus!='C' AND a.tipo_venta='2' AND LEFT(a.fecha,7) = '".$_POST['reg']."' ORDER BY a.placa,a.cve";
	
	//$select.=" ORDER BY a.cve DESC";
	$res=mysql_query($select);
	$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			$resultado = array();
			$placas=array();
			for($i=0;$row=mysql_fetch_array($res);$i++) {
				$resultado[$i] = $row;
				$placas[$row['placa']]++;
			}

			$placas_repetidas = 0;
			foreach($placas as $cantidad)
			{
				if($cantidad > 1)
				{
					$placas_repetidas++;
				}
			}

			echo '<h3>'.$placas_repetidas.' Placas Repetidas</h3>';

			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Ticket</th><th>Fecha</th><th>Placa</th>
			<th>Tiene Multa</th><th>Tipo de Certificado</th>
			<th>A&ntilde;o Certificacion</th><th>Tipo de Pago</th>
			<th>Tipo Combustible</th><th>Factura</th><th>Entrega Certificado</th><th>Holograma Entregado</th><th>Usuario</th>
			<th>Observaciones</th></tr>';
			$t=0;
			
			foreach($resultado as $i => $row){
				rowb();
				
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				
				if($placas[$row['placa']] > 1)
					echo '<td align="center"><font color="RED">'.htmlentities($row['placa']).'</font></td>';
				else
					echo '<td align="center">'.htmlentities($row['placa']).'</td>';
				
				echo '<td align="center">'.htmlentities($array_nosi[$row['multa']]).'</td>';
				echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
				echo '<td align="center">'.htmlentities($array_anios[$row['anio']]).'</td>';
				echo '<td align="center">'.htmlentities($array_tipo_pago[$row['tipo_pago']]).'</td>';
				echo '<td align="center">'.htmlentities($array_tipo_combustible[$row['tipo_combustible']]).'</td>';

				echo '<td align="center">'; 
				if($row['factura']==0){
					echo '&nbsp;';
				}
				else{
					$res1=mysql_query("SELECT serie,folio FROM facturas WHERE plaza='".$row['plaza']."' AND cve='".$row['factura']."'") or die(mysql_error());
					$row1=mysql_fetch_array($res1);
					echo $row1['serie'].' '.$row1['folio']; 
				}
				echo '</td>';
				echo '<td align="center">'.$row['certificado'].'</td>';
				echo '<td align="center">'.$row['holograma'].'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($array_usuario[$row['usuario']])).'</td>';
				echo '<td>'.htmlentities(utf8_encode($row['obs'])).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="13" bgcolor="#E9F2F8">';menunavegacion();echo '</td></tr>
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
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		if($_POST['plazausuario']==0){
			echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" onChange="traerCuentas()"><option value="all">Todas</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td></tr>';
		}
		else{
			echo '<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.date('Y').'-01-01" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td></tr>';
		echo '<tr><td>Agencia</td><td><select name="agencia" id="agencia"><option value="all" selected>Todos</option>';
		foreach($array_nosi as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
	
bottom();



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
			objeto.open("POST","depositantes_cortesias.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&agencia="+document.getElementById("agencia").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&plaza="+document.getElementById("plaza").value+"&nom="+document.getElementById("nom").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}';	
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
	
	</Script>
';

?>

