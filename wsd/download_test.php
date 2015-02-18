<?php

/*
This program downloads testing images & text for given [word] [sense] and [start].
Author: Dihong Gong
Date:   Feb 7, 2015.
*/


function get_objects($q,$start,$word,$sense){
	$sense = strtolower($sense);
	$keys = explode ("_", $sense);
	$sense = str_replace(" ","_",$sense);
	$url = "https://ajax.googleapis.com/ajax/services/search/images?v=1.0&q=$q&start=$start";
	$try = 0;
	while($try<5){
		$body = file_get_contents($url);
		$json = json_decode($body);
		if($json!==null && $json->responseData!==null)
			break;
		else{
			$try += 1;
			sleep(2*$try);
		}
	}
	if($json===null || $json->responseData===null) die("Fail to get data: $url");
	@mkdir("download/$word/test", 0777, true);
	for($i=0;$i<sizeof($json->responseData->results);$i++){
		$image = @file_get_contents($json->responseData->results[$i]->tbUrl);
		if($image===false) continue;
		$title = $json->responseData->results[$i]->titleNoFormatting;
		
        $title = preg_replace("/[^a-zA-Z0-9]+/", " ", $title);
        
		for($j = 0;$j<sizeof($keys);$j++)  //remove all keys contained within the title.
			$title = str_replace(" ".$keys[$j]." "," ",$title);
        
        $title =  preg_replace('/\s+/',' ',$title);
        $title = strtolower(trim($title));
        $title =  str_replace(' ', '_',$title);
        
		@file_put_contents("download/$word/test/$title",$image);
	}
	return sizeof($json->responseData->results);
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

echo $q."\n";

$name = str_replace(" ","_",$sense);

if(file_exists("download/$word/test"))   //senses have been downloaded.
	return;

//download text & images.
print(get_objects($q,$start,$word,$sense));  //$start = 0,4,8,...
return;
?>





