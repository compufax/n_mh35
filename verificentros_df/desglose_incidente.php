<?php
include ("main.php");
$res = mysql_query("SELECT * FROM cat_incidentes WHERE 1 ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_incidentes[$row['cve']]=$row['nombre']; 
	}
	
if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM incidentes WHERE plaza='".$_POST['plazausuario']."'";
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
				$select= " SELECT count(cve) as total FROM incidentes WHERE plaza='".$_POST['plazausuario']."' and incidente='".$k."' ";
				if($_POST['fini']!= "" and $_POST['ffin']== ""){$select.=" and fecha  BETWEEN '".$_POST['fini']."' AND '".fechaLocal()."'";}
				if($_POST['fini']== "" && $_POST['ffin']!= ""){$select.=" and fecha< '".$_POST['fini']."'";}
				if($_POST['fini']!= "" && $_POST['ffin']!= ""){$select.=" and fecha  BETWEEN '".$_POST['fini']."' AND '".$_POST['ffin']."'";}
				$res=mysql_query($select);
				$row=mysql_fetch_array($res);
				rowb();

				echo '<td align="left">'.htmlentities(utf8_encode($v)).'</td>';
				echo '<td align="center"><a href="#" onClick="atcr(\'desglose_incidente.php\',\'\',\'4\','.$k.');">'.$row['total'].'</a></td>';
				$por=($row['total']*100)/$totalRegistros;
				echo '<td align="center" width="">'.htmlentities(number_format($por,2)).' %</td>';
				echo '</tr>';
				$tota= $tota +$row['total'];
			}
			echo '	
				<tr>
				<td align="right" colspan="" bgcolor="#E9F2F8">total</td>
				<td  align="center" colspan="" bgcolor="#E9F2F8">'.$tota.'</td>
				<td  align="center" colspan="" bgcolor="#E9F2F8">100%';echo '</td>
				</tr>
			</table>';
			
		
		exit();	
}	


top($_SESSION);
if($_POST['cmd']==4) {
		//Listado de plazas
		echo '<table>';
		echo '
			<tr>';			
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'desglose_incidente.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
			echo'</tr>';
		echo '</table>';
		echo '<br>
		<table width="100%"><tr><th align="center" class="" style="font-size:18px">Incidentes por Motivo de: '.$array_incidentes[$_POST['reg']].' </th></tr></table>';
		
		$select= " SELECT * FROM incidentes WHERE plaza='".$_POST['plazausuario']."' and incidente='".$_POST['reg']."' ";
		if($_POST['fini']!= "" and $_POST['ffin']== ""){$select.=" and fecha  BETWEEN '".$_POST['fini']."' AND '".fechaLocal()."'";}
		if($_POST['fini']== "" && $_POST['ffin']!= ""){$select.=" and fecha< '".$_POST['fini']."'";}
		if($_POST['fini']!= "" && $_POST['ffin']!= ""){$select.=" and fecha  BETWEEN '".$_POST['fini']."' AND '".$_POST['ffin']."'";}
		$select.=" ORDER BY cve desc";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
//			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th></th><th>Folio</th>';echo'<th>Fecha</th><th>Motivo</th><th>Observaciones</th><th>Usuario</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				if($row['estatus']==1){
					echo '<td align="center">Cerrado </br>'.$row['fecha_cerrado'].' '.$row['hora_cerrado'].'</td>';
				}else{
				echo '<td align="center" width="40" nowrap></td>';
				}
				echo '<td align="center">'.htmlentities(utf8_encode($row['cve_aux'])).'</td>';
				echo '<td align="center" width="100">'.htmlentities(utf8_encode($row['fecha'])).' '.$row['hora'].'</td>';
				echo '<td align="" width="250">'.htmlentities(utf8_encode($array_incidentes[$row['incidente']])).'</td>';
				echo '<td align="center" width="500">';
				$sel= " SELECT * FROM incidentes_obs WHERE cve_aux='".$row['cve']."' ";
				$sel.=" ORDER BY cve desc";
				$re=mysql_query($sel);
			echo'<table width="100%">';
				while($row1=mysql_fetch_array($re)) {
					echo'<tr><td>('.$row1['fecha'].' '.$row1['hora'].')  **'.utf8_encode($row1['obs']).'**     ('.$array_usuario[$row1['usuario']].')</td></tr>';
				}
			echo'</table>';
				echo'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($array_usuario[$row['usuario']])).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="8" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				</tr>
			</table>';
			
		} else {
			echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
		}
		exit();	
}	


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