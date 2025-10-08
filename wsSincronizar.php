<?php
date_default_timezone_set ("America/Mexico_City");
set_time_limit(0);
require_once("nusoap/nusoap.php");
$base='maron';
$namespace = "http://verificentrosgp1.net/sincronizarservices";
// create a new soap server
$server = new soap_server();
// configure our WSDL
$server->configureWSDL("wssincronizar");
// set our namespace
$server->wsdl->schemaTargetNamespace = $namespace;
//Definimos la estructura de la Respuesta
$server->wsdl->addComplexType(
    'Response',
    'complexType',
    'struct',
    'all',
    '',
    array(
		'resultado'           => array('name'=>'resultado',          'type'=>'xsd:boolean'),
		'mensaje'             => array('name'=>'mensaje',            'type'=>'xsd:string')
	)
);

$server->wsdl->addComplexType(
    'cambio',
    'complexType',
    'struct',
    'all',
    '',
    array('tipo'                => array('name'=>'tipo',                  'type'=>'xsd:integer'),
          'cambio'              => array('name'=>'cambio',              'type'=>'xsd:string'),
          'nombre'              => array('name'=>'nombre',  'type'=>'xsd:string'))
    );

$server->wsdl->addComplexType(
    'cambios',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:cambio[]')),
    'tns:cambio'
);

$server->wsdl->addComplexType(
    'listacambios',
    'complexType',
    'struct',
    'all',
    '',
    array('resultado' => array('name'=>'resultado', 'type'=>'xsd:integer'),
    'mensaje' => array('name'=>'mensaje', 'type'=>'xsd:string'),
          'datos'     => array('name'=>'datos',     'type'=>'tns:cambios'))
    );
    
$server->wsdl->addComplexType(
    'folio',
    'complexType',
    'struct',
    'all',
    '',
    array('cve'                => array('name'=>'cve',                  'type'=>'xsd:integer'),
          'folio'              => array('name'=>'folio',  'type'=>'xsd:integer'),
          'tipo'              => array('name'=>'tipo',  'type'=>'xsd:integer'),
          'estatus'              => array('name'=>'estatus',  'type'=>'xsd:integer'))
    );
    
$server->wsdl->addComplexType(
    'folios',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:folio[]')),
    'tns:folio'
); 

$server->wsdl->addComplexType(
    'compra',
    'complexType',
    'struct',
    'all',
    '',
    array('cve'                => array('name'=>'cve',                  'type'=>'xsd:integer'),
          'fecha'              => array('name'=>'fecha',              'type'=>'xsd:string'),
          'hora'              => array('name'=>'hora',  'type'=>'xsd:string'),
          'engomado'              => array('name'=>'engomado',  'type'=>'xsd:integer'),
          'anio'              => array('name'=>'anio',  'type'=>'xsd:integer'),
          'folioini'              => array('name'=>'folioini',  'type'=>'xsd:integer'),
          'foliofin'              => array('name'=>'foliofin',  'type'=>'xsd:integer'),
          'usuario'              => array('name'=>'usuario',  'type'=>'xsd:integer'),
          'estatus'              => array('name'=>'estatus',  'type'=>'xsd:string'),
          'usucan'              => array('name'=>'usucan',  'type'=>'xsd:integer'),
          'fechacan'              => array('name'=>'fechacan',  'type'=>'xsd:string'),
          'obscan'              => array('name'=>'obscan',  'type'=>'xsd:string'),
          'folios'              => array('name'=>'folios', 'type'=>'tns:folios'))
    );
  
$server->wsdl->addComplexType(
    'compras',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:compra[]')),
    'tns:compra'
);    

$server->wsdl->addComplexType(
    'listacompras',
    'complexType',
    'struct',
    'all',
    '',
    array('resultado' => array('name'=>'resultado', 'type'=>'xsd:integer'),
    		'mensaje' => array('name'=>'mensaje', 'type'=>'xsd:string'),
          'datos'     => array('name'=>'datos',     'type'=>'tns:compras'))
    );
    

