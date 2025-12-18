<?php
include 'db.php';

// Yetki kontrolü (Sadece yönetici)
if (!isset($_SESSION['kullanici_tipi']) || $_SESSION['kullanici_tipi'] != 1) {
    header("Location: index.php?sayfa=anasayfa"); 
    exit; 
}

// ID kontrolü
if (!isset($_GET['id'])) {
    header("Location: index.php?sayfa=anasayfa"); 
    exit; 
}

$konu_id = (int)$_GET['id'];

// Kademeli silme işlemi
// Yorum beğenilerini sil
$stmt1 = $conn->prepare("DELETE FROM yorum_begenileri WHERE yorum_id IN (SELECT id FROM yorumlar WHERE konu_id = ?)");
$stmt1->bind_param("i", $konu_id);
$stmt1->execute();
$stmt1->close();

// Konu beğenilerini sil
$stmt2 = $conn->prepare("DELETE FROM konu_begenileri WHERE konu_id = ?");
$stmt2->bind_param("i", $konu_id);
$stmt2->execute();
$stmt2->close();

// Yorumları sil
$stmt3 = $conn->prepare("DELETE FROM yorumlar WHERE konu_id = ?");
$stmt3->bind_param("i", $konu_id);
$stmt3->execute();
$stmt3->close();

// Etiket bağlantılarını sil
$stmt4 = $conn->prepare("DELETE FROM konu_etiketleri WHERE konu_id = ?");
$stmt4->bind_param("i", $konu_id);
$stmt4->execute();
$stmt4->close();

// Konuyu sil
$stmt5 = $conn->prepare("DELETE FROM konular WHERE id = ?");
$stmt5->bind_param("i", $konu_id);

if ($stmt5->execute()) {
    $_SESSION['mesaj'] = "Konu ve bağlantılı tüm veriler silindi.";
} else {
    $_SESSION['mesaj'] = "Hata: İşlem başarısız.";
}

$stmt5->close();

header("Location: index.php?sayfa=anasayfa");
exit;
?>