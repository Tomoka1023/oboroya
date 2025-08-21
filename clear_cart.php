<?php
require_once __DIR__ . '/_config.php';
session_start();
unset($_SESSION['cart']);
header('Location: '.BASE_PATH.'/cart.php');
exit;
?>