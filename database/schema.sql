-- Pet Game Server database schema
-- MySQL 8.x / phpstudy_pro compatible

CREATE DATABASE IF NOT EXISTS `pet_game`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `pet_game`;

DROP TABLE IF EXISTS `user_sign_logs`;
DROP TABLE IF EXISTS `user_tasks`;
DROP TABLE IF EXISTS `tasks`;
DROP TABLE IF EXISTS `bag_items`;
DROP TABLE IF EXISTS `shop_goods`;
DROP TABLE IF EXISTS `pets`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nickname` VARCHAR(50) NOT NULL DEFAULT '玩家',
  `avatar` VARCHAR(255) NOT NULL DEFAULT '',
  `level` INT UNSIGNED NOT NULL DEFAULT 1,
  `exp` INT UNSIGNED NOT NULL DEFAULT 0,
  `coin` INT UNSIGNED NOT NULL DEFAULT 100,
  `diamond` INT UNSIGNED NOT NULL DEFAULT 20,
  `token` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='玩家用户表';

CREATE TABLE `pets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(50) NOT NULL DEFAULT '布丁',
  `type` VARCHAR(30) NOT NULL DEFAULT 'cat',
  `level` INT UNSIGNED NOT NULL DEFAULT 1,
  `exp` INT UNSIGNED NOT NULL DEFAULT 0,
  `hunger` INT UNSIGNED NOT NULL DEFAULT 60,
  `clean_value` INT UNSIGNED NOT NULL DEFAULT 60,
  `mood` INT UNSIGNED NOT NULL DEFAULT 60,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pets_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='宠物表';

CREATE TABLE `shop_goods` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_name` VARCHAR(100) NOT NULL,
  `goods_type` VARCHAR(50) NOT NULL DEFAULT '',
  `price_coin` INT UNSIGNED NOT NULL DEFAULT 0,
  `price_diamond` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '1=上架 0=下架',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商城商品表';

CREATE TABLE `bag_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `item_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `item_name` VARCHAR(100) NOT NULL,
  `item_type` VARCHAR(50) NOT NULL DEFAULT '',
  `item_count` INT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bag_items_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户背包表';

CREATE TABLE `tasks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(100) NOT NULL,
  `task_type` VARCHAR(50) NOT NULL DEFAULT 'daily',
  `target_value` INT UNSIGNED NOT NULL DEFAULT 1,
  `reward_coin` INT UNSIGNED NOT NULL DEFAULT 0,
  `reward_exp` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '1=启用 0=停用',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='任务配置表';

CREATE TABLE `user_tasks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `task_id` INT UNSIGNED NOT NULL,
  `progress` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0=未完成 1=已领取',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_task` (`user_id`, `task_id`),
  KEY `idx_user_tasks_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户任务进度表';

CREATE TABLE `user_sign_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `sign_date` DATE NOT NULL,
  `reward_coin` INT UNSIGNED NOT NULL DEFAULT 50,
  `reward_diamond` INT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_sign_date` (`user_id`, `sign_date`),
  KEY `idx_user_sign_logs_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户签到记录表';

INSERT INTO `users` (`id`, `nickname`, `level`, `exp`, `coin`, `diamond`) VALUES
(1, '玩家001', 1, 0, 1000, 100);

INSERT INTO `pets` (`id`, `user_id`, `name`, `type`, `level`, `exp`, `hunger`, `clean_value`, `mood`) VALUES
(1, 1, '布丁', 'cat', 1, 0, 60, 60, 60);

INSERT INTO `shop_goods` (`id`, `goods_name`, `goods_type`, `price_coin`, `price_diamond`, `status`) VALUES
(101, '高级猫粮', 'food', 30, 0, 1),
(102, '香氛沐浴露', 'clean', 40, 0, 1),
(103, '逗猫棒', 'toy', 50, 0, 1);

INSERT INTO `bag_items` (`user_id`, `item_id`, `item_name`, `item_type`, `item_count`) VALUES
(1, 101, '小鱼干', 'food', 5),
(1, 102, '沐浴露', 'clean', 2),
(1, 103, '玩具球', 'toy', 1);

INSERT INTO `tasks` (`id`, `title`, `task_type`, `target_value`, `reward_coin`, `reward_exp`, `status`) VALUES
(1, '喂食 1 次', 'daily', 1, 20, 5, 1),
(2, '洗澡 1 次', 'daily', 1, 20, 5, 1),
(3, '玩耍 1 次', 'daily', 1, 20, 5, 1);

INSERT INTO `user_tasks` (`user_id`, `task_id`, `progress`, `status`) VALUES
(1, 1, 0, 0),
(1, 2, 0, 0),
(1, 3, 0, 0);
