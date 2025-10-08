<?php 

include ("eco_main.php"); 
if($_POST['ajax']==99){
	mysql_query("INSERT eco_registros_sistemamov SET cveacceso='".$_POST['cvereg']."',usuario='".$_POST['usuario']."',menu='".$_POST['idmenu']."',fechahora='".fechaLocal()." ".horaLocal()."'");
	exit();
}
top($_SESSION);
echo '<h1>Bienvenido</h1>';
bottom();
?>

