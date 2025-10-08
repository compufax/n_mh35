<?php 

include ("main.php"); 
$res = mysql_query("SELECT * FROM areas");
while($row=mysql_fetch_array($res)){
	$array_localidad[$row['cve']]=$row['nombre'];
}

$array_tipo=array("Telefonista","Administrador");
		
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	
		$select= " SELECT a.*,b.numero FROM datosempresas a INNER JOIN plazas b ON b.cve = a.plaza WHERE 1";
		if ($_POST['nom']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nom']."%' "; }
		if ($_POST['sucursal']!="") { $select.=" AND a.nombre_callcenter LIKE '%".$_POST['sucursal']."%' "; }
		if ($_POST['maneja_web']!="all") $select.=" AND a.maneja_web='".$_POST['maneja_web']."'";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY a.nombre";
		$res=mysql_query($select);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="7">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Nombre</th><th>Localidad</th><th>Sucursal</th><th>Direccion</th><th>Citas Web</th><th>Numero Lineas</th></tr>';
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['plaza'].')"><img src="images/key.png" border="0" title="Editar '.$Usuario['nombre'].'"></a></td>';
				echo '<td>'.htmlentities(utf8_encode($row['numero'].' '.$row['nombre'])).$extra.'</td>';
				echo '<td>'.htmlentities(utf8_encode($array_localidad[$row['localidad_id']])).'</td>';
				echo '<td>'.htmlentities(utf8_encode($row['nombre_callcenter'])).$extra.'</td>';
				echo '<td>'.htmlentities(utf8_encode($row['direccion_callcenter'])).$extra.'</td>';
				echo '<td>'.htmlentities($array_nosi[$row['maneja_web']]).'</td>';
				echo '<td align="center">'.htmlentities($row['numero_lineas']).'</td>';
			}
			echo '	
				<tr>
				<td colspan="7" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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

/*** ELIMINAR REGISTRO  **************************************************/



