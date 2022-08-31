<?php

session_start();

require '../../includes/mysql_connect.php';
include '../../includes/functions.php';

// this is a really REALLY bad keygen api
if (isset($_GET['key']))
{
	$key = $_GET['key'];
	if ($key != "c966b9ed80b79917fba5b7abccf028cc3")
	{
		die();
	}
        
   $expiry = sanitize($_GET['expiry']);
   $level = sanitize($_GET['level']);
        
   die(gen_license($expiry, $level));
}

if (!isset($_SESSION["user_data"]) || $_SESSION["user_data"]["level"] !== 5)
{
	http_response_code(404);
	die();
}

if (isset($_POST['getlogs']))
{
    die(file_get_contents("../../logs/all.log"));
}

if (isset($_POST['genlicense']))
{
	$expiry = sanitize($_POST['expiry']);
    $level = sanitize($_POST['level']);
        
    $amount = sanitize($_POST['amount']);
    if (!isset($amount) || !is_numeric($amount) || $amount < 1)
    {
    	$amount = 1;
    }

	if ($amount > 100)
    {
        die("There is a max amount of 100.");
    }

	$keys = array();
	for ($x = 1; $x <= $amount; $x++)
	{
		$key = gen_license($expiry, $amount);
		array_push($keys, $key);
	}
	
	die(json_encode($keys));
}

?>

<!DOCTYPE html>
<html>
    
<head>
	<title>Admin panel</title>
	<meta charset="UTF-8">
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="shortcut icon" href="../../assets/images/favicon.ico" type="image/x-icon">
	<link rel="icon" type="image/icon" href="../../assets/images/favicon.ico">
	<meta content="Intertwined helps you set up your web servers securely, safely, and easily" name="description" />

</head>

<body>
    <form method="post">
		<br><button name="getlogs">GetLogs</button></br>

		<br><input type="level" name="level" placeholder="level"></br>
		<br><input type="expiry" name="expiry" placeholder="expiry"></br>
		<br><input type="amount" name="amount" placeholder="amount"></br>

		<br><button name="genlicense">Generate licenses</button></br>

    </form>
</body>
