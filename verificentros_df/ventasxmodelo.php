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
	
$res = mysql_query("SELECT * FROM cat_marcas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_marcas[$row['cve']]=$row['nombre'];
}
$res = mysql_query("SELECT * FROM cobro_engomado group BY modelo order by modelo desc");
$i=1;
while($row=mysql_fetch_array($res)){
	$array_modelo[$i]=$row['modelo'];
	$i++;
}

	
if($_POST['ajax']==1){
//	$select="SELECT * FROM clientes WHERE 1";
	$select="SELECT count(a.cve) as totall,a.*,b.cve as certificado, b.certificado as holograma,b.engomado as engomado_entrega, CONCAT(b.fecha,' ',b.hora) as fechaentrega,
	TIMEDIFF(IFNULL(CONCAT(b.fecha,' ',b.hora),NOW()),CONCAT(a.fecha,' ',a.hora)) as diferencia 
	FROM cobro_engomado a 
	LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
	LEFT JOIN depositantes c ON c.cve = a.depositante AND c.plaza = a.plaza 
	WHERE a.plaza='".$_POST['plazausuario']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	//if($_POST['cveusuario'] != 1) $select .= " AND usuario>=0";
//	if($_POST['nombre']!="") $select.=" AND nombre LIKE '%".$_POST['nombre']."%'";
//	if($_POST['marca']!="") $select.=" AND a.marca = '".$_POST['marca']."'";
	if($_POST['modelo']!="") $select.=" AND a.modelo = '".$_POST['modelo']."'";
	$select.=" group by a.modelo ORDER BY totall DESC";
	

	$res=mysql_query($select);
	$totalRegistros = mysql_num_rows($res);
//echo''.$select.'';		
	if(mysql_num_rows($res)>0) 
	{
			
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
		echo '<thead><tr bgcolor="#E9F2F8"><td colspan="2">'.mysql_num_rows($res).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8">';
		echo '<th>Nombre</th>
		<th>Cantidad</th>
		';
		echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
		$i=0;
		$t_fc=0;
		while($row=mysql_fetch_array($res)) {
//				$select1= " SELECT count(a.cve) as t_facturas FROM facturas as a LEFT JOIN clientes b ON b.plaza = a.plaza AND b.cve = a.cliente LEFT JOIN cobro_engomado as c ON a.plaza = c.plaza AND a.cve = c.factura WHERE a.tipo_serie = '0' and b.rfc='".$row['rfccliente']."' ";
//					$res1=mysql_query($select1);
//					$roww1=mysql_fetch_array($res1);
		//		if($row['t_facturas']>=10){
				rowb();
				echo '<td align="center">'.utf8_encode($row['modelo']).'</td>';
				echo '<td align="center">'.$row['totall'].'</td>';
				$t_fc=($t_fc)+($row['totall']);
				echo '</tr>';
		}
		
		echo '</tbody>
			<tr bgcolor="#E9F2F8">
			<td colspan="" >';menunavegacion(); echo '</td>
	
			<td align="center">'.$t_fc.'</td>
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

/*if($_POST['ajax']==2){
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
*/	
top($_SESSION);
/*if($_POST['cmd']==1){
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
					objeto.open("POST","ventasxmarca.php",true);
					objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto.send("ajax=2&rfc="+document.getElementById("rfc").value+"&cliente="+reg+"&plazausuario="+document.forma.plazausuario.value);
					objeto.onreadystatechange = function()
					{
						if (objeto.readyState==4)
						{
							if(objeto.responseText == "no"){
								atcr(\'ventasxmarca.php\',\'\',2,reg);
							}
							else{
								$(\'#panel\').hide();
								alert("Ya esta dado de alta el rfc");
							}
						}
					}
				}*/
				/*$.ajax({
				  url: "ventasxmarca.php",
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
							atcr(\'ventasxmarca.php\',\'\',2,reg);
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
	echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'ventasxmarca.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
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
/*}*/	
if ($_POST['cmd']<1) {
	//Busqueda
	echo '<table>';

	/*echo '<tr>
			<td><a href="#" onclick="
			if(document.forma.rfc.value==\'\')
					alert(\'Necesita ingresar el RFC\');
				else
				buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;
		 </tr>';*/
	echo '<tr>
			<td><a href="#" onclick="
				buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
//	echo '<tr style="display:none"><td>Nombre</td><td><input type="text" name="nombre" id="nombre" class="textField"></td></tr>'; 
	//echo '<tr><td>RFC</td><td><input type="text" name="rfc" id="rfc" class="textField"></td></tr>'; 
	echo '<tr><td>Modelo</td><td><select name="modelo" id="modelo"><option value="">Todos</option>';
	foreach($array_modelo as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$_POST['marca']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
/*	echo '<tr><td>Modelo</td><td><select name="modelo" id="modelo"><option value="">Todos</option>';
	foreach($array_modelo as $k=>$v){
		echo '<option value="'.$v.'"';
		if($k==$_POST['modelo']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';*/
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
			objeto.open("POST","ventasxmodelo.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&modelo="+document.getElementById("modelo").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value);
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
