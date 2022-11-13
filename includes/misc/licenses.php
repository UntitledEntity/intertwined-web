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
    if ($resp === false)
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

?>