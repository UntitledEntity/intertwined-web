<?php
session_start(); 

//Display errors
ini_set ('display_errors', 1); ini_set ('display_startup_errors', 1); error_reporting (E_ALL); 

require '../../includes/mysql_connect.php';
include '../../includes/include_all.php';

if (isset($_SESSION["user_data"]))
{
    header("location: ../../dashboard");
    die();
}

if (!isset($_GET['resetcode']) || !check_auth_code($_GET['resetcode']))
{
    header("location: https://intertwined.solutions/login/forgot/");
    die();
}

?>

<!DOCTYPE html>
<html>
    
<head>
	<title>Intertwined login</title>
	<meta charset="UTF-8">
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="shortcut icon" href="../../assets/images/favicon.ico" type="image/x-icon">
	<link rel="icon" type="image/icon" href="../../assets/images/favicon.ico">
	<meta content="Intertwined helps you set up your web servers securely, safely, and easily" name="description" />

	<link rel="stylesheet" href="../../assets/css/index-bootstrap.min.css">

	<link rel="stylesheet" type="text/css" href="../../assets/css/index-style.css" />
    <link rel="stylesheet" type="text/css" href="../../assets/css/index-accordian.css" />
	<link rel="stylesheet" type="text/css" href="../../assets/css/util.css">
	<link rel="stylesheet" type="text/css" href="../../assets/css/main.css">

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
						Reset Password
					</span>
				
                    <div class="wrap-input100 validate-input m-b-16" data-validate = "New password is required">
						<input class="input100" type="text" name="pass" placeholder="New Password">
						<span class="focus-input100"></span>
					</div>
					
					<img class="captcha-image" src="../../assets/php/captcha.php">
					
					<div class="wrap-input100 validate-input m-b-16" data-validate = "Captcha is required">
						<input class="input100" type="text" name="captcha" placeholder="Captcha">
						<span class="focus-input100"></span>
					</div>
					
					<div class="container-login100-form-btn m-t-17">
						<button name="reset" class="login100-form-btn">
							Reset Password
						</button>
					</div>
     	
				</form>
			</div>
		</div>
	</div>
</body>

    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
	
	<?php
	
	    if (isset($_POST['reset']))
        {
			
            if (!strlen($_POST['captcha'])) 
            {
                notification("Please complete captcha.", NOTIF_ERR);
                return;	
            }
            if ($_POST['captcha'] !== $_SESSION["captcha"]){
                notification("Invalid Captcha.", NOTIF_ERR);
                return;
            }
            
            // resetcode is passed through get, new pass is submitted in the form.
            if (!reset_password($_GET['resetcode'], $_POST['pass'])) {
				notification("Unable to reset at this moment. If the error persits, please contact us.", NOTIF_ERR);
				return;
			}

			notification("Successfully updated your password!", NOTIF_OK);
			header("location: https://intertwined.solutions/login");
		}

	?>

</html>
