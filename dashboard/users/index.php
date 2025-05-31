<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

$filter = sanitize($_POST['Filter']);
$filter_val = $filter ?? "";

if (isset($_POST['ban_user'])) {
    global $mysql_link;

    $user = sanitize($_POST['ban_user']);
    mysqli_query($mysql_link, "UPDATE application_users SET banned = 1 WHERE username = '$user' and application = '$appid'");
}

if (isset($_POST['unban_user'])) {
    global $mysql_link;

    $user = sanitize($_POST['unban_user']);
    mysqli_query($mysql_link, "UPDATE application_users SET banned = 0 WHERE username = '$user' and application = '$appid'");
}

if (isset($_POST['change_user_level'])) {
    global $mysql_link;

    $user = sanitize($_POST['user']);
    $new_level = sanitize($_POST['new_level']);
    mysqli_query($mysql_link, "UPDATE application_users SET level = $new_level WHERE username = '$user' and application = '$appid'");
}

if (isset($_POST['reset_ip'])) {

    $user = sanitize($_POST['reset_ip']);
    mysqli_query($mysql_link, "UPDATE application_users SET ip = NULL WHERE username = '$user' and application = '$appid'");
}

if (isset($_POST['reset_hwid'])) {

    $user = sanitize($_POST['reset_ip']);
    mysqli_query($mysql_link, "UPDATE application_users SET hwid = NULL WHERE username = '$user' and application = '$appid'");
}

if (isset($_POST['reset_password'])) {
    $password = password_hash(sanitize($_POST['pass']), PASSWORD_BCRYPT);
    $user = sanitize($_POST['user']);

    mysqli_query($mysql_link, "UPDATE application_users SET password = '$password' WHERE username = '$user' and application = '$appid'");
}

