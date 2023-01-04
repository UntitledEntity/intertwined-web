<?php
session_start(); 

require '../includes/mysql_connect.php';
include '../includes/include_all.php';

if (isset($_SESSION["user_data"]))
{
    header("location: ../dashboard");
    die();
}

?>

<!DOCTYPE html>
<html>
    
<head>
	<title>Intertwined login</title>
	<meta charset="UTF-8">
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="shortcut icon" href="../assets/images/favicon.ico" type="image/x-icon">
	<link rel="icon" type="image/icon" href="../assets/images/favicon.ico">
	<meta content="Intertwined helps you set up your web servers securely, safely, and easily" name="description" />

	<link rel="stylesheet" href="../assets/css/index-bootstrap.min.css">

	<link rel="stylesheet" type="text/css" href="../assets/css/index-style.css" />
    <link rel="stylesheet" type="text/css" href="../assets/css/index-accordian.css" />
	<link rel="stylesheet" type="text/css" href="../assets/css/util.css">
	<link rel="stylesheet" type="text/css" href="../assets/css/main.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700,800&display=swap">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800&display=swap">
	<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

</head>

<body>
	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100 p-t-50 p-b-90">
				<form class="login100-form validate-form flex-sb flex-w" method="post">

					<span class="login100-form-title p-b-51">
						Intertwined Login
					</span>
				
					<div class="wrap-input100 validate-input m-b-16" data-validate = "Username is required">
						<input class="input100" type="text" name="user" placeholder="Username">
						<span class="focus-input100"></span>
					</div>
					
					<div class="wrap-input100 validate-input m-b-16" data-validate = "Password is required">
						<input class="input100" type="password" name="pass" placeholder="Password">
						<span class="focus-input100"></span>
					</div>
					
					<img class="captcha-image" src="../assets/php/captcha.php">
					
					<div class="wrap-input100 validate-input m-b-16" data-validate = "Captcha is required">
						<input class="input100" type="text" name="captcha" placeholder="Captcha (if new I.P address)">
						<span class="focus-input100"></span>
					</div>
					
					<div class="container-login100-form-btn m-t-17">
						<button name="login" class="login100-form-btn">
							Login
						</button>
					</div>
     	
				</form>
			</div>
		</div>
	</div>
</body>

    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
	
	<?php
	
	    if (isset($_POST['login']))
        {
			global $ip;
			$result = mysqli_query($mysql_link, "SELECT * FROM users WHERE username = '" . $_POST['user'] . "';");

			$ipp = mysqli_fetch_array($result)['ip'];
			if ($ip !== $ipp) 
			{
				admin_log("IP mismatch detected for " . $_POST['user'] . " on IP" . $ip . " with the stored IP ". $ipp, LOG_USR);
				
				if (!strlen($_POST['captcha'])) 
				{
					notification("Ip adress mismatch. Please complete captcha.", NOTIF_ERR);
					$_SESSION['captcha_needed'] = true;
					echo "<meta http-equiv='Refresh' Content='2; >";
					return;	
				}

				if ($_POST['captcha'] !== $_SESSION["captcha"]){
					notification("Invalid Captcha.", NOTIF_ERR);
					return;
				}
			}

            $resp = login($_POST['user'], $_POST['pass']);
           
            switch ($resp)
            {
                case 'user_not_found':
                    notification("The provided username is incorrect. Please check your spelling.", NOTIF_ERR);
					return;
                case 'blacklisted':
					admin_log($_POST['user'] . "attempted to login with the blacklisted ip " . $ip, LOG_USR);
					admin_log("A login attempt was made on a blacklisted account.", LOG_DISC);
                    notification("The IP you are trying to login to from has been blacklisted due to breaking TOS.", NOTIF_ERR);
					return;
				case 'banned':
					admin_log($ip . " attempted to login to the banned account " . $_POST['user'], LOG_USR);
					admin_log("A login attempt was made on a banned account.", LOG_DISC);
                    notification("The account you are trying to login to has been banned due to breaking TOS.", NOTIF_ERR);
					return;
                case 'subscription_expired':
                    notification("Your subscription has expired.", NOTIF_ERR);
					return;
                case 'password_mismatch':
                    notification("The provided password is incorrect. Please check your spelling.", NOTIF_ERR);
					return;
				case 'success':
					echo "<meta http-equiv='Refresh' Content='2; url=../dashboard/'>";
                	notification("You have successfully logged in.", NOTIF_OK);
                default:
					notification("There has been an error logging in. If this persists, please contact an administrator", NOTIF_ERR);
					admin_log($resp, LOG_ERRR);
					return;
            }
            
		}

	?>

</html>
