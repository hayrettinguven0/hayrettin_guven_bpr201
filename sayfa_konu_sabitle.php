<?php
include 'db.php';

// Güvenlik: Sadece admin girebilir
if (!isset($_SESSION['kullanici_tipi']) || $_SESSION['kullanici_tipi'] != 1) {
    header("Location: index.php?sayfa=anasayfa"); exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Mevcut sabitleme durumunu tersine çevir (1 ise 0, 0 ise 1 yap)
    $stmt = $conn->prepare("UPDATE konular SET sabitlendi_mi = 1 - sabitlendi_mi WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: index.php?sayfa=konu&id=" . $id);
exit;