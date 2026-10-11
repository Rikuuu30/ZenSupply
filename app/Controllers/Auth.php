<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class Auth extends BaseController
{
    public function login(): string|RedirectResponse
    {
        if (session()->has('user_id')) {
            return redirect()->to(site_url('/'));
        }

        return view('auth/login', [
            'pageTitle'  => 'Staff sign in',
            'activePage' => 'login',
            'formErrors' => session()->getFlashdata('formErrors') ?? [],
        ]);
    }

    public function attempt(): RedirectResponse
    {
        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');
        $credentials = ['username' => $username, 'password' => $password];

        $rules = [
            'username' => 'required|max_length[50]',
            'password' => 'required|max_length[255]',
        ];

        if (! $this->validateData($credentials, $rules)) {
            return redirect()->to(site_url('login'))->with('formErrors', $this->validator->getErrors());
        }

        $limitKey = 'zen-supply-login-' . hash('sha256', $this->request->getIPAddress());
        if (! service('throttler')->check($limitKey, 5, 60)) {
            return redirect()->to(site_url('login'))->with('error', 'Too many sign-in attempts. Wait a minute and try again.');
        }

        $user = (new UserModel())->where('username', $username)->where('is_active', 1)->first();
        if ($user === null || ! is_string($user['password_hash'] ?? null) || ! password_verify($password, $user['password_hash'])) {
            return redirect()->to(site_url('login'))->with('error', 'The username or password is incorrect.');
        }

        session()->regenerate(true);
        session()->set([
            'user_id'   => (int) $user['id'],
            'username'  => $user['username'],
            'full_name' => $user['full_name'],
            'role'      => $user['role'] ?? 'staff',
        ]);

        return redirect()->to(site_url('/'))->with('success', 'Welcome back, ' . $user['full_name'] . '.');
    }

    public function logout(): RedirectResponse
    {
        session()->remove(['user_id', 'username', 'full_name']);
        session()->destroy();

        return redirect()->to(site_url('login'))->with('success', 'You have been signed out.');
    }
}
