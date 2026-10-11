<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAvatarToUsers extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('avatar', 'users')) {
            $this->forge->addColumn('users', [
                'avatar' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'full_name',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('avatar', 'users')) {
            $this->forge->dropColumn('users', 'avatar');
        }
    }
}
