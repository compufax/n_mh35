<?php 

include ("main.php"); 





/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM plazas WHERE 1 ";
		if ($_POST['nom']!="") { $select.=" AND numero LIKE '%".$_POST['nom']."%' "; }
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
			echo '<tr bgcolor="#E9F2F8">';
			echo '<th>Centro</th><th>Pago</th><th>Fecha</th><th>Oficio P/F</th><th>Fecha</th><th>Oficio P/E</th><th>Fecha</th>
			<th>Examen</th><th>Fecha</th><th>Hora</th><th>Dia</th><th>Observacion</th>';
			if(nivelUsuario()>=2){
				echo '<th>Guardar Cambios</th>';
			}
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			while($Plaza=mysql_fetch_array($rsplaza)) {
				rowb();
				if($Plaza['fecha_pago']=='0000-00-00') $Plaza['fecha_pago']='';
				if($Plaza['fecha_oficio_p_f']=='0000-00-00') $Plaza['fecha_oficio_p_f']='';
				if($Plaza['fecha_oficio_p_e']=='0000-00-00') $Plaza['fecha_oficio_p_e']='';
				if($Plaza['fecha_examen']=='0000-00-00') $Plaza['fecha_examen']='';
				echo '<td>'.htmlentities($Plaza['numero']).'</td>';
				echo '<td align="center"><input type="hidden" id="pago_'.$Plaza['cve'].'" value="'.$Plaza['pago'].'">
				<input type="checkbox" onClick="if(this.checked) $(\'#pago_'.$Plaza['cve'].'\').val(1); else $(\'#pago_'.$Plaza['cve'].'\').val(0)"';
				if($Plaza['pago']==1) echo ' checked';
				echo '></td>';
				echo '<td align="center"><input type="text" id="fecha_pago_'.$Plaza['cve'].'" class="readOnly" size="12" value="'.$Plaza['fecha_pago'].'" readOnly>
				&nbsp;<span style="cursor:pointer" onClick="displayCalendar(document.getElementById(\'fecha_pago_'.$Plaza['cve'].'\'),\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></span></td>';
				echo '<td align="center"><input type="hidden" id="oficio_p_f_'.$Plaza['cve'].'" value="'.$Plaza['oficio_p_f'].'">
				<input type="checkbox" onClick="if(this.checked) $(\'#oficio_p_f_'.$Plaza['cve'].'\').val(1); else $(\'#oficio_p_f_'.$Plaza['cve'].'\').val(0)"';
				if($Plaza['oficio_p_f']==1) echo ' checked';
				echo '></td>';
				echo '<td align="center"><input type="text" id="fecha_oficio_p_f_'.$Plaza['cve'].'" class="readOnly" size="12" value="'.$Plaza['fecha_oficio_p_f'].'" readOnly>
				&nbsp;<span style="cursor:pointer" onClick="displayCalendar(document.getElementById(\'fecha_oficio_p_f_'.$Plaza['cve'].'\'),\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></span></td>';
				echo '<td align="center"><input type="hidden" id="oficio_p_e_'.$Plaza['cve'].'" value="'.$Plaza['oficio_p_e'].'">
				<input type="checkbox" onClick="if(this.checked) $(\'#oficio_p_e_'.$Plaza['cve'].'\').val(1); else $(\'#oficio_p_e_'.$Plaza['cve'].'\').val(0)"';
				if($Plaza['oficio_p_e']==1) echo ' checked';
				echo '></td>';
				echo '<td align="center"><input type="text" id="fecha_oficio_p_e_'.$Plaza['cve'].'" class="readOnly" size="12" value="'.$Plaza['fecha_oficio_p_e'].'" readOnly>
				&nbsp;<span style="cursor:pointer" onClick="displayCalendar(document.getElementById(\'fecha_oficio_p_e_'.$Plaza['cve'].'\'),\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></span></td>';
				echo '<td align="center"><input type="hidden" id="examen_'.$Plaza['cve'].'" value="'.$Plaza['examen'].'">
				<input type="checkbox" onClick="if(this.checked) $(\'#examen_'.$Plaza['cve'].'\').val(1); else $(\'#examen_'.$Plaza['cve'].'\').val(0)"';
				if($Plaza['examen']==1) echo ' checked';
				echo '></td>';
				echo '<td align="center"><input type="text" id="fecha_examen_'.$Plaza['cve'].'" class="readOnly" size="12" value="'.$Plaza['fecha_examen'].'" readOnly>
				&nbsp;<span style="cursor:pointer" onClick="document.forma.nplaza.value='.$Plaza['cve'].';displayCalendar(document.getElementById(\'fecha_examen_'.$Plaza['cve'].'\'),\'yyyy-mm-dd\',this,true,\'\',\'traerDiaSemana\')"><img src="images/calendario.gif" border="0"></span></td>';
				echo '<td align="center"><input type="text" id="hora_examen_'.$Plaza['cve'].'" class="textField" size="12" value="'.$Plaza['hora_examen'].'"></td>';
				echo '<td align="center"><input type="text" id="dia_'.$Plaza['cve'].'" class="readOnly" size="12" value="'.$Plaza['dia'].'" readOnly></td>';
				echo '<td align="center"><textarea rows="3" cols="30" class="textField" id="obs_'.$Plaza['cve'].'">'.$Plaza['hora_examen'].'</textarea></td>';
				if(nivelUsuario()>=2){
					echo '<td align="center"><span style="cursor:pointer;" onClick="guardarPlaza('.$Plaza['cve'].')"><img src="images/guardar.gif" border="0"></span></td>';
				}
				echo '</tr>';
			}
			echo '	
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

