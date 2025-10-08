<?php
include("main.php");

//ARREGLOS


function mestexto($fec){
	global $array_meses;
	$datos=explode("-",$fec);
	return $array_meses[intval($datos[1])].' '.$datos[0];
}
//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$resempresa = mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plazausuario']."'");
$rowempresa = mysql_fetch_array($resempresa);

$rsUsuario=mysql_query("SELECT a.cve,a.numero,a.nombre FROM plazas a inner join datosempresas b on a.cve = b.plaza where a.estatus!='I' and b.localidad_id ORDER BY b.localidad_id, a.lista, a.numero");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_plazas[$Usuario['cve']]=$Usuario['numero'].' '.$Usuario['nombre'];
}
$rechazos = "9,19";
$abono=0;

if($_POST['cmd']==100){
	$filtroplazas = "";
	if($_POST['localidad']==2) $filtroplazas = " a.plaza IN (".$_POST['plaza'].") AND ";
	if($_POST['mostrar']==1){
		$select="SELECT MONTH(a.fecha),SUM(IF(a.engomado NOT IN ($rechazos),1,0)) FROM certificados a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY MONTH(a.fecha)";
		$res = mysql_query($select);
		$array_montos = array();
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][0]=$row[1];
		}
		$select="SELECT MONTH(a.fecha),SUM(a.monto),COUNT(a.cve) FROM cobro_engomado a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY MONTH(a.fecha)";
		$res = mysql_query($select);
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][1]=$row[1];
		}
		$select="SELECT MONTH(a.fecha),SUM(a.devolucion),COUNT(a.cve) FROM devolucion_certificado a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY MONTH(a.fecha)";
		$res = mysql_query($select);
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][2]=$row[1];
		}
		$select="SELECT MONTH(a.fecha),SUM(a.monto),COUNT(a.cve) FROM pagos_caja a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY MONTH(a.fecha)";
		$res = mysql_query($select);
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][1]+=$row[1];
		}
		$select="SELECT MONTH(a.fecha),SUM(a.recuperacion),COUNT(a.cve) FROM recuperacion_certificado a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY MONTH(a.fecha)";
		$res = mysql_query($select);
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][1]+=$row[2];
		}
		echo '<h1>A&ntilde;o: '.$_POST['anio'].'</h1>';
		$total=array(0,0);
		echo '<table style="font-size:20px" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>Mes</th><th>Aforo</th><th>Total Venta</th><th>Devoluciones</th><th>Gran Total</th></tr>'; 
		foreach($array_meses as $k=>$v){
			if($k>0){
				rowb();
				echo '<td>'.$v.'</td>';
				echo '<td align="right">'.number_format($array_montos[$k][0],0).'</td>';
				echo '<td align="right">'.number_format($array_montos[$k][1],2).'</td>';
				echo '<td align="right">'.number_format($array_montos[$k][2],2).'</td>';
				echo '<td align="right">'.number_format($array_montos[$k][1]-$array_montos[$k][2],2).'</td>';
				echo '</tr>';
				$total[0]+=round($array_montos[$k][0],2);
				$total[1]+=round($array_montos[$k][1],0);
				$total[2]+=round($array_montos[$k][2],0);
			}
		}
		echo '<tr bgcolor="#E9F2F8"><th align="right">Totales:&nbsp;</th><th align="right">'.number_format($total[1],0).'</th><th align="right">'.number_format($total[0],2).'</th><th align="right">'.number_format($total[2],2).'</th><th align="right">'.number_format($total[0]-$total[2],2).'</th></tr>'; 
		echo '</table>';
	}
	else{
		$select="SELECT a.fecha,SUM(a.monto),COUNT(a.cve) FROM cobro_engomado a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND MONTH(a.fecha)='".$_POST['mes']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY a.fecha";
		$res = mysql_query($select);
		$array_montos = array();
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][0]=$row[1];
			$array_montos[$row[0]][3]=$row[1];
		}
		$select="SELECT a.fecha,SUM(IF(a.engomado NOT IN ($rechazos),1,0)) FROM certificados a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND MONTH(a.fecha)='".$_POST['mes']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY a.fecha";
		$res = mysql_query($select);
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][1]=$row[1];
		}
		$select="SELECT a.fecha,SUM(a.devolucion),COUNT(a.cve) FROM devolucion_certificado a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND MONTH(a.fecha)='".$_POST['mes']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY a.fecha";
		$res = mysql_query($select);
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][2]=$row[1];
			$array_montos[$row[0]][3]-=$row[1];
		}
		$select="SELECT a.fecha,SUM(a.monto),COUNT(a.cve) FROM pagos_caja a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND MONTH(a.fecha)='".$_POST['mes']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY a.fecha";
		$res = mysql_query($select);
		$array_montos = array();
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][0]+=$row[1];
			$array_montos[$row[0]][3]+=$row[1];
		}
		$select="SELECT a.fecha,SUM(a.recuperacion),COUNT(a.cve) FROM recuperacion_certificado a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND MONTH(a.fecha)='".$_POST['mes']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY a.fecha";
		$res = mysql_query($select);
		$array_montos = array();
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][0]+=$row[1];
			$array_montos[$row[0]][3]+=$row[1];
		}
		$fecha=$_POST['anio'].'-'.sprintf("%02s",$_POST['mes']);
		echo '<h1>Mes: '.$array_meses[intval($_POST['mes'])].' '.$_POST['anio'].'</h1>';
		echo '<table style="font-size:15px" width="100%" border="1">
			<tr bgcolor="#E9F2F8"><th>Domingo</th><th>Lunes</th><th>Martes</th><th>Miercoles</th><th>Jueves</th><th>Viernes</th><th>Sabado</th></tr>';
		$sumren=0;
		$arfecha=explode("-",$fecha);
		$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval(1), $arfecha[0]));
		for($i=0;$i<$dia;$i++){
			if($i==0) echo '<tr>';
			echo '<td align="center"><br><br>&nbsp;</td>';
		}
		$fec=$fecha.'-01';
		$fecultima=date( "Y-m-t" , strtotime ( "+ 1 day" , strtotime($fec) ) );
		for($i=$dia;$fec<=$fecultima;$i++){
			if($i==7){
				echo '</tr>';
				$i=0;
			}
			if($i==0){
				echo '<tr>';
			}
			
			echo '<td align="center" valign="center">'.substr($fec,8,2).'<br>Aforo: '.number_format($array_montos[$fec][1],0).'
			<br>Total Venta: '.number_format($array_montos[$fec][0],2).'<br>Devolucion: '.number_format($array_montos[$fec][2],2).'<br>
			Gran Total: '.number_format($array_montos[$fec][3],2).'</td>';
			$fec=date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fec) ) );
		}
		$arfecha=explode("-",$fecultima);
		$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
		for($i=$dia;$i<6;$i++){
			echo '<td align="center"><br><br>&nbsp;</td>';
		}
		echo '</tr>';
		echo '</table>';
	}
	exit();
}

