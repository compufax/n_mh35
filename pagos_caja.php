<?php 
include ("main.php"); 

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$res=mysql_query("SELECT local FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=0;


$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}


$array_tipo_pago = array();
$res = mysql_query("SELECT * FROM tipos_pago WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}


$array_depositantes = array();
if($_POST['cmd']==1){
	$res = mysql_query("SELECT * FROM depositantes WHERE plaza='".$_POST['plazausuario']."' AND estatus=0 AND edo_cuenta=1 ORDER BY nombre");
}
else{
	$res = mysql_query("SELECT * FROM depositantes WHERE plaza='".$_POST['plazausuario']."' AND edo_cuenta=1 ORDER BY nombre");
}
while($row=mysql_fetch_array($res)){
	$array_depositantes[$row['cve']]=$row['nombre'];
}

$array_forma_pago = array(1=>"Efectivo",2=>"Deposito Bancario",3=>"Cheque",4=>"Transferencia");

$array_estatus = array('A'=>'Activo','C'=>'Cancelado');
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		
		
		
		//Listado de plazas
		$select= " SELECT a.* FROM pagos_caja a
		WHERE a.plaza='".$_POST['plazausuario']."'";
		$select.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		if ($_POST['usuario']!="") { $select.=" AND a.usuario='".$_POST['usuario']."' "; }
		if ($_POST['depositante']!="") { $select.=" AND a.depositante='".$_POST['depositante']."' "; }
		if ($_POST['estatus']!="") { $select.=" AND a.estatus='".$_POST['estatus']."' "; }
		if ($_POST['tipo_pago']!="all") { $select.=" AND a.tipo_pago='".$_POST['tipo_pago']."' "; }
			
		$select.=" ORDER BY a.cve DESC";
		if($_POST['btn']==0) $select.=" LIMIT 1";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Forma Pago</th>
			<th>Referencia</th><th>Tipo de Pago</th><th>Depositante</th><th>Monto</th><th>Observaciones</th><th>Usuario</th>';
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
					echo '<a href="#" onClick="atcr(\'pagos_caja.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					if(nivelUsuario()>1 && ($row['fecha']==fechaLocal() || $_POST['cveusuario']==1))
						echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el pago\')) atcr(\'pagos_caja.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				}	
				echo '</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="center">'.htmlentities($array_forma_pago[$row['forma_pago']]).'</td>';
				echo '<td align="center">'.htmlentities($row['referencia']).'</td>';
				echo '<td align="center">'.htmlentities($array_tipo_pago[$row['tipo_pago']]).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($array_depositantes[$row['depositante']])).'</td>';
				echo '<td align="center">'.number_format($row['monto'],2).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($row['obs'])).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
				$t+=$row['monto'];
			}
			echo '	
				<tr>
				<td colspan="7" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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



