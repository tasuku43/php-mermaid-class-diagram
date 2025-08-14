<?php
declare(strict_types=1);

namespace TestProject\Service;

use TestProject\Model\User;
use TestProject\Repository\UserRepositoryInterface;

trait RepositoryAwareTrait
{
    use LoggerTrait;
    private UserRepositoryInterface $repoAware;

    public function touch(User $user): void
    {
        // noop for test fixtures
    }

}
