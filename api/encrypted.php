<?php

session_start();

require '../includes/mysql_connect.php';
include '../includes/include_all.php';

// Intertwined web encrypted API v0.2.2
// TODO: Documentation

function encrypt($in, $key, $iv) {
    $keyhash = substr(hash('sha256', $key), 0, 32);
    $ivhash = substr(hash('sha256', $iv), 0, 16);
    return bin2hex(openssl_encrypt($in, 'aes-256-cbc', $keyhash, OPENSSL_RAW_DATA, $ivhash));
}
function decrypt($in, $key, $iv) {
    $keyhash = substr(hash('sha256', $key), 0, 32);
    $ivhash = substr(hash('sha256', $iv), 0, 16);
    return openssl_decrypt(hex2bin($in), 'aes-256-cbc', $keyhash, OPENSSL_RAW_DATA, $ivhash);
}


$IV = hex2bin(sanitize($_POST['iv'] ?? $_GET['iv']));
if (!isset($IV))
{
    die(json_encode(array(
        "success" => false,
        "error" => "No IV was sent."
    )));
}

function die_with_header($str, $enckey) {

    // Get the IV
    global $IV;

    // Salt the return hash with the IV
    $rethash = hash_hmac('sha256', $IV . "." . $str, $enckey);

    // Set the header
    // CURLOPT_HEADER or whatever itterates through every header so we could just chcek if buf.substr(0, 10) == 'returnhash'
    header("returnhash: $rethash");

    die($str);
}

// Do this globally so we don't have to do it in every single case
$enckey = "";
$appid = "";
if ((isset($_POST['sid']) || isset($_GET['sid'])) ||
 (($_POST['type'] ?? $_GET['type']) != bin2hex('init') && ($_POST['type'] ?? $_GET['type']) != bin2hex('close'))) {
    
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
    $enckey = get_application_params($appid)['enckey'];

    if (!check_app_enabled($appid)) 
    {
        die_with_header(encrypt(json_encode(array(
           "success" => false,
            "error" => "Application disabled."
        )), $enckey, $IV), $enckey);
    }
}