$server->wsdl->addComplexType(
    'personal',
    'complexType',
    'struct',
    'all',
    '',
    array('cve'                => array('name'=>'cve',                  'type'=>'xsd:integer'),
          'nombre'              => array('name'=>'nombre',              'type'=>'xsd:string'),
          'plaza'              => array('name'=>'plaza',  'type'=>'xsd:integer'),
          'estatus'              => array('name'=>'estatus',  'type'=>'xsd:string'),
          'huella'              => array('name'=>'huella',  'type'=>'xsd:string'))
    );
    
$server->wsdl->addComplexType(
    'personals',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:personal[]')),
    'tns:personal'
);  

$server->wsdl->addComplexType(
    'listapersonals',
    'complexType',
    'struct',
    'all',
    '',
    array('resultado' => array('name'=>'resultado', 'type'=>'xsd:integer'),
    		'mensaje' => array('name'=>'mensaje', 'type'=>'xsd:string'),
          'datos'     => array('name'=>'datos',     'type'=>'tns:personals'))
    );

// registar WebMethod para leer informacion de una tabla
$server->register(
                // nombre del metodo
                'ReadTable',
                // lista de parametros
                array('tabla'=>'xsd:string','plaza'=>'xsd:string'), 
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
                'Leer informacion de una tabla');

// registrar WebMethod para actualizar los Boletos
$server->register(
                // nombre del metodo
                'UpdateTickets', 		 
                // lista de parametros
                array('plaza'=>'xsd:integer','registros'=>'xsd:string'), 
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
                'Actualizar tabla de boletos');
$server->register(
    // nombre del metodo
    'getCambios',          
    // lista de parametros
    array('plaza'=>'xsd:integer'), 
    // valores de return
    array('return'=>'tns:listacambios'),
    // namespace
    $namespace,
    // soapaction: (use default)
    false,
    // style: rpc or document
    'rpc',
    // use: encoded or literal
    'encoded',
    // descripcion: documentacion del metodo
    'Obtener Listado de Cambios');
				
				
$server->register(
    // nombre del metodo
    'getCompras',          
    // lista de parametros
    array('plaza'=>'xsd:integer'), 
    // valores de return
    array('return'=>'tns:listacompras'),
    // namespace
    $namespace,
    // soapaction: (use default)
    false,
    // style: rpc or document
    'rpc',
    // use: encoded or literal
    'encoded',
    // descripcion: documentacion del metodo
    'Obtener Listado de Compras');
    
$server->register(
    // nombre del metodo
    'getPersonal',          
    // lista de parametros
    array('plaza'=>'xsd:integer'), 
    // valores de return
    array('return'=>'tns:listapersonals'),
    // namespace
    $namespace,
    // soapaction: (use default)
    false,
    // style: rpc or document
    'rpc',
    // use: encoded or literal
    'encoded',
    // descripcion: documentacion del metodo
    'Obtener Listado de Personal');
    
