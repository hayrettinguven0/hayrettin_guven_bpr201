<?php
include 'db.php';

// Kullanıcı ID kontrolü
if (!isset($_GET['id'])) {
    header("Location: index.php?sayfa=anasayfa");
    exit;
}

$profil_id = (int)$_GET['id'];

// Kullanıcı bilgilerini ve istatistiklerini çekme
$stmt_user = $conn->prepare("SELECT u.*, 
    (SELECT COUNT(*) FROM konular WHERE olusturan_id = u.id) as toplam_konu,
    (SELECT COUNT(*) FROM yorumlar WHERE yazar_id = u.id) as toplam_yorum
    FROM uyeler u WHERE u.id = ?");
$stmt_user->bind_param("i", $profil_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();

if (!$user_data) {
    echo "<div class='alert alert-danger'>Üye bulunamadı!</div>";
    exit;
}

// Kullanıcının paylaştığı konuları çekme
$stmt_konular = $conn->prepare("SELECT k.*, 
    (SELECT COUNT(*) FROM yorumlar WHERE konu_id = k.id) as yorum_sayisi,
    (SELECT COUNT(*) FROM konu_begenileri WHERE konu_id = k.id) as begeni_sayisi
    FROM konular k WHERE k.olusturan_id = ? 
    ORDER BY k.id DESC");
$stmt_konular->bind_param("i", $profil_id);
$stmt_konular->execute();
$sonuc_konular = $stmt_konular->get_result();
?>

<div class="container py-4">
    <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
        <div class="card-body p-4">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <?php if (!empty($user_data['profil_resmi'])): ?>
                        <img src="uploads/<?php echo $user_data['profil_resmi']; ?>" 
                             class="rounded-circle border border-3 border-white shadow-sm" 
                             style="width: 100px; height: 100px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center border" style="width: 100px; height: 100px;">
                            <i class="bi bi-person-fill text-primary" style="font-size: 50px;"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="ms-4">
                    <h2 class="h3 mb-1 fw-bold"><?php echo htmlspecialchars($user_data['kullanici_adi']); ?></h2>
                    <p class="text-muted mb-2 small">
                        <i class="bi bi-calendar-check me-1"></i> Üyelik Tarihi: <?php echo date("d.m.Y", strtotime($user_data['kayit_tarihi'])); ?>
                    </p>
                    <div class="d-flex gap-2">
                        <span class="badge bg-light text-dark border rounded-pill px-3">
                            <b class="text-primary"><?php echo $user_data['toplam_konu']; ?></b> Konu
                        </span>
                        <span class="badge bg-light text-dark border rounded-pill px-3">
                            <b class="text-primary"><?php echo $user_data['toplam_yorum']; ?></b> Yorum
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h5 class="mb-3 fw-bold text-dark"><i class="bi bi-collection me-2 text-primary"></i>Paylaştığı Konular</h5>
    
    <div class="list-group shadow-sm border-0">
        <?php if ($sonuc_konular->num_rows > 0): ?>
            <?php while($k = $sonuc_konular->fetch_assoc()): ?>
                <a href="index.php?sayfa=konu&id=<?php echo $k['id']; ?>" class="list-group-item list-group-item-action py-3 border-bottom">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($k['baslik']); ?></h6>
                            <small class="text-muted"><?php echo zaman_once($k['olusturma_tarihi']); ?></small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-white text-dark border rounded-pill small">
                                <i class="bi bi-chat-left-text text-primary me-1"></i> <?php echo $k['yorum_sayisi']; ?>
                            </span>
                            <span class="badge bg-white text-dark border rounded-pill small ms-1">
                                <i class="bi bi-heart-fill text-danger me-1"></i> <?php echo $k['begeni_sayisi']; ?>
                            </span>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="list-group-item text-center py-5 text-muted">
                <i class="bi bi-emoji-frown d-block mb-2" style="font-size: 30px;"></i>
                Bu kullanıcı henüz bir konu paylaşmamış.
            </div>
        <?php endif; ?>
    </div>
</div>