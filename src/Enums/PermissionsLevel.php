<?php

declare(strict_types=1);

namespace Nukeflame\Webmatics\Enums;

use BenSampo\Enum\Enum;

final class PermissionsLevel extends Enum
{
    const SUPERADMIN = 6; // Full system access
    const ADMIN      = 5; // Full access
    const MODERATOR  = 4; // Can manage users and content
    const EDITOR     = 3; // Can edit content but has limited admin rights
    const VIEWER     = 2; // Can only view content
    const GUEST      = 1; // Limited access, mostly read-only
}
