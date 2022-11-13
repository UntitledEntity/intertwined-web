<?php

require '../../includes/mysql_connect.php';
include '../../includes/include_all.php';

if (!isset($_SESSION["user_data"]))
{
	header("location: ../");
	die();
}


$fileid = randomstring();

$filename = $_FILES['fileToUpload']['name'];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

$path = "../../api/files/$fileid.$ext";

if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $path)) 
{  
    die("Ok");
}
else
{
    die("Not ok");
}

?>