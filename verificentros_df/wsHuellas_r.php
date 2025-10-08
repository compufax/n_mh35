<?php
require_once("nusoap/nusoap.php");
set_time_limit(0);
$base='enero_aaz';
$namespace = "http://verificentros.net/huellaservices";
// create a new soap server
$server = new soap_server();
// configure our WSDL
$server->configureWSDL("wshuella");
// set our namespace
$server->wsdl->schemaTargetNamespace = $namespace;
// Get our posted data if the service is being consumed
$server->wsdl->addComplexType(
    'persona',
    'complexType',
    'struct',
    'all',
    '',
    array(
		'cve'         => array('name'=>'cve',      'type'=>'xsd:integer'),
		'clave'       => array('name'=>'clave',    'type'=>'xsd:integer'),
		'apaterno'    => array('name'=>'apaterno', 'type'=>'xsd:string'),
		'amaterno'    => array('name'=>'amaterno', 'type'=>'xsd:string'),
		'nombre'      => array('name'=>'nombre',   'type'=>'xsd:string'),
		'foto'        => array('name'=>'foto',   'type'=>'xsd:string'),
		'huella'      => array('name'=>'huella',   'type'=>'xsd:string')
	)
);
$server->wsdl->addComplexType(
    'personas',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:persona[]')),
    'tns:persona'
);
$server->wsdl->addComplexType(
    'Response',
    'complexType',
    'struct',
    'all',
    '',
    array(
		'resultado'       => array('name'=>'resultado',  'type'=>'xsd:boolean'),
		'personas'        => array('name'=>'personas',   'type'=>'tns:personas'),
		'mensaje'         => array('name'=>'mensaje',    'type'=>'xsd:string')
	)
);
// registar WebMethod para Consultar los operadores
$server->register(
                // nombre del metodo
                'ConsultarPersonas',
                // lista de parametros
                array('usuario'=>'xsd:string','password'=>'xsd:string','id'=>'xsd:integer','empresa'=>'xsd:integer'), 
                // valores de return
                array('return'=>'tns:Response'),
                // namespace
                $namespace,
                // soapaction: (use default)
                false,
                // style: rpc or document
                'rpc',
                // use: encoded or literal
                'encoded',
                // descripcion: documentacion del metodo
                'Regresa los registros de las personas sin huella registrada'
			);
// registar WebMethod para Consultar los operadores
$server->register(
                // nombre del metodo
                'ConsultarHuellas',
                // lista de parametros
                array('usuario'=>'xsd:string','password'=>'xsd:string','id'=>'xsd:integer','empresa'=>'xsd:integer'), 
                // valores de return
                array('return'=>'tns:Response'),
                // namespace
                $namespace,
                // soapaction: (use default)
                false,
                // style: rpc or document
                'rpc',
                // use: encoded or literal
                'encoded',
                // descripcion: documentacion del metodo
                'Regresa el registro de la siguiente persona con huella registrada en base al id'
			);
// registar WebMethod para registrar la huella
$server->register(
                // nombre del metodo
                'RegistraHuella',
                // lista de parametros
                array('usuario'=>'xsd:string','password'=>'xsd:string','id'=>'xsd:integer','huella'=>'xsd:string','empresa'=>'xsd:integer'), 
                // valores de return
                array('return'=>'tns:Response'),
                // namespace
                $namespace,
                // soapaction: (use default)
                false,
                // style: rpc or document
                'rpc',
                // use: encoded or literal
                'encoded',
                // descripcion: documentacion del metodo
                'Registra la huella de la persona indicada en el id'
			);
// registar WebMethod para Consultar los operadores
$server->register(
                // nombre del metodo
                'Ping',
                // lista de parametros
                array('usuario'=>'xsd:string','password'=>'xsd:string','serie'=>'xsd:string'), 
                // valores de return
                array('return'=>'tns:Response'),
                // namespace
                $namespace,
                // soapaction: (use default)
                false,
                // style: rpc or document
                'rpc',
                // use: encoded or literal
                'encoded',
                // descripcion: documentacion del metodo
                'Regresa un mensaje Pong al cliente'
			);
// registar WebMethod para Consultar los operadores
$server->register(
                // nombre del metodo
                'RegistraChecada',
                // lista de parametros
                array('usuario'=>'xsd:string','password'=>'xsd:string','lector'=>'xsd:integer','operador'=>'xsd:integer'), 
                // valores de return
                array('return'=>'tns:Response'),
                // namespace
                $namespace,
                // soapaction: (use default)
                false,
                // style: rpc or document
                'rpc',
                // use: encoded or literal
                'encoded',
                // descripcion: documentacion del metodo
                'Regresa un mensaje Pong al cliente'
			);
