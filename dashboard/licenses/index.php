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

if (isset($_POST['DownloadLicenses'])) {
    echo "<meta http-equiv='Refresh' Content='0; url=../misc/licenses-download.php'>";
}

if (isset($_POST['GenerateLicense'])) {
    $expiry = sanitize($_POST['LicenseExpiration']);

    $level = sanitize($_POST['LicenseLevel']);
    $amount = sanitize($_POST['LicenseAmount']);

    if (!isset($level) || !isset($amount)) {
        notification("One or more fields are missing.", NOTIF_ERR);
        return;
    }

    if (!is_numeric($level) || $level < 1) {
        notification("Level must be numeric", NOTIF_ERR);
        return;
    }

    if (!is_numeric($amount) || $amount < 1 || $amount > 50) {
        notification("Invalid amount.", NOTIF_ERR);
        return;
    }

    if ($_POST['format'] == "json") {
        $keys = array();
        for ($x = 1; $x <= $amount; $x++) {
            $key = generate_application_license($appid, $expiry, $level);
            array_push($keys, $key);
        }

        $keys = json_encode($keys);
    } else {
        $keys = "";
        for ($x = 1; $x <= $amount; $x++) {
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

if (isset($_POST['DeleteUnused'])) {
    mysqli_query($mysql_link, "DELETE FROM licenses WHERE applied is null AND application = '$appid'");
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
                xhr.onreadystatechange = function() {
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
                            <a class="u-sidebar-nav-menu__link" href="../users">
                                <span class="ti-user u-sidebar-nav-menu__item-icon"></span>
                                <span class="u-sidebar-nav-menu__item-title">Users</span>
                            </a>
                        </li>
                        <!-- End Users -->

                        <!-- Licenses -->
                        <li class="u-sidebar-nav-menu__item">
                            <a class="u-sidebar-nav-menu__link active" href="../licenses">
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

                    <div class="col-md-3 mb-5 mb-md-0">
                        <!-- Application Data -->
                        <div class="card h-100">
                            <!-- Card Header -->
                            <header class="card-header d-flex align-items-center justify-content-between">
                                <h2 class="h4 card-header-title">Generate Licenses</h2>
                            </header>
                            <!-- End Card Header -->

                            <form method="POST" action="">
                                <!-- Card Body -->
                                <div class="card-body py-0">

                                    <!-- License Level -->
                                    <div class="form-group">
                                        <label for="LicenseLevel">Level</label>
                                        <input id="LicenseLevel" name="LicenseLevel" class="form-control" type="text">
                                    </div>

                                    <!-- License Level -->
                                    <div class="form-group">
                                        <label for="LicenseExpiration">Expiration</label>
                                        <select id="LicenseExpiration" name="LicenseExpiration" class="form-control">
                                            <option value="week">1 week</option>
                                            <option value="2week">2 weeks</option>
                                            <option value="month">1 month</option>
                                            <option value="half-year">6 months</option>
                                            <option value="year">1 year</option>
                                            <option value="never">Never</option>
                                        </select>
                                    </div>

                                    <!-- License Amount -->
                                    <div class="form-group">
                                        <label for="LicenseAmount">Amount</label>
                                        <input id="LicenseAmount" name="LicenseAmount" class="form-control" type="text">
                                    </div>

                                    <!-- Output Format -->
                                    <div class="form-group">
                                        <label for="Format">Format</label>
                                        <select id="Format" name="Format" class="form-control">
                                            <option value="text">Text</option>
                                            <option value="json">JSON</option>
                                        </select>
                                    </div>

                                    <button type="submit" name="GenerateLicense" class="btn btn-outline-primary text-uppercase mb-2 mr-2">Generate</button>
                                    <button type="submit" name="DownloadLicenses" class="btn btn-outline-info text-uppercase mb-2 mr-2">Download Licenses</button>
                                    <button type="submit" name="DeleteUnused" class="btn btn-outline-danger text-uppercase mb-2 mr-2">Delete Unused Licenses</button>

                                </div>
                                <!-- End Card Body -->
                            </form>
                        </div>
                        <!-- End Application Data -->
                    </div>

                    <div class="col-md-9 mb-5 mb-md-0">
                        <!-- Licenses -->
                        <div class="card h-100">
                            <!-- Card Header -->
                            <header class="card-header d-flex align-items-center justify-content-between">
                                <h2 class="h4 card-header-title">Licenses</h2>
                            </header>
                            <!-- End Card Header -->

                            <!-- Crad Body -->
                            <div class="card-body pt-0">
                                <!-- Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>License</th>
                                                <th>Expires</th>
                                                <th>Level</th>
                                                <th>Banned</th>
                                                <th>Applied</th>
                                                <th>User</th>
                                                <th>Use date</th>
                                                <th>Created</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php
                                            $licensesPerPage = 4;

                                            $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                            $start = ($page - 1) * $licensesPerPage;

                                            $response = mysqli_query($mysql_link, "SELECT * FROM `licenses` WHERE application = '$appid' LIMIT $start, $licensesPerPage");

                                            while ($rows = mysqli_fetch_array($response)) {
                                                echo "
                                                    <tr>
                                                        <th>" . $rows['license'] . " </th>
                                                        <th>" . gmdate("F j, Y, g:i a", $rows['expires']) . " </th>
                                                        <th>" . $rows['level'] . " </th>
                                                        <th>" . $rows['banned'] . " </th>
                                                        <th>" . $rows['applied'] . " </th>
                                                        <th>" . $rows['applieduser'] . " </th>
                                                        <th>" . gmdate("F j, Y, g:i a", $rows['usedate']) . " </th>
                                                        <th>" . gmdate("F j, Y, g:i a", $rows['created']) . " </th>
                                            
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
                                                                                    <button class='d-block link-dark btn btn-link p-0' onclick='DeleteLicense(\"" . $rows['license'] . "\")'>Delete License</button>
                                                                                </li>
                                                                                <li class='mb-3'>
                                                                                    <button class='d-block link-dark btn btn-link p-0' onclick='ChangeLevel(\"" . $rows['license'] . "\")'>Change Level</button>
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

                                            $total_licenses_query = mysqli_query($mysql_link, "SELECT COUNT(*) AS total FROM `licenses` WHERE application = '$appid'");
                                            $total_licenses = mysqli_fetch_assoc($total_licenses_query)['total'];
                                            $total_pages = ceil($total_licenses / $licensesPerPage);

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
                            <!-- End Licenses -->
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