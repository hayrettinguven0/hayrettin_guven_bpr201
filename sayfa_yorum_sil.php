<?php
include 'db.php';

// Yönetici yetki kontrolü
if (!isset($_SESSION['kullanici_tipi']) || $_SESSION['kullanici_tipi'] != 1) {
    header("Location: index.php?sayfa=anasayfa"); 
    exit; 
}

if (isset($_GET['id']) && isset($_GET['konu_id'])) {
    $yorum_id = (int)$_GET['id'];
    $konu_id = (int)$_GET['konu_id'];

    // Yorumu veritabanından silme
    $stmt = $conn->prepare("DELETE FROM yorumlar WHERE id = ?");
    $stmt->bind_param("i", $yorum_id);
    
    if ($stmt->execute()) {
        $_SESSION['mesaj'] = "Yorum silindi.";
    }
    $stmt->close();
}

// İlgili konuya yönlendirme
header("Location: index.php?sayfa=konu&id=" . $konu_id);
exit;