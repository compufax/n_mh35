<?php 
include ("main.php"); 

$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$res=mysql_query("SELECT local, validar_certificado FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];
$ValidarCertificados = $row[1];

$array_engomado = array();
$array_engomadoprecio = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio_compra'];
}

$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$res = mysql_query("SELECT * FROM anios_certificados  ORDER BY nombre DESC LIMIT 2");
while($row=mysql_fetch_array($res)){
	$array_anios[$row['cve']]=$row['nombre'];
}


$array_estatus = array('A'=>'Activo','C'=>'Cancelado','E'=>'Confirmado');
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {

	if($_POST['folio']>0){
		$res=mysql_query("SELECT * FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['folio']."'");
		if($row=mysql_fetch_array($res)){
			$_POST['folio_ini'] = $row['folioini'];
			$_POST['folio_fin'] = $row['foliofin'];
			$_POST['engomado'] = $row['engomado'];
			$_POST['anio'] = $row['anio'];
		}
		else{
			echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontro el folio de compra</font></td>
			</tr>	  
			</table>';
			exit();
		}
	}
	$cantidad = $_POST['folio_fin']-$_POST['folio_ini']+1;
	$res=mysql_query("SELECT * FROM certificados 
		WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND engomado='".$_POST['engomado']."' AND anio='".$_POST['anio']."' 
		AND CAST(certificado AS UNSIGNED) BETWEEN '".$_POST['folio_ini']."' AND '".$_POST['folio_fin']."'");
	$array_certificados_encontrados = array();
	while($row=mysql_fetch_array($res)){
		$array_certificados_encontrados[]=intval($row['certificado']);
	}
	$res=mysql_query("SELECT * FROM certificados_cancelados 
		WHERE plaza='".$_POST['plazausuario']."' AND estatus!='C' AND engomado='".$_POST['engomado']."' AND anio='".$_POST['anio']."' 
		AND CAST(certificado AS UNSIGNED) BETWEEN '".$_POST['folio_ini']."' AND '".$_POST['folio_fin']."'");
	while($row=mysql_fetch_array($res)){
		$array_certificados_encontrados[]=intval($row['certificado']);
	}
	if($cantidad==1){
		if($cantidad>count($array_certificados_encontrados)){
			echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B">Holograma no entregado</font></td>
			</tr>	  
			</table>';
		}
		else{
			echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B">Holograma entregado</font></td>
			</tr>	  
			</table>';
		}
	}
	elseif($cantidad<=count($array_certificados_encontrados)){
		echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B">Hologramas entregados</font></td>
			</tr>	  
			</table>';
	}
	else{
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th align="left">Holograma</th>';
		echo '</tr>';
		$x=0;
		for($i=$_POST['folio_ini'];$i<=$_POST['folio_fin'];$i++){
			if(!in_array($i, $array_certificados_encontrados)){
				rowb();
				echo '<td>'.$i.'</td>';
				echo '</tr>';
				$x++;
			}
		}
		echo '<tr bgcolor="#E9F2F8"><th align="left">'.$x.' Registro(s)</th>';
		echo '</tr>';
	}
	exit();	
}	


top($_SESSION);



/*** ACTUALIZAR REGISTRO  **************************************************/



/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	echo '
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Folio Compra</td><td><input type="text" name="folio" id="folio" class="textField" size="12" value="" ></td></tr>';
	echo '<tr><td>A&ntilde;o</td><td><select name="anio" id="anio"><option value="">Todos</option>';
	foreach($array_anios as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Tipo de Certificado</td><td><select name="engomado" id="engomado"><option value="">Todos</option>';
	foreach($array_engomado as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Folio Inicial</td><td><input type="text" name="folio_ini" id="folio_ini" class="textField" size="12" value="" ></td></tr>';
	echo '<tr><td>Folio Final</td><td><input type="text" name="folio_fin" id="folio_fin" class="textField" size="12" value="" ></td></tr>';

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
			objeto.open("POST","certificados_sin_entregar.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&folio="+document.getElementById("folio").value+"&anio="+document.getElementById("anio").value+"&folio_ini="+document.getElementById("folio_ini").value+"&folio_fin="+document.getElementById("folio_fin").value+"&engomado="+document.getElementById("engomado").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

		
	
	</Script>
	';

	
}
	
bottom();

?>

