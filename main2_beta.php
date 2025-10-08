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

	if($_POST['idcliente'] > 0){
		$res = mysql_query("SELECT rfc FROM clientes WHERE cve='".$_POST['idcliente']."'");
		$row = mysql_fetch_array($res);
		$nomusuario = $row['rfc'];
	}
	else{
		$nomusuario = '';
	}

	//$url=split("/",$PHP_SELF);
	$url=split("/",$_SERVER["PHP_SELF"]);
	$url=array_reverse($url);
    ?>
	

	


	<!DOCTYPE html>
    <html lang="es">
    <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<title>:: Asociaci&oacute;n de centros de verificaci&oacute;n vehicular de Puebla A.C. ::</title>
	<!--<link rel="stylesheet" type="text/css" href="css/style.css" />-->
	<link rel="stylesheet" type="text/css" href="../css/style.css" />

	<link rel="stylesheet" type="text/css" href="calendar/dhtmlgoodies_calendar.css" />
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
            z-index:10000;
        }
        .clink{
    		cursor: pointer;
    	}
	</style>
	<script src="js/rutinas.js"></script>
	<link href="js/multiple-select.css" rel="stylesheet"/>
	<link rel="stylesheet" type="text/css" href="css/ui.css" />
	<!--<script src="js/jquery-1.8.0.min.js" type="text/javascript"></script>-->
    <script src="https://code.jquery.com/jquery-1.9.1.min.js" type="text/javascript"></script>
	<script src="js/jquery-ui-1.8.23.custom.min.js" type="text/javascript"></script>
	<script src="js/serializeform.js" type="text/javascript"></script>
	<script src="js/jquery.multiple.select.js" type="text/javascript"></script>
	<script src="calendar/dhtmlgoodies_calendar.js"></script>
	<script src="js/validacampo.js" type="text/javascript"></script>
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
    
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://website.verificentros.net/css/style.css">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn\'t work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->        
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>	
	</head>
	<body>
    <header id="header">
           <div id="top-menu">
               <div class="col-md-4 pull-right" id="social">
                   <!--<div id="twitter"><a href="https://twitter.com/" target="_new" id="twitter_img">Twitter</a></div>
                   <div id="facebook"><a href="https://facebook.com/" target="_new" id="facebook_img">Facebook</a></div>-->
               </div>
           </div>
           <div class="container">
               <div class="row">
                   <!--<div class="col-sm-2 logo"><img alt="logo" src="https://website.verificentros.net/img/logo.png" class="img-responsive" /></div>-->
				   <!--
                   <div class="col-sm-2 logo hidden-xs"><img alt="logo" src="https://website.verificentros.net/img/logo2.png?" class="img-responsive"></div>
                   <div class="col-sm-2 logo hidden-xs"><img alt="logo" src="https://website.verificentros.net/img/logo3.png" class="img-responsive"></div>
		   -->
                   <div class="col-sm-12 hidden-xs"><h1>Asociación de Centros de Verificación Vehicular<!-- de Puebla A.C. --></h1></div>
               </div>
           </div>
       </header>
	  <nav class="navbar navbar-default">
		 <div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-4">
				  <span class="sr-only">Toggle navigation</span>
				  <span class="icon-bar"></span>
				  <span class="icon-bar"></span>
				  <span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">Bienvenido <?php echo $nomusuario ?></a>
			  </div>
			  <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-4">
			  	
				<p class="navbar-text navbar-right" id="idreloj"><?php echo fechaLocal() ?> <?php echo horaLocal() ?></p>
				<!--<ul class="nav navbar-nav navbar-right">
					<li><a href="#" onClick="document.forma3.cmd.value=31;document.forma3.submit();"><img title="Facturas Generadas" src="images/list.jpg" height="20"></a></li>
			        <li><a href="#" onClick="document.forma3.cmd.value=21;document.forma3.submit();"><img title="Editar Datos" src="images/settings.png" height="20"></a></li>
					<li><a href="#" onClick="document.forma3.cmd.value=0;document.forma3.submit();"><img title="Cerrar Sesion" src="images/logout.png" height="20"></a></li>

			    </ul>-->
				
			</div>
		 </div>
	  </nav>   
    <form name="forma3" id="forma3" method="POST" action="login_web.php" enctype="multipart/form-data" class="form-horizontal">
		<input type="hidden" name="cmd" id="cmd">
		<input type="hidden" name="idcliente" id="idcliente" value="<?php echo $_POST['idcliente']; ?>">
	</form>
	<form name="forma" id="forma" method="POST" enctype="multipart/form-data" class="form-horizontal">
	<!-- Definicion de variables ocultas -->
		<input type="hidden" name="cmd" id="cmd">
		<input type="hidden" name="reg" id="reg">
		<input type="hidden" name="numeroPagina" id="numeroPagina" value="0">
		<input type="hidden" name="recargado" id="recargado" value="1">
	<div id="panel" class="panel"></div>    
