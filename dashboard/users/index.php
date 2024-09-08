<?php

require '../../includes/mysql_connect.php';
include '../../includes/include_all.php';

if (!isset($_SESSION["user_data"]) || $_SESSION["user_data"]["ip"] != $ip)
{
	session_destroy();
	unset($_SESSION["user_data"]);
	header("location: https://intertwined.solutions");
	die();
}

$appinfo = get_application($_SESSION["user_data"]["user"]);
if ($appinfo == 'no_application')
{
	create_application($_SESSION["user_data"]["user"]);
	header('Location: '.$_SERVER['REQUEST_URI']);
}

$appid = $appinfo['appid'];

$showadmin = "none";
if ($_SESSION["user_data"]["level"] == 5)
{
    $showadmin = "dash100-form-text";
}

$filter = "";

if (isset($_POST['logout']))
{
    session_destroy();
    unset($_SESSION["user_data"]);
    header("location: https://intertwined.solutions");
    die();
}

if (isset($_POST['applyfilter'])) {
    $filter = sanitize($_POST['filter']);
}

if (isset($_POST['ban_user'])) {
	global $mysql_link;

	$user = sanitize($_POST['ban_user']);
	mysqli_query($mysql_link, "UPDATE application_users SET banned = 1 WHERE username = '$user' and application = '$appid'");
}

if (isset($_POST['unban_user'])) {
	global $mysql_link;

	$user = sanitize($_POST['unban_user']);
	mysqli_query($mysql_link, "UPDATE application_users SET banned = 0 WHERE username = '$user' and application = '$appid'");
}

if (isset($_POST['change_user_level'])) {
	global $mysql_link;

	$user = sanitize($_POST['user']);
	$new_level = sanitize($_POST['new_level']);
	mysqli_query($mysql_link, "UPDATE application_users SET level = $new_level WHERE username = '$user' and application = '$appid'");
}

if (isset($_POST['reset_ip'])) {
	
	$user = sanitize($_POST['reset_ip']);
	mysqli_query($mysql_link, "UPDATE application_users SET ip = NULL WHERE username = '$user' and application = '$appid'");
}

if (isset($_POST['reset_hwid'])) {
	
	$user = sanitize($_POST['reset_ip']);
	mysqli_query($mysql_link, "UPDATE application_users SET hwid = NULL WHERE username = '$user' and application = '$appid'");
}

if (isset($_POST['reset_password'])) 
{
	$password = password_hash(sanitize($_POST['pass']), PASSWORD_BCRYPT);
	$user = sanitize($_POST['user']);

	mysqli_query($mysql_link, "UPDATE application_users SET password = '$password' WHERE username = '$user' and application = '$appid'");
}

if (isset($_POST['reset_user'])) 
{
	$user = sanitize($_POST['user']);
	$new_user = sanitize($_POST['new_user']);

	mysqli_query($mysql_link, "UPDATE application_users SET username = '$new_user' WHERE username = '$user' and application = '$appid'");
}

?>

<!DOCTYPE html>
<html>

<head>
	<title>Intertwined dashboard</title>
	<meta charset="UTF-8">

	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="shortcut icon" href="../../assets/images/favicon.ico" type="image/x-icon">
	<link rel="icon" type="image/icon" href="../../assets/images/favicon.ico">
	<meta content="Intertwined helps you set up your web servers securely, safely, and easily" name="description" />

	<link rel="stylesheet" href="../../assets/css/index-bootstrap.min.css">

	<link rel="stylesheet" type="text/css" href="../misc/css/main.css" />
    <link rel="stylesheet" type="text/css" href="../misc/css/util.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700,800&display=swap">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800&display=swap">
	<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

    <!-- prevent resubmission -->
    <script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
    </script>
</head>

