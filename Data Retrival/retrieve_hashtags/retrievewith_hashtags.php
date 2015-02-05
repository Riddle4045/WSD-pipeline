<?php

ini_set('display_errors', 1);
require_once('TwitterAPIExchange.php');


/** Set access tokens here - see: https://dev.twitter.com/apps/ * */
$settings = array(
    'oauth_access_token' => "2451534170-ISETliFOuTR2BYoVSLCqZO8tRrOmSX7SfMcuECa",
    'oauth_access_token_secret' => "mRB9c9deqEdSIjblXf6wgm05N6fizKJohNFykYfjFuWYx",
    'consumer_key' => "isYSRFHsJGZeklzczLtNXbA8V",
    'consumer_secret' => "YAW2RhMjHiWJ3V3YJnm9yf5AZ2HdstszxTLJpAdaOq9j2Todon"
);


/**
 * Currently only first 200 tweets are retireveed using the "count" parameter. 
 * Use the  "since_id" and "max_id" parameters and make repeated requests to  to get  all the tweets. 
 * refer to the following link for the statergy :https:dev.twitter.com/docs/working-with-timelines use the code from index.php to download all the media w.r.t to a twe
 * 
 * 
 * first_getfield is call without the max_id or since ID , once we get a json object from the first call
 * the max_id and since_id are determined 
 */
-//$url = 'https://api.twitter.com/1.1/blocks/create.json';
$url = 'https://api.twitter.com/1.1/search/tweets.json';
//list of keywords for worldcup event
//$keywords  = array(1=>'?q=#2014Fifa',2=>'?q=#WorldCup',3=> '?q=#WorldCup2014',4=>'?q=#WorldCupSoccer',5=>'?q=#CocaCola',6=>'?q=#Fifa2014',7=>'?q=#Brasil',8=>'?q #2014',9 =>'?q=#FIFAWorldCup'.10=>'?q#Bresil' 11=>'?q#OpWorldCup',12=>'?q#OpWorldCup2014',13=>'?q#FIFAgoHome',14=>'?q#WorldCup2014',15=>'?q#NAOVAITERCOPA',16=>'?q#NaoVaiTerFIFA',17=>'?q#NoWorldCup',18=>'?q#WeNeedFoodNoFootball',19=>'?q#Brasil2014',20=>'?q#Brazil',21=>'?q#FIFA',22=>'?q#NaoWorldCup',23=>'?q#VemPraRua',24=>'?q#Weltmeisterschaft',25=>'?q#μουντιάλ',26=>'?q#mondiali',27=>'?q#mundial',28=>'?q#BecauseFutbol');
//$keywords  = array(1=>'?q=#2014Fifa',2=>'?q=#WorldCup',3=>'?q=#WorldCup2014',4=>'?q=#WorldCupSoccer',5=>'?q=#CocaCola',6=>'?q=#Fifa2014',7=>'?q=#Brasil',8=>'?q #2014',9 =>'?q=#FIFAWorldCup',
//10=>'?q=#Bresil', 11=>'?q=#OpWorldCup',12=>'?q=#OpWorldCup2014',13=>'?q=#FIFAgoHome',14=>'?q=#WorldCup2014',15=>'?q=#NAOVAITERCOPA',16=>'?q=#NaoVaiTerFIFA',17=>'?q=#NoWorldCup',18=>'?q=#WeNeedFoodNoFootball',
//19=>'?q=#Brasil2014',20=>'?q=#Brazil',21=>'?q=#FIFA',22=>'?q=#NaoWorldCup',23=>'?q=#VemPraRua',24=>'?q=#Weltmeisterschaft',25=>'?q=#μουντιάλ',26=>'?q=#mondiali',27=>'?q=#mundial',28=>'?q=#BecauseFutbol');

$num_images = 0;

//$keywords= array(1=>'?q=#2014Fifa',2=>'?q=#WorldCup',3=>'?q=#WorldCup2014',4=>'?q=#WorldCupSoccer',5=>'?q=#CocaCola',6=>'?q=#Fifa2014',7=>'?q=#Brasil',8=>'?q #2014',9 =>'?q=#FIFAWorldCup',
//10=>'?q=#Bresil', 11=>'?q=#OpWorldCup',12=>'?q=#OpWorldCup2014',13=>'?q=#FIFAgoHome',14=>'?q=#WorldCup2014',15=>'?q=#NAOVAITERCOPA',16=>'?q=#NaoVaiTerFIFA',17=>'?q=#NoWorldCup',18=>'?q=#WeNeedFoodNoFootball',19=>'?q=#Brasil2014',20=>'?q=#Brazil',21=>'?q=#FIFA',22=>'?q=#NaoWorldCup',23=>'?q=#VemPraRua',24=>'?q=#Weltmeisterschaft',25=>'?q=#μουντιάλ',26=>'?q=#mondiali',27=>'?q=#mundial',28=>'?q=#BecauseFutbol',29=>'?q=#Bresil',30=>'?q=#OpWorldCup',31=>'?q=#OpWorldCup2014',32=>'?q=#FIFAgoHome',33=>'?q=#WorldCup2014',34=>'?q=#NAOVAITERCOPA',35=>'?q=#NaoVaiTerFIFA',36=>'?q=#NoWorldCup',37=>'?q=#WeNeedFoodNoFootball',38=>'?q=#Brasil2014',39=>'?q=#Brazil',40=>'?q=#FIFA',41=>'?q=#NaoWorldCup',42=>'?q=#VemPraRua',43=>'?q=#Weltmeisterschaft',44=>'?q=#μουντιάλ',45=>'?q=#mondiali',46=>'?q=#mundial',47=>'?q=#BecauseFutbol',48=>'?q=#BoycottBrazil2014',49=>'?q=#OpBoycottCup',50=>'?q=#NÃOVAITERCOPA',51=>'?q=#NOFIFA');

