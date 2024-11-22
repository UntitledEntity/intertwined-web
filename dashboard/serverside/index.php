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

if (isset($_POST['logout']))
{
    session_destroy();
    unset($_SESSION["user_data"]);
    header("location: https://intertwined.solutions");
    die();
}

// GLOBAL VARIABLES

$webhooks = mysqli_query($mysql_link, "SELECT * FROM webhooks WHERE appid = '$appid'");
$whrows = mysqli_fetch_all($webhooks, MYSQLI_ASSOC);

$variables = mysqli_query($mysql_link, "SELECT * FROM variables WHERE appid = '$appid'");
$varrows = mysqli_fetch_all($variables, MYSQLI_ASSOC);


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
				    Server-Side Data
				</span>
				
				<div class="dash100-wrap-columns">
					<div class="dash100-column p-r-30">
						<span class="dash100-form-text">Webhook ID</span>
						<div class="wrap-select100 m-b-16">
							<select class="select100" name="webhook" id="webhook">
								<?php 
									if (mysqli_num_rows($webhooks) == 0)
									{
										echo "<option class=\"option100\" value=\"nowhid\">No available ID</option>";
									}

									foreach ($whrows as $i => $row) {
										$id = $row['id'];
										$link = $row['link'];
										echo "<option class=\"option100\" value=\"$i\" link=\"$link\">$id</option>";
									}
								?>
							</select>
							<span class="focus-select100"></span>
						</div>

						<span class="dash100-form-text">Current: <span id="selectedlink"></span>

							<div class="wrap-input100 validate-input m-b-16">
								<input class="input100" type="text" name="link_input" id="link">
								<span class="focus-input100"></span>
							</div>
						
							<div class="container-dash100-form-btn m-t-17">
								<button name="setlink" class="dash100-form-btn">
									Update
								</button>
							</div>

							<div class="container-dash100-form-btn m-t-17">
								<button name="makewebhook" class="dash100-form-btn">
									Create
								</button>
							</div>
					</div>

					<div class="dash100-column">
						<span class="dash100-form-text">Variable id</span>
						<div class="wrap-select100 m-b-16">
							<select class="select100" name="variable" id="variable">
								<?php 
									if (mysqli_num_rows($variables) == 0)
									{
										echo "<option class=\"option100\" value=\"nousers\">No available ID</option>";
									}

									foreach ($varrows as $i => $row) {
										$id = $row['id'];
										$value = $row['value'];
										echo "<option class=\"option100\" value=\"$i\" var=\"$value\">$id</option>";
									}
								?>
							</select>
							<span class="focus-select100"></span>
						</div>

						<span class="dash100-form-text">Current: <span id="selectedvar"></span></span>

						<div class="wrap-input100 validate-input m-b-16">
								<input class="input100" type="text" name="var_input" id="var">
								<span class="focus-input100"></span>
							</div>
						

						<div class="container-dash100-form-btn m-t-17">
							<button name="setvar" class="dash100-form-btn">
								Update
							</button>
						</div>

						<div class="container-dash100-form-btn m-t-17">
							<button name="makevariable" class="dash100-form-btn">
								Create
							</button>
						</div>
					</div>
				</div>
            </form>
		</div>
	</div>
</body>


<script>

	document.addEventListener('DOMContentLoaded', function () {
		// Webhook elements
		const Webhook = document.getElementById('webhook');
		const linkInput = document.getElementById('link');
		const linkText = document.getElementById('selectedlink');
		
		// Variable elements
		const Variable = document.getElementById('variable');
		const varInput = document.getElementById('var');
		const varText = document.getElementById('selectedvar');

		// Function to update the link input and text
		function updateLink(index) {
			if (index >= 0) {
				const selectedOption = Webhook.options[index];
				const currentLink = selectedOption.getAttribute('link') || '';
				
				// Update the input field and displayed text
				linkInput.value = currentLink;
				linkText.textContent = currentLink || 'No link selected';
			} else {
				// If no valid option, clear input and text
				linkInput.value = '';
				linkText.textContent = 'No link selected';
			}
		}

		// Function to update the var input and text
		function updateVariable(index) {
			if (index >= 0 ) {
				const selectedOption = Variable.options[index];
				const currentVar = selectedOption.getAttribute('var') || '';
				
				// Update the input field and displayed text
				varInput.value = currentVar;
				varText.textContent = currentVar || 'No variable selected';
			} else {
				// If no valid option, clear input and text
				varInput.value = '';
				varText.textContent = 'No variable selected';
			}
		}

		// Initial update when the page loads
		updateLink(Webhook.selectedIndex);
		updateVariable(Variable.selectedIndex);

		// Event listener to handle changes to the dropdowns
		Webhook.addEventListener('change', function () {
			updateLink(Webhook.selectedIndex);
		});

		Variable.addEventListener('change', function() {
			updateVariable(Variable.selectedIndex);
		});
	});

</script>


<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

<!-- Button functions -->
<?php
	if (isset($_POST['setlink'])) 
	{
		if (isset($_POST['link_input'])) {
			$link = sanitize($_POST['link_input']);
			$whid = sanitize($whrows[$_POST['webhook']]['id']);

    		mysqli_query($mysql_link, "UPDATE webhooks SET link = '$link' WHERE appid = '$appid' and id = '$whid'");
		}
		else {
			notification("Link field empty", NOTIF_ERR);
		}
	}

	if (isset($_POST['setvar'])) 
	{
		if (isset($_POST['var_input'])) {
			$value = sanitize($_POST['var_input']);
			$varid = sanitize($varrows[$_POST['variable']]['id']);

    		mysqli_query($mysql_link, "UPDATE variables SET value = '$value' WHERE appid = '$appid' and id = '$varid'");
		}
		else {
			notification("Link field empty", NOTIF_ERR);
		}
	}

    if (isset($_POST['makewebhook'])){
        create_webhook($_POST['link_input'],$appid);
    }

    if (isset($_POST['makevariable'])){
        create_webhook($_POST['var_input'],$appid);
    }
?>

</html>	
