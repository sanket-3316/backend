<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserModel;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // Login Page
    public function login()
    {
        return view('auth.login');
    }

    // Login Process
    public function loginPost(Request $request)
    {
        $user = $this->userModel->getByEmail($request->email);

        if ($user && Hash::check($request->password, $user->password)) {
            session(['user' => $user]);

            return redirect('dashboard');
        }

        return back()->with('error', 'Invalid credentials');
    }

    // Logout
    public function logout()
    {
        session()->forget('user');
        return redirect('/');
    }
}