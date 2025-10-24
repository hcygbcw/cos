<?php
session_start();
include '../config/db.php';
include '../actions/register.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户注册</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="page-header">
        <h1>🌸 二次元账号注册 🌸</h1>
    </div>

    <div class="card form-card">
        <?php if (isset($error)): ?>
            <div style="color: #ff6666; text-align: center; margin-bottom: 20px; font-size: 13px;">
                (｡•́︿•̀｡) <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <h2>创建你的二次元接单账号</h2>
        <form method="post" action="">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" class="form-control" required placeholder="请设置唯一用户名">
            </div>
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="请设置密码">
            </div>
            <div class="form-group">
                <label for="nickname">昵称</label>
                <input type="text" id="nickname" name="nickname" class="form-control" required placeholder="请设置可爱的二次元昵称">
            </div>
            <div class="form-group">
                <label for="role">用户类型</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="user">普通用户（找妆娘/毛娘）</option>
                    <option value="artist">妆娘/毛娘（提供服务）</option>
                </select>
            </div>
            <button type="submit" name="register" class="btn btn-primary">注册</button>
            <div style="text-align: center; margin-top: 18px; font-size: 13px;">
                已有账号？<a href="login.php" style="color: #ff66b2; text-decoration: none;">立即登录 (≧∇≦)ﾉ</a>
            </div>
        </form>
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
