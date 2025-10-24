<?php
$host = 'localhost';
$dbname = 'WSToolbox';
$username = 'root';
$password = '896252036';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("数据库连接失败！请检查配置：" . $e->getMessage());
}
?>