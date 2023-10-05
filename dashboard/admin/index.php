<?php

session_start();

require '../../includes/mysql_connect.php';
include '../../includes/include_all.php';

if (!isset($_SESSION["user_data"]) || 
	$_SESSION["user_data"]["ip"] != $ip ||
	$_SESSION["user_data"]["level"] !== 5)
{
	session_destroy();
	unset($_SESSION["user_data"]);
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

if (isset($_POST['delete_license'])) {
	global $mysql_link;

	$user = sanitize($_POST['delete_license']);
	mysqli_query($mysql_link, "DELETE FROM licenses WHERE license = '$user' and application is null");
}

if (isset($_POST['change_license_level'])) {
	global $mysql_link;

	$license = sanitize($_POST['license']);
	$new_level = sanitize($_POST['new_level']);

	mysqli_query($mysql_link, "UPDATE licenses SET level = '$new_level' WHERE license = '$license' and application is null");
}

if (isset($_POST['change_user_level'])) {
	global $mysql_link;

	$user = sanitize($_POST['user']);
	$new_level = sanitize($_POST['new_level']);

	mysqli_query($mysql_link, "UPDATE users SET level = '$new_level' WHERE username = '$user'");
}

if (isset($_POST['change_username'])) {
	global $mysql_link;

	$old_user = sanitize($_POST['user']);
	$new_user = sanitize($_POST['new_user']);

	mysqli_query($mysql_link, "UPDATE users SET username = '$new_user' WHERE username = '$old_user'");
	
	mysqli_query($mysql_link, "UPDATE user_applications SET owner = '$new_user' WHERE owner = '$old_user'");
	
	mysqli_query($mysql_link, "UPDATE licenses SET applieduser = '$new_user' WHERE applieduser = '$old_user' and application is NULL");
}


if (isset($_POST['reset_enckey'])) {
	global $mysql_link;

	$appid = sanitize($_POST['reset_enckey']);

	$new_enckey = md5(rand());

	// the chance of two randomly generated md5 keys being the same is 2.9387359e-39%, so it's safe to say if it happens once (it won't), it won't happen consecutively twice.
	$result = mysqli_query($mysql_link, "SELECT * from user_applications WHERE enckey = '$new_enckey'");
	if (mysqli_num_rows($result))
		$new_enckey = md5(rand());

	mysqli_query($mysql_link, "UPDATE user_applications SET enckey = '$new_enckey' WHERE appid = '$appid'");
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

		.padding-25{
			padding: 25px;
		}
		
		.table-button {
			width: 100%;
   			padding: 5px;
		}

		blur {
			-webkit-filter: blur(4px); /* Chrome, Safari, Opera */
			filter: blur(4px);
		}

		blur:hover {
			-webkit-filter: blur(0px); /* Chrome, Safari, Opera */
			filter: blur(0px);
		}


	</style>

</head>

<body>

	<script>
		function ChangeLevel(id, license) {
			var newLevel = prompt("Enter the new level:");
			if (newLevel !== null && !isNaN(newLevel)) {
				// Submit the form with AJAX
				var xhr = new XMLHttpRequest();
				xhr.open("POST", "", true);
				xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xhr.onreadystatechange = function () {
					if (xhr.readyState == 4 && xhr.status == 200) {
						// Reload the page or update the table as needed
						location.reload();
					}
				};
				// Assuming you have a proper way to identify the license in your backend
				if (license)
					xhr.send("change_license_level=true&license=" + id + "&new_level=" + newLevel);
				else
					xhr.send("change_user_level=true&user=" + id + "&new_level=" + newLevel);
			}
		}

		function ChangeUser(user) {
			var newUser = prompt("Enter the new username:");
			if (newUser !== null && newUser !== "") {
				// Submit the form with AJAX
				var xhr = new XMLHttpRequest();
				xhr.open("POST", "", true);
				xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xhr.onreadystatechange = function () {
					if (xhr.readyState == 4 && xhr.status == 200) {
						// Reload the page or update the table as needed
						location.reload();
					}
				};

				xhr.send("change_username=true&user=" + user + "&new_user=" + encodeURIComponent(newUser));
			}
		}

		function DeleteLicense(license) {
			var Result = confirm("Are you sure you want to delete " + license + " ?");
			if (Result == true) {
				var xhr = new XMLHttpRequest();
				xhr.open("POST", "", true);
				xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xhr.onreadystatechange = function () {
					if (xhr.readyState == 4 && xhr.status == 200) {
						// Reload the page or update the table as needed
						location.reload();
					}
				};

				xhr.send("delete_license=" + license);
			}
		}

	</script>

    <form method="post">
		<br><button name="getlogs">GetLogs</button></br>

		<br><input name="level" placeholder="level"></br>
		<br><input name="expiry" placeholder="expiry"></br>
		<br><input name="amount" placeholder="amount"></br>

		<br><button name="genlicense">Generate licenses</button></br>

		<div class="padding-25">
			<a>Table of users</a>

			<table>
				<tr>	
					<th>Username</th>
					<th>Expires</th>
					<th>Banned</th>
					<th>Ip</th>
					<th>Lastlogin</th>
					<th>Level</th>
					<th>Appid</th>
					<th>Ban</th>
					<th>Unban</th>
					<th>Change Level</th>
					<th>Change User</th>
				</tr>

				<?php 
					
					$response = mysqli_query($mysql_link, "SELECT * FROM `users`");

					while ($rows = mysqli_fetch_array($response)) {
						echo "
							<tr>
								<th>". $rows['username'] . " </th>
								<th>". $rows['expires'] . " </th>
								<th>". $rows['banned'] . " </th>
								<th> <blur>". $rows['ip'] . "</blur> </th>
								<th>". gmdate("F j, Y, g:i a", $rows['lastlogin'] ) . " GMT </th>
								<th>". $rows['level'] . " </th>
								<th>". get_application($rows['username'])['appid'] . " </th>
								<th> 
									<button class='table-button' type='submit' name='ban_user' value='" . $rows['username'] . "'>Ban</button>
								</th>
								<th> 
									<button class='table-button' type='submit' name='unban_user' value='" . $rows['username'] . "'>Unban</button>
								</th>
								<th>
									<button class='table-button' onclick='ChangeLevel(\"" . $rows['username'] . "\", false)'>Change Level</button>
								</th>
								<th>
									<button class='table-button' onclick='ChangeUser(\"" . $rows['username'] . "\")'>Change Username</button>
								</th>
							</tr>
						";
					};
				?>
			</table>
		</div>

		<div class="padding-25">
			<a>Table of licenses</a>

			<table>
				<tr>	
					<th>License</th>
					<th>Exires</th>
					<th>Level</th>
					<th>Applied</th>
					<th>Applied User</th>
					<th>Use date</th>
					<th>Created</th>
					<th>Delete</th>
					<th>Change Level</th>
				</tr>

				<?php 
					
					$response = mysqli_query($mysql_link, "SELECT * FROM `licenses` WHERE application is NULL");

					while ($rows = mysqli_fetch_array($response)) {
						echo "
							<tr>
								<th>". $rows['license'] . " </th>
								<th>". $rows['expires'] . " </th>
								<th>". $rows['level'] . " </th>
								<th>". $rows['applied'] . " </th>
								<th>". $rows['applieduser'] . " </th>
								<th>". gmdate("F j, Y, g:i a", $rows['usedate'] ) . " GMT </th>
								<th>". gmdate("F j, Y, g:i a", $rows['created']  ) . " GMT </th>
								<th> 
									<button class='table-button' onclick='DeleteLicense(\"" . $rows['license'] . "\")'>Delete</button>
								</th>
								<th>
									<button class='table-button' onclick='ChangeLevel(\"" . $rows['license'] . "\", true)'>Change Level</button>
								</th>
							</tr>
						";
					};
				?>
			</table>
		</div>

		<div class="padding-25">
			<a>Table of user applications</a>

			<table>
				<tr>	
					<th>Appid</th>
					<th>Owner</th>
					<th>EncKey</th>
					<th>Enabled</th>
				</tr>

				<?php 
					
					$response = mysqli_query($mysql_link, "SELECT * FROM `user_applications`");

					while ($rows = mysqli_fetch_array($response)) {
						echo "
							<tr>
								<th>". $rows['appid'] . " </th>
								<th>". $rows['owner'] . " </th>
								<th> <blur>". $rows['enckey'] . "</blur> </th>
								<th>". $rows['enabled'] . " </th>
								<th> 
									<button class='table-button' type='submit' name='reset_enckey' value='" . $rows['appid'] . "'>Reset Encryption Key</button>
								</th>
							</tr>
						";
					};
				?>
			</table>
		</div>

    </form>
</body>