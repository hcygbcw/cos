<?php
$success = '';
$error = '';

// 退出登录
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../pages/login.php");
    exit();
}

// 🔥 头像上传功能
if (isset($_POST['upload_avatar'])) {
    $user_id = $_SESSION['user_id'];
    if (!empty($_FILES['avatar']['name'])) {
        $upload_result = uploadImage($_FILES['avatar'], '../uploads/avatars/');
        if (isset($upload_result['error'])) {
            $error = $upload_result['error'];
        } else {
            $avatar = $upload_result['success'];
            $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            if ($stmt->execute([$avatar, $user_id])) {
                $success = "头像上传成功！";
                // 更新session中的头像信息
                $_SESSION['avatar'] = $avatar;
                header("Refresh: 1; url=../pages/profile.php");
            } else {
                $error = "头像更新失败，请重试！";
            }
        }
    } else {
        $error = "请选择要上传的头像！";
    }
}

// 资料修改逻辑
if (isset($_POST['edit_profile'])) {
    $user_id = $_SESSION['user_id'];
    $nickname = trim($_POST['nickname'] ?? '');
    $intro = trim($_POST['intro'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');

    if (empty($nickname)) {
        $error = "昵称不能为空！";
    } else {
        try {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if (!empty($new_password)) {
                $hashed_pwd = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET nickname = :nickname, intro = :intro, skills = :skills, password = :password WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nickname', $nickname);
                $stmt->bindParam(':intro', $intro);
                $stmt->bindParam(':skills', $skills);
                $stmt->bindParam(':password', $hashed_pwd);
                $stmt->bindParam(':id', $user_id);
            } else {
                $sql = "UPDATE users SET nickname = :nickname, intro = :intro, skills = :skills WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nickname', $nickname);
                $stmt->bindParam(':intro', $intro);
                $stmt->bindParam(':skills', $skills);
                $stmt->bindParam(':id', $user_id);
            }

            if ($stmt->execute()) {
                $success = "信息修改成功！";
                $_SESSION['nickname'] = $nickname;
                header("Refresh: 1; url=../pages/profile.php");
            } else {
                $error = "修改失败，请重试！";
            }
        } catch (PDOException $e) {
            $error = "修改失败：" . $e->getMessage();
        }
    }
}
?>
