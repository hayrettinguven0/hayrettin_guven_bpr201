<?php
session_start();

require_once 'db.php'; // $pdo değişkeni artık burada mevcut

// --- MESAJLAR ---
$hata_mesaji = null;
$kayit_mesaji = null;

// URL'den gelen işlem/hata mesajlarını yakala
if (isset($_GET['kayit']) && $_GET['kayit'] == 'basarili') {
    $kayit_mesaji = "Kayıt işlemi başarıyla tamamlandı. Şimdi giriş yapabilirsiniz.";
}
if (isset($_GET['konu']) && $_GET['konu'] == 'basarili') {
    $kayit_mesaji = "Yeni konu başarıyla oluşturuldu.";
}
if (isset($_GET['islem'])) {
    if ($_GET['islem'] == 'sabitlendi') {
        $kayit_mesaji = "Konu başarıyla başa sabitlendi.";
    } elseif ($_GET['islem'] == 'sabit_kaldirildi') {
        $kayit_mesaji = "Konu sabitlemesi kaldırıldı.";
    } elseif ($_GET['islem'] == 'silindi') {
        $kayit_mesaji = "Konu ve ilgili tüm yorumlar başarıyla silindi.";
    } 
    // YENİ EKLENDİ: Kullanıcı yönetimi mesajı
    elseif ($_GET['islem'] == 'rol_degisti') {
        $kayit_mesaji = "Kullanıcının rolü başarıyla güncellendi.";
    }
}
if (isset($_GET['hata'])) {
    if ($_GET['hata'] == 'yetki_yok') {
        $hata_mesaji = "Bu işlemi yapmak için yönetici yetkiniz bulunmamaktadır.";
    } elseif ($_GET['hata'] == 'id_yok') {
        $hata_mesaji = "İşlem için geçerli bir konu ID'si belirtilmedi.";
    } elseif ($_GET['hata'] == 'db_hatasi') {
        $hata_mesaji = "İşlem sırasında bir veritabanı hatası oluştu.";
    }
    // YENİ EKLENDİ: Kullanıcı yönetimi hatası
    elseif ($_GET['hata'] == 'rol_hata_kendi') {
        $hata_mesaji = "Güvenlik nedeniyle kendi rolünüzü değiştiremezsiniz.";
    }
}

