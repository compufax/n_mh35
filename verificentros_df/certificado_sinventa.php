<?php 

include ("main.php"); 
$array_tipo = array(1=>"Pago Anticipado", 2=>"Credito");
$res = mysql_query("SELECT * FROM cat_plantillas WHERE 1 order by nombre DESC");
while($row=mysql_fetch_array($res)){
	$array_plantilla[$row['cve']]=$row['nombre']; 
	}
$rsDepto=mysql_query("SELECT * FROM tipo_plaza");
while($Depto=mysql_fetch_array($rsDepto)){
	$array_tipo_plaza[$Depto['cve']]=$Depto['nombre'];
}
$rsUsuario=mysql_query("SELECT * FROM plazas where estatus!='I' ORDER BY numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}
$sel="SELECT * FROM engomados WHERE plazas like '%|".$_POST['plazausuario']."|%' AND entrega=1 ORDER BY nombre";
//echo''.$sel.'';
$res = mysql_query($sel);
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
	$array_engomadoprecio[$row['cve']]=$row['precio'];
}

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT c.nombre as engomado, a.certificado, d.numero as NumeroCentro 
		FROM certificados a inner join engomados c on c.cve=a.engomado inner join plazas d on d.cve=a.plaza 
		left join (select a.plaza, a.engomado, b.folio from compra_certificados a 
		inner join compra_certificados_detalle b on a.plaza = b.plaza and a.cve=b.cvecompra 
		where a.estatus!='C') b on a.plaza = b.plaza and a.engomado = b.engomado and a.certificado=b.folio where a.plaza={$_POST['plazausuario']} AND isnull(b.folio)";
//		if($_POST['plaza']!="all") $select .= " AND plaza='".$_POST['plaza']."'";
	//	if($_POST['plantilla']!="all") $select .= " AND plantilla='".$_POST['plantilla']."'";
		if ($_POST['engomado']!="") { $select.=" AND a.engomado = '".$_POST['engomado']."' "; }
//		if($_POST['estatus']!="all") $select .= " AND estatus='".$_POST['estatus']."'";
//		$select.=" ORDER BY nombre";
//		echo''.$select.'';
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		$nivelUsuario = nivelUsuario();
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Engomado</th><th>Certifiado</th><th>Centro</th>';
			echo '</tr>';
			while($row=mysql_fetch_array($res)) {
				rowb();

				
				echo '<td>'.utf8_encode($row['engomado']).'</td>';
				echo '<td align="center">'.utf8_encode($row['certificado']).'</td>';
				echo '<td align="center">'.utf8_encode(utf8_encode($row['NumeroCentro'])).'</td>';
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


top($_SESSION);

/*** ACTUALIZAR REGISTRO  **************************************************/
/*
if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			$select=" SELECT * FROM plantilla WHERE cve='".$_POST['reg']."' ";
		$rssuario=mysql_query($select);
		$Usuario=mysql_fetch_array($rssuario);
		if($Usuario['plantilla']!=$_POST['plantilla']){
			mysql_query("INSERT plantilla_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Plantilla',nuevo='".$_POST['plantilla']."',anterior='".$Usuario['plantilla']."',arreglo='plantilla',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['tipo_plaza']!=$_POST['tipo_plaza']){
			mysql_query("INSERT plantilla_historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Tipo plaza',nuevo='".$_POST['tipo_plaza']."',anterior='".$Usuario['tipo_plaza']."',arreglo='plazas',usuario='".$_POST['cveusuario']."'");
		}
			//Actualizar el Registro
			$update = " UPDATE plantilla 
						SET tipo_plaza='".$_POST['tipo_plaza']."',plantilla='".$_POST['plantilla']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);			
	} else {
			//Insertar el Registro
			$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM plantilla WHERE plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
			$insert = " INSERT plantilla 
						SET folio='".$Folio[0]."',fecha='".fechaLocal()."',hora='".horaLocal()."',plaza='".$_POST['plaza']."',
						nombre='".$_POST['nombre']."',usuario='".$_POST['usuario']."',tipo_plaza='".$_POST['tipo_plaza']."',plantilla='".$_POST['plantilla']."',
						usu='".$_POST['cveusuario']."'";
			$ejecutar = mysql_query($insert);
	}
	$_POST['cmd']=0;
}
*/
/*** EDICION  **************************************************/
/*
	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM plantilla WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="if(document.forma.plaza.value==\'0\'){ alert(\'Necesita seleccionar la plaza\');}else{ atcr(\'certificado_sinventa.php\',\'\',\'2\',\''.$row['cve'].'\');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'certificado_sinventa.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Plantilla</td></tr>';
		echo '</table>';
		
		echo '<table>';
		if($_POST['plazausuario']==0 && $row['plaza']==0)
		{
			echo '<tr><th align="left">Plaza</th><td><select name="plaza" id="plaza"><option value="0">Seleccione</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td></tr>';
		}
		elseif($_POST['plazausuario']==0 && $row['plaza']>0){
			echo '<tr><th align="left">Plaza</th><td><b>'.$array_plaza[$row['plaza']].'</b><input type="hidden" name="plaza" id="plaza" value="'.$row['plaza'].'"></td></tr>';
		}
		else{
			echo '<tr style="display:none;"><th align="left">Plaza</th><td><b>'.$array_plaza[$_POST['plazausuario']].'</b><input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'"></td></tr>';
		}
		
		if($_POST['reg']==0 || $_POST['cveusuario']==1)
			echo '<tr><th align="left">Usuario</th><td><input type="text" name="usuario" id="usuario" class="textField" size="100" value="'.$row['usuario'].'"><small></small></td></tr>';
		else
			echo '<tr><th align="left">Usuario</th><td><input type="text" name="usuario" id="usuario" class="readOnly" size="100" value="'.$row['usuario'].'" readOnly><small></small></td></tr>';
		if($_POST['reg']==0 || $_POST['cveusuario']==1)
			echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"><small>Comenzar con el nombre</small></td></tr>';
		else
			echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="readOnly" size="100" value="'.$row['nombre'].'" readOnly><small>Comenzar con el nombre</small></td></tr>';

		echo '<tr';
//		if(nivelUsuario() < 3) echo ' style="display:none;"';
		echo '><th align="left">Tipo Plaza</th><td><select name="tipo_plaza" id="tipo_plaza"><option value="0">Seleccione</option>';
		foreach($array_tipo_plaza as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row['tipo_plaza'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr';
//		if(nivelUsuario() < 3) echo ' style="display:none;"';
		echo '><th align="left">Plantilla</th><td><select name="plantilla" id="plantilla"><option value="0">Seleccione</option>';
		foreach($array_plantilla as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row['plantilla'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		

		echo '</table>';
		
	}
*/
/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<!--<td><a href="#" onClick="atcr(\'certificado_sinventa.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>-->
			 </tr>';
		echo '</table>';
		echo '<table>';
		if($_POST['plazausuario']==0){
			echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" onChange=""><option value="all">Todas</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td></tr>';
		}
		else{
			echo '<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Certifiado</td><td><select name="engomado" id="engomado" onChange=""><option value="">Todas</option>';
			foreach($array_engomado as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
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
			objeto.open("POST","certificado_sinventa.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&engomado="+document.getElementById("engomado").value+"&plaza="+document.getElementById("plaza").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

	}';	
	echo '
	
	</Script>
';

?>

