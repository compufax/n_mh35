<?php
include("main.php");
$res = mysql_query("SELECT a.plaza,a.localidad_id FROM datosempresas a WHERE a.plaza='".$_POST['plazausuario']."'");
$Plaza=mysql_fetch_array($res);

$array_engomado = array();
$res = mysql_query("SELECT * FROM engomados WHERE localidad='".$Plaza['localidad_id']."' AND plazas like '%|".$_POST['plazausuario']."|%' AND entrega=1 ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
}

top($_SESSION);

if($_POST['cmd']==2){
	foreach($array_engomado as $k=>$v){
		mysql_query("INSERT minimos_plaza_engomado SET plaza='".$_POST['plazausuario']."',engomado='$k',minimo='".$_POST['minimo_'.$k]."',usuario='".$_POST['cveusuario']."',modificacion='".fechaLocal()." ".horaLocal()."',cantidad_restar='".$_POST['cantidad_restar_'.$k]."',existencia2016='".$_POST['existencia2016_'.$k]."'");
	}
}

echo '<table>';
echo '
	<tr>';
	if(nivelUsuario()>1)
		echo '<td><a href="#" onClick="atcr(\'minimos_plaza_engomado.php\',\'\',2,0);"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	echo '</tr>';
echo '</table>';
echo '<br>';

//Formulario 
echo '<table>';
echo '<tr><td class="tableEnc">Existencia Minima de Certificados</td></tr>';
echo '</table>';
echo '<table><th>Tipo de Verificaci&oacute;n</th><th>Cantidad Minima</th>';
if($_POST['cveusuario']==1) echo '<th>Cantidad a Restar</th><th>Existencia 2016</th>';
echo '</tr>';
foreach($array_engomado as $k=>$v){
	$res=mysql_query("SELECT minimo,cantidad_restar,existencia2016 FROM minimos_plaza_engomado WHERE plaza='".$_POST['plazausuario']."' AND engomado='$k' ORDER BY cve DESC");
	$row=mysql_fetch_array($res);
	echo '<tr><th align="left">'.$v.'</th><td><input type="text" class="textField" size="10" name="minimo_'.$k.'" value="'.$row['minimo'].'"></td>';
	if($_POST['cveusuario']==1){
		echo '<td><input type="text" class="textField" size="10" name="cantidad_restar_'.$k.'" value="'.$row['cantidad_restar'].'"></td>';
		echo '<td><input type="text" class="textField" size="10" name="existencia2016_'.$k.'" value="'.$row['existencia2016'].'"></td>';
	}
	else{
		echo '<input type="hidden" class="textField" size="10" name="cantidad_restar_'.$k.'" value="'.$row['cantidad_restar'].'">';
		echo '<input type="hidden" class="textField" size="10" name="existencia2016_'.$k.'" value="'.$row['existencia2016'].'">';
	}
	echo '</tr>';
}

echo '</table>';

bottom();

?>