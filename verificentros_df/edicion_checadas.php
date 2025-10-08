<?php

include("main.php");
$res = mysql_query("SELECT * FROM motivos_checada WHERE reporte=1 ORDER BY orden");
while($row = mysql_fetch_array($res)) $array_motivo[$row['cve']] = $row['nombre'];

if($_POST['ajax']==1){
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>&nbsp;</th><th>Empleado</th><th>Motivo Checada</th><th>Fecha y Hora</th></tr>';
	$filtro = "";
	if($_POST['nombre'] != '') $filtro = " AND b.nombre LIKE '%".$_POST['nombre']."%'";
	$res = mysql_query("SELECT a.cve, a.fechahora, a.tipo, b.nombre FROM checada_lector a INNER JOIN personal b ON b.cve = a.cvepersonal WHERE DATE(a.fechahora) BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' $filtro ORDER BY a.fechahora");
	while($row = mysql_fetch_array($res)){
		rowb();
		echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'edicion_checadas.php\',\'\',\'1\','.$row['cve'].');"><img src="images/modificar.gif" border="0" title="Editar"></a></td>';
		echo '<td>'.$row['nombre'].'</td>';
		echo '<td>'.$array_motivo[$row['tipo']].'</td>';
		echo '<td align="center">'.$row['fechahora'].'</td>';
		echo '</tr>';
	}
	echo '</table>';
	exit();
}



top($_SESSION);

if($_POST['cmd']==2){
	mysql_query("UPDATE checada_lector SET tipo='".$_POST['motivo']."' WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}


if($_POST['cmd']==1){
	$res = mysql_query("SELECT a.cve, a.fechahora, a.tipo, b.nombre FROM checada_lector a INNER JOIN personal b ON b.cve = a.cvepersonal WHERE a.cve='".$_POST['reg']."' ORDER BY a.fechahora");
	$row = mysql_fetch_array($res);
	echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="atcr(\'edicion_checadas.php\',\'\',\'2\',\''.$_POST['reg'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="atcr(\'edicion_checadas.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';

		echo '<table>';
		echo '<tr><th align="left">Nombre</th><td>'.$row['nombre'].'</td></tr>';
		echo '<tr><th align="left">Nombre</th><td><select name="motivo" id="motivo">';
		foreach($array_motivo as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['tipo']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Fecha y Hora</th><td>'.$row['fechahora'].'</td></tr>';
		echo '</table>';


}


/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" class="textField"></td></tr>'; 
	echo '</table>';
	echo '<br>';
	//Listado
	echo '<div id="Resultados">';
	echo '</div>';
	echo '
<Script language="javascript">

	function buscarRegistros(ordenamiento,orden)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","edicion_checadas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nombre="+document.getElementById("nombre").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
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
	
	</Script>
';
}
	
bottom();