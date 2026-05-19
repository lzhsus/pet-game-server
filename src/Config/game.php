<?php

/**
 * 游戏核心配置。
 *
 * 重点说明：宠物有 3 个状态值，数值范围统一是 0 ~ max_status_value。
 *
 * 1. hunger：饥饿值
 *    - 数值越高，代表宠物越饱。
 *    - 喂食 feed 会增加 hunger。
 *    - 随时间自动下降。
 *
 * 2. clean_value：清洁值
 *    - 数值越高，代表宠物越干净。
 *    - 洗澡 bath 会增加 clean_value。
 *    - 随时间自动下降。
 *    - 注意：前端为了显示方便可能叫 clean，数据库字段叫 clean_value。
 *
 * 3. mood：心情值
 *    - 数值越高，代表宠物越开心。
 *    - 玩耍 play 会增加 mood。
 *    - 随时间自动下降。
 */
return [
    // 当前阶段先固定默认用户 ID，后续接入微信登录后可以替换成真实用户 ID。
    'default_user_id' => 1,

    // 每次喂食 / 洗澡 / 玩耍成功后，宠物获得的经验值。
    'pet_action_reward_exp' => 1,

    // 宠物升级所需经验。比如 20 表示每满 20 点经验升 1 级。
    'pet_level_exp_base' => 20,

    // 预留字段：宠物升级时奖励金币，目前业务逻辑暂未使用。
    'pet_level_up_coin' => 30,

    // 宠物状态最大值。hunger / clean_value / mood 都不会超过这个值。
    'max_status_value' => 100,

    // 状态衰减周期，单位：分钟。180 表示每 3 小时衰减一次。
    'status_decay_minutes' => 180,

    // 每个衰减周期内，三个状态分别下降多少。
    // 例：status_decay_minutes = 180，hunger = 5，表示每 3 小时饥饿值减少 5。
    'status_decay_values' => [
        'hunger' => 5,
        'clean_value' => 3,
        'mood' => 4,
    ],

    // 宠物操作配置。
    // field 表示要影响哪个状态字段，value 表示每次操作增加多少。
    // feed：喂食，提升饥饿值 hunger。
    // bath：洗澡，提升清洁值 clean_value。
    // play：玩耍，提升心情值 mood。
    'pet_actions' => [
        'feed' => ['field' => 'hunger', 'value' => 1],
        'bath' => ['field' => 'clean', 'value' => 1],
        'play' => ['field' => 'mood', 'value' => 1],
    ],
];
