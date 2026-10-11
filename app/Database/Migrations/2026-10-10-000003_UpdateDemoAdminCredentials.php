<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateDemoAdminCredentials extends Migration
{
    private const OLD_USERNAME = 'admin.mara';
    private const NEW_USERNAME = 'admin.rikuuu';
    private const NEW_PASSWORD = 'ZSD123';

    public function up()
    {
        $existingAdmin = $this->db->table('users')
            ->where('username', self::OLD_USERNAME)
            ->get()
            ->getRowArray();

        if ($existingAdmin !== null) {
            $this->db->table('users')
                ->where('id', $existingAdmin['id'])
                ->update([
                    'username'      => self::NEW_USERNAME,
                    'password_hash' => password_hash(self::NEW_PASSWORD, PASSWORD_DEFAULT),
                ]);

            return;
        }

        // A fresh SQL import already contains this username; ensure its hash
        // still matches the requested local demo password.
        $this->db->table('users')
            ->where('username', self::NEW_USERNAME)
            ->update(['password_hash' => password_hash(self::NEW_PASSWORD, PASSWORD_DEFAULT)]);
    }

    public function down()
    {
        // Keep account credentials intact when rolling back unrelated schema changes.
    }
}
