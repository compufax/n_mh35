<?php 

include ("call_main.php"); 
$res = mysql_query("SELECT * FROM engomados ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
}

$res = mysql_query("SELECT * FROM call_usuarios");
$array_usuario[-1]="WEB";
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$res = mysql_query("SELECT * FROM datosempresas WHERE maneja_callcenter=1 ORDER BY nombre_callcenter");
while($row=mysql_fetch_array($res)){
	$array_plazas[$row['plaza']]=$row['nombre_callcenter'];
}


$array_tipo=array("Telefonista","Administrador");
		
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	
		$select= " SELECT a.nombre,count(b.cve) FROM call_usuarios a INNER JOIN call_citas b ON a.cve=b.usuario 
		WHERE date(b.fechayhora ) BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND b.estatus!='C'";
		if ($_POST['plaza']!="all") $select.=" AND plaza='".$_POST['plaza']."'";
		$select .= " GROUP BY a.cve ORDER BY a.nombre";		
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);

		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Nombre</th><th>Total de Citas</th></tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="left">'.htmlentities(utf8_encode($row['nombre'])).'</td>';
				echo '<td align="right">'.number_format($row[1],0).'</td>';
				$t+=$row[1];
			}
			echo '	
				<tr bgcolor="#E9F2F8">
				<th align="left">';menunavegacion();echo '</th>
				<th align="right">'.number_format($t,0).'</th>
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



top($_SESSION);

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="'.fechaLocal().'" >&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="'.fechaLocal().'" >&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr style="display:none;"><td>Centro de Verificacion</td><td><select name="plaza" id="plaza"><option value="all">Todos</option>';
		foreach($array_plazas as $k=>$v){
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
					objeto.open("POST","call_consultas_telefonista.php",true);
					objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&plaza="+document.getElementById("plaza").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
			}	
			
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
			</Script>';
	}
	
bottom();





?>

