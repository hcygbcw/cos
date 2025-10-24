<?php
session_start();
include '../config/db.php';
$error = '';

// 引入登录处理逻辑
if (isset($_POST['login'])) {
    // 获取表单数据
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // 验证表单非空
    if (empty($username) || empty($password)) {
        $error = "用户名和密码不能为空";
    } else {
        // 数据库查询用户
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 验证用户存在且密码正确（假设密码已用 password_hash 加密存储）
        if ($user && password_verify($password, $user['password'])) {
            // 保存用户信息到 Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // 保存角色（user/artist）

            // 跳转到接单大厅
            header("Location: hall.php");
            exit();
        } else {
            $error = "用户名或密码错误";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户登录</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="page-header">
        <h1>漫展妆娘毛娘对接平台</h1>
    </div>

    <div class="card form-card">
        <?php if ($error): ?>
            <div style="color: #ef4444; text-align: center; margin-bottom: 15px; font-size: 13px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <h2>用户登录</h2>
        <form method="post" action="">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" class="form-control" required placeholder="请输入用户名" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="请输入密码">
            </div>
            <button type="submit" name="login" class="btn btn-primary">登录</button>
            <div style="text-align: center; margin-top: 15px; font-size: 13px;">
                没有账号？<a href="register.php" style="color: #6b72ff; text-decoration: none;">立即注册</a>
            </div>
        </form>
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