<?php
function uploadImage($file, $upload_dir) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = [
            UPLOAD_ERR_INI_SIZE => '文件超过服务器最大限制（5MB）',
            UPLOAD_ERR_FORM_SIZE => '文件超过表单最大限制（5MB）',
            UPLOAD_ERR_PARTIAL => '文件仅部分上传，请重新尝试',
            UPLOAD_ERR_NO_FILE => '未选择文件',
            UPLOAD_ERR_NO_TMP_DIR => '服务器缺少临时文件夹，请联系管理员',
            UPLOAD_ERR_CANT_WRITE => '文件写入失败，请检查服务器权限'
        ];
        return ['error' => $errorMsg[$file['error']] ?? '文件上传异常，错误码：' . $file['error']];
    }
    
    if (empty($file['name'])) return ['success' => ''];
    
    $upload_dir = rtrim($upload_dir, '/') . '/';
    if (!file_exists($upload_dir)) {
        $oldUmask = umask(0);
        $dirCreated = mkdir($upload_dir, 0777, true);
        umask($oldUmask);
        if (!$dirCreated) {
            return ['error' => '存储目录创建失败，请手动创建 ' . $upload_dir];
        }
    }
    
    if (!is_writable($upload_dir)) {
        return ['error' => '存储目录不可写，请设置 ' . $upload_dir . ' 权限为777'];
    }
    
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $max_size = 5 * 1024 * 1024;
    
    if ($file['size'] > $max_size) {
        return ['error' => '图片大小不能超过5MB！当前文件大小：' . round($file['size'] / 1024 / 1024, 2) . 'MB'];
    }
    
    if (!in_array($file_ext, $allowed_ext)) {
        return ['error' => '仅支持JPG、PNG格式图片！当前文件格式：' . $file_ext];
    }
    
    $file_name = uniqid() . '.' . $file_ext;
    $file_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        @chmod($file_path, 0644);
        return ['success' => $file_name];
    } else {
        return ['error' => '图片上传失败！可能原因：1.临时目录不可写 2.磁盘空间不足'];
    }
}
?>
