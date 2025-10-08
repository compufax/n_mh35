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
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th></th><th>Folio</th><th>Fecha</th><th>Hora</th><th>Placa</th><th>Cantidad</th><th>Total</th><th>Usuario</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($row=mysql_fetch_array($res)) {
				rowb();
					if($row['estatus']!="A"){
					echo '<td align="center">Cancelado</br>'.$array_usuario[$row['usucan']].' </br>'.$row['fechacan'].' '.$row['horacan'].'</td>';
					$row['total']=0;
				}else{
				echo '<td align="center" width="40" nowrap><a href="#" onClick="if(confirm(\'Esta seguro de cancelar\')) atcr(\'copiasxplaca.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$Plaza['cve'].'"></a>
						<a href="#" onClick="atcr(\'\',\'_blank\',\'102\','.$row['cve'].');"><img src="images/b_print.png" border="0" title="Imprimir"></a>&nbsp;</td>';
				}
				echo '<td align="center">'.htmlentities(utf8_encode($row['folio'])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($row['fecha'])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($row['hora'])).'</td>';
				$selec= " SELECT placa FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' and cve='".$row['placa']."'";
			//$select.=" ORDER BY cve desc";
//		echo''.$select.'';
		$re=mysql_query($selec)or die(mysql_error());
		$row1=mysql_fetch_array($re);
				
				echo '<td align="center">'.htmlentities(utf8_encode($row1['placa'])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($row['cantidad'])).'</td>';
				echo '<td align="right">'.htmlentities(utf8_encode($row['total'])).'</td>';
//				echo '<td>'.htmlentities(utf8_encode($row['obs'])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($array_usuario[$row['usuario']])).'</td>';
				echo '</tr>';
				$tot=$tot+$row['total'];
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

if($_POST['ajax']==2) {
		//Listado de plazas
		$select= " SELECT cve FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' and placa='".$_POST['cod']."'";
		$select.=" ORDER BY cve desc";
//		echo''.$select.'';
		$res=mysql_query($select)or die(mysql_error());
		$row=mysql_fetch_array($res);
		
		$selec= " SELECT cve,mes FROM cobro_engomado_referencia WHERE plaza='".$_POST['plazausuario']."' and ticket='".$row['cve']."'";
//		$selec.=" ORDER BY cve desc";
//		echo''.$selec.'';
		$re=mysql_query($selec)or die(mysql_error());
		$row1=mysql_fetch_array($re);
		
		echo''.$row1[0].'|*|'.$row1[1].'|*|'.$row[0].'';
		exit();
}

top($_SESSION);
if($_POST['cmd']==102){
		
		$select= " SELECT * FROM copiasxplaca WHERE plaza='".$_POST['plazausuario']."' and cve='".$_POST['reg']."'";
		$select.=" ORDER BY folio desc";
//		echo''.$select.'';
		$res=mysql_query($select)or die(mysql_error());
		$row=mysql_fetch_array($res);
	$textoimp="";
	$textoimp.="    Nota|||";
	$textoimp.="FOLIO: ".$row['folio']."|";
	$textoimp.="FECHA: ".$row['fecha']." ".$row['hora']."|";
	
	$selec= " SELECT placa FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' and cve='".$row['placa']."'";
			//$select.=" ORDER BY cve desc";
//		echo''.$select.'';
		$re=mysql_query($selec)or die(mysql_error());
		$row1=mysql_fetch_array($re);
	$textoimp.="PLACA: ".$row1['placa']."|";
	$textoimp.="CANTIDAD: ".$row['cantidad']."|";
	$textoimp.="TOTAL: ".$row['cantidad']."|";
	//	$textoimp.=sprintf("%10s",$row['precio']).sprintf("%10s",number_format($row['litros'],3)).sprintf("%15s",number_format($row['precio']*$row['litros'],3))."|";
	$impresion='<iframe src="http://localhost/impresiongeneral.php?textoimp='.$textoimp.'&copia=si&ncopias=1" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",1000);</script>';
}


/*** ACTUALIZAR REGISTRO  **************************************************/
if ($_POST['cmd']==-7) {

	if($_POST['reg']>0) {
			//Actualizar el Registro
			if($_POST['f_inicial']>0){
			if($_POST['f_final']>$_POST['f_inicial']){$resutado=$_POST['f_final'] - $_POST['f_inicial'];}else{$_POST['f_final']="";$resutado=0;}
			mysql_query("update copias_folios SET f_inicial='".$_POST['f_inicial']."',usuario_i='".$_POST['cveusuario']."',
			fecha_i='".fechaLocal()."',hora_i='".horaLocal()."',
			resto='".$resutado."',f_final='".$_POST['f_final']."',usuario_f='".$_POST['cveusuario']."',fecha_f='".fechaLocal()."',hora_f='".horaLocal()."'
			WHERE cve='".$_POST['reg']."'")or die(mysql_error());
			}
			
	} else {
			//Insertar el Registro

			if($_POST['f_inicial']>0){
				if($_POST['f_final']>$_POST['f_inicial']){$resutado=$_POST['f_final'] - $_POST['f_inicial'];}else{$_POST['f_final']="";$resutado=0;}
			$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM copias_folios WHERE plaza='".$_POST['plazausuario']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
			mysql_query("insert copias_folios SET folio='".$Folio[0]."',f_inicial='".$_POST['f_inicial']."',usuario_i='".$_POST['cveusuario']."',
			fecha='".fechaLocal()."',fecha_i='".fechaLocal()."',hora_i='".horaLocal()."',
			resto='".$resutado."',f_final='".$_POST['f_final']."',usuario_f='".$_POST['cveusuario']."',fecha_f='".fechaLocal()."',hora_f='".horaLocal()."',
			plaza='".$_POST['plazausuario']."'")or die(mysql_error());
		}
	}
	$_POST['cmd']=0;
}
if($_POST['cmd']==-6){
	if($_POST['f_final']>$_POST['f_inicial']){
			  $resutado=$_POST['f_final'] - $_POST['f_inicial'];
	mysql_query("UPDATE copias_folios SET resto='".$resutado."',f_final='".$_POST['f_final']."',usuario_f='".$_POST['cveusuario']."',fecha_f='".fechaLocal()."',hora_f='".horaLocal()."' WHERE cve='".$_POST['reg']."'")or die(mysql_error());
	}
	$_POST['cmd']=0;
}
if($_POST['cmd']==-5){
	if($_POST['f_inicial']>0){
	$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM copias_folios WHERE plaza='".$_POST['plazausuario']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
	mysql_query("insert copias_folios SET folio='".$Folio[0]."',f_inicial='".$_POST['f_inicial']."',usuario_i='".$_POST['cveusuario']."',fecha='".fechaLocal()."',fecha_i='".fechaLocal()."',hora_i='".horaLocal()."',plaza='".$_POST['plazausuario']."'")or die(mysql_error());
	}
	$_POST['cmd']=0;
}
if($_POST['cmd']==3){
	mysql_query("UPDATE copiasxplaca SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()."',horacan='".horaLocal()."' WHERE cve='".$_POST['reg']."'")or die(mysql_error());
	$_POST['cmd']=0;
}
if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE copi 
						SET nombre='".$_POST['nombre']."',numero='".$_POST['numero']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update);		
	} else {
			//Insertar el Registro
			$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM copiasxplaca WHERE plaza='".$_POST['plazausuario']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);

			$insert = " INSERT copiasxplaca set folio='".$Folio[0]."',cantidad='".$_POST['cantidad']."',total='".$_POST['cantidad']."',costo_uni='1',obs='".$_POST['obs']."',
						fecha='".fechaLocal()."',hora='".horaLocal()."',estatus='A',usuario='".$_POST['cveusuario']."',plaza='".$_POST['plazausuario']."',placa='".$_POST['placa_']."'";
			$ejecutar = mysql_query($insert) or die(mysql_error());
			$id = mysql_insert_id();

			
	}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
//		$select=" SELECT * FROM copias WHERE cve='".$_POST['reg']."' ";
//		$res=mysql_query($select);
//		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'copiasxplaca.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'copiasxplaca.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
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
			objeto.open("POST","copiasxplaca.php",true);
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
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
			if($PlazaLocal!=1)
				echo '<td><a href="#" onClick="atcr(\'copiasxplaca.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
';
		echo '</tr>';
		echo '</table>';
		echo '<table><tr><td><table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" readOnly size="12" value="'.fechaLocal().'" >&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
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
			objeto.open("POST","copiasxplaca.php",true);
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

