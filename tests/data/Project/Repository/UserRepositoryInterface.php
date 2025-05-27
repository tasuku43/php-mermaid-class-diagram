<?php
declare(strict_types=1);

namespace TestProject\Repository;

use TestProject\Model\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function save(User $user): void;
    public function delete(int $id): void;
    public function findAll(): array;
}
