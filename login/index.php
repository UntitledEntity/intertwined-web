<?php
session_start(); 

require '../includes/mysql_connect.php';
include '../includes/functions.php';

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
					
					<img src="../includes/captcha.php">

					<div class="wrap-input100 validate-input m-b-16" data-validate = "Captcha is required">
						<input class="input100" type="text" name="captcha" placeholder="Captcha (if applicable)">
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
			$u = $_POST['user'];
			$result = mysqli_query($mysql_link, "SELECT * FROM users WHERE username = '$u'");

			$ipp = mysqli_fetch_array($result)['ip'];
			if ($ip !== $ipp) 
			{
				if (!strlen($_POST['captcha'])) 
				{
					error("Ip adress mismatch. Please complete captcha.");
					$_SESSION['captcha_needed'] = true;
					echo "<meta http-equiv='Refresh' Content='2; >";
					return;	
				}

				if ($_POST['captcha'] !== $_SESSION["captcha"]){
					error("Invalid Captcha.");
					return;
				}
			}

            $resp = login($_POST['user'], $_POST['pass']);
            if ($resp === 'success')
            {
                echo "<meta http-equiv='Refresh' Content='2; url=../dashboard/'>";
                notif("You have successfully logged in.");
            }
            else {
                switch ($resp)
                {
                    case 'user_not_found':
                        error("The provided username is incorrect. Please check your spelling.");
                    case 'blacklisted':
                        error("The IP you are trying to login to from has been blacklisted due to breaking out TOS.");
                    case 'banned':
                        error("The account you are trying to login to has been banned due to breaking out TOS.");
                    case 'subscription_expired':
                        error("Your subscription has expired.");
                    case 'password_mismatch':
                        error("The provided password is incorrect. Please check your spelling.");
                    default:
                        error("There has been an error registering. If this persists, please contact an administrator");
                }
            }
    }

	?>

</html>
