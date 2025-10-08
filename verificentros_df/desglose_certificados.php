<?php
include("main.php");

//ARREGLOS

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsUsuario=mysql_query("SELECT a.* FROM plazas a INNER JOIN datosempresas b ON a.cve = b.plaza where a.estatus!='I' ORDER BY b.localidad_id, a.lista, a.numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}


$res=mysql_query("SELECT * FROM areas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_localidad[$row['cve']]=$row['nombre'];
}
$rsUsuario=mysql_query("SELECT * FROM tipo_combustible order by cve desc");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_tipo_combustible[$Usuario['cve']]=$Usuario['nombre'];
}
$rsUsuario=mysql_query("SELECT * FROM engomados where cve in(19,4,3,2,5,1)order by nombre");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_certificados[$Usuario['cve']]=$Usuario['nombre'];
}

$array_color=array('5 y 6'=>'FFFF00','7 y 8'=>'FF00FF', '3 y 4'=>'FF0000','1 y 2'=>'00FF00','0 y 9'=>'0000FF');

//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);

$abono=0;

function numeroPlaca($placa){
	$numero = '';
	for($i=0;$i<strlen($placa);$i++){
		if($placa[$i]>='0' && $placa[$i]<='9'){
			$numero = $placa[$i];
		}
	}
	return $numero;
}

if($_POST['ajax']==1){
	

		echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>Certificado</th>';
		foreach($array_tipo_combustible as $k=>$v){
			echo '<th>'.$v.'</th>';
		}
		echo '<th>Total</th></tr>';
		
		
		$t1=0;
		$t2=0;
		$t3=0;
		$t4=0;
		foreach($array_certificados as $k=>$v){
		$combus_gaso=0;
		$combus_gas=0;
		$combus_dis=0;
		$total=0;
		rowb();
		echo'<th>'.$v.'</th>';
			$re="SELECT * FROM certificados WHERE plaza IN ('".$_POST['plazausuario']."') AND fecha between '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."' AND estatus!='C' AND engomado='".$k."'";
			$res_1 = mysql_query($re);
			while($row1 = mysql_fetch_array($res_1)){
				$re_="SELECT * FROM cobro_engomado WHERE cve='".$row1['ticket']."' and plaza in ('".$_POST['plazausuario']."')";
				$res11 = mysql_query($re_);
				$row=mysql_fetch_array($res11);
				if($row['engomado']==6){$recha_00++;}
				if($row['engomado']==7){$recha_0++;}
				if($row['engomado']==8){$recha_1++;}
				if($row['engomado']==24){$recha_2++;}
				if($row['engomado']==10){$recha_int++;}
				if($row['tipo_combustible']==1){$combus_gaso++;}
				if($row['tipo_combustible']==2){$combus_gas++;}
				if($row['tipo_combustible']==3){$combus_dis++;}
				
			
			}
			$total=mysql_num_rows($res_1);
		echo'<td align="center">'.$combus_dis.'</td>';
		echo'<td align="center">'.$combus_gas.'</td>';
		echo'<td align="center">'.$combus_gaso.'</td>';
		echo'<td align="center">'.$total.'</td>';
		$t1=$t1 +$combus_dis;
		$t2=$t2 +$combus_gas;
		$t3=$t3 +$combus_gaso;
		$t4=$t4 +$total;
		echo'</tr>';
		}
		echo '<tr bgcolor="#E9F2F8"><th align="right">Total</th><th>'.$t1.'</th><th>'.$t2.'</th><th>'.$t3.'</th><th>'.$t4.'</th>';
		echo '</tr>';
		echo '</table>';
		
		
	
	/*echo'*|*';
	$recha_00=0;
	$recha_0=0;
	$recha_1=0;
	$recha_2=0;
	$recha_int=0;
	$tot=0;
	$re="SELECT * FROM certificados WHERE plaza IN ('".$_POST['plaza']."') AND fecha between '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."' AND estatus!='C' AND engomado='9'";
	$res_1 = mysql_query($re);
		while($row1 = mysql_fetch_array($res_1)){
			$re_="SELECT * FROM cobro_engomado WHERE cve='".$row1['ticket']."' and plaza in ('".$_POST['plaza']."')";
			$res11 = mysql_query($re_);
			$row=mysql_fetch_array($res11);
			if($row['engomado']==6){$recha_00++;}
			if($row['engomado']==7){$recha_0++;}
			if($row['engomado']==8){$recha_1++;}
			if($row['engomado']==24){$recha_2++;}
			if($row['engomado']==10){$recha_int++;}
			}
			
	echo'<table border="1">
		 <tr><td align="center" colspan="">Engomado</td><td>Cantidad</td></tr>
		 <tr><td>Engomado 00</td><td align="center">'.$recha_00.'</td></tr>
		 <tr><td>Engomado 0</td><td align="center">'.$recha_0.'</td></tr>
		 <tr><td>Engomado 1</td><td align="center">'.$recha_1.'</td></tr>
		 <tr><td>Engomado 2</td><td align="center">'.$recha_2.'</td></tr>
		 <tr><td>Intento</td><td align="center">'.$recha_int.'</td></tr>';
		 $tot=$recha_0+$recha_00+$recha_1+$recha_2+$recha_int;
		 echo'<tr><td align="right">Total</td><td>'.$tot.'</td></tr>
		 </table>';*/
	
	
	exit();
}


top($_SESSION);
	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><tr></table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.date( "Y-m-d" , strtotime ( "-6 day" , strtotime(fechaLocal()) ) ).'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '</table>';
		echo '<br>';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
bottom();
echo '
<Script language="javascript">';

	echo 'function buscarRegistros(){
	';
echo '  document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","desglose_certificados.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					res=objeto.responseText;
					opc=res.split("*|*");
					document.getElementById("Resultados").innerHTML = opc[0];
					document.getElementById("Resultados_recha").innerHTML = opc[1];

				}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
		
	
	

	</Script>
';

?>