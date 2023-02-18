<?php

session_start();

require '../../includes/mysql_connect.php';
include '../../includes/include_all.php';

$license = sanitize($_GET['license']);

if (!isset($license))
{
    die("Missing license");
}

$license_data = get_license_data($license);

if (!$license_data)
{
    die("Invalid license.");
}

$application = get_application($license_data['applieduser']);

switch ($_GET['type'])
{
    case 'setstatus':
        $appid = $application['appid'];

        $input = sanitize($_GET['status']);
        if (!isset($input)) 
        {
            die(json_encode(array(
                "status" => "ERR",
                "response" => "Critical error: Parameter 'status' was not provided."
            )));
        }

        if (!is_numeric($input) || ($input < 0 || $input > 1)) {
            die(json_encode(array(
                "status" => "ERR",
                "response" => "Critical error: Parameter 'status' must be 1 or 0."
            )));
        }
        
        mysqli_query($mysql_link, "UPDATE user_applications SET enabled = $input WHERE appid = '$appid'");

        die(json_encode(array(
            "status" => "OK",
            "response" => "Successfully updated status."
        )));

    case 'getusers':
        $appid = $application['appid'];

        // find user
        $result = mysqli_query($mysql_link, "SELECT * FROM application_users WHERE application = '$appid'");

        // unable to find user
        if (mysqli_num_rows($result) == 0)
        {
            die(json_encode(array(
                "status" => "ERR",
                "response" => "Critical error: No users in application."
            )));
        }

        $rows = array();
        while ($r = mysqli_fetch_assoc($result)) {
            $rows[] = $r;
        }


        die(json_encode(array(
            "status" => "OK",
            "response" => $rows
        )));

    case 'deleteuser':
        $user = sanitize($_GET['user']);
        if (!isset($user))
        {
            die(json_encode(array(
                "status" => "ERR",
                "response" => "Critical error: Parameter 'user' was not provided."
            )));
        }

        $resp = delete_application_account($user, $appid);

        die(json_encode(array(
            "status" => "OK",
            "response" => $resp
        )));

    case 'genlicense':
        $appid = $application['appid'];

        $expiry = sanitize($_GET['expiry']);
        $level = sanitize($_GET['level']);

        $amount = sanitize($_GET['amount']);
        if (!isset($amount) || $amount < 0)
        {
            $amount = 1;
        }

        if (!is_numeric($amount))
        {
            die(json_encode(array(
                "status" => "ERR",
                "response" => "Critical error: 'Amount' Parameter must be numeric, please provide a numeric format."
            )));
        }

        if ($amount > 100)
        {
            die(json_encode(array(
                "status" => "ERR",
                "response" => "Critical error: Maximum amounts of licenses generated at once is 100."
            )));
        }

        $keys = array();
        for ($x = 1; $x <= $amount; $x++)
        {
            $key = generate_application_license($appid, $expiry, $amount);
            array_push($keys, $key);
        }

        die(json_encode(array(
            "status" => "OK",
            "response" => $keys
        )));

    case 'userdata':
        $appid = $application['appid'];

        $user = sanitize($_GET['user']);
        if (!isset($user))
        {
            die(json_encode(array(
                "status" => "ERR",
                "response" => "Critical error: Parameter 'user' was not provided."
            )));
        }

        // find user
        $result = mysqli_query($mysql_link, "SELECT * FROM application_users WHERE username = '$user' and application = '$appid'");

        // unable to find user
        if (mysqli_num_rows($result) == 0)
        {
            die("User not found");
        }

        $rows = array();
        while ($r = mysqli_fetch_assoc($result)) {
            $rows[] = $r;
        }

        die(json_encode(array(
            "status" => "OK",
            "response" => $rows
        )));

    case 'closesession':
        $appid = $application['appid'];

        $sid = sanitize($_GET['sessionid']);
        if (!isset($sid))
        {
            die(json_encode(array(
                "status" => "ERR",
                "response" => "Critical error: Parameter 'sessionid' was not provided."
            )));
        }

        close_session($sid);

        die(json_encode(array(
            "status" => "OK",
            "response" => "Successfully closed session."
        )));

    case 'createwebhook':
        $link = sanitize($_GET['link']);
        if (!isset($link))
        {
            die(json_encode(array(
                "status" => "ERR",
                "response" => "Critical error: Parameter 'link' was not provided."
            )));
        }

        die(json_encode(array(
            "status" => "OK",
            "response" => create_webhook($link)
        )));

    case 'callwebhook':
        $whid = sanitize($_GET['id']);
        if (!isset($whid))
        {
            die(json_encode(array(
                "status" => "ERR",
                "response" => "Critical error: Parameter 'id' was not provided."
            )));
        }

        $link = get_webhook($whid);
        if (!isset($link))
        {
            die(json_encode(array(
                "status" => "ERR",
                "response" => "Critical error: Webhook not found. Please check the parameter 'id'."
            )));
        }


        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        
        if ($response === false) 
        {
            die(json_encode(array(
                "status" => "ERR",
                "response" => "Critical error: Failed to connect to the provided webhook",
                "wh" => curl_error($ch)
            )));
        }

        curl_close($ch);
        
        die(json_encode(array(
            "status" => "OK",
            "response" => "Successfully called webhook.",
            "wh" => $response
        )));

    case 'deletewebhook':
        $whid = sanitize($_GET['id']);
        if (!isset($whid))
        {
            die(json_encode(array(
                "status" => "ERR",
                "response" => "Critical error: Parameter 'id' was not provided."
            )));
        }

        die(json_encode(array(
            "status" => "OK",
            "response" => delete_webhook($whid)
        )));

    case 'getwebhook':
        $whid = sanitize($_GET['id']);
        if (!isset($whid))
        {
            die(json_encode(array(
                "status" => "ERR",
                "response" => "Critical error: Parameter 'id' was not provided."
            )));
        }

        die(json_encode(array(
            "status" => "OK",
            "response" => get_webhook($whid)
        )));

    default:
        die(json_encode(array(
                "status" => "ERR",
                "response" => "Critical error: Parameter 'type' was not provided."
    )));
}

?>
