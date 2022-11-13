<?php

if (empty($_POST) && empty($_GET)) {
  die(header("location: https://intertwined.solutions/docs"));  
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

        if (!isset($appid))
        {
           die(json_encode(array(
            "success" => false,
            "error" => "No application id was sent."
            )));
        }

        $sessionid = open_session($appid);
        if ($sessionid === false)
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

        $resp = check_session_open($sessionid);
        if ($resp === false || !isset($resp))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid session."
            )));
        }


        $session_data = json_decode($resp);

        $user = sanitize($_POST['user'] ?? $_GET['user']);
        $pass = sanitize($_POST['pass'] ?? $_GET['pass']);
        $appid = $session_data->appid;

        if (!check_app_enabled($appid)) 
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Application disabled."
            )));
        }


        $login_resp = login_application($appid, $user, $pass);
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

        $resp = check_session_open($sessionid);
        if ($resp === false || !isset($resp))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid session."
            )));
        }


        $session_data = json_decode($resp);

        $user = sanitize($_POST['user'] ?? $_GET['user']);
        $pass = sanitize($_POST['pass'] ?? $_GET['pass']);
        $license = sanitize($_POST['license'] ?? $_GET['license']);
        $appid = $session_data->appid;


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

        $resp = check_session_open($sessionid);
        if ($resp === false || !isset($resp))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid session."
            )));
        }

        $session_data = json_decode($resp);

        $user = sanitize($_POST['user'] ?? $_GET['user']);
        $license = sanitize($_POST['license'] ?? $_GET['license']);
        $appid = $session_data->appid;

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

        $resp = check_session_open($sessionid);
        if ($resp === false || !isset($resp))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid session."
            )));
        }

        $webhookid = sanitize($_POST['whid'] ?? $_GET['whid']);
        $params = sanitize($_POST['params'] ?? $_GET['params']);

        $baselink = get_webhook($webhookid);

        $url = $baselink .= $params;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        curl_close($ch);

        die(json_encode(array(
            "success" => true,
            "response" => $response
        )));

    case 'close':
        $sessionid = sanitize($_POST['sid'] ?? $_GET['sid']);
        
        $resp = check_session_open($sessionid);
        if ($resp === false || !isset($resp))
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