if($_POST['ajax']==2){
	mysql_query("INSERT historial_estatus_ecologia SET plaza='".$_POST['cveplaza']."',fechag='".fechaLocal()."',horag='".horaLocal()."',
	usuario='".$_POST['cveusuario']."',pago='".$_POST['pago']."',fecha_pago='".$_POST['fecha_pago']."',oficio_p_f='".$_POST['oficio_p_f']."',
	fecha_oficio_p_f='".$_POST['fecha_oficio_p_f']."',oficio_p_e='".$_POST['oficio_p_e']."',
	fecha_oficio_p_e='".$_POST['fecha_oficio_p_e']."',examen='".$_POST['examen']."',
	fecha_examen='".$_POST['fecha_examen']."',hora_examen='".$_POST['hora_examen']."',dia='".$_POST['dia']."',
	obs='".$_POST['obs']."'");
	mysql_query("UPDATE plazas SET pago='".$_POST['pago']."',fecha_pago='".$_POST['fecha_pago']."',oficio_p_f='".$_POST['oficio_p_f']."',
	fecha_oficio_p_f='".$_POST['fecha_oficio_p_f']."',oficio_p_e='".$_POST['oficio_p_e']."',
	fecha_oficio_p_e='".$_POST['fecha_oficio_p_e']."',examen='".$_POST['examen']."',
	fecha_examen='".$_POST['fecha_examen']."',hora_examen='".$_POST['hora_examen']."',dia='".$_POST['dia']."',
	obs='".$_POST['obs']."' WHERE cve='".$_POST['cveplaza']."'");
	exit();
}
top($_SESSION);

/*** EDICION  **************************************************/


/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td>Centro</td><td><input type="text" name="nom" id="nom" size="50" class="textField" value=""></td><td>&nbsp;</td><td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<!--<td><a href="#" onClick="atcr(\'plazas.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>-->
				</tr>';
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="nplaza" value="">';
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
			objeto.open("POST","estatus_ecologia.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nom="+document.getElementById("nom").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	function traerDiaSemana(){
		datosfecha=document.getElementById("fecha_examen_"+document.forma.nplaza.value).value.split("-");
		miFecha = new Date(datosfecha[0],(datosfecha[1]-1),datosfecha[2]);
		dsemana = ["Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"];
		document.getElementById("dia_"+document.forma.nplaza.value).value = dsemana[miFecha.getDay()];
	}
	
	function guardarPlaza(cveplaza){
		if(confirm("Esta seguro de guardar los cambios en el registro?")){
			objeto=crearObjeto();
			if (objeto.readyState != 0) {
				alert("Error: El Navegador no soporta AJAX");
			} else {
				objeto.open("POST","estatus_ecologia.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=2&cveplaza="+cveplaza+"&pago="+document.getElementById("pago_"+cveplaza).value+"&fecha_pago="+document.getElementById("fecha_pago_"+cveplaza).value+"&oficio_p_f="+document.getElementById("oficio_p_f_"+cveplaza).value+"&fecha_oficio_p_f="+document.getElementById("fecha_oficio_p_f_"+cveplaza).value+"&oficio_p_e="+document.getElementById("oficio_p_e_"+cveplaza).value+"&fecha_oficio_p_e="+document.getElementById("fecha_oficio_p_e_"+cveplaza).value+"&examen="+document.getElementById("examen_"+cveplaza).value+"&fecha_examen="+document.getElementById("fecha_examen_"+cveplaza).value+"&hora_examen="+document.getElementById("hora_examen_"+cveplaza).value+"&dia="+document.getElementById("dia_"+cveplaza).value+"&obs="+document.getElementById("obs_"+cveplaza).value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
				objeto.onreadystatechange = function()
				{
					if (objeto.readyState==4)
					{alert("El registro se ha guardado correctamente");}
				}
			}
		}
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

