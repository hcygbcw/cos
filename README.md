数据库
-- 用户表（区分普通用户/妆娘毛娘）
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- 存储加密后的密码
    role ENUM('user', 'artist') NOT NULL, -- user=普通用户，artist=妆娘毛娘
    nickname VARCHAR(50) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default_avatar.jpg',
    intro TEXT, -- 妆娘毛娘自我介绍
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 订单表
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- 下单用户ID
    artist_id INT NOT NULL, -- 接单妆娘毛娘ID
    title VARCHAR(100) NOT NULL, -- 订单标题（如：漫展日常妆）
    content TEXT, -- 订单详情
    status ENUM('pending', 'accepted', 'completed') NOT NULL DEFAULT 'pending', -- 待接单/已接单/已完成
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (artist_id) REFERENCES users(id)
);



-- 创建用户表
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'artist') NOT NULL,
    nickname VARCHAR(50) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default_avatar.jpg',
    intro TEXT,
    skills TEXT,
    works_imgs TEXT,
    avg_rating DECIMAL(2,1) DEFAULT 0,
    rating_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 创建订单表
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    artist_id INT NOT NULL DEFAULT 0,
    title VARCHAR(100) NOT NULL,
    content TEXT,
    status ENUM('pending', 'accepted', 'completed') NOT NULL DEFAULT 'pending',
    order_img VARCHAR(255) DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 创建评分表
CREATE TABLE ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    artist_id INT NOT NULL,
    score INT NOT NULL CHECK (score BETWEEN 1 AND 5),
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (artist_id) REFERENCES users(id),
    UNIQUE KEY unique_order_rating (order_id)
);



-- 添加用户头像字段（默认头像路径）
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT 'default_avatar.png' AFTER nickname;



-- 新增"已撤回"状态，用于订单撤回功能
ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'accepted', 'completed', 'cancelled') DEFAULT 'pending';


-- 订单表新增地址字段，用于存储用户填写的地址
ALTER TABLE orders ADD COLUMN address VARCHAR(255) AFTER content;





ALTER TABLE `orders`
ADD `contact_type` VARCHAR(20) DEFAULT NULL COMMENT '联系方式类型（qq/wechat）' AFTER `content`,
ADD `contact_info` VARCHAR(50) DEFAULT NULL COMMENT '联系方式内容' AFTER `contact_type`;





-- 1. 给users表新增管理员角色字段（若未存在）
ALTER TABLE `users` ADD `is_admin` TINYINT(1) DEFAULT 0 COMMENT '是否为管理员：0-否，1-是';

-- 2. 创建公告表
CREATE TABLE `notices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL COMMENT '公告标题',
  `content` TEXT NOT NULL COMMENT '公告内容',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `status` TINYINT(1) DEFAULT 1 COMMENT '状态：1-显示，0-隐藏'
);






以下是指定用户成为后台管理员

UPDATE `users` SET `is_admin` = 1 WHERE `username` = '用户账号';

