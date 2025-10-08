<?php 
include ("main.php"); 
$array_clientes=array();
$res=mysql_query("SELECT * FROM clientes WHERE 1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_clientes[$row['cve']]=$row['nombre'].'-('.$row['rfc'].')';
	$array_clientes_[$row['cve']]=$row['nombre'];
}
$array_clientes_factura=array();
$res=mysql_query("SELECT * FROM facturas WHERE 1 ");
while($row=mysql_fetch_array($res)){
	$array_clientes_factura[$row['cliente']]=$array_clientes_[$row['cliente']];

}
if($_GET['ajax']==10){
//	$res = mysql_query("SELECT cve,nombre,referencia FROM clientes WHERE tipo_cliente != 2 AND nombre like '%".utf8_decode($_GET['term'])."%' ORDER BY nombre");
//	$res = mysql_query("SELECT * FROM facturacion_usuarios WHERE estatus!='I' AND cve>'1' and usuario like '%".utf8_decode($_GET['term'])."%' ORDER BY usuario");

			$res = mysql_query("SELECT * FROM clientes WHERE nombre like '".utf8_decode($_GET['term'])."%' ");
//			$select= " SELECT * FROM facturacion_usuarios WHERE estatus!='I'";
		
	$matches = array();
	while($row=mysql_fetch_assoc($res)){
		// Adding the necessary "value" and "label" fields and appending to result set
		$row['value'] = $row['nombre'];
//		if($row['referencia']!='') $row['usuario'] .= '('.$row['referencia'].')';
		$row['label'] = $row['nombre'];
		$row['nombre'] = utf8_encode($row['nombre']);
		$matches[] = $row;
	 } 
	  // Truncate, encode and return the results
	$matches = array_slice($matches, 0, 5);
	print json_encode($matches);
	exit();
}

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= "SELECT d.numero as NumeroPlaza, a.serie as SerieFactura, a.folio as FolioFactura, a.fecha as FechaFactura, b.nombre as NombreCliente, b.rfc as RfcCliente, c.cve as Ticket, c.fecha as FechaTicket, c.placa as PlacaTicket, IF(a.usuario=-1,'WEB',e.usuario) as Usuario 
FROM facturas a 
inner join clientes b on a.cliente = b.cve 
inner join cobro_engomado c on c.plaza = a.plaza and c.factura = a.cve 
inner join plazas d on d.cve = a.plaza 
left join usuarios e on e.cve = a.usuario 
where a.estatus!='C' and b.nombre = '".$_POST['cliente']."' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
//		if ($_POST['referencia']!="") { $select.=" AND referencia='".$_POST['referencia']."' "; }
//		if ($_POST['usuario']!="") { $select.=" AND usuario='".$_POST['usuario']."' "; }
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
//		echo''.$select.'';
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8">
				  <th>Plaza</th>
				  <th>Serie</th>
				  <th>Factura</th>
				  <th>Fecha</th>
				  <th>Cliente</th>
				  <th>RFC</th>
				  <th>Ticket</th>
				  <th>Fecha</th>
				  <th>Placa</th>
				  <th>Usuario</th>';
			echo '</tr>';
			
			while($row=mysql_fetch_array($res)) {
				rowb();
/*				echo '<td align="center" width="40" nowrap>';
				if($row['estatus']=='C'){
					echo 'Cancelado';
					$row['monto']=0;
				}
				else{
					if($_POST['cveusuario']==1){echo '<a href="#" onClick="atcr(\'\',\'\',\'1\','.$row['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Benef[''].'"></a>';}
					echo '<a href="#" onClick="atcr(\'factura_desglose_x_cliente.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					if(nivelUsuario()>1)
						echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el recibo\')) atcr(\'factura_desglose_x_cliente.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
				}	
				echo '</td>';*/
				echo '<td align="center">'.htmlentities($row[0]).'</td>';
				echo '<td align="center">'.htmlentities($row[1]).'</td>';
				echo '<td align="center">'.htmlentities($row[2]).'</td>';
				echo '<td align="center">'.htmlentities($row[3]).'</td>';
				echo '<td align="left">'.htmlentities(utf8_encode($row[4])).'</td>';
				echo '<td align="left">'.htmlentities($row[5]).'</td>';
				echo '<td align="center">'.htmlentities($row[6]).'</td>';
				echo '<td align="center">'.htmlentities($row[7]).'</td>';
				echo '<td align="center">'.htmlentities($row[8]).'</td>';
				echo '<td align="center">'.htmlentities($row[9]).'</td>';
				echo '</tr>';
		
			}
			echo '	
				<tr>
				<td colspan="10" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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



if ($_POST['cmd']<1) {
	
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<!--<td><a href="#" onClick="atcr(\'factura_desglose_x_cliente.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>-->
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo'<tr><td>Cliente</td><td><input type="text" size="50" name="cliente" id="cliente" value="" class="cliente"></td></tr>';
	/*echo '<tr><td>Cliente</td><td><select name="cliente" id="cliente" class=""><option value="">Todos</option>';
		foreach($array_clientes_ as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
	echo '</select></td></tr>';*/
	echo '</table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';




/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">
 var ac_config = {
			source: "factura_desglose_x_cliente.php?ajax=10",
			minLength:2
		};

		$("#cliente").autocomplete(ac_config);


	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","factura_desglose_x_cliente.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cliente="+document.getElementById("cliente").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	
	
	</Script>
	';

	
}
	
bottom();	
