<?php 
include ("call_main.php"); 
	$fechahora=fechahoraLocal();
	mysql_query("UPDATE call_registros_sistema SET salida='".$fechahora."' WHERE cve='".$_SESSION['reg_sistema']."'");
	// Unset all of the session variables.
	$_SESSION = array();
	
	// Finally, destroy the session.
	session_destroy();
	
	header("Location: call_index.php");
	
?>
