<?php


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
    if ($resp == false)
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

    // get the timestamp
    $timestamp = time();

    $resp = mysqli_query($mysql_link, "DELETE FROM sessions WHERE sessionid = '$sessionid'");

    if ($resp == false)
    {
        return mysqli_error($mysql_link);
    }

    return 'deleted';
}

function check_session_open($sessionid)
{
    // get the mysql_link
    global $mysql_link;

    // get the ip
    global $ip;

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
        $ipp = $row['ip'];
    }
    
    // Verify the person connecting is the same person
    if ($ipp != $ip) {
        return false;
    }

    return array(
        "appid" => $appid,
        "opentime" => $opentime
    );
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

    // check session exists
    $result = mysqli_query($mysql_link, "SELECT * FROM sessions WHERE sessionid = '$sessionid'");
    if (mysqli_num_rows($result) < 1)
    {  
        return -1;
    }

    // check session validity
    $result = mysqli_query($mysql_link, "SELECT * FROM sessions WHERE sessionid = '$sessionid' AND validated = '1'");  

    return $result;
}

?>