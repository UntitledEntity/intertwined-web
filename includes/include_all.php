<?php

include 'functions.php';
include 'misc/applications.php';
include 'misc/licenses.php';
include 'misc/sessions.php';
include 'misc/users.php';
include 'misc/webhooks.php';

function error_handler($errno, $errstr, $errfile, $errline)
{
  admin_log("[" . $errno . "]" . $errstr . ". Located at line " . $errline . " in " . $errfile, LOG_ERRR);
}

set_error_handler("error_handler");

define("NOTIF_ERR", 0);
define("NOTIF_OK", 1);
define("NOTIF_WARN", 2);

define("LOG_USR", 0);
define("LOG_RGSTR", 1);
define("LOG_ALL", 2);
define("LOG_ERRR", 3);

?>