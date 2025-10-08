<?php

include("main2_beta.php");

if($_POST['ajax']==1){
	$res = mysql_query("SELECT cve FROM clientes WHERE usuario_web = '".$_POST['usuario']."'");
	if($row = mysql_fetch_array($res)){
		echo '1';
	}
	exit();
}
if($_POST['cmd']=="2")
{
	foreach($_POST as $k=>$v){
		if($k!='plaza' && $k!='placat' && $k!='ticket'){
			$_POST[$k] = mb_strtoupper(utf8_decode($v));
		}
	}
	mysql_query("INSERT clientes SET plaza='".$plaza."',fechayhora=NOW(),usuario='-1',usuario_web='".$_POST['usuario']."',
							contrasena_web='".$_POST['pass']."',
							rfc='".$_POST['rfc']."',nombre='".addslashes($_POST['nombre'])."',email='".$_POST['email']."',calle='".addslashes($_POST['calle'])."',
							numexterior='".addslashes($_POST['numexterior'])."',numinterior='".addslashes($_POST['numinterior'])."',colonia='".addslashes($_POST['colonia'])."',
							municipio='".addslashes($_POST['municipio'])."',estado='".addslashes($_POST['estado'])."',codigopostal='".$_POST['codigopostal']."',
							localidad='".addslashes($_POST['localidad'])."'");
	
	$cliente_id = mysql_insert_id();
	if($_POST['cuenta'] != ''){
		mysql_query("INSERT clientes_cuentas SET cliente = '$cliente_id',cuenta='".$_POST['cuenta']."'");
	}
	$_POST['cmd'] = 0;
}

if($_POST['cmd'] == 32){
	include("imp_factura.php");
	$datos[0] = $_POST['plazadescarga'];
	$datos[1] = $_POST['cvedescarga'];
	$archivo='factura';
	$postfijo='';
	$nombre='Factura';
	$zip = new ZipArchive();
	if($zip->open("cfdi/zip".$archivo."_".$datos[0]."_".$datos[1].".zip",ZipArchive::CREATE)){
		generaFacturaPdf($datos[0], $datos[1], 0);
		$zip->addFile("cfdi/comprobantes/".$archivo."_".$datos[0]."_".$datos[1].".pdf",$nombre.".pdf");
		$zip->addFile("cfdi/comprobantes/cfdi".$postfijo."_".$datos[0]."_".$datos[1].".xml",$nombre.".xml");
		$zip->close(); 
	    if(file_exists("cfdi/zip".$archivo."_".$datos[0]."_".$datos[1].".zip")){ 
	        header('Content-type: "application/zip"'); 
	        header('Content-Disposition: attachment; filename="'.$nombre.'_'.$datos[1].'.zip"'); 
	        readfile("cfdi/zip".$archivo."_".$datos[0]."_".$datos[1].".zip"); 
	         
	        unlink("cfdi/zip".$archivo."_".$datos[0]."_".$datos[1].".zip"); 
	        @unlink("cfdi/comprobantes/".$archivo."_".$datos[0]."_".$datos[1].".pdf"); 
	    } 
	    else{
			echo '<h1>Ocurrio un problema al generar el archivo favor de intentarlo de nuevo</h1>';
		}
	}
	else{
		echo '<h1>Ocurrio un problema al generar el archivo favor de intentarlo de nuevo</h1>';
	}
	exit();
}

if($_POST['cmd']==22)
{
	foreach($_POST as $k=>$v){
		if($k!='plaza' && $k!='placat' && $k!='ticket'){
			$_POST[$k] = mb_strtoupper(utf8_decode($v));
		}
	}
	mysql_query("UPDATE clientes SET plaza='".$plaza."',
							contrasena_web='".$_POST['pass']."',
							rfc='".$_POST['rfc']."',nombre='".addslashes($_POST['nombre'])."',email='".$_POST['email']."',calle='".addslashes($_POST['calle'])."',
							numexterior='".addslashes($_POST['numexterior'])."',numinterior='".addslashes($_POST['numinterior'])."',colonia='".addslashes($_POST['colonia'])."',
							municipio='".addslashes($_POST['municipio'])."',estado='".addslashes($_POST['estado'])."',codigopostal='".$_POST['codigopostal']."',
							localidad='".addslashes($_POST['localidad'])."'
				WHERE cve='".$_POST['idcliente']."'");
	
	mysql_query("DELETE FROM clientes_cuentas WHERE cliente = '".$_POST['idcliente']."'");
	if($_POST['cuenta'] != ''){
		mysql_query("INSERT clientes_cuentas SET cliente = '".$_POST['idcliente']."',cuenta='".$_POST['cuenta']."'");
	}
	echo '
		<form name="forma3" id="forma3" method="POST" action="facturacion_web.php">
			<input type="hidden" name="cmd" id="cmd" value="0">
			<input type="hidden" name="fechahoracarga" value="'.date('Y-m-d H:i:s').'">
			<input type="hidden" name="idcliente" id="idcliente" value="'.$_POST['idcliente'].'">
		</form>
		<script>document.forma3.submit();</script>';
	exit();
}

if($_POST['cmd']==11){
	$res = mysql_query("SELECT cve FROM clientes WHERE usuario_web != '' AND usuario_web = '".$_POST['loginUser']."' AND contrasena_web = '".$_POST['loginPassword']."'");
	if($row = mysql_fetch_array($res)){
		echo '
		<form name="forma3" id="forma3" method="POST" action="facturacion_web.php">
			<input type="hidden" name="cmd" id="cmd" value="0">
			<input type="hidden" name="fechahoracarga" value="'.date('Y-m-d H:i:s').'">
			<input type="hidden" name="idcliente" id="idcliente" value="'.$row['cve'].'">
		</form>
		<script>document.forma3.submit();</script>';
	}
	else{
		$_POST['cmd'] = 0;
		$_POST['error'] = 2;
	}
}

if($_POST['cmd']==12){
	$res = mysql_query("SELECT contrasena_web FROM clientes WHERE usuario_web = '".$_POST['username']."' AND email = '".$_POST['email']."'");
	if($row = mysql_fetch_array($res)){
		require_once('PHPMailer-master/PHPMailerAutoload.php');
		$mail = new PHPMailer;		
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.mailgun.org';                     // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'postmaster@verificentrosgp1.net';   // SMTP username
		$mail->Password = 'a4f9c1bb34ed1c639a0cdeedb5f79aea';                           // SMTP password
		$mail->SMTPSecure = 'tls';    
		$mail->From = "verificentros@verificentrosgp1.net";
		$mail->FromName = "Facturacion GP1";
		$mail->Subject = "Recuperacion de Contraseña";
		$mail->Body = "La contraseña para el usuario ".$_POST['username']." es: ".$row['contrasena_web'];
		$mail->AddAddress($_POST['email']);
		$mail->Send();
	}
	else{
		$_POST['cmd'] = 0;
		$_POST['error'] = 3;
	}
}

?>
 <html lang="es">
    <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<title>:: Asociaci&oacute;n de centros de verificaci&oacute;n vehicular de Puebla A.C. ::</title>
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
    <style>
		.panel2 {
            background:#DFE6EF;
            top:0px;
            left:0px;
            display:none;
            position:absolute;
            filter:alpha(opacity=40);
            opacity:.4;
            z-index:10000;
        }
	</style>
</head>
<body>
	<div id="panel" class="panel2"></div>   
	<form name="forma2" id="forma2" method="POST" action="login_web.php" enctype="multipart/form-data">
	<input type="hidden" name="cmd" id="cmd" value="0">
	</form>
	<header id="header">
           <div class="container">
               <div class="row">
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
				<a class="navbar-brand" href="#">Bienvenido <?php echo $SESSION['NomUsuario'] ?></a>
			  </div>
			  <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-4">
				<p class="navbar-text navbar-right" id="idreloj"><?php echo fechaLocal() ?> <?php echo horaLocal() ?></p>
			</div>
		 </div>
	  </nav>   
<?php

if($_POST['cmd']==31){
?>
<script src="https://code.jquery.com/jquery-1.9.1.min.js" type="text/javascript"></script>
<script type="text/javascript" src="js/datatable/js/jquery.dataTables.js?<?php echo date('H:i:s'); ?>"></script>
<link rel="stylesheet" href="js/datatable/css/jquery.dataTables.css?<?php echo date('H:i:s'); ?>">
<form name="forma3" id="forma3" method="POST" action="facturacion_web.php">
	<input type="hidden" name="cmd" id="cmd" value="0">
	<input type="hidden" name="fechahoracarga" value="<?php echo date("Y-m-d H:i:s"); ?>">
	<input type="hidden" name="idcliente" id="idcliente" value="<?php echo $_POST['idcliente']; ?>">
</form>
<form name="forma" id="forma" method="POST" target="_blank" action="login_web.php" enctype="multipart/form-data">
	<input type="hidden" name="cmd" value="32">
	<input type="hidden" name="plazadescarga" value="">
	<input type="hidden" name="cvedescarga" value="">
	<input type="hidden" name="idcliente" value="<?php echo $_POST['idcliente']; ?>">

<section class="col-sm-9">
    <h1 class="text-center">Facturas Generadas</h1>
	
	<div class="row">

		<div class="col-sm-12">
			<input type="button" id="cancelar" class="btn btn-info" value="Volver" onClick="document.forma3.cmd.value=0;document.forma3.submit();"></p><br /><br />					
		</div>
		<!-- col izq -->
		
	</div>

	<table id="listado" class="display table-grid" cellspacing="0">
	<thead>
		<tr><th>Descargar</th><th>Folio</th><th>Fecha</th><th>UUID</th><th>Subtotal</th><th>I.V.A.</th><th>Total</th></tr>
	</thead>
	<tbody>
	<?php
	$res = mysql_query("SELECT * FROM facturas WHERE cliente = '".$_POST['idcliente']."' AND respuesta1!='' AND estatus!='C'");
	while($row = mysql_fetch_array($res)){
		echo '<tr>';
		echo '<td align="center"><span style="cursosr:pointer;" onClick="document.forma.plazadescarga.value='.$row['plaza'].';document.forma.cvedescarga.value='.$row['cve'].';">
		<img src="images/zip_grande.png" border="0" width="30px" height="30px" title="Descargar"></span></td>';
		echo '<td align="center">'.$row['serie'].' '.$row['folio'].'</td>';
		echo '<td align="center">'.$row['fecha'].' '.$row['hora'].'</td>';
		echo '<td align="center">'.$row['uuid'].'</td>';
		echo '<td align="right">'.number_format($row['subtotal'],2).'</td>';
		echo '<td align="right">'.number_format($row['iva'],2).'</td>';
		echo '<td align="right">'.number_format($row['total'],2).'</td>';
		echo '</tr>';
	}
	
	?>
	</tbody>

	</table>
	<script>
		$("#listado").DataTable();
	</script>';
	
</section>
</form>

<?php
}

if($_POST['cmd']==21)
{
	$res = mysql_query("SELECT * FROM clientes WHERE cve='".$_POST['idcliente']."'");
	$row = mysql_fetch_array($res);
	$res1=mysql_query("SELECT * FROM clientes_cuentas WHERE cliente='".$row['cve']."' AND cuenta!=''");
	$row1=mysql_fetch_array($res1);
?>
<script src="https://code.jquery.com/jquery-1.9.1.min.js" type="text/javascript"></script>
<form name="forma3" id="forma3" method="POST" action="facturacion_web.php">
	<input type="hidden" name="cmd" id="cmd" value="0">
	<input type="hidden" name="fechahoracarga" value="<?php echo date("Y-m-d H:i:s"); ?>">
	<input type="hidden" name="idcliente" id="idcliente" value="<?php echo $_POST['idcliente']; ?>">
</form>
<form name="forma" id="forma" method="POST" action="login_web.php" enctype="multipart/form-data">
	<input type="hidden" name="cmd" value="22">
	<input type="hidden" name="idcliente" value="<?php echo $_POST['idcliente']; ?>">

<section class="col-sm-9">
    <h1 class="text-center">Editar</h1>
	
	<div class="row">
		<!-- col izq -->
		<div class="col-sm-6">

			<div class="form-group">
				<label for="usuario" class="col-sm-5 control-label">Usuario</label>
				<div class="col-sm-7">
					<input type="text" class="required form-control input-sm" id="usuario" name="usuario" value="<?php echo $row['usuario_web'];?>" disabled>
				</div>
			</div>

			<div class="form-group">
				<label for="pass" class="col-sm-5 control-label">Contraseña</label>
				<div class="col-sm-7">
					<input type="password" class="required form-control input-sm" id="pass" name="pass" value="<?php echo $row['contrasena_web'];?>">
				</div>
			</div>

			<div class="form-group">
				<label for="nombre" class="col-sm-5 control-label">Razon Social</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="nombre" value="<?php echo $row['nombre'];?>" name="nombre" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group">
				<label for="email" class="col-sm-5 control-label">Correo electrónico</label>
				<div class="col-sm-7">
					<input type="text" class="required form-control input-sm" id="email" name="email" value="<?php echo $row['email'];?>">
					<br><span class="text-muted"><small>Si desea entrar mas de un email, solo separelos por comas</small></span>			
				</div>
			</div>
			
			
			<div class="form-group">
				<label for="rfc" class="col-sm-5 control-label">RFC</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="rfc" name="rfc" maxlength="13" value="<?php echo $row['rfc'];?>" onKeyUp="this.value=this.value.toUpperCase();">
					<br><span class="text-muted"><small>El RFC es sin espacios ni guiones, con homoclave</small></span>
				</div>
			</div>

			<div class="form-group ccuenta">
				<label for="rfc" class="col-sm-5 control-label">Cuenta de Pago</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="cuenta" name="cuenta" value="<?php echo $row1['cuenta'];?>" maxlength="4" onKeyUp="this.value=this.value.toUpperCase();">
					<br><span class="text-muted"><small>Últimos 4 digitos de la cuenta de pago</small></span>
				</div>
			</div>
			
		</div>
		
		<!-- col der -->
		<div class="col-sm-6">
			

			<div class="form-group">
				<label for="calle" class="col-sm-5 control-label">Calle</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="calle" name="calle" value="<?php echo $row['calle'];?>" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group">
				<label for="numexterior" class="col-sm-5 control-label">Número exterior</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="numexterior" name="numexterior" value="<?php echo $row['numexterior'];?>" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group" style="display:none;">
				<label for="numinterior" class="col-sm-5 control-label">Número interior</label>
				<div class="col-sm-7">
					<input type="text" class="mayusculas form-control input-sm" id="numinterior" name="numeinterior" value="<?php echo $row['numinterior'];?>" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group">
				<label for="colonia" class="col-sm-5 control-label">Colonia</label>
				<div class="col-sm-7">
					<input type="text" class="mayusculas form-control input-sm" id="colonia" name="colonia" value="<?php echo $row['colonia'];?>" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group">
				<label for="localidad" class="col-sm-5 control-label">Localidad</label>
				<div class="col-sm-7">
					<input type="text" class="mayusculas form-control input-sm" id="localidad" name="localidad" value="<?php echo $row['localidad'];?>" onKeyUp="this.value=this.value.toUpperCase();">
					<span class="text-muted"><small>Esta prohibido usar Distrito Federal</small></span>
				</div>
			</div>
			
			<div class="form-group">
				<label for="municipio" class="col-sm-5 control-label">Municipio</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="municipio" name="municipio" value="<?php echo $row['municipio'];?>" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group">
				<label for="estado" class="col-sm-5 control-label">Estado</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="estado" name="estado" value="<?php echo $row['estado'];?>" onKeyUp="this.value=this.value.toUpperCase();">
					<span class="text-muted"><small>Esta prohibido usar Distrito Federal</small></span>
				</div>
			</div>
		
			<div class="form-group">
				<label for="codigopostal" class="col-sm-5 control-label">Código Postal</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="codigopostal" name="codigopostal" value="<?php echo $row['codigopostal'];?>" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
		</div>

		
		<div class="col-sm-12">
			<p class="text-center"><input type="button" id="aceptar" class="btn btn-success" value="Guardar" onClick="$('#panel').show();if(validarRFC()){validar('');} else{ $('#panel').hide(); alert('RFC invalido');}">

			&nbsp;&nbsp;<input type="button" id="cancelar" class="btn btn-danger" value="Volver" onClick="document.forma3.cmd.value=0;document.forma3.submit();"></p><br /><br />					
		</div>
	</div>
	
</section>
</form>
<script>
function validarRFC(){
        var ValidChars2 = "0123456789";
        var ValidChars1 = "abcdefghijklmnÃ±opqrstuvwxyzABCDEFGHIJKLMNÃ‘OPQRSTUVWXYZ&";
        var cadena=document.getElementById("rfc").value;
        correcto = true;
        if(cadena.length!=13 && cadena.length!=12){
            correcto = false;
        }
        else{
            if(cadena.length==12)
                resta=1;
            else
                resta=0;
            for(i=0;i<cadena.length;i++) {
                digito=cadena.charAt(i);
                if (i<(4-resta) && ValidChars1.indexOf(digito) == -1){
                    correcto = false;
                }
                if (i>=(4-resta) && i<(10-resta) && ValidChars2.indexOf(digito) == -1){
                    correcto = false;
                }
                if (i>=(10-resta) && ValidChars1.indexOf(digito) == -1 && ValidChars2.indexOf(digito) == -1){
                    correcto = false;
                }
            }
        }
        return correcto;
    }



    function validar(){
    		if(document.getElementById("pass").value==""){
                $('#panel').hide();
                alert("Necesita ingresar la contraseña");
            }
            else if(document.getElementById("nombre").value==""){
                $('#panel').hide();
                alert("Necesita ingresar la razon social");
            }
            else if(document.getElementById("email").value==""){
                $('#panel').hide();
                alert("Necesita ingresar el email");
            }
            else if(document.forma.rfc.value==""){
                $('#panel').hide();
                alert("Necesita ingresar el rfc");
            }
            else if(document.forma.calle.value==""){
                $('#panel').hide();
                alert("Necesita ingresar la calle");
            }
            else if($.trim(document.forma.localidad.value)=="D.F." || $.trim(document.forma.localidad.value)=="D. F." || $.trim(document.forma.localidad.value).indexOf("DISTRITO FEDERAL")!=-1){
                $('#panel').hide();
                alert("En la localidad puede ir distrito federal");
            }
            else if(document.forma.municipio.value==""){
                $('#panel').hide();
                alert("Necesita ingresar el municipio");
            }
            else if(document.forma.estado.value==""){
                $('#panel').hide();
                alert("Necesita ingresar el estado");
            }
            else if($.trim(document.forma.estado.value)=="D.F." || $.trim(document.forma.estado.value)=="D. F." || $.trim(document.forma.estado.value).indexOf("DISTRITO FEDERAL")!=-1){
                $('#panel').hide();
                alert("En el estado no puede ir distrito federal");
            }
            else if(document.forma.codigopostal.value==""){
                $('#panel').hide();
                alert("Necesita ingresar el código postal");
            }
            else{
                document.forma.cmd.value=22;
                document.forma.submit();
            }
    }
</script>

<?php
}

if($_POST['cmd']==1)
{
?>
<script src="https://code.jquery.com/jquery-1.9.1.min.js" type="text/javascript"></script>
<form name="forma" id="forma" method="POST" action="login_web.php" enctype="multipart/form-data">
	<input type="hidden" name="cmd" value="2">
<section class="col-sm-9">
    <h1 class="text-center">Registrarse</h1>
	
	<div class="row">
		<!-- col izq -->
		<div class="col-sm-6">

			<div class="form-group">
				<label for="usuario" class="col-sm-5 control-label">Usuario</label>
				<div class="col-sm-7">
					<input type="text" class="required form-control input-sm" id="usuario" name="usuario">
				</div>
			</div>

			<div class="form-group">
				<label for="pass" class="col-sm-5 control-label">Contraseña</label>
				<div class="col-sm-7">
					<input type="password" class="required form-control input-sm" id="pass" name="pass">
				</div>
			</div>

			<div class="form-group">
				<label for="nombre" class="col-sm-5 control-label">Razon Social</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="nombre" name="nombre" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group">
				<label for="email" class="col-sm-5 control-label">Correo electrónico</label>
				<div class="col-sm-7">
					<input type="text" class="required form-control input-sm" id="email" name="email">
					<br><span class="text-muted"><small>Si desea entrar mas de un email, solo separelos por comas</small></span>			
				</div>
			</div>
			
			
			<div class="form-group">
				<label for="rfc" class="col-sm-5 control-label">RFC</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="rfc" name="rfc" maxlength="13" onKeyUp="this.value=this.value.toUpperCase();">
					<br><span class="text-muted"><small>El RFC es sin espacios ni guiones, con homoclave</small></span>
				</div>
			</div>

			<div class="form-group ccuenta">
				<label for="rfc" class="col-sm-5 control-label">Cuenta de Pago</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="cuenta" name="cuenta" maxlength="4" onKeyUp="this.value=this.value.toUpperCase();">
					<br><span class="text-muted"><small>Últimos 4 digitos de la cuenta de pago</small></span>
				</div>
			</div>
			
		</div>
		
		<!-- col der -->
		<div class="col-sm-6">
			

			<div class="form-group">
				<label for="calle" class="col-sm-5 control-label">Calle</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="calle" name="calle" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group">
				<label for="numexterior" class="col-sm-5 control-label">Número exterior</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="numexterior" name="numexterior" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group" style="display:none;">
				<label for="numinterior" class="col-sm-5 control-label">Número interior</label>
				<div class="col-sm-7">
					<input type="text" class="mayusculas form-control input-sm" id="numinterior" name="numeinterior" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group">
				<label for="colonia" class="col-sm-5 control-label">Colonia</label>
				<div class="col-sm-7">
					<input type="text" class="mayusculas form-control input-sm" id="colonia" name="colonia" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group">
				<label for="localidad" class="col-sm-5 control-label">Localidad</label>
				<div class="col-sm-7">
					<input type="text" class="mayusculas form-control input-sm" id="localidad" name="localidad" onKeyUp="this.value=this.value.toUpperCase();">
					<span class="text-muted"><small>Esta prohibido usar Distrito Federal</small></span>
				</div>
			</div>
			
			<div class="form-group">
				<label for="municipio" class="col-sm-5 control-label">Municipio</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="municipio" name="municipio" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
			<div class="form-group">
				<label for="estado" class="col-sm-5 control-label">Estado</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="estado" name="estado" onKeyUp="this.value=this.value.toUpperCase();">
					<span class="text-muted"><small>Esta prohibido usar Distrito Federal</small></span>
				</div>
			</div>
		
			<div class="form-group">
				<label for="codigopostal" class="col-sm-5 control-label">Código Postal</label>
				<div class="col-sm-7">
					<input type="text" class="required mayusculas form-control input-sm" id="codigopostal" name="codigopostal" onKeyUp="this.value=this.value.toUpperCase();">
				</div>
			</div>
			
		</div>

		
		<div class="col-sm-12">
			<p class="text-center"><input type="button" id="aceptar" class="btn btn-success" value="Registrar" onClick="$('#panel').show();if(validarRFC()){validar('');} else{ $('#panel').hide(); alert('RFC invalido');}">

			&nbsp;&nbsp;<input type="button" id="cancelar" class="btn btn-danger" value="Cancelar" onClick="document.forma2.cmd.value=0;document.forma2.submit();"></p><br /><br />					
		</div>
	</div>
	
</section>
</form>
<script>
function validarRFC(){
        var ValidChars2 = "0123456789";
        var ValidChars1 = "abcdefghijklmnÃ±opqrstuvwxyzABCDEFGHIJKLMNÃ‘OPQRSTUVWXYZ&";
        var cadena=document.getElementById("rfc").value;
        correcto = true;
        if(cadena.length!=13 && cadena.length!=12){
            correcto = false;
        }
        else{
            if(cadena.length==12)
                resta=1;
            else
                resta=0;
            for(i=0;i<cadena.length;i++) {
                digito=cadena.charAt(i);
                if (i<(4-resta) && ValidChars1.indexOf(digito) == -1){
                    correcto = false;
                }
                if (i>=(4-resta) && i<(10-resta) && ValidChars2.indexOf(digito) == -1){
                    correcto = false;
                }
                if (i>=(10-resta) && ValidChars1.indexOf(digito) == -1 && ValidChars2.indexOf(digito) == -1){
                    correcto = false;
                }
            }
        }
        return correcto;
    }

    function validarUsuario(){
    	regresar = false;
    	$.ajax({
          url: "login_web.php",
          type: "POST",
          async: false,
          data: {
            usuario: $.trim(document.forma.usuario.value),
            ajax: 1,
          },
            success: function(data) {   
            	if(data == '1'){
            		alert('El usuario ya existe');
            	}   
            	else{
            		regresar = true;
            	}
            }
        });
        return regresar;
    }

    function validar(){
    		if(document.getElementById("usuario").value==""){
                $('#panel').hide();
                alert("Necesita ingresar el usuario");
            }
            else if(!validarUsuario()){
            	alert("El usuario ya existe");
            }
            else if(document.getElementById("pass").value==""){
                $('#panel').hide();
                alert("Necesita ingresar la contraseña");
            }
            else if(document.getElementById("nombre").value==""){
                $('#panel').hide();
                alert("Necesita ingresar la razon social");
            }
            else if(document.getElementById("email").value==""){
                $('#panel').hide();
                alert("Necesita ingresar el email");
            }
            else if(document.forma.rfc.value==""){
                $('#panel').hide();
                alert("Necesita ingresar el rfc");
            }
            else if(document.forma.calle.value==""){
                $('#panel').hide();
                alert("Necesita ingresar la calle");
            }
            else if($.trim(document.forma.localidad.value)=="D.F." || $.trim(document.forma.localidad.value)=="D. F." || $.trim(document.forma.localidad.value).indexOf("DISTRITO FEDERAL")!=-1){
                $('#panel').hide();
                alert("En la localidad puede ir distrito federal");
            }
            else if(document.forma.municipio.value==""){
                $('#panel').hide();
                alert("Necesita ingresar el municipio");
            }
            else if(document.forma.estado.value==""){
                $('#panel').hide();
                alert("Necesita ingresar el estado");
            }
            else if($.trim(document.forma.estado.value)=="D.F." || $.trim(document.forma.estado.value)=="D. F." || $.trim(document.forma.estado.value).indexOf("DISTRITO FEDERAL")!=-1){
                $('#panel').hide();
                alert("En el estado no puede ir distrito federal");
            }
            else if(document.forma.codigopostal.value==""){
                $('#panel').hide();
                alert("Necesita ingresar el código postal");
            }
            else{
                document.forma.cmd.value=2;
                document.forma.submit();
            }
    }
</script>

<?php
}

//top();

if($_POST['cmd']==0){
?>
<link href="js/multiple-select.css" rel="stylesheet"/>
	<link rel="stylesheet" type="text/css" href="js/jquery/jquery-ui.css" />
	<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-1.9.1.min.js" type="text/javascript"></script>
	<script src="js/jquery/jquery-ui.js" type="text/javascript"></script>
	<script src="js/serializeform.js" type="text/javascript"></script>
	<script src="js/jquery.multiple.select.js" type="text/javascript"></script>
	<script src="js/validacampo.js" type="text/javascript"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>	
	<style>
		.panel-login {
			border-color: #ccc;
			-webkit-box-shadow: 0px 2px 3px 0px rgba(0,0,0,0.2);
			-moz-box-shadow: 0px 2px 3px 0px rgba(0,0,0,0.2);
			box-shadow: 0px 2px 3px 0px rgba(0,0,0,0.2);
		}
		.panel-login>.panel-heading {
			color: #00415d;
			background-color: #fff;
			border-color: #fff;
			text-align:center;
		}
		.panel-login>.panel-heading a{
			text-decoration: none;
			color: #666;
			font-weight: bold;
			font-size: 15px;
			-webkit-transition: all 0.1s linear;
			-moz-transition: all 0.1s linear;
			transition: all 0.1s linear;
		}
		.panel-login>.panel-heading a.active{
			color: #029f5b;
			font-size: 18px;
		}
		.panel-login>.panel-heading hr{
			margin-top: 10px;
			margin-bottom: 0px;
			clear: both;
			border: 0;
			height: 1px;
			background-image: -webkit-linear-gradient(left,rgba(0, 0, 0, 0),rgba(0, 0, 0, 0.15),rgba(0, 0, 0, 0));
			background-image: -moz-linear-gradient(left,rgba(0,0,0,0),rgba(0,0,0,0.15),rgba(0,0,0,0));
			background-image: -ms-linear-gradient(left,rgba(0,0,0,0),rgba(0,0,0,0.15),rgba(0,0,0,0));
			background-image: -o-linear-gradient(left,rgba(0,0,0,0),rgba(0,0,0,0.15),rgba(0,0,0,0));
		}
		.panel-login input[type="text"],.panel-login input[type="email"],.panel-login input[type="password"] {
			height: 45px;
			border: 1px solid #ddd;
			font-size: 16px;
			-webkit-transition: all 0.1s linear;
			-moz-transition: all 0.1s linear;
			transition: all 0.1s linear;
		}
		.panel-login input:hover,
		.panel-login input:focus {
			outline:none;
			-webkit-box-shadow: none;
			-moz-box-shadow: none;
			box-shadow: none;
			border-color: #ccc;
		}
		.btn-login {
			background-color: #59B2E0;
			outline: none;
			color: #fff;
			font-size: 14px;
			height: auto;
			font-weight: normal;
			padding: 14px 0;
			text-transform: uppercase;
			border-color: #59B2E6;
		}
		.btn-login:hover,
		.btn-login:focus {
			color: #fff;
			background-color: #53A3CD;
			border-color: #53A3CD;
		}
		.forgot-password {
			text-decoration: underline;
			color: #888;
		}
		.forgot-password:hover,
		.forgot-password:focus {
			text-decoration: underline;
			color: #666;
		}

		.btn-register {
			background-color: #1CB94E;
			outline: none;
			color: #fff;
			font-size: 14px;
			height: auto;
			font-weight: normal;
			padding: 14px 0;
			text-transform: uppercase;
			border-color: #1CB94A;
		}
		.btn-register:hover,
		.btn-register:focus {
			color: #fff;
			background-color: #1CA347;
			border-color: #1CA347;
		}
	</style>
	<script>
		$(function() {

		    $('#login-form-link').click(function(e) {
				$("#login-form").delay(100).fadeIn(100);
		 		$("#register-form").fadeOut(100);
				$('#register-form-link').removeClass('active');
				$(this).addClass('active');
				e.preventDefault();
			});
			$('#register-form-link').click(function(e) {
				$("#register-form").delay(100).fadeIn(100);
		 		$("#login-form").fadeOut(100);
				$('#login-form-link').removeClass('active');
				$(this).addClass('active');
				e.preventDefault();
			});

		});
	</script>
<div class="container">
	<div class="row">
		<div class="col-md-6 col-md-offset-3">
			<div class="panel panel-login">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-6">
							<a href="#" class="active" id="login-form-link">Acceso</a>
						</div>
						<div class="col-xs-6">
							<a href="#" id="register-form-link">Recuperar Contraseña</a>
						</div>
					</div>
					<hr>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-lg-12">
							<form id="login-form" action="login_web.php" method="post" role="form" style="display: block;">
								<input type="hidden" name="cmd" value="11">
								<div class="form-group">
									<input type="text" name="loginUser" id="loginUser" tabindex="1" class="form-control" placeholder="Usuario" value="">
								</div>
								<div class="form-group">
									<input type="password" name="loginPassword" id="loginPassword" tabindex="2" class="form-control" placeholder="Contraseña">
								</div>
								<!--<div class="form-group text-center">
									<input type="checkbox" tabindex="3" class="" name="remember" id="remember">
									<label for="remember"> Remember Me</label>
								</div>-->
								<div class="form-group">
									<div class="row">
										<div class="col-sm-6 col-sm-offset-3">
											<input type="submit" name="login-submit" id="login-submit" tabindex="4" class="form-control btn btn-login" value="Accesar">
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-lg-12">
											<div class="text-center">
												<a href="#" onClick="document.forma2.cmd.value=1;document.forma2.submit();" tabindex="5" class="forgot-password">Registrarse</a>
											</div>
										</div>
									</div>
								</div>
							</form>
							<form id="register-form" action="login_web.php" method="post" role="form" style="display: none;">
								<input type="hidden" name="cmd" value="12">
								<div class="form-group">
									<input type="text" name="username" id="username" tabindex="1" class="form-control" placeholder="Username" value="">
								</div>
								<div class="form-group">
									<input type="email" name="email" id="email" tabindex="1" class="form-control" placeholder="Email Address" value="">
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-sm-6 col-sm-offset-3">
											<input type="submit" onClick="alert('Le llegará al correo el password del usuario');" name="register-submit" id="register-submit" tabindex="4" class="form-control btn btn-register" value="Recuperar">
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>



<?php
	if($_POST['error'] == 1){
		echo '<script>alert("Se ha cerrado su sesion por inactividad");</script>';
	}
	elseif($_POST['error'] == 2){
		echo '<script>alert("Error en el usuario o contraseña");</script>';
	}
	elseif($_POST['error'] == 3){
		echo '<script>alert("Error en el usuario o email");</script>';
	}
}
bottom();
?>
<script>
window.onload=function(){
            if (self.screen.availWidth) {
                $("#panel").css("width",parseFloat(self.screen.availWidth)+50);
            }
            if (self.screen.availHeight) {
                $("#panel").css("height",self.screen.availHeight+1600);
            }
        }  
$("#panel").show();
alert("Cerrado por mantenimiento, disculpe las molestias");
</script>
</body>
</html>