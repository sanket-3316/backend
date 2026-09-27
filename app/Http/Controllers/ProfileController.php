<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // Always the fresh DB row, not the (possibly stale) session copy —
        // this is the "logged in user details" the profile tab shows.
        $user = DB::table('users')
            ->leftJoin('roles', 'users.role', '=', 'roles.id')
            ->where('users.id', session('user')->id)
            ->select('users.id', 'users.name', 'users.email', 'users.role', 'roles.name as role_name', 'users.created_at')
            ->first();

        return view('profile.index', compact('user'));
    }

    // Name/email only — password changes go through changePassword() below,
    // kept separate so a wrong current-password doesn't block an unrelated
    // name edit and vice versa.
    public function update(Request $request)
    {
        $id = session('user')->id;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $this->userModel->updateUser($id, [
            'name' => $request->name,
            'email' => $request->email,
            'updated_at' => now(),
        ]);

        // Keep the session copy in sync so the navbar/sidebar greeting and
        // any other session('user')->name/email use reflect the change
        // immediately, without forcing a re-login.
        $fresh = DB::table('users')->where('id', $id)->first();
        session(['user' => $fresh]);

        return response()->json(['status' => true, 'message' => 'Profile updated successfully']);
    }

    public function changePassword(Request $request)
    {
        $id = session('user')->id;

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $user = DB::table('users')->where('id', $id)->first();

        if (!$user || !Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => false,
                'errors' => ['current_password' => ['Current password is incorrect']],
            ], 422);
        }

        $this->userModel->updateUser($id, [
            'password' => Hash::make($request->new_password),
            'updated_at' => now(),
        ]);

        return response()->json(['status' => true, 'message' => 'Password changed successfully']);
    }
}
