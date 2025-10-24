<?php
session_start();
// 验证管理员登录状态
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
include '../config/db.php';
$notice_error = '';
$notice_success = '';
$order_error = '';
$order_success = '';

// 1. 公告发布/修改逻辑
if (isset($_POST['submit_notice'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $status = $_POST['status'] ?? 0;

    if (empty($title) || empty($content)) {
        $notice_error = "标题和内容不能为空！";
    } else {
        // 检查是否已有公告，有则更新，无则新增
        $stmt = $pdo->prepare("SELECT id FROM notices WHERE status = 1");
        $stmt->execute();
        $exist_notice = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exist_notice) {
            $stmt = $pdo->prepare("UPDATE notices SET title = ?, content = ?, status = ?, created_at = NOW() WHERE id = ?");
            $stmt->execute([$title, $content, $status, $exist_notice['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO notices (title, content, status, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$title, $content, $status]);
        }
        $notice_success = "公告操作成功！";
    }
}

// 2. 删除不良订单逻辑
if (isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    if ($stmt->execute([$order_id])) {
        $order_success = "订单删除成功！";
    } else {
        $order_error = "订单删除失败！";
    }
}

// 3. 获取所有订单和当前公告
$orders_stmt = $pdo->prepare("SELECT o.*, u.nickname as user_nickname FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
$orders_stmt->execute();
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

$notice_stmt = $pdo->prepare("SELECT * FROM notices WHERE status = 1 ORDER BY created_at DESC LIMIT 1");
$notice_stmt->execute();
$current_notice = $notice_stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理首页</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="page-header">
        <h1>🔧 后台管理中心</h1>
        <div style="text-align: right; margin-top: -25px;">
            <a href="admin_login.php?action=logout" style="color: #ef4444; text-decoration: none; font-size: 13px;">退出登录</a>
        </div>
    </div>
    <div class="card-container">
        <!-- 公告管理区域 -->
        <div class="card form-card" style="max-width: 800px;">
            <h2>📢 公告管理</h2>
            <?php if ($notice_error): ?>
                <div style="color: #ef4444; text-align: center; margin-bottom: 15px; font-size: 13px;">
                    <?php echo $notice_error; ?>
                </div>
            <?php endif; ?>
            <?php if ($notice_success): ?>
                <div style="color: #10b981; text-align: center; margin-bottom: 15px; font-size: 13px;">
                    <?php echo $notice_success; ?>
                </div>
            <?php endif; ?>
            <form method="post" action="">
                <div class="form-group">
                    <label for="title">公告标题</label>
                    <input type="text" id="title" name="title" class="form-control" required placeholder="输入公告标题" value="<?php echo $current_notice['title'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label for="content">公告内容</label>
                    <textarea id="content" name="content" class="form-control" required placeholder="输入公告内容" rows="5"><?php echo $current_notice['content'] ?? ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="status">公告状态</label>
                    <select id="status" name="status" class="form-control">
                        <option value="1" <?php echo $current_notice && $current_notice['status'] == 1 ? 'selected' : ''; ?>>显示</option>
                        <option value="0" <?php echo $current_notice && $current_notice['status'] == 0 ? 'selected' : ''; ?>>隐藏</option>
                    </select>
                </div>
                <button type="submit" name="submit_notice" class="btn btn-primary">提交公告</button>
            </form>
        </div>

        <!-- 订单管理区域 -->
        <div class="card" style="max-width: 1200px; margin-top: 20px;">
            <h2>🗑️ 不良订单删除</h2>
            <?php if ($order_error): ?>
                <div style="color: #ef4444; text-align: center; margin-bottom: 15px; font-size: 13px;">
                    <?php echo $order_error; ?>
                </div>
            <?php endif; ?>
            <?php if ($order_success): ?>
                <div style="color: #10b981; text-align: center; margin-bottom: 15px; font-size: 13px;">
                    <?php echo $order_success; ?>
                </div>
            <?php endif; ?>
            <div class="order-list">
                <?php if (empty($orders)): ?>
                    <p style='grid-column: 1/-1; text-align: center; color: #666; font-size: 14px; padding: 30px;'>暂无订单</p>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="card order-card">
                            <h3><?php echo $order['title']; ?></h3>
                            <div class="order-meta">
                                <span><?php echo $order['created_at']; ?></span>
                                <span><?php echo $order['status']; ?></span>
                            </div>
                            <p><strong>发布人：</strong><?php echo $order['user_nickname']; ?></p>
                            <p><strong>详情：</strong><?php echo $order['content']; ?></p>
                            <form method="post" action="" style="margin-top: 10px;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" name="delete_order" class="btn btn-danger" onclick="return confirm('确定要删除该订单吗？')">删除订单</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
