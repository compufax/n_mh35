<?php 

include ("main.php"); 

/*** ARREGLOS ***********************************************************/

$rsPlaza=mysql_query("SELECT * FROM plazas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['numero'];
}

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsPuestos=mysql_query("SELECT * FROM puestos ORDER BY nombre");
while($Puestos=mysql_fetch_array($rsPuestos)){
	$array_puestos[$Puestos['cve']]=$Puestos['nombre'];
}

$rsDepto=mysql_query("SELECT * FROM areas");
while($Depto=mysql_fetch_array($rsDepto)){
	$arreglo_departamentos[$Depto['cve']]=$Depto['nombre'];
}

$rsDepto=mysql_query("SELECT * FROM cat_personal_documentos");
while($Depto=mysql_fetch_array($rsDepto)){
	$array_documentos[$Depto['cve']]=$Depto['nombre'];
}

$res=mysql_query("SELECT * FROM bancos ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_banco[$row['cve']]=$row['nombre'];
}
$res=mysql_query("SELECT * FROM tipo_contrato ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_contrato[$row['cve']]=$row['nombre'];
}
$res=mysql_query("SELECT * FROM tipo_jornada ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_jornada[$row['cve']]=$row['nombre'];
}
$res=mysql_query("SELECT * FROM tipo_regimen ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_regimen[$row['cve']]=$row['nombre'];
}
$res=mysql_query("SELECT * FROM metodo_pago ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_tipo_pago[$row['cve']]=$row['nombre'];
}

