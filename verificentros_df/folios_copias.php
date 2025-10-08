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

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM copias_folios WHERE plaza='".$_POST['plazausuario']."' and fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		$select.=" ORDER BY folio desc";
//		echo''.$select.'';
		$res=mysql_query($select)or die(mysql_error());
		$totalRegistros = mysql_num_rows($res);
		
		$tot=0;
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($rsbenef).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Folio</th><th>Fecha</th><th>Folio</br>Inicial</th><th>Folio</br>Final</th><th>Cantidad</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$f_inicial_anterior=0;
			$cve_anterior=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
/*					if($row['estatus']!="A"){
					echo '<td align="center">Cancelado</br>'.$array_usuario[$row['usucan']].' </br>'.$row['fechacan'].' '.$row['horacan'].'</td>';
					$row['total']=0;
				}else{
				echo '<td align="center" width="40" nowrap><a href="#" onClick="if(confirm(\'Esta seguro de cancelar\')) atcr(\'folios_copias.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$Plaza['cve'].'"></a></td>';
				}*/
				echo '<td align="center">'.htmlentities(utf8_encode($row['folio'])).'</td>';
				echo '<td align="center">'.htmlentities(utf8_encode($row['fecha'])).'</td>';
				/*if($_POST['cveusuario']==1){
			echo'<td align="center"><input type="text" name="f_inicial_'.$row['cve'].'" id="f_inicial_'.$row['cve'].'" value="'.$row['f_inicial'].'"> -<a href="#" onClick="Guardar_folio('.$row['cve'].');"><img src="images/guardar.gif" border="0"></a></td>';
				}else{
				echo '<td align="center">'.htmlentities(utf8_encode($row['f_inicial'])).'</td>';
					}
				if($row['f_final']==0){
					$folio_anterior=$row['folio'] + 1;

					$select1= " SELECT * FROM copias_folios WHERE plaza='".$_POST['plazausuario']."' and folio='".$folio_anterior."'";
					$res1=mysql_query($select1)or die(mysql_error());
					$row1=mysql_fetch_array($res1);
					$nuevo_resto=($row1['f_inicial'])-($row['f_inicial']);
					$resto=(($nuevo_resto)-($row1['resto']));
					if($resto<0){$resto=0;}

				if($_POST['cveusuario']==1){
					echo'<td align="center"><input type="text" name="f_final_'.$row['cve'].'" id="f_final_'.$row['cve'].'" value="'.$row['f_final'].'">+ <a href="#" onClick="Guardar_folio('.$row['cve'].');"><img src="images/guardar.gif" border="0"></a></td>';
				}else{
				echo '<td align="center">'.htmlentities(utf8_encode($row['f_final'])).'</td>';
			}
				echo '<td align="center">'.htmlentities(utf8_encode($resto)).'</td>';
				}else{
				$f_inicial_anterior=0;	
				if($_POST['cveusuario']==1){
		echo'<td align="center"><input type="text" name="f_final_'.$row['cve'].'" id="f_final_'.$row['cve'].'" value="'.$row['f_final'].'"> *<a href="#" onClick="Guardar_folio('.$row['cve'].');"><img src="images/guardar.gif" border="0"></a></td>';
				}else{
				echo '<td align="center">'.htmlentities(utf8_encode($row['f_final'])).'</td>';
			}*/
				echo'<td align="center"><input type="text" name="f_inicial_'.$row['cve'].'" id="f_inicial_'.$row['cve'].'" value="'.$row['f_inicial'].'"> -<a href="#" onClick="Guardar_folio('.$row['cve'].');"><img src="images/guardar.gif" border="0"></a></td>';

				echo'<td align="center"><input type="text" name="f_final_'.$row['cve'].'" id="f_final_'.$row['cve'].'" value="'.$row['f_final'].'"> *<a href="#" onClick="Guardar_folio('.$row['cve'].'	);"><img src="images/guardar.gif" border="0"></a></td>';
				$resto=($row['f_final'])-($row['f_inicial']);
				if($resto<0){$resto=0;}
				echo '<td align="center">'.htmlentities(utf8_encode($resto)).'</td>';
