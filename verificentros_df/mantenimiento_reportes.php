<?php 
include ("main.php"); 
$rsMotivo=mysql_query("SELECT * FROM proveedores ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_empresas[$Motivo['cve']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM tecnicos_mantenimiento ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_tecnico[$Motivo['cve']]=$Motivo['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM refacciones ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_materiales[$Motivo['cve']]=$Motivo['nombre'];
}
$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$array_tipo_cargo=Array(1=>"Con Cargo",2=>"Garantia",3=>"Poliza");
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM mantenimiento_reportes WHERE 1 ";
		if ($_POST['nom']!="") { $select.=" AND cve='".$_POST['nom']."'"; }
		$select.=" ORDER BY cve desc";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th></th><th>Folio</th>';
			echo '<th>Empresa</th><th>Tipo Cargo</th><th>Monto</th><th>Usuario</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$tot=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar"></a></td>';
				echo '<td align="center">'.$row['cve'].'</td>';
				echo '<td align="center">'.$array_empresas[$row['empresa']].'</td>';
				echo '<td align="center">'.$array_tipo_cargo[$row['tipo_cargo']].'</td>';
				echo '<td align="right">'.number_format($row['monto'],2).'</td>';
				echo '<td align="center">'.$array_usuario[$row['usuario']].'</td>';
				$tot=$tot + $row['monto'];
				echo '</tr>';
			}
			echo '	
				<tr bgcolor="#E9F2F8">
				<td colspan="3" bgcolor="#E9F2F8">';menunavegacion();echo '</td><td align="right">Total</td><td align="right">'.number_format($tot,2).'</td><td></td>
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

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE mantenimiento_reportes 
						set empresa='".$_POST['empresa']."',no_reporte='".$_POST['no_reporte']."',tecnico='".$_POST['tecnico']."',operaciones='".$_POST['operaciones']."',
						obs='".$_POST['obs']."',tipo_cargo='".$_POST['tipo_cargo']."',monto='".$_POST['monto']."' where cve='".$_POST['reg']."'";
			$ejecutar = mysql_query($update) or die(mysql_error());

			foreach ($array_materiales as $k=>$v) { 
			
			$insert = " update mantenimiento_reportes_detalle_material 

						set cantidad='".$_POST['cve_'.$k.'']."'  where cve_aux='".$_POST['reg']."' and cve_mat='".$k."'";
			$ejecutar = mysql_query($insert);
			}
	} else {
			//Insertar el Registro
			$insert = " INSERT INTO mantenimiento_reportes 
						(empresa,no_reporte,tecnico,operaciones,obs,tipo_cargo,monto,usuario,fecha,hora)
						VALUES 
						('".$_POST['empresa']."','".$_POST['no_reporte']."','".$_POST['tecnico']."','".$_POST['operaciones']."','".$_POST['obs']."',
						 '".$_POST['tipo_cargo']."','".$_POST['monto']."','".$_POST['cveusuario']."','".fechaLocal()."','".horaLocal()."')";
			$ejecutar = mysql_query($insert);
			$id=mysql_insert_id();
			foreach ($array_materiales as $k=>$v) { 
			
			$insert = " INSERT INTO mantenimiento_reportes_detalle_material 
						(cve_aux,cve_mat,cantidad)
						VALUES 
						('".$id."','".$k."','".$_POST['cve_'.$k.'']."')";
			$ejecutar = mysql_query($insert);
			}
			
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM mantenimiento_reportes WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show(); atcr(\'mantenimiento_reportes.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'mantenimiento_reportes.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Reportes</td></tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><th align="left">Empresa</th><td><select name="empresa" id="empresa" class="textField"><option value="">---Seleccione---</option>';
			foreach ($array_empresas as $k=>$v) { 
	    echo '<option value="'.$k.'"';if($row['empresa']==$k){echo'selected';}echo'>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Reporte</th><td><input type="text" name="no_reporte" id="no_reporte" class="textField" size="" value="'.$row['no_reporte'].'"></td></tr>';
		echo '<tr><th align="left">Tecnico</th><td><select name="tecnico" id="tecnico" class="textField"><option value="">---Seleccione---</option>';
			foreach ($array_tecnico as $k=>$v) { 
	    echo '<option value="'.$k.'"';if($row['tecnico']==$k){echo'selected';}echo'>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Tipo Cargo</th><td><select name="tipo_cargo" id="tipo_cargo" onChange="cargo()"><option value="">---Seleccione---</option>';
			foreach ($array_tipo_cargo as $k=>$v) { 
	    echo '<option value="'.$k.'"';if($row['tipo_cargo']==$k){echo'selected';}echo'>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr class="monto_" style="display:none;"><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="textField" size="" value="'.number_format($row['monto'],2).'"></td></tr>';

		foreach ($array_materiales as $k=>$v) { 
		
			$res1=mysql_query("SELECT * FROM mantenimiento_reportes_detalle_material where cve_aux='".$row['cve']."' and cve_mat ='".$k."' ");
			$row1=mysql_fetch_array($res1);
			echo '<tr><th align="left">'.$v.'</th><td><input type="text" name="cve_'.$k.'" id="cve_'.$k.'" class="textField" size="" placeholder="Cantidad" value="'.$row1['cantidad'].'"></td></tr>';
			}

		
		
		echo '<tr><th align="left">Operaciones</th><td><textarea cols="50" rows="5" name="operaciones" id="operaciones">'.$row['operaciones'].'</textarea></td></tr>';
		echo '<tr><th align="left">Observaciones</th><td><textarea cols="50" rows="5" name="obs" id="obs">'.$row['obs'].'</textarea></td></tr>';
				
		echo '</table>';
		echo '
<Script>
	function cargo(){
	
	if(document.getElementById("tipo_cargo").value==1 ){
		$(".monto_").show();
	}
	if(document.getElementById("tipo_cargo").value==2 || document.getElementById("tipo_cargo").value==3){
		$(".monto_").hide();
	}
	
	}

	</script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'mantenimiento_reportes.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Folio</td><td><input type="text" name="nom" id="nom" size="" class="textField" value=""></td></tr>';
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
			objeto.open("POST","mantenimiento_reportes.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

