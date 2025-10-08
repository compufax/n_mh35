<?php 

include ("main.php"); 

/*** ARREGLOS ***********************************************************/

$rsPlaza=mysql_db_query($base,"SELECT * FROM plazas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['nombre'];
}

$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}


if($_POST['cmd']==100){
	include('../fpdf153/fpdf.php');
	include("numlet.php");	
	class FPDF2 extends PDF_MC_Table {
		function Header(){
			global $_POST;
			$this->Image('images/membrete.JPG',60,3,150,15);
			$this->SetFont('Arial','B',16);
			//$this->Cell(190,10,'Autobuses Rapidos del Valle de Mexico',0,0,'C');
			//$this->Ln();
			$this->SetY(23);
			$tit='';
			$this->MultiCell(180,5,'Registros del Sistema de la fecha '.$_POST['fecha'],0,'C');
			$this->Ln();
			$this->Ln();
			$this->SetFont('Arial','B',11);
			$this->Cell(50,4,'Usuario',0,0,'C',0);
			$this->Cell(50,4,'Entrada',0,0,'C',0);
			$this->Cell(50,4,'IP',0,0,'C',0);
			//$this->Cell(50,4,'Salida',0,0,'C',0);
			//$this->Cell(30,4,'Tiempo',0,0,'C',0);
			$this->Ln();		
		}
		
		//Pie de página
		function Footer(){
			//Posición: a 1,5 cm del final
			$this->SetY(-15);
			//Arial bold 12
			$this->SetFont('Arial','B',11);
			//Número de página
			$this->Cell(0,10,'Página '.$this->PageNo().' de {nb}',0,0,'C');
		}
	}
	$pdf=new FPDF2('P','mm','LETTER');
	$pdf->AliasNbPages();
	$pdf->AddPage('P');
	$pdf->SetFont('Arial','',11);
	$select= " SELECT usuario, entrada , salida, ip FROM registros_sistema WHERE 1 ";
	if ($_POST['fecha']!="") { $select.=" AND left(entrada,10)>='".$_POST['fecha']."'"; }
	if ($_POST['fechaf']!="") { $select.=" AND left(entrada,10)<='".$_POST['fechaf']."'"; }
	if($_POST['usuario']!="all") { $select.=" AND usuario='".$_POST['usuario']."'";}
	$rsentradas=mysql_db_query($base,$select);
	$totalRegistros = mysql_num_rows($rsentradas);
	$select .= " ORDER BY entrada desc";
	$rsentradas=mysql_db_query($base,$select);
	$pdf->SetWidths(array(50,50,50));
	$pdf->SetAligns(array('C','C','C'));
	while($Entradas=mysql_fetch_array($rsentradas)) {
		$renglon=array();
		$renglon[]=$array_usuario[$Entradas['usuario']];
		$renglon[]=$Entradas['entrada'];
		/*$renglon[]=$Entradas['salida'];
		$fecha1=explode("-",substr($Entradas['entrada'],0,10));
		$tiempo1=explode(":",substr($Entradas['entrada'],11,8));
		if($Entradas['salida']=="0000-00-00 00:00:00"){
			$fechahora=fechahoraLocal();
			$fecha2=explode("-",substr($fechahora,0,10));
			$tiempo2=explode(":",substr($fechahora,11,8));
		}
		else{
			$fecha2=explode("-",substr($Entradas['salida'],0,10));
			$tiempo2=explode(":",substr($Entradas['salida'],11,8));
		}
		$t1=mktime($tiempo1[0],$tiempo1[1],$tiempo1[2],$fecha1[1],$fecha1[2],$fecha1[0]);
		$t2=mktime($tiempo2[0],$tiempo2[1],$tiempo2[2],$fecha2[1],$fecha2[2],$fecha2[0]);
		$tiempo=$t2-$t1;
		$horas = intval($tiempo / 3600);
		$min = intval(($tiempo-$horas*3600)/60);
		$seg = $tiempo-$horas*3600-$min*60;
		if($horas<10) $horas="0".$horas;
		if($min<10) $min="0".$min;
		if($seg<10) $seg="0".$seg;
		$tiempo_final=$horas.':'.$min.':'.$seg;
		$renglon[]=$tiempo_final;*/
		$renglon[]=$Entradas['ip'];
		$pdf->Row($renglon);
	}
	$pdf->SetFont('Arial','B',11);
	$pdf->Cell(180,5,$totalRegistros.' Registro(s)');
	$pdf->Output();
	exit();
}



