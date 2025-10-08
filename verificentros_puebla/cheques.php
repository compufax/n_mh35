<?php
include ("main.php"); 
$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}
$array_cuenta = array();
$res = mysql_query("SELECT * FROM cuentas ORDER BY cuenta");
while($row = mysql_fetch_array($res)){
	$array_cuenta[$row['cve']] = $row['cuenta'].' '.$row['banco'];
}
$array_motivo = array();
$res = mysql_query("SELECT * FROM motivos ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_motivo[$row['cve']]=$row['nombre'];
}
$array_beneficiario = array();
$res = mysql_query("SELECT * FROM beneficiarios_chequera ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_beneficiario[$row['cve']]=$row['nombre'];
}


if($_POST['ajax']==1){
	$plazas_ids = "";
	foreach($array_plaza as $k=>$v){
		$plazas_ids.=",".$k;
	}
	$plazas_ids=substr($plazas_ids,1);
	$select = "SELECT * FROM cheques WHERE plaza IN (".$plazas_ids.") AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	if($_POST['plaza']!="all") $select .= " AND plaza='".$_POST['plaza']."'";
	if($_POST['cuenta']!="all") $select .= " AND cuenta='".$_POST['cuenta']."'";
	if($_POST['motivo']!="all") $select .= " AND motivo='".$_POST['motivo']."'";
	if($_POST['beneficiario']!="all") $select .= " AND beneficiario='".$_POST['beneficiario']."'";
	if($_POST['usuario']!="all") $select .= " AND usuario='".$_POST['usuario']."'";
	$select.=" ORDER BY cve DESC";
	$res=mysql_query($select);
	if(mysql_num_rows($res)>0) 
	{
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		if($_POST['plazausuario']==0) echo '<th>Plaza</th>';
		echo '<th>Folio</th><th>Fecha</th><th>Fecha Corte</th>
		<th>Cuenta</th><th>Motivo</th><th>Beneficiario</th><th>Monto</th><th>Concepto</th><th>Usuario</th>';
		echo '</tr>';
		$t=0;
		while($row=mysql_fetch_array($res)) {
			rowb();
			echo '<td align="center" width="40" nowrap>';
			if($row['estatus']=='C'){
				echo 'Cancelado';
				$row['monto']=0;
			}
			else{
				echo '<a href="#" onClick="atcr(\'imp_poliza.php\',\'_blank\',\'0\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
				if(nivelUsuario()>1)
					echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el cheque\')) atcr(\'cheques.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
			}	
			echo '</td>';
			if($_POST['plazausuario']==0) echo '<td>'.$array_plaza[$row['plaza']].'</td>';
			echo '<td align="center">'.htmlentities($row['folio']).'</td>';
			echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
			echo '<td align="center">'.htmlentities($row['fecha_corte']).'</td>';
			echo '<td align="left">'.htmlentities(utf8_encode($array_cuenta[$row['cuenta']])).'</td>';
			echo '<td align="left">'.htmlentities(utf8_encode($array_motivo[$row['motivo']])).'</td>';
			echo '<td align="left">'.htmlentities(utf8_encode($array_beneficiario[$row['beneficiario']])).'</td>';
			echo '<td align="right">'.number_format($row['monto'],2).'</td>';
			echo '<td align="left">'.htmlentities(utf8_encode($row['concepto'])).'</td>';
			echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
			echo '</tr>';
			$t+=$row['monto'];
		}
		$c=7;
		if($_POST['plazausuario']==0) ++$c;
		echo '	
			<tr>
			<td colspan="'.$c.'" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
			<td align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</td>
			<td colspan="2" bgcolor="#E9F2F8">&nbsp;</td>
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
	$res = mysql_query("SELECT cve,cuenta,banco FROM cuentas WHERE plaza='".$_POST['plaza']."'");
	if(mysql_num_rows($res)!=1) echo '<option value="0">Seleccione</option>';
	while($row = mysql_fetch_array($res)) echo '<option value="'.$row['cve'].'">'.$row['cuenta'].' '.$row['banco'].'</option>';
	exit();
}



if($_POST['ajax']==3){
	$res = mysql_query("SELECT folio_siguiente FROM cuentas WHERE cve='".$_POST['cuenta']."'");
	$row = mysql_fetch_array($res);
	echo $row['folio_siguiente'];
	exit();
}

if($_POST['ajax']==4){
	$res = mysql_query("SELECT cve,nombre FROM beneficiarios WHERE plaza='".$_POST['plaza']."'");
	if(mysql_num_rows($res)!=1) echo '<option value="0">Seleccione</option>';
	while($row = mysql_fetch_array($res)) echo '<option value="'.$row['cve'].'">'.$row['nombre'].'</option>';
	exit();
}

top($_SESSION);

if($_POST['cmd']==3){
	mysql_query("UPDATE cheques SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	$intentos = 0;
	$folio = $_POST['folio'];
	$insert = " INSERT cheques 
					SET 
					plaza = '".$_POST['plaza']."',fecha='".fechaLocal()."',hora='".horaLocal()."',fecha_corte='".$_POST['fecha_corte']."',
					cuenta='".$_POST['cuenta']."',motivo='".$_POST['motivo']."',
					beneficiario='".$_POST['beneficiario']."',monto='".$_POST['monto']."',concepto='".$_POST['concepto']."',
					usuario='".$_POST['cveusuario']."',estatus='A'";
	while(!$res = mysql_query($insert.",folio='".$folio."'")){
		$folio++;
		$intentos++;
		if($intentos==100){
			break;
		}
	}
	if($intentos == 100){
		echo '<script>alert("Error en el folio de cheque");</script>';
	}
	else{
		$cheque = mysql_insert_id();
		$folio++;
		mysql_query("UPDATE cuentas SET folio_siguiente='$folio' WHERE cve='".$_POST['cuenta']."'");
	}
	$_POST['cmd']=0;
}


if ($_POST['cmd']==1) {
		
	$select=" SELECT * FROM cheques WHERE cve='".$_POST['reg']."' ";
	$res=mysql_query($select);
	$row=mysql_fetch_array($res);			
	if($_POST['reg']==0){
		$row['fecha_corte']=fechaLocal();
		$row['folio']='';
	}
	//Menu
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();validar();"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'cheques.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Cheques</td></tr>';
	echo '</table>';
	
	echo '<table>';
	if($_POST['plazausuario']==0 && $row['plaza']==0)
	{
		echo '<tr><th align="left">Plaza</th><td><select name="plaza" id="plaza" onChange="traerCuentas()"><option value="0">Seleccione</option>';
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
	echo '<tr><th align="left">Fecha Corte</th><td><input type="text" name="fecha_corte" id="fecha_corte" class="readOnly" size="12" value="'.$row['fecha_corte'].'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_corte,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><th align="left">Cuenta</th><td><select name="cuenta" id="cuenta" onChange="traerFolioSiguiente()">';
	if($_POST['plazausuario']==0 && $row['plaza']==0){
		echo '<option value="0">Seleccione Plaza</option>';
	}
	else{
		if($_POST['plazausuario']>0)
			$res1 = mysql_query("SELECT cve,cuenta,banco,folio_siguiente FROM cuentas WHERE plaza='".$_POST['plazausuario']."'");
		else
			$res1 = mysql_query("SELECT cve,cuenta,banco,folio_siguiente FROM cuentas WHERE plaza='".$row['plaza']."'");
		if(mysql_num_rows($res1)!=1){
			echo '<option value="0">Seleccione</option>';
		}
		while($row1 = mysql_fetch_array($res1)){ 
			echo '<option value="'.$row1['cve'].'"';
			if($row1['cve']==$row['cuenta']) echo ' selected';
			echo '>'.$row1['cuenta'].' '.$row1['banco'].'</option>';
			if(mysql_num_rows($res1)==1 && $_POST['reg']==0){
				$row['folio']=$row1['folio_siguiente'];
			}
		}
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Folio</th><td><input type="text" name="folio" id="folio" class="readOnly" size="10" value="'.$row['folio'].'" readOnly></td></tr>';
	echo '<tr><th align="left">Motivo</th><td><select name="motivo" id="motivo"><option value="0">Seleccione</option>';
	foreach($array_motivo as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$row['motivo']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Beneficiario</th><td><select name="beneficiario" id="beneficiario"><option value="0">Seleccione</option>';
	foreach($array_beneficiario as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$row['beneficiario']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="textField" size="10" value="'.$row['monto'].'"></td></tr>';
	echo '<tr><th align="left">Concepto</th><td><textarea cols="30" rows="3" name="concepto" id="concepto" class="textField">'.$row['concepto'].'</textarea></td></tr>';
	echo '</table>';
	
	echo '<script>
			function validar(){
				if(document.forma.plaza.value=="0"){
					$("#panel").hide();
					alert("Necesita seleccionar una plaza");
				}
				else if(document.forma.cuenta.value=="0"){
					$("#panel").hide();
					alert("Necesita seleccionar una cuenta");
				}
				else if(document.forma.motivo.value=="0"){
					$("#panel").hide();
					alert("Necesita seleccionar un motivo");
				}
				else if(document.forma.beneficiario.value=="0"){
					$("#panel").hide();
					alert("Necesita seleccionar un beneficiario");
				}
				else if((document.forma.monto.value/1)<=0){
					$("#panel").hide();
					alert("El monto debe de ser mayor a cero");
				}
				else{
					atcr("cheques.php","",2,0);
				}
			}
			
			function traerCuentas(){
				if(document.forma.plaza.value=="0"){
					$("#cuenta").html(\'<option value="0">Seleccione Plaza</option>\');
					$("#beneficiario").html(\'<option value="0">Seleccione Plaza</option>\');
					$("#folio").val(\'\');
				}
				else{
					$.ajax({
						url: "cheques.php",
						type: "POST",
						data: "ajax=2&plaza="+document.forma.plaza.value,
						success: function(resultado)
						{
							$("#cuenta").html(resultado);
							traerFolioSiguiente();
						}
					});
				}
			}
			
			function traerFolioSiguiente(){
				if(document.forma.cuenta.value=="0"){
					$("#folio").val(\'\');
				}
				else{
					$.ajax({
						url: "cheques.php",
						type: "POST",
						data: "ajax=3&cuenta="+document.forma.cuenta.value,
						success: function(resultado)
						{
							$("#folio").val(resultado);
						}
					});
				}
			}
			
		</script>';
	
}

if($_POST['cmd']==0){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'cheques.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.substr(fechaLocal(),0,8).'01" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	if($_POST['plazausuario']==0){
		echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" onChange="traerCuentas()"><option value="all">Todas</option>';
		foreach($array_plaza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
	}
	else{
		echo '<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
	}
	echo '<tr><td>Cuenta</td><td><select name="cuenta" id="cuenta"><option value="all">Todas</option>';
	if($_POST['plazausuario']>0){
		$res=mysql_query("SELECT cve,cuenta,banco FROM cuentas WHERE plaza='".$_POST['plazausuario']."' ORDER BY cuenta");
		while($row=mysql_fetch_array($res)){
			echo '<option value="'.$row['cve'].'">'.$row['cuenta'].' '.$row['banco'].'</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr><td>Motivo</td><td><select name="motivo" id="motivo"><option value="all">Todos</option>';
	foreach($array_motivo as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Beneficiario</td><td><select name="beneficiario" id="beneficiario"><option value="all">Todos</option>';
	foreach($array_beneficiario as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="all">Todos</option>';
	if($_POST['plazausuario']==0)
		$res=mysql_query("SELECT b.cve,b.usuario FROM cheques a INNER JOIN usuarios b ON a.usuario = b.cve WHERE 1 GROUP BY a.usuario ORDER BY b.usuario");
	else
		$res=mysql_query("SELECT b.cve,b.usuario FROM cheques a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY b.usuario");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['usuario'].'</option>';
	}
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
			objeto.open("POST","cheques.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&plaza="+document.getElementById("plaza").value+"&cuenta="+document.getElementById("cuenta").value+"&motivo="+document.getElementById("motivo").value+"&beneficiario="+document.getElementById("beneficiario").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
		
	
	function traerCuentas(){
		if(document.forma.plaza.value=="0"){
			$("#cuenta").html(\'<option value="all">Todas</option>\');
			$("#beneficiario").html(\'<option value="all">Todos</option>\');
		}
		else{
			$.ajax({
				url: "cheques.php",
				type: "POST",
				data: "ajax=2&plaza="+document.forma.plaza.value,
				success: function(resultado)
				{
					$("#cuenta").html(resultado);
				}
			});
		}
	}
	
	</Script>
	';

	
}
	
bottom();




?>