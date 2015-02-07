
<?php

	
		$n =  new MongoClient(); 	
		$dbname = "wsd"; 	
		$db = $n->$dbname; //get the collections.. 
		$list = $db->listCollections();
	 //	$collection = $db->raretweets; 
	//	$collection2 = $db->tweets;
		


		//make the regex object that contiains the qyery 
		//in this case the request is the text containg keyword.
        $keywords = array("apple","band","bank","bass","crane","mouse","chip","tank","plant");
		foreach($keywords as $keyword){
		writeTexttoFile($list,$keyword);
		}		




  // the function takes in a list of collections a keyword 
 // it searches for the query object agains the collection  gets the text back and writes it to a file
		function writeTexttoFile($list,$keyword){		
		$regex = "/".$keyword."/i";
        $regexObj = new MongoRegex($regex);
        $where = array("text" => $regexObj);

     
	//browse through all the collections that we want to search
        foreach ($list as $collection) {
                if ( $collection == 'wsd.raretweets' || $collection == 'wsd.tweets'){
                $cursor =$collection->find($where);
				$cursor->timeout(-1);
                while ( $cursor->hasNext() ) {
                        $document = $cursor->getNext();
                        $text = $document['text'];
						$text = $text."\n";
						$numWords = getWordLength($text);
						if ( $numWords < 6 ) {
						$path = $keyword.".txt";
						$user = $document['user'];
						$lang = $document['lang'];
	//					print_r($lang);	
					//get the text and append it to a file $keyword.txt , where keywords are different words we are looking for.
						if ( $lang == "en") {
								$id = $document['id'];
								$text = $id." : ".$text;
								file_put_contents($path,$text,FILE_APPEND); 
					}
						}
						    
    }
}
}
}

//get number of words in a sentence.
function getWordLength($text) {

			$num  = str_word_count($text);
			return $num;

	}



?>

