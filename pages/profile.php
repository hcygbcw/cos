<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include '../config/db.php';
include '../config/upload.php';
include '../actions/profile_edit.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 初始化session中的头像信息
if (!isset($_SESSION['avatar'])) {
    $_SESSION['avatar'] = $user['avatar'];
}

$work_upload_error = '';
if (isset($_POST['upload_work']) && $user['role'] == 'artist') {
    if (!empty($_FILES['work_img']['name'])) {
        $upload_result = uploadImage($_FILES['work_img'], '../uploads/works/');
        if (isset($upload_result['error'])) {
            $work_upload_error = $upload_result['error'];
        } else {
            $new_work = $upload_result['success'];
            $existing_works = $user['works_imgs'] ? explode(',', $user['works_imgs']) : [];
            $existing_works[] = $new_work;
            $works_str = implode(',', $existing_works);
            
            $stmt = $pdo->prepare("UPDATE users SET works_imgs = ? WHERE id = ?");
            $stmt->execute([$works_str, $user_id]);
            $work_upload_error = "上传成功！";
            header("Refresh: 1; url=profile.php");
        }
    } else {
        $work_upload_error = "请选择要上传的图片！";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>个人中心</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="page-header">
        <h1>个人中心</h1>
    </div>

    <div class="card-container">
        <!-- 头像上传卡片 -->
        <div class="card form-card">
            <?php if (isset($error)): ?>
                <div style="color: #ef4444; text-align: center; margin-bottom: 15px; font-size: 13px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div style="color: #10b981; text-align: center; margin-bottom: 15px; font-size: 13px;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <!-- 头像预览与上传 -->
            <div class="avatar-upload">
                <div class="avatar-preview">
                    <img src="../uploads/avatars/<?php echo $user['avatar']; ?>" alt="用户头像">
                </div>
                <form method="post" action="" enctype="multipart/form-data">
                    <input type="file" id="avatar" name="avatar" accept="image/jpg,image/jpeg,image/png" style="display: none;" onchange="this.form.submit()">
                    <label for="avatar" class="btn btn-secondary" style="width: auto; padding: 6px 20px;">更换头像</label>
                    <input type="hidden" name="upload_avatar" value="1">
                </form>
            </div>

            <!-- 资料修改表单 -->
            <form method="post" action="">
                <div class="form-group">
                    <label for="nickname">昵称</label>
                    <input type="text" id="nickname" name="nickname" class="form-control" value="<?php echo $user['nickname']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" class="form-control" value="<?php echo $user['username']; ?>" disabled style="background: #f9fafe;">
                </div>
                <div class="form-group">
                    <label for="role">用户类型</label>
                    <input type="text" id="role" class="form-control" value="<?php echo $user['role'] == 'user' ? '普通用户' : '妆娘/毛娘'; ?>" disabled style="background: #f9fafe;">
                </div>
                
                <?php if ($user['role'] == 'artist'): ?>
                    <div class="form-group">
                        <label for="intro">自我介绍</label>
                        <textarea id="intro" name="intro" class="form-control" placeholder="介绍你的经验、擅长风格、价格等"><?php echo $user['intro']; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="skills">技能标签（用逗号分隔）</label>
                        <input type="text" id="skills" name="skills" class="form-control" value="<?php echo $user['skills']; ?>" placeholder="如：日常妆,cos妆,毛造型">
                    </div>
                    
                    <div class="form-group">
                        <label>我的评分</label>
                        <div class="rating">
                            <span><?php echo $user['avg_rating']; ?>⭐</span>
                            <span style="color: #888; margin-left: 8px;">（共<?php echo $user['rating_count']; ?>人评价）</span>
                        </div>
                    </div>

                    <!-- 查看评论入口 -->
                    <a href="reviews.php" class="btn btn-secondary" style="margin-bottom: 15px;">查看我的评论</a>
                <?php endif; ?>

                <div class="form-group">
                    <label for="new_password">新密码（不填则保持不变）</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="请输入新密码">
                </div>
                <button type="submit" name="edit_profile" class="btn btn-primary">保存修改</button>
            </form>
        </div>

        <!-- 作品上传卡片（妆娘/毛娘专属） -->
        <?php if ($user['role'] == 'artist'): ?>
            <div class="card form-card">
                <h2>作品展示</h2>
                <form method="post" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="work_img">上传作品图片</label>
                        <input type="file" id="work_img" name="work_img" accept="image/jpg,image/jpeg,image/png" required>
                        <p style="font-size: 12px; color: #999; margin-top: 5px;">支持JPG、PNG格式，大小不超过5MB</p>
                    </div>
                    <button type="submit" name="upload_work" class="btn btn-primary">上传作品</button>
                    <?php if (isset($work_upload_error)): ?>
                        <p style="color: <?php echo strpos($work_upload_error, '成功') !== false ? '#10b981' : '#ef4444'; ?>; text-align: center; margin-top: 10px; font-size: 13px;">
                            <?php echo $work_upload_error; ?>
                        </p>
                    <?php endif; ?>
                </form>

                <?php if ($user['works_imgs']): ?>
                    <h3 style="font-size: 14px; margin-top: 20px; margin-bottom: 10px; color: #333;">已上传作品</h3>
                    <div class="works-gallery">
                        <?php foreach (explode(',', $user['works_imgs']) as $img): ?>
                            <div class="works-item">
                                <img src="../uploads/works/<?php echo $img; ?>" alt="作品">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- 退出登录卡片 -->
        <div class="card form-group">
            <form method="post" action="">
                <button type="submit" name="logout" class="btn btn-danger">退出登录</button>
            </form>
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
