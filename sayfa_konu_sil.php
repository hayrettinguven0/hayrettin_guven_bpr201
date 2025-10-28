<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1> Silme Sayfası Başladı</h1>"; 

// admin özel
include 'db.php';
echo "<h1>Veritabanı (db.php) Dahil Edildi</h1>"; 
if (!isset($_SESSION['kullanici_tipi']) || $_SESSION['kullanici_tipi'] != 1) {
    echo "<h1>HATA: Admin Değilsin! (Yönlendirme kapalı)</h1>"; 
    header("Location: index.php?sayfa=anasayfa"); 
    exit; 
}
echo "<h1> Admin Kontrolü Başarıyla Geçildi</h1>"; 
if (!isset($_GET['id'])) {
    echo "<h1>HATA: Silinecek Konu ID'si Adreste Belirtilmemiş!</h1>"; 
    header("Location: index.php?sayfa=anasayfa"); 
    exit; 
}
$konu_id = (int)$_GET['id'];

echo "<h1> Silinecek Konu ID Alındı: " . $konu_id . "</h1>"; 

// konu yorum idleri bulma
$yorum_idler = array();
$stmt_yorum_bul = $conn->prepare("SELECT id FROM yorumlar WHERE konu_id = ?");

// hata dedektörü
if (!$stmt_yorum_bul) {
    die("<h1>Hata: Yorum Bulma Sorgusu Hazırlanamadı! MySQL Hatası: " . $conn->error . "</h1>"); 
}

$stmt_yorum_bul->bind_param("i", $konu_id);

// hata dedektörü
if (!$stmt_yorum_bul->execute()) {
     die("<h1>Hata: Yorum Bulma Sorgusu Çalıştırılamadı! MySQL Hatası: " . $stmt_yorum_bul->error . "</h1>");
}

$sonuc_yorumlar = $stmt_yorum_bul->get_result();
while ($yorum = $sonuc_yorumlar->fetch_assoc()) {
    $yorum_idler[] = $yorum['id']; 
}
$stmt_yorum_bul->close();

echo "<h1> Silinecek Yorum ID'leri Bulundu (Toplam: " . count($yorum_idler) . " adet)</h1>"; 
// todo
if (!empty($yorum_idler)) {
    echo "<h1> Yorum Beğenilerini Silme Başlıyor...</h1>"; 
    
    $stmt_yorum_begeni_sil = $conn->prepare("DELETE FROM yorum_begenileri WHERE yorum_id = ?");
    
    // hata dedektörü
    if (!$stmt_yorum_begeni_sil) {
        die("<h1>Hata : Yorum Beğeni Silme Sorgusu Hazırlanamadı! MySQL Hatası: " . $conn->error . "</h1>");
    }
    foreach ($yorum_idler as $tek_yorum_id) {
        $stmt_yorum_begeni_sil->bind_param("i", $tek_yorum_id);
        if (!$stmt_yorum_begeni_sil->execute()) {
             die("<h1>Hata: Yorum Beğenisi Silinemedi (Yorum ID: ".$tek_yorum_id.")! MySQL Hatası: " . $stmt_yorum_begeni_sil->error . "</h1>");
        }
    }
    $stmt_yorum_begeni_sil->close();
    echo "<h1> Yorum Beğenilerini Silme Tamamlandı (veya denendi).</h1>"; 
    // todo

} else {
    echo "<h1> Silinecek Yorum Beğenisi Yoktu (Çünkü Yorum Yoktu).</h1>"; 
}


// konu beğenisi sil
$stmt_konu_begeni_sil = $conn->prepare("DELETE FROM konu_begenileri WHERE konu_id = ?");
// hata dedektrü
if (!$stmt_konu_begeni_sil) { die("<h1>Hataa : Konu Beğeni Silme Sorgusu Hazırlanamadı: ".$conn->error."</h1>"); }
$stmt_konu_begeni_sil->bind_param("i", $konu_id);
// hata dedektörü 
if (!$stmt_konu_begeni_sil->execute()) { die("<h1>Hata : Konu Beğenisi Silinemedi! MySQL Hatası: ".$stmt_konu_begeni_sil->error."</h1>"); }
$stmt_konu_begeni_sil->close();
echo "<h1> Konu Beğenileri Silindi.</h1>"; 
// todo


// yorum sil
$stmt_yorum_sil = $conn->prepare("DELETE FROM yorumlar WHERE konu_id = ?");
// Hata dedektörü
if (!$stmt_yorum_sil) { die("<h1>Hata : Yorum Silme Sorgusu Hazırlanamadı: ".$conn->error."</h1>"); }
$stmt_yorum_sil->bind_param("i", $konu_id);
// hata dedektörü 
if (!$stmt_yorum_sil->execute()) { die("<h1>Hata : Yorumlar Silinemedi! MySQL Hatası: ".$stmt_yorum_sil->error."</h1>"); }
$stmt_yorum_sil->close();

echo " Yorumlar Silindi.</h1>"; 
// TODO 


// konu sil
$stmt_konu_sil = $conn->prepare("DELETE FROM konular WHERE id = ?");
// Hata dedektörü
if (!$stmt_konu_sil) { die("<h1>Hata 9: Konu Silme Sorgusu Hazırlanamadı: ".$conn->error."</h1>"); }
$stmt_konu_sil->bind_param("i", $konu_id);
// Hata dedektörü
if (!$stmt_konu_sil->execute()) { die("<h1>Hata: Konu Silinemedi! MySQL Hatası: ".$stmt_konu_sil->error."</h1>"); }
$stmt_konu_sil->close();
echo "<h1>Konu Başarıyla Silindi! Tüm İşlemler Tamam!</h1>"; 
 header("Location: index.php?sayfa=anasayfa");
 exit;

?>