<?php
include ("main.php"); 

$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$array_denominaciones=array('1000','500','200','100','50','20','10','5','2','1','0.50','0.20','0.10','0.05');

if($_POST['ajax']==1){
	$select= " SELECT * FROM desglose_dinero WHERE plaza='".$_POST['plazausuario']."' AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	if ($_POST['usuario']!="") { $select.=" AND usuario='".$_POST['usuario']."' "; }
	$select.=" ORDER BY cve DESC";
	$res=mysql_query($select);
	$totalRegistros = mysql_num_rows($res);
	
	
	if(mysql_num_rows($res)>0) 
	{
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th>
		<th>Usuario</th><th>Monto</th>';
		echo '</tr>';
		$t=0;
		$c=0;
		while($row=mysql_fetch_array($res)) {
			//$c++;
			//mysql_query("UPDATE desglose_dinero SET folio=$c WHERE plaza='".$row['plaza']."' AND cve='".$row['cve']."'");
			rowb();
			echo '<td align="center" width="40" nowrap>';
			if($row['estatus']=='C'){
				echo 'Cancelado';
				$row['monto']=0;
			}
			else{
				echo '<a href="#" onClick="atcr(\'desglose_dinero.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
				if(nivelUsuario()>1)
					echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el desglose\')) atcr(\'desglose_dinero.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
			}	
			echo '</td>';
			echo '<td align="center">'.htmlentities($row['folio']).'</td>';
			echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
			echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
			echo '<td align="center">'.number_format($row['monto'],2).'</td>';
			echo '</tr>';
			$t+=$row['monto'];
		}
		echo '	
			<tr>
			<td colspan="4" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
			<td align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</td>
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

if($_POST['cmd']==101){
	require_once("numlet.php");
	$res=mysql_query("SELECT * FROM desglose_dinero WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	$texto=chr(27)."@";
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." ".$array_plaza[$row['plaza']];
	$texto.='||';
	$texto.=chr(27).'!'.chr(8)." FOLIO: ".$row['cve'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." DESGLOSE DE DINERO";
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." FECHA: ".$row['fecha']."   ".$row['hora'].'|';
	$texto.=chr(27).'!'.chr(8)." USUARIO: ".$array_usuario[$row['usuario']];
	$texto.='|';
	$res1=mysql_query("SELECT * FROM desglose_dineromov WHERE plaza='".$_POST['plazausuario']."' AND desglose='".$_POST['reg']."'");
	while($row1=mysql_fetch_array($res1)){
		$texto.=chr(27).'!'.chr(8)." ".sprintf("%-5s",$row1['denominacion'])." C: ".sprintf("%-3s",$row1['cantidad'])." I: ".sprintf("%10s",$row1['importe']);
		$texto.='|';	
	}
	$texto.=chr(27).'!'.chr(8)."| TOTAL: ".$row['monto'];
	$texto.='|| ADMINISTRADOR||';
	$texto.='___________________________|';
	$res1=mysql_query("SELECT * FROM administradores WHERE plaza='".$_POST['plazausuario']."' AND fecha_ini<='".$row['fecha']."' AND (fecha_fin>='".$row['fecha']."' OR fecha_fin='0000-00-00') LIMIT 1");
	$row1=mysql_fetch_array($res1);
	$texto.=' '.$row1['nombre'].'|| USUARIO||';
	$texto.='___________________________|';
	$texto.=' '.$array_usuario[$row['usuario']].'|';
	
	$impresion='<iframe src="http://localhost/impresiongeneral.php?textoimp='.$texto.'" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",2000);</script>';
	exit();
}

top($_SESSION);

if($_POST['cmd']==3){
	mysql_query("UPDATE desglose_dinero SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

if ($_POST['cmd']==2) {
	$res=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM desglose_dinero WHERE plaza='".$_POST['plazausuario']."' AND fecha='".fechaLocal()."'");
	$row=mysql_fetch_array($res);
	$folio=$row[0];
	$insert = " INSERT desglose_dinero 
					SET 
					plaza = '".$_POST['plazausuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',
					monto='".$_POST['monto']."',usuario='".$_POST['cveusuario']."',estatus='A',folio='$folio'";
	mysql_query($insert);
	$cvedesglose = mysql_insert_id();
	foreach($_POST['denominacion'] as $k=>$v){
		mysql_query("INSERT desglose_dineromov SET plaza='".$_POST['plazausuario']."',desglose='$cvedesglose',
		denominacion='$v',cantidad='".$_POST['cantidad'][$k]."',importe='".$_POST['importe'][$k]."'");
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==1){
	if ($_POST['cmd']==1) {
		
				
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();validar();"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'desglose_dinero.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Desglose de Dinero</td></tr>';
		echo '</table>';
		
		echo '<table><tr><th>&nbsp;</th><th>Cantidad</th><th>Importe</th></tr>';
		foreach($array_denominaciones as $k=>$denominacion){
			echo '<tr><th align="left">'.$denominacion.'<input type="hidden" name="denominacion[]" value="'.$denominacion.'"></th>
			<td><input type="text" name="cantidad[]" id="cantidad_'.$k.'" class="textField" style="font-size:20px" size="10" value="" onKeyUp="document.getElementById(\'importe_'.$k.'\').value = this.value*'.$denominacion.'; sumar();"></td>
			<td><input type="text" name="importe[]" id="importe_'.$k.'" class="readOnly suma" style="font-size:20px" size="10" value="" readOnly></td></tr>';
		}
		echo '<tr><th align="right" colspan="2">Monto</th><td><input type="text" name="monto" id="monto" class="readOnly" size="10" style="font-size:20px" value="" readOnly></td></tr>';
		
		echo '</table>';
		
		echo '<script>
				function validar(){
					if((document.forma.monto.value/1)==0){
						$("#panel").hide();
						alert("El total no puede ser cero");
					}
					else{
						atcr("desglose_dinero.php","",2,0);
					}
				}
				
				function sumar(){
					total = 0;
					$(".suma").each(function(){
						total += (this.value/1);
					});
					document.forma.monto.value = total.toFixed(2);
				}	
				
			</script>';
		
	}
}

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {

	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'desglose_dinero.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">Todos</option>';
	$res=mysql_query("SELECT b.cve,b.usuario FROM desglose_dinero a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY b.cve ORDER BY b.usuario");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['usuario'].'</option>';
	}
	echo '</select></td></tr>';
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
			objeto.open("POST","desglose_dinero.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
	buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
		
	
	
	</Script>
	';

	
}
	
bottom();

if($cvedesglose>0){
	echo '<script>atcr(\'desglose_dinero.php\',\'_blank\',\'101\','.$cvedesglose.');</script>';
}
?>