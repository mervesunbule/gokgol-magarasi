<?php
require 'db.php';
session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: yorum_sayfasi.php"); // Zaten giriş yapmış kullanıcıyı yorum sayfasına yönlendir
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici = $_POST['username'];
    $sifre = $_POST['password'];

    $stmt = $veritabani->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$kullanici]);
    $user = $stmt->fetch();

    if ($user && password_verify($sifre, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"]; // Kullanıcı adını da oturuma ekleyelim (yorumlar.php için)
        header("Location: yorum_sayfasi.php");
        exit;
    } else {
        $hata = "Hatalı kullanıcı adı veya şifre!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giriş Yap</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 400px; margin: auto; padding: 20px; background: white; border-radius: 10px; margin-top: 40px; }
        input, button { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { background-color: #007bff; color: white; border: none; }
        button:hover { background-color: #0056b3; }
        .error { color: red; text-align: center; }
        .info { color: green; text-align: center; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Giriş Yap</h2>
        <?php if (isset($hata)) echo "<p class='error'>$hata</p>"; ?>
        <?php if (isset($_GET['kayit_basarili'])) echo "<p class='info'>Kayıt başarılı! Giriş yapabilirsiniz.</p>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Kullanıcı Adı" required>
            <input type="password" name="password" placeholder="Şifre" required>
            <button type="submit">Giriş Yap</button>
        </form>
        <p style="text-align:center;">Hesabınız yok mu? <a href="register.php">Kayıt ol</a></p>
    </div>
</body>
</html>