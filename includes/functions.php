<?php

session_start();

require 'mysql_connect.php';

function sanitize($data)
{
    if (empty($data) && !is_numeric($data))
	{
		return NULL;
	}

    global $mysql_link;
    return mysqli_real_escape_string($mysql_link, strip_tags(trim($data)));
}

function request($link)
{
    $curl = curl_init($link);
        
    curl_setopt($curl, CURLOPT_USERAGENT, "Intertwined");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($curl);

    curl_close($curl);
    
    return $response;
}

function randomstring($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $strlength = strlen($characters);
    $ret = '';
    for ($i = 0; $i < $length; $i++) {
        $ret .= $characters[rand(0, $strlength - 1)];
    }
    return $ret;
}

function error($msg)
{
echo '
                <script type=\'text/javascript\'>
                
                const notyf = new Notyf();
                notyf
                  .error({
                    message: \'' . addslashes($msg) . '\',
                    duration: 3500,
                    dismissible: true
                  });                
                
                </script>
                ';
}

function notif($msg)
{
    echo '<script type=\'text/javascript\'>
                            
                            const notyf = new Notyf();
                            notyf
                              .success({
                                message: \'' . addslashes($msg) . '\',
                                duration: 3500,
                                dismissible: true
                              });                
                            
                            </script>
                            ';     
}

function warning($msg)
{
    echo '<script type=\'text/javascript\'>
                            
const notyf = new Notyf({
  types: [
    {
      type: \'info\',
      background: \'orange\',
      icon: false
    }
  ]
});
                               notyf.open({
                                    type: \'info\',
                                    message: \'' . addslashes($msg) . '\',
                               });
                            
                            </script>
                            ';     
}

function log_msg($file, $msg)
{
    $log_file = fopen("../logs/all.log", "a");
    fwrite($log_file, "$msg\n");
    fclose($log_file);
    
    $log_file = fopen($file, "a");
    fwrite($log_file, "$msg\n");
    fclose($log_file);
}

// global ip variable
$ip = fetch_ip();

function fetch_ip()
{
    return $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
}

#region website DB
function login($user,$pass)
{
    // get the mysql_link
    global $mysql_link;

    // get the ip
    global $ip;

    // sanitize
    $user = sanitize($user);
    $pass = sanitize($pass);
    
    // find user
    $result = mysqli_query($mysql_link, "SELECT * FROM users WHERE username = '$user'");
    
    // unable to find user
    if (mysqli_num_rows($result) === 0)
    {
        return 'user_not_found';
    }

    // get user data
    while ($row = mysqli_fetch_array($result))
    {
        $pw = $row['password'];
        $hwidd = $row['hwid'];
        $level = $row['level'];
        $banned = $row['banned'];
        $expiry = $row['expires'];
    }
    
    // check blacklist
    $result = mysqli_query($mysql_link, "SELECT * FROM blacklists WHERE ip = '$ip' or hwid='$hwidd'");
    if (mysqli_num_rows($result) >= 1)
    {
        // ban the user if the hwid or ip is blacklisted
        // they already should be, but just in case
        mysqli_query($mysql_link, "UPDATE users SET banned = '1' WHERE username = '$user'");
        return 'blacklisted';
    }

    // banned
    if ($banned === 1)
    {
        blacklist($user, $ip);
        return 'banned';
    }

    // check if the user's sub is expired
    if ($expiry <= time())
    {
        return 'subscription_expired';
    }

    // check if pass matches
    if (password_verify($pass, $pw) === false)
    {
        return 'password_mismatch';
    }
    
    unset($_SESSION["user_data"]);
    $_SESSION["user_data"] = array(
        "user" => $user,
        "expiry" => $expiry,
        "level" => intval($level),
        "ip" => $ip
    );
    
    // update last login time
    $timestamp = time();
    mysqli_query($mysql_link, "UPDATE users SET lastlogin = '$timestamp' WHERE username = '$user'");
    
    // update the ip
    mysqli_query($mysql_link, "UPDATE users SET ip = '$ip' WHERE username = '$user'");


    if ($user !== "basic_example")
    {
        log_msg("../logs/logins.log", "$user logged in with the IP $ip");   
    }
    
    return 'success';
}

