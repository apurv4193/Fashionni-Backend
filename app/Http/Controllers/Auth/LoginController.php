<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Log;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
    }
    
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        return $this->authenticated($request, $this->guard()->user())
                ?: redirect()->intended($this->redirectPath());
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);
        
        if (Auth::guard()->attempt(['email' => $request->email, 'password' => $request->password], $request->filled('remember'))) 
        {
            if( Auth::user()->hasRole('superadmin') ) 
            {
                Log::info('Super admin '. Auth::user()->name .' - '.Auth::user()->id .' LoggedIn. Request ip - '. \Request::ip());
                return $this->sendLoginResponse($request);
            }
            
            $this->guard()->logout();
            $request->session()->invalidate();
            return $this->sendFailedLoginResponse($request);
        } 
        else 
        {
            return $this->sendFailedLoginResponse($request);
        }
    }
}