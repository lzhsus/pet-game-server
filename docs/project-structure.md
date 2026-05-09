# Pet Game Server 项目结构

后端定位：宠物养成游戏 API 服务。

当前第一阶段不使用框架，采用轻量原生 PHP 分层结构，后续可平滑迁移到 Laravel / ThinkPHP。

## 目录结构

```text
pet-game-server/
├── public/
│   └── index.php                 # API 入口文件
├── src/
│   ├── Core/
│   │   ├── Router.php             # 路由分发
│   │   ├── Request.php            # 请求读取
│   │   ├── Response.php           # JSON 响应
│   │   └── Auth.php               # 简单 Token 鉴权
│   ├── Controllers/
│   │   ├── AuthController.php     # 登录接口
│   │   ├── UserController.php     # 用户接口
│   │   ├── PetController.php      # 宠物接口
│   │   ├── TaskController.php     # 任务接口
│   │   ├── BagController.php      # 背包接口
│   │   └── ShopController.php     # 商城接口
│   ├── Services/
│   │   ├── UserService.php        # 用户业务
│   │   ├── PetService.php         # 宠物业务
│   │   ├── RewardService.php      # 奖励业务
│   │   ├── TaskService.php        # 任务业务
│   │   ├── BagService.php         # 背包业务
│   │   └── ShopService.php        # 商城业务
│   ├── Repositories/
│   │   └── JsonGameRepository.php # JSON 数据仓库
│   └── Config/
│       ├── routes.php             # 路由配置
│       └── game.php               # 游戏配置
├── storage/
│   └── game-data.json             # 开发阶段数据存储
├── README.md
└── .gitignore
```

## 命名规范

### PHP 类

- 控制器：`XxxController`
- 服务层：`XxxService`
- 仓库层：`XxxRepository`
- 核心工具：语义名，例如 `Router`、`Response`、`Request`

### 接口路径

```text
POST /api/auth/login
GET  /api/user/profile
GET  /api/pet/profile
POST /api/pet/feed
POST /api/pet/bath
POST /api/pet/play
GET  /api/task/list
POST /api/task/receive
GET  /api/bag/list
GET  /api/shop/list
POST /api/shop/buy
```

### 响应格式

```json
{
  "code": 0,
  "message": "success",
  "data": {}
}
```

### code 约定

```text
0     成功
400   参数错误
401   未登录
404   资源不存在
500   服务错误
```

## 第一阶段功能

- 登录
- 用户资料
- 宠物资料
- 喂食
- 洗澡
- 玩耍
- 任务列表
- 领取任务奖励
- 背包列表
- 商城列表
- 购买商品
