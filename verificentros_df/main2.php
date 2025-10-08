<?php
require_once('subs/cnx_db.php');

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

$array_tipokardex=array("Compra","Vale","Venta Mostrador","Traspaso Almacen");

$array_meses=array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$array_dias=array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sabado");
$array_nosi=array('No','Si');

function top() {



	global $base,$PHP_SELF,$array_modulos,$_POST,$cveempresanomina;

	

	//$url=split("/",$PHP_SELF);
	$url=split("/",$_SERVER["PHP_SELF"]);
	$url=array_reverse($url);

	

	
	echo '

	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">



	<html xmlns="http://www.w3.org/1999/xhtml">

	<head>

	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

	<title>:: Asociaci&oacute;n de centros de verificaci&oacute;n vehicular de Puebla A.C. ::</title>

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


		<input type="hidden" name="reg" id="reg">
		
		
		<input type="hidden" name="numeroPagina" id="numeroPagina" value="0">

	<body>
	<div id="panel" class="panel"></div>
	<table width="100%" height="50" border="0" cellpadding="0" cellspacing="0">

	  <tr>

	   <td background="images/bannertop-bg.gif"><span class="whiteText17">Asociaci&oacute;n de centros de verificaci&oacute;n vehicular de Puebla A.C.</span></td>
		 <!--<td bgcolor="#FFFFFF" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="whiteText17"><img src="images/foldio-logo-inicio.jpg" height="48px"/></span></td>-->

	  </tr>

	</table>

	<table width="100%" border="0" cellspacing="0" cellpadding="0">

	  <tr>

		

	<td width="6" valign="top" background="images/collapse_side_bg.gif"><img src="images/collapse_side_bg.gif" width="6" height="1" /></td>

	<td valign="top" bgcolor="#FFFFFF"><table width="100%" border="0" cellspacing="0" cellpadding="0">

	  <tr>

		<td width="30%" height="24" nowrap background="images/optionHeader.gif"><b>:: Registro ::</b></td>

		<td background="images/optionHeader.gif"><div align="right">Bienvenido '.$SESSION['NomUsuario'].'</div></td>
		
		<td background="images/optionHeader.gif"><div align="center" id="idreloj">'.fechaLocal().' '.horaLocal().'</div></td>

		<td width="15%" background="images/optionHeader.gif" align="center" nowrap>&nbsp;</td>

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

	    <td colspan="3" valign="top" bgcolor="#666666">
		 <p>&nbsp;</p>
			<table>
			<tr>
			   <!--<td width="200" align="center"><img src="http://website.verificentros.net/img/logo.png"></td>-->
			   <td width="200" align="center"><img src="http://website.verificentros.net/img/logo2.png"></td>
			   <td width="200" align="center"><img src="http://website.verificentros.net/img/logo3.png"></td>
			   <td width="300" align="right"><h2><font color="white">Asociaci&oacute;n de centros de verificaci&oacute;n vehicular de Puebla A.C.</font></h2></td>
			</tr>
			</table>
		</td>

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

	function rowb($imprimir = true) {

		static $rc;
		$regresar = '';
		if ($rc) {
			if($imprimir)
				echo '<tr bgcolor="#d5d5d5" onmouseover="sc(this, 1, 1);" onmouseout="sc(this, 0, 1);" onmousedown="sc(this, 2, 1);">';
			else
				$regresar = '<tr bgcolor="#d5d5d5" onmouseover="sc(this, 1, 1);" onmouseout="sc(this, 0, 1);" onmousedown="sc(this, 2, 1);">';
			$rc=FALSE;

		}

		else {
			if($imprimir)
				echo '<tr bgcolor="#e5e5e5" onmouseover="sc(this, 1, 2);" onmouseout="sc(this, 0, 2);" onmousedown="sc(this, 2, 2);">';
			else
				$regresar= '<tr bgcolor="#e5e5e5" onmouseover="sc(this, 1, 2);" onmouseout="sc(this, 0, 2);" onmousedown="sc(this, 2, 2);">';

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




?>