<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include '../config/db.php';
include '../actions/order.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的订单</title>
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
        <h1>我的订单</h1>
    </div>

        <div class="card">
            <div class="order-list">
                <?php
                $user_id = $_SESSION['user_id'];
                $role = $_SESSION['role'];
                if ($role == 'user') {
                    $stmt = $pdo->prepare("SELECT o.*, a.nickname as artist_nickname, a.intro, a.id as artist_id, a.avatar as artist_avatar FROM orders o LEFT JOIN users a ON o.artist_id = a.id WHERE o.user_id = ? ORDER BY o.created_at DESC");
                } else {
                    // 新增：查询时关联用户头像和联系方式字段
                    $stmt = $pdo->prepare("SELECT o.*, u.nickname as user_nickname, u.avatar as user_avatar, o.contact_type, o.contact_info FROM orders o JOIN users u ON o.user_id = u.id WHERE o.artist_id = ? ORDER BY o.created_at DESC");
                }
                $stmt->execute([$user_id]);
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (empty($orders)) {
                    echo "<p style='grid-column: 1/-1; text-align: center; color: #666; font-size: 14px; padding: 30px;'>暂无订单</p>";
                } else {
                    foreach ($orders as $order) {
                ?>
                    <div class="card order-card">
                        <?php if ($order['order_img']): ?>
                            <div class="img-container">
                                <img src="../uploads/orders/<?php echo $order['order_img']; ?>" alt="订单参考图">
                            </div>
                        <?php endif; ?>
                        <h3><?php echo $order['title']; ?></h3>
                        <div class="order-meta">
                            <span><?php echo $order['created_at']; ?></span>
                            <span>
                                <?php
                                switch ($order['status']) {
                                    case 'pending': echo '<span style="color: #6b72ff;">待接单</span>'; break;
                                    case 'accepted': echo '<span style="color: #3b82f6;">已接单</span>'; break;
                                    case 'completed': echo '<span style="color: #10b981;">已完成</span>'; break;
                                    case 'cancelled': echo '<span style="color: #999;">已撤回</span>'; break;
                                }
                                ?>
                            </span>
                        </div>
                        <p><strong>详情：</strong><?php echo $order['content']; ?></p>
                        <!-- 新增：展示地址 -->
                        <?php if ($order['address']): ?>
                            <p><strong>地址：</strong><?php echo $order['address']; ?></p>
                        <?php endif; ?>
                        
                        <?php if ($role == 'user'): ?>
                            <p><strong>接单者：</strong>
                                <?php if ($order['artist_nickname']): ?>
                                    <!-- 新增：展示接单者头像 -->
                                    <div style="display: flex; align-items: center;">
                                        <div style="width: 32px; height: 32px; border-radius: 50%; overflow: hidden; margin-right: 8px;">
                                            <img src="<?php echo $order['artist_avatar'] ? '../uploads/avatars/' . $order['artist_avatar'] : '../images/default-avatar.png'; ?>" alt="接单者头像" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                        <a href="artist_detail.php?id=<?php echo $order['artist_id']; ?>" style="color: #6b72ff; text-decoration: none;">
                                            <?php echo $order['artist_nickname']; ?>
                                        </a>
                                    </div>
                                <?php else: ?>
                                   
                                <?php endif; ?>
                            </p>
                            
                            <!-- 订单撤回按钮 -->
                            <?php if ($order['status'] == 'pending'): ?>
                                <form method="post" action="" style="margin-top: 8px; display: inline-block; width: 48%;">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" class="btn btn-secondary">撤回订单</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] == 'accepted'): ?>
                                <form method="post" action="" style="margin-top: 12px;">
                                    <input type="hidden" name="action" value="complete">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <div class="form-group" style="margin-bottom: 10px;">
                                        <label style="font-size: 13px;">请评分</label>
                                        <div style="display: flex; gap: 10px; margin-top: 5px;">
                                            <?php for ($i=1; $i<=5; $i++): ?>
                                                <label style="cursor: pointer; font-size: 13px; color: #6b72ff;">
                                                    <input type="radio" name="score" value="<?php echo $i; ?>" required> <?php echo $i; ?>⭐
                                                </label>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 10px;">
                                        <label style="font-size: 13px;">评价（可选）</label>
                                        <textarea name="comment" class="form-control" style="font-size: 13px; padding: 8px;" rows="2" placeholder="输入你的评价"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">确认完成并评分</button>
                                </form>
                            <?php endif; ?>
                            
                        <?php endif; ?>
                        
                        <?php if ($role == 'artist'): ?>
                            <p><strong>客户：</strong>
                                <!-- 新增：展示客户头像 -->
                                <div style="display: flex; align-items: center;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; overflow: hidden; margin-right: 8px;">
                                        <img src="<?php echo $order['user_avatar'] ? '../uploads/avatars/' . $order['user_avatar'] : '../images/default-avatar.png'; ?>" alt="客户头像" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <?php echo $order['user_nickname']; ?>
                                </div>
                            </p>
                            <!-- 新增：仅接单者可见的联系方式 -->
                            <?php if ($order['contact_type'] && $order['contact_info']): ?>
                                <p><strong>联系方式：</strong>
                                    <?php echo $order['contact_type'] == 'qq' ? 'QQ：' : '微信：'; ?>
                                    <?php echo $order['contact_info']; ?>
                                </p>
                            <?php endif; ?>
                            <p><strong>服务状态：</strong><?php echo $order['status'] == 'accepted' ? '<span style="color: #3b82f6;">服务中</span>' : '<span style="color: #10b981;">已完成</span>'; ?></p>
                        <?php endif; ?>
                    </div>
                <?php } } ?>
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