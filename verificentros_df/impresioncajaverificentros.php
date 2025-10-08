<?php

	$textosimp=chr(27).'!'.chr(30)." ".$_GET['plaza']."|".$_GET['nomplaza'];
	$textosimp.='| RFC: '.$_GET['rfc'];
	$textosimp.='||';
	$textosimp.=chr(27).'!'.chr(8)." FOLIO: ".$_GET['folio'];
	$textosimp.='|';
	$textosimp.=chr(27).'!'.chr(8)." PAGO";
	$textosimp.='|';
	$textosimp.=chr(27).'!'.chr(8)." FECHA: ".$_GET['fecha'].'|';
	$textosimp.='|';
	$textosimp.=chr(27).'!'.chr(8)." FORMA PAGO: ".$_GET['formapago'];
	if($_GET['cveformapago']>1){
		$textosimp.=chr(27).'!'.chr(8)." REFERENCIA: ".$_GET['referencia'];
		$textosimp.='|';
	}
	$textosimp.='|';
	$textosimp.=chr(27).'!'.chr(8)." TIPO PAGO: ".$_GET['tipopago'];
	$textosimp.='|';
	$textosimp.=chr(27).'!'.chr(8)." DEPOSITANTE: ".$_GET['depositante'];
	$textosimp.='|';
	$textosimp.=chr(27).'!'.chr(8)." MONTO: ".number_format($_GET['monto'],2);
	$textosimp.='|';
	$textosimp.=chr(27).'!'.chr(8)." ".$_GET['montoletra'];
	$textosimp.='|';
	if($_GET['logo']!="") $logo=$_GET['plaza'];
	else $logo='BRUAS';
	$barcode = '1'.sprintf("%011s",(intval($_GET['folio'])));
	$texto=chr(27)."@";
	$textoimp=explode("|",$textosimp);
	for($i=0;$i<count($textoimp);$i++){
		$texto.=$textoimp[$i].chr(10).chr(13);
	}
	if($barcode!="")$texto.=chr(29)."h".chr(80).chr(29)."H".chr(2).chr(29)."k".chr(2).$barcode.chr(0);
	$texto.=chr(10).chr(13).chr(29).chr(86).chr(66).chr(0);
	if($file=fopen("nota.txt","w+")){
		fwrite($file,$texto);
		fclose($file);
	}
	if(substr(PHP_OS,0,3) != "WIN"){
		system("lp ".$logo.".TMB");
		system("lp nota.txt");
	}
	else{
		//system("copy ".$logo.".TMB lpt2");
		//system("copy nota.txt lpt2: >null:");
		if(file_exists($logo.".TMB")){
			exec('copy '.$logo.'.TMB "\\\\caja\\EPSON TM-T20II Receipt"');
		}
		exec('copy nota.txt "\\\\caja\\EPSON TM-T20II Receipt"');
	}
	$texto=chr(27)."@"."        COPIA".chr(10).chr(13);
	for($i=0;$i<count($textoimp);$i++){
		$texto.=$textoimp[$i].chr(10).chr(13);
	}
	if($barcode!="")$texto.=chr(29)."h".chr(80).chr(29)."H".chr(2).chr(29)."k".chr(2).$barcode.chr(0);
	$texto.=chr(10).chr(13).chr(29).chr(86).chr(66).chr(0);
	if($file=fopen("nota1.txt","w+")){
		fwrite($file,$texto);
		fclose($file);
	}
	if(substr(PHP_OS,0,3) != "WIN"){
		system("lp ".$logo.".TMB");
		system("lp nota1.txt");
	}
	else{
		//system("copy ".$logo.".TMB lpt2");
		//system("copy nota1.txt lpt2: >null:");
		if(file_exists($logo.".TMB")){
			exec('copy '.$logo.'.TMB "\\\\caja\\EPSON TM-T20II Receipt"');
		}
		exec('copy nota1.txt "\\\\caja\\EPSON TM-T20II Receipt"');
	}
	if($_GET['vales']!=''){
		$vales = explode(',', $_GET['vales']);
		$cont=10;
		foreach($vales as $vale){
			$textosimp=chr(27).'!'.chr(30)." ".$_GET['plaza']."|".$_GET['nomplaza'];
			$textosimp.='| RFC: '.$_GET['rfc'];
			$textosimp.='||';
			$textosimp.=chr(27).'!'.chr(8)." FOLIO: ".$vale;
			$textosimp.='|';
			$textosimp.=chr(27).'!'.chr(8)." VALE PAGO ANTICIPADO";
			$textosimp.='|';
			$textosimp.=chr(27).'!'.chr(8)." FECHA: ".$_GET['fecha'].'|';
			$textosimp.='|';
			$textosimp.=chr(27).'!'.chr(8)." DEPOSITANTE: ".$_GET['depositante'];
			$textosimp.='|';
			$texto=chr(27)."@";
			$textoimp=explode("|",$textosimp);
			for($i=0;$i<count($textoimp);$i++){
				$texto.=$textoimp[$i].chr(10).chr(13);
			}
			$barcode = '6'.sprintf("%011s",(intval($vale)));
			if($barcode!="")$texto.=chr(29)."h".chr(80).chr(29)."H".chr(2).chr(29)."k".chr(2).$barcode.chr(0);
			$texto.=chr(10).chr(13).chr(29).chr(86).chr(66).chr(0);
			$archivo = 'nota'.$cont;
			if($file=fopen($archivo.".txt","w+")){
				fwrite($file,$texto);
				fclose($file);
			}
			if(substr(PHP_OS,0,3) != "WIN"){
				system("lp ".$logo.".TMB");
				system("lp ".$archivo.".txt");
			}
			else{
				//system("copy ".$logo.".TMB lpt2");
				//system("copy ".$archivo.".txt lpt2: >null:");
				if(file_exists($logo.".TMB")){
					exec('copy '.$logo.'.TMB "\\\\caja\\EPSON TM-T20II Receipt"');
				}
				exec('copy '.$archivo.'.txt "\\\\caja\\EPSON TM-T20II Receipt"');
			}
			$cont++;
		}
	}
?>