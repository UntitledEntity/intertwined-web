<?php

$mysql_link = mysqli_connect("localhost", "root", "password", "main");

if ($mysql_link == false)
{
    $errorfile = fopen("err.txt", "w");
    fwrite($errorfile, mysqli_connect_error());
}

$timestamp = time();

$sessions_to_del = mysqli_query($mysql_link, "SELECT * FROM sessions WHERE opentime + 86400 < $timestamp");

$logfile = fopen("/var/www/html/clearsessions.log", "a");
fwrite($logfile, "Deleting sessions: $sessions_to_del\n");

// delete any session older than 24 hours
mysqli_query($mysql_link, "DELETE FROM sessions WHERE opentime + 86400 < $timestamp");

mysqli_close($mysql_link);

?>