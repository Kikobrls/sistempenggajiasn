<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /pages/auth/login.php');
exit;
