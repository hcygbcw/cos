<?php
$error = '';
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $nickname = trim($_POST['nickname']);
    $role = trim($_POST['role']);

    if (empty($username) || empty($password) || empty($nickname)) {
        $error = "请填写完整信息！";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $error = "用户名已被注册！";
        } else {
            $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nickname) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $hashed_pwd, $role, $nickname])) {
                header("Location: ../pages/login.php");
                exit();
            } else {
                $error = "注册失败，请重试！";
            }
        }
    }
}
?>
