<?php

session_start();

require '../../includes/mysql_connect.php';
include '../../includes/include_all.php';

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

if (isset($_POST['ban_user'])) {
	global $mysql_link;

	$user = sanitize($_POST['ban_user']);
	mysqli_query($mysql_link, "UPDATE users SET banned = 1 WHERE username = '$user'");
}

if (isset($_POST['unban_user'])) {
	global $mysql_link;

	$user = sanitize($_POST['unban_user']);
	mysqli_query($mysql_link, "UPDATE users SET banned = 0 WHERE username = '$user'");
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
	<meta content="Intertwined helps you set up your web servers securely, safely, and easily" name="description">
  
	<style>
		html {
			font-family: arial, sans-serif;
			width: 100%;
		}

		table {
			font-family: arial, sans-serif;
			border-collapse: collapse;
			width: 100%;
		}

		tr, th {
			border: 1px solid #dddddd;
			text-align: left;
			padding: 8px;
		}

	</style>

</head>

<body>
    <form method="post">
		<br><button name="getlogs">GetLogs</button></br>

		<br><input name="level" placeholder="level"></br>
		<br><input name="expiry" placeholder="expiry"></br>
		<br><input name="amount" placeholder="amount"></br>

		<br><button name="genlicense">Generate licenses</button></br>

		<a>Table of users</a>
		<table>
			<tr>	
				<th>Username</th>
				<th>Expires</th>
				<th>Banned</th>
				<th>Ip</th>
				<th>Lastlogin</th>
				<th>Level</th>
				<th>Ban</th>
				<th>UnBan</th>
			</tr>

			<?php 
				
				$response = mysqli_query($mysql_link, "SELECT * FROM `users`");

				while ($rows = mysqli_fetch_array($response)) {
					echo "
						<tr>
							<th>". $rows['username'] . " </th>
							<th>". $rows['expires'] . " </th>
							<th>". $rows['banned'] . " </th>
							<th>". $rows['ip'] . " </th>
							<th>". $rows['lastlogin'] . " </th>
							<th>". $rows['level'] . " </th>
							<th> 
								<button type='submit' name='ban_user' value='" . $rows['username'] . "'>Ban</button>
							</th>
							<th> 
								<button type='submit' name='unban_user' value='" . $rows['username'] . "'>UnBan</button>
							</th>
						</tr>
					";
				};
			?>
		</table>

    </form>
</body>