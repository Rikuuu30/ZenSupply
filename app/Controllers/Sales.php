<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\SaleModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

class Sales extends BaseController
{
    public function new(): string
    {
        return view('sales/new', [
            'pageTitle' => 'Record sale', 'activePage' => 'sales',
            'products' => (new ProductModel())->where('is_active', 1)->where('stock_quantity >', 0)->orderBy('name', 'ASC')->findAll(),
            'customers' => (new CustomerModel())->where('is_active', 1)->orderBy('full_name', 'ASC')->findAll(),
            'formErrors' => session()->getFlashdata('formErrors') ?? [],
            'idempotencyToken' => bin2hex(random_bytes(32)),
        ]);
    }

    public function create(): RedirectResponse
    {
        $data = [
            'product_id' => trim((string) $this->request->getPost('product_id')),
            'customer_id' => trim((string) $this->request->getPost('customer_id')),
            'quantity' => trim((string) $this->request->getPost('quantity')),
            'idempotency_token' => trim((string) $this->request->getPost('idempotency_token')),
        ];
        $rules = [
            'product_id' => 'required|is_natural_no_zero',
            'customer_id' => 'permit_empty|is_natural_no_zero',
            'quantity' => 'required|is_natural_no_zero|less_than_equal_to[10000]',
            'idempotency_token' => 'required|exact_length[64]',
        ];
        if (! $this->validateData($data, $rules)) {
            return redirect()->back()->withInput()->with('formErrors', $this->validator->getErrors());
        }
        if (preg_match('/\A[a-f0-9]{64}\z/', $data['idempotency_token']) !== 1) {
            return redirect()->back()->withInput()->with('error', 'The sale form expired. Please submit it again.');
        }

        $db = db_connect();
        $existingSale = $db->table('sales')->where('idempotency_token', $data['idempotency_token'])->get()->getRowArray();
        if ($existingSale !== null) {
            return redirect()->to(site_url('sales/' . $existingSale['id']))->with('success', 'This sale was already recorded; no duplicate was created.');
        }

        $quantity = (int) $data['quantity'];
        $productId = (int) $data['product_id'];
        $customerId = $data['customer_id'] === '' ? null : (int) $data['customer_id'];
        $userId = (int) session()->get('user_id');

        try {
            $db->transBegin();
            $lock = $db->DBDriver === 'MySQLi' ? ' FOR UPDATE' : '';
            $productSql = $db->table('products')->select('id, name, price, stock_quantity')
                ->where('id', $productId)->where('is_active', 1)->getCompiledSelect();
            $product = $db->query($productSql . $lock)->getRowArray();
            if ($product === null) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'That product is not available for sale.');
            }
            if ((int) $product['stock_quantity'] < $quantity) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Only ' . (int) $product['stock_quantity'] . ' unit(s) are currently in stock.');
            }

            $customer = null;
            if ($customerId !== null) {
                $customerSql = $db->table('customers')->select('id, full_name')->where('id', $customerId)->where('is_active', 1)->getCompiledSelect();
                $customer = $db->query($customerSql . $lock)->getRowArray();
                if ($customer === null) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'That customer is no longer active. Choose another customer or select a walk-in sale.');
                }
            }

            $staffSql = $db->table('users')->select('id, full_name')->where('id', $userId)->where('is_active', 1)->getCompiledSelect();
            $staff = $db->query($staffSql . $lock)->getRowArray();
            if ($staff === null) {
                $db->transRollback();
                session()->destroy();
                return redirect()->to(site_url('login'))->with('error', 'Your staff account is no longer active. Please sign in again.');
            }

            $unitCents = $this->toCents((string) $product['price']);
            $totalCents = $unitCents * $quantity;
            if ($unitCents < 1 || $totalCents > 999_999_999_999) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'The calculated sale total is outside the supported range.');
            }

            $changed = $db->table('products')->set('stock_quantity', 'stock_quantity - ' . $quantity, false)
                ->where('id', $productId)->where('is_active', 1)->where('stock_quantity >=', $quantity)->update();
            if (! $changed || $db->affectedRows() !== 1) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Stock changed while you were recording this sale. Please review the current stock and try again.');
            }

            $db->table('sales')->insert([
                'product_id' => $productId,
                'customer_id' => $customerId,
                'sold_by' => $userId,
                'product_name' => $product['name'],
                'customer_name' => $customer['full_name'] ?? null,
                'sold_by_name' => $staff['full_name'],
                'quantity' => $quantity,
                'unit_price' => $this->fromCents($unitCents),
                'total_price' => $this->fromCents($totalCents),
                'idempotency_token' => $data['idempotency_token'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'The sale could not be saved; inventory was not changed. Please retry.');
            }
            $saleId = (int) $db->insertID();
            $db->transCommit();
        } catch (Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Sale transaction failed: {message}', ['message' => $exception->getMessage()]);
            $existingSale = $db->table('sales')->where('idempotency_token', $data['idempotency_token'])->get()->getRowArray();
            if ($existingSale !== null) {
                return redirect()->to(site_url('sales/' . $existingSale['id']))->with('success', 'This sale was already recorded; no duplicate was created.');
            }
            return redirect()->back()->withInput()->with('error', 'The sale could not be completed. No stock was deducted; please retry.');
        }

        return redirect()->to(site_url('sales/' . $saleId))->with('success', 'Sale recorded and inventory updated.');
    }

    public function index(): string
    {
        $search = trim((string) $this->request->getGet('q'));
        $from = trim((string) $this->request->getGet('from'));
        $to = trim((string) $this->request->getGet('to'));
        $errors = [];
        if ($from !== '' && ! $this->validDate($from)) {
            $errors['from'] = 'Enter a real date using YYYY-MM-DD.';
            $from = '';
        }
        if ($to !== '' && ! $this->validDate($to)) {
            $errors['to'] = 'Enter a real date using YYYY-MM-DD.';
            $to = '';
        }
        if ($from !== '' && $to !== '' && $from > $to) {
            $errors['to'] = 'The end date must be on or after the start date.';
            $to = '';
        }

        $model = new SaleModel();
        $this->applyHistoryFilters($model, $search, $from, $to);
        $sales = $model->orderBy('created_at', 'DESC')->paginate(15);

        $summary = db_connect()->table('sales');
        if ($from !== '') {
            $summary->where('created_at >=', $from . ' 00:00:00');
        }
        if ($to !== '') {
            $summary->where('created_at <=', $to . ' 23:59:59');
        }
        $totals = $summary->select('COUNT(*) AS transactions, COALESCE(SUM(total_price), 0) AS revenue, COALESCE(SUM(quantity), 0) AS units', false)->get()->getRowArray();

        return view('sales/index', [
            'pageTitle' => 'Sales history', 'activePage' => 'sales', 'sales' => $sales,
            'search' => $search, 'from' => $from, 'to' => $to, 'formErrors' => $errors,
            'pager' => $model->pager, 'summary' => $totals,
        ]);
    }

    public function show(int $id): string
    {
        $sale = (new SaleModel())->find($id);
        if ($sale === null) {
            throw PageNotFoundException::forPageNotFound('Sale record not found.');
        }
        return view('sales/show', [
            'pageTitle' => 'Receipt #' . $sale['id'], 'activePage' => 'sales', 'sale' => $sale,
        ]);
    }

    private function applyHistoryFilters(SaleModel $model, string $search, string $from, string $to): void
    {
        if ($search !== '') {
            $model->groupStart()->groupStart()
                ->like('product_name', $search)->orLike('customer_name', $search)->orLike('sold_by_name', $search)
                ->groupEnd();
            if (ctype_digit($search)) {
                $model->orWhere('id', (int) $search);
            }
            $model->groupEnd();
        }
        if ($from !== '') {
            $model->where('created_at >=', $from . ' 00:00:00');
        }
        if ($to !== '') {
            $model->where('created_at <=', $to . ' 23:59:59');
        }
    }

    private function validDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }

    private function toCents(string $amount): int
    {
        if (preg_match('/\A(\d{1,8})(?:\.(\d{1,2}))?\z/', $amount, $parts) !== 1) {
            return 0;
        }
        return ((int) $parts[1] * 100) + (int) str_pad($parts[2] ?? '', 2, '0');
    }

    private function fromCents(int $amount): string
    {
        return intdiv($amount, 100) . '.' . str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }
}
