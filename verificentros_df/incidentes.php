<?php 
include ("main.php");
	  function lastday() { 
      $month = date('m');
      $year = date('Y');
      $day = date("d", mktime(0,0,0, $month+1, 0, $year));
 
      return date('Y-m-d', mktime(0,0,0, $month, $day, $year));
  };

  function firstday() {
      $month = date('m');
      $year = date('Y');
      return date('Y-m-d', mktime(0,0,0, $month, 1, $year));
  }
$res = mysql_query("SELECT * FROM cat_incidentes WHERE 1 ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_incidentes[$row['cve']]=$row['nombre']; 
	}
	
$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}
$res=mysql_db_query($base,"SELECT * FROM plazas");
	while($Plaza=mysql_fetch_array($res)){
		$array_plaza[$row['cve']]=$row['nombre'];
	}
$res = mysql_query("SELECT * FROM incidentes WHERE 1 ORDER BY plaza DESC");
while($row=mysql_fetch_array($res)){
	$array_plaza_[$row['plaza']]=$array_plaza[$row['plaza']]; 
	}
	if($_POST['cveusuario']==1){
		$res = mysql_query("SELECT * FROM incidentes where 1 group by usuario");
	}else{$res = mysql_query("SELECT * FROM incidentes where plaza='".$_POST['plazausuario']."' group by usuario");}
