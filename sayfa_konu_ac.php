<?php
if ( !isset($_SESSION['kullanici_id']) ) {
    header("Location: index.php?sayfa=anasayfa");
    exit;
}
include 'db.php';
$mesaj = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $baslik = $_POST['baslik'];
    $icerik = $_POST['icerik']; 
    $olusturan_id = $_SESSION['kullanici_id']; 
    if (empty(trim($baslik))) {
        $mesaj = "Hata: Konu başlığı boş olamaz!";
    } else {
        
        $stmt_konu = $conn->prepare("INSERT INTO konular (baslik, olusturan_id) VALUES (?, ?)");
        $stmt_konu->bind_param("si", $baslik, $olusturan_id);
        if ($stmt_konu->execute()) {

            $yeni_konu_id = $conn->insert_id;
            $stmt_yorum = $conn->prepare("INSERT INTO yorumlar (konu_id, yazar_id, icerik) VALUES (?, ?, ?)");
            $stmt_yorum->bind_param("iis", $yeni_konu_id, $olusturan_id, $icerik);
            
            if ($stmt_yorum->execute()) {
                header("Location: index.php?sayfa=anasayfa");
                exit;
                
            } else {
                $mesaj = "Hata: Konu başlığı eklendi ancak ilk yorum eklenemedi: " . $stmt_yorum->error;
            }
            $stmt_yorum->close(); } 
            else {
            $mesaj = "Hata: Konu açılırken bir sorun oluştu: " . $stmt_konu->error;
        }
        $stmt_konu->close();
    }
    $conn->close();
}

?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 text-center">Yeni Konu Aç</h1>
                
                <?php
                if (!empty($mesaj)) {
                    echo "<div class='alert alert-danger'>$mesaj</div>";
                }
                ?>
                <form action="index.php?sayfa=konu_ac" method="POST">
                    <div class="mb-3">
                        <label for="baslik" class="form-label">Konu Başlığı:</label>
                        <input type="text" class="form-control" id="baslik" name="baslik" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="icerik" class="form-label">İlk Yorumunuz (İçerik):</label>
                        <textarea class="form-control" id="icerik" name="icerik" rows="5" required></textarea>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Konuyu Aç</button>
                    </div>
                </form>
                
            </div>
        </div>
    </div>
</div>