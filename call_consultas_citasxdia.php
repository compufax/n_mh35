<?php 

include ("call_main.php"); 
$res = mysql_query("SELECT * FROM engomados ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_engomado[$row['cve']]=$row['nombre'];
}

$res = mysql_query("SELECT * FROM call_usuarios");
$array_usuario[-1]="WEB";
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$res = mysql_query("SELECT * FROM datosempresas WHERE maneja_callcenter=1 ORDER BY nombre_callcenter");
while($row=mysql_fetch_array($res)){
	$array_plazas[$row['plaza']]=$row['nombre_callcenter'];
}


$array_tipo=array("Telefonista","Administrador");


		
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	
		echo genera_html($_POST['plaza'], $_POST['fecha']);
		exit();	
}	

if($_POST['ajax']==2){
	$html = genera_html($_POST['plaza'], $_POST['fecha'],1);
	$Plaza = mysql_fetch_array(mysql_query("SELECT * FROM datosempresas WHERE plaza='".$_POST['plaza']."'"));
	require_once("phpmailer/class.phpmailer.php");
	/*$mail = new PHPMailer();
	$mail->Host = "localhost";
	$mail->From = "verificentros@verificentros.net";
	$mail->FromName = "Verificentros ".$Plaza['nombre_callcenter'];
	$mail->Subject = "Citas para Verificacion del Dia ".fechaNormal($_POST['fecha']);
	//$mail­>CharSet = "UTF­8";
	//$mail­>Encoding = "quoted­printable";
	$mail->IsHTML(true);
	//$mail->Body = '<html>'.$html.'</html>';
	$mail->MsgHTML('<html>'.$html.'</html>');
	$emails = explode(",",$Plaza['call_emails']);
	foreach($emails as $email){
		if(trim($email)!="")
			$mail->AddAddress(trim($email));
	}
	$mail->Send();
	echo $html;*/
	
	$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
	$cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	// Cabeceras adicionales
	$cabeceras .= 'From: Verificentros '.$Plaza['nombre_callcenter'].' <verificentros@verificentros.net>' . "\r\n";
	
	mail($Plaza['call_emails'], "Citas para Verificacion del Dia ".fechaNormal($_POST['fecha']), $html, $cabeceras);
	
	exit();
}

if($_POST['cmd']==10){
	header("Content-type: application/vnd.ms-excel; name='excel'");
	header("Content-Disposition: filename=CitasxDia.xls");
	header("Pragma: no-cache");
	header("Expires: 0");

	echo genera_html($_POST['plaza'], $_POST['fecha'],2);
	exit();
}

top($_SESSION);

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onclick="mandarEmail();"><img src="images/finalizar.gif" border="0"></a>&nbsp;&nbsp;Enviar Email</td><td>&nbsp;</td>
				<td><a href="#" onclick="atcr(\'call_consultas_citasxdia.php\',\'\',10,0);"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Excel</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><td>Fecha</td><td><input type="text" name="fecha" id="fecha" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Centro de Verificacion</td><td><select name="plaza" id="plaza"><option value="0">Seleccione</option>';
		foreach($array_plazas as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';		

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
		/*** RUTINAS JS **************************************************/
		echo '
		<Script language="javascript">

			function buscarRegistros()
			{
				document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
				objeto=crearObjeto();
				if (objeto.readyState != 0) {
					alert("Error: El Navegador no soporta AJAX");
				} else {
					objeto.open("POST","call_consultas_citasxdia.php",true);
					objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto.send("ajax=1&fecha="+document.getElementById("fecha").value+"&plaza="+document.getElementById("plaza").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
					objeto.onreadystatechange = function()
					{
						if (objeto.readyState==4)
						{document.getElementById("Resultados").innerHTML = objeto.responseText;}
					}
				}
				document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
			}
			
			function mandarEmail()
			{
				objeto=crearObjeto();
				if (objeto.readyState != 0) {
					alert("Error: El Navegador no soporta AJAX");
				} else {
					objeto.open("POST","call_consultas_citasxdia.php",true);
					objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto.send("ajax=2&fecha="+document.getElementById("fecha").value+"&plaza="+document.getElementById("plaza").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
					objeto.onreadystatechange = function()
					{
						if (objeto.readyState==4)
						{alert("Email Enviado");}
					}
				}
			}
			
			//Funcion para navegacion de Registros. 20 por pagina.
			function moverPagina(x) {
				document.getElementById("numeroPagina").value = x;
				buscarRegistros();
			}	
			function sc2(theRow, theCmd, theColor) {
				var c,theCells,rowCellsCnt,colorCell,newColor,colorOut,colorIn,colorClick,colorClick2;
				colorIn="#d1dadf";
				colorClick="#b5d0df";
				colorClick2="#7dbcdf";
				if (theColor==0) colorOut="#ffffff";
				if (theColor==1) colorOut="#d5d5d5";
				if (theColor==2) colorOut="#e5e5e5";
				theCells = theRow.cells;
				rowCellsCnt  = theCells.length;
				colorCell=theCells[0].getAttribute("bgcolor");
				if (colorCell==colorClick2) newColor=colorClick2; else newColor=colorClick;
				if (theCmd==1 && (colorCell!=colorClick && colorCell!=colorClick2)) newColor=colorIn;
				if (theCmd==0 && (colorCell!=colorClick && colorCell!=colorClick2)) newColor=colorOut;
				if (theCmd==2 && colorCell==colorClick) newColor=colorClick2;
				if (theCmd==2 && colorCell==colorClick2) newColor=colorIn;
				for (c = 1; c < rowCellsCnt; c++) {
					theCells[c].setAttribute("bgcolor", newColor, 0);
				}
			}
			</Script>';
	}
	
bottom();





?>

