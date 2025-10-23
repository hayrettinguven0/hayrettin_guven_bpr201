<?php



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
<body class="ana-sayfa-body">

    <nav class="navbar navbar-expand-lg bg-light shadow-sm">
      <div class="container">
        <a class="navbar-brand" href="index.php?sayfa=anasayfa">Güven Medya</a>
        
        <div class="ms-auto">
          <a href="index.php?sayfa=giris" class="btn btn-primary btn-sm">Giriş Yap</a>
          <a href="index.php?sayfa=kayit" class="btn btn-success btn-sm">Üye Ol</a>
        </div>
      </div>
    </nav>

    <div class="container mt-4">
        
       <?php
        
        // Adres çubuğunda ?sayfa=... diye bir şey var mı diye kontrol et
        if ( isset($_GET['sayfa']) ) {
            
            $sayfa = $_GET['sayfa'];
            
            if ($sayfa == 'giris') {
                include 'giris_yap.php'; 
                
            } elseif ($sayfa == 'kayit') {
                include 'kayit_ol.php';

            } elseif ($sayfa == 'anasayfa') {
                include 'anasayfa.php';
                
            } else {
                include 'ilk_sayfa.php'; 
            }
            // Varsayılan sayfa
        } else {
            
            include 'ilk_sayfa.php';
        }
        
        ?>

    </div> <footer class="text-center text-muted mt-5 mb-3">
        <p>&copy; 2025 Güven Medya</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>