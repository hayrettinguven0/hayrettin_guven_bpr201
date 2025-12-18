<?php
// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: index.php?sayfa=anasayfa");
    exit;
}

include 'db.php';
$mesaj = "";
$kullanici_id = $_SESSION['kullanici_id'];

// Kullanıcı verilerini çekme
$stmt = $conn->prepare("SELECT * FROM uyeler WHERE id = ?");
$stmt->bind_param("i", $kullanici_id);
$stmt->execute();
$kullanici = $stmt->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $yeni_resim_adi = $kullanici['profil_resmi']; 

    // Profil resmi yükleme işlemi
    if (isset($_FILES['profil_foto']) && $_FILES['profil_foto']['error'] == 0) {
        $dosya = $_FILES['profil_foto'];
        $uzanti = strtolower(pathinfo($dosya['name'], PATHINFO_EXTENSION));
        $yeni_isim = "profil_" . $kullanici_id . "_" . uniqid() . "." . $uzanti;
        $yukleme_yolu = 'uploads/' . $yeni_isim;

        if (move_uploaded_file($dosya['tmp_name'], $yukleme_yolu)) {
            $yeni_resim_adi = $yeni_isim;
            $_SESSION['profil_resmi'] = $yeni_isim; 
        }
    }

    // Veritabanı güncelleme
    $update = $conn->prepare("UPDATE uyeler SET profil_resmi = ? WHERE id = ?");
    $update->bind_param("si", $yeni_resim_adi, $kullanici_id);
    
    if ($update->execute()) {
        $mesaj = "Profil başarıyla güncellendi!";
        header("Refresh: 1; url=index.php?sayfa=profil"); 
    }
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 fw-bold"><i class="bi bi-person-gear me-2"></i>Profil Ayarları</h2>
        <a href="index.php?sayfa=anasayfa" class="btn btn-outline-primary btn-sm shadow-sm rounded-pill px-3">
            <i class="bi bi-house-door me-1"></i> Anasayfaya Dön
        </a>
    </div>

    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 text-center">
                    
                    <?php if (!empty($mesaj)) echo "<div class='alert alert-success py-2 small'>$mesaj</div>"; ?>

                    <div class="mb-4">
                        <?php if (!empty($kullanici['profil_resmi'])): ?>
                            <img src="uploads/<?php echo $kullanici['profil_resmi']; ?>" 
                                 class="rounded-circle shadow-sm border border-3 border-white" 
                                 style="width: 130px; height: 130px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center border border-dashed" 
                                 style="width: 130px; height: 130px;">
                                <i class="bi bi-person-circle text-secondary" style="font-size: 60px;"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form action="index.php?sayfa=profil" method="POST" enctype="multipart/form-data">
                        <div class="mb-3 text-start">
                            <label class="form-label small fw-bold text-muted">Kullanıcı Adı</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-at text-muted"></i></span>
                                <input type="text" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($kullanici['kullanici_adi']); ?>" disabled>
                            </div>
                            <div class="form-text mt-1" style="font-size: 0.75rem;">Kullanıcı adı değiştirilemez.</div>
                        </div>

                        <div class="mb-4 text-start">
                            <label for="profil_foto" class="form-label small fw-bold text-muted">Yeni Profil Fotoğrafı</label>
                            <input type="file" class="form-control form-control-sm" id="profil_foto" name="profil_foto" accept="image/*">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary shadow-sm rounded-pill">
                                <i class="bi bi-check2-circle me-1"></i> Bilgileri Güncelle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>