function register($user, $pass, $license)
{
    // get the mysql_link
    global $mysql_link;

    // get the ip
    global $ip;

    // sanitize
    $user = sanitize($user);
    $pass = sanitize($pass);
    if ($pass === $user || strlen($pass) < 4)
    {
        return 'invalid_pass';
    }
        
    $license = sanitize($license);

    // check if there's an existing user before inserting one
    $result = mysqli_query($mysql_link, "SELECT * FROM users WHERE username = '$user'");
    if (mysqli_num_rows($result) >= 1)
    {
        return 'user_already_taken';
    }

    // check if there's a blacklist on the ip we're registering from
    $result = mysqli_query($mysql_link, "SELECT * FROM blacklists WHERE ip = '$ip'");
    if (mysqli_num_rows($result) >= 1)
    {
        return 'blacklisted';
    }

    // get license info
    $result = mysqli_query($mysql_link, "SELECT * FROM licenses WHERE license = '$license' and application is NULL");
    if (mysqli_num_rows($result) < 1)
    {
        return 'invalid_license';
    }


    while ($row = mysqli_fetch_array($result))
    {
        $expiry = $row['expires'];
        $level = $row['level'];
    }
    
    // check to see if the license has already expired
    if ($expiry <= time())
    {
        return 'expired_license';
    }

    if ($level < 1)
    {
        return 'invalid_level';
    }

    // hash the password 
    $hashed_pass = password_hash($pass, PASSWORD_BCRYPT);

    $timestamp = time();

    $resp = mysqli_query($mysql_link, "INSERT INTO users (username, password, expires, level, ip, lastlogin) VALUES ('$user', '$hashed_pass', '$expiry', '$level', '$ip', '$timestamp')");
    if ($resp === false)
    {
        return mysqli_error($mysql_link);
    }
    
    $resp = mysqli_query($mysql_link, "UPDATE licenses SET applied = '1', usedate = '$timestamp', applieduser = '$user' WHERE license = '$license'");
    if ($resp === false)
    {
        return mysqli_error($mysql_link);
    }
    
    log_msg("../logs/registers.log", "$user registered (License: $license, IP: $ip)");   

    return 'success';
}

function gen_license($expiry, $level)
{
    // get the mysql_link
    global $mysql_link;

    // sanitize
    $expiry = sanitize($expiry);
    $level = sanitize($level);

    if ($level < 1)
    {
        return 'invalid_level';
    }

    // gen a license
    $generated_license = md5(rand());
    
    // check if the license we generated already exists. Statistically impossible but check anyway
    $result = mysqli_query($mysql_link, "SELECT * FROM licenses WHERE license = '$generated_license'");
    if (mysqli_num_rows($result) >= 1)
    {
        return gen_license($expiry, $level);
    }

    switch ($expiry)
    {
        case 'month':
            $expiry_time = strtotime("+1 months", time());
            break;
        case 'half-year':
            $expiry_time = strtotime('+6 months', time());
            break;
        case 'year':
            $expiry_time = strtotime('+1 years', time());
            break;
        case 'never':
            $expiry_time = strtotime('+5 years', time());
            break;
    }

    $timestamp = time();

    $resp = mysqli_query($mysql_link, "INSERT INTO licenses (license, expires, level, created) VALUES ('$generated_license', '$expiry_time', '$level', '$timestamp')");
    if ($resp === false)
    {
        return mysqli_error($mysql_link);
    }

    return $generated_license;
}

function blacklist($user, $ip, $hwid = NULL)
{
    // get the mysql_link
    global $mysql_link;

    // sanitize
    $ip = sanitize($ip);
    $hwid = sanitize($hwid);

    // check if there's a blacklist on the ip we're registering from
    $result = mysqli_query($mysql_link, "SELECT * FROM blacklists WHERE ip = '$ip'");
    if (mysqli_num_rows($result) >= 1)
    {
        return 'blacklisted';
    }

    $resp = mysqli_query($mysql_link, "INSERT INTO blacklists (user, ip, hwid) VALUES ('$user', '$ip', '$hwid')");

    if ($resp === false)
    {
        return mysqli_error($mysql_link);
    }

    $resp = mysqli_query($mysql_link, "UPDATE users SET banned = '1' WHERE username = '$user' OR ip = '$ip'");

    if ($resp === false)
    {
        return mysqli_error($mysql_link);
    }

    return 'blacklisted';
}

function check_blacklist($ip = NULL, $hwid = NULL)
{
    // get the mysql_link
    global $mysql_link;

    $ip = sanitize($ip);
    $hwid = sanitize($hwid);

    // check blacklist
    $result = mysqli_query($mysql_link, "SELECT * FROM blacklists WHERE ip = '$ip' and application is NULL");
    if (mysqli_num_rows($result) >= 1)
    {
        // ban the user if the hwid or ip is blacklisted
        // they already should be, but just in case
        mysqli_query($mysql_link, "UPDATE users SET banned = '1' WHERE ip = '$ip' and application is NULL");
        return true;
    }

    return false;
}

