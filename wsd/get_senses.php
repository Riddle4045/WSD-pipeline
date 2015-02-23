<?php

/*
This program discovers available senses for a given ambiguous word.
Author: Dihong Gong
Date:   Feb 7, 2015.
*/

function generate_html($senses){
	$out = "<br />&nbsp;&nbsp;Please select at least two interesting senses: <br />\n";
	for($i=0;$i<sizeof($senses);$i++){
		$s = $senses[$i];
		$id = str_replace("+","_",$s);
		if($i==0)
			$out .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label for=\"$id\">$s</label><input type=\"checkbox\" name=words[] value=\"$s\" id=\"$id\"> &nbsp;&nbsp;&nbsp;";
		else
			$out .= "\n<label for=\"$id\">$s</label><input type=\"checkbox\" name=words[] value=\"$s\" id=\"$id\"> &nbsp;&nbsp;&nbsp;";
	}
	return $out."\n<input type=\"submit\" value=\"confirm\" id = \"confirmation\" onclick=\"confirm_senses();\">";
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function append_embedded($root,$str){
	return file_put_contents("$root/embedded.html",$str."\n",FILE_APPEND);
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

$time_start = microtime_float();

$word = $_POST['word'];
$root = $_POST['root'];


$word = strtolower($word);

if(file_exists("download/$word/available_senses.txt")){   //senses have been discovered.
	$senses = explode("\n",trim(file_get_contents("download/$word/available_senses.txt")));
}
else{
	$senses = get_senses($word);
	//replace ' ' with '+'
	for($i=0;$i<sizeof($senses);$i++)
		$senses[$i] = str_replace(" ","+",$senses[$i]);
}

	
if(!file_exists("download/$word/available_senses.txt")){
	$out = "";
	for($i=0;$i<sizeof($senses);$i++){
		if($i==0)
			$out .= $senses[$i];
		else
			$out .= "\n".$senses[$i];
	}
	@mkdir("download/$word", 0777, true);
	file_put_contents("download/$word/available_senses.txt",$out);
}
//write output into embedded.html.
$time_end = microtime_float();
$diff = $time_end - $time_start;
$diff = round($diff*100)/100;
append_embedded($root, "done. Elapsed time is $diff seconds.");
append_embedded($root, generate_html($senses));
for($i=0;$i<sizeof($senses);$i++)
	$senses[$i] = str_replace("+","_",$senses[$i]);
print json_encode($senses);
return;
?>
