<?php 
//echo '<h1>PROYECTO CALLCENTER WEB 2014 A TERMINADO</h1>';
//exit();
include("main2.php");
$res = mysql_query("SELECT * FROM engomados ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
}

$array_tipocombustible = array(1=>"Gasolina",2=>"Gas",3=>"Diesel");
$_POST['cveusuario']=-1;
if($_POST['cmd']==10){
	$res = mysql_query("SELECT * FROM call_citas WHERE cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	$Plaza=mysql_fetch_array(mysql_query("SELECT * FROM datosempresas WHERE plaza='".$row['plaza']."'"));
	echo '<div align="center">';
	echo '<h1>Registro Nuevo</h1>';
	echo '<table style="font-size:15px">';
	echo '<tr><th align="left">Folio</th><td>'.$row['cve'].'</td></tr>';
	echo '<tr><th align="left">Centro de verificaci&oacute;n</td><td>'.$Plaza['nombre_callcenter'].'</td></tr>';
	echo '<tr><th align="left">Direcci&oacute;n</td><td>'.$Plaza['direccion_callcenter'].'</td></tr>';
	echo '<tr><th align="left">Fecha cita</th><td>'.diaSemana($row['fecha']).' '.fecha_letra($row['fecha']).'</td></tr>';
	echo '<tr><th align="left">Hora cita</th><td>'.substr($row['hora'],0,5).'</td></tr>';
	echo '<tr><th align="left">Correo electr&oacute;nico</th><td>'.$row['email'].'</td></tr>';
	echo '<tr><th align="left">Nombre</th><td>'.$row['nombre'].'</td></tr>';
	echo '<tr><th align="left">Placa</th><td>'.$row['placa'].'</td></tr>';
	echo '<tr><th align="left">Numero tarjeta circulaci&oacute;n</th><td>'.$row['tarjeta_circulacion'].'</td></tr>';
	echo '<tr><th align="left">Engomado</th><td>'.$array_engomado[$row['engomado']].'</td></tr>';
	echo '<tr><th align="left">Marca</th><td>'.$row['marca'].'</td></tr>';
	echo '<tr><th align="left">Submarca</th><td>'.$row['modelo'].'</td></tr>';
	echo '<tr><th align="left">Modelo (a&ntilde;o)</th><td>'.$row['anio'].'</td></tr>';
	echo '<tr><th align="left">Requiere Factura</th><td>'.$array_nosi[$row['requiere_factura']].'</td></tr>'; 
	if($row['requiere_factura']==1){
		echo '<tr><th align="left">RFC</th><td>'.$row['rfc'].'</td></tr>';
		echo '<tr><th align="left">Correo electr&oacute;nico</th><td>'.$row['email'].'</td></tr>';
		echo '<tr><th align="left">Calle</th><td>'.$row['calle'].'</td></tr>';
		echo '<tr><th align="left">N&uacute;mero exterior</th><td>'.$row['numexterior'].'</td></tr>';
		echo '<tr><th align="left">N&uacute;mero interior</th><td>'.$row['numinterior'].'</td></tr>';
		echo '<tr><th align="left">Colonia</th><td>'.$row['colonia'].'</td></tr>';
		echo '<tr><th align="left">Municipio</th><td>'.$row['municipio'].'</td></tr>';
		echo '<tr><th align="left">Estado</th><td>'.$row['estado'].'</td></tr>';
		echo '<tr><th align="left">C&oacute;digo Postal</th><td>'.$row['codigopostal'].'</td></tr>';
	}	
	echo '<tr><th align="left">C&oacute;digo de Transacci&oacute;n</th><td>'.$row['codigoverificacion'].'</td></tr>';
	echo '</table>';

	echo '</div>';
	
	echo '<script>window.print();</script>';
	exit();
}

if($_POST['ajax']==1) {
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
		$select= " SELECT * FROM call_citas WHERE placa='".$_POST['placa']."'";
		
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY cve DESC";
		$res=mysql_query($select);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Sucursal</th><th>Nombre</th><th>Placa</th><th>Tarjeta de Circulacion</th><th>Marca</th><th>Submarca</th><th>A&ntilde;o</th><th>Tipo de Verificacion</th></tr>';
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'CANCELADO';
				}
				elseif($row['fecha']>fechaLocal()){
					echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar la cita?\')){ resp=prompt(\'Capture el codigo de transaccion de la cita:\');atcr(\'registro_web.php?codigo=\'+resp,\'\',\'13\','.$row['cve'].');}"><img src="images/validono.gif" border="0" title="Cancelar"></a>';	
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


if($_POST['ajax']==3){
	$res = mysql_query("SELECT * FROM call_precitas WHERE placa='".$_POST['placa']."' ORDER BY cve DESC LIMIT 1");
	if($row=mysql_fetch_array($res)){
		echo $row['nombre'].'|'.$row['email'].'|'.$row['tarjeta_circulacion'].'|'.$row['engomado'].'|'.$row['marca'].'|'.$row['modelo'].'|'.$row['anio'].'|'.$row['recordatorio'].'|'.$row['requiere_factura'].'|'.$row['rfc'].'|'.$row['calle'].'|'.$row['numexterior'].'|'.$row['numinterior'].'|'.$row['colonia'].'|'.$row['municipio'].'|'.$row['estado'].'|'.$row['codigopostal'];
	}
	exit();
}

top();

if($_POST['cmd']==13){
	$res = mysql_query("SELECT * FROM call_citas WHERE cve='".$_POST['reg']."' AND codigoverificacion='".$_GET['codigo']."'");
	if($row=mysql_fetch_array($res)){
		mysql_query("UPDATE call_citas SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['reg']."'");
		mysql_query("INSERT INTO call_precitas (rfc, nombre, email, calle, numexterior, numinterior, colonia, municipio, estado, codigopostal, placa, tarjeta_circulacion, engomado, marca, modelo, anio, requiere_factura, recordatorio) 
		select rfc, nombre, email, calle, numexterior, numinterior, colonia, municipio, estado, codigopostal, placa, tarjeta_circulacion, engomado, marca, modelo, anio, requiere_factura, recordatorio from call_citas WHERE cve='".$_POST['reg']."'");
		echo '<script>alert("La cita se cancelo correctamente");</script>';
	}
	else{
		echo '<script>alert("Error en el codigo de transaccion");</script>';
	}
	$_POST['cmd']=11;
}

if ($_POST['cmd']==11) {
	echo '<div align="right"><input type="button" value="Reservar Cita" style="font-size:20px" onClick="atcr(\'registro_web.php\',\'\',0,0);"></div>';
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="if(document.forma.placa.value==\'\') alert(\'Necesita ingresar la placa\'); else buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	
	echo '<table>';
	echo '<tr><td>Placa</td><td><input type="text" name="placa" id="placa" size="10" class="textField placas"></td></tr>';		
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
				objeto.open("POST","registro_web.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=1&placa="+document.getElementById("placa").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
		
		//buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
		</Script>';
}
else{
	echo '<div align="right"><input type="button" value="Consulta de Cita" style="font-size:20px" onClick="atcr(\'registro_web.php\',\'\',11,0);"></div>';

	if($_POST['cmd']==3){
		if($_POST['numeroverificacion1']!="" && $_POST['numeroverificacion1']==$_POST['numeroverificacion2']){
			$res = mysql_query("SELECT a.cve,a.modelo,a.engomado FROM call_citas a WHERE a.placa='".$_POST['camposi']['placa']."' AND a.fecha>'".fechaLocal()."' AND a.estatus!='C'");
			if(mysql_num_rows($res)==0){
				$Plaza=mysql_fetch_array(mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plaza']."'"));
				$res=mysql_query("SELECT COUNT(cve) FROM call_citas WHERE plaza='".$_POST['plaza']."' AND fecha='".$_POST['camposi']['fecha']."' AND hora='".$_POST['camposi']['hora']."' AND estatus!='C'");
				$row=mysql_fetch_array($res);
				$disponibles = $Plaza['numero_lineas']-$row[0];
				if($disponibles>0){
					$campos="";
					foreach($_POST['camposi'] as $k=>$v){
						$campos.=",".$k."='".$v."'";
					}	
					mysql_query("INSERT call_citas SET codigoverificacion='".sprintf('%05s',rand(0,99999))."',recordatorio='".$_POST['recordatorio']."',fechayhora='".fechaLocal()." ".horaLocal()."',usuario='".$_POST['cveusuario']."',estatus='A',ip='".getRealIP()."'".$campos) or die(mysql_error());
					$cita_id=mysql_insert_id();
					mysql_query("DELETE FROM call_precitas WHERE placa='".$_POST['camposi']['placa']."'");
			
					$res = mysql_query("SELECT * FROM call_citas WHERE cve='".$cita_id."'");
					$row=mysql_fetch_array($res);
					$Plaza=mysql_fetch_array(mysql_query("SELECT * FROM datosempresas WHERE plaza='".$row['plaza']."'"));
					$html='';
					$html.= '<div align="center">';
					$html.= '<h1>Registro Nuevo</h1>';
					$html.= '<table style="font-size:15px">';
					$html.= '<tr><th align="left">Folio</th><td>'.$row['cve'].'</td></tr>';
					$html.= '<tr><th align="left">Centro de verificaci&oacute;n</td><td>'.$Plaza['nombre_callcenter'].'</td></tr>';
					$html.= '<tr><th align="left">Direcci&oacute;n</td><td>'.$Plaza['direccion_callcenter'].'</td></tr>';
					$html.= '<tr><th align="left">Fecha cita</th><td>'.diaSemana($row['fecha']).' '.fecha_letra($row['fecha']).'</td></tr>';
					$html.= '<tr><th align="left">Hora cita</th><td>'.substr($row['hora'],0,5).'</td></tr>';
					$html.= '<tr><th align="left">Correo electr&oacute;nico</th><td>'.$row['email'].'</td></tr>';
					$html.= '<tr><th align="left">Nombre</th><td>'.$row['nombre'].'</td></tr>';
					$html.= '<tr><th align="left">Placa</th><td>'.$row['placa'].'</td></tr>';
					$html.= '<tr><th align="left">Numero tarjeta circulaci&oacute;n</th><td>'.$row['tarjeta_circulacion'].'</td></tr>';
					$html.= '<tr><th align="left">Engomado</th><td>'.$array_engomado[$row['engomado']].'</td></tr>';
					$html.= '<tr><th align="left">Marca</th><td>'.$row['marca'].'</td></tr>';
					$html.= '<tr><th align="left">Submarca</th><td>'.$row['modelo'].'</td></tr>';
					$html.= '<tr><th align="left">Modelo (a&ntilde;o)</th><td>'.$row['anio'].'</td></tr>';
					$html.= '<tr><th align="left">Requiere Factura</th><td>'.$array_nosi[$row['requiere_factura']].'</td></tr>'; 
					if($row['requiere_factura']==1){
						$html.= '<tr><th align="left">RFC</th><td>'.$row['rfc'].'</td></tr>';
						$html.= '<tr><th align="left">Correo electr&oacute;nico</th><td>'.$row['email'].'</td></tr>';
						$html.= '<tr><th align="left">Calle</th><td>'.$row['calle'].'</td></tr>';
						$html.= '<tr><th align="left">N&uacute;mero exterior</th><td>'.$row['numexterior'].'</td></tr>';
						$html.= '<tr><th align="left">N&uacute;mero interior</th><td>'.$row['numinterior'].'</td></tr>';
						$html.= '<tr><th align="left">Colonia</th><td>'.$row['colonia'].'</td></tr>';
						$html.= '<tr><th align="left">Municipio</th><td>'.$row['municipio'].'</td></tr>';
						$html.= '<tr><th align="left">Estado</th><td>'.$row['estado'].'</td></tr>';
						$html.= '<tr><th align="left">C&oacute;digo Postal</th><td>'.$row['codigopostal'].'</td></tr>';
					}	
					$html.= '<tr><th align="left">C&oacute;digo de Transacci&oacute;n</th><td>'.$row['codigoverificacion'].'</td></tr>';
					$html.= '</table>';

					$html.= '</div>';
					if(trim($row['email'])!=""){
						require_once("phpmailer/class.phpmailer.php");
						$mail = new PHPMailer();
						$mail->Host = "localhost";
						$mail->From = "verificentros@verificentros.net";
						$mail->FromName = "Verificentros Sucursal ".$Plaza['nombre_callcenter'];
						$mail->Subject = "Cita para Verificacion";
						$mail->IsHTML(true);
						$mail->Body = $html;
						$mail->AddAddress(trim($row['email']));
						$mail->Send();
					}
					echo '<script>atcr("registro_web.php","",10,'.$cita_id.');</script>';
				}
				else{
					$campos="";
					foreach($_POST['camposi'] as $k=>$v){
						$campos.=",".$k."='".$v."'";
					}	
					mysql_query("INSERT call_precitas SET recordatorio='".$_POST['recordatorio']."',fechayhora='".fechaLocal()." ".horaLocal()."',usuario='".$_POST['cveusuario']."',estatus='A',ip='".getRealIP()."'".$campos) or die(mysql_error());
					echo '<script>alert("Lo sentimos pero no hay disponibilidad en el horario seleccionado");</script>';
				}
			}
			else{
				echo '<script>alert("La placa ya tiene un registro");</script>';
			}
		}
		$_POST['cmd']=0;
	}

	if($_POST['cmd']==2){
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
			
				function validar(){
					if(document.getElementById("plaza").value==""){
						$(\'#panel\').hide();
						alert("Necesita seleccionar la sucursal");
					}
					else if(document.getElementById("nombre").value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar el nombre");
					}
					else if(document.getElementById("placa").value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar la placa");
					}
					/*else if(document.getElementById("email").value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar el email");
					}*/
					else if(document.getElementById("email").value!="" && document.getElementById("confirmacionemail").value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar la confirmacion email");
					}
					else if(document.getElementById("email").value!="" && document.getElementById("confirmacionemail").value!=document.getElementById("email").value){
						$(\'#panel\').hide();
						alert("No son iguales los emails");
					}
					else if(document.getElementById("tarjeta_circulacion").value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar el numero de tarjeta de circulacion");
					}
					else if(document.getElementById("engomado").value=="0"){
						$(\'#panel\').hide();
						alert("Necesita seleccionar el engomado");
					}
					else if(document.getElementById("marca").value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar la marca");
					}
					else if(document.getElementById("modelo").value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar el modelo");
					}
					else if(document.getElementById("anio").value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar el año");
					}
					else if(document.forma.numeroverificacion2.value==""){
						$(\'#panel\').hide();
						alert("Necesita ingresar el codigo  de verificacion");
					}
					else if(document.forma.numeroverificacion2.value!=document.forma.numeroverificacion1.value){
						$(\'#panel\').hide();
						alert("Error en el codigo de verificacion");
					}
					else{
						atcr("registro_web.php","",3,0);
					}
				}
			
				function agregar_cuenta(){
					$("#cuentas").append(\'<tr>\
					<td align="center"><select name="banco[]"><option value="">Seleccione</option>'.$bancos.'</select></td>\
					<td align="center"><input type="text" class="textField" name="cuenta[]" value=""></td></tr>\');
				}
			</script>';
	
		//Menu
		echo '<table>';
		echo '
			<tr>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();if(document.getElementById(\'requiere_factura_1\').checked == false || validarRFC()){validar(\'\');} else{ $(\'#panel\').hide(); alert(\'RFC invalido\');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<br>';
	
		echo '<table>';
		echo '<tr><td class="tableEnc">Registro Nuevo</td></tr>';
		echo '</table>';
		$Plaza=mysql_fetch_array(mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plaza']."'"));
		$array_engomado = array();
		$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND mostrar_registro=1 ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			$array_engomado[$row['cve']]=$row['nombre'];
		}
		echo '<input type="hidden" name="camposi[localidad_plaza]" value="'.$Plaza['localidad_id'].'">';
		echo '<input type="hidden" name="plaza" value="'.$Plaza['plaza'].'">';
		echo '<table width="100%"><tr><td>';
		echo '<table>';
		echo '<tr><th align="left">Centro de verificaci&oacute;n</td><td><input type="hidden" name="camposi[plaza]" id="plaza" value="'.$_POST['plaza'].'"><input type="text" name="nomplaza" id="nomplaza" class="readOnly" size="50" value="'.$Plaza['nombre_callcenter'].'" readOnly></td></tr>';
		echo '<tr><th align="left">Direcci&oacute;n</td><td><input type="text" class="readOnly dirsucursales" size="100" value="'.$Plaza['direccion_callcenter'].'" readOnly></td></tr>';
		echo '<tr><th align="left">Fecha cita</th><td><input type="hidden" name="camposi[fecha]" id="fecha" value="'.substr($_POST['reg'],0,10).'">
		<input type="text" class="readOnly" size="50" id="nomfecha" value="'.diaSemana(substr($_POST['reg'],0,10)).' '.fecha_letra(substr($_POST['reg'],0,10)).'" readOnly></td></tr>';
		echo '<tr><th align="left">Hora cita</th><td><input type="hidden" name="camposi[hora]" id="hora" value="'.substr($_POST['reg'],11,8).'">
		<input type="text" class="readOnly" size="50" id="nomhora" value="'.substr($_POST['reg'],11,5).'" readOnly></td></tr>';
		echo '<tr><th align="left">Nombre</th><td><input type="text" class="textField" name="camposi[nombre]" id="nombre" value="" size="50" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
		echo '<tr><th align="left">Placa</th><td><input type="text" class="textField placas" name="camposi[placa]" id="placa" value="" size="15" maxlength="13" onKeyUp="if(event.keyCode==13){ traeRegistro();}else{this.value=this.value.toUpperCase();}"><b><font color="RED">Pulsar enter despu&eacute;s de capturar la placa</font></b></td></tr>';
		echo '<tr><th align="left">Correo electr&oacute;nico</th><td><input type="text" class="textField" name="camposi[email]" id="email" value="" size="100"></td></tr>';
		echo '<tr><th align="left">Confirmaci&oacute;n correo electr&oacute;nico</th><td><input type="text" class="textField" id="confirmacionemail" value="" size="100"><br>En caso de no encontrar el correo en su bandeja de entrada buscarlo en correo no deseado(spam)</td></tr>';
		echo '<tr><th align="left">Numero tarjeta circulaci&oacute;n</th><td><input type="text" class="textField" name="camposi[tarjeta_circulacion]" id="tarjeta_circulacion" value="" size="20" maxlength="30" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
		echo '<tr><th align="left">Engomado</th><td><input type="hidden" name="camposi[engomado]" id="engomado" value="0">';
		$i=0;
		foreach($array_engomado as $k=>$v){
			if($i==4){
				echo '<br>';
				$i=0;
			}
			echo '<input type="radio" name="auxengomado" id="auxengomado_'.$k.'" value="'.$k.'" onClick="if(this.checked){document.getElementById(\'engomado\').value=this.value;}"';
			if($row['engomado']==$k) echo ' checked';
			echo '>'.$v.'&nbsp;&nbsp;&nbsp;';
			$i++;
		}
		echo '</td></tr>';
		echo '<tr><th align="left">Marca</th><td><input type="text" class="textField" name="camposi[marca]" id="marca" value="" size="20" maxlength="30" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
		echo '<tr><th align="left">Submarca</th><td><input type="text" class="textField" name="camposi[modelo]" id="modelo" value="" size="10" maxlength="30" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
		echo '<tr><th align="left">Modelo (a&ntilde;o)</th><td><input type="text" class="textField" name="camposi[anio]" id="anio" value="" size="10" maxlength="30" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
		echo '<tr><th align="left" style="font-size:15px">¿Le gustar&iacute;a que se le avisara<br> para su pr&oacute;xima verificaci&oacute;n?</th><td><input type="checkbox" name="recordatorio" id="recordatorio" value="1"></td></tr>';
		echo '<tr><th align="left" style="color:#FF0000">Requiere Factura</th><td><input type="radio" name="camposi[requiere_factura]" id="requiere_factura_0" value="0" onClick="$(\'.cfactura\').hide()" checked>No&nbsp;&nbsp;
		<input type="radio" name="camposi[requiere_factura]" id="requiere_factura_1" value="1" onClick="$(\'.cfactura\').show()">Si</td></tr>'; 
		echo '<tr style="display:none;" class="cfactura"><th align="left">RFC</th><td><input type="text" class="textField" name="camposi[rfc]" id="rfc" value="" size="15" maxlength="13" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
		echo '<tr style="display:none;" class="cfactura"><th align="left">Calle</th><td><input type="text" class="textField" name="camposi[calle]" id="calle" value="'.$row['calle'].'" size="30" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
		echo '<tr style="display:none;" class="cfactura"><th align="left">N&uacute;mero exterior</th><td><input type="text" class="textField" name="camposi[numexterior]" id="numexterior" value="'.$row['numexterior'].'" size="10" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
		echo '<tr style="display:none;" class="cfactura"><th align="left">N&uacute;mero interior</th><td><input type="text" class="textField" name="camposi[numinterior]" id="numinterior" value="'.$row['numinterior'].'" size="10" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
		echo '<tr style="display:none;" class="cfactura"><th align="left">Colonia</th><td><input type="text" class="textField" name="camposi[colonia]" id="colonia" value="'.$row['colonia'].'" size="30" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
		echo '<tr style="display:none;"><th align="left">Localidad</th><td><input type="text" class="textField" name="camposi[localidad]" id="localidad" value="'.$row['localidad'].'" size="50" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
		echo '<tr style="display:none;" class="cfactura"><th align="left">Municipio</th><td><input type="text" class="textField" name="camposi[municipio]" id="municipio" value="'.$row['municipio'].'" size="50" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
		echo '<tr style="display:none;" class="cfactura"><th align="left">Estado</th><td><input type="text" class="textField" name="camposi[estado]" id="estado" value="'.$row['estado'].'" size="50" onKeyUp="this.value=this.value.toUpperCase();"></td></tr>';
		echo '<tr style="display:none;" class="cfactura"><th align="left">C&oacute;digo Postal</th><td><input type="text" class="textField" name="camposi[codigopostal]" id="codigopostal" value="'.$row['codigopostal'].'" size="50"></td></tr>';
		echo '<tr style="display:none;"><th align="left">Cuentas<br><span style="cursor:pointer" onClick="agregar_cuenta()"><font color="BLUE">Agregar</font></span></th><td><table id="cuentas"><tr><th>Banco</th><th>Cuenta</th></tr>';
		echo '</td></tr></table>';
	
		echo '<tr><th align="left">Escriba el siguiente C&oacute;digo<br> de verificaci&oacute;n</th><td>';
		$numeroverificacion = sprintf('%04s',rand(0,9999));
		echo '<span style="font-size: 20px"><fonto color="RED">'.$numeroverificacion.'</font></span>&nbsp;&nbsp;
		<input type="hidden" class="textField" name="numeroverificacion1" value="'.$numeroverificacion.'" style="font-size: 20px">
		<input type="text" class="textField" name="numeroverificacion2" value="" style="font-size: 20px" size="10">';
		echo '</td></tr>';
		echo '</table>';
		echo '</td></tr></table>';
		echo '<input type="button" value="Guardar" onClick="$(\'#panel\').show();if(document.getElementById(\'requiere_factura_1\').checked == false || validarRFC()){validar(\'\');} else{ $(\'#panel\').hide(); alert(\'RFC invalido\');}" class="textField" style="font-size:50px;">';
	
		echo '<script>
				function traeRegistro(){
					$.ajax({
					  url: "registro_web.php",
					  type: "POST",
					  async: false,
					  data: {
						placa: document.getElementById("placa").value,
						ajax: 3
					  },
						success: function(data) {
							if(data!=""){
								datos = data.split("|");
								document.getElementById("nombre").value=datos[0];
								document.getElementById("email").value=datos[1];
								document.getElementById("confirmacionemail").value=datos[1];
								document.getElementById("tarjeta_circulacion").value=datos[2];
								document.getElementById("engomado").value=datos[3];
								document.getElementById("auxengomado_"+datos[3]).checked=true;
								document.getElementById("marca").value=datos[4];
								document.getElementById("modelo").value=datos[5];
								document.getElementById("anio").value=datos[6];
								if(datos[7]=="1")
									document.getElementById("recordatorio").checked=true;
								else
									document.getElementById("recordatorio").checked=false;
								document.getElementById("requiere_factura_"+datos[8]).checked=true;
								if(datos[8]==1){
									$(\'.cfactura\').show();
								}
								else{
									$(\'.cfactura\').hide();
								}
								document.getElementById("rfc").value=datos[9];
								document.getElementById("calle").value=datos[10];
								document.getElementById("numexterior").value=datos[11];
								document.getElementById("numinterior").value=datos[12];
								document.getElementById("colonia").value=datos[13];
								document.getElementById("municipio").value=datos[14];
								document.getElementById("estado").value=datos[15];
								document.getElementById("codigopostal").value=datos[16];
							}
						}
					});
				}
			</script>';
	}

	if($_POST['cmd']==1){
		$res = mysql_query("SELECT * FROM datosempresas WHERE maneja_web=1 ORDER BY nombre_callcenter");
		$numerocentros = mysql_num_rows($res);
		echo '<table><tr><td><input type="button" style="font-size:20px" value="Ver Mes" onClick="atcr(\'registro_web.php\',\'\',0,0);"></td></tr></table><br>';
		echo '<table style="font-size:20px">';
		echo '<tr';
		if($numerocentros<=1) echo ' style="display:none;"';
		echo '><th align="left">Tipo de Combustible</th><td><select style="font-size:20px" name="tipocombustible" id="tipocombustible" onChange="$(\'#panel\').show();document.forma.plaza.value=\'0\';atcr(\'registro_web.php\',\'\',0,0);"><option value="0">Todos los tipos de combustible</option>';
		foreach($array_tipocombustible as $k => $v){
			echo '<option value="'.$k.'"';
			if($_POST['tipocombustible'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>&nbsp;</td></tr>';
		echo '<tr><th align="left">Centro de Verificaci&oacute;n</th><td><select style="font-size:20px" name="plaza" id="plaza" onChange="$(\'#panel\').show();atcr(\'registro_web.php\',\'\',0,\''.$_POST['reg'].'\');">';
		if($numerocentros>1) echo '<option value="0">Seleccione centro de verificación</option>';
		$filtro = '';
		$fecha_limite='';
		if($_POST['tipocombustible'] > 0) $filtro = " AND tipocombustible LIKE '%|".$_POST['tipocombustible']."|%'";
		$res = mysql_query("SELECT * FROM datosempresas WHERE maneja_web=1 $filtro ORDER BY nombre_callcenter");
		while($row=mysql_fetch_array($res)){
			echo '<option value="'.$row['plaza'].'"';
			if($row['plaza']==$_POST['plaza']){
				echo ' selected';
				$fecha_limite=$row['fechalimiteweb'];
			}
			echo '>'.$row['nombre_callcenter'].'</option>';
			if($numerocentros<=1){
				$_POST['plaza']=$row['plaza'];
				$fecha_limite=$row['fechalimiteweb'];
			}
		}
		echo '</select></td></tr></table>';
		if($_POST['plaza']>0){
			$res = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plaza']."'");
			$row = mysql_fetch_array($res);
			$lineas = $row['numero_lineas'];
			echo '<input type="hidden" name="mes" id="mes" value="'.substr($_POST['reg'],0,7).'">';
			echo '<input type="hidden" name="dia" id="dia" value="">';
			echo '<table width="100%"><td width="50%" align="left"><input type="button" value="Anterior" style="font-size:20px" onClick="atcr(\'registro_web.php\',\'\',1,\''.date( "Y-m-d" , strtotime ( "-1 day" , strtotime($_POST['reg']) ) ).'\');">
			<td width="50%" align="right"><input type="button" value="Siguiente" style="font-size:20px" onClick="atcr(\'registro_web.php\',\'\',1,\''.date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($_POST['reg']) ) ).'\');"></td></tr></table>';
	
			$fecha_ini = date( "Y-m-d" , strtotime ( "-2 day" , strtotime($_POST['reg']) ) );
			$fecha_fin = date( "Y-m-d" , strtotime ( "+ 2 day" , strtotime($_POST['reg']) ) );
			
			$diausuario=5;
			if($diasusuario==5 && date("w")>1) $diausuario=6;
	
			echo '<table style="font-size:20px" width="100%" border="1">';
			echo '<tr bgcolor="#E9F2F8"><th>Horarios</th>';
			$fecha = $fecha_ini;
			while($fecha<=$fecha_fin){
				echo '<th>'.fecha_normal($fecha).'<br>'.diaSemana($fecha).'</th>';
				$fecha = date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fecha) ) );
			}
			$hora = $row['horainicio'].':00';
			
			$array_fechas_citas = array();
			$res1=mysql_query("SELECT fecha, hora, COUNT(cve) FROM call_citas WHERE plaza='".$_POST['plaza']."' AND fecha BETWEEN '".date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime(date("Y-m-d")) ) )."' AND '".date( "Y-m-d" , strtotime ( "+ ".$diausuario." day" , strtotime(date("Y-m-d")) ) )."' AND estatus!='C' GROUP BY fecha, hora");
			while($row1=mysql_fetch_array($res1)){
				$array_fechas_citas[$row1['fecha'].' '.substr($row1['hora'],0,6)] = $row1[2];
			}
			
			while($hora<$row['horafin'].':00'){
				echo '<tr><th bgcolor="#E9F2F8">'.substr($hora,0,5).'</th>';
				$fecha = $fecha_ini;
				while($fecha<=$fecha_fin){
					$arfecha=explode("-",$fecha);
					$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
					if($fecha<=date("Y-m-d") || $dia == 0 || $fecha>date( "Y-m-d" , strtotime ( "+ ".$diausuario." day" , strtotime(date("Y-m-d")) ) )){
						echo '<td bgcolor="#CCCCCC">0 Disponible(s)</td>';
					}
					elseif($fecha_limite>'0000-00-00' && $fecha_limite<$fecha){
						echo '<td bgcolor="#CCCCCC">0 Disponible(s)</td>';
					}
					else{
						//$res1=mysql_query("SELECT COUNT(cve) FROM call_citas WHERE plaza='".$_POST['plaza']."' AND fecha='$fecha' AND hora='$hora' AND estatus!='C'");
						//$row1=mysql_fetch_array($res1);
						//$disponibles = $lineas-$row1[0];
						$disponibles = $lineas-$array_fechas_citas[$fecha.' '.substr($hora,0,6)];
						if($disponibles<=0){
							echo '<td bgcolor="#FF0000">0 Disponible(s)</td>';
						}
						elseif($disponibles>=($lineas/2)){
							echo '<td bgcolor="#00FF00"><a href="#" onClick="atcr(\'registro_web.php\',\'\',2,\''.$fecha.' '.$hora.'\');">'.$disponibles.' Disponible(s)</a></td>';
						}
						else{
							echo '<td bgcolor="#FFFF00"><a href="#" onClick="atcr(\'registro_web.php\',\'\',2,\''.$fecha.' '.$hora.'\');">'.$disponibles.' Disponible(s)</a></td>';
						}
					}
					$fecha = date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fecha) ) );
				}
				echo '</tr>';
				$hora = date( "H:i:s" , strtotime ( "+ ".$row['minutos']." minute" , strtotime($hora) ) );
			}
			echo '</table>';
		}
		
		echo "<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		  ga('create', 'UA-59973264-2', 'auto');
		  ga('send', 'pageview');

		</script>";

	}

	if($_POST['cmd']==0){
		$res = mysql_query("SELECT * FROM datosempresas WHERE maneja_web=1 ORDER BY nombre_callcenter");
		$numerocentros = mysql_num_rows($res);
		echo '<table style="font-size:20px">';
		echo '<tr';
		if($numerocentros<=1) echo ' style="display:none;"';
		echo '><th align="left">Tipo de Combustible</th><td><select style="font-size:20px" name="tipocombustible" id="tipocombustible" onChange="$(\'#panel\').show();document.forma.plaza.value=\'0\';atcr(\'registro_web.php\',\'\',0,0);"><option value="0">Todos los tipos de combustible</option>';
		foreach($array_tipocombustible as $k => $v){
			echo '<option value="'.$k.'"';
			if($_POST['tipocombustible'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>&nbsp;</td></tr>';
		echo '<tr><th align="left">Centro de Verificaci&oacute;n</th><td><select style="font-size:20px" name="plaza" id="plaza" onChange="$(\'#panel\').show();atcr(\'registro_web.php\',\'\',0,0);">';
		if($numerocentros>1) echo '<option value="0">Seleccione centro de verificación</option>';
		$filtro = '';
		$fecha_limite='';
		if($_POST['tipocombustible'] > 0) $filtro = " AND tipocombustible LIKE '%|".$_POST['tipocombustible']."|%'";
		$res = mysql_query("SELECT * FROM datosempresas WHERE maneja_web=1 $filtro ORDER BY nombre_callcenter");
		while($row=mysql_fetch_array($res)){
			echo '<option value="'.$row['plaza'].'"';
			if($row['plaza']==$_POST['plaza']){
				echo ' selected';
				$fecha_limite=$row['fechalimiteweb'];
			}
			echo '>'.$row['nombre_callcenter'].'</option>';
			if($numerocentros<=1){ 
				$_POST['plaza']=$row['plaza'];
				$fecha_limite=$row['fechalimiteweb'];
			}
		}
		echo '</select></td></tr></table>';

		if($_POST['plaza']>0){
			$res = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plaza']."'");
			$row = mysql_fetch_array($res);
			
			$hora = $row['horainicio'].':00';
			$citastotal = 0;
			while($hora<$row['horafin'].':00'){
				$citastotal += $row['numero_lineas'];
				$hora = date( "H:i:s" , strtotime ( "+ ".$row['minutos']." minute" , strtotime($hora) ) );
			}
			
			$diausuario=5;
			if($diasusuario==5 && date("w")>1) $diausuario=6;
		
			if($_POST['mes']=="") $_POST['mes']=date("Y-m");
			$fecha = $_POST['mes'];
	
			echo '<input type="hidden" name="mes" id="mes" value="'.$_POST['mes'].'">';
			echo '<h1 style="font-size:20px">';
			$datos = explode("-",$_POST['mes']);
			echo $array_meses[intval($datos[1])].' '.$datos[0].'</h1>';
			echo '<table width="100%"><td width="50%" align="left"><input type="button" value="Anterior" style="font-size:20px" onClick="document.forma.mes.value=\''.date( "Y-m" , strtotime ( "-1 month" , strtotime($fecha) ) ).'\';atcr(\'registro_web.php\',\'\',0,0);"></td>
			<td width="50%" align="right"><input type="button" value="Siguiente" style="font-size:20px" onClick="document.forma.mes.value=\''.date( "Y-m" , strtotime ( "+ 1 month" , strtotime($fecha) ) ).'\';atcr(\'registro_web.php\',\'\',0,0);"></td></tr></table>';
			echo '<table style="font-size:20px" width="100%" border="1">
			<tr bgcolor="#E9F2F8"><th>Domingo</th><th>Lunes</th><th>Martes</th><th>Miercoles</th><th>Jueves</th><th>Viernes</th><th>Sabado</th></tr>';
			$sumren=0;
			$arfecha=explode("-",$fecha);
			$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval(1), $arfecha[0]));
			for($i=0;$i<$dia;$i++){
				if($i==0) echo '<tr>';
				echo '<td align="center"><br><br>&nbsp;</td>';
			}
			$fec=$fecha.'-01';
			$fecultima=date( "Y-m-t" , strtotime ( "+ 1 day" , strtotime($fec) ) );
			$array_fechas_citas = array();
			$res=mysql_query("SELECT fecha, COUNT(cve) FROM call_citas WHERE plaza='".$_POST['plaza']."' AND fecha BETWEEN '".date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime(date("Y-m-d")) ) )."' AND '".date( "Y-m-d" , strtotime ( "+ ".$diausuario." day" , strtotime(date("Y-m-d")) ) )."' AND estatus!='C' GROUP BY fecha");
			while($row=mysql_fetch_array($res)){
				$array_fechas_citas[$row['fecha']] = $row[1];
			}
			for($i=$dia;$fec<=$fecultima;$i++){
				$arfecha=explode("-",$fec);
				$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
				if($i==7){
					echo '</tr>';
					$i=0;
				}
				if($i==0){
					echo '<tr>';
				}
				if($fec<=date("Y-m-d") || $dia == 0 || $fec>date( "Y-m-d" , strtotime ( "+ ".$diausuario." day" , strtotime(date("Y-m-d")) ) )){
					echo '<td align="center" valign="center" bgcolor="#CCCCCC"><table width="100%"><tr><td width="100%">&nbsp;</td></tr><tr><td align="center">'.substr($fec,8,2).'</td></tr><tr><td style="font-size:10px" align="right">&nbsp;</td></tr></table></td>';
				}
				elseif($fecha_limite>'0000-00-00' && $fecha_limite<$fec){
					echo '<td align="center" valign="center" bgcolor="#CCCCCC"><table width="100%"><tr><td width="100%">&nbsp;</td></tr><tr><td align="center">'.substr($fec,8,2).'</td></tr><tr><td style="font-size:10px" align="right">&nbsp;</td></tr></table></td>';
				}
				else{
					//$res=mysql_query("SELECT COUNT(cve) FROM call_citas WHERE plaza='".$_POST['plaza']."' AND fecha='$fec' AND estatus!='C'");
					//$row=mysql_fetch_array($res);
					//$disponible = $citastotal - $row[0];
					$disponible = $citastotal - $array_fechas_citas[$fec];
					echo '<td align="center" valign="center"';
					if($disponible<=0) echo ' bgcolor="#FF0000"';
					elseif($disponible>=($citastotal/2)) echo ' bgcolor="#00FF00"';
					else echo ' bgcolor="#FFFF00"';
					echo '><table width="100%"><tr><td width="100%">&nbsp;</td></tr><tr><td align="center"><a href="#" onClick="atcr(\'registro_web.php\',\'\',1,\''.$fec.'\');">'.substr($fec,8,2).'</a></td></tr><tr><td style="font-size:10px" align="right">'.$disponible.' Disponible(s)</td></tr></table></td>';
				}
				$fec=date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fec) ) );
			}
			$arfecha=explode("-",$fecultima);
			$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
			for($i=$dia;$i<6;$i++){
				echo '<td align="center"><br><br>&nbsp;</td>';
			}
			echo '</tr>';
			echo '</table>';
			echo '<table style="font-size:20px"><tr><td bgcolor="#FF0000"><br>Sin Disponibilidad<br>&nbsp;</td>
			<td bgcolor="#FFFF00"><br>Poca Disponibilidad<br>&nbsp;</td><td bgcolor="#00FF00"><br>Buena Disponibilidad<br>&nbsp;</td></tr></table>';
		}
	}
}
bottom();

?>

