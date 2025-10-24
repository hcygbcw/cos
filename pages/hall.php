<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include '../config/db.php';
include '../actions/order.php';

// 新增：获取当前登录用户信息（用于展示发布者头像昵称）
$current_user = null;
if ($_SESSION['role'] == 'user') {
    $user_stmt = $pdo->prepare("SELECT nickname, avatar FROM users WHERE id = ?");
    $user_stmt->execute([$_SESSION['user_id']]);
    $current_user = $user_stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>接单大厅</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
</head>
<body>
<!-- 公告弹窗 -->
<div id="notice-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 90%; max-width: 500px; position: relative;">
        <button id="close-notice" style="position: absolute; top: 10px; right: 10px; background: transparent; border: none; font-size: 20px; cursor: pointer; color: #999;">&times;</button>
        <h3 id="notice-title" style="color: #6b72ff; margin-bottom: 15px;"></h3>
        <p id="notice-content" style="font-size: 14px; line-height: 1.6;"></p>
    </div>
</div>
<!-- 引入公共JS -->
<script src="../js/common.js"></script>
    <div class="page-header">
        <h1>🎪 接单大厅 🎪</h1>
    </div>
    <div class="card-container">
        <?php if ($_SESSION['role'] == 'user'): ?>
            <div class="card form-card">
                <h2>发布你的订单需求</h2>
                <?php if (isset($error)): ?>
                    <div style="color: #ff6666; text-align: center; margin-bottom: 15px; font-size: 13px;">
                        (｡•́︿•̀｡) <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div style="color: #66cc99; text-align: center; margin-bottom: 15px; font-size: 13px;">
                        (≧∇≦)ﾉ <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                <!-- 新增：发布者信息展示 -->
                <div style="display: flex; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #f0f2f5;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; overflow: hidden; margin-right: 12px;">
                        <img src="<?php echo $current_user['avatar'] ? '../uploads/avatars/' . $current_user['avatar'] : '../images/default-avatar.png'; ?>" alt="用户头像" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div>
                        <p style="font-weight: 500; color: #333;"><?php echo $current_user['nickname']; ?></p>
                        <p style="font-size: 12px; color: #999;">普通用户</p>
                    </div>
                </div>
                <form method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="publish">
                    <div class="form-group">
                        <label for="title">订单标题</label>
                        <input type="text" id="title" name="title" class="form-control" required placeholder="如：漫展日常妆、cos角色毛造型">
                    </div>
                    <div class="form-group">
                        <label for="content">订单详情</label>
                        <textarea id="content" name="content" class="form-control" required placeholder="说明时间、地点、具体需求等"></textarea>
                    </div>
                    <!-- 新增：地址字段 -->
                    <div class="form-group">
                        <label for="address">服务地址</label>
                        <input type="text" id="address" name="address" class="form-control" placeholder="如：XX市XX区XX漫展中心">
                    </div>
                    <!-- 新增：联系方式字段 -->
                    <div class="form-group">
                        <label>联系方式</label>
                        <div style="display: flex; gap: 10px;">
                            <select name="contact_type" class="form-control" style="flex: 1;">
                                <option value="">选择联系方式</option>
                                <option value="qq">QQ</option>
                                <option value="wechat">微信</option>
                            </select>
                            <input type="text" name="contact_info" class="form-control" style="flex: 2;" placeholder="输入账号内容">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="order_img">上传参考图片（可选）</label>
                        <input type="file" id="order_img" name="order_img" accept="image/jpg,image/jpeg,image/png">
                        <p style="font-size: 12px; color: #999; margin-top: 5px;">支持JPG、PNG格式，大小不超过5MB</p>
                    </div>
                    <button type="submit" class="btn btn-primary">发布订单</button>
                </form>
            </div>
        <?php endif; ?>
        <div class="card">
            <h2 style="font-size: 16px; margin-bottom: 15px; color: #ff66b2; text-align: center;">待接单订单列表</h2>
            <div class="order-list">
                <?php
                // 新增：查询时关联用户头像字段
                $stmt = $pdo->prepare("SELECT o.*, u.nickname as user_nickname, u.avatar as user_avatar FROM orders o JOIN users u ON o.user_id = u.id WHERE o.status = 'pending' ORDER BY o.created_at DESC");
                $stmt->execute();
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (empty($orders)) {
                    echo "<p style='grid-column: 1/-1; text-align: center; color: #666; font-size: 14px; padding: 20px;'>暂无待接单订单 (´･ω･`)</p>";
                } else {
                    foreach ($orders as $order) {
                ?>
                    <div class="card order-card">
                        <?php if ($order['order_img']): ?>
                            <div class="img-container">
                                <img src="../uploads/orders/<?php echo $order['order_img']; ?>" alt="订单参考图">
                            </div>
                        <?php endif; ?>
                        <!-- 新增：展示发布者头像和昵称 -->
                        <div style="display: flex; align-items: center; margin-bottom: 10px;">
                            <div style="width: 36px; height: 36px; border-radius: 50%; overflow: hidden; margin-right: 8px;">
                                <img src="<?php echo $order['user_avatar'] ? '../uploads/avatars/' . $order['user_avatar'] : '../images/default-avatar.png'; ?>" alt="发布者头像" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <span style="font-size: 13px; font-weight: 500;"><?php echo $order['user_nickname']; ?></span>
                        </div>
                        <h3><?php echo $order['title']; ?></h3>
                        <p><strong>发布人：</strong><?php echo $order['user_nickname']; ?></p>
                        <p><strong>详情：</strong><?php echo $order['content']; ?></p>
                        <!-- 新增：展示地址 -->
                        <?php if ($order['address']): ?>
                            <p><strong>地址：</strong><?php echo $order['address']; ?></p>
                        <?php endif; ?>
                        <p><strong>发布时间：</strong><?php echo $order['created_at']; ?></p>
                        
                        <?php if ($_SESSION['role'] == 'artist'): ?>
                            <form method="post" action="" style="margin-top: 12px;">
                                <input type="hidden" name="action" value="accept">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" class="btn btn-primary">接受订单</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php } } ?>
            </div>
        </div>
    </div>
    <!-- 底部导航栏 -->
    <nav class="bottom-nav">
        <a href="hall.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'hall.php' ? 'active' : ''; ?>">
            <i class="fa fa-list"></i>
            <span>接单大厅</span>
        </a>
        <a href="orders.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
            <i class="fa fa-shopping-cart"></i>
            <span>已接订单</span>
        </a>
        <a href="profile.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <i class="fa fa-user"></i>
            <span>个人中心</span>
        </a>
    </nav>
</body>
</html>