<?php 

include ("main.php"); 

/*** ARREGLOS ***********************************************************/

$rsPlaza=mysql_query("SELECT * FROM plazas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['numero'];
}


$res = mysql_query("SELECT * FROM motivos_checada ORDER BY cve");
while($row = mysql_fetch_array($res)) $array_motivo[$row['cve']] = $row['nombre'];

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de personal
		
		$select= " SELECT a.cve,a.plaza,a.nombre,MIN(c.fechahora) as entrada, MAX(c.fechahora) as salida
		 FROM personal a 
		LEFT JOIN checada_lector c ON a.cve = c.cvepersonal AND DATE(c.fechahora) = CURDATE() 
		WHERE a.estatus=1 ";
		if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
		if ($_POST['num']!="") { $select.=" AND a.cve='".$_POST['num']."'"; }
		if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; }
		$select.=" GROUP BY a.cve ORDER BY trim(a.nombre)";
		$rspersonal=mysql_query($select);
		$totalRegistros = mysql_num_rows($rspersonal);
		
		if(mysql_num_rows($rspersonal)>0) 
		{
			
			/*echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<thead><tr bgcolor="#E9F2F8"><td colspan="5">'.mysql_num_rows($rspersonal).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8">';
			echo '<th>Plaza</th>';
			echo '<th><a href="#" onclick="SortTable(1,\'N\',\'tabla1\');">No.</a></th>
			<th><a href="#" onclick="SortTable(2,\'S\',\'tabla1\');">Nombre</a></th><th>Entrada</th><th>Salida</th>';
			echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
				rowb();
				echo '<td>'.utf8_encode($array_plaza[$Personal['plaza']]).'</td>';
				echo '<td align="center"><a href="#" onClick="atcr(\'personal_lite.php\',\'\',1,'.$Personal['cve'].');">'.$Personal['cve'].'</a></td>';
				if(file_exists("imgpersonal/foto".$Personal['cve'].".jpg"))
					echo '<td align="left" onMouseOver="document.getElementById(\'foto'.$Personal['cve'].'\').style.visibility=\'visible\';" onMouseOut="document.getElementById(\'foto'.$Personal['cve'].'\').style.visibility=\'hidden\';">'.$Personal['nombre'].'<img width="200" id="foto'.$Personal['cve'].'" height="250" style="position:absolute;visibility:hidden" src="imgpersonal/foto'.$Personal['cve'].'.jpg?'.date('h:i:s').'" border="1"></td>';
				else
					echo '<td align="left">'.htmlentities(utf8_encode(trim($Personal['nombre']))).'</td>';
				if($Personal['entrada'] == $Personal['salida']) $Personal['salida'] = '';
				echo '<td align="center">'.$Personal['entrada'].'</td>';
				echo '<td align="center">'.$Personal['salida'].'</td>';
				echo '</tr>';
			}*/

			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<thead><tr bgcolor="#E9F2F8"><td colspan="15">'.mysql_num_rows($rspersonal).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8">';
			$c=0;
			if(count($array_plaza)>1){
				echo '<th>Plaza</th>';
				$c++;
			}
			echo '<th><a href="#" onclick="SortTable('.$c.',\'N\',\'tabla1\');">No.</a></th>
			<th><a href="#" onclick="SortTable('.($c+1).',\'S\',\'tabla1\');">Nombre</a></th>';		
			foreach($array_motivo as $v) echo '<th>'.$v.'</th>';
			echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
				rowb();
				if(count($array_plaza)>1)
					echo '<td align="center">'.$array_plaza[$Personal['plaza']].'</td>';
				echo '<td align="center"><a href="#" onClick="atcr(\'personal_lite.php\',\'\',1,'.$Personal['cve'].');">'.$Personal['cve'].'</a></td>';
				echo '<td align="left">'.$Personal['nombre'].'</td>';
				
				$horas = array();
				$resPersonal1 = mysql_query("SELECT IF(tipo=0,1,tipo),fechahora FROM checada_lector WHERE cvepersonal='{$Personal['cve']}' AND DATE(fechahora)='".fechaLocal()."' GROUP BY IF(tipo=0,1,tipo)");
				while($Personal1 = mysql_fetch_array($resPersonal1)) $horas[$Personal1[0]] = $Personal1[1];
				foreach($array_motivo as $k=>$v) echo '<td align="center">'.substr($horas[$k],11,8).'</td>';
				
				echo '</tr>';
			}
			
			echo '</tbody>
				<tr>
				<td colspan="'.(3+count($array_motivo)).'" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
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

