<?php 
include ("eco_main.php"); 
	$fechahora=fechahoraLocal();
	mysql_query("UPDATE eco_registros_sistema SET salida='".$fechahora."' WHERE cve='".$_SESSION['reg_sistema']."'");
	// Unset all of the session variables.
	$_SESSION = array();
	
	// Finally, destroy the session.
	session_destroy();
	
	header("Location: eco_index.php");
	
?>
