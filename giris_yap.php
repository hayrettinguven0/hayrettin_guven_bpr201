<!DOCTYPE html>
<html lang="TR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class= " card shadow-sm giris-kutusu">
        <div class="card-body p-4">
            <h1 class="h3 text-center">Giriş Yap</h1>
            <p class="text-center text-muted mb-4">Hesabınıza giriş yapın</p>
            <form>
                <div class="mb-3 text-start">
                    <label for="kullaniciAdiEmail" class="form-label">Kullanıcı Adı veya E-posta Adresi:</label>
                    <input type="text" class="form-control" id="kullaniciAdiEmail" required>
    </div>
    <div class="mb-3 text-start">
        <label for="sifre" class="form-label">Şifre:</label>
        <input type="password" class="form-control" id="sifre" required> 
    </div>
    <div class="d-grid mt-4">
        <button type="submit" class="btn btn-primary">Giriş Yap</button>
</div>
            </form> 
            <p class="text-center mt-3">
                Hesabın mı Yok? Hemen👉<a href="kayit_ol.php"> Kaydol </a>
            </p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>    

    
</body>
</html>