<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/BaseDao.php';
require_once __DIR__ . '/../config.php';

class UserService extends BaseService {
    public function __construct() {
        $dao = new UserDao();
        parent::__construct($dao);
    }

    /**
     * Create a new user with validation
     */
    public function create($data) {
        $validator = $this->getValidator();
        $validator->clear();

        // Validation rules
        $validator->required('name', $data['name'] ?? null);
        $validator->required('email', $data['email'] ?? null);
        $validator->required('password', $data['password'] ?? null);
        
        if (isset($data['email'])) {
            $validator->email('email', $data['email']);
            $validator->maxLength('email', $data['email'], 100);
        }
        
        if (isset($data['name'])) {
            $validator->minLength('name', $data['name'], 2);
            $validator->maxLength('name', $data['name'], 100);
        }
        
        if (isset($data['password'])) {
            $validator->minLength('password', $data['password'], 6, 'Password must be at least 6 characters');
        }

        // Check if email already exists
        if (isset($data['email']) && $this->dao->getByEmail($data['email'])) {
            return [
                'success' => false,
                'message' => 'Email already exists',
                'errors' => ['email' => 'This email is already registered']
            ];
        }

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->getErrors()
            ];
        }

        // Hash password before storing
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Set default values
        $data['is_admin'] = $data['is_admin'] ?? 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return parent::create($data);
    }

    /**
     * Update user with validation
     */
    public function update($id, $data) {
        $validator = $this->getValidator();
        $validator->clear();

        // Validation rules for update
        if (isset($data['email'])) {
            $validator->email('email', $data['email']);
            $validator->maxLength('email', $data['email'], 100);
            
            // Check if email is already taken by another user
            $existingUser = $this->dao->getByEmail($data['email']);
            if ($existingUser && $existingUser['id'] != $id) {
                return [
                    'success' => false,
                    'message' => 'Email already exists',
                    'errors' => ['email' => 'This email is already registered']
                ];
            }
        }
        
        if (isset($data['name'])) {
            $validator->minLength('name', $data['name'], 2);
            $validator->maxLength('name', $data['name'], 100);
        }
        
        if (isset($data['password'])) {
            $validator->minLength('password', $data['password'], 6, 'Password must be at least 6 characters');
            // Hash password before storing
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->getErrors()
            ];
        }

        // Update timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');

        return parent::update($id, $data);
    }

    /**
     * Get user by email
     */
    public function getByEmail($email) {
        try {
            if (empty($email)) {
                return [
                    'success' => false,
                    'message' => 'Email is required'
                ];
            }

            $user = $this->dao->getByEmail($email);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            return [
                'success' => true,
                'data' => $user
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update user password
     */
    public function updatePassword($id, $password) {
        $validator = $this->getValidator();
        $validator->clear();

        $validator->required('password', $password);
        $validator->minLength('password', $password, 6, 'Password must be at least 6 characters');

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->getErrors()
            ];
        }

        try {
            // Check if user exists
            $user = $this->dao->getById($id);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $result = $this->dao->updatePassword($id, $hashedPassword);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Password updated successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to update password'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update password: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify user password
     */
    public function verifyPassword($email, $password) {
        $result = $this->getByEmail($email);
        
        if (!$result['success']) {
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }

        $user = $result['data'];
        
        if (password_verify($password, $user['password'])) {
            // Remove password from response
            unset($user['password']);
            return [
                'success' => true,
                'data' => $user
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid credentials'
        ];
    }
}
?>
