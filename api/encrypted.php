<?php

session_start();

require '../includes/mysql_connect.php';
include '../includes/include_all.php';

// Intertwined web encrypted API v0.3.1
// TODO: Documentation, Force encryption

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

// Handle rate limits. Cloudflare has got us covered for the rest of the website, 
// just want to have a second wall of defense for the API.
if (!handle_rate_limits()) {
    http_response_code(429);
    die("Rate limit exceeded. Please wait and try again.");
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

    $app_owner = get_app_owner($appid);

    $result = mysqli_query($mysql_link, "SELECT * FROM licenses WHERE applieduser = '$app_owner' AND application IS NULL");
    $app_owner_license_data = mysqli_fetch_array($result);
    if ($app_owner_license_data['banned'] == 1 || $app_owner_license_data['expires'] < time())
    {
        die_with_header(encrypt(json_encode(array(
            "success" => false,
            "error" => "Application not allowed to use API."
        )), $enckey, $IV), $enckey);
    }
}

// Clear invalid/expired sessions every time the API is accessed.
clear_invalid_sessions($appid);

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

        if (num_sessions($appid) > 1000) 
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Number of sessions exceeded, please close some and try again."
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

        $appver = get_application_params($appid)['version'];
        
        $version = decrypt(sanitize($_POST['ver'] ?? $_GET['ver']), $enckey, $IV);

        if (!isset($version) || $appver != $version) {
            die_with_header(encrypt(json_encode(array(
                "success" => false,
                "error" => "Invalid version."
            )), $enckey, $IV), $enckey);
        }

        $sessionid = open_session($appid);
        if ($sessionid == false)
        {
            die_with_header(encrypt(json_encode(array(
            "success" => false,
            "error" => "Unable to open session."
            )), $enckey, $IV), $enckey);
        }

        die_with_header(encrypt(json_encode(array(
            "success" => true,
            "sessionid" => $sessionid
        )), $enckey, $IV), $enckey);

    case bin2hex('login'):

        if (get_application_params($appid)['enabled_functions'][0] == "0") {
            die_with_header(encrypt(json_encode(array(
                "success" => false,
                "error" => "Login API function disabled."
            )), $enckey, $IV), $enckey);
        }

        $sessionid = hex2bin(sanitize($_POST['sid'] ?? $_GET['sid']));
    
        $session_data = check_session_open($sessionid);
        if ($session_data == false || !isset($session_data))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Incorrect session ID."
            )));
        }
    
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

        if (get_application_params($appid)['enabled_functions'][1] == "0") {
            die_with_header(encrypt(json_encode(array(
                "success" => false,
                "error" => "Login via license API function disabled."
            )), $enckey, $IV), $enckey);
        }

        $sessionid = hex2bin(sanitize($_POST['sid'] ?? $_GET['sid']));
    
        $session_data = check_session_open($sessionid);
        if ($session_data == false || !isset($session_data))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Incorrect session ID."
            )));
        }

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
    
        if (get_application_params($appid)['enabled_functions'][2] == "0") {
            die_with_header(encrypt(json_encode(array(
                "success" => false,
                "error" => "Register API function disabled."
            )), $enckey, $IV), $enckey);
        }

        $sessionid = hex2bin(sanitize($_POST['sid'] ?? $_GET['sid']));
    
        $session_data = check_session_open($sessionid);
        if ($session_data == false || !isset($session_data))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Incorrect session ID."
            )));
        }

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
    
        if (get_application_params($appid)['enabled_functions'][3] == "0") {
            die_with_header(encrypt(json_encode(array(
                "success" => false,
                "error" => "Upgrade API function disabled."
            )), $enckey, $IV), $enckey);
        }

        $sessionid = hex2bin(sanitize($_POST['sid'] ?? $_GET['sid']));
    
        $session_data = check_session_open($sessionid);
        if ($session_data == false || !isset($session_data))
        {
            die(json_encode(array(
                "success" => false,
                "error" => "Incorrect session ID."
            )));
        }

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
        
        if (get_application_params($appid)['enabled_functions'][4] == "0") {
            die_with_header(encrypt(json_encode(array(
                "success" => false,
                "error" => "Webhook API function disabled."
            )), $enckey, $IV), $enckey);
        }

        if (get_application_params($appid)['authlock'] && !check_session_valid($sessionid))
        {
            die_with_header(json_encode(array(
                "success" => false,
                "error" => "Session is not authenticated."
            )), $enckey);
        }

        $webhookid = decrypt(sanitize($_POST['whid'] ?? $_GET['whid']), $enckey, $IV);

        $link = get_webhook($webhookid, $session_data['appid']);
        
        ini_set('memory_limit', '-1');

        $response = bin2hex(request($link));        
        die_with_header(encrypt($response, $enckey, $IV), $enckey);

  case bin2hex('check_validity'):
    
        $sessionid = hex2bin(sanitize($_POST['sid'] ?? $_GET['sid']));
    
        if (!check_session_valid($sessionid) || !isset($sessionid))
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

    case bin2hex('get_var'):
    
        if (get_application_params($appid)['enabled_functions'][5] == "0") {
            die_with_header(encrypt(json_encode(array(
                "success" => false,
                "error" => "Variables API functions disabled."
            )), $enckey, $IV), $enckey);
        }

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

        if (get_application_params($appid)['authlock'] && !check_session_valid($sessionid))
        {
            die_with_header(json_encode(array(
                "success" => false,
                "error" => "Session is not authenticated."
            )), $enckey);
        }

        $var_id = decrypt(sanitize($_POST['var_id'] ?? $_GET['var_id']), $enckey, $IV);
        if (!isset($var_id))
        {
            die_with_header(encrypt(json_encode(array(
                "success" => false,
                "error" => "No variable ID."
            )), $enckey, $IV), $enckey);
        }

        $var = get_var($var_id, $appid);
        if ($var == 'invalid_appid' || $var == 'no_value' || $var == 'bad_mysql') {
            die_with_header(encrypt(json_encode(array(
                "success" => false,
                "error" => $var
            )), $enckey, $IV), $enckey);
        }
        
        die_with_header(encrypt(json_encode(array(
            "success" => true,
            "var" => $var
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