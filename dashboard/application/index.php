<?php
require '../../includes/mysql_connect.php';
include '../../includes/include_all.php';

if (!isset($_SESSION["user_data"]) || $_SESSION["user_data"]["ip"] != $ip) {
    session_destroy();
    unset($_SESSION["user_data"]);
    header("location: https://intertwined.solutions");
    die();
}

$appinfo = get_application($_SESSION["user_data"]["user"]);
if ($appinfo == 'no_application') {
    create_application($_SESSION["user_data"]["user"]);
    header('Location: ' . $_SERVER['REQUEST_URI']);
}

if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION["user_data"]);
    header("location: https://intertwined.solutions");
    die();
}

$license_data = get_license($_SESSION["user_data"]["user"]);

$appid = $appinfo['appid'];

$level = "";
$price = "";
$max_users = 0;
$max_licenses = 0;
$max_serverside = 0;
$max_sessions = 0;

switch ($license_data['level']) {
    case 1:
        $level = "Basic";
        $price = "$4.99 / year";
        $max_users = $max_licenses = 50;
        $max_serverside = 5;
        $max_sessions = 500;
        break;
    case 2:
        $level = "Standard";
        $price = "$9.99 / year";
        $max_users = $max_licenses = 100;
        $max_serverside = 10;
        $max_sessions = 1000;
        break;
    case 3:
        $level = "Premium";
        $price = "$14.99 / year";
        $max_users = $max_licenses = 150;
        $max_serverside = 25;
        $max_sessions = 1000;
        break;
    case 4:
    case 5:
        $level = "Enterprise";
        $price = "$34.99 / year";
        $max_users = $max_licenses = 1000;
        $max_serverside = 100;
        $max_sessions = 10000;
        break;
}


if (isset($_POST['UpdateAppData'])) {
    $enabled = isset($_POST['AppEnabled']) ? 1 : 0;
    $iplock = isset($_POST['IpLock']) ? 1 : 0;
    $authlock = isset($_POST['AuthLock']) ? 1 : 0;
    $hwidlock = isset($_POST['HWIDLock']) ? 1 : 0;

    $version = sanitize($_POST['AppVer']);

    $hashcheck = isset($_POST['HashCheck']) ? 1 : 0;
    $hash = sanitize($_POST['AppHash']);

    $result = mysqli_query($mysql_link, "UPDATE user_applications SET enabled = '$enabled', iplock = '$iplock', authlock = '$authlock', hwidlock = '$hwidlock', hashcheck = '$hashcheck', hash = '$hash', version = '$version' WHERE appid = '$appid'");
}

if (isset($_POST['UpdateActiveFunctions'])) {
    $bitstr = "";

    // Please clean this up later
    $bitstr .= isset($_POST['UserLoginEnable']) ? '1' : '0';
    $bitstr .= isset($_POST['LicenseLoginEnable']) ? '1' : '0';
    $bitstr .= isset($_POST['RegisterEnable']) ? '1' : '0';
    $bitstr .= isset($_POST['UpgradeEnable']) ? '1' : '0';
    $bitstr .= isset($_POST['WebhooksEnable']) ? '1' : '0';
    $bitstr .= isset($_POST['VarsEnable']) ? '1' : '0';

    $result = mysqli_query($mysql_link, "UPDATE user_applications SET enabled_functions = '$bitstr' where appid = '$appid'");
}

$appstats = get_application_stats($appinfo['appid']);
$app_params = get_application_params($appid);

$application_enabled = $app_params['enabled'];
$application_iplock = $app_params['iplock'];
$application_authlock = $app_params['authlock'];
$application_hwidlock = $app_params['hwidlock'];
$application_hashcheck = $app_params['hashcheck'];
$application_version = $app_params['version'];
$application_hash = $app_params['hash'];

if (isset($_POST['ResetEncKey'])) {
    global $mysql_link;

    $new_enckey = md5(rand());

    // the chance of two randomly generated md5 keys being the same is 2.9387359e-39%, so it's safe to say if it happens once (it won't), it won't happen consecutively twice.
    $result = mysqli_query($mysql_link, "SELECT * from user_applications WHERE enckey = '$new_enckey'");
    if (mysqli_num_rows($result))
        $new_enckey = md5(rand());

    mysqli_query($mysql_link, "UPDATE user_applications SET enckey = '$new_enckey' WHERE appid = '$appid'");
}