if($_POST['ajax']==1){
	$filtroplazas = "";
	if($_POST['localidad']==2) $filtroplazas = " a.plaza IN (".$_POST['plaza'].") AND ";

	if($_POST['mostrar']==1){
		$select="SELECT MONTH(a.fecha),SUM(IF(a.engomado NOT IN ($rechazos),1,0)) FROM certificados a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY MONTH(a.fecha)";
		$res = mysql_query($select);
		$array_montos = array();
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][0]=$row[1];
		}
		$select="SELECT MONTH(a.fecha),SUM(a.monto),COUNT(a.cve) FROM cobro_engomado a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY MONTH(a.fecha)";
		$res = mysql_query($select);
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][1]=$row[2];
		}
		$select="SELECT MONTH(a.fecha),SUM(a.devolucion),COUNT(a.cve) FROM devolucion_certificado a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY MONTH(a.fecha)";
		$res = mysql_query($select);
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][2]=$row[1];
		}
		$select="SELECT MONTH(a.fecha),SUM(a.monto),COUNT(a.cve) FROM pagos_caja a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY MONTH(a.fecha)";
		$res = mysql_query($select);
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][1]+=$row[2];
		}
		$select="SELECT MONTH(a.fecha),SUM(a.recuperacion),COUNT(a.cve) FROM recuperacion_certificado a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY MONTH(a.fecha)";
		$res = mysql_query($select);
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][1]+=$row[2];
		}
		echo '<h1>A&ntilde;o: '.$_POST['anio'].'</h1>';
		$total=array(0,0);
		echo '<table style="font-size:20px" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>Mes</th><th>Aforo</th><th>Total Venta</th><th>Devoluciones</th><th>Gran Total</th></tr>'; 
		foreach($array_meses as $k=>$v){
			if($k>0){
				rowb();
				echo '<td>'.$v.'</td>';
				echo '<td align="right">'.number_format($array_montos[$k][0],0).'</td>';
				echo '<td align="right">'.number_format($array_montos[$k][1],2).'</td>';
				echo '<td align="right">'.number_format($array_montos[$k][2],2).'</td>';
				echo '<td align="right">'.number_format($array_montos[$k][1]-$array_montos[$k][2],2).'</td>';
				echo '</tr>';
				$total[0]+=round($array_montos[$k][0],2);
				$total[1]+=round($array_montos[$k][1],0);
				$total[2]+=round($array_montos[$k][2],0);
			}
		}
		echo '<tr bgcolor="#E9F2F8"><th align="right">Totales:&nbsp;</th><th align="right">'.number_format($total[1],0).'</th><th align="right">'.number_format($total[0],2).'</th><th align="right">'.number_format($total[2],2).'</th><th align="right">'.number_format($total[0]-$total[2],2).'</th></tr>'; 
		echo '</table>';
	}
	else{
		$select="SELECT a.fecha,SUM(a.monto),COUNT(a.cve) FROM cobro_engomado a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND MONTH(a.fecha)='".$_POST['mes']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY a.fecha";
		$res = mysql_query($select);
		$array_montos = array();
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][0]=$row[1];
			$array_montos[$row[0]][3]=$row[1];
		}
		$select="SELECT a.fecha,SUM(IF(a.engomado NOT IN ($rechazos),1,0)) FROM certificados a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND MONTH(a.fecha)='".$_POST['mes']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY a.fecha";
		$res = mysql_query($select);
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][1]=$row[1];
		}
		$select="SELECT a.fecha,SUM(a.devolucion),COUNT(a.cve) FROM devolucion_certificado a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND MONTH(a.fecha)='".$_POST['mes']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY a.fecha";
		$res = mysql_query($select);
		//$array_montos = array();
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][2]=$row[1];
			$array_montos[$row[0]][3]-=$row[1];
		}
		$select="SELECT a.fecha,SUM(a.monto),COUNT(a.cve) FROM pagos_caja a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND MONTH(a.fecha)='".$_POST['mes']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY a.fecha";
		$res = mysql_query($select);
		$array_montos = array();
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][0]+=$row[1];
			$array_montos[$row[0]][3]+=$row[1];
		}
		$select="SELECT a.fecha,SUM(a.recuperacion),COUNT(a.cve) FROM recuperacion_certificado a INNER JOIN datosempresas b ON a.plaza=b.plaza WHERE $filtroplazas YEAR(a.fecha)='".$_POST['anio']."' AND MONTH(a.fecha)='".$_POST['mes']."' AND a.estatus!='C'";
		if($_POST['localidad']!=0) $select.=" AND b.localidad_id='".$_POST['localidad']."'";
		$select.=" GROUP BY a.fecha";
		$res = mysql_query($select);
		$array_montos = array();
		while($row=mysql_fetch_array($res)){
			$array_montos[$row[0]][0]+=$row[1];
			$array_montos[$row[0]][3]+=$row[1];
		}
		$fecha=$_POST['anio'].'-'.sprintf("%02s",$_POST['mes']);
		echo '<h1>Mes: '.$array_meses[intval($_POST['mes'])].' '.$_POST['anio'].'</h1>';
		echo '<table style="font-size:10px" width="100%" border="1">
			<tr bgcolor="#E9F2F8"><th>Domingo</th><th>Lunes</th><th>Martes</th><th>Miercoles</th><th>Jueves</th><th>Viernes</th><th>Sabado</th></tr>';
		$sumren=0;
		$arfecha=explode("-",$fecha);
		$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval(1), $arfecha[0]));
		for($i=0;$i<$dia;$i++){
			if($i==0) echo '<tr>';
			echo '<td align="center"><br><br>&nbsp;</td>';
		}
		$fec=$fecha.'-01';
		$fecultima=date( "Y-m-t" , strtotime ( "+ 1 day" , strtotime($fec) ) );
		for($i=$dia;$fec<=$fecultima;$i++){
			if($i==7){
				echo '</tr>';
				$i=0;
			}
			if($i==0){
				echo '<tr>';
			}
			
			echo '<td align="center" valign="center">'.substr($fec,8,2).'<br>Aforo: '.number_format($array_montos[$fec][1],0).'
			<br>Total Venta: '.number_format($array_montos[$fec][0],2).'<br>Devolucion: '.number_format($array_montos[$fec][2],2).'<br>
			Gran Total: '.number_format($array_montos[$fec][3],2).'</td>';
			$fec=date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fec) ) );
		}
		$arfecha=explode("-",$fecultima);
		$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
		for($i=$dia;$i<6;$i++){
			echo '<td align="center"><br><br>&nbsp;</td>';
		}
		echo '</tr>';
		echo '</table>';
	}
	exit();
}


