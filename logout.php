<?php
require_once __DIR__ . '/_config.php';
require_once __DIR__ . '/db.php';
session_start();
$_SESSION = [];
session_destroy();
header('Location: '.BASE_PATH.'/index.php');
exit;
?>