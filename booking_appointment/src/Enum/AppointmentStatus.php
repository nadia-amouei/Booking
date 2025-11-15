<?php
namespace App\Enum;
enum AppointmentStatus : string {

    case READY = 'ready';
    case CONFIRM = 'confirm';
    case CANCELED = 'canceled';
    case REJECT = 'reject';

}
