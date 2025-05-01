<?php
require 'db.php';
session_start();

$kayit_basarili = isset($_GET['kayit_basarili']);
$giris_hatali = isset($_GET['giris_hatali']);
$kayit_hatali = isset($_GET['kayit_hatali']);

// Kayıt işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $kullanici = $_POST['username_reg'];
    $sifre = password_hash($_POST['password_reg'], PASSWORD_DEFAULT);

    $kontrol = $veritabani->prepare("SELECT * FROM users WHERE username = ?");
    $kontrol->execute([$kullanici]);

    if ($kontrol->rowCount() > 0) {
        header("Location: yorum_sayfasi.php?kayit_hatali=1");
        exit;
    } else {
        $sorgu = $veritabani->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $sorgu->execute([$kullanici, $sifre]);
        header("Location: yorum_sayfasi.php?kayit_basarili=1");
        exit;
    }
}

// Giriş işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $kullanici = $_POST['username_login'];
    $sifre = $_POST['password_login'];

    $stmt = $veritabani->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$kullanici]);
    $user = $stmt->fetch();

    if ($user && password_verify($sifre, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        header("Location: yorum_sayfasi.php");
        exit;
    } else {
        header("Location: yorum_sayfasi.php?giris_hatali=1");
        exit;
    }
}

// Çıkış işlemi
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: yorum_sayfasi.php");
    exit;
}

// Yorum ekleme (sadece giriş yapılmışsa)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["comment"]) && isset($_SESSION["user_id"]) && isset($_POST["rating"])) {
    $yorum = htmlspecialchars($_POST["yorum_text"]); // Metin yorumunu al
    $rating = filter_var($_POST["rating"], FILTER_SANITIZE_NUMBER_INT);
    if ($rating >= 1 && $rating <= 5) {
        $stmt = $veritabani->prepare("INSERT INTO comments (user_id, comment, rating) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION["user_id"], $yorum, $rating]);
    }
}

// Yorum silme (sadece giriş yapılmışsa)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_comment"]) && isset($_SESSION["user_id"])) {
    $stmt = $veritabani->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST["comment_id"], $_SESSION["user_id"]]);
}

