<?php

return [
    'default_user_id' => 1,
    'pet_action_reward_exp' => 1,
    'pet_level_exp_base' => 20,
    'pet_level_up_coin' => 30,
    'max_status_value' => 100,
    'status_decay_minutes' => 1,
    'status_decay_values' => [
        'hunger' => 5,
        'clean_value' => 3,
        'mood' => 4,
    ],
    'pet_actions' => [
        'feed' => ['field' => 'hunger', 'value' => 1],
        'bath' => ['field' => 'clean', 'value' => 1],
        'play' => ['field' => 'mood', 'value' => 1],
    ],
];
