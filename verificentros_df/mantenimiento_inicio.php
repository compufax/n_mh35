<?php 

include ("mantenimiento_main.php"); 
if($_POST['ajax']==99){
	mysql_query("INSERT mantenimiento_registros_sistemamov SET cveacceso='".$_POST['cvereg']."',usuario='".$_POST['usuario']."',menu='".$_POST['idmenu']."',fechahora='".fechaLocal()." ".horaLocal()."'");
	exit();
}

 
?>

