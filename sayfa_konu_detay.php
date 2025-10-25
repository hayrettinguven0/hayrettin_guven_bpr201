<?php
include 'db.php';
if ( !isset($_GET['id']) ) {
    header("Location: index.php?sayfa=anasayfa");
    exit;
}

$konu_id = (int)$_GET['id']; 

// konu başlığı
$stmt_konu = $conn->prepare("SELECT * FROM konular WHERE id = ?");
$stmt_konu->bind_param("i", $konu_id);
$stmt_konu->execute();
$sonuc_konu = $stmt_konu->get_result();

if ($sonuc_konu->num_rows == 0) {
    // konu yoksa
    header("Location: index.php?sayfa=anasayfa");
    exit;
}

// konu varsa
$konu = $sonuc_konu->fetch_assoc();


// konuya göre bilgi çekme
$sql_yorumlar = "SELECT y.*, u.kullanici_adi 
                 FROM yorumlar y
                 JOIN uyeler u ON y.yazar_id = u.id
                 WHERE y.konu_id = ?
                 ORDER BY y.id ASC"; // 

$stmt_yorumlar = $conn->prepare($sql_yorumlar);
$stmt_yorumlar->bind_param("i", $konu_id);
$stmt_yorumlar->execute();
$sonuc_yorumlar = $stmt_yorumlar->get_result();

?>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h1 class="h3"><?php echo htmlspecialchars($konu['baslik']); ?></h1>
        <?php
        // DAHA SONRA EKLENECEK BİKAÇ DOKUNUŞ
        ?>
    </div>
</div>
<h2 class="h5 mb-3">Yorumlar (<?php echo $sonuc_yorumlar->num_rows; ?>)</h2>

<?php
if ($sonuc_yorumlar->num_rows > 0) {
    while ($yorum = $sonuc_yorumlar->fetch_assoc()) {
        ?>
        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong><?php echo htmlspecialchars($yorum['kullanici_adi']); ?></strong> 
                <small class="text-muted">yazdı:</small>
            </div>
            <div class="card-body">
                <p class="card-text"><?php echo nl2br(htmlspecialchars($yorum['icerik'])); ?></p>
            </div>
            <div class="card-footer text-muted" style="font-size: 0.9em;">
                <?php echo $yorum['yazma_tarihi']; ?>
            </div>
        </div>
        <?php
    } 

} else {
    echo "<p class='text-muted'>Henüz yorum yok!.</p>";
}
?>

<?php
$stmt_konu->close();
$stmt_yorumlar->close();
$conn->close();
?>