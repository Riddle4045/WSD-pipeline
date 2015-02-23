<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
 
<title>upload</title>

<script type="text/javascript" src="js/plupload.full.min.js"></script>

<script type="text/javascript" src="fancybox/lib/jquery-1.10.1.min.js"></script>

<style type="text/css">
a{
    text-decoration: none;
}
a:link,a:visited{
    color:rgb(255,255,255);
}
a:hover{
    cursor:pointer;
}
td{
	max-width: 300px;
	overflow:hidden;
	text-overflow:ellipsis;
	white-space:nowrap;
}
.rounded_div{
border:1px solid;
border-radius:5px;
border-color:rgb(222,235,247);
}
</style>

</head>
<body>

<?php  /*clear existing data & session variable.*/
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
			$ip = $_SERVER['REMOTE_ADDR'];
	}
	shell_exec("rm users/$ip/uploaded_temp/*");  //clean existing data.
?>

<div style="width:100%;min-height:50px;background-color:rgb(247,251,255);overflow: hidden;">
<span style="line-height:50px;font-size:24px;font-family:Arial, Helvetica, sans-serif">&nbsp;&nbsp;Upload Files<span>
</div>
<div id="container" style="width:100%;background-color:rgb(222,235,247);overflow: hidden;">
<table align="center" style="width:100%;" cellspacing="5">
<tr><td align="center" colspan="5">
<div style="width:94%;height:220px;overflow-y:auto" align="center" id="filelist"><span style="font-size:28px; font-family:Arial, Helvetica, sans-serif; color:rgb(150,150,150)"><br /><br /><br />No file selected</span></div>
</td></tr>
<tr><h1></h1></tr>
<tr><h1></h1></tr>
<tr>
<td>&nbsp;&nbsp;</td>
<td algin="left">* Please do not refresh the page during upload.<br /></td>
<td align="center" style="width:120px;"><a id="browse" href="javascript:;" ><div class="rounded_div" id="choose" onmouseout="this.style.background='rgb(34,94,168)';" onmouseover="this.style.background='rgb(60,112,190)';" style="background-color:rgb(34,94,168);line-height: 35px;font-size:16px;font-family:Arial, Helvetica, sans-serif">Choose files</div></a></td>
<td align="center" style="width:90px;"><div id="cancel" onclick="javascript:parent.jQuery.fancybox.close();" class="rounded_div" style="background-color:rgb(130,10,18); color:rgb(255,255,255); line-height: 35px;font-size:16px;font-family:Arial, Helvetica, sans-serif" onmouseout="this.style.background='rgb(130,10,18)';" onmouseover="this.style.background='rgb(160,30,40)';this.style.cursor='pointer';">Cancel</div></td>
<td>&nbsp;&nbsp;</td>
</tr>
</table>
</div>

<pre id="console"></pre>
 
<script type="text/javascript">

function set_uploaded(num){
	parent.document.getElementById('files_uploaded').innerHTML = "#Files uploaded: "+num;
	parent.num_file_uploaded = num;
}

function confirm_upload_success(num){
	$.post("confirm_upload.php",
	{
		data: null
	},
		function(server_output, status){
 			document.getElementById('cancel').innerHTML="<div onclick=\"javascript:parent.jQuery.fancybox.close(); set_uploaded("+num+")\" class=\"rounded_div\" onmouseout=\"this.style.background='rgb(34,94,168)';\" onmouseover=\"this.style.background='rgb(60,112,190)';\" style=\"background-color:rgb(34,94,168);line-height: 35px;font-size:16px;font-family:Arial, Helvetica, sans-serif\">Done</div>";
	});
}

var uploader = new plupload.Uploader({
  browse_button: 'browse', // this can be an id of a DOM element or the DOM element itself
  url: 'upload.php',
  chunk_size: '2000kb',
  max_retries: 3,
	filters: {
	  mime_types : [
		{ title : "Image files", extensions : "jpg,gif,png" }
	  ],
	  max_file_size: "1mb",
	  prevent_duplicates: true
	}
});
 
uploader.init();


uploader.bind('FilesAdded', function(up, files) {
  var html = '<table bgcolor="#e5f5e0" border="1" style="border-color:rgb(255,255,255);" cellpadding="0" cellspacing="0"\" width=\"100%\">';
  plupload.each(files, function(file) {
    html += '<tr><td align="left" height="28">&nbsp;&nbsp;' + file.name + '&nbsp;&nbsp;<span style="color:rgb(100,100,100)">[' + plupload.formatSize(file.size) + ']</span></td><td height="28" align="center" id="' + file.id + '" width=\"100px\"><span>waiting</span></td></tr>';
  });
  document.getElementById('filelist').innerHTML = html+"</table>";
  document.getElementById('choose').style.display="none";
  document.getElementById('cancel').innerHTML="<div onclick=\"javascript:parent.jQuery.fancybox.close();\" class=\"rounded_div\" style=\"background-color:rgb(130,10,18);line-height: 35px;font-size:16px;font-family:Arial, Helvetica, sans-serif\" onmouseout=\"this.style.background='rgb(130,10,18)';\" onmouseover=\"this.style.background='rgb(160,30,40)';\">Abort</div>";
  uploader.start();
});
 
uploader.bind('UploadProgress', function(up, file){
  document.getElementById(file.id).getElementsByTagName('span')[0].innerHTML = '<span>' + file.percent + "%</span>";
});

uploader.bind('Error', function(up, err) {
  alert("\nFailed to upload the files.");
});

uploader.bind('FileUploaded', function() {
 if (uploader.files.length == (uploader.total.uploaded + uploader.total.failed)) {
 	 
 	 if(uploader.total.failed>0){
 	 	alert("\nFailed to upload the files.");
 		document.getElementById('cancel').innerHTML="<div onclick=\"javascript:parent.jQuery.fancybox.close(); set_uploaded(0)\" class=\"rounded_div\" onmouseout=\"this.style.background='rgb(34,94,168)';\" onmouseover=\"this.style.background='rgb(60,112,190)';\" style=\"background-color:rgb(34,94,168);line-height: 35px;font-size:16px;font-family:Arial, Helvetica, sans-serif\">Done</div>";
 	 }
 	 else{
 	 	set_uploaded(uploader.total.uploaded);
 	 	confirm_upload_success(uploader.total.uploaded);
 	 }
 }
 });

</script>
</body>
</html>