/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de Parque
		$select= " SELECT usuario, entrada , ip FROM registros_sistema WHERE 1 ";
		if ($_POST['fecha']!="") { $select.=" AND left(entrada,10)>='".$_POST['fecha']."'"; }
		if ($_POST['fechaf']!="") { $select.=" AND left(entrada,10)<='".$_POST['fechaf']."'"; }
		if($_POST['usuario']!="all") { $select.=" AND usuario='".$_POST['usuario']."'";}
		$rsentradas=mysql_db_query($base,$select);
		$totalRegistros = mysql_num_rows($rsentradas);
		/*if($totalRegistros / $eRegistrosPagina > 1) 
		{
			$eTotalPaginas = $totalRegistros / $eRegistrosPagina;
			if(is_int($eTotalPaginas))
			{$eTotalPaginas--;}
			else
			{$eTotalPaginas = floor($eTotalPaginas);}
		}
		$select .= " ORDER BY entrada desc  LIMIT ".$primerRegistro.",".$eRegistrosPagina;*/
		$select .= " ORDER BY entrada desc";
		$rsentradas=mysql_db_query($base,$select);
		
		if(mysql_num_rows($rsentradas)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="5">'.mysql_num_rows($rsentradas).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8">';
			echo '<th>Usuario</th><th>Entrada</th><!--<th>Salida</th>';
			echo '<th>Tiempo</th>--><th>IP</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			while($Entradas=mysql_fetch_array($rsentradas)) {
				rowb();
				echo '<td align="center">'.htmlentities($array_usuario[$Entradas['usuario']]).'</td>';
				echo '<td align="center">'.$Entradas['entrada'].'</td>';
				/*echo '<td align="center">'.$Entradas['salida'].'</td>';
				$fecha1=explode("-",substr($Entradas['entrada'],0,10));
				$tiempo1=explode(":",substr($Entradas['entrada'],11,8));
				if($Entradas['salida']=="0000-00-00 00:00:00"){
					$fechahora=fechahoraLocal();
					$fecha2=explode("-",substr($fechahora,0,10));
					$tiempo2=explode(":",substr($fechahora,11,8));
				}
				else{
					$fecha2=explode("-",substr($Entradas['salida'],0,10));
					$tiempo2=explode(":",substr($Entradas['salida'],11,8));
				}
	
				$t1=mktime($tiempo1[0],$tiempo1[1],$tiempo1[2],$fecha1[1],$fecha1[2],$fecha1[0]);
				$t2=mktime($tiempo2[0],$tiempo2[1],$tiempo2[2],$fecha2[1],$fecha2[2],$fecha2[0]);
				$tiempo=$t2-$t1;
				$horas = intval($tiempo / 3600);
				$min = intval(($tiempo-$horas*3600)/60);
				$seg = $tiempo-$horas*3600-$min*60;
				if($horas<10) $horas="0".$horas;
				if($min<10) $min="0".$min;
				if($seg<10) $seg="0".$seg;
				$tiempo_final=$horas.':'.$min.':'.$seg;
				echo '<td align="center">'.$tiempo_final.'</td>';*/
				echo '<td align="center">'.$Entradas['ip'].'</td>';
				$i++;
				echo '</tr>';
			}
			
			echo '	
				<tr>
				<td colspan="5" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
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

if($_POST['ajax']==2){
	$rsnum=mysql_db_query($base,"SELECT * FROM parque WHERE plaza='".$_POST['plaza']."' AND no_eco='".$_POST['no_eco']."'");
	if($Num=mysql_fetch_array($rsnum))
		echo 'si';
	else
		echo 'no';
	exit();
}	

