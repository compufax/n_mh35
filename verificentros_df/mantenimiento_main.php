<?php
require_once('../subs/cnx_db.php');
include("parametros-navegacion.php");

global $base,$PHP_SELF,$cveempresanomina;
/*Validamos solicitud de login a este sitio*/
if (!isset($_SESSION)) {
  session_start();
}

function getRealIP()
{
   global $_SERVER;
   if( $_SERVER['HTTP_X_FORWARDED_FOR'] != '' )
   {
      $client_ip =
         ( !empty($_SERVER['REMOTE_ADDR']) ) ?
            $_SERVER['REMOTE_ADDR']
            :
            ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
               $_ENV['REMOTE_ADDR']
               :
               "unknown" );

      // los proxys van añadiendo al final de esta cabecera
      // las direcciones ip que van "ocultando". Para localizar la ip real
      // del usuario se comienza a mirar por el principio hasta encontrar
      // una dirección ip que no sea del rango privado. En caso de no
      // encontrarse ninguna se toma como valor el REMOTE_ADDR

      $entries = split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);

      reset($entries);
      while (list(, $entry) = each($entries))
      {
         $entry = trim($entry);
         if ( preg_match("/^([0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+)/", $entry, $ip_list) )
         {
            // http://www.faqs.org/rfcs/rfc1918.html
            $private_ip = array(
                  '/^0\\./',
                  '/^127\\.0\\.0\\.1/',
                  '/^192\\.168\\..*/',
                  '/^172\\.((1[6-9])|(2[0-9])|(3[0-1]))\\..*/',
                  '/^10\\..*/');

            $found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);

            if ($client_ip != $found_ip)
            {
               $client_ip = $found_ip;
               break;
            }
         }
      }
   }
   else
   {
      $client_ip =
         ( !empty($_SERVER['REMOTE_ADDR']) ) ?
            $_SERVER['REMOTE_ADDR']
            :
            ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
               $_ENV['REMOTE_ADDR']
               :
               "unknown" );
   }

   return $client_ip;

}

if(!$_SESSION['MantCveUsuario'] && !$_SESSION['MantNomUsuario'] && !isset($_POST['loginUser']) && !isset($_POST['loginPassword'])) {

	header("Location: mantenimiento_index.php");
	exit();

}

if($_SESSION['MantCveUsuario']!=1 && $_POST['loginUser']!="root"){
	$rsCerrado=mysql_query("SELECT * FROM usuarios WHERE cve='1'") or die(mysql_error());
	$Cerrado=mysql_fetch_array($rsCerrado);
	if($Cerrado['cerrar_sistema']=='S'){
		echo '<script>window.location="mantenimiento_index.php";</script>';
	}
}
$archivo=explode("/",$_SERVER["PHP_SELF"]);
global $archivo,$reg_sistema;


$array_meses=array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$array_dias=array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sabado");
//7=>"Manejo de Tickets",
//,20=>"Banco"
//$array_modulos=array(1=>"Catalogos",2=>"Vales",3=>"Equitrac",4=>"Inventarios",5=>"Cotizaciones",106=>"Control de Envio",107=>"Contacto",108=>"Escuela",109=>"Nomina",
$array_modulos=array();
$empresanomina = 999999;
$array_nosi=array('NO','SI');
$array_estatus_personal = array(1=>"Alta",2=>"Baja",3=>"Inactivo");
$array_diasmes=array(0,31,28,31,30,31,30,31,31,30,31,30,31);
$array_forma_pago=array("PAGO EN UNA SOLA EXHIBICION");
$array_tipo_pago=array(0=>"EFECTIVO",2=>"TRANSFERENCIA",3=>"DEPOSITO",4=>"NO ESPECIFICADO",5=>"CHEQUE DENOMINATIVO",6=>"NO APLICA",7=>"CREDITO");//,1=>"CHEQUE"
$array_tipo_nomina=array(1=>"Semanal",2=>"Decenal",3=>"Quincenal",4=>"Mensual");
$array_documentos=array(1=>"Factura",2=>"Nota");

