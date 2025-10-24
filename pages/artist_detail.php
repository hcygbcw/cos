<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include '../config/db.php';

$artist_id = $_GET['id'] ?? 0;
if (empty($artist_id)) {
    header("Location: hall.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'artist'");
$stmt->execute([$artist_id]);
$artist = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$artist) {
    header("Location: hall.php");
    exit();
}

$stmt_ratings = $pdo->prepare("SELECT r.*, u.nickname, u.avatar FROM ratings r JOIN users u ON r.user_id = u.id WHERE r.artist_id = ? ORDER BY r.created_at DESC");
$stmt_ratings->execute([$artist_id]);
$ratings = $stmt_ratings->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $artist['nickname']; ?>的主页</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="page-header">
        <h1><?php echo $artist['nickname']; ?>的主页</h1>
    </div>

    <div class="card-container">
        <div class="card form-card">
            <!-- 头像和评分 -->
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px; justify-content: center;">
                <div class="avatar-preview" style="width: 80px; height: 80px;">
                    <img src="../uploads/avatars/<?php echo $artist['avatar']; ?>" alt="用户头像">
                </div>
                <div>
                    <h2 style="margin-bottom: 5px;"><?php echo $artist['nickname']; ?></h2>
                    <div class="rating">
                        <span><?php echo $artist['avg_rating']; ?>⭐</span>
                        <span style="color: #888; margin-left: 8px;">（共<?php echo $artist['rating_count']; ?>人评价）</span>
                    </div>
                </div>
            </div>

            <!-- 技能标签 -->
            <div class="skill-tags" style="justify-content: center;">
                <?php if ($artist['skills']): ?>
                    <?php foreach (explode(',', $artist['skills']) as $skill): ?>
                        <span class="skill-tag"><?php echo trim($skill); ?></span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="skill-tag">暂无技能标签</span>
                <?php endif; ?>
            </div>

            <!-- 自我介绍 -->
            <div style="margin: 20px 0;">
                <h3 style="font-size: 14px; margin-bottom: 10px; color: #333;">自我介绍</h3>
                <p style="font-size: 13px; color: #666; line-height: 1.6; padding: 10px; background: #f9fafe; border-radius: 8px;">
                    <?php echo $artist['intro'] ?: '暂无自我介绍'; ?>
                </p>
            </div>

            <!-- 作品展示 -->
            <div style="margin: 20px 0;">
                <h3 style="font-size: 14px; margin-bottom: 10px; color: #333;">作品展示</h3>
                <?php if ($artist['works_imgs']): ?>
                    <div class="works-gallery">
                        <?php foreach (explode(',', $artist['works_imgs']) as $img): ?>
                            <div class="works-item">
                                <img src="../uploads/works/<?php echo $img; ?>" alt="作品">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="font-size: 13px; color: #666; padding: 10px; text-align: center; background: #f9fafe; border-radius: 8px;">暂无作品展示</p>
                <?php endif; ?>
            </div>

            <!-- 用户评价 -->
            <div style="margin: 20px 0;">
                <h3 style="font-size: 14px; margin-bottom: 10px; color: #333;">用户评价</h3>
                <?php if ($ratings): ?>
                    <div class="review-list">
                        <?php foreach ($ratings as $rating): ?>
                            <div class="review-item">
                                <div class="review-avatar">
                                    <img src="../uploads/avatars/<?php echo $rating['avatar']; ?>" alt="用户头像">
                                </div>
                                <div class="review-content">
                                    <div class="review-header">
                                        <span class="review-author"><?php echo $rating['nickname']; ?></span>
                                        <div class="rating">
                                            <span><?php echo $rating['score']; ?>⭐</span>
                                        </div>
                                    </div>
                                    <p class="review-text"><?php echo $rating['comment'] ?: '暂无评论内容'; ?></p>
                                    <div class="review-time"><?php echo $rating['created_at']; ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="font-size: 13px; color: #666; padding: 10px; text-align: center; background: #f9fafe; border-radius: 8px;">暂无用户评价</p>
                <?php endif; ?>
            </div>
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
