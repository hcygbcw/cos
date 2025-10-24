<?php
session_start();
include '../config/db.php';
$error = '';

// 管理员登录验证
if (isset($_POST['admin_login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "用户名和密码不能为空！";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_admin = 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // 验证密码（假设密码用password_hash加密）
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            header("Location: admin_index.php");
            exit();
        } else {
            $error = "管理员账号或密码错误！";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="page-header">
        <h1>🔧 后台管理系统</h1>
    </div>
    <div class="card form-card" style="max-width: 400px;">
        <?php if ($error): ?>
            <div style="color: #ef4444; text-align: center; margin-bottom: 15px; font-size: 13px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <h2>管理员登录</h2>
        <form method="post" action="">
            <div class="form-group">
                <label for="username">管理员账号</label>
                <input type="text" id="username" name="username" class="form-control" required placeholder="输入管理员用户名">
            </div>
            <div class="form-group">
                <label for="password">管理员密码</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="输入管理员密码">
            </div>
            <button type="submit" name="admin_login" class="btn btn-primary">登录</button>
        </form>
    </div>
</body>
</html>