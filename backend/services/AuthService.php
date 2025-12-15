<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/UserService.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../data/Roles.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService extends BaseService {
    private $userService;

    public function __construct() {
        require_once __DIR__ . '/../dao/UserDao.php';
        $this->userService = new UserService();
        parent::__construct(new UserDao());
    }

    /**
     * Register a new user
     */
    public function register($data) {
        if (empty($data['email']) || empty($data['password'])) {
            return [
                'success' => false,
                'error' => 'Email and password are required.'
            ];
        }

        // Check if email already exists
        $existingUser = $this->userService->getByEmail($data['email']);
        if ($existingUser['success']) {
            return [
                'success' => false,
                'error' => 'Email already registered.'
            ];
        }

        // Use existing UserService create method which handles validation and password hashing
        $result = $this->userService->create($data);

        if ($result['success']) {
            // Get the created user without password
            $user = $this->userService->getById($result['data']['id']);
            if ($user['success']) {
                unset($user['data']['password']);
                return [
                    'success' => true,
                    'data' => $user['data']
                ];
            }
        }

        // Propagate validation and other errors from UserService
        return [
            'success' => false,
            'error' => $result['message'] ?? 'Registration failed.',
            'errors' => $result['errors'] ?? null
        ];
    }

    /**
     * Login user and generate JWT token
     */
    public function login($data) {
        if (empty($data['email']) || empty($data['password'])) {
            return [
                'success' => false,
                'error' => 'Email and password are required.'
            ];
        }

        // Verify password using existing UserService method
        $result = $this->userService->verifyPassword($data['email'], $data['password']);

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => 'Invalid email or password.'
            ];
        }

        $user = $result['data'];

        // Derive role from is_admin flag
        $role = (!empty($user['is_admin']) && (int)$user['is_admin'] === 1)
            ? Roles::ADMIN
            : Roles::USER;

        // Attach role (and optional permissions) to user payload
        $user['role'] = $role;

        // Generate JWT token
        $jwt_payload = [
            'user' => $user,
            'iat' => time(),
            'exp' => time() + Config::JWT_TTL_SECONDS() // token lifetime from config
        ];

        $token = JWT::encode(
            $jwt_payload,
            Config::JWT_SECRET(),
            'HS256'
        );

        return [
            'success' => true,
            'data' => array_merge($user, ['token' => $token])
        ];
    }
}
