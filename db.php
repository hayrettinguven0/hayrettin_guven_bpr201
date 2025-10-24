<?php
$sunucu = "localhost";
$kullanici_adi ="root";
$sifre = "H@yrettin_6uveN.66";
$veritabani_adi = "guvenmedya";
$conn = new mysqli($sunucu, $kullanici_adi, $sifre, $veritabani_adi);
if ($conn->connect_error) {
    die(" Vertabanı bağlantısında sorun var : " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
