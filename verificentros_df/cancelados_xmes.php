<?php 
include ("main.php"); 
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND plazas like '%|".$_POST['plazausuario']."|%' AND entrega=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_datos_engomado[] = $row;
}

$array_meses=array("",01=>"Enero",02=>"Febrero",03=>"Marzo",04=>"Abril",05=>"Mayo",06=>"Junio",07=>"Julio",08=>"Agosto",09=>"Septiembre",10=>"Octubre",11=>"Noviembre",12=>"Diciembre")

	if ($_POST['cmd']<1) {
	
	$ini=substr($_POST['fecha_ini'], 5, 2);
	$fin=substr($_POST['fecha_fin'], 5, 2);
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>Mes</th><th>Certificado</th><th>Cantidad</th>';
	for($i=$ini;$i<=$fin;$i++){
		rowb();
		echo '<td align="left">'.htmlentities($array_meses[$i]).'</td>';
			foreach($array_engomado as $k=>$v) {
				$select= " SELECT * ,count(cve) as total_cert FROM certificados_cancelados WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$k."'";
				$select.=" AND month(fecha) BETWEEN '".$ini."' AND '".$fin."'";
//			if ($_POST['motivo']!="all") { $select.=" AND motivo='".$_POST['motivo']."' "; }
//			if ($_POST['engomado']!="all") { $select.=" AND engomado='".$_POST['engomado']."' "; }
				if ($_POST['engomado']!="") { $select .= " AND engomado IN (".$_POST['engomado'].")"; }		
//			if ($_POST['anio']!="all") { $select.=" AND anio='".$_POST['anio']."' "; }
			//if ($_POST['certificado']!="") { $select.=" AND certificado='".$_POST['certificado']."' "; }
				$select.=" ORDER BY cve DESC";
		//if($_POST['btn']==0) $select.=" LIMIT 1";
				$res=mysql_query($select);
				$totalRegistros = mysql_num_rows($res);
				$row=mysql_fetch_array($res);
				echo '<td align="left">'.htmlentities($array_engomado[$k]).'</td>';
				echo '<td align="left">'.$row['total_cert'].'</td>';
			}
		
	}
	
	}
top($_SESSION);


	if ($_POST['cmd']<1) {
		
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
		echo '
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr style="display:none;><td>Tipo de Certificado</td><td><select name="engomado" id="engomado"><option value="">Todos</option>';
		foreach($array_engomado as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
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
			objeto.open("POST","cancelados_xmes.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&engomado="+document.getElementById("engomado").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	buscarRegistros(0); //Realizar consulta de todos los registros al iniciar la forma.
	</Script>
	';

	
}
	
bottom();

?>