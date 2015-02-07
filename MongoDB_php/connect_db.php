



<?php 

$user = array(
	'first_name' => 'MongoDB',
	'last_name' => 'Fan',
	'tags' => array('developer','user')
);

$connection = new MongoClient(); 
$dbname = "ishan_test";
$db  = $connection->$dbname;

$c_users =  $db->users;
$c_users->save($user);


