<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Customers extends BaseController
{
    private CustomerModel $customers;

    public function __construct()
    {
        $this->customers = new CustomerModel();
    }

    public function index(): string
    {
        $search = trim((string) $this->request->getGet('q'));
        $showArchived = $this->request->getGet('archived') === '1';
        $builder = $this->customers->orderBy('full_name', 'ASC');
        $builder->where('is_active', $showArchived ? 0 : 1);
        if ($search !== '') {
            $builder->groupStart()
                ->like('full_name', $search)
                ->orLike('email', $search)
                ->orLike('phone', $search)
                ->groupEnd();
        }
        $customers = $builder->paginate(10);

        return view('customers/index', [
            'pageTitle' => 'Customer accounts', 'activePage' => 'customers',
            'customers' => $customers, 'search' => $search, 'showArchived' => $showArchived,
            'total' => $builder->pager->getTotal(), 'pager' => $builder->pager,
        ]);
    }

    public function new(): string
    {
        return $this->form(null);
    }

    public function show(int $id): string
    {
        $customer = $this->customers->find($id);
        if ($customer === null) {
            throw PageNotFoundException::forPageNotFound('Customer not found.');
        }

        $sales = db_connect()->table('sales')->where('customer_id', $id)->orderBy('created_at', 'DESC')->limit(8)->get()->getResultArray();
        return view('customers/show', [
            'pageTitle' => $customer['full_name'], 'activePage' => 'customers',
            'customer' => $customer, 'sales' => $sales,
        ]);
    }

    public function create()
    {
        $data = $this->customerInput();
        if (! $this->validateData($data, $this->customerRules())) {
            return redirect()->back()->withInput()->with('formErrors', $this->validator->getErrors());
        }

        if (! $this->customers->insert($data + ['is_active' => 1, 'created_at' => date('Y-m-d H:i:s')])) {
            return redirect()->back()->withInput()->with('formErrors', $this->customers->errors() ?: ['general' => 'The customer could not be saved.']);
        }

        return redirect()->to(site_url('customers'))->with('success', 'Customer account added.');
    }

    public function edit(int $id)
    {
        $customer = $this->customers->find($id);
        if ($customer === null) {
            throw PageNotFoundException::forPageNotFound('Customer not found.');
        }
        return $this->form($customer);
    }

    public function update(int $id)
    {
        if ($this->customers->find($id) === null) {
            return redirect()->to(site_url('customers'))->with('error', 'That customer account no longer exists.');
        }
        $data = $this->customerInput();
        if (! $this->validateData($data, $this->customerRules())) {
            return redirect()->back()->withInput()->with('formErrors', $this->validator->getErrors());
        }
        if (! $this->customers->update($id, $data)) {
            return redirect()->back()->withInput()->with('formErrors', $this->customers->errors() ?: ['general' => 'The customer could not be updated.']);
        }
        return redirect()->to(site_url('customers/' . $id))->with('success', 'Customer account updated.');
    }

    public function archive(int $id)
    {
        $customer = $this->customers->find($id);
        if ($customer === null) {
            throw PageNotFoundException::forPageNotFound('Customer not found.');
        }
        $this->customers->update($id, ['is_active' => 0]);
        return redirect()->to(site_url('customers'))->with('success', 'Customer archived. Past sales and customer details are retained.');
    }

    public function restore(int $id)
    {
        if ($this->customers->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Customer not found.');
        }
        $this->customers->update($id, ['is_active' => 1]);
        return redirect()->to(site_url('customers?archived=1'))->with('success', 'Customer restored to the active directory.');
    }

    private function form(?array $customer): string
    {
        return view('customers/form', [
            'pageTitle' => $customer === null ? 'Add customer' : 'Edit customer', 'activePage' => 'customers',
            'customer' => $customer, 'formErrors' => session()->getFlashdata('formErrors') ?? [],
            'formAction' => site_url($customer === null ? 'customers' : 'customers/' . $customer['id']),
        ]);
    }

    private function customerInput(): array
    {
        return [
            'full_name' => preg_replace('/\s+/u', ' ', trim((string) $this->request->getPost('full_name'))),
            'email' => trim((string) $this->request->getPost('email')),
            'phone' => trim((string) $this->request->getPost('phone')) ?: null,
        ];
    }

    private function customerRules(): array
    {
        return [
            'full_name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|max_length[100]',
            'phone' => 'permit_empty|max_length[20]',
        ];
    }
}
