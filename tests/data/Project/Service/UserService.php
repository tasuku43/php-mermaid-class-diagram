<?php
declare(strict_types=1);

namespace TestProject\Service;

use TestProject\Model\User;
use TestProject\Model\UserStatus;
use TestProject\Repository\UserRepositoryInterface;

class UserService
{
    use RepositoryAwareTrait;
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function createUser(string $username, string $email): User
    {
        $user = new User(
            mt_rand(1, 1000), // Simple ID generation for test
            $username,
            $email,
            UserStatus::ACTIVE
        );
        
        $this->userRepository->save($user);
        
        return $user;
    }

    public function suspendUser(int $id): void
    {
        $user = $this->userRepository->findById($id);
        
        if ($user === null) {
            throw new \InvalidArgumentException("User with ID $id not found");
        }
        
        $user->setStatus(UserStatus::SUSPENDED);
        $this->userRepository->save($user);
    }

    public function getAllActiveUsers(): array
    {
        $users = $this->userRepository->findAll();
        
        return array_filter($users, function (User $user) {
            return $user->getStatus() === UserStatus::ACTIVE;
        });
    }
}
