<?php
include 'db.php';
$mesaj = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_adi = trim($_POST['kullanici_adi']);
    $sifre = $_POST['sifre'];
    
    if (empty($kullanici_adi) || empty($sifre)) {
        $mesaj = "Hata: Lütfen tüm alanları doldurun!";
    } else {
        // Kullanıcıyı kullanıcı adı veya e-posta ile sorgula
        $stmt = $conn->prepare("SELECT * FROM uyeler WHERE kullanici_adi = ? OR email = ?");
        $stmt->bind_param("ss", $kullanici_adi, $kullanici_adi); 
        $stmt->execute();
        $sonuc = $stmt->get_result();
        
        if ($sonuc->num_rows == 1) {
            $uye = $sonuc->fetch_assoc(); 
            
            // Şifre doğrulaması
            if (password_verify($sifre, $uye['sifre'])) {
                // Oturum bilgilerini ata
                $_SESSION['kullanici_id'] = $uye['id'];
                $_SESSION['kullanici_adi'] = $uye['kullanici_adi'];
                $_SESSION['profil_resmi'] = $uye['profil_resmi']; 
                $_SESSION['kullanici_tipi'] = $uye['kullanici_tipi'];
                
                header("Location: index.php?sayfa=anasayfa");
                exit;
            } else {
                $mesaj = "Hata: Girdiğiniz şifre yanlış!";
            }
        } else {
            $mesaj = "Hata: Kullanıcı veya e-posta bulunamadı!";
        }
        $stmt->close();
    }
}
?>

<div class="row">
    <div class="col-md-5 mx-auto">
        <div class="card shadow-sm border-0 rounded-4 mt-5">
            <div class="card-body p-5">
                
                <div class="text-center mb-4"> 
                    <img src="guvenmedyalogo.png" alt="Logo" style="max-height: 70px;" class="mb-3">
                    <h1 class="h4 fw-bold text-dark">Tekrar Hoş Geldiniz</h1>
                    <p class="small text-muted">Forumdaki yenilikleri keşfetmeye hazır mısınız?</p>
                </div>

                <?php if (!empty($mesaj)): ?>
                    <div class="alert alert-danger py-2 small border-0 text-center rounded-3 shadow-sm mb-4">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>
                        <?php echo $mesaj; ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?sayfa=giris" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Kullanıcı Adı veya E-posta</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-person-badge text-muted"></i></span>
                            <input type="text" class="form-control bg-light border-0" name="kullanici_adi" 
                                   placeholder="Kullanıcı adınız veya mailiniz..." required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Şifre</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-key text-muted"></i></span>
                            <input type="password" class="form-control bg-light border-0" name="sifre" 
                                   placeholder="Şifreniz..." required>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm fs-6" style="background-color: #6f42c1; border: none;">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Giriş Yap
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="small text-muted mb-0">Henüz bir hesabınız yok mu?</p>
                    <a href="index.php?sayfa=kayit" class="text-decoration-none fw-bold" style="color: #6f42c1;">
                        Hemen Üye Olun!
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>