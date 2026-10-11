<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['username', 'full_name', 'password_hash', 'avatar', 'role', 'is_active', 'created_at'];
    protected $useTimestamps = false;
}