//				}

				$t_resto=$t_resto+$resto;
//				echo '<td align="right">'.htmlentities(utf8_encode($row['total'])).'</td>';
//				echo '<td>'.htmlentities(utf8_encode($row['obs'])).'</td>';
//				echo '<td align="center">'.htmlentities(utf8_encode($array_usuario[$row['usuario']])).'</td>';
				echo '</tr>';
				$tot=$tot+$row['total'];
			}
			echo '	
				<tr>
				<td colspan="3" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td colspan="" bgcolor="#E9F2F8" align="right">Total</td>
				<td colspan="" bgcolor="#E9F2F8" align="center">'.$t_resto.'</td>
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
		/*echo'|*|';
		
		$sel="SELECT sum(copias) as t_copias from cobro_engomado where fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."'";
		$rs=mysql_query($sel) or die(mysql_error());
		$row1=mysql_fetch_array($rs);
		echo'<table>
			<tr><td>Total de Copias</td><td>'.number_format($tot,2).'</td></tr>
			<tr><td>Total de Copias en Ventas</td><td>'.number_format($row1['t_copias'],2).'</td></tr>
			<tr><td>Gran Total</td><td>'.number_format($tot+$row1['t_copias']).'</td></tr>
		</table>';
		
		echo'|*|';
		if($_POST['fecha_ini']<fechaLocal() and $_POST['fecha_fin']<fechaLocal()){
		if($_POST['fecha_ini']==$_POST['fecha_fin']){
		$sel3="SELECT * from copias_folios where fecha='".$_POST['fecha_ini']."' and plaza='".$_POST['plazausuario']."'";
		$rs3=mysql_query($sel3);
		$row3=mysql_fetch_array($rs3);
		echo'<table>
			<tr><td>Folios del dia </td><td>'.$row3['fecha'].'</td></tr>
			<tr><td>Folio Inicial</td><td>'.$row3['f_inicial'].'</td></tr>
			<tr><td>Folio Final</td><td>'.$row3['f_final'].'</td></tr>
			<tr><td>Total</td><td>'.($row3['f_final']-$row3['f_inicial']).'</td></tr>
		</table>';
		}else{
		$sel2="SELECT sum(resto) as r_copias from copias_folios where fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and plaza='".$_POST['plazausuario']."' ";
		$rs2=mysql_query($sel2) or die(mysql_error());
		$row2=mysql_fetch_array($rs2);
		echo'<table>
			<tr><td>Folios del Periodo </td><td>'.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</td></tr>
			<tr><td></td><td></td></tr>
			<tr><td>Total</td><td>'.$row2['r_copias'].'</td></tr>
			<tr><td></td><td></td></tr>
		</table>';
		}
		}*/
		echo'<Script language="javascript">
        
		
		</Script>';
		
		
		exit();	
}	


