<?php
include 'db.php';

// Oturum ve ID denetimi
if (!isset($_SESSION['kullanici_id']) || !isset($_GET['id'])) { 
    header("Location: index.php?sayfa=anasayfa"); 
    exit; 
}

$yorum_id = (int)$_GET['id'];
$yazar_id = $_SESSION['kullanici_id'];
$mesaj = "";

// Yazar yetki kontrolü
$stmt_y = $conn->prepare("SELECT * FROM yorumlar WHERE id = ? AND yazar_id = ?");
$stmt_y->bind_param("ii", $yorum_id, $yazar_id);
$stmt_y->execute();
$yorum = $stmt_y->get_result()->fetch_assoc();

if (!$yorum) { 
    echo "<div class='alert alert-danger m-4 shadow-sm border-0 rounded-4'>
            <i class='bi bi-exclamation-triangle-fill me-2'></i>Bu yorumu düzenleme yetkiniz bulunmamaktadır!
          </div>"; 
    exit; 
}

// Güncelleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $yeni_icerik = trim($_POST['icerik']);
    
    if (!empty($yeni_icerik)) {
        $stmt = $conn->prepare("UPDATE yorumlar SET icerik = ?, guncelleme_tarihi = NOW() 
                               WHERE id = ? AND yazar_id = ?");
        $stmt->bind_param("sii", $yeni_icerik, $yorum_id, $yazar_id);
        
        if ($stmt->execute()) {
            header("Location: index.php?sayfa=konu&id=" . $yorum['konu_id']);
            exit;
        } else {
            $mesaj = "Hata oluştu: " . $conn->error;
        }
    } else {
        $mesaj = "Yorum içeriği boş olamaz!";
    }
}
?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card shadow-sm border-0 rounded-4 mt-2">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-chat-dots me-2"></i>Yorumu Düzenle
                    </h5>
                    <small class="text-muted" style="font-size: 0.7rem;">ID: #<?php echo $yorum_id; ?></small>
                </div>

                <?php if (!empty($mesaj)) echo "<div class='alert alert-warning py-2 small border-0'>$mesaj</div>"; ?>

                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Yorum İçeriği</label>
                        <textarea name="icerik" class="form-control border-light bg-light" 
                                  rows="6" required><?php echo htmlspecialchars($yorum['icerik']); ?></textarea>
                        <div class="form-text small">Yorumunuzu buradan güncelleyebilirsiniz.</div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="index.php?sayfa=konu&id=<?php echo $yorum['konu_id']; ?>" 
                           class="btn btn-light border rounded-pill px-4 btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Vazgeç
                        </a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Güncelle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>