<?php 
include ("mantenimiento_main.php"); 
	$fechahora=fechahoraLocal();
	mysql_query("UPDATE mantenimiento_registros_sistema SET salida='".$fechahora."' WHERE cve='".$_SESSION['Mantreg_sistema']."'");
	// Unset all of the session variables.
	$_SESSION = array();
	
	// Finally, destroy the session.
	session_destroy();
	
	header("Location: mantenimiento_index.php");
	
?>
