<?php
include("main.php");


if($_POST['ajax']==1){
	$select= " SELECT * FROM bitacora_digital WHERE plaza='".$_POST['plazausuario']."'";
	if($_POST['fecha_ini'] != '') $select .= " AND fecha_ingreso >= '".$_POST['fecha_ini']."'";
	if($_POST['fecha_fin'] != '') $select .= " AND fecha_ingreso <= '".$_POST['fecha_fin']."'";
	if($_POST['placa'] != '') $select .= " AND placa = '".$_POST['placa']."'";
			
	$select.=" ORDER BY fecha_ingreso DESC, hora_ingreso DESC";

	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>Placa</th><th>VIN</th><th>Marca</th><th>Submarca</th><th>Modelo</th>
	<th>Motivo Ingreso</th><th>Fecha Ingreso</th><th>Fecha Salida</th></tr>';
	$res = mysql_query($select);
	$x = 0;
	while($row = mysql_fetch_array($res)){
		rowb();
		echo '<td align="center">'.$row['placa'].'</td>';
		echo '<td align="center">'.$row['vin'].'</td>';
		echo '<td align="center">'.$row['marca'].'</td>';
		echo '<td align="center">'.$row['submarca'].'</td>';
		echo '<td align="center">'.$row['modelo'].'</td>';
		echo '<td align="center">'.$row['motivo_ingreso'].'</td>';
		echo '<td align="center">'.$row['fecha_ingreso'].' '.$row['hora_ingreso'].'</td>';
		echo '<td align="center">'.$row['fecha_salida'].' '.$row['hora_salida'].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th align="left" colspan="8">'.$x.' Registro(s)</th></tr></table>';
	exit();
}


top($_SESSION);

if($_POST['cmd']==2){
	if (is_uploaded_file($_FILES['archivobitacora']['tmp_name'])) {
		$f=fopen($_FILES['archivobitacora']['tmp_name'],'r');
		if($f){
			$primer_linea = true;
		    while (($l = fgets($f, 4096)) !== false) {
		    	if (!$primer_linea){
					$datos = explode(",", str_replace('"','',$l));
					$datosfechahora = explode(" ", $datos[6]);
					$hora_ingreso = substr($datosfechahora[1], 0, 8);
					$datosfecha = explode('/', $datosfechahora[0]);
					$fecha_ingreso = $datosfecha[2].'-'.$datosfecha[1].'-'.$datosfecha[0];
					$datosfechahora = explode(" ", $datos[7]);
					$hora_salida = substr($datosfechahora[1], 0, 8);
					$datosfecha = explode('/', $datosfechahora[0]);
					$fecha_salida = $datosfecha[2].'-'.$datosfecha[1].'-'.$datosfecha[0];
					if ($_POST['borrar'] == 1){
						mysql_query("DELETE FROM bitacora_digital WHERE plaza='".$_POST['plazausuario']."' AND placa = '".$datos[0]."' AND fecha_ingreso = '$fecha_ingreso' AND hora_ingreso = '$hora_ingreso'");
					}
					else{
						mysql_query("INSERT bitacora_digital SET plaza='".$_POST['plazausuario']."',fecha_creacion=CURDATE(),hora_creacion=CURTIME(),placa='".$datos[0]."',vin='".$datos[1]."',marca='".$datos[2]."',submarca='".$datos[3]."',modelo='".$datos[4]."',motivo_ingreso='".$datos[5]."',fecha_ingreso='$fecha_ingreso',hora_ingreso='$hora_ingreso',fecha_salida='$fecha_salida',hora_salida='$hora_salida',usuario='".$_POST['cveusuario']."'");
					}
				}
				$primer_linea = false;
			}
		}
	}
	else{
		echo '<b>Error al subir el archivo</b><br>';
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==1){
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'bitacora_digital.php\',\'\',\'2\',\'0\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'bitacora_digital.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	echo '<table>';
	echo '<tr><td class="tableEnc">Subir Archivo</td></tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><th align="left">Borrar Informacion</th><td><input type="checkbox" name="borrar" value="1"></td></tr>';
	echo '<tr><th align="left">Archivo</th><td><input type="file" name="archivobitacora" class="textField"></td></tr>';
	echo '</table>';


}

if($_POST['cmd'] == 0){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';

	echo '<td><a href="#" onClick="atcr(\'bitacora_digital.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Subir Archivo</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="'.fechaLocal().'">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="'.fechaLocal().'">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Placa</td><td><input type="text" name="placa" id="placa" size="10" class="textField" value=""></td></tr>';
	echo '</table>';
	echo '<div id="Resultados">';
	echo '</div>';
	echo '
	<script>

	function buscarRegistros(btn)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","bitacora_digital.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&placa="+document.getElementById("placa").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

	</script>';



}
bottom();
?>