switch ($_POST['type'] ?? $_GET['type'])
{
    case bin2hex('init'):
        // App ID shouldn't be encrypted, just sent in hex format
        $appid = hex2bin(sanitize($_POST['appid'] ?? $_GET['appid']));

        if (!isset($appid))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "No application id was sent."
            )));
        }

        if (!check_app_enabled($appid)) 
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Application disabled."
            )));
        }

        $enckey = get_application_params($appid)['enckey'];

        $hash = decrypt(sanitize($_POST['hash'] ?? $_GET['hash']), $enckey, $IV);
        if (!verify_hash($appid, $hash))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Invalid hash."
            )));
        }

        $sessionid = open_session($appid);
        if ($sessionid == false)
        {
            die(encrypt(json_encode(array(
            "success" => false,
            "error" => "Unable to open session."
            )), $enckey, $IV));
        }

        die_with_header(encrypt(json_encode(array(
            "success" => true,
            "sessionid" => $sessionid
        )), $enckey, $IV), $enckey);

    case bin2hex('login'):
    
        $user = decrypt(sanitize($_POST['user'] ?? $_GET['user']), $enckey, $IV);
        $pass = decrypt(sanitize($_POST['pass'] ?? $_GET['pass']), $enckey, $IV);
        $hwid = decrypt(sanitize($_POST['hwid'] ?? $_GET['hwid']), $enckey, $IV);

        
        $login_resp = login_application($appid, $user, $pass, $hwid);
        if (!is_array($login_resp))
        { 
            die_with_header(encrypt(json_encode(array(
                "success" => false,
                "error" => $login_resp
            )), $enckey, $IV), $enckey);
        }

        validate_session($sessionid);

        die_with_header(encrypt(json_encode(array(
            "success" => true,
            "data" => $login_resp
        )), $enckey, $IV), $enckey);


    case bin2hex('loginlicense'):

        $license = decrypt(sanitize($_POST['license'] ?? $_GET['license']), $enckey, $IV);
        $hwid = decrypt(sanitize($_POST['hwid'] ?? $_GET['hwid']), $enckey, $IV);
        
        $login_resp = check_application_license($license, $appid, $hwid);
        if (!is_array($login_resp))
        { 
            die_with_header(encrypt(json_encode(array(
                "success" => false,
                "error" => $login_resp
            )), $enckey, $IV), $enckey);
        }

        validate_session($sessionid);

        die_with_header(encrypt(json_encode(array(
            "success" => true,
            "data" => $login_resp
        )), $enckey, $IV), $enckey);

    case bin2hex('register'):
    
        $user = decrypt(sanitize($_POST['user'] ?? $_GET['user']), $enckey, $IV);
        $pass = decrypt(sanitize($_POST['pass'] ?? $_GET['pass']), $enckey, $IV);
        $license = decrypt(sanitize($_POST['license'] ?? $_GET['license']), $enckey, $IV);
    
            
        $register_resp = register_application($appid, $user, $pass, $license);
        if ($register_resp !== 'success')
        { 
            die_with_header(encrypt(json_encode(array(
                "success" => false,
                "error" => $register_resp
            )), $enckey, $IV), $enckey);
        }
        
        $userdata = login_application($appid, $user, $pass);

        validate_session($sessionid);
        
        die_with_header(encrypt(json_encode(array(
            "success" => true,
            "data" => $userdata
        )), $enckey, $IV), $enckey);

    case bin2hex('upgrade'):
    
        $user = decrypt(sanitize($_POST['user'] ?? $_GET['user']), $enckey, $IV);
        $license = decrypt(sanitize($_POST['license'] ?? $_GET['license']), $enckey, $IV);
    
        $upgrade_resp = upgrade_application($appid, $user, $license);
        if (!is_array($upgrade)) 
        {
            die_with_header(json_encode(array(
                "success" => false,
                "error" => $upgrade
            )), $enckey);
        }
        
        die_with_header(encrypt(json_encode(array(
            "success" => true,
            "upgrade_data" => $upgrade_resp
        )), $enckey, $IV), $enckey);

    case bin2hex('webhook'):
        
        if (get_application_params($appid)['authlock'] && !check_session_valid($sessionid))
        {
            die_with_header(json_encode(array(
                "success" => false,
                "error" => "Session is not authenticated."
            )), $enckey);
        }

        $webhookid = decrypt(sanitize($_POST['whid'] ?? $_GET['whid']), $enckey, $IV);

        $link = get_webhook($webhookid, $session_data['appid']);

        $response = request($link);

        // Prevent returning server IP
        if (strstr($response, $_SERVER['SERVER_ADDR'])) {
            blacklist(get_app_owner($appid), $ip, $appid);
            die_with_header(json_encode(array(
                "success" => false,
                "error" => "Response contains data which should not be returned."
            )), $enckey);
        }

        if (sanitize($_POST['raw'] ?? $_GET['raw'])) {
            header('Content-type: text/plain'); // Preserve newlines when returning 
            die_with_header(encrypt($response, $enckey, $IV), $enckey);
        }
        else {
            die_with_header(encrypt(json_encode(array(
                "success" => true,
                "response" => $response
            )), $enckey, $IV), $enckey);
        }

  case bin2hex('check_validity'):
    
        $sessionid = hex2bin(sanitize($_POST['sid'] ?? $_GET['sid']));
    
        $valid = check_session_valid($sessionid);
        if ($valid == -1 || !isset($sessionid))
        {
            die_with_header(json_encode(array(
                "success" => false,
                "error" => "Incorrect session ID."
            )), $enckey);
        }

        die_with_header(encrypt(json_encode(array(
            "success" => true,
            "validity" => $valid
        )), $enckey, $IV), $enckey);

    case bin2hex('close'):
    
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
        $enckey = get_application_params($appid)['enckey'];

        $resp = close_session($sessionid);
        
        if ($resp !== 'deleted')
        { 
            die_with_header(encrypt(json_encode(array(
                "success" => false,
                "error" => $resp
            )), $enckey, $IV), $enckey);
        }

        die_with_header(encrypt(json_encode(array(
            "success" => true,
            "message" => "successfully closed session"
        )), $enckey, $IV), $enckey);
    
    default:
      die("Invalid");
}

?>