if (isset($_POST['ResetAppID'])) {
    global $mysql_link;

    $new_appid = randomstring();

    $result = mysqli_query($mysql_link, "SELECT * from user_applications WHERE appid = '$appid'");
    if (mysqli_num_rows($result)) {
        notification("Please try again.", NOTIF_ERR);
    }

    mysqli_query($mysql_link, "UPDATE user_applications SET appid = '$new_appid' WHERE appid = '$appid'");
    mysqli_query($mysql_link, "UPDATE variables SET appid = '$new_appid' WHERE appid = '$appid'");
    mysqli_query($mysql_link, "UPDATE webhooks SET appid = '$new_appid' WHERE appid = '$appid'");

    mysqli_query($mysql_link, "UPDATE application_users SET application = '$new_appid' WHERE application = '$appid'");
    mysqli_query($mysql_link, "UPDATE licenses SET application = '$new_appid' WHERE application = '$appid'");;
    mysqli_query($mysql_link, "UPDATE blacklists SET application = '$new_appid' WHERE application = '$appid'");
}
?>

<!DOCTYPE html>
<html lang="en" class="no-js">
<!-- Head -->

<head>
    <title>Intertwined Dashboard</title>

    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <!-- Favicon -->
    <link rel="shortcut icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <meta content="Intertwined helps you set up your web servers securely, safely, and easily" name="description" />

    <!-- Schema.org -->
    <meta itemprop="image" content="../misc/assets/img-temp/aduik-preview.png">

    <!-- Web Fonts -->
    <link href="//fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">

    <!-- Components Vendor Styles -->
    <link rel="stylesheet" href="../misc/assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../misc/assets/vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css">

    <!-- Theme Styles -->
    <link rel="stylesheet" href="../misc/assets/css/theme.css">

    <!-- Notifications -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
</head>
<!-- End Head -->

<!-- Body -->

