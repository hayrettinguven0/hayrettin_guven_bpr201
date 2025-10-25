<?php

// oturum verilerini temizler
$_SESSION = array();

// oturumu kapat
session_destroy();

// indexe gider
header("Location: index.php");
exit;

?>