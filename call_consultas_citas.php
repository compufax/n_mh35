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
	
		$select= " SELECT * FROM call_citas WHERE 1 ";
		if($_POST['folio']!=''){
			$select .=" AND cve='".$_POST['folio']."'";
		}
		elseif($_POST['placa']!=''){
			$select .=" AND placa='".$_POST['placa']."'";
		}
		else{
			if($_POST['fecha_ini']!='') $select.=" AND fecha>='".$_POST['fecha_ini']."'";
			if($_POST['fecha_fin']!='') $select.=" AND fecha<='".$_POST['fecha_fin']."'";
			if ($_POST['plaza']!="all") $select.=" AND plaza='".$_POST['plaza']."'";
			if ($_POST['engomado']!="all") $select.=" AND engomado='".$_POST['engomado']."'";
			if ($_POST['mostrar']!="all"){
				if($_POST['mostrar']==1){
					$select.=" AND cvecobro>0";
				}
				elseif($_POST['mostrar']==2){
					$select.=" AND CONCAT(fecha,' ',hora)<NOW() AND cvecobro=0";
				}
				elseif($_POST['mostrar']==3){
					$select.=" AND estatus='C'";
				}
			}
		}
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY cve DESC";
		$res=mysql_query($select);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Centro de Verificacion</th><th>Nombre</th><th>Placa</th><th>Tarjeta de Circulacion</th><th>Marca</th><th>Modelo</th><th>A&ntilde;o</th><th>Tipo de Verificacion</th><th>Usuario</th></tr>';
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'CANCELADO';
				}
				elseif($row['fecha']>fechaLocal()){
					echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar la cita?\')){ atcr(\'\',\'\',\'3\','.$row['cve'].');}"><img src="images/validono.gif" border="0" title="Editar '.$Usuario['nombre'].'"></a>';	
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

if($_POST['cmd']==3){
	mysql_query("UPDATE call_citas SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['reg']."'");
	mysql_query("INSERT INTO call_precitas (rfc, nombre, email, calle, numexterior, numinterior, colonia, municipio, estado, codigopostal, placa, tarjeta_circulacion, engomado, marca, modelo, anio, requiere_factura, recordatorio) 
	select rfc, nombre, email, calle, numexterior, numinterior, colonia, municipio, estado, codigopostal, placa, tarjeta_circulacion, engomado, marca, modelo, anio, requiere_factura, recordatorio from call_citas WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

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
		echo '<tr><td>Folio</td><td><input type="text" name="folio" id="folio" size="10" class="textField"></td></tr>';		
		echo '<tr><td>Placa</td><td><input type="text" name="placa" id="placa" size="10" class="textField placas"></td></tr>';		
		echo '<tr><td>Tipo de Certificado</td><td><select name="engomado" id="engomado"><option value="all">Todos</option>';
		$res=mysql_query("SELECT * FROM areas ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			echo '<optgroup label="'.$row['nombre'].'">';
			$res1=mysql_query("SELECT * FROM engomados WHERE localidad='".$row['cve']."' AND mostrar_registro=1");
			while($row1=mysql_fetch_array($res1)){
				echo '<option value="'.$row1['cve'].'">'.$row1['nombre'].'</option>';
			}
			echo '</optgroup>'; 
		}
		echo '</select></td></tr>';
		echo '<tr><td>Centro de Verificacion</td><td><select name="plaza" id="plaza"><option value="all">Todos</option>';
		foreach($array_plazas as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Mostrar</td><td><select name="mostrar" id="mostrar"><option value="all" selected>Todos</option>';
		echo '<option value="1">Realizadas</option>';
		echo '<option value="2">No Realizadas</option>';
		echo '<option value="3">Canceladas</option>';
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
					objeto.open("POST","call_consultas_citas.php",true);
					objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&folio="+document.getElementById("folio").value+"&placa="+document.getElementById("placa").value+"&plaza="+document.getElementById("plaza").value+"&engomado="+document.getElementById("engomado").value+"&mostrar="+document.getElementById("mostrar").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

