<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Services\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    protected $logger;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Log $logger)
    {
        $this->logger = $logger;
    }
    public function signIn()
    {
        return view('auth.signIn');
    }

    public function login(Request $request)
    {
        $logins = $request->only('username', 'password');

        if (auth()->attempt($logins)) {
//            return \auth()->user();
            return Redirect::intended('/dashboard')->with('success', 'Successful! You logged in successfully');
        } else {
            return redirect()->back()->with('error', 'Oops! Username or password is invalid');
        }
    }

    public function dashboard()
    {
        if (Auth::check() && \auth()->user()->status === 1) {
            if (auth()->user()->role_id == 1) {
                $this->logger->create();
                return redirect()->route('admin.dashboard')->with('success', 'Successful! You logged in successfully');
            }elseif(auth()->user()->role_id == 7) {
                $this->logger->create();
                return redirect()->route('accounts.dashboard')->with('success', 'Successful! You logged in successfully');

            } else{
                $this->logger->create();
                return redirect()->route('clerk.dashboard')->with('success', 'Successful! You logged in successfully');
            }
        } else {
            return redirect()->route('/')->with('info', 'Oops! Your session expired, sign in again');

        }
    }

    public function passwordReset()
    {
        return view('auth.passwordReset');
    }

    public function emailConfirmation()
    {
        return view('auth.emailConfirmation');
    }

    public function resetPassword()
    {
        return view('auth.resetPassword');
    }

    public function logOut()
    {
        \auth()->logout();
        Session::flush();
        return redirect()->route('/')->with('info', 'Goodbye! You have logged out');

    }
}
