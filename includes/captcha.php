<?php

session_start();

include 'functions.php';

// Generate a random string
$captcha = randomstring();
$_SESSION["captcha"] = $captcha;


$im = imagecreatetruecolor(80, 20);

// colors
$bg = imagecolorallocate($im, 25, 40, 65);
$border = imagecolorallocate($im, 255, 64, 0);
$textclr = imagecolorallocate($im, 255, 255, 255);

// write the captcha string and background in the image
imagefilltoborder($im, 0, 0, $border, $bg);
imagestring($im, rand(1, 7), rand(1, 12), rand(1, 7), $captcha, $textclr);

// header
header("Cache-Control: no-store, no-cache, must-revalidate");
header('Content-type: image/png');

// output and clean image
imagepng($im);
imagedestroy($im);

?>