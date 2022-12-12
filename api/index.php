<?php

if (empty($_POST) && empty($_GET)) {
  die(header("location: https://docs.intertwined.solutions"));  
}

session_start();

require '../includes/mysql_connect.php';
include '../includes/include_all.php';


switch ($_POST['type'] ?? $_GET['type'])
{
    case 'init':

        $appid = sanitize($_POST['appid'] ?? $_GET['appid']);

        if (!check_app_enabled($appid)) 
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Application disabled."
            )));
        }

        $hash = sanitize($_POST['hash'] ?? $_GET['hash']);
        if (!verify_hash($appid, $hash))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid hash."
            )));
        }

        if (!isset($appid))
        {
           die(json_encode(array(
            "success" => false,
            "error" => "No application id was sent."
            )));
        }

        $sessionid = open_session($appid);
        if ($sessionid == false)
        {
            die(json_encode(array(
            "success" => false,
            "error" => "Unable to open session."
            )));
        }

        die(json_encode(array(
            "success" => true,
            "sessionid" => $sessionid
        )));

    case 'login':
        $sessionid = sanitize($_POST['sid'] ?? $_GET['sid']);

        $session_data = check_session_open($sessionid);
        if ($session_data == false || !isset($session_data))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid session."
            )));
        }

        $hash = sanitize($_POST['hash'] ?? $_GET['hash']);
        if (!verify_hash($appid, $hash))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid hash."
            )));
        }

        $user = sanitize($_POST['user'] ?? $_GET['user']);
        $pass = sanitize($_POST['pass'] ?? $_GET['pass']);
        $hwid = sanitize($_POST['hwid'] ?? $_GET['hwid']);

        $appid = $session_data['appid'];

        if (!check_app_enabled($appid)) 
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Application disabled."
            )));
        }


        $login_resp = login_application($appid, $user, $pass, $hwid);
        if (!is_array($login_resp))
        {
            die(json_encode(array(
                "success" => false,
                "error" => $login_resp
            )));
        }

        validate_session($sessionid);

        die(json_encode(array(
            "success" => true,
            "data" => $login_resp
        )));


    case 'register':

        $sessionid = sanitize($_POST['sid'] ?? $_GET['sid']);

        $session_data = check_session_open($sessionid);
        if ($session_data == false || !isset($session_data))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid session."
            )));
        }

        $hash = sanitize($_POST['hash'] ?? $_GET['hash']);
        if (!verify_hash($appid, $hash))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid hash."
            )));
        }

        $user = sanitize($_POST['user'] ?? $_GET['user']);
        $pass = sanitize($_POST['pass'] ?? $_GET['pass']);
        $license = sanitize($_POST['license'] ?? $_GET['license']);
        $appid = $session_data['appid'];


        if (!check_app_enabled($appid)) 
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Application disabled."
            )));
        }


        $register_resp = register_application($appid, $user, $pass, $license);
        if ($register_resp !== 'success')
        {
            die(json_encode(array(
                "success" => false,
                "error" => $register_resp
            )));
        }

        $userdata = login_application($appid, $user, $pass);

        validate_session($sessionid);

        die(json_encode(array(
            "success" => true,
            "data" => $userdata
        )));

    case 'upgrade': 
        $sessionid = sanitize($_POST['sid'] ?? $_GET['sid']);

        $session_data = check_session_open($sessionid);
        if ($session_data == false || !isset($session_data))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid session."
            )));
        }

        $hash = sanitize($_POST['hash'] ?? $_GET['hash']);
        if (!verify_hash($appid, $hash))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid hash."
            )));
        }

        $user = sanitize($_POST['user'] ?? $_GET['user']);
        $license = sanitize($_POST['license'] ?? $_GET['license']);
        $appid = $session_data['appid'];

        $upgrade = upgrade_application($appid, $user, $license);
        if (!is_array($upgrade)) 
        {
            die(json_encode(array(
                "success" => false,
                "error" => $upgrade
            )));
        }

        die(json_encode(array(
            "success" => true,
            "upgrade_data" => $upgrade
        )));


    case 'webhook':
        $sessionid = sanitize($_POST['sid'] ?? $_GET['sid']);

        $session_data = check_session_open($sessionid);
        if ($session_data == false || !isset($session_data))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid session."
            )));
        }

        $hash = sanitize($_POST['hash'] ?? $_GET['hash']);
        if (!verify_hash($appid, $hash))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid hash."
            )));
        }

        $webhookid = sanitize($_POST['whid'] ?? $_GET['whid']);

        $link = get_webhook($webhookid);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        curl_close($ch);

        die(json_encode(array(
            "success" => true,
            "response" => $response
        )));

    case 'check_validity':
        $sessionid = sanitize($_POST['sid'] ?? $_GET['sid']);

        $valid = check_session_valid($sessionid);
        if ($valid == -1 || !isset($sessionid))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid session."
            )));
        }

        die(json_encode(array(
            "success" => true,
            "validity" => $valid
        )));

    case 'close':
        $sessionid = sanitize($_POST['sid'] ?? $_GET['sid']);
        
        $session_data = check_session_open($sessionid);
        if ($session_data == false || !isset($session_data))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid session."
            )));
        }

        $resp = close_session($sessionid);

        if ($resp !== 'deleted')
        { 
            die(json_encode(array(
            "success" => false,
            "error" => $resp
            )));
        }

        die(json_encode(array(
            "success" => true,
            "message" => "successfully closed session."
        )));

    default:
        die("Invalid type");
}

?>
