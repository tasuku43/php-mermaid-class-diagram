<?php
declare(strict_types=1);

namespace TestProject\Repository;

use TestProject\Model\User;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @var User[]
     */
    private array $users = [];

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function save(User $user): void
    {
        $this->users[$user->getId()] = $user;
    }

    public function delete(int $id): void
    {
        unset($this->users[$id]);
    }

    public function findAll(): array
    {
        return $this->users;
    }
}
