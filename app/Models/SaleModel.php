<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleModel extends Model
{
    protected $table = 'sales';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'product_id', 'customer_id', 'sold_by', 'product_name', 'customer_name',
        'sold_by_name', 'quantity', 'unit_price', 'total_price', 'idempotency_token', 'created_at',
    ];
    protected $useTimestamps = false;
}
