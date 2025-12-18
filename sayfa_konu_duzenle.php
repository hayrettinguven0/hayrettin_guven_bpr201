<?php
include 'db.php';

// Oturum ve ID denetimi
if (!isset($_SESSION['kullanici_id']) || !isset($_GET['id'])) { 
    header("Location: index.php?sayfa=anasayfa"); 
    exit; 
}

$konu_id = (int)$_GET['id'];
$k_id = $_SESSION['kullanici_id'];
$mesaj = "";

// Konu bilgilerini çekme ve yetki kontrolü
$stmt_cek = $conn->prepare("SELECT * FROM konular WHERE id = ? AND olusturan_id = ?");
$stmt_cek->bind_param("ii", $konu_id, $k_id);
$stmt_cek->execute();
$konu = $stmt_cek->get_result()->fetch_assoc();

if (!$konu) { 
    echo "<div class='alert alert-danger m-4'>Bu konuyu düzenleme yetkiniz bulunmamaktadır!</div>"; 
    exit; 
}

// Güncelleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $baslik = trim($_POST['baslik']);
    $icerik = trim($_POST['icerik']);
    
    if (!empty($baslik) && !empty($icerik)) {
        $stmt = $conn->prepare("UPDATE konular SET baslik = ?, icerik = ?, guncelleme_tarihi = NOW() 
                               WHERE id = ? AND olusturan_id = ?");
        $stmt->bind_param("ssii", $baslik, $icerik, $konu_id, $k_id);
        
        if ($stmt->execute()) {
            header("Location: index.php?sayfa=konu&id=" . $konu_id);
            exit;
        } else {
            $mesaj = "Hata oluştu: " . $conn->error;
        }
    } else {
        $mesaj = "Lütfen tüm alanları doldurun!";
    }
}
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-pencil-square me-2"></i>Konuyu Düzenle
                    </h5>
                    <small class="text-muted small">ID: #<?php echo $konu_id; ?></small>
                </div>

                <?php if (!empty($mesaj)) echo "<div class='alert alert-warning py-2 small'>$mesaj</div>"; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Konu Başlığı</label>
                        <input type="text" name="baslik" class="form-control form-control-lg border-light bg-light" 
                               value="<?php echo htmlspecialchars($konu['baslik']); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Konu İçeriği</label>
                        <textarea name="icerik" class="form-control border-light bg-light" rows="12" 
                                  placeholder="Konu içeriğini buraya yazın..." required><?php echo htmlspecialchars($konu['icerik'] ?? ''); ?></textarea>
                        <div class="form-text small">Konu içeriği doldurulmalıdır.</div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="index.php?sayfa=konu&id=<?php echo $konu_id; ?>" class="btn btn-light border rounded-pill px-4">
                            <i class="bi bi-x-lg me-1"></i> Vazgeç
                        </a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>