<?php
$host = 'localhost';
$port = '5433';
$dbname = 'db';
$user = 'postgres';
$password = '12345678';

try {
    $veritabani = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $veritabani->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Veritabanı bağlantı hatası: " . $e->getMessage();
    exit;
}
?>
