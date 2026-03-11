<?php

namespace App\Libraries;

use App\Models\UserModel;
use Psr\Log\LoggerInterface;

class Authentication
{
    protected $userModel;
    protected $session;
    protected $logger;

    public function __construct()
    {
        $this->userModel = model(UserModel::class);
        $this->session = service('session');
        $this->logger = service('logger');
    }

    /**
     * Attempt to login user with credentials
     * Regenerates session ID after successful login (prevent session fixation)
     * 
     * @param string $username
     * @param string $password
     * @return bool
     */
    /**
     * Attempt to login user with credentials
     * Regenerates session ID after successful login (prevent session fixation)
     * 
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function login($username, $password)
    {
        $user = $this->userModel
            ->where('username', $username)
            ->first();

        if (!$user) {
            $this->logger->info('Failed login attempt - user not found: ' . $username);
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            $this->logger->info('Failed login attempt - invalid password for user: ' . $username);
            return false;
        }

        // Regenerate session ID to prevent session fixation attacks
        $this->session->regenerate();

        $this->session->set([
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'logged_in' => true,
        ]);

        $this->generateToken($user['user_id']);

        $this->logger->info('User logged in: ' . $username . ' (ID: ' . $user['user_id'] . ')');

        return true;
    }

    /**
     * Logout user and clear session
     * 
     * @return bool
     */
    public function logout()
    {
        if (!$this->check()) {
            return false;
        }

        $user_id = $this->session->get('user_id');
        $username = $this->session->get('username');

        // Skip validation since we're only clearing the remember token
        $this->userModel->skipValidation()->update($user_id, ['remember_token' => null]);
        
        $this->logger->info('User logged out: ' . $username . ' (ID: ' . $user_id . ')');
        
        return $this->session->destroy();
    }

    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    public function check(): bool
    {
        return $this->session->get('logged_in') === true;
    }

    /**
     * Get current logged-in user data
     * 
     * @return array|null
     */
    public function user(): ?array
    {
        if (!$this->check()) {
            return null;
        }

        return [
            'user_id' => $this->session->get('user_id'),
            'username' => $this->session->get('username'),
            'email' => $this->session->get('email'),
            'role' => $this->session->get('role'),
        ];
    }

    /**
     * Generate remember token for remember-me functionality
     * 
     * @param int $userId
     * @return string
     */
    public function generateToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        // Skip validation since we're only updating one field
        $this->userModel->skipValidation()->update($userId, ['remember_token' => $token]);
        return $token;
    }

    /**
     * Validate remember token and return user ID if valid
     * 
     * @param string $token
     * @return int|null
     */
    public function validateToken(string $token): ?int
    {
        $user = $this->userModel
            ->where('remember_token', $token)
            ->first();

        if (!$user) {
            return null;
        }

        return $user['user_id'];
    }
}