<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'middleware' => ['web'],
    'auth_middleware' => 'auth',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'lowercase_usernames' => true,
    'home' => '/auth/redirect',
    'prefix' => '',
    'domain' => null,
    'views' => true,
    'limiters' => ['login' => 'login', 'two-factor' => 'two-factor'],
    'features' => [Features::resetPasswords()],
];
