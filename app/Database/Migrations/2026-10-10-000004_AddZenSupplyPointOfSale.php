<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds inventory, transaction history, and safe archival flags without
 * replacing the existing Zen Supply customer or staff records.
 */
class AddZenSupplyPointOfSale extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('is_active', 'customers')) {
            $this->forge->addColumn('customers', [
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'phone'],
            ]);
        }

        if (! $this->db->fieldExists('is_active', 'users')) {
            $this->forge->addColumn('users', [
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'avatar'],
            ]);
        }

        if (! $this->db->fieldExists('role', 'users')) {
            $this->forge->addColumn('users', [
                'role' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'staff', 'after' => 'full_name'],
            ]);
        }

        if (! $this->db->tableExists('products')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'sku' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
                'name' => ['type' => 'VARCHAR', 'constraint' => 150],
                'description' => ['type' => 'TEXT', 'null' => true],
                'price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'stock_quantity' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
                'low_stock_threshold' => ['type' => 'INT', 'unsigned' => true, 'default' => 5],
                'image' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at' => ['type' => 'DATETIME'],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('sku', 'products_sku_unique');
            $this->forge->addKey('is_active', false, false, 'products_active_index');
            $this->forge->createTable('products', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_unicode_ci']);
        }

        if (! $this->db->tableExists('sales')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'product_id' => ['type' => 'INT', 'unsigned' => true],
                'customer_id' => ['type' => 'INT', 'null' => true],
                'sold_by' => ['type' => 'INT', 'null' => false],
                'product_name' => ['type' => 'VARCHAR', 'constraint' => 150],
                'customer_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'sold_by_name' => ['type' => 'VARCHAR', 'constraint' => 100],
                'quantity' => ['type' => 'INT', 'unsigned' => true],
                'unit_price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'total_price' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
                'idempotency_token' => ['type' => 'CHAR', 'constraint' => 64],
                'created_at' => ['type' => 'DATETIME'],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('product_id');
            $this->forge->addKey('customer_id');
            $this->forge->addKey('sold_by');
            $this->forge->addKey('created_at');
            $this->forge->addUniqueKey('idempotency_token', 'sales_idempotency_unique');
            $this->forge->addForeignKey('product_id', 'products', 'id', 'RESTRICT', 'RESTRICT', 'sales_product_fk');
            $this->forge->addForeignKey('customer_id', 'customers', 'id', 'RESTRICT', 'SET NULL', 'sales_customer_fk');
            $this->forge->addForeignKey('sold_by', 'users', 'id', 'RESTRICT', 'RESTRICT', 'sales_staff_fk');
            $this->forge->createTable('sales', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_unicode_ci']);
        }

        // Promote the existing local administrator while preserving all IDs,
        // password hashes, names, and profile images.
        $this->db->table('users')->where('username', 'admin.rikuuu')->update(['role' => 'admin']);
    }

    public function down()
    {
        // Do not remove tables or columns containing live POS history during
        // rollback. Reversal should be an explicit, backed-up data operation.
    }
}
