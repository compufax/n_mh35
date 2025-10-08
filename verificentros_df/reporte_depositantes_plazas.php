<?php 

include ("main.php"); 
$array_tipo = array(1=>"Pago Anticipado", 2=>"Credito");

$select= " SELECT a.*, b.numero, b.nombre FROM depositantes a INNER JOIN plazas b ON b.cve = a.plaza 
WHERE a.solo_contado=0 AND a.estatus!=1";
$select.=" ORDER BY b.numero, a.nombre";
$res=mysql_query($select);
$encabezado = '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
$encabezado .= '<tr bgcolor="#E9F2F8">';
$encabezado .= '<th>Nombre</th><th>Tipo</th><th>Propietario</th><th>Email</th><th>Telefonos</th>';
$encabezado .= '</tr>';
$plaza = 0;
while($row=mysql_fetch_array($res)) {
	if($plaza==0){
		$plaza = $row['plaza'];
		$i=0;
		echo '<h2>Depositantes del Centro '.$row['numero'].' '.$row['nombre'].'</h2>';
		echo $encabezado;
	}
	if($plaza != $row['plaza']){
		echo '	
			<tr>
			<td colspan="8" bgcolor="#E9F2F8">'.$i.' Registro(s)</td>
			</tr>
		</table>';
		$plaza = $row['plaza'];
		$i=0;
		echo '<h2>Depositantes del Centro '.$row['numero'].' '.$row['nombre'].'</h2>';
		echo $encabezado;
	}
	rowb();
	echo '<td>'.utf8_encode($row['nombre']).'</td>';
	if($row['agencia']==1){
		echo '<td align="center" width="40" >Agencia</td>';	
	}else{
		echo '<td align="center" width="40" >Taller</td>';		
	}
	echo '<td>'.utf8_encode($row['propietario']).'</td>';
	echo '<td>'.utf8_encode($row['email']).'</td>';
	echo '<td>'.utf8_encode($row['telefono']).'</td>';
	
	echo '</tr>';
	$i++;
}
echo '	
	<tr>
	<td colspan="8" bgcolor="#E9F2F8">'.$i.' Registro(s)</td>
	</tr>
</table>';
			
		

?>

