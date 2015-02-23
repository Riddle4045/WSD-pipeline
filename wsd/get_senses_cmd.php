<?php

/*
This program discovers available senses for a given ambiguous word.
Author: Dihong Gong
Date:   Feb 7, 2015.
*/

function generate_html($senses){
	return "";
}

function get_senses($word){
	$url = "http://www.google.com/images?oe=utf8&ie=utf8&source=uds&start=0&hl=en&q=$word";
	$data = file_get_contents($url);
	//file_put_contents('data.html',$data);
	$begin = strpos($data,'class="_Bmc"');
	$end = strpos($data,'<div id="foot">');
	$offset = $begin;
	$ret = Array();
	while($offset<$end){
		$p1 = strpos($data,"<b>",$offset)+3;
		if($p1===false || $p1>=$end)
			break;
		$p2 = strpos($data,"</b>",$offset);
		$sense = substr($data,$p1,$p2-$p1);
		$offset = $p2+1;
		array_push($ret,$sense);
	}
	return $ret;
}

/*Entry point begins here*/

if(sizeof($argv) !=2){
	print("Need to specify an ambiguous word.\n");
	return;
}

$word = strtolower($argv[1]);

if(file_exists("download/$word/available_senses.txt"))   //senses have been discovered.
	return;

$senses = get_senses($argv[1]);

$out = "";

for($i=0;$i<sizeof($senses);$i++){
	if($i==0)
		$out .= $senses[$i];
	else
		$out .= "\n".$senses[$i];
}

@mkdir("download/$word", 0777, true);
file_put_contents("download/$word/available_senses.txt",$out);
file_put_contents("download/$word/available_senses.formatted",generate_html($senses));
?>
