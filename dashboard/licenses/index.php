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

$result = mysqli_query($mysql_link, "SELECT * FROM licenses WHERE application = '$appid'");

$rows = array();
while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}

$licenses = json_encode($rows);


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
				    Licenses
				</span>
                
                <div class="dash100-wrap-columns">
                    
					<div class="dash100-column">
                        <div class="wrap-input100 validate-input m-b-16" data-validate = "Level required">
                            <input class="input100" type="text" name="level" placeholder="Level">
                            <span class="focus-input100"></span>
                        </div>


                        <span class="dash100-form-text">License expiration</span>
                        <div class="wrap-select100 m-b-16">
                            <select class="select100" name="expiration">
                                <option class="option100" value="month">1 month</option>
                                <option class="option100" value="half-year">6 months</option>
                                <option class="option100" value="year">1 year</option>
                                <option class="option100" value="never">Never</option>
                            </select>
                        </div>

                        <div class="wrap-input100 validate-input m-b-16" data-validate = "Amount required">
                            <input class="input100" type="text" name="amount" placeholder="Amount">
                            <span class="focus-input100"></span>
                        </div>

                        <span class="dash100-form-text">Format</span>
                        <div class="wrap-select100 m-b-16">
                            <select class="select100" name="format">
                                <option class="option100" value="text">Text</option>
                                <option class="option100" value="json">JSON</option>
                            </select>
                        </div>

                        <div class="container-dash100-form-btn m-t-17">
                            <button name="genlicense" class="dash100-form-btn">
                                Generate
                            </button>
                        </div>
                    </div>

                    <div class="dash100-column">
                        <div class="container-dash100-form-btn m-t-17">
                            <button name="downloadlicenses" class="dash100-form-btn">
                                Download all licenses
                            </button>
                        </div>

                        <div class="container-dash100-form-btn m-t-17">
                            <button name="deleteunusedlicenses" class="dash100-form-btn">
                                Delete unused licenses
                            </button>
                        </div>
                    </div>

                </div>

            </form>

				<span class="dash100-form-text"><?php echo $licenses; ?></span>
		</div>
	</div>
</body>

<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

<?php

    if (isset($_POST['downloadlicenses']))
    {
        echo "<meta http-equiv='Refresh' Content='0; url=https://intertwined.solutions/dashboard/misc/licenses-download.php'>";
    }

    if (isset($_POST['genlicense']))
    {
        $expiry = sanitize($_POST['expiration']);
        
        $level = sanitize($_POST['level']);
        $amount = sanitize($_POST['amount']);
        if (!isset($level) || !isset($amount))
        {
            notification("One or more fields are missing.", NOTIF_ERR);
            return;
        }

        if (!is_numeric($level) || $level < 1)
        {
            notification("Level must be numeric", NOTIF_ERR);
            return;
        }

        if (!is_numeric($amount) || $amount < 1 || $amount > 50)
        {
            notification("Invalid amount.", NOTIF_ERR);
            return;
        }

        if ($_POST['format'] == "json") 
        {
            $keys = array();
            for ($x = 1; $x <= $amount; $x++)
            {
                $key = generate_application_license($appid, $expiry, $level);
                array_push($keys, $key);
            }

            $keys = json_encode($keys);
        }
        else 
        {
            $keys = "";
            for ($x = 1; $x <= $amount; $x++)
            {
                $key = generate_application_license($appid, $expiry, $level);
    
                if ($x == 1)
                    $keys = $key;
                else
                    $keys = $keys .= ", $key";
            }
        }

        echo '<script type=\'text/javascript\'>

        navigator.clipboard.writeText(\'' . addslashes($keys) . '\')

        </script>
        ';

        notification("Copied license(s) to clipboard", NOTIF_OK);
    }

    if (isset($_POST['deleteunusedlicenses']))
    {
        mysqli_query($mysql_link, "DELETE FROM licenses WHERE applied is null AND application = '$appid'");
    }
?>

</html>
