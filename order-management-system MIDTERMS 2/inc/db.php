<?php
// inc/db.php
require_once __DIR__.'/config.php';
$mysqli = new mysqli('localhost', 'root', '', 'oms_db');
if ($mysqli->connect_errno) {
    http_response_code(500);
    die("DB Connect failed: ".$mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');
