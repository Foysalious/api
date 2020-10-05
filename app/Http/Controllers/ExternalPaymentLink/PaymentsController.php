<?php


namespace App\Http\Controllers\ExternalPaymentLink;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentsController extends Controller
{
    public function create(Request $request)
    {
        try {
            $this->validate($request, ['amount' => 'required|numeric|min:10|max:100000',]);
        } catch (ValidationException $e) {
            $msg = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        }
    }
}
