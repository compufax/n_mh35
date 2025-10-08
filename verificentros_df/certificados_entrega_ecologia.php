<?php
include("main.php");


if($_POST['ajax']==1){
	
	
	
	


	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>Fecha</br>Venta</th><th>Placa</th><th>Fecha</br>Entrega</th><th>Placa</th><th>Certificado</th><th>Placa</br>Ecologia</th><th>Certificado</br>Ecologia</th></tr>';
	$select="SELECT a.*,b.cve as certificado, b.certificado as holograma,b.engomado as engomado_entrega, CONCAT(b.fecha,' ',b.hora) as fechaentrega,
					TIMEDIFF(IFNULL(CONCAT(b.fecha,' ',b.hora),NOW()),CONCAT(a.fecha,' ',a.hora)) as diferencia 
			 FROM cobro_engomado a 
			 LEFT JOIN certificados b ON a.plaza=b.plaza AND a.cve=b.ticket AND b.estatus!='C' 
			 LEFT JOIN depositantes c ON c.cve = a.depositante AND c.plaza = a.plaza 
			 WHERE a.estatus='A' and a.plaza='".$_POST['plazausuario']."' AND left(a.fecha,7) = '".$_POST['anio']."-".$_POST['mes']."'";
			 if($_POST['placa'] != '') $select .= " AND a.placa = '".$_POST['placa']."'";
	$select.=" ORDER BY a.fecha DESC, a.cve DESC";
	$res = mysql_query($select);
	$x = 0;
	while($row = mysql_fetch_array($res)){
		rowb();
		echo '<td align="center">'.$row['fecha'].'</td>';
		echo '<td align="center">'.$row['placa'].'</td>';
		
		//entrega
		$selec="SELECT a.*, b.tipo_venta, b.tipo_pago, d.nombre as nomdepositante, b.engomado as engomadoticket, b.tipo_combustible, b.factura 
		FROM certificados a 
		INNER JOIN cobro_engomado b ON b.plaza = a.plaza AND b.cve = a.ticket 
		LEFT JOIN depositantes d ON d.plaza = b.plaza AND d.cve = b.depositante 
		WHERE a.plaza='".$_POST['plazausuario']."' and a.estatus='A' AND a.ticket ='".$row['cve']."' ";
		$res1 = mysql_query($selec);
		$row1=mysql_fetch_array($res1);
		echo '<td align="center">'.$row1['fecha'].'</td>';
		echo '<td align="center">'.$row1['placa'].'</td>';
		echo '<td align="center">'.$row1['certificado'].'</td>';
		//entrega
		
		
		
		//ecologia
		$sele= " SELECT a.*, b.nombre as engomado FROM certificados_ecologia a LEFT JOIN engomados b ON b.cve = a.tipo 
		WHERE a.plaza='".$_POST['plazausuario']."' AND certificado = '".$row1['certificado']."'";
		//if($_POST['placa'] != '') $select .= " AND a.placa = '".$_POST['placa']."'";			
		//$selec.=" ORDER BY a.fecha DESC, a.cve DESC";
		$res2 = mysql_query($sele);
		$row2=mysql_fetch_array($res2);
		echo '<td align="center">'.$row2['placa'].'</td>';
		echo '<td align="center">'.$row2['certificado'].'</td>';
		//ecologia
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th align="left" colspan="7">'.$x.' Registro(s)</th></tr></table>';
	exit();
}

top($_SESSION);

if($_POST['cmd'] == 0){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';

	echo '
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>A&ntilde;o</td><td><select name="anio" id="anio">';
	for($i=date('Y');$i>=2018;$i--){
		echo '<option value="'.$i.'">'.$i.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Mes</td><td><select name="mes" id="mes">';
	for($i=1;$i<=12;$i++){
		echo '<option value="'.sprintf('%02s',$i).'"';
		if($i==intval(date('m'))) echo ' selected';
		echo '>'.$array_meses[$i].'</option>';
	}
	echo '</option></td></tr>';
	echo '<tr><td>Placa</td><td><input type="text" name="placa" id="placa" size="10" class="textField" value=""></td></tr>';
	echo '</table>';
	echo '<div id="Resultados">';
	echo '</div>';
	echo '
	<script>

	function buscarRegistros(btn)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","certificados_entrega_ecologia.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&anio="+document.getElementById("anio").value+"&mes="+document.getElementById("mes").value+"&placa="+document.getElementById("placa").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					document.getElementById("Resultados").innerHTML = objeto.responseText;
				}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}

	</script>';



}
bottom();
?>