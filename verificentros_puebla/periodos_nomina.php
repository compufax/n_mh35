<?php 

include ("main.php"); 
$_POST['cveempresa']=1;
/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE periodos_nomina 
						SET nombre='".$_POST['nombre']."',fecha_ini='".$_POST['fecha_ini']."',fecha_fin='".$_POST['fecha_fin']."',
						dias='".$_POST['dias']."',factor='".$_POST['factor']."',aguinaldo='".$_POST['aguinaldo']."',
						anio='".$_POST['anio']."',aguinaldo_excento='".$_POST['aguinaldo_excento']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);			
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO periodos_nomina 
						(nombre,empresa,fecha_ini,fecha_fin,dias,factor, aguinaldo, anio, aguinaldo_excento)
						VALUES 
						('".$_POST['nombre']."','".$_POST['cveempresa']."','".$_POST['fecha_ini']."','".$_POST['fecha_fin']."',
						'".$_POST['dias']."','".$_POST['factor']."', '".$_POST['aguinaldo']."', '".$_POST['anio']."',
						'".$_POST['aguinaldo_excento']."')";
			$ejecutar = mysql_query($insert);
	}
	$_POST['cmd']=0;
	
}


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM periodos_nomina WHERE 1 ";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		$select.=" AND empresa='".$_POST['cveempresa']."'"; 
		$select.=" ORDER BY fecha_fin DESC";
		$rsmotivo=mysql_query($select);
		$totalRegistros = mysql_num_rows($rsmotivo);
		/*if($totalRegistros / $eRegistrosPagina > 1) 
		{
			$eTotalPaginas = $totalRegistros / $eRegistrosPagina;
			if(is_int($eTotalPaginas))
			{$eTotalPaginas--;}
			else
			{$eTotalPaginas = floor($eTotalPaginas);}
		}
		$select .= " ORDER BY nombre LIMIT ".$primerRegistro.",".$eRegistrosPagina;
		$rsmotivo=mysql_query($select);*/
		
		if(mysql_num_rows($rsmotivo)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsmotivo).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th><th>Nombre</th><th>Fecha Inicial</th><th>Fecha Final</th><th>Factor</th><th>Dias</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($Motivo=mysql_fetch_array($rsmotivo)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$Motivo['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Motivo['nombre'].'"></a></td>';
				echo '<td>'.htmlentities($Motivo['nombre']).'</td>';
				echo '<td align="center">'.htmlentities($Motivo['fecha_ini']).'</td>';
				echo '<td align="center">'.htmlentities($Motivo['fecha_fin']).'</td>';
				echo '<td align="center">'.htmlentities($Motivo['factor']).'</td>';
				echo '<td align="center">'.htmlentities($Motivo['dias']).'</td>';
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


top($_SESSION);

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM periodos_nomina WHERE cve='".$_POST['reg']."' ";
		$rsmotivo=mysql_query($select);
		$Motivo=mysql_fetch_array($rsmotivo);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1 && $Motivo['generada_fiscal']!=1 && $Motivo['generada_nofiscal']!=1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'periodos_nomina.php\',\'\',\'2\',\''.$Motivo['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'periodos_nomina.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Periodos Nomina</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th>Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$Motivo['nombre'].'"></td></tr>';
		echo '<tr><th>Aguinaldo</th><td><input type="checkbox" name="aguinaldo" id="aguinaldo" value="1" onClick="
		if(this.checked){
			$(\'.cnormal\').hide();
			$(\'.caguinaldo\').show();
		}
		else{
			$(\'.cnormal\').show();
			$(\'.caguinaldo\').hide();
			document.forma.anio.value=\'\';
			document.forma.fecha_ini.value=\'\';
			document.forma.fecha_fin.value=\'\';
			document.forma.aguinaldo_excento.checked=false;
		}
		"';
		$display1=' style="display:none;"';
		$display2='';
		if($Motivo['aguinaldo']==1){
			echo ' checked';
			$display1='';
			$display2=' style="display:none;"';
		}
		echo '></td></tr>';
		echo '<tr'.$display1.' class="caguinaldo"><th>Año</th><td><input type="text" name="anio" id="anio" class="textField" size="10" value="'.$Motivo['anio'].'" onKeyUp="document.forma.fecha_ini.value=this.value+\'-01-01\'; document.forma.fecha_fin.value=this.value+\'-12-31\'"></td></tr>';
		echo '<tr'.$display1.' class="caguinaldo"><th>Aguinaldo Excento de Impuestos</th><td><input type="checkbox" name="aguinaldo_excento" id="aguinaldo_excento" class="textField" value="1"';
		if($Motivo['aguinaldo_excento']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr'.$display2.' class="cnormal"><th>Fecha Inicial</th><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="15" value="'.$Motivo['fecha_ini'].'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr'.$display2.' class="cnormal"><th>Fecha Final</th><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="15" value="'.$Motivo['fecha_fin'].'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th>Dias</th><td><input type="text" name="dias" id="dias" class="textField" size="5" value="'.$Motivo['dias'].'"></td></tr>';
		echo '<tr><th>Factor</th><td><input type="text" name="factor" id="factor" class="textField" size="10" value="'.$Motivo['factor'].'"></td></tr>';
		echo '</table>';	
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'periodos_nomina.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr>
				<td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td>
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
			objeto.open("POST","periodos_nomina.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

