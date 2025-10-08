<?php 
include ("main.php"); 


$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}
$res = mysql_query("SELECT * FROM plazas where estatus='A'");
while($row=mysql_fetch_array($res)){
	$array_plazas[$row['cve']]=$row['numero'].' - '.$row['nombre'];
}



$array_estatus = array('A'=>'Activo','C'=>'Cancelado');
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		
		
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Plaza</th><th>Libres</th><th>Compra</br>Timbres</th><th>Facturas</br>Sin Timbrar</th><th>Facturas</br>Timbradas</th><th>Facturas</br>Canceladas</th>';
			echo '</tr>';
			$t3=0;
		foreach ($array_plazas as $k=>$v) 
		{
			
			
			$saldo = saldo_timbres($k);
			
				rowb();	
				echo '<td>&nbsp</td>';
				echo '<td align="">'.htmlentities(utf8_encode($v)).'</td>';
				echo '<td align="right">'.number_format($saldo,0).'</td>';
				$select= " SELECT sum(cantidad) as comprados FROM compra_timbres WHERE plaza='".$k."' and estatus!='C'";
				$res=mysql_query($select);
				$row=mysql_fetch_array($res);
				echo '<td align="right">'.number_format($row['comprados'],0).'</td>';
				
				$select1= " SELECT count(a.cve) as sin_timbrar,a.*, b.rfc as rfccliente FROM facturas as a 
				LEFT JOIN clientes b ON b.plaza = a.plaza AND b.cve = a.cliente 
				LEFT JOIN cobro_engomado as c ON a.plaza = c.plaza AND a.cve = c.factura 
				WHERE a.plaza='$k' AND a.tipo_serie = 0 AND a.estatus!='C' AND a.respuesta1 = ''";
				$res1=mysql_query($select1);
				$row1=mysql_fetch_array($res1);
				echo '<td align="right">'.number_format($row1['sin_timbrar'],0).'</td>';
				$select2= " SELECT count(a.cve) as timbrada,a.*, b.rfc as rfccliente FROM facturas as a 
				LEFT JOIN clientes b ON b.plaza = a.plaza AND b.cve = a.cliente 
				LEFT JOIN cobro_engomado as c ON a.plaza = c.plaza AND a.cve = c.factura 
				WHERE a.plaza='$k' AND a.tipo_serie = 0 AND a.respuesta1 != ''";
				$res2=mysql_query($select2);
				$row2=mysql_fetch_array($res2);
				echo '<td align="right">'.number_format($row2['timbrada'],0).'</td>';
				$select3= " SELECT count(a.cve) as canceladas_,a.*, b.rfc as rfccliente FROM facturas as a 
				LEFT JOIN clientes b ON b.plaza = a.plaza AND b.cve = a.cliente 
				LEFT JOIN cobro_engomado as c ON a.plaza = c.plaza AND a.cve = c.factura 
				WHERE a.plaza='$k' AND a.tipo_serie = 0 AND a.estatus='C'";
				$res3=mysql_query($select3);
				$row3=mysql_fetch_array($res3);
				echo '<td align="right">'.number_format($row3['canceladas_'],0).'</td>';
				echo '</tr>';
				$t3+=$saldo;
				$t4+=$row['comprados'];
				$t5+=$row1['sin_timbrar'];
				$t6+=$row2['timbrada'];
				$t7+=$row3['canceladas_'];
			
			
			
		}
		echo '	
				<tr>
				<td  aling="right" colspan="2" bgcolor="#E9F2F8">';echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t3,0).'</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t4,0).'</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t5,0).'</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t6,0).'</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t7,0).'</td>
				</tr>
			';
		echo'</table>';
		exit();	
}	


top($_SESSION);

if ($_POST['cmd']<1) {
	
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	//echo '<td><a href="#" onClick="atcr(\'existencia_timbres.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
	echo '
		 </tr>';
	echo '</table>';
	echo '<table width="100%"><tr><td width="50%">';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="" >&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="" >&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	/*echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_plazas as $k=>$v) { 
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';*/
	echo '</table>';
	echo '</td><td id="concentrado"></td></tr></table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros(btn)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","existencia_timbres.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&btn="+btn+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					datos = objeto.responseText.split("|");
					document.getElementById("Resultados").innerHTML = datos[0];
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