top($_SESSION);
	

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="document.forma.plaza.value=$(\'#plazas\').multipleSelect(\'getSelects\');atcr(\'ventasxmes.php\',\'_blank\',100,0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Localidad</td><td><select name="localidad" id="localidad" onChange="if(this.value!=\'2\')$(\'#plazas\').parents(\'tr:first\').hide(); else $(\'#plazas\').parents(\'tr:first\').show();"><option value="0">Todos</option>';
		$res=mysql_query("SELECT * FROM areas ORDER BY nombre");
		while($row=mysql_fetch_array($res)){
			echo '<option value="'.$row['cve'].'"';
			if($row['cve']==2) echo ' selected';
			echo '>'.$row['nombre'].'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td align="left">Mostrar por:</td><td><select name="mostrar" id="mostrar" onChange="if(this.value==\'1\') $(\'#mes\').parents(\'tr:first\').hide(); else $(\'#mes\').parents(\'tr:first\').show();"><option value="1">Año</option><option value="2">Mes</option></select></td></tr>';
		echo '<tr><td>Año</td><td><select name="anio" id="anio">';
		for($i=date("Y");$i>=2014;$i--) echo '<option value="'.$i.'">'.$i.'</option>';
		echo '</select></td></tr>';
		echo '<tr style="display:none;"><td align="left">Mes</td><td><select name="mes" id="mes">';
		foreach($array_meses as $k=>$v){
			if($k>0){
				echo '<option value="'.$k.'"';
				if(intval(date("m"))==$k) echo ' selected';
				echo '>'.$v.'</option>';
			}
		}
		echo '</select>';
		echo '<tr><td align="left">Plaza</td><td><select multiple="multiple" name="plazas" id="plazas">';
		foreach($array_plazas as $k=>$v){
			echo '<option value="'.$k.'" selected>'.$v.'</option>';
		}
		echo '</select>';
		echo '<input type="hidden" name="plaza" id="plaza" value=""></td></tr>';
		echo '</table>';
		echo '<br>';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
bottom();
echo '
<Script language="javascript">
	$("#plazas").multipleSelect({
		width: 500
	});	
	function buscarRegistros(){
		document.forma.plaza.value=$("#plazas").multipleSelect("getSelects");
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","ventasxmes.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&localidad="+document.getElementById("localidad").value+"&plaza="+document.getElementById("plaza").value+"&mostrar="+document.getElementById("mostrar").value+"&anio="+document.getElementById("anio").value+"&mes="+document.getElementById("mes").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
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