// --- ÇIKIŞ İŞLEMİ ---
if (isset($_GET['sayfa']) && $_GET['sayfa'] == 'cikis') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// --- POST İŞLEMLERİ (Form Gönderimleri) ---
// (Giriş, Kayıt, Yeni Konu, Yeni Yorum - DEĞİŞİKLİK YOK)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    // --- GİRİŞ İŞLEMİ ---
    if ($_POST['action'] == 'giris') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE kullanici_adi = ?");
            $stmt->execute([$_POST['kullanici_adi']]);
            $kullanici = $stmt->fetch();

            if ($kullanici && password_verify($_POST['sifre'], $kullanici['sifre'])) {
                $_SESSION['kullanici'] = [
                    'id' => $kullanici['id'],
                    'kullanici_adi' => $kullanici['kullanici_adi'],
                    'rol' => $kullanici['rol']
                ];
                $_SESSION['show_welcome'] = true;
                header('Location: index.php?sayfa=anasayfa');
                exit;
            } else {
                $hata_mesaji = "Kullanıcı adı veya şifre hatalı!";
            }
        } catch (PDOException $e) {
            $hata_mesaji = "Veritabanı hatası: " . $e->getMessage();
        }
    }

    // --- KAYIT İŞLEMİ ---
    elseif ($_POST['action'] == 'kayit') {
        try {
            $hashed_sifre = password_hash($_POST['sifre'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO kullanicilar (kullanici_adi, email, sifre) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['kullanici_adi'], $_POST['email'], $hashed_sifre]);
            header('Location: index.php?sayfa=giris&kayit=basarili');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $hata_mesaji = "Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor!";
            } else {
                $hata_mesaji = "Kayıt sırasında bir hata oluştu: " . $e->getMessage();
            }
        }
    }

    // --- YENİ KONU AÇMA İŞLEMİ ---
    elseif ($_POST['action'] == 'yeni_konu') {
        if (!isset($_SESSION['kullanici'])) {
            $hata_mesaji = "Konu açmak için giriş yapmanız gerekmektedir.";
        } else {
            $baslik = $_POST['baslik'];
            $icerik = $_POST['icerik'];
            $kullanici_id = $_SESSION['kullanici']['id'];

            if (empty($baslik) || empty($icerik)) {
                $hata_mesaji = "Başlık ve içerik alanları boş bırakılamaz.";
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO konular (baslik, icerik, kullanici_id) VALUES (?, ?, ?)");
                    $stmt->execute([$baslik, $icerik, $kullanici_id]);
                    $yeni_konu_id = $pdo->lastInsertId();
                    header("Location: index.php?sayfa=konu_detay&id=$yeni_konu_id&konu=basarili");
                    exit;
                } catch (PDOException $e) {
                    $hata_mesaji = "Konu oluşturulurken bir hata oluştu: " . $e->getMessage();
                }
            }
        }
    }

    // --- YORUM EKLEME İŞLEMİ ---
    elseif ($_POST['action'] == 'yeni_yorum') {
        if (!isset($_SESSION['kullanici'])) {
            $hata_mesaji = "Yorum gönderebilmek için giriş yapmanız gerekmektedir.";
        } else {
            $konu_id = $_POST['konu_id'] ?? 0;
            $yorum = trim($_POST['yorum'] ?? '');

            if (empty($yorum)) {
                $hata_mesaji = "Yorum içeriği boş olamaz.";
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO yorumlar (konu_id, kullanici_id, yorum_metni) VALUES (?, ?, ?)");
                    $stmt->execute([$konu_id, $_SESSION['kullanici']['id'], $yorum]);
                    header("Location: index.php?sayfa=konu_detay&id={$konu_id}");
                    exit;
                } catch (PDOException $e) {
                    $hata_mesaji = "Yorum eklenirken hata oluştu: " . $e->getMessage();
                }
            }
        }
    }
} // --- POST İŞLEMLERİ BİTİŞİ ---


