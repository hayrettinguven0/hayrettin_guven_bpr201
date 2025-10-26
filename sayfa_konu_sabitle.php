<?php //admin özel
include 'db.php';

// admin mi değilse anasayfaya
if (!isset($_SESSION['kullanici_tipi']) || $_SESSION['kullanici_tipi'] != 1) {
    header("Location: index.php?sayfa=anasayfa");
    exit;
}

// konu sabitleme
if (!isset($_GET['id'])) {
    header("Location: index.php?sayfa=anasayfa");
    exit;
}
$konu_id = (int)$_GET['id'];
$stmt_konu = $conn->prepare("SELECT sabitlendi_mi FROM konular WHERE id = ?");
$stmt_konu->bind_param("i", $konu_id);
$stmt_konu->execute();
$sonuc_konu = $stmt_konu->get_result();

if ($sonuc_konu->num_rows == 0) {
    header("Location: index.php?sayfa=anasayfa");
    exit;
}
$konu = $sonuc_konu->fetch_assoc();
$su_anki_durum = $konu['sabitlendi_mi']; // 1 veya 0

// 1 se 0 yap, 0 sa 1 yap
$yeni_durum = ($su_anki_durum == 0) ? 1 : 0;

// veritabanı yenileme
$stmt_guncelle = $conn->prepare("UPDATE konular SET sabitlendi_mi = ? WHERE id = ?");
$stmt_guncelle->bind_param("ii", $yeni_durum, $konu_id);
if ($stmt_guncelle->execute()) {
} 
// yönlendir
header("Location: index.php?sayfa=anasayfa");
exit;

?>