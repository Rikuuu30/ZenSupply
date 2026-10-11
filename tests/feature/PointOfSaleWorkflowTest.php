<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;

final class PointOfSaleWorkflowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect('tests');
        $this->db->initialize();
        foreach (['sales', 'products', 'customers', 'users'] as $table) {
            $this->db->query('DROP TABLE IF EXISTS db_' . $table);
        }
        $this->db->query('CREATE TABLE db_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT, username VARCHAR(50) NOT NULL UNIQUE,
            full_name VARCHAR(100) NOT NULL, role VARCHAR(20) NOT NULL DEFAULT "staff",
            password_hash VARCHAR(255) NOT NULL, avatar VARCHAR(255) NULL,
            is_active INTEGER NOT NULL DEFAULT 1, created_at DATETIME NOT NULL
        )');
        $this->db->query('CREATE TABLE db_customers (
            id INTEGER PRIMARY KEY AUTOINCREMENT, full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL, phone VARCHAR(20) NULL,
            is_active INTEGER NOT NULL DEFAULT 1, created_at DATETIME NOT NULL
        )');
        $this->db->query('CREATE TABLE db_products (
            id INTEGER PRIMARY KEY AUTOINCREMENT, sku VARCHAR(40) NULL UNIQUE,
            name VARCHAR(150) NOT NULL, description TEXT NULL, price DECIMAL(10,2) NOT NULL,
            stock_quantity INTEGER NOT NULL DEFAULT 0, low_stock_threshold INTEGER NOT NULL DEFAULT 5,
            image VARCHAR(255) NULL, is_active INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL, updated_at DATETIME NULL
        )');
        $this->db->query('CREATE TABLE db_sales (
            id INTEGER PRIMARY KEY AUTOINCREMENT, product_id INTEGER NOT NULL,
            customer_id INTEGER NULL, sold_by INTEGER NOT NULL, product_name VARCHAR(150) NOT NULL,
            customer_name VARCHAR(100) NULL, sold_by_name VARCHAR(100) NOT NULL,
            quantity INTEGER NOT NULL, unit_price DECIMAL(10,2) NOT NULL,
            total_price DECIMAL(12,2) NOT NULL, idempotency_token CHAR(64) NOT NULL UNIQUE,
            created_at DATETIME NOT NULL
        )');
        $this->db->table('users')->insert([
            'username' => 'admin.rikuuu', 'full_name' => 'Zen Administrator', 'role' => 'admin',
            'password_hash' => password_hash('ZSD123', PASSWORD_DEFAULT), 'is_active' => 1,
            'created_at' => '2025-01-05 08:00:00',
        ]);
        $this->db->table('users')->insert([
            'username' => 'cashier.one', 'full_name' => 'Sample Cashier', 'role' => 'cashier',
            'password_hash' => password_hash('LongDemoPass2026!', PASSWORD_DEFAULT), 'is_active' => 1,
            'created_at' => '2025-01-06 08:00:00',
        ]);
        $this->db->table('customers')->insert([
            'full_name' => 'Jian Pineda', 'email' => 'jian@example.test', 'phone' => null,
            'is_active' => 1, 'created_at' => '2025-01-12 10:15:00',
        ]);
    }

    protected function tearDown(): void
    {
        foreach (['sales', 'products', 'customers', 'users'] as $table) {
            $this->db->query('DROP TABLE IF EXISTS db_' . $table);
        }
        $this->db->close();
        parent::tearDown();
    }

    public function testPublicPagesAndGuestRedirects(): void
    {
        $login = $this->get('/login');
        $login->assertOK();
        $login->assertSee('Sign in');
        $this->get('/about')->assertOK();
        foreach (['/', '/customers', '/products', '/sales', '/sales/new', '/users'] as $uri) {
            $this->get($uri)->assertRedirectTo(site_url('login'));
        }
        foreach (['/customers', '/products', '/sales', '/users', '/customers/1/archive', '/products/1/archive', '/users/1/archive'] as $uri) {
            $this->post($uri, $this->csrfPayload())->assertRedirectTo(site_url('login'));
        }
    }

    public function testValidAndInvalidLoginAndSessionRole(): void
    {
        $this->post('/login', $this->csrfPayload(['username' => 'admin.rikuuu', 'password' => 'ZSD123']))
            ->assertRedirectTo(site_url('/'));
        $this->assertSame(1, session()->get('user_id'));
        $this->assertSame('admin', session()->get('role'));
        $this->assertNull(session()->get('password'));

        session()->destroy();
        $this->post('/login', $this->csrfPayload(['username' => 'unknown.person', 'password' => 'not-correct']))
            ->assertRedirectTo(site_url('login'));
        $this->assertFalse(session()->has('user_id'));
        $this->assertSame('The username or password is incorrect.', session()->getFlashdata('error'));
    }

    public function testAdminCanOpenStorePagesButCashierCannotManageStaffOrInventory(): void
    {
        $admin = ['user_id' => 1, 'username' => 'admin.rikuuu', 'full_name' => 'Zen Administrator', 'role' => 'admin'];
        foreach (['/', '/customers', '/products', '/sales', '/sales/new', '/users'] as $uri) {
            $this->withSession($admin)->get($uri)->assertOK();
        }
        $cashier = ['user_id' => 2, 'username' => 'cashier.one', 'full_name' => 'Sample Cashier', 'role' => 'cashier'];
        $this->withSession($cashier)->get('/users')->assertRedirectTo(site_url('/'));
        $this->withSession($cashier)->get('/products/new')->assertRedirectTo(site_url('/'));
        $this->withSession($cashier)->get('/sales/new')->assertOK();
    }

    public function testProductCreateValidationAndArchiveWorkflow(): void
    {
        $admin = ['user_id' => 1, 'username' => 'admin.rikuuu', 'full_name' => 'Zen Administrator', 'role' => 'admin'];
        $this->withSession($admin)->post('/products', $this->csrfPayload([
            'name' => 'Training Gloves', 'sku' => 'BOX-100', 'description' => 'Leather gloves',
            'price' => '1250.00', 'stock_quantity' => '3', 'low_stock_threshold' => '4',
        ]))->assertRedirectTo(site_url('products'));
        $product = $this->db->table('products')->where('sku', 'BOX-100')->get()->getRowArray();
        $this->assertNotNull($product);
        $this->assertSame(3, (int) $product['stock_quantity']);

        $this->withSession($admin)->post('/products/' . $product['id'], $this->csrfPayload([
            'name' => 'Updated Training Gloves', 'sku' => 'BOX-100', 'description' => 'Updated leather gloves',
            'price' => '1299.00', 'stock_quantity' => '0', 'low_stock_threshold' => '2',
        ]))->assertRedirectTo(site_url('products/' . $product['id']));
        $updatedProduct = $this->db->table('products')->where('id', $product['id'])->get()->getRowArray();
        $this->assertSame('Updated Training Gloves', $updatedProduct['name']);
        $this->assertSame('1299.00', number_format((float) $updatedProduct['price'], 2, '.', ''));
        $this->withSession($admin)->post('/products/' . $product['id'] . '/archive', $this->csrfPayload())
            ->assertRedirectTo(site_url('products'));
        $this->assertSame(1, (int) $this->db->table('products')->where('id', $product['id'])->countAllResults());
        $this->assertSame(0, (int) $this->db->table('products')->where('id', $product['id'])->where('is_active', 1)->countAllResults());
    }

    public function testCustomerCreateAndArchiveRetainsRecord(): void
    {
        $admin = ['user_id' => 1, 'username' => 'admin.rikuuu', 'full_name' => 'Zen Administrator', 'role' => 'admin'];
        $this->withSession($admin)->post('/customers', $this->csrfPayload([
            'full_name' => 'Taylor Santos', 'email' => 'taylor@example.test', 'phone' => '',
        ]))->assertRedirectTo(site_url('customers'));
        $customer = $this->db->table('customers')->where('email', 'taylor@example.test')->get()->getRowArray();
        $this->assertNotNull($customer);
        $this->withSession($admin)->post('/customers/' . $customer['id'], $this->csrfPayload([
            'full_name' => 'Taylor Santos Updated', 'email' => 'taylor@example.test', 'phone' => '09170000000',
        ]))->assertRedirectTo(site_url('customers/' . $customer['id']));
        $updatedCustomer = $this->db->table('customers')->where('id', $customer['id'])->get()->getRowArray();
        $this->assertSame('Taylor Santos Updated', $updatedCustomer['full_name']);
        $this->assertSame('09170000000', $updatedCustomer['phone']);
        $this->withSession($admin)->post('/customers/' . $customer['id'] . '/archive', $this->csrfPayload())
            ->assertRedirectTo(site_url('customers'));
        $this->assertSame(1, (int) $this->db->table('customers')->where('id', $customer['id'])->where('is_active', 0)->countAllResults());
    }

    public function testSaleCalculatesFromDatabaseAndDeductsStockOnlyOnce(): void
    {
        $this->db->table('products')->insert([
            'sku' => 'WRAP-45', 'name' => 'Hand Wraps', 'price' => '420.00', 'stock_quantity' => 5,
            'low_stock_threshold' => 2, 'is_active' => 1, 'created_at' => '2025-02-01 08:00:00',
        ]);
        $productId = (int) $this->db->insertID();
        $session = ['user_id' => 1, 'username' => 'admin.rikuuu', 'full_name' => 'Zen Administrator', 'role' => 'admin'];
        $token = str_repeat('a', 64);
        $saleInput = $this->csrfPayload([
            'product_id' => (string) $productId, 'customer_id' => '', 'quantity' => '2',
            'idempotency_token' => $token,
        ]);
        $this->withSession($session)->post('/sales', $this->csrfPayload([
            'product_id' => (string) $productId, 'customer_id' => '', 'quantity' => '2',
            'idempotency_token' => $token,
        ]))->assertRedirectTo(site_url('sales/1'));
        $this->assertSame(3, (int) $this->db->table('products')->where('id', $productId)->get()->getRowArray()['stock_quantity']);
        $sale = $this->db->table('sales')->where('idempotency_token', $token)->get()->getRowArray();
        $this->assertSame('840.00', number_format((float) $sale['total_price'], 2, '.', ''));
        $this->assertSame('Hand Wraps', $sale['product_name']);

        $this->assertSame(3, (int) $this->db->table('products')->where('id', $productId)->get()->getRowArray()['stock_quantity']);
        $this->assertSame(1, (int) $this->db->table('sales')->countAllResults());
    }

    public function testAdministratorCanCreateEditAndArchiveStaffWithoutReplacingBlankPassword(): void
    {
        $admin = ['user_id' => 1, 'username' => 'admin.rikuuu', 'full_name' => 'Zen Administrator', 'role' => 'admin'];
        $this->withSession($admin)->post('/users', $this->csrfPayload([
            'username' => 'inventory.test', 'full_name' => 'Temporary Inventory Staff',
            'role' => 'inventory', 'password' => 'A-Long-Temp-Password-2026!',
        ]))->assertRedirectTo(site_url('users'));
        $user = $this->db->table('users')->where('username', 'inventory.test')->get()->getRowArray();
        $this->assertNotNull($user);
        $originalHash = $user['password_hash'];
        $this->assertTrue(password_verify('A-Long-Temp-Password-2026!', $originalHash));

        $this->withSession($admin)->post('/users/' . $user['id'], $this->csrfPayload([
            'username' => 'inventory.test', 'full_name' => 'Temporary Manager', 'role' => 'manager', 'password' => '',
        ]))->assertRedirectTo(site_url('users/' . $user['id']));
        $updatedUser = $this->db->table('users')->where('id', $user['id'])->get()->getRowArray();
        $this->assertSame('manager', $updatedUser['role']);
        $this->assertSame('Temporary Manager', $updatedUser['full_name']);
        $this->assertSame($originalHash, $updatedUser['password_hash']);

        $this->withSession($admin)->post('/users/' . $user['id'] . '/archive', $this->csrfPayload())
            ->assertRedirectTo(site_url('users'));
        $this->assertSame(0, (int) $this->db->table('users')->where('id', $user['id'])->get()->getRowArray()['is_active']);
    }

    public function testInsufficientStockDoesNotCreateSaleOrChangeInventory(): void
    {
        $this->db->table('products')->insert([
            'sku' => 'BELT-1', 'name' => 'Training Belt', 'price' => '199.00', 'stock_quantity' => 1,
            'low_stock_threshold' => 1, 'is_active' => 1, 'created_at' => '2025-02-01 08:00:00',
        ]);
        $productId = (int) $this->db->insertID();
        $session = ['user_id' => 1, 'username' => 'admin.rikuuu', 'full_name' => 'Zen Administrator', 'role' => 'admin'];
        $this->withSession($session)->post('/sales', $this->csrfPayload([
            'product_id' => (string) $productId, 'customer_id' => '', 'quantity' => '2',
            'idempotency_token' => str_repeat('b', 64),
        ]))->assertRedirect();
        $this->assertSame(1, (int) $this->db->table('products')->where('id', $productId)->get()->getRowArray()['stock_quantity']);
        $this->assertSame(0, (int) $this->db->table('sales')->countAllResults());
    }

    public function testLogoutDestroysAuthenticatedSession(): void
    {
        $this->withSession(['user_id' => 1, 'username' => 'admin.rikuuu', 'full_name' => 'Zen Administrator', 'role' => 'admin'])
            ->post('/logout', $this->csrfPayload())->assertRedirectTo(site_url('login'));
        $this->assertFalse(session()->has('user_id'));
    }

    private function csrfPayload(array $payload = []): array
    {
        return array_merge($payload, [csrf_token() => csrf_hash()]);
    }
}
