<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Güven Medya - Üye Ol</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="card shadow-sm giris-kutusu">
        
        <div class="card-body p-4">
            
            <h1 class="h3 text-center">Üye Ol</h1>
            <p class="text-center text-muted mb-4">Güven Medya ailesine katılın!</p>

            <form>
                <div class="mb-3 text-start">
                    <label for="kullaniciAdi" class="form-label">Kullanıcı Adı:</label>
                    <input type="text" class="form-control" id="kullaniciAdi" required>
                </div>
                
                <div class="mb-3 text-start">
                    <label for="email" class="form-label">E-posta Adresi:</label>
                    <input type="email" class="form-control" id="email" required>
                </div>

                <div class="mb-3 text-start">
                    <label for="sifre" class="form-label">Şifre:</label>
                    <input type="password" class="form-control" id="sifre" required>
                </div>

                <div class="mb-3 text-start">
                    <label for="sifreTekrar" class="form-label">Şifre (Tekrar):</label>
                    <input type="password" class="form-control" id="sifreTekrar" required>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-success">Kayıt Ol</button>
                </div>

            </form>

            <p class="text-center mt-3">
                Zaten üye misin? <a href="index.php">Giriş Yap</a>
            </p>

        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>