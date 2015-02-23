<?php
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
	var_dump($ret);
	return $ret;
}

function analyze_page_image($page,$sense,$id,$word){
	$name = str_replace(" ","_",$sense);
	$begin = strpos($page,'<img');
	$end = strpos($page,'<div id="foot">');
	$offset = $begin - 1;
	$cnt = 0;
	while($offset<$end){
		$p1 = strpos($page,'src="',$offset)+5;
		if($p1===false || $p1>=$end)
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
		print("$word:$sense:$imglink\n");
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
		if($p1===false || $p1>=$end)
			break;
		$p2 = strpos($page,'</span>',$p1)+7;
		$descpr = strip_tags(substr($page,$p1,$p2-$p1));
		$descpr = preg_replace('/[^A-Za-z0-9]/', ' ', $descpr);
		$offset = $p2+1;
		print($descpr."\n");
		$cnt++;
		$buffer .= $descpr."\n";
	}
	@mkdir("download/$word/train/$name", 0777, true);
	file_put_contents("download/$word/train/$name.txt",$buffer,FILE_APPEND);
	return $cnt;
}


function download($word){
	$senses = get_senses($word);
	//download images.
	for($i=0;$i<sizeof($senses);$i++){
		$sense = str_replace(" ","+",$senses[$i]);
		$q = $word."+".$sense;
		$start = 0;
		while($start<20){
			$url = "http://www.google.com/images?oe=utf8&ie=utf8&source=uds&start=$start&hl=en&q=$q";
			$page = file_get_contents($url);
			$n = analyze_page_image($page,$sense,$start+1,$word);
			$start += $n;
		}
	}
	//download text.
	for($i=0;$i<sizeof($senses);$i++){
		$sense = str_replace(" ","+",$senses[$i]);
		$q = $word."+".$sense;
		$start = 0;
		while($start<100){
			$url = "https://www.google.com/search?oe=utf8&ie=utf8&source=uds&start=$start&hl=en&q=$q";
			$page = file_get_contents($url);
			$n = analyze_page_text($page,$sense,$start+1,$word);
			$start += $n;
		}
	}
}

if(sizeof($argv) !=2){
	print("Need to specify an ambiguous word.\n");
	return;
}

download($argv[1]);

?>
