<?php
include 'db.php';
$mesaj = "";
$mesaj_tipi = "danger"; // Varsayılan mesaj rengi

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_adi = trim($_POST['kullanici_adi']);
    $email = trim($_POST['email']);
    $sifre = $_POST['sifre'];
    $sifre_tekrar = $_POST['sifre_tekrar'];

    if ($sifre != $sifre_tekrar) {
        $mesaj = "Hata: Girdiğiniz şifreler birbiriyle uyuşmuyor!";
    } else {
        // Mevcut kullanıcı kontrolü
        $stmt = $conn->prepare("SELECT id FROM uyeler WHERE kullanici_adi = ? OR email = ?");
        $stmt->bind_param("ss", $kullanici_adi, $email); 
        $stmt->execute();
        $sonuc = $stmt->get_result();

        if ($sonuc->num_rows > 0) {
            $mesaj = "Hata: Bu kullanıcı adı veya e-posta adresi zaten alınmış!";
        } else {
            // Şifreleme ve kayıt işlemi
            $hashli_sifre = password_hash($sifre, PASSWORD_DEFAULT);
            $stmt_kayit = $conn->prepare("INSERT INTO uyeler (kullanici_adi, email, sifre) VALUES (?, ?, ?)");
            $stmt_kayit->bind_param("sss", $kullanici_adi, $email, $hashli_sifre);
            
            if ($stmt_kayit->execute()) {
                $mesaj = "Başarıyla kayıt oldunuz! Şimdi giriş yapabilirsiniz.";
                $mesaj_tipi = "success"; 
            } else {
                $mesaj = "Kayıt sırasında teknik bir hata oluştu!";
            }
            $stmt_kayit->close();
        }
        $stmt->close(); 
    }
}
?>

<div class="row">
    <div class="col-md-5 mx-auto">
        <div class="card shadow-sm border-0 rounded-4 mt-4">
            <div class="card-body p-5">
                
                <div class="text-center mb-4"> 
                    <img src="guvenmedyalogo.png" alt="Logo" style="height: 60px;" class="mb-3">
                    <h1 class="h4 fw-bold text-dark">Aramıza Katılın</h1>
                    <p class="small text-muted">Güven Medya ailesinin bir parçası olun!</p>
                </div>

                <?php if (!empty($mesaj)): ?>
                    <div class="alert alert-<?php echo $mesaj_tipi; ?> py-2 small border-0 text-center rounded-3 shadow-sm">
                        <i class="bi <?php echo ($mesaj_tipi == 'success') ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
                        <?php echo $mesaj; ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?sayfa=kayit" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Kullanıcı Adı</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" class="form-control bg-light border-0" name="kullanici_adi" placeholder="Kullanıcı adınız..." required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">E-posta Adresi</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" class="form-control bg-light border-0" name="email" placeholder="E-posta adresiniz..." required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Şifre</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" class="form-control bg-light border-0" name="sifre" placeholder="Şifreniz..." required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Şifre (Tekrar)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-shield-lock text-muted"></i></span>
                            <input type="password" class="form-control bg-light border-0" name="sifre_tekrar" placeholder="Şifrenizi doğrulayın..." required>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm fs-6">
                            <i class="bi bi-person-plus-fill me-2"></i>Kayıt Ol
                        </button>
                        <a href="index.php?sayfa=giris" class="btn btn-link btn-sm text-decoration-none text-muted">
                            Zaten hesabınız var mı? <b class="text-primary">Giriş Yapın</b>
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <p class="text-center mt-4 small text-muted text-uppercase" style="letter-spacing: 2px;">
            Güven Medya &copy; 2025
        </p>
    </div>
</div>