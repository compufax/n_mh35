<?php
include ("main.php"); 
require("fpdf153/fpdf.php");
include("numlet.php");  
$res = mysql_query("SELECT * FROM usuarios");
while($row=mysql_fetch_array($res)){
	$array_usuario[$row['cve']]=$row['usuario'];
}

$array_motivo = array();
$optionsmotivos='<option value="0">Seleccione</option>';
$res = mysql_query("SELECT * FROM motivos_gastos ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_motivo[$row['cve']]=$row['nombre'];
	$optionsmotivos.='<option value="'.$row['cve'].'">'.$row['nombre'].'</option>';
}

$array_consignatario = array();
$res = mysql_query("SELECT * FROM consignatarios ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_consignatario[$row['cve']]=$row['nombre'];
}

$array_plaza=array(0=>"Ninguna");
$res=mysql_db_query($base,"SELECT * FROM plazas a WHERE estatus!='I' ORDER BY numero,nombre");
while($row=mysql_fetch_array($res)){
	$array_plaza[$row['cve']]=$row['numero'].' '.$row['nombre'];
}

if($_POST['cmd']==101){
	//180 el tamaño toal de la hoja dividido entre la primera variable
	//260 el tamaño toal de la hoja dividido entre la primera variable horizonta
	//ob_end_clean();
  //include('fpdf153/fpdf.php');
class PDF1 extends PDF_MC_Table 
{
}

$pdf = new PDF1('L','mm','LETTER');
$pdf->AliasNbPages();
    $array_plaza=array();
	$res=mysql_db_query($base,"SELECT * FROM plazas");
	while($row=mysql_fetch_array($res)){
	$array_plaza[$row['cve']]=$row['nombre'];
	}
	$res1=mysql_db_query($base,"SELECT * FROM salida_gastos WHERE cve='".$_POST['reg']."'") or die(mysql_error());
	$row=mysql_fetch_array($res1);
	$pdf->AddPage();
	//global $row,$array_motivo,$array_plaza;
  $pdf->SetFont('Arial','B',8);
  $pdf->Cell(60,10,'Fecha de Solicitud : '.$row['fecha'],0,0,'L');
  $pdf->Ln();
  $pdf->SetFont('Arial','B',10);
  $pdf->SetFillColor(185,185,185);
  $pdf->Cell(260,10,'Requisicin de Compra ',1,0,'C',true);
  $pdf->Ln();
  $pdf->SetFont('Arial','B',8);
  $pdf->Cell(220,5,'Tipo de Compra : ',1,0,'L',true);
  $pdf->Cell(20,5,'No Folio',1,0,'L',true);
  $pdf->Cell(20,5,$row['cve'],1,0,'L');
  $pdf->Ln();
  $pdf->Cell(70,5,'Insumos de Verificentro : ',1,0,'L');
  $pdf->Cell(10,5,'',1,0,'L');
  $pdf->Cell(180,5,'',1,0,'L');
  //$pdf->SetFont('Arial','B',5);
  $pdf->Ln();
  $pdf->Cell(70,5,'Insumo de Computadora : ',1,0,'L');
  $pdf->Cell(10,5,'',1,0,'L');
  $pdf->Cell(180,5,'',1,0,'L');
  $pdf->Ln();
  $pdf->Cell(70,5,'Alimentos : ',1,0,'L');
  $pdf->Cell(10,5,'',1,0,'L');
  $pdf->Cell(180,5,'',1,0,'L');
  $pdf->Ln();
  $pdf->Cell(70,5,'Servicios : ',1,0,'L');
  $pdf->Cell(10,5,'',1,0,'L');
  $pdf->Cell(180,5,'',1,0,'L');
  $pdf->Ln();
  $pdf->Cell(70,5,'Otros : ',1,0,'L');
  $pdf->Cell(10,5,'',1,0,'L');
  $pdf->Cell(180,5,'',1,0,'L');
  $pdf->Ln();
  $pdf->Cell(150,8,'No de Verificentro : '.$array_plaza[$row['plaza']],1,0,'L',true);
  $pdf->Cell(110,8,'Fecha de Solicitud :'.fecha_letra($row['fecha']),1,0,'L',true);
  $pdf->Ln();
  $pdf->Cell(35,8,'Verificentro/Oficina : ',1,0,'L',true);
  $pdf->Cell(93,8,$array_plaza[$row['plaza']],1,0,'L',true);
  $pdf->Cell(33,8,'Formaa de pago : ',1,0,'L',true);
  $pdf->Cell(33,8,'Cheque :',1,0,'L',true);
  $pdf->Cell(33,8,'Transferencia : '.'  ',1,0,'L',true);
  $pdf->Cell(33,8,'Efectivo :',1,0,'L',true);
  $pdf->Ln();
  $pdf->Cell(260,8,'Proyecto u/o Programa : ',1,0,'L',true);
  $pdf->Ln();
  $pdf->Cell(10,8,'No ',1,0,'C',true);
  $pdf->Cell(15,8,'Cantidad  ',1,0,'C',true);
  $pdf->Cell(35,8,'U.Medida  ',1,0,'C',true);
  $pdf->Cell(20,8,'P.U ',1,0,'C',true);
  $pdf->Cell(24.5,8,'Precio Total',1,0,'C',true);
  $pdf->Cell(155.5,8,'Descripcion  ',1,0,'C',true);
  $pdf->Ln();
  $baja=1;
  $costo_=0;
  $res1=mysql_query("SELECT * FROM salida_gastos_detalle WHERE plaza = '".$row['plaza']."' AND gasto='".$row['cve']."'") or die(mysql_error());
	while($row1=mysql_fetch_array($res1)){
		if($pdf->GetY()>=180){$pdf->Ln(); $pdf->Ln(); $pdf->Ln();}
		$pdf->Cell(10,5,mysql_num_rows($res1).'-'.$baja,1,0,'L');
		$pdf->Cell(15,5,$row1['cantidad'],1,0,'L');
		$pdf->Cell(35,5,$array_motivo[$row1['motivo']],1,0,'L');
		$pdf->Cell(20,5,'',1,0,'L');
		$pdf->Cell(24.5,5,number_format($row1['importe'],2),1,0,'L');
		$pdf->Cell(155.5,5,$row1['concepto'],1,0,'C');
		$pdf->Ln();
		$baja++;
		$costo_= $costo_ + $row1['importe'];
	}
   $pdf->Cell(80,8,'Costo Total : ',1,0,'L');
   $pdf->Cell(24.5,8,number_format($costo_,2),1,0,'C');
   $pdf->Ln();
   $pdf->Cell(80,8,'Se Cumple con lo solicitado : ',1,0,'L',true);
   $pdf->Cell(176,8,'',1,0,'C');
   $pdf->Ln();
   $pdf->Cell(38,8,'Para Ser Utilizado en : ',1,0,'L',true);
   $com=$pdf->GetY();
   $pdf->Cell(222,8,'',1,0,'C');
      if($com>=149){$pdf->Ln(); $pdf->Ln(); $pdf->Ln(); $pdf->Ln(); $pdf->Ln();}
   $pdf->Cell(30,8,'',0,0,'L');
   $pdf->SetY(160);
   $pdf->Cell(30,8,'',0,0,'L');
   $pdf->Cell(59.4,24,'',1,0,'C');
   $pdf->Cell(10,8,'',0,0,'L');
   $pdf->Cell(59.4,24,'',1,0,'C');
   $pdf->Cell(10,8,'',0,0,'L');
   $pdf->Cell(59.4,24,'',1,0,'C');
   $pdf->Cell(10,8,'',0,0,'L');
   $pdf->SetXY(10,160);
   $pdf->Cell(30,8,'',0,0,'L');
   $pdf->Cell(59.4,8,'Solicitante',0,0,'C');
   $pdf->Cell(10,8,'',0,0,'L');
   $pdf->Cell(59.4,8,'Autortizo',0,0,'C');
   $pdf->Cell(10,8,'',0,0,'L');
   $pdf->Cell(59.4,8,'Aprobo',0,0,'C');
   $pdf->Ln();
   $pdf->Cell(30,8,'',0,0,'L');
   $pdf->Cell(59.4,8,'____________________________',0,0,'C');
   $pdf->Cell(10,8,'',0,0,'L');
   $pdf->Cell(59.4,8,'____________________________',0,0,'C');
   $pdf->Cell(10,8,'',0,0,'L');
   $pdf->Cell(59.4,8,'____________________________',0,0,'C');
   $pdf->Ln();
   $pdf->Cell(30,5,'',0,0,'L');
   $pdf->Cell(59.4,5,$array_consignatario[$row['consignatario']],0,0,'C');
   $pdf->Cell(10,5,'',0,0,'L');
   $pdf->Cell(59.4,5,'',0,0,'C');
   $pdf->Cell(10,5,'',0,0,'L');
   $pdf->Cell(59.4,5,'',0,0,'C');
   $pdf->Cell(59.4,5,'',0,0,'C');
	$pdf->Output();
  //$this=new FPDF('L','mm','LETTER');
  //$this->AddPage();
  //$this->Output();	
  
	exit();	
  
   
   
  //$this->Cell(95,4,$array_autoriza[$Salida['autoriza']],0,0,'C');
  //$this->Cell(95,4,$array_nomusuario[$Salida['usuario']],0,0,'C');
    /*
	include('fpdf153/fpdf.php');
	include("numlet.php");
	$res=mysql_db_query($base,"SELECT * FROM salida_gastos WHERE cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	$this=new FPDF('P','mm','LETTER');
	$this->AddPage();
	$this->SetFont('Arial','B',16);
	$this->Cell(190,10,'VERIFICENTROS',0,0,'C');
	$this->Ln();
	$this->Cell(95,10,'Vale de Gasto',0,0,'L');
	$this->Cell(95,10,'Folio: '.$row['cve'],0,0,'R');
	$this->Ln();
	$this->SetFont('Arial','B',10);
	//$this->Cell(95,5,'',0,0,'L');
	$this->Cell(95,5,'Fecha: '.fecha_letra($row['fecha']),0,0,'L');
	$this->Cell(95,5,'Bueno por: $ '.number_format($row['monto'],2),0,0,'R');
	$this->Ln();
	//$y=$this->GetY();
	//$this->MultiCell(95,5,'Motivo: '.$array_motivo[$row['motivo']],0,'L');
	//$this->SetXY(105,$y);
	//$this->Cell(95,5,'Fecha: '.fecha_letra($row['fecha']),0,0,'R');
	//$this->Ln();
	$this->Ln();
	$this->SetFont('Arial','',10);
	$this->MultiCell(190,5,"Recibi la cantidad de ".numlet($row['monto']),0,"R");
	$this->Ln();
	$this->MultiCell(190,5,"Por Concepto de: ".$row['obs'],0,"R");
	$this->Ln();
	$this->Cell(30,5,"Motivo",1,0,'C');
	$this->Cell(30,5,"Cantidad",1,0,'C');
	$this->Cell(30,5,"Importe",1,0,'C');
	$this->Cell(100,5,"Concepto",1,0,'C');
	$this->Ln();
	$res1=mysql_query("SELECT * FROM salida_gastos_detalle WHERE plaza = '".$row['plaza']."' AND gasto='".$row['cve']."'");
	while($row1=mysql_fetch_array($res1)){
		$this->Cell(30,5,$array_motivo[$row1['motivo']],1,0,'L');
		$this->Cell(30,5,$row1['cantidad'],1,0,'C');
		$this->Cell(30,5,$row1['importe'],1,0,'R');
		$this->Cell(100,5,$row1['concepto'],1,0,'L');
		$this->Ln();
	}
	$this->Ln();
	$this->Ln();
	$this->SetFont('Arial','U',12);
	$this->Cell(60,5,'');
	$this->MultiCell(70,5,$array_consignatario[$row['consignatario']],0,'C');
	$this->Ln();
	$this->SetFont('Arial','',12);
	$this->Cell(190,5,"Recibi",0,0,'C');
	$this->Ln();
	$this->Ln();
	$this->Ln();
	$this->Ln();
	/*$rsfirmas=mysql_db_query($base,"SELECT * FROM administradores WHERE depositos='1' AND plaza='".$row['plaza']."' AND fecha_ini<='".$row['fecha']."' AND (fecha_fin>='".$row['fecha']."' OR fecha_fin='0000-00-00')");
	$numfirmas=mysql_num_rows($rsfirmas);
	$ancho=190/$numfirmas;
	$array_puestoadmon=array();
	$i=0;
	$this->SetFont('Arial','U',9);
	while($Firmas=mysql_fetch_array($rsfirmas)){
	
		$this->Cell($ancho,5,$Firmas['nombre'],0,0,'C');
		$array_puestoadmon[$i]=$array_puesto[$Firmas['puesto']];
		$i++;
	}
	$this->Ln();
	$this->SetFont('Arial','',9);
	for($x=0;$x<$i;$x++){
		$this->Cell($ancho,5,$array_puestoadmon[$x],0,0,'C');
	}
	$this->Ln();
	$this->Ln();
	$this->Ln();

	$this->SetFont('Arial','',10);
	$this->Cell(95,5,'Impreso por: '.$array_usuario[$_POST['cveusuario']],0,0,'L');
	$this->Cell(95,5,'Creado por: '.$array_usuario[$row['usuario']],0,0,'R');*/
	
}

