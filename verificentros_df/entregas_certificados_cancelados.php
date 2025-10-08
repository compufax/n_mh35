<?php 

include ("main.php"); 
 function firstday() {
      $month = date('m');
      $year = date('Y');
      return date('Y-m-d', mktime(0,0,0, $month, 1, $year));
  }
$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados  ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio'];
}
	$res = mysql_query("SELECT * FROM anios_certificados  ORDER BY nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
}

$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$res = mysql_query("SELECT * FROM anios_certificados ORDER BY cve DESC");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
}

$res = mysql_query("SELECT * FROM tipo_combustible ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_combustible[$row['cve']]=$row['nombre'];
}

$array_tipo_pago = array();
$res = mysql_query("SELECT * FROM tipos_pago WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}
$array_tipo_venta = array();
$res = mysql_query("SELECT * FROM tipo_venta WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_venta[$row['cve']]=$row['nombre'];
}

$array_motivos_intento = array();
$res = mysql_query("SELECT * FROM motivos_intento WHERE localidad IN (0,2) ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_motivos_intento[$row['cve']]=$row['nombre'];
}

$array_depositantes = array();
$res = mysql_query("SELECT * FROM depositantes WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_depositantes[$row['cve']]=$row['nombre'];
}

$res = mysql_query("SELECT * FROM tecnicos WHERE 1");
while($row=mysql_fetch_array($res)){
	$array_personal[$row['cve']]=$row['nombre'];
}

$res = mysql_query("SELECT * FROM cat_lineas WHERE 1");
while($row=mysql_fetch_array($res)){
	$array_lineas[$row['cve']]=$row['nombre'];
}


$array_estatus = array('A'=>'Activo','C'=>'Cancelado');

/*$array_cuentas = array();
$res = mysql_query("SELECT * FROM cuentas_contables ORDER BY cuenta,nombre");
while($row = mysql_fetch_array($res)){
	$array_cuentas[$row['cve']]=$row['cuenta'].' '.$row['nombre'];
}*/

if($_POST['ajax']==1) {
		
		
		
		//Listado de plazas
		$select= " SELECT CONCAT(c.numero,' ',c.nombre) as nomplaza,a.*,b.cve as certificado, b.certificado as holograma,b.engomado as engomado_entrega,d.folio as foliofactura,a.tipo_venta
		FROM cobro_engomado a 
		INNER JOIN plazas c ON c.cve = a.plaza
		LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
	/**/inner join certificados_cancelados e on b.plaza = e.plaza and b.placa = e.placa	
		LEFT JOIN facturas d ON d.plaza = a.plaza AND d.cve = a.factura
		WHERE a.placa='".$_POST['nom']."' and a.plaza='".$_POST['plazausuario']."' and a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
//		if ($_POST['anio']!="all") { $select.=" AND a.anio='".$_POST['anio']."' "; }
		$select.="  ORDER BY a.fecha DESC, a.hora DESC";

		$res=mysql_query($select) or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);
		
		echo '<h2>Ventas</h2>';
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Plaza</th><th>Ticket</th><th>Fecha</th></th><th>Placa</th>
			<th>Tiene Multa</th><th>Tipo de Certificado</th><th>Monto</th><th>A&ntilde;o Certificacion</th><th>Tipo de Pago</th><th>Tipo de Venta</th><th>Factura</th><th>Entrega Certificado</th><th>Holograma Entregado</th><th>Usuario</th>';
			if($_POST['cveusuario']==1) echo '<th>Clave Facturacion</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
					$row['monto']=0;
				}
				elseif($row['estatus']=='D'){
					echo 'Devuleto';
				}
				else{
					echo '&nbsp;';
				}	
				echo '</td>';
				echo '<td>'.$row['nomplaza'].'</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				if($row['estatus']=='C'){
					echo '<td align="center">'.htmlentities($row['placa']).'</td>';
				}
				elseif($row['certificado']>0 && $row['engomado']!=$row['engomado_entrega']){
					echo '<td align="center"><font color="RED">'.htmlentities($row['placa']).'</font></td>';
				}
				else{
					$res1 = mysql_query("SELECT cve FROM certificados WHERE placa='".$row['placa']."' AND fecha>='".$row['fecha']."' AND DATE_ADD(fecha,INTERVAL 30 DAY)>='".$row['fecha']."'");
					if(mysql_num_rows($res1)==0)
						echo '<td align="center"><font color="GREEN">'.htmlentities($row['placa']).'</font></td>';
					else
						echo '<td align="center">'.htmlentities($row['placa']).'</td>';
				}
				echo '<td align="center">'.htmlentities($array_nosi[$row['multa']]).'<br>'.$row['folio_multa'].'</td>';
				echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
				echo '<td align="center">'.number_format($row['monto'],2).'</td>';
				echo '<td align="center">'.htmlentities($array_anios[$row['anio']]).'</td>';
				echo '<td align="center">'.htmlentities($array_tipo_pago[$row['tipo_pago']]).'</td>';
			echo '<td align="center">'.htmlentities($array_tipo_venta[$row['tipo_venta']]).'</td>';
				//echo '<td align="center">'.htmlentities($array_tipo_combustible[$row['tipo_combustible']]).'</td>';
				echo '<td align="center">'; echo ($row['factura']==0)?'&nbsp;':$row['foliofactura']; echo '</td>';
				echo '<td align="center">'.$row['certificado'].'</td>';
				echo '<td align="center">'.$row['holograma'].'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				if($_POST['cveusuario']==1){
					$res2=mysql_query("SELECT * FROM claves_facturacion WHERE plaza='".$row['plaza']."' AND ticket='".$row['cve']."'");
					$row2=mysql_fetch_array($res2);
					echo '<td>'.$row2['cve'].'</td>';
				}
				echo '</tr>';
				$t+=$row['monto'];
			}
			echo '	
				<tr>
				<td colspan="7" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</td>
				<td colspan="8" bgcolor="#E9F2F8">&nbsp;</td>
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


		echo '<h2>Entregas</h2>';
		//Listado de plazas
		$select= " SELECT a.*,CONCAT(c.numero,' ',c.nombre) as nomplaza FROM certificados a 
					INNER JOIN plazas c ON c.cve = a.plaza
					inner join certificados_cancelados e on a.plaza = e.plaza and a.placa = e.placa
					 WHERE a.placa='".$_POST['nom']."' and a.plaza='".$_POST['plazausuario']."' and a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'"; 
		
		$select.=" group by a.cve ORDER BY a.fecha DESC, a.hora DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		$array_totales_engomados=array();
		$rechazados=0;
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Plaza</th><th>Folio</th><th>Fecha</th><th>Ticket</th><th>Placa</th><th>Tipo de Venta</th>
			<th>Tipo de Certificado</th><th>Tecnico</th><th>Holograma</th><th>Entregado</th><th>Usuario</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				$res1=mysql_query("SELECT engomado,factura,tipo_combustible,tipo_venta FROM cobro_engomado WHERE plaza = '".$row['plaza']."' AND cve='".$row['ticket']."'");
				if ($_POST['anio']!="") { $select.=" AND anio='".$_POST['anio']."' "; }
				$row1=mysql_fetch_array($res1);
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
					$row['certificado']='';
				}
				else{
					echo '&nbsp;';
				}	
				echo '</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($row['nomplaza'])).'</td>';
				if($row1['engomado']!=$row['engomado'])
					echo '<td align="center"><font color="RED">'.htmlentities($row['cve']).'</font></td>';
				else
					echo '<td align="center">'.htmlentities($row['cve']).'</td>';

				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="center">'.htmlentities($row['ticket']).'</td>';
				echo '<td align="center">'.htmlentities($row['placa']).'</td>';
				echo '<td align="center">'.htmlentities($array_tipo_venta[$row1['tipo_venta']]).'</td>';