//Si existen las variables POST  usuario y password viene de login
if (isset($_POST['loginUser']) && isset($_POST['loginPassword'])) {
	//Como se supone venimos de ventana de login o sesion expirada, eliminamos cualquier rastro de sesion anterior
	// Unset all of the session variables.
	$_SESSION = array();
	// Finally, destroy the session.
	session_destroy();
	$loginUsername=$_POST['loginUser'];
	$password=$_POST['loginPassword'];
	$redirectLoginSuccess = "mantenimiento_reporte_lineas.php";
	$redirectLoginFailed = "mantenimiento_index.php?ErrLogUs=true";
	//Hacemos uso de la funcion GetSQLValueString para evitar la inyeccion de SQL
	//$LoginRS_query = sprintf("SELECT * FROM usuarios WHERE usuario LIKE BINARY %s AND password LIKE BINARY %s", Se le quito validacion de distincion de mayusculas y minusculas
	$LoginRS_query = sprintf("SELECT * FROM mantenimiento_usuarios WHERE usuario = %s AND password = %s AND estatus!='I'",
			  GetSQLValueString($loginUsername, "text"), GetSQLValueString($password, "text")); 
	   
	//echo $LoginRS_query;
	//exit();
	$LoginRS = mysql_query($LoginRS_query) or die(mysql_error());

	$loginFoundUser = mysql_num_rows($LoginRS);

	if ($loginFoundUser) {

		$Usuario=mysql_fetch_array($LoginRS);

		if($Usuario['cve']!=1){
			$rsCerrado=mysql_query("SELECT * FROM usuarios WHERE cve='1'");
			$Cerrado=mysql_fetch_array($rsCerrado);
			if($Cerrado['cerrar_sistema']=='S'){
				echo '<script>window.location="mantenimiento_index.php";</script>';
			}
		}
		$ip=getRealIP();
		$fechahora=date( "Y-m-d H:i:s" , strtotime ( "0 hour" , strtotime(date("Y-m-d H:i:s")) ) );
		mysql_query("INSERT mantenimiento_registros_sistema SET usuario='".$Usuario['cve']."',entrada='".$fechahora."',ip='$ip'");
		$reg_sistema=mysql_insert_id();

		//Creamos la sesion

		session_start();		

		

		//Creamos las variables de sesion del usuario en cuestion

		$_SESSION['MantCveUsuario'] = $Usuario['cve'];

		$_SESSION['MantNomUsuario'] = $Usuario['nombre'];

		$_SESSION['MantTipoUsuario'] = $Usuario['tipo'];

		$_SESSION['MantNickUsuario'] = $Usuario['usuario'];

		$_SESSION['MantProvUsuario'] = $Usuario['proveedor'];
				
		$_SESSION['Mantreg_sistema'] = $reg_sistema;
		
		header("Location: " . $redirectLoginSuccess );

	} else {
		
		header("Location: " . $redirectLoginFailed);


	}

}

if(intval($_POST['cveusuario'])==0){
	$_POST['cveusuario']=$_SESSION['MantCveUsuario'];
	$_POST['tipousuario']=$_SESSION['MantTipoUsuario'];
	$_POST['cveregistro']=$_SESSION['Mantreg_sistema'];
}


