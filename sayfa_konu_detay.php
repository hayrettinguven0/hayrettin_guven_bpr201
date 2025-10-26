<?php
include 'db.php';
if ( !isset($_GET['id']) ) {
    // ID yoksa ana sayfaya at
    header("Location: index.php?sayfa=anasayfa");
    exit;
}
$konu_id = (int)$_GET['id']; 
$mesaj = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ( !isset($_SESSION['kullanici_id']) ) {
        $mesaj = "Hata: Yorum yapmak için giriş yapmalısınız!";
    } else {
        $icerik = $_POST['yorum_icerik'];
        $yazar_id = $_SESSION['kullanici_id'];
        
        if (empty(trim($icerik))) {
            $mesaj = "Hata: Yorum alanı boş olamaz!";
        } else {
            $stmt_yeni_yorum = $conn->prepare("INSERT INTO yorumlar (konu_id, yazar_id, icerik) VALUES (?, ?, ?)");
            $stmt_yeni_yorum->bind_param("iis", $konu_id, $yazar_id, $icerik);
            
            if ($stmt_yeni_yorum->execute()) {
                $mesaj = "Yorumunuz başarıyla eklendi!";
                $mesaj_tipi = "success";
            } else {
                $mesaj = "Hata: Yorum eklenirken bir sorun oluştu: " . $stmt_yeni_yorum->error;
            }
            $stmt_yeni_yorum->close();
        }
    }
}
// konu bşlgileri
$stmt_konu = $conn->prepare("SELECT * FROM konular WHERE id = ?");
$stmt_konu->bind_param("i", $konu_id);
$stmt_konu->execute();
$sonuc_konu = $stmt_konu->get_result();

if ($sonuc_konu->num_rows == 0) {
    header("Location: index.php?sayfa=anasayfa");
    exit;
}
// Konu var
$konu = $sonuc_konu->fetch_assoc();
$sql_yorumlar = "SELECT y.*, u.kullanici_adi 
                 FROM yorumlar y
                 JOIN uyeler u ON y.yazar_id = u.id
                 WHERE y.konu_id = ?
                 ORDER BY y.id ASC"; 
$stmt_yorumlar = $conn->prepare($sql_yorumlar);
$stmt_yorumlar->bind_param("i", $konu_id);
$stmt_yorumlar->execute();
$sonuc_yorumlar = $stmt_yorumlar->get_result();
?>
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h1 class="h3"><?php echo htmlspecialchars($konu['baslik']); ?></h1>
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
    // yorum yoksa
    echo "<p class='text-muted'>Henüz yorum yok!.</p>";
}
?>
<hr> <h3 class="h5 mt-4 mb-3">Yorum Yaz</h3>
<?php
if (!empty($mesaj)) {
    $alert_tipi = isset($mesaj_tipi) ? 'alert-success' : 'alert-danger';
    echo "<div class='alert $alert_tipi'>$mesaj</div>";
}
// giriş yapıldı mı
if ( isset($_SESSION['kullanici_id']) ) {   
?>
    <form action="index.php?sayfa=konu&id=<?php echo $konu_id; ?>" method="POST">
        <div class="mb-3">
            <label for="yorum_icerik" class="form-label">Yorumunuz:</label>
            <textarea class="form-control" id="yorum_icerik" name="yorum_icerik" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Yorumu Gönder</button>
    </form>
<?php
} else {
    // misafirse
    echo '<div class="alert alert-warning">';
    echo 'Yorum yapmak için <a href="index.php?sayfa=giris">giriş yapmanız</a> gerekmektedir.';
    echo '</div>';
}
?>
<?php
$stmt_konu->close();
$stmt_yorumlar->close();
$conn->close();
?>