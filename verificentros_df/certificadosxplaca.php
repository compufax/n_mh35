<?php
include("main.php");
$res = mysql_query("SELECT * FROM certificados_ecologia where 1 group by estado");
while($row=mysql_fetch_array($res)){
	$array_estado[$row['estado']]=$row['estado'];
}

if($_POST['ajax']==1){
	$select= " SELECT count(a.cve) as cantidad, a.*, b.nombre as engomado FROM certificados_ecologia a LEFT JOIN engomados b ON b.cve = a.tipo WHERE a.plaza='".$_POST['plazausuario']."' AND fecha = '".$_POST['anio']."-".$_POST['mes']."'";
	if($_POST['placa'] != '') $select .= " AND a.placa = '".$_POST['placa']."'";
	if($_POST['certificado'] != '') $select .= " AND a.certificado = '".$_POST['certificado']."'";
	if($_POST['estado'] != '') $select .= " AND a.estado = '".$_POST['estado']."'";
	$select.=" group by a.placa";		
	//$select.=" ORDER BY a.fecha DESC, a.cve DESC";

	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<!--<th>Mes</th><th>Certificado</th><th>Tipo</th><th>Estado</th>--><th>Placa</th><th>Cantidad</th></tr>';
	$res = mysql_query($select) or die(mysql_error());
	$x = 0;
	//echo''.$select.'';
	while($row = mysql_fetch_array($res)){
		rowb();
//		echo '<td align="center">'.$row['fecha'].'</td>';
//		echo '<td align="center">'.$row['certificado'].'</td>';
//		echo '<td align="center">'.$row['engomado'].'</td>';
//		echo '<td align="center">'.$row['estado'].'</td>';
//		$selec= " SELECT count(a.cve) as veces FROM certificados_ecologia a LEFT JOIN engomados b ON b.cve = a.tipo WHERE a.plaza='".$_POST['plazausuario']."' AND fecha = '".$_POST['anio']."-".$_POST['mes']."' AND a.placa='".$row['placa']."'";
//		if($_POST['placa'] != '') $selec .= " AND a.placa = '".$_POST['placa']."'";
//		if($_POST['certificado'] != '') $selec .= " AND a.certificado = '".$_POST['certificado']."'";
//		if($_POST['estado'] != '') $selec .= " AND a.estado = '".$_POST['estado']."'";
//		$re=mysql_query($selec);
//		$row1=mysql_fetch_array($re);
//		if($row1['veces']>1){echo '<td align="center" ><font color="red">'.$row['placa'].'</font></td>';}else{echo '<td align="center" >'.$row['placa'].'</td>';}
		echo '<td align="" >'.$row['placa'].'</td>';
		echo '<td align="center" >'.$row['cantidad'].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th align="left" colspan="5">'.$x.' Registro(s)</th></tr></table>';
	exit();
}


top($_SESSION);

/*if($_POST['cmd']==2){
	if (is_uploaded_file($_FILES['archivobitacora']['tmp_name'])) {
		$f=fopen($_FILES['archivobitacora']['tmp_name'],'r');
		if($f){
			$primer_linea = true;
		    while (($l = fgets($f, 4096)) !== false) {
		    	if (!$primer_linea){
					$datos = explode(",", str_replace('"','',$l));
					if (count($datos) > 4) {
						$datos[2] = $datos[3];
						$datos[3] = $datos[4];
					}
					if ($_POST['borrar'] == 1){
						mysql_query("DELETE FROM certificados_ecologia WHERE plaza='".$_POST['plazausuario']."' AND certificado='".$datos[1]."' AND placa = '".$datos[3]."'");
					}
					else{
						mysql_query("INSERT certificados_ecologia SET plaza='".$_POST['plazausuario']."',fecha_creacion=CURDATE(),hora_creacion=CURTIME(),fecha='".$datos[0]."',certificado='".$datos[1]."',placa='".$datos[3]."',estado='".$datos[2]."',usuario='".$_POST['cveusuario']."'");
					}
				}
				$primer_linea = false;
			}
			mysql_query("UPDATE certificados_ecologia a INNER JOIN compra_certificados_detalle b on a.plaza = b.plaza AND a.certificado = b.folio INNER JOIN compra_certificados c ON c.plaza = b.plaza AND c.cve=b.cvecompra AND c.estatus!='C' SET a.tipo = c.engomado WHERE a.plaza = '".$_POST['plazausuario']."' AND a.tipo=0");
		}
	}
	else{
		echo '<b>Error al subir el archivo</b><br>';
	}
	$_POST['cmd']=0;
}
*/
if($_POST['cmd']==1){
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'certificadosxplaca.php\',\'\',\'2\',\'0\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'certificadosxplaca.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
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

	echo '<!--<td><a href="#" onClick="atcr(\'certificadosxplaca.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Subir Archivo</td><td>&nbsp;</td>
		 --></tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>A&ntilde;o</td><td><select name="anio" id="anio">';
	for($i=date('Y');$i>=2018;$i--){
		echo '<option value="'.$i.'">'.$i.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Mes</td><td><select name="mes" id="mes">';
	for($i=1;$i<=12;$i++){
		echo '<option value="'.sprintf('%02s',$i).'"';
		if($i==intval(date('m'))) echo ' selected';
		echo '>'.$array_meses[$i].'</option>';
	}
	echo '</option></td></tr>';
	echo '<tr style="display:none"><td>Placa</td><td><input type="text" name="placa" id="placa" size="10" class="textField" value=""></td></tr>';
	echo '<tr style="display:none"><td>Certificado</td><td><input type="text" name="certificado" id="certificado" size="15" class="textField" value=""></td></tr>';
	echo '<tr style="display:none"><td>Estado</td><td><select name="estado" id="estado"><option value="">Todos</option>';
		foreach($array_estado as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
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
			objeto.open("POST","certificadosxplaca.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&anio="+document.getElementById("anio").value+"&mes="+document.getElementById("mes").value+"&placa="+document.getElementById("placa").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&certificado="+document.getElementById("certificado").value+"&estado="+document.getElementById("estado").value);
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