top($_SESSION);
if ($_POST['cmd']==7) {
//	print_r($_POST);
//echo''.$_POST['f_final_'.$_POST['reg']].'';
//	if($_POST['reg']>0) {
			//Actualizar el Registro
//			if($_POST['f_inicial_'.$_POST['reg']]>0 and $_POST['f_final_'.$_POST['reg']]>0){
//			if($_POST['f_final_'.$_POST['reg']]>$_POST['f_inicial_'.$_POST['reg']]){$resutado=$_POST['f_final_'.$_POST['reg']] - $_POST['f_inicial_'.$_POST['reg']];}else{$_POST['f_final']="";$resutado=0;}
			mysql_query("update copias_folios SET f_inicial='".$_POST['f_inicial_'.$_POST['reg']]."',f_final='".$_POST['f_final_'.$_POST['reg']]."' WHERE cve='".$_POST['reg']."'")or die(mysql_error());
//			}
			
	/*} else {
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
	}*/
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/
/*if ($_POST['cmd']==7) {

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
if($_POST['cmd']==6){
	if($_POST['f_final']>$_POST['f_inicial']){
			  $resutado=$_POST['f_final'] - $_POST['f_inicial'];
	mysql_query("UPDATE copias_folios SET resto='".$resutado."',f_final='".$_POST['f_final']."',usuario_f='".$_POST['cveusuario']."',fecha_f='".fechaLocal()."',hora_f='".horaLocal()."' WHERE cve='".$_POST['reg']."'")or die(mysql_error());
	}
	$_POST['cmd']=0;
}
if($_POST['cmd']==5){
	if($_POST['f_inicial']>0){
	$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM copias_folios WHERE plaza='".$_POST['plazausuario']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
	mysql_query("insert copias_folios SET folio='".$Folio[0]."',f_inicial='".$_POST['f_inicial']."',usuario_i='".$_POST['cveusuario']."',fecha='".fechaLocal()."',fecha_i='".fechaLocal()."',hora_i='".horaLocal()."',plaza='".$_POST['plazausuario']."'")or die(mysql_error());
	}
	$_POST['cmd']=0;
}
if($_POST['cmd']==3){
	mysql_query("UPDATE copias SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()."',horacan='".horaLocal()."' WHERE cve='".$_POST['reg']."'")or die(mysql_error());
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
			$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM copias WHERE plaza='".$_POST['plazausuario']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);

			$insert = " INSERT copias set folio='".$Folio[0]."',cantidad='".$_POST['cantidad']."',total='".$_POST['cantidad']."',costo_uni='1',obs='".$_POST['obs']."',
						fecha='".fechaLocal()."',hora='".horaLocal()."',estatus='A',usuario='".$_POST['cveusuario']."',plaza='".$_POST['plazausuario']."'";
			$ejecutar = mysql_query($insert) or die(mysql_error());
			$id = mysql_insert_id();
			
	}
	$_POST['cmd']=0;
}
*/
/*** EDICION  **************************************************/

/*	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM copias WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1 && $row1[0]<=10)
				echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'folios_copias.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'folios_copias.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Copias</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">Catindad</th><td><input type="text" name="cantidad" id="cantidad" class="textField" size="5" value="'.$row['cantidad'].'" onKeyUp="if(event.keyCode==13){ traeTotal(this.value);}"></td></tr>';
		echo '<tr style="display:none"><th align="left">Total</th><td><input type="text" name="total" id="total" class="textField" size="5" value="'.$row['total'].'" class="readOnly" readonly></td></tr>';
		echo '<tr><th align="left">Observaciones</th><td><textarea name="obs" id="obs" >'.$row['obs'].'</textarea></td></tr>';
		echo '</table>';
		
		echo '
<Script language="javascript">
        function traeTotal(cant){
			document.getElementById("total").value=cant;
		}
		
</Script>';
		
	}*/

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
//			if($PlazaLocal!=1)
//				echo '<td><a href="#" onClick="atcr(\'folios_copias.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table><tr><td><table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
//		echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td></tr>';
		echo '</table></td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		</td><td id="totalCopias">
		</td><td></td>
		<td id="totalFolios">
		</td>
		<td>';

		$sel="SELECT * from copias_folios where fecha ='".fechaLocal()."' and plaza='".$_POST['plazausuario']."'";
		$rs=mysql_query($sel) or die(mysql_error());
		$row=mysql_fetch_array($rs);		
//		echo''.$sel.'';
		
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
			objeto.open("POST","folios_copias.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4){
				datos = objeto.responseText.split("|*|");
	
				document.getElementById("Resultados").innerHTML = datos[0];
	
				}
			}
		}

	}
	buscarRegistros();
	function Guardar_folio(cant){
			atcr("folios_copias.php","",7,cant);
		}
	
	</Script>
';

?>