//$keywords= array(1=>'?q=#Bresil', 2=>'?q=#OpWorldCup',3=>'?q=#OpWorldCup2014',4=>'?q=#FIFAgoHome',5=>'?q=#WorldCup2014',6=>'?q=#NAOVAITERCOPA',7=>'?q=#NaoVaiTerFIFA',8=>'?q=#NoWorldCup',9=>'?q=#WeNeedFoodNoFootball',10=>'?q=#NaoWorldCup',11=>'?q=#VemPraRua',12=>'?q=#Weltmeisterschaft',13=>'?q=#μουντιάλ',14=>'?q=#mondiali',15=>'?q=#mundial',16=>'?q=#OpWorldCup',17=>'?q=#FIFAgoHome',18=>'?q=#NAOVAITERCOPA',19=>'?q=#NaoVaiTerFIFA',20=>'?q=#BoycottBrazil2014',21=>'?q=#NaoVaiTerCopa',22=>'?q=#NotGoingtoBrazil');

$keywords = array(1=>'?q=#WorldCup');
//this rate limit counter keeps track of number of get request to make sure we dont exceed  rate limit.
$rate_limit_counter = 0;

/*
 * generates the "hashValue" :"text" combinantion 
 * and sends it out to stdout
 */

function processData($json_data) {

    foreach ($json_data->statuses as $status) {
	//var_dump($status);
        $hashstring = $status->created_at;
        $hashValue = hash("md5", $hashstring);
            writeTweet($status, $hashValue);
            if (array_key_exists("media", (array) $status->entities)) {
                downLoadImages($status, $hashValue);
            }
        
}}
function writeTweet($status, $hashValue) {
    echo "\n";
    echo '"';
    echo $hashValue;
    echo '" : "';
    echo $status->text;
    echo '"';
    echo "\n";
    sleep(9);
	$string = '"'.$hashValue.'" : "'.$status->text.'"'.PHP_EOL;
	$file = "protest_data_new.txt";
	file_put_contents($file,$string, FILE_APPEND);
}

function downLoadImages($status, $hashValue) {
    foreach ($status->entities->media as $images) {
        if ($images->media_url != "") {
            $mediaUrl = $images->media_url;
            $cmd = "wget --quiet -O\t" . $hashValue . ".png\t" . $mediaUrl;
            exec($cmd);
            echo "\n";
            $GLOBALS['num_images'] = $GLOBALS['num_images'] + 1;
        }
    }
}

function makeRequests($get_field) {
    if ( $GLOBALS['rate_limit_counter'] >= 180){
         echo "Going to sleep for 15 minutes :P something you ll never have !!!! ";
        sleep(900);
    } else {
    // $first_getfield = '?screen_name=JamesFrancoTV&max_id=473257670443409408&count=200&include_rts=1';
    $requestMethod = 'GET';
    $twitter = new TwitterAPIExchange($GLOBALS['settings']);
    $response = $twitter->setGetfield($get_field)->buildOauth($GLOBALS['url'], $requestMethod)->performRequest();
    $json_data = json_decode($response);
    $GLOBALS['rate_limit_counter']++;
    return $json_data;
    }
}

/*
 * Iterates through users timeline , fetches all the media
 * and downloads it to the current direcotory.
 * twitter max_id impleentation used to browse through all the tweets in timeline.
 */

function getAllData($keyword) {

    echo "***************************PROCESSING KEYWORD";echo $keyword;echo "*******************";
    $first_getfield = $keyword;
    $json_data = makeRequests($first_getfield);
    if (!empty($json_data)) {
	//var_dump($json_data);
        processData($json_data);
    }
}

foreach($keywords as $k => $v ){
getAllData($v);
}
//getAllData('?q=#test');
		

//world cup hashtags:Fifa #2014Fifa #WorldCup #WorldCup2014 #WorldCupSoccer #CocaCola #Fifa2014 #Brasil #2014

