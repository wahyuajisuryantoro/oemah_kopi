<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Check user role
            $user = Auth::user();
            if ($user->role === 'owner') {
                Auth::logout();
                Alert::error('Error', 'Access denied for owner account');
                return redirect()->route('login');
            }

            if ($user->role === 'kasir') {
                Alert::success('Success', 'Welcome back, Cashier!');
                return redirect()->intended('/dashboard-kasir');
            }

            if ($user->role === 'dapur') {
                Alert::success('Success', 'Welcome back, Kitchen Staff!');
                return redirect()->intended('/dashboard-dapur');
            }
        }

        Alert::error('Error', 'The provided credentials do not match our records.');
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Alert::success('Success', 'Successfully logged out!');
        return redirect()->route('login');
    }
}
