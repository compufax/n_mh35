<?php
include ("main.php"); 
$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$array_motivo = array();
$res = mysql_query("SELECT * FROM motivos_gastos ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_motivo[$row['cve']]=$row['nombre'];
}


if($_POST['ajax']==1){
	$plazas_ids = "";
	foreach($array_plaza as $k=>$v){
		$plazas_ids.=",".$k;
	}
	$plazas_ids=substr($plazas_ids,1);
	$select = "SELECT * FROM depositos_gastos WHERE fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	if($_POST['usuario']!="all") $select .= " AND usuario='".$_POST['usuario']."'";
	$select.=" ORDER BY cve DESC";
	$res=mysql_query($select);
	if(mysql_num_rows($res)>0) 
	{
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		echo '<th>Folio</th><th>Fecha</th><th>Monto</th><th>Concepto</th><th>Usuario</th>';
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
				if(nivelUsuario()>1)
					echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el deposito\')) atcr(\'depositos_gastos.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
			}	
			echo '</td>';
			echo '<td align="center">'.htmlentities($row['cve']).'</td>';
			echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
			echo '<td align="right">'.number_format($row['monto'],2).'</td>';
			echo '<td align="left">'.htmlentities($row['obs']).'</td>';
			echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
			echo '</tr>';
			$t+=$row['monto'];
		}
		$c=3;
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



top($_SESSION);

if($_POST['cmd']==3){
	mysql_query("UPDATE depositos_gastos SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	$insert = " INSERT depositos_gastos 
					SET 
					fecha='".fechaLocal()."',hora='".horaLocal()."',monto='".$_POST['monto']."',obs='".$_POST['concepto']."',
					usuario='".$_POST['cveusuario']."',estatus='A'";
	mysql_query($insert);
	$_POST['cmd']=0;
}


if ($_POST['cmd']==1) {
		
	$select=" SELECT * FROM depositos_gastos WHERE cve='".$_POST['reg']."' ";
	$res=mysql_query($select);
	$row=mysql_fetch_array($res);			
	if($_POST['reg']==0){
		$row['fecha_corte']=fechaLocal();
	}
	//Menu
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();validar();"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'depositos_gastos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Depositos</td></tr>';
	echo '</table>';
	
	echo '<table>';
	echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="textField" size="10" value="'.$row['monto'].'"></td></tr>';
	echo '<tr><th align="left">Concepto</th><td><textarea cols="30" rows="3" name="concepto" id="concepto" class="textField">'.$row['concepto'].'</textarea></td></tr>';
	echo '</table>';
	
	echo '<script>
			function validar(){
				if((document.forma.monto.value/1)<=0){
					$("#panel").hide();
					alert("El monto debe de ser mayor a cero");
				}
				else if(document.forma.concepto.value==""){
					$("#panel").hide();
					alert("Debe de agregar el concepto");
				}
				else{
					atcr("depositos_gastos.php","",2,0);
				}
			}
			
			
		</script>';
	
}

if($_POST['cmd']==0){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'depositos_gastos.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.substr(fechaLocal(),0,8).'01" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="all">Todos</option>';
	if($_POST['plazausuario']==0)
		$res=mysql_query("SELECT b.cve,b.usuario FROM depositos_gastos a INNER JOIN usuarios b ON a.usuario = b.cve WHERE 1 GROUP BY a.usuario ORDER BY b.usuario");
	else
		$res=mysql_query("SELECT b.cve,b.usuario FROM depositos_gastos a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY b.usuario");
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
			objeto.open("POST","depositos_gastos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
		
	
	
	
	</Script>
	';

	
}
	
bottom();




?>