<?php
include "config.php";

$headers = getallheaders();

if (!isset($headers['x-api-key'])) {
    echo "API_KEY_MISSING";
    exit;
}

if ($headers['x-api-key'] !== API_KEY) {
    echo "API_KEY_INVALID";
    exit;
}
?>
