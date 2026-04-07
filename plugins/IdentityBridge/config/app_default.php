<?php
declare(strict_types=1);

use IdentityBridge\Enum\AuthenticationMode;

return [
    'IdentityBridge' => [
        'mode' => AuthenticationMode::ProtectedByDefault->value,
        'overrides' => [
            'Api/Auth/*' => false,
            'Api/Health/index' => false,
        ],
    ],
];
