<?php
namespace App\Enum;
enum AppointmentStatus : string {

    case PENDING = 'pending';
    case CONFIRM = 'confirm';
    case CANCELED = 'canceled';

}