while($row=mysql_fetch_array($res)){
	$array_usuario_[$row['usuario']]=$array_usuario[$row['usuario']];
}
$array_estatuss=array( 0=>'Abierto',1=>'Cerrado');
/*** CONSULTA AJAX  **************************************************/
if($_POST['ajax']==1) {
		//Listado de plazas
		if($_POST['cveusuario']==1){
			$select= " SELECT * FROM incidentes WHERE 1 ";
			if ($_POST['plaza_']!="") { $select.=" AND plaza = '".$_POST['plaza_']."' "; }
		}
		else{
			$select= " SELECT * FROM incidentes WHERE plaza='".$_POST['plazausuario']."' ";
		}
		
		if($_POST['fini']!= "" and $_POST['ffin']== ""){$select.=" and fecha  BETWEEN '".$_POST['fini']."' AND '".fechaLocal()."'";}
		if($_POST['fini']== "" && $_POST['ffin']!= ""){$select.=" and fecha< '".$_POST['fini']."'";}
		if($_POST['fini']!= "" && $_POST['ffin']!= ""){$select.=" and fecha  BETWEEN '".$_POST['fini']."' AND '".$_POST['ffin']."'";}
		if ($_POST['folio']!="") { $select.=" AND cve = '".$_POST['folio']."' "; }
		if ($_POST['usuario']!="") { $select.=" AND usuario = '".$_POST['usuario']."' "; }
		if ($_POST['estatus']!="") { $select.=" AND estatus = '".$_POST['estatus']."' "; }
		$select.=" ORDER BY cve desc";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th></th><th>Folio</th>';if($_POST['cveusuario']==1){echo'<th>Plaza</th>';}echo'<th>Fecha</th><th>Motivo</th><th>Observaciones</th><th>Usuario</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				if($row['estatus']==1){
					echo '<td align="center">Cerrado </br>'.$row['fecha_cerrado'].' '.$row['hora_cerrado'].'</td>';
				}else{
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef['nombre'].'"></a></td>';
				}
				echo '<td align="center">'.htmlentities(utf8_encode($row['cve_aux'])).'</td>';
				if($_POST['cveusuario']==1){echo '<td align="center">'.htmlentities(utf8_encode($array_plaza[$row['plaza']])).'</td>';}
				echo '<td align="center" width="100">'.htmlentities(utf8_encode($row['fecha'])).' '.$row['hora'].'</td>';
				echo '<td align="" width="250">'.htmlentities(utf8_encode($array_incidentes[$row['incidente']])).'</td>';
				echo '<td align="center" width="500">';
				$sel= " SELECT * FROM incidentes_obs WHERE cve_aux='".$row['cve']."' ";
				$sel.=" ORDER BY cve desc";
				$re=mysql_query($sel);
			echo'<table width="100%">';
				while($row1=mysql_fetch_array($re)) {
					echo'<tr><td>('.$row1['fecha'].' '.$row1['hora'].')  **'.utf8_encode($row1['obs']).'**     ('.$array_usuario[$row1['usuario']].')</td></tr>';
				}
			echo'</table>';
				echo'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($array_usuario[$row['usuario']])).'</td>';
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

if($_POST['cmd']==6){
	$res=mysql_query("SELECT * FROM imagenes_incidentes WHERE cve='".$_POST['cvefoto']."'");
	$row=mysql_fetch_array($res);
//	unlink("imgincidentes/".$row['nombre'].".jpg");
//	mysql_query("DELETE FROM imagenes_incidentes WHERE cve='".$_POST['cvefoto']."'");
	mysql_query("update imagenes_incidentes set estatus='1' WHERE cve='".$_POST['cvefoto']."'");
	$_POST['cmd']=4;
}

if($_POST['cmd']==5){
	if(is_uploaded_file ($_FILES['foto']['tmp_name'])){
		/*if(file_exists("fotos/foto".$_POST['reg'].".jpg")){
			unlink("fotos/foto".$id.".jpg");
		}*/
		mysql_query("INSERT imagenes_incidentes SET incidente='".$_POST['reg']."'");
		$id=mysql_insert_id();
		$arch = $_FILES['foto']['tmp_name'];
		$nombre="foto".$_POST['reg']."_".$id;
		copy($arch,"imgincidentes/".$nombre.".jpg");
		chmod("imgincidentes/".$nombre.".jpg", 0777);
		mysql_query("UPDATE imagenes_incidentes SET nombre='$nombre' WHERE cve='$id'");
		
	}
	$_POST['cmd']=4;
}

top($_SESSION);
/*** ACTUALIZAR REGISTRO  **************************************************/
	if($_POST['cmd']==4){
		$select1="SELECT * FROM incidentes WHERE cve='".$_POST['reg']."'";
		$rsaccidentes=mysql_query($select1);
		$Accidente=mysql_fetch_array($rsaccidentes);
		if($_POST['reg']==0){
			$Accidente['folio']="Nuevo";
			$Accidente['fecha']=fechaLocal();
		}
		//Menu
		echo '<table>';
		echo '
			<tr>';

				echo '<td><a href="#" onClick="
					atcr(\'incidentes.php\',\'\',\'5\',\''.$Accidente['cve'].'\');"
				><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
				
			
			echo '<td><a href="#" onClick="atcr(\'incidentes.php\',\'\',\'1\',\''.$_POST['reg'].'\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Fotos del Incidente  '.$Accidente['cve_aux'].'</td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<table>';
		echo '<tr><th>Nueva Foto</th><td><input type="file" name="foto" id="foto" class="textField"></td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="cvefoto" value="">';
		echo '<table><tr><th>No.</th><th>Foto</th>';
		if($_POST['cveusuario']==1){
			echo '<th>Borrar</th>';
		}
		echo '</tr>';
		$rsFotos=mysql_query("SELECT * FROM imagenes_incidentes WHERE incidente='".$_POST['reg']."' and estatus!='1' ORDER BY cve");
		$x=1;
		while($Foto=mysql_fetch_array($rsFotos)){
			rowb();
			echo '<td align="center"><b>'.$x.'</b></td>';
			echo '<td><img src="imgincidentes/'.$Foto['nombre'].'.jpg" border="1" height="200" width="200"></td>';
			if($_POST['cveusuario']==1){
				echo '<td align="center"><a href="#" onClick="document.forma.cvefoto.value=\''.$Foto['cve'].'\';atcr(\'incidentes.php\',\'\',\'6\',\''.$_POST['reg'].'\')"><img src="images/basura.gif" border="0" title="Borrar"></a></td></td>';
			}
			echo '</tr>';
			$x++;
		}
		echo '</table>';


	}

if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			if($_POST['estatus']!=0){
			$update = " UPDATE incidentes 
						SET estatus='1',fecha_cerrado='".fechaLocal()."',hora_cerrado='".horaLocal()."',usu_cerrado='".$_POST['cveusuario']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update) or die(mysql_error());
			}
			
			if($_POST['obs']!=""){
			$insert = " INSERT INTO incidentes_obs 
						(cve_aux,fecha,hora,obs,usuario)
						VALUES 
						('".$_POST['reg']."','".fechaLocal()."','".horaLocal()."','".$_POST['obs']."','".$_POST['cveusuario']."')";
			$ejecutar = mysql_query($insert);
			}
			
		if(is_uploaded_file ($_FILES['foto']['tmp_name'])){
		/*if(file_exists("fotos/foto".$_POST['reg'].".jpg")){
			unlink("fotos/foto".$id.".jpg");
		}*/
		mysql_query("INSERT imagenes_incidentes SET incidente='".$_POST['reg']."'");
		$id=mysql_insert_id();
		$arch = $_FILES['foto']['tmp_name'];
		$nombre="foto".$_POST['reg']."_".$id;
		copy($arch,"imgincidentes/".$nombre.".jpg");
		chmod("imgincidentes/".$nombre.".jpg", 0777);
		mysql_query("UPDATE imagenes_incidentes SET nombre='$nombre' WHERE cve='$id'");
		
	}
	} else {
		$select= " SELECT * FROM incidentes WHERE plaza='".$_POST['plazausuario']."' ";
		$select.=" ORDER BY cve_aux desc";
		$res=mysql_query($select);
		$ro=mysql_fetch_array($res);
		$folio=$ro['cve_aux'] + 1;
			//Insertar el Registro
			$insert = " INSERT INTO incidentes 
						(cve_aux,fecha,hora,incidente,plaza,usuario,folio_ticket,placa_mal,placa_correcta,certificado_mal,certificado_correcto)
						VALUES 
						('".$folio."','".$_POST['fecha']."','".horaLocal()."','".$_POST['incidente']."','".$_POST['plazausuario']."','".$_POST['cveusuario']."',
						'".$_POST['folio_ticket']."','".$_POST['placa_mal']."','".$_POST['placa_correcta']."','".$_POST['certificado_mal']."','".$_POST['certificado_correcto']."')";
			$ejecutar = mysql_query($insert) or die(mysql_error());
			$id=mysql_insert_id();
			$id_inc=$id;
			$insert = " INSERT INTO incidentes_obs 
						(cve_aux,fecha,hora,obs,usuario)
						VALUES 
						('".$id."','".fechaLocal()."','".horaLocal()."','".$_POST['obs']."','".$_POST['cveusuario']."')";
			$ejecutar = mysql_query($insert);
			
			if(is_uploaded_file ($_FILES['foto']['tmp_name'])){
		/*if(file_exists("fotos/foto".$_POST['reg'].".jpg")){
			unlink("fotos/foto".$id.".jpg");
		}*/
		mysql_query("INSERT imagenes_incidentes SET incidente='".$id_inc."'");
		$id=mysql_insert_id();
		$arch = $_FILES['foto']['tmp_name'];
		$nombre="foto".$id_inc."_".$id;
		copy($arch,"imgincidentes/".$nombre.".jpg");
		chmod("imgincidentes/".$nombre.".jpg", 0777);
		mysql_query("UPDATE imagenes_incidentes SET nombre='$nombre' WHERE cve='$id'");
		
	}
	}
	$_POST['cmd']=0;
}
/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
	
		/*if($_POST['cveusuario']==1){
		$array_estatuss=array( 0=>'Abierto',1=>'Cerrado');
		}else{
			$array_estatuss=array( 0=>'Abierto');
		}*/	
		$select=" SELECT * FROM incidentes WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();
				if(document.forma.incidente.value==\'\'){
					$(\'#panel\').hide();
					alert(\'Necesita ingresar el Incidente\');
				}
				else{atcr(\'incidentes.php\',\'\',\'2\',\''.$row['cve'].'\');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'incidentes.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
			if($_POST['reg']){echo '<td style="font-size:17px">&nbsp;&nbsp;<a href="#" onClick="atcr(\'incidentes.php\',\'\',\'4\',\''.$_POST['reg'].'\');"><img src="images/historial.gif" border="0" height="20" width="20"><font color="RED">&nbsp; **Agregar Imagenes del Incidente** </a></font></td><td>&nbsp;</td>';}
			echo'</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Incidentes</td></tr>';
		echo '</table>';
		
		echo '<table>';
//		if($_POST['cveusuario']==1){
				
//		}
		if($_POST['reg']){
			echo '<tr><th align="left">Estatus</th><td><select name="estatus" id="estatus" class="textField">';
				foreach ($array_estatuss as $k=>$v) { 
					echo '<option value="'.$k.'"';if($row['estatus']==$k){echo'selected';}echo'>'.$v.'</option>';
				}
		echo '</select></td></tr>';
			echo'<tr>
					<th align="left">Folio</th><td><input type="text" name="" size="10" id="" value="'.$row['cve_aux'].'" class="readOnly" readonly></td>
				 </tr>
				 <tr>
					<th align="left">Fecha</th><td><input type="text" name="fecha" id="fecha" value="'.$row['fecha'].'" class="readOnly" readonly></td>
				 </tr>
				 <tr><th align="left">Folio de Ticket</th><td><input type="text" size="" name="folio_ticket" id="folio_ticket" value="'.$row['folio_ticket'].'" class="readOnly" readonly></td></tr>
		<tr><th align="left">Placa Erronea</th><td><input type="text" size="" name="placa_mal" id="placa_mal" value="'.$row['placa_mal'].'" class="readOnly" readonly></td></tr>
		<tr><th align="left">Placa Correcta</th><td><input type="text" size="" name="placa_correcta" id="placa_correcta" value="'.$row['placa_correcta'].'" class="readOnly" readonly></td></tr>
		<tr><th align="left">Certificado Erroneo</th><td><input type="text" size="" name="certificado_mal" id="certificado_mal" value="'.$row['certificado_mal'].'" class="readOnly" readonly></td></tr>
		<tr><th align="left">Certificado Correcto</th><td><input type="text" size="" name="certificado_correcto" id="certificado_correcto" value="'.$row['certificado_correcto'].'" class="readOnly" readonly></td></tr>
				 <tr>
					<th align="left">Incidente</th><td><input type="text" name="incidente" size="30" id="incidente" value="'.$array_incidentes[$row['incidente']].'" class="readOnly" readonly>
				 </tr>
				 <tr>
					<th align="left">Observaciones</th><td><input type="text" width:"70px" height:"10px" name="obs" size="100" id="obs" value="">
				 </tr>';
		}else{
		echo '<tr><th align="left">Fecha</th><td><input type="text" name="fecha" id="fecha" size="" value="'.fechaLocal().'" class="readOnly" readOnly></td></tr>
				<tr><th align="left">Folio de Ticket</th><td><input type="text" size="" name="folio_ticket" id="folio_ticket" value="'.$row['folio_ticket'].'"></td></tr>
		<tr><th align="left">Placa Erronea</th><td><input type="text" size="" name="placa_mal" id="placa_mal" value="'.$row['placa_mal'].'"></td></tr>
		<tr><th align="left">Placa Correcta</th><td><input type="text" size="" name="placa_correcta" id="placa_correcta" value="'.$row['placa_correcta'].'"></td></tr>
		<tr><th align="left">Certificado Erroneo</th><td><input type="text" size="" name="certificado_mal" id="certificado_mal" value="'.$row['certificado_mal'].'"></td></tr>
		<tr><th align="left">Certificado Correcto</th><td><input type="text" size="" name="certificado_correcto" id="certificado_correcto" value="'.$row['certificado_correcto'].'"></td></tr>';
		
		
		
		echo '<tr><th align="left">Incidente</th><td><select name="incidente" id="incidente" class="textField"><option value="">---Seleccione---</option>';
		  foreach ($array_incidentes as $k=>$v) { 
	    echo '<option value="'.$k.'"';echo'>'.$v.'</option>';
			}
		echo '</select></td></tr>
			  <tr><th align="left">Observaciones</th><td><input type="text" width:"70px" height:"10px" size="100" name="obs" id="obs" value=""></td></tr>';
		}
		echo '</table></br></br></br>';
	//	if($_POST['reg']){
		$select= " SELECT * FROM incidentes_obs WHERE cve_aux='".$_POST['reg']."' ";
		$select.=" ORDER BY cve desc";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		echo'<table with="100%"><tr><td valign="top"><table>
			<tr bgcolor="#E9F2F8"><td>Fecha</td><td>Observaciones</td><td>Usuario</td></tr>';
		while($row=mysql_fetch_array($res)) { 
		rowb();
		echo'
		<td>'.$row['fecha'].' '.$row['hora'].'</td>
		<td>'.$row['obs'].'</td>
		<td>'.$array_usuario[$row['usuario']].'</td>
		</tr>';
			
		}
		echo'</table></td><td>';
				echo '<table>';
				echo '<tr><th>Nueva Foto</th><td><input type="file" name="foto" id="foto" class="textField"></td></tr>';
		echo '<br>';
		echo '<input type="hidden" name="cvefoto" value="">';echo'<tr><th>No.</th><th>Foto</th>';
	//	if($_POST['cveusuario']==1){
		//	echo '<th>Borrar</th>';
		//}
		echo '</tr>';
		$rsFotos=mysql_query("SELECT * FROM imagenes_incidentes WHERE incidente='".$_POST['reg']."' and estatus!='1' ORDER BY cve");
		$x=1;
		while($Foto=mysql_fetch_array($rsFotos)){
			rowb();
			echo '<td align="center"><b>'.$x.'</b></td>';
			echo '<td><img src="imgincidentes/'.$Foto['nombre'].'.jpg" border="1" height="200" width="200"></td>';
		//	if($_POST['cveusuario']==1){
		//		echo '<td align="center"><a href="#" onClick="document.forma.cvefoto.value=\''.$Foto['cve'].'\';atcr(\'incidentes.php\',\'\',\'6\',\''.$_POST['reg'].'\')"><img src="images/basura.gif" border="0" title="Borrar"></a></td>';
		//	}
			echo '</tr>';
			$x++;
		}
		echo '</table></td></tr></table>';
	//	}
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'incidentes.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>
		      <tr>
	       <td><span>Fecha inicial</span></td>
           <td><input size="10" value="" name="fini" id="fini" type="text" class="readOnly" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td>
           </tr>
           <tr>
           <td><span>Fecha final</span></td>
           <td><input size="10" value="" name="ffin" id="ffin" type="text" class="readOnly" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].ffin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td>
           </tr>';
		echo '<tr><td>Folio</td><td><input type="text" name="folio" id="folio" size="" class="textField" value=""></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus" class="textField"><option value="">--Todos---</option>';
				foreach ($array_estatuss as $k=>$v) { 
					echo '<option value="'.$k.'"';echo'>'.$v.'</option>';
			}
		echo '</select></td></tr>';
		if($_POST['cveusuario']==1){
			echo '<tr><td>Plaza</td><td><select name="plaza_" id="plaza_" class="textField"><option value="">--Seleccione---</option>';
				foreach ($array_plaza_ as $k=>$v) { 
					echo '<option value="'.$k.'"';echo'>'.$v.'</option>';
			}
	echo '</select></td></tr>';
		}else{echo '<tr><td><input type="hidden" name="plaza_" id="plaza_" value=""></td><td></td>';
	echo '</tr>';
	}
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario" class="textField"><option value="">--Seleccione---</option>';
				foreach ($array_usuario_ as $k=>$v) { 
					echo '<option value="'.$k.'"';echo'>'.$v.'</option>';
			}
		echo '</select></td></tr>';
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
			objeto.open("POST","incidentes.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&folio="+document.getElementById("folio").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&fini="+document.getElementById("fini").value+"&ffin="+document.getElementById("ffin").value+"&estatus="+document.getElementById("estatus").value+"&plaza_="+document.getElementById("plaza_").value+"&usuario="+document.getElementById("usuario").value);
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

