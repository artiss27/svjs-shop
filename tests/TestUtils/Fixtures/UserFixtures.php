<?php

namespace App\Tests\TestUtils\Fixtures;

class UserFixtures
{
    public const USER             = [
        'email'    => 'test@example.com',
        'roles'    => ['ROLE_USER'],
        'password' => '111111',
    ];
    public const USER_ADMIN       = [
        'email'    => 'admin_1@example.com',
        'roles'    => ['ROLE_ADMIN'],
        'password' => '111111',
    ];
    public const USER_SUPER_ADMIN = [
        'email'    => 'super_admin_1@example.com',
        'roles'    => ['ROLE_SUPER_ADMIN'],
        'password' => '111111',
    ];
}