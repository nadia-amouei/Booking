<?php
use App\Entity\User;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    // ...

    $security->provider('app_user_provider')
        ->entity()
            ->class(User::class)
            ->property('email')
    ;

};
