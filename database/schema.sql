-- Pet Game Server table schema
-- 新数据表统一使用 pet_ 前缀。
-- 执行前请确认旧数据不需要保留。

DROP TABLE IF EXISTS `pet_user_sign_logs`;
DROP TABLE IF EXISTS `pet_user_tasks`;
DROP TABLE IF EXISTS `pet_tasks`;
DROP TABLE IF EXISTS `pet_bag_items`;
DROP TABLE IF EXISTS `pet_shop_goods`;
DROP TABLE IF EXISTS `pet_pets`;
DROP TABLE IF EXISTS `pet_users`;

CREATE TABLE `pet_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `nickname` VARCHAR(50) NOT NULL DEFAULT '玩家001' COMMENT '用户昵称',
  `avatar` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '头像地址',
  `coin` INT UNSIGNED NOT NULL DEFAULT 1000 COMMENT '金币',
  `diamond` INT UNSIGNED NOT NULL DEFAULT 100 COMMENT '钻石',
  `token` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '登录 token，可选',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

CREATE TABLE `pet_pets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '宠物ID',
  `user_id` INT UNSIGNED NOT NULL COMMENT '所属用户ID',
  `name` VARCHAR(50) NOT NULL DEFAULT '布丁' COMMENT '宠物名称',
  `type` VARCHAR(30) NOT NULL DEFAULT 'cat' COMMENT '宠物类型',
  `level` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT '宠物等级',
  `exp` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '宠物经验',
  `hunger` INT UNSIGNED NOT NULL DEFAULT 60 COMMENT '饥饿值',
  `clean_value` INT UNSIGNED NOT NULL DEFAULT 60 COMMENT '清洁值',
  `mood` INT UNSIGNED NOT NULL DEFAULT 60 COMMENT '心情值',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_pet_pets_user_id` (`user_id`),
  KEY `idx_pet_pets_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='宠物表';

CREATE TABLE `pet_shop_goods` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商品ID',
  `goods_name` VARCHAR(100) NOT NULL COMMENT '商品名称',
  `goods_type` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '商品类型：food/clean/toy',
  `price_coin` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '金币价格',
  `price_diamond` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '钻石价格',
  `item_count` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT '购买后获得数量',
  `icon` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '商品图标',
  `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '1=上架 0=下架',
  `sort` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序值',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_pet_shop_goods_status` (`status`),
  KEY `idx_pet_shop_goods_type` (`goods_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商城商品表';

CREATE TABLE `pet_bag_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '背包记录ID',
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `item_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '物品ID，来源商城或配置',
  `item_name` VARCHAR(100) NOT NULL COMMENT '物品名称',
  `item_type` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '物品类型：food/clean/toy',
  `item_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '物品数量',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_pet_bag_items_user_id` (`user_id`),
  KEY `idx_pet_bag_items_type` (`item_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户背包表';

CREATE TABLE `pet_tasks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '任务ID',
  `title` VARCHAR(100) NOT NULL COMMENT '任务标题',
  `task_type` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '任务类型：feed/bath/play/sign',
  `target_value` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT '目标次数',
  `reward_coin` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '奖励金币',
  `reward_exp` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '奖励宠物经验',
  `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '1=启用 0=停用',
  `sort` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序值',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_pet_tasks_type` (`task_type`),
  KEY `idx_pet_tasks_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='任务配置表';

CREATE TABLE `pet_user_tasks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户任务记录ID',
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `task_id` INT UNSIGNED NOT NULL COMMENT '任务ID',
  `task_date` DATE NOT NULL COMMENT '任务日期，用于每日重置',
  `progress` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '当前进度',
  `status` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0=未完成 1=可领取 2=已领取',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_pet_user_task_daily` (`user_id`, `task_id`, `task_date`),
  KEY `idx_pet_user_tasks_user_id` (`user_id`),
  KEY `idx_pet_user_tasks_task_date` (`task_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户每日任务进度表';

CREATE TABLE `pet_user_sign_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '签到记录ID',
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `sign_date` DATE NOT NULL COMMENT '签到日期',
  `reward_coin` INT UNSIGNED NOT NULL DEFAULT 50 COMMENT '奖励金币',
  `reward_diamond` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT '奖励钻石',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_pet_user_sign_date` (`user_id`, `sign_date`),
  KEY `idx_pet_user_sign_logs_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户签到记录表';

-- 基础配置数据：只初始化系统配置，不初始化用户操作数据。

INSERT INTO `pet_tasks` (`id`, `title`, `task_type`, `target_value`, `reward_coin`, `reward_exp`, `status`, `sort`) VALUES
(1, '喂食 1 次', 'feed', 1, 20, 5, 1, 10),
(2, '洗澡 1 次', 'bath', 1, 20, 5, 1, 20),
(3, '玩耍 1 次', 'play', 1, 20, 5, 1, 30),
(4, '每日签到 1 次', 'sign', 1, 30, 5, 1, 40);

INSERT INTO `pet_shop_goods` (`id`, `goods_name`, `goods_type`, `price_coin`, `price_diamond`, `item_count`, `icon`, `status`, `sort`) VALUES
(101, '高级猫粮', 'food', 30, 0, 1, '', 1, 10),
(102, '香氛沐浴露', 'clean', 40, 0, 1, '', 1, 20),
(103, '逗猫棒', 'toy', 50, 0, 1, '', 1, 30);
