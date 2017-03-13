<?php


require_once('config.lib.php');
require_once('mail.lib.php');
require_once('ldap.lib.php');
require_once('server.lib.php');
require_once('db.lib.php');

set_exception_handler('sp_exception_handler');
function sp_exception_handler($exception) {
    show_error("Uncaught exception: " , $exception->getMessage());
}

/****** OUTPUT functions *****/
function show_header($text) {
    echo '<h1>'.$text.'</h1>';
}

function show_error($text) {
    echo '<div style="color:red;">'.$text.'</div>';
}

function show_warning($text) {
    echo '<div style="color:#CAC004;">'.$text.'</div>';
}

function show_success($text) {
    echo '<div style="color:green;">'.$text.'</div>';
}

function show_object($object) {
    echo '<div style="height: 80px; overflow:scroll;"><pre>';
    print_r($object);
    echo '</pre></div>';
}