function top($SESSION,$enter=0) {



	global $base,$PHP_SELF,$array_modulos,$_POST,$cveempresanomina;

	

	//$url=split("/",$PHP_SELF);
	$url=split("/",$_SERVER["PHP_SELF"]);
	$url=array_reverse($url);

	

	$menuRS=mysql_query("SELECT * FROM mantenimiento_menu WHERE cve='".$_POST['cvemenu']."'");

	while($Menu=mysql_fetch_array($menuRS)) {

			$menuEncabezado=$Menu['nombre'];

	}
	
	echo '

	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">



	<html xmlns="http://www.w3.org/1999/xhtml">

	<head>

	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

	<title>:: VERIFICENTROS MANTENIMIENTO LINEAS ::</title>

	<link rel="stylesheet" type="text/css" href="../css/style.css" />

	<link rel="stylesheet" type="text/css" href="../calendar/dhtmlgoodies_calendar.css" />
	<style>
		.colorrojo { color: #FF0000 } 
		.panel {
            background:#DFE6EF;
            top:0px;
            left:0px;
            display:none;
            position:absolute;
            filter:alpha(opacity=40);
            opacity:.4;
        }
	</style>
	<script src="../js/rutinas.js"></script>
	<link href="../js/multiple-select.css" rel="stylesheet"/>
	<link rel="stylesheet" type="text/css" href="../css/ui.css" />
	<script src="../js/jquery-1.8.0.min.js" type="text/javascript"></script>
	<script src="../js/jquery-ui-1.8.23.custom.min.js" type="text/javascript"></script>
	<script src="../js/serializeform.js" type="text/javascript"></script>
	<script src="../js/jquery.multiple.select.js" type="text/javascript"></script>
	<script src="../calendar/dhtmlgoodies_calendar.js"></script>
	<script src="../js/validacampo.js" type="text/javascript"></script>
	<script>
	if(top.window.location.href!="http://verificentrosgp1.net/mantenimiento/" && top.window.location.href!="http://maron.verificentrosdf.net/mantenimiento/" && top.window.location.href!="http://hqtechnology.com.mx/")
		top.window.location.href="http://hqtechnology.com.mx";
	
	
	
	function pulsar(e) {
		tecla=(document.all) ? e.keyCode : e.which;
		if(tecla==13) return false;
	}';
	foreach($array_modulos as $k=>$v){
		echo 'var menu'.$k.'=0;';
	}
	
	echo '
	function mueveReloj(){
		cadena=document.getElementById("idreloj").innerHTML;
		if(cadena.substr(11,1)=="0")
			var	horas = parseInt(cadena.substr(12,1));
		else
			var	horas = parseInt(cadena.substr(11,2));
		if(cadena.substr(14,1)=="0")
			var	minuto = parseInt(cadena.substr(15,1));
		else
			var	minuto = parseInt(cadena.substr(14,2));
		if(cadena.substr(17,1)=="0")
			var	segundo = parseInt(cadena.substr(18,1));
		else
			var	segundo = parseInt(cadena.substr(17,2));
		var	anio = parseInt(cadena.substr(0,4));
		if(cadena.substr(5,1)=="0")
			var	mes = parseInt(cadena.substr(6,1));
		else
			var	mes = parseInt(cadena.substr(5,2));
		if(cadena.substr(8,1)=="0")
			var	dia = parseInt(cadena.substr(9,1));
		else
			var	dia = parseInt(cadena.substr(8,2));
		segundo++;
		if (segundo==60) {
			segundo=0;
			minuto++;
			if (minuto==60) {
				minuto=0;
				horas++;
				if (horas==24) {
					horas=0;
					dia++;
					if((dia==31 && (mes==4 || mes==6 || mes==9 || mes==11)) || (dia==32 && (mes==1 || mes==3 || mes==5 || mes==7 || mes==8 || mes==10 || mes==12)) || (dia==29 && mes==2 && (anio%4)!=0) || (dia==30 && mes==2 && (anio%4)==0)){
						dia=1;
						mes++;
					}
					if(mes==13){
						mes=1;
						anio++;
					}
				}
			}
		}
		if(horas<10) horas="0"+parseInt(horas);
		if(minuto<10) minuto="0"+parseInt(minuto);
		if(segundo<10) segundo="0"+parseInt(segundo);
		if(dia<10) dia="0"+parseInt(dia);
		if(mes<10) mes="0"+parseInt(mes);
		horaImprimible = anio+"-"+mes+"-"+dia+" "+horas+":"+minuto+ ":"+segundo;

		document.getElementById("idreloj").innerHTML = horaImprimible;

		setTimeout("mueveReloj()",1000)
	}
	</script>
	
	</head>



	<form name="forma" id="forma" method="POST" enctype="multipart/form-data">



	<!-- Definicion de variables ocultas -->

		<input type="hidden" name="cmd" id="cmd">

		<input type="hidden" name="cmdreferer" id="cmdreferer">

		<input type="hidden" name="reg" id="reg">
		
		<input type="hidden" name="cveusuario" id="cveusuario" value="'.$_POST['cveusuario'].'">
		<input type="hidden" name="tipousuario" id="plazausuario" value="'.$_POST['tipousuario'].'">
		
		<input type="hidden" name="cvemenu" id="cvemenu" value="'.$_POST['cvemenu'].'">
		
		<input type="hidden" name="cveregistro" id="cveregistro" value="'.$_POST['cveregistro'].'">

		<input type="hidden" name="numeroPagina" id="numeroPagina" value="0">

	<body'; if($enter==1) echo ' onkeypress="return pulsar(event)"'; echo '>
	<div id="panel" class="panel"></div>
	<table width="100%" height="50" border="0" cellpadding="0" cellspacing="0">

	  <tr>

	   <td background="images/bannertop-bg.png"><span class="whiteText17">VERIFICENTROS CALLCENTER</span></td>
		 <!--<td bgcolor="#FFFFFF" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="whiteText17"><img src="images/foldio-logo-inicio.jpg" height="48px"/></span></td>-->

	  </tr>

	</table>

	<table width="100%" border="0" cellspacing="0" cellpadding="0">

	  <tr>

		<td width="170" valign="top" bgcolor="#FFFFFF">

	';	
	mysql_select_db($base);
	if($Menu['tipo']>$_POST['tipousuario']){
		echo '<script>alert("No tiene acceso al menu");document.forma.cvemenu.value=1;atcr("mantenimiento_reporte_lineas.php","",0,0);</script>';
	}

	menuppal2($SESSION);

	
	echo '



	</td>

	<td width="6" valign="top" background="images/collapse_side_bg.png"><img src="images/collapse_side_bg.png" width="6" height="1" /></td>

	<td valign="top" bgcolor="#FFFFFF"><table width="100%" border="0" cellspacing="0" cellpadding="0">

	  <tr>

		<td width="30%" height="24" nowrap background="images/optionHeader.png"><b>:: '.$menuEncabezado.' ::</b></td>

		<td background="images/optionHeader.png"><div align="right">Bienvenido '.$SESSION['MantNomUsuario'].'</div></td>
		
		<td background="images/optionHeader.png"><div align="center" id="idreloj">'.fechaLocal().' '.horaLocal().'</div></td>

		<td width="15%" background="images/optionHeader.png" align="center" nowrap><a href="mantenimiento_logout.php">Cerrar Sesion</a></td>

	  </tr>

	</table>

	  <br />

	  <table width="98%" border="0" align="center" cellpadding="0" cellspacing="0">

		<tr><td>

		<!-- INICIO REGION EDITABLE -->

	';

}




function bottom() {



	echo '

			<!-- FIN REGION EDITABLE -->		

			</td></tr>

	      </table>

	      <p>&nbsp;</p>

	      <p>&nbsp;</p>

	      <p>&nbsp;</p>

	      <p>&nbsp;</p>

	      <p>&nbsp;</p>

	      <p>&nbsp;</p>

	      <p>&nbsp;</p>

	      <p>&nbsp;</p>

	      <p>&nbsp;</p>

	      <p>&nbsp;</p>

	      <p>&nbsp;</p>

	      <p>&nbsp;</p>

	      <p>&nbsp;</p>

		<p>&nbsp;</p>

		</td>

	  </tr>

	  <tr>

	    <td colspan="3" valign="top" bgcolor="#CC9933">&nbsp;</td>

	  </tr>

	</table>

	</body>
	<script>
		mueveReloj();
		window.onload=function(){
            if (self.screen.availWidth) {
                $("#panel").css("width",parseFloat(self.screen.availWidth)+50);
            }
            if (self.screen.availHeight) {
                $("#panel").css("height",self.screen.availHeight);
            }
        }  
        $(".placas").validCampo("abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ1234567890");
	</script>
	</form>

	</html>

	';

}

function menuppal2($SESSION) {
	global $base,$array_modulos,$array_plaza,$PHP_SELF,$_POST;
	$url=split("/",$_SERVER["PHP_SELF"]);
	$url=array_reverse($url);
	echo '
	<table width="100%" border="0" cellspacing="0" cellpadding="3">
		<tr><td height="20" bgcolor="#CC9933"><span class="style1">Menu</span></td></tr>';
		$rs=mysql_query("SELECT * FROM mantenimiento_menu WHERE tipo<='".$_POST['tipousuario']."' ORDER BY orden");
		while($ro=mysql_fetch_array($rs)){
			echo '<tr><td><a href="#" onClick="document.forma.cvemenu.value='.$ro['cve'].';atcr(\''.$ro['link'].'\',\'\',\'\',\'\')">-'.$ro['nombre'].'</a></td></tr>';
		}
		
	
	echo '</table>';
}

function menunavegacion() {



	global $totalRegistros, $eTotalPaginas, $eNumeroPagina, $primerRegistro, $eAnteriorPagina, $eSiguientePagina, $eNumeroPagina;



	echo '



	<table width="100%" height="20" border="0" cellpadding="0" cellspacing="0">

	<tr>

	<td width="20%" class="">'.$totalRegistros.'</font> Registro(s)</td>';

	if ($eTotalPaginas>0) {

		echo '

		<td width="60%" class="" align="right">P&aacute;gina <font class="fntN10B">';print $eNumeroPagina+1; echo'</font> de <font class="fntN10B">'; print $eTotalPaginas+1; echo'</font> </td>';

		if ($primerRegistro>0) {

			echo '

			<td width="12" align="center" class="sanLR10"><a href="JavaScript:moverPagina(0);"><img src="images/mover-primero.gif" width="10" height="12" border="0" align="absmiddle" title="Inicio"></a> </td>';

		} else {

			echo '

			<td width="12" align="center" class="sanLR10"><img src="images/mover-primero-d.gif" width="10" height="12" border="0" align="absmiddle"></td>';

		}



		if ($eAnteriorPagina>=0) {

			echo '

			<td width="12" align="center" class="sanLR10"><a href="JavaScript:moverPagina('.$eAnteriorPagina.');"><img src="images/mover-anterior.gif" width="7" height="12" border="0" align="absmiddle" title="Anterior"></a></td>';

		} else {

			echo '

			<td width="12" align="center" class="sanLR10"><img src="images/mover-anterior-d.gif" width="7" height="12" border="0" align="absmiddle"></td>';

		}



		if ($eSiguientePagina<=$eTotalPaginas) {

			echo '

			<td width="12" align="center" class="sanLR10"><a href="JavaScript:moverPagina('.$eSiguientePagina.');"><img src="images/mover-siguiente.gif" width="7" height="12" border="0" align="absmiddle" title="Siguiente"></a></td>';

		} else {

			echo '

			<td width="12" align="center" class="sanLR10"><img src="images/mover-siguiente-d.gif" width="7" height="12" border="0" align="absmiddle"></td>';

		}



		if ($eNumeroPagina<$eTotalPaginas) {

			echo '

			<td width="12" align="center" class="sanLR10"> <a href="JavaScript:moverPagina('.$eTotalPaginas.');"><img src="images/mover-ultimo.gif" width="10" height="12" border="0" align="absmiddle" title="Fin"></a></td>';

		} else {

			echo '

			<td width="12" align="center" class="sanLR10"><img src="images/mover-ultimo-d.gif" width="10" height="12" border="0" align="absmiddle"></td>';

		}



	}

	echo '

	</tr>

	</table>';

	

}





function menu() {

echo '';

}



	// Renglon en fondo Blanco

	function rowc() {

		echo '<tr bgcolor="#ffffff" onmouseover="sc(this, 1, 0);" onmouseout="sc(this, 0, 0);" onmousedown="sc(this, 2, 0);">';

	}



	// Renglones que cambian el color de fondo

	function rowb($imprimir = true, $sc = '') {

		static $rc;
		$regresar = '';
		if ($rc) {
			if($imprimir)
				echo '<tr bgcolor="#d5d5d5" onmouseover="sc'.$sc.'(this, 1, 1);" onmouseout="sc'.$sc.'(this, 0, 1);" onmousedown="sc'.$sc.'(this, 2, 1);">';
			else
				$regresar = '<tr bgcolor="#d5d5d5" onmouseover="sc'.$sc.'(this, 1, 1);" onmouseout="sc'.$sc.'(this, 0, 1);" onmousedown="sc'.$sc.'(this, 2, 1);">';
			$rc=FALSE;

		}

		else {
			if($imprimir)
				echo '<tr bgcolor="#e5e5e5" onmouseover="sc'.$sc.'(this, 1, 2);" onmouseout="sc'.$sc.'(this, 0, 2);" onmousedown="sc'.$sc.'(this, 2, 2);">';
			else
				$regresar= '<tr bgcolor="#e5e5e5" onmouseover="sc'.$sc.'(this, 1, 2);" onmouseout="sc'.$sc.'(this, 0, 2);" onmousedown="sc'.$sc.'(this, 2, 2);">';

			$rc=TRUE;

		}
		if(!$imprimir)
			return $regresar;

	}





	function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 

	{

		$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;



		$theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);



		switch ($theType) {

		case "text":

		  $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";

		  break;    

		case "long":

		case "int":

		  $theValue = ($theValue != "") ? intval($theValue) : "NULL";

		  break;

		case "double":

		  $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";

		  break;

		case "date":

		  $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";

		  break;

		case "defined":

		  $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;

		  break;

		}

		return $theValue;

	}



	

		function diaSemana($fecha) {

			$weekDay=array('Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado');

			$ano=substr($fecha,0,4);

			$mes=substr($fecha,5,2);

			$dia=substr($fecha,8,2);

			$numDia=jddayofweek ( cal_to_jd(CAL_GREGORIAN, date($mes),date($dia), date($ano)) , 0 );

			$result=$weekDay[$numDia];

			return $result;

		}

	function fecha_normal($fecha){
		$datos = explode("-",$fecha);
		return $datos[2].'/'.$datos[1].'/'.$datos[0];
	}

	function horaLocal() {
		
		$differencetolocaltime=1;

		$new_U=date("U")+$differencetolocaltime*3600;

		//$fulllocaldatetime= date("d-m-Y h:i:s A", $new_U);

		$hora= date("H:i:s", $new_U);
		
		$hora=date( "Y-m-d H:i:s" , strtotime ( "0 hour" , strtotime(date("Y-m-d H:i:s")) ) );
		
		$hora=date( "H:i:s" , strtotime ( "0 minute" , strtotime($hora) ) );

		return $hora;

		//Regards. Mohammed Ahmad. MSN: m@maaking.com

	}
	
	function fechaLocal(){
		$differencetolocaltime=1;

		$new_U=date("U")+$differencetolocaltime*3600;

		//$fulllocaldatetime= date("d-m-Y h:i:s A", $new_U);

		//$fecha= date("Y-m-d", $new_U);
		
		$fecha=date( "Y-m-d H:i:s" , strtotime ( "0 hour" , strtotime(date("Y-m-d H:i:s")) ) );
		
		$fecha=date( "Y-m-d" , strtotime ( "0 minute" , strtotime($fecha) ) );

		return $fecha;
	}
	
	function fechahoraLocal(){
		$differencetolocaltime=1;

		$new_U=date("U")+$differencetolocaltime*3600;

		//$fulllocaldatetime= date("d-m-Y h:i:s A", $new_U);

		$//fechahora= date("Y-m-d H:i:s", $new_U);
		
		$fechahora=date( "Y-m-d H:i:s" , strtotime ( "0 hour" , strtotime(date("Y-m-d H:i:s")) ) );
		
		$fechahora=date( "Y-m-d H:i:s" , strtotime ( "0 minute" , strtotime($fechahora) ) );

		return $fechahora;
	}

	function fecha_letra($fecha){
		$fecven=split("-",$fecha);
		$fecha_letra=$fecven[2]." de ";;
		switch($fecven[1]){
			case "01":$fecha_letra.="Enero";break;
			case "02":$fecha_letra.="Febrero";break;
			case "03":$fecha_letra.="Marzo";break;
			case "04":$fecha_letra.="Abril";break;
			case "05":$fecha_letra.="Mayo";break;
			case "06":$fecha_letra.="Junio";break;
			case "07":$fecha_letra.="Julio";break;
			case "08":$fecha_letra.="Agosto";break;
			case "09":$fecha_letra.="Septiembre";break;
			case "10":$fecha_letra.="Octubre";break;
			case "11":$fecha_letra.="Noviembre";break;
			case "12":$fecha_letra.="Diciembre";break;
		}
		$fecha_letra.=" del ".$fecven[0]."";
		return $fecha_letra;
	}
	
	function fechaNormal($fecha){
		$arrFecha=explode("-",$fecha);
		return $arrFecha[2].'/'.$arrFecha[1].'/'.$arrFecha[0];
	}
	
	function traer_numero_semana($fechasem){
		global $base;
		$anio=substr($fechasem,0,4);
		$fecha=$anio.'-01-01';
		$arfecha=explode("-",$fecha);
		$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
		if($dia!=1){
			$dias=8-$dia;
			$fecha=date( "Y-m-d" , strtotime ( "+".$dias." day" , strtotime($fecha) ) );
		}
		$fecha=date( "Y-m-d" , strtotime ( "+2 day" , strtotime($fecha) ) );
		if($fechasem<$fecha){
			$anio--;
			$fecha=$anio.'-01-01';
			$arfecha=explode("-",$fecha);
			$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
			if($dia!=1){
				$dias=8-$dia;
				$fecha=date( "Y-m-d" , strtotime ( "+".$dias." day" , strtotime($fecha) ) );
			}
			$fecha=date( "Y-m-d" , strtotime ( "+2 day" , strtotime($fecha) ) );
		}
		$res=mysql_query("SELECT TO_DAYS('$fechasem')-TO_DAYS('$fecha')");
		$row=mysql_fetch_array($res);
		$semana=intval($row[0]/7)+1;
		return $semana;
	}
	
	function traer_fechas_semana($semana,$anio){
		$fecha=$anio.'-01-01';
		$arfecha=explode("-",$fecha);
		$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
		if($dia!=1){
			$dias=8-$dia;
			$fecha=date( "Y-m-d" , strtotime ( "+".$dias." day" , strtotime($fecha) ) );
		}
		$fecha=date( "Y-m-d" , strtotime ( "+2 day" , strtotime($fecha) ) );
		$fecha_ini=date( "Y-m-d" , strtotime ( "+".(($semana-1)*7)." day" , strtotime($fecha) ) );
		$fecha_fin=date( "Y-m-d" , strtotime ( "+6 day" , strtotime($fecha_ini) ) );
		return $fecha_ini.' - '.$fecha_fin;
	}
	
	
	
	function calculaDias($fecha1,$fecha2){
		global $base;
		$rs=mysql_query("SELECT to_days('$fecha2')-to_days('$fecha1')");
		$ro=mysql_fetch_array($rs);
		return $ro[0]+1;
	}
	
	
	function edad($rfc){
		$anio=intval("19".substr($rfc,4,2));
		$mes=intval(substr($rfc,6,2));
		$dia=intval(substr($rfc,8,2));
		
		$anio2=intval(substr(fechaLocal(),0,4));
		$mes2=intval(substr(fechaLocal(),5,2));
		$dia2=intval(substr(fechaLocal(),8,2));
		
		$edad=$anio2-$anio;
		if($mes2<$mes){
			$edad--;
		}
		elseif($mes2==$mes){
			if($dia2<$dia){
				$edad--;
			}
		}
		return $edad;
	}
	
	function antiguedad($fecha_inicio){
		global $base;
		$res=mysql_query("SELECT DATEDIFF(CURDATE(),'$fecha_inicio') as dias");
		$row=mysql_fetch_array($res);
		$semanas = intval($row['dias']/7);
		return $semanas;
	}
	
	
?>