if (isset($_POST['reset_user'])) {
    $user = sanitize($_POST['user']);
    $new_user = sanitize($_POST['new_user']);

    mysqli_query($mysql_link, "UPDATE application_users SET username = '$new_user' WHERE username = '$user' and application = '$appid'");
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


    <script>
        function ChangeLevel(id) {
            var newLevel = prompt("Enter the new level:");
            if (newLevel !== null && !isNaN(newLevel)) {
                // Submit the form with AJAX
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        // Reload the page or update the table as needed
                        location.reload();
                    }
                };

                xhr.send("change_user_level=true&user=" + id + "&new_level=" + newLevel);
            }
        }

        function ChangeUser(id) {
            var newUser = prompt("Enter the new user:");
            if (newUser !== null && isNaN(newUser)) {
                // Submit the form with AJAX
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        // Reload the page or update the table as needed
                        location.reload();
                    }
                };

                xhr.send("reset_user=true&user=" + id + "&new_user=" + newUser);
            }
        }

        function ChangePassword(id) {
            var newPassword = prompt("Enter the new password:");
            if (newPassword !== null && isNaN(newPassword)) {
                // Submit the form with AJAX
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        // Reload the page or update the table as needed
                        location.reload();
                    }
                };

                xhr.send("reset_password=true&user=" + id + "&pass=" + newPassword);
            }
        }
    </script>

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
                            <a class="u-sidebar-nav-menu__link" href="../application/">
                                <span class="ti-dashboard u-sidebar-nav-menu__item-icon"></span>
                                <span class="u-sidebar-nav-menu__item-title">Application</span>
                            </a>
                        </li>
                        <!-- End Application -->

                        <!-- Users -->
                        <li class="u-sidebar-nav-menu__item">
                            <a class="u-sidebar-nav-menu__link active" href="../users">
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
                <!-- Card -->
                <div class="card mb-5">
                    <!-- Crad Body -->
                    <div class="card-body pt-0">
                        <!-- Card Header -->
                        <header class="card-header">
                            <!-- Filter Form-->
                            <form method="post">
                                <input id="Filter" name="Filter" class="form-control" type="text" placeholder="Filter" value="<?= $filter_val ?>">
                            </form>
                            <!-- End Filter Form-->
                        </header>
                        <!-- End Card Header -->

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Level</th>
                                        <th>IP</th>
                                        <th>Expires</th>
                                        <th>Last Login</th>
                                        <th>Banned</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    $usersPerPage = 8;

                                    $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                    $start = ($page - 1) * $usersPerPage;

                                    $response = mysqli_query($mysql_link, "SELECT * FROM `application_users` WHERE application = '$appid' LIMIT $start, $usersPerPage");

                                    if ($filter) {
                                        $response = mysqli_query($mysql_link, "SELECT * FROM `application_users` WHERE application = '$appid' and username LIKE '%{$filter}%' LIMIT $start, $usersPerPage");
                                    }

                                    while ($rows = mysqli_fetch_array($response)) {
                                        echo "
                                                    <tr>
                                                        <th>" . $rows['username'] . " </th>
                                                        <th>" . $rows['level'] . " </th>
                                                        <th> <blur>" . $rows['ip'] . "</blur> </th>
                                                        <th>" . gmdate("F j, Y, g:i a", $rows['expires']) . " </th>
                                                        <th>" . gmdate("F j, Y, g:i a", $rows['lastlogin']) . " </th>
                                                        <th>" . $rows['banned'] . " </th>
                                                        <td class='text-center'>
                                                            <!-- Actions -->
                                                            <div class='dropdown'>
                                                                <!-- Actions Invoker -->
                                                                <a id='basicTable2MenuInvoker' class='u-icon-sm link-muted' href='#' role='button' aria-haspopup='true' aria-expanded='false'
                                                                    data-toggle='dropdown'
                                                                    data-offset='8'>
                                                                    <span class='ti-more'></span>
                                                                </a>
                                                                <!-- End Actions Invoker -->

                                                                <!-- Actions Menu -->
                                                                <div class='dropdown-menu dropdown-menu-right' style='width: 150px;'>
                                                                    <div class='card border-0 p-3'>
                                                                        <ul class='list-unstyled mb-0'>
                                                                            <form method='post'>
                                                                                <li class='mb-3'>
                                                                                    <button class='d-block link-dark btn btn-link p-0' type='submit' name='ban_user' value='" . $rows['username'] . "'>Ban</button>
                                                                                </li>
                                                                                <li class='mb-3'>
                                                                                    <button class='d-block link-dark btn btn-link p-0' type='submit' name='unban_user' value='" . $rows['username'] . "'>UnBan</button>
                                                                                </li>
                                                                                <li class='mb-3'>
                                                                                    <button class='d-block link-dark btn btn-link p-0' type='submit' name='reset_hwid' value='" . $rows['username'] . "'>Reset HWID</button>
                                                                                </li>
                                                                                <li class='mb-3'>
                                                                                    <button class='d-block link-dark btn btn-link p-0' name='reset_ip' value='" . $rows['username'] . "'>Reset IP</button>
                                                                                </li>
                                                                                <li class='mb-3'>
                                                                                    <button class='d-block link-dark btn btn-link p-0' type='submit' name='reset_hwid' value='" . $rows['username'] . "'>Reset HWID</button>
                                                                                </li>
                                                                                <li class='mb-3'>
                                                                                    <button class='d-block link-dark btn btn-link p-0' onclick='ChangeLevel(\"" . $rows['username'] . "\")'>Change Level</button>
                                                                                </li>
                                                                                <li class='mb-3'>
                                                                                    <button class='d-block link-dark btn btn-link p-0' onclick='ChangeUser(\"" . $rows['username'] . "\")'>Change Username</button>
                                                                                </li>
                                                                                <li class='mb-3'>
                                                                                    <button class='d-block link-dark btn btn-link p-0' onclick='ChangePassword(\"" . $rows['username'] . "\")'>Change Password</button>
                                                                                </li>
                                                                            </form>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                                <!-- End Actions Menu -->
                                                            </div>
                                                            <!-- End Actions -->
                                                        </td>
                                                    </tr>
                                                ";
                                    };

                                    $total_users_query = mysqli_query($mysql_link, "SELECT COUNT(*) AS total FROM `application_users` WHERE application = '$appid'");
                                    $total_users = mysqli_fetch_assoc($total_users_query)['total'];
                                    $total_pages = ceil($total_users / $usersPerPage);

                                    $prev_page = $page - 1;
                                    $next_page = $page + 1;
                                    $prev_button_enabled = $prev_page > 0 ? "" : "disabled";
                                    echo "
                                    
                                    </tbody>
                            </table>
                            <!-- End Table -->
                        </div>
                        ";
                                    if ($total_pages > 1) {
                                        echo "
                            <!-- Pager -->
                            <footer class='card-footer'>
                                <nav aria-label='Users Table Pager'>
                                    <ul class='pager justify-content-between'>
                                            <li class='pager-item pager-item-prev'>
                                            <a class='pager-link " . $prev_button_enabled . "' href='?page=$prev_page' aria-label='Previous'>
                                                <span class='ti-angle-left mr-2'></span>
                                                Previous
                                            </a>
                                        </li>
                                        <li class='pager-item'>
                                            $page of $total_pages
                                        </li>
                                        <li class='pager-item pager-item-next'>
                                            <a class='pager-link' href='?page=$next_page' aria-label='Next'>
                                                Next
                                                <span class='ti-angle-right ml-2'></span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </footer>
                        <!-- End Pager -->";
                                    }
                                    ?>
                    </div>
                    <!-- Crad Body -->
                </div>
                <!-- End Card -->

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