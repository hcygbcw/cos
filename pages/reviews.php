<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'artist') {
    header("Location: login.php");
    exit();
}
include '../config/db.php';

$artist_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT r.*, u.nickname, u.avatar FROM ratings r JOIN users u ON r.user_id = u.id WHERE r.artist_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$artist_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的评论</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="page-header">
        <h1>我的评论</h1>
    </div>

    <div class="card-container">
        <div class="card form-card">
            <?php if (empty($reviews)): ?>
                <p style="text-align: center; color: #666; font-size: 14px; padding: 30px;">暂无用户评价</p>
            <?php else: ?>
                <div class="review-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-avatar">
                                <img src="../uploads/avatars/<?php echo $review['avatar']; ?>" alt="用户头像">
                            </div>
                            <div class="review-content">
                                <div class="review-header">
                                    <span class="review-author"><?php echo $review['nickname']; ?></span>
                                    <div class="rating">
                                        <span><?php echo $review['score']; ?>⭐</span>
                                    </div>
                                </div>
                                <p class="review-text"><?php echo $review['comment'] ?: '暂无评论内容'; ?></p>
                                <div class="review-time"><?php echo $review['created_at']; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 底部导航栏 -->
    <nav class="bottom-nav">
        <a href="hall.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'hall.php' ? 'active' : ''; ?>">
            <i class="fa fa-th-large"></i>
            <span>接单大厅</span>
        </a>
        <a href="orders.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
            <i class="fa fa-shopping-bag"></i>
            <span>我的订单</span>
        </a>
        <a href="profile.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <i class="fa fa-user"></i>
            <span>个人中心</span>
        </a>
    </nav>
</body>
</html>
