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
    case WorkManage = 'work.manage';
    case EditionManage = 'edition.manage';
    case PatronTypeManage = 'patron_type.manage';
    case LocationManage = 'location.manage';
    case PatronView = 'patron.view';
    case PatronCreate = 'patron.create';
    case PatronUpdate = 'patron.update';
    case PatronArchive = 'patron.archive';
}