function delete_account($user)
{
    // get the mysql_link
    global $mysql_link;

    $user = sanitize($user);
    
    $resp = mysqli_query($mysql_link, "DELETE FROM users WHERE username = '$user'");

    if ($resp === false)
    {
        return mysqli_error($mysql_link);
    }

    return 'deleted';
}

function get_license($user)
{
    // get the mysql_link
    global $mysql_link;

    $user = sanitize($user);
    
    $result = mysqli_query($mysql_link, "SELECT * FROM licenses WHERE applieduser = '$user'");
    if ($result === false)
    {
        return mysqli_error($mysql_link);
    }

    if (mysqli_num_rows($result) < 1)
    {
        return 'invalid_user';
    }

    while ($row = mysqli_fetch_array($result))
    {
        $expiry = $row['expires'];
        $level = $row['level'];
        $license = $row['license'];
    }

    return json_encode(array(
        "expiry" => $expiry,
        "level" => $level,
        "license" => $license
    ));
}

function get_license_data($license)
{
    // get the mysql_link
    global $mysql_link;

    $license = sanitize($license);
    
    $result = mysqli_query($mysql_link, "SELECT * FROM licenses WHERE license = '$license'");
    if (mysqli_num_rows($result) < 1)
    {
        return false;
    }

    while ($row = mysqli_fetch_array($result))
    {
        $expiry = $row['expires'];
        $level = $row['level'];
        $applieduser = $row['applieduser'];
    }

    return json_encode(array(
        "expiry" => $expiry,
        "level" => intval($level),
        "applieduser" => $applieduser
    ));
}

function change_level($user, $level)
{
    global $mysql_link;

    $user = sanitize($user);
    $level = sanitize($level);

    if ($level < 1)
    {
        return 'invalid_level';
    }

    // check for user
    $result = mysqli_query($mysql_link, "SELECT * FROM users WHERE username = '$user'");
    if (mysqli_num_rows($result) < 1)
    {
        return 'invalid_user';
    }

    $result = mysqli_query($mysql_link, "UPDATE users SET level = '$level' WHERE username = '$user'");
    if ($result === false)
    {
        return mysqli_error($mysql_link);
    }

    return 'success';
}
#endregion

#region application DB
function create_application($owner)
{
    // get the mysql_link
    global $mysql_link;

    $owner = sanitize($owner);

    // generate a 8-digit, random string to be our application ID
    $appid = randomstring();

    // generate an enckey (md5 random hash)
    $enckey = md5(rand());

    $resp = mysqli_query($mysql_link, "INSERT INTO user_applications (appid, enckey, owner) VALUES ('$appid', '$enckey', '$owner')");
    if ($resp === false)
    {
        return false;
    }

    return $appid;
}

function check_app_enabled($appid) 
{
    global $mysql_link;

    $appid = sanitize($appid);

    $result = mysqli_query($mysql_link, "SELECT * FROM user_applications WHERE appid = '$appid' and enabled = 1");
    
    return mysqli_num_rows($result) > 0;
}

function login_application($appid, $user, $pass)
{
    // get the mysql_link
    global $mysql_link;

    // get the ip
    global $ip;

    // sanitize
    $user = sanitize($user);
    $pass = sanitize($pass);
    $appid = sanitize($appid);
    
    // find user
    $result = mysqli_query($mysql_link, "SELECT * FROM application_users WHERE username = '$user' and application = '$appid'");
    
    // unable to find user
    if (mysqli_num_rows($result) === 0)
    {
        return 'user_not_found';
    }

    // get user data
    while ($row = mysqli_fetch_array($result))
    {
        $pw = $row['password'];
        $hwidd = $row['hwid'];
        $level = $row['level'];
        $banned = $row['banned'];
        $expiry = $row['expires'];
    }
    
    // check blacklist
    $result = mysqli_query($mysql_link, "SELECT * FROM blacklists WHERE ip = '$ip' or hwid='$hwidd' and application = '$appid'");
    if (mysqli_num_rows($result) >= 1)
    {
        // ban the user if the hwid or ip is blacklisted
        // they already should be, but just in case
        mysqli_query($mysql_link, "UPDATE application_users SET banned = '1' WHERE username = '$user'");
        return 'blacklisted';
    }

    // banned
    if ($banned === 1)
    {
        blacklist($user, $ip);
        return 'banned';
    }

    // check if the user's sub is expired
    if ($expiry <= time())
    {
        return 'subscription_expired';
    }

    // check if pass matches
    if (password_verify($pass, $pw) === false)
    {
        return 'password_mismatch';
    }

    // update last login time
    $timestamp = time();
    mysqli_query($mysql_link, "UPDATE application_users SET lastlogin = '$timestamp' WHERE username = '$user'");
    
    // update the ip
    mysqli_query($mysql_link, "UPDATE application_users SET ip = '$ip' WHERE username = '$user'");
    
    return array(
        "user" => $user,
        "expiry" => $expiry,
        "level" => intval($level),
        "ip" => $ip
    );;
}

