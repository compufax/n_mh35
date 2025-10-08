<?php 

include ("main.php"); 		
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1)
{
	$pagina=file_get_contents('http://www.finanzas.df.gob.mx/sma/detallePlaca.php?placa='.$_POST['placa']);
	$pos = strpos($pagina, '<hr');
	$pagina=substr($pagina,0,$pos);
	
	$pagina2=file_get_contents('http://www.finanzas.df.gob.mx/consultas_pagos/consulta_adeudosten.php?wlplaca='.$_POST['placa'].'&consulta=Consulta+de+adeudos');
	$pos = strpos($pagina2, '<th>Relaci&oacute;n de adeudos de');
	$pagina2=substr($pagina2,$pos);
	$pos = strpos($pagina2, 'table');
	if($pos>0)
		$pagina2=substr($pagina2,0,$pos+6);
	else
		$pagina2=str_replace("<br />","</table>",$pagina2);
	echo $pagina.'<center><br/>TENENCIA<br/><table><thead><tr>'.$pagina2.'</center>';
		/*
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Sucursal</th><th>Nombre</th><th>Placa</th><th>Tarjeta de Circulacion</th><th>Marca</th><th>Modelo</th><th>A&ntilde;o</th><th>Tipo de Verificacion</th><th>Usuario</th></tr>';
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'CANCELADO';
				}
				else{
					echo '&nbsp;';
				}
				echo '</td>';
				echo '<td align="center">'.$row['cve'].'</td>';
				echo '<td align="center">'.$row['fecha'].' '.$row['hora'].'</td>';
				echo '<td>'.htmlentities(utf8_encode($array_plazas[$row['plaza']])).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($row['nombre'])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($row['placa'])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($row['tarjeta_circulacion'])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($row['marca'])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($row['modelo'])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($row['anio'])).'</td>';
				echo '<td>'.htmlentities(utf8_encode($array_engomado[$row['engomado']])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($array_usuario[$row['usuario']])).'</td>';
			}
			echo '	
				<tr>
				<td colspan="12" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				</tr>
			</table>';
			
		}*/
		exit();	
}	



top($_SESSION);

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="if(document.forma.placa.value==\'\') alert(\'Necesita ingresar la placa\'); else buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Placa</td><td><input type="text" name="placa" id="placa" size="10" class="textField placas"></td></tr>';
		echo '<tr><td>Sucursal</td><td><select name="plaza" id="plaza"><option value="all">Todos</option>';
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
					objeto.open("POST","consulta_tenencia.php",true);
					objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto.send("ajax=1&placa="+document.getElementById("placa").value+"&plaza="+document.getElementById("plaza").value+"&cveusuario="+document.getElementById("cveusuario").value+"&plazausuario="+document.getElementById("plazausuario").value);
					objeto.onreadystatechange = function()
					{
						if (objeto.readyState==4)
						{document.getElementById("Resultados").innerHTML = objeto.responseText;}
					}
				}
			}
			</Script>';
	}
	
bottom();





?>

