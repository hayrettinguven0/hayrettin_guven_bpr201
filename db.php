<?php
// Hata raporlama ayarı
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Veritabanı bağlantı bilgileri
$sunucu = "localhost";
$kullanici_adi = "root";
$sifre = "H@yrettin_6uveN.66"; 
$veritabani_adi = "guvenmedya";

try {
    // Bağlantı oluşturma
    $conn = new mysqli($sunucu, $kullanici_adi, $sifre, $veritabani_adi);

    // Karakter seti ve dil desteği ayarı
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    // Bağlantı hatası durumunda mesaj
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>