if($_POST['ajax']==3) {
		//Listado de Historial
		$select= " SELECT * FROM cambios_datos_parque WHERE cve_unidad='".$_POST['cve_unidad']."' and plaza='".$_POST['plaza']."'";
		$rscambios=mysql_db_query($base,$select);
		$totalRegistros = mysql_num_rows($rscambios);
		if($totalRegistros / $eRegistrosPagina > 1) 
		{
			$eTotalPaginas = $totalRegistros / $eRegistrosPagina;
			if(is_int($eTotalPaginas))
			{$eTotalPaginas--;}
			else
			{$eTotalPaginas = floor($eTotalPaginas);}
		}
		$select .= " ORDER BY fecha desc  LIMIT ".$primerRegistro.",".$eRegistrosPagina;
		$rscambios=mysql_db_query($base,$select);
		
		if(mysql_num_rows($rscambios)>0) 
		{
		
			echo '<h3 align="center"> Historial de Cambios </h3>';
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Folio</th>';
			echo '<th>Dato</th><th>Valor Nuevo</th><th>Valor Anterior</th><th>Fecha</th><th>Usuario</th>';
			echo '</tr>';
			$i=0;
			while($Cambios=mysql_fetch_array($rscambios)) {
				rowb();
		//		echo '<td align="center" width="40" nowrap><a href="#" onClick="document.forma.regcve_unidad.value=\''.$Cambios['cve_unidad'].'\';document.forma.regplaza.value=\''.$Cambios['plaza'].'\';atcr(\'parque.php\',\'\',\'1\','.$Cambios['cve'].')">'.$Cambios['folio'].'</a></td>';
				echo '<td align="center">'.$Cambios['folio'].'</td>';
				echo '<td align="left">'.htmlentities($Cambios['dato']).'</td>';
				if($Cambios['dato']=="Estatus"){
					echo '<td align="left">'.$array_estatus_parque[$Cambios['valor_nuevo']].'</td>';
					echo '<td align="left">'.$array_estatus_parque[$Cambios['valor_anterior']].'</td>';
				}else{
					if($Cambios['dato']=="Tipo de Vehiculo"){
						echo '<td align="left">'.$array_tipo_vehiculo[$Cambios['valor_nuevo']].'</td>';
						echo '<td align="left">'.$array_tipo_vehiculo[$Cambios['valor_anterior']].'</td>';
					}else{
						if($Cambios['dato']=="Tipo Placa"){
							echo '<td align="left">'.$array_tipo_placa[$Cambios['valor_nuevo']].'</td>';
							echo '<td align="left">'.$array_tipo_placa[$Cambios['valor_anterior']].'</td>';
						}else{
							echo '<td align="left">'.$Cambios['valor_nuevo'].'</td>';
							echo '<td align="left">'.$Cambios['valor_anterior'].'</td>';
						}
					}
				}	
				echo '<td align="center">'.$Cambios['fecha'].'</td>';
				echo '<td align="left">'.$array_usuario[$Cambios['usuario']].'';
				$i++;
				echo '</tr>';
			}
			
			echo '	
				<tr>
				<td colspan="9" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
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
		echo '<style>
		#Cambios {
			width: 70%;
			border-style: solid;
			border-width: 1px;
			border-color: #96BDE0;
		}
		</style>';
		
		$select=" SELECT * FROM parque WHERE plaza='".$_POST['plaza']."' AND cve='".$_POST['reg']."' ";
		$rsparque=mysql_db_query($base,$select);
		$Parque=mysql_fetch_array($rsparque);
		if($_POST['reg']>0){
			$Encabezado = 'No. Economico.'.$_POST['reg'];
			$noeco=$Parque['no_eco'];
		}
		else{
			$Encabezado = 'Nuevo Catálogo de Unidades';
		}
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if($_SESSION[$archivo[(count($archivo)-1)]]>1){
				echo '<td><a href="#" onClick="
				if(document.forma.no_eco.value==\'\')
					alert(\'Necesita ingresar el no. economico\');
				else if(document.forma.fecha_ini.value==\'\')
					alert(\'Necesita ingresar la fecha de ingreso\');
				else if(document.forma.plaza.value==\'0\')
					alert(\'Necesita seleccionar la plaza\');
				else 
					validar_no_eco(\''.$Parque['cve'].'\');"
				><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			
			}
		
			echo '<td><a href="#" onClick="atcr(\'parque.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Catálogo de Unidades</td></tr>';
		echo '</table>';
		
		echo '<table>';
	//	echo '<tr><th align="left">'.$Encabezado.'</th><td>&nbsp;</td></tr>';
		echo '<tr><th align="left">No. Económico</th><td><input type="text" name="no_eco" id="no_eco"  size="15" value="'.$Parque['no_eco'].'"';
		if($_POST['reg']>0) echo ' class="readOnly" readonly>';
		else echo 'class="textField">';
		echo '</td></tr>';
		echo '<tr><th align="left" valign="top">Propietario</th><td><input type="text" name="propietario" id="propietario" class="textField" size="50" value="'.$Parque['propietario'].'">  <input type="button" name="mostrar" onclick="mostrar_ocultar()" value="Mostrar">';
	
		echo '<div id="datos_propietario" style="visibility:hidden; position:absolute;" >';
		echo '<table>';
		echo '<tr><th align=left">Direccion</th><td><input type="text" name="dir_propietario" id="dir_propietario" class="textField" size="50" value="'.$Parque['dir_propietario'].'"></td></tr>';
		echo '<tr><th align=left">Teléfono</th><td><input type="text" name="tel_propietario" id="tel_propietario" class="textField" size="15" value="'.$Parque['tel_propietario'].'"></td></tr>';
		echo '<tr><th align=left">RFC</th><td><input type="text" name="rfc_propietario" id="rfc_propietario" class="textField" size="15" value="'.$Parque['rfc_propietario'].'"></td></th>';
		echo '</table>';	
		
		echo '</div></td></tr>';
	
		echo '<tr><th align="left">Tipo de Dueño</th><td><input type="radio" name="tipo_propietario" id="tipo_propietario" value="Socio"';
		if($Parque['tipo_propietario']=="Socio") echo 'checked';
		echo '>Socio&nbsp;&nbsp;<input type="radio" name="tipo_propietario" id="tipo_propietario" value="Usufructuario"';
		if($Parque['tipo_propietario']=="Usufructuario") echo 'checked';
		echo ' >Usufructuario</td></tr>';
		if($Parque['fecha_ini']=="0000-00-00") $Parque['fecha_ini']="";
		echo '<tr><th align="left">Fecha Ingreso</th><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" value="'.$Parque['fecha_ini'].'" class="readOnly" readonly>';
		if($_POST['reg']==0 || $Parque['fecha_ini']=="") echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
		echo '</td></tr>';
		if($_POST['reg']>0) { 
			echo '<tr><th align="left">Estatus</th><td><select name="estatus" id="estatus" class="textField">';
			foreach($array_estatus_parque as $k=>$v){
				echo '<option value="'.$k.'"';
				if($k==$Parque['estatus']) echo ' selected';
				echo '>'.$v.'</option>';
			}
		
			if($Parque['fecha_sta']=="0000-00-00") $Parque['fecha_sta']="";		
			echo '<tr><th align="left">Fecha Cambio Estatus</th><td><input type="text" name="fecha_sta" id="fecha_sta" class="readOnly" size="15" value="'.$Parque['fecha_sta'].'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_sta,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		}
		echo '<tr><th align="left">Tipo Vehiculo</th><td><select name="tipo_vehiculo" id="tipo_vehiculo" class="textField"><option value="all">---Seleccione un tipo de vehiculo---</option>';
		foreach($array_tipo_vehiculo as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$Parque['tipo_vehiculo']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '<tr><th align="left">Marca</th><td><input type="text" name="marca" id="marca" class="textField" size="50" value="'.$Parque['marca'].'" ></td></tr>';
		echo '<tr><th align="left">Modelo</th><td><input type="text" name="modelo" id="modelo" class="textField" size="50" value="'.$Parque['modelo'].'"></td></tr>';
		echo '<tr><th align="left">Motor</th><td><input type="text" name="motor" id="motor" class="textField" size="50" value="'.$Parque['motor'].'"></td></tr>';
		echo '<tr><th align="left">Serie</th><td><input type="text" name="serie" id="serie" class="textField" size="50" value="'.$Parque['serie'].'"></td></tr>';
		echo '<tr><th align="left">Concesión</th><td><input type="text" name="concesion" id="concesion" class="textField" size="50" value="'.$Parque['concesion'].'"></td></tr>';
		echo '<tr><th align="left">Unidad Asegurada</th><td><input type="radio" name="asegurada" id="asegurada" value="SI"';
		if($Parque['asegurada']!="NO") echo ' checked';
		echo '>Si&nbsp;&nbsp;<input type="radio" name="asegurada" id="asegurada" value="NO"';
		if($Parque['asegurada']=="NO") echo ' checked';
		echo ' >No</td></tr>';
		echo '<tr><th  align="left">Tipo Placa</th><td><select name="tipo_placa" id="tipo_placa" class="textField" onChange="habilita_placa(this.value)">';
		foreach($array_tipo_placa as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$Parque['tipo_placa']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '<tr><th align="left">Placa</th><td><input type="text" name="placa" id="placa" class="textField" size="50" value="'.$Parque['placa'].'"';
		if($Parque['tipo_placa']==0) echo ' disabled'; 
		echo '></td></tr>';
		if($_SESSION['PlazaUsuario']==0){
			echo '<tr><th align="left">Plaza</th><td><select name="plaza" id="plaza"><option value="0">---Seleccione una Plaza---</option>';
			$rsParque=mysql_db_query($base,"SELECT * FROM plazas ORDER BY nombre");
			while($Plaza=mysql_fetch_array($rsParque)){
				echo '<option value="'.$Plaza['cve'].'"';
				if($Parque['plaza']==$Plaza['cve']) echo ' selected';
				echo '>'.$Plaza['nombre'].'</option>';
			}
			echo '</select></td></tr>';
		}
		else{
			echo '<input type="hidden" name="plaza" id="plaza" value="'.$_SESSION['PlazaUsuario'].'">';
		}
		
		echo '</table>';
		echo '<br>';
		echo '<div id="Cambios">';
		echo '</div>';
		echo '<input type="hidden" name="regplaza" id="plaza" value="'.$_SESSION['PlazaUsuario'].'">';
		echo '<input type="hidden" name="regcve_unidad" id="cve_unidad" value="">';
		
		echo '<script language="javascript">
				function cambiosParque()
					{
						document.getElementById("Cambios").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
						objeto=crearObjeto();
						if (objeto.readyState != 0) {
							alert("Error: El Navegador no soporta AJAX");
						} else {
							objeto.open("POST","parque.php",true);
							objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
							objeto.send("ajax=3&cve_unidad='.$_POST['reg'].'&plaza="+document.getElementById("plaza").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
							objeto.onreadystatechange = function()
							{
								if (objeto.readyState==4)
									{document.getElementById("Cambios").innerHTML = objeto.responseText;}
							}
						}
						document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
					}
				function moverPagina(x) {
					document.getElementById("numeroPagina").value = x;
					cambiosParque();
				}	
				cambiosParque()
				
				function habilita_placa(valor){
					if(valor=="0"){
						document.getElementById("placa").disabled=true;
					}
					else{
						document.getElementById("placa").disabled=false;
					}
				}
				
				function validar_no_eco(reg){
					if(reg>0) {
						atcr("parque.php","",2,reg);
					} else {
						objeto=crearObjeto();
						if (objeto.readyState != 0) {
							alert("Error: El Navegador no soporta AJAX");
						} else {
							objeto.open("POST","parque.php",true);
							objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
							objeto.send("ajax=2&no_eco="+document.getElementById("no_eco").value+"&plaza="+document.getElementById("plaza").value);
							objeto.onreadystatechange = function(){
								if (objeto.readyState==4){
									if(objeto.responseText=="si")
										alert("El numero economico ya existe");
									else
										atcr("parque.php","",2,reg);
								}
							}
						}
					}
				}
				function mostrar_ocultar(){
					
					obj=document.getElementById("datos_propietario");
					if(obj.style.visibility=="hidden"){
						obj.style.visibility="visible";
						obj.style.position="relative";
					} else {
						obj.style.visibility="hidden";
						obj.style.position="absolute";
					}
					
				}
					
				
				function mostrar(nombreCapa){
					document.getElementById(nombreCapa).style.visibility="visible";
				}
				function ocultar(nombreCapa){
					document.getElementById(nombreCapa).style.visibility="hidden";
				} 
				
					
				
			  </script>'; 
			
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;<a href="#" onClick="atcr(\'registros_sistema.php\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		$fecha=date("Y-m-d");
		echo '<tr><th align="left">Fecha Inicial</th><td><input type="text" name="fecha" id="fecha" class="readOnly" size="15" value="'.$fecha.'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Fecha Final</th><td><input type="text" name="fechaf" id="fechaf" class="readOnly" size="15" value="'.$fecha.'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fechaf,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Usuario</th><td><select name="usuario" id="usuario"><option value="all">--- Todos ---</option>';
		$estatus=array("A"=>"Activo","I"=>"Inactivo");
		$res=mysql_db_query($base,"SELECT * FROM usuarios ORDER BY usuario");
		while($row=mysql_fetch_array($res)){
			echo '<option value="'.$row['cve'].'">'.$row['usuario'].' ('.$estatus[$row['estatus']].')</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
		echo '<script language="javascript">
			//Funcion para navegacion de Registros. 20 por pagina.
			function moverPagina(x) {
				document.getElementById("numeroPagina").value = x;
				buscarRegistros();
			} </script>';
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
			objeto.open("POST","registros_sistema.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha="+document.getElementById("fecha").value+"&fechaf="+document.getElementById("fechaf").value+"&usuario="+document.getElementById("usuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	';	
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

