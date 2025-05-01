<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Yorum ekleme (bu dosya üzerinden de yorum eklemek isterseniz aktif edin)
/*
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['yorum'])) {
    $yorum = htmlspecialchars($_POST['yorum']);
    $kullanici_id = $_SESSION['user_id'];
    $stmt = $veritabani->prepare("INSERT INTO comments (user_id, comment, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$kullanici_id, $yorum]);
    header("Location: yorumlar.php"); // Yorum sonrası sayfayı yenile
    exit;
}
*/

if (isset($_GET['sil'])) {
    $silID = (int) $_GET['sil'];
    $veritabani->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?")->execute([$silID, $_SESSION['user_id']]);
    header("Location: yorumlar.php"); // Silme sonrası sayfayı yenile
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yorumlar</title>
    <style>
        body { font-family: Arial; background: #fff; padding: 20px; }
        .yorum-formu, .yorum-listesi { max-width: 600px; margin: auto; }
        textarea { width: 100%; height: 100px; padding: 10px; }
        button { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; }
        .yorum { background: #f4f4f4; margin: 10px 0; padding: 10px; border-radius: 5px; }
        .yorum small { color: gray; }
        .sil-link { color: red; float: right; text-decoration: none; }
        .sil-link:hover { text-decoration: underline; }
        .logout-link { display: block; margin-top: 10px; color: red; text-decoration: none; }
        .logout-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="yorum-formu">
        <h2>Yorum Yap</h2>
        <form method="post">
            <textarea name="yorum" placeholder="Yorumunuzu yazınız..." required></textarea><br>
            <button type="submit">Gönder</button>
        </form>
        <p><a href="logout.php">Çıkış yap</a></p>
    </div>

    <div class="yorum-listesi">
        <h3>Yorumlar</h3>
        <?php
        $yorumlar = $veritabani->query("SELECT comments.id, comments.comment, comments.created_at, users.username
                                         FROM comments JOIN users ON comments.user_id = users.id
                                         ORDER BY comments.created_at DESC");
        foreach ($yorumlar as $yorum) {
            echo "<div class='yorum'>";
            echo "<strong>" . htmlspecialchars($yorum['username']) . "</strong>: " . htmlspecialchars($yorum['comment']);
            echo "<br><small>" . $yorum['created_at'] . "</small>";
            if ($yorum['username'] == $_SESSION['username']) {
                echo "<a class='sil-link' href='?sil=" . $yorum['id'] . "' onclick='return confirm(\"Silmek istediğinize emin misiniz?\")'>Sil</a>";
            }
            echo "</div>";
        }
        ?>
    </div>
    <a href="logout.php" class="logout-link">Çıkış Yap</a>
</body>
</html>