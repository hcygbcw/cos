<?php
$error = '';
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "请填写用户名和密码！";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password, role, nickname FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            $error = "用户名或密码错误！";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nickname'] = $user['nickname'];

            header("Location: ../pages/index.php");
            exit();
        }
    }
}
?>
