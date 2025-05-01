<?php
require 'db.php';
session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: yorum_sayfasi.php"); // Zaten giriş yapmış kullanıcıyı yorum sayfasına yönlendir
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici = $_POST['username'];
    $sifre = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $kontrol = $veritabani->prepare("SELECT * FROM users WHERE username = ?");
    $kontrol->execute([$kullanici]);

    if ($kontrol->rowCount() > 0) {
        $hata = "Bu kullanıcı adı zaten kayıtlı!";
    } else {
        $sorgu = $veritabani->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $sorgu->execute([$kullanici, $sifre]);
        header("Location: login.php?kayit_basarili=1"); // Kayıt sonrası giriş sayfasına yönlendir
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kayıt Ol</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 400px; margin: auto; padding: 20px; background: white; border-radius: 10px; margin-top: 40px; }
        input, button { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { background-color: #28a745; color: white; border: none; }
        button:hover { background-color: #218838; }
        .error { color: red; text-align: center; }
        .info { color: green; text-align: center; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Kayıt Ol</h2>
        <?php if (isset($hata)) echo "<p class='error'>$hata</p>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Kullanıcı Adı" required>
            <input type="password" name="password" placeholder="Şifre" required>
            <button type="submit">Kayıt Ol</button>
        </form>
        <p style="text-align:center;">Zaten hesabınız var mı? <a href="login.php">Giriş yap</a></p>
    </div>
</body>
</html>