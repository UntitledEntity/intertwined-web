<?php

header("x-xss-protection: 1; mode=block");
header("strict-transport-security: max-age=31536000; includeSubDomains; preload");
header("Permissions-Policy: interest-cohort=()");
header("x-content-type-options: nosniff");
header("x-frame-options: DENY");
header("Referrer-Policy: no-referrer");

$mysql_link = mysqli_connect("localhost", "root", "password", "main");

if ($mysql_link == false)
{
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

?>
