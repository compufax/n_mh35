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
	$array_engomadoprecio[$row['cve']]=$row['precio'];
}

$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}


$array_estatus = array('A'=>'Activo','C'=>'Cancelado');
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM compra_certificados 
		WHERE plaza='".$_POST['plazausuario']."'";
		$select.=" AND fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		if ($_POST['usuario']!="") { $select.=" AND usuario='".$_POST['usuario']."' "; }
		if ($_POST['engomado']!="") { $select.=" AND engomado='".$_POST['engomado']."' "; }
		if ($_POST['estatus']!="") { $select.=" AND estatus='".$_POST['estatus']."' "; }
		$select.=" ORDER BY cve DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Folio</th><th>Fecha</th><th>Tipo de Certificado</th><th>Folio Inicial</th><th>Folio Final</th><th>Cantidad</th><th>Usuario</th>';
			echo '</tr>';
			$t=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado';
					$cantidad=0;
				}
				else{
					//echo '<a href="#" onClick="atcr(\'cobro_engomado.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					$cantidad=$row['foliofin']+1-$row['folioini'];
					$puede_cancelar = 0;
					$res1=mysql_query("SELECT cve FROM certificados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."'");
					$res2=mysql_query("SELECT cve FROM certificados_cancelados WHERE plaza='".$row['plaza']."' AND engomado='".$row['engomado']."' AND (certificado/1) BETWEEN '".$row['folioini']."' AND '".$row['foliofin']."'");
					if(mysql_num_rows($res1)==0 && mysql_num_rows($res2)==0) $puede_cancelar = 1;
					if(nivelUsuario()>1 && $puede_cancelar == 1)
						echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar la compra\')) atcr(\'compra_certificados.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				}	
				echo '</td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
				echo '<td align="center">'.htmlentities($array_engomado[$row['engomado']]).'</td>';
				echo '<td align="center">'.$row['folioini'].'</td>';
				echo '<td align="center">'.$row['foliofin'].'</td>';
				echo '<td align="center">'.$cantidad.'</td>';
				echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
				echo '</tr>';
				$t+=$cantidad;
			}
			echo '	
				<tr>
				<td colspan="6" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</td>
				<td bgcolor="#E9F2F8">&nbsp;</td>
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

/*
if($_POST['cmd']==101){
	require_once("numlet.php");
	$res=mysql_query("SELECT * FROM cobro_engomado WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	$texto=chr(27)."@";
	$texto.='|';
	$resPlaza = mysql_query("SELECT nombre FROM plazas WHERE cve='".$row['plaza']."'");
	$rowPlaza = mysql_fetch_array($resPlaza);
	$resPlaza2 = mysql_query("SELECT rfc FROM datosempresas WHERE plaza='".$row['plaza']."'");
	$rowPlaza2 = mysql_fetch_array($resPlaza2);
	$texto.=chr(27).'!'.chr(30)." ".$array_plaza[$row['plaza']]."|".$rowPlaza['nombre'];
	$texto.='| RFC: '.$rowPlaza2['rfc'];
	$texto.='||';
	$texto.=chr(27).'!'.chr(8)." TICKET: ".$row['cve'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." VENTA DE CERTIFICADO";
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." FECHA: ".$row['fecha']."   ".$row['hora'].'|';
	$texto.=chr(27).'!'.chr(40)."PLACA: ".$row['placa'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." T. CERTIFICADO: ".$array_engomado[$row['engomado']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." MODELO: ".$row['modelo'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." TIPO PAGO ".$array_tipo_pago[$row['tipo_pago']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." MONTO: ".$row['monto'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(8)." ".numlet($row['monto']);
	$texto.='|';
	
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?textoimp='.$texto.'&logo='.str_replace(' ','',$array_plaza[$row['plaza']]).'&barcode=1'.sprintf("%011s",(intval($row['cve']))).'&copia=1" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",2000);</script>';
	exit();
}*/

if($_POST['ajax']==3){
	$res = mysql_query("SELECT * FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$_POST['engomado']."' AND ((folioini BETWEEN '".$_POST['folioini']."' AND '".$_POST['foliofin']."') OR (foliofin BETWEEN '".$_POST['folioini']."' AND '".$_POST['foliofin']."')) AND estatus!='C' ORDER BY cve DESC LIMIT 1");
	if(mysql_num_rows($res)>0){
		echo "1";
	}
	exit();
}

top($_SESSION);

if($_POST['cmd']==13){
	mysql_query("UPDATE plazas SET validar_certificado=0, cambios_validar_certificado = CONCAT(cambios_validar_certificado,'|0,".$_POST['cveusuario'].",".fechaLocal()." ".horaLocal()."') WHERE cve = '".$_POST['plazausuario']."'") or die(mysql_error());
	$ValidarCertificados = 0;
	$_POST['cmd']=0;
}

if($_POST['cmd']==12){
	mysql_query("UPDATE plazas SET validar_certificado=1, cambios_validar_certificado = CONCAT(cambios_validar_certificado,'|1,".$_POST['cveusuario'].",".fechaLocal()." ".horaLocal()."') WHERE cve = '".$_POST['plazausuario']."'");
	$ValidarCertificados = 1;
	$_POST['cmd']=0;
}

if($_POST['cmd']==3){
	mysql_query("UPDATE compra_certificados SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
	mysql_query("UPDATE compra_certificados_detalle SET estatus=3 WHERE plaza='".$_POST['plazausuario']."' AND cvecompra='".$_POST['reg']."' AND estatus=0");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	
		$res = mysql_query("SELECT * FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND engomado='".$_POST['engomado']."' AND ((folioini BETWEEN '".$_POST['folioini']."' AND '".$_POST['foliofin']."') OR (foliofin BETWEEN '".$_POST['folioini']."' AND '".$_POST['foliofin']."')) AND estatus!='C' ORDER BY cve DESC LIMIT 1");
		if(mysql_num_rows($res)==0){
			
			$insert = " INSERT compra_certificados 
							SET 
							plaza = '".$_POST['plazausuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',
							engomado='".$_POST['engomado']."',folioini='".$_POST['folioini']."',
							foliofin='".$_POST['foliofin']."',usuario='".$_POST['cveusuario']."',estatus='A'";
			mysql_query($insert);
			$cvecompra = mysql_insert_id();
			for($i=$_POST['folioini'];$i<=$_POST['foliofin'];$i++){
				mysql_query("INSERT compra_certificados_detalle SET plaza='".$_POST['plazausuario']."',cvecompra='$cvecompra',folio='$i',tipo=0");
			}
			foreach($_POST['faltante'] as $valor){
				if($valor>0){
					mysql_query("UPDATE compra_certificados_detalle SET tipo=1,estatus=2 WHERE plaza='".$_POST['plazausuario']."' AND cvecompra='$cvecompra' AND folio='$valor'");
				}
			}
		}
	$_POST['cmd']=0;
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$res = mysql_query("SELECT * FROM compra_certificados WHERE plaza='".$_POST['plazausuario']."' AND cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td><a href="#" onClick="$(\'#panel\').show();validar('.$_POST['reg'].');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'compra_certificados.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Compra de Certificados</td></tr>';
		echo '</table>';
		echo '<table style="font-size:15px">';
		echo '<tr><th align="left">Tipo de Certificado</th><td><input type="hidden" name="engomado" id="engomado" value="'.intval($row['engomado']).'"><table><tr>';
		$i=0;
		foreach($array_engomado as $k=>$v){
			if($i==4){
				echo '</tr><tr>';
				$i=0;
			}
			echo '<td><input type="radio" name="auxengomado" id="auxengomado_'.$k.'" value="'.$k.'" onClick="if(this.checked){document.forma.engomado.value=this.value; }"';
			if($row['engomado']==$k) echo ' checked';
			echo '>'.$v.'&nbsp;&nbsp;&nbsp;</td>';
			$i++;
		}
		echo '</tr></table></td></tr>';
		echo '<tr><th align="left">Folio Inicial</th><td><input type="text" name="folioini" id="folioini" class="textField enteros" size="30" style="font-size:12px" value="'.$row['folioini'].'" onKeyUp="calcular()"></td></tr>';
		echo '<tr><th align="left">Folio Final</th><td><input type="text" name="foliofin" id="foliofin" class="textField enteros" size="30" style="font-size:12px" value="'.$row['foliofin'].'" onKeyUp="calcular()"></td></tr>';
		echo '<tr><th align="left">Cantidad</th><td><input type="text" name="cantidad" id="cantidad" class="readOnly enteros" size="15" style="font-size:12px" value="'.$row['cantidad'].'" readOnly></td></tr>';
		echo '<tr><th align="left">Folios Faltantes<br><span style="cursor:pointer;" onClick="agregar_faltante()"><font color="BLUE">Agregar</font></span></th>
		<td><table id="faltantes"><tr><td><input type="text" class="textField cfaltantes" style="font-size:12px" onKeyUp="validar_faltante(this)" size="30" name="faltante[]"></td></tr></table></td></tr>';
		echo '<tr><th align="left">Cantidad Faltantes</th><td><input type="text" name="cantidadf" id="cantidadf" class="readOnly enteros" size="15" style="font-size:12px" value="" readOnly></td></tr>';
		echo '</table>';
		
		echo '<script>
				function agregar_faltante(){
					$("#faltantes").append(\'<tr><td><input type="text" class="textField cfaltantes" style="font-size:12px" onKeyUp="validar_faltante(this)" size="30" name="faltante[]"></td></tr>\');
				}
				
				function validar_faltante(campo){
					/*if((campo.value/1) > 0 && (campo.value/1)<(document.forma.folioini.value/1))
						campo.value = document.forma.folioini.value;
					else if((campo.value/1) > 0 && (campo.value/1)>(document.forma.foliofin.value/1))
						campo.value = document.forma.foliofin.value;*/
						
					var cantf=0;
					$(".cfaltantes").each(function(){
						if((this.value/1) > 0 && (this.value/1)>=(document.forma.folioini.value/1) && (this.value/1)<=(document.forma.foliofin.value/1))
							cantf++;
					});
					document.forma.cantidadf.value=cantf;
				}
				
				function validar(reg){
					if(document.forma.engomado.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar el tipo de certificado");
					}
					else if((document.forma.cantidad.value/1)==0){
						$("#panel").hide();
						alert("La cantidad no puede ser cero");
					}
					else if(validarFolios()==false){
						$("#panel").hide();
						alert("Error en los folios chocan con folios ya comprados");
					}
					else{
						atcr("compra_certificados.php","",2,reg);
					}
				}
				
				function calcular(){
					if((document.forma.folioini.value/1)>0 && (document.forma.foliofin.value/1)>0 && (document.forma.foliofin.value/1)>=(document.forma.folioini.value/1)){
						document.forma.cantidad.value=1+(document.forma.foliofin.value/1)-(document.forma.folioini.value/1);
					}
					else{
						document.forma.cantidad.value=0;
					}
				}
				
				function validarFolios(){
					var regresar = true;
					$.ajax({
					  url: "compra_certificados.php",
					  type: "POST",
					  async: false,
					  data: {
						engomado: document.getElementById("engomado").value,
						plazausuario: document.forma.plazausuario.value,
						folioini: document.forma.folioini.value,
						foliofin: document.forma.foliofin.value,
						ajax: 3
					  },
						success: function(data) {
							if(data == "1"){
								regresar = false;
							}
						}
					});
					return regresar;
				}
				
			</script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

if ($_POST['cmd']<1) {
	
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(1);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
	echo '<td><a href="#" onClick="atcr(\'compra_certificados.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>';
	if(nivelUsuario()>2){
		if($ValidarCertificados==1)
			echo '<td><input type="checkbox" checked onClick="atcr(\'compra_certificados.php\',\'\',13,0)">Validacion de Certificados</td></tr>';
		else
			echo '<td><input type="checkbox" onClick="atcr(\'compra_certificados.php\',\'\',12,0)">Validacion de Certificados</td></tr>';
	}
	echo '
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Tipo de Certificado</td><td><select name="engomado" id="engomado"><option value="">Todos</option>';
	foreach($array_engomado as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">Todos</option>';
	$res=mysql_query("SELECT b.cve,b.usuario FROM compra_certificados a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY b.usuario");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['usuario'].'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="">Todos</option>';
	foreach($array_estatus as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
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

	function buscarRegistros(btn)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","compra_certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&btn="+btn+"&estatus="+document.getElementById("estatus").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&engomado="+document.getElementById("engomado").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
	buscarRegistros(0); //Realizar consulta de todos los registros al iniciar la forma.
		
	
	</Script>
	';

	
}
	
bottom();

if($cvecobro>0){
		echo '<script>atcr(\'cobro_engomado.php\',\'_blank\',\'101\','.$cvecobro.');</script>';
	}
?>