<?php 
}



function bottom() {

?>
<footer id="footer" style="min-height: 300px;">
   <!--
        <div class="container">
            <div class="row">
                    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <h3>Cuidemos el ambiente</h3>
                <ul class="menu-widget">
                    <li><a href="https://website.verificentros.net/home/por_que_verificar">¿Por qué verificar?</a></li>
                    <li><a href="https://website.verificentros.net/home/antes_de_verificar">Antes de verificar</a></li>
                    <li><a href="https://website.verificentros.net/home/preguntas_frecuentes">Preguntas frecuentes</a></li>
                    <li><a href="https://website.verificentros.net/home/tienes_dudas">¿Tienes dudas?</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h3>Cumplamos todos</h3>
                <ul class="menu-widget">
                    <li><a href="http://verificentros.net/registro_web.php" target="_blank">Reserva tu cita</a></li>
                    <li><a href="http://verificentros.net/facturacion_web.php" target="_blank">Facturación</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h3>Formulario de contacto</h3>
                <form method="post" class="form-horizontal" id="contact">
                    <div class="form-group">
                        <div class="col-md-12">
                            <input id="inputName" name="inputName" placeholder="Nombre completo" class="form-control" type="text">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <input id="inputEmail" name="inputEmail" placeholder="Correo electrónico" class="form-control" type="text">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <textarea id="inputMessage" name="inputMessage" placeholder="Quejas, Dudas o Sugerencias" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <input type="submit" value="ENVIAR!" class="btn btn-default pull-right footer-button">
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-3">
                <h3>Verificentros Puebla</h3>
                <ul class="menu-widget">
                    <li><a href="https://website.verificentros.net/home/requisitos/#horarios">Ubica tu verificentro</a></li>
                    <li><img src="https://website.verificentros.net/img/fba.png" class="mim-bottom"><img src="https://website.verificentros.net/img/fb.png" class="mim-top"><a href="https://twitter.com/" target="_new" class="follow-widget">Facebook</a></li>
                    <li><img src="https://website.verificentros.net/img/twa.png" class="mim-bottom"><img src="https://website.verificentros.net/img/tw.png" class="mim-top"><a href="https://facebook.com/" target="_new" class="follow-widget">Twitter</a></li>
                </ul>
                
            </div>
        </div>
    </div>
            </div>
        </div>
    </footer>
    <section id="footer-menu">
            <div class="container">
                <div class="row">
                    <p class="col-md-4 pull-left"><span class="color4">Asociación de Verificentros de Puebla</span> | Derechos reservados</p>
                    
                </div>
            </div>
    </section>
	-->
    <a href="#" id="toTop" style="display: block;"><span id="toTopHover"></span>To Top</a>
    <div id="window-resizer-tooltip"><a href="#" title="Edit settings"></a><span class="tooltipTitle">Window size: </span><span class="tooltipWidth" id="winWidth"></span> x <span class="tooltipHeight" id="winHeight"></span><br><span class="tooltipTitle">Viewport size: </span><span class="tooltipWidth" id="vpWidth"></span> x <span class="tooltipHeight" id="vpHeight"></span></div>

	</body>
	<script>
		mueveReloj();
		window.onload=function(){
            if (self.screen.availWidth) {
                $("#panel").css("width",parseFloat(self.screen.availWidth)+50);
            }
            if (self.screen.availHeight) {
                $("#panel").css("height",self.screen.availHeight+1600);
            }
        }  
        $(".placas").validCampo("abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ1234567890");
	</script>
	</form>

	</html>

	
<?php
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