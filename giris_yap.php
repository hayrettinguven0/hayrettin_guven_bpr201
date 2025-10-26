<?php
include 'db.php';
$mesaj = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_adi = $_POST['kullanici_adi'];
    $sifre = $_POST['sifre'];
    $stmt = $conn->prepare("SELECT * FROM uyeler WHERE kullanici_adi = ? OR email = ?");
    $stmt->bind_param("ss", $kullanici_adi, $kullanici_adi); 
    $stmt->execute();
    $sonuc = $stmt->get_result();
    if ($sonuc->num_rows == 1) {
        $uye = $sonuc->fetch_assoc(); 
        if (password_verify($sifre, $uye['sifre'])) {
            $_SESSION['kullanici_id'] = $uye['id'];
            $_SESSION['kullanici_adi'] = $uye['kullanici_adi'];
            $_SESSION['kullanici_tipi'] = $uye['kullanici_tipi']; 
            header("Location: index.php?sayfa=anasayfa");
            exit;
        } else {
            $mesaj = "Hata: Girdiğiniz şifre yanlış!";
        }
    } else {
        $mesaj = "Hata: Bu kullanıcı adı veya e-posta ile kayıtlı bir üye bulunamadı!";
    }
    $stmt->close();
    $conn->close();
}
?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                
                <div class="text-center"> <img src="guvenmedyalogo.png" alt="Güven Medya Logo" class="makyaj-logo">
                </div>
                
                <h1 class="h3 text-center">Giriş Yap</h1>
                <p class="text-center text-muted mb-4">Tekrar hoş geldin!</p>

                <?php 
                if (!empty($mesaj)) {
                    echo "<div class='alert alert-danger'>$mesaj</div>";
                }
                ?>

                <form action="index.php?sayfa=giris" method="POST">
                    <div class="mb-3 text-start">
                        <label for="kullaniciAdi" class="form-label">Kullanıcı Adı (veya E-posta):</label>
                        <input type="text" class="form-control" id="kullaniciAdi" name="kullanici_adi" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="sifre" class="form-label">Şifre:</label>
                        <input type="password" class="form-control" id="sifre" name="sifre" required>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-mor">Giriş Yap</button>
                    </div>
                </form>

                <p class="text-center mt-3">
                    Hesabın yok mu? <a href="index.php?sayfa=kayit">Hemen Üye Ol</a>
                </p>
            </div>
        </div>
    </div>
</div>