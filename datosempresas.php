<?php 

include ("main.php"); 

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	
		//Listado de tecnicos y administradores
		$select= " SELECT * FROM empresas WHERE 1 ";
		if ($_POST['nombre']!="") { $select.=" AND (nombre LIKE '%".$_POST['nombre']."%' OR rfc LIKE '%".$_POST['nombre']."%'"; }
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY nombre DESC";
		$res=mysql_query($select);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="3">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Nombre</th><th>RFC</th><th>ID Plaza</th><th>ID Certificado</th></tr>';
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center"><a href="#" onClick="atcr(\'empresas.php\',\'\',1,\''.$row['cve'].'\')"><img src="images/modificar.gif" border="0" title="Editar"></a></td>';
				echo '<td align="left">'.htmlentities($row['nombre']).'</td>';
				echo '<td align="left">'.htmlentities($row['rfc']).'</td>';
				echo '<td align="left">'.htmlentities($row['idplaza']).'</td>';
				echo '<td align="left">'.htmlentities($row['idcertificado']).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="3" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	$campos="";
	foreach($_POST['camposi'] as $k=>$v){
		$campos.=",".$k."='".$v."'";
	}	
	if($_POST['reg']) {
		//Actualizar el Registro
		$update = " UPDATE datosempresas 
					SET nombre='".$_POST['nombre']."',rfc='".$_POST['rfc']."',idplaza='".$_POST['idplaza']."',idcertificado='".$_POST['idcertificado']."',
					usuario='".$_POST['usuario']."',pass='".$_POST['pass']."',timbra='".$_POST['timbra']."',logoencabezado='".$_POST['logoencabezado']."'".$campos."
					WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_query($update);			
		$id=$_POST['reg'];
	} else {
		//Insertar el Registro
		$insert = " INSERT datosempresas 
					SET plaza='".$_POST['plazausuario']."',nombre='".$_POST['nombre']."',rfc='".$_POST['rfc']."',idplaza='".$_POST['idplaza']."',idcertificado='".$_POST['idcertificado']."',
					usuario='".$_POST['usuario']."',pass='".$_POST['pass']."',timbra='".$_POST['timbra']."',logoencabezado='".$_POST['logoencabezado']."'".$campos."";
		$ejecutar = mysql_query($insert) or die(mysql_error());
		$id = mysql_insert_id();
	}
	
	/*if($_POST['timbra']==1)
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE empresa='".$id."' AND tipo=0 AND tipodocumento=1");
	else
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE empresa='".$id."' AND tipo=1 AND tipodocumento=1");
	if($row1=mysql_fetch_array($res1)){
		mysql_query("UPDATE foliosiniciales SET folio_inicial='".$_POST['folio_inicial']."' WHERE cve='".$row1['cve']."'");
	}
	else{
		if($_POST['timbra']==1)
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial']."',empresa='".$id."',tipo=0,tipodocumento=1");
		else
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial']."',empresa='".$id."',tipo=1,tipodocumento=1");
	}
	
	if($_POST['timbra']==1)
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE empresa='".$id."' AND tipo=0 AND tipodocumento=2");
	else
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE empresa='".$id."' AND tipo=1 AND tipodocumento=2");
	if($row1=mysql_fetch_array($res1)){
		mysql_query("UPDATE foliosiniciales SET folio_inicial='".$_POST['folio_inicial2']."' WHERE cve='".$row1['cve']."'");
	}
	else{
		if($_POST['timbra']==1)
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial2']."',empresa='".$id."',tipo=0,tipodocumento=2");
		else
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial2']."',empresa='".$id."',tipo=1,tipodocumento=2");
	}
	
	if($_POST['timbra']==1)
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE empresa='".$id."' AND tipo=0 AND tipodocumento=3");
	else
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE empresa='".$id."' AND tipo=1 AND tipodocumento=3");
	if($row1=mysql_fetch_array($res1)){
		mysql_query("UPDATE foliosiniciales SET folio_inicial='".$_POST['folio_inicial3']."' WHERE cve='".$row1['cve']."'");
	}
	else{
		if($_POST['timbra']==1)
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial3']."',empresa='".$id."',tipo=0,tipodocumento=3");
		else
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial3']."',empresa='".$id."',tipo=1,tipodocumento=3");
	}
	
	if($_POST['timbra']==1)
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE empresa='".$id."' AND tipo=0 AND tipodocumento=4");
	else
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE empresa='".$id."' AND tipo=1 AND tipodocumento=4");
	if($row1=mysql_fetch_array($res1)){
		mysql_query("UPDATE foliosiniciales SET folio_inicial='".$_POST['folio_inicial4']."' WHERE cve='".$row1['cve']."'");
	}
	else{
		if($_POST['timbra']==1)
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial4']."',empresa='".$id."',tipo=0,tipodocumento=4");
		else
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial4']."',empresa='".$id."',tipo=1,tipodocumento=4");
	}
	
	if($_POST['borrar_foto']=="S")
		unlink("logos/logo".$id.".jpg");
	if(is_uploaded_file ($_FILES['foto']['tmp_name'])){
		$arch = $_FILES['foto']['tmp_name'];
		copy($arch,"logos/logo".$id.".jpg");
		chmod("logos/logo".$id.".jpg", 0777);
	}*/
	$_POST['cmd']=0;
	
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==0) {
		$select=" SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		//Menu
		if(nivelUsuario()>1){
			echo '<table>';
			echo '
				<tr>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'datosempresas.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '</tr>';
			echo '</table>';
			echo '<br>';
		}
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Empresas</td></tr>';
		echo '</table>';

		//Formulario 
		//echo '<table width="100%"><tr><td>';
		echo '<table>';
		echo '<tr><th>Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"></td></tr>';
		echo '<tr><th>RFC</th><td><input type="text" name="rfc" id="rfc" class="textField" size="15" value="'.$row['rfc'].'"></td></tr>';
		echo '<tr><th>Email</th><td><input type="text" class="textField" name="camposi[email]" id="email" value="'.$row['email'].'" size="80"></td></tr>';
		echo '<tr><th>Regimen</th><td><input type="text" class="textField" name="camposi[regimen]" id="regimen" value="'.$row['regimen'].'" size="50"></td></tr>';
		echo '<tr><th>Calle</th><td><input type="text" class="textField" name="camposi[calle]" id="calle" value="'.$row['calle'].'" size="30"></td></tr>';
		echo '<tr><th>Numero Exterior</th><td><input type="text" class="textField" name="camposi[numexterior]" id="numexterior" value="'.$row['numexterior'].'" size="10"></td></tr>';
		echo '<tr><th>Numero Interior</th><td><input type="text" class="textField" name="camposi[numinterior]" id="numinterior" value="'.$row['numinterior'].'" size="10"></td></tr>';
		echo '<tr><th>Colonia</th><td><input type="text" class="textField" name="camposi[colonia]" id="colonia" value="'.$row['colonia'].'" size="30"></td></tr>';
		echo '<tr><th>Localidad</th><td><input type="text" class="textField" name="camposi[localidad]" id="localidad" value="'.$row['localidad'].'" size="50"></td></tr>';
		echo '<tr><th>Municipio</th><td><input type="text" class="textField" name="camposi[municipio]" id="municipio" value="'.$row['municipio'].'" size="50"></td></tr>';
		echo '<tr><th>Estado</th><td><input type="text" class="textField" name="camposi[estado]" id="estado" value="'.$row['estado'].'" size="50"></td></tr>';
		echo '<tr><th>Codigo Postal</th><td><input type="text" class="textField" name="camposi[codigopostal]" id="codigopostal" value="'.$row['codigopostal'].'" size="50"></td></tr>';
		echo '<tr><th>Numero de Lineas</th><td><input type="text" class="textField" name="camposi[numero_lineas]" id="numero_lineas" value="'.$row['numero_lineas'].'" size="10"></td></tr>';
		/*echo '<tr><th>ID Plaza</th><td><input type="text" name="idplaza" id="idplaza" class="textField" size="10" value="'.$row['idplaza'].'"></td></tr>';
		echo '<tr><th>ID Certificado</th><td><input type="text" name="idcertificado" id="idcertificado" class="textField" size="10" value="'.$row['idcertificado'].'"></td></tr>';
		echo '<tr><th>Usuario</th><td><input type="text" name="usuario" id="usuario" class="textField" size="20" value="'.$row['usuario'].'"></td></tr>';
		echo '<tr><th>Password</th><td><input type="text" name="pass" id="pass" class="textField" size="20" value="'.$row['pass'].'"></td></tr>';
		echo '<tr><th>Timbra las Facturas</th><td><input type="checkbox" name="timbra" id="timbra" value="1"';
		if($row['timbra']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr><th>Logo Encabezado</th><td><input type="checkbox" name="logoencabezado" id="logoencabezado" value="1"';
		if($row['logoencabezado']==1) echo ' checked';
		echo '></td></tr>';
		if($row['timbra']==1)
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE empresa='".$_POST['reg']."' AND tipo=0 AND tipodocumento=1");
		else
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE empresa='".$_POST['reg']."' AND tipo=1 AND tipodocumento=1");
		$row1=mysql_fetch_array($res1);
		echo '<tr><th>Folio Inicial Factura</th><td><input type="text" name="folio_inicial" id="folio_inicial" class="textField" size="10" value="'.$row1['folio_inicial'].'"></td></tr>';
		if($row['timbra']==1)
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE empresa='".$_POST['reg']."' AND tipo=0 AND tipodocumento=2");
		else
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE empresa='".$_POST['reg']."' AND tipo=1 AND tipodocumento=2");
		$row1=mysql_fetch_array($res1);
		echo '<tr><th>Folio Inicial Nota de Credito</th><td><input type="text" name="folio_inicial2" id="folio_inicial2" class="textField" size="10" value="'.$row1['folio_inicial'].'"></td></tr>';
		if($row['timbra']==1)
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE empresa='".$_POST['reg']."' AND tipo=0 AND tipodocumento=3");
		else
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE empresa='".$_POST['reg']."' AND tipo=1 AND tipodocumento=3");
		$row1=mysql_fetch_array($res1);
		echo '<tr><th>Folio Inicial Nota de Cargo</th><td><input type="text" name="folio_inicial3" id="folio_inicial3" class="textField" size="10" value="'.$row1['folio_inicial'].'"></td></tr>';
		if($row['timbra']==1)
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE empresa='".$_POST['reg']."' AND tipo=0 AND tipodocumento=4");
		else
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE empresa='".$_POST['reg']."' AND tipo=1 AND tipodocumento=4");
		$row1=mysql_fetch_array($res1);
		echo '<tr><th>Folio Inicial Remision</th><td><input type="text" name="folio_inicial4" id="folio_inicial4" class="textField" size="10" value="'.$row1['folio_inicial'].'"></td></tr>';
		echo '<tr><th>Porcentaje de Iva Retenido</th><td><input type="text" class="textField" name="camposi[por_iva_retenido]" id="por_iva_retenido" value="'.$row['por_iva_retenido'].'" size="5"></td></tr>';
		echo '<tr><th>Modificar porcentaje de iva retenido</th><td><input type="hidden" name="camposi[mod_iva_retenido]" id="mod_iva_retenido" value="'.intval($row['mod_iva_retenido']).'"><input type="checkbox" id="mod_iva_retenido_chk" value="1" onClick="if(this.checked) $(\'#mod_iva_retenido\').val(1); else $(\'#mod_iva_retenido\').val(0);"';
		if($row['mod_iva_retenido']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr><th>Porcentaje de ISR Retenido</th><td><input type="text" class="textField" name="camposi[por_isr_retenido]" id="por_isr_retenido" value="'.$row['por_isr_retenido'].'" size="5"></td></tr>';
		echo '<tr><th>Modificar porcentaje de isr retenido</th><td><input type="hidden" name="camposi[mod_isr_retenido]" id="mod_isr_retenido" value="'.intval($row['mod_isr_retenido']).'"><input type="checkbox" id="mod_isr_retenido_chk" value="1" onClick="if(this.checked) $(\'#mod_isr_retenido\').val(1); else $(\'#mod_isr_retenido\').val(0);"';
		if($row['mod_isr_retenido']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr><th>Carta Porte</th><td><input type="hidden" name="camposi[carta_porte]" id="carta_porte" value="'.intval($row['carta_porte']).'"><input type="checkbox" id="carta_porte_chk" value="1" onClick="if(this.checked) $(\'#carta_porte\').val(1); else $(\'#carta_porte\').val(0);"';
		if($row['carta_porte']==1) echo ' checked';
		echo '></td></tr>';*/
		echo '</table>';
		/*echo '</td><td valign="top">';
		echo '<table align="right"><tr><td colspan="2" align="center"><img width="200" height="250" src="logos/logo'.$_POST['reg'].'.jpg?'.date('h:i:s').'" border="1"></td></tr>';
		echo '<tr><th>Nuevo Logo</th><td><input type="file" name="foto" id="foto"></td></tr>';
		echo '<tr><th>Borrar Logo</th><td><input type="checkbox" name="borrar_foto" id="borrar_foto" value="S"></td></tr></table>';
		echo '</td></tr></table>';*/
	}


	
bottom();
?>

