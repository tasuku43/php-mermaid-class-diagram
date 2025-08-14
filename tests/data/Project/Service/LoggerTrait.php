<?php
declare(strict_types=1);

namespace TestProject\Service;

use TestProject\Service\AuditLogger;
use TestProject\Service\AuditTarget;

trait LoggerTrait
{
    private AuditLogger $logger;

    public function log(AuditTarget $target): void
    {
        // noop for test fixtures
    }
}

