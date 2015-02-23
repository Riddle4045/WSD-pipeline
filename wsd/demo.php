<?php
	function append_embedded($root,$str){
		file_put_contents("$root/embedded.html",$str."\n",FILE_APPEND);
	}
	
	function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	
	/*Entry point.*/
	

	$word = $_POST['data'];
	
	$num_file_uploaded = $_POST['upload'];
	
	session_start(); //Never forget this line when using $_SESSION
	if($num_file_uploaded>0)
		$_SESSION['upload'] = true;
	else
		$_SESSION['upload'] = false;
	
	if($word==NULL)
		die('You must enter an ambiguous word to be disambiguated.');
	//check input.
	$word = strtolower($word);
	$word2 = preg_replace('/[^a-z]/', '', $word); //remove special characters.
	if(strcmp($word,$word2)!=0){
		die('You must enter a single valid word.');
	}
	
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
			$ip = $_SERVER['REMOTE_ADDR'];
	}
	$root_dir = "users/$ip";
	if(!file_exists("$root_dir")){
		if(!mkdir($root_dir, 0777, true)){
			die("Error: Cannot create user folder. Please try again later.");
		}
	}
	if(file_exists("$root_dir/session.xml")){  //session.xml exists.
		$xml=simplexml_load_file("$root_dir/session.xml") or die("Error: Cannot create xml object");
		$n = count($xml->children()); //number of sessions.
		$ts1 = $xml->session[$n-1]->begintime;
		$pos1 = strpos($ts1," ");
		$date1 = substr($ts1,0,$pos1);
		$time1 = substr($ts1,$pos1+1);
		$ts2 = date("Y-m-d H:i:s");
		$pos2 = strpos($ts2," ");
		$date2 = substr($ts2,0,$pos2);
		$time2 = substr($ts2,$pos2+1);
		$diff = strtotime($time2)-strtotime($time1);
		if($n>0 && $xml->session[$n-1]->status=="active"){  //user has an active session.
			if(strcmp($date1,$date2) !=0 || $diff<10)
				die("You have an active session. Please wait for that session to complete.");
			else{
				$xml->session[$n-1]->status="aborted";
				$xml->session[$n-1]->addChild('endtime',"N/A");
				file_put_contents("$root_dir/session.xml",$xml->asXML()) or die("Error: Cannot create session file. Please try again later.");
			}
		}
		unlink("$root_dir/embedded.html");
	}else{ //create new session.xml
		file_put_contents("$root_dir/session.xml","<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<records>\n</records>") or die("Error: Cannot create session file. Please try again later.");
		$xml=simplexml_load_file("$root_dir/session.xml") or die("Error: Cannot initialize a new session. Please try again later.");
		$xml->addAttribute('userip', $ip);
		file_put_contents("$root_dir/session.xml",$xml->asXML()) or die("Error: Cannot create session file. Please try again later.");
	}
	//create a new session for the user.
	$xml=simplexml_load_file("$root_dir/session.xml") or die("Error: Cannot initialize a new session. Please try again later.");
	$session = $xml->addChild('session');
	$session->addChild('status','active');
	$session->addChild('begintime',date("Y-m-d H:i:s"));
	$session->addChild('word',$word);
	file_put_contents("$root_dir/session.xml",$xml->asXML()) or die("Error: Cannot create session file. Please try again later.");
	append_embedded($root_dir,"Session initialization done, now discovering senses for word: <b>$word</b> ...");
	unlink("$root_dir/labeled_train_feat.int32");
	shell_exec("rm -r $root_dir/test_image_feat");
	shell_exec("rm -r $root_dir/kmeans");
	shell_exec("rm -r $root_dir/*.seed");
	shell_exec("rm -r $root_dir/*.classifier");
	shell_exec("rm -r $root_dir/*.train");
	if($_SESSION['upload']!==true)
		shell_exec("rm -r $root_dir/uploaded $root_dir/uploaded_temp");
	
	die("-".$root_dir);
?>

