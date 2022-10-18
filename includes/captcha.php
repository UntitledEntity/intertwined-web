<?php

session_start();

include 'functions.php';

// Generate a random string
$captcha = randomstring();
$_SESSION["captcha"] = $captcha;


$im = imagecreatetruecolor(80, 30);

// Blue color
$bg = imagecolorallocate($im, 22, 86, 165);
$textclr = imagecolorallocate($im, 255, 255, 255);

// write the captcha string and background in the image
imagefill($im, 0, 0, $bg);
imagestring($im, rand(1, 7), rand(1, 14), rand(1, 7), $captcha, $textclr);

// header
header("Cache-Control: no-store, no-cache, must-revalidate");
header('Content-type: image/png');

// output and clean image
imagepng($im);
imagedestroy($im);

?>