if($_POST['ajax']==1){
	$plazas_ids = "";
	foreach($array_plaza as $k=>$v){
		$plazas_ids.=",".$k;
	}
	$plazas_ids=substr($plazas_ids,1);
	$select = "SELECT * FROM salida_gastos WHERE fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	if($_POST['plaza']!="all") $select .= " AND plaza='".$_POST['plaza']."'";
	if($_POST['motivo']!="all") $select .= " AND motivo='".$_POST['motivo']."'";
	if($_POST['consignatario']!="all") $select .= " AND consignatario='".$_POST['consignatario']."'";
	if($_POST['usuario']!="all") $select .= " AND usuario='".$_POST['usuario']."'";
	$select.=" ORDER BY cve DESC";
	$res=mysql_query($select);
	if(mysql_num_rows($res)>0) 
	{
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		echo '<th>Folio</th><th>Fecha</th><th>Plaza</th><th>Consignatario</th><th>Monto</th><th>Concepto</th><th>Usuario</th>';
		echo '</tr>';
		$t=0;
		while($row=mysql_fetch_array($res)) {
			rowb();
			echo '<td align="center" width="40" nowrap>';
			if($row['estatus']=='C'){
				echo 'Cancelado';
				$row['monto']=0;
			}
			else{
				echo '<a href="#" onClick="atcr(\'salida_gastos.php\',\'_blank\',\'101\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
				if(nivelUsuario()>1 && $row['estatus']=='A')
					echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el vale\')) atcr(\'salida_gastos.php\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a>';
			}	
			echo '</td>';
			echo '<td align="center">'.htmlentities($row['cve']).'</td>';
			echo '<td align="center">'.htmlentities($row['fecha'].' '.$row['hora']).'</td>';
			echo '<td align="left">'.htmlentities(utf8_encode($array_plaza[$row['plaza']])).'</td>';
			//echo '<td align="left">'.htmlentities(utf8_encode($array_motivo[$row['motivo']])).'</td>';
			echo '<td align="left">'.htmlentities(utf8_encode($array_consignatario[$row['consignatario']])).'</td>';
			echo '<td align="right">'.number_format($row['monto'],2).'</td>';
			echo '<td align="left">'.htmlentities($row['obs']).'</td>';
			echo '<td align="center">'.htmlentities($array_usuario[$row['usuario']]).'</td>';
			echo '</tr>';
			$t+=$row['monto'];
		}
		$c=5;
		echo '	
			<tr>
			<td colspan="'.$c.'" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
			<td align="right" bgcolor="#E9F2F8">'.number_format($t,2).'</td>
			<td colspan="2" bgcolor="#E9F2F8">&nbsp;</td>
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

if($_POST['cmd']==3){
	mysql_query("UPDATE salida_gastos SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	$insert = " INSERT salida_gastos 
					SET 
					fecha='".fechaLocal()."',hora='".horaLocal()."',monto='".$_POST['monto']."',obs='".$_POST['concepto']."',
					plaza='".$_POST['plaza']."',motivo='".$_POST['motivo']."',consignatario='".$_POST['consignatario']."',
					usuario='".$_POST['cveusuario']."',estatus='A'";
	mysql_query($insert);
	$gastos_id = mysql_insert_id();
	foreach($_POST['motivod'] as $k=>$v)
	
  //foreach($array_motivo as $k=>$v)
  {
		
		if($v>0 && $_POST['imported'][$k]>0){
			mysql_query("INSERT salida_gastos_detalle SET plaza='".$_POST['plaza']."',gasto='".$gastos_id."',cantidad='".$_POST['cantidadd'][$k]."',
			motivo='".$v."',importe='".$_POST['imported'][$k]."',concepto='".$_POST['conceptod'][$k]."'") or die(mysql_error());
		}
	}
	$_POST['cmd']=0;
}


if ($_POST['cmd']==1) {
		
	$select=" SELECT * FROM salida_gastos WHERE cve='".$_POST['reg']."' ";
	$res=mysql_query($select);
	$row=mysql_fetch_array($res);			
	if($_POST['reg']==0){
		$row['fecha_corte']=fechaLocal();
	}
	//Menu
	echo '<table>';
	echo '
		<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();validar();"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'salida_gastos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Vale de Gastos</td></tr>';
	echo '</table>';
	
	echo '<table>';
	if($_POST['plazausuario']==0)
	{
		echo '<tr><th align="left">Plaza</th><td><select name="plaza" id="plaza">';
		foreach($array_plaza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
	}
	else{
		echo '<tr><th align="left">Plaza</th><td><b>'.$array_plaza[$_POST['plazausuario']].'</b><input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'"></td></tr>';
	}
	/*echo '<tr><th align="left">Motivo</th><td><select name="motivo" id="motivo"><option value="0">Seleccione</option>';
	foreach($array_motivo as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$row['motivo']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';*/
	echo '<tr><th align="left">Consignatario</th><td><select name="consignatario" id="consignatario"><option value="0">Seleccione</option>';
	foreach($array_consignatario as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$row['consignatario']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="readOnly" size="10" value="'.$row['monto'].'" readOnly></td></tr>';
	echo '<tr><th align="left">Concepto</th><td><textarea cols="30" rows="3" name="concepto" id="concepto" class="textField">'.$row['concepto'].'</textarea></td></tr>';
	echo '</table>';
	echo '<table id="detalles"><tr><th>Motivo</th><th>Cantidad</th><th>Importe</th><th>Concepto</th></tr>';
	echo '</table><br><input type="button" class="textField" value="Agregar" onClick="agregar()">';
	echo '<input type="hidden" name="numreg" value="0">';
	echo '<script>
			
			function agregar(){
				n=document.forma.numreg.value/1;
				$("#detalles").append(\'<tr>\
				<td align="center"><select name="motivod[\'+n+\']" id="motivod_\'+n+\'" onChange="activar(\'+n+\')">'.$optionsmotivos.'</select></td>\
				<td align="center"><input type="text" class="textField" name="cantidadd[\'+n+\']" id="cantidadd_\'+n+\'" value="" size="10" disabled></td>\
				<td align="center"><input type="text" class="textField" name="imported[\'+n+\']" id="imported_\'+n+\'" value="" onKeyUp="calcular()" size="10"disabled></td>\
				<td align="center"><textarea class="textField" name="conceptod[\'+n+\']" id="conceptod_\'+n+\'" rows="3" cols="30" disabled></textarea></td>\
				</tr>\');
				n++;
				document.forma.numreg.value=n;
			}
			
			function activar(n){
				if(document.getElementById("motivod_"+n).value=="0"){
					document.getElementById("cantidadd_"+n).value="";
					document.getElementById("imported_"+n).value="";
					document.getElementById("conceptod_"+n).value="";
					document.getElementById("cantidadd_"+n).disabled=true;
					document.getElementById("imported_"+n).disabled=true;
					document.getElementById("conceptod_"+n).disabled=true;
				}
				else{
					document.getElementById("cantidadd_"+n).disabled=false;
					document.getElementById("imported_"+n).disabled=false;
					document.getElementById("conceptod_"+n).disabled=false;
				}
				calcular();
			}
			
			function calcular(){
				var total=0;
				for(i=0;i<(document.forma.numreg.value/1);i++){
					total += document.getElementById("imported_"+i).value/1;
				}
				document.forma.monto.value=total.toFixed(2);
			}
	
			function validar(){
				if(document.forma.consignatario.value=="0"){
					$("#panel").hide();
					alert("Necesita seleccionar un consignatario");
				}
				else if((document.forma.monto.value/1)<=0){
					$("#panel").hide();
					alert("El monto debe de ser mayor a cero");
				}
				else{
					atcr("salida_gastos.php","",2,0);
				}
			}
			
			
		</script>';
	
}

if($_POST['cmd']==0){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'salida_gastos.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
		 </tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.substr(fechaLocal(),0,8).'01" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	if($_POST['plazausuario']==0){
		echo '<tr><td>Plaza</td><td><select name="plaza" id="plaza"><option value="all" selected>Todas</option>';
		foreach($array_plaza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
	}
	else{
		echo '<input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
	}
	echo '<tr style="display:none;"><td>Motivo</td><td><select name="motivo" id="motivo"><option value="all">Todos</option>';
	foreach($array_motivo as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Consignatario</td><td><select name="consignatario" id="consignatario"><option value="all">Todos</option>';
	foreach($array_consignatario as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="all">Todos</option>';
	if($_POST['plazausuario']==0)
		$res=mysql_query("SELECT b.cve,b.usuario FROM salida_gastos a INNER JOIN usuarios b ON a.usuario = b.cve WHERE 1 GROUP BY a.usuario ORDER BY b.usuario");
	else
		$res=mysql_query("SELECT b.cve,b.usuario FROM salida_gastos a INNER JOIN usuarios b ON a.usuario = b.cve WHERE a.plaza='".$_POST['plazausuario']."' GROUP BY a.usuario ORDER BY b.usuario");
	while($row=mysql_fetch_array($res)){
		echo '<option value="'.$row['cve'].'">'.$row['usuario'].'</option>';
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
			objeto.open("POST","salida_gastos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&plaza="+document.getElementById("plaza").value+"&motivo="+document.getElementById("motivo").value+"&consignatario="+document.getElementById("consignatario").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&plazausuario="+document.getElementById("plazausuario").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
	}
	buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
		
	
	
	
	</Script>
	';

	
}
	
bottom();




?>