// Yorumları çek (giriş yapılmışsa)
if (isset($_SESSION["user_id"])) {
    $comments = $veritabani->query("SELECT comments.id, comments.comment, comments.rating, users.username, comments.user_id FROM comments JOIN users ON comments.user_id = users.id ORDER BY comments.id DESC")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yorum Sayfası</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px;
        }
        h2, h3 {
            color: #007bff;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="text"],
        input[type="password"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 12px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: #dc3545;
            margin-top: 10px;
            text-align: center;
        }
        .success {
            color: #28a745;
            margin-top: 10px;
            text-align: center;
        }
        .comment {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
            display: flex;
            flex-direction: column;
        }
        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }
        .comment strong {
            color: #007bff;
        }
        .rating-display {
            color: gold;
            font-size: 1.5em;
        }
        .rating-display span {
            cursor: pointer;
            margin-right: 5px;
        }
        .rating-display .full-star {
            color: gold;
        }
        .rating-display .empty-star {
            color: #ccc;
        }
        .logout-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #dc3545;
            text-decoration: none;
        }
        .logout-link:hover {
            text-decoration: underline;
        }
        .yorum-bolumu {
            margin-top: 30px;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }
        .rating-select {
            display: flex;
            gap: 5px;
            font-size: 1.5em;
            color: #ccc;
            margin-bottom: 10px;
        }
        .rating-select span {
            cursor: pointer;
        }
        .rating-select .active {
            color: gold;
        }
        /* Mobil Uyum */
        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
            h2 {
                font-size: 1.5em;
            }
            h3 {
                font-size: 1.2em;
            }
            button {
                padding: 10px;
                font-size: 0.9em;
            }
            .comment-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .rating-display {
                margin-top: 5px;
            }
            .rating-select {
                font-size: 1.2em;
                gap: 3px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Yorum Sayfası</h2>

        <?php if (!isset($_SESSION["user_id"])): ?>
            <?php if ($kayit_basarili): ?>
                <p class="success">Kayıt başarılı! Şimdi giriş yapabilirsiniz.</p>
            <?php endif; ?>
            <?php if ($giris_hatali): ?>
                <p class="error">Hatalı kullanıcı adı veya şifre!</p>
            <?php endif; ?>
            <?php if ($kayit_hatali): ?>
                <p class="error">Bu kullanıcı adı zaten kayıtlı!</p>
            <?php endif; ?>

            <h3>Kayıt Ol</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="username_reg">Kullanıcı Adı:</label>
                    <input type="text" id="username_reg" name="username_reg" required>
                </div>
                <div class="form-group">
                    <label for="password_reg">Şifre:</label>
                    <input type="password" id="password_reg" name="password_reg" required>
                </div>
                <button type="submit" name="register">Kayıt Ol</button>
            </form>

            <hr style="border-top: 1px solid #ccc; margin: 20px 0;">

            <h3>Giriş Yap</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="username_login">Kullanıcı Adı:</label>
                    <input type="text" id="username_login" name="username_login" required>
                </div>
                <div class="form-group">
                    <label for="password_login">Şifre:</label>
                    <input type="password" id="password_login" name="password_login" required>
                </div>
                <button type="submit" name="login">Giriş Yap</button>
            </form>

        <?php else: ?>
            <div class="yorum-bolumu">
                <h3>Yorum Yap ve Puanla</h3>
                <form method="POST" class="comment-form">
                    <div class="form-group">
                        <label for="yorum_text">Yorumunuz:</label>
                        <textarea id="yorum_text" name="yorum_text" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Favori Seç:</label>
                        <div class="rating-select">
                            <span data-rating="1">☆</span>
                            <span data-rating="2">☆</span>
                            <span data-rating="3">☆</span>
                            <span data-rating="4">☆</span>
                            <span data-rating="5">☆</span>
                            <input type="hidden" name="rating" id="selected_rating" value="0">
                        </div>
                    </div>
                    <button type="submit" name="comment">Gönder</button>
                </form>
                <hr style="border-top: 1px solid #ccc; margin: 20px 0;">
                <h3>Mevcut Yorumlar ve Puanlar</h3>
                <?php if (isset($comments) && !empty($comments)): ?>
                    <?php foreach ($comments as $c): ?>
                        <div class="comment">
                            <div class="comment-header">
                                <strong><?= htmlspecialchars($c["username"]) ?></strong>
                                <div class="rating-display">
                                    <?php for ($i = 0; $i < $c["rating"]; $i++): ?>
                                        <span class="full-star">★</span>
                                    <?php endfor; ?>
                                    <?php for ($i = 0; $i < (5 - $c["rating"]); $i++): ?>
                                        <span class="empty-star">☆</span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <?= nl2br(htmlspecialchars($c["comment"])) ?> <br>
                            <?php if ($c["user_id"] == $_SESSION["user_id"]): ?>
                                <form method="POST" style="display:inline; margin-top: 10px;">
                                    <input type="hidden" name="comment_id" value="<?= $c["id"] ?>">
                                    <button type="submit" name="delete_comment" style="background-color: #dc3545; padding: 8px 10px; font-size: 0.8em;">Sil</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><span role="img" aria-label="üzgün">😔</span> Henüz yorum veya puan bulunmuyor.</p>
                <?php endif; ?>
                <a href="?logout=1" class="logout-link">Çıkış Yap</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ratingStars = document.querySelectorAll('.rating-select span');
            const selectedRatingInput = document.getElementById('selected_rating');

            ratingStars.forEach(star => {
                star.addEventListener('click', function() {
                    const ratingValue = parseInt(this.dataset.rating);
                    selectedRatingInput.value = ratingValue;

                    ratingStars.forEach(s => {
                        s.classList.remove('active');
                        if (parseInt(s.dataset.rating) <= ratingValue) {
                            s.textContent = '★';
                            s.classList.add('active');
                        } else {
                            s.textContent = '☆';
                        }
                    });
                });

                star.addEventListener('mouseover', function() {
                    const ratingValue = parseInt(this.dataset.rating);
                    ratingStars.forEach(s => {
                        if (parseInt(s.dataset.rating) <= ratingValue) {
                            s.textContent = '★';
                        } else {
                            s.textContent = '☆';
                        }
                    });
                });

                star.addEventListener('mouseout', function() {
                    const currentRating = parseInt(selectedRatingInput.value);
                    ratingStars.forEach(s => {
                        if (parseInt(s.dataset.rating) <= currentRating) {
                            s.textContent = '★';
                            s.classList.add('active');
                        } else {
                            s.textContent = '☆';
                            s.classList.remove('active');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>