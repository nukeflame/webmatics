<?php

declare(strict_types=1);

namespace Nukeflame\Webmatics\Enums;

use BenSampo\Enum\Enum;

final class SystemActionEnums extends Enum
{
    const COVER_REGISTRATION_PROCESS = 'cover-registration';
    const CLAIM_INTIMATION_PROCESS = 'claim_intimation_process';
    const CLAIM_VERIFICATION_PROCESS = 'claim_verification_process';
    const CLAIM_REGISTRATION = 'claim_registration';
    const REQUISITION_PROCESS = 'requisition-process';
    const GL_BATCH_PROCESS = 'gl-batch-process';

    const VERIFY_CLAIM_INTIMATION_PROCESS = 'verify_claim_intimation_process';
    const VERIFY_CLAIM_NOTIFICATION_PROCESS = 'verify-claim-notification';
    const VERIFY_COVER_PROCESS = 'verify_cover';
    const VERIFY_CLAIM_PROCESS = 'verify_claim';
    const AUTHORIZE_REQUISITION_PROCESS = 'authorize-requisition';
    const APPROVE_REQUISITION_PROCESS = 'approve-requisition';
    const VERIFY_GL_BATCH_PROCESS = 'verify-glbatch';
}
