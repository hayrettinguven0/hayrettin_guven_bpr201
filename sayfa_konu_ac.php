<?php
// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: index.php?sayfa=anasayfa");
    exit;
}

include 'db.php';
$mesaj = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Form verileri
    $baslik = trim($_POST['baslik']);
    $icerik = trim($_POST['icerik']); 
    $etiketler_str = trim($_POST['etiketler']);
    $olusturan_id = $_SESSION['kullanici_id'];

    // Fotoğraf yükleme işlemi
    $dosya_adi_db = null; 

    if (isset($_FILES['resim']) && $_FILES['resim']['error'] == 0) {
        $dosya = $_FILES['resim'];
        $uzanti = strtolower(pathinfo($dosya['name'], PATHINFO_EXTENSION));
        $izin_verilenler = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($uzanti, $izin_verilenler)) {
            $yeni_isim = "konu_" . uniqid() . "." . $uzanti;
            $yukleme_yolu = 'uploads/' . $yeni_isim;

            if (move_uploaded_file($dosya['tmp_name'], $yukleme_yolu)) {
                $dosya_adi_db = $yeni_isim; 
            }
        }
    }

    // Girdi kontrolleri
    if (empty($baslik) || empty($icerik)) {
        $mesaj = "Hata: Başlık ve içerik alanları boş bırakılamaz!"; 
    } else { 
        
        // Konuyu veritabanına ekle
        $stmt_konu = $conn->prepare("INSERT INTO konular (baslik, icerik, olusturan_id, resim) VALUES (?, ?, ?, ?)");
        $stmt_konu->bind_param("ssis", $baslik, $icerik, $olusturan_id, $dosya_adi_db);
        
        if ($stmt_konu->execute()) {
            $yeni_konu_id = $conn->insert_id;
            $stmt_konu->close();

            // İlk yorumu ekle
            $stmt_yorum = $conn->prepare("INSERT INTO yorumlar (konu_id, yazar_id, icerik) VALUES (?, ?, ?)");
            $stmt_yorum->bind_param("iis", $yeni_konu_id, $olusturan_id, $icerik);
            $stmt_yorum->execute();
            $stmt_yorum->close();

            // Etiketleri işle
            if(!empty($etiketler_str)){
                $etiketler_dizi = explode(',', $etiketler_str);
                foreach($etiketler_dizi as $etiket_ham) {
                    $etiket_temiz = strtolower(trim($etiket_ham));
                    if (!empty($etiket_temiz)) {
                        // Etiket varlık kontrolü
                        $stmt_ebul = $conn->prepare("SELECT id FROM etiketler WHERE etiket_adi = ?");
                        $stmt_ebul->bind_param("s", $etiket_temiz);
                        $stmt_ebul->execute();
                        $sonuc = $stmt_ebul->get_result();
                        
                        if($sonuc->num_rows > 0){
                            $etiket_id = $sonuc->fetch_assoc()['id'];
                        } else {
                            $stmt_eekle = $conn->prepare("INSERT INTO etiketler (etiket_adi) VALUES (?)");
                            $stmt_eekle->bind_param("s", $etiket_temiz);
                            $stmt_eekle->execute();
                            $etiket_id = $conn->insert_id;
                            $stmt_eekle->close();
                        }
                        $stmt_ebul->close();

                        // Bağlantı tablosuna ekle
                        $stmt_bagla = $conn->prepare("INSERT IGNORE INTO konu_etiketleri (konu_id, etiket_id) VALUES (?, ?)");
                        $stmt_bagla->bind_param("ii", $yeni_konu_id, $etiket_id);
                        $stmt_bagla->execute();
                        $stmt_bagla->close();
                    }
                }
            }

            header("location: index.php?sayfa=anasayfa");
            exit;

        } else { 
            $mesaj = "Hata: Konu oluşturulurken bir problem oluştu!";
        }
    } 
}
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h1 class="h3 fw-bold text-primary">Yeni Bir Konu Başlatın</h1>
                    <p class="text-muted small">Düşüncelerinizi toplulukla paylaşın!</p>
                </div>
                
                <?php if (!empty($mesaj)) echo "<div class='alert alert-danger py-2 small border-0'>$mesaj</div>"; ?>

                <form action="index.php?sayfa=konu_ac" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="baslik" class="form-label small fw-bold text-secondary">Konu Başlığı</label>
                        <input type="text" class="form-control form-control-lg border-light bg-light" 
                               id="baslik" name="baslik" placeholder="Başlığı buraya yazın..." required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="icerik" class="form-label small fw-bold text-secondary">İçerik</label>
                        <textarea class="form-control border-light bg-light" id="icerik" name="icerik" 
                                  rows="8" placeholder="Konuyu detaylandırın..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="etiketler" class="form-label small fw-bold text-secondary">Etiketler</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-tags"></i></span>
                            <input type="text" class="form-control border-light bg-light" id="etiketler" name="etiketler" 
                                   placeholder="ör: güven, medya, destek (virgülle ayırın)">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="resim" class="form-label small fw-bold text-secondary">Konu Görseli (İsteğe Bağlı)</label>
                        <input type="file" class="form-control form-control-sm border-light bg-light" id="resim" name="resim" accept="image/*">
                        <div class="form-text" style="font-size: 0.7rem;">Sadece resim dosyaları (jpg, png, gif) kabul edilir.</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                            <i class="bi bi-send-plus me-2"></i>Konuyu Yayınla
                        </button>
                        <a href="index.php?sayfa=anasayfa" class="btn btn-light btn-sm rounded-pill text-muted">Vazgeç</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>