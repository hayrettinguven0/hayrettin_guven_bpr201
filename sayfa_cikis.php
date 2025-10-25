<?php
$_SESSION = array();

// oturumu kapat
session_destroy();

// ilk sayfaya yolla
header("Location: index.php");
exit;

?>