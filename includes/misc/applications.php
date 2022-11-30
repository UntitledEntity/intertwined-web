<?php


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
    if ($resp == false)
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

function check_ban_application($appid, $user)
{
    // get the mysql_link
    global $mysql_link;

    // sanitize
    $user = sanitize($user);
    $appid = sanitize($appid);

    // find user
    $result = mysqli_query($mysql_link, "SELECT * FROM application_users WHERE username = '$user' and application = '$appid'");
    
    // unable to find user
    if (mysqli_num_rows($result) == 0)
    {
        return -1;
    }

    $banned = mysqli_fetch_array($result)['banned'];

    return $banned;
}

function verify_hash($appid, $hash) 
{
    // get the mysql_link
    global $mysql_link;

    // sanitize
    $hash = sanitize($hash);
    $appid = sanitize($appid);

    // get application
    $result = mysqli_query($mysql_link, "SELECT * FROM user_applications WHERE appid = '$appid'");

    // unable to find application
    if (mysqli_num_rows($result) == 0)
    {
        return -1;
    }

    $application_data = mysqli_fetch_array($result);
    
    return $application_data['hashcheck'] == false || $application_data['hash'] === $hash;
}

function login_application($appid, $user, $pass, $hwid = NULL)
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
    if (mysqli_num_rows($result) == 0)
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
        $ipp = $row['ip'];
    }
    
    if ($ip != $ipp && get_application_params($appid)['iplock'] == true)
    {
        return 'invalid_ip';
    }

    // check blacklist
    $result = mysqli_query($mysql_link, "SELECT * FROM blacklists WHERE ip = '$ip' or hwid='$hwidd' and application = '$appid'");
    if (mysqli_num_rows($result) >= 1)
    {
        // ban the user if the hwid or ip is blacklisted
        // they already should be, but just in case
        mysqli_query($mysql_link, "UPDATE application_users SET banned = '1' WHERE username = '$user' and application = '$appid'");
        return 'blacklisted';
    }

    // banned
    if ($banned == true)
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
    if (password_verify($pass, $pw) == false)
    {
        return 'password_mismatch';
    }

    // check if HWID matches
    // use the password_verify function because hwids are stored with BCrypt hashing
    if (isset($hwid))
    {
        // no stored hwid, set the current one.
        if (is_null($hwidd) || $hwidd == 0) 
        {
            $hashed_hwid = password_hash($hwid, PASSWORD_BCRYPT);
            mysqli_query($mysql_link, "UPDATE application_users SET hwid = '$hashed_hwid' WHERE username = '$user' and application = '$appid'");
        }
        else if (password_verify($hwid, $hwidd) == false)
        {
            return 'hwid_mismatch';
        }
    }

    // update last login time
    $timestamp = time();
    mysqli_query($mysql_link, "UPDATE application_users SET lastlogin = '$timestamp' WHERE username = '$user' and application = '$appid'");
    
    // update the ip
    mysqli_query($mysql_link, "UPDATE application_users SET ip = '$ip' WHERE username = '$user' and application = '$appid'");
    
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

    if ($pass == $user || strlen($pass) < 4)
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
    if ($resp == false)
    {
        return mysqli_error($mysql_link);
    }
    
    $resp = mysqli_query($mysql_link, "UPDATE licenses SET applied = '1', usedate = '$timestamp', applieduser = '$user' WHERE license = '$license' and application = '$appid'");
    if ($resp == false)
    {
        return mysqli_error($mysql_link);
    }

    return 'success';
}

function upgrade_application($appid, $user, $license) 
{
    // get the mysql_link
    global $mysql_link;

    // get the ip
    global $ip;

    // sanitize
    $user = sanitize($user);
    $license = sanitize($license);
    $appid = sanitize($appid);

    // check if there's a user
    $result = mysqli_query($mysql_link, "SELECT * FROM application_users WHERE username = '$user' and application = '$appid'");
    if (mysqli_num_rows($result) < 1)
    {
        return 'user_not_found';
    }

    $userlevel = mysqli_fetch_array($result)['level'];
    
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

    if ($level < 1 || $userlevel > $level)
    {
        return 'invalid_level';
    }

    $resp = mysqli_query($mysql_link, "UPDATE application_users SET level = '$level', expires = '$expiry' WHERE username = '$user' and application = '$appid'");
    if ($resp == false)
    {
        return mysqli_error($mysql_link);
    }

    $timestamp = time();
    $resp = mysqli_query($mysql_link, "UPDATE licenses SET applied = '1', usedate = '$timestamp', applieduser = '$user' WHERE license = '$license'");
    if ($resp == false)
    {
        return mysqli_error($mysql_link);
    }

    return array(
        "level" => $level,
        "expiry" => $expiry
    );
}

function get_application_params($appid) 
{
    // get the mysql_link
    global $mysql_link;

    $appid = sanitize($appid);

    $result = mysqli_query($mysql_link, "SELECT * FROM user_applications WHERE appid = '$appid'");
    if (mysqli_num_rows($result) < 1)
    {
        return 'invalid_appid';
    }

    return mysqli_fetch_array($result);
}

function get_application($user)
{
    // get the mysql_link
    global $mysql_link;

    $user = sanitize($user);  
    
    $result = mysqli_query($mysql_link, "SELECT * FROM users WHERE username = '$user'");
    if (mysqli_num_rows($result) < 1)
    {
        return 'invalid_user';
    }

    // check application
    $result = mysqli_query($mysql_link, "SELECT * FROM user_applications WHERE owner = '$user'");
    if (mysqli_num_rows($result) < 1)
    {  
        return 'no_application';
    }
    
    // get session data
    while ($row = mysqli_fetch_array($result))
    {
        $appid = $row['appid'];
        $enckey = $row['enckey'];
        $enabled = $row['enabled'];
    }
    
    return array(
        "appid" => $appid,
        "enckey" => $enckey,
        "enabled" => $enabled 
    );
}

function delete_application_account($user)
{
    // get the mysql_link
    global $mysql_link;

    $user = sanitize($user);
    
    $resp = mysqli_query($mysql_link, "DELETE FROM application_users WHERE username = '$user'");

    if ($resp == false)
    {
        return mysqli_error($mysql_link);
    }

    return 'deleted';
}

?>