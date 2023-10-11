<?php

require '../../includes/mysql_connect.php';
include '../../includes/include_all.php';

if (!isset($_SESSION["user_data"]) || $_SESSION["user_data"]["ip"] != $ip)
{
	session_destroy();
	unset($_SESSION["user_data"]);
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

$result = mysqli_query($mysql_link, "SELECT * FROM variables WHERE appid = '$appid'");

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
				    Variables
				</span>

                <span class="dash100-form-text">Select variable id</span>
				<div class="wrap-select100 m-b-16">
                   	<select class="select100" name="variable" id="variable">
                        <?php 

							if (mysqli_num_rows($result) == 0)
							{
								echo "<option class=\"option100\" var_value=\"novars\">No available ID</option>";
							}


                            for ($i = 0; $i < count($rows); $i++) {
                                $row = $rows[$i];     
                                $var_id = $row['id'];
								$value = $row['value'];
                                echo "<option class=\"option100\" value=\"$i\" var_value=\"$value\">$var_id</option>";
                            }

                        ?>
					</select>
					<span class="focus-select100"></span>
                </div>

				<span class="dash100-form-text">Selected variable value: <span id="selectedvar"></span></span>

				<div class="dash100-wrap-columns">
					<div class="dash100-column">
						
						<div class="wrap-input100 validate-input m-b-16" data-validate = "new value">
							<input class="input100" type="text" name="value" placeholder="New Value">
							<span class="focus-input100"></span>
						</div>
							
						

						<div class="container-dash100-form-btn m-t-17">
							<button name="setvalue" class="dash100-form-btn">
								Set Value
							</button>
						</div>

						<div class="container-dash100-form-btn m-t-17">
							<button name="makevar" class="dash100-form-btn">
								Create Variable
							</button>
						</div>
					</div>
				</div>
            </form>
		</div>
	</div>
</body>


<script>

    const Variable = document.getElementById('variable');
	const SelectedVar = document.getElementById('selectedvar');

	const Selected = Variable.selectedIndex;

	// Do it once to set it to the array(0) link
	const CurrentVar = Variable.options[Selected].getAttribute('var_value');
	SelectedVar.textContent = CurrentVar;

	Variable.addEventListener('change', function () {
		const Selected = Variable.selectedIndex;

		if (Selected >= 0) {
			const CurrentVar = Variable.options[Selected].getAttribute('var_value');
			SelectedVar.textContent = CurrentVar;
		} 
		else {
			SelectedVar.textContent = '';
		}
	});
			
					
</script>


<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

<!-- Button functions -->
<?php
	if (isset($_POST['setvalue'])) 
	{
		if (isset($_POST['value'])) {
			$value = sanitize($_POST['value']);
			$var_id = sanitize($rows[$_POST['variable']]['id']);

    		mysqli_query($mysql_link, "UPDATE variables SET value = '$value' WHERE appid = '$appid' and id = '$var_id'");
		}
		else {
			notification("Value field empty", NOTIF_ERR);
		}
	}

    if (isset($_POST['makevar'])){
        create_variable($appid, $_POST['value']);
    }
?>

</html>	