<body>
    <!-- Header (Topbar) -->
    <header class="u-header">
        <!-- Header Left Section -->
        <div class="u-header-left">
            <!-- Header Logo -->
            <a class="u-header-logo" href="index.html">
                <img class="u-header-logo__icon" src="../../assets/images/favicon.ico" width="50" height="50" alt="Main ICO">
                <h2 class="h4 card-header-title">Intertwined</h2>
            </a>
            <!-- End Header Logo -->
        </div>
        <!-- End Header Left Section -->

        <!-- Header Middle Section -->
        <div class="u-header-middle">
            <!-- Sidebar Invoker -->
            <div class="u-header-section">
                <a class="js-sidebar-invoker u-header-invoker u-sidebar-invoker" href="#"
                    data-is-close-all-except-this="true"
                    data-target="#sidebar">
                    <span class="ti-align-left u-header-invoker__icon u-sidebar-invoker__icon--open"></span>
                    <span class="ti-align-justify u-header-invoker__icon u-sidebar-invoker__icon--close"></span>
                </a>
            </div>
            <!-- End Sidebar Invoker -->

            <!-- Header Search -->
            <div class="u-header-section justify-content-sm-start flex-grow-1 py-0">
            </div>
            <!-- End Header Search -->

            <!-- Apps -->
            <div class="u-header-section">
                <div class="u-header-dropdown dropdown pt-1">
                    <a id="appsInvoker" class="u-header-invoker d-flex align-items-center" href="#" role="button" aria-haspopup="true" aria-expanded="false"
                        data-toggle="dropdown"
                        data-offset="20">
                        <span class="position-relative">
                            <span class="ti-layout-grid3 u-header-invoker__icon"></span>
                        </span>
                    </a>

                    <div class="u-header-dropdown__menu dropdown-menu dropdown-menu-right" aria-labelledby="appsInvoker" style="width: 320px;">
                        <div class="card p-0">
                            <div class="card-body p-0">
                                <div class="row no-gutters">
                                    <!-- Github -->
                                    <div class="col-4 p-3">
                                        <a class="d-flex flex-column link-dark" href="https://github.com/UntitledEntity/intertwined-web">
                                            <div class="u-icon u-icon-sm rounded-circle bg-info text-white mx-auto mb-2">
                                                <span class="ti-github"></span>
                                            </div>

                                            <span class="font-weight-semi-bold text-center">Web Github</span>
                                        </a>
                                    </div>
                                    <!-- End Github -->

                                    <!-- CPP Example -->
                                    <div class="col-4 p-3">
                                        <a class="d-flex flex-column link-dark" href="https://github.com/UntitledEntity/Intertwined-CPP-Example">
                                            <div class="u-icon u-icon-sm rounded-circle bg-info text-white mx-auto mb-2">
                                                <span class="ti-github"></span>
                                            </div>

                                            <span class="font-weight-semi-bold text-center">C++ Example</span>
                                        </a>
                                    </div>
                                    <!-- End CPP Example -->

                                    <!-- Discord -->
                                    <div class="col-4 p-3">
                                        <a class="d-flex flex-column link-dark" href="https://discord.gg/QZb96GqhGZ">
                                            <div class="u-icon u-icon-sm rounded-circle bg-info text-white mx-auto mb-2">
                                                <span class="ti-world"></span>
                                            </div>

                                            <span class="font-weight-semi-bold text-center">Discord</span>
                                        </a>
                                    </div>
                                    <!-- End App -->

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Apps -->

            <!-- User Profile -->
            <div class="u-header-section u-header-section--profile">
                <div class="u-header-dropdown dropdown">
                    <a class="link-muted d-flex align-items-center" href="#" role="button" id="userProfileInvoker" aria-haspopup="true" aria-expanded="false"
                        data-toggle="dropdown"
                        data-offset="0">
                        <img class="u-header-avatar img-fluid rounded-circle mr-md-3" src="../misc/assets/img-temp/avatars/user-unknown.jpg" alt="User Profile">
                        <span class="text-dark d-none d-md-inline-flex align-items-center">
                            <?php echo $_SESSION["user_data"]["user"]; ?>
                            <span class="ti-angle-down text-muted ml-4"></span>
                        </span>
                    </a>

                    <div class="u-header-dropdown__menu dropdown-menu dropdown-menu-right" aria-labelledby="userProfileInvoker" style="width: 260px;">
                        <div class="card p-3">
                            <div class="card-header border-0 p-0">
                                <!-- Storage -->
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="mb-0">Your AppID: </span>

                                    <div class="text-muted">
                                        <strong class="text-dark"> <?php echo $appinfo['appid']; ?></strong>
                                    </div>
                                </div>

                                <div class="text-muted">
                                    <strong class="text-dark"> <?php echo $license_data['license']; ?></strong>
                                </div>
                                <!-- End Storage -->
                            </div>

                            <hr class="my-4">

                            <form method="post">
                                <div class="card-body p-0">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-3">
                                            <a class="link-dark" href="#">Settings</a>
                                        </li>
                                        <li>
                                            <a class="link-dark" href="?logout">Sign Out</a>
                                        </li>
                                    </ul>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
            <!-- End User Profile -->
        </div>
        <!-- End Header Middle Section -->
    </header>
    <!-- End Header (Topbar) -->

    <!-- Main -->
    <main class="u-main">
        <!-- Sidebar -->
        <aside id="sidebar" class="u-sidebar">
            <div class="u-sidebar-inner">
                <!-- Sidebar Header -->
                <header class="u-sidebar-header">
                    <!-- Sidebar Logo -->
                    <a class="u-sidebar-logo" href="index.html">
                        <img class="u-sidebar-logo__icon" src="../misc/assets/svg/logo-mini.svg" alt="Awesome Icon">
                        <img class="u-sidebar-logo__text" src="../misc/assets/svg/logo-text-light.svg" alt="Awesome">
                    </a>
                    <!-- End Sidebar Logo -->
                </header>
                <!-- End Sidebar Header -->

                <!-- Sidebar Nav -->
                <nav class="u-sidebar-nav">
                    <!-- Sidebar Nav Menu -->
                    <ul class="u-sidebar-nav-menu u-sidebar-nav-menu--top-level">
                        <!-- Application -->
                        <li class="u-sidebar-nav-menu__item">
                            <a class="u-sidebar-nav-menu__link active" href="../application/">
                                <span class="ti-dashboard u-sidebar-nav-menu__item-icon"></span>
                                <span class="u-sidebar-nav-menu__item-title">Application</span>
                            </a>
                        </li>
                        <!-- End Application -->

                        <!-- Users -->
                        <li class="u-sidebar-nav-menu__item">
                            <a class="u-sidebar-nav-menu__link" href="../users">
                                <span class="ti-user u-sidebar-nav-menu__item-icon"></span>
                                <span class="u-sidebar-nav-menu__item-title">Users</span>
                            </a>
                        </li>
                        <!-- End Users -->

                        <!-- Licenses -->
                        <li class="u-sidebar-nav-menu__item">
                            <a class="u-sidebar-nav-menu__link" href="../licenses">
                                <span class="ti-key u-sidebar-nav-menu__item-icon"></span>
                                <span class="u-sidebar-nav-menu__item-title">Licenses</span>
                            </a>
                        </li>
                        <!-- End Licenses -->

                        <!-- Serverside -->
                        <li class="u-sidebar-nav-menu__item">
                            <a class="u-sidebar-nav-menu__link" href="../serverside">
                                <span class="ti-server u-sidebar-nav-menu__item-icon"></span>
                                <span class="u-sidebar-nav-menu__item-title">Serverside</span>
                            </a>
                        </li>
                        <!-- End Serverside -->

                        <!-- Admin -->
                        <li class="<?php $_SESSION["user_data"]["level"] == 5 ? 'u-sidebar-nav-menu__item' : ''; ?>">
                            <a class="u-sidebar-nav-menu__link" href="../admin">
                                <span class="ti-server u-sidebar-nav-menu__item-icon"></span>
                                <span class="u-sidebar-nav-menu__item-title">Admin</span>
                            </a>
                        </li>
                        <!-- End Admin -->


                        <li class="u-sidebar-nav-menu__divider"></li>

                        <!-- Documentation -->
                        <li class="u-sidebar-nav-menu__item">
                            <a class="u-sidebar-nav-menu__link" href="https://github.com/UntitledEntity/intertwined-web/blob/main/DOCS.md" target="_blank">
                                <span class="ti-files u-sidebar-nav-menu__item-icon"></span>
                                <span class="u-sidebar-nav-menu__item-title">Documentation</span>
                            </a>
                        </li>
                        <!-- End Documentation -->

                        <!-- Discord -->
                        <li class="u-sidebar-nav-menu__item">
                            <a class="u-sidebar-nav-menu__link" href="https://github.com/UntitledEntity/intertwined-web" target="_blank">
                                <span class="ti-github u-sidebar-nav-menu__item-icon"></span>
                                <span class="u-sidebar-nav-menu__item-title">Github</span>
                            </a>
                        </li>
                        <!-- End Discord -->

                        <!-- Github -->
                        <li class="u-sidebar-nav-menu__item">
                            <a class="u-sidebar-nav-menu__link" href="https://discord.gg/QZb96GqhGZ" target="_blank">
                                <span class="ti-world u-sidebar-nav-menu__item-icon"></span>
                                <span class="u-sidebar-nav-menu__item-title">Support Discord</span>
                            </a>
                        </li>
                        <!-- End Github -->
                    </ul>
                    <!-- End Sidebar Nav Menu -->
                </nav>
                <!-- End Sidebar Nav -->
            </div>
        </aside>
        <!-- End Sidebar -->

        <!-- Content -->
        <div class="u-content">
            <!-- Content Body -->
            <div class="u-body">
                <div class="row">
                    <div class="col-lg-6 mb-5">
                        <div class="card h-100">
                            <!-- Card Header -->
                            <header class="card-header d-flex align-items-center justify-content-between">
                                <h2 class="h4 card-header-title">Application</h2>
                            </header>
                            <!-- End Card Header -->

                            <!-- Crad Body -->
                            <div class="card-body pt-0">
                                <h5 class="card-title"><strong>Application ID:</strong> <blur><?php echo $appinfo['appid']; ?></blur></h5>
                                <h5 class="card-title"><strong>Encryption Key:</strong> <blur><?php echo $appinfo['enckey']; ?></blur></h5>
                            </div>
                            <!-- End Card Body -->

                            <!-- Card Footer -->
                            <footer class="card-footer border-0">
                                <form method="post">
                                    <button type="submit" name="ResetEncKey" class="btn btn-outline-primary text-uppercase mb-2 mr-2">Reset Encryption key</button>
                                    <button type="submit" name="ResetAppID" class="btn btn-outline-primary text-uppercase mb-2 mr-2">Reset App ID</button>
                                </form>
                            </footer>
                            <!-- End Card Footer -->
                        </div>
                    </div>

                    <div class="col-lg-6 mb-5">
                        <div class="card h-100">
                            <!-- Card Header -->
                            <header class="card-header d-flex align-items-center justify-content-between">
                                <h2 class="h4 card-header-title">Subscription</h2>
                            </header>
                            <!-- End Card Header -->

                            <!-- Crad Body -->
                            <div class="card-body pt-0">
                                <h5 class="card-title"><strong>Subscription:</strong> <?php echo $level; ?></h5>
                                <h5 class="card-title"><strong>Price:</strong> <?php echo $price; ?></h5>
                                <h5 class="card-title"><strong>Expires:</strong> <?php echo date("m/d/y", $license_data['expires']); ?></h5>
                            </div>
                            <!-- End Card Body -->

                            <!-- Card Footer -->
                            <footer class="card-footer border-0">
                                <button href="#" class="btn btn-outline-primary text-uppercase mb-2 mr-2">Renew Subscription</button>
                                <button href="#" class="btn btn-outline-warning text-uppercase mb-2 mr-2">Cancel Subscription</button>
                            </footer>
                            <!-- End Card Footer -->
                        </div>
                    </div>
                </div>

                  <!-- APPINFO ROW -->
                <div class="row">
                    <div class="col-sm-6 col-xl-3 mb-5">
                        <!-- Card -->
                        <div class="card">
                            <!-- Card Body -->
                            <div class="card-body">
                                <!-- Chart with Info -->
                                <div class="media align-items-center py-2">
                                    <!-- Chart with Info - Info -->
                                    <div class="media-body">
                                        <h5 class="h5 text-muted mb-2">Users</h5>
                                        <span class="h2 font-weight-normal mb-0"><?php echo "$appstats[0] / $max_users"  ?></span>
                                    </div>
                                    <!-- End Chart with Info - Info -->
                                </div>
                                <!-- End Chart with Info -->
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo ($appstats[0] / $max_users) * 100 ?>%;" aria-valuenow="<?php echo $appstats[0] ?>" aria-valuemin="0" aria-valuemax="<?php echo $max_users ?>"></div>
                                </div>
                            </div>
                            <!-- End Card Body -->
                        </div>
                        <!-- End Card -->
                    </div>

                    <div class="col-sm-6 col-xl-3 mb-5">
                        <!-- Card -->
                        <div class="card">
                            <!-- Card Body -->
                            <div class="card-body">
                                <!-- Chart with Info -->
                                <div class="media align-items-center py-2">
                                    <!-- Chart with Info - Info -->
                                    <div class="media-body">
                                        <h5 class="h5 text-muted mb-2">Licenses</h5>
                                        <span class="h2 font-weight-normal mb-0"><?php echo "$appstats[1] / $max_licenses"  ?></span>
                                    </div>
                                    <!-- End Chart with Info - Info -->
                                </div>
                                <!-- End Chart with Info -->
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo ($appstats[1] / $max_licenses) * 100 ?>%;" aria-valuenow="<?php echo $appstats[1] ?>" aria-valuemin="0" aria-valuemax="<?php echo $max_licenses ?>"></div>
                                </div>
                            </div>
                            <!-- End Card Body -->
                        </div>
                        <!-- End Card -->
                    </div>

                    <div class="col-sm-6 col-xl-3 mb-5">
                        <!-- Card -->
                        <div class="card">
                            <!-- Card Body -->
                            <div class="card-body">
                                <!-- Chart with Info -->
                                <div class="media align-items-center py-2">
                                    <!-- Chart with Info - Info -->
                                    <div class="media-body">
                                        <h5 class="h5 text-muted mb-2">Serverside Data</h5>
                                        <span class="h2 font-weight-normal mb-0"><?php echo "$appstats[2] / $max_serverside"  ?></span>
                                    </div>
                                    <!-- End Chart with Info - Info -->
                                </div>
                                <!-- End Chart with Info -->
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo ($appstats[2] / $max_serverside) * 100 ?>%;" aria-valuenow="<?php echo $appstats[2] ?>" aria-valuemin="0" aria-valuemax="<?php echo $max_serverside ?>"></div>
                                </div>
                            </div>
                            <!-- End Card Body -->
                        </div>
                        <!-- End Card -->
                    </div>

                    <div class="col-sm-6 col-xl-3 mb-5">
                        <!-- Card -->
                        <div class="card">
                            <!-- Card Body -->
                            <div class="card-body">
                                <!-- Chart with Info -->
                                <div class="media align-items-center py-2">
                                    <!-- Chart with Info - Info -->
                                    <div class="media-body">
                                        <h5 class="h5 text-muted mb-2">Sessions</h5>
                                        <span class="h2 font-weight-normal mb-0"><?php echo "$appstats[3] / $max_sessions"  ?></span>
                                    </div>
                                    <!-- End Chart with Info - Info -->
                                </div>
                                <!-- End Chart with Info -->
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo ($appstats[3] / $max_sessions) * 100 ?>%;" aria-valuenow="<?php echo $appstats[3] ?>" aria-valuemin="0" aria-valuemax="<?php echo $max_sessions ?>"></div>
                                </div>
                            </div>
                            <!-- End Card Body -->
                        </div>
                        <!-- End Card -->
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5 mb-md-0">
                        <!-- Active API Functions -->
                        <div class="card h-100">
                            <!-- Card Header -->
                            <header class="card-header d-flex align-items-center justify-content-between">
                                <h2 class="h4 card-header-title">Active API Functions</h2>
                            </header>
                            <!-- End Card Header -->

                            <form method="POST" action="">
                                <!-- Card Body -->
                                <div class="card-body py-0">
                                    <div class="list-group list-group-flush">

                                        <!-- UserLogin -->
                                        <div class="list-group-item border-0 px-0">
                                            <div class="media align-items-center">
                                                <!-- Task Checkbox -->
                                                <span class="custom-control custom-checkbox custom-checkbox-bordered custom-checkbox-empty mr-4">
                                                    <input id="UserLoginEnable" name="UserLoginEnable" class="custom-control-input" type="checkbox" <?= $appinfo['enabled_functions'][0] == "1" ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="UserLoginEnable"></label>
                                                </span>
                                                <!-- End Task Checkbox -->

                                                <div class="media-body">
                                                    <h4 class="font-weight-normal mb-0">Login (User & Pass)</h4>
                                                    <small class="text-muted">
                                                        Allows a user to login via their username and password
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End UserLogin -->

                                        <!-- LicenseLogin -->
                                        <div class="list-group-item border-0 px-0">
                                            <div class="media align-items-center">
                                                <!-- Task Checkbox -->
                                                <span class="custom-control custom-checkbox custom-checkbox-bordered custom-checkbox-empty mr-4">
                                                    <input id="LicenseLoginEnable" name="LicenseLoginEnable" class="custom-control-input" type="checkbox" <?= $appinfo['enabled_functions'][1] == "1" ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="LicenseLoginEnable"></label>
                                                </span>
                                                <!-- End Task Checkbox -->

                                                <div class="media-body">
                                                    <h4 class="font-weight-normal mb-0">Login (License)</h4>
                                                    <small class="text-muted">
                                                        Allows a user to login via their license assigned to their account
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End LicenseLogin -->

                                        <!-- Register -->
                                        <div class="list-group-item border-0 px-0">
                                            <div class="media align-items-center">
                                                <!-- Task Checkbox -->
                                                <span class="custom-control custom-checkbox custom-checkbox-bordered custom-checkbox-empty mr-4">
                                                    <input id="RegisterEnable" name="RegisterEnable" class="custom-control-input" type="checkbox" <?= $appinfo['enabled_functions'][2] == "1" ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="RegisterEnable"></label>
                                                </span>
                                                <!-- End Task Checkbox -->

                                                <div class="media-body">
                                                    <h4 class="font-weight-normal mb-0">Register</h4>
                                                    <small class="text-muted">
                                                        Allows a user to register an account using a license key
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Register -->

                                        <!-- Upgrade -->
                                        <div class="list-group-item border-0 px-0">
                                            <div class="media align-items-center">
                                                <!-- Task Checkbox -->
                                                <span class="custom-control custom-checkbox custom-checkbox-bordered custom-checkbox-empty mr-4">
                                                    <input id="UpgradeEnable" name="UpgradeEnable" class="custom-control-input" type="checkbox" <?= $appinfo['enabled_functions'][3] == "1" ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="UpgradeEnable"></label>
                                                </span>
                                                <!-- End Task Checkbox -->

                                                <div class="media-body">
                                                    <h4 class="font-weight-normal mb-0">Upgrade</h4>
                                                    <small class="text-muted">
                                                        Allows a user to upgrade an account using a license key
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Upgrade -->

                                        <!-- Webhook -->
                                        <div class="list-group-item border-0 px-0">
                                            <div class="media align-items-center">
                                                <!-- Task Checkbox -->
                                                <span class="custom-control custom-checkbox custom-checkbox-bordered custom-checkbox-empty mr-4">
                                                    <input id="WebhooksEnable" name="WebhooksEnable" class="custom-control-input" type="checkbox" <?= $appinfo['enabled_functions'][4] == "1" ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="WebhooksEnable"></label>
                                                </span>
                                                <!-- End Task Checkbox -->

                                                <div class="media-body">
                                                    <h4 class="font-weight-normal mb-0">Webhooks</h4>
                                                    <small class="text-muted">
                                                        Allows webhooks to be accessed within the application
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Webhook -->

                                        <!-- Get Variables -->
                                        <div class="list-group-item border-0 px-0">
                                            <div class="media align-items-center">
                                                <!-- Task Checkbox -->
                                                <span class="custom-control custom-checkbox custom-checkbox-bordered custom-checkbox-empty mr-4">
                                                    <input id="VarsEnable" name="VarsEnable" class="custom-control-input" type="checkbox" <?= $appinfo['enabled_functions'][5] == "1" ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="VarsEnable"></label>
                                                </span>
                                                <!-- End Task Checkbox -->

                                                <div class="media-body">
                                                    <h4 class="font-weight-normal mb-0">Variables</h4>
                                                    <small class="text-muted">
                                                        Allows server-side variables to be retrieved within the application
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Get Variables -->

                                    </div>
                                </div>
                                <!-- End Card Body -->

                                <!-- Card Footer -->
                                <footer class="card-footer border-0">
                                    <button type="submit" name="UpdateActiveFunctions" class="btn btn-link font-weight-semi-bold p-0">Update Active Functions</button>
                                </footer>
                                <!-- End Card Footer -->
                            </form>
                        </div>
                        <!-- End Active API Functions -->
                    </div>


                    <div class="col-md-6 mb-5 mb-md-0">
                        <!-- Application Data -->
                        <div class="card h-100">
                            <!-- Card Header -->
                            <header class="card-header d-flex align-items-center justify-content-between">
                                <h2 class="h4 card-header-title">Settings</h2>
                            </header>
                            <!-- End Card Header -->

                            <form method="POST" action="">
                                <!-- Card Body -->
                                <div class="card-body py-0">

                                    <!-- Enabled -->
                                    <div class="form-group" title="Overall killswitch for every API function."
                                        data-toggle="tooltip"
                                        data-placement="bottom">
                                        <div class="custom-control custom-checkbox">
                                            <input id="AppEnabled" name="AppEnabled" class="custom-control-input" type="checkbox" <?= $application_enabled == 1 ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="AppEnabled">Application Enabled</label>
                                        </div>
                                    </div>

                                    <!-- Ip Lock -->
                                    <div class="form-group" title="Locks user to the IP they first login with after a reset or creating an account."
                                        data-toggle="tooltip"
                                        data-placement="bottom">
                                        <div class="custom-control custom-checkbox">
                                            <input id="IpLock" class="custom-control-input" type="checkbox" <?= $application_iplock == 1 ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" name="IpLock" for="IpLock">Hash Check</label>
                                        </div>
                                    </div>

                                    <!-- Auth Lock -->
                                    <div class="form-group" title="Webhook function will not work unless session is authenticated through 'loginlicense' or 'login' API function."
                                        data-toggle="tooltip"
                                        data-placement="bottom">
                                        <div class="custom-control custom-checkbox">
                                            <input id="AuthLock" name="AuthLock" class="custom-control-input" type="checkbox" <?= $application_authlock == 1 ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="AuthLock">Authentification Lock</label>
                                        </div>
                                    </div>

                                    <!-- HWID Lock -->
                                    <div class="form-group" title="Locks user to the HWID they first login with after a reset or creating an account."
                                        data-toggle="tooltip"
                                        data-placement="bottom">
                                        <div class="custom-control custom-checkbox">
                                            <input id="HWIDLock" name="HWIDLock" class="custom-control-input" type="checkbox" <?= $application_hwidlock == 1 ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="HWIDLock">HWID Lock</label>
                                        </div>
                                    </div>

                                    <!-- App Version -->
                                    <div class="form-group" title="String version which an application uploads and is checked for accuracy serverside."
                                        data-toggle="tooltip"
                                        data-placement="bottom">
                                        <label for="AppVer">Application Version</label>
                                        <input id="AppVer" name="AppVer" class="form-control" type="text" value="<?= $application_version ?>">
                                    </div>

                                    <!-- Hash Check -->
                                    <div class="form-group" title="String hash which an application uploads and is checked for accuracy serverside."
                                        data-toggle="tooltip"
                                        data-placement="bottom">
                                        <div class="custom-control custom-checkbox">
                                            <input id="HashCheck" name="HashCheck" class="custom-control-input" type="checkbox" <?= $application_hashcheck == 1 ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="HashCheck">Hash Check</label>
                                        </div>
                                    </div>

                                    <!-- App Hash -->
                                    <div class="form-group" title="Hash which an application uploads (presumably from a client-side function) and is checked for accuracy serverside."
                                        data-toggle="tooltip"
                                        data-placement="bottom">
                                        <label for="AppHash">Application Hash</label>
                                        <input id="AppHash" name="AppHash" class="form-control" type="text" value="<?= $application_hash ?>">
                                    </div>
                                </div>
                                <!-- End Card Body -->

                                <!-- Card Footer -->
                                <footer class="card-footer border-0">
                                    <button type="submit" name="UpdateAppData" class="btn btn-link font-weight-semi-bold p-0">Update Application Data</button>
                                </footer>
                                <!-- End Card Footer -->
                            </form>
                        </div>
                        <!-- End Application Data -->
                    </div>
                </div>
            </div>
            <!-- End Content Body -->

            <!-- Footer -->
            <footer class="u-footer d-md-flex align-items-md-center text-center text-md-right text-muted">
                <!-- Copyright -->
                <span class="text-muted ml-auto">&copy; AGPL 2022-2024 <a class="text-muted" href="https://github.com/UntitledEntity/intertwined-web/blob/main/LICENSE" target="_blank">Intertwined</a>. All Rights Reserved.</span>
                <!-- End Copyright -->
            </footer>
            <!-- End Footer -->
        </div>
        <!-- End Content -->
    </main>
    <!-- End Main -->

    <!-- Global Vendor -->
    <script src="../misc/assets/vendor/jquery/dist/jquery.min.js"></script>
    <script src="../misc/assets/vendor/jquery-migrate/jquery-migrate.min.js"></script>
    <script src="../misc/assets/vendor/popper.js/dist/umd/popper.min.js"></script>
    <script src="../misc/assets/vendor/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- Plugins -->
    <script src="../misc/assets/vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="../misc/assets/vendor/chart.js/dist/Chart.min.js"></script>
    <script src="../misc/assets/vendor/chartjs-plugin-style/dist/chartjs-plugin-style.min.js"></script>

    <!-- Initialization  -->
    <script src="../misc/assets/js/sidebar-nav.js"></script>
    <script src="../misc/assets/js/main.js"></script>

    <script src="../misc/assets/js/charts/area-chart.js"></script>
    <script src="../misc/assets/js/charts/area-chart-small.js"></script>
    <script src="../misc/assets/js/charts/doughnut-chart.js"></script>
</body>
<!-- End Body -->

</html>