<body>

	<script>
		function ChangeLevel(id) {
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

                xhr.send("change_user_level=true&user=" + id + "&new_level=" + newLevel);
            }
        }

		function ChangeUser(id) {
            var newUser = prompt("Enter the new user:");
            if (newUser !== null && isNaN(newUser)) {
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

                xhr.send("reset_user=true&user=" + id + "&new_user=" + newUser);
            }
        }

		function ChangePassword(id) {
            var newPassword = prompt("Enter the new password:");
            if (newPassword !== null && isNaN(newPassword)) {
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

                xhr.send("reset_password=true&user=" + id + "&pass=" + newPassword);
            }
        }
	</script>

	<div class="limiter">
	    
		<div class="sidebar-dash100">
			<img src="../../assets/images/favicon_img.png" width="150" height="150" class="sidebar-logo-dash100">

            <span class="dash100-form-text">
                Welcome, <?php echo $_SESSION["user_data"]["user"]; ?>.
            </span>

            <a href="../application" class="dash100-form-text">Application</a>
            <a href="../licenses" class="dash100-form-text">Licenses</a>
            <a href="../users" class="dash100-form-text">Users</a>
			<a href="../webhooks" class="dash100-form-text">Webhooks</a>
			<a href="../variables" class="dash100-form-text">Variables</a>
            <a href="../admin" class='<?php echo $showadmin; ?>'>Admin</a>

                    
            <div class ="sidebar-logout-dash100 m-t-17">
                <form method="post">
                    <button name="logout" class="dash100-form-btn">
                        Logout
                    </button>
                </form>
            </div>
        </div>

		<div class="container-dash100">
			<form method="post">

                <span class="dash100-form-title">
				    Users
				</span>

                <div class="dash100-wrap-columns">
                    <div class="dash100-column">
                        <div class="wrap-input100 validate-input m-b-16" data-validate = "">
                                <input class="input100" type="text" name="filter" placeholder="Filter">
                                <span class="focus-input100"></span>
                        </div>
                    </div>

                    <div class="dash100-column">
                        <div class="wrap-input100 validate-input m-b-16" data-validate = "">
                            <button name="applyfilter" class="dash100-form-btn">
                                Apply 
                            </button>
                        </div>
                    </div>
                </div>

				<table class="dash100-table">
                            <table class="dash100-table">
                            <tr>	
                                <th>Username</th>
                                <th>Expires</th>
								<th>Last Login</th>
                                <th>Level</th>
                                <th>Banned</th>
                                <th>Ban</th>
                                <th>Unban</th>
								<th>Reset Ip</th>
								<th>Reset HWID</th>
                                <th>Change Level</th>
                                <th>Change User</th>
                                <th>Change Password</th>
                            </tr>

                            <?php
                                $usersPerPage = 8;

                                $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                $start = ($page - 1) * $usersPerPage;

                                $response = mysqli_query($mysql_link, "SELECT * FROM `application_users` WHERE application = '$appid' LIMIT $start, $usersPerPage");

                                if ($filter) {
                                    $response = mysqli_query($mysql_link, "SELECT * FROM `application_users` WHERE application = '$appid' and username LIKE '%{$filter}%' LIMIT $start, $usersPerPage");
                                }

                                while ($rows = mysqli_fetch_array($response)) {
                                    echo "
                                        <tr>
                                            <th>". $rows['username'] . " </th>
                                            <th>". gmdate("F j, Y, g:i a", $rows['expires'] ) . " </th>
                                            <th>". gmdate("F j, Y, g:i a", $rows['lastlogin'] ) . " </th>
                                            <th>". $rows['level'] . " </th>
                                            <th>". $rows['banned'] . " </th>
                                            <th> 
												<button class='dash100-table-button' type='submit' name='ban_user' value='" . $rows['username'] . "'>Ban</button>
											</th>
											<th> 
												<button class='dash100-table-button' type='submit' name='unban_user' value='" . $rows['username'] . "'>Unban</button>
											</th>
											<th> 
												<button class='dash100-table-button' type='submit' name='reset_ip' value='" . $rows['username'] . "'>Reset IP</button>
											</th>
											<th> 
												<button class='dash100-table-button' type='submit' name='reset_hwid' value='" . $rows['username'] . "'>Reset HWID</button>
											</th>
											<th>
												<button class='dash100-table-button' onclick='ChangeLevel(\"" . $rows['username'] . "\")'>Change Level</button>
											</th>
											<th>
												<button class='dash100-table-button' onclick='ChangeUser(\"" . $rows['username'] . "\")'>Change Username</button>
											</th>
											<th>
												<button class='dash100-table-button' onclick='ChangePassword(\"" . $rows['username'] . "\")'>Change Password</button>
											</th>
                                        </tr>
                                    ";
                                };

                                $totalUsersQuery = mysqli_query($mysql_link, "SELECT COUNT(*) AS total FROM `application_users` WHERE application = '$appid'");
                                $totalUsers = mysqli_fetch_assoc($totalUsersQuery)['total'];
                                $totalPages = ceil($totalUsers / $usersPerPage);

                                echo "<div class='pagination'>";
                                for ($i = 1; $i <= $totalPages; $i++) {
                                    echo "<a href='?page=$i'>$i</a> ";
                                }
                                echo "</div>";
                                ?>
			            </table>
			</form>
		</div>
	</div>
</body>

<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

<!-- Button functions -->
<?php


	if (isset($_POST['getdata'])) {
		$userdata = json_encode($rows[$_POST['user']]);

		echo '<script type=\'text/javascript\'>

        	navigator.clipboard.writeText(\'' . addslashes($userdata) . '\')

        	</script>
        	';

		notification("User data copied to clipboard", NOTIF_OK);
	}
?>

</html>	
