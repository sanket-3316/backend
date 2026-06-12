<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserModel;
use App\Models\RoleModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $userModel;
    protected $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    // Dashboard
    public function index()
    {
        $roles = $this->roleModel->getAll();
        return view('user.dashboard', compact('roles'));
    }


    /* ADD USER */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }


        $this->userModel->insertUser([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'User added');
    }

    /* UPDATE USER */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'updated_at' => now(),
        ];

        if ($request->password && !empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        $this->userModel->updateUser($id, $data);

        return back()->with('success', 'User updated');
    }

    // Soft delete
    public function delete(Request $req)
    {
        try {
            $id = $req->user_id;
            $result = $this->userModel->deleteUser($id);

            if ($result) {
                return response()->json([
                    'status' => true,
                    'message' => 'User deleted successfully'
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Delete failed'
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage()
            ]);
        }
    }

    public function ajaxList(Request $request)
    {
        $start = $request->start;
        $length = $request->length;
        $search = $request->search['value'] ?? '';

        $data = $this->userModel->getUsersDataTable($start, $length, $search);

        $total = $this->userModel->countAll();
        $filtered = $this->userModel->countFiltered($search);

        $rows = [];

        foreach ($data as $user) {
            $rows[] = [
                $user->id,
                $user->name,
                $user->email,
                $user->role_name ?? '—',
                '<button class="btn-custom btn-primary-gradient editUser"
                    data-id="' . $user->id . '"
                    data-name="' . htmlspecialchars($user->name) . '"
                    data-email="' . htmlspecialchars($user->email) . '"
                    data-role="' . $user->role . '">Edit</button>
                <button class="btn-custom btn-danger-gradient deleteUser"
                    data-id="' . $user->id . '">Delete</button>',
            ];
        }

        return response()->json([
            "draw" => intval($request->draw),
            "recordsTotal" => $total,
            "recordsFiltered" => $filtered,
            "data" => $rows
        ]);
    }
}
