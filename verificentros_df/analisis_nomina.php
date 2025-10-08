<?php 

include ("main.php"); 

/*** ARREGLOS ***********************************************************/

$rsPlaza=mysql_query("SELECT * FROM plazas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['numero'].' '.$Plaza['nombre'];
}
$rsPlaza=mysql_query("SELECT * FROM datosempresas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_lineas[$Plaza['plaza']]=$Plaza['numero_lineas'];
}

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsPuestos=mysql_query("SELECT * FROM puestos ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_puestos[$Puestos['cve']]=$Puestos['nombre'];
}


if($_POST['cmd']==50) {
		//Listado de personal
		
		if($_POST['mes']<10) $mes = '0'.intval($_POST['mes']);
		else $mes=$_POST['mes'];
		$diaultimo = date( "t" , strtotime ( "0 minute" , strtotime($_POST['anio'].'-'.$mes.'-01') ) );
		$fecha_ultima = date( "Y-m-t" , strtotime ( "0 minute" , strtotime($_POST['anio'].'-'.$mes.'-01') ) );
		$fecha_inicio = $_POST['anio'].'-'.$mes.'-01';
		
			echo '<h2>'.$array_plaza[$_POST['plaza']].'</h2>';
			echo '<h3>'.$array_meses[$_POST['mes']].' '.$_POST['anio'].'</h3>';
		if($_POST['reg']==0){

			
			$select= " SELECT count(cve),puesto,ROUND(salario_integrado*(IF(fecha_sta<='$fecha_inicio',30,DATEDIFF('$fecha_ultima',fecha_sta)+1)),2) as total FROM personal WHERE plaza = '".$_POST['plaza']."' AND estatus='1' AND administrativo!=1 AND fecha_sta<='$fecha_ultima'";
			$select.=" GROUP BY puesto,total";
			$rspersonal=mysql_query($select);
			$totalRegistros = mysql_num_rows($rspersonal);
			echo '<br><h3>LINEAS: '.$array_lineas[$_POST['plaza']].', Importe Promedio Mensual de Nomina<br>Cuenta con:</h3>';
			
			echo '<table width="50%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<tr bgcolor="#E9F2F8"><th>Cantidad</th><th>Puesto</th>';
			echo '<th>Sueldo</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			$z=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
				rowb();
				echo '<td align="center">'.$Personal[0].'</td>';
				echo '<td align="left">'.$array_puestos[$Personal['puesto']].'</td>';
				echo '<td align="right" onMouseOver="document.getElementById(\'listado'.$z.'\').style.visibility=\'visible\';" onMouseOut="document.getElementById(\'listado'.$z.'\').style.visibility=\'hidden\';">'.number_format($Personal['total'],2).'
				<div id="listado'.$z.'" style="position:absolute;visibility:hidden"><table bgcolor="#E9F2F8">';
				$res1=mysql_query("SELECT nombre FROM personal WHERE plaza='".$_POST['plaza']."' AND estatus=1 AND administrativo!=1 AND puesto='".$Personal['puesto']."' AND ROUND(salario_integrado*(IF(fecha_sta<='$fecha_inicio',30,DATEDIFF('$fecha_ultima',fecha_sta)+1)),2)='".$Personal['total']."'");
				while($row1=mysql_fetch_array($res1)){
					echo '<tr><td>'.htmlentities($row1['nombre']).'</td></tr>';
				}
				echo '</table></div></td>';
				echo '</tr>';
				$total += round($Personal['total']*$Personal[0],2);
				$i+=$Personal[0];
				$z++;
			}
			echo '<tr bgcolor="#FFFFFF"><td colspan="3">&nbsp;</td></tr>';
			echo '
				<tr>
				<th align="center" bgcolor="#E9F2F8">'.$i.'</th>
				<th align="left" bgcolor="#E9F2F8">Subtotal</th>
				<th align="right" bgcolor="#E9F2F8">'.number_format($total,2).'</a></th>
				</tr>';
			echo '<tr bgcolor="#FFFFFF"><td colspan="3">&nbsp;</td></tr>';
			$select= " SELECT count(cve),puesto,ROUND(salario_integrado*(IF(fecha_sta<='$fecha_inicio',30,DATEDIFF('$fecha_ultima',fecha_sta)+1)),2) as total FROM personal WHERE plaza = '".$_POST['plaza']."' AND estatus='1' AND administrativo=1 AND fecha_sta<='$fecha_ultima'";
			$select.=" GROUP BY puesto,total";
			$rspersonal=mysql_query($select);
			$totalRegistros = mysql_num_rows($rspersonal);
			while($Personal=mysql_fetch_array($rspersonal)) {
				rowb();
				echo '<td align="center">'.$Personal[0].'</td>';
				echo '<td align="left">'.$array_puestos[$Personal['puesto']].'</td>';
				echo '<td align="right" onMouseOver="document.getElementById(\'listado'.$z.'\').style.visibility=\'visible\';" onMouseOut="document.getElementById(\'listado'.$z.'\').style.visibility=\'hidden\';">'.number_format($Personal['total'],2).'
				<div id="listado'.$z.'" style="position:absolute;visibility:hidden"><table bgcolor="#E9F2F8">';
				$res1=mysql_query("SELECT nombre FROM personal WHERE plaza='".$_POST['plaza']."' AND estatus=1 AND administrativo=1 AND puesto='".$Personal['puesto']."' AND ROUND(salario_integrado*(IF(fecha_sta<='$fecha_inicio',30,DATEDIFF('$fecha_ultima',fecha_sta)+1)),2)='".$Personal['total']."'");
				while($row1=mysql_fetch_array($res1)){
					echo '<tr><td>'.htmlentities($row1['nombre']).'</td></tr>';
				}
				echo '</table></div></td>';
				echo '</tr>';
				$total += round($Personal['total']*$Personal[0],2);
				$i+=$Personal[0];
				$z++;
			}
			echo '<tr bgcolor="#FFFFFF"><td colspan="3">&nbsp;</td></tr>';
			echo '
				<tr>
				<th align="center" bgcolor="#E9F2F8">'.$i.'</th>
				<th align="left" bgcolor="#E9F2F8">Total</th>
				<th align="right" bgcolor="#E9F2F8">'.number_format($total,2).'</a></th>
				</tr>';
			echo '</table>';
		}	
		else{
		
			$select= " SELECT nombre,puesto,ROUND(salario_integrado*(IF(fecha_sta<='$fecha_inicio',30,DATEDIFF('$fecha_ultima',fecha_sta)+1)),2) as total FROM personal WHERE plaza = '".$_POST['plaza']."' AND estatus='1' AND fecha_sta<'$fecha_ultima'";
			$select.=" ORDER BY nombre";
			$rspersonal=mysql_query($select);
			$totalRegistros = mysql_num_rows($rspersonal);
			echo '<br><h3>Desglose de Nomina Mensual</h3>';
			
			echo '<table width="50%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<tr bgcolor="#E9F2F8"><th>Nombre</th><th>Puesto</th><th>Sueldo</th>';
			echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			$z=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
				rowb();
				echo '<td align="left">'.htmlentities($Personal['nombre']).'</td>';
				echo '<td align="left">'.$array_puestos[$Personal['puesto']].'</td>';
				echo '<td align="right">'.number_format($Personal['salario_integrado']*$diaultimo,2).'</td>';
				echo '</tr>';
				$total += round($Personal['salario_integrado']*$diaultimo,2);
				$z++;
			}
			
			echo '</tbody>
				<tr>
				<td align="left" bgcolor="#E9F2F8">'.$z.' Registro(s)</td>
				<td align="left" bgcolor="#E9F2F8">Total</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($total,2).'</td>
				</tr>
			</table>';
		}
		exit();	
}

