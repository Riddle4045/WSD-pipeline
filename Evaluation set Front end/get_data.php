<?php
		



		$n =  new MongoClient(); 	
		$dbname = "wsd"; 	
		//handle to mongo db
		$db = $n->$dbname; 
		$list = $db->listCollections();
		//json string holding the ajax response.
		$jsonString = array();
		$collection_names = array('raretweets');
		//get the number of objects returned by the query to set up pagination.
		$count =0; 		$index = 0;
		$get_narrow_set=false;

		
		if ( isset($_POST['input'])) {
				$keyword = $_POST['input'];
		}
		if ( isset($_POST['currIndex']) ) {
				$currIndex = $_POST['currIndex']*10;

		}
		if ( isset($_POST['numItemsPerPage']) ) {
				$numItemsPerPage = $_POST['numItemsPerPage'];

		}

		if ( isset($_POST["set1"])) {
				$get_narrow_set =true;
		}

	
		//preparing the query w.r.t keyword choosen
		$regex = "/".$keyword."/i";
		$regexObj = new MongoRegex($regex);
		$where = array("text" => $regexObj); 
		

		$addedTweets = array();
		foreach ($list as $collection) {
				if ( $collection == 'wsd.raretweets' || $collection == 'wsd.tweets'){
				$cursor =$collection->find($where);
				while ( $cursor->hasNext() ) {
						$index = $index +1;
						$document = $cursor->getNext();
		                $id = $document['id'];
						$text = $document['text'];
						$entities = $document['entities'];
						if (isset($entities['media'])) {
								//add only if its a new tweet
								if ( !isset($addedTweets['id']))
									{
											$media  = $entities['media'];
											$img = $media[0]["media_url"];
											if( $index >= $currIndex*$numItemsPerPage){
													$jsonString[] = array("id"=>$id,"text"=>$text,"imgUrl"=>$img);
													$count = $count + 1;
													$addedTweets['id'] = 1;
 													}			
									}
						}
						if ( ($get_narrow_set == true && $count > 50)) break;

				 	
				}
			}
	}	

		$jsonString[] = array("numItems"=>$count);
		echo json_encode($jsonString);
?>