//				echo '<td align="center">'.htmlentities($array_tipo_combustible[$row1['tipo_combustible']]).'</td>';
				echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_personal[$row['tecnico']])).'</td>';
				echo '<td align="center">'.htmlentities($row['certificado']).'</td>';
				echo '<td align="center">'.htmlentities($array_nosi[$row['entregado']]).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
				$array_totales_engomados[$row['engomado']][$row1['tipo_combustible']]++;
				if($row['engomado']==9) $rechazados++;
			}
			echo '	
				<tr>
				<td colspan="12" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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

		echo '<h2>Cancelacion de Certificados</h2>';
		//Listado de plazas
		$select= " SELECT a.*,CONCAT(c.numero,' ',c.nombre) as nomplaza FROM certificados_cancelados a 
					INNER JOIN plazas c ON c.cve = a.plaza
					inner join certificados e on a.plaza = e.plaza and a.placa = e.placa
					 WHERE a.placa='".$_POST['nom']."' and a.plaza='".$_POST['plazausuario']."' and a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'"; 
		
		$select.=" group by a.cve ORDER BY a.fecha DESC, a.hora DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		$array_totales_engomados=array();
		$rechazados=0;
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Motivo</th><th>Tipo de Certificado</th><th>A&ntilde;o</th><th>Holograma</th><th>Placa</th><th>Tecnico</th><th>Linea</th><th>Observaciones</th><th>Usuario</th>';
		/*	if($_POST['cveusuario']==1){
				echo '<th>Auditoria</th>';
			}*/
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center">&nbsp;';
				if($row['estatus']=='C'){
					$row['certificado']='';
					echo 'Cancelado<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
				}
				
				echo '&nbsp;</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_motivos[$row['motivo']])).'</td>';
				
				echo '<td align="left">'.htmlentities(utf8_encode($array_engomado[$row['engomado']])).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($array_anios[$row['anio']])).'</td>';
				echo '<td align="center">'.htmlentities($row['certificado']).'</td>';
				echo '<td align="center">'.htmlentities($row['placa']).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($array_personal[$row['tecnico']])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($array_lineas[$row['linea']])).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($row['obs'])).'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				/*if($_POST['cveusuario']==1){
					echo '<td align="center">';
					if($row['estatus']=='C'){
						echo '&nbsp;';
					}
					else{
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'cancelar_certificados.php\',\'\',\'11\','.$row['cve'].')"><img src="images/finalizar.gif" border="0" title="Auditar '.$row['cve'].'"></a>';
					}
					echo '</td>';
				}*/
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="15" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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

/*if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE motivos 
						SET nombre='".$_POST['nombre']."',cuenta='".$_POST['cuenta']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);			
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO motivos 
						(nombre,cuenta)
						VALUES 
						('".$_POST['nombre']."','".$_POST['cuenta']."')";
			$ejecutar = mysql_query($insert);
	}
	$_POST['cmd']=0;
}*/

/*** EDICION  **************************************************/

/*	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM motivos WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'entregas_certificados_cancelados.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'entregas_certificados_cancelados.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Motivos</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"></td></tr>';
		echo '<tr><th align="left">Cuenta</th><td><select name="cuenta" id="cuenta"><option value="0">Seleccione</option>';
		foreach($array_cuentas as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['cuenta']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		
	}*/

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<!--<td><a href="#" onClick="atcr(\'entregas_certificados_cancelados.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>-->
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="30" class="textField" value=""></td></tr>';
			echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.firstday().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
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
			objeto.open("POST","entregas_certificados_cancelados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	';	
	
	
	echo '
	
	</Script>
';

?>

