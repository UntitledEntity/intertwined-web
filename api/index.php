<?php

if (empty($_POST) && empty($_GET)) {
  die(header("location: https://docs.intertwined.solutions"));  
}

session_start();

require '../includes/mysql_connect.php';
include '../includes/include_all.php';

// TODO: authentification lock for webhook function and file function
// TODO 2: file function

$hash = sanitize($_POST['hash'] ?? $_GET['hash']);
if (!verify_hash($appid, $hash))
{
    die(json_encode(array(
        "success" => false,
        "error" => "Invalid hash."
    )));
}

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

        $appver = get_application_params($appid)['version'];
        $version = sanitize($_POST['ver'] ?? $_GET['ver']);
        if (!isset($version) || $appver != $version) {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid version."
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
                "error" => "Incorrect session ID."
            )));
        }

        $appid = $session_data['appid'];

        $user = sanitize($_POST['user'] ?? $_GET['user']);
        $pass = sanitize($_POST['pass'] ?? $_GET['pass']);
        $hwid = sanitize($_POST['hwid'] ?? $_GET['hwid']);

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


    case 'loginlicense':

        $sessionid = sanitize($_POST['sid'] ?? $_GET['sid']);

        $session_data = check_session_open($sessionid);
        if ($session_data == false || !isset($session_data))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Incorrect session ID."
            )));
        }

        $appid = $session_data['appid'];

        if (!check_app_enabled($appid)) 
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Application disabled."
            )));
        }

        $hwid = sanitize($_POST['hwid'] ?? $_GET['hwid']);
        $license =  sanitize($_POST['license'] ?? $_GET['license']);

        $check_resp = check_application_license($license, $appid, $hwid);
        if (!is_array($check_resp))
        {
            die(json_encode(array(
                "success" => false,
                "error" => $check_resp
            )));
        }

        validate_session($sessionid);

        die(json_encode(array(
            "success" => true,
            "data" => $check_resp
        )));

    case 'register':

        $sessionid = sanitize($_POST['sid'] ?? $_GET['sid']);

        $session_data = check_session_open($sessionid);
        if ($session_data == false || !isset($session_data))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Incorrect session ID."
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
                "error" => "Incorrect session ID."
            )));
        }

        $user = sanitize($_POST['user'] ?? $_GET['user']);
        $license = sanitize($_POST['license'] ?? $_GET['license']);
        $appid = $session_data['appid'];

        if (!check_app_enabled($appid)) 
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Application disabled."
            )));
        }

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


    case 'get_var':
    
        $sessionid = hex2bin(sanitize($_POST['sid'] ?? $_GET['sid']));
    
        $session_data = check_session_open($sessionid);
        if ($session_data == false || !isset($session_data))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Incorrect session ID."
            )));
        }

        $appid = $session_data['appid'];

        if (get_application_params($appid)['authlock'] && !check_session_valid($sessionid))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Session is not authenticated."
            )));
        }

        $var_id = sanitize($_POST['var_id'] ?? $_GET['var_id']);
        if (!isset($var_id))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "No variable ID."
            )));
        }

        $var = get_var($var_id, $appid);
        if ($var == 'invalid_appid' || $var == 'no_value' || $var == 'bad_mysql') {
            die(json_encode(array(
                "success" => false,
                "error" => $var
            )));
        }
        
        die(json_encode(array(
            "success" => true,
            "var" => $var
        )));


    case 'webhook':
        $sessionid = sanitize($_POST['sid'] ?? $_GET['sid']);

        $session_data = check_session_open($sessionid);
        if ($session_data == false || !isset($session_data))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Incorrect session ID."
            )));
        }

        $appid = $session_data['appid'];

        if (!check_app_enabled($appid))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "App is disabled."
            )));
        }

        if (get_application_params($appid)['authlock'] && !check_session_valid($sessionid))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Session is not authenticated."
            )));
        }

        $webhookid = sanitize($_POST['whid'] ?? $_GET['whid']);

        $link = get_webhook($webhookid, $session_data['appid']);

        // TODO: make this a function, ugly code
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $response = curl_exec($ch);

        curl_close($ch);
        
        if (sanitize($_POST['raw'] ?? $_GET['raw'])) {
            header('Content-type: text/plain'); // Preserve newlines when returning 
            die($response);
        }
        else {
            die(json_encode(array(
                "success" => true,
                "response" => $response
            )));
        }

    case 'check_validity':
        $sessionid = sanitize($_POST['sid'] ?? $_GET['sid']);

        
        if (!check_session_valid($sessionid) || !isset($sessionid))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Incorrect session ID."
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
                "error" => "Incorrect session ID."
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
