<?php


function create_webhook($webhook_link, $appid)
{
    // get the mysql_link
    global $mysql_link;

    $webhook_link = sanitize($webhook_link);

    $webhook_id = randomstring();

    $resp = mysqli_query($mysql_link, "INSERT INTO webhooks (id, link, appid) VALUES ('$webhook_id', '$webhook_link', '$appid')");

    if ($resp == false)
    {
        return mysqli_error($mysql_link);
    }

    return $webhook_id;
}

function get_webhook($webhook_id, $appid)
{
    // get the mysql_link
    global $mysql_link;

    $webhook_id = sanitize($webhook_id);  
     
    // check application
    $result = mysqli_query($mysql_link, "SELECT * FROM webhooks WHERE id = '$webhook_id'and appid = '$appid'");
    if (mysqli_num_rows($result) < 1)
    {  
       return 'invalid_id';
    }
     
    // get session data
    $webhook_link = mysqli_fetch_array($result)['link'];
    return $webhook_link;
}

function delete_webhook($webhook_id, $appid)
{
    // get the mysql_link
    global $mysql_link;

    $webhook_id = sanitize($webhook_id);  
     
    $result = mysqli_query($mysql_link, "DELETE FROM webhooks WHERE id = '$webhook_id' and appid = '$appid'");
    if ($result == false)
    {
        return mysqli_error($mysql_link);
    }

    return 'deleted';
}

?>