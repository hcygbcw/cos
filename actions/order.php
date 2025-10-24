<?php
include '../config/upload.php';
$error = '';
$success = '';
// 发布订单
if (isset($_POST['action']) && $_POST['action'] == 'publish' && $_SESSION['role'] == 'user') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $address = trim($_POST['address']); // 新增：接收地址
    $contact_type = $_POST['contact_type'] ?? ''; // 新增：接收联系方式类型
    $contact_info = trim($_POST['contact_info'] ?? ''); // 新增：接收联系方式内容
    $user_id = $_SESSION['user_id'];
    $order_img = '';
    
    if (empty($title) || empty($content)) {
        $error = "标题和详情不能为空！";
    } else {
        // 新增：联系方式验证（选填但填写时需完整）
        if (!empty($contact_type) && empty($contact_info)) {
            $error = "选择联系方式后请填写对应账号！";
        } elseif (empty($contact_type) && !empty($contact_info)) {
            $error = "请先选择联系方式类型！";
        }
        
        if (empty($error) && !empty($_FILES['order_img']['name'])) {
            $upload_result = uploadImage($_FILES['order_img'], '../uploads/orders/');
            if (isset($upload_result['error'])) {
                $error = $upload_result['error'];
            } else {
                $order_img = $upload_result['success'];
            }
        }
        
        if (empty($error)) {
            // 新增：插入语句添加新字段
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, title, content, address, contact_type, contact_info, status, order_img, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())");
            if ($stmt->execute([$user_id, $title, $content, $address, $contact_type, $contact_info, $order_img])) {
                $success = "订单发布成功！";
                header("Refresh: 1; url=../pages/hall.php");
            } else {
                $error = "发布失败，请重试！";
            }
        }
    }
}
// 接受订单（原有逻辑不变）
if (isset($_POST['action']) && $_POST['action'] == 'accept' && $_SESSION['role'] == 'artist') {
    $order_id = $_POST['order_id'];
    $artist_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($order['status'] != 'pending') {
        $error = "该订单已被接单或撤回！";
    } else {
        $stmt = $pdo->prepare("UPDATE orders SET artist_id = ?, status = 'accepted' WHERE id = ?");
        if ($stmt->execute([$artist_id, $order_id])) {
            $success = "接单成功！";
            header("Refresh: 1; url=../pages/orders.php");
        } else {
            $error = "接单失败，请重试！";
        }
    }
}
// 完成订单并评分（原有逻辑不变）
if (isset($_POST['action']) && $_POST['action'] == 'complete' && $_SESSION['role'] == 'user') {
    $order_id = $_POST['order_id'];
    $user_id = $_SESSION['user_id'];
    $score = $_POST['score'] ?? 0;
    $comment = trim($_POST['comment'] ?? '');
    if ($score < 1 || $score > 5) {
        $error = "请选择有效评分（1-5星）！";
    } else {
        $stmt = $pdo->prepare("SELECT id, artist_id FROM orders WHERE id = ? AND user_id = ? AND status = 'accepted'");
        $stmt->execute([$order_id, $user_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            $error = "无法完成该订单（订单不存在或状态错误）！";
        } else {
            $artist_id = $order['artist_id'];
            try {
                $pdo->beginTransaction();
                $stmt1 = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
                $stmt1->execute([$order_id]);
                $stmt2 = $pdo->prepare("INSERT INTO ratings (order_id, user_id, artist_id, score, comment, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt2->execute([$order_id, $user_id, $artist_id, $score, $comment]);
                $stmt3 = $pdo->prepare("SELECT AVG(score) as avg_score, COUNT(id) as count FROM ratings WHERE artist_id = ?");
                $stmt3->execute([$artist_id]);
                $rating_info = $stmt3->fetch(PDO::FETCH_ASSOC);
                $stmt4 = $pdo->prepare("UPDATE users SET avg_rating = ?, rating_count = ? WHERE id = ?");
                $stmt4->execute([round($rating_info['avg_score'], 1), $rating_info['count'], $artist_id]);
                $pdo->commit();
                $success = "订单已完成并提交评分！";
                header("Refresh: 1; url=../pages/orders.php");
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "操作失败：" . $e->getMessage();
            }
        }
    }
}
// 订单撤回功能（原有逻辑不变）
if (isset($_POST['action']) && $_POST['action'] == 'cancel' && $_SESSION['role'] == 'user') {
    $order_id = $_POST['order_id'];
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) {
        $error = "订单不存在！";
    } elseif ($order['status'] != 'pending') {
        $error = "仅待接单订单可撤回！";
    } else {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        if ($stmt->execute([$order_id])) {
            $success = "订单已撤回！";
            header("Refresh: 1; url=../pages/orders.php");
        } else {
            $error = "撤回失败，请重试！";
        }
    }
}
?>
