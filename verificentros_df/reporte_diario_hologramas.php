<?php 

include ("main.php"); 
  function lastday() { 
      $month = date('m');
      $year = date('Y');
      $day = date("d", mktime(0,0,0, $month+1, 0, $year));
 
      return date('Y-m-d', mktime(0,0,0, $month, $day, $year));
  };
 
  /** Actual month first day **/
  function firstday() {
      $month = date('m');
      $year = date('Y');
      return date('Y-m-d', mktime(0,0,0, $month, 1, $year));
  }
$res=mysql_query("SELECT local FROM plazas WHERE cve='".$_POST['plazausuario']."'");
$row=mysql_fetch_array($res);
$PlazaLocal=$row[0];
$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}
/*** CONSULTA AJAX  **************************************************/
$impresion="";
if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM copiasxplaca WHERE plaza='".$_POST['plazausuario']."' and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		$select.=" ORDER BY folio desc";
//		echo''.$select.'';
		$res=mysql_query($select)or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);
		
		$tot=0;
		
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
//			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Holograma</th>';
			$fecha=$_POST['fecha_ini'];
			while($fecha<=$_POST['fecha_fin']){
				echo'<th width="25">'.$fecha.'</th>';
			$fecha=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
			}
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			
			$res = mysql_query("SELECT * FROM engomados WHERE localidad = '".$_POST['localidad']."' AND entrega=1 ORDER BY nombre");
			while($row=mysql_fetch_array($res)){
			echo'<th>'.$row['nombre'].'</th>';
			$fech=$_POST['fecha_ini'];
			while($fech<=$_POST['fecha_fin']){
				rowb();
					
				echo '<td align="center">'.htmlentities(utf8_encode($row1['placa'])).'</td>';

				
				$tot=$tot+$row['total'];
				
			$fech=date( "Y-m-d" , strtotime ( "+1 day" , strtotime($fecha) ) );
			}
			echo '</tr>';
			}
			
			
			echo '	
				<tr>
				<td colspan="8" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				</tr>
			</table>';
			
		
		
		exit();	
}	



top($_SESSION);

/*** ACTUALIZAR REGISTRO  **************************************************/


/*if($_POST['cmd']==3){
	mysql_query("UPDATE copiasxpla SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()."',horacan='".horaLocal()."' WHERE cve='".$_POST['reg']."'")or die(mysql_error());
	$_POST['cmd']=0;
}*/
/*if ($_POST['cmd']==-2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE copi 
						SET nombre='".$_POST['nombre']."',numero='".$_POST['numero']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);		
	} else {
			//Insertar el Registro
			$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM copiasxpl WHERE plaza='".$_POST['plazausuario']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);

			$insert = " INSERT copiasxpla set folio='".$Folio[0]."',cantidad='".$_POST['cantidad']."',total='".$_POST['cantidad']."',costo_uni='1',obs='".$_POST['obs']."',
						fecha='".fechaLocal()."',hora='".horaLocal()."',estatus='A',usuario='".$_POST['cveusuario']."',plaza='".$_POST['plazausuario']."',placa='".$_POST['placa_']."'";
			$ejecutar = mysql_query($insert) or die(mysql_error());
			$id = mysql_insert_id();

			
	}
	$_POST['cmd']=0;
}*/

/*** EDICION  **************************************************/

/*	if ($_POST['cmd']==1) {
		
//		$select=" SELECT * FROM copias WHERE cve='".$_POST['reg']."' ";
//		$res=mysql_query($select);
//		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'reporte_diario_hologramas.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'reporte_diario_hologramas.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc"></td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">Placa</th><input type="hidden" name="placa_" id="placa_" value=""><td><input type="text" name="placa" id="placa" class="textField" size="10" value="'.$row['placa'].'" onKeyUp="if(event.keyCode==13){ traeNota(this.value);}">**Enter</td></tr>';
		echo '<tr><th align="left">Nota</th><td><input type="text" name="nota" id="nota" class="textField" size="10" value="'.$row['nota'].'" class="readOnly" readOnly></td></tr>';
		echo '<tr><th align="left">Fecha</th><td><input type="text" name="fecha" id="fecha" class="textField" size="15" value="'.$row['fecha'].'" class="readOnly" readOnly></td></tr>';
		echo '<tr><th align="left">Catindad</th><td><input type="text" name="cantidad" id="cantidad" class="textField" size="5" value="'.$row['cantidad'].'" onKeyUp="if(event.keyCode==13){ traeTotal(this.value);}"></td></tr>';
//		echo '<tr><th align="left">Total</th><td><input type="text" name="total" id="total" class="textField" size="5" value="'.$row['total'].'" class="readOnly" readonly></td></tr>';
		
		//echo '<tr><th align="left">Observaciones</th><td><textarea name="obs" id="obs" >'.$row['obs'].'</textarea></td></tr>';
		echo '</table>';		
		echo '
<Script language="javascript">
        function traeTotal(cant){
			tot=(cant)*(1);
			document.getElementById("total").value=tot;
		}
		
		function traeNota(reg)
	{

		objeto=crearObjeto(reg);
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","reporte_diario_hologramas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&cod="+reg+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4){
				datos = objeto.responseText.split("|*|");
				document.getElementById("nota").value = datos[0];
				document.getElementById("fecha").value = datos[1];
				document.getElementById("placa_").value = datos[2];
				
				}
			}
		}

	}
		
</Script>';
		
	}*/

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
			if($PlazaLocal!=1)
				echo '<td><a href="#" onClick="atcr(\'reporte_diario_hologramas.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
';
		echo '</tr>';
		echo '</table>';
		echo '<table><tr><td><table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" readOnly size="12" value="'.firstday().'" >&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.lastday().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
//		echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td></tr>';
		echo '</table></td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		</td>
		<td>';
		echo'</td></tr></table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
	
bottom();



/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript" >

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","reporte_diario_hologramas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4){
				document.getElementById("Resultados").innerHTML = objeto.responseText

				}
			}
		}

	}
	buscarRegistros();
	
	</Script>
';

?>

