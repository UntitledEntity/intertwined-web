<?php


function login($email,$pass)
{
    // get the mysql_link
    global $mysql_link;

    // get the ip
    global $ip;

    // sanitize
    $email = sha1(sanitize($email));
    $pass = sanitize($pass);

    // find user
    $result = mysqli_query($mysql_link, "SELECT * FROM users WHERE email = '$email'");
    
    // unable to find user
    if (mysqli_num_rows($result) == 0)
    {
        return 'user_not_found';
    }

    // get user data
    while ($row = mysqli_fetch_array($result))
    {
        $pw = $row['password'];
        $user = $row['username'];
        $level = $row['level'];
        $banned = $row['banned'];
        $expiry = $row['expires'];
    }
    
    // check blacklist
    $result = mysqli_query($mysql_link, "SELECT * FROM blacklists WHERE ip = '$ip'");
    if (mysqli_num_rows($result) >= 1)
    {
        // ban the user if the ip is blacklisted
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

function register($user, $email, $pass, $license)
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

    // check if it's a valid email
    $email = sanitize($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        return 'invalid_email';
    }

    $email = sha1($email);

    // check if there's an existing user before inserting one
    $result = mysqli_query($mysql_link, "SELECT * FROM users WHERE username = '$user'");
    if (mysqli_num_rows($result) >= 1)
    {
        return 'user_already_taken';
    }

    // check if there's an existing email before inserting one
    $result = mysqli_query($mysql_link, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($result) >= 1)
    {
        return 'email_already_taken';
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

    $resp = mysqli_query($mysql_link, "INSERT INTO users (username, email, password, expires, level, ip, lastlogin) VALUES ('$user', '$email', '$hashed_pass', '$expiry', '$level', '$ip', '$timestamp')");
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

    create_application($user);

    return 'success';
}

function blacklist($user, $ip, $appid = NULL)
{
    // get the mysql_link
    global $mysql_link;

    // sanitize
    $ip = sanitize($ip);

    // check if there's a blacklist on the ip we're registering from
    $result = mysqli_query($mysql_link, "SELECT * FROM blacklists WHERE ip = '$ip'");
    if (mysqli_num_rows($result) >= 1)
    {
        return 'blacklisted';
    }

    $resp = mysqli_query($mysql_link, "INSERT INTO blacklists (ip, application) VALUES ('$ip', '$appid')");

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

function check_blacklist($ip = NULL)
{
    // get the mysql_link
    global $mysql_link;

    $ip = sanitize($ip);

    // check blacklist
    $result = mysqli_query($mysql_link, "SELECT * FROM blacklists WHERE ip = '$ip' and application is NULL");
    if (mysqli_num_rows($result) >= 1)
    {
        // ban the user if the ip is blacklisted
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

function check_email_valid($email) 
{
    global $mysql_link;

    $email = sanitize($email);
    if (!isset($email)) 
    {
        return 0;
    }

    $email = sha1($email);

    $result = mysqli_query($mysql_link, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($result) < 1)
    {
        return 0;
    }

    return 1;
}

function check_auth_code($code) 
{
    global $mysql_link;

    $code = sanitize($code);
    if (!isset($code)) 
    {
        return 0;
    }

    $result = mysqli_query($mysql_link, "SELECT * FROM users WHERE resetcode = '$code'");
    if (mysqli_num_rows($result) < 1)
    {
        return 0;
    }

    // Codes last for 15 minutes
    if ((time() - mysqli_fetch_array($result)['lastreset']) > (15 * 60))
    {
        return 0;
    }

    return 1;
}

function reset_password($code, $newpass)
{
    global $mysql_link;

    $code = sanitize($code);
    $newpass = sanitize($newpass);
    if (!isset($code) || !isset($newpass))
    {
        return 0;
    }

    $result = mysqli_query($mysql_link, "SELECT * FROM users WHERE resetcode = '$code'");
    if (mysqli_num_rows($result) < 1)
    {
        return 0;
    }

    $username = mysqli_fetch_array($result)['username'];
    $hashed_pass = password_hash($newpass, PASSWORD_BCRYPT);

    return mysqli_query($mysql_link, "UPDATE users SET password = '$hashed_pass' WHERE username = '$username'");
}

function set_auth_code($email, $resetcode) 
{
    global $mysql_link;

    $email = sanitize($email);
    $resetcode = sanitize($resetcode);
    if (!isset($email) || !isset($resetcode)) 
    {
        return 0;
    }

    $email = sha1($email);

    $result = mysqli_query($mysql_link, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($result) < 1)
    {
        return 0;
    }

    $username = mysqli_fetch_array($result)['username'];
    $timestamp = time();
    
    return mysqli_query($mysql_link, "UPDATE users SET resetcode = '$resetcode', lastreset = '$timestamp' WHERE username = '$username'");
}

function get_user_from_email($email) {
    global $mysql_link;

    $email = sanitize($email);
    if (!isset($email)) 
    {
        return 0;
    }

    $email = sha1($email);

    $result = mysqli_query($mysql_link, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($result) < 1)
    {
        return 0;
    }

    return mysqli_fetch_array($result)['username'];
}

?>