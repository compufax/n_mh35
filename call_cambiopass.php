<?php
include("call_main.php");

top($_SESSION);

if($_POST['cmd']==2){
	$update = " UPDATE call_usuarios 
						SET 
						  password='".$_POST['passnuevo']."',fechacambiopass=NOW()
						WHERE cve='".$_POST['cveusuario']."' " ;
	$ejecutar = mysql_query($update) or die(mysql_error());
}

$select=" SELECT * FROM call_usuarios WHERE cve='".$_POST['cveusuario']."' ";
$res=mysql_query($select);
$row=mysql_fetch_array($res);
//Menu
echo '<table>';
	echo '
		<tr>';
		echo '<td><a href="#" onClick="
		if(\''.$row['password'].'\'!=document.forma.passactual.value)
			alert(\'El password actual es incorrecto\');
		else if(document.forma.passnuevo.value==document.forma.passactual.value)
			alert(\'El nuevo password no puede ser el mismo que el actual\');
		else if(document.forma.passnuevo.value!=document.forma.passconfirma.value)
			alert(\'La confirmacion de password es incorrecta\');
		else
			atcr(\'call_cambiopass.php\',\'\',\'2\',\'0\');"><img src="images/guardar.gif" border="0">&nbsp;Cambiar</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Cambio de Password</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">Password Actual</th><td><input type="password" autocomplete="off" name="passactual" id="passactual" value=""></td></tr>';
		echo '<tr><th align="left">Nuevo Password</th><td><input type="password" autocomplete="off" name="passnuevo" id="passnuevo" value=""></td></tr>';
		echo '<tr><th align="left">Confirmar Password</th><td><input type="password" autocomplete="off" name="passconfirma" id="passconfirma" value=""></td></tr>';
		echo '</table>';

bottom();
?>