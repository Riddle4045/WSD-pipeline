
<?php

require_once("simple_html_dom.php");

$dir = '/Users/Ishan/Downloads/yahoo5_all/speaker/good/';
$dirIterator = new DirectoryIterator('/Users/Ishan/Downloads/yahoo5_all/speaker/good/');



foreach ( $dirIterator as $file){
		
			if (!$file->isDot()){

				$myFile = $dir . $file->getFilename();
				echo $myFile."\n";
				$html = file_get_html($myFile);
				$mtString = "";
				if ( $html != null){
						// Find all images 
						$body = $html->find('body');

						foreach( $body as $tag){
									$children_of_tag = $tag->children();
									foreach( $children_of_tag as $last_line){
												$mtString = $mtString . trim($last_line->plaintext);
									}
						}
						$pattern = '/\s/';
						$mtString = preg_replace($pattern, " ", $mtString);
						$newFilename = $file->getFilename();
						$newFilename = "new" .$newFilename;
						file_put_contents($newFilename, $mtString);
			}
		}

}

?>
