<?php

/*
This program downloads training images & text for given [word] [sense] and [start].
Author: Dihong Gong
Date:   Feb 7, 2015.
*/

function analyze_page_image($page,$sense,$id,$word){
	$name = str_replace(" ","_",$sense);
	$begin = strpos($page,'<img');
	$end = strpos($page,'<div id="foot">');
	$offset = $begin - 1;
	$cnt = 0;
	while($offset<$end){
		$p1 = strpos($page,'src="',$offset)+5;
		if($p1===false || $p1>=$end || $cnt>=20)   //download at most 20 entries per call.
			break;
		$p2 = strpos($page,'"',$p1);
		$imglink = substr($page,$p1,$p2-$p1);
		$offset = $p2+1;
		$image = @file_get_contents($imglink);
		$cnt++;
		if($image===false)
			continue;
		@mkdir("download/$word/train/$name", 0777, true);
		file_put_contents("download/$word/train/$name/$id",$image);
		$id++;
	}
	return $cnt;
}

function analyze_page_text($page,$sense,$id,$word){
	$page = str_replace("\n", "", $page);
	$page = str_replace("\r", "", $page);
	$name = str_replace(" ","_",$sense);
	$begin = strpos($page,'<span class="st">');
	$end = strpos($page,'<div id="foot">');
	$offset = $begin - 1;
	$cnt = 0;
	$buffer = "";
	while($offset<$end){
		$p1 = strpos($page,'<span class="st">',$offset);
		if($p1===false || $p1>=$end  || $cnt>=10)  //download at most 10 entries per call.
			break;
		$p2 = strpos($page,'</span>',$p1)+7;
		$descpr = strip_tags(substr($page,$p1,$p2-$p1));
		
		
        $descpr = preg_replace("/[^a-zA-Z0-9]+/", " ", $descpr);
        $descpr =  preg_replace('/\s+/',' ',$descpr);
        $descpr = strtolower(trim($descpr));
        $descpr =  str_replace(' ', '_',$descpr);

		$descpr = str_replace("nbsp","",$descpr);
		$offset = $p2+1;
		$cnt++;
		$buffer .= $descpr."\n";
	}
	@mkdir("download/$word/train/$name", 0777, true);
	file_put_contents("download/$word/train/$name.txt",$buffer,FILE_APPEND);
	return $cnt;
}

/*Entry point begins here*/

if(sizeof($argv) !=4){
	print("Need to specify [word] [sense] and [start].\n");
	return;
}

$word = strtolower($argv[1]);
$sense = strtolower($argv[2]);
$start = $argv[3];

$q = $word."+".$sense;

$name = str_replace(" ","_",$sense);

if(file_exists("download/$word/train/$name"))   //senses have been downloaded.
	return;

//download images.
$url = "http://www.google.com/images?oe=utf8&ie=utf8&source=uds&start=$start&hl=en&q=$q";
$page = @file_get_contents($url);
if($page!==false)
	$nimg = analyze_page_image($page,$sense,$start+1,$word);  //$start = 0,20,40,...
else
	$nimg = 0;
//download text.
$url = "https://www.google.com/search?oe=utf8&ie=utf8&source=uds&start=$start&hl=en&q=$q";
$page = file_get_contents($url);
if($page!==false)
	$n = analyze_page_text($page,$sense,$start+1,$word);
else
	$n = 0;
$start = $start+$n;
$url = "https://www.google.com/search?oe=utf8&ie=utf8&source=uds&start=$start&hl=en&q=$q";
$page = file_get_contents($url);
if($page!==false)
	analyze_page_text($page,$sense,$start+1,$word);  //$start = 0,20,40,...
echo $nimg;
return;
?>





