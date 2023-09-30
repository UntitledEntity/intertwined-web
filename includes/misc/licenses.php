<?php

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
    if ($resp == false)
    {
        return mysqli_error($mysql_link);
    }

    return $generated_license;
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
        case 'week':
            $expiry_time = strtotime("+1 week", time());
            break;
        case '2week':
            $expiry_time = strtotime("+2 week", time());
            break;
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
 
    $resp = mysqli_query($mysql_link, "INSERT INTO licenses (license, expires, level, created, application) VALUES ('$generated_license', '$expiry_time', '$level', '$timestamp', '$appid')");
    if ($resp == false)
    {
        return mysqli_error($mysql_link);
    }
 
    return $generated_license;
}

function get_license($user)
{
    // get the mysql_link
    global $mysql_link;

    $user = sanitize($user);
    
    $result = mysqli_query($mysql_link, "SELECT * FROM licenses WHERE applieduser = '$user'");
    if ($result == false)
    {
        return mysqli_error($mysql_link);
    }

    if (mysqli_num_rows($result) < 1)
    {
        return 'invalid_user';
    }

    return mysqli_fetch_array($result);
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

    return mysqli_fetch_array($result);
}

function get_user_license($license, $appid) {
    // get the mysql_link
    global $mysql_link;

    $license = sanitize($license);
    $appid = sanitize($appid);

    $result = mysqli_query($mysql_link, "SELECT * FROM licenses WHERE application = '$appid' and license = '$license'");
    if ($result == false)
    {
        return mysqli_error($mysql_link);
    }
  
    if (mysqli_num_rows($result) < 1)
    {
        return 'invalid_license';
    }

    $user = mysqli_fetch_array($result)['applieduser'];

    if (!isset($user))
        return 'no_user';

    return mysqli_fetch_array($result)['applieduser'];
}

function check_application_license($license, $appid, $hwid = NULL) {
    // get the mysql_link
    global $mysql_link;

    // get the ip
    global $ip;

    $license = sanitize($license);
    $appid = sanitize($appid);

    // get application paramaters
    $appparams = get_application_params($appid);

    $result = mysqli_query($mysql_link, "SELECT * FROM licenses WHERE application = '$appid' and license = '$license'");
    if ($result == false)
    {
        return mysqli_error($mysql_link);
    }
  
    if (mysqli_num_rows($result) < 1)
    {
        return 'invalid_license';
    }

    while ($row = mysqli_fetch_array($result))
    {
        $expiry = $row['expires'];
        $ipp = $row['ip'];
        $hwidd = $row['hwid'];
        $level = $row['level'];
        $banned = $row['banned'];
    }

    if ($banned) {
        blacklist(NULL, $ip);
        return 'banned';
    }

    if ($appparams['hwidlock'] && $hwid != $hwidd) {
        // no stored hwid, set the current one.
        if (is_null($hwidd) || $hwidd == 0) 
        {
            $hashed_hwid = password_hash($hwid, PASSWORD_BCRYPT);
            mysqli_query($mysql_link, "UPDATE licenses SET hwid = '$hashed_hwid' WHERE application = '$appid' and license = '$license'");
        }
        else if (password_verify($hwid, $hwidd) == false)
        {
            return 'hwid_mismatch';
        }
    }

    if ($appparams['iplock'] && $ip != $ipp) {
        return 'invalid_ip';
    }

    $timestamp =  time();

    if ($expiry <= $timestamp){
        return 'license_expired';
    }

    mysqli_query($mysql_link, "UPDATE licenses SET ip = '$ip' WHERE application = '$appid' and license = '$license'");
    mysqli_query($mysql_link, "UPDATE licenses SET lastlogin = '$timestamp' WHERE application = '$appid' and license = '$license'");

    return array(
        "license" => $license,
        "expiry" => intval($expiry),
        "ip" => $ip,
        "level" => intval($level)
    );

}

?>