<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'sku', 'name', 'description', 'price', 'stock_quantity',
        'low_stock_threshold', 'image', 'is_active', 'created_at', 'updated_at',
    ];
    protected $useTimestamps = false;
}
