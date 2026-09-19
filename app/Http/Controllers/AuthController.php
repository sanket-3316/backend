<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserModel;
use App\Models\LoginLog;
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

            $log = LoginLog::create([
                'user_id' => $user->id,
                'login_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            session(['login_log_id' => $log->id]);

            return redirect('dashboard');
        }

        return back()->with('error', 'Invalid credentials');
    }

    // Logout
    public function logout()
    {
        $logId = session('login_log_id');
        if ($logId) {
            LoginLog::where('id', $logId)->update(['logout_at' => now()]);
        }

        session()->forget(['user', 'login_log_id']);
        return redirect('/');
    }
}