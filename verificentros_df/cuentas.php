<?php 

include ("main.php"); 


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM cuentas WHERE 1 ";
		if ($_POST['plaza']!="all") { $select.=" AND plaza='".$_POST['plaza']."' "; }
		if ($_POST['banco']!="") { $select.=" AND banco LIKE '%".$_POST['banco']."%' "; }
		if ($_POST['cuenta']!="") { $select.=" AND cuenta LIKE '%".$_POST['cuenta']."%' "; }
		$select.=" ORDER BY cuenta";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th>';
			if($_POST['plazausuario']==0) echo '<th>Plaza</th>';
			echo '<th>Cuenta</th><th>Banco</th><th>Folio Inicial</th><th>Folio Siguiente</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef['nombre'].'"></a></td>';
				if($_POST['plazausuario']==0) echo '<td>'.$array_plaza[$row['plaza']].'</td>';
				echo '<td>'.htmlentities($row['cuenta']).'</td>';
				echo '<td>'.htmlentities($row['banco']).'</td>';
				echo '<td align="right">'.htmlentities($row['folio_inicial']).'</td>';
				echo '<td align="right">'.htmlentities($row['folio_siguiente']).'</td>';
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
		$select=" SELECT * FROM cuentas WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		$cambios='';
		if($row['cuenta']!=$_POST['cuenta']){
			$cambios.='|Cuenta='.$row['cuenta'].'='.$_POST['cuenta'].'='.$_POST['cveusuario'].'='.fechaLocal().' '.horaLocal();
		}
		if($row['banco']!=$_POST['banco']){
			$cambios.='|Banco='.$row['banco'].'='.$_POST['banco'].'='.$_POST['cveusuario'].'='.fechaLocal().' '.horaLocal();
		}
		if($row['folio_inicial']!=$_POST['folio_inicial']){
			$cambios.='|Folio Inicial='.$row['folio_inicial'].'='.$_POST['folio_inicial'].'='.$_POST['cveusuario'].'='.fechaLocal().' '.horaLocal();
		}
		if($row['folio_siguiente']!=$_POST['folio_siguiente']){
			$cambios.='|Folio Siguiente='.$row['folio_siguiente'].'='.$_POST['folio_siguiente'].'='.$_POST['cveusuario'].'='.fechaLocal().' '.horaLocal();
		}
		
			//Actualizar el Registro
			$update = " UPDATE cuentas 
						SET cuenta='".$_POST['cuenta']."',banco='".$_POST['banco']."',folio_siguiente='".$_POST['folio_siguiente']."',
						copia='".$_POST['copia']."',folio_inicial='".$_POST['folio_inicial']."',cambios=CONCAT(cambios,'".$cambios."'),
						coorfecha='".$_POST['xfecha'].",".$_POST['yfecha']."',coorbeneficiario='".$_POST['xbenef'].",".$_POST['ybenef']."',
						coormonto='".$_POST['xmonto'].",".$_POST['ymonto']."',coormontoletra='".$_POST['xmontol'].",".$_POST['ymontol']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update) or die(mysql_error());			
	} else {
			//Insertar el Registro
			$insert = " INSERT cuentas SET plaza='".$_POST['plaza']."',cuenta='".$_POST['cuenta']."',banco='".$_POST['banco']."',
			fecha='".fechaLocal()."',hora='".horaLocal()."',folio_inicial='".$_POST['folio_inicial']."',folio_siguiente='".$_POST['folio_siguiente']."',
			coorfecha='".$_POST['xfecha'].",".$_POST['yfecha']."',coorbeneficiario='".$_POST['xbenef'].",".$_POST['ybenef']."',
			coormonto='".$_POST['xmonto'].",".$_POST['ymonto']."',coormontoletra='".$_POST['xmontol'].",".$_POST['ymontol']."',
			copia='".$_POST['copia']."',usuario='".$_POST['cveusuario']."'";
			$ejecutar = mysql_query($insert);
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM cuentas WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();
				if(document.forma.plaza.value==\'0\'){
					$(\'#panel\').hide();
					alert(\'Necesita seleccionar una plaza\');
				}
				else atcr(\'cuentas.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'cuentas.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Chequeras</td></tr>';
		echo '</table>';
		
		echo '<table>';
		if($_POST['plazausuario']>0){
			echo '<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
		}
		else{
			echo '<tr><th align="left">Plaza</th><td><select name="plaza" id="plaza" class="textField"><option value="0">---Seleccione---</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'"';
				if($row['plaza']==$k) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select></td></tr>';
		}
		echo '<tr><th align="left">Cuenta</th><td><input type="text" name="cuenta" id="cuenta" class="textField" size="20" value="'.$row['cuenta'].'"></td></tr>';
		echo '<tr><th align="left">Banco</th><td><input type="text" name="banco" id="banco" class="textField" size="50" value="'.$row['banco'].'"></td></tr>';
		echo '<tr><th align="left">Folio Inicial</th><td><input type="text" name="folio_inicial" id="folio_inicial" class="textField" size="10" value="'.$row['folio_inicial'].'"></td></tr>';
		echo '<tr><th align="left">Folio Siguiente</th><td><input type="text" name="folio_siguiente" id="folio_siguiente" class="textField" size="10" value="'.$row['folio_siguiente'].'"></td></tr>';
		echo '<tr><th align="left">Copia de poliza</th><td><input type="checkbox" name="copia" id="copia" class="textField" value="1"';
		if($row['copia']==1) echo ' checked';
		echo '></td></tr>';
		$datos=explode(",",$row['coorfecha']);
		echo '<tr><th align="left">Coordenadas Fecha</th><td><b>X:</b><input type="text" class="textField" name="xfecha" id="xfecha" size="5" value="'.$datos[0].'">&nbsp;
		<b>Y:</b><input type="text" class="textField" name="yfecha" id="yfecha" size="5" value="'.$datos[1].'"></td></tr>';
		$datos=explode(",",$row['coorbeneficiario']);
		echo '<tr><th align="left">Coordenadas Beneficiario</th><td><b>X:</b><input type="text" class="textField" name="xbenef" id="xbenef" size="5" value="'.$datos[0].'">&nbsp;
		<b>Y:</b><input type="text" class="textField" name="ybenef" id="ybenef" size="5" value="'.$datos[1].'"></td></tr>';
		$datos=explode(",",$row['coormonto']);
		echo '<tr><th align="left">Coordenadas Monto</th><td><b>X:</b><input type="text" class="textField" name="xmonto" id="xmonto" size="5" value="'.$datos[0].'">&nbsp;
		<b>Y:</b><input type="text" class="textField" name="ymonto" id="ymonto" size="5" value="'.$datos[1].'"></td></tr>';
		$datos=explode(",",$row['coormontoletra']);
		echo '<tr><th align="left">Coordenadas Monto Letra</th><td><b>X:</b><input type="text" class="textField" name="xmontol" id="xmontol" size="5" value="'.$datos[0].'">&nbsp;
		<b>Y:</b><input type="text" class="textField" name="ymontol" id="ymontol" size="5" value="'.$datos[1].'"></td></tr>';
		echo '</table>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'cuentas.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		if($_POST['plazausuario']>0){
			echo '<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
		}
		else{
			echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" class="textField"><option value="all">---Todas---</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td><td></td><td>&nbsp;</td></tr>';
		}
		echo '<tr><td>Cuenta</td><td><input type="text" name="cuenta" id="cuenta" size="20" class="textField" value=""></td></tr>';
		echo '<tr><td>Banco</td><td><input type="text" name="banco" id="banco" size="40" class="textField" value=""></td></tr>';
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
			objeto.open("POST","cuentas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&cuenta="+document.getElementById("cuenta").value+"&banco="+document.getElementById("banco").value+"&plaza="+document.getElementById("plaza").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

