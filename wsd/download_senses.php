<?php

function append_embedded($root,$str){
	file_put_contents("$root/embedded.html",$str."\n",FILE_APPEND);
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function maxind($arr){
	$max = $arr[0];
	$ind = 0;
	for($i=1;$i<sizeof($arr);$i++){
		if($arr[$i]>$max){
			$max = $arr[$i];
			$ind = $i;
		}
	}
	return $ind;
}

/*Entry point begins here*/
$time_start = microtime_float();
$word = $_POST['word'];
$root = $_POST['root'];
$senses = $_POST['senses'];
$all_senses = $_POST['all_senses'];

session_start(); //Never forget this line when using $_SESSION
if($_SESSION['upload'] === true){
	$test_sample_path = "$root/uploaded";
}else
	$test_sample_path = "download/$word/test";

$_SESSION['upload'] = false;

for($i=0;$i<sizeof($senses);$i++)
	$senses[$i] = str_replace("_","+",$senses[$i]);
	
for($i=0;$i<sizeof($all_senses);$i++)
	$all_senses[$i] = str_replace("_","+",$all_senses[$i]);

if(count($senses)<2){
	$n = count($senses);
	append_embedded($root,"<br />&nbsp;&nbsp;<span style=\"color:rgb(255,0,0)\">Error: At least two senses are required. You have selected $n sense.</span>");
	$xml=simplexml_load_file("$root/session.xml") or die("Error: Cannot open xml object.");
	$n = count($xml->children()); //number of sessions.
	$xml->session[$n-1]->status = "inputerr";
	$xml->session[$n-1]->addChild('endtime',date("Y-m-d H:i:s"));
	file_put_contents("$root/session.xml",$xml->asXML()) or die("Error: Cannot open xml object.");
	return;
}
append_embedded($root,"<head>
<style>
p.small {
    line-height: 0.8;
}
</style>
</head>");
$sense_list = "";
for($i=0;$i<sizeof($all_senses);$i++)
	$sense_list .= " ".$all_senses[$i];
append_embedded($root,"<br />&nbsp;&nbsp;Downloading text/image data, this might take a few minutes ... ");
$cmd = "python download.py $word $sense_list";
shell_exec($cmd);
$time_end = microtime_float();
$diff = $time_end - $time_start;
$diff = round($diff*100)/100;
append_embedded($root, "done. Elapsed time is $diff seconds.");
append_embedded($root, "<br />&nbsp;&nbsp;Example training images: <br />");
$table1 = "<table style=\"margin-left: 15px\">\n";
for($i=0;$i<sizeof($senses);$i++){
	$s = $senses[$i];
	$dir_train = "download/$word/train/$s";
	$files = scandir($dir_train);
	$cnt = 0;
	$table1 .= "<tr>\n<td><b>$s</b></td>\n";
	for($j = 0; $j < sizeof($files) && $cnt < 9;$j++){
		if(strcmp($files[$j],".")==0 || strcmp($files[$j],"..")==0 || strpos($files[$j],".sift")!==false || strpos($files[$j],".feat")!==false)
			continue;
		$name = $dir_train . "/" . $files[$j];
		$table1 .= "<td align=\"center\"><img src=\"$name\" height=\"75\" width=\"92\"></td>\n";
		$cnt += 1;
	}
	$table1 .= "\n</tr>";
}
$table1 .= "\n</table>";
append_embedded($root, $table1);
append_embedded($root, "&nbsp;&nbsp;Example training text: <br />");
$table2 = "<table style=\"margin-left: 5px\" cellpadding=\"10\">\n";
$pad = "                                                                                ";
$pad = ". . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .";
for($i=0;$i<sizeof($senses);$i++){
	$s = $senses[$i];
	$dir_train = "download/$word/train/$s";
	$cnt = 0;
	$lines = explode("\n",file_get_contents($dir_train . ".txt"));
	$table2 .= "<tr>\n<td><b>$s</b></td>\n";
	$txt = "";
	for($j = 0; $j < sizeof($lines) && $cnt < 3;$j++){
		$pos = strpos($lines[$j],"_".$word."_");
		if($pos===false)  //not found.
			continue;
		$L = $pad.$lines[$j].$pad;
		$pos = strpos($L,$word);
		$txt .= substr($L,$pos-40,2*40+strlen($word))."<br />";
		$cnt += 1;
	}
	$txt = str_replace("_".$word."_"," <u>".$word."</u> ",$txt);
	$txt = str_replace("_","&nbsp;",$txt);
	$txt = str_replace("<br&nbsp;/>","<br />",$txt);
	$table2 .= "\n<td>$txt</td>\n</tr>\n";
}
$table2 .= "\n</table>";
append_embedded($root, $table2);

/*YAROSKY*/
append_embedded($root,"&nbsp;&nbsp;Training text classifier ... ");
$time_start = microtime_float();
$seeds = str_replace("+"," ",$senses[0]);
$train_text = file_get_contents("download/$word/train/".$senses[0].".txt");
for($i=1;$i<sizeof($senses);$i++){
	$train_text .= "\n".file_get_contents("download/$word/train/".$senses[$i].".txt");
	$seeds .= "\n".str_replace("+"," ",$senses[$i]);
}
file_put_contents("$root/$word.seed",$seeds);
file_put_contents("$root/$word.train",$train_text);
shell_exec("apps/yarosky/uwsd -train $root/$word.train $root/$word.seed $root/$word.classifier $word");
$time_end = microtime_float();
$diff = $time_end - $time_start;
$diff = round($diff*100)/100;
append_embedded($root, "done. Elapsed time is $diff seconds.<br />");
append_embedded($root,"&nbsp;&nbsp;Example rules [<u>prediction</u> <u>confidence</u> <u>position</u> <u>word</u>]:<br />");
$data = file_get_contents("$root/$word.classifier");
$arr = explode("\n",$data);
$arr = array_slice($arr,0,10);
$arr = implode ("<br />&nbsp;&nbsp;&nbsp;&nbsp;", $arr);
append_embedded($root, "&nbsp;&nbsp;&nbsp;&nbsp;$arr<br />");

/*SIFT*/
append_embedded($root,"&nbsp;&nbsp;SIFT features extraction ... ");
$time_start = microtime_float();
for($i=0;$i<sizeof($senses);$i++){
	$s = $senses[$i];
	shell_exec("apps/sift/bin/proc download/$word $s $test_sample_path");
}
$time_end = microtime_float();
$diff = $time_end - $time_start;
$diff = round($diff*100)/100;
append_embedded($root, "done. Elapsed time is $diff seconds.<br />");


/*K-MEANS*/
append_embedded($root,"&nbsp;&nbsp;K-means clustering ... ");
$time_start = microtime_float();
shell_exec("apps/kmeans/kmeans.sh $root download/$word $sense_list");
$enc = shell_exec("cd $root/kmeans && mpirun -np 36 --hostfile user_data/hostfile mpikmeans out 2");
$enc = str_replace(" ","&nbsp;",$enc);
$enc = str_replace("\n","<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$enc);
$enc = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$enc);
$time_end = microtime_float();
$diff = $time_end - $time_start;
$diff = round($diff*100)/100;
append_embedded($root, "done. Elapsed time is $diff seconds.");
append_embedded($root,"<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$enc);



/*ENCODE*/
mkdir("$root/test_image_feat");
append_embedded($root,"Encoding SIFT features using KD tree ... ");
$time_start = microtime_float();
for($i=0;$i<sizeof($senses);$i++){
	$s = $senses[$i];
	shell_exec("apps/encode/bin/proc $root download/$word $s $i $test_sample_path");
}
$time_end = microtime_float();
$diff = $time_end - $time_start;
$diff = round($diff*100)/100;
append_embedded($root, "done. Elapsed time is $diff seconds.<br />");


/*SVM*/



/*TESTING*/
append_embedded($root,"&nbsp;&nbsp;WSD Testing (text-only vs. image-only vs. text-image):<br />");
$test_files = scandir($test_sample_path); //path of the testing images.
$dir_test = "$root/test_image_feat";   //path of the BOV features of testing images.
$ret = "<div align=\"center\"><table cellspacing=\"15\" border=\"1\">\n";
$num_obj_per_row = 2;
$row = 0;
for($j = 0; $j < sizeof($test_files);$j++){  //for each testing example.
	if(strcmp($test_files[$j],".")==0 || strcmp($test_files[$j],"..")==0 || strpos($test_files[$j],".sift")!==false || strpos($test_files[$j],".feat")!==false)
		continue;
	$path_of_bov_feat = $dir_test . "/" . $test_files[$j] . ".feat";
	$title = $test_files[$j];
	$out_text = shell_exec("apps/testing/text -test $root/$word.classifier '$title' $word");  //text-only output.
	$out_image = shell_exec("apps/testing/image $root '$path_of_bov_feat'");  //image-only output.
	$predictions_score_text = array_map('floatval', explode(' ', ($out_text)));
	$predictions_score_image = array_map('floatval', explode(' ', ($out_image)));
	for($k=0;$k<sizeof($predictions_score_text);$k++){
		$predictions_score_text[$k] = round($predictions_score_text[$k]*100)/100;
		$predictions_score_image[$k] = round($predictions_score_image[$k]*100)/100;
		$predictions_score_combined[$k] = ($predictions_score_text[$k] + $predictions_score_image[$k])/2;
	}
	if($row==$num_obj_per_row){
		$ret .= "\n</tr>\n";
		$row = 0;
	}
	if($row==0){
		$ret .= "<tr>\n";
	}
	
	//----------------
	$formatted_text = "<tr><td>Text:</td>";
	
	$arr = array();
	for($k=0;$k<sizeof($predictions_score_text);$k++)
		$arr += array($senses[$k]=>round(100*$predictions_score_text[$k])/100);

	arsort($arr);
	
	$cnt = 1;
	
	foreach ($arr as $key => $value) {
		$formatted_text .= "<td>".substr($key,0,4) ."</td><td>". "($value)"."</td>";
		$cnt += 1;
		if($cnt>3) break;
	}
	$formatted_text .= "</tr>";
	//----------------
	$formatted_image = "<tr><td>Image:</td>";
	
	$arr = array();
	for($k=0;$k<sizeof($predictions_score_image);$k++)
		$arr += array($senses[$k]=>round(100*$predictions_score_image[$k])/100);

	arsort($arr);
	
	$cnt = 1;
	
	foreach ($arr as $key => $value) {
		$formatted_image .= "<td>".substr($key,0,4) ."</td><td>". "($value)"."</td>";
		$cnt += 1;
		if($cnt>3) break;
	}
	$formatted_image .= "</tr>";
	
	//----------------
	$formatted_combined = "<tr><td>Comb:</td>";
	
	$arr = array();
	for($k=0;$k<sizeof($predictions_score_combined);$k++)
		$arr += array($senses[$k]=>round(100*$predictions_score_combined[$k])/100);

	arsort($arr);
	
	$cnt = 1;
	
	foreach ($arr as $key => $value) {
		$formatted_combined .= "<td>".substr($key,0,4) ."</td><td>". "($value)"."</td>";
		$cnt += 1;
		if($cnt>3) break;
	}
	$formatted_combined .= "</tr>";

	
	//$results = "<p class=\"small\">".$formatted_text."</p><p class=\"small\">".$formatted_image."</p><p class=\"small\">".$formatted_combined."</p>";
	$results = "<table>".$formatted_text.$formatted_image.$formatted_combined."</table>";
	
	$img_full_path = $test_sample_path."/".preg_replace('!\s+!', ' ',$title);
	
	$subtable = "<table></div>\n";
	
	$subtable .= "<tr><td title=\"$title\"><img src=\"$img_full_path\" width=120 height=120></td><td style=\"width: 350px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;\" valign=\"middle\">$results</td></tr>\n";
	
	$title = implode("<br />",str_split($title,55));
	$subtable .= "<tr><td title=\"$title\" colspan=\"2\"  align=\"left\">$title</td></tr>\n";
	//$subtable .= "<tr><td title=\"$title\" colspan=\"2\"  align=\"left\" style=\"max-width: 90px;overflow: scroll;text-overflow: scroll;white-space: nowrap;\">$title</td></tr>\n";
	//$subtable .= "<tr><td title=\"$title\" colspan=\"2\"  align=\"left\" >$title</td></tr>\n";
	
	
	$subtable .= "</table>\n";
	
	
	

	$ret .= "<td valign=\"top\">$subtable</td>\n";
	
	
	/*ob_start();
	var_dump($predictions_score_text);
	$text = ob_get_clean();
	
	ob_start();
	var_dump($predictions_score_image);
	$image = ob_get_clean();
	
	ob_start();
	var_dump($predictions_score_combined);
	$combined = ob_get_clean();
	
	$ret .= "[$cnt]: "."*$out_text*"."<br />"."*$out_image*"."<br />"."$text"."<br />".$image."<br />".$combined."<br /><br />";*/
	$row += 1;
}

$ret .= "\n</table>";

append_embedded($root,$ret);

/*
for($i=0;$i<sizeof($senses);$i++){  //for each sense.
	$ret = "";
	$dir_test = "$root/test_image_feat";
	$s = $senses[$i];
	append_embedded($root,"&nbsp;&nbsp; Sense = <b>$s</b>: <br />");
	$files = scandir("download/$word/test/$s");
	$line = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Text-Only Prediction: ";
	for($j = 0; $j < sizeof($files);$j++){  //for each testing example.
		if(strcmp($files[$j],".")==0 || strcmp($files[$j],"..")==0 || strpos($files[$j],".sift")!==false || strpos($files[$j],".feat")!==false)
			continue;
		$name = $dir_test . "/" . $files[$j] . ".feat";
		$title = $files[$j];
		$out = shell_exec("apps/testing/text -test $root/$word.classifier '$title' $word")."<br />";  //text-only output.
		$predictions_text = array_map('floatval', explode(' ', ($out)));
		$line .= "&nbsp;&nbsp;".$senses[maxind($predictions_text)].substr(0,10);
	}
	$ret .= $line."<br />";
	
	$line = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Image-Only Prediction: ";
	for($j = 0; $j < sizeof($files);$j++){  //for each testing example.
		if(strcmp($files[$j],".")==0 || strcmp($files[$j],"..")==0 || strpos($files[$j],".sift")!==false || strpos($files[$j],".feat")!==false)
			continue;
		$testname = $files[$j] . ".feat";
		$out = shell_exec("apps/testing/image $root '$testname'")."<br />";  //image-only output.
		$predictions_image = array_map('floatval', explode(' ', ($out)));
		$line .= "&nbsp;&nbsp;".$senses[maxind($predictions_image)].substr(0,10);
	}
	$ret .= $line."<br />";
	
	
	$line = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Text-Image Prediction: ";
	for($j = 0; $j < sizeof($files);$j++){  //for each testing example.
		if(strcmp($files[$j],".")==0 || strcmp($files[$j],"..")==0 || strpos($files[$j],".sift")!==false || strpos($files[$j],".feat")!==false)
			continue;
		$name = $dir_test . "/" . $files[$j] . ".feat";
		$title = $files[$j];
		$out = shell_exec("apps/testing/text -test $root/$word.classifier '$title' $word")."<br />";  //text-only output.
		$predictions_text = array_map('floatval', explode(' ', ($out)));
		$testname = $files[$j] . ".feat";
		$out = shell_exec("apps/testing/image $root '$testname'")."<br />";  //image-only output.
		$predictions_image = array_map('floatval', explode(' ', ($out)));
		for($k = 0;$k<sizeof($predictions_image);$k++){
			$predictions_image[$k] += $predictions_text[$k];
		}
		$line .= "&nbsp;&nbsp;".$senses[maxind($predictions_image)].substr(0,10);
	}
	$ret .= $line."<br />";
	append_embedded($root,$ret);
}*/


/*DONE*/
$xml=simplexml_load_file("$root/session.xml") or die("Error: Cannot open xml object.");
$n = count($xml->children()); //number of sessions.
$time_start = floatval(file_get_contents("$root/ping.txt"));
$time_end = microtime_float();
$diff = $time_end - $time_start;
if($diff<5)
	$xml->session[$n-1]->status = "completed";
else
	$xml->session[$n-1]->status = "aborted";
$xml->session[$n-1]->addChild('endtime',date("Y-m-d H:i:s"));
file_put_contents("$root/session.xml",$xml->asXML()) or die("Error: Cannot open xml object.");
append_embedded($root,"&nbsp;&nbsp;<span style=\"color:#009900\">Demo completed successfully.</span>");
?>












