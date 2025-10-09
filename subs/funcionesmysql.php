<?php

function mysql_connect($host, $user, $pass){
	global $mysqli;
	$mysqli = new mysqli($host, $user, $pass);
	return $mysqli;
}

function mysql_select_db($base){
	global $MySQL;
	$MySQL->select_db($base);
}

function mysql_query($query){
	GLOBAL $mysqli, $MySQL;
	$result = $MySQL->query($query) or die($MySQL->error.'<br>'.$query);

	return $result;
}

function mysql_fetch_assoc($result)
{
	GLOBAL $mysqli, $MySQL;
	$row = $result->fetch_assoc();
	return $row;
}

function mysql_fetch_array($result)
{
	GLOBAL $mysqli, $MySQL;
	$row = $result->fetch_array();
	return $row;
}

function mysql_num_rows($result)
{
	GLOBAL $mysqli, $MySQL;
	$rows = $result->num_rows;
	return $rows;
}

function mysql_insert_id(){
	GLOBAL $mysqli, $MySQL;
	return $MySQL->insert_id;
}

?>