function ConsultarPersonas($usuario, $password, $id, $empresa = 4)
{
	global $base;
	$respuesta['resultado']=false;
	$respuesta['mensaje']='';
	$strcnn=ConectarDB();
	if($strcnn!="OK")
		$respuesta['mensaje']=$strcnn;
	if($respuesta['mensaje']=='')
	{
		if($usuario!='usrwebservices')
			$respuesta['mensaje']='Usuario invalido';
	}
	if($respuesta['mensaje']=='')
	{
		if($password!='usrw3bs3rv1c3s')
			$respuesta['mensaje']='Password invalido';
	}
	if($respuesta['mensaje']=='')
	{
		//Tomar la informacion de la tabla
		$query="SELECT cve,cve as credencial,nombre,huella FROM personal where 1 ";
		if($id>0)
			$query.=" and cve='$id'";
		else
			$query.=" and huella=''"; 
		$rs = mysql_query($query);
		$i=0;
		$aregistros=array();
		while($row=mysql_fetch_array($rs))
		{
			$aregistros[$i]['cve']        =$row['cve'];
			$aregistros[$i]['clave']      =$row['credencial'];
			$aregistros[$i]['apaterno']   =$row['apaterno'];
			$aregistros[$i]['amaterno']   =$row['amaterno'];
			$aregistros[$i]['nombre']     =$row['nombre'];
			//Verificar la Foto
			$nomfoto="imgpersonal/foto{$row['cve']}.jpg";
			if(file_exists ( $nomfoto ))
				$aregistros[$i]['foto']     =base64_encode(file_get_contents($nomfoto));
			else
				$aregistros[$i]['foto']     ="";
			$aregistros[$i]['huella']     =$row['huella'];
			$i++;
		}
		$respuesta['personas']=$aregistros;
		$respuesta['resultado']=true;
	}
	return $respuesta;
}
function ConsultarHuellas($usuario, $password, $id, $empresa = 4)
{
	global $base;
	$respuesta['resultado']=false;
	$respuesta['mensaje']='';
	$strcnn=ConectarDB();
	if($strcnn!="OK")
		$respuesta['mensaje']=$strcnn;
	if($respuesta['mensaje']=='')
	{
		if($usuario!='usrwebservices')
			$respuesta['mensaje']='Usuario invalido';
	}
	if($respuesta['mensaje']=='')
	{
		if($password!='usrw3bs3rv1c3s')
			$respuesta['mensaje']='Password invalido';
	}
	if($respuesta['mensaje']=='')
	{
		$aregistros=array();
		//Tomar la informacion de la tabla
		$query="SELECT cve,cve as credencial,nombre,huella FROM personal where cve>'$id' and huella<>'' order by cve limit 1"; 
		$rs = mysql_query($query);
		if($row=mysql_fetch_array($rs))
		{
			$i=0;
			$aregistros[$i]['cve']        =$row['cve'];
			$aregistros[$i]['clave']      =$row['credencial'];
			$aregistros[$i]['apaterno']   =$row['apaterno'];
			$aregistros[$i]['amaterno']   =$row['amaterno'];
			$aregistros[$i]['nombre']     =$row['nombre'];
			//Verificar la Foto
			$nomfoto="imgpersonal/foto{$row['cve']}.jpg";
			if(file_exists ( $nomfoto ))
				$aregistros[$i]['foto']     =base64_encode(file_get_contents($nomfoto));
			else
				$aregistros[$i]['foto']     ="";
			$aregistros[$i]['huella']     =$row['huella'];
		}
		$respuesta['personas']=$aregistros;
		$respuesta['resultado']=true;
	}
	return $respuesta;
}
function RegistraHuella($usuario, $password, $id, $huella, $empresa = 4)
{
	global $base;
	$respuesta['resultado']=false;
	$respuesta['mensaje']='';
	$strcnn=ConectarDB();
	if($strcnn!="OK")
		$respuesta['mensaje']=$strcnn;
	if($respuesta['mensaje']=='')
	{
		if($usuario!='usrwebservices')
			$respuesta['mensaje']='Usuario invalido';
	}
	if($respuesta['mensaje']=='')
	{
		if($password!='usrw3bs3rv1c3s')
			$respuesta['mensaje']='Password invalido';
	}
	if($respuesta['mensaje']=='')
	{
		//Tomar la informacion de la tabla
		$query="SELECT cve,cve as credencial,nombre FROM personal where cve='$id'";
		$rs = mysql_query($query);
		if($row=mysql_fetch_array($rs))
		{
			$row1= mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM checada_lector WHERE cvepersonal='$id'"));
			if($row1[0] <= 10)
			{
				$query="Update personal set huella='$huella' where cve='$id'";
				mysql_query($query);
				$respuesta['resultado']=true;
			}
			else
			{
				$respuesta['mensaje']='Clave de Persona bloqueada';
			}
		}
		else
			$respuesta['mensaje']='Clave de Persona no registrada';
			
		//$respuesta['mensaje']='Clave2 de Persona no registrada'.$query;
		//$respuesta['resultado']=false;
		
	}
	return $respuesta;
}
function Ping($usuario, $password, $serie)
{
	global $base;
	$strcnn=ConectarDB();
	$respuesta['resultado']=false;
	if($strcnn!="OK")
		$respuesta['mensaje']=$strcnn;
	if($respuesta['mensaje']=='')
	{
		if($usuario!='usrwebservices')
			$respuesta['mensaje']='Usuario invalido';
	}
	if($respuesta['mensaje']=='')
	{
		if($password!='usrw3bs3rv1c3s')
			$respuesta['mensaje']='Password invalido';
	}
	if($respuesta['mensaje']=='')
	{
		$query="select cve from series where serie='$serie'";
		$rs = mysql_query($query);
		if($row=mysql_fetch_array($rs))
		{
			$respuesta['mensaje']=$row['cve'];
			$respuesta['resultado']=true;
		}
		else
			$respuesta['mensaje']="Lector no registrado";
	}
	return $respuesta;
}
function RegistraChecada($usuario, $password, $lector, $operador)
{
	global $base;
	$strcnn=ConectarDB();
	$respuesta['resultado']=false;
	if($strcnn!="OK")
		$respuesta['mensaje']=$strcnn;
	if($respuesta['mensaje']=='')
	{
		if($usuario!='usrwebservices')
			$respuesta['mensaje']='Usuario invalido';
	}
	if($respuesta['mensaje']=='')
	{
		if($password!='usrw3bs3rv1c3s')
			$respuesta['mensaje']='Password invalido';
	}
	if($respuesta['mensaje']=='')
	{
		$fechahora=date('Y-m-d H:i:s');
		$res = mysql_query("SELECT HOUR(TIMEDIFF('$fechahora',fechahora)) FROM checada_lector WHERE cvepersonal='$operador' AND DATE(fechahora)='".date("Y-m-d")."' ORDER BY fechahora");
		$checadas = mysql_num_rows($res);
		if($checadas>=4){
			$respuesta['mensaje']='El empleado ya checo su salida del dia';
		}
		else{
			$row=mysql_fetch_array($res);
			if($row[0]<1 && $checadas > 0){
				$respuesta['mensaje']='Debe de pasar al menos una hora de la ultima checada para volver a hacerlo';
			}
			else{
				$query="Insert into checada_lector(cvelector,cvepersonal,fechahora) values('$lector','$operador','$fechahora')";
				mysql_query($query);
				mysql_query("UPDATE asistencia SET estatus=1 WHERE personal = '$operador' AND fecha = '".substr($fechahora,0,10)."'");
				$respuesta['mensaje']=$fechahora;
				$respuesta['resultado']=true;
			}
		}
	}
	return $respuesta;
}
function ConectarDB()
{
	$msg="OK";
	//Conexion con la base
	if (!$MySQL=@mysql_connect('localhost', 'vereficentros', 'bAllenA6##6')) {
		$t=time();
		while (time()<$t+5) {}
		if (!$MySQL=@mysql_connect('localhost', 'vereficentros', 'bAllenA6##6')) {
			$t=time();
			while (time()<$t+10) {}
			if (!$MySQL=@mysql_connect('localhost', 'vereficentros', 'bAllenA6##6')) {
				$msg='Hay problemas de comunicaci&oacute;n con la Base de datos.';
			}
		}
	}
	mysql_select_db("vereficentros");
	return $msg;
}
// Get our posted data if the service is being consumed
// otherwise leave this data blank.                
$POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) 
? $GLOBALS['HTTP_RAW_POST_DATA'] : '';

// pass our posted data (or nothing) to the soap service                    
$server->service($POST_DATA);
?>