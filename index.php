<?php
session_start();
// Saat dilimi ayarı
date_default_timezone_set('Europe/Istanbul'); 

// Zaman farkı fonksiyonu
function zaman_once($zaman) {
    $zaman_farki = time() - strtotime($zaman);
    
    if ($zaman_farki < 1) { 
        return 'az önce'; 
    }
    
    $birimler = array(
        31536000 => 'yıl', 
        2592000  => 'ay',
        604800   => 'hafta', 
        86400    => 'gün',
        3600     => 'saat', 
        60       => 'dakika', 
        1        => 'saniye'
    );
    
    foreach ($birimler as $saniye => $kelime) {
        $oran = $zaman_farki / $saniye;
        if ($oran >= 1) {
            $sonuc = round($oran);
            if ($kelime == 'gün' && $sonuc == 1) return 'dün';
            return $sonuc . ' ' . $kelime . ' önce';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Güven Medya Forumu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<?php 
// Mevcut sayfayı belirleme
$current_page = $_GET['sayfa'] ?? 'ilk_sayfa';

// Navbar kontrolü
if ($current_page != 'ilk_sayfa' && 
    $current_page != 'giris' && 
    $current_page != 'kayit') {
?>
    <nav class="navbar navbar-expand-lg bg-white shadow-sm border-bottom sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php?sayfa=anasayfa">
                <img src="guvenmedyalogo.png" style="height: 35px;" class="me-2">
                <span class="fw-bold text-primary" style="letter-spacing: -1px;">GÜVEN MEDYA</span>
            </a>
            
            <div class="ms-auto">
                <?php if (isset($_SESSION['kullanici_id'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-light border dropdown-toggle d-flex align-items-center rounded-pill px-3 shadow-sm" 
                                type="button" data-bs-toggle="dropdown">
                            <?php if (!empty($_SESSION['profil_resmi'])): ?>
                                <img src="uploads/<?php echo $_SESSION['profil_resmi']; ?>" 
                                     class="rounded-circle me-2" 
                                     style="width: 25px; height: 25px; object-fit: cover;">
                            <?php else: ?>
                                <i class="bi bi-person-circle me-2"></i>
                            <?php endif; ?>
                            <span class="small fw-bold"><?php echo htmlspecialchars($_SESSION['kullanici_adi']); ?></span>
                        </button>
                        
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-3">
                            <li><a class="dropdown-item py-2" href="index.php?sayfa=profil">
                                <i class="bi bi-person-gear me-2"></i> Profil Ayarları</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="index.php?sayfa=cikis">
                                <i class="bi bi-box-arrow-right me-2"></i> Güvenli Çıkış</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="d-flex gap-2">
                        <a href="index.php?sayfa=giris" class="btn btn-outline-primary btn-sm rounded-pill px-3">Giriş</a>
                        <a href="index.php?sayfa=kayit" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">Kayıt Ol</a>
                    </div>
                <?php endif; ?>
            </div> 
        </div> 
    </nav> 
<?php } ?>

<div class="container mt-4" style="min-height: 80vh;">
<?php
    // Sayfa yönlendirme sistemi
    if (isset($_GET['sayfa'])) {
        $sayfa = $_GET['sayfa'];
        
        if ($sayfa == 'anasayfa') {
            include 'anasayfa.php';
        } elseif ($sayfa == 'konu') {
            include 'sayfa_konu_detay.php';
        } elseif ($sayfa == 'konu_duzenle') {
            include 'sayfa_konu_duzenle.php';
        } elseif ($sayfa == 'yorum_duzenle') {
            include 'sayfa_yorum_duzenle.php';
        } elseif ($sayfa == 'giris') {
            include 'giris_yap.php';
        } elseif ($sayfa == 'kayit') {
            include 'kayit_ol.php';
        } elseif ($sayfa == 'yorum_sil') {
            include 'sayfa_yorum_sil.php';
        } elseif ($sayfa == 'cikis') {
            include 'sayfa_cikis.php';
        } elseif ($sayfa == 'konu_ac') {
            include 'sayfa_konu_ac.php';
        } elseif ($sayfa == 'konu_sil') { 
            include 'sayfa_konu_sil.php'; 
        } elseif ($sayfa == 'konu_sabitle') { 
            include 'sayfa_konu_sabitle.php'; 
        } elseif ($sayfa == 'profil') {
            include 'sayfa_profil.php'; 
        } elseif ($sayfa == 'kullanici') {
            include 'sayfa_kullanici.php'; 
        } else { 
            include 'ilk_sayfa.php'; 
        }
    } else {
        include 'ilk_sayfa.php';
    }
?>
</div>

<footer class="text-center text-muted mt-5 py-4 border-top bg-white">
    <p class="mb-0 fw-bold">&copy; 2025 Güven Medya Forum Platformu</p>
    <small><i class="bi bi-geo-alt-fill text-danger me-1"></i>Bartın / Türkiye</small> 
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Popover özelliğini başlatma
        var popoverTriggerList = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="popover"]')
        )
        popoverTriggerList.map(function (el) {
            return new bootstrap.Popover(el, {
                container: 'body', 
                html: true, 
                trigger: 'hover',
                sanitize: false
            })
        })
    })
</script>
</body>
</html>