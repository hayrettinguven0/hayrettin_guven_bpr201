<?php
// PHP Kodları 
include 'db.php';

// Hata olursa bunu dolduracağız.
$mesaj = "";

// Form kontrolü
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // veri güveniği
    $kullanici_adi = $_POST['kullanici_adi'];
    $email = $_POST['email'];
    $sifre = $_POST['sifre'];
    $sifre_tekrar = $_POST['sifre_tekrar'];

    // şifre eşleşmesi
    if ($sifre != $sifre_tekrar) {
        $mesaj = "Hata: Girdiğiniz şifreler uyuşmuyor!";
    } else {
        
        // zaten var mı kontrol
        $stmt = $conn->prepare("SELECT id FROM uyeler WHERE kullanici_adi = ? OR email = ?");
        $stmt->bind_param("ss", $kullanici_adi, $email); 
        $stmt->execute();
        $sonuc = $stmt->get_result();

        if ($sonuc->num_rows > 0) {
            // sıfırdan büyükse birisi vardır
            $mesaj = "Hata: Bu kullanıcı adı veya e-posta adresi zaten kayıtlı!";
        } else {
            
            // kayıt olma
            $hashli_sifre = password_hash($sifre, PASSWORD_DEFAULT);

            // Veritabanı
            $stmt = $conn->prepare("INSERT INTO uyeler (kullanici_adi, email, sifre) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $kullanici_adi, $email, $hashli_sifre);

            if ($stmt->execute()) {
                $mesaj = "Başarıyla kayıt oldunuz! Şimdi giriş yapabilirsiniz.";
                $mesaj_tipi = "success"; 
            } else {
                $mesaj = "Kayıt olurken bir hata oluştu: " . $stmt->error;
            }
        }
        $stmt->close(); 
    }
    $conn->close(); 
}
?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                
                <h1 class="h3 text-center">Üye Ol</h1>
                <p class="text-center text-muted mb-4">Güven Medya ailesine katılın!</p>

                <?php 
                if (!empty($mesaj)) {
                    $alert_tipi = isset($mesaj_tipi) ? 'alert-success' : 'alert-danger';
                    
                    echo "<div class='alert $alert_tipi'>$mesaj</div>";
                }
                ?>

                <form action="index.php?sayfa=kayit" method="POST">
                    <div class="mb-3 text-start">
                        <label for="kullaniciAdi" class="form-label">Kullanıcı Adı:</label>
                        <input type="text" class="form-control" id="kullaniciAdi" name="kullanici_adi" required>
                    </div>
                    
                    <div class="mb-3 text-start">
                        <label for="email" class="form-label">E-posta Adresi:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3 text-start">
                        <label for="sifre" class="form-label">Şifre:</label>
                        <input type="password" class="form-control" id="sifre" name="sifre" required>
                    </div>

                    <div class="mb-3 text-start">
                        <label for="sifreTekrar" class="form-label">Şifre (Tekrar):</label>
                        <input type="password" class="form-control" id="sifreTekrar" name="sifre_tekrar" required>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-success">Kayıt Ol</button>
                    </div>
                </form>

                <p class="text-center mt-3">
                    Zaten üye misin? <a href="index.php?sayfa=giris">Giriş Yap</a>
                </p>
            </div>
        </div>
    </div>
</div>