<?php

// Intertwined web encrypted API v0.1.0
// TODO: Test this with the same encryption class in the TCPServer proj

function encrypt($in,$key) {
    return bin2hex(openssl_encrypt($in, 'aes256', $key, OPENSSL_RAW_DATA));
}
function decrypt($in,$key) {
    return openssl_decrypt(hex2bin($in), 'aes256', $key, OPENSSL_RAW_DATA);
}

die ((encrypt("test", "test1")));
?>