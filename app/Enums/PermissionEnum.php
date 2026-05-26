<?php

declare(strict_types=1);

namespace App\Enums;

enum PermissionEnum: string
{
    case AdminlandAccess = 'adminland.access';
    case OrganizationUpdate = 'organization.update';
    case OrganizationDelete = 'organization.delete';
    case RoleManage = 'role.manage';
    case BranchManage = 'branch.manage';
    case ItemTypeManage = 'item_type.manage';
    case PatronTypeManage = 'patron_type.manage';
}
