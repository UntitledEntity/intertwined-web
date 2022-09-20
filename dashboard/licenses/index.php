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

$result = mysqli_query($mysql_link, "SELECT * FROM licenses WHERE application = '$appid'");

// unable to find user
if (mysqli_num_rows($result) === 0)
{
    die("No licenses");
}

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
            <a href="../users" class="dash100-form-text">Users</a>
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
				    Licenses
				</span>

				<div class="wrap-input100 validate-input m-b-16" data-validate = "Level required">
					<input class="input100" type="text" name="level" placeholder="Level">
					<span class="focus-input100"></span>
				</div>


				<span class="dash100-form-text">License expiration</span>
				<div class="wrap-input100 m-b-16">
                    <select class="input100" name="expiration">
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

                <div class="container-dash100-form-btn m-t-17">
					<button name="genlicense" class="dash100-form-btn">
						Generate
					</button>
				</div>

                <div class="container-dash100-form-btn m-t-17">
					<button name="downloadlicenses" class="dash100-form-btn">
						Download all licenes
					</button>
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
        echo "<meta http-equiv='Refresh' Content='0; url=download.php'>";
    }

    if (isset($_POST['genlicense']))
    {
        $expiry = sanitize($_POST['expiration']);
        $level = sanitize($_POST['level']);

        $amount = sanitize($_POST['amount']);
        if (!is_numeric($amount) || $amount < 1)
        {
            error("Amount must be numeric");
        }

        $keys = "";
        for ($x = 1; $x <= $amount; $x++)
        {
            $key = generate_application_license($appinfo->appid, $expiry, $amount);

            if ($x === 1)
                $keys = $key;
            else
                $keys = $keys .= ", $key";
        }

        echo '<script type=\'text/javascript\'>

        navigator.clipboard.writeText(\'' . addslashes($keys) . '\')

        </script>
        ';

        if ($amount === 1)
            notif("Copied license to clipboard");
        else
            notif("Copied licenses to clipboard");
    }


?>

</html>