if($_POST['cmd']==2){
	$res1=mysql_query("SELECT * FROM cat_documentos_personal");
	while($row1 = mysql_fetch_array($res1)){
		if(is_uploaded_file ($_FILES['archivo_'.$row1['cve']]['tmp_name'])){
			$arch = $_FILES['archivo_'.$row1['cve']]['tmp_name'];
			$ext_aux = explode(".",$_FILES['archivo_'.$row1['cve']]['name']);
			$ext = end($ext_aux);
			if($_POST['clave_'.$row1['cve']]>0){
				$iddoc = $_POST['clave_'.$row1['cve']];
				mysql_query("UPDATE documentos_personal SET nombre='".$_FILES['archivo_'.$row1['cve']]['name']."' WHERE id = '$iddoc'");
			}
			else{
				mysql_query("INSERT documentos_personal SET personal='".$_POST['reg']."',documento='".$row1['cve']."',nombre='".$_FILES['archivo_'.$row1['cve']]['name']."'");
				$iddoc = mysql_insert_id();
			}
			$nombredoc='doc_'.$iddoc.'.'.$ext;
			copy($arch,"imgpersonal/".$nombredoc);
			chmod("imgpersonal/".$nombredoc, 0777);
		}
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==1){
	//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick=""$(\'#panel\').show();atcr(\'personal_lite.php\',\'\',\'2\',\''.$_POST['reg'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'personal_lite.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		$res = mysql_query("SELECT * FROM personal WHERE cve='".$_POST['reg']."'");
		$row = mysql_fetch_array($res);
		echo '<h2>Documentos del Personal '.$row['nombre'].'</h2>';
		echo '<table>';
		$res1=mysql_query("SELECT a.cve,a.nombre,b.cve as iddoc, b.nombre as archivo FROM cat_documentos_personal a LEFT JOIN documentos_personal b ON a.cve = b.documento AND b.personal = '".$_POST['reg']."'");
		while($row1 = mysql_fetch_array($res1)){
			echo '<tr><th align="left">'.$row1['nombre'].'</th>
			<td><input type="file" name="archivo_'.$row1['cve'].'">
			<input type="hidden" name="clave_'.$row1['cve'].'" value="'.$row1['iddoc'].'">';
			if($row1['iddoc']>0){
				$ext_aux = explode(".",$row1['archivo']);
				$ext = end($ext_aux);
				$nombredoc='doc_'.$row1['iddoc'].'.'.$ext;
				'<span style="cursor:pointer;color:#0000FF;" onClick="atcr(\'imgpersonal/'.$nombredoc.'?'.date('h:i:s').'\',\'_blank\',0,0);">'.$row1['archivo'].'</span>';
			}
			echo '</td></tr>';
		}
		echo '</table>';
}
/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" class="textField">';
		if($_POST['plazausuario']==0)
			echo '<option value="all">---Todas---</option>';
		foreach($array_plaza as $k=>$v){
			if($_POST['plazausuario']==0 || $_POST['plazausuario']==$k)
				echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td><td></td><td>&nbsp;</td></tr>';
		echo '<tr style="display:none;"><td>Nombre</td><td><input type="text" name="nombre" id="nombre" class="textField"></td></tr>'; 
		echo '<tr><td>Numero</td><td><input type="text" name="num" id="num" class="textField"></td></tr>'; 
		echo '</table>';
		echo '<br>';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	


/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros(ordenamiento,orden)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","personal_lite.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nombre="+document.getElementById("nombre").value+"&num="+document.getElementById("num").value+"&plaza="+document.getElementById("plaza").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
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
	/*if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			 //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}*/
	echo '
	buscarRegistros();
	</Script>
';
}
	
bottom();

?>

