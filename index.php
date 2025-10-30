<?php
session_start();


?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Güven Medya Forumu</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body> 
    <?php 
    $current_page = $_GET['sayfa'] ?? 'ilk_sayfa';
    if ($current_page != 'ilk_sayfa' && $current_page !='giris' && $current_page != 'kayit') {
        ?>
  
    <nav class="navbar navbar-expand-lg bg-light shadow-sm">
      <div class="container">
        <a class="navbar-brand" href="index.php?sayfa=anasayfa">
            <img src="guvenmedyalogo.png" alt="Güven Medya Logo" style="height: 40px; width: auto;">
        </a>
        <div class="ms-auto">
            <?php
            // kullanıcı giriş yaptı mı
            if ( isset($_SESSION['kullanici_id']) ) {
                
                // yaptıysa
                echo '<span class="navbar-text me-3">';
                echo ' <strong>' . htmlspecialchars($_SESSION['kullanici_adi']) . '</strong>!';
                echo '</span>';
                
                // çıkış butonu
                echo '<a href="index.php?sayfa=cikis" class="btn btn-danger btn-sm">Çıkış Yap</a>';
                
            } else {
                
                // misaffir 
                echo '<a href="index.php?sayfa=giris" class="btn btn-primary btn-sm">Giriş Yap</a>';
                echo '<a href="index.php?sayfa=kayit" class="btn btn-success btn-sm ms-2">Üye Ol</a>';
            }
            ?>
        </div> 
    </div> 
</nav> 
<?php
    }
    ?>
<div class="container mt-4">
  <?php
        
        // ALLAHIN BELASI YER 
        
        if ( isset($_GET['sayfa']) ) {
            
            $sayfa = $_GET['sayfa'];
            
            if ($sayfa == 'giris') {
                include 'giris_yap.php'; 
                
            } elseif ($sayfa == 'kayit') {
                include 'kayit_ol.php';
                
            } elseif ($sayfa == 'anasayfa') {
                include 'anasayfa.php';
                
            } elseif ($sayfa == 'cikis') {
                include 'sayfa_cikis.php';
                
            } elseif ($sayfa == 'konu_ac') {
                include 'sayfa_konu_ac.php';
                
            } elseif ($sayfa == 'konu') {
                include 'sayfa_konu_detay.php';
                
            }
            elseif ($sayfa == 'sabitle' ) {
                include 'sayfa_konu_sabitle.php';
            }
            elseif ($sayfa == 'konu_sil' ) {
                include 'sayfa_konu_sil.php';
            }
             else { 
                include 'ilk_sayfa.php'; 
            }
            
        } else {
            include 'ilk_sayfa.php';
        }
        ?>
    </div>
     <footer class="text-center text-muted mt-5 mb-3">
        <p>&copy; 2025 Güven Medya</p>
    </footer>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>