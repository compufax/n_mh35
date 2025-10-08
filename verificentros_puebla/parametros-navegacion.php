<?php
//Parametros
$eRegistrosPagina = 100;
$eNumeroPagina = 0;
$eTotalPaginas = 0;
if ($_POST['numeroPagina']>0)
	{
	$eNumeroPagina = $_POST['numeroPagina'];
	}
$primerRegistro = ($eNumeroPagina * $eRegistrosPagina);
$eAnteriorPagina = $eNumeroPagina-1;
$eSiguientePagina = $eNumeroPagina+1;
?>