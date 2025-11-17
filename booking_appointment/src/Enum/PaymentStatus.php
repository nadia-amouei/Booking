<?php

namespace App\Enum;

enum PaymentStatus: string{
    case PENDING = "pending";
    case PROCESSING = "processing";
    case CANCELED = "canceled";
}
