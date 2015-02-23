<?php
if (empty($_FILES) || $_FILES['file']['error']) {
  die('{"OK": 0, "info": "Failed to move uploaded file."}');
}

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    	$ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    	$ip = $_SERVER['REMOTE_ADDR'];
}
mkdir("temp/$ip");

$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
 
$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];

$pos = strrpos($fileName,".");
if($pos!==false && $pos > 0)
	$fileName = substr($fileName,0,$pos);

$fileName = strtolower($fileName);
$fileName = preg_replace("/[^a-zA-Z0-9]+/", " ", $fileName);
$fileName =  preg_replace('!\s+!',' ',$fileName);
$fileName =  str_replace(' ', '_',$fileName);
$fileName =  preg_replace('/_+/','_',$fileName);

@mkdir("users/$ip/uploaded_temp");
$filePath = "users/$ip/uploaded_temp/$fileName";
// Open temp file
$out = @fopen("{$filePath}.internal_reserved_chunk_part", $chunk == 0 ? "wb" : "ab");
if ($out) {
  // Read binary input stream and append it to temp file
  $in = @fopen($_FILES['file']['tmp_name'], "rb");
 
  if ($in) {
    while ($buff = fread($in, 4096))
      fwrite($out, $buff);
  } else
    die('{"OK": 0, "info": "Failed to open input stream."}');
 
  @fclose($in);
  @fclose($out);
 
  @unlink($_FILES['file']['tmp_name']);
} else
  die('{"OK": 0, "info": "Failed to open output stream."}');
 
 
// Check if file has been uploaded
if (!$chunks || $chunk == $chunks - 1) {
  // Strip the temp .part suffix off
  rename("{$filePath}.internal_reserved_chunk_part", $filePath);
        $pic = new imagick($filePath); 
        $pic->resizeImage (400, 400, imagick::FILTER_LANCZOS, 1, TRUE);  //rescale image to fit into 400x400 bounding box.
        file_put_contents($filePath,$pic);
}
 
die('{"OK": 1, "info": "Upload successful."}');
