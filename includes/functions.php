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

function error($msg)
{
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
}

function notif($msg)
{
    echo '<script type=\'text/javascript\'>
                            
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

function warning($msg)
{
    echo '<script type=\'text/javascript\'>
                            
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
}

function log_msg($file, $msg)
{
  $log_file = fopen("../logs/all.log", "a");
  fwrite($log_file, "$msg\n");
  fclose($log_file);
    
  $log_file = fopen("../logs/$file", "a");
  fwrite($log_file, "$msg\n");
  fclose($log_file);
}

// global ip variable
$ip = fetch_ip();

function fetch_ip()
{
  return $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
}



?>