// --- ADMİN GET İŞLEMLERİ (Sabitleme, Silme, Rol Değiştirme) ---
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // YENİ EKLENDİ: 'toggle_rol' admin işlemi listeye eklendi
    $admin_eylemleri = ['toggle_sabitle', 'konu_sil', 'toggle_rol'];

    if (in_array($action, $admin_eylemleri)) {
        
        // 1. GÜVENLİK: Admin değilse, işlemi hemen durdur.
        if (!isset($_SESSION['kullanici']) || $_SESSION['kullanici']['rol'] != 'admin') {
            header('Location: index.php?sayfa=anasayfa&hata=yetki_yok');
            exit;
        }

        // 2. GÜVENLİK: Hangi *öğe* üzerinde işlem yapılacak? ID'yi al.
        $id = $_GET['id'] ?? 0;
        if (empty($id)) {
            header('Location: index.php?sayfa=anasayfa&hata=id_yok');
            exit;
        }

        // 3. Eylemleri gerçekleştir
        try {
            // --- SABİTLEME / KALDIRMA EYLEMİ ---
            if ($action == 'toggle_sabitle') {
                $stmt = $pdo->prepare("SELECT sabitlendi FROM konular WHERE id = ?");
                $stmt->execute([$id]);
                $konu = $stmt->fetch();
                if ($konu) {
                    $yeni_durum = $konu['sabitlendi'] ? 0 : 1;
                    $update_stmt = $pdo->prepare("UPDATE konular SET sabitlendi = ? WHERE id = ?");
                    $update_stmt->execute([$yeni_durum, $id]);
                    $islem_mesaji = $yeni_durum ? 'sabitlendi' : 'sabit_kaldirildi';
                    header("Location: index.php?sayfa=konu_detay&id=$id&islem=$islem_mesaji");
                    exit;
                }
            }
            
            // --- KONU SİLME EYLEMİ ---
            elseif ($action == 'konu_sil') {
                $stmt = $pdo->prepare("DELETE FROM konular WHERE id = ?");
                $stmt->execute([$id]);
                header("Location: index.php?sayfa=anasayfa&islem=silindi");
                exit;
            }

            // ----------------------------------------------------
            // --- YENİ EKLENDİ: ROL DEĞİŞTİRME EYLEMİ ---
            // ----------------------------------------------------
            elseif ($action == 'toggle_rol') {
                $kullanici_id = $id; // ID'nin bir kullanıcı ID'si olduğunu netleştirelim
                
                // 3A. GÜVENLİK: Admin kendi rolünü değiştiremesin!
                if ($kullanici_id == $_SESSION['kullanici']['id']) {
                    header("Location: index.php?sayfa=kullanici_yonetimi&hata=rol_hata_kendi");
                    exit;
                }
                
                // 3B. Kullanıcının mevcut rolünü al
                $stmt = $pdo->prepare("SELECT rol FROM kullanicilar WHERE id = ?");
                $stmt->execute([$kullanici_id]);
                $kullanici = $stmt->fetch();
                
                if ($kullanici) {
                    // Rolü tersine çevir (admin -> uye, uye -> admin)
                    $yeni_rol = ($kullanici['rol'] == 'admin') ? 'uye' : 'admin';
                    
                    // Veritabanını güncelle
                    $update_stmt = $pdo->prepare("UPDATE kullanicilar SET rol = ? WHERE id = ?");
                    $update_stmt->execute([$yeni_rol, $kullanici_id]);
                    
                    // Başarı mesajıyla yönetim sayfasına dön
                    header("Location: index.php?sayfa=kullanici_yonetimi&islem=rol_degisti");
                    exit;
                }
            }

        } catch (PDOException $e) {
            header("Location: index.php?sayfa=anasayfa&hata=db_hatasi");
            exit;
        }
    }
} // --- GET İŞLEMLERİ BİTİŞİ ---


// --- SAYFA YÖNLENDİRİCİ (ROUTER) ---
$sayfa = $_GET['sayfa'] ?? 'giris_ekrani';