/*** CONSULTA AJAX  **************************************************/


if($_POST['ajax']==1) {
		//Listado de personal
		
		
		if($_POST['mes']<10) $mes = '0'.intval($_POST['mes']);
		else $mes=$_POST['mes'];
		$diaultimo = date( "t" , strtotime ( "0 minute" , strtotime($_POST['anio'].'-'.$mes.'-01') ) );
		$fecha_ultima = date( "Y-m-t" , strtotime ( "0 minute" , strtotime($_POST['anio'].'-'.$mes.'-01') ) );
		$fecha_inicio = $_POST['anio'].'-'.$mes.'-01';
		$select= " SELECT count(cve),puesto,ROUND(salario_integrado*(IF(fecha_sta<='$fecha_inicio',30,DATEDIFF('$fecha_ultima',fecha_sta)+1)),2) as total FROM personal WHERE plaza = '".$_POST['plaza']."' AND estatus='1' AND administrativo!=1 AND fecha_sta<='$fecha_ultima'";
		$select.=" GROUP BY puesto,total";
		$rspersonal=mysql_query($select);
		$totalRegistros = mysql_num_rows($rspersonal);
			echo '<h2>'.$array_plaza[$_POST['plaza']].'</h2>';
			echo '<h3>'.$array_meses[$_POST['mes']].' '.$_POST['anio'].'</h3>';
			echo '<br><h3>LINEAS: '.$array_lineas[$_POST['plaza']].', Importe Promedio Mensual de Nomina<br>Cuenta con:</h3>';
			
			echo '<table width="50%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<tr bgcolor="#E9F2F8"><th>Cantidad</th><th>Puesto</th>';
			echo '<th>Sueldo</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			$z=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
				rowb();
				echo '<td align="center">'.$Personal[0].'</td>';
				echo '<td align="left">'.$array_puestos[$Personal['puesto']].'</td>';
				echo '<td align="right" onMouseOver="document.getElementById(\'listado'.$z.'\').style.visibility=\'visible\';" onMouseOut="document.getElementById(\'listado'.$z.'\').style.visibility=\'hidden\';">'.number_format($Personal['total'],2).'
				<div id="listado'.$z.'" style="position:absolute;visibility:hidden"><table bgcolor="#E9F2F8">';
				$res1=mysql_query("SELECT nombre FROM personal WHERE plaza='".$_POST['plaza']."' AND estatus=1 AND administrativo!=1 AND puesto='".$Personal['puesto']."' AND ROUND(salario_integrado*(IF(fecha_sta<='$fecha_inicio',30,DATEDIFF('$fecha_ultima',fecha_sta)+1)),2)='".$Personal['total']."'");
				while($row1=mysql_fetch_array($res1)){
					echo '<tr><td>'.htmlentities($row1['nombre']).'</td></tr>';
				}
				echo '</table></div></td>';
				echo '</tr>';
				$total += round($Personal['total']*$Personal[0],2);
				$i+=$Personal[0];
				$z++;
			}
			echo '<tr bgcolor="#FFFFFF"><td colspan="3">&nbsp;</td></tr>';
			echo '
				<tr>
				<th align="center" bgcolor="#E9F2F8">'.$i.'</th>
				<th align="left" bgcolor="#E9F2F8">Subtotal</th>
				<th align="right" bgcolor="#E9F2F8">'.number_format($total,2).'</a></th>
				</tr>';
			echo '<tr bgcolor="#FFFFFF"><td colspan="3">&nbsp;</td></tr>';
			$select= " SELECT count(cve),puesto,ROUND(salario_integrado*(IF(fecha_sta<='$fecha_inicio',30,DATEDIFF('$fecha_ultima',fecha_sta)+1)),2) as total FROM personal WHERE plaza = '".$_POST['plaza']."' AND estatus='1' AND administrativo=1 AND fecha_sta<='$fecha_ultima'";
			$select.=" GROUP BY puesto,total";
			$rspersonal=mysql_query($select);
			$totalRegistros = mysql_num_rows($rspersonal);
			while($Personal=mysql_fetch_array($rspersonal)) {
				rowb();
				echo '<td align="center">'.$Personal[0].'</td>';
				echo '<td align="left">'.$array_puestos[$Personal['puesto']].'</td>';
				echo '<td align="right" onMouseOver="document.getElementById(\'listado'.$z.'\').style.visibility=\'visible\';" onMouseOut="document.getElementById(\'listado'.$z.'\').style.visibility=\'hidden\';">'.number_format($Personal['total'],2).'
				<div id="listado'.$z.'" style="position:absolute;visibility:hidden"><table bgcolor="#E9F2F8">';
				$res1=mysql_query("SELECT nombre FROM personal WHERE plaza='".$_POST['plaza']."' AND estatus=1 AND administrativo=1 AND puesto='".$Personal['puesto']."' AND ROUND(salario_integrado*(IF(fecha_sta<='$fecha_inicio',30,DATEDIFF('$fecha_ultima',fecha_sta)+1)),2)='".$Personal['total']."'");
				while($row1=mysql_fetch_array($res1)){
					echo '<tr><td>'.htmlentities($row1['nombre']).'</td></tr>';
				}
				echo '</table></div></td>';
				echo '</tr>';
				$total += round($Personal['total']*$Personal[0],2);
				$i+=$Personal[0];
				$z++;
			}
			echo '<tr bgcolor="#FFFFFF"><td colspan="3">&nbsp;</td></tr>';
			echo '
				<tr>
				<th align="center" bgcolor="#E9F2F8">'.$i.'</th>
				<th align="left" bgcolor="#E9F2F8">Total</th>
				<th align="right" bgcolor="#E9F2F8">'.number_format($total,2).'</a></th>
				</tr>';
			echo '</table>';
			echo '<b><a href="#" onClick="atcr(\'analisis_nomina.php\',\'\',1,0);">Ver Plantilla</a></b>';
			
		echo '<br>';
		
		/*$select= " SELECT nombre,puesto,salario_integrado FROM personal WHERE plaza = '".$_POST['plaza']."' AND estatus='1'";
		$select.=" ORDER BY nombre";
		$rspersonal=mysql_query($select);
		$totalRegistros = mysql_num_rows($rspersonal);
			echo '<br><h3>Desglose de Nomina Mensual</h3>';
			
			echo '<table width="50%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<tr bgcolor="#E9F2F8"><th>Nombre</th><th>Puesto</th><th>Sueldo</th>';
			echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			$z=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
				rowb();
				echo '<td align="left">'.htmlentities($Personal['nombre']).'</td>';
				echo '<td align="left">'.$array_puestos[$Personal['puesto']].'</td>';
				echo '<td align="right">'.number_format($Personal['salario_integrado']*$diaultimo,2).'</td>';
				echo '</tr>';
				$total += round($Personal['salario_integrado']*$diaultimo,2);
				$z++;
			}
			
			echo '</tbody>
				<tr>
				<td align="left" bgcolor="#E9F2F8">'.$z.' Registro(s)</td>
				<td align="left" bgcolor="#E9F2F8">Total</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($total,2).'</td>
				</tr>
			</table>';
			
	*/
		exit();	
}


