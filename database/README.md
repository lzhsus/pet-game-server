# Database

本目录保存宠物游戏服务端的数据库结构和初始化数据。

## 初始化数据库

在 phpstudy_pro 的 MySQL 管理工具中执行：

```sql
source database/schema.sql;
```

或者在 Navicat / phpMyAdmin 中直接导入：

```text
database/schema.sql
```

## 本地数据库配置

复制配置模板：

```bash
copy config\database.example.php config\database.php
```

然后修改 `config/database.php`：

```php
<?php

return [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'pet_game',
    'username' => 'admin',
    'password' => 'admin123',
    'charset' => 'utf8mb4',
];
```

注意：`config/database.php` 是本地私有配置，不要提交到仓库。

## 当前表

```text
users              玩家用户
pets               宠物
shop_goods         商城商品
bag_items          用户背包
tasks              任务配置
user_tasks         用户任务进度
user_sign_logs     签到记录
```
