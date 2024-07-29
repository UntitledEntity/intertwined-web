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

if (isset($_POST['change_license_level'])) {
	global $mysql_link;

	$license = sanitize($_POST['license']);
	$new_level = sanitize($_POST['new_level']);

	mysqli_query($mysql_link, "UPDATE licenses SET level = '$new_level' WHERE license = '$license' and application = '$appid'");
	
}

if (isset($_POST['delete_license'])) {
	global $mysql_link;

//applieduser

	$license = sanitize($_POST['license']);
    $applied_user = get_license_data($license, $appid)['applieduser'];

	mysqli_query($mysql_link, "DELETE FROM licenses WHERE license = '$license' and application = '$appid'");
	
    $delete_user_account = sanitize($_POST['delete_user_acc']);
    if ($delete_user_account) {
        mysqli_query($mysql_link, "DELETE FROM application_users WHERE username = '$applied_user' AND application = '$appid'");
    }

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

    <script>

        function ChangeLevel(id) {
            var newLevel = prompt("Enter the new level:");
            if (newLevel !== null && !isNaN(newLevel)) {
                // Submit the form with AJAX
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        // Reload the page or update the table as needed
                        location.reload();
                    }
                };

                xhr.send("change_license_level=true&license=" + id + "&new_level=" + newLevel);
            }
        }

        function DeleteLicense(license) {
			var Result = confirm("Are you sure you want to delete " + license + " ?");
			if (Result == true) {
                var DeleteUserAcc = confirm("Do you also want to delete the user account associated to " + license + " ?");
				// Submit the form with AJAX
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        // Reload the page or update the table as needed
                        location.reload();
                    }
                };

                var construct = "delete_license=true&delete_user_acc=" + DeleteUserAcc + "&license=" + license;
                console.log(construct)
                xhr.send(construct);
            }
		}

    </script>

	<div class="limiter">
	    
        <div class="sidebar-dash100">
            <img src="../../assets/images/favicon_img.png" width="150" height="150" class="sidebar-logo-dash100">


            <span class="dash100-form-text">
                Welcome, <?php echo $_SESSION["user_data"]["user"]; ?>.
            </span>

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
                                <option class="option100" value="week">1 week</option>
                                <option class="option100" value="2week">2 weeks</option>
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
                    <div class="dash100-column">
                        <table class="dash100-table">
                            <tr>	
                                <th>License</th>
                                <th>Expires</th>
                                <th>Level</th>
                                <th>Banned</th>
                                <th>Applied</th>
                                <th>Applied User</th>
                                <th>Use date</th>
                                <th>Created</th>
                                <th>Delete</th>
                                <th>Change Level</th>
                            </tr>

                            <?php
                                $licensesPerPage = 4;

                                $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                $start = ($page - 1) * $licensesPerPage;

                                $response = mysqli_query($mysql_link, "SELECT * FROM `licenses` WHERE application = '$appid' LIMIT $start, $licensesPerPage");

                                while ($rows = mysqli_fetch_array($response)) {
                                    echo "
                                        <tr>
                                            <th>". $rows['license'] . " </th>
                                            <th>". gmdate("F j, Y, g:i a", $rows['expires'] ) . " </th>
                                            <th>". $rows['level'] . " </th>
                                            <th>". $rows['banned'] . " </th>
                                            <th>". $rows['applied'] . " </th>
                                            <th>". $rows['applieduser'] . " </th>
                                            <th>". gmdate("F j, Y, g:i a", $rows['usedate'] ) . " </th>
                                            <th>". gmdate("F j, Y, g:i a", $rows['created']  ) . " </th>
                                            <th> 
                                                <button class='dash100-table-button' onclick='DeleteLicense(\"" . $rows['license'] . "\")'>Delete</button>
                                            </th>
                                            <th>
                                                <button class='dash100-table-button' onclick='ChangeLevel(\"" . $rows['license'] . "\")'>Change Level</button>
                                            </th>
                                        </tr>
                                    ";
                                };

                                $totalLicensesQuery = mysqli_query($mysql_link, "SELECT COUNT(*) AS total FROM `licenses` WHERE application = '$appid'");
                                $totalLicenses = mysqli_fetch_assoc($totalLicensesQuery)['total'];
                                $totalPages = ceil($totalLicenses / $licensesPerPage);

                                echo "<div class='pagination'>";
                                for ($i = 1; $i <= $totalPages; $i++) {
                                    echo "<a href='?page=$i'>$i</a> ";
                                }
                                echo "</div>";
                                ?>
			            </table>
                    </div>
                </div>
            </form>
		</div>
	</div>
</body>

<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

<?php

    if (isset($_POST['downloadlicenses']))
    {
        echo "<meta http-equiv='Refresh' Content='0; url=../misc/licenses-download.php'>";
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