mysql_select_db($base);
/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$select="SELECT * FROM personal WHERE cve='".$_POST['reg']."'";
			$rsconductor=mysql_query($select);
			$Conductor=mysql_fetch_array($rsconductor);
			if($Conductor['estatus']!=$_POST['estatus']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Estatus' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				if($_POST['estatus']==2){
					if($_POST['tipo_baja']==0) $_POST['obs'].' Normal';
					if($_POST['tipo_baja']==1) $_POST['obs'].' Renuncia Voluntaria';
				}
				else{
					$_POST['tipo_baja']=0;
					mysql_query("UPDATE personal SET estatus_eco=0 WHERE cve='".$_POST['reg']."'");
				}
				$insert_estatus="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Estatus',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['estatus']."',valor_nuevo='".$_POST['estatus']."',
							cve_personal='".$_POST['reg']."',fecha='".$_POST['fecha_sta']."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_estatus);		

				
			}
			elseif($Conductor['fecha_sta']!=$_POST['fecha_sta']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Fecha Estatus' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_infonavit="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Fecha Estatus',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['fecha_sta']."',valor_nuevo='".$_POST['fecha_sta']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_infonavit);		
			}
			if($Conductor['plaza']!=$_POST['plaza']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Plaza' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_infonavit="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Plaza',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['plaza']."',valor_nuevo='".$_POST['plaza']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_infonavit);			
			}
			if($Conductor['infonavit']!=$_POST['infonavit']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Infonavit' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_infonavit="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Infonavit',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['infonavit']."',valor_nuevo='".$_POST['infonavit']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_infonavit);			
			}
			if($Conductor['monto_infonavit']!=$_POST['monto_infonavit']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Monto Infonavit' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_infonavit="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Monto Infonavit',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['monto_infonavit']."',valor_nuevo='".$_POST['monto_infonavit']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_infonavit);			
			}
			if($Conductor['puesto']!=$_POST['puesto']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Puesto' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_tipo_cond="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Puesto',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['puesto']."',valor_nuevo='".$_POST['puesto']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_tipo_cond);			
			}
			if($Conductor['afiliado_imss']!=$_POST['afiliado_imss']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Afiliado IMSS' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Afiliado IMSS',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['afiliado_imss']."',valor_nuevo='".$_POST['afiliado_imss']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['obs']!=$_POST['observaciones']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Observacion' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Observaciones',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['obs']."',valor_nuevo='".$_POST['observaciones']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['salario_integrado']!=$_POST['salario_integrado']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Sueldo Diario' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Sueldo Diario',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['salario_integrado']."',valor_nuevo='".$_POST['salario_integrado']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['departamento']!=$_POST['departamento']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Departamento' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Departamento',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['departamento']."',valor_nuevo='".$_POST['departamento']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['sdi']!=$_POST['sdi']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Salario Diario Integrado' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Salario Diario Integrado',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['sdi']."',valor_nuevo='".$_POST['sdi']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['rfc']!=$_POST['rfc']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Rfc' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Rfc',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['rfc']."',valor_nuevo='".$_POST['rfc']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['curp']!=$_POST['curp']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Curp' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Curp',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['curp']."',valor_nuevo='".$_POST['curp']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['calle']!=$_POST['calle']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Calle' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Calle',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['calle']."',valor_nuevo='".$_POST['calle']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['num_ext']!=$_POST['num_ext']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Num Ext' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Num Ext',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['num_ext']."',valor_nuevo='".$_POST['num_ext']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['num_int']!=$_POST['num_int']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Num Int' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Num Int',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['num_int']."',valor_nuevo='".$_POST['num_int']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['colonia']!=$_POST['colonia']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Colonia' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Colonia',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['colonia']."',valor_nuevo='".$_POST['colonia']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['localidad']!=$_POST['localidad']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Localidad' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Localidad',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['localidad']."',valor_nuevo='".$_POST['localidad']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['municipio']!=$_POST['municipio']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Municipio' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Municipio',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['municipio']."',valor_nuevo='".$_POST['municipio']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['estado']!=$_POST['estado']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Estado' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Estado',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['estado']."',valor_nuevo='".$_POST['estado']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['codigopostal']!=$_POST['codigopostal']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Codigo Postal' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Codigo Postal',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['codigopostal']."',valor_nuevo='".$_POST['codigopostal']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['banco']!=$_POST['banco']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Banco' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Banco',fecha_reg='".fechaLocal()."',
							valor_anterior='".$array_banco[$Conductor['banco']]."',valor_nuevo='".$array_banco[$_POST['banco']]."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['clabe']!=$_POST['clabe']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Clabe' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Clabe',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['clabe']."',valor_nuevo='".$_POST['clabe']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['metodo_pago']!=$_POST['metodo_pago']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Metodo Pago' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Metodo Pago',fecha_reg='".fechaLocal()."',
							valor_anterior='".$array_tipo_pago[$Conductor['metodo_pago']]."',valor_nuevo='".$array_tipo_pago[$_POST['metodo_pago']]."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['tipo_contrato']!=$_POST['tipo_contrato']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Tipo Contrato' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Tipo Contrato',fecha_reg='".fechaLocal()."',
							valor_anterior='".$array_tipo_contrato[$Conductor['tipo_contrato']]."',valor_nuevo='".$array_tipo_contrato[$_POST['tipo_contrato']]."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['tipo_jornada']!=$_POST['tipo_jornada']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Tipo Jornada' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Tipo Jornada',fecha_reg='".fechaLocal()."',
							valor_anterior='".$array_tipo_jornada[$Conductor['tipo_jornada']]."',valor_nuevo='".$array_tipo_jornada[$_POST['tipo_jornada']]."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['tipo_regimen']!=$_POST['tipo_regimen']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Tipo Regimen' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Tipo Regimen',fecha_reg='".fechaLocal()."',
							valor_anterior='".$array_tipo_regimen[$Conductor['tipo_regimen']]."',valor_nuevo='".$array_tipo_regimen[$_POST['tipo_regimen']]."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['clave_ecologica']!=$_POST['clave_ecologica']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Clave Ecologica' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Clave Ecologica',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['clave_ecologica']."',valor_nuevo='".$_POST['clave_ecologica']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($_POST['fecha_eco'] =="") $_POST['fecha_eco']='0000-00-00';
			if($Conductor['fecha_eco']!=$_POST['fecha_eco']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Fecha Ecologia' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Fecha Ecologia',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['fecha_eco']."',valor_nuevo='".$_POST['fecha_eco']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['nombre']!=$_POST['nombre']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Nombre' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Nombre',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['nombre']."',valor_nuevo='".$_POST['nombre']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['imss']!=$_POST['imss']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='IMSS' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='IMSS',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['imss']."',valor_nuevo='".$_POST['imss']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['monto_imss']!=$_POST['monto_imss']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Monto IMSS' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Monto IMSS',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['monto_imss']."',valor_nuevo='".$_POST['monto_imss']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($_POST['fecha_imss']=="") $_POST['fecha_imss']='0000-00-00';
			if($Conductor['fecha_imss']!=$_POST['fecha_imss']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Fecha IMSS' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Fecha IMSS',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['fecha_imss']."',valor_nuevo='".$_POST['fecha_imss']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['email']!=$_POST['email']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Email' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Email',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['email']."',valor_nuevo='".$_POST['email']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			}
			if($Conductor['tiene_licencia']!=intval($_POST['tiene_licencia'])){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Tiene Licencia' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Tiene Licencia',fecha_reg='".fechaLocal()."',
							valor_anterior='".$array_nosi[$Conductor['tiene_licencia']]."',valor_nuevo='".$array_nosi[$_POST['tiene_licencia']]."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);	
			}
			if($Conductor['administrativo']!=intval($_POST['administrativo'])){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Administrativo' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Administrativo',fecha_reg='".fechaLocal()."',
							valor_anterior='".$array_nosi[$Conductor['administrativo']]."',valor_nuevo='".$array_nosi[$_POST['administrativo']]."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);	
			}
			if($Conductor['licencia']!=$_POST['licencia']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Licencia' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Licencia',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['licencia']."',valor_nuevo='".$_POST['licencia']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);	
			}
			if($Conductor['fecha_venc_licencia']=='0000-00-00') $Conductor['fecha_venc_licencia']='';
			if($Conductor['fecha_venc_licencia']!=$_POST['fecha_venc_licencia']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Fecha Vencimiento Licencia' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Fecha Vencimiento Licencia',fecha_reg='".fechaLocal()."',
							valor_anterior='".$_POST['conductor']."',valor_nuevo='".$_POST['fecha_venc_licencia']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);	
			}	
			if($Conductor['cuenta']!=$_POST['cuenta']){
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Cuenta' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Cuenta',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['cuenta']."',valor_nuevo='".$_POST['cuenta']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);	
			}
			$update = " UPDATE personal 
						SET fecha_ini='".$_POST['fecha_ini']."',clave_ecologica='".$_POST['clave_ecologica']."',nombre='".$_POST['nombre']."',rfc='".$_POST['rfc']."',estatus='".$_POST['estatus']."',plaza='".$_POST['plaza']."',
							fecha_sta='".$_POST['fecha_sta']."',edad='".$_POST['edad']."',lugar_nacimiento='".$_POST['nac']."',
							infonavit='".$_POST['infonavit']."',puesto='".$_POST['puesto']."',email='".$_POST['email']."',
							asistencia='".$_POST['asistencia']."',telefono='".$_POST['telefono']."',
							imss='".$_POST['imss']."',fecha_imss='".$_POST['fecha_imss']."',afiliado_imss='".$_POST['afiliado_imss']."',obs='".$_POST['observaciones']."',
							salario_integrado='".$_POST['salario_integrado']."',ecologia='".$_POST['ecologia']."',
							tipo_baja='".$_POST['tipo_baja']."',monto_imss='".$_POST['monto_imss']."',
							monto_infonavit='".$_POST['monto_infonavit']."',departamento='".$_POST['departamento']."',curp='".$_POST['curp']."',
							banco='".$_POST['banco']."',clabe='".$_POST['clabe']."',metodo_pago='".$_POST['metodo_pago']."',
							tipo_contrato='".$_POST['tipo_contrato']."',tipo_jornada='".$_POST['tipo_jornada']."',tipo_regimen='".$_POST['tipo_regimen']."',
							sdi='".$_POST['sdi']."',calle='".$_POST['calle']."',num_ext='".$_POST['num_ext']."',
							num_int='".$_POST['num_int']."',colonia='".$_POST['colonia']."',localidad='".$_POST['localidad']."',municipio='".$_POST['municipio']."',
							estado='".$_POST['estado']."',codigopostal='".$_POST['codigopostal']."',administrativo='".$_POST['administrativo']."',
							tiene_licencia='".$_POST['tiene_licencia']."',licencia='".$_POST['licencia']."',fecha_venc_licencia='".$_POST['fecha_venc_licencia']."',
							cuenta='".$_POST['cuenta']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_query($update) or die(mysql_error());		
			$id=$_POST['reg'];
			
	} else {
			//Insertar el Registro
			$insert = " INSERT personal 
						SET plaza='".$_POST['plaza']."',fecha_ini='".$_POST['fecha_ini']."',
						    clave_ecologica='".$_POST['clave_ecologica']."',nombre='".$_POST['nombre']."',rfc='".$_POST['rfc']."',estatus='".$_POST['estatus']."',
							fecha_sta='".$_POST['fecha_ini']."',edad='".$_POST['edad']."',lugar_nacimiento='".$_POST['nac']."',
							infonavit='".$_POST['infonavit']."',puesto='".$_POST['puesto']."',
							asistencia='".$_POST['asistencia']."',telefono='".$_POST['telefono']."',
							imss='".$_POST['imss']."',fecha_imss='".$_POST['fecha_imss']."',afiliado_imss='".$_POST['afiliado_imss']."',obs='".$_POST['observaciones']."',
							salario_integrado='".$_POST['salario_integrado']."',email='".$_POST['email']."',
							monto_imss='".$_POST['monto_imss']."',ecologia='".$_POST['ecologia']."',
							monto_infonavit='".$_POST['monto_infonavit']."',departamento='".$_POST['departamento']."',
							banco='".$_POST['banco']."',clabe='".$_POST['clabe']."',metodo_pago='".$_POST['metodo_pago']."',curp='".$_POST['curp']."',
							tipo_contrato='".$_POST['tipo_contrato']."',tipo_jornada='".$_POST['tipo_jornada']."',tipo_regimen='".$_POST['tipo_regimen']."',
							sdi='".$_POST['sdi']."',calle='".$_POST['calle']."',num_ext='".$_POST['num_ext']."',
							num_int='".$_POST['num_int']."',colonia='".$_POST['colonia']."',localidad='".$_POST['localidad']."',municipio='".$_POST['municipio']."',
							estado='".$_POST['estado']."',codigopostal='".$_POST['codigopostal']."',estatus_eco=0,administrativo='".$_POST['administrativo']."',
							tiene_licencia='".$_POST['tiene_licencia']."',licencia='".$_POST['licencia']."',fecha_venc_licencia='".$_POST['fecha_venc_licencia']."',
							cuenta='".$_POST['cuenta']."'";
			$ejecutar = mysql_query($insert) or die(mysql_error());
			$id=mysql_insert_id();
			
			$_POST['reg']=$id;
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Plaza' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_infonavit="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Plaza',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['plaza']."',valor_nuevo='".$_POST['plaza']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_infonavit);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Infonavit' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_infonavit="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Infonavit',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['infonavit']."',valor_nuevo='".$_POST['infonavit']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_infonavit);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Monto Infonavit' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_infonavit="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Monto Infonavit',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['monto_infonavit']."',valor_nuevo='".$_POST['monto_infonavit']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_infonavit);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Puesto' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_tipo_cond="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Puesto',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['puesto']."',valor_nuevo='".$_POST['puesto']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_tipo_cond);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Afiliado IMSS' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Afiliado IMSS',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['afiliado_imss']."',valor_nuevo='".$_POST['afiliado_imss']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Observacion' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Observaciones',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['obs']."',valor_nuevo='".$_POST['observaciones']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Sueldo Diario' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Sueldo Diario',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['salario_integrado']."',valor_nuevo='".$_POST['salario_integrado']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Departamento' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Departamento',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['departamento']."',valor_nuevo='".$_POST['departamento']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Salario Diario Integrado' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Salario Diario Integrado',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['sdi']."',valor_nuevo='".$_POST['sdi']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Rfc' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Rfc',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['rfc']."',valor_nuevo='".$_POST['rfc']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Curp' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Curp',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['curp']."',valor_nuevo='".$_POST['curp']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Calle' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Calle',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['calle']."',valor_nuevo='".$_POST['calle']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Num Ext' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Num Ext',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['num_ext']."',valor_nuevo='".$_POST['num_ext']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Num Int' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Num Int',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['num_int']."',valor_nuevo='".$_POST['num_int']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Colonia' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Colonia',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['colonia']."',valor_nuevo='".$_POST['colonia']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Localidad' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Localidad',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['localidad']."',valor_nuevo='".$_POST['localidad']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Municipio' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Municipio',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['municipio']."',valor_nuevo='".$_POST['municipio']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Estado' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Estado',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['estado']."',valor_nuevo='".$_POST['estado']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Codigo Postal' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Codigo Postal',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['codigopostal']."',valor_nuevo='".$_POST['codigopostal']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Banco' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Banco',fecha_reg='".fechaLocal()."',
							valor_anterior='".$array_banco[$Conductor['banco']]."',valor_nuevo='".$array_banco[$_POST['banco']]."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Clabe' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Clabe',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['clabe']."',valor_nuevo='".$_POST['clabe']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Metodo Pago' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Metodo Pago',fecha_reg='".fechaLocal()."',
							valor_anterior='".$array_tipo_pago[$Conductor['metodo_pago']]."',valor_nuevo='".$array_tipo_pago[$_POST['metodo_pago']]."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Tipo Contrato' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Tipo Contrato',fecha_reg='".fechaLocal()."',
							valor_anterior='".$array_tipo_contrato[$Conductor['tipo_contrato']]."',valor_nuevo='".$array_tipo_contrato[$_POST['tipo_contrato']]."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Tipo Jornada' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Tipo Jornada',fecha_reg='".fechaLocal()."',
							valor_anterior='".$array_tipo_jornada[$Conductor['tipo_jornada']]."',valor_nuevo='".$array_tipo_jornada[$_POST['tipo_jornada']]."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Tipo Regimen' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Tipo Regimen',fecha_reg='".fechaLocal()."',
							valor_anterior='".$array_tipo_regimen[$Conductor['tipo_regimen']]."',valor_nuevo='".$array_tipo_regimen[$_POST['tipo_regimen']]."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Clave Ecologica' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Clave Ecologica',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['clave_ecologica']."',valor_nuevo='".$_POST['clave_ecologica']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Nombre' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Nombre',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['nombre']."',valor_nuevo='".$_POST['nombre']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='IMSS' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='IMSS',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['imss']."',valor_nuevo='".$_POST['imss']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);			
			/*	$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Monto IMSS' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Monto IMSS',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['monto_imss']."',valor_nuevo='".$_POST['monto_imss']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);	*/	
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Fecha IMSS' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Fecha IMSS',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['fecha_imss']."',valor_nuevo='".$_POST['fecha_imss']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);	
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Email' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Email',fecha_reg='".fechaLocal()."',
							valor_anterior='".$Conductor['email']."',valor_nuevo='".$_POST['email']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);		
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Tiene Licencia' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Tiene Licencia',fecha_reg='".fechaLocal()."',
							valor_anterior='',valor_nuevo='".$array_nosi[$_POST['tiene_licencia']]."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);	
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Licencia' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Licencia',fecha_reg='".fechaLocal()."',
							valor_anterior='',valor_nuevo='".$_POST['licencia']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);	
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Fecha Vencimiento Licencia' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Fecha Vencimiento Licencia',fecha_reg='".fechaLocal()."',
							valor_anterior='',valor_nuevo='".$_POST['fecha_venc_licencia']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);		
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Cuenta' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Cuenta',fecha_reg='".fechaLocal()."',
							valor_anterior='',valor_nuevo='".$_POST['cuenta']."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);	
	}
	if($_POST['borrar_foto']=="S")
		unlink("imgpersonal/foto".$id.".jpg");
	if(is_uploaded_file ($_FILES['foto']['tmp_name'])){
		/*if(file_exists("fotos/foto".$_POST['reg'].".jpg")){
			unlink("fotos/foto".$id.".jpg");
		}*/
		$arch = $_FILES['foto']['tmp_name'];
		copy($arch,"imgpersonal/foto".$id.".jpg");
		chmod("imgpersonal/foto".$id.".jpg", 0777);
		//echo "si se pudo";
	}
	
	//mysql_query("DELETE FROM personal_documentos WHERE personal='$id'");
	foreach($_POST['personal_documentos'] as $k=>$v){
		$res = mysql_query("SELECT * FROM personal_documentos WHERE personal='$id' AND documento='$k'");
		if($row=mysql_fetch_array($res)){
			if($row['entregado']!=intval($v)){
				mysql_query("UPDATE personal_documentos SET entregado='$v' WHERE cve='".$row['cve']."'");
				$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Entrego ".$array_documentos[$k]."' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_afiliado="	INSERT cambios_datos_personal
							SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Entrego ".$array_documentos[$k]."',fecha_reg='".fechaLocal()."',
							valor_anterior='".$array_nosi[intval($row['entregado'])]."',valor_nuevo='".$array_nosi[intval($v)]."',
							cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
							observaciones='".$_POST['obs']."'";
				$ejecutar_estatus=mysql_query($insert_afiliado);	
			}
		}
		else{
			mysql_query("INSERT personal_documentos SET personal='$id',documento='$k',entregado='$v'");
			$rsfolio=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_personal WHERE dato='Entrego ".$array_documentos[$k]."' AND plaza='".$_POST['plaza']."'") or die(mysql_error());
			$Folio=mysql_fetch_array($rsfolio);
			$insert_afiliado="	INSERT cambios_datos_personal
						SET plaza='".$_POST['plaza']."',folio='".$Folio[0]."',dato='Entrego ".$array_documentos[$k]."',fecha_reg='".fechaLocal()."',
						valor_anterior='',valor_nuevo='".$array_nosi[intval($v)]."',
						cve_personal='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_POST['cveusuario']."',
						observaciones='".$_POST['obs']."'";
			$ejecutar_estatus=mysql_query($insert_afiliado);		
		}
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==50) {
	require_once('excel/Worksheet.php');
	require_once('excel/Workbook.php');
	function HeaderingExcel($filename) {
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$filename" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}		
	// HTTP headers
	HeaderingExcel('personal.xls');
	// Creating a workbook
	$workbook = new Workbook("-");
	// Creating the first worksheet
	$worksheet1 =& $workbook->add_worksheet('Listado de Personal');
	$normal =& $workbook->add_format();
	$normal->set_align('left');
	$normal->set_align('vjustify');
	$select= " SELECT a.* FROM personal a INNER JOIN datosempresas b on a.plaza = b.plaza WHERE 1 ";
	if ($_POST['estatus']!="all") { $select.=" AND a.estatus='".$_POST['estatus']."'"; }
	if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
	if ($_POST['num']!="") { $select.=" AND a.clave_ecologica='".$_POST['num']."'"; }
	if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; }
	if ($_POST['asegurado']!="all") { $select.=" AND a.afiliado_imss='".$_POST['asegurado']."'"; }
	if ($_POST['puesto']!="all") { $select.=" AND a.puesto='".$_POST['puesto']."'"; }
	if ($_POST['localidad']!="all") { $select.=" AND b.localidad_id='".$_POST['localidad']."'"; }
	if ($_POST['fecha_ini']!="") { $select.=" AND a.fecha_sta>='".$_POST['fecha_ini']."'"; }
	if ($_POST['fecha_fin']!="") { $select.=" AND a.fecha_sta<='".$_POST['fecha_fin']."'"; }
	$select.=" ORDER BY trim(a.nombre)";
	$rspersonal=mysql_query($select);
	$totalRegistros = mysql_num_rows($rspersonal);
	$c=0;
	$worksheet1->write_string(0,$c,'Numero');++$c;
	$worksheet1->write_string(0,$c,'Plaza');++$c;
	$worksheet1->write_string(0,$c,'Clave Ecologica');++$c;
	$worksheet1->write_string(0,$c,'Nombre');++$c;
	$worksheet1->write_string(0,$c,'Fecha de Ingreso');++$c;
	$worksheet1->write_string(0,$c,'RFC');++$c;
	$worksheet1->write_string(0,$c,'CURP');++$c;
	$worksheet1->write_string(0,$c,'Calle');++$c;
	$worksheet1->write_string(0,$c,'Num Ext');++$c;
	$worksheet1->write_string(0,$c,'Num Int');++$c;
	$worksheet1->write_string(0,$c,'Colonia');++$c;
	$worksheet1->write_string(0,$c,'Localidad');++$c;
	$worksheet1->write_string(0,$c,'Municipio');++$c;
	$worksheet1->write_string(0,$c,'Estado');++$c;
	$worksheet1->write_string(0,$c,'Codigo Postal');++$c;
	$worksheet1->write_string(0,$c,'Lugar de Nacimeinto');++$c;
	$worksheet1->write_string(0,$c,'Edad');++$c;
	$worksheet1->write_string(0,$c,'Telefono');++$c;
	$worksheet1->write_string(0,$c,'Puesto');++$c;
	//$worksheet1->write_string(0,$c,'Departamento');++$c;
	$worksheet1->write_string(0,$c,'Estatus');++$c;
	$worksheet1->write_string(0,$c,'Fecha de Estatus');++$c;
	$worksheet1->write_string(0,$c,'Observaciones');++$c;
	$worksheet1->write_string(0,$c,'IMSS');++$c;
	$worksheet1->write_string(0,$c,'Fecha IMSS');++$c;
	$worksheet1->write_string(0,$c,'Afiliado IMSS');++$c;
	$worksheet1->write_string(0,$c,'Infonavit');++$c;
	$worksheet1->write_string(0,$c,'Monto Infonavit');++$c;
	$worksheet1->write_string(0,$c,'Asistencia');++$c;
	$worksheet1->write_string(0,$c,'CLABE');++$c;
	$worksheet1->write_string(0,$c,'Banco');++$c;
	$worksheet1->write_string(0,$c,'Tipo de Contrato');++$c;
	$worksheet1->write_string(0,$c,'Tipo de Jornada');++$c;
	$worksheet1->write_string(0,$c,'Tipo de Regimen');++$c;
	$worksheet1->write_string(0,$c,'Metodo de Pago');++$c;
	$worksheet1->write_string(0,$c,'Sueldo Total Diario');++$c;
	$worksheet1->write_string(0,$c,'Salario Diario Integrado');++$c;
	$res=mysql_query($select);
	$l=1;
	while($row=mysql_fetch_array($res)){
		$c=0;
		$worksheet1->write_string($l,$c,$row['cve']);++$c;
		$worksheet1->write_string($l,$c,$array_plaza[$row['plaza']]);++$c;
		$worksheet1->write_string($l,$c,$row['clave_ecologica']);++$c;
		$worksheet1->write_string($l,$c,$row['nombre']);++$c;
		$worksheet1->write_string($l,$c,$row['fecha_ini']);++$c;
		$worksheet1->write_string($l,$c,$row['rfc']);++$c;
		$worksheet1->write_string($l,$c,$row['curp']);++$c;
		$worksheet1->write_string($l,$c,$row['calle']);++$c;
		$worksheet1->write_string($l,$c,$row['num_eco']);++$c;
		$worksheet1->write_string($l,$c,$row['num_int']);++$c;
		$worksheet1->write_string($l,$c,$row['colonia']);++$c;
		$worksheet1->write_string($l,$c,$row['localidad']);++$c;
		$worksheet1->write_string($l,$c,$row['municipio']);++$c;
		$worksheet1->write_string($l,$c,$row['estado']);++$c;
		$worksheet1->write_string($l,$c,$row['codigopostal']);++$c;
		$worksheet1->write_string($l,$c,$row['lugar_nacimiento']);++$c;
		$worksheet1->write_string($l,$c,edad($row['rfc']));++$c;
		$worksheet1->write_string($l,$c,$row['telefono']);++$c;
		$worksheet1->write_string($l,$c,$array_puesto[$row['puesto']]);++$c;
		//$worksheet1->write_string($l,$c,$arreglo_departamentos[$row['departamento']]);++$c;
		$worksheet1->write_string($l,$c,$array_estatus_personal[$row['estatus']]);++$c;
		$worksheet1->write_string($l,$c,$row['fecha_sta']);++$c;
		$worksheet1->write_string($l,$c,$row['obs']);++$c;
		$worksheet1->write_string($l,$c,$row['imss']);++$c;
		$worksheet1->write_string($l,$c,$row['fecha_imss']);++$c;
		$worksheet1->write_string($l,$c,$row['afiliado_imss']);++$c;
		$worksheet1->write_string($l,$c,$row['infonavit']);++$c;
		$worksheet1->write_string($l,$c,$row['monto_infonavit']);++$c;
		$worksheet1->write_string($l,$c,$row['asistencia']);++$c;
		$worksheet1->write_string($l,$c,$row['clabe']);++$c;
		$worksheet1->write_string($l,$c,$array_banco[$row['banco']]);++$c;
		$worksheet1->write_string($l,$c,$array_tipo_contrato[$row['tipo_contrato']]);++$c;
		$worksheet1->write_string($l,$c,$array_tipo_jornada[$row['tipo_jornada']]);++$c;
		$worksheet1->write_string($l,$c,$array_tipo_regimen[$row['tipo_regimen']]);++$c;
		$worksheet1->write_string($l,$c,$array_tipo_pago[$row['metodo_pago']]);++$c;
		$worksheet1->write_string($l,$c,($row['salario_integrado']));++$c;
		$worksheet1->write_string($l,$c,$row['sdi']);++$c;
		$l++;
	}
	$workbook->close();
	exit();
}


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de personal
		$select= " SELECT a.* FROM personal a INNER JOIN datosempresas b on a.plaza = b.plaza WHERE 1 ";
		if ($_POST['credencial']!="") { $select.=" AND a.cve='".$_POST['credencial']."'"; }
		if ($_POST['estatus']!="all") { $select.=" AND a.estatus='".$_POST['estatus']."'"; }
		if ($_POST['nombre']!="") { $select.=" AND a.nombre LIKE '%".$_POST['nombre']."%'"; }
		if ($_POST['num']!="") { $select.=" AND a.clave_ecologica='".$_POST['num']."'"; }
		if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; }
		if ($_POST['asegurado']!="all") { $select.=" AND a.afiliado_imss='".$_POST['asegurado']."'"; }
		if ($_POST['puesto']!="all") { $select.=" AND a.puesto='".$_POST['puesto']."'"; }
		if ($_POST['localidad']!="all") { $select.=" AND b.localidad_id='".$_POST['localidad']."'"; }
		if ($_POST['fecha_ini']!="") { $select.=" AND a.fecha_sta>='".$_POST['fecha_ini']."'"; }
		if ($_POST['fecha_fin']!="") { $select.=" AND a.fecha_sta<='".$_POST['fecha_fin']."'"; }
		
		$select.=" ORDER BY trim(a.nombre)";
		$rspersonal=mysql_query($select);
		$totalRegistros = mysql_num_rows($rspersonal);
		
		if(mysql_num_rows($rspersonal)>0) 
		{
			
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<thead><tr bgcolor="#E9F2F8"><td colspan="14">'.mysql_num_rows($rspersonal).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th><th>Imprimir</th>';
			echo '<th>Plaza</th>';
			echo '<th><a href="#" onclick="SortTable(3,\'N\',\'tabla1\');">No.</a></th>
			<th><a href="#" onclick="SortTable(4,\'S\',\'tabla1\');">Nombre</a></th><th>Clave Ecologica</th>
					<th>Fecha de Ingreso</th><th>Fecha de Estatus</th><th>Estatus</th><th>RFC</th><th>Sueldo</th><th>Puesto</th>';
			echo '</tr></thead><tbody>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			while($Personal=mysql_fetch_array($rspersonal)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'personal.php\',\'\',\'1\','.$Personal['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Conductor['nombre'].'"></a></td>';
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'imp_personal.php\',\'_blank\',\'0\','.$Personal['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$Conductor['nombre'].'"></a></td>';
				echo '<td>'.utf8_encode($array_plaza[$Personal['plaza']]).'</td>';
				echo '<td align="center">'.$Personal['cve'].'</td>';
				if(file_exists("imgpersonal/foto".$Personal['cve'].".jpg"))
					echo '<td align="left" onMouseOver="document.getElementById(\'foto'.$Personal['cve'].'\').style.visibility=\'visible\';" onMouseOut="document.getElementById(\'foto'.$Personal['cve'].'\').style.visibility=\'hidden\';">'.$Personal['nombre'].'<img width="200" id="foto'.$Personal['cve'].'" height="250" style="position:absolute;visibility:hidden" src="imgpersonal/foto'.$Personal['cve'].'.jpg?'.date('h:i:s').'" border="1"></td>';
				else
					echo '<td align="left">'.htmlentities(utf8_encode(trim($Personal['nombre']))).'</td>';
				echo '<td align="center">'.$Personal['clave_ecologica'].'</td>';
				echo '<td align="center">'.$Personal['fecha_ini'].'</td>';
				echo '<td align="center">'.$Personal['fecha_sta'].'</td>';
				echo '<td align="center">'.$array_estatus_personal[$Personal['estatus']].'</td>';
				echo '<td align="center">'.$Personal['rfc'].'</td>';
				echo '<td align="right">'.number_format($Personal['salario_integrado']+$Personal['compensacion'],2).'</td>';
				echo '<td align="center">'.$array_puestos[$Personal['puesto']].'</td>';
				echo '</tr>';
			}
			
			echo '</tbody>
				<tr>
				<td colspan="14" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
				</tr>
			</table>';
			
		} else {
			echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
		}
		exit();	
}

