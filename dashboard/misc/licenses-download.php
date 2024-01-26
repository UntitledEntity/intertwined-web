<?php

require '../../includes/mysql_connect.php';
include '../../includes/include_all.php';

if (!isset($_SESSION["user_data"]) || $_SESSION["user_data"]["ip"] != $ip)
{
	session_destroy();
	unset($_SESSION["user_data"]);
	header("location: ../");
	die();
}

$appinfo = get_application($_SESSION["user_data"]["user"]);
if ($appinfo == 'no_application')
{
	create_application($_SESSION["user_data"]["user"]);
	header('Location: '.$_SERVER['REQUEST_URI']);
}

$appid = $appinfo['appid'];

$export  = mysqli_query($mysql_link, "SELECT * FROM licenses WHERE application = '$appid'");

$data = "";

// Column names
$column_names = array('expires', 'level', 'applied', 'banned', 'ip', 'hwid', 'usedate', 'lastlogin', 'applieduser', 'created', 'createdby', 'license', 'application');

// Add column names to the CS
$data .= implode(',', $column_names) . PHP_EOL;

while( $row = mysqli_fetch_assoc($export))
{
    $line = '';
    foreach( $row as $key => $value )
    {      
        if (in_array($key, array('expires', 'usedate', 'lastlogin', 'created')) && isset($value)) {
            // Convert timestamp to human-readable time string
            $value = date('Y-m-d H:i:s', $value);
        }                                      
        if ( !isset($value) || $value == "" )
        {
            $value = "NULL";
        }
        else
        {
            $value = str_replace('"', '""', $value);
            $value = '"' . $value . '"';
        }
        $line .= $value . ",";
    }
    $data .= rtrim($line, ",") . PHP_EOL;
}

if ($data == "")
{
    $data = "(0) Records Found!" . PHP_EOL;
}

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=licenses.csv");
header("Pragma: no-cache");
header("Expires: 0");
echo $data;
exit;
?>
