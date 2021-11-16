<?php namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Payment\PaymentManager;

class UpayController extends Controller
{
    public function validatePayment(Request $request,PaymentManager $manager)
    {
        dd($request->all());
    }
}