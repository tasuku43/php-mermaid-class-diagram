<?php
declare(strict_types=1);

namespace TestProject\Service;

use TestProject\Model\User;
use TestProject\Repository\UserRepositoryInterface;
use TestProject\Service\AuditLogger;
use TestProject\Service\AuditTarget;

trait RepositoryAwareTrait
{
    private UserRepositoryInterface $repoAware;
    private AuditLogger $logger;

    public function touch(User $user): void
    {
        // noop for test fixtures
    }

    public function log(AuditTarget $target): void
    {
        // noop for test fixtures
    }
}
