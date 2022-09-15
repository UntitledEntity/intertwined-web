<?php

if (empty($_POST)) {
  die(header("location: https://intertwined.solutions/api/docs"));  
}

session_start();

require '../includes/mysql_connect.php';
include '../includes/functions.php';


switch ($_POST['type'])
{
    case 'init':

        $appid = sanitize($_POST['appid']);
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
        $sessionid = sanitize($_POST['sid']);

        $resp = check_session_open($sessionid);
        if ($resp === false || !isset($resp))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid session."
            )));
        }


        $session_data = json_decode($resp);

        $user = sanitize($_POST['user']);
        $pass = sanitize($_POST['pass']);
        $appid = $session_data->appid;


        $login_resp = login_application($appid, $user, $pass);
        if ($login_resp !== 'success')
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

        $sessionid = sanitize($_POST['sid']);

        $resp = check_session_open($sessionid);
        if ($resp === false || !isset($resp))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid session."
            )));
        }


        $session_data = json_decode($resp);

        $user = sanitize($_POST['user']);
        $pass = sanitize($_POST['pass']);
        $license = sanitize($_POST['license']);
        $appid = $session_data->appid;


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

    case 'webhook':
        $sessionid = sanitize($_POST['sid']);

        $resp = check_session_open($sessionid);
        if ($resp === false || !isset($resp))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid session."
            )));
        }

        $webhookid = sanitize($_POST['whid']);
        $params = sanitize($_POST['params']);

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
        $sessionid = sanitize($_POST['sid']);
        close_session($sessionid);

        die(json_encode(array(
            "success" => true,
            "message" => "successfully closed session."
        )));

    default:
        die("Invalid type");
}

?>
