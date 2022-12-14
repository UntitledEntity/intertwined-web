<?php

require '../../includes/mysql_connect.php';
include '../../includes/include_all.php';

if (!isset($_SESSION["user_data"]))
{
	header("location: ../");
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

$result = mysqli_query($mysql_link, "SELECT * FROM application_users WHERE application = '$appid'");

$rows = array();
while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}

if (isset($_POST['logout']))
{
    session_destroy();
    unset($_SESSION["user_data"]);
    header("location: ../");
    die();
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
	<div class="limiter">
	    
		<div class="sidebar-dash100">
			<img src="../../assets/images/favicon_img.png" width="150" height="150" class="sidebar-logo-dash100">

            <span class="dash100-form-text">
                Welcome, <?php echo $_SESSION["user_data"]["user"]; ?>.
            </span>

			<a href="../" class="dash100-form-text">Home</a>
            <a href="../application" class="dash100-form-text">Application</a>
            <a href="../licenses" class="dash100-form-text">Licenses</a>
            <a href="../users" class="dash100-form-text">Users</a>
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

                <span class="dash100-form-text">Selected user</span>
				<div class="wrap-select100 m-b-16">
                   	<select class="select100" name="user">
                        <?php 

							if (mysqli_num_rows($result) == 0)
							{
								echo "<option class=\"option100\" value=\"nousers\">No available users</option>";
							}


                            for ($i = 0; $i < count($rows); $i++) {
                                $row = $rows[$i];     
                                $username = $row['username'];

                                echo "<option class=\"option100\" value=\"$i\">$username</option>";
                            }

                        ?>
					</select>
					<span class="focus-select100"></span>
                </div>

				<div class="dash100-wrap-columns">
					<div class="dash100-column">
						<div class="wrap-input100 validate-input m-b-16" data-validate = "new password required">
							<input class="input100" type="text" name="pass" placeholder="New password">
							<span class="focus-input100"></span>
						</div>
							
						<div class="container-dash100-form-btn m-t-17">
							<button name="resetpassword" class="dash100-form-btn">
								Reset password
							</button>
						</div>

						</br>

						<div class="wrap-input100 validate-input m-b-16" data-validate = "license">
							<input class="input100" type="text" name="license" placeholder="License">
							<span class="focus-input100"></span>
						</div>

						<div class="container-dash100-form-btn m-t-17">
							<button name="upgrade" class="dash100-form-btn">
								Upgrade user
							</button>
						</div>
					</div>

					<div class="dash100-column">
						<div class="container-dash100-form-btn m-t-17">
							<button name="getdata" class="dash100-form-btn">
								Copy userdata
							</button>
						</div>
						
						<div class="container-dash100-form-btn m-t-17">
							<button name="ban" class="dash100-form-btn">
								Ban
							</button>
						</div>

						<div class="container-dash100-form-btn m-t-17">
							<button name="unban" class="dash100-form-btn">
								Unban
							</button>
						</div>
					</div>
				</div>
            </form>
		</div>
	</div>
</body>

<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

<!-- Button functions -->
<?php
	if (isset($_POST['resetpassword'])) 
	{
		if (isset($_POST['pass'])) {
			$password = password_hash(sanitize($_POST['pass']), PASSWORD_BCRYPT);
    		$user = sanitize($rows[$_POST['user']]['username']);

    		mysqli_query($mysql_link, "UPDATE application_users SET password = '$password' WHERE username = '$user' and application = '$appid'");
		}
		else {
			notification("Password field empty", NOTIF_ERR);
		}
	}

	if (isset($_POST['getdata'])) {
		$userdata = json_encode($rows[$_POST['user']]);

		echo '<script type=\'text/javascript\'>

        	navigator.clipboard.writeText(\'' . addslashes($userdata) . '\')

        	</script>
        	';

		notification("User data copied to clipboard", NOTIF_OK);
	}

	if (isset($_POST['ban'])) {
		$user = sanitize($rows[$_POST['user']]['username']);

    	mysqli_query($mysql_link, "UPDATE application_users SET banned = 1 WHERE username = '$user' and application = '$appid'");
	}

	if (isset($_POST['unban'])) {
		$user = sanitize($rows[$_POST['user']]['username']);

		mysqli_query($mysql_link, "DELETE from blacklists WHERE user = '$user' and application = '$appid';");
		mysqli_query($mysql_link, "UPDATE application_users SET banned = 0 WHERE username = '$user' and application = '$appid'");
	}

	if (isset($_POST['upgrade'])) {
		$user = sanitize($rows[$_POST['user']]['username']);

		upgrade_application($appid, $user, sanitize($_POST['license']));
	}
?>

</html>	
