<?php

session_start();

require '../../includes/mysql_connect.php';
include '../../includes/include_all.php';

$license = sanitize($_GET['license']);

if (!isset($license))
{
    die("Missing license");
}

$license_data = json_decode(get_license_data($license));

if (!isset($license_data))
{
    die("Invalid license.");
}

$application = get_application($license_data->applieduser);
$application_data = json_decode($application);

switch ($_GET['type'])
{
    case 'getusers':
        $appid = $application_data->appid;

        // find user
        $result = mysqli_query($mysql_link, "SELECT * FROM application_users WHERE application = '$appid'");

        // unable to find user
        if (mysqli_num_rows($result) === 0)
        {
            die("No users");
        }

        $rows = array();
        while ($r = mysqli_fetch_assoc($result)) {
            $rows[] = $r;
        }

        die(json_encode($rows));

    case 'deleteuser':
        $user = sanitize($_GET['user']);
        if (!isset($user))
        {
            die("No user");
        }

        die(delete_application_account($user));

    case 'genlicense':
        $appid = $application_data->appid;

        $expiry = sanitize($_GET['expiry']);
        $level = sanitize($_GET['level']);

        $amount = sanitize($_GET['amount']);
        if (!isset($amount))
        {
            $amount = 1;
        }

        if (!is_numeric($amount))
        {
            die("Invalid amount format (must be numeric)");
        }

        if ($amount > 100)
        {
            die("There is a max amount of 100.");
        }

        $keys = array();
        for ($x = 1; $x <= $amount; $x++)
        {
            $key = generate_application_license($appid, $expiry, $amount);
            array_push($keys, $key);
        }

        die(json_encode($keys));

    case 'userdata':
        $appid = $application_data->appid;

        $user = sanitize($_GET['user']);
        if (!isset($user))
        {
            die("No user");
        }

        // find user
        $result = mysqli_query($mysql_link, "SELECT * FROM application_users WHERE username = '$user' and application = '$appid'");

        // unable to find user
        if (mysqli_num_rows($result) === 0)
        {
            die("User not found");
        }

        $rows = array();
        while ($r = mysqli_fetch_assoc($result)) {
            $rows[] = $r;
        }

        die(json_encode($rows));

    case 'closesession':
        $appid = $application_data->appid;

        $sid = sanitize($_GET['sessionid']);
        if (!isset($sid))
        {
            die("No session id");
        }

        close_session($sid);

    case 'createwebhook':
        $link = sanitize($_GET['link']);
        if (!isset($link))
        {
            die("No link");
        }

        die(create_webhook($link));

    case 'callwebhook':
        $whid = sanitize($_GET['id']);
        if (!isset($whid))
        {
            die("No webhook id");
        }

        $link = get_webhook($whid);
        if (!isset($link))
        {
            die("Invalid webhook");
        }

        $params = sanitize($_GET['params']);

        $url = $link .= $params;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        curl_close($ch);

        die($response);

    case 'deletewebhook':
        $whid = sanitize($_GET['id']);
        if (!isset($whid))
        {
            die("No webhook id");
        }

        die(delete_webhook($whid));

    case 'getwebhook':
        $whid = sanitize($_GET['id']);
        if (!isset($whid))
        {
            die("No webhook id");
        }

        die(get_webhook($whid));

    default:
        die("Invalid type");
}

?>
