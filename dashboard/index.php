<?php 

require '../includes/mysql_connect.php';
include '../includes/functions.php';

if (!isset($_SESSION["user_data"]))
{
	header("location: ../");
	die();
}

$showadmin = "none";
if ($_SESSION["user_data"]["level"] === 5)
{
    $showadmin = "dash100-form-text";
}

$license_data = get_license($_SESSION["user_data"]["user"]);
$license_data = json_decode($license_data);
    
?>

<!DOCTYPE html>
<html>
    
<head>
	<title>Intertwined dashboard</title>
	<meta charset="UTF-8">
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="shortcut icon" href="../assets/images/favicon.ico" type="image/x-icon">
	<link rel="icon" type="image/icon" href="../assets/images/favicon.ico">
	<meta content="Intertwined helps you set up your web servers securely, safely, and easily" name="description" />

	<link rel="stylesheet" href="../assets/css/index-bootstrap.min.css">
	
	<link rel="stylesheet" type="text/css" href="css/main.css" />
    <link rel="stylesheet" type="text/css" href="css/util.css" />

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
			
            <a href="stats" class="dash100-form-text">Server stats</a>
            <a href="application" class="dash100-form-text">Application</a>
            <a href="licenses" class="dash100-form-text">Licenses</a>
			<a href="files" class="dash100-form-text">Files</a>
			<a href="users" class="dash100-form-text">Users</a>
            <a href="admin" class='<?php echo $showadmin; ?>'>Admin</a>
            
        </div>
	    
		<div class="container-dash100">

				<form method="post">
				    
				    <span class="dash100-main-title">
				        Home
			        </span>
				    
				    <span class="dash100-form-text"> 
				       Your license: <blur><?php echo $license_data->license ?></blur>
		        	</span>
				    
				    <div class="container-dash100-form-btn m-t-17">
						<button name="logout" class="dash100-form-btn">
							Logout
						</button>
					</div>
					
					<div class="container-dash100-form-btn m-t-17">
						<button name="deleteaccount" class="dash100-form-btn">
							Delete account
						</button>
					</div>
					
                </form>
                
			
		</div>
	</div>
</body>

<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

<?php

    if (isset($_POST['logout']))
    {
        session_destroy();
        unset($_SESSION["user_data"]);
        header("location: ../");
        die();
    }


    if (isset($_POST['deleteaccount']))
    {
        warning("Are you sure you want to delete your account? Please click again to delete your account.");
        $_SESSION["timesclicked_delete"] += 1;
    
        if ($_SESSION["timesclicked_delete"] >= 2)
        {
            delete_account($_SESSION["user_data"]["user"]);
            session_destroy();
            unset($_SESSION["user_data"]);
            header("location: ../");
            die();
        }
    }
?>

</html>