function register_application($appid, $user, $pass, $license)
{
    // get the mysql_link
    global $mysql_link;

    // get the ip
    global $ip;

    // sanitize
    $user = sanitize($user);
    $pass = sanitize($pass);
    $license = sanitize($license);
    $appid = sanitize($appid);

    if ($pass === $user || strlen($pass) < 4)
    {
        return 'invalid_pass';
    }
        
    
    // check if there's an existing user before inserting one
    $result = mysqli_query($mysql_link, "SELECT * FROM application_users WHERE username = '$user' and application = '$appid'");
    if (mysqli_num_rows($result) >= 1)
    {
        return 'user_already_taken';
    }

    // get license info
    $result = mysqli_query($mysql_link, "SELECT * FROM licenses WHERE license = '$license' and application = '$appid'");
    if (mysqli_num_rows($result) < 1)
    {
        return 'invalid_license';
    }


    while ($row = mysqli_fetch_array($result))
    {
        $expiry = $row['expires'];
        $level = $row['level'];
        $applieduser = $row['applieduser'];
    }
    
    if (isset($applieduser))
    {
        return 'license_already_used';
    }
    
    // check to see if the license has already expired
    if ($expiry <= time())
    {
        return 'expired_license';
    }

    if ($level < 1)
    {
        return 'invalid_level';
    }

    // hash the password 
    $hashed_pass = password_hash($pass, PASSWORD_BCRYPT);

    $timestamp = time();

    $resp = mysqli_query($mysql_link, "INSERT INTO application_users (username, password, expires, level, ip, lastlogin, application) VALUES ('$user', '$hashed_pass', '$expiry', '$level', '$ip', '$timestamp', '$appid')");
    if ($resp === false)
    {
        return mysqli_error($mysql_link);
    }
    
    $resp = mysqli_query($mysql_link, "UPDATE licenses SET applied = '1', usedate = '$timestamp', applieduser = '$user' WHERE license = '$license' and application = '$appid'");
    if ($resp === false)
    {
        return mysqli_error($mysql_link);
    }

    return 'success';
}

function generate_application_license($appid, $expiry, $level)
{
    // get the mysql_link
    global $mysql_link;


    // sanitize
    $expiry = sanitize($expiry);
    $level = sanitize($level);
    $appid = sanitize($appid);
 
    if ($level < 1)
    {
        return 'invalid_level';
    }
 
    // gen a license
    $generated_license = md5(rand());
     
    // check if the license we generated already exists. Statistically impossible but check anyway
    $result = mysqli_query($mysql_link, "SELECT * FROM licenses WHERE license = '$generated_license' and application = '$appid'");
    if (mysqli_num_rows($result) >= 1)
    {
        return generate_application_license($appid, $expiry, $level);
    }
 
    switch ($expiry)
    {
        case 'month':
            $expiry_time = strtotime("+1 months", time());
            break;
        case 'half-year':
            $expiry_time = strtotime('+6 months', time());
            break;
        case 'year':
            $expiry_time = strtotime('+1 years', time());
            break;
        case 'never':
            $expiry_time = strtotime('+3827 years', time());
            break;
    }
 
    $timestamp = time();
 
    $resp = mysqli_query($mysql_link, "INSERT INTO licenses (license, expires, level, created, application) VALUES ('$generated_license', '$expiry_time', '$level', '$timestamp', '$appid')");
    if ($resp === false)
    {
        return mysqli_error($mysql_link);
    }
 
    return $generated_license;
}

function open_session($appid)
{
    // get the mysql_link
    global $mysql_link;

    // get the ip
    global $ip;

    $appid = sanitize($appid);

    // check appid
    $result = mysqli_query($mysql_link, "SELECT * FROM user_applications WHERE appid = '$appid'");
    if (mysqli_num_rows($result) < 1)
    {  
        return false;
    }

    // generate a session id
    $sessionid = randomstring();

    // get the timestamp
    $timestamp = time();

    // open session
    $resp = mysqli_query($mysql_link, "INSERT INTO sessions (sessionid, ip, opentime, application) VALUES ('$sessionid', '$ip', '$timestamp', '$appid')");
    if ($resp === false)
    {
        return mysqli_error($mysql_link);
    }

    return $sessionid;
}

