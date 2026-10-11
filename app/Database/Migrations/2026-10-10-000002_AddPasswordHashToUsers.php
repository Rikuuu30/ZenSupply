<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPasswordHashToUsers extends Migration
{
    private const DEMO_PASSWORD = 'ZenSupplyDemo!2026';

    public function up()
    {
        if (! $this->db->fieldExists('password_hash', 'users')) {
            $this->forge->addColumn('users', [
                'password_hash' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'full_name',
                ],
            ]);
        }

        $users = $this->db->table('users')
            ->select('id')
            ->where('password_hash', null)
            ->get()
            ->getResultArray();

        foreach ($users as $user) {
            $this->db->table('users')
                ->where('id', $user['id'])
                ->update(['password_hash' => password_hash(self::DEMO_PASSWORD, PASSWORD_DEFAULT)]);
        }

        $this->forge->modifyColumn('users', [
            'password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->fieldExists('password_hash', 'users')) {
            $this->forge->dropColumn('users', 'password_hash');
        }
    }
}
