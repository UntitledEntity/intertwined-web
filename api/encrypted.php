<?php

// Intertwined web encrypted API v0.1.0
// TODO: Work on this more.

function encrypt($in, $key, $iv) {
    return bin2hex(openssl_encrypt($in, 'aes-256-cbc', hash('sha256', $key), OPENSSL_RAW_DATA, hash('sha256', $iv)));
}

?>