top($_SESSION);


if($_POST['cmd']==1){
	echo '<table>';
		echo '<tr>
				<td><a href="#" onClick="atcr(\'analisis_nomina.php\',\'_blank\',\'50\',\'1\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'analisis_nomina.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0"></a>&nbsp;Regresar</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<input type="hidden" name="mes" value="'.$_POST['mes'].'">';
		echo '<input type="hidden" name="anio" value="'.$_POST['anio'].'">';
		echo '<input type="hidden" name="plaza" value="'.$_POST['plaza'].'">';
		if($_POST['mes']<10) $mes = '0'.intval($_POST['mes']);
		else $mes=$_POST['mes'];
		$diaultimo = date( "t" , strtotime ( "0 minute" , strtotime($_POST['anio'].'-'.$mes.'-01') ) );
		$fecha_ultima = date( "Y-m-t" , strtotime ( "0 minute" , strtotime($_POST['anio'].'-'.$mes.'-01') ) );
		$fecha_inicio = $_POST['anio'].'-'.$mes.'-01';	
		$select= " SELECT nombre,puesto,ROUND(salario_integrado*(IF(fecha_sta<='$fecha_inicio',30,DATEDIFF('$fecha_ultima',fecha_sta)+1)),2) as total FROM personal WHERE plaza = '".$_POST['plaza']."' AND estatus='1' AND fecha_sta<'$fecha_ultima'";
		$select.=" ORDER BY nombre";
		$rspersonal=mysql_query($select);
		$totalRegistros = mysql_num_rows($rspersonal);
		
			echo '<h2>'.$array_plaza[$_POST['plaza']].'</h2>';
			echo '<h3>'.$array_meses[$_POST['mes']].' '.$_POST['anio'].'</h3>';
			echo '<br><h3>Desglose de Nomina Mensual</h3>';
			
			echo '<table width="50%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<tr bgcolor="#E9F2F8"><th>Nombre</th><th>Puesto</th><th>Sueldo</th>';
			echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			$z=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
				rowb();
				echo '<td align="left">'.$Personal['nombre'].'</td>';
				echo '<td align="left">'.$array_puestos[$Personal['puesto']].'</td>';
				echo '<td align="right">'.number_format($Personal['total'],2).'</td>';
				echo '</tr>';
				$total += round($Personal['total'],2);
				$z++;
			}
			
			echo '</tbody>
				<tr>
				<td align="left" bgcolor="#E9F2F8">'.$z.' Registro(s)</td>
				<td align="left" bgcolor="#E9F2F8">Total</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($total,2).'</td>
				</tr>
			</table>';
			
}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="if(document.forma.plaza.value==\'0\') alert(\'Necesita seleccionar una plaza\'); else buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;
					<a href="#" onClick="atcr(\'analisis_nomina.php\',\'_blank\',\'50\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" class="textField"><option value="0">---Seleccione---</option>';
		foreach($array_plaza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td><td></td><td>&nbsp;</td></tr>';
		echo '<tr><td>Mes</td><td><select name="mes" id="mes">';
		foreach($array_meses as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==intval(date("m"))) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Año</td><td><select name="anio" id="anio">';
		$anio = date("Y");
		while($anio>=2014){
			echo '<option value="'.$anio.'">'.$anio.'</option>';
			$anio--;
		}
		echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
	




/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros(ordenamiento,orden)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","analisis_nomina.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&mes="+document.getElementById("mes").value+"&anio="+document.getElementById("anio").value+"&plaza="+document.getElementById("plaza").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
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
	/*if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}*/
	echo '
	
	</Script>
';
bottom();
?>