function ReadTable($tabla, $plaza){
	global $base;
	$respuesta['resultado']=false;
	$respuesta['mensaje']='';
	$strcnn=ConectarDB();
	if($strcnn!="OK")
		$respuesta['mensaje']=$strcnn;
	if($respuesta['mensaje']==''){
		//Tomar la informacion de la tabla
		$atablas=array('engomados'=>'cve,nombre,precio,localidad,mostrar_registro,numero,venta,entrega,plazas','metodo_pago'=>'*','tipos_pago'=>'*','usuarios'=>'*','plazas'=>'cve,numero,nombre,tipo_plaza,estatus,local,validar_certificado_anterior','areas'=>'*','depositantes'=>'*','motivos_cancelacion_certificados'=>'*', 'tipo_combustible'=>'*', 'motivos_intento'=>'cve, nombre', 'anios_certificados'=>'*', 'series'=>'cve,serie', 'motivos_checada'=>'cve,nombre');
		if(array_key_exists($tabla,$atablas)){
			$strdata='';
			if($tabla=='plazas'){
				$query="Select {$atablas[$tabla]} from $tabla where cve=".$plaza;
				try{
					$rs = mysql_query($query);
					while($row=mysql_fetch_array($rs)){
						$strvalores='';
						foreach($row as $key=>$val){
							if(!is_numeric($key)){
								if($strvalores!='')
									$strvalores.=',';
								$strvalores.="$key='$val'";
							}
						}
						$strdata.="update plazas set $strvalores where cve='$plaza';\n";
					}
					$query="Select nombre,rfc,calle,numexterior,numinterior,colonia,localidad,localidad_id,municipio,estado,codigopostal from datosempresas where plaza=".$plaza;
					$rs = mysql_query($query);
					while($row=mysql_fetch_array($rs)){
						$strvalores='';
						foreach($row as $key=>$val){
							if(!is_numeric($key)){
								if($strvalores!='')
									$strvalores.=',';
								$strvalores.="$key='$val'";
							}
						}
						$strdata.="update datosempresas set $strvalores where plaza='$plaza';\n";
					}
					$respuesta['mensaje']=base64_encode($strdata);
					$respuesta['resultado']=true;
				}
				catch(Exception $e){
					$respuesta['mensaje']="Exepcion:".$e->getCode()." ".$e->getMessage();
				}
			}
			elseif($tabla=='usuarios'){
				$query="Select a.* from usuarios a inner join usuario_accesos b on a.cve=b.usuario where a.estatus != 'I' AND b.plaza=".$plaza." AND b.acceso>0 GROUP BY a.cve";
				try{
					$rs = mysql_query($query);
					while($row=mysql_fetch_array($rs)){
						$strvalores='';
						foreach($row as $key=>$val){
							if(!is_numeric($key)){
								if($strvalores!='')
									$strvalores.=',';
								$strvalores.="$key='$val'";
							}
						}
						$strdata.="insert usuarios set $strvalores;\n";
					}
					$query="Select b.* from usuarios a inner join usuario_accesos b on a.cve=b.usuario where a.estatus != 'I' AND b.plaza=".$plaza." AND b.acceso>0";
					$rs = mysql_query($query);
					while($row=mysql_fetch_array($rs)){
						$strvalores='';
						foreach($row as $key=>$val){
							if(!is_numeric($key)){
								if($strvalores!='')
									$strvalores.=',';
								$strvalores.="$key='$val'";
							}
						}
						$strdata.="insert usuario_accesos set $strvalores;\n";
					}
					$respuesta['mensaje']=base64_encode($strdata);
					$respuesta['resultado']=true;
				}
				catch(Exception $e){
					$respuesta['mensaje']="Exepcion:".$e->getCode()." ".$e->getMessage();
				}
			}
			else{
				$query="Select {$atablas[$tabla]} from $tabla where 1";
				if($tabla == 'motivos_intento') $query .= " AND localidad IN (0,2)";
				elseif($tabla == 'motivos_checada') $query .= " AND estatus!=1";
				try{
					$rs = mysql_query($query);
					while($row=mysql_fetch_array($rs)){
						$strvalores='';
						foreach($row as $key=>$val){
							if(!is_numeric($key)){
								if($strvalores!='')
									$strvalores.=',';
								$strvalores.="$key='$val'";
							}
						}
						$strdata.="$strvalores;\n";
					}
					$respuesta['mensaje']=base64_encode($strdata);
					$respuesta['resultado']=true;
				}
				catch(Exception $e){
					$respuesta['mensaje']="Exepcion:".$e->getCode()." ".$e->getMessage();
				}
			}
		}
		else{
			$respuesta['mensaje']="No se ha Configurado la tabla:$tabla, para sincronizar";
		}
	}
	return $respuesta;
}
function UpdateTickets($plaza,$registros){
	global $base;
	$respuesta['resultado']=false;
	$respuesta['mensaje']='';
	$strcnn=ConectarDB();
	if($strcnn!="OK")
		$respuesta['mensaje']=$strcnn;
	if($respuesta['mensaje']==''){
		$res = mysql_query("SELECT * FROM plazas WHERE cve='$plaza'");
		$row=mysql_fetch_array($res);
		if($row['local'] >= 0){
			mysql_query("DELETE FROM tecnicos WHERE plaza='$plaza'");
			//Eliminar los registros de la taquilla y periodo
			//$query="Delete from tickets2 where taq='$taquilla' and fecha between '$fechainicial' And '$fechafinal'";
			//mysql_query($query);
			//Insertar los registros actuales
			$strData=base64_decode($registros);
			$vecData=explode(";\n", $strData);
			foreach($vecData As $query){
				if($res = mysql_query($query)){
					if((strpos($query,"INSERT") === true || strpos($query,"UPDATE") === true) && strpos($query," certificados ") === true){
						$ticket = mysql_insert_id();
						mysql_query("UPDATE certificados c 
						INNER JOIN compra_certificados a on c.plaza = a.plaza AND c.engomado = a.engomado
						INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra AND CAST(c.certificado as UNSIGNED) = b.folio 
						SET b.estatus=1 
						WHERE c.plaza='".$plaza."' AND c.cve = '$ticket' AND b.tipo=0 AND c.estatus != 'C'");
						
						mysql_query("UPDATE certificados c 
						INNER JOIN compra_certificados a on c.plaza = a.plaza AND c.engomado = a.engomado
						INNER JOIN compra_certificados_detalle b ON a.plaza = b.plaza AND a.cve = b.cvecompra AND CAST(c.certificado as UNSIGNED) = b.folio 
						SET b.estatus=0 
						WHERE c.plaza='".$plaza."' AND c.cve = '$ticket' AND b.tipo=0 AND c.estatus = 'C'");
					}
				}
			}
			$respuesta['resultado']=true;
		}
		else{
			$respuesta['mensaje']='La plaza no es local';
		}
	}
	return $respuesta;	
} 

function getCambios($plaza){
	global $base;
	$respuesta['resultado']=true;
	$respuesta['mensaje']='';
	$strcnn=ConectarDB();
	if($strcnn!="OK")
		$respuesta['mensaje']=$strcnn;
	$respuesta['datos']=array();
	$res = mysql_query("SELECT a.* FROM cambios_local a LEFT JOIN cambios_local_plaza b ON a.cve = b.cambio AND b.plaza = '$plaza' WHERE a.plaza IN (0,'$plaza') AND ISNULL(b.cve)");
	while($row = mysql_fetch_array($res)){
		$respuesa['mensaje'].='entra';
		if($row['tipo']==1){
			$respuesta['datos'][] = array(
				'tipo'=>$row['tipo'],
				'cambio'=>$row['cambio'],
				'nombre'=>'');
		}
		else{
			$archivo = file_get_contents('verificentros_local/'.$row['cambio'],true);
			$respuesta['datos'][] = array(
				'tipo'=>$row['tipo'],
				'nombre'=>$row['cambio'],
				'cambio'=>base64_encode($archivo));
		}
		mysql_query("INSERT cambios_local_plaza SET cambio='".$row['cve']."',plaza='$plaza'");
	}
	$res = mysql_query("SELECT * FROM pagos_caja WHERE plaza = '$plaza' AND sincronizado IN (0,2)");
	while($row=mysql_fetch_array($res)){
		if($row['sincronizado']==0){
			$query="INSERT INTO pagos_caja SET plaza='{$row['plaza']}',cve='{$row['cve']}',fecha='{$row['fecha']}',hora='{$row['hora']}',tipo_pago='{$row['tipo_pago']}',forma_pago='{$row['forma_pago']}',referencia='{$row['referencia']}',depositante='{$row['depositante']}',monto='{$row['monto']}',obs='{$row['obs']}',estatus='{$row['estatus']}',usuario='{$row['usuario']}',usucan='{$row['usucan']}',fechacan='{$row['fechacan']}';";
		}
		else{
			$query="UPDATE pagos_caja SET fecha='{$row['fecha']}',hora='{$row['hora']}',tipo_pago='{$row['tipo_pago']}',forma_pago='{$row['forma_pago']}',referencia='{$row['referencia']}',depositante='{$row['depositante']}',monto='{$row['monto']}',obs='{$row['obs']}',estatus='{$row['estatus']}',usuario='{$row['usuario']}',usucan='{$row['usucan']}',fechacan='{$row['fechacan']}' WHERE plaza='{$row['plaza']}' AND cve='{$row['cve']}';";
		}
		$respuesta['datos'][] = array(
			'tipo'=>1,
			'cambio'=>$query,
			'nombre'=>'');
		mysql_query("UPDATE pagos_caja SET sincronizado=1 WHERE plaza='$plaza' AND cve='".$row['cve']."'");
	}
	//$respuesta['mensaje']=print_r($respuesta['datos'],true);
	return $respuesta;
}

function getCompras($plaza){
	global $base;
	$respuesta['resultado']=true;
	$respuesta['mensaje']='';
	$strcnn=ConectarDB();
	if($strcnn!="OK")
		$respuesta['mensaje']=$strcnn;
	$respuesta['datos']=array();
	$res = mysql_query("SELECT * FROM compra_certificados WHERE plaza = '$plaza' AND DATEDIFF(CURDATE(),fecha_compra)<=15");
	while($row = mysql_fetch_array($res)){
		$folios = array();
		$res1=mysql_query("SELECT * FROM compra_certificados_detalle WHERE plaza = '$plaza' AND cvecompra = '".$row['cve']."' ");
		while($row1=mysql_fetch_array($res1)){
			$folios[] = array(
				'cve'=>$row1['cve'],
				'folio'=>$row1['folio'],
				'tipo'=>$row1['tipo'],
				'estatus'=>$row1['estatus']
			);
		}
		$respuesta['datos'][] = array(
				'cve'=>$row['cve'],
				'fecha'=>$row['fecha'],
				'hora'=>$row['hora'],
				'engomado'=>$row['engomado'],
				'anio'=>$row['anio'],
				'folioini'=>$row['folioini'],
				'foliofin'=>$row['foliofin'],
				'usuario'=>$row['usuario'],
				'estatus'=>$row['estatus'],
				'usucan'=>$row['usucan'],
				'fechacan'=>$row['fechacan'],
				'obscan'=>$row['obscan'],
				'folios'=>$folios);
		
	}
	return $respuesta;
}

function getPersonal($plaza){
	global $base;
	$respuesta['resultado']=true;
	$respuesta['mensaje']='';
	$strcnn=ConectarDB();
	if($strcnn!="OK")
		$respuesta['mensaje']=$strcnn;
	$respuesta['datos']=array();
	$res = mysql_query("SELECT * FROM personal WHERE 1");
	while($row = mysql_fetch_array($res)){
		
		$respuesta['datos'][] = array(
				'cve'=>$row['cve'],
				'nombre'=>$row['nombre'],
				'estatus'=>$row['estatus'],
				'plaza'=>$row['plaza'],
				'huella'=>$row['huella']);
		
	}
	return $respuesta;
}

function ConectarDB(){
	$msg="OK";
	eval(file_get_contents('/var/www/config.cfg'));
	//Conexion con la base
	if (!$MySQL=@mysql_connect($DB_HOST, 'tepe_tepe2', 'Ballena6')) {
		$t=time();
		while (time()<$t+5) {}
		if (!$MySQL=@mysql_connect($DB_HOST, 'tepe_tepe2', 'Ballena6')) {
			$t=time();
			while (time()<$t+10) {}
			if (!$MySQL=@mysql_connect($DB_HOST, 'tepe_tepe2', 'Ballena6')) {
			echo '<br><br><br><h3 align=center">Hay problemas de comunicaci&oacute;n con la Base de datos.</h3>';
			echo '<h4>Por favor intente mas tarde.-</h4>';
			exit;
			}
		}
	}
	mysql_select_db("gveri");
	mysql_query("SET time_zone = CST6CDT;");
	return $msg;
}
// Get our posted data if the service is being consumed
// otherwise leave this data blank.                
$POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) 
? $GLOBALS['HTTP_RAW_POST_DATA'] : '';

// pass our posted data (or nothing) to the soap service                    
$server->service($POST_DATA);

?>