/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	if($_POST['reg']) {
		$tiposc = '';
		if(count($_POST['tiposcombustible'])>0){
			foreach($_POST['tiposcombustible'] as $v){
				$tiposc .= $v;
			}
		}
		$select=" SELECT * FROM datosempresas WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		if($row['nombre_callcenter']!=$_POST['nombre_callcenter']){
			mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Sucursal',nuevo='".$_POST['nombre_callcenter']."',anterior='".$row['nombre_callcenter']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['numero_lineas']!=$_POST['numero_lineas']){
			mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Numero Lineas',nuevo='".$_POST['numero_lineas']."',anterior='".$row['numero_lineas']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['direccion_callcenter']!=$_POST['direccion_callcenter']){
			mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Direccion',nuevo='".$_POST['direccion_callcenter']."',anterior='".$row['direccion_callcenter']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['maneja_web']!=intval($_POST['maneja_web'])){
			mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Registro Web',nuevo='".intval($_POST['maneja_web'])."',anterior='".$row['maneja_web']."',arreglo='nosi',usuario='".$_POST['cveusuario']."'");
		}
		if($row['maneja_callcenter']!=intval($_POST['maneja_callcenter'])){
			mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='CallCenter',nuevo='".intval($_POST['maneja_callcenter'])."',anterior='".$row['maneja_callcenter']."',arreglo='nosi',usuario='".$_POST['cveusuario']."'");
		}
		if($row['horainicio']!=$_POST['horainicio']){
			mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Hora Inicio',nuevo='".$_POST['horainicio']."',anterior='".$row['horainicio']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['horafin']!=$_POST['horafin']){
			mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Hora Fin',nuevo='".$_POST['horafin']."',anterior='".$row['horafin']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['minutos']!=intval($_POST['minutos'])){
			mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Minutos',nuevo='".intval($_POST['minutos'])."',anterior='".$row['minutos']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['call_emails']!=$_POST['call_emails']){
			mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Correos Electronicos',nuevo='".$_POST['call_emails']."',anterior='".$row['call_emails']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['tipocombustible']!=$tiposc){
			mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Tipo Combustible',nuevo='".$tiposc."',anterior='".$row['tipocombustible']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($_POST['fechalimiteweb']=='') $_POST['fechalimiteweb']='0000-00-00';
		if($row['fechalimiteweb']!=$_POST['fechalimiteweb']){
			mysql_query("INSERT call_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Fecha Limite Web',nuevo='".$_POST['fechalimiteweb']."',anterior='".$row['fechalimiteweb']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		//Actualizar el Registro
			$update = " UPDATE datosempresas 
					SET nombre_callcenter='".$_POST['nombre_callcenter']."',numero_lineas='".$_POST['numero_lineas']."',
					maneja_callcenter='".$_POST['maneja_callcenter']."',direccion_callcenter='".$_POST['direccion_callcenter']."',
					horainicio='".$_POST['horainicio']."',horafin='".$_POST['horafin']."',minutos='".$_POST['minutos']."',tipocombustible='$tiposc',
					call_emails='".$_POST['call_emails']."',maneja_web='".$_POST['maneja_web']."',fechalimiteweb='".$_POST['fechalimiteweb']."'
					WHERE plaza='".$_POST['reg']."' " ;
		$ejecutar = mysql_query($update) or die(mysql_error());
	} 
	
	
	$_POST['cmd']=0;
}


/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM datosempresas WHERE plaza='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		$res1=mysql_query("SELECT numero FROM plazas WHERE cve='".$_POST['reg']."'");
		$row1=mysql_fetch_array($res1);
		//Menu
		echo '<table>';
		echo '
			<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'call_sucursales.php\',\'\',\'2\',\''.$_POST['reg'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '
			<td><a href="#" onClick="$(\'#panel\').show();atcr(\'call_sucursales.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Sucursales</td></tr>';
		echo '</table>';

		//Formulario 
		echo '<table>';
		echo '<tr><th align="left">Nombre Plaza</th><td><input type="text" name="nombre" id="nombre" value="'.$row1['numero'].'" size="20" class="readOnly" readOnly></td></tr>';
		echo '<tr><th align="left">Nombre Plaza</th><td><input type="text" name="nombre" id="nombre" value="'.$row['nombre'].'" size="100" class="readOnly" readOnly></td></tr>';
		echo '<tr><th align="left">Nombre Plaza</th><td><input type="text" name="nombre" id="nombre" value="'.$array_localidad[$row['localidad_id']].'" size="50" class="readOnly" readOnly></td></tr>';
		echo '<tr><th align="left">Nombre Sucursal</th><td><input type="text" name="nombre_callcenter" id="nombre_callcenter" value="'.$row['nombre_callcenter'].'" size="100" class="textField"></td></tr>';
		echo '<tr><th align="left">Direccion Sucursal</th><td><input type="text" name="direccion_callcenter" id="direccion_callcenter" value="'.$row['direccion_callcenter'].'" size="100" class="textField"></td></tr>';
		echo '<tr><th align="left">Numero Lineas</th><td><input type="text" name="numero_lineas" id="numero_lineas" value="'.$row['numero_lineas'].'" size="10" class="textField"></td></tr>';
		echo '<tr style="display:none;"><th align="left">CallCenter</th><td><input type="checkbox" name="maneja_callcenter" id="maneja_callcenter" value="1"';
		if($row['maneja_callcenter']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr><th align="left">Citas Web</th><td><input type="checkbox" name="maneja_web" id="maneja_web" value="1"';
		if($row['maneja_web']==1) echo ' checked';
		echo '></td></tr>';
		if($row['fechalimiteweb']=='0000-00-00') $row['fechalimiteweb']='';
		echo '<tr><th align="left">Fecha Limite Web</th><td><input type="text" name="fechalimiteweb" id="fechalimiteweb" value="'.$row['fechalimiteweb'].'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fechalimiteweb,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Hora Inicio</th><td><input type="text" class="textFiedl" name="horainicio"  id="horainicio" value="'.$row['horainicio'].'" size="10"><b>hh:mm</b></td></tr>';
		echo '<tr><th align="left">Hora Fin</th><td><input type="text" class="textFiedl" name="horafin"  id="horafin" value="'.$row['horafin'].'" size="10"><b>hh:mm</b></td></tr>';
		echo '<tr><th align="left">Minutos</th><td><input type="text" class="textFiedl" name="minutos"  id="minutos" value="'.$row['minutos'].'" size="10"></td></tr>';
		echo '<tr><th align="left">Correos Electronicos Reporte</th><td><input type="text" class="textField" size="100" value="'.$row['call_emails'].'" name="call_emails" id="call_emails"></td></tr>';
		echo '<tr><th align="left">Gasolina</th><td><input type="checkbox" name="tiposcombustible[]" id="tipocompustible_1" value="|1|"';
		if(strpos($row['tipocombustible'],'|1|') !== false) echo ' checked';
		echo '></td></tr>';
		echo '<tr><th align="left">Gas</th><td><input type="checkbox" name="tiposcombustible[]" id="tipocompustible_2" value="|2|"';
		if(strpos($row['tipocombustible'],'|2|') !== false) echo ' checked';
		echo '></td></tr>';
		echo '<tr><th align="left">Diesel</th><td><input type="checkbox" name="tiposcombustible[]" id="tipocompustible_3" value="|3|"';
		if(strpos($row['tipocombustible'],'|3|') !== false) echo ' checked';
		echo '></td></tr>';
		echo '</table>';
		
		
		
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
		echo '<tr><td>Nombre Plaza</td><td><input type="text" name="nom" id="nom" size="20" class="textField"></td></tr>';		
		echo '<tr><td>Sucursal</td><td><input type="text" name="sucursal" id="sucursal" size="20" class="textField"></td></tr>';		
		echo '<tr><td>Citas Web</td><td><select name="maneja_web" id="maneja_web"><option value="all" selected>Todos</option>';
		foreach($array_nosi as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
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
					objeto.open("POST","call_sucursales.php",true);
					objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&sucursal="+document.getElementById("sucursal").value+"&maneja_web="+document.getElementById("maneja_web").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

