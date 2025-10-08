<?php 

include ("main.php"); 
$array_estaus=array(1=>"Activo",2=>"Bloqueado");


/*** CONSULTA AJAX  **************************************************/
if($_POST['ajax']==2){
	$res=mysql_query("SELECT * FROM cat_rfc WHERE cve!='".$_POST['cliente']."' AND rfc='".$_POST['rfc']."'") or die(mysql_error());
	if(mysql_num_rows($res)>0){
		echo "si";
	}
	else{
		echo "no";
	}
	exit();
}
if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM cat_rfc WHERE 1 ";
		if ($_POST['estatus']!="") { $select.=" AND estatus= '".$_POST['estatus']."'"; }
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }

		$select.=" ORDER BY nombre";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th><th>Nombre</th><th>Fecha</th><th>RFC</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']==1){
					echo'<a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef['nombre'].'"></a>
					<a href="#" onClick="atcr(\'\',\'\',\'3\','.$row['cve'].')"><img src="images/basura.gif" border="0" title="Borrar '.$Benef['nombre'].'"></a>';
			}else{
				echo'Bloqueado';
			}
			echo'</td>';
				echo '<td>'.htmlentities($row['nombre']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' - '.$row['hora']).'</td>';
				echo '<td align="center">'.htmlentities($row['rfc']).'</td>';
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
if ($_POST['cmd']==3) {

			$update = " update cat_rfc set estatus='2' WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);			
	
	$_POST['cmd']=0;
}
if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE cat_rfc 
						SET nombre='".$_POST['nombre']."',rfc='".$_POST['rfc']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);			
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO cat_rfc 
						(nombre,rfc,fecha,hora,estatus)
						VALUES 
						('".$_POST['nombre']."','".$_POST['rfc']."','".fechaLocal()."','".horaLocal()."','1')";
			$ejecutar = mysql_query($insert);
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM cat_rfc WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();if(validarRFC()){validar_rfc_repetido(\''.$_POST['reg'].'\');} else{ $(\'#panel\').hide(); alert(\'RFC invalido\');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'cat_rfc.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion de RFC</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"></td></tr>';
		echo '<tr><th align="left">RFC</th><td><input type="text" name="rfc" id="rfc" class="textField" size="30" value="'.$row['rfc'].'"></td></tr>';
		echo '</table>';
		echo '<script>
			function validarRFC(){
				var ValidChars2 = "0123456789";
				var ValidChars1 = "abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ&";
				var cadena=document.getElementById("rfc").value;
				correcto = true;
				if(cadena.length!=13 && cadena.length!=12){
					correcto = false;
				}
				else{
					if(cadena.length==12)
						resta=1;
					else
						resta=0;
					for(i=0;i<cadena.length;i++) {
						digito=cadena.charAt(i);
						if (i<(4-resta) && ValidChars1.indexOf(digito) == -1){
							correcto = false;
						}
						if (i>=(4-resta) && i<(10-resta) && ValidChars2.indexOf(digito) == -1){
							correcto = false;
						}
						if (i>=(10-resta) && ValidChars1.indexOf(digito) == -1 && ValidChars2.indexOf(digito) == -1){
							correcto = false;
						}
					}
				}
				return correcto;
			}
			function validar_rfc_repetido(reg){
				
				$.ajax({
				  url: "cat_rfc.php",
				  type: "POST",
				  async: false,
				  data: {
					rfc: document.getElementById("rfc").value,
					cliente: reg,
					plazausuario: document.forma.plazausuario.value,
					ajax: 2
				  },
					success: function(data) {
						if(data == "no"){
							atcr(\'cat_rfc.php\',\'\',2,reg);
						}
						else{
							$(\'#panel\').hide();
							alert("Ya esta dado de alta el rfc");
						}
					}
				});
			}
			</script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'cat_rfc.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Estaus</td><td><select name="estatus" id="estatus">';
			foreach($array_estaus as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
			}
	echo '</select></td></tr>';
		echo '<tr><td>RFC</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td></tr>';
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
			objeto.open("POST","cat_rfc.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&estatus="+document.getElementById("estatus").value);
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

