<?php
include("main.php");

$array_bancos=array();
$bancos='';
$res = mysql_query("SELECT * FROM bancos ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_bancos[$row['cve']]=$row['nombre'];
	$bancos.='<option value="'.$row['cve'].'">'.$row['nombre'].'</option>';
}

$array_usocfdi=array();
$res = mysql_query("SELECT * FROM usocfdi_sat ORDER BY cve");
while($row=mysql_fetch_array($res)) $array_usocfdi[$row['cve']] = $row['cve'].' '.$row['nombre'];

$array_regimen=array();
$res = mysql_query("SELECT * FROM regimen_sat ORDER BY clave");
while($row=mysql_fetch_array($res)) $array_regimen[$row['clave']] = $row['clave'].' '.$row['nombre'];

if($_POST['cmd']==110){
	
	header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=Clientes.xls");
header("Pragma: no-cache");
header("Expires: 0");


//echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1" >';
/* 	echo '<!--<tr style="font-size:26px"><td align="center"></td></tr>-->
	<tr style="font-size:26px"><td align="center" colspan="5">Clientes</td></tr>
	<!--<tr style="font-size:24px">
			<td align="left" colspan="5" style="font-size:24px">Periodo  del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</td>
		 </tr>-->';
	echo '</table>';
	echo '<br>'; */
	
	$select="SELECT * FROM clientes WHERE plaza='".$_POST['plazausuario']."'";
	//if($_POST['cveusuario'] != 1) $select .= " AND usuario>=0";
	if($_POST['nombre']!="") $select.=" AND nombre LIKE '%".$_POST['nombre']."%'";
	if($_POST['rfc']!="") $select.=" AND rfc LIKE '%".$_POST['rfc']."%'";
	$select.=" ORDER BY nombre";
	$res=mysql_query($select);
	$totalRegistros = mysql_num_rows($res);
		
	if(mysql_num_rows($res)>0) 
	{
			
		echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="" id="tabla1">';
		echo '<thead>';
		echo '<tr bgcoor="#E9F2F8">';
		echo '<th>RFC</th>
		<th>NOMBRE</th>
		<th>E-MAIL</th>
		<th>CODIGO POSTAL</th>';
		echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
		$i=0;
		while($row=mysql_fetch_array($res)) {
				//rowb();
				$i++;
				//echo '<tr><td align="center" width="40" nowrap>'.$i.'</td>';
				echo '<td align="left">'.utf8_encode($row['nombre']).'</td>';
				echo '<td align="center">'.utf8_encode($row['rfc']).'</td>';	
				echo '<td align="center">'.utf8_encode($row['email']).'</td>';
				echo '<td align="center">'.utf8_encode($row['codigopostal']).'</td>';
//				echo '<td align="center">'.utf8_encode($row['calle'].' '.$row['numexterior'].' '.$row['numinterior'].' '.$row['colonia']).'</td>';
//				echo '<td align="center">'.utf8_encode($row['colonia']).'</td>';
//				echo '<td align="center">'.utf8_encode($row['municipio']).'</td>';
//				echo '<td align="center">'.utf8_encode($row['estado']).'</td>';
//				
				echo '</tr>';
		}
		
		echo '</tbody>
			
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


if($_POST['cmd']==2){
	$campos="";
	foreach($_POST['camposi'] as $k=>$v){
		$campos.=",".$k."='".addslashes($v)."'";
	}	
	if($_POST['reg']>0){
		mysql_query("UPDATE clientes SET fechayhora='".fechaLocal()." ".horaLocal()."',usuario='".$_SESSION['CveUsuario']."'".$campos." WHERE cve=".$_POST['reg']) or die(mysql_error());
		$id = $_POST['reg'];
		mysql_query("DELETE FROM clientes_cuentas WHERE cliente='$id'");
	}
	else{
		mysql_query("INSERT clientes SET plaza='".$_POST['plazausuario']."',fechayhora='".fechaLocal()." ".horaLocal()."',usuario='".$_SESSION['CveUsuario']."'".$campos) or die(mysql_error());
		$id=mysql_insert_id();
	}
	if(is_array($_POST['cuenta'])){
		foreach($_POST['cuenta'] as $k=>$v){
			if($v!=''){
				mysql_query("INSERT clientes_cuentas SET cliente='$id',banco='".$_POST['banco'][$k]."',cuenta='$v'");
			}
		}
	}
		$_POST['cmd']=0;
}
	
	
if($_POST['ajax']==1){
	$select="SELECT * FROM clientes WHERE plaza='".$_POST['plazausuario']."'";
	//if($_POST['cveusuario'] != 1) $select .= " AND usuario>=0";
	if($_POST['nombre']!="") $select.=" AND nombre LIKE '%".$_POST['nombre']."%'";
	if($_POST['rfc']!="") $select.=" AND rfc LIKE '%".$_POST['rfc']."%'";
	$select.=" ORDER BY nombre";
	$res=mysql_query($select);
	$totalRegistros = mysql_num_rows($res);
		
	if(mysql_num_rows($res)>0) 
	{
			
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
		echo '<thead><tr bgcolor="#E9F2F8"><td colspan="14">'.mysql_num_rows($res).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th>Editar</th>';
		echo '<th>Nombre</th>
		<th>RFC</th>
		<th>Uso CFDI</th>
		<th>Email</th>
		<!--<th>Domicilio</th>
		<th>Colonia</th>
		<th>Municipio</th>
		<th>Estado</th>
		<th>Codigo Postal</th>-->';
		echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
		$i=0;
		while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'clientes.php\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$row['nombre'].'"></a></td>';
				echo '<td align="left">';
				if($row['rfc']=="" || $row['nombre']=="" || $row['calle']=="" || $row['numexterior']=="" || $row['colonia']=="" || $row['municipio']=="" || $row['codigopostal']==""){
						echo '<font color="red">'.utf8_encode($row['nombre']).'</font></td>';
				}
				else{
					echo utf8_encode($row['nombre']).'</td>';	
				}
				echo '<td align="center">'.utf8_encode($row['rfc']).'</td>';
				echo '<td align="center">'.utf8_encode($array_usocfdi[$row['usocfdi']]).'</td>';
				echo '<td align="center">'.utf8_encode($row['email']).'</td>';
//				echo '<td align="center">'.utf8_encode($row['calle'].' '.$row['numexterior'].' '.$row['numinterior'].' '.$row['colonia']).'</td>';
//				echo '<td align="center">'.utf8_encode($row['colonia']).'</td>';
//				echo '<td align="center">'.utf8_encode($row['municipio']).'</td>';
//				echo '<td align="center">'.utf8_encode($row['estado']).'</td>';
//				echo '<td align="center">'.utf8_encode($row['codigopostal']).'</td>';
				echo '</tr>';
		}
		
		echo '</tbody>
			<tr>
			<td colspan="14" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
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

if($_POST['ajax']==2){
	$Repetir = mysql_fetch_array(mysql_query("SELECT repetir_rfc FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'"));
	if($Repetir[0]==1){
		echo 'no';
		exit();
	}
	$res=mysql_query("SELECT * FROM clientes WHERE plaza='".$_POST['plazausuario']."' AND cve!='".$_POST['cliente']."' AND rfc='".$_POST['rfc']."'");
	if(mysql_num_rows($res)>0){
		echo "si";
	}
	else{
		echo "no";
	}
	exit();
}
	
top($_SESSION);
if($_POST['cmd']==1){
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
				/*objeto=crearObjeto();
				if (objeto.readyState != 0) {
					alert("Error: El Navegador no soporta AJAX");
				} else {
					objeto.open("POST","clientes.php",true);
					objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto.send("ajax=2&rfc="+document.getElementById("rfc").value+"&cliente="+reg+"&plazausuario="+document.forma.plazausuario.value);
					objeto.onreadystatechange = function()
					{
						if (objeto.readyState==4)
						{
							if(objeto.responseText == "no"){
								atcr(\'clientes.php\',\'\',2,reg);
							}
							else{
								$(\'#panel\').hide();
								alert("Ya esta dado de alta el rfc");
							}
						}
					}
				}*/
				$.ajax({
				  url: "clientes.php",
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
							atcr(\'clientes.php\',\'\',2,reg);
						}
						else{
							$(\'#panel\').hide();
							alert("Ya esta dado de alta el rfc");
						}
					}
				});
			}
			
			function agregar_cuenta(){
				$("#cuentas").append(\'<tr>\
				<td align="center"><select name="banco[]"><option value="">Seleccione</option>'.$bancos.'</select></td>\
				<td align="center"><input type="text" class="textField" name="cuenta[]" value=""></td></tr>\');
			}
		</script>';
	$select=" SELECT * FROM clientes WHERE cve='".$_POST['reg']."'";
	$res=mysql_query($select);
	$row=mysql_fetch_array($res);
	//Menu
	echo '<table>';
	echo '
		<tr>';
	$nivelUsuario = nivelUsuario();
	//if($nivelUsuario>1){
	if($nivelUsuario>=2){
		echo '<td><a href="#" onClick="$(\'#panel\').show();if(validarRFC()){validar_rfc_repetido(\''.$_POST['reg'].'\');} else{ $(\'#panel\').hide(); alert(\'RFC invalido\');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	}
	echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'clientes.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
	echo '</tr>';
	echo '</table>';
	echo '<br>';
	$class='textField';
	$bloqueo = '';
	$res1=mysql_query("SELECT COUNT(cve) FROM facturas WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND cliente = '".$_POST['reg']."'");
	$row1=mysql_fetch_array($res1);
	if($_POST['reg'] > 0 && $row1[0]>0){
		$class='readOnly';
		$bloqueo=' readOnly';

	}

	echo '<table>';
	echo '<tr><td class="tableEnc">Edicion Datos de Clientes</td></tr>';
	echo '</table>';
	echo '<table width="100%"><tr><td>';
	echo '<table>';
	//echo '<tr><th align="left">Nombre</th><td><input type="text" class="'.$class.'" name="camposi[nombre]" id="nombre" value="'.htmlentities(utf8_encode($row['nombre'])).'" size="50" onKeyUp="this.value=this.value.toUpperCase();"'.$bloqueo.'></td></tr>';
	echo '<tr><th align="left">Nombre</th><td><input type="text" class="" name="camposi[nombre]" id="nombre" value="'.htmlentities(utf8_encode($row['nombre'])).'" size="50" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
	echo '<tr><th align="left">Email</th><td><input type="text" class="textField" name="camposi[email]" id="email" value="'.$row['email'].'" size="100"></td></tr>';
	//echo '<tr><th align="left">RFC</th><td><input type="text" class="'.$class.'" name="camposi[rfc]" id="rfc" value="'.$row['rfc'].'" size="15" maxlength="13" onKeyUp="this.value=this.value.toUpperCase();"'.$bloqueo.'></td></tr>';
	echo '<tr><th align="left">RFC</th><td><input type="text" class="" name="camposi[rfc]" id="rfc" value="'.$row['rfc'].'" size="15" maxlength="13" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
	echo '<tr';
	if($_POST['cveusuario'] != 1) echo ' style="display:none;"';
	echo '><th align="left">Autorizado para Facturar</th><td><input type="hidden" name="camposi[autorizado]" id="autorizado" value="'.$row['autorizado'].'">
	<input type="checkbox" id="chkautorizado" onClick="if(this.checked) $(\'#autorizado\').val(1); else $(\'#autorizado\').val(0);"';
	if($row['autorizado'] == 1) echo ' checked';
	echo '></td></tr>';
	echo '<tr';
	if($_POST['cveusuario'] != 1) echo ' style="display:none;"';
	echo '><th align="left">Factura sin ticket</th><td><input type="hidden" name="camposi[facturasinticket]" id="facturasinticket" value="'.$row['autorizado'].'">
	<input type="checkbox" id="chkfacturasinticket" onClick="if(this.checked) $(\'#facturasinticket\').val(1); else $(\'#facturasinticket\').val(0);"';
	if($row['facturasinticket'] == 1) echo ' checked';
	echo '></td></tr>';
	echo '<tr><th align="left">Credito</th><td><input type="hidden"  name="camposi[credito]" id="credito" value="'.$row['credito'].'" >
	<input type="checkbox" id="chkcredito" onClick="if(this.checked) $(\'#credito\').val(1); else $(\'#credito\').val(0);"';
	if($row['credito'] == 1) echo ' checked';
	echo '></td></tr>';
	echo '<tr><th align="left">Calle</th><td><input type="text" class="textField" name="camposi[calle]" id="calle" value="'.$row['calle'].'" size="30" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
	echo '<tr><th align="left">Numero Exterior</th><td><input type="text" class="textField" name="camposi[numexterior]" id="numexterior" value="'.$row['numexterior'].'" size="10" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
	echo '<tr><th align="left">Numero Interior</th><td><input type="text" class="textField" name="camposi[numinterior]" id="numinterior" value="'.$row['numinterior'].'" size="10" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
	echo '<tr><th align="left">Colonia</th><td><input type="text" class="textField" name="camposi[colonia]" id="colonia" value="'.$row['colonia'].'" size="30" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
	echo '<tr style="display:none;"><th align="left">Localidad</th><td><input type="text" class="textField" name="camposi[localidad]" id="localidad" value="'.$row['localidad'].'" size="50" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
	echo '<tr><th align="left">Municipio</th><td><input type="text" class="textField" name="camposi[municipio]" id="municipio" value="'.$row['municipio'].'" size="50" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
	echo '<tr><th align="left">Estado</th><td><input type="text" class="textField" name="camposi[estado]" id="estado" value="'.$row['estado'].'" size="50" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
	echo '<tr><th align="left">Codigo Postal</th><td><input type="text" class="textField" name="camposi[codigopostal]" id="codigopostal" value="'.$row['codigopostal'].'" size="50"></td></tr>';
	echo '<tr><th align="left">Uso CFDI</th><td><select name="camposi[usocfdi]" id="usocfdi"><option value="0">Seleccione</option>';
	foreach($array_usocfdi as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$row['usocfdi']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Regimen</th><td><select name="camposi[regimensat]" id="regimensat"><option value="0">Seleccione</option>';
	foreach($array_regimen as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$row['regimensat']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Cuentas<br><span style="cursor:pointer" onClick="agregar_cuenta()"><font color="BLUE">Agregar</font></span></th><td><table id="cuentas"><tr><th>Banco</th><th>Cuenta</th></tr>';
	$res1=mysql_query("SELECT * FROM clientes_cuentas WHERE cliente='".$_POST['reg']."'");
	while($row1=mysql_fetch_array($res1)){
		echo '<tr><td align="center"><select name="banco[]"><option value="">Seleccione</option>';
		foreach($array_bancos as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row1['banco']==$k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td><td align="center"><input type="text" class="textField" name="cuenta[]" value="'.$row1['cuenta'].'"></td></tr>';
	}
	echo '</table></td></tr>';
	echo '</table>';
	echo '</td></tr></table>';
	echo '<BR>';
	if($_SESSION['CveUsuario']!=1){
	echo'<Script language="javascript">
	if(document.getElementById("credito").value==1){
			document.getElementById("chkcredito").disabled = true;
	}
	
	</Script>';}
}	
if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;
			<a href="#" onClick="atcr(\'clientes.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo&nbsp;&nbsp;';
			
			if($_POST['cveusuario']==1){
			echo '<a href="#" onClick="atcr(\'\',\'_blank\',\'110\',\'0\');"><img src="images/b_print.png" border="0" title="Excel"></a>Excel';
			}
			
		 echo'
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" class="textField"></td></tr>'; 
	echo '<tr><td>RFC</td><td><input type="text" name="rfc" id="rfc" class="textField"></td></tr>'; 
	echo '</table>';

	echo '<br>';
	//Listado
	echo '<div id="Resultados">';
	echo '</div>';
	echo '
	<Script language="javascript">document.getElementById("myCheck").disabled = true;

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","clientes.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&rfc="+document.getElementById("rfc").value+"&nombre="+document.getElementById("nombre").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value);
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
	//window.onload = function () {
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	//}
	
	</Script>
';

}
bottom();
