<?php
include 'db.php';

// Kullanıcı ID kontrolü
if (!isset($_GET['id'])) {
    header("Location: index.php?sayfa=anasayfa");
    exit;
}

$tiklanan_id = (int)$_GET['id'];

// Kullanıcı temel bilgilerini çekme
$stmt_uye = $conn->prepare("SELECT kullanici_adi, profil_resmi, kullanici_tipi FROM uyeler WHERE id = ?");
$stmt_uye->bind_param("i", $tiklanan_id);
$stmt_uye->execute();
$uye_bilgi = $stmt_uye->get_result()->fetch_assoc();

if (!$uye_bilgi) {
    echo "<div class='alert alert-danger'>Kullanıcı bulunamadı!</div>";
    return;
}

// Kullanıcının açtığı konuları ve yorum sayılarını çekme
$sql_konular = "SELECT k.*, 
                (SELECT COUNT(*) FROM yorumlar WHERE konu_id = k.id) as yorum_sayisi
                FROM konular k 
                WHERE k.olusturan_id = ? 
                ORDER BY k.id DESC";
$stmt_konular = $conn->prepare($sql_konular);
$stmt_konular->bind_param("i", $tiklanan_id);
$stmt_konular->execute();
$konular_sonuc = $stmt_konular->get_result();
?>

<div class="card shadow-sm mb-4 border-0 bg-light rounded-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <?php if (!empty($uye_bilgi['profil_resmi'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($uye_bilgi['profil_resmi']); ?>" 
                         class="rounded-circle border border-3 border-white shadow-sm" 
                         style="width: 100px; height: 100px; object-fit: cover;">
                <?php else: ?>
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center border shadow-sm" style="width: 100px; height: 100px;">
                        <i class="bi bi-person text-muted" style="font-size: 50px;"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex-grow-1 ms-4">
                <h2 class="mb-1 fw-bold"><?php echo htmlspecialchars($uye_bilgi['kullanici_adi']); ?></h2>
                <span class="badge bg-primary mb-2 rounded-pill px-3">
                    <?php echo ($uye_bilgi['kullanici_tipi'] == 1) ? 'Yönetici' : 'Üye'; ?>
                </span>
                <p class="text-muted mb-0 small"><i class="bi bi-journal-text me-1"></i> Toplam <?php echo $konular_sonuc->num_rows; ?> konu paylaştı.</p>
            </div>
        </div>
    </div>
</div>

<h5 class="mb-3 fw-bold px-2 text-dark">Açtığı Konular</h5>
<div class="list-group shadow-sm border-0 rounded-4 overflow-hidden">
    <?php if ($konular_sonuc->num_rows > 0): ?>
        <?php while($konu = $konular_sonuc->fetch_assoc()): ?>
            <a href="index.php?sayfa=konu&id=<?php echo $konu['id']; ?>" class="list-group-item list-group-item-action py-3">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 text-primary fw-bold"><?php echo htmlspecialchars($konu['baslik']); ?></h6>
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i><?php echo zaman_once($konu['olusturma_tarihi']); ?>
                        </small>
                    </div>
                    <span class="badge rounded-pill bg-white text-dark border small shadow-sm">
                        <i class="bi bi-chat-left-text me-1 text-primary"></i><?php echo $konu['yorum_sayisi']; ?>
                    </span>
                </div>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="list-group-item text-center py-5 text-muted bg-white border-0">
            Bu kullanıcı henüz içerik paylaşmamış.
        </div>
    <?php endif; ?>
</div>

<?php
$stmt_uye->close();
$stmt_konular->close();
?>