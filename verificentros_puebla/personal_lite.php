<?php 

include ("main.php"); 

/*** ARREGLOS ***********************************************************/

$rsPlaza=mysql_query("SELECT * FROM plazas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['numero'];
}




/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de personal
		
		$select= " SELECT a.cve,a.plaza,a.nombre,MIN(c.fechahora) as entrada, MAX(c.fechahora) as salida
		 FROM personal a 
		LEFT JOIN checada_lector c ON a.cve = c.cvepersonal AND DATE(c.fechahora) = CURDATE() 
		WHERE a.estatus=1 ";
		if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
		if ($_POST['num']!="") { $select.=" AND a.cve='".$_POST['num']."'"; }
		if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; }
		$select.=" GROUP BY a.cve ORDER BY trim(a.nombre)";
		$rspersonal=mysql_query($select);
		$totalRegistros = mysql_num_rows($rspersonal);
		
		if(mysql_num_rows($rspersonal)>0) 
		{
			
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<thead><tr bgcolor="#E9F2F8"><td colspan="5">'.mysql_num_rows($rspersonal).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8">';
			echo '<th>Plaza</th>';
			echo '<th><a href="#" onclick="SortTable(1,\'N\',\'tabla1\');">No.</a></th>
			<th><a href="#" onclick="SortTable(2,\'S\',\'tabla1\');">Nombre</a></th><th>Entrada</th><th>Salida</th>';
			echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
				rowb();
				echo '<td>'.utf8_encode($array_plaza[$Personal['plaza']]).'</td>';
				echo '<td align="center">'.$Personal['cve'].'</td>';
				if(file_exists("../imgpersonal/foto".$Personal['cve'].".jpg"))
					echo '<td align="left" onMouseOver="document.getElementById(\'foto'.$Personal['cve'].'\').style.visibility=\'visible\';" onMouseOut="document.getElementById(\'foto'.$Personal['cve'].'\').style.visibility=\'hidden\';">'.$Personal['nombre'].'<img width="200" id="foto'.$Personal['cve'].'" height="250" style="position:absolute;visibility:hidden" src="../imgpersonal/foto'.$Personal['cve'].'.jpg?'.date('h:i:s').'" border="1"></td>';
				else
					echo '<td align="left">'.htmlentities(utf8_encode(trim($Personal['nombre']))).'</td>';
				if($Personal['entrada'] == $Personal['salida']) $Personal['salida'] = '';
				echo '<td align="center">'.$Personal['entrada'].'</td>';
				echo '<td align="center">'.$Personal['salida'].'</td>';
				echo '</tr>';
			}
			
			echo '</tbody>
				<tr>
				<td colspan="5" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
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
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" class="textField">';
		if($_POST['plazausuario']==0)
			echo '<option value="all">---Todas---</option>';
		foreach($array_plaza as $k=>$v){
			if($_POST['plazausuario']==0 || $_POST['plazausuario']==$k)
				echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td><td></td><td>&nbsp;</td></tr>';
		echo '<tr style="display:none;"><td>Nombre</td><td><input type="text" name="nombre" id="nombre" class="textField"></td></tr>'; 
		echo '<tr><td>Numero</td><td><input type="text" name="num" id="num" class="textField"></td></tr>'; 
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

	function buscarRegistros(ordenamiento,orden)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","personal_lite.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nombre="+document.getElementById("nombre").value+"&num="+document.getElementById("num").value+"&plaza="+document.getElementById("plaza").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
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
			 //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}*/
	echo '
	buscarRegistros();
	</Script>
';

?>

