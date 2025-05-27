<?php
declare(strict_types=1);

namespace TestProject\Controller;

use TestProject\Service\UserService;

class UserController extends AbstractController
{
    public function __construct(
        private UserService $userService
    ) {
    }

    public function show(int $id): array
    {
        $user = $this->userService->getUserById($id);
        
        if ($user === null) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'status' => $user->getStatus()->value
            ]
        ];
    }

    public function create(string $username, string $email): array
    {
        $user = $this->userService->createUser($username, $email);
        
        return [
            'success' => true,
            'data' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'status' => $user->getStatus()->value
            ]
        ];
    }

    public function suspend(int $id): array
    {
        try {
            $this->userService->suspendUser($id);
            return [
                'success' => true
            ];
        } catch (\InvalidArgumentException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function listActive(): array
    {
        $users = $this->userService->getAllActiveUsers();
        
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'status' => $user->getStatus()->value
            ];
        }
        
        return [
            'success' => true,
            'data' => $data
        ];
    }
    
    protected function handleRequest(): void
    {
        // Implementation of the abstract method
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'show':
                $id = (int)($_GET['id'] ?? 0);
                echo $this->jsonResponse($this->show($id));
                break;
            case 'create':
                $username = $_POST['username'] ?? '';
                $email = $_POST['email'] ?? '';
                echo $this->jsonResponse($this->create($username, $email));
                break;
            case 'suspend':
                $id = (int)($_GET['id'] ?? 0);
                echo $this->jsonResponse($this->suspend($id));
                break;
            case 'list':
            default:
                echo $this->jsonResponse($this->listActive());
                break;
        }
    }
}
