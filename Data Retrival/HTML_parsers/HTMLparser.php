
<?php

require_once("simple_html_dom.php");

$dir = '/Users/Ishan/Downloads/yahoo5_all/bass/good/';
$dirIterator = new DirectoryIterator('/Users/Ishan/Downloads/yahoo5_all/bass/good/');



foreach ( $dirIterator as $file){
		
			if (!$file->isDot()){

				$myFile = $dir . $file->getFilename();
				//echo $myFile."\n";
				$html = file_get_html($myFile);
				$mtString = "";
				if ( $html != null){
						//find all body tags
						$body = $html->find('body');

						foreach( $body as $tag){
									$children_of_tag = $tag->children();
									var_dump($tag->plaintext);
									//cvar_dump($children_of_tag);
									foreach( $children_of_tag as $last_line){
												if ( $last_line->tag == 'p'){
														$mtString = $mtString . $last_line->outertext;
														$pattern = '/&nbsp;/';
														$pattern2 = '/&amp;/';
														$pattern3 = '/&quot;/';
														$mtString = preg_replace($pattern,'', $mtString);
														$mtString = preg_replace($pattern2,'', $mtString);
														$mtString = preg_replace($pattern3,'', $mtString);
														$mtString = strip_tags($mtString);
													//	print $mtString;
												}

									}
						}

						$tbody = $html->find('tbody');
						foreach( $tbody as $tag){
									$children_of_tag = $tag->children();
									var_dump($tag->plaintext);
									//cvar_dump($children_of_tag);
									foreach( $children_of_tag as $last_line){
												if ( $last_line->tag == 'p'){
														$mtString = $mtString . $last_line->outertext;
														$pattern = '/&nbsp;/';
														$pattern2 = '/&amp;/';
														$pattern3 = '/&quot;/';
														$mtString = preg_replace($pattern,'', $mtString);
														$mtString = preg_replace($pattern2,'', $mtString);
														$mtString = preg_replace($pattern3,'', $mtString);
														$mtString = strip_tags($mtString);
													//	print $mtString;
												}

									}
						}
						//$pattern = '/\s/';
						//$mtString = preg_replace($pattern, " ", $mtString);
						$newFilename = $file->getFilename();
						$newFilename = str_replace('html', 'txt', $newFilename);
						$newFilename = "NEW" .$newFilename;
						//echo $newFilename."\n";
						file_put_contents($newFilename, $mtString);
			}
		}

}

?>
