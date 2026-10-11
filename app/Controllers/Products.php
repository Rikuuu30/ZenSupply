<?php

namespace App\Controllers;

use App\Libraries\ImageUploadService;
use App\Models\ProductModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Files\UploadedFile;
use Throwable;

class Products extends BaseController
{
    private ProductModel $products;
    private ImageUploadService $images;

    public function __construct()
    {
        $this->products = new ProductModel();
        $this->images = new ImageUploadService();
    }

    public function index(): string
    {
        $search = trim((string) $this->request->getGet('q'));
        $stock = (string) $this->request->getGet('stock');
        $showArchived = $this->request->getGet('archived') === '1';
        $builder = $this->products->where('is_active', $showArchived ? 0 : 1)->orderBy('name', 'ASC');
        if ($search !== '') {
            $builder->groupStart()->like('name', $search)->orLike('sku', $search)->orLike('description', $search)->groupEnd();
        }
        if ($stock === 'low' && ! $showArchived) {
            $builder->where('stock_quantity <= low_stock_threshold', null, false);
        } elseif ($stock === 'out' && ! $showArchived) {
            $builder->where('stock_quantity', 0);
        }
        $products = $builder->paginate(12);

        return view('products/index', [
            'pageTitle' => 'Product inventory', 'activePage' => 'products',
            'products' => $products, 'search' => $search, 'stockFilter' => $stock,
            'showArchived' => $showArchived, 'total' => $builder->pager->getTotal(), 'pager' => $builder->pager,
            'canManage' => in_array(session()->get('role'), ['admin', 'manager', 'inventory'], true),
        ]);
    }

    public function new(): string
    {
        return $this->form(null);
    }

    public function show(int $id): string
    {
        $product = $this->products->find($id);
        if ($product === null) {
            throw PageNotFoundException::forPageNotFound('Product not found.');
        }
        $sales = db_connect()->table('sales')->where('product_id', $id)->orderBy('created_at', 'DESC')->limit(8)->get()->getResultArray();
        return view('products/show', [
            'pageTitle' => $product['name'], 'activePage' => 'products', 'product' => $product, 'sales' => $sales,
            'canManage' => in_array(session()->get('role'), ['admin', 'manager', 'inventory'], true),
        ]);
    }

    public function create()
    {
        $data = $this->productInput();
        $rules = $this->productRules();
        if ($data['sku'] !== null) {
            $rules['sku'] = 'max_length[40]|is_unique[products.sku]';
        }
        if (! $this->validateData($data, $rules)) {
            return redirect()->back()->withInput()->with('formErrors', $this->validator->getErrors());
        }

        $image = $this->storeProductImage();
        if (isset($image['error'])) {
            return redirect()->back()->withInput()->with('formErrors', ['image' => $image['error']]);
        }
        $data += [
            'image' => $image['path'] ?? null, 'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => null,
        ];
        if (! $this->products->insert($data)) {
            $this->images->removeGenerated($data['image']);
            return redirect()->back()->withInput()->with('formErrors', $this->products->errors() ?: ['general' => 'The product could not be saved.']);
        }
        return redirect()->to(site_url('products'))->with('success', 'Product added to inventory.');
    }

    public function edit(int $id)
    {
        $product = $this->products->find($id);
        if ($product === null) {
            throw PageNotFoundException::forPageNotFound('Product not found.');
        }
        return $this->form($product);
    }

    public function update(int $id)
    {
        $existing = $this->products->find($id);
        if ($existing === null) {
            return redirect()->to(site_url('products'))->with('error', 'That product no longer exists.');
        }
        $data = $this->productInput();
        $rules = $this->productRules();
        if ($data['sku'] !== null) {
            $rules['sku'] = "max_length[40]|is_unique[products.sku,id,{$id}]";
        }
        if (! $this->validateData($data, $rules)) {
            return redirect()->back()->withInput()->with('formErrors', $this->validator->getErrors());
        }

        $image = $this->storeProductImage();
        if (isset($image['error'])) {
            return redirect()->back()->withInput()->with('formErrors', ['image' => $image['error']]);
        }
        if (isset($image['path'])) {
            $data['image'] = $image['path'];
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        if (! $this->products->update($id, $data)) {
            $this->images->removeGenerated($image['path'] ?? null);
            return redirect()->back()->withInput()->with('formErrors', $this->products->errors() ?: ['general' => 'The product could not be updated.']);
        }
        if (isset($image['path'])) {
            $this->images->removeGenerated($existing['image'] ?? null);
        }
        return redirect()->to(site_url('products/' . $id))->with('success', 'Product details updated.');
    }

    public function archive(int $id)
    {
        $product = $this->products->find($id);
        if ($product === null) {
            throw PageNotFoundException::forPageNotFound('Product not found.');
        }
        if ((int) $product['stock_quantity'] > 0) {
            return redirect()->to(site_url('products/' . $id))->with('error', 'A product can only be archived after its remaining stock is zero. Record a stock adjustment through editing first.');
        }
        $this->products->update($id, ['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        return redirect()->to(site_url('products'))->with('success', 'Product archived. Existing sales keep their product details.');
    }

    public function restore(int $id)
    {
        if ($this->products->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Product not found.');
        }
        $this->products->update($id, ['is_active' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
        return redirect()->to(site_url('products?archived=1'))->with('success', 'Product restored to the active catalog.');
    }

    private function form(?array $product): string
    {
        return view('products/form', [
            'pageTitle' => $product === null ? 'Add product' : 'Edit product', 'activePage' => 'products',
            'product' => $product, 'formErrors' => session()->getFlashdata('formErrors') ?? [],
            'formAction' => site_url($product === null ? 'products' : 'products/' . $product['id']),
        ]);
    }

    private function productInput(): array
    {
        $sku = trim((string) $this->request->getPost('sku'));
        return [
            'sku' => $sku === '' ? null : strtoupper($sku),
            'name' => preg_replace('/\s+/u', ' ', trim((string) $this->request->getPost('name'))),
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'price' => trim((string) $this->request->getPost('price')),
            'stock_quantity' => trim((string) $this->request->getPost('stock_quantity')),
            'low_stock_threshold' => trim((string) $this->request->getPost('low_stock_threshold')),
        ];
    }

    private function productRules(): array
    {
        return [
            'name' => 'required|min_length[2]|max_length[150]',
            'description' => 'permit_empty|max_length[10000]',
            'price' => 'required|decimal|greater_than[0]|less_than_equal_to[99999999.99]',
            'stock_quantity' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[2147483647]',
            'low_stock_threshold' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[2147483647]',
        ];
    }

    private function storeProductImage(): array
    {
        $file = $this->request->getFile('image');
        if (! $file instanceof UploadedFile || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return [];
        }
        try {
            return ['path' => $this->images->storeThumbnail($file, 'products', 'product', 900)];
        } catch (Throwable $exception) {
            log_message('warning', 'Product image upload rejected: {message}', ['message' => $exception->getMessage()]);
            return ['error' => $exception->getMessage()];
        }
    }
}
