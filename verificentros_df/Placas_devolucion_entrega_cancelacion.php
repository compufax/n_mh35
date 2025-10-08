<?php 

include ("main.php"); 
 function firstday() {
      $month = date('m');
      $year = date('Y');
      return date('Y-m-d', mktime(0,0,0, $month, 1, $year));
  }

/*$array_cuentas = array();
$res = mysql_query("SELECT * FROM cuentas_contables ORDER BY cuenta,nombre");
while($row = mysql_fetch_array($res)){
	$array_cuentas[$row['cve']]=$row['cuenta'].' '.$row['nombre'];
}*/
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= "SELECT d.numero as Plaza, a.placa, IF(IFNULL(b.cve,0)>0, 'Si','No') as Entrega, IF(IFNULL(c.cve,0)>0, 'Si','No') as Cancelacion FROM cobro_engomado a LEFT JOIN certificados b on a.plaza = b.plaza and a.placa = b.placa AND b.estatus!='C' LEFT JOIN certificados_cancelados c ON a.plaza = c.plaza AND a.placa = c.placa AND c.estatus!='C' INNER JOIN plazas d ON d.cve = a.plaza WHERE a.estatus='D' AND (IFNULL(b.cve,0)>0 OR IFNULL(c.cve,0)>0) and a.plaza='".$_POST['plazausuario']."' and a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		if ($_POST['nom']!="") { $select.=" AND a.placa = '".$_POST['nom']."' "; }
		$select.=" GROUP BY a.plaza, a.placa";
		$res=mysql_query($select) or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);
		
//		echo''.$select.'';
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Placa</th><th>Entrega</th><th>Cancelacion</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center">'.htmlentities(utf8_encode($row['placa'])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($row['Entrega'])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($row['Cancelacion'])).'</td>';

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
				echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'Placas_devolucion_entrega_cancelacion.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'Placas_devolucion_entrega_cancelacion.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
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
				<!--<td><a href="#" onClick="atcr(\'Placas_devolucion_entrega_cancelacion.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>-->
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.firstday().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td></tr>';
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
			objeto.open("POST","Placas_devolucion_entrega_cancelacion.php",true);
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
	
	//Funcion para navegacion de Registros. 20 por pagina.
	';	
	
	echo '
	
	</Script>
';

?>

