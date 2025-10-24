<?php
include '../config/db.php';

// 获取当前显示的公告
$stmt = $pdo->prepare("SELECT title, content FROM notices WHERE status = 1 ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$notice = $stmt->fetch(PDO::FETCH_ASSOC);

if ($notice) {
    echo json_encode([
        'status' => 'success',
        'notice' => $notice
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => '暂无公告'
    ]);
}
?>