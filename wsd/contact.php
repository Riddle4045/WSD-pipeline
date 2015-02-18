<?php
$name = $_POST['name'];
$email = $_POST['email'];

$pos = strpos($email, "@");
$domain = substr($email,$pos+1);

if($name==NULL){
	echo "<script>alert('You must enter your name. $domain');</script>";
}
else if($email==NULL){
	echo "<script>alert('You must enter your email.');</script>";
}
else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
	echo "<script>alert('Your email address is invalid.');</script>";
}
else if(strpos($domain,"edu")==false){
	echo "<script>alert('You must enter an education email.');</script>";
}
else{ //send the message.
	$headers = "From: ".$name."<".$email.">";
	$toaddress = "dihong@cise.ufl.edu";
	mail($toaddress, "UFIT Request", "" ,$headers);
	echo "<script>alert('You request has been submitted successfully.');</script>";
}

echo "<script>window.location = 'ufit.html#contact';</script>";

 ?>


