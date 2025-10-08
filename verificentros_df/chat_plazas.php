<?php 

include ("main.php"); 

if($_POST['ajax']==1){
	$res = mysql_query("SELECT * FROM usuarios");
	while($row=mysql_fetch_array($res)){
		$array_usuario[$row['cve']]=$row['usuario'];
	}
	$select = "SELECT * FROM chat_plazas WHERE DATE(fechahora) BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' ORDER BY cve DESC";
	$res=mysql_query($select);
	$totalRegistros = mysql_num_rows($res);
	if(mysql_num_rows($res)>0) 
	{
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Consecutivo</th><th>Fecha</th><th>Plazas</th><th>Usuario</th></tr>';
		while($row = mysql_fetch_array($res)){
			rowb();
			echo '<td align="center">';
			if($row['estatus']!='C'){
				echo '<a href="#" onClick="if(confirm(\'Esta seguro de terminar el mensaje\')) atcr(\'chat_plazas.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
			}
			else{
				echo 'TERMINADO';
			}
			echo '</td>';
			echo '<td align="center">'.$row['cve'].'</td>';
			echo '<td align="center">'.$row['fechahora'].'</td>';
			$plazas = explode('|', $row['plazas']);
			$cadenaplazas='';
			foreach($plazas as $plaza){
				if(trim($plaza)!=''){
					$cadenaplazas.=','.$plaza;
				}
			}
			echo '<td><ul>';
			if($cadenaplazas!=''){
				$res1=mysql_query("SELECT * FROM plazas WHERE cve IN (".substr($cadenaplazas,1).")");
				while($row1=mysql_fetch_array($res1)){
					echo '<li>'.$row1['numero'].' '.$row1['nombre'].'</li>';
				}
			}
			echo '</ul></td>';
			echo '<td align="center">'.$array_usuario[$row['usuario']].'</td>';
			echo '</tr>';
		}
		echo '	
				<tr>
				<td colspan="6" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				</tr>
			</table>';
	}
	else {
		echo '
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
		</tr>	  
		</table>';
	}
	exit();
}

if($_POST['cmd']==3){
	mysql_query("UPDATE chat_plazas SET estatus='C',fechaquita=NOW() WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

if ($_POST['cmd']==2) {
	$plazas='';
	
	foreach($_POST['mplazas'] as $k=>$v){
		$plazas .= '|'.$v.'|';
	}
	
	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE chat_plazas 
						SET mensaje='".$_POST['mensaje']."',plazas='$plazas'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);			
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO chat_plazas 
						(mensaje,plazas,usuario,fechahora,estatus)
						VALUES 
						('".$_POST['mensaje']."','$plazas','".$_POST['cveusuario']."',NOW(),'A')";
			$ejecutar = mysql_query($insert);
	}
	$_POST['cmd']=0;
}

top($_SESSION);	
	if(($_POST['cveusuario']!=1 && $_POST['cmd'] == 0) || $_POST['cmd']==1){

		$select=" SELECT * FROM chat_plazas ORDER BY cve DESC LIMIT 1 ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'chat_plazas.php\',\'\',\'2\',\'0\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		if($_POST['cmd']==1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'chat_plazas.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
		echo '
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Chat</td></tr>';
		echo '</table>';
		
		echo '<table>';
		//echo '<tr><th align="left">Etiqueta</th><td><textarea name="mensaje" id="mensaje" class="textField" cols="50" rows="5">'.$row['mensaje'].'</textarea></td></tr>';
		
		echo '<tr><th align="left" valign="top">Plazas<input type="checkbox" onClick="if(this.checked){$(\'.cplazas\').attr(\'checked\',\'checked\');} else{$(\'.cplazas\').removeAttr(\'checked\');}"></th><td>';
		$res1 = mysql_query("SELECT a.cve, a.numero, a.nombre, b.localidad_id FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza WHERE a.estatus != 'I'");
		while($row1 = mysql_fetch_array($res1)){
			echo '<div><input type="checkbox" name="mplazas[]"';
			echo ' id="plazas_'.$row1['cve'].'" value="'.$row1['cve'].'" class="cplazas"';
			$pos = strpos($row['plazas'], '|'.$row1['cve'].'|');
			if ($pos !== false) echo ' checked';
			echo '>'.$row1['numero'].'    '.$row1['nombre'].'</div>';
		}
		echo '</td>';
		echo '</table>';
		
	}

	if($_POST['cveusuario']==1 && $_POST['cmd'] == 0){
		echo '<table>';
		echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="atcr(\'chat_plazas.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
		echo '
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.date('Y-m').'-01" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '</table>';
		echo '<div id="Resultados">';
		echo '</div>';
			echo '
	<Script language="javascript">

		function buscarRegistros()
		{
			document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
			objeto=crearObjeto();
			if (objeto.readyState != 0) {
				alert("Error: El Navegador no soporta AJAX");
			} else {
				objeto.open("POST","chat_plazas.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
		buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	 	</script>';
		
	}
	
bottom();


?>

