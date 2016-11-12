<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Http\Request;
use Cache;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller {
    private $customer;

    public function __construct()
    {
        $this->customer = new CustomerRepository();
    }

    public function sendResetPasswordEmail(Request $request)
    {
        if ($customer = $this->customer->ifExist($request->input('email'), 'email'))
        {
            $this->customer->sendResetPasswordEmail($customer);
            return response()->json(['msg' => 'Reset Password link send to email!', 'code' => '200']);
        }
        else
        {
            return response()->json(['msg' => 'This email doesn\'t exist', 'code' => '404']);
        }
    }

    public function getResetPasswordForm(Customer $customer, $code)
    {
        $code = Cache::get('$customer->id' . '-reset-password');
        if (empty($code))
        {
            Cache::forget('$customer->id' . '-reset-password');
            return view('reset-password-form', ['show' => false]);
        }
        else
        {
            return view('reset-password-form', ['show' => true, 'customer' => $customer]);
        }
    }

    public function resetPassword(Customer $customer, Request $request)
    {
        if (Hash::check($request->input('old_password'), $customer->password))
        {
            $customer->password = bcrypt($request->input('new_password'));
            $customer->update();
            Cache::forget('$customer->id' . '-reset-password');
            return back();
        }
        else
        {
            return "error";
        }
    }
}
