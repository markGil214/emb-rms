<?php

namespace App\Libraries;

use App\Models\UserModel;

class Authentication
{
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->session = service('session');  // Get session service
    }

    public function login($username, $password)
    {
        $user = $this->userModel
            ->where('username', $username)
            ->first();

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        $this->session->set([
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'logged_in' => true,
        ]);

        $this->generateToken($user['user_id']);

        return true;
    }

    public function logout()
    {
        if (!$this->check())
            return false;

        $user_id = $this->session->get('user_id');

        $this->userModel->update($user_id, ['remember_token' => null]);
        return $this->session->destroy();
    }

    public function check(): bool
    {
        return $this->session->get('logged_in') === true;
    }

    public function user(): ?array
    {
        if (!$this->check())
            return null;
        return [
            'user_id' => $this->session->get('user_id'),
            'username' => $this->session->get('username'),
            'email' => $this->session->get('email'),
            'role' => $this->session->get('role'),
        ];

    }

    public function generateToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $this->userModel->update($userId, ['remember_token' => $token]);

        return $token;
    }

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