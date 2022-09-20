<?php

require '../../includes/mysql_connect.php';
include '../../includes/functions.php';

if (!isset($_SESSION["user_data"]))
{
	header("location: ../");
	die();
}

$appinfo = get_application($_SESSION["user_data"]["user"]);
if ($appinfo === 'no_application')
{
	create_application($_SESSION["user_data"]["user"]);
	header('Location: '.$_SERVER['REQUEST_URI']);
}

$appinfo = json_decode($appinfo);
$appid = $appinfo->appid;

$showadmin = "none";
if ($_SESSION["user_data"]["level"] === 5)
{
    $showadmin = "dash100-form-text";
}

$result = mysqli_query($mysql_link, "SELECT * FROM application_users WHERE application = '$appid'");

// unable to find user
if (mysqli_num_rows($result) === 0)
{
    die("No application_users");
}

$rows = array();
while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}

$application_users = json_encode($rows);

if (isset($_POST['resetpassword']))
{
    $password = password_hash(sanitize($_POST['pass']), PASSWORD_BCRYPT);
    $user = sanitize($_POST['user']);

    mysqli_query($mysql_link, "UPDATE application_users SET password = '$password' WHERE username = '$user' and application = '$appid'");
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

	<link rel="stylesheet" type="text/css" href="../css/main.css" />
    <link rel="stylesheet" type="text/css" href="../css/util.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700,800&display=swap">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800&display=swap">
	<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

</head>

<body>
	<div class="limiter">
	    <div class="sidebar-dash100">
	        <span class="dash100-form-title">
				Dashboard
			</span>

			<span class="dash100-form-text">
				Welcome, <?php echo $_SESSION["user_data"]["user"]; ?>.
			</span>

            <a href="../stats" class="dash100-form-text">Server stats</a>
            <a href="../application" class="dash100-form-text">Application</a>
            <a href="../licenses" class="dash100-form-text">Licenses</a>
            <a href="../files" class="dash100-form-text">Files</a>
            <a href="../admin" class='<?php echo $showadmin; ?>'>Admin</a>

            <form method="post">
                <div class="container-dash100-form-btn m-t-17">
				    <button name="logout" class="dash100-form-btn">
					    Logout
				    </button>
			    </div>
            </form>

        </div>

		<div class="container-dash100">
			<form method="post">

                <span class="dash100-form-title">
				    Users
				</span>

				<div class="wrap-input100 validate-input m-b-16" data-validate = "user required">
					<input class="input100" type="user" name="user" placeholder="User">
					<span class="focus-input100"></span>
				</div>

                <div class="wrap-input100 validate-input m-b-16" data-validate = "new password required">
					<input class="input100" type="pass" name="pass" placeholder="New password">
					<span class="focus-input100"></span>
				</div>

                <div class="container-dash100-form-btn m-t-17">
					<button name="resetpassword" class="dash100-form-btn">
						Reset password
					</button>
				</div>

            </form>

			<span class="dash100-form-text"><?php echo $application_users; ?></span>
		</div>
	</div>
</body>

<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

<?php

?>

</html>
