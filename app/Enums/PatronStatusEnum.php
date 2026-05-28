<?php

declare(strict_types=1);

namespace App\Enums;

enum PatronStatusEnum: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Suspended = 'suspended';
    case Archived = 'archived';
}
