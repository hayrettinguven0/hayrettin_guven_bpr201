<?php
include 'db.php';

// ID kontrolü
if (!isset($_GET['id'])) { 
    header("Location: index.php?sayfa=anasayfa"); 
    exit; 
}

$konu_id = (int)$_GET['id'];
$k_id = $_SESSION['kullanici_id'] ?? null;
$mesaj = "";

// Mesaj kontrolü
if (isset($_SESSION['mesaj'])) {
    $mesaj = $_SESSION['mesaj'];
    unset($_SESSION['mesaj']);
}

// Beğeni işlemi
if (isset($_GET['islem']) && $_GET['islem'] == 'konu_begeni') {
    if (!$k_id) {
        $_SESSION['mesaj'] = "Hata: Beğenmek için giriş yapmalısınız!";
    } else {
        $kontrol = $conn->prepare("SELECT id FROM konu_begenileri WHERE konu_id=? AND kullanici_id=?");
        $kontrol->bind_param("ii", $konu_id, $k_id);
        $kontrol->execute();
        $res = $kontrol->get_result();

        if ($res->num_rows > 0) {
            $sil = $conn->prepare("DELETE FROM konu_begenileri WHERE konu_id=? AND kullanici_id=?");
            $sil->bind_param("ii", $konu_id, $k_id);
            $sil->execute();
        } else {
            $ekle = $conn->prepare("INSERT INTO konu_begenileri (konu_id, kullanici_id) VALUES (?, ?)");
            $ekle->bind_param("ii", $konu_id, $k_id);
            $ekle->execute();
        }
    }
    header("Location: index.php?sayfa=konu&id=$konu_id");
    exit;
}

// Yorum ekleme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['yorum_gonder'])) {
    if (!$k_id) { 
        $_SESSION['mesaj'] = "Hata: Yorum yapmak için giriş yapmalısınız!";
        header("Location: index.php?sayfa=konu&id=$konu_id");
        exit;
    }
    
    $icerik = $_POST['yorum_icerik'];
    $resim = NULL;
    if (!empty($_FILES['yorum_foto']['name'])) {
        $ext = pathinfo($_FILES['yorum_foto']['name'], PATHINFO_EXTENSION);
        $resim = "yorum_" . time() . "_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['yorum_foto']['tmp_name'], "uploads/" . $resim);
    }

    if (!empty(trim($icerik)) || $resim != NULL) {
        $stmt = $conn->prepare("INSERT INTO yorumlar (konu_id, yazar_id, icerik, resim) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $konu_id, $k_id, $icerik, $resim);
        if ($stmt->execute()) {
            $_SESSION['mesaj'] = "Yorumunuz başarıyla eklendi!";
            header("Location: index.php?sayfa=konu&id=$konu_id");
            exit;
        }
    }
}

