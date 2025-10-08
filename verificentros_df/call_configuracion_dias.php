<?php 
include("main.php");

if($_POST['ajax'] == 2){
	$res = mysql_query("SELECT * FROM call_configuracion_dias WHERE fecha='".$_POST['fecha']."'");
	if($row = mysql_fetch_array($res)){
		mysql_query("UPDATE call_configuracion_dias SET estatus='".$_POST['estatus']."',cambios=CONCAT(cambios,'".date('Y-m-d H:i:s').",".$_POST['cveusuario'].",".$_POST['estatus']."') WHERE cve='".$row['cve']."'");
	}
	else{
		mysql_query("INSERT call_configuracion_dias SET fecha='".$_POST['fecha']."',estatus='".$_POST['estatus']."',cambios='".date('Y-m-d H:i:s').",".$_POST['cveusuario'].",".$_POST['estatus']."'");
	}
	exit();
}

top($_SESSION);

$array_estatus_dia = array('Normal', 'Bloqueado', 'Desbloqueado');

if($_POST['mes']=="") $_POST['mes']=date("Y-m");
$fecha = $_POST['mes'];

echo '<input type="hidden" name="mes" id="mes" value="'.$_POST['mes'].'">';
echo '<h1 style="font-size:20px">';
$datos = explode("-",$_POST['mes']);
echo $array_meses[intval($datos[1])].' '.$datos[0].'</h2>';
echo '<table width="100%"><td width="50%" align="left"><input type="button" value="Anterior" style="font-size:20px" onClick="document.forma.mes.value=\''.date( "Y-m" , strtotime ( "-1 month" , strtotime($fecha) ) ).'\';atcr(\'call_configuracion_dias.php\',\'\',0,0);"></td>
<td width="50%" align="right"><input type="button" value="Siguiente" style="font-size:20px" onClick="document.forma.mes.value=\''.date( "Y-m" , strtotime ( "+ 1 month" , strtotime($fecha) ) ).'\';atcr(\'call_configuracion_dias.php\',\'\',0,0);"></td></tr></table>';
echo '<table style="font-size:20px" width="100%" border="1">
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

$array_fechas_conf = array();
$res=mysql_query("SELECT fecha, estatus FROM call_configuracion_dias WHERE fecha BETWEEN '".$fec."' AND '".$fecultima."'");
while($row=mysql_fetch_array($res)){
	$array_fechas_conf[$row['fecha']] = $row[1];
}
for($i=$dia;$fec<=$fecultima;$i++){
	$arfecha=explode("-",$fec);
	$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
	if($i==7){
		echo '</tr>';
		$i=0;
	}
	if($i==0){
		echo '<tr>';
	}
	
	echo '<td align="center" valign="center">
	<table width="100%"><tr><td width="100%">&nbsp;</td></tr>
	<tr><td align="center">'.substr($fec,8,2).'<input type="hidden" id="estatus_hidden_'.substr($fec,8,2).'" value="'.intval($array_fechas_conf[$fec]).'" </td></tr>
	<tr><td><select style="font-size:20px;" id="estatus_'.substr($fec,8,2).'" onChange="cambiar_estatus(\''.substr($fec,8,2).'\',\''.$fec.'\')"';
	if($fec<=date('Y-m-d')) echo ' disabled';
	echo '>';
	foreach($array_estatus_dia as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k == intval($array_fechas_conf[$fec])) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr></table></td>';
	$fec=date( "Y-m-d" , strtotime ( "+ 1 day" , strtotime($fec) ) );
}
$arfecha=explode("-",$fecultima);
$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
for($i=$dia;$i<6;$i++){
	echo '<td align="center"><br><br>&nbsp;</td>';
}
echo '</tr>';
echo '</table>';
echo '<script>
		function cambiar_estatus(id, fecha){
			if(confirm("Esta seguro de cambiar el estatus del dia?")){
				$.ajax({
				  url: "call_configuracion_dias.php",
				  type: "POST",
				  async: false,
				  data: {
					estatus: document.getElementById("estatus_"+id).value,
					fecha: fecha,
					cveusuario: document.forma.cveusuario.value,
					ajax: 2
				  },
					success: function(data) {
						alert("Se cambio con exito el estatus");
						document.getElementById("estatus_hidden_"+id).value = document.getElementById("estatus_"+id).value;
					}
				});
			}
			else{
				document.getElementById("estatus_"+id).value = document.getElementById("estatus_hidden_"+id).value;
			}
		}

	</script>';
bottom();

?>

