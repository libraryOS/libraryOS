<?php

declare(strict_types=1);

namespace App\Enums;

enum UserActionEnum: string
{
    case AccountCreation = 'account_creation';
    case ApiKeyCreation = 'api_key_creation';
    case BranchCreation = 'branch_creation';
    case ItemTypeCreation = 'item_type_creation';
    case LocationCreation = 'location_creation';
    case MagicLinkCreated = 'magic_link_created';
    case OrganizationCreation = 'organization_creation';
    case PatronCreation = 'patron_creation';
    case PatronTypeCreation = 'patron_type_creation';
    case RoleCreation = 'role_creation';
    case ApiKeyDeletion = 'api_key_deletion';
    case BranchDeletion = 'branch_deletion';
    case ItemTypeDeletion = 'item_type_deletion';
    case LocationDeletion = 'location_deletion';
    case OrganizationDeletion = 'organization_deletion';
    case PatronArchive = 'patron_archive';
    case PatronTypeDeletion = 'patron_type_deletion';
    case RoleDeletion = 'role_deletion';
    case TwoFaQrCodeGeneration = '2fa_qr_code_generation';
    case OrganizationJoined = 'organization_joined';
    case TwoFaRemoval = '2fa_removal';
    case AutoDeleteAccountUpdate = 'auto_delete_account_update';
    case BranchUpdate = 'branch_update';
    case ItemTypeUpdate = 'item_type_update';
    case LocationUpdate = 'location_update';
    case OrganizationUpdate = 'organization_update';
    case PatronUpdate = 'patron_update';
    case PatronTypeUpdate = 'patron_type_update';
    case RoleUpdate = 'role_update';
    case PersonalProfileUpdate = 'personal_profile_update';
    case UpdateUserPassword = 'update_user_password';
}
