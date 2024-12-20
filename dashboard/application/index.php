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

$showadmin = "none";
if ($_SESSION["user_data"]["level"] == 5)
{
    $showadmin = "dash100-form-text";
}

if (isset($_POST['logout']))
{
    session_destroy();
    unset($_SESSION["user_data"]);
	header("location: https://intertwined.solutions");
    die();
}

$license_data = get_license($_SESSION["user_data"]["user"]);

$appid = $appinfo['appid'];

if (isset($_POST['apply'])) {

	$enabled = sanitize($_POST['enabled']) == "true" ? 1 : 0;
	$iplock = sanitize($_POST['iplock']) == "true" ? 1 : 0;
	$authlock = sanitize($_POST['authlock']) == "true" ? 1 : 0;
	$hwidlock = sanitize($_POST['hwidlock']) == "true" ? 1 : 0;
	$hashcheck = sanitize($_POST['hashcheck']) == "true" ? 1 : 0;
	$hash = sanitize($_POST['hash']);
	$version = sanitize($_POST['version']);
	
	$result = mysqli_query($mysql_link, "UPDATE user_applications SET enabled = '$enabled', iplock = '$iplock', authlock = '$authlock', hwidlock = '$hwidlock', hashcheck = '$hashcheck', hash = '$hash', version = '$version' WHERE appid = '$appid'");
}

$app_params = get_application_params($appid);

$application_enabled = $app_params['enabled'];
$application_iplock = $app_params['iplock'];
$application_authlock = $app_params['authlock'];
$application_hwidlock = $app_params['hwidlock'];
$application_hashcheck = $app_params['hashcheck'];
$application_version = $app_params['version'];
$application_hash = $app_params['hash'];
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

</head>

<body>
	<div class="limiter">

	    <div class="sidebar-dash100">
			<img src="../../assets/images/favicon_img.png" width="150" height="150" class="sidebar-logo-dash100">

            <span class="dash100-form-text">
                Welcome, <?php echo $_SESSION["user_data"]["user"]; ?>.
            </span>

            <a href="../application" class="dash100-form-text">Application</a>
            <a href="../licenses" class="dash100-form-text">Licenses</a>
            <a href="../users" class="dash100-form-text">Users</a>
			<a href="../serverside" class="dash100-form-text">Serverside</a>
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
				    Application
				</span>
				
				<div class="dash100-wrap-columns">
                    <div class="dash100-column p-r-30">

						<span class="dash100-form-text">
							App ID: <blur><?php echo $appinfo['appid']; ?></blur>
						</span>
							
						<span class="dash100-form-text">
							Enckey: <blur><?php echo $appinfo['enckey']; ?></blur>
						</span>

						<span class="dash100-form-text"> 
							Your license: <blur><?php echo $license_data['license']; ?></blur>
						</span>

						<span class="dash100-form-text">Hash</span>
						<div class="wrap-input100 validate-input m-b-16">
							<input class="input100" type="text" name="hash" value="<?= $application_hash ?>">
							<span class="focus-input100"></span>
						</div>

						<span class="dash100-form-text">Version</span>
						<div class="wrap-input100 validate-input m-b-16">
							<input class="input100" type="text" name="version" value="<?= $application_version ?>">
							<span class="focus-input100"></span>
						</div>
					</div>

					<div class="dash100-column">
						<span class="dash100-form-text">Enabled</span>
						<div class="wrap-select100 m-b-16">
							<select class="select100" name="enabled">
								<option class="option100" value="true" <?= $application_enabled == 1 ? ' selected="selected"' : '';?>>True</option>
								<option class="option100" value="false" <?= $application_enabled == 0 ? ' selected="selected"' : '';?>>False</option>
							</select>
						</div>

						<span class="dash100-form-text">Ip lock</span>
						<div class="wrap-select100 m-b-16">
							<select class="select100" name="iplock">
								<option class="option100" value="true" <?= $application_iplock == 1 ? ' selected="selected"' : '';?>>True</option>
								<option class="option100" value="false" <?= $application_iplock == 0 ? ' selected="selected"' : '';?>>False</option>
							</select>
						</div>

						<span class="dash100-form-text">Auth lock</span>
						<div class="wrap-select100 m-b-16">
							<select class="select100" name="authlock">
								<option class="option100" value="true" <?= $application_authlock == 1 ? ' selected="selected"' : '';?>>True</option>
								<option class="option100" value="false" <?= $application_authlock == 0 ? ' selected="selected"' : '';?>>False</option>
							</select>
						</div>

						<span class="dash100-form-text">HWID lock</span>
						<div class="wrap-select100 m-b-16">
							<select class="select100" name="hwidlock">
								<option class="option100" value="true" <?= $application_hwidlock == 1 ? ' selected="selected"' : '';?>>True</option>
								<option class="option100" value="false" <?= $application_hwidlock == 0 ? ' selected="selected"' : '';?>>False</option>
							</select>
						</div>


						<span class="dash100-form-text">Hash integrity check</span>
						<div class="wrap-select100 m-b-16">
							<select class="select100" name="hashcheck">
								<option class="option100" value="true" <?= $application_hashcheck == 1 ? ' selected="selected"' : '';?>>True</option>
								<option class="option100" value="false" <?= $application_hashcheck == 0 ? ' selected="selected"' : '';?>>False</option>
							</select>
						</div>
					</div>
				</div>


				<div class="container-dash100-form-btn m-t-17">
					<button name="apply" class="dash100-form-btn">
						Apply
					</button>		
				</div>

            </form>
		</div>
	</div>
</body>

<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

<script> 

$(document).ready(function() {
	$('select')
})

</script>

</html>