<?php

namespace App\Controllers;

use App\Libraries\ImageUploadService;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Files\UploadedFile;
use Throwable;

class Users extends BaseController
{
    private UserModel $users;
    private ImageUploadService $images;

    public function __construct()
    {
        $this->users = new UserModel();
        $this->images = new ImageUploadService();
    }

    public function index(): string
    {
        $search = trim((string) $this->request->getGet('q'));
        $showArchived = $this->request->getGet('archived') === '1';
        $builder = $this->users->where('is_active', $showArchived ? 0 : 1)->orderBy('full_name', 'ASC');
        if ($search !== '') {
            $builder->groupStart()->like('full_name', $search)->orLike('username', $search)->groupEnd();
        }
        $users = $builder->paginate(10);

        return view('users/index', [
            'pageTitle' => 'User accounts', 'activePage' => 'users', 'users' => $users,
            'search' => $search, 'showArchived' => $showArchived,
            'total' => $builder->pager->getTotal(), 'pager' => $builder->pager,
        ]);
    }

    public function new(): string
    {
        return $this->form(null);
    }

    public function show(int $id): string
    {
        $user = $this->users->find($id);
        if ($user === null) {
            throw PageNotFoundException::forPageNotFound('Staff account not found.');
        }
        $sales = db_connect()->table('sales')->where('sold_by', $id)->orderBy('created_at', 'DESC')->limit(8)->get()->getResultArray();
        return view('users/show', [
            'pageTitle' => $user['full_name'], 'activePage' => 'users', 'user' => $user, 'sales' => $sales,
        ]);
    }

    public function create()
    {
        $data = $this->userInput();
        $password = (string) $this->request->getPost('password');
        $validationData = $data + ['password' => $password];
        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|alpha_numeric_punct|is_unique[users.username]',
            'full_name' => 'required|min_length[2]|max_length[100]',
            'password' => 'required|min_length[12]|max_length[255]',
            'role' => 'required|in_list[admin,manager,cashier,inventory]',
        ];
        if (! $this->validateData($validationData, $rules)) {
            return redirect()->back()->withInput()->with('formErrors', $this->validator->getErrors());
        }

        $avatar = $this->storeAvatar();
        if (isset($avatar['error'])) {
            return redirect()->back()->withInput()->with('formErrors', ['avatar' => $avatar['error']]);
        }

        $data += [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'avatar' => $avatar['path'] ?? null,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        if (! $this->users->insert($data)) {
            $this->images->removeGenerated($data['avatar']);
            return redirect()->back()->withInput()->with('formErrors', $this->users->errors() ?: ['general' => 'The staff account could not be saved.']);
        }

        return redirect()->to(site_url('users'))->with('success', 'Staff account added.');
    }

    public function edit(int $id)
    {
        $user = $this->users->find($id);
        if ($user === null) {
            throw PageNotFoundException::forPageNotFound('Staff account not found.');
        }
        return $this->form($user);
    }

    public function update(int $id)
    {
        $existing = $this->users->find($id);
        if ($existing === null) {
            return redirect()->to(site_url('users'))->with('error', 'That staff account no longer exists.');
        }

        $data = $this->userInput();
        $password = (string) $this->request->getPost('password');
        $validationData = $data;
        $rules = [
            'username' => "required|min_length[3]|max_length[50]|alpha_numeric_punct|is_unique[users.username,id,{$id}]",
            'full_name' => 'required|min_length[2]|max_length[100]',
            'role' => 'required|in_list[admin,manager,cashier,inventory]',
        ];
        if ($password !== '') {
            $validationData['password'] = $password;
            $rules['password'] = 'min_length[12]|max_length[255]';
        }

        if (! $this->validateData($validationData, $rules)) {
            return redirect()->back()->withInput()->with('formErrors', $this->validator->getErrors());
        }
        if (($existing['role'] ?? '') === 'admin' && ($data['role'] !== 'admin' || $existing['is_active'] != 1) && $this->activeAdminCount() <= 1) {
            return redirect()->back()->withInput()->with('formErrors', ['role' => 'At least one active administrator must remain.']);
        }

        if ($password !== '') {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $avatar = $this->storeAvatar();
        if (isset($avatar['error'])) {
            return redirect()->back()->withInput()->with('formErrors', ['avatar' => $avatar['error']]);
        }
        if (isset($avatar['path'])) {
            $data['avatar'] = $avatar['path'];
        }

        if (! $this->users->update($id, $data)) {
            $this->images->removeGenerated($avatar['path'] ?? null);
            return redirect()->back()->withInput()->with('formErrors', $this->users->errors() ?: ['general' => 'The staff account could not be updated.']);
        }
        if (isset($avatar['path'])) {
            $this->images->removeGenerated($existing['avatar'] ?? null);
        }
        if ((int) session()->get('user_id') === $id) {
            session()->set(['full_name' => $data['full_name'], 'role' => $data['role']]);
        }

        return redirect()->to(site_url('users/' . $id))->with('success', 'Staff account updated.');
    }

    public function archive(int $id)
    {
        $user = $this->users->find($id);
        if ($user === null) {
            throw PageNotFoundException::forPageNotFound('Staff account not found.');
        }
        if ((int) session()->get('user_id') === $id) {
            return redirect()->to(site_url('users'))->with('error', 'You cannot archive the account you are currently using.');
        }
        if (($user['role'] ?? '') === 'admin' && $this->activeAdminCount() <= 1) {
            return redirect()->to(site_url('users'))->with('error', 'At least one active administrator must remain.');
        }
        $this->users->update($id, ['is_active' => 0]);
        return redirect()->to(site_url('users'))->with('success', 'Staff account archived. Existing sales remain in the history.');
    }

    public function restore(int $id)
    {
        if ($this->users->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Staff account not found.');
        }
        $this->users->update($id, ['is_active' => 1]);
        return redirect()->to(site_url('users?archived=1'))->with('success', 'Staff account restored.');
    }

    private function form(?array $user): string
    {
        return view('users/form', [
            'pageTitle' => $user === null ? 'Add staff account' : 'Edit staff account', 'activePage' => 'users',
            'user' => $user, 'formErrors' => session()->getFlashdata('formErrors') ?? [],
            'formAction' => site_url($user === null ? 'users' : 'users/' . $user['id']),
        ]);
    }

    private function userInput(): array
    {
        return [
            'username' => strtolower(trim((string) $this->request->getPost('username'))),
            'full_name' => preg_replace('/\s+/u', ' ', trim((string) $this->request->getPost('full_name'))),
            'role' => (string) $this->request->getPost('role'),
        ];
    }

    private function storeAvatar(): array
    {
        $file = $this->request->getFile('avatar');
        if (! $file instanceof UploadedFile || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return [];
        }
        try {
            return ['path' => $this->images->storeThumbnail($file, 'avatars', 'avatar', 256)];
        } catch (Throwable $exception) {
            log_message('warning', 'Avatar upload rejected: {message}', ['message' => $exception->getMessage()]);
            return ['error' => $exception->getMessage()];
        }
    }

    private function activeAdminCount(): int
    {
        return $this->users->where('role', 'admin')->where('is_active', 1)->countAllResults();
    }
}
