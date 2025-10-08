<?php 
include ("main.php"); 

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$res=mysql_query("SELECT local FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];


$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}


$array_tipo_pago = array();
$res = mysql_query("SELECT * FROM tipos_pago WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}
$array_tipo_pago[2] = 'RECUPERACION DE CREDITO';

$array_depositantes = array();
$res = mysql_query("SELECT * FROM depositantes WHERE plaza='".$_POST['plazausuario']."' AND edo_cuenta=1 AND solo_contado=0 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_depositantes[$row['cve']]=$row['nombre'];
	if($row['estatus']==1)$array_depositantes[$row['cve']].=' (Inactivo)';
}

$array_forma_pago = array(1=>"Efectivo",2=>"Deposito Bancario",3=>"Cheque",4=>"Transferencia");

$array_estatus = array('A'=>'Activo','C'=>'Cancelado');

$array_tipo_vale = array('Pago Anticipado', 'Cortesia');
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		$res = mysql_query("SELECT * FROM usuarios WHERE cve='".$_POST['cveusuario']."'");
		$row = mysql_fetch_array($res);
		$permite_editar = $row['editar_venta'];
		//Listado de plazas
		$select= " SELECT a.* FROM vales_pago_anticipado a
		WHERE a.plaza='".$_POST['plazausuario']."'";
		$select.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		if ($_POST['usuario']!="") { $select.=" AND a.usuario='".$_POST['usuario']."' "; }
		if ($_POST['depositante']!="") { $select.=" AND a.depositante='".$_POST['depositante']."' "; }
		if ($_POST['estatus']!="") { $select.=" AND a.estatus='".$_POST['estatus']."' "; }
		if ($_POST['tipo']!="all") { $select.=" AND a.tipo='".$_POST['tipo']."' "; }
			
		$select.=" ORDER BY a.cve DESC";
		if($_POST['btn']==0) $select.=" LIMIT 1";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha Creacion</th><th>Fecha Aplicacion</th>
			<th>Folio de Pago</th><th>Depositante</th><th>Tipo</th><th>Usuario</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
					$row['monto']=0;
				}
				else{
					//echo '<a href="#" onClick="atcr(\'pagos_caja.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					if(nivelUsuario()>1 && ($row['fecha']==fechaLocal() || $_POST['cveusuario']==1 || $permite_editar==1))
						echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el pago\')) atcr(\'vales_pago_anticipado.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				}	
				echo '</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha_creacion'].' '.$row['hora']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha']).'</td>';
				echo '<td align="center">'.htmlentities($row['pago']).'</td>';
				echo '<td align="center">'.utf8_encode($array_depositantes[$row['depositante']]).'</td>';
				echo '<td align="center">'.utf8_encode($array_tipo_vale[$row['tipo']]).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
				$t+=$row['monto'];
			}
			echo '	
				<tr>
				<td colspan="6" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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


if($_POST['ajax']==40){
	mysql_query("UPDATE pagos_caja SET ".$_POST['campo']."='".$_POST['valor']."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
	
	mysql_query("INSERT historial SET menu='77',cveaux='".$_POST['folio']."',fecha='".fechaLocal()." ".horaLocal()."',obs='".$_POST['plazausuario']."',
			dato='".$_POST['nombre']."',nuevo='".$_POST['valor']."',anterior='".$_POST['valor_anterior']."',arreglo='".$_POST['arreglo']."',usuario='".$_POST['cveusuario']."'");
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

	$variables='?plaza='.$array_plaza[$row['plaza']];
	$variables.='&nomplaza='.$rowPlaza['nombre'];
	$variables.='&rfc='.$rowPlaza2['rfc'];	
	$variables.='&folio='.$row['cve'];
	$variables.='&fecha='.$row['fecha'].' '.$row['hora'];
	$variables.='&formapago='.$array_forma_pago[$row['forma_pago']];
	$variables.='&cveformapago='.$row['forma_pago'];
	$variables.='&referencia='.$row['referencia'];
	$variables.='&tipopago='.$array_tipo_pago[$row['tipo_pago']];
	$variables.='&depositante='.$array_depositantes[$row['depositante']];
	$variables.='&monto='.$row['monto'];
	$vales = '';
	if($row['tipo_pago']==6){
		$res1=mysql_query("SELECT cve FROM vales_pago_anticipado WHERE plaza='".$row['plaza']."' AND pago='".$row['cve']."'");
		while($row1 = mysql_fetch_array($res1)){
			$vales .= ','.$row1['cve'];
		}
		$vales = substr($vales, 1);
	}
	$variables.='&vales='.$vales;
	//$variables.='&montol='.numlet($row['monto']);
	$impresion='<iframe src="http://localhost/impresioncajaverificentros.php?'.$variables.'" width=200 height=200></iframe>';
	//$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$texto.'&logo='.str_replace(' ','',$array_plaza[$row['plaza']]).'&barcode=1'.sprintf("%011s",(intval($row['cve']))).'&copia=1" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",2000);</script>';
	exit();
}

top($_SESSION);


if($_POST['cmd']==3){
	mysql_query("UPDATE vales_pago_anticipado SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/


/*** EDICION  **************************************************/

	
/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
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
	echo '<tr><td>Tipo</td><td><select name="tipo" id="tipo"><option value="all">Todos</option>';
	foreach($array_tipo_vale as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
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
			objeto.open("POST","vales_pago_anticipado.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&btn=1&tipo="+document.getElementById("tipo").value+"&depositante="+document.getElementById("depositante").value+"&estatus="+document.getElementById("estatus").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

	function guardarCampo(folio, valor_anterior, campo, nombre, arreglo){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","pagos_caja.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=40&arreglo="+arreglo+"&nombre="+nombre+"&campo="+campo+"&folio="+folio+"&valor_anterior="+valor_anterior+"&valor="+document.getElementById(campo+"_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros(1);}
			}
		}
	}

	buscarRegistros(0); //Realizar consulta de todos los registros al iniciar la forma.
		
	
	
	
	
	
	
	</Script>
	';

	
}
	
bottom();

if($cvecobro>0){
		echo '<script>atcr(\'pagos_caja.php?nuevo=1\',\'_blank\',\'101\','.$cvecobro.');</script>';
	}
?>

