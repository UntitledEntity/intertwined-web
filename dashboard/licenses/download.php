<?php

require '../../includes/mysql_connect.php';
include '../../includes/functions.php';

if (!isset($_SESSION["user_data"]))
{
	header("location: ../");
	die();
}

$appinfo = json_decode(get_application($_SESSION["user_data"]["user"]));

$appid = $appinfo->appid;

$result = mysqli_query($mysql_link, "SELECT * FROM licenses WHERE application = '$appid'");

// unable to find user
if (mysqli_num_rows($result) === 0)
{
    die("No licenses");
}

$rows = array();
while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}

$data = preg_replace(

    '~[\r\n]+~',

    "\r\n",

    trim(json_encode($rows))

);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="licenses.txt"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($data));


die($data);

?>
