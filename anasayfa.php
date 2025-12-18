<?php
include 'db.php';
$arama = $_GET['arama'] ?? '';

// Aktif kullanıcı sorgusu
$top_yazarlar = $conn->query("SELECT u.id, u.kullanici_adi, u.profil_resmi, 
    COUNT(k.id) as konu_sayisi FROM uyeler u 
    JOIN konular k ON u.id = k.olusturan_id 
    GROUP BY u.id ORDER BY konu_sayisi DESC LIMIT 5");
?>

<div class="row mb-4">
    <div class="col-md-12">
        <form action="index.php" method="GET" class="d-flex shadow-sm rounded overflow-hidden">
            <input type="hidden" name="sayfa" value="anasayfa">
            <input type="text" name="arama" class="form-control border-0 py-2" 
                   placeholder="Forumda bir şeyler ara ..." value="<?php echo htmlspecialchars($arama); ?>">
            <button class="btn btn-primary rounded-0 px-4" type="submit">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h5 mb-0 text-muted fw-bold"><i class="bi bi-list-stars me-2"></i>Son Konular</h1>
            <?php if (isset($_SESSION['kullanici_id'])): ?>
                <a href="index.php?sayfa=konu_ac" class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> Yeni Konu Aç
                </a>
            <?php else: ?>
                <a href="index.php?sayfa=giris" class="btn btn-outline-secondary btn-sm rounded-pill">
                    Konu açmak için giriş yap
                </a>
            <?php endif; ?>
        </div>

        <div class="list-group border-0 bg-transparent">
            <?php
            // Konuları ve istatistikleri çeken sorgu
            $sql = "SELECT k.*, u.kullanici_adi, u.profil_resmi, 
                (SELECT COUNT(*) FROM yorumlar WHERE konu_id = k.id) as yorum_sayisi,
                (SELECT COUNT(*) FROM konu_begenileri WHERE konu_id = k.id) as begeni_sayisi
                FROM konular k JOIN uyeler u ON k.olusturan_id = u.id 
                WHERE k.baslik LIKE ? ORDER BY k.sabitlendi_mi DESC, k.id DESC";
            
            $stmt = $conn->prepare($sql);
            $like_val = "%$arama%";
            $stmt->bind_param("s", $like_val);
            $stmt->execute();
            $sonuc = $stmt->get_result();

            if ($sonuc->num_rows > 0):
                while($konu = $sonuc->fetch_assoc()): 
                    $kart_stili = ($konu['sabitlendi_mi'] == 1) ? "bg-light border-start border-danger border-4" : "bg-white";
                    
                    // Ön izleme hazırlığı
                    $ozet = mb_strimwidth(strip_tags($konu['icerik']), 0, 150, "...");
                    $pop_content = "";
                    if (!empty($konu['resim'])) {
                        $pop_content .= "<img src='uploads/" . $konu['resim'] . "' class='img-fluid rounded mb-2' style='max-height:150px; width:100%; object-fit:cover;'>";
                    }
                    $pop_content .= "<p class='small mb-0 text-dark'>" . htmlspecialchars($ozet) . "</p>";
                    ?>
                    
                    <a href="index.php?sayfa=konu&id=<?php echo $konu['id']; ?>" 
                       class="list-group-item list-group-item-action <?php echo $kart_stili; ?> py-3 mb-2 rounded-3 shadow-sm border-0"
                       data-bs-toggle="popover" 
                       data-bs-trigger="hover"
                       data-bs-html="true"
                       data-bs-title="<i class='bi bi-eye me-1'></i> <?php echo htmlspecialchars($konu['baslik']); ?>" 
                       data-bs-content="<?php echo htmlspecialchars($pop_content); ?>"
                       data-bs-placement="right">
                       
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <img src="uploads/<?php echo $konu['profil_resmi'] ?: 'default.png'; ?>" 
                                     class="rounded-circle border" style="width: 45px; height: 45px; object-fit: cover;">
                            </div>
                            
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold text-dark">
                                    <?php if($konu['sabitlendi_mi'] == 1): ?><i class="bi bi-pin-angle-fill text-danger me-1"></i><?php endif; ?>
                                    <?php echo htmlspecialchars($konu['baslik']); ?>
                                </h6>
                                <small class="text-muted">
                                    Yazan: <b><?php echo $konu['kullanici_adi']; ?></b> • <?php echo zaman_once($konu['olusturma_tarihi']); ?>
                                </small>
                            </div>
                            
                            <div class="ms-3 text-end d-none d-sm-block">
                                <span class="badge bg-white text-dark border rounded-pill small">
                                    <i class="bi bi-chat-left-text text-primary me-1"></i> <?php echo $konu['yorum_sayisi']; ?>
                                </span>
                                <span class="badge bg-white text-dark border rounded-pill small ms-1">
                                    <i class="bi bi-heart-fill text-danger me-1"></i> <?php echo $konu['begeni_sayisi']; ?>
                                </span>

                                <?php if (isset($_SESSION['kullanici_tipi']) && $_SESSION['kullanici_tipi'] == 1): ?>
                                    <div class="mt-2">
                                        <object>
                                            <a href="index.php?sayfa=konu_sabitle&id=<?php echo $konu['id']; ?>" 
                                               class="btn <?php echo ($konu['sabitlendi_mi'] == 1 ? 'btn-warning' : 'btn-outline-warning'); ?> btn-sm rounded-circle p-1 shadow-sm" 
                                               style="width: 32px; height: 32px;" title="Sabitle/Kaldır">
                                                <i class="bi bi-pin-angle-fill"></i>
                                            </a>
                                            <a href="index.php?sayfa=konu_sil&id=<?php echo $konu['id']; ?>" 
                                               class="btn btn-outline-dark btn-sm rounded-circle p-1 ms-1 shadow-sm" 
                                               style="width: 32px; height: 32px;" title="Sil"
                                               onclick="return confirm('Bu konuyu silmek istediğine emin misiniz?')">
                                                <i class="bi bi-trash3-fill"></i>
                                            </a>
                                        </object>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="list-group-item text-center py-5 text-muted bg-white border-0 rounded-4 shadow-sm">
                    <i class="bi bi-search mb-2 d-block fs-1"></i>
                    Buralar henüz boş... İlk konuyu siz başlatın!
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4 overflow-hidden rounded-4">
            <div class="card-header bg-white fw-bold py-3 border-bottom">
                <i class="bi bi-fire text-danger me-2"></i>En Aktif Kullanıcılar
            </div>
            <div class="list-group list-group-flush">
                <?php while($y = $top_yazarlar->fetch_assoc()): ?>
                    <a href="index.php?sayfa=kullanici&id=<?php echo $y['id']; ?>" 
                       class="list-group-item list-group-item-action d-flex align-items-center py-3 border-bottom-0">
                        <img src="uploads/<?php echo $y['profil_resmi'] ?: 'default.png'; ?>" 
                             class="rounded-circle me-3 border shadow-sm" 
                             style="width: 35px; height: 35px; object-fit: cover;">
                        <span class="small flex-grow-1 fw-bold text-dark"><?php echo htmlspecialchars($y['kullanici_adi']); ?></span>
                        <span class="badge bg-primary rounded-pill small"><?php echo $y['konu_sayisi']; ?> Konu</span>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
        
        <div class="alert alert-info border-0 shadow-sm rounded-4 small p-3">
            <i class="bi bi-info-circle-fill me-2"></i>
            Konuların üzerine gelerek içeriklerini ve fotoğraflarını görebilirsiniz.
        </div>
    </div>
</div>