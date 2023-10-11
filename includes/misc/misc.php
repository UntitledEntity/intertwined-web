<?php

function create_variable($appid, $value) {

    global $mysql_link;

    $appid = sanitize($appid);
    $value = sanitize($value);    
    
    $check_for_app = mysqli_query($mysql_link, "SELECT * from user_applications WHERE appid = '$appid'");
    if (mysqli_num_rows($check_for_app) == 0) 
        return 'invalid_appid';

    if (!isset($value))
        return 'no_value';

    $var_id = randomstring();
    $creation_result = mysqli_query($mysql_link, "INSERT INTO variables (id, value, appid) VALUES ('$var_id', '$value', '$appid')");
    if (!$creation_result)
        return 'bad_mysql';

    return $var_id;
}

function delete_variable($appid, $var_id) {

    global $mysql_link;

    $appid = sanitize($appid);
    $var_id = sanitize($var_id);    
    
    $check_for_app = mysqli_query($mysql_link, "SELECT * from user_applications WHERE appid = '$appid'");
    if (mysqli_num_rows($check_for_app) == 0) 
        return 'invalid_appid';

    if (!isset($value))
        return 'no_value';

    $var_id = randomstring();
    $creation_result = mysqli_query($mysql_link, "DELETE from variables WHERE appid = '$appid' and id = '$var_id'");
    if (!$creation_result)
        return 'bad_mysql';

    return $var_id;
}

function get_var($var_id, $appid) {

    global $mysql_link;

    $appid = sanitize($appid);
    $var_id = sanitize($var_id);    
    
    $check_for_app = mysqli_query($mysql_link, "SELECT * from user_applications WHERE appid = '$appid'");
    if (mysqli_num_rows($check_for_app) == 0) 
        return 'invalid_appid';

    if (!isset($var_id))
        return 'no_var_id';

    $get_var = mysqli_query($mysql_link, "SELECT * from variables WHERE appid = '$appid' and id = '$var_id'");
    if (mysqli_num_rows($get_var) == 0)
        return 'bad_mysql';

    return mysqli_fetch_assoc($get_var)['value'];
}

?>