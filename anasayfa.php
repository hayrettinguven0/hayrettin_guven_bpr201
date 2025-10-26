<?php
include 'db.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4">Forum Konuları</h1>
  
  <?php

  if ( isset($_SESSION['kullanici_id']) ) {
      
      echo '<a href="index.php?sayfa=konu_ac" class="btn btn-primary">Yeni Konu Aç</a>';
      
  } else {
      
      echo '<a href="index.php?sayfa=giris" class="btn btn-primary disabled">Konu Açmak İçin Giriş Yapın veya Üye Olun</a>';
      
  }
  ?>
  
</div>

<div class="list-group">
    
    <?php
    // veritabanından çekme
    
    $sql = "SELECT k.*, u.kullanici_adi 
            FROM konular k 
            LEFT JOIN uyeler u ON k.olusturan_id = u.id 
            ORDER BY k.sabitlendi_mi DESC, k.id DESC";
            
    $sonuc = $conn->query($sql);
    
    // konu varmı
    if ($sonuc->num_rows > 0) {
        while($konu = $sonuc->fetch_assoc()) {
            
            $kart_stili ="";
            $ikon ="";
            if ($konu['sabitlendi_mi'] == 1) { 
                $kart_stili ="bg-light"; 
                $ikon = "[SABİT] ";      
            }
            echo '<a href="index.php?sayfa=konu&id=' . $konu['id'] . '" class="list-group-item list-group-item-action ' . $kart_stili . '">';
            echo '  <div class="d-flex w-100 justify-content-between">';
            echo '    <h5 class="mb-1">' . $ikon . htmlspecialchars($konu['baslik']) . '</h5>';
            echo '    <small class="text-muted">' . $konu['olusturma_tarihi'] . '</small>';
            echo '  </div>';
            echo '  <small class="text-muted">Yazan: ' . htmlspecialchars($konu['kullanici_adi']) . '</small>';

            // admin kontrolü
            if (isset($_SESSION['kullanici_tipi']) && $_SESSION['kullanici_tipi'] == 1) {
                echo '<div class="float-end">'; 
                $sabitle_link = 'index.php?sayfa=sabitle&id=' . $konu['id']; 
                $sabitle_yazi = ($konu['sabitlendi_mi'] == 1) ? 'Sabitlemeyi Kaldır' : 'Sabitle';
                $sabitle_renk = ($konu['sabitlendi_mi'] == 1) ? 'btn-warning' : 'btn-info'; 
                
                echo '<a href="' . $sabitle_link . '" class="btn ' . $sabitle_renk . ' btn-sm me-2">' . $sabitle_yazi . '</a>';
                $sil_link = 'index.php?sayfa=konu_sil&id=' . $konu['id']; 
                echo '<a href="' . $sil_link . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Emin misin\')">Sil</a>';                
                echo '</div>'; 
                
            } 

            echo '</a>'; 

        } 
    } else { 
        // konu yoksa
        echo '<div class="list-group-item">';
        echo '    <h5 class="text-center text-muted">Henüz hiç konu açılmamış.</h5>';
        echo '    <p class="text-center text-muted mb-0">İlk konuyu sen aç!</p>';
        echo '</div>';
    }
    $conn->close();
    ?>

</div> 