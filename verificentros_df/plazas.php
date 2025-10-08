<?php 

include ("main.php"); 

$rsDepto=mysql_query("SELECT * FROM areas");
while($Depto=mysql_fetch_array($rsDepto)){
	$array_localidad[$Depto['cve']]=$Depto['nombre'];
}

$array_regimensat=array();
$res = mysql_query("SELECT * FROM regimen_sat ORDER BY nombre");
while($row = mysql_fetch_assoc($res)) $array_regimensat[$row['clave']] = $row['nombre'];

$rsDepto=mysql_query("SELECT * FROM tipo_plaza");
while($Depto=mysql_fetch_array($rsDepto)){
	$array_tipo_plaza[$Depto['cve']]=$Depto['nombre'];
}
/*$array_tipo_plaza=array(1=>"CAPITAL",2=>"MUNICIPIO",3=>"MOVIL"
,4=>"TEOPANZOLCO"
,5=>"SAN DIEGO"
,6=>"JOJUTLA"
,7=>"JANTETELCO"
,8=>"MAZATEPEC"
,9=>"ARRASTRADERO"
,10=>"CUAHUTEMOC"
,11=>"TEMIXCO"
,12=>"CUAUTLA"
,13=>"JIUTEPEC"
,14=>"YECAPIXTLA"
,15=>"YAUTEPEC");*/

/*$array_tipo_plaza=array(1=>"Capital",2=>"Municipio",3=>"Movil"
,4=>"Teopanzolco"
,5=>"San Diego"
,6=>"Jojutla"
,7=>"Jantetelco"
,8=>"Mazatepec"
,9=>"Arrastradero"
,10=>"Cuahutemoc"
,11=>"Temixco"
,12=>"Cuautla"
,13=>"Jiutepec"
,14=>"Yecapixtla"
,15=>"Yautepec");*/

/*** ELIMINAR REGISTRO  **************************************************/

