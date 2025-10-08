<?php 

include ("main.php"); 

$array_puestos = array();
$res = mysql_query("SELECT * FROM puestos ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_puestos[$row['cve']]=$row['nombre'];
}
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM administradores WHERE 1 ";
		if($_POST['plaza']!="all") $select .= " AND plaza='".$_POST['plaza']."'";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		$select.=" ORDER BY nombre";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th>';
			if($_POST['plazausuario']==0) echo '<th>Plaza</th>';
			echo '<th>Nombre</th><th>Puesto</th><th>Fecha Inicial</th><th>Fecha Final</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef['nombre'].'"></a></td>';
				if($_POST['plazausuario']==0) echo '<td>'.$array_plaza[$row['plaza']].'</td>';
				echo '<td>'.htmlentities($row['nombre']).'</td>';
				echo '<td>'.htmlentities($array_puestos[$row['puesto']]).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha_ini']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha_fin']).'</td>';
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


/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE administradores 
						SET nombre='".$_POST['nombre']."',puesto='".$_POST['puesto']."',fecha_ini='".$_POST['fecha_ini']."',
						fecha_fin='".$_POST['fecha_fin']."',recibo_salida='".$_POST['recibo_salida']."',polizas='".$_POST['polizas']."',
						depositos='".$_POST['depositos']."',transferencias='".$_POST['transferencias']."',nomina='".$_POST['nomina']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);			
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO administradores 
						(nombre,puesto,fecha_ini,fecha_fin,recibo_salida,plaza,polizas,depositos,transferencias,nomina)
						VALUES 
						('".$_POST['nombre']."','".$_POST['puesto']."','".$_POST['fecha_ini']."','".$_POST['fecha_fin']."',
						'".$_POST['recibo_salida']."','".$_POST['plaza']."','".$_POST['polizas']."','".$_POST['depositos']."',
						'".$_POST['transferencias']."','".$_POST['nomina']."')";
			$ejecutar = mysql_query($insert);
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM administradores WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		if($row['fecha_ini']=='0000-00-00') $row['fecha_ini']='';
		if($row['fecha_fin']=='0000-00-00') $row['fecha_fin']='';
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();if(document.forma.plaza.value==\'0\'){$(\'#panel\').show(); alert(\'Necesita seleccionar la plaza\');}else{ atcr(\'administradores.php\',\'\',\'2\',\''.$row['cve'].'\');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'administradores.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Administradores</td></tr>';
		echo '</table>';
		
		echo '<table>';
		if($_POST['plazausuario']==0 && $row['plaza']==0)
		{
			echo '<tr><th align="left">Plaza</th><td><select name="plaza" id="plaza"><option value="0">Seleccione</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td></tr>';
		}
		elseif($_POST['plazausuario']==0 && $row['plaza']>0){
			echo '<tr><th align="left">Plaza</th><td><b>'.$array_plaza[$row['plaza']].'</b><input type="hidden" name="plaza" id="plaza" value="'.$row['plaza'].'"></td></tr>';
		}
		else{
			echo '<tr style="display:none;"><th align="left">Plaza</th><td><b>'.$array_plaza[$_POST['plazausuario']].'</b><input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'"></td></tr>';
		}
		echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"></td></tr>';
		echo '<tr><th align="left">Puesto</th><td><select name="puesto" id="puesto"><option value="0" precio="">Seleccione</option>';
		foreach($array_puestos as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['puesto']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Fecha Inicial</th><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.$row['fecha_ini'].'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Fecha Final</th><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.$row['fecha_fin'].'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Firma Recibos de Salida</th><td><input type="checkbox" name="recibo_salida" id="recibo_salida" class="textField" value="1"';
		if($row['recibo_salida']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr><th align="left">Firma Polizas</th><td><input type="checkbox" name="polizas" id="polizas" class="textField" value="1"';
		if($row['polizas']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr><th align="left">Firma Depositos</th><td><input type="checkbox" name="depositos" id="depositos" class="textField" value="1"';
		if($row['depositos']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr><th align="left">Firma Transferencias</th><td><input type="checkbox" name="transferencias" id="transferencias" class="textField" value="1"';
		if($row['transferencias']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr><th align="left">Firma Nomina</th><td><input type="checkbox" name="nomina" id="nomina" class="textField" value="1"';
		if($row['nomina']==1) echo ' checked';
		echo '></td></tr>';
		echo '</table>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'administradores.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		if($_POST['plazausuario']==0){
			echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza"><option value="all">Todas</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td></tr>';
		}
		else{
			echo '<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td></tr>';
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
			objeto.open("POST","administradores.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&plaza="+document.getElementById("plaza").value+"&nom="+document.getElementById("nom").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

