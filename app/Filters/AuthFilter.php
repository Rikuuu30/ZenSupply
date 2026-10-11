<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->has('user_id')) {
            return redirect()->to(site_url('login'))->with('error', 'Please sign in to access the store workspace.');
        }

        $user = (new UserModel())->select('id, username, full_name, role, is_active')->find((int) session()->get('user_id'));
        if ($user === null || (int) $user['is_active'] !== 1) {
            session()->destroy();
            return redirect()->to(site_url('login'))->with('error', 'Your staff account is no longer active. Please sign in again.');
        }

        // Keep a still-open session aligned with current database permissions.
        session()->set([
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => $user['role'] ?? 'staff',
        ]);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