if ($_POST['cmd']==3) {
	$delete= "UPDATE productos SET borrado='SI' WHERE cve='".$_POST['reg']."' ";
	$ejecutar=mysql_query($delete);
	header("Location: plazas.php");
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	$campos="";
	foreach($_POST['camposi'] as $k=>$v){
		$campos.=",".$k."='".$v."'";
	}	
	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE plazas 
						SET nombre='".$_POST['nombre']."',numero='".$_POST['numero']."',tipo_plaza='".$_POST['tipo_plaza']."',estatus='".$_POST['estatus']."',local='".$_POST['local']."',genera_devolucion='".$_POST['genera_devolucion']."',
						genera_factura_mostrador='".$_POST['genera_factura_mostrador']."',validar_certificado_anterior='".$_POST['validar_certificado_anterior']."',nuevo_formato='".$_POST['nuevo_formato']."',
						lista='".$_POST['lista']."',num_intentos='".$_POST['num_intentos']."',intentoporcertificadodif='".$_POST['intentoporcertificadodif']."',
						bloqueada_sat='".$_POST['bloqueada_sat']."',validar_timbres='".$_POST['validar_timbres']."',timbres_exis='".$_POST['timbres_exis']."',
						bloquear_impresion='".$_POST['bloquear_impresion']."',
						entrega_simple='".$_POST['entrega_simple']."',maneja_medio_pago='".$_POST['maneja_medio_pago']."',monto_medio_pago='".$_POST['monto_medio_pago']."',
						cveclientefactura='".$_POST['cveclientefactura']."',regimensat='".$_POST['regimensat']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);	
			
			$update = " UPDATE datosempresas 
					SET nombre='".$_POST['nombre']."',rfc='".$_POST['rfc']."',idplaza='".$_POST['idplaza']."',idcertificado='".$_POST['idcertificado']."',
					usuario='".$_POST['usuario']."',pass='".$_POST['pass']."',timbra='".$_POST['timbra']."',logoencabezado='".$_POST['logoencabezado']."'".$campos."
					WHERE plaza='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update) or die(mysql_error());				
			$id=$_POST['reg'];
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO plazas 
						(nombre,numero,estatus,local,tipo_plaza,genera_devolucion,genera_factura_mostrador,
							validar_certificado_anterior,lista,num_intentos,intentoporcertificadodif,nuevo_formato,
							bloqueada_sat,validar_timbres,bloquear_impresion,
							cveclientefactura,regimensat,timbres_exis,entrega_simple,maneja_medio_pago,monto_medio_pago)
						VALUES 
						('".$_POST['nombre']."','".$_POST['numero']."','A','".$_POST['local']."','".$_POST['tipo_plaza']."',
							'".$_POST['genera_devolucion']."','".$_POST['genera_factura_mostrador']."',
							'".$_POST['validar_certificado_anterior']."','".$_POST['lista']."','".$_POST['num_intentos']."',
							'".$_POST['intentoporcertificadodif']."','".$_POST['nuevo_formato']."',
							'".$_POST['bloqueada_sat']."','".$_POST['validar_timbres']."','".$_POST['bloquear_impresion']."',
							'".$_POST['cveclientefactura']."','".$_POST['regimensat']."','".$_POST['timbres_exis']."','".$_POST['entrega_simple']."','".$_POST['maneja_medio_pago']."','{$_POST['monto_medio_pago']}')";
			$ejecutar = mysql_query($insert);
			$id = mysql_insert_id();
			$insert = " INSERT datosempresas 
					SET plaza='".$id."',nombre='".$_POST['nombre']."',rfc='".$_POST['rfc']."',idplaza='".$_POST['idplaza']."',idcertificado='".$_POST['idcertificado']."',
					usuario='".$_POST['usuario']."',pass='".$_POST['pass']."',timbra='".$_POST['timbra']."',logoencabezado='".$_POST['logoencabezado']."'".$campos."";
		$ejecutar = mysql_query($insert) or die(mysql_error());
	}
	
	if($_POST['timbra']==1)
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$id."' AND tipo=0 AND tipodocumento=1");
	else
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$id."' AND tipo=1 AND tipodocumento=1");
	if($row1=mysql_fetch_array($res1)){
		mysql_query("UPDATE foliosiniciales SET folio_inicial='".$_POST['folio_inicial']."',serie='".$_POST['serie_inicial']."' WHERE cve='".$row1['cve']."'");
	}
	else{
		if($_POST['timbra']==1)
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial']."',serie='".$_POST['serie_inicial']."',plaza='".$id."',tipo=0,tipodocumento=1");
		else
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial']."',serie='".$_POST['serie_inicial']."',plaza='".$id."',tipo=1,tipodocumento=1");
	}

	if($_POST['timbra']==1)
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$id."' AND tipo=0 AND tipodocumento=5");
	else
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$id."' AND tipo=1 AND tipodocumento=5");
	if($row1=mysql_fetch_array($res1)){
		mysql_query("UPDATE foliosiniciales SET folio_inicial='".$_POST['folio_inicial5']."',serie='".$_POST['serie_inicial5']."' WHERE cve='".$row1['cve']."'");
	}
	else{
		if($_POST['timbra']==1)
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial5']."',serie='".$_POST['serie_inicial5']."',plaza='".$id."',tipo=0,tipodocumento=5");
		else
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial5']."',serie='".$_POST['serie_inicial5']."',plaza='".$id."',tipo=1,tipodocumento=5");
	}

	if($_POST['timbra']==1)
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$id."' AND tipo=0 AND tipodocumento=6");
	else
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$id."' AND tipo=1 AND tipodocumento=6");
	if($row1=mysql_fetch_array($res1)){
		mysql_query("UPDATE foliosiniciales SET folio_inicial='".$_POST['folio_inicial6']."',serie='".$_POST['serie_inicial6']."' WHERE cve='".$row1['cve']."'");
	}
	else{
		if($_POST['timbra']==1)
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial6']."',serie='".$_POST['serie_inicial6']."',plaza='".$id."',tipo=0,tipodocumento=6");
		else
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial6']."',serie='".$_POST['serie_inicial6']."',plaza='".$id."',tipo=1,tipodocumento=6");
	}
	
	if($_POST['timbra']==1)
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$id."' AND tipo=0 AND tipodocumento=2");
	else
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$id."' AND tipo=1 AND tipodocumento=2");
	if($row1=mysql_fetch_array($res1)){
		mysql_query("UPDATE foliosiniciales SET folio_inicial='".$_POST['folio_inicial2']."',serie='".$_POST['serie_inicial2']."' WHERE cve='".$row1['cve']."'");
	}
	else{
		if($_POST['timbra']==1)
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial2']."',serie='".$_POST['serie_inicial2']."',plaza='".$id."',tipo=0,tipodocumento=2");
		else
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial2']."',serie='".$_POST['serie_inicial2']."',plaza='".$id."',tipo=1,tipodocumento=2");
	}
	
	/*if($_POST['timbra']==1)
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$id."' AND tipo=0 AND tipodocumento=3");
	else
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$id."' AND tipo=1 AND tipodocumento=3");
	if($row1=mysql_fetch_array($res1)){
		mysql_query("UPDATE foliosiniciales SET folio_inicial='".$_POST['folio_inicial3']."' WHERE cve='".$row1['cve']."'");
	}
	else{
		if($_POST['timbra']==1)
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial3']."',plaza='".$id."',tipo=0,tipodocumento=3");
		else
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial3']."',plaza='".$id."',tipo=1,tipodocumento=3");
	}
	
	if($_POST['timbra']==1)
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$id."' AND tipo=0 AND tipodocumento=4");
	else
		$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$id."' AND tipo=1 AND tipodocumento=4");
	if($row1=mysql_fetch_array($res1)){
		mysql_query("UPDATE foliosiniciales SET folio_inicial='".$_POST['folio_inicial4']."' WHERE cve='".$row1['cve']."'");
	}
	else{
		if($_POST['timbra']==1)
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial4']."',plaza='".$id."',tipo=0,tipodocumento=4");
		else
			mysql_query("INSERT foliosiniciales SET folio_inicial='".$_POST['folio_inicial4']."',plaza='".$id."',tipo=1,tipodocumento=4");
	}*/
	
	if($_POST['borrar_foto']=="S")
		unlink("../logos/logo".$id.".jpg");
	if(is_uploaded_file ($_FILES['foto']['tmp_name'])){
		$arch = $_FILES['foto']['tmp_name'];
		copy($arch,"../logos/logo".$id.".jpg");
		chmod("../logos/logo".$id.".jpg", 0777);
	}
	$_POST['cmd']=0;
	
}


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM plazas WHERE 1 ";
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		$rsplaza=mysql_query($select);
		$totalRegistros = mysql_num_rows($rsplaza);
		if($totalRegistros / $eRegistrosPagina > 1) 
		{
			$eTotalPaginas = $totalRegistros / $eRegistrosPagina;
			if(is_int($eTotalPaginas))
			{$eTotalPaginas--;}
			else
			{$eTotalPaginas = floor($eTotalPaginas);}
		}
		//$select .= " ORDER BY nombre LIMIT ".$primerRegistro.",".$eRegistrosPagina;
		$rsplaza=mysql_query($select);
		
		if(mysql_num_rows($rsplaza)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="11">'.mysql_num_rows($rsplaza).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th>';
			if($_POST['cveusuario']==1) echo '<th>Cve</th>';
			echo '<th>Nombre</th><th>Numero</th><th>Localidad</th><th>ID Plaza</th><th>ID Certificado</th><th>Tipo Plaza</th><th>Serie Factura</th><th>Serie Nota de Credito</th><th>Serie Factura Gobierno</th></tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($Plaza=mysql_fetch_array($rsplaza)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$Plaza['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Plaza['nombre'].'"></a></td>';
				if($_POST['cveusuario']==1) echo '<td align="center">'.utf8_encode($Plaza['cve']).' - '.$array_nosi[$Plaza['genera_devolucion']].'</td>';
				echo '<td>'.utf8_encode($Plaza['nombre']).'</td>';
				echo '<td>'.utf8_encode($Plaza['numero']).'</td>';
				$select=" SELECT * FROM datosempresas WHERE plaza='".$Plaza['cve']."' ";
				$res=mysql_query($select);
				$row=mysql_fetch_array($res);
				echo '<td>'.utf8_encode($array_localidad[$row['localidad_id']]).'</td>';
				echo '<td align="center">'.$row['idplaza'].'</td>';
				echo '<td align="center">'.$row['idcertificado'].'</td>';
				echo '<td>'.utf8_encode($array_tipo_plaza[$Plaza['tipo_plaza']]).'</td>';
				$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$Plaza['cve']."' AND tipo=0 AND tipodocumento=1");
				$row1=mysql_fetch_array($res1);
				echo '<td align="center">'.$row1['serie'].'</td>';
				$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$Plaza['cve']."' AND tipo=0 AND tipodocumento=2");
				$row1=mysql_fetch_array($res1);
				echo '<td align="center">'.$row1['serie'].'</td>';
				$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$Plaza['cve']."' AND tipo=0 AND tipodocumento=5");
				$row1=mysql_fetch_array($res1);
				echo '<td align="center">'.$row1['serie'].'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="11" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM plazas WHERE cve='".$_POST['reg']."' ";
		$rsplaza=mysql_query($select);
		$Plaza=mysql_fetch_array($rsplaza);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1 && $Plaza['baja']!=1)
				echo '<td><a href="#" onClick="atcr(\'plazas.php\',\'\',\'2\',\''.$Plaza['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="atcr(\'plazas.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		$select=" SELECT * FROM datosempresas WHERE plaza='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Plazas</td></tr>';
		echo '</table>';
		echo '<table width="100%"><tr><td>';
		echo '<table>';
		echo '<tr><th>Numero</th><td><input type="text" name="numero" id="numero" class="textField" size="30" value="'.$Plaza['numero'].'"></td></tr>';
		$res2=mysql_query("SELECT COUNT(cve) FROM cobro_engomado WHERE plaza='".$Plaza['cve']."'");
		$row2=mysql_fetch_array($res2);
		echo '<tr><th>Plaza Local</th><td><input type="hidden" name="local" id="local" value="'.intval($Plaza['local']).'"><input type="checkbox" id="local_chk" value="1" onClick="if(this.checked){ $(\'#local\').val(1);} else{ $(\'#local\').val(0);}"';
		if($Plaza['local']==1){ 
			echo ' checked';
			$style='';
		}
		if($row2[0]>0) echo ' disabled';
		echo '></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Nuevo Formato Factura</th><td><input type="checkbox" id="nuevo_formato" name="nuevo_formato" value="1"';
		if($Plaza['nuevo_formato']==1){ 
			echo ' checked';
		}
		echo '></td></tr>';

		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Bloquear Impresion</th><td><input type="checkbox" id="bloquear_impresion" name="bloquear_impresion" value="1"';
		if($Plaza['bloquear_impresion']==1){ 
			echo ' checked';
		}
		echo '></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Bloqueada SAT</th><td><input type="checkbox" id="bloqueada_sat" name="bloqueada_sat" value="1"';
		if($Plaza['bloqueada_sat']==1){ 
			echo ' checked';
		}
		echo '></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Validar Timbres</th><td><input type="checkbox" id="validar_timbres" name="validar_timbres" value="1"';
		if($Plaza['validar_timbres']==1){ 
			echo ' checked';
		}
		echo '></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Ver en Listado de Timbres</th><td><input type="checkbox" id="timbres_exis" name="timbres_exis" value="1"';
		if($Plaza['timbres_exis']==1){ 
			echo ' checked';
		}
		echo '></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>ID Cliente HoyFactura</th><td><input type="text" class="textField" id="cveclientefactura" name="cveclientefactura" value="'.$Plaza['cveclientefactura'].'" size="10"></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Validar Certificado Anterior</th><td><input type="checkbox" id="validar_certificado_anterior" name="validar_certificado_anterior" value="1"';
		if($Plaza['validar_certificado_anterior']==1){ 
			echo ' checked';
		}
		echo '></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Genera Devolucion</th><td><input type="checkbox" id="genera_devolucion" name="genera_devolucion" value="1"';
		if($Plaza['genera_devolucion']==1){ 
			echo ' checked';
		}
		echo '></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Genera Factura Mostrador</th><td><input type="checkbox" id="genera_factura_mostrador" name="genera_factura_mostrador" value="1"';
		if($Plaza['genera_factura_mostrador']==1){ 
			echo ' checked';
		}
		echo '></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Lista</th><td><input type="text" class="textField" id="lista" name="lista" value="'.$Plaza['lista'].'"></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Numero Intentos</th><td><input type="text" class="textField" id="num_intentos" name="num_intentos" value="'.$Plaza['num_intentos'].'"</td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Numero Intentos Pago Anticipados</th><td><input type="text" class="textField" id="num_intentosanticipados" name="num_intentosanticipados" value="'.$Plaza['num_intentosanticipados'].'"</td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Permitir intento por certificado diferente</th><td><input type="checkbox" id="intentoporcertificadodif" name="intentoporcertificadodif" value="1"';
		if($Plaza['intentoporcertificadodif']==1){ 
			echo ' checked';
		}
		echo '></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Entrega Simple</th><td><input type="checkbox" id="entrega_simple" name="entrega_simple" value="1"';
		if($Plaza['entrega_simple']==1){ 
			echo ' checked';
		}
		echo '></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Maneja Medio Pago</th><td><input type="checkbox" id="maneja_medio_pago" name="maneja_medio_pago" value="1"';
		if($Plaza['maneja_medio_pago']==1){ 
			echo ' checked';
		}
		echo '></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Importe Medio Pago</th><td><input type="text" id="monto_medio_pago" name="monto_medio_pago" value="'.$Plaza['monto_medio_pago'].'" size="10" class="textField">';
		echo '</td></tr>';
		echo '<tr><th>Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$Plaza['nombre'].'"></td></tr>';
		echo '<tr';
		if($Plaza['baja']==1) echo ' style="display:none;"';
		echo '><th>Estatus</th><td><select name="estatus" id="estatus"><option value="A">Activa</option>';
		echo '<option value="I"';
		if($Plaza['estatus']=='I') echo ' selected';
		echo '>Inactivo</option>';
		echo '</select></td></tr>';
		echo '<tr><th>Tipo Plaza</th><td><select name="tipo_plaza" id="tipo_plaza"><option value="0">Seleccione</option>';
		foreach($array_tipo_plaza as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$Plaza['tipo_plaza']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>RFC</th><td><input type="text" name="rfc" id="rfc" class="textField" size="15" value="'.$row['rfc'].'"></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1 && $_SESSION['TipoUsuario']!=1) echo ' style="display:none;"';
		echo '><th>Repetir RFC</th><td><input type="hidden" name="camposi[repetir_rfc]" id="repetir_rfc" class="textField" value="'.$row['repetir_rfc'].'">
		<input type="checkbox" onClick="if(this.checked) document.getElementById(\'repetir_rfc\').value=1; else  document.getElementById(\'repetir_rfc\').value=0;"';
		if($row['repetir_rfc']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr><th>Email</th><td><input type="text" class="textField" name="camposi[email]" id="email" value="'.$row['email'].'" size="80"></td></tr>';
		echo '<tr><th>Regimen SAT</th><td><select name="regimensat" id="regimensat"><option value="">Seleccione</option>';
		foreach($array_regimensat as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$Plaza['regimensat']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>Regimen</th><td><input type="text" class="textField" name="camposi[regimen]" id="regimen" value="'.$row['regimen'].'" size="50"></td></tr>';
		echo '<tr><th>Registro Patronal</th><td><input type="text" class="textField" name="camposi[registro_patronal]" id="registro_patronal" value="'.$row['registro_patronal'].'" size="50"></td></tr>';
		echo '<tr><th>Calle</th><td><input type="text" class="textField" name="camposi[calle]" id="calle" value="'.$row['calle'].'" size="30"></td></tr>';
		echo '<tr><th>Numero Exterior</th><td><input type="text" class="textField" name="camposi[numexterior]" id="numexterior" value="'.$row['numexterior'].'" size="10"></td></tr>';
		echo '<tr><th>Numero Interior</th><td><input type="text" class="textField" name="camposi[numinterior]" id="numinterior" value="'.$row['numinterior'].'" size="10"></td></tr>';
		echo '<tr><th>Colonia</th><td><input type="text" class="textField" name="camposi[colonia]" id="colonia" value="'.$row['colonia'].'" size="30"></td></tr>';
		//echo '<tr><th>Localidad</th><td><input type="text" class="textField" name="camposi[localidad]" id="localidad" value="'.$row['localidad'].'" size="50"></td></tr>';
		echo '<tr><th>Localidad</th><td><select name="camposi[localidad_id]" id="localidad_id"><option value="0">Seleccione</option>';
		$res1=mysql_query("SELECT cve,nombre FROM areas ORDER BY nombre");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['cve'].'"';
			if($row1['cve']==$row['localidad_id']) echo ' selected';
			echo '>'.$row1['nombre'].'</option>';
		}
		echo '</td></tr>';
		echo '<tr><th>Municipio</th><td><input type="text" class="textField" name="camposi[municipio]" id="municipio" value="'.$row['municipio'].'" size="50"></td></tr>';
		echo '<tr><th>Estado</th><td><input type="text" class="textField" name="camposi[estado]" id="estado" value="'.$row['estado'].'" size="50"></td></tr>';
		echo '<tr><th>Codigo Postal</th><td><input type="text" class="textField" name="camposi[codigopostal]" id="codigopostal" value="'.$row['codigopostal'].'" size="50"></td></tr>';
		echo '<tr><th>Numero de Lineas</th><td><input type="text" class="textField" name="camposi[numero_lineas]" id="numero_lineas" value="'.$row['numero_lineas'].'" size="10"></td></tr>';
		echo '<tr><th>Cuenta Pago</th><td><input type="text" class="textField" name="camposi[cuenta_pago]" id="cuenta_pago" value="'.$row['cuenta_pago'].'" size="10"></td></tr>';
		echo '<tr><td colspan="2" class="tableEnc">Datos Nomina</td></tr>';
		echo '<tr><th>Registro Patronal</th><td><input type="text" name="camposi[registro_patronal]" id="registro_patronal" class="textField" size="30" value="'.$row['registro_patronal'].'"></td></tr>';
		echo '<tr><th>ID Plaza</th><td><input type="text" name="camposi[idplazanomina]" id="idplazanomina" class="textField" size="10" value="'.$row['idplazanomina'].'"></td></tr>';
		echo '<tr><th>ID Certificado</th><td><input type="text" name="camposi[idcertificadonomina]" id="idcertificadonomina" class="textField" size="10" value="'.$row['idcertificadonomina'].'"></td></tr>';
		echo '<tr><th>Usuario</th><td><input type="text" name="camposi[usuarionomina]" id="usuarionomina" class="textField" size="20" value="'.$row['usuarionomina'].'"></td></tr>';
		echo '<tr><th>Password</th><td><input type="text" name="camposi[passnomina]" id="passnomina" class="textField" size="20" value="'.$row['passnomina'].'"></td></tr>';
		echo '<tr><td colspan="2" class="tableEnc">Datos Facturacion</td></tr>';
		echo '<tr><th>Descripcion</th><td><input type="text" class="textField" name="camposi[descripcionfactura]" id="descripcionfactura" value="'.$row['descripcionfactura'].'" size="100"></td></tr>';
		echo '<tr><th>Direccion Fiscal</th><td><textarea cols="50" rows="5" class="textField" name="camposi[direccionfiscal]" id="direccionfiscal">'.$row['direccionfiscal'].'</textarea></td></tr>';
		echo '<tr><th>Lugar de Expedicion</th><td><textarea cols="50" rows="5" class="textField" name="camposi[lugarexpedicion]" id="lugarexpedicion">'.$row['lugarexpedicion'].'</textarea></td></tr>';
		echo '<tr class="rsucursal"'.$style.'><th>RFC Sucursal</th><td><input type="text" class="textField" name="camposi[rfc_sucursal]" id="rfc_sucursal" value="'.$row['rfc_sucursal'].'" size="50"></td></tr>';
		echo '<tr><th>ID Plaza</th><td><input type="text" name="idplaza" id="idplaza" class="textField" size="10" value="'.$row['idplaza'].'"></td></tr>';
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
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$_POST['reg']."' AND tipo=0 AND tipodocumento=1");
		else
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$_POST['reg']."' AND tipo=1 AND tipodocumento=1");
		$row1=mysql_fetch_array($res1);
		echo '<tr><th>Serie Factura</th><td><input type="text" name="serie_inicial" id="serie_inicial" class="textField" size="10" value="'.$row1['serie'].'"></td></tr>';
		echo '<tr><th>Folio Inicial Factura</th><td><input type="text" name="folio_inicial" id="folio_inicial" class="textField" size="10" value="'.$row1['folio_inicial'].'"></td></tr>';
		if($row['timbra']==1)
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$_POST['reg']."' AND tipo=0 AND tipodocumento=5");
		else
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$_POST['reg']."' AND tipo=1 AND tipodocumento=5");
		$row1=mysql_fetch_array($res1);
		echo '<tr><th>Serie Factura Gobierno</th><td><input type="text" name="serie_inicial5" id="serie_inicial5" class="textField" size="10" value="'.$row1['serie'].'"></td></tr>';
		echo '<tr><th>Folio Inicial Factura Gobierno</th><td><input type="text" name="folio_inicial5" id="folio_inicial5" class="textField" size="10" value="'.$row1['folio_inicial'].'"></td></tr>';
		if($row['timbra']==1)
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$_POST['reg']."' AND tipo=0 AND tipodocumento=6");
		else
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$_POST['reg']."' AND tipo=1 AND tipodocumento=6");
		$row1=mysql_fetch_array($res1);
		echo '<tr><th>Serie Factura Atrasado</th><td><input type="text" name="serie_inicial6" id="serie_inicial6" class="textField" size="10" value="'.$row1['serie'].'"></td></tr>';
		echo '<tr><th>Folio Inicial Factura Atrasado</th><td><input type="text" name="folio_inicial6" id="folio_inicial6" class="textField" size="10" value="'.$row1['folio_inicial'].'"></td></tr>';
		if($row['timbra']==1)
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$_POST['reg']."' AND tipo=0 AND tipodocumento=2");
		else
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$_POST['reg']."' AND tipo=1 AND tipodocumento=2");
		$row1=mysql_fetch_array($res1);
		echo '<tr><th>Serie Nota de Credito</th><td><input type="text" name="serie_inicial2" id="serie_inicial2" class="textField" size="10" value="'.$row1['serie'].'"></td></tr>';
		echo '<tr><th>Folio Inicial Nota de Credito</th><td><input type="text" name="folio_inicial2" id="folio_inicial2" class="textField" size="10" value="'.$row1['folio_inicial'].'"></td></tr>';
		/*if($row['timbra']==1)
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$_POST['reg']."' AND tipo=0 AND tipodocumento=3");
		else
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$_POST['reg']."' AND tipo=1 AND tipodocumento=3");
		$row1=mysql_fetch_array($res1);
		echo '<tr><th>Folio Inicial Nota de Cargo</th><td><input type="text" name="folio_inicial3" id="folio_inicial3" class="textField" size="10" value="'.$row1['folio_inicial'].'"></td></tr>';
		if($row['timbra']==1)
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$_POST['reg']."' AND tipo=0 AND tipodocumento=4");
		else
			$res1=mysql_query("SELECT * FROM foliosiniciales WHERE plaza='".$_POST['reg']."' AND tipo=1 AND tipodocumento=4");
		$row1=mysql_fetch_array($res1);
		echo '<tr><th>Folio Inicial Remision</th><td><input type="text" name="folio_inicial4" id="folio_inicial4" class="textField" size="10" value="'.$row1['folio_inicial'].'"></td></tr>';*/
		echo '<tr><th>Porcentaje de Iva Retenido</th><td><input type="text" class="textField" name="camposi[por_iva_retenido]" id="por_iva_retenido" value="'.$row['por_iva_retenido'].'" size="5"></td></tr>';
		echo '<tr><th>Modificar porcentaje de iva retenido</th><td><input type="hidden" name="camposi[mod_iva_retenido]" id="mod_iva_retenido" value="'.intval($row['mod_iva_retenido']).'"><input type="checkbox" id="mod_iva_retenido_chk" value="1" onClick="if(this.checked) $(\'#mod_iva_retenido\').val(1); else $(\'#mod_iva_retenido\').val(0);"';
		if($row['mod_iva_retenido']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr><th>Porcentaje de ISR Retenido</th><td><input type="text" class="textField" name="camposi[por_isr_retenido]" id="por_isr_retenido" value="'.$row['por_isr_retenido'].'" size="5"></td></tr>';
		echo '<tr><th>Modificar porcentaje de isr retenido</th><td><input type="hidden" name="camposi[mod_isr_retenido]" id="mod_isr_retenido" value="'.intval($row['mod_isr_retenido']).'"><input type="checkbox" id="mod_isr_retenido_chk" value="1" onClick="if(this.checked) $(\'#mod_isr_retenido\').val(1); else $(\'#mod_isr_retenido\').val(0);"';
		if($row['mod_isr_retenido']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr><td colspan="2" class="tableEnc">Datos Sucursal</td></tr>';
		echo '<tr><th>Sucursal</th><td><input type="hidden" name="camposi[check_sucursal]" id="check_sucursal" value="'.intval($row['check_sucursal']).'"><input type="checkbox" id="check_sucursal_chk" value="1" onClick="if(this.checked){ $(\'#check_sucursal\').val(1); $(\'.rsucursal\').show();} else{ $(\'#check_sucursal\').val(0); $(\'.rsucursal\').hide();}"';
		$style=' style="display:none;"';
		if($row['check_sucursal']==1){ 
			echo ' checked';
			$style='';
		}
		echo '></td></tr>';
		echo '<tr class="rsucursal"'.$style.'><th>Nombre Sucursal</th><td><input type="text" class="textField" name="camposi[nombre_sucursal]" id="nombre_sucursal" value="'.$row['nombre_sucursal'].'" size="50"></td></tr>';
		echo '<tr class="rsucursal"'.$style.'><th>RFC Sucursal</th><td><input type="text" class="textField" name="camposi[rfc_sucursal]" id="rfc_sucursal" value="'.$row['rfc_sucursal'].'" size="50"></td></tr>';
		echo '<tr class="rsucursal"'.$style.'><th>Calle Sucursal</th><td><input type="text" class="textField" name="camposi[calle_sucursal]" id="calle_sucursal" value="'.$row['calle_sucursal'].'" size="50"></td></tr>';
		echo '<tr class="rsucursal"'.$style.'><th>Numero Sucursal</th><td><input type="text" class="textField" name="camposi[numero_sucursal]" id="numero_sucursal" value="'.$row['numero_sucursal'].'" size="50"></td></tr>';
		echo '<tr class="rsucursal"'.$style.'><th>Colonia Sucursal</th><td><input type="text" class="textField" name="camposi[colonia_sucursal]" id="colonia_sucursal" value="'.$row['colonia_sucursal'].'" size="50"></td></tr>';
		echo '<tr class="rsucursal"'.$style.'><th>Localidad Sucursal</th><td><input type="text" class="textField" name="camposi[localidad_sucursal]" id="localidad_sucursal" value="'.$row['localidad_sucursal'].'" size="50"></td></tr>';
		echo '<tr class="rsucursal"'.$style.'><th>Municipio Sucursal</th><td><input type="text" class="textField" name="camposi[municipio_sucursal]" id="municipio_sucursal" value="'.$row['municipio_sucursal'].'" size="50"></td></tr>';
		echo '<tr class="rsucursal"'.$style.'><th>Estado Sucursal</th><td><input type="text" class="textField" name="camposi[estado_sucursal]" id="estado_sucursal" value="'.$row['estado_sucursal'].'" size="50"></td></tr>';
		echo '<tr class="rsucursal"'.$style.'><th>CP Sucursal</th><td><input type="text" class="textField" name="camposi[cp_sucursal]" id="cp_sucursal" value="'.$row['cp_sucursal'].'" size="50"></td></tr>';
		echo '<tr';
		if($_POST['cveusuario']!=1) echo ' style="display:none;"';
		echo '><th>Mensaje Inicio</th><td><textarea class="textField" name="camposi[mensajeinicio]" id="mensajeinicio" rows="5" cols="50">'.$row['mensajeinicio'].'</textarea></td></tr>';
		echo '</table>';
		echo '</td><td valign="top">';
		echo '<table align="right"><tr><td colspan="2" align="center"><img width="200" height="250" src="../logos/logo'.$_POST['reg'].'.jpg?'.date('h:i:s').'" border="1"></td></tr>';
		echo '<tr><th>Nuevo Logo</th><td><input type="file" name="foto" id="foto"></td></tr>';
		echo '<tr><th>Borrar Logo</th><td><input type="checkbox" name="borrar_foto" id="borrar_foto" value="S"></td></tr></table>';
		echo '</td></tr></table>';
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td><td>&nbsp;</td><td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'plazas.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
				</tr>';
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
			objeto.open("POST","plazas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value);
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

