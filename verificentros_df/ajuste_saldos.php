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



$array_depositantes = array();
$res = mysql_query("SELECT * FROM depositantes WHERE plaza='".$_POST['plazausuario']."' AND edo_cuenta=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_depositantes[$row['cve']]=$row['nombre'];
}


$array_estatus = array('A'=>'Activo','C'=>'Cancelado');
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		
		$res = mysql_query("SELECT * FROM usuarios WHERE cve='".$_POST['cveusuario']."'");
		$row = mysql_fetch_array($res);
		$permite_editar = $row['permite_editar'];
		
		//Listado de plazas
		$select= " SELECT a.* FROM ajuste_saldos a
		WHERE a.plaza='".$_POST['plazausuario']."'";
		$select.=" AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		if ($_POST['usuario']!="") { $select.=" AND a.usuario='".$_POST['usuario']."' "; }
		if ($_POST['depositante']!="") { $select.=" AND a.depositante='".$_POST['depositante']."' "; }
		if ($_POST['estatus']!="") { $select.=" AND a.estatus='".$_POST['estatus']."' "; }
			
		$select.=" ORDER BY a.cve DESC";
		if($_POST['btn']==0) $select.=" LIMIT 1";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha Creacion</th><th>Fecha Aplicacion</th>
			<th>Depositante</th><th>Monto</th><th>Observaciones</th><th>Usuario</th>';
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
					if(nivelUsuario()>1 && ($row['fecha']==fechaLocal() || $_POST['cveusuario']==1 || $permite_editar==1))
						echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el pago\')) atcr(\'ajuste_saldos.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				}	
				echo '</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha_creacion'].' '.$row['hora']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha']).'</td>';
				
				if($permite_editar==1 && $row['estatus'] != 'C'){
					echo '<td align="center"><select id="depositante_'.$row['cve'].'">';
					foreach($array_depositantes as $k=>$v){
						echo '<option value="'.$k.'"';
						if($k==$row['depositante']) echo ' selected';
						echo '>'.$v.'</option>';
					}
					echo '</select><br>
					<input type="button" value="Guardar" onClick="guardarCampo('.$row['cve'].', \''.$row['depositante'].'\',\'depositante\',\'Depositante\',\'array_depositantes\')"></td>';
				}
				else{
					echo '<td align="center">'.htmlentities(utf8_encode($array_depositantes[$row['depositante']])).'</td>';
				}
				echo '<td align="center">'.number_format($row['monto'],2).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($row['obs'])).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
				$t+=$row['monto'];
			}
			echo '	
				<tr>
				<td colspan="5" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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


if($_POST['ajax']==40){
	mysql_query("UPDATE ajuste_saldos SET ".$_POST['campo']."='".$_POST['valor']."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
	
	mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['folio']."',fecha='".fechaLocal()." ".horaLocal()."',obs='".$_POST['plazausuario']."',
			dato='".$_POST['nombre']."',nuevo='".$_POST['valor']."',anterior='".$_POST['valor_anterior']."',arreglo='".$_POST['arreglo']."',usuario='".$_POST['cveusuario']."'");
	exit();
}



top($_SESSION);


if($_POST['cmd']==3){
	mysql_query("UPDATE ajuste_saldos SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
		$res = mysql_query("SELECT * FROM ajuste_saldos WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' ORDER BY cve DESC LIMIT 1");
		$row = mysql_fetch_array($res);
		$fecha4min = date('Y-m-d H:i:s', strtotime ( "+ 4 minute" , strtotime($row['fecha'].' '.$row['hora']) ) );
		if($row['monto']!=$_POST['monto'] || $row['depositante']!=$_POST['depositante'] || $fecha4min<date('Y-m-d H:i:s')){
			
			$insert = " INSERT ajuste_saldos 
							SET 
							plaza = '".$_POST['plazausuario']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',
							monto='".$_POST['monto']."',
							depositante='".$_POST['depositante']."',
							usuario='".$_POST['cveusuario']."',estatus='A',
							obs='".$_POST['obs']."'";
			mysql_query($insert);
			$cvecobro = mysql_insert_id();
		}
				
	
	$_POST['cmd']=1;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		$res = mysql_query("SELECT * FROM usuarios WHERE cve='".$_POST['cveusuario']."'");
		$row = mysql_fetch_array($res);
		$permite_editar = $row['permite_editar'];
		
		$res = mysql_query("SELECT * FROM ajuste_saldos WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		$nivel=nivelUsuario();
		echo '
			<tr>';
			if($nivel>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();validar('.$_POST['reg'].');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'ajuste_saldos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Ajuste de Saldos</td></tr>';
		echo '</table>';
		echo '<table style="font-size:15px">';
		echo '<tr><th align="left">Fecha Aplicacion</td><td><input type="text" name="fecha" id="fecha" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>';
		echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
		echo '</td></tr>';
		
		echo '<tr><th align="left">Depositante</th><td><select name="depositante" id="depositante" style="font-size:20px"><option value="0">Seleccione</option>';
		foreach($array_depositantes as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row['depositante']==$k) echo ' selected';
			echo '>'.$v.'</option>';
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
					else if((document.forma.monto.value/1)==0){
						$("#panel").hide();
						alert("El monto no puede ser cero");
					}
					else{
						atcr("ajuste_saldos.php","",2,reg);
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
		echo '<td><a href="#" onClick="atcr(\'ajuste_saldos.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
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
	$res=mysql_query("SELECT b.cve,b.usuario FROM ajuste_saldos a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY b.usuario");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['usuario'].'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="">Todos</option>';
	foreach($array_estatus as $k=>$v){
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
			objeto.open("POST","ajuste_saldos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&btn="+btn+"&depositante="+document.getElementById("depositante").value+"&estatus="+document.getElementById("estatus").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
		
	function guardarCampo(folio, valor_anterior, campo, nombre, arreglo){
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","ajuste_saldos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=40&arreglo="+arreglo+"&nombre="+nombre+"&campo="+campo+"&folio="+folio+"&valor_anterior="+valor_anterior+"&valor="+document.getElementById(campo+"_"+folio).value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros(1);}
			}
		}
	}
	
	
	
	
	</Script>
	';

	
}
	
bottom();


?>

