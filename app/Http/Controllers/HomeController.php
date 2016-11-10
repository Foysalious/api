<?php namespace App\Http\Controllers;

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use App\Http\Requests;

class HomeController extends Controller {
    private $auth;

    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->auth = new AuthController();
        $this->middleware('guest', ['except' => ['logout']]);
        $this->middleware('auth', ['only' => ['logout']]);
    }

    /**
     * Show the application landing page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home.landing');
    }

    /**
     * Show the application login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('home.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $login = $this->auth->login(["email_or_mobile" => $request->email_or_mobile, "password" => $request->password]);
        if ($login['success'])
        {
            return redirect()->to(getDomain() . '/dashboard');
        }
        return redirect()->back()->with('msg', $login['msg'])->with('account', $login['partner']);
    }

    /**
     * Handle a login request from facebook kit to the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function login_with_kit(Request $request)
    {
        $login = $this->auth->login_with_kit($request->code);
        if ($login['success'])
        {
            return redirect()->to(getDomain() . '/dashboard');
        }
        return redirect()->back()->with('msg', $login['msg'])->with('account', $login['partner']);
    }

    /**
     * Handle a logout request.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        $this->auth->logout();
        return redirect()->to('/login');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegisterForm()
    {
        return view('home.register');
    }

    /**
     * Handle a register request to the application. (with Account kit)
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {

    }

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Http\Response
     */
    public function showResetPasswordEmailForm()
    {
        return view('home.email');
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Http\Response
     */
    public function showResetPasswordForm()
    {
        return view('home.reset');
    }
}