if (isset($_SESSION['kullanici']) && ($sayfa == 'giris_ekrani' || $sayfa == 'giris' || $sayfa == 'kayit')) {
    $sayfa = 'anasayfa';
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Güven Medya - Donanım ve Yazılım Desteği</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?php 
    // Arka plan stilleri için body class'larını ayarla
    $splash_sayfalari = ['giris_ekrani', 'giris', 'kayit']; 
    $forum_sayfalari = ['anasayfa', 'konu_detay', 'yeni_konu', 'kullanici_yonetimi']; // YENİ: Admin sayfası da eklendi
    
    if (in_array($sayfa, $splash_sayfalari)) {
        echo 'body-splash'; // Giriş ekranı (şeffaf container)
    } elseif (in_array($sayfa, $forum_sayfalari)) {
        echo 'body-forum'; // Forum sayfaları (koyu arkaplan + beyaz kart)
    }
?>">

    <div class="container">
        <header>
            <h1><a href="index.php">Güven Medya</a></h1>
            <p>Dijital Dünya'nın GÜVEN'ilir Destek Merkezi</p>
            <nav>
                <?php if (isset($_SESSION['kullanici'])): ?>
                    <span>Hoş geldin, <strong><?php echo htmlspecialchars($_SESSION['kullanici']['kullanici_adi']); ?></strong> (Yetki: <?php echo htmlspecialchars($_SESSION['kullanici']['rol']); ?>)</span>
                    <a href="index.php?sayfa=anasayfa">Ana Sayfa</a>
                    <a href="index.php?sayfa=cikis">Çıkış Yap</a>
                <?php else: ?>
                    <a href="index.php?sayfa=anasayfa">Ziyaretçi Olarak Göz At</a>
                    <a href="index.php?sayfa=giris">Giriş Yap</a>
                    <a href="index.php?sayfa=kayit">Kayıt Ol</a>
                <?php endif; ?>
            </nav>
        </header>

        <main>
            <?php
            // YENİ: 'kullanici_yonetimi' de sidebar'lı sayfalara eklendi
            $pages_with_sidebar = ['anasayfa', 'konu_detay', 'yeni_konu', 'kullanici_yonetimi'];
            $show_sidebar = in_array($sayfa, $pages_with_sidebar); 
            ?>

            <?php if ($show_sidebar): ?>
            <div class="layout">
                <aside class="sidebar">
                    <h3>Forum Menüsü</h3>
                    <a href="index.php?sayfa=anasayfa">Tüm Konular</a>
                    <a href="index.php?sayfa=yeni_konu">Yeni Konu Aç</a>
                    <div class="section-title">Kategoriler</div>
                    <a href="#">Donanım</a>
                    <a href="#">Yazılım</a>
                    <a href="#">Ağ & Sunucular</a>
                    <a href="#">Haberler & Duyurular</a>
                    <div class="section-title">Kullanıcı</div>
                    <?php if (isset($_SESSION['kullanici'])): ?>
                        <a href="#">Profilim</a>
                        
                        <?php if ($_SESSION['kullanici']['rol'] == 'admin'): ?>
                            <div class="section-title" style="color:#ee9b00;">Admin Paneli</div>
                            <a href="index.php?sayfa=kullanici_yonetimi" style="color:#ee9b00;">Kullanıcı Yönetimi</a>
                        <?php endif; ?>
                        
                        <a href="index.php?sayfa=cikis">Çıkış</a>
                    <?php else: ?>
                        <a href="index.php?sayfa=giris">Giriş Yap</a>
                        <a href="index.php?sayfa=kayit">Kayıt Ol</a>
                    <?php endif; ?>
                </aside>
                <div class="content">
            <?php else: ?>
                <div class="content-full">
            <?php endif; ?>
            
            <?php
            // Hata veya başarı mesajlarını göster
            if (isset($hata_mesaji)) {
                echo "<div class='mesaj hata'>$hata_mesaji</div>";
            }
            if (isset($kayit_mesaji)) {
                echo "<div class='mesaj bilgi'>$kayit_mesaji</div>";
            }

            // Sayfa yönlendiricisi
            switch ($sayfa) {
                
                // --- 'giris', 'kayit', 'yeni_konu' sayfaları DEĞİŞMEDİ ---
                
                case 'giris':
                    ?>
                    <div class="auth-card">
                        <div class="auth-left"><h2>Hoşgeldiniz!</h2><p>Topluluğumuza katılın ve konular hakkında tartışın. Hesabınız varsa giriş yapın.</p></div>
                        <div class="auth-right">
                            <form action="index.php?sayfa=giris" method="POST" class="forum-form">
                                <input type="hidden" name="action" value="giris">
                                <div><label for="kullanici_adi">Kullanıcı Adı:</label><input type="text" id="kullanici_adi" name="kullanici_adi" required></div>
                                <div><label for="sifre">Şifre:</label><input type="password" id="sifre" name="sifre" required></div>
                                <button type="submit">Giriş Yap</button>
                            </form>
                        </div>
                    </div>
                    <?php
                    break;

                case 'kayit':
                    ?>
                    <div class="auth-card">
                        <div class="auth-left"><h2>Yeni misiniz?</h2><p>Hızlıca kayıt olarak topluluğa katılın. Güvenli ve dost canlısı bir forum.</p></div>
                        <div class="auth-right">
                            <form action="index.php?sayfa=kayit" method="POST" class="forum-form">
                                <input type="hidden" name="action" value="kayit">
                                <div><label for="k_kullanici_adi">Kullanıcı Adı:</label><input type="text" id="k_kullanici_adi" name="kullanici_adi" required></div>
                                <div><label for="k_email">E-posta:</label><input type="email" id="k_email" name="email" required></div>
                                <div><label for="k_sifre">Şifre:</label><input type="password" id="k_sifre" name="sifre" required></div>
                                <button type="submit">Kayıt Ol</button>
                            </form>
                        </div>
                    </div>
                    <?php
                    break;
                
                case 'yeni_konu':
                    if (!isset($_SESSION['kullanici'])) {
                        echo "<div class='mesaj hata'>Konu açabilmek için <a href='index.php?sayfa=giris'>giriş yapmanız</a> gerekmektedir.</div>";
                    } else {
                        ?>
                        <h2>Yeni Konu Aç</h2>
                        <form action="index.php" method="POST" class="forum-form">
                            <input type="hidden" name="action" value="yeni_konu">
                            <div><label for="baslik">Konu Başlığı:</label><input type="text" id="baslik" name="baslik" required></div>
                            <div><label for="icerik">İçerik:</label><textarea id="icerik" name="icerik" rows="10" required></textarea></div>
                            <button type="submit">Konuyu Gönder</button>
                        </form>
                        <?php
                    }
                    break;
                
                // --- 'konu_detay' sayfası DEĞİŞMEDİ (sadece linkler güncellenmişti) ---
                case 'konu_detay':
                    $konu_id = $_GET['id'] ?? 0;
                    $stmt = $pdo->prepare("SELECT konular.*, kullanicilar.kullanici_adi FROM konular LEFT JOIN kullanicilar ON konular.kullanici_id = kullanicilar.id WHERE konular.id = ?");
                    $stmt->execute([$konu_id]);
                    $konu = $stmt->fetch();

                    if (!$konu) {
                        echo "<div class='mesaj hata'>Hata: Konu bulunamadı.</div>";
                    } else {
                        $yazar = $konu['kullanici_adi'] ? htmlspecialchars($konu['kullanici_adi']) : '[Silinmiş Kullanıcı]';
                        echo "<h2>" . htmlspecialchars($konu['baslik']) . "</h2>";
                        echo "<p class='konu-meta'><strong>Yazan:</strong> $yazar | <strong>Tarih:</strong> " . date('d M Y, H:i', strtotime($konu['olusturma_tarihi'])) . "</p>";
                        if($konu['sabitlendi']) { echo "<span class='sabit-ikonu'>📌 Sabitlendi</span>"; }
                        echo "<div class='konu-icerigi'>" . nl2br(htmlspecialchars($konu['icerik'])) . "</div>";
                        
                        // Admin Araçları (Linkler artık çalışıyor)
                        if (isset($_SESSION['kullanici']) && $_SESSION['kullanici']['rol'] == 'admin') {
                            echo "<hr><div class='admin-araclari'>";
                            echo "<strong>Admin Araçları:</strong> ";
                            $sabitle_yazisi = $konu['sabitlendi'] ? 'Sabitlemeyi Kaldır' : 'Başa Sabitle';
                            echo "<a href='index.php?action=toggle_sabitle&id={$konu['id']}'>$sabitle_yazisi</a> | ";
                            echo "<a href='index.php?action=konu_sil&id={$konu['id']}' onclick=\"return confirm('Bu konuyu ve tüm yorumlarını kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!');\" class='admin-sil'>Konuyu Sil</a> | ";
                            echo "<a href='#'>Kullanıcıyı Engelle ($yazar)</a>";
                            echo "</div>";
                        }
                        
                        // Yorumlar Bölümü (Değişiklik yok)
                        echo "<hr><h3>Yorumlar</h3>";
                        $stmtY = $pdo->prepare("SELECT yorumlar.*, kullanicilar.kullanici_adi FROM yorumlar LEFT JOIN kullanicilar ON yorumlar.kullanici_id = kullanicilar.id WHERE yorumlar.konu_id = ? ORDER BY yorumlar.yorum_tarihi ASC");
                        $stmtY->execute([$konu_id]);
                        $yorumlar = $stmtY->fetchAll();
                        if (empty($yorumlar)) {
                            echo "<p><em>Henüz yorum yok. İlk yorumu siz yazın!</em></p>";
                        } else {
                            foreach ($yorumlar as $yr) {
                                $yazarY = $yr['kullanici_adi'] ? htmlspecialchars($yr['kullanici_adi']) : '[Silinmiş Kullanıcı]';
                                $tarihY = date('d M Y, H:i', strtotime($yr['yorum_tarihi'])); 
                                echo "<div class='yorum'><div class='yorum-meta'><strong>$yazarY</strong> <span class='tarih'>$tarihY</span></div>";
                                echo "<div class='yorum-icerik'>" . nl2br(htmlspecialchars($yr['yorum_metni'])) . "</div></div>";
                            }
                        }
                        if (isset($_SESSION['kullanici'])) {
                            echo "<hr><h4>Yorum Yaz</h4>";
                            echo "<form action='index.php' method='POST' class='forum-form'>";
                            echo "<input type='hidden' name='action' value='yeni_yorum'>";
                            echo "<input type='hidden' name='konu_id' value='".htmlspecialchars($konu_id)."'>";
                            echo "<div><textarea name='yorum' rows='5' required></textarea></div>";
                            echo "<button type='submit'>Yorumu Gönder</button>";
                            echo "</form>";
                        } else {
                            echo "<p>Yorum yapmak için <a href='index.php?sayfa=giris'>giriş yapın</a>.</p>";
                        }
                    }
                    break;
                    
                // --- 'anasayfa' ve 'giris_ekrani' sayfaları DEĞİŞMEDİ ---
                case 'anasayfa':
                    echo '<h2>Forum Ana Sayfası</h2>';
                    if (isset($_SESSION['show_welcome']) && $_SESSION['show_welcome'] && isset($_SESSION['kullanici'])) {
                        $username = htmlspecialchars($_SESSION['kullanici']['kullanici_adi']);
                        echo "<div class='welcome-banner'>";
                        echo "<div class='left'><div class='avatar'>" . strtoupper(substr($username,0,1)) . "</div><div><h4>Hoşgeldiniz, $username!</h4><p>Forumda dolaşmaya başlamak için aşağıdaki butona tıklayabilirsiniz.</p></div></div>";
                        echo "<div><a class='btn-browse' href='index.php?sayfa=anasayfa'>Gezmeye Başla</a></div>";
                        echo "</div>";
                        unset($_SESSION['show_welcome']);
                    }
                    if (isset($_SESSION['kullanici'])) {
                        echo '<a href="index.php?sayfa=yeni_konu" class="btn btn-yeni-konu">Yeni Konu Aç</a>';
                    } else {
                        echo '<p>Konu açmak veya yorum yapmak için lütfen <a href="index.php?sayfa=giris">giriş yapın</a>.</p>';
                    }
                    $stmt = $pdo->prepare("SELECT konular.*, kullanicilar.kullanici_adi FROM konular LEFT JOIN kullanicilar ON konular.kullanici_id = kullanicilar.id ORDER BY konular.sabitlendi DESC, konular.olusturma_tarihi DESC");
                    $stmt->execute();
                    $konular = $stmt->fetchAll();
                    echo '<hr><h3>Tüm Konular</h3>';
                    if (empty($konular)) {
                        echo "<p>Maalesef böyle bir konu daha önce açılmamış, her şeyin ilki vardır. İlk konuyu sen aç!</p>";
                    } else {
                        foreach ($konular as $konu) {
                            $yazar = $konu['kullanici_adi'] ? htmlspecialchars($konu['kullanici_adi']) : '[Silinmiş Kullanıcı]';
                            $kutu_class = $konu['sabitlendi'] ? 'konu-kutusu sabitlenmis' : 'konu-kutusu';
                            echo "<div class='$kutu_class'>";
                            if($konu['sabitlendi']) { echo "<span class='sabit-ikonu'>📌 Sabitlendi</span>"; }
                            echo "<h4><a href='index.php?sayfa=konu_detay&id={$konu['id']}'>" . htmlspecialchars($konu['baslik']) . "</a></h4>";
                            echo "<p class='konu-meta'>Yazan: <strong>$yazar</strong> | Tarih: " . date('d M Y', strtotime($konu['olusturma_tarihi'])) . "</p>";
                            echo "</div>";
                        }
                    }
                    break;

                case 'giris_ekrani':
                default:
                    ?>
                    <div class="giris-secenekleri">
                        <h2>Güven Medya'ya Hoş Geldiniz!</h2>
                        <p>Bilgisayar dünyası hakkında bilgi paylaşmak, öğrenmek ve tecrübelerinizi aktarmak için doğru yerdesiniz.</p>
                        <a href="index.php?sayfa=giris" class="btn">Giriş Yap</a>
                        <a href="index.php?sayfa=kayit" class="btn">Kayıt Ol</a>
                        <a href="index.php?sayfa=anasayfa" class="btn btn-guest">Üyesiz Olarak Devam Et "Bakıp Çıkacağım" </a>
                    </div>
                    <?php
                    break;
                
                // ----------------------------------------------------
                // --- YENİ EKLENDİ: KULLANICI YÖNETİMİ SAYFASI ---
                // ----------------------------------------------------
                case 'kullanici_yonetimi':
                    // 1. GÜVENLİK: Sadece adminler bu sayfayı görebilir.
                    if (!isset($_SESSION['kullanici']) || $_SESSION['kullanici']['rol'] != 'admin') {
                        echo "<div class='mesaj hata'>Bu sayfayı görüntüleme yetkiniz yok.</div>";
                        break; // Sayfanın geri kalanını yüklemeyi durdur.
                    }
                    
                    // 2. Tüm kullanıcıları veritabanından çek
                    echo '<h2>Kullanıcı Yönetimi</h2>';
                    echo '<p>Kullanıcıların rollerini buradan değiştirebilirsiniz.</p>';
                    
                    $stmt = $pdo->prepare("SELECT id, kullanici_adi, email, rol, kayit_tarihi FROM kullanicilar ORDER BY kayit_tarihi DESC");
                    $stmt->execute();
                    $kullanicilar = $stmt->fetchAll();
                    
                    // 3. Kullanıcıları bir tablo içinde listele
                    echo "<div class='kullanici-listesi'>"; // CSS ile stil vermek için
                    echo "<table>";
                    echo "<thead><tr><th>Kullanıcı Adı</th><th>Email</th><th>Rol</th><th>Kayıt Tarihi</th><th>Eylem</th></tr></thead>";
                    echo "<tbody>";
                    
                    foreach ($kullanicilar as $k) {
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($k['kullanici_adi']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($k['email']) . "</td>";
                        
                        // Role göre renkli etiket yap
                        if ($k['rol'] == 'admin') {
                            echo "<td><span class='rol-etiket admin'>" . htmlspecialchars($k['rol']) . "</span></td>";
                        } else {
                            echo "<td><span class='rol-etiket uye'>" . htmlspecialchars($k['rol']) . "</span></td>";
                        }
                        
                        echo "<td>" . date('d M Y', strtotime($k['kayit_tarihi'])) . "</td>";
                        
                        // Eylem Linki
                        echo "<td>";
                        // Admin kendi kendini değiştiremesin
                        if ($k['id'] == $_SESSION['kullanici']['id']) {
                            echo "<span class='eylem-link devre-disi'>(Kendiniz)</span>";
                        } else {
                            // Rolü 'uye' ise "Admin Yap" linki göster
                            if ($k['rol'] == 'uye') {
                                echo "<a href='index.php?action=toggle_rol&id={$k['id']}' class='eylem-link admin-yap'>Admin Yap</a>";
                            } 
                            // Rolü 'admin' ise "Üye Yap" linki göster
                            else {
                                echo "<a href='index.php?action=toggle_rol&id={$k['id']}' class='eylem-link uye-yap'>Üye Yap</a>";
                            }
                        }
                        echo "</td>";
                        
                        echo "</tr>";
                    }
                    
                    echo "</tbody></table>";
                    echo "</div>"; // .kullanici-listesi sonu
                    
                    break;

            } // switch sonu
            ?>
                </div> <?php if ($show_sidebar): ?>
            </div> <?php endif; ?>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> Güven Medya. Tüm hakları saklıdır.</p>
        </footer>
    </div>

</body>
</html>