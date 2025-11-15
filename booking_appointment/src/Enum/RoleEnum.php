<?php

namespace App\Enum;

enum RoleEnum : string {
    case USER = 'ROLE_USER';
    case PROVIDER = 'ROLE_PROVIDER';
    case ADMIN='ROLE_ADMIN';
}