function close_session($sessionid)
{
    // get the mysql_link
    global $mysql_link;

    $sessionid = sanitize($sessionid);    
    
    $resp = mysqli_query($mysql_link, "DELETE FROM sessions WHERE sessionid = '$sessionid'");

    if ($resp === false)
    {
        return mysqli_error($mysql_link);
    }

    return 'deleted';
}

function check_session_open($sessionid)
{
    // get the mysql_link
    global $mysql_link;

    $sessionid = sanitize($sessionid);  

    // check session
    $result = mysqli_query($mysql_link, "SELECT * FROM sessions WHERE sessionid = '$sessionid'");
    if (mysqli_num_rows($result) < 1)
    {  
        return false;
    }

    // get session data
    while ($row = mysqli_fetch_array($result))
    {
        $appid = $row['application'];
        $opentime = $row['opentime'];
    }
    
    return json_encode(array(
        "appid" => $appid,
        "opentime" => $opentime
    ));
}

function validate_session($sessionid)
{
    // get the mysql_link
    global $mysql_link;

    $sessionid = sanitize($sessionid);  

    // check session
    $result = mysqli_query($mysql_link, "SELECT * FROM sessions WHERE sessionid = '$sessionid'");
    if (mysqli_num_rows($result) < 1)
    {  
        return false;
    }

    $result = mysqli_query($mysql_link, "UPDATE sessions SET validated = '1' WHERE sessionid = '$sessionid'");
    
    return $result;
}

function check_session_valid($sessionid)
{
    // get the mysql_link
    global $mysql_link;

    $sessionid = sanitize($sessionid);  

    // check session
    $result = mysqli_query($mysql_link, "SELECT * FROM sessions WHERE sessionid = '$sessionid' AND validated = '1'");  

    return $result;
}

function get_application($user)
{
    // get the mysql_link
    global $mysql_link;

    $user = sanitize($user);  
    
    // check application
    $result = mysqli_query($mysql_link, "SELECT * FROM user_applications WHERE owner = '$user'");
    if (mysqli_num_rows($result) < 1)
    {  
        return 'invalid_owner';
    }
    
    // get session data
    while ($row = mysqli_fetch_array($result))
    {
        $appid = $row['appid'];
        $enckey = $row['enckey'];
        $enabled = $row['enabled'];
    }
    
    return json_encode(array(
        "appid" => $appid,
        "enckey" => $enckey,
        "enabled" => $enabled 
    ));
}

function delete_application_account($user)
{
    // get the mysql_link
    global $mysql_link;

    $user = sanitize($user);
    
    $resp = mysqli_query($mysql_link, "DELETE FROM application_users WHERE username = '$user'");

    if ($resp === false)
    {
        return mysqli_error($mysql_link);
    }

    return 'deleted';
}

#endregion

#region webhooks

function create_webhook($webhook_link)
{
    // get the mysql_link
    global $mysql_link;

    $webhook_link = sanitize($webhook_link);

    $webhook_id = randomstring();

    $resp = mysqli_query($mysql_link, "INSERT INTO webhooks (id, link) VALUES ('$webhook_id', '$webhook_link')");

    if ($resp === false)
    {
        return mysqli_error($mysql_link);
    }

    return $webhook_id;
}

function get_webhook($webhook_id)
{
    // get the mysql_link
    global $mysql_link;

    $webhook_id = sanitize($webhook_id);  
     
    // check application
    $result = mysqli_query($mysql_link, "SELECT * FROM webhooks WHERE id = '$webhook_id'");
    if (mysqli_num_rows($result) < 1)
    {  
       return 'invalid_id';
    }
     
    // get session data
    while ($row = mysqli_fetch_array($result))
    {
       $webhook_link = $row['link'];
    }
    
    return $webhook_link;
}

function delete_webhook($webhook_id)
{
    // get the mysql_link
    global $mysql_link;

    $webhook_id = sanitize($webhook_id);  
     
    $result = mysqli_query($mysql_link, "DELETE FROM webhooks WHERE id = '$webhook_id'");
    if ($result === false)
    {
        return mysqli_error($mysql_link);
    }

    return 'deleted';
}

#endregion

?>