// Verileri çek
$stmt_konu = $conn->prepare("SELECT k.*, u.kullanici_adi, u.profil_resmi, 
    (SELECT COUNT(*) FROM konu_begenileri WHERE konu_id = k.id) as begeni_sayisi 
    FROM konular k JOIN uyeler u ON k.olusturan_id = u.id WHERE k.id = ?");
$stmt_konu->bind_param("i", $konu_id);
$stmt_konu->execute();
$konu = $stmt_konu->get_result()->fetch_assoc();

if (!$konu) { echo "Konu bulunamadı!"; exit; }

$kendi_begendim_mi = 0;
if ($k_id) {
    $b_kontrol = $conn->prepare("SELECT id FROM konu_begenileri WHERE konu_id=? AND kullanici_id=?");
    $b_kontrol->bind_param("ii", $konu_id, $k_id);
    $b_kontrol->execute();
    $kendi_begendim_mi = $b_kontrol->get_result()->num_rows;
}

$stmt_y = $conn->prepare("SELECT y.*, u.kullanici_adi, u.profil_resmi FROM yorumlar y 
    JOIN uyeler u ON y.yazar_id = u.id WHERE y.konu_id = ? ORDER BY y.id ASC");
$stmt_y->bind_param("i", $konu_id);
$stmt_y->execute();
$sonuc_yorumlar = $stmt_y->get_result();
?>

<div class="card border-0 shadow-sm mb-4 rounded-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center mb-4">
            <img src="uploads/<?php echo $konu['profil_resmi'] ?: 'default.png'; ?>" 
                 class="rounded-circle me-3 border shadow-sm" style="width: 55px; height: 55px; object-fit: cover;">
            <div class="flex-grow-1">
                <h1 class="h4 mb-0 fw-bold"><?php echo ($konu['sabitlendi_mi'] == 1 ? "📌 " : "") . htmlspecialchars($konu['baslik']); ?></h1>
                <small class="text-muted">Paylaşan: <b class="text-primary"><?php echo $konu['kullanici_adi']; ?></b> • <?php echo zaman_once($konu['olusturma_tarihi']); ?></small>
            </div>
            <?php if ($k_id && $k_id == $konu['olusturan_id']): ?>
                <a href="index.php?sayfa=konu_duzenle&id=<?php echo $konu['id']; ?>" class="btn btn-light btn-sm rounded-pill border">
                    <i class="bi bi-pencil-square"></i> Düzenle
                </a>
            <?php endif; ?>
        </div>
        
        <p class="card-text fs-5" style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($konu['icerik'] ?? 'İçerik boş.')); ?></p>
        
        <?php if (!empty($konu['resim'])): ?>
            <div class="text-center bg-light p-2 rounded-4 border mt-3 mb-3">
                <img src="uploads/<?php echo $konu['resim']; ?>" 
                     style="max-height: 300px; max-width: 50%; object-fit: contain; border-radius: 10px;" class="shadow-sm img-fluid">
            </div>
        <?php endif; ?>

        <div class="mt-4 pt-3 border-top d-flex flex-wrap gap-2 align-items-center">
            <a href="index.php?sayfa=konu&id=<?php echo $konu_id; ?>&islem=konu_begeni" 
               class="btn <?php echo $kendi_begendim_mi ? 'btn-danger' : 'btn-outline-danger'; ?> btn-sm rounded-pill px-4 shadow-sm">
                <i class="bi <?php echo $kendi_begendim_mi ? 'bi-heart-fill' : 'bi-heart'; ?> me-1"></i>
                Beğen (<?php echo $konu['begeni_sayisi']; ?>)
            </a>

            <?php if (isset($_SESSION['kullanici_tipi']) && $_SESSION['kullanici_tipi'] == 1): ?>
                <div class="vr mx-2 text-muted opacity-25 d-none d-sm-block"></div>
                <a href="index.php?sayfa=konu_sabitle&id=<?php echo $konu_id; ?>" class="btn btn-warning btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-pin-angle-fill me-1"></i> <?php echo ($konu['sabitlendi_mi'] == 1 ? 'Sabitlemeyi Kaldır' : 'Konuyu Sabitle'); ?>
                </a>
                <a href="index.php?sayfa=konu_sil&id=<?php echo $konu_id; ?>" class="btn btn-dark btn-sm rounded-pill px-3 shadow-sm" 
                   onclick="return confirm('Bu konuyu tamamen silmek istediğinizden emin misiniz?')">
                    <i class="bi bi-trash3-fill me-1"></i> Konuyu Sil
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<h6 class="mb-3 fw-bold text-secondary"><i class="bi bi-chat-left-dots me-2"></i>Yorumlar</h6>
<div class="mb-5">
    <?php while ($y = $sonuc_yorumlar->fetch_assoc()): ?>
        <div class="card mb-3 border-0 shadow-sm rounded-4">
            <div class="card-body py-3 px-4">
                <div class="d-flex align-items-center mb-2">
                    <img src="uploads/<?php echo $y['profil_resmi'] ?: 'default.png'; ?>" 
                         class="rounded-circle me-2 border" style="width: 30px; height: 30px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <strong class="small"><?php echo htmlspecialchars($y['kullanici_adi']); ?></strong>
                        <span class="text-muted" style="font-size: 0.7rem;"> • <?php echo zaman_once($y['yazma_tarihi']); ?></span>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if ($k_id && $k_id == $y['yazar_id']): ?>
                            <a href="index.php?sayfa=yorum_duzenle&id=<?php echo $y['id']; ?>" class="text-muted small"><i class="bi bi-pencil"></i></a>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['kullanici_tipi']) && $_SESSION['kullanici_tipi'] == 1): ?>
                            <a href="index.php?sayfa=yorum_sil&id=<?php echo $y['id']; ?>&konu_id=<?php echo $konu_id; ?>" 
                               class="text-danger small" onclick="return confirm('Bu yorumu silmek istediğinizden emin misiniz?')">
                                <i class="bi bi-trash3"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <p class="mb-1 small text-dark"><?php echo nl2br(htmlspecialchars($y['icerik'])); ?></p>
                
                <?php if (!empty($y['resim'])): ?>
                    <div class="my-2 text-start">
                        <img src="uploads/<?php echo $y['resim']; ?>" 
                             style="max-height: 250px; max-width: 100%; border-radius: 8px;" class="border shadow-sm">
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<div class="card border-0 shadow-sm rounded-4 sticky-bottom mb-4">
    <div class="card-body bg-white rounded-4 p-3">
        <?php if(!empty($mesaj)) echo "<div class='alert alert-info py-2 small border-0 shadow-sm rounded-3'>$mesaj</div>"; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <textarea name="yorum_icerik" class="form-control border-light bg-light mb-3 rounded-3" 
                      rows="3" placeholder="Fikrinizi paylaşın..." required></textarea>
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <label class="btn btn-light btn-sm border rounded-pill px-3" for="yorum_foto">
                        <i class="bi bi-image me-1"></i> Fotoğraf Ekle
                    </label>
                    <input type="file" name="yorum_foto" class="d-none" id="yorum_foto">
                    <small id="file-chosen" class="ms-2 text-muted small"></small>
                </div>
                <button type="submit" name="yorum_gonder" class="btn btn-primary rounded-pill px-5 shadow-sm">
                    <i class="bi bi-send-fill me-1"></i> Gönder
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Dosya ismi gösterimi
    document.getElementById('yorum_foto').onchange = function () {
        document.getElementById('file-chosen').textContent = this.files[0].name;
    };
</script>