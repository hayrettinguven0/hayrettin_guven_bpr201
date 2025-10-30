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
    $etiketler_str = $_POST['etiketler']; 
    $olusturan_id = $_SESSION['kullanici_id'];
    if (empty(trim($baslik))) {
        $mesaj = "Hata: Konu başlığı boş olamaz!"; 
    } 
    elseif (empty(trim($icerik))) { 
        $mesaj = "Hata: İçerik boş olamaz!!"; 
    }
    else { 
        // etiketler işlemleri
        $etiketler_dizi_ham = explode(',', $etiketler_str);
        $etiket_idler = array();
        
        foreach($etiketler_dizi_ham as $etiket_ham) {
            $etiket_temiz = strtolower(trim($etiket_ham)); 
            
            if (!empty($etiket_temiz) && mb_strlen($etiket_temiz) <= 50) {
                
                $stmt_etiket_bul = $conn->prepare("SELECT id FROM etiketler WHERE etiket_adi = ?");
                if (!$stmt_etiket_bul) { 
                    die("Hata:" . $conn->error); 
                }
                $stmt_etiket_bul->bind_param("s", $etiket_temiz);
                if (!$stmt_etiket_bul->execute()) { 
                    die("Hata:" . $stmt_etiket_bul->error); 
                }
                $sonuc_etiket = $stmt_etiket_bul->get_result();
                $etiket_id = null;
                
                if($sonuc_etiket->num_rows > 0){
                    $etiket_satir = $sonuc_etiket->fetch_assoc();
                    $etiket_id = $etiket_satir['id']; 
                } else {
                    $stmt_etiket_ekle = $conn->prepare("insert into etiketler (etiket_adi) values(?)");
                    if (!$stmt_etiket_ekle) { 
                        die("Hata:" . $conn->error); 
                    }
                    $stmt_etiket_ekle->bind_param("s", $etiket_temiz);
                    if ($stmt_etiket_ekle->execute()) {
                        $etiket_id = $conn->insert_id; 
                    } else {
                        die("Hata:" . $stmt_etiket_ekle->error); 
                    }
                    $stmt_etiket_ekle->close();
                }
                $stmt_etiket_bul->close(); 
                
                if ($etiket_id !== null && !in_array($etiket_id, $etiket_idler)) {
                    $etiket_idler[] = $etiket_id; 
                }
            } 
        } 
        $stmt_konu = $conn->prepare("INSERT INTO konular (baslik, olusturan_id) VALUES (?, ?)");
        if (!$stmt_konu) { die("Hata (konu prepare): " . $conn->error); } 
        $stmt_konu->bind_param("si", $baslik, $olusturan_id);
        
        if ($stmt_konu->execute()) {
            $yeni_konu_id = $conn->insert_id;
            $stmt_konu->close(); 
            $stmt_yorum = $conn->prepare("INSERT INTO yorumlar (konu_id, yazar_id, icerik) VALUES (?, ?, ?)");
            if (!$stmt_yorum) { die("Hata (yorum prepare): " . $conn->error); } 
            $stmt_yorum->bind_param("iis", $yeni_konu_id, $olusturan_id, $icerik); 
            if ($stmt_yorum->execute()) 
                {
                $stmt_yorum->close(); 
                if(!empty($etiket_idler)){
                    $stmt_konu_etiket = $conn->prepare("insert into konu_etiketleri (konu_id, etiket_id) values(?,?)");
                    if(!$stmt_konu_etiket) { 
                        die("Hata (konu_etiket prepare): " . $conn->error); 
                    }
                    foreach($etiket_idler as $tek_etiket_id){ 
                        $stmt_konu_etiket->bind_param ("ii", $yeni_konu_id, $tek_etiket_id);
                        if(!$stmt_konu_etiket->execute()){
                            if ($conn->errno != 1062) { 
                                die ("Hata (konu_etiket execute):" . $stmt_konu_etiket->error);
                            }
                        }
                    } 
                    $stmt_konu_etiket->close();
                } 
                header("location: index.php?sayfa=anasayfa");
                exit;
            } else { 
                $mesaj = "Hata: Konu başlığı eklendi ancak ilk yorum eklenemedi: " . $stmt_yorum->error;
                $stmt_yorum->close(); 
            }
        } else { 
            $mesaj = "Hata: Konu açılırken bir sorun oluştu: " . $stmt_konu->error;
            $stmt_konu->close(); 
        }
    } 
    if (!empty($mesaj)) {
         $conn->close();
    }
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
                   <div class ="mb-3">
                    <label for="etiketler" class="form--label">Konuyu tanımlayacak etiketler(Virgülle ayırın. ör:güven, medya, destek, forum) </label>
                    <input type="text" class="form-control" id="etiketler" name="etiketler"> </div>
                   <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Konuyu Aç</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>