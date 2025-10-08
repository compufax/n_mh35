<?php

include("main.php");

if($_POST['ajax']==1){
	
	$res = mysql_query("SELECT c.numero, c.nombre, d.nombre as anio, a.placa, a.fecha, MIN(b.fecha) as fecha2 FROM cobro_engomado a 
			INNER JOIN cobro_engomado b ON a.plaza = b.plaza AND a.placa = b.placa AND a.anio = b.anio AND a.cve < b.cve AND b.estatus!='C' 
			INNER JOIN plazas c ON c.cve = a.plaza
			INNER JOIN anios_certificados d ON d.cve = a.anio
			WHERE a.plaza IN ({$_POST['plaza']}) a.estatus = 'C' GROUP BY a.plaza, a.placa");
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>Nombre Plaza</th><th>Placa</th><th>Semestre</th><th>Fecha Cancelacion</th><th>Fecha Nueva Venta</th></tr>';
	echo '</tr>';
	$x=0;
	while($row = mysql_fetch_array($res)){
		rowb();
		echo '<td align="left">'.$row['numero'].' '.$row['nombre'].'</td>';
		echo '<td align="center">'.$row['placa'].'</td>';
		echo '<td align="center">'.$row['anio'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		echo '<td align="center">'.$row['fecha2'].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr  bgcolor="#E9F2F8"><th align="left" colspan="5">'.$x.' Registro(s)</th></tr>';
	echo '</table>';
	exit();
}


top($_SESSION);

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<!--<td><a href="#" onclick="document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');atcr(\'certificadosxplaza.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir</td><td>&nbsp;</td>-->
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td align="left">Plaza</td><td><select multiple="multiple" name="plazas" id="plazas">';
	foreach($array_plazas as $k=>$v){
		echo '<option value="'.$k.'" selected>'.$v.'</option>';
	}
	echo '</select>';
	echo '<input type="hidden" name="plaza" id="plaza" value=""></td></tr>';
	echo '</table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	$("#plazas").multipleSelect({
		width: 500
	});	
	function buscarRegistros(){
		document.forma.plaza.value=$("#plazas").multipleSelect("getSelects");
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","rep_placas_cancelacion.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&plaza="+document.getElementById("plaza").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

	
	
	</Script>
	';

	
}
	
bottom();
?>