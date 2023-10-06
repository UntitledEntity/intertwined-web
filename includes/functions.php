<?php

session_start();

require 'mysql_connect.php';

function sanitize($data)
{
    if (empty($data) && !is_numeric($data))
	{
		return NULL;
	}

    global $mysql_link;
    return mysqli_real_escape_string($mysql_link, strip_tags(trim($data)));
}

function request($link)
{
    $curl = curl_init($link);
        
    curl_setopt($curl, CURLOPT_USERAGENT, "Intertwined");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($curl);

    curl_close($curl);
    
    return $response;
}

function randomstring($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $strlength = strlen($characters);
    $ret = '';
    for ($i = 0; $i < $length; $i++) {
        $ret .= $characters[random_int(0, $strlength - 1)];
    }
    return $ret;
}

function discord_webhook($content) 
{
  $webhookurl = "REDACTED";
  
  $json_data = json_encode([
      // Message
      "content" => $content,
      
      // Username
      "username" => "Intertwined alerts",
  
      // Text-to-speech
      "tts" => false,
  
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
  
  
  $ch = curl_init( $webhookurl );
  curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
  curl_setopt( $ch, CURLOPT_POST, 1);
  curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
  curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt( $ch, CURLOPT_HEADER, 0);
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
  
  $response = curl_exec( $ch );
  
  curl_close( $ch );
}

function notification($msg, $type) 
{
  switch($type) {

    case NOTIF_ERR:
      echo '
        <script type=\'text/javascript\'>
        
        const notyf = new Notyf();
        notyf
          .error({
            message: \'' . addslashes($msg) . '\',
            duration: 3500,
            dismissible: true
          });                
        
        </script>
      ';
      break;
    case NOTIF_WARN:
      echo '
        <script type=\'text/javascript\'>
                            
        const notyf = new Notyf({
          types: [
            {
              type: \'info\',
              background: \'orange\',
              icon: false
            }
          ]
        });

        notyf.open({
          type: \'info\',
          message: \'' . addslashes($msg) . '\',
        });
                            
        </script>
      ';     
      break;
    case NOTIF_OK:
      echo '
        <script type=\'text/javascript\'>
                            
        const notyf = new Notyf();
        notyf
          .success({
            message: \'' . addslashes($msg) . '\',
            duration: 3500,
            dismissible: true
          });                
      
        </script>
      ';     
  }
}

function admin_log($msg, $type) 
{
  $timestamp = date('Y-m-d H:i:s', time());

  $file = "";
  $output = "";

  switch($type) {
    case LOG_ERRR:
      $output = "[" . $timestamp . "] ERROR >> " . $msg;
      $file = "err.log";

      // Send a notification to a discord server. Fastest way to get the attention of the staff members. 
      // Dont send any critical information because discord isn't secure enough IMO.
      discord_webhook("[" . $timestamp . "] Error detected: $msg");
      break;

    case LOG_ALL: 
      $output = "[" . $timestamp . "] ALL >> " . $msg;
      $file = "all.log";
      break;

    case LOG_USR:
      $output = "[" . $timestamp . "] USER >> " . $msg;
      $file = "users.log";
      break;

    case LOG_RGSTR:
      $output = "[" . $timestamp . "] REGISTER >> " . $msg;
      $file = "registers.log";
      break;

    case LOG_DISC:
      discord_webhook("[" . $timestamp . "] $msg");
      break;
  }

  /*
  When moving from beta dir to main dir, remember to update the perms for the logs directory.
  chown www-data -R /var/www/html/logs 
  Thanks: https://stackoverflow.com/a/64633818
  */

  // log to all.log, this file holds all of the logs and is backed up weekly.
  $log_file = fopen("/var/www/html/logs/all.log", "a");
  fwrite($log_file, "$output\n");
  fclose($log_file);

  // log to type-specific file.
  if ($type != LOG_ALL) {
    $log_file = fopen("/var/www/html/logs/" . $file, "a");
    fwrite($log_file, "$output\n");
    fclose($log_file);
  }
    
}

// global ip variable
$ip = fetch_ip();

function fetch_ip()
{
  return $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
}



?>