if($_POST['cmd']==101){
	require_once("numlet.php");
	$res=mysql_query("SELECT * FROM pagos_caja WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	$texto=chr(27)."@";
	$texto.='|';
	$resPlaza = mysql_query("SELECT nombre FROM plazas WHERE cve='".$row['plaza']."'");
	$rowPlaza = mysql_fetch_array($resPlaza);
	$resPlaza2 = mysql_query("SELECT rfc FROM datosempresas WHERE plaza='".$row['plaza']."'");
	$rowPlaza2 = mysql_fetch_array($resPlaza2);
	$texto.=chr(27).'!'.chr(30)." ".$array_plaza[$row['plaza']]."|".$rowPlaza['nombre'];
	$texto.='| RFC: '.$rowPlaza2['rfc'];
	$texto.='||';
	$texto.=chr(27).'!'.chr(8)." FOLIO: ".$row['cve'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." PAGO";
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." FECHA: ".$row['fecha']."   ".$row['hora'].'|';
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." FORMA PAGO: ".$array_forma_pago[$row['forma_pago']];
	if($row['forma_pago']>1){
		$texto.=chr(27).'!'.chr(8)." REFERENCIA: ".$row['referencia'];
		$texto.='|';
	}
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." TIPO PAGO: ".$array_tipo_pago[$row['tipo_pago']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." DEPOSITANTE: ".$array_depositantes[$row['depositante']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." MONTO: ".$row['monto'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." ".numlet($row['monto']);
	$texto.='|';
	
	
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$texto.'&logo='.str_replace(' ','',$array_plaza[$row['plaza']]).'&barcode=1'.sprintf("%011s",(intval($row['cve']))).'&copia=1" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",2000);</script>';
	exit();
}

top($_SESSION);


if($_POST['cmd']==3){
	$res = mysql_query("SELECT * FROM pagos_caja WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	$sincronizado=$row['sincronizado'];
	if($row['sincronizado']==1) $sincronizado=2;
	mysql_query("UPDATE pagos_caja SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."',sincronizado='$sincronizado' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
			
		$insert = " INSERT pagos_caja 
						SET 
						plaza = '".$_POST['plazausuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',
						forma_pago='".$_POST['forma_pago']."',referencia='".$_POST['referencia']."',monto='".$_POST['monto']."',
						tipo_pago='".$_POST['tipo_pago']."',depositante='".$_POST['depositante']."',
						usuario='".$_POST['cveusuario']."',estatus='A',sincronizado=0,
						obs='".$_POST['obs']."'";
		mysql_query($insert);
		$cvecobro = mysql_insert_id();
				
	
	$_POST['cmd']=1;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$res = mysql_query("SELECT * FROM pagos_caja WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();validar('.$_POST['reg'].');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'pagos_caja.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Pagos</td></tr>';
		echo '</table>';
		echo '<table style="font-size:15px">';
		
		echo '<tr><th align="left">Forma de Pago</th><td><select name="forma_pago" id="forma_pago" style="font-size:20px" onChange="
		if((this.value/1)>1){
			$(\'#referencia\').parents(\'tr:first\').show();
		}
		else{
			$(\'#referencia\').parents(\'tr:first\').hide();
			$(\'#referencia\').val(\'\');
		}"><option value="0">Seleccione</option>';
		foreach($array_forma_pago as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row['forma_pago'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr style="display:none;"><th align="left">Referencia</th><td><input type="text" name="referencia" id="referencia" class="textField" size="20" style="font-size:12px" value="'.$row['referencia'].'"></td></tr>';
		echo '<tr><th align="left">Tipo de Pago</th><td><select name="tipo_pago" id="tipo_pago" style="font-size:20px"><option value="0">Seleccione</option>';
		foreach($array_tipo_pago as $k=>$v){
			if($k==2 || $k==6){
				echo '<option value="'.$k.'"';
				if($row['tipo_pago'] == $k) echo ' selected';
				echo '>'.$v.'</option>';
			}
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Depositante</th><td><select name="depositante" id="depositante" style="font-size:20px"><option value="0">Seleccione</option>';
		foreach($array_depositantes as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row['depositante']==$k) echo ' selected';
			echo '>'.$v.' (Saldo: '.number_format(saldo_depositante($k),2).')</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="readOnly" size="10" style="font-size:12px" value="'.$row['monto'].'"></td></tr>';
		
		echo '<tr><th align="left">Observaciones</th><td><textarea name="obs" id="obs" class="textField" rows="3" cols="30"></textarea></td></tr>';
		
		echo '</table>';
		
		echo '<script>
				function validar(reg){
					if(document.forma.depositante.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar depositante");
					}
					else if(document.forma.forma_pago.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar la forma de pago");
					}
					else if(document.forma.tipo_pago.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el tipo de pago");
					}
					else if((document.forma.forma_pago.value=="2" || document.forma.tipo_pago.value=="3" || document.forma.forma_pago.value == "4") && document.forma.referencia.value==""){
						$("#panel").hide();
						alert("Necesita ingresar la referencia");
					}
					else if((document.forma.monto.value/1)==0){
						$("#panel").hide();
						alert("El monto no puede ser cero");
					}
					else{
						atcr("pagos_caja.php","",2,reg);
					}
				}
				
				
				
			</script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	if($PlazaLocal!=1)
		echo '<td><a href="#" onClick="atcr(\'pagos_caja.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
	echo '
		 </tr>';
	echo '</table>';
	echo '<table width="100%"><tr><td valign="top" width="50%">';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Depositante</td><td><select name="depositante" id="depositante"><option value="">Todos</option>';
	foreach($array_depositantes as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">Todos</option>';
	$res=mysql_query("SELECT b.cve,b.usuario FROM pagos_caja a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY b.usuario");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['usuario'].'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="">Todos</option>';
	foreach($array_estatus as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Tipo de Pago</td><td><select name="tipo_pago" id="tipo_pago"><option value="all" selected>Todos</option>';
	foreach($array_tipo_pago as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
	echo '</td><td width="50%" valign="top" id="capacorte"></td></tr></table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros(btn)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","pagos_caja.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&btn="+btn+"&tipo_pago="+document.getElementById("tipo_pago").value+"&depositante="+document.getElementById("depositante").value+"&estatus="+document.getElementById("estatus").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
	buscarRegistros(0); //Realizar consulta de todos los registros al iniciar la forma.
		
	
	
	
	
	
	
	</Script>
	';

	
}
	
bottom();

if($cvecobro>0){
		echo '<script>atcr(\'pagos_caja.php\',\'_blank\',\'101\','.$cvecobro.');</script>';
	}
?>