if($_POST['ajax']==2){
	if(trim($_POST['rfc'])!=""){
		$rsnum=mysql_query("SELECT * FROM personal WHERE rfc='".$_POST['rfc']."' AND cve!='".$_POST['personal']."'");
		if($Num=mysql_fetch_array($rsnum))
			echo 'si';
		else
			echo 'no';
	}
	else{
		echo 'no';
	}
	exit();
}	

if($_POST['ajax']==4){
	if(trim($_POST['clave_ecologica'])!=""){
		$rsnum=mysql_query("SELECT * FROM personal WHERE clave_ecologica='".$_POST['clave_ecologica']."' AND cve!='".$_POST['personal']."'");
		if($Num=mysql_fetch_array($rsnum))
			echo 'si';
		else
			echo 'no';
	}
	else{
		echo 'no';
	}
	exit();
}	

if($_POST['ajax']==3) {
		//Listado de Historial
		$select= " SELECT * FROM cambios_datos_personal WHERE cve_personal='".$_POST['personal']."'";
		$rscambios=mysql_query($select);
		$totalRegistros = mysql_num_rows($rscambios);
		if($totalRegistros / $eRegistrosPagina > 1) 
		{
			$eTotalPaginas = $totalRegistros / $eRegistrosPagina;
			if(is_int($eTotalPaginas))
			{$eTotalPaginas--;}
			else
			{$eTotalPaginas = floor($eTotalPaginas);}
		}
		$select .= " ORDER BY cve desc  LIMIT ".$primerRegistro.",".$eRegistrosPagina;
		$rscambios=mysql_query($select) or die(mysql_error() . $select);
		
		if(mysql_num_rows($rscambios)>0) 
		{
		
			echo '<h3 align="center"> Historial de Cambios </h3>';
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Fecha Mov</th><th>Folio</th>';
			echo '<th>Dato</th><th>Valor Nuevo</th><th>Valor Anterior</th><th>Fecha</th><th>Usuario</th>';
			echo '</tr>';
			$i=0;
			while($Cambios=mysql_fetch_array($rscambios)) {
				rowb();
		//		echo '<td align="center" width="40" nowrap><a href="#" onClick="document.forma.regcve_unidad.value=\''.$Cambios['cve_unidad'].'\';document.forma.regplaza.value=\''.$Cambios['plaza'].'\';atcr(\'parque.php\',\'\',\'1\','.$Cambios['cve'].')">'.$Cambios['folio'].'</a></td>';
				echo '<td align="center">'.$Cambios['fecha_reg'].'</td>';
				echo '<td align="center">'.$Cambios['folio'].'</td>';
				echo '<td align="left">'.htmlentities($Cambios['dato']).'</td>';
				if($Cambios['dato']=="Estatus"){
					echo '<td align="left">'.$array_estatus_personal[$Cambios['valor_nuevo']].'</td>';
					echo '<td align="left">'.$array_estatus_personal[$Cambios['valor_anterior']].'</td>';
				}else{
					if($Cambios['dato']=="Plaza"){
						echo '<td align="left">'.$array_plaza[$Cambios['valor_nuevo']].'</td>';
						echo '<td align="left">'.$array_plaza[$Cambios['valor_anterior']].'</td>';
					}
					else{
						if($Cambios['dato']=="Tipo Conductor"){
							echo '<td align="left">'.$array_tipo_conductor[$Cambios['valor_nuevo']].'</td>';
							echo '<td align="left">'.$array_tipo_conductor[$Cambios['valor_anterior']].'</td>';
						}else{
							if($Cambios['dato']=="Puesto"){
								echo '<td align="left">'.$array_puestos[$Cambios['valor_nuevo']].'</td>';
								echo '<td align="left">'.$array_puestos[$Cambios['valor_anterior']].'</td>';
							}else{
								if($Cambios['dato']=="Unidad"){
									$rsparque_nuevo=mysql_query("SELECT * FROM parque WHERE cve='".$Cambios['valor_nuevo']."'");
									$Parque_nuevo=mysql_fetch_array($rsparque_nuevo);
									$rsparque_anterior=mysql_query("SELECT * FROM parque WHERE cve='".$Cambios['valor_anterior']."'");
									$Parque_anterior=mysql_fetch_array($rsparque_anterior);
									echo '<td align="left">'.$Parque_nuevo['no_eco'].' - '.$array_tipo_vehiculo[$Parque_nuevo['tipo_vehiculo']].'</td>';
									echo '<td align="left">'.$Parque_anterior['no_eco'].' - '.$array_tipo_vehiculo[$Parque_anterior['tipo_vehiculo']].'</td>';
								}else{
									echo '<td align="left">'.$Cambios['valor_nuevo'].'</td>';
									echo '<td align="left">'.$Cambios['valor_anterior'].'</td>';
								}
							}
						}
					}
				}	
				echo '<td align="center">'.$Cambios['fecha'].'</td>';
				echo '<td align="left">'.$array_usuario[$Cambios['usuario']].'';
				$i++;
				echo '</tr>';
			}
			
			echo '	
				<tr>
				<td colspan="9" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
				</tr>
			</table>';
			
		} else {
			echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
		}
		exit();	
}	



