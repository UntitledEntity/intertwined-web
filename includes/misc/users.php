<?php


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
    if ($banned)
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
        admin_log("$user logged in.", LOG_USR);   
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
    if ($resp == false)
    {
        return mysqli_error($mysql_link);
    }
    
    $resp = mysqli_query($mysql_link, "UPDATE licenses SET applied = '1', usedate = '$timestamp', applieduser = '$user' WHERE license = '$license'");
    if ($resp == false)
    {
        return mysqli_error($mysql_link);
    }
    
    admin_log("$user registered (License: $license, IP: $ip)", LOG_RGSTR);   

    return 'success';
}

function blacklist($user, $ip, $appid = NULL)
{
    // get the mysql_link
    global $mysql_link;

    // sanitize
    $ip = sanitize($ip);
    $hwid = NULL;

    // check if there's a blacklist on the ip we're registering from
    $result = mysqli_query($mysql_link, "SELECT * FROM blacklists WHERE ip = '$ip'");
    if (mysqli_num_rows($result) >= 1)
    {
        return 'blacklisted';
    }

    $resp = mysqli_query($mysql_link, "INSERT INTO blacklists (ip, hwid, application) VALUES ('$ip', '$hwid', '$appid')");

    if ($resp == false)
    {
        return mysqli_error($mysql_link);
    }

    if (isset($user)) {
        $resp = mysqli_query($mysql_link, "UPDATE users SET banned = '1' WHERE username = '$user' OR ip = '$ip'");

        if ($resp == false)
        {
            return mysqli_error($mysql_link);
        }
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

    if ($resp == false)
    {
        return mysqli_error($mysql_link);
    }

    return 'deleted';
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
    if ($result == false)
    {
        return mysqli_error($mysql_link);
    }

    return 'success';
}

?>