<?php
$res = mysql_query("SELECT * FROM cat_incidentes WHERE 1 ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_incidentes[$row['cve']]=$row['nombre']; 
	}
	
if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM incidentes WHERE plaza='".$_POST['plazausuario']."' and estatus!='1'";
		if($_POST['fini']!= "" and $_POST['ffin']== ""){$select.=" and fecha  BETWEEN '".$_POST['fini']."' AND '".fechaLocal()."'";}
		if($_POST['fini']== "" && $_POST['ffin']!= ""){$select.=" and fecha< '".$_POST['fini']."'";}
		if($_POST['fini']!= "" && $_POST['ffin']!= ""){$select.=" and fecha  BETWEEN '".$_POST['fini']."' AND '".$_POST['ffin']."'";}
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
//			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Incidente</th><th>Cantidad</th>';echo'<th>Porcentaje(%)</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			foreach($array_incidentes as $k =>$v) {
				$select= " SELECT sum(cve) as total FROM incidentes WHERE plaza='".$_POST['plazausuario']."' and incidente='".$k."' and estatus!='1' ";
				if($_POST['fini']!= "" and $_POST['ffin']== ""){$select.=" and fecha  BETWEEN '".$_POST['fini']."' AND '".fechaLocal()."'";}
				if($_POST['fini']== "" && $_POST['ffin']!= ""){$select.=" and fecha< '".$_POST['fini']."'";}
				if($_POST['fini']!= "" && $_POST['ffin']!= ""){$select.=" and fecha  BETWEEN '".$_POST['fini']."' AND '".$_POST['ffin']."'";}
				$res=mysql_query($select);
				$row=mysql_fetch_array($res);
				rowb();
				echo '<td align="left">'.htmlentities(utf8_encode($v).'</td>';

				echo '<td align="right"><a href="#" onClick="atcr(\'desglose_incidente.php\',\'\',\'4\',\'0\');">'.$row['total'].'</a></td>';
				$por=($row['total']*100)/$totalRegistros;
				echo '<td align="center" width="">'.htmlentities($por).' %</td>';
				echo '</tr>';
				$tota= $tota +$row['total'];
			}
			echo '	
				<tr>
				<td align="right" colspan="" bgcolor="#E9F2F8">total'echo '</td>
				<td  align="right" colspan="" bgcolor="#E9F2F8">'.$tota.'</td>
				<td  align=""center colspan="" bgcolor="#E9F2F8">100%';echo '</td>
				</tr>
			</table>';
			
		
		exit();	
}	


top($_SESSION);


	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'desglose_incidente.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>
		      <tr>
	       <td><span>Fecha inicial</span></td>
           <td><input size="10" value="" name="fini" id="fini" type="text" class="readOnly" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td>
           </tr>
           <tr>
           <td><span>Fecha final</span></td>
           <td><input size="10" value="" name="ffin" id="ffin" type="text" class="readOnly" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].ffin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td>
           </tr>';
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
			objeto.open("POST","desglose_incidente.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&fini="+document.getElementById("fini").value+"&ffin="+document.getElementById("ffin").value);
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