top($_SESSION);

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		echo '<style>
		#Cambios {
			width: 70%;
			border-style: solid;
			border-width: 1px;
			border-color: #96BDE0;
		}
		</style>';
		$select=" SELECT * FROM personal WHERE cve='".$_POST['reg']."' ";
		$rspersonal=mysql_query($select);
		$Personal=mysql_fetch_array($rspersonal);
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1){
				echo '<td><a href="#" onClick="
				if(document.forma.afiliado_imss[0].checked && document.forma.fecha_imss.value==\'\') 
					alert(\'Necesita seleccionar la fecha IMSS\'); 
				else if(document.forma.tiene_licencia.checked && document.forma.licencia.value==\'\') 
					alert(\'Necesita ingresar la licencia\'); 
				else if(document.forma.tiene_licencia.checked && document.forma.fecha_venc_licencia.value==\'\') 
					alert(\'Necesita seleccionar la fecha de vencimiento de la licencia\'); 
				else 
					validar_rfc(\''.$_POST['reg'].'\');"
				><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			
			}
			echo '<td><a href="#" onClick="atcr(\'personal.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Catálogo de Personal</td></tr>';
		echo '</table>';
		echo '<table width="100%"><tr><td>';
		echo '<table>';
		echo '<tr><th align="left">Plaza</th><td><select name="plaza" id="plaza">';
		//if($Personal['plaza']==0 || $_SESSION['TipoUsuario']==1 || $Personal['estatus'] == 2){
		if($Personal['plaza']==0 || nivelUsuario()>1){
			if($_POST['plazausuario']==0)
				echo '<option value="0">---Seleccione una Plaza---</option>';
			$rsPlaza=mysql_query("SELECT * FROM plazas ORDER BY nombre");
		}
		else{
			$rsPlaza=mysql_query("SELECT * FROM plazas WHERE cve='".$Personal['plaza']."' ORDER BY nombre");
		}
		while($Plaza=mysql_fetch_array($rsPlaza)){
			if($_POST['plazausuario']==0 || $_POST['plazausuario']==$Plaza['cve'] || $Personal['estatus'] == 2 || nivelUsuario()>1){
				echo '<option value="'.$Plaza['cve'].'"';
				if($Plaza['cve']==$Personal['plaza']) echo ' selected';
				echo '>'.$Plaza['numero'].'</option>';
			}
		}
		echo '</select></td></tr>';
		if($Personal['fecha_ini']=="0000-00-00") $Personal['fecha_ini']="";
		echo '<tr><th align="left">Fecha Ingreso</th><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" value="'.$Personal['fecha_ini'].'" class="readOnly" readonly>';
		if($_POST['reg']==0 || $Personal['fecha_ini']=="" || $_POST['cveusuario']==1) echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
		echo '</td></tr>';
		if($_POST['reg']>0) { 
			if($Personal['estatus']==2) $visibility="visible";
			else $visibility="hidden";
			echo '<tr><th align="left">Estatus</th><td><select name="estatus" id="estatus" class="textField" onChange="
				if(this.value==\'2\') document.getElementById(\'capatipobaja\').style.visibility=\'visible\';
				else document.getElementById(\'capatipobaja\').style.visibility=\'hidden\';">';
			foreach($array_estatus_personal as $k=>$v){
				echo '<option value="'.$k.'"';
				if($k==$Personal['estatus']) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select>&nbsp;&nbsp;&nbsp;<span id="capatipobaja" style="visibility:'.$visibility.';"><b>Tipo de Baja&nbsp;</b>
				<input type="radio" name="tipo_baja" id="tipo_baja" value="0"';
			if($Personal['tipo_baja']!=1) echo ' checked';
			echo '>Normal&nbsp;&nbsp;<input type="radio" name="tipo_baja" id="tipo_baja" value="1"';
			if($Sepronal['tipo_baja']==1) echo ' checked';
			echo '>Renuncia Voluntaria';	
			echo '</span></td></tr>';
			if($Personal['fecha_sta']=="0000-00-00") $Personal['fecha_sta']="";		
			echo '<tr><th align="left">Fecha Cambio Estatus</th><td><input type="text" name="fecha_sta" id="fecha_sta" class="readOnly" size="15" value="'.$Personal['fecha_sta'].'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_sta,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		}
		else{
			echo '<input type="hidden" name="estatus" id="estatus" value="1">';
		}
		echo '<tr><th align="left">No.</th><td><input type="text" name="folio" id="folio" class="readOnly" size="20" value="'.$Personal['cve'].'" readonly></td></tr>';
		echo '<tr><th align="left">Ecologia</th><td><input type="checkbox" name="ecologia" id="ecologia" value="1" onClick="if(this.checked) $(\'.recologia\').show(); else $(\'.recologia\').hide();"';
		if($Personal['ecologia']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr class="recologia"';
		if($Personal['ecologia']!=1) echo ' style="display:none;"';
		if($Personal['clave_ecologica']!=0 && $_SESSION['TipoUsuario']!=1){
			echo '><th align="left">Clave Ecologica</th><td><input type="text" name="clave_ecologica" id="clave_ecologica" class="readOnly" size="30" value="'.$Personal['clave_ecologica'].'" readOnly></td></tr>';
		}
		else{
			echo '><th align="left">Clave Ecologica</th><td><input type="text" name="clave_ecologica" id="clave_ecologica" class="textField" size="30" value="'.$Personal['clave_ecologica'].'"></td></tr>';
		}
		if($Personal['fecha_eco']=="0000-00-00") $Personal['fecha_eco']="";
		echo '<tr><th align="left">Fecha Alta Ecologia</th><td><input type="text" name="fecha_eco" id="fecha_eco"  size="15" value="'.$Personal['fecha_eco'].'" class="readOnly" readonly>';
		if($_POST['reg']==0 || $Personal['fecha_eco']=="") echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_eco,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
		echo '<tr><th align="left">Administrativo</th><td><input type="checkbox" name="administrativo" id="administrativo" value="1"';
		if($Personal['administrativo']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr><th align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="50" value="'.$Personal['nombre'].'"></td></tr>';
		echo '<tr><th align="left">RFC</th><td><input type="text" name="rfc" id="rfc" class="textField" size="25" value="'.$Personal['rfc'].'" ></td></tr>';
		echo '<tr><th align="left">CURP</th><td><input type="text" name="curp" id="curp" class="textField" size="25" value="'.$Personal['curp'].'" ></td></tr>';
		echo '<tr><th align="left">Email</th><td><input type="text" name="email" id="email" class="textField" size="50" value="'.$Personal['email'].'" ></td></tr>';
		echo '<tr><th align="left">Calle</th><td><input type="text" name="calle" id="calle" class="textField" size="50" value="'.$Personal['calle'].'"></td></tr>';
		echo '<tr><th align="left">No. Exterior</th><td><input type="text" name="num_ext" id="num_ext" class="textField" size="10" value="'.$Personal['num_ext'].'"></td></tr>';
		echo '<tr><th align="left">No. Interior</th><td><input type="text" name="num_int" id="num_int" class="textField" size="10" value="'.$Personal['num_int'].'"></td></tr>';
		echo '<tr><th align="left">Colonia</th><td><input type="text" name="colonia" id="colonia" class="textField" size="50" value="'.$Personal['colonia'].'"></td></tr>';
		echo '<tr style="display:none;"><th align="left">Localidad</th><td><input type="text" name="localidad" id="localidad" class="textField" size="50" value="'.$Personal['localidad'].'"></td></tr>';
		echo '<tr><th align="left">Municipio</th><td><input type="text" name="municipio" id="municipio" class="textField" size="50" value="'.$Personal['municipio'].'"></td></tr>';
		echo '<tr><th align="left">Estado</th><td><input type="text" name="estado" id="estado" class="textField" size="50" value="'.$Personal['estado'].'"></td></tr>';
		echo '<tr><th align="left">Codigo Postal</th><td><input type="text" name="codigopostal" id="codigopostal" class="textField" size="10" value="'.$Personal['codigopostal'].'"></td></tr>';
		echo '<tr><th align="left">Lugar de Nacimiento</th><td><input type="text" name="nac" id="nac" class="textField" size="50" value="'.$Personal['lugar_nacimiento'].'" ></td></tr>';
		echo '<tr><th align="left">Edad</th><td><input type="text" name="edad" id="edad" class="readOnly" size="5" value="'.edad($Personal['rfc']).'" readonly></td></tr>';
		echo '<tr><th align="left">Telefono</th><td><input type="text" name="telefono" id="telefono" class="textField" size="20" value="'.$Personal['telefono'].'" ></td></tr>';
		echo '<tr><th align="left">Puesto</th><td><select name="puesto" id="puesto" class="textField">';
		if($Personal['puesto']==0){
			echo '<option value="0">--- Seleccione Puesto---</option>';
		}
		foreach($array_puestos as $k=>$v){
			//if($Personal['puesto']==0 || $Personal['puesto']==$k){
				echo '<option value="'.$k.'"';
				if($Personal['puesto']==$k) echo ' selected';
				echo '>'.$v.'</option>';
			//}
		}
		echo '</select></td></tr>';
		echo '<tr style="display:none;"><th align="left">Departamento</th><td><select name="departamento" id="departamento"><option value="0">--- Seleccione ---</option>';
		foreach($arreglo_departamentos as $k=>$v){
			echo '<option value="'.$k.'"';
			if($Personal['departamento']==$k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		
		echo '<tr><th align="left">Observaciones</th><td><textarea class="textField" name="observaciones" id="observaciones" rows="5" cols="50">'.$Personal['obs'].'</textarea></td></tr>';
		echo '<tr><th align="left">IMSS</th><td><input type="text" name="imss" id="imss" class="textField" size="20" value="'.$Personal['imss'].'"></td></tr>';
		if($Personal['fecha_imss']=="0000-00-00") $Personal['fecha_imss']="";
		echo '<tr><th align="left">Fecha IMSS</th><td><input type="text" name="fecha_imss" id="fecha_imss"  size="15" value="'.$Personal['fecha_imss'].'" class="readOnly" readonly>';
		echo '&nbsp;<span style="cursor:pointer;" onClick="displayCalendar(document.forms[0].fecha_imss,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></span>';
		echo '</td></tr>';
		echo '<tr><th align="left">Afiliado al IMSS</th><td><input type="radio" name="afiliado_imss" id="afiliado_imss" value="SI"';
		if($Personal['afiliado_imss']=="SI"){ 
			echo ' checked';
			$visibility="visible";
		}
		echo ' onClick="document.getElementById(\'capamonto2\').style.visibility=\'visible\';">Si&nbsp;&nbsp;<input type="radio" name="afiliado_imss" id="afiliado_imss" value="NO"';
		if($Personal['afiliado_imss']!="SI"){ 
			echo ' checked';
			$visibility="hidden";
		}
		echo ' onClick="document.getElementById(\'capamonto2\').style.visibility=\'hidden\';">No &nbsp;&nbsp;<!--<input type="radio" name="afiliado_imss" id="afiliado_imss" value="SN"';
		if($Personal['afiliado_imss']=="SN"){ 
			echo ' checked';
			$visibility="visible";
		}
		echo ' onClick="document.getElementById(\'capamonto2\').style.visibility=\'visible\';">Ambos-->&nbsp;&nbsp;&nbsp;<span id="capamonto2" style="visibility:'.$visibility.';"><input type="hidden" name="monto_imss" id="monto_imss" value="'.$Personal['monto_imss'].'"';
		if($Personal['monto_imss']>0 && nivelUsuario()<3)
			echo 'class="readOnly" readonly';
		else
			echo 'class="textField"';
		echo '></span></td></tr>';
		echo '<tr><th align="left">Infonavit</th><td><input type="radio" name="infonavit" id="infonavit" value="SI"';
		if($Personal['infonavit']=="SI"){ 
			echo 'checked';
			$visibility="visible";
		}
		echo ' onClick="document.getElementById(\'capamonto\').style.visibility=\'visible\';">Si&nbsp;&nbsp;<input type="radio" name="infonavit" id="infonavit" value="NO"';
		if($Personal['infonavit']!="SI"){ 
			echo 'checked';
			$visibility="hidden";
		}
		echo ' onClick="document.getElementById(\'capamonto\').style.visibility=\'hidden\';">No &nbsp;&nbsp;&nbsp;<span id="capamonto" style="visibility:'.$visibility.';">Monto&nbsp;<input type="text" name="monto_infonavit" id="monto_infonavit" value="'.$Personal['monto_infonavit'].'"';
		if($Personal['monto_infonavit']>0 && nivelUsuario()<3)
			echo 'class="readOnly" readonly';
		else
			echo 'class="textField"';
		echo '></span></td></tr>';
		echo '<tr style="display:none;"><th align="left">Asistencia</th><td><input type="radio" name="asistencia" id="asistencia" value="S"';
		if($Personal['asistencia']=="S") echo 'checked';
		echo '>Si&nbsp;&nbsp;<input type="radio" name="asistencia" id="asistencia" value="N"';
		if($Personal['asistencia']!="S") echo 'checked';
		echo '>No</td></tr>';
		echo '<tr><th align="left">Cuenta</th><td><input type="text" name="cuenta" id="cuenta" class="textField" size="20" value="'.$Personal['cuenta'].'" ></td></tr>';
		echo '<tr><th align="left">CLABE</th><td><input type="text" name="clabe" id="clabe" class="textField" size="20" value="'.$Personal['clabe'].'" ></td></tr>';
		echo '<tr><th align="left">Banco</th><td><select name="banco" id="banco" class="textField"><option value="">---Seleccione un banco---</option>';
		foreach($array_banco as $k=>$v){
			echo '<option value="'.$k.'"';
				if($k==$Personal['banco']) echo ' selected';
				echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Tipo de Contrato</th><td><select name="tipo_contrato" id="tipo_contrato" class="textField"><option value="0">---Seleccione un tipo de contrato---</option>';
		foreach($array_tipo_contrato as $k=>$v){
			echo '<option value="'.$k.'"';
				if($k==$Personal['tipo_contrato']) echo ' selected';
				echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Tipo de Jornada</th><td><select name="tipo_jornada" id="tipo_jornada" class="textField"><option value="0">---Seleccione un tipo de jornada---</option>';
		foreach($array_tipo_jornada as $k=>$v){
			echo '<option value="'.$k.'"';
				if($k==$Personal['tipo_jornada']) echo ' selected';
				echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Tipo Regimen</th><td><select name="tipo_regimen" id="tipo_regimen" class="textField"><option value="0">---Seleccione un tipo regimen---</option>';
		foreach($array_tipo_regimen as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$Personal['tipo_regimen']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Metodo de Pago</th><td><select name="metodo_pago" id="metodo_pago" class="textField"><option value="">---Seleccione un metodo de pago---</option>';
		foreach($array_tipo_pago as $k=>$v){
			echo '<option value="'.$k.'"';
				if($k==$Personal['metodo_pago']) echo ' selected';
				echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Sueldo Total Diario</th><td><input type="text" name="salario_integrado" id="salario_integrado" value="'.$Personal['salario_integrado'].'" class="textField" size="15"></td></tr>';
		echo '<tr><th align="left">Salario Diario Integrado</th><td><input type="text" class="textField" name="sdi" id="sdi" value="'.$Personal['sdi'].'"></td></tr>';
		echo '<tr><th align="left">Observaciones Cambio de Datos</th><td><textarea class="textField" name="obs" id="obs" cols="50" rows="5"></textarea></td></tr>';
		echo '<tr><th align="left">Tiene Licencia</th><td><input type="checkbox" name="tiene_licencia" id="tiene_licencia" value="1" onClick="if(this.checked) $(\'.rlicencia\').show(); else $(\'.rlicencia\').hide();"';
		if($Personal['tiene_licencia']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr class="rlicencia"';
		if($Personal['tiene_licencia']!=1) echo ' style="display:none;"';
		echo '><th align="left">Licencia</th><td><input type="text" name="licencia" id="licencia" value="'.$Personal['licencia'].'" class="textField" size="20"></td></tr>';
		if($Personal['fecha_venc_licencia']=="0000-00-00") $Personal['fecha_venc_licencia']="";
		echo '<tr class="rlicencia"';
		if($Personal['tiene_licencia']!=1) echo ' style="display:none;"';
		echo '><th align="left">Fecha Vencimiento Licencia</th><td><input type="text" name="fecha_venc_licencia" id="fecha_venc_licencia"  size="15" value="'.$Personal['fecha_venc_licencia'].'" class="readOnly" readonly>';
		echo '&nbsp;<span style="cursor:pointer;" onClick="displayCalendar(document.forms[0].fecha_venc_licencia,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></span>';
		echo '</td></tr>';
		echo '<tr><td colspan="2" class="tableEnc">Documentacion Requerida</td></tr>';
		$res1=mysql_query("SELECT a.cve,a.nombre,b.entregado FROM cat_personal_documentos a LEFT JOIN personal_documentos b ON a.cve = b.documento AND b.personal = '".$_POST['reg']."' ORDER BY a.nombre");
		while($row1=mysql_fetch_array($res1)){
			echo '<tr><th align="left">'.$row1['nombre'].'</th><td><input type="hidden" name="personal_documentos['.$row1['cve'].']" value="'.$row1['entregado'].'" id="personal_documentos_'.$row1['cve'].'">
			<input type="checkbox" onClick="if(this.checked) document.getElementById(\'personal_documentos_'.$row1['cve'].'\').value=\'1\'; else document.getElementById(\'personal_documentos_'.$row1['cve'].'\').value=\'0\';"';
			if($row1['entregado']==1) echo ' checked';
			echo '></td></tr>';
		}
		echo '</table>';
		echo '</td><td valign="top">';
		echo '<table align="right"><tr><td colspan="2" align="center"><img width="200" height="250" src="imgpersonal/foto'.$_POST['reg'].'.jpg?'.date('h:i:s').'" border="1"></td></tr>';
		echo '<tr><th>Nueva Foto</th><td><input type="file" name="foto" id="foto"></td></tr>';
		echo '<tr><th>Borrar Foto</th><td><input type="checkbox" name="borrar_foto" id="borrar_foto" value="S"></td></tr></table>';
		echo '</td></tr></table>';
		echo '<BR>';
		echo '<div id="Cambios">';
		echo '</div>';
	//	echo '<input type="hidden" name="regplaza" id="plaza" value="'.$_SESSION['PlazaUsuario'].'">';
	//	echo '<input type="hidden" name="regunidad" id="unidad" value="">';
		
		echo '<script language="javascript">
				function cambiospersonal()
					{
						document.getElementById("Cambios").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
						objeto=crearObjeto();
						if (objeto.readyState != 0) {
							alert("Error: El Navegador no soporta AJAX");
						} else {
							objeto.open("POST","personal.php",true);
							objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
							objeto.send("ajax=3&personal='.$_POST['reg'].'&plazausuario='.$_POST['plazausuario'].'&numeroPagina="+document.getElementById("numeroPagina").value);
							objeto.onreadystatechange = function()
							{
								if (objeto.readyState==4)
									{document.getElementById("Cambios").innerHTML = objeto.responseText;}
							}
						}
						document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
					}
				function moverPagina(x) {
					document.getElementById("numeroPagina").value = x;
					cambiospersonal();
				}	
				function validar_rfc(reg){
					if(document.getElementById("plaza").value=="0")
						alert("Necesita seleccionar la plaza del personal");
					else if(document.getElementById("puesto").value=="0")
						alert("Necesita seleccionar el puesto del personal");
					else{
						objeto=crearObjeto();
						if (objeto.readyState != 0) {
							alert("Error: El Navegador no soporta AJAX");
						} else {
							objeto.open("POST","personal.php",true);
							objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
							objeto.send("ajax=2&personal="+reg+"&rfc="+document.getElementById("rfc").value+"&plazausuario='.$_POST['plazausuario'].'");
							objeto.onreadystatechange = function(){
								if (objeto.readyState==4){
									if(objeto.responseText=="si")
										alert("El RFC ya existe");
									else if(document.forma.ecologia.checked)
										validar_clave_ecologica(reg)
									else
										atcr("personal.php","",2,reg);
								}
							}
						}
					}
				}
				function validar_clave_ecologica(reg){
					if((document.getElementById("clave_ecologica").value/1)==0)
						alert("Necesita ingresar la clave ecologica");
					else{
						objeto=crearObjeto();
						if (objeto.readyState != 0) {
							alert("Error: El Navegador no soporta AJAX");
						} else {
							objeto.open("POST","personal.php",true);
							objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
							objeto.send("ajax=4&personal="+reg+"&clave_ecologica="+document.getElementById("clave_ecologica").value+"&plazausuario='.$_POST['plazausuario'].'");
							objeto.onreadystatechange = function(){
								if (objeto.readyState==4){
									if(objeto.responseText=="si")
										alert("La clave ecologica ya existe");
									else
										atcr("personal.php","",2,reg);
								}
							}
						}
					}
				}
				
				cambiospersonal()
				  </script>'; 
			
		
	}
	

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;
					<a href="#" onClick="atcr(\'personal.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo&nbsp;&nbsp;
					<a href="#" onClick="atcr(\'personal.php\',\'_blank\',\'50\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Excel</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza" class="textField">';
		if($_POST['plazausuario']==0)
			echo '<option value="all">---Todas---</option>';
		foreach($array_plaza as $k=>$v){
			if($_POST['plazausuario']==0 || $_POST['plazausuario']==$k)
				echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td><td></td><td>&nbsp;</td></tr>';
		echo '<tr';
		if($_POST['plazausuario']>0) echo ' style="display:none;"';
		echo '><td>Localidad</td><td><select name="localidad" id="localidad" class="textField"><option value="all">---Todos---</option>';
		foreach($arreglo_departamentos as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus" class="textField"><option value="all">---Todos---</option>';
		foreach($array_estatus_personal as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==1) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td><td></td><td>&nbsp;</td></tr>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Credencia</td><td><input type="text" name="credencial" id="credencial" class="textField"></td></tr>'; 
		echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" class="textField"></td></tr>'; 
		echo '<tr><td>Clave Ecologica</td><td><input type="text" name="num" id="num" class="textField"></td></tr>'; 
		echo '<tr><td>Puesto</td><td><select name="puesto" id="puesto" class="textField"><option value="all">---Todos---</option>';
		foreach($array_puestos as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Asegurado</td><td><select name="asegurado" id="asegurado" class="textField"><option value="all">---Todos---</option>';
		echo '<option value="SI">Si</option>';
		echo '<option value="NO">No</option>';
		echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
	
bottom();



/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros(ordenamiento,orden)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","personal.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&credencial="+document.getElementById("credencial").value+"&localidad="+document.getElementById("localidad").value+"&asegurado="+document.getElementById("asegurado").value+"&nombre="+document.getElementById("nombre").value+"&puesto="+document.getElementById("puesto").value+"&estatus="+document.getElementById("estatus").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&num="+document.getElementById("num").value+"&plaza="+document.getElementById("plaza").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	
	
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}';	
	if($_POST['cmd']<1){
	echo '
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	';
	}
	echo '
	
	</Script>
';

?>

