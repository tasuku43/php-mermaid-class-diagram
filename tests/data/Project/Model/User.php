<?php
declare(strict_types=1);

namespace TestProject\Model;

class User
{
    public function __construct(
        private int $id,
        private string $username,
        private string $email,
        private UserStatus $status
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setStatus(UserStatus $status): void
    